<?php
class AR_driver_group_relations extends CActiveRecord
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
		return '{{driver_group_relations}}';
	}
	
	public function primaryKey()
	{
	    return 'group_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'group_id'=>t("group_id"),		    
		);
	}
	
	public function rules()
	{
		return array(

		  array('group_id', 
		  'required','message'=> t( Helper_field_required2 ),
		    'on'=>"insert,update"
		   ),		  
		  		  		  		             		  
		  array('driver_id,vehicle_id,date_created','safe')
            
		);
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){
			
		     if(DEMO_MODE){				
		        return false;
		     }

			 $this->date_created = CommonUtility::dateNow();					

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
	    if(DEMO_MODE){				
		    return false;
		}
	    return true;
	}

	
	protected function afterDelete()
	{
		parent::afterDelete();		
				
		/*ADD CACHE REFERENCE*/
		CCacheData::add();
	}
		
}
/*end class*/
