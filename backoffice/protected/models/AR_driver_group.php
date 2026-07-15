<?php
class AR_driver_group extends CActiveRecord
{		
    	
	public $drivers;
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
		return '{{driver_group}}';
	}
	
	public function primaryKey()
	{
	    return 'group_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'group_name'=>t("Name"),		    
		);
	}
	
	public function rules()
	{
		return array(

		  array('group_name,drivers', 
		  'required','message'=> t( Helper_field_required2 ),
		    'on'=>"insert,update"
		   ),		  
		  		  		  
		  array('group_name',
            'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')),         
		             		  
		  array('color_hex,group_uuid,date_created,date_modified,ip_address','safe')
            
		);
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){
			
		     if(DEMO_MODE){				
		        return false;
		     }

			if($this->isNewRecord){
				$this->date_created = CommonUtility::dateNow();					
			} else {
				$this->date_modified = CommonUtility::dateNow();											
			}

			if(empty($this->group_uuid)){
				$this->group_uuid = CommonUtility::createUUID("{{driver_group}}",'group_uuid');
			}

			$this->ip_address = CommonUtility::userIp();	
			
			return true;
		} else return true;
	}
	
	protected function afterSave()
	{
		parent::afterSave();

		$params = [];
		AR_driver_group_relations::model()->deleteAll("group_id=:group_id",[
			':group_id'=>intval($this->group_id)
		]);
		if(!empty($this->drivers)){
			foreach ($this->drivers as $items) {				
				$params[]=array(
					'date_created'=>CommonUtility::dateNow(),
					'group_id'=>intval($this->group_id),
					'driver_id'=> intval($items) 
				);
			}
			$builder=Yii::app()->db->schema->commandBuilder;
		    $command=$builder->createMultipleInsertCommand('{{driver_group_relations}}',$params);
		    $command->execute();			
		}		
				
		/*ADD CACHE REFERENCE*/
		CCacheData::add();
	}
	
    protected function beforeDelete()
	{				
	    if(DEMO_MODE){				
		    return false;
		}
	    return true;
	}

	
	protected function afterDelete()
	{
		parent::afterDelete();		

		AR_driver_group_relations::model()->deleteAll("group_id=:group_id",array(
			':group_id'=>$this->group_id			  
		));
				
		/*ADD CACHE REFERENCE*/
		CCacheData::add();
	}
		
}
/*end class*/
