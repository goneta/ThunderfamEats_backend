<?php
class AR_table_reservation_history extends CActiveRecord
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
		return '{{table_reservation_history}}';
	}
	
	public function primaryKey()
	{
	    return 'id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'reservation_id '=>t("reservation id"),
            'status'=>t("Status"),
		);
	}
	
	public function rules()
	{
		return array(
		  array('reservation_id,status', 
		  'required','message'=> t( Helper_field_required ) ),		  
		  		  
		  array('status,remarks,ramarks_trans,change_by', 'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')), 		  
		  
		  array('created_at,ip_address,change_by,remarks,ramarks_trans','safe'),		  
		  
		);
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){
			if($this->isNewRecord){
				$this->created_at = CommonUtility::dateNow();					
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