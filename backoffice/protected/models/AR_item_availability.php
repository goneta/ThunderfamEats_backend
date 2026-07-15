<?php
class AR_item_availability extends CActiveRecord
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
		return '{{item}}';
	}
	
	public function primaryKey()
	{
	    return 'item_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'item_id'=>t("item_id"),		    			
		);
	}
	
	public function rules()
	{
		return array(
		  array('item_name', 
		    'required','message'=> t( Helper_field_required ) ),		  
		  
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
		/*ADD CACHE REFERENCE*/
		CCacheData::add();
		
		if(!parent::afterSave()){
			return false;
		}
			
	}

	protected function afterDelete()
	{
		/*ADD CACHE REFERENCE*/
		CCacheData::add();

		if(!parent::afterDelete()){
			return false;
		}			
	}
		
}
/*end class*/
