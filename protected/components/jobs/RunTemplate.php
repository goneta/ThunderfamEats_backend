<?php
class RunTemplate 
{
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }
    
    public function execute()
    {        
        try { 

            $logs = '';                      
            $template_id  = isset($this->data['template_id'])?$this->data['template_id']:null;
            $order_uuid  = isset($this->data['order_uuid'])?$this->data['order_uuid']:null;
            $additional_data  = isset($this->data['additional_data'])?$this->data['additional_data']:null;
            $language = isset($this->data['language'])?$this->data['language']:null;
            if($language){
                Yii::app()->language = $language;
            } 
            
            if(!$template_id && !$additional_data){
                return;
            }

            $order_data = CNotifications::getOrder($order_uuid , array(
                'merchant_info','items','summary','order_info','customer','logo','total'
            ));	      
            
            $order_data['order_id'] = $order_data['order']['order_info']['order_id'];
            $order_data['additional_data'] = $additional_data;

            $interest = AttributesTools::pushInterest();  
            $to['email'] = [
                'email_address'=>$order_data['order']['order_info']['contact_email'],
               'name'=>$order_data['order']['order_info']['customer_name'],
              ];
            $to['sms'] = [
                'mobile_number'=>$order_data['order']['order_info']['contact_number']
            ];
            $to['pusher'] = [
                'notication_channel'=>$order_data['customer']['client_uuid'],
                'notification_event'=>Yii::app()->params->realtime['notification_event'],
                'notification_type'=>$interest['order_update'],
            ];
            $to['firebase'] = [ 
                'push_type'=>"broadcast",
                'merchant_id'=>0,
                'channel_device_id'=>$order_data['customer']['client_uuid'],
                'dialog_title'=>''
            ];                                
            CNotifications::runTemplates($template_id,$order_data,$to,Yii::app()->language);
        } catch (Exception $e) {                                            
            $logs = $e->getMessage();
        }        
        //Yii::log( $logs, CLogger::LEVEL_ERROR);
    }
}
// end class