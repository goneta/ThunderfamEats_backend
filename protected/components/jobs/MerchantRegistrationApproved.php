<?php
class MerchantRegistrationApproved 
{
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }
    
    public function execute()
    {        
        try { 

            $logs = ''; $template_id = null;
            $merchant_id  = isset($this->data['merchant_id'])?$this->data['merchant_id']:null;
            $language = isset($this->data['language'])?$this->data['language']:null;
            if($language){
                Yii::app()->language = $language;
            } 
            if(!$merchant_id){
                return false;
            }

            $options = OptionsTools::find([
                'merchant_registration_approved'
            ],0);        
            $template_id = isset($options['merchant_registration_approved'])?$options['merchant_registration_approved']:null;
            if(!$template_id){
                return;
            }
				
            $merchant = CMerchants::get($merchant_id);
            $site = CNotifications::getSiteData();    

            $data = array(					      
                'site'=>$site,
                'site_name'=>isset($site['site_name'])?$site['site_name']:'',
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
                'image'=>'zmdi zmdi-alert-circle-o',
                'merchant_panel_url'=>websiteUrl()."/".BACKOFFICE_FOLDER."/merchant"
            );

            $to['email'] = [
                'email_address'=>$merchant->contact_email,
                'name'=>$merchant->restaurant_name,
            ];
            $to['sms'] = [
                'mobile_number'=>$merchant->contact_phone
            ];
            $to['pusher'] = [
                'notication_channel'=>$merchant->merchant_uuid,
                'notification_event'=>Yii::app()->params->realtime['notification_event'],
                'notification_type'=>'merchant_approved'
            ];                 

            if($merchant->merchant_type==2){
                // COMMISSION                            
                CNotifications::runTemplates($template_id,$data,$to,Yii::app()->language);          
            } else {
                // MEMBERSHIP                
                $model_plan = Cplans::getSubscriberLatestSubscriptions($merchant->merchant_id,'merchant');
                $model_plan->status = 'active';
                $model_plan->save();

                $jobs = $model_plan->jobs;
                $jobs_data = [
                    'subscription_id'=>$model_plan->subscriber_id,
                    'package_id'=>$model_plan->package_id,
                    'subscriber_type'=>$model_plan->subscriber_type,
                    'subscriber_id'=>$model_plan->subscriber_id,							
                ];    

                $jobs_data = [
                    'subscription_id'=>$model_plan->subscriber_id,
                    'package_id'=>$model_plan->package_id,
                    'subscriber_type'=>$model_plan->subscriber_type,
                    'subscriber_id'=>$model_plan->subscriber_id,							
                ];                    
                if (!class_exists($jobs)) {				
                    Yii::log( "Job class $jobs does not exist." , CLogger::LEVEL_INFO);
                    http_response_code(200);
                    Yii::app()->end();										
                }
                $jobInstance = new $jobs($jobs_data);
                $jobInstance->execute();	
                CNotifications::runTemplates($template_id,$data,$to,Yii::app()->language);                  
            }
        } catch (Exception $e) {                                            
            $logs = $e->getMessage();
        }        
        dump($logs);
        //Yii::log( $logs, CLogger::LEVEL_ERROR);
    }
}
// end class