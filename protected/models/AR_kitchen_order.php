<?php
class AR_kitchen_order extends CActiveRecord
{	

	public $table_name,$room_name,$item_name,$total_pending,$merchant_uuid,$can_recall;
	/**
	 * Returns the static model of the specified AR class.
	 * @return static the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{kitchen_order}}';
	}
	
	public function primaryKey()
	{
	    return 'kitchen_order_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'kitchen_order_id'=>t("kitchen_order_id"),
			'merchant_id'=>t("Merchant ID"),			
		);
	}
	
	public function rules()
	{
		return array(

		  array('merchant_id,item_token', 
		  'required','message'=> t( Helper_field_required ) ),
		  
		  array('table_uuid,special_instructions,item_status,created_at,updated_at,order_reference,order_ref_id,
		  room_uuid,customer_name,transaction_type,attributes,addons,delivery_date,delivery_time,timezone,date_completed,whento_deliver,sequence',
		  'safe')
		  		  
		);
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){
			if($this->isNewRecord){
				$this->created_at = CommonUtility::dateNow();	
			} else {
				$this->updated_at = CommonUtility::dateNow();	
			}						
			return true;
		} else return true;
	}
	
	protected function afterSave()
	{
		parent::afterSave();

		switch ($this->scenario) {
			case 'insert':
				// if(!empty($this->merchant_uuid)){
				// 	$title = '';
				// 	if($this->whento_deliver=="schedule"){
				// 		$title = t("New Scheduled Alert");
				// 		$message = "There is a new scheduled order with order# {order_reference}.";
				// 	} else {
				// 		$title = t("New Order Alert");
				// 		$message = "There is a new order awaiting acknowledgment with order# {order_reference}.";
				// 	}					
				// 	$message_parameters = [
				// 		'{order_reference}'=>$this->order_reference
				// 	];
				// 	$channel = $this->merchant_uuid."-kitchen";
				// 	$noti = new AR_notifications;    							
				// 	$noti->notication_channel = $channel;
				// 	$noti->notification_event = Yii::app()->params->realtime['notification_event'] ;
				// 	$noti->notification_type = $this->whento_deliver=='schedule'? 'schedule_order': 'new_order';
				// 	$noti->message = $message;		
				// 	$noti->message_parameters = json_encode($message_parameters);
				// 	$noti->meta_data = [
				// 		'title'=>$title,
				// 		'whento_deliver'=>$this->whento_deliver,
				// 		'new_order'=>true,
				// 		'order_reference'=>$this->order_reference
				// 	];
				// 	$noti->save();		

				// 	$push = new AR_push;					
				// 	$push->push_type = 'broadcast';
				// 	$push->provider  = 'firebase';
				// 	$push->channel_device_id = $channel;
				// 	$push->platform = 'android';
				// 	$push->title =  $title;
				// 	$push->body = t($message,$message_parameters);
				// 	$push->save();

				// 	$push = new AR_push;					
				// 	$push->push_type = 'broadcast';
				// 	$push->provider  = 'firebase';
				// 	$push->channel_device_id = $channel;
				// 	$push->platform = 'ios';
				// 	$push->title =  $title;
				// 	$push->body = t($message,$message_parameters);
				// 	$push->save();
				// }				
				break;					

			case "item_status_update":			   
				    $message = "Order reference #{order_reference} is updated by kitchen";
					$message_parameters = json_encode([
						'{order_reference}'=>$this->order_reference
					]);
			        $channel = $this->merchant_uuid;
					$noti = new AR_notifications;    							
					$noti->notication_channel = $channel;
					$noti->notification_event = Yii::app()->params->realtime['notification_event'] ;
					$noti->notification_type = 'new_order';
					$noti->message = $message;		
					$noti->message_parameters = $message_parameters;
					$noti->meta_data = [
						'title'=>t("Order Updated by kitchen"),
						'order_reference'=>$this->order_reference
					];
					$noti->save();		

					// SEND PUSH TO TABLE ORDERING
					Yii::import('ext.runactions.components.ERunActions');	
					$cron_key = CommonUtility::getCronKey();		
					$get_params = array( 
						'kitchen_order_id'=> $this->kitchen_order_id,
						'key'=>$cron_key,
						'language'=>Yii::app()->language,
						'time'=>time()
					);					
					//CommonUtility::saveCronURL(CommonUtility::getHomebaseUrl()."/task/sendUpdatestable?".http_build_query($get_params));
					CommonUtility::runActions(CommonUtility::getHomebaseUrl()."/task/sendUpdatestable?".http_build_query($get_params));

			   break;						
		}

        CCacheData::add();
	}

	protected function afterDelete()
	{
		parent::afterDelete();	
        CCacheData::add();	
	}

	public static function SendNotifications($data = [])
	{
		$kitchen_uuid = isset($data['kitchen_uuid'])?$data['kitchen_uuid']:'';
		$order_reference = isset($data['order_reference'])?$data['order_reference']:'';
		$whento_deliver = isset($data['whento_deliver'])?$data['whento_deliver']:'';
		$merchant_uuid = isset($data['merchant_uuid'])?$data['merchant_uuid']:'';
		$merchant_id = isset($data['merchant_id'])?$data['merchant_id']:'';
		
		$printer_id = '';		
		$printer_model = '';
		try {
			$printer = CommonUtility::getPrinterAutoPrint($merchant_id,'kitchen');
			$printer_id = isset($printer['printer_id'])?$printer['printer_id']:'';
			$printer_model = isset($printer['printer_model'])?$printer['printer_model']:'';
		} catch (Exception $e) {
			//dump($e->getMessage());
		}		

		$title = '';
		if($whento_deliver=="schedule"){
			$title = t("New Scheduled Alert");
			$message = "There is a new scheduled order with order# {order_reference}.";
		} else {
			$title = t("New Order Alert");
			$message = "There is a new order awaiting acknowledgment with order# {order_reference}.";
		}					
		$message_parameters = [
			'{order_reference}'=>$order_reference
		];
		$channel = $merchant_uuid."-kitchen";
		$noti = new AR_notifications;    							
		$noti->notication_channel = $channel;
		$noti->notification_event = Yii::app()->params->realtime['notification_event'] ;
		$noti->notification_type = $whento_deliver=='schedule'? 'schedule_order': 'new_order';
		$noti->message = $message;		
		$noti->message_parameters = json_encode($message_parameters);
		$noti->meta_data = [
			'title'=>$title,
			'whento_deliver'=>$whento_deliver,
			'new_order'=>true,
			'order_reference'=>$order_reference,
			'printer_id'=>$printer_id,
			'printer_model'=>$printer_model
		];
		$noti->save();		

		$push = new AR_push;					
		$push->push_type = 'broadcast';
		$push->provider  = 'firebase';
		$push->channel_device_id = $channel;
		$push->platform = 'android';
		$push->title =  $title;
		$push->body = t($message,$message_parameters);
		$push->save();

		$push = new AR_push;					
		$push->push_type = 'broadcast';
		$push->provider  = 'firebase';
		$push->channel_device_id = $channel;
		$push->platform = 'ios';
		$push->title =  $title;
		$push->body = t($message,$message_parameters);
		$push->save();
	}
			
}
/*end class*/