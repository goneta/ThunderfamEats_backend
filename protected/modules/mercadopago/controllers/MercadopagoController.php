<?php
require 'php-jwt/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class MercadopagoController extends SiteCommon
{
	
	public function beforeAction($action)
	{								
		$method = Yii::app()->getRequest()->getRequestType();
		if($method!="PUT"){
			//return false;
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
	
	private function searchCustomer($email_address='',$access_token=array())
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, 'https://api.mercadopago.com/v1/customers/search?email='.$email_address);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		
		$headers = array();
		$headers[] = 'Authorization: Bearer '.$access_token;
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		$result = curl_exec($ch);
		if (curl_errno($ch)) {
		    //echo 'Error:' . curl_error($ch);
		    throw new Exception( 'Error:' . curl_error($ch) );
		}
		curl_close($ch);
		
		if($json=json_decode($result,true)){			
			if($json['paging']['total']>0){
				foreach ($json['results'] as $items) {
					$customer_id = $items['id'];
					break;
				}
				return $customer_id;
			}
		} 
		throw new Exception( 'no results' );
	}
	
	public function actioncreateCustomer()
	{
		try {
						
		    $merchant_id = isset($this->data['merchant_id'])?$this->data['merchant_id']:0;		
			$payment_code = isset($this->data['payment_code'])?$this->data['payment_code']:'';		
			$merchant_type = isset($this->data['merchant_type'])?$this->data['merchant_type']:'';
			
			$credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,$merchant_type);
			$credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';
			$is_live = isset($credentials['is_live'])?intval($credentials['is_live']):0;
						
			$email_address = Yii::app()->user->email_address;
			if($credentials['is_live']<=0){
				$email_address = "test".Yii::app()->user->id."_".$email_address;
			}
						
			$acess_token = isset($credentials['attr2'])?trim($credentials['attr2']):'';
			require_once 'mercadopago/vendor/autoload.php';
			MercadoPago\SDK::setAccessToken($acess_token);
			
			$customer_id = '';
			try {
				$customer_id = $this->searchCustomer($email_address,$acess_token);
			} catch (Exception $e) {
				//
			}
						
			if(!empty($customer_id) && strlen($customer_id)>5){
				// already created
			} else {
				$customer = new MercadoPago\Customer();
			    $customer->email = $email_address ;
			    $customer->first_name = Yii::app()->user->first_name ;
			    $customer->last_name = Yii::app()->user->last_name ;
			    $customer->save();			
			    $customer_id = $customer->id;
			    if(empty($customer_id)){
			    	if(isset($customer->error->causes)){
						foreach ($customer->error->causes as $items) {
							$this->msg[] = $items->description;
						}
					} else $this->msg[] = t("An error has occured." . json_encode($customer->error));
			    }
			}
			
			if(!empty($customer_id) && strlen($customer_id)>5){
				$this->code = 1;
				$this->msg = "OK";
				$this->details = array(
				  'customer_id'=>$customer_id,
				  'test_card'=>false
				);
				if(DEMO_MODE){
					$this->details['test_card'] = array(
					  'card_number'=>'5031755734530604',
					  'expiry'=>'11/2022',
					  'cvv'=>'123',
					  'identification_type'=>'DNI',
					  'identification_number'=>'12334566'
					);
				}
			}
								    
		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());							
		}			
		$this->responseJson();	
	}
	
	public function actionAddCard()
	{
		try {			
			
			$merchant_id = isset($this->data['merchant_id'])?$this->data['merchant_id']:0;		
			$payment_code = isset($this->data['payment_code'])?$this->data['payment_code']:'';		
			$merchant_type = isset($this->data['merchant_type'])?$this->data['merchant_type']:'';
			$card_name = isset($this->data['card_name'])?$this->data['card_name']:'';
			$email_address = isset($this->data['email_address'])?$this->data['email_address']:'';
			$card_token = isset($this->data['id'])?$this->data['id']:'';
			$customer_id = isset($this->data['customer_id'])?$this->data['customer_id']:'';
						
			$credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,$merchant_type);
			$credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';
			$is_live = isset($credentials['is_live'])?intval($credentials['is_live']):0;
			
			$acess_token = isset($credentials['attr2'])?trim($credentials['attr2']):'';
			require_once 'mercadopago/vendor/autoload.php';
			MercadoPago\SDK::setAccessToken($acess_token);
						
			if(!empty($customer_id) && strlen($customer_id)>5){
				
				$card = new MercadoPago\Card();
                $card->token = $card_token;
                $card->customer_id = $customer_id;
                $card->save();                      
                if($card->id>0){  
                	
                    $mask_card = CommonUtility::mask("111111111111".$card->last_four_digits);
                                        
				    $model_method = new AR_client_payment_method;
				    $model_method->client_id = intval(Yii::app()->user->id);
				    $model_method->payment_code = $payment_code;
				    $model_method->as_default = 1;				    
				    $model_method->attr1 = $card->issuer->name;
				    $model_method->attr2 = $mask_card;			
				    $model_method->merchant_id = isset($credentials['merchant_id'])? intval($credentials['merchant_id']) :0;
				    
				    $model_method->method_meta = array(
				      array(
				        'meta_name'=>'customer_id',
				        'meta_value'=>$customer_id,
				        'date_created'=>CommonUtility::dateNow(),
				      ),
				      array(
				        'meta_name'=>'card_id',
				        'meta_value'=>$card->id,
				        'date_created'=>CommonUtility::dateNow(),
				      ),
				      array(
				        'meta_name'=>'is_live',
				        'meta_value'=>$is_live,
				        'date_created'=>CommonUtility::dateNow(),
				      ),
				    );
				    
				    if($model_method->save()){
				    	$this->code = 1; 
				    	$this->msg = "OK";			    	
				    } else $this->msg = CommonUtility::parseError( $model_method->getErrors());	
	                    
                } else {
                	if(isset($card->error->causes)){
						foreach ($card->error->causes as $items) {
							$this->msg[] = $items->description;
						}
					} else $this->msg[] = t("An error has occured." . json_encode($customer->error));
                }
			} else $this->msg[] = t("Invalid customer id");
		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());							
		}			
		$this->responseJson();	
	}
	
	public function actiongetCardID()
	{
		try {
			
			$payment_uuid = isset($this->data['payment_uuid'])?$this->data['payment_uuid']:'';			
			
			$data = AR_client_payment_method::model()->find('payment_uuid=:payment_uuid',
			array(':payment_uuid'=>$payment_uuid));	  
			
			if($data){
			   $payment_method = CPayments::getPaymentMethodMeta($payment_uuid, Yii::app()->user->id );
			   $this->code = 1;
			   $this->msg = "OK";
			   $this->details = array(
			     'card_number'=>$data->attr2,			     
			     'card_id'=>isset($payment_method['card_id'])?$payment_method['card_id']:'',
			     'is_live'=>isset($payment_method['is_live'])?$payment_method['is_live']:'',
			   );
			   
			} else $this->msg = t("Payment id not fond");
			
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());							
		}			
		$this->responseJson();	
	}
	
	public function actioncapturePayment()
	{
		try {
			
			$merchant_id = isset($this->data['merchant_id'])?$this->data['merchant_id']:0;		
			$payment_code = isset($this->data['payment_code'])?$this->data['payment_code']:'';		
			$merchant_type = isset($this->data['merchant_type'])?$this->data['merchant_type']:'';
			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';
			$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
			
			$payment_uuid = isset($this->data['payment_uuid'])?$this->data['payment_uuid']:'';
			$card_token = isset($this->data['card_token'])?$this->data['card_token']:'';			
			
			$credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,$merchant_type);
			$credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';
			$is_live = isset($credentials['is_live'])?intval($credentials['is_live']):0;
			
			$data = AR_ordernew::model()->find('order_uuid=:order_uuid',array(':order_uuid'=>$order_uuid));

			if($data){
				$payment_method = CPayments::getPaymentMethodMeta($payment_uuid, Yii::app()->user->id );
				$customer_id = isset($payment_method['customer_id'])?$payment_method['customer_id']:'';
				
				$acess_token = isset($credentials['attr2'])?trim($credentials['attr2']):'';
				require_once 'mercadopago/vendor/autoload.php';
				MercadoPago\SDK::setAccessToken($acess_token);				
				$payment = new MercadoPago\Payment();
								
				$exchange_rate = $data->exchange_rate>0?$data->exchange_rate:1;			  		        	
				if($data->amount_due>0){
					$total = floatval(Price_Formatter::convertToRaw( ($data->amount_due*$exchange_rate) ));	
				 } else $total = floatval(Price_Formatter::convertToRaw( ($data->total*$exchange_rate) ));			  
				
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
								
				$payment->transaction_amount = $total;
				$payment->token = $card_token;
				$payment->installments = 1;
				$payment->payer = array(
				    "type" => "customer",
				    "id" => $customer_id
				);				
				$payment->save();			
				
				if($payment->status_detail=="accredited" || $payment->status_detail=="pending_contingency"
				|| $payment->status_detail=="pending_review_manual"  ){
					
					/*dump($payment->id);
					dump($payment->status);				
					dump($payment->status_detail);*/	
					
					$transaction_id = $payment->id;
					$payment_status = CPayments::paidStatus();
					if($payment->status!="approved"){
						$payment_status = CPayments::umpaidStatus();
					}
					
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
				    
					$model->currency_code = $data->use_currency_code;
					$model->to_currency_code = $data->base_currency_code;
					$model->exchange_rate = $data->exchange_rate;
					$model->admin_base_currency = $data->admin_base_currency;
					$model->exchange_rate_merchant_to_admin = $data->exchange_rate_merchant_to_admin;
					$model->exchange_rate_admin_to_merchant = $data->exchange_rate_admin_to_merchant;

				    $model->payment_reference = $transaction_id;
				    $model->status = $payment_status;
				    $model->reason = '';
				    $model->payment_uuid = $payment_uuid;
				    if($model->save()){				
				    	
				    	/*INSERT NOTES FOR PAYMENT*/
						$params = array(  
						   array('transaction_id'=>$model->transaction_id,'order_id'=>$data->order_id, 
						   'meta_name'=>'status', 'meta_value'=>$payment->status ),
						   					   
						   array('transaction_id'=>$model->transaction_id,'order_id'=>$data->order_id, 
						   'meta_name'=>'status_detail', 'meta_value'=>$payment->status_detail ),
						    
						);
						$builder=Yii::app()->db->schema->commandBuilder;
					    $command=$builder->createMultipleInsertCommand('{{ordernew_trans_meta}}',$params);
					    $command->execute();  
					    	
					    $this->code = 1;
					    if($payment->status_detail=="accredited"){
					       $this->msg = t("Payment successful. please wait while we redirect you.");
					    } else if ($payment->status_detail=="pending_contingency") {
					    	$this->msg = t("We are processing your payment. please wait while we redirect you.");
					    } else if ($payment->status_detail=="pending_review_manual") {
					    	$this->msg = t("We are processing your payment. please wait while we redirect you.");
					    } else {
					    	$this->msg = t("Payment successful. please wait while we redirect you.");
					    }
					
					    $redirect = Yii::app()->createAbsoluteUrl("orders/index",array(
					      'order_uuid'=>$data->order_uuid
					    ));					
					    $this->details = array(  					  
					      'redirect'=>$redirect
					    );					
				    } else $this->msg = CommonUtility::parseError( $model->getErrors() );
				} else {
					if(!empty($payment->status_detail)){
					   $this->msg[] = CMercadopagoError::get($payment->status_detail);
					} else {
						if(isset($payment->error->causes)){
							foreach ($payment->error->causes as $items) {
								$this->msg[] = $items->description;
							}
					    } else $this->msg[] = t("An error has occured." . json_encode($payment->error));
					}
				}
				
			} else $this->msg[] = t("Order id not found");
		
		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());							
		}			
		$this->responseJson();			
	}

	public function actionprocesspayment()
	{
		try {
			
			$data = isset($this->data['data'])?$this->data['data']:'';
			$card_token = isset($this->data['card_token'])?$this->data['card_token']:'';
			$jwt_key = new Key(CRON_KEY, 'HS256');
			$decoded = (array) JWT::decode($data, $jwt_key);  			
			
			$payment_code = isset($decoded['payment_code'])?$decoded['payment_code']:'';
			$payment_uuid = isset($decoded['payment_uuid'])?$decoded['payment_uuid']:'';
			$payment_name = isset($decoded['payment_name'])?$decoded['payment_name']:'';
			$merchant_id = isset($decoded['merchant_id'])?$decoded['merchant_id']:'';
			$merchant_type = isset($decoded['merchant_type'])?$decoded['merchant_type']:'';
			$payment_description = isset($decoded['payment_description'])?$decoded['payment_description']:'';
			$payment_description_raw = isset($decoded['payment_description_raw'])?$decoded['payment_description_raw']:'';
			$transaction_description_parameters = isset($decoded['transaction_description_parameters'])?$decoded['transaction_description_parameters']:'';
			$amount = isset($decoded['amount'])?$decoded['amount']:'';			
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
			

			$payment_method = CPayments::getPaymentMethodMeta($payment_uuid, Yii::app()->user->id );
			$customer_id = isset($payment_method['customer_id'])?$payment_method['customer_id']:'';
			$acess_token = isset($credentials['attr2'])?trim($credentials['attr2']):'';			

			require_once 'mercadopago/vendor/autoload.php';
			MercadoPago\SDK::setAccessToken($acess_token);				
			$payment = new MercadoPago\Payment();

			if($payment->status_detail=="accredited" || $payment->status_detail=="pending_contingency" || $payment->status_detail=="pending_review_manual"){

				$transaction_id = $payment->id;				

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
			} else {
				if(!empty($payment->status_detail)){
					$this->msg[] = CMercadopagoError::get($payment->status_detail);
				 } else {
					 if(isset($payment->error->causes)){
						 foreach ($payment->error->causes as $items) {
							 $this->msg[] = $items->description;
						 }
					 } else $this->msg[] = t("An error has occured." . json_encode($payment->error));
				 }
			}
		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());							
		}			
		$this->responseJson();			
	}

} 
/*end class*/