<?php
require 'php-jwt/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class WalletLoading 
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

            $options = OptionsTools::find(['wallet_loading_tpl','website_title'],0);            
            $template_id = $options['wallet_loading_tpl'] ?? null;
            $website_title = $options['website_title'] ?? null;            

            $find = AR_wallet_transactions::model()->find("reference_id=:reference_id",[
                ':reference_id'=>$reference_id
            ]);
            if($find){                
                Yii::log( "Payment already exist $reference_id" , CLogger::LEVEL_INFO);   
                http_response_code(200);
                Yii::app()->end();                            
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
                $data = [
                    'first_name'=>$first_name,
                    'last_name'=>$last_name,
                    'amount'=>Price_Formatter::convertToRaw($amount,2),
                    'reference_id'=>$reference_id,
                    'date_submitted'=>Date_Formatter::dateTime($bank_deposit->date_modified),
                    'site_title'=>$website_title,                    
                ];

                $customer = ACustomer::get($client_id);
                $contact_phone = $customer->contact_phone;              
                
                $to['email'] = [
                    'email_address'=>$email_address,
                    'name'=>$first_name
                ];
                $to['sms'] = [
                    'mobile_number'=>$contact_phone
                ];
                $to['pusher'] = [
                    'notication_channel'=>$customer->client_uuid,
                    'notification_event'=>Yii::app()->params->realtime['notification_event'],
                    'notification_type'=>'wallet_loading',
                ];
                $to['firebase'] = [ 
                    'push_type'=>"broadcast",
                    'merchant_id'=>0,
                    'channel_device_id'=>$customer->client_uuid,
                    'dialog_title'=>$website_title
                ];                     
                
                $payment_code = $decoded['payment_code'] ?? null;
                $payment_name = isset($decoded['payment_name'])?$decoded['payment_name']:'';
                $transaction_amount = isset($decoded['transaction_amount'])?$decoded['transaction_amount']:0;			
                $payment_description_raw = isset($decoded['payment_description_raw'])?$decoded['payment_description_raw']:'';
                $transaction_description_parameters = isset($decoded['transaction_description_parameters'])?$decoded['transaction_description_parameters']:'';
                $currency_code = isset($decoded['currency_code'])?$decoded['currency_code']:'';

                $customer_details = isset($decoded['customer_details'])?$decoded['customer_details']:'';	
                $customer_id = $customer_details->client_id; 

                $card_id = CWallet::createCard( Yii::app()->params->account_type['digital_wallet'],$customer_id);				   
                $meta_array = [];

                try {
                    $date_now = date("Y-m-d");
                    $bonus = AttributesTools::getDiscountToApply($transaction_amount,CDigitalWallet::transactionName(),$date_now);
                    $transaction_amount = floatval($transaction_amount)+floatval($bonus);				  
                    $meta_array[]=[
                        'meta_name'=>'topup_bonus',
                        'meta_value'=>floatval($bonus)
                    ];
                    $payment_description_raw = "Funds Added via {payment_name} with {bonus} Bonus";
                    $transaction_description_parameters = [
                        '{payment_name}'=>$payment_name,
                        '{bonus}'=>Price_Formatter::formatNumber($bonus),
                    ];
                } catch (Exception $e) {}

                CPaymentLogger::afterAdddFunds($card_id,[				    
                    'transaction_description'=>$payment_description_raw,
                    'transaction_description_parameters'=>$transaction_description_parameters,
                    'transaction_type'=>'credit',
                    'transaction_amount'=>$transaction_amount,
                    'status'=>CPayments::paidStatus(),
                    'reference_id'=>$reference_id,
                    'reference_id1'=>CDigitalWallet::transactionName(),
                    'merchant_base_currency'=>$currency_code,
                    'admin_base_currency'=>$currency_code,
                    'meta_name'=>'topup',
                    'meta_value'=>$card_id,
                    'meta_array'=>$meta_array,			
               ]);
               
               if($template_id){
                  CNotifications::runTemplates($template_id,$data,$to,Yii::app()->language);
               }               
               $logs = "$payment_code wallet loading succesful => $reference_id";

            } else $logs = "Payment reference not found";  
        } catch (Exception $e) {                                            
            $logs = $e->getMessage();
        }                
        //Yii::log( $logs, CLogger::LEVEL_ERROR);
    }
}
// end class