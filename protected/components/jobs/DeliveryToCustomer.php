<?php
class DeliveryToCustomer 
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

            CCacheData::add();
            $order = COrders::get($order_uuid);
            $meta = AR_admin_meta::getValue('tracking_status_in_transit');
			$status = isset($meta['meta_value'])?$meta['meta_value']:'';
            if(!empty($status)){                
			    $order->status = $status;
			    $order->save();

                $jobs = 'DeliveryChangeStatus'; $jobInstance = new $jobs($this->data); $jobInstance->execute();
			    CommonUtility::pushJobs("DeliveryRunStatus",$this->data);
                CommonUtility::pushJobs("DeliveryTimeEstimation",$this->data);
            }

        } catch (Exception $e) {                                            
            $logs = $e->getMessage();
        }        
        //Yii::log( $logs, CLogger::LEVEL_ERROR);
    }
}
// end class