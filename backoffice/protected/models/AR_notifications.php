<?php
class AR_notifications extends CActiveRecord
{		   				
	
	public $settings,$realtime_provider;
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
		return '{{notifications}}';
	}
	
	public function primaryKey()
	{
	    return 'notification_uuid';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'notication_channel'=>t("notication_channel"),		    
		);
	}
	
	public function rules()
	{
		return array(
		  array('notication_channel,notification_event,notification_type,message', 
		  'required','message'=> t( Helper_field_required ) ),
		  
		  array('message', 'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')), 
		  array('date_created,ip_address,visible,meta_data,settings,realtime_provider','safe'),
		  
		);
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){
			if($this->isNewRecord){
				$this->notification_uuid = CommonUtility::createUUID('{{notifications}}','notification_uuid');
				$this->date_created = CommonUtility::dateNow();					
			} 
			$this->ip_address = CommonUtility::userIp();	
			
			if(is_array($this->meta_data) && count($this->meta_data)>=1){
				$this->meta_data = json_encode($this->meta_data);
			}						
			
			if($this->scenario=="send"){
				$pusher_status = 'pending';
				$response = null;
				$params=array(
					'notification_type'=> $this->notification_type,
					'message'=>t($this->message,json_decode($this->message_parameters,true)),
					'date'=>PrettyDateTime::parse(new DateTime($this->date_created)),				  
					'meta_data'=>!empty($this->meta_data)?json_decode($this->meta_data,true):'',
					'image_type'=>$this->image_type,
					'image'=>$this->image,
					'notification_uuid'=>$this->notification_uuid
				);						
				try {
					$response = CNotifier::send($this->realtime_provider,$this->settings,$params);					
					$pusher_status = 'process';
				} catch (Exception $e) {
					$pusher_status = $e->getMessage();
				}			
				$this->response = $response;
				$this->status = $pusher_status;
			}			
			
			return true;
		} else return true;
	}
	
	protected function afterSave()
	{
		parent::afterSave();		

		Yii::import('ext.runactions.components.ERunActions');	
		$cron_key = CommonUtility::getCronKey();		
		$get_params = array( 
		   'notification_uuid'=> $this->notification_uuid,
		   'key'=>$cron_key,
		   'language'=>Yii::app()->language
		);			
		
		if($this->scenario=="insert"){
			CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/sendnotifications?".http_build_query($get_params) );
		} 				
			
	}

	protected function afterDelete()
	{
		parent::afterDelete();			
	}
		
}
/*end class*/
