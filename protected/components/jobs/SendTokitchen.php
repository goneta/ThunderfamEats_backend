<?php
class SendTokitchen 
{
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }
    
    public function execute()
    {        
        try { 
            $logs = [];  $to = []; $data = [];            
            $order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:''; 
            
            $options = OptionsTools::find(['tableside_send_status']);			
			$status = isset($options['tableside_send_status'])? strtolower(trim($options['tableside_send_status'])) :'';			
			$model  = COrders::get($order_uuid);             
            if($status == strtolower(trim($model->status))){				                
				Ckitchen::sendtoKitchen($order_uuid);
			}			
        } catch (Exception $e) {                                            
            $logs[] = $e->getMessage();
        }                
    }
}
// end class