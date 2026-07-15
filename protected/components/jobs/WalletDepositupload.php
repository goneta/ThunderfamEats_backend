<?php
require 'php-jwt/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class WalletDepositupload 
{
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }
    
    public function execute()
    {        
        try { 

            $logs = '';
            
            $reference_id = $this->data['reference_id'] ?? null;
            $language = $this->data['language'] ?? null;
            if($language){
                Yii::app()->language = $language;
            } 

            $options = OptionsTools::find([
                'wallet_deposit_upload','website_title','admin_enabled_alert','admin_email_alert'
            ],0);            
            $template_id = $options['wallet_deposit_upload'] ?? null;
            $website_title = $options['website_title'] ?? null;
            $admin_enabled = $options['admin_enabled_alert'] ?? false;
            $admin_email_alert = $options['admin_email_alert'] ?? false;
            $admin_mobile_alert = $options['admin_mobile_alert'] ?? false;

            if(!$template_id){
                return ;
            }                      
                        
            $model = AR_wallet_transactions_meta::model()->find("meta_name=:meta_name",[
                ':meta_name'=>$reference_id
            ]);
            if($model){

                $meta_value = json_decode($model->meta_value,true);								
                $data = isset($meta_value['data'])?$meta_value['data']:null;
                $jwt_key = new Key(CRON_KEY, 'HS256');
                $decoded = (array) JWT::decode($data, $jwt_key);                
                $amount = $decoded['amount'] ?? 0;
                $customer_details = $decoded['customer_details'] ?? null;
                $client_id = $customer_details->client_id ?? null;
                $first_name = $customer_details->first_name ?? null;
                $last_name = $customer_details->last_name ?? null;
                $email_address = $customer_details->email_address ?? null;
                $contact_phone = $customer_details->contact_phone ?? null;
                
                $bank_deposit = AR_bank_deposit::model()->find('transaction_ref_id=:transaction_ref_id',[
                    ':transaction_ref_id'=>$reference_id
                ]);
                if(!$bank_deposit){            
                    Yii::log( "Bank deposit not found $reference_id" , CLogger::LEVEL_INFO);   
                    http_response_code(200);
                    Yii::app()->end();                                    
                }                

                $proof_payment_link = CMedia::getImage($bank_deposit->proof_image,$bank_deposit->path);
                $data = [
                    'first_name'=>$first_name,
                    'last_name'=>$last_name,
                    'email_address'=>$email_address,
                    'amount'=>Price_Formatter::convertToRaw($amount,2),
                    'reference_id'=>$reference_id,
                    'uploade_time'=>Date_Formatter::dateTime($bank_deposit->date_modified),
                    'site_title'=>$website_title,      
                    'proof_payment_link'=>$proof_payment_link              
                ];                
                
                if($admin_enabled){
                    if(!empty($admin_email_alert)){
                        $to['email'] = [
                            'email_address'=>$admin_email_alert,
                            'name'=>''
                        ];
                    }                    
                    if(!empty($admin_mobile_alert)){
                        $to['sms'] = [
                            'mobile_number'=>$admin_mobile_alert
                        ];
                    }                    
                }                
                $to['pusher'] = [
                    'notication_channel'=>Yii::app()->params->realtime['admin_channel'],
                    'notification_event'=>Yii::app()->params->realtime['notification_event'],
                    'notification_type'=>'wallet_loading',
                ];                
                $to['webpush'] = [                    
                    'channel_device_id'=>Yii::app()->params->realtime['admin_channel'],                    
                    'push_type'=>'wallet_loading',
                    'merchant_id'=>0
                ]; 
                            
                CNotifications::runTemplates($template_id,$data,$to,Yii::app()->language);
                $logs = "OK";

            } else $logs = "Payment reference not found";              
        } catch (Exception $e) {                                            
            $logs = $e->getMessage();
        }        
        dump($logs);
        //Yii::log( $logs, CLogger::LEVEL_ERROR);
    }
}
// end class