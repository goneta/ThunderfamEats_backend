<?php
class OrderRunStatus 
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
        
        $language = isset($this->data['language'])?$this->data['language']:null;
            if($language){
                Yii::app()->language = $language;
            } 

        try {

            CCacheData::add();
            $order = COrders::get($order_uuid);                                        
                                    
            $interest = AttributesTools::pushInterest();            
            $actions = CNotifications::getStatusActions($order->status);                

            $customer = COrders::getClientInfo($order->client_id);         
            $merchant_info = COrders::getMerchant($order->merchant_id,Yii::app()->language);                        
            $merchant_settings = OptionsTools::find([
                'merchant_enabled_alert','merchant_email_alert','merchant_mobile_alert'
            ],$order->merchant_id);

            $driver_data = [];
            try {
                $driver_data = CDriver::getDriver($order->driver_id);	
            } catch (Exception $e) {}
            
    
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

            if($driver_data){
                $data['driver_firstname']=$driver_data->first_name;
                $data['driver_last_name']=$driver_data->last_name;
                $data['driver_name']="$driver_data->first_name $driver_data->last_name";
                $data['driver_email']=$driver_data->email;
                $data['driver_phone_prefix']=$driver_data->phone_prefix;
                $data['driver_phone']=$driver_data->phone;
            }            
                                 
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
                        'mobile_number'=>$customer['contact_phone']
                    ];
                    $to['pusher'] = [
                        'notication_channel'=>$customer['client_uuid'],
                        'notification_event'=>Yii::app()->params->realtime['notification_event'],
                        'notification_type'=>$interest['order_update'],
                    ];
                    $to['firebase'] = [ 
                        'push_type'=>"broadcast",
                        'merchant_id'=>0,
                        'channel_device_id'=>$customer['client_uuid'],
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
                } else if ( $action_type=="notification_to_driver" && $driver_data ){    
                    if($driver_data->notification==1){
                        $to['email'] = [
                            'email_address'=>$driver_data->email,
                            'name'=>"$driver_data->first_name $driver_data->last_name"
                        ];
                        $to['sms'] = [
                            'mobile_number'=>$driver_data->phone
                        ];
                        $to['pusher'] = [
                            'notication_channel'=>$driver_data->driver_uuid,
                            'notification_event'=>Yii::app()->params->realtime['notification_event'],
                            'notification_type'=>$interest['order_update'],
                        ];
                        $to['firebase'] = [ 
                            'push_type'=>"broadcast",
                            'merchant_id'=>0,
                            'channel_device_id'=>$driver_data->driver_uuid,
                            'dialog_title'=>$website_title
                        ];
                    }                    
                }
                                                        
                if($to){
                    CNotifications::runTemplates($template_id,$data,$to,Yii::app()->language);
                }                
                $logs = 'Process';
            }
            // end for
            
        } catch (Exception $e) {                                            
            dump($e->getMessage());
            $logs = $e->getMessage();
        }               
    }
}
// end class