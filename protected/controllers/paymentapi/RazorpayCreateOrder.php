<?php
require 'razorpay/vendor/autoload.php';
use Razorpay\Api\Api;

class RazorpayCreateOrder extends CAction
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
								
			$credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,$merchant_type);
			$credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';
			$is_live = isset($credentials['is_live'])?intval($credentials['is_live']):0;	

            $data = AR_ordernew::model()->find('order_uuid=:order_uuid',array(':order_uuid'=>$order_uuid));
            if($data){
                
				$exchange_rate = $data->exchange_rate>0?$data->exchange_rate:1;			  		        
				if($data->amount_due>0){
					$total = floatval(Price_Formatter::convertToRaw( ($data->amount_due*$exchange_rate) ));	
				 } else $total = floatval(Price_Formatter::convertToRaw( ($data->total*$exchange_rate) ));			  

				$merchant = CMerchantListingV1::getMerchant($data->merchant_id);
				
				$payment_description = t("Payment to merchant [merchant]. Order#[order_id]",
		         array('[merchant]'=>$merchant->restaurant_name,'[order_id]'=>$data->order_id ));	

				$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
				$multicurrency_enabled = $multicurrency_enabled==1?true:false;		   	
				$enabled_checkout_currency = isset(Yii::app()->params['settings']['multicurrency_enabled_checkout_currency'])?Yii::app()->params['settings']['multicurrency_enabled_checkout_currency']:false;
				$enabled_force = $multicurrency_enabled==true? ($enabled_checkout_currency==1?true:false) :false;
				
				$use_currency_code = $data->use_currency_code;				
				if($enabled_force){
					 if($force_result = CMulticurrency::getForceCheckoutCurrency($data->payment_code,$use_currency_code)){					 					 
						$use_currency_code = $force_result['to_currency'];
						$total = Price_Formatter::convertToRaw($total*$force_result['exchange_rate'],2);
					 }
				}			  						
		         		        
		        $api = new Api($credentials['attr1'], $credentials['attr2']);
		        $data->use_currency_code = "INR";
		        
		        $order  = $api->order->create([
		          'receipt'=>$order_uuid,
		          'amount'=>($total*100),
		          'currency'=>$data->use_currency_code,
		          'notes'=>array(
		            'order_uuid'=>$order_uuid,	            
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
				   'amount'=>($total*100),
				  'currency'=>$data->use_currency_code,
				  'name'=>$merchant->restaurant_name,
				  'description'=>$payment_description,
				  'order_id'=>$order->id,				  
				  'customer_id'=>$model?$model->meta4:''
				);
				
				$this->_controller->code = 1;
				$this->_controller->msg = "ok";
				$this->_controller->details = $options;				

            } else $this->_controller->msg = t("Order id not found");
          
		} catch (Exception $e) {
			$this->_controller->msg = t($e->getMessage());							
		}			
		$this->_controller->responseJson();
    }
}