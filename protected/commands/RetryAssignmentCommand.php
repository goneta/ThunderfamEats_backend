<?php
set_time_limit(0);

class RetryAssignmentCommand extends CConsoleCommand
{
    public function actionIndex()
    {                
        CommonUtility::SetTimezone();
        CommonUtility::mysqlSetTimezone(); 
        
        Yii::app()->db->createCommand("
            DELETE FROM {{driver_attempts}}             
            WHERE attempt_time < NOW() - INTERVAL 30 MINUTE
        ")->execute();  

        $options = OptionsTools::find(['driver_auto_assign_retry',
          'driver_assign_max_retry',
          'driver_time_allowed_accept_order',
          'driver_on_demand_availability',
          'driver_allowed_number_task'
        ]);
        $driver_auto_assign_retry = isset($options['driver_auto_assign_retry'])?$options['driver_auto_assign_retry']:null;
        $driver_assign_max_retry = isset($options['driver_assign_max_retry'])? ($options['driver_assign_max_retry']>1?$options['driver_assign_max_retry']:1) :1;
        $max_retry = $driver_assign_max_retry;
        $time_allowed_accept_order = isset($options['driver_time_allowed_accept_order'])?$options['driver_time_allowed_accept_order']:1;
        $time_allowed_accept_order = $time_allowed_accept_order>0?$time_allowed_accept_order:1;                                
        $on_demand_availability = isset($options['driver_on_demand_availability'])? ($options['driver_on_demand_availability']==1?true:false) :false;					
        $allowed_number_task = isset($options['driver_allowed_number_task'])?$options['driver_allowed_number_task']:1;				            
        $allowed_number_task = $allowed_number_task>0?$allowed_number_task:1;

        $meta = AR_admin_meta::getValue('status_assigned');
        $status_assigned = isset($meta['meta_value'])?$meta['meta_value']:'assigned';
        
        if(!$driver_auto_assign_retry){
            return false;
        }

        $now = date("Y-m-d");
        $dateTime = new DateTime(); 
        $dateTime->modify("+$time_allowed_accept_order minutes");            

        $not_in_status = '';
        $status_completed = AOrderSettings::getStatus(array('status_delivered','status_completed','status_cancel_order','status_rejection'));			
        if(is_array($status_completed) && count($status_completed)>=1){
            $not_in_status = " AND status NOT IN (".CommonUtility::arrayToQueryParameters($status_completed).") ";
        }                

        //AND TIMESTAMPDIFF(MINUTE, NOW(), delivery_date_time) <= (preparation_time_estimation + delivery_time_estimation) 
        $stmt = "
        SELECT order_id,order_uuid,retry_attempts
        FROM {{ordernew}}
        WHERE delivery_status='unassigned'      
        AND driver_id=0 
        AND service_code='delivery'
        $not_in_status
        AND retry_attempts < ".q($max_retry)."
        AND auto_assign_status ='failed'        
        AND (last_retry IS NULL OR last_retry < (NOW() - INTERVAL 5 MINUTE))
        ORDER BY date_created ASC
        LIMIT 0,10
        ";                    
        if($model = AR_ordernew::model()->findAllBySql($stmt)){                    
            foreach ($model as $order) {                
                if ($order->retry_attempts >= $max_retry) {
                    echo "escalateToSupport";
                    continue;
                }

                COrders::UpdateAutoAssignStatus($order->order_id,'retrying');

                try {
                    $drivers = CDriver::getAvailableDrivers($order->order_id);
                    foreach ($drivers as $driver) {         
                        $model_order = COrders::get($order->order_uuid);                        
                        $model_order->scenario = "assign_order";
                        $model_order->on_demand_availability = $on_demand_availability;
                        $model_order->driver_id = intval($driver['driver_id']);
                        $model_order->delivered_old_status = $model_order->delivery_status;
                        $model_order->delivery_status = $status_assigned;
                        $model_order->change_by = $driver['first_name'];
                        $model_order->date_now = date("Y-m-d");
                        $model_order->assigned_at = CommonUtility::dateNow();
                        $model_order->assigned_expired_at = $dateTime->format('Y-m-d H:i:s');
                        $model_order->allowed_number_task = intval($allowed_number_task);
                        try {						
                            $vehicle = CDriver::getVehicleAssign($driver['driver_id'],$now);
                            $model_order->vehicle_id = $vehicle->vehicle_id;
                        } catch (Exception $e) {                            
                        }                                      
                        if($model_order->save()){                            
                            dump("Order #$order->order_id Successfully assigned");	
                            COrders::UpdateAutoAssignStatus($order->order_id,'assigned');
                            break;
                        } else {
                            dump($order->getErrors());
                        }
                    }
                } catch (Exception $e) {
                    COrders::UpdateAutoAssignStatus($order->order_id,'failed');
                }
                
                
                $retry_attempts = $order->retry_attempts+1;                
                
                $stmt_update = "
                    UPDATE {{ordernew}}
                    SET retry_attempts = :retry_attempts,
                        last_retry = :last_retry
                    WHERE order_id = :order_id
                ";
                Yii::app()->db->createCommand($stmt_update)->bindValues([
                        ':retry_attempts' => $retry_attempts,
                        ':last_retry' => CommonUtility::dateNow(),
                        ':order_id' => $order->order_id,
                    ])->query();

               
               Yii::app()->db->createCommand("
                    DELETE FROM {{driver_attempts}}
                    WHERE order_id=".q($order->order_id)."
               ")->query();


            }    
            // end for        
        } else echo "no results";
    }
}
// end class