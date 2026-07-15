<?php
Yii::import('zii.widgets.CMenu', true);

class WidgetMerchantInfoMenu extends CMenu
{		 
	 public $merchant_id;
	 public $merchant_type;
	 public $self_delivery;
	 	
	 public function init()
	 {		 		 	 	 	
	 	  $menu = array();	
		  $access = [];		  
		  if($get_access = AdminUserIdentity::getRoleAccess()){
			 $access = $get_access;
		  }		  		  
		   
		  $addon_single = CommonUtility::getAddonStatus('DXpn3kxHj8oVc64YvsHDTm2n6srn87gmcA2ZqXhgxI3dZ0cvYHh6UE8YXZQW/Xr2Mzf7svb3dPWaqg==');
		  if(!$addon_single){
			$addon_single = CommonUtility::getAddonStatus('Jg4VxUMHOMb+LmLX6n25mae/LK56BekNPAwm/8UMuFWAbdME9MW2b7i9OScUzEo32xbOMFXt416romMxR7RZexk=');
		  }		  		
		  
		  $home_search_mode = isset(Yii::app()->params['settings']['home_search_mode'])?Yii::app()->params['settings']['home_search_mode']:'address';
		  $home_search_mode = !empty($home_search_mode)?$home_search_mode:'address';		  
		  
	 	  $menu[]=array(
	 	    'label'=>'<i class="zmdi zmdi-store"></i>'.t("Merchant information"),
	 	    'url'=>array("/".Yii::app()->controller->id."/edit",'id'=>$this->merchant_id),
			 'visible'=>AdminUserIdentity::CheckHasAccess($access,Yii::app()->controller->id.'.edit'),
	 	  );
	 	  $menu[]=array(	 	    
	 	    'label'=>'<i class="zmdi zmdi-account-circle"></i>'.t("Login information"),
	 	    'url'=>array("/".Yii::app()->controller->id."/login",'id'=>$this->merchant_id),
			 'visible'=>AdminUserIdentity::CheckHasAccess($access,Yii::app()->controller->id.'.login'),
	 	  );
	 	  
	 	  $menu[]=array(	 	    
	 	    'label'=>'<i class="zmdi zmdi-pin"></i>'.t("Address"),
	 	    'url'=>array("/".Yii::app()->controller->id."/address",'id'=>$this->merchant_id),
			 'visible'=>AdminUserIdentity::CheckHasAccess($access,Yii::app()->controller->id.'.address'),
	 	  );

		  if($home_search_mode=="location"):
			$menu[]=array(	 	    
				'label'=>'<i class="zmdi zmdi-pin"></i>'.t("Location Management"),
				'url'=>array("/".Yii::app()->controller->id."/location",'id'=>$this->merchant_id),
				'visible'=>AdminUserIdentity::CheckHasAccess($access,Yii::app()->controller->id.'.location'),
			 );
		  endif;
	 	  
	 	  $menu[]=array(	 	    
	 	    'label'=>'<i class="zmdi zmdi-pin-drop"></i>'.t("Zone"),
	 	    'url'=>array("/".Yii::app()->controller->id."/zone",'id'=>$this->merchant_id),
			 'visible'=>AdminUserIdentity::CheckHasAccess($access,Yii::app()->controller->id.'.zone'),
	 	  );
	 	  
	 	  $menu[]=array(	 	    
	 	    'label'=>'<i class="zmdi zmdi-cutlery"></i>'.t("Merchant Type"),
	 	    'url'=>array("/".Yii::app()->controller->id."/membership",'id'=>$this->merchant_id),
			 'visible'=>AdminUserIdentity::CheckHasAccess($access,Yii::app()->controller->id.'.membership'),
	 	  );
	 	  $menu[]=array(	 	    
	 	    'label'=>'<i class="zmdi zmdi-star-outline"></i>'.t("Featured"),
	 	    'url'=>array("/".Yii::app()->controller->id."/featured",'id'=>$this->merchant_id),
			 'visible'=>AdminUserIdentity::CheckHasAccess($access,Yii::app()->controller->id.'.featured'),
	 	  );

	 	//   $menu[]=array(	 	    
	 	//     'label'=>'<i class="zmdi zmdi-tv-list"></i>'.t("Payment History"),
	 	//     'url'=>array("/".Yii::app()->controller->id."/payment_history",'id'=>$this->merchant_id),
		// 	 'visible'=>AdminUserIdentity::CheckHasAccess($access,Yii::app()->controller->id.'.payment_history'),
	 	//   );
		   
		   if($this->merchant_type==1):
			$menu[]=array(	 	    			
			'label'=>'<i class="zmdi zmdi-tv-list"></i>'.t("Subscriptions"),
				'url'=>array("/".Yii::app()->controller->id."/subscriptions",'id'=>$this->merchant_id),
				'visible'=>AdminUserIdentity::CheckHasAccess($access,Yii::app()->controller->id.'.subscriptions'),
			);
		   endif;

	 	  $menu[]=array(	 	    
	 	    'label'=>'<i class="zmdi zmdi-money"></i>'.t("Payment Settings"),
	 	    'url'=>array("/".Yii::app()->controller->id."/payment_settings",'id'=>$this->merchant_id),
			 'visible'=>AdminUserIdentity::CheckHasAccess($access,Yii::app()->controller->id.'.payment_settings'),
	 	  );

		  $menu[]=array(	 	    
			'label'=>'<i class="zmdi zmdi-lock-outline"></i>'.t("Access Settings"),
			'url'=>array("/".Yii::app()->controller->id."/access_settings",'id'=>$this->merchant_id),			
			'visible'=>AdminUserIdentity::CheckHasAccess($access,Yii::app()->controller->id.'.access_settings'),
		  );
	 	  
	 	  $menu[]=array(	 	    
	 	    'label'=>'<i class="zmdi zmdi-settings-square"></i>'.t("Settings"),
	 	    'url'=>array("/".Yii::app()->controller->id."/others",'id'=>$this->merchant_id),
			 'visible'=>AdminUserIdentity::CheckHasAccess($access,Yii::app()->controller->id.'.others'),
	 	  );
	 	  
		  if($addon_single):		  
		  $menu[]=array(	 	    
			'label'=>'<i class="zmdi zmdi-code-setting"></i>'.t("API Access"),
			'url'=>array("/".Yii::app()->controller->id."/api_access",'id'=>$this->merchant_id),
			'visible'=>AdminUserIdentity::CheckHasAccess($access,Yii::app()->controller->id.'.api_access'),
		  );
		  endif;

		
		  if($addon_single):
		  $menu[]=array(	 	    
			'label'=>'<i class="zmdi zmdi-settings"></i>'.t("Mobile Settings"),
			'url'=>array("/".Yii::app()->controller->id."/mobile_settings",'id'=>$this->merchant_id),
			'visible'=>AdminUserIdentity::CheckHasAccess($access,Yii::app()->controller->id.'.mobile_settings'),
		  );

		  $menu[]=array(	 	    
			'label'=>'<i class="zmdi zmdi-search"></i>'.t("Search Mode"),
			'url'=>array("/".Yii::app()->controller->id."/search_mode",'id'=>$this->merchant_id),
			'visible'=>AdminUserIdentity::CheckHasAccess($access,Yii::app()->controller->id.'.search_mode'),
		  );

		  $menu[]=array(	 	    
			'label'=>'<i class="zmdi zmdi-account-o"></i>'.t("Login & Signup"),
			'url'=>array("/".Yii::app()->controller->id."/login_sigup",'id'=>$this->merchant_id),
			'visible'=>AdminUserIdentity::CheckHasAccess($access,Yii::app()->controller->id.'.login_sigup'),
		  );

		  $menu[]=array(	 	    
			'label'=>'<i class="zmdi zmdi-phone"></i>'.t("Phone Settings"),
			'url'=>array("/".Yii::app()->controller->id."/phone_settings",'id'=>$this->merchant_id),
			'visible'=>AdminUserIdentity::CheckHasAccess($access,Yii::app()->controller->id.'.phone_settings'),
		  );

		  $menu[]=array(	 	    
			'label'=>'<i class="zmdi zmdi-facebook"></i>'.t("Social Settings"),
			'url'=>array("/".Yii::app()->controller->id."/social_settings",'id'=>$this->merchant_id),
			'visible'=>AdminUserIdentity::CheckHasAccess($access,Yii::app()->controller->id.'.social_settings'),
		  );

		  $menu[]=array(	 	    
			'label'=>'<i class="zmdi zmdi-google"></i>'.t("Google Recaptcha"),
			'url'=>array("/".Yii::app()->controller->id."/recaptcha_settings",'id'=>$this->merchant_id),
			'visible'=>AdminUserIdentity::CheckHasAccess($access,Yii::app()->controller->id.'.recaptcha_settings'),
		  );

		  $menu[]=array(	 	    
			'label'=>'<i class="zmdi zmdi-map"></i>'.t("Map API Keys"),
			'url'=>array("/".Yii::app()->controller->id."/map_keys",'id'=>$this->merchant_id),
			'visible'=>AdminUserIdentity::CheckHasAccess($access,Yii::app()->controller->id.'.map_keys'),
		  );

		  $menu[]=array(	 	    
			'label'=>'<i class="zmdi zmdi-comment-more"></i>'.t("Push notifications"),
			'url'=>array("/".Yii::app()->controller->id."/push_notifications",'id'=>$this->merchant_id),
			'visible'=>AdminUserIdentity::CheckHasAccess($access,Yii::app()->controller->id.'.map_keys'),
		  );
		  endif;

		  
	 	  $this->items = $menu;	 	  
	 	  	 	  
	 	  $this->encodeLabel = false;
	 	  $this->activeCssClass = "active";
	 	  $this->activateParents = true;
	 	  $this->htmlOptions = array(
	 	    'class'=>'attributes-menu'
	 	  ); 
	 	  $this->submenuHtmlOptions = array(
	 	    'class'=>'attributes-sub-menu'
	 	  ); 
	 	  
	 	  parent::init();
	 }
	 
}
/*end class*/