<?php
require_once 'LeagueCSV/vendor/autoload.php';
require 'php-jwt/vendor/autoload.php';
use League\Csv\Writer;
use League\Csv\Reader;
use League\Csv\Statement;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\Translation\Dumper\DumperInterface;

class CommunicationsController extends Commonmerchant
{
		
	public function beforeAction($action)
	{				
		
		InlineCSTools::registerStatusCSS();
		InlineCSTools::registerOrder_StatusCSS();
			
		return true;
	}
	
    private function includeJS()
	{
		
		AssetsFrontBundle::includeMaps();

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
		Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl."/assets/js/chat.js?time=".time(),CClientScript::POS_END,[
			'type'=>"module"
		]);
	}

	public function actionchats()
	{
		$this->layout = 'backend-no-header';	
		$this->layout = 'backend-merchant-no-header';		

		$chat_enabled = isset(Yii::app()->params['settings'])? (isset(Yii::app()->params['settings']['chat_enabled'])?Yii::app()->params['settings']['chat_enabled']:false) :false;		

		if($chat_enabled){
			$this->render("//communication/chats-frame",[      
				'chat_url'=>Yii::app()->createAbsoluteUrl("/communications/framechat")
			]);
		} else {
			$error = t("Chat is disabled by website owner");
			$this->render("//tpl/error",[
				'error'=>['message'=>$error]
			]);
		}		
	}

    public function actionframechat()
    {		
        $this->layout = 'backend_full';	
        $this->pageTitle = t("Chats");        
        $this->includeJS();		
				
		$ajax_url = str_replace(BACKOFFICE_FOLDER,"",websiteUrl());	
		$main_user_uuid = Yii::app()->merchant->merchant_uuid;        

		$someWords = AttributesTools::someWords();		
		$someWords = json_encode($someWords);
		
		$from_info = [
			'client_uuid'=>Yii::app()->merchant->merchant_uuid,
			'first_name'=>Yii::app()->merchant->first_name,
			'last_name'=>Yii::app()->merchant->last_name,
			'photo'=>Yii::app()->merchant->avatar,
			'user_type'=>'merchant'
		];		
		
		ScriptUtility::registerScript(array(
			"var chat_api='".CJavaScript::quote($ajax_url)."';",		  
			"var main_user_uuid='".CJavaScript::quote($main_user_uuid)."';",
			"var chat_language='".CJavaScript::quote(Yii::app()->language)."';",
			"var from_info='".CJavaScript::quote(json_encode($from_info))."';",
			"var some_words='".CJavaScript::quote($someWords)."';",	      
		),'chat_api');
		
		$chat_enabled = isset(Yii::app()->params['settings'])? (isset(Yii::app()->params['settings']['chat_enabled'])?Yii::app()->params['settings']['chat_enabled']:false) :false;		
		$delete_chat = isset(Yii::app()->params['settings'])? (isset(Yii::app()->params['settings']['chat_enabled_merchant_delete_chat'])?Yii::app()->params['settings']['chat_enabled_merchant_delete_chat']:false) :false;		
		
		if($chat_enabled){
			$this->render("//communication/chats",[
				'ajax_url'=>$ajax_url."chatapi",
				'main_user_uuid'=>$main_user_uuid,
				'main_user_type'=>"merchant",
				'search_type'=>['customer','admin'],
				'search_chat'=>t("Search customer or admin"),
				'delete_chat'=>$delete_chat
			]);
	    } else {
			$error = t("Chat is disabled by website owner");
			$this->render("//tpl/error",[
				'error'=>['message'=>$error]
			]);
		}
    }

}
// end class