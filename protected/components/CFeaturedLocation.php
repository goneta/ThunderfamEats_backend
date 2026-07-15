<?php
class CFeaturedLocation
{
	
	public static function Details($featured_name='')
	{		
		$dependency = CCacheData::dependency(); 
		$model = AR_featured_location::model()->cache(Yii::app()->params->cache, $dependency)->find('featured_name=:featured_name AND status=:status', 
		array(
		  ':featured_name'=>$featured_name,
		  ':status'=>'publish'
		)); 		
		if($model){
			return array(  
			  'location_name'=>Yii::app()->input->stripClean($model->location_name),
			  'latitude'=>$model->latitude,
			  'longitude'=>$model->longitude,
			);
		}
		throw new Exception( 'no results' );
	}
	
	public static function Listing($featured_name='', $lang = KMRS_DEFAULT_LANGUAGE)
	{		
		$todays_date = date("Y-m-d H:i");
		$today_now = strtolower(date("l",strtotime($todays_date)));
		$time_now = date("H:i",strtotime($todays_date));
		$day_of_week = strtolower(date("N",strtotime($todays_date)));
		$day_of_week = $day_of_week>6?1:$day_of_week;
		$today_day_of_week = date('N');
		$currentDate = new DateTime();  

		CommonUtility::mysqlSetTimezone();
		$stmt="
		SELECT 
		a.merchant_id,
		a.restaurant_name,
		a.restaurant_slug,
		a.logo,
		a.path,
		a.header_image,
		a.path2,
		a.close_store,
		a.disabled_ordering,
		a.pause_ordering,		
	    (
			select count(*) from
			{{opening_hours}}
			where
			merchant_id = a.merchant_id
			and
			day=".q($today_now)."
			and
			status = 'open'
			and 			
			(
			CAST(".q($time_now)." AS TIME)
			BETWEEN CAST(start_time AS TIME) and CAST(end_time AS TIME)								
			)			
		) as merchant_open_status,

		(
		   select count(*) from {{holidays}}
		   where 
		   merchant_id = a.merchant_id
		   and
		   holiday_date=CURDATE()
		) as holiday_status,
	    
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
		END AS next_opening_hours,

		holiday.reason as holiday_reason,

		(
		   select count(*) from {{holidays}}
		   where 
		   merchant_id = a.merchant_id
		   and
		   holiday_date=CURDATE()
		) as holiday_status,

		cm.cuisines,
		rt.review_count,
        rt.ratings,
			
		(
		select option_value
		from {{option}}
		where option_name='merchant_delivery_charges_type'
		and merchant_id = a.merchant_id
		) as charge_type		
		
		FROM {{merchant}} a		

		LEFT JOIN (
          select merchant_id,cuisines from {{view_cuisine_merchant}} where language=".q($lang)."
        ) cm 
        on a.merchant_id = cm.merchant_id

		LEFT JOIN (
          select merchant_id,review_count,ratings from {{view_ratings}} 
        ) rt 
        on a.merchant_id = rt.merchant_id

		left JOIN (
		   SELECT merchant_id,reason from {{holidays}}
		   where holiday_date=CURDATE()
		) holiday 
		on a.merchant_id = holiday.merchant_id

		LEFT JOIN {{opening_hours}} oh 
        ON a.merchant_id = oh.merchant_id 
        AND oh.day_of_week = DAYOFWEEK(NOW()) - 1
		

		WHERE a.status='active'  AND a.is_ready ='2' 
		AND a.merchant_id IN (
		  select merchant_id from {{merchant_meta}}
		  where merchant_id = a.merchant_id 
		  and meta_name='featured'
		  and meta_value=".q($featured_name)."
		)			
		GROUP BY a.merchant_id
		ORDER BY close_store,disabled_ordering,pause_ordering ASC, merchant_open_status+0 DESC,holiday_status+0 ASC, a.date_created ASC		
		LIMIT 0,50			
		";					
		if($res = CCacheData::queryAll($stmt)){						
			$merchants = [];
			foreach ($res as $val) {						
				$merchants[] = $val['merchant_id'];
				$ratings = isset($val['ratings'])?$val['ratings']:0;
                $ratings = $ratings>0? number_format($val['ratings'],0) : 0;

				/*next_opening*/	
				$next_opening = '';				                    
				if($val['current_status']=="closed"){
					$next_opening_hours = !empty($val['next_opening_hours'])?explode(';',$val['next_opening_hours']):null;					
					if(!empty($next_opening_hours)){
						$start_time = isset($next_opening_hours[0])?$next_opening_hours[0]:'';
						$end_time = isset($next_opening_hours[1])?$next_opening_hours[1]:'';
						$day_of_week = isset($next_opening_hours[2])?$next_opening_hours[2]:'';	
												
                        $daysToAdd = ($day_of_week > $today_day_of_week) 
                        ? ($day_of_week - $today_day_of_week) 
                        : (7 - $today_day_of_week + $day_of_week);
                        $openingDate = Date_Formatter::date($currentDate->modify("+$daysToAdd days")->format('Y-m-d'),"E",true);

                        $startTime = DateTime::createFromFormat('H:i', $start_time)->format('c');           						
                        $endTime = DateTime::createFromFormat('H:i', $end_time)->format('c');
						$startTime = Date_Formatter::Time($startTime);
						$endTime = Date_Formatter::Time($endTime);
                        $next_opening = t("Opens [day] at [time]",[                            
                            '[day]'=>$openingDate,
                            '[time]'=>"$startTime - $endTime"
                        ]);                        
					}										
				}

				$data[] = [
					'merchant_id'=>$val['merchant_id'],
					'restaurant_name'=>CommonUtility::safeDecode($val['restaurant_name']),
					'cuisines'=>CommonUtility::safeDecode($val['cuisines']),
					'review_count'=>$val['review_count'],
					'ratings'=>$ratings,
                    'ratings_pretty'=>t("{ratings} Ratings",[
                        '{ratings}'=>$ratings
                    ]),
					'url'=>Yii::app()->createAbsoluteUrl($val['restaurant_slug']),
					'logo'=> CMedia::getImage($val['logo'],$val['path'],Yii::app()->params->size_image_medium,CommonUtility::getPlaceholderPhoto('merchant_logo')),
					'bglogo'=> !empty($val['header_image'])? CMedia::getImage($val['header_image'],$val['path2'],Yii::app()->params->size_image_medium,CommonUtility::getPlaceholderPhoto('merchant_logo')) :'',
					'charge_type'=>$val['charge_type'],
					'close_store'=>$val['close_store'],
					'disabled_ordering'=>$val['disabled_ordering'],
					'pause_ordering'=>$val['pause_ordering'],					
					'holiday_status'=>$val['holiday_status'],
					'merchant_open_status'=>$val['merchant_open_status'],
					'holiday_reason'=>$val['holiday_reason'],					
					'next_opening'=>$next_opening
				];
			}			
			return [
				'data'=>$data,
				'merchants'=>$merchants,
			];
		}
		throw new Exception( 'no results' );
	}
	
	public static function services($featured_name='')
	{
		$data = array();
		$stmt="
		SELECT a.meta_value as service_name,
		a.merchant_id
		
		FROM {{merchant_meta}} a
		WHERE 
		a.merchant_id IN (
		  select merchant_id from {{merchant_meta}}
		  where merchant_id = a.merchant_id 
		  and meta_name='featured'
		  and meta_value=".q($featured_name)."
		)
		AND meta_name ='services'
		";				
		if($res = CCacheData::queryAll($stmt)){
			foreach ($res as $val) {
				$data[$val['merchant_id']][] = $val['service_name'];
			}
			return $data;
		}
		return false;
	}
	
	public static function estimation($featured_name=0)
	{
	    $data = array();
		$stmt="
		SELECT merchant_id,service_code,charge_type,
		estimation
		FROM {{shipping_rate}} a
		WHERE merchant_id  IN (
		    select merchant_id from {{merchant_meta}}
		    where merchant_id = a.merchant_id 
		    and meta_name='featured'
		    and meta_value=".q($featured_name)."
		)
		";				
		if($res = CCacheData::queryAll($stmt)){
			foreach ($res as $val) {
				$data[$val['merchant_id']][$val['service_code']][$val['charge_type']] = array(
				  'service_code'=>$val['service_code'],
				  'charge_type'=>$val['charge_type'],
				  'estimation'=>$val['estimation'],
				);
			}
			return $data;
		}
		return false;
	}	

	public static function merchantsEstimation($merchants=[])
	{
		$stmt = "
		select 
		shipping.id,
		shipping.merchant_id,
		shipping.service_code,
		shipping.charge_type,
		shipping.estimation
		FROM {{shipping_rate}} shipping
		WHERE shipping.merchant_id IN (".CommonUtility::arrayToQueryParameters($merchants).")
		AND shipping_type='standard'
		AND shipping.service_code 
		IN (
			select meta_value from 
			{{merchant_meta}}
			where 
			merchant_id=shipping.merchant_id 
		)
		and shipping.charge_type = (
			select option_value
			from {{option}}
			where option_name='merchant_delivery_charges_type'
			and merchant_id = shipping.merchant_id
		)
		group by shipping.merchant_id,shipping.service_code
		";
		if($res = CCacheData::queryAll($stmt)){
			$data = [];
			foreach ($res as $items) {
				$data[$items['merchant_id']] = $items['estimation'];
			}
			return $data;
		}
		return false;
	}

	public static function PauseReasonList()
	{
		$data =[];
		$model = AR_merchant_meta::model()->findAll("meta_name=:meta_name AND meta_value<>''",array(
			':meta_name'=>'pause_reason'
		));
		if($model){
			$data = array();
			foreach ($model as $items) {
				$data[$items->merchant_id] = $items->meta_value;
			}
		}
		return $data;
	}
	
}
/*end class*/