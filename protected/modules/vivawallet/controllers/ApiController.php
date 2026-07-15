<?php
class ApiController extends SiteCommon
{
	
	public function beforeAction($action)
	{								
		$method = Yii::app()->getRequest()->getRequestType();
		if($method!="POST"){
			//return false;
		}
		
		if(Yii::app()->user->isGuest){
			return false;
		}
				
		Price_Formatter::init();	        
		if($method=="PUT"){            
			$this->data = Yii::app()->input->xssClean(json_decode(file_get_contents('php://input'), true));
		} else $this->data = Yii::app()->input->xssClean($_POST);				
		
		return true;
	}

	public function actioncheckout()
	{
		try {

			$merchant_id = isset($this->data['merchant_id'])?$this->data['merchant_id']:0;		
			$payment_code = isset($this->data['payment_code'])?$this->data['payment_code']:'';		
			$merchant_type = isset($this->data['merchant_type'])?$this->data['merchant_type']:'';
			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';
			$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';		
			$payment_uuid = isset($this->data['payment_uuid'])?$this->data['payment_uuid']:'';
			
            $credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,2);                        
            $credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';         			
            $is_live = isset($credentials['is_live'])?intval($credentials['is_live']):0;
			$client_id = isset($credentials['attr1'])?trim($credentials['attr1']):'';
            $secret = isset($credentials['attr2'])?trim($credentials['attr2']):'';
            $soure_code = isset($credentials['attr3'])?trim($credentials['attr3']):'';

			$data = COrders::get($order_uuid);
			$merchant = CMerchantListingV1::getMerchant($data->merchant_id);

			$total = floatval(Price_Formatter::convertToRaw($data->total));
            $payment_description = t("Payment to merchant [merchant]. Order#[order_id]",
			array('[merchant]'=>$merchant->restaurant_name,'[order_id]'=>$data->order_id ));
			
			$customer = ACustomer::get($data->client_id);      

			$country_code='';
                $local_id = CommonUtility::getCookie(Yii::app()->params->local_id);			
			    $local_info = CMaps::locationDetails($local_id,'');
                if($local_info){                    
                    $country_code = isset($local_info['address'])?$local_info['address']['country_code']:'';
                }                                

			$params = [
				'amount'              => ($total*100),
				'customerTrns'        => $payment_description,
				'customer'            => [
					'email'       => $customer->email_address,
					'fullName'    => $customer->first_name." ".$customer->last_name,
					'phone'       => $customer->contact_phone,
					'countryCode' =>  !empty($country_code)?$country_code:'US',
					'requestLang' => 'en-GB'
				],
				'paymentTimeout'      => 1800,
				'preauth'             => false,
				'allowRecurring'      => false,
				'maxInstallments'     => 12,
				'paymentNotification' => true,
				'tipAmount'           => 1,
				'disableExactAmount'  => false,
				'disableCash'         => false,
				'disableWallet'       => false,    				
				'sourceCode'          => $soure_code,
				'merchantTrns'        => $order_uuid
			];     
						
			$tokens = PaymentVivaWallet::getTokens($client_id,$secret,$is_live);                
			$order_code = PaymentVivaWallet::SmartCheckout($params,$tokens,$is_live);   
            $redirect_url = PaymentVivaWallet::getRedirectURL($is_live);                                
            $redirect_url.="?ref=$order_code";

			$order_model = new AR_ordernew_meta();
			$order_model->order_id = $data->order_id;
			$order_model->meta_name = "vivawallet_tokens";
			$order_model->meta_value = json_encode([
				'tokens'=>$tokens,
				'is_live'=>$is_live,
				'cart_uuid'=>$cart_uuid,
				'payment_uuid'=>$payment_uuid,				
				'soure_code'=>$soure_code
			]);
			$order_model->save();

			$order_model = new AR_ordernew_meta();
			$order_model->order_id = $data->order_id;
			$order_model->meta_name = "vivawallet_ordercode";
			$order_model->meta_value = $order_code;
			$order_model->save();

			$this->code = 1;
			$this->msg = "OK";
			$this->details = [
				'redirect'=>$redirect_url
			];

		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());							
		}			
		$this->responseJson();	
	}

	
	public function actionverifypayment()
	{
		$error = '';
		try {
						
			$redirectURl = false;
            $transaction_uuid = Yii::app()->input->get('t');
            $order_code = Yii::app()->input->get('s');
            $eventId = Yii::app()->input->get('eventId');
            $eci = Yii::app()->input->get('eci');

			if(empty($transaction_uuid)){
				die();
			}
			
			$model = AR_ordernew_meta::model()->find("meta_name=:meta_name AND meta_value=:meta_value",[
				':meta_name'=>"vivawallet_ordercode",
				':meta_value'=>$order_code
			]);
			if($model){
				$meta_model = AR_ordernew_meta::model()->find("order_id=:order_id AND meta_name=:meta_name",[
					':order_id'=>$model->order_id,
					':meta_name'=>'vivawallet_tokens'
				]);
				$data = COrders::getByID($model->order_id);
				if($meta_model && $data){
					$meta = json_decode($meta_model->meta_value,true);	
					$tokens = isset($meta['tokens'])?$meta['tokens']:'';
					$is_live = isset($meta['is_live'])?$meta['is_live']:'';								
					$cart_uuid = isset($meta['cart_uuid'])?$meta['cart_uuid']:'';
					$payment_uuid = isset($meta['payment_uuid'])?$meta['payment_uuid']:'';	
					
					$resp = PaymentVivaWallet::retrieveTransaction($tokens,$transaction_uuid,$is_live);					
					$transaction_id = $transaction_uuid;

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
					
					$this->redirect(Yii::app()->createUrl('orders/index',[
						'order_uuid'=>$data->order_uuid
					]));
					Yii::app()->end();

				} else $this->msg = t("Payment tokens not found");
			} else $error = t("Order code not found");

		} catch (Exception $e) {
			$error =  $e->getMessage();
		}		
				
		$redirect = Yii::app()->createAbsoluteUrl("account/checkout",array(
			'error'=>$error
		));				
		$this->redirect($redirect);		
	}

	public function actionfailed()
	{		
		$transaction_uuid = Yii::app()->input->get('t');
		$order_code = Yii::app()->input->get('s');
		$eventId = Yii::app()->input->get('eventId');
		$eci = Yii::app()->input->get('eci');
		
		$error = t('Payment failed');
		$redirect = Yii::app()->createAbsoluteUrl("account/checkout",array(
			'error'=>$error
		));				
		$this->redirect($redirect);		
	}

}
// end class