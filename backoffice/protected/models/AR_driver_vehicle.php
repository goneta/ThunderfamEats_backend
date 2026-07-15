<?php
class AR_driver_vehicle extends CActiveRecord
{	
    public $new_password,$confirm_password;
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
		return '{{driver_vehicle}}';
	}
	
	public function primaryKey()
	{
	    return 'vehicle_id';	 
	}
		
	public function attributeLabels()
	{
		return array(		    
            'vehicle_type_id'=>t("Vehicle type"),
            'plate_number'=>t("Plate number"),
            'maker'=>t("Maker"),
            'model'=>t("Model"),
            'color'=>t("Color"),
            'active'=>t("Status"),
		);
	}
	
	public function rules()
	{
		return array(
		  array('vehicle_type_id,plate_number,active', 
		  'required','message'=> t( Helper_field_required2 ) ),	       

          array('maker,plate_number', 'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')), 		  

          array('merchant_id,driver_id,vehicle_uuid,maker,model,color,photo,path,date_created,date_modified,ip_address','safe'),
          
          array('plate_number','unique','message'=>t(Helper_field_unique)),

		);
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){

			if(DEMO_MODE){				
				return false;
			}

			if($this->isNewRecord){				
				$this->date_created = CommonUtility::dateNow();					
			} else {								
				$this->date_modified = CommonUtility::dateNow();
			}

			if(empty($this->vehicle_uuid)){
				$this->vehicle_uuid = CommonUtility::createUUID("{{driver_vehicle}}",'vehicle_uuid');
			}
			
			$this->ip_address = CommonUtility::userIp();	
						
			return true;
		} else return true;
	}
	
	protected function afterSave()
	{
		parent::afterSave();
	}

	protected function afterDelete()
	{
		parent::afterDelete();		
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