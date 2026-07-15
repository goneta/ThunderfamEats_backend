<?php
class Ckitchen 
{  

    public static function getByReference($order_reference='',$return_data=false)
    {
        $model = AR_kitchen_order::model()->find("order_reference=:order_reference",[
            ':order_reference'=>$order_reference
        ]);
        if($model){
            return $return_data? $model :true;
        }
        return false;
    }

    public static function orderIDsAttribtes($merchant_id=0,$is_completed=0,$whento_deliver='now',$language=KMRS_DEFAULT_LANGUAGE)
    {
        $model = AR_kitchen_order::model()->findAll("merchant_id=:merchant_id AND is_completed=:is_completed AND whento_deliver=:whento_deliver",[
            ':merchant_id'=>intval($merchant_id),
            ':is_completed'=>$is_completed,
            ':whento_deliver'=>$whento_deliver
        ]);

        $cooking_ref = []; $ingredients = []; $list_sub_items = [];
        foreach ($model as $items) {
            $attributes = !empty($items->attributes)?json_decode($items->attributes,true):'';
            $addons = !empty($items->addons)?json_decode($items->addons,true):'';     
            
            if(is_array($addons) && count($addons)>=1){
                $sub_item_ids = array_column($addons, 'sub_item_id');
                $sub_item_ids = array_filter($sub_item_ids);
                $sub_item_ids = array_values($sub_item_ids);                       
                $list_sub_items = array_merge($list_sub_items,$sub_item_ids);
            }                
            
            if(is_array($attributes) && count($attributes)>=1){
                foreach ($attributes as $value_attributes) {                        
                    if($value_attributes['meta_name']=="cooking_ref"){
                        if(!in_array($value_attributes['meta_id'],$cooking_ref)){
                            $cooking_ref[] = $value_attributes['meta_id'];
                        }                        
                    } else if ( $value_attributes['meta_name']=='ingredients'){
                        if(!in_array($value_attributes['meta_id'],$ingredients)){
                            $ingredients[] = $value_attributes['meta_id'];
                        }
                    }
                }
            }            
        }
        
        $list_cooking_ref = Ckitchen::CookingList($cooking_ref,$language);
        $list_ingredients = Ckitchen::IngredientsList($ingredients,$language);
        $list_sub_items = Ckitchen::AddonList($list_sub_items,$language);
        return [
            'list_cooking_ref'=>$list_cooking_ref,
            'list_ingredients'=>$list_ingredients,
            'list_sub_items'=>$list_sub_items,
        ];
    }

    public static function getNewKitchenOrders($merchant_id=0,$filters=[],$language=KMRS_DEFAULT_LANGUAGE)
    {
        $position = isset($filters['position'])?$filters['position']:'';               
        $whento_deliver = isset($filters['whento_deliver'])?$filters['whento_deliver']:'now';               
        $is_completed = isset($filters['is_completed'])?$filters['is_completed']:0;
        $q = isset($filters['q'])?$filters['q']:'';
        $order_type = isset($filters['order_type'])?$filters['order_type']:'';
        $order_status = isset($filters['order_status'])?$filters['order_status']:'';                        
        $single_search = isset($filters['single_search'])?$filters['single_search']:false;                

        $atts = self::orderIDsAttribtes($merchant_id,$is_completed,$whento_deliver,$language);
        $list_cooking_ref = $atts['list_cooking_ref'];        
        $list_ingredients = $atts['list_ingredients'];
        $list_sub_items = $atts['list_sub_items'];        
                
        $data = array();
        $criteria = new CDbCriteria;
        $criteria->alias = "a";
        $criteria->select = "
        a.kitchen_order_id,
        a.is_completed,
        a.transaction_type,
        a.merchant_id,
        a.order_reference,   
        a.customer_name,     
        a.delivery_date,
        a.delivery_time,
        a.timezone,
        a.created_at,
        a.item_token,
        a.sequence,
        d.item_name,
        IF(COALESCE(NULLIF(e.item_name, ''), '') = '', d.item_name, e.item_name) as item_name,
        a.qty,
        a.item_status,
        a.special_instructions,
        a.addons,
        a.attributes,
        b.table_name,
        c.room_name,
        (
            select count(*) 
            from {{kitchen_order}}
            where order_reference = a.order_reference
            and item_status IN ('queue','in progress','ready','delayed')
        ) as total_pending
        ";


        if($single_search){
            $criteria->addCondition("a.merchant_id=:merchant_id AND order_reference=:order_reference");
            $criteria->params = [
                ':merchant_id'=>intval($merchant_id),
                ':order_reference'=>trim($q)
            ];
        } else {
            $criteria->addCondition("a.merchant_id=:merchant_id AND a.is_completed=:is_completed AND whento_deliver=:whento_deliver");
            $criteria->params = [
                ':merchant_id'=>intval($merchant_id),
                ':is_completed'=>$is_completed,
                ':whento_deliver'=>$whento_deliver
            ];
            if(is_array($order_type) && count($order_type)>=1){
                $criteria->addInCondition("a.transaction_type",$order_type);
            } else {            
                if(!empty($position)){
                    $criteria->addInCondition("a.transaction_type",[-1]);
                }
            }
            if(is_array($order_status) && count($order_status)>=1){
                $criteria->addInCondition("a.item_status",$order_status);
            }
            if(!empty($q)){
                $criteria->addSearchCondition("order_reference",$q);
                //$criteria->addSearchCondition("customer_name",$q,true,"OR");                        
            }
        }        

        $criteria->join="
		LEFT JOIN {{table_tables}} b on a.table_uuid = b.table_uuid 			
		LEFT JOIN {{table_room}} c on b.room_id = c.room_id 			
        LEFT JOIN {{item}} d on a.item_token = d.item_token 		
        
        left JOIN (
			SELECT item_id, item_name FROM {{item_translation}} where language = ".q($language)."
		) e
		on d.item_id = e.item_id
		";	                     
        $criteria->order = "a.sequence,a.kitchen_order_id ASC";         
        $criteria->limit = 100;        
        if($model = AR_kitchen_order::model()->findAll($criteria)){             
            foreach ($model as $items) {                
                $order_reference = $items->order_reference;
                $attributes = !empty($items->attributes)?json_decode($items->attributes,true):'';
                $addons = !empty($items->addons)?json_decode($items->addons,true):'';     
                                
                if(is_array($attributes) && count($attributes)>=1){
                    foreach ($attributes as $index=>$value_attributes) {                        
                        if($value_attributes['meta_name']=="cooking_ref"){                            
                            $attributes[$index]['value'] = isset($list_cooking_ref[$value_attributes['meta_id']])?$list_cooking_ref[$value_attributes['meta_id']]:'';
                        } else if ( $value_attributes['meta_name']=='ingredients'){
                            $attributes[$index]['value']=isset($list_ingredients[$value_attributes['meta_id']])?$list_ingredients[$value_attributes['meta_id']]:'';
                        }
                    }
                }

                if(is_array($addons) && count($addons)>=1){
                    foreach ($addons as $index=>$value_addons) {                        
                        $addons[$index]['value'] = isset($list_sub_items[$value_addons['sub_item_id']])?$list_sub_items[$value_addons['sub_item_id']]:'';
                    }
                }
                               
                $item = array(
                    'kitchen_order_id'=>$items->kitchen_order_id,                    
                    'checked'=>false,
					'item_token'=>$items->item_token,
                    'item_name'=>CHtml::decode($items->item_name),
                    'qty'=>$items->qty,
                    'item_status'=>$items->item_status,
                    'item_status_pretty'=>t(ucwords($items->item_status)),
                    'special_instructions'=>$items->special_instructions,                    
                    'attributes'=>$attributes,
                    'addons'=>$addons,
				);
                if(array_key_exists($order_reference, $data)){                    
                    $data[$order_reference]["items"][] = $item;
                } else {                                                                    
                    $data[$order_reference] = [
                        'order_reference'=>$order_reference,         
                        'sequence'=>$items->sequence,               
                        'customer_name'=>t($items->customer_name),
                        'transaction_type'=>$items->transaction_type,
                        'transaction_type_pretty'=>t($items->transaction_type),
                        'room_name'=>$items->room_name, 
                        'table_name'=>$items->table_name,                        
                        'delivery_date'=>$items->delivery_date,
                        'delivery_time'=>$items->delivery_time,                        
                        'delivery_time_pretty'=> !empty($items->delivery_time)? date("H:i",strtotime("$items->delivery_date $items->delivery_time")) :t("Asap") ,                        
                        'delivery_datetime'=>Date_Formatter::dateTime("$items->delivery_date $items->delivery_time","dd.MM.yyyy, HH:mm",true),	
                        'timezone'=>$items->timezone,
                        'created_at'=>$items->created_at,
                        "request_time" => date("c",strtotime($items->created_at)),
                        'created_at_pretty'=>Date_Formatter::dateTime($items->created_at,"dd.MM.yyyy, HH:mm",true),		
                        'total_pending'=>intval($items->total_pending),      
                        'order_status'=>'ontime',
                        'items'=>array($item)
                    ];
                }
            }                     
            
            $values = array_values($data);
            usort($values, function ($a, $b) {
                return $a['sequence'] <=> $b['sequence'];
            });
            
            return $values;       
        }
        throw new Exception( t("No available data") );
    }

    public static function CookingList($ids=[],$language=KMRS_DEFAULT_LANGUAGE)
    {        

        $data = [];        
        if(is_array($ids) && count($ids)<=0){
            return $data;
        }

        $stmt = "
        SELECT         
        a.cook_id,
        IF(COALESCE(NULLIF(b.cooking_name, ''), '') = '', a.cooking_name, b.cooking_name) as cooking_name
        FROM {{cooking_ref}} a

        left JOIN (
			SELECT cook_id, cooking_name FROM {{cooking_ref_translation}} where language = ".q($language)."
		) b
		on a.cook_id = b.cook_id

        WHERE
        a.cook_id IN (".CommonUtility::arrayToQueryParameters($ids).")
        ";                   
        if($res=CCacheData::queryAll($stmt)){            
            foreach ($res as $items ){
                $data[$items['cook_id']] = $items['cooking_name'];
            }
        }
        return $data;
    }

    public static function IngredientsList($ids=[],$language=KMRS_DEFAULT_LANGUAGE)
    {        
        $data = [];
        if(is_array($ids) && count($ids)<=0){
            return $data;
        }
        
        $stmt = "
        SELECT         
        a.ingredients_id,
        IF(COALESCE(NULLIF(b.ingredients_name, ''), '') = '', a.ingredients_name, b.ingredients_name) as ingredients_name
        FROM {{ingredients}} a

        left JOIN (
			SELECT ingredients_id, ingredients_name FROM {{ingredients_translation}} where language = ".q($language)."
		) b
		on a.ingredients_id = b.ingredients_id

        WHERE
        a.ingredients_id IN (".CommonUtility::arrayToQueryParameters($ids).")
        ";                
        if($res=CCacheData::queryAll($stmt)){            
            foreach ($res as $items ){
                $data[$items['ingredients_id']] = $items['ingredients_name'];
            }
        }
        return $data;
    }

    public static function AddonList($ids=[],$language=KMRS_DEFAULT_LANGUAGE)
    {        
        $data = [];
        if(is_array($ids) && count($ids)<=0){
            return $data;
        }
        
        $stmt = "
        SELECT         
        a.sub_item_id,
        IF(COALESCE(NULLIF(b.sub_item_name, ''), '') = '', a.sub_item_name, b.sub_item_name) as sub_item_name
        FROM {{subcategory_item}} a

        left JOIN (
			SELECT sub_item_id, sub_item_name FROM {{subcategory_item_translation}} where language = ".q($language)."
		) b
		on a.sub_item_id = b.sub_item_id

        WHERE
        a.sub_item_id IN (".CommonUtility::arrayToQueryParameters($ids).")
        ";                
        if($res=CCacheData::queryAll($stmt)){            
            foreach ($res as $items ){
                $data[$items['sub_item_id']] = $items['sub_item_name'];
            }
        }
        return $data;
    }


    public static function orderIDsAttribtesByRef($order_reference_list=[],$language=KMRS_DEFAULT_LANGUAGE)
    {        
        $criteria = new CDbCriteria;
        $criteria->addInCondition("order_reference",$order_reference_list);
        $model = AR_kitchen_order::model()->findAll($criteria);

        $cooking_ref = []; $ingredients = []; $list_sub_items = [];
        foreach ($model as $items) {
            $attributes = !empty($items->attributes)?json_decode($items->attributes,true):'';
            $addons = !empty($items->addons)?json_decode($items->addons,true):'';     
            
            if(is_array($addons) && count($addons)>=1){
                $sub_item_ids = array_column($addons, 'sub_item_id');
                $sub_item_ids = array_filter($sub_item_ids);
                $sub_item_ids = array_values($sub_item_ids);                       
                $list_sub_items = array_merge($list_sub_items,$sub_item_ids);
            }                
            
            if(is_array($attributes) && count($attributes)>=1){
                foreach ($attributes as $value_attributes) {                        
                    if($value_attributes['meta_name']=="cooking_ref"){
                        if(!in_array($value_attributes['meta_id'],$cooking_ref)){
                            $cooking_ref[] = $value_attributes['meta_id'];
                        }                        
                    } else if ( $value_attributes['meta_name']=='ingredients'){
                        if(!in_array($value_attributes['meta_id'],$ingredients)){
                            $ingredients[] = $value_attributes['meta_id'];
                        }
                    }
                }
            }            
        }
        
        $list_cooking_ref = Ckitchen::CookingList($cooking_ref,$language);
        $list_ingredients = Ckitchen::IngredientsList($ingredients,$language);
        $list_sub_items = Ckitchen::AddonList($list_sub_items,$language);
        return [
            'list_cooking_ref'=>$list_cooking_ref,
            'list_ingredients'=>$list_ingredients,
            'list_sub_items'=>$list_sub_items,
        ];
    }
    
    public static function getKicthenItems($order_reference_list=[],$language=KMRS_DEFAULT_LANGUAGE)
    {
        $atts = self::orderIDsAttribtesByRef($order_reference_list,$language);        
        $list_cooking_ref = $atts['list_cooking_ref'];        
        $list_ingredients = $atts['list_ingredients'];
        $list_sub_items = $atts['list_sub_items'];
        
        $q = isset($filters['q'])?$filters['q']:'';
        $order_type = isset($filters['order_type'])?$filters['order_type']:'';
        $order_status = isset($filters['order_status'])?$filters['order_status']:'';        

        $data = array();
        $criteria = new CDbCriteria;
        $criteria->alias = "a";
        $criteria->select = "
        a.kitchen_order_id,
        a.is_completed,
        a.transaction_type,
        a.merchant_id,
        a.order_reference,   
        a.customer_name,     
        a.delivery_date,
        a.delivery_time,
        a.timezone,
        a.created_at,
        a.item_token,
        d.item_name,
        IF(COALESCE(NULLIF(e.item_name, ''), '') = '', d.item_name, e.item_name) as item_name,
        a.qty,
        a.item_status,
        a.special_instructions,
        a.addons,
        a.attributes,
        b.table_name,
        c.room_name,
        (
            select count(*) 
            from {{kitchen_order}}
            where order_reference = a.order_reference
            and item_status IN ('queue','in progress','ready','delayed')
        ) as total_pending
        ";
        
        $criteria->addInCondition("a.order_reference",$order_reference_list);

        $criteria->join="
		LEFT JOIN {{table_tables}} b on a.table_uuid = b.table_uuid 			
		LEFT JOIN {{table_room}} c on b.room_id = c.room_id 			
        LEFT JOIN {{item}} d on a.item_token = d.item_token 		
        
        left JOIN (
			SELECT item_id, item_name FROM {{item_translation}} where language = ".q($language)."
		) e
		on d.item_id = e.item_id
		";	                    
        $criteria->order = "a.kitchen_order_id DESC";                        
        if($model = AR_kitchen_order::model()->findAll($criteria)){                
            foreach ($model as $items) {                
                $order_reference = $items->order_reference;
                $attributes = !empty($items->attributes)?json_decode($items->attributes,true):'';
                $addons = !empty($items->addons)?json_decode($items->addons,true):'';     
                                
                if(is_array($attributes) && count($attributes)>=1){
                    foreach ($attributes as $index=>$value_attributes) {                        
                        if($value_attributes['meta_name']=="cooking_ref"){                            
                            $attributes[$index]['value'] = isset($list_cooking_ref[$value_attributes['meta_id']])?$list_cooking_ref[$value_attributes['meta_id']]:'';
                        } else if ( $value_attributes['meta_name']=='ingredients'){
                            $attributes[$index]['value']=isset($list_ingredients[$value_attributes['meta_id']])?$list_ingredients[$value_attributes['meta_id']]:'';
                        }
                    }
                }

                if(is_array($addons) && count($addons)>=1){
                    foreach ($addons as $index=>$value_addons) {                        
                        $addons[$index]['value'] = isset($list_sub_items[$value_addons['sub_item_id']])?$list_sub_items[$value_addons['sub_item_id']]:'';
                    }
                }
                               
                $item = array(
                    'kitchen_order_id'=>$items->kitchen_order_id,
                    'checked'=>false,
					'item_token'=>$items->item_token,
                    'item_name'=>CHtml::decode($items->item_name),
                    'qty'=>$items->qty,
                    'item_status'=>$items->item_status,
                    'item_status_pretty'=>t($items->item_status),
                    'special_instructions'=>$items->special_instructions,                    
                    'attributes'=>$attributes,
                    'addons'=>$addons,
				);
                if(array_key_exists($order_reference, $data)){                    
                    $data[$order_reference]["items"][] = $item;
                } else {
                    $data[$order_reference] = [
                        'order_reference'=>$order_reference,                        
                        'customer_name'=>$items->customer_name,
                        'transaction_type'=>$items->transaction_type,
                        'transaction_type_pretty'=>t($items->transaction_type),
                        'room_name'=>$items->room_name, 
                        'table_name'=>$items->table_name,                        
                        'delivery_date'=>$items->delivery_date,
                        'delivery_time'=>$items->delivery_time,
                        'timezone'=>$items->timezone,
                        'created_at'=>$items->created_at,
                        "request_time" => date("c",strtotime($items->created_at)),
                        'created_at_pretty'=>Date_Formatter::dateTime($items->created_at,"dd.MM.yyyy, HH:mm",true),		
                        'total_pending'=>intval($items->total_pending),                        
                        'items'=>array($item)
                    ];
                }
            }                  
                                        
            return $data;       
        }
        throw new Exception( t("No available data") );
    }    

    public static function getCurrentCount($merchant_id=0)
    {
        $count = 0;
        $stmt = "
        SELECT COUNT(DISTINCT order_reference) AS count
        FROM {{kitchen_order}}
        WHERE merchant_id=".q($merchant_id)."
        AND is_completed=0
        AND whento_deliver='now'        
        ";        
        if($res = CCacheData::queryRow($stmt)){
            $count = $res['count'];
        }
        return $count;
    }

    public static function getScheduledCount($merchant_id=0)
    {
        $count = 0;
        $stmt = "
        SELECT COUNT(DISTINCT order_reference) AS count
        FROM {{kitchen_order}}
        WHERE merchant_id=".q($merchant_id)."
        AND is_completed=0
        AND whento_deliver='schedule'        
        ";        
        if($res = CCacheData::queryRow($stmt)){
            $count = $res['count'];
        }
        return $count;
    }

    public static function getUnacknowledged($status='queue')
    {
        CommonUtility::mysqlSetTimezone();
        $stmt = "
        SELECT
        a.order_reference,
        a.customer_name,
        a.transaction_type,        
        b.merchant_uuid
        FROM {{kitchen_order}} a

        LEFT JOIN {{merchant}} b
        ON 
        a.merchant_id = b.merchant_id

        WHERE a.whento_deliver='now'
        AND a.item_status = ".q($status)."
        AND TIMESTAMPDIFF(SECOND, a.created_at, NOW()) > 180
        AND TIMESTAMPDIFF(SECOND, created_at, NOW()) <= 1800
        AND a.merchant_id IN (
            select merchant_id from {{merchant_meta}}
            where merchant_id=a.merchant_id 
            and meta_name='kitchen_repeat_notification'
            and meta_value=1
        )
        GROUP BY a.order_reference
        LIMIT 0,10
        ";
        if($res = CCacheData::queryAll($stmt)){
            return $res;
        }
        throw new Exception( t("No available data") );
    }

    public static function getScheduledOrderTomove($status='queue')
    {
        CommonUtility::mysqlSetTimezone();
        $stmt = "
        SELECT 
        a.order_reference,
        a.customer_name,
        a.transaction_type,
        STR_TO_DATE(CONCAT(a.delivery_date, ' ', a.delivery_time), '%Y-%m-%d %H:%i:%s') AS delivery_datetime,
        b.merchant_uuid

        FROM
        {{kitchen_order}} a

        LEFT JOIN {{merchant}} b
        ON 
        a.merchant_id = b.merchant_id

        WHERE a.item_status=".q($status)."        
        AND a.whento_deliver='schedule'        
        AND a.merchant_id IN (
            select merchant_id from {{merchant_meta}}
            where meta_name='scheduled_order_transition_time'            
            AND STR_TO_DATE(CONCAT(a.delivery_date, ' ', a.delivery_time), '%Y-%m-%d %H:%i:%s') <= DATE_ADD(NOW(), INTERVAL TIME_TO_SEC(meta_value) SECOND)
        )
        GROUP BY a.order_reference        
        ";
        if($res = CCacheData::queryAll($stmt)){
            return $res;
        }
        throw new Exception( t("No available data") );
    }    

    public static function setKitchenStatus($order_reference='',$status='',$channel='')
    {
        AR_kitchen_order::model()->updateAll(array(
            'item_status' =>$status,					
        ), "order_reference=:order_reference",[
            ":order_reference"=>$order_reference,
        ]);			

        if($status=="cancelled" && !empty($channel)){
            $title = t("New Canncelled Alert");
			$message = "There is a new cancelled order with order# {order_reference}.";
            $message_parameters = [
                '{order_reference}'=>$order_reference
            ];
            $noti = new AR_notifications;    							
            $noti->notication_channel = $channel;
            $noti->notification_event = Yii::app()->params->realtime['notification_event'] ;
            $noti->notification_type = 'cancelled_order';
            $noti->message = $message;		
            $noti->message_parameters = json_encode($message_parameters);
            $noti->meta_data = [
                'title'=>$title,                
            ];
            $noti->save();		

            $push = new AR_push;					
            $push->push_type = 'broadcast';
            $push->provider  = 'firebase';
            $push->channel_device_id = $channel;
            $push->platform = 'android';
            $push->title =  $title;
            $push->body = t($message,$message_parameters);
            $push->save();

            $push = new AR_push;					
            $push->push_type = 'broadcast';
            $push->provider  = 'firebase';
            $push->channel_device_id = $channel;
            $push->platform = 'ios';
            $push->title =  $title;
            $push->body = t($message,$message_parameters);
            $push->save();
        }
    }
    
    public static function sendtoKitchen($order_uuid='')
    {
        
        COrders::getContent($order_uuid,Yii::app()->language);
        $merchant_id = COrders::getMerchantId($order_uuid);		
        $data = COrders::getItems();
        $order = COrders::orderInfo(Yii::app()->language, date("Y-m-d") );
        $order_info = isset($order['order_info'])?$order['order_info']:'';
        $order_type = isset($order['order_info'])?$order['order_info']['order_type']:'';
        $order_id = isset($order_info['order_id'])?$order_info['order_id']:'';

        if(Ckitchen::getByReference($order_id)){
            throw new Exception( t("Order already exist in kitchen table") );           
        }        

        $merchant_id = COrders::getMerchantId($order_uuid);
		$merchant_info = CMerchants::get($merchant_id);        
        
        $order_table_data = [];
        if($order_type=="dinein"){
            $order_table_data = COrders::orderMeta(['table_id','room_id','guest_number']);	
            $room_id = isset($order_table_data['room_id'])?$order_table_data['room_id']:0;							
            $table_id = isset($order_table_data['table_id'])?$order_table_data['table_id']:0;							
            try {
                $table_info = CBooking::getTableByID($table_id);
                $order_table_data['table_name'] = $table_info->table_name;
            } catch (Exception $e) {                
            }				
            try {
                $room_info = CBooking::getRoomByID($room_id);					
                $order_table_data['room_name'] = $room_info->room_name;
            } catch (Exception $e) {                
            }				
        }					
        
        $kitchen_uuid = '';
        $order_reference = '';
        $whento_deliver = '';
        $merchant_uuid = $merchant_info->merchant_uuid;
        $merchant_id = '';

        if(is_array($data) && count($data)>=1){
            foreach ($data as $items) {
                $order_reference = isset($order_info['order_id'])?$order_info['order_id']:'';
                $whento_deliver = isset($order_info['whento_deliver'])?$order_info['whento_deliver']:'';
                $kitchen_uuid = $merchant_info->merchant_uuid;
                $modelKitchen = new AR_kitchen_order();
                $modelKitchen->order_reference = isset($order_info['order_id'])?$order_info['order_id']:'';
                $modelKitchen->merchant_uuid = $kitchen_uuid;
                $modelKitchen->order_ref_id = $items['item_row'];
                $modelKitchen->merchant_id = isset($order_info['merchant_id'])?$order_info['merchant_id']:'';
                $merchant_id = $modelKitchen->merchant_id;
                $modelKitchen->table_uuid = isset($order_table_data['table_uuid'])?$order_table_data['table_uuid']:'';
                $modelKitchen->room_uuid = isset($order_table_data['room_uuid'])?$order_table_data['room_uuid']:'';
                $modelKitchen->item_token = $items['item_token'];
                $modelKitchen->qty = $items['qty'];
                $modelKitchen->special_instructions = $items['special_instructions'];
                $modelKitchen->customer_name = isset($order_info['customer_name'])?$order_info['customer_name']:'';
                $modelKitchen->transaction_type = isset($order_info['order_type'])?$order_info['order_type']:'';
                $modelKitchen->timezone =  isset($order_info['timezone'])?$order_info['timezone']:'';
                $modelKitchen->whento_deliver = isset($order_info['whento_deliver'])?$order_info['whento_deliver']:'';
                $modelKitchen->delivery_date = isset($order_info['delivery_date'])?$order_info['delivery_date']:'';
                $modelKitchen->delivery_time = isset($order_info['delivery_time'])?$order_info['delivery_time']:'';

                $addons = []; $attributes =[];
                if(is_array($items['addons']) && count($items['addons'])>=1){
                    foreach ($items['addons'] as $addons_key=> $addons_items) {								
                        $addonItems = isset($addons_items['addon_items'])?$addons_items['addon_items']:'';
                        if(is_array($addonItems) && count($addonItems)>=1 ){
                            foreach ($addonItems as $addons_items_val) {									
                                $addons[] = [
                                    'subcat_id'=>$addons_items['subcat_id'],
                                    'sub_item_id'=>$addons_items_val['sub_item_id'],
                                    'qty'=>$addons_items_val['qty'],
                                    'multi_option'=>$addons_items_val['multiple'],
                                ];
                            }
                        }
                    }
                }
                $modelKitchen->addons = json_encode($addons);

                if(is_array($items['attributes_raw']) && count($items['attributes_raw'])>=1){
                    foreach ($items['attributes_raw'] as $attributes_key=> $attributes_items) {	
                        if(is_array($attributes_items) && count($attributes_items)>=1 ){
                            foreach ($attributes_items as $meta_id=> $attributesItems) {									
                                $attributes[] = [
                                    'meta_name'=>$attributes_key,
                                    'meta_id'=>$meta_id
                                ];
                            }
                        }
                    }
                }
                
                $modelKitchen->attributes = json_encode($attributes);	
                $modelKitchen->sequence = CommonUtility::getNextAutoIncrementID('kitchen_order');                
                $modelKitchen->save();
            }
        }        
        
        // SEND NOTIFICATIONS
        if(!empty($kitchen_uuid)){
           AR_kitchen_order::SendNotifications([
              'kitchen_uuid'=>$kitchen_uuid,
              'order_reference'=>$order_reference,
              'whento_deliver'=>$whento_deliver,
              'merchant_uuid'=>$merchant_uuid,
              'merchant_id'=>$merchant_id
           ]);
        }
        // SEND NOTIFICATIONS

        return true;
    }

    public static function getKicthenItemsByID($kitchen_order_id='',$language=KMRS_DEFAULT_LANGUAGE)
    {
        $stmt = "
        SELECT 
        a.item_token,     
        a.table_uuid,
        a.item_status,
        b.item_id,   
        IF(COALESCE(NULLIF(c.item_name, ''), '') = '', b.item_name, c.item_name) as item_name

        FROM {{kitchen_order}} a
        LEFT JOIN {{item}} b
        ON 
        a.item_token = b.item_token

        left JOIN (
           SELECT item_id, item_name FROM {{item_translation}} where language=".q($language)."
        ) c 
        on b.item_id = c.item_id

        WHERE kitchen_order_id=".q($kitchen_order_id)."
        ";
        if($res = CCacheData::queryRow($stmt)){
            return $res;
        }
        throw new Exception( t("No available data") );
    }

}
// end class