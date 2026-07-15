<?php
class notifyChatuser extends CAction
{
    public $_controller;
    public $_id;
    public $data;

    public function __construct($controller,$id)
    {              
       $this->_controller=$controller;
       $this->_id=$id;
    }

    public function run()
    {        
        try {
                                
            $this->data = $this->_controller->data;                                      
            $to = isset($this->data['to'])?$this->data['to']:'';
            $from = isset($this->data['from'])?$this->data['from']:'';
            $first_name = isset($this->data['first_name'])?$this->data['first_name']:'';
            $last_name = isset($this->data['last_name'])?$this->data['last_name']:'';
            $conversation_id = isset($this->data['conversation_id'])?$this->data['conversation_id']:'';
            $avatar = isset($this->data['avatar'])?$this->data['avatar']:'';
            
            $device_type = ['android','ios'];
            $settings = CNotificationData::getRealtimeSettings();
            $provider = isset($settings['provider'])?$settings['provider']:'';				            
            $settings['notification_event'] = Yii::app()->params->realtime['notification_event'];		
            $interest = AttributesTools::pushInterest();
            $settings['notication_channel'] = $to;
            

            $push_title = "Hello! You've got a new message from {first_name}";
            $message_parameters = [
                '{first_name}'=>$first_name
            ];            

            $noti = new AR_notifications;    
            $noti->scenario = 'send';							
            $noti->notication_channel = $to;
            $noti->notification_event = Yii::app()->params->realtime['notification_event'] ;
            //$noti->notification_type = $interest['order_update'];
            $noti->notification_type = "chat-message";
            $noti->message = $push_title;
            $noti->message_parameters = json_encode($message_parameters);
            $noti->image_type = 'icon';
            $noti->image = 'zmdi zmdi-info-outline';
            $noti->settings = $settings;	
            $noti->realtime_provider = $provider;	    
            $noti->meta_data = json_encode([
                'message_type'=>"chat",
                'page'=>'chat-view',
                'conversation_id'=>$conversation_id,
                'from'=>"$first_name $last_name",
                'avatar'=>$avatar,
                'message'=>t("Hello! You've got a new message from {first_name}",$message_parameters),                
            ]);      
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
            
            $this->_controller->code = 1;
            $this->_controller->msg = "Ok";
            
		} catch (Exception $e) {			
            $this->_controller->msg = t($e->getMessage());	
		}					
        $this->_controller->responseJson();
    }
  
}
// end class