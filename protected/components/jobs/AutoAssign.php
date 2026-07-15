<?php
class AutoAssign 
{
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }
    
    public function execute()
    {        
        try { 

            $logs = '';            
            $order_uuid  = isset($this->data['order_uuid'])?$this->data['order_uuid']:null;
            $language = isset($this->data['language'])?$this->data['language']:null;
            if($language){
                Yii::app()->language = $language;
            } 

            $options = OptionsTools::find([
                'driver_enabled_auto_assign','driver_assign_when_accepted','driver_allowed_number_task',
                'driver_on_demand_availability','driver_time_allowed_accept_order','driver_assign_max_retry','driver_auto_assign_retry'
            ]);			            
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

            $enabled_assign_retry = isset($options['driver_auto_assign_retry'])?$options['driver_auto_assign_retry']:null;
            $max_retry = isset($options['driver_assign_max_retry'])? ($options['driver_assign_max_retry']>1?$options['driver_assign_max_retry']:1) :1;            

            $dateTime = new DateTime(); 
            $dateTime->modify("+$time_allowed_accept_order minutes");
            
            $meta = AR_admin_meta::getValue('status_assigned');
            $status_assigned = isset($meta['meta_value'])?$meta['meta_value']:'';                         

            if($enabled_auto_assign!=1){
				Yii::log( "enabled_auto_assign" , CLogger::LEVEL_ERROR);
				return false;
			}
			
            $order = COrders::get($order_uuid);
			$order_status = AttributesTools::cleanString($order->status);

			
			$status_completed = AOrderSettings::getStatus(array('status_delivered','status_completed','status_cancel_order','status_rejection'));
			if(in_array($order_status,(array)$status_completed)){
				echo "Order is already completed";
				return;
			}

			if($order_status=="delivery failed"){
				echo "Order is already completed";
				return;
			}

			if($order_status=="failed"){
				echo "Order is already completed";
				return;
			}

			$exclude_status = AttributesTools::excludeStatus();
			if(in_array($order_status,(array)$exclude_status)){				
				Yii::log( "Order is excluded status" , CLogger::LEVEL_INFO);
				return;
			}
			
			// CHECK IF DATE IS WITHIN 30MINS PERIOD
			if($order->whento_deliver=="schedule"){
				$delivery_date_time = $order->delivery_date_time;
				if(empty($delivery_date_time)){
					$delivery_date_time = date('c');
				}
				$total_needed_time = $order->preparation_time_estimation+$order->delivery_time_estimation;
				$total_needed_time = $total_needed_time>0?$total_needed_time:30;
				//$total_needed_time = 60;
				$currentDateTime = new DateTime();
				$deliveryDateTimeObj = new DateTime($delivery_date_time);			

				echo $total_needed_time . PHP_EOL;
				echo $delivery_date_time . PHP_EOL;
				if ($deliveryDateTimeObj > $currentDateTime) {
					$timeDifference = $currentDateTime->diff($deliveryDateTimeObj);
					$totalMinutes = ($timeDifference->days * 24 * 60) + ($timeDifference->h * 60) + $timeDifference->i;
					echo $totalMinutes . PHP_EOL;
					if ($totalMinutes <= $total_needed_time) {
						echo "The delivery time is within 1 hour from now.";
					} else {
						echo "The delivery time is more than 1 hour from now.";
						return;
					}
				} else {
					echo "The delivery time is in the past.";				
				}			
			}			
            
            if($assign_when_accepted==1){				
				if($order_status!=$processing_status){
					echo "Status not accepted by merchant";
					Yii::log( "Status not accepted by merchant" , CLogger::LEVEL_ERROR);					
                    return false;
				}
			}

            if($order->driver_id>0){
				Yii::log( "Already have driver assigned" , CLogger::LEVEL_ERROR);				
                return false;
			}

			COrders::UpdateAutoAssignStatus($order->order_id,'assigned');

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

			$exludeDriverQuery = '';
            if($respExlude = CDriver::fetchExcludeDriver($order->order_id) ){
                $exludeDriverQuery = " AND a.driver_id NOT IN (".CommonUtility::arrayToQueryParameters($respExlude).") ";                                
            }            

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
                $exludeDriverQuery
				ORDER BY active_task ASC, total_delivered ASC , distance ASC
				LIMIT 0,1
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
                $exludeDriverQuery
				ORDER BY active_task ASC, total_delivered ASC , distance ASC
				LIMIT 0,1
				";				                
            }			
            
            if($res = Yii::app()->db->createCommand($stmt)->queryAll()){                
                foreach ($res as $items) {                    
                    $order->scenario = "assign_order";
                    $order->on_demand_availability = $on_demand_availability;
					$order->driver_id = intval($items['driver_id']);
					$order->delivered_old_status = $order->delivery_status;
					$order->delivery_status = $status_assigned;
					$order->change_by = $items['first_name'];
					$order->date_now = date("Y-m-d");
                    $order->assigned_at = CommonUtility::dateNow();
                    $order->assigned_expired_at = $dateTime->format('Y-m-d H:i:s');
					$order->allowed_number_task = intval($allowed_number_task);
                    try {						
						$vehicle = CDriver::getVehicleAssign($items['driver_id'],$now);
						$order->vehicle_id = $vehicle->vehicle_id;
					} catch (Exception $e) {
						Yii::log( json_encode($e->getMessage()) , CLogger::LEVEL_ERROR);			    			    			    
					}                        
					if($order->save()){
						Yii::log( json_encode("Order #$order->order_id Successfully assigned to driver $order->driver_id") , CLogger::LEVEL_ERROR);
						dump("Order #$order->order_id Successfully assigned");			
						COrders::UpdateAutoAssignStatus($order->order_id,'assigned');			
					} else {
						$logs = $order->getErrors();
						Yii::log( json_encode("failed assigned") , CLogger::LEVEL_ERROR);
					}
                }
            } else {
                $logs = "Order #$order->order_id auto assign no results";                
				COrders::UpdateAutoAssignStatus($order->order_id,'failed');
                if($order->retry_attempts >= $max_retry && $enabled_assign_retry){
                    dump("escalateToSupport $order->order_id");
					//COrders::UpdateAutoAssignStatus($order->order_id,'failed');
                }           
            }                        
        } catch (Exception $e) {                                            
            $logs = $e->getMessage();
        }    
        dump($logs);            
        //Yii::log( $logs, CLogger::LEVEL_ERROR);
    }
}
// end class