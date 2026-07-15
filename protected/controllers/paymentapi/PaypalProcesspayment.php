<?php
require 'stripe/vendor/autoload.php';
require 'php-jwt/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class PaypalProcesspayment extends CAction
{
    public $_controller;
    public $_id;
    public $data;

    public function __construct($controller,$id)
    {
       $this->_controller=$controller;
       $this->_id=$id;
    }

    public function run()
    {        
        try {
            
            Yii::import('application.modules.paypal.components.*');

            $this->data = $this->_controller->data;                       
            $data = isset($this->data['data'])?$this->data['data']:'';		
			$transaction_id = isset($this->data['transaction_id'])?$this->data['transaction_id']:'';
			$order_id = isset($this->data['order_id'])?$this->data['order_id']:'';		
            
            $jwt_key = new Key(CRON_KEY, 'HS256');
			$decoded = (array) JWT::decode($data, $jwt_key);  	
            
            $payment_code = isset($decoded['payment_code'])?$decoded['payment_code']:'';
			$payment_name = isset($decoded['payment_name'])?$decoded['payment_name']:'';
			$merchant_id = isset($decoded['merchant_id'])?$decoded['merchant_id']:'';
			$merchant_type = isset($decoded['merchant_type'])?$decoded['merchant_type']:'';
			$payment_description = isset($decoded['payment_description'])?$decoded['payment_description']:'';
			$payment_description_raw = isset($decoded['payment_description_raw'])?$decoded['payment_description_raw']:'';
			$transaction_description_parameters = isset($decoded['transaction_description_parameters'])?$decoded['transaction_description_parameters']:'';
			$amount = isset($decoded['amount'])?$decoded['amount']:'';			
			$transaction_amount = isset($decoded['transaction_amount'])?$decoded['transaction_amount']:0;			
			$currency_code = isset($decoded['currency_code'])?$decoded['currency_code']:'';
			$payment_type = isset($decoded['payment_type'])?$decoded['payment_type']:'';		
			$reference_id = isset($decoded['reference_id'])?$decoded['reference_id']:'';		

			$payment_details = isset($decoded['payment_details'])?(array)$decoded['payment_details']:'';
			$payment_customer_id = isset($payment_details['payment_customer_id'])?$payment_details['payment_customer_id']:'';
			$payment_method_id = isset($payment_details['payment_method_id'])?$payment_details['payment_method_id']:'';					
			
			$credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,$merchant_type);
			$credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';		
			
			$is_live = isset($credentials['is_live'])?intval($credentials['is_live']):0;

			CPaypalTokens::setProduction($is_live);
			CPaypalTokens::setCredentials($credentials,$payment_code);
			$token = CPaypalTokens::getToken(date("c"));

			CPaypal::setProduction($is_live);
		    CPaypal::setToken($token);		    	
		    $resp = CPaypal::getOrders($order_id);

            if($payment_type=="add_funds"){
                $card_id = CWallet::createCard( Yii::app()->params->account_type['digital_wallet'],Yii::app()->user->id);				   
                $meta_array = [];
                try {
                   $date_now = date("Y-m-d");
                   $bonus = AttributesTools::getDiscountToApply($transaction_amount,CDigitalWallet::transactionName(),$date_now);
                   $transaction_amount = floatval($transaction_amount)+floatval($bonus);				  
                   $meta_array[]=[
                     'meta_name'=>'topup_bonus',
                     'meta_value'=>floatval($bonus)
                   ];
                   $payment_description_raw = "Funds Added via {payment_name} with {bonus} Bonus";
                   $transaction_description_parameters = [
                     '{payment_name}'=>$payment_name,
                     '{bonus}'=>Price_Formatter::formatNumber($bonus),
                   ];
                 } catch (Exception $e) {}
 
                 CPaymentLogger::afterAdddFunds($card_id,[				    
                     'transaction_description'=>$payment_description_raw,
                     'transaction_description_parameters'=>$transaction_description_parameters,
                     'transaction_type'=>'credit',
                     'transaction_amount'=>$transaction_amount,
                     'status'=>CPayments::paidStatus(),
                     'reference_id'=>$reference_id,
                     'reference_id1'=>CDigitalWallet::transactionName(),
                     'merchant_base_currency'=>$currency_code,
                     'admin_base_currency'=>$currency_code,
                     'meta_name'=>'topup',
                     'meta_value'=>$card_id,
                     'meta_array'=>$meta_array,			
                ]);
             } elseif ($payment_type=="file_storage") {
                 # code...
             }

            Price_Formatter::init($currency_code);

			$this->_controller->code = 1;
			$this->_controller->msg = t("Payment successful. please wait while we redirect you.");
			$this->_controller->details = array(  					  
				'payment_name'=>$payment_name,
				'transaction_id'=>$transaction_id,
				'amount'=>Price_Formatter::formatNumber($amount),
				'amount_raw'=>$amount,
				'transaction_date'=>Date_Formatter::dateTime(date('c'))
			);

		} catch (Exception $e) {
			$this->_controller->msg = t($e->getMessage());							
		}			
		$this->_controller->responseJson();
    }
}