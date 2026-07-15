<?php
class AR_category_sort extends CActiveRecord
{	

	public $category_translation,$category_description_translation,
	$multi_language,$image,$dish_selected, 
	$available_day, $available_time_start, $available_time_end
	;
	
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
		return '{{category}}';
	}
	
	public function primaryKey()
	{
	    return 'cat_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'category_name'=>t("Name"),		    
		    'category_description'=>t("Description"),		    
		    'status'=>t("Status"),	
		    'image'=>t("Featured Image"),	
		    'available_time_start[1]'=>t("From"),
		    'available_time_start[2]'=>t("From"),
		    'available_time_start[3]'=>t("From"),
		    'available_time_start[4]'=>t("From"),
		    'available_time_start[5]'=>t("From"),
		    'available_time_start[6]'=>t("From"),
		    'available_time_start[7]'=>t("From"),
		    'available_time_end[1]'=>t("To"),
		    'available_time_end[2]'=>t("To"),
		    'available_time_end[3]'=>t("To"),
		    'available_time_end[4]'=>t("To"),
		    'available_time_end[5]'=>t("To"),
		    'available_time_end[6]'=>t("To"),
		    'available_time_end[7]'=>t("To"),
		);
	}
	
	public function rules()
	{
		return array(
		  array('category_name,status', 
		  'required','message'=> t( Helper_field_required ) ),
		  		  
		  array('category_name,category_description,
		  status', 'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')), 		  
		  
		  array('photo,path,available_at_specific,sequence,available','safe'),
		  
		  array('category_name','length', 'min'=>0, 'max'=>255,
               'tooShort'=>t("category name is is too short"),
               'tooLong'=>t("category name is is too long"),                           
             ),

		  array('category_translation,category_description_translation,dish_selected','safe'),

		);
	}

    protected function beforeSave()
	{
		if(!parent::beforeSave()){
			return false;
		} 		
		
		if(DEMO_MODE && !$this->isNewRecord && in_array($this->merchant_id,DEMO_MERCHANT)){				
		    return false;
		}
				
		if($this->isNewRecord){
			$this->date_created = CommonUtility::dateNow();					
		} else {
			$this->date_modified = CommonUtility::dateNow();											
		}
		$this->ip_address = CommonUtility::userIp();	
		
		
		return true;
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
		Item_translation::deleteTranslation($this->cat_id,'cat_id','category_translation');		

		Yii::app()->db->createCommand("
		DELETE FROM {{item_relationship_category}}
		WHERE cat_id=".q($this->cat_id)."
		")->query();
				
		/*ADD CACHE REFERENCE*/
		CCacheData::add();
	}
		
}
/*end class*/
