<?php
class LoginController extends CController
{
	public $layout='login';

	public function __construct($id,$module=null){
		parent::__construct($id,$module);
		// If there is a post-request, redirect the application to the provided url of the selected language 
		if(isset($_POST['language'])) {
			$lang = $_POST['language'];
			$MultilangReturnUrl = $_POST[$lang];
			$this->redirect($MultilangReturnUrl);
		}

		if(isset($_GET['setlang'])) {
			// Yii::app()->cache->delete('admin_menu');
			// Yii::app()->cache->delete('cache_search_menu_data');
			Yii::app()->cache->flush();
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

	public function init()
	{
		AssetsBundle::registerBundle(array(		 
		 'login-css'
		));			
	}
	
	public function behaviors() {
		return array(
	        'BodyClassesBehavior' => array(
	            'class' => 'ext.yii-body-classes.BodyClassesBehavior'
	        ),        
	    );
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
                'actions'=>array('logout','error','login'),
                'users'=>array('*'),
            ),
			array('allow', 			    
				'expression'=>array('ItemIdentity','initializeIdentity'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
				'deniedCallback' => function() { $this->redirect(array('/login/error')); }
			),
		);
	}
    
	public function beforeAction($action)
	{		
		if(!Yii::app()->user->isGuest){
			$login_type = Yii::app()->user->getState("login_type");
			if($login_type=="admin"){
			   $this->redirect(Yii::app()->createUrl('/admin/dashboard'));			
			}
		}								
		return true;
	}
	
	
	public function actionIndex()
	{
		Yii::app()->clientScript->registerMetaTag('noindex', null, null, array('name' => 'robots'));
		
		if (!defined('CRYPT_BLOWFISH')||!CRYPT_BLOWFISH)
			throw new CHttpException(500,"This application requires that PHP was compiled with Blowfish support for crypt().");
			
		$this->pageTitle = t("Administrator Login");
		
		if(DEMO_MODE){
			ScriptUtility::registerScript(array(
			 "
			 function copyCredentials() {
		         $('#LoginForm_username').val('admin');
		         $('#LoginForm_password').val('admin');
		     }
			 "
			),'demo_script');
		}
		
		$options = OptionsTools::find(array(
			'captcha_site_key','captcha_secret','captcha_lang','capcha_admin_login_enabled',
			'enabled_mobileapp_section','android_download_url','ios_download_url','website_logo'
		));
		$captcha_site_key = isset($options['captcha_site_key'])?$options['captcha_site_key']:'';
		$captcha_secret = isset($options['captcha_secret'])?$options['captcha_secret']:'';
		$captcha_lang = isset($options['captcha_lang'])?$options['captcha_lang']:'';
		$captcha_enabled = isset($options['capcha_admin_login_enabled'])?$options['capcha_admin_login_enabled']:'';
		$captcha_enabled = $captcha_enabled==1?true:false;	
		if($captcha_enabled){
			if(empty($captcha_site_key) || empty($captcha_secret) ){
				$captcha_enabled = false;
			}
		}
		
		Yii::app()->reCaptcha->key=$captcha_site_key;
		Yii::app()->reCaptcha->secret=$captcha_secret;
		
						
		$model=new LoginForm;		
		$model->captcha_enabled = $captcha_enabled;
		

		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		// collect user input data
		if(isset($_POST['LoginForm']))
		{		
			$model->attributes=$_POST['LoginForm'];
			// validate user input and redirect to the previous page if valid
			if($model->validate() && $model->login())		
			    Yii::app()->cache->delete('admin_menu');
				Yii::app()->request->redirect( Yii::app()->createUrl("/admin/dashboard") );
		}		

		$enabled_mobileapp_section = isset($options['enabled_mobileapp_section'])?$options['enabled_mobileapp_section']:false;
		$enabled_mobileapp_section = $enabled_mobileapp_section==1?true:false;
		$android_download_url = isset($options['android_download_url'])?$options['android_download_url']:'';
		$ios_download_url = isset($options['ios_download_url'])?$options['ios_download_url']:'';
		$website_logo = isset($options['website_logo'])?$options['website_logo']:'';
		$website_logo = !empty($website_logo)? CMedia::getImage($website_logo,"upload/all") :'';		

		Yii::app()->params['settings'] = [
			'enabled_mobileapp_section'=>$enabled_mobileapp_section,
			'android_download_url'=>$android_download_url,
			'ios_download_url'=>$ios_download_url,
			'website_logo'=>$website_logo
		];
				
		$this->render('loginForm',array(
		  'model'=>$model,
		  'captcha_enabled'=>$captcha_enabled,
		));
	}
	
	public function actionerror()
	{
		echo 'Something went wrong contact the author https://codecanyon.net/user/bastikikang';
	}
	
}
/*end class*/