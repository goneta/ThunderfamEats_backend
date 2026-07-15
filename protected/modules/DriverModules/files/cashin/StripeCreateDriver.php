<?php
require 'stripe/vendor/autoload.php';

class StripeCreateDriver extends CAction
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
            $payment_code = isset($this->data['payment_code'])?$this->data['payment_code']:'';		            
            
            $credentials = CPayments::getPaymentCredentials(0,$payment_code,0);			            
			      $credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';
			      $is_live = isset($credentials['is_live'])?intval($credentials['is_live']):0;		

            $driver = CDriver::getDriver($driver_id);
                                    
            $model = AR_driver_meta::model()->find('reference_id=:reference_id AND meta_name=:meta_name AND meta_value1=:meta_value1 ', 
            array( 
              ':reference_id'=>intval($driver_id),
              ':meta_name'=>$payment_code,		      
              ':meta_value1'=>$is_live,		      
            )); 
                          
          $create = false; $customer_id='';
          if($model){
            if(empty($model->meta_value2)){
              $create = true;
            } else $customer_id = $model->meta_value2;
          } else $create = true;
            
            \Stripe\Stripe::setApiKey( isset($credentials['attr1'])?$credentials['attr1']:'' );

            if($create){		    	  
                                
                $customer = \Stripe\Customer::create([
                   'email'=>$driver->email,
                   'name'=>Yii::app()->input->xssClean("$driver->first_name $driver->last_name") 
                 ]);			
                 $customer_id = $customer->id;
                 $model = new AR_driver_meta();
                 $model->reference_id = intval($driver->driver_id);
                 $model->meta_name = $payment_code;
                 $model->meta_value1 = $is_live;
                 $model->meta_value2 = $customer_id;		    	
                 $model->save();		
             }
             
            $client_secret = '';

		    $intent = \Stripe\SetupIntent::create([
			    'customer' => $customer_id
			]);
			
			$client_secret = $intent->client_secret;
			
		    $this->_controller->code = 1;
		    $this->_controller->msg = "OK";
		    $this->_controller->details = array(
		      'client_secret'=>$client_secret,
		      'customer_id'=>$customer_id,		      
		    );

		} catch (Exception $e) {
			$this->_controller->msg[] = t($e->getMessage());							
		}			
		$this->_controller->responseJson();
    }
}