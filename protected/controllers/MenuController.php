<?php
class MenuController extends SiteCommon
{
	public function beforeAction($action)
	{						
		
		// CHECK IF PWA IS ENABLED AND REDIRECT
		$enabled_pwa = isset(Yii::app()->params['settings']['enabled_auto_pwa_redirect'])?Yii::app()->params['settings']['enabled_auto_pwa_redirect']:'';				
		$pwa_url = isset(Yii::app()->params['settings']['pwa_url'])?Yii::app()->params['settings']['pwa_url']:'';		

		$slug_name = '';
		$pathInfo = Yii::app()->request->getPathInfo();		
		$matches = explode('/', $pathInfo);			
		if(is_array($matches) && count($matches)>=1){
			$slug_name = isset($matches[0])?$matches[0]:''; 
			$pwa_url.="/#/$slug_name";
		}	
		if($enabled_pwa==1 && !empty($pwa_url) && Yii::app()->params['isMobile']==1 ){						
			$this->redirect($pwa_url);
			Yii::app()->end();
		}				

		// CHECK MAINTENANCE MODE
		$this->maintenanceMode();

		return true;
	}
			
	public function actionMenu()
	{					
				
		try {
			
			AssetsFrontBundle::includeMaps();
			$pathInfo = Yii::app()->request->getPathInfo();		
			$matches = explode('/', $pathInfo);		
			$table = Yii::app()->request->getQuery('table');
			if(is_array($matches) && count($matches)>=1){
				$slug_name = isset($matches[0])?$matches[0]:''; 
							
				$data = CMerchantListingV1::getMerchantInfo($slug_name,Yii::app()->language);								
				Yii::app()->params['seo_data'] = [
					'restaurant_name'=>$data['restaurant_name']
				];		
													
				$merchant_id = intval($data['merchant_id']);
				$merchant_uuid = trim($data['merchant_uuid']);
				$gallery = CMerchantListingV1::getGallery($merchant_id);						
				$opening_hours = CMerchantListingV1::openingHours($merchant_id);		

				// SET SEO
				try {
					$seo_page =CMerchants::getSEO($merchant_id,Yii::app()->language);
					$seo_data = isset(Yii::app()->params['seo_data'])?Yii::app()->params['seo_data']:false;
					$seo_data = CommonUtility::toLanguageParameters($seo_data,"{{","}}");                        							
					$seo_page->meta_title = t($seo_page->meta_title,$seo_data);
					$seo_page->meta_description = t($seo_page->meta_description,$seo_data);
					$seo_page->meta_keywords = t($seo_page->meta_keywords,$seo_data);
					CommonUtility::setSEO($seo_page->meta_title,$seo_page->meta_title, $seo_page->meta_description,$seo_page->meta_keywords , $seo_page->image);				
				} catch (Exception $e) {
					CSeo::setPage();
				}
				
				
				$open_start=''; $open_end='';
				$today = strtolower(date("l")); 
				if(is_array($opening_hours) && count($opening_hours)>=1){
					foreach ($opening_hours as $items) {
					   if($items['day']==$today){
						  $open_start = Date_Formatter::Time($items['start_time']);
						  $open_end = Date_Formatter::Time($items['end_time']);
					   }
					}
				}        		
				
				$setttings = Yii::app()->params['settings'];
				$home_search_mode = isset($setttings['home_search_mode'])?$setttings['home_search_mode']:'address';		
		        $home_search_mode = !empty($home_search_mode)?$home_search_mode:'address';
								
				// GET DISTANCE
				$place_id = CommonUtility::getCookie(Yii::app()->params->local_id);				
				$distance = 0;
				$unit = isset($data['distance_unit'])?$data['distance_unit']:'mi';

				if($home_search_mode=="address"){
					try {			
						$place_data = CMaps::locationDetails($place_id,'');					
						$distance = CMaps::getLocalDistance($data['distance_unit'],$place_data['latitude'],$place_data['longitude'],
						$data['latitude'],$data['lontitude']);			
					} catch (Exception $e) {}				
				}				
										
				$static_maps=''; $map_direction='';
				if($data){			  					
				   $static_maps = CMerchantListingV1::staticMapLocation(
				     Yii::app()->params['map_credentials'],
				     $data['latitude'],$data['lontitude'],
				     '500x200',websiteDomain().Yii::app()->theme->baseUrl."/assets/images/marker2@2x.png"
				   );		
				   $map_direction = CMerchantListingV1::mapDirection(Yii::app()->params['map_credentials'],
				     $data['latitude'],$data['lontitude']
				   );	   					  
				}
							
							
				$payload = array(
				  'items','subtotal','distance_local_new','merchant_info','items_count','delivery_fee'
				);			
				
				ScriptUtility::registerScript(array(
				  "var merchant_id='".CJavaScript::quote($merchant_id)."';",		
				  "var merchant_uuid='".CJavaScript::quote($merchant_uuid)."';",		
				  "var payload='".CJavaScript::quote(json_encode($payload))."';",
				  "var isGuest='".CJavaScript::quote(Yii::app()->user->isGuest)."';",						  
				  "var table_uuid='".CJavaScript::quote($table)."';",						  
				),'merchant_id');

				CBooking::setIdentityToken();
				
				$checkout_link = Yii::app()->createUrl("account/login?redirect=". Yii::app()->createAbsoluteUrl("/account/checkout") );
				if(!Yii::app()->user->isGuest){
					$checkout_link = Yii::app()->createUrl("/account/checkout");
				}
				if($table !== ""){
					$checkout_link = Yii::app()->createUrl("account/login?redirect=". Yii::app()->createAbsoluteUrl("/account/checkout_qr?table_uuid=" . $table) );
					if(!Yii::app()->user->isGuest){
						$checkout_link = Yii::app()->createUrl("/account/checkout_qr?table_uuid=" . $table);
					}
				}
				if($data['has_header']){
					Yii::app()->clientScript->registerCss('headerCSS', '
						.top-merchant-details,
						.merchant-top-header .right-info
						{
							background: url("'.$data['url_header'].'") no-repeat center center #fedc79;
							background-size:cover;
						}
					');
				}

				// BOOKING
				$options = OptionsTools::find([
					'booking_enabled','booking_enabled_capcha','menu_layout','merchant_tax_number',
					'facebook_page','twitter_page','google_page','instagram_page','merchant_enabled_age_verification','merchant_extenal'
				],$merchant_id);												
				$data['facebook_page'] = isset($options['facebook_page'])?$options['facebook_page']:'';
				$data['twitter_page'] = isset($options['twitter_page'])?$options['twitter_page']:'';
				$data['google_page'] = isset($options['google_page'])?$options['google_page']:'';
				$data['instagram_page'] = isset($options['instagram_page'])?$options['instagram_page']:'';				
				$data['age_verifications'] = isset($options['merchant_enabled_age_verification'])?$options['merchant_enabled_age_verification']:false;				
				$data['merchant_extenal'] = isset($options['merchant_extenal'])?$options['merchant_extenal']:'';	

				$booking_enabled = isset($options['booking_enabled'])?$options['booking_enabled']:false;
				$booking_enabled = $booking_enabled==1?true:false;
				$booking_enabled_capcha = isset($options['booking_enabled_capcha'])?$options['booking_enabled_capcha']:false;
				$booking_enabled_capcha = $booking_enabled_capcha==1?true:false;
				$menu_layout = isset(Yii::app()->params['settings']['menu_layout'])?Yii::app()->params['settings']['menu_layout']:'left_image';
				$menu_layout = !empty($menu_layout)?$menu_layout:'left_image';
				$category_position = isset(Yii::app()->params['settings']['category_position'])?Yii::app()->params['settings']['category_position']:'left';
				$category_position = !empty($category_position)?$category_position:'left';				
				
		        $tax_number = isset($options['merchant_tax_number'])?$options['merchant_tax_number']:'';				
				
				$options = OptionsTools::find(['captcha_site_key','menu_disabled_inline_addtocart','enabled_review']);				
				$captcha_site_key = isset($options['captcha_site_key'])?$options['captcha_site_key']:'';
				$disabled_inline_addtocart = isset($options['menu_disabled_inline_addtocart'])?$options['menu_disabled_inline_addtocart']:false;
				$disabled_inline_addtocart = $disabled_inline_addtocart==1?true:false;
				
				$enabled_review = isset($options['enabled_review'])?$options['enabled_review']:'';
				$enabled_review = $enabled_review==1?true:false;				
				
				$maps_config = CMaps::config();	

				$tpl = $category_position=='top'? "//store/menu_categorytop" : "//store/menu";		
				if($table){
					$tpl = "//store/menu-qr";
				}									
				if($data){		   								
			    	$this->render($tpl,array(
			    	  'data'=>$data,
			    	  'gallery'=>$gallery,
			    	  'opening_hours'=>$opening_hours,
			    	  'static_maps'=>$static_maps,
			    	  'map_direction'=>$map_direction,
			    	  'checkout_link'=>$checkout_link,
					  'booking_enabled'=>$booking_enabled,
					  'merchant_uuid'=>$merchant_uuid,
					  'booking_enabled_capcha'=>$booking_enabled_capcha,
					  'captcha_site_key'=>$captcha_site_key,
					  'menu_layout'=>$menu_layout,
					  'tax_number'=>$tax_number,
					  'maps_config'=>$maps_config,
					  'disabled_inline_addtocart'=>$disabled_inline_addtocart,
					  'open_start'=>$open_start,
					  'open_end'=>$open_end,
					  'distance'=>[
						'value'=>$distance,
						'label'=>t("{{distance} {{unit}} away",[
							'{{distance}'=>$distance,
							'{{unit}}'=>MapSdk::prettyUnit($unit)
						])
					  ],		
					  'enabled_review'=>$enabled_review,
					  'home_search_mode'=>$home_search_mode
			    	));
			    }
			} else $this->render("//store/404-page");		
			
		} catch (Exception $e) {
			$this->render("//store/404-page");		
		}
	}
	
}
/*end class*/