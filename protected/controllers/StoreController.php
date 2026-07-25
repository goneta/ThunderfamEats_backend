<?php
class StoreController extends SiteCommon
{
	public function beforeAction($action)
	{						
		$settings = Yii::app()->params['settings'];		
		$enabled_google_analytics = isset($settings['enabled_google_analytics'])?$settings['enabled_google_analytics']:false;
		$google_analytics_tracking_id = isset($settings['google_analytics_tracking_id'])?$settings['google_analytics_tracking_id']:'';
		$map_custom_style = $settings['map_custom_style'] ?? '';		

		$enabled_fb_pixel = isset($settings['enabled_fb_pixel'])?$settings['enabled_fb_pixel']:false;		
		$fb_pixel_id = isset($settings['fb_pixel_id'])?$settings['fb_pixel_id']:'';		

		$cs = Yii::app()->getClientScript();			

		if($enabled_google_analytics==1 && !empty($google_analytics_tracking_id)){			
			Yii::app()->clientScript->registerScriptFile('https://www.googletagmanager.com/gtag/js?id='.$google_analytics_tracking_id,CClientScript::POS_HEAD,[
				'async'=>true,				
			]);	
			ScriptUtility::registerScript(array(
				"window.dataLayer = window.dataLayer || [];",
				"function gtag(){dataLayer.push(arguments);}",
				"gtag('js', new Date());",
				"gtag('config', '".$google_analytics_tracking_id."');"
			),'googleAnalytics');								  
		}

		if($enabled_fb_pixel==1 && !empty($fb_pixel_id)){			
			$cs->registerScript(
				'facebookPixels',
				"
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '".$fb_pixel_id."');
fbq('track', 'PageView');
				",
				CClientScript::POS_HEAD
			  );		
		}


		// CHECK IF PWA IS ENABLED AND REDIRECT
		$enabled_pwa = isset(Yii::app()->params['settings']['enabled_auto_pwa_redirect'])?Yii::app()->params['settings']['enabled_auto_pwa_redirect']:'';		
		$pwa_url = isset(Yii::app()->params['settings']['pwa_url'])?Yii::app()->params['settings']['pwa_url']:'';				
		if($enabled_pwa==1 && !empty($pwa_url) && Yii::app()->params['isMobile']==1 ){						
			$this->redirect($pwa_url);
			Yii::app()->end();
		}		

		ScriptUtility::registerScript(array(			
			"var map_style='".CJavaScript::quote($map_custom_style)."';",					
		),'map_style');

		// CHECK MAINTENANCE MODE
		$this->maintenanceMode();
		
		// SEO 
		CSeo::setPage();		

		return true;
	}			  
	
	public function actionIndex()
	{							
						
		AssetsFrontBundle::includeMaps();
		
		ScriptUtility::registerScript(array(			
			"var isGuest='".CJavaScript::quote(Yii::app()->user->isGuest)."';",		
			"var next_url='".CJavaScript::quote(Yii::app()->createAbsoluteUrl('store/restaurants'))."';",
		  ),'is_guest');

	    $local_id = CommonUtility::getCookie(Yii::app()->params->local_id);		
		$setttings = Yii::app()->params['settings'];
		$allow_return_home = isset($setttings['allow_return_home'])?$setttings['allow_return_home']:false;
		$allow_return_home = $allow_return_home==1?true:false;
		$android_download_url = isset($setttings['android_download_url'])?$setttings['android_download_url']:'#';
		$ios_download_url = isset($setttings['ios_download_url'])?$setttings['ios_download_url']:'#';

		$enabled_auto_detect_address = isset($setttings['enabled_auto_detect_address'])?$setttings['enabled_auto_detect_address']:false;		
		$enabled_auto_detect_address = $enabled_auto_detect_address==1?true:false;		

		$home_search_mode = isset($setttings['home_search_mode'])?$setttings['home_search_mode']:'address';		
		$home_search_mode = !empty($home_search_mode)?$home_search_mode:'address';
		$location_searchtype = isset($setttings['location_searchtype'])?$setttings['location_searchtype']:'';						
		$enabled_registration = isset($setttings['merchant_enabled_registration'])? ($setttings['merchant_enabled_registration']==1?true:false) :false;

		$country_id = '';
		if($home_search_mode=="location"){
			$country_id = Clocations::getDefaultCountry();
		}		

		$website_title = isset($setttings['website_title'])?$setttings['website_title']:$this->pageTitle;		
		
	    if(!empty($local_id) && $allow_return_home==false){			 
	    	 $this->redirect(array('/store/restaurants'));
	    	 Yii::app()->end();
	    }		

		ScriptUtility::registerScript(array(			
			"var location_searchtype='".CJavaScript::quote($location_searchtype)."';",		
		),'location_searchtype');

		ScriptUtility::registerJS([
			Yii::app()->baseUrl."/assets/js/components-item-dialog.js?time=".time(),
			Yii::app()->baseUrl."/assets/js/components-featured-items.js?time=".time(),
		],CClientScript::POS_HEAD);
				
		$tpl = $home_search_mode=="location"?'location_index':'index';		

		$this->render($tpl,[
			'responsive'=>AttributesTools::FrontCarouselResponsiveSettings('full'),
			'android_download_url'=>$android_download_url,
			'ios_download_url'=>$ios_download_url,
			'enabled_auto_detect_address'=>$enabled_auto_detect_address,
			'home_search_mode'=>$home_search_mode,
			'location_searchtype'=>$location_searchtype,
			'country_id'=>$country_id,
			'website_title'=>$website_title,
			'enabled_registration'=>$enabled_registration
		]);
	}	
	
	public function actionRestaurants()
	{

		ScriptUtility::registerScript(array(
			"var isGuest='".CJavaScript::quote(Yii::app()->user->isGuest)."';",
		),'is_guest');

		// Service-category filter passed from a home banner category link
		// (/store/restaurants?service=<code>). Emits `service_filter` which the
		// feed Vue includes in the getMerchantFeed filters (see assets/js/front.js).
		$service_filter = Yii::app()->input->get('service');
		if(!empty($service_filter)){
			ScriptUtility::registerScript(array(
				"var service_filter='".CJavaScript::quote($service_filter)."';",
			),'service_filter');
		}

		$setttings = Yii::app()->params['settings'];
        $home_search_mode = isset($setttings['home_search_mode'])?$setttings['home_search_mode']:'address';
		$home_search_mode = !empty($home_search_mode)?$home_search_mode:'address';
		$location_searchtype = isset($setttings['location_searchtype'])?$setttings['location_searchtype']:'';
		$enabled_registration = isset($setttings['merchant_enabled_registration'])? ($setttings['merchant_enabled_registration']==1?true:false) :false;
		
		if($home_search_mode=="location"){						
			$this->render("feed-locations",[
				'tabs_suggestion'=>AttributesTools::suggestionTabs(),				
			]);
			Yii::app()->end();
		}

		$cookie_name = Yii::app()->params->local_id;
		$local_id = CommonUtility::getCookie($cookie_name);
		// Fallback: read directly from PHP $_COOKIE in case CommonUtility::getCookie behaves differently
		if (empty($local_id) && isset($_COOKIE[$cookie_name])) {
			$local_id = $_COOKIE[$cookie_name];
		}
		
		// Force log to file immediately
		$log_file = Yii::app()->getRuntimePath() . '/application.log';
		file_put_contents($log_file, date('Y-m-d H:i:s') . " [INFO] StoreController::actionRestaurants - local_id cookie (CommonUtility/getCookie): " . (CommonUtility::getCookie($cookie_name) ?: 'empty') . "\n", FILE_APPEND);
		file_put_contents($log_file, date('Y-m-d H:i:s') . " [INFO] StoreController::actionRestaurants - local_id cookie (raw \$_COOKIE['{$cookie_name}']): " . (isset($_COOKIE[$cookie_name]) ? $_COOKIE[$cookie_name] : 'not set') . "\n", FILE_APPEND);
		file_put_contents($log_file, date('Y-m-d H:i:s') . " [INFO] StoreController::actionRestaurants - local_id resolved value: " . ($local_id ?: 'empty') . "\n", FILE_APPEND);
		
		if(empty($local_id)){
			 $this->redirect(array('/'));
			 Yii::app()->end();
		}
		

		MapSdk::$map_provider = Yii::app()->params['settings']['map_provider'];		   
		MapSdk::setKeys(array(
			'google.maps'=>Yii::app()->params['settings']['google_geo_api_key'],
			'mapbox'=>Yii::app()->params['settings']['mapbox_access_token'],
		));
		
		$place_details = [];
		try {
			$place_details = MapSdk::placeDetails($local_id);
		} catch (Exception $e) {
			Yii::log("StoreController::actionRestaurants - MapSdk::placeDetails failed for local_id: " . $local_id . " with error: " . $e->getMessage(), 'error', 'application.store');
		}
		
		AssetsFrontBundle::includeMaps();
		
		$this->render('feed',array(
			'responsive'=>AttributesTools::FrontCarouselResponsiveSettings('full'), 
			'tabs_suggestion'=>AttributesTools::suggestionTabs(),
			'place_details'=>$place_details,
			'enabled_registration'=>$enabled_registration
		));
	}
	
	public function actionFeed()
	{
		$this->render('feed');
	}
	
	public function actionmenu()
	{
		$this->render('menu');
		//$this->render('menu-1');
	}
	
	public function actioncheckout()
	{
		$this->render('checkout');
		//$this->render('checkout-1');
	}	
	
	public function actionreceipt()
	{
		$this->render('receipt');
	}	
		
	public function actionoffers()
	{
		$this->render('offers');
	}	
	
	public function actionpagenotfound()
	{
		$this->render('404-page');
	}	
	
	public function actionregister()
	{
		$this->render('register');
	}

	public function actioncuisine()
	{
		$pathInfo = Yii::app()->request->getPathInfo();		
		$matches = explode('/', $pathInfo);					
		if(is_array($matches) && count($matches)>=1){			
			$slug = isset($matches[1])?$matches[1]:'';             			
			$dependency = CCacheData::dependency();
			$model = AR_cuisine::model()->cache(Yii::app()->params->cache, $dependency)->find('slug=:slug AND status=:status',[
				':slug'=>trim($slug), ':status'=>'publish'
			]); 
			if($model){				
				$cuisine_filter = json_encode(array($model->cuisine_id));
				ScriptUtility::registerScript(array(
					"var cuisine_filter='".CJavaScript::quote($cuisine_filter)."';",					  
				),'cuisine_filter');	
				
				$setttings = Yii::app()->params['settings'];
				$home_search_mode = isset($setttings['home_search_mode'])?$setttings['home_search_mode']:'address';		
		        $home_search_mode = !empty($home_search_mode)?$home_search_mode:'address';
								
				$tpl = $home_search_mode=="location"?'location-restaurant-list':'feed_cuisine';	

				$cuisine_data = CCuisine::getByID($model->cuisine_id,Yii::app()->language);				
				$title = $cuisine_data? $cuisine_data['cuisine_name'] : t($model->cuisine_name);
								
				$this->render($tpl,array(
					'model'=>$model,					
					'title'=>$title
				));
			} else $this->render("//store/404-page");
		} else $this->render("//store/404-page");		
	}
	
	public function actioncontactus()
	{
		
		$options = array('enabled_contact_form','contact_email_receiver','contact_field','contact_content','contact_enabled_captcha','captcha_site_key','captcha_lang');		

		$data = OptionsTools::find($options);
		$enabled_contact_form = isset($data['enabled_contact_form'])?$data['enabled_contact_form']:false;
		$enabled_contact_form = $enabled_contact_form==1?true:false;
		$contact_content = isset($data['contact_content'])?$data['contact_content']:'';
		$contact_email_receiver = isset($data['contact_email_receiver'])?$data['contact_email_receiver']:'';
		$contact_field = isset($data['contact_field'])?$data['contact_field']:false;
		$contact_field = !empty($contact_field)?json_decode($contact_field,true):false;				
		$contact_enabled_captcha = isset($data['contact_enabled_captcha'])?$data['contact_enabled_captcha']:false;
		$contact_enabled_captcha = $contact_enabled_captcha==1?true:false;
		$captcha_site_key = isset($data['captcha_site_key'])?$data['captcha_site_key']:'';		
		$captcha_lang = isset($data['captcha_lang'])?$data['captcha_lang']:'en'; 

		if(!$enabled_contact_form){
			$this->render("404-page");
			Yii::app()->end();
		}
		
		$model = new AR_contact();

		if(isset($_POST['AR_contact'])){			
			$model->attributes=$_POST['AR_contact'];			
			$model->capcha = $contact_enabled_captcha;
			$model->recaptcha_response = isset($_POST['recaptcha_response'])?$_POST['recaptcha_response']:'';			
			if($model->validate()){						
				$model->receiver_email_address = $contact_email_receiver;
				if($model->save()){
					Yii::app()->user->setFlash('success',t("Your request has been sent."));
					$this->refresh();
				} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString( $model->getErrors(),"<br/>" ));
			} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString( $model->getErrors(),"<br/>" ));
		}

		$this->render('contact_us',[
			'model'=>$model,
			'contact_field'=>$contact_field,
			'contact_content'=>$contact_content,
			'responsive'=>AttributesTools::FrontCarouselResponsiveSettings('full'), 
			'enabled_captcha'=>$contact_enabled_captcha,
			'captcha_site_key'=>$captcha_site_key,
			'captcha_lang'=>$captcha_lang
		]);
	}

	public function actionsearch()
	{
		$this->render("search");
	}

	
	public function actionfeatured()
	{
		$featured_id = Yii::app()->input->get("id");
		$featured_list = AttributesTools::MerchantFeatured();

		$setttings = Yii::app()->params['settings'];
        $home_search_mode = isset($setttings['home_search_mode'])?$setttings['home_search_mode']:'address';		
		$home_search_mode = !empty($home_search_mode)?$home_search_mode:'address';
		
		
		$title = isset($featured_list[$featured_id])?$featured_list[$featured_id]:t("Featured Restaurant");		

		if($home_search_mode=="location"){	
			ScriptUtility::registerScript(array(
				"var featured_id='".CJavaScript::quote($featured_id)."';",					  
			),'featured_id');	
			$this->render("location-restaurant-list",[
				'title'=>$title
			]);
	    } else {
			$featured_id =  Yii::app()->request->getQuery('id', null); 
			ScriptUtility::registerScript(array(
				"var featured_id='".CJavaScript::quote($featured_id)."';",					  
			),'featured_id');	

			ScriptUtility::registerJS([				
				Yii::app()->baseUrl."/assets/js/components-featured.js?time=".time(),
		    ],CClientScript::POS_HEAD);

			$this->render("featured-list",[
				'featured_id'=>$featured_id,
				'title'=>$title
			]);
	   }
	}

	public function actionclearcache()
	{
		Yii::app()->cache->flush();
	}
		
}
/*end class*/