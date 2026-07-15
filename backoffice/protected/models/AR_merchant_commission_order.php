<?php
class AR_merchant_commission_order extends CActiveRecord
{	

	public $applicable_selected;
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
		return '{{merchant_commission_order}}';
	}
	
	public function primaryKey()
	{
	    return 'id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		  'transaction_type'=>t("Transaction Type"),
		  'commission_type'=>t("Commission Type"),
		  'commission'=>t("Commission"),		  
		);
	}
	
	public function rules()
	{
		return array(
		  array('merchant_id,transaction_type,commission_type,commission', 
		  'required','message'=> t( Helper_field_required ) ),
		  		  
		  array('date_created,date_modified,ip_address','safe'),
		  		  
		);
	}

    protected function beforeSave()
	{
		if(!parent::beforeSave()){
			return false;
		} 
			
		if($this->isNewRecord){
			$this->date_created = CommonUtility::dateNow();					
		} else {
			$this->date_modified = CommonUtility::dateNow();											
		}
		$this->ip_address = CommonUtility::userIp();	
		
		return true;
	}
	
	protected function afterSave()
	{
		parent::afterSave();

        /*ADD CACHE REFERENCE*/
		CCacheData::add();
	}

	protected function afterDelete()
	{
		parent::afterDelete();		

        /*ADD CACHE REFERENCE*/
		CCacheData::add();
	}
		
}
/*end class*/
