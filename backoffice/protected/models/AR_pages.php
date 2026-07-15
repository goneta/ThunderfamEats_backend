<?php
class AR_pages extends CActiveRecord
{	
	   			
	public $multi_language, $title_translation, $long_content_translation,$image;
	public $page_privacy_policy,$page_terms,$page_aboutus;

	public $merchant_page_privacy_policy,$merchant_page_terms,$merchant_page_aboutus;

	public $kitchen_page_privacy_policy,$kitchen_page_terms,$kitchen_page_aboutus;
	
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
		return '{{pages}}';
	}
	
	public function primaryKey()
	{
	    return 'page_id';	 
	}
		
	public function attributeLabels()
	{
		return array(		    
		  'meta_image'=>t("Meta Image"),
		  'title'=>t("Title"),
		  'meta_title'=>t("Meta Title"),
		  'meta_description'=>t("Meta Description"),
		  'meta_keywords'=>t("Meta Keywords"),
		  'page_privacy_policy'=>t("Privacy Policy"),
		  'page_terms'=>t("Terms and condition"),
		  'page_aboutus'=>t("About us"),
		  'kitchen_page_privacy_policy'=>t("Privacy Policy"),
		  'kitchen_page_terms'=>t("Terms and condition"),
		  'kitchen_page_aboutus'=>t("About us"),
		);
	}
	
	public function rules()
	{
		return array(
		  array('slug,title,long_content,short_content', 
		  'required','message'=> t( Helper_field_required ) ),
		  
		  array('slug,title,long_content,status, short_content,meta_title,meta_description,meta_keywords,
		  title_translation,long_content_translation
		  ', 'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')), 
		  		  
		  array('slug','unique','message'=>t(Helper_field_unique)),
		  
		  array('title_translation,long_content_translation,
		  page_privacy_policy,page_terms,page_aboutus,
		  merchant_page_privacy_policy,merchant_page_terms,merchant_page_aboutus,
		  kitchen_page_privacy_policy,kitchen_page_terms,kitchen_page_aboutus
		  ','safe'),
		  
		  array('title,short_content,meta_title','length','max'=>255)
		  
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
				
		$name = array(); $description = array();
			
		$name = $this->title_translation;
		$description = $this->long_content_translation;

		if(!is_array($name) && count((array)$name)<=1){
			$name = array();			
		}
		if(!is_array($description) && count((array)$description)<=1){
			$description = array();
		}
			
		$name[KMRS_DEFAULT_LANGUAGE] = $this->title;
		$description[KMRS_DEFAULT_LANGUAGE] = $this->long_content;				
		
		Item_translation::insertTranslation( 
		(integer) $this->page_id ,
		'page_id',
		'title',
		'long_content',
		array(	                  
		  'title'=>$name,
		  'long_content'=>$description
		),"{{pages_translation}}",true);
		
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
		
		Item_translation::deleteTranslation($this->page_id,'page_id','pages_translation');
		
		/*ADD CACHE REFERENCE*/
		CCacheData::add();
	}
		
}
/*end class*/
