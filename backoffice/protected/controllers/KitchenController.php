<?php
require 'php-jwt/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class KitchenController extends CommonController
{
		
	public function beforeAction($action)
	{				
		InlineCSTools::registerStatusCSS();
		InlineCSTools::registerOrder_StatusCSS();
		return true;
	}

    public function actionIndex()
    {
        $this->redirect(Yii::app()->createUrl("/tableside/settings"));
    }

    public function actionsettings()
	{
		$this->pageTitle = t("Settings");		

		$model=new AR_option;
		$model->scenario=Yii::app()->controller->action->id;
		
		$options = [
			'tableside_send_status','tableside_auto_print_status'
		];

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
			} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString($model->getErrors()) );
		}
        
		if($data = OptionsTools::find($options)){			
			foreach ($data as $name=>$val) {
				$model[$name]=$val;
			}		
		}		

		$status_list = AttributesTools::getOrderStatus(Yii::app()->language);
		if(is_array($status_list) && count($status_list)>=1){	
			$firstOption = array('' => t("Please select..."));
		    $status_list = array_merge($firstOption, $status_list);		
		}		
		
		$this->render("//tpl/submenu_tpl",array(
		    'model'=>$model,
			'template_name'=>"//kitchen/settings",
			'widget'=>'WidgetKitchen',					
			'params'=>array(  
			   'model'=>$model,			
			   'status_list'=>$status_list,		                  
			   'links'=>array(		 
				t("Kicthen App")=>array(Yii::app()->controller->id."/api_access"),          
                  $this->pageTitle,                           
			   ),
			 )
		));				

	}

    public function actionapi_access()
    {
	
		try {
			ItemIdentity::addonIdentity('Karenderia Kitchen App');			
		} catch (Exception $e) {
			$this->render('//tpl/error',[
				'error'=>[
					'message'=>$e->getMessage()
				]
			]);
			Yii::app()->end();
		}		

        $this->pageTitle = t("Settings");
		CommonUtility::setMenuActive('.Kitchen_app','.kitchen_settings');	

		try {
			ItemIdentity::addonIdentity('Karenderia Kitchen App');
		} catch (Exception $e) {
			$this->render('//tpl/error',[
				'error'=>[
					'message'=>$e->getMessage()
				]
			]);
			Yii::app()->end();
		}

        $this->pageTitle = t("API Access");		        
		$jwt_token = AttributesTools::JwtKitchenTokenID();   
        
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
				$model->kitchen_jwt_token = $jwt;				
				if(OptionsTools::save($options, $model)){
					Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
					$this->refresh();
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString($model->getErrors()) );
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
			'template_name'=>"//kitchen/api_access",
			'widget'=>'WidgetKitchen',					
			'params'=>array(  
			   'model'=>$model,		
			   'data_found'=>$data_found,	   
               'delete_keys'=>Yii::app()->createUrl(Yii::app()->controller->id."/delete_apikeys"),
			   'links'=>array(		 
				t("Kicthen App")=>array(Yii::app()->controller->id."/api_access"),          
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

		$jwt_token = AttributesTools::JwtKitchenTokenID();
		$model = AR_option::model()->find("option_name=:option_name",[':option_name'=>$jwt_token]);
		if($model){
			$model->delete();
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array(Yii::app()->controller->id.'/api_access'));			
		} else $this->render("error");
	}

    public function actionsettings_page()
    {
        $this->pageTitle = t("Pages");		
		CommonUtility::setMenuActive('.Kitchen_app','.kitchen_settings');	

        $model = new AR_pages;

		$options = array('kitchen_page_privacy_policy','kitchen_page_terms','kitchen_page_aboutus');

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
			'template_name'=>"//kitchen/page",
			'widget'=>'WidgetKitchen',					
			'params'=>array(  
			   'model'=>$model,		
			   'page_list'=>$page_list,			      
			   'links'=>array(		 
				t("Kitchen App")=>array('kitchen/settings'),        
                  $this->pageTitle,                           
			   ),
			 )
		));				
    }

	public function actionpush_notifications()
	{
		$this->pageTitle = t("API Access");
		CommonUtility::setMenuActive('.Kitchen_app','.kitchen_settings');	
		
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
		  'widget'=>'WidgetKitchen',	
		  'params'=>array(  
		     'model'=>$model,	
			 'links'=>array(		 
				t("Kitchen App")=>array('kitchen/settings'),  
                  $this->pageTitle,                           
			   ),	   
		  )
		));
	}	

}
// end class