<?php
class OrderChangestatus 
{
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }
    
    public function execute()
    {                  
        $logs = [];  $to = []; $data = [];
        $settings = OptionsTools::find([
            'points_enabled','points_first_order','website_title',
            'admin_enabled_alert','admin_email_alert','admin_mobile_alert'
        ]);        
        $points_enabled = isset($settings['points_enabled'])?$settings['points_enabled']:false;
        $points_enabled = $points_enabled==1?true:false;	        
        $points_first_order = isset($settings['points_first_order'])? floatval($settings['points_first_order']):0;
        $order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';        
        $website_title = isset($settings['website_title'])?$settings['website_title']:''; 

        $admin_enabled_alert = isset($settings['admin_enabled_alert'])?$settings['admin_enabled_alert']:false;
        $admin_enabled_alert = $admin_enabled_alert==1?true:false;
        $admin_email = isset($settings['admin_email_alert'])?$settings['admin_email_alert']:'';        
        $admin_mobile = isset($settings['admin_mobile_alert'])?$settings['admin_mobile_alert']:'';        

        try {

            $order = COrders::get($order_uuid);                
            
            // CREDIT MERCHANT
            try {			    	 		    	 		    	 		    					
                CEarnings::creditMerchant($order_uuid);				
            } catch (Exception $e) {                
            }					

            /*UPDATE PAYMENT IF DELIVERED USING OFFLINE PAYMENT */
            $status_completed = AOrderSettings::getStatus(array('status_delivered','status_completed'));        
            try {			
                $all_offline = CPayments::getPaymentTypeOnline(0);							                
                if(in_array($order->status,(array)$status_completed) && array_key_exists($order->payment_code,$all_offline) ){									
                    $order->payment_status = 'paid';
                    $order->save();									
                    AR_ordernew_transaction::model()->updateAll(array('status'=>'paid'),
                        'order_id=:order_id',array(':order_id'=>$order->order_id)
                    );					
                } 			
            } catch (Exception $e) {
                $logs[] = $e->getMessage();	    			    			    			    
            }		

            // CREDIT POINTS
            if($order && in_array($order->status,(array)$status_completed) && $points_enabled ){
                try {
                    CPoints::FirstOrder($order->client_id,'points_firstorder',CPoints::getDescription('points_firstorder'),$points_first_order);
                } catch (Exception $e) { 
                    $logs[] = $e->getMessage();	    
                }  
                try {
                    CPoints::creditPoints($order_uuid);
                } catch (Exception $e) { 
                    $logs[] = $e->getMessage();	    
                }  
            }

            // AUTO UPDATE
            $auto_update = isset($this->data['auto_update'])? ($this->data['auto_update']==1?true:false) :false;        
            if($auto_update){
                  $tracking_stats = AR_admin_meta::getMeta(['tracking_status_process','tracking_status_in_transit']);						
			      $tracking_status_process = isset($tracking_stats['tracking_status_process'])?$tracking_stats['tracking_status_process']['meta_value']:'accepted'; 			
			      $tracking_status_delivering = isset($tracking_stats['tracking_status_in_transit'])?$tracking_stats['tracking_status_in_transit']['meta_value']:'delivery on its way'; 			
                  if($order->status==$tracking_status_process){
                      $order->order_accepted_at = CommonUtility::dateNowAdd();
                      $order->save();
                  }
                  if($order->status==$tracking_status_delivering){
                      $order->pickup_time = CommonUtility::dateNowAdd();				
                      $order->save();
                  }
            }
                        
            $interest = AttributesTools::pushInterest();    
            $actions = CNotifications::getStatusActions($order->status);                                    
            $customer = COrders::getClientInfo($order->client_id);         
            $merchant_info = COrders::getMerchant($order->merchant_id,Yii::app()->language);                        
            $merchant_settings = OptionsTools::find([
                'merchant_enabled_alert','merchant_email_alert','merchant_mobile_alert'
            ],$order->merchant_id);
    
            $customer_email = isset($customer['email_address'])?$customer['email_address']:'';
            $first_name = isset($customer['first_name'])?$customer['first_name']:'';
            $last_name  = isset($customer['last_name'])?$customer['last_name']:'';
            $customer_name = "$first_name $last_name";

            $merchant_uuid = $merchant_info['merchant_uuid'];
            $merchant_name = $merchant_info['restaurant_name'];
            
            $merchant_enabled_alert = isset($merchant_settings['merchant_enabled_alert'])?$merchant_settings['merchant_enabled_alert']:false;
            $merchant_enabled_alert = $merchant_enabled_alert==1?true:false;
            $merchant_email_alert = isset($merchant_settings['merchant_email_alert'])?$merchant_settings['merchant_email_alert']:'';
            $merchant_mobile_alert = isset($merchant_settings['merchant_mobile_alert'])?$merchant_settings['merchant_mobile_alert']:'';            

            $data = CNotifications::getOrder($order_uuid , array(
                'merchant_info','items','summary','order_info','customer','logo','total'
            ));			
            
            if(is_array($data['order_info']) && count($data['order_info'])>=1){
				foreach ($data['order_info'] as $data_key=>$data_value) {
					if($data_key=="service_code"){
						$data_key='order_type';
					}
					$data[$data_key]=$data_value;					
				}
				$data['order_id'] =  isset($data['order_info'])?  ( isset($data['order_info']['order_id'])?$data['order_info']['order_id']:'' )  : '';
				$data['customer_name'] = isset($data['order_info'])?  ( isset($data['order_info']['customer_name'])?$data['order_info']['customer_name']:'' )  : '';
				$data['payment_name'] = isset($data['order_info'])?  ( isset($data['order_info']['payment_name'])?$data['order_info']['payment_name']:'' )  : '';				
				$data['total'] = isset($data['order_info'])?  ( isset($data['order_info']['pretty_total'])?$data['order_info']['pretty_total']:'' )  : '';
				$data['order_type'] = isset($data['order_info'])?  ( isset($data['order_info']['order_type'])?$data['order_info']['order_type']:'' )  : '';				
				$data['place_on'] = isset($data['order_info'])?  ( isset($data['order_info']['place_on'])?$data['order_info']['place_on']:'' )  : '';				
			}
			if(is_array($data['merchant']) && count($data['merchant'])>=1){
				foreach ($data['merchant'] as $data_key=>$data_value) {				
					$data[$data_key]=$data_value;				
				}				
				$data['restaurant_name'] = isset($data['merchant'])? ( isset($data['merchant']['restaurant_name'])?$data['merchant']['restaurant_name']:''  )  :'';
			}
                 
            $data['logo'] = isset($data['url_logo'])?$data['url_logo']:'';
                                    
            foreach ($actions['data'] as $items) {                
                $to = null;
                $action_type = $items['action_type'];
                $template_id = $items['action_value'];
                if ( $action_type=="notification_to_customer"){
                    $to['email'] = [
                        'email_address'=>$customer_email,
                        'name'=>$customer_name
                    ];
                    $to['sms'] = [
                        'mobile_number'=>isset($customer['contact_phone'])?$customer['contact_phone']:''
                    ];
                    $to['pusher'] = [
                        'notication_channel'=>isset($customer['client_uuid'])?$customer['client_uuid']:'',
                        'notification_event'=>Yii::app()->params->realtime['notification_event'],
                        'notification_type'=>$interest['order_update'],
                    ];
                    $to['firebase'] = [ 
                        'push_type'=>"broadcast",
                        'merchant_id'=>0,
                        'channel_device_id'=>isset($customer['client_uuid'])?$customer['client_uuid']:'',
                        'dialog_title'=>$website_title
                    ]; 
                }  else if ( $action_type=="notification_to_merchant"){
                    if($merchant_enabled_alert){
                        $to['email'] = [
                            'email_address'=>$merchant_email_alert,
                            'name'=>"Admin"
                        ];
                    }                    
                    $to['sms'] = [
                        'mobile_number'=>$merchant_mobile_alert
                    ];
                    $to['pusher'] = [
                        'notication_channel'=>$merchant_uuid,
                        'notification_event'=>Yii::app()->params->realtime['notification_event'],
                        'notification_type'=>$interest['order_update'],
                    ];
                    $to['firebase'] = [ 
                        'push_type'=>"broadcast",
                        'merchant_id'=>0,
                        'channel_device_id'=>$merchant_uuid,
                        'dialog_title'=>$website_title
                    ]; 
                }  else if ( $action_type=="notification_to_admin"){
                    if($admin_enabled_alert){
                        $to['email'] = [
                            'email_address'=>$admin_email,
                            'name'=>$merchant_name
                        ];
                    }                    
                    $to['sms'] = [
                        'mobile_number'=>$admin_mobile
                    ];
                    $to['pusher'] = [
                        'notication_channel'=>Yii::app()->params->realtime['admin_channel'],
                        'notification_event'=>Yii::app()->params->realtime['notification_event'],
                        'notification_type'=>$interest['order_update'],
                    ];                    
                }
                                        
                if($to){
                    CNotifications::runTemplates($template_id,$data,$to,Yii::app()->language);
                }                
                $logs = 'Process';
            }
            // end for
            
        } catch (Exception $e) {                                            
            $logs[] = $e->getMessage();
        }        
        // dump("ERROR \n");
        // dump($logs);
    }
}
// end class