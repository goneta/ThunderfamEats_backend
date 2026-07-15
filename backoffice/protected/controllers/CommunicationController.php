<?php
require_once 'LeagueCSV/vendor/autoload.php';
require 'php-jwt/vendor/autoload.php';
use League\Csv\Writer;
use League\Csv\Reader;
use League\Csv\Statement;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\Translation\Dumper\DumperInterface;

class CommunicationController extends CommonController
{
		
	public function beforeAction($action)
	{									
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
		Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl."/assets/vendor/lame.min.js",CClientScript::POS_END);
		Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl."/assets/js/chat.js?time=".time(),CClientScript::POS_END,[
			'type'=>"module"
		]);
	}

	public function actionchats()
	{
		$this->layout = 'backend-no-header';	
		$this->render("chats-frame",[      
			'chat_url'=>Yii::app()->createAbsoluteUrl("/communication/framechat")
        ]);
	}

    public function actionframechat()
    {		
        $this->layout = 'backend_full';	
        $this->pageTitle = t("Chats");        
        $this->includeJS();		
				
		$ajax_url = str_replace(BACKOFFICE_FOLDER,"",websiteUrl());	
		$main_user_uuid = Yii::app()->user->user_uuid;
		$main_user_type = 'admin';

		$someWords = AttributesTools::someWords();		
		$someWords = json_encode($someWords);
		
		$from_info = [
			'client_uuid'=>Yii::app()->user->user_uuid,
			'first_name'=>Yii::app()->user->first_name,
			'last_name'=>Yii::app()->user->last_name,
			'photo'=>CMedia::getImage(Yii::app()->user->avatar,Yii::app()->user->avatar_path,'@thumbnail',CommonUtility::getPlaceholderPhoto('customer')),
			'user_type'=>'admin'
		];				
		
		ScriptUtility::registerScript(array(
			"var chat_api='".CJavaScript::quote($ajax_url)."';",		  
			"var main_user_uuid='".CJavaScript::quote($main_user_uuid)."';",
			"var chat_language='".CJavaScript::quote(Yii::app()->language)."';",
			"var main_user_type='".CJavaScript::quote($main_user_type)."';",
			"var from_info='".CJavaScript::quote(json_encode($from_info))."';",
			"var some_words='".CJavaScript::quote($someWords)."';",	      
		),'chat_api');

        $this->render("chats",[
            'ajax_url'=>$ajax_url."chatapi",
			'main_user_uuid'=>$main_user_uuid,
			'main_user_type'=>$main_user_type,
			'search_type'=>['customer','merchant'],
			'search_chat'=>t("Search customer or merchant"),
			'delete_chat'=>true
        ]);
    }

	public function actionsettings()
	{
		$this->pageTitle = t("Chat Settings");				

		$model=new AR_option;
		$options = ['chat_enabled' , 'chat_enabled_merchant_delete_chat'];

		if(isset($_POST['AR_option'])){
						
			if(DEMO_MODE){			
			    $this->render('//tpl/error',array(  
			          'error'=>array(
			            'message'=>t("Modification not available in demo")
			          )
			        ));	
			    return false;
			}
			
			$model->attributes=$_POST['AR_option'];
			if($model->validate()){				
				if(OptionsTools::save($options, $model)){
					Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
					$this->refresh();
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			}
		}
		
		if($data = OptionsTools::find($options)){
			foreach ($data as $name=>$val) {
				$model[$name]=$val;	
			}			
		}		
		
		$this->render("chat-settings",array(
			'model'=>$model,
		));
	}

}
// end class