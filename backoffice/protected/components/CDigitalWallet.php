<?php
class CDigitalWallet
{

    public static function paymentName(){
        return t("Digital Wallet");
    }

    public static function transactionName()
    {        
        return 'digital_wallet';        
    }

    public static function transactionType()
    {        
        //return ['digital_wallet_credit','digital_wallet_debit'];
        return ['digital_wallet'];
    }

    public static function getAvailableBalance($client_id=0)
    {
        $balance = 0;
        try {								
            $card_id = CWallet::createCard( Yii::app()->params->account_type['digital_wallet'],$client_id);
            $balance = CWallet::getBalance($card_id);
        } catch (Exception $e) {             
        }
        return floatval($balance);
    }

    public static function calculateAmountDue($totalAmount=0, $walletBalance=0) 
    {
        if ($walletBalance >= $totalAmount) {
            return 0; // User can pay the entire amount using wallet balance
        } else {
            return $totalAmount - $walletBalance; // Calculate the remaining amount due
        }
    }

    public static function canContinueWithWallet($totalAmount=0, $walletBalance=0 ,$payment_uuid='')
    {
        $amount_due = self::calculateAmountDue($totalAmount,$walletBalance);
        if($amount_due>0){
            if(empty($payment_uuid)){
                throw new Exception( t("Your wallet balance is insufficient to cover the total amount due. Please select a card payment or adjust the payment method to cover the remaining balance.") );
            }
        } 
        return $amount_due;
    }

    public static function debitWallet($order_uuid='')
    {
        $order = COrders::get($order_uuid);
        if($order){ 
            
            //$transaction_amount = $order->wallet_amount>0? $order->wallet_amount : $order->total;
            $transaction_amount = $order->wallet_amount;

            if($transaction_amount<=0){
                throw new Exception( "No wallet amount to debit" );
            }
            
            try { 					
				CDigitalWallet::debitWalletByOrder($order);
			} catch (Exception $e) {
				//die($e->getMessage());		
			}

            $payment_ref = CommonUtility::generateToken("{{ordernew_transaction}}",'payment_reference',
            CommonUtility::generateAplhaCode(10) );

            
            
            $model = new AR_ordernew_transaction;
            $model->order_id = $order->order_id;
            $model->merchant_id = $order->merchant_id;
            $model->client_id = $order->client_id;
            $model->payment_code = CDigitalWallet::transactionName();
            $model->trans_amount = $transaction_amount;
            $model->currency_code = $order->use_currency_code;
            $model->to_currency_code = $order->base_currency_code;
            $model->exchange_rate = $order->exchange_rate;
            $model->admin_base_currency = $order->admin_base_currency;
            $model->exchange_rate_merchant_to_admin = $order->exchange_rate_merchant_to_admin;
            $model->exchange_rate_admin_to_merchant = $order->exchange_rate_admin_to_merchant;
            $model->payment_reference = $payment_ref;
            $model->status = CPayments::paidStatus();				
            $model->save();		

        }
        throw new Exception( 'Order not found' );
    }

    public static function debitWalletByOrder($order)
    {
        if($order){

            $card_id = CWallet::createCard( Yii::app()->params->account_type['digital_wallet'], $order->client_id );             

            $transaction_type = 'debit';
            $model = AR_wallet_transactions::model()->find("reference_id=:reference_id AND transaction_type=:transaction_type",[
                ':reference_id'=>$order->order_id,
                ':transaction_type'=>$transaction_type
            ]);
            if($model){
				throw new Exception( 'Transaction already exist' );
			}

            //$transaction_amount = $order->wallet_amount>0? $order->wallet_amount : $order->total;
            $transaction_amount = $order->wallet_amount;

            $params = array(					  		                 
                'transaction_description'=>"Order #{{order_id}} Successfully Placed with Wallet",                   
                'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id),
                'transaction_type'=>$transaction_type,
                'transaction_amount'=>($transaction_amount*$order->exchange_rate_merchant_to_admin),
                'orig_transaction_amount'=>$transaction_amount,
                'status'=>'paid',                
                'reference_id'=>$order->order_id,
                'reference_id1'=>CDigitalWallet::transactionName(),
                'merchant_base_currency'=>$order->base_currency_code,
                'admin_base_currency'=>$order->admin_base_currency,
                'exchange_rate_merchant_to_admin'=>$order->exchange_rate_merchant_to_admin,
                'exchange_rate_admin_to_merchant'=>$order->exchange_rate_admin_to_merchant,
                'meta_name'=>"order",
				'meta_value'=>$order->order_id
            );            
            $resp = CWallet::inserTransactions($card_id,$params);
            return $resp;
        }
        throw new Exception( 'Order not found' );
    }   

}
// end class