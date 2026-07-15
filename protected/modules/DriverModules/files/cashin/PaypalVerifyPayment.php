<?php
require 'stripe/vendor/autoload.php';
class PaypalVerifyPayment extends CAction
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
            
            $payment_code = 'paypal';
            $transaction_id = isset($this->data['transaction_id'])?$this->data['transaction_id']:'';
			$order_id = isset($this->data['order_id'])?$this->data['order_id']:'';
            $driver_id = isset($this->data['reference'])?$this->data['reference']:'';
            
            $driver = CDriver::getDriver($driver_id);
            $card_id = CWallet::getCardID(Yii::app()->params->account_type['driver'], $driver_id ); 

            $merchant_id=0; $merchant_type = 2;        
            $credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,$merchant_type);
			$credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';            
			$is_live = isset($credentials['is_live'])?intval($credentials['is_live']):0;

            CPaypalTokens::setProduction($is_live);
			CPaypalTokens::setCredentials($credentials,$payment_code);
            $token = CPaypalTokens::getToken(date("c"));
            
            CPaypal::setProduction($is_live);
		    CPaypal::setToken($token);		    	
		    $resp = CPaypal::getOrders($order_id);            
            $purchase_units = isset($resp['purchase_units'])?$resp['purchase_units']:'';
            $purchase_data = isset($purchase_units[0]['amount'])?$purchase_units[0]['amount']:'';
            if(is_array($purchase_data) && count($purchase_data)>=1){                
                $amount = isset($purchase_data['value'])?$purchase_data['value']:0;                

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
                
                $model_check = AR_wallet_transactions_meta::model()->find("meta_name=:meta_name AND meta_value=:meta_value",[
                    ':meta_name'=>'cashin_payment_reference',
                    ':meta_value'=>$transaction_id
                ]);
                
                if(!$model_check){
                    CWallet::inserTransactions($card_id,$params);

                    $this->_controller->code = 1;
                    $this->_controller->msg = t("Payment successful. please wait while we redirect you.");
                    $this->_controller->details = array(  					  
                        'transaction_id'=>$transaction_id
                    );
                } else $this->_controller->msg = t("Transaction already exist");

            } else $this->_controller->msg = t("Amount not valid");
		} catch (Exception $e) {
			$this->_controller->msg = t($e->getMessage());							        
		}			
		$this->_controller->responseJson();
    }
}