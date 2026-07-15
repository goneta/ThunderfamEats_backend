<?php
class Subscriptiondepositupdate 
{
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }
    
    public function execute()
    {                  
        $logs = ''; $to = []; $data = [];
        $interest = AttributesTools::pushInterest();  

        try {     
            $deposit_id  = isset($this->data['deposit_id'])?$this->data['deposit_id']:null;
            $status  = isset($this->data['status'])?$this->data['status']:null;            
            $language = isset($this->data['language'])?$this->data['language']:null;
            if($language){
                Yii::app()->language = $language;
            } 
            if($deposit_id && $status=="approved"){
                $deposit_model = AR_bank_deposit::model()->find("deposit_id=:deposit_id",[
                    ':deposit_id'=>$deposit_id
                ]);
                if(!$deposit_model){                                        
                    return false;
                }
                
                $options = OptionsTools::find(['website_title','merchant_subscription_approved'],0);
                $website_title = isset($options['website_title'])?$options['website_title']:'';                 
                $merchant_subscription_approved = isset($options['merchant_subscription_approved'])?$options['merchant_subscription_approved']:null;

                if(!$merchant_subscription_approved){
                    return ;
                }
                                
                $template_id = $merchant_subscription_approved;

                $model_plan = Cplans::getSubscriptionID($deposit_model->transaction_ref_id);
                $jobs = $model_plan->jobs;
                if($model_plan->subscriber_type=="merchant"){

                    $model_plan->status = 'active';
                    $model_plan->save();

                    $merchant = CMerchants::get($model_plan->subscriber_id);

                    $data = [
                        'restaurant_name'=>$merchant->restaurant_name,
                        'plan_name'=>$model_plan->plan_name,
                        'start_date'=>$model_plan->current_start,
                        'end_date'=>$model_plan->current_end,
                        'site_title'=>$website_title,
                        'merchant_panel_url'=>websiteUrl()."/".BACKOFFICE_FOLDER."/merchant"
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
                    $logs = 'Process';
                } 
            }
        } catch (Exception $e) {                                            
            $logs = $e->getMessage();
        }        
        //Yii::log( $logs, CLogger::LEVEL_ERROR);
    }
}
// end class