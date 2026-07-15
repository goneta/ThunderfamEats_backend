<?php
class CPaymentLogger
{
    public static function afterAdddFunds($card_id=0, $data='')
    {        
        $reference_id = isset($data['reference_id'])?$data['reference_id']:'';        
        $model = AR_wallet_transactions::model()->find("reference_id=:reference_id",[
            ':reference_id'=>$reference_id
        ]);
        if(!$model){            
            return CWallet::inserTransactions($card_id,$data);     
        }  
        throw new Exception( t("Payment transaction already exist #$reference_id") );                     
    }
}
// end class