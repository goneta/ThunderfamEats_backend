<?php
require 'twig/vendor/autoload.php';

class Sendbanksubscription 
{
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }
    
    public function execute()
    {                        
        try {
            $plan_subscriptions_id = isset($this->data['plan_subscriptions_id'])?$this->data['plan_subscriptions_id']:null;
            if($plan_subscriptions_id){                        
                $plans = Cplans::getSubscriptionID($plan_subscriptions_id);                
                $subscriber_model =  Cplans::getSubscriberRecords($plans->subscriber_id,$plans->subscriber_type,'model');                          
                $email_address = $plans->subscriber_type=='merchant'?$subscriber_model->contact_email:$subscriber_model->email_address;

                $name = $plans->subscriber_type=='merchant'?$subscriber_model->restaurant_name:$subscriber_model->first_name;
                Price_Formatter::init();	                   
                $total = Price_Formatter::formatNumber($plans->amount);                                                                            
                $upload_deposit_url = websiteUrl()."/merchant/upload_subscription_deposit?id=".$plans->payment_id;
                
                $tpl_data = CPayments::getBankDepositInstructions(2,0);
                
                $tpl = isset($tpl_data['content'])?$tpl_data['content']:'';
				$subject = isset($tpl_data['subject'])?$tpl_data['subject']:'';

                $path = Yii::getPathOfAlias('backend_webroot')."/twig"; 
				$loader = new \Twig\Loader\FilesystemLoader($path);
				$twig = new \Twig\Environment($loader, [
					'cache' => $path."/compilation_cache",
					'debug'=>true
				]);

                $data = [
					'first_name'=>$name,
					'amount'=>$total,
					'upload_deposit_url'=>$upload_deposit_url
				];
                                                
                $twig_template = $twig->createTemplate($tpl);
				$template = $twig_template->render($data);				                
                $resp = CommonUtility::sendEmail($email_address,$name,$subject,$template);                 
                Yii::log( "Sendbanksubscription $resp $email_address" , CLogger::LEVEL_INFO);
            } else {
                Yii::log( "Subscription not found" , CLogger::LEVEL_INFO);
            }
        } catch (Exception $e) {                    
            Yii::log( $e->getMessage() , CLogger::LEVEL_ERROR);
        }
    }
}
// end class