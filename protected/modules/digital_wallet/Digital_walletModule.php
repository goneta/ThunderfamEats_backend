<?php
class Digital_walletModule extends CWebModule
{	
	public function init()
	{
		$this->setImport(array(			
			'digital_wallet.components.*',
			'digital_wallet.models.*'
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
			$order->payment_status =$order->amount_due<=0 ? CPayments::paidStatus() : CPayments::partialyStatus();
	    	$order->save();	
		}			
	}

	public function refund($credentials=array(), $transaction=array(), $payment = array())
	{
		try {
			$card_id = CWallet::createCard( Yii::app()->params->account_type['digital_wallet'], $transaction->client_id );             			
			$amount = $transaction->trans_amount * $transaction->exchange_rate_merchant_to_admin ;
			$refund_amount = Price_Formatter::convertToRaw($amount);			
			
			$model = AR_wallet_transactions::model()->find("card_id=:card_id AND transaction_type=:transaction_type AND
			reference_id=:reference_id",[
				':card_id'=>$card_id,
				':transaction_type'=>"credit",
				':reference_id'=>$transaction->order_id
			]);
			if(!$model){				
				$transaction_description = $transaction->transaction_name=="partial_refund"? "Partial Refund processed for order #{{order_id}}" : "Refund processed for canceled order #{{order_id}}";
				$params = array(					  		                 
					'transaction_description'=>$transaction_description,
					'transaction_description_parameters'=>array('{{order_id}}'=>$transaction->order_id),
					'transaction_type'=>'credit',
					'transaction_amount'=>$refund_amount,
					'orig_transaction_amount'=>$refund_amount,
					'status'=>'paid',                
					'reference_id'=>$transaction->order_id,
					'reference_id1'=>CDigitalWallet::transactionName(),
					'merchant_base_currency'=>$transaction->currency_code,
					'admin_base_currency'=>$transaction->admin_base_currency,
					'exchange_rate_merchant_to_admin'=>$transaction->exchange_rate_merchant_to_admin,
					'exchange_rate_admin_to_merchant'=>$transaction->exchange_rate_admin_to_merchant,
					'meta_name'=>"refund",
					'meta_value'=>$transaction->order_id
				);            
				$transaction_id = CWallet::inserTransactions($card_id,$params);
				return array(
					'id'=>$transaction_id
				);						
		    } else {
				throw new Exception( t("Transction already exist"));
			}
		} catch (Exception $e) {			
			throw new Exception( $e->getMessage() );
		}
	}
	
}
/*end class*/