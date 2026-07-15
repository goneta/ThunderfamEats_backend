<?php
require 'stripe/vendor/autoload.php';
class StripeSavePayment extends CAction
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
            
            $payment_method_id = isset($this->data['payment_method_id'])?$this->data['payment_method_id']:'';				
			$merchant_id = isset($this->data['merchant_id'])?$this->data['merchant_id']:0;
			$payment_code = isset($this->data['payment_code'])?$this->data['payment_code']:'';
			$merchant_type = isset($this->data['merchant_type'])?$this->data['merchant_type']:2;
            $driver_id = isset($this->data['reference'])?$this->data['reference']:0;

            $driver = CDriver::getDriver($driver_id);
            
            $credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,$merchant_type);
			$credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';
			$is_live = isset($credentials['is_live'])?intval($credentials['is_live']):0;

            $model = AR_driver_meta::model()->find('reference_id=:reference_id AND meta_name=:meta_name AND meta_value1=:meta_value1 ', 
		    array( 
		      ':reference_id'=>intval($driver_id),
		      ':meta_name'=>$payment_code,		      
		      ':meta_value1'=>$is_live,		      
		    )); 
            
            if($model){
                \Stripe\Stripe::setApiKey($credentials['attr1']);
		    	$payment = \Stripe\PaymentMethod::retrieve($payment_method_id,[]);
                $mask_card = CommonUtility::mask("111111111111".$payment->card->last4);
                
                $gateway =  AR_payment_gateway::model()->find('payment_code=:payment_code', 
			    array(':payment_code'=>$payment_code)); 

                $model_method = new AR_driver_payment_method;
			    $model_method->driver_id = intval($driver_id);
			    $model_method->payment_code = $payment_code;
			    $model_method->as_default = 1;
			    $model_method->attr1 = $gateway?$gateway->payment_name:'';
			    $model_method->attr2 = $mask_card;			
			    $model_method->merchant_id = isset($credentials['merchant_id'])? intval($credentials['merchant_id']) :0;

                $model_method->method_meta = array(
                array(
                    'meta_name'=>'payment_customer_id',
                    'meta_value1'=>$model->meta_value2,
                    'date_created'=>CommonUtility::dateNow(),
                ),
                array(
                    'meta_name'=>'payment_method_id',
                    'meta_value1'=>$payment_method_id,
                    'date_created'=>CommonUtility::dateNow(),
                ),
                array(
                    'meta_name'=>'is_live',
                    'meta_value1'=>$is_live,
                    'date_created'=>CommonUtility::dateNow(),
                ),
                );
                
                if($model_method->save()){
                    $this->_controller->code = 1; 
                    $this->_controller->msg = "OK";			    	
                } else $this->_controller->msg = CommonUtility::parseError( $model_method->getErrors());                

            } else $this->_controller->msg = t("Payment details not found");        
          
        } catch (Exception $e) {
            $this->_controller->msg[] = t($e->getMessage());							            
        }			        
        $this->_controller->responseJson();	
    }
}