<?php
set_time_limit(0);

class AssignScheduleorderCommand extends CConsoleCommand
{
    public function actionIndex()
    {                
        CommonUtility::SetTimezone();
        CommonUtility::mysqlSetTimezone();        

        $not_in_status = '';
        $status_completed = AOrderSettings::getStatus(array('status_delivered','status_completed','status_cancel_order','status_rejection'));			
        if(is_array($status_completed) && count($status_completed)>=1){
            $not_in_status = " AND status NOT IN (".CommonUtility::arrayToQueryParameters($status_completed).") ";
        }                

        $stmt = "
        SELECT order_id,order_uuid,service_code,retry_attempts
        FROM {{ordernew}}
        WHERE delivery_status='unassigned'      
        AND driver_id=0 
        AND service_code='delivery'
        AND auto_assign_status ='pending'       
        $not_in_status
        AND TIMESTAMPDIFF(MINUTE, NOW(), delivery_date_time) <= (preparation_time_estimation + delivery_time_estimation)         
        ORDER BY date_created ASC
        LIMIT 0,10
        ";                        
        if($model = AR_ordernew::model()->findAllBySql($stmt)){
            foreach ($model as $order) {                            
                try {			
                    $jobs = 'AutoAssign';
                    $jobInstance = new $jobs([
                        'order_uuid'=> $order->order_uuid,
                    ]);
                    $jobInstance->execute();	
                } catch (Exception $e) { }             
            }
        } else {
            echo "No results";
        }
    }
}
// end class