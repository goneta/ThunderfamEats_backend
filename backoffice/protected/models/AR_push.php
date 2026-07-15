<?php
class AR_push extends CActiveRecord
{		   				
	
	public $settings;
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
		return '{{push}}';
	}
	
	public function primaryKey()
	{
	    return 'push_uuid';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'push_type'=>t("push_type"),		    
		);
	}
	
	public function rules()
	{
		return array(
		  
		  array('push_type','required','message'=>t("Push type is required")),
		  array('provider','required','message'=>t("Provider is required")),
		  array('platform','required','message'=>t("Platform is required")),
		  array('channel_device_id','required','message'=>t("Channel is required")),
		  array('title','required','message'=>t("Title is required")),
		  array('body','required','message'=>t("Body is required")),
		  
		  array('title,body', 'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')), 
		  array('merchant_id,date_created,ip_address,response,image,path,settings','safe'),
		  
		);
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){
			if($this->isNewRecord){
				$this->push_uuid = CommonUtility::createUUID('{{push}}','push_uuid');
				$this->date_created = CommonUtility::dateNow();					
			} 
			$this->ip_address = CommonUtility::userIp();	

			if($this->scenario=="send" && $this->provider=="pusher"){
				$response='';
				try {				
					$response = CPushweb::send($this->settings,[
						'title'=>$this->title,
						'body'=>$this->body,
					]);
				} catch (Exception $e) {
					$response = $e->getMessage();
				}					
				$this->response = $response;
				$this->status = 'process';
			} elseif ($this->scenario=="send" && $this->provider=="firebase" ){
				$device_id = $this->channel_device_id;
				$json_path = AttributesTools::getPushJsonFile();
				$target = $this->push_type=='broadcast'?'topic':'token';
				if($this->merchant_id>0){
					$json_path = AttributesTools::getMerchantPushJsonFile($this->merchant_id);
				}
				$image = $this->image;
				if(!empty($this->image)){
					$image = CMedia::getImage($this->image,$this->path);
				}
				$params_message = [
					'device_id'=>$device_id,
					'push_type'=>$this->push_type,
					'target'=>$target,
					'json_path'=>$json_path,
					'dialog_title'=>isset($this->settings['dialog_title'])?$this->settings['dialog_title']:'',
					'title'=>$this->title,
					'body'=>$this->body,
					'icon' => 'stock_ticker_update',
					'color'=>isset($this->settings['color'])?$this->settings['color']:'',
					'sound'=>isset($this->settings['sound'])?$this->settings['sound']:'',
					'channelId'=>isset($this->settings['channel'])?$this->settings['channel']:'',
					'image'=>$image
				];							
				$response = CNotifications::sendFirebasePush($this->platform,$params_message,$json_path);
				$this->status = "process";
				$this->response = $response;				
			}
			
			return true;
		} else return true;
	}
	
	protected function afterSave()
	{		
		Yii::import('ext.runactions.components.ERunActions');	
		$cron_key = CommonUtility::getCronKey();		
		$get_params = array( 
		   'push_uuid'=> $this->push_uuid,
		   'key'=>$cron_key,
		);			
				
		if($this->scenario=="insert"){
			if($this->platform=="web"){
				CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/sendwebpush?".http_build_query($get_params) );
			} else if( $this->platform=="android" || $this->platform=="ios" ){				
				CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/senddevicepush?".http_build_query($get_params) );
			} else if ( $this->platform=='pwa'){										
				CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/sendpwapush?".http_build_query($get_params) );
			}
		}

		parent::afterSave();
	}

	protected function afterDelete()
	{
		parent::afterDelete();			
	}
		
}
/*end class*/
