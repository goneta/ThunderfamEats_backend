<?php
class ChatdriverController extends SiteCommon
{
  
	public function beforeAction($action)
	{				
		if(isset($_GET)){			
			$_GET = Yii::app()->input->xssClean($_GET);			
		}
		
		if(!Yii::app()->user->isGuest){			
			if(!ACustomer::validateUserStatus()){						
				Yii::app()->user->logout(false);		
				$this->redirect(Yii::app()->createUrl("store/index"));
			}
		}		

		// SEO 
		CSeo::setPage();
		return true;
	}
	
    private function includeJS()
	{			

		$option_data = OptionsTools::find(['firebase_apikey','firebase_domain','firebase_projectid','firebase_storagebucket','firebase_messagingid','firebase_appid']);
		$firebase_apikey = isset($option_data['firebase_apikey'])?$option_data['firebase_apikey']:'';
		$firebase_domain = isset($option_data['firebase_domain'])?$option_data['firebase_domain']:'';
		$firebase_projectid = isset($option_data['firebase_projectid'])?$option_data['firebase_projectid']:'';
		$firebase_storagebucket = isset($option_data['firebase_storagebucket'])?$option_data['firebase_storagebucket']:'';
		$firebase_messagingid = isset($option_data['firebase_messagingid'])?$option_data['firebase_messagingid']:'';
		$firebase_appid = isset($option_data['firebase_appid'])?$option_data['firebase_appid']:'';		

		$firebase_config = json_encode([
			'firebase_apikey'=>$firebase_apikey,
			'firebase_domain'=>$firebase_domain,
			'firebase_projectid'=>$firebase_projectid,
			'firebase_storagebucket'=>$firebase_storagebucket,
			'firebase_messagingid'=>$firebase_messagingid,
			'firebase_appid'=>$firebase_appid,
		]);		

		ScriptUtility::registerScript(array(
		  "var firebase_configuration='".CJavaScript::quote($firebase_config)."';",		  
		),'firebase_configuration');

		$cs = Yii::app()->getClientScript();
		Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl."/assets/js/chat-driver.js?time=".time(),CClientScript::POS_END,[
			'type'=>"module"
		]);
	}


	public function actionIndex()
	{
	    $this->pageTitle = t("Live Chat");
		$this->addBodyClasses("column2-layout");
		$this->layout = 'full-layout';

        $chat_enabled = isset(Yii::app()->params['settings'])? (isset(Yii::app()->params['settings']['chat_enabled'])?Yii::app()->params['settings']['chat_enabled']:false) :false;		
		$chat_enabled = $chat_enabled==1?true:false;
		if(!$chat_enabled){
			$this->render("//store/404-page");
			return false;
		}

        $this->includeJS();		

        $driver = null; $driver_uuid = null; $driver_sender = null; $driver_avatar = null;
        $ajax_url = Yii::app()->createAbsoluteUrl("/chatapi");
        $main_user_uuid = Yii::app()->user->client_uuid;	        
        $order_uuid = Yii::app()->input->get('id');	
        $orderUuid = str_replace("ORD-",'',$order_uuid);
        
        if(!empty($order_uuid)){
			try {
				$order = COrders::get($orderUuid);				
				$order_id = $order->order_id;				
                $driver = CDriver::getDriver($order->driver_id);        
				$driver_uuid = $driver->driver_uuid;			
                $driver_sender = $driver->first_name;		
                $driver_avatar =  CMedia::getImage($driver->photo,$driver->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('driver'));
			} catch (Exception $e) {
				$this->render("//store/404-page");
                Yii::app()->end();
			}
		}

        $from_data = [
			'client_uuid'=>Yii::app()->user->client_uuid,
			'name'=>Yii::app()->user->first_name,
            'first_name'=>Yii::app()->user->first_name,
			'last_name'=>Yii::app()->user->last_name,
            'avatar'=>Yii::app()->user->avatar,
			'photo'=>Yii::app()->user->avatar,
			'user_type'=>"customer"
        ];		
		
        $to_data = [
			'client_uuid'=>$driver_uuid,
            'name'=>$driver_sender,
			'first_name'=> $driver? $driver->first_name :'',
			'last_name'=>$driver?$driver->first_name:'',
            'avatar'=>$driver_avatar,
			'photo'=>$driver_avatar,
			'user_type'=>"driver"
        ];        		
    
        ScriptUtility::registerScript(array(
			"var chat_api='".CJavaScript::quote($ajax_url)."';",		  
			"var main_user_uuid='".CJavaScript::quote($main_user_uuid)."';",            
			"var order_uuid='".CJavaScript::quote($order_uuid)."';",
			"var order_id='".CJavaScript::quote($order_id)."';",
			"var driver_uuid='".CJavaScript::quote($driver_uuid)."';",            
			"var chat_language='".CJavaScript::quote(Yii::app()->language)."';",			
            "var from_data='".CJavaScript::quote(json_encode($from_data))."';",			
            "var to_data='".CJavaScript::quote(json_encode($to_data))."';",			
		),'chat_api');

		$mode_view = Yii::app()->input->get('view');
		$mode_view = !empty($mode_view)?$mode_view:'desktop';
        
        $this->render("//chat/chats",[
			'ajax_url'=>$ajax_url,	
            'main_user_uuid'=>$main_user_uuid,
            'order_uuid'=>$order_uuid,
			'mode_view'=>$mode_view
		]);

	}
	

}
// end class