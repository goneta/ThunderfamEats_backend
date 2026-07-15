<?php
class PosoldController extends Commonmerchant
{
		
	public function beforeAction($action)
	{				
		$this->layout = 'backend_merchant_orders';		
		return true;
	}
	
	public function actioncreate_order()
	{				
		AssetsFrontBundle::includeMaps();
		
		$printer_list = [];
		try {
			$printer_list = FPinterface::getPrinterList(Yii::app()->merchant->merchant_id);
		} catch (Exception $e) {
			//
		}

		$this->render("//pos/create_order",array(
		   'ajax_url'=>Yii::app()->createUrl("/apibackend"),
		   'merchant_id'=>Yii::app()->merchant->merchant_id,
		   'view_admin'=>false,
		   'responsive'=>AttributesTools::CategoryResponsiveSettings('half'),
		   'printer_list'=>$printer_list
		));
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

		$ajax_url = Yii::app()->createAbsoluteUrl("/apipos");
		
		ScriptUtility::registerScript(array(
			"var pos_api='".CJavaScript::quote($ajax_url)."';",		  			
		),'pos_api');

		$this->render("create-order",[

		]);
	}
	
	public function actionorders()
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
	
} 
/*end class*/