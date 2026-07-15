<?php
class OrderReversal 
{
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }
    
    public function execute()
    {        
        try { 

            $logs = [];            
            $language = isset($this->data['language'])?$this->data['language']:null;
            $order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:null;
            if($language){
                Yii::app()->language = $language;
            } 

            $order = COrders::get($order_uuid);	                        
            $criteria=new CDbCriteria();
            $criteria->condition="reference_id=:reference_id AND status=:status AND reference_id2<>'reversal'";
            $criteria->params = [
               ':reference_id'=>$order->order_id,
                ':status'=>'paid', 
            ];
            $criteria->order = "transaction_id ASC";
            $model = AR_wallet_transactions::model()->findAll($criteria);
            if($model){        
                
                $params = [];
                foreach ($model as $items) {
                    $card_id = $items->card_id;
                    $transaction_description = "Reversal ".$items->transaction_description;       

                    if($items->reference_id1=="digital_wallet" && $items->transaction_type=="credit"){
                        continue;
                    }
                                                     
                    $mapping = [
                        "points_earned" => "points_redeemed",
                        "points_firstorder" => "points_redeemed",
                        "points_redeemed" => "points_earned",
                        "credit" => "debit",
                        "debit" => "credit"
                    ];
                    $transaction_type = $mapping[$items->transaction_type] ?? 'debit';

                    $parameters = json_decode($items->transaction_description_parameters,true);                    
                    $params = [                        
                        'transaction_date'=>CommonUtility::dateNow(),
                        'transaction_description'=>$transaction_description,
                        'transaction_description_parameters'=>$parameters,
                        'transaction_type'=>$transaction_type,
                        'transaction_amount'=>$items->transaction_amount,
                        'status'=>'paid',	
                         'orig_transaction_amount'=>$items->transaction_amount,
                        'merchant_base_currency'=>$items->merchant_base_currency,
                        'admin_base_currency'=>$items->admin_base_currency,			  
                        'exchange_rate_merchant_to_admin'=>$items->exchange_rate_merchant_to_admin,
                        'exchange_rate_admin_to_merchant'=>$items->exchange_rate_admin_to_merchant,
                        'reference_id'=>$items->reference_id,
                        'reference_id1'=>$items->transaction_id,
                        'reference_id2'=>'reversal'
                    ];                                
                    try {
                        CEarnings::findTransaction($card_id,$transaction_type,$items->reference_id,$items->transaction_id);
                        CWallet::inserTransactions($card_id,$params);
                        $logs[] = "INSERT $items->transaction_id";
                    } catch (Exception $e) {
                        $logs[]= $items->transaction_id ." ". $e->getMessage();                                                
                    }			                    
                }                
            }
        } catch (Exception $e) {                                            
            $logs = $e->getMessage();
        }      
        //dump($logs);
        //Yii::log( $logs, CLogger::LEVEL_ERROR);
    }
}
// end class