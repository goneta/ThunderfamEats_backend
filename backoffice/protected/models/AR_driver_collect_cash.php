<?php
class AR_driver_collect_cash extends CActiveRecord
{	
    public $driver_name, $card_id;
	//public $merchant_base_currency,$admin_base_currency,$exchange_rate_merchant_to_admin,$exchange_rate_admin_to_merchant;
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
		return '{{driver_collect_cash}}';
	}
	
	public function primaryKey()
	{
	    return 'collect_id';	 
	}
		
	public function attributeLabels()
	{
		return array(		    
            'collect_id'=>t("Collection ID"),
            'reference_id'=>t("Reference"),
            'amount_collected'=>t("Amount"),            
            'driver_id'=>t("Driver"),            
		);
	}
	
	public function rules()
	{
		return array(
		  array('driver_id,amount_collected', 
		  'required','message'=> t( Helper_field_required2 ) ),	       

          array('reference_id', 'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')), 		  

          array('merchant_id,collection_uuid,transaction_date,date_created,ip_address,
		  merchant_base_currency,admin_base_currency,exchange_rate_merchant_to_admin,exchange_rate_admin_to_merchant
		  ','safe'),
          
          array('reference_id','unique','message'=>t(Helper_field_unique)),

          array('amount_collected', 'numerical', 'integerOnly' => false, 'min'=>1,
		  'message'=>t(Helper_field_numeric)),          

		  array('driver_id', 'numerical', 'integerOnly' => true, 'min'=>1,
		  'message'=>t("Driver is required"),'tooSmall'=>t("Driver is required")),          

		);
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){

			if(DEMO_MODE){				
				return false;
			}

			if($this->isNewRecord){				
                $this->transaction_date = CommonUtility::dateNow();
				$this->date_created = CommonUtility::dateNow();                
			} 

			if(empty($this->collection_uuid)){
				$this->collection_uuid = CommonUtility::createUUID("{{driver_collect_cash}}",'collection_uuid');
			}
			
			$this->ip_address = CommonUtility::userIp();	
						
			return true;
		} else return true;
	}
	
	protected function afterSave()
	{
		parent::afterSave();

		$description = !empty($this->reference_id)?"Collected cash reference#{{reference_id}}":'Collected cash';
		$params = [
			'transaction_description'=>$description,
			'transaction_description_parameters'=>array('{{reference_id}}'=>$this->reference_id), 
			'transaction_type'=>"credit",
			'transaction_amount'=>floatval($this->amount_collected),
			'meta_name'=>"collected_cash",
			'meta_value'=>$this->driver_id,
			'status'=>"paid",
			'merchant_base_currency'=>$this->merchant_base_currency,
			'admin_base_currency'=>$this->admin_base_currency,
			'exchange_rate_merchant_to_admin'=>floatval($this->exchange_rate_merchant_to_admin),
			'exchange_rate_admin_to_merchant'=>floatval($this->exchange_rate_admin_to_merchant),	
			'reference_id'=>$this->merchant_id,
			'reference_id1'=>$this->collection_uuid
		];
		CWallet::inserTransactions($this->card_id,$params);
	}

	protected function afterDelete()
	{
		parent::afterDelete();	

		$model = AR_wallet_transactions::model()->find("reference_id1=:reference_id1",[
			':reference_id1'=>$this->collection_uuid,
		]);
		if($model){		
			$description = !empty($this->reference_id)?"Void collected cash reference#{{reference_id}}":'Void collected cash';
			$params = [
				'transaction_description'=>$description,
				'transaction_description_parameters'=>array('{{reference_id}}'=>$this->reference_id), 
				'transaction_type'=>"debit",
				'transaction_amount'=>floatval($this->amount_collected),
				'meta_name'=>"collected_cash_void",
				'meta_value'=>$this->driver_id,
				'status'=>"paid",
				'merchant_base_currency'=>$this->merchant_base_currency,
				'admin_base_currency'=>$this->admin_base_currency,
				'exchange_rate_merchant_to_admin'=>floatval($this->exchange_rate_merchant_to_admin),
				'exchange_rate_admin_to_merchant'=>floatval($this->exchange_rate_admin_to_merchant),	
				'reference_id'=>$this->merchant_id,
				'reference_id1'=>$this->collection_uuid
			];
			CWallet::inserTransactions($this->card_id,$params);	
	    }
	}

	protected function beforeDelete()
	{				
	    if(DEMO_MODE){	
		    return false;
		}
	    return true;
	}
		
}
/*end class*/