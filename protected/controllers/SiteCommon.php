<?php
require 'php-jwt/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class SiteCommon extends CController
{
	public $layout='main_layout';	
	public $code=2,$msg,$details,$data;			
	
	public function __construct($id,$module=null){
		parent::__construct($id,$module);
		// If there is a post-request, redirect the application to the provided url of the selected language 
		if(isset($_POST['language'])) {
			$lang = $_POST['language'];
			$MultilangReturnUrl = $_POST[$lang];
			$this->redirect($MultilangReturnUrl);
		}
		// Set the application language if provided by GET, session or cookie		
		if(isset($_GET['language'])) {			
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
		} else {						
			$arr = array('language'=>$lang);			
		}			
		$url =  $this->createUrl('', $arr);
		if (preg_match("/menu/i", $url)) {
			$pathInfo = Yii::app()->request->getPathInfo();						
			$url =  $this->createUrl("/$pathInfo/?".http_build_query($arr) );
		} else {			
			if(Yii::app()->controller->id=="page"){				
				$clean_url= explode("?",Yii::app()->request->requestUri)[0];
				$clean_url = str_replace(Yii::app()->baseUrl,'',$clean_url);				
				$url =  $this->createUrl("$clean_url/?".http_build_query($arr) );
			}			
		}
		return $url;
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
		   array('deny',
                'actions'=>array('checkout','logout',
                    'orders','booking','vouchers','favourites','payments','addresses','points','profile','change_password',
                    'manage_account','orderHistory','pdf','orderSummary','orderDetails','orderBuyAgain','getAddressAttributesx',
                    'getAddresses','getAdddress','SaveAddress','deleteAddress','PaymentMethod','MyPayments','SavedPaymentProvider',
                    'deletePayment','saveStoreList','SaveStore','saveProfile','updatePassword','requestData','RequestEmailCode',
                    'verifyAccountDelete','getNotifications','checkout_details','getPointsTransaction','getAvailablePoints','applyPoints',
					'getCartpoints','getPointsthresholds'
                   ),
                'users'=>array('?'),
            ),           
		);
	}
	
	public function behaviors() {
		return array(
	        'BodyClassesBehavior' => array(
	            'class' => 'ext.yii-body-classes.BodyClassesBehavior'
	        ),        
	    );
    }
	
	public function init()
	{
		
		$this->initSettings();
		
		$detect = CommonUtility::MobileDetect();
		$is_mobile = false;
		
		$ajaxurl = Yii::app()->createUrl("/api");
				
		if ($detect->isMobile() || $detect->isTablet() ) {						
			$is_mobile=true;
		} 

		$is_rtl = false;
		if($language = AttributesTools::getLanguageData(Yii::app()->language)){			
			$is_rtl = $language->rtl==1?true:false;
		}
		
		Yii::app()->params['is_rtl'] = $is_rtl;
		Yii::app()->params['isMobile'] = $is_mobile;
		
		if($is_rtl){
			$include = array('front-core-rtl','owl-carousel','swiperjs','front-css','front-js');
		} else $include = array('front-core','owl-carousel','swiperjs','front-css','front-js');		

		$realtime = AR_admin_meta::getMeta(array('realtime_app_enabled','realtime_provider','webpush_app_enabled','webpush_provider'));		
		$realtime_app_enabled = isset($realtime['realtime_app_enabled'])?$realtime['realtime_app_enabled']['meta_value']:'';
		$realtime_provider = isset($realtime['realtime_provider'])?$realtime['realtime_provider']['meta_value']:'';
		
		$webpush_app_enabled = isset($realtime['webpush_app_enabled'])?$realtime['webpush_app_enabled']['meta_value']:'';
		$webpush_provider = isset($realtime['webpush_provider'])?$realtime['webpush_provider']['meta_value']:''; 
		
		if($realtime_app_enabled==1 && !empty($realtime_provider)){	
		   array_unshift($include, $realtime_provider);
		}
				
		if($webpush_app_enabled==1 && !empty($webpush_provider)){
		   array_unshift($include, "webpush_".$webpush_provider );
		}

		$cookie_consent_enabled = isset(Yii::app()->params['settings']['cookie_consent_enabled'])?Yii::app()->params['settings']['cookie_consent_enabled']:false;
		$cookie_consent_enabled = $cookie_consent_enabled==1?true:false;
		if($cookie_consent_enabled){
			array_push($include, 'cookie_consent');
		}

		// CHAT
		if(Yii::app()->controller->id=="account"){
			if (preg_match("/framechat/i",Yii::app()->request->requestUri)) {		
				$include = [];
				$include[]='quasar';				
				$include[]='chat';
			}
			if (preg_match("/chatorderframe/i",Yii::app()->request->requestUri)) {		
				$include = [];
				$include[]='quasar';				
				$include[]='chat';
			}
		} else if ( Yii::app()->controller->id=="chatdriver" ){
			$include = [];
			$include[]='quasar';				
			$include[]='chat';
		}
		
		$setttings = Yii::app()->params['settings'];
		$home_search_mode = isset($setttings['home_search_mode'])?$setttings['home_search_mode']:'address';		
		$location_searchtype = isset($setttings['location_searchtype'])?$setttings['location_searchtype']:'';		
		$home_search_mode = !empty($home_search_mode)?$home_search_mode:'address';				
		if($home_search_mode=="location"){						
			$requestUri = Yii::app()->request->requestUri;
			if (!preg_match("/framechat/i", $requestUri)) {
				$include[] = 'front-location-js';
			}
		}		
											
		AssetsFrontBundle::registerBundle($include);
		$settings = AttributesTools::MoneyConfig();
		$someWords = AttributesTools::someWords();
		$someWords = json_encode($someWords);

		$site_url = Yii::app()->createAbsoluteUrl("/");		

		$client_token = '';
		if(!Yii::app()->user->isGuest){		
			if(!isset(Yii::app()->user->logintoken)){
				Yii::app()->user->logout(false);		
				return;
			}			
			$payload = [
				'iss'=>Yii::app()->request->getServerName(),
				'sub'=>0,				
				'iat'=>time(),	
				'token'=>Yii::app()->user->logintoken,
				'username'=>Yii::app()->user->username,
				'hash'=>Yii::app()->user->hash,			
			];		
			$client_token = JWT::encode($payload, CRON_KEY, 'HS256'); 				
		}
		
		$sounds_order = isset(Yii::app()->params['settings']['admin_sounds_order'])?Yii::app()->params['settings']['admin_sounds_order']:'';
		$sounds_chat = isset(Yii::app()->params['settings']['admin_sounds_chat'])?Yii::app()->params['settings']['admin_sounds_chat']:'';				
		$front_url = CommonUtility::getHomebaseUrl();		
		$sounds_order = !empty($sounds_order)? $front_url."/upload/sounds/".basename($sounds_order) :'';
		$sounds_chat = !empty($sounds_chat)? $front_url."/upload/sounds/".basename($sounds_chat) :'';		
					
		ScriptUtility::registerScript(array(
		  "var ajaxurl='$ajaxurl';",
		  "var site_url='$site_url';",
		  "var is_mobile='$is_mobile';",
		  "var money_config='".CJavaScript::quote($settings)."';",		  
		  "var some_words='".CJavaScript::quote($someWords)."';",		  
		  "var is_rtl='".CJavaScript::quote($is_rtl)."';",		
		  "var language='".CJavaScript::quote(Yii::app()->language)."';",		
		  "var client_token='".CJavaScript::quote($client_token)."';",
		  "var list_limit='".CJavaScript::quote(20)."';",
		  "var location_searchtype='".CJavaScript::quote($location_searchtype)."';",
		  "var sounds_order='".CJavaScript::quote($sounds_order)."';",
		  "var sounds_chat='".CJavaScript::quote($sounds_chat)."';",
		),'front_script');
	}
	
	public function initSettings()
	{
		Yii::app()->params['settings'] = OptionsTools::find(array(
			  'website_date_format_new','website_time_format_new','map_provider','google_geo_api_key','google_maps_api_key',
			  'mapbox_access_token','home_search_unit_type','website_timezone_new','website_title',
			  'captcha_customer_signup','website_address','website_contact_phone','website_contact_email',
			  'image_resizing','image_driver','website_logo','review_image_resize_width','merchant_specific_country',
			  'merchant_enabled_registration_capcha','registration_program','registration_terms_condition',
			  'enabled_language_bar_front','allow_return_home','enabled_google_analytics','google_analytics_tracking_id',
			  'enabled_fb_pixel','fb_pixel_id','enabled_auto_pwa_redirect','pwa_url','android_download_url','ios_download_url',
			  'enabled_home_steps','enabled_home_promotional','enabled_signup_section','enabled_mobileapp_section','enabled_social_links',
			  'facebook_page','twitter_page','google_page','instagram_page','linkedin_page','enabled_auto_detect_address','runactions_enabled',
			  'cookie_consent_enabled','menu_layout','category_position','merchant_enabled_registration','merchant_allow_login_afterregistration',
			  'multicurrency_enabled','multicurrency_enabled_hide_payment','multicurrency_enabled_checkout_currency','points_enabled','points_earning_rule',
			  'points_earning_points','points_minimum_purchase','points_maximum_purchase','points_redeemed_points','points_redeemed_value',
			  'points_minimum_redeemed','points_maximum_redeemed','points_redemption_policy','points_registration','points_review','points_first_order',
			  'points_booking','booking_time_format','points_cover_cost','site_user_avatar','site_merchant_avatar','site_food_avatar',
			  'default_location_lat','default_location_lng','digitalwallet_enabled','digitalwallet_enabled_topup',
			  'digitalwallet_topup_minimum','digitalwallet_topup_maximum','digitalwallet_transaction_limit','digitalwallet_refund_to_wallet',
			  'chat_enabled','chat_enabled_merchant_delete_chat','points_use_thresholds','points_expiry','enabled_include_utensils','enabled_review','address_format_use',
			  'password_reset_options','mobilephone_settings_default_country','mobilephone_settings_country','yandex_javascript_api','yandex_language',
			  'yandex_geosuggest_api','yandex_geocoder_api','yandex_static_api','yandex_distance_api','cancel_order_enabled','custom_field_enabled',
			  'home_search_mode','location_default_country','location_searchtype','location_enabled_map_selection','driver_enabled_registration','admin_sounds_order',
			  'admin_sounds_chat','maintenance_mode','website_twentyfour_format','strict_to_wallet','show_prices_commission','map_custom_style','use_local_distance',
			  'order_assigment_flow'
		));			

		$setttings = Yii::app()->params['settings'];				
		$android_download_url = isset($setttings['android_download_url'])?$setttings['android_download_url']:'#';
		$ios_download_url = isset($setttings['ios_download_url'])?$setttings['ios_download_url']:'#';		
		Yii::app()->params['android_download_url'] = $android_download_url;
		Yii::app()->params['ios_download_url'] = $ios_download_url;
						
		Yii::app()->params['map_credentials'] =  CommonUtility::MapCredentials();
		
		/*SET TIMEZONE*/
		$timezone = Yii::app()->params['settings']['website_timezone_new'];		
		if (is_string($timezone) && strlen($timezone) > 0){
		   Yii::app()->timeZone=$timezone;		   
		}
		Price_Formatter::init();	
				
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
	    echo CJSON::encode($feed_data);    
    }    
    
    public function responseJson()
    {
    	header('Content-type: application/json');
		$resp=array('code'=>$this->code,'msg'=>$this->msg,'details'=>$this->details);
		echo CJSON::encode($resp);
		Yii::app()->end();
    }        

	public function maintenanceMode()
	{
		// CHECK MAINTENANCE MODE
		$maintenance_mode = isset(Yii::app()->params['settings']['maintenance_mode'])?Yii::app()->params['settings']['maintenance_mode']:false;
		$maintenance_mode = $maintenance_mode==1?true:false;
		if($maintenance_mode){
			$this->renderPartial("//store/maintenance");
		    Yii::app()->end();
		}		
	}
    
}
/*end class*/