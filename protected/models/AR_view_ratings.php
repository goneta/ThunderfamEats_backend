<?php
class AR_view_ratings extends CActiveRecord
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
		return '{{view_ratings}}';
	}
	
	public function primaryKey()
	{
	    return 'merchant_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'merchant_id'=>t("merchant_id"),		    
		);
	}
	
	public function rules()
	{
		return array(		  
		  array('merchant_id','safe'),		  
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
	}

	protected function afterDelete()
	{
		parent::afterDelete();		
	}
		
}
/*end class*/
