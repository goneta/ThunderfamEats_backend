<?php
class ResetpswdController extends CController
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
			array(
			  'application.filters.HtmlCompressorFilter',
			)
		);
	}
    
	public function beforeAction($action)
	{		
		if(!Yii::app()->merchant->isGuest){
			$this->redirect(Yii::app()->createUrl('/merchant/dashboard'));
			Yii::app()->end();
		}								
		return true;
	}
	
	public function actionIndex()
	{
		$this->pageTitle = t("Merchant - Forgot Password");
		
		$model = new AR_merchant_login;
		$model->scenario='forgot_password';
		
		if(isset($_POST['AR_merchant_login'])){
			$model->attributes=$_POST['AR_merchant_login'];				
			if($model->validate()){				
				$user = AR_merchant_login::model()->find('contact_email=:contact_email', array(':contact_email'=>$model->email_address));			
				$user->scenario = "send_forgot_password";
				$user->date_modified = CommonUtility::dateNow();
				$user->save();				
				Yii::app()->user->setFlash('success',t("E-mail has been sent to your account."));
				$this->refresh();
			}
		}
		
		$this->render('//forgotpassword/forgot_password',
		   array(
		     'model'=>$model,
		     'back_link'=>Yii::app()->createUrl("/auth/login")
		));
	}

	public function actionreset()
	{
		$this->pageTitle = t("Merchant - Forgot Password");
		$token = Yii::app()->input->get('token');		
		$model = AR_merchant_user::model()->find("user_uuid=:user_uuid",[
			':user_uuid'=>$token
		]);		
		if($model){
			$model->scenario = "reset_password";
			if(isset($_POST['AR_merchant_user'])){
				$model->attributes=$_POST['AR_merchant_user'];
				if($model->validate()){
					$model->password = md5($model->new_password);					
					$model->date_modified = CommonUtility::dateNow();
					$model->user_uuid = CommonUtility::generateToken("{{merchant_user}}",'user_uuid');
					if($model->save()){
					   $this->redirect( Yii::app()->createUrl("/resetpswd/success") );
					} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
				} else Yii::app()->user->setFlash('error',t("An error has occured."));
			}

			$this->render('//forgotpassword/reset_password',
			array(		    
				'model'=>$model,
				'back_link'=>Yii::app()->createUrl("/auth/login")
			));
		} else {
			$this->render("//admin/error",[
				'error'=>[
					'message'=>t("Sorry we cannot find what your looking for.")
				]
			]);
		}
	}

	public function actionsuccess()
	{
		Yii::app()->user->setFlash('success',CommonUtility::t("Your password has been reset."));
		$this->render('//forgotpassword/reset_success',[
			'back_link'=>Yii::app()->createUrl("/auth/login")
		]);
	}
	
}
/*end class*/