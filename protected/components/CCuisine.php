<?php
class CCuisine
{
	public static function getList($lang=KMRS_DEFAULT_LANGUAGE , $q='', $return_type="",$status='publish')
	{
	
		$and = '';
		if(!empty($q)){
			$and = "AND b.cuisine_name LIKE ".q("$q%")." ";
		}
			
		$stmt="
		SELECT 
		a.cuisine_id,
		a.slug, 
		a.cuisine_name as original_cuisine_name, 
		b.cuisine_name,
		a.featured_image, 
		a.path,
		a.icon, 
		a.icon_path
		FROM {{cuisine}} a		

		left JOIN (
		   SELECT cuisine_id, cuisine_name FROM {{cuisine_translation}} where language =".q($lang)."
		) b 
		on a.cuisine_id = b.cuisine_id
		
		WHERE 		
		a.cuisine_name IS NOT NULL AND TRIM(a.cuisine_name) <> ''
		AND a.status = ".q($status)."
		$and
		ORDER BY a.sequence ASC
		";		
		$depency = CCacheData::dependency();				
		if($res = Yii::app()->db->cache(Yii::app()->params->cache, $depency )->createCommand($stmt)->queryAll() ){									
			$data = array();
			foreach ($res as $val) {
				
				$val['featured_image'] =  CMedia::getImage($val['featured_image'],$val['path'],
				Yii::app()->params->size_image_thumbnail,
				CommonUtility::getPlaceholderPhoto('item'));
				$val['url_icon'] =  CMedia::getImage($val['icon'],$val['icon_path'],
				Yii::app()->params->size_image_thumbnail,
				CommonUtility::getPlaceholderPhoto('icon'));								
				$val['url'] = Yii::app()->createAbsoluteUrl("cuisine/".$val['slug']);				

				$val['cuisine_name'] = empty($val['cuisine_name'])?$val['original_cuisine_name']:$val['cuisine_name'];
				$val['cuisine_name'] = CommonUtility::safeDecode($val['cuisine_name']);

				if($return_type=="sort"){
					$data[]=[
						'id'=>$val['cuisine_id'],
						'name'=>CommonUtility::safeDecode($val['cuisine_name']),					
						'url_image'=>$val['featured_image'],
						'url_icon'=>$val['url_icon'],
					];
				} else $data[]=$val;				
			}			
			return $data;
		}
		throw new Exception( 'no results' );
	}	
	
	public static function getByID($cuisine_id='',$lang=KMRS_DEFAULT_LANGUAGE)
	{
		$stmt="
		SELECT 
		a.cuisine_id,				
		IF(COALESCE(NULLIF(b.cuisine_name, ''), '') = '', a.cuisine_name, b.cuisine_name) as cuisine_name
		FROM {{cuisine}} a		
		left JOIN (
		   SELECT cuisine_id, cuisine_name FROM {{cuisine_translation}} where language =".q($lang)."
		) b 
		on a.cuisine_id = b.cuisine_id
		
		WHERE
		a.cuisine_id=".q($cuisine_id)."
		";				
		if($res = Yii::app()->db->createCommand($stmt)->queryRow	()){
			return $res;
		}
		return false;
	}

}
/*end class*/