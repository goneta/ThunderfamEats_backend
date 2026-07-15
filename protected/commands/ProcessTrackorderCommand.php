<?php
set_time_limit(0);

class ProcessTrackorderCommand extends CConsoleCommand
{
    public function actionIndex()
    {                
        CommonUtility::SetTimezone();
        CommonUtility::mysqlSetTimezone(); 

        $settings = CNotificationData::getRealtimeSettings();
        $provider = isset($settings['provider'])?$settings['provider']:'';				            
        $settings['notification_event'] = Yii::app()->params->realtime['notification_event'];		
        $interest = AttributesTools::pushInterest();
        $status_ongoing = AOrderSettings::getStatus([
            'tracking_status_process','tracking_status_in_transit','status_new_order','tracking_status_ready'
        ]);			        
        $device_type = ['android','ios'];

        $stmt = "
        SELECT order_id,order_uuid
        FROM {{ordernew}}
        WHERE status IN (".CommonUtility::arrayToQueryParameters($status_ongoing).")        
        AND date_created >= NOW() - INTERVAL 2 HOUR     
        AND ( late_notification_sent = 0  OR  preparation_late_sent = 0 OR delivering_late_sent = 0 )                 
        LIMIT 0,30
        ";             
        if($res = Yii::app()->db->createCommand($stmt)->queryAll()){                                    
            foreach ($res as $items) {                
                $progress = CTrackingOrder::getProgress($items['order_uuid'] , date("Y-m-d g:i:s a") );			                    
                $client_uuid = isset($progress['customer'])?$progress['customer']['client_uuid']:'';
                if($progress['customer']){unset($progress['customer']);}

                $restaurant_name = $progress['merchant']['restaurant_name'];
                $request_from = $progress['request_from'];
                $merchant_id = $progress['merchant_id'];
                
                $order_id = $progress['order_id'];
                $estimated_time = $progress['estimated_time'];
                $is_order_ongoing = $progress['is_order_ongoing']==1?true:false;
                $is_order_late = $progress['is_order_late']==1?true:false;
                $is_preparation_late = $progress['is_preparation_late']==1?true:false;
                $is_order_need_cancellation = $progress['is_order_need_cancellation']==1?true:false;
                $is_driver_delivering_late = $progress['is_driver_delivering_late']==1?true:false;

                $late_notification_sent = $progress['late_notification_sent']==1?true:false;
                $preparation_late_sent = $progress['preparation_late_sent']==1?true:false;
                $delivering_late_sent = $progress['delivering_late_sent']==1?true:false;                

                $order_status = $progress['order_status'];
                $order_status_details = $progress['order_status_details'];
                $settings['notication_channel'] = $client_uuid;
                                
                if($is_order_late && !$late_notification_sent){                                              
                    $push_title = t("Order #{order_id} is delayed. The restaurant hasn't accepted it yet. Sorry for the wait!");
                    $message_parameters = [
                        '{order_id}'=>$order_id
                    ];
                    $noti = new AR_notifications;    
                    $noti->scenario = 'send';							
                    $noti->notication_channel = $client_uuid;
                    $noti->notification_event = Yii::app()->params->realtime['notification_event'] ;
                    $noti->notification_type = $interest['order_update'];
                    $noti->message = $push_title;
                    $noti->message_parameters = json_encode($message_parameters);
                    $noti->image_type = 'icon';
                    $noti->image = 'zmdi zmdi-info-outline';
                    $noti->settings = $settings;	
					$noti->realtime_provider = $provider;	
                    $noti->save();                
                    
                    $push_settings = Yii::app()->params['push'];
                    foreach ($device_type as $items_device) {                        
                        $push = new AR_push;
                        $push->scenario = 'send';
                        $push->merchant_id = $request_from=='singleapp' ? $merchant_id : 0;
                        $push->push_type = 'broadcast';
                        $push->provider  = 'firebase';
                        $push->channel_device_id = $client_uuid;
                        $push->platform = $items_device;
                        $push->title = t("Order #{order_id} is delayed",$message_parameters);
                        $push->body = t($push_title,$message_parameters);					
                        $push->settings = $push_settings;
                        $push->save();                                        
                    }                   

                    Yii::app()->db->createCommand("
                    UPDATE {{ordernew}} SET late_notification_sent = 1
                    WHERE order_id=".q($order_id)."
                    ")->query();
                }
                
                if($is_preparation_late && !$preparation_late_sent){
                    $push_title = t('Order #{order_id} is running behind schedule. Your order will be ready soon.');
                    $message_parameters = [
                        '{order_id}'=>$order_id
                    ];
                    $noti = new AR_notifications;    
                    $noti->scenario = 'send';							
                    $noti->notication_channel = $client_uuid;
                    $noti->notification_event = Yii::app()->params->realtime['notification_event'] ;
                    $noti->notification_type = $interest['order_update'];
                    $noti->message = $push_title;
                    $noti->message_parameters = json_encode($message_parameters);
                    $noti->image_type = 'icon';
                    $noti->image = 'zmdi zmdi-info-outline';
                    $noti->settings = $settings;	
					$noti->realtime_provider = $provider;	
                    $noti->save();        
                    
                    $push_settings = Yii::app()->params['push'];
                    foreach ($device_type as $items_device) {  
                        $push = new AR_push;
                        $push->scenario = 'send';
                        $push->merchant_id = $request_from=='singleapp' ? $merchant_id : 0;
                        $push->push_type = 'broadcast';
                        $push->provider  = 'firebase';
                        $push->channel_device_id = $client_uuid;
                        $push->platform = $items_device;
                        $push->title = t("Order #{order_id} is running behind schedule",$message_parameters);
                        $push->body = t($push_title,$message_parameters);					
                        $push->settings = $push_settings;
                        $push->save();           
                    }  
                    
                    Yii::app()->db->createCommand("
                    UPDATE {{ordernew}} SET preparation_late_sent = 1
                    WHERE order_id=".q($order_id)."
                    ")->query();
                }

                if($is_driver_delivering_late && !$delivering_late_sent){
                    $push_title = t("Your order #{order_id} is running a little late, but it's on its way and should arrive shortly");
                    $message_parameters = [
                        '{order_id}'=>$order_id
                    ];
                    $noti = new AR_notifications;    
                    $noti->scenario = 'send';							
                    $noti->notication_channel = $client_uuid;
                    $noti->notification_event = Yii::app()->params->realtime['notification_event'] ;
                    $noti->notification_type = $interest['order_update'];
                    $noti->message = $push_title;
                    $noti->message_parameters = json_encode($message_parameters);
                    $noti->image_type = 'icon';
                    $noti->image = 'zmdi zmdi-info-outline';
                    $noti->settings = $settings;	
					$noti->realtime_provider = $provider;	
                    $noti->save();       
                    
                    $push_settings = Yii::app()->params['push'];
                    foreach ($device_type as $items_device) { 
                        $push = new AR_push;
                        $push->scenario = 'send';
                        $push->merchant_id = $request_from=='singleapp' ? $merchant_id : 0;
                        $push->push_type = 'broadcast';
                        $push->provider  = 'firebase';
                        $push->channel_device_id = $client_uuid;
                        $push->platform = $items_device;
                        $push->title = t("Order #{order_id} is running a little late",$message_parameters);
                        $push->body = t($push_title,$message_parameters);					
                        $push->settings = $push_settings;
                        $push->save();         
                    }          
                    
                    Yii::app()->db->createCommand("
                    UPDATE {{ordernew}} SET delivering_late_sent = 1
                    WHERE order_id=".q($order_id)."
                    ")->query();
                }

            }
            // end for
        } //else echo "No results";
    }
}
// end class