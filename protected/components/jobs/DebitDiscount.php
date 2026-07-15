<?php
class DebitDiscount 
{
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }
    
    public function execute()
    {        
        try { 
            return true;
            $logs = []; $order = null;
            $order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:''; 

            $status_completed = AOrderSettings::getStatus(array('status_delivered','status_completed'));		
		    $all_online = CPayments::getPaymentTypeOnline();	

            $settings = OptionsTools::find(['points_cover_cost']);

            try {

                $order = COrders::get($order_uuid);	
                $merchant = CMerchants::get($order->merchant_id);			
                $card_id = CWallet::getCardID( Yii::app()->params->account_type['merchant'], $order->merchant_id );
    
                if(in_array($order->status,(array)$status_completed) && array_key_exists($order->payment_code,$all_online) && $merchant->merchant_type==2 ){
                    try {			    	 
                        //sleep(1);
                        CEarnings::debitDiscount($order,$card_id);				
                    } catch (Exception $e) {					
                        $logs = t($e->getMessage());			    			    			    			    
                    }	
                }
            } catch (Exception $e) {			
                $logs = t($e->getMessage());
            }		

            if($order && in_array($order->status,(array)$status_completed) && array_key_exists($order->payment_code,$all_online)){
                try {			    	 		    	 
                    $admin_card_id = CWallet::getCardID( Yii::app()->params->account_type['admin'], 0);                    
                    CEarnings::debitDiscount($order,$admin_card_id,'admin');						
                } catch (Exception $e) {					
                    $logs = t($e->getMessage());
                }	
            }

            if($order && in_array($order->status,(array)$status_completed) && array_key_exists($order->payment_code,$all_online)){			
                if($order->points>0){
                    try {						
                        $points_amount = $order->points;	 
                        $points_cover_cost = isset($settings['points_cover_cost'])?$settings['points_cover_cost']:'';					
                        if($points_cover_cost=="website"){
                            if($order->base_currency_code!=$order->admin_base_currency){
                                $points_amount = ($points_amount*$order->exchange_rate_use_currency_to_admin);
                            }
                        }					
                        $card_id = $points_cover_cost=="merchant"?$card_id:$admin_card_id;					
                        //sleep(1);
                        CEarnings::debitPoints($order,$card_id,$points_amount);
                    } catch (Exception $e) {					
                        $logs = t($e->getMessage());
                    }	
                }
            }

        } catch (Exception $e) {                                            
            $logs = t($e->getMessage());
        }                

        dump($logs);
    }
}
// end class