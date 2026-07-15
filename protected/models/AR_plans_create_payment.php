<?php
class AR_plans_create_payment extends CActiveRecord
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
		return '{{plans_create_payment}}';
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

			array('package_id,subscriber_id,subscription_type', 
			'required','message'=> t( Helper_field_required ) ),

			array('success_url,failed_url,subscriber_type,jobs',
			'safe'),
		  		  
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
			if(empty($this->payment_id)){
				$this->payment_id = CommonUtility::createUUID("{{plans_create_payment}}",'payment_id');
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