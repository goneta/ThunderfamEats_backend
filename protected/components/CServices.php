<?php
class CServices
{
	public static function Listing($lang = KMRS_DEFAULT_LANGUAGE, $return_all=true)
	{		
		// $stmt="
		// SELECT 
		// a.service_id,
		// a.service_code,
		// a.service_name as original_service_name,
		// b.service_name,
		// a.description
		// FROM {{services}} a		
		// left JOIN (
		// 	SELECT service_id, service_name FROM {{services_translation}} where language = ".q($lang)."
		// ) b 
		// on a.service_id = b.service_id
		// WHERE a.status = 'publish'
		// ORDER BY sequence ASC
		// ";				
		$stmt = "
		SELECT 
			a.service_id,
			a.service_code,			
			a.description,
			CASE 
				WHEN b.service_name IS NOT NULL AND b.service_name != '' THEN b.service_name
				ELSE a.service_name
			END AS service_name
		FROM {{services}} a
		LEFT JOIN {{services_translation}} b 
		ON a.service_id = b.service_id AND b.language = ".q($lang)."
		WHERE a.status = 'publish'
		ORDER BY a.sequence ASC
		";
		$dependency = new CDbCacheDependency("SELECT MAX(date_modified) FROM {{services}}");
		if($res = Yii::app()->db->cache(Yii::app()->params->cache, $dependency)->createCommand($stmt)->queryAll()){			
			$data = array();
			foreach ($res as $val) {	
				if($return_all){
					$val['service_name']= empty($val['service_name']) ?   CHtml::decode($val['original_service_name']) : CHtml::decode($val['service_name']);				
					$val['description'] = t($val['description']);
					$data[$val['service_code']]=$val;
				} else {
					$data[$val['service_code']]=$val['service_name'];
				}
			}			
			return $data;
		}
		throw new Exception( 'no results' );
	}
	
	public static function List($lang = KMRS_DEFAULT_LANGUAGE)
	{		
		$stmt="
		SELECT 
		a.service_id,
		a.service_code,
		a.service_name as original_service_name,
		b.service_name,
		a.description
		FROM {{services}} a		
		left JOIN (
			SELECT service_id, service_name FROM {{services_translation}} where language = ".q($lang)."
		) b 
		on a.service_id = b.service_id
		WHERE a.status = 'publish'
		ORDER BY sequence ASC
		";				
		$dependency = new CDbCacheDependency("SELECT MAX(date_modified) FROM {{services}}");
		if($res = Yii::app()->db->cache(Yii::app()->params->cache, $dependency)->createCommand($stmt)->queryAll()){			
			$data = array();
			foreach ($res as $val) {	
				$data[]	= [
					'id'=>$val['service_id'],
					'name'=>empty($val['service_name']) ?   CHtml::decode($val['original_service_name']) : CHtml::decode($val['service_name']),
					'description'=>'',
					'url_image'=>CMedia::getImage($val['service_name'],$val['service_name'],'@thumbnail',CommonUtility::getPlaceholderPhoto('item')),
					'url_icon'=>CMedia::getImage($val['service_name'],$val['service_name'],'@thumbnail',CommonUtility::getPlaceholderPhoto('item')),
				];
			}			
			return $data;
		}
		throw new Exception( 'no results' );
	}

	public static function getFirstService()
	{	
		$criteria=new CDbCriteria();		
		$criteria->addInCondition('status', ['publish'] );
		$criteria->order ="sequence ASC";
		$model = AR_services::model()->find($criteria); 		
		if($model){
			return $model->service_code;
		}
		return false;
	}

	public static function getFirstServiceByMerchant($merchant_id=0)
	{
		if($model = AR_merchant_meta::getValue($merchant_id,'services')){									
			return $model['meta_value'];
		} else {
			return self::getFirstService();
		}		
	}
	
	public static function getSetService($cart_uuid='')
	{
		$transaction_type='';
		try {			
			$merchant_id = CCart::getMerchantId($cart_uuid);
			$transaction_type = CCart::cartTransaction($cart_uuid,Yii::app()->params->local_transtype,$merchant_id);						
		} catch (Exception $e) {			
			if($model = CCart::getAttributes($cart_uuid,Yii::app()->params->local_transtype)){
			  $transaction_type =  $model->meta_id;
			} else $transaction_type = CServices::getFirstService();
		}
		return $transaction_type;
	}
	
	public static function getMerchantServices($merchant_id=0,$lang=KMRS_DEFAULT_LANGUAGE,$format_type='label_value',$meta_name='services')
	{
		$stmt="
		SELECT 
		a.service_id,
		a.service_code,
		a.service_name as original_service_name,
		b.service_name
		FROM {{services}} a		
		left JOIN (
		   SELECT service_id, service_name FROM {{services_translation}} where language = ".q($lang)."
		) b 
		on a.service_id = b.service_id

		WHERE 
		a.status ='publish'
		and 
		a.service_code IN (
		  select meta_value from {{merchant_meta}}
		  where meta_name=".q($meta_name)."
		  and merchant_id = ".q($merchant_id)."
		)
		";				
		$dependency = CCacheData::dependency();		
		$res = Yii::app()->db->cache(Yii::app()->params->cache, $dependency)->createCommand($stmt)->queryAll();									
		if($res){
			$data = array();
			foreach ($res as $item) {			
				if($format_type=='label_value'){
					$data[] = [
						'label'=>empty($item['service_name']) ? $item['original_service_name'] : CHtml::decode($item['service_name']),
						'value'=>trim($item['service_code'])
					];
				} else {
					$data[] = [
						'service_code'=>trim($item['service_code']),
						'service_name'=> empty($item['service_name']) ? $item['original_service_name'] : CHtml::decode($item['service_name'])
					];
				}				
			}
			return $data;
		}
		throw new Exception( 'no results' );
	}
	
}
/*end class*/