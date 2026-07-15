<?php
class AR_user_custom_field_values extends CActiveRecord
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
		return '{{user_custom_field_values}}';
	}
	
	public function primaryKey()
	{
	    return 'value_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'value_id'=>t("value id"),		    	
		);
	}
	
	public function rules()
	{
		return array(
		  array('user_id,field_id,value', 
		  'required','message'=> t( Helper_field_required2 ) ),		  		  
		);
	}

    protected function beforeSave()
	{
		if(!parent::beforeSave()){
			return false;
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

	protected function afterDelete()
	{
		if(!parent::afterDelete()){
			return false;
		}
		
		/*ADD CACHE REFERENCE*/
		CCacheData::add();
	}
		
}
/*end class*/
