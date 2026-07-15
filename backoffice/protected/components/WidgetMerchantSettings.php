<?php
Yii::import('zii.widgets.CMenu', true);

class WidgetMerchantSettings extends CMenu
{		 	
	 public function init()
	 {		 		 	  

		  $options = OptionsTools::find(['self_delivery'],Yii::app()->merchant->merchant_id);
		  $self_delivery = isset($options['self_delivery'])?$options['self_delivery']:false;
		  $self_delivery = $self_delivery==1?true:false;
	 	  	 	  
	 	  $menu = array();
		  $addon_single = CommonUtility::getAddonStatus('DXpn3kxHj8oVc64YvsHDTm2n6srn87gmcA2ZqXhgxI3dZ0cvYHh6UE8YXZQW/Xr2Mzf7svb3dPWaqg==');
		  if(!$addon_single){
			$addon_single = CommonUtility::getAddonStatus('Jg4VxUMHOMb+LmLX6n25mae/LK56BekNPAwm/8UMuFWAbdME9MW2b7i9OScUzEo32xbOMFXt416romMxR7RZexk=');
		  }

		  $access = [];		  
		  if($get_access = UserIdentityMerchant::getRoleAccess()){
			 $access = $get_access;
		  }		  		
		  		  
		  $location_addon = CommonUtility::getAddonStatus(Yii::app()->params['location_addon_identity']);
		  $home_search_mode = isset(Yii::app()->params['settings']['home_search_mode'])?Yii::app()->params['settings']['home_search_mode']:'address';
		  $home_search_mode = !empty($home_search_mode)?$home_search_mode:'address';		  
		  

	 	  $menu[]=array(
	 	    'label'=>'<i class="zmdi zmdi-settings"></i>'.t("Basic Settings"),
	 	    'url'=>array("/merchant/settings"),
			'visible'=>UserIdentityMerchant::CheckHasAccess($access,'merchant.settings'),
	 	  );	 	  	 	 	 	

		   $menu[]=array(
			'label'=>'<i class="zmdi zmdi-calendar-check"></i>'.t("Time Zone"),
			'url'=>array("/merchant/timezone"),
			'itemOptions'=>array(
			  'class'=>"time-zone"
		   ),
		   'visible'=>UserIdentityMerchant::CheckHasAccess($access,'merchant.timezone'),
		  );	 	  	 	 	 	
	 	  
	 	  $menu[]=array(
	 	    'label'=>'<i class="zmdi zmdi-time"></i>'.t("Store Hours"),
	 	    'url'=>array("/merchant/store_hours"),
	 	    'itemOptions'=>array(
	 	      'class'=>"store-hours"
			),
			'visible'=>UserIdentityMerchant::CheckHasAccess($access,'merchant.store_hours'),
	 	  );	 	  	 	 	 	
	 	  
	 	$menu[]=array(
	 	    'label'=>'<i class="zmdi zmdi-balance"></i>'.t("Taxes"),
	 	    'url'=>array("/merchant/taxes"),
	 	    'itemOptions'=>array(
	 	      'class'=>"taxes"
			),
			'visible'=>UserIdentityMerchant::CheckHasAccess($access,'merchant.taxes'),
	 	);

		 $menu[]=array(
			'label'=>'<i class="zmdi zmdi-view-web"></i>'.t("SEO"),
			'url'=>array("/merchant/seo"),
			'itemOptions'=>array(
			  'class'=>"seo"
		   ),
		   'visible'=>UserIdentityMerchant::CheckHasAccess($access,'merchant.seo'),
		);

		$menu[]=array(
			'label'=>'<i class="fas fa-utensil-spoon"></i>'.t("Kitchen Workload"),
			'url'=>array("/merchant/kitchen_settings"),
			'itemOptions'=>array(
			  'class'=>"kitchen_settings"
		   ),
		   'visible'=>UserIdentityMerchant::CheckHasAccess($access,'merchant.kitchen_settings'),
		);
				 
		 if($self_delivery && $home_search_mode=="location" && $location_addon):
			$menu[]=array(
				'label'=>'<i class="zmdi zmdi-pin"></i>'.t("Location Management"),
				'url'=>array("/merchant/location_management"),
				'itemOptions'=>array(
				'class'=>"location-management"
				),
				'visible'=>UserIdentityMerchant::CheckHasAccess($access,'merchant.zone'),
			);	 	  	
			$menu[]=array(
				'label'=>'<i class="zmdi zmdi-car"></i>'.t("Delivery Management"),
				'url'=>array("/merchant/delivery_management"),
				'itemOptions'=>array(
				'class'=>"delivery-management"
				),
				'visible'=>UserIdentityMerchant::CheckHasAccess($access,'merchant.delivery_management'),
			);	 	  	
			$menu[]=array(
				'label'=>'<i class="zmdi zmdi-time"></i>'.t("Time Estimates Management"),
				'url'=>array("/merchant/estimate_management"),
				'itemOptions'=>array(
				'class'=>"estimate-management"
				),
				'visible'=>UserIdentityMerchant::CheckHasAccess($access,'merchant.estimate_management'),
			);	 	  	
	     endif;

		 if($self_delivery):
		 $menu[]=array(
			'label'=>'<i class="zmdi zmdi-pin-drop"></i>'.t("Zone"),
			'url'=>array("/merchant/zone_settings"),
			'itemOptions'=>array(
			'class'=>"zone-settings"
			),
			'visible'=>UserIdentityMerchant::CheckHasAccess($access,'merchant.zone'),
		  );	 	  	
		  endif;

		  if($addon_single):
			$menu[]=array(
				'label'=>'<i class="zmdi zmdi-search"></i>'.t("Search Mode"),
				'url'=>array("/merchant/search_settings"),
				'itemOptions'=>array(
				'class'=>"search-settings"
				),
				'visible'=>UserIdentityMerchant::CheckHasAccess($access,'merchant.search_settings'),
			);	 	  	 	 	 	
	

			$menu[]=array(
				'label'=>'<i class="zmdi zmdi-account-o"></i>'.t("Login & Signup"),
				'url'=>array("/merchant/login_sigup"),
				'itemOptions'=>array(
				'class'=>"login-sigup"
				),
				'visible'=>UserIdentityMerchant::CheckHasAccess($access,'merchant.login_sigup'),
			);	 	  	 	 	 	

			$menu[]=array(
				'label'=>'<i class="zmdi zmdi-phone"></i>'.t("Phone Settings"),
				'url'=>array("/merchant/phone_settings"),
				'itemOptions'=>array(
				'class'=>"phone-settings"
				),
				'visible'=>UserIdentityMerchant::CheckHasAccess($access,'merchant.phone_settings'),
			);	 	  	 	 
	 	  
	 	  $menu[]=array(
	 	    'label'=>'<i class="zmdi zmdi-facebook"></i>'.t("Social Settings"),
	 	    'url'=>array("/merchant/social_settings"),
	 	    'itemOptions'=>array(
	 	      'class'=>"social-settings"
			),
			'visible'=>UserIdentityMerchant::CheckHasAccess($access,'merchant.social_settings'),
	 	  );	 	  
		   
			$menu[]=array(
				'label'=>'<i class="zmdi zmdi-google"></i>'.t("Google Recaptcha"),
				'url'=>array("/merchant/recaptcha_settings"),
				'itemOptions'=>array(
				'class'=>"recaptcha-settings"
				),
				'visible'=>UserIdentityMerchant::CheckHasAccess($access,'merchant.recaptcha_settings'),
			);	

			$menu[]=array(
				'label'=>'<i class="zmdi zmdi-map"></i>'.t("Map API Keys"),
				'url'=>array("/merchant/map_keys"),
				'itemOptions'=>array(
				'class'=>"map-keys"
				),
				'visible'=>UserIdentityMerchant::CheckHasAccess($access,'merchant.map_keys'),
			);	 	  	 	

		  endif;
		  	 			   
	 	  	 	  
		  $menu[]=array(
			'label'=>'<i class="zmdi zmdi-notifications-active"></i>'.t("Notification Settings"),
			'url'=>array("/merchant/notification_settings"),
			'itemOptions'=>array(
			  'class'=>"notification-settings"
			),
			'visible'=>UserIdentityMerchant::CheckHasAccess($access,'merchant.notification_settings'),
		  );	 	  	 	 	 	
		  

	 	  $menu[]=array(
	 	    'label'=>'<i class="zmdi zmdi-settings-square"></i>'.t("Orders Settings"),
	 	    'url'=>array("/merchant/orders_settings"),
	 	    'itemOptions'=>array(
	 	      'class'=>"orders-settings"
			),
			'visible'=>UserIdentityMerchant::CheckHasAccess($access,'merchant.orders_settings'),
	 	  );	 	  	 	 	 	
		  
		  $menu[]=array(
			'label'=>'<i class="zmdi zmdi-group"></i>'.t("Menu Options"),
			'url'=>array("/merchant/menu_options"),
			'itemOptions'=>array(
			'class'=>"menu-options"
			),
			'visible'=>UserIdentityMerchant::CheckHasAccess($access,'merchant.menu_options'),
		  ); 	 	  	 	
		  
          if($addon_single):			 	 	
			$menu[]=array(
				'label'=>'<i class="zmdi zmdi-view-web"></i>'.t("Mobile Page"),
				'url'=>array("/merchant/mobilepage"),
				'itemOptions'=>array(
				'class'=>"mobile-page"
				),
				'visible'=>UserIdentityMerchant::CheckHasAccess($access,'merchant.mobilepage'),
			);	 	
		  endif;
		
	 	  	 	
	 	  $this->items = $menu;	 	  
	 	  	 	  
	 	  $this->encodeLabel = false;
	 	  $this->activeCssClass = "active";
	 	  $this->activateParents = true;
	 	  $this->htmlOptions = array(
	 	    'class'=>'merchant-settings'
	 	  ); 
	 	  $this->submenuHtmlOptions = array(
	 	    'class'=>'user-sub-menu'
	 	  ); 
	 	  
	 	  parent::init();
	 }
	 
}
/*end class*/