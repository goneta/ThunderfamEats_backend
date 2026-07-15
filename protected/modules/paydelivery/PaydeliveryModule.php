<?php
class PaydeliveryModule extends CWebModule
{	
	public function init()
	{
		$this->setImport(array(			
			'paydelivery.components.*',
			'paydelivery.models.*'
		));
	}
		
	public function beforeControllerAction($controller, $action)
	{									
		if(parent::beforeControllerAction($controller, $action))
		{
			// this method is called before any module controller action is performed
			// you may place customized code here									
			return true;
		}
		else
			return false;
	}
	
	public function paymentInstructions()
	{
		return array(
		  'method'=>"offline",
		  'redirect'=>''
		);
	}
	
	public function savedTransaction($data)
	{		
		$order = AR_ordernew::model()->find('order_id=:order_id', 
		array(':order_id'=>$data->order_id)); 
		if($order){
			$order->scenario = "new_order";
			$order->status = COrders::newOrderStatus();
			$order->cart_uuid = $data->cart_uuid;
	    	$order->save();	
		}
		        
		$payment_ref = CommonUtility::generateToken("{{ordernew_transaction}}",'payment_reference',
		CommonUtility::generateAplhaCode(10) );
		
		$model = new AR_ordernew_transaction;
		$model->order_id = $data->order_id;
		$model->merchant_id = $data->merchant_id;
		$model->client_id = $data->client_id;
		$model->payment_code = $data->payment_code;
		if($order->amount_due>0){
			$model->trans_amount = $order->amount_due;
		} else $model->trans_amount = $data->total;		
		
		$model->currency_code = $order->use_currency_code;
		$model->to_currency_code = $order->base_currency_code;
		$model->exchange_rate = $order->exchange_rate;
		$model->admin_base_currency = $order->admin_base_currency;
		$model->exchange_rate_merchant_to_admin = $order->exchange_rate_merchant_to_admin;
		$model->exchange_rate_admin_to_merchant = $order->exchange_rate_admin_to_merchant;
		
		$model->payment_reference = $payment_ref;
		$model->save();		

		if($payments = CPayments::getPaymentMethod($data->payment_uuid, $model->client_id )){			
			$payment_id = isset($payments['reference_id'])?intval($payments['reference_id']):0;		  
			try{
				$card = CPayments::getPayondelivery($payment_id);				
				$card_model=new AR_ordernew_trans_meta;
				$card_model->transaction_id = $model->transaction_id;		   			
				$card_model->order_id = $model->order_id;
				$card_model->meta_name = 'payment_name';
				$card_model->meta_value = $card->payment_name;				
				$card_model->save();
			} catch (Exception $e) {
			}
		}
	}

	public function delete($data)
	{		

	}
}
/*end class*/