<?php
class Clocations {

    private static $exchange_rate;	
		
	public static function setExchangeRate($exchange_rate=0)
	{
		if($exchange_rate>0){
			self::$exchange_rate = $exchange_rate;
		} else {
			self::$exchange_rate = 1;
		}
	}

	public static function getExchangeRate()
	{
		return floatval( self::$exchange_rate>0? self::$exchange_rate : 1 );
	}

    public static function getDefaultCountry()
    {
        $country_id = '';
        $options = OptionsTools::find(['location_default_country']);
		$default_country = isset($options['location_default_country'])?$options['location_default_country']:'';
        if($default_country){
            if($model = AR_location_countries::model()->find("shortcode=:shortcode",[':shortcode'=>$default_country])){
                $country_id = $model->country_id;
            }
        }
        return $country_id;
    }

    public static function getDefaultCountryDetails()
    {     
        $options = OptionsTools::find(['location_default_country']);
		$default_country = isset($options['location_default_country'])?$options['location_default_country']:'';
        if($default_country){
            if($model = AR_location_countries::model()->find("shortcode=:shortcode",[':shortcode'=>$default_country])){
               return [
                'country_id'=>$model->country_id,
                'shortcode'=>$model->shortcode,
                'country_name'=>$model->country_name,
               ];
            }
        }
        return false;
    }

    public static function fetchCountry($search = '')
    {
        $data = [];
        $criteria=new CDbCriteria();
        $criteria->select ="country_id,shortcode,country_name";
        if(!empty($search)){
            $criteria->addSearchCondition("country_name",$search);
        }
        if($model=AR_location_countries::model()->findAll($criteria)){
            foreach ($model as $items) {
                $data[] = [
                    'value'=>$items->country_id,
                    'label'=>$items->country_name
                ];
            }
        }
        return $data;
    }

    public static function fetchState($country_id='', $search='')
    {
        $data = [];
        $criteria=new CDbCriteria();
        $criteria->select ="country_id,name,state_id";
        $criteria->condition = "country_id=:country_id";
        $criteria->params = [
            ':country_id'=>$country_id
        ];
        if(!empty($search)){
            $criteria->addSearchCondition("name",$search);
        }        
        if($model = AR_location_states::model()->findAll($criteria)){
            foreach ($model as $items) {
                $data[] = [
                    'value'=>$items->state_id,
                    'label'=>$items->name
                ];
            }
        }
        return $data;
    }

    public static function fetchCity($state_id='', $search='',$state_list=[])
    {
        $data = [];
        $criteria=new CDbCriteria();
        $criteria->select ="city_id,name,postal_code,state_id";        

        if($state_id>0){
            $criteria->condition = "state_id=:state_id";
            $criteria->params = [
                ':state_id'=>$state_id
            ];
        } elseif ( is_array($state_list) && count($state_list)>=1 ){
            $criteria->addInCondition("state_id",$state_list);
        }
        if(!empty($search)){
            $criteria->addSearchCondition("name",$search);
        }                
        if($model = AR_city::model()->findAll($criteria)){
            foreach ($model as $items) {
                $data[] = [
                    'value'=>$items->city_id,
                    'label'=>$items->name
                ];
            }
        }
        return $data;
    }

    public static function fetchPostal($state_list=[],$search='')
    {
        $data = [];
        $criteria=new CDbCriteria();
        $criteria->select ="city_id,name,postal_code,state_id";        

        $criteria->addInCondition("state_id",$state_list);
        if(!empty($search)){
            $criteria->addSearchCondition("name",$search);
        }                
        if($model = AR_city::model()->findAll($criteria)){
            foreach ($model as $items) {
                $data[] = [                    
                    'value'=>$items->postal_code,
                    'label'=>$items->postal_code
                ];
            }
        }
        return $data;
    }

    public static function fetchArea($city_id='', $search='')
    {
        $data = [];
        $criteria=new CDbCriteria();
        $criteria->select ="area_id,name";
        $criteria->condition = "city_id=:city_id";
        $criteria->params = [
            ':city_id'=>$city_id
        ];
        if(!empty($search)){
            $criteria->addSearchCondition("name",$search);
        }        
        if($model = AR_area::model()->findAll($criteria)){
            foreach ($model as $items) {
                $data[] = [
                    'value'=>$items->area_id,
                    'label'=>$items->name
                ];
            }
        }
        return $data;
    }

    public static function fetchZip($area_id='')
    {
        $stmt = "
        SELECT 
        a.area_id,
        b.city_id,
        b.postal_code

        FROM {{location_area}} a
        LEFT JOIN {{location_cities}} b
        ON
        a.city_id = b.city_id

        WHERE
        a.area_id = ".q($area_id)."
        ";
        if($res = Yii::app()->db->createCommand($stmt)->queryRow()){ 
            return $res;
        }
        throw new Exception( t(Helper_not_found) );
    }

    public static function locationStateList($country_id='')
    {
        $data = [];
        $criteria=new CDbCriteria();
        $criteria->select ="state_id,name";
        $criteria->condition = "country_id=:country_id";
        $criteria->params = [
            ':country_id'=>$country_id
        ];
        if($model = AR_location_states::model()->findAll($criteria)){
            foreach ($model as $items) {
                $data[] = $items->state_id;
            }
            return $data;
        }
        return false;
    }

    public static function getAddress($client_id=0)
    {
        $criteria=new CDbCriteria();        
        $criteria->select = "
        address_uuid,address_label,
        Concat(house_number,' ',street_number,' ',street,' ',area_name,' ',city_name,' ',zip_code,' ',state_name,' ',country_name) as complete_address,
        state_id,city_id,area_id,zip_code,
        city_name,area_name,state_name,country_name,
        street,street_number,
        location_name,delivery_options,delivery_instructions,address_label,
        country_id,state_id,city_id,area_id,zip_code,house_number,latitude,longitude
        ";
        $criteria->condition = "client_id=:client_id";
        $criteria->params = [
            ':client_id'=>$client_id,            
        ];
        $criteria->order = "address_id DESC";
        if($model = AR_view_client_address_locations::model()->findAll($criteria)){
            $data = [];
            foreach ($model as $items) {                
                $data[] = [
                    'address_uuid'=>$items->address_uuid,
                    'address_label'=>t($items->address_label),
                    'complete_address'=>$items->complete_address,
                    'city_name'=>$items->city_name,
                    'area_name'=>$items->area_name,
                    'state_name'=>$items->state_name,
                    'street_number'=>$items->street_number,
                    'state_id'=>$items->state_id,
                    'city_id'=>$items->city_id,
                    'area_id'=>$items->area_id,
                    'postal_code'=>$items->zip_code,
                    'zip_code'=>$items->zip_code,
                    'street_name'=>$items->street,
                    'country_name'=>$items->country_name,
                    'location_name'=>$items->location_name,
                    'delivery_options'=>$items->delivery_options,
                    'delivery_instructions'=>$items->delivery_instructions,
                    'address_label'=>$items->address_label,
                    'country_id'=>$items->country_id,
                    'state_id'=>$items->state_id,
                    'city_id'=>$items->city_id,
                    'area_id'=>$items->area_id,
                    'zip_code'=>$items->zip_code,
                    'house_number'=>$items->house_number,
                    'latitude'=>$items->latitude,
                    'longitude'=>$items->longitude,
                ];
            }
            return $data;
        } 
        throw new Exception( t("No available data") );
    }

    public static function getAddressDetails($client_id=0,$address_uuid='')
    {
        $model = AR_view_client_address_locations::model()->find("client_id=:client_id AND address_uuid=:address_uuid",[
            ':client_id'=>$client_id,
            ':address_uuid'=>$address_uuid            
        ]);        
        if($model){
            return [
                'address_uuid'=>$model->address_uuid,
                'street'=>$model->street,
                'street_name'=>$model->street,
                'street_number'=>$model->street_number,
                'state_name'=>$model->state_name,
                'city_name'=>$model->city_name,
                'area_name'=>$model->area_name,
                'country_name'=>$model->country_name,
                'country_code'=>$model->country_code,
                'location_name'=>$model->location_name,
                'delivery_options'=>$model->delivery_options,
                'delivery_instructions'=>$model->delivery_instructions,
                'address_label'=>$model->address_label,
                'country_id'=>$model->country_id,
                'state_id'=>$model->state_id,
                'city_id'=>$model->city_id,
                'area_id'=>$model->area_id,
                'zip_code'=>$model->zip_code,
                'house_number'=>$model->house_number,
                'formatted_address'=>"$model->house_number $model->street_number $model->street, $model->area_name, $model->city_name, $model->zip_code, $model->state_name",
                'complete_address'=>"$model->house_number $model->street_number $model->street, $model->area_name, $model->city_name, $model->zip_code, $model->state_name",
                'latitude'=>$model->latitude,
                'longitude'=>$model->longitude,
            ];
        }
        throw new Exception( t("Address not found") );
    }

    public static function defaultAddress($client_id=0,$params=[])
    {
        
        $search_type = isset($params['search_type'])?$params['search_type']:'';
        $city_id = isset($params['city_id'])?intval($params['city_id']):0;
        $area_id = isset($params['area_id'])?intval($params['area_id']):0;
        $state_id = isset($params['state_id'])?intval($params['state_id']):0;

        $criteria=new CDbCriteria();
        $criteria->select = "
        address_uuid,address_label,
        Concat(house_number,' ',street_number,' ',street,' ',area_name,' ',city_name,' ',zip_code,' ',state_name,' ',country_name) as complete_address,
        delivery_instructions,
        state_id,city_id,area_id,zip_code,
        street,street_number,state_name,city_name,area_name,country_name,country_code,location_name,
        delivery_options,delivery_instructions,address_label,country_id
        ";

        $criteria->addColumnCondition(array(
            'client_id' => $client_id,            
        ));
        
        if($state_id>0){
            $criteria->addColumnCondition(array(
                'state_id'=>$state_id  
            ));            
        }        
        if($city_id>0){
            $criteria->addColumnCondition(array(
                'city_id'=>$city_id  
            ));            
        }        
        if($area_id>0){
            $criteria->addColumnCondition(array(
                'area_id'=>$area_id  
            ));            
        }        

        if($model = AR_view_client_address_locations::model()->find($criteria)){
            return [
                'address_uuid'=>$model->address_uuid,                
                'street_name'=>$model->street,
                'street_number'=>$model->street_number,
                'state_name'=>$model->state_name,
                'city_name'=>$model->city_name,
                'area_name'=>$model->area_name,
                'country_name'=>$model->country_name,
                'country_code'=>$model->country_code,
                'location_name'=>$model->location_name,
                'delivery_options'=>$model->delivery_options,
                'delivery_instructions'=>$model->delivery_instructions,
                'address_label'=>$model->address_label,
                'complete_address'=>$model->complete_address,
                'delivery_instructions'=>$model->delivery_instructions,
                'state_id'=>$model->state_id,
                'city_id'=>$model->city_id,
                'area_id'=>$model->area_id,
                'zip_code'=>$model->zip_code,
                'country_id'=>$model->country_id,
            ];
        }        
        throw new Exception( t(HELPER_NO_RESULTS) );        
    }

    public static function getFeatured($params=[], $lang=KMRS_DEFAULT_LANGUAGE)
    {
                                
        $search_type = isset($params['search_type'])?$params['search_type']:'';
        $limit = isset($params['limit'])?$params['limit']:'';
        $offset = isset($params['offset'])?$params['offset']:'';
        $limit = intval($limit);
        $offset = intval($offset);

        $query = isset($params['query'])?$params['query']:'';
        $transaction_type = isset($params['transaction_type'])?$params['transaction_type']:'';
        $featured = isset($params['featured'])?$params['featured']:'';
        $city_id = isset($params['city_id'])?$params['city_id']:'';
        $area_id = isset($params['area_id'])?$params['area_id']:'';
        $state_id = isset($params['state_id'])?$params['state_id']:'';
        $postal_id = isset($params['postal_id'])?$params['postal_id']:'';

        $quick_filters = isset($params['quick_filters'])?$params['quick_filters']:'';
        $today_now = isset($params['today_now'])?$params['today_now']:'';
        $time_now = isset($params['time_now'])?$params['time_now']:'';
        $day_of_week = isset($params['day_of_week'])?$params['day_of_week']:'';
        $date_now = isset($params['date_now'])?$params['date_now']:'';
        $offers_filters = isset($params['offers_filters'])?$params['offers_filters']:'';
        $cuisine = isset($params['cuisine'])?$params['cuisine']:'';
        $today_day_of_week = isset($params['today_day_of_week'])?$params['today_day_of_week']:'';      
        
        $filter_client = '';
        $client_id = $params['client_id'] ?? null;
        if($client_id){
            $filter_client = ",
            (
                select COUNT(DISTINCT(merchant_id))
                from {{favorites}}
                where merchant_id = a.merchant_id
                and client_id=".q($client_id)."
                and fav_type='restaurant'
            ) as saved_store
            ";
        }        
        
        $where = "WHERE 1"; 
        $and = ''; 
        $and_estimate = '';
        $order_by = "b.id ASC";

        if($search_type==1){
            if($city_id>0 && $area_id>0){
                $where = "
                WHERE b.city_id = ".q($city_id)."
                AND b.area_id = ".q($area_id)."
                ";
                $and_estimate = "AND l.city_id = @city_id AND l.area_id = @area_id";
            }            
        } elseif ($search_type==2){
            if($state_id>0 && $city_id>0){
                $where = "
                WHERE b.state_id = ".q($state_id)."
                AND b.city_id = ".q($city_id)."
                ";
                $and_estimate = "AND l.city_id = @city_id AND l.state_id = @state_id";
            }
        } elseif ($search_type==3){
            if(!empty($postal_id)){
                $city_model = AR_city::model()->find("postal_code=:postal_code",[
                    ':postal_code'=>$postal_id
                ]);
                if($city_model){
                    $city_id = $city_model->city_id;
                    $where = "
                    WHERE b.city_id = ".q($city_id)."
                    ";
                    $and_estimate = "AND l.city_id = @city_id";
                }
            }
        }

        if(!empty($transaction_type)){
            $and = "
            AND a.merchant_id IN (
              select merchant_id from {{merchant_meta}}
              where merchant_id = a.merchant_id and meta_name='services'
              and meta_value = @transaction_type
            )
            ";
        }                
        
        if(is_array($featured) && count($featured)>=1){
            $in_featured = CommonUtility::arrayToQueryParameters($featured);
            $and.= "
            AND a.merchant_id IN (
              select merchant_id from {{merchant_meta}}
              where merchant_id = a.merchant_id and meta_name='featured'
              and meta_value  IN (".$in_featured.")
            )
            ";
        }
        if(in_array('ratings_plus4',(array)$quick_filters)){
            $quick_filters = array_diff($quick_filters, ['ratings_plus4']);
            $and.="
            AND rt.ratings > 3
            ";
            $order_by = "                
                rt.ratings DESC,
                rt.review_count DESC
            ";
        }        
        if(is_array($quick_filters) && count($quick_filters)>=1){
            $in_featured = CommonUtility::arrayToQueryParameters($quick_filters);
            $and.= "
            AND a.merchant_id IN (
              select merchant_id from {{merchant_meta}}
              where merchant_id = a.merchant_id and meta_name='featured'
              and meta_value  IN (".$in_featured.")
            )
            ";
        }                
        if(is_array($offers_filters) && count($offers_filters)>=1){        
            $today = strtolower(date("l",strtotime($date_now)));  	
            $dateNow = strtolower(date("Y-m-d",strtotime($date_now)));  	
            if(in_array('accept_vouchers',(array)$offers_filters)){                
                $and.="
                    AND a.merchant_id IN (
                    select merchant_id from {{voucher_new}}            
                    where
                    merchant_id = a.merchant_id
                    AND expiration >= ".q($dateNow)."   
                    AND status in ('publish','published')             
                    AND ".$today."=1
                    AND used_once <> 6
                    AND visible=1
                    )
                ";
            }
            if(in_array('accept_deals',(array)$offers_filters)){
                $and.= "
                AND a.merchant_id IN (
                    select merchant_id from {{offers}}
                    where merchant_id = a.merchant_id
                    and status = 'publish'
                    and ".q($dateNow)." >= valid_from and ".q($dateNow)." <= valid_to
                )
                ";
            }
        }        
        if(is_array($cuisine) && count($cuisine)>=1){ 
            $in_cuisine = CommonUtility::arrayToQueryParameters($cuisine);
            $and.= "
            AND a.merchant_id IN (
               select merchant_id from {{cuisine_merchant}}              
               where merchant_id = a.merchant_id
               and cuisine_id IN (".$in_cuisine.")
            )
            ";
        }

        if(!empty($query)){                   
            $and = "
            AND (
                a.restaurant_name LIKE ".q("%$query%")." 
                OR EXISTS ( 
                    select 1 from {{item}}
                    where 
                    merchant_id = a.merchant_id
                    and item_name LIKE ".q("%$query%")." 
                    and available = 1
                    and status='publish'
                )
                OR EXISTS ( 
                    select 1 from {{view_cuisine_merchant}}        
                    where merchant_id = a.merchant_id
                    and cuisines LIKE ".q("%$query%")."
                )
            )
            ";
        }

        $and.= "
        AND a.status = 'active'
        AND a.is_ready = 2
        ";
        
        CommonUtility::mysqlSetTimezone();
        $set_sql = "        
        SET @transaction_type = ".q($transaction_type).";
        SET @city_id = ".q($city_id).";
        SET @area_id = ".q($area_id).";
        SET @state_id = ".q($state_id).";
        SET @postal_id = ".q($postal_id).";
        SET @lang = ".q($lang).";
        SET @today_now = ".q($today_now).";
        SET @time_now = ".q($time_now).";
        SET @day_of_week = ".q($day_of_week).";
        SET @close_reason = ".q(t('Currently unavailable')).";
        ";
        Yii::app()->db->createCommand($set_sql)->query();	

        $stmt = "        
        SELECT SQL_CALC_FOUND_ROWS
        a.merchant_id,
        a.restaurant_name,
        a.self_delivery,
        a.close_store,
		a.disabled_ordering,
		a.pause_ordering,
        a.is_sponsored,
        cm.cuisines,
        rt.review_count,
        rt.ratings,
        a.restaurant_slug,
        a.logo,a.path,
        a.header_image,a.path2,

        COALESCE( concat(l.estimated_time_min,'-',l.estimated_time_max) , '') AS estimated_time_min,
        
       (
			select count(*) from
			{{opening_hours}}
			where
			merchant_id = a.merchant_id
			and
			day=@today_now
			and
			status = 'open'
			and 			
			(
			  CAST(@time_now AS TIME)
			  BETWEEN CAST(start_time AS TIME) and CAST(end_time AS TIME)								
			)			
		) as merchant_open_status,
             
        CASE 
            WHEN a.pause_ordering = 1 THEN (
                SELECT mm.meta_value
                FROM {{merchant_meta}} mm 
                WHERE mm.merchant_id = a.merchant_id
                AND meta_name='pause_reason'
                LIMIT 0,1
            )
            ELSE @close_reason
        END AS close_reason,

        (
		   select count(*) from {{holidays}}
		   where 
		   merchant_id = a.merchant_id
		   and
		   holiday_date=CURDATE()
		) as holiday_status,

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
        
        ".$filter_client."

        FROM {{merchant}} a
        JOIN {{merchant_location}} b
        ON a.merchant_id = b.merchant_id   

        LEFT JOIN (
          select merchant_id,cuisines from {{view_cuisine_merchant}} where language=@lang
        ) cm 
        on a.merchant_id = cm.merchant_id
        
        LEFT JOIN (
          select merchant_id,review_count,ratings from {{view_ratings}} 
        ) rt 
        on a.merchant_id = rt.merchant_id

        LEFT JOIN {{location_time_estimate}} l 
        ON ( 
          a.self_delivery = 1 AND a.merchant_id = l.merchant_id AND l.service_type=@transaction_type $and_estimate 
        )
        OR ( 
          a.self_delivery = 0 AND l.merchant_id = 0 AND l.service_type=@transaction_type $and_estimate 
        )

        left JOIN (
		   SELECT merchant_id,reason from {{holidays}}
		   where holiday_date=CURDATE()
		) holiday 
		on a.merchant_id = holiday.merchant_id
        
        LEFT JOIN {{opening_hours}} oh 
        ON a.merchant_id = oh.merchant_id 
        AND oh.day_of_week = DAYOFWEEK(NOW()) - 1

        $where        
        $and
        GROUP BY a.merchant_id
        ORDER BY 
        a.close_store,a.disabled_ordering,a.pause_ordering ASC,
        merchant_open_status+0 DESC, a.is_sponsored DESC,
        $order_by        
        limit ".q($offset).",".q($limit)."
        ";           
        try {
            if($res = Yii::app()->db->createCommand($stmt)->queryAll()){              
                $total_records=0;									
                if($resc = Yii::app()->db->createCommand("SELECT FOUND_ROWS() as total_records")->queryRow()){				
                    $total_records=$resc['total_records'];
                }			
                
                $data = []; $merchants = [];
                foreach ($res as $items) {     
                    $merchants[] = $items['merchant_id'];

                    /*next_opening*/	
                    $next_opening = '';                      
                    if($items['current_status']=="closed"){
                        $currentDate = new DateTime();  
                        $next_opening_hours = !empty($items['next_opening_hours'])?explode(';',$items['next_opening_hours']):null;					
                        if(!empty($next_opening_hours)){
                            $start_time = isset($next_opening_hours[0])?$next_opening_hours[0]:'';
                            $end_time = isset($next_opening_hours[1])?$next_opening_hours[1]:'';
                            $day_of_week = isset($next_opening_hours[2])?$next_opening_hours[2]:'';	
                                                    
                            $daysToAdd = ($day_of_week > $today_day_of_week) 
                            ? ($day_of_week - $today_day_of_week) 
                            : (7 - $today_day_of_week + $day_of_week);
                            $openingDate = Date_Formatter::date($currentDate->modify("+$daysToAdd days")->format('Y-m-d'),"E",true);
    
                            $startTime = DateTime::createFromFormat('H:i', $start_time)->format('h:i A');           
                            $endTime = DateTime::createFromFormat('H:i', $end_time)->format('h:i A');
                            $next_opening = t("Opens [day] at [time]",[                            
                                '[day]'=>$openingDate,
                                '[time]'=>"$startTime - $endTime"
                            ]);                        
                        }										
                    }                  
                  

                    $available = true;                    
                    if($items['close_store']==1 || $items['pause_ordering'] == 1 || $items['disabled_ordering'] == 1 || $items['merchant_open_status'] == 0 ){
                        $available = false;
                    }
                    
                    $ratings = isset($items['ratings'])?$items['ratings']:0;
                    $ratings = $ratings>0? number_format($items['ratings'],0) : 0;
				
                    $logo = CMedia::getImage($items['logo'],$items['path'],Yii::app()->params->size_image_medium,CommonUtility::getPlaceholderPhoto('merchant_logo'));
                    $bglogo = !empty($items['header_image'])? CMedia::getImage($items['header_image'],$items['path2'],Yii::app()->params->size_image_medium,CommonUtility::getPlaceholderPhoto('merchant_logo')) :'';

                    $estimated_time =  !empty($items['estimated_time_min']) ? t("{estimated} mins",[
                        '{estimated}'=>$items['estimated_time_min']
                    ]):'';

                    $data[] = [
                        'merchant_id'=>$items['merchant_id'],
                        'restaurant_slug'=>$items['restaurant_slug'],
                        'restaurant_name'=>CommonUtility::safeDecode($items['restaurant_name']),
                        'self_delivery'=>$items['self_delivery']==1?true:false,
                        'cuisines'=>CommonUtility::safeDecode($items['cuisines']),
                        'review_count'=>intval($items['review_count']),
                        'ratings'=>$ratings,
                        'ratings_pretty'=>t("{ratings} Ratings",[
                            '{ratings}'=>$ratings
                        ]),
                        'estimated_time_min'=> $estimated_time,
                        'estimation2'=> $estimated_time,
                        'url'=>Yii::app()->createAbsoluteUrl($items['restaurant_slug']),
                        'logo'=> $logo,
                        'url_logo'=>$logo,
                        'bglogo'=> $bglogo,
                        'url_banner'=>$bglogo,
                        'open_status'=>$items['merchant_open_status']==1?true:false,
                        'next_opening'=>$next_opening,   
                        'available'=>$available,
                        'close_reason'=>$items['close_reason'],
                        'close_store'=>$items['close_store'],
                        'disabled_ordering'=>$items['disabled_ordering'],
                        'pause_ordering'=>$items['pause_ordering'],					
                        'holiday_status'=>$items['holiday_status'],
                        'merchant_open_status'=>$items['merchant_open_status'],
                        'holiday_reason'=>$items['holiday_reason'],		
                        'saved_store'=>$items['saved_store'] ?? null
                    ];
                }                     
                return [
                    'total'=>$total_records,
                    'data'=>$data,
                    'merchants'=>$merchants
                ];
            } else throw new Exception( t(HELPER_NO_RESULTS) );
        } catch (Exception $e) {	                        
            throw new Exception( $e->getMessage() );
        }
    }
    
    public static function checkLocations($merchant_id=0,$params=[])
    {        
        $search_type = isset($params['search_type'])?$params['search_type']:'';        
        $city_id = isset($params['city_id'])?intval($params['city_id']):0;
        $area_id = isset($params['area_id'])?intval($params['area_id']):0;
        $state_id = isset($params['state_id'])?intval($params['state_id']):0;
        $postal_id = isset($params['postal_id'])?intval($params['postal_id']):0;

        $criteria=new CDbCriteria();
        $criteria->select="id,merchant_id";

        $criteria->addColumnCondition(array(
            'merchant_id' => $merchant_id,            
        ));
        
        if($search_type==1){        
            $criteria->addColumnCondition(array(
                'city_id' => $city_id,
                'area_id'=>$area_id
            ));    
        } else if ($search_type==2){
            $criteria->addColumnCondition(array(
                'city_id' => $city_id,
                'state_id'=>$state_id
            ));    
        } else if ($search_type==3){
            $city_model = AR_city::model()->find("postal_code=:postal_code",[
                ':postal_code'=>$postal_id
            ]);
            if($city_model){                
                $criteria->addColumnCondition(array(
                    'city_id' => $city_model->city_id,                    
                ));    
            }
        }        

        if(AR_merchant_location::model()->find($criteria)){
            return true;
        }
        throw new Exception( t("Location not found") );
    }

    public static function LocationEstimation($merchant_id=0,$params=array())
    {
        try {            
            $search_type = isset($params['search_type'])?$params['search_type']:'';
            $city_id = isset($params['city_id'])?$params['city_id']:'';
            $area_id = isset($params['area_id'])?$params['area_id']:'';
            $state_id = isset($params['state_id'])?$params['state_id']:'';
            $postal_id = isset($params['postal_id'])?$params['postal_id']:'';
                    
            $and_sub = ''; $and='';
            if($search_type==1){
                $and_sub  = "AND l.city_id =".$city_id." AND l.area_id= ".q($area_id)." ";
                $and = "            
                AND b.city_id = ".q($city_id)."
                AND b.area_id = ".q($area_id)."
                ";
            } else if ( $search_type==2){
                $and_sub  = "AND l.city_id =".$city_id." AND l.state_id= ".q($state_id)." ";
                $and = "            
                AND b.city_id = ".q($city_id)."
                AND b.state_id = ".q($state_id)."
                ";
            } else if ( $search_type==3){                
                $city_model = AR_city::model()->find("postal_code=:postal_code",[
                    ':postal_code'=>$postal_id
                ]);
                if($city_model){
                    $city_id = $city_model->city_id;
                    $and_sub  = "AND l.city_id =".$city_id."";
                    $and = "            
                    AND b.city_id = ".q($city_id)."                    
                    ";
                }
            }

            $stmt = "
            SELECT 
            a.merchant_id,
            a.restaurant_name,
            a.self_delivery,
            l.service_type,
            COALESCE( concat(l.estimated_time_min,'-',l.estimated_time_max) , '') AS estimated_time_min
            
            FROM {{merchant}} a      
            JOIN {{merchant_location}} b
            ON a.merchant_id = b.merchant_id   

            LEFT JOIN {{location_time_estimate}} l 
            ON ( 
              a.self_delivery = 1 AND a.merchant_id = l.merchant_id $and_sub
            )
            OR ( 
              a.self_delivery = 0 AND l.merchant_id = 0 $and_sub
            )    

            WHERE a.merchant_id=".q($merchant_id)."
            $and
            ";            
            if($res = Yii::app()->db->createCommand($stmt)->queryAll()){ 
                $data = [];
                foreach ($res as $items) {
                    $data[$items['service_type']] = !empty($items['estimated_time_min']) ? t("{estimated} mins",[
                        '{estimated}'=>$items['estimated_time_min']
                    ]):'';
                }                
                return $data;
            } else throw new Exception( t(HELPER_NO_RESULTS) );
        } catch (Exception $e) {	            
            throw new Exception( $e->getMessage() );
        }
    }

    public static function getLocationFee($merchant_id=0,$params=array())
    {
        try {

            $search_type = isset($params['search_type'])?$params['search_type']:'';
            $city_id = isset($params['city_id'])? intval($params['city_id']) :0;
            $area_id = isset($params['area_id'])? intval($params['area_id']) :0;
            $state_id = isset($params['state_id'])? intval($params['state_id']) :0;
            
            $and_sub = ''; $and='';
          
            if($state_id>0){
                $and.= " AND b.state_id = ".q($state_id)."";
                $and_sub.= " AND l.state_id = ".q($state_id)."";
            }
            if($city_id>0){
                $and.= " AND b.city_id = ".q($city_id)."";
                $and_sub.= " AND l.city_id = ".q($city_id)."";
            }
            if($area_id>0){
                $and.= " AND b.area_id = ".q($area_id)."";
                $and_sub.= " AND l.area_id = ".q($area_id)."";
            }
            

            $stmt = "
            SELECT 
            a.merchant_id,
            a.restaurant_name,
            a.self_delivery,
            l.rate_id,
            l.fee,
            l.minimum_order,
            l.maximum_amount,
            l.free_above_subtotal
            
            FROM {{merchant}} a      
            JOIN {{merchant_location}} b
            ON a.merchant_id = b.merchant_id   

            LEFT JOIN {{location_rate}} l 
            ON ( 
              a.self_delivery = 1 AND a.merchant_id = l.merchant_id $and_sub
            )
            OR ( 
              a.self_delivery = 0 AND l.merchant_id = 0 $and_sub
            )    

            WHERE a.merchant_id=".q($merchant_id)."
            $and
            ";                          
            if($res = Yii::app()->db->createCommand($stmt)->queryRow()){                 
                return [
                    'fee'=>$res['fee'],
                    'minimum_order'=>$res['minimum_order'],
                    'maximum_amount'=>$res['maximum_amount'],
                    'free_above_subtotal'=>$res['free_above_subtotal'],
                ];
            } else throw new Exception( t("This restaurant cannot deliver to your location.") );
        } catch (Exception $e) {	            
            throw new Exception( $e->getMessage() );
        }
    }

    public static function getVoucherList($merchant_ids=[],$date_now='')
    {
        try {
            
            $merchants = CommonUtility::arrayToQueryParameters($merchant_ids);            
            $today = strtolower(date("l",strtotime($date_now)));  	

            $like_clauses = array_map(function($id) {
                return "joining_merchant LIKE '%\"" . intval($id) . "\"%'";
            }, $merchant_ids);                        

            $stmt = "
                SELECT 
                voucher_id as promo_id,
                voucher_name,
                voucher_type as promo_type,
                amount,
                expiration,
                min_order,
                merchant_id
                FROM {{voucher_new}}                
                WHERE 
                expiration >= ".q($date_now)."   
                AND status in ('publish','published')             
                AND ".$today."=1
                AND used_once <> 6
                AND visible=1                      
                AND ( merchant_id IN ($merchants) OR " . implode(' OR ', $like_clauses) . " )
            ";            
            if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
                $exchange_rate = self::getExchangeRate();
                $data = [];
                foreach ($res as $items) {

                    $discount = '';
                    $pretty_expiration = Date_Formatter::date( $items['expiration'] );
					$pretty_amount = Price_Formatter::formatNumber( ($items['amount']*$exchange_rate) );
					$pretty_min_order = Price_Formatter::formatNumber( ($items['min_order']*$exchange_rate) );

                    $use_until = t("Use until {{date}}",array(
                        '{{date}}'=>$pretty_expiration
                    ));

                    if($items['promo_type']=="percentage"){
                        $discount = Price_Formatter::convertToRaw( ($items['amount']) ,0)."%";
						$name = t("({{coupon_name}}) {{amount}}% off",array(
						 '{{amount}}'=>Price_Formatter::convertToRaw( ($items['amount']) ,0),
						 '{{coupon_name}}'=>$items['voucher_name'],
						));
					} else {
                        $discount = $pretty_amount;
						$name = t("({{coupon_name}}) {{amount}} off",array(
						 '{{amount}}'=>$pretty_amount,
						 '{{coupon_name}}'=>$items['voucher_name'],
						));
					}

                    $min_spend = '';
                    if($items['min_order']>0){
						$min_spend = t("Min. spend {{amount}}",array(
						  '{{amount}}'=>$pretty_min_order
						));
					}
                    
                    $data[$items['merchant_id']][] = array(
                        'promo_type'=>'voucher',
                        'promo_id'=>$items['promo_id'],
                        'title'=>$name,		
                        'discount'=>t('{discount} off',[
                            '{discount}'=>$discount
                        ]),
                        'sub_title'=>$min_spend,
                        'valid_to'=>$use_until,					  
                    );
                }                
                return $data;
            } else throw new Exception( t(HELPER_NO_RESULTS) );
        } catch (Exception $e) {	            
            throw new Exception( $e->getMessage() );
        }
    }

    public static function getPromoList($merchant_ids=[],$date_now='')
    {
         try {
         $merchants = CommonUtility::arrayToQueryParameters($merchant_ids); 
            $stmt = "
            SELECT             
            *
            FROM {{offers}}
            WHERE merchant_id  IN ($merchants)
            AND status in ('publish','published')
            AND ".q($date_now)." >= valid_from and ".q($date_now)." <= valid_to
            ";            
            if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
                $data = [];
                foreach ($res as $items) {           
                    $discount = '';         
                    $exchange_rate = self::getExchangeRate();
                    $transaction_type = json_decode($items['applicable_to'],true);                    
                    $name = t("{{amount}}% off over {{order_over}} on {{transaction}}",array(
                        '{{amount}}'=>Price_Formatter::convertToRaw( ($items['offer_percentage']),0),
                        '{{order_over}}'=>Price_Formatter::formatNumber( ($items['offer_price']*$exchange_rate) ),
                        '{{transaction}}'=>CommonUtility::arrayToString($transaction_type)
                    ));
                    $discount = Price_Formatter::convertToRaw( ($items['offer_percentage']),0)."%";
                    $valid_to = t("valid {{from}} to {{to}}",array(
                        '{{from}}'=> Date_Formatter::date($items['valid_from']),
                        '{{to}}'=> Date_Formatter::date($items['valid_to']),
                    ));
                    $data[$items['merchant_id']][] = array(                        
                        'promo_type'=>'offers',
                        'promo_id'=>$items['offers_id'],
                        'title'=>$name,		
                        'discount'=>t('{discount} off',[
                            '{discount}'=>$discount
                        ]),
                        'sub_title'=>'',
                        'valid_to'=>$valid_to,					  
                    );
                }
                return $data;
            } else throw new Exception( t(HELPER_NO_RESULTS) );
        } catch (Exception $e) {	            
            throw new Exception( $e->getMessage() );
        }
    }

    public static function searchSuggestion($search='')
    {
        try {
            $stmt = "		
            (
                SELECT 
                'merchant' as type,
                restaurant_name as name
                FROM {{merchant}}
                WHERE restaurant_name LIKE ".q("%$search%")."
                AND status='active'
                AND is_ready = 2
                LIMIT 0,10		
            )

            UNION ALL

            (
                SELECT 
                'cuisine' as type,
                cuisine_name as name
                FROM {{cuisine}}
                WHERE cuisine_name LIKE ".q("%$search%")."
                AND status='publish'            
                LIMIT 0,10		
            )
            ";			            
            if($res = Yii::app()->db->createCommand($stmt)->queryAll()){			                
                return $res;
            }
		    throw new Exception( "No results" );
        } catch (Exception $e) {	            
            throw new Exception( $e->getMessage() );
        }
    }

    public static function getLocations($search_type='', $city_id='',$area_id='', $state_id='',$postal_id='')
    {     
        try {
            $stmt = '';            
            if($search_type==1){
                $stmt = "
                SELECT 
                lc.name as city_name,
                la.name as area_name,
                concat(lc.name,' ',la.name) as complete_address,
                state.name as state_name,
                country.country_name as country_name,
                state.state_id

                FROM {{location_cities}} lc        

                JOIN {{location_area}} la
                ON lc.city_id = la.city_id 

                JOIN {{location_states}} state
                ON lc.state_id = state.state_id 
                
                JOIN {{location_countries}} country
                ON state.country_id = country.country_id 

                WHERE
                lc.city_id = ".q($city_id)."
                AND la.area_id = ".q($area_id)."
                ";            
            } else if($search_type==2) {
                $stmt = "
                SELECT 
                lc.name as city_name,
                ls.name as state_name,
                concat(lc.name,' ',ls.name) as complete_address,
                country.country_name as country_name

                FROM {{location_states}} ls        

                JOIN {{location_cities}} lc
                ON ls.state_id = lc.state_id 

                JOIN {{location_countries}} country
                ON ls.country_id = country.country_id 

                WHERE
                ls.state_id = ".q($state_id)."
                AND lc.city_id = ".q($city_id)."
                ";            
            } else if($search_type==3) {                
                $stmt = "
                SELECT 
                city.name as city_name,
                concat(city.name,' ',city.postal_code) as complete_address,
                state.name as state_name,
                country.country_name as country_name,
                city.city_id,
                state.state_id                

                FROM {{location_cities}} city

                JOIN {{location_states}} state
                ON city.state_id = state.state_id

                JOIN {{location_countries}} country
                ON state.country_id = country.country_id 

                WHERE
                city.postal_code = ".q($postal_id)."
                ";                                
            }                 
            if(!empty($stmt)){
                if($res = Yii::app()->db->createCommand($stmt)->queryRow()){                
                    return $res;
                }                 
            }            
            throw new Exception( "No results" );
        } catch (Exception $e) {	            
            throw new Exception( $e->getMessage() );
        }
    }

    public static function getbanner($merchant_id=0,$owner='merchant',$filters=[])
    {
         $state_id = isset($filters['state_id'])?$filters['state_id']:0;
         $city_id = isset($filters['city_id'])?$filters['city_id']:0;         
         $area_id = isset($filters['area_id'])?$filters['area_id']:0;
         $postal_id = isset($filters['postal_id'])?$filters['postal_id']:0;         

         $and = '';
         $and.= $state_id>0? "AND state_id=".q($state_id)." " :'';
         $and.= "\n";
         $and.= $city_id>0? "AND city_id=".q($city_id)."" :'';
         $and.= "\n";
         $and.= $area_id>0? "AND area_id=".q($area_id)."" :'';
         $and.= "\n";
         //$and.= $postal_id>0? "AND postal_id=".q($postal_id)."" :'';

         if($merchant_id>0){
            $and = " AND meta_value1=".q($merchant_id)." ";
         }

         $stmt = "
         SELECT * FROM
         {{banner}}         
         WHERE ( owner=".q($owner)." AND status=1 $and )
         OR ( owner=".q($owner)." AND status=1  AND state_id=0 AND city_id=0 AND area_id=0 )
         ";                 
         if($res = Yii::app()->db->createCommand($stmt)->queryAll()){            
            $data = [];
			foreach ($res as $items) {
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

}
// end class