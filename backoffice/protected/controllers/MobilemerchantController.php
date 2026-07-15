<?php
require 'php-jwt/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class MobilemerchantController extends CommonController
{
		
	public function beforeAction($action)
	{				
		InlineCSTools::registerStatusCSS();
		InlineCSTools::registerOrder_StatusCSS();

		// try {
		// 	ItemIdentity::addonIdentity('Merchant app');
		// } catch (Exception $e) {
		// 	$this->render('//tpl/error',[
		// 		'error'=>[
		// 			'message'=>$e->getMessage()
		// 		]
		// 	]);
		// 	Yii::app()->end();
		// }
			
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

	
    public function actionapi_access()
    {
		
		try {
			ItemIdentity::addonIdentity('Merchant app');
		} catch (Exception $e) {
			$this->render('//tpl/error',[
				'error'=>[
					'message'=>$e->getMessage()
				]
			]);
			Yii::app()->end();
		}

        $this->pageTitle = t("API Access");		
		$jwt_token = AttributesTools::JwtMerchantTokenID();   
        
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
				$model->merchant_jwt_token = $jwt;				
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
		
		$this->render("//tpl/submenu_tpl",array(
		    'model'=>$model,
			'template_name'=>"//mobilemerchant/api_access",
			'widget'=>'WidgetMobileMerchantSettings',					
			'params'=>array(  
			   'model'=>$model,		
			   'data_found'=>$data_found,	   
			   'links'=>array(		 
				t("Mobile Merchant")=>array('mobilemerchant/api_access'),          
                  $this->pageTitle,                           
			   ),
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

		$jwt_token = AttributesTools::JwtMerchantTokenID();
		$model = AR_option::model()->find("option_name=:option_name",[':option_name'=>$jwt_token]);
		if($model){
			$model->delete();
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array('mobilemerchant/api_access'));			
		} else $this->render("error");
	}

	public function actionsettings()
	{
		$this->pageTitle = t("Settings");		
		CommonUtility::setMenuActive('.mobile_merchant','.mobilemerchant_api_access');	

		$model=new AR_option;
		$model->scenario=Yii::app()->controller->action->id;		

		$options = array(
			'mt_app_version_android',
			'mt_app_version_ios',
			'mt_android_download_url',
			'mt_ios_download_url',
			'enable_new_order_alert',
			'new_order_alert_interval',
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
			} else {
				Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString($model->getErrors()) );	
			}
		}
        
		if($data = OptionsTools::find($options)){			
			foreach ($data as $name=>$val) {
				$model[$name]=$val;
			}		
		}		
		
		$this->render("//tpl/submenu_tpl",array(
		    'model'=>$model,
			'template_name'=>"//mobilemerchant/settings",
			'widget'=>'WidgetMobileMerchantSettings',					
			'params'=>array(  
			   'model'=>$model,					   
			   'links'=>array(		 
				t("Mobile Merchant")=>array('mobilemerchant/api_access'),        
                  $this->pageTitle,                           
			   ),
			 )
		));				
	}
    
    public function actionsettings_page()
    {
        $this->pageTitle = t("Pages");		
		CommonUtility::setMenuActive('.mobile_merchant','.mobilemerchant_api_access');	

        $model = new AR_pages;

		$options = array('merchant_page_privacy_policy','merchant_page_terms','merchant_page_aboutus');

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
		"","ORDER BY title ASC");
        
		$this->render("//tpl/submenu_tpl",array(
		    'model'=>$model,
			'template_name'=>"//mobilemerchant/page",
			'widget'=>'WidgetMobileMerchantSettings',					
			'params'=>array(  
			   'model'=>$model,		
			   'page_list'=>$page_list,			      
			   'links'=>array(		 
				t("Mobile Merchant")=>array('mobilemerchant/api_access'),        
                  $this->pageTitle,                           
			   ),
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

		$this->render('//tpl/submenu_tpl',array( 
		  'model'=>$model,
		  'template_name'=>"//admin/push_notifications",
		  'widget'=>'WidgetMobileMerchantSettings',	
		  'params'=>array(  
		     'model'=>$model,	
			 'links'=>array(		 
				t("Mobile Merchant")=>array('mobilemerchant/api_access'),        
                  $this->pageTitle,                           
			   ),	   
		  )
		));
	}

	public function actioncronjobs()
	{		
		$this->pageTitle = t("Cron jobs");		
		CommonUtility::setMenuActive('.mobile_merchant',".mobilemerchant_api_access");

		$cron_key = CommonUtility::getCronKey();		
		$params = ['key'=>$cron_key];

		$cron_link = [
			[
				'link'=> "curl ".CommonUtility::getHomebaseUrl()."/task/getneworder?".http_build_query($params)." >/dev/null 2>&1",
				'label'=>t("run every (2)minute")
			],			
		];

		$message = t("This will send continues alert to merchant app for new orders");
		$message.="<br/>";
		$message.= t("below are the cron jobs that needed to run in your cpanel as http cron");

		$this->render("//tpl/submenu_tpl",array(		    
			'template_name'=>"//driver/cronjobs",
			'widget'=>'WidgetMobileMerchantSettings',					
			'params'=>array(  			
			   'links'=>array(		 
			      t("Settings")=>array('driver/settings'),        
                  $this->pageTitle,                           
			   ),
			   'cron_link'=>$cron_link,
			   'message'=>$message
			 )
		));					
	}

}
// end controller