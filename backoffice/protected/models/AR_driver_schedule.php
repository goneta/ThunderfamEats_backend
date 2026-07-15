<?php
class AR_driver_schedule extends CActiveRecord
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
		  array('driver_id,vehicle_id,zone_id,time_start,time_end,active', 
		  'required','message'=> t( Helper_field_required2 ) , 'on'=>"insert,update"  ),	       

          array('schedule_uuid,date_created,date_modified,ip_address,break_duration','safe'),                  

          array('vehicle_id','numerical', 'min'=>1 , 'tooSmall'=>t("Vehicle is required") , 'on'=>"insert,update"),

          array('driver_id','numerical', 'min'=>1 , 'tooSmall'=>t("Driver is required") , 'on'=>"insert,update"),

		  array('time_start', 'validateSched', 'on'=>"insert,update" ),
		  array('time_end', 'validateCarSched', 'on'=>"insert,update" ),
		  array('shift_time_ended', 'validateEndShift', 'on'=>"end_shift" ),
		  
		  array('csv',
		  'file', 'types' => 'csv',
		  'maxSize'=>5242880,
		  'allowEmpty' => false,
		  'wrongType'=>t('Only csv allowed.'),
		  'tooLarge'=>t('File too large! 5MB is the limit'),
		  'on'=>'csv_upload'
		  ),

		  array('shift_time_started,shift_time_ended,shift_id','safe'),

		  array('break_duration','validateRequestBreak'),
		  
		);
	}

	public function validateSched($attribute, $params)
	{
		$criteria=new CDbCriteria();	
		$criteria->addCondition('driver_id=:driver_id AND active=:active 	
		AND shift_time_ended IS NULL
		AND ( :time_start between time_start and time_end OR  :time_end between time_start and time_end )
		');
		$criteria->params = array(
			':driver_id'=>$this->driver_id,			
			':active' => 1,
			':time_start'=>$this->time_start,
			':time_end'=>$this->time_end,
		);		
		if(!$this->isNewRecord){			
			$criteria->addNotInCondition("schedule_id",array($this->schedule_id));
		}								
		if($model = AR_driver_schedule::model()->findAll($criteria)){		
			$this->addError('date_start', t("Driver Conflict schedule") );	
		} 
	}
	

	public function validateCarSched($attribute, $params)
	{
		$criteria=new CDbCriteria();		
		$criteria->addCondition('merchant_id=:merchant_id AND vehicle_id=:vehicle_id AND active=:active 
		AND shift_time_ended IS NULL
		AND ( :time_start between time_start and time_end OR  :time_end between time_start and time_end )
		');
		$criteria->params = array(
			':merchant_id'=>$this->merchant_id,
			':vehicle_id'=>$this->vehicle_id,			
			':active' => 1,
			':time_start'=>$this->time_start,
			':time_end'=>$this->time_end,
		);
		if(!$this->isNewRecord){			
			$criteria->addNotInCondition("vehicle_id",array($this->vehicle_id));
		}
			
		if(AR_driver_schedule::model()->findAll($criteria)){	
			$this->addError('time_start', t("Car Conflict schedule") );	
		}		
	}

	public function validateRequestBreak($attribute, $params)
	{		
		if($this->scenario=="request_break"){		
			$now = date("Y-m-d");		
			if(!CDriver::canRequestBreak($this->driver_id,$now)){			
				$this->addError($attribute, t("You already consume your maximum allowed break time") );	
			}
			
			$model = AR_driver_break::model()->find("schedule_id=:schedule_id",[
				':schedule_id'=>$this->schedule_id
			]);
			if($model){
				if($model->break_ended==null){				
					$this->addError($attribute, t("You have already existing break") );	
				}
			}		
	    }
	}

	public function validateEndShift($attribute, $params)
	{
		$assigned_group = AOrders::getOrderTabsStatus('assigned');					
		$criteria=new CDbCriteria();
		$criteria->addCondition("driver_id=:driver_id AND DATE(delivery_date)=:delivery_date");
		$criteria->params = [
			':driver_id'=>$this->driver_id,
			':delivery_date'=>date("Y-m-d")
		];
		$criteria->addInCondition("delivery_status",(array)$assigned_group);		
		if(AR_ordernew::model()->findAll($criteria)){
			$this->addError($attribute, t("You have active order, please finish the delivery first before ending shift") );	
		}		
	}

    protected function beforeSave()
	{
		//dump($this->scenario);die();
		if(parent::beforeSave()){

			if(DEMO_MODE){				
				return false;
			}

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
		if($this->isNewRecord){
			$noti = new AR_notifications; 
			$noti->notication_channel = Yii::app()->params->realtime['admin_channel'] ;
			$noti->notification_event = Yii::app()->params->realtime['notification_event'];						
			$noti->notification_type = "silent";
			$noti->message = 'new schedule';
			$noti->visible = 0;						
			if(!$noti->save()){
				//dump($noti->getErrors());
			}								
		}

		switch ($this->scenario) {
			case "request_break":
				 $model_break = new AR_driver_break();
				 $model_break->schedule_id = $this->schedule_id;
				 $model_break->break_duration = $this->break_duration;
				 $model_break->break_started = $this->break_started;
				 $model_break->driver_id = $this->driver_id;
				 $model_break->save();
				 break;

			case 'start_shift':		
				$driver = CDriver::getDriver($this->driver_id);
				$model_activity = new AR_driver_activity;
				$model_activity->driver_id = $this->driver_id;
				$model_activity->order_id = 0;
				$model_activity->reference_id = intval($this->schedule_id);
				$model_activity->status = "started shift";
				$model_activity->remarks = "started shift at {{time}}";
				$model_activity->remarks_args = json_encode([
					'{{time}}'=>Date_Formatter::Time($this->shift_time_started)
				]);	
				$model_activity->latitude = $driver->latitude;		
				$model_activity->longitude = $driver->lontitude;				
				if(!$model_activity->save()){
					//dump($model_activity->getErrors());
				}				
				break;
			
			default:				
				break;
		}
	}

	protected function afterDelete()
	{
		parent::afterDelete();		
		$noti = new AR_notifications; 
		$noti->notication_channel = Yii::app()->params->realtime['admin_channel'] ;
		$noti->notification_event = Yii::app()->params->realtime['notification_event'];						
		$noti->notification_type = "silent";
		$noti->message = 'delete schedule';
		$noti->visible = 0;						
		if(!$noti->save()){
			//dump($noti->getErrors());
		}								
	}

	protected function beforeDelete()
	{				
	    if(DEMO_MODE){	
		    return false;
		}
	    return true;
	}

	public static function validateSchedule($driver_id,$time_start,$time_end)
	{
		$criteria=new CDbCriteria();
		$criteria->addCondition('driver_id=:driver_id AND active=:active');
		$criteria->params = array(
			':driver_id'=>$driver_id,			
			':active' => 1 
		);		
		$criteria->addBetweenCondition('time_start',$time_start,$time_end);					
		if($model = AR_driver_schedule::model()->findAll($criteria)){
			throw new Exception("Driver Conflict schedule", 1);			
		}		
		return true;
	}

	public static function validateCarSchedule($vehicle_id,$time_start,$time_end)
	{
		$criteria=new CDbCriteria();
		$criteria->addCondition('vehicle_id=:vehicle_id AND active=:active');
		$criteria->params = array(
			':vehicle_id'=>$vehicle_id,			
			':active' => 1 
		);		
		$criteria->addBetweenCondition('time_start',$time_start,$time_end);					
		if($model = AR_driver_schedule::model()->findAll($criteria)){			
			throw new Exception("Car Conflict schedule", 1);			
		}		
		return true;
	}
		
}
/*end class*/