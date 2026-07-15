<?php
Yii::import('zii.widgets.CMenu', true);

class WidgetDriverSettings extends CMenu
{		 
	 public static $ctr = array();
	 
	 public function init()
	 {		 		 	  
	 	  	 	  	 	  
	 	  $menu = array();

		   $menu[]=array(
			'label'=>'<i class="zmdi zmdi-settings"></i>'.t("Settings"),
			'url'=>array('driver/settings')
		  );	 	  	 	 	 	
		  
		  $menu[]=array(
			'label'=>'<i class="zmdi zmdi-code-setting"></i>'.t("API Access"),
			'url'=>array('driver/api_access')
		  );	 	  	 	 	 	

		  $menu[]=array(
			'label'=>'<i class="zmdi zmdi-comment-more"></i>'.t("Push notifications"),
			'url'=>array('driver/push_notifications')
		  );	 	  	 	 	 	

		  $menu[]=array(
			'label'=>'<i class="zmdi zmdi-code-setting"></i>'.t("Firebase Settings"),
			'url'=>array('driver/firebase_settings')
		  );	 	  	 	 	 	

		  $menu[]=array(
			'label'=>'<i class="zmdi zmdi-account-o"></i>'.t("Signup Settings"),
			'url'=>array('driver/signup_settings')
		  );	 	  	 	 	 	

		//   $menu[]=array(
		// 	'label'=>'<i class="zmdi zmdi-money-box"></i>'.t("Commission"),
		// 	'url'=>array('driver/commission')
		//   );

		  $menu[]=array(
			'label'=>'<i class="zmdi zmdi-settings-square"></i>'.t("Cashout settings"),
			'url'=>array('driver/withdrawal_settings')
		  );	 	  	 	 	 	

	 	  $menu[]=array(
	 	    'label'=>'<i class="zmdi zmdi-shopping-basket"></i>'.t("Order Status"),
	 	    'url'=>array('driver/order_status')
	 	  );	 	  	 	 	 	
	 	  
	 	  $menu[]=array(
	 	    'label'=>'<i class="zmdi zmdi-tab"></i>'.t("Order Tabs"),
	 	    'url'=>array('driver/settings_tabs'),
	 	    'itemOptions'=>array(
	 	      'class'=>"settings-tabs"
	 	    )
	 	  );	 	  	 	 	 		 		 	 	 	  	 	 

		   $menu[]=array(
			'label'=>'<i class="zmdi zmdi-search-in-page"></i>'.t("Pages"),
			'url'=>array('driver/settings_page'),
			'itemOptions'=>array(
			  'class'=>"settings-tabs"
			)
		  );	 	  	 	 	 		 		 	 	 	  	 	 

		//   $menu[]=array(
		// 	'label'=>'<i class="zmdi zmdi-wrench"></i>'.t("Cron jobs"),
		// 	'url'=>array('driver/cronjobs'),
		// 	'itemOptions'=>array(
		// 	  'class'=>"settings-cron"
		// 	)
		//   );	 	  	 	 	 		 		 	 	 	  	 	 
	 	  	 	
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