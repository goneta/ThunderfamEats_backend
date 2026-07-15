<?php
class AR_driver_location extends CActiveRecord
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
		return '{{driver_location}}';
	}
	
	public function primaryKey()
	{
	    return 'id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'driver_id'=>t("driver_id"),		    
		);
	}
	
	public function rules()
	{
		return array(
		  array('latitude,longitude', 
		  'required','message'=> t( Helper_field_required ) ),
		  		  		  
		  array('driver_id,latitude,longitude,accuracy,altitude,
          altitudeAccuracy,speed,bearing,time,simulated,created_at,created_timestamp',
          'safe'),
		  
		);
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){
			if($this->isNewRecord){
				//$this->date_created = CommonUtility::dateNow();					
			} else {
				//$this->date_modified = CommonUtility::dateNow();											
			}			
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
		
}
/*end class*/