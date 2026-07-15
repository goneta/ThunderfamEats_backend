<?php
Yii::import('zii.widgets.CMenu', true);

class WidgetMultiCurrency extends CMenu
{		 
	 public static $ctr = array();
	 
	 public function init()
	 {		 		 	  
	 	  	 	  	 	  
	 	  $menu = array();

		   $menu[]=array(
			'label'=>'<i class="zmdi zmdi-settings"></i>'.t("Settings"),
			'url'=>array(Yii::app()->controller->id.'/settings')
		  );	 	  	 	 	 	
		  
		  $menu[]=array(
			'label'=>'<i class="fas fa-shopping-bag"></i>'.t("Payment gateway"),
			'url'=>array(Yii::app()->controller->id.'/payment_gateway')
		  );	 	  	 	 	 		 	   	  	 	 	 		 		 	 	 	  	 	 

          $menu[]=array(
			'label'=>'<i class="fas fa-shopping-basket"></i>'.t("Checkout currency"),
			'url'=>array(Yii::app()->controller->id.'/checkout_currency')
		  );	 	  	 	 	 		 	   	  	 	 	 		 		 	 	 	  	 	 
	 	  	 	
	 	  $this->items = $menu;	 	  
	 	  	 	  
	 	  $this->encodeLabel = false;
	 	  $this->activeCssClass = "active";
	 	  $this->activateParents = true;
	 	  $this->htmlOptions = array(
	 	    'class'=>'user-menu'
	 	  ); 
	 	  $this->submenuHtmlOptions = array(
	 	    'class'=>'user-sub-menu'
	 	  ); 
	 	  
	 	  parent::init();
	 }
	 
}
/*end class*/