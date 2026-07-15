<?php
class SuggestedItems
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
            $item_id  = isset($this->data['item_id'])?$this->data['item_id']:null;
            $language = isset($this->data['language'])?$this->data['language']:null;
            $status = isset($this->data['status'])?$this->data['status']:null;

            if($language){
                Yii::app()->language = $language;
            } 

            $settings = CNotificationData::getRealtimeSettings();
            $provider = isset($settings['provider'])?$settings['provider']:'';				            
            $settings['notification_event'] = Yii::app()->params->realtime['notification_event'];		            
                        
            $merchant = CMerchants::get($merchant_id);
            $settings['notication_channel'] = $merchant->merchant_uuid;

            $item = Citems::findItem($item_id);
            $item_name = $item->item_name;
            
            $noti = new AR_notifications;    
            $noti->scenario = 'send';							
            $noti->notication_channel = $merchant->merchant_uuid;
            $noti->notification_event = Yii::app()->params->realtime['notification_event'] ;
            $noti->notification_type = 'suggested_items';
            $noti->message = $status=='approved' ? "Your suggested item {item_name} has been approved" : 'Were sorry, but {item_name} could not be approved at this time.';
            $noti->message_parameters = json_encode([
                '{item_name}'=>$item_name
            ]);
            $noti->image_type = 'icon';
            $noti->image = 'zmdi zmdi-info-outline';
            $noti->meta_data = [
                'page'=>'suggested_items',
                'page_url'=>Yii::app()->createAbsoluteUrl("/merchant/suggested_items")
            ];
            $noti->settings = $settings;	
            $noti->realtime_provider = $provider;	            
            $noti->save();                           

        } catch (Exception $e) {                                            
            $logs = $e->getMessage();
        }        
        //Yii::log( $logs, CLogger::LEVEL_ERROR);
    }
}
// end class