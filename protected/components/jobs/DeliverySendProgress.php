<?php
class DeliverySendProgress 
{
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }
    
    public function execute()
    {        
        try { 

            $logs = '';            
            $order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:null;
            $language = isset($this->data['language'])?$this->data['language']:null;
            if($language){
                Yii::app()->language = $language;
            } 
            
			$data = CTrackingOrder::getProgress($order_uuid);			
            if(!isset($data['customer'])){
                Yii::log( "customer is not isset", CLogger::LEVEL_ERROR);
                return;
            }
            if(!isset($data['customer']['client_uuid'])){
                Yii::log( "customer client_uuid not isset ", CLogger::LEVEL_ERROR);
                return;
            }

			$client_uuid = isset($data['customer'])?$data['customer']['client_uuid']:'';
            if(isset($data['customer'])){
			   if($data['customer']){
                  unset($data['customer']);
               }
            }
			$settings = CNotificationData::getRealtimeSettings();
			$provider = isset($settings['provider'])?$settings['provider']:'';				
			
			$settings['notication_channel'] = $client_uuid;
			$settings['notification_event'] = Yii::app()->params->realtime['event_tracking_order'];				
											            
			CNotifier::send($provider,$settings,$data);
            $logs = "Ok";

        } catch (Exception $e) {                                            
            $logs = $e->getMessage();
        }                
        //Yii::log( $logs, CLogger::LEVEL_ERROR);
    }
}
// end class