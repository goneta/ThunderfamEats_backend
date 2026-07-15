<?php
class NotifyChatuser 
{
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }
    
    public function execute()
    {        
        try { 

            $logs = '';                        
            $language = isset($this->data['language'])?$this->data['language']:null;
            if($language){
                Yii::app()->language = $language;
            } 

            $to = isset($this->data['to'])?$this->data['to']:'';
            $from = isset($this->data['from'])?$this->data['from']:'';
            $from_user = isset($this->data['from_user'])?$this->data['from_user']:'';

            $device_type = ['android','ios'];
            $settings = CNotificationData::getRealtimeSettings();
            $provider = isset($settings['provider'])?$settings['provider']:'';				            
            $settings['notification_event'] = Yii::app()->params->realtime['notification_event'];		
            $interest = AttributesTools::pushInterest();
            $settings['notication_channel'] = $to;

            $from_info = null; $first_name = '';
            if($from_user=="customer"){
                $from_info = ACustomer::getUUID($from);
                $first_name = $from_info->first_name;
            } else if ($from_user=="driver"){
                $from_info = CDriver::getDriverByUUID($from);
                $first_name = $from_info->first_name;
            } else if ($from_user=="merchant"){
                $from_info = CMerchants::getByUUID($from);
                $first_name = $from_info->restaurant_name;
            }

            $push_title = t("Hello! You've got a new message from {first_name}");
            $message_parameters = [
                '{first_name}'=> $first_name
            ];            

            $noti = new AR_notifications;    
            $noti->scenario = 'send';							
            $noti->notication_channel = $to;
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
                $push->merchant_id = 0;
                $push->push_type = 'broadcast';
                $push->provider  = 'firebase';
                $push->channel_device_id = $to;
                $push->platform = $items_device;
                $push->title = t($push_title,$message_parameters);
                $push->body = t($push_title,$message_parameters);					
                $push->settings = $push_settings;
                $push->save();                                        
            } 

        } catch (Exception $e) {                                            
            $logs = $e->getMessage();
        }        
        //Yii::log( $logs, CLogger::LEVEL_ERROR);
    }
}
// end class