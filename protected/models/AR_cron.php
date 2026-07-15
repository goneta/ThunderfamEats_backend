<?php
class AR_cron extends CActiveRecord
{	

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
		return '{{cron}}';
	}
	
	public function primaryKey()
	{
	    return 'cron_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'cron_id'=>t("cron_id"),		    
		);
	}
	
	public function rules()
	{
		return array(

		  array('url', 
		  'required','message'=> t( Helper_field_required ) ,'on'=>'registration_phone' ),
		  
		  array('status,date_created','safe')
		  		  
		);
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){
			if($this->isNewRecord){
				$this->date_created = CommonUtility::dateNow();	
			} else {
				$this->date_modified = CommonUtility::dateNow();	
			}			
			$this->ip_address = CommonUtility::userIp();				
			return true;
		} else return true;
	}
	
	protected function afterSave()
	{
		parent::afterSave();
        CCacheData::add();
	}

	protected function afterDelete()
	{
		parent::afterDelete();	
        CCacheData::add();	
	}
			
}
/*end class*/