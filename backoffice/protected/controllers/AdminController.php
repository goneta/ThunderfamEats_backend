<?php
require 'php-jwt/vendor/autoload.php';
require 'firebase-php/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;
require_once "php-qrcode/vendor/autoload.php";
use chillerlan\QRCode\{QRCode, QROptions};

class AdminController extends CommonController
{
		
	public function beforeAction($action)
	{				
		InlineCSTools::registerStatusCSS();
		InlineCSTools::registerOrder_StatusCSS();
			
		CSeo::setPage();
		return true;
	}
		
	public function actionError()
	{
	    if($error=Yii::app()->errorHandler->error)
	    {
	    	if(Yii::app()->request->isAjaxRequest)
	    		echo $error['message'];
	    	else	    	    
	        	$this->render('error', array(
	        	 'error'=>$error
	        	));
	    }
	}
	
	public function actionlogout()
	{
		Yii::app()->user->logout(false);
		$this->redirect(Yii::app()->homeUrl);
	}
	
	public function actionIndex()
	{	
		$this->redirect(array(Yii::app()->controller->id.'/dashboard'));		
	}	
	
	public function actiondashboard()
	{							
		$this->render('dashboard',array(
		  'orders_tab'=>AttributesTools::dashboardOrdersTab(),
		  'item_tab'=>AttributesTools::dashboardItemTab(),
		  'popular_merchant_tab'=>AttributesTools::dashboardPopularMerchantTab(),
		  'limit'=>5,
		  'months'=>6,		  
		  'ajax_url'=>Yii::app()->createUrl("/api"),		  
		));
	}
		
	public function actionprofile()
	{
		$this->pageTitle = CommonUtility::t("Profile");
		
		$model = AR_AdminUser::model()->findByPk( Yii::app()->user->id );
		if(!$model){
			$this->render("error");
			Yii::app()->end();
		}
		
		$model->scenario='profile_update';
		$upload_path = CMedia::adminFolder();
				
		if(isset($_POST['AR_AdminUser'])){
			$model->attributes=$_POST['AR_AdminUser'];			
			if($model->validate()){				
												
				if(isset($_POST['photo'])){
					if(!empty($_POST['photo'])){
						$model->profile_photo = $_POST['photo'];						
						$model->path = isset($_POST['path'])?$_POST['path']:$upload_path;
						Yii::app()->user->avatar = $_POST['photo'];					
						Yii::app()->user->avatar_path = $model->path;	
					} else {
						$model->profile_photo = '';
						Yii::app()->user->avatar='';
					}
				} else {
					$model->profile_photo = '';			
					Yii::app()->user->avatar='';
				}
				
				if($model->save()){
					Yii::app()->user->setFlash('success',CommonUtility::t("Profile updated"));
					$this->refresh();
				} else {
					Yii::app()->user->setFlash('error',CommonUtility::t(Helper_failed_update));
				}				
			} 
		}
							
		$avatar = CMedia::getImage($model->profile_photo,$model->path,'@thumbnail',
		CommonUtility::getPlaceholderPhoto('customer'));
		
		$settings = AR_admin_meta::getMeta(array('webpush_app_enabled'));			
		$webpush_app_enabled = isset($settings['webpush_app_enabled'])?$settings['webpush_app_enabled']['meta_value']:'';		
		
		WidgetUserMenu::$ctr[0] = Yii::app()->controller->id."/profile";
		WidgetUserMenu::$ctr[1] = Yii::app()->controller->id."/change_password";
		if($webpush_app_enabled){
		   WidgetUserMenu::$ctr[2] = Yii::app()->controller->id."/web_notifications";
		}
		
		$this->render("submenu_tpl",array(
		    'model'=>$model,
			'template_name'=>"profile",
			'widget'=>'WidgetUserMenu',		
			'avatar'=>$avatar,
			'params'=>array(  
			   'model'=>$model,		
			   'upload_path'=>$upload_path,	   
			   'links'=>array(		            
			   ),
			 )
		));
	}
	
	public function actionprofile_remove_image()
	{
	    $id =  (integer)  Yii::app()->user->id;		    
	    $model = AR_AdminUser::model()->findByPk($id);
	    if($model){
			Yii::app()->user->setState("avatar",'');
			$model->profile_photo = '';
			$model->save();
			$this->redirect(array(Yii::app()->controller->id.'/profile'));			
		} else $this->render("error");
	}
	
	public function actionchange_password()
	{
		$this->pageTitle = CommonUtility::t("Change Password");
						
		$model = AR_AdminUser::model()->findByPk( Yii::app()->user->id );
		if(!$model){
			$this->render("error");
			Yii::app()->end();
		}
		
		$model->scenario='update_password';
		
		if(isset($_POST['AR_AdminUser'])){
			$model->attributes=$_POST['AR_AdminUser'];			
			if($model->validate()){				
				
				if(!empty($model->new_password) && !empty($model->new_password)){					
					$model->password = md5(trim($model->new_password));
				}
				
				if($model->save()){
					Yii::app()->user->setFlash('success',t("Password updated"));
					$this->refresh();
				} else {
					Yii::app()->user->setFlash('error',t(Helper_failed_update));
				}				
			}
		}
							
		$avatar = CMedia::getImage($model->profile_photo,$model->path,'@thumbnail',
		CommonUtility::getPlaceholderPhoto('customer'));
		
		$settings = AR_admin_meta::getMeta(array('webpush_app_enabled'));			
		$webpush_app_enabled = isset($settings['webpush_app_enabled'])?$settings['webpush_app_enabled']['meta_value']:'';		
		WidgetUserMenu::$ctr[0] = Yii::app()->controller->id."/profile";
		WidgetUserMenu::$ctr[1] = Yii::app()->controller->id."/change_password";
		if($webpush_app_enabled){
		   WidgetUserMenu::$ctr[2] = Yii::app()->controller->id."/web_notifications";
		}
		
		$this->render("submenu_tpl",array(
		    'model'=>$model,
			'template_name'=>"profile_changepass",
			'widget'=>'WidgetUserMenu',		
			'avatar'=>$avatar,
			'params'=>array(  
			   'model'=>$model,			   
			   'links'=>array(		            
			   ),
			 )
		));
	}	
	
	public function actionweb_notifications()
	{
		$this->pageTitle = CommonUtility::t("Change Password");
		
		$model = AR_AdminUser::model()->findByPk( Yii::app()->user->id );
		if(!$model){
			$this->render("error");
			Yii::app()->end();
		}
		
		$avatar = CMedia::getImage($model->profile_photo,$model->path,'@thumbnail',
		CommonUtility::getPlaceholderPhoto('customer'));
		
		WidgetUserMenu::$ctr[0] = Yii::app()->controller->id."/profile";
		WidgetUserMenu::$ctr[1] = Yii::app()->controller->id."/change_password";		
		
		$settings = AR_admin_meta::getMeta(array('webpush_provider','pusher_instance_id','webpush_app_enabled'));			
		$webpush_provider = isset($settings['webpush_provider'])?$settings['webpush_provider']['meta_value']:'';
		$pusher_instance_id = isset($settings['pusher_instance_id'])?$settings['pusher_instance_id']['meta_value']:'';
		$webpush_app_enabled = isset($settings['webpush_app_enabled'])?$settings['webpush_app_enabled']['meta_value']:'';
		$webpush_app_enabled = $webpush_app_enabled==1?true:false;
		
		if($webpush_app_enabled){
		   WidgetUserMenu::$ctr[2] = Yii::app()->controller->id."/web_notifications";
		}

		if($webpush_app_enabled){
		$this->render("submenu_tpl",array(
		    'model'=>$model,
			'template_name'=>"profile_webpush",
			'widget'=>'WidgetUserMenu',		
			'avatar'=>$avatar,
			'params'=>array(  
			   'model'=>$model,			   
			   'iterest_list'=>AttributesTools::pushInterestList(),
			   'pusher_instance_id'=>$pusher_instance_id,
			   'webpush_provider'=>$webpush_provider,			   
			   'links'=>array(		            
			   ),
			 )
		));
		} else $this->render('//tpl/error',array(  
		  'error'=>array(
		    'message'=>t("Web push is not enabled")
		  )
		));
	}
	
	public function actionmigrate()
	{
		/*$CreateTables = new CreateTables;
		$CreateTables->up();*/
		//MigrateTables::run();
	}	
	
	public function actionallorder()
	{
		$this->render('allorder');
	}
	
	public function actioncancelorders()
	{
		$this->render('allorder');
	}
	
	public function actionsite_information()
	{		
		$this->pageTitle = t("Site configuration");		
		ItemIdentity::instantiateIdentity();
		
		$model=new AR_option;
		$model->scenario='site_config';
		
		$options = array('website_title','website_logo','mobilelogo','admin_country_set',
		'website_address','website_contact_phone','website_contact_email');
		
		$upload_path = CMedia::adminFolder();
		
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
								
				if(isset($_POST['photo'])){
					if(!empty($_POST['photo'])){
						$model->website_logo = $_POST['photo'];
					} else $model->website_logo = '';
				} else $model->website_logo = '';
								
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
		
		$country_list = AttributesTools::CountryList();
					
		$this->render('site_config_tpl',array( 
		  'model'=>$model,
		  'template_name'=>"site_information",
		  'params'=>array(  
		   'model'=>$model,
		   'country_list'=>$country_list,
		   'upload_path'=>$upload_path,
		  )
		));
	}
	
	public function actioncurrency()
	{
		$this->pageTitle = t("Site configuration");
		
		$model=new AR_option;		
		
		$options = array('admin_currency_set','admin_currency_position','admin_decimal_place',
		'admin_thousand_separator','admin_decimal_separator');
				
		if(isset($_POST['AR_option'])){
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
		
		$country_list = AttributesTools::CountryList();
					
		$this->render('site_config_tpl',array( 
		  'model'=>$model,
		  'template_name'=>"site_currency",
		  'params'=>array(  
		   'model'=>$model,	
		   'currency'=>AttributesTools::CurrencyList(),
		   'currency_position'=>AttributesTools::CurrencyPosition(),
		  )
		));
	}
	
	public function actionmap_keys()
	{
		$this->pageTitle = t("Map API Keys");
		
		CommonUtility::setMenuActive('.admin_site_information',".admin_site_information");
		
		$model=new AR_option;
		$model->scenario=Yii::app()->controller->action->id;		
		
		$options = array(
			'map_provider','google_geo_api_key','google_maps_api_key','mapbox_access_token',
			'yandex_javascript_api','yandex_static_api','yandex_distance_api','yandex_geocoder_api','yandex_geosuggest_api','yandex_language',
			'google_maps_api_key_for_mobile','map_custom_style'
	    );
		
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
				$hasError = false;
				if(!empty($model->map_custom_style)){
					$decoded = json_decode($model->map_custom_style, true);
					if (json_last_error() !== JSON_ERROR_NONE) {
						Yii::app()->user->setFlash('error',t("Invalid Google Maps Custom Style"));						
						$hasError = true;
					} else {
						$minified = json_encode($decoded, JSON_UNESCAPED_SLASHES);
						$model->map_custom_style = $minified;
					}
				}				

				if(!$hasError){
					if(OptionsTools::save($options, $model)){
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
						$this->refresh();
					} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
				}				
			}
		}
		
		if($data = OptionsTools::find($options)){
			foreach ($data as $name=>$val) {
				if(DEMO_MODE){
					$model[$name] = CommonUtility::mask(date("Ymjhs"));
				} else $model[$name]=$val;				
			}			
		}		
		
		$this->render('site_config_tpl',array( 
		  'model'=>$model,
		  'template_name'=>"site_mapkeys",
		  'params'=>array(  
		   'model'=>$model,
		   'map_provider'=>AttributesTools::mapsProvider()
		  )
		));
	}
	
	public function actionrecaptcha()
	{
		$this->pageTitle = t("Google Recaptcha");
		CommonUtility::setMenuActive('.admin_site_information',".admin_site_information");
		
		$model=new AR_option;
		$model->scenario=Yii::app()->controller->action->id;		
		
		/*$options = array('captcha_site_key','captcha_secret','captcha_lang',
		'captcha_customer_signup','captcha_merchant_signup','captcha_customer_login',
		'captcha_merchant_login','captcha_admin_login','captcha_order','captcha_driver_signup',
		'captcha_contact_form','capcha_admin_login_enabled','capcha_merchant_login_enabled');*/
		
		$options = array('captcha_site_key','captcha_secret','captcha_lang','capcha_admin_login_enabled','capcha_merchant_login_enabled');
		
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
		
		$this->render('site_config_tpl',array( 
		  'model'=>$model,
		  'template_name'=>"site_recaptcha",
		  'params'=>array(  
		   'model'=>$model		   
		  )
		));
	}
	
	public function actionprintingOLD()
	{
		$this->pageTitle = t("Printing Options");
		
		CommonUtility::setMenuActive('.admin_site_information',".admin_site_information");
		
		$model=new AR_option;
		$model->scenario=Yii::app()->controller->action->id;		
		
		$options = array('admin_printing_receipt_width','admin_printing_receipt_size','website_enabled_rcpt','website_receipt_logo');
		
		if(isset($_POST['AR_option'])){
			$model->attributes=$_POST['AR_option'];
			if($model->validate()){				
				
				
				$model->image=CUploadedFile::getInstance($model,'image');
				if($model->image){
					$model->website_receipt_logo =  time()."-".$model->image->name;				
					$path = CommonUtility::uploadDestination('')."/".$model->website_receipt_logo;								
					$model->image->saveAs( $path );
				}
				
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
		
		$this->render('site_config_tpl',array( 
		  'model'=>$model,
		  'template_name'=>"site_printing",
		  'params'=>array(  
		   'model'=>$model,		   
		  )
		));
	}	
	
	public function actionprinting()
	{
		$model = new AR_admin_meta;
		$model->scenario = "with_translation";
		$upload_path = CMedia::adminFolder();
		
		if(isset($_POST['AR_admin_meta'])){			
									
			if(DEMO_MODE){			
			    $this->render('//tpl/error',array(  
			          'error'=>array(
			            'message'=>t("Modification not available in demo")
			          )
			        ));	
			    return false;
			}
			
			$post = Yii::app()->input->post('AR_admin_meta');		
			$receipt_logo = Yii::app()->input->post('receipt_logo');
			$receipt_logo_path = Yii::app()->input->post('path');
									
			$model->saveMeta('receipt_logo', $receipt_logo, $receipt_logo_path );
			$model->saveMeta('receipt_thank_you', isset($post['receipt_thank_you'])?$post['receipt_thank_you']:'','','with_translation');
			$model->saveMeta('receipt_footer', isset($post['receipt_footer'])?$post['receipt_footer']:'','','with_translation');
			$model->saveMeta('auto_print_status', isset($post['auto_print_status'])?$post['auto_print_status']:'','');

			if(empty($receipt_logo)){
				Yii::app()->db->createCommand("
				DELETE from {{admin_meta}}
				WHERE meta_name='receipt_logo'
				")->query();
			}

			Yii::app()->user->setFlash('success',CommonUtility::t(Helper_success));
			$this->refresh();			
		} else {
			$criteria = new CDbCriteria;
			$criteria->addInCondition('meta_name',(array) array('receipt_logo','receipt_thank_you','receipt_footer','auto_print_status') );
			$find = AR_admin_meta::model()->findAll($criteria);		
			if($find){
				foreach ($find as $items) {					
					$model[$items->meta_name] = $items->meta_value;
				}
			}			
		}
		
		$fields = array(); $data = array();		
		if(!isset($_POST['AR_admin_meta'])){
			$model_trans = AR_admin_meta::model()->find("meta_name=:meta_name",array(
		     ':meta_name'=>'receipt_thank_you'
		    )); 		    
		    if($model_trans){
				$translation = AttributesTools::GetFromTranslation($model_trans->meta_id,'{{admin_meta}}',
				'{{admin_meta_translation}}',
				'meta_id',
				array('meta_id','meta_value'),
				array('meta_value'=>'meta_value_trans')
				);					
				$data['receipt_thank_you_trans'] = isset($translation['meta_value'])?$translation['meta_value']:'';
		    }
		    
		    $model_trans1 = AR_admin_meta::model()->find("meta_name=:meta_name",array(
		     ':meta_name'=>'receipt_footer'
		    )); 		    
		    if($model_trans1){
				$translation = AttributesTools::GetFromTranslation($model_trans1->meta_id,'{{admin_meta}}',
				'{{admin_meta_translation}}',
				'meta_id',
				array('meta_id','meta_value'),
				array('meta_value'=>'meta_value_trans')
				);					
				$data['receipt_footer_trans'] = isset($translation['meta_value'])?$translation['meta_value']:'';
		    }
		}		
		
		$fields[]=array(
		  'name'=>'receipt_thank_you_trans',
		  'placeholder'=>"Receipt Thank you text - [lang]",
		  'type'=>"textarea"
		);
		$fields[]=array(
		  'name'=>'receipt_footer_trans',
		  'placeholder'=>"Receipt Footer text - [lang]",
		  'type'=>"textarea"
		);		

		$status_list = AttributesTools::getOrderStatus(Yii::app()->language);
		$new_status = ['none' => t("Please select") ];
		$status_list = array_merge($new_status, $status_list);		
		
		$this->render('site_config_tpl',array( 
		  'model'=>$model,
		  'template_name'=>"site_printing",
		  'params'=>array(  
		     'model'=>$model,
		     'upload_path'=>$upload_path,
		     'language'=>AttributesTools::getLanguage(),
		     'fields'=>$fields,
		     'data'=>$data,
			 'status_list'=>$status_list
		  )
		));
	}
	
    public function actionlogin_sigup()
	{
		$this->pageTitle = t("Login & Signup");
		
		CommonUtility::setMenuActive('.admin_site_information',".admin_site_information");
		
		$model=new AR_option;
		$model->scenario=Yii::app()->controller->action->id;		
		
		$options = array('signup_enabled_verification','signup_verification_type',
		'blocked_email_add','blocked_mobile', 'signup_type','signup_enabled_terms','signup_terms',
		'signup_enabled_capcha','signup_welcome_tpl','signup_verification_tpl','signup_resetpass_tpl',
		'signup_resend_counter','signupnew_tpl','enabled_guest','backend_forgot_password_tpl','site_user_avatar',
		'signup_complete_registration_tpl','password_reset_options','login_method'
		);
		
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

				if(isset($_POST['photo'])){
					if(!empty($_POST['photo'])){
						$model->site_user_avatar = $_POST['photo'];
					} else $model->site_user_avatar = '';
				} else $model->site_user_avatar = '';

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
		
		$template_list =  CommonUtility::getDataToDropDown("{{templates}}",'template_id','template_name',
		"","ORDER BY template_name ASC");
		
		$this->render('site_config_tpl',array( 
		  'model'=>$model,		  
		  'template_name'=>"site_login_sigup",
		  'params'=>array(  
		     'model'=>$model,
		     'signup_type_list'=>AttributesTools::signupTypeList(),	
		     'verification_list'=>AttributesTools::verificationType(),
		     'template_list'=>$template_list,
			 'upload_path'=>CMedia::adminFolder(),
		  )
		));
	}	
	
	
	public function actionphone_settings()
	{
		$this->pageTitle = t("Phone Settings");
		
		CommonUtility::setMenuActive('.admin_site_information',".admin_site_information");
		
		$model=new AR_option;
		$model->scenario=Yii::app()->controller->action->id;		
		
		$options = array('mobilephone_settings_country','mobilephone_settings_default_country','backend_phone_mask');
		
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
				$model->mobilephone_settings_country = !empty($model->mobilephone_settings_country)? json_encode($model->mobilephone_settings_country):'';
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
			$model->mobilephone_settings_country = !empty($model->mobilephone_settings_country)? json_decode(stripslashes($model->mobilephone_settings_country),true):'';			
		}		
				
		$this->render('site_config_tpl',array( 
		  'model'=>$model,		  
		  'template_name'=>"site_phone_settings",
		  'params'=>array(  
		     'model'=>$model,
		     'country_list'=>AttributesTools::CountryList(),				     
		  )
		));
	}	
	
    public function actionreviews()
	{
		$this->pageTitle = t("Reviews");		
		CommonUtility::setMenuActive('.admin_site_information',".admin_site_information");
		
		$model=new AR_option;
		$model->scenario=Yii::app()->controller->action->id;		
		
		$options = array('website_review_type','review_baseon_status','earn_points_review_status','publish_review_status',
		'website_reviews_actual_purchase','merchant_can_edit_reviews','review_template_id','review_send_after',
		'review_template_enabled','review_image_resize_width','enabled_review'
		);
		
		if(isset($_POST['AR_option'])){
			$model->attributes=$_POST['AR_option'];
			if($model->validate()){				
				
				$model->review_baseon_status = !empty($model->review_baseon_status)? json_encode($model->review_baseon_status):'';
				$model->earn_points_review_status = !empty($model->earn_points_review_status)? json_encode($model->earn_points_review_status):'';
				$model->publish_review_status = !empty($model->publish_review_status)? json_encode($model->publish_review_status):'';
				
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
			$model->review_baseon_status = !empty($model->review_baseon_status)? json_decode(stripslashes($model->review_baseon_status),true):'';			
			$model->earn_points_review_status = !empty($model->earn_points_review_status)? json_decode(stripslashes($model->earn_points_review_status),true):'';			
			$model->publish_review_status = !empty($model->publish_review_status)? json_decode(stripslashes($model->publish_review_status),true):'';
		}		
		
		$template_list =  CommonUtility::getDataToDropDown("{{templates}}",'template_id','template_name',
		"","ORDER BY template_name ASC");
		
		$this->render('site_config_tpl',array( 
		  'model'=>$model,
		  'template_name'=>"site_reviews",
		  'params'=>array(  
		   'model'=>$model,
		   'review_type'=>AttributesTools::reviewType(),
		   'status_list'=>AttributesTools::StatusList(),
		   'template_list'=>$template_list
		  )
		));
	}	
	
	public function actionsecurity()
	{
		$this->pageTitle = t("Security");
		
		CommonUtility::setMenuActive('.admin_site_information',".admin_site_information");
		
		$model=new AR_option;
		$model->scenario=Yii::app()->controller->action->id;		
		
		$options = array('website_admin_mutiple_login','website_merchant_mutiple_login');
		
		if(isset($_POST['AR_option'])){
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
		
		$this->render('site_config_tpl',array( 
		  'model'=>$model,
		  'template_name'=>"site_security",
		  'params'=>array(  
		   'model'=>$model,
		   'map_provider'=>AttributesTools::mapsProvider()
		  )
		));
	}
	
    public function actiontimezone()
	{
		$this->pageTitle = t("Timezone");
		
		CommonUtility::setMenuActive('.admin_site_information',".admin_site_information");
		
		$model=new AR_option;
		$model->scenario=Yii::app()->controller->action->id;		
		
		$options = array(
			'website_timezone_new',
			'website_date_format_new',
			'website_time_format_new',
			'website_time_picker_interval',
			'website_twentyfour_format',
			'website_date_range',
		);
		
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
		
		$this->render('site_config_tpl',array( 
		  'model'=>$model,
		  'template_name'=>"site_timezone",
		  'params'=>array(  
		   'model'=>$model,
		   'timezone'=>AttributesTools::timezoneList(),
		   'date_format'=>AttributesTools::DateFormat(),
		   'time_format'=>AttributesTools::TimeFormat()
		  )
		));
	}
	
    public function actionordering()
	{
		$this->pageTitle = t("Ordering");
		CommonUtility::setMenuActive('.admin_site_information',".admin_site_information");
		
		$model=new AR_option;
		$model->scenario=Yii::app()->controller->action->id;		
		
		$options = array('disabled_website_ordering','website_hide_foodprice','website_disbaled_auto_cart',
			'website_disabled_cart_validation','enabled_merchant_check_closing_time','disabled_order_confirm_page',
			'restrict_order_by_status','enabled_map_selection_delivery','admin_service_fee','admin_service_fee_applytax',
			'cancel_order_enabled','cancel_order_days_applied','cancel_order_hours','cancel_order_status_accepted',
			'website_review_approved_status','enabled_website_ordering','site_food_avatar','auto_accept_order_enabled','auto_accept_order_status',
			'auto_accept_order_timer','menu_layout','category_position','menu_disabled_inline_addtocart','enabled_include_utensils',
			'order_assigment_flow'
		);
		
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
				
				$model->restrict_order_by_status = !empty($model->restrict_order_by_status)? json_encode($model->restrict_order_by_status):'';
				$model->cancel_order_status_accepted = !empty($model->cancel_order_status_accepted)? json_encode($model->cancel_order_status_accepted):'';

				if(isset($_POST['photo'])){
					if(!empty($_POST['photo'])){
						$model->site_food_avatar = $_POST['photo'];
					} else $model->site_food_avatar = '';
				} else $model->site_food_avatar = '';
						
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
			$model->restrict_order_by_status = !empty($model->restrict_order_by_status)? json_decode(stripslashes($model->restrict_order_by_status),true):'';
			$model->cancel_order_status_accepted = !empty($model->cancel_order_status_accepted)? json_decode(stripslashes($model->cancel_order_status_accepted),true):'';
		}		

		$upload_path = CMedia::adminFolder();
		$time_list = AttributesTools::delayedMinutes(false);				
		$newArray = array(
			1 => t('1 min(s)'),
			2 => t('2 min(s)'),
		);
		$time_list = $newArray + $time_list;		
		
		$this->render('site_config_tpl',array( 
		  'model'=>$model,
		  'template_name'=>"site_ordering",
		  'params'=>array(  
			'model'=>$model,		   
			'status_list'=>AttributesTools::StatusList(),
			'upload_path'=>$upload_path,
			'time_list'=>$time_list,
			'menu_layout'=>[
				'left_image'=>t("Left food image"),
				'right_image'=>t("Right food image"),
				'no_image'=>t("No food image"),			  
				'two_column'=>t("Two column"),
			 ],
		  )
		));
	}	
	
	public function actionmenu_options()
	{
		$this->pageTitle = t("Menu Options");
		CommonUtility::setMenuActive('.admin_site_information',".admin_site_information");
		
		$model=new AR_option;
		$model->scenario=Yii::app()->controller->action->id;		
		
		$options = array('admin_menu_allowed_merchant','admin_menu_lazyload','mobile2_hide_empty_category',
		'admin_activated_menu','enabled_food_search_menu');
		
		if(isset($_POST['AR_option'])){
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
		
		$this->render('site_config_tpl',array( 
		  'model'=>$model,
		  'template_name'=>"site_menu_options",
		  'params'=>array(  
		   'model'=>$model,
		   'menu_style'=>AttributesTools::MenuStyle()
		  )
		));
	}
	
	public function actionmerchant_registration()
	{
		$this->pageTitle = t("Merchant Registration");
		CommonUtility::setMenuActive('.admin_site_information',".admin_site_information");
		
		$model=new AR_option;
		$model->scenario=Yii::app()->controller->action->id;		
				
		$options = array('merchant_enabled_registration',
		'merchant_default_country','merchant_specific_country','pre_configure_size','merchant_enabled_registration_capcha',
		'registration_program','registration_confirm_account_tpl','registration_welcome_tpl',
		'registration_terms_condition','merchant_registration_new_tpl','merchant_registration_welcome_tpl','merchant_plan_expired_tpl',
		'merchant_plan_near_expired_tpl','merchant_allow_login_afterregistration','enabled_copy_opening_hours','merchant_default_opening_hours_start',
		'merchant_default_opening_hours_end','enabled_copy_payment_setting','copy_payment_list','site_merchant_avatar',
		'merchant_bank_deposit_subscriptions','merchant_subscription_approved','merchant_registration_approved',
		'merchant_subscription_payment_process','merchant_subscription_payment_failed','merchant_subscription_cancelled'
		);
		
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
				
				$model->merchant_specific_country = !empty($model->merchant_specific_country)? json_encode($model->merchant_specific_country):'';
				$model->registration_program = !empty($model->registration_program)? json_encode($model->registration_program):'';
				$model->copy_payment_list = !empty($model->copy_payment_list)? json_encode($model->copy_payment_list):'';				
				
				if(isset($_POST['photo'])){
					if(!empty($_POST['photo'])){
						$model->site_merchant_avatar = $_POST['photo'];
					} else $model->site_merchant_avatar = '';
				} else $model->site_merchant_avatar = '';

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
			$model->merchant_specific_country = !empty($model->merchant_specific_country)? json_decode(stripslashes($model->merchant_specific_country),true):'';			
			$model->registration_program = !empty($model->registration_program)? json_decode(stripslashes($model->registration_program),true):'';			
			$model->copy_payment_list = !empty($model->copy_payment_list)? json_decode(stripslashes($model->copy_payment_list),true):'';
		}		
		
		
		$template_list =  CommonUtility::getDataToDropDown("{{templates}}",'template_id','template_name',
		"","ORDER BY template_name ASC");
		
		$program_list = AttributesTools::ListMerchantType();
		
		$this->render('site_config_tpl',array( 
		  'model'=>$model,
		  'template_name'=>"site_merchant_registration",
		  'params'=>array(  
		   'model'=>$model,		 
		   'status_list'=>(array)AttributesTools::StatusManagement('customer'),
		   'country_list'=>AttributesTools::CountryList(),
		   'template_list'=>$template_list,
		   'program_list'=>$program_list,
		   'provider'=>AttributesTools::PaymentProvider(),
		   'upload_path'=>CMedia::adminFolder(),
		  )
		));
	}	
	
	public function actionsearch_settings()
	{
		$this->pageTitle = t("Search");
		CommonUtility::setMenuActive('.admin_site_information',".admin_site_information");
		
		$model=new AR_option;
		$model->scenario=Yii::app()->controller->action->id;		
				
		$options = array('merchant_specific_country','home_search_mode','search_enabled_select_from_map',
		  'search_default_country','location_searchtype','location_default_country','enabled_auto_detect_address',
		  'default_location_lat','default_location_lng','address_format_use','location_enabled_map_selection',
		  'default_distance_unit','use_local_distance'
	   );		
		
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
				$model->merchant_specific_country = !empty($model->merchant_specific_country)? json_encode($model->merchant_specific_country):'';								
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
			$model->merchant_specific_country = !empty($model->merchant_specific_country)? json_decode(stripslashes($model->merchant_specific_country),true):'';			
		}		
		
		
		$country_list = CommonUtility::getDataToDropDown("{{location_countries}}",'shortcode','country_name',
		"WHERE 1","ORDER BY country_name ASC");
		$all = array('all' => t("All Country") );
		$country_list = $all + $country_list;		

		$home_search_mode = !empty($model->home_search_mode)?$model->home_search_mode:'address';		
				
		$location_addon = CommonUtility::getAddonStatus(Yii::app()->params['location_addon_identity']);
		$search_type = AttributesTools::SearchType();		
		if(!$location_addon){
			unset($search_type['location']);
		}
		
		$this->render('site_config_tpl',array( 
		  'model'=>$model,
		  'template_name'=>"site_search_settings",
		  'params'=>array(  
		   'model'=>$model,	
		   'search_type'=>$search_type,
		   'location_search'=>AttributesTools::LocationSearchType(),
		   'country_list'=>$country_list,
		   'home_search_mode'=>$home_search_mode,
		   'unit'=>AttributesTools::unit(),
		   'location_addon'=>$location_addon
		  )
		));
	}
	
	public function actionbooking_settings()
	{
		$this->pageTitle = t("Booking");
		CommonUtility::setMenuActive('.admin_site_information',".admin_site_information");
		
		$model=new AR_option;
		$model->scenario=Yii::app()->controller->action->id;		
		
		$options = array('merchant_tbl_book_enabled','booking_cancel_days','booking_cancel_hours');		
		
		if(isset($_POST['AR_option'])){
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
		
		
		$this->render('site_config_tpl',array( 
		  'model'=>$model,
		  'template_name'=>"site_booking_settings",
		  'params'=>array(  
		   'model'=>$model,		   
		  )
		));
	}
	
	public function actionsite_others()
	{
		$this->pageTitle = t("Others");
		CommonUtility::setMenuActive('.admin_site_information',".admin_site_information");
		
		$model=new AR_option;
		$model->scenario=Yii::app()->controller->action->id;		
		
		$options = array('allow_return_home','image_resizing',
	 	   'runactions_method','runactions_enabled','runaction_test_tpl','maintenance_mode'		   
	    );
		
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
		
		$template_list =  CommonUtility::getDataToDropDown("{{templates}}",'template_id','template_name',
		"","ORDER BY template_name ASC");

		$status_list = AttributesTools::getOrderStatus(Yii::app()->language);		
		$time_list = AttributesTools::delayedMinutes(false);		
		
		$this->render('site_config_tpl',array( 
		  'model'=>$model,
		  'template_name'=>"site_others",
		  'params'=>array(  
		   'model'=>$model,		   
		   'template_list'=>$template_list,
		   'runactions_method'=>[
			   'touchUrl'=>t("touchUrl"),
			   'touchUrlExt'=>t("touchUrlExt"),
			   'fastRequest'=>t("fastRequest"),
		   ],
		   'menu_layout'=>[
			  'left_image'=>t("Left food image"),
			  'right_image'=>t("Right food image"),
			  'no_image'=>t("No food image"),			  
			  'two_column'=>t("Two column"),
		   ],
		   'provider'=>AttributesTools::PaymentProvider(),
		   'test_runactions'=>Yii::app()->createUrl("/admin/test_runactions"),
		   'status_list'=>$status_list,
		   'time_list'=>$time_list
		  )
		));
	}	
	
	public function actionnotifications()
	{
		$this->pageTitle = t("Notifications");
		CommonUtility::setMenuActive('.admin_site_information',".admin_site_information");
		
		$model=new AR_option;
		$model->scenario=Yii::app()->controller->action->id;		
				
		$options = array(
			'admin_enabled_alert',
			'admin_email_alert',
			'admin_mobile_alert',
			'admin_enabled_continues_alert',
			'admin_continues_alert_interval',
			'admin_sounds_order',
			'admin_sounds_chat',
	    );
		
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
			$model->file_sounds = CUploadedFile::getInstance($model,'file_sounds');	
			$model->file_sounds1 = CUploadedFile::getInstance($model,'file_sounds1');	
			if($model->validate()){									
				
				if($model->file_sounds){
					$path = CommonUtility::uploadDestination('upload/sounds/').$model->file_sounds->name;								
				    $model->file_sounds->saveAs($path);		
					$model->admin_sounds_order = $path;
				} else {					
					$options = array_values(array_diff($options, ["admin_sounds_order"]));
				}					
				if($model->file_sounds1){
					$path = CommonUtility::uploadDestination('upload/sounds/').$model->file_sounds1->name;								
				    $model->file_sounds1->saveAs($path);		
					$model->admin_sounds_chat = $path;
				} else {					
					$options = array_values(array_diff($options, ["admin_sounds_chat"]));
				}
				
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
		
		$this->render('site_config_tpl',array( 
		  'model'=>$model,
		  'template_name'=>"site_notifications",
		  'params'=>array(  
		   'model'=>$model,		   
		   'reset_url'=>Yii::app()->createUrl("/admin/resetsounds")
		  )
		));
	}

	public function actionresetsounds(){		
		AR_option::model()->deleteAll('option_name IN (:option_name1, :option_name2)', [
			':option_name1' => 'admin_sounds_order',
			':option_name2' => 'admin_sounds_chat'
		]);		
		CCacheData::add();
		$this->redirect(array('admin/notifications'));	
	}
	
	public function actionlanguage_settings()
	{
		$this->pageTitle = t("Languages");
		CommonUtility::setMenuActive('.admin_site_information',".admin_site_information");
		
		$model=new AR_option;
		$model->scenario=Yii::app()->controller->action->id;		
		
		$options = array('enabled_multiple_translation_new','enabled_language_admin',
		'enabled_language_merchant','enabled_language_front');		
		
		if(isset($_POST['AR_option'])){
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
		
		$this->render('site_config_tpl',array( 
		  'model'=>$model,
		  'template_name'=>"site_language_settings",
		  'params'=>array(  
		   'model'=>$model,		   
		  )
		));
	}
	
	public function actionsocial_settings()
	{
		$this->pageTitle = t("Social Settings");
		CommonUtility::setMenuActive('.admin_site_information',".admin_site_information");
		
		$model=new AR_option;
		$model->scenario=Yii::app()->controller->action->id;		
		
		$options = array('fb_flag','fb_app_id','fb_app_secret',
		  'google_login_enabled','google_client_id','google_client_secret','google_client_redirect_url',
		  'facebook_page','twitter_page','google_page','instagram_page','linkedin_page',
		  'app_enabled_google_login',
		  'app_enabled_fb_login',
		  'app_enabled_apple_login',
		  'app_apple_app_id','app_apple_team_id','app_apple_key_id','app_apple_key_crt',
		  'web_enabled_apple_login','app_google_client_id','app_facebook_client_token'
	    );		
		
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
			$model->file_crt = CUploadedFile::getInstance($model,'file_crt');						
			if($model->validate()){		
									
				if($model->file_crt){
                   $path = CommonUtility::uploadDestination('upload/crt/').$model->file_crt->name;
				   $extension = strtolower($model->file_crt->getExtensionName());				   
				   if($extension=="p8"){
					   $model->app_apple_key_crt = $model->file_crt->name;
					   $model->file_crt->saveAs( $path );	
				   } else {
					   Yii::app()->user->setFlash('error', t("Invalid file extension must be a .json file") );					
					   return false;
				   }
				}	

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
		$model['google_client_redirect_url']=websiteUrl();
		
		$this->render('site_config_tpl',array( 
		  'model'=>$model,
		  'template_name'=>"site_social",
		  'params'=>array(  
		   'model'=>$model,		  
		   'website_redirect_uri'=>CommonUtility::getHomebaseUrl()."/account/apple_callback",
		   'web_redirect_uri'=>CommonUtility::getHomebaseUrl()."/interface/apple_callback",
		   'app_redirect_uri'=>CommonUtility::getHomebaseUrl()."/interface/app_apple_callback",
		  )
		));
	}
	
	public function actioncontact_settings()
	{
		$this->pageTitle = t("Contact Settings");
		CommonUtility::setMenuActive('.admin_site_information',".admin_site_information");
		
		$model=new AR_option;
		$model->scenario=Yii::app()->controller->action->id;		
		
		$options = array('enabled_contact_form','contact_email_receiver','contact_field','contact_content','contact_us_tpl','contact_enabled_captcha');			
		
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

				$model->contact_field = !empty($model->contact_field)? json_encode($model->contact_field):'';
							
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
			$model->contact_field = !empty($model->contact_field)? json_decode(stripslashes($model->contact_field),true):'';			
		}

		$template_list =  CommonUtility::getDataToDropDown("{{templates}}",'template_id','template_name',
		"","ORDER BY template_name ASC");

		$model->contact_page_url = CommonUtility::getHomebaseUrl()."/contactus";
		
		$this->render('site_config_tpl',array( 
		  'model'=>$model,
		  'template_name'=>"site_contact",
		  'params'=>array(  
		     'model'=>$model,	
		     'contact_fields'=>AttributesTools::ContactFields(),
			 'template_list'=>$template_list,			 
		  )
		));
	}	
	
	public function actionanalytics_settings()
	{
	    $this->pageTitle = t("Analytics");
		CommonUtility::setMenuActive('.admin_site_information',".admin_site_information");
		
		$model=new AR_option;
		$model->scenario=Yii::app()->controller->action->id;		
		
		$options = array('enabled_fb_pixel','fb_pixel_id','enabled_google_analytics','google_analytics_tracking_id');		
		
		if(isset($_POST['AR_option'])){
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
		
		$this->render('site_config_tpl',array( 
		  'model'=>$model,
		  'template_name'=>"site_analytics",
		  'params'=>array(  
		   'model'=>$model,		   
		  )
		));
	}
		
	public function actionapi_access()
	{

		try {
			ItemIdentity::addonIdentity('Mobile app');
		} catch (Exception $e) {
			$this->render('//tpl/error',[
				'error'=>[
					'message'=>$e->getMessage()
				]
			]);
			Yii::app()->end();
		}

		$this->pageTitle = t("API Access");
		CommonUtility::setMenuActive('.admin_site_information',".admin_site_information");
		$jwt_token = AttributesTools::JwtMainTokenID();
		
		$model=new AR_option;
		$model->scenario=Yii::app()->controller->action->id;		
				
		$options = array($jwt_token);
						
		if(isset($_POST['AR_option'])){
			$model->attributes=$_POST['AR_option'];			
			if($model->validate()){		
				$payload = [
					'iss'=>Yii::app()->request->getServerName(),
					'sub'=>CommonUtility::generateUIID(),					
					'iat'=>time(),						
				];						
				$jwt = JWT::encode($payload, CRON_KEY, 'HS256');
				$model->website_jwt_token = $jwt;				
				if(OptionsTools::save($options, $model)){
					Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
					$this->refresh();
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			} else dump($model->getErrors());
		}
		
		$data_found = false;
		if($data = OptionsTools::find($options)){
			$data_found = true;
			foreach ($data as $name=>$val) {
				$model[$name]=$val;
			}		
		}		
		
		$template_list =  CommonUtility::getDataToDropDown("{{templates}}",'template_id','template_name',
		"","ORDER BY template_name ASC");
		
		$this->render('site_config_tpl',array( 
		  'model'=>$model,
		  'template_name'=>"api_access",
		  'params'=>array(  
		   'model'=>$model,		   
		   'data_found'=>$data_found,
		   'template_list'=>$template_list
		  )
		));
	}		

	public function actionmobilepage()
	{
		$model = new AR_pages;

		$options = array('page_privacy_policy','page_terms','page_aboutus');

		if(isset($_POST['AR_pages'])){

			if(DEMO_MODE){			
			    $this->render('//tpl/error',array(  
			          'error'=>array(
			            'message'=>t("Modification not available in demo")
			          )
			        ));	
			    return false;
			}
			
			$model->attributes=$_POST['AR_pages'];						
			if(OptionsTools::save($options, $model)){
				Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
				$this->refresh();
			} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
		}

		if($data = OptionsTools::find($options)){
			foreach ($data as $name=>$val) {
				$model[$name]=$val;
			}			
		}		

		$page_list =  CommonUtility::getDataToDropDown("{{pages}}",'page_id','title',
		"WHERE owner='admin'","ORDER BY title ASC");

		$this->render('site_config_tpl',array( 
			'model'=>$model,
			'template_name'=>"mobile_page",
			'params'=>array(  
			 'model'=>$model,		
			 'page_list'=>$page_list			 
			)
		  ));
	}	

	public function actiondelete_apikeys()
	{
		if(DEMO_MODE){			
		    $this->render('//tpl/error',array(  
		          'error'=>array(
		            'message'=>t("Modification not available in demo")
		          )
		        ));	
		    return false;
		}
		
		$jwt_token = AttributesTools::JwtMainTokenID();
		$model = AR_option::model()->find("option_name=:option_name",[':option_name'=>$jwt_token]);
		if($model){
			$model->delete();
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array('admin/api_access'));			
		} else $this->render("error");
	}

	public function actionmobile_settings()
	{
		
		$model=new AR_option;
		$options = array('pwa_url','android_download_url','ios_download_url','enabled_auto_pwa_redirect',
		  'mobile_app_version_android','mobile_app_version_ios','admin_addons_use_checkbox',
		  'admin_category_use_slide','webpush_certificates'
	    );
		
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
		
		$this->render('site_config_tpl',array( 
			'model'=>$model,
			'template_name'=>"mobile_settings",
			'params'=>array(  
			 'model'=>$model			 
			)
		  ));
	}

	public function actionpush_notifications()
	{
		$this->pageTitle = t("API Access");
		CommonUtility::setMenuActive('.admin_site_information',".admin_site_information");
		
		$model = AR_admin_meta::model()->find("meta_name=:meta_name",[
			':meta_name'=>'push_json_file'
		]);
		if(!$model){
			$model = new AR_admin_meta; 
		}		
		
		if(isset($_POST['AR_admin_meta'])){

			if(DEMO_MODE){			
			    $this->render('//tpl/error',array(  
			          'error'=>array(
			            'message'=>t("Modification not available in demo")
			          )
			        ));	
			    return false;
			}

			$model->file = CUploadedFile::getInstance($model,'file');
			if($model->file){				
				$path = CommonUtility::uploadDestination('upload/all/').$model->file->name;				
				$extension = strtolower($model->file->getExtensionName());				
				if($extension=="json"){
					$model->meta_name = "push_json_file";
					$model->meta_value = $model->file->name;
					if($model->save()){
						$model->file->saveAs( $path );					
				        Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
				        $this->refresh();				
					} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString($model->getErrors()) );							
				} else Yii::app()->user->setFlash('error', t("Invalid file extension must be a .json file") );			
			} else {
				Yii::app()->user->setFlash('error', t("an error has occured") );
			}
		}

		$this->render('site_config_tpl',array( 
		  'model'=>$model,
		  'template_name'=>"push_notifications",
		  'params'=>array(  
		     'model'=>$model,		   
		  )
		));
	}

	public function actioncookie_consent()
	{
		$this->pageTitle = t("GDPR cookie consent");
		CommonUtility::setMenuActive('.admin_site_information',".admin_site_information");

		$model=new AR_option;
		$model->scenario=Yii::app()->controller->action->id;		

		$options = array('cookie_consent_enabled','cookie_show_preferences','cookie_theme_primary_color','cookie_theme_mode','cookie_theme_dark_color','cookie_theme_light_color',
		  'cookie_position','cookie_full_width','cookie_title','cookie_link_label','cookie_link_accept_button','cookie_link_reject_button',
		  'cookie_message','cookie_show_preferences','cookie_expiration'
	    );
		
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
		
		$model->cookie_expiration = !empty($model->cookie_expiration)?$model->cookie_expiration:365;
				
		$page_list =  CommonUtility::getDataToDropDown("{{pages}}",'page_id','title',
		"","ORDER BY title ASC");

		$this->render('site_config_tpl',array( 
		  'model'=>$model,
		  'template_name'=>"cookie_settings",		  
		  'params'=>array(  
		     'model'=>$model,
			 'page_list'=>$page_list,
			 'theme_mode'=>[
				'light'=>t("Light"),
				'dark'=>t("Dark"),
			 ],
			 'display_position'=>[
				'top_right'=>t("Top right"),
				'bottom_right'=>t("Bottom Right"),
				'bottom_left'=>t("Bottom left"),
				'top_left'=>t("Top left"),
			 ]
		  )
		));
	}

	public function actiontest_runactions()
	{
		$this->pageTitle = t("Test Runactions");
		CommonUtility::setMenuActive('.admin_site_information',".admin_site_information");

		$model = new AR_option();
		$model->scenario = "test_runactions";

		if(isset($_POST['AR_option'])){
			$model->attributes=$_POST['AR_option'];
			if($model->validate()){
				Yii::import('ext.runactions.components.ERunActions');	
		        $cron_key = CommonUtility::getCronKey();		

				$get_params = array( 					
					'key'=>$cron_key,					
					'language'=>Yii::app()->language,
					'email_address'=>$model->test_runactions_email_address
				);											
				CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/test_runactions?".http_build_query($get_params) );				
				Yii::app()->user->setFlash('success',t("Runactions request successful"));
			} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString($model->getErrors()) );	
		}

		$this->render('test_runactions',[
			'model'=>$model,
			'links'=>array(
				t("Back to site configuration")=>array('/admin/site_others'), 								
			 ),	    
		]);
	}

	public function actioncronjobs()
	{		
		$this->pageTitle = t("Cron jobs");		
		CommonUtility::setMenuActive('.admin_site_information',".admin_site_information");

		$cron_key = CommonUtility::getCronKey();		
		$params = ['key'=>$cron_key];
		
		$cron_link = [
			[
				'link'=> "curl ".CommonUtility::getHomebaseUrl()."/task/runcron?".http_build_query($params)." >/dev/null 2>&1",
				'label'=>t("run every (1)minute")
			],			
			[
				'link'=> "curl ".CommonUtility::getHomebaseUrl()."/task/orderautoaccept?".http_build_query($params)." >/dev/null 2>&1",
				'label'=>t("run every (2)minute")
			],			
		];

		$message = t("Change new order status");
		$message.="<br/>";
		$message.= t("below are the cron jobs that needed to run in your cpanel as http cron");

		$this->render("site_config_tpl",array(		    
			'template_name'=>"//driver/cronjobs",			
			'params'=>array(  						   
			   'cron_link'=>$cron_link,
			   'message'=>$message
			 )
		));					
	}		
	
	public function actiondelivery_management()
	{
		$this->pageTitle = t("Delivery Fee Management");
		CommonUtility::setMenuActive('.admin_site_information',".admin_site_information");

		$settings = Yii::app()->params['settings'];
		$home_search_mode = isset($settings['home_search_mode'])?$settings['home_search_mode']:'';		

		if($home_search_mode=="address"){
			$this->deliverySettings();
		} else if ($home_search_mode=="location"){
			$this->deliveryLocationSettings();
		}		
	}

	public function deliverySettings()
	{
		
		$table_col = array(
			'id'=>array(
				'label'=>t("ID"),
				'width'=>'1%'
			),			
			'distance_from'=>array(
				'label'=>t("Shipping"),
				'width'=>'10%'
			),			
			'distance_price'=>array(
				'label'=>t("Fee"),
				'width'=>'10%'
			),			
			'minimum_order'=>array(
				'label'=>t("Min. Order"),
				'width'=>'10%'
			),			
			'maximum_order'=>array(
				'label'=>t("Max. Order"),
				'width'=>'10%'
			),			
			'fixed_free_delivery_threshold'=>array(
				'label'=>t("Free Threshold"),
				'width'=>'10%'
			),						
			'last_update'=>array(
				'label'=>t("Actions"),
				'width'=>'15%'
			),		  
		);
		$columns = array(			
			array('data'=>'id','visible'=>false),
			array('data'=>'distance_from'),
			array('data'=>'distance_price'),		  
			array('data'=>'minimum_order'),		  
			array('data'=>'maximum_order'),		  
			array('data'=>'fixed_free_delivery_threshold'),		  			  
			array('data'=>null,'orderable'=>false,
			'defaultContent'=>'
			<div class="btn-group btn-group-actions" role="group">
				<a class="ref_edit_popup normal btn btn-light tool_tips"><i class="zmdi zmdi-border-color"></i></a>
				<a class="ref_delete normal btn btn-light tool_tips"><i class="zmdi zmdi-delete"></i></a>
			</div>
			'
			),
		);	
		
		$data_unit = AttributesTools::getDefaultDistanceUnit();				
		$distance_unit = $data_unit['unit'];
		$distance_pretty_unit = $data_unit['pretty'];		
		  
		$action_name = 'getRangeRate';
		ScriptUtility::registerScript(array(
			"var action_get='$action_name';",				
		),'location_manage');	


		$this->render('site_config_tpl',array( 			
			'template_name'=>"delivery_settings",
			'params'=>array(  			   
			   'table_col'=>$table_col,
			   'columns'=>$columns,
			   'order_col'=>0,
			   'sortby'=>'desc',
			   'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/locationrate_create"),
			   'ajax_url'=>Yii::app()->createUrl("/api"),
			   'distance_unit'=>$distance_unit,
			   'distance_pretty_unit'=>$distance_pretty_unit,
			   'eg_distance'=>t("eg. 1 {unit}",[
				'{unit}'=>$distance_pretty_unit
			   ]),
			   'eg_fee'=>t("eg. {price}",[
				'{price}'=>Price_Formatter::formatNumber(1)
			   ])
			)
		  ));
	}

	public function deliveryLocationSettings()
	{	

          $table_col = array(
			'rate_id'=>array(
			  'label'=>t("ID"),
			  'width'=>'1%'
			),			
			'country_name'=>array(
			  'label'=>t("Country"),
			  'width'=>'10%'
			),			
			'state_name'=>array(
			  'label'=>t("State"),
			  'width'=>'10%'
			),			
			'city_name'=>array(
			  'label'=>t("City"),
			  'width'=>'10%'
			),			
			'area_name'=>array(
			  'label'=>t("Area"),
			  'width'=>'10%'
			),			
			'delivery_fee'=>array(
			  'label'=>t("Fee"),
			  'width'=>'10%'
			),			
			'date_created'=>array(
				'label'=>t("Actions"),
				'width'=>'15%'
		    ),		  
		  );
		  $columns = array(			
			array('data'=>'rate_id','visible'=>true),
			array('data'=>'country_name'),
			array('data'=>'state_name'),		  
			array('data'=>'city_name'),		  
			array('data'=>'area_name'),		  
			array('data'=>'delivery_fee'),		  
			array('data'=>null,'orderable'=>false,
			   'defaultContent'=>'
			   <div class="btn-group btn-group-actions" role="group">
				  <a class="ref_edit_popup normal btn btn-light tool_tips"><i class="zmdi zmdi-border-color"></i></a>
				  <a class="ref_delete normal btn btn-light tool_tips"><i class="zmdi zmdi-delete"></i></a>
			  </div>
			   '
			),
		);	

		$action_name = 'getRate';
		ScriptUtility::registerScript(array(
			"var action_get='$action_name';",				
		),'location_manage');	

		$this->render('site_config_tpl',array( 			
			'template_name'=>"delivery_management_list",
			'params'=>array(  
			   'table_col'=>$table_col,
			   'columns'=>$columns,
			   'order_col'=>0,
			   'sortby'=>'desc',
			   'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/locationrate_create"),
			   'ajax_url'=>Yii::app()->createUrl("/api"),
			)
		));
	}

	public function actiondelete_location_rate()
	{
		if(DEMO_MODE){			
		    $this->render('//tpl/error',array(  
		          'error'=>array(
		            'message'=>t("Modification not available in demo")
		          )
		        ));	
		    return false;
		}
		$rate_id = Yii::app()->input->get('id');		
		$model = AR_location_rate::model()->find("rate_id=:rate_id",[
			':rate_id'=>$rate_id
		]);
		if($model){
			$model->delete();
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array('admin/delivery_management'));			
		} else $this->render("error");
	}

	public function actionestimate_management()
	{
		$this->pageTitle = t("Time Estimates Management");
		CommonUtility::setMenuActive('.admin_site_information',".admin_site_information");

		$table_col = array(
			'id'=>array(
			  'label'=>t("ID"),
			  'width'=>'1%'
			),			
			'service_type'=>array(
			  'label'=>t("Services"),
			  'width'=>'10%'
			),			
			'country_name'=>array(
			  'label'=>t("Country"),
			  'width'=>'10%'
			),			
			'state_name'=>array(
			  'label'=>t("State"),
			  'width'=>'10%'
			),			
			'city_name'=>array(
			  'label'=>t("City"),
			  'width'=>'10%'
			),			
			'area_name'=>array(
			  'label'=>t("Area"),
			  'width'=>'10%'
			),			
			'estimated_time_min'=>array(
			  'label'=>t("Estimations"),
			  'width'=>'10%'
			),			
			'date_created'=>array(
				'label'=>t("Actions"),
				'width'=>'15%'
		    ),		  
		  );
		  $columns = array(			
			array('data'=>'id','visible'=>true),
			array('data'=>'service_type'),
			array('data'=>'country_name'),
			array('data'=>'state_name'),		  
			array('data'=>'city_name'),		  
			array('data'=>'area_name'),		  
			array('data'=>'estimated_time_min'),		  
			array('data'=>null,'orderable'=>false,
			   'defaultContent'=>'
			   <div class="btn-group btn-group-actions" role="group">
				  <a class="ref_edit_popup normal btn btn-light tool_tips"><i class="zmdi zmdi-border-color"></i></a>
				  <a class="ref_delete normal btn btn-light tool_tips"><i class="zmdi zmdi-delete"></i></a>
			  </div>
			   '
			),
		);	

		$action_name = 'getEstimate';
		ScriptUtility::registerScript(array(
			"var action_get='$action_name';",				
		),'location_manage');	

		$this->render('site_config_tpl',array( 			
			'template_name'=>"times_management_list",
			'params'=>array(  
			   'services'=>CommonUtility::ArrayToLabelValue((array)AttributesTools::ListSelectServices()),
			   'table_col'=>$table_col,
			   'columns'=>$columns,
			   'order_col'=>0,
			   'sortby'=>'desc',
			   'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/locationrate_create"),
			   'ajax_url'=>Yii::app()->createUrl("/api")
			)
		  ));
	}	

	public function actiondelete_estimate_time()
	{
		if(DEMO_MODE){			
		    $this->render('//tpl/error',array(  
		          'error'=>array(
		            'message'=>t("Modification not available in demo")
		          )
		        ));	
		    return false;
		}
		$id = Yii::app()->input->get('id');		
		$model = AR_location_time_estimate::model()->find("id=:id",[
			':id'=>$id
		]);
		if($model){
			$model->delete();
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array('admin/estimate_management'));			
		} else $this->render("error");
	}

	public function actionfee_management()
	{
		$this->pageTitle = t("Small Order Fee Management");
		CommonUtility::setMenuActive('.admin_site_information',".admin_site_information");

		$model = new AR_services_fee();
		$merchant_id = 0;

		if(isset($_POST['AR_services_fee'])){

			if(DEMO_MODE){			
			    $this->render('//tpl/error',array(  
			          'error'=>array(
			            'message'=>t("Modification not available in demo")
			          )
			        ));	
			    return false;
			}
			$model->attributes=$_POST['AR_services_fee'];
			if($model->validate()){				

				OptionsTools::saveOptions($merchant_id,'show_prices_commission',$model->show_prices_commission);

				if(is_array($model->transaction_type) && count($model->transaction_type)>=1){
					$services_fee = $model->services_fee;
					$service_fee_type = $model->service_fee_type;
					$small_order_fee = $model->small_order_fees;
					$min_order_amount = $model->min_order_amount;
					$service_ids = [];
					foreach ($model->transaction_type as $service_id) {						
						$service_ids[] = $service_id;
						$find = AR_services_fee::model()->find("service_id=:service_id AND merchant_id=:merchant_id",[
							':service_id'=>$service_id,
							':merchant_id'=>$merchant_id
						]);
						if(!$find){
							$find = new AR_services_fee();							
						}
						$find->service_id = intval($service_id);
						$find->merchant_id = $merchant_id;
						$find->charge_type = isset($service_fee_type[$service_id])?$service_fee_type[$service_id]:'fixed';
						$find->service_fee = isset($services_fee[$service_id])? floatval($services_fee[$service_id]) :0;
						$find->small_order_fee = isset($small_order_fee[$service_id])? floatval($small_order_fee[$service_id]) :0;
						$find->small_less_order_based = isset($min_order_amount[$service_id])? floatval($min_order_amount[$service_id]) :0;												
						$find->save();
					}		
									
					Yii::app()->db->createCommand("
					DELETE FROM {{services_fee}}
					WHERE 
					service_id NOT IN (".CommonUtility::arrayToQueryParameters($service_ids).")
					")->query();					
					
					Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
					$this->refresh();							
				} else 	Yii::app()->user->setFlash('error', t("No transaction found on the list"));			
			} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString($model->getErrors()) );
		}

		$models = AR_services_fee::model()->findAll("merchant_id=:merchant_id",[
			':merchant_id'=>$merchant_id
		]);
		if($models){
			foreach ($models as $value) {
				$model->service_fee_type[$value->service_id] = $value->charge_type;
				$model->services_fee[$value->service_id] = Price_Formatter::formatNumberNoSymbol($value->service_fee,true);
				$model->transaction_type[$value->service_id] = $value->service_id;
				$model->small_order_fees[$value->service_id] = Price_Formatter::formatNumberNoSymbol($value->small_order_fee,true);
				$model->min_order_amount[$value->service_id] = Price_Formatter::formatNumberNoSymbol($value->small_less_order_based,true);				
			}				
		}

		$options_find = OptionsTools::find(['show_prices_commission'],$merchant_id);
		$show_prices_commission = $options_find['show_prices_commission'] ?? null;
		$model->show_prices_commission = $show_prices_commission;

		$services = CommonUtility::getDataToDropDown("{{services}}",'service_id','service_name',
		"WHERE status='publish' ","ORDER BY service_name ASC");		

		$this->render('site_config_tpl',array( 
			'model'=>$model,
			'template_name'=>"small_order_fee",
			'params'=>array(  
			   'model'=>$model,	   
			   'services'=>$services,
			   'services_type'=>AttributesTools::TipType()
			)
		));
	}

	public function actiondeleterangebasedfee()
	{
		if(DEMO_MODE){			
		    $this->render('//tpl/error',array(  
		          'error'=>array(
		            'message'=>t("Modification not available in demo")
		          )
		        ));	
		    return false;
		}
		$id = Yii::app()->input->get('id');		
		$model = AR_shipping_rate::model()->find("id=:id AND merchant_id=:merchant_id",[
			':id'=>$id,
			':merchant_id'=>0
		]);
		if($model){
			$model->delete();
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array('admin/delivery_management'));			
		} else $this->render("error");
	}

	public function actionautomatedstatus()
	{
		$this->pageTitle = t("Automated Status Updates");
		CommonUtility::setMenuActive('.admin_site_information',".admin_site_information");
		
		$this->render('site_config_tpl',array( 			
			'template_name'=>"automated_status",
			'params'=>array(  			  
			)
		));
	}

	public function actioncustom_fields()
	{
		$this->pageTitle = t("Automated Status Updates");
		CommonUtility::setMenuActive('.admin_site_information',".admin_site_information");

		$fieldtype_list = [
			'text'=>t("Text"),
			'number'=>t("Number"),
			'date'=>t("Date"),
			'checkbox'=>t("Checkbox"),
			'select'=>t("Select"),
			'textarea'=>t("Textarea"),
		];
		$entity_list = [
			'client'=>t("Customer"),
		];

		$someWords = AttributesTools::someWords();		
		$someWords = json_encode($someWords);		

		ScriptUtility::registerScript(array(			
			"var fieldtype_list='".CJavaScript::quote(json_encode($fieldtype_list))."';",		
			"var entity_list='".CJavaScript::quote(json_encode($entity_list))."';",		
			"var some_words='".CJavaScript::quote($someWords)."';",	      
		  ),'custom_field_script');		

		
		$this->render('site_config_tpl',array( 			
			'template_name'=>"custom_fields",
			'params'=>array(  			  
			)
		));
	}	
	
}
/*end class*/