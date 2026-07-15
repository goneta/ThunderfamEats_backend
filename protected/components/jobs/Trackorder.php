<?php
class Trackorder 
{
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }
    
    public function execute()
    {        
        try {             
            CCacheData::add();
            $logs = [];  $to = []; $data = [];            
            $order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';             
            $data = CTrackingOrder::getProgress($order_uuid);				
            $client_uuid = isset($data['customer'])? (isset($data['customer']['client_uuid'])?$data['customer']['client_uuid']:'') :'';
            if($data['customer']){unset($data['customer']);}
            $settings = CNotificationData::getRealtimeSettings();
            $provider = isset($settings['provider'])?$settings['provider']:'';				
            
            $settings['notication_channel'] = $client_uuid;
            $settings['notification_event'] = Yii::app()->params->realtime['event_tracking_order'];		
            
            CNotifier::send($provider,$settings,$data);

        } catch (Exception $e) {                                            
            $logs[] = $e->getMessage();
        }                
    }
}
// end class