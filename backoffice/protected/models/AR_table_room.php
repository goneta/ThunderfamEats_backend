<?php
class AR_table_room extends CActiveRecord
{	
    
	public $capacity,$total_tables;
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
		return '{{table_room}}';
	}
	
	public function primaryKey()
	{
	    return 'room_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'room_id '=>t("Room ID"),
            'room_name '=>t("Room Name"),
		);
	}
	
	public function rules()
	{
		return array(
		  array('room_name,merchant_id', 
		  'required','message'=> t( Helper_field_required ) ),

		  //array("room_name",'unique','message'=>t("Room name aready exist")),
		  array('room_name','ext.UniqueAttributesValidator','with'=>'merchant_id',
		      'message'=>t("Room name aready exist")
		     ),            
		  		  
		  array('room_name', 'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')), 		  
		  
		  array('status,date_created,date_modified,ip_address,sequence','safe'),		  
		  
		);
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){

			if(DEMO_MODE && !$this->isNewRecord && in_array($this->merchant_id,DEMO_MERCHANT)){				
				return false;
			}
			
			if($this->isNewRecord){
				$this->date_created = CommonUtility::dateNow();					
			} else {
				$this->date_modified = CommonUtility::dateNow();											
			}
			$this->ip_address = CommonUtility::userIp();	

            if(empty($this->room_uuid)){
				$this->room_uuid = CommonUtility::createUUID("{{table_room}}",'room_uuid');
			}			
			if(empty($this->sequence)){
				$this->sequence = CommonUtility::getLastSequence('table_room',"WHERE merchant_id=".q($this->merchant_id)." ");
			}						
			return true;
		} else return true;
	}
	
	protected function afterSave()
	{
		parent::afterSave();
        /*ADD CACHE REFERENCE*/
		CCacheData::add();
	}

	protected function beforeDelete()
	{				
	    if(DEMO_MODE && in_array($this->merchant_id,DEMO_MERCHANT)){				
	        return false;
	    }
	    return true;
	}

	protected function afterDelete()
	{
		parent::afterDelete();		
        /*ADD CACHE REFERENCE*/
		AR_table_tables::model()->deleteAll('room_id=:room_id',array(
			':room_id'=> $this->room_id
		));
		
		CCacheData::add();
	}
		
}
/*end class*/