<?php
class ClocationCountry
{
	
	public static function listing($filter=array())
	{
		$where="";		
		$only_countries = isset($filter['only_countries'])?$filter['only_countries']:'';				
		if(is_array($only_countries) && count($only_countries)>=1){
			if(!empty($only_countries[0])){
				if($country = CommonUtility::arrayToQueryParameters($only_countries)){				
					$where = "WHERE shortcode IN ($country) ";
				}
			}			
		}		
	    $stmt="
	    SELECT shortcode,country_name,phonecode
	    FROM {{location_countries}}
	    $where
	    ORDER BY country_name ASC
	    ";	 		
	    $dependency = new CDbCacheDependency('SELECT MAX(country_id) FROM {{location_countries}}');
	    if($res = Yii::app()->db->cache(Yii::app()->params->cache, $dependency)->createCommand($stmt)->queryAll() ){
	    	$data = array();
	    	foreach ($res as $val) {
	    		$val['flag']= Yii::app()->createAbsoluteUrl("themes/".Yii::app()->theme->name."/assets/flag/".strtolower($val['shortcode']).".svg");
	    		$data[]=$val;
	    	}
	    	return $data;
	    }
	    throw new Exception( 'no results' );
	}
	
	public static function get($shortcode='')
	{
		 $dependency = new CDbCacheDependency('SELECT MAX(country_id) FROM {{location_countries}}');
	     $model = AR_country::model()->cache(Yii::app()->params->cache, $dependency)->find('shortcode=:shortcode', 
		 array(':shortcode'=>$shortcode)); 	
		 if($model){
		 	return array(
		 	  'shortcode'=>$model->shortcode,
		 	  'country_name'=>$model->country_name,
		 	  'phonecode'=>$model->phonecode,
		 	  'flag'=>Yii::app()->createAbsoluteUrl("themes/".Yii::app()->theme->name."/assets/flag/".strtolower($model->shortcode).".svg"),
		 	);
		 }
		 return false;
	}

	public static function getPhoneSettings()
	{
		$options = OptionsTools::find(array('mobilephone_settings_default_country','mobilephone_settings_country'));			
		$default_country = isset($options['mobilephone_settings_default_country'])?$options['mobilephone_settings_default_country']:'us';
		$phone_country_list = isset($options['mobilephone_settings_country'])?$options['mobilephone_settings_country']:'';
		$phone_country_list = !empty($phone_country_list)?json_decode($phone_country_list,true):array();        
		
		$result = [];

		try {			

			$filter = array(
				'only_countries'=>(array)$phone_country_list
			);			  
			$data = self::listing($filter);		
			foreach ($data as $items) {				
				$result[] = [
					'label'=>"+$items[phonecode] ($items[shortcode])",
					'value'=>$items['phonecode'],
					'flag'=>$items['flag']
				];
			}

			$default_prefix = self::get($default_country);

			return [
				'prefixes'=>$result,
				'default_prefix'=>$default_prefix
			];
		} catch (Exception $e) {
			throw new Exception( 'no results' );
		}
	}

	public static function getLanguageList()
	{
		$dependency = CCacheData::dependency();        
        $model = AR_language::model()->cache(Yii::app()->params->cache, $dependency)->find("code=:code",array(
            ':code'=>Yii::app()->language
        ));           

        $criteria=new CDbCriteria();        
        $criteria->condition = "status=:status ";		    
        $criteria->params  = array(			  
            ':status'=>'publish'
        );
        $criteria->order ="sequence ASC";
        $model2 = AR_language::model()->cache(Yii::app()->params->cache, $dependency)->findAll($criteria); 

		if($model2){
			$data = [];
			foreach ($model2 as $items) {
				$data[] = [
					'code'=>$items->code,
					'title'=>$items->title,
					'description'=>$items->description,
					'flag'=>CMedia::themeAbsoluteUrl() ."/assets/flag/". strtolower($items->flag).".svg",
					'rtl'=>$items->rtl==1?true:false
				];
			}
			return [
				'data'=>$data,
				'current_flag'=>$model?$model->flag:'us',
				'current_lang'=>Yii::app()->language,
			];
		}
		throw new Exception( 'no results' );
	}
	
	public static function getCountrybyPhoneCode($prefix='')
	{
		$model = AR_location_countries::model()->find("phonecode=:phonecode",[
			':phonecode'=>$prefix
		]);
		if($model){
			return $model;
		}
		throw new Exception( 'no results' );
	}

}
/*end class*/