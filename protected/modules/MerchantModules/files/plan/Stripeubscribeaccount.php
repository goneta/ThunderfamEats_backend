<?php
require 'stripe/vendor/autoload.php';

class Stripeubscribeaccount extends CAction
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
			$package_uuid = isset($this->data['package_uuid'])?$this->data['package_uuid']:'';
			$customer_id = isset($this->data['customer_id'])?$this->data['customer_id']:'';

            $merchant = CMerchants::getByUUID($merchant_uuid);
			$plans = Cplans::getByUUID($package_uuid);			
			$meta_name = "plan_price_$payment_code";			

            $price = Cplans::planPriceID($meta_name,$plans->package_id);
			$price_id = $price->meta_value;
			
			$credentials = CPayments::getPaymentCredentials(0,$payment_code,0);			
			$credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';
			$is_live = isset($credentials['is_live'])?intval($credentials['is_live']):0;		
           
            $meta_name = "subscription_$payment_code";

            $model = AR_merchant_meta::model()->find('
			merchant_id=:merchant_id AND 			
			meta_name=:meta_name AND 
			meta_value=:meta_value AND 
			meta_value1=:meta_value1 ', 
		    array( 
		      ':merchant_id'=>intval($merchant->merchant_id),
		      ':meta_name'=>$meta_name,		      
		      ':meta_value'=>$plans->package_id,		      
		      ':meta_value1'=>$is_live,		      
		    )); 	
		    		    
		    $create = false; $subscriber_id=''; $client_secret='';
		    if($model){
		    	if(empty($model->meta_value3)){
		    		$create = true;
		    	} else $client_secret = $model->meta_value3;
		    } else $create = true;

            $end = '';
		    if($plans->trial_period>0){		    	
			    $dateTime = new DateTime(date('c'));			    
			    $dateTime->modify("+$plans->trial_period days");		    
			    $end = $dateTime->format( "Y-m-d H:i");			    			    
			    $end = strtotime($end);			    			    
		    }

            if($create){
                $model = new AR_merchant_meta;		    	
		    	$stripe = new \Stripe\StripeClient(isset($credentials['attr1'])?$credentials['attr1']:'');	    			    	
		    	
		    	if($plans->trial_period>0){
		    		$subscription = $stripe->subscriptions->create([
				        'customer' => $customer_id,
				        'items' => [[
				            'price' => $price_id,
				        ]],
				        'metadata'=>array(
				          'merchant_uuid'=>$merchant_uuid,
				        ),
				        'payment_behavior' => 'default_incomplete',
				        'expand' => ['latest_invoice.payment_intent'],
				        'trial_end'=>$end
				    ]);		
		    	} else {
			    	$subscription = $stripe->subscriptions->create([
				        'customer' => $customer_id,
				        'items' => [[
				            'price' => $price_id,
				        ]],
				        'metadata'=>array(
				          'merchant_uuid'=>$merchant_uuid,
				        ),
				        'payment_behavior' => 'default_incomplete',
				        'expand' => ['latest_invoice.payment_intent'],				        
				    ]);		
		    	}				    			   
			    				
			    $client_secret='';
			    if(isset($subscription->latest_invoice->payment_intent->client_secret)){
			    	$client_secret = $subscription->latest_invoice->payment_intent->client_secret;
			    } else {			    	
			    	$this->_controller->code = 3;
			    	$this->_controller->msg = "ok";
			    	$this->_controller->details = array(
			    	  'subscriber_id'=>$subscription->id
			    	);			    	
			    }
			    
			    $model->merchant_id = $merchant->merchant_id;
				$model->meta_name = $meta_name;
				$model->meta_value = $plans->package_id;
				$model->meta_value1 = $is_live;
				$model->meta_value2 = $subscription->id;			
				$model->meta_value3 = $client_secret;
				$model->save();
            }
			
            if($this->_controller->code==2){
				$origin = isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST']; 
			    $this->_controller->code = 1;
		        $this->_controller->msg = "OK";
			    $this->_controller->details = array(		      
			      'client_secret'=>$client_secret,
				  'publish_key'=>isset($credentials['attr2'])?$credentials['attr2']:'',
				  'origin'=>$origin
			    );		
		    }				

		} catch (Exception $e) {
			$this->_controller->msg[] = t($e->getMessage());							
		}			
		$this->_controller->responseJson();
    }
}