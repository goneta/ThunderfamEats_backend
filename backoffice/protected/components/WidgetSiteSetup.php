<?php
Yii::import('zii.widgets.CMenu', true);

class WidgetSiteSetup extends CMenu
{		 
	 public function init()
	 {		 		 	  		  
		  $access = [];		  
		  if($get_access = AdminUserIdentity::getRoleAccess()){
			 $access = $get_access;
		  }		  		  

		  $location_addon = CommonUtility::getAddonStatus(Yii::app()->params['location_addon_identity']);
		  $home_search_mode = isset(Yii::app()->params['settings']['home_search_mode'])?Yii::app()->params['settings']['home_search_mode']:'address';
		  $home_search_mode = !empty($home_search_mode)?$home_search_mode:'address';		  
		  

	 	  $menu = array();
	 	  $menu[]=array(
	 	    'label'=>'<i class="zmdi zmdi-home"></i>'.t("Site information"),
	 	    'url'=>array("/admin/site_information"),
			'visible'=>AdminUserIdentity::CheckHasAccess($access,'admin.site_information'),
	 	  );	 	  
	 	  $menu[]=array(	 	    
	 	    'label'=>'<i class="zmdi zmdi-map"></i>'.t("Map API Keys"),
	 	    'url'=>array("/admin/map_keys"),			
			 'visible'=>AdminUserIdentity::CheckHasAccess($access,'admin.map_keys'),
	 	  );	 	
	 	  $menu[]=array(	 	    
	 	    'label'=>'<i class="zmdi zmdi-google"></i>'.t("Google Recaptcha"),
	 	    'url'=>array("/admin/recaptcha"),
			 'visible'=>AdminUserIdentity::CheckHasAccess($access,'admin.recaptcha'),
	 	  );
	 	  $menu[]=array(	 	    
	 	    'label'=>'<i class="zmdi zmdi-search"></i>'.t("Search Mode"),
	 	    'url'=>array("/admin/search_settings"),
			 'visible'=>AdminUserIdentity::CheckHasAccess($access,'admin.search_settings'),
	 	  );
		  
		  if($home_search_mode=="location" && $location_addon):
				$menu[]=array(	 	    
						'label'=>'<i class="zmdi zmdi-car"></i>'.t("Delivery Fee Management"),
						'url'=>array("/admin/delivery_management"),
						'visible'=>AdminUserIdentity::CheckHasAccess($access,'admin.delivery_fee_management'),
				);
					
				$menu[]=array(	 	    
					'label'=>'<i class="zmdi zmdi-time"></i>'.t("Time Estimates Management"),
					'url'=>array("/admin/estimate_management"),
					'visible'=>AdminUserIdentity::CheckHasAccess($access,'admin.estimate_management'),
				);
				
		  endif;

		  $menu[]=array(	 	    
			'label'=>'<i class="fas fa-comment-dollar"></i>'.t("Fee Management"),
			'url'=>array("/admin/fee_management"),
			'visible'=>AdminUserIdentity::CheckHasAccess($access,'admin.fee_management'),
		  );

	 	  $menu[]=array(	 	    
	 	    'label'=>'<i class="zmdi zmdi-account-o"></i>'.t("Login & Signup"),
	 	    'url'=>array("/admin/login_sigup"),
			 'visible'=>AdminUserIdentity::CheckHasAccess($access,'admin.login_sigup'),
	 	  );

		   $menu[]=array(	 	    
			'label'=>'<i class="zmdi zmdi-border-outer"></i>'.t("Custom Fields"),
			'url'=>array("/admin/custom_fields"),
			'visible'=>AdminUserIdentity::CheckHasAccess($access,'admin.custom_fields'),
		  );

	 	  $menu[]=array(	 	    
	 	    'label'=>'<i class="zmdi zmdi-phone"></i>'.t("Phone Settings"),
	 	    'url'=>array("/admin/phone_settings"),
			 'visible'=>AdminUserIdentity::CheckHasAccess($access,'admin.phone_settings'),
	 	  );
	 	  $menu[]=array(	 	    
	 	    'label'=>'<i class="zmdi zmdi-facebook"></i>'.t("Social Settings"),
	 	    'url'=>array("/admin/social_settings"),
			 'visible'=>AdminUserIdentity::CheckHasAccess($access,'admin.social_settings'),
	 	  );
	 	  $menu[]=array(	 	    
	 	    'label'=>'<i class="zmdi zmdi-print"></i>'.t("Printing Settings"),
	 	    'url'=>array("/admin/printing"),
			 'visible'=>AdminUserIdentity::CheckHasAccess($access,'admin.printing'),
	 	  );	 	  	 	  
	 	  $menu[]=array(	 	    
	 	    'label'=>'<i class="zmdi zmdi-star-half"></i>'.t("Reviews"),
	 	    'url'=>array("/admin/reviews"),
			 'visible'=>AdminUserIdentity::CheckHasAccess($access,'admin.reviews'),
	 	  );	 	  
	 	  $menu[]=array(	 	    
	 	    'label'=>'<i class="zmdi zmdi-calendar-check"></i>'.t("Timezone"),
	 	    'url'=>array("/admin/timezone"),
			 'visible'=>AdminUserIdentity::CheckHasAccess($access,'admin.timezone'),
	 	  );
	 	  $menu[]=array(	 	    
	 	    'label'=>'<i class="zmdi zmdi-shopping-basket"></i>'.t("Ordering & Food"),
	 	    'url'=>array("/admin/ordering"),
			 'visible'=>AdminUserIdentity::CheckHasAccess($access,'admin.ordering'),
	 	  );	 	  	 	  
		  $menu[]=array(	 	    
	 	    'label'=>'<i class="zmdi zmdi-time"></i>'.t("Automated Status Updates"),
	 	    'url'=>array("/admin/automatedstatus"),
			 'visible'=>AdminUserIdentity::CheckHasAccess($access,'admin.automatedstatus'),
	 	  );	 	  	 	  
	 	  $menu[]=array(	 	    
	 	    'label'=>'<i class="zmdi zmdi-sign-in"></i>'.t("Merchant Registration"),
	 	    'url'=>array("/admin/merchant_registration"),
			 'visible'=>AdminUserIdentity::CheckHasAccess($access,'admin.merchant_registration'),
	 	  );	 	  	 	  
	 	  $menu[]=array(	 	    
	 	    'label'=>'<i class="zmdi zmdi-notifications-add"></i>'.t("Notifications"),
	 	    'url'=>array("/admin/notifications"),
			 'visible'=>AdminUserIdentity::CheckHasAccess($access,'admin.notifications'),
	 	  );	 	  
	 	  $menu[]=array(	 	    
	 	    'label'=>'<i class="zmdi zmdi-phone-in-talk"></i>'.t("Contact Settings"),
	 	    'url'=>array("/admin/contact_settings"),
			 'visible'=>AdminUserIdentity::CheckHasAccess($access,'admin.contact_settings'),
	 	  );
	 	  
	 	  $menu[]=array(	 	    
	 	    'label'=>'<i class="zmdi zmdi-chart"></i>'.t("Analytics"),
	 	    'url'=>array("/admin/analytics_settings"),
			 'visible'=>AdminUserIdentity::CheckHasAccess($access,'admin.analytics_settings'),
	 	  );

		  $menu[]=array(	 	    
			'label'=>'<i class="zmdi zmdi-code-setting"></i>'.t("API Access"),
			'url'=>array("/admin/api_access"),
			'visible'=>AdminUserIdentity::CheckHasAccess($access,'admin.api_access'),
		  );

		  $menu[]=array(	 	    
			'label'=>'<i class="zmdi zmdi-view-web"></i>'.t("Mobile Page"),
			'url'=>array("/admin/mobilepage"),
			'visible'=>AdminUserIdentity::CheckHasAccess($access,'admin.mobilepage'),
		  );

		  $menu[]=array(	 	    
			'label'=>'<i class="zmdi zmdi-settings"></i>'.t("Mobile Settings"),
			'url'=>array("/admin/mobile_settings"),
			'visible'=>AdminUserIdentity::CheckHasAccess($access,'admin.mobile_settings'),
		  );

		  $menu[]=array(	 	    
			'label'=>'<i class="zmdi zmdi-comment-more"></i>'.t("Push notifications"),
			'url'=>array("/admin/push_notifications"),
			'visible'=>AdminUserIdentity::CheckHasAccess($access,'admin.push_notifications'),
		  );

		  $menu[]=array(	 	    
			'label'=>'<i class="zmdi zmdi-group"></i>'.t("GDPR cookie consent"),
			'url'=>array("/admin/cookie_consent"),
			'visible'=>AdminUserIdentity::CheckHasAccess($access,'admin.cookie_consent'),
		  );
	 	  
		//   $menu[]=array(	 	    
		// 	'label'=>'<i class="zmdi zmdi-settings-square"></i>'.t("Cron Jobs"),
		// 	'url'=>array("/admin/cronjobs"),
		// 	'visible'=>AdminUserIdentity::CheckHasAccess($access,'admin.site_others'),
		//   );

	 	  $menu[]=array(	 	    
	 	    'label'=>'<i class="zmdi zmdi-more"></i>'.t("Others"),
	 	    'url'=>array("/admin/site_others"),
			 'visible'=>AdminUserIdentity::CheckHasAccess($access,'admin.site_others'),
	 	  );		 
	 	 
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