<?php

use MercadoPago\Resources\User\Status;

class CDataFeed {
   
    public static function categoryList($merchant_id='',$language='',$status='publish')
    {

        $criteria=new CDbCriteria();
        $criteria->alias="a";
        $criteria->select="
        a.cat_id,
        a.category_name as original_category_name,
        a.category_description as original_category_description,
        a.photo,a.path,
        b.category_name,
        b.category_description
        ";
        $criteria->join="
        LEFT JOIN (
            select cat_id,category_name,category_description
            from {{category_translation}}
            where language = ".q($language)."
        ) b
        on a.cat_id = b.cat_id
        ";
        $criteria->condition = "merchant_id=:merchant_id AND a.status=:status";
        $criteria->params = [
          ':merchant_id'=>$merchant_id,          
          ':status'=>$status
        ];            
        $criteria->order="a.sequence ASC, a.category_name ASC";   
        $dependency = CCacheData::dependency();        
        if($model = AR_category::model()->cache(Yii::app()->params->cache, $dependency)->findAll($criteria)){            
            $data = [];             
            foreach ($model as $item) {
                $data[] = [
                    'id'=>$item->cat_id,
                    'name'=> empty($item->category_name)?$item->original_category_name:$item->category_name ,
                    'description'=> empty($item->category_description)?$item->original_category_description:$item->category_description,
                    'url_image'=>CMedia::getImage($item->photo,$item->path,Yii::app()->params->size_image_thumbnail,CommonUtility::getPlaceholderPhoto('item')),
                    'url_icon'=>CMedia::getImage($item->icon,$item->icon_path,Yii::app()->params->size_image_thumbnail,CommonUtility::getPlaceholderPhoto('item')),
                ];
            }
            return $data;
        }
        throw new Exception(HELPER_NO_RESULTS);  
    }

    public static function subcategoryList($merchant_id='',$language='',$status='publish',$return_keys=false)
    {        
        $criteria=new CDbCriteria();
        $criteria->alias="a";
        $criteria->select="a.subcat_id, 
        a.subcategory_name as original_subcategory_name, 
        a.subcategory_description as original_subcategory_description, 
         a.featured_image, a.path, a.status, a.sequence,a.date_created,
        b.subcategory_name as subcategory_name, b.subcategory_description as subcategory_description";
        $criteria->join="
        LEFT JOIN (
            select subcat_id,subcategory_name,subcategory_description
            from {{subcategory_translation}}
            where language = ".q($language)."
        ) b
        on a.subcat_id = b.subcat_id
        ";
        $criteria->condition = "merchant_id=:merchant_id AND a.status=:status";
        $criteria->params = [
          ':merchant_id'=>$merchant_id,          
          ':status'=>$status
        ];    
        $criteria->order="a.sequence ASC, a.subcategory_name ASC"; 
        $dependency = CCacheData::dependency();        
        if($model = AR_subcategory::model()->cache(Yii::app()->params->cache, $dependency)->findAll($criteria)){    
            $data = [];             
            foreach ($model as $item) {
                if($return_keys){
                    $data[$item->subcat_id] = [
                        'id'=>$item->subcat_id,
                        'name'=> empty($item->subcategory_name) ? $item->original_subcategory_name : $item->subcategory_name,
                        'description'=> empty($item->subcategory_description)? $item->original_subcategory_description : $item->subcategory_description,
                        'url_image'=>CMedia::getImage($item->featured_image,$item->path,Yii::app()->params->size_image_thumbnail,CommonUtility::getPlaceholderPhoto('item')),                    
                    ];
                } else {
                    $data[] = [
                        'id'=>$item->subcat_id,
                        'name'=> empty($item->subcategory_name) ? $item->original_subcategory_name : $item->subcategory_name,
                        'description'=> empty($item->subcategory_description)? $item->original_subcategory_description : $item->subcategory_description,
                        'url_image'=>CMedia::getImage($item->featured_image,$item->path,Yii::app()->params->size_image_thumbnail,CommonUtility::getPlaceholderPhoto('item')),                    
                    ];
                }                
            }
            return $data;
        }
        throw new Exception(HELPER_NO_RESULTS);  
    }

    public static function subcategoryItemList($merchant_id='',$language='',$status='publish',$return_keys=false)
    {       
        $criteria=new CDbCriteria();
        $criteria->alias="a";
        $criteria->select="
        a.sub_item_id,
        a.photo, a.path, a.status, a.sequence,
        b.sub_item_name,b.item_description,
        a.sub_item_name as original_sub_item_name, a.item_description as original_item_description
        ";
        $criteria->join="
        LEFT JOIN (
            select sub_item_id,sub_item_name,item_description
            from {{subcategory_item_translation}}
            where language = ".q($language)."
        ) b
        on a.sub_item_id = b.sub_item_id
        ";
        $criteria->condition = "a.merchant_id=:merchant_id AND a.status=:status";
        $criteria->params = [
          ':merchant_id'=>$merchant_id,          
          ':status'=>$status
        ];    
        $criteria->order="a.sequence ASC, a.sub_item_name ASC";        
                        
        $dependency = CCacheData::dependency();        
        if($model = AR_subcategory_item::model()->cache(Yii::app()->params->cache, $dependency)->findAll($criteria)){                                    
            $data = [];             
            foreach ($model as $item) {                
                if($return_keys){
                    $data[$item->sub_item_id] = [
                        'id'=>$item->sub_item_id,
                        'name'=> empty($item->sub_item_name)?$item->original_sub_item_name:$item->sub_item_name ,
                        'description'=>empty($item->item_description)?$item->original_item_description:$item->item_description,
                        'url_image'=>CMedia::getImage($item->photo,$item->path,Yii::app()->params->size_image_thumbnail,CommonUtility::getPlaceholderPhoto('item')),                    
                    ];
                } else {
                    $data[] = [
                        'id'=>$item->sub_item_id,
                        'name'=> empty($item->sub_item_name)?$item->original_sub_item_name:$item->sub_item_name ,
                        'description'=>empty($item->item_description)?$item->original_item_description:$item->item_description,
                        'url_image'=>CMedia::getImage($item->photo,$item->path,Yii::app()->params->size_image_thumbnail,CommonUtility::getPlaceholderPhoto('item')),                    
                    ];
                }                
            }
            return $data;
        }        
        throw new Exception(HELPER_NO_RESULTS);  
    }

    public static function getCategorySelected($merchant_id=0,$item_id=0, $language='', $result_type="list")
    {
        // $stmt="
        // SELECT a.item_id,a.cat_id, b.category_name
        // FROM {{item_relationship_category}} a
        // LEFT JOIN {{category_translation}} b 
        // ON 
        // a.cat_id = b.cat_id
        // WHERE
        // merchant_id = ".q($merchant_id)."
        // AND item_id=".q($item_id)."
        // AND b.language=".q($language)."
        // ";
        $stmt="
        SELECT a.item_id,a.cat_id, 
        b.category_name
        FROM {{item_relationship_category}} a        
        left JOIN (
            SELECT cat_id, category_name FROM {{category_translation}} where language=".q($language)."
        ) b 
        ON 
        a.cat_id = b.cat_id
        WHERE
        a.merchant_id = ".q($merchant_id)."
        AND a.item_id=".q($item_id)."        
        "; 
        if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
            if($result_type=="list"){
                $data = [];
                foreach ($res as $key => $items) {
                    $data[] = [
                        'label'=>$items['category_name'],
                        'value'=>$items['cat_id'],
                    ];
                }
                return $data;
            } else return $res;            
        }
        throw new Exception(HELPER_NO_RESULTS);  
    }

    public static function getFeaturedSelected($merchant_id=0,$item_id=0,$result_type="list")
    {
        $model = AR_item_meta::getMeta($merchant_id,$item_id,['item_featured']);        
        if($model){
            $atts  = AttributesTools::ItemFeatured();            
            if($result_type=="list"){
                foreach ($model as $items) {
                    $data[] = [
                        'label'=> isset($atts[$items['meta_id']]) ? $atts[$items['meta_id']]  :'' ,
                        'value'=>$items['meta_id'],                        
                    ];
                }
                return $data;
            } else return $model;
        }
        throw new Exception(HELPER_NO_RESULTS); 
    }

    public static function getSelectedSize($merchant_id=0, $item_id=0 , $result_type="list")
    {
                
        $criteria=new CDbCriteria();
        $criteria->alias="a";
        $criteria->select = "a.item_size_id, a.size_id , b.size_name as item_name";
        $criteria->condition = "a.merchant_id=:merchant_id AND a.item_id=:item_id";
        $criteria->join='LEFT JOIN {{size}} b on a.size_id = b.size_id';
        $criteria->params = [
          ':merchant_id'=>intval($merchant_id),
          ':item_id'=>intval($item_id)
        ];        
                  
        $dependency = CCacheData::dependency();        
        if($model = AR_item_relationship_size::model()->cache(Yii::app()->params->cache, $dependency)->findAll($criteria)){         
            if($result_type=="list"){
                $data = [];                
                foreach ($model as $items) {
                    $data[] = [
                        'label'=>$items->item_name,
                        'value'=>$items->size_id,
                    ];
                }
                return $data;
           } else return $model;
        }
        throw new Exception(HELPER_NO_RESULTS); 
    }

    public static function getItemPrice($merchant_id = '', $item_id=0, $language='')
    {
        $size = AttributesTools::Size( $merchant_id );        
        $stmt = "
        SELECT a.price, a.size_id, a.item_size_id        
        FROM {{item_relationship_size}} a        
        WHERE merchant_id=".q($merchant_id)."
        AND item_id = ".q($item_id)."                
        ORDER BY a.price ASC
        ";                
        if($res = Yii::app()->db->createCommand($stmt)->queryAll()){            
            foreach ($res as $items) {                
                $data[] = [
                    'price'=>Price_Formatter::formatNumber($items['price']),
                    'raw_price'=>$items['price'],
                    'item_size_id'=>$items['item_size_id'],
                    'size_name'=> isset($size[$items['size_id']])?$size[$items['size_id']]:'' ,
                ];
            }
            return $data;
        }
        throw new Exception(HELPER_NO_RESULTS); 
    }

    public static function getItemAddon($merchant_id = '', $item_id=0, $language='')
    {
        $stmt = "
		 	 SELECT SQL_CALC_FOUND_ROWS
			 a.*,	
			 IF(a.require_addon>0,".q(t("Yes")).",".q(t("No")).") as require_addon,
			 IFNULL(b.subcategory_name, '' ) as subcategory_name,
			 
			 IFNULL(c.price,'') as price,
			 IFNULL(c.size_name,'') as size_name
			 
			 FROM {{item_relationship_subcategory}} a	
			 JOIN {{subcategory}} b
			 ON 
			 a.subcat_id  = b.subcat_id
			 
			 JOIN {{view_item_size}} c
			 ON 
			 a.item_size_id  = c.item_size_id
			 
			 WHERE a.merchant_id = ".q($merchant_id)."			 
			 AND a.item_id = ".q($item_id)."			
		";		        
        if($res = Yii::app()->db->createCommand($stmt)->queryAll()){ 
            foreach ($res as $items) {                
                $data[] = [
                    'id'=>$items['id'],
                    'subcategory_name'=>$items['subcategory_name'],
                    'price'=>Price_Formatter::formatNumber($items['price']),
                    'size_name'=>$items['size_name']
                ];
            }
            return $data;
        } else throw new Exception(HELPER_NO_RESULTS); 
    }

    public static function getTotalAddon($merchant_id = '', $item_id=0)
    {
        $count = AR_item_addon::model()->count("merchant_id=:merchant_id AND item_id=:item_id",[
            ':merchant_id'=>$merchant_id,
            ':item_id'=>$item_id
        ]);
        if($count){
            return $count;
        } else throw new Exception(HELPER_NO_RESULTS); 
    }

    public static function getItemMeta($merchant_id=0, $item_id=0, $meta_name='')
    {
        $selected = array();
        $find = AR_item_meta::model()->findAll(
            'item_id=:item_id AND merchant_id=:merchant_id AND meta_name=:meta_name',
            array(  
               ':item_id'=> intval($item_id),
               ':merchant_id'=> intval($merchant_id),
               ':meta_name'=>$meta_name
            )
        );
        if($find){            
            foreach ($find as $items) {					
                $selected[]=$items->meta_id;
            }            
        }	
        return $selected;	        
    }

    public static function getSalePromotion($merchant_id=0)
    {
        $stmt = "
		 	 SELECT SQL_CALC_FOUND_ROWS
			 a.*,
			 IFNULL(b.item_name,'') as item_name
			  			 			
			 FROM {{item_promo}} a			 
			 LEFT JOIN {{item}} b
			 ON
			 a.item_id_promo = b.item_id
			 
			 WHERE a.merchant_id = ".q($merchant_id)."
             ORDER BY promo_id DESC
		";		 			

        if($res = Yii::app()->db->createCommand($stmt)->queryAll()){             
            $data = [];
            $promo_type = AttributesTools::ItemPromoType2();            
            foreach ($res as $items) {
                $promo_type = isset($promo_type[$items['promo_type']])?$promo_type[$items['promo_type']]:'';
                $promo_type = t($promo_type,[
                    '(buy_qty)'=>$items['buy_qty'],
                    '(get_qty)'=>$items['get_qty'],
                ]);                
                $data[] = [
                    'promo_id'=>$items['promo_id'],
                    'item_id'=>$items['item_id'],
                    'promo_type'=>$items['promo_type'],
                    'item_id_promo'=>$items['item_id_promo'],
                    'name'=>t("{promo_type} {item_name}",[
                        '{promo_type}'=>$promo_type,
                        '{item_name}'=>$items['item_name']                        
                    ])
                ];
            }            
            return $data;
        } else throw new Exception(HELPER_NO_RESULTS); 
    }    

    public static function getAddoncategorySize($merchant_id='',$item_id=0)
    {        
        $stmt = "        
        SELECT DISTINCT a.item_size_id, b.size_id,
        (
        select GROUP_CONCAT(subcat_id ORDER BY sequence ASC)
                    from {{item_relationship_subcategory}}
                    where item_id = a.item_id
                    and item_size_id = a.item_size_id
        ) as addoncategory
        FROM {{item_relationship_subcategory}} a

        left JOIN (
            SELECT size_id,item_size_id FROM {{item_relationship_size}}
        ) b 
        on a.item_size_id = b.item_size_id

        WHERE a.item_id=".q($item_id)."
        and a.merchant_id=".q($merchant_id)."
        ";        
        $dependency = CCacheData::dependency();					
        if($res = Yii::app()->db->cache(Yii::app()->params->cache, $dependency)->createCommand($stmt)->queryAll()){                        
            $data = [];
            foreach ($res as $items) {                
                $addoncategory = isset($items['addoncategory'])?explode(",",$items['addoncategory']):'';                
                $data[] = [                    
                    'item_size_id'=>$items['item_size_id'],
                    'size_id'=>$items['size_id'],
                    'addoncategory'=>$addoncategory
                ];
            }
            return $data;
        }
        throw new Exception(HELPER_NO_RESULTS); 
    }

    public static function getSizeList($merchant_id=0,$language=KMRS_DEFAULT_LANGUAGE)
    {
        $stmt = "
        SELECT a.size_id,a.size_name as original_size_name, b.size_name
        FROM {{size}} a
        left JOIN (
            SELECT size_id,size_name FROM {{size_translation}} where language=".q($language)."
        ) b 
        ON a.size_id = b.size_id
        WHERE a.merchant_id = ".q($merchant_id)."
        AND a.status='publish'
        ";        
        $dependency = CCacheData::dependency();					
        if($res = Yii::app()->db->cache(Yii::app()->params->cache, $dependency)->createCommand($stmt)->queryAll()){            
            $data = [];
            foreach ($res as $items) {
                $data[$items['size_id']] = [
                    'size_id'=>$items['size_id'],
                    'size_name'=>empty($items['size_name'])?$items['original_size_name']:$items['size_name'],
                ];
            }
            return $data;
        }
        throw new Exception(HELPER_NO_RESULTS); 
    }

    public static function getAddonItemsList($merchant_id=0,$language=KMRS_DEFAULT_LANGUAGE)
    {
        $stmt = "    
        SELECT a.subcat_id,a.subcategory_name as original_subcategory_name,
        b.subcategory_name,
        (
        select GROUP_CONCAT(sub_item_id ORDER BY sequence ASC)
        FROM {{subcategory_item_relationships}}
        where 
        subcat_id = a.subcat_id
        ) as addonitems
        from {{subcategory}} a
        left JOIN (
            SELECT subcat_id, subcategory_name FROM {{subcategory_translation}} where language = ".q($language)."
        ) b 
        on a.subcat_id = b.subcat_id
        WHERE
        a.merchant_id=".q($merchant_id)."
        and a.status ='publish'
        ORDER BY a.sequence ASC
        ";        
        $dependency = CCacheData::dependency();					
        if($res = Yii::app()->db->cache(Yii::app()->params->cache, $dependency)->createCommand($stmt)->queryAll()){            
            $data = [];
            foreach ($res as $items) {                
                $addonitems = isset($items['addonitems'])?explode(",",$items['addonitems']):'';
                $data[] = [                                        
                    'subcat_id'=>$items['subcat_id'],
                    'subcategory_name'=> empty($items['subcategory_name']) ? $items['original_subcategory_name'] : $items['subcategory_name'],
                    'addoncategory'=>$addonitems
                ];
            }
            return $data;
        }
        throw new Exception(HELPER_NO_RESULTS); 
    }
    
}
// end class