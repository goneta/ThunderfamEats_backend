<?php
class SendOrderWhatsapp 
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
            $order = COrders::get($order_uuid);
            $merchant_id = $order->merchant_id;            
            $options = OptionsTools::find(['merchant_whatsapp_phone_number','merchant_enabled_whatsapp'],$merchant_id);
            $merchant_whatsapp_phone_number = isset($options['merchant_whatsapp_phone_number'])?$options['merchant_whatsapp_phone_number']:false;
            $merchant_enabled_whatsapp = isset($options['merchant_enabled_whatsapp'])?$options['merchant_enabled_whatsapp']:false;
            $merchant_enabled_whatsapp = $merchant_enabled_whatsapp==1?true:false;                        
            if($merchant_enabled_whatsapp && !empty($merchant_whatsapp_phone_number)){
                CNotifications::sendReceiptByWhatsapp($order_uuid,$merchant_whatsapp_phone_number,2);
            }
        } catch (Exception $e) {                                            
            $logs = $e->getMessage();
        }        
        //Yii::log( $logs, CLogger::LEVEL_ERROR);
    }
}
// end class