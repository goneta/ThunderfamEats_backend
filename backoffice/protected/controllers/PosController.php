<?php
require 'php-jwt/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class PosController extends Commonmerchant
{
		
	public function beforeAction($action)
	{				
		$this->layout = 'backend_merchant_orders';		
		
		if(empty(Yii::app()->merchant->logintoken)){			
			$session_token = CommonUtility::createUUID("{{merchant_user}}",'session_token');				
			$model_user = AR_merchant_user::model()->findByPk(Yii::app()->merchant->id);
			if($model_user){
				$model_user->session_token = $session_token;
				$model_user->save();
				Yii::app()->merchant->logintoken = $session_token;
			}
		}

		$payload = [
			'iss'=>Yii::app()->request->getServerName(),
			'sub'=>0,
			'iat'=>time(),
			'token'=>Yii::app()->merchant->logintoken			
		];		
		$jwt_token = JWT::encode($payload, CRON_KEY, 'HS256');		

		$ajax_url = str_replace(BACKOFFICE_FOLDER,"",websiteUrl())."apipos";		
		
		ScriptUtility::registerScript(array(
			"var pos_api='".CJavaScript::quote($ajax_url)."';",		  			
			"var token='".CJavaScript::quote($jwt_token)."';",		 
			"var language='".CJavaScript::quote(Yii::app()->language)."';",		
		),'pos_api');

		return true;
	}

	public function actionIndex()
	{
		$this->redirect(Yii::app()->createUrl("/pos/create_order"));
	}
	
	public function actioncreate_order()
	{										
		// $this->layout = 'backend-merchant-no-header';	

		// $this->pageTitle = t("POS - Create order");
		
		// $this->render("//communication/chats-frame",[      
		// 	'chat_url'=>Yii::app()->createAbsoluteUrl("/pos/createorder")
		// ]);
		$this->actionCreateorder();
	}
	
	public function actionCreateorder()
	{
		$this->layout = 'backend_full';	
        $this->pageTitle = t("Create Order");        
        
		$cs = Yii::app()->getClientScript();
		Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl."/assets/js/pos.js?time=".time(),CClientScript::POS_END,[
			'type'=>"module",
			'defer'=>'defer'
		]);
		
		$options = OptionsTools::find(['merchant_enabled_tableside_alert'],Yii::app()->merchant->merchant_id);		
		$enabled_tableside_alert = isset($options['merchant_enabled_tableside_alert'])?$options['merchant_enabled_tableside_alert']:false;
		$enabled_tableside_alert = $enabled_tableside_alert==1?true:false;

		AssetsFrontBundle::includeMaps();

		$option = OptionsTools::find(['website_logo','mobilelogo']);
		$website_logo = isset($option['website_logo'])?$option['website_logo']:'';
		if(!empty($website_logo)){
			$website_logo = CMedia::getImage($website_logo, CMedia::adminFolder());		
		} else {
			$website_logo = Yii::app()->theme->baseUrl."/assets/images/logo@2x.png";
		}
		
		$this->render("create-order",[
			'enabled_tableside_alert'=>$enabled_tableside_alert,
			'interval_seconds'=>30,
			'website_logo'=>$website_logo
		]);
	}
	
	public function actionordersOLD()
	{
				
		$this->layout = 'backend_merchant';
		$this->pageTitle = t("POS Orders");
		
		$table_col = array(
		  'logo'=>array(
		    'label'=>'',
		    'width'=>'8%'
		  ),
		  'order_id'=>array(
		    'label'=>t("Order ID"),
		    'width'=>'8%'
		  ),
		  'client_id'=>array(
		    'label'=>t("Customer"),
		    'width'=>'15%'
		  ),		  
		  'status'=>array(
		    'label'=>t("Order Information"),
		    'width'=>'25%'
		  ),
		  'order_uuid'=>array(
		    'label'=>t("Actions"),
		    'width'=>'10%'
		  ),
		);
		$columns = array(
		  array('data'=>'logo','orderable'=>false),
		  array('data'=>'order_id'),
		  array('data'=>'client_id','orderable'=>false),
		  array('data'=>'status','orderable'=>false),		  
		  array('data'=>'order_uuid','orderable'=>false),		  
		);				
		
		$this->render("post-order-list",array(
		  'table_col'=>$table_col,
		  'columns'=>$columns,		
		  'order_col'=>1,
          'sortby'=>'desc',  
		));
	}

	public function actionhold_orders()
	{		
		$this->layout = 'backend-merchant-no-header';	
		
		$this->pageTitle = t("POS - Hold Orders");

		$this->render("//communication/chats-frame",[      
			'chat_url'=>Yii::app()->createAbsoluteUrl("/pos/holdolders")
		]);
	}

	public function actionholdolders()
	{
		$this->layout = 'backend_full';	
        $this->pageTitle = t("Create Order");        
        
		$cs = Yii::app()->getClientScript();
		Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl."/assets/js/pos.js?time=".time(),CClientScript::POS_END,[
			'type'=>"module",
			'defer'=>'defer'
		]);

		// $ajax_url = Yii::app()->createAbsoluteUrl("/apipos");
		
		// ScriptUtility::registerScript(array(
		// 	"var pos_api='".CJavaScript::quote($ajax_url)."';",		  			
		// ),'pos_api');

		$this->render("hold-orders",[
		]);
	}

	public function actionorder_history()
	{		
		$this->layout = 'backend-merchant-no-header';	

		$this->pageTitle = t("POS - Order history");
		
		$this->render("//communication/chats-frame",[      
			'chat_url'=>Yii::app()->createAbsoluteUrl("/pos/orderhistory")
		]);
	}

	public function actionorderhistory()
	{
		$this->layout = 'backend_full';	
        $this->pageTitle = t("Create Order");        
        
		$cs = Yii::app()->getClientScript();
		Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl."/assets/js/pos-history.js?time=".time(),CClientScript::POS_END,[
			'type'=>"module",
			'defer'=>'defer'
		]);		

		$this->render("order-history",[
		]);
	}
	
} 
/*end class*/