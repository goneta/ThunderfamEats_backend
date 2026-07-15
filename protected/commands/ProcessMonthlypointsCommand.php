<?php
set_time_limit(0);

class ProcessMonthlypointsCommand extends CConsoleCommand
{
    public function actionIndex()
    {                

        CommonUtility::SetTimezone();
        CommonUtility::mysqlSetTimezone();        

        $options = OptionsTools::find(['points_bunos_template','website_title']);
        $template_id = $options['points_bunos_template'] ?? null;
        $website_title = $options['website_title'] ?? '';                
        $to = []; $data = [];

        $stmt = "
        SELECT 
            a.card_id,
            b.account_id,
            c.client_uuid,
            c.first_name,
            c.last_name,
            c.email_address,
            c.contact_phone,
            
            (COALESCE(SUM(CASE 
                            WHEN transaction_type NOT IN ('points_redeemed', 'debit') 
                            AND transaction_date BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND LAST_DAY(CURDATE())
                            THEN transaction_amount 
                            ELSE 0 
                        END), 0) - 
            COALESCE(SUM(CASE 
                            WHEN transaction_type IN ('points_redeemed', 'debit') 
                            AND transaction_date BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND LAST_DAY(CURDATE())
                            THEN transaction_amount 
                            ELSE 0 
                        END), 0)) AS total_available_points
        FROM {{wallet_transactions}} a
        LEFT JOIN {{wallet_cards}} b
        ON
        a.card_id = b.card_id

        LEFT JOIN {{client}} c
        ON
        b.account_id = c.client_id

        WHERE a.card_id NOT IN (
           select card_id from {{wallet_transactions}}
           where card_id=a.card_id
           and transaction_date BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND LAST_DAY(CURDATE())
           and transaction_type='points_bonus'
           and status='paid'
        )

        GROUP BY 
            card_id
        ";
        if($res = Yii::app()->db->createCommand($stmt)->queryAll()){               
            foreach ($res as $items) {                
                $data = [
                    'first_name'=>$items['first_name'],
                    'last_name'=>$items['last_name'],
                    'email_address'=>$items['email_address'],
                    'contact_phone'=>$items['contact_phone'],
                    'website_title'=>$website_title
                ];

                $customer_name = "{$items['first_name']} {$items['last_name']}";

                $to['email'] = [
                    'email_address'=>$items['email_address'] ?? '',
                    'name'=>$customer_name
                ];
                $to['sms'] = [
                    'mobile_number'=>$items['contact_phone'] ?? ''
                ];
                $to['pusher'] = [
                    'notication_channel'=>$items['client_uuid'] ?? '',
                    'notification_event'=>Yii::app()->params->realtime['notification_event'],
                    'notification_type'=>'points'
                ];
                $to['firebase'] = [ 
                    'push_type'=>"broadcast",
                    'merchant_id'=>0,
                    'channel_device_id'=>$customer['client_uuid'] ?? '',
                    'dialog_title'=>$website_title
                ]; 

                $card_id = $items['card_id'];
                $balance = $items['total_available_points'];
                if($points = CPoints::getUserRedeemthresholds($balance,'monthly_thresholds')){                    
                    $extra_points = $points['redeeming_value']; 
                    $params = array(					  		 
                        'transaction_description'=>'Monthly bonus',
                        'transaction_description_parameters'=>'',
                        'transaction_type'=>'points_bonus',
                        'transaction_amount'=>floatval($extra_points),
                        'status'=>'paid',                                                        
                    );                   
                    try {
                        CWallet::inserTransactions($card_id,$params);
                        if($template_id){                            
                            CNotifications::runTemplates($template_id,$data,$to,Yii::app()->language);
                        }
                    } catch (Exception $e) {}     
                }
            }
        }
    }
}
// end class