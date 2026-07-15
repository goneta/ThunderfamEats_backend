<?php
class Ordercancel 
{
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }
    
    public function execute()
    {        
        try { 

            $logs = '';            
            $order_uuid  = isset($this->data['order_uuid'])?$this->data['order_uuid']:null;
            $language = isset($this->data['language'])?$this->data['language']:null;
            $refund_type = isset($this->data['refund_type'])?$this->data['refund_type']:null;
            $refundAmount = isset($this->data['refund_amount'])?$this->data['refund_amount']:null;
            $payment_code = isset($this->data['payment_code'])?$this->data['payment_code']:null;
            if($language){
                Yii::app()->language = $language;
            } 
            
            $options = OptionsTools::find([
                'points_enabled','digitalwallet_enabled','digitalwallet_refund_to_wallet'
            ]);

            $points_enabled = isset($options['points_enabled'])?$options['points_enabled']:false;
            $points_enabled = $points_enabled==1?true:false;

            $digitalwallet_enabled = isset($options['digitalwallet_enabled'])?$options['digitalwallet_enabled']:false;
            $digitalwallet_enabled = $digitalwallet_enabled==1?true:false;

            $refund_to_wallet = isset($options['digitalwallet_refund_to_wallet'])?$options['digitalwallet_refund_to_wallet']:false;
            $refund_to_wallet = $refund_to_wallet==1?true:false;

            /*SEND NOTIFICATIONS*/
            try {
                $jobs = 'DeliveryRunStatus';
                $jobInstance = new $jobs([
                    'order_uuid'=>$order_uuid,
                    'language'=>Yii::app()->language
                ]);
                //$jobInstance->execute();
            } catch (Exception $e) { }

            $all_online = CPayments::getPaymentTypeOnline();				
            $all_offline = CPayments::getPaymentTypeOnline(0);
            $order = COrders::get($order_uuid);	
            
            // POINTS REVERSAL
            try {
                if($points_enabled){
                    CPoints::reversal($order_uuid);
                }					
            } catch (Exception $e) {
                //
            }

            // DIGITAL WALLET
            if($digitalwallet_enabled){
                $all_online[CDigitalWallet::transactionName()] =[
                    'payment_code'=>CDigitalWallet::transactionName(),
                    'payment_name'=>CDigitalWallet::paymentName(),
                ];
            }

            if(array_key_exists($order->payment_code,(array)$all_offline)){        
                
                $model = AR_ordernew_transaction::model()->find("order_id=:order_id AND 
					transaction_name=:transaction_name AND status=:status",array(
					  ':order_id'=>intval($order->order_id),
					  ':transaction_name'=>'payment',
					  ':status'=>'paid'
				));	
                if(!$model){                    
                    return ;
                }
                
                $refund_amount =  $refundAmount > 0 ? $refundAmount : $order->total_original;							
                $trans = new AR_ordernew_transaction;
                $trans->order_id = intval($order->order_id);
                $trans->merchant_id = intval($order->merchant_id);
                $trans->client_id = intval($order->client_id);
                $trans->payment_code = !empty($payment_code)?$payment_code:$order->payment_code;
                $trans->transaction_name = 'refund';
                $trans->transaction_type = 'debit';
                $trans->transaction_description = $refund_type=="full_refund"?"Full refund":"Partial Refund";
                $trans->trans_amount = floatval($refund_amount);
                $trans->currency_code = $order->use_currency_code;
                $trans->to_currency_code = $order->base_currency_code;
                $trans->exchange_rate = $order->exchange_rate;
                $trans->admin_base_currency = $order->admin_base_currency;
                $trans->exchange_rate_merchant_to_admin = $order->exchange_rate_merchant_to_admin;
                $trans->exchange_rate_admin_to_merchant = $order->exchange_rate_admin_to_merchant;	                
                $get_params = array( 
                    'order_uuid'=> $order_uuid,
                    'transaction_id'=>$trans->transaction_id,                            
                    'language'=>Yii::app()->language,
                    'refund_type'=>$refund_type,
                    'refund_amount'=>$refundAmount
                );			                                                

                if($order->amount_due>0 && $order->wallet_amount>0){                                        
                    if($trans->save()){                        
                        try {			
                            $jobs = 'ProcessRefund';
                            $jobInstance = new $jobs($get_params);
                            $jobInstance->execute();	
                            CommonUtility::pushJobs(($refund_type=="full_refund"?'OrderReversal':'OrderPartialReversal'),$get_params); 
                        } catch (Exception $e) { }
                    }					
                } else {       
                    $trans->status = 'paid';
                    if($trans->save()){ 
                        try {			
                            $jobs = $refund_type=="full_refund"?'OrderReversal':'OrderPartialReversal';
                            $jobInstance = new $jobs([
                                'order_uuid'=> $order_uuid,
                                'refund_type'=>$refund_type,
                                'refund_amount'=>$refundAmount
                            ]);
                            $jobInstance->execute();	                        
                        } catch (Exception $e) { }
                    }                                 
                }
            } else if( array_key_exists($order->payment_code,(array)$all_online) ){                
                $model = AR_ordernew_transaction::model()->find("order_id=:order_id AND 
					transaction_name=:transaction_name AND status=:status",array(
					  ':order_id'=>intval($order->order_id),
					  ':transaction_name'=>'payment',
					  ':status'=>'paid'
				));					
                if($model){
                                        
                    if(in_array($refund_type,['partial_refund','partial'])){
                        $refund_amount = $refundAmount;
                    } else $refund_amount = $order->total_original;                    

                    
                    // REFUND TO DIGITAL WALLET IF PARTIAL PAYMENT
                    if($digitalwallet_enabled && $order->amount_due>0 && $order->wallet_amount>0){
                        $order->payment_code = CDigitalWallet::transactionName();
                    } else if($digitalwallet_enabled && $refund_to_wallet){
                        $order->payment_code = CDigitalWallet::transactionName();
                    }
                                        
                    $trans = new AR_ordernew_transaction;
                    $trans->scenario = $refund_type;                                        
                    $trans->order_id = intval($order->order_id);
                    $trans->merchant_id = intval($order->merchant_id);
                    $trans->client_id = intval($order->client_id);
                    $trans->payment_code = $order->payment_code;
                    $model->order_status = $order->status;
                    $trans->transaction_name = $refund_type;
                    $trans->transaction_type = 'debit';
                    $trans->transaction_description = $refund_type=="full_refund"?"Full refund":"Partial Refund";
                    $trans->trans_amount = floatval($refund_amount);
                    $trans->currency_code = $order->use_currency_code;
                    $trans->to_currency_code = $order->base_currency_code;
                    $trans->exchange_rate = $order->exchange_rate;
                    $trans->admin_base_currency = $order->admin_base_currency;
                    $trans->exchange_rate_merchant_to_admin = $order->exchange_rate_merchant_to_admin;
                    $trans->exchange_rate_admin_to_merchant = $order->exchange_rate_admin_to_merchant;                                        
                    if($trans->save()){						   						
                        $get_params = array( 
                           'order_uuid'=> $order_uuid,
                           'transaction_id'=>$trans->transaction_id,                           
                           'language'=>Yii::app()->language,
                           'refund_type'=>$refund_type,
						   'refund_amount'=>$refund_amount
                        );                                        
                        try {			
                            $jobs = 'ProcessRefund';
                            $jobInstance = new $jobs($get_params);
                            $jobInstance->execute();	
                            CommonUtility::pushJobs( ($refund_type=="full_refund"?'OrderReversal':'OrderPartialReversal'),$get_params);  
                        } catch (Exception $e) { }
                    }							
                }
            }
        } catch (Exception $e) {                                            
            $logs = $e->getMessage();
        }        
        //Yii::log( $logs, CLogger::LEVEL_ERROR);
        //dump($logs);
    }
}
// end class