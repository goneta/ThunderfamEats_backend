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
			$agency_code = isset($credentials['attr1'])?trim($credentials['attr1']):'';
            $secure_code = isset($credentials['attr7'])?trim($credentials['attr7']):'';
            $soure_code = isset($credentials['attr3'])?trim($credentials['attr3']):'';

			$data = COrders::get($order_uuid);
			$order_meta = COrders::getAttributesAll($data->order_id,['place_id']);
			$merchant = CMerchantListingV1::getMerchant($data->merchant_id);

			$total = floatval(Price_Formatter::convertToRaw($data->total));
            $payment_description = t("Payment to merchant [merchant]. Order#[order_id]",
			array('[merchant]'=>$merchant->restaurant_name,'[order_id]'=>$data->order_id ));
			$client_city = isset($order_meta['place_id'])?CClientAddress::getAddress($order_meta['place_id'],$data->client_id):"";
			$customer = ACustomer::get($data->client_id);      
			$city = isset($client_city['address']['city'])&&!empty($client_city['address']['city'])?$client_city['address']['city']:"";
			$country_code='';
                $local_id = CommonUtility::getCookie(Yii::app()->params->local_id);			
			    $local_info = CMaps::locationDetails($local_id,'');
                if($local_info){                    
                    $country_code = isset($local_info['address'])?$local_info['address']['country_code']:'';
                }                                

			$params = [
				"order_number"=>$data->order_uuid,
				"agency_code"=>$agency_code,
				"secure_code"=>$secure_code,
				"domain_name"=>$_SERVER['SERVER_NAME'],
				"url_redirection_success"=>"https://thunderfameats.com/orders/index?order_uuid=$order_uuid",
				"url_redirection_failed"=>"https://thunderfameats.com/account/checkout",
				"amount"=>($total),
				"email"=>$customer->email_address,
				"clientFirstName"=>$customer->first_name,
				"clientLastName"=>$customer->last_name,
				"clientPhone"=>$customer->contact_phone,
				"city"=>$city,
			];     
						
			// $tokens = PaymentVivaWallet::getTokens($client_id,$secret,$is_live);                
			// $order_code = PaymentVivaWallet::SmartCheckout($params,$tokens,$is_live);   
            // $redirect_url = PaymentVivaWallet::getRedirectURL($is_live);                                
            // $redirect_url.="?ref=$order_code";

			$order_model = new AR_ordernew_meta();
			$order_model->order_id = $data->order_id;
			$order_model->meta_name = "touchpay_tokens";
			$order_model->meta_value = json_encode([
				'cart_uuid'=>$cart_uuid,
				'payment_uuid'=>$payment_uuid,				
				'soure_code'=>$soure_code
			]);
			$order_model->save();

			$order_model = new AR_ordernew_meta();
			$order_model->order_id = $data->order_id;
			$order_model->meta_name = "touchpay_ordercode";
			$order_model->meta_value = $order_uuid;
			$order_model->save();

			$this->code = 1;
			$this->msg = "OK";
			$this->details = [
				'data'=>$params
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
            $order_uuid = Yii::app()->input->get('order_uuid');
            $cart_uuid = Yii::app()->input->get('cart_uuid');
            $num_transaction_from_gu = Yii::app()->input->get('num_transaction_from_gu');
            $num_command = Yii::app()->input->get('num_command');
            $amount = Yii::app()->input->get('amount');
            $codeInput = Yii::app()->input->get('errorCode');
			$errorCode = "";
			$methodCode = "";
			if (preg_match('/^(\d+)#!\/(\w+)$/', $codeInput, $matches)) {
				$errorCode = (int)$matches[1];
				$methodCode = $matches[2];
			}

			if(empty($order_uuid)){
				die();
			}
			
			if($errorCode < 300){
					
				$model = AR_ordernew::model()->find("order_uuid=:order_uuid",[
					':order_uuid'=>$order_uuid
				]);
				if($model){
					$data = COrders::getByID($model->order_id);
					if($data){
						
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
						$model->payment_reference = $num_transaction_from_gu;
						$model->status = CPayments::paidStatus();
						$model->reason = '';
						$model->payment_uuid = "touchpay";
						$model->save();
						
						$this->redirect(Yii::app()->createUrl('orders/index',[
							'order_uuid'=>$data->order_uuid
						]));
						Yii::app()->end();

					} else $this->msg = t("Payment tokens not found");
				} else $error = t("Order code not found");
			}
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
		$order_uuid = Yii::app()->input->get('order_uuid');
		$cart_uuid = Yii::app()->input->get('cart_uuid');
		
		$error = t('Payment failed');
		$redirect = Yii::app()->createAbsoluteUrl("account/checkout",array(
			'error'=>$error
		));				
		$this->redirect($redirect);		
	}

}
// end class