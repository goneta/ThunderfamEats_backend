<?php
require 'twig/vendor/autoload.php';

class Subscriptiondepositupload 
{
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }
    
    public function execute()
    {        
        try {           
            $deposit_id = isset($this->data['deposit_id'])?$this->data['deposit_id']:null;
            $language = isset($this->data['language'])?$this->data['language']:null;
            $model = AR_bank_deposit::model()->find("deposit_id=:deposit_id",[
                ':deposit_id'=>$deposit_id
            ]);
            if($model){
                if($language){
                    Yii::app()->language = $language;
                }           

                $options = OptionsTools::find(['admin_enabled_alert','admin_email_alert',
                'admin_mobile_alert','website_title','merchant_bank_deposit_subscriptions'],0); 
                $admin_enabled = isset($options['admin_enabled_alert'])?$options['admin_enabled_alert']:'';
                $admin_email_alert = isset($options['admin_email_alert'])?$options['admin_email_alert']:'';
                $admin_mobile_alert = isset($options['admin_mobile_alert'])?$options['admin_mobile_alert']:'';            
                $website_title = isset($options['website_title'])?$options['website_title']:'';      
                $merchant_bank_deposit_subscriptions = isset($options['merchant_bank_deposit_subscriptions'])?$options['merchant_bank_deposit_subscriptions']:null;
                                                
                if(!$merchant_bank_deposit_subscriptions){
                    return;
                }

                $merchants = CMerchants::get($model->merchant_id);
                $plans = Cplans::getSubscriptionID($model->transaction_ref_id);                           
                Price_Formatter::init($model->use_currency_code);
                $data = [
                    'restaurant_name'=>$merchants->restaurant_name,
                    'restaurant_email'=>$merchants->contact_email,
                    'plan_name'=>$plans->plan_name,
                    'amount'=>Price_Formatter::formatNumber($plans->amount),
                    'reference_number'=>$model->reference_number,
                    'uploaded_date'=>Date_Formatter::dateTime($model->date_created),
                    'site_title'=>$website_title,
                    'image_type'=>'icon',
                    'image'=>'zmdi zmdi-alert-circle-o'
                ];
                                
                $template_id = $merchant_bank_deposit_subscriptions;
                $interest = AttributesTools::pushInterest();  

                if($admin_enabled){
                    $to['email'] = [
                        'email_address'=>$admin_email_alert,
                        'name'=>$merchants->restaurant_name
                    ];
                    $to['sms'] = [
                        'mobile_number'=>$admin_mobile_alert
                    ];
                }                

                $to['pusher'] = [
                    'notication_channel'=>Yii::app()->params->realtime['admin_channel'],
                    'notification_event'=>Yii::app()->params->realtime['notification_event'],
                    'notification_type'=>$interest['subscriptions'],
                ];
                $to['webpush'] = [                    
                    'channel_device_id'=>Yii::app()->params->realtime['admin_channel'],                    
                    'push_type'=>$interest['subscriptions'],
                    'merchant_id'=>0
                ];            
                $to['firebase'] = [ 
                    'push_type'=>"broadcast",
                    'merchant_id'=>0,
                    'channel_device_id'=>'subscriptions',
                    'dialog_title'=>$website_title
                ];                
                CNotifications::runTemplates($template_id,$data,$to,Yii::app()->language);
            }
        } catch (Exception $e) {                                
            Yii::log( $e->getMessage() , CLogger::LEVEL_ERROR);
        }
    }
}
// end class