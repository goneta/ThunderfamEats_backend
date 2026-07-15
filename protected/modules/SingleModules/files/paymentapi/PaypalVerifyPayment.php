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
            $transaction_id = isset($this->data['transaction_id'])?$this->data['transaction_id']:'';
			$order_id = isset($this->data['order_id'])?$this->data['order_id']:'';
			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';
			$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
			
			$data = AR_ordernew::model()->find('order_uuid=:order_uuid',array(':order_uuid'=>$order_uuid));
            if($data){

                $merchant = CMerchantListingV1::getMerchant($data->merchant_id);
		    	
		    	$credentials = CPayments::getPaymentCredentials($data->merchant_id,$data->payment_code,
		    	$merchant->merchant_type);
			    $credentials = isset($credentials[$data->payment_code])?$credentials[$data->payment_code]:'';

			    $is_live = isset($credentials['is_live'])?intval($credentials['is_live']):0;
			    
			    CPaypalTokens::setProduction($is_live);
			    CPaypalTokens::setCredentials($credentials,$data->payment_code);
			    $token = CPaypalTokens::getToken(date("c"));
			    
			    CPaypal::setProduction($is_live);
		    	CPaypal::setToken($token);		    	
		    	$resp = CPaypal::getOrders($order_id);

		    	$data->scenario = "new_order";
		    	$data->status = COrders::newOrderStatus();
		    	$data->payment_status = CPayments::paidStatus();
		    	$data->cart_uuid = $cart_uuid;
		    	$data->save();
		    			    			    	
		    	$model = new AR_ordernew_transaction;
				$model->order_id = $data->order_id;
				$model->merchant_id = $data->merchant_id;
				$model->client_id = $data->client_id;
				$model->payment_code = $data->payment_code;
				$model->trans_amount = $data->total;
				$model->currency_code = $data->use_currency_code;
				$model->payment_reference = $transaction_id;
				$model->status = CPayments::paidStatus();
				$model->reason = isset($resp['status'])?$resp['status']:'';
				if($model->save()){
					
					/*INSERT NOTES FOR PAYMENT*/
					$params = array(  
					   array('transaction_id'=>$model->transaction_id,'order_id'=>$data->order_id, 
					   'meta_name'=>'order_id', 'meta_value'=>$order_uuid ),
					   					   
					   array('transaction_id'=>$model->transaction_id,'order_id'=>$data->order_id, 
					   'meta_name'=>'transaction_id', 'meta_value'=>$transaction_id ),
					    
					);                    
					$builder=Yii::app()->db->schema->commandBuilder;
				    $command=$builder->createMultipleInsertCommand('{{ordernew_trans_meta}}',$params);
				    $command->execute();		

                    $this->_controller->code = 1;
                    $this->_controller->msg = t("Payment successful. please wait while we redirect you.");
                                    
                    $this->_controller->details = array(  					  
                      'order_uuid'=>$data->order_uuid
                    );
					
				} else $this->_controller->msg = $model->getErrors();								

            } else $this->_controller->msg = t("Order id not found");

		} catch (Exception $e) {
			$this->_controller->msg = t($e->getMessage());							
		}			
		$this->_controller->responseJson();
    }
}