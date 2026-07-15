<?php
Yii::import('zii.widgets.CMenu', true);

class WidgetDriverMenu extends CMenu
{		 
	 public function init()
	 {		 		 	  
	 	   $id = trim(Yii::app()->input->get('id'));	
	 	  	 	  
	 	   $menu = array();
		   $controller = Yii::app()->controller->id;		   

		   $menu[]=array(
			'label'=>'<i class="zmdi zmdi-info-outline"></i>'.t("Overview"),
			'url'=>array("/$controller/overview",'id'=>$id)
		  );	 	  	 	 	 	


	 	  $menu[]=array(
	 	    'label'=>'<i class="zmdi zmdi-account-circle"></i>'.t("Information"),
	 	    'url'=>array("/$controller/update",'id'=>$id)
	 	  );	 	  	 	 	 	

		   $menu[]=array(
			'label'=>'<i class="zmdi zmdi-image"></i>'.t("License"),
			'url'=>array("/$controller/license",'id'=>$id)
		  );	 	  	 	 	 	

		   $menu[]=array(
			'label'=>'<i class="zmdi zmdi-car"></i>'.t("Vehicle"),
			'url'=>array("/$controller/vehicle",'id'=>$id)
		  );	 	  	 	 	 	
	 	   	 	  	 	  
		  $menu[]=array(
			'label'=>'<i class="zmdi zmdi-balance"></i>'.t("Bank information"),
			'url'=>array("/$controller/bank_info",'id'=>$id)
		  );	 	  	 	 	 	

		  $menu[]=array(
			'label'=>'<i class="zmdi zmdi-balance-wallet"></i>'.t("Wallet"),
			'url'=>array("/$controller/wallet",'id'=>$id),
			'itemOptions'=>array(
			  'class'=>"driver-wallet"
			)
		  );

		  $menu[]=array(
			'label'=>'<i class="zmdi zmdi-money-off"></i>'.t("Cashout"),
			'url'=>array("/$controller/cashout_transactions",'id'=>$id),
			'itemOptions'=>array(
			  'class'=>"cashout-transactions"
			)
		  );

		  $menu[]=array(
			'label'=>'<i class="zmdi zmdi-shopping-basket"></i>'.t("Delivery transactions"),
			'url'=>array("/$controller/delivery_transactions",'id'=>$id),
			'itemOptions'=>array(
			  'class'=>"driver-delivery-trans"
			)
		  );	 	  	 	 	 	
		  
		  $menu[]=array(
			'label'=>'<i class="zmdi zmdi-money"></i>'.t("Order Tips"),
			'url'=>array("/$controller/order_tips",'id'=>$id),
			'itemOptions'=>array(
			  'class'=>"driver-order-tips"
			)
		  );	 	  	 	 	 	

		  $menu[]=array(
			'label'=>'<i class="zmdi zmdi-time"></i>'.t("Time logs"),
			'url'=>array("/$controller/time_logs",'id'=>$id),
			'itemOptions'=>array(
			  'class'=>"driver-time-logs"
			)
		  );	 	  	 	 	 	

		   $menu[]=array(
			'label'=>'<i class="zmdi zmdi-star"></i>'.t("Reviews"),
			'url'=>array("/$controller/review_ratings",'id'=>$id),
			'itemOptions'=>array(
			  'class'=>"driver-reviews"
			)
		  );	 	 
	 	  	 	 
	 	  $this->items = $menu;	 	  
	 	  	 	  
	 	  $this->encodeLabel = false;
	 	  $this->activeCssClass = "active";
	 	  $this->activateParents = true;
	 	  $this->htmlOptions = array(
	 	    'class'=>'driver-menu'
	 	  ); 
	 	  $this->submenuHtmlOptions = array(
	 	    'class'=>'driver-sub-menu'
	 	  ); 
	 	  
	 	  parent::init();
	 }
	 
}
/*end class*/