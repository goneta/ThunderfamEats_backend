<?php
class AR_customer_request extends CActiveRecord
{	

	public $table_name,$room_name,$merchant_uuid,$title;
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
		return '{{customer_request}}';
	}
	
	public function primaryKey()
	{
	    return 'request_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'request_id'=>t("request id"),		    
		);
	}
	
	public function rules()
	{
		return array(

		  array('merchant_id,cart_uuid,table_uuid,request_item', 
		  'required','message'=> t( Helper_field_required ) ),
		  
		  array('request_time,request_status,is_view','safe')
		  		  
		);
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){
			if($this->isNewRecord){
				$this->request_time = CommonUtility::dateNow();	
			} 					
			return true;
		} else return true;
	}
	
	protected function afterSave()
	{
		parent::afterSave();
        CCacheData::add();

		$noti = new AR_notifications;    							
		$noti->notication_channel = $this->merchant_uuid;
		$noti->notification_event = Yii::app()->params->realtime['notification_event'] ;
		$noti->notification_type = 'customer_request';
		$noti->message = "Request - {request_item} Table #{room_name}-{table_name}";				
		$noti->message_parameters = json_encode([
			'{request_item}'=>$this->request_item,
			'{room_name}'=>$this->room_name,
			'{table_name}'=>$this->table_name,
		]);
		$meta_data = [
			'notification_type'=>"call_staff",
			'table_uuid'=>$this->table_uuid,
			'request_id'=>$this->request_id,
			'title'=>''
		];
		$noti->meta_data = json_encode($meta_data);
		$noti->save();

	}

	protected function afterDelete()
	{
		parent::afterDelete();	
        CCacheData::add();	
	}
			
}
/*end class*/