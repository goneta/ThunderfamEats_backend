<?php
require 'stripe/vendor/autoload.php';

class Createmerchantaccount extends CAction
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
            $merchant_uuid = isset($this->data['merchant_uuid'])?$this->data['merchant_uuid']:'';		
            $payment_code = isset($this->data['payment_code'])?$this->data['payment_code']:'';		
            
            $merchant = CMerchants::getByUUID($merchant_uuid);
            $credentials = CPayments::getPaymentCredentials(0,$payment_code,0);			
			$credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';
			$is_live = isset($credentials['is_live'])?intval($credentials['is_live']):0;		

            $model = AR_merchant_meta::model()->find('merchant_id=:merchant_id AND meta_name=:meta_name AND meta_value=:meta_value ', 
		    array( 
		      ':merchant_id'=>intval($merchant->merchant_id),
		      ':meta_name'=>$payment_code,		      
		      ':meta_value'=>$is_live,		      
		    )); 	

            $create = false; $customer_id='';
		    if($model){
		    	if(empty($model->meta_value1)){
		    		$create = true;
		    	} else $customer_id = $model->meta_value1;
		    } else $create = true;

            if($create){		    	   
                \Stripe\Stripe::setApiKey( isset($credentials['attr1'])?$credentials['attr1']:'' );
                $customer = \Stripe\Customer::create([
                   'email'=>$merchant->contact_email,
                   'name'=>Yii::app()->input->xssClean($merchant->restaurant_name) 
                 ]);			
                 $customer_id = $customer->id;
                 $model = new AR_merchant_meta;
                 $model->merchant_id = intval($merchant->merchant_id);
                 $model->meta_name = $payment_code;
                 $model->meta_value = $is_live;
                 $model->meta_value1 = $customer_id;		    	
                 $model->save();		
             }
             
             $this->_controller->code = 1;
		         $this->_controller->msg = "OK";
             $this->_controller->details = array(		      
               'customer_id'=>$customer_id
             );			         
		} catch (Exception $e) {
			$this->_controller->msg[] = t($e->getMessage());							
		}			
		$this->_controller->responseJson();
    }
}