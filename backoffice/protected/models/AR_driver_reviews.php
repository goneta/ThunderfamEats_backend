<?php
class AR_driver_reviews extends CActiveRecord
{	
    
	public $driver_fullname,$customer_fullname,$driver_uuid,$client_uuid;
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
		return '{{driver_reviews}}';
	}
	
	public function primaryKey()
	{
	    return 'review_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'review_id'=>t("ID"), 
			'order_id'=>t("Order ID"), 
			'customer_id'=>t("Customer ID"), 
			'driver_id'=>t("Driver ID"), 
			'rating'=>t("Rating"), 
			'review_text'=>t("Review"), 
			'as_anonymous'=>t("Anonymous")
		);
	}
	
	public function rules()
	{
		return array(
		  array('order_id,customer_id,driver_id,rating', 
		  'required','message'=> t( Helper_field_required2 )  ),          

		  array('rating', 'numerical', 'integerOnly' => true, 'min'=>1,
		  'message'=>t("Rating must be numeric"),
		  'tooSmall'=>t("Rating must be numeric")),          

		  array('as_anonymous','safe'),

		);
	}	

    protected function beforeSave()
	{
		if(parent::beforeSave()){

			if($this->isNewRecord){
				$this->created_at = CommonUtility::dateNow();					
			} else {
				$this->updated_at = CommonUtility::dateNow();											
			}
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
	    return true;
	}
		
}
/*end class*/