<?php
class SubscriptionsPaymentFailed 
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

            $id  = isset($this->data['id'])?$this->data['id']:null;
            $language = isset($this->data['language'])?$this->data['language']:null;
            if($language){
                Yii::app()->language = $language;
            } 
            if(!$id){
                return;
            }

            $options = OptionsTools::find(['website_title','merchant_subscription_payment_failed'],0);
            $website_title = isset($options['website_title'])?$options['website_title']:'';  
            $template_id = isset($options['merchant_subscription_payment_failed'])?$options['merchant_subscription_payment_failed']:null;  
            if(!$template_id){                                
                return;
            }
            
            $subscriptions = Cplans::getSubscriptionID($id);
            $merchant = CMerchants::get($subscriptions->subscriber_id);

            Price_Formatter::init($subscriptions->currency_code);
            $data = [
                'restaurant_name'=>$merchant->restaurant_name,
                'plan_name'=>$subscriptions->plan_name,
                'amount'=>Price_Formatter::formatNumber($subscriptions->amount),
                'start_date'=>Date_Formatter::date($subscriptions->current_start),
                'end_date'=>Date_Formatter::date($subscriptions->current_end),
                'site_title'=>$website_title,
                'merchant_panel_url'=>websiteUrl()."/".BACKOFFICE_FOLDER."/merchant",
                'attempted_payment_date'=>Date_Formatter::dateTime(date('c'))
            ];

            $to['email'] = [
                'email_address'=>$merchant->contact_email,
                'name'=>$merchant->restaurant_name
            ];
            $to['sms'] = [
                'mobile_number'=>$merchant->contact_phone
            ];
            $to['pusher'] = [
                'notication_channel'=>$merchant->merchant_uuid,
                'notification_event'=>Yii::app()->params->realtime['notification_event'],
                'notification_type'=>$interest['subscriptions'],
            ];
            $to['firebase'] = [ 
                'push_type'=>"broadcast",
                'merchant_id'=>0,
                'channel_device_id'=>$merchant->merchant_uuid,
                'dialog_title'=>$website_title
            ];
            
            CNotifications::runTemplates($template_id,$data,$to,Yii::app()->language);

        } catch (Exception $e) {                                            
            $logs = $e->getMessage();
        }        
        dump($logs);
        //Yii::log( $logs, CLogger::LEVEL_ERROR);
    }
}
// end class