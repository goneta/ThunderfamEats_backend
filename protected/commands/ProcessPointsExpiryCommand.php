<?php
set_time_limit(0);

class ProcessPointsExpiryCommand extends CConsoleCommand
{
    public function actionIndex()
    {                  
        CommonUtility::SetTimezone();
        CommonUtility::mysqlSetTimezone();        

        $credits = Yii::app()->db->createCommand("
            SELECT card_id,
            SUM(transaction_amount) as total_points
            FROM {{wallet_transactions}}
            WHERE transaction_type = 'points_earned'
            AND expiry_date <= CURDATE()
            AND expired_flag = 0
            GROUP BY card_id
        ")->queryAll();
                        
        if(is_array($credits) && count($credits)>=1){            
            foreach ($credits as $credit) {
                dump($credit);
                $card_id = $credit['card_id'];
                $total_points = $credit['total_points'];

                $last_balance = Yii::app()->db->createCommand("
                    SELECT running_balance
                    FROM {{wallet_transactions}}
                    WHERE card_id = :card_id
                    ORDER BY transaction_id DESC LIMIT 1
                ")->queryScalar([':card_id' => $card_id]);

                dump("last_balance=>$last_balance");                 

                if($last_balance<=0){
                    continue;
                }

                $new_balance = max(0, $last_balance - $total_points);
                if($new_balance<=0){
                    $expired_points = $last_balance;
                } else $expired_points = $total_points;
                dump("new_balance=>$new_balance"); 
                
                $params = array(					  		 
                    'transaction_description'=>'Points Expired',
                    'transaction_description_parameters'=>'',
                    'transaction_type'=>'debit',
                    'transaction_amount'=>floatval($expired_points),
                    'status'=>'paid',                                                        
                );                     
                
                try {
                    CWallet::inserTransactions($card_id,$params);
                } catch (Exception $e) {}     
            }

            Yii::app()->db->createCommand("
                UPDATE {{wallet_transactions}} 
                SET expired_flag = 1 
                WHERE transaction_type = 'points_earned'
                AND expiry_date <= CURDATE()
                AND expired_flag = 0                
            ")->execute();
        } else {
            echo "No results";
        }
    }
}
// end class