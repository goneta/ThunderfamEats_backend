<?php
class AR_plans_webhooks extends CActiveRecord
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
		return '{{plans_webhooks}}';
	}
	
	public function primaryKey()
	{
	    return 'id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'event_type'=>t("event_type"),		    
		);
	}
	
	public function rules()
	{
		return array(

			array('id	','unique','message'=>t("Webhook id already exist")),

			array('id,event_type', 
			'required','message'=> t( Helper_field_required ) ),

			array('processed_at',
			'safe'),
		  		  
		);
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){
			if($this->isNewRecord){
				$this->processed_at = CommonUtility::dateNow();	
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