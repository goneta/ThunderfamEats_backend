<?php
class DeliveryMissedOrder 
{
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }
    
    public function execute()
    {        
        try { 

            $logs = '';              
            $order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';
            $driver_id = isset($this->data['driver_id'])?$this->data['driver_id']:0;
            $language = isset($this->data['language'])?$this->data['language']:'';

            $interest = AttributesTools::pushInterest();  
            $order = COrders::get($order_uuid);			
			$driver = CDriver::getDriver($driver_id);            

            $site = CNotifications::getSiteData();
            $meta_data = [
                'order_id'=>$order->order_id,
                'order_uuid'=>$order->order_uuid,
            ];
            $data = array(		
                'order_id'=>$order->order_id,
                'driver_name'=>"$driver->first_name $driver->last_name",
                'site'=>$site,
                'logo'=>isset($site['logo'])?$site['logo']:'',
                'facebook'=>isset($site['facebook'])?$site['facebook']:'',
                'twitter'=>isset($site['twitter'])?$site['twitter']:'',
                'instagram'=>isset($site['instagram'])?$site['instagram']:'',
                'whatsapp'=>isset($site['whatsapp'])?$site['whatsapp']:'',
                'youtube'=>isset($site['youtube'])?$site['youtube']:'',
                'meta_data'=>$meta_data
            );            

            $options = OptionsTools::find(['driver_missed_order_tpl','admin_email_alert','admin_mobile_alert']);
			$template_id = isset($options['driver_missed_order_tpl'])?$options['driver_missed_order_tpl']:'';
			$phone = isset($options['admin_mobile_alert'])?$options['admin_mobile_alert']:'';
			$email = isset($options['admin_email_alert'])?$options['admin_email_alert']:'';
            
            if(!$template_id){
                return;
            }

            $to['email'] = [
                'email_address'=>$email,
                'name'=>"Admin"
            ];
            $to['sms'] = [
                'mobile_number'=>$phone
            ];
            $to['pusher'] = [
                'notication_channel'=>Yii::app()->params->realtime['admin_channel'],
                'notification_event'=>Yii::app()->params->realtime['notification_event'],
                'notification_type'=>$interest['order_update'],
            ];
            CNotifications::runTemplates($template_id,$data,$to,$language);        
            
            $find = array('merchant_enabled_alert','merchant_email_alert','merchant_mobile_alert');
            if($merchant_opts = OptionsTools::find($find,$order->merchant_id)){
                $merchant = CMerchants::get($order->merchant_id);
                $merchant_email = isset($merchant_opts['merchant_email_alert'])?$merchant_opts['merchant_email_alert']:'';
		    	$merchant_mobile = isset($merchant_opts['merchant_mobile_alert'])?$merchant_opts['merchant_mobile_alert']:'';
                
                $to['email'] = [
                    'email_address'=>$merchant_email,
                    'name'=>$merchant->restaurant_name
                ];
                $to['sms'] = [
                    'mobile_number'=>$merchant_mobile
                ];
                $to['pusher'] = [
                    'notication_channel'=>$merchant->merchant_uuid,
                    'notification_event'=>Yii::app()->params->realtime['notification_event'],
                    'notification_type'=>$interest['order_update'],
                ];                
                CNotifications::runTemplates($template_id,$data,$to,$language);        
            }

        } catch (Exception $e) {                                            
            $logs = $e->getMessage();            
        }        
        //Yii::log( $logs, CLogger::LEVEL_ERROR);
    }
}
// end class