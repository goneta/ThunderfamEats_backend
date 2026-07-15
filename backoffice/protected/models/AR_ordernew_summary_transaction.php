<?php
class AR_ordernew_summary_transaction extends CActiveRecord
{	

	public $order;
	public $card_id;
	
	/**
	 * Returns the static model of the specified AR class.
	 * @return static the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{ordernew_summary_transaction}}';
	}
	
	public function primaryKey()
	{
	    return 'transaction_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'order_id'=>t("Order ID"),		    
		);
	}
	
	public function rules()
	{
		return array(
		  array('order_id,transaction_amount,transaction_type,transaction_description', 
		  'required','message'=> t( Helper_field_required ) ),
		  
		  array('transaction_description,transaction_description_parameters', 
		  'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')), 		  
		  
		  array('transaction_amount', 'numerical', 'integerOnly' => false,		  
		  'message'=>t(Helper_field_numeric)),
		  
		  array('status,transaction_uuid,transaction_description_parameters,
		  date_created,ip_address,order','safe')
		  
		);
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){
			if($this->isNewRecord){
				$this->transaction_uuid = CommonUtility::createUUID("{{ordernew_summary_transaction}}",'transaction_uuid');
				$this->transaction_date = CommonUtility::dateNow();
			} 
			$this->ip_address = CommonUtility::userIp();	
			return true;
		} else return true;
	}
	
	protected function afterSave()
	{
		parent::afterSave();

		Yii::import('ext.runactions.components.ERunActions');	
		$cron_key = CommonUtility::getCronKey();		
		
		$order = $this->order;
		
		if ( $this->scenario=="refund"){								
												
				$summary = AR_ordernew_summary_transaction::model()->findByPk( $this->transaction_id );			     
				if($summary){
					$summary->status = "process";
					$summary->save();
				}

				// $digitalwallet_enabled = isset(Yii::app()->params['settings']['digitalwallet_enabled'])?Yii::app()->params['settings']['digitalwallet_enabled']:false;
		        // $digitalwallet_enabled = $digitalwallet_enabled==1?true:false;

				// $refund_to_wallet = isset(Yii::app()->params['settings']['digitalwallet_refund_to_wallet'])?Yii::app()->params['settings']['digitalwallet_refund_to_wallet']:false;
		        // $refund_to_wallet = $refund_to_wallet==1?true:false;

				// // REFUND TO DIGITAL WALLET IF PARTIAL PAYMENT
				// if($digitalwallet_enabled && $order->amount_due>0 && $order->wallet_amount>0){
				// 	$order->payment_code = CDigitalWallet::transactionName();
				// } else if($digitalwallet_enabled && $refund_to_wallet){
				// 	$order->payment_code = CDigitalWallet::transactionName();
				// }
				
				// $model = new AR_ordernew_transaction;
				// $model->order_id = $order->order_id;
				// $model->merchant_id = $order->merchant_id;
				// $model->client_id = $order->client_id;
				// $model->payment_code = $order->payment_code;
				// $model->transaction_type = "debit";
				// $model->transaction_name = "partial_refund";
				// $model->transaction_description = "Partial refund";
				// $model->trans_amount = floatval($this->transaction_amount);
				// $model->currency_code = $order->use_currency_code;

				// $model->exchange_rate = $order->exchange_rate;
				// $model->admin_base_currency = $order->admin_base_currency;
				// $model->exchange_rate_merchant_to_admin = $order->exchange_rate_merchant_to_admin;
				// $model->exchange_rate_admin_to_merchant = $order->exchange_rate_admin_to_merchant;			
				
				// if($model->save()){
				//    $get_params = array( 
				// 	  'order_uuid'=> $order->order_uuid,
				// 	  'transaction_id'=>$model->transaction_id,
				// 	  'key'=>$cron_key,
				// 	  'language'=>Yii::app()->language
				//    );
				//    CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/processrefund?".http_build_query($get_params) );
				// }

		} elseif ( $this->scenario=="invoice" ){
										
			$model = new AR_ordernew_transaction;
			$model->order_id = $order->order_id;
			$model->merchant_id = $order->merchant_id;
			$model->client_id = $order->client_id;
			$model->payment_code = $order->payment_code;			
			$model->transaction_type = "credit";
			$model->transaction_name = "invoice";
			$model->transaction_description = "Invoice";
			$model->trans_amount = floatval($this->transaction_amount);
			$model->currency_code = $order->use_currency_code;
			
			$model->to_currency_code = $order->base_currency_code;				
			$model->exchange_rate = $order->exchange_rate;
			$model->admin_base_currency = $order->admin_base_currency;
			$model->exchange_rate_merchant_to_admin = $order->exchange_rate_merchant_to_admin;
			$model->exchange_rate_admin_to_merchant = $order->exchange_rate_admin_to_merchant;			
			
			if($model->save()){			   
			   $get_params = array( 
				  'order_uuid'=> $order->order_uuid,
				  'transaction_id'=>$model->transaction_id,
				  'key'=>$cron_key,
				  'language'=>Yii::app()->language,
			   );			   
			   CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/sendinvoice?".http_build_query($get_params) );
			}
			
			/*INSERT META*/
			$meta = new AR_ordernew_meta;
			$meta->order_id = intval($order->order_id);
			$meta->meta_name = 'order_revision';
			$meta->meta_value = 'invoice';
			$meta->save();
			
		} elseif ( $this->scenario=="less_account" ){			
			$params = array(
			  'merchant_id'=>$order->merchant_id,					  
			  'transaction_description'=>"Payment to order #{{order_id}}",
			  'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id),					  
			  'transaction_type'=>"debit",
			  'transaction_amount'=>floatval($this->transaction_amount),
			  'meta_name'=>"order",
			  'meta_value'=>$order->order_id,
			  'merchant_base_currency'=>$order->base_currency_code,
			  'admin_base_currency'=>$order->admin_base_currency,
			  'exchange_rate_merchant_to_admin'=>$order->exchange_rate_merchant_to_admin,
			  'exchange_rate_admin_to_merchant'=>$order->exchange_rate_admin_to_merchant,
			);						
			CWallet::inserTransactions($this->card_id,$params);		
			
			/*INSERT META*/
			$meta = new AR_ordernew_meta;
			$meta->order_id = intval($order->order_id);
			$meta->meta_name = 'order_revision';
			$meta->meta_value = 'less_account';
			$meta->save();
		}
		
	}

	protected function afterDelete()
	{
		parent::afterDelete();		
	}
		
}
/*end class*/