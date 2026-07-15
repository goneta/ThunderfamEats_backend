<?php
class AR_custom_fields extends CActiveRecord
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
		return '{{custom_fields}}';
	}
	
	public function primaryKey()
	{
	    return 'field_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'field_name'=>t("Field Name (Internal use)"),		    	
		);
	}
	
	public function rules()
	{
		return array(
		  array('field_name,field_label,field_type,entity', 
		  'required','message'=> t( Helper_field_required2 ) ),

		  //array("field_name",'unique','message'=>t("Field name already exist")),
		  array('field_name','ext.UniqueAttributesValidator','with'=>'entity',
		   'message'=>t("Field name already exist")
		  ),

		  array('field_name,field_label', 'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')), 		  
		  
		  array('is_required', 'numerical', 'integerOnly' => true,		  
		  'message'=>t(Helper_field_numeric)),

		  array('options,sequence,visible','safe'),
		  
		);
	}

    protected function beforeSave()
	{
		if(!parent::beforeSave()){
			return false;
		} 
		if($this->sequence<=0){
			$this->sequence = CommonUtility::getLastSequence("custom_fields","where entity=".q($this->entity)." ");		
		}		
		return true;
	}
	
	protected function afterSave()
	{
		if(!parent::afterSave()){
			return false;
		}
		
		/*ADD CACHE REFERENCE*/
		CCacheData::add();
	}

	protected function beforeDelete()
	{				
	    if(DEMO_MODE){		
		    return false;
		}
	    return true;
	}

	protected function afterDelete()
	{		
		AR_user_custom_field_values::model()->deleteAll('field_id=:field_id', array(
			':field_id' => $this->field_id,
		));

		if (!parent::afterDelete()) {
			return false; 
		}
						
		// Add cache reference (custom action)
		CCacheData::add();

		return true; // Indicate successful completion
	}

		
}
/*end class*/
