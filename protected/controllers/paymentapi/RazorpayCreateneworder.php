<?php
require 'razorpay/vendor/autoload.php';
require 'php-jwt/vendor/autoload.php';
use Razorpay\Api\Api;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class RazorpayCreateneworder extends CAction
{
    public $_controller;
    public $_id;
    public $data;

    public function __construct($controller,$id)
    {
      Yii::import('application.modules.razorpay.components.*');
       $this->_controller=$controller;
       $this->_id=$id;
    }

    public function run()
    {        
        try {
                        
            $this->data = $this->_controller->data;            
            $data = isset($this->data['data'])?$this->data['data']:'';						
			$jwt_key = new Key(CRON_KEY, 'HS256');
			$decoded = (array) JWT::decode($data, $jwt_key); 
            
            $payment_code = isset($decoded['payment_code'])?$decoded['payment_code']:'';
			$payment_name = isset($decoded['payment_name'])?$decoded['payment_name']:'';
			$merchant_id = isset($decoded['merchant_id'])?$decoded['merchant_id']:'';
			$merchant_type = isset($decoded['merchant_type'])?$decoded['merchant_type']:'';
			$payment_description = isset($decoded['payment_description'])?$decoded['payment_description']:'';
			$amount = isset($decoded['amount'])?$decoded['amount']:'';			
			$currency_code = isset($decoded['currency_code'])?$decoded['currency_code']:'';
			$payment_type = isset($decoded['payment_type'])?$decoded['payment_type']:'';		
			$reference_id = isset($decoded['reference_id'])?$decoded['reference_id']:'';		

			$payment_details = isset($decoded['payment_details'])?(array)$decoded['payment_details']:'';
			$payment_customer_id = isset($payment_details['payment_customer_id'])?$payment_details['payment_customer_id']:'';
			$payment_method_id = isset($payment_details['payment_method_id'])?$payment_details['payment_method_id']:'';					
			
			$credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,$merchant_type);
			$credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';
			$is_live = isset($credentials['is_live'])?intval($credentials['is_live']):0;
			
			$api = new Api($credentials['attr1'], $credentials['attr2']);

			$order = $api->order->create([
				'receipt'=>$reference_id,
				'amount'=>($amount*100),
				'currency'=>$currency_code,
				'notes'=>array(
				  'order_uuid'=>$reference_id,	            
				)
			]);

            $model = AR_client_meta::model()->find('client_id=:client_id AND meta1=:meta1 AND meta2=:meta2 
				AND meta3=:meta3 ', 
			    array( 
			      ':client_id'=>intval(Yii::app()->user->id),
			      ':meta1'=>$payment_code,
			      ':meta2'=>$is_live,
			      ':meta3'=>isset($credentials['merchant_id'])?$credentials['merchant_id']:'',
			)); 	

			$options = array(				  
				'amount'=>($amount*100),
				'currency'=>$currency_code,
				'name'=>t("Digital wallet"),
				'description'=>$payment_description,
				'order_id'=>$order->id,				  
				'customer_id'=>$model?$model->meta4:''
			);
					
			$this->_controller->code = 1;
			$this->_controller->msg = "ok";
			$this->_controller->details = $options;
          
		} catch (Exception $e) {
			$this->_controller->msg = t($e->getMessage());							
		}			
		$this->_controller->responseJson();
    }
}