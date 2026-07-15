<?php
class OnassignOrder 
{
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }
    
    public function execute()
    {        
        try { 

            $logs = '';               
            $order_uuid  = isset($this->data['order_uuid'])?$this->data['order_uuid']:null;            
            $order = AR_ordernew::model()->find("order_uuid=:order_uuid",[
				':order_uuid'=>$order_uuid
			]);

            $meta_activity = AR_admin_meta::getValue('status_assigned');
			$status_assigned = isset($meta_activity['meta_value'])?$meta_activity['meta_value']:'';
            if($status_assigned==$order->delivery_status){
                $driver_data = CDriver::getDriver($order->driver_id);
                $noti = new AR_notifications; 
				$noti->notication_channel = $driver_data->driver_uuid;
				$noti->notification_event = Yii::app()->params->realtime['notification_event'];						
				$noti->notification_type = "assign_order";
				$noti->message = json_encode([
					'order_uuid'=>$order->order_uuid
				]);
				$noti->visible = 0;						
				if(!$noti->save()){
					//dump($noti->getErrors());
				}	
            }            
        } catch (Exception $e) {                                            
            $logs = $e->getMessage();
        }        
        //Yii::log( $logs, CLogger::LEVEL_ERROR);
    }
}
// end class