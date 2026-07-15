<?php
require 'stripe/vendor/autoload.php';

class StripePaymentIntent extends CAction
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
            $order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';
		    $cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
		    $payment_uuid = isset($this->data['payment_uuid'])?$this->data['payment_uuid']:'';

            $data = AR_ordernew::model()->find('order_uuid=:order_uuid',array(':order_uuid'=>$order_uuid));
            if($data){
                $merchant = CMerchantListingV1::getMerchant($data->merchant_id);		    	
		      $credentials = CPayments::getPaymentCredentials($data->merchant_id,$data->payment_code,
		      $merchant->merchant_type);
			  $credentials = isset($credentials[$data->payment_code])?$credentials[$data->payment_code]:'';
			  $is_live = isset($credentials['is_live'])?intval($credentials['is_live']):0;   
			  			  
			  $payment_method = CPayments::getPaymentMethodMeta($payment_uuid, Yii::app()->user->id );
			  
			  $payment_description = t("Payment to merchant [merchant]. Order#[order_id]",
		      array('[merchant]'=>$merchant->restaurant_name,'[order_id]'=>$data->order_id ));	
		      
		      $total = floatval(Price_Formatter::convertToRaw($data->total));
		      
		      \Stripe\Stripe::setApiKey($credentials['attr1']);					  
			  
		       $payment_intent = \Stripe\PaymentIntent::create([
			    'amount' => ($total*100),
			    'currency' => $data->use_currency_code,
			    'customer' =>  isset($payment_method['payment_customer_id'])?$payment_method['payment_customer_id']:'' ,
			    'payment_method' => isset($payment_method['payment_method_id'])?$payment_method['payment_method_id']:'' ,
			    'off_session' => true,
			    'confirm' => true,
			    'description'=> $payment_description
			  ]);      
			  
			  $transaction_id = $payment_intent->id;		      	  
		      
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
			  $model->payment_uuid = $payment_uuid;
			  $model->save();			  
			  
              $this->_controller->code = 1;
			  $this->_controller->msg = t("Payment successful. please wait while we redirect you.");

			  $this->_controller->details = array(  					  
			    'order_uuid'=>$data->order_uuid
			  );
            } else $this->_controller->msg = t("Order id not found");
		    
		} catch (Exception $e) {
			$this->_controller->msg = t($e->getMessage());							
		}			
		$this->_controller->responseJson();
    }
}