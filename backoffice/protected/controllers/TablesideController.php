<?php
require 'php-jwt/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class TablesideController extends CommonController
{
		
	public function beforeAction($action)
	{				
		InlineCSTools::registerStatusCSS();
		InlineCSTools::registerOrder_StatusCSS();
		return true;
	}

    public function actionIndex()
    {
        $this->redirect(Yii::app()->createUrl("/tableside/api_access"));
    }

    public function actionapi_access()
    {
		
		try {
			ItemIdentity::addonIdentity('Karenderia Tableside Ordering');
		} catch (Exception $e) {
			$this->render('//tpl/error',[
				'error'=>[
					'message'=>$e->getMessage()
				]
			]);
			Yii::app()->end();
		}

        $this->pageTitle = t("API Access");		        
		$jwt_token = AttributesTools::JwttablesideTokenID();   
        
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
				$model->tableside_jwt_token = $jwt;				
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
			'template_name'=>"//tableside/api_access",
			'widget'=>'WidgetTableSide',					
			'params'=>array(  
			   'model'=>$model,		
			   'data_found'=>$data_found,	   
               'delete_keys'=>Yii::app()->createUrl(Yii::app()->controller->id."/delete_apikeys"),
			   'links'=>array(		 
				t("Tableside Ordering")=>array(Yii::app()->controller->id."/api_access"),          
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

		$jwt_token = AttributesTools::JwttablesideTokenID();
		$model = AR_option::model()->find("option_name=:option_name",[':option_name'=>$jwt_token]);
		if($model){
			$model->delete();
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array(Yii::app()->controller->id.'/api_access'));			
		} else $this->render("error");
	}

}
// end class