<?php
class AR_driver_break extends CActiveRecord
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
		return '{{driver_break}}';
	}
	
	public function primaryKey()
	{
	    return 'id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'schedule_id'=>t("schedule_id"),		    
		);
	}
	
	public function rules()
	{
		return array(
		  array('schedule_id,break_duration,break_started', 
		  'required','message'=> t( Helper_field_required ) ),
		  		  		  		  
		  array('break_ended,date_created,ip_address','safe'),
		  
		);
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){
			if($this->isNewRecord){
				$this->date_created = CommonUtility::dateNow();	                
			}
			$this->ip_address = CommonUtility::userIp();				
			return true;
		} else return true;
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