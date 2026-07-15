<?php
require 'razorpay/vendor/autoload.php';
require 'php-jwt/vendor/autoload.php';
use Razorpay\Api\Api;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class RazorpayapiController extends SiteCommon
{
	
	public function beforeAction($action)
	{								
		$method = Yii::app()->getRequest()->getRequestType();
		if($method!="PUT"){
			return false;
		}
		
		if(Yii::app()->user->isGuest){
			return false;
		}
				
		Price_Formatter::init();
		$method = Yii::app()->getRequest()->getRequestType();
		if($method=="PUT"){
			$this->data = Yii::app()->input->xssClean(json_decode(file_get_contents('php://input'), true));
		} else $this->data = Yii::app()->input->xssClean($_POST);				
		
		return true;
	}
	
	public function actionIndex()
	{
		//
	}
	
	public function actionCreateCustomer()
	{
		try {
										
			$merchant_id = isset($this->data['merchant_id'])?$this->data['merchant_id']:0;		
			$payment_code = isset($this->data['payment_code'])?$this->data['payment_code']:'';		
			$merchant_type = isset($this->data['merchant_type'])?$this->data['merchant_type']:'';
			$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
								
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
					$this->msg[] = $e->getMessage();
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
		    		   		    		  
		    $this->code = 1;
		    $this->msg = "OK";
		    $this->details = array(		      
		      'customer_id'=>$customer_id
		    );
									 									
	    } catch (Exception $e) {
			$this->msg[] = t($e->getMessage());							
		}			
		$this->responseJson();	
	}
	
	public function actionCreateOrder()
	{
		try {
					
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
		        $data->use_currency_code = $use_currency_code;				
		        
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
				
				$this->code = 1;
				$this->msg = "ok";
				$this->details = $options;				
				
			} else $this->msg = t("Order id not found");
               
		 } catch (Exception $e) {
			$this->msg[] = t($e->getMessage());							
		}			
		$this->responseJson();	
	}
	
	public function actionverifypayment()
	{
		try {
					  
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

			   $exchange_rate = $data->exchange_rate>0?$data->exchange_rate:1;			  
		       $total = Price_Formatter::convertToRaw( ($data->total*$exchange_rate) );

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
							   
			   /*CAPTURE PAYMENT*/
			   //$amount = Price_Formatter::convertToRaw($data->total);			   
			   $capture = $api->payment->fetch($razorpay_payment_id)->capture(array(
			     'amount'=>($total*100),
			     'currency' => $use_currency_code
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
			   //$model->trans_amount = $data->total;
			   $model->trans_amount = $data->amount_due>0? $data->amount_due: $data->total;
			   $model->currency_code = Price_Formatter::$number_format['currency_code'];				
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
			   }
			   
			   $this->code = 1;
			   $this->msg = t("Payment successful. please wait while we redirect you.");
			
			   $redirect = Yii::app()->createAbsoluteUrl("orders/index",array(
			    'order_uuid'=>$data->order_uuid
			   ));					
			   $this->details = array(  					  
			    'redirect'=>$redirect
			   );
		   	
		   } else $this->msg = t("Order id not found");
		   
		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());							
		}			
		$this->responseJson();	
	}

	public function actioncreateneworder()
	{
		try {

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
					
			$this->code = 1;
			$this->msg = "ok";
			$this->details = $options;

		} catch (Exception $e) {
			$this->msg = t($e->getMessage());							
		}			
		$this->responseJson();	
	}

	public function actionprocesspayment()
	{
		try {
			
			$data = isset($this->data['data'])?$this->data['data']:'';
			$razorpay_payment_id = isset($this->data['razorpay_payment_id'])?$this->data['razorpay_payment_id']:'';
		    $razorpay_order_id = isset($this->data['razorpay_order_id'])?$this->data['razorpay_order_id']:'';
		    $razorpay_signature = isset($this->data['razorpay_signature'])?$this->data['razorpay_signature']:'';

			$jwt_key = new Key(CRON_KEY, 'HS256');
			$decoded = (array) JWT::decode($data, $jwt_key);  					
			
			$payment_code = isset($decoded['payment_code'])?$decoded['payment_code']:'';
			$payment_name = isset($decoded['payment_name'])?$decoded['payment_name']:'';
			$merchant_id = isset($decoded['merchant_id'])?$decoded['merchant_id']:'';
			$merchant_type = isset($decoded['merchant_type'])?$decoded['merchant_type']:'';
			$payment_description = isset($decoded['payment_description'])?$decoded['payment_description']:'';
			$payment_description_raw = isset($decoded['payment_description_raw'])?$decoded['payment_description_raw']:'';
			$transaction_description_parameters = isset($decoded['transaction_description_parameters'])?$decoded['transaction_description_parameters']:'';
			$amount = isset($decoded['amount'])?$decoded['amount']:0;						
			$transaction_amount = isset($decoded['transaction_amount'])?$decoded['transaction_amount']:0;			
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
			   $attributes  = array(
			       'razorpay_signature'  => $razorpay_signature,
			       'razorpay_payment_id'  => $razorpay_payment_id,
			       'razorpay_order_id' => $razorpay_order_id
			);			   
			$api->utility->verifyPaymentSignature($attributes);

			$capture = $api->payment->fetch($razorpay_payment_id)->capture(array(
				'amount'=>($amount*100),
				'currency' => $currency_code
			));
			
			$transaction_id = $razorpay_payment_id;

			if($payment_type=="add_funds"){
			   $card_id = CWallet::createCard( Yii::app()->params->account_type['digital_wallet'],Yii::app()->user->id);				   
			   $meta_array = [];
			   try {
				  $date_now = date("Y-m-d");
				  $bonus = AttributesTools::getDiscountToApply($transaction_amount,CDigitalWallet::transactionName(),$date_now);
				  $transaction_amount = floatval($transaction_amount)+floatval($bonus);				  
				  $meta_array[]=[
                    'meta_name'=>'topup_bonus',
                    'meta_value'=>floatval($bonus)
                  ];
				  $payment_description_raw = "Funds Added via {payment_name} with {bonus} Bonus";
				  $transaction_description_parameters = [
					'{payment_name}'=>$payment_name,
					'{bonus}'=>Price_Formatter::formatNumber($bonus),
				  ];
				} catch (Exception $e) {}

			   CPaymentLogger::afterAdddFunds($card_id,[				    
				    'transaction_description'=>$payment_description_raw,
					'transaction_description_parameters'=>$transaction_description_parameters,
					'transaction_type'=>'credit',
					'transaction_amount'=>$transaction_amount,
					'status'=>CPayments::paidStatus(),
					'reference_id'=>$reference_id,
					'reference_id1'=>CDigitalWallet::transactionName(),
					'merchant_base_currency'=>$currency_code,
					'admin_base_currency'=>$currency_code,
					'meta_name'=>'topup',
					'meta_value'=>$card_id,
					'meta_array'=>$meta_array,			
			   ]);
			} elseif ($payment_type=="file_storage") {
				# code...
			}
			
			Price_Formatter::init($currency_code);
 
			$this->code = 1;
			$this->msg = t("Payment successful. please wait while we redirect you.");
			$this->details = array(  					  
			   'payment_name'=>$payment_name,
			   'transaction_id'=>$transaction_id,
			   'amount'=>Price_Formatter::formatNumber($amount),
			   'amount_raw'=>$amount,
			   'transaction_date'=>Date_Formatter::dateTime(date('c'))
			);

		} catch (Exception $e) {
			$this->msg = t($e->getMessage());							
		}					
		$this->responseJson();	
	}
	
}
/*end index*/