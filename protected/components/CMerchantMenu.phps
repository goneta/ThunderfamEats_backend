<?php
class CMerchantMenu
{
	public static $parameters;
	private static $exchange_rate;	
	private static $points_enabled;	
	private static $points_earning_rule;	
	private static $points_earning_points;	

	private static $admin_exchange_rate;	
	
	public static function setParameters($parameters=array())
	{
		self::$parameters = $parameters;
	}
	
	public static function getParameters()
	{
		return self::$parameters;
	}
	
	public static function getCategory($merchant_id='',$lang=KMRS_DEFAULT_LANGUAGE)
	{		
		$stmt = "
		SELECT 
		a.merchant_id,a.cat_id,
		a.photo, a.path, a.icon, a.icon_path,
		a.category_name as original_category_name , 
		a.category_description as original_category_description,
		b.category_name,
		b.category_description,

		IFNULL((
			 select GROUP_CONCAT(DISTINCT item_id ORDER BY sequence ASC SEPARATOR ',')
			 from {{item_relationship_category}}
			 where merchant_id = a.merchant_id
			 and cat_id = a.cat_id
			 and item_id in (
			    select item_id from {{item}}
			    where status='publish'
			    and available = 1
			 )
		),'') as items

		FROM {{category}} a 

		left JOIN (
		  SELECT cat_id, category_name, category_description FROM 
		  {{category_translation}} 
		  where language = ".q($lang)."
		) b 
		on a.cat_id = b.cat_id
		WHERE a.merchant_id = ".q($merchant_id)."
		AND a.status='publish'
		ORDER BY a.sequence, a.category_name ASC
		";				
		$dependency = CCacheData::dependency();					
        if($res = Yii::app()->db->cache(Yii::app()->params->cache, $dependency)->createCommand($stmt)->queryAll()){			
			$data = array();
			foreach ($res as $val) {				
				$items = explode(",",$val['items']);				
				$first_item = isset($items[0])?$items[0]:'';				
				if($first_item>0){
					$category_name = empty($val['category_name'])? CHtml::decode($val['original_category_name']) : CHtml::decode($val['category_name']);
					$data[] = array(
					'cat_id'=>$val['cat_id'],
					'category_uiid'=>CommonUtility::toSeoURL($category_name),
					'category_name'=> $category_name,
					'category_description'=> empty($val['category_description']) ? CHtml::decode($val['original_category_description']) : CHtml::decode($val['category_description']),
					'url_image'=>CMedia::getImage($val['photo'],$val['path'],Yii::app()->params->size_image_thumbnail
					,CommonUtility::getPlaceholderPhoto('item')),					
					'url_icon'=>CMedia::getImage($val['icon'],$val['icon_path'],Yii::app()->params->size_image_thumbnail
					,CommonUtility::getPlaceholderPhoto('icon')),
					'items'=>$items
					);
				}
			}
			return $data;
		}
		throw new Exception( 'no results' );
	}

	public static function getCategory2($merchant_id='',$lang=KMRS_DEFAULT_LANGUAGE , $sort = "ORDER BY sequence, category_name ASC")
	{
		
		$stmt="
		SELECT a.merchant_id,a.cat_id,
		a.photo, a.path, a.icon, a.icon_path,
		b.category_name,
		b.category_description,
		
		IFNULL((
		 select GROUP_CONCAT(DISTINCT item_id ORDER BY sequence ASC SEPARATOR ',')
		 from {{item_relationship_category}}
		 where merchant_id = a.merchant_id
		 and cat_id = a.cat_id
		 and item_id in (
		    select item_id from {{item}}		    
		 )
		),'') as items
		
		FROM {{category}} a
		LEFT JOIN {{category_translation}} b
		ON
		b.cat_id = a.cat_id
		
		WHERE a.merchant_id = ".q($merchant_id)."		
		AND b.language = ".q($lang)."
		ORDER BY a.sequence, a.category_name ASC
		";								
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){			
			$data = array();
			foreach ($res as $val) {							
				$items = !empty($val['items'])? explode(",",$val['items']) : "";												
				$data[] = array(
					'cat_id'=>$val['cat_id'],
					'category_uiid'=>CommonUtility::toSeoURL($val['category_name']),
					'category_name'=>CHtml::decode($val['category_name']),
					'category_description'=>CHtml::decode($val['category_description']),
					'url_image'=>CMedia::getImage($val['photo'],$val['path'],Yii::app()->params->size_image_thumbnail
					,CommonUtility::getPlaceholderPhoto('item')),
					
					'url_icon'=>CMedia::getImage($val['icon'],$val['icon_path'],Yii::app()->params->size_image_thumbnail
					,CommonUtility::getPlaceholderPhoto('icon')),

					'items'=>$items
				);				
			}
			return $data;
		}
		throw new Exception( 'no results' );
	}
	
	public static function getMenu($merchant_id='',$lang='en')
	{
		
		$exchange_rate = self::getExchangeRate();		

		$stmt = "		
		SELECT 
		a.merchant_id,
		a.item_id, 
		a.slug, 
		a.item_token,
		a.photo,
		a.path,
		a.item_name as original_item_name,
		a.item_short_description as original_item_short_description,
		b.item_name,
		b.item_description,

		(
			select GROUP_CONCAT(f.size_uuid,';',f.price,';', IF(f.size_name='',f.original_size_name,f.size_name) ,';',f.discount,';',f.discount_type,';',
			(
			select count(*) from {{view_item_lang_size}}
			where item_id = a.item_id 
			and size_uuid = f.size_uuid
			and CURDATE() >= discount_start and CURDATE() <= discount_end
			),';',f.item_size_id
		)
		
		from {{view_item_lang_size}} f
			where 
			item_id = a.item_id
			and language IN('',".q($lang).")
		) as prices,
		
		(
			select count(*) from {{item_relationship_subcategory}}
			where item_id = a.item_id 
			and item_size_id > 0 and subcat_id > 0
		) as total_addon,
		
		(
			select count(*) from {{item_meta}}
			where item_id = a.item_id 		
			and meta_name not in ('delivery_options','dish','delivery_vehicle','item_gallery')
		) as total_meta,

		(
			select count(*) from {{item_meta}}
			where item_id = a.item_id 		
			and meta_name in ('allergens')
		) as total_allergens

		FROM {{item}} a 

		left JOIN (
		   SELECT item_id, item_name,item_description 
		   FROM {{item_translation}} 
		   where language = ".q($lang)."
		) b 
		ON a.item_id = b.item_id
		WHERE a.merchant_id = ".q($merchant_id)."
		AND a.status='publish'
		AND a.available=1
		";						
		$dependency = CCacheData::dependency();					
        if($res = Yii::app()->db->cache(Yii::app()->params->cache, $dependency)->createCommand($stmt)->queryAll()){			
			$data = array();
			foreach ($res as $val) {				
				$price = array();
				$prices = explode(",",$val['prices']);				
				if(is_array($prices) && count($prices)>=1){
					foreach ($prices as $pricesval) {
						$sizes = explode(";",$pricesval);							
						$item_price = isset($sizes[1])?(float)$sizes[1]:0;
						$item_price = ($item_price*$exchange_rate);
						$item_discount = isset($sizes[3])?(float)$sizes[3]:0;
						$discount_type = isset($sizes[4])?$sizes[4]:'';
						$discount_valid = isset($sizes[5])?(integer)$sizes[5]:0;						
												
						$price_after_discount=0;
						if($item_discount>0 && $discount_valid>0){
							if($discount_type=="percentage"){
								$price_after_discount = $item_price - (($item_discount/100)*$item_price);
							} else {
								$price_after_discount = $item_price - ($item_discount*$exchange_rate);
							}
						
						} else $item_discount = 0;
						
						$price[] = array(
						  'size_uuid'=>isset($sizes[0])?$sizes[0]:'',
						  'item_size_id'=>isset($sizes[6])?$sizes[6]:'',
						  'price'=>$item_price,
						  'size_name'=>isset($sizes[2])?$sizes[2]:'',
						  'discount'=>$item_discount,
						  'discount_type'=>$discount_type,
						  'price_after_discount'=>$price_after_discount,
						  'pretty_price'=>Price_Formatter::formatNumber($item_price),
						  'pretty_price_after_discount'=>Price_Formatter::formatNumber($price_after_discount),
						);
					}
				}
				
				$data[$val['item_id']] = array(  
				  'item_id'=>$val['item_id'],
				  'item_uuid'=>$val['item_token'],
				  'slug'=>$val['slug'],
				  'item_name'=> empty($val['item_name']) ? CHtml::decode($val['original_item_name']) : CHtml::decode($val['item_name']),
				  'item_description'=> empty($val['item_description']) ? CommonUtility::formatShortText($val['original_item_short_description'],130) : CommonUtility::formatShortText($val['item_description'],130),
				  'has_photo'=>!empty($val['photo'])?true:false,
				  'url_image'=>CMedia::getImage($val['photo'],$val['path'],Yii::app()->params->size_image
				  ,CommonUtility::getPlaceholderPhoto('item')),
				  'price'=>$price,
				  'total_addon'=>(integer)$val['total_addon'],
				  'total_meta'=>(integer)$val['total_meta'],
				  'total_allergens'=>intval($val['total_allergens']),
				  'qty'=>0
				);
			}
			return $data;
		}
		throw new Exception( 'no results' );
	}			

	public static function getItemPrice($item_id=0, $lang=KMRS_DEFAULT_LANGUAGE, $item_points = 1)
	{		
		$exchange_rate = self::getExchangeRate();
		$points_enabled = self::getEnabledPoints();
		$points_earning_rule = self::getPointsRule();
		$points_earning_points = self::getEarningPoints();			
		$admin_exchange_rate =  self::getAdminExchangeRate();

		$stmt = "		
		SELECT
		a.item_size_id,
		a.item_token as size_uuid,
		a.price,
		b.size_name as original_size_name,
		c.size_name,
		a.discount,
		a.discount_type,
		if(CURDATE() >= a.discount_start and CURDATE() <= a.discount_end,1,0) as discount_valid

		from {{item_relationship_size}} a
		left join st_size b
		ON
		a.size_id = b.size_id

		LEFT JOIN (
		SELECT size_id, size_name FROM {{size_translation}} where language = ".q($lang)."
		) c
		ON a.size_id = c.size_id

		WHERE a.item_id = ".q($item_id)."
		";		
		$dependency = CCacheData::dependency();			
		if($res = Yii::app()->db->cache(Yii::app()->params->cache, $dependency)->createCommand($stmt)->queryAll()){			
			$data = [];
			foreach ($res as $key => $items) {
				$item_price = floatval($items['price']) * $exchange_rate; 
				$item_discount = floatval($items['discount']); 
				$discount_valid = floatval($items['discount_valid']); 
				$discount_type = trim($items['discount_type']); 

				$price_after_discount=0;
				if($item_discount>0 && $discount_valid>0){
						if($discount_type=="percentage"){
							$price_after_discount = $item_price - (($item_discount/100)*$item_price);
						} else $price_after_discount = $item_price - ($item_discount*$exchange_rate);
					
				} else $item_discount = 0;

				
				$earning_points = 0;
				if($points_enabled && $points_earning_points>0){						
					$earning_points = $points_earning_rule=='food_item'?$item_points: (($item_price*$admin_exchange_rate)*$points_earning_points);					
					$earning_points = Price_Formatter::convertToRaw($earning_points,0);					
				}								

				$size_name = empty($items['size_name'])?$items['original_size_name']:$items['size_name'];
				$data[$items['item_size_id']] = [
					'key'=>$key,
					'size_uuid'=>$items['size_uuid'],
					'item_size_id'=>$items['item_size_id'],
					'price'=>floatval($items['price'])* $exchange_rate,
					'size_name'=>!empty($size_name)?$size_name:'',
					'discount'=>floatval($item_discount),
					'discount_type'=>trim($items['discount_type']),
					'price_after_discount'=>$price_after_discount,
					'pretty_price'=>Price_Formatter::formatNumber($item_price),
					'pretty_price_after_discount'=>Price_Formatter::formatNumber($price_after_discount),
					'points_enabled'=>$points_enabled,
					'earning_points'=>$earning_points,
					'earning_points_label'=>$earning_points>0?t("Buy this item to earn {points} Points.",['{points}'=>$earning_points]):'',
				];
			}
			return $data;
		}
		return false;
	}
	
	public static function getMenuItem($merchant_id='', $cat_id='', $item_uuid='',$lang=KMRS_DEFAULT_LANGUAGE)
	{		
		$stmt = "
		SELECT a.item_id, a.item_name as original_item_name, 
		a.item_description as original_item_description,
		a.item_short_description as original_item_short_description,
		 a.photo,a.path, a.item_token, a.cooking_ref_required, a.not_for_sale, a.ingredients_preselected,a.points_earned,		
		b.item_name,b.item_description,	
				
		IFNULL((
		select GROUP_CONCAT(DISTINCT g.item_size_id,';', 
		
			(
			select GROUP_CONCAT(subcat_id ORDER BY sequence ASC)
			from {{item_relationship_subcategory}}
			where item_id = a.item_id
			and item_size_id = g.item_size_id
		)
			SEPARATOR '|'
		)
		from {{item_relationship_subcategory}} g
		where item_id  = a.item_id
		and item_size_id <> 0
		order by g.id ASC
		),'') as addons

		FROM {{item}} a
		left JOIN (
			SELECT item_id,item_name,item_description FROM {{item_translation}} where language = ".q($lang)."
		) b 
		on a.item_id = b.item_id
		
		WHERE merchant_id =	".q($merchant_id)."	
		AND a.item_token=".q($item_uuid)."
		AND a.available = 1
		LIMIT 0,1
		";						
		$dependency = CCacheData::dependency();		
		if($item = Yii::app()->db->cache(Yii::app()->params->cache, $dependency)->createCommand($stmt)->queryRow()){		   										
			$price = [];			
			if($get_price = self::getItemPrice($item['item_id'],$lang, $item['points_earned'])){
				$price = $get_price;
			}					

			$addons = array(); 
			$addon = !empty($item['addons'])? explode("|",$item['addons']) : '';
			if(is_array($addon) && count($addon)>=1){
				foreach ($addon as $addon_val) {
					$itemsizeid = explode(";",$addon_val);					
					$item_size_id = isset($itemsizeid[0])?(integer)$itemsizeid[0]:0;
					$subcategory = isset($itemsizeid[1])?$itemsizeid[1]:'';
					$subcategory1 = explode(",",$subcategory);					
					$addons[$item_size_id] = $subcategory1;
				}
			}
			
			$item_name = empty($item['item_name'])? Yii::app()->input->xssClean($item['original_item_name']) : Yii::app()->input->xssClean($item['item_name']);
			$item_description = empty($item['item_description'])? Yii::app()->input->xssClean($item['original_item_description']) : Yii::app()->input->xssClean($item['item_description']);
			return array(
			  'merchant_id'=>$merchant_id,
			  'item_id'=>$item['item_id'],
			  'item_token'=>$item['item_token'],
			  'cat_id'=>$cat_id,
			  'item_name'=>$item_name,
			  'item_description'=>$item_description,			  
			  'url_image'=>CMedia::getImage($item['photo'],$item['path'],"@2x",
				CommonUtility::getPlaceholderPhoto('item')),
			  'cooking_ref_required'=>$item['cooking_ref_required']==1?true:false,
			  'ingredients_preselected'=>$item['ingredients_preselected']==1?true:false,
			  'not_for_sale'=>$item['not_for_sale']==1?true:false,
			  'price'=>$price,
			  'item_addons'=>$addons
			);			
		}
		throw new Exception( 'no results' );
	}
	
	public static function getItemAddonCategory($merchant_id='', $item_uuid='',$lang = KMRS_DEFAULT_LANGUAGE)
	{		
		$data = array();
		$stmt="
		SELECT a.subcat_id,
		a.subcategory_name as original_subcategory_name ,
		a.subcategory_description as original_subcategory_description ,
		b.subcategory_name,		
		b.subcategory_description,
		c.multi_option,c.multi_option_min,c.multi_option_value,c.require_addon,c.pre_selected,
		c.item_size_id,c.id as size_primary_id,
		
		(
		select GROUP_CONCAT(sub_item_id ORDER BY sequence ASC)
		from {{subcategory_item_relationships}}
		where subcat_id = a.subcat_id		
		) as sub_items
		
		
		FROM {{subcategory}} a
		left JOIN (
			SELECT subcat_id, subcategory_name, subcategory_description FROM {{subcategory_translation}} where language = ".q($lang)."
		) b 

		ON
		a.subcat_id = b.subcat_id
		
		LEFT JOIN {{view_item_relationship_subcategory}} c
		ON
		a.subcat_id = c.subcat_id
		
		WHERE a.merchant_id = ".q($merchant_id)."	
		AND a.status = 'publish'					
		AND c.item_token =".q($item_uuid)."
		ORDER BY c.id ASC		
		";						
		$dependency = CCacheData::dependency();					
		if($res = Yii::app()->db->cache(Yii::app()->params->cache, $dependency)->createCommand($stmt)->queryAll()){		   		   
		   foreach ($res as $val) {		
		   	    $sub_items = !empty($val['sub_items'])? explode(",",$val['sub_items']) : '';		   	    	   		 
			    $multi_option_value = intval($val['multi_option_value']);				
				if($val['multi_option']=="multiple" && $multi_option_value<=0){					
					$multi_option_value = 1;
				}
		   		$data[$val['item_size_id']][$val['subcat_id']] = array(		   		
		   		  'subcat_id'=>$val['subcat_id'],
		   		  'subcategory_name'=> empty($val['subcategory_name'])? Yii::app()->input->xssClean($val['original_subcategory_name']) : Yii::app()->input->xssClean($val['subcategory_name']),
		   		  'subcategory_description'=> empty($val['subcategory_description'])? Yii::app()->input->xssClean($val['original_subcategory_description']) :  Yii::app()->input->xssClean($val['subcategory_description']),
		   		  'multi_option'=>$val['multi_option'],
				  'multi_option_min'=>intval($val['multi_option_min']),
		   		  'multi_option_value'=>$multi_option_value,
		   		  'require_addon'=>$val['require_addon'],
		   		  'pre_selected'=>$val['pre_selected'],
		   		  'sub_items'=>$sub_items
		   		);
		   	}			   	
		   	return $data;
		}		 
		return false;
	}
	
	public static function getAddonItems($merchant_id='', $item_uuid='',$lang = KMRS_DEFAULT_LANGUAGE)
	{
		$exchange_rate = self::getExchangeRate();
		$stmt="
		SELECT 
		a.sub_item_id,
		a.sub_item_name as original_sub_item_name,
		a.item_description as original_item_description,
		b.sub_item_name, 
		b.item_description,
		a.price, a.photo,a.path
		
		FROM {{subcategory_item}} a		
		left JOIN (
			SELECT id,sub_item_id, sub_item_name, item_description FROM {{subcategory_item_translation}} where language = ".q($lang)."
		) b 		
		ON
		a.sub_item_id = b.sub_item_id
		WHERE a.merchant_id = ".q($merchant_id)."	
		AND a.status = 'publish'				
		AND a.sub_item_id IN (		  
		  select sub_item_id from {{view_item_relationship_subcategory_item}}
		  where merchant_id = ".q($merchant_id)."
		  and item_token=".q($item_uuid)."
		)
		ORDER BY a.sequence,b.id ASC
		";				
		$dependency = CCacheData::dependency();
		$res = Yii::app()->db->cache(Yii::app()->params->cache, $dependency)->createCommand($stmt)->queryAll();		
		if($res){			
			$data = array();
			foreach ($res as $val) {	
				$sub_item_id = (integer) $val['sub_item_id'];
				$data[$sub_item_id] = array(
				  'sub_item_id'=>$sub_item_id,
				  'sub_item_name'=> empty($val['sub_item_name'])? Yii::app()->input->xssClean($val['original_sub_item_name']) : Yii::app()->input->xssClean($val['sub_item_name']),
				  'item_description'=> empty($val['item_description'])? Yii::app()->input->xssClean($val['original_item_description']) : Yii::app()->input->xssClean($val['item_description']),
				  'price'=>floatval($val['price']) * $exchange_rate,
				  'pretty_price'=>Price_Formatter::formatNumber( (floatval($val['price'])*$exchange_rate) ),				  
				  'url_image'=>CMedia::getImage($val['photo'],$val['path'],Yii::app()->params->size_image,
				   CommonUtility::getPlaceholderPhoto('item')),
				   'hasimage'=>!empty($val['photo'])?true:false
				);
			}
			return $data;
		}
		return false;
	}
	
	public static function getItemMeta($merchant_id='', $item_uuid='')
	{
		$stmt="
		SELECT a.id, a.merchant_id, a.item_id, 
		a.meta_name, a.meta_id,a.meta_value
		FROM {{item_meta}} a
		WHERE 
		meta_name IN ('ingredients','cooking_ref','dish','item_gallery')
		AND a.item_id IN (
		  select item_id from {{item}}
		  where item_token = ".q($item_uuid)."
		  AND merchant_id = ".q( (integer) $merchant_id )."
		)
		ORDER BY a.id ASC
		";							
		$dependency = CCacheData::dependency();					
        if($res = Yii::app()->db->cache(Yii::app()->params->cache, $dependency)->createCommand($stmt)->queryAll()){		   			
			$data = array();
			foreach ($res as $val){				
				if($val['meta_name']=="item_gallery"){
					$data[$val['meta_name']][] = CMedia::getImage($val['meta_id'],$val['meta_value'],Yii::app()->params->size_image,CommonUtility::getPlaceholderPhoto('item'));
				} else $data[$val['meta_name']][] = $val['meta_id'];				
			}	
			return $data;	
		}		
		return false;
	}

	public static function getItemMeta2($merchant_id='', $item_id='')
	{
		$stmt="
		SELECT a.id, a.merchant_id, a.item_id, 
		a.meta_name, a.meta_id,a.meta_value		
		FROM {{item_meta}} a
		WHERE 
		a.meta_name IN ('ingredients','cooking_ref','dish','item_gallery')
		AND a.merchant_id=".q($merchant_id)."
		AND a.item_id =".q($item_id)."
		ORDER BY a.id ASC
		";		
		$dependency = CCacheData::dependency();					
        if($res = Yii::app()->db->cache(Yii::app()->params->cache, $dependency)->createCommand($stmt)->queryAll()){		   			
			$data = array();
			foreach ($res as $val){								
				if($val['meta_name']=="item_gallery"){
					$data[$val['meta_name']][] = CMedia::getImage($val['meta_id'],$val['meta_value'],Yii::app()->params->size_image,CommonUtility::getPlaceholderPhoto('item'));
				} else $data[$val['meta_name']][] = $val['meta_id'];				
			}	
			return $data;	
		}		
		return false;
	}	
	
	public static function getMeta($merchant_id='', $item_uuid='',$lang= KMRS_DEFAULT_LANGUAGE )
	{
		$stmt="
		SELECT 'cooking_ref' as meta_type,a.merchant_id,a.cook_id as meta_id,
		a.cooking_name as original_meta_name,
		b.cooking_name as meta_name
		FROM {{cooking_ref}} a		
		left JOIN (
			SELECT cook_id,cooking_name FROM {{cooking_ref_translation}} where language = ".q($lang)."
		) b 		
		ON a.cook_id = b.cook_id
		
		WHERE a.status = 'publish' 		
		AND a.merchant_id = ".q($merchant_id)."
		AND a.cook_id IN (
		  select meta_id from {{item_meta}}
		  where meta_name='cooking_ref'
		  and item_id IN (
		    select item_id from {{item}}
		    where item_token = ".q($item_uuid)."		  
		  )
		)
		
		UNION ALL
		
		SELECT 'ingredients' as meta_type,a.merchant_id,a.ingredients_id as meta_id,
		a.ingredients_name as original_meta_name,
		b.ingredients_name as meta_name
		FROM {{ingredients}} a		
		left JOIN (
			SELECT ingredients_id,ingredients_name FROM {{ingredients_translation}} where language = ".q($lang)."
		) b 				
		ON
		a.ingredients_id = b.ingredients_id
		
		WHERE a.status = 'publish' 		
		AND a.merchant_id = ".q($merchant_id)."
		AND a.ingredients_id IN (
		  select meta_id from {{item_meta}}
		  where meta_name='ingredients'
		  and item_id IN (
		    select item_id from {{item}}
		    where item_token = ".q($item_uuid)."		  
		  )
		)
		
		
		UNION ALL
		
		SELECT 'dish' as meta_type,'',a.dish_id as meta_id,
		a.dish_name as original_meta_name,
		b.dish_name  as meta_name
		FROM {{dishes}} a		
		LEFT JOIN {{dishes_translation}} b
		ON
		a.dish_id = b.dish_id
		
		WHERE a.status = 'publish' 
		AND b.language = ".q($lang)."		
		AND a.dish_id IN (
		  select meta_id from {{item_meta}}
		  where meta_name='dish'
		  and item_id IN (
		    select item_id from {{item}}
		    where item_token = ".q($item_uuid)."		  
		    AND merchant_id = ".q( (integer) $merchant_id )."
		  )
		)
		";					
		$dependency = CCacheData::dependency();		
		if($res = Yii::app()->db->cache(Yii::app()->params->cache, $dependency)->createCommand($stmt)->queryAll()){
			$data = array();
			foreach ($res as $val){								
				$data[$val['meta_type']][$val['meta_id']] = array(
				  'meta_id'=>$val['meta_id'],
				  'meta_name'=> empty($val['meta_name']) ? $val['original_meta_name'] : $val['meta_name'],
				);
			}				
			return $data;	
		}		
		return false;		
	}
	
	
	public static function CategoryItem($merchant_id=0,$cat_id=0,$search='',$page=0,$lang=KMRS_DEFAULT_LANGUAGE)
	{
	
	    $criteria=new CDbCriteria();	
	    $criteria->alias = "a";	    		    		
		$criteria->select = "
		a.merchant_id, a.item_id, a.item_token,a.photo,a.path,
		a.item_name ,
		a.item_description,
		b.item_name as item_name_translation, 
		b.item_description as item_description_translation,
		c.sequence as sequence,
		(
			select GROUP_CONCAT(f.size_uuid,';',f.price,';',f.size_name,';',f.discount,';',f.discount_type,';',
			 (
			  select count(*) from {{view_item_lang_size}}
			  where item_id = a.item_id 
			  and size_uuid = f.size_uuid
			  and CURDATE() >= discount_start and CURDATE() <= discount_end
			 ),';',f.item_size_id
			)
			
			from {{view_item_lang_size}} f
			where 
			item_id = a.item_id
			and language IN('',".q($lang).")
			) as prices,
			
			(
			select GROUP_CONCAT(cat_id)
			from {{item_relationship_category}}
			where item_id = a.item_id
		) as group_category
		";

		if($cat_id>0){
			$criteria->condition = "
				a.merchant_id = :merchant_id 
				AND status=:status 
				AND available=:available
				AND c.cat_id=:cat_id
		    ";
		} else {
			$criteria->condition = "
				a.merchant_id = :merchant_id 
				AND status=:status 
				AND available=:available			
		    ";
		}

		$criteria->mergeWith(array(
			"join"=>"LEFT JOIN {{item_translation}} b ON a.item_id = b.item_id and language=".q($lang)." ",				
		));
		$criteria->mergeWith(array(
			"join"=>"LEFT JOIN {{item_relationship_category}} c ON a.item_id = c.item_id ",				
		));		

		if($cat_id>0){
			$criteria->params = array (
			':merchant_id'=>intval($merchant_id),
			':status'=>'publish',
			':available'=>1,
			':cat_id'=>$cat_id
			);		    
		} else {
			$criteria->params = array (
			   ':merchant_id'=>intval($merchant_id),
			   ':status'=>'publish',
			   ':available'=>1,			
			);		    
		}

		if (is_string($search) && strlen($search) > 0){
			$criteria->addSearchCondition('a.item_name', $search ,true,"OR");
			$criteria->addSearchCondition('b.item_name', $search );
		}

		$criteria->order = "c.sequence asc";	
		
	    $count = AR_item::model()->count($criteria);        	
	    		    
	    $pages=new CPagination($count);
        $pages->setCurrentPage($page);        
        $pages->pageSize = intval(Yii::app()->params->list_limit);        
        $pages->applyLimit($criteria);        

		$dependency = CCacheData::dependency(); 
		$models = AR_item::model()->cache(Yii::app()->params->cache, $dependency)->findAll($criteria);		
        
        $page_count = $pages->getPageCount();	        
        $current_page = $pages->getCurrentPage();
                    
        if($models){
        	$data = array();
        	foreach ($models as $val) {            		
        		
        		$price = array();
        		$prices = explode(",",$val->prices);
        		
        		$group_category = explode(",",$val->group_category);
        		
        		if(is_array($prices) && count($prices)>=1){
					foreach ($prices as $pricesval) {
						$sizes = explode(";",$pricesval);							
						$item_price = isset($sizes[1])?(float)$sizes[1]:0;
						$item_discount = isset($sizes[3])?(float)$sizes[3]:0;
						$discount_type = isset($sizes[4])?$sizes[4]:'';
						$discount_valid = isset($sizes[5])?(integer)$sizes[5]:0;						
												
						$price_after_discount=0;
						if($item_discount>0 && $discount_valid>0){
							if($discount_type=="percentage"){
								$price_after_discount = $item_price - (($item_discount/100)*$item_price);
							} else $price_after_discount = $item_price-$item_discount;
						
						} else $item_discount = 0;
						
						$price[] = array(
						  'size_uuid'=>isset($sizes[0])?$sizes[0]:'',
						  'item_size_id'=>isset($sizes[6])?$sizes[6]:'',
						  'price'=>$item_price,
						  'size_name'=>isset($sizes[2])?$sizes[2]:'',
						  'discount'=>$item_discount,
						  'discount_type'=>$discount_type,
						  'price_after_discount'=>$price_after_discount,
						  'pretty_price'=>Price_Formatter::formatNumber($item_price),
						  'pretty_price_after_discount'=>Price_Formatter::formatNumber($price_after_discount),
						);
					}
				}          
								
				$item_name = empty($val->item_name_translation) ? $val->item_name : $val->item_name_translation;
				$item_description = empty($val->item_description_translation) ? $val->item_description : $val->item_description_translation;								
				
				$data[] = array(  
				  'item_id'=>$val->item_id,
				  'item_uuid'=>$val->item_token,
				  'item_name'=>$item_name,
				  'item_description'=>CommonUtility::formatShortText($item_description,130),
				  'url_image'=>CMedia::getImage($val['photo'],$val->path,Yii::app()->params->size_image
				  ,CommonUtility::getPlaceholderPhoto('item')),
				  'category_id'=>$cat_id>0?array($cat_id):$group_category,
				  'price'=>$price,					  
				);
				  		
        	}/* end foreach*/
        	
        	return array(
        	  'total_records'=>t("{{total}} results",array('{{total}}'=>$count)),
        	  'page_count'=>$page_count,
        	  'current_page'=>$current_page+1,
        	  'data'=>$data
        	);
        	
        } /*if model*/
        throw new Exception( 'no results' );
	}

	public static function getItemFeatured($merchant_id=0,$meta_name='',$lang=KMRS_DEFAULT_LANGUAGE)
	{
		$criteria=new CDbCriteria();	
	    $criteria->alias = "a";
	    $criteria->select="a.merchant_id,a.item_id, a.item_token, a.slug , a.photo,a.path,
	    b.item_name,a.item_short_description,

	    (
		select GROUP_CONCAT(f.size_uuid,';',f.price,';',f.size_name,';',f.discount,';',f.discount_type,';',
		 (
		  select count(*) from {{view_item_lang_size}}
		  where item_id = a.item_id 
		  and size_uuid = f.size_uuid
		  and CURDATE() >= discount_start and CURDATE() <= discount_end
		 ),';',f.item_size_id
		)
		
		from {{view_item_lang_size}} f
		where 
		item_id = a.item_id
		and language IN('',".q($lang).")
		) as prices,
		
		(
		select GROUP_CONCAT(cat_id)
		from {{item_relationship_category}}
		where item_id = a.item_id
		) as group_category
	    	    
	    ";		
	    $criteria->condition = "merchant_id = :merchant_id 
	    AND status=:status AND available=:available AND b.language=:language";
	    
	    
		$criteria->condition = " 
		merchant_id = :merchant_id AND status=:status AND available=:available
		AND b.language=:language
		AND
		a.item_id IN (
			select item_id from {{item_meta}}
			where 
			meta_name='item_featured'
			and 
			meta_id = ".q($meta_name)."
		)
		";
		    
	    $criteria->params = array (
	       ':merchant_id'=>intval($merchant_id),
	       ':status'=>'publish',
	       ':available'=>1,
	       ':language'=>$lang
	    );		    
	    
	    $criteria->mergeWith(array(
			'join'=>'LEFT JOIN {{item_translation}} b ON a.item_id = b.item_id',				
		));

		$dependency = CCacheData::dependency();	 	  
		if($models = AR_item::model()->cache(Yii::app()->params->cache, $dependency)->findAll($criteria)){		
			$data = array();
        	foreach ($models as $val) {            		
        		
        		$price = array();
        		$prices = explode(",",$val->prices);
        		
        		$group_category = explode(",",$val->group_category);
        		
        		if(is_array($prices) && count($prices)>=1){
					foreach ($prices as $pricesval) {
						$sizes = explode(";",$pricesval);							
						$item_price = isset($sizes[1])?(float)$sizes[1]:0;
						$item_discount = isset($sizes[3])?(float)$sizes[3]:0;
						$discount_type = isset($sizes[4])?$sizes[4]:'';
						$discount_valid = isset($sizes[5])?(integer)$sizes[5]:0;						
												
						$price_after_discount=0;
						if($item_discount>0 && $discount_valid>0){
							if($discount_type=="percentage"){
								$price_after_discount = $item_price - (($item_discount/100)*$item_price);
							} else $price_after_discount = $item_price-$item_discount;
						
						} else $item_discount = 0;
						
						$price[] = array(
						  'size_uuid'=>isset($sizes[0])?$sizes[0]:'',
						  'item_size_id'=>isset($sizes[6])?$sizes[6]:'',
						  'price'=>$item_price,
						  'size_name'=>isset($sizes[2])?$sizes[2]:'',
						  'discount'=>$item_discount,
						  'discount_type'=>$discount_type,
						  'price_after_discount'=>$price_after_discount,
						  'pretty_price'=>Price_Formatter::formatNumber($item_price),
						  'pretty_price_after_discount'=>Price_Formatter::formatNumber($price_after_discount),
						);
					}
				}          
				
				$data[$val['item_id']] = array(  
				  'item_id'=>$val['item_id'],
				  'item_uuid'=>$val['item_token'],
				  'slug'=>stripslashes($val['slug']),
				  'item_name'=>stripslashes($val['item_name']),
				  'item_description'=>CommonUtility::formatShortText($val['item_short_description'],130),
				  'url_image'=>CMedia::getImage($val['photo'],$val['path'],Yii::app()->params->size_image
				  ,CommonUtility::getPlaceholderPhoto('item')),		
				  'category_id'=>$group_category,		  
				  'price'=>$price,					  
				);
				  		
        	}/* end foreach*/

			return $data;
		}
		throw new Exception( 'no results' );
	}

	public static function getSimilarItems($merchant_id='',$lang='en', $limit=20, $q='',$items_not_available=array(),$category_not_available=array())
	{
				
		$exchange_rate = self::getExchangeRate();

		$stmt="
		SELECT a.merchant_id,a.item_id, a.slug, a.item_token,a.photo,a.path,
		b.item_name,a.item_short_description,a.not_for_sale,	
		(
		select GROUP_CONCAT(f.size_uuid,';',f.price,';',f.size_name,';',f.discount,';',f.discount_type,';',
		 (
		  select count(*) from {{view_item_lang_size}}
		  where item_id = a.item_id 
		  and size_uuid = f.size_uuid
		  and CURDATE() >= discount_start and CURDATE() <= discount_end
		 ),';',f.item_size_id
		)
		
		from {{view_item_lang_size}} f
		where 
		item_id = a.item_id
		and language IN('',".q($lang).")
		) as prices,

		(
		  select cat_id from {{item_relationship_category}}
		  where item_id = a.item_id 
		  limit 0,1
		) as category
		
		
		FROM {{item}} a
		LEFT JOIN {{item_translation}} b
		ON
		a.item_id = b.item_id
		
		WHERE 
		a.merchant_id = ".q($merchant_id)."
		AND a.status ='publish'
		AND a.available=1
		AND b.language = ".q($lang)."					
		";				
		if(!empty($q)){
			$stmt.="
			AND b.item_name LIKE ".q("%$q%")."
			";
		}					
		$stmt.="
		ORDER BY rand()		
		LIMIT 0,$limit
		";		
		
		$dependency = CCacheData::dependency();
		if($res = Yii::app()->db->cache(Yii::app()->params->cache, $dependency)->createCommand($stmt)->queryAll()){										
			$data = array();
			foreach ($res as $val) {				
				$price = array();
				$prices = explode(",",$val['prices']);				
				if(is_array($prices) && count($prices)>=1){
					foreach ($prices as $pricesval) {
						$sizes = explode(";",$pricesval);							
						$item_price = isset($sizes[1])?(float)$sizes[1]:0;
						$item_discount = isset($sizes[3])?(float)$sizes[3]:0;
						$discount_type = isset($sizes[4])?$sizes[4]:'';
						$discount_valid = isset($sizes[5])?(integer)$sizes[5]:0;						
												
						$price_after_discount=0;
						if($item_discount>0 && $discount_valid>0){
							if($discount_type=="percentage"){
								$price_after_discount = $item_price - (($item_discount/100)*$item_price);
							} else $price_after_discount = $item_price-$item_discount;
						
						} else $item_discount = 0;
						
						$price[] = array(
						  'size_uuid'=>isset($sizes[0])?$sizes[0]:'',
						  'item_size_id'=>isset($sizes[6])?$sizes[6]:'',
						  'price'=>($item_price*$exchange_rate),
						  'size_name'=>isset($sizes[2])?$sizes[2]:'',
						  'discount'=>$item_discount,
						  'discount_type'=>$discount_type,
						  'price_after_discount'=>($price_after_discount*$exchange_rate),
						  'pretty_price'=>Price_Formatter::formatNumber( ($item_price*$exchange_rate) ),
						  'pretty_price_after_discount'=>Price_Formatter::formatNumber( ($price_after_discount*$exchange_rate) ),
						);
					}
				}

				$available = true;
				if(in_array($val['item_id'],(array)$items_not_available)){
					$available = false;
				} else {
					$available = in_array($val['category'],(array)$category_not_available)?false:true;
				}
				
				$data[$val['item_id']] = array(  
				   'cat_id'=>intval($val['category']),
				  'item_id'=>$val['item_id'],
				  'item_uuid'=>$val['item_token'],
				  'slug'=>$val['slug'],
				  'item_name'=>CHtml::decode($val['item_name']),
				  'item_description'=>CommonUtility::formatShortText($val['item_short_description'],130),
				  'url_image'=>CMedia::getImage($val['photo'],$val['path'],Yii::app()->params->size_image
				  ,CommonUtility::getPlaceholderPhoto('item')),
				  'price'=>$price,				  
				  'qty'=>0,
				  'not_for_sale'=>$val['not_for_sale']==1?true:false,
				  'available'=>$available
				);
			}
			return $data;
		}
		throw new Exception( 'no results' );
	}		
	
	public static function searchItems($search_array=[], $lang='en', $limit=20 , $currency_code='',$multicurrency_enabled=false,$search_type='item_name')
	{
	
		$or = '';
		if($search_type=="item_name"){
			if(is_array($search_array) && count($search_array)>=1){
				foreach ($search_array as $key => $value) {
					if($key==0){
						//$or.="AND b.item_name LIKE ".q("%$value%")." ";
						$or.="AND ( b.item_name LIKE ".q("%$value%")." ";
					} else {
						$or.="OR  b.item_name LIKE ".q("%$value%")." ";
					}				
				}			
				$or.=" )";
			}
	    } else if ($search_type=="meta_name"){			
			$meta_name = isset($search_array['meta_name'])?$search_array['meta_name']:'';
			$or = "
			AND a.item_id IN (
				select item_id from {{item_meta}}
				where item_id = a.item_id and meta_name='item_featured' and meta_id=".q($meta_name)."
			)
			";
		}

		$stmt="
		SELECT a.merchant_id,a.item_id, a.slug, a.item_token,a.photo,a.path,
		b.item_name,a.item_short_description,
		IF(IFNULL(c.option_value, '') = '', ".q($currency_code).", c.option_value) AS merchant_currency,
		".q($currency_code)." as to_currency,
		d.exchange_rate,
		
		(
		select GROUP_CONCAT(f.size_uuid,';',f.price,';',f.size_name,';',f.discount,';',f.discount_type,';',
		 (
		  select count(*) from {{view_item_lang_size}}
		  where item_id = a.item_id 
		  and size_uuid = f.size_uuid
		  and CURDATE() >= discount_start and CURDATE() <= discount_end
		 ),';',f.item_size_id
		)
		
		from {{view_item_lang_size}} f
		where 
		item_id = a.item_id
		and language IN('',".q($lang).")
		) as prices,

		(
		  select cat_id from {{item_relationship_category}}
		  where item_id = a.item_id 
		  limit 0,1
		) as category,

		(
			select restaurant_slug from {{merchant}}
			where merchant_id = a.merchant_id
			limit 0,1
		) as slug
		
		
		FROM {{item}} a
		LEFT JOIN {{item_translation}} b
		ON
		a.item_id = b.item_id

		LEFT JOIN (
		SELECT merchant_id,option_value FROM {{option}} where option_name = 'merchant_default_currency'
		) c		
		ON a.merchant_id = c.merchant_id

		LEFT JOIN (
		SELECT base_currency,exchange_rate FROM {{currency_exchangerate}} where currency_code = ".q($currency_code)."
		) d				
		ON d.base_currency = IF(IFNULL(c.option_value, '') = '', ".q($currency_code)." , c.option_value)
		
		WHERE 		
		a.status ='publish'
		AND a.available=1
		AND b.language = ".q($lang)."					
		$or
		GROUP BY a.item_id
		ORDER BY rand()		
		LIMIT 0,$limit
		";				
		$dependency = CCacheData::dependency();
		if($res = Yii::app()->db->cache(Yii::app()->params->cache, $dependency)->createCommand($stmt)->queryAll()){																
			$data = array(); $merchant_ids = []; $price_list = [];
			if($multicurrency_enabled){
				$price_list = CMulticurrency::getAllCurrency($currency_code);			
			}						
			foreach ($res as $val) {				
				$price = array();
				$prices = explode(",",$val['prices']);	
				$exchange_rate = $val['exchange_rate']>0?$val['exchange_rate']:1;		
				$merchant_currency = $val['merchant_currency'];
				$to_currency = $val['to_currency'];

				$price_list_format = isset($price_list[$to_currency]) ? $price_list[$to_currency] : Price_Formatter::$number_format;
				
				$money_config = [				
					'precision' => $price_list_format['decimals'],
					'minimumFractionDigits'=>$price_list_format['decimals'],
					'decimal' => $price_list_format['decimal_separator'],
					'thousands' => $price_list_format['thousand_separator'],
					'separator' => $price_list_format['thousand_separator'],
					'prefix'=> $price_list_format['position']=='left'?$price_list_format['currency_symbol']:'',
					'suffix'=> $price_list_format['position']=='right'?$price_list_format['currency_symbol']:'',
					'prefill'=>true
				];	

				if(is_array($prices) && count($prices)>=1){
					foreach ($prices as $pricesval) {
						$sizes = explode(";",$pricesval);							
						$item_price = isset($sizes[1])?(float)$sizes[1]:0;
						$item_price = ($item_price*$exchange_rate);
						$item_discount = isset($sizes[3])?(float)$sizes[3]:0;
						$discount_type = isset($sizes[4])?$sizes[4]:'';
						$discount_valid = isset($sizes[5])?(integer)$sizes[5]:0;						
												
						$price_after_discount=0;
						if($item_discount>0 && $discount_valid>0){
							if($discount_type=="percentage"){
								$price_after_discount = $item_price - (($item_discount/100)*$item_price);
							} else {
								$price_after_discount = $item_price - ($item_discount*$exchange_rate);
							}
						
						} else $item_discount = 0;
						
						
						$price[] = array(
						  'size_uuid'=>isset($sizes[0])?$sizes[0]:'',
						  'item_size_id'=>isset($sizes[6])?$sizes[6]:'',
						  'price'=>$item_price,
						  'size_name'=>isset($sizes[2])?$sizes[2]:'',
						  'discount'=>$item_discount,
						  'discount_type'=>$discount_type,
						  'price_after_discount'=>$price_after_discount,
						  'pretty_price'=> $multicurrency_enabled? Price_Formatter::formatNumber2($item_price,$price_list_format) : Price_Formatter::formatNumber($item_price),
						  'pretty_price_after_discount'=> $multicurrency_enabled? Price_Formatter::formatNumber2($price_after_discount,$price_list_format) :  Price_Formatter::formatNumber($price_after_discount),
						  'exchange_rate'=>$exchange_rate,
						  'merchant_currency'=>$merchant_currency,
						  'to_currency'=>$to_currency,						  
						);
					}
				}

				$merchant_ids[]=$val['merchant_id'];

				
				$data[] = array(  
				  'merchant_id'=>intval($val['merchant_id']),
				  'cat_id'=>intval($val['category']),
				  'item_id'=>$val['item_id'],
				  'item_uuid'=>$val['item_token'],
				  'slug'=>$val['slug'],
				  'item_name'=>CHtml::decode($val['item_name']),
				  'item_description'=>CommonUtility::formatShortText($val['item_short_description'],130),
				  'url_image'=>CMedia::getImage($val['photo'],$val['path'],Yii::app()->params->size_image
				  ,CommonUtility::getPlaceholderPhoto('item')),
				  'price'=>$price,				  
				  'qty'=>0,		
				  'money_config'=>$money_config
				);
			}
			return [
				'data'=>$data,
				'merchant_ids'=>$merchant_ids,
				'total'=>count($data)
			];
		}
		throw new Exception( 'no results' );
	}		

	public static function searchMenuItems($q='', $merchant_id=0, $lang='en', $limit=20)
	{
				
		$stmt="
		SELECT a.merchant_id,a.item_id, a.slug, a.item_token,a.photo,a.path,
		b.item_name,a.item_short_description,
		
		(
		select GROUP_CONCAT(f.size_uuid,';',f.price,';',f.size_name,';',f.discount,';',f.discount_type,';',
		 (
		  select count(*) from {{view_item_lang_size}}
		  where item_id = a.item_id 
		  and size_uuid = f.size_uuid
		  and CURDATE() >= discount_start and CURDATE() <= discount_end
		 ),';',f.item_size_id
		)
		
		from {{view_item_lang_size}} f
		where 
		item_id = a.item_id
		and language IN('',".q($lang).")
		) as prices,

		(
		  select cat_id from {{item_relationship_category}}
		  where item_id = a.item_id 
		  limit 0,1
		) as category,

		(
			select restaurant_slug from {{merchant}}
			where merchant_id = a.merchant_id
			limit 0,1
		) as slug
		
		
		FROM {{item}} a
		LEFT JOIN {{item_translation}} b
		ON
		a.item_id = b.item_id
		
		WHERE 		
		a.status ='publish'
		AND a.merchant_id = ".q( intval($merchant_id) )."
		AND a.available=1
		AND b.language = ".q($lang)."					
		AND b.item_name LIKE ".q("%$q%")."
		ORDER BY b.item_name ASC	
		LIMIT 0,$limit
		";					
		$dependency = CCacheData::dependency();
		if($res = Yii::app()->db->cache(Yii::app()->params->cache, $dependency)->createCommand($stmt)->queryAll()){										
			$data = array(); $merchant_ids = [];
			foreach ($res as $val) {				
				$price = array();
				$prices = explode(",",$val['prices']);				
				if(is_array($prices) && count($prices)>=1){
					foreach ($prices as $pricesval) {
						$sizes = explode(";",$pricesval);							
						$item_price = isset($sizes[1])?(float)$sizes[1]:0;
						$item_discount = isset($sizes[3])?(float)$sizes[3]:0;
						$discount_type = isset($sizes[4])?$sizes[4]:'';
						$discount_valid = isset($sizes[5])?(integer)$sizes[5]:0;						
												
						$price_after_discount=0;
						if($item_discount>0 && $discount_valid>0){
							if($discount_type=="percentage"){
								$price_after_discount = $item_price - (($item_discount/100)*$item_price);
							} else $price_after_discount = $item_price-$item_discount;
						
						} else $item_discount = 0;
						
						$price[] = array(
						  'size_uuid'=>isset($sizes[0])?$sizes[0]:'',
						  'item_size_id'=>isset($sizes[6])?$sizes[6]:'',
						  'price'=>$item_price,
						  'size_name'=>isset($sizes[2])?$sizes[2]:'',
						  'discount'=>$item_discount,
						  'discount_type'=>$discount_type,
						  'price_after_discount'=>$price_after_discount,
						  'pretty_price'=>Price_Formatter::formatNumber($item_price),
						  'pretty_price_after_discount'=>Price_Formatter::formatNumber($price_after_discount),
						);
					}
				}

				$merchant_ids[]=$val['merchant_id'];
				
				$data[] = array(  
				  'merchant_id'=>intval($val['merchant_id']),
				  'cat_id'=>intval($val['category']),
				  'item_id'=>$val['item_id'],
				  'item_uuid'=>$val['item_token'],
				  'slug'=>$val['slug'],
				  'item_name'=>CHtml::decode($val['item_name']),
				  'item_description'=>CommonUtility::formatShortText($val['item_short_description'],130),
				  'url_image'=>CMedia::getImage($val['photo'],$val['path'],Yii::app()->params->size_image
				  ,CommonUtility::getPlaceholderPhoto('item')),
				  'price'=>$price,				  
				  'qty'=>0
				);
			}
			return [
				'data'=>$data,
				'merchant_ids'=>$merchant_ids
			];
		}
		throw new Exception( 'no results' );
	}		

	public static function getCategoryList($merchant_id=0)
	{
		$stmt="
		SELECT a.merchant_id,a.cat_id,
		IFNULL((
		 select GROUP_CONCAT(DISTINCT item_id SEPARATOR ',')
		 from {{item_relationship_category}}
		 where merchant_id = a.merchant_id
		 and cat_id = a.cat_id
		 and item_id in (
		    select item_id from {{item}}
		    where status='publish'
		    and available = 1
		 )
		),'') as items
		
		FROM {{category}} a				
		WHERE a.merchant_id = ".q($merchant_id)."
		AND a.status='publish'				
		";					
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
			$data = array();
			foreach ($res as $val) {
				$items = explode(",",$val['items']);
				$data[]=[
					'cat_id'=>$val['cat_id'],
					'items'=>$items
				];
			}			
			return $data;
		}
		throw new Exception( 'no results' );		
	}

	public static function getMenuByGroupID($item_ids=array(),$lang='en')
	{
				
		$stmt="
		SELECT a.merchant_id,a.item_id, a.slug, a.item_token,a.photo,a.path,
		b.item_name,a.item_short_description,
		
		(
		select GROUP_CONCAT(f.size_uuid,';',f.price,';',f.size_name,';',f.discount,';',f.discount_type,';',
		 (
		  select count(*) from {{view_item_lang_size}}
		  where item_id = a.item_id 
		  and size_uuid = f.size_uuid
		  and CURDATE() >= discount_start and CURDATE() <= discount_end
		 ),';',f.item_size_id
		)
		
		from {{view_item_lang_size}} f
		where 
		item_id = a.item_id
		and language IN('',".q($lang).")
		) as prices,
		
		(
		select count(*) from {{item_relationship_subcategory}}
		where item_id = a.item_id 
		and item_size_id > 0 and subcat_id > 0
		) as total_addon,
		
		(
		select count(*) from {{item_meta}}
		where item_id = a.item_id 		
		and meta_name not in ('delivery_options','dish','delivery_vehicle')
		) as total_meta
		 
		
		FROM {{item}} a
		LEFT JOIN {{item_translation}} b
		ON
		a.item_id = b.item_id
		
		WHERE 
		a.item_id IN (".implode(",",$item_ids).")
		AND a.status ='publish'
		AND a.available=1
		AND b.language = ".q($lang)."				
		";				
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){						
			$data = array();
			foreach ($res as $val) {				
				$price = array();
				$prices = explode(",",$val['prices']);				
				if(is_array($prices) && count($prices)>=1){
					foreach ($prices as $pricesval) {
						$sizes = explode(";",$pricesval);							
						$item_price = isset($sizes[1])?(float)$sizes[1]:0;
						$item_discount = isset($sizes[3])?(float)$sizes[3]:0;
						$discount_type = isset($sizes[4])?$sizes[4]:'';
						$discount_valid = isset($sizes[5])?(integer)$sizes[5]:0;						
												
						$price_after_discount=0;
						if($item_discount>0 && $discount_valid>0){
							if($discount_type=="percentage"){
								$price_after_discount = $item_price - (($item_discount/100)*$item_price);
							} else $price_after_discount = $item_price-$item_discount;
						
						} else $item_discount = 0;
						
						$price[] = array(
						  'size_uuid'=>isset($sizes[0])?$sizes[0]:'',
						  'item_size_id'=>isset($sizes[6])?$sizes[6]:'',
						  'price'=>$item_price,
						  'size_name'=>isset($sizes[2])?$sizes[2]:'',
						  'discount'=>$item_discount,
						  'discount_type'=>$discount_type,
						  'price_after_discount'=>$price_after_discount,
						  'pretty_price'=>Price_Formatter::formatNumber($item_price),
						  'pretty_price_after_discount'=>Price_Formatter::formatNumber($price_after_discount),
						);
					}
				}
				$data[$val['item_id']] = array(  
				  'item_id'=>$val['item_id'],
				  'item_uuid'=>$val['item_token'],
				  'slug'=>$val['slug'],
				  'item_name'=>CHtml::decode($val['item_name']),
				  'item_description'=>CommonUtility::formatShortText($val['item_short_description'],130),
				  'url_image'=>CMedia::getImage($val['photo'],$val['path'],Yii::app()->params->size_image
				  ,CommonUtility::getPlaceholderPhoto('item')),
				  'price'=>$price,
				  'total_addon'=>(integer)$val['total_addon'],
				  'total_meta'=>(integer)$val['total_meta'],
				  'qty'=>0
				);
			}
			return $data;
		}
		throw new Exception( 'no results' );
	}	

	public static function getStoreAddon($merchant_id='',$lang=KMRS_DEFAULT_LANGUAGE, $sort='ORDER BY subcat_id DESC' )
	{
					
		$stmt="
		SELECT a.subcat_id,
		a.subcategory_name as original_subcategory_name,
		b.subcategory_description as original_subcategory_description,
		b.subcategory_name,
		b.subcategory_description, 
		a.featured_image,
		a.path,
		a.status,

		IFNULL((
			select GROUP_CONCAT(DISTINCT sub_item_id ORDER BY sequence SEPARATOR ',')
			from {{subcategory_item_relationships}}
			where subcat_id = a.subcat_id	
			and sub_item_id IN (
				 select sub_item_id from {{subcategory_item}}
				 where sub_item_id = sub_item_id
			)
		),'') as items

		FROM {{subcategory}} a
		left JOIN (
			SELECT subcat_id,subcategory_name, subcategory_description FROM {{subcategory_translation}} where language = ".q($lang)."
		) b 
		ON
		b.subcat_id = a.subcat_id
		WHERE merchant_id=".q($merchant_id)."		
		$sort
		";
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){			
			$data = array();
			foreach ($res as $val) {				
				$items = explode(",",$val['items']);
				$data[] = [
					'subcat_id'=>$val['subcat_id'],
					'subcategory_name'=> !empty($val['subcategory_name']) ? CHtml::decode($val['subcategory_name']) : $val['original_subcategory_name'],
					'subcategory_description'=> !empty($val['subcategory_description']) ? CHtml::decode($val['subcategory_description']) : $val['original_subcategory_description'],	
					'items'=> !empty($val['items'])? $items:"",
					'url_image'=>CMedia::getImage($val['featured_image'],$val['path'],Yii::app()->params->size_image_thumbnail
					,CommonUtility::getPlaceholderPhoto('item')),		
					'status'=>$val['status']
				];				
			}
			return $data;
		} 
		throw new Exception( 'no results' );
	}

	public static function getStoreAddonItems($merchant_id='',$lang=KMRS_DEFAULT_LANGUAGE, $sort='ORDER BY sub_item_id DESC')
	{
		$stmt = "
		SELECT a.sub_item_id, b.sub_item_name, b.item_description,
		a.price	, a.photo , a.path, a.status
		FROM {{subcategory_item}} a
		LEFT JOIN {{subcategory_item_translation}} b
		ON
		b.sub_item_id = a.sub_item_id
		WHERE merchant_id=".q($merchant_id)."
		AND b.language = ".q($lang)."
		$sort
		";		
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
			$data = array();
			foreach ($res as $val) {
				$data[$val['sub_item_id']] = [
					'sub_item_id'=>$val['sub_item_id'],
					'sub_item_name'=>CHtml::decode($val['sub_item_name']),
					'item_description'=>CHtml::decode($val['item_description']),	
					'has_photo'=>!empty($val['photo'])?true:false,
					'url_image'=>CMedia::getImage($val['photo'],$val['path'],Yii::app()->params->size_image_thumbnail
					,CommonUtility::getPlaceholderPhoto('item')),		
					'price'=>Price_Formatter::formatNumber($val['price']),
					'status'=>$val['status'],
					'available'=>$val['status']=="publish"?true:false
				];
			}			
			return $data;
		}
		throw new Exception( 'no results' );
	}

	public static function getUncategorizeAddonitem($merchant_id=0)
	{		
		$stmt = "
		SELECT a.sub_item_id
		FROM {{subcategory_item}} a
		WHERE merchant_id = 3
		AND
		a.sub_item_id NOT IN (
			select sub_item_id from {{subcategory_item_relationships}}
			where sub_item_id=a.sub_item_id
		)
		";
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
			$data = [];
			foreach ($res as $items) {
				$data[]=$items['sub_item_id'];
			}
			return $data;
		}
		throw new Exception( 'no results' );
	}

	public static function getMenu2($merchant_id='',$lang='en')
	{
				
		$stmt="
		SELECT a.merchant_id,a.item_id, a.slug, a.item_token,a.photo,a.path,
		b.item_name,a.item_short_description,a.available,
		
		(
		select GROUP_CONCAT(f.size_uuid,';',f.price,';',f.size_name,';',f.discount,';',f.discount_type,';',
		 (
		  select count(*) from {{view_item_lang_size}}
		  where item_id = a.item_id 
		  and size_uuid = f.size_uuid
		  and CURDATE() >= discount_start and CURDATE() <= discount_end
		 ),';',f.item_size_id
		)
		
		from {{view_item_lang_size}} f
		where 
		item_id = a.item_id
		and language IN('',".q($lang).")
		) as prices,
		
		(
		select count(*) from {{item_relationship_subcategory}}
		where item_id = a.item_id 
		and item_size_id > 0 and subcat_id > 0
		) as total_addon,
		
		(
		select count(*) from {{item_meta}}
		where item_id = a.item_id 		
		and meta_name not in ('delivery_options','dish','delivery_vehicle')
		) as total_meta
		 
		
		FROM {{item}} a
		LEFT JOIN {{item_translation}} b
		ON
		a.item_id = b.item_id
		
		WHERE 
		a.merchant_id = ".q($merchant_id)."				
		AND b.language = ".q($lang)."				
		";		
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){						
			$data = array();
			foreach ($res as $val) {				
				$price = array();
				$prices = explode(",",$val['prices']);				
				if(is_array($prices) && count($prices)>=1){
					foreach ($prices as $pricesval) {
						$sizes = explode(";",$pricesval);							
						$item_price = isset($sizes[1])?(float)$sizes[1]:0;
						$item_discount = isset($sizes[3])?(float)$sizes[3]:0;
						$discount_type = isset($sizes[4])?$sizes[4]:'';
						$discount_valid = isset($sizes[5])?(integer)$sizes[5]:0;						
												
						$price_after_discount=0;
						if($item_discount>0 && $discount_valid>0){
							if($discount_type=="percentage"){
								$price_after_discount = $item_price - (($item_discount/100)*$item_price);
							} else $price_after_discount = $item_price-$item_discount;
						
						} else $item_discount = 0;
						
						$price[] = array(
						  'size_uuid'=>isset($sizes[0])?$sizes[0]:'',
						  'item_size_id'=>isset($sizes[6])?$sizes[6]:'',
						  'price'=>$item_price,
						  'size_name'=>isset($sizes[2])?$sizes[2]:'',
						  'discount'=>$item_discount,
						  'discount_type'=>$discount_type,
						  'price_after_discount'=>$price_after_discount,
						  'pretty_price'=>Price_Formatter::formatNumber($item_price),
						  'pretty_price_after_discount'=>Price_Formatter::formatNumber($price_after_discount),
						);
					}
				}
				$data[$val['item_id']] = array(  
				  'item_id'=>$val['item_id'],
				  'item_uuid'=>$val['item_token'],
				  'slug'=>$val['slug'],
				  'item_name'=>CHtml::decode($val['item_name']),
				  'item_description'=>CommonUtility::formatShortText($val['item_short_description'],130),
				  'has_photo'=>!empty($val['photo'])?true:false,
				  'url_image'=>CMedia::getImage($val['photo'],$val['path'],Yii::app()->params->size_image
				  ,CommonUtility::getPlaceholderPhoto('item')),
				  'price'=>$price,
				  'total_addon'=>(integer)$val['total_addon'],
				  'total_meta'=>(integer)$val['total_meta'],
				  'qty'=>0,
				  'available'=>$val['available']==1?true:false,
				);
			}
			return $data;
		}
		throw new Exception( 'no results' );
	}			
		
	public static function getUncategorizeitem($merchant_id=0)
	{		
		$stmt = "
		SELECT a.item_id
		FROM {{item}} a
		WHERE merchant_id = 3
		AND
		a.item_id NOT IN (
			select item_id from {{item_relationship_category}}
			where item_id=a.item_id
		)
		";		
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
			$data = [];
			foreach ($res as $items) {
				$data[]=$items['item_id'];
			}
			return $data;
		}
		throw new Exception( 'no results' );
	}

	public static function searchCategory($merchant_id=0, $search='',$lang=KMRS_DEFAULT_LANGUAGE)
	{
		$stmt="
		SELECT 
		'category' as food_type,
		a.cat_id,a.category_name as name,a.category_description as description,
		b.photo,b.path
		FROM {{category_translation}} a 
		LEFT JOIN {{category}} b 
		ON
		a.cat_id = b.cat_id
		WHERE a.category_name LIKE ".q("%$search%")."
		AND a.language=".q($lang)."		
		AND b.merchant_id=".q($merchant_id)."
		LIMIT 0,100
		";		
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
			$data = [];
			foreach ($res as $items) {
				$items['url_image'] =  CMedia::getImage($items['photo'],$items['path'],Yii::app()->params->size_image,CommonUtility::getPlaceholderPhoto('item'));
				$data[]=$items;
			}
			return $data;
		}
		throw new Exception( HELPER_NO_RESULTS );
	}

	public static function searchByItems($merchant_id=0, $search='',$lang=KMRS_DEFAULT_LANGUAGE)
	{
		$stmt="
		SELECT 
		'item' as food_type,
		a.item_id,b.item_token,a.item_name as name,a.item_description as description,
		b.photo,b.path
		FROM {{item_translation}} a 
		LEFT JOIN {{item}} b 
		ON
		a.item_id = b.item_id
		WHERE a.item_name LIKE ".q("%$search%")."
		AND a.language=".q($lang)."		
		AND b.merchant_id=".q($merchant_id)."
		LIMIT 0,100
		";		
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
			$data = [];
			foreach ($res as $items) {
				$items['url_image'] =  CMedia::getImage($items['photo'],$items['path'],Yii::app()->params->size_image,CommonUtility::getPlaceholderPhoto('item'));
				$data[]=$items;
			}
			return $data;
		}
		throw new Exception( HELPER_NO_RESULTS );
	}

	public static function searchAddons($merchant_id=0, $search='',$lang=KMRS_DEFAULT_LANGUAGE)
	{
		$stmt="
		SELECT 
		'addon' as food_type,
		a.subcat_id,a.subcategory_name as name,a.subcategory_description as description,
		b.featured_image as photo,b.path
		FROM {{subcategory_translation}} a 
		LEFT JOIN {{subcategory}} b 
		ON
		a.subcat_id = b.subcat_id
		WHERE a.subcategory_name LIKE ".q("%$search%")."
		AND a.language=".q($lang)."		
		AND b.merchant_id=".q($merchant_id)."
		LIMIT 0,100
		";		
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
			$data = [];
			foreach ($res as $items) {
				$items['url_image'] =  CMedia::getImage($items['photo'],$items['path'],Yii::app()->params->size_image,CommonUtility::getPlaceholderPhoto('item'));
				$data[]=$items;
			}
			return $data;
		}
		throw new Exception( HELPER_NO_RESULTS );
	}
	
	public static function searchAddonItem($merchant_id=0, $search='',$lang=KMRS_DEFAULT_LANGUAGE)
	{
		$stmt="
		SELECT 
		'addon_item' as food_type,
		a.sub_item_id,a.sub_item_name as name,a.item_description as description,
		b.photo,b.path
		FROM {{subcategory_item_translation}} a 
		LEFT JOIN {{subcategory_item}} b 
		ON
		a.sub_item_id = b.sub_item_id
		WHERE a.sub_item_name LIKE ".q("%$search%")."
		AND a.language=".q($lang)."		
		AND b.merchant_id=".q($merchant_id)."
		LIMIT 0,100
		";		
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
			$data = [];
			foreach ($res as $items) {
				$items['url_image'] =  CMedia::getImage($items['photo'],$items['path'],Yii::app()->params->size_image,CommonUtility::getPlaceholderPhoto('item'));
				$data[]=$items;
			}
			return $data;
		}
		throw new Exception( HELPER_NO_RESULTS );
	}

	public static function getItemAvailability($merchant_id=0,$day_of_week='',$timenow='')
	{			
		$day_of_week = $day_of_week>0?$day_of_week:7;		
		$stmt = "		
		SELECT a.*,b.available_at_specific
		FROM {{availability}} a
		left join {{item}} b
		on 
		a.meta_value = b.item_id
		where 
		a.meta_name='item'
		and a.day_of_week=".q($day_of_week)."	
		and a.merchant_id=".q($merchant_id)."    
		and b.available_at_specific = 1
		and ( a.status=0 or  ".q($timenow)." NOT BETWEEN  CAST(a.start_time as TIME) and CAST(a.end_time AS TIME) )	
		";
		$dependency = CCacheData::dependency();
		if($res = Yii::app()->db->cache(Yii::app()->params->cache, $dependency)->createCommand($stmt)->queryAll()){
			$data = [];
			foreach ($res as $items) {				
				$data[] = $items['meta_value'];
			}
			return $data;
		}
		return [];
	}

	public static function getCategoryAvailability($merchant_id=0,$day_of_week='',$timenow='')
	{				
		$day_of_week = $day_of_week>0?$day_of_week:7;	
		$stmt="		
		SELECT a.*,b.available_at_specific
		FROM {{availability}} a
		LEFT JOIN {{category}} b
		on 
		a.meta_value = b.cat_id
		where 
		a.meta_name='category'
		and a.day_of_week=".q($day_of_week)."	
		and a.merchant_id=".q($merchant_id)." 
		and b.available_at_specific = 1
		and ( a.status=0 or ".q($timenow)." NOT BETWEEN CAST(a.start_time as TIME) and CAST(a.end_time as TIME) )
		";		
		$dependency = CCacheData::dependency();
		if($res = Yii::app()->db->cache(Yii::app()->params->cache, $dependency)->createCommand($stmt)->queryAll()){
			$data = [];
			foreach ($res as $items) {				
				$data[] = $items['meta_value'];
			}
			return $data;
		}
		return [];
	}

	public static function getItemsByIds($item_ids=array())
	{
		$data = [];
		$line_item_ids = CommonUtility::arrayToQueryParameters($item_ids);
		$stmt = "
		SELECT a.item_id, a.slug, a.item_token, b.cat_id 
		FROM {{item}} a
		LEFT JOIN {{item_relationship_category}} b
		ON a.item_id = b.item_id
		WHERE
		a.item_id IN (".$line_item_ids.")
		";		
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
			$data  = [];
			foreach ($res as $key => $items) {	
				$data[$items['item_id']] = [
					'item_id'=>$items['item_id'],
					'slug'=>$items['slug'],
					'item_token'=>$items['item_token'],
					'cat_id'=>$items['cat_id'],
				];
			}			
		}
		return $data;
	}

	public static function setExchangeRate($exchange_rate=0)
	{
		if($exchange_rate>0){
			self::$exchange_rate = $exchange_rate;
		} else {
			self::$exchange_rate = 1;
		}
	}

	public static function getExchangeRate()
	{	
		return self::$exchange_rate>0? floatval(self::$exchange_rate) : 1 ;
	}

	public static function setAdminExchangeRate($exchange_rate=0)
	{
		if($exchange_rate>0){
			self::$admin_exchange_rate = $exchange_rate;
		} else {
			self::$admin_exchange_rate = 1;
		}
	}

	public static function getAdminExchangeRate()
	{	
		return self::$admin_exchange_rate>0? floatval(self::$admin_exchange_rate) : 1 ;
	}

	public static function setPointsRate($points_enabled=false,$earning_rule='',$earning_points=0)
	{
		self::$points_enabled = $points_enabled;
		self::$points_earning_rule = $earning_rule;
		self::$points_earning_points = $earning_points;
	}

	public static function getEnabledPoints()
	{
		return self::$points_enabled?self::$points_enabled:false;
	}

	public static function getPointsRule()
	{
		return !empty(self::$points_earning_rule) ? trim(self::$points_earning_rule) : 'sub_total';
	}

	public static function getEarningPoints()
	{
		return self::$points_earning_points>0? floatval(self::$points_earning_points) : 0;
	}

	public static function getAllergens($merchant_id=0, $item_id=0)
	{
		$data = [];
		$dependency = CCacheData::dependency(); 
		$model = AR_item_meta::model()->cache(Yii::app()->params->cache, $dependency)->findAll("merchant_id=:merchant_id AND item_id=:item_id AND meta_name=:meta_name",[
			':merchant_id'=>$merchant_id,
			':item_id'=>$item_id,
			':meta_name'=>"allergens"
		]);
		if($model){
			foreach ($model as $items) {
				$data[] = (integer)$items->meta_id;
			}
		}
		return $data;
	}

	public static function getItemAvailabilityByIDs($merchant_ids=0,$day_of_week='',$timenow='')
	{			
		$merchant_id = CommonUtility::arrayToQueryParameters($merchant_ids);
		$day_of_week = $day_of_week>0?$day_of_week:7;		
		$stmt = "		
		SELECT a.*,b.available_at_specific
		FROM {{availability}} a
		left join {{item}} b
		on 
		a.meta_value = b.item_id
		where 
		a.meta_name='item'
		and a.day_of_week=".q($day_of_week)."	
		and a.merchant_id  IN ($merchant_id)
		and b.available_at_specific = 1
		and ( a.status=0 or  ".q($timenow)." NOT BETWEEN  CAST(a.start_time as TIME) and CAST(a.end_time AS TIME) )	
		";		
		if(is_array($merchant_ids) && count($merchant_ids)>=1){	
			$dependency = CCacheData::dependency();
			if($res = Yii::app()->db->cache(Yii::app()->params->cache, $dependency)->createCommand($stmt)->queryAll()){
				$data = [];
				foreach ($res as $items) {				
					$data[] = $items['meta_value'];
				}
				return $data;
			}
	    }
		return [];
	}

	public static function getCategoryAvailabilityByIDs($merchant_ids=0,$day_of_week='',$timenow='')
	{				
		$merchant_id = CommonUtility::arrayToQueryParameters($merchant_ids);
		$day_of_week = $day_of_week>0?$day_of_week:7;	
		$stmt="		
		SELECT a.*,b.available_at_specific
		FROM {{availability}} a
		LEFT JOIN {{category}} b
		on 
		a.meta_value = b.cat_id
		where 
		a.meta_name='category'
		and a.day_of_week=".q($day_of_week)."	
		and a.merchant_id  IN ($merchant_id)
		and b.available_at_specific = 1
		and ( a.status=0 or ".q($timenow)." NOT BETWEEN CAST(a.start_time as TIME) and CAST(a.end_time as TIME) )
		";		
		if(is_array($merchant_ids) && count($merchant_ids)>=1){	
			$dependency = CCacheData::dependency();
			if($res = Yii::app()->db->cache(Yii::app()->params->cache, $dependency)->createCommand($stmt)->queryAll()){
				$data = [];
				foreach ($res as $items) {				
					$data[] = $items['meta_value'];
				}
				return $data;
			}
	    }
		return [];
	}

	public static function getCategoryItems($cat_id='',$merchant_id='',$lang='en')
	{
		
		$exchange_rate = self::getExchangeRate();		

		$stmt = "		
		SELECT 
		a.merchant_id,
		a.item_id, 
		a.slug, 
		a.item_token,
		a.photo,
		a.path,
		a.item_name as original_item_name,
		a.item_short_description as original_item_short_description,
		b.item_name,
		b.item_description,

		(
			select GROUP_CONCAT(f.size_uuid,';',f.price,';', IF(f.size_name='',f.original_size_name,f.size_name) ,';',f.discount,';',f.discount_type,';',
			(
			select count(*) from {{view_item_lang_size}}
			where item_id = a.item_id 
			and size_uuid = f.size_uuid
			and CURDATE() >= discount_start and CURDATE() <= discount_end
			),';',f.item_size_id
		)
		
		from {{view_item_lang_size}} f
			where 
			item_id = a.item_id
			and language IN('',".q($lang).")
		) as prices,
		
		(
			select count(*) from {{item_relationship_subcategory}}
			where item_id = a.item_id 
			and item_size_id > 0 and subcat_id > 0
		) as total_addon,
		
		(
			select count(*) from {{item_meta}}
			where item_id = a.item_id 		
			and meta_name not in ('delivery_options','dish','delivery_vehicle','item_gallery')
		) as total_meta,

		(
			select count(*) from {{item_meta}}
			where item_id = a.item_id 		
			and meta_name in ('allergens')
		) as total_allergens

		FROM {{item}} a 

		left JOIN (
		   SELECT item_id, item_name,item_description 
		   FROM {{item_translation}} 
		   where language = ".q($lang)."
		) b 
		ON a.item_id = b.item_id

		LEFT JOIN {{item_relationship_category}} c
		ON
		a.item_id = c.item_id

		WHERE a.merchant_id = ".q($merchant_id)."		
		AND c.cat_id = ".q($cat_id)."
		AND a.status='publish'
		AND a.available=1
		ORDER BY c.sequence ASC
		";								
		$dependency = CCacheData::dependency();					
        if($res = Yii::app()->db->cache(Yii::app()->params->cache, $dependency)->createCommand($stmt)->queryAll()){			
			$data = array();
			foreach ($res as $val) {					
				$price = array();
				$prices = explode(",",$val['prices']);				
				if(is_array($prices) && count($prices)>=1){
					foreach ($prices as $pricesval) {
						$sizes = explode(";",$pricesval);							
						$item_price = isset($sizes[1])?(float)$sizes[1]:0;
						$item_price = ($item_price*$exchange_rate);
						$item_discount = isset($sizes[3])?(float)$sizes[3]:0;
						$discount_type = isset($sizes[4])?$sizes[4]:'';
						$discount_valid = isset($sizes[5])?(integer)$sizes[5]:0;						
												
						$price_after_discount=0;
						if($item_discount>0 && $discount_valid>0){
							if($discount_type=="percentage"){
								$price_after_discount = $item_price - (($item_discount/100)*$item_price);
							} else {
								$price_after_discount = $item_price - ($item_discount*$exchange_rate);
							}
						
						} else $item_discount = 0;
						
						$price[] = array(
						  'size_uuid'=>isset($sizes[0])?$sizes[0]:'',
						  'item_size_id'=>isset($sizes[6])?$sizes[6]:'',
						  'price'=>$item_price,
						  'size_name'=>isset($sizes[2])?$sizes[2]:'',
						  'discount'=>$item_discount,
						  'discount_type'=>$discount_type,
						  'price_after_discount'=>$price_after_discount,
						  'pretty_price'=>Price_Formatter::formatNumber($item_price),
						  'pretty_price_after_discount'=>Price_Formatter::formatNumber($price_after_discount),
						);
					}
				}
				
				$data[] = array(  
				  'item_id'=>$val['item_id'],
				  'item_uuid'=>$val['item_token'],
				  'slug'=>$val['slug'],
				  'item_name'=> empty($val['item_name']) ? CHtml::decode($val['original_item_name']) : CHtml::decode($val['item_name']),
				  'item_description'=> empty($val['item_description']) ? CommonUtility::formatShortText($val['original_item_short_description'],130) : CommonUtility::formatShortText($val['item_description'],130),
				  'has_photo'=>!empty($val['photo'])?true:false,
				  'url_image'=>CMedia::getImage($val['photo'],$val['path'],Yii::app()->params->size_image
				  ,CommonUtility::getPlaceholderPhoto('item')),
				  'price'=>$price,
				  'total_addon'=>(integer)$val['total_addon'],
				  'total_meta'=>(integer)$val['total_meta'],
				  'total_allergens'=>intval($val['total_allergens']),
				  'qty'=>0
				);
			}
			return $data;
		}
		throw new Exception( 'no results' );
	}				

}
/*end class*/