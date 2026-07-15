<?php
class AfterPurchase 
{
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }
    
    public function execute()
    {        
        try { 

            $logs = '';            
            $language = isset($this->data['language'])?$this->data['language']:null;
            $order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:null;
            if($language){
                Yii::app()->language = $language;
            } 

            // $order = COrders::get($order_uuid);
            // CEarnings::CreditOrderCommission($order);

            $options = OptionsTools::find(['points_enabled','digitalwallet_enabled']);
            $points_enabled = isset($options['points_enabled'])?$options['points_enabled']:false;
            $points_enabled = $points_enabled==1?true:false;

            $digitalwallet_enabled = isset($options['digitalwallet_enabled'])?$options['digitalwallet_enabled']:false;
            $digitalwallet_enabled = $digitalwallet_enabled==1?true:false;

            if($points_enabled){
                try {
                    CPoints::debitPoints($order_uuid);
                } catch (Exception $e) {}
            }

            if($digitalwallet_enabled){
                try {
                    CDigitalWallet::debitWallet($order_uuid);
                } catch (Exception $e) {}
            }

        } catch (Exception $e) {                                            
            $logs = $e->getMessage();
        }        
        //Yii::log( $logs, CLogger::LEVEL_ERROR);
    }
}
// end class