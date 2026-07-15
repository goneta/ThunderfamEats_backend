<?php
class PPages
{
	public static function menuType()
	{
		return 'website';
	}

	public static function menuMerchantType()
	{
		return 'website_merchant';
	}
	
	public static function menuActiveKey()
	{
		return 'theme_menu_active';
	}
	
	public static function all($lang='')
	{
		$data = CommonUtility::getDataToDropDown("{{pages_translation}}",'page_id','title',
    	"where language=".q($lang)." 
    	and title IS NOT NULL AND TRIM(title) <> ''
    	and page_id IN (
    	  select page_id from {{pages}}
    	  where page_type='page'
    	  and status='publish'
		  and owner='admin'
    	)
    	"
    	);
    	return $data;
	}

	public static function merchantPages($lang='',$merchant_id=0)
	{
		$data = CommonUtility::getDataToDropDown("{{pages_translation}}",'page_id','title',
    	"where language=".q($lang)." 
    	and title IS NOT NULL AND TRIM(title) <> ''
    	and page_id IN (
    	  select page_id from {{pages}}
    	  where page_type='page'
    	  and status='publish'
		  and owner='merchant'
		  and merchant_id=".q(intval($merchant_id))."
    	)
    	"
    	);
    	return $data;
	}
	
	public static function get($page_id=0)
	{
		$model = AR_pages::model()->findByPk( intval($page_id) );
		if($model){
			return $model;
		}
		throw new Exception( 'page not found' );
	}
	
	public static function pageDetailsSlug($slug='',$lang=KMRS_DEFAULT_LANGUAGE , $where_field='a.slug')
	{
		$stmt = "
		SELECT

		CASE 
			WHEN b.title IS NOT NULL AND b.title != '' THEN b.title
			ELSE a.title
		END AS title,

		CASE 
			WHEN b.long_content IS NOT NULL AND b.long_content != '' THEN b.long_content
			ELSE a.long_content
		END AS long_content,

		CASE 
			WHEN b.meta_title IS NOT NULL AND b.meta_title != '' THEN b.meta_title
			ELSE a.meta_title
		END AS meta_title,

		CASE 
			WHEN b.meta_description IS NOT NULL AND b.meta_description != '' THEN b.meta_description
			ELSE a.meta_description
		END AS meta_description,

		CASE 
			WHEN b.meta_keywords IS NOT NULL AND b.meta_keywords != '' THEN b.meta_keywords
			ELSE a.meta_keywords
		END AS meta_keywords,

		a.short_content,
		a.meta_image,
		a.path

		FROM {{pages}} a
		left JOIN (
			SELECT page_id,title,long_content,meta_title,meta_description,meta_keywords
			FROM {{pages_translation}} where language = ".q($lang)."
		) b 
		on a.page_id = b.page_id
		WHERE
		$where_field = ".q($slug)."
		";		
		if($model = CCacheData::queryRow($stmt)){						
			return (object) [
				'title'=> $model['title'],
				'long_content'=> $model['long_content'],
				'meta_title'=> $model['meta_title'],
				'meta_description'=> $model['meta_description'],
				'meta_keywords'=> $model['meta_keywords'],
				'image'=> $model['meta_image'],
			];
		}
		throw new Exception( 'page not found' );		
	}

	public static function pageDetailsByID($page_id='', $lang='')
	{
	
		$criteria=new CDbCriteria();
		$criteria->alias ="a";
		$criteria->select = "
		IF(COALESCE(NULLIF(b.title, ''), '') = '', a.title, b.title) as title,
		IF(COALESCE(NULLIF(b.long_content, ''), '') = '', a.long_content, b.long_content) as long_content,
		IF(COALESCE(NULLIF(b.meta_title, ''), '') = '', a.meta_title, b.meta_title) as meta_title,
		IF(COALESCE(NULLIF(b.meta_description, ''), '') = '', a.meta_description, b.meta_description) as meta_description,
		IF(COALESCE(NULLIF(b.meta_keywords, ''), '') = '', a.meta_keywords, b.meta_keywords) as meta_keywords
		";
		$criteria->join = "
		left JOIN (
			SELECT page_id,title,long_content,meta_title,meta_description,meta_keywords
			FROM {{pages_translation}} where language = ".q($lang)."
		) b 
		 on a.page_id = b.page_id
		";		
		$criteria->condition = "a.page_id=:page_id";
		$criteria->params = [
			':page_id'=>$page_id
		];		
		$dependency = CCacheData::dependency();
		$model = AR_pages::model()->cache(Yii::app()->params->cache, $dependency)->find($criteria);
		if($model){
			return $model;
		}
		throw new Exception( 'page not found' );
	}	
	
	public static function pageTitleBySlug($merchant_id='', $lang='')
	{
		$criteria=new CDbCriteria();
		$criteria->alias ="a";
		$criteria->select="a.title, a.long_content, a.meta_title ,a.meta_description,a.meta_keywords,
		b.meta_image,b.path,b.slug as page_slug
		";
		$criteria->join='LEFT JOIN {{pages}} b on a.page_id = b.page_id ';
		$criteria->condition = "a.language=:language AND b.merchant_id=:merchant_id AND a.title IS NOT NULL AND TRIM(a.title) <> ''";
		$criteria->params = array(
		  ':language'=>$lang,
		  ':merchant_id'=>intval($merchant_id)
		);		
		$dependency = CCacheData::dependency();
		$model = AR_pages_translation::model()->cache(Yii::app()->params->cache, $dependency)->findAll($criteria);
		if($model){			
			$data = [];
			foreach ($model as $items) {				
				$data[$items->page_slug] = $items->title;
			}
			return $data;
		}
		return false;
	}	

	public static function getPageBySlug($slug='')
	{
		$model = AR_pages::model()->find("slug=:slug",[
			':slug'=>$slug
		]);
		if($model){
			return $model;
		}
		throw new Exception( 'page not found' );
	}	

}
/*end class*/