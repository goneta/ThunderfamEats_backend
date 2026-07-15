<?php
class AR_table_device extends CActiveRecord
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
		return '{{table_device}}';
	}
	
	public function primaryKey()
	{
	    return 'table_uuid';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'table_uuid'=>t("table_uuid"),		    
		);
	}
	
	public function rules()
	{
		return array(

		  array('table_uuid,device_id,device_info', 
		  'required','message'=> t( Helper_field_required )),
		  
		  array('created_at','safe')
		  		  
		);
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){
			if($this->isNewRecord){
				$this->created_at = CommonUtility::dateNow();	
			} else {
				$this->created_at = CommonUtility::dateNow();	
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