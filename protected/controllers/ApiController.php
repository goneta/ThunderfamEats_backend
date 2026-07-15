<?php
require 'intervention/vendor/autoload.php';
require 'php-jwt/vendor/autoload.php';
use Intervention\Image\ImageManager;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
                    
class ApiController extends SiteCommon
{
	public function beforeAction($action)
	{								
		$method = Yii::app()->getRequest()->getRequestType();
		if($method=="PUT"){
			$this->data = Yii::app()->input->xssClean(json_decode(file_get_contents('php://input'), true));
		} else $this->data = Yii::app()->input->xssClean($_POST);
		
		if(!Yii::app()->user->isGuest){
			if(!ACustomer::validateUserStatus()){						
				Yii::app()->user->logout(false);		
				return false;
			}
		}		
		
		return true;
	}
	
	public function actiongetlocation_autocomplete()
	{						
		try {
					   
		   $q = isset($this->data['q'])?$this->data['q']:'';
		   
		   if(!isset(Yii::app()->params['settings']['map_provider'])){
		   	   $this->msg = t("No default map provider, check your settings.");
		   	   $this->responseJson();
		   }
		   
		   MapSdk::$map_provider = Yii::app()->params['settings']['map_provider'];		   
		   MapSdk::setKeys(array(
		     'google.maps'=>Yii::app()->params['settings']['google_geo_api_key'],
		     'mapbox'=>Yii::app()->params['settings']['mapbox_access_token'],
		   ));
		   		   
		   if ( $country_params = AttributesTools::getSetSpecificCountry()){			   
		   	   MapSdk::setMapParameters(array(
		        'country'=>$country_params
		       ));
		   }		   
		     		  
		   $resp = MapSdk::findPlace($q);		   		   
		   $this->code =1; $this->msg = "ok";
		   $this->details = array(
		     'data'=>$resp
		   );		   
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		   
		}
		$this->responseJson();
	}
		
	public function actiongetLocationDetails()
	{				
		try {
			$address_uuid = '';
			$place_id = isset($this->data['id'])?trim($this->data['id']):'';			
			$place_description = isset($this->data['description'])?trim($this->data['description']):'';			
			$country_name = isset($this->data['country'])?trim($this->data['country']):'';			
			$country_code = isset($this->data['country_code'])?trim($this->data['country_code']):'';
			$saveplace = isset($this->data['saveplace'])?trim($this->data['saveplace']):1;
			$saveplace = $saveplace==1?true:false;			
			
			$autosaved_addres = isset($this->data['autosaved_addres'])?trim($this->data['autosaved_addres']):'';
			$autosaved_addres = $autosaved_addres=="true"?true:false;
			$auto_generate_uuid = isset($this->data['auto_generate_uuid'])?($this->data['auto_generate_uuid']):'';
			$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
			
			$address_format_use = isset(Yii::app()->params['settings']['address_format_use'])? (!empty(Yii::app()->params['settings']['address_format_use'])?Yii::app()->params['settings']['address_format_use']:'') :'';
			$address_format_use = !empty($address_format_use)?$address_format_use:1;
			
			$resp = CMaps::locationDetails($place_id, $country_code ,$country_name,$place_description);						
			if($address_format_use==2){				
				unset($resp['address']['address2']);
			}
						
			$resp_place_id = $resp['place_id'];
			$set_place_id = !empty($resp_place_id)?$resp_place_id:$place_id;
						
									
			if($saveplace){								
				CommonUtility::WriteCookie( Yii::app()->params->local_id , $set_place_id );	
			}			
						
			// if(!Yii::app()->user->isGuest){				
			// 	if($autosaved_addres){
			// 	  $address_uuid = CCheckout::saveDeliveryAddress($place_id , Yii::app()->user->id , $resp);
			// 	  $resp['address_uuid']=$address_uuid;				  
			// 	}
			// } 
			
			if($auto_generate_uuid===true || $auto_generate_uuid==="true"){
				$cart_uuid = !empty($cart_uuid)?$cart_uuid:CommonUtility::generateUIID();
				$trans_type = CServices::getSetService($cart_uuid);				
				CCart::savedAttributes($cart_uuid,Yii::app()->params->local_transtype,$trans_type);	
				$when = CCheckout::getWhenDeliver($cart_uuid);
		        CCart::savedAttributes($cart_uuid,'whento_deliver',$when);			  
		        CommonUtility::WriteCookie( "cart_uuid_local" ,$cart_uuid);		
			}
			
			$this->code =1; $this->msg = "ok";
			$this->details = array(
			  'data'=>$resp,		
			  'cart_uuid'=>$cart_uuid,
			);
							
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}
	
	public function actionreverseGeocoding()
	{		
		$lat = isset($this->data['lat'])?$this->data['lat']:'';
		$lng = isset($this->data['lng'])?$this->data['lng']:'';
		$next_steps = isset($this->data['next_steps'])?$this->data['next_steps']:'';
		
		$services = isset($this->data['services'])?$this->data['services']:'';		  
	    if(!empty($services)){
	   	  $services = substr($services,0,-1);
	    } else $services="all";
		
		try {
			
		   MapSdk::$map_provider = Yii::app()->params['settings']['map_provider'];		   
		   MapSdk::setKeys(array(
		     'google.maps'=>Yii::app()->params['settings']['google_geo_api_key'],
		     'mapbox'=>Yii::app()->params['settings']['mapbox_access_token'],
			 'yandex'=>isset(Yii::app()->params['settings']['yandex_geocoder_api'])?Yii::app()->params['settings']['yandex_geocoder_api']:'',
		   ));
		   
		   if(MapSdk::$map_provider=="mapbox"){
			   MapSdk::setMapParameters(array(
			    //'types'=>"poi",
			    'limit'=>1
			   ));
		   }

		   $address_format_use = isset(Yii::app()->params['settings']['address_format_use'])? (!empty(Yii::app()->params['settings']['address_format_use'])?Yii::app()->params['settings']['address_format_use']:'') :'';
		   $address_format_use = !empty($address_format_use)?$address_format_use:1;			
		   
		   $resp = MapSdk::reverseGeocoding($lat,$lng);		   
		   if($resp && $address_format_use==2){
			   unset($resp['address']['address2']);			
		   }
		   
		   $this->code =1; $this->msg = "ok";
		   $this->details = array(
		     'next_action'=>$next_steps,		     
		     'services'=>$services,
		     'provider'=>MapSdk::$map_provider,
		     'data'=>$resp
		   );		   		   
		   
		} catch (Exception $e) {		   
		   $this->msg = t($e->getMessage());	
		   $this->details = array(
		     'next_action'=>"show_error_msg"		     
		   );	   
		}
		$this->jsonResponse();
	}	
	
	public function actionCuisineList()
	{
		try {			
		    $data_cuisine = CCuisine::getList( Yii::app()->language );
		    $this->code = 1;
		    $this->msg = "OK";
		    $this->details = array(
			  'cuisine_url'=>Yii::app()->createAbsoluteUrl("/cuisine"),
		      'data_cuisine'=>$data_cuisine
		    );
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();
	}
	
	public function actionsearchAttributes()
	{
		$currency_code = isset($this->data['currency_code'])?$this->data['currency_code']:'';
		$base_currency = Price_Formatter::$number_format['currency_code'];		

		$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		$multicurrency_enabled = $multicurrency_enabled==1?true:false;		

		$currency_code = !empty($currency_code)?$currency_code:$base_currency;
		if(!empty($currency_code) && $multicurrency_enabled){
			Price_Formatter::init($currency_code);
		}		

		$data = array(		  
		  'price_range'=>AttributesTools::SortPrinceRange(),
		  'sort_by'=>AttributesTools::SortMerchant()
		);
		$this->code = 1;
		$this->msg = "OK";
		$this->details = $data;
		$this->responseJson();
	}
	
	public function actiongetFeedV1()
	{
		// Force log to file immediately - use multiple methods to ensure logging works
		$log_file = Yii::app()->getRuntimePath() . '/application.log';
		file_put_contents($log_file, date('Y-m-d H:i:s') . " [INFO] getFeedV1 - Starting request\n", FILE_APPEND);
		
		try {
		  
		  $transaction_type=''; $whento_deliver='';
		  $page  = isset($this->data['page'])?(integer)$this->data['page']:0;
		  $filters  = isset($this->data['filters'])?(array)$this->data['filters']:array();
		  $cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';		  
		  $filter_location = true;
		  
		  $currency_code = isset($this->data['currency_code'])?$this->data['currency_code']:'';
		  $base_currency = Price_Formatter::$number_format['currency_code'];
		  
		  $multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		  $multicurrency_enabled = $multicurrency_enabled==1?true:false;		
		  $exchange_rate = 1;

		  if(!empty($currency_code) && $multicurrency_enabled){
			Price_Formatter::init($currency_code);
			if($currency_code!=$base_currency){
				$exchange_rate = CMulticurrency::getExchangeRate($base_currency,$currency_code);					
			}
		  }
		  
		  $cookie_name = Yii::app()->params->local_id;
		  $local_id = CommonUtility::getCookie($cookie_name);
		  // Fallback: read directly from PHP $_COOKIE in case CommonUtility::getCookie behaves differently
		  if (empty($local_id) && isset($_COOKIE[$cookie_name])) {
		  	$local_id = $_COOKIE[$cookie_name];
		  }
		  
		  $local_info = array();
		  if(!empty($local_id)){
		  	// Check if place_id is in coordinate format (lng,lat) - common for Ivory Coast
		  	if(preg_match('/^-?\d+\.?\d*,-?\d+\.?\d*$/', $local_id)){
		  		// Extract lat/lng directly from coordinate string
		  		list($lng, $lat) = explode(',', $local_id);
		  		$local_info = array(
		  			'latitude' => trim($lat),
		  			'longitude' => trim($lng),
		  			'place_id' => $local_id,
		  			'reference' => $local_id,
		  			'formatted_address' => t("Current Location")
		  		);
		  		// Try to get more details via reverse geocoding, but don't fail if it doesn't work
		  		try {
		  			$detailed_info = CMaps::locationDetails($local_id,'');
		  			if(!empty($detailed_info) && isset($detailed_info['latitude'])){
		  				$local_info = $detailed_info;
		  			}
		  		} catch (Exception $e) {
		  			// If locationDetails fails, use the coordinate-based info we already have
		  			Yii::log("Location details lookup failed for coordinates: " . $local_id . " - " . $e->getMessage(), 'warning', 'application.api');
		  		}
		  	} else {
		  		// Standard place_id lookup
		  		try {
		  			$local_info = CMaps::locationDetails($local_id,'');
		  		} catch (Exception $e) {
		  			Yii::log("Location details lookup failed for place_id: " . $local_id . " - " . $e->getMessage(), 'warning', 'application.api');
		  			$local_info = array();
		  		}
		  	}
		  }
		  

		//   $filter_location = !empty($local_id)?true:false;
		  
		  $offset = 0; $show_next_page = false;
		  		  		  
		  if(empty($filters['transaction_type'])){
		  	 $transaction_type = CServices::getSetService($cart_uuid);
		  	 $filters['transaction_type'] = $transaction_type;
		  } else $transaction_type  = $filters['transaction_type'];

		  $todays_date = date("Y-m-d H:i"); $set_date = '';
		  
		  /*CHECK IF TIME IS SCHEDULE*/			  
		  $whento_deliver = isset($filters['whento_deliver'])?$filters['whento_deliver']:"now";
		  if($whento_deliver=="schedule"){
		  	 $delivery_date = isset($filters['delivery_date'])?$filters['delivery_date']:'';
		  	 $delivery_time = isset($filters['delivery_time'])?$filters['delivery_time']:'';
		  	 if(!empty($delivery_date) && !empty($delivery_time) ){
		  	 	 $set_date = $delivery_date." ".$delivery_time;		  	 	 
		  	 	 $todays_date = !empty($set_date)?date("Y-m-d H:i" , strtotime($set_date)):$todays_date;	
		  	 }
		  }
		  		  		  		
		  $day_of_week = strtolower(date("N",strtotime($todays_date)));
		  $filter = array(
		    'lat'=>isset($local_info['latitude'])?$local_info['latitude']:'',
		    'lng'=>isset($local_info['longitude'])?$local_info['longitude']:'',
		    'unit'=>Yii::app()->params['settings']['home_search_unit_type'],
		    'limit'=>intval(Yii::app()->params->list_limit),		    
		    'day_of_week'=>$day_of_week>6?1:$day_of_week,
		    'today_now'=>strtolower(date("l",strtotime($todays_date))),
		    'time_now'=>date("H:i",strtotime($todays_date)),
		    'date_now'=>$todays_date,
		    'client_id'=>!Yii::app()->user->isGuest?Yii::app()->user->id:0,
		    'filters'=>$filters,
		  );
		  
		  
		  $count = CMerchantListingV1::preSearch($filter,$filter_location);
		  $total_message = $count<=1? t("{{count}} store",array('{{count}}'=>$count)) : t("{{count}} stores",array('{{count}}'=>$count)) ;
		  
		  $pages = new CPagination($count);
		  $pages->pageSize = intval(Yii::app()->params->list_limit);
		  $pages->setCurrentPage($page);
		  $offset = $pages->getOffset();	
		  $page_count = $pages->getPageCount();	
		  
		  if($page_count > ($page+1) ){
			 $show_next_page = true;
		  }
		  
		  $filter['offset'] = intval($offset);
		  $filter['transaction_type']=$transaction_type;		  
		  
		  $data = CMerchantListingV1::Search($filter,Yii::app()->language,$filter_location);
		  
		  $services = CMerchantListingV1::services( $filter , $filter_location );		  
		  
		  $estimation = [];
		  try {			
			$estimation = CMerchantListingV1::estimationNew( $filter , $filter_location );		
		  } catch (Exception $e) {}		  
		  				  

		  $tranactionType = !empty($transaction_type)?$transaction_type:'delivery';

		  $merchantIds = [];
		  foreach ($data as $key=> $items) {
			    $merchantIds[] =$items['merchant_id'];
		  }
		  
		  $promos = [];
		  try {			
			CPromos::setExchangeRate($exchange_rate);
			$promos = CPromos::getAvaialblePromoNew($merchantIds,CommonUtility::dateOnly());
		  } catch (Exception $e) {}		  
		  
		
		  foreach ($data as $key=> $items) {
			 if(array_key_exists($items['merchant_id'],(array)$estimation)){
				$charge_type = $items['charge_type'];
				$estimationList = isset($estimation[$items['merchant_id']])?$estimation[$items['merchant_id']]:null;
				$estimationFound = isset($estimationList[$tranactionType])?$estimationList[$tranactionType]:null;;
				$data[$key]['estimation'] = isset($estimationFound[$charge_type])?$estimationFound[$charge_type]['estimation']:null;
			 }
			 if(array_key_exists($items['merchant_id'],(array)$promos)){
				$data[$key]['promos'] = isset($promos[$items['merchant_id']])?$promos[$items['merchant_id']]:null;
			 } else $data[$key]['promos'] = null;
		  }		  
		  
		  $options = OptionsTools::find(['enabled_review']);		  
		  $enabled_review = isset($options['enabled_review'])?$options['enabled_review']:'';
		  $enabled_review = $enabled_review==1?true:false;				
		  		  
		  $this->code = 1;
		  $this->msg = "OK";
		  $this->details = array(		      
		    'total_message'=>$total_message,
		    'transaction_type'=>$transaction_type,
		    'show_next_page'=>$show_next_page,
		    'page'=>intval($page)+1,
	        'data'=>$data,
	        'services'=>$services,
	        'estimation'=>$estimation,
			'enabled_review'=>$enabled_review
		  );
		} catch (Exception $e) {
			$this->code = 0;
			$this->msg = is_array($this->msg) ? array_merge($this->msg, array($e->getMessage())) : array($e->getMessage());
		}
		$this->responseJson();
	}
	
	public function actionpauseReasonList()
	{
		try {
		   
			$model = AR_merchant_meta::model()->findAll("meta_name=:meta_name AND meta_value<>''",array(
			 ':meta_name'=>'pause_reason'
			));
			if($model){
				$data = array();
				foreach ($model as $items) {
					$data[$items->merchant_id] = $items->meta_value;
				}
				$this->code = 1;
				$this->msg = t("ok");
				$this->details = $data;
			} else $this->msg = t("No results");
			
		} catch (Exception $e) {
			$this->msg = $e->getMessage();			
		}
		$this->responseJson();
	}
	
	public function actiongetCategory()
	{		
	    $merchant_id = isset($this->data['merchant_id'])?(integer)$this->data['merchant_id']:'';
	    	    
	    try {
		   $category = CMerchantMenu::getCategory($merchant_id,Yii::app()->language);		   
		   $data = array(
		     'category'=>$category,		     
		   );		   		   
		   $this->code = 1; $this->msg = "OK";
		   $this->details = array(		     		    
		     'data'=>$data
		   );		   		   
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		   
		}		
		$this->responseJson();
	}
	
	public function actionservicesList()
	{		

		$merchant_id = isset($this->data['merchant_id'])?(integer)$this->data['merchant_id']:'';
		$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';		
		$table_uuid = isset($this->data['table_uuid'])?$this->data['table_uuid']:'';		
		
		$table_model = null;
		if(!empty($table_uuid)){
			try {
				$table_model = CBooking::getTable($table_uuid);
			} catch (Exception $e) {
				// Table not found, continue without table model
			}
		}
		try {
			
			$merchant = CMerchants::get($merchant_id);			
			$data = CCheckout::getMerchantTransactionList($merchant_id,Yii::app()->language);		
			$transaction = CCart::cartTransaction($cart_uuid,Yii::app()->params->local_transtype,$merchant_id);			
			
			$local_info = []; $estimation = [];
			$local_id = CommonUtility::getCookie(Yii::app()->params->local_id);		
			try {
				$local_info = CMaps::locationDetails($local_id,'');
				$filter = array(
					'merchant_id'=>$merchant_id,
					'lat'=>isset($local_info['latitude'])?$local_info['latitude']:'',
					'lng'=>isset($local_info['longitude'])?$local_info['longitude']:'',
					'unit'=> !empty($merchant->distance_unit)?$merchant->distance_unit:Yii::app()->params['settings']['home_search_unit_type'],
					'shipping_type'=>"standard"
				);				    				
				// $estimation  = CMerchantListingV1::estimationMerchant($filter);
												
				$distance = CMaps::getLocalDistance($filter['unit'],$local_info['latitude'],$local_info['longitude'],$merchant['latitude'],$merchant['lontitude']);							
				$option_merchant = OptionsTools::find(['merchant_delivery_charges_type'],$merchant_id);				
				$filter['charges_type'] = isset($option_merchant['merchant_delivery_charges_type'])?$option_merchant['merchant_delivery_charges_type']:'fixed';;
				$filter['distance'] = floatval($distance);				
				$estimation  = CMerchantListingV1::estimationMerchantNew($filter);				
			} catch (Exception $e) {
				
			}			
									
		    $charge_type = OptionsTools::find(array('merchant_delivery_charges_type'),$merchant_id);
		    $charge_type = isset($charge_type['merchant_delivery_charges_type'])?$charge_type['merchant_delivery_charges_type']:'';
			
			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(
			  'data'=>$data,
			  'transaction'=> $table_model? 'dinein' : $transaction,
			  'table'=> $table_uuid,
			  'charge_type'=>$charge_type,
			  'estimation'=>$estimation,
			);						
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		   
		}		
		$this->responseJson();
	}
	
	public function actionupdateService()
	{		
		$uuid = CommonUtility::createUUID("{{cart}}",'cart_uuid');
		$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';		
		$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';				
		$cart_uuid = !empty($cart_uuid)?$cart_uuid:$uuid;
		
		if(!empty($cart_uuid) && !empty($transaction_type)){
		   CCart::savedAttributes($cart_uuid,Yii::app()->params->local_transtype,$transaction_type);	
		}
		
		$this->code = 1;
		$this->msg = "OK";
		$this->details = [
			'cart_uuid'=>$cart_uuid
		];
		$this->jsonResponse();
	}
		
	public function actiongeStoreMenu()
	{
				
		$merchant_id = isset($this->data['merchant_id'])?(integer)$this->data['merchant_id']:0;
		$image_use = isset($this->data['image_use'])?$this->data['image_use']:'';
		$currency_code = isset($this->data['currency_code'])?$this->data['currency_code']:'';
		$base_currency = Price_Formatter::$number_format['currency_code'];		
		
		$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		$multicurrency_enabled = $multicurrency_enabled==1?true:false;		

		$show_prices_commission = Yii::app()->params['settings']['show_prices_commission'] ?? false;
		$show_prices_commission = $show_prices_commission==1?true:false;
						
		$cart_uuid =  Yii::app()->request->getPost('cart_uuid', null);				
		$transaction_type = CCart::getCartTransactionType($cart_uuid,$merchant_id);		
		
		if($show_prices_commission){
			$commission_results = CMerchants::getCommissionByTransaction($merchant_id,$transaction_type);			
			if($commission_results){
				CMerchantMenu::setCommissionPrice($commission_results);
			}
		}		

		$exchange_rate = 1;
	
		// CHECK IF MERCHANT HAS DIFFERENT TIMEZONE
		$options_merchant = OptionsTools::find(['merchant_timezone','merchant_default_currency'],$merchant_id);				
		$merchant_timezone = isset($options_merchant['merchant_timezone'])?$options_merchant['merchant_timezone']:'';		
		$merchant_default_currency = isset($options_merchant['merchant_default_currency'])?$options_merchant['merchant_default_currency']:'';
		$merchant_default_currency = !empty($merchant_default_currency)?$merchant_default_currency:$base_currency;
		if(!empty($merchant_timezone)){
			Yii::app()->timezone = $merchant_timezone;
		}			

		$currency_code = !empty($currency_code)?$currency_code: (empty($merchant_default_currency)?$base_currency:$merchant_default_currency) ;		
				
		// SET CURRENCY
		if(!empty($currency_code) && $multicurrency_enabled){
			Price_Formatter::init($currency_code);
			if($currency_code!=$merchant_default_currency){
				$exchange_rate = CMulticurrency::getExchangeRate($merchant_default_currency,$currency_code);					
			}
		}
		
		CMerchantMenu::setExchangeRate($exchange_rate);
				
		try {
		   $category = CMerchantMenu::getCategory($merchant_id,Yii::app()->language);		
		   $items = CMerchantMenu::getMenu($merchant_id,Yii::app()->language);		   
		   $promoItems = CMerchantMenu::getPromoItems($merchant_id);		   		   		   
		   $items_not_available = CMerchantMenu::getItemAvailability($merchant_id,date("w"),date("H:h:i"));	
		   $category_not_available = CMerchantMenu::getCategoryAvailability($merchant_id,date("w"),date("H:h:i"));		   
		   $dish = CMerchantMenu::getDishByMerchant($merchant_id,Yii::app()->language);		   

		   if(is_array($promoItems) && count($promoItems)>=1){
			 foreach ($items as &$itemss) {			   			   			   
			   $itemss['promo_data'] = $promoItems[$itemss['item_id']] ?? [];
		     }
		   }		   

		   $data = array(
		     'category'=>$category,
		     'items'=>$items,
			 'items_not_available'=>$items_not_available,
			 'category_not_available'=>$category_not_available,
			 'dish'=>$dish
		   );		   				   
		   $this->code = 1; $this->msg = "OK";
		   $this->details = array(		     		      
		     'merchant_id'=>$merchant_id,
		     'data'=>$data
		   );		   
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		   
		}		
		$this->responseJson();
	}	
		
	public function actiongetMenuItem()
	{		
						
		$merchant_id = isset($this->data['merchant_id'])?(integer)$this->data['merchant_id']:'';
		$item_uuid = isset($this->data['item_uuid'])?CommonUtility::safeTrim($this->data['item_uuid']):'';
		$cat_id = isset($this->data['cat_id'])?(integer)$this->data['cat_id']:0;			
		$cart_uuid = isset($this->data['cart_uuid'])?CommonUtility::safeTrim($this->data['cart_uuid']):null;
		$currency_code = isset($this->data['currency_code'])?CommonUtility::safeTrim($this->data['currency_code']):'';		
		$base_currency = Price_Formatter::$number_format['currency_code'];		


		$cart_data = null;
		if($cart_uuid){
			try {
				$cart_data =CCart::getCartWithMerchant($cart_uuid);					
		    } catch (Exception $e) {}
		}
				
		try {

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		    $multicurrency_enabled = $multicurrency_enabled==1?true:false;		

			$points_enabled = isset(Yii::app()->params['settings']['points_enabled'])?Yii::app()->params['settings']['points_enabled']:false;
		    $points_enabled = $points_enabled==1?true:false;
			$points_earning_rule = isset(Yii::app()->params['settings']['points_earning_rule'])?Yii::app()->params['settings']['points_earning_rule']:'sub_total';						
			$points_earning_points = isset(Yii::app()->params['settings']['points_earning_points'])?Yii::app()->params['settings']['points_earning_points']:0;
						
			if ($points_enabled && ($meta = AR_merchant_meta::getValue($merchant_id, 'loyalty_points'))) {
				$points_enabled = $meta['meta_value'] == 1;
			}

		    $exchange_rate = 1; $admin_exchange_rate = 1;

			$options_merchant = OptionsTools::find(['merchant_default_currency'],$merchant_id);
			$merchant_default_currency = isset($options_merchant['merchant_default_currency'])?$options_merchant['merchant_default_currency']:'';
			$merchant_default_currency = !empty($merchant_default_currency)?$merchant_default_currency:$base_currency;
			$currency_code = !empty($currency_code)?$currency_code: (empty($merchant_default_currency)?$base_currency:$merchant_default_currency) ;
			
			// SET CURRENCY
			if(!empty($currency_code) && $multicurrency_enabled){
				Price_Formatter::init($currency_code);		
				if($currency_code!=$merchant_default_currency){
					$exchange_rate = CMulticurrency::getExchangeRate($merchant_default_currency,$currency_code);					
				}

				if($currency_code!=$base_currency){					
					$admin_exchange_rate = CMulticurrency::getExchangeRate($currency_code,$base_currency);					
				}
			}						
								
			CMerchantMenu::setExchangeRate($exchange_rate);
			CMerchantMenu::setAdminExchangeRate($admin_exchange_rate);
			CMerchantMenu::setPointsRate($points_enabled,$points_earning_rule,$points_earning_points);

			$items = CMerchantMenu::getMenuItem($merchant_id,$cat_id,$item_uuid,Yii::app()->language);			
			$addons = CMerchantMenu::getItemAddonCategory($merchant_id,$item_uuid,Yii::app()->language);
			$addon_items = CMerchantMenu::getAddonItems($merchant_id,$item_uuid,Yii::app()->language);				
			$meta = CMerchantMenu::getItemMeta2($merchant_id, isset($items['item_id'])?$items['item_id']:0 );			
			$meta_details = CMerchantMenu::getMeta($merchant_id,$item_uuid,Yii::app()->language);			
									
			$data = array(
			  'items'=>$items,
			  'addons'=>$addons,
			  'addon_items'=>$addon_items,
			  'meta'=>$meta,
			  'meta_details'=>$meta_details,			  
			);			

			$points = [
				'points_enabled'=>$points_enabled,
				'points_earning_rule'=>$points_earning_rule,
				'points_earning_points'=>$points_earning_points,
			];

			$money_config = AttributesTools::MoneyConfig(true);

			$show_points = true;
			if(!Yii::app()->user->isGuest){				
				if(Yii::app()->user->social_strategy=="guest"){
					$show_points = false;
				}
			}
						
			$this->code = 1; $this->msg = "ok";
		    $this->details = array(
		      'next_action'=>"show_item_details",
		      'sold_out_options'=>AttributesTools::soldOutOptions(),
		      'data'=>$data,
			  'points'=>$points,
			  'money_config'=>$money_config,
			  'cart_data'=>$cart_data,
			  'show_points'=>$show_points,
			  'exchange_rate'=>$exchange_rate
		    );		    		    
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());
		   $this->details = array(
		      'data'=>array(),
			  'cart_data'=>$cart_data
		    );		    	   
		}		
		$this->responseJson();
	}
	
	public function actionpriceFormat()
	{		
		$price = isset($this->data['price'])?(float)$this->data['price']:'';
		$target = isset($this->data['target'])?trim($this->data['target']):'';
		
		$this->code = 1; $this->msg = "ok";
	    $this->details = array(
	      'next_action'=>"fill_price_format",
	      'data'=>array(
	        'target'=>$target,
	        'pretty_price'=>Price_Formatter::formatNumber($price)
	      )
	    );		    	    
		    
		$this->jsonResponse();
	}

	public function actionaddCartItems()
	{							
		$uuid = CommonUtility::createUUID("{{cart}}",'cart_uuid');
		$cart_row = CommonUtility::generateUIID();
				
		$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';		
		$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';		
		$cart_uuid = !empty($cart_uuid)?$cart_uuid:$uuid;
		$merchant_id = isset($this->data['merchant_id'])?(integer)$this->data['merchant_id']:'';
		$cat_id = isset($this->data['cat_id'])?(integer)$this->data['cat_id']:'';
		$item_token = isset($this->data['item_token'])?$this->data['item_token']:'';
		$item_size_id = isset($this->data['item_size_id'])?(integer)$this->data['item_size_id']:0;
		$item_qty = isset($this->data['item_qty'])?(integer)$this->data['item_qty']:0;
		$special_instructions = isset($this->data['special_instructions'])?$this->data['special_instructions']:'';
		$if_sold_out = isset($this->data['if_sold_out'])?$this->data['if_sold_out']:'';
		$inline_qty = isset($this->data['inline_qty'])?(integer)$this->data['inline_qty']:0;
		$item_total = $this->data['item_total'] ?? 0;		
		
		$addons = array();
		$item_addons = isset($this->data['item_addons'])?$this->data['item_addons']:'';
		if(is_array($item_addons) && count($item_addons)>=1){
			foreach ($item_addons as $val) {				
				$multi_option = isset($val['multi_option'])?$val['multi_option']:'';
				$subcat_id = isset($val['subcat_id'])?(integer)$val['subcat_id']:0;
				$sub_items = isset($val['sub_items'])?$val['sub_items']:'';
				$sub_items_checked = isset($val['sub_items_checked'])?(integer)$val['sub_items_checked']:0;				
				if($multi_option=="one" && $sub_items_checked>0){
					$addons[] = array(
					  'cart_row'=>$cart_row,
					  'cart_uuid'=>$cart_uuid,
					  'subcat_id'=>$subcat_id,
					  'sub_item_id'=>$sub_items_checked,					 
					  'qty'=>1,
					  'multi_option'=>$multi_option,
					);
				} else {
					foreach ($sub_items as $sub_items_val) {
						if($sub_items_val['checked']==1){							
							$addons[] = array(
							  'cart_row'=>$cart_row,
							  'cart_uuid'=>$cart_uuid,
							  'subcat_id'=>$subcat_id,
							  'sub_item_id'=>isset($sub_items_val['sub_item_id'])?(integer)$sub_items_val['sub_item_id']:0,							  
							  'qty'=>isset($sub_items_val['qty'])?(integer)$sub_items_val['qty']:0,
							  'multi_option'=>$multi_option,
							);
						}
					}
				}
			}
		}
		
		
		$attributes = array();
		$meta = isset($this->data['meta'])?$this->data['meta']:'';
		if(is_array($meta) && count($meta)>=1){
			foreach ($meta as $meta_name=>$metaval) {				
				if($meta_name!="dish"){
					foreach ($metaval as $val) {
						if($val['checked']>0){	
							$attributes[]=array(
							  'cart_row'=>$cart_row,
							  'cart_uuid'=>$cart_uuid,
							  'meta_name'=>$meta_name,
							  'meta_id'=>$val['meta_id']
							);
						}
					}
				}
			}
		}
		
		$items = array(
		  'merchant_id'=>$merchant_id,
		  'cart_row'=>$cart_row,
		  'cart_uuid'=>$cart_uuid,
		  'cat_id'=>$cat_id,
		  'item_token'=>$item_token,
		  'item_size_id'=>$item_size_id,
		  'qty'=>$item_qty,
		  'special_instructions'=>$special_instructions,
		  'if_sold_out'=>$if_sold_out,
		  'addons'=>$addons,
		  'attributes'=>$attributes,
		  'inline_qty'=>$inline_qty,		  
		);					
		
		try {
							  
		  CCart::validateItemPromo($item_token,$cart_uuid);		  
		  CCart::add($items);					  
		  CCart::applyAutoAddPromo($cart_uuid, $merchant_id);		  
		  		  		  		  
		  CCart::savedAttributes($cart_uuid,Yii::app()->params->local_transtype,$transaction_type);
		  CommonUtility::WriteCookie( "cart_uuid_local" ,$cart_uuid);	
		  		  
		  /*SAVE DELIVERY DETAILS*/
		  if(!CCart::getAttributes($cart_uuid,'whento_deliver')){		     
		     $whento_deliver = isset($this->data['whento_deliver'])?$this->data['whento_deliver']:'now';
		     CCart::savedAttributes($cart_uuid,'whento_deliver',$whento_deliver);
		     if($whento_deliver=="schedule"){
		        $delivery_date = isset($this->data['delivery_date'])?$this->data['delivery_date']:'';
		        $delivery_time_raw = isset($this->data['delivery_time_raw'])?$this->data['delivery_time_raw']:'';
		        if(!empty($delivery_date)){
		        	CCart::savedAttributes($cart_uuid,'delivery_date',$delivery_date);
		        }
		        if(!empty($delivery_time_raw)){
		        	CCart::savedAttributes($cart_uuid,'delivery_time',json_encode($delivery_time_raw));
		        }
		     }
		  }
		  		  		  		
		  $this->code = 1 ; 
		  $this->msg = t("Items added to your cart");
	      $this->details = array(
	        'cart_uuid'=>$cart_uuid
	      );		 
		    
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());
		   $this->details = array(
		      'data'=>array()
		    );		    	   
		}		
		$this->jsonResponse();
	}
	
	public function actiongetCart()
	{			
		
		$local_id = CommonUtility::getCookie(Yii::app()->params->local_id);
		$cart_uuid = isset($this->data['cart_uuid'])?trim($this->data['cart_uuid']):'';
		$payload = isset($this->data['payload'])?$this->data['payload']:'';
		$location = isset($this->data['location'])?$this->data['location']:[];		

		$latitude = isset($this->data['latitude'])?$this->data['latitude']:'';
		$longitude = isset($this->data['longitude'])?$this->data['longitude']:'';

		$currency_code = isset($this->data['currency_code'])?$this->data['currency_code']:'';
		$base_currency = Price_Formatter::$number_format['currency_code'];		

		$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		$multicurrency_enabled = $multicurrency_enabled==1?true:false;		
				
		$distance = 0; 
		$unit = isset(Yii::app()->params['settings']['home_search_unit_type'])?Yii::app()->params['settings']['home_search_unit_type']:'mi';
		$error = array(); 
		$minimum_order = 0; 
		$maximum_order=0;
		$min_order_free_delivery = 0;
		$merchant_info = array(); 
		$delivery_fee = 0; 
		$distance_covered=0;
		$merchant_lat = ''; 
		$merchant_lng=''; 
		$out_of_range = false;
		$address_component = array();
		$exchange_rate = 1;
		$admin_exchange_rate = 1;
		$points_to_earn = 0; $points_label = '';	
		$free_delivery_on_first_order = false;	
		
		try {

			$merchant_id = CCart::getMerchantId($cart_uuid);
		 	$options_merchant = OptionsTools::find(['merchant_timezone','merchant_default_currency','free_delivery_on_first_order'],$merchant_id);						
		    $merchant_default_currency = isset($options_merchant['merchant_default_currency'])?$options_merchant['merchant_default_currency']:'';
			$merchant_default_currency = !empty($merchant_default_currency)?$merchant_default_currency:$base_currency;

			$free_delivery_on_first_order = isset($options_merchant['free_delivery_on_first_order'])?$options_merchant['free_delivery_on_first_order']:false;
			$free_delivery_on_first_order = $free_delivery_on_first_order==1?true:false;			

			$currency_code = !empty($currency_code)?$currency_code: (empty($merchant_default_currency)?$base_currency:$merchant_default_currency) ;			

			$points_enabled = isset(Yii::app()->params['settings']['points_enabled'])?Yii::app()->params['settings']['points_enabled']:false;
		    $points_enabled = $points_enabled==1?true:false;

			if ($points_enabled && ($meta = AR_merchant_meta::getValue($merchant_id, 'loyalty_points'))) {
				$points_enabled = $meta['meta_value'] == 1;
			}

		    // HIDE POINTS IF GUEST
			if(!Yii::app()->user->isGuest){				
				if(Yii::app()->user->social_strategy=="guest"){
					$points_enabled = false;
				}
			}
			
		    $points_earning_rule = isset(Yii::app()->params['settings']['points_earning_rule'])?Yii::app()->params['settings']['points_earning_rule']:'sub_total';									
			$points_earning_points = isset(Yii::app()->params['settings']['points_earning_points'])?Yii::app()->params['settings']['points_earning_points']:0;	
			$points_minimum_purchase = isset(Yii::app()->params['settings']['points_minimum_purchase'])?Yii::app()->params['settings']['points_minimum_purchase']:0;	
			$points_maximum_purchase = isset(Yii::app()->params['settings']['points_maximum_purchase'])?Yii::app()->params['settings']['points_maximum_purchase']:0;

			// SET CURRENCY			
			if(!empty($currency_code) && $multicurrency_enabled){
				Price_Formatter::init($currency_code);
				if($currency_code!=$merchant_default_currency){
					$exchange_rate = CMulticurrency::getExchangeRate($merchant_default_currency,$currency_code);					
				}

				if($currency_code!=$base_currency){							
					$admin_exchange_rate = CMulticurrency::getExchangeRate($currency_code,$base_currency);						
				}
			}
		
			CCart::setExchangeRate($exchange_rate);
			CCart::setAdminExchangeRate($admin_exchange_rate);
			CCart::setPointsRate($points_enabled,$points_earning_rule,$points_earning_points,$points_minimum_purchase,$points_maximum_purchase);

			require_once 'get-cart.php';								
						
			$unit_pretty = !empty($unit)? MapSdk::prettyUnit($unit) : $unit;
			if(isset($checkout_data['data'])){
				$checkout_data['data']['delivery_distance'] = "$distance $unit_pretty";			
			}						

			try {									
				AR_cart::model()->updateAll(array(
					'subtotal'=>isset($sub_total) ? floatval($sub_total) :0,
					'total'=> isset($total)? floatval($total):0,		
					'currency_code'=>$currency_code
				), "cart_uuid=:cart_uuid",[
					":cart_uuid"=>$cart_uuid,					
				]);				
			} catch (Exception $e) {}			
			
			$this->code = 1; $this->msg = "ok";
		    $this->details = array(			      
		      'cart_uuid'=>$cart_uuid,
		      'payload'=>$payload,
		      'error'=>$error,
		      'checkout_data'=>$checkout_data,
		      'out_of_range'=>$out_of_range,
		      'address_component'=>$address_component,
		      'go_checkout'=>$go_checkout,
		      'items_count'=>$items_count,
		      'data'=>$data,		     
			  'distance_pretty' => "$distance $unit_pretty",
			  'points_data'=>[
				'points_enabled'=>$points_enabled,
			    'points_to_earn'=>$points_to_earn,
			    'points_label'=>$points_label,
			  ],
			  'delivery_details'=>[
				 'delivery_fee_raw'=>$delivery_fee,
			     'delivery_fee'=>t("Delivery Fee {delivery_fee}",[
					'{delivery_fee}'=>Price_Formatter::formatNumber($delivery_fee)
				 ])
			  ]
		    );				    
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());			   		   
		}						
		$this->responseJson();
	}
	
	public function actionremoveCartItem()
	{		
		$cart_uuid = isset($this->data['cart_uuid'])?trim($this->data['cart_uuid']):'';		
		$row = isset($this->data['row'])?trim($this->data['row']):'';		
		
		try {
			
			CCart::remove($cart_uuid,$row);
			$this->code = 1; $this->msg = "Ok";
			$this->details = array(
		      'data'=>array()
		    );		    	   			
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());
		   $this->details = array(
		      'data'=>array()
		    );		    	   
		}		
		$this->jsonResponse();
	}
	
	public function actionupdateCartItems()
	{		
		$cart_uuid = isset($this->data['cart_uuid'])?trim($this->data['cart_uuid']):'';		
		$cart_row = isset($this->data['cart_row'])?trim($this->data['cart_row']):'';		
		$item_qty = isset($this->data['item_qty'])?(integer)trim($this->data['item_qty']):0;		
		try {
			
			CCart::update($cart_uuid,$cart_row,$item_qty);

			$merchant_id = CCart::getMerchantId($cart_uuid);
			CCart::applyAutoAddPromo($cart_uuid, $merchant_id);

			$this->code = 1; $this->msg = "Ok";
			$this->details = array(
		      'data'=>array()
		    );		    	   			
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());
		   $this->details = array(
		      'data'=>array()
		    );		    	   
		}		
		$this->jsonResponse();
	}
	
	public function actionclearCart()
	{				
		$cart_uuid = isset($this->data['cart_uuid'])?trim($this->data['cart_uuid']):'';		
		try {
			
			CCart::clear($cart_uuid);
			$this->code = 1; $this->msg = "Ok";
			$this->details = array(
		      'data'=>array()
		    );		    	   			
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());
		   $this->details = array(
		      'data'=>array()
		    );		    	   
		}		
		$this->jsonResponse();
	}
	
	public function actiongetReview()
	{		
		$merchant_id = isset($this->data['merchant_id'])?(integer)$this->data['merchant_id']:'';
		$page = isset($this->data['page'])?(integer)$this->data['page']:0;
		
		try {			
			
			$offset = 0; $show_next_page = false;
		    $limit = Yii::app()->params->list_limit;
		
		    $total_rows = CReviews::reviewsCount($merchant_id);
		   
		   	$pages = new CPagination($total_rows);
			$pages->pageSize = $limit;
			$pages->setCurrentPage($page);
			$offset = $pages->getOffset();	
			$page_count = $pages->getPageCount();
								
		   if($page_count > ($page+1) ){
				$show_next_page = true;
		   }
		   		   		 
		   $data = CReviews::reviews($merchant_id,$offset,$limit);		   
		   $this->code = 1;
		   $this->msg = "OK";
		   $this->details = array(
		     'show_next_page'=>$show_next_page,
		     'page'=>intval($page)+1,
		     'data'=>$data
		   );		   		   		   
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		   
		}		
		$this->responseJson();
	}
	
	public function actionuploadReview()
	{
		$upload_uuid = CommonUtility::generateUIID();
		$merchant_id = isset($this->data['merchant_id'])?(integer)$this->data['merchant_id']:0;		
		$allowed_extension = CommonUtility::safeExplode(",",  Yii::app()->params['upload_type']);
		$maxsize = (integer) Yii::app()->params['upload_size'] ;
					
		if (!empty($_FILES)) {
			
			$title = $_FILES['file']['name'];   
			$size = (integer)$_FILES['file']['size'];   
			$filetype = $_FILES['file']['type'];   								
			
			if(isset($_FILES['file']['name'])){
			   $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
			} else $extension = strtolower(substr($title,-3,3));
			
			if(!in_array($extension,$allowed_extension)){			
				$this->msg = t("Invalid file extension");
				$this->jsonResponse();
			}
			if($size>$maxsize){
				$this->msg = t("Invalid file size");
				$this->jsonResponse();
			}
			
			$upload_path = "upload/reviews";
			$tempFile = $_FILES['file']['tmp_name'];   								
			$upload_uuid = CommonUtility::createUUID("{{media_files}}",'upload_uuid');
			$filename = $upload_uuid.".$extension";						
			$path = CommonUtility::uploadDestination($upload_path)."/".$filename;						
			
			$image_set_width = isset(Yii::app()->params['settings']['review_image_resize_width']) ? intval(Yii::app()->params['settings']['review_image_resize_width']) : 0;
			$image_set_width = $image_set_width<=0?300:$image_set_width;
						
			$image_driver = !empty(Yii::app()->params['settings']['image_driver'])?Yii::app()->params['settings']['image_driver']:Yii::app()->params->image['driver'];			
			$manager = new ImageManager(array('driver' => $image_driver ));								
			$image = $manager->make($tempFile);
			$image_width = $manager->make($tempFile)->width();
						
			if($image_width>$image_set_width){
				$image->resize(null, $image_set_width, function ($constraint) {
				    $constraint->aspectRatio();
				});
				$image->save($path);
			} else {
				$image->save($path,60);
			}				
			
			//move_uploaded_file($tempFile,$path);
			
			$media = new AR_media;		
			$media->merchant_id = intval($merchant_id);
			$media->title = $title;			
			$media->path = $upload_path;
			$media->filename = $filename;
			$media->size = $size;
			$media->media_type = $filetype;						
			$media->meta_name = AttributesTools::metaReview();		
			$media->upload_uuid = $upload_uuid;
			$media->save();
			
			$this->code = 1; $this->msg = "OK";			
			$this->details = array(			   			   
			   'url_image'=>CMedia::getImage($filename,$upload_path),
			   'filename'=>$media->filename,
			   'id'=>$upload_uuid			   
			);			
			
		} else $this->msg = t("Invalid file");
		$this->responseJson();		
	}
	
	public function actionremoveReviewImage()
	{				
		$id = isset($this->data['id'])?$this->data['id']:'';
		$media = AR_media::model()->find('upload_uuid=:upload_uuid', 
		array(':upload_uuid'=>$id)); 		
		if($media){
			$media->delete();
			$this->code = 1; 
			$this->msg = "OK";			
			$this->details = $id;
		} else $this->msg = t("record not found");
		$this->jsonResponse();		
	}
	public function actionremoveAllReviewImage()
	{		
		if(isset($this->data['upload_images'])){
			$all_uuid = array();
			foreach ($this->data['upload_images'] as $val) {															
				$all_uuid[]=$val['id'];				
			}						
			$criteria = new CDbCriteria();
			$criteria->addInCondition('upload_uuid', $all_uuid);
			AR_media::model()->deleteAll($criteria);			
		}
		$this->code = 1; $this->msg = "ok";
		$this->jsonResponse();		
	}
	
	public function actionaddReview()
	{		
		try {

			
			$order_uuid = isset($this->data['order_uuid'])?trim($this->data['order_uuid']):'';
			$order = COrders::get($order_uuid);
						
			$find = AR_review::model()->find('merchant_id=:merchant_id AND client_id=:client_id
			AND order_id=:order_id', 
		    array( 
		      ':merchant_id'=>intval($order->merchant_id),
		      ':client_id'=>intval(Yii::app()->user->id),
		      ':order_id'=>intval($order->order_id)
		    )); 	
		    
		    if(!$find){
				$model = new AR_review;	
				$model->merchant_id  = intval($order->merchant_id);
				$model->order_id  = intval($order->order_id);
				$model->client_id = intval(Yii::app()->user->id) ;
				$model->review  = isset($this->data['review_content'])?$this->data['review_content']:'';		
				$model->rating  = isset($this->data['rating_value'])?(integer)$this->data['rating_value']:0;
				$model->date_created = CommonUtility::dateNow();
				$model->ip_address = CommonUtility::userIp();
				$model->as_anonymous = isset($this->data['as_anonymous'])?(integer)$this->data['as_anonymous']:0;		
				$model->scenario = 'insert';
				if ($model->save()){
					$this->code = 1; $this->msg = t("Review has been added. Thank you.");
					CReviews::insertMeta($model->id,'tags_like',$this->data['tags_like']);
					CReviews::insertMeta($model->id,'tags_not_like',$this->data['tags_not_like']);
					CReviews::insertMetaImages($model->id,'upload_images',$this->data['upload_images']);
				} else {							
					if ( $error = CommonUtility::parseError( $model->getErrors()) ){
						$this->msg = $error;
					} else $this->msg[] = array('invalid error');				
				}				
		    }else $this->msg[] = t("You already added review for this order");
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		    
		}	
		$this->responseJson();
	}		
	
	public function actionuserLogin()
	{					
		$redirect = isset($this->data['redirect'])?$this->data['redirect']:'';
		$_POST['AR_customer_login'] = array(
		  'username'=>isset($this->data['username'])?$this->data['username']:'',
		  'password'=>isset($this->data['password'])?$this->data['password']:'',
		  'rememberMe'=>intval($this->data['rememberme'])
		);		
		
		$options = OptionsTools::find(array('signup_enabled_verification','signup_enabled_capcha'));
		$signup_enabled_capcha = isset($options['signup_enabled_capcha'])?$options['signup_enabled_capcha']:false;
		$capcha = $signup_enabled_capcha==1?true:false;
		$recaptcha_response = isset($this->data['recaptcha_response'])?$this->data['recaptcha_response']:'';			
		
		$model=new AR_customer_login;
		$model->attributes=$_POST['AR_customer_login'];
		$model->capcha = $capcha;
		$model->recaptcha_response = $recaptcha_response;
		$model->merchant_id = 0;		
			
		if($model->validate() && $model->login() ){
												
			$place_id = CommonUtility::getCookie(Yii::app()->params->local_id);			
			//$address_uuid = CCheckout::saveDeliveryAddress($place_id , Yii::app()->user->id);
			
			$this->code = 1 ;
			$this->msg = t("Login successful");
			$this->details = array(
			  'redirect'=>!empty($redirect)?$redirect:Yii::app()->getBaseUrl(true)
			);			
		} else {			
			$this->msg = CommonUtility::parseError( $model->getErrors() );
		}
		$this->jsonResponse();
	}
	
	public function actiongetphoneprefix()
	{
		if ( $data = AttributesTools::countryMobilePrefix()){
			$this->code = 1; $this->msg = "ok";
			$this->details = array(
			  'data'=>$data
			);						
		} else $this->msg = "failed";
		$this->responseJson();
	}
	
	public function actionregisterUser()
	{	
		try {
									
			$options = OptionsTools::find(array('signup_enabled_verification','signup_enabled_capcha'));
			$enabled_verification = isset($options['signup_enabled_verification'])?$options['signup_enabled_verification']:false;
			$verification = $enabled_verification==1?true:false;
			
			$signup_enabled_capcha = isset($options['signup_enabled_capcha'])?$options['signup_enabled_capcha']:false;
			$capcha = $signup_enabled_capcha==1?true:false;
		
			$digit_code = CommonUtility::generateNumber(4,true);
						
			$recaptcha_response = isset($this->data['recaptcha_response'])?$this->data['recaptcha_response']:'';			
			
			$prefix = isset($this->data['mobile_prefix'])?$this->data['mobile_prefix']:'';
			$mobile_number = isset($this->data['mobile_number'])?$this->data['mobile_number']:'';
			$redirect = isset($this->data['redirect'])?$this->data['redirect']:'';
			$next_url = isset($this->data['next_url'])?$this->data['next_url']:'';									
			
			$model=new AR_clientsignup;
			$model->scenario = 'register';
			$model->capcha = $capcha;
			$model->recaptcha_response = $recaptcha_response;
			
			$model->first_name = isset($this->data['firstname'])?$this->data['firstname']:'';
			$model->last_name = isset($this->data['lastname'])?$this->data['lastname']:'';
			$model->email_address = isset($this->data['email_address'])?$this->data['email_address']:'';
			$model->contact_phone = $prefix.$mobile_number;
			$model->password = isset($this->data['password'])?$this->data['password']:'';		
			$password = $model->password;
			$model->cpassword = isset($this->data['cpassword'])?$this->data['cpassword']:'';
			$model->phone_prefix = $prefix;			
			$model->mobile_verification_code = $digit_code;
			$model->merchant_id = 0;
			
			if($verification==1 || $verification==true){
				$model->status='pending';
			}

			if($local_id = CommonUtility::getCookie(Yii::app()->params->local_id)){
				$model->local_id = $local_id;
			}			

			// CUSTOM FIELDS			
			$field_data = [];
			$custom_fields = isset($this->data['custom_fields'])?$this->data['custom_fields']:'';					
			$field_data = AttributesTools::getCustomFields('customer','key2'); 							
			$model->custom_fields = $custom_fields;			
			CommonUtility::validateCustomFields($field_data,$custom_fields);			
			
			if ($model->save()){
				$this->code = 1 ;

				$redirect = !empty($redirect)?$redirect:Yii::app()->getBaseUrl(true);			
				
				if($verification==1 || $verification==true){
										
					$this->msg = t("Please wait until we redirect you");	
					$redirect = Yii::app()->createUrl("/account/verify",array(
					  'uuid'=>$model->client_uuid,
					  'redirect'=>$redirect
					));			
					$this->details = array(
					  'redirect'=>$redirect
					);			
				} else {
					$this->msg = t("Registration successful");				
					$this->details = array(
					  'redirect'=>$redirect
					);			
					
					//AUTO LOGIN
					$this->autoLogin($model->email_address,$password);
				}
			} else {							
				$this->msg = CommonUtility::parseError( $model->getErrors() );	
				if($models = ACustomer::checkEmailExists($model->email_address)){
					$this->code = 3;
					$this->msg = t("We found your email address in our records. Instructions have been sent to complete sign-up.");
					ACustomer::SendCompleteRegistration($models->client_uuid);
				}
			}		
			
		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());	
		}
		$this->jsonResponse();
	}
	
	public function actioncheckoutTransaction()
	{		
		$cart_uuid = isset($this->data['cart_uuid'])?trim($this->data['cart_uuid']):'';			
		try {
		   $merchant_id = CCart::getMerchantId($cart_uuid);		  
		   $transactions = CCheckout::getMerchantTransactionList( $merchant_id , Yii::app()->language);

		   $options = OptionsTools::find(['merchant_time_selection'],$merchant_id);				
		   $time_selection = isset($options['merchant_time_selection'])?$options['merchant_time_selection']:1;
		   $time_selection = !empty($time_selection)?$time_selection:1;

		   $delivery_option = CCheckout::deliveryOptionList("",$time_selection);	
		   	   
		   $options = OptionsTools::find(array('website_time_picker_interval','website_date_range'));
           $interval = isset($options['website_time_picker_interval'])?$options['website_time_picker_interval']." mins":'20 mins';		
		   $date_range = isset($options['website_date_range'])?$options['website_date_range']:7;			
		   $date_range = $date_range>0?$date_range:7;			   

		   // CHECK IF MERCHANT HAS DIFFERENT TIMEZONE
		   $options_merchant = OptionsTools::find(['merchant_time_picker_interval','merchant_timezone'],$merchant_id);
		   $interval_merchant = isset($options_merchant['merchant_time_picker_interval'])? ( !empty($options_merchant['merchant_time_picker_interval']) ? $options_merchant['merchant_time_picker_interval']." mins" :''):'';
		   $interval = !empty($interval_merchant)?$interval_merchant:$interval;
		   $merchant_timezone = isset($options_merchant['merchant_timezone'])?$options_merchant['merchant_timezone']:'';
		   if(!empty($merchant_timezone)){
			  Yii::app()->timezone = $merchant_timezone;
		   }
		   		   
		   $opening_hours = CMerchantListingV1::openHours2($merchant_id,$interval,$date_range); 
		   $delivery_date_list = $opening_hours['dates'] ?? null;

		   $date_today = date('Y-m-d'); 				
			if(!empty($delivery_date_list) && !array_key_exists($date_today,(array)$delivery_date_list) && $merchant_id>0 ){
				if(isset($delivery_option['now'])){					
					unset($delivery_option['now']);
				}				
			}							

		   $transaction_type = '';
		   $data = array();
		   		   
		   $this->code = 1; $this->msg = "ok";
		   $this->details = array(		     
		     'data'=>$data,
		     'transaction_type'=>$transaction_type,
		     'transactions'=>$transactions,
		     'delivery_option'=>$delivery_option,
		     'opening_hours'=>$opening_hours,		     		     
		   );		   
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}
	
	public function actioncheckoutSave()
	{				
		try {
			
			$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';	
		    $whento_deliver = isset($this->data['whento_deliver'])?$this->data['whento_deliver']:'';	
							
			CCart::savedAttributes($cart_uuid,'transaction_type',
			 isset($this->data['transaction_type'])?$this->data['transaction_type']:''
			);
			
			CCart::savedAttributes($cart_uuid,'whento_deliver',
			 isset($this->data['whento_deliver'])?$this->data['whento_deliver']:''
			);
			
			CCart::savedAttributes($cart_uuid,'delivery_date',
			 isset($this->data['delivery_date'])?$this->data['delivery_date']:''
			);
			
			if($whento_deliver=="schedule"){
				CCart::savedAttributes($cart_uuid,'delivery_time',
				 isset($this->data['delivery_time'])? json_encode($this->data['delivery_time']) :''
				);
			} else CCart::deleteAttributesAll($cart_uuid, array('delivery_time','delivery_date') );
			
			$this->code = 1; $this->msg = "ok";
			$this->details = array(
			   'whento_deliver'=>$whento_deliver,
			   'delivery_date'=>isset($this->data['delivery_date'])?$this->data['delivery_date']:'',
			   'delivery_time'=>isset($this->data['delivery_time'])?$this->data['delivery_time']:'',
			);
						
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());
		}
		
		$this->jsonResponse();
	}
	
	public function actionloadPromo()
	{			
		$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
		
		try {
			
			$currency_code = Yii::app()->input->post('currency_code');	
			$base_currency = Price_Formatter::$number_format['currency_code'];		

			$merchant_id = CCart::getMerchantId($cart_uuid);

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		    $multicurrency_enabled = $multicurrency_enabled==1?true:false;		
		    $exchange_rate = 1;

			$options_merchant = OptionsTools::find(['merchant_default_currency'],$merchant_id);							
			$merchant_default_currency = isset($options_merchant['merchant_default_currency'])?$options_merchant['merchant_default_currency']:'';
			$merchant_default_currency = !empty($merchant_default_currency)?$merchant_default_currency:$base_currency;
			$currency_code = !empty($currency_code)?$currency_code: (empty($merchant_default_currency)?$base_currency:$merchant_default_currency) ;

			// SET CURRENCY
			if(!empty($currency_code) && $multicurrency_enabled){
				Price_Formatter::init($currency_code);
				if($currency_code!=$merchant_default_currency){
					$exchange_rate = CMulticurrency::getExchangeRate($merchant_default_currency,$currency_code);					
				}
		    }

			CPromos::setExchangeRate($exchange_rate);
			$data = CPromos::promo($merchant_id,date("Y-m-d"));	
			
			$promo_selected = array();
			$atts = CCart::getAttributesAll($cart_uuid,array('promo','promo_type','promo_id'));
			if($atts){
				CCart::getContent($cart_uuid,Yii::app()->language);	
				$subtotal = CCart::getSubTotal();							
				$sub_total = floatval($subtotal['sub_total']);				
				$saving = '';
				if(isset($atts['promo'])){
					if ($promo = json_decode($atts['promo'],true)){												

						$promo_id = isset($promo['id'])?$promo['id']:null;	

						if($promo['type']=="offers"){							
							$offers_details = CPromos::findOffers($promo_id);
							$max_discount_cap = $offers_details ? $offers_details->max_discount_cap :0;
							$discount_percent = isset($promo['value'])? CCart::cleanValues($promo['value']):0;							
							$discount_value = ($discount_percent/100) * $sub_total;
							
							$discount_value = ($max_discount_cap > 0 && $discount_value > $max_discount_cap) ? $max_discount_cap : $discount_value;

							$saving = t("You're saving {{discount}}",array(
								'{{discount}}'=>Price_Formatter::formatNumber(($discount_value*$exchange_rate))
							));							
						} elseif ( $promo['type']=="voucher" ){														
							if($promo_details = CPromos::findVoucherByID($promo_id)){								
								$max_discount_cap = isset($promo_details['max_discount_cap'])?$promo_details['max_discount_cap']:0;								
								if($promo_details->voucher_type=="percentage"){
									$discount_value = $sub_total*($promo_details->amount/100);									
								} else {
									$discount_value = $promo_details->amount;
								}
								
								$final_discount_value = ($max_discount_cap > 0 && $discount_value >= $max_discount_cap) ? $max_discount_cap : $discount_value;

								$discount_value = $final_discount_value*-1;	
									$saving = t("You're saving {{discount}}",array(
									'{{discount}}'=>Price_Formatter::formatNumber(($discount_value*$exchange_rate))
								));
							} else {
								$discount_value = isset($promo['value'])?$promo['value']:0;
								$discount_value = $discount_value*-1;	
								$saving = t("You're saving {{discount}}",array(
								'{{discount}}'=>Price_Formatter::formatNumber(($discount_value*$exchange_rate))
								));
							}							
						}
						$promo_selected = array( $atts['promo_type'],$atts['promo_id'] , $saving );
					}
				}				
			}
				
			
			if($data){
				$this->code = 1; $this->msg = "ok";	
				$this->details = array(
				  'count'=>count($data),
				  'data'=>$data,
				  'promo_selected'=>$promo_selected
				);				
			} else $this->msg = t("no results");			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}
	
	public function actionapplyPromo()
	{
		$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
		$promo_id = isset($this->data['promo_id'])?intval($this->data['promo_id']):'';
		$promo_type = isset($this->data['promo_type'])?$this->data['promo_type']:'';
		$currency_code = isset($this->data['currency_code'])?$this->data['currency_code']:'';
		$base_currency = Price_Formatter::$number_format['currency_code'];		

		$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		$multicurrency_enabled = $multicurrency_enabled==1?true:false;
		$exchange_rate = 1;
		
		try {

			$merchant_id = CCart::getMerchantId($cart_uuid);

			$options_merchant = OptionsTools::find(['merchant_default_currency'],$merchant_id);							
			$merchant_default_currency = isset($options_merchant['merchant_default_currency'])?$options_merchant['merchant_default_currency']:'';
			$merchant_default_currency = !empty($merchant_default_currency)?$merchant_default_currency:$base_currency;
			$currency_code = !empty($currency_code)?$currency_code: (empty($merchant_default_currency)?$base_currency:$merchant_default_currency) ;

			// SET CURRENCY
			if(!empty($currency_code) && $multicurrency_enabled){
				Price_Formatter::init($currency_code);
				if($currency_code!=$merchant_default_currency){
					$exchange_rate = CMulticurrency::getExchangeRate($merchant_default_currency,$currency_code);					
				}
		    }

			CCart::getContent($cart_uuid,Yii::app()->language);	
			$subtotal = CCart::getSubTotal();
			$sub_total = floatval($subtotal['sub_total']);
			
			$now = date("Y-m-d");			
			$params = array();

			CPromos::setExchangeRate($exchange_rate);
				   
			if($promo_type==="voucher"){
											
				$transaction_type = CCart::cartTransaction($cart_uuid,Yii::app()->params->local_transtype,$merchant_id);				
				$resp = CPromos::applyVoucher( $merchant_id, $promo_id, Yii::app()->user->id , $now , ($sub_total*$exchange_rate) , $transaction_type);
				$less_amount = $resp['less_amount'];
				
				$params = array(
				  'name'=>"less voucher",
				  'type'=>$promo_type,
				  'id'=>$promo_id,
				  'target'=>'subtotal',
				  'value'=>"-$less_amount",
				);	
				// dump($resp);
				// dump($params);
				// die();
				
			} else if ($promo_type=="offers") {		
				
				$transaction_type = CCart::cartTransaction($cart_uuid,Yii::app()->params->local_transtype,$merchant_id);			
				$resp = CPromos::applyOffers( $merchant_id, $promo_id, $now , ($sub_total*$exchange_rate) , $transaction_type);				
				$less_amount = $resp['less_amount'];
				$max_discount_cap = isset($resp['max_discount_cap'])?$resp['max_discount_cap']:0;
				
				$value = ($max_discount_cap > 0 && $less_amount > $max_discount_cap) ? "-$max_discount_cap" : "-%$less_amount";
				if(($max_discount_cap > 0 && $less_amount > $max_discount_cap)){
					$valueLabel = Price_Formatter::formatNumber($max_discount_cap);
					$label = "Discount";
				} else {
					$valueLabel = Price_Formatter::convertToRaw($less_amount,0);
					$label = "Discount {{discount}}%";
				}

				$name = array(
				  'label'=>$label,
				  'params'=>array(
				   '{{discount}}'=>$valueLabel
				  )
				);							

				$params = array(
				  'name'=> json_encode($name),
				  'type'=>$promo_type,
				  'id'=>$promo_id,
				  'target'=>'subtotal',
				  'value'=>$value
				);						
			}
			
			CCart::savedAttributes($cart_uuid,'promo',json_encode($params));
			CCart::savedAttributes($cart_uuid,'promo_type',$promo_type);
			CCart::savedAttributes($cart_uuid,'promo_id',$promo_id);
								
			$this->code = 1; 
			$this->msg = "succesful";
			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->jsonResponse();
	}
	
	public function actionremovePromo()
	{
				
		$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
		$promo_id = isset($this->data['promo_id'])?intval($this->data['promo_id']):'';
		$promo_type = isset($this->data['promo_type'])?$this->data['promo_type']:'';
		
		
		try {
			
			$merchant_id = CCart::getMerchantId($cart_uuid);			
			CCart::deleteAttributesAll($cart_uuid,CCart::CONDITION_RM);
			$this->code = 1;
			$this->msg = "ok";
			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->jsonResponse();
	}
	
	public function actionloadTips()
	{		
		try {
	
			$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
			$merchant_id = CCart::getMerchantId($cart_uuid);			
			$options_data = OptionsTools::find(['merchant_enabled_tip','tips_in_transactions','merchant_tip_type','merchant_default_currency'],$merchant_id);							
			$tip_type = isset($options_data['merchant_tip_type'])?$options_data['merchant_tip_type']:'fixed';			
			$merchant_default_currency = isset($options_data['merchant_default_currency'])?$options_data['merchant_default_currency']:'';

			$exchange_rate = 1;
			$currency_code = Yii::app()->input->post('currency_code');	
		    $base_currency = Price_Formatter::$number_format['currency_code'];		
			$merchant_default_currency = !empty($merchant_default_currency)?$merchant_default_currency:$base_currency;
			$currency_code = !empty($currency_code)?$currency_code: (empty($merchant_default_currency)?$base_currency:$merchant_default_currency) ;

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		    $multicurrency_enabled = $multicurrency_enabled==1?true:false;			

			// SET CURRENCY
			if(!empty($currency_code) && $multicurrency_enabled){
				Price_Formatter::init($currency_code);				
				if($currency_code!=$merchant_default_currency){
					$exchange_rate = CMulticurrency::getExchangeRate($merchant_default_currency,$currency_code);					
				}
			}

			// if(!empty($currency_code) && $multicurrency_enabled){
			// 	Price_Formatter::init($currency_code);
			// 	$to_use_currency = !empty($merchant_default_currency)?$merchant_default_currency:$base_currency;			
			// 	if($to_use_currency!=$currency_code){
			// 		$exchange_rate = CMulticurrency::getExchangeRate($to_use_currency,$currency_code);				
			// 	}			
			// } else if ( !empty($merchant_default_currency) && $multicurrency_enabled ){			
			// 	Price_Formatter::init($merchant_default_currency);
			// }	
			
			
			$data = CTips::data('name',$tip_type,$exchange_rate);
			$enabled_tip = false;
			
			$tips = 0; $transaction_type = '';			
			if ( $resp = CCart::getAttributesAll($cart_uuid,array('tips','transaction_type')) ){				
				$tips = isset($resp['tips'])?floatval($resp['tips']):0;
				$transaction_type = isset($resp['transaction_type'])?$resp['transaction_type']:'';				
			}
			
		    $enabled_tip = isset($options_data['merchant_enabled_tip'])?$options_data['merchant_enabled_tip']:false;			

			$in_transaction = false;
			$tips_in_transactions = isset($options_data['tips_in_transactions'])?json_decode($options_data['tips_in_transactions']):array();						
			if(is_array($tips_in_transactions) && count($tips_in_transactions)>=1){
				if(in_array($transaction_type,(array)$tips_in_transactions)){
					$in_transaction = true;
				}
			}
						
			$this->code = 1; $this->msg = "OK";
			$this->details = array(
			  'transaction_type'=>$transaction_type,
			  'tips'=>$tips,
			  'data'=>$data,
			  'enabled_tips'=>$enabled_tip==1?true:false,
			  'in_transaction'=>$in_transaction
			);			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}
	
	public function actioncheckoutAddTips()
	{
		$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
		$value = isset($this->data['value'])?floatval($this->data['value']):0;		
		$is_manual = isset($this->data['is_manual'])?trim($this->data['is_manual']):false;		
		try {
			
			$merchant_id = CCart::getMerchantId($cart_uuid);
			$options_data = OptionsTools::find(['merchant_enabled_tip','merchant_tip_type'],$merchant_id);							
			$enabled_tip = isset($options_data['merchant_enabled_tip'])?$options_data['merchant_enabled_tip']:false;
			$tip_type = isset($options_data['merchant_tip_type'])?$options_data['merchant_tip_type']:'fixed';						

			if($tip_type=="percentage" && $enabled_tip==1 && $is_manual==false){
				$distance = 0; 
				$unit = isset(Yii::app()->params['settings']['home_search_unit_type'])?Yii::app()->params['settings']['home_search_unit_type']:'mi';
				$error = array(); 
				$minimum_order = 0; 
				$maximum_order=0;
				$merchant_info = array(); 
				$delivery_fee = 0; 
				$distance_covered=0;
				$merchant_lat = ''; 
				$merchant_lng=''; 
				$out_of_range = false;
				$address_component = array();
				$payload = ['subtotal'];
				try {
					require_once 'get-cart.php';
					$subtotal = $data['subtotal']['raw'];
					$value = ($value/100)*$subtotal;
				} catch (Exception $e) {
					$this->msg = $e->getMessage();
				}
			}
			
			if($enabled_tip){				
				CCart::savedAttributes($cart_uuid,'tips',$value);	
				$this->code = 1; $this->msg = "OK";
				$this->details = array(
				'tips'=>$value,			  
				);
			} else $this->msg = t("Tip are disabled");
			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->jsonResponse();
	}
	
	public function actioncheckoutAddress()
	{		
		$data = array();
		$attributes = array(); $addresses = array();
		$transaction_type = '';
		$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
		
		try {
			
			$local_id = CommonUtility::getCookie(Yii::app()->params->local_id);
			//$merchant_id = CCart::getMerchantId($cart_uuid);
			
			$resp = CCart::getAttributesAll($cart_uuid,array(
			  'transaction_type','location_name','delivery_instructions','delivery_options','address_label'
			));			
						
			$transaction_type = isset($resp['transaction_type'])?$resp['transaction_type']:'';					
			
			if(!Yii::app()->user->isGuest){
				$addresses = CClientAddress::getAddresses( Yii::app()->user->id );		
				try {
					$data = CClientAddress::getAddress($local_id,Yii::app()->user->id);					
				} catch (Exception $e) {
					if($addresses){						
						if($addresses[0]){
							$data = isset($addresses[0])?$addresses[0]:[];
							CommonUtility::WriteCookie( Yii::app()->params->local_id ,$addresses[0]['place_id']); 						
						}						
					}					
				}				
			}
																					
			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(
			  'transaction_type'=>$transaction_type,
			  'data'=>$data,	
			  'addresses'=>$addresses,			  
			  'delivery_option'=>CCheckout::deliveryOption(),
			  'address_label'=>CCheckout::addressLabel(),
			  'maps_config'=>CMaps::config(),			  
			);			
		} catch (Exception $e) {		    			
		    $this->code = 1;
			$this->msg = "OK";
			$this->details = array(
			  'transaction_type'=>$transaction_type,
			  'data'=>$data,	
			  'addresses'=>$addresses,			  
			  'delivery_option'=>CCheckout::deliveryOption(),
			  'address_label'=>CCheckout::addressLabel(),
			  'maps_config'=>CMaps::config(),			  
			);				
		}
		$this->responseJson();
	}
	
	public function actioncheckoutValidateCoordinates()
	{		
		$unit = Yii::app()->params['settings']['home_search_unit_type'];	
		$lat = isset($this->data['lat'])?$this->data['lat']:'';
		$lng = isset($this->data['lng'])?$this->data['lng']:'';
		$new_lat = isset($this->data['new_lat'])?$this->data['new_lat']:'';
		$new_lng = isset($this->data['new_lng'])?$this->data['new_lng']:'';
		$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
				
		if(!empty($cart_uuid)){
			try {
				$merchant_id = CCart::getMerchantId($cart_uuid);
				$merchant = CMerchants::get($merchant_id);
				$unit = $merchant->distance_unit;			    
			} catch (Exception $e) {
				//
			}		
		}				
		$distance = CMaps::getLocalDistance($unit,$lat,$lng,$new_lat,$new_lng);				
		$new_pin = CMaps::reverseGeocoding($new_lat,$new_lng);				
		if($distance=="NaN"){
			$this->code = 1;
			$this->msg = "OK";
		} else if ($distance<0.2) {	
			$this->code = 1;
			$this->msg = "OK";
		} else if ($distance>=0.2) {
			if($unit=="km"){
				if($distance<=0.5){
					$this->code = 1;
			        $this->msg = "OK";
				} else $this->msg[] = t("Pin location is too far from the address");			
			} else $this->msg[] = t("Pin location is too far from the address");			
		}
		$this->details = array(
		  'distance'=>$distance,
		  'data'=> $new_pin
		);
		$this->jsonResponse();
	}
	
	public function actioncheckoutsaveaddress()
	{				
		try {
									
			$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
			$data = isset($this->data['data'])?$this->data['data']:'';
			$place_id = isset($this->data['place_id'])?$this->data['place_id']:'';
			$location_name = isset($this->data['location_name'])?$this->data['location_name']:'';
			$city = isset($this->data['city'])?$this->data['city']:'';
			$street_number = isset($this->data['street_number'])?$this->data['street_number']:'';
			$delivery_instructions = isset($this->data['delivery_instructions'])?$this->data['delivery_instructions']:'';
			$delivery_options = isset($this->data['delivery_options'])?$this->data['delivery_options']:'';
			$address_label = isset($this->data['address_label'])?$this->data['address_label']:'';				

			$address_format_use = isset(Yii::app()->params['settings']['address_format_use'])? (!empty(Yii::app()->params['settings']['address_format_use'])?Yii::app()->params['settings']['address_format_use']:'') :'';
			$address_format_use = !empty($address_format_use)?$address_format_use:1;						
						
			$address = array(); 			
			$new_place_id = isset($data['place_id'])?$data['place_id']:'';
			$address_uuid = isset($data['address_uuid'])?$data['address_uuid']:'';
			$new_lat = isset($data['latitude'])?$data['latitude']:''; 
			$new_lng = isset($data['longitude'])?$data['longitude']:'';
			// $place_id = isset($data['place_id'])?$data['place_id']:'';
									
			$model = AR_client_address::model()->find('address_uuid=:address_uuid AND client_id=:client_id', 
		    array(':address_uuid'=>$address_uuid,'client_id'=>Yii::app()->user->id)); 

			if(!$model){				
				$model = new AR_client_address();
				$model->place_id = $new_place_id;
				$model->client_id = Yii::app()->user->id;
			}
			$map_place = AR_map_places::model()->find('reference_id=:reference_id', 
			array(':reference_id'=>$new_place_id));
		
		    if($model){		    		
				if($address_format_use==2){
					$model->scenario="forms2";
				}								
		    	$model->latitude = $new_lat;
		    	$model->longitude = $new_lng;
		    	$model->location_name = $location_name;
		    	$model->city = !empty($city)?$city:(isset($map_place['city'])?$map_place['city']:'');
		    	$model->street_number = !empty($street_number)?$street_number:(isset($map_place['street_number'])?$map_place['street_number']:'');
		    	$model->delivery_options = $delivery_options;
		    	$model->delivery_instructions = $delivery_instructions;
		    	$model->address_label = $address_label;
		    	$model->place_id = $place_id;
				$model->address1 = isset($this->data['address1'])?$this->data['address1']:(isset($map_place['address1'])?$map_place['address1']:'');
		    	$model->formatted_address = isset($this->data['formatted_address'])?$this->data['formatted_address']:(isset($map_place['formatted_address'])?$map_place['formatted_address']:'');
				$model->address2 = isset($this->data['address2'])?$this->data['address2']:(isset($map_place['address2'])?$map_place['address2']:'');
				$model->postal_code = isset($this->data['postal_code'])?$this->data['postal_code']:(isset($map_place['postal_code'])?$map_place['postal_code']:'');
				$model->company = isset($this->data['company'])?$this->data['company']:'';
				$model->address_format_use = $address_format_use;
		    	
				if($model->save()){
					if(!empty($place_id)){
						CommonUtility::WriteCookie( Yii::app()->params->local_id ,$place_id);  
					 }			 
					 $this->code = 1;
					 $this->msg = "OK";
					 $this->details = [];
				} else $this->msg = CommonUtility::parseError( $model->getErrors());
		    } else $this->msg = t("Address not found");					
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->jsonResponse();
	}
	
	public function actionsetPlaceID()
	{		
		$place_id = isset($this->data['place_id'])?trim($this->data['place_id']):'';
		if(!empty($place_id)){
	       CommonUtility::WriteCookie( Yii::app()->params->local_id ,$place_id);  
	    }
	    $this->code = 1;
		$this->msg = "OK";
		$this->responseJson();
	}
	
	public function actiondeleteAddress()
	{		
		$address_uuid = isset($this->data['address_uuid'])?trim($this->data['address_uuid']):'';
		if(!Yii::app()->user->isGuest){			
			try {
				CClientAddress::delete(Yii::app()->user->id,$address_uuid);
				$this->code = 1; 
				$this->msg = "OK";
			} catch (Exception $e) {
			    $this->msg = t($e->getMessage());
			}
		} else $this->msg = t("User not login or session has expired");
		$this->responseJson();
	}
	
	public function actiongetCheckoutPhone()
	{
		try {
			$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
			$atts = CCart::getAttributesAll($cart_uuid,array('contact_number','contact_number_prefix'));			
			$contact_number = isset($atts['contact_number'])?$atts['contact_number']:'';
			$default_prefix = isset($atts['contact_number_prefix'])?$atts['contact_number_prefix']:63;	
						
			$contact_number = str_replace($default_prefix,"",$contact_number);
			$default_prefix = str_replace("+","",$default_prefix);
			
			$data = AttributesTools::countryMobilePrefix();
			$this->code = 1;
			$this->msg = "OK";			
			$this->details = array(
			  'contact_number_w_prefix'=>isset($atts['contact_number'])?$atts['contact_number']:'',
			  'contact_number'=>$contact_number,
			  'default_prefix'=>$default_prefix,
			  'prefixes'=>$data,
			);
			
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());
		}
		$this->responseJson();
	}
	
	public function actionRequestEmailCode()
	{
		try {
		    		    
		    if(!Yii::app()->user->isGuest){		    
		    	$model = AR_client::model()->find('client_id=:client_id', 
		        array(':client_id'=>Yii::app()->user->id)); 	
		        if($model){		           
		           $digit_code = CommonUtility::generateNumber(4,true);
		           $model->mobile_verification_code = $digit_code;
				   $model->scenario="resend_otp";
		           if($model->save()){		   
		           	   // SEND EMAIL HERE         
			           $this->code = 1;
			           $this->msg = t("We sent a code to {{email_address}}.",array(
			             '{{email_address}}'=> CommonUtility::maskEmail($model->email_address)
			           ));			           
                       if(DEMO_MODE==TRUE){
		    			  $this->details['verification_code']=t("Your verification code is {{code}}",array('{{code}}'=>$digit_code));
		    		   }
		           } else $this->msg = CommonUtility::parseError($model->getErrors());
		        } else $this->msg[] = t("Record not found");
		    } else $this->msg[] = t("Your session has expired please relogin");
		    
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());
		}
		$this->responseJson();
	}
	
	public function action2authVerication()
	{
		try {
			
			$code = isset($this->data['code'])?$this->data['code']:'';
			$model = AR_client::model()->find('client_id=:client_id AND mobile_verification_code=:mobile_verification_code', 
		    array(':client_id'=>Yii::app()->user->id,':mobile_verification_code'=>trim($code) )); 		
		    if($model){
		    	$this->code = 1; $this->msg = "OK";
		    } else $this->msg[] = t("Invalid verification code");			
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());
		}		
		$this->responseJson();
	}
	
	public function actionChangePhone()
	{
		try {
			
		   $cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
		   $data = isset($this->data['data'])?$this->data['data']:'';
		   $code = isset($this->data['code'])?$this->data['code']:'';
		   $mobile_prefix = isset($data['mobile_prefix'])?$data['mobile_prefix']:'';
		   $mobile_number = isset($data['mobile_number'])?$data['mobile_number']:'';
		   		   
		   $model = AR_client::model()->find('client_id=:client_id AND mobile_verification_code=:mobile_verification_code', 
		   array(':client_id'=>Yii::app()->user->id,':mobile_verification_code'=>trim($code) )); 		
		   if($model){
		   	   $model->phone_prefix = $mobile_prefix;
		   	   $model->contact_phone = $mobile_prefix.$mobile_number;
		   	   if($model->save()){	
			   	   CCart::savedAttributes($cart_uuid,'contact_number', $model->contact_phone );
			   	   CCart::savedAttributes($cart_uuid,'contact_number_prefix', $mobile_prefix );
			   	   
			   	   Yii::app()->user->setState('contact_number', $model->contact_phone );
			   	   
			   	   $this->code = 1;
			   	   $this->msg = t("Succesfull change contact number");
			   	   $this->details = array(
			   	     'contact_number'=>$model->contact_phone
			   	   );
		   	   } else $this->msg = CommonUtility::parseError($model->getErrors()); 
		   } else $this->msg[] = t("Invalid verification code");
		   
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());
		}		
		$this->responseJson();
	}
	
	public function actionapplyPromoCode()
	{		
		$promo_code = isset($this->data['promo_code'])?trim($this->data['promo_code']):'';
		$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
		try {
			
			$merchant_id = CCart::getMerchantId($cart_uuid);
			CCart::getContent($cart_uuid,Yii::app()->language);	
			$subtotal = CCart::getSubTotal();
			$sub_total = floatval($subtotal['sub_total']);
			$now = date("Y-m-d");	
			
			$model = AR_voucher::model()->find('voucher_name=:voucher_name', 
		    array(':voucher_name'=>$promo_code)); 		
		    if($model){
		    	
		    	$promo_id = $model->voucher_id;
		    	$voucher_owner = $model->voucher_owner;
		    	$promo_type = 'voucher';
		    	
				$transaction_type = CCart::cartTransaction($cart_uuid,Yii::app()->params->local_transtype,$merchant_id);
		    	$resp = CPromos::applyVoucher( $merchant_id, $promo_id, Yii::app()->user->id , $now , $sub_total , $transaction_type);
		    	$less_amount = $resp['less_amount'];
		    	
		    	$params = array(
				  'name'=>"less voucher",
				  'type'=>$promo_type,
				  'id'=>$promo_id,
				  'target'=>'subtotal',
				  'value'=>"-$less_amount",
				  'voucher_owner'=>$voucher_owner,
				);						
				
				CCart::savedAttributes($cart_uuid,'promo',json_encode($params));
			    CCart::savedAttributes($cart_uuid,'promo_type',$promo_type);
			    CCart::savedAttributes($cart_uuid,'promo_id',$promo_id);
			    
			    $this->code = 1; 
			    $this->msg = "succesful";
			    
		    } else $this->msg = t("Voucher code not found");
					
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}
	
	public function actiongetAppliedPromo()
	{
		$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
		try {
			$promo = array();
			$atts = CCart::getAttributesAll($cart_uuid,array('promo','promo_type','promo_id'));
			if($atts){
				$saving = '';
				if(isset($atts['promo'])){
					if ($promo = json_decode($atts['promo'],true)){												
						if($promo['type']=="offers"){
							
						} elseif ( $promo['type']=="voucher" ){
							$discount_value = isset($promo['value'])?$promo['value']:0;
							$discount_value = $discount_value*-1;	
							$saving = t("You're saving [discount]",array(
							  '[discount]'=>Price_Formatter::formatNumber($discount_value)
							));
						}
					}
				}	
								
				$this->code = 1; $this->msg = "ok";	
				$this->details = array(
				  'data'=>$promo,
				  'saving'=>$saving
				);									
			} else $this->msg = t("No results");	
			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}
	
	public function actionPaymentList()
	{
		try {
			
		   $currency_code = Yii::app()->input->post('currency_code');
		   $cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';		   
		   $base_currency = Price_Formatter::$number_format['currency_code'];		

		   $multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		   $multicurrency_enabled = $multicurrency_enabled==1?true:false;		   	
		   $enabled_hide_payment = isset(Yii::app()->params['settings']['multicurrency_enabled_hide_payment'])?Yii::app()->params['settings']['multicurrency_enabled_hide_payment']:false;		   

		   $hide_payment = $multicurrency_enabled==true? ($enabled_hide_payment==1?true:false) :false;

		   $merchant_id = CCart::getMerchantId($cart_uuid);
		   
		   if($multicurrency_enabled){
			  $options_merchant = OptionsTools::find(['merchant_default_currency'],$merchant_id);						
		      $merchant_default_currency = isset($options_merchant['merchant_default_currency'])?$options_merchant['merchant_default_currency']:'';
		      $merchant_default_currency = !empty($merchant_default_currency)?$merchant_default_currency:$base_currency;
		      $currency_code = !empty($currency_code)?$currency_code: (empty($merchant_default_currency)?$base_currency:$merchant_default_currency);		   
		   }		   		

		   $data = CPayments::PaymentList($merchant_id,false,$hide_payment,$currency_code);		   		   
		   $block_payment_list = ACustomer::getBlockPaymentmethod(Yii::app()->user->id);		   
		   if(is_array($block_payment_list) && count($block_payment_list)>=1){
			  $data = array_values(array_filter($data, function($payment) use ($block_payment_list) {
					return !in_array($payment['payment_code'], $block_payment_list);
			  }));
		   }
		   
		   $this->code = 1;
		   $this->msg = "ok";
		   $this->details = array(		     
		     'data'=>$data
		   );		   
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}
	
	public function actionsavedCards()
	{		
		try {
			
			$expiration_month='';$expiration_yr=''; $error_data = array(); $error = array();
			$card_name = isset($this->data['card_name'])?$this->data['card_name']:'';
			$credit_card_number = isset($this->data['credit_card_number'])?$this->data['credit_card_number']:'';
			$expiry_date = isset($this->data['expiry_date'])?$this->data['expiry_date']:'';
			$cvv = isset($this->data['cvv'])?$this->data['cvv']:'';
			$billing_address = isset($this->data['billing_address'])?$this->data['billing_address']:'';
			$payment_code = isset($this->data['payment_code'])?$this->data['payment_code']:'';
			$card_uuid = isset($this->data['card_uuid'])?$this->data['card_uuid']:'';
			$merchant_id = isset($this->data['merchant_id'])?$this->data['merchant_id']:'';
					
			if(empty($card_uuid)){
				$model=new AR_client_cc;
				$model->scenario='add';
			} else {
				$model = AR_client_cc::model()->find('client_id=:client_id AND card_uuid=:card_uuid', 				
			    array(
			      ':client_id'=>Yii::app()->user->id,
			      ':card_uuid'=>$card_uuid
			    )); 	
			    if(!$model){
			    	$this->msg[] = t("Record not found");
			    	$this->responseJson();
			    }
			    $model->scenario='update';
			}
						
			$model->client_id = Yii::app()->user->id;
			$model->payment_code = $payment_code;
			$model->card_name = $card_name;
			$model->credit_card_number = str_replace(" ","",$credit_card_number);
			$model->expiration = $expiry_date;
			$model->cvv = $cvv;
			$model->billing_address = $billing_address;
			$model->merchant_id = $merchant_id;

			if($model->save()){
	    		$this->code = 1;
		    	$this->msg = "OK";	
	    	} else $this->msg = CommonUtility::parseError( $model->getErrors());
			
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		    
		}				
		$this->responseJson();
	}
	
	public function actionSavedPaymentList()
	{		
		try {
			
			$default_payment_uuid = ''; $default_payment = [];
			$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		    $multicurrency_enabled = $multicurrency_enabled==1?true:false;		   	
		    $enabled_hide_payment = isset(Yii::app()->params['settings']['multicurrency_enabled_hide_payment'])?Yii::app()->params['settings']['multicurrency_enabled_hide_payment']:false;
			$hide_payment = $multicurrency_enabled==true? ($enabled_hide_payment==1?true:false) :false;

			$currency_code = Yii::app()->input->post('currency_code');
			$base_currency = Price_Formatter::$number_format['currency_code'];
			
			$data_merchant = CCart::getMerchantForCredentials($cart_uuid);			
			$merchant_id = isset($data_merchant['merchant_id'])?$data_merchant['merchant_id']:0;			

			if($multicurrency_enabled){
				$options_merchant = OptionsTools::find(['merchant_default_currency'],$merchant_id);						
		        $merchant_default_currency = isset($options_merchant['merchant_default_currency'])?$options_merchant['merchant_default_currency']:'';
		        $merchant_default_currency = !empty($merchant_default_currency)?$merchant_default_currency:$base_currency;
		        $currency_code = !empty($currency_code)?$currency_code: (empty($merchant_default_currency)?$base_currency:$merchant_default_currency);		   
			}
									
			if($data_merchant['merchant_type']==2){	
				$merchant_id=0;			
			}

			$available_payment = [];
			$data = CPayments::SavedPaymentList( Yii::app()->user->id , $data_merchant['merchant_type'] , 
			$data_merchant['merchant_id'],$hide_payment,$currency_code );		
			foreach ($data as $items) {
				$available_payment[]=$items['payment_code'];
			}	
						
			$model = AR_client_payment_method::model()->find(
			'client_id=:client_id AND as_default=:as_default AND merchant_id=:merchant_id ', 
		    array(
		      ':client_id'=>Yii::app()->user->id,		      
		      ':as_default'=>1,
		      ':merchant_id'=>$merchant_id
		    )); 	
		    if($model){						
				$hide_payment_list = [];								
				$match = current(array_filter($data, fn($p) => $p['payment_code'] === $model->payment_code));
				$attr_required = $match['attr_required'] ?? false;
				$default_payment = [
					'payment_code'=>$model->payment_code,
					'attr_required'=>$attr_required==1?true:false
				];
				if($hide_payment){
					try {
						$hide_payment_list = CMulticurrency::getHidePaymentList($currency_code);																		
						if(!in_array($model->payment_code,(array)$hide_payment_list)){
							$default_payment_uuid=$model->payment_uuid;
						}
					} catch (Exception $e) {
						$default_payment_uuid=$model->payment_uuid;
					}			
				} else $default_payment_uuid=$model->payment_uuid;		  
								
				if(!in_array($model->payment_code,(array)$available_payment)){
					$default_payment_uuid='';
				}						    	
		    }		    
						
			$this->code = 1;
		    $this->msg = "ok";
		    $this->details = array(
		      'default_payment_uuid'=>$default_payment_uuid,
			  'default_payment'=>$default_payment,
		      'data'=>$data,
		    );		    
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		    
		}	
		$this->responseJson();
	}
	
	public function actiondeleteSavedPaymentMethod()
	{
		try {
		   $payment_uuid = isset($this->data['payment_uuid'])?$this->data['payment_uuid']:'';
		   $payment_code = isset($this->data['payment_code'])?$this->data['payment_code']:'';
		   CPayments::delete(Yii::app()->user->id,$payment_uuid);
		   $this->code = 1;
		   $this->msg = "ok";
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		    
		}	
		$this->responseJson();
	}
	
	public function actionSavedPaymentProvider()
	{		
		try {
			
			$payment_code = isset($this->data['payment_code'])?$this->data['payment_code']:'';
			$merchant_id = isset($this->data['merchant_id'])?$this->data['merchant_id']:'';
			
			$payment = AR_payment_gateway::model()->find('payment_code=:payment_code', 
		    array(':payment_code'=>$payment_code)); 	
		    
		    if($payment){		    	
				$model = new AR_client_payment_method;
				$model->scenario = "insert";
				$model->client_id = Yii::app()->user->id;
				$model->payment_code = $payment_code;
				$model->as_default = intval(1);
				$model->attr1 = $payment?$payment->payment_name:'unknown';	
				$model->merchant_id = intval($merchant_id);
				if($model->save()){
					$this->code = 1;
		    		$this->msg = t("Succesful");
				} else $this->msg = CommonUtility::parseError($model->getErrors());
		    } else $this->msg[] = t("Payment provider not found");
			
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		    
		}				
		$this->responseJson();
	}
	
	public function actionSetDefaultPayment()
	{			
		try {	
			$payment_uuid = isset($this->data['payment_uuid'])?$this->data['payment_uuid']:'';
			$model = AR_client_payment_method::model()->find('client_id=:client_id AND payment_uuid=:payment_uuid', 
			array(
			  ':client_id'=>Yii::app()->user->id,
			  ':payment_uuid'=>$payment_uuid
			)); 		
			if($model){
				$model->as_default = 1;
				$model->save();
				$this->code = 1;
		    	$this->msg = t("Succesful");
			} else $this->msg = t("Record not found");			
		    
	    } catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		    
		}	
		
		$this->responseJson();
	}
	
	public function actionPlaceOrder()
	{		
											
		$setttings = Yii::app()->params['settings'];
		$home_search_mode = isset($setttings['home_search_mode'])?$setttings['home_search_mode']:'address';	
		$location_searchtype = isset($setttings['location_searchtype'])?$setttings['location_searchtype']:'';		
		$strict_to_wallet = $setttings['strict_to_wallet'] ?? false;
		$strict_to_wallet = $strict_to_wallet==1?true:false;
		
		$use_local_distance = $setttings['use_local_distance'] ?? false;
		$use_local_distance = $use_local_distance==1?true:false;
		
		$local_id = CommonUtility::getCookie(Yii::app()->params->local_id);
		$cart_uuid = isset($this->data['cart_uuid'])?trim($this->data['cart_uuid']):'';
		$payment_uuid = isset($this->data['payment_uuid'])?trim($this->data['payment_uuid']):'';		
		$payment_change = isset($this->data['payment_change'])?floatval($this->data['payment_change']):0;
		$currency_code = isset($this->data['currency_code'])?trim($this->data['currency_code']):'';		
		$base_currency = Price_Formatter::$number_format['currency_code'];		

		$use_digital_wallet = isset($this->data['use_digital_wallet'])?intval($this->data['use_digital_wallet']):false;
		$use_digital_wallet = $use_digital_wallet==1?true:false;

		if($strict_to_wallet){
			$use_digital_wallet = true;
		}		

		$room_uuid = isset($this->data['room_uuid'])?trim($this->data['room_uuid']):'';	
		$table_uuid = isset($this->data['table_uuid'])?trim($this->data['table_uuid']):'';	
		$guest_number = isset($this->data['guest_number'])?intval($this->data['guest_number']):0;	
		
		$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		$multicurrency_enabled = $multicurrency_enabled==1?true:false;	
		$enabled_checkout_currency = isset(Yii::app()->params['settings']['multicurrency_enabled_checkout_currency'])?Yii::app()->params['settings']['multicurrency_enabled_checkout_currency']:false;
		$enabled_force = $multicurrency_enabled==true? ($enabled_checkout_currency==1?true:false) :false;		

		$address_uuid = isset($this->data['address_uuid'])?trim($this->data['address_uuid']):'';		

		$payload = array(
		   'items','merchant_info','service_fee',
		   'delivery_fee','packaging','tax','tips','checkout','discount','distance_new',
		   'summary','total','card_fee','points','points_discount'
	    );		
		if($use_local_distance){
			unset($payload['distance_new']);
			$payload[] = 'distance_local_new';
		}		
	    		
	    $unit = Yii::app()->params['settings']['home_search_unit_type']; 
	    $distance = 0; 	    
		$error = array(); 
		$minimum_order = 0; 
		$maximum_order=0;
		$merchant_info = array(); 		
		$distance_covered=0;
		$merchant_lat = ''; 
		$merchant_lng=''; 
		$out_of_range = false;
		$address_component = array();
		$commission = 0;
		$commission_based = ''; 
		$merchant_id = 0; 
		$merchant_earning = 0; 
		$total_discount = 0; 
		$service_fee = 0; 
		$delivery_fee = 0; 
		$packagin_fee = 0; 
		$tip = 0;
		$total_tax = 0;
		$tax = 0;
		$promo_details = array();
		$summary = array();
		$offer_total = 0;
		$tax_type = '';
        $tax_condition = '';
		$small_order_fee = 0;
		$self_delivery = false;		
		$exchange_rate = 1;		
		$exchange_rate_use_currency_to_admin = 1;
		$exchange_rate_merchant_to_admin = 1; 
		$exchange_rate_base_customer = 1;
		$exchange_rate_admin_to_merchant = 1;		
		$payment_exchange_rate = 1;
		$card_fee = 0;
		$points_to_earn = 0; 
		$points_label = ''; 
		$points_earned=0;
		$sub_total_without_cnd = 0;
		$booking_enabled = false;

		$digital_wallet_balance = 0;
		$amount_due = 0;
		$wallet_use_amount = 0;
		$use_partial_payment = false;
		$free_delivery_on_first_order = false;	
		$location = [];
		$preparation_time_estimation = 0;
		$delivery_time_estimation = 0;
				
		/*CHECK IF MERCHANT IS OPEN*/
		try {
			$merchant_id = CCart::getMerchantId($cart_uuid);	
			
			// CHECK IF MERCHANT HAS DIFFERENT TIMEZONE
			$options_merchant = OptionsTools::find(['merchant_timezone',
			'booking_enabled','free_delivery_on_first_order','merchant_whatsapp_phone_number','merchant_enabled_whatsapp'
		    ],$merchant_id);
			$merchant_timezone = isset($options_merchant['merchant_timezone'])?$options_merchant['merchant_timezone']:'';
			$booking_enabled = isset($options_merchant['booking_enabled'])?$options_merchant['booking_enabled']:'';
			$booking_enabled = $booking_enabled==1?true:false;			

			$free_delivery_on_first_order = isset($options_merchant['free_delivery_on_first_order'])?$options_merchant['free_delivery_on_first_order']:false;
			$free_delivery_on_first_order = $free_delivery_on_first_order==1?true:false;

			$merchant_whatsapp_phone_number = isset($options_merchant['merchant_whatsapp_phone_number'])?$options_merchant['merchant_whatsapp_phone_number']:'';
			$merchant_enabled_whatsapp = isset($options_merchant['merchant_enabled_whatsapp'])?$options_merchant['merchant_enabled_whatsapp']:false;
			$merchant_enabled_whatsapp = $merchant_enabled_whatsapp==1?true:false;			

			if(!empty($merchant_timezone)){
				Yii::app()->timezone = $merchant_timezone;
			}
			
			$date = date("Y-m-d");
			$time_now = date("H:i");
			
			$choosen_delivery = isset($this->data['choosen_delivery'])?$this->data['choosen_delivery']:'';		
			$whento_deliver = isset($choosen_delivery['whento_deliver'])?$choosen_delivery['whento_deliver']:'';
			
			if(is_array($choosen_delivery) && count($choosen_delivery)>=1){
				if($whento_deliver=="schedule"){
					$date = isset($choosen_delivery['delivery_date'])?$choosen_delivery['delivery_date']:$date;
					$time_now = isset($choosen_delivery['delivery_time'])?$choosen_delivery['delivery_time']['start_time']:$time_now;
				}
		    } else {
				$atts_delivery = CCart::getAttributesAll($cart_uuid,array('whento_deliver','delivery_date','delivery_time'));						
				$whento_deliver = isset($atts_delivery['whento_deliver'])?$atts_delivery['whento_deliver']:'';
				if($whento_deliver=="schedule"){
					$date = isset($atts_delivery['delivery_date'])?$atts_delivery['delivery_date']:$date;
					$time_now = isset($atts_delivery['delivery_time'])?CCheckout::jsonTimeToSingleTime($atts_delivery['delivery_time']):$time_now;
				}
			}
						
			$datetime_to = date("Y-m-d g:i:s a",strtotime("$date $time_now"));			
			CMerchantListingV1::checkCurrentTime( date("Y-m-d g:i:s a") , $datetime_to);			
			
			$resp = CMerchantListingV1::checkStoreOpen($merchant_id,$date,$time_now);			
			if($resp['merchant_open_status']<=0){
				$this->msg[] = t("This store is closed right now, but you can schedule an order later.");
				$this->responseJson();
			}					
						
			CMerchantListingV1::storeAvailableByID($merchant_id);
			
									
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		    
		    $this->responseJson();
		}	

				
		try {

			$merchant_id = CCart::getMerchantId($cart_uuid);
		 	$options_merchant = OptionsTools::find(['merchant_timezone','merchant_default_currency'],$merchant_id);						
		    $merchant_default_currency = isset($options_merchant['merchant_default_currency'])?$options_merchant['merchant_default_currency']:'';
			$merchant_default_currency = !empty($merchant_default_currency)?$merchant_default_currency:$base_currency;			
			$currency_code = !empty($currency_code)?$currency_code: (empty($merchant_default_currency)?$base_currency:$merchant_default_currency) ;

			$points_enabled = isset(Yii::app()->params['settings']['points_enabled'])?Yii::app()->params['settings']['points_enabled']:false;
		    $points_enabled = $points_enabled==1?true:false;
		    $points_earning_rule = isset(Yii::app()->params['settings']['points_earning_rule'])?Yii::app()->params['settings']['points_earning_rule']:'sub_total';									
			$points_earning_points = isset(Yii::app()->params['settings']['points_earning_points'])?Yii::app()->params['settings']['points_earning_points']:0;	
			$points_minimum_purchase = isset(Yii::app()->params['settings']['points_minimum_purchase'])?Yii::app()->params['settings']['points_minimum_purchase']:0;	
            $points_maximum_purchase = isset(Yii::app()->params['settings']['points_maximum_purchase'])?Yii::app()->params['settings']['points_maximum_purchase']:0;

			$whatsapp_use_api = isset(Yii::app()->params['settings']['whatsapp_use_api'])?Yii::app()->params['settings']['whatsapp_use_api']:false;
			$whatsapp_use_api = $whatsapp_use_api==1?true:false;
						
			CCart::setExchangeRate($exchange_rate);		
			CCart::setPointsRate($points_enabled,$points_earning_rule,$points_earning_points,$points_minimum_purchase,$points_maximum_purchase);
			
			if($multicurrency_enabled){
				if($merchant_default_currency!=$currency_code){
					$exchange_rate_base_customer = CMulticurrency::getExchangeRate($merchant_default_currency,$currency_code);
					$payment_exchange_rate = CMulticurrency::getExchangeRate($currency_code,$merchant_default_currency);
				}
				if($merchant_default_currency!=$base_currency){
					$exchange_rate_merchant_to_admin = CMulticurrency::getExchangeRate($merchant_default_currency,$base_currency);
					$exchange_rate_admin_to_merchant = CMulticurrency::getExchangeRate($base_currency,$merchant_default_currency);
				}
				if($base_currency!=$merchant_default_currency){
					//dump("$merchant_default_currency=>$base_currency");
					$exchange_rate_use_currency_to_admin = CMulticurrency::getExchangeRate($merchant_default_currency,$base_currency);
				}				
			} else {
				$merchant_default_currency = $base_currency;
				$currency_code = $base_currency;
			}
			
			CCart::setAdminExchangeRate($exchange_rate_use_currency_to_admin);

			// GET CLIENT ADDRESS AND SAVE LOCATION NAME / DELIVERY OPTIONS AND INSTRUCSTIONS			
			if($home_search_mode=="location"){				
				try {
					$address_component = Clocations::getAddressDetails(Yii::app()->user->id,$address_uuid);					
					$location = [
						'state_id'=>$address_component['state_id'],
						'city_id'=>$address_component['city_id'],
						'area_id'=>$address_component['area_id'],
						'zip_code'=>$address_component['zip_code'],
					];					
				} catch (Exception $e) {}	
			} else {
				$client_address = AR_client_address::model()->find('place_id=:place_id AND client_id=:client_id', 
				array(':place_id'=>$local_id,'client_id'=>Yii::app()->user->id)); 
				if($client_address){										
					$latitude = $client_address->latitude;
					$longitude = $client_address->longitude;
					$address_format_use = isset(Yii::app()->params['settings']['address_format_use'])? (!empty(Yii::app()->params['settings']['address_format_use'])?Yii::app()->params['settings']['address_format_use']:'') :'';
					$address_format_use = !empty($address_format_use)?$address_format_use:1;

					$address_component['latitude'] = $client_address->latitude;
				    $address_component['longitude'] = $client_address->longitude;
					$address_component['formatted_address'] = $client_address->formatted_address;
					$address_component['formattedAddress'] = $client_address->formattedAddress;
					$address_component['address1'] = $client_address->address1;
					$address_component['address2'] = $client_address->address2;
					$address_component['country_code'] = $client_address->country_code;
					
					$address_component['location_name']	 = $client_address->location_name;
					$address_component['delivery_options']	 = $client_address->delivery_options;
				        $address_component['city'] = $client_address->city;
					$address_component['delivery_instructions']	 = $client_address->delivery_instructions;
					$address_component['address_label']	 = $client_address->address_label;				
					$address_component['postal_code']	 = $client_address->postal_code;
					$address_component['company']	 = $client_address->company;
					$address_component['address_format_use'] =  $address_format_use;
					$address_component['custom_field1'] =  $client_address->custom_field1;
					$address_component['custom_field2'] =  $client_address->custom_field2;
				}					
			}			
									
			require_once 'get-cart.php';	

			if(!isset($show_prices_commission)){
				$show_prices_commission = false;
			}
			$merchant_total  = CCart::getOriginaltotal();
			$commission_total = $sub_total - $merchant_subtotal;
		

			// DIGITAL WALLET
			try {
				if($use_digital_wallet){
					$digital_wallet_balance = CDigitalWallet::getAvailableBalance(Yii::app()->user->id);
					$digital_wallet_balance = $digital_wallet_balance*$exchange_rate_admin_to_merchant;					
					
					if($strict_to_wallet){
						$amount_due = CDigitalWallet::calculateAmountDue($total,$digital_wallet_balance);
						if($amount_due>0){
							$this->msg[] = t("You don't have enough balance in your wallet to place this order.");
				            $this->responseJson();
						}
						$wallet_use_amount = $total;
					} else {
						$amount_due = CDigitalWallet::canContinueWithWallet($total,$digital_wallet_balance,$payment_uuid);	
						if($amount_due>0){						
						   $wallet_use_amount = $digital_wallet_balance;
							$use_partial_payment = true;
						} else {
							$wallet_use_amount = $total;
						}					
					}					
				}					
			} catch (Exception $e) {
				$this->msg[] = t($e->getMessage());		    
				$this->responseJson();
			}									
								
								
			$include_utensils = isset($this->data['include_utensils'])?$this->data['include_utensils']:false;
		    $include_utensils = $include_utensils=="true"?true:false;
		    CCart::savedAttributes($cart_uuid,'include_utensils',$include_utensils);
						
			if(is_array($error) && count($error)>=1){				
				$this->msg = $error;
			} else {					
														
				$merchant_type = $data['merchant']['merchant_type'];
				$commision_type = $data['merchant']['commision_type'];				
				$merchant_commission = $data['merchant']['commission'];				
												
				$sub_total_based  = CCart::getSubTotal_TobeCommission();				
				$tax_total =  CCart::getTotalTax();					
				$resp_comm = CCommission::getCommissionValueNew([
					'merchant_id'=>$merchant_id,
					'transaction_type'=>$transaction_type,
					'merchant_type'=>$merchant_type,
					'commision_type'=>$commision_type,
					'merchant_commission'=>$merchant_commission,
					'sub_total'=>$sub_total_based,
					'sub_total_without_cnd'=>$sub_total_without_cnd,
					'total'=>$total,
					'service_fee'=>$service_fee,
					'delivery_fee'=>$delivery_fee,
					'tax_settings'=>$tax_settings,
					'tax_total'=>$tax_total,
					'self_delivery'=>$self_delivery,					
				]);				
				if($resp_comm){							
					$commission_based = $resp_comm['commission_based'];
					$commission = $resp_comm['commission'];
					$merchant_earning = $resp_comm['merchant_earning'];
					$merchant_commission = $resp_comm['commission_value'];
					$commision_type = $resp_comm['commission_type'];
				}											
				
				$atts = CCart::getAttributesAll($cart_uuid,array('whento_deliver',
				  'promo','promo_type','promo_id','tips','delivery_date','delivery_time'
				));						
												
				if($use_digital_wallet && !$use_partial_payment){					
					$payments = ['payment_code'=>CDigitalWallet::transactionName()];
				} else $payments = CPayments::getPaymentMethod( $payment_uuid, Yii::app()->user->id );	
					
				$sub_total_less_discount  = CCart::getSubTotal_lessDiscount();				
											
				if(is_array($summary) && count($summary)>=1){	
					foreach ($summary as $summary_item) {
						switch ($summary_item['type']) {
							case "voucher":								
								$total_discount = CCart::cleanNumber($summary_item['raw']);
								break;
						
							case "offers":	
							    $total_discount += CCart::cleanNumber($summary_item['raw']);
								$offer_total = CCart::cleanNumber($summary_item['raw']);							    
								break;
								
							case "service_fee":
								$service_fee = CCart::cleanNumber($summary_item['raw']);
								break;
								
							case "delivery_fee":
								$delivery_fee = CCart::cleanNumber($summary_item['raw']);
								break;	
							
							case "packaging_fee":
								$packagin_fee = CCart::cleanNumber($summary_item['raw']);
								break;			
								
							case "tip":
								$tip = CCart::cleanNumber($summary_item['raw']);
								break;				
								
							case "tax":
								$total_tax+= CCart::cleanNumber($summary_item['raw']);
								break;					

							case "points_discount":								
								$total_discount += CCart::cleanNumber($summary_item['raw']);
								$points_earned = CCart::cleanNumber($summary_item['raw']);
								break;					
									
							default:
								break;
						}
					}				
				}
				
				if($tax_enabled){					
					$tax_type = CCart::getTaxType();									
					$tax_condition = CCart::getTaxCondition();					
					if($tax_type=="standard" || $tax_type=="euro"){			
						if(is_array($tax_condition) && count($tax_condition)>=1){
							foreach ($tax_condition as $tax_item_cond) {
								$tax = isset($tax_item_cond['tax_rate'])?$tax_item_cond['tax_rate']:0;
							}
						}
					}									
				}			
				
				if($multicurrency_enabled){
				   $payment_change = $currency_code==$merchant_default_currency ? $payment_change : ($payment_change*$payment_exchange_rate);
				}
						
												
				$model = new AR_ordernew;
				$model->scenario = $transaction_type;				
				$model->order_uuid = CommonUtility::generateUIID();
				$model->merchant_id = intval($merchant_id);	
				$model->client_id = intval(Yii::app()->user->id);
				$model->service_code = $transaction_type;
				$model->payment_code = isset($payments['payment_code'])?$payments['payment_code']:'';				
				$model->payment_change = $payment_change;
				$model->validate_payment_change = true;				
				$model->total_discount = floatval($total_discount);
				$model->points = floatval($points_earned);
				$model->sub_total = floatval($sub_total);
				$model->sub_total_less_discount = floatval($sub_total_less_discount);
				$model->service_fee = floatval($service_fee);
				$model->small_order_fee = floatval($small_order_fee);
				$model->delivery_fee = floatval($delivery_fee);
				$model->packaging_fee = floatval($packagin_fee);
				$model->card_fee = floatval($card_fee);
				$model->tax_type = $tax_type;
				$model->tax = floatval($tax);
				$model->tax_total = floatval($total_tax);				
				$model->courier_tip = floatval($tip);				
				$model->total = floatval($total);
				$model->total_original = floatval($total);								
				$model->amount_due = floatval($amount_due);
				$model->wallet_amount = floatval($wallet_use_amount);

				$model->booking_enabled = $booking_enabled;
				$model->room_id = $room_uuid;
			    $model->table_id = $table_uuid;
				$model->guest_number = $guest_number;
				
				if(is_array($promo_details) && count($promo_details)>=1){					
					if($promo_details['promo_type']=="voucher"){
						$model->promo_code = $promo_details['voucher_name'];
						$model->promo_total = $promo_details['less_amount'];
						$model->promo_owner = $promo_details['voucher_owner'];
					} elseif ( $promo_details['promo_type']=="offers" ){												
						$offer_cap = isset($promo_details['max_discount_cap'])?$promo_details['max_discount_cap']:0;
						$model->offer_discount = $promo_details['less_amount'];
						$model->offer_total = floatval($offer_total);
						$model->offer_cap = floatval($offer_cap);
					}
				}
				
				$delivery_date_time = null;
				$model->whento_deliver = isset($atts['whento_deliver'])?$atts['whento_deliver']:'';
				if($model->whento_deliver=="now"){
					$model->delivery_date = CommonUtility::dateNow();
					$delivery_date_time = CommonUtility::dateNow();
				} else {
					$model->delivery_date = isset($atts['delivery_date'])?$atts['delivery_date']:'';
					$model->delivery_time = isset($atts['delivery_time'])?CCheckout::jsonTimeToSingleTime($atts['delivery_time']):'';
					$model->delivery_time_end = isset($atts['delivery_time'])?CCheckout::jsonTimeToSingleTime($atts['delivery_time'],'end_time'):'';
					$delivery_date_time = "$model->delivery_date $model->delivery_time";
				}
				$model->delivery_date_time = $delivery_date_time;				
												
				$model->commission_type = $commision_type;
				$model->commission_value = $merchant_commission;
				$model->commission_based = $commission_based;
				$model->commission = floatval($commission);
				$model->commission_original = floatval($commission);
				$model->merchant_earning = floatval($merchant_earning);	
				$model->merchant_earning_original = floatval($merchant_earning);	
				$model->formatted_address = isset($address_component['formatted_address'])?$address_component['formatted_address']:'';
				
				$metas = CCart::getAttributesAll($cart_uuid,
				  array('promo','promo_type','promo_id','tips',
				  'cash_change','customer_name','contact_number','contact_email','include_utensils','point_discount'
				  )
				);

				// GET ESTIMATION				
				$filter = [
					'merchant_id'=>$merchant_id,
					'distance'=>$distance,
					'shipping_type'=>'standard',
					'charges_type'=>$charge_type
				];
				$currentDateTime = date('Y-m-d H:i:s');
				$whenDelivery = isset($atts['whento_deliver'])?$atts['whento_deliver']:'';				
				if($whenDelivery=="schedule"){
					$deliveryDate = isset($atts['delivery_date'])?$atts['delivery_date']:'';
				    $deliveryTime = isset($atts['delivery_time'])?CCheckout::jsonTimeToSingleTime($atts['delivery_time'],'end_time'):'';				
					$currentDateTime = date('Y-m-d H:i:s', strtotime("$deliveryDate $deliveryTime"));
				} 												
				//$tracking_estimation = CCart::getTimeEstimation($filter,$transaction_type,$currentDateTime);				

				$metas['payment_change'] = floatval($payment_change);
				$metas['self_delivery'] = $self_delivery==true?1:0;	
				$metas['points_to_earn'] = floatval($points_to_earn);		
				// $metas['tracking_start'] = isset($tracking_estimation['tracking_start'])?$tracking_estimation['tracking_start']:'';
				// $metas['tracking_end'] = isset($tracking_estimation['tracking_end'])?$tracking_estimation['tracking_end']:'';
				$metas['timezone'] = Yii::app()->timezone;
				
				if($transaction_type=="dinein"){
					$metas['guest_number'] = $guest_number;
					try {									
						$model_room = CBooking::getRoom($room_uuid); 
						$metas['room_id'] = $model_room->room_id;
					} catch (Exception $e) {											
					}
	
					try {			
						$model_table = CBooking::getTable($table_uuid); 					
						$metas['table_id'] = $model_table->table_id;
					} catch (Exception $e) {					
					}
				}				

				if($home_search_mode=="address"){
					$metas['order_version'] = 1;
				}				
				
				/*LINE ITEMS*/
				$model->items = $data['items'];				
				$model->meta = $metas;
				$model->address_component = $address_component;
				$model->cart_uuid = $cart_uuid;
				
				$model->tax_use = $tax_settings;				
				$model->tax_for_delivery = $tax_delivery;
				$model->payment_uuid  = $payment_uuid;
				
				$model->base_currency_code = $merchant_default_currency;
				$model->use_currency_code = $currency_code;		
				$model->admin_base_currency = $base_currency;			
				
				$model->exchange_rate = floatval($exchange_rate_base_customer);
				$model->exchange_rate_use_currency_to_admin = floatval($exchange_rate_use_currency_to_admin);
				$model->exchange_rate_merchant_to_admin = floatval($exchange_rate_merchant_to_admin);												
				$model->exchange_rate_admin_to_merchant = floatval($exchange_rate_admin_to_merchant);				

				$model->preparation_time_estimation = $preparation_time_estimation;
				$model->delivery_time_estimation = $delivery_time_estimation;
												
				if($model->save()){
											
					$redirect = Yii::app()->createAbsoluteUrl("orders/index",array(
					   'order_uuid'=>$model->order_uuid
					));					
									
					/*EXECUTE MODULES*/						
					$payment_instructions = Yii::app()->getModule($model->payment_code)->paymentInstructions();
					if($payment_instructions['method']=="offline"){
						Yii::app()->getModule($model->payment_code)->savedTransaction($model);							
					}									
					
					$order_bw = OptionsTools::find(array('bwusit'));
					$order_bw = isset($order_bw['bwusit'])?$order_bw['bwusit']:0;
									
					if($model->amount_due>0){
						$total = Price_Formatter::convertToRaw($model->amount_due);
					} else $total = Price_Formatter::convertToRaw($model->total);
					
					$use_currency_code = $model->use_currency_code;					
		            $total_exchange = floatval(Price_Formatter::convertToRaw( ($total*$exchange_rate_base_customer) ));											
					if($enabled_force){
						if($force_result = CMulticurrency::getForceCheckoutCurrency($model->payment_code,$use_currency_code)){						   						   
						   $use_currency_code = $force_result['to_currency'];
						   $total_exchange = Price_Formatter::convertToRaw($total_exchange*$force_result['exchange_rate'],2);
						}
					}			  	

					
					$payment_url = CommonUtility::getHomebaseUrl()."/$model->payment_code/api/createcheckout?".http_build_query([
						'order_uuid'=>$model->order_uuid,
						'cart_uuid'=>$cart_uuid,
						'payment_uuid'=>$payment_uuid,
						'request_from'=>"website",						
						'return_url'=>'',
					]);
																							
					$this->code = 1;
					$this->msg = t("Your Order has been place");
					$this->details = array(  
					  'order_uuid' => $model->order_uuid,
					  'redirect'=>$redirect,
					  'payment_code'=>$model->payment_code,
					  'payment_uuid'=>$payment_uuid,
					  'payment_instructions'=>$payment_instructions,		
					  'order_bw'=>$order_bw,										  
					  'force_payment_data'=>[		
						'payment_url'=>$payment_url,						 
						 'enabled_force'=>$enabled_force,
						 'use_currency_code'=>$use_currency_code,
						 'total_exchange'=>$total_exchange,
						 'reference_id'=>$model->order_uuid,	
						 'cart_uuid'=>$cart_uuid					 
					  ],					  
					);						
				} else {					
					if ( $error = CommonUtility::parseError( $model->getErrors()) ){				
						$this->msg = $error;						
					} else $this->msg[] = array('invalid error');
				}				
			}		
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		    
		}					
		$this->responseJson();
	}
	
	public function actiongetOrder()
	{		
		try {
			
		   $order_uuid = isset($this->data['order_uuid'])?trim($this->data['order_uuid']):'';		   
		   $model_order = COrders::get($order_uuid);
		   $merchant_id = $model_order->merchant_id;
		   $merchant_info = COrders::getMerchant($merchant_id,Yii::app()->language);			   

		   $exchange_rate = 1;
		   if($model_order->base_currency_code!=$model_order->use_currency_code){
			    $exchange_rate = $model_order->exchange_rate>0?$model_order->exchange_rate:1;
				Price_Formatter::init($model_order->use_currency_code);			
		   } else {
			    Price_Formatter::init($model_order->use_currency_code);
		   }		   		   
		   
		   $website_twentyfour_format = Yii::app()->params['settings']['website_twentyfour_format'] ?? false;
		   $website_twentyfour_format = $website_twentyfour_format==1?true:false;		   
		   COrders::setTimeformat($website_twentyfour_format);

		   COrders::setExchangeRate($exchange_rate);
		   
		   COrders::getContent($order_uuid,Yii::app()->language);
		   $items = COrders::getItemsOnly();		   
		   $meta  = COrders::orderMeta();		   
		   $order_id = COrders::getOrderID();
		   $items_count = COrders::itemCount($order_id);
		   $progress = CTrackingOrder::getProgress($order_uuid , date("Y-m-d g:i:s a") );		   
		   $order_info  = COrders::orderInfo(Yii::app()->language,date("Y-m-d"));		   
		   $order_info  = isset($order_info['order_info'])?$order_info['order_info']:'';
		   $order_type = isset($order_info['order_type'])?$order_info['order_type']:'';    			   
		   		   
		   $subtotal = COrders::getSubTotal();
		   $subtotal = isset($subtotal['sub_total'])?$subtotal['sub_total']:0;
		   $subtotal = Price_Formatter::formatNumber(floatval($subtotal));
		   $order_info['sub_total'] = $subtotal;
		   
		   $instructions = CTrackingOrder::getInstructions($merchant_id,$order_type);

		   $points_to_earn = isset($meta['points_to_earn'])? ($meta['points_to_earn']>0?$meta['points_to_earn']:0) :0;		   
		   $points_label  = $points_to_earn>0 ? t("This order will earn you {points} points upon completion!",['{points}'=>$points_to_earn]) : '';

		   $enabled_review = isset(Yii::app()->params['settings']['enabled_review'])?Yii::app()->params['settings']['enabled_review']:'';
		   $enabled_review = $enabled_review==1?true:false;		   		   		   
		   $this->code = 1;
		   $this->msg = "Ok";
		   $this->details = array(
		     'merchant_info'=>$merchant_info,
		     'order_info'=>$order_info,
		     'items_count'=>$items_count,		     
		     'items'=>$items,
		     'meta'=>$meta,		    			 
		     'progress'=>$progress,
		     'instructions'=>$instructions,
		     'maps_config'=>CMaps::config(),
			 'points_label'=>$points_label,
			 'enabled_review'=>$enabled_review			 
		   );		   
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());
		}	
		$this->responseJson();
	}
	
	public function actionorderHistory()
	{	     
	     try {
	     	  	     	  
	     	  $page = isset($this->data['page'])?intval($this->data['page']):'';	     
	     	  $q = isset($this->data['q'])?trim($this->data['q']):'';
	     	     	  
	     	  $offset = 0; $show_next_page = false;
	     	  $limit = Yii::app()->params->list_limit;
	     	  $total_rows = COrders::orderHistoryTotal(Yii::app()->user->id);    	
	     	  	          
	          $pages = new CPagination($total_rows);
			  $pages->pageSize = $limit;
			  $pages->setCurrentPage($page);
			  $offset = $pages->getOffset();	
			  $page_count = $pages->getPageCount();
									
			  if($page_count > ($page+1) ){
				  $show_next_page = true;
			  }   
			  			  			  			  
			  $data = COrders::getOrderHistory(Yii::app()->user->id,$q,$offset,$limit,Yii::app()->language);			  			  
			  	          	 	                   	       
	          $this->code = 1;
	          $this->msg = "Ok";	        
	          $this->details = array(
			     'show_next_page'=>$show_next_page,
			     'page'=>intval($page)+1,
			     'data'=>$data
			  );			  
	     } catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		    
		 }	
		 $this->responseJson();
	}
	
	public function actionorderSummary()
	{
		$summary = COrders::getOrderSummary(Yii::app()->user->id);
		$this->code = 1; $this->msg = "OK";
		$this->details = array(
		  'summary'=>$summary
		);
		$this->responseJson();
	}
	
	public function actionorderdetails()
	{
		try {		 	
						
			 $refund_transaction = array();
		     $order_uuid = isset($this->data['order_uuid'])?trim($this->data['order_uuid']):'';

			 $exchange_rate = 1;
			 $model_order = COrders::get($order_uuid);			 			 
			 if($model_order->base_currency_code!=$model_order->use_currency_code){
				$exchange_rate = $model_order->exchange_rate>0?$model_order->exchange_rate:1;
				Price_Formatter::init($model_order->use_currency_code);
			 } else {
				Price_Formatter::init($model_order->use_currency_code);
			 }			 
			 COrders::setExchangeRate($exchange_rate);
		     
		     COrders::getContent($order_uuid,Yii::app()->language,'customer');
		     $merchant_id = COrders::getMerchantId($order_uuid);
		     $merchant_info = COrders::getMerchant($merchant_id,Yii::app()->language);			

		     $items = COrders::getItems();		     
		     $summary = COrders::getSummary();				 
		     $order = COrders::orderInfo(Yii::app()->language);			 

		     try {
			     $order_id = COrders::getOrderID();		     
			     $refund_transaction = COrders::getPaymentTransactionList(Yii::app()->user->id,$order_id,array(
			       'paid'
			     ),array(
			       'refund',
				   'full_refund',
				   'partial',
			       'partial_refund'
			     ));					     
		     } catch (Exception $e) {
		     	//echo $e->getMessage(); die();
		     }
		     
		     $label = array(		       
		       'your_order_from'=>t("Your order from"),
		       'summary'=>t("Summary"),	
		       'track'=>t("Track"),
		       'buy_again'=>t("Buy again"),
		     );
			 
			 $order_table_data = [];
			 $order_type = $order['order_info']['order_type'];
			 if($order_type=="dinein"){
				$order_table_data = COrders::orderMeta(['table_id','room_id','guest_number']);	
				$room_id = isset($order_table_data['room_id'])?$order_table_data['room_id']:0;							
				$table_id = isset($order_table_data['table_id'])?$order_table_data['table_id']:0;							
				try {
					$table_info = CBooking::getTableByID($table_id);
					$order_table_data['table_name'] = $table_info->table_name;
				} catch (Exception $e) {
					//$order_table_data['table_name'] = t("Unavailable");
				}				
				try {
					$room_info = CBooking::getRoomByID($room_id);					
					$order_table_data['room_name'] = $room_info->room_name;
				} catch (Exception $e) {
					//$order_table_data['room_name'] = t("Unavailable");
				}
			 }			 
		     
		     $data = array(
		       'merchant'=>$merchant_info,
		       'order'=>$order,
		       'items'=>$items,
		       'summary'=>$summary,	
		       'label'=>$label,
		       'refund_transaction'=>$refund_transaction,
			   'order_table_data'=>$order_table_data
		     );		     
		    		     		     
		     $this->code = 1; $this->msg = "ok";
		     $this->details = array(			 		      
		       'data'=>$data,		      
		     );
		     		     		     		     		
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		   
		}	
		$this->responseJson();
	}
	
	public function actionorderBuyAgain()
	{	
		try {
		    $current_cart_uuid = isset($this->data['cart_uuid'])?trim($this->data['cart_uuid']):'';
		    CCart::clear($current_cart_uuid);
		} catch (Exception $e) {
			//
		}
		
		try {
			
		   $order_uuid = isset($this->data['order_uuid'])?trim($this->data['order_uuid']):'';		   		  
		   
		   COrders::$buy_again = true;
		   COrders::getContent($order_uuid,Yii::app()->language);
		   $merchant_id = COrders::getMerchantId($order_uuid);
		   $items = COrders::getItems();
		   
		   $merchant_info = COrders::getMerchant($merchant_id,Yii::app()->language);
		   $restaurant_url = isset($merchant_info['restaurant_url'])?$merchant_info['restaurant_url']:'';
		   	 
		   $cart_uuid = CCart::addOrderToCart($merchant_id,$items);
		   
		   $transaction_type = COrders::orderTransaction($order_uuid,$merchant_id,Yii::app()->language);
		   CCart::savedAttributes($cart_uuid,Yii::app()->params->local_transtype,$transaction_type);	
		   CCart::savedAttributes($cart_uuid,'whento_deliver','now');
		   CommonUtility::WriteCookie( "cart_uuid_local" ,$cart_uuid);	
		   
		   $this->code = 1 ; $this->msg = "OK";			
	       $this->details = array(
	         'cart_uuid'=>$cart_uuid,
	         'restaurant_url'=>$restaurant_url
	       );			   
		   
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		 		    
		}	
		$this->responseJson();
	}
	
	public function actioncancelOrderStatus()
	{
		try {

			$order_uuid = isset($this->data['order_uuid'])?trim($this->data['order_uuid']):'';			
			$resp = COrders::getCancelStatus($order_uuid);					
			$this->code = 1;
		    $this->msg = "OK";
		    $this->details = $resp;
			
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		    
		}	
		$this->responseJson();
	}
	
	public function actionapplycancelorder()
	{
		try {			

			$order_uuid = isset($this->data['order_uuid'])?trim($this->data['order_uuid']):'';
			$order = COrders::get($order_uuid);
			$resp = COrders::getCancelStatus($order_uuid);			
			
			$cancel = AR_admin_meta::getValue('status_cancel_order');			
			$cancel_status = isset($cancel['meta_value'])?$cancel['meta_value']:'cancelled';

			$cancel2 = AR_admin_meta::getValue('status_delivery_cancelled');			
			$cancel_status2 = isset($cancel2['meta_value'])?$cancel2['meta_value']:'cancelled';
						
			$reason = "Customer cancel this order";
			
			if($resp['payment_type']=="online"){
				if($resp['cancel_status']==1 && $resp['refund_status']=="full_refund"){
					// FULL REFUND								
					$order->scenario = "cancel_order";
					if($order->status==$cancel_status){
						$this->msg = t("This order has already been cancelled");
				        $this->responseJson();
					}					
					$order->status = $cancel_status;					
					$order->delivery_status  = $cancel_status2;
			        $order->remarks = $reason;
					if($order->save()){
					   $this->code = 1;
					   $this->msg = t("Your order is now cancel. your refund is on its way.");			   
					   if(!empty($reason)){
					   	  COrders::savedMeta($order->order_id,'rejetion_reason',$reason);
					   }			   
					} else $this->msg = CommonUtility::parseError( $order->getErrors());
					
				} elseif ( $resp['cancel_status']==1 && $resp['refund_status']=="partial_refund" ){
					///PARTIAL REFUND					
					$refund_amount = floatval($resp['refund_amount']);
					$order->scenario = "customer_cancel_partial_refund";					
					
					$model = new AR_ordernew_summary_transaction;
					$model->scenario = "refund";
					$model->order = $order;
					$model->order_id = $order->order_id;
					$model->transaction_description = "Refund";
					$model->transaction_amount = floatval($refund_amount);
					
					if($model->save()){					
						$order->status = $cancel_status;
						$order->delivery_status  = $cancel_status2;
						$order->remarks = $reason;
						$order->refund_amount = $refund_amount;
						if($order->save()){
						   $this->code = 1;
						   $this->msg = t("Your order is now cancel. your partial refund is on its way.");			   
						   if(!empty($reason)){
						   	  COrders::savedMeta($order->order_id,'rejetion_reason',$reason);
						   }			   
						} else $this->msg = CommonUtility::parseError( $order->getErrors());					
					} else $this->msg = CommonUtility::parseError( $order->getErrors());
										
				} else {
					//REFUND NOT AVAILABLE
					$this->msg = $resp['cancel_msg'];
				}
			} else {							
				if($resp['cancel_status']==1 && $resp['refund_status']=="full_refund"){
					//CANCEL ORDER
					$order->scenario = "cancel_order";
					if($order->status==$cancel_status){
						$this->msg = t("This order has already been cancelled");
				        $this->responseJson();
					}					
					$order->status = $cancel_status;
					$order->delivery_status  = $cancel_status2;
					$reason = "Customer cancell this order";
			        $order->remarks = $reason;
					if($order->save()){
					   $this->code = 1;
					   $this->msg = t("Your order is now cancel.");			   
					   if(!empty($reason)){
					   	  COrders::savedMeta($order->order_id,'rejetion_reason',$reason);
					   }			   
					} else $this->msg = CommonUtility::parseError( $order->getErrors());
					
				} else {
					$this->msg = $resp['cancel_msg'];
				}
			}						
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		    
		}	
		$this->responseJson();
	}
	
	public function actiongetAddressAttributes()
	{
		try {			
			$this->code = 1;
			$this->msg = "OK";

			$custom_field_enabled = isset(Yii::app()->params['settings']['custom_field_enabled']) ? Yii::app()->params['settings']['custom_field_enabled'] : false;
			$custom_field_enabled = $custom_field_enabled==1?true:false;
			
			$this->details = array(			
			  'delivery_option'=>CCheckout::deliveryOption(),
			  'address_label'=>CCheckout::addressLabel(),
			  'maps_config'=>CMaps::config(),
			  'default_atts'=>CCheckout::defaultAttrs(),
			  'custom_field_enabled'=>$custom_field_enabled,
			  'custom_field1_data'=>CCheckout::customeFieldList()
			);				
		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());	
		}
		$this->responseJson();
	}
	
	public function actiongetAddresses()
	{				
		if(!Yii::app()->user->isGuest){			
			if ( $data = CClientAddress::getAddresses(Yii::app()->user->id)){								
				$this->code = 1;
				$this->msg = "OK";
				$this->details = array(
				  'data'=>$data
				);			
			} else $this->msg[] = t("No results");
		} else $this->msg = "not login";
		$this->responseJson();
	}
	
	public function actiongetAdddress()
	{
		try {	
			
		   $address_uuid = isset($this->data['address_uuid'])?trim($this->data['address_uuid']):'';
		   $data = CClientAddress::find(Yii::app()->user->id,$address_uuid);
		   $this->code = 1;
		   $this->msg = "OK";		   
		   $this->details = array(
		     'data'=>$data
		   );		  		   
		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());	
		}
		$this->responseJson();
	}	
	
	public function actionSaveAddress()
	{
		try {	
												
			$address_format_use = isset(Yii::app()->params['settings']['address_format_use'])? (!empty(Yii::app()->params['settings']['address_format_use'])?Yii::app()->params['settings']['address_format_use']:'') :'';
			$address_format_use = !empty($address_format_use)?$address_format_use:1;			

			$custom_field_enabled = isset(Yii::app()->params['settings']['custom_field_enabled'])? (!empty(Yii::app()->params['settings']['custom_field_enabled'])?Yii::app()->params['settings']['custom_field_enabled']:'') :'';
			$custom_field_enabled = !empty($custom_field_enabled)?$custom_field_enabled:false;			
			$custom_field_enabled = $custom_field_enabled==1?true:false;			

			$address_uuid = isset($this->data['address_uuid'])?trim($this->data['address_uuid']):'';			
			$set_place_id = isset($this->data['set_place_id'])?($this->data['set_place_id']):false;
			$data =  isset($this->data['data'])?$this->data['data']:array();			
			$place_id = isset($data['place_id'])?$data['place_id']:'';
			
			if(!empty($address_uuid)){
				$model = AR_client_address::model()->find('address_uuid=:address_uuid AND client_id=:client_id', 
		        array(':address_uuid'=>$address_uuid,'client_id'=>Yii::app()->user->id)); 
			} else {
				$model = AR_client_address::model()->find('place_id=:place_id AND client_id=:client_id', 
		        array(':place_id'=>$place_id,'client_id'=>Yii::app()->user->id));				
			}		
		    if(!$model){		    	
		    	$model = new AR_client_address;
		    	$model->client_id = intval(Yii::app()->user->id);
		    	$model->address_uuid = CommonUtility::generateUIID();		    	
		    	$model->place_id = isset($data['place_id'])?$data['place_id']:'';
		    	$model->country = isset($data['address']['country'])?$data['address']['country']:'';
		    	$model->country_code = isset($data['address']['country_code'])?$data['address']['country_code']:'';
		    } 
		    $map_place = AR_map_places::model()->find('reference_id=:reference_id', 
			array(':reference_id'=>$place_id));
			$model->place_id = $place_id;
		    $model->location_name = isset($this->data['location_name'])?$this->data['location_name']:'';
		    $model->city = isset($this->data['city'])?$this->data['city']:(isset($map_place['city'])?$map_place['city']:'');
	    	$model->delivery_instructions = isset($this->data['delivery_instructions'])?$this->data['delivery_instructions']:'';
	    	$model->delivery_options = isset($this->data['delivery_options'])?$this->data['delivery_options']:'';
	    	$model->address_label = isset($this->data['address_label'])?$this->data['address_label']:'';
	    	$model->latitude = isset($this->data['latitude'])?$this->data['latitude']:(isset($map_place['latitude'])?$map_place['latitude']:'');
	    	$model->longitude = isset($this->data['longitude'])?$this->data['longitude']:(isset($map_place['longitude'])?$map_place['longitude']:'');
	    	$model->address1 = isset($this->data['address1'])?$this->data['address1']:(isset($map_place['address1'])?$map_place['address1']:'');			
	    	$model->street_number = isset($this->data['street_number'])?$this->data['street_number']:(isset($map_place['street_number'])?$map_place['street_number']:'');			
	    	$model->formatted_address = isset($this->data['formatted_address'])?$this->data['formatted_address']:(isset($map_place['formatted_address'])?$map_place['formatted_address']:'');
			$model->address_format_use = $address_format_use;
			$model->custom_field1 = isset($this->data['custom_field1'])?$this->data['custom_field1']:(isset($map_place['custom_field1'])?$map_place['custom_field1']:'');
			$model->custom_field2 = isset($this->data['custom_field2'])?$this->data['custom_field2']:(isset($map_place['custom_field2'])?$map_place['custom_field2']:'');

			if($address_format_use==2){
				$model->scenario = "forms2";
				$model->address2 = isset($this->data['address2'])?$this->data['address2']:(isset($map_place['address2'])?$map_place['address2']:'');
				$model->postal_code = isset($this->data['postal_code'])?$this->data['postal_code']:(isset($map_place['postal_code'])?$map_place['postal_code']:'');
				$model->company = isset($this->data['company'])?$this->data['company']:(isset($map_place['company'])?$map_place['company']:'');		
			} else if($address_format_use==3){
				$model->scenario = "forms3";	
				$model->postal_code = isset($data['address']['postal_code'])?$data['address']['postal_code']:(isset($map_place['postal_code'])?$map_place['postal_code']:'');		
			} else {				
				$model->address2 = isset($data['address']['address2'])?$data['address']['address2']:(isset($map_place['address2'])?$map_place['address2']:'');
			    $model->postal_code = isset($data['address']['postal_code'])?$data['address']['postal_code']:(isset($map_place['postal_code'])?$map_place['postal_code']:'');
				if($custom_field_enabled){
					$model->scenario = "custom_field_enabled";
				}
			}									

	    	if($model->save()){
	    		$this->code = 1;
		    	$this->msg = t("Address saved succesfully");
		    	$this->details = array(
		    	  'place_id'=>$model->place_id
		    	);
				CommonUtility::WriteCookie( Yii::app()->params->local_id ,$model->place_id );  		    			    	
	    	} else $this->msg = CommonUtility::parseError( $model->getErrors());
			
		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());				
		}
		$this->responseJson();
	}
	
	public function actionMyPayments()
	{
		try {
			
			$default_payment_uuid = '';
			$model = AR_client_payment_method::model()->find('client_id=:client_id AND as_default=:as_default', 
		    array(
		      ':client_id'=>Yii::app()->user->id,
		      ':as_default'=>1
		    )); 	
		    if($model){		    	
		    	$default_payment_uuid=$model->payment_uuid;
		    }
		    
			$data = CPayments::SavedPaymentList( Yii::app()->user->id , 0);
			
			$this->code = 1;
		    $this->msg = "ok";
		    $this->details = array(
		      'default_payment_uuid'=>$default_payment_uuid,
		      'data'=>$data,
		    );					
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		    
		}	
		$this->responseJson();
	}
	
	public function actiondeletePayment()
	{		
		try {
						
			$payment_uuid = isset($this->data['payment_uuid'])?trim($this->data['payment_uuid']):'';
			CPayments::delete(Yii::app()->user->id,$payment_uuid);
			$this->code = 1;
		    $this->msg = "ok";
			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}	
		$this->responseJson();
	}
	
	public function actiongetCards()
	{
		try {
					   
		   $cc_id = isset($this->data['cc_id'])?trim($this->data['cc_id']):'';
		   $model = AR_client_cc::model()->find('client_id=:client_id AND cc_id=:cc_id', 
		   array(
		     ':client_id'=>Yii::app()->user->id,
		     ':cc_id'=>$cc_id,
		   )); 	
		   if($model){
		   			   	  
		   	  try {
					$card = CreditCardWrapper::decryptCard($model->encrypted_card);
			  } catch (Exception $e) {
					$card ='';
			  }		
			  			  
		   	  $data = array(
		   	    'card_uuid'=>$model->card_uuid,
		   	    'card_name'=>$model->card_name,
		   	    'credit_card_number'=>$card,
		   	    'expiry_date'=>$model->expiration_month."/".$model->expiration_yr,
		   	    'cvv'=>$model->cvv,
		   	    'billing_address'=>$model->billing_address,
		   	  );
		   	  $this->code = 1;
		   	  $this->msg = "OK";
		   	  $this->details = array('data'=>$data);		   	  
		   } else $this->msg[] = t("Record not found");
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		    		    
		}	
		$this->responseJson();
	}
	
	public function actionPaymentMethod()
	{
		try {
			
		   $data = array();
		   $payment_type = isset($this->data['payment_type'])?trim($this->data['payment_type']):'';
		   $filter=array(
		     'payment_type'=>$payment_type
		   );
		   $data = CPayments::DefaultPaymentList();
		   
		   $this->code = 1;
		   $this->msg = "OK";		  
		   $this->details = array(
		     'data'=>$data
		   );		   
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		    		    
		}	
		$this->responseJson();
	}
	
	public function actiongetSaveStore()
	{
		try {			
					   
		   if(!Yii::app()->user->isGuest){
			   $merchant_id = isset($this->data['merchant_id'])?intval($this->data['merchant_id']):0;
			   $data = CSavedStore::getStoreReview($merchant_id,Yii::app()->user->id);
			   $this->code = 1;
			   $this->msg = "OK";		   
		   } else $this->msg = t("not login");
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		  		      		   
		}	
		$this->responseJson();
	}
	
	public function actionSaveStore()
	{
		try {			
					   
		   if(!Yii::app()->user->isGuest){
			   $merchant_id = isset($this->data['merchant_id'])?intval($this->data['merchant_id']):0;
			   
			   $model = AR_favorites::model()->find('fav_type=:fav_type AND merchant_id=:merchant_id AND client_id=:client_id', 
		       array(
				   ':fav_type'=>"restaurant",
				   ':merchant_id'=>$merchant_id ,
				   'client_id'=> Yii::app()->user->id  
				)); 		
		       
		       if($model){
		       	  $model->delete();
		       	  $this->code = 1;
				  $this->msg = "OK";	
				  $this->details = array('found'=>false);
		       } else {			   
				   $model = new AR_favorites;
				   $model->client_id = Yii::app()->user->id;
				   $model->merchant_id = $merchant_id;
				   if($model->save()){
				   	  $this->code = 1;
				      $this->msg = "OK";	
				      $this->details = array('found'=>true);	   
				   } else $this->msg = CommonUtility::parseModelErrorToString( $model->getErrors());
		       }
		   } else $this->msg = t("You must login to save this store");
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		  		      		   
		}	
		$this->responseJson();
	}
	
	public function actionsaveStoreList()
	{
		try {	
			
		   $data = CSavedStore::Listing( Yii::app()->user->id );		   
		   $services = CSavedStore::services( Yii::app()->user->id  );
		   $estimation = CSavedStore::estimation( Yii::app()->user->id  );					   
		   $this->code = 1;
		   $this->msg = "Ok";		   
		   $this->details = array(
		     'data'=>$data,
		     'services'=>$services,
		     'estimation'=>$estimation
		   );		   				   
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		  		      		   		   
		}
		$this->responseJson();
	}
	
	public function actionGetFeaturedLocation()
	{		
		try {
			
			$query =  Yii::app()->request->getQuery('query', '');  
							
			$results = CFeaturedLocation::Listing($query, Yii::app()->language );
			$data = $results['data'];			
			$merchants = $results['merchants'];						
			$vouchers = [];
		    try {
		      $vouchers = Clocations::getVoucherList($merchants,date("Y-m-d"));		   
		    } catch (Exception $e) {}

		    $promos = [];
		    try {
		      $promos = Clocations::getPromoList($merchants,date("Y-m-d"));		   
		    } catch (Exception $e) {}

			$estimation = CFeaturedLocation::merchantsEstimation($merchants);		
			
			$pause_reason_list = CFeaturedLocation::PauseReasonList();			
			
			foreach ($data as &$restaurant) {
				$merchant_id = $restaurant['merchant_id'];				
				$restaurant['vouchers'] = isset($vouchers[$merchant_id])?$vouchers[$merchant_id]:[];
				$restaurant['promos'] = isset($promos[$merchant_id]) ? $promos[$merchant_id] : [];
				$estimations = isset($estimation[$merchant_id]) ? $estimation[$merchant_id] : [];
				$restaurant['estimated_time_min'] = !empty($estimations) ? t("{estimated} mins",[
                            '{estimated}'=>$estimations
                        ]):'';									
				$restaurant['pause_reason'] = isset($pause_reason_list[$merchant_id])?$pause_reason_list[$merchant_id]:'';
		    }
						
			$this->code = 1;
		    $this->msg = "Ok";
		    $this->details = array(		      
		      'data'=>$data		      
		    );			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}
		$this->responseJson();
	}
	
	public function actiongetServices()
	{		
		$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';				
		$table_uuid = isset($this->data['table_uuid'])?$this->data['table_uuid']:'';				
		try {

			$transaction_type = CServices::getSetService($cart_uuid);	
			if($table_uuid!==""){
				$transaction_type = "dinein";
				CCart::savedAttributes($cart_uuid,Yii::app()->params->local_transtype,$transaction_type);	
			}		
			$data = CServices::Listing(  Yii::app()->language );			
			if(!array_key_exists($transaction_type,(array)$data)){				
				$keys = array_keys($data);
				$transaction_type = isset($keys[0])?$keys[0]:$transaction_type;
			}					
			$this->code = 1; $this->msg = "OK";
			$this->details = array(
			  'transaction_type'=>$transaction_type,
			  'data'=>$data
			);						
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}
		$this->responseJson();
	}
	
	public function actionsetTransactionType()
	{		
		try {
			
			$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
			$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';
			if(empty($cart_uuid)){
				$cart_uuid = CommonUtility::generateUIID();			
			}		
			CCart::savedAttributes($cart_uuid,Yii::app()->params->local_transtype,$transaction_type);
			CommonUtility::WriteCookie( "cart_uuid_local" ,$cart_uuid);
			
			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(
			  'cart_uuid'=>$cart_uuid,
			  'transaction_type'=>$transaction_type
			);
			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}
		$this->responseJson();
	}
	
	public function actionTransactionInfo()
	{
		try {
			
			$whento_deliver = ''; $delivery_datetime='';
			$merchant_id = isset($this->data['merchant_id'])?$this->data['merchant_id']:'';
			$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
			$local_id = CommonUtility::getCookie(Yii::app()->params->local_id);	

			$options = OptionsTools::find(['merchant_time_selection'],$merchant_id);				
			$time_selection = isset($options['merchant_time_selection'])?$options['merchant_time_selection']:1;
			$time_selection = !empty($time_selection)?$time_selection:1;			
			
			$local_info = [];
			if(!empty($local_id)){
				if(Yii::app()->user->isGuest){									
 				    $local_info = CMaps::locationDetails($local_id,'');						
				} else {			
					try {					
						$local_info = CClientAddress::getAddress($local_id,Yii::app()->user->id);
					} catch (Exception $e) {					
						$local_info = CMaps::locationDetails($local_id,'');
					}			
				}			
		    }  
			
			$delivery_option = CCheckout::deliveryOptionList();
												
			$data = isset($this->data['choosen_delivery'])?$this->data['choosen_delivery']:'';			

			$date = date("Y-m-d");
			$time_now = date("H:i");
			$attrs_name = ['whento_deliver','delivery_date','delivery_time'];
			
			if(is_array($data) && count($data)>=1){												
				$whento_deliver = isset($data['whento_deliver'])?$data['whento_deliver']:'now';
				$delivery_date = isset($data['delivery_date'])?$data['delivery_date']:date("Y-m-d");
				$delivery_time = isset($data['delivery_time'])?$data['delivery_time']:'';								
				$delivery_datetime = CCheckout::jsonTimeToFormat($delivery_date,json_encode($delivery_time));				

				if($whento_deliver=="schedule"){
					$date = !empty($delivery_date)?$delivery_date:$date;										
					$timenow = isset($delivery_time['start_time'])?$delivery_time['start_time']:$time_now;
					$time_now = !empty($timenow)?$timenow:$time_now;							
				}
			} else {				
				$atts_delivery = CCart::getAttributesAll($cart_uuid,$attrs_name);						
				$whento_deliver = isset($atts_delivery['whento_deliver'])?$atts_delivery['whento_deliver']:'now';				
				if($whento_deliver=="schedule"){					
					if($time_selection==3){
						$whento_deliver = "now";
					} else {
						$date = isset($atts_delivery['delivery_date'])?$atts_delivery['delivery_date']:$date;
					    $time_now = isset($atts_delivery['delivery_time'])?CCheckout::jsonTimeToSingleTime($atts_delivery['delivery_time']):$time_now;
					    $delivery_datetime = CCheckout::jsonTimeToFormat($atts_delivery['delivery_date'],$atts_delivery['delivery_time']);					
					}				
				} else {
					if($time_selection==2){
						$whento_deliver = "schedule";						
					}
				}
			}			
			
			try {
				$datetime_to = date("Y-m-d g:i:s a",strtotime("$date $time_now"));
				CMerchantListingV1::checkCurrentTime( date("Y-m-d g:i:s a") , $datetime_to);
				$time_already_passed = false;
			} catch (Exception $e) {
				$time_already_passed = true;
				$whento_deliver = "now";
				$delivery_datetime = '';
				CCart::deleteAttributesAll($cart_uuid,$attrs_name);
			}
													
			$this->code = 1; $this->msg ="ok";
			$this->details = array(
			  'address1'=> isset($local_info['address'])? $local_info['address']['address1']:"",
			  'street_number'=> isset($local_info['address'])? $local_info['address']['street_number']:"",
			  'postal_code'=> isset($local_info['address'])? $local_info['address']['postal_code']:"",
			  'formatted_address'=> isset($local_info['address'])? $local_info['address']['formatted_address']:"",			  
			  'delivery_option'=>$delivery_option,
			  'whento_deliver'=>$whento_deliver,
			  'delivery_datetime'=>$delivery_datetime,
			  'time_already_passed'=>$time_already_passed,
			);						
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    		    
		}
		$this->responseJson();
	}
	
	public function actiongetDeliveryTimes()
	{
		try {
						
			$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
			$merchant_id = isset($this->data['merchant_id'])?intval($this->data['merchant_id']):0;			

			$options = OptionsTools::find(['merchant_time_selection'],$merchant_id);				
			$time_selection = isset($options['merchant_time_selection'])?$options['merchant_time_selection']:1;
			$time_selection = !empty($time_selection)?$time_selection:1;
						
			$delivery_option = CCheckout::deliveryOptionList('',$time_selection);			
			$whento_deliver = CCheckout::getWhenDeliver($cart_uuid);
						
			$model = AR_opening_hours::model()->find("merchant_id=:merchant_id",array(
			  ':merchant_id'=>$merchant_id
			));
			if(!$model){
				$this->msg[] = t("Merchant has not set time opening yet");
				$this->responseJson();
			}			
			
			$options = OptionsTools::find(array('website_time_picker_interval','website_date_range'));			
			$interval = isset($options['website_time_picker_interval'])?$options['website_time_picker_interval']." mins":'20 mins';			
			$date_range = isset($options['website_date_range'])?$options['website_date_range']:7;			
			$date_range = $date_range>0?$date_range:7;			

			// CHECK IF MERCHANT HAS DIFFERENT TIMEZONE
			$options_merchant = OptionsTools::find(['merchant_time_picker_interval','merchant_timezone'],$merchant_id);
			$interval_merchant = isset($options_merchant['merchant_time_picker_interval'])? ( !empty($options_merchant['merchant_time_picker_interval']) ? $options_merchant['merchant_time_picker_interval']." mins" :''):'';
			$interval = !empty($interval_merchant)?$interval_merchant:$interval;
			$merchant_timezone = isset($options_merchant['merchant_timezone'])?$options_merchant['merchant_timezone']:'';
			if(!empty($merchant_timezone)){
				Yii::app()->timezone = $merchant_timezone;
			}

			CommonUtility::mysqlSetTimezone();
						
			$opening_hours = CMerchantListingV1::openHours2($merchant_id,$interval,$date_range); 
			$delivery_date_list = $opening_hours['dates'] ?? null;
			$delivery_date = ''; $delivery_time='';

			if($atts = CCart::getAttributesAll($cart_uuid,array('delivery_date','delivery_time'))){				
				$delivery_date = isset($atts['delivery_date'])?$atts['delivery_date']:'';
				$delivery_time = isset($atts['delivery_time'])?$atts['delivery_time']:'';
			}			

			$date_today = date('Y-m-d'); 				
			if(!empty($delivery_date_list) && !array_key_exists($date_today,(array)$delivery_date_list) && $merchant_id>0 ){
				if(isset($delivery_option['now'])){					
					unset($delivery_option['now']);
				}				
			}			
									
			$this->code = 1; $this->msg = "ok";			
		    $this->details = array(		     
		       'delivery_option'=>$delivery_option,
		       'whento_deliver'=>$whento_deliver,
		       'delivery_date'=>!empty($delivery_date)?$delivery_date:date("Y-m-d"),
		       'delivery_time'=>$delivery_time,
		       'opening_hours'=>$opening_hours,		       
		    );
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		    		    
		}
		$this->responseJson();
	}
	
	public function actionsaveTransactionInfo()
	{
		try {
						
			$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
			$whento_deliver = isset($this->data['whento_deliver'])?$this->data['whento_deliver']:'';
			$delivery_date = isset($this->data['delivery_date'])?$this->data['delivery_date']:'';
			$delivery_time = isset($this->data['delivery_time'])?$this->data['delivery_time']:'';
						
			CCart::savedAttributes($cart_uuid,'whento_deliver',$whento_deliver);			  
			CCart::savedAttributes($cart_uuid,'delivery_date',$delivery_date);
			CCart::savedAttributes($cart_uuid,'delivery_time',json_encode($delivery_time));
								
			$delivery_datetime = CCheckout::jsonTimeToFormat($delivery_date,json_encode($delivery_time));
			
			$this->code = 1; $this->msg = "OK";
			$this->details = array(
			  'whento_deliver'=>$whento_deliver,
			  'delivery_date'=>$delivery_date,
			  'delivery_time'=>$delivery_time,
			  'delivery_datetime'=>$delivery_datetime,			  
			);						
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    		    
		}
		$this->responseJson();
	}
	
	public function actiongetSearchSuggestion()
	{
		try {
			
			$q = isset($this->data['q'])?$this->data['q']:'';
			$local_info = '';
			try {
			   $local_id = CommonUtility::getCookie(Yii::app()->params->local_id);
		       $local_info = CMerchantListingV1::getLocalID($local_id);				
		    } catch (Exception $e) {
		    	//
		    }
		    
			$filter = array(			
			  'search'=>$q,
			  'lat'=>$local_info?$local_info->latitude:'',
			  'lng'=>$local_info?$local_info->longitude:'',
			  'unit'=>Yii::app()->params['settings']['home_search_unit_type'],
			  'page'=>0,
			  'limit'=>Yii::app()->params->list_limit,
			);			
			$data = CMerchantListingV1::searchSuggestion($filter , Yii::app()->language );
			$this->code = 1; $this->msg = "OK";
			$this->details = array(
			  'data'=>$data
			);			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    		    
		}
		$this->responseJson();		
	}

	public function actiongetsearchsuggestionv1()
	{		
		try {

			$q = Yii::app()->input->post('q');
			$category = Yii::app()->input->post('category');			

			$local_info = '';
			try {
			   $local_id = CommonUtility::getCookie(Yii::app()->params->local_id);
		       $local_info = CMerchantListingV1::getLocalID($local_id);				
		    } catch (Exception $e) {
		    	//
		    }

			$filter = array(			
			  'search'=>$q,
			  'lat'=>$local_info?$local_info->latitude:'',
			  'lng'=>$local_info?$local_info->longitude:'',
			  'unit'=>Yii::app()->params['settings']['home_search_unit_type'],
			  'page'=>0,
			  'limit'=>Yii::app()->params->list_limit,
			);		
			
			if($category=="restaurant"){			   					
				$data = CMerchantListingV1::searchSuggestion($filter , Yii::app()->language );
				$this->code = 1; $this->msg = "OK";
				$this->details = array( 'data'=>$data);							
			} else {
				$data = CMerchantListingV1::searchSuggestionFood($filter , Yii::app()->language );				
				$this->code = 1; $this->msg = "OK";
				$this->details = array( 'data'=>$data);							
			}


		} catch (Exception $e) {
			$this->msg = t($e->getMessage());		    		    			
		}		
		$this->responseJson();	
	}
		
	public function actiongetSignupAttributes()
	{
		try {
			
			$capcha = Yii::app()->params['settings']['merchant_enabled_registration_capcha'];			
			$program = Yii::app()->params['settings']['registration_program'];			
			$program = !empty($program)?json_decode($program,true):false;
			
			$membership_list = array(); $membership_commission = [];
			$mobile_prefixes = AttributesTools::countryMobilePrefix(); 
			try {
			    $membership_list = CMerchantSignup::membershipProgram( Yii::app()->language , (array)$program );					
				foreach ($membership_list as $items) {					
					$membership_commission[$items['type_id']] = $items['commission_data'];
				}				
			} catch (Exception $e) {
				//
			}

			$services_list = [];
			try {
				$services_list = CServices::Listing(  Yii::app()->language );
			} catch (Exception $e) {

			}
			
			$currency_list = CMulticurrency::currencyList();
			$select = [''=>t("Please select")];
			$currency_list = $select+$currency_list;
			
			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(
			  'capcha'=>$capcha==1?true:false,
			  'mobile_prefixes'=>$mobile_prefixes,
			  'membership_list'=>$membership_list,
			  'membership_commission'=>$membership_commission,
			  'services_list'=>$services_list,
			  'currency_list'=>$currency_list
			);			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    		    
		}
		$this->responseJson();
	}
	
	public function actiongetLocationCountries()
	{
		try {
			
			$default_country = isset($this->data['default_country'])?$this->data['default_country']:'';
			$only_countries = isset($this->data['only_countries'])?(array)$this->data['only_countries']:array();
			$filter = array(
			  'only_countries'=>(array)$only_countries
			);
			
			$data = ClocationCountry::listing($filter);
			$default_data = ClocationCountry::get($default_country);			
			
			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(
			  'data'=>$data,
			  'default_data'=>$default_data,			  
			);			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    		    
		}
		$this->responseJson();		
	}
	
	public function actionCreateAccountMerchant()
	{				
		$model = new AR_merchant;
		$model->scenario = 'website_registration';
		$model->restaurant_name = isset($this->data['restaurant_name'])?$this->data['restaurant_name']:'';
		$model->address = isset($this->data['address'])?$this->data['address']:'';
		$model->contact_email = isset($this->data['contact_email'])?$this->data['contact_email']:'';
		$mobile_prefix = isset($this->data['mobile_prefix'])?$this->data['mobile_prefix']:'';
		$model->contact_phone = isset($this->data['mobile_number'])?$mobile_prefix.$this->data['mobile_number']:'';
		$model->merchant_type = isset($this->data['membership_type'])? intval($this->data['membership_type']) :0;	
		$model->service2 = isset($this->data['services'])?$this->data['services']:'';
		$model->merchant_base_currency = isset($this->data['currency'])?$this->data['currency']:'';

		$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
        $multicurrency_enabled = $multicurrency_enabled==1?true:false;					
		$model->multicurrency_enabled = $multicurrency_enabled;
				
		if($program = CMerchantSignup::get($model->merchant_type)){								
			if($program->type_id==2){
				$model->commision_type = trim($program->commision_type);
				$model->percent_commision = floatval($program->commission);
				$model->commision_based = $program->based_on;				
			}
		}				

		$commission_type = []; $commission_value = [];

		if($model->merchant_type==2){
			$model_merchant_type = AR_merchant_type::model()->find("type_id=:type_id",[
				":type_id"=>$model->merchant_type
			]);
			if($model_merchant_type){
				$commission_data = !empty($model_merchant_type->commission_data)?json_decode($model_merchant_type->commission_data,true):false;
				if(is_array($commission_data) && count($commission_data)>=1){				
					foreach ($commission_data as $items) {
						$commission_type[$items['transaction_type']] = $items['commission_type'];
						$commission_value[$items['transaction_type']] = $items['commission'];
					}				
					$model->commission_type = $commission_type;
					$model->commission_value = $commission_value;
				}			
			}		
		}		
						
		if ($model->save()){
			$this->code = 1; $this->msg = t("Registration successful");							
			$redirect = Yii::app()->createAbsoluteUrl("merchant/user-signup/?uuid=".$model->merchant_uuid);			
			$this->details = array(
			  'redirect'=>$redirect
			);			
		} else {							
			if ( $error = CommonUtility::parseError( $model->getErrors()) ){
				$this->msg = $error;
			} else $this->msg[] = array('invalid error');				
		}			
		$this->responseJson();		
	}

	public function actioncreateMerchantUser()
	{
		try {
					   
		   $merchant_uuid = isset($this->data['merchant_uuid'])?$this->data['merchant_uuid']:'';
		   $merchant = CMerchants::getByUUID($merchant_uuid);
		   
		   $model =  AR_merchant_user::model()->find("merchant_id=:merchant_id AND main_account=:main_account",array(
		    ':merchant_id'=>intval($merchant->merchant_id),
		    ':main_account'=>1
		   ));
		   
		   if(!$model){
		   	   $model = new AR_merchant_user;
		   	   $model->scenario = 'register';
		   }
		   		   		   

		   $allow_login = isset(Yii::app()->params['settings']['merchant_allow_login_afterregistration'])?Yii::app()->params['settings']['merchant_allow_login_afterregistration']:false;		
		   $allow_login = $allow_login==1?true:false;		
		   
		   $model->username = isset($this->data['username'])?$this->data['username']:'';
		   $model->password = isset($this->data['password'])?trim($this->data['password']):'';
		   $model->new_password = isset($this->data['password'])?trim($this->data['password']):'';
		   $model->repeat_password = isset($this->data['cpassword'])?trim($this->data['cpassword']):'';
		   
		   if($model->scenario=="update"){
		   	  $model->password = md5($model->password);		   	  
		   }
		   
		   $model->first_name = isset($this->data['first_name'])?$this->data['first_name']:'';
		   $model->last_name = isset($this->data['last_name'])?$this->data['last_name']:'';
		   $model->contact_email = isset($this->data['contact_email'])?$this->data['contact_email']:'';
		   $mobile_prefix = isset($this->data['mobile_prefix'])?$this->data['mobile_prefix']:'';
		   $model->contact_number = isset($this->data['mobile_number'])?$mobile_prefix.$this->data['mobile_number']:'';		   
		   $model->merchant_id = $merchant->merchant_id;
		   $model->main_account = 1;		   
		   if($model->save()){
		   	   $this->code = 1;
		   	   $this->msg = t("Registration successful");	
			   
				Yii::import('ext.runactions.components.ERunActions');	
				$cron_key = CommonUtility::getCronKey();						
				$get_params = array( 
					'merchant_uuid'=> $merchant->merchant_uuid,
					'key'=>$cron_key,							
					'language'=>Yii::app()->language
				);							
		   	   
		   	   $redirect = '';
			   if($merchant->merchant_type==1){
					$redirect = Yii::app()->createAbsoluteUrl("merchant/choose_plan",array(
					  'uuid'=>$merchant->merchant_uuid
					));
			   } elseif ($merchant->merchant_type==2){
				    if($allow_login){

						$merchant->status="active";
						$merchant->save();
						
						$redirect = Yii::app()->createAbsoluteUrl("merchant/thankyou");
						// SEND EMAIL AND SMS						
						//CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/aftermerchantpayment?".http_build_query($get_params) );
						CommonUtility::pushJobs("MerchantRegConfirm",[
							'merchant_id'=>$merchant->merchant_id,
							'language'=>Yii::app()->language
						]);
						CommonUtility::pushJobs("MerchantRegWelcome",[
							'merchant_id'=>$merchant->merchant_id,
							'language'=>Yii::app()->language
						]);
						// CommonUtility::pushJobs("MerchantRegNew",[
						// 	'merchant_id'=>$merchant->merchant_id,
						// 	'language'=>Yii::app()->language
						// ]);
					} else {
						$redirect = Yii::app()->createAbsoluteUrl("merchant/getbacktoyou");
						// SEND NOTIFICATION TO ADMIN
						//CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/aftermerchantregistered?".http_build_query($get_params) );
						// CommonUtility::pushJobs("MerchantRegNew",[
						// 	'merchant_id'=>$merchant->merchant_id,
						// 	'language'=>Yii::app()->language
						// ]);
					}
			   }
		   	   
			   $this->details = array(
				  'redirect'=>$redirect
			   );			
		   } else $this->msg =  CommonUtility::parseError( $model->getErrors());
		   
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		
		}
		$this->responseJson();	
	}
	
	public function actiongetPlan()
	{
		try {
			
			$details = array();			
						
			$data = CPlan::listing( Yii::app()->language );			
			try {
			    $details = CPlan::Details();		
			} catch (Exception $e) {
				//
			}			
			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(			
			  'data'=>$data,
			  'plan_details'=>$details,			  
			);									
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		
		}
		$this->responseJson();	
	}
	
	public function actiongetSelectPaymentPlan()
	{
		try {

			$plan_uuid = Yii::app()->input->post('plan_uuid');
			$plan = CPlan::getPlanByUUID($plan_uuid);			
			try {
				$payment_list = AttributesTools::PaymentPlansProvider(); 
			} catch (Exception $e) {}			

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'plan_details'=>$plan,
				'payment_list'=>$payment_list
			];
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		
		}
		$this->responseJson();	
	}

	public function actionPaymenPlanList()
	{
		 try {
		 	
		 	$payment_list = AttributesTools::PaymentPlansProvider(); 
		 	$this->code = 1;
		 	$this->msg = "ok";
		 	$this->details = $payment_list; 
		 	
		 } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		
		 }
		 $this->responseJson();	
	}
	
	public function actionverifyRecaptcha()
	{
		try {
			
			$options = OptionsTools::find(array('captcha_secret'));
			$secret = isset($options['captcha_secret'])?$options['captcha_secret']:'';						
			$recaptcha_response = isset($this->data['recaptcha_response'])?$this->data['recaptcha_response']:'';			
			$resp = CRecaptcha::verify($secret,$recaptcha_response);
			
			$this->code = 1;
			$this->msg = "ok";
			
		} catch (Exception $e) {
		    $this->msg[] = $e->getMessage();
		    $err = CRecaptcha::getError();		    
		    if($err == "timeout-or-duplicate"){
		    	$this->code = 3;
		    }
		}
		$this->responseJson();	
	}
	
	public function actionRegistrationPhone()
	{		
		
		$capcha = false;
		if(isset(Yii::app()->params['settings']['captcha_customer_signup'])){
		   $capcha = Yii::app()->params['settings']['captcha_customer_signup']==1?true:false;
		}
		$recaptcha_response = isset($this->data['recaptcha_response'])?$this->data['recaptcha_response']:'';				
				
		try {
						
			$digit_code = CommonUtility::generateNumber(4,true);
		    $mobile_number = isset($this->data['mobile_number'])?$this->data['mobile_number']:'';
		    $mobile_prefix = isset($this->data['mobile_prefix'])?$this->data['mobile_prefix']:'';		    
		    $mobile_number = $mobile_prefix.$mobile_number;
		    		    
		    $model = AR_clientsignup::model()->find('contact_phone=:contact_phone', 
		    array(':contact_phone'=>$mobile_number)); 
		    if(!$model){		    	
		    	$model = new AR_clientsignup;		
		    	$model->capcha = $capcha;
			    $model->recaptcha_response = $recaptcha_response;	
		    	$model->scenario = 'registration_phone';
		    	$model->phone_prefix = $mobile_prefix;
		    	$model->contact_phone = $mobile_number;
		    	$model->mobile_verification_code = $digit_code;
		    	$model->status='pending';
				$model->merchant_id = 0;
				
		    	if ($model->save()){
		    		$this->code = 1;
		    		$this->msg = "OK";
		    		$this->details = array(
		    		  'client_uuid'=>$model->client_uuid
		    		);		    	
		    		if(DEMO_MODE==TRUE){
		    			$this->details['verification_code']=t("Your verification code is {{code}}",array('{{code}}'=>$digit_code));
		    		}
		    	} else $this->msg = CommonUtility::parseError( $model->getErrors() );
		    } else {
		    	if($model->status=='pending'){		    		
		    		$model->scenario = 'registration_phone';
		    		$model->capcha = $capcha;
			        $model->recaptcha_response = $recaptcha_response;	
		    		$model->mobile_verification_code = $digit_code;
		    		if ($model->save()){
			    		$this->code = 1;
			    		$this->msg = "OK";
			    		$this->details = array(
			    		  'client_uuid'=>$model->client_uuid
			    		);			    	
			    		if(DEMO_MODE==TRUE){
			    			$this->details['verification_code']=t("Your verification code is {{code}}",array('{{code}}'=>$digit_code));
			    		}			    				    	
		    		} else $this->msg = CommonUtility::parseError( $model->getErrors() );
		    		
		    	} else $this->msg[]  = t("Phone number already exist");		    	
		    }		    	
		    
		} catch (Exception $e) {
		    $this->msg[] = $e->getMessage();		    
		}
		$this->responseJson();	
	}
	
	public function actionverifyCode()
	{		
		try {
			
			$client_uuid = isset($this->data['client_uuid'])?$this->data['client_uuid']:'';
			$verification_code = isset($this->data['verification_code'])?intval($this->data['verification_code']):'';
			
			$redirect_to = isset($this->data['redirect_to'])?$this->data['redirect_to']:'';
			$auto_login = isset($this->data['auto_login'])?$this->data['auto_login']:'';
			
			$model = AR_clientsignup::model()->find('client_uuid=:client_uuid', 
		    array(':client_uuid'=>$client_uuid)); 
		    		    		   
		    if($model){
		    	$model->scenario = 'complete_standard_registration';
		    	if($model->mobile_verification_code==$verification_code){
		    		$model->account_verified = 1;
		    		
		    		if($auto_login==1){
		    			$model->status='active';
		    		}
		    				    		
		    		if($model->save()){
			    		$this->code = 1;
			    		$this->msg = "ok";		    		
			    		
			    		if($auto_login==1){
			    			$this->msg = t("Login successful");
			    			$this->details = array(
							  'redirect'=>!empty($redirect_to)?$redirect_to:Yii::app()->getBaseUrl(true)
							);			
							
							//AUTO LOGIN						
							$login=new AR_customer_autologin;
							$login->username = $model->email_address;
							$login->password = $model->password;
							$login->rememberMe = 1;
							if($login->validate() && $login->login() ){
								//
							} 
			    		}
		    		} else $this->msg = CommonUtility::parseError( $model->getErrors() );
		    		
		    	} else $this->msg[] = t("Invalid verification code");
		    } else $this->msg[] = t("Records not found");
			
		} catch (Exception $e) {							
		    $this->msg[] = t($e->getMessage());
		}
		$this->responseJson();	
	}
	
	public function actioncompleteSignup()
	{
		try {
			
			$client_uuid = isset($this->data['client_uuid'])?$this->data['client_uuid']:'';
			$next_url = isset($this->data['next_url'])?$this->data['next_url']:'';
			
			$model = AR_clientsignup::model()->find('client_uuid=:client_uuid', 
		    array(':client_uuid'=>$client_uuid)); 
		    if($model){
		    	$model->scenario = 'complete_registration';
		    	if($model->account_verified==1){
			    	$model->first_name = isset($this->data['firstname'])?$this->data['firstname']:'';
			    	$model->last_name = isset($this->data['lastname'])?$this->data['lastname']:'';
			    	$model->email_address = isset($this->data['email_address'])?$this->data['email_address']:'';
			    				    	
			    	$model->password = isset($this->data['password'])? trim($this->data['password']) :'';
			    	$model->cpassword = isset($this->data['cpassword'])? trim($this->data['cpassword']) :'';			    
			    	$password = isset($this->data['password'])? trim($this->data['password']) :'';			    				    	
			    	$model->status='active';

					if($local_id = CommonUtility::getCookie(Yii::app()->params->local_id)){
						$model->local_id = $local_id;
					}
					
					// CUSTOM FIELDS			
					$field_data = [];
					$custom_fields = isset($this->data['custom_fields'])?$this->data['custom_fields']:'';					
					$field_data = AttributesTools::getCustomFields('customer','key2'); 							
					$model->custom_fields = $custom_fields;			
					CommonUtility::validateCustomFields($field_data,$custom_fields);					

			    	if ($model->save()){
			    		$this->code = 1;
			    		$this->msg = t("Registration successful");
			    		
			    		$redirect = !empty($next_url)?$next_url:Yii::app()->getBaseUrl(true);
			    		
			    		$this->details = array(
						  'redirect_url'=>$redirect
						);			
						
						//AUTO LOGIN
						$this->autoLogin($model->email_address,$password);
						
			    	} else $this->msg = CommonUtility::parseError( $model->getErrors() );
		    	} else $this->msg[] = t("Accout not verified");		    	
		    } else $this->msg[] = t("Records not found");
		} catch (Exception $e) {
		    $this->msg[] = $e->getMessage();		    
		}
		$this->responseJson();	
	}
	
	public function actionSocialRegister()
	{		
		try {
									
			$digit_code = CommonUtility::generateNumber(4,true);
			$redirect_to = isset($this->data['redirect_to'])?$this->data['redirect_to']:'';
			$email_address = isset($this->data['email_address'])?$this->data['email_address']:'';
			$id = isset($this->data['id'])?$this->data['id']:'';			
			$verification = isset($this->data['verification'])?$this->data['verification']:'';	
			$social_strategy = isset($this->data['social_strategy'])?$this->data['social_strategy']:'';	
			$social_token = isset($this->data['social_token'])?$this->data['social_token']:'';	
												
			$model = AR_clientsignup::model()->find('email_address=:email_address', 
		    array(':email_address'=>$email_address)); 
		    if(!$model){
		    	$model = new AR_clientsignup;		
		    	$model->scenario = 'registration_social';		    	
		    	$model->social_token = $social_token;
		    	$model->email_address = $email_address;
		    	$model->password = $id;		    	
		    	$model->social_id = $id;
		    	$model->first_name = isset($this->data['first_name'])?$this->data['first_name']:'';
		    	$model->last_name = isset($this->data['last_name'])?$this->data['last_name']:'';
		    	$model->mobile_verification_code = $digit_code;
		    	$model->status = $verification==1?'pending':'active';
		    	$model->social_strategy = $social_strategy;		    	
		    	$model->account_verified  = $verification==1?0:1;
				$model->merchant_id = 0;
		    	
		    	if ($model->save()){			    					    	
		    		$this->SocialRegister($verification,$model,$redirect_to);
		    	} else $this->msg = CommonUtility::parseError( $model->getErrors() );
		    } else {		    	
		    	$model->scenario = 'social_login';		
		    	$model->social_strategy = $social_strategy;	
		    	$model->social_token = $social_token;    		    	
		    	if($model->status=='pending' && $model->social_id==$id){
		    		$model->mobile_verification_code = $digit_code;
		    		if ($model->save()){
		    			$this->SocialRegister($verification,$model,$redirect_to);
		    		} else $this->msg = CommonUtility::parseError( $model->getErrors() );
		    	} elseif ( $model->status=="active" ){		 
		    				    	
		    		$model->password = md5($id);	
		    		if ($model->save()){
		    			
		    			//AUTO LOGIN
			    		$this->autoLogin($model->email_address,$id);			    		
			    		
			    		$this->code = 1;
			    		$this->msg = t("Login successful");
						$this->details = array(
						  'redirect'=>!empty($redirect_to)?$redirect_to:Yii::app()->getBaseUrl(true)
						);			
		    		} else $this->msg = CommonUtility::parseError( $model->getErrors() );
		    	} else $this->msg[] = t("Your account is {{status}}",array('{{status}}'=> t($model->status) ) );
		    }
			
		} catch (Exception $e) {
		    $this->msg[] = $e->getMessage();		    
		}		
		$this->responseJson();
	}
	
	private function SocialRegister($verification='',$model=null ,$redirect_to='')
	{
		$this->code = 1;			
		$redirect='';
				
		if($verification==1){
			// SEND EMAIL CODE
			$this->msg = t("Please wait until we redirect you");				
			
			$redirect = Yii::app()->createUrl("/account/verification",array(
			  'uuid'=>$model->client_uuid,
			  'redirect_to'=>$redirect_to
			));
		
		} else {			
			
			$this->msg = t("Login successful");
			$redirect = Yii::app()->createUrl("/account/complete_registration",array(
			  'uuid'=>$model->client_uuid,
			  'redirect_to'=>$redirect_to
			));						
		}
		$this->details = array(		    		  
		  'redirect'=>$redirect
		);
	}
		
	private function autoLogin($username='',$password='')
	{		
		$login=new AR_customer_login;
		$login->username = $username;
		$login->password = $password;
		$login->rememberMe = 1;
		if($login->validate() && $login->login() ){
			//echo 'ok';
		} //else dump( $model->getErrors() );			
	}
	
	public function actiongetCustomerInfo()
	{
		try {
			
			$client_uuid = isset($this->data['client_uuid'])?$this->data['client_uuid']:'';
			$model = AR_clientsignup::model()->find('client_uuid=:client_uuid', 
		    array(':client_uuid'=>$client_uuid)); 
		    if($model){
		    	$this->code = 1;
		    	$this->msg  = "Ok";
		    	$this->details = array(
		    	  'firstname'=>$model->first_name,
		    	  'lastname'=>$model->last_name,
		    	  'email_address'=>$model->email_address,
		    	);
		    } else $this->msg[] = t("Records not found");						
		} catch (Exception $e) {
		    $this->msg[] = $e->getMessage();		    
		}
		$this->responseJson();	
	}
	
	public function actioncompleteSocialSignup()
	{
		try {
		    						
			$client_uuid = isset($this->data['client_uuid'])?$this->data['client_uuid']:'';
			$next_url = isset($this->data['next_url'])?$this->data['next_url']:'';
			$prefix = isset($this->data['mobile_prefix'])?$this->data['mobile_prefix']:'';
		    $mobile_number = isset($this->data['mobile_number'])?$this->data['mobile_number']:'';
		    		   
			$model = AR_clientsignup::model()->find('client_uuid=:client_uuid', 
		    array(':client_uuid'=>$client_uuid)); 
		    if($model){
		    	$model->scenario = 'complete_social_registration';
		    	$password = $model->social_id;
		    	if($model->account_verified==1){
		    		$model->first_name = isset($this->data['firstname'])?$this->data['firstname']:'';
			    	$model->last_name = isset($this->data['lastname'])?$this->data['lastname']:'';
		    		$model->contact_phone = $prefix.$mobile_number;
		    		$model->phone_prefix = $prefix;		    		
		    		$model->status='active';
		    		if ($model->save()){
		    			
		    			$this->code = 1;
			    		$this->msg = t("Registration successful");
			    		
			    		$redirect = !empty($next_url)?$next_url:Yii::app()->getBaseUrl(true);
			    		
			    		$this->details = array(
						  'redirect_url'=>$redirect
						);			
						
						//AUTO LOGIN
						$this->autoLogin($model->email_address,$password);
						
		    		} else $this->msg = CommonUtility::parseError( $model->getErrors() );		    		
		    	} else $this->msg[] = t("Accout not verified");	
		    } else $this->msg[] = t("Records not found");			
		} catch (Exception $e) {
		    $this->msg[] = $e->getMessage();		    
		}
		$this->responseJson();	
	}
	
	public function actionrequestCode()
	{
		try {
			
		   $client_uuid = isset($this->data['client_uuid'])?$this->data['client_uuid']:'';
		   
		   $model = AR_clientsignup::model()->find('client_uuid=:client_uuid', 
		   array(':client_uuid'=>$client_uuid)); 
		   if($model){
		   	  $digit_code = CommonUtility::generateNumber(4,true);
		   	  $model->mobile_verification_code = $digit_code;
			  $model->scenario = 'resend_otp';
		   	  if($model->save()){	
		   	  	 
		   	  	   // SEND EMAIL HERE  
		   	  	   	   	  	
		   	  	   $this->code = 1;
		           $this->msg = t("We sent a code to {{email_address}}.",array(
		             '{{email_address}}'=> CommonUtility::maskEmail($model->email_address)
		           ));			    
				   
				   if(DEMO_MODE){
						$this->details = [
							'verification_code'=>t("Your OTP is {{otp}}",['{{otp}}'=> $digit_code ])
						];
				   }	 
				   
		   	  } else $this->msg = CommonUtility::parseError($model->getErrors());		   	  
		   } else $this->msg[] = t("Records not found");
		   
		} catch (Exception $e) {							
		    $this->msg[] = t($e->getMessage());
		}
		$this->responseJson();	
	}
	
	public function actionrequestCodePhone()
	{
		try {
			
		   $client_uuid = isset($this->data['client_uuid'])?$this->data['client_uuid']:'';		   
		   
		   $model = AR_clientsignup::model()->find('client_uuid=:client_uuid', 
		   array(':client_uuid'=>$client_uuid)); 
		   if($model){
		   	  $digit_code = CommonUtility::generateNumber(4,true);
		   	  $model->scenario = 'resend_otp';
		   	  $model->mobile_verification_code = $digit_code;
		   	  if($model->save()){	
		   	  	 		   	  	   	   	  
		   	  	   $this->code = 1;
		           $this->msg = t("We sent a code to +[contact_phone].",array(
		             '[contact_phone]'=> $model->contact_phone
		           ));			          
		   	  } else $this->msg = CommonUtility::parseError($model->getErrors());		   	  
		   } else $this->msg[] = t("Records not found");
		   
		} catch (Exception $e) {							
		    $this->msg[] = t($e->getMessage());
		}
		$this->responseJson();	
	}
	
	public function actionrequestResetPassword()
	{
		try {
			
			$email_address = isset($this->data['email_address'])?$this->data['email_address']:'';			
			$model = AR_clientsignup::model()->find('email_address=:email_address AND status=:status AND merchant_id=:merchant_id', 
		    array(
				':email_address'=>$email_address,
				':status'=>'active',
				':merchant_id'=>0
			)); 
		    if($model){				
		    	if($model->status=="active"){
		    		$model->scenario = "reset_password";
		    		$model->reset_password_request = 1;
					$model->mobile_verification_code =  CommonUtility::generateNumber(4,true);
		    		if($model->save()){											
						$this->code = 1;
						$this->msg = t("Check {{email_address}} for an email to reset your password.",array(
						'{{email_address}}'=>$model->email_address
						));
						$this->details = array(
						   'uuid'=>$model->client_uuid,
						   'steps'=>2
						);
					} else {
						$this->msg = CommonUtility::parseError($model->getErrors());
					}							    				    	
		    	} else $this->msg[] = t("Your account is either inactive or not verified.");
		    } else $this->msg[] = t("No email address found in our records. please verify your email.");
			
		} catch (Exception $e) {							
		    $this->msg[] = t($e->getMessage());
		}
		$this->responseJson();	
	}

	public function actionrequestResetpasswordsms()
	{
		try {

			$steps = 3;			
			$mobile_number = isset($this->data['mobile_number'])?$this->data['mobile_number']:'';
			$mobile_prefix = isset($this->data['mobile_prefix'])?$this->data['mobile_prefix']:'';			
			$contact_phone=$mobile_prefix.$mobile_number;

			$model = AR_clientsignup::model()->find("contact_phone=:contact_phone AND status=:status AND merchant_id=:merchant_id AND social_strategy!='guest' ", 
			   array(
				':contact_phone'=>$contact_phone,
				':status'=>'active',
				':merchant_id'=>0
			)); 
			if($model){					
				$model->scenario = 'reset_password_sms';
				$model->reset_password_request = 1;
				$model->verify_code_requested = CommonUtility::dateNow();
				$model->mobile_verification_code =  CommonUtility::generateNumber(4,true);
				if($model->save()){	
					$this->code =1;
					$this->msg = t("An OTP has been sent to your phone number {{mobile_number}}",array(
						'{{mobile_number}}'=>CommonUtility::mask($contact_phone)
					));							
					$this->details = array(
						'uuid'=>$model->client_uuid,
						'steps'=>$steps
					);					
				} else $this->msg = CommonUtility::parseError($model->getErrors());
			} else $this->msg[] = t("We couldn't find any account associated with the provided phone number.");
		} catch (Exception $e) {							
			$this->msg[] = t($e->getMessage());
		}
		$this->responseJson();	
	}

	public function actionverifyOTP()
	{
		try {

			$client_uuid = Yii::app()->input->post('client_uuid');
			$otp = Yii::app()->input->post('otp');
			$model = AR_clientsignup::model()->find('client_uuid=:client_uuid', array(':client_uuid'=>$client_uuid)); 
			if($model){				
				if($model->reset_password_request==1){
					if(trim($model->mobile_verification_code)===trim($otp)){
						$this->code = 1;
						$this->msg = t(Helper_success);
						$this->details = [
							'redirect'=>Yii::app()->createAbsoluteUrl("/account/reset_password",[
								'token'=>$client_uuid
							])
						];
					} else $this->msg = t("The OTP code you entered is invalid. Please double-check the code and try again.");
				} else $this->msg = t("Account has no request for OTP");
			} else $this->msg = t(HELPER_RECORD_NOT_FOUND);
			
		} catch (Exception $e) {							
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();	
	}

	public function actionresendresetpasswordsms()
	{
		try {			
			$client_uuid = isset($this->data['client_uuid'])?$this->data['client_uuid']:'';			
			$model = AR_clientsignup::model()->find('client_uuid=:client_uuid', array(':client_uuid'=>$client_uuid)); 
			if($model){	
				$model->scenario = 'reset_password_sms';
				$model->reset_password_request = 1;
				$model->verify_code_requested = CommonUtility::dateNow();
				$model->mobile_verification_code =  CommonUtility::generateNumber(4,true);
				if($model->save()){	
					$this->code =1;
					$this->msg = t("An OTP has been sent to your phone number {{mobile_number}}",array(
						'{{mobile_number}}'=>CommonUtility::mask($model->contact_phone)
					));							
					$this->details = array(
						'uuid'=>$model->client_uuid,						
					);					
				} else $this->msg = CommonUtility::parseError($model->getErrors());
			} else $this->msg[] = t("We couldn't find any account associated with the provided phone number.");
		} catch (Exception $e) {							
			$this->msg[] = t($e->getMessage());
		}
		$this->responseJson();	
	}

	public function actionresendResetEmail()
	{
		try {
			
		   $client_uuid = isset($this->data['client_uuid'])?$this->data['client_uuid']:'';
		   
		   $model = AR_clientsignup::model()->find('client_uuid=:client_uuid', 
		   array(':client_uuid'=>$client_uuid)); 
		   if($model){		   	  
			  $model->scenario = "reset_password";
		   	  $model->reset_password_request = 1;		
			  $model->mobile_verification_code =  CommonUtility::generateNumber(4,true);    		
		   	  if($model->save()){			   	  	 
		   	  	      	  	   	   	  
		   	  	   $this->code = 1;
		           $this->msg = t("Check {{email_address}} for an email to reset your password.",array(
		    		  '{{email_address}}'=>$model->email_address
		    	   ));

		   	  } else $this->msg = CommonUtility::parseError($model->getErrors());		   	  
		   } else $this->msg[] = t("Records not found");
		   
		} catch (Exception $e) {							
		    $this->msg[] = t($e->getMessage());
		}
		$this->responseJson();	
	}
	
	public function actionresetPassword()
	{
		try {
			
		    $client_uuid = isset($this->data['client_uuid'])?$this->data['client_uuid']:'';
		    $password = isset($this->data['password'])?$this->data['password']:'';
		    $cpassword = isset($this->data['cpassword'])?$this->data['cpassword']:'';
		    
		    $model = AR_client::model()->find('client_uuid=:client_uuid', 
		    array(':client_uuid'=>$client_uuid)); 		
		    
		    if($model){
		    	if($model->status=="active"){
		    				    		
		    		$model->scenario = "reset_password";
		    		$model->npassword =  $password;
		    		$model->cpassword =  $cpassword;
		    		$model->password = md5($password);
		    		$model->reset_password_request = 0;
		    		
		    		if($model->save()){
					    $this->code = 1;
					    $this->msg  = t("Your password is now updated.");
					    $this->details = array(
					      'redirect'=>Yii::app()->createUrl("/account/login")
					    );
		    		} else $this->msg =  CommonUtility::parseError( $model->getErrors() );;
		    		
		    	} else $this->msg[] = t("Account not active");
		    } else $this->msg[] = t("Records not found"); 
		    
		} catch (Exception $e) {							
		    $this->msg[] = t($e->getMessage());
		}
		$this->responseJson();	
	}	
	
	public function actiongetProfile()
	{
		try {
			$client_id = Yii::app()->user->id;
			$model = AR_client::model()->find('client_id=:client_id', 
		    array(':client_id'=> intval($client_id) )); 		
			if($model){
				$this->code = 1; $this->msg = "ok";
				$custom_fields = AttributesTools::getCustomFieldsValue($client_id);				
				$this->details = array(
				  'first_name'=>$model->first_name,
				  'last_name'=>$model->last_name,
				  'email_address'=>$model->email_address,
				  'mobile_prefix'=>$model->phone_prefix,
				  'mobile_number'=>substr($model->contact_phone,strlen($model->phone_prefix)),
				  'custom_fields'=>$custom_fields
				);			
			} else $this->msg = t("User not login or session has expired");
		} catch (Exception $e) {							
		    $this->msg[] = t($e->getMessage());
		}
		$this->responseJson();
	}
	
	public function actionsaveProfile()
	{
		try {
						
			$code = isset($this->data['code'])?$this->data['code']:'';
		    $email_address = isset($this->data['email_address'])?$this->data['email_address']:'';
		    $mobile_prefix = isset($this->data['mobile_prefix'])?$this->data['mobile_prefix']:'';
		    $mobile_number = isset($this->data['mobile_number'])?$this->data['mobile_number']:'';
		    $contact_number = $mobile_prefix.$mobile_number;
		    
		    $model = AR_client::model()->find('client_id=:client_id', 
		    array(':client_id'=> intval(Yii::app()->user->id) )); 	
		    if($model){
		    	$_change = false;
		    	if ($model->email_address!=$email_address){
		    		$_change = true;
		    	}
		    	if ($model->contact_phone!=$contact_number){
		    		$_change = true;
		    	}
		    	if($_change){
		    		if($model->mobile_verification_code!=$code){
		    			$this->msg[] = t("Invalid verification code");
		    			$this->responseJson();
		    			Yii::app()->end();
		    		}
		    	}

		    	$model->first_name = isset($this->data['first_name'])?$this->data['first_name']:'';
		    	$model->last_name = isset($this->data['last_name'])?$this->data['last_name']:'';
		    	$model->email_address = $email_address;
		    	$model->phone_prefix = $mobile_prefix;
		    	$model->contact_phone = $contact_number;

				// CUSTOM FIELDS			
				$field_data = [];
				$custom_fields = isset($this->data['custom_fields'])?$this->data['custom_fields']:'';					
				$field_data = AttributesTools::getCustomFields('customer','key2'); 							
				$model->custom_fields = $custom_fields;			
				CommonUtility::validateCustomFields($field_data,$custom_fields);

		    	if($model->save()){
		    		$this->code = 1;
		    		$this->msg = t("Profile updated");

					Yii::app()->user->contact_number = $contact_number;
					Yii::app()->user->email_address = $email_address;

		    	} else $this->msg = CommonUtility::parseError( $model->getErrors() );
		    		    	
		    } else $this->msg = t("User not login or session has expired");
		    		    
		} catch (Exception $e) {							
		    $this->msg[] = t($e->getMessage());
		}
		$this->responseJson();
	}
	
	public function actionupdatePassword()
	{
		try {
					   
		   $model = AR_client::model()->find('client_id=:client_id', 
		   array(':client_id'=> intval(Yii::app()->user->id) )); 	
		   if($model){
		   	   //array('old_password,npassword,cpassword', 'required', 'on'=>'update_password'), 
		   	   $model->scenario = 'update_password';
		   	   $model->old_password = isset($this->data['old_password'])?$this->data['old_password']:'';
		   	   $model->npassword = isset($this->data['new_password'])?$this->data['new_password']:'';
		   	   $model->cpassword = isset($this->data['confirm_password'])?$this->data['confirm_password']:'';
		   	   $model->password = md5($model->npassword);
		   	   if($model->save()){
		    	  $this->code = 1;
		    	  $this->msg = t("Password change");
		      } else $this->msg = CommonUtility::parseError( $model->getErrors() );		   	   
		   } else $this->msg[] = t("User not login or session has expired");
		   		   
		} catch (Exception $e) {							
		    $this->msg[] = t($e->getMessage());
		}
		$this->responseJson();
	}
	
	public function actionverifyAccountDelete()
	{
		$code = isset($this->data['code'])?$this->data['code']:'';
		$model = AR_client::model()->find('client_id=:client_id', 
		array(':client_id'=> intval(Yii::app()->user->id) )); 	
		if($model){
			if($model->mobile_verification_code==$code){
			   	$this->code = 1;
			   	$this->msg = "ok";			   	
			} else $this->msg[] = t("Invalid verification code");
		} else $this->msg[] = t("User not login or session has expired");
		$this->responseJson();
	}
	
	public function actiondeleteAccount()
	{		
		$code = isset($this->data['code'])?$this->data['code']:'';
		$model = AR_client::model()->find('client_id=:client_id', 
		array(':client_id'=> intval(Yii::app()->user->id) )); 	
		if($model){
			if($model->mobile_verification_code==$code){			   	
				$model->status = "deleted";
				$model->save();
			   	Yii::app()->user->logout(false);
			   	$this->code = 1;
			   	$this->msg = "ok";
			   	$this->details = array(
			   	  'redirect'=>Yii::app()->getBaseUrl(true)
			   	);
			} else $this->msg[] = t("Invalid verification code");
		} else $this->msg[] = t("User not login or session has expired");
		$this->responseJson();
	}
	
	public function actionrequestData()
	{		
		$model = AR_client::model()->find('client_id=:client_id', 
		array(':client_id'=> intval(Yii::app()->user->id) )); 	
		if($model){
			$gpdr = AR_gpdr_request::model()->find('client_id=:client_id AND request_type=:request_type AND status=:status', 
		    array( 
		      ':client_id'=> intval(Yii::app()->user->id),
		      ':request_type'=> 'request_data',
		      ':status'=> 'pending'
		    )); 			    
		    if(!$gpdr){
				$gpdr = new AR_gpdr_request;
				$gpdr->request_type = "request_data";
				$gpdr->client_id = intval(Yii::app()->user->id);
				$gpdr->first_name = $model->first_name;
				$gpdr->last_name = $model->last_name;
				$gpdr->email_address = $model->email_address;
				if($gpdr->save()){
					$this->code = 1;
				   	$this->msg = "ok";
				} else $this->msg = CommonUtility::parseError( $model->getErrors() );
		    } else $this->msg[] = t("You have already existing request.");
		} else $this->msg[] = t("User not login or session has expired");
		$this->responseJson();
	}
	
	public function actionuploadProfilePhoto()
	{
		$upload_uuid = CommonUtility::generateUIID();
		$allowed_extension = CommonUtility::safeExplode(",",  Yii::app()->params['upload_type']);
		$maxsize = (integer) Yii::app()->params['upload_size'] ;
		if (!empty($_FILES)) {
			
			$title = $_FILES['file']['name'];   
			$file_size = (integer)$_FILES['file']['size'];   
			$filetype = $_FILES['file']['type'];   								
			
			
			if(isset($_FILES['file']['name'])){
			   $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
			} else $extension = strtolower(substr($title,-3,3));
			
			if(!in_array($extension,$allowed_extension)){			
				$this->msg = t("Invalid file extension");
				$this->jsonResponse();
			}
			if($file_size>$maxsize){
				$this->msg = t("Invalid file size");
				$this->jsonResponse();
			}
			
			$allowed_extension = CommonUtility::safeExplode(",",Helper_imageType);
		    $maxsize = (integer)Helper_maxSize;			
		    
		    if(isset($_FILES['file']['name'])){
			   $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
			   $extension = strtolower($extension);
			} else $extension = strtolower(substr($title,-3,3)); 	
				
			if(!in_array($extension,$allowed_extension)){			
				$this->msg = t("Invalid file extension");
				$this->jsonResponse();
			}
			if($file_size>$maxsize){
				$this->msg = t("Invalid file size, allowed size are {{size}}",array(
				 '{{size}}'=>CommonUtility::HumanFilesize($maxsize)
				));
				$this->jsonResponse();
			}
			
			$upload_path = CMedia::avatarFolder();
			$tempFile = $_FILES['file']['tmp_name'];
			$upload_uuid = CommonUtility::createUUID("{{media_files}}",'upload_uuid');
			$filename = $upload_uuid.".$extension";			
			$path = CommonUtility::uploadDestination($upload_path)."/".$filename;						
			$path2 = CommonUtility::uploadDestination($upload_path)."/";
						
			if(move_uploaded_file($tempFile,$path)){					
			   	$this->code = 1; $this->msg = "OK";	
				$this->details = array(			   
				   'url_image'=> CMedia::getImage($filename,$upload_path,'',CommonUtility::getPlaceholderPhoto('customer')),
				   'filename'=>$filename,
				   'id'=>$upload_uuid			   
				);		
			} else $this->msg = t("Failed cannot upload file.");
					
		} else $this->msg = t("Invalid file");
	
		$this->jsonResponse();		
	}
	
	public function actionsaveProfilePhoto()
	{				
		$model = AR_client::model()->find('client_id=:client_id', 
		array(':client_id'=> intval(Yii::app()->user->id) )); 	
		if($model){
			$filename = isset($this->data['filename'])?$this->data['filename']:'';
			$img = isset($_POST['photo'])?$_POST['photo']:'';						
			if(!empty($filename)  && !empty($img)){									
				$upload_path = CMedia::avatarFolder();
				$path = CommonUtility::uploadDestination($upload_path)."/".$filename;
				$img = str_replace('data:image/png;base64,', '', $img);
				$img = str_replace(' ', '+', $img);
				$data = base64_decode($img);		
				@file_put_contents($path,$data);

				$delete_old_image = CommonUtility::uploadDestination($model->path)."/".$model->avatar;
				if(file_exists($delete_old_image)){
					@unlink($delete_old_image);
				}
				
				$model->avatar = $filename;
				$model->path = $upload_path;
				if($model->save()){
					$this->code = 1;
					$this->msg = t("Profile photo saved");
					
					$url_image = CMedia::getImage($filename,$upload_path,'',CommonUtility::getPlaceholderPhoto('customer'));
					Yii::app()->user->avatar = $url_image;						 
					
					$this->details = array(			   
					   'url_image'=>$url_image,
					   'filename'=>$filename,					   
					);	
				} else $this->msg = CommonUtility::parseError( $model->getErrors() );						
			} else $this->msg[] = t("Invalid data");			
		} else $this->msg[] = t("User not login or session has expired");
		$this->responseJson();		
	}
	
	public function actionremoveProfilePhoto()
	{				
		$id = isset($this->data['id'])?$this->data['id']:'';
			if(!empty($id)){
			$upload_path = CMedia::avatarFolder();
			$path = CommonUtility::uploadDestination($upload_path)."/".$id;
			if(file_exists($path)){
				@unlink($path);
				$this->code = 1;
				$this->msg = "OK";			
			} else $this->msg = t("File not found");
		} else $this->msg = t("ID is empty");
		$this->responseJson();
	}
	
	public function actioncheckStoreOpen()
	{		
		try {
						
			$merchant_id = isset($this->data['merchant_id'])?intval($this->data['merchant_id']):'';

			// CHECK IF MERCHANT HAS DIFFERENT TIMEZONE
			$options_merchant = OptionsTools::find(['merchant_timezone','merchant_time_selection'],$merchant_id);
			$merchant_timezone = isset($options_merchant['merchant_timezone'])?$options_merchant['merchant_timezone']:'';		
			if(!empty($merchant_timezone)){
				Yii::app()->timezone = $merchant_timezone;
			}			

			$time_selection = isset($options_merchant['merchant_time_selection'])?$options_merchant['merchant_time_selection']:1;
			$time_selection = !empty($time_selection)?$time_selection:1;	
			$time_required = false;		
			
			$date = date("Y-m-d");
			$time_now = date("H:i");
			
			$choosen_delivery = isset($this->data['choosen_delivery'])?$this->data['choosen_delivery']:'';		
			$whento_deliver = isset($choosen_delivery['whento_deliver'])?$choosen_delivery['whento_deliver']:'';			
			
			if($whento_deliver=="schedule"){
				$date = isset($choosen_delivery['delivery_date'])?$choosen_delivery['delivery_date']:$date;
				$time_now = isset($choosen_delivery['delivery_time'])?$choosen_delivery['delivery_time']['start_time']:$time_now;
			} else {				
				$time_required = $time_selection==2?true:false;
			}			
												
			try {
				$datetime_to = date("Y-m-d g:i:s a",strtotime("$date $time_now"));
				CMerchantListingV1::checkCurrentTime( date("Y-m-d g:i:s a") , $datetime_to);
				$time_already_passed = false;
			} catch (Exception $e) {
				$time_already_passed = true;
			}
			
			$resp = CMerchantListingV1::checkStoreOpen($merchant_id,$date,$time_now);						
			$this->code = 1;
			$this->msg = $resp['merchant_open_status']>0?"ok": ($time_selection==3?"Store is closed.":t("This store is close right now, but you can schedulean order later."))  ;
			if($resp['holiday_id']>0 && !empty($resp['holiday_reason']) ){
				$this->msg  = t("Were on holidays")." : ". $resp['holiday_reason'];
			}
			$this->details =  $resp;
			$this->details['time_already_passed'] = $time_already_passed;
			$this->details['time_required'] = $time_required;
			$this->details['time_required_message'] = t("Please Select Date/Time");
			$this->details['time_selection'] = $time_selection;
					
		} catch (Exception $e) {							
		    $this->msg = t($e->getMessage());		    
		}					
		$this->responseJson();
	}
	
	public function actionstoreAvailable()
	{
		try {		   
			
			$merchant_uuid = Yii::app()->input->post('merchant_uuid');
			CMerchantListingV1::storeAvailable($merchant_uuid);
			$this->code = 1; $this->msg = "ok";
		} catch (Exception $e) {							
		    $this->msg = t($e->getMessage());		    
		}					
		$this->responseJson();
	}
	
	public function actiongetWebpushSettings()
	{
		try {						
						
			$settings = AR_admin_meta::getMeta(array('webpush_app_enabled','webpush_provider','pusher_instance_id','onesignal_app_id'
			));		
						
			$enabled = isset($settings['webpush_app_enabled'])?$settings['webpush_app_enabled']['meta_value']:'';
			$provider = isset($settings['webpush_provider'])?$settings['webpush_provider']['meta_value']:'';
			$pusher_instance_id = isset($settings['pusher_instance_id'])?$settings['pusher_instance_id']['meta_value']:'';			
			$onesignal_app_id = isset($settings['onesignal_app_id'])?$settings['onesignal_app_id']['meta_value']:'';	
			
			$user_settings = array();
			
			try {
			   $user_settings = CNotificationData::getUserSettings(Yii::app()->user->id,'client');		
			   array_unshift($user_settings['interest'], Yii::app()->user->client_uuid);
			} catch (Exception $e) {
			   //
			}
									
			$data = array(
			  'enabled'=>$enabled,
			  'provider'=>$provider,
			  'pusher_instance_id'=>$pusher_instance_id,			  
			  'onesignal_app_id'=>$onesignal_app_id,
			  'safari_web_id'=>'',			  
			  'user_settings'=>$user_settings,
			);				
			$this->code = 1;
			$this->msg = "OK";
			$this->details = $data;
						
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();		
	}
	
	public function actiongetNotifications()
	{
		try {											
			$data = CNotificationData::getList( Yii::app()->user->client_uuid );			
			$this->code = 1; $this->msg = "ok";
			$this->details = $data;
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();		
	}
		
	public function actionclearNotifications()
	{
		try {						
						
			AR_notifications::model()->deleteAll('notication_channel=:notication_channel',array(
			 ':notication_channel'=> Yii::app()->user->client_uuid
			));
			$this->code = 1; $this->msg = "ok";
						
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();		
	}
	
    public function actiongetwebnotifications()
	{
		try {
			
			$data = CNotificationData::getUserSettings(Yii::app()->user->id,'client');
			$this->code = 1;
		    $this->msg = "OK";
		    $this->details = $data;
			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();		
	}
	
	public function actionsavewebnotifications()
	{
		try {		
			
			$user_type='client';
					    			
		    $webpush_enabled = isset($this->data['webpush_enabled'])?intval($this->data['webpush_enabled']):0;	
		    $interest = isset($this->data['interest'])?$this->data['interest']:'';
		    $device_id = isset($this->data['device_id'])?$this->data['device_id']:'';
		    		    
		    $model = AR_device::model()->find("user_id=:user_id AND user_type=:user_type",array(
		      ':user_id'=>intval(Yii::app()->user->id),
		      ':user_type'=>$user_type
		    ));
		    if(!$model){
		       $model = new AR_device;			       
		    } 		    		    
		    $model->interest = $interest;
		    $model->user_type = $user_type;
	    	$model->user_id = intval(Yii::app()->user->id);
	    	$model->platform = "web";
	    	$model->device_token = $device_id;
	    	$model->browser_agent = $_SERVER['HTTP_USER_AGENT'];
	    	$model->enabled = $webpush_enabled;
	    	if($model->save()){
		   	   $this->code = 1;
			   $this->msg = t("Setting saved");		    
		    } else $this->msg = CommonUtility::parseError( $model->getErrors());
		    		   		    		    
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    		    
		}	
		$this->responseJson();		
	}	

	public function actionnotificationList()
	{
		try {
			
			PrettyDateTime::$category = 'front';

			$page = isset($this->data['page'])?intval($this->data['page']):0;	
			$length = Yii::app()->params->list_limit;
			$show_next_page = false;
					
			$criteria=new CDbCriteria();
			$criteria->condition="notication_channel=:notication_channel";
			$criteria->params = array(':notication_channel'=> Yii::app()->user->client_uuid );
			
			$criteria->order = "date_created DESC";
		    $count = AR_notifications::model()->count($criteria); 
		    
		    $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );        
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);        
            $model = AR_notifications::model()->findAll($criteria);
            
            if($model){
            	$data = array();
            	foreach ($model as $item) {
					
					$image=''; $url = '';
					if($item->image_type=="icon"){
						$image = !empty($item->image)?$item->image:'';
					} else {
						if(!empty($item->image)){
							$image = CMedia::getImage($item->image,$item->image_path,
							Yii::app()->params->size_image_thumbnail ,
							CommonUtility::getPlaceholderPhoto('item') );
						}
					}
					
					$params = !empty($item->message_parameters)?json_decode($item->message_parameters,true):'';
					
					$data[]=array(
					  'notification_type'=>$item->notification_type,
					  'message'=>t($item->message,(array)$params),
					  'date'=>PrettyDateTime::parse(new DateTime($item->date_created)),				  
					  'image_type'=>$item->image_type,
					  'image'=>$image,
					  'url'=>$url
					);
				}
				
				$page_count = $pages->getPageCount();					
				if($page_count > ($page+1) ){
				   $show_next_page = true;
				}
		  
				$this->code = 1; $this->msg = "OK";
				$this->details =  array(
				  'count'=>$count,
				  'show_next_page'=>$show_next_page,
				  'page'=>intval($page)+1,
				  'data'=>$data
				);				
            } else $this->msg = t("No results");
				
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    		    
		}	
		$this->responseJson();		
	}
	
	public function actiongetpaymentlist()
	{
		try {
					   		   
		   $data = CPayments::getPaymentList(1,'','',['stripe']);		   
		   $this->code = 1;
		   $this->msg = "ok";
		   $this->details = array(		     
		     'data'=>$data
		   );		   
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}
	
	public function actionmerchantsavedpayment()
	{
		try {

		   $default_payment_uuid = ''; $default_payment = array();
		   $merchant_uuid = Yii::app()->input->post('merchant_uuid');		   
		   $merchant = CMerchants::getByUUID($merchant_uuid);		   
		   
		   $model = AR_merchant_payment_method::model()->find("merchant_id=:merchant_id AND as_default=:as_default",array(
		     ':merchant_id'=>$merchant->merchant_id,
		     'as_default'=>1
		   ));
		    if($model){		  		    	 
		    	$default_payment_uuid=$model->payment_uuid;
		    	$default_payment = array(
		    	  'payment_uuid'=>$model->payment_uuid,
		    	  'payment_code'=>$model->payment_code
		    	);
		    }
		    
		    $data = CPayments::MerchantSavedPaymentList($merchant->merchant_id);		
		    $this->code = 1;
		    $this->msg = "ok";
		    $this->details = array(
		      'default_payment_uuid'=>$default_payment_uuid,
		      'default_payment'=>$default_payment,
		      'data'=>$data,
		    );		    
		   
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}		
		$this->responseJson();
	}
	
	public function actionmerchantsetdefaultpayment()
	{
		try {
									
			$payment_uuid = Yii::app()->input->post('payment_uuid');
			
			$model = AR_merchant_payment_method::model()->find("payment_uuid=:payment_uuid",array(			 
			 ":payment_uuid"=>$payment_uuid
			));
			
			if($model){
				$model->as_default = 1;
				$model->save();
				$this->code = 1;
		    	$this->msg = t("Succesful");
			} else $this->msg = t("Payment not found");	
			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}				
		$this->responseJson();
	}
	
	public function actionmerchantdeletesavedpaymentmethod()
	{
		try {
		   $payment_uuid = isset($this->data['payment_uuid'])?$this->data['payment_uuid']:'';		   
		   $model = AR_merchant_payment_method::model()->find("payment_uuid=:payment_uuid",array(			 
			 ":payment_uuid"=>$payment_uuid
			));
		   if($model){
		   	   $model->delete();
			   $this->code = 1;
			   $this->msg = "ok";
		   } else $this->msg = t("Payment not found");
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actiongetLanguage()
	{
		try {
			$data = WidgetLangselection::getData();		
			$this->code = 1; 
			$this->msg = "OK";
			$this->details = $data;
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actionSetBooking()
	{
		try {
			
			$reservation_date = Yii::app()->input->post('reservation_date');
            $reservation_time = Yii::app()->input->post('reservation_time');
			$guest = intval(Yii::app()->input->post('guest'));

			$full_time = "$reservation_date $reservation_time";			
			$full_time = Date_Formatter::dateTime($full_time);

			$guest = Yii::t('front', '{n} person|{n} persons', $guest );

			$this->code = 1;
			$this->msg = "OK";
			$this->details = [
				'full_time'=>$full_time,
				'guest'=>$guest,
			];
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actiongetlocationautocomplete()
	{
		try {

			$q = Yii::app()->input->post("q");
			
			if(!isset(Yii::app()->params['settings']['map_provider'])){
				$this->msg = t("No default map provider, check your settings.");
				$this->responseJson();
		    }

		   MapSdk::$map_provider = Yii::app()->params['settings']['map_provider'];		   
		   MapSdk::setKeys(array(
		     'google.maps'=>Yii::app()->params['settings']['google_geo_api_key'],
		     'mapbox'=>Yii::app()->params['settings']['mapbox_access_token'],
			 'yandex'=>isset(Yii::app()->params['settings']['yandex_geosuggest_api'])?Yii::app()->params['settings']['yandex_geosuggest_api']:'',
		   ));
		   		   
		   if ( $country_params = AttributesTools::getSetSpecificCountry()){			   
				if(MapSdk::$map_provider!="yandex"){
					MapSdk::setMapParameters(array(
						'country'=>$country_params
					));
				}		   	   
		   }		   

		   if(MapSdk::$map_provider=="yandex"){
			   MapSdk::setMapParameters(array(
		        'lang'=>isset(Yii::app()->params['settings']['yandex_language'])?Yii::app()->params['settings']['yandex_language']:'',
		       ));
		   }
		     		  
		   $resp = MapSdk::findPlace($q);		   		   
		   $this->code =1; $this->msg = "ok";
		   $this->details = array(
		     'data'=>$resp
		   );
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();
	}

	public function actiongetaddressdetails()
	{
		$local_id = CommonUtility::getCookie(Yii::app()->params->local_id);		
		$address_found = false;		
		$maps_config = CMaps::config();

		$count_address = CClientAddress::countAddress(Yii::app()->user->id);		

		$custom_field_enabled = isset(Yii::app()->params['settings']['custom_field_enabled']) ? Yii::app()->params['settings']['custom_field_enabled'] : false;
		$custom_field_enabled = $custom_field_enabled==1?true:false;
		
		try {
			$data = CClientAddress::getAddress($local_id,Yii::app()->user->id);							
			$this->code = 1;
			$this->msg = "OK";
			$address_found = true;
		} catch (Exception $e) {
			try {			
				$data = CMaps::locationDetails($local_id,'');
			} catch (Exception $e) {			
				try {
				   $data = MapSdk::reverseGeocoding($maps_config['default_lat'],$maps_config['default_lng']);			
			    } catch (Exception $e) {}
			}
		}				
		$this->details = array(		  
		  'address_found'=>$address_found,
		  'count_address'=>$count_address,
		  'data'=>$data,			  
		  'delivery_option'=>CCheckout::deliveryOption(),
		  'address_label'=>CCheckout::addressLabel(),
		  'maps_config'=>$maps_config,
		  'custom_field_enabled'=>$custom_field_enabled,
		  'custom_field1_data'=>CCheckout::customeFieldList()
		);						
		$this->responseJson();
	}

	public function actionregisterGuestUser()
	{
		try {		
			
			$social_strategy  = "guest";
			//$redirect_to = "/account/checkout_details";
			$redirect_to = "/account/checkout";

			$local_id = CommonUtility::getCookie(Yii::app()->params->local_id);
			$cart_uuid = CommonUtility::getCookie("cart_uuid_local");
			try {
				CCart::getContent($cart_uuid,Yii::app()->language);				
			} catch (Exception $e) {
				$redirect_to = !empty($local_id)?"/restaurants":"/";
			}

			$options = OptionsTools::find(array('signup_enabled_verification','signup_enabled_capcha'));			
			$signup_enabled_capcha = isset($options['signup_enabled_capcha'])?$options['signup_enabled_capcha']:false;
			$capcha = $signup_enabled_capcha==1?true:false;

			$enabled_verification = isset($options['signup_enabled_verification'])?$options['signup_enabled_verification']:false;
			$verification = $enabled_verification==1?true:false;

			$recaptcha_response = isset($this->data['recaptcha_response'])?$this->data['recaptcha_response']:'';			

			$firstname = isset($this->data['firstname'])?$this->data['firstname']:'';
			$lastname = isset($this->data['lastname'])?$this->data['lastname']:'';
			$email_address = isset($this->data['email_address'])?$this->data['email_address']:'';			
			$prefix = isset($this->data['mobile_prefix'])?$this->data['mobile_prefix']:'';
			$mobile_number = isset($this->data['mobile_number'])?$this->data['mobile_number']:'';
			$password = isset($this->data['password'])?$this->data['password']:'';
			$cpassword = isset($this->data['cpassword'])?$this->data['cpassword']:'';

			$redirect = isset($this->data['redirect'])?$this->data['redirect']:'';

			$model = new AR_clientsignup();
		    $model->scenario = "guest";
			$model->capcha = $capcha;
			$model->recaptcha_response = $recaptcha_response;
			
			if(!empty($password) || !empty($email_address)){
				$model2 = new AR_clientsignup();
				$model2->scenario = "guest_with_account";
				$model2->first_name = $firstname;
				$model2->last_name = $lastname;
				$model2->contact_phone = $prefix.$mobile_number;
				$model2->email_address = $email_address;
				$model2->guest_password = $password;
				$model2->cpassword = $cpassword;
				$model2->password = $password;
				$model2->social_strategy = "web";		
				$model2->merchant_id = 0;	
				$model2->capcha = $capcha;
			    $model2->recaptcha_response = $recaptcha_response;		

				$digit_code = CommonUtility::generateNumber(4,true);
				$model2->mobile_verification_code = $digit_code;
				
				if($verification==1 || $verification==true){
					$model2->status='pending';
				}
				if($model2->save()){
					if($verification==1 || $verification==true){
						$this->code = 1;
						$this->msg = t("Please wait until we redirect you");	
						$redirect = Yii::app()->createAbsoluteUrl("/account/verify",array(
							'uuid'=>$model2->client_uuid,
							'redirect'=>$redirect
						));			
						$this->details = array(
						    'redirect'=>$redirect
						);			
					} else {
						$login=new AR_customer_login;
						$login->username = $email_address;
						$login->password = $password;
						$login->rememberMe = 1;	
						if($login->validate() && $login->login() ){
							$this->code = 1;
							$this->msg = t("Registration successful");
							$this->details = [
								'redirect'=>Yii::app()->createAbsoluteUrl($redirect_to)
							];
						} else $this->msg = t("Login failed");
				    } 
				} else $this->msg = CommonUtility::parseError( $model2->getErrors() );				
			} else {
				$model->first_name = $firstname;
				$model->last_name = $lastname;
				$model->phone_prefix = $prefix;
				$model->contact_phone = $prefix.$mobile_number;
				
				$username = CommonUtility::uuid()."@gmail.com";				
				$password = CommonUtility::generateAplhaCode(20);				

				$model->social_strategy = $social_strategy;
				$model->password = $password;
				$model->email_address = $username;
				if($model->save()){
					$login=new AR_customer_login;
					$login->username = $model->email_address;
					$login->password = $password;
					$login->rememberMe = 1;						
					if($login->validate() && $login->login() ){
						$this->code = 1;
						$this->msg = t("Registration successful");
						$this->details = [
							'redirect'=>Yii::app()->createAbsoluteUrl($redirect_to)
						];
					} else $this->msg = t("Login failed");
				} else $this->msg = CommonUtility::parseError( $model->getErrors() );
			}

		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		    
		}	
		$this->responseJson();
	}
	
	public function actionaddressneeded()
	{
		try {

			$merchant_id = intval(Yii::app()->input->post('merchant_id'));
			$cart_uuid = trim(Yii::app()->input->post('cart_uuid'));						
			$local_id = CommonUtility::getCookie(Yii::app()->params->local_id);			

			$address_needed = CCheckout::addressIsNeeded($merchant_id,$local_id,$cart_uuid);
						
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [				
				'address_needed'=>$address_needed
			];
		} catch (Exception $e) {
		    $this->msg= t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actioncheckoutAddressNeeded()
	{
		try {
			$merchant_id = intval(Yii::app()->input->post('merchant_id'));
			$cart_uuid = trim(Yii::app()->input->post('cart_uuid'));
			$transaction = CCart::cartTransaction($cart_uuid,Yii::app()->params->local_transtype,$merchant_id);
			$address_needed = $transaction=="delivery"?true:false;
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [				
				'address_needed'=>$address_needed
			];
		} catch (Exception $e) {
		    $this->msg= t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actiongetmerchantbycart()
	{
		try {			
			$cart_uuid = Yii::app()->input->post('cart_uuid');					
			$merchant_id = CCart::getMerchantId($cart_uuid);
			$merchant = CMerchants::get($merchant_id);
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'merchant_uuid'=>$merchant->merchant_uuid
			];
		} catch (Exception $e) {
		    $this->msg= t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actionvalidateCartItems()
	{
		try {
			
			$cart_uuid = Yii::app()->input->post('cart_uuid');
			$item_id = Yii::app()->input->post('item_id');
			
			$result = CCart::validateFoodItems($item_id,$cart_uuid,Yii::app()->language);	
			$plural1 = "We regret to inform you that the {food_items} you had in your cart is no longer available.";		
			$plural2 = "We regret to inform you that the following item(s) {food_items} you had in your cart is no longer available.";		
			$message = Yii::t('front', "$plural1|$plural2",
			  array($result['count'], '{food_items}' => $result['item_line'])
		    );
			$this->code = 1;
			$this->msg = $message;
			$this->details = $result;

			// REMOVE ITEM FROM CART
			$model = AR_cart::model()->find("cart_uuid=:cart_uuid AND item_token=:item_token",[
				':cart_uuid'=>$cart_uuid,
				':item_token'=>$item_id
			]);
			if($model){
				$model->delete();
			}

		} catch (Exception $e) {
		    $this->msg= t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actionriderregistration()
	{
		try {			
			$model = new AR_driver;
			$model->myscenario = "register";
			$model->first_name = Yii::app()->input->post("first_name");
			$model->last_name = Yii::app()->input->post("last_name");
			$model->email = Yii::app()->input->post("email");
			$model->address = Yii::app()->input->post("address");
			$model->new_password = Yii::app()->input->post("password");
			$model->confirm_password = Yii::app()->input->post("cpassword");
			$model->phone_prefix = Yii::app()->input->post("mobile_prefix");
			$model->phone = Yii::app()->input->post("mobile_number");
			$model->status = 'pending';
			if($model->save()){
				$this->code = 1;
				$this->msg = t("Registration successful");
				$this->details = [					
					'redirect'=>Yii::app()->createAbsoluteUrl("/deliveryboy/verification",[
						'uuid'=>$model->driver_uuid
					])
				];
			} else $this->msg = CommonUtility::parseModelErrorToString( $model->getErrors() );
		} catch (Exception $e) {
		    $this->msg= t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actionresendriderverificationcode()
	{
		try {
			$driver_uuid = Yii::app()->input->post('uuid');			
			$model = AR_driver::model()->find('driver_uuid=:driver_uuid',array(':driver_uuid'=>$driver_uuid));
			if($model){
				$digit_code = CommonUtility::generateNumber(4,true);
				$model->verification_code = $digit_code;
			    $model->verify_code_requested = CommonUtility::dateNow();
				$model->scenario = 'send_otp';
				if($model->save()){
					$this->code = 1;
					$this->msg = t("We sent a code to {{email_address}}.",array(
						'{{email_address}}'=> CommonUtility::maskEmail($model->email)
					));
					if(DEMO_MODE){
						$this->details = [
							'otp'=>t("Your OTP is {{otp}}",['{{otp}}'=>$model->verification_code])
						];
					}
				} else $this->msg = CommonUtility::parseError($model->getErrors());
			} else $this->msg = t("Records not found");
		} catch (Exception $e) {
		    $this->msg= t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actionsearchmenu()
	{
		try {

			$q = Yii::app()->input->post('q');
			$merchant_id = Yii::app()->input->post('merchant_id');
			$currency_code = isset($this->data['currency_code'])?$this->data['currency_code']:'';
		    $base_currency = Price_Formatter::$number_format['currency_code'];		
			$exchange_rate = 1;
		
			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
			$multicurrency_enabled = $multicurrency_enabled==1?true:false;		

			// CHECK IF MERCHANT HAS DIFFERENT TIMEZONE
			$options_merchant = OptionsTools::find(['merchant_timezone','merchant_default_currency'],$merchant_id);
			$merchant_timezone = isset($options_merchant['merchant_timezone'])?$options_merchant['merchant_timezone']:'';		
			$merchant_default_currency = isset($options_merchant['merchant_default_currency'])?$options_merchant['merchant_default_currency']:'';
		    $merchant_default_currency = !empty($merchant_default_currency)?$merchant_default_currency:$base_currency;
			if(!empty($merchant_timezone)){
				Yii::app()->timezone = $merchant_timezone;
			}
			
			$items_not_available = CMerchantMenu::getItemAvailability($merchant_id,date("w"),date("H:h:i"));	
		    $category_not_available = CMerchantMenu::getCategoryAvailability($merchant_id,date("w"),date("H:h:i"));		 
			
			$currency_code = !empty($currency_code)?$currency_code: (empty($merchant_default_currency)?$base_currency:$merchant_default_currency);

			// SET CURRENCY
			if(!empty($currency_code) && $multicurrency_enabled){
				Price_Formatter::init($currency_code);
				if($currency_code!=$merchant_default_currency){
					$exchange_rate = CMulticurrency::getExchangeRate($merchant_default_currency,$currency_code);					
				}
			}

			CMerchantMenu::setExchangeRate($exchange_rate);

			$items = CMerchantMenu::getSimilarItems($merchant_id,Yii::app()->language,100,$q,$items_not_available,$category_not_available);			
			$promoItems = CMerchantMenu::getPromoItems($merchant_id);
			
			if(is_array($promoItems) && count($promoItems)>=1){
				foreach ($items as &$itemss) {			   			   			   
				    $itemss['promo_data'] = $promoItems[$itemss['item_id']] ?? [];
				}
		    }
						
			$this->code = 1; $this->msg = "ok";			
			$this->details = [				
				'data'=>$items,			
			];

		} catch (Exception $e) {
		    $this->msg= t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actiongetcurrencylist()
	{
		try {
			
			$currency_code = Yii::app()->input->post('currency_code');			
			$based_currency = AttributesTools::defaultCurrency();
			$data = CMulticurrency::currencyList();
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'based_currency'=> empty($currency_code) ? $based_currency : $currency_code,
				'data'=>$data
			];

		} catch (Exception $e) {
		    $this->msg= t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actiongetpointstransaction()
	{
		
		$data = array(); $card_id = 0;
		try {	
		    $card_id = CWallet::getCardID(Yii::app()->params->account_type['customer_points'],Yii::app()->user->id);				
		} catch (Exception $e) {
		    $this->msg = t("Invalid card id");
			$this->responseJson();
		}
		
		$page = Yii::app()->input->post('page');
		$page_raw = intval(Yii::app()->input->post('page'));
		$length = Yii::app()->params->list_limit;
		$show_next_page = false;
		
		if($page>0){
			$page = $page-1;
		}
		$criteria=new CDbCriteria();
		$criteria->addCondition('card_id=:card_id');
		$criteria->params = array(':card_id'=>intval($card_id));

		$criteria->order = "transaction_id DESC";
		$count = AR_wallet_transactions::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
		$pages->pageSize = intval($length);		
        $pages->setCurrentPage( intval($page) );                
        $pages->applyLimit($criteria);        	
		$page_count = $pages->getPageCount();	

		if($page>0){
			if($page_raw>$page_count){
				$this->code = 3;
				$this->msg  = t("end of results");                    
				$this->responseJson();
			}
		}
		
        $models = AR_wallet_transactions::model()->findAll($criteria);
		if($models){			
			foreach ($models as $item) {
				$description = Yii::app()->input->xssClean($item->transaction_description);        		
        		$parameters = json_decode($item->transaction_description_parameters,true);        		
        		if(is_array($parameters) && count($parameters)>=1){        			
        			$description = t($description,$parameters);
        		} else  $description = t($description);
				
				$transaction_amount = 0; $transaction_type = '';
				switch ($item->transaction_type) {					
        			case "points_redeemed":        			
        				   $transaction_amount = "-".Price_Formatter::convertToRaw($item->transaction_amount,0);
						   $transaction_type = 'debit';
        				break;      			
					default:
					       $transaction_amount = "+".Price_Formatter::convertToRaw($item->transaction_amount,0);		  		        			
						   $transaction_type = 'credit';
					    break;      			
        		} 

				$data[] = [
					'transaction_date'=>Date_Formatter::dateTime($item->transaction_date),
					'transaction_type'=>$transaction_type,
					'transaction_description'=>$description,
					'transaction_amount'=>$transaction_amount,
				];
			}
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'data'=>$data,
				'show_next_page'=>($page_raw+1)>$page_count?false:true,
				'page'=>$page_raw+1,                
                'page_count'=>$page_count,      
			];
		} else $this->msg = t("No results");
		$this->responseJson();
	}

	public function actiongetpointstransactionmerchant()
	{
		$data = array(); $card_id = 0;
		try {	
		    $card_id = CWallet::getCardID(Yii::app()->params->account_type['customer_points'],Yii::app()->user->id);				
		} catch (Exception $e) {
		    $this->msg = t("Invalid card id");
			$this->responseJson();
		}
		
		$page = intval(Yii::app()->input->post('page'));				
		$page_raw = intval(Yii::app()->input->post('page'));
		if($page>0){
			$page = $page-1;
		}
		$length = Yii::app()->params->list_limit;
		$show_next_page = false;
			
		$criteria = "
		SELECT
        a.reference_id1 as merchant_id, b.restaurant_name,
			SUM(CASE WHEN a.transaction_type = 'points_earned' THEN a.transaction_amount ELSE -transaction_amount END) AS total_earning
		FROM
			{{wallet_transactions}} a

		left JOIN (
		  SELECT merchant_id,restaurant_name FROM {{merchant}}
		) b 
		on a.reference_id1 = b.merchant_id

		WHERE a.card_id =".q($card_id)."

		GROUP BY a.reference_id1
		ORDER BY b.restaurant_name ASC
		";

		$count = AR_wallet_transactions::model()->countBySql($criteria);		
		$pages=new CPagination( intval($count) );
		$pages->pageSize = intval($length);		       
        $pages->setCurrentPage( intval($page) ); 				
        $models = AR_wallet_transactions::model()->findAllBySql($criteria);		
		$page_count = $pages->getPageCount();			

		if($page>0){
			if($page_raw>$page_count){
				$this->code = 3;
				$this->msg  = t("end of results");                    
				$this->responseJson();
			}
		}

		if($models){			
			foreach ($models as $item) {
				$merchant_ids[]=$item->merchant_id;
				$total = $item->total_earning;
				if($item->merchant_id<=0){
					$total = $total<=0? (-1*$total) :$total;
				}
				$data[] = [					
					'merchant_id'=>$item->merchant_id,
					'restaurant_name'=>!empty($item->restaurant_name)?$item->restaurant_name:t("Global points"),
					'total_earning'=>Price_Formatter::convertToRaw($total,0),					
				];
			}
			
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'data'=>$data,				
				'show_next_page'=>($page_raw+1)>$page_count?false:true,
				'page'=>$page_raw+1,  
				'page_count'=>$page_count,        			
			];
		}
		$this->responseJson();
	}

	public function actiongetavailablepoints()
	{
		try {
			$total = CPoints::getAvailableBalance(Yii::app()->user->id);
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'total'=>$total,				
			];
		} catch (Exception $e) {
		    $this->msg= t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actiongetcartpoints()
	{		
		try {		

			$cart_uuid = trim(Yii::app()->input->post('cart_uuid'));
			$currency_code = trim(Yii::app()->input->post('currency_code'));
			$base_currency = Price_Formatter::$number_format['currency_code'];
			$exchange_rate = 1; $exchange_rate_to_merchant = 1;

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
            $multicurrency_enabled = $multicurrency_enabled==1?true:false;		
			$merchant_id = CCart::getMerchantId($cart_uuid);
			$options_merchant = OptionsTools::find(['merchant_default_currency'],$merchant_id);						
		    $merchant_default_currency = isset($options_merchant['merchant_default_currency'])?$options_merchant['merchant_default_currency']:'';
			$merchant_default_currency = !empty($merchant_default_currency)?$merchant_default_currency:$base_currency;
			$currency_code = !empty($currency_code)?$currency_code: (empty($merchant_default_currency)?$base_currency:$merchant_default_currency) ;		
						
			if($multicurrency_enabled){
				Price_Formatter::init($currency_code);
				$exchange_rate = CMulticurrency::getExchangeRate($base_currency,$currency_code);	
				$exchange_rate_to_merchant = CMulticurrency::getExchangeRate($merchant_default_currency,$currency_code);								
		    }

			$redemption_policy = isset(Yii::app()->params['settings']['points_redemption_policy'])?Yii::app()->params['settings']['points_redemption_policy']:'universal';			

			$total = CPoints::getAvailableBalancePolicy(Yii::app()->user->id,$redemption_policy,$merchant_id);

			$attrs = OptionsTools::find(['points_redeemed_points','points_redeemed_value','points_maximum_redeemed']);			
			$points_maximum_redeemed = isset($attrs['points_maximum_redeemed'])? floatval($attrs['points_maximum_redeemed']) :0;			

			$points_redeemed_points = isset($attrs['points_redeemed_points'])? floatval($attrs['points_redeemed_points']) :0;			
			
			$amount = $points_redeemed_points * (1/$points_redeemed_points);			
			$amount = ($amount*$exchange_rate);
									
			$redeem_discount = t("Get {amount} off for every {points} points",[
				'{amount}'=>Price_Formatter::formatNumber( ($amount) ),
				'{points}'=>$points_redeemed_points
			]);												

			$redeem_label = '';
			if($total>0){
				$redeem_label = t("Your available balance is {points} points.",[
					'{points}'=>$total
				]);
				if($points_maximum_redeemed>0 && $total>$points_maximum_redeemed){
					$redeem_label = t("Redeem {max} out of {points} points",[
						'{max}'=>$points_maximum_redeemed,
						'{points}'=>$total
					]);
				}
			}			

			$discount = 0; $points = 0;
			if($model = CCart::getAttributes($cart_uuid,'point_discount')){
				$discount_raw = !empty($model->meta_id)?json_decode($model->meta_id,true):false;				
				$discount = floatval($discount_raw['value'])*$exchange_rate_to_merchant;
				$points = floatval($discount_raw['points']);

				CCart::getContent($cart_uuid,Yii::app()->language);	
				$subtotal = CCart::getSubTotal();
				$sub_total = floatval($subtotal['sub_total']) * $exchange_rate_to_merchant;
				$total_after_discount = floatval($sub_total) - floatval( CCart::cleanNumber($discount) );				
				if($total_after_discount<=0){
					CCart::deleteAttributes($cart_uuid,'point_discount');
					$discount = 0; $points = 0;
				}
			}

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'total'=>$total,
				'redeem_discount'=>$redeem_discount,				
				'redeem_label'=>$redeem_label,
				'discount'=>(-1*$discount),
				'discount_label'=>t("Discount Applied: {amount} off using {points} points.",
				  [
					'{amount}'=>Price_Formatter::formatNumber($discount),
					'{points}'=>$points
				]),
				'redeemed_points'=>$points_redeemed_points
			];
		} catch (Exception $e) {
		    $this->msg= t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actionapplyPoints()
	{
		try {

			$cart_uuid = trim(Yii::app()->input->post('cart_uuid'));
			$currency_code = trim(Yii::app()->input->post('currency_code'));	
			$base_currency = Price_Formatter::$number_format['currency_code'];		
			$points = floatval(Yii::app()->input->post('points'));
			$points_id = intval(Yii::app()->input->post('points_id')); 
			$merchant_id = 0;

			$options = OptionsTools::find(['points_minimum_subtotal']);			
			$minimum_amount = isset($options['points_minimum_subtotal'])?floatval($options['points_minimum_subtotal']):0;
			
			try {
				$merchant_id = CCart::getMerchantId($cart_uuid);
			} catch (Exception $e) {}						
						
			$redemption_policy = isset(Yii::app()->params['settings']['points_redemption_policy'])?Yii::app()->params['settings']['points_redemption_policy']:'universal';			
			$balance = CPoints::getAvailableBalancePolicy(Yii::app()->user->id,$redemption_policy,$merchant_id);			
			
			if($points>$balance){
				$this->msg = t("Insufficient balance");
				$this->responseJson();		
			}
			
			$attrs = OptionsTools::find(['points_redeemed_points','points_redeemed_value','points_maximum_redeemed','points_minimum_redeemed']);			
			$points_maximum_redeemed = isset($attrs['points_maximum_redeemed'])? floatval($attrs['points_maximum_redeemed']) :0;
			$points_minimum_redeemed = isset($attrs['points_minimum_redeemed'])? floatval($attrs['points_minimum_redeemed']) :0;			
			$points_redeemed_points = isset($attrs['points_redeemed_points'])? floatval($attrs['points_redeemed_points']) :0;
			
			if($points_maximum_redeemed>0 && $points>$points_maximum_redeemed){
				$this->msg = t("Maximum points for redemption: {points} points.",[
					'{points}'=>$points_maximum_redeemed
				]);
				$this->responseJson();				
			} 
			if($points_minimum_redeemed>0 && $points<$points_minimum_redeemed){
				$this->msg = t("Minimum points for redemption: {points} points.",[
					'{points}'=>$points_minimum_redeemed
				]);
				$this->responseJson();				
			} 

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
            $multicurrency_enabled = $multicurrency_enabled==1?true:false;		

			$merchant_id = CCart::getMerchantId($cart_uuid);
		 	$options_merchant = OptionsTools::find(['merchant_default_currency'],$merchant_id);						
		    $merchant_default_currency = isset($options_merchant['merchant_default_currency'])?$options_merchant['merchant_default_currency']:'';
			$merchant_default_currency = !empty($merchant_default_currency)?$merchant_default_currency:$base_currency;
			
			$currency_code = !empty($currency_code)?$currency_code: (empty($merchant_default_currency)?$base_currency:$merchant_default_currency) ;	

			$exchange_rate = 1; $exchange_rate_to_merchant = 1; $admin_exchange_rate=1;
			if(!empty($currency_code) && $multicurrency_enabled){
				$exchange_rate = CMulticurrency::getExchangeRate($base_currency,$merchant_default_currency);				
				$exchange_rate_to_merchant = CMulticurrency::getExchangeRate($merchant_default_currency,$currency_code);												
			}
															
			$discount = $points * (1/$points_redeemed_points);

			if($points_id>0){
				if($points_data = CPoints::getThresholdData($points_id)){               					
                    $points = $points_data['points'];
                    $discount = $points_data['value'];
                } 
			}			

			$discount = $discount *$exchange_rate;
			$discount2 = $discount *$exchange_rate_to_merchant;

			CCart::setExchangeRate($exchange_rate_to_merchant);
			CCart::getContent($cart_uuid,Yii::app()->language);	
			$subtotal = CCart::getSubTotal();
			$sub_total = floatval($subtotal['sub_total']);
			$total = floatval($sub_total) - floatval($discount2);			
			if($total<=0){
				$this->msg = t("Discount cannot be applied due to total less than zero after discount");				
				$this->responseJson();				
			}		
			
			// CHECK IF MINIMUM AMOUN IS SATISFY
			if($minimum_amount>0){
				$admin_base_currency = AttributesTools::defaultCurrency();
				$exchange_rate_admin_to_merchant =1;
				if($admin_base_currency!=$currency_code && $multicurrency_enabled){			
					$exchange_rate_admin_to_merchant = CMulticurrency::getExchangeRate($admin_base_currency,$currency_code);
				}																		
				$minimum_amount = $minimum_amount*$exchange_rate_admin_to_merchant;	
				if($sub_total<$minimum_amount){
					$this->msg = t("A minimum subtotal of {minimum_amount} is required to apply points.",[
						'{minimum_amount}'=>Price_Formatter::formatNumber($minimum_amount)
					]);
					$this->jsonResponse();
				}
			}			
			
			$params = [
				'name'=>"Less Points",
				'type'=>"points_discount",
				'target'=>"subtotal",
				'value'=>-$discount,
				'points'=>$points
			];			
			
			CCart::savedAttributes($cart_uuid,'point_discount', json_encode($params));
			$this->code = 1;
			$this->msg = "Ok";
			
		} catch (Exception $e) {
		    $this->msg= t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actionremovePoints()
	{
		try {

			$cart_uuid = trim(Yii::app()->input->post('cart_uuid'));
			CCart::deleteAttributesAll($cart_uuid,['point_discount']);
			$this->code = 1;
			$this->msg = "ok";

		} catch (Exception $e) {
		    $this->msg= t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actiongetAllergenInfo()
	{
		try {
			
			$item_id = Yii::app()->input->post("item_id");
			$merchant_id = Yii::app()->input->post("merchant_id");
			
			$allergen = CMerchantMenu::getAllergens($merchant_id, $item_id );	
			$allergen_data = AttributesTools::adminMetaList('allergens',Yii::app()->language,true);			

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'allergen'=>$allergen,
				'allergen_data'=>$allergen_data,
			];

		} catch (Exception $e) {
		    $this->msg= t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actionsearchsuggestion()
	{
		$q = Yii::app()->input->post("q");
		$select = [
			'type'=>"items",
			'name'=>$q,
		];
		try {						
			$data = CMerchantListingV1::searchSuggestionFoodRestaurants($q);			
			array_unshift($data,$select);			
			$this->code = 1; $this->msg = "Ok";
			$this->details = $data;
		} catch (Exception $e) {
			$this->code = 1; $this->msg = "Ok";						
			$this->details[] = $select;
		}	
		$this->responseJson();
	}

	public function actionsearchfood()
	{
		try {

			$q = Yii::app()->input->post("q");			
			$search = CommonUtility::safeExplode(" ",$q);			
			$currency_code = Yii::app()->input->post('currency_code');
			$base_currency = Price_Formatter::$number_format['currency_code'];		

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		    $multicurrency_enabled = $multicurrency_enabled==1?true:false;			

			$items = []; $merchant_list = []; $total = 0; $items_not_available =[];

			$currency_code = !empty($currency_code)? $currency_code : $base_currency; $category_not_available = [];

			try {				
				$data = CMerchantMenu::searchItems($search,Yii::app()->language,100,$currency_code,$multicurrency_enabled);					
				$total = $data['total'];
				$items = $data['data'];
				$merchant_ids = $data['merchant_ids'];				
				$items_not_available = CMerchantMenu::getItemAvailabilityByIDs($merchant_ids,date("w"),date("H:h:i"));	
				$category_not_available = CMerchantMenu::getCategoryAvailabilityByIDs($merchant_ids,date("w"),date("H:h:i"));		   
			} catch (Exception $e) {											
			}			
						
			$this->code = 1;
			$this->msg = "OK";
			$this->details = [			   
			   'food_list'=>$items,
			   'merchant_list'=>$merchant_list,			 
			   'total'=>$total,
			   'items_not_available'=>$items_not_available,
			   'category_not_available'=>$category_not_available
			];

		} catch (Exception $e) {
		    $this->msg= t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actionsearchRestaurant()
	{
		try {
						
			$page = 0;
			$todays_date = date("Y-m-d H:i");						
			
			$payload = [
				'cuisine','reviews','services'
			];

			$q = Yii::app()->input->post("q");
			$place_id = CommonUtility::getCookie(Yii::app()->params->local_id);			

			$place_data = CMaps::locationDetails($place_id,'');

			$filters = [
				'lat'=>isset($place_data['latitude'])?$place_data['latitude']:'',
				'lng'=>isset($place_data['longitude'])?$place_data['longitude']:'',
				'limit'=>100,
				'unit'=>Yii::app()->params['settings']['home_search_unit_type'],
				'today_now'=>strtolower(date("l",strtotime($todays_date))),
				'time_now'=>date("H:i",strtotime($todays_date)),
				'date_now'=>$todays_date,
				'page'=>intval($page),
				'client_id'=>!Yii::app()->user->isGuest?Yii::app()->user->id:0,
			];

			$and = '';

			$filters['having'] = "distance < a.delivery_distance_covered";		
			$filters['condition'] = "a.status=:status  AND a.is_ready = :is_ready $and";
			$filters['params'] = [
				':status'=>'active',
				':is_ready'=>2
			];			
			$filters['search'] = "a.restaurant_name";
			$filters['search_params'] = $q;

			$merchant_data = []; $cuisine = []; $total = 0;

			try {
				
				$data = CMerchantListingV1::getFeed($filters);					
				$merchant_data = $data['data'];
				$total = $data['count'];

				if(in_array('cuisine',$payload)){
					try {
						$cuisine = CMerchantListingV1::getCuisine( $data['merchant'] , Yii::app()->language );			
					} catch (Exception $e) {
						$cuisine = [];
					}
				}

			} catch (Exception $e) {							
				$merchant_data = [];
			}			

			$this->code = 1; $this->msg = "Ok";
			$this->details = [
				'merchant_data'=>$merchant_data,
			    'cuisine'=>$cuisine,
				'total'=>$total
			];

		} catch (Exception $e) {
		    $this->msg= t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actiongetFoodList()
	{
		try {

			$meta_name= Yii::app()->input->post("meta_name");						
			$currency_code = Yii::app()->input->post('currency_code');
			$base_currency = Price_Formatter::$number_format['currency_code'];

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		    $multicurrency_enabled = $multicurrency_enabled==1?true:false;			

			$items = []; $merchant_list = []; $total = 0; $items_not_available =[];

			$currency_code = !empty($currency_code)? $currency_code : $base_currency; $category_not_available = [];

			try {				
				$data = CMerchantMenu::searchItems(['meta_name'=>$meta_name],Yii::app()->language,100,$currency_code,$multicurrency_enabled,'meta_name');					
				$total = $data['total'];
				$items = $data['data'];
				$merchant_ids = $data['merchant_ids'];				
				$items_not_available = CMerchantMenu::getItemAvailabilityByIDs($merchant_ids,date("w"),date("H:h:i"));	
				$category_not_available = CMerchantMenu::getCategoryAvailabilityByIDs($merchant_ids,date("w"),date("H:h:i"));		   
			} catch (Exception $e) {											
			}			
			
			$featured = AttributesTools::ItemFeatured();			
			
			$this->code = 1;
			$this->msg = "OK";
			$this->details = [			
			   'title'=> isset($featured[$meta_name])?$featured[$meta_name]:'' ,
			   'food_list'=>$items,
			   'merchant_list'=>$merchant_list,			 
			   'total'=>$total,
			   'items_not_available'=>$items_not_available,
			   'category_not_available'=>$category_not_available
			];

		} catch (Exception $e) {
		    $this->msg= t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actiongetTableAttributes()
	{
		try {

			$cart_uuid = Yii::app()->input->post('cart_uuid');
			$merchant_id = CCart::getMerchantId($cart_uuid);
			$atts = OptionsTools::find(['booking_enabled'],$merchant_id);
			$booking_enabled = isset($atts['booking_enabled'])?$atts['booking_enabled']:false;
			$booking_enabled = $booking_enabled==1?true:false;
			
			$room_list = []; $default_room_uuid = '';
 		    $room_list = CommonUtility::getDataToDropDown("{{table_room}}","room_uuid","room_name","WHERE merchant_id=".q($merchant_id)." ","order by room_name asc");                						
		    if(is_array($room_list) && count($room_list)>=1){
			   $default_room_uuid = array_key_first($room_list);
			   $room_list = CommonUtility::ArrayToLabelValue($room_list);   
		    }	

			$table_list = [];
			try{
			   $table_list = CBooking::getTableList($merchant_id);		
			} catch (Exception $e) {
			}

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'room_list'=>$room_list,
				'table_list'=>$table_list,
				'booking_enabled'=>$booking_enabled,
				'default_room_uuid'=>$default_room_uuid
			];
		} catch (Exception $e) {
		    $this->msg= t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actiongetpromodetails()
	{
		try {

			$merchant_id = intval(Yii::app()->input->post('merchant_id'));
			$currency_code = Yii::app()->input->post('currency_code');			

			$currency_code = Yii::app()->input->post('currency_code');	
			$base_currency = Price_Formatter::$number_format['currency_code'];

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		    $multicurrency_enabled = $multicurrency_enabled==1?true:false;		
		    $exchange_rate = 1;

			$options_merchant = OptionsTools::find(['merchant_default_currency'],$merchant_id);							
			$merchant_default_currency = isset($options_merchant['merchant_default_currency'])?$options_merchant['merchant_default_currency']:'';
			$merchant_default_currency = !empty($merchant_default_currency)?$merchant_default_currency:$base_currency;
			$currency_code = !empty($currency_code)?$currency_code: (empty($merchant_default_currency)?$base_currency:$merchant_default_currency) ;

			// SET CURRENCY
			if(!empty($currency_code) && $multicurrency_enabled){
				Price_Formatter::init($currency_code);
				if($currency_code!=$merchant_default_currency){
					$exchange_rate = CMulticurrency::getExchangeRate($merchant_default_currency,$currency_code);					
				}
		    }

			CPromos::setExchangeRate($exchange_rate);
			$data = CPromos::getAvaialblePromoNew([$merchant_id],CommonUtility::dateOnly(),false,true);
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = $data;					
		} catch (Exception $e) {
		    $this->msg= t($e->getMessage());				
		}	
		$this->responseJson();
	}

	public function actiongetCartWallet()
	{
		try {

			$cart_uuid = trim(Yii::app()->input->post('cart_uuid'));
			$currency_code = trim(Yii::app()->input->post('currency_code'));
			$base_currency = Price_Formatter::$number_format['currency_code'];
			$exchange_rate = 1; $exchange_rate_to_merchant = 1;

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
            $multicurrency_enabled = $multicurrency_enabled==1?true:false;		
												
			if(!empty($currency_code) && $multicurrency_enabled){
				Price_Formatter::init($currency_code);
				if($currency_code!=$base_currency){
					$exchange_rate = CMulticurrency::getExchangeRate($base_currency,$currency_code);					
				}
			}

			$atts = CCart::getAttributesAll($cart_uuid,['use_wallet']);
			$use_wallet = isset($atts['use_wallet'])?$atts['use_wallet']:false;
			$use_wallet = $use_wallet==1?true:false;

			$balance_raw = CDigitalWallet::getAvailableBalance(Yii::app()->user->id);
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'balance_raw'=>($balance_raw*$exchange_rate),
				'balance'=>Price_Formatter::formatNumber(($balance_raw*$exchange_rate)),
				'use_wallet'=>$balance_raw>0?$use_wallet:false
			];

		} catch (Exception $e) {
		    $this->msg= t($e->getMessage());				
		}	
		$this->responseJson();
	}

	public function actionapplyDigitalWallet()
	{
		try {

			$cart_uuid = trim(Yii::app()->input->post('cart_uuid'));
			$use_wallet = intval(Yii::app()->input->post('use_wallet'));
			$use_wallet = $use_wallet==1?true:false;
			$amount_to_pay = floatval(Yii::app()->input->post('amount_to_pay'));
			$currency_code = trim(Yii::app()->input->post('currency_code'));
			$base_currency = Price_Formatter::$number_format['currency_code'];
			$exchange_rate = 1; $exchange_rate_to_merchant = 1;

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
            $multicurrency_enabled = $multicurrency_enabled==1?true:false;		

			$transaction_limit = isset(Yii::app()->params['settings']['digitalwallet_transaction_limit'])? floatval(Yii::app()->params['settings']['digitalwallet_transaction_limit']) :0;			
									
			if(!empty($currency_code) && $multicurrency_enabled){
				Price_Formatter::init($currency_code);
				if($currency_code!=$base_currency){
					$exchange_rate = CMulticurrency::getExchangeRate($base_currency,$currency_code);					
				}
			}

			$balance_raw = CDigitalWallet::getAvailableBalance(Yii::app()->user->id);
			$balance_raw = ($balance_raw*$exchange_rate);
			$amount_due = CDigitalWallet::calculateAmountDue($amount_to_pay,$balance_raw);
			
			if($transaction_limit>0 && $use_wallet){
				if($balance_raw>$transaction_limit){
					$this->msg = t("Transaction amount exceeds wallet spending limit.");
					$this->responseJson();
				}
			}
			
			$message = t("Looks like this order is higher than your digital wallet credit.");
			$message.="\n";
			$message.= t("Please select a payment method below to cover the remaining amount.");

			CCart::savedAttributes($cart_uuid,'use_wallet',$use_wallet);
			
			$this->code = 1;
			$this->msg = $amount_due>0? $message : '';
			$this->details = [
				'use_wallet'=>$use_wallet,
				'balance_raw'=>$balance_raw,
				'amount_to_pay'=>$amount_to_pay,
				'amount_due_raw'=>$amount_due,
				'amount_due'=>Price_Formatter::formatNumber($amount_due),
				'pay_remaining'=>t("Pay remaining {amount}",[
					'{amount}'=>Price_Formatter::formatNumber($amount_due)
				])
			];
		} catch (Exception $e) {
		    $this->msg= t($e->getMessage());				
		}	
		$this->responseJson();
	}

	public function actiongetwalletbalance()
	{
		try {	
			$total = CDigitalWallet::getAvailableBalance(Yii::app()->user->id);			
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'total'=>Price_Formatter::formatNumber($total),	
			];
		} catch (Exception $e) {
		    $this->msg= t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actiongetWalletTransaction()
	{
		$data = array(); $card_id = 0;
		try {	
		    $card_id = CWallet::getCardID(Yii::app()->params->account_type['digital_wallet'],Yii::app()->user->id);				
		} catch (Exception $e) {
		    $this->msg = t("Invalid card id");
			$this->responseJson();
		}

		$page = Yii::app()->input->post('page');
		$page_raw = intval(Yii::app()->input->post('page'));
		$transaction_type = Yii::app()->input->post('transaction_type');		

		$length = Yii::app()->params->list_limit;
		$show_next_page = false;
		
		if($page>0){
			$page = $page-1;
		}
		$criteria=new CDbCriteria();
		$criteria->alias = "a";

		if($transaction_type=="all"){
			$criteria->addCondition("a.card_id=:card_id");			
			$criteria->params = array(
				':card_id'=>intval($card_id)				
			);		
		} else {
			$criteria->addCondition("a.card_id=:card_id AND b.meta_name=:meta_name");
			$criteria->join="LEFT JOIN {{wallet_transactions_meta}} b on  a.transaction_id = b.transaction_id";
			$criteria->params = array(
				':card_id'=>intval($card_id),
				':meta_name'=>$transaction_type
			);		
		}		

		$criteria->order = "a.transaction_id DESC";		
		$count = AR_wallet_transactions::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
		$pages->pageSize = intval($length);		
        $pages->setCurrentPage( intval($page) );                
        $pages->applyLimit($criteria);        	
		$page_count = $pages->getPageCount();	

		if($page>0){
			if($page_raw>$page_count){
				$this->code = 3;
				$this->msg  = t("end of results");                    
				$this->responseJson();
			}
		}
				
        $models = AR_wallet_transactions::model()->findAll($criteria);
		if($models){			
			foreach ($models as $item) {				
				$description = Yii::app()->input->xssClean($item->transaction_description);        		
        		$parameters = json_decode($item->transaction_description_parameters,true);        		
        		if(is_array($parameters) && count($parameters)>=1){        			
        			$description = t($description,$parameters);
        		} else  $description = t($description);
				
				$transaction_amount = 0; $transaction_type = '';
				switch ($item->transaction_type) {					
        			case "debit":        			
        				   $transaction_amount = "-".Price_Formatter::formatNumber($item->transaction_amount);
						   $transaction_type = 'debit';
        				break;      			
					default:
					       $transaction_amount = "+".Price_Formatter::formatNumber($item->transaction_amount);			
						   $transaction_type = 'credit';
					    break;      			
        		} 

				$data[] = [
					'transaction_date'=>Date_Formatter::dateTime($item->transaction_date),
					'transaction_type'=>$transaction_type,
					'transaction_description'=>$description,
					'transaction_amount'=>$transaction_amount,
				];
			}			
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'data'=>$data,
				'show_next_page'=>($page_raw+1)>$page_count?false:true,
				'page'=>$page_raw+1,                
                'page_count'=>$page_count,      
			];
		} else $this->msg = t("No results");
		$this->responseJson();
		
	}

	public function actiongetCustomerDefaultPayment()
	{
		try {
			
			$data = [];			
			$data = CPayments::defaultPaymentOnline(Yii::app()->user->id);			
						
			$this->code = 1;
		    $this->msg = "ok";
		    $this->details = array(
		      'data'=>$data,
		    );		    				
		} catch (Exception $e) {
		    $this->msg= t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actionprepareaddfunds()
	{
		try {
			
			$topup_minimum = isset(Yii::app()->params['settings']['digitalwallet_topup_minimum'])?floatval(Yii::app()->params['settings']['digitalwallet_topup_minimum']):1;			
			$topup_maximum = isset(Yii::app()->params['settings']['digitalwallet_topup_maximum'])?floatval(Yii::app()->params['settings']['digitalwallet_topup_maximum']):10000;			

			$merchant_id = 0; $merchant_type = 2; $payment_details = [];
			$payment_description = '';

			$amount = floatval(Yii::app()->input->post('amount'));
			$is_checkout =  Yii::app()->request->getPost('is_checkout', null);            			
			
			if($amount<$topup_minimum){
				$this->msg = t("Top-up amount should meet the minimum requirement of {{topup_minimum}} for a successful transaction. The maximum allowed is {{topup_maximum}}.",[					
					'{{topup_minimum}}'=>Price_Formatter::formatNumber($topup_minimum),
					'{{topup_maximum}}'=>Price_Formatter::formatNumber($topup_maximum)
				]);
				$this->responseJson();
			}
			if($amount>$topup_maximum){
				$this->msg = t("Top-up amount exceeds the maximum limit of {{topup_maximum}} for a single transaction. The minimum required is {{topup_minimum}}.",[					
					'{{topup_minimum}}'=>Price_Formatter::formatNumber($topup_minimum),
					'{{topup_maximum}}'=>Price_Formatter::formatNumber($topup_maximum)
				]);
				$this->responseJson();
			}

			$original_amount = $amount;
			$transaction_amount = $amount;
			$payment_code = Yii::app()->input->post('payment_code');
			$payment_uuid = Yii::app()->input->post('payment_uuid');			
			$currency_code = Yii::app()->input->post('currency_code');			
			$base_currency = Price_Formatter::$number_format['currency_code'];
			$currency_code = $base_currency;

			$exchange_rate = 1;
			$merchant_base_currency = $base_currency;
			$admin_base_currency = $base_currency;
			$exchange_rate_merchant_to_admin = $exchange_rate;
			$exchange_rate_admin_to_merchant = $exchange_rate;
			
			$payment_model = CPayments::getPaymentByCode($payment_code);		
			$payment_name = $payment_model->payment_name;

			$payment_description_raw = "Funds Added via {payment_name}";
			$transaction_description_parameters = [
				'{payment_name}'=>$payment_name
			];
			$payment_description = t($payment_description_raw,$transaction_description_parameters);

			if($payment_code=="stripe"){
				$payment_details = CPayments::getPaymentMethodMeta($payment_uuid, Yii::app()->user->id );				
			} elseif ( $payment_code=="paypal"){
			}

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
			$multicurrency_enabled = $multicurrency_enabled==1?true:false;		   	
			$enabled_checkout_currency = isset(Yii::app()->params['settings']['multicurrency_enabled_checkout_currency'])?Yii::app()->params['settings']['multicurrency_enabled_checkout_currency']:false;
			$enabled_force = $multicurrency_enabled==true? ($enabled_checkout_currency==1?true:false) :false;
		
			if($enabled_force && $multicurrency_enabled){
				if($force_result = CMulticurrency::getForceCheckoutCurrency($payment_code,$currency_code)){					
					$currency_code = $force_result['to_currency'];
					$amount = Price_Formatter::convertToRaw($amount*$force_result['exchange_rate'],2);
				}
			}

			$customer = ACustomer::get(Yii::app()->user->id);
			$customer_details = [
				'client_id'=>$customer->client_id,
				'first_name'=>$customer->first_name,
				'last_name'=>$customer->last_name,
				'email_address'=>$customer->email_address,
				'phone_prefix'=>$customer->phone_prefix,
				"contact_phone" => substr($customer->contact_phone,strlen($customer->phone_prefix))
			];			
			
			$payment_instructions = Yii::app()->getModule($payment_code)->paymentInstructions();			

			$redirect = Yii::app()->createAbsoluteUrl("account/wallet");
			if($is_checkout){
				$redirect = Yii::app()->createAbsoluteUrl("account/checkout");
			}			
			
			$data = [
				'payment_code'=>$payment_code,
				'payment_uuid'=>$payment_uuid,
				'payment_name'=>$payment_name,
				'merchant_id'=>$merchant_id,
				'merchant_type'=>$merchant_type,
				'payment_description'=>$payment_description,
				'payment_description_raw'=>$payment_description_raw,
				'transaction_description_parameters'=>$transaction_description_parameters,
				'amount'=>$amount,
				'currency_code'=>$currency_code,
				'transaction_amount'=>$transaction_amount,
				'orig_transaction_amount'=>$original_amount,
				'merchant_base_currency'=>$currency_code,
				'merchant_base_currency'=>$merchant_base_currency,
				'admin_base_currency'=>$admin_base_currency,
				'exchange_rate_merchant_to_admin'=>$exchange_rate_merchant_to_admin,
				'exchange_rate_admin_to_merchant'=>$exchange_rate_admin_to_merchant,
				'payment_details'=>$payment_details,
				'customer_details'=>$customer_details,
				'payment_type'=>"add_funds",
				'reference_id'=>CommonUtility::createUUID("{{wallet_transactions}}",'transaction_uuid'),
				'successful_url'=>$redirect,
				'failed_url'=>$redirect,
				'cancel_url'=>$redirect,
				'is_checkout'=>$is_checkout
			];							
			$details = JWT::encode($data , CRON_KEY, 'HS256');  

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'payment_code'=>$payment_code,
				'data'=>$details,
				'amount'=>$amount,
				'currency_code'=>$currency_code,
				'transaction_amount'=>$transaction_amount,		
				'payment_uuid'=>$payment_uuid,
				'payment_instructions'=>$payment_instructions
			];						
		} catch (Exception $e) {
		    $this->msg= t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actiongetDiscount()
	{
		try {

			$transaction_type = Yii::app()->input->post('transaction_type');			
			$data = AttributesTools::getDiscount($transaction_type,date("Y-m-d"));
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = $data;

		} catch (Exception $e) {
		    $this->msg= t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actiongetPointsthresholds()
	{
		try {
						
			$customer_id = Yii::app()->user->id;
			$cart_uuid = trim(Yii::app()->input->post('cart_uuid'));
			$currency_code = trim(Yii::app()->input->post('currency_code'));
			$base_currency = Price_Formatter::$number_format['currency_code'];
			$exchange_rate = 1; $exchange_rate_to_merchant = 1;

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
            $multicurrency_enabled = $multicurrency_enabled==1?true:false;		
			$merchant_id = CCart::getMerchantId($cart_uuid);
			$options_merchant = OptionsTools::find(['merchant_default_currency'],$merchant_id);						
		    $merchant_default_currency = isset($options_merchant['merchant_default_currency'])?$options_merchant['merchant_default_currency']:'';
			$merchant_default_currency = !empty($merchant_default_currency)?$merchant_default_currency:$base_currency;
			$currency_code = !empty($currency_code)?$currency_code: (empty($merchant_default_currency)?$base_currency:$merchant_default_currency) ;		
						
			if($multicurrency_enabled){
				Price_Formatter::init($currency_code);
				$exchange_rate = CMulticurrency::getExchangeRate($base_currency,$currency_code);	
				$exchange_rate_to_merchant = CMulticurrency::getExchangeRate($merchant_default_currency,$currency_code);								
		    }
			
			$data = CPoints::getThresholds($exchange_rate);

            $redemption_policy = isset(Yii::app()->params['settings']['points_redemption_policy'])?Yii::app()->params['settings']['points_redemption_policy']:'universal';
            $balance = CPoints::getAvailableBalancePolicy($customer_id,$redemption_policy,$merchant_id);			

            $this->code = 1;
            $this->msg = "Ok";
            $this->details = [
                'balance'=>$balance,
                'data'=>$data
            ];

		} catch (Exception $e) {
		    $this->msg= t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actionsubscribetoemail()
	{
		try {
			$email_address = Yii::app()->input->post('email_address');					
			$model = new AR_subscriber;
			$model->email_address  = $email_address;
			$model->subcsribe_type = 'website';
			$model->merchant_id = 0;			
			if($model->save()){
				$this->code = 1;
				$this->msg = t("Thank you for subscribing to our newsletter");
			} else {
				$this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
			}
		} catch (Exception $e) {
		    $this->msg= t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actiongetRecentAddress()
	{				
		if(!Yii::app()->user->isGuest){			
			if ( $data = CClientAddress::getAddresses(Yii::app()->user->id)){				
				$new_data = [];				
				foreach ($data as $items) {
					$new_data[] = [
						'id'=>$items['place_id'],
						'place_id'=>$items['place_id'],
						'provider'=>'',
						'addressLine1'=>$items['address']['address1'],
						'addressLine2'=>$items['address']['complete_delivery_address'],
						'latitude'=>$items['latitude'],
						'longitude'=>$items['longitude'],
						'place_type'=>'',
						'description'=>$items['address']['complete_delivery_address'],
						'from_addressbook'=>true
					];
				}
				$this->code = 1;
				$this->msg = "OK";
				$this->details = array(
				  'data'=>$new_data
				);			
			} else $this->msg[] = t("No results");
		} else $this->msg = "not login";
		$this->responseJson();
	}

	public function actionrequestemailotp()
	{
		try {

			$email_address = Yii::app()->input->post('email_address');
			$model = AR_client::model()->find("email_address=:email_address AND social_strategy!=:social_strategy",[
				':email_address'=>trim($email_address),
				':social_strategy'=>'single'
			]);
			if($model){				
				$digit_code = CommonUtility::generateNumber(4,true);
				$model->mobile_verification_code = $digit_code;
				$model->scenario="resend_otp";
				if($model->save()){		   
					// SEND EMAIL HERE         
					$this->code = 1;
					$this->msg = t("We sent a code to {{email_address}}.",array(
					   '{{email_address}}'=> CommonUtility::maskEmail($model->email_address)
					));			      					     
					if(DEMO_MODE==TRUE){
						$this->details['verification_code']=t("Your verification code is {{code}}",array('{{code}}'=>$digit_code));
					}
			    } else $this->msg = CommonUtility::parseError($model->getErrors());
			} else $this->msg = t("Sorry, we couldn't find an account associated with this email address");
		} catch (Exception $e) {
		    $this->msg= t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actionuserloginbyotp()
	{
		try {			
			$redirect = Yii::app()->input->post('redirect');
			$email_address = Yii::app()->input->post('email_address');
			$mobile_number = Yii::app()->input->post('mobile_number');
			$mobile_prefix = Yii::app()->input->post('mobile_prefix');
			$validation_type = Yii::app()->input->post('validation_type');			
			$otp = Yii::app()->input->post('otp');
			
			if($validation_type==1){
				$model = AR_client::model()->find("email_address=:email_address AND social_strategy!=:social_strategy AND mobile_verification_code=:mobile_verification_code",[
					':email_address'=>trim($email_address),
				    ':social_strategy'=>'single',
					':mobile_verification_code'=>$otp
				]);
			} else {				
				$model = AR_client::model()->find("contact_phone=:contact_phone AND social_strategy!=:social_strategy AND mobile_verification_code=:mobile_verification_code",[
					':contact_phone'=>trim($mobile_prefix.$mobile_number),
				    ':social_strategy'=>'single',
					':mobile_verification_code'=>intval($otp)
				]);
			}
			
			if($model){															
				$login=new AR_customer_autologin;
				$login->username = $model->email_address;
				$login->password = $model->password;
				$login->rememberMe = 1;
				if($login->validate() && $login->login() ){
					$this->code = 1 ;
					$this->msg = t("Login successful");
					$this->details = array(
					   'redirect'=>!empty($redirect)?$redirect:Yii::app()->getBaseUrl(true)
					);									
				} else $this->msg = CommonUtility::parseModelErrorToString( $model->getErrors() );
			} else $this->msg  = t("Incorrect OTP");
		} catch (Exception $e) {
		    $this->msg= t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actiongetjoin()
	{

		$enabled_registration = isset(Yii::app()->params['settings']['merchant_enabled_registration'])?Yii::app()->params['settings']['merchant_enabled_registration']:false;
		$enabled_driver_signup = isset(Yii::app()->params['settings']['driver_enabled_registration'])?Yii::app()->params['settings']['driver_enabled_registration']:false;
		
		if($enabled_registration){
			$data[] = [
				'title'=>t("Join Our Network of Restaurants"),
				'content'=>t("Discover the advantages of partnering with us and see how we can help your business grow."),
				'thumbnail'=>Yii::app()->theme->baseUrl."/assets/images/reg-restaurant.jpg",
				'button_caption'=>t("Signup now"),
				'url'=>Yii::app()->createAbsoluteUrl("/merchant/signup"),
			];
		}		
		$data[] = [
			'title'=>t("Order Your Favorite Dishes Online!"),
			'content'=>t("Enjoy a seamless food ordering experience with fast delivery from your favorite local restaurants."),
			'thumbnail'=>Yii::app()->theme->baseUrl."/assets/images/reg-customer.jpg",
			'button_caption'=>t("Signup now"),
			'url'=>Yii::app()->createAbsoluteUrl("/account/login"),
		];
		if($enabled_driver_signup){
			$data[] = [
				'title'=>t("Join as a delivery person!"),
				'content'=>t("Start earning extra income by delivering to homes. Enjoy top rates and great benefits as part of our team."),
				'thumbnail'=>Yii::app()->theme->baseUrl."/assets/images/reg-driver.jpg",
				'button_caption'=>t("Register now!"),
				'url'=>Yii::app()->createAbsoluteUrl("/deliveryboy/signup"),
			];
		}		
		$this->code = 1;
		$this->msg = "Ok";
		$this->details = [
			'data'=>$data
		];
		$this->responseJson();
	}

	public function actionupdateprofilephoto()
	{
		try {

			$client_id = Yii::app()->user->id;					

			$model = AR_client::model()->find('client_id=:client_id', 
		    array(':client_id'=> intval($client_id) )); 		

			if(!$model){
				$this->msg = t("Session has expired, please re-login again.");
				$this->jsonResponse();
			}

			$allowed_extension = CommonUtility::safeExplode(",",  Yii::app()->params['upload_type']);
		    $maxsize = (integer) Yii::app()->params['upload_size'] ;
			if (!empty($_FILES)) {
				$title = $_FILES['file']['name'];   
				$file_size = (integer)$_FILES['file']['size'];   
				$filetype = $_FILES['file']['type'];   
				
				if(isset($_FILES['file']['name'])){
					$extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
				 } else $extension = strtolower(substr($title,-3,3));
				 
				 if(!in_array($extension,$allowed_extension)){			
					 $this->msg = t("Invalid file extension");
					 $this->jsonResponse();
				 }
				 if($file_size>$maxsize){
					 $this->msg = t("Invalid file size");
					 $this->jsonResponse();
				 }
				 
				 $allowed_extension = CommonUtility::safeExplode(",",Helper_imageType);
				 $maxsize = (integer)Helper_maxSize;			
				 
				 if(isset($_FILES['file']['name'])){
					$extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
					$extension = strtolower($extension);
				 } else $extension = strtolower(substr($title,-3,3)); 	
					 
				 if(!in_array($extension,$allowed_extension)){			
					 $this->msg = t("Invalid file extension");
					 $this->jsonResponse();
				 }
				 if($file_size>$maxsize){
					 $this->msg = t("Invalid file size, allowed size are {{size}}",array(
					  '{{size}}'=>CommonUtility::HumanFilesize($maxsize)
					 ));
					 $this->jsonResponse();
				 }
				 
				$upload_path = CMedia::avatarFolder();
				$tempFile = $_FILES['file']['tmp_name'];
				$upload_uuid = CommonUtility::createUUID("{{media_files}}",'upload_uuid');
				$filename = $upload_uuid.".$extension";			
				$path = CommonUtility::uploadDestination($upload_path)."/".$filename;						
				$path2 = CommonUtility::uploadDestination($upload_path)."/";

				if(move_uploaded_file($tempFile,$path)){				
					
					$delete_old_image = CommonUtility::uploadDestination($model->path)."/".$model->avatar;
					if(file_exists($delete_old_image)){
						@unlink($delete_old_image);
					}

					$model->path = $upload_path;
					$model->avatar = $filename;
					$model->save();

					$url_image = CMedia::getImage($filename,$upload_path,'',CommonUtility::getPlaceholderPhoto('customer'));
					Yii::app()->user->avatar = $url_image;
					
					$this->code = 1; $this->msg = t("Profile photo updated");	
					$this->details = array(			   
						'url_image'=> $url_image,
						'filename'=>$filename,
						'id'=>$upload_uuid			   
					);		
			   } else $this->msg = t("Failed cannot upload file.");

			} else $this->msg = t("Invalid file");
			
		} catch (Exception $e) {
		    $this->msg= t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actiongetBanner()
	{
		try {
						
			$data = CMerchants::getBannerByRadius(0,'admin',[
				'latitude'=>Yii::app()->request->getParam('latitude'),
				'longitude'=>Yii::app()->request->getParam('longitude'),
			]);			
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'data'=>$data
			];
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actiontrackOrder()
	{
		try {
			$order_uuid = Yii::app()->input->get('order_uuid');
			$progress = CTrackingOrder::getProgress($order_uuid , date("Y-m-d g:i:s a") );			
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'data'=>$progress
			];
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actionCancelOrder()
	{
		try {

			if(Yii::app()->user->isGuest){
				$this->msg = t("User not login or session has expired");
				$this->responseJson();	
			}
			$order_uuid = Yii::app()->input->post('order_uuid');

			$cancel = AR_admin_meta::getValue('status_cancel_order');			
			$cancel_status = isset($cancel['meta_value'])?$cancel['meta_value']:'cancelled';
			$cancel2 = AR_admin_meta::getValue('status_delivery_cancelled');			
			$cancel_status2 = isset($cancel2['meta_value'])?$cancel2['meta_value']:'cancelled';
			$reason = "Customer cancel this order";
			
			$order = COrders::get($order_uuid);
			$resp = COrders::getCancelStatus($order_uuid);
			
			if($resp['payment_type']=="online"){
				$order->scenario = "cancel_order";
				if($order->status==$cancel_status){
					$this->msg = t("This order has already been cancelled");
					$this->responseJson();
				}					
				$order->status = $cancel_status;					
				$order->delivery_status  = $cancel_status2;
				$order->remarks = $reason;
				if($order->save()){
					$this->code = 1;
					$this->msg = t("Your order is now cancel. your refund is on its way.");			   
					if(!empty($reason)){
						COrders::savedMeta($order->order_id,'rejetion_reason',$reason);
					}			   
				} else $this->msg = CommonUtility::parseError( $order->getErrors());
			} else {
				$order->scenario = "cancel_order";
				if($order->status==$cancel_status){
					$this->msg = t("This order has already been cancelled");
					$this->responseJson();
				}					
				$order->status = $cancel_status;
				$order->delivery_status  = $cancel_status2;				
				$order->remarks = $reason;
				if($order->save()){
					$this->code = 1;
					$this->msg = t("Your order is now cancel.");			   
					if(!empty($reason)){
						COrders::savedMeta($order->order_id,'rejetion_reason',$reason);
					}			   
				} else $this->msg = CommonUtility::parseError( $order->getErrors());
			}

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actionreviewattributes()
	{
		try {

			$data = AOrders::rejectionList('delivery_like_options', Yii::app()->language );
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = $data;

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actionaddRiderReview()
	{
		try {

			$customer_id = Yii::app()->user->id;
			$rating = Yii::app()->input->post("rating");
			$review_text = Yii::app()->input->post("review_text");
			$order_id = Yii::app()->input->post("order_id");
			$driver_id = Yii::app()->input->post("driver_id");
			
			$model = AR_driver_reviews::model()->find("order_id=:order_id AND customer_id=:customer_id AND driver_id=:driver_id",[
				':order_id'=>$order_id,
				':customer_id'=>$customer_id,
				':driver_id'=>$driver_id,
			]);
			if($model){
				$this->msg = t("You already added review for this order");
				$this->responseJson();
			}
			$model = new AR_driver_reviews();
			$model->order_id = $order_id;
			$model->customer_id = $customer_id;
			$model->driver_id = $driver_id;
			$model->rating = $rating;
			$model->review_text = $review_text;
			if($model->save()){				
				$this->code = 1; $this->msg = t("Thank you for sharing your experience!");
			} else $this->msg = CommonUtility::parseError( $model->getErrors());
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actiongetdriverinfo()
	{
		try {

			$driver_uuid = Yii::app()->input->get('driver_uuid');
			$data = CDriver::getDriverByUUID($driver_uuid);
			$data = CDriver::getDriverInfo($data->driver_id);
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'data'=>$data
			];
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actioncuisinelist2()
	{
		try {
			$cuisine = CCuisine::getList( Yii::app()->language );
			$this->code = 1;
			$this->details = $cuisine;
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actionsendtowhatsapp()
	{
		try {

			$order_uuid = Yii::app()->request->getQuery('order_uuid', '');  
			$order = COrders::get($order_uuid);
			$merchant_id = $order->merchant_id;            
            $options = OptionsTools::find(['merchant_whatsapp_phone_number','merchant_enabled_whatsapp'],$merchant_id);
            $merchant_whatsapp_phone_number = isset($options['merchant_whatsapp_phone_number'])?$options['merchant_whatsapp_phone_number']:false;
            $merchant_enabled_whatsapp = isset($options['merchant_enabled_whatsapp'])?$options['merchant_enabled_whatsapp']:false;
            $merchant_enabled_whatsapp = $merchant_enabled_whatsapp==1?true:false;                        
			$tpl = CNotifications::sendReceiptByWhatsapp($order_uuid,$merchant_whatsapp_phone_number,2,false);			
			$encodedMessage = urlencode($tpl);			
			$whatsappURL = "https://api.whatsapp.com/send?phone=$merchant_whatsapp_phone_number&text=$encodedMessage";
			header("Location: $whatsappURL");
			Yii::app()->end();
		} catch (Exception $e) {		   
			echo t($e->getMessage());		   
		}		
	}

	public function actionuploadaudio()
	{
		try {			
			$input = json_decode(file_get_contents('php://input'), true);
			if (!empty($input['audioBase64']) && !empty($input['fileName'])) {
				$audioBase64 = $input['audioBase64'];
                $fileName = $input['fileName'];
				$audioData = base64_decode($audioBase64);

				$fileSizeInBytes = strlen($audioData);
				$fileSizeInMB = $fileSizeInBytes / (1024 * 1024);

				if ($fileSizeInMB > 3) {
					$this->msg = t("File size exceeds the 3MB limit.");
					$this->responseJson();
				}

				$upload_path = "upload/audio";
				$uploadDir = CommonUtility::uploadDestination($upload_path);
				$filePath = "$uploadDir/$fileName";
				if (file_put_contents($filePath, $audioData)) {
					$this->code = 1;
					$this->msg = t("File uploaded successfully!");					
					$file_url = Yii::app()->createAbsoluteUrl("/")."/$upload_path/$fileName";
					$this->details = [
						'filename'=>$fileName,
						'file_url'=>$file_url,
						'fileSizeInMB'=>CommonUtility::HumanFilesize($fileSizeInMB)
					];
				} else {
					$this->msg = t("Failed to save the file.");
				}
			} else $this->msg = t("Invalid input data.");
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actiongetFeaturedItems()
	{
		try {
										
			$currency_code =  Yii::app()->request->getQuery('currency_code', '');  
			$cart_uuid =  Yii::app()->request->getQuery('cart_uuid', '');  
			$lat =  Yii::app()->request->getQuery('latitude', null);  
			$lng =  Yii::app()->request->getQuery('longitude', null);  
		    $base_currency = Price_Formatter::$number_format['currency_code'];		

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		    $multicurrency_enabled = $multicurrency_enabled==1?true:false;		

			$exchange_rate = 1;
			$currency_code = !empty($currency_code)?$currency_code:$base_currency;
			// SET CURRENCY
			if(!empty($currency_code) && $multicurrency_enabled){				
				Price_Formatter::init($currency_code);
				if($currency_code!=$base_currency){
					$exchange_rate = CMulticurrency::getExchangeRate($base_currency,$currency_code);					
				}
			}

			$promos = []; $subtotal= 0; $cart_merchant_id ='';
			try {				
				$cart_merchant_id = CCart::getMerchantId($cart_uuid);				
			    $promos = CMerchantMenu::getItemActivePromo($cart_merchant_id);					
				if(is_array($promos) && count($promos)>=1){
					$subtotal = CCart::getCartSubtotal($cart_uuid,$promos);
				}			    
			} catch (Exception $e) {}			
						

			CMerchantMenu::setExchangeRate($exchange_rate);
			$data = CMerchantMenu::getFeaturedItems(Yii::app()->language,[
				'cart_merchant_id'=>$cart_merchant_id,
				'subtotal'=>$subtotal,
				'lat' => $lat,
				'lng' => $lng,
			]);						
			
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'data'=>$data
			];

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}	
		$this->responseJson();
	}
	
	public function actionpromocheck()
	{
		try {			
			
			$subtotal = 0;
			$cart_uuid =  Yii::app()->request->getQuery('cart_uuid', null);						
			$merchant_id =  Yii::app()->request->getQuery('merchant_id', null);
						
			if(!$merchant_id){
				$this->msg = t("Invalid merchant id");
				$this->responseJson();
			}
			
			if (!is_numeric($merchant_id) || $merchant_id == 0) {
                $this->msg = t("Invalid merchant id");
				$this->responseJson();
			}

			$promos = CMerchantMenu::getItemActivePromo($merchant_id);			
			if (!is_array($promos) || empty($promos)) {
				$this->msg = t("No promos");
				$this->responseJson();
			}

			try {
				$cart_merchant_id = CCart::getMerchantId($cart_uuid);			
				if($cart_merchant_id!=$merchant_id){
					$this->msg = t("Merchant cart invalid");
					$this->responseJson();
				}				
			} catch (Exception $e) {}			

			$subtotal = CCart::getCartSubtotal($cart_uuid,$promos);					

			$data = CMerchantMenu::PromoItemsCheck($merchant_id,$subtotal);
			$this->code = 1;
			$this->msg = "Ok";			
			$this->details = [
				'subtotal'=>$subtotal,
				'data'=>$data
			];
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actionfetchwallet()
	{
		try {
			
			$cart_uuid =  Yii::app()->request->getQuery('cart_uuid', null);
			$currency_code =  Yii::app()->request->getQuery('currency_code', null);
			$amount_to_pay =  Yii::app()->request->getQuery('cart_total', 0);
			$base_currency = Price_Formatter::$number_format['currency_code'];			
			$exchange_rate = 1; $exchange_rate_to_merchant = 1;
			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
            $multicurrency_enabled = $multicurrency_enabled==1?true:false;		

			if(!empty($currency_code) && $multicurrency_enabled){
				Price_Formatter::init($currency_code);
				if($currency_code!=$base_currency){
					$exchange_rate = CMulticurrency::getExchangeRate($base_currency,$currency_code);					
				}
			}

			$balance_raw = CDigitalWallet::getAvailableBalance(Yii::app()->user->id);
			$balance_raw = ($balance_raw*$exchange_rate);
			$amount_due = CDigitalWallet::calculateAmountDue($amount_to_pay,$balance_raw);

			$message = t("Insufficient balance to place this order. You need at least {amount_due} in your wallet",[
				'{amount_due}'=>Price_Formatter::formatNumber($amount_due)
			]);

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'topup'=> $amount_due>0 ? true : false,
				'message'=>$message,				
				'amount_due'=>Price_Formatter::formatNumber($amount_due),
				'amount_due_raw'=>$amount_due,
				'balance'=>t("Wallet Balance: {balance}",[
					'{balance}'=>Price_Formatter::formatNumber($balance_raw)
				])
			];
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}	
		$this->responseJson();
	}
}
/*end class*/