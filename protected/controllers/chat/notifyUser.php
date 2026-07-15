<?php
class notifyUser extends CAction
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
            $toUser = []; $fromUser = [];
            $interest = AttributesTools::pushInterest();

            $user_uuid = isset($this->data['user_uuid'])?$this->data['user_uuid']:'';
            $from_user_uuid = isset($this->data['from_user_uuid'])?$this->data['from_user_uuid']:'';         
            $first_name = isset($this->data['first_name'])?$this->data['first_name']:''; 
            $last_name = isset($this->data['last_name'])?$this->data['last_name']:''; 
            $avatar = isset($this->data['avatar'])?$this->data['avatar']:''; 
            $conversation_id = isset($this->data['conversation_id'])?$this->data['conversation_id']:''; 
            
            $toUser=AttributesTools::getUserUnion($user_uuid);
            $fromUser=AttributesTools::getUserUnion($from_user_uuid);
            
            $user_type = isset($toUser['user_type'])?$toUser['user_type']:'';
            $to_uuid = isset($toUser['uuid'])?$toUser['uuid']:'';
            $from_uuid = isset($fromUser['uuid'])?$fromUser['uuid']:'';
            //$first_name = isset($fromUser['first_name'])?$fromUser['first_name']:'';

            if($user_type=="admin"){
                $to_uuid = 'admin-channel';            
            }
            
            if(!empty($to_uuid)){
                $message = "Hello! You've got a new message from {first_name}";
                $message_parameters = ['{first_name}'=>$first_name];
                $noti = new AR_notifications;    	
                $noti->notification_type = "chat-message";						
                $noti->notication_channel = $to_uuid;
                $noti->notification_event = Yii::app()->params->realtime['notification_event'];                
                $noti->message = $message;				
                $noti->message_parameters = json_encode($message_parameters);                    
                $noti->image_type = 'icon';
                $noti->image = 'zmdi zmdi-alert-circle-o';  
                $noti->meta_data = json_encode([
                    'message_type'=>"chat",
                    'page'=>'chat-view',
                    'conversation_id'=>$conversation_id,
                    'from'=>"$first_name $last_name",
                    'avatar'=>$avatar,
                    'message'=>t("Hello! You've got a new message from {first_name}",$message_parameters),                
                ]);                      
                $noti->save();                

                if($user_type!="admin"){
                    $push = new AR_push;
                    $push->push_type = 'broadcast';
                    $push->provider  = 'firebase';
                    $push->channel_device_id = $to_uuid;
                    $push->platform = 'android';
                    $push->title = t($message,$message_parameters);
                    $push->body = t($message,$message_parameters);																				                
                    $push->save();

                    $push = new AR_push;
                    $push->push_type = 'broadcast';
                    $push->provider  = 'firebase';
                    $push->channel_device_id = $to_uuid;
                    $push->platform = 'ios';
                    $push->title = t($message,$message_parameters);
                    $push->body = t($message,$message_parameters);                
                    $push->save();
                }

            }
           
            $this->_controller->code = 1;
            $this->_controller->msg = "Ok";
            $this->_controller->details = [
                'toUser'=>$toUser,
                'fromUser'=>$fromUser,
            ];
            
		} catch (Exception $e) {			
            $this->_controller->msg = t($e->getMessage());	
		}					
        $this->_controller->responseJson();
    }
  
}
// end class