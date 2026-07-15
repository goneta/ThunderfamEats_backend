<?php
class MerchantRegNew 
{
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }
    
    public function execute()
    {        
        try { 

            $logs = '';        
            $interest = AttributesTools::pushInterest();  

            $merchant_id  = isset($this->data['merchant_id'])?$this->data['merchant_id']:null;
            $language = isset($this->data['language'])?$this->data['language']:null;
            if($language){
                Yii::app()->language = $language;
            } 

            $options = OptionsTools::find(array('admin_email_alert','admin_mobile_alert','admin_enabled_alert','merchant_registration_new_tpl'));
            $enabled = isset($options['admin_enabled_alert'])?$options['admin_enabled_alert']:0;
            $enabled = $enabled==1?true:false;
            $email_address = isset($options['admin_email_alert'])?$options['admin_email_alert']:'';
            $phone_number = isset($options['admin_mobile_alert'])?$options['admin_mobile_alert']:'';
            $template_id = isset($options['merchant_registration_new_tpl'])?$options['merchant_registration_new_tpl']:null;
                    
            if(!$template_id){
                return;
            }

            if($merchant_id){
                $merchant = CMerchants::get($merchant_id);
                $site = CNotifications::getSiteData();
                $data = array(					      
                    'site'=>$site,
                    'logo'=>isset($site['logo'])?$site['logo']:'',
                    'facebook'=>isset($site['facebook'])?$site['facebook']:'',
                    'twitter'=>isset($site['twitter'])?$site['twitter']:'',
                    'instagram'=>isset($site['instagram'])?$site['instagram']:'',
                    'whatsapp'=>isset($site['whatsapp'])?$site['whatsapp']:'',
                    'youtube'=>isset($site['youtube'])?$site['youtube']:'',
                    'restaurant_name'=>$merchant->restaurant_name,
                    'contact_phone'=>$merchant->contact_phone,
                    'contact_email'=>$merchant->contact_email,
                    'address'=>$merchant->address,	
                    'image_type'=>'icon',
                    'image'=>'zmdi zmdi-alert-circle-o'			      			      
                );		   
                
                if($enabled){
                    $to['email'] = [
                        'email_address'=>$email_address,
                        'name'=>"Admin"
                    ];
                    $to['sms'] = [
                        'mobile_number'=>$phone_number
                    ];
                }                
                
                $to['pusher'] = [
                    'notication_channel'=>Yii::app()->params->realtime['admin_channel'],
                    'notification_event'=>Yii::app()->params->realtime['notification_event'],
                    'notification_type'=>$interest['merchant_new_signup'],
                ];
                $to['webpush'] = [                    
                    'channel_device_id'=>Yii::app()->params->realtime['admin_channel'],                    
                    'push_type'=>$interest['merchant_new_signup'],
                    'merchant_id'=>0
                ];                                  
                CNotifications::runTemplates($template_id,$data,$to,Yii::app()->language);
            }
        } catch (Exception $e) {                                            
            $logs = $e->getMessage();
        }                
    }
}
// end class