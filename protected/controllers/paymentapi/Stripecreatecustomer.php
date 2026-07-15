<?php
require 'stripe/vendor/autoload.php';

class Stripecreatecustomer extends CAction
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

			$merchant_id = isset($this->data['merchant_id'])?$this->data['merchant_id']:0;		
			$payment_code = isset($this->data['payment_code'])?$this->data['payment_code']:'';		
			$merchant_type = isset($this->data['merchant_type'])?$this->data['merchant_type']:'';

			$merchant_id = intval($merchant_id);
								
			$credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,$merchant_type);
			$credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';
			$is_live = isset($credentials['is_live'])?intval($credentials['is_live']):0;		
			
			$full_name = Yii::app()->user->first_name." ".Yii::app()->user->last_name;
			
			$model = AR_client_meta::model()->find('client_id=:client_id AND meta1=:meta1 AND meta2=:meta2 
			AND meta3=:meta3 ', 
		    array( 
		      ':client_id'=>intval(Yii::app()->user->id),
		      ':meta1'=>$payment_code,
		      ':meta2'=>$is_live,
		      ':meta3'=>isset($credentials['merchant_id'])?$credentials['merchant_id']:'',
		    )); 			    
			
		    \Stripe\Stripe::setApiKey( isset($credentials['attr1'])?$credentials['attr1']:'' );
		    
		    $create = false; $customer_id='';
		    if($model){
		    	if(empty($model->meta4)){
		    		$create = true;
		    	} else $customer_id = $model->meta4;
		    } else $create = true;
			
		    if($create){
		    	$customer = \Stripe\Customer::create([
				  'email'=>Yii::app()->user->email_address,
				  'name'=>$full_name
				]);						    
				$customer_id = $customer->id;
				$model = new AR_client_meta;
		    	$model->client_id = intval(Yii::app()->user->id);
		    	$model->meta1 = $payment_code;
		    	$model->meta2 = $is_live;
		    	$model->meta3 = $credentials['merchant_id'];
		    	$model->meta4 = $customer_id;
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
		      'test_card'=>false
		    );
		    
		    if(DEMO_MODE){
				$this->_controller->details['test_card'] = array(
				  'card_number'=>'4242424242424242',
				  'expiry'=>'Future date',
				  'cvv'=>'123',
				  'zip'=>'12345',				  
				);
			}
		    
		} catch (Exception $e) {
			$this->_controller->msg[] = t($e->getMessage());							
		}			
		$this->_controller->responseJson();
    }
}