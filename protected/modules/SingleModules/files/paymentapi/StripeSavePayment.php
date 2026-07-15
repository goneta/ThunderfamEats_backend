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

            $merchant_id = intval($merchant_id);
						
			$credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,$merchant_type);
			$credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';
			$is_live = isset($credentials['is_live'])?intval($credentials['is_live']):0;
						
			$model = AR_client_meta::model()->find('client_id=:client_id AND meta1=:meta1 AND meta2=:meta2 
			AND meta3=:meta3 ', 
		    array( 
		      ':client_id'=>intval(Yii::app()->user->id),
		      ':meta1'=>$payment_code,
		      ':meta2'=>$is_live,
		      ':meta3'=>$credentials['merchant_id'],
		    )); 	

            if($model){
                \Stripe\Stripe::setApiKey($credentials['attr1']);
		    	$payment = \Stripe\PaymentMethod::retrieve($payment_method_id,[]);
                $mask_card = CommonUtility::mask("111111111111".$payment->card->last4);

                $gateway =  AR_payment_gateway::model()->find('payment_code=:payment_code', 
			    array(':payment_code'=>$payment_code)); 

                $model_method = new AR_client_payment_method;
			    $model_method->client_id = intval(Yii::app()->user->id);
			    $model_method->payment_code = $payment_code;
			    $model_method->as_default = 1;
			    $model_method->attr1 = $gateway?$gateway->payment_name:'';
			    $model_method->attr2 = $mask_card;			
			    $model_method->merchant_id = isset($credentials['merchant_id'])? intval($credentials['merchant_id']) :0;

                $model_method->method_meta = array(
                array(
                    'meta_name'=>'payment_customer_id',
                    'meta_value'=>$model->meta4,
                    'date_created'=>CommonUtility::dateNow(),
                ),
                array(
                    'meta_name'=>'payment_method_id',
                    'meta_value'=>$payment_method_id,
                    'date_created'=>CommonUtility::dateNow(),
                ),
                array(
                    'meta_name'=>'is_live',
                    'meta_value'=>$is_live,
                    'date_created'=>CommonUtility::dateNow(),
                ),
                );
                
                if($model_method->save()){
                    $this->_controller->code = 1; 
                    $this->_controller->msg = "OK";			    	
                } else $this->_controller->msg = CommonUtility::parseError( $model_method->getErrors());	

            } else $this->_controller->msg = t("Customer payment details not found");        
        } catch (Exception $e) {
            $this->_controller->msg[] = t($e->getMessage());							
        }			
        $this->_controller->responseJson();	
    }
}