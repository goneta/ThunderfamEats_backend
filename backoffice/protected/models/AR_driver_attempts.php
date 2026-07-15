<?php
class AR_driver_attempts extends CActiveRecord
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
		return '{{driver_attempts}}';
	}
	
	public function primaryKey()
	{
	    return 'attempt_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'attempt_id'=>t("attempt_id"),		    		    
		);
	}
	
	public function rules()
	{
		return array(
		  array('order_id,driver_id,attempt_status', 
		  'required','message'=> t( Helper_field_required ) ),		  		  
		);
	}

    protected function beforeSave()
	{
		if(!parent::beforeSave()){
			return false;
		} 

		if($this->isNewRecord){
			$this->attempt_time = CommonUtility::dateNow();
		}
		
		return true;
	}
	
	protected function afterSave()
	{		
		/*ADD CACHE REFERENCE*/
		CCacheData::add();
	}

	protected function afterDelete()
	{
		/*ADD CACHE REFERENCE*/
		CCacheData::add();
	}
		
}
/*end class*/
