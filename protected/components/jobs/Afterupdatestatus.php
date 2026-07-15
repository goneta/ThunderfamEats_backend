<?php
class Afterupdatestatus 
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
            $language = isset($this->data['language'])?$this->data['language']:null;
            if($language){
                Yii::app()->language = $language;
            } 

            $settings = OptionsTools::find(['points_enabled','points_first_order']);    
            $points_enabled = isset($settings['points_enabled'])?$settings['points_enabled']:false;
		    $points_enabled = $points_enabled==1?true:false;			
		    $points_first_order = isset($settings['points_first_order'])? floatval($settings['points_first_order']):0;        
                       
            $status_completed = AOrderSettings::getStatus(array('status_delivered','status_completed'));		            

            $order = null;

            /*UPDATE PAYMENT IF DELIVERED USING OFFLINE PAYMENT */
            try {			                						
                $order = COrders::get($order_uuid);				                								
                if(in_array($order->status,(array)$status_completed)){									
                    $order->payment_status = 'paid';
                    $order->save();									
                    AR_ordernew_transaction::model()->updateAll(array('status'=>'paid'),
                        'order_id=:order_id',array(':order_id'=>$order->order_id)
                    );					
                } 			
            } catch (Exception $e) { }		

            // CREDIT POINTS
            if($order && in_array($order->status,(array)$status_completed) && $points_enabled ){
                try {
                    CPoints::FirstOrder(
                        $order->client_id,'points_firstorder',
                        CPoints::getDescription('points_firstorder'),
                        $points_first_order,
                        $order->order_id,
                        'points'
                   );
                } catch (Exception $e) {                     
                }  
                try {
                    CPoints::creditPoints($order_uuid);
                } catch (Exception $e) {                     
                }                  
            }            

            if($order && in_array($order->status,(array)$status_completed)){
                // CREDIT COMMISSION
                try {
                    CEarnings::CreditOrderCommission($order);
                } catch (Exception $e) {                     
                }  
            }

        } catch (Exception $e) {                                            
            $logs = $e->getMessage();
        }        
        
        //Yii::log( $logs, CLogger::LEVEL_ERROR);
    }
}
// end class