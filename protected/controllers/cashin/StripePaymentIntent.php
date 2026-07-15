<?php
require 'stripe/vendor/autoload.php';

class StripePaymentIntent extends CAction
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
						            
            $this->data = $this->_controller->data;                   
            $driver_id = isset($this->data['reference'])?$this->data['reference']:0;
            $amount = isset($this->data['amount'])?$this->data['amount']:0;
            $payment_code = isset($this->data['payment_code'])?$this->data['payment_code']:'';
            $payment_uuid = isset($this->data['payment_uuid'])?$this->data['payment_uuid']:'';

            $driver = CDriver::getDriver($driver_id);
            $card_id = CWallet::getCardID(Yii::app()->params->account_type['driver'], $driver_id );            
            
            $merchant_id=0; $merchant_type = 2;
            
            $credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,$merchant_type);
			$credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';            
			$is_live = isset($credentials['is_live'])?intval($credentials['is_live']):0;

            $amount = floatval(Price_Formatter::convertToRaw($amount));
            $base_currency = AttributesTools::defaultCurrency();            

            $merchantID = $driver->merchant_id;                        
            $exchange_rate_merchant_to_admin = 1; $exchange_rate_admin_to_merchant=1;

            $atts = OptionsTools::find(['multicurrency_enabled']);
            $multicurrency_enabled = isset($atts['multicurrency_enabled'])?$atts['multicurrency_enabled']:false;
            $multicurrency_enabled = $multicurrency_enabled==1?true:false;       

            $attr = OptionsTools::find(['merchant_default_currency'],$merchantID);
            if($multicurrency_enabled){
				$merchant_base_currency = isset($attr['merchant_default_currency'])? (!empty($attr['merchant_default_currency'])?$attr['merchant_default_currency']:$base_currency) :$base_currency;						
			} else $merchant_base_currency = $base_currency;

            $driver_default_currency = !empty($driver->default_currency)?$driver->default_currency:$merchant_base_currency;	                        

            if($multicurrency_enabled && $base_currency!=$driver_default_currency){
                $exchange_rate_merchant_to_admin = CMulticurrency::getExchangeRate($driver_default_currency,$base_currency);
                $exchange_rate_admin_to_merchant = CMulticurrency::getExchangeRate($base_currency,$driver_default_currency);
            }            
            
            $payment_method = CDriver::getPaymentMethodMeta($payment_uuid, $driver_id );            

            $payment_description = t("Cash in");
            
            \Stripe\Stripe::setApiKey($credentials['attr1']);					  
			  
		    $payment_intent = \Stripe\PaymentIntent::create([
			    'amount' => ($amount*100),
			    'currency' => $driver_default_currency,
			    'customer' =>  isset($payment_method['payment_customer_id'])?$payment_method['payment_customer_id']:'' ,
			    'payment_method' => isset($payment_method['payment_method_id'])?$payment_method['payment_method_id']:'' ,
			    'off_session' => true,
			    'confirm' => true,
			    'description'=> $payment_description
			]);      

            $transaction_id = $payment_intent->id;

            $meta_array = [];
            $meta_array[]=[
                'meta_name'=>'cashin_payment_reference',
                'meta_value'=>$transaction_id
            ];
            $meta_array[]=[
                'meta_name'=>'payment_code',
                'meta_value'=>$payment_code
            ];
            $params = [
                'transaction_description'=>"Cash in payment reference#({payment_reference})",
                'transaction_description_parameters'=>array('{payment_reference}'=>$transaction_id), 
                'transaction_type'=>"credit",
                'transaction_amount'=>$amount,
                'meta_array'=>$meta_array,                
                'status'=>"paid",
                'merchant_base_currency'=>$driver_default_currency,
                'admin_base_currency'=>$base_currency,
                'exchange_rate_merchant_to_admin'=>$exchange_rate_merchant_to_admin,
                'exchange_rate_admin_to_merchant'=>$exchange_rate_admin_to_merchant,
            ];                 
            CWallet::inserTransactions($card_id,$params);

            $this->_controller->code = 1;
			$this->_controller->msg = t("Payment successful. please wait while we redirect you.");
            $this->_controller->details = array(  					  
			    'transaction_id'=>$transaction_id
			);
         
		} catch (Exception $e) {
			$this->_controller->msg = t($e->getMessage());							            
		}			
		$this->_controller->responseJson();
    }
}