<?php
class AR_table_status extends CActiveRecord
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
		return '{{table_status}}';
	}
	
	public function primaryKey()
	{
	    return 'table_uuid';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'table_uuid'=>t("table_uuid"),		    
		);
	}
	
	public function rules()
	{
		return array(

		  array('table_uuid,merchant_id,status', 
		  'required','message'=> t( Helper_field_required ) ),				  
		  		  
		);
	}


    protected function beforeSave()
	{
		if(parent::beforeSave()){				
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