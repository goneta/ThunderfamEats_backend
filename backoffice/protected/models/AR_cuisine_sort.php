<?php
class AR_cuisine_sort extends CActiveRecord
{	
	   		
	public $image;
	public $cuisine_name_translation;
	public $multi_language;
	
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
		return '{{cuisine}}';
	}
	
	public function primaryKey()
	{
	    return 'cuisine_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'cuisine_name'=>t("Cuisine Name"),
		    'image'=>t("Featured Image"),
		);
	}
	
	public function rules()
	{
		return array(
		  array('cuisine_name,status', 
		  'required','message'=> t( Helper_field_required ) ),
		  array('cuisine_name', 'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')), 
		  array('cuisine_name_translation,color_hex,font_color_hex,slug','safe'),
		  array('image', 'file', 'types'=>Helper_imageType, 'safe' => false,
			  'maxSize'=>Helper_maxSize,
			  'tooLarge'=>t(Helper_file_tooLarge),
			  'wrongType'=>t(Helper_file_wrongType),
			  'allowEmpty' => true
			),      
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
