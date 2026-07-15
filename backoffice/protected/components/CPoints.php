<?php
class CPoints
{

    public static function transactionType()
    {
        //points_balance
        return ['points_earned','points_redeemed','points_firstorder','points_signup','points_review','points_booking'];
    }

    public static function getDescription($earn_type='')
    {
        $list = [];
        $list['points_signup'] = "Earn points by registering";
        $list['points_firstorder'] = "First order earn points";
        $list['points_review'] = "Points earned on Booking #{reservation_id}";
        $list['points_booking'] = "Points earned by adding review";
        return isset($list[$earn_type])?$list[$earn_type]:"Earn points";
    }

    public static function getAvailableBalance($client_id=0)
    {
        $balance = 0;
        try {								
            $card_id = CWallet::createCard( Yii::app()->params->account_type['customer_points'],$client_id);
            $balance = CWallet::getBalance($card_id);
        } catch (Exception $e) {             
        }
        return floatval($balance);
    }

    public static function getAvailableBalancePolicy($client_id=0,$redemption_policy='universal',$merchant_id=0)
    {
        $balance = 0;
        try {								
            $card_id = CWallet::createCard( Yii::app()->params->account_type['customer_points'],$client_id);
            if($redemption_policy=="merchant_specific"){
                $criteria = "                
                SELECT                
                SUM(CASE WHEN transaction_type = 'points_earned' THEN transaction_amount ELSE -transaction_amount END) AS points_balance
                FROM {{wallet_transactions}}
                WHERE card_id = ".q($card_id)."
                AND reference_id1 = ".q($merchant_id)."
                GROUP BY
                reference_id1;
                ";
                if($model = CCacheData::queryRow($criteria)){                    
                    $balance = isset($model['points_balance'])?floatval($model['points_balance']):0;
                }
            } else $balance = CWallet::getBalance($card_id);                 
        } catch (Exception $e) {             
        }
        return floatval($balance);
    }

    public static function creditPoints($order_uuid='')
    {
        $order = COrders::get($order_uuid);
        if($order){
            $atts = COrders::getAttributesAll($order->order_id,['points_to_earn']);			
            $points_to_earn = isset($atts['points_to_earn'])? ($atts['points_to_earn']>0?$atts['points_to_earn']:0) :0;
            if($points_to_earn<=0){
                throw new Exception( "points is less than zero");
            }
            
            $card_id = CWallet::createCard( Yii::app()->params->account_type['customer_points'], $order->client_id ); 

            $transaction_type = 'points_earned';

            $model = AR_wallet_transactions::model()->find("reference_id=:reference_id AND transaction_type=:transaction_type",[
                ':reference_id'=>$order->order_id,
                ':transaction_type'=>$transaction_type
            ]);
            if($model){
				throw new Exception( 'Transaction already exist' );
			}


            $settings = OptionsTools::find(['points_expiry']);  
            $points_expiry = $settings['points_expiry'] ?? 4;
            
            $expiry_date = null;
            if($points_expiry==1){
                $earning_date = date("Y-m-d");
                $year = date("Y", strtotime($earning_date)) + 1;
                $expiry_date = $year . "-12-31";
            } else if ( $points_expiry==2){
                try {
                    $customer = ACustomer::get($order->client_id);
                } catch (Exception $e) {}     

                $registration_date = $customer ? $customer->date_created : date("Y-m-d");
                $year = date("Y", strtotime($registration_date));
                $expiry_date = $year . "-12-31"; 
            }
            
            $params = array(					  		 
                'transaction_description'=>"Points earned on order #{order_id}",
                'transaction_description_parameters'=>array('{order_id}'=>$order->order_id),					  
                'transaction_type'=>$transaction_type,
                'transaction_amount'=>$points_to_earn,
                'status'=>'paid',                
                'reference_id'=>$order->order_id,
                'reference_id1'=>$order->merchant_id,
                'expiry_date'=>$expiry_date
            );
            $resp = CWallet::inserTransactions($card_id,$params);                
            return $resp;
        }
        throw new Exception( 'Order not found' );
    }   

    public static function debitPoints($order_uuid='')
    {
        $order = COrders::get($order_uuid);
        if($order){            

            $atts = COrders::getAttributesAll($order->order_id,['point_discount']);		
            if(!$atts){
                throw new Exception( 'Record not found' );
            }
            	
            $point_discount = isset($atts['point_discount'])? json_decode($atts['point_discount'],true) :false;
            $points_used = isset($point_discount['points'])?$point_discount['points']:0;    
            
            if($points_used<=0){
                throw new Exception( "points is less than zero");
            }

            $card_id = CWallet::createCard( Yii::app()->params->account_type['customer_points'], $order->client_id );             

            $transaction_type = 'points_redeemed';

            $model = AR_wallet_transactions::model()->find("reference_id=:reference_id AND transaction_type=:transaction_type",[
                ':reference_id'=>$order->order_id,
                ':transaction_type'=>$transaction_type
            ]);
            if($model){
				throw new Exception( 'Transaction already exist' );
			}

            $params = array(					  		 
                'transaction_description'=>"Redeem {points} points to order #{order_id}",
                'transaction_description_parameters'=>array('{points}'=>$points_used ,'{order_id}'=>$order->order_id),
                'transaction_type'=>$transaction_type,
                'transaction_amount'=>$points_used,
                'status'=>'paid',                
                'reference_id'=>$order->order_id,
                'reference_id1'=>$order->merchant_id
            );            
            $resp = CWallet::inserTransactions($card_id,$params);
            return $resp;
        }
        throw new Exception( 'Order not found' );
    }

    public static function reversal($order_uuid='')
    {
        $order = COrders::get($order_uuid);
        if($order){
            if($order->points>0){
                $atts = COrders::getAttributesAll($order->order_id,['point_discount']);
                if(!$atts){
                    throw new Exception( 'Record not found' );
                }
                $point_discount = isset($atts['point_discount'])? json_decode($atts['point_discount'],true) :false;                
                $points_used = isset($point_discount['points'])?$point_discount['points']:0;                

                if($points_used<=0){
                    throw new Exception( "points is less than zero");
                }

                $card_id = CWallet::createCard( Yii::app()->params->account_type['customer_points'], $order->client_id );
                $transaction_type = 'points_earned';

                $model = AR_wallet_transactions::model()->find("reference_id=:reference_id AND transaction_type=:transaction_type",[
                    ':reference_id'=>$order->order_id,
                    ':transaction_type'=>$transaction_type
                ]);
                if($model){
                    throw new Exception( 'Transaction already exist' );
                }

                $params = array(					  		 
                    'transaction_description'=>"Reversal {points} points on order #{order_id}",
                    'transaction_description_parameters'=>array('{points}'=>$points_used ,'{order_id}'=>$order->order_id),
                    'transaction_type'=>$transaction_type,
                    'transaction_amount'=>$points_used,
                    'status'=>'paid',                
                    'reference_id'=>$order->order_id,
                    'reference_id1'=>$order->merchant_id
                );                
                $resp = CWallet::inserTransactions($card_id,$params);
                return $resp;
            }            
        }
        throw new Exception( 'Order not found' );
    }

    public static function FirstOrder($client_id='',$earn_type='',$description='',$points='',
    $reference_id='',$reference_id1='')
    {

        if($points<=0){
            throw new Exception( "points is less than zero");
        }

        $dependency = CCacheData::dependency();     
        $model = AR_client::model()->cache(Yii::app()->params->cache, $dependency)->find("client_id=:client_id",[
            ':client_id'=>$client_id
        ]);
        if($model){
            $status_completed = AOrderSettings::getStatus(array('status_delivered','status_completed'));
            $in_query = CommonUtility::arrayToQueryParameters($status_completed);
            $criteria = "SELECT count(*) as total_sold FROM {{ordernew}} 
            WHERE client_id=".q($model->client_id)."
            AND status IN ($in_query)            
            ";                   
            $dependency = CCacheData::dependency();
            if($order = AR_ordernew::model()->cache(Yii::app()->params->cache, $dependency)->findBySql($criteria)){                                
                if($order->total_sold==1){
                    $card_id = CWallet::createCard( Yii::app()->params->account_type['customer_points'], $model->client_id );    
                    
                    $model = AR_wallet_transactions::model()->find("card_id=:card_id AND transaction_type=:transaction_type",[
                        ':card_id'=>$card_id,
                        ':transaction_type'=>$earn_type
                    ]);
                    if($model){
                        throw new Exception( 'Transaction already exist' );
                    }    
                    
                    $params = array(					  		 
                        'transaction_description'=>$description,
                        'transaction_description_parameters'=>'',
                        'transaction_type'=>$earn_type,
                        'transaction_amount'=>floatval($points),
                        'status'=>'paid',  
                        'reference_id'=>$reference_id,                                                    
                        'reference_id1'=>$reference_id1,
                    );                     
                    $resp = CWallet::inserTransactions($card_id,$params);
                    return $resp;
                } else {
                    throw new Exception( $order->total_sold>0?"Already many orders completed":"No completed orders" );                
                }
            }
        }
        throw new Exception( HELPER_RECORD_NOT_FOUND );
    }

    public static function globalPoints($client_uuid='',$earn_type='',$description='',$points='',$reference_id='',$description_parameters=array())
    {
        $dependency = CCacheData::dependency();     
        $model = AR_client::model()->cache(Yii::app()->params->cache, $dependency)->find("client_uuid=:client_uuid",[
            ':client_uuid'=>$client_uuid
        ]);
        if($model){            
            $card_id = CWallet::createCard( Yii::app()->params->account_type['customer_points'], $model->client_id );            

            switch ($earn_type) {
                case 'points_signup':                
                    $transact = AR_wallet_transactions::model()->find("reference_id=:reference_id AND transaction_type=:transaction_type",[                
                        ':reference_id'=>$model->client_id,
                        ':transaction_type'=>$earn_type
                    ]);
                    if($transact){
                        throw new Exception( 'Transaction already exist' );
                    }      
                    $reference_id = $model->client_id;
                    break;        
                    
               case 'points_booking':
               case 'points_review':        
                    $transact = AR_wallet_transactions::model()->find("reference_id=:reference_id AND transaction_type=:transaction_type",[                
                        ':reference_id'=>$reference_id,
                        ':transaction_type'=>$earn_type
                    ]);
                    if($transact){
                        throw new Exception( 'Transaction already exist' );
                    }        
                    break;        
            }            

            $params = array(					  		 
                'transaction_description'=>$description,
                'transaction_description_parameters'=>$description_parameters,
                'transaction_type'=>$earn_type,
                'transaction_amount'=>floatval($points),
                'status'=>'paid',                                
                'reference_id'=>$reference_id,
            );             
            $resp = CWallet::inserTransactions($card_id,$params);
            return $resp;
        }
        throw new Exception( HELPER_RECORD_NOT_FOUND );
    }

    public static function getThresholds($exchange_rate=1,$add_zero=false)
    {
        $criteria=new CDbCriteria();
        $criteria->condition = "meta_name=:meta_name";
        $criteria->params  = array(
        ':meta_name'=>AttributesTools::pointsThresholds()
        );
        //$criteria->order = "cast(meta_value as int) asc";
        $criteria->order = "cast(meta_value as unsigned)";
        if($model = AR_admin_meta::model()->findAll($criteria)){            
            $data = [];
            foreach ($model as $items) {
                $amount = $items->meta_value1 * $exchange_rate;
                $data[] = [
                    'id'=>$items->meta_id,
                    'label'=>t("{{points}} Points",['{{points}}'=>$items->meta_value]),
                    'label2'=>t("{points} Off",['{points}'=>Price_Formatter::formatNumber($amount)]),
                    'points'=>$items->meta_value,
                    'amount_raw'=>$items->meta_value1,
                    'exchange_rate'=>$exchange_rate,
                    'amount'=>Price_Formatter::formatNumber($amount),
                    'amount2'=>Price_Formatter::formatNumber3($amount,[
                        'decimals'=>0,
                        'decimal_separator'=>'',
                        'thousand_separator'=>'',
                        'position'=>'right',
                        'spacer'=>'',
                        'currency_symbol'=>Price_Formatter::$number_format['currency_symbol']
                    ])
                ];
            }

            $newItem = [
				'id' => 99999,
				'label' => '',
				'points' => 0,
				'amount_raw' => 0,
				'exchange_rate' => 0,
				'amount' => '',
				'amount2' => '',
			];
            if($add_zero){
                array_unshift($data, $newItem);
            }			
            return $data;
        }
        throw new Exception( HELPER_RECORD_NOT_FOUND );
    }

    public static function getMonthlyThresholds($exchange_rate=1,$add_zero=false)
    {
        $criteria=new CDbCriteria();
        $criteria->condition = "meta_name=:meta_name";
        $criteria->params  = array(
        ':meta_name'=>AttributesTools::monthlyThresholds()
        );        
        $criteria->order = "cast(meta_value as unsigned)";
        if($model = AR_admin_meta::model()->findAll($criteria)){            
            $data = [];
            foreach ($model as $items) {
                $amount = $items->meta_value1 * $exchange_rate;
                $data[] = [
                    'id'=>$items->meta_id,
                    'label'=>t("{{points}} Points",['{{points}}'=>$items->meta_value]),
                    'points'=>$items->meta_value,
                    'amount_raw'=>$items->meta_value1,
                    'exchange_rate'=>$exchange_rate,
                    'amount'=>Price_Formatter::formatNumber($amount),
                    'amount2'=>Price_Formatter::formatNumber3($amount,[
                        'decimals'=>0,
                        'decimal_separator'=>'',
                        'thousand_separator'=>'',
                        'position'=>'right',
                        'spacer'=>'',
                        'currency_symbol'=>Price_Formatter::$number_format['currency_symbol']
                    ])
                ];
            }

            $newItem = [
				'id' => 99999,
				'label' => '',
				'points' => 0,
				'amount_raw' => 0,
				'exchange_rate' => 0,
				'amount' => '',
				'amount2' => '',
			];
            if($add_zero){
                array_unshift($data, $newItem);
            }			

            return $data;
        }
        throw new Exception( HELPER_RECORD_NOT_FOUND );
    }

    public static function getThresholdData($id=0)
    {
        $model = AR_admin_meta::model()->find("meta_id=:meta_id",[
            ':meta_id'=>intval($id)
        ]);
        if($model){
            return [
                'points'=>$model->meta_value,
                'value'=>$model->meta_value1,
            ];
        }
        return false;
    }

    public static function getUserRedeemthresholds($balance=0,$meta_name='points_thresholds')
    {
        $stmt = "
        SELECT 
        CAST(meta_value AS UNSIGNED) AS meta_value, 
        meta_value1
        FROM {{admin_meta}}
        WHERE meta_name = = ".q($meta_name)."
        AND CAST(meta_value AS UNSIGNED) <= ".q($balance)."
        ORDER BY CAST(meta_value AS UNSIGNED) DESC
        LIMIT 1
        ";        
        if($res = Yii::app()->db->createCommand($stmt)->queryRow()){
            return [
                'redeeming_points'=>$res['meta_value'],
                'redeeming_value'=>$res['meta_value1'],
                'redeeming_value_pretty'=>Price_Formatter::formatNumber($res['meta_value1']),
            ];
        }
        return false;
    }

    public static function getCustomerPointsbyRange($start='', $end='',$limit="LIMIT 0,20")
    {        
        $stmt = "        
        SELECT 
            a.card_id,
            b.account_id,
            c.first_name,
            c.last_name,
            c.avatar,
            c.path,    
            
             (COALESCE(SUM(CASE 
                            WHEN transaction_type NOT IN ('points_redeemed','debit')
                            AND 
                                a.transaction_date >= ".q($start)."
                            AND 
                                a.transaction_date <= ".q($end)."                           
                            THEN transaction_amount 
                            ELSE 0 
                        END), 0) - 
            COALESCE(SUM(CASE 
                            WHEN transaction_type IN ('points_redeemed','debit')
                            AND 
                                a.transaction_date >= ".q($start)."
                            AND 
                                a.transaction_date <= ".q($end)."
                            THEN transaction_amount 
                            ELSE 0 
                        END), 0)) AS total_earning

        FROM 
            {{wallet_transactions}} a
        LEFT JOIN 
            {{wallet_cards}} b 
            ON a.card_id = b.card_id
        LEFT JOIN 
            {{client}} c
            ON b.account_id = c.client_id
        WHERE 
            b.account_type = 'customer_points'        
        GROUP BY 
            a.card_id, b.account_id, c.first_name, c.last_name
        ORDER BY 
            total_earning DESC
        $limit        
        ";                          
        if($res = Yii::app()->db->createCommand($stmt)->queryAll()){            
            $data = [];
            foreach ($res as $items) {
                $items['full_name'] = ucwords($items['first_name']." ".$items['last_name']);
                $items['avatar_url'] = CMedia::getImage($items['avatar'],$items['path'],CommonUtility::getPlaceholderPhoto('customer'));
                $items['total_earning'] = Price_Formatter::convertToRaw($items['total_earning'],0,false,',');
                $data[] = $items;
            }
            return $data;
        }
        throw new Exception( HELPER_NO_RESULTS );
    }

    public static function getCustomerEarnPointsbyRange($card_id='',$start='', $end='')
    {        
        $stmt = "        
        SELECT             
            (COALESCE(SUM(CASE 
                            WHEN transaction_type NOT IN ('points_redeemed','debit')
                            AND 
                                a.transaction_date >= ".q($start)."
                            AND 
                                a.transaction_date <= ".q($end)."  
                            AND card_id = ".q($card_id)."
                            THEN transaction_amount 
                            ELSE 0 
                        END), 0) - 
            COALESCE(SUM(CASE 
                            WHEN transaction_type IN ('points_redeemed','debit')
                            AND 
                                a.transaction_date >= ".q($start)."
                            AND 
                                a.transaction_date <= ".q($end)."  
                            AND card_id = ".q($card_id)."
                            THEN transaction_amount 
                            ELSE 0 
            END), 0)) AS total_earning

        FROM  {{wallet_transactions}} a        
        WHERE 
            card_id = ".q($card_id)."        
        ";                                
        if($res = Yii::app()->db->createCommand($stmt)->queryRow()){                        
            return $res['total_earning'];
        }
        throw new Exception( HELPER_NO_RESULTS );
    }

    public static function getUserSummaryTransaction($card_id=0)
    {
        $weekly = null; $monthly = null; $yearly = null;        
        $weekly_range = null; $monthly_range = null;  $yearly_range = null; 
        try {
            $today = new DateTime();						
            $weekly_date = new DateTime();
            $today->modify('last monday');
            $weekly_range = $weekly_date->format('M d')." - ".$weekly_date->modify('next sunday')->format('M d, Y');
            $startOfLastWeek = $today->format('Y-m-d 00:00:00');
            $endOfLastWeek = $today->modify('next sunday')->format('Y-m-d 00:00:00');
            $weekly = CPoints::getCustomerEarnPointsbyRange($card_id,$startOfLastWeek,$endOfLastWeek);
        } catch (Exception $e) {}        

        try {
            $today = new DateTime();
            $monthly_date = new DateTime();
            $today->modify('first day of this month');
            $monthly_range = $monthly_date->format('M d')." - ".$monthly_date->modify('last day of this month')->format('M d, Y');
            $startOfLastMonth = $today->format('Y-m-d 00:00:00');
            $endOfLastMonth = $today->modify('last day of this month')->format('Y-m-d 00:00:00');				
            $monthly = CPoints::getCustomerEarnPointsbyRange($card_id,$startOfLastMonth,$endOfLastMonth);
        } catch (Exception $e) {}        

        try {
            $today = new DateTime();
            $year_date = new DateTime();
            $yearly_range = $year_date->modify('first day of January this year')->format('M d')." - ".$year_date->modify('last day of December this year')->format('M d, Y');
			$startOfThisYear = $today->modify('first day of January this year')->format('Y-m-d 00:00:00');
			$endOfThisYear = $today->modify('last day of December this year')->format('Y-m-d 00:00:00');
            $yearly = CPoints::getCustomerEarnPointsbyRange($card_id,$startOfThisYear,$endOfThisYear);
        } catch (Exception $e) {}        
        
        return [
            'weekly'=>[
                'balance'=>Price_Formatter::formatDistance($weekly),
                'range'=>$weekly_range,
                'label'=>t("This week!"),
            ],            
            'monthly'=>[
                'balance'=>Price_Formatter::formatDistance($monthly),
                'range'=>$monthly_range,
                'label'=>t("This Month!"),
            ],            
            'yearly'=>[
                'balance'=>Price_Formatter::formatDistance($yearly),
                'range'=>$yearly_range,
                'label'=>t("This Year!"),
            ],            
        ];
    }

    public static function getBunosPointsByRange($card_id=0,$start='', $end='',$transaction_type='points_bonus')
    {
        $balance = 0;
        $stmt = "
        SELECT SUM(transaction_amount) as balance
        FROM {{wallet_transactions}}
        WHERE 
           card_id = ".q($card_id)."
        AND 
           transaction_date >= ".q($start)."
        AND 
           transaction_date <= ".q($end)."
        AND 
           transaction_type  = ".q($transaction_type)."
        ";        
        if($res = Yii::app()->db->createCommand($stmt)->queryRow()){          
            $balance = $res['balance'];
        }
        return $balance;
    }

    public static function getUserRank($client_id=0)
    {
        $stmt = "        
        SELECT * FROM {{customer_points_ranks}}
        WHERE account_id=".$client_id."
        ";        
        if($res = Yii::app()->db->createCommand($stmt)->queryRow()){            
            return [
                'rank'=>$res['rank'],
                'percentage_better_than'=>$res['percentage_better_than'],
                'percentage_better_than_pretty'=>t("You are doing better than {percentage}% of other players!",[
                    '{percentage}'=>intval($res['percentage_better_than'])
                ])
            ];
        }
        throw new Exception( HELPER_NO_RESULTS );
    }

    public static function checkPointsThreshold($pointsArray, $threshold) {
        foreach ($pointsArray as $entry) {
            if ($threshold === $entry['points']) {
                return $entry; // Return exact match
            }
        }

        // If no exact match, return closest lower value
        $closest = null;
        foreach ($pointsArray as $entry) {
            if ($threshold >= $entry['points']) {
                $closest = $entry; // Update closest lower value
            }
        }

        return $closest; 
    }

    public static function getMonthlyPoints($card_id=0)
    {
        try {
            $monthly_points = 0;
            $today = new DateTime();
            $today->modify('first day of this month');
            $startOfLastMonth = $today->format('Y-m-d');
            $endOfLastMonth = $today->modify('last day of this month')->format('Y-m-d');	
            $monthly_points = CPoints::getCustomerEarnPointsbyRange($card_id,$startOfLastMonth,$endOfLastMonth);	
        } catch (Exception $e) {}		

        return $monthly_points;	
    }

    public static function getMonthlyBunosPoints($card_id=0)
    {
        try {
            $monthly_points = 0;
            $today = new DateTime();
            $today->modify('first day of this month');
            $startOfLastMonth = $today->format('Y-m-d');
            $endOfLastMonth = $today->modify('last day of this month')->format('Y-m-d');	
            $monthly_points = CPoints::getBunosPointsByRange($card_id,$startOfLastMonth,$endOfLastMonth);
        } catch (Exception $e) {}		
        
        return $monthly_points;	
    }

    public static function getExpiryPoints($expiry_type=1,$card_id=0)
    {
        $stmt = '';
        $expiry_date = null; $balance = 0;
        if($expiry_type==1){
            $currentDate = new DateTime();
            $currentDate->add(new DateInterval('P1Y'));
            $expiry_date = $currentDate->format('Y-m-d');

            $stmt = "          
            SELECT 
                (COALESCE(SUM(CASE 
                                WHEN transaction_type NOT IN ('points_redeemed','debit')
                                AND YEAR(DATE_ADD(transaction_date, INTERVAL 1 YEAR)) = YEAR(CURDATE()) + 1 
                                AND card_id = ".q($card_id)."
                                THEN transaction_amount 
                                ELSE 0 
                            END), 0) - 
                COALESCE(SUM(CASE 
                                WHEN transaction_type IN ('points_redeemed','debit')
                                AND YEAR(DATE_ADD(transaction_date, INTERVAL 1 YEAR)) = YEAR(CURDATE()) + 1 
                                AND card_id = ".q($card_id)."
                                THEN transaction_amount 
                                ELSE 0 
                            END), 0)) AS total_available_points
            FROM {{wallet_transactions}}
            ";
        } else if ($expiry_type==2){
            $currentYear = date('Y');
            $expiry_date = "$currentYear-12-31";

            $stmt = "          
            SELECT 
                (COALESCE(SUM(CASE 
                                WHEN transaction_type NOT IN ('points_redeemed','debit')
                                AND YEAR(DATE_ADD(transaction_date, INTERVAL 1 YEAR)) = YEAR(CURDATE())
                                AND card_id = ".q($card_id)."
                                THEN transaction_amount 
                                ELSE 0 
                            END), 0) - 
                COALESCE(SUM(CASE 
                                WHEN transaction_type IN ('points_redeemed','debit')
                                AND YEAR(DATE_ADD(transaction_date, INTERVAL 1 YEAR)) = YEAR(CURDATE())
                                AND card_id = ".q($card_id)."
                                THEN transaction_amount 
                                ELSE 0 
                            END), 0)) AS total_available_points
            FROM {{wallet_transactions}}
            ";
        } else if ($expiry_type==4){
            $expiry_date = t("unlimited");
            $stmt = "          
            SELECT 
                (COALESCE(SUM(CASE 
                                WHEN transaction_type NOT IN ('points_redeemed','debit')                                
                                AND card_id = ".q($card_id)."
                                THEN transaction_amount 
                                ELSE 0 
                            END), 0) - 
                COALESCE(SUM(CASE 
                                WHEN transaction_type IN ('points_redeemed','debit')                                
                                AND card_id = ".q($card_id)."
                                THEN transaction_amount 
                                ELSE 0 
                            END), 0)) AS total_available_points
            FROM {{wallet_transactions}}
            ";
        }        
        if($res = Yii::app()->db->createCommand($stmt)->queryRow()){ 
            $balance = Price_Formatter::formatDistance($res['total_available_points']);
        }
        return [
            'balance'=>$balance,
            'expiry_date'=>$expiry_date,
            'expiry_type_pretty'=>t("they expire by {date}",[
                '{date}'=>date("m/d/Y",strtotime($expiry_date))
            ])
        ]; 
    }

    public static function getAvailablethresholds($balance=0,$exchange_rate=1)
    {
        $data = [];
        $stmt = "
        SELECT meta_value as points,
        meta_value1 as amount
        FROM {{admin_meta}}
        WHERE meta_name=".q(AttributesTools::pointsThresholds())."
        AND meta_value <= $balance
        ORDER by meta_value desc
        LIMIT 0,1
        ";               
        if($res = Yii::app()->db->createCommand($stmt)->queryRow()){ 
            $data['discount'] = t("{discount} Off",[
                '{discount}'=>Price_Formatter::formatNumber(($res['amount'] *$exchange_rate))
            ]);            
            $data['points_needed']=t("Your available balance {balance}",['{balance}'=>$balance]);	
            return $data;
        } else {
            $stmt = "
            SELECT meta_value as points,
            meta_value1 as amount
            FROM {{admin_meta}}
            WHERE meta_name=".q(AttributesTools::pointsThresholds())."
            AND meta_value > $balance
            ORDER by meta_value ASC
            LIMIT 0,1
            ";               
            if($res = Yii::app()->db->createCommand($stmt)->queryRow()){                  
                $data['discount'] = t("{discount} Off",[
                    '{discount}'=>Price_Formatter::formatNumber(($res['amount'] *$exchange_rate))
                ]);
                $needed_points = $res['points']-$balance;
                $data['points_needed']=t("{points} more points needed",[
                    '{points}'=>$needed_points
                ]);
                return $data;
            }            
        }
        return false;
    }

}
// end class
