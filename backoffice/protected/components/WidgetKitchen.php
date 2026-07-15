<?php
Yii::import('zii.widgets.CMenu', true);

class WidgetKitchen extends CMenu
{		 
	 public static $ctr = array();
	 
	 public function init()
	 {		 		 	  
	 	  	 	  	 	  
	 	  $menu = array();
		   
		   $menu[]=array(
			'label'=>'<i class="zmdi zmdi-settings"></i>'.t("Settings"),
			'url'=>array('kitchen/settings')
		  );	 	

		  $menu[]=array(
			'label'=>'<i class="zmdi zmdi-code-setting"></i>'.t("API Access"),
			'url'=>array('kitchen/api_access')
		  );	 	  	 	 	 				 	 	 	

		  $menu[]=array(
			'label'=>'<i class="zmdi zmdi-comment-more"></i>'.t("Push notifications"),
			'url'=>array('kitchen/push_notifications')
		  );	 	  	 	 	 				 	 	 	

		  $menu[]=array(
			'label'=>'<i class="zmdi zmdi-search-in-page"></i>'.t("Page"),
			'url'=>array('kitchen/settings_page')
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