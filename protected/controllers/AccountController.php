<?php
class AccountController extends SiteCommon
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

		// CHECK MAINTENANCE MODE
		$maintenance_mode = isset(Yii::app()->params['settings']['maintenance_mode'])?Yii::app()->params['settings']['maintenance_mode']:false;
		$maintenance_mode = $maintenance_mode==1?true:false;
		if($maintenance_mode){
			$this->renderPartial("//store/maintenance");
		    Yii::app()->end();
		}

		$settings = Yii::app()->params['settings'];		
		$map_custom_style = $settings['map_custom_style'] ?? '';		

		ScriptUtility::registerScript(array(			
			"var map_style='".CJavaScript::quote($map_custom_style)."';",					
		),'map_style');

		return true;
	}
	
	public function actionIndex()
	{
	   if(Yii::app()->user->isGuest){
	   	  $this->redirect(Yii::app()->getBaseUrl(true));		
	   } else $this->redirect(Yii::app()->createUrl("/account/profile"));		
	}
	
	public function actionlogin()
	{				
		$redirect_to = isset($_GET['redirect'])?$_GET['redirect']:'';		
		if(!Yii::app()->user->isGuest){			
			$this->redirect(!empty($redirect_to)?$redirect_to:Yii::app()->getBaseUrl(true)  );		
		}

		$options = OptionsTools::find(array('signup_enabled_capcha','signup_enabled_capcha','captcha_site_key',
		 'fb_flag','google_login_enabled','fb_app_id','google_client_id','signup_enabled_verification','captcha_lang','enabled_guest',
		 'login_method','signup_resend_counter'
		));
		$capcha = isset($options['signup_enabled_capcha'])?$options['signup_enabled_capcha']:'';
        $capcha = $capcha==1?true:false;                
        $captcha_site_key = isset($options['captcha_site_key'])?$options['captcha_site_key']:'';	
		$captcha_lang = isset($options['captcha_lang'])?$options['captcha_lang']:'en'; 

        $fb_enabled = isset($options['fb_flag'])?$options['fb_flag']:'';
        $google_enabled = isset($options['google_login_enabled'])?$options['google_login_enabled']:'';
        $fb_app_id = isset($options['fb_app_id'])?$options['fb_app_id']:'';
        $google_client_id = isset($options['google_client_id'])?$options['google_client_id']:''; 
        
        $enabled_verification = isset($options['signup_enabled_verification'])?$options['signup_enabled_verification']:''; 
        $enabled_verification = $enabled_verification==1?true:false;
				
        $enabled_guest = isset($options['enabled_guest'])?$options['enabled_guest']:false; 
        $enabled_guest = $enabled_guest==1?true:false;

		$login_method = isset($options['login_method'])?$options['login_method']:'user'; 
		$login_method = !empty($login_method)?$login_method:'user';		

		$phone_default_country= isset(Yii::app()->params['settings']['mobilephone_settings_default_country'])?Yii::app()->params['settings']['mobilephone_settings_default_country']:'us';			
		$phone_country_list = isset(Yii::app()->params['settings']['mobilephone_settings_country'])?Yii::app()->params['settings']['mobilephone_settings_country']:'';			
		$phone_country_list = !empty($phone_country_list)?json_decode($phone_country_list,true):array();        
		$resend_counter = isset($options['signup_resend_counter'])?intval($options['signup_resend_counter']):40;
        
		ScriptUtility::registerScript(array(
			  "var redirect_to='".CJavaScript::quote($redirect_to)."';",	
			  "var _resend_counter='".CJavaScript::quote($resend_counter)."';",						  
			),'redirect_to');	
			        			
		$this->render('login',array(
		   'redirect_to'=>$redirect_to,
		   'capcha'=>$capcha,
		   'captcha_site_key'=>$captcha_site_key,
		   'fb_enabled'=>$fb_enabled,
		   'google_enabled'=>$google_enabled,
		   'fb_app_id'=>$fb_app_id,
		   'google_client_id'=>$google_client_id,	
		   'enabled_verification'=>$enabled_verification,
		   'captcha_lang'=>$captcha_lang,
		   'enabled_guest'=>$enabled_guest,
		   'guestcheckout_url'=>Yii::app()->createUrl('/account/guest'),
		   'login_method'=>$login_method,
		   'phone_country_list'=>$phone_country_list,
		   'phone_default_country'=>$phone_default_country,
		));
	}	
	
	public function actionlogout()
	{		
		Yii::app()->user->logout(false);		
		$this->redirect(Yii::app()->user->loginUrl);		
	}
		
	public function actionforgot_pass()
	{		
		$redirect_to = isset($_GET['redirect'])?$_GET['redirect']:'';

		$password_reset_options = isset(Yii::app()->params['settings']['password_reset_options'])?Yii::app()->params['settings']['password_reset_options']:'email';					
		$phone_default_country= isset(Yii::app()->params['settings']['mobilephone_settings_default_country'])?Yii::app()->params['settings']['mobilephone_settings_default_country']:'us';			
		$phone_country_list = isset(Yii::app()->params['settings']['mobilephone_settings_country'])?Yii::app()->params['settings']['mobilephone_settings_country']:'';			
		$phone_country_list = !empty($phone_country_list)?json_decode($phone_country_list,true):array();        
		
		ScriptUtility::registerScript(array(
			"var password_reset_options='".CJavaScript::quote($password_reset_options)."';",					  
		  ),'password_reset_options');	

		$this->render('forgot_pass',array(
		  'redirect_to'=>$redirect_to,
		  'password_reset_options'=>$password_reset_options,
		  'phone_country_list'=>$phone_country_list,
		  'phone_default_country'=>$phone_default_country,
		));
	}	
	
	public function actionreset_password()
	{				
		$client_uuid = isset($_GET['token'])?$_GET['token']:'';		
		$model = AR_clientsignup::model()->find('client_uuid=:client_uuid', 
		array(':client_uuid'=>$client_uuid)); 				
		if($model){
			if($model->status=="active" && $model->reset_password_request==1){
				ScriptUtility::registerScript(array(
					  "var _client_id='".CJavaScript::quote($client_uuid)."';",			  
					),'reset_password');
					
				$this->render('reset_password',array(
				  'first_name'=>Yii::app()->input->xssClean($model->first_name)
				));
			} else $this->render("//store/404-page");
		} else $this->render("//store/404-page");
	}
	
	public function actionsignup()
	{		
		
		$redirect_to = isset($_GET['redirect'])?$_GET['redirect']:'';		
		$next_url = Yii::app()->createAbsoluteUrl("/account/login");
				
		$options = OptionsTools::find(array('signup_type','signup_enabled_capcha','signup_enabled_terms',
		'signup_terms','signup_resend_counter','captcha_site_key','fb_app_id','google_client_id',
		'mobilephone_settings_country','mobilephone_settings_default_country','captcha_lang'
		));
				
        $signup_type = isset($options['signup_type'])?$options['signup_type']:'';
        $capcha = isset($options['signup_enabled_capcha'])?$options['signup_enabled_capcha']:'';
        $capcha = $capcha==1?true:false;                
        $captcha_site_key = isset($options['captcha_site_key'])?$options['captcha_site_key']:'';
		$captcha_lang = isset($options['captcha_lang'])?$options['captcha_lang']:'en'; 
                
        $enabled_terms = isset($options['signup_enabled_terms'])?$options['signup_enabled_terms']:'';
        $signup_terms = isset($options['signup_terms'])?$options['signup_terms']:'';
        $resend_counter = isset($options['signup_resend_counter'])?intval($options['signup_resend_counter']):40;
        if($resend_counter<=0){
        	$resend_counter = 40;
        }
        
        $phone_default_country = isset($options['mobilephone_settings_default_country'])?$options['mobilephone_settings_default_country']:'us';
        $phone_country_list = isset($options['mobilephone_settings_country'])?$options['mobilephone_settings_country']:'';
        $phone_country_list = !empty($phone_country_list)?json_decode($phone_country_list,true):array();        
                
		ScriptUtility::registerScript(array(
			  "var redirect_to='".CJavaScript::quote($redirect_to)."';",
			  "var next_url='".CJavaScript::quote($next_url)."';",				
			  "var _capcha='".CJavaScript::quote($capcha)."';",	
			  "var _resend_counter='".CJavaScript::quote($resend_counter)."';",				  
			),'redirect_to');
									
		$tpl_use = $signup_type=="mobile_phone"?'signup-less':'signup';				
		
		$this->render($tpl_use,array(
		  'redirect_to'=>$redirect_to,			  
		  'capcha'=>$capcha,
		  'captcha_site_key'=>$captcha_site_key,
		  'enabled_terms'=>$enabled_terms,
		  'signup_terms'=>$signup_terms,	
		  'phone_country_list'=>$phone_country_list,
		  'phone_default_country'=>$phone_default_country,
		  'captcha_lang'=>$captcha_lang
		));
	}	
	
	public function actionverify()
	{
		$redirect_to = Yii::app()->input->get('redirect_to');
		$uuid = Yii::app()->input->get('uuid');
				
		$model = AR_clientsignup::model()->find('client_uuid=:client_uuid', 
		array(':client_uuid'=>$uuid)); 
		
		if($model){					
			if($model->account_verified==11 && $model->status=="active"){
				$this->render("//store/404-page");
			} else {				
				
				$options = OptionsTools::find(array('signup_resend_counter'));
				$resend_counter = isset($options['signup_resend_counter'])?intval($options['signup_resend_counter']):40;				
				
				ScriptUtility::registerScript(array(
				  "var _uuid='".CJavaScript::quote($uuid)."';",		
				  "var _redirect_to='".CJavaScript::quote($redirect_to)."';",
				  "var _steps='".CJavaScript::quote(1)."';",	
				  "var _resend_counter='".CJavaScript::quote($resend_counter)."';",		
				),'verify');	
				
				$this->render('account-verify',array(
				  'redirect_to'=>$redirect_to,
				  'uuid'=>$uuid,
				  'email_address'=>$model->email_address,			
				));
			}
		} else $this->render("//store/404-page");
	}
	
	public function actionverification()
	{
		$redirect_to = isset($_GET['redirect_to'])?$_GET['redirect_to']:'';
		$uuid = isset($_GET['uuid'])?$_GET['uuid']:'';
				
		$model = AR_clientsignup::model()->find('client_uuid=:client_uuid', 
		array(':client_uuid'=>$uuid)); 
		
		if($model){
			
			$options = OptionsTools::find(array('signup_resend_counter','mobilephone_settings_country','mobilephone_settings_default_country'));
		    $resend_counter = isset($options['signup_resend_counter'])?intval($options['signup_resend_counter']):40;
			$phone_default_country = isset($options['mobilephone_settings_default_country'])?$options['mobilephone_settings_default_country']:'us';
	        $phone_country_list = isset($options['mobilephone_settings_country'])?$options['mobilephone_settings_country']:'';
	        $phone_country_list = !empty($phone_country_list)?json_decode($phone_country_list,true):array();        
	        
			ScriptUtility::registerScript(array(
			  "var _uuid='".CJavaScript::quote($uuid)."';",		
			  "var _redirect_to='".CJavaScript::quote($redirect_to)."';",
			  "var _steps='".CJavaScript::quote(1)."';",	
			  "var _resend_counter='".CJavaScript::quote($resend_counter)."';",		
			),'verification');	
			
			$this->render('account-verification',array(
			  'redirect_to'=>$redirect_to,
			  'uuid'=>$uuid,
			  'email_address'=>$model->email_address,	
			  'phone_default_country'=>$phone_default_country,
			  'phone_country_list'=>$phone_country_list
			));
		} else $this->render("//store/404-page");
	}
	
	public function actioncomplete_registration()
	{
		$redirect_to = isset($_GET['redirect_to'])?$_GET['redirect_to']:'';
		$uuid = isset($_GET['uuid'])?$_GET['uuid']:'';
				
		$model = AR_clientsignup::model()->find('client_uuid=:client_uuid', 
		array(':client_uuid'=>$uuid)); 
		
		if($model){
			
			ScriptUtility::registerScript(array(
			  "var _uuid='".CJavaScript::quote($uuid)."';",		
			  "var _redirect_to='".CJavaScript::quote($redirect_to)."';",
			  "var _steps='".CJavaScript::quote(2)."';",		
			),'complete_registration');	
			
			$options = OptionsTools::find(array('mobilephone_settings_country','mobilephone_settings_default_country'));						
			$phone_default_country = isset($options['mobilephone_settings_default_country'])?$options['mobilephone_settings_default_country']:'us';
	        $phone_country_list = isset($options['mobilephone_settings_country'])?$options['mobilephone_settings_country']:'';
	        $phone_country_list = !empty($phone_country_list)?json_decode($phone_country_list,true):array();        
	        		
			$this->render('account-verification',array(
			  'redirect_to'=>$redirect_to,
			  'uuid'=>$uuid,
			  'email_address'=>$model->email_address,
			  'phone_country_list'=>$phone_country_list,
		      'phone_default_country'=>$phone_default_country
			));
		} else $this->render("//store/404-page");
	}
	
	public function actioncheckout()
	{
		$this->layout = 'checkout_layout';					
		AssetsFrontBundle::includeMaps();				
		$payments = array(); $payments_credentials = array(); $merchant_id = 0; $merchant_uuid='';
		$loyalty_points_activated = false;
			
		try {

			$cart_uuid = CommonUtility::getCookie("cart_uuid_local");			
			$merchant_id = CCart::getMerchantId($cart_uuid);
			$merchants = CMerchantListingV1::getMerchant( $merchant_id );			
			$merchant_uuid = isset($merchants['merchant_uuid'])?$merchants['merchant_uuid']:'';

			try {
				if($payments = CPayments::PaymentList($merchant_id)){	
					$payments_credentials = CPayments::getPaymentCredentials($merchant_id,'',$merchants->merchant_type);
					CComponentsManager::RegisterBundle($payments);
				}
			} catch (Exception $e) {}		

			$currency_code = isset($_COOKIE['currency_code'])?trim($_COOKIE['currency_code']):'';			
			$base_currency = Price_Formatter::$number_format['currency_code'];            
			$options_merchant = OptionsTools::find(['merchant_timezone','merchant_default_currency'],$merchant_id);						
			$merchant_default_currency = isset($options_merchant['merchant_default_currency'])?$options_merchant['merchant_default_currency']:'';
			$merchant_default_currency = !empty($merchant_default_currency)?$merchant_default_currency:$base_currency;
			$currency_code = !empty($currency_code)?$currency_code: (empty($merchant_default_currency)?$base_currency:$merchant_default_currency) ;
			Price_Formatter::init($currency_code);
			
			if($meta = AR_merchant_meta::getValue($merchant_id,'loyalty_points')){
				$loyalty_points_activated = $meta['meta_value']==1?true:false;
			}					
					
			$payload = array(
			'items','merchant_info','service_fee',
			'delivery_fee','packaging','tax','tips','checkout','discount','distance_local_new',
			'summary','total','items_count','card_fee','points','points_discount'
			);

			$options = OptionsTools::find(array('mobilephone_settings_default_country','mobilephone_settings_country','strict_to_wallet'));
			$phone_default_country = isset($options['mobilephone_settings_default_country'])?$options['mobilephone_settings_default_country']:'us';
			$phone_country_list = isset($options['mobilephone_settings_country'])?$options['mobilephone_settings_country']:'';
			$phone_country_list = !empty($phone_country_list)?json_decode($phone_country_list,true):array();        
			$strict_to_wallet = $options['strict_to_wallet'] ?? false;
			$strict_to_wallet = $strict_to_wallet==1?true:false;			
			
			$shortcode = CCheckout::getPhoneCodeByUserID(Yii::app()->user->id);
			if($shortcode){
				$phone_default_country = $shortcode;
			}

			$points_enabled = isset(Yii::app()->params['settings']['points_enabled'])?Yii::app()->params['settings']['points_enabled']:false;
			$points_use_thresholds = isset(Yii::app()->params['settings']['points_use_thresholds'])? (isset(Yii::app()->params['settings']['points_use_thresholds'])?Yii::app()->params['settings']['points_use_thresholds']:false) :false;
			$points_use_thresholds = $points_use_thresholds==1?true:false;		

			$enabled_include_utensils = isset(Yii::app()->params['settings']['enabled_include_utensils'])? (isset(Yii::app()->params['settings']['enabled_include_utensils'])?Yii::app()->params['settings']['enabled_include_utensils']:false) :false;
			$enabled_include_utensils = $enabled_include_utensils==1?true:false;			

			$setttings = Yii::app()->params['settings'];
			$home_search_mode = isset($setttings['home_search_mode'])?$setttings['home_search_mode']:'address';					
			$home_search_mode = !empty($home_search_mode)?$home_search_mode:'address';			
			$location_searchtype = isset($setttings['location_searchtype'])?$setttings['location_searchtype']:'';
			$enabled_map_selection = isset($setttings['location_enabled_map_selection'])?$setttings['location_enabled_map_selection']:false;					
			$enabled_map_selection = $enabled_map_selection==1?true:false;

			$country_id = '';
			$delivery_option = [];
			$address_label = [];
			$delivery_option_first_value = '';
			$address_label_first_value = '';
			if($home_search_mode=="location"){
				$country_id = Clocations::getDefaultCountry();
				$delivery_option = CCheckout::deliveryOption();
				$address_label = CCheckout::addressLabel();			
				$delivery_option_first_value = reset($delivery_option);			
			    $address_label_first_value = reset($address_label);						
			}			

			ScriptUtility::registerScript(array(
				"var is_checkout='".CJavaScript::quote(1)."';",
				"var payload='".CJavaScript::quote(json_encode($payload))."';",			  
				"var merchant_id='".CJavaScript::quote($merchant_id)."';",
				"var merchant_uuid='".CJavaScript::quote($merchant_uuid)."';",		
				"var use_thresholds='".CJavaScript::quote($points_use_thresholds)."';",		
			),'is_checkout');
							
			$this->render("checkout",array(
			'payments'=>$payments,
			'payments_credentials'=>$payments_credentials,
			'merchant_id'=>$merchant_id,
			'phone_country_list'=>$phone_country_list,
			'phone_default_country'=>$phone_default_country,  
			'points_enabled'=>$points_enabled,
			'points_use_thresholds'=>$points_use_thresholds,
			'loyalty_points_activated'=>$loyalty_points_activated,
			'enabled_include_utensils'=>$enabled_include_utensils,
			// location parameters
			'home_search_mode'=>$home_search_mode,
			'location_searchtype'=>$location_searchtype,
			'country_id'=>$country_id,
			'delivery_option'=>CommonUtility::ArrayToLabelValue($delivery_option),
			'address_label'=>CommonUtility::ArrayToLabelValue($address_label),
			'delivery_option_first_value'=>$delivery_option_first_value,
			'address_label_first_value'=>$address_label_first_value,
			'enabled_map_selection'=>$enabled_map_selection,
			'strict_to_wallet'=>$strict_to_wallet
			));
		} catch (Exception $e) {			
			$this->render("//store/404-page");
		}		
	}

	public function actioncheckout_qr()
	{
		$this->layout = 'checkout_layout';					
		AssetsFrontBundle::includeMaps();				
		$payments = array(); $payments_credentials = array(); $merchant_id = 0; $merchant_uuid='';
		$loyalty_points_activated = false;
		$table_uuid = Yii::app()->request->getQuery('table_uuid');
			
		try {

			$cart_uuid = CommonUtility::getCookie("cart_uuid_local");			
			$merchant_id = CCart::getMerchantId($cart_uuid);
			$merchants = CMerchantListingV1::getMerchant( $merchant_id );			
			$merchant_uuid = isset($merchants['merchant_uuid'])?$merchants['merchant_uuid']:'';

			try {
				if($payments = CPayments::PaymentList($merchant_id)){	
					$payments_credentials = CPayments::getPaymentCredentials($merchant_id,'',$merchants->merchant_type);
					CComponentsManager::RegisterBundle($payments);
				}
			} catch (Exception $e) {}		

			$currency_code = isset($_COOKIE['currency_code'])?trim($_COOKIE['currency_code']):'';			
			$base_currency = Price_Formatter::$number_format['currency_code'];            
			$options_merchant = OptionsTools::find(['merchant_timezone','merchant_default_currency'],$merchant_id);						
			$merchant_default_currency = isset($options_merchant['merchant_default_currency'])?$options_merchant['merchant_default_currency']:'';
			$merchant_default_currency = !empty($merchant_default_currency)?$merchant_default_currency:$base_currency;
			$currency_code = !empty($currency_code)?$currency_code: (empty($merchant_default_currency)?$base_currency:$merchant_default_currency) ;
			Price_Formatter::init($currency_code);
			
			if($meta = AR_merchant_meta::getValue($merchant_id,'loyalty_points')){
				$loyalty_points_activated = $meta['meta_value']==1?true:false;
			}					
					
			$payload = array(
			'items','merchant_info','service_fee',
			'delivery_fee','packaging','tax','tips','checkout','discount','distance_local',
			'summary','total','items_count','card_fee','points','points_discount'
			);							    	   	    	  		

			$options = OptionsTools::find(array('mobilephone_settings_default_country','mobilephone_settings_country'));
			$phone_default_country = isset($options['mobilephone_settings_default_country'])?$options['mobilephone_settings_default_country']:'us';
			$phone_country_list = isset($options['mobilephone_settings_country'])?$options['mobilephone_settings_country']:'';
			$phone_country_list = !empty($phone_country_list)?json_decode($phone_country_list,true):array();        
			
			$shortcode = CCheckout::getPhoneCodeByUserID(Yii::app()->user->id);
			if($shortcode){
				$phone_default_country = $shortcode;
			}

			$points_enabled = isset(Yii::app()->params['settings']['points_enabled'])?Yii::app()->params['settings']['points_enabled']:false;
			$points_use_thresholds = isset(Yii::app()->params['settings']['points_use_thresholds'])? (isset(Yii::app()->params['settings']['points_use_thresholds'])?Yii::app()->params['settings']['points_use_thresholds']:false) :false;
			$points_use_thresholds = $points_use_thresholds==1?true:false;		

			$enabled_include_utensils = isset(Yii::app()->params['settings']['enabled_include_utensils'])? (isset(Yii::app()->params['settings']['enabled_include_utensils'])?Yii::app()->params['settings']['enabled_include_utensils']:false) :false;
			$enabled_include_utensils = $enabled_include_utensils==1?true:false;			

			$setttings = Yii::app()->params['settings'];
			$home_search_mode = isset($setttings['home_search_mode'])?$setttings['home_search_mode']:'address';					
			$home_search_mode = !empty($home_search_mode)?$home_search_mode:'address';			
			$location_searchtype = isset($setttings['location_searchtype'])?$setttings['location_searchtype']:'';
			$enabled_map_selection = isset($setttings['location_enabled_map_selection'])?$setttings['location_enabled_map_selection']:false;					
			$enabled_map_selection = $enabled_map_selection==1?true:false;

			$country_id = '';
			$delivery_option = [];
			$address_label = [];
			$delivery_option_first_value = '';
			$address_label_first_value = '';
			if($home_search_mode=="location"){
				$country_id = Clocations::getDefaultCountry();
				$delivery_option = CCheckout::deliveryOption();
				$address_label = CCheckout::addressLabel();			
				$delivery_option_first_value = reset($delivery_option);			
			    $address_label_first_value = reset($address_label);						
			}			

			ScriptUtility::registerScript(array(
				"var is_checkout='".CJavaScript::quote(1)."';",
				"var payload='".CJavaScript::quote(json_encode($payload))."';",			  
				"var merchant_id='".CJavaScript::quote($merchant_id)."';",
				"var merchant_uuid='".CJavaScript::quote($merchant_uuid)."';",		
				"var use_thresholds='".CJavaScript::quote($points_use_thresholds)."';",	
				"var table_uuid='".CJavaScript::quote($table_uuid)."';",			
			),'is_checkout');
				
			$this->render("checkout_qr",array(
			'payments'=>$payments,
			'payments_credentials'=>$payments_credentials,
			'merchant_id'=>$merchant_id,
			'phone_country_list'=>$phone_country_list,
			'phone_default_country'=>$phone_default_country,  
			'points_enabled'=>$points_enabled,
			'points_use_thresholds'=>$points_use_thresholds,
			'loyalty_points_activated'=>$loyalty_points_activated,
			'enabled_include_utensils'=>$enabled_include_utensils,
			// location parameters
			'home_search_mode'=>$home_search_mode,
			'location_searchtype'=>$location_searchtype,
			'country_id'=>$country_id,
			'delivery_option'=>CommonUtility::ArrayToLabelValue($delivery_option),
			'address_label'=>CommonUtility::ArrayToLabelValue($address_label),
			'delivery_option_first_value'=>$delivery_option_first_value,
			'address_label_first_value'=>$address_label_first_value,
			'enabled_map_selection'=>$enabled_map_selection
			));
		} catch (Exception $e) {			
			$this->render("//store/404-page");
		}		
	}
	
	public function actionprofile()
	{		
		$this->addBodyClasses("column2-layout");
		$this->layout = 'column2';
		
		$message='';
		$model = AR_client::model()->findByPk( Yii::app()->user->id );	
		if($model){
		   $message = t("We sent a code to {{email_address}}.",array(
             '{{email_address}}'=> CommonUtility::maskEmail($model->email_address)
           ));           		    			          
		} else {
			Yii::app()->user->logout(false);		
		    $this->redirect(Yii::app()->user->loginUrl);		
		}
	
		$avatar = Yii::app()->user->avatar;

		ScriptUtility::registerScript(array(			   
		   "var _message='".CJavaScript::quote($message)."';",
		   "var profileAvatar='".CJavaScript::quote($avatar)."';",
		),'manage_account');		           
			
		
		$options = OptionsTools::find(array('mobilephone_settings_country','mobilephone_settings_default_country'));
		
		$phone_default_country = isset($options['mobilephone_settings_default_country'])?$options['mobilephone_settings_default_country']:'us';
        $phone_country_list = isset($options['mobilephone_settings_country'])?$options['mobilephone_settings_country']:'';
        $phone_country_list = !empty($phone_country_list)?json_decode($phone_country_list,true):array();        

		$shortcode = CCheckout::getPhoneCodeByUserID(Yii::app()->user->id);
		if($shortcode){
			$phone_default_country = $shortcode;
		}
				
		$this->render('my-profile',array(			  
		  'avatar'=>$avatar,
		  'phone_country_list'=>$phone_country_list,
		  'phone_default_country'=>$phone_default_country,
		  'model'=>$model,
		  'menu'=>WidgetUserProfile::CustomMenu()
		));
	}
	
	public function actionchange_password()
	{
		$this->addBodyClasses("column2-layout");
		$this->layout = 'column2';
		$avatar = Yii::app()->user->avatar;
		
		$model = AR_client::model()->findByPk( Yii::app()->user->id );	

		$avatar = Yii::app()->user->avatar;
		ScriptUtility::registerScript(array(			   		   
		   "var profileAvatar='".CJavaScript::quote($avatar)."';",
		),'manage_account');		           
		
		$phone_default_country = isset($options['mobilephone_settings_default_country'])?$options['mobilephone_settings_default_country']:'us';
        $phone_country_list = isset($options['mobilephone_settings_country'])?$options['mobilephone_settings_country']:'';
        $phone_country_list = !empty($phone_country_list)?json_decode($phone_country_list,true):array();        

		$this->render('change-password',array(			  
			'avatar'=>$avatar,
			'phone_country_list'=>$phone_country_list,
			'phone_default_country'=>$phone_default_country,
			'model'=>$model,
			'menu'=>WidgetUserProfile::CustomMenu()
		));		
	}
	
	public function actionmanage_account()
	{			
		$this->addBodyClasses("column2-layout");
		$this->layout = 'column2';
		$model = AR_client::model()->findByPk( Yii::app()->user->id );		
		if($model){			

			$avatar = Yii::app()->user->avatar;
			
			$message = t("We sent a code to {{email_address}}.",array(
			             '{{email_address}}'=> CommonUtility::maskEmail($model->email_address)
			           ));
		    
			ScriptUtility::registerScript(array(
			   "var _phone_prefix='".CJavaScript::quote($model->phone_prefix)."';",
			   "var _contact_phone='".CJavaScript::quote($model->contact_phone)."';",
			   "var _message='".CJavaScript::quote($message)."';",
			   "var profileAvatar='".CJavaScript::quote($avatar)."';",
			),'manage_account');	
		
			
			$avatar = Yii::app()->user->avatar;
			$this->render('manage-account',array(			  
			  'avatar'=>$avatar,
			  'model'=>$model,
			  'menu'=>WidgetUserProfile::CustomMenu()	  
			));			
		} else $this->redirect(Yii::app()->createUrl("/account/login"));
	}
	
	public function actionnotifications()
	{
		$this->addBodyClasses("column2-layout");
		$this->layout = 'column2';
		$model = AR_client::model()->findByPk( Yii::app()->user->id );		
		
		if($model){				

			$avatar = Yii::app()->user->avatar;
			ScriptUtility::registerScript(array(			   		   
			"var profileAvatar='".CJavaScript::quote($avatar)."';",
			),'manage_account');		           

			$settings = AR_admin_meta::getMeta(array('webpush_provider','pusher_instance_id','webpush_app_enabled'));			
		    $webpush_provider = isset($settings['webpush_provider'])?$settings['webpush_provider']['meta_value']:'';
		    $pusher_instance_id = isset($settings['pusher_instance_id'])?$settings['pusher_instance_id']['meta_value']:'';
		    $webpush_app_enabled = isset($settings['webpush_app_enabled'])?$settings['webpush_app_enabled']['meta_value']:'';
		    $webpush_app_enabled = $webpush_app_enabled==1?true:false;
		    		    
		    $iterest_list = array();
		    try {
		       $iterest_list = CNotificationData::interestListing('communication_client');
		    } catch (Exception $e) {
		       //echo $e->getMessage();
		    }
		    		    
			$avatar = Yii::app()->user->avatar;
			$this->render('account-notifications',array(			  
			   'avatar'=>$avatar,
			   'model'=>$model,			   
			   'iterest_list'=>(array)$iterest_list,
			   'pusher_instance_id'=>$pusher_instance_id,
			   'webpush_provider'=>$webpush_provider,	
			   'webpush_app_enabled'=>$webpush_app_enabled,
			   'menu'=>WidgetUserProfile::CustomMenu()
			));			
		}
	}
	
    public function actionorders()
	{
		$this->addBodyClasses("column2-layout");
		$this->layout = 'column2';

		$enabled_review = isset(Yii::app()->params['settings']['enabled_review'])?Yii::app()->params['settings']['enabled_review']:'';
		$enabled_review = $enabled_review==1?true:false;

		$cancel_order_enabled = isset(Yii::app()->params['settings']['cancel_order_enabled'])?Yii::app()->params['settings']['cancel_order_enabled']:'';
		$cancel_order_enabled = $cancel_order_enabled==1?true:false;

		$this->render('my-orders',[
			'enabled_review'=>$enabled_review,
			'cancel_order_enabled'=>$cancel_order_enabled
		]);
	}	
	
	public function actionfavourites()
	{
		$this->addBodyClasses("column2-layout");
		$this->layout = 'column2';
		$this->render('my-favourites');
	}
	
	public function actionaddresses()
	{
		AssetsFrontBundle::includeMaps();
		
		$setttings = Yii::app()->params['settings'];
		$home_search_mode = isset($setttings['home_search_mode'])?$setttings['home_search_mode']:'address';		
		$home_search_mode = !empty($home_search_mode)?$home_search_mode:'address';
		$enabled_map_selection = isset($setttings['location_enabled_map_selection'])?$setttings['location_enabled_map_selection']:false;					
		$enabled_map_selection = $enabled_map_selection==1?true:false;
		
		$this->addBodyClasses("column2-layout");
		$this->layout = 'column2';
		if($home_search_mode=="location"){
			$country_id = Clocations::getDefaultCountry();
			$setttings = Yii::app()->params['settings'];
			$location_searchtype = isset($setttings['location_searchtype'])?$setttings['location_searchtype']:'';

			$delivery_option = CCheckout::deliveryOption();
			$address_label = CCheckout::addressLabel();			
			$delivery_option_first_value = reset($delivery_option);			
			$address_label_first_value = reset($address_label);						
			$delivery_option_first_value = 'Hand it to me';

			$this->render('location-addresses',[
				'country_id'=>$country_id,
				'location_searchtype'=>$location_searchtype,
				'delivery_option'=>CommonUtility::ArrayToLabelValue($delivery_option),
				'address_label'=>CommonUtility::ArrayToLabelValue($address_label),
				'delivery_option_first_value'=>$delivery_option_first_value,
				'address_label_first_value'=>$address_label_first_value,
				'enabled_map_selection'=>$enabled_map_selection
			]);
		} else $this->render('my-addresses');		
	}
	
	public function actionbooking()
	{
		$this->addBodyClasses("column2-layout");
		$this->layout = 'column2';
		
		CBooking::setIdentityToken();

		$status_list = AttributesTools::bookingStatus();
		$status_list = array_merge([
			'all'=>t("All")
		], $status_list);		
				
		try {				
			$summary = CBooking::customerSummary( Yii::app()->user->id,0,date("Y-m-d"));
		} catch (Exception $e) {
			$summary  = [];
		}		

		$this->render('my-booking',[
			'status_list'=>(array)$status_list,
			'summary'=>$summary
		]);
	}
	
	public function actionpayments()
	{
		try {
			$payments = CPayments::DefaultPaymentList();			
			CComponentsManager::RegisterBundle($payments);
		} catch (Exception $e) {
		    //echo $e->getMessage();
		}	
		
		try {
			$payments_credentials = CPayments::getPaymentCredentials(0,'',2);
		} catch (Exception $e) {
		    //echo $e->getMessage();
		}	
						
		$this->addBodyClasses("column2-layout");
		$this->layout = 'column2';
		$this->render('my-payments',array(
		  'payments'=>$payments,
		  'payments_credentials'=>$payments_credentials,
		));
	}
	
	public function actionpoints()
	{
		$points_enabled = isset(Yii::app()->params['settings']['points_enabled'])?Yii::app()->params['settings']['points_enabled']:false;
		$this->addBodyClasses("column2-layout");
		$this->layout = 'column2';
		if($points_enabled){
			$this->render('my-points');
		} else $this->render('//store/404-page');
	}
	
	public function actionvouchers()
	{
		$this->addBodyClasses("column2-layout");
		$this->layout = 'column2';
		$this->render('my-vouchers');
	}
	
	public function actioncuisine()
	{
		$this->render('cuisine');
	}
	
	public function actionnotificationslist()
	{		
		
		AssetsFrontBundle::includeMaps();
		$this->addBodyClasses("column2-layout");
		$this->layout = 'column2';
		$this->render('notifications_list');
	}

	public function actionrider_reset_password()
	{
		$driver_uuid = isset($_GET['token'])?$_GET['token']:'';		
		$model = AR_driver::model()->find('driver_uuid=:driver_uuid', 
		array(':driver_uuid'=>$driver_uuid)); 						
		if($model){			
			if($model->status=="active" && $model->reset_password_request==1){				
				$model->scenario = "reset_password";
				if(isset($_POST['AR_driver'])){
					$model->attributes=$_POST['AR_driver'];
					if($model->validate()){						
						if($model->save()){
							Yii::app()->user->setFlash('success',t("Password successfully reset"));							
							$this->refresh();
						} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString( $model->getErrors(),"<br/>" ));
					} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString( $model->getErrors(),"<br/>" ));
				} 

				$this->render('rider_reset_password',array(
				  'model'=>$model
				));
			} else $this->render("//store/404-page");
		} else $this->render("//store/404-page");
	}

	public function actionguest()
	{

		$redirect_to = isset($_GET['redirect'])?$_GET['redirect']:'';		
		$next_url = Yii::app()->createAbsoluteUrl("/account/login");

		$options = OptionsTools::find(array('signup_type','signup_enabled_capcha','signup_enabled_terms',
		'signup_terms','signup_resend_counter','captcha_site_key','fb_app_id','google_client_id',
		'mobilephone_settings_country','mobilephone_settings_default_country','captcha_lang'
		));
				
        $signup_type = isset($options['signup_type'])?$options['signup_type']:'';
        $capcha = isset($options['signup_enabled_capcha'])?$options['signup_enabled_capcha']:'';
        $capcha = $capcha==1?true:false;                
        $captcha_site_key = isset($options['captcha_site_key'])?$options['captcha_site_key']:'';
		$captcha_lang = isset($options['captcha_lang'])?$options['captcha_lang']:'en'; 
                
        $enabled_terms = isset($options['signup_enabled_terms'])?$options['signup_enabled_terms']:'';
        $signup_terms = isset($options['signup_terms'])?$options['signup_terms']:'';
        $resend_counter = isset($options['signup_resend_counter'])?intval($options['signup_resend_counter']):40;
        if($resend_counter<=0){
        	$resend_counter = 40;
        }
        
        $phone_default_country = isset($options['mobilephone_settings_default_country'])?$options['mobilephone_settings_default_country']:'us';
        $phone_country_list = isset($options['mobilephone_settings_country'])?$options['mobilephone_settings_country']:'';
        $phone_country_list = !empty($phone_country_list)?json_decode($phone_country_list,true):array();        
                
		ScriptUtility::registerScript(array(
			  "var redirect_to='".CJavaScript::quote($redirect_to)."';",
			  "var next_url='".CJavaScript::quote($next_url)."';",				
			  "var _capcha='".CJavaScript::quote($capcha)."';",	
			  "var _resend_counter='".CJavaScript::quote($resend_counter)."';",				  
		),'redirect_to');

		$this->render("guest_information",array(
			'redirect_to'=>$redirect_to,			  
			'capcha'=>$capcha,
			'captcha_site_key'=>$captcha_site_key,
			'enabled_terms'=>$enabled_terms,
			'signup_terms'=>$signup_terms,	
			'phone_country_list'=>$phone_country_list,
			'phone_default_country'=>$phone_default_country,
			'captcha_lang'=>$captcha_lang
		));		
	}

	public function actioncheckout_details()
	{
		$this->pageTitle = t("Delivery details");
		AssetsFrontBundle::includeMaps();			
		$maps_config = CMaps::config();				
		$local_id = CommonUtility::getCookie(Yii::app()->params->local_id);		
		
		$redirect_to = Yii::app()->createAbsoluteUrl("/account/checkout");
		$merchant_id = 0; $cart_uuid ='';

		try {
			$cart_uuid = CommonUtility::getCookie("cart_uuid_local");			
			$merchant_id = CCart::getMerchantId($cart_uuid);			
		} catch (Exception $e) {
		    //
		}	

		try {
			$address_needed = CCheckout::addressIsNeeded($merchant_id,$local_id,$cart_uuid);
			if(!$address_needed){
				$this->redirect($redirect_to);
				Yii::app()->end();
			}
		} catch (Exception $e) {
			//
		}
				
		ScriptUtility::registerScript(array(
			"var redirect_to='".CJavaScript::quote($redirect_to)."';",					  
			"var merchant_id='".CJavaScript::quote($merchant_id)."';",
		),'redirect_to');	
		
		$this->render("checkout_details",[
			'maps_config'=>$maps_config,
			'local_id'=>$local_id,
			'redirect_to'=>$redirect_to
		]);
	}

	public function actionwallet()
	{
		
		$this->pageTitle = t("Digital Wallet");

		$enabled = isset(Yii::app()->params['settings']['digitalwallet_enabled'])?Yii::app()->params['settings']['digitalwallet_enabled']:false;		
		$this->addBodyClasses("column2-layout");
		$this->layout = 'column2';
		if($enabled){

			$payments = []; $payments_credentials = [];
			try {
				$payments = CPayments::DefaultPaymentList();				
				CComponentsManager::RegisterBundle($payments);
			} catch (Exception $e) {				
			}	

			try {
				$payments_credentials = CPayments::getPaymentCredentials(0,'',2);
			} catch (Exception $e) {				
			}	

			$bonus_count = 0;
			try {
				$transaction_type = CDigitalWallet::transactionName();
				$bonus_count = AttributesTools::getDiscountCount($transaction_type,date("Y-m-d"));
			} catch (Exception $e) {								
			}	
			
			$enabled_topup = isset(Yii::app()->params['settings']['digitalwallet_enabled_topup'])?Yii::app()->params['settings']['digitalwallet_enabled_topup']:false;
			$enabled_topup = $enabled_topup==1?true:false;
			$topup_minimum = isset(Yii::app()->params['settings']['digitalwallet_topup_minimum'])?floatval(Yii::app()->params['settings']['digitalwallet_topup_minimum']):1;
			$topup_maximum = isset(Yii::app()->params['settings']['digitalwallet_topup_maximum'])?floatval(Yii::app()->params['settings']['digitalwallet_topup_maximum']):10000;			

			$this->render('my-wallet',[
				'payments'=>$payments,
		        'payments_credentials'=>$payments_credentials,
				'enabled_topup'=>$enabled_topup,
				'topup_minimum'=>$topup_minimum,
				'topup_maximum'=>$topup_maximum,
				'bonus_count'=>$bonus_count
			]);
		} else $this->render('//store/404-page');
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
		Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl."/assets/js/chat-front.js?time=".time(),CClientScript::POS_END,[
			'type'=>"module"
		]);
	}

	public function actionlivechat()
	{
		$this->addBodyClasses("column2-layout");
		$this->layout = 'column2-full';

		$chat_enabled = isset(Yii::app()->params['settings'])? (isset(Yii::app()->params['settings']['chat_enabled'])?Yii::app()->params['settings']['chat_enabled']:false) :false;		
		$chat_enabled = $chat_enabled==1?true:false;
		if(!$chat_enabled){
			$this->render("//store/404-page");
			return false;
		}

		$order_uuid = Yii::app()->input->get("order_uuid");
		if(!empty($order_uuid)){
			$chat_url = Yii::app()->createAbsoluteUrl("/account/framechat",[
				'order_uuid'=>$order_uuid
			]);
		} else $chat_url = Yii::app()->createAbsoluteUrl("/account/framechat");
		
		$this->render("chats-frame",[      
			'chat_url'=>$chat_url
        ]);
	}

	public function actionframechat()
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

		$ajax_url = Yii::app()->createAbsoluteUrl("/chatapi");				
		$main_user_uuid = Yii::app()->user->client_uuid;	
		
		$merchant_uuid = ''; $order_id = ''; $to_info = [];
		$order_uuid = Yii::app()->input->get('order_uuid');		
		if(!empty($order_uuid)){
			try {
				$order = COrders::get($order_uuid);				
				$order_id = $order->order_id;
				$merchant = CMerchants::get($order->merchant_id);				
				$to_info = [
					'client_uuid'=>$merchant->merchant_uuid,
					'photo'=>CMedia::getImage($merchant->logo,$merchant->path,'',CommonUtility::getPlaceholderPhoto('merchant_logo')),
					'first_name'=>$merchant->restaurant_name,
					'last_name'=>'',
					'user_type'=>'merchant'
				];				
				$merchant_uuid = $merchant->merchant_uuid;				
			} catch (Exception $e) {
				//$e->getMessage();
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
		
		ScriptUtility::registerScript(array(
			"var chat_api='".CJavaScript::quote($ajax_url)."';",		  
			"var main_user_uuid='".CJavaScript::quote($main_user_uuid)."';",
			"var order_uuid='".CJavaScript::quote($order_uuid)."';",
			"var order_id='".CJavaScript::quote($order_id)."';",
			"var merchant_uuid='".CJavaScript::quote($merchant_uuid)."';",
			"var chat_language='".CJavaScript::quote(Yii::app()->language)."';",	
			"var from_data='".CJavaScript::quote(json_encode($from_data))."';",					
			"var toInfo='".CJavaScript::quote(json_encode($to_info))."';",					
		),'chat_api');

		$this->render("chats-front",[
			'ajax_url'=>$ajax_url,
			'main_user_uuid'=>$main_user_uuid,
			'search_type'=>['merchant','admin'],
		]);
	}

	public function actionpayment_pending()
	{
		$this->render("payment_pending");
	}
	
			
}
/*end class*/