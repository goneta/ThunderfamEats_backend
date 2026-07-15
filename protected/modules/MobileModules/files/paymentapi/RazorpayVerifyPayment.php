<?php
require 'razorpay/vendor/autoload.php';
use Razorpay\Api\Api;

class RazorpayVerifyPayment extends CAction
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
            $merchant_id = isset($this->data['merchant_id'])?$this->data['merchant_id']:0;		
            $payment_code = isset($this->data['payment_code'])?$this->data['payment_code']:'';		
            $merchant_type = isset($this->data['merchant_type'])?$this->data['merchant_type']:'';
            $cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
            $order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';
            $razorpay_payment_id = isset($this->data['razorpay_payment_id'])?$this->data['razorpay_payment_id']:'';
            $razorpay_order_id = isset($this->data['razorpay_order_id'])?$this->data['razorpay_order_id']:'';
            $razorpay_signature = isset($this->data['razorpay_signature'])?$this->data['razorpay_signature']:'';

            $data = AR_ordernew::model()->find('order_uuid=:order_uuid',array(':order_uuid'=>$order_uuid));
            if($data){
                $credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,$merchant_type);
                $credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';
                $is_live = isset($credentials['is_live'])?intval($credentials['is_live']):0;
                
                $api = new Api($credentials['attr1'], $credentials['attr2']);			   			   			 
                $attributes  = array(
                    'razorpay_signature'  => $razorpay_signature,
                    'razorpay_payment_id'  => $razorpay_payment_id,
                    'razorpay_order_id' => $razorpay_order_id
                );			   
                $api->utility->verifyPaymentSignature($attributes);
                
                
                /*CAPTURE PAYMENT*/
                $amount = Price_Formatter::convertToRaw($data->total);			   
                $capture = $api->payment->fetch($razorpay_payment_id)->capture(array(
                  'amount'=>($amount*100),
                  'currency' => $data->use_currency_code
                ));
                
                
                $transaction_id = $razorpay_payment_id;
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
                $model->reason = '';
                if($model->save()){
                      /*INSERT NOTES FOR PAYMENT*/
                     $params = array(  
                        array('transaction_id'=>$model->transaction_id,'order_id'=>$data->order_id, 
                        'meta_name'=>'razorpay_payment_id', 'meta_value'=>$razorpay_payment_id ),
                                               
                        array('transaction_id'=>$model->transaction_id,'order_id'=>$data->order_id, 
                        'meta_name'=>'razorpay_order_id', 'meta_value'=>$razorpay_order_id ),
                        
                        array('transaction_id'=>$model->transaction_id,'order_id'=>$data->order_id, 
                        'meta_name'=>'razorpay_signature', 'meta_value'=>$razorpay_signature ),
                         
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