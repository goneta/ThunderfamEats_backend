<?php
class CTemplates
{	
	public static function getOLD( $id='', $template_type=array(), $lang=KMRS_DEFAULT_LANGUAGE)
	{				
		$criteria=new CDbCriteria();
		$criteria->alias="a";
		$criteria->select = "a.template_id,a.template_type,a.language,a.title,a.content, b.enabled_email,b.enabled_sms,b.enabled_push";
		$criteria->join='LEFT JOIN {{templates}} b on  a.template_id = b.template_id ';
		$criteria->condition = "a.template_id=:template_id AND a.language=:language";
		$criteria->params = array(		  
		  ':template_id'=>$id,  
		  ':language'=>$lang
		);
		$criteria->addInCondition('template_type', (array) $template_type );	
		$model=AR_templates_translation::model()->findAll($criteria);	
		if($model){
			$data = array();
			foreach ($model as $item) {				
				$data[]=array(
				  'template_type'=>$item->template_type,
				  'enabled_email'=>$item->enabled_email,
				  'enabled_sms'=>$item->enabled_sms,
				  'enabled_push'=>$item->enabled_push,
				  'title'=>$item->title,
				  'content'=>$item->content,
				);
			}
			return $data;
		}
		throw new Exception( 'no results' );
	}

	public static function get( $id='', $template_type=array(), $lang=KMRS_DEFAULT_LANGUAGE)
	{
		$in_query = CommonUtility::arrayToQueryParameters($template_type);
		$stmt = "
		SELECT a.template_id,a.language, a.template_type, 
		
		IF(COALESCE(NULLIF(a.title, ''), '') = '', (
			SELECT title 
			FROM {{templates_translation}}
			WHERE template_id = a.template_id
			AND template_type = a.template_type
			AND language = ".q(KMRS_DEFAULT_LANGUAGE)."
		), a.title) as title,

		IF(COALESCE(NULLIF(a.content, ''), '') = '', (
			SELECT content 
			FROM {{templates_translation}}
			WHERE template_id = a.template_id
			AND template_type = a.template_type
			AND language = ".q(KMRS_DEFAULT_LANGUAGE)."
		), a.content) as content,

		b.enabled_email,
		b.enabled_sms,
		b.enabled_push,
		b.sms_template_id

		FROM {{templates_translation}} a
		LEFT JOIN {{templates}} b
		ON
		a.template_id = b.template_id

		WHERE a.template_id = ".q($id)."		
		AND a.language = ".q($lang)."
		AND a.template_type IN ($in_query)
		";		
		if(!$res = Yii::app()->db->createCommand($stmt)->queryAll()){	
			$stmt = "
			SELECT a.template_id,a.language, a.template_type, 
			a.title,
			a.content,						
			b.enabled_email,
			b.enabled_sms,
			b.enabled_push,
			b.sms_template_id
	
			FROM {{templates_translation}} a
			LEFT JOIN {{templates}} b
			ON
			a.template_id = b.template_id
	
			WHERE a.template_id = ".q($id)."		
			AND a.language = ".q(KMRS_DEFAULT_LANGUAGE)."
			AND a.template_type IN ($in_query)
			";			
			$res = Yii::app()->db->createCommand($stmt)->queryAll();
		}

		if(is_array($res) && count($res)>=1){
			$data = [];
			foreach ($res as $items) {
				$data[] = [
					'template_type'=>$items['template_type'],
					'enabled_email'=>$items['enabled_email'],
					'enabled_sms'=>$items['enabled_sms'],
					'enabled_push'=>$items['enabled_push'],
					'title'=>$items['title'],
					'content'=>$items['content'],
					'sms_template_id'=>$items['sms_template_id']
				];
			}
			return $data;
		}		

		throw new Exception( 'no results' );
	}
	
	public static function getMany($ids=array() , $template_type=array(), $lang=KMRS_DEFAULT_LANGUAGE )
	{
		$criteria=new CDbCriteria();
		$criteria->alias="a";
		$criteria->select = "a.template_id,a.template_type,a.language,a.title,a.content, b.enabled_email,b.enabled_sms,b.enabled_push,b.sms_template_id";
		$criteria->join='LEFT JOIN {{templates}} b on  a.template_id = b.template_id ';
		$criteria->condition = "a.language=:language";
		$criteria->params = array(		  
		  ':language'=>$lang
		);
		$criteria->addInCondition('a.template_id', (array) $ids );
		$criteria->addInCondition('a.template_type', (array) $template_type );				
		$model=AR_templates_translation::model()->findAll($criteria);
		if($model){
			$data = array();
			foreach ($model as $item) {				
				$data[$item->template_id][]=array(
				  'template_type'=>$item->template_type,
				  'enabled_email'=>$item->enabled_email,
				  'enabled_sms'=>$item->enabled_sms,
				  'enabled_push'=>$item->enabled_push,
				  'title'=>$item->title,
				  'content'=>$item->content,
				  'sms_template_id'=>$item->sms_template_id
				);
			}
			return $data;
		}
		throw new Exception( 'no results' );
	}
		
}
/*end class*/