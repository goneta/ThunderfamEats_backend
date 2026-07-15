<?php
require 'razorpay/vendor/autoload.php';
use Razorpay\Api\Api;

class RazorpayCreateCustomer extends CAction
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
            
            Yii::import('application.modules.razorpay.components.*');
            $this->data = $this->_controller->data;  
            $merchant_id = isset($this->data['merchant_id'])?$this->data['merchant_id']:0;		
			$payment_code = isset($this->data['payment_code'])?$this->data['payment_code']:'';		
			$merchant_type = isset($this->data['merchant_type'])?$this->data['merchant_type']:'';	
            
            $credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,$merchant_type);
			$credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';
			$is_live = isset($credentials['is_live'])?intval($credentials['is_live']):0;		
								
			$full_name = Yii::app()->user->first_name." ".Yii::app()->user->last_name;            
			$api = new Api($credentials['attr1'], $credentials['attr2']);           
            
            $model = AR_client_meta::model()->find('client_id=:client_id AND meta1=:meta1 AND meta2=:meta2 
			AND meta3=:meta3 ', 
		    array( 
		      ':client_id'=>intval(Yii::app()->user->id),
		      ':meta1'=>$payment_code,
		      ':meta2'=>$is_live,
		      ':meta3'=>isset($credentials['merchant_id'])?$credentials['merchant_id']:'',
		    )); 	
            
            $create = false; $customer_id='';
		    if($model){
		    	if(empty($model->meta4)){
		    		$create = true;
		    	} else $customer_id = $model->meta4;
		    } else $create = true;

            if($create){
                try {
					
					$client = AR_client::model()->findbyPk(intval(Yii::app()->user->id));
					
					$customer = $api->customer->create(array(
					   'name' => $full_name,
					   'email' => $client? $client->email_address : Yii::app()->user->email_address,
					   'contact'=>$client? $client->contact_phone : Yii::app()->user->contact_number,
					   'fail_existing'=>0,
					   'notes'=> array(
					          'client_id'=> Yii::app()->user->id
					       )
					));					
					$customer_id = $customer->id;
				} catch (Exception $e) {
					$this->_controller->msg = $e->getMessage();
					$this->responseJson();						
				}
								
				if(!empty($customer_id)){			    
					$model = new AR_client_meta;
			    	$model->client_id = intval(Yii::app()->user->id);
			    	$model->meta1 = $payment_code;
			    	$model->meta2 = $is_live;
			    	$model->meta3 = $credentials['merchant_id'];
			    	$model->meta4 = $customer_id;
			    	$model->save();			
				} 							
            } 

            $payment = AR_payment_gateway::model()->find('payment_code=:payment_code', 
		    array(':payment_code'=>$payment_code)); 		
            
            if($payment){
                $model = new AR_client_payment_method;
				$model->scenario = "insert";
				$model->client_id = Yii::app()->user->id;
				$model->payment_code = $payment_code;
				$model->as_default = intval(1);
				$model->attr1 = $payment?$payment->payment_name:'unknown';	
				$model->merchant_id = intval($merchant_id);
				if($model->save()){
					$this->_controller->code = 1;
		    		$this->_controller->msg = t("Succesful");
				} else $this->_controller->msg = CommonUtility::parseError($model->getErrors());
            } else $this->_controller->msg[] = t("Payment provider not found");
            

		} catch (Exception $e) {
			$this->_controller->msg = t($e->getMessage());							
		}			
		$this->_controller->responseJson();
    }
}