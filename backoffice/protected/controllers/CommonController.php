<?php
class CommonController extends CController
{

	public function __construct($id,$module=null){
		parent::__construct($id,$module);
		// If there is a post-request, redirect the application to the provided url of the selected language 
		if(isset($_POST['language'])) {
			$lang = $_POST['language'];
			$MultilangReturnUrl = $_POST[$lang];
			$this->redirect($MultilangReturnUrl);
		}

		if(isset($_GET['setlang'])) {						
			Yii::app()->cache->flush();
		}

		// Set the application language if provided by GET, session or cookie
		if(isset($_GET['language'])) {
			Yii::app()->cache->flush();
			Yii::app()->language = $_GET['language'];
			Yii::app()->user->setState('language', $_GET['language']); 
			$cookie = new CHttpCookie('language', $_GET['language']);
			$cookie->expire = time() + (60*60*24*365); // (1 year)
			Yii::app()->request->cookies['language'] = $cookie; 
		} else if (Yii::app()->user->hasState('language')){
			Yii::app()->language = Yii::app()->user->getState('language');			
		} else if(isset(Yii::app()->request->cookies['language'])){
			Yii::app()->language = Yii::app()->request->cookies['language']->value;			
			if(!empty(Yii::app()->language) && strlen(Yii::app()->language)>=10){
				Yii::app()->language = KMRS_DEFAULT_LANGUAGE;
			}
		} else {
			$options = OptionsTools::find(['default_language']);
			$default_language = isset($options['default_language'])?$options['default_language']:'';			
			if(!empty($default_language)){
				Yii::app()->language = $default_language;
			} else Yii::app()->language = KMRS_DEFAULT_LANGUAGE;
		}	
	}

	public function createMultilanguageReturnUrl($lang='en'){
		if (count($_GET)>0){
			$arr = $_GET;
			$arr['language']= $lang;
		}
		else 
			$arr = array('language'=>$lang,'setlang'=>1);
		return $this->createUrl('', $arr);
	}
	
	public function filters()
	{
		return array(
			'accessControl',
			array(
			  'application.filters.HtmlCompressorFilter',
			)
		);
	}
	
	public function accessRules()
	{		
		return array(			
		    array('allow',
                'actions'=>array('logout','error','migrate'),
                'users'=>array('*'),
            ),
			array('allow', 			    
				'expression'=>array('AdminUserIdentity','verifyAccess'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
	
	public function init()
	{
		
		$this->initSettings();
		
		$detect = CommonUtility::MobileDetect();
		$is_mobile = false;

		$this->layout = 'backend';	
		$ajaxurl = Yii::app()->createUrl("/backend");
				
		if ($detect->isMobile() || $detect->isTablet() ) {						
			//$is_mobile=true;
		} 
		
		Yii::app()->params['isMobile'] = $is_mobile;
		
		$realtime = AR_admin_meta::getMeta(array('realtime_app_enabled','realtime_provider','webpush_app_enabled','webpush_provider'));		
		$realtime_app_enabled = isset($realtime['realtime_app_enabled'])?$realtime['realtime_app_enabled']['meta_value']:'';
		$realtime_provider = isset($realtime['realtime_provider'])?$realtime['realtime_provider']['meta_value']:'';
		
		$webpush_app_enabled = isset($realtime['webpush_app_enabled'])?$realtime['webpush_app_enabled']['meta_value']:'';
		$webpush_provider = isset($realtime['webpush_provider'])?$realtime['webpush_provider']['meta_value']:'';
				
		$include = array('backend-core','backend-css','admin-js');
		if($realtime_app_enabled==1){
		   array_unshift($include, $realtime_provider);
		}
				
		if($webpush_app_enabled==1){
		   array_unshift($include, "webpush_".$webpush_provider );
		}

		if (preg_match("/schedule/i",Yii::app()->request->requestUri)) {			
			array_unshift($include, 'fullcalendar');
		}
						
		if(Yii::app()->controller->id=="driver"){
			if (preg_match("/mapview/i",Yii::app()->request->requestUri)) {		
				$include = [];
				$include[]='quasar';				
				$include[]='task';
				$include[]='materialfont';
			}
			if (preg_match("/orders/i",Yii::app()->request->requestUri)) {		
				$include = [];
				$include[]='quasar';
				$include[]='task';
				$include[]='materialfont';
			}
			if($realtime_app_enabled==1){
				array_unshift($include, $realtime_provider);
			}
	    }
		
		if(Yii::app()->controller->id=="communication"){
			if (preg_match("/framechat/i",Yii::app()->request->requestUri)) {		
				$include = [];
				$include[]='quasar';				
				$include[]='chat';
				$include[]='materialfont';
			}
		}
									
		AssetsBundle::registerBundle($include);				
		
		$upload_ajaxurl = Yii::app()->createUrl("/uploadfiles");
		$api_url = Yii::app()->createUrl("/api");
		
		
		$translation_vendor = AttributesTools::translationVendor();
		$daysofweek = Ccalendar::daterangepickerdaysOfWeek();
		$monthsname = Ccalendar::daterangepickermonthNames();		

		$someWords = AttributesTools::someWords();				
		$someWords = json_encode($someWords);
		$paginationSize = AttributesTools::paginationPageSizes();
		
		$sounds_order = isset(Yii::app()->params['settings']['admin_sounds_order'])?Yii::app()->params['settings']['admin_sounds_order']:'';
		$sounds_chat = isset(Yii::app()->params['settings']['admin_sounds_chat'])?Yii::app()->params['settings']['admin_sounds_chat']:'';				
		$front_url = CommonUtility::getHomebaseUrl();		
		$sounds_order = !empty($sounds_order)? $front_url."/upload/sounds/".basename($sounds_order) :'';
		$sounds_chat = !empty($sounds_chat)? $front_url."/upload/sounds/".basename($sounds_chat) :'';
				
		ScriptUtility::registerScript(array(
		  "var ajaxurl='$ajaxurl';",
		  "var upload_ajaxurl='$upload_ajaxurl';",
		  "var api_url='$api_url';",
		  "var is_mobile='$is_mobile';",
		  "var translation_vendor='".CJavaScript::quote(json_encode($translation_vendor))."';",
		  "var daysofweek='".CJavaScript::quote(json_encode($daysofweek))."';",
		  "var monthsname='".CJavaScript::quote(json_encode($monthsname))."';",
		  "var printerServer='".CJavaScript::quote(Yii::app()->params['printer_server'])."';",
		  "var some_words='".CJavaScript::quote($someWords)."';",	      
		  "var paginationSize='".CJavaScript::quote(json_encode($paginationSize))."';",
		  "var sounds_order='".CJavaScript::quote($sounds_order)."';",
		  "var sounds_chat='".CJavaScript::quote($sounds_chat)."';",
		),'admin_global_script');		
				
	}
	
	public function initSettings()
	{
		Yii::app()->params['settings'] = OptionsTools::find(array(
			  'website_date_format_new','website_time_format_new','website_timezone_new',
			  'image_resizing','image_driver','enabled_language_bar','default_language','backend_version',
			  'map_provider','google_geo_api_key','google_maps_api_key','mapbox_access_token','driver_map_enabled_cluster','driver_task_take_pic','website_title',
			  'multicurrency_enabled','multicurrency_allowed_merchant_choose_currency','admin_enabled_continues_alert','admin_continues_alert_interval',
			  'site_food_avatar','site_user_avatar','site_merchant_avatar','backend_phone_mask','yandex_javascript_api','yandex_language',
			  'yandex_geosuggest_api','yandex_geocoder_api','yandex_static_api','yandex_distance_api','home_search_mode','admin_sounds_chat','admin_sounds_order'
		));		

		/*SET TIMEZONE*/
		$timezone = isset(Yii::app()->params['settings']['website_timezone_new'])?Yii::app()->params['settings']['website_timezone_new']:'';		
		if (is_string($timezone) && strlen($timezone) > 0){
		   Yii::app()->timeZone=$timezone;		   
		}
		
		$realtime = AR_admin_meta::getMeta(array('realtime_app_enabled','realtime_provider',
		  'pusher_key','pusher_cluster','ably_apikey','piesocket_api_key','piesocket_websocket_api','piesocket_clusterid'
		));				
		$realtime_app_enabled = isset($realtime['realtime_app_enabled'])?$realtime['realtime_app_enabled']['meta_value']:'';
		
		$realtime_provider = isset($realtime['realtime_provider'])?$realtime['realtime_provider']['meta_value']:'';
		$pusher_key = isset($realtime['pusher_key'])?$realtime['pusher_key']['meta_value']:'';
		$pusher_cluster = isset($realtime['pusher_cluster'])?$realtime['pusher_cluster']['meta_value']:'';		
		$ably_apikey = isset($realtime['ably_apikey'])?$realtime['ably_apikey']['meta_value']:'';
		
		$piesocket_api_key = isset($realtime['piesocket_api_key'])?$realtime['piesocket_api_key']['meta_value']:'';
		$piesocket_websocket_api = isset($realtime['piesocket_websocket_api'])?$realtime['piesocket_websocket_api']['meta_value']:'';
		$piesocket_clusterid = isset($realtime['piesocket_clusterid'])?$realtime['piesocket_clusterid']['meta_value']:'';

		$continues_alert_interval = isset(Yii::app()->params['settings']['admin_continues_alert_interval'])?Yii::app()->params['settings']['admin_continues_alert_interval']:30;		
		$continues_alert_interval = $continues_alert_interval>0?$continues_alert_interval:30;

		$backend_phone_mask = isset(Yii::app()->params['settings']['backend_phone_mask'])?Yii::app()->params['settings']['backend_phone_mask']:'';		
		$backend_phone_mask = !empty($backend_phone_mask)?$backend_phone_mask:'+000000000000';		

		$list_limit = Yii::app()->params['list_limit'];		

		ScriptUtility::registerScript(array(
			"var continues_alert_interval='$continues_alert_interval';",	
			"var backend_phone_mask='".CJavaScript::quote($backend_phone_mask)."';",
			"var list_limit='".CJavaScript::quote($list_limit)."';"
		  ),'continues_alert_interval');	
		
		Yii::app()->params['realtime_settings'] = array(
		  'enabled'=>$realtime_app_enabled,
		  'provider'=>$realtime_provider,
		  'key'=>$pusher_key,
		  'cluster'=>$pusher_cluster,
		  'ably_apikey'=>$ably_apikey,
		  'piesocket_api_key'=>$piesocket_api_key,
		  'piesocket_websocket_api'=>$piesocket_websocket_api,
		  'piesocket_clusterid'=>$piesocket_clusterid,
		);
		
		Price_Formatter::init();						
	}
	
	public function actionDatableLocalize()
	{
		$data = CommonUtility::dataTablesLocalization();
		header('Content-Type: application/json; charset="UTF-8"');
		echo CJSON::encode($data);
	}
	
	public function jsonResponse()
	{
		$resp=array('code'=>$this->code,'msg'=>$this->msg,'details'=>$this->details);
		echo CJSON::encode($resp);
		Yii::app()->end();
	}
	
	public function DataTablesNodata()
	{
		if (isset($_POST['draw'])){
			$feed_data['draw']=(integer)$_POST['draw'];
		} else $feed_data['draw']=1;	   
		     
        $feed_data['recordsTotal']=0;
        $feed_data['recordsFiltered']=0;
        $feed_data['data']=array();		        
        echo CJSON::encode($feed_data);    	
	}

	public function DataTablesData($feed_data='')
	{
		header('Content-type: application/json');
	    echo CJSON::encode($feed_data);    
    }    
    
    /*public function responseJson($data)
    {
    	header('Content-type: application/json');
		echo CJSON::encode($data);
    }     */   
    
    public function responseJson()
    {
    	header('Content-type: application/json');
		$resp=array('code'=>$this->code,'msg'=>$this->msg,'details'=>$this->details);
		echo CJSON::encode($resp);
		Yii::app()->end();
    }       

	public function responseSelect2($data)
    {
    	header('Content-type: application/json');
		echo CJSON::encode($data);
		Yii::app()->end();
    }     
    
}
/*end class*/