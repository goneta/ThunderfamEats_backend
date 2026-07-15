<?php
class MerchantAftersignup 
{
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }
    
    public function execute()
    {        
        try { 

            $logs = '';          
            $merchant_id  = isset($this->data['merchant_id'])?$this->data['merchant_id']:null;
            $language = isset($this->data['language'])?$this->data['language']:null;
            if($language){
                Yii::app()->language = $language;
            } 

            if(!$merchant_id){
                return;
            }

            $merchant = CMerchants::get($merchant_id);
            try {
                CWallet::getCardID( Yii::app()->params->account_type['merchant'] , $merchant->merchant_id );
            } catch (Exception $e) {
                $wallet = new AR_wallet_cards;	
                $wallet->account_type = Yii::app()->params->account_type['merchant'] ;
                $wallet->account_id = intval($merchant->merchant_id);
                $wallet->save();
            }	

            $options = OptionsTools::find(['enabled_copy_payment_setting']);
            $enabled = isset($options['enabled_copy_payment_setting'])?$options['enabled_copy_payment_setting']:false;
            $enabled = $enabled==1?true:false;
                        
            /*ADD DEFAULT PAYMENT GATEWAY*/
            if(!$enabled){			                
                $payment_list = CommonUtility::getDataToDropDown("{{payment_gateway}}",'payment_code','payment_code',
                "WHERE status='active'","ORDER BY sequence ASC");                
                if(is_array($payment_list) && count($payment_list)>=1){				
                    $payment_data  = array();
                    foreach ($payment_list as $payment_item) {
                        $payment_data[]=$payment_item;
                    }				                    
                    MerchantTools::saveMerchantMeta($merchant->merchant_id,$payment_data,'payment_gateway');
                } 
            }

        } catch (Exception $e) {                                            
            $logs = $e->getMessage();
        }                
        //Yii::log( $logs, CLogger::LEVEL_ERROR);
    }
}
// end class