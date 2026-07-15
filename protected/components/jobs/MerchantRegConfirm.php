<?php
class MerchantRegConfirm 
{
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }
    
    public function execute()
    {        
        try { 

            $logs = '';          
                        
            $merchant_id  = isset($this->data['merchant_id'])?$this->data['merchant_id']:null;
            $language = isset($this->data['language'])?$this->data['language']:null;
            if($language){
                Yii::app()->language = $language;
            } 

            if(!$merchant_id){
                return false;
            }

            $options = OptionsTools::find(array('registration_confirm_account_tpl','merchant_registration_welcome_tpl'));
            $template_id = isset($options['registration_confirm_account_tpl'])?$options['registration_confirm_account_tpl']:'';            
				

            $merchant = CMerchants::get($merchant_id);
            $site = CNotifications::getSiteData();            
            $confirm_link = websiteUrl()."/merchant/confirm_account?uuid=".$merchant->merchant_uuid;

            try{
                $plans = Cplans::planDetails($merchant->package_id,Yii::app()->language);								
            } catch (Exception $e) {
                $plans = '';
            }		

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
                'confirm_link'=>$confirm_link,
                'plan_title'=>$plans?$plans->title:'',
                'image_type'=>'icon',
                'image'=>'zmdi zmdi-alert-circle-o'			      			      
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
                'notification_type'=>'merchant_confirm_account'
            ];            
            CNotifications::runTemplates($template_id,$data,$to,Yii::app()->language);
        } catch (Exception $e) {                                            
            $logs = $e->getMessage();
        }        
        //Yii::log( $logs, CLogger::LEVEL_ERROR);
    }
}
// end class