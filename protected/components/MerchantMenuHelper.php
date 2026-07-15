<?php
class MerchantMenuHelper
{

    public static function getExchangeRate($merchant_id='',$currency_code='')
    {

        $exchange_rate = 1;
        $base_currency = Price_Formatter::$number_format['currency_code'];
        $multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled']) ? Yii::app()->params['settings']['multicurrency_enabled'] : false;
        $multicurrency_enabled = $multicurrency_enabled == 1 ? true : false;						
        $options_merchant = OptionsTools::find(['merchant_default_currency'], $merchant_id);			
        $merchant_default_currency = isset($options_merchant['merchant_default_currency']) ? $options_merchant['merchant_default_currency'] : '';
        $merchant_default_currency = !empty($merchant_default_currency) ? $merchant_default_currency : $base_currency;			
        $currency_code = !empty($currency_code) ? $currency_code : (empty($merchant_default_currency) ? $base_currency : $merchant_default_currency);

        // SET CURRENCY
        if (!empty($currency_code) && $multicurrency_enabled) {
            Price_Formatter::init($currency_code);
            if ($currency_code != $merchant_default_currency) {
                $exchange_rate = CMulticurrency::getExchangeRate($merchant_default_currency, $currency_code);
            }
        }
        return $exchange_rate;
    }

    public static function getCategory($cat_id='')
    {
        $model = AR_category::model()->findByPk($cat_id);
        if($model){
            return $model;
        }
        return false;
    }

    public static function getMerchantIdBySlug($slug)
    {
        $model = CMerchantListingV1::getMerchantBySlug($slug);
        return $model ? $model->merchant_id : null;
    }

    public static function getMenuCategories($merchant_id, $language, $page = 1, $limit = 10)
    {
        $criteria = new CDbCriteria();
        $criteria->alias = "a";			
        $criteria->select = "
            a.cat_id,			
            IF(COALESCE(NULLIF(b.category_name, ''), '') = '', a.category_name, b.category_name) as category_name,			
            IF(COALESCE(NULLIF(b.category_description, ''), '') = '', a.category_description, b.category_description) as category_description,
            a.photo,
            a.path
        ";
        $criteria->join = "
            LEFT JOIN (
                SELECT cat_id, category_name, category_description FROM 
                {{category_translation}} 
                WHERE language = :language
            ) b ON a.cat_id = b.cat_id
        ";
        $criteria->addCondition("
            a.merchant_id=:merchant_id 
            AND a.status=:status 
            AND a.available=:available
            AND a.cat_id IN (
               select cat_id from {{item_relationship_category}}
               where 
               cat_id = a.cat_id 
               and merchant_id = a.merchant_id
               and item_id in (
                    select item_id from {{item}}
                    where status='publish'
                    and available = 1
                    and visible = 1
               )
            )            
        ");
        $criteria->params = [
            ':language' => $language,
            ':merchant_id' => $merchant_id,
            ':status' => 'publish',
            ':available' => 1
        ];
        $criteria->order = "a.sequence, a.category_name ASC";

        $count = AR_category::model()->count($criteria);
        $pages = new CPagination($count);
        $pages->pageSize = $limit;
        $pages->setCurrentPage($page);
        $pages->applyLimit($criteria);
        
        $models = AR_category::model()->findAll($criteria);
        if(!$models){
            throw new Exception(t(HELPER_NO_RESULTS));            
        }

        $data = [];

        foreach ($models as $items) {
            $data[] = [
                'cat_id' => $items->cat_id,
                'category_uiid' => CommonUtility::toSeoURL($items->category_name),
                'category_name' => CommonUtility::safeDecode($items->category_name),
                'category_description' => CommonUtility::safeDecode($items->category_description),
                'url_image' => CMedia::getImage($items->photo, $items->path, Yii::app()->params->size_image_thumbnail, CommonUtility::getPlaceholderPhoto('item')),
            ];
        }

        $remaining = $count - (($page + 1) * $limit);
        $is_last_page = $remaining <= 0;

        return [
            'is_last_page' => $is_last_page,
            'data' => $data
        ];
    }

    public static function getCategoryItems($merchant_id=0, $cat_id=0, $exchange_rate=1, 
    $search_string = null, $language='', $page = 1, $limit = 10, $query_available=true, $query_all=false)
    {        
        $criteria = new CDbCriteria();
        $criteria->alias = "item";			
        $criteria->select = "
        item.item_id,
        item.item_token as item_uuid,
        item.slug,
        item.photo,
        item.path,
        item.is_promo_free_item,
        item.available,
        IF(COALESCE(NULLIF(item_trans.item_name, ''), '') = '', item.item_name, item_trans.item_name) as item_name,		
		IF(COALESCE(NULLIF(item_trans.item_description, ''), '') = '', item.item_short_description, item_trans.item_description) as item_short_description,
        item_category.cat_id,
        (
			select GROUP_CONCAT(f.size_uuid,';',f.price,';', IF(f.size_name='',f.original_size_name,f.size_name) ,';',f.discount,';',f.discount_type,';',
			(
			select count(*) from {{view_item_lang_size}}
			where item_id = item.item_id 
			and size_uuid = f.size_uuid
			and CURDATE() >= discount_start and CURDATE() <= discount_end
			),';',f.item_size_id
		)
		
		from {{view_item_lang_size}} f
			where 
			item_id = item.item_id
			and language IN('',".q($language).")
		) as prices,

        (
			select count(*) from {{item_relationship_subcategory}}
			where item_id = item.item_id 
			and item_size_id > 0 and subcat_id > 0
		) as total_addon,

        (
			select count(*) from {{item_meta}}
			where item_id = item.item_id 		
			and meta_name not in ('delivery_options','dish','delivery_vehicle','item_gallery')
		) as total_meta,

        	(
			select count(*) from {{item_meta}}
			where item_id = item.item_id 		
			and meta_name in ('allergens')
		) as total_allergens,

        IFNULL((
			select GROUP_CONCAT(DISTINCT meta_id ORDER BY id ASC SEPARATOR ',')
			from {{item_meta}}
			where item_id = item.item_id
			and meta_name='dish'
		 ),'') as dish
        ";

        $criteria->join = "
        LEFT JOIN {{item_relationship_category}} item_category
		ON
		item.item_id = item_category.item_id

        left JOIN (
		   SELECT item_id, item_name,item_description 
		   FROM {{item_translation}} 
		   where language = :language
		) item_trans 
		ON item.item_id = item_trans.item_id
        ";
                
        $criteria->addCondition("
        item.merchant_id=:merchant_id                 
        AND item.visible=:visible
        ");        

        //AND item.status=:status        
        //':status'=>'publish',            

        $criteria->params = [
            ':merchant_id'=>$merchant_id,                        
            ':visible'=>1,
            ':language'=>$language
        ];

        if(!$query_all){
            $criteria->addCondition("item.status=:status");
            $criteria->params[':status'] = 'publish';
        }

        if($query_available){
            $criteria->addCondition("item.available=:available");
            $criteria->params[':available'] = 1;
        }

        if($cat_id){
            $criteria->addCondition("item_category.cat_id=:cat_id");
            $criteria->params[':cat_id'] = $cat_id;
        }

        if($search_string){
            $criteria->addSearchCondition("item.item_name",$search_string);
        }

        if($cat_id){
            $criteria->order = "item_category.sequence ASC";
        } else $criteria->order = "item.item_name ASC";        
                                                
        $count = AR_item::model()->count($criteria);
        $pages = new CPagination($count);
        $pages->pageSize = $limit;
        $pages->setCurrentPage($page);
        $pages->applyLimit($criteria);

        $models = AR_item::model()->findAll($criteria);
        if(!$models){
            throw new Exception(t(HELPER_NO_RESULTS));            
        }        

        $data = [];
        foreach ($models as $items) {
            $price = array();
            $prices = CommonUtility::safeExplode(",",$items->prices);
            if(is_array($prices) && count($prices)>=1){
                foreach ($prices as $pricesval) {
                    $sizes = CommonUtility::safeExplode(";",$pricesval);							
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

                    $item_price2 = $price_after_discount>0?$price_after_discount:$item_price;                    

                    $price[] = array(
                        'size_uuid'=>isset($sizes[0])?$sizes[0]:'',
                        'item_size_id'=>isset($sizes[6])?$sizes[6]:'',
                        'price'=>$item_price,
                        'price2'=>$item_price2,
                        'size_name'=>isset($sizes[2])?$sizes[2]:'',
                        'discount'=>$item_discount,
                        'discount_type'=>$discount_type,
                        'price_after_discount'=>$price_after_discount,
                        'pretty_price'=>Price_Formatter::formatNumber($item_price),
                        'pretty_price_after_discount'=>Price_Formatter::formatNumber($price_after_discount),
                    );
                }
            }      

            $dish = !empty($items->dish)?CommonUtility::safeExplode(",",$items->dish):'';

            $lowest_price = ''; $lowest_price_discount = 0; $has_discount = false;
            if(is_array($price) && count($price)>=1){
                $lowestprices = array_column($price, 'price2');				    
                $lowest_price = !empty($lowestprices) ? min($lowestprices) : null;

                $rows_with_lowest_price = array_filter($price, function($row) use ($lowest_price) {
                    return isset($row['price2']) && $row['price2'] == $lowest_price;
                });
                if(is_array($rows_with_lowest_price) && count($rows_with_lowest_price)>=1){
                    $first_match = reset($rows_with_lowest_price);						
                    $first_match_discount = $first_match['discount'] ?? null;
                    if($first_match_discount>0){
                        $lowest_price_discount  = $first_match['price_after_discount'] ?? null;
                    }
                }			
                
                $discountedItems = array_filter($price, function($item) {
                    return !empty($item['discount']) && $item['discount'] > 0;
                });
                if(is_array($discountedItems) && count($discountedItems)>=1){
                    $has_discount = true;
                }

            }				
                        
            $item_name = $items->item_name ?? '';
            $data[] = [
                'cat_id'=>$items->cat_id,
                'item_id'=>$items->item_id,
                'item_uuid'=>$items->item_uuid,
                'slug'=>$items->slug,
                'item_name'=> CommonUtility::safeDecode($item_name),
                'item_unavailable'=>t("{item_name} is not available",[
					'{item_name}'=>CommonUtility::safeDecode($item_name)
				]),
                'item_description'=> CommonUtility::formatShortText($items->item_short_description,130) ?? '',
                'is_promo_free_item'=>$items->is_promo_free_item==1?true:false,
                'has_photo'=>!empty($items->photo)?true:false,
                'url_image'=>CMedia::getImage($items->photo,$items->path,Yii::app()->params->size_image
				  ,CommonUtility::getPlaceholderPhoto('item')),		
                'lowest_price'=>Price_Formatter::formatNumber($lowest_price),
				'lowest_price_raw'=>$lowest_price,		  
                'lowest_price_label'=>t("from {lowest_price}",['{lowest_price}'=>Price_Formatter::formatNumber($lowest_price)]),
                'lowest_price_discount'=>Price_Formatter::formatNumber($lowest_price_discount),
				'lowest_price_discount_raw'=>$lowest_price_discount,
				'has_discount'=>$has_discount,
                'price'=>$price,
				'total_addon'=>(integer)$items->total_addon,
				'total_meta'=>(integer)$items->total_meta,
				'total_allergens'=>intval($items->total_allergens),
				'dish'=>$dish,
				'qty'=>0,
                'available'=>$items->available==1? true : false
            ];            
        }
        
        $remaining = $count - (($page + 1) * $limit);
        $is_last_page = $remaining <= 0;

        return [
            'page'=>$page,
            'is_last_page' => $is_last_page,
            'data' => $data
        ];
    }

}
// end class