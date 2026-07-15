<?php
class CDriver
{
    public static $on_demand_availability = false;

    public static function getDriver($driver_id)
    {
        $dependency = CCacheData::dependency();         
        $model = AR_driver::model()->cache(Yii::app()->params->cache, $dependency)->findByPk($driver_id);
        if($model){
            return $model;
        }
        throw new Exception( t("Driver Id not found") );        
    }

    public static function getDriverInfo($driver_id)
    {
        $dependency = CCacheData::dependency();         
        $model = AR_driver::model()->cache(Yii::app()->params->cache, $dependency)->findByPk($driver_id);
        if($model){
            return [
                'driver_id'=>$model->driver_id,
                'driver_uuid'=>$model->driver_uuid,
                'first_name'=>$model->first_name,
                'last_name'=>$model->last_name,
                'full_name'=>"$model->first_name $model->last_name",
                'email'=>$model->email,
                'phone'=>$model->phone_prefix.$model->phone,
                'photo'=>CMedia::getImage($model->photo,$model->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('driver')),
                'latitude'=>$model->latitude,
                'lontitude'=>$model->lontitude,
            ];
        }
        throw new Exception( t("Driver Id not found") );        
    }

    public static function getDriverInfoWithVehicle($driver_id)
    {
        $stmt = "
        SELECT 
        a.driver_id,
        a.driver_uuid,
        a.first_name,
        a.last_name,
        concat(a.first_name,' ',a.last_name) as full_name,
        a.email,
        concat(a.phone_prefix,'',a.phone) as phone,
        a.latitude,
        a.lontitude,
        a.photo,a.path,
        b.plate_number,        
        c.meta_value as car_maker,                
        FORMAT(COALESCE(AVG(rating.rating), 0), 1) AS average_rating 
        
        FROM {{driver}} a
        LEFT JOIN {{driver_vehicle}} b
        ON
        a.driver_id = b.driver_id

        LEFT JOIN {{admin_meta}} c
        ON
        b.maker = c.meta_id

        LEFT JOIN {{driver_reviews}} rating
        ON
        a.driver_id = rating.driver_id

        WHERE a.driver_id=".q($driver_id)."
        ";            
        if($res = Yii::app()->db->createCommand($stmt)->queryRow()){
            if($res['driver_id']>0){
                $res['photo'] =  CMedia::getImage($res['photo'],$res['path'],'@thumbnail',CommonUtility::getPlaceholderPhoto('driver'));
                $res['chat_url'] = Yii::app()->createAbsoluteUrl("/chat");
                return $res;
            }            
        }
        throw new Exception( t("Driver Id not found") );        
    }

    public static function getDriverByUUID($driver_uuid)
    {
        $dependency = CCacheData::dependency(); 
        $model = AR_driver::model()->cache(Yii::app()->params->cache, $dependency)->find("driver_uuid=:driver_uuid",array(
            ':driver_uuid'=>$driver_uuid
        ));
        if($model){
            return $model;
        }
        throw new Exception( t(HELPER_RECORD_NOT_FOUND) );        
    }

    public static function getVehicle($vehicle_id)
    {
        $model = AR_driver_vehicle::model()->findByPk($vehicle_id);
        if($model){
            return $model;
        }
        throw new Exception( t("Vehicle Id not found") );        
    }

    public static function SummaryCountOrderTotal($driver_id=0,$transaction_type='delivery')
    {
        $draft_status = AttributesTools::initialStatus();
        $criteria=new CDbCriteria();
		$criteria->select ="count(*) as total";
        $criteria->condition = "driver_id=:driver_id AND service_code=:service_code";
        $criteria->params = array(
            ':driver_id'=>$driver_id,                 
            ':service_code'=>$transaction_type
        );			
        $criteria->addNotInCondition('status',[$draft_status]);        
        $dependency = CCacheData::dependency();				
        $model = AR_ordernew::model()->cache(Yii::app()->params->cache, $dependency)->find($criteria);
        $total = isset($model->total)?$model->total:0;
        return intval($total);
    }

    public static function CountOrderStatus($driver_id=0,$status='',$transaction_type='delivery')
    {
        $criteria=new CDbCriteria();
		$criteria->select ="count(*) as total";
        $criteria->condition = "driver_id=:driver_id AND delivery_status=:delivery_status AND service_code=:service_code";
        $criteria->params = array(
            ':driver_id'=>$driver_id,     
            ':delivery_status'=>$status,
            ':service_code'=>$transaction_type
        );			        
        $dependency = CCacheData::dependency();				
        $model = AR_ordernew::model()->cache(Yii::app()->params->cache, $dependency)->find($criteria);
        $total = isset($model->total)?$model->total:0;
        return intval($total);
    }
    
    public static function TotaLTips($driver_id=0,$status_array='',$transaction_type='delivery')
    {
        $criteria=new CDbCriteria();
		$criteria->select ="sum(courier_tip) as total";
        $criteria->condition = "driver_id=:driver_id AND service_code=:service_code";
        $criteria->params = array(
            ':driver_id'=>$driver_id,
            ':service_code'=>$transaction_type
        );			             
        $criteria->addInCondition('delivery_status',(array)$status_array);        
        $dependency = CCacheData::dependency();				        
        $model = AR_ordernew::model()->cache(Yii::app()->params->cache, $dependency)->find($criteria);        
        $total = isset($model->total)?$model->total:0;
        return intval($total);
    }

    public static function SummaryTotaLTips($driver_id=0,$transaction_type='delivery')
    {
        $draft_status = AttributesTools::initialStatus();

        $criteria=new CDbCriteria();
		$criteria->select ="sum(courier_tip) as total";
        $criteria->condition = "driver_id=:driver_id AND service_code=:service_code";
        $criteria->params = array(
            ':driver_id'=>$driver_id,                 
            ':service_code'=>$transaction_type
        );			
        $criteria->addNotInCondition('status',[$draft_status]);        
        $dependency = CCacheData::dependency();				
        $model = AR_ordernew::model()->cache(Yii::app()->params->cache, $dependency)->find($criteria);
        $total = isset($model->total)?$model->total:0;
        return intval($total);
    }

    public static function GetGroups($group_id=0)
    {
        $groups = [];
        $model = AR_driver_group_relations::model()->findAll("group_id=:group_id",[
            ':group_id'=>intval($group_id)
        ]);
        if($model){
            foreach ($model as $item) {
                $groups[] = intval($item->driver_id);
            }            
            return $groups;
        }
        return $groups;
    }

    public static function getDriverTabs($date_start='',$date_end='',$status='', $q='',$merchant_owner_id=0)
    {        
                
        $assigned_group = AOrders::getOrderTabsStatus('assigned');
                
        $params=array();
        if(is_array($assigned_group) && count($assigned_group)>=1){
            foreach($assigned_group as $value){
               $params[] = q($value);
            }               
        } else {
            throw new Exception(HELPER_NO_RESULTS);     
        }

        $in_condition = '';
        if(!empty($q)){
            $in_condition = "
            and driver_id = (
                select driver_id from {{driver}}
                where status='active'   
                and ( first_name LIKE ".q("$q%")."   or  last_name LIKE ".q("$q%")."   )                
            )
            ";
        }

        $on_demand_availability = self::getOndemand();        

        $stmt = '';         
        if($status=="duty"){            
            if($on_demand_availability){
                $stmt = "
                SELECT a.* FROM {{driver_schedule}} a
                WHERE active=1 AND on_demand = 1
                AND merchant_id=".q($merchant_owner_id)."
                AND driver_id NOT IN (
                    select driver_id from {{ordernew}}
                    where driver_id = a.driver_id
                    and delivery_date = ".q($date_start)."
                    and delivery_status in (".implode(', ',$params).")      
                )  
                AND driver_id IN (
                    select driver_id from {{driver}}
                    where merchant_id=".q($merchant_owner_id)."
                    and is_online = 1
                )
                ".$in_condition."
                ";
            } else {
                $stmt = '
                SELECT a.* FROM {{driver_schedule}} a
                WHERE active=1 
                AND DATE(time_start) BETWEEN '.q($date_end).' AND '.q($date_end).'  
                AND DATE(shift_time_started) IS NOT NULL  
                AND DATE(shift_time_ended) IS NULL  
                AND driver_id NOT IN (
                    select driver_id from {{ordernew}}
                    where driver_id = a.driver_id
                    and delivery_date = '.q($date_start).'
                    and delivery_status in ('.implode(', ',$params).')                
                )  
                '.$in_condition.'
                ';            
            }            
        } else {
            if($on_demand_availability){
                $stmt = "
                SELECT a.* FROM {{driver_schedule}} a
                WHERE active=1 AND on_demand = 1
                AND merchant_id=".q($merchant_owner_id)."
                AND driver_id IN (
                    select driver_id from {{ordernew}}
                    where driver_id = a.driver_id
                    and delivery_date = ".q($date_start)."
                    and delivery_status in (".implode(', ',$params).")      
                )  
                AND driver_id IN (
                    select driver_id from {{driver}}
                    where merchant_id=".q($merchant_owner_id)."
                    and is_online = 1
                )
                ".$in_condition."
                ";
            } else {
                $stmt = '
                SELECT a.* FROM {{driver_schedule}} a
                WHERE active=1 
                AND DATE(time_start) BETWEEN '.q($date_end).' AND '.q($date_end).'
                AND DATE(shift_time_started) IS NOT NULL  
                AND DATE(shift_time_ended) IS NULL  
                AND driver_id IN (
                    select driver_id from {{ordernew}}
                    where driver_id = a.driver_id
                    and delivery_date = '.q($date_start).'
                    and delivery_status in ('.implode(', ',$params).')
                    '.$in_condition.'
                )
                ';
            }
        }                        
                
        $model = AR_driver_schedule::model()->findAllBySql($stmt);    
        // dump($stmt);
        // dump($model);
        // die();
        if($model){
            $data = []; $drivers = [];
            foreach ($model as $items) {
                $drivers[] = $items->driver_id;
                $data[$items->driver_id] = [
                    'driver_id'=>$items->driver_id,
                    'vehicle_id'=>$items->vehicle_id,
                    'zone_id'=>$items->zone_id,
                    'date_start'=>Date_Formatter::date($items->time_start),
                    'time_start'=>Date_Formatter::Time($items->time_start),
                    'time_end'=>Date_Formatter::Time($items->time_end),
                    'instructions'=>$items->instructions,
                    'shift_time_started'=> $items->shift_time_started<>null? Date_Formatter::Time($items->shift_time_started) :'',
                ];
            }
            return [
                'drivers'=>$drivers,
                'list'=>$data
            ];
        }
        throw new Exception(HELPER_NO_RESULTS);        
    }

    public static function getDriverSched($date_start='',$date_end='',$drivers=array())
    {
        $criteria=new CDbCriteria();
        $criteria->addCondition('active=1');		
        $criteria->addBetweenCondition('DATE(time_start)',$date_start,$date_end);        
        if(is_array($drivers) && count($drivers)>=1){
            $criteria->addInCondition('driver_id',(array)$drivers);
        }        
        $model = AR_driver_schedule::model()->findAll($criteria);
        if($model){
            $data = []; $drivers = [];
            foreach ($model as $items) {
                $drivers[] = $items->driver_id;
                $data[$items->driver_id] = [
                    'driver_id'=>$items->driver_id,
                    'vehicle_id'=>$items->vehicle_id,                    
                    'time_start'=>$items->time_start,
                    'time_end'=>$items->time_end,
                    'instructions'=>$items->instructions
                ];
            }
            return [
                'drivers'=>$drivers,
                'list'=>$data
            ];
        }
        throw new Exception(HELPER_NO_RESULTS);        
    }
    

    public static function getDriverSchedule($driver_id=0,$date_start='',$date_end='', $now='')
    {
        $criteria=new CDbCriteria();
        $criteria->addCondition('driver_id = :driver_id AND shift_time_ended IS NULL  AND active=1');
        $criteria->params = [
            ':driver_id'=>intval($driver_id),            
        ];	
        $criteria->order = "time_start ASC";
        $criteria->addBetweenCondition('DATE(time_start)',$date_start,$date_end);          
        $model = AR_driver_schedule::model()->findAll($criteria);     
        if($model){
            $data = []; $vehicles = [];

            $zone_list = CommonUtility::getDataToDropDown("{{zones}}",'zone_id','zone_name',"
		    WHERE merchant_id = 0","ORDER BY zone_name ASC"); 			

            foreach ($model as $items) {        
                $vehicles[] = $items->vehicle_id;        

                $shift_end = "$items->time_end";
			    $shift_end = date("Y-m-d g:i:s a",strtotime($shift_end));
                                
                $delivery_startime = "$items->time_start";
			    $delivery_startime = date("Y-m-d g:i:s a",strtotime($delivery_startime));                
                $data[] = [
                    'schedule_id'=>$items->schedule_id,
                    'schedule_uuid'=>$items->schedule_uuid,
                    'driver_id'=>$items->driver_id,
                    'vehicle_id'=>$items->vehicle_id,
                    'zone_id'=>$items->zone_id,
                    'zone_name'=>isset($zone_list[$items->zone_id])?$zone_list[$items->zone_id]:$items->zone_id,
                    'date_start'=>Date_Formatter::date($items->time_start,"yyyy-MM-dd",true),
                    'date_start_split'=>[
                        'day'=>Date_Formatter::date($items->time_start,"dd",true),
                        'day_words'=>Date_Formatter::date($items->time_start,"EEE",true),
                        'month'=>Date_Formatter::date($items->time_start,"MMM",true),
                        'year'=>Date_Formatter::date($items->time_start,"yyyy",true),
                        'date_string'=>t(Date_Formatter::getRangeDateString($items->time_start))
                    ],
                    'time_start_raw'=>$items->time_start,
                    'time_end_raw'=>$items->time_end,
                    'time_start'=>Date_Formatter::time($items->time_start),
                    'time_end'=>Date_Formatter::time($items->time_end),
                    'shift_start'=>date("c",strtotime($delivery_startime)),
                    'shift_end'=>date("c",strtotime($shift_end)),                    
                    'shift_time_started'=>$items->shift_time_started,
                    'shift_time_ended'=>$items->shift_time_ended,
                    'instructions'=>$items->instructions,                                                     
                    'date_now'=>date("c",strtotime($now)),                    
                ];
            }
            return [         
                'vehicles'=>$vehicles,
                'list'=>$data
            ];
        }
        throw new Exception(HELPER_NO_RESULTS);        
    }

    public static function getDriverScheduleToday($driver_id=0,$date_start='',$date_end='', $now='')
    {
        $criteria=new CDbCriteria();
        $criteria->addCondition('driver_id = :driver_id AND shift_time_ended IS NULL  AND active=1');
        $criteria->params = [
            ':driver_id'=>intval($driver_id),            
        ];	
        $criteria->order = "time_start ASC";
        $criteria->addBetweenCondition('DATE(time_start)',$date_start,$date_end);          
        $model = AR_driver_schedule::model()->find($criteria);     
        if($model){
            return [
                'time_start'=>$model->time_start,
                'time_end'=>$model->time_end,
                'shift_time_started'=>$model->shift_time_started,
                'shift_time_ended'=>$model->shift_time_ended,
            ];
        }
        throw new Exception(HELPER_NO_RESULTS);        
    }

    public static function getDriverListByIDS($drivers=array())
    {
        $on_demand_availability = self::getOndemand();
        
        $criteria=new CDbCriteria();
        $criteria->addCondition('status=:status');		
        $criteria->params = [
            ':status'=>"active"            
        ];
        $criteria->addInCondition('driver_id',(array)$drivers);
        $criteria->order = "last_seen,first_name ASC";
        $model = AR_driver::model()->findAll($criteria);
        if($model){
            $data = []; $drivers = [];
            foreach ($model as $items) {
                $photo = CMedia::getImage($items->photo,$items->path,'@thumbnail',
		         CommonUtility::getPlaceholderPhoto('customer'));

                $data[$items->driver_id] = [
                    'driver_id'=>$items->driver_id,
                    'driver_uuid'=>$items->driver_uuid,
                    'last_seen'=>$items->last_seen<>null? Date_Formatter::dateTime($items->last_seen) :'',
                    'last_seen_human'=>!empty($items->last_seen)? PrettyDateTime::parse(new DateTime($items->last_seen)):'',
                    'initial'=> !empty($items->first_name) ? strtoupper(substr($items->first_name,0,1)) : "N",
                    'first_name'=>$items->first_name,
                    'last_name'=>$items->last_name,
                    'phone_prefix'=>$items->phone_prefix,
                    'phone'=>$items->phone,
                    'color_hex'=>$items->color_hex,
                    'photo'=>$items->photo,
                    'photo_url'=>$photo,
                    'online_status'=>$on_demand_availability?$items->is_online:3,
                    'is_online'=>$items->is_online,
                    'latitude'=>$items->latitude,
                    'lontitude'=>$items->lontitude,
                ];
            }            
            return $data;
        }
        throw new Exception(HELPER_NO_RESULTS);  
    }

    //https://learnsql.com/cookbook/how-to-calculate-the-difference-between-two-timestamps-in-mysql/
    //https://database.guide/mysql-timediff-vs-timestampdiff-whats-the-difference/
    //https://stackoverflow.com/questions/17832906/how-to-check-if-field-is-null-or-empty-in-mysql
    public static function getOrdersByStatus($status=[],$delivery_date='', $q='', $transaction_type='delivery')
    {
        
        CommonUtility::mysqlSetTimezone();
        
        $draft_status = AttributesTools::initialStatus();
        $unassigned_group = AOrders::getOrderTabsStatus('unassigned');
        $options = OptionsTools::find([
            'driver_enabled_alert','driver_alert_time'
        ]);
        $driver_enabled_alert = isset($options['driver_enabled_alert'])?$options['driver_enabled_alert']:'';
        $driver_alert_time = isset($options['driver_alert_time'])?$options['driver_alert_time']:'';                

        $criteria=new CDbCriteria();
        $criteria->alias = "a";
        $criteria->select = "
        a.*,        
        TIMESTAMPDIFF(DAY, concat(a.delivery_date,' ',a.delivery_time), now() ) AS diff_days, 
        TIMESTAMPDIFF(HOUR, concat(a.delivery_date,' ',a.delivery_time), now()  ) AS diff_hours, 
        TIMESTAMPDIFF(MINUTE, concat(a.delivery_date,' ',a.delivery_time), now()  ) AS diff_minutes,               

        TIMESTAMPDIFF(DAY, a.date_created, now() ) AS diff_days1, 
        TIMESTAMPDIFF(HOUR, a.date_created, now() ) AS diff_hours1, 
        TIMESTAMPDIFF(MINUTE, a.date_created, now() ) AS diff_minutes1,                
        (            
            select meta_value 
            from {{ordernew_meta}}
            where
            order_id=a.order_id
            and
            meta_name = 'customer_name'
        ) as customer_name
        ";
        $criteria->addCondition("delivery_date=:delivery_date AND service_code=:service_code 
        AND merchant_id NOT IN (
            select merchant_id from {{option}}
            where merchant_id=a.merchant_id and option_name='self_delivery'
            and option_value=1
        )
        ");
        $criteria->params = [
            ':delivery_date'=>$delivery_date,
            ':service_code'=>$transaction_type
        ];
        $criteria->addInCondition("delivery_status",(array)$status);
        $criteria->addNotInCondition('status',[$draft_status]);        
        if(!empty($q)){
            $criteria->addSearchCondition("a.order_id",$q);
        }
        $criteria->order = "order_id DESC";
        $criteria->limit=100;               
        $count = AR_ordernew::model()->count($criteria);           
        $model = AR_ordernew::model()->findAll($criteria);          
        if($model){        
            $data = []; $merchant = []; $drivers = []; $order_ids = [];
            foreach ($model as $items){                
                
                $is_delayed = false; $days = 0; $hours = 0; $mins = 0;

                if($items->delivery_time=='' && $driver_enabled_alert==1){
                    $days = $items->diff_days1;
                    $hours = $items->diff_hours1;
                    $mins = $items->diff_minutes1;                    
                } else if ( $items->delivery_time <> '' && $driver_enabled_alert==1 ){
                    $days = $items->diff_days;
                    $hours = $items->diff_hours;
                    $mins = $items->diff_minutes;                    
                }

                
                if(in_array($items->delivery_status,$unassigned_group) && $driver_enabled_alert ){
                    if($days>0){
                        $is_delayed = true;
                    } else if ( $hours>0){
                        $is_delayed = true;
                    } else if ( $mins>$driver_alert_time ){
                        $is_delayed = true;
                    }
                }

                $merchant[$items->merchant_id] = $items->merchant_id;
                if($items->driver_id>0){
                    $drivers[$items->driver_id] = $items->driver_id;
                }                

                $order_ids[$items->order_id] = $items->order_id;

                $data[]=[
                    'order_id'=>$items->order_id,
                    'order_uuid'=>$items->order_uuid,
                    'merchant_id'=>$items->merchant_id,
                    'customer_name'=>$items->customer_name,
                    'driver_id'=>$items->driver_id,
                    'vehicle_id'=>$items->vehicle_id,
                    'status'=>$items->status,
                    'delivery_status'=>$items->delivery_status,
                    'payment_status'=>t($items->payment_status),
                    'payment_status_raw'=>$items->payment_status,
                    'total'=>Price_Formatter::formatNumber($items->total),                    
                    'delivery_date'=>Date_Formatter::date($items->delivery_date),
                    'delivery_time'=>!empty($items->delivery_time)? Date_Formatter::Time($items->delivery_time,'h:mm a',true) : t("Asap") , 
                    'date_created'=>Date_Formatter::Time($items->date_created,'h:mm a',true),
                    'formatted_address'=>CHtml::encode($items->formatted_address),
                    'is_delayed'=>$is_delayed
                ];
            }
            return [
                'total'=>$count,
                'data'=>$data,
                'merchant'=>$merchant,
                'drivers'=>$drivers,
                'order_ids'=>$order_ids
            ];
        }
        throw new Exception(HELPER_NO_RESULTS);  
    }    

    public static function getCoordinatesByOrderID($order_ids='')
    {
        $criteria=new CDbCriteria();
        $criteria->addInCondition("order_id",$order_ids);
        $criteria->addInCondition("meta_name",array('longitude','latitude'));        
        if($model = AR_ordernew_meta::model()->findAll($criteria)){
            $data = [];
            foreach ($model as $items) {                          
                $data[$items->order_id][$items->meta_name] = $items->meta_value;
            }
            return $data;
        }
        throw new Exception(HELPER_NO_RESULTS);  
    }

    public static function getOrdersByStatusCount($status=[],$delivery_date='',$q='',$transaction_type='delivery')
    {
        $draft_status = AttributesTools::initialStatus();
        $criteria=new CDbCriteria();
        $criteria->alias = "a";
      
        $criteria->addCondition("delivery_date=:delivery_date AND service_code=:service_code
        AND merchant_id NOT IN (
            select merchant_id from {{option}}
            where merchant_id=a.merchant_id and option_name='self_delivery'
            and option_value=1
        )
        ");
        $criteria->params = [
            ':delivery_date'=>$delivery_date,
            ':service_code'=>$transaction_type
        ];
        $criteria->addInCondition("delivery_status",(array)$status);          
        $criteria->addNotInCondition('status',[$draft_status]); 
        if(!empty($q)){
            $criteria->addSearchCondition("order_id",$q);
        }                   
        $count = AR_ordernew::model()->count($criteria);         
        return intval($count);
    }    

    public static function getOrdersByDriverID($diver_id=0, $delivery_date='',$transaction_type='delivery',$exlude_status=array())
    {
        $criteria=new CDbCriteria();
        $criteria->alias = "a";
        $criteria->select = "
        a.*, 
        (            
            select meta_value 
            from {{ordernew_meta}}
            where
            order_id=a.order_id
            and
            meta_name = 'customer_name'
            limit 0,1
        ) as customer_name,
        (
            select concat(avatar,'|',path,'|',client_uuid,'|',first_name,'|',last_name)
            from {{client}}
            where 
            client_id=a.client_id
            limit 0,1
        ) as meta
        ";        
        $criteria->addCondition("driver_id=:driver_id AND service_code=:service_code");
        $criteria->params = [
            ':driver_id'=>intval($diver_id),            
            ':service_code'=>$transaction_type,
        ];        
        if(is_array($exlude_status) && count($exlude_status)>=1){
            $criteria->addNotInCondition("delivery_status",$exlude_status);
        }
        $criteria->order = "order_id DESC";
        $criteria->limit=50;            
        if($model = AR_ordernew::model()->findAll($criteria)){        
            return $model;
        }
        throw new Exception(HELPER_NO_RESULTS);  
    }

    public static function getTotalTask($diver_id=0,$exlude_status=array(), $transaction_type='delivery')
    {
        $criteria=new CDbCriteria();
        $criteria->addCondition("driver_id=:driver_id AND service_code=:service_code");
        $criteria->params = [
            ':driver_id'=>intval($diver_id),            
            ':service_code'=>$transaction_type,
        ];        
        if(is_array($exlude_status) && count($exlude_status)>=1){
            $criteria->addNotInCondition("delivery_status",$exlude_status);
        }
        $count = AR_ordernew::model()->count($criteria);
        return $count;
    }

    public static function getVehicleAssign($driver_id='',$date_start='')
    {
         $model = AR_driver_schedule::model()->find("driver_id=:driver_id AND DATE(time_start)=:time_start",[
            ':driver_id'=>intval($driver_id),
            ':time_start'=>$date_start
         ]);
         if($model){            
            $model_vehicle = self::getVehicle($model->vehicle_id);
            return $model_vehicle;
         }
         throw new Exception("No vehicle assign");  
    }

    public static function getVehicleByIDs($vehicles=array())
    {
        $criteria=new CDbCriteria();
        $criteria->addCondition('active=:active');		
        $criteria->params = [
            ':active'=>"1"            
        ];
        $criteria->addInCondition('vehicle_id',(array)$vehicles);
        $criteria->order = "vehicle_id ASC";
        $model = AR_driver_vehicle::model()->findAll($criteria);
        if($model){
            $data = [];
            foreach ($model as $items) {
                $data[$items->vehicle_id] = [
                    'vehicle_id'=>$items->vehicle_id,
                    'vehicle_uuid'=>$items->vehicle_uuid,
                    'vehicle_type_id'=>$items->vehicle_type_id,
                    'plate_number'=>$items->plate_number,
                    'maker'=>$items->maker,
                    'model'=>$items->model,
                    'color'=>$items->color,
                    'photo'=>CMedia::getImage($items->photo,$items->path,Yii::app()->params->size_image_thumbnail,CommonUtility::getPlaceholderPhoto('car','car.png')),
                ];
            }            
            return $data;
        }
        throw new Exception(HELPER_NO_RESULTS);  
    }

    public static function deliveryStatusList()
    {
        // $model = AR_status::model()->findAll("group_name=:group_name",[
        //     ':group_name'=>'delivery_status'
        // ]);
        // if($model){
        //     $data = [];
        //     foreach ($model as $items) {
        //         $data[]  = [
        //             'label'=>$items->description,
        //             'value'=>$items->description
        //         ];
        //     }
        //     return $data;
        // }
        // throw new Exception(HELPER_NO_RESULTS);  
        
        $model = AttributesTools::getOrderStatusList(Yii::app()->language,'delivery_status');
        if($model){
            foreach ($model as $items) {
                $data[]  = [
                    'label'=>$items['description'],
                    'value'=>$items['status']
                ];
            }
            return $data;
        }
        throw new Exception(HELPER_NO_RESULTS);  
    }

    public static function getTotalTaskByDriverIDS($driver_ids=array(), $assigned_group=array() , $delivery_date='')
    {     
        $criteria=new CDbCriteria();
        $criteria->select="driver_id,count(*) as total_sold";        
        $criteria->addCondition("delivery_date=:delivery_date");
        $criteria->params = [
            ':delivery_date'=>$delivery_date
        ];
        $criteria->group = 'driver_id';
        $criteria->addInCondition('driver_id',(array)$driver_ids);
        $criteria->addInCondition('delivery_status',(array)$assigned_group);                
        if($model = AR_ordernew::model()->findAll($criteria)){                        
            $data = array();
            foreach ($model as $key => $items) {
                if($items->driver_id>0){
                    $data[$items->driver_id] = $items->total_sold;
                }                
            }
            return $data;
        }
        throw new Exception(HELPER_NO_RESULTS);  
    }

    public static function getActivity($driver_id=0, $date_start='',$date_end='')
    {
        $criteria=new CDbCriteria;
        $criteria->addCondition("driver_id=:driver_id");
        $criteria->params = [
            ':driver_id'=>intval($driver_id)            
        ];
        if(!empty($date_start)){
            $criteria->addBetweenCondition('date_created',$date_start,$date_end);
        }
        
        $criteria->order="created_at DESC";
        $dependency = CCacheData::dependency();				        
        $model = AR_driver_activity::model()->cache(Yii::app()->params->cache, $dependency)->findAll($criteria);
        if($model){
            return $model;
        }
        throw new Exception(HELPER_NO_RESULTS);  
    }

    public static function getDriverToAssign($filter=array())
    {        
        $stmt = "
        SELECT a.driver_id,a.first_name,
        a.delivery_distance_covered
        ,
        ( $filter[distance_exp] * acos( cos( radians($filter[latitude]) ) * cos( radians( latitude ) ) 
        * cos( radians( lontitude ) - radians($filter[longitude]) ) 
        + sin( radians($filter[latitude]) ) * sin( radians( latitude ) ) ) ) 
        AS distance
        FROM {{driver}} a				
        WHERE a.status='active'
        AND a.driver_id IN (
            select driver_id from {{driver_schedule}}
            where driver_id = a.driver_id
            and date_start = ".q($filter['now'])."
            and active=1
            and $filter[timenow] BETWEEN HOUR(time_start) and HOUR(time_end)
        )
        HAVING distance < a.delivery_distance_covered				
        ORDER BY distance ASC					
        LIMIT 0,20
        ";
        if($res = Yii::app()->db->createCommand($stmt)->queryAll()){          
            $data = []; $drivers = [];
            foreach ($res as $items) {                
                $drivers[] = $items['driver_id'];
                $data[$items['driver_id']] = [
                    'driver_id'=>$items['driver_id'],
                    'first_name'=>$items['first_name'],
                    'distance'=>$items['distance'],
                ];
            }
            return [
                'data'=>$data,
                'drivers'=>$drivers
            ];
        }
        throw new Exception(HELPER_NO_RESULTS); 
    }

    public static function TotalDriversByTabs($date_start='',$date_end='',$status='',$q='',$merchant_owner_id=0)
    {
        $assigned_group = AOrders::getOrderTabsStatus('assigned');

        if(!is_array($assigned_group)){
            return 0;
        }

        $params=array();
        foreach($assigned_group as $value){
            $params[] = q($value);
        }            

        $in_condition = '';
        if(!empty($q)){
            $in_condition = "
            and driver_id = (
                select driver_id from {{driver}}
                where status='active'   
                and driver_id=a.driver_id  
                and ( first_name LIKE ".q("$q%")."   or  last_name LIKE ".q("$q%")."   )                
            )
            ";
        } else {
            $in_condition = "
            and driver_id = (
                select driver_id from {{driver}}
                where status='active'    
                and driver_id=a.driver_id             
            )
            ";
        }

        $on_demand_availability = self::getOndemand();
        if($status=="duty"){            
            if($on_demand_availability){
                $stmt = "
                SELECT count(DISTINCT(driver_id)) FROM {{driver_schedule}} a
                WHERE active=1 AND on_demand = 1
                AND merchant_id=".q($merchant_owner_id)."
                AND driver_id NOT IN (
                    select driver_id from {{ordernew}}
                    where driver_id = a.driver_id
                    and delivery_date = ".q($date_start)."
                    and delivery_status in (".implode(', ',$params).")      
                )  
                AND driver_id IN (
                    select driver_id from {{driver}}
                    where merchant_id=".q($merchant_owner_id)."
                    and is_online = 1
                )
                ".$in_condition."                
                ";
            } else {
                $stmt = '
                SELECT count(*),a.driver_id FROM {{driver_schedule}} a
                WHERE active=1 
                AND DATE(time_start) BETWEEN '.q($date_start).' AND '.q($date_end).'
                AND DATE(shift_time_started) IS NOT NULL  
                AND DATE(shift_time_ended) IS NULL  
                AND driver_id NOT IN (
                    select driver_id from {{ordernew}}
                    where driver_id = a.driver_id
                    and delivery_date = '.q($date_start).'
                    and delivery_status in ('.implode(', ',$params).')                
                )             
                '.$in_condition.'
                ';
            }
        } else {     
            if($on_demand_availability){
                $stmt = "
                SELECT count(DISTINCT(driver_id)) FROM {{driver_schedule}} a
                WHERE active=1 AND on_demand = 1
                AND merchant_id=".q($merchant_owner_id)."
                AND driver_id IN (
                    select driver_id from {{ordernew}}
                    where driver_id = a.driver_id
                    and delivery_date = ".q($date_start)."
                    and delivery_status in (".implode(', ',$params).")      
                )  
                AND driver_id IN (
                    select driver_id from {{driver}}
                    where merchant_id=".q($merchant_owner_id)."
                    and is_online = 1
                )                                
                ";                
            } else {
                $stmt = '
                SELECT count(*),a.driver_id FROM {{driver_schedule}} a
                WHERE active=1 
                AND DATE(time_start) BETWEEN '.q($date_start).' AND '.q($date_end).'
                AND DATE(shift_time_started) IS NOT NULL  
                AND DATE(shift_time_ended) IS NULL  
                AND driver_id IN (
                    select driver_id from {{ordernew}}
                    where driver_id = a.driver_id
                    and delivery_date = '.q($date_start).'
                    and delivery_status in ('.implode(', ',$params).')                
                )            
                '.$in_condition.'
                ';
            }
        }                         
        if($model = AR_driver_schedule::model()->countBySql($stmt)){              
            return $model;
        }
        return 0;
    }

    public static function getDeliverySteps($status='')
    {
        $meta = AR_admin_meta::getMeta(['status_assigned',
          'status_acknowledged','status_driver_to_restaurant','status_arrived_at_restaurant',
          'status_waiting_for_order','status_order_pickup','status_delivery_started',
          'status_arrived_at_customer','status_delivery_delivered'
        ]);
        $status_assigned = isset($meta['status_assigned'])?strtolower($meta['status_assigned']['meta_value']):'';
        $status_acknowledged = isset($meta['status_acknowledged'])?strtolower($meta['status_acknowledged']['meta_value']):'';
        $status_driver_to_restaurant = isset($meta['status_driver_to_restaurant'])?strtolower($meta['status_driver_to_restaurant']['meta_value']):'';
        $status_arrived_at_restaurant = isset($meta['status_arrived_at_restaurant'])?strtolower($meta['status_arrived_at_restaurant']['meta_value']):'';
        $status_waiting_for_order = isset($meta['status_waiting_for_order'])?strtolower($meta['status_waiting_for_order']['meta_value']):'';
        $status_order_pickup = isset($meta['status_order_pickup'])?strtolower($meta['status_order_pickup']['meta_value']):'';
        $status_delivery_started = isset($meta['status_delivery_started'])?strtolower($meta['status_delivery_started']['meta_value']):'';
        $status_arrived_at_customer = isset($meta['status_arrived_at_customer'])?strtolower($meta['status_arrived_at_customer']['meta_value']):'';

        //dump($status_acknowledged);

        $steps = [];
        $status = strtolower($status);
        //dump($status);
        switch ($status) {
            case $status_assigned:
                $steps = [
                    'steps'=>1,
                    'next_action'=>'accept'
                ];
                break;                    
            case $status_acknowledged:                
                $status_data = AttributesTools::StatusColor('status_driver_to_restaurant');
                $steps = [
                    'steps'=>2,
                    'next_action'=>'driver_to_restaurant',
                    'label'=>t("On the way to merchant"),                                        
                    'methods'=>"onthewayvendor",
                    'instructions'=>t("Go to restaurant"),
                    'status_data'=>$status_data
                ];
                break;                        
            case $status_driver_to_restaurant:
                $status_data = AttributesTools::StatusColor('status_arrived_at_restaurant');
                $steps = [
                    'steps'=>3,
                    'next_action'=>'arrived_at_restaurant',
                    'label'=>t("Arrived at merchant"),
                    'methods'=>"arrivedatvendor",
                    'instructions'=>t("Go to restaurant"),
                    'status_data'=>$status_data
                ];
                break;                             
            case $status_arrived_at_restaurant:
            case $status_waiting_for_order:    
                $status_data = AttributesTools::StatusColor('status_order_pickup');
                $steps = [
                    'steps'=>4,
                    'next_action'=>'status_order_pickup',
                    'label'=>t("Pick Up"),
                    'methods'=>"orderpickup",
                    'instructions'=>t("Pickup"),
                    'status_data'=>$status_data
                ];
                break;                                  
            case $status_order_pickup:
                $status_data = AttributesTools::StatusColor('status_delivery_started');
                $steps = [
                    'steps'=>5,
                    'next_action'=>'status_delivery_started',
                    'label'=>t("On the way to customer"),
                    'methods'=>"onthewaycustomer",
                    'instructions'=>t("Go to customer"),
                    'status_data'=>$status_data
                ];
                break;                             
            case $status_delivery_started:
                $status_data = AttributesTools::StatusColor('status_arrived_at_customer');
                $steps = [
                    'steps'=>6,
                    'next_action'=>'status_arrived_at_customer',
                    'label'=>t("Arrived at customer"),
                    'methods'=>"arrivedatcustomer",
                    'instructions'=>t("Go to customer"),
                    'status_data'=>$status_data
                ];
                break;                                 
            case $status_arrived_at_customer:
                $status_data = AttributesTools::StatusColor('status_delivery_delivered');
                $steps = [
                    'steps'=>7,
                    'next_action'=>'delivery_delivered',
                    'label'=>t("Delivered"),
                    'methods'=>"orderdelivered",
                    'instructions'=>t("Confirm Drop-off"),
                    'status_data'=>$status_data
                ];
                break; 
        }
        return $steps;            
    }

    public static function getTotalDeliveries($driver_id='',$completed_status=array(),$date_from='',$date_to='')
    {                        
        $criteria=new CDbCriteria;
        $criteria->addCondition("driver_id=:driver_id");
        $criteria->params = [
            ':driver_id'=>intval($driver_id),            
        ];        
        $criteria->addInCondition("delivery_status",(array)$completed_status);                
        $criteria->addBetweenCondition("delivery_date",$date_from,$date_to); 
        $count=AR_ordernew::model()->count($criteria);        
        if($count){
            return $count;
        }
        return 0;
    }

    public static function getSummaryDeliveries($driver_id='', $completed_status='',$dates=array())
    {        
        $criteria=new CDbCriteria;
        $criteria->select="count(*) as total_sold,delivery_date";
        $criteria->addCondition("driver_id=:driver_id AND delivery_status=:delivery_status");
        $criteria->params = [
            ':driver_id'=>intval($driver_id),
            ':delivery_status'=>$completed_status
        ];        
        $criteria->addInCondition("delivery_date",$dates);
        $criteria->group="delivery_date";        
        $model=AR_ordernew::model()->findAll($criteria); 
        if($model){
            $data = [];
            foreach($model as $items){
                $data[$items->delivery_date] = $items->total_sold;
            }
            return $data;
        }
        throw new Exception(HELPER_NO_RESULTS); 
    }

    public static function getTotalDeliveryFee($driver_id='',$completed_status=array(),$date_from='', $date_to='')
    {        	
        $criteria=new CDbCriteria;
        $criteria->select="SUM(delivery_fee) as delivery_fee";
        $criteria->addCondition("driver_id=:driver_id");
        $criteria->params = [
            ':driver_id'=>intval($driver_id)
        ];
        $criteria->addInCondition("delivery_status",(array)$completed_status);
        $criteria->addBetweenCondition("delivery_date",$date_from,$date_to);          
        if($model = AR_ordernew::model()->find($criteria)){ 
            return $model->delivery_fee;
        }
        return false;
    }

    public static function getTotalTips($driver_id='',$completed_status=array(),$date_from='', $date_to='')
    {        	
        $criteria=new CDbCriteria;
        $criteria->select="SUM(courier_tip) as courier_tip";
        $criteria->addCondition("driver_id=:driver_id");
        $criteria->params = [
            ':driver_id'=>intval($driver_id)
        ];
        $criteria->addInCondition("delivery_status",(array)$completed_status);
        $criteria->addBetweenCondition("delivery_date",$date_from,$date_to);        
        if($model = AR_ordernew::model()->find($criteria)){ 
            return $model->courier_tip;
        }
        return false;
    }

    public static function getCashCollected($driver_id='',$completed_status=array(),$date_from='', $date_to='',$payment_codes=array())
    {        	
        $criteria=new CDbCriteria;
        $criteria->select="SUM(total) as total";
        $criteria->addCondition("driver_id=:driver_id");
        $criteria->params = [':driver_id'=>intval($driver_id)];
        $criteria->addInCondition("delivery_status",(array)$completed_status);
                
        if(!empty($date_from)){
            $criteria->addBetweenCondition("delivery_date",$date_from,$date_to);              
        }        

        if(is_array($payment_codes) && count($payment_codes)>=1){
            $criteria->addInCondition("payment_code",(array)$payment_codes);     
        }        
        if($model = AR_ordernew::model()->find($criteria)){             
            return $model->total;
        }
        return false;
    }

    public static function deleteNotifications($channel='',$ids='')
    {
        $criteria=new CDbCriteria;
        $criteria->addCondition("notication_channel=:notication_channel");
        $criteria->params = [':notication_channel'=>trim($channel)];
        $criteria->addInCondition("notification_uuid",$ids);
        $model = AR_notifications::model()->deleteAll($criteria);
        if($model){
            return true;
        }
        throw new Exception("Error deleting records."); 
    }

    public static function getMeta($reference_id=0, $meta_name='')
    {
        $criteria=new CDbCriteria;
        $criteria->addCondition("reference_id=:reference_id AND meta_name=:meta_name");
        $criteria->params = [
            ':reference_id'=>intval($reference_id),
            ':meta_name'=>$meta_name
        ];
        if($model = AR_ordernew::model()->findAll($criteria)){             
            return $model;
        }
        throw new Exception(HELPER_NO_RESULTS); 
    }

    public static function getMetaAll($reference_ids=array(), $meta_name='')
    {
        $criteria=new CDbCriteria;
        $criteria->addCondition("meta_name=:meta_name");
        $criteria->params = [            
            ':meta_name'=>trim($meta_name)
        ];
        $criteria->addInCondition('reference_id',(array)$reference_ids);        
        if($model = AR_driver_meta::model()->findAll($criteria)){                         
            $data = [];
            foreach ($model as $items) {                
                $data[$items->reference_id][] = [
                    'document'=>CMedia::getImage($items->meta_value1,$items->meta_value2,Yii::app()->params->size_image_thumbnail,CommonUtility::getPlaceholderPhoto('driver'))
                ];
            }
            return $data;
        }
        throw new Exception(HELPER_NO_RESULTS); 
    }

    public static function GetZone($zone_id='',$merchant_id=0)
    {
        $model = AR_zones::model()->find("zone_id=:zone_id AND merchant_id=:merchant_id",[
            ':zone_id'=>intval($zone_id),
            ':merchant_id'=>intval($merchant_id),
        ]);
        if($model){
            return $model;
        }
        throw new Exception(HELPER_NO_RESULTS); 
    }

    public static function getShiftSchedule($shift_uuid)
    {
        $model = AR_driver_shift_schedule::model()->find("shift_uuid=:shift_uuid",[
            ':shift_uuid'=>$shift_uuid
        ]);
        if($model){
            return $model;
        }
        throw new Exception( t(HELPER_RECORD_NOT_FOUND) );        
    }

    public static function getVehicleByDriverID($driver_id)
    {
        $model = AR_driver_vehicle::model()->find("driver_id=:driver_id",[
            ':driver_id'=>$driver_id
        ]);
        if($model){
            return $model;
        }
        throw new Exception( t(HELPER_RECORD_NOT_FOUND) );        
    }

    public static function getBreak($driver_id=0,$date='')
    {
        $model = AR_driver_break::model()->find("driver_id=:driver_id AND DATE(break_started)=:break_started AND break_ended IS NULL",[
            ':driver_id'=>intval($driver_id),
            ':break_started'=>$date
        ]);
        if($model){
            $break_until = date('c', strtotime("$model->break_duration minutes", strtotime($model->break_started)));
            return [
                'id'=>$model->id,
                'break_duration'=>$model->break_duration,
                'break_started'=>$model->break_started,
                'break_until'=>$break_until,
                'break_until_hours'=>Date_Formatter::Time($break_until)
            ];
        }        
        throw new Exception( t(HELPER_NO_RESULTS) );      
    }

    public static function canRequestBreak($driver_id=0, $date='')
    {
        $stmt="
		SELECT count(*) as total FROM {{driver_break}} a
		WHERE 
		driver_id=".q($driver_id)."
		AND
		DATE(break_started)=".q($date)."
		";				
		if($res = Yii::app()->db->createCommand($stmt)->queryRow()){						
			$total = isset($res['total'])?intval($res['total']):0;
			if($total>2){				
				return false;
			}			
		}			
        return true;
    }

    public static function getTotalTrips($driver_id=0, $start='', $end='',$status=array())
    {
        $status = CommonUtility::arrayToQueryParameters($status);
        $stmt="
        SELECT count(*) as total
        FROM {{ordernew}}
        WHERE driver_id=".q($driver_id)."
        AND DATE(delivery_date) BETWEEN  ".q($start)." AND ".q($end)."        
        AND delivery_status IN (".$status.")
        ";                
        if($model = Yii::app()->db->createCommand($stmt)->queryRow()){            
            return floatval($model['total']);
        }
        throw new Exception( t(HELPER_NO_RESULTS) );    
    }

    public static function getEarningsByRange($card_id=0, $start='', $end='')
    {
        $stmt="
        SELECT *
        FROM {{wallet_transactions}}
        WHERE card_id=".q($card_id)."
        AND DATE(transaction_date) BETWEEN  ".q($start)." AND ".q($end)."
        ORDER BY transaction_id DESC        
        ";        
        if($model = Yii::app()->db->createCommand($stmt)->queryRow()){
            return $model['running_balance'];
        }
        throw new Exception( t(HELPER_NO_RESULTS) );    
    }

    public static function EarningCharts($card_id=0, $start='', $end='',$dense=1)
    {
        $date_range = []; $date_range2=[]; $data = []; $new_data = [];
        for ($x = 0; $x <= 6; $x++) {
            $date = date('Y-m-d', strtotime($start. "+$x days"));
            $date_range[] =  date('Y-m-d', strtotime($start. "+$x days"));		            
            $date_range2[] =   substr(date('D', strtotime($start. "+$x days")),0,$dense) ;		
        }        

        $transaction_type = 'credit';
        $meta_name_array = ['payout_tip','payout_delivery_fee','payout_commission','payout_fixed','payout_fixed_and_commission','payout_incentives','adjustment'];
        $meta_name_array = CommonUtility::arrayToQueryParameters($meta_name_array);
        
        $stmt = "
        SELECT sum(a.transaction_amount) as total,DATE(a.transaction_date) as date
        FROM {{wallet_transactions}} a
        WHERE 
        a.card_id = ".q($card_id)."
        AND DATE(a.transaction_date) BETWEEN  ".q($start)." AND ".q($end)."
        AND transaction_type= ".q($transaction_type)."
        AND a.transaction_id IN (
            select transaction_id FROM {{wallet_transactions_meta}}
            where transaction_id = a.transaction_id
            and meta_name IN (".$meta_name_array.")
        )        
        GROUP BY DATE(a.transaction_date)
        ";                      
        if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
            foreach ($res as $items) {                
                $data[$items['date']] = $items['total'];
            }                              
        }
                
        foreach ($date_range as $item) {                        
            if(array_key_exists($item,(array)$data)){                
                $new_data[] = floatval($data[$item]);
            } else $new_data[] = 0;
        }      

        return [
            'date_range'=>$date_range2,
            'data'=>$new_data
        ];
    }

    public static function EarningByMeta($card_id=0, $start='', $end='',$meta_name='',$transaction_type='credit')
    {
        $meta_name_array = CommonUtility::arrayToQueryParameters($meta_name);
        $stmt = "
        SELECT sum(a.transaction_amount) as total
        FROM {{wallet_transactions}} a
        WHERE 
        a.card_id = ".q($card_id)."
        AND DATE(a.transaction_date) BETWEEN  ".q($start)." AND ".q($end)."
        AND transaction_type= ".q($transaction_type)."
        AND a.transaction_id IN (
            select transaction_id FROM {{wallet_transactions_meta}}
            where transaction_id = a.transaction_id
            and meta_name IN (".$meta_name_array.")
        )
        ";              
        if($res = Yii::app()->db->createCommand($stmt)->queryRow()){
            return floatval($res['total']);
        }
        return 0;
    }

    public static function EarningAdjustment($card_id=0, $start='', $end='',$meta_name='',$transaction_type='credit')
    {
        $meta_name_array = CommonUtility::arrayToQueryParameters($meta_name);
        $stmt = "
        SELECT sum(a.transaction_amount) as total
        FROM {{wallet_transactions}} a
        WHERE 
        a.card_id = ".q($card_id)."
        AND DATE(a.transaction_date) BETWEEN  ".q($start)." AND ".q($end)."   
        AND transaction_type = ".q($transaction_type)."
        AND a.transaction_id IN (
            select transaction_id FROM {{wallet_transactions_meta}}
            where transaction_id = a.transaction_id
            and meta_name IN (".$meta_name_array.")
        )
        ";              
        if($res = Yii::app()->db->createCommand($stmt)->queryRow()){
            return floatval($res['total']);
        }
        return 0;
    }

    public static function GetTotalCashout($card_id=0, $start='', $end='',$transaction_type='cashout')
    {
        $stmt = "
        SELECT sum(a.transaction_amount) as total
        FROM {{wallet_transactions}} a
        WHERE 
        a.card_id = ".q($card_id)."
        AND DATE(a.transaction_date) BETWEEN  ".q($start)." AND ".q($end)."   
        AND transaction_type = ".q($transaction_type)."        
        ";              
        if($res = Yii::app()->db->createCommand($stmt)->queryRow()){
            return floatval($res['total']);
        }
        return 0;
    }

    public static function getBankAccount($driver_id=0,$meta_name='bank_information')
    {
        $model = AR_driver_meta::model()->find("reference_id=:reference_id AND meta_name=:meta_name",[
            ':reference_id'=>intval($driver_id),
            ':meta_name'=>$meta_name
        ]);
        if($model){
            $bank = json_decode($model->meta_value1,true);
            if(is_array($bank) && count($bank)>=1){
                return $bank;
            }
        }
        throw new Exception( t(HELPER_NO_RESULTS) ); 
    }

    public static function requestPayout($data=[],$card_id='', $amount=0 , $cashout_fee=0, $account='',$status='unpaid',$description="Cashout to Bank account {{account}}")
    {    
        $balance = CWallet::getBalance($card_id);
        $total_cashout =  floatval($amount) - floatval($cashout_fee);
        		
		if($total_cashout<=0){
			throw new Exception( 'Amount must be greater than 0' );
		}

        $options = OptionsTools::find(['driver_cashout_minimum','driver_cashout_miximum']);
        $cashout_minimum = isset($options['driver_cashout_minimum'])?floatval($options['driver_cashout_minimum']):0;
        $cashout_miximum = isset($options['driver_cashout_miximum'])?floatval($options['driver_cashout_miximum']):0;
        
        if($cashout_minimum>0){
			if($cashout_minimum>$total_cashout){
				throw new Exception( t("Cashout minimum amount is {{minimum_amount}}",
				array('{{minimum_amount}}'=>Price_Formatter::formatNumber($cashout_minimum))) );
			}
		}
    
        if($cashout_miximum>0){            
			if($total_cashout>$cashout_miximum){
				throw new Exception( t("Cashout maximum amount is {{cashout_miximum}}",
				array('{{cashout_miximum}}'=>Price_Formatter::formatNumber($cashout_miximum))) );
			}
		}

        $remaining_cashout = Cdriver::getCashoutRemaining($card_id,date("Y-m-d"));
        if($remaining_cashout<=0){
            throw new Exception( t("You have reach the maximum cash out request for today. You can cash out again tomorrow.") );
        }
        
        if($total_cashout<=$balance){             
            $params = array(			  
                'transaction_description'=>$description,
                'transaction_description_parameters'=>array('{{account}}'=>$account),			  
                'transaction_type'=>"cashout",
                'transaction_amount'=>floatval($total_cashout),		
                'status'=>$status,
                'merchant_base_currency'=>isset($data['driver_default_currency'])?$data['driver_default_currency']:Price_Formatter::$number_format['currency_code'],
                'admin_base_currency'=>isset($data['admin_base_currency'])?$data['admin_base_currency']:Price_Formatter::$number_format['currency_code'],
                'exchange_rate_merchant_to_admin'=>isset($data['exchange_rate_merchant_to_admin'])? ($data['exchange_rate_merchant_to_admin']>0?$data['exchange_rate_merchant_to_admin']:1) :1,
                'exchange_rate_admin_to_merchant'=>isset($data['exchange_rate_admin_to_merchant'])? ($data['exchange_rate_admin_to_merchant']>0?$data['exchange_rate_admin_to_merchant']:1) :1,
                'reference_id'=>isset($data['reference_id'])? ($data['reference_id']>0?$data['reference_id']:0) :0,
            );			            
            $transaction_id = CWallet::inserTransactions($card_id,$params);

            if($cashout_fee>0){            
                CWallet::inserTransactions($card_id,[
                    'transaction_description'=>"Cashout fee",                
                    'transaction_type'=>"debit",
                    'transaction_amount'=>floatval($cashout_fee),
                    'meta_name'=>"cashout_fee",
                    'meta_value'=>$transaction_id,
                    'status'=>"paid",
                    'merchant_base_currency'=>isset($data['driver_default_currency'])?$data['driver_default_currency']:Price_Formatter::$number_format['currency_code'],
                    'admin_base_currency'=>isset($data['admin_base_currency'])?$data['admin_base_currency']:Price_Formatter::$number_format['currency_code'],
                    'exchange_rate_merchant_to_admin'=>isset($data['exchange_rate_merchant_to_admin'])? ($data['exchange_rate_merchant_to_admin']>0?$data['exchange_rate_merchant_to_admin']:1) :1,
                    'exchange_rate_admin_to_merchant'=>isset($data['exchange_rate_admin_to_merchant'])? ($data['exchange_rate_admin_to_merchant']>0?$data['exchange_rate_admin_to_merchant']:1) :1,
                    'reference_id'=>isset($data['reference_id'])? ($data['reference_id']>0?$data['reference_id']:0) :0,
                ]);
            }

			return $transaction_id;		
        } else throw new Exception( t("The amount may not be greater than [balance].",array('[balance]'=>$balance)) );		
    }

    public static function getCashoutRemaining($card_id=0,$transaction_date='')
    {
        $balance = 0;
        $criteria=new CDbCriteria();
        $criteria->addCondition("card_id=:card_id AND transaction_type=:transaction_type AND DATE(transaction_date)=:transaction_date");
        $criteria->params = [
            ':card_id'=>$card_id,
            ':transaction_type'=>"cashout",
            ':transaction_date'=>$transaction_date
        ];
        $criteria->addInCondition("status",['paid','unpaid']);           
        $count=AR_wallet_transactions::model()->count($criteria);        
        
        $options = OptionsTools::find(['driver_cashout_request_limit']);
		$cashout_limit = isset($options['driver_cashout_request_limit'])?intval($options['driver_cashout_request_limit']):0;			
        $balance = $cashout_limit-$count;
        return $balance;
    }

    public static function getDenomination($meta_name='')
	{
        $dependency = CCacheData::dependency();         
		$model=AR_admin_meta::model()->cache(Yii::app()->params->cache, $dependency)->findAll("meta_name=:meta_name",array(
		  ':meta_name'=>$meta_name
		));
		if($model){			
			$data = array();
			foreach ($model as $items) {
				$data[$items->meta_value] = Price_Formatter::formatNumber($items->meta_value);
			}
			return $data;
		}
		throw new Exception( 'no results' );
	}

    public static function getPaymentMethodMeta($payment_uuid='', $driver_id='')
	{
		$stmt="
		SELECT meta_name,meta_value1
		FROM {{driver_meta}}
		WHERE reference_id IN (
		   select payment_method_id
		   from {{driver_payment_method}}
		   where payment_uuid = ".q($payment_uuid)."
		   and
		   driver_id = ".q( intval($driver_id) )."
		)
		";		
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
			$data = array();
			foreach ($res as $val) {
				$data[$val['meta_name']] = $val['meta_value1'];
			}
			return $data;
		}
		throw new Exception( 'No payment method meta found' );
	}

    public static function getPayment($driver_id='', $payment_uuid='')
    {
        $model = AR_driver_payment_method::model()->find("driver_id=:driver_id AND payment_uuid=:payment_uuid",[
            ':driver_id'=>intval($driver_id),
            ':payment_uuid'=>trim($payment_uuid),
        ]);
        if($model){
            return $model;
        }
        throw new Exception(t(HELPER_NO_RESULTS));		
    }

    
    public static function getLegalMenu()
	{
		$legal_menu = array();
		$legal_menu['page_driver_privacy'] = t("Privacy Policy");
		$legal_menu['page_driver_terms_condition'] = t("Terms and condition");
		$legal_menu['page_driver_about_us'] = t("About us");
		return $legal_menu;
	}

    public static function getCollectCashDetails($collection_uuid='')
    {
        $stmt = "
        SELECT a.reference_id,a.driver_id,a.amount_collected,a.transaction_date
        ,b.driver_uuid, concat(b.first_name,' ',b.last_name) as driver_name,
        b.employment_type
        FROM {{driver_collect_cash}} a
        LEFT JOIN {{driver}} b 
        ON b.driver_id = a.driver_id
        WHERE 
        a.collection_uuid = ".q($collection_uuid)."
        ";
        if($res = Yii::app()->db->createCommand($stmt)->queryRow()){
            return $res;
        }
        throw new Exception(t(HELPER_NO_RESULTS));
    }

    public static function cashCollectedBalance($card_id=0)
    {
        $stmt = "
        SELECT IFNULL(SUM(transaction_amount),0) - 
        (
            select IFNULL(sum(transaction_amount),0) from {{wallet_transactions}}
            where 
            card_id = ".q($card_id)."
            and transaction_id IN (
                select transaction_id from {{wallet_transactions_meta}}
                where meta_name='collected_cash'                
            )
        ) as balance 
        FROM {{wallet_transactions}}
        WHERE 
        card_id = ".q($card_id)."
        AND transaction_id IN (
            select transaction_id from {{wallet_transactions_meta}}
            where meta_name='collected_payment'
        )
        ";              
        if($res = Yii::app()->db->createCommand($stmt)->queryRow()){
            return $res['balance'];
        }        
        throw new Exception(t(HELPER_NO_RESULTS));
    }

    public static function getActiveTaskStatus()
    {
        $result = [];
        $data = [
            'status_acknowledged','status_driver_to_restaurant',
            'status_arrived_at_restaurant','status_waiting_for_order','status_order_pickup','status_delivery_started','status_arrived_at_customer'            
        ];   
        $model = AR_admin_meta::getMeta($data);
        if(is_array($model) && count($model)>=1){
            foreach ($model as $items){
                $result[] = $items['meta_value'];
            }
        }
        return $result;
    }

    public static function getCountActiveTask($driver_id=0, $date='')
    {        
        $status = self::getActiveTaskStatus();        
        $criteria=new CDbCriteria();	
        $criteria->addCondition("driver_id=:driver_id AND DATE(delivery_date)=:delivery_date");        
        $criteria->params = [
            ':driver_id'=>intval($driver_id),
            ':delivery_date'=>$date
        ];
        $criteria->addInCondition("delivery_status",(array)$status);        
        $count = AR_ordernew::model()->count($criteria); 
        return $count;
    }

    public static function getCountActiveTaskAll($driver_ids=[],$date='')
    {
        $data = [];
        $status = self::getActiveTaskStatus();        
        //$status = AOrders::getOrderTabsStatus('assigned');
        $driver_params = CommonUtility::arrayToQueryParameters($driver_ids);        
        $status_params = CommonUtility::arrayToQueryParameters($status);
        $stmt = "
        SELECT count(*) as total ,driver_id FROM {{ordernew}}
        WHERE 
        DATE(delivery_date)=".q($date)."
        AND driver_id IN (".$driver_params.")
        AND delivery_status IN (".$status_params.")
        GROUP BY driver_id
        ";        
        if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
            foreach($res as $items){
                $data[$items['driver_id']] = $items['total'];
            }
        }
        return $data;
    }

    public static function zoneList($merchant_id = 0)
    {
        $criteria=new CDbCriteria();	
        $criteria->addCondition("merchant_id=:merchant_id");        
        $criteria->params = [
            ':merchant_id'=>intval($merchant_id),            
        ];
        if($model = AR_zones::model()->findAll($criteria)){
            $data = [];
            foreach ($model as $items) {
                $data[] = [
                    'zone_id'=>intval($items->zone_id),
                    'zone_name'=>$items->zone_name,
                    'description'=>$items->description,
                ];
            }
            return $data;
        }
        throw new Exception(t(HELPER_NO_RESULTS));
    }

    public static function getZoneSelected($merchant_id=0, $driver_id=0 )
    {
        $criteria=new CDbCriteria();	
        $criteria->addCondition("merchant_id=:merchant_id AND driver_id=:driver_id AND on_demand=1"); 
        $criteria->params = [
            ':merchant_id'=>intval($merchant_id),
            ':driver_id'=>intval($driver_id),
        ];
        if($model = AR_driver_schedule::model()->findAll($criteria)){
            $data = [];
            foreach ($model as $items) {
                $data[] = intval($items->zone_id);
            }
            return $data;
        }
        return false;
    }

    public static function setOndemand($data=false)
    {       
        self::$on_demand_availability = $data;
    }

    public static function getOndemand()
    {
        return self::$on_demand_availability;
    }

    public static function fetchExcludeDriver($order_id=0)
    {
        $criteria=new CDbCriteria();	
        $criteria->addCondition("order_id=:order_id"); 
        $criteria->params = [
            ':order_id'=>intval($order_id)
        ];
        if($model = AR_driver_attempts::model()->findAll($criteria)){
            $data = [];
            foreach ($model as $items) {
                $data[]=$items->driver_id;
            }
            return $data;
        }
        return false;
    }

    public static function getAvailableDrivers($order_id='')
    {

        $options = OptionsTools::find(['driver_enabled_auto_assign','driver_assign_when_accepted','driver_allowed_number_task','driver_on_demand_availability','driver_time_allowed_accept_order']);			            
        $time_allowed_accept_order = isset($options['driver_time_allowed_accept_order'])?$options['driver_time_allowed_accept_order']:1;
        $time_allowed_accept_order = $time_allowed_accept_order>0?$time_allowed_accept_order:1;            
        $enabled_auto_assign = isset($options['driver_enabled_auto_assign'])?$options['driver_enabled_auto_assign']:'';
        $assign_when_accepted = isset($options['driver_assign_when_accepted'])?$options['driver_assign_when_accepted']:'';
        $allowed_number_task = isset($options['driver_allowed_number_task'])?$options['driver_allowed_number_task']:1;				            
        $allowed_number_task = $allowed_number_task>0?$allowed_number_task:1;
        $on_demand_availability = isset($options['driver_on_demand_availability'])? ($options['driver_on_demand_availability']==1?true:false) :false;					

        $tracking_stats = AR_admin_meta::getMeta(['tracking_status_process','status_new_order','status_delivery_delivered']);											
		$processing_status = isset($tracking_stats['tracking_status_process'])?AttributesTools::cleanString($tracking_stats['tracking_status_process']['meta_value']):'';
		$status_new_order = isset($tracking_stats['status_new_order'])?AttributesTools::cleanString($tracking_stats['status_new_order']['meta_value']):'';
		$status_delivery_delivered = isset($tracking_stats['status_delivery_delivered'])?AttributesTools::cleanString($tracking_stats['status_delivery_delivered']['meta_value']):'';

        $order = COrders::getByID($order_id);
		$order_status = AttributesTools::cleanString($order->status);

        $merchant_id = $order->merchant_id;			
		$merchant_info = COrders::getMerchant($merchant_id,Yii::app()->language);

        $options_merchant = OptionsTools::find(['self_delivery'],$merchant_id);			
        $self_delivery = isset($options_merchant['self_delivery'])?$options_merchant['self_delivery']:0;
        $self_delivery = $self_delivery==1?true:false;
        $merchant_id_owner = $self_delivery==true?$merchant_id:0;
        
        $merchant_zone = []; $zone_query = '';
        if($zone_data = CMerchants::getListMerchantZone([$merchant_id],false)){
            $merchant_zone = isset($zone_data[$merchant_id])?$zone_data[$merchant_id]:'';									
            $zone_query = CommonUtility::arrayToQueryParameters($merchant_zone);
        } else $zone_query = CommonUtility::arrayToQueryParameters([0]);

        $merchant_latitude = isset($merchant_info['latitude'])?$merchant_info['latitude']:'';
        $merchant_longitude = isset($merchant_info['longitude'])?$merchant_info['longitude']:'';

        $options = OptionsTools::find(['home_search_unit_type']);
        $unit = isset($options['home_search_unit_type'])?$options['home_search_unit_type']:'';            
        $distance_exp = CMerchantListingV1::getDistanceExp(array('unit'=>$unit));	

        $assigned_group = AOrders::getOrderTabsStatus('assigned');			
        $active_status = CommonUtility::arrayToQueryParameters($assigned_group);
        $now = date("Y-m-d");

        $filter = [
            'now'=>$now,			
            'merchant_id'=>$merchant_id,
            'latitude'=>$merchant_latitude,
            'longitude'=>$merchant_longitude,
            'unit'=>$unit,
            'distance_exp'=>$distance_exp
        ];
        if(!$on_demand_availability){
            $stmt = "
				SELECT a.driver_id,a.first_name,
				a.delivery_distance_covered,
				(
					select count(*) from {{ordernew}}
					where driver_id = a.driver_id		
					and delivery_date = ".q($now)."
					and delivery_status IN ($active_status)		
				) as active_task,

				(
					select count(*) from {{ordernew}}
					where driver_id = a.driver_id		
					and delivery_date = ".q($now)."
					and delivery_status IN (".q($status_delivery_delivered).")		
				) as total_delivered
				,
				( $filter[distance_exp] * acos( cos( radians($filter[latitude]) ) * cos( radians( latitude ) ) 
				* cos( radians( lontitude ) - radians($filter[longitude]) ) 
				+ sin( radians($filter[latitude]) ) * sin( radians( latitude ) ) ) ) 
				AS distance
				FROM {{driver}} a				
				WHERE a.status='active'			
				AND a.merchant_id=".q($merchant_id_owner)."		
				AND a.driver_id IN (
					select driver_id from {{driver_schedule}}
					where driver_id = a.driver_id
					and DATE(time_start) BETWEEN ".q($filter['now'])." AND ".q($filter['now'])." 
					and DATE(shift_time_started) IS NOT NULL  
					and DATE(shift_time_ended) IS NULL  
					and active=1
					and zone_id IN ($zone_query)
				)
				HAVING distance < a.delivery_distance_covered
				AND ".$allowed_number_task." > active_task          
                AND a.driver_id NOT IN (
                   select driver_id
                   from {{driver_attempts}}
                   where order_id=".q($order_id)."                   
                   AND attempt_status IN ('declined')
                )      
				ORDER BY active_task ASC, total_delivered ASC , distance ASC
				LIMIT 0,5
			";
        } else {
            $stmt = "
				SELECT a.driver_id,a.first_name,
				a.delivery_distance_covered,
				(
					select count(*) from {{ordernew}}
					where driver_id = a.driver_id		
					and delivery_date = ".q($now)."
					and delivery_status IN ($active_status)		
				) as active_task,

				(
					select count(*) from {{ordernew}}
					where driver_id = a.driver_id		
					and delivery_date = ".q($now)."
					and delivery_status IN (".q($status_delivery_delivered).")		
				) as total_delivered,

				( $filter[distance_exp] * acos( cos( radians($filter[latitude]) ) * cos( radians( latitude ) ) 
				* cos( radians( lontitude ) - radians($filter[longitude]) ) 
				+ sin( radians($filter[latitude]) ) * sin( radians( latitude ) ) ) ) 
				AS distance
				
				FROM {{driver}} a				
			    WHERE a.status='active'		
				AND a.is_online=1
				AND a.driver_id IN (
					select driver_id from {{driver_schedule}}
					where 
					merchant_id=".q($merchant_id_owner)." and driver_id = a.driver_id 
					and on_demand=1 and zone_id IN ($zone_query)
				)
				HAVING distance < a.delivery_distance_covered
				AND ".$allowed_number_task." > active_task	
                AND a.driver_id NOT IN (
                   select driver_id
                   from {{driver_attempts}}
                   where order_id=".q($order_id)."                   
                   AND attempt_status IN ('declined')
                )			                
				ORDER BY active_task ASC, total_delivered ASC , distance ASC
				LIMIT 0,5	
				";			
        }        
        if($res = Yii::app()->db->createCommand($stmt)->queryAll()){ 
            return $res;
        }
        throw new Exception(t("No driver found to assign orders"));
    }    
        
}
// end class