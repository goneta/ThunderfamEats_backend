<?php

use PhpParser\Node\Expr\Isset_;

class ThirdpartyController extends CommonController
{
		
	public function beforeAction($action)
	{				
		
		return true;
	}
	
	public function actionrealtime()
	{
		$this->pageTitle = t("Real time applications");
				
		$model = new AR_admin_meta;		
		$model->scenario=Yii::app()->controller->action->id;		
		
		if(isset($_POST['AR_admin_meta'])){		
									
			if(DEMO_MODE){			
			    $this->render('//tpl/error',array(  
			          'error'=>array(
			            'message'=>t("Modification not available in demo")
			          )
			        ));	
			    return false;
			}
				
			$post = $_POST['AR_admin_meta'];		
			
			$model->saveMeta('realtime_app_enabled', isset($post['realtime_app_enabled'])?intval($post['realtime_app_enabled']):0 );
			$model->saveMeta('realtime_provider', isset($post['realtime_provider'])?$post['realtime_provider']:'pusher' );
			$model->saveMeta('pusher_app_id', isset($post['pusher_app_id'])?$post['pusher_app_id']:'' );
			$model->saveMeta('pusher_key', isset($post['pusher_key'])?$post['pusher_key']:'' );
			$model->saveMeta('pusher_secret', isset($post['pusher_secret'])?$post['pusher_secret']:'' );
			$model->saveMeta('pusher_cluster', isset($post['pusher_cluster'])?$post['pusher_cluster']:'' );
			
			$model->saveMeta('ably_apikey', isset($post['ably_apikey'])?$post['ably_apikey']:'' );
			
			$model->saveMeta('piesocket_clusterid', isset($post['piesocket_clusterid'])?$post['piesocket_clusterid']:'' );
			$model->saveMeta('piesocket_api_key', isset($post['piesocket_api_key'])?$post['piesocket_api_key']:'' );
			$model->saveMeta('piesocket_api_secret', isset($post['piesocket_api_secret'])?$post['piesocket_api_secret']:'' );
			$model->saveMeta('piesocket_websocket_api', isset($post['piesocket_websocket_api'])?$post['piesocket_websocket_api']:'' );
			
			Yii::app()->user->setFlash('success',CommonUtility::t(Helper_success));
			$this->refresh();					
		} else {
			$criteria = new CDbCriteria;
			$criteria->addInCondition('meta_name',(array) array('realtime_app_enabled','realtime_provider','pusher_app_id',
			  'pusher_key','pusher_secret','pusher_cluster','ably_apikey',
			  'piesocket_clusterid','piesocket_api_key','piesocket_api_secret','piesocket_websocket_api'
			));
			$find = AR_admin_meta::model()->findAll($criteria);		
			if($find){
				foreach ($find as $items) {					
					if(DEMO_MODE){
						$model[$items->meta_name] = CommonUtility::mask(date('YjmdHs'));
					} else $model[$items->meta_name] = $items->meta_value;					
				}
			}			
		}
		
		$this->render("realtime_settings",array(
		  'model'=>$model,
		));
	}
	
	public function actionwebpush()
	{
		$this->pageTitle = t("Web push notifications");
		$model = new AR_admin_meta;		
		$model->scenario=Yii::app()->controller->action->id;		
		
		if(isset($_POST['AR_admin_meta'])){			
						
			if(DEMO_MODE){			
			    $this->render('//tpl/error',array(  
			          'error'=>array(
			            'message'=>t("Modification not available in demo")
			          )
			        ));	
			    return false;
			}
			
			$post = $_POST['AR_admin_meta'];						
			$model->saveMeta('webpush_provider', isset($post['webpush_provider'])?$post['webpush_provider']:'pusher' );
			$model->saveMeta('pusher_instance_id', isset($post['pusher_instance_id'])?$post['pusher_instance_id']:'' );
			$model->saveMeta('pusher_primary_key', isset($post['pusher_primary_key'])?$post['pusher_primary_key']:'' );	
			$model->saveMeta('onesignal_app_id', isset($post['onesignal_app_id'])?$post['onesignal_app_id']:'' );	
			$model->saveMeta('onesignal_rest_apikey', isset($post['onesignal_rest_apikey'])?$post['onesignal_rest_apikey']:'' );	
			$model->saveMeta('webpush_app_enabled', isset($post['webpush_app_enabled'])?$post['webpush_app_enabled']:0 );	
			Yii::app()->user->setFlash('success',CommonUtility::t(Helper_success));
			$this->refresh();					
		} else {
			$criteria = new CDbCriteria;
			$criteria->addInCondition('meta_name',(array) array('webpush_provider','pusher_instance_id',
			'pusher_primary_key','onesignal_app_id','onesignal_rest_apikey','webpush_app_enabled') );
			$find = AR_admin_meta::model()->findAll($criteria);		
			if($find){
				foreach ($find as $items) {		
					if(DEMO_MODE){
						$model[$items->meta_name] = CommonUtility::mask(date('YjmdHs'));
					} else $model[$items->meta_name] = $items->meta_value;					
				}
			}			
		}
		
		$this->render("webpush_settings",array(
		  'model'=>$model,
		));
	}

	public function actionfirebase()
	{
		$this->pageTitle = t("Firebase Configuration");				

		$model=new AR_option;
		$options = ['firebase_apikey','firebase_domain','firebase_projectid','firebase_storagebucket','firebase_messagingid','firebase_appid'];

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
				if(DEMO_MODE){
					$model[$name] = CommonUtility::mask(date("Ymjhs"));
				} else $model[$name]=$val;				
			}			
		}		
		
		$this->render("//driver/firebase_settings",array(
			'model'=>$model,
		));
							
	}

	public function actionwhatsapp_settings()
	{
		$this->pageTitle = t("WhatsApp Configuration");	
		$model=new AR_option;
		$options = [
			'whatsapp_enabled','whatsapp_business_accountid','whatsapp_phone_number','whatsapp_token','whatsapp_receipt_templatename',
			'whatsapp_receipt_templatename_merchant','whatsapp_language'
		];
		
		if(isset($_GET['success'])){
			if(!isset($_POST['AR_option'])){
				if($_GET['success']==true){
					Yii::app()->user->setFlash('success',t("Update Templates successful"));
				}
			}			
		}

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
				if(DEMO_MODE){
					$model[$name] = CommonUtility::mask(date("Ymjhs"));
				} else $model[$name]=$val;				
			}			
		}		

		$templates = CWhatsApp::listTemplates();
		$language_list = CWhatsApp::languageList();
		
		$this->render("whatsapp_settings",array(
			'model'=>$model,
			'update_template_link'=>Yii::app()->createUrl("/thirdparty/whatsapp_update_templates"),
			'templates'=>$templates,
			'language_list'=>$language_list
		));
							
	}

	public function actionwhatsapp_update_templates()
	{
		try {

			$error = '';
			$options = OptionsTools::find([
				'whatsapp_business_accountid','whatsapp_token'
			]);			
			$business_accountid = isset($options['whatsapp_business_accountid'])?$options['whatsapp_business_accountid']:'';
			$token = isset($options['whatsapp_token'])?$options['whatsapp_token']:'';

			CWhatsApp::setBusiness($business_accountid);
			CWhatsApp::setToken($token);
			$data = CWhatsApp::getTemplates([
				'fields'=>'name,status',
				'limit'=>100
			]);

			$meta_name = 'whatsapp_templates';

			AR_admin_meta::model()->deleteAll('meta_name=:meta_name',array(
				':meta_name'=> $meta_name
			));

			$params = [];
			foreach ($data as $items) {
				$params[] = [
					'meta_name'=>$meta_name,
					'meta_value'=>$items['name'],
					'meta_value1'=>$items['id'],
				];
			}

			$builder=Yii::app()->db->schema->commandBuilder;
			$command=$builder->createMultipleInsertCommand('{{admin_meta}}',$params);
			$command->execute();

			$this->redirect(Yii::app()->createUrl("/thirdparty/whatsapp_settings?success=true"));

		} catch (Exception $e) {
		    $error = t($e->getMessage());		
			$this->render("//tpl/errors",[
				'back'=>Yii::app()->createUrl("/thirdparty/whatsapp_settings"),
				'error'=>[
					'message'=>$error
				]
			]);
		}	
	}

	
} 
/*end class*/