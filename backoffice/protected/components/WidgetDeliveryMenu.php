<?php
Yii::import('zii.widgets.CMenu', true);

class WidgetDeliveryMenu extends CMenu
{		 
	 public static $charge_type ;
	 
	 public function init()
	 {		 		 	  
	 	  
	 	  $menu = array();
	 	  $menu[]=array(
	 	    'label'=>'<i class="zmdi zmdi-settings"></i>'.t("Settings"),
	 	    'url'=>array("/services/delivery_settings")
	 	  );	 	  	 	 	 	
	 	  	 	  
		  $menu[]=array(
		   'label'=>'<i class="zmdi zmdi-time-countdown"></i>'.t("Fixed Charge"),
		   'url'=>array("/services/fixed_charge"),
		   'itemOptions'=>array(
			 'class'=>"fixed_charge"
		    )
		  );	 	  	 	 	 	
	 	  	 	  
		  $menu[]=array(
			'label'=>'<i class="zmdi zmdi-time-countdown"></i>'.t("Dynamic Rates"),
			'url'=>array("/services/charges_table"),
			'itemOptions'=>array(
				'class'=>"services_charges_table"
			)
		  );	 	  
	 	  	 	
	 	  $this->items = $menu;	 	  
	 	  	 	  
	 	  $this->encodeLabel = false;
	 	  $this->activeCssClass = "active";
	 	  $this->activateParents = true;
	 	  $this->htmlOptions = array(
	 	    'class'=>'services-menu'
	 	  ); 
	 	  $this->submenuHtmlOptions = array(
	 	    'class'=>'services-sub-menu'
	 	  ); 
	 	  
	 	  parent::init();
	 }
	 
}
/*end class*/