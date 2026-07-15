<?php
class AR_printer_logs extends CActiveRecord
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
		return '{{printer_logs}}';
	}
	
	public function primaryKey()
	{
	    return 'id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'printer_type'=>t("Printer type"),
            'printer_number'=>t("Printer number"),            
            'print_content'=>t("Content"),
            'job_id'=>t("Job id"),
		);
	}
	
	public function rules()
	{
		return array(
		  array('printer_type,printer_number,print_content', 
		  'required','message'=> t( Helper_field_required ) ),		  
		);
	}
		
    protected function beforeSave()
	{
		if(parent::beforeSave()){
						
			if($this->isNewRecord){
				$this->date_created = CommonUtility::dateNow();					
			} 
			$this->ip_address = CommonUtility::userIp();	
            
			return true;
		} else return true;
	}
	
	protected function afterSave()
	{
		parent::afterSave();
        /*ADD CACHE REFERENCE*/
		CCacheData::add();
	}
	
	protected function afterDelete()
	{
		parent::afterDelete();		
        /*ADD CACHE REFERENCE*/
		CCacheData::add();
	}
		
}
/*end class*/