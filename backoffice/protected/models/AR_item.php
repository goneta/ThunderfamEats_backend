<?php
class AR_item extends CActiveRecord
{	

	public $item_name_translation,$item_description_translation,$item_short_description_translation,
	$multi_language,$image,$category_selected,$item_price, $item_unit, $item_featured,
	$prices, $group_category,
	$discount,$discount_type,$discount_start,$discount_end,$discount_valid,$sequence,$is_bulk,$merchant_type,$cat_id
	;	

	public $restaurant_name,$item_uuid,$total_addon,$total_meta,$total_allergens,$dish;

	public $total_reviews,$average_rating,$is_favorite;
	
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
		return '{{item}}';
	}
	
	public function primaryKey()
	{
	    return 'item_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'item_name'=>t("Item Name"),		    
		    'item_description'=>t("Description"),
		    'status'=>t("Status"),	
		    'image'=>t("Featured Image"),
		    'sku'=>t("SKU"),
			'item_price'=>t("Item price"),
			'slug'=>t("Slug")
		);
	}
	
	public function rules()
	{
		return array(
		  array('item_name,status,category_selected', 
		  'required','message'=> t( Helper_field_required ), 'on'=> "create,update" ),
		  
		  array('item_name,status,photo', 
		  'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')), 		  
		  
		  array('item_description,item_name_translation,price,
		  item_description_translation,category_selected,photo,item_token,item_price,item_unit,track_stock,
		  supplier_id,item_short_description,sku,item_featured,available,color_hex,item_short_description_translation,is_bulk,
		  preparation_time,extra_preparation_time,unavailable_until
		  ','safe'),
		  
		  array('item_price', 'numerical', 'integerOnly' => false,		  
		  'message'=>t(Helper_field_numeric)),
		  
		  array('image', 'file', 'types'=> Helper_imageType, 'safe' => false,
			  'maxSize'=> Helper_maxSize,
			  'tooLarge'=>t(Helper_file_tooLarge),
			  'wrongType'=>t(Helper_file_wrongType),
			  'allowEmpty' => false,'on'=>'new','message'=>t(Helper_file_allowEmpty)
			),      
			
		 array('item_short_description', 'length', 'max'=>255,
              'tooShort'=>t(Helper_password_toshort) ,
              ),
		  		  
         array('sku','unique','message'=>t(Helper_field_unique)),

		 array('slug','unique','message'=>t(Helper_field_unique)),

		 array('item_name','ValidateCanAdd','on'=>"create"),
              
		);
	}

	public function ValidateCanAdd($attribute,$params)
	{	
		// PLANS	
		if($this->isNewRecord && $this->merchant_type==1){
			try {
			    Cplans::canAddItems($this->merchant_id);			
		    } catch (Exception $e) {
				$this->addError($attribute, $e->getMessage() );
			}
		}
	}

    protected function beforeSave()
	{
		if(!parent::beforeSave()){
			return false;
		} 
					
		if(DEMO_MODE && !$this->isNewRecord && in_array($this->merchant_id,DEMO_MERCHANT)){				
		    return false;
		}		
				
		$allowed_scenario = array('create','update');
		
		/*if($this->scenario=="item_inventory"){
		   $this->not_available = $this->not_available>0?$this->not_available:2;
		}*/
		
		/*
		if($this->scenario=="remove_image" || $this->scenario=="item_inventory"){
			return true;
		}*/

		if(!in_array($this->scenario,$allowed_scenario)){
			return true;
		}		
		
		/*if(is_array($this->item_name_translation) && count($this->item_name_translation)){
			$this->item_name_trans = json_encode($this->item_name_translation);				
		} else $this->item_name_trans='';
		
		if(is_array($this->item_description_translation) && count($this->item_description_translation)){
			$this->item_description_trans = json_encode($this->item_description_translation);				
		} else $this->item_description_trans='';
		
		if(is_array($this->category_selected) && count($this->category_selected)){
			$this->category = json_encode($this->category_selected);				
		} else $this->category='';*/
		
		if($this->isNewRecord){
			$this->date_created = CommonUtility::dateNow();	
			$this->item_token = CommonUtility::generateToken("{{item}}",'item_token', CommonUtility::generateAplhaCode(20) );
		} else {
			$this->date_modified = CommonUtility::dateNow();			
			if(empty($this->item_token)){
			$this->item_token = CommonUtility::generateToken("{{item}}",'item_token', CommonUtility::generateAplhaCode(20) );
			}
		}

		if(empty($this->slug)){
			$this->slug = $this->createSlug(CommonUtility::toSeoURL($this->item_name));
		}

		$this->ip_address = CommonUtility::userIp();	
						
		return true;
	}

	private function createSlug($slug='')
	{
		$stmt="SELECT count(*) as total FROM {{item}}
		WHERE slug=".q($slug)."
		";					
		if($res = Yii::app()->db->createCommand($stmt)->queryRow()){	
			if($res['total']>0){
				$new_slug = $slug.$res['total'];					
				return self::createSlug($new_slug);
			}
		}
		return $slug;
	}
	
	protected function afterSave()
	{
		parent::afterSave();
						
		Yii::import('ext.runactions.components.ERunActions');	
		$cron_key = CommonUtility::getCronKey();		
		$get_params = array( 
			'merchant_id'=> $this->merchant_id,
			'item_id'=> $this->item_id,
			'item_token'=> $this->item_token,
			'available'=>$this->available,
			'forsale'=>$this->not_for_sale,
			'available_at_specific'=>$this->available_at_specific,
			'key'=>$cron_key,
			'language'=>Yii::app()->language,
			'time'=>time()
		);				
		if($this->scenario=="update_item_available"){			
			CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/itemavailability?".http_build_query($get_params) );
		}

		$allowed_scenario = array('create','update');
		if(!in_array($this->scenario,$allowed_scenario)){
			CCacheData::add();
			return true;
		}		
				
		$merchant_id = (integer) $this->merchant_id;
				
		$name = $this->item_name_translation;
		$description = $this->item_description_translation;
		
		$name[KMRS_DEFAULT_LANGUAGE] = $this->item_name;
		$description[KMRS_DEFAULT_LANGUAGE] = $this->item_description;

		$item_short_description = $this->item_short_description_translation;
		$item_short_description[KMRS_DEFAULT_LANGUAGE] = $this->item_short_description;
								
		if($this->multi_language){	
			Item_translation::insertTranslation3( 
			(integer) $this->item_id ,
			'item_id',
			'item_name',
			'item_description',
			'item_short_description',
			array(	                  
			  'item_name'=>(array)$name,	  
			  'item_description'=>(array)$description,
			  'item_short_description'=>(array)$item_short_description,
			),"{{item_translation}}");
		}
		
		/*MEDIA*/
		if($this->image){
			$media = new AR_media;
			$media->merchant_id = (integer) $merchant_id;
			$media->title = $this->image->name;
			$media->filename = $this->photo;
			$media->path = CommonUtility::uploadPath(false);
			$media->size = $this->image->size;
			$media->media_type = $this->image->type;
			$media->date_created = CommonUtility::dateNow();
			$media->ip_address = CommonUtility::userIp();
			$media->save();
		}
		
		/*ITEM RELATIONSHIP*/		
		$params_category = [];		
		$existingRelationships = CommonUtility::getItemRelationshipCategory($this->item_id);		
		if(!empty($this->category_selected)){
			if(is_array($this->category_selected) && count($this->category_selected)){
				foreach ($this->category_selected as $cat_id) {								
					if (isset($existingRelationships[$cat_id])) {					
						unset($existingRelationships[$cat_id]);
					} else {
						$last_sequence = CommonUtility::getMaxSequenceRelatinshipCategory($merchant_id,$cat_id);				
						$params_category[]=array(
							'merchant_id'=>(integer)$merchant_id,
							'item_id'=>(integer)$this->item_id,
							'cat_id'=>(integer)$cat_id,
							'sequence'=>$last_sequence
						);					
					}				
				}
		    }
			// dump("INSERT");
			// dump($params_category);
			if(is_array($params_category) && count($params_category)>=1){
				$builder=Yii::app()->db->schema->commandBuilder;
		        $command=$builder->createMultipleInsertCommand('{{item_relationship_category}}',$params_category);
		        $command->execute();			
			}

			// dump("existingRelationships");
			// dump($existingRelationships);
			$ids_delete = [];
			if(is_array($existingRelationships) && count($existingRelationships)>=1 ){
				foreach ($existingRelationships as $key => $value) {
					$ids_delete[] = $value['id'];					
				}
				CommonUtility::deleteAllRelationshipCategory($ids_delete);
			}
		} else {
			Yii::app()->db->createCommand("DELETE FROM {{item_relationship_category}}
		     WHERE item_id=".q($this->item_id)."
		    ")->query();
		}		

		// PLANS
		if($this->isNewRecord && $this->merchant_type==1){
			Cplans::UpdateItemsAdded($this->merchant_id);			
		}
		
		/*INSERT SIZE*/		
		if($this->isNewRecord){
			$item_size = new AR_item_size;
			$item_size->merchant_id = (integer)$merchant_id;			
			$item_size->item_id = (integer)$this->item_id;
			$item_size->price = (float)$this->item_price;		
			if(!empty($this->item_unit)){
				$item_size->size_id = (integer)$this->item_unit;
			} 	
			if(empty($this->is_bulk)){
				$item_size->save();
			}			
		}
		
		/*DELETE META*/
		Yii::app()->db->createCommand("DELETE FROM {{item_meta}}
		WHERE item_id=".q($this->item_id)."
		AND merchant_id = ".q($merchant_id)."
		AND meta_name IN ('item_featured')
		 ")->query();
					
		$item_featured = array();	
		if($this->item_featured){						
			foreach ($this->item_featured as $id) {
				$params[]=array(
				  'merchant_id'=>(integer)$merchant_id,
				  'item_id'=>(integer)$this->item_id,
				  'meta_name'=>'item_featured',
				  'meta_id'=>$id
				);
			}		
			$builder=Yii::app()->db->schema->commandBuilder;
			$command=$builder->createMultipleInsertCommand('{{item_meta}}',$params);
			$command->execute();		
		}
		
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

		// PLANS				
		if($this->merchant_type==1){
			Yii::app()->db->createCommand("
			UPDATE {{merchant}}
			SET items_added = items_added-1
			WHERE 
			merchant_id = ".q($this->merchant_id)."
			")->query();		
		}		

		$sql_delete = "
        SET @merchant_id = ".q($this->merchant_id).";
		SET @item_id = ".q($this->item_id).";

		delete from {{item}}
        where merchant_id = @merchant_id and item_id = @item_id;

		delete from {{item_translation}}
        where merchant_id = @merchant_id and item_id = @item_id;      

		delete from {{item_meta}}
        where merchant_id = @merchant_id and item_id = @item_id;      

		delete from {{item_relationship_category}}
        where merchant_id = @merchant_id and item_id = @item_id;      

		delete from {{item_relationship_size}}
        where merchant_id = @merchant_id and item_id = @item_id;      

		delete from {{item_relationship_subcategory}}
        where merchant_id = @merchant_id and item_id = @item_id;      

		delete from {{item_relationship_subcategory_item}}
        where merchant_id = @merchant_id and item_id = @item_id;      

		delete from {{availability}}
        where merchant_id = @merchant_id and meta_value = @item_id;   
		
		delete from {{item_promo}}
        where merchant_id = @merchant_id and item_id = @item_id;        
		";
		Yii::app()->db->createCommand($sql_delete)->query();		
		
		/*ADD CACHE REFERENCE*/
		CCacheData::add();
	}
		
}
/*end class*/
