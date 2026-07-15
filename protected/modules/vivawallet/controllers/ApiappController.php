<?php
class ApiappController extends SiteCommon
{	
	const CONSTANT_PAYMENTCODE = 'vivawallet';

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
				$error = t("Transaction is empty");
				$redirect = Yii::app()->createAbsoluteUrl(self::CONSTANT_PAYMENTCODE."/apiapp/failed",array(
					'message'=>$error
				));					
				$this->redirect($redirect);
				Yii::app()->end();
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

					$this->code = 1;
				    $this->msg = t("Payment successful. please wait while we redirect you.");

					$redirect = Yii::app()->createAbsoluteUrl( self::CONSTANT_PAYMENTCODE."/apiapp/successful",array(
						'order_uuid'=>$data->order_uuid,
						'message'=>$this->msg
					));					
					$this->redirect($redirect);
					Yii::app()->end();				
				} else $this->msg = t("Payment tokens not found");
			} else $error = t("Order code not found");
		} catch (Exception $e) {
			$error =  $e->getMessage();
		}		
				
		$redirect = Yii::app()->createAbsoluteUrl(self::CONSTANT_PAYMENTCODE."/apiapp/failed",array(
			'message'=>$error
		));					
		$this->redirect($redirect);
		Yii::app()->end();	
	}

    public function actionfailed()
	{
		$message = Yii::app()->input->get('message');
		$message = !empty($message)?$message:t("Payment has failed");
		echo $message;
	}

	public function actionsuccessful()
	{
		$message = Yii::app()->input->get('message');
		echo $message;
	}

	public function actioncancel()
	{
		echo "payment cancelled";
	}

}
// end class