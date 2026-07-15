<?php
class AR_bank_deposit extends CActiveRecord
{	
    public $order_uuid, $invoice_uuid;

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
		return '{{bank_deposit}}';
	}
	
	public function primaryKey()
	{
	    return 'deposit_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'deposit_id'=>t("ID"),
            'account_name'=>t("Account name"),
            'amount'=>t("Amount"),
            'reference_number'=>t("Reference Number"),
			'proof_image'=>t("Proof of payment"),
			'transaction_ref_id'=>t("Order#")
		);
	}
	
	public function rules()
	{
		return array(
		  array('account_name,amount,status', 
		  'required','message'=> t( Helper_field_required ) ),		 

		  array('proof_image', 
		  'required','message'=> t("Proof of payment is required") ),		 

          array('proof_image', 'file', 'types'=>'jpg, gif, png,jpeg', 
		  'safe' => false , 'on'=>"insert"
		),
		  
		  array('amount', 'numerical', 'integerOnly' => false,
			'min'=>1,		  
			'tooSmall'=>t("Amount is required"),		  
			'message'=>t("Amount is required")			
		 ),
		  		  
		  array('account_name', 'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')), 		  
		  
		  array('merchant_id,reference_number,date_created,date_modified,ip_address,use_amount,use_currency_code,
		  base_currency_code,admin_base_currency,exchange_rate,exchange_rate_merchant_to_admin,exchange_rate_admin_to_merchant','safe'),		  
		  
		);
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){

			// if(DEMO_MODE && in_array($this->meta_value1,DEMO_MERCHANT)){				
			// 	return false;
			// }

			if($this->isNewRecord){
				$this->date_created = CommonUtility::dateNow();					
			} else {
				$this->date_modified = CommonUtility::dateNow();											
			}

			if(empty($this->deposit_uuid)){
				$this->deposit_uuid = CommonUtility::createUUID("{{bank_deposit}}",'deposit_uuid');
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
		$get_params = array( 
		   'invoice_number'=> $this->transaction_ref_id,
		   'key'=>$cron_key,
		   'language'=>Yii::app()->language
		);		
		
		if(!$this->isNewRecord){		
			// Update							
			if($this->deposit_type=="order"){
				try {
					$model = COrders::getByID($this->transaction_ref_id);

					if($this->status=="approved"){
						$model->payment_status = "paid";												
					} else $model->payment_status = "unpaid";
					$model->save();

					$model_trans = AR_ordernew_transaction::model()->find("order_id=:order_id",[
						':order_id'=>$this->transaction_ref_id
					]);			
					if($model_trans){
						if($this->status=="approved"){
							$model_trans->status = "paid";												
						} else $model_trans->status = "unpaid";
						$model_trans->save();
					}
					
				} catch (Exception $e) {
					//
				}		
		    } else if ( $this->deposit_type=="invoice" ){
				try {
					
					$model = CMerchantInvoice::getInvoiceByID($this->transaction_ref_id);
					$total = CMerchantInvoice::getTotalDeposit($this->transaction_ref_id);

					if($this->status=="approved"){																												
						$model->amount_paid = floatval($total);
						$invoice_total  = floatval($model->invoice_total);						
						$model->payment_status = $model->amount_paid==$invoice_total ? "paid" :"balance";		
					} else {
						$model->payment_status = "unpaid";						
						$model->amount_paid = floatval($total);
					}					
					$model->save();

				} catch (Exception $e) {
					//dump($e->getMessage());
				}					
			} else if ($this->deposit_type=="subscriptions"){				
				if($this->scenario=="update"){					
					CommonUtility::pushJobs("Subscriptiondepositupload",[
						'deposit_id'=>$this->deposit_id,
						'language'=>Yii::app()->language
					]);
				} else if ($this->scenario=="update_deposit_status"){
					CommonUtility::pushJobs("Subscriptiondepositupdate",[
						'deposit_id'=>$this->deposit_id,
						'status'=>$this->status,
						'language'=>Yii::app()->language
					]);
				}
			} else if ( $this->deposit_type=="wallet_loading" ){
				if($this->status=="approved"){
					CommonUtility::pushJobs("WalletLoading",[
						'reference_id'=>$this->transaction_ref_id,
						'language'=>Yii::app()->language
					]);
				}				
			}
	    } else {	
			// NEW 
			if($this->deposit_type=="invoice"){
				$invoice_meta = new AR_invoice_meta();
				$invoice_meta->invoice_number = $this->transaction_ref_id;
				$invoice_meta->meta_name = "bank_deposit";
				$invoice_meta->meta_value1 = "Bank deposit attach to invoice";
				$invoice_meta->meta_value2 = CommonUtility::dateNow();
				$invoice_meta->save();

				CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/taskinvoice/afteruploaddeposit?".http_build_query($get_params) );
			} else if ($this->deposit_type=="subscriptions"){				
				CommonUtility::pushJobs("Subscriptiondepositupload",[
					'deposit_id'=>$this->deposit_id,
					'language'=>Yii::app()->language
				]);
			} else if ( $this->deposit_type=="wallet_loading" ){
				CommonUtility::pushJobs("WalletDepositupload",[
					'reference_id'=>$this->transaction_ref_id,
					'language'=>Yii::app()->language
				]);
			}
		}
	}

	protected function afterDelete()
	{
		parent::afterDelete();		
		
		$file_path = CommonUtility::uploadDestination($this->path)."/".$this->proof_image;			
		if(file_exists($file_path)){			
			@unlink($file_path);
		}

		if($this->deposit_type=="invoice"){
			try {
				$model = CMerchantInvoice::getInvoiceByID($this->transaction_ref_id);
				$total = CMerchantInvoice::getTotalDeposit($this->transaction_ref_id);					
				$model->amount_paid = floatval($total);
				$model->payment_status = $model->amount_paid>0?"balance":"unpaid";
				$model->save();
			} catch (Exception $e) {
				//
			}		
	    }		
	}

	protected function beforeDelete()
	{				
	    if(DEMO_MODE && in_array($this->meta_value1,DEMO_MERCHANT)){
		    return false;
		}

		if($this->deposit_type=="order"){
			try {
				$model = COrders::getByID($this->transaction_ref_id);

				$model->payment_status = "unpaid";
				$model->save();

				$model_trans = AR_ordernew_transaction::model()->find("order_id=:order_id",[
					':order_id'=>$this->transaction_ref_id
				]);			
				if($model_trans){
					$model_trans->status = "unpaid";
					$model_trans->save();
				}
				
			} catch (Exception $e) {
				//
			}		
	    } else if ( $this->deposit_type=="invoice" ){	
			//		
		}

	    return true;
	}
		
}
/*end class*/