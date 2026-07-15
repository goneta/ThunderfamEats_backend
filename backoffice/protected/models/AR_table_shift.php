<?php
class AR_table_shift extends CActiveRecord
{	
    
	public $capacity,$total_tables;
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
		return '{{table_shift}}';
	}
	
	public function primaryKey()
	{
	    return 'shift_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'shift_id '=>t("Shift ID"),
            'shift_name'=>t("Shift name"),
            'first_seating'=>t("First seating"),
            'last_seating'=>t("Last seating"),
            'shift_interval'=>t("Interval"),
            'status'=>t("Status"),
		);
	}
	
	public function rules()
	{
		return array(
		  array('merchant_id,shift_name,days_of_week,first_seating,last_seating,shift_interval', 
		  'required','message'=> t( Helper_field_required ) ),

		  ///array("shift_name",'unique','message'=>t("Room name aready exist")),
		  		  
		  array('shift_name', 'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')), 		  
		  
		  array('status,date_created,date_modified,ip_address','safe'),		  

          array('first_seating','validateTime'),
          array('last_seating','validateTime2'),
		  
		);
	}

    public function validateTime($attribute,$params)
    {
        $stmt="
        SELECT * FROM
        {{table_shift}}
        WHERE 
        first_seating BETWEEN ".q($this->first_seating)." AND ".q($this->first_seating)."
        AND merchant_id=".q($this->merchant_id)."        
        ";
        if(!$this->isNewRecord){
            $stmt.="
            AND shift_id <>".q($this->shift_id)."
            ";
        }
        if(Yii::app()->db->createCommand($stmt)->queryRow()){
            $this->addError($attribute, t("Shift time is overlapping with other shifts"));
        }        
    }

    public function validateTime2($attribute,$params)
    {
        $stmt="
        SELECT * FROM
        {{table_shift}}
        WHERE 
        last_seating BETWEEN ".q($this->last_seating)." AND ".q($this->last_seating)."
        AND merchant_id=".q($this->merchant_id)."        
        ";
        if(!$this->isNewRecord){
            $stmt.="
            AND shift_id <>".q($this->shift_id)."
            ";
        }
        if(Yii::app()->db->createCommand($stmt)->queryRow()){
            $this->addError($attribute, t("Shift time is overlapping with other shifts"));
        }        
    }

    protected function beforeSave()
	{
		if(parent::beforeSave()){

			if(DEMO_MODE && !$this->isNewRecord && in_array($this->merchant_id,DEMO_MERCHANT)){				
				return false;
			}

			if($this->isNewRecord){
				$this->date_created = CommonUtility::dateNow();					
			} else {
				$this->date_modified = CommonUtility::dateNow();											
			}
			$this->ip_address = CommonUtility::userIp();	

            $this->days_of_week = json_encode($this->days_of_week);

            if(empty($this->shift_uuid)){
				$this->shift_uuid = CommonUtility::createUUID("{{table_shift}}",'shift_uuid');
			}			
			
			return true;
		} else return true;
	}
	
	protected function afterSave()
	{
		parent::afterSave();	
		$day_list =  array(
    	  1=>"monday",
    	  2=>"tuesday",
    	  3=>"wednesday",
    	  4=>"thursday",
    	  5=>"friday",
    	  6=>"saturday",
    	  7=>"sunday"
    	);
		$day_list = array_flip($day_list);
		$day_list['sunday']=0;		

		$new_days = [];
		$days = json_decode($this->days_of_week,true);				
		if(is_array($days) && count($days)>=1){
			foreach ($days as $key => $items) {				
				if($items!="0"){					
					$new_days[] = isset($day_list[$key])?$day_list[$key]:'';
				}
			}
		}

		
		$stmt= "DELETE FROM {{table_shift_days}}
		WHERE merchant_id=".q($this->merchant_id)."
		AND shift_id=".q($this->shift_id)."		
		";				
		Yii::app()->db->createCommand($stmt)->query();

		if(is_array($new_days) && count($new_days)>=1){
			foreach ($new_days as $key => $items) {				
				$params[] = [
					'merchant_id'=>$this->merchant_id,
					'shift_id'=>$this->shift_id,
					'day_of_week'=>intval($items),
				];
			}			
			$builder=Yii::app()->db->schema->commandBuilder;
		    $command=$builder->createMultipleInsertCommand("{{table_shift_days}}",$params);
		    $command->execute();
		}				

        /*ADD CACHE REFERENCE*/
		CCacheData::add();
	}

	protected function beforeDelete()
	{				
	    if(DEMO_MODE && in_array($this->merchant_id,DEMO_MERCHANT)){				
	        return false;
	    }
	    return true;
	}

	protected function afterDelete()
	{
		parent::afterDelete();		

		Yii::app()->db->createCommand("
		DELETE FROM {{table_shift_days}}
		WHERE shift_id = ".CommonUtility::q($this->shift_id)."
		")->query();

        /*ADD CACHE REFERENCE*/
		CCacheData::add();
	}
		
}
/*end class*/