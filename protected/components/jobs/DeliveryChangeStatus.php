<?php
class DeliveryChangeStatus 
{
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }
    
    public function execute()
    {        
        try { 

            CCacheData::add();
            $logs = '';            
            $order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';
            $current_status = isset($this->data['current_status'])?$this->data['current_status']:'';
            $change_by = isset($this->data['change_by'])?$this->data['change_by']:'';
            $remarks = isset($this->data['remarks'])?$this->data['remarks']:'';
            $scenario = isset($this->data['scenario'])?$this->data['scenario']:'';

            $order = COrders::get($order_uuid);			
			$driver_data = CDriver::getDriver($order->driver_id);	

            $meta = AR_admin_meta::model()->find("meta_value=:meta_value",[
				':meta_value'=>$order->delivery_status
			]);			            
            if($meta){
                $first_name = $driver_data->first_name;					
				$args = [
					'{{order_id}}'=>$order->order_id,
					'{{first_name}}'=>$first_name,
					'{{current_status}}'=>$current_status,
					'{{status}}'=>$order->delivery_status,
					'{{remarks}}'=>$remarks
				];
                $history = new AR_ordernew_history;
				$history->order_id = $order->order_id;
				$history->status = $order->delivery_status;
				$history->remarks = $meta->meta_value1;
				$history->ramarks_trans = json_encode($args);
				$history->change_by = $change_by;		
				$history->latitude = $driver_data->latitude;		
				$history->longitude = $driver_data->lontitude;					
				if(!$history->save()){					
				}
                if($scenario=="delivery_declined"){
					$order->driver_id = 0;
					$order->vehicle_id = 0;
					$order->save();
				}
            }	
        } catch (Exception $e) {                                            
            $logs = $e->getMessage();
        }        
        //Yii::log( $logs, CLogger::LEVEL_ERROR);
    }
}
// end class