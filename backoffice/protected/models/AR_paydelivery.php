<?php
class AR_paydelivery extends CActiveRecord
{	

    public $file;
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
		return '{{paydelivery}}';
	}
	
	public function primaryKey()
	{
	    return 'id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'payment_name'=>t("Payment name"),		    
		);
	}
	
	public function rules()
	{
		return array(
		  array('payment_name', 
		  'required','message'=> t( Helper_field_required )),

		  array('photo', 
		  'required','message'=> t( "Payment logo is required" )),
		  		  
		  array('payment_name', 'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')), 		  
		  
		  array('status,path,date_created,date_modified,ip_address','safe'),		  
		  
		);
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){
			if($this->isNewRecord){
				$this->date_created = CommonUtility::dateNow();					
			} else {
				$this->date_modified = CommonUtility::dateNow();											
			}
			$this->ip_address = CommonUtility::userIp();	
			
			return true;
		} else return true;
	}

	protected function beforeDelete()
	{					
		if(DEMO_MODE){				
			return false;
		}
		return true;
	}
	
	protected function afterSave()
	{
		CCacheData::add();
		parent::afterSave();
	}

	protected function afterDelete()
	{
		CCacheData::add();
		parent::afterDelete();		
	}
		
}
/*end class*/
