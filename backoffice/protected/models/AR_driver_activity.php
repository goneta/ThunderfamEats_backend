<?php
class AR_driver_activity extends CActiveRecord
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
		return '{{driver_activity}}';
	}
	
	public function primaryKey()
	{
	    return 'id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'created_at'=>t("created_at"),		    
		);
	}
	
	public function rules()
	{
		return array(
		  array('driver_id,status', 
		  'required','message'=> t( Helper_field_required ) ),
		  		  
		  array('status,remarks,remarks_args', 'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')), 		  
		  
		  array('order_id,remarks,remarks_args,ip_address','safe'),
		  
		);
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){
			if($this->isNewRecord){
				$this->created_at = CommonUtility::dateNow();					
                $this->date_created = date("Y-m-d");
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