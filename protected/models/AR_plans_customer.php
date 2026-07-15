<?php
class AR_plans_customer extends CActiveRecord
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
		return '{{plans_customer}}';
	}
	
	public function primaryKey()
	{
	    return 'id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'id'=>t("ID"),		    
		);
	}
	
	public function rules()
	{
		return array(

			array('subscriber_id,subscriber_type,customer_id,payment_code', 
			'required','message'=> t( Helper_field_required ) ),

			array('created_at,updated_at,livemode','safe'),
		  		  
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
			
}
/*end class*/