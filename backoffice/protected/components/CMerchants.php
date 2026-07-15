<?php
class CMerchants
{	
	public static function get($merchant_id='')
	{
		$dependency = CCacheData::dependency();
		$model = AR_merchant::model()->cache(Yii::app()->params->cache, $dependency)->find('merchant_id=:merchant_id', 
		array(':merchant_id'=>$merchant_id)); 
		if($model){
			return $model;
		}
		throw new Exception( 'merchant not found' );
	}
	
	public static function getByUUID($merchant_uuid='')
	{
		$dependency = CCacheData::dependency();
		$model = AR_merchant::model()->cache(Yii::app()->params->cache, $dependency)->find('merchant_uuid=:merchant_uuid', 
		array(':merchant_uuid'=>$merchant_uuid)); 
		if($model){
			return $model;
		}
		throw new Exception( 'merchant not found' );
	}
	
	public static function getTotalOrders($merchant_id=0)
	{
		$draft = AttributesTools::initialStatus();
		$not_in_status = AOrderSettings::getStatus(array('status_cancel_order','status_rejection'));
		array_push($not_in_status,$draft);    		
		$criteria=new CDbCriteria();
		$criteria->select="sum(total) as total";		
		$criteria->condition = "merchant_id=:merchant_id";		    
		$criteria->params  = array(
		  ':merchant_id'=>intval($merchant_id)		  
		);				
		$criteria->addNotInCondition('status', (array)$not_in_status );
		$count = AR_ordernew::model()->count($criteria); 
		return intval($count);
	}
	
	public static function getMerchantType($merchant_id=0)
	{
		$model = self::get($merchant_id);
		if($model){
			return $model->merchant_type;
		}
	}

	public static function getBanner($merchant_id=0,$owner='merchant')
	{		
		$criteria=new CDbCriteria();

		if($merchant_id>0){
			$criteria->condition = "owner=:owner AND meta_value1=:meta_value1 AND status=:status";		    
			$criteria->params  = array(
			':owner'=>$owner,
			':meta_value1'=>intval($merchant_id),
			':status'=>1
			);				
		} else {
			$criteria->condition = "owner=:owner AND status=:status";		    
			$criteria->params  = array(
			':owner'=>$owner,			
			':status'=>1
			);		
		}
		$criteria->order = "sequence ASC";		
		$cache = CCacheData::dependency();
		$model = AR_banner::model()->cache(Yii::app()->params->cache, CCacheData::dependency() )->findAll($criteria); 
		if($model){
			$data = [];
			foreach ($model as $items) {
				$url = '';
				if($items->banner_type=="cuisine"){
					$url = Yii::app()->createAbsoluteUrl("/cuisine/".$items->meta_slug);
				} else if ( $items->banner_type=="restaurant_featured" ){
					$url = Yii::app()->createAbsoluteUrl("/featured/?id=".$items->meta_slug);
				} else if ( $items->banner_type=="food" ){
					$url = Yii::app()->createAbsoluteUrl($items->meta_slug);
				} else if ( $items->banner_type=="restaurant" ){
					$url = Yii::app()->createAbsoluteUrl($items->meta_slug);
				}
				$data[] = [
					'banner_id'=>$items->banner_id,
					'banner_uuid'=>$items->banner_uuid,
					'title'=>CHtml::encode($items->title),
					'banner_type'=>$items->banner_type,
					'image'=>CMedia::getImage($items->photo,$items->path),
					'merchant_id'=>$items->meta_value1,
					'item_id'=>$items->meta_value2,
					'featured'=>$items->meta_value3,
					'cuisine_id'=>$items->meta_value4,
					'meta_slug'=>$items->meta_slug,
					'url'=>$url
				];
			}			
			return $data;
		}
		throw new Exception( 'Banner not found' );
	}

	public static function getBannerByRadius($merchant_id=0,$owner='admin',$coordinates=[])
	{
		
		$and = '';
		if($merchant_id>0){
			$and = " AND meta_value1=merchant_id";
		}
		$stmt = "
			SELECT *, 
			( 
				CASE 
					WHEN radius_unit = 'mi' THEN 3959 
					ELSE 6371 
				END * acos( cos( radians(:latitude) ) 
				* cos( radians( latitude ) ) 
				* cos( radians( longitude ) - radians(:longitude) ) 
				+ sin( radians(:latitude) ) 
				* sin( radians( latitude ) ) ) 
			) AS distance
			FROM {{banner}}
			HAVING (distance <= radius AND status = 1 AND owner = :owner $and) 
            OR (latitude IS NULL AND longitude IS NULL AND status = 1 AND owner = :owner $and)
			ORDER BY sequence ASC
		";		
		$command = Yii::app()->db->createCommand($stmt);
		$command->bindParam(":latitude", $coordinates['latitude'], PDO::PARAM_STR);
		$command->bindParam(":longitude", $coordinates['longitude'], PDO::PARAM_STR);
		$command->bindParam(":owner", $owner, PDO::PARAM_STR);		
		if($merchant_id>0){
			$command->bindParam(":merchant_id", $merchant_id, PDO::PARAM_INT);		
		}
						
		if($resp = $command->queryAll()){				
			$data = [];
			foreach ($resp as $items) {
				$url = '';
				if($items['banner_type']=="cuisine"){
					$url = Yii::app()->createAbsoluteUrl("/cuisine/".$items['meta_slug']);
				} else if ( $items['banner_type']=="restaurant_featured" ){
					$url = Yii::app()->createAbsoluteUrl("/featured/?id=".$items['meta_slug']);
				} else if ( $items['banner_type']=="food" ){
					$url = Yii::app()->createAbsoluteUrl($items['meta_slug']);
				} else if ( $items['banner_type']=="restaurant" ){
					$url = Yii::app()->createAbsoluteUrl($items['meta_slug']);
				}
				$data[] = [
					'banner_id'=>$items['banner_id'],
					'banner_uuid'=>$items['banner_uuid'],
					'title'=>CHtml::encode($items['title']),
					'banner_type'=>$items['banner_type'],
					'image'=>CMedia::getImage($items['photo'],$items['path']),
					'merchant_id'=>$items['meta_value1'],
					'item_id'=>$items['meta_value2'],
					'featured'=>$items['meta_value3'],
					'cuisine_id'=>$items['meta_value4'],
					'meta_slug'=>$items['meta_slug'],
					'url'=>$url
				];
			}
			return $data;
		} 
		throw new Exception( 'Banner not found' ); 
	}

	public static function MapsConfig($merchant_id=0,$geocoding_api = true)
	{		
		if($merchant_id>0){
			$items = OptionsTools::find([
				'merchant_map_provider','merchant_google_geo_api_key','merchant_google_maps_api_key','merchant_mapbox_access_token','merchant_google_maps_api_key_for_mobile'
			],$merchant_id);

			$default_location_lat = '34.04703'; 
			$default_location_lng ='-118.246860';

			if(isset(Yii::app()->params['settings'])){
				$default_location_lat = isset(Yii::app()->params['settings']['default_location_lat'])?
				( !empty(Yii::app()->params['settings']['default_location_lat'])?Yii::app()->params['settings']['default_location_lat']: $default_location_lat )
				:$default_location_lat;

				$default_location_lng = isset(Yii::app()->params['settings']['default_location_lng'])?
				( !empty(Yii::app()->params['settings']['default_location_lng'])?Yii::app()->params['settings']['default_location_lng']: $default_location_lng )
				:$default_location_lng;
			}

			$provider = isset($items['merchant_map_provider'])?$items['merchant_map_provider']:'';
			$google_geo_api_key = isset($items['merchant_google_geo_api_key'])?$items['merchant_google_geo_api_key']:'';
			$google_maps_api_key_old = isset($items['merchant_google_maps_api_key'])?$items['merchant_google_maps_api_key']:'';
			$google_maps_api_key = isset($items['merchant_google_maps_api_key_for_mobile'])?$items['merchant_google_maps_api_key_for_mobile']:'';
			$google_maps_api_key = !empty($google_maps_api_key) ? $google_maps_api_key : $google_maps_api_key_old;
			$mapbox_access_token = isset($items['merchant_mapbox_access_token'])?$items['merchant_mapbox_access_token']:'';
			
			MapSdk::$map_provider = $provider;
			MapSdk::setKeys(array(
			  'google.maps'=>$geocoding_api==true?$google_geo_api_key:$google_maps_api_key,
			  'mapbox'=>$mapbox_access_token
			));
			return array(
				'provider'=>MapSdk::$map_provider,
				'key'=>MapSdk::$api_key,
				'zoom'=>15,		  
				'icon'=>websiteDomain().Yii::app()->theme->baseUrl."/assets/images/marker2@2x.png",
				'icon_merchant'=>websiteDomain().Yii::app()->theme->baseUrl."/assets/images/restaurant-icon1.png",
				'icon_destination'=>websiteDomain().Yii::app()->theme->baseUrl."/assets/images/home-icon1.png",
				'icon_rider'=>websiteDomain().Yii::app()->theme->baseUrl."/assets/images/rider-icon.svg",
				'default_lat'=> $default_location_lat,
			    'default_lng'=> $default_location_lng,
			  );			
		}
		return false;
	}

	public static function getListByID($merchant_ids=array())
	{
		$criteria=new CDbCriteria();
		$criteria->addInCondition("merchant_id",(array)$merchant_ids);
		if($model = AR_merchant::model()->findAll($criteria)){
			$data = [];
			foreach($model as $items){
				$data[$items->merchant_id] = [
					'merchant_id'=>$items->merchant_id,
					'restaurant_name'=>$items->restaurant_name,
					'address'=>$items->address,
					'latitude'=>$items->latitude,
					'lontitude'=>$items->lontitude,
					'contact_phone'=>$items->contact_phone,
					'contact_email'=>$items->contact_email,
					'logo'=>$items->logo,
					'logo_url'=>CMedia::getImage($items->logo,$items->path),
				];
			}
			return $data;
		}
		throw new Exception( HELPER_NO_RESULTS );
	}

	public static function getListMerchantZone($merchant_ids=array())
	{
		$query = CommonUtility::arrayToQueryParameters($merchant_ids);
		$stmt = "
		SELECT a.merchant_id,
		
		IFNULL((
			select GROUP_CONCAT(DISTINCT meta_value SEPARATOR ',')
			from {{merchant_meta}}
			where merchant_id = a.merchant_id
			and meta_name = 'zone'			
		),'') as items

		FROM {{merchant}} a
		WHERE merchant_id IN (".$query.")
		";		
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
			$data = [];
			foreach($res as $items){				
				$data[$items['merchant_id']] = explode(",",$items['items']);
			}
			return $data;
		}
		return false;
	}

	public static function getCommissionData($merchant_id=0)
	{
		$model = AR_merchant_commission_order::model()->findAll("merchant_id=:merchant_id",[
			':merchant_id'=>intval($merchant_id)
		]);
		if($model){
			$commission_type = []; $commission_value = [];
			foreach ($model as $items) {
				$commission_type[$items->transaction_type] = $items->commission_type;
				$commission_value[$items->transaction_type] = $items->commission;
			}
			return [
				'commission_type'=>$commission_type,
				'commission_value'=>$commission_value,
			];
		}
		return false;
	}

	public static function getOldCommissionData($commissionType='',$commission=0)
	{		
		if($list = CServices::Listing(Yii::app()->language)){
			$commission_type = []; $commission_value = [];
			foreach ($list as $items) {
				$commission_type[$items['service_code']] = $commissionType;
				$commission_value[$items['service_code']] = $commission;
			}
			return [
				'commission_type'=>$commission_type,
				'commission_value'=>$commission_value,
			];
		}
		return false;
	}

	public static function getCommissionByTransaction($merchant_id=0,$transaction_type='')
	{
		$model = AR_merchant_commission_order::model()->find("merchant_id=:merchant_id AND transaction_type=:transaction_type",[
			':merchant_id'=>intval($merchant_id),
			':transaction_type'=>$transaction_type
		]);
		if($model){
			return [
				'commission_type'=>$model->commission_type,
				'commission'=>$model->commission
			];
		}
		return false;
	}	

	public static function getOpeningHours($merchant_id=0, $format_hours=false,$with_default=false,$time_config_type='regular_hours',$transaction_type='')
	{
		$data = [];				
		$criteria=new CDbCriteria();
		$criteria->condition = "merchant_id=:merchant_id AND time_config_type=:time_config_type";
		$criteria->params = [
			':merchant_id'=>intval($merchant_id),
			':time_config_type'=>$time_config_type
		];
		if($time_config_type=="custom_hours_transaction"){
			$criteria->addCondition("transaction_type = :transaction_type");
			$criteria->params[':transaction_type'] = $transaction_type;
		}				
		$model = AR_opening_hours::model()->findAll($criteria);	
		if($model){			
			foreach ($model as $items) {				
				if($time_config_type=="schedule_day"){
					$data[] = [
						'id'=>$items->id,
						'status'=>$items->status,
						'close'=>$items->status=="close"?true:false,
						'start_time'=> $format_hours? Date_Formatter::Time($items->start_time,"HH:mm",true) : $items->start_time,
						'end_time'=> $format_hours ? Date_Formatter::Time($items->end_time,"HH:mm",true) : $items->end_time,
						'start_time_pretty'=> $format_hours? Date_Formatter::Time($items->start_time) : $items->start_time,
						'end_time_pretty'=> $format_hours ? Date_Formatter::Time($items->end_time) : $items->end_time,
						'custom_text'=>$items->custom_text
					];
				} else {
					$data[$items->day][] = [
						'id'=>$items->id,
						'status'=>$items->status,
						'close'=>$items->status=="close"?true:false,
						'start_time'=> $format_hours? Date_Formatter::Time($items->start_time,"HH:mm",true) : $items->start_time,
						'end_time'=> $format_hours ? Date_Formatter::Time($items->end_time,"HH:mm",true) : $items->end_time,
						'start_time_pretty'=> $format_hours? Date_Formatter::Time($items->start_time) : $items->start_time,
						'end_time_pretty'=> $format_hours ? Date_Formatter::Time($items->end_time) : $items->end_time,
						'custom_text'=>$items->custom_text
					];
			    }
			}			
		} else {
			if($with_default){			
				$days = AttributesTools::dayList();
				foreach ($days as $day_code => $day) {
					$data[$day_code][] = [
						'id'=>0,
						'status'=>"open",
						'close'=>false,
						'start_time'=>"00:00",
						'end_time'=>"00:00",
						'custom_text'=>""
					];
				}
		    }
		}
		return $data;
	}

	public static function BannerItems()
	{
		$stmt = "	
		SELECT
		a.meta_value2 as item_id,
		b.item_token as item_uuid,
		c.cat_id,
		d.restaurant_slug

		FROM {{banner}} a
		LEFT JOIN {{item}} b
		on
		a.meta_value2 = b.item_id

		LEFT JOIN (
		SELECT cat_id,item_id from {{item_relationship_category}}
		) c
		on a.meta_value2  = c.item_id

		LEFT JOIN (
		select merchant_id,restaurant_slug from {{merchant}}
		) d
		on a.meta_value1  = d.merchant_id

		WHERE
		a.owner='admin'
		and a.banner_type='food'
		and a.status=1
		";
		if($res = CCacheData::queryAll($stmt)){
			$data = [];
			foreach ($res as $items) {
				$data[$items['item_id']] = $items;
			}
			return $data;
		}
		return false;
	}
	
	public static function BannerMerchant()
	{
		$stmt = "
		SELECT
		distinct a.meta_value1 as merchant_id,
		b.restaurant_slug

		FROM {{banner}} a
		left join {{merchant}} b
		on
		a.meta_value1 = b.merchant_id

		where
		a.owner='admin'
		and a.banner_type='restaurant'
		and a.status=1
		";
		if($res = CCacheData::queryAll($stmt)){
			$data = [];
			foreach ($res as $items) {
				$data[$items['merchant_id']] = $items;
			}
			return $data;
		}
		return false;
	}
	
	public static function BannerCuisine($lang=KMRS_DEFAULT_LANGUAGE)
	{
		$stmt="
		SELECT 
		a.cuisine_id,		
		a.cuisine_name as original_cuisine_name, 
		b.cuisine_name		
		FROM {{cuisine}} a		

		left JOIN (
		   SELECT cuisine_id, cuisine_name FROM {{cuisine_translation}} where language =".q($lang)."
		) b 
		on a.cuisine_id = b.cuisine_id
		
		WHERE 		
		a.cuisine_name IS NOT NULL AND TRIM(a.cuisine_name) <> ''		
		ORDER BY a.sequence ASC
		";		
		if($res = CCacheData::queryAll($stmt)){
			foreach ($res as $items) {
				$data[$items['cuisine_id']] = !empty($items['original_cuisine_name'])?$items['original_cuisine_name']:$items['cuisine_name'];
			}
			return $data;
		}
		return false;
	}

	public static function SearchMerchant($search_string='')
	{
		$data = [];
		$criteria=new CDbCriteria();
		$criteria->condition = "status=:status";
		$criteria->params = [
			':status'=>"active"
		];
		$criteria->addSearchCondition('restaurant_name', $search_string);				
		$model = AR_merchant::model()->findAll($criteria);				
		if($model){
			foreach ($model as $items) {				
				$photo = CMedia::getImage($items->logo,$items->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('merchant_logo'));
				$data[] = [
					'merchant_id'=>$items->merchant_id,
					'user_type'=>t("Merchant"),
					'user_type_raw'=>"merchant",
					'client_id'=>$items->merchant_id,
					'client_uuid'=>$items->merchant_uuid,                    
					'first_name'=>$items->restaurant_name,
					'last_name'=>'',
					'photo'=>$items->logo,
					'photo_url'=>$photo,
				];
			}
			return $data;
		}	
		throw new Exception(HELPER_NO_RESULTS);  
	}

	public static function getAllByUUID($uuid=array())
    {
        $criteria=new CDbCriteria();
        $criteria->addCondition('status=:status');		
        $criteria->params = [
            ':status'=>"active"            
        ];
        $criteria->addInCondition('merchant_uuid',(array)$uuid);
        $criteria->order = "restaurant_name ASC";
        $model = AR_merchant::model()->findAll($criteria);
        if($model){
            $data = [];			
            foreach ($model as $items) {
                $photo = CMedia::getImage($items->logo,$items->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('merchant_logo'));				
				$data[$items->merchant_uuid] = [
					'user_type'=>t("Merchant"),
					'user_type_raw'=>"merchant",
                    'client_id'=>$items->merchant_id,
                    'client_uuid'=>$items->merchant_uuid,                    
                    'first_name'=>$items->restaurant_name,
                    'last_name'=>'',
                    'phone_prefix'=>'',
                    'contact_phone'=>$items->restaurant_phone, 
					'email_address'=>$items->contact_email, 
                    'photo'=>$items->logo,
                    'photo_url'=>$photo,
					'profile_url'=>Yii::app()->createAbsoluteUrl(BACKOFFICE_FOLDER."/vendor/edit",[
						'id'=>$items->merchant_id
					])
                ];
            }            
            return $data;
        }
        throw new Exception(HELPER_NO_RESULTS);  
    }
	
	public static function getSEO($merchant_id=0,$language=KMRS_DEFAULT_LANGUAGE)
	{
		$dependency = CCacheData::dependency();		
		$model = AR_pages_seo::model()->cache(Yii::app()->params->cache, $dependency)->find("owner=:owner AND merchant_id=:merchant_id",[
			':owner'=>"merchant_seo",
			':merchant_id'=>$merchant_id
		]);
		if($model){
			$models = PPages::pageDetailsSlug($model->page_id , $language , "a.page_id" ); 			
			return $models;
		}
		throw new Exception(HELPER_NO_RESULTS);  
	}

	public static function getMerchantInfo($merchant_id=0)
	{
		$dependency = CCacheData::dependency();
		$model = AR_merchant::model()->cache(Yii::app()->params->cache, $dependency)->find('merchant_id=:merchant_id', 
		array(':merchant_id'=>$merchant_id)); 
		if($model){
			return [
				'merchant_id'=>$model->merchant_id,
				'restaurant_name'=>$model->restaurant_name,
				'restaurant_phone'=>$model->restaurant_phone,
				'contact_phone'=>$model->contact_phone,
				'contact_name'=>$model->contact_name,
				'address'=>$model->address,				
			];
		}
		throw new Exception( 'merchant not found' );
	}

	public static function getService($merchant_id=0)
	{
		$model = AR_merchant_meta::model()->find("merchant_id=:merchant_id AND meta_name=:meta_name",[
			':merchant_id'=>$merchant_id,
			':meta_name'=>'services'
		]);
		if($model){
			return $model->meta_value;
		}
		return 'delivery';
	}

	public static function validateService($merchant_id=0,$transaction_type='')
	{
		$model = AR_merchant_meta::model()->find("merchant_id=:merchant_id AND meta_name=:meta_name AND meta_value=:meta_value",[
			':merchant_id'=>$merchant_id,
			':meta_name'=>'services',
			':meta_value'=>$transaction_type
		]);
		if($model){			
			return $model->meta_value;
		}
		return false;
	}

	public static function updateChargeType($merchant_id=0,$data='')
	{		
		if($merchant_id<=0){
			$stmt = "UPDATE
			{{merchant}}
			SET charge_type=".q($data['admin_delivery_charges_type']).",			
			free_delivery_on_first_order=".q($data['admin_free_delivery_on_first_order'])."
			WHERE self_delivery=0
			";
		} else {
			$stmt = "UPDATE
			{{merchant}}
			SET charge_type=".q($data['admin_delivery_charges_type']).",			
			free_delivery_on_first_order=".q($data['admin_free_delivery_on_first_order'])."
			WHERE merchant_id=".q($merchant_id)."
			";
		}		
		try {
			Yii::app()->db->createCommand($stmt)->query();
			return true;
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}		
	}

	public static function getFixedCharge($merchant_id=0,$service_code='',$charge_type='',$shipping_type='')
	{
		$model = AR_shipping_rate::model()->find("merchant_id=:merchant_id AND service_code=:service_code AND charge_type=:charge_type AND shipping_type=:shipping_type ",[
			':merchant_id'=>$merchant_id,
			':service_code'=>$service_code,
			':charge_type'=>$charge_type,
			':shipping_type'=>$shipping_type,
		]);
		if($model){
			return [
				'fixed_price'=>$model->distance_price,
				'fixed_estimation'=>$model->estimation,
				'fixed_minimum_order'=>$model->minimum_order,
				'fixed_maximum_order'=>$model->maximum_order,
				'fixed_free_delivery_threshold'=>$model->fixed_free_delivery_threshold,
			];
		}
		throw new Exception(t(HELPER_RECORD_NOT_FOUND));
	}

	public static function getBasedistanceCharge($merchant_id=0,$service_code='',$charge_type='',$shipping_type='')
	{
		$model = AR_shipping_rate::model()->find("merchant_id=:merchant_id AND service_code=:service_code AND charge_type=:charge_type AND shipping_type=:shipping_type ",[
			':merchant_id'=>$merchant_id,
			':service_code'=>$service_code,
			':charge_type'=>$charge_type,
			':shipping_type'=>$shipping_type,
		]);
		if($model){
			return [
				'bd_base_distance'=>$model->distance_from,
				'bd_base_delivery_fee'=>Price_Formatter::convertToRaw($model->distance_price,2),
				'bd_price_extra_distance'=>$model->distance_to,
				'bd_delivery_radius'=>Price_Formatter::convertToRaw($model->delivery_radius,1)	,
				'bd_minimum_order'=>Price_Formatter::convertToRaw($model->minimum_order,2),
				'bd_maximum_order'=>Price_Formatter::convertToRaw($model->maximum_order,2),
				'bd_free_delivery_threshold'=>$model->fixed_free_delivery_threshold,
				'bd_cap_delivery_charge'=>$model->cap_delivery_charge,
				'bd_base_time_estimate'=>Price_Formatter::convertToRaw($model->estimation,0),
				'bd_base_time_estimate_additional'=>Price_Formatter::convertToRaw($model->time_per_additional,0),
			];
		}
		throw new Exception(t(HELPER_RECORD_NOT_FOUND));
	}	

	public static function getReviews($merchant_id=0)
	{
		$model = AR_view_ratings::model()->find("merchant_id=:merchant_id",[
			':merchant_id'=>$merchant_id
		]);
		if($model){
			return [
				'review_count'=>CommonUtility::formatShortNumber($model->review_count,0),
				'ratings'=>CommonUtility::formatShortNumber($model->ratings,1),
			];
		}
		return false;
	}

	public static function getPrinterAutoPrinterAll($merchant_ids=[],$printer_model=[],$platform='merchant')
	{
		$data = [];
		$criteria=new CDbCriteria();			
		$criteria->condition = "auto_print=:auto_print AND platform=:platform";
		$criteria->params  = array(			
        ':auto_print'=>1,        
		':platform'=>$platform
		);            
		$criteria->addInCondition('merchant_id',$merchant_ids);
		$criteria->addInCondition('printer_model',$printer_model);
				
		if($model = AR_printer::model()->findAll($criteria)){
			foreach ($model as $items) {				
				switch ($items->printer_model) {
					case 'wifi':
						$data[] = [
							'merchant_id'=>$items->merchant_id,
							'printer_id'=>$items->printer_id,
							'printer_name'=>$items->printer_name,
							'printer_model'=>$items->printer_model,
							'paper_width'=>$items->paper_width,
							'auto_print'=>$items->auto_print,
							'auto_close'=>$items->auto_close,
							'ip_address'=>$items->service_id,
							'port'=>$items->characteristics,
							'print_type'=>$items->print_type,
						];
						break;

					case 'bluetooth':
					case 'sunmi':      
						$data[] = [
							'merchant_id'=>$items->merchant_id,
							'printer_id'=>$items->printer_id,
							'printer_name'=>$items->printer_name,
							'printer_model'=>$items->printer_model,
							'paper_width'=>$items->paper_width,
							'auto_print'=>$items->auto_print,
							'auto_close'=>$items->auto_close,
							'printer_bt_name'=>$items->printer_bt_name,
							'device_id'=>$items->device_id,
							'service_id'=>$items->service_id,
							'characteristics'=>$items->characteristics,
							'print_type'=>$items->print_type,
							'printer_uuid'=>$items->printer_uuid
						];      
						break;
					case "feieyun":
						$meta = AR_printer_meta::getMeta($items->printer_id,['printer_user','printer_ukey','printer_sn','printer_key']);                    
                        $printer_user = isset($meta['printer_user'])?$meta['printer_user']['meta_value1']:'';
                        $printer_ukey = isset($meta['printer_ukey'])?$meta['printer_ukey']['meta_value1']:'';
                        $printer_sn = isset($meta['printer_sn'])?$meta['printer_sn']['meta_value1']:'';
                        $printer_key = isset($meta['printer_key'])?$meta['printer_key']['meta_value1']:'';
                        $data[] = [
							'merchant_id'=>$items->merchant_id,
                            'printer_id'=>$items->printer_id,
                            'printer_name'=>$items->printer_name,
                            'printer_model'=>$items->printer_model,
                            'paper_width'=>$items->paper_width,
                            'auto_print'=>$items->auto_print,
                            'printer_user'=>$printer_user,
                            'printer_ukey'=>$printer_ukey,
                            'printer_sn'=>$printer_sn,
                            'printer_key'=>$printer_key,
                        ];
						break;
				}
			}
			return $data;
		}		
		return false;
	}

	public static function getPrinterAutoPrinter($merchant_id=0,$printer_model=['sunmi','feieyun','bluetooth'],$platform='merchant')
	{
			
		$criteria=new CDbCriteria();		
		$criteria->condition = "merchant_id=:merchant_id AND platform=:platform AND auto_print=:auto_print";		    
		$criteria->params  = array(
		  ':merchant_id'=>intval($merchant_id),		  
		  ":platform"=>$platform,
		  ':auto_print'=>1
		);				
		$criteria->addInCondition("printer_model",$printer_model);			
		$model = AR_printer::model()->find($criteria); 		
		if($model){
			$data = [];
			$printer_user='';
			$printer_ukey='';
			$printer_sn='';
			$printer_key='';

			switch ($model->printer_model) {
				case 'wifi':
					$data = [
						'printer_id'=>$model->printer_id,
						'printer_name'=>$model->printer_name,
						'printer_model'=>$model->printer_model,
						'paper_width'=>$model->paper_width,
						'auto_print'=>$model->auto_print,
						'auto_close'=>$model->auto_close,
						'ip_address'=>$model->service_id,
						'port'=>$model->characteristics,
						'print_type'=>$model->print_type,
					];
					break;

				case 'bluetooth':
                case 'sunmi':            
					$data = [
						'printer_id'=>$model->printer_id,
						'printer_name'=>$model->printer_name,
						'printer_uuid'=>$model->printer_uuid,
						'printer_model'=>$model->printer_model,
						'paper_width'=>$model->paper_width,
						'auto_print'=>$model->auto_print,
						'auto_close'=>$model->auto_close,
						'printer_bt_name'=>$model->printer_bt_name,
						'device_id'=>$model->device_id,
						'service_id'=>$model->service_id,
						'characteristic'=>$model->characteristics,
						'print_type'=>$model->print_type,
					];
					break;

					case "feieyun":
                        $meta = AR_printer_meta::getMeta($model->printer_id,['printer_user','printer_ukey','printer_sn','printer_key']);                    
                        $printer_user = isset($meta['printer_user'])?$meta['printer_user']['meta_value1']:'';
                        $printer_ukey = isset($meta['printer_ukey'])?$meta['printer_ukey']['meta_value1']:'';
                        $printer_sn = isset($meta['printer_sn'])?$meta['printer_sn']['meta_value1']:'';
                        $printer_key = isset($meta['printer_key'])?$meta['printer_key']['meta_value1']:'';
                        $data = [
                            'printer_id'=>$model->printer_id,
                            'printer_name'=>$model->printer_name,
                            'printer_model'=>$model->printer_model,
                            'paper_width'=>$model->paper_width,
                            'auto_print'=>$model->auto_print,
                            'printer_user'=>$printer_user,
                            'printer_ukey'=>$printer_ukey,
                            'printer_sn'=>$printer_sn,
                            'printer_key'=>$printer_key,
                        ];
                        break;
			}
			return $data;
		} else throw new Exception( t("Printer not found") );
	}

}
/*end class*/