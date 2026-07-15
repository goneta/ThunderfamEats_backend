<?php
class CMerchantListingV1
{
	public static function getMerchant($merchant_id='')
	{
		$dependency = new CDbCacheDependency('SELECT MAX(date_modified) FROM {{merchant}}');
		$model = AR_merchant::model()->cache(Yii::app()->params->cache, $dependency)->find('merchant_id=:merchant_id', 
		array(':merchant_id'=>$merchant_id)); 
		if($model){
			return $model;
		}
		throw new Exception( 'merchant not found' );
	}

	public static function getMerchantBySlug($restaurant_slug='')
	{
		$dependency = CCacheData::dependency();
		$model = AR_merchant::model()->cache(Yii::app()->params->cache, $dependency)->find('restaurant_slug=:restaurant_slug', 
		array(':restaurant_slug'=>$restaurant_slug)); 
		if($model){
			return $model;
		}
		throw new Exception( 'merchant not found' );
	}
	
	public static function getMerchantInfo($slug_name='',$lang='')
	{
		$stmt = "
		SELECT a.merchant_id, a.merchant_uuid,
		a.restaurant_name,
		CASE 
		WHEN c.meta_value1 IS NOT NULL AND c.meta_value1 != '' THEN c.meta_value1
		ELSE a.description
		END AS description,

		CASE 
		WHEN d.meta_value1 IS NOT NULL AND d.meta_value1 != '' THEN d.meta_value1
		ELSE a.short_description
		END AS short_description,

		a.logo,a.path,	
		a.header_image,a.path2,	
		a.address,
		a.restaurant_slug, 
		a.latitude,
		a.lontitude,
		a.distance_unit,
		a.delivery_distance_covered,
		a.contact_phone,
		b.review_count,
		b.ratings,
		
		IFNULL((
		 select GROUP_CONCAT(DISTINCT cuisine_name SEPARATOR ';')
		 from {{view_cuisine}}
		 where language=".q($lang)."
		 and cuisine_id in (
		    select cuisine_id from {{cuisine_merchant}}
		    where merchant_id  = a.merchant_id
		 )		 
		),'') as cuisine_name
		
		FROM {{merchant}} a
		LEFT JOIN {{view_ratings}} b
		ON
		a.merchant_id = b.merchant_id

		left JOIN (
			SELECT merchant_id,meta_value1 FROM {{merchant_meta}} where meta_value=".q($lang)."
			and meta_name='merchant_about_trans'
		) c
		ON a.merchant_id = c.merchant_id

		left JOIN (
			SELECT merchant_id,meta_value1 FROM {{merchant_meta}} where meta_value=".q($lang)."
			and meta_name='merchant_short_about_trans'
		) d
		ON a.merchant_id = d.merchant_id
		
		WHERE restaurant_slug=".q($slug_name)."
		AND a.status='active' 
		LIMIT 0,1
		";				
		///AND a.status='active'  AND a.is_ready ='2' 
		if($res = CCacheData::queryRow($stmt) ){				
			$val2 = $res;						
			$cuisine = '';
			$cuisine_name = explode(";",$res['cuisine_name']);	
			if(is_array($cuisine_name) && count($cuisine_name)>=1){
				foreach ($cuisine_name as $name) {
					$cuisine.= "&#8226; $name ";
				}								
			}
			unset($val2['cuisine_name']);
			$val2['restaurant_name'] = Yii::app()->input->xssClean($res['restaurant_name']);				
			$val2['merchant_address'] = Yii::app()->input->xssClean($res['address']);				
			$val2['url']= Yii::app()->createAbsoluteUrl($val2['restaurant_slug']);
			$val2['cuisine'] = (array)$cuisine_name;
			$val2['cuisine2'] = $cuisine;			
			$val2['url_logo'] = CMedia::getImage($res['logo'],$res['path'],"@2x",
				CommonUtility::getPlaceholderPhoto('merchant_logo'));
			$val2['url_header'] = CMedia::getImage($res['header_image'],$res['path2'],"",
				CommonUtility::getPlaceholderPhoto('logo'));	
			$val2['has_header']	 = !empty($res['header_image'])?true:false;
			$val2['latitude'] = $res['latitude'];
			$val2['lontitude'] = $res['lontitude'];
			$val2['delivery_estimation']='';
			$val2['description'] = $res['description'];
			$val2['short_description'] = $res['short_description'];		
			$val2['review_words'] = t('{n} rating|{n} ratings',$res['review_count'] ?? 0);
			$val2['ratings'] =  $res['ratings'] ? CommonUtility::formatShortNumber($res['ratings']) : 0;
			$val2['delivery_distance_covered'] = CommonUtility::formatDistance($res['delivery_distance_covered']);
			return $val2;
		}
		throw new Exception( 'no results' );
	}

	public static function getGallery($merchant_id='')
	{		
		$criteria=new CDbCriteria;
		$criteria->condition = "merchant_id=:merchant_id AND meta_name=:meta_name";		    
		$criteria->params  = array(
		  ':merchant_id'=>intval($merchant_id),
		  ':meta_name'=>'merchant_gallery'
		);
		$criteria->order='meta_id ASC';
		$model = AR_merchant_meta::model()->findAll($criteria); 
		if($model){
			$data = array();
			foreach ($model as $val) {
				$data[] = array(
				  'thumbnail' =>CMedia::getImage($val['meta_value'],$val['meta_value1'],
				    Yii::app()->params->size_image_thumbnail,CommonUtility::getPlaceholderPhoto('gallery')),
				  'image_url' =>CMedia::getImage($val['meta_value'],$val['meta_value1'],
				    Yii::app()->params->size_image_medium,CommonUtility::getPlaceholderPhoto('gallery'))  
				);
			}
			return $data;
		}
		return false;
	}

	public static function openingHours($merchant_id='')
	{
		$stmt = "
		SELECT day,status,start_time,end_time,
		start_time_pm,end_time_pm,custom_text
		FROM {{opening_hours}}
		WHERE merchant_id=".q($merchant_id)."		
		AND status='open'	
		ORDER BY day_of_week ASC
		";				
		if($res = CCacheData::queryAll($stmt) ){	
			$data = []; $days = [];
			foreach ($res as $item) {										
				$item['value']=  in_array($item['day'],(array)$days)?'':t($item['day']);
				$days[] = $item['day'];				
				$item['start_time'] = Date_Formatter::Time($item['start_time']);
				$item['end_time'] = Date_Formatter::Time($item['end_time']);
				$item['start_time_pm'] = Date_Formatter::Time($item['start_time_pm']);
				$item['end_time_pm'] = Date_Formatter::Time($item['end_time_pm']);
				$data[]	= $item;
			}			
			return $data;
		}
		return false;
	}
	
	public static function staticMapLocation($maps_credentials=array(),
	   $lat='', $lng='',$size='500x300',$icon='',$zoom=13,$scale=2,$format='png8')
	{
		$link = '';		
		if($maps_credentials){
			$api_keys = $maps_credentials['api_keys'];
			if($maps_credentials['map_provider']=="google.maps"){
				$link = "https://maps.googleapis.com/maps/api/staticmap";
				$link.= "?".http_build_query(array(
				  'center'=>"$lat,$lng",
				  'size'=>$size,
				  'zoom'=>$zoom,
				  'scale'=>$scale,
				  'format'=>$format,
				  'markers'=>"icon:$icon|$lat,$lng",
				  'key'=>$api_keys,				  
				));
			} else if ( $maps_credentials['map_provider']=="mapbox"  ) {
				$link = "https://api.mapbox.com/styles/v1/mapbox/streets-v12/static";
				$link.="/pin-s-l+000";
				$link.="($lng,$lat)/$lng,$lat,14/$size";
				$link.= "?".http_build_query(array(				
					'access_token'=>$api_keys,				  
				));
			}			
			return $link;
		}
		return false;
	}
	
    public static function mapDirection($maps_credentials=array(),$lat='', $lng='')
	{
		$link = '';
		if($maps_credentials){
			// if($maps_credentials['map_provider']=="google.maps"){
			// 	$link = "https://www.google.com/maps/dir/?api=1&destination=$lat,$lng";
			// } else if ( $maps_credentials['map_provider']=="mapbox"  ) {
			// 	$link = "https://www.google.com/maps/dir/?api=1&destination=$lat,$lng";
			// }
			$link = "https://www.google.com/maps/dir/?api=1&destination=$lat,$lng";
			return $link;
		}
		return false;
	}

	public static function openHours($merchant_id='', $interval="20 mins",$time_config_type='regular_hours')
	{
		if (!preg_match('/\d/', $interval)) {
			$interval="20 mins";
		}
		$today = date('Y-m-d'); $order_by_days = ''; $daylist = array();
		$yesterday = date('Y-m-d', strtotime($today. " -1 days"));	
		$tomorrow = date('Y-m-d', strtotime($today. " +1 days"));		
		$current_time = date("Hi");
		$time_now = date("H:i",strtotime("+".intval($interval)." minutes"));
		$day_of_week = date("N");		

		for($i=1; $i<=7; $i++){			
			$days = date('l', strtotime($yesterday. " +$i days"));			
			$days = strtolower($days);	
			$order_by_days.=q($days).",";	
			$daylist[$days]= date('Y-m-d', strtotime($yesterday. " +$i days"));	 
		}
						
		$order_by_days = substr($order_by_days,0,-1);
		$stmt="
		SELECT day,start_time,end_time
		FROM {{opening_hours}}
		WHERE merchant_id=".q($merchant_id)."
		AND status='open'			
		AND time_config_type=".q($time_config_type)."
		ORDER BY FIELD(day, $order_by_days),start_time ASC;	
		";								
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){			
			$data = array(); $times = array();
			foreach ($res as $val) {	
								
				$start_time = date("Hi",strtotime($val['start_time']));		
				$item_start_time = $val['start_time'];				
				
				$date = isset($daylist[$val['day']])?$daylist[$val['day']]:'';	
				if(empty($date)){
					continue;
				}
							
				$name = Date_Formatter::date($date,"eee dd MMM",true);
							
				if($today==$date){
					$name = t("Today").", $name";
					if($current_time>$start_time){								
						$item_start_time = self::blockMinutesRound($time_now, intval($interval) ); 						
					}
				} elseif ($tomorrow==$date){
					$name = t("Tomorrow").", $name";
					//$item_start_time = date("H:i",strtotime($item_start_time." +".intval($interval)." minutes"));
				} else {
					//$item_start_time = date("H:i",strtotime($item_start_time." +".intval($interval)." minutes"));
				}				

				$end_time = $val['end_time'];						
				$end_time = date("H:i",strtotime($end_time." -".intval($interval)." minutes"));
												
				$time = self::createTimeRange($item_start_time,$end_time,$interval);		
				if(array_key_exists($date,(array)$times)){					
					if(isset($times[$date][0])){
						$lastIndex = count($times[$date][0]);					
						foreach ($time as $key => $value) {
							$times[$date][0][$lastIndex + $key] = $value;
						}
					}					
				} else $times[$date][]=$time;				
								
				if(is_array($time) && count($time)>=1){
				$data[$date] = array(
				  'name'=>$name,
				  'value'=>$date,				  
				  'data'=>$val,				  
				);
				}
			} //endfor	

			//die();
						
			$_times = array();
			if(is_array($times) && count($times)>=1){
				foreach ($times as $key=>$item) {				
					$merge = array();
					for ($x = 0; $x <= count($item)-1; $x++) {				
						$merge += $item[$x];
					}
					$_times[$key] = $merge;
				}			
			}
			
			return array(
			  'dates'=>$data,
			  'time_ranges'=>$_times
			);
		}
		return false;
	}
	
	 public static function createTimeRange($start, $end, $interval = '30 mins', $format = '24') {
	    $startTime = strtotime($start); 
	    $startEnd = strtotime($start); 
	    $endTime   = strtotime($end);
	    $returnTimeFormat = ($format == '12')?'g:i:s A':'G:i:s';	    
	
	    $current   = time(); 
	    $addTime   = strtotime('+'.$interval, $current); 
	    $diff      = $addTime - $current;
	
	    $times = array(); 	    
	    while ($startTime < $endTime) { 	 
	    	$start_time =  date("H:i", $startTime);   		    		    		    	
	    	$startEnd  += $diff; 
	    	$start_end =  date("H:i", $startEnd);  
	        	        
	        $pretty_time = Date_Formatter::Time($startTime,"hh:mm a") . " - " . Date_Formatter::Time($startEnd,"hh:mm a"); 
	        
	        $times[] = array(
	          'start_time'=>date($returnTimeFormat, $startTime),
	          'end_time'=>date($returnTimeFormat, $startEnd),
	          'pretty_time'=>$pretty_time
	        );
	        $startTime += $diff; 
	    } 
	    
	    $start_time =  date("H:i", $startTime);  	       
	    return $times; 
	}    
	
	public static function blockMinutesRound($hour, $minutes = '5', $format = "H:i") {
	   $seconds = strtotime($hour);
	   $minutes=$minutes<=0?5:$minutes;
	   $rounded = round($seconds / ($minutes * 60)) * ($minutes * 60);
	   return date($format, $rounded);
	}

	//https://ourcodeworld.com/articles/read/756/how-to-round-up-down-to-nearest-10-or-5-minutes-of-datetime-in-php
	public static function roundToNearestMinuteInterval(\DateTime $dateTime, $minuteInterval = 10)
	{
	    return $dateTime->setTime(
	        $dateTime->format('H'),
	        round($dateTime->format('i') / $minuteInterval) * $minuteInterval,
	        0
	    );
	}	
	
	public static function getDistanceExp($filter=array())
	{
	    $distance_exp=3959;
		if ($filter['unit']=="km"){
			$distance_exp=6371;
		}
		return $distance_exp;			
	}
	
	public static function preFilter($filter=array())
	{				
		$and = '';				

		if(isset($filter['filters'])){
			if(!is_array($filter['filters']) && count((array)$filter['filters'])<=1){
				return $and;
			}
		}

		$current_date = date("Y-m-d H:i");
		$date_now = isset($filter['date_now'])? (!empty($filter['date_now'])?$filter['date_now']:$current_date) :$current_date;
		
		if(isset($filter['filters'])){
			foreach ($filter['filters'] as $filter_by=>$val) {				

				switch ($filter_by) {

					case "query":												
						//$and .= "AND a.restaurant_name LIKE ".q("%$val%")."";
						$and .= " 
						AND (
						    a.restaurant_name LIKE ".q("%$val%")."
							OR EXISTS ( 
							select 1 from {{item}}
							where 
							merchant_id = a.merchant_id
							and item_name LIKE ".q("%$val%")."
							and available = 1
							and status='publish'
							)
						)
						";
						break;

					case "offers_filters":
						$offers_filters = $val;
						if(is_array($offers_filters) && count($offers_filters)>=1){
							$today = strtolower(date("l",strtotime($date_now)));  	
                            $dateNow = strtolower(date("Y-m-d",strtotime($date_now)));									
							$required = ['accept_deals', 'accept_vouchers'];
							$voucher_condition = "
							AND EXISTS (
								SELECT 1 from {{voucher_new}}            
								where
								merchant_id = a.merchant_id
								AND expiration >= ".q($dateNow)."   
								AND status in ('publish','published')             
								AND ".$today."=1
								AND used_once <> 6
								AND visible=1
							)
							";
							$offer_condition = "
							EXISTS (
								SELECT 1  from {{offers}}
								where merchant_id = a.merchant_id
								and status = 'publish'
								and ".q($dateNow)." >= valid_from and ".q($dateNow)." <= valid_to
							)
							";
							if (count(array_intersect($required, (array)$offers_filters)) === count($required)) {
								$and .= $voucher_condition . " OR " . $offer_condition;
							} else {
								if(in_array('accept_vouchers',(array)$offers_filters)){	
									$and .= $voucher_condition;
								} else if ( in_array('accept_deals',(array)$offers_filters)) {
									$and .= "AND $offer_condition";
								}
							}						
						}
						break;
					
					case "transaction_type":
						    if(!empty($val)){
								$and.="\n\n";
								$and.= "
								AND a.merchant_id IN (
								select merchant_id from {{merchant_meta}}
								where merchant_id = a.merchant_id
								and meta_name='services' 
								and meta_value=".q($val)."
								)
								";
							}
						break;
						
					case "sortby":
						if($val=="sort_most_popular"){
							$and.="\n\n";
							$and.= "
							AND a.merchant_id IN (
							 select merchant_id from {{merchant_meta}}
							 where merchant_id = a.merchant_id
							 and meta_name='featured' 
							 and meta_value='popular'
							)
							";
						} elseif ($val=="sort_rating"){
							$and.="\n\n";
							$and.= "
							AND a.merchant_id IN (
							  select merchant_id from {{review}}
							  where merchant_id = a.merchant_id
							  and status = 'publish'
							)
							";
						} elseif ( $val=="sort_promo"){	
							$date_now = isset($filter['date_now'])?$filter['date_now']:'';
							$today = strtolower(date("l",strtotime($date_now))); 
							$and.="\n\n";
							$and.= "
							AND a.merchant_id IN (
							  select merchant_id from {{promos}}
							  where merchant_id = a.merchant_id
							  and status = 'publish'
							  and ".q($date_now)." >= valid_from and ".q($date_now)." <= valid_to
							  AND ".$today."=1
							)
							";							
						} elseif ($val=="sort_free_delivery"){
							$and.="\n\n";
							$and.="
							AND a.merchant_id IN (
							  select merchant_id from {{option}}
							  where merchant_id = a.merchant_id
							  and option_name='free_delivery_on_first_order'
							  and option_value=1
							)
							";
						}
						break;
				
					case "price_range":				
					     if(!empty($val)){
							 $based_price = str_pad(9, intval($val) ,9);	
							 $and.="\n\n";
							 $and.=" AND a.merchant_id IN (
							  select merchant_id from {{item_relationship_size}}
							  where price <=".q($based_price)."
							  and available = 1
							 )
						    ";
					     }
						break;
					
					case "cuisine":		
					    if(is_array($val) && count($val)>=1){
					    	$in = '';
					    	foreach ($val as $cuisine_id) {
					    		$in.=q(intval($cuisine_id)).",";
					    	}
					    	$in = substr($in,0,-1);
					    	if(!empty($in)){
								$and.="\n\n";
								$and.=" AND a.merchant_id IN (
								 select merchant_id from {{cuisine_merchant}}
								 where merchant_id = a.merchant_id				 
								 and cuisine_id IN ($in)
							   )";		 
						   }
					    }
					    break;
							
					case "max_delivery_fee":    
					    $max_delivery_fee = floatval($val);
					    if($max_delivery_fee>0){
					    	$and.="\n\n";
					    	$and.="
					    	AND a.merchant_id IN (
					    	  select merchant_id
					    	  from {{shipping_rate}}
					    	  where distance_price between 1 and ".q($max_delivery_fee)."
					    	  and service_code='delivery'
					    	  and charge_type  = (
					    	    select option_value  
					    	    from {{option}}	
					    	    where merchant_id = a.merchant_id
					    	    and option_name='merchant_delivery_charges_type'					    	    
					    	  )
					    	)
					    	";
					    }
					    break;
					    
					case "rating":    
					    $rating = intval($val);					    
					    if($rating>0){
					    	$and.="\n\n";
							$and.= "
							AND a.merchant_id IN (
							  select merchant_id from {{view_ratings}}
							  where merchant_id = a.merchant_id
							  and ratings=".q($rating)."
							)
							";							
							//and ratings>=".q($rating)."
					    }					    
					    break;
					    
					default:
						break;
				}
			}
		}		
		return $and;
	}
	
	public static function getLocalID($local_id='')
	{		
		if(!empty($local_id)){
			$dependency = new CDbCacheDependency('SELECT MAX(date_modified) FROM {{map_places}}');
			$model = AR_map_places::model()->cache( Yii::app()->params->cache , $dependency)->find("reference_id=:reference_id",array(
			  ':reference_id'=>$local_id		  
			));	
			/*$model = AR_map_places::model()->find("reference_id=:reference_id",array(
			  ':reference_id'=>$local_id		  
			));	*/
			if($model){
				return $model;
			}
		} else throw new Exception( 'Place id is empty' );
		throw new Exception( 'Place id not found' );
	}
	
	public static function preSearch($filter=array(), $filter_location=true)
	{
		
		if(!is_array($filter) && count($filter)<=0){
			throw new Exception( 'Invalid filter' );
		}
		
		if($filter_location){
			if(empty($filter['lat']) || empty($filter['lng']) ){
				throw new Exception( 'Invalid coordinates' );
			}
			if(empty($filter['unit'])){
				throw new Exception( 'Invalid distance unit' );
			}
		}		
		
		$distance_exp = self::getDistanceExp($filter);
		$and = self::preFilter($filter);

		$and_distance_filter = '';
		if($filter_location){
			// Check transaction type for pickup (use larger radius)
			$transaction_type = isset($filter['filters']['transaction_type']) ? $filter['filters']['transaction_type'] : '';
			
			if($transaction_type == "pickup"){
				$and_distance_filter = "
				 AND 
				 CASE 
				 WHEN a.distance_unit = 'mi' THEN
					(3959 * ACOS(SIN(RADIANS(a.latitude)) * SIN(RADIANS($filter[lat])) + COS(RADIANS(a.latitude)) * COS(RADIANS($filter[lat])) * COS(RADIANS(a.lontitude - $filter[lng]))))
				 WHEN a.distance_unit = 'km' THEN
					(6371 * ACOS(SIN(RADIANS(a.latitude)) * SIN(RADIANS($filter[lat])) + COS(RADIANS(a.latitude)) * COS(RADIANS($filter[lat])) * COS(RADIANS($filter[lng] - a.lontitude))))
				 END < (a.delivery_distance_covered * 10)
				";
			} else {
				// For delivery: use polygon if available, else use radius
				$and_distance_filter = "
				AND 
				(
					(
						a.delivery_zone_covered IS NOT NULL 
						AND a.delivery_zone_covered != '' 
						AND a.delivery_zone_type = 'POLYGON'
						AND ST_Within(
							ST_PointFromText(CONCAT('POINT(', ".q($filter['lng']).", ' ', ".q($filter['lat']).", ')')), 
							ST_PolyFromText(CONCAT('POLYGON', a.delivery_zone_covered))
						) = 1
					) OR (
						(a.delivery_zone_covered IS NULL OR a.delivery_zone_covered = '' OR a.delivery_zone_type != 'POLYGON' OR a.delivery_zone_type IS NULL) 
						AND (
							$distance_exp * acos( 
								cos( radians($filter[lat]) ) * cos( radians( a.latitude ) ) 
								* cos( radians( a.lontitude ) - radians($filter[lng]) ) 
								+ sin( radians($filter[lat]) ) * sin( radians( a.latitude ) ) 
							)  
						) <= a.delivery_distance_covered
					)
				)
			   ";
			}
		}		
					
		$stmt="
		SELECT count(*) as total		
		FROM {{merchant}} a 					
		WHERE a.status='active' AND a.is_ready ='2' 		
		$and_distance_filter
		$and
		";
		
		
		if($res = Yii::app()->db->createCommand($stmt)->queryRow()){
			$count = isset($res['total']) ? $res['total'] : 0;
			return $count;
		}
		throw new Exception( 'no results' );
	}

	/*
	@parameters
	
	'filters':{
			   	  'cuisine': this.cuisine,
			   	  'sortby' : this.sortby,
			   	  'price_range': this.price_range,
			   	  'max_delivery_fee': this.max_delivery_fee,
			   },	
			   
	$filter = array(
		    'lat'=>$local_info->latitude,
		    'lng'=>$local_info->longitude,
		    'unit'=>Yii::app()->params['settings']['home_search_unit_type'],
		    'limit'=>intval(Yii::app()->params->list_limit),
		    'today_now'=>strtolower(date("l")),
		    'time_now'=>date("H:i"),
		    'date_now'=>date("Y-m-d"),
		    'filters'=>$filters,
		  );
	*/
	public static function Search($filter=array(), $lang = KMRS_DEFAULT_LANGUAGE , $filter_location = true )
	{
		
		if(!is_array($filter) && count($filter)<=0){
			throw new Exception( 'Invalid filter' );
		}
		
		if($filter_location){
			if(empty($filter['lat']) || empty($filter['lng']) ){
				throw new Exception( 'Invalid coordinates' );
			}
			if(empty($filter['unit'])){
				throw new Exception( 'Invalid distance unit' );
			}
		}		
		if(empty($filter['limit'])){
			throw new Exception( 'Invalid limit' );
		}
		
		$distance_exp = self::getDistanceExp($filter);
		$and = self::preFilter($filter);

		$unit = isset($filter['unit'])?$filter['unit']:'';		
		$unit = !empty($unit)? MapSdk::prettyUnit($unit) : $unit;		
		
		$query_distance = ''; $having_condition = ''; $sort_distance = ''; $where_distance_filter = '';
		$today_day_of_week = date('N');
		$currentDate = new DateTime();  

		if($filter_location){
			// Check transaction type for pickup (use larger radius)
			$transaction_type = isset($filter['filters']['transaction_type']) ? $filter['filters']['transaction_type'] : '';
			
			$query_distance = ",
			CASE 
			WHEN a.distance_unit = 'mi' THEN
			  (3959 * ACOS(SIN(RADIANS(a.latitude)) * SIN(RADIANS($filter[lat])) + COS(RADIANS(a.latitude)) * COS(RADIANS($filter[lat])) * COS(RADIANS(a.lontitude - $filter[lng]))))
			WHEN a.distance_unit = 'km' THEN
			  (6371 * ACOS(SIN(RADIANS(a.latitude)) * SIN(RADIANS($filter[lat])) + COS(RADIANS(a.latitude)) * COS(RADIANS($filter[lat])) * COS(RADIANS($filter[lng] - a.lontitude))))
		    END AS distance
			";
			
			if($transaction_type == "pickup"){
				// For pickup, use larger radius (10x)
				$where_distance_filter = "
				AND 
				CASE 
				WHEN a.distance_unit = 'mi' THEN
				  (3959 * ACOS(SIN(RADIANS(a.latitude)) * SIN(RADIANS($filter[lat])) + COS(RADIANS(a.latitude)) * COS(RADIANS($filter[lat])) * COS(RADIANS(a.lontitude - $filter[lng]))))
				WHEN a.distance_unit = 'km' THEN
				  (6371 * ACOS(SIN(RADIANS(a.latitude)) * SIN(RADIANS($filter[lat])) + COS(RADIANS(a.latitude)) * COS(RADIANS($filter[lat])) * COS(RADIANS($filter[lng] - a.lontitude))))
				END < (a.delivery_distance_covered * 10)
				";
				$having_condition = "";
				$sort_distance = ', distance ASC';
			} else {
				// For delivery: use polygon if available, else use radius
				// Wrap ST_Within in a way that handles errors gracefully
				$where_distance_filter = "
				AND (
					(
						a.delivery_zone_covered IS NOT NULL 
						AND a.delivery_zone_covered != '' 
						AND a.delivery_zone_type = 'POLYGON'
						AND ST_Within(
							ST_PointFromText(CONCAT('POINT(', ".q($filter['lng']).", ' ', ".q($filter['lat']).", ')')), 
							ST_PolyFromText(CONCAT('POLYGON', a.delivery_zone_covered))
						) = 1
					) OR (
						(a.delivery_zone_covered IS NULL OR a.delivery_zone_covered = '' OR a.delivery_zone_type != 'POLYGON' OR a.delivery_zone_type IS NULL) 
						AND (
							$distance_exp * acos( 
								cos( radians($filter[lat]) ) * cos( radians( a.latitude ) ) 
								* cos( radians( a.lontitude ) - radians($filter[lng]) ) 
								+ sin( radians($filter[lat]) ) * sin( radians( a.latitude ) ) 
							)  
						) <= a.delivery_distance_covered
					)
				)
				";
				$having_condition = "";
				$sort_distance = ', distance ASC';
			}
		} else {
			// When filter_location is false, no distance filtering needed
			$having_condition = "";
			$sort_distance = '';
		}
		
		$stmt="
		SELECT 
		a.merchant_id,
		a.restaurant_name,
		a.restaurant_slug,
		a.logo,
		a.delivery_distance_covered,
		a.distance_unit	,
		a.status,a.is_ready,
		a.close_store,
		a.disabled_ordering,
		a.pause_ordering,		
		a.path,
		a.header_image,a.path2,
		a.latitude,a.lontitude,
		
		IFNULL((
		 select GROUP_CONCAT(cuisine_name,';',color_hex,';',font_color_hex)
		 from {{view_cuisine}}
		 where language=".q($lang)."
		 and cuisine_id in (
		    select cuisine_id from {{cuisine_merchant}}
		    where merchant_id  = a.merchant_id
		 )		 		 
		),'') as cuisine_name,
		
		(
		select concat(review_count,';',ratings) as ratings from {{view_ratings}}
		where merchant_id = a.merchant_id
		) as ratings,

		(
		select option_value
		from {{option}}
		where option_name='merchant_delivery_charges_type'
		and merchant_id = a.merchant_id
		) as charge_type,
		
		(
		select option_value
		from {{option}}
		where option_name='free_delivery_on_first_order'
		and merchant_id = a.merchant_id
		) as free_delivery,

		(
		select option_value
		from {{option}}
		where option_name='merchant_extenal'
		and merchant_id = a.merchant_id
		) as merchant_website,
		
		(
		select COUNT(DISTINCT(merchant_id))
		from {{favorites}}
		where merchant_id = a.merchant_id
		and client_id=".q($filter['client_id'])."
		and fav_type='restaurant'
		) as saved_store,
		
		(
		select GROUP_CONCAT(day_of_week,';',start_time,';',end_time order by day_of_week asc)
		from {{opening_hours}}
		where merchant_id = a.merchant_id
		and day_of_week>=".q(intval($filter['day_of_week']))."
		and CAST(".q($filter['time_now'])." AS TIME) < CAST(end_time AS TIME)
		and status='open'		
		) as next_opening
		
		$query_distance	
		
		,IFNULL((
			select count(*) from
			{{opening_hours}}
			where
			merchant_id = a.merchant_id
			and
			day=".q($filter['today_now'])."
			and
			status = 'open'
			and 
			
			(
			CAST(".q($filter['time_now'])." AS TIME)
			BETWEEN CAST(start_time AS TIME) and CAST(end_time AS TIME)					
			
			)
			
		), 0) as merchant_open_status,

		IFNULL((
		   select count(*) from {{holidays}}
		   where 
		   merchant_id = a.merchant_id
		   and
		   holiday_date=CURDATE()
		), 0) as holiday_status,

		holiday.reason as holiday_reason,

		CASE         
		WHEN oh.status = 'open' 
		AND (
			(TIME(NOW()) BETWEEN STR_TO_DATE(oh.start_time, '%H:%i') 
								AND STR_TO_DATE(oh.end_time, '%H:%i'))
			OR 
			(TIME(NOW()) BETWEEN STR_TO_DATE(oh.start_time_pm, '%H:%i') 
								AND STR_TO_DATE(oh.end_time_pm, '%H:%i'))
		) 
		THEN 'open'
		ELSE 'closed'
		END AS current_status,

		CASE 
		WHEN oh.status = 'open' 
		AND STR_TO_DATE(oh.start_time, '%H:%i') > TIME(NOW()) 
		THEN CONCAT(oh.start_time,';',oh.end_time,';',oh.day_of_week,';',oh.day)

		ELSE (
		SELECT CONCAT(next_oh.start_time,';',next_oh.end_time,';',next_oh.day_of_week,';',next_oh.day)
		FROM {{opening_hours}} next_oh
		WHERE next_oh.merchant_id = a.merchant_id
			AND next_oh.status = 'open'
			AND (
				next_oh.day_of_week > DAYOFWEEK(NOW()) - 1  -- Find next day
				OR (next_oh.day_of_week = 0 AND DAYOFWEEK(NOW()) != 1)  -- Handle Sunday properly
				)
		ORDER BY next_oh.day_of_week ASC 
		LIMIT 1
		) 
		END AS next_opening_hours        
		
		FROM {{merchant}} a		

		left JOIN (
		   SELECT merchant_id,reason from {{holidays}}
		   where holiday_date=CURDATE()
		) holiday 
		on a.merchant_id = holiday.merchant_id

		LEFT JOIN {{opening_hours}} oh 
        ON a.merchant_id = oh.merchant_id 
        AND oh.day_of_week = DAYOFWEEK(NOW()) - 1

		WHERE 1=1
		$where_distance_filter
		AND a.status='active'  AND a.is_ready ='2'		
		$and
		$having_condition
		ORDER BY close_store,disabled_ordering,pause_ordering ASC, merchant_open_status+0 DESC,holiday_status+0 ASC, is_sponsored DESC $sort_distance		
		LIMIT $filter[offset],$filter[limit]
		";									
		CommonUtility::mysqlSetTimezone();
		
		// Log query for debugging
		
		try {
			$res = CCacheData::queryAll($stmt);
		} catch (Exception $e) {
			// If query fails (e.g., spatial functions not available), log and return empty
			throw new Exception('Merchant search failed: ' . $e->getMessage());
		}
		
		$data = array(); // Initialize data array
		if($res && is_array($res) && count($res) > 0){
			foreach ($res as $val) {
				// Skip null or invalid merchant records
				if(empty($val) || !isset($val['merchant_id']) || empty($val['merchant_id'])){
					continue;
				}
				
				$val2 = $val;
				// Ensure merchant_open_status has a default value
				if(!isset($val2['merchant_open_status']) || $val2['merchant_open_status'] === null){
					$val2['merchant_open_status'] = 0;
				}	
				$cuisine_list = array();
				$cuisine_name = explode(",",$val['cuisine_name']);
				$cuisines = '';
				if(is_array($cuisine_name) && count($cuisine_name)>=1){
					foreach ($cuisine_name as $cuisine_val) {						
						$cuisine = explode(";",$cuisine_val);								
						$cuisine_list[]=array(
						  'cuisine_name'=>isset($cuisine[0])?Yii::app()->input->xssClean($cuisine[0]):'',
						  'bgcolor'=>isset($cuisine[1])?  (!empty($cuisine[1])?$cuisine[1]:'#ffd966')  :'#ffd966',
						  'fncolor'=>isset($cuisine[2])? (!empty($cuisine[2])?$cuisine[2]:'#ffd966') :'#000',
						);						
						$cuisineName = CommonUtility::safeDecode($cuisine[0]) ?? '';
						$cuisines.="$cuisineName, ";
					}
				}
				
				$ratings = array();
				if(!empty($val['ratings'])){
					if($rate = explode(";",$val['ratings'])){
						$ratings = array(
							'review_count'=>$rate[0] ?? 0,							
							'rating'=>isset($rate[1])?Price_Formatter::convertToRaw($rate[1],1):0,
						);
					}			
			    }
				
				
				/*next_opening*/	
				$next_opening = '';			
				if(isset($val['current_status'])){
					if($val['current_status']=="closed"){
						$currentDate = new DateTime();  
						$next_opening_hours = !empty($val['next_opening_hours'])?explode(';',$val['next_opening_hours']):null;					
						if(!empty($next_opening_hours)){
							$start_time = isset($next_opening_hours[0])?$next_opening_hours[0]:'';
							$end_time = isset($next_opening_hours[1])?$next_opening_hours[1]:'';
							$day_of_week = isset($next_opening_hours[2])?$next_opening_hours[2]:'';	
													
							$daysToAdd = ($day_of_week > $today_day_of_week) 
							? ($day_of_week - $today_day_of_week) 
							: (7 - $today_day_of_week + $day_of_week);
							$openingDate = Date_Formatter::date($currentDate->modify("+$daysToAdd days")->format('Y-m-d'),"E",true);

							// $startTime = DateTime::createFromFormat('H:i', $start_time)->format('c');           
							// $endTime = DateTime::createFromFormat('H:i', $end_time)->format('c');
							// $startTime = Date_Formatter::Time($startTime);
						    // $endTime = Date_Formatter::Time($endTime);
							// $next_opening = t("Opens [day] at [time]",[                            
							// 	'[day]'=>$openingDate,
							// 	'[time]'=>"$startTime - $endTime"
							// ]);                        

							$startDt = DateTime::createFromFormat('H:i', $start_time);
							if ($startDt !== false) {
								$startTime = $startDt->format('h:i A');
							} 
							$endDt = DateTime::createFromFormat('H:i', $end_time);
							if ($endDt !== false) {
								$endTime = $endDt->format('h:i A');
							} 

							if (!empty($startTime) && !empty($endTime)) {
								$next_opening = t("Opens [day] at [time]", [                            
									'[day]' => $openingDate,
									'[time]' => "$startTime - $endTime"
								]);
							}

						}										
					}          
			    }        	
				
			    
				$val2['restaurant_name'] = Yii::app()->input->xssClean($val2['restaurant_name']);
				$val2['cuisine_name'] = (array)$cuisine_list;
				$val2['cuisines'] = !empty($cuisines)?substr($cuisines,0,-2):'';
				$val2['ratings'] = $ratings;
				$val2['merchant_url']= Yii::app()->createAbsoluteUrl($val2['restaurant_slug']);				
				$val2['url_logo']= CMedia::getImage($val2['logo'],$val2['path'],Yii::app()->params->size_image_medium,
				CommonUtility::getPlaceholderPhoto('merchant_logo'));

				$val2['url_header']= CMedia::getImage($val2['header_image'],$val2['path2'],Yii::app()->params->size_image_medium,
				CommonUtility::getPlaceholderPhoto('item'));

				$val2['next_opening'] = $next_opening;			

				if(isset($val2['distance'])){
					$distance = Price_Formatter::convertToRaw($val2['distance'],2);										
				    $val2['distance'] = $distance;				    
					$val2['distance_pretty'] = t("{{distance} {{unit}}",[
						'{{distance}'=>$distance,
						'{{unit}}'=>MapSdk::prettyUnit($val['distance_unit'])
					]);
				} else {
					$val2['distance'] = '';
					$val2['distance_pretty'] = '';
				}
				
				// Ensure all required fields have default values
				if(!isset($val2['merchant_open_status'])){
					$val2['merchant_open_status'] = 0;
				}
				if(!isset($val2['close_store'])){
					$val2['close_store'] = 0;
				}
				if(!isset($val2['disabled_ordering'])){
					$val2['disabled_ordering'] = 0;
				}
				if(!isset($val2['pause_ordering'])){
					$val2['pause_ordering'] = 0;
				}
				
				$val2['merchant_website'] = CommonUtility::correctUrl($val['merchant_website']);
				$val2['map_direction']= CMerchantListingV1::mapDirection(Yii::app()->params['map_credentials'],$val['latitude'],$val['lontitude']);
				$data[] = $val2;
			}
			return $data;
		} else {
			// Return empty array instead of throwing exception when query succeeds but returns no results
			return array();
		}		
	}
	
	public static function getDayWeek($date='',$day=0)
	{
		$days = array('Sunday', 'Monday', 'Tuesday', 'Wednesday','Thursday','Friday', 'Saturday');
		if(isset($days[$day])){
		   return date('Y-m-d', strtotime($days[$day], strtotime($date)));
		}
	}
	
    public static function services($filter='' , $filter_location = true)
	{
		$distance_exp = self::getDistanceExp($filter);

		$distance_query = "";
		if($filter_location){
			$distance_query = "
			AND a.delivery_distance_covered > (
				( $distance_exp * acos( cos( radians($filter[lat]) ) * cos( radians( latitude ) ) 
				 * cos( radians( lontitude ) - radians($filter[lng]) ) 
				+ sin( radians($filter[lat]) ) * sin( radians( latitude ) ) ) ) 
			)
			";
		}
		
		$data = array();
		$stmt="
		SELECT a.meta_value as service_name,
		a.merchant_id
		
		FROM {{merchant_meta}} a
		WHERE 
		a.merchant_id IN (
		    SELECT merchant_id
			FROM {{merchant}} a 					
			WHERE a.status='active' AND a.is_ready ='2' 		
			$distance_query			
		)
		AND meta_name ='services'
		";				
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){						
			foreach ($res as $val) {
				$data[$val['merchant_id']][] = $val['service_name'];
			}
			return $data;
		}
		return false;
	}
	
    public static function estimation($filter=array() , $filter_location = true)
	{
		$distance_exp = self::getDistanceExp($filter);		
		$distance_query = "";
		if($filter_location){
			$distance_query = "
			AND a.delivery_distance_covered > (
				( $distance_exp * acos( cos( radians($filter[lat]) ) * cos( radians( latitude ) ) 
				 * cos( radians( lontitude ) - radians($filter[lng]) ) 
				+ sin( radians($filter[lat]) ) * sin( radians( latitude ) ) ) ) 
			)
			";
		}
		
	    $data = array();
		$stmt="
		SELECT merchant_id,service_code,charge_type,distance_price,
		estimation,shipping_type
		FROM {{shipping_rate}} a
		WHERE
		shipping_type='standard'
		AND merchant_id  IN (
		    SELECT merchant_id
			FROM {{merchant}} a 					
			WHERE a.status='active' AND a.is_ready ='2'		
			$distance_query	
		)
		";						
		$dependency = CCacheData::dependency();	
		if($res = Yii::app()->db->cache(Yii::app()->params->cache,$dependency)->createCommand($stmt)->queryAll()){			
			foreach ($res as $val) {
				$data[$val['merchant_id']][$val['service_code']][$val['charge_type']] = array(
				  'service_code'=>$val['service_code'],
				  'charge_type'=>$val['charge_type'],
				  'estimation'=>$val['estimation'],
				  'shipping_type'=>$val['shipping_type'],
				  'fee'=>$val['distance_price']
				);
			}
			return $data;
		}
		return false;
	}		
	
	public static function estimationMerchant($filter=array())
	{
		$distance_exp = self::getDistanceExp($filter);
		
	    $data = array();
		$stmt="
		SELECT merchant_id,service_code,charge_type,
		estimation,shipping_type
		FROM {{shipping_rate}} a
		WHERE
		shipping_type=".q($filter['shipping_type'])."
		AND merchant_id  IN (
		    SELECT merchant_id
			FROM {{merchant}} a 					
			WHERE a.status='active' AND a.is_ready ='2' 	
			AND merchant_id = ".intval($filter['merchant_id'])."	
			AND a.delivery_distance_covered > (
			  ( $distance_exp * acos( cos( radians($filter[lat]) ) * cos( radians( latitude ) ) 
			   * cos( radians( lontitude ) - radians($filter[lng]) ) 
			  + sin( radians($filter[lat]) ) * sin( radians( latitude ) ) ) ) 
			)
		)
		";						
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){				
			foreach ($res as $val) {
				$data[$val['service_code']][$val['charge_type']] = array(
				  'service_code'=>$val['service_code'],
				  'charge_type'=>$val['charge_type'],
				  'estimation'=>$val['estimation'],
				  'shipping_type'=>$val['shipping_type']
				);
			}
			return $data;
		}
		return false;
	}		
	

	public static function estimationMerchant2($filter=array())
	{
		
	    $data = array();
		$stmt="
		SELECT merchant_id,service_code,charge_type,
		estimation,shipping_type
		FROM {{shipping_rate}} a
		WHERE
		shipping_type=".q($filter['shipping_type'])."
		AND merchant_id  IN (
		    SELECT merchant_id
			FROM {{merchant}} a 					
			WHERE a.status='active' AND a.is_ready ='2' 	
			AND merchant_id = ".intval($filter['merchant_id'])."			
		)
		";		
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){				
			foreach ($res as $val) {
				$data[$val['service_code']][$val['charge_type']] = array(
				  'service_code'=>$val['service_code'],
				  'charge_type'=>$val['charge_type'],
				  'estimation'=>$val['estimation'],
				  'shipping_type'=>$val['shipping_type']
				);
			}
			return $data;
		}
		throw new Exception( 'no results' );
	}		


	/*
	$filter = array(			
	  'search'=>$q,
	  'lat'=>$local_info->latitude,
	  'lng'=>$local_info->longitude,
	  'unit'=>Yii::app()->params['settings']['home_search_unit_type']  
	);
	*/
	public static function searchSuggestion($filter=array(), $lang = KMRS_DEFAULT_LANGUAGE)
	{
		$query_distance='';	$where = ''; $and= '';	 	
		$distance_exp = self::getDistanceExp($filter);
		$query = isset($filter['search'])?$filter['search']:'';
		$page = isset($filter['page'])?intval($filter['page']):0;
		$limit = isset($filter['limit'])?intval($filter['limit']):10;
		
		$unit = isset($filter['unit'])?$filter['unit']:'mi';
		$lat = isset($filter['lat'])?$filter['lat']:'';
		$lng = isset($filter['lng'])?$filter['lng']:'';
		
		if(!empty($lat) && !empty($lng)){
			$query_distance = "
			( $distance_exp * acos( cos( radians($lat) ) * cos( radians( latitude ) ) 
			* cos( radians( lontitude ) - radians($lng) ) 
			+ sin( radians($lat) ) * sin( radians( latitude ) ) ) ) 
			AS distance,
			";
			$where='HAVING distance < a.delivery_distance_covered';
			//$and = "OR cuisine_name LIKE ".q("%$query%")." ";
		} else $where = "WHERE 1";
		
		if(empty($query)){
			throw new Exception( 'no results' );
		}
		
		$stmt = "
		SELECT a.restaurant_slug as slug,a.restaurant_name as title,
		a.logo,a.path,a.delivery_distance_covered,a.status,a.is_ready,
		
		$query_distance
		
		IFNULL((
		 select GROUP_CONCAT(cuisine_name,';',color_hex,';',font_color_hex)
		 from {{view_cuisine}}
		 where language=".q($lang)."
		 and cuisine_id in (
		    select cuisine_id from {{cuisine_merchant}}
		    where merchant_id  = a.merchant_id
		 )		 		 
		),'') as cuisine_name
		
		FROM {{merchant}} a
		$where
		AND restaurant_name LIKE ".q("%$query%")."
		AND a.status='active'  AND a.is_ready ='2' 		
		$and
		ORDER BY a.restaurant_name ASC
		LIMIT $page,$limit
		";								
		if( $res = CCacheData::queryAll($stmt)){
			$data = array();
			foreach ($res as $val) {
				$val2 = $val;	
				$cuisine_list = array();
				$cuisine_name = explode(",",$val['cuisine_name']);				
				if(is_array($cuisine_name) && count($cuisine_name)>=1){
					foreach ($cuisine_name as $cuisine_val) {						
						$cuisine = explode(";",$cuisine_val);								
						$cuisine_list[]=array(
						  'cuisine_name'=>isset($cuisine[0])?Yii::app()->input->xssClean($cuisine[0]):'',
						  'bgcolor'=>isset($cuisine[1])?  (!empty($cuisine[1])?$cuisine[1]:'#ffd966')  :'#ffd966',
						  'fncolor'=>isset($cuisine[2])? (!empty($cuisine[2])?$cuisine[2]:'#ffd966') :'#000',
						);
					}
				}
				
				$val2['title'] = Yii::app()->input->xssClean($val2['title']);
				$val2['cuisine_name'] = (array)$cuisine_list;
				$val2['url']= Yii::app()->createAbsoluteUrl($val2['slug']);				
				$val2['url_logo'] = CMedia::getImage($val2['logo'],$val2['path'],"@2x",
				CommonUtility::getPlaceholderPhoto('merchant'));
				$data[] = $val2;
			}
			return $data;
		}
		throw new Exception( 'no results' );
	}

	public static function searchSuggestionFood($filter=array(), $lang = KMRS_DEFAULT_LANGUAGE)
	{
		$query_distance='';	$where = ''; $and= '';	 	
		$distance_exp = self::getDistanceExp($filter);
		$query = isset($filter['search'])?$filter['search']:'';
		$page = isset($filter['page'])?intval($filter['page']):0;
		$limit = isset($filter['limit'])?intval($filter['limit']):10;
		
		$unit = isset($filter['unit'])?$filter['unit']:'mi';
		$lat = isset($filter['lat'])?$filter['lat']:'';
		$lng = isset($filter['lng'])?$filter['lng']:'';
		
		if(!empty($lat) && !empty($lng)){
			$query_distance = "
			( $distance_exp * acos( cos( radians($lat) ) * cos( radians( latitude ) ) 
			* cos( radians( lontitude ) - radians($lng) ) 
			+ sin( radians($lat) ) * sin( radians( latitude ) ) ) ) 
			AS distance
			";
			$where='HAVING distance < a.delivery_distance_covered';			
		} else $where = "WHERE 1";
		
		if(empty($query)){
			throw new Exception( 'no results' );
		}

		$stmt = "
		SELECT 
		a.item_id,
		CASE 
			WHEN b.item_name IS NOT NULL AND b.item_name != '' THEN b.item_name
			ELSE a.item_name
		END AS item_name,		
		a.photo,
		a.path,
		a.merchant_id,
		merchant.restaurant_slug

		FROM {{item}} a

		LEFT JOIN {{item_translation}} b 
		ON a.item_id = b.item_id AND b.language = ".q($lang)."

		LEFT JOIN {{merchant}} merchant
		ON a.merchant_id = merchant.merchant_id 

		WHERE a.item_name LIKE ".q("%$query%")."
		AND a.status='publish'
		AND a.available=1
		AND a.visible=1

		AND a.merchant_id IN (
			select merchant_id
			from {{merchant}}
			where delivery_distance_covered > (
				select 
			    $query_distance from {{merchant}}
				where merchant_id = a.merchant_id
				AND status='active'  AND is_ready ='2' 		
			)
		)
		";										
		if( $res = CCacheData::queryAll($stmt)){			
			$data = array();
			foreach ($res as $val) {
				$val2 = $val;					
				$val2['title'] = CommonUtility::safeDecode($val2['item_name']);
				$val2['cuisine_name'] = '';
				$val2['url']= Yii::app()->createAbsoluteUrl($val2['restaurant_slug']);				
				$val2['url_logo'] = CMedia::getImage($val2['photo'],$val2['path'],"@2x",
				CommonUtility::getPlaceholderPhoto('item'));
				$data[] = $val2;
			}
			return $data;
		}
		throw new Exception( 'no results' );
	}
	
	public static function checkStoreOpen($merchant_id=0, $date_now='', $time_now='',$time_config_type='regular_hours')
	{
		$day_of_week = strtolower(date("N",strtotime($date_now)));
		$today_now = strtolower(date("l",strtotime($date_now)));
		
		$stmt="
		SELECT a.merchant_id,
		
		(
		select GROUP_CONCAT(day_of_week,';',start_time,';',end_time order by day_of_week asc)
		from {{opening_hours}}
		where merchant_id = a.merchant_id
		and day_of_week>=".q(intval($day_of_week))."
		and status='open'		
		and time_config_type =".q($time_config_type)."
		) as next_opening,		
		(
			select count(*) from
			{{opening_hours}}
			where
			merchant_id = a.merchant_id
			and
			day=".q($today_now)."
			and
			status = 'open'
			and time_config_type =".q($time_config_type)."
			and 
			
			(
			CAST(".q($time_now)." AS TIME)
			BETWEEN CAST(start_time AS TIME) and CAST(end_time AS TIME)
			
			or
			
			CAST(".q($time_now)." AS TIME)
			BETWEEN CAST(start_time_pm AS TIME) and CAST(end_time_pm AS TIME)
			
			)			
		) as merchant_open_status,

		holiday.id as holiday_id,
		holiday.reason as holiday_reason
		
		FROM {{merchant}} a
		
		left JOIN (
		   SELECT merchant_id,id,reason from {{holidays}}
		   where holiday_date=".q($date_now)."
		) holiday 
		on a.merchant_id = holiday.merchant_id

		WHERE 
		a.merchant_id = ".q($merchant_id)."		
		";					
		if($res=Yii::app()->db->createCommand($stmt)->queryRow()){					
			/*next_opening*/	
			$next_opening = '';
			if(!empty($res['next_opening'])){
				$next_open = explode(",",$res['next_opening']);							
				if(is_array($next_open) && count($next_open)>=1){
					$next_open = isset($next_open[0])?$next_open[0]:'';						
					$next_open = explode(";",$next_open);	
																	
					$next_open_date = self::getDayWeek($date_now,$next_open[0]);
					$next_open_date ="$next_open_date $next_open[1]";
										
					$next_opening = t("Opens [day] at [time]",array(
					 '[day]'=>Date_Formatter::date($next_open_date,"E"),
					 '[time]'=>Date_Formatter::Time($next_open_date,"h:mm a")
					));
				}
			}

			$res['next_opening'] = $next_opening;
			if($res['holiday_id']>0){
				$res['merchant_open_status'] = 0;
			}			
			return $res;
		}
		throw new Exception( 'no results' );
	}
	
	public static function checkCurrentTime($datetime_now='', $datetime_to='')
	{		
		$diff = CommonUtility::dateDifference($datetime_to,$datetime_now);
		if(is_array($diff) && count($diff)>=1){
			if($diff['days']>0){
			   throw new Exception( "Selected delivery time is already past" );	
			}			
			if($diff['hours']>0){
			   throw new Exception( "Selected delivery time is already past" );	
			}			
			if($diff['minutes']>1){
			   throw new Exception( "Selected delivery time is already past" );	
			}			
		}
		return true;
	}
	
	public static function storeAvailable($merchant_uuid='')
	{
		$merchant = CMerchants::getByUUID($merchant_uuid);
		$message = t("Currently unavailable");
		if($merchant->close_store>0){
             throw new Exception( $message );	
         } elseif ( $merchant->pause_ordering>0){
             $meta = AR_merchant_meta::getValue($merchant->merchant_id,'pause_reason');
             if($meta){			 		                  
                  throw new Exception( !empty($meta['meta_value'])?$meta['meta_value']:$message );	
             } else throw new Exception( $message );
         } else {
			$options = OptionsTools::find(['enabled_website_ordering']);
			$enabled_website_ordering = isset($options['enabled_website_ordering'])?$options['enabled_website_ordering']:false;
			$enabled_website_ordering = $enabled_website_ordering==1?true:false;			
			if(!$enabled_website_ordering){
				throw new Exception( $message );	
			}
		 } 
         return true;
	}
	
	public static function storeAvailableByID($merchant_id='')
	{
		$merchant = CMerchants::get($merchant_id);
		$message = t("Currently unavailable");
		if($merchant->close_store>0){
             throw new Exception( $message );	
        } elseif ( $merchant->pause_ordering>0){
             $meta = AR_merchant_meta::getValue($merchant->merchant_id,'pause_reason');
             if($meta){			 		                  
                  throw new Exception( !empty($meta['meta_value'])?$meta['meta_value']:$message );	
             } else throw new Exception( $message );
        } else {
			$options = OptionsTools::find(['enabled_website_ordering']);
			$enabled_website_ordering = isset($options['enabled_website_ordering'])?$options['enabled_website_ordering']:false;
			$enabled_website_ordering = $enabled_website_ordering==1?true:false;			
			if(!$enabled_website_ordering){
				throw new Exception( $message );	
			}
		}
        return true;
	}
	
	public static function getFeed($filter=array(),$sort_by='')
	{		
		
		$length = isset($filter['limit'])?$filter['limit']:10;
		$page = isset($filter['page'])?$filter['page']:0;						
		$continue = false;
		$distance_exp = self::getDistanceExp($filter);		
		$unit = isset($filter['unit'])?$filter['unit']:'mi';
		// if($transaction_type == "pickup"){
		// 		// For pickup, use larger radius (10x)
		// 		$where_distance_filter = "
		// 		AND 
		// 		CASE 
		// 		WHEN a.distance_unit = 'mi' THEN
		// 		  (3959 * ACOS(SIN(RADIANS(a.latitude)) * SIN(RADIANS($filter[lat])) + COS(RADIANS(a.latitude)) * COS(RADIANS($filter[lat])) * COS(RADIANS(a.lontitude - $filter[lng]))))
		// 		WHEN a.distance_unit = 'km' THEN
		// 		  (6371 * ACOS(SIN(RADIANS(a.latitude)) * SIN(RADIANS($filter[lat])) + COS(RADIANS(a.latitude)) * COS(RADIANS($filter[lat])) * COS(RADIANS($filter[lng] - a.lontitude))))
		// 		END < (a.delivery_distance_covered * 10)
		// 		";
		// 		$having_condition = "";
		// 		$sort_distance = ', distance ASC';
		// 	} else {
				// For delivery: use polygon if available, else use radius
				// Wrap ST_Within in a way that handles errors gracefully
				$filter['condition'] .= "
				AND (
					(
						a.delivery_zone_covered IS NOT NULL 
						AND a.delivery_zone_covered != '' 
						AND a.delivery_zone_type = 'POLYGON'
						AND ST_Within(
							ST_PointFromText(CONCAT('POINT(', ".q($filter['lng']).", ' ', ".q($filter['lat']).", ')')), 
							ST_PolyFromText(CONCAT('POLYGON', a.delivery_zone_covered))
						) = 1
					) OR (
						(a.delivery_zone_covered IS NULL OR a.delivery_zone_covered = '' OR a.delivery_zone_type != 'POLYGON' OR a.delivery_zone_type IS NULL) 
						AND (
							$distance_exp * acos( 
								cos( radians($filter[lat]) ) * cos( radians( a.latitude ) ) 
								* cos( radians( a.lontitude ) - radians($filter[lng]) ) 
								+ sin( radians($filter[lat]) ) * sin( radians( a.latitude ) ) 
							)  
						) <= a.delivery_distance_covered
					)
				)
				";
				$filter['having'] = "";
				// $sort_distance = ', distance ASC';
			// }
		$today_day_of_week = date('N');

		$criteria=new CDbCriteria();
		$criteria->alias="a";    		
    	$criteria->select="
		a.merchant_id,
		a.merchant_uuid,
		a.restaurant_name,
		a.restaurant_slug,
		a.delivery_distance_covered,
		a.logo,
		a.path,
		a.header_image,
		a.path2,
		a.distance_unit,
		a.close_store,
		a.disabled_ordering,
		a.pause_ordering,
		b.ratings,

		CASE 
			WHEN a.distance_unit = 'mi' THEN
			  (3959 * ACOS(SIN(RADIANS(a.latitude)) * SIN(RADIANS($filter[lat])) + COS(RADIANS(a.latitude)) * COS(RADIANS($filter[lat])) * COS(RADIANS(a.lontitude - $filter[lng]))))
			WHEN a.distance_unit = 'km' THEN
			  (6371 * ACOS(SIN(RADIANS(a.latitude)) * SIN(RADIANS($filter[lat])) + COS(RADIANS(a.latitude)) * COS(RADIANS($filter[lat])) * COS(RADIANS($filter[lng] - a.lontitude))))
		    END AS distance

		,(
			select count(*) from
			{{opening_hours}}
			where
			merchant_id = a.merchant_id
			and
			day=".q($filter['today_now'])."
			and
			status = 'open'
			and 
			
			(
			CAST(".q($filter['time_now'])." AS TIME)
			BETWEEN CAST(start_time AS TIME) and CAST(end_time AS TIME)							
			)
			
		) as open_status,

		(
			select GROUP_CONCAT(cuisine_id)
			from {{cuisine_merchant}}
			where merchant_id = a.merchant_id
		) as cuisine_group,

		(
			select option_value
			from {{option}}
			where option_name='merchant_delivery_charges_type'
			and merchant_id = a.merchant_id
		) as charge_type,

		(
			select COUNT(DISTINCT(merchant_id))
			from {{favorites}}
			where merchant_id = a.merchant_id
			and client_id=".q($filter['client_id'])."
			and fav_type='restaurant'
		) as saved_store,

		(
		select option_value
		from {{option}}
		where option_name='free_delivery_on_first_order'
		and merchant_id = a.merchant_id
		) as free_delivery,


		CASE 
		WHEN opening_hours.status = 'open' 
		AND STR_TO_DATE(opening_hours.start_time, '%H:%i') > TIME(NOW()) 
		THEN CONCAT(opening_hours.start_time,';',opening_hours.end_time,';',opening_hours.day_of_week,';',opening_hours.day)

		ELSE (
		SELECT CONCAT(next_oh.start_time,';',next_oh.end_time,';',next_oh.day_of_week,';',next_oh.day)
		FROM {{opening_hours}} next_oh
		WHERE next_oh.merchant_id = a.merchant_id
			AND next_oh.status = 'open'
			AND (
				next_oh.day_of_week > DAYOFWEEK(NOW()) - 1  -- Find next day
				OR (next_oh.day_of_week = 0 AND DAYOFWEEK(NOW()) != 1)  -- Handle Sunday properly
				)
		ORDER BY next_oh.day_of_week ASC 
		LIMIT 1
		) 
		END AS next_opening_hours,

		CASE 
		WHEN a.pause_ordering = 1 THEN (
			SELECT mm.meta_value
			FROM {{merchant_meta}} mm 
			WHERE mm.merchant_id = a.merchant_id
			AND meta_name='pause_reason'
			LIMIT 0,1
		)
		ELSE ".q(t('Currently unavailable'))."
		END AS close_reason
		";
				
		
		if(isset($filter['having'])){
			$criteria->having = $filter['having'];
		}
		if(isset($filter['condition'])){
			$criteria->condition = $filter['condition'];
		}
		if(isset($filter['params'])){
			$criteria->params = $filter['params'];
		}
		if(isset($filter['search'])){
			$criteria->addSearchCondition($filter['search'], $filter['search_params'] );
		}

		$criteria->join = "
		LEFT JOIN {{view_ratings}} b ON a.merchant_id = b.merchant_id
		
		LEFT JOIN {{opening_hours}} opening_hours 
			ON a.merchant_id = opening_hours.merchant_id 
			AND opening_hours.day_of_week = DAYOFWEEK(NOW()) - 1
		";

		$criteria->order = "close_store,disabled_ordering,pause_ordering ASC, open_status+0 DESC, is_sponsored DESC, distance ASC";			
		if(!empty($sort_by)){
			switch($sort_by){
				case "distance":					
					$criteria->order = "distance ASC,close_store,disabled_ordering,pause_ordering ASC, open_status+0 DESC";			
					break;
				case "recommended":	
					$criteria->order = "is_sponsored DESC,close_store,disabled_ordering,pause_ordering ASC, open_status+0 DESC";
					break;
				case "top_rated":	
					$criteria->order = "ratings DESC, close_store,disabled_ordering,pause_ordering ASC, open_status+0 DESC";
					break;
			}
		}
		
		
		$count = AR_merchant::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
		$pages->setCurrentPage( intval($page) );        
		$pages->pageSize = intval($length);
		$pages->applyLimit($criteria);      
		$page_count = $pages->getPageCount();	        
		if($page_count > ($page+1) ){
			$continue = true;
		}   	        			
		
		// $dependency = CCacheData::dependency();
		$model = AR_merchant::model()->findAll($criteria); 		
				
		if($model){
			$data = []; $merchant=[];
			foreach ($model as $items) {		
				
				//dump($items->open_status);
				$merchant[] = $items->merchant_id;		
				$distance = Price_Formatter::convertToRaw($items->distance,2);

				$available = true;
				if($items->close_store==1 || $items->open_status==0 || $items->disabled_ordering==1 || $items->pause_ordering){
					$available = false;
				} 				

				/*next_opening*/	
				$next_opening = '';   	
				if($items->open_status==0){
					$currentDate = new DateTime();  
					$next_opening_hours = !empty($items->next_opening_hours)?explode(';',$items->next_opening_hours):null;					
					if(!empty($next_opening_hours)){
						$start_time = isset($next_opening_hours[0])?$next_opening_hours[0]:'';
						$end_time = isset($next_opening_hours[1])?$next_opening_hours[1]:'';
						$day_of_week = isset($next_opening_hours[2])?$next_opening_hours[2]:'';	
												
						$daysToAdd = ($day_of_week > $today_day_of_week) 
						? ($day_of_week - $today_day_of_week) 
						: (7 - $today_day_of_week + $day_of_week);
						$openingDate = Date_Formatter::date($currentDate->modify("+$daysToAdd days")->format('Y-m-d'),"E",true);

						// $startTime = DateTime::createFromFormat('H:i', $start_time)->format('h:i A');           
						// $endTime = DateTime::createFromFormat('H:i', $end_time)->format('h:i A');
						// $next_opening = t("Opens [day] at [time]",[                            
						// 	'[day]'=>$openingDate,
						// 	'[time]'=>"$startTime - $endTime"
						// ]);                        

						$startDt = DateTime::createFromFormat('H:i', $start_time);
						if ($startDt !== false) {
							$startTime = $startDt->format('h:i A');
						} 
						$endDt = DateTime::createFromFormat('H:i', $end_time);
						if ($endDt !== false) {
							$endTime = $endDt->format('h:i A');
						} 

						if (!empty($startTime) && !empty($endTime)) {
							$next_opening = t("Opens [day] at [time]", [                            
								'[day]' => $openingDate,
								'[time]' => "$startTime - $endTime"
							]);
					    }

					}										
				}

				$data[$items->merchant_id] = [
					'merchant_id'=>$items->merchant_id,
					'merchant_uuid'=>$items->merchant_uuid,
					'restaurant_name'=>Yii::app()->input->xssClean($items->restaurant_name),
					'restaurant_slug'=>$items->restaurant_slug,
					'restaurant_url'=>Yii::app()->createAbsoluteUrl("/$items->restaurant_slug"),
					'delivery_distance_covered'=>$items->delivery_distance_covered,
					'distance'=>$items->distance,
					'distance_short'=>"$distance $items->distance_unit",
					'distance_pretty'=>t("{{distance} {{unit}}",[
						'{{distance}'=>$distance,
						'{{unit}}'=>MapSdk::prettyUnit($items->distance_unit)
					]),
					'charge_type'=>$items->charge_type,					
					'cuisine_group'=>  explode(",", $items->cuisine_group ?? ""),
					'url_logo'=>CMedia::getImage($items->logo,$items->path,"@2x",CommonUtility::getPlaceholderPhoto('item')),
					'url_banner'=>CMedia::getImage($items->header_image,$items->path2,"@2x",CommonUtility::getPlaceholderPhoto('item')),
					'available'=>$available,
					'open_status_raw'=>$items->open_status,
					'open_status'=>$items->close_store==1?0:$items->open_status,
					'saved_store'=>$items->saved_store,					
					'close_store'=>$items->close_store,
					'close_reason'=>$items->close_reason,
					'next_opening'=>$next_opening,					
					'free_delivery'=>$items->free_delivery==1?true:false,
					'cuisine'=>[],
					'promos'=>[],
					'reviews'=>[],
					'services'=>[],
					'estimation'=>''
				];
			}			
			return [
				'continue'=>$continue,
				'merchant'=>$merchant,
				'page_count'=>$page_count,
				'count'=>$count,
				'data'=>$data
			];
		}
		throw new Exception( "No results" );
	}	

	public static function getReviews($merchant=array())
	{
		$criteria=new CDbCriteria();
		$criteria->select = "merchant_id,review_count,ratings";
		$criteria->addInCondition('merchant_id',$merchant);		

		$dependency = CCacheData::dependency();
		$model = AR_view_ratings::model()->cache(Yii::app()->params->cache, $dependency)->FindAll($criteria); 				
		if($model){
			$data = [];
			foreach ($model as $items) {				
				$data[$items->merchant_id] = [
					'review_count'=>intval($items->review_count),
					'rating'=>$items->ratings,
					'ratings'=>Price_Formatter::convertToRaw($items->ratings,1),
				];
			}
			return $data;
		}
		throw new Exception( "No results" );
	}

	public static function getCuisine($merchant=array(),$language='')
	{
		$criteria=new CDbCriteria();
		$criteria->select = "cuisine_id,cuisine_name";
		$criteria->condition = "language=:language AND cuisine_id IN (
			select cuisine_id from {{cuisine_merchant}} 
			where merchant_id IN (". implode(',', $merchant) .")
		) ";
		$criteria->params = [
			':language'=>$language
		];		
		$dependency = CCacheData::dependency();
		$model = AR_cuisine_translation::model()->cache(Yii::app()->params->cache, $dependency)->FindAll($criteria); 						
		if($model){
			$data = [];
			foreach ($model as $items) {				
				$data[$items->cuisine_id] = [					
					'name'=>$items->cuisine_name
				];
			}
			return $data;
		}
		throw new Exception( "No results" );
	}

	public static function getMaxMinItem($merchant=array())
	{
		$criteria=new CDbCriteria();
		$criteria->select = "
		merchant_id,MIN(price) as min_price , MAX(price) as max_price
		";
		$criteria->addInCondition('merchant_id',$merchant);
		$criteria->group = 'merchant_id';		
		$dependency = CCacheData::dependency();
		$model = AR_item_relationship_size::model()->cache(Yii::app()->params->cache, $dependency)->FindAll($criteria); 								
		if($model){
			$data = [];
			foreach ($model as $items) {				
				$data[$items->merchant_id] = [					
					'min'=>$items->min_price,
					'max'=>$items->max_price,
					'min_pretty'=>Price_Formatter::formatNumber($items->min_price),
					'max_pretty'=>Price_Formatter::formatNumber($items->max_price),
				];
			}			
			return $data;
		}
		throw new Exception( "No results" );
	}

	public static function getMerchantList($merchant_ids=array())
	{
		$data = [];
		$criteria=new CDbCriteria();
		$criteria->addInCondition('merchant_id',$merchant_ids);
		$dependency = CCacheData::dependency();
		$model = AR_merchant::model()->cache(Yii::app()->params->cache, $dependency)->FindAll($criteria);
		if($model){			
			foreach ($model as $items) {
				$data[$items->merchant_id] = [
					'merchant_uuid'=>$items->merchant_uuid,
					'restaurant_slug'=>$items->restaurant_slug,
					'restaurant_name'=>$items->restaurant_name,
				];
			}
		}
		return $data;
	}

	public static function searchSuggestionFoodRestaurants($search='',$language=KMRS_DEFAULT_LANGUAGE)
	{
		$stmt = "
		(SELECT 
		'items' as type,
		b.item_name as name
		FROM {{item}} a
		left JOIN (
			SELECT item_id,item_name FROM {{item_translation}} WHERE language = ".q($language)."
		) b 
		on a.item_id = b.item_id
		WHERE
		b.item_name LIKE ".q("%$search%")."
		and a.status='publish' 
		and a.available=1
		LIMIT 0,10
		)

		UNION ALL

		(SELECT 
		'merchant' as type,
		restaurant_name as name
		FROM {{merchant}}
		WHERE restaurant_name LIKE ".q("%$search%")."
		AND status='active'
		AND is_ready = 2
		LIMIT 0,10
		)
		";			
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){			
			return $res;
		}
		throw new Exception( "No results" );
	}

    public static function estimationNew($filter=array() , $filter_location = true)
	{
		$lat = isset($filter['lat'])?$filter['lat']:'';
		$lng = isset($filter['lng'])?$filter['lng']:'';
		$transaction_type = isset($filter['transaction_type'])?$filter['transaction_type']:'delivery';
			
		$and = '';	$and2='';	
		if($filter_location){
			$and = "
			,
			CASE 
				WHEN a.distance_unit = 'mi' THEN
					(3959 * ACOS(SIN(RADIANS(a.latitude)) * SIN(RADIANS($lat)) + COS(RADIANS(a.latitude)) * COS(RADIANS($lat)) * COS(RADIANS(a.lontitude - $lng))))
				WHEN a.distance_unit = 'km' THEN
					(6371 * ACOS(SIN(RADIANS(a.latitude)) * SIN(RADIANS($lat)) + COS(RADIANS(a.latitude)) * COS(RADIANS($lat)) * COS(RADIANS($lng - a.lontitude))))
			END AS distance
			";
			$and2 = "
			AND (
				b.charge_type = 'fixed' OR
				(
					CASE 
						WHEN a.distance_unit = 'mi' THEN
							(3959 * ACOS(SIN(RADIANS(a.latitude)) * SIN(RADIANS($lat)) + COS(RADIANS(a.latitude)) * COS(RADIANS($lat)) * COS(RADIANS(a.lontitude - $lng))))
						WHEN a.distance_unit = 'km' THEN
							(6371 * ACOS(SIN(RADIANS(a.latitude)) * SIN(RADIANS($lat)) + COS(RADIANS(a.latitude)) * COS(RADIANS($lat)) * COS(RADIANS($lng - a.lontitude))))
					END
				) BETWEEN b.distance_from AND b.distance_to
			);
			";
		}
		$stmt = "
		SELECT
			a.merchant_id,
			a.restaurant_name,
			b.service_code,
			b.charge_type,
			b.estimation,
			b.distance_from,
			b.distance_to
			$and
		FROM
			{{merchant}} a
		LEFT JOIN
			{{shipping_rate}} b ON a.merchant_id = b.merchant_id
		WHERE
			b.service_code = ".q($transaction_type)."
			and b.shipping_type ='standard'		
			$and2	
		";		
		$dependency = CCacheData::dependency();	
		if($res = Yii::app()->db->cache(Yii::app()->params->cache,$dependency)->createCommand($stmt)->queryAll()){
			foreach ($res as $items) {
				$data[$items['merchant_id']][$items['service_code']][$items['charge_type']] = [
					'merchant_id'=>$items['merchant_id'],
					'estimation'=>$items['estimation'],
				];
			}
			return $data;
		}
		return false;
	}			

	public static function estimationMerchantNew($filter=array())
	{
		$data = [];
		$merchant_id = isset($filter['merchant_id'])?$filter['merchant_id']:0;  		
		$distance = isset($filter['distance'])?$filter['distance']:0;
		$shipping_type = isset($filter['shipping_type'])?$filter['shipping_type']:'';		
		$charges_type = isset($filter['charges_type'])?$filter['charges_type']:'';		
		$transaction_type = $filter['transaction_type'] ?? null;

		$and = '';
		if($transaction_type){
			$and = "
			AND a.service_code=".q($transaction_type)." 
			AND a.charge_type=".q($charges_type)."
			";
		}
		
		$stmt = "
		SELECT a.* 
		FROM {{shipping_rate}} a
		WHERE
		(
				(a.service_code ='delivery' AND a.charge_type = 'dynamic' AND ".q(floatval($distance))." BETWEEN a.distance_from AND a.distance_to ) OR
				(a.service_code ='delivery' AND a.charge_type = 'fixed'  ) OR
				(a.service_code ='pickup' AND a.charge_type = 'fixed') OR 
				(a.service_code ='dinein' AND a.charge_type = 'fixed')
			)
		AND a.merchant_id = ".q($merchant_id)."
		AND a.shipping_type = ".q($shipping_type)."
		$and
		";					
		
		if($transaction_type){
			if($res = Yii::app()->db->createCommand($stmt)->queryRow()){
				return $res;
			}
			return false;
		}
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){			
			foreach ($res as $val) {			
				if($val['service_code']=='delivery'){
					if($val['charge_type']==$charges_type){
						$data[$val['service_code']][$val['charge_type']] = array(
							'service_code'=>$val['service_code'],
							'charge_type'=>$val['charge_type'],
							'estimation'=>$val['estimation'],
							'shipping_type'=>$val['shipping_type']
						);
					}
				} else {
					$data[$val['service_code']][$val['charge_type']] = array(
						'service_code'=>$val['service_code'],
						'charge_type'=>$val['charge_type'],
						'estimation'=>$val['estimation'],
						'shipping_type'=>$val['shipping_type']
					);
				}				
			}			
			return $data;
		}
		return false;
	}

	public static function getDayOfWeek($date) {
		return strtolower(date('l', strtotime($date)));
	}

	public static function getHolidayList($merchant_id=0)
	{
		$data = [];
		$stmt = "
		SELECT holiday_date
		FROM {{holidays}}		
		WHERE merchant_id=".q($merchant_id)."
		AND holiday_date >=CURRENT_DATE
		";
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
			foreach ($res as $items) {
				$data[] = $items['holiday_date'];
			}
		}
		return $data;
	}

	public static function openHours2($merchant_id='', $interval="20 mins", $days_to_generate=15)
	{
		$opening_hours = [];
		$order_by_days = '';
		$date_today = date('Y-m-d'); 		
		$yesterday = date('Y-m-d', strtotime($date_today. " -1 days"));	
		$tomorrow = date('Y-m-d', strtotime($date_today. " +1 days"));
		$current_time = date("H:i");		
		$time_now = date("H:i",strtotime("+".intval($interval)." minutes"));

		for($i=1; $i<=7; $i++){			
			$days = date('l', strtotime($yesterday. " +$i days"));			
			$days = strtolower($days);	
			$order_by_days.=q($days).",";	
			$daylist[$days]= date('Y-m-d', strtotime($yesterday. " +$i days"));	 
		}
		
		$order_by_days = substr($order_by_days,0,-1);
		$stmt="
		SELECT day,start_time,end_time
		FROM {{opening_hours}}
		WHERE merchant_id=".q($merchant_id)."
		AND status='open'			
		ORDER BY FIELD(day, $order_by_days),start_time ASC
		";				
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){						
			foreach ($res as $row) {
				$opening_hours[$row['day']][] = [
					'start_time' => $row['start_time'],
					'end_time' => $row['end_time'],
				];
			}							
						
			$today = new DateTime(); 			
			$dates_with_opening_hours = [];
			$dates_with_opening_times = [];

			for ($i = 0; $i < $days_to_generate; $i++) {
				$date = $today->format('Y-m-d');
				$day_of_week = self::getDayOfWeek($date);				
				$name = Date_Formatter::date($date,"eee dd MMM",true);																				
				if (isset($opening_hours[$day_of_week])) {					

					$is_today = false;
					if($date_today==$date){
						$name = t("Today").", $name";
						$is_today = true;
					} elseif ($tomorrow==$date){
						$name = t("Tomorrow").", $name";
					}

					$start_times = [];
					$end_times = [];
					foreach ($opening_hours[$day_of_week] as $period) {
						$start_times[] = $period['start_time'];
						$end_times[] = $period['end_time'];
					}					
										
					$start_time = min(array_map('strtotime', $start_times));
					$end_time = max(array_map('strtotime', $end_times));

					$start_time = date('H:i', $start_time);
					$end_time = date('H:i', $end_time);					
					
					$time_is_valid = true;
					if($is_today){												
						$start_datetime = new DateTime($current_time);
                        $end_datetime = new DateTime($end_time);						
						if ($end_datetime < $start_datetime) {							
							$time_is_valid = false;
						} else {							
							$timeInterval = $start_datetime->diff($end_datetime);
							$remaining_hours = $timeInterval->h;
							$remaining_minutes = $timeInterval->i; 
							//echo "Remaining time: {$remaining_hours} hour(s) and {$remaining_minutes} minute(s).";
							if($remaining_hours<=0){
								$time_is_valid = false;
							} else {								
								$start_time = self::blockMinutesRound($time_now, intval($interval) ); 
							}
						}
					}										
					
					$end_time = date("H:i",strtotime($end_time." -".intval($interval)." minutes"));
					
					if($time_is_valid){
						$dates_with_opening_hours[$date] = [						
							'name'=>$name,
							'value' => $date,
							'data'=>[
								'day'=>$day_of_week,
								'start_time' => $start_time,
								'end_time' => $end_time
							]						
						];	
					}
					
					//$end_time = date("H:i",strtotime($end_time." -".intval($interval)." minutes"));
					//$start_time = date("H:i",strtotime($start_time." +".intval(30)." minutes"));					
					$time_range = self::createTimeRange($start_time,$end_time,$interval);		
					$dates_with_opening_times[$date] = $time_range;

				} 
				// end if
				$today->modify('+1 day');	
			}
			// end for
			
			$holidays_date = self::getHolidayList($merchant_id);			
			if(!empty($dates_with_opening_hours) && !empty($holidays_date) ){				
				$dates_with_opening_hours = array_diff_key($dates_with_opening_hours, array_flip((array)$holidays_date));						
			}			
			return array(
				'dates'=>$dates_with_opening_hours,
				'time_ranges'=>$dates_with_opening_times				
			);
		}	
		// end if
		return false;
	}

	public static function getCuisineNew($merchant_ids=[], $language=KMRS_DEFAULT_LANGUAGE)
	{
		$stmt = "		
		SELECT 
		cm.merchant_id, 
		COALESCE(NULLIF(ct.cuisine_name, ''), c.cuisine_name) AS cuisine_name
		FROM 
		{{cuisine_merchant}} cm
		LEFT JOIN 
		{{cuisine}} c ON cm.cuisine_id = c.cuisine_id
		LEFT JOIN 
		{{cuisine_translation}} ct ON ct.cuisine_id = c.cuisine_id AND ct.language = ".q($language)."
		WHERE cm.merchant_id IN (".CommonUtility::arrayToQueryParameters($merchant_ids).")
		";		
		if( $res = CCacheData::queryAll($stmt)){
			return $res;
		}
		return false;
	}

	public static function searchSuggest($search='', $lang = KMRS_DEFAULT_LANGUAGE)
	{
		$stmt = "
			SELECT 
				'restaurant' AS result_type, 
				restaurant_name AS name
			FROM 
				{{merchant}}
			WHERE 
				restaurant_name LIKE :search

			UNION

			SELECT 
				'food_item' AS result_type, 
				CASE 
					WHEN b.item_name IS NOT NULL AND b.item_name != '' THEN b.item_name
					ELSE a.item_name
				END AS name
			FROM 
				{{item}} a
			LEFT JOIN 
				{{item_translation}} b 
			ON 
				a.item_id = b.item_id AND b.language = :language
			WHERE 
				(a.item_name LIKE :search OR b.item_name LIKE :search)
		";

		$command = Yii::app()->db->createCommand($stmt);
		$command->bindValue(':search', '%'.$search.'%');
		$command->bindValue(':language', $lang);		
		if($res = $command->queryAll()){
			foreach ($res as &$row) {
				foreach ($row as $key => $value) {
					$row[$key] = is_string($value) ? html_entity_decode($value, ENT_QUOTES, 'UTF-8') : $value;
				}
			}
			return $res;
		}
		throw new Exception( "No results" );
	}

	public static function isFavoriteAdded($fav_type='', $client_id=0, $merchant_id=0 )
	{
		$model = AR_favorites::model()->find("fav_type=:fav_type AND client_id=:client_id AND merchant_id=:merchant_id",[
			':fav_type'=>$fav_type,
			':client_id'=>$client_id,
			':merchant_id'=>$merchant_id,
		]);
		if($model){
			return $model;
		}
		return false;
	}

	public static function ReviewDetails($merchant_id = 0)
	{		
		$stmt = "
		SELECT rating, COUNT(*) as count
		FROM {{review}}
		WHERE merchant_id = :merchant_id
		AND status = :status
		GROUP BY rating
		";
		$command = Yii::app()->db->createCommand($stmt);
		$command->bindValues([
			':merchant_id' => $merchant_id,
			':status' => 'publish',
		]);
		$res = $command->queryAll();
	
		$counts = [
			5 => 0,
			4 => 0,
			3 => 0,
			2 => 0,
			1 => 0,
		];
		$total = 0;
	
		// Populate counts
		if ($res) {
			foreach ($res as $row) {				
				$rating = (int)$row['rating'];
				$count = (int)$row['count'];
				if (isset($counts[$rating])) {
					$counts[$rating] = $count;
					$total += $count;
				}
			}
		}

		
		// Calculate percentages
		$percentages = [];
		foreach ($counts as $rating => $count) {
			$percentages[$rating] = $total > 0 ? round(($count / $total) * 100, 2) : 0;
		}
	
		return $percentages;
	}	

}
/*end class*/