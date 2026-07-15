<?php
class AR_driver_shift_schedule extends CActiveRecord
{	
    
	public $date_shift,$date_shift_end,$csv;
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
		return '{{driver_shift_schedule}}';
	}
	
	public function primaryKey()
	{
	    return 'shift_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    //'location_name'=>t("Location name"),	
            'zone_id'=>t("Zone"),
            'date_shift'=>t("Date Start"),       
			'date_shift_end'=>t("Date End"),     			
            'time_start'=>t("Time Start"),
            'time_end'=>t("Time Ends"),
            'max_allow_slot'=>t("Max allow slot"),
		);
	}
	
	public function rules()
	{
		return array(
		  array('date_shift,date_shift_end,time_start,time_end,status,zone_id', 
		  'required','message'=> t( Helper_field_required ) , 'on'=>"insert,update"  ),		  
		  				  
		  
		  array('merchant_id,max_allow_slot,shift_uuid,date_created,date_modified,ip_address','safe'),		
          
          array('max_allow_slot', 'numerical', 'integerOnly'=>true),
          
          array('date_shift', 'validateSched', 'on'=>"insert,update" ),
		  
		  array('csv',
			'file', 'types' => 'csv',
			'maxSize'=>5242880,
			'allowEmpty' => false,
			'wrongType'=>t('Only csv allowed.'),
			'tooLarge'=>t('File too large! 5MB is the limit'),
			'on'=>'csv_upload'
		  ),
		  
		);
	}

    public function validateSched($attribute, $params)
	{
		// $criteria=new CDbCriteria();				
		// $criteria->addCondition('zone_id=:zone_id AND date_shift=:date_shift AND status=:status 	
		// AND ( :time_start between time_start and time_end OR  :time_end between time_start and time_end )
		// ');
		// $criteria->params = array(
        //     ':zone_id'=>$this->zone_id,
		// 	':date_shift'=>$this->date_shift,			
		// 	':status' => 'active',
		// 	':time_start'=>$this->time_start,
		// 	':time_end'=>$this->time_end,
		// );		

		$criteria=new CDbCriteria();				
		$criteria->addCondition('merchant_id=:merchant_id AND zone_id=:zone_id AND status=:status 
		 AND ( :time_start between time_start and time_end OR  :time_end between time_start and time_end )
		');		
		$criteria->params = [
			':merchant_id'=>$this->merchant_id,
			':zone_id'=>$this->zone_id,
			':status' => 'active',
			':time_start'=>"$this->date_shift $this->time_start",
			':time_end'=>"$this->date_shift_end $this->time_end",
		];
		if(!$this->isNewRecord){			
			$criteria->addNotInCondition("shift_uuid",array($this->shift_uuid));
		}								
		if($model = AR_driver_shift_schedule::model()->findAll($criteria)){						
			$this->addError('date_start', t("Shift schedule conflict") );	
		} 		
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){
			if($this->isNewRecord){
				$this->date_created = CommonUtility::dateNow();					
			} else {
				$this->date_modified = CommonUtility::dateNow();											
			}

            if(empty($this->shift_uuid)){
				$this->shift_uuid = CommonUtility::createUUID("{{driver_shift_schedule}}",'shift_uuid');
			}

			$this->time_start = "$this->date_shift $this->time_start";
			$this->time_end = "$this->date_shift_end $this->time_end";

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
