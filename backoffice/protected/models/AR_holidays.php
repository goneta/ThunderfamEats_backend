<?php
class AR_holidays extends CActiveRecord
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
		return '{{holidays}}';
	}
	
	public function primaryKey()
	{
	    return 'id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'holiday_date'=>t("Date"),		    
		    'holiday_name'=>t("Event Name"),					    
		);
	}
	
	public function rules()
	{
		return array(
		  array('merchant_id,holiday_name,holiday_date', 
		  'required','message'=> t( Helper_field_required ) ),

		  array('holiday_name', 'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')), 		  
		  
		  array('is_closed,open_time,close_time,created_at','safe'),				  

		  array('holiday_name','ext.UniqueAttributesValidator','with'=>'merchant_id',
		    'message'=>t("Event name already exist")
		  ), 
		  
		);
	}

    protected function beforeSave()
	{
		if(!parent::beforeSave()){
			return false;
		} 

		if($this->isNewRecord){
			$this->created_at = CommonUtility::dateNow();
		}
		
		return true;
	}
	
	protected function afterSave()
	{		
		/*ADD CACHE REFERENCE*/
		CCacheData::add();
	}

	protected function afterDelete()
	{
		/*ADD CACHE REFERENCE*/
		CCacheData::add();
	}
		
}
/*end class*/
