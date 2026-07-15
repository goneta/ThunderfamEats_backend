<?php
class AOrders
{

	private static $request_from = null;

	public static function setRequestfrom($data=''){
		self::$request_from = $data;
	}

	public static function getRequestfrom(){
		return self::$request_from ?? null;
	}

	public static function getOrderAll($merchant_id=0, $status=array(), $schedule=false, $date='',$datetime='',$filter=array(), $limit=100)
	{		
		
		$merchant_id = Yii::app()->merchant->merchant_id;
		$settings = OptionsTools::find(array('merchant_order_critical_mins'),$merchant_id);
		$critical_mins = isset($settings['merchant_order_critical_mins'])?$settings['merchant_order_critical_mins']:0;
		$critical_mins = intval($critical_mins);    
		
		$status_in = AOrders::getOrderTabsStatus('new_order');  

		$criteria = new CDbCriteria;
		$criteria->select = "order_id,order_uuid,merchant_id,
		client_id,status,payment_status,service_code,formatted_address,
		whento_deliver,delivery_date,delivery_time,delivery_time_end,
		is_view,is_critical,date_created,
		(
		 select sum(qty) 
		 from {{ordernew_item}}
		 where order_id = t.order_id
		) as total_items,
		
	    IF(whento_deliver='now', 
	      TIMESTAMPDIFF(MINUTE, date_created, NOW())
	    , 
	     TIMESTAMPDIFF(MINUTE, concat(delivery_date,' ',delivery_time), NOW())
	    ) as min_diff
		
		";		
		$criteria->order = "order_id ASC";
		
		if($schedule){
			$status_scheduled = (array) $status;				
			$status_accepted = AOrders::getOrderTabsStatus('order_processing');	
			if($status_accepted){				
				foreach ($status_accepted as $status_accepted_val) {
					array_push($status_scheduled,$status_accepted_val);
				}
			}
						
			$criteria->condition = "merchant_id=:merchant_id AND whento_deliver=:whento_deliver";		    
			$criteria->params  = array(
			  ':merchant_id'=>intval($merchant_id),		  
			  ':whento_deliver'=>"schedule"
			);						
			$criteria->addInCondition('status', (array) $status_scheduled );					
			$criteria->addCondition('delivery_date > "'.$date.'" ');
		} else {
			$criteria->addCondition('merchant_id =:merchant_id');
			$criteria->params = array(':merchant_id' => intval($merchant_id) );
			$criteria->addInCondition('status',(array) $status );		
			$criteria->addSearchCondition('delivery_date', $date );
		}
		
		if(is_array($filter) && count($filter)>=1){
			
			if(isset($filter['search_filter'])){
				$search_filter = trim($filter['search_filter']);
				if(is_numeric($search_filter) && !empty($search_filter)){
				    $criteria->addSearchCondition('order_id', $search_filter );
				} else if (!empty($search_filter)) {									
					$criteria->addCondition("
					 order_id IN (
					   select order_id from {{ordernew_meta}}
					   where meta_name='customer_name'
					   and meta_value LIKE ".q("%$search_filter%")."
					 )
					");
				}
			}
			
			if(isset($filter['order_type'])){
				if(is_array($filter['order_type']) && count($filter['order_type'])>=1){					
					$criteria->addInCondition('service_code',(array) $filter['order_type'] );
				}
			}
			if(isset($filter['payment_status'])){
				if(is_array($filter['payment_status']) && count($filter['payment_status'])>=1){					
					$criteria->addInCondition('payment_status',(array) $filter['payment_status'] );
				}
			}
			if(isset($filter['sort'])){
				if(!empty($filter['sort'])){
					$sort = $filter['sort'];
					switch ($sort) {
						case "order_id_asc":		
						    $criteria->order = "order_id ASC";					
							break;											
						case "order_id_desc":
							$criteria->order = "order_id DESC";
							break;												
						case "delivery_time_asc":
							$criteria->order = "delivery_time ASC";
							break;	
						case "delivery_time_desc":
							$criteria->order = "delivery_time DESC";
							break;		
					}
				}
			}
		}
		
		$criteria->limit = $limit;
		//dump($criteria);die();
		$model=AR_ordernew::model()->findAll($criteria);				
		if($model){							
			$data = array(); $all_merchant = array(); $all_order = array();
			foreach ($model as $item) {
				$delivery_date = '';
				$all_merchant[] = $item->merchant_id;
				$all_order[] = $item->order_id;			
				if($item->whento_deliver=="now"){
			    	$delivery_date = t("Asap");
			    } else {
			    	if($item->delivery_date==$date){
			    		$date = Date_Formatter::Time( $item->delivery_date." ".$item->delivery_time );
				    	$delivery_date = t("Due at [delivery_date], Today",array(
				    	 '[delivery_date]'=>$date
				    	));
			    	} else {
				    	$date = Date_Formatter::dateTime( $item->delivery_date." ".$item->delivery_time );
				    	$delivery_date = t("Scheduled at [delivery_date]",array(
				    	 '[delivery_date]'=>$date
				    	));
			    	}
			    }
			    
			    $items = t("[item_count] items",array('[item_count]'=>$item->total_items));
			    if($item->total_items<=1){
			    	$items = t("[item_count] item",array('[item_count]'=>$item->total_items));
			    }
			    
			    $is_critical =  0;			    
			    if($item->whento_deliver=="schedule"){
		        	if($item->min_diff>0){
		        		$is_critical = true;
		        	}
		        } else if ($critical_mins>0 && $item->min_diff>$critical_mins && in_array($item->status,(array)$status_in) ) {
		        	$is_critical = true;
		        }	    
			    /*if($item->whento_deliver=="schedule"){
			    	$delivery_datetime = $item->delivery_date ." ".$item->delivery_time;			    	
			    	$delivery_datetime = date("Y-m-d g:i:s a",strtotime($delivery_datetime));			    	
			    	$diff = CommonUtility::dateDifference($delivery_datetime,$datetime);
			    	if(is_array($diff) && count($diff)>=1){
			    		$is_critical = 1;
			    	}
			    } elseif ($item->whento_deliver=="now"){			    	
			    	$delivery_datetime = date("Y-m-d g:i:s a",strtotime($item->date_created));
			    	$diff = CommonUtility::dateDifference($delivery_datetime,$datetime);
			    	if(is_array($diff) && count($diff)>=1){
			    		if($diff['hours']>0){
			    			$is_critical = 1;
			    		}
			    		if($diff['minutes']>10){
			    			$is_critical = 1;
			    		}
			    	}
			    }*/
			    
			    
				$data[]=array(
				  'order_name'=>t("Order #[order_id]",array('[order_id]'=>$item->order_id)),
				  'order_id'=>$item->order_id,
				  'order_uuid'=>$item->order_uuid,
				  'client_id'=>$item->order_id,
				  'status'=>$item->status,
				  'payment_status'=>$item->payment_status,
				  'service_code'=>$item->service_code,
				  'formatted_address'=>$item->formatted_address,
				  'delivery_date'=>$delivery_date,
				  'total_items'=>$items,
				  'is_view'=>$item->is_view,
				  'is_critical'=>$is_critical
				);
			}
			return array(
			 'data'=>$data,
			 'all_merchant'=>$all_merchant,
			 'all_order'=>$all_order,
			 'total'=>count($model)
			);
		}
		throw new Exception( 'No results' );
	}		
	
	public static function getOrderMeta($order_id=array())
	{
		$criteria = new CDbCriteria;		
		$criteria->order = "order_id ASC";				
		$criteria->addColumnCondition(array('meta_name' => 'customer_name'));
		$criteria->addInCondition('order_id', (array)$order_id );		
		$model=AR_ordernew_meta::model()->findAll($criteria);		
		if($model){
			$data = array();
			foreach ($model as $item) {
				$data[$item->order_id][$item->meta_name] = $item->meta_value;
			}
			return $data;
		}
		return false;
	}
	
	public static function getAllTabsStatus()
	{
		$new_order = AOrders::getOrderTabsStatus("new_order");
		$order_processing = AOrders::getOrderTabsStatus("order_processing");
		$order_ready = AOrders::getOrderTabsStatus("order_ready");
		$completed_today = AOrders::getOrderTabsStatus("completed_today");
		return array(
		  'new_order'=>$new_order,
		  'order_processing'=>$order_processing,
		  'order_ready'=>$order_ready,
		  'completed_today'=>$completed_today,
		);
	}
	
	public static function getOrderTabsStatus($group_name='')
	{
		$stmt="
		SELECT description as status
		FROM {{order_status}}
		WHERE 
		stats_id IN (
		 select stats_id from {{order_settings_tabs}}
		 where group_name =".q($group_name)."
		)
		";	
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
			$data = array();
			foreach ($res as $val) {				
				array_push($data,$val['status']);
			}
			return $data;
		}
		return false;
	}
	
	public static function getOrderButtons($group_name='', $order_type='')
	{		
		$criteria = new CDbCriteria;
		$criteria->order = "id ASC";						
		if($order_type){
			$criteria->addCondition("group_name=:group_name AND order_type=:order_type");
			$criteria->params = array(
			  ':group_name'=>$group_name,
			  ':order_type'=>trim($order_type)
			);
		} else $criteria->addColumnCondition(array('group_name' => $group_name ));
		
		$model = AR_order_settings_buttons::model()->findAll($criteria);	
		if($model){
			$data = array();
			foreach ($model as $items) {
				$data[]=array(
				  'button_name'=>t($items->button_name),
				  'uuid'=>$items->uuid,
				  'class_name'=>$items->class_name,
				  'do_actions'=>$items->do_actions,					  			 
				);
			}
			return $data;
		}
		return false;
	}
	
	public static function getOrderButtonStatus($uuid='')
	{
		$stmt="
		SELECT a.description as status
		FROM {{order_status}} a
		LEFT JOIN {{order_settings_buttons}} b
		ON
		a.stats_id = b.stats_id	
		WHERE b.uuid = ".q($uuid)."
		";		
		if($res = Yii::app()->db->createCommand($stmt)->queryRow()){			
			return $res['status'];
		}
		throw new Exception( 'no results' );
	}
	
	public static function getOrderButtonActions($uuid='')
	{
		$model = AR_order_settings_buttons::model()->find("uuid=:uuid",array(
		 ':uuid'=>$uuid
		));
		if($model){
		   return $model->do_actions;	
		}
		throw new Exception( 'no results' );
	}
	
	public static function rejectionList($meta_name='rejection_list',$language=KMRS_DEFAULT_LANGUAGE)
	{		
		$stmt = "
		SELECT a.meta_value as meta_value_original,
		b.meta_value
		FROM {{admin_meta}}	a
		left JOIN (
			SELECT meta_id,meta_value FROM {{admin_meta_translation}} where language = ".q($language)."
		) b 
		on a.meta_id = b.meta_id

		WHERE
		meta_name=".q($meta_name)."
		";		
		$dependency = CCacheData::dependency();					
        if($res = Yii::app()->db->cache(Yii::app()->params->cache, $dependency)->createCommand($stmt)->queryAll()){
			foreach ($res as $items) {
				$data[] = !empty($items['meta_value'])? trim($items['meta_value']) : trim($items['meta_value_original']);
			}
			return $data;
		}
		throw new Exception( 'no results' );
	}
	
	public static function getOrderHistory($order_uuid='' , $lang=KMRS_DEFAULT_LANGUAGE)
	{
		$stmt="
		SELECT created_at,order_id,status,change_by,
		remarks,ramarks_trans,latitude,longitude
		FROM {{ordernew_history}}
		WHERE order_id = (
		 select order_id from {{ordernew}}
		 where order_uuid=".q($order_uuid)."
		)
		ORDER BY id DESC
		";		
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
			$data = array();
			foreach ($res as $item) {		
				$remarks = $item['remarks'];
				if(!empty($item['ramarks_trans'])){
					$ramarks_trans = json_decode($item['ramarks_trans'],true);
					$remarks = t($item['remarks'],(array)$ramarks_trans);
				}
				
				$change_by = '';
				if(!empty($item['change_by'])){
					$change_by = t("change status by {{user}}",array(
					  '{{user}}'=>Yii::app()->input->xssClean($item['change_by'])
					));
				}
				
				$data[] = array(
				  'created_at'=>Date_Formatter::dateTime($item['created_at'],"dd MMM yyyy h:mm:ss a"),
				  'status'=>$item['status'],
				  'remarks'=>$remarks,
				  'change_by'=>$change_by,
				  'latitude'=>$item['latitude'],
				  'longitude'=>$item['longitude'],
				);
			}
			return $data;
		}
		throw new Exception( 'no results' );
	}
	
	public static function getOrderCountPerStatus($merchant_id=0, $status=array() , $date = '',$filter_date=true)
	{				
		$criteria=new CDbCriteria();	    
	    $criteria->condition = "merchant_id=:merchant_id";		    
		$criteria->params  = array(
		  ':merchant_id'=>intval($merchant_id),		  
		);
		$criteria->addInCondition('status', (array) $status );		
		if($filter_date){
			$criteria->addSearchCondition('delivery_date', $date );
		}				

		$request_from = self::getRequestfrom();		
		if($request_from){
			$criteria->addInCondition("request_from",$request_from);
		}
		$count = AR_ordernew::model()->count($criteria); 
		return intval($count);
	}
	
	public static function getOrderCountSchedule($merchant_id=0, $status=array() , $date = '')
	{		
		$criteria=new CDbCriteria();	    
	    $criteria->condition = "merchant_id=:merchant_id AND whento_deliver=:whento_deliver";		    
		$criteria->params  = array(
		  ':merchant_id'=>intval($merchant_id),		  
		  ':whento_deliver'=>"schedule"
		);
		$criteria->addInCondition('status', (array) $status );					
		$criteria->addCondition('delivery_date > "'.$date.'" ');

		$request_from = self::getRequestfrom();		
		if($request_from){
			$criteria->addInCondition("request_from",$request_from);
		}
		
		$count = AR_ordernew::model()->count($criteria); 		
		return intval($count);
	}
	
	public static function getAllOrderCount($merchant_id=0)
	{
		$criteria=new CDbCriteria();	    
	    $criteria->condition = "merchant_id=:merchant_id";		    
		$criteria->params  = array(
		  ':merchant_id'=>intval($merchant_id),		  
		);
		$not_in_status = AttributesTools::initialStatus();		
		$criteria->addNotInCondition('status', (array) array($not_in_status) );		

		$request_from = self::getRequestfrom();		
		if($request_from){
			$criteria->addInCondition("request_from",$request_from);
		}
		
		$count = AR_ordernew::model()->count($criteria); 
		return intval($count);
	}
	
	public static function OrderNotViewed($merchant_id=0, $status=array() , $date = '')
	{
		$criteria=new CDbCriteria();	    
	    $criteria->condition = "merchant_id=:merchant_id AND is_view=:is_view";		    
		$criteria->params  = array(
		  ':merchant_id'=>intval($merchant_id),		  
		  ':is_view'=>0,		  
		);
		$criteria->addInCondition('status', (array) $status );
		$criteria->addSearchCondition('delivery_date', $date );
		
		$count = AR_ordernew::model()->count($criteria); 
		return intval($count);
	}
	
	public static function getOrdersTotal($merchant_id=0, $status=array(), $not_in_status=array(), $start=null,$end=null )
	{				
		$criteria=new CDbCriteria();
	    $criteria->select="order_id,order_uuid,total,status";
	    
	    if($merchant_id>0){
		    $criteria->condition = "merchant_id=:merchant_id";		    
			$criteria->params  = array(
			  ':merchant_id'=>intval($merchant_id),		  
			);
	    }
		if(is_array($status) && count($status)>=1){
			$criteria->addInCondition('status', (array) $status );
		}
		if(is_array($not_in_status) && count($not_in_status)>=1){
			$criteria->addNotInCondition('status', (array) $not_in_status );
		}		

		if(!empty($start) && !empty($end)){
			$criteria->addCondition("DATE(date_created) >= :startDate");
            $criteria->addCondition("DATE(date_created) <= :endDate");
			$criteria->params[':startDate'] = $start;
            $criteria->params[':endDate'] = $end;
		}
		
		$count = AR_ordernew::model()->count($criteria); 
		if($count){
			return $count;
		}
		return 0;
	}
	
	public static function getOrderSummary($merchant_id=0, $status=array() , $exchange_rate_options='',$start=null,$end=null)
	{		
		$criteria=new CDbCriteria();		

		switch ($exchange_rate_options) {
			case 'exchange_rate_merchant_to_admin':				
				$criteria->select="sum((total*exchange_rate_merchant_to_admin)) as total";
				break;
			
			default:
			    $criteria->select="IFNULL(sum(total),0) as total";
				break;
		}
		
		if($merchant_id>0){
			$criteria->condition = "merchant_id=:merchant_id";		    
			$criteria->params  = array(
			  ':merchant_id'=>intval($merchant_id)		  
			);
		}		
		$criteria->addInCondition('status', (array) $status );

		if(!empty($start) && !empty($end)){
			$criteria->addCondition("DATE(date_created) >= :startDate");
            $criteria->addCondition("DATE(date_created) <= :endDate");
			$criteria->params[':startDate'] = $start;
            $criteria->params[':endDate'] = $end;
		}
		
		$model = AR_ordernew::model()->find($criteria); 
		if($model){
			return $model->total;
		}
		return 0;
	}
	
	public static function getTotalRefund($merchant_id=0, $status= array() , $exchange_rate_options='',$start=null,$end=null)
	{
		$criteria=new CDbCriteria();		
		
		switch ($exchange_rate_options) {
			case 'exchange_rate_merchant_to_admin':
				$criteria->select="sum((trans_amount*exchange_rate_merchant_to_admin)) as total";
				break;
			
			default:
			    $criteria->select="IFNULL(sum(trans_amount),0) as total";
				break;
		}
		
		if($merchant_id>0){
			// $criteria->condition = "merchant_id=:merchant_id AND status=:status";		    
			// $criteria->params  = array(
			//   ':merchant_id'=>intval($merchant_id),
			//   ':status'=>"paid"
			// );		
			$criteria->condition = "merchant_id=:merchant_id";		    
			$criteria->params  = array(
			  ':merchant_id'=>intval($merchant_id),			  
			);		
		} else {
			// $criteria->condition = "status=:status";		    
			// $criteria->params  = array(			  
			//   ':status'=>"paid"
			// );		
		}
		
		$criteria->addInCondition('transaction_name', (array) $status );

		if(!empty($start) && !empty($end)){
			$criteria->addCondition("DATE(date_created) >= :startDate");
            $criteria->addCondition("DATE(date_created) <= :endDate");
			$criteria->params[':startDate'] = $start;
            $criteria->params[':endDate'] = $end;
		}
						
		$model = AR_ordernew_transaction::model()->find($criteria); 		
		if($model){
			return $model->total;
		}
		return 0;
	}

	public static function findUseDiscount($order_id=0,$owner='merchant',$meta_name='promo_id')
	{
		$stmt = "
		SELECT a.order_id,b.voucher_id
		from st_ordernew_meta a
		left join {{voucher_new}} b
		ON
		a.meta_value = b.voucher_id

		WHERE
		a.order_id=".q(intval($order_id))."
		AND
		a.meta_name=".q($meta_name)."
		AND b.voucher_owner=".q($owner)."
		";				
		if($res = CCacheData::queryRow($stmt)){				
			return $res;
		}
		return false;
	}

	public static function fetchChangeOrderStatus($order_uuid='')
	{
		$criteria=new CDbCriteria();
		$criteria->alias = "a";
		$criteria->select = "a.order_id, a.order_uuid, a.client_id, a.status, a.order_uuid ,
		a.payment_code, a.service_code,a.total, a.delivery_date, a.delivery_time, a.date_created, a.payment_code, a.total,
		a.payment_status, a.is_view, a.is_critical, a.whento_deliver, a.order_accepted_at,a.preparation_time_estimation,a.delivery_time_estimation,
		b.meta_value as customer_name,

		IF(a.whento_deliver='now',
			TIMESTAMPDIFF(MINUTE, a.date_created, NOW())
		,
			TIMESTAMPDIFF(MINUTE, concat(a.delivery_date,' ',a.delivery_time), NOW())
		) as min_diff

		,
		(
			select sum(qty)
			from {{ordernew_item}}
			where order_id = a.order_id
		) as total_items,

		(
			select GROUP_CONCAT(cat_id,';',item_id,';',item_size_id,';',price,';',discount,';',qty)
			from {{ordernew_item}}
			where order_id = a.order_id
		) as items,

		(
			select GROUP_CONCAT(meta_name,';',meta_value)
			from {{ordernew_meta}}
			where order_id = a.order_id
			and meta_name IN ('tracking_start','tracking_end','timezone')
		) as tracking_data
		";
		$criteria->join='LEFT JOIN {{ordernew_meta}} b on  a.order_id=b.order_id ';
		$criteria->condition = "a.order_uuid=:order_uuid AND meta_name=:meta_name ";
		$criteria->params  = array(
			':order_uuid'=>trim($order_uuid),
			':meta_name'=>'customer_name'
		);
		$item = AR_ordernew::model()->find($criteria);
		if(!$item){
			return false;
		}

		$status_not_in = AOrderSettings::getStatus(array('status_delivered','status_completed',
            'status_cancel_order','status_rejection','status_delivery_fail','status_failed'
        ));				
				
		$tracking_status = AR_admin_meta::getMeta([
			'status_new_order','tracking_status_process','status_delivered','tracking_status_completed',
			'tracking_status_in_transit','tracking_status_ready','status_cancel_order','status_rejection'
		]);		
		
		$tracking_stats[] = $tracking_status['status_new_order']['meta_value'] ?? '';
		$tracking_stats[] = $tracking_status['tracking_status_process']['meta_value'] ?? '';            
		
		$status_completed[] =  $tracking_status['tracking_status_completed']['meta_value'] ?? '';
		$status_completed[] =  $tracking_status['status_delivered']['meta_value'] ?? '';
		$status_completed[] =  $tracking_status['status_cancel_order']['meta_value'] ?? '';
		$status_completed[] =  $tracking_status['status_rejection']['meta_value'] ?? '';            
	
		$is_critical =  false;                	
		if(!in_array($item->status,(array)$status_completed)){
			if($item->whento_deliver=="schedule"){
				if($item->min_diff>0){
					$is_critical = true;
				}
			} else if ($item->min_diff>10 && !in_array($item->status,(array)$status_not_in) ) {
				$is_critical = true;
			}         
	    }
		

		$settings_tabs = COrders::OrderSettingTabs();   
		$order_group_buttons = COrders::OrderGroupButtons();
		$order_buttons = COrders::OrderButtons(Yii::app()->language);

		$actions_buttons = [];
		$group_name = $settings_tabs[$item->status]['group_name'] ?? null;                
		$buttons = $order_group_buttons[$group_name] ?? null;                		
		$button_id = $buttons[$item->service_code] ??  ( $buttons['none'] ?? null );                
		if($button_id && is_array($button_id) && count($button_id)>=1){
			foreach ($button_id as $button_key) {                 
				$actions_buttons[] = $order_buttons[$button_key] ?? null;
			}
		}                
		
		$parsedData = [];                
		if(!empty($item->tracking_data)){
			$keyValuePairs = explode(",", $item->tracking_data);                    
			foreach ($keyValuePairs as $pair) {                            
				$pairArray = explode(";", $pair);
				$key = $pairArray[0]; // Key is the first element
				$value = $pairArray[1]; // Value is the second element                                                        
				$parsedData[$key] = $value;
			}
		} 

		$preparation_starts = null;

		if($item->whento_deliver=="schedule"){
			$scheduled_delivery_time = $item->delivery_date." ".$item->delivery_time;
			$preparationStartTime = CommonUtility::calculatePreparationStartTime($scheduled_delivery_time,($item->preparation_time_estimation+$item->delivery_time_estimation) );					
			$currentTime = time();
			if ($currentTime < $preparationStartTime) {															
				$preparation_starts = Date_Formatter::dateTime($preparationStartTime,"EEEE h:mm a",true);					
			}
		}
		
		$is_timepreparation = in_array($item->status,(array)$tracking_stats)?true:false;    
		if($is_timepreparation && !$item->order_accepted_at){
			$is_timepreparation = false;
		}

		$is_completed = in_array($item->status,(array)$status_completed)?true:false;	
		
		$data = [
			'order_id'=>$item->order_id,
			'order_uuid'=>$item->order_uuid,
			'total'=>Price_Formatter::formatNumber($item->total),
			'customer_name'=>$item->customer_name,
			'status'=>$status[$item->status]['status']  ?? t($item->status),
			'status_raw'=>$item->status,
			'status_class'=>str_replace(" ","_", strtolower($item->status ?? '')),
			'total_items'=>Yii::t('front', '{n} item|{n} items', $item->total_items ),                    
			'order_type'=>$services_list[$item->service_code]['service_name'] ?? t($item->service_code),
			'total'=>Price_Formatter::formatNumber($item->total),
			'is_completed'=>$is_completed,
			'is_view'=>$item->is_view,
			'is_critical'=>$is_critical,
			'tracking_start'=>isset($parsedData['tracking_start'])?$parsedData['tracking_start']:'',
			'tracking_end'=>isset($parsedData['tracking_end'])?$parsedData['tracking_end']:'',
			'timezone'=>isset($parsedData['timezone'])?$parsedData['timezone']:'',
			'order_accepted_at'=>!is_null($item->order_accepted_at)? CommonUtility::calculateReadyTime($item->order_accepted_at,$item->preparation_time_estimation) : null,
			'order_accepted_at_raw'=>$item->order_accepted_at,
			'preparation_starts'=>$preparation_starts,
			'is_timepreparation'=>$is_timepreparation,
			'preparation_time_estimation_raw'=>$item->preparation_time_estimation,
			'preparation_time_estimation'=>CommonUtility::convertMinutesToReadableTime( (!is_null($item->preparation_time_estimation)?$item->preparation_time_estimation:10) ),
			'date_created'=>PrettyDateTime::parse(new DateTime($item->date_created)),
			'actions_buttons'=>$actions_buttons			
	    ];
	    return $data;
	}

	public static function getOrderTotalPerTabs($merchant_id=0)
	{
		$new_order = AOrders::getOrderTabsStatus('new_order');	
		$order_processing = AOrders::getOrderTabsStatus('order_processing');	
		$order_ready = AOrders::getOrderTabsStatus('order_ready');
		$completed_today = AOrders::getOrderTabsStatus('completed_today');			
		
		$status_scheduled = (array) $new_order;

		if($order_processing){				
			foreach ($order_processing as $order_processing_val) {
				array_push($status_scheduled,$order_processing_val);
			}
		}

		$new = AOrders::getOrderCountPerStatus($merchant_id,$new_order,date("Y-m-d"),false);
		$new_for_today = AOrders::getOrderCountPerStatus($merchant_id,$new_order,date("Y-m-d"),true);
		$processing = AOrders::getOrderCountPerStatus($merchant_id,$order_processing,date("Y-m-d"),false);
		$ready = AOrders::getOrderCountPerStatus($merchant_id,$order_ready,date("Y-m-d"),false);
		$completed = AOrders::getOrderCountPerStatus($merchant_id,$completed_today,date("Y-m-d"),false);
		$scheduled = AOrders::getOrderCountSchedule($merchant_id,$status_scheduled,date("Y-m-d"),false);
		$all_orders = AOrders::getAllOrderCount($merchant_id);

		$not_viewed = AOrders::OrderNotViewed($merchant_id,$new_order,date("Y-m-d"));

		return array(
			'new_order'=>$new,
			'new_for_today'=>$new_for_today,
			'order_processing'=>$processing,
			'order_ready'=>$ready,
			'completed_today'=>$completed,
			'scheduled'=>$scheduled,
			'all_orders'=>$all_orders,
			'all'=>$all_orders,
			'not_viewed'=>$not_viewed,
		);						
	}

}
/*end class */