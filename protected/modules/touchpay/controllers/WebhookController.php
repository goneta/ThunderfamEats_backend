<?php
class WebhookController extends SiteCommon
{	
	const CONSTANT_PAYMENTCODE = 'touchpay';

	public function beforeAction($action)
	{								
        Yii::app()->setImport(array(			
            'application.modules.kotak.components.*',
       ));

		$method = Yii::app()->getRequest()->getRequestType();
						
		Price_Formatter::init();	        
		if($method=="PUT"){            
			$this->data = Yii::app()->input->xssClean(json_decode(file_get_contents('php://input'), true));
		} else $this->data = Yii::app()->input->xssClean($_POST);				
		
		return true;
	}  

    public function actionIndex()
    {
        try {
            $paymentMode = Yii::app()->request->getQuery('payment_mode');
            $paidSum = Yii::app()->request->getQuery('paid_sum');
            $paidAmount = Yii::app()->request->getQuery('paid_amount');
            $paymentToken = Yii::app()->request->getQuery('payment_token');
            $paymentStatus = Yii::app()->request->getQuery('payment_status');
            $commandNumber = Yii::app()->request->getQuery('command_number');
            $paymentValidationDate = Yii::app()->request->getQuery('payment_validation_date');
            $model = AR_ordernew_meta::model()->find("meta_name=:meta_name AND meta_value=:meta_value",[
				':meta_name'=>"touchpay_ordercode",
				':meta_value'=>$commandNumber
			]);
 
            if($model){
                $meta_model = AR_ordernew_meta::model()->find("order_id=:order_id AND meta_name=:meta_name",[
					':order_id'=>$model->order_id,
					':meta_name'=>'touchpay_tokens'
				]);
                $data = COrders::getByID($model->order_id);

                $payment_status = $data->payment_status;
                
                if($meta_model && $data && $data->payment_status=="unpaid" ){
                    $meta = json_decode($meta_model->meta_value,true);	                    
					$cart_uuid = isset($meta['cart_uuid'])?$meta['cart_uuid']:'';
					$payment_uuid = isset($meta['payment_uuid'])?$meta['payment_uuid']:'';	

                    $transaction_id = $paymentToken;       
                    
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
					$model->trans_amount = $paidAmount;
					$model->currency_code = $data->use_currency_code;
					$model->payment_reference = $transaction_id;
					$model->status = $paymentStatus;
					$model->reason = $paymentValidationDate;
					$model->payment_uuid = $payment_uuid;
					$model->save();

                    $this->code = 1; $this->msg = "Successful";
                    Yii::log( json_encode($this->msg) , CLogger::LEVEL_ERROR);

                    // $merchant_type = '';
                    // $merchant = CMerchantListingV1::getMerchant($data->merchant_id);
                    // $merchant_type = $merchant->merchant_type;                    
                    // $credentials = CPayments::getPaymentCredentials($data->merchant_id,$data->payment_code,$merchant_type);                             
                    // $credentials = isset($credentials[$data->payment_code])?$credentials[$data->payment_code]:'';         			            
                    // $is_live = isset($credentials['is_live'])?intval($credentials['is_live']):0;                    
                    // $merchantid = isset($credentials['attr5'])?trim($credentials['attr5']):'';
                    // $api_key = isset($credentials['attr6'])?trim($credentials['attr6']):'';
                    
                    // $auth_token = base64_encode("$merchantid:$api_key");
                    // $resp = PaymentVivaWallet::verificationToken($auth_token,$is_live);                    
                    // header('Content-type: application/json');
                    // echo CJSON::encode($resp);
                    Yii::app()->end();                    

                } else $this->msg = t("Payment tokens not found =>$payment_status");
            } else $this->msg = t("Order code not found");         
        } catch (Exception $e) {
			$this->msg = t($e->getMessage());        
		}			
        Yii::log( json_encode($this->msg) , CLogger::LEVEL_ERROR);
        $this->responseJson();		
    }
    
}
// end class