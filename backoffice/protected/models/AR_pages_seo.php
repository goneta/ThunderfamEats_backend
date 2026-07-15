<?php
class AR_pages_seo extends CActiveRecord
{	
	   			
	public $meta_title_translation,$meta_description_translation,$meta_keywords_translation;
	
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
		  'title'=>t("Page"),
		  'meta_title'=>t("SEO Title"),
		  'meta_description'=>t("Meta Description"),
		  'meta_keywords'=>t("Meta Keywords"),
		  'page_privacy_policy'=>t("Privacy Policy"),
		  'page_terms'=>t("Terms and condition"),
		  'page_aboutus'=>t("About us"),          
		);
	}
	
	public function rules()
	{
		return array(
		  array('title,meta_title,meta_description,slug', 
		  'required','message'=> t( Helper_field_required ) ),
		  
		  array('meta_title,meta_description', 'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')), 
		  		  
		  array('slug','unique','message'=>t(Helper_field_unique)),
		  
		  array('date_created,date_modified,ip_address,status,meta_title_translation,meta_description_translation,meta_keywords_translation
          ','safe'),
		  
		  array('meta_title,meta_keywords','length','max'=>255)
		  
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
                $this->owner = !empty($this->owner)? $this->owner :  "seo";				
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
						
        $name = array(); $description = array(); $keywords = array();

        $name = $this->meta_title_translation;
		$description = $this->meta_description_translation;
        $keywords = $this->meta_keywords_translation;

		if(!is_array($name) && count((array)$name)<=1){
			$name = array();			
		}
		if(!is_array($description) && count((array)$description)<=1){
			$description = array();
		}
        if(!is_array($keywords) && count((array)$keywords)<=1){
			$keywords = array();
		}
			
		$name[KMRS_DEFAULT_LANGUAGE] = $this->meta_title;
		$description[KMRS_DEFAULT_LANGUAGE] = $this->meta_description;		
        $keywords[KMRS_DEFAULT_LANGUAGE] = $this->meta_keywords;
        
		Item_translation::insertTranslation3( 
		(integer) $this->page_id,
		'page_id',
		'meta_title',
		'meta_description',
        'meta_keywords',
		array(	                  
		  'meta_title'=>(array)$name,
		  'meta_description'=>(array)$description,
          'meta_keywords'=>(array)$keywords
		),"{{pages_translation}}"); 
			
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
