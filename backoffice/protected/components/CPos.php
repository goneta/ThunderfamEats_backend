<?php
class CPos
{
	
	public static function get($merchant_id='',$status='draft', $service_code='pos',$date_created='')
	{				
		$model = AR_ordernew::model()->find("merchant_id=:merchant_id AND status=:status AND DATE_FORMAT(date_created,'%Y-%m-%d')=:date_created",array(
			':merchant_id'=>intval($merchant_id),
			':status'=>$status,	
			':date_created'=>$date_created
		));
		if($model){
			return $model;
		}
		return false;
	}
	
	public static function createOrder($merchant_id=0,$transaction_type='')
	{
		$order_uuid = '';
		$pos_code = AttributesTools::PosCode();		
		if($model = CPos::get($merchant_id,'draft',$transaction_type,date("Y-m-d"))){
			return $model->order_uuid;
		} else {
			
			/*GET TAX*/
			$tax_settings = array(); $tax_delivery = array(); $tax = 0;
			try {
				$tax_settings = CTax::getSettings($merchant_id);				
				$tax_type = $tax_settings['tax_type'];
				
				if($tax_settings['tax_type']=="multiple"){
					$tax_delivery = CTax::taxForDelivery($merchant_id,$tax_settings['tax_type']);		
				} else $tax_delivery = $tax_settings['tax'];
								
				if($tax_type=="standard" || $tax_type=="euro"){
					if(is_array($tax_settings['tax']) && count($tax_settings['tax'])>=1){
						foreach ($tax_settings['tax'] as $tax_item_cond) {
							$tax = isset($tax_item_cond['tax_rate'])?$tax_item_cond['tax_rate']:0;
						}
					}
				}
			} catch (Exception $e) {					
			}
													
			$order = new AR_ordernew();
			$order->scenario = $pos_code;
			$order->merchant_id = $merchant_id;			
			$order->service_code = $transaction_type ;
			$order->payment_code = "cash" ;
			$order->order_uuid = CommonUtility::createUUID("{{ordernew}}",'order_uuid');
			$order->whento_deliver = "now";
			$order->delivery_date = CommonUtility::dateNow();
			
			$order->tax_type = isset($tax_settings['tax_type'])?$tax_settings['tax_type']:'';
			$order->tax_use = $tax_settings;	
			$order->tax = $tax;
			$order->tax_for_delivery = $tax_delivery;			
															
			if($order->save()){
				return $order->order_uuid;
			} else throw new Exception( CommonUtility::parseModelErrorToString($order->getErrors()) );
		}	
	}
	
	public static function resetPos($order_uuid='')
	{
		$model = AR_ordernew::model()->find("order_uuid=:order_uuid",array(
		 ':order_uuid'=>$order_uuid
		));
		if($model){
			$model->scenario = "reset_cart";
			$model->delete();
			return true;
		}
		throw new Exception("Cart items not found");
	}

	public static function getSendOrderItems($merchant_id=0,$language=KMRS_DEFAULT_LANGUAGE)
	{
		$prefix = Yii::app()->params->tableside_prefix;
		$data = [];
		$stmt = "
		SELECT 
		a.id,
		a.cart_uuid, 
		a.item_token,
		a.qty,
		a.special_instructions,
		b.item_id,
		b.item_name as item_name_orig,
		c.item_id,
		c.item_name,
		d.order_ref_id,
		d.item_status

		FROM {{cart}} a

		LEFT JOIN {{item}} b
		ON 
		a.item_token = b.item_token

		left JOIN (
			SELECT item_id, item_name FROM {{item_translation}} where language = ".q($language)."
		) c 
		on b.item_id = c.item_id

		left JOIN (
			SELECT order_ref_id, item_status FROM {{kitchen_order}} 
		) d
		on a.id = d.order_ref_id

		WHERE a.merchant_id=".q($merchant_id)."
		AND EXISTS  (
			SELECT 1 from {{cart}}
			where 
			cart_uuid = a.cart_uuid 
			and order_reference like '$prefix-%'
		)		
		";
		if($res = CCacheData::queryAll($stmt)){
			foreach ($res as $items) {		
				$item_name = !empty($items['item_name'])?$items['item_name']:$items['item_name_orig'];				
				$data[$items['cart_uuid']][] = [
					'id'=>$items['id'],
					'item_token'=>$items['item_token'],
					'item_name'=>CHtml::decode($item_name),
					'qty'=>$items['qty'],
					'special_instructions'=>$items['special_instructions'],
					'status'=>$items['item_status']
				];
			}			
		}		
		return $data;
	}

	public static function getPendingItems($merchant_id=0,$language=KMRS_DEFAULT_LANGUAGE)
	{

		$data = [];
		$stmt = "
		SELECT 
		a.id,
		a.cart_uuid, 
		a.item_token,
		a.qty,
		a.special_instructions,
		b.item_id,
		b.item_name as item_name_orig,
		c.item_id,
		c.item_name		

		FROM {{cart}} a

		LEFT JOIN {{item}} b
		ON 
		a.item_token = b.item_token

		left JOIN (
			SELECT item_id, item_name FROM {{item_translation}} where language = ".q($language)."
		) c 
		on b.item_id = c.item_id	

		WHERE a.merchant_id=".q($merchant_id)."
		AND EXISTS  (
			SELECT 1 from {{cart}}
			where 
			cart_uuid = a.cart_uuid 			
		)		
		";
		if($res = CCacheData::queryAll($stmt)){
			foreach ($res as $items) {			
				$item_name = !empty($items['item_name'])?$items['item_name']:$items['item_name_orig'];							
				$data[$items['cart_uuid']][] = [
					'id'=>$items['id'],
					'item_token'=>$items['item_token'],
					'item_name'=>CHtml::decode($item_name),
					'qty'=>$items['qty'],
					'special_instructions'=>$items['special_instructions'],
					'status'=>t('pending')
				];
			}			
		}		
		return $data;		
	}

	public static function getSendOrder($transaction_type='',$merchant_id=0,$filters=array(),$language=KMRS_DEFAULT_LANGUAGE)
	{

		$data = [];
		$in_query = '';
		$order_type = isset($filters['order_type'])?$filters['order_type']:'';
		if(is_array($order_type) && count($order_type)>=1){			
			$order_type = CommonUtility::arrayToQueryParameters($order_type);
			$in_query = " AND transaction_type IN ($order_type)";
		}

		if($transaction_type=="send_orders"){
			$query = "
			AND a.send_order=1		    
			";
		} else if ( $transaction_type=="hold_orders"){
			$query = "
			AND a.hold_order=1		    
			";
		}

		$stmt = "
		SELECT 
		a.*,
		b.table_uuid as ref_table_uuid,
		IFNULL(b.table_name,'') as table_name,
		c.item_id,		
		IF(COALESCE(NULLIF(d.item_name, ''), '') = '', c.item_name, d.item_name) as item_name,
		e.item_status,
		IFNULL(f.status,'available') as table_status

		FROM 
		{{cart}} a

		LEFT JOIN {{item}} c
		ON 
		a.item_token = c.item_token

		left JOIN (
			SELECT item_id, item_name FROM {{item_translation}} where language = ".q($language)."
		) d 
		on c.item_id = d.item_id

		left JOIN (
			SELECT table_uuid, table_name FROM {{table_tables}}
		) b
		on a.table_uuid = b.table_uuid	
		
		left JOIN (
			SELECT order_ref_id, item_status FROM {{kitchen_order}} 			
		) e
		on a.cart_row = e.order_ref_id

		left JOIN (
			SELECT table_uuid,status  FROM {{table_status}} 
			limit 0,1
		) f
		on a.table_uuid = f.table_uuid
		
		WHERE a.merchant_id = ".q($merchant_id)."
		$query
		$in_query
		ORDER BY a.id ASC
		";						
		if($model = AR_cart::model()->findAllBySql($stmt)){
			foreach ($model as $items) {
				$atts = CCart::getAttributesAll($items->cart_uuid,[
					'customer_name','timezone','guest_number'
				]);			

				$cart_uuid = $items->cart_uuid;
				$item = [
					'id'=>$items->id,
					'item_token'=>$items->item_token,
					'item_name'=>chtml::decode($items->item_name),
					'qty'=>$items->qty,
					'special_instructions'=>$items->special_instructions,
					'status_raw'=>!is_null($items->item_status)?$items->item_status:t('pending'),
					'status'=>!is_null($items->item_status)?t($items->item_status):t('pending'),
				];

				if(array_key_exists($cart_uuid, $data)){
					$data[$cart_uuid]["items"][] = $item;
				} else {
					$data[$cart_uuid] = [
						'cart_uuid'=>$items->cart_uuid,
						'order_reference'=>$items->order_reference,
						'hold_order_reference'=>$items->hold_order_reference,
						'total'=>$items->total,
						'total_pretty'=>Price_Formatter::formatNumber($items->total),
						'table_uuid'=>$items->table_uuid,
						'table_name'=>$items->table_name,
						'customer_name'=>isset($atts['customer_name'])?$atts['customer_name']:'',					
						'guest_number'=>isset($atts['guest_number'])?$atts['guest_number']:'',	
						'transaction_type_raw'=>$items->transaction_type,
						'transaction_type'=>t($items->transaction_type),
						'timezone'=>isset($atts['timezone'])?$atts['timezone']:Yii::app()->timezone,
						'date'=>date("c",strtotime($items->date_created)),
						'date_pretty'=>Date_Formatter::dateTime($items->date_created,"dd.MM.yyyy, HH:mm",true),
						'table_status'=>$items->table_status,
						'status_class'=>str_replace(" ","-",$items->table_status),
						'items'=>array($item)
					];
				}				
			}
			return $data;			
		}
		throw new Exception( t("No available data") );
	}

	public static function getCartOrderRefence($cart_uuid='')
	{
		$prefix = Yii::app()->params->tableside_prefix;
		$criteria = new CDbCriteria;		
		$criteria->addCondition('cart_uuid=:cart_uuid');
		$criteria->params = [
			':cart_uuid'=>$cart_uuid,			
		];
		$criteria->addSearchCondition("order_reference","$prefix-");							
		$model = AR_cart::model()->find($criteria);	
		if($model){
			return $model;
		}
		return false;
	}

	public static function getTableneworder($merchant_id=0)
	{
		$prefix = Yii::app()->params->tableside_prefix;
		$merchant_id = Yii::app()->merchant->merchant_id;                    
		$criteria = new CDbCriteria;		
		$criteria->alias = "a";
		$criteria->select = "a.*,b.table_name,c.room_name";
		$criteria->addCondition("a.merchant_id=:merchant_id 
		AND a.is_view=:is_view AND a.send_order=:send_order
		AND DATE_FORMAT(a.date_created,'%Y-%m-%d')=:date_created
		");
		$criteria->params = [
			':merchant_id'=>$merchant_id,
			':is_view'=>0,
			':send_order'=>1,
			':date_created'=>CommonUtility::dateOnly()
		];
		$criteria->join='
		LEFT JOIN {{table_tables}} b on a.table_uuid = b.table_uuid 			
		LEFT JOIN {{table_room}} c on b.room_id = c.room_id 			
		';	            
		$criteria->addSearchCondition("a.order_reference","$prefix-");					
		$criteria->order = "a.id ASC";						
		if($model = AR_cart::model()->findAll($criteria)){
			$data = [];
			foreach ($model as $item) {
				$data[] = [
					'title'=>t("You have new table order"),
					'message'=>t("Table #{room_name}-{table_name}",[
						'{room_name}'=>$item->room_name,
						'{table_name}'=>$item->table_name,						
					]),					
					'metadata'=>[
						'notification_type'=>"order",
						'cart_uuid'=>$item->cart_uuid
					]
				];
			}
			return $data;
		}
		throw new Exception( t("No available data") );
	}

	public static function getPendingTablerequest($merchant_id=0)
	{
		$criteria = new CDbCriteria;		
		$criteria->alias = "a";
		$criteria->select = "a.*,b.table_name,c.room_name";
		$criteria->addCondition("a.merchant_id=:merchant_id AND a.request_status=:request_status
		  AND DATE_FORMAT(a.request_time,'%Y-%m-%d')=:request_time
		");
		$criteria->params = [
			':merchant_id'=>$merchant_id,
			':request_status'=>"pending",
			':request_time'=>CommonUtility::dateOnly()
		];
		$criteria->join='
		LEFT JOIN {{table_tables}} b on a.table_uuid = b.table_uuid 			
		LEFT JOIN {{table_room}} c on b.room_id = c.room_id 			
		';	            
		$criteria->order = "a.request_id ASC";		
		if($model = AR_customer_request::model()->findAll($criteria)){
			$data = [];
			foreach ($model as $item) {
				$data[] = [
					'title'=>t("Request - {request_time}",[
						'{request_time}'=>$item->request_item
					]),
					'message'=>t("Table #{room_name}-{table_name}",[
						'{room_name}'=>$item->room_name,
						'{table_name}'=>$item->table_name,						
					]),					
					'metadata'=>[
						'notification_type'=>"call_staff",
						'table_uuid'=>$item->table_uuid,
						'request_id'=>$item->request_id
					]
				];
			}
			return $data;
		}
		throw new Exception( t("No available data") );
	}

	public static function getPendingRequestList($merchant_id=0)
	{		
		$data = array();
		$criteria = new CDbCriteria;		
		$criteria->alias = "a";
		$criteria->select = "a.*,b.table_name,c.room_name";
		$criteria->addCondition("a.merchant_id=:merchant_id AND a.request_status=:request_status
		  AND DATE_FORMAT(a.request_time,'%Y-%m-%d')=:request_time
		");
		$criteria->params = [
			':merchant_id'=>$merchant_id,
			':request_status'=>"pending",
			':request_time'=>CommonUtility::dateOnly()
		];
		$criteria->join='
		LEFT JOIN {{table_tables}} b on a.table_uuid = b.table_uuid 			
		LEFT JOIN {{table_room}} c on b.room_id = c.room_id 			
		';	            
		$criteria->order = "a.request_id DESC";
		if($model = AR_customer_request::model()->findAll($criteria)){
			foreach ($model as $items) {				
				$table_uuid = $items->table_uuid;
				$item = array(
					'checked'=>false,
					"request_id" => $items->request_id,
					'qty'=>$items->qty,		
					'request_item'=>$items->request_item,					
					"request_status" => $items->request_status
				);
				if(array_key_exists($table_uuid, $data)){
					$data[$table_uuid]["items"][] = $item;
				} else {
					$data[$table_uuid] = [
						'loading'=>false,
						'transaction_type'=>$items->transaction_type,
						'transaction_type_pretty'=>t($items->transaction_type),
						'table_uuid'=>$table_uuid,
						'table_name'=>$items->table_name,
						'room_name'=>$items->room_name,
						'timezone'=>$items->timezone,
						"request_time" => date("c",strtotime($items->request_time)),
					    'request_time_pretty'=>Date_Formatter::dateTime($items->request_time,"dd.MM.yyyy, HH:mm",true),		
						'items'=>array($item)
					];
				}
			}
			return $data;
		}		
		throw new Exception( t("No available data") );
	}

	public static function getTableExistingRequest($merchant_id=0,$cart_uuid='',$table_uuid='',$request_items=[])
	{
        try {
			$criteria = new CDbCriteria;		
			$criteria->condition = "merchant_id=:merchant_id AND cart_uuid=:cart_uuid 
			AND table_uuid=:table_uuid
			AND request_status=:request_status
			AND DATE_FORMAT(request_time,'%Y-%m-%d')=:request_time
			";
			$criteria->params = [
				':merchant_id'=>$merchant_id,
				':cart_uuid'=>$cart_uuid,
				':table_uuid'=>$table_uuid,
				':request_status'=>'pending',
				':request_time'=>CommonUtility::dateOnly()
			];
			$criteria->addInCondition("request_item",$request_items);		
			if($model = AR_customer_request::model()->find($criteria)){
				return $model;
			} else return false;
	    } catch (Exception $e) {			
			return false;
		}		
	}

	public static function getItemStatus($order_reference='')
	{
		$data = [];
        $model = AR_kitchen_order::model()->findAll("order_reference=:order_reference",[
            ':order_reference'=>$order_reference
        ]);
        if($model){
            foreach ($model as $items) {
                $data[$items->order_ref_id] = [
                    'item_status_raw'=>$items->item_status,
                    'item_status'=>t($items->item_status),
                ];
            }
        }
        return $data;
	}

	public static function getPOSOrder($cart_uuid='',$language=KMRS_DEFAULT_LANGUAGE)
	{

		$data = [];

		$atts = CCart::getAttributesAll($cart_uuid,[
			'customer_name','timezone','guest_number'
		]);	

		$addons = CCart::getAddons($cart_uuid,$language);		
		$cooking = CCart::getCooking($cart_uuid,$language);	
		$ingredients = CCart::getIngredients($cart_uuid,$language);			
		
		CommonUtility::mysqlSetTimezone();
		$stmt = "
		SELECT 
		a.*,
		b.table_uuid as ref_table_uuid,
		IFNULL(b.table_name,'') as table_name,
		IFNULL(g.room_name,'') as room_name,
		c.item_id,		
		IF(COALESCE(NULLIF(d.item_name, ''), '') = '', c.item_name, d.item_name) as item_name,
		e.item_status,
		IFNULL(f.status,'available') as table_status,		
        (
		  select 
		  IF(COALESCE(NULLIF(size_name, ''), '') = '', original_size_name, size_name) as size_name
		  from {{view_item_lang_size}}
		  where 
		  item_size_id = a.item_size_id
		  and
		  language=".q($language)."
		) as size_name,

		CASE 
        WHEN CURDATE() BETWEEN h.discount_start AND h.discount_end THEN
            CASE 
                WHEN h.discount_type = 'fixed' THEN 
                    h.price - h.discount
                WHEN h.discount_type = 'percentage' THEN 
                    h.price - (h.price * h.discount / 100)
                ELSE 
                    h.price
            END
        ELSE 
            h.price
        END AS item_price

		FROM 
		{{cart}} a

		LEFT JOIN {{item}} c
		ON 
		a.item_token = c.item_token

		left JOIN (
			SELECT item_id, item_name FROM {{item_translation}} where language = ".q($language)."
		) d 
		on c.item_id = d.item_id

		left JOIN (
			SELECT room_id, table_uuid, table_name FROM {{table_tables}}
		) b
		on a.table_uuid = b.table_uuid	
		
		left JOIN (
			SELECT order_ref_id, item_status FROM {{kitchen_order}} 			
		) e
		on a.cart_row = e.order_ref_id

		left JOIN (
			SELECT table_uuid,status  FROM {{table_status}} 
			limit 0,1
		) f
		on a.table_uuid = f.table_uuid

		left JOIN (
			SELECT room_id, room_name FROM {{table_room}}
		) g
		on b.room_id = g.room_id	

		left JOIN (
			SELECT item_size_id,price,discount,discount_type,discount_start,discount_end
			FROM {{item_relationship_size}} 			
		) h
		on a.item_size_id = h.item_size_id
		
		WHERE a.cart_uuid = ".q($cart_uuid)."

		ORDER BY a.id ASC
		";							
		if($model = AR_cart::model()->findAllBySql($stmt)){						
			foreach ($model as $items) {	
								
				$cart_uuid = $items->cart_uuid;
				$cart_row = $items->cart_row;				

				$item = [
					'id'=>$items->id,
					'item_token'=>$items->item_token,
					'item_name'=>chtml::decode($items->item_name),
					'size_name'=>$items->size_name,
					'qty'=>$items->qty,
					'item_price'=>$items->item_price,
					'item_price_pretty'=>Price_Formatter::formatNumber($items->item_price),
					'special_instructions'=>$items->special_instructions,
					'status_raw'=>!is_null($items->item_status)?$items->item_status:t('pending'),
					'status'=>!is_null($items->item_status)?t($items->item_status):t('pending'),
					'addons'=>isset($addons[$cart_row])?$addons[$cart_row]:'',
					'cooking'=>isset($cooking[$cart_row])?$cooking[$cart_row]:'',
					'ingredients'=>isset($ingredients[$cart_row])?$ingredients[$cart_row]:'',
				];

				if(array_key_exists($cart_uuid, $data)){
					$data[$cart_uuid]["items"][] = $item;
				} else {
					$data[$cart_uuid] = [
						'cart_uuid'=>$items->cart_uuid,
						'order_reference'=>$items->order_reference,
						'hold_order_reference'=>$items->hold_order_reference,
						'total'=>$items->total,
						'total_pretty'=>Price_Formatter::formatNumber($items->total),
						'table_uuid'=>$items->table_uuid,
						'table_name'=>$items->table_name,
						'room_name'=>$items->room_name,
						'customer_name'=>isset($atts['customer_name'])?$atts['customer_name']:'',					
						'guest_number'=>isset($atts['guest_number'])?$atts['guest_number']:'',	
						'transaction_type_raw'=>$items->transaction_type,
						'transaction_type'=>t($items->transaction_type),
						'timezone'=>isset($atts['timezone'])?$atts['timezone']:Yii::app()->timezone,
						'date'=>date("c",strtotime($items->date_created)),
						'date_pretty'=>Date_Formatter::dateTime($items->date_created,"dd.MM.yyyy, HH:mm",true),
						'table_status'=>$items->table_status,
						'status_class'=>str_replace(" ","-",$items->table_status),
						'items'=>array($item)
					];
				}				
			}
			return $data;			
		}
		throw new Exception( t("No available data") );
	}	
	
}
/*end class*/