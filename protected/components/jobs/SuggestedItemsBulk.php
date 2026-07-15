<?php
class SuggestedItemsBulk
{
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }
    
    public function execute()
    {        
        try { 

            $logs = '';            
            $items_ids  = isset($this->data['items_ids'])?$this->data['items_ids']:null;            
            $language = isset($this->data['language'])?$this->data['language']:null;
            $status = isset($this->data['status'])?$this->data['status']:null;

            if($language){
                Yii::app()->language = $language;
            } 

            $settings = CNotificationData::getRealtimeSettings();
            $provider = isset($settings['provider'])?$settings['provider']:'';				            
            $settings['notification_event'] = Yii::app()->params->realtime['notification_event'];		            
                               
            $stmt = "
            SELECT a.item_id, a.merchant_id,a.item_name,
            b.merchant_uuid
            FROM {{item}} a
            LEFT JOIN {{merchant}} b 
            ON
            a.merchant_id = b.merchant_id
            WHERE 
            a.item_id IN (".CommonUtility::arrayToQueryParameters($items_ids).")
            ";            
            if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
                $data = [];
                foreach ($res as $items) {
                    if(isset($data[$items['merchant_id']])){
                        $data[$items['merchant_id']][] = $items;  
                    } else $data[$items['merchant_id']][] = $items;                    
                }
                                
                foreach ($data as $merchant_id=>$item) {                    
                    $line_items = ''; $merchant_uuid = '';
                    foreach ($item as $items) {                              
                        $line_items.=$items['item_name'].",";
                        $merchant_uuid = $items['merchant_uuid'];
                    }
                    $line_items = substr($line_items,0,-1);

                    $settings['notication_channel'] = $merchant_uuid;

                    $noti = new AR_notifications;
                    $noti->scenario = 'send';
                    $noti->notication_channel = $merchant_uuid;
                    $noti->notification_event = Yii::app()->params->realtime['notification_event'] ;
                    $noti->notification_type = 'suggested_items';
                    $noti->message = $status=='approved' ? "The following suggested item {line_items} has been approved" : 'Were sorry, but {line_items} could not be approved at this time.';
                    $noti->message_parameters = json_encode([
                        '{line_items}'=>$line_items
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
                }
            }            
        } catch (Exception $e) {                                            
            $logs = $e->getMessage();
        }        
        //Yii::log( $logs, CLogger::LEVEL_ERROR);
    }
}
// end class