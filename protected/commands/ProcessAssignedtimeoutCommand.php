<?php
set_time_limit(0);

class ProcessAssignedtimeoutCommand extends CConsoleCommand
{
    public function actionIndex()
    {                
        CommonUtility::SetTimezone();
        CommonUtility::mysqlSetTimezone();        
        
        $options = OptionsTools::find(['driver_time_allowed_accept_order']);
        $time_allowed_accept_order = isset($options['driver_time_allowed_accept_order'])?$options['driver_time_allowed_accept_order']:0;

        $meta = AR_admin_meta::getValue('status_assigned');
        $status = isset($meta['meta_value'])?$meta['meta_value']:'';
        
        $criteria = new CDbCriteria();        
        $criteria->condition = "delivery_status=:delivery_status 
        AND driver_id>0 AND assigned_at < NOW() - INTERVAL :interval MINUTE";
        $criteria->params = [
            ':delivery_status'=>$status,
            ':interval'=>$time_allowed_accept_order
        ];        
        if($model = AR_ordernew::model()->findAll($criteria)){              
            foreach ($model as $items) {
                $driver_id = $items->driver_id;
                $items->driver_id = 0;
                $items->vehicle_id = 0;
                $items->delivery_status = 'unassigned';
                $items->assigned_at = null;
                $items->assigned_expired_at = null;
                if($items->save()){
                    
                    $model_attempt = new AR_driver_attempts();
                    $model_attempt->order_id = $items->order_id;
                    $model_attempt->driver_id = $driver_id;
                    $model_attempt->attempt_status = 'expired';
                    $model_attempt->save();

                    // CommonUtility::pushJobs("AutoAssign",[
                    //     'order_uuid'=>$items->order_uuid
                    // ]);
                    try {            
                        $jobs = 'AutoAssign';
                        $jobInstance = new $jobs([
                            'order_uuid'=>$items->order_uuid
                        ]);
                        $jobInstance->execute();
                    } catch (Exception $e) { }        
                    
                    try {            
                        $jobs = 'DeliveryMissedOrder';
                        $jobInstance = new $jobs([
                            'order_uuid'=>$items->order_uuid,
                            'driver_id'=>$driver_id,
                            'language'=>Yii::app()->language
                        ]);
                        $jobInstance->execute();
                    } catch (Exception $e) { }                    
                } 
            }
        }
    }
}
// end class