<?php
class AR_driver_sched extends CActiveRecord
{	   
    public $fullname,$plate_number,$csv,$break_started,$zone_name;

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
		return '{{driver_schedule}}';
	}
	
	public function primaryKey()
	{
	    return 'schedule_id';	 
	}
		
	public function attributeLabels()
	{
		return array(		    
            'driver_id'=>t("Driver id"),
            'vehicle_id'=>t("Vehicle id"),
            'date_start'=>t("Date start"),
            'time_start'=>t("Timestart"),
            'time_end'=>t("Time end"),
            'active'=>t("Status"),
			'instructions'=>t("Instructions"),
			'zone_id'=>t("Zone")
		);
	}
	
	public function rules()
	{
		return array(
		  array('merchant_id,driver_id,zone_id', 
		  'required','message'=> t( Helper_field_required2 ) , 'on'=>"insert,update"  ),	       

          array('schedule_uuid,date_created,date_modified,ip_address,break_duration,on_demand','safe'),                            
		);
	}
	
    protected function beforeSave()
	{		
		if(parent::beforeSave()){

			// if(DEMO_MODE){				
			// 	return false;
			// }

			if($this->isNewRecord){				
				$this->date_created = CommonUtility::dateNow();					
			} else {								
				$this->date_modified = CommonUtility::dateNow();
			}

			if(empty($this->schedule_uuid)){
				$this->schedule_uuid = CommonUtility::createUUID("{{driver_schedule}}",'schedule_uuid');
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

	protected function beforeDelete()
	{				
	    if(DEMO_MODE){	
		    return false;
		}
	    return true;
	}	
		
}
/*end class*/