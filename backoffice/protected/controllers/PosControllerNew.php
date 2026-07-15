<?php
require 'php-jwt/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class PosController extends Commonmerchant
{
		
	public function beforeAction($action)
	{				
		$this->layout = 'backend_merchant_orders';		

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
	
	public function actioncreate_order()
	{										
		$this->layout = 'backend-merchant-no-header';	

		$this->pageTitle = t("POS - Create order");
		
		$this->render("//communication/chats-frame",[      
			'chat_url'=>Yii::app()->createAbsoluteUrl("/pos/createorder")
		]);
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
			

		$this->render("create-order",[

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
		Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl."/assets/js/pos.js?time=".time(),CClientScript::POS_END,[
			'type'=>"module",
			'defer'=>'defer'
		]);

		// $ajax_url = Yii::app()->createAbsoluteUrl("/apipos");
		
		// ScriptUtility::registerScript(array(
		// 	"var pos_api='".CJavaScript::quote($ajax_url)."';",		  			
		// ),'pos_api');

		$this->render("order-history",[
		]);
	}
	
} 
/*end class*/