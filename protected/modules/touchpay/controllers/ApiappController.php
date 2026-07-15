<?php
class ApiappController extends SiteCommon
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
				$error = t("Transaction is empty");
				$redirect = Yii::app()->createAbsoluteUrl(self::CONSTANT_PAYMENTCODE."/apiapp/failed",array(
					'message'=>$error
				));					
				$this->redirect($redirect);
				Yii::app()->end();
			}
			
			if($errorCode < 300){

				$model = AR_ordernew::model()->find("order_uuid=:order_uuid",[
					':order_uuid'=>$order_uuid
				]);
				if($model){
					// Check if order is already paid (idempotency check)
					if($model->payment_status == CPayments::paidStatus()){
						$this->code = 1;
						$this->msg = t("Payment already processed.");
						$redirect = Yii::app()->createUrl( self::CONSTANT_PAYMENTCODE."/apiapp/success",array(
							'order_uuid'=>$order_uuid,
							'message'=>$this->msg
						));					
						$this->redirect($redirect);
						Yii::app()->end();
					}
					
					$data = COrders::getByID($model->order_id);
					if($data){
						// Update order status and payment status
						$model->scenario = "new_order";
						$model->status = COrders::newOrderStatus();
						$model->payment_status = CPayments::paidStatus();
						if(!empty($cart_uuid)){
							$model->cart_uuid = $cart_uuid;
						}
						$model->save();
						
						// Create transaction record
						$transaction_model = new AR_ordernew_transaction;
						$transaction_model->order_id = $data->order_id;
						$transaction_model->merchant_id = $data->merchant_id;
						$transaction_model->client_id = $data->client_id;
						$transaction_model->payment_code = $data->payment_code;
						$transaction_model->trans_amount = $data->total;
						$transaction_model->currency_code = $data->use_currency_code;
						$transaction_model->payment_reference = !empty($num_transaction_from_gu) ? $num_transaction_from_gu : CommonUtility::generateToken("{{ordernew_transaction}}",'payment_reference',CommonUtility::generateAplhaCode(10));
						$transaction_model->status = CPayments::paidStatus();
						$transaction_model->reason = '';
						$transaction_model->payment_uuid = self::CONSTANT_PAYMENTCODE;
						$transaction_model->save();

						$this->code = 1;
						$this->msg = t("Payment successful. please wait while we redirect you.");

						$redirect = Yii::app()->createUrl( self::CONSTANT_PAYMENTCODE."/apiapp/success",array(
							'order_uuid'=>$data->order_uuid,
							'message'=>$this->msg
						));					
						$this->redirect($redirect);
						Yii::app()->end();		
								
					} else $error = t("Payment tokens not found");
				} else $error = t("Order code not found");
			} else {
				$error = t("Payment failed with error code: ").$errorCode;
			}
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

	public function actionsuccess()
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