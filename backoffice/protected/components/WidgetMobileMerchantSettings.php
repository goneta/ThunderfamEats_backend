<?php
Yii::import('zii.widgets.CMenu', true);

class WidgetMobileMerchantSettings extends CMenu
{		 
	 public static $ctr = array();
	 
	 public function init()
	 {		 		 	  
	 	  	 	  	 	  
	 	  $menu = array();
		   

		  $menu[]=array(
			'label'=>'<i class="zmdi zmdi-code-setting"></i>'.t("API Access"),
			'url'=>array('mobilemerchant/api_access')
		  );	 	  	 	 	 	

          $menu[]=array(
			'label'=>'<i class="zmdi zmdi-settings"></i>'.t("Settings"),
			'url'=>array('mobilemerchant/settings')
		  );	 	  	 	 	 	
          
		  $menu[]=array(
			'label'=>'<i class="zmdi zmdi-comment-more"></i>'.t("Push notifications"),
			'url'=>array('mobilemerchant/push_notifications')
		  );	 	  	 	 	 	
	 	  
		   $menu[]=array(
			'label'=>'<i class="zmdi zmdi-search-in-page"></i>'.t("Pages"),
			'url'=>array('mobilemerchant/settings_page'),
			'itemOptions'=>array(
			  'class'=>"settings-tabs"
			)
		  );	 	  	 	 	 		 		 	 	 	  	 	 

		//   $menu[]=array(
		// 	'label'=>'<i class="zmdi zmdi-wrench"></i>'.t("Cron jobs"),
		// 	'url'=>array('mobilemerchant/cronjobs'),
		// 	'itemOptions'=>array(
		// 	  'class'=>"settings-cronjobs"
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