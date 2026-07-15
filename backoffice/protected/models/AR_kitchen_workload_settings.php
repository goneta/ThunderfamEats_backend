<?php
class AR_kitchen_workload_settings extends CActiveRecord
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
		return '{{kitchen_workload_settings}}';
	}
	
	public function primaryKey()
	{
	    return 'id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'low_workload_max_orders'=>t("Orders Below"),
			'low_workload_extra_time'=>t("Add (Minutes)"),
			'medium_workload_min_orders'=>t("Orders From"),
			'medium_workload_max_orders'=>t("Orders To"),
			'medium_workload_extra_time'=>t("Add (Minutes)"),
			'high_workload_min_orders'=>t("Orders Above"),
			'high_workload_extra_time'=>t("Add (Minutes)"),
		);
	}
	
	public function rules()
	{
		return array(
		  array('merchant_id', 
		    'required','message'=> t( Helper_field_required2 ) ),		  		 		  
		
		  array('low_workload_max_orders,low_workload_extra_time,medium_workload_min_orders,
		  medium_workload_max_orders,medium_workload_extra_time,high_workload_min_orders,high_workload_extra_time
		  ','safe'),

		  array('low_workload_max_orders,low_workload_extra_time,medium_workload_min_orders,
		  medium_workload_max_orders,medium_workload_extra_time,high_workload_min_orders,high_workload_extra_time', 
		  'numerical', 'integerOnly' => true,		   
		   'min'=>0,		  
		   'tooSmall'=>t("{attribute} minimum value is 1"),		  
		   'message'=>t("{attribute} is required"),		  
		  ),

		);
	}

    protected function beforeSave()
	{
		if(!parent::beforeSave()){
			return false;
		} 

		if($this->isNewRecord){
			$this->created_at = CommonUtility::dateNow();				
		} else {
			$this->updated_at = CommonUtility::dateNow();			
		}
		return true;
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
