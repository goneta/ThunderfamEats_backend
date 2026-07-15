<?php
class AR_view_client_address_locations extends CActiveRecord
{	

	public $complete_address;
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
		return '{{view_client_address_locations}}';
	}
	
	public function primaryKey()
	{
	    return 'address_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'address_id'=>t("Address ID"),		    			
		);
	}
	
	public function rules()
	{
		return array(
		  array('address_id', 
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
