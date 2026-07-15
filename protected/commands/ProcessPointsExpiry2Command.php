<?php
set_time_limit(0);

class ProcessPointsExpiry2Command extends CConsoleCommand
{
    public function actionIndex()
    {                
        CommonUtility::SetTimezone();
        CommonUtility::mysqlSetTimezone(); 

        $stmt = "            
        SELECT 
        card_id,
        (COALESCE(SUM(CASE 
                        WHEN transaction_type NOT IN ('points_redeemed', 'debit') 
                        AND YEAR(DATE_ADD(transaction_date, INTERVAL 1 YEAR)) = YEAR(CURDATE()) 
                        THEN transaction_amount 
                        ELSE 0 
                        END), 0) - 
            COALESCE(SUM(CASE 
                        WHEN transaction_type IN ('points_redeemed', 'debit') 
                        AND YEAR(DATE_ADD(transaction_date, INTERVAL 1 YEAR)) = YEAR(CURDATE()) 
                        THEN transaction_amount 
                        ELSE 0 
                        END), 0)) AS total_expiring_points
        FROM {{wallet_transactions}}
        GROUP BY 
        card_id
        HAVING 
        total_expired_points > 0
        ";
        if($res = Yii::app()->db->createCommand($stmt)->queryAll()){                          
            foreach ($res as $items) {                
                $card_id = $items['card_id'];
                $points_expired = $items['total_expired_points'];
                $params = array(					  		 
                    'transaction_description'=>'Points Expired',
                    'transaction_description_parameters'=>'',
                    'transaction_type'=>'debit',
                    'transaction_amount'=>floatval($points_expired),
                    'status'=>'paid',                                                        
                );                   
                try {
                    CWallet::inserTransactions($card_id,$params);
                } catch (Exception $e) {}     
            }
        } 
    }
}
// end class