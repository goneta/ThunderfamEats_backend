<?php
require 'intervention/vendor/autoload.php';
require 'php-jwt/vendor/autoload.php';
use Intervention\Image\ImageManager;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
require 'dompdf/vendor/autoload.php';
require 'twig/vendor/autoload.php';
use Dompdf\Dompdf;
define("SOCIAL_STRATEGY", 'single');

class PartnerapiController extends PartnerCommon
{
 
    public function beforeAction($action)
	{										
		$method = Yii::app()->getRequest()->getRequestType();    		
		if($method=="POST"){
			$this->data = Yii::app()->input->xssClean(json_decode(file_get_contents('php://input'), true));
		} else if($method=="GET"){
		   $this->data = Yii::app()->input->xssClean($_GET);				
		} elseif ($method=="OPTIONS" ){
			$this->responseJson();
		} else $this->data = Yii::app()->input->xssClean($_POST);			
				
		$this->initSettings();
		return true;
	}

    public function actionIndex()
    {
		echo "API Index";
    }

	public function actiongetBanner()
	{
		try {						
			$data = CMerchants::getBanner(Yii::app()->merchant->id);			
			$items_id = array();
			foreach ($data as $items) {
				$items_id[] = $items['item_id'];
			}
			$item_data = CMerchantMenu::getItemsByIds($items_id);
			$this->code = 1; $this->msg = "OK";
			$this->details = [ 
				'data'=>$data,
				'item_data'=>$item_data
			];
		} catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
	}

	public function actiongetFooter()
	{
		MMenu::buildMenu(0,false,PPages::menuMerchantType(),Yii::app()->merchant->id);		
		$options = OptionsTools::find(['facebook_page','twitter_page','google_page','instagram_page'],Yii::app()->merchant->id);		
		$data = MMenu::$items;		

		$page_title_translation = PPages::pageTitleBySlug(Yii::app()->merchant->id,Yii::app()->language);		

		$this->code = 1;
		$this->msg = 'ok';
		$this->details = [
			'data'=>$data,
			'social'=>$options,
			'page_title_translation'=>$page_title_translation
		];
		$this->responseJson();
	}

    public function actionitemfeatured()
    {
        try {            			
            $meta = Yii::app()->input->post('meta');        
			$rows = Yii::app()->input->post('rows');        			
            $data = CMerchantMenu::getItemFeatured(Yii::app()->merchant->id,$meta,Yii::app()->language);			
			$total = count($data);
			if($rows>0){
				$data = CommonUtility::dataToRow($data,$total>$rows?$rows:$total);
			}			
            $this->code = 1;
            $this->msg = "OK";        
            $this->details = $data;
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actionCategory()
    {
        	    		
	    try {
           
		   $rows = intval(Yii::app()->input->get('rows')); 		    		   
		   $category = CMerchantMenu::getCategory( Yii::app()->merchant->id ,Yii::app()->language);			
		   $total = count($category);
		   if($rows>0){
			 $category = CommonUtility::dataToRow($category,$total>$rows?$rows:$total);
		   }		   
		   $this->code = 1; $this->msg = "OK";
		   $this->details = array(		     		    
		     'data'=>$category
		   );		   		   
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		   
		}		
		$this->responseJson();
    }

	public function actionMenuCategory()
    {        	    		
	    try {
		   			
		   $category = CMerchantMenu::getCategory( Yii::app()->merchant->id ,Yii::app()->language);		   
		   $this->code = 1; $this->msg = "OK";
		   $this->details = array(		     		    
		     'data'=>$category
		   );		   		   
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		   
		}		
		$this->responseJson();
    }

	public function actiongeStoreMenu()
	{						
		try {
		   
		   $merchant_id = Yii::app()->merchant->id;
		   $category = CMerchantMenu::getCategory($merchant_id,Yii::app()->language);		
		   $items = CMerchantMenu::getMenu($merchant_id,Yii::app()->language);		   		   
		   $items_not_available = CMerchantMenu::getItemAvailability($merchant_id,date("w"),date("H:h:i"));
		   $category_not_available = CMerchantMenu::getCategoryAvailability($merchant_id,date("w"),date("H:h:i"));			   
		   $data = array(
		     'category'=>$category,
		     'items'=>$items,
			 'items_not_available'=>$items_not_available,
			 'category_not_available'=>$category_not_available
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

	public function actionSimilarItems()
	{
		$merchant_id = Yii::app()->merchant->id;		
		try {		   
		   $row = trim(Yii::app()->input->post('rows'));
		   $rows = intval(Yii::app()->input->get('rows')); 		   
		   $rows = $rows<=0?4:$rows;		   
		   $items = CMerchantMenu::getSimilarItems($merchant_id,Yii::app()->language);	
		   $total = count($items);
		   if($row!="infinite"){
			  $items = CommonUtility::dataToRow($items,$total>$rows?$rows:$total);		   
		   }		   
		   $this->code = 1; $this->msg = "OK";
		   $this->details = array(		     
		     'data'=>$items
		   );		   
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		   		   
		}		
		$this->responseJson();
	}

	public function actiongetMenuItem2(){

		$this->actiongetMenuItem();
	}

	public function actiongetMenuItem()
	{		
						
		$merchant_id = Yii::app()->merchant->id;
		$item_uuid = Yii::app()->input->post('item_uuid');
		$cat_id = intval(Yii::app()->input->post('cat_id'));
				
		try {
			$items = CMerchantMenu::getMenuItem($merchant_id,$cat_id,$item_uuid,Yii::app()->language);
			$addons = CMerchantMenu::getItemAddonCategory($merchant_id,$item_uuid,Yii::app()->language);
			$addon_items = CMerchantMenu::getAddonItems($merchant_id,$item_uuid,Yii::app()->language);	
			$meta = CMerchantMenu::getItemMeta($merchant_id,$item_uuid);
			$meta_details = CMerchantMenu::getMeta($merchant_id,$item_uuid,Yii::app()->language);	
			
			AppUserIdentity::getCustomerIdentity();
			$items['save_item']	= false;
						
			if(!Yii::app()->user->isGuest){
				try {
					CSavedStore::getSaveItems(Yii::app()->user->id,$items['merchant_id'],$items['item_id']);
					$items['save_item']	= true;
				} catch (Exception $e) {
					//
				}
			}

			$items_not_available = CMerchantMenu::getItemAvailability($merchant_id,date("w"),date("H:h:i"));
			$category_not_available = CMerchantMenu::getCategoryAvailability($merchant_id,date("w"),date("H:h:i"));
							
			$data = array(
			  'items'=>$items,
			  'addons'=>$addons,
			  'addon_items'=>$addon_items,
			  'meta'=>$meta,
			  'meta_details'=>$meta_details,
			  'items_not_available'=>$items_not_available,
			  'category_not_available'=>$category_not_available
			);

			$config = array();
			$format = Price_Formatter::$number_format;
			$config = [				
				'precision' => $format['decimals'],
				'decimal' => $format['decimal_separator'],
				'thousands' => $format['thousand_separator'],
				'prefix'=> $format['position']=='left'?$format['currency_symbol']:'',
				'suffix'=> $format['position']=='right'?$format['currency_symbol']:''
			];			
			$this->code = 1; $this->msg = "ok";
		    $this->details = array(
		      'next_action'=>"show_item_details",
		      'sold_out_options'=>AttributesTools::soldOutOptions(),
			  'default_sold_out_options'=>[
				  'label'=>t("Go with merchant recommendation"),
				  'value'=>"substitute"
			  ],
		      'data'=>$data,
			  'config'=>$config
		    );		    		    
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());
		   $this->details = array(
		      'data'=>array()
		    );		    	   
		}		
		$this->responseJson();
	}

	public function actionaddCartItems()
	{
		
		$this->data = Yii::app()->input->xssClean(json_decode(file_get_contents('php://input'), true));		

		$merchant_id = Yii::app()->merchant->id;
		$uuid = CommonUtility::createUUID("{{cart}}",'cart_uuid');
		$cart_row = CommonUtility::generateUIID();
		$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';		
		$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';		
		$cart_uuid = !empty($cart_uuid)?$cart_uuid:$uuid;		
		$cat_id = isset($this->data['cat_id'])?(integer)$this->data['cat_id']:'';
		$item_token = isset($this->data['item_token'])?$this->data['item_token']:'';
		$item_size_id = isset($this->data['item_size_id'])?(integer)$this->data['item_size_id']:0;
		$item_qty = isset($this->data['item_qty'])?(integer)$this->data['item_qty']:0;
		$special_instructions = isset($this->data['special_instructions'])?$this->data['special_instructions']:'';
		$if_sold_out = isset($this->data['if_sold_out'])?$this->data['if_sold_out']:'';
		$inline_qty = isset($this->data['inline_qty'])?(integer)$this->data['inline_qty']:0;

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
			'inline_qty'=>$inline_qty
		);		
				 		
		try {
			
			CCart::add($items);
										  
			CCart::savedAttributes($cart_uuid,Yii::app()->params->local_transtype,$transaction_type);			
					  
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
										
			$this->code = 1 ; $this->msg = "OK";			
			$this->details = array(
			  'cart_uuid'=>$cart_uuid
			);		 
			  
		  } catch (Exception $e) {
			 $this->msg = t($e->getMessage());
			 $this->details = array(
				'data'=>array()
			  );		    	   
		  }		
		$this->responseJson();
	}

	public function actiongetCartCheckout()
	{		
		$this->actiongetCart();
	}

	public function actiongetCart()
	{							
		$local_id = isset($this->data['local_id'])?trim($this->data['local_id']):'';				
		$cart_uuid = isset($this->data['cart_uuid'])?trim($this->data['cart_uuid']):'';		
		$payload = isset($this->data['payload'])?$this->data['payload']:'';
		
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
		$items_count=0;
		
		try {
							
			
			if(in_array('distance',(array)$payload)){
				if($credentials = CMerchants::MapsConfig(Yii::app()->merchant->id)){
					MapSdk::$map_provider = $credentials['provider'];
					MapSdk::setKeys(array(
					  'google.maps'=>$credentials['key'],
					  'mapbox'=>$credentials['key'],
					));				 
				}
			}
				
			require_once 'get-cart.php';			
						
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
		    );				    			
		} catch (Exception $e) {
		   $error[] = t($e->getMessage());		
		   $this->details = array('items_count'=>$items_count);	   		   
		}						
		$this->responseJson();
	}

	public function actionclearCart()
	{				
		$cart_uuid = Yii::app()->input->post('cart_uuid');
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
		$this->responseJson();
	}

	public function actionremoveCartItem()
	{		
		$cart_uuid = Yii::app()->input->post('cart_uuid');
		$row = Yii::app()->input->post('row');
		
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
		$this->responseJson();
	}

	public function actionupdateCartItems()
	{		
		
		$cart_uuid = Yii::app()->input->post('cart_uuid');
		$cart_row = Yii::app()->input->post('cart_row');
		$item_qty = Yii::app()->input->post('item_qty');
		try {
			
			CCart::update($cart_uuid,$cart_row,$item_qty);
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
		$this->responseJson();
	}

	public function actiongetlocationAutocomplete()
	{						
		try {
					   		
		   $q = Yii::app()->input->post('q');
		   
		   $credentials = CMerchants::MapsConfig(Yii::app()->merchant->id);

		   if(!$credentials){
		   	   $this->msg = t("No default map provider, check your settings.");
		   	   $this->responseJson();
		   }
		   		   		   
		   MapSdk::$map_provider = $credentials['provider'];
		   MapSdk::setKeys(array(
		     'google.maps'=>$credentials['key'],
		     'mapbox'=>$credentials['key'],
		   ));

		   $options = OptionsTools::find(['merchant_set_default_country'],Yii::app()->merchant->id);
		   $merchant_set_default_country = isset($options['merchant_set_default_country'])?$options['merchant_set_default_country']:'';
		   if(!empty($merchant_set_default_country)){
			  MapSdk::setMapParameters(array(
				'country'=>$merchant_set_default_country
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

	public function actionreverseGeocoding()
	{		
		$lat = Yii::app()->input->post('lat');
		$lng = Yii::app()->input->post('lng');
		$next_steps = isset($this->data['next_steps'])?$this->data['next_steps']:'';
		
		$services = Yii::app()->input->post('services');
	    if(!empty($services)){
	   	  $services = substr($services,0,-1);
	    } else $services="all";
		
		try {
			
		   $credentials = CMerchants::MapsConfig(Yii::app()->merchant->id);
		   if(!$credentials){
			  $this->msg = t("No default map provider, check your settings.");
			  $this->responseJson();
	       }
		   
		   MapSdk::$map_provider =  $credentials['provider'];
		   MapSdk::setKeys(array(
		     'google.maps'=>$credentials['key'],
		     'mapbox'=>$credentials['key']
		   ));
		   
		   if(MapSdk::$map_provider=="mapbox"){
			   MapSdk::setMapParameters(array(
			    'types'=>"poi",
			    'limit'=>1
			   ));
		   }
		   
		   $resp = MapSdk::reverseGeocoding($lat,$lng);
		   
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
		$this->responseJson();
	}	

	public function actiongetLocationDetails()
	{
		try {

			if($credentials = CMerchants::MapsConfig(Yii::app()->merchant->id)){				
				MapSdk::$map_provider = $credentials['provider'];
				MapSdk::setKeys(array(
					'google.maps'=>$credentials['key'],
					'mapbox'=>$credentials['key'],
				));
			}
			
			$place_id = Yii::app()->input->post('place_id');
			$resp = CMaps::locationDetailsNew($place_id,'');

			$this->code =1; $this->msg = "ok";
			$this->details = array(
			  'data'=>$resp,					  
			);
							
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetDeliveryDetails()
	{		
		try {

			$cart_uuid = Yii::app()->input->post('cart_uuid');
			$delivery_option = CCheckout::deliveryOptionList();
			$merchant_id = Yii::app()->merchant->id;

			$transaction_type = CServices::getSetService($cart_uuid);			
			$data = CCheckout::getMerchantTransactionList($merchant_id,Yii::app()->language);						

			$delivery_date = ''; $delivery_time='';
			if($atts = CCart::getAttributesAll($cart_uuid,array('delivery_date','delivery_time'))){				
				$delivery_date = isset($atts['delivery_date'])?$atts['delivery_date']:'';
				$delivery_time = isset($atts['delivery_time'])?$atts['delivery_time']:'';
			}
			$whento_deliver = CCheckout::getWhenDeliver($cart_uuid);

			$this->code = 1; $this->msg = "OK";
			$this->details = array(
			  'transaction_type'=>$transaction_type,
			  'data'=>$data,
			  'delivery_option'=>$delivery_option,
			  'delivery_date'=>$delivery_date,
			  'delivery_time'=>$delivery_time,
			  'whento_deliver'=>$whento_deliver
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
			$merchant_id = Yii::app()->merchant->id;
			
			$delivery_option = CCheckout::deliveryOptionList();
			$whento_deliver = CCheckout::getWhenDeliver($cart_uuid);
						
			$model = AR_opening_hours::model()->find("merchant_id=:merchant_id",array(
			  ':merchant_id'=>$merchant_id
			));
			if(!$model){
				$this->msg[] = t("Merchant has not set time opening yet");
				$this->responseJson();
			}			
			
			$options = OptionsTools::find(array('website_time_picker_interval'));
			$interval = isset($options['website_time_picker_interval'])?$options['website_time_picker_interval']." mins":'20 mins';			

			// CHECK IF MERCHANT HAS DIFFERENT TIMEZONE
			$options_merchant = OptionsTools::find(['merchant_time_picker_interval','merchant_timezone'],$merchant_id);			
			$interval_merchant = isset($options_merchant['merchant_time_picker_interval'])? ( !empty($options_merchant['merchant_time_picker_interval']) ? $options_merchant['merchant_time_picker_interval']." mins" :''):'';
			$interval = !empty($interval_merchant)?$interval_merchant:$interval;
			$merchant_timezone = isset($options_merchant['merchant_timezone'])?$options_merchant['merchant_timezone']:'';
			if(!empty($merchant_timezone)){
			   Yii::app()->timezone = $merchant_timezone;
			}			

			$opening_hours = CMerchantListingV1::openHours($merchant_id,$interval);		
			$delivery_date = ''; $delivery_time='';

			if($atts = CCart::getAttributesAll($cart_uuid,array('delivery_date','delivery_time'))){				
				$delivery_date = isset($atts['delivery_date'])?$atts['delivery_date']:'';
				$delivery_time = isset($atts['delivery_time'])?$atts['delivery_time']:'';
			}
						
			$this->code = 1; $this->msg = "ok";			
		    $this->details = array(		     
		       'delivery_option'=>$delivery_option,
		       'whento_deliver'=>$whento_deliver,
		       'delivery_date'=>$delivery_date,
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
			if(empty($cart_uuid)){
				$cart_uuid = CommonUtility::createUUID("{{cart}}",'cart_uuid');
			}			

			$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';
			$whento_deliver = isset($this->data['whento_deliver'])?$this->data['whento_deliver']:'';
			$delivery_date = isset($this->data['delivery_date'])?$this->data['delivery_date']:'';
			$delivery_time = isset($this->data['delivery_time'])?$this->data['delivery_time']:'';

			if($whento_deliver=="schedule"){
				if(empty($delivery_date)){
					$this->msg = t("Delivery date is required");
					$this->responseJson();
				}				
				if(empty($delivery_time)){
					$this->msg = t("Delivery time is required");
					$this->responseJson();
				}				
			}
						
			CCart::savedAttributes($cart_uuid,'whento_deliver',$whento_deliver);			  
			CCart::savedAttributes($cart_uuid,'delivery_date',$delivery_date);
			CCart::savedAttributes($cart_uuid,'delivery_time',json_encode($delivery_time));
			CCart::savedAttributes($cart_uuid,'transaction_type',$transaction_type);			
								
			$delivery_datetime = CCheckout::jsonTimeToFormat($delivery_date,json_encode($delivery_time));
			
			$this->code = 1; $this->msg = "OK";
			$this->details = array(
			  'whento_deliver'=>$whento_deliver,
			  'delivery_date'=>$delivery_date,
			  'delivery_time'=>$delivery_time,
			  'delivery_datetime'=>$delivery_datetime,	
			  'cart_uuid'=>$cart_uuid		  
			);						
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    		    
		}
		$this->responseJson();
	}

	public function actionTransactionInfo()
	{

		try {
			$credentials = CMerchants::MapsConfig(Yii::app()->merchant->id);			
			if($credentials){
				MapSdk::$map_provider = $credentials['provider'];
				MapSdk::setKeys(array(
					'google.maps'=>$credentials['key'],
					'mapbox'=>$credentials['key'],
				));
			}						
		} catch (Exception $e) {
			//
		}
		
		try {
			
			$whento_deliver = ''; $delivery_datetime='';
			$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
			$local_id = isset($this->data['local_id'])?$this->data['local_id']:'';			

			$local_info = [];
			try {
				$local_info = CMaps::locationDetailsNew($local_id,'');			
			} catch (Exception $e) {}
			
			$delivery_option = CCheckout::deliveryOptionList();
												
			$data = isset($this->data['choosen_delivery'])?$this->data['choosen_delivery']:'';

			$transaction_type='';
			$services_list = CServices::Listing( Yii::app()->language );
			
			if(is_array($data) && count($data)>=1){						
				$whento_deliver = isset($data['whento_deliver'])?$data['whento_deliver']:'now';
				$delivery_date = isset($data['delivery_date'])?$data['delivery_date']:date("Y-m-d");
				$delivery_time = isset($data['delivery_time'])?$data['delivery_time']:'';			
				$transaction_type = isset($data['transaction_type'])?$data['transaction_type']:'';				
				$delivery_datetime = CCheckout::jsonTimeToFormat($delivery_date,json_encode($delivery_time));
			} else {				
				$whento_deliver = CCheckout::getWhenDeliver($cart_uuid);
				$delivery_datetime = CCheckout::getScheduleDateTime($cart_uuid,$whento_deliver);	
				$transaction_type = CServices::getSetService($cart_uuid);
			}

			// CHECK TRANSACTION TYPE GET ONLY AVAILABLE SERVICES LIST
			$merchant_services = [];
			try {
				$merchant_services = CCheckout::getMerchantTransactionList(Yii::app()->merchant->id,Yii::app()->language);	
			} catch (Exception $e) {}	
			
			if(!array_key_exists($transaction_type,(array)$merchant_services)){
				$transaction_type = CCheckout::getFirstTransactionType(Yii::app()->merchant->id);
			}			

			$phone  = CCart::getAttributesAll($cart_uuid,['contact_number_prefix','contact_number']);			
									
			$this->code = 1; $this->msg ="ok";
			$this->details = array(
			  'address1'=> isset($local_info['address']) ? $local_info['address']['address1'] :'',
			  'formatted_address'=> isset($local_info['address'])? $local_info['address']['formatted_address'] :'',
			  'delivery_option'=>$delivery_option,
			  'whento_deliver'=>$whento_deliver,
			  'delivery_datetime'=>$delivery_datetime,
			  'transaction_type'=>$transaction_type,
			  'services_list'=>$services_list,
			  'merchant_services'=>$merchant_services,
			  'contact_number_prefix'=>isset($phone['contact_number_prefix'])?$phone['contact_number_prefix']:'',
			  'contact_number'=>isset($phone['contact_number'])?$phone['contact_number']:'',
			);					
		} catch (Exception $e) {			
		    $this->msg = t($e->getMessage());		    		    
		}
		$this->responseJson();
	}

	public function actionaddressAtttibues()
	{
		try {
			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(			  
			  'delivery_option'=>CCheckout::deliveryOption(),
			  'address_label'=>CCheckout::addressLabel(),
			  'maps_config'=>CMerchants::MapsConfig(Yii::app()->merchant->id,false)
			);				
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());		
		}			
		$this->responseJson();
	}

	public function actionsaveClientAddress()
	{
		$this->data = Yii::app()->input->xssClean(json_decode(file_get_contents('php://input'), true));	
				
		$data = isset($this->data['data'])?$this->data['data']:'';
		$location_name = isset($this->data['location_name'])?$this->data['location_name']:'';
		$delivery_instructions = isset($this->data['delivery_instructions'])?$this->data['delivery_instructions']:'';
		$delivery_options = isset($this->data['delivery_options'])?$this->data['delivery_options']:'';
		$address_label = isset($this->data['address_label'])?$this->data['address_label']:'';				
		try {
			
						
			$address = array(); 			
			$address = isset($data['address'])?$data['address']:'';

			$address_uuid = isset($data['address_uuid'])?$data['address_uuid']:'';
			$new_lat = isset($data['latitude'])?$data['latitude']:''; 
			$new_lng = isset($data['longitude'])?$data['longitude']:'';
			$place_id = isset($data['place_id'])?$data['place_id']:'';
			
			$address1 = isset($address['address1'])?$address['address1']:'';
			$address2 = isset($address['address2'])?$address['address2']:'';
			$country = isset($address['country'])?$address['country']:'';
			$country_code = isset($address['country_code'])?$address['country_code']:'';
			$postal_code = isset($address['postal_code'])?$address['postal_code']:'';			
									
			if(!empty($address_uuid)){
				$model = AR_client_address::model()->find('address_uuid=:address_uuid AND client_id=:client_id', 
		        array(':address_uuid'=>$address_uuid,'client_id'=>Yii::app()->user->id)); 
			} else $model = new AR_client_address;
		    
			$model->client_id = Yii::app()->user->id;
			$model->place_id = $place_id; 
			$model->latitude = $new_lat;
			$model->longitude = $new_lng;
			$model->location_name = $location_name;
			$model->delivery_options = $delivery_options;
			$model->delivery_instructions = $delivery_instructions;
			$model->address_label = $address_label;
			$model->formatted_address = isset($this->data['formatted_address'])?$this->data['formatted_address']:'';
			$model->address1 = $address1;
			$model->address2 = $address2;
			$model->country = $country;
			$model->country_code = $country_code;
			$model->postal_code = $postal_code;

			if($model->save()){
				$this->code = 1;
				$this->msg = "OK";
				$this->details = array(
                   'place_id'=>$place_id
				);
			} else {
				$this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
			}
					
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}		
		$this->responseJson();
	}

	public function actionclientAddresses()
	{
	   	try {			
			$addresses = CClientAddress::getAddresses( Yii::app()->user->id );				
			$this->code = 1;
			$this->msg = "ok";
			$this->details = array(
				'addresses'=>$addresses,			  
			);
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}			    
		$this->responseJson();
	}

	public function actiondeleteAddress()
	{				
		$address_uuid =  Yii::app()->input->post('address_uuid');		
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

	public function actionvalidateCoordinates()
	{		
		$unit = Yii::app()->params['settings']['home_search_unit_type'];			
		$lat = isset($this->data['lat'])?$this->data['lat']:'';
		$lng = isset($this->data['lng'])?$this->data['lng']:'';
		$new_lat = isset($this->data['new_lat'])?$this->data['new_lat']:'';
		$new_lng = isset($this->data['new_lng'])?$this->data['new_lng']:'';
		
		$distance = CMaps::getLocalDistance($unit,$lat,$lng,$new_lat,$new_lng);				
		if($distance=="NaN"){
			$this->code = 1;
			$this->msg = "OK";
		} else if ($distance<0.2) {	
			$this->code = 1;
			$this->msg = "OK";
		} else if ($distance>=0.2) {
			$this->msg[] = t("Pin location is too far from the address");
		}		
		$this->details = array(
		  'distance'=>$distance
		);		
		$this->responseJson();
	}

	public function actioncheckoutAddress()
	{		
		try {
			$place_id = Yii::app()->input->post('place_id');					
			$data = CClientAddress::getAddress($place_id,Yii::app()->user->id);
			
			$tips_settings = OptionsTools::find(['merchant_enabled_tip'],Yii::app()->merchant->id);
			$enabled_tip = isset($tips_settings['merchant_enabled_tip'])?$tips_settings['merchant_enabled_tip']:false;			

			$this->code =1;
			$this->msg = "ok";
			$this->details = [
				'data'=>$data,
				'maps_config'=>CMerchants::MapsConfig(Yii::app()->merchant->id,false),
				'enabled_tip'=>$enabled_tip
			];
	    } catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetPhone()
	{
		try {

			$options = OptionsTools::find(array('mobilephone_settings_default_country','mobilephone_settings_country'));			
			$phone_default_country = isset($options['mobilephone_settings_default_country'])?$options['mobilephone_settings_default_country']:'us';
            $phone_country_list = isset($options['mobilephone_settings_country'])?$options['mobilephone_settings_country']:'';
            $phone_country_list = !empty($phone_country_list)?json_decode($phone_country_list,true):array();        
			
			$data = AttributesTools::countryMobilePrefixWithFilter($phone_country_list);

			$cart_uuid = Yii::app()->input->post('cart_uuid');
			
			$atts = CCart::getAttributesAll($cart_uuid,array('contact_number','contact_number_prefix'));			
			$contact_number = isset($atts['contact_number'])?$atts['contact_number']:'';
			$default_prefix = isset($atts['contact_number_prefix'])?$atts['contact_number_prefix']:'';	
						
			if(empty($contact_number)){
				$contact_number = Yii::app()->user->contact_number;
				$default_prefix = Yii::app()->user->phone_prefix;			
			}
												
			$contact_number = str_replace($default_prefix,"",$contact_number);
			$default_prefix = str_replace("+","",$default_prefix);
			
			if(empty($default_prefix)){
				$default_prefix_array = AttributesTools::getMobileByShortCode($phone_default_country);
			} else $default_prefix_array = AttributesTools::getMobileByPhoneCode($default_prefix);
			
						
			$this->code = 1;
			$this->msg = "OK";			
			$this->details = array(
			  'contact_number_w_prefix'=>$default_prefix.$contact_number,
			  'contact_number'=>$contact_number,
			  'default_prefix'=>$default_prefix,
			  'default_prefix_array'=>$default_prefix_array,
			  'prefixes'=>$data,
			  'phone_default_country'=>$phone_default_country
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
		           $digit_code = CommonUtility::generateNumber(3,true);
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
	
	public function actionverifyCode()
	{
		try {
			$code = Yii::app()->input->post('code');
			$model = AR_client::model()->find('client_id=:client_id AND mobile_verification_code=:mobile_verification_code', 
			array(':client_id'=>Yii::app()->user->id,':mobile_verification_code'=>trim($code) )); 		
			if(!$model){
				$this->code = 1;
				$this->msg = "OK";
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
			$code = isset($this->data['code'])?$this->data['code']:'';
			$mobile_prefix = isset($this->data['phone_prefix'])?$this->data['phone_prefix']:'';
			$mobile_number = isset($this->data['phone_number'])?$this->data['phone_number']:'';
					   
			$model = AR_client::model()->find('client_id=:client_id AND mobile_verification_code=:mobile_verification_code', 
			array(':client_id'=>Yii::app()->user->id,':mobile_verification_code'=>trim($code) )); 		
			if($model){
				   $model->phone_prefix = $mobile_prefix;
				   $model->contact_phone = $mobile_prefix.$mobile_number;
				   if($model->save()){	
					   CCart::savedAttributes($cart_uuid,'contact_number', $model->contact_phone );
					   CCart::savedAttributes($cart_uuid,'contact_number_prefix', $mobile_prefix );

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

	public function actionloadPromo()
	{
		
		try {
			
			$cart_uuid = Yii::app()->input->post('cart_uuid');
			$merchant_id = CCart::getMerchantId($cart_uuid);			
			$data = CPromos::promo($merchant_id,date("Y-m-d"));	
			
			$promo_selected = array();
			$atts = CCart::getAttributesAll($cart_uuid,array('promo','promo_type','promo_id'));
			if($atts){
				$saving = '';
				if(isset($atts['promo'])){
					if ($promo = json_decode($atts['promo'],true)){																		
						if($promo['type']=="offers"){
							$merchant_id = CCart::getMerchantId($cart_uuid);
			                CCart::getContent($cart_uuid,Yii::app()->language);	
							$subtotal = CCart::getSubTotal();							
			                $sub_total = floatval($subtotal['sub_total']);
							$discount_percent = isset($promo['value'])? CCart::cleanValues($promo['value']):0;							
							$discount_value = ($discount_percent/100) * $sub_total;
							$saving = t("You're saving {{discount}}",array(
								'{{discount}}'=>Price_Formatter::formatNumber($discount_value)
							));
						} elseif ( $promo['type']=="voucher" ){
							$discount_value = isset($promo['value'])?$promo['value']:0;
							$discount_value = $discount_value*-1;	
							$saving = t("You're saving {{discount}}",array(
							  '{{discount}}'=>Price_Formatter::formatNumber($discount_value)
							));
						}
						$promo_selected = [
							'promo_type'=>$atts['promo_type'],
							'promo_id'=>$atts['promo_id'],
							'savings'=>$saving
						];
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
		
		try {

			$merchant_id = CCart::getMerchantId($cart_uuid);
			CCart::getContent($cart_uuid,Yii::app()->language);	
			$subtotal = CCart::getSubTotal();
			$sub_total = floatval($subtotal['sub_total']);
			
			$now = date("Y-m-d");			
			$params = array();
				   
			if($promo_type==="voucher"){
												
				$resp = CPromos::applyVoucher( $merchant_id, $promo_id, Yii::app()->user->id , $now , $sub_total);
				$less_amount = $resp['less_amount'];
				
				$params = array(
				  'name'=>"less voucher",
				  'type'=>$promo_type,
				  'id'=>$promo_id,
				  'target'=>'subtotal',
				  'value'=>"-$less_amount",
				);		
				
				
			} else if ($promo_type=="offers") {		
				
				$transaction_type = CCart::cartTransaction($cart_uuid,Yii::app()->params->local_transtype,$merchant_id);							
				$resp = CPromos::applyOffers( $merchant_id, $promo_id, $now , $sub_total , $transaction_type);				
				$less_amount = $resp['less_amount'];
				
				$name = array(
				  'label'=>"Discount {{discount}}%",
				  'params'=>array(
				   '{{discount}}'=>Price_Formatter::convertToRaw($less_amount,0)
				  )
				);
				$params = array(
				  'name'=> json_encode($name),
				  'type'=>$promo_type,
				  'id'=>$promo_id,
				  'target'=>'subtotal',
				  'value'=>"-%$less_amount"
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
		$this->responseJson();
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
		    	
		    	$resp = CPromos::applyVoucher( $merchant_id, $promo_id, Yii::app()->user->id , $now , $sub_total);
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

	public function actionloadTips()
	{
		$cart_uuid = Yii::app()->input->post('cart_uuid');
		try {
			
			$merchant_id = CCart::getMerchantId($cart_uuid);			
			$options_data = OptionsTools::find(['merchant_tip_type'],$merchant_id);
			$tip_type = isset($options_data['merchant_tip_type'])?$options_data['merchant_tip_type']:'fixed';						
			//$data = CTips::data('label');
			$data = CTips::data('label',$tip_type);
			
			$tips = 0; $transaction_type = '';			
			if ( $resp = CCart::getAttributesAll($cart_uuid,array('tips','transaction_type')) ){				
				$tips = isset($resp['tips'])?floatval($resp['tips']):0;
				$transaction_type = isset($resp['transaction_type'])?$resp['transaction_type']:'';				
			}
						
			$this->code = 1; $this->msg = "OK";
			$this->details = array(
			  'transaction_type'=>$transaction_type,
			  'tips'=>$tips,
			  'data'=>$data
			);
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	// public function actioncheckoutAddTips()
	// {
	// 	$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
	// 	$value = isset($this->data['value'])?floatval($this->data['value']):0;		
	// 	try {
			
	// 		$merchant_id = CCart::getMerchantId($cart_uuid);
	// 		CCart::savedAttributes($cart_uuid,'tips',$value);	
	// 		$this->code = 1; $this->msg = "OK";
	// 		$this->details = array(
	// 		  'tips'=>$value,			  
	// 		);
			
	// 	} catch (Exception $e) {
	// 	    $this->msg = t($e->getMessage());
	// 	}
	// 	$this->responseJson();
	// }

	public function actioncheckoutAddTips()
	{
		try {

			$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
		    $value = isset($this->data['value'])?floatval($this->data['value']):0;		
		    $is_manual = isset($this->data['is_manual'])?trim($this->data['is_manual']):false;		

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
					$this->msg = t($e->getMessage());
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
		$this->responseJson();
	}
	
	public function actionPaymentList()
	{
		try {
			
		   $cart_uuid = Yii::app()->input->post('cart_uuid');
		   //$merchant_id = CCart::getMerchantId($cart_uuid);		   
		   $merchant_id = Yii::app()->merchant->id;
		   $data = CPayments::PaymentList($merchant_id,true);		   
		   
		   $merchants = CMerchantListingV1::getMerchant( $merchant_id );
		   $payments_credentials = CPayments::getPaymentCredentialsPublic($merchant_id,'',$merchants->merchant_type);			
		   
		   $this->code = 1;
		   $this->msg = "ok";
		   $this->details = array(		     
		     'data'=>$data,
			 'credentials'=>$payments_credentials
		   );		   
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
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

	public function actionSavedPaymentList()
	{		
		try {
						
			$default_payment_uuid = '';
			$cart_uuid = Yii::app()->input->post('cart_uuid');
			
			$data_merchant = CCart::getMerchantForCredentials($cart_uuid);			
			$merchant_id = isset($data_merchant['merchant_id'])?$data_merchant['merchant_id']:0;
						
			if($data_merchant['merchant_type']==2){	
				$merchant_id=0;			
			}
			
			$payments_credentials = CPayments::getPaymentCredentialsPublic($merchant_id,'',$data_merchant['merchant_type']);
												
			$model = AR_client_payment_method::model()->find(
			'client_id=:client_id AND as_default=:as_default AND merchant_id=:merchant_id ', 
		    array(
		      ':client_id'=>Yii::app()->user->id,		      
		      ':as_default'=>1,
		      ':merchant_id'=>$merchant_id
		    )); 	
		    if($model){		    	
		    	$default_payment_uuid=$model->payment_uuid;
		    }
		    
			$data = CPayments::SavedPaymentList( Yii::app()->user->id , $data_merchant['merchant_type'] , 
			$data_merchant['merchant_id'] );		
						
			$this->code = 1;
		    $this->msg = "ok";
		    $this->details = array(
		      'default_payment_uuid'=>$default_payment_uuid,
		      'data'=>$data,
			  'credentials'=>$payments_credentials
		    );		    
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actionSetDefaultPayment()
	{			
		try {	
			$payment_uuid = Yii::app()->input->post('payment_uuid');
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
		    $this->msg = t($e->getMessage());		    
		}				
		$this->responseJson();
	}

	public function actionPlaceOrder()
	{
		
		$local_id = isset($this->data['local_id'])?$this->data['local_id']:'';
		$cart_uuid = isset($this->data['cart_uuid'])?trim($this->data['cart_uuid']):'';
		$payment_uuid = isset($this->data['payment_uuid'])?trim($this->data['payment_uuid']):'';
		$payment_change = isset($this->data['payment_change'])?floatval($this->data['payment_change']):0;
		
		$payload = array(
			'items','merchant_info','service_fee',
			'delivery_fee','packaging','tax','tips','checkout','discount','distance',
			'summary','total'
		 );		
		 
		 
		 $unit = Yii::app()->params['settings']['home_search_unit_type']; 
		 $distance = 0; 	    
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

		 	/*CHECK IF MERCHANT IS OPEN*/
		try {
			$merchant_id = CCart::getMerchantId($cart_uuid);	
			
			// CHECK IF MERCHANT HAS DIFFERENT TIMEZONE
			$options_merchant = OptionsTools::find(['merchant_timezone'],$merchant_id);
			$merchant_timezone = isset($options_merchant['merchant_timezone'])?$options_merchant['merchant_timezone']:'';
			if(!empty($merchant_timezone)){
				Yii::app()->timezone = $merchant_timezone;
			}
			
			$date = date("Y-m-d");
			$time_now = date("H:i");

            $attributes_data = CCart::getAttributesAll($cart_uuid,['whento_deliver','delivery_time','delivery_date']);             
						
            $whento_deliver = isset($attributes_data['whento_deliver'])?$attributes_data['whento_deliver']:'';
            if($whento_deliver=="schedule"){
                $date = isset($attributes_data['delivery_date'])?$attributes_data['delivery_date']:$date;
                $att_delivery_time = isset($attributes_data['delivery_time'])?json_decode($attributes_data['delivery_time'],true):false;
                if(is_array($att_delivery_time) && count($att_delivery_time)>=1){                    
                    $time_now  = isset($att_delivery_time['end_time'])?$att_delivery_time['end_time']:$time_now;
                }                
            }

						
			$datetime_to = date("Y-m-d g:i:s a",strtotime("$date $time_now"));
			CMerchantListingV1::checkCurrentTime( date("Y-m-d g:i:s a") , $datetime_to);
			            
			$resp = CMerchantListingV1::checkStoreOpen($merchant_id,$date,$time_now);			
			if($resp['merchant_open_status']<=0){
				$this->msg[] = t("This store is close right now, but you can schedulean order later.");
				$this->responseJson();
			}					
						
			CMerchantListingV1::storeAvailableByID($merchant_id);
			
									
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		    
		    $this->responseJson();
		}	

		try {
			
			if($credentials = CMerchants::MapsConfig(Yii::app()->merchant->id)){
				MapSdk::$map_provider = $credentials['provider'];
				MapSdk::setKeys(array(
				  'google.maps'=>$credentials['key'],
				  'mapbox'=>$credentials['key'],
				));				 
			}
			
			require_once 'get-cart.php';

			
			// GET CLIENT ADDRESS AND SAVE LOCATION NAME / DELIVERY OPTIONS AND INSTRUCSTIONS
			$client_address = AR_client_address::model()->find('place_id=:place_id AND client_id=:client_id', 
		    array(':place_id'=>$local_id,'client_id'=>Yii::app()->user->id)); 
			if($client_address){
				$address_component['location_name']	 = $client_address->location_name;
				$address_component['delivery_options']	 = $client_address->delivery_options;
				$address_component['delivery_instructions']	 = $client_address->delivery_instructions;
				$address_component['address_label']	 = $client_address->address_label;
			}			
			
			$include_utensils = isset($this->data['include_utensils'])?$this->data['include_utensils']:false;
		    $include_utensils = $include_utensils==1?true:false;			
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
				}
				
				$atts = CCart::getAttributesAll($cart_uuid,array('whento_deliver',
				  'promo','promo_type','promo_id','tips','delivery_date','delivery_time'
				));						
				
				$payments = CPayments::getPaymentMethod( $payment_uuid, Yii::app()->user->id );
				$sub_total_less_discount  = CCart::getSubTotal_lessDiscount();				
												
				if(is_array($summary) && count($summary)>=1){	
					foreach ($summary as $summary_item) {						
						switch ($summary_item['type']) {
							case "voucher":
								$total_discount = CCart::cleanNumber($summary_item['raw']);
								break;
						
							case "offers":	
							    $total_discount = CCart::cleanNumber($summary_item['raw']);
							    $offer_total = $total_discount;
							    $total_discount = floatval($total_discount)+ floatval($total_discount);
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
				$model->sub_total = floatval($sub_total);
				$model->sub_total_less_discount = floatval($sub_total_less_discount);
				$model->service_fee = floatval($service_fee);
				$model->small_order_fee = floatval($small_order_fee);
				$model->delivery_fee = floatval($delivery_fee);
				$model->packaging_fee = floatval($packagin_fee);
				$model->tax_type = $tax_type;
				$model->tax = floatval($tax);
				$model->tax_total = floatval($total_tax);				
				$model->courier_tip = floatval($tip);				
				$model->total = floatval($total);
				$model->total_original = floatval($total);				
				
				if(is_array($promo_details) && count($promo_details)>=1){
					if($promo_details['promo_type']=="voucher"){
						$model->promo_code = $promo_details['voucher_name'];
						$model->promo_total = $promo_details['less_amount'];
					} elseif ( $promo_details['promo_type']=="offers" ){						
						$model->offer_discount = $promo_details['less_amount'];
						$model->offer_total = floatval($offer_total);
					}
				}
				
				$model->whento_deliver = isset($atts['whento_deliver'])?$atts['whento_deliver']:'';
				if($model->whento_deliver=="now"){
					$model->delivery_date = CommonUtility::dateNow();
				} else {
					$model->delivery_date = isset($atts['delivery_date'])?$atts['delivery_date']:'';
					$model->delivery_time = isset($atts['delivery_time'])?CCheckout::jsonTimeToSingleTime($atts['delivery_time']):'';
					$model->delivery_time_end = isset($atts['delivery_time'])?CCheckout::jsonTimeToSingleTime($atts['delivery_time'],'end_time'):'';
				}
												
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
				  'cash_change','customer_name','contact_number','contact_email','include_utensils'
				  )
				);

				$metas['payment_change'] = floatval($payment_change);
				
				/*LINE ITEMS*/
				$model->items = $data['items'];				
				$model->meta = $metas;
				$model->address_component = $address_component;
				$model->cart_uuid = $cart_uuid;
				$model->use_currency_code = Price_Formatter::$number_format['currency_code'];
				$model->base_currency_code = Price_Formatter::$number_format['currency_code'];
				$model->exchange_rate = 1;				
				$model->tax_use = $tax_settings;				
				$model->tax_for_delivery = $tax_delivery;
				$model->request_from = "singleapp";
								
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
																		
					$this->code = 1;
					$this->msg = t("Your Order has been place");
					$this->details = array(  
					  'order_uuid' => $model->order_uuid,
					  'cart_uuid'=>$cart_uuid,
					  'redirect'=>$redirect,
					  'payment_code'=>$model->payment_code,
					  'payment_uuid'=>$payment_uuid,
					  'payment_instructions'=>$payment_instructions,		
					  'order_bw'=>$order_bw,
					  //'total'=>floatval($model->total),
					  'total'=>Price_Formatter::convertToRaw($model->total,2),
					  'currency'=>$model->use_currency_code
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
			
		   $order_uuid = Yii::app()->input->post('order_uuid');
		   $merchant_id = COrders::getMerchantId($order_uuid);
		   $merchant_info = COrders::getMerchant($merchant_id,Yii::app()->language);	
		   
		   COrders::getContent($order_uuid);
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

		   $summary = COrders::getSummary();
		   $order_info['summary'] = $summary;
		   
		   $instructions = CTrackingOrder::getInstructions($merchant_id,$order_type);
		   
		   try {
			   $estimation  = CMerchantListingV1::estimationMerchant2([
				'merchant_id'=>$merchant_id,			
				'shipping_type'=>"standard"
			   ]);
		   } catch (Exception $e) {
			   $estimation = '';
		   }

		   $charge_type = OptionsTools::find(array('merchant_delivery_charges_type'),$merchant_id);
		   $charge_type = isset($charge_type['merchant_delivery_charges_type'])?$charge_type['merchant_delivery_charges_type']:'';
		   		   
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
		     'maps_config'=>CMerchants::MapsConfig(Yii::app()->merchant->id,false),
			 'estimation'=>$estimation,
			 'charge_type'=>$charge_type
		   );		   
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());
		}	
		$this->responseJson();
	}

	public function actiongetMapsConfig()
	{
		try {				
			$maps_config = CMerchants::MapsConfig(Yii::app()->merchant->id,false);	
			$maps_config = JWT::encode($maps_config, CRON_KEY, 'HS256');	
			$this->code = 1;
		    $this->msg = "Ok";
			$this->details = array(					      
			   'maps_config'=>$maps_config
			);			
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());
		}	
		$this->responseJson();
	}
		
	public function actiongetReview()
	{				
		try {			
			
			$merchant_id = Yii::app()->merchant->id;
		    $page = isset($this->data['page'])?(integer)$this->data['page']:0;
		    $rows = Yii::app()->input->post('rows'); 	

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
		   $total = $total_rows;
		   if($rows>0){
			  $data = CommonUtility::dataToRow($data,$total>$rows?$rows:$total);
		   }		   

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
          'default_data'=>$default_data,	
          'data'=>$data,          	  
        );			
      } catch (Exception $e) {
          $this->msg = t($e->getMessage());		    		    
      }
      $this->responseJson();		
    }

	public function actiongetSignupSettings()
	{
	   		
		$options = OptionsTools::find([
			'merchant_captcha_enabled','merchant_captcha_site_key','merchant_captcha_lang',
			'merchant_fb_flag','merchant_fb_app_id',
			'merchant_google_login_enabled','merchant_google_client_id','merchant_mobilephone_settings_default_country',
			'merchant_signup_enabled_terms','merchant_signup_terms'
		],Yii::app()->merchant->id);

		$default_country = isset($options['merchant_mobilephone_settings_default_country'])?$options['merchant_mobilephone_settings_default_country']:'';
		$default_data = ClocationCountry::get($default_country);
		
		$options['default_data'] = $default_data;

		$maps_config = '';
		try {
		    $maps_config = CMerchants::MapsConfig(Yii::app()->merchant->id,false);
			$maps_config = JWT::encode($maps_config, CRON_KEY, 'HS256');	
		} catch (Exception $e) {
			$maps_config = '';
		}

		$options['maps_config'] = $maps_config;

		$this->code = 1;
		$this->details = $options;		
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
						
			$digit_code = CommonUtility::generateNumber(3,true);
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

  public function actionverifyCodeSignup()
	{		
		try {
			
			$local_id = isset($this->data['local_id'])?$this->data['local_id']:'';
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
						$this->details = array();
			    		
			    		if($auto_login==1){
			    			$this->msg = t("Login successful");

							//AUTO LOGIN							
							$login=new AR_customer_autologin;
							$login->username = $model->email_address;
							$login->password = $model->password;
							$login->merchant_id = Yii::app()->merchant->id;
							$login->rememberMe = 1;
							if($login->validate() && $login->login() ){						
								
								$user_data = array(
									'first_name'=>Yii::app()->user->first_name,
									'last_name'=>Yii::app()->user->last_name,
									'email_address'=>Yii::app()->user->email_address,
									'contact_number'=>Yii::app()->user->contact_number,
									'avatar'=>Yii::app()->user->avatar,
								);		 
								$payload = [
									'iss'=>Yii::app()->request->getServerName(),
									'sub'=>Yii::app()->merchant->id,
									'aud'=>Yii::app()->merchant->website_url,
									'iat'=>time(),	
									'token'=>Yii::app()->user->logintoken					
								];		

								$settings = AR_client_meta::getMeta2(['app_push_notifications','promotional_push_notifications'],Yii::app()->user->id);					
								$user_settings = [
									'app_push_notifications'=> isset($settings['app_push_notifications'])?$settings['app_push_notifications']:false ,
									'promotional_push_notifications'=>isset($settings['promotional_push_notifications'])?$settings['promotional_push_notifications']:false ,
								];

								$user_data = JWT::encode($user_data, CRON_KEY, 'HS256');
								$jwt_token = JWT::encode($payload, CRON_KEY, 'HS256');        
								$this->details['user_token'] = $jwt_token;
								$this->details['user_data'] = $user_data;		
								$this->details['user_settings'] = $user_settings;	
								
								$this->saveDeliveryAddress($local_id,$model->client_id);
							} 		

							
			    		}
		    		} else $this->msg = CommonUtility::parseError( $model->getErrors() );
		    		
		    	} else $this->msg = t("Invalid verification code");
		    } else $this->msg = t("Records not found");
			
		} catch (Exception $e) {							
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();	
	}
	
	public function actionrequestCode()
	{
		try {
			
		   $client_uuid = Yii::app()->input->post('client_uuid');
		   
		   $model = AR_clientsignup::model()->find('client_uuid=:client_uuid', 
		   array(':client_uuid'=>$client_uuid)); 
		   if($model){
		   	  $digit_code = CommonUtility::generateNumber(3,true);
		   	  $model->mobile_verification_code = $digit_code;
			  $model->scenario = 'resend_otp';
		   	  if($model->save()){			   	  	 	

				   $this->code = 1;
				   $options = OptionsTools::find(['signup_type']);
			       $signup_type = isset($options['signup_type'])?$options['signup_type']:'';
				   if($signup_type=="mobile_phone"){
					$this->msg = t("We sent a code to {{contact_phone}}.",array(
						'{{contact_phone}}'=> CommonUtility::maskEmail($model->contact_phone)
					  ));			          
				   } else {
					$this->msg = t("We sent a code to {{email_address}}.",array(
						'{{email_address}}'=> CommonUtility::maskEmail($model->email_address)
					  ));			          
				   }		   	  	   		           
		   	  } else $this->msg = CommonUtility::parseError($model->getErrors());		   	  
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
			    				    	
			    	$model->status='active';
			    	if ($model->save()){
			    		$this->code = 1;
			    		$this->msg = t("Registration successful");
			    		
			    		$redirect = !empty($next_url)?$next_url:Yii::app()->getBaseUrl(true);
			    		
			    		$this->details = array(
						  'redirect_url'=>$redirect
						);			
						
						//AUTO LOGIN						
						
			    	} else $this->msg = CommonUtility::parseError( $model->getErrors() );
		    	} else $this->msg[] = t("Accout not verified");		    	
		    } else $this->msg[] = t("Records not found");
		} catch (Exception $e) {
		    $this->msg[] = $e->getMessage();		    
		}
		$this->responseJson();	
	}

  public function actionregisterUser()
	{	
		try {
									
			$options = OptionsTools::find(array('merchant_signup_enabled_verification','merchant_captcha_enabled','merchant_captcha_secret'),Yii::app()->merchant->id);						
			$enabled_verification = isset($options['merchant_signup_enabled_verification'])?$options['merchant_signup_enabled_verification']:false;
			$merchant_captcha_secret = isset($options['merchant_captcha_secret'])?$options['merchant_captcha_secret']:'';
			$verification = $enabled_verification==1?true:false;			
			
			$signup_enabled_capcha = isset($options['merchant_captcha_enabled'])?$options['merchant_captcha_enabled']:false;
			$capcha = $signup_enabled_capcha==1?true:false;
		
			$digit_code = CommonUtility::generateNumber(3,true);			
						
			$recaptcha_response = isset($this->data['recaptcha_response'])?$this->data['recaptcha_response']:'';			
			
			$prefix = isset($this->data['mobile_prefix'])?$this->data['mobile_prefix']:'';
			$mobile_number = isset($this->data['mobile_number'])?$this->data['mobile_number']:'';
			$redirect = isset($this->data['redirect'])?$this->data['redirect']:'';
			$next_url = isset($this->data['next_url'])?$this->data['next_url']:'';		
			$local_id = isset($this->data['local_id'])?$this->data['local_id']:'';		
			
			$model=new AR_clientsignup;
			$model->scenario = 'register';
			$model->capcha = $capcha;
			$model->recaptcha_response = $recaptcha_response;
			$model->captcha_secret = $merchant_captcha_secret;
			
			$model->first_name = isset($this->data['first_name'])?$this->data['first_name']:'';
			$model->last_name = isset($this->data['last_name'])?$this->data['last_name']:'';
			$model->email_address = isset($this->data['email_address'])?$this->data['email_address']:'';
			$model->contact_phone = $prefix.$mobile_number;
			$model->password = isset($this->data['password'])?$this->data['password']:'';		
			$password = $model->password;
			$model->cpassword = isset($this->data['cpassword'])?$this->data['cpassword']:'';
			$model->phone_prefix = $prefix;			
			$model->mobile_verification_code = $digit_code;
			$model->merchant_id = Yii::app()->merchant->id;
			$model->social_strategy = SOCIAL_STRATEGY;			
			
			if($verification==1 || $verification==true){
				$model->status='pending';
			}
			
			if ($model->save()){
				$this->code = 1 ;
				
				if($verification==1 || $verification==true){										
					$this->msg = t("We sent a code to {{email_address}}.",array(
						'{{email_address}}'=> CommonUtility::maskEmail($model->email_address)
					));			  
					$this->details = array(
					  'uuid'=>$model->client_uuid,
					  'verify'=>true
					);			
				} else {
					$this->msg = t("Registration successful");				
					$this->details = array(
					  'verify'=>false
					);																
					
					$this->autoLogin($model->email_address,$password);	
					$this->saveDeliveryAddress($local_id,$model->client_id);

				}
			} else {				
				$this->msg = CommonUtility::parseError( $model->getErrors() );
			}		
			
		} catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
	}
	
	private function saveDeliveryAddress($local_id='',$client_id='')
	{
		try {

			$location_details = array();
			$credentials = CMerchants::MapsConfig(Yii::app()->merchant->id);		
			if($credentials){
				MapSdk::$map_provider = $credentials['provider'];
				MapSdk::setKeys(array(
					'google.maps'=>$credentials['key'],
					'mapbox'=>$credentials['key'],
				));
				$location_details = CMaps::locationDetailsNew($local_id);			
			}
			CCheckout::saveDeliveryAddress($local_id , $client_id ,$location_details);

		} catch (Exception $e) {
			//
		}
	}

    public function actionuserLogin()
	{					
		
		$redirect = isset($this->data['redirect'])?$this->data['redirect']:'';
		$local_id = isset($this->data['local_id'])?$this->data['local_id']:'';
		$_POST['AR_customer_login'] = array(
		  'username'=>isset($this->data['username'])?$this->data['username']:'',
		  'password'=>isset($this->data['password'])?$this->data['password']:'',		  
		);		

		$options = OptionsTools::find(array('merchant_captcha_enabled','merchant_captcha_secret'),Yii::app()->merchant->id);		
		$signup_enabled_capcha = isset($options['merchant_captcha_enabled'])?$options['merchant_captcha_enabled']:false;
		$merchant_captcha_secret = isset($options['merchant_captcha_secret'])?$options['merchant_captcha_secret']:'';
		$capcha = $signup_enabled_capcha==1?true:false;
		$recaptcha_response = isset($this->data['recaptcha_response'])?$this->data['recaptcha_response']:'';			
		
		$model=new AR_customer_login;
		$model->attributes=$_POST['AR_customer_login'];		
		$model->capcha = $capcha;		
		$model->recaptcha_response = $recaptcha_response;
		$model->captcha_secret = $merchant_captcha_secret;
		$model->merchant_id = Yii::app()->merchant->id;
			
		if($model->validate() && $model->login() ){
						
			$this->saveDeliveryAddress($local_id, Yii::app()->user->id );

			$user_data = array(
               'first_name'=>Yii::app()->user->first_name,
			   'last_name'=>Yii::app()->user->last_name,
			   'email_address'=>Yii::app()->user->email_address,
			   'contact_number'=>Yii::app()->user->contact_number,
			   'avatar'=>Yii::app()->user->avatar,
			);			
            $payload = [
                'iss'=>Yii::app()->request->getServerName(),
                'sub'=>Yii::app()->merchant->id,
                'aud'=>Yii::app()->merchant->website_url,
                'iat'=>time(),	
                'token'=>Yii::app()->user->logintoken					
            ];					
            $user_data = JWT::encode($user_data, CRON_KEY, 'HS256');
            $jwt_token = JWT::encode($payload, CRON_KEY, 'HS256');  
			
			$settings = AR_client_meta::getMeta2(['app_push_notifications','promotional_push_notifications'],Yii::app()->user->id);					
			$user_settings = [
				'app_push_notifications'=> isset($settings['app_push_notifications'])?$settings['app_push_notifications']:false ,
				'promotional_push_notifications'=>isset($settings['promotional_push_notifications'])?$settings['promotional_push_notifications']:false ,
			];

			$this->code = 1 ;
			$this->msg = t("Login successful");
			$this->details = array(			  
              'user_token'=>$jwt_token,
			  'user_data'=>$user_data,
			  'user_settings'=>$user_settings
			);						
		} else {			
			$this->msg = CommonUtility::parseError( $model->getErrors() );
		}		
		$this->responseJson();
  }

  public function actionauthenticate()
  {
	    $token = Yii::app()->input->post('token');		
		$model = AR_client::model()->find('token=:token',array(':token'=>$token));
		if($model){
			$this->code = 1;
			$this->msg = "ok";
		} else $this->msg = t("Token is not valid");
		
	    $this->responseJson(); 
  }

  public function actionSocialRegister()
	{		
		try {
											
			$digit_code = CommonUtility::generateNumber(3,true);
			$redirect_to = isset($this->data['redirect_to'])?$this->data['redirect_to']:'';
			$email_address = isset($this->data['email_address'])?$this->data['email_address']:'';
			$id = isset($this->data['id'])?$this->data['id']:'';			
			$verification = isset($this->data['verification'])?$this->data['verification']:'';	
			$social_strategy = isset($this->data['social_strategy'])?$this->data['social_strategy']:'';	
			$social_token = isset($this->data['social_token'])?$this->data['social_token']:'';	
			$local_id = isset($this->data['local_id'])?$this->data['local_id']:'';

			$options = OptionsTools::find(['merchant_google_client_id'],Yii::app()->merchant->id);
			$merchant_google_client_id = isset($options['merchant_google_client_id'])?$options['merchant_google_client_id']:'';
												
			$model = AR_clientsignup::model()->find('email_address=:email_address AND merchant_id=:merchant_id', 
		    array(
				':email_address'=>$email_address,
				':merchant_id'=>Yii::app()->merchant->id
			)); 			
		    if(!$model){
		    	$model = new AR_clientsignup;		
		    	$model->scenario = 'registration_social';	
				$model->google_client_id = $merchant_google_client_id; 	
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
				$model->merchant_id = Yii::app()->merchant->id;
			    $model->social_strategy = SOCIAL_STRATEGY;				
		    	
		    	if ($model->save()){			    					    	
		    		$this->SocialRegister($verification,$model,$redirect_to);
					$this->saveDeliveryAddress($local_id,$model->client_id);
		    	} else $this->msg = CommonUtility::parseError( $model->getErrors() );
		    } else {		    					
		    	$model->scenario = 'social_login';		
				$model->google_client_id = $merchant_google_client_id; 	
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
			    		$this->code = 1;
			    		$this->msg = t("Login successful");
						$this->details = array(
						  'redirect'=>!empty($redirect_to)?$redirect_to:Yii::app()->getBaseUrl(true)
						);			
						
						//AUTO LOGIN						
			    		$this->autoLogin($model->email_address, $id );
						$this->saveDeliveryAddress($local_id,Yii::app()->user->id);
						
		    		} else $this->msg = CommonUtility::parseError( $model->getErrors() );
		    	} else $this->msg[] = t("Your account is {{status}}",array('{{status}}'=> t($model->status) ) );
		    }
			
		} catch (Exception $e) {
		    $this->msg = $e->getMessage();		    
		}		
		$this->responseJson();
  }

  private function SocialRegister($verification='',$model ,$redirect_to='')
	{
		$this->code = 1;			
		$redirect='';
				
		if($verification==1){
			// SEND EMAIL CODE			
			$this->msg = t("We sent a code to {{email_address}}.",array(
				'{{email_address}}'=> CommonUtility::maskEmail($model->email_address)
			  ));			           			
			$redirect = [
				'page'=>'verify',
				'uuid'=>$model->client_uuid,
			];	
		} else {						
			$this->msg = t("Login successful");			
			$redirect = [
				'page'=>'complete-registration',
				'uuid'=>$model->client_uuid,
			];		
			//AUTO LOGIN									
		}
		$this->details = array(		    		  
		  'redirect'=>$redirect
		);
  }

  public function actiongetAccountStatus()
  {
	  try {
	      
		  $client_uuid = Yii::app()->input->post('client_uuid');
		  $model = AR_client::model()->find('client_uuid=:client_uuid', 
		  array(':client_uuid'=> $client_uuid )); 				  		  
		  if($model){
			  $data =[
				  'status'=>$model->status,
				  'account_verified'=>$model->account_verified,
				  'social_strategy'=>$model->social_strategy
			  ];
			  $options = OptionsTools::find(['merchant_signup_enabled_verification','merchant_signup_resend_counter'] , Yii::app()->merchant->id );
			  $enabled_verification  = isset($options['merchant_signup_enabled_verification'])?$options['merchant_signup_enabled_verification']:'';
			  $signup_resend_counter  = isset($options['merchant_signup_resend_counter'])?$options['merchant_signup_resend_counter']:20;			  
			  $this->code = 1;
			  $this->msg = t("We sent a code to {{email_address}}.",array(
				'{{email_address}}'=> CommonUtility::maskEmail($model->email_address)
			  ));			           
			  $this->details = [
				  'data'=>$data,
				  'settings'=>[
					'enabled_verification'=>$enabled_verification,
					'signup_resend_counter'=>$signup_resend_counter<=0?20:$signup_resend_counter
				  ]
			  ];
		  } else $this->msg = t("account not found");

	  } catch (Exception $e) {							
		  $this->msg[] = t($e->getMessage());
	  }
	  $this->responseJson();
  }

  public function actiongetCustomerInfo()
	{
		try {
			
			$client_uuid = Yii::app()->input->post('client_uuid');
			$model = AR_clientsignup::model()->find('client_uuid=:client_uuid', 
		    array(':client_uuid'=>$client_uuid)); 
		    if($model){
		    	$this->code = 1;
		    	$this->msg  = "Ok";

				$options = OptionsTools::find(['merchant_mobilephone_settings_default_country','merchant_signup_enabled_terms','merchant_signup_terms'],Yii::app()->merchant->id);				
				$default_country = isset($options['merchant_mobilephone_settings_default_country'])?$options['merchant_mobilephone_settings_default_country']:'';		
				if($countrycode = ClocationCountry::get($default_country)){
					$options['phonecode'] = $countrycode['phonecode'];
				} else $options['phonecode'] = 1;

		    	$this->details = array(
				  'client_uuid'=>$model->client_uuid,
		    	  'firstname'=>$model->first_name,
		    	  'lastname'=>$model->last_name,
		    	  'email_address'=>$model->email_address,
				  'options'=>$options
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
		  $local_id = isset($this->data['local_id'])?$this->data['local_id']:'';
		  $password = isset($this->data['password'])?$this->data['password']:'';
			 
		  $model = AR_clientsignup::model()->find('client_uuid=:client_uuid', 
		  array(':client_uuid'=>$client_uuid)); 
		  if($model){
			  $model->scenario = 'complete_social_registration';			  
			  if($model->account_verified==1){
				  $model->first_name = isset($this->data['firstname'])?$this->data['firstname']:'';
				  $model->last_name = isset($this->data['lastname'])?$this->data['lastname']:'';
				  $model->password = md5($password);
				  $model->cpassword = isset($this->data['cpassword'])? md5($this->data['cpassword']) :'';
				  $model->contact_phone = $prefix.$mobile_number;
				  $model->phone_prefix = $prefix;		    		
				  $model->status='active';					  
				  if ($model->save()){
					  
					  $this->code = 1;
					  $this->msg = t("Registration successful");
					  $this->details = array();
					  					  
					  //AUTO LOGIN								  	  
					  $this->autoLogin($model->email_address,$password);	  
					  $this->saveDeliveryAddress($local_id,$model->client_id);
					  
				  } else $this->msg = CommonUtility::parseError( $model->getErrors() );		    		
			  } else $this->msg[] = t("Accout not verified");	
		  } else $this->msg[] = t("Records not found");			
	  } catch (Exception $e) {
		  $this->msg = $e->getMessage();		    
	  }
	  $this->responseJson();	
  }  

  private function autoLogin($username='',$password='')
  {		
	$login=new AR_customer_login;	
	$login->username = $username;
	$login->password = $password;
	$login->merchant_id = Yii::app()->merchant->id;
	$login->rememberMe = 1;
	if($login->validate() && $login->login() ){
		$user_data = array(
			'first_name'=>Yii::app()->user->first_name,
			'last_name'=>Yii::app()->user->last_name,
			'email_address'=>Yii::app()->user->email_address,
			'contact_number'=>Yii::app()->user->contact_number,
			'avatar'=>Yii::app()->user->avatar,
		);		 
		$payload = [
			'iss'=>Yii::app()->request->getServerName(),
			'sub'=>Yii::app()->merchant->id,
			'aud'=>Yii::app()->merchant->website_url,
			'iat'=>time(),	
			'token'=>Yii::app()->user->logintoken					
		];		

		$settings = AR_client_meta::getMeta2(['app_push_notifications','promotional_push_notifications'],Yii::app()->user->id);					
		$user_settings = [
			'app_push_notifications'=> isset($settings['app_push_notifications'])?$settings['app_push_notifications']:false ,
			'promotional_push_notifications'=>isset($settings['promotional_push_notifications'])?$settings['promotional_push_notifications']:false ,
		];

		$user_data = JWT::encode($user_data, CRON_KEY, 'HS256');
        $jwt_token = JWT::encode($payload, CRON_KEY, 'HS256');        
		$this->details['user_token'] = $jwt_token;
		$this->details['user_data'] = $user_data;		
		$this->details['user_settings'] = $user_settings;
	} //else dump( $login->getErrors() );			
 } 
	
  public function actiongetProfile()
	{
		try {

			$model = AR_client::model()->find('client_id=:client_id', 
		    array(':client_id'=> intval(Yii::app()->user->id) )); 		
			if($model){
				$this->code = 1; $this->msg = "ok";
				$avatar = CMedia::getImage($model->avatar,$model->path,Yii::app()->params->size_image_thumbnail,CommonUtility::getPlaceholderPhoto('customer'));

				$this->details = array(
				  'first_name'=>$model->first_name,
				  'last_name'=>$model->last_name,
				  'email_address'=>$model->email_address,
				  'mobile_prefix'=>$model->phone_prefix,
				  'mobile_number'=>str_replace($model->phone_prefix,"",$model->contact_phone),
				  'avatar'=>$avatar 
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

			$file_data = isset($this->data['file_data'])?$this->data['file_data']:'';
			$image_type = isset($this->data['image_type'])?$this->data['image_type']:'png';
						
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

				if(!empty($file_data)){
					$result = [];
					try {
						$result = CImageUploader::saveBase64Image($file_data,$image_type,"upload/avatar");
						$model->avatar = isset($result['filename'])?$result['filename']:'';
						$model->path = isset($result['path'])?$result['path']:'';
					} catch (Exception $e) {
						$this->msg = t($e->getMessage());
						$this->responseJson();
					}
				} else {
					$featured_filename = isset($this->data['featured_filename'])?$this->data['featured_filename']:'';
					$upload_path = isset($this->data['upload_path'])?$this->data['upload_path']:'';
					if(!empty($featured_filename) && !empty($upload_path) ){
						$model->avatar = $featured_filename;
						$model->path = $upload_path;
					}
				}

		    	$model->first_name = isset($this->data['first_name'])?$this->data['first_name']:'';
		    	$model->last_name = isset($this->data['last_name'])?$this->data['last_name']:'';
		    	$model->email_address = $email_address;
		    	$model->phone_prefix = $mobile_prefix;
		    	$model->contact_phone = $contact_number;
		    	if($model->save()){

					$avatar = CMedia::getImage($model->avatar,$model->path,Yii::app()->params->size_image_thumbnail,
				    CommonUtility::getPlaceholderPhoto('customer'));

					$user_data = array(
						'first_name'=>$model->first_name,
						'last_name'=>$model->last_name,
						'email_address'=>$model->email_address,
						'contact_number'=>$contact_number,
						'avatar'=>$avatar
					);
					$user_data = JWT::encode($user_data, CRON_KEY, 'HS256');

		    		$this->code = 1;
		    		$this->msg = t("Profile updated");					
					$this->details = $user_data;

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
		$code = Yii::app()->input->post('code');		
		$model = AR_client::model()->find('client_id=:client_id', 
		array(':client_id'=> intval(Yii::app()->user->id) )); 	
		if($model){
			if($model->mobile_verification_code==$code){
				$this->code = 1;
				$this->msg = t("Ok");
				$model->status = "deleted";
				$model->save();				
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

  public function actionorderHistory()
	{	     
	     try {
	     	  	     	  
	     	  $page = Yii::app()->input->post('page');
	     	  $q = Yii::app()->input->post('q');
			  $group = Yii::app()->input->post('group');
			  $getreview = Yii::app()->input->post('getreview');
			  $getreview = $getreview==1?true:false;
			  $group_status = COrders::getStatusByGroup($group);			  

			  if($page>0){
				$page = $page-1;
			  }
	     	     	  
	     	  $offset = 0; $show_next_page = false;
	     	  $limit = Yii::app()->params->list_limit;			  
	     	  $total_rows = COrders::orderHistoryTotal(Yii::app()->user->id,Yii::app()->merchant->id,$group_status);    	
	     	  	          
	          $pages = new CPagination($total_rows);
			  $pages->pageSize = $limit;
			  $pages->setCurrentPage($page);
			  $offset = $pages->getOffset();	
			  $page_count = $pages->getPageCount();
			  						
			  if($page_count > ($page+1) ){
				  $show_next_page = true;
			  }   
			  				  			  					  
			  $data = COrders::getOrderHistory(Yii::app()->user->id,$q,$offset,$limit,Yii::app()->language,Yii::app()->merchant->id,$group_status);			  
			  $payment_list = CPayments::DefaultPaymentList(true);	
			  
			  $order_reviewed = [];
			  if($getreview){
				$order_ids = [];			  
				foreach ($data['data'] as $items) {								
					$order_ids[]=$items['order_id_raw'];
				}
				$order_reviewed = CReviews::getUserReviewsByOrder(Yii::app()->user->id,Yii::app()->merchant->id,$order_ids);
			  }			  
			  	          	 	                   	       
	          $this->code = 1;
	          $this->msg = "Ok";	        
	          $this->details = array(
			     'show_next_page'=>$show_next_page,
			     'page'=>intval($page)+1,
			     'data'=>$data,
				 'payment_list'=>$payment_list,
				 'order_reviewed'=>$order_reviewed
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
		     $order_uuid = Yii::app()->input->post('order_uuid');
			 $track_progress = Yii::app()->input->post('track_progress');
			 $track_progress = $track_progress==1?true:false;
			 		     
		     COrders::getContent($order_uuid,Yii::app()->language);
		     $merchant_id = COrders::getMerchantId($order_uuid);
		     $merchant_info = COrders::getMerchant($merchant_id,Yii::app()->language);
		     $items = COrders::getItems();		     
		     $summary = COrders::getSummary();	
		     $order = COrders::orderInfo();		     
		     		     
		     try {
			     $order_id = COrders::getOrderID();		     
			     $refund_transaction = COrders::getPaymentTransactionList(Yii::app()->user->id,$order_id,array(
			       'paid'
			     ),array(
			       'refund',
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
		     
		     $data = array(
		       'merchant'=>$merchant_info,
		       'order'=>$order,
		       'items'=>$items,
		       'summary'=>$summary,	
		       'label'=>$label,
		       'refund_transaction'=>$refund_transaction,
		     );		     

			 $progress = [];
			 if($track_progress){
				$progress = CTrackingOrder::getProgress($order_uuid , date("Y-m-d g:i:s a") );
				unset($progress['customer']);
				unset($progress['merchant']);				
			 }
		    		     		     
		     $this->code = 1; $this->msg = "ok";
		     $this->details = array(			 		      
		       'data'=>$data,		      
			   'progress'=>$progress
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

			$order_uuid = Yii::app()->input->post('order_uuid');
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
			$order_uuid = Yii::app()->input->post('order_uuid');
			$order = COrders::get($order_uuid);
			$resp = COrders::getCancelStatus($order_uuid);			
			
			$cancel = AR_admin_meta::getValue('status_cancel_order');			
			$cancel_status = isset($cancel['meta_value'])?$cancel['meta_value']:'cancelled';
			
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
						$order->remarks = $reason;
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
			$this->details = array(			
			  'delivery_option'=>CCheckout::deliveryOption(),
			  'address_label'=>CCheckout::addressLabel(),			  
			  'default_atts'=>CCheckout::defaultAttrs()
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
			
			$update = false;		
			$address_uuid = isset($this->data['address_uuid'])?trim($this->data['address_uuid']):'';			
			$set_place_id = isset($this->data['set_place_id'])?($this->data['set_place_id']):false;
			$data =  isset($this->data['data'])?$this->data['data']:array();
			
			$model = AR_client_address::model()->find('address_uuid=:address_uuid AND client_id=:client_id', 
		    array(':address_uuid'=>$address_uuid,'client_id'=>Yii::app()->user->id)); 
		    if(!$model){		    	
		    	$model = new AR_client_address;
		    	$model->client_id = intval(Yii::app()->user->id);
		    	$model->address_uuid = CommonUtility::generateUIID();		    	
		    	$model->place_id = isset($data['place_id'])?$data['place_id']:'';
		    	$model->country = isset($data['address']['country'])?$data['address']['country']:'';
		    	$model->country_code = isset($data['address']['country_code'])?$data['address']['country_code']:'';
		    } 
		    
		    $model->location_name = isset($this->data['location_name'])?$this->data['location_name']:'';
	    	$model->delivery_instructions = isset($this->data['delivery_instructions'])?$this->data['delivery_instructions']:'';
	    	$model->delivery_options = isset($this->data['delivery_options'])?$this->data['delivery_options']:'';
	    	$model->address_label = isset($this->data['address_label'])?$this->data['address_label']:'';
	    	$model->latitude = isset($this->data['latitude'])?$this->data['latitude']:'';
	    	$model->longitude = isset($this->data['longitude'])?$this->data['longitude']:'';
	    	$model->address1 = isset($this->data['address1'])?$this->data['address1']:'';
	    	$model->formatted_address = isset($this->data['formatted_address'])?$this->data['formatted_address']:'';
	    	
	    	if($model->save()){
	    		$this->code = 1;
		    	$this->msg = "OK";	
		    	$this->details = array(
		    	  'place_id'=>$model->place_id
		    	);
		    	
		    	if($set_place_id=="true" || $set_place_id==true){
		    		CommonUtility::WriteCookie( Yii::app()->params->local_id ,$model->place_id );  
		    	}
		    	
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
						
			$payment_uuid = Yii::app()->input->post('payment_uuid');
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
		   $payments_credentials = CPayments::getPaymentCredentialsPublic(0,'',2);			
		   $this->code = 1;
		   $this->msg = "OK";		  
		   $this->details = array(
		     'data'=>$data,
			 'credentials'=>$payments_credentials			 
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
			   
			   $model = AR_favorites::model()->find('merchant_id=:merchant_id AND client_id=:client_id', 
		       array(':merchant_id'=>$merchant_id ,'client_id'=> Yii::app()->user->id  )); 		
		       
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
	
	public function actionuploadReview()
	{
		$upload_uuid = CommonUtility::generateUIID();
		$merchant_id = Yii::app()->input->post('merchant_id');
		$allowed_extension = explode(",",  Yii::app()->params['upload_type']);
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
				$this->responseJson();
			}
			if($size>$maxsize){
				$this->msg = t("Invalid file size");
				$this->responseJson();
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
		    $this->msg = t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actionaddTofav()
	{
		try {			
			$item_token = Yii::app()->input->post('item_token');						
			$cat_id = Yii::app()->input->post('cat_id');
			$item = AR_item::model()->find("item_token=:item_token",[
				':item_token'=>$item_token
			]);
			if($item){
				$model = AR_favorites::model()->find("fav_type=:fav_type AND client_id=:client_id 
				AND merchant_id=:merchant_id 
				AND item_id=:item_id
				",[
					':fav_type'=>'item',
					':client_id'=>intval(Yii::app()->user->id),
					':merchant_id'=>intval($item->merchant_id),
					':item_id'=>intval($item->item_id)
				]);
				if($model){
					$model->delete();
					$this->details = array('found'=>false);
				} else {
					$model = new AR_favorites();
				    $model->fav_type='item';
				    $model->client_id = intval(Yii::app()->user->id);
				    $model->merchant_id = intval($item->merchant_id);
					$model->cat_id = intval($cat_id);
					$model->item_id = intval($item->item_id);
					$model->save();
					$mode = 'save';
					$this->details = array('found'=>true);
				}
				$this->code = 1;
				$this->msg = "OK";				
			} else $this->msg = t("Item not found");			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actiongetsaveitems()
	{
		try {

			$merchant_id = Yii::app()->merchant->id;            
			$data = CSavedStore::getSaveItemsByMerchant(Yii::app()->user->id,$merchant_id);			
			$items = CMerchantMenu::getMenu($merchant_id,Yii::app()->language);		
			$this->code = 1;
			$this->msg = "OK";
			$this->details = [
				'data'=>$data,
				'items'=>$items				
			];
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actionstoreAvailable()
	{
		try {		   			
			$merchant_id = Yii::app()->merchant->id;			
			$merchant = CMerchants::get($merchant_id);			
			$merchant_uuid = $merchant->merchant_uuid;
			CMerchantListingV1::storeAvailable($merchant_uuid);			
			$this->code = 1; $this->msg = "ok";
		} catch (Exception $e) {							
		    $this->msg = t($e->getMessage());		    
		}					
		$this->responseJson();
	}

	public function actionmenuSearch()
	{
		try {						
			$q = Yii::app()->input->post('q');
			$merchant_id = Yii::app()->merchant->id;
			$items = CMerchantMenu::getSimilarItems($merchant_id,Yii::app()->language,100,$q);			
			$this->code = 1; $this->msg = "ok";			
			$this->details = [
				'data'=>$items				
			];
		} catch (Exception $e) {							
		    $this->msg = t($e->getMessage());		    
		}					
		$this->responseJson();
	}
	
	public function actiongetRealtime()
	{
		$getevent = Yii::app()->input->post('getevent');
		$realtime = AR_admin_meta::getMeta(array('realtime_app_enabled','realtime_provider',
		'webpush_app_enabled','webpush_provider','pusher_key','pusher_cluster'));						
		$realtime_app_enabled = isset($realtime['realtime_app_enabled'])?$realtime['realtime_app_enabled']['meta_value']:'';
		$realtime_provider = isset($realtime['realtime_provider'])?$realtime['realtime_provider']['meta_value']:'';
		$pusher_key = isset($realtime['pusher_key'])?$realtime['pusher_key']['meta_value']:'';
		$pusher_cluster = isset($realtime['pusher_cluster'])?$realtime['pusher_cluster']['meta_value']:'';

		if($realtime_app_enabled==1){
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'realtime_app_enabled'=>$realtime_app_enabled,
				'realtime_provider'=>$realtime_provider,
				'pusher_key'=>$pusher_key,
				'pusher_cluster'=>$pusher_cluster,
				'channel'=>Yii::app()->user->client_uuid,
				'event'=>$getevent=="tracking"?Yii::app()->params->realtime['event_tracking_order']:Yii::app()->params->realtime['notification_event'],
				'event2'=>[
					'tracking'=>Yii::app()->params->realtime['event_tracking_order'],
					'notification_event'=>Yii::app()->params->realtime['notification_event']
				]
			];
		} else $this->msg = t("realtime not enabled");
		$this->responseJson();
	}

	public function actionsubscribeNews()
	{
		try {
			
			$email_address = Yii::app()->input->post('email_address');
			$model = new AR_subscriber;
			$model->email_address  = $email_address;
			$model->subcsribe_type = 'merchant';
			$model->merchant_id = Yii::app()->merchant->id;
			
			if($model->save()){
				$this->code = 1;
				$this->msg = t("Thank you for subscribing to our newsletter");
			} else {
				$this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
			}

		} catch (Exception $e) {							
		    $this->msg = t($e->getMessage());		    
		}					
		$this->responseJson();
	}

	public function actionSavePlaceByID()
	{
		try {

			$place_id = Yii::app()->input->post('place_id');

			$location_details = array();
			$credentials = CMerchants::MapsConfig(Yii::app()->merchant->id);		
			if($credentials){
				MapSdk::$map_provider = $credentials['provider'];
				MapSdk::setKeys(array(
					'google.maps'=>$credentials['key'],
					'mapbox'=>$credentials['key'],
				));				
				$location_details = CMaps::locationDetailsNew($place_id);			
			}
			
			CCheckout::saveDeliveryAddress($place_id , Yii::app()->user->id,$location_details);		    		
			$this->code = 1; $this->msg = "ok";		
		} catch (Exception $e) {							
		    $this->msg = t($e->getMessage());		    
		}					
		$this->responseJson();
	}

	public function actiongetTipSettings()
	{
		try {

			$cart_uuid = Yii::app()->input->post('cart_uuid');			
			$transaction_type = '';			

			if ( $resp = CCart::getAttributesAll($cart_uuid,array('tips','transaction_type')) ){							
				$transaction_type = isset($resp['transaction_type'])?$resp['transaction_type']:'';				
			}
			
			$tips_settings = OptionsTools::find(['merchant_enabled_tip','tips_in_transactions'],Yii::app()->merchant->id);
			$enabled_tip = isset($tips_settings['merchant_enabled_tip'])?$tips_settings['merchant_enabled_tip']:false;
			$tips_in_transactions = isset($tips_settings['tips_in_transactions'])?json_decode($tips_settings['tips_in_transactions'],true):array();			
			$enabled_tip = false;			
			if(in_array($transaction_type,(array)$tips_in_transactions)){
				$enabled_tip = true;
			}
			$this->code = 1; $this->msg = "OK";
			$this->details = array(			  
			  'enabled_tip'=>$enabled_tip,			  
			);
		} catch (Exception $e) {							
		    $this->msg = t($e->getMessage());		    
		}					
		$this->responseJson();
	}

	public function actioncheckStoreOpen()
	{
		try {
			
			$merchant_id = Yii::app()->merchant->id;
			
			// CHECK IF MERCHANT HAS DIFFERENT TIMEZONE
			$options_merchant = OptionsTools::find(['merchant_timezone'],$merchant_id);			
			$merchant_timezone = isset($options_merchant['merchant_timezone'])?$options_merchant['merchant_timezone']:'';
			if(!empty($merchant_timezone)){
				Yii::app()->timezone = $merchant_timezone;
			}

			$date = date("Y-m-d");
			$time_now = date("H:i");
			
		    $whento_deliver = isset($this->data['whento_deliver'])?$this->data['whento_deliver']:'';						
			if($whento_deliver=="schedule"){
				$date = isset($this->data['delivery_date'])?$this->data['delivery_date']:$date;
				$time_now = isset($this->data['delivery_time'])?$this->data['delivery_time']['start_time']:$time_now;
			}

			$datetime_to = date("Y-m-d g:i:s a",strtotime("$date $time_now"));
			CMerchantListingV1::checkCurrentTime( date("Y-m-d g:i:s a") , $datetime_to);		

			$resp = CMerchantListingV1::checkStoreOpen($merchant_id,$date,$time_now);
			if($resp['merchant_open_status']==1){

				$merchant = CMerchants::get($merchant_id);			
			    $merchant_uuid = $merchant->merchant_uuid;
				CMerchantListingV1::storeAvailable($merchant_uuid);

				$this->code = 1;
				$this->msg = "Ok";
			} else $this->msg = t("This store is close right now, but you can schedulean order later.");
			
		} catch (Exception $e) {							
		    $this->msg = t($e->getMessage());		    
		}					
		$this->responseJson();	
	}

	public function actionrequestResetPassword()
	{
		try {

			$merchant_id = Yii::app()->merchant->id;
			$email_address = Yii::app()->input->post('email_address');			
			$model = AR_clientsignup::model()->find('email_address=:email_address AND merchant_id=:merchant_id', 
		    array(
				':email_address'=>$email_address,
				':merchant_id'=>$merchant_id
			)); 
			if($model){
				if($model->status=="active"){
					$model->scenario = "reset_password";
					$model->mobile_verification_code =  CommonUtility::generateNumber(3,true);
					$model->reset_password_request = 1;
					if($model->save()){
						$this->code = 1;
						$this->msg = t("Check {{email_address}} for an email to reset your password.",array(
						'{{email_address}}'=>$model->email_address
						));				
						$this->details = array(
							'uuid'=>$model->client_uuid
						);		
					} else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
				} else $this->msg = t("Your account is either inactive or not verified.");
			} else $this->msg = t("No email address found in our records. please verify your email.");

		} catch (Exception $e) {							
		    $this->msg = t($e->getMessage());		    
		}					
		$this->responseJson();	
	}

	public function actionresetPassword()
	{
		try {
			
			$uuid = Yii::app()->input->post('uuid');
			$otp = Yii::app()->input->post('otp');
			$newpassword = Yii::app()->input->post('newpassword');
			$confirmpassword = Yii::app()->input->post('confirmpassword');
						
			$model = AR_client::model()->find('client_uuid=:client_uuid', array(':client_uuid'=> $uuid )); 	
			if($model){
				if($model->mobile_verification_code==$otp){
					$model->scenario = "reset_password";
					$model->npassword = $newpassword;
					$model->cpassword = $confirmpassword;
					$model->password = md5($model->npassword);					
					if($model->save()){
						$this->code = 1;
						$this->msg = t("Password change");
						$this->autoLogin($model->email_address,$newpassword);	
					} else $this->msg = CommonUtility::parseError( $model->getErrors() );		   	   
				} else $this->msg = t("Invalid verification code");
			} else $this->msg = t(HELPER_RECORD_NOT_FOUND);
		} catch (Exception $e) {							
		    $this->msg = t($e->getMessage());		    
		}					
		$this->responseJson();	
	}

	public function actionresendResetEmail()
	{
		try {
			
		   $client_uuid =  Yii::app()->input->post('customer_uuid');
		   
		   $model = AR_clientsignup::model()->find('client_uuid=:client_uuid', 
		   array(':client_uuid'=>$client_uuid)); 
		   if($model){		   	  
			  $model->scenario = "reset_password";
		   	  $model->reset_password_request = 1;		    		
		   	  if($model->save()){			   	  	 
		   	  	      	  	   	   	  
		   	  	   $this->code = 1;
		           $this->msg = t("Check {{email_address}} for an email to reset your password.",array(
		    		  '{{email_address}}'=>$model->email_address
		    	   ));

		   	  } else $this->msg = CommonUtility::parseError($model->getErrors());		   	  
		   } else $this->msg = t("Records not found");
		   
		} catch (Exception $e) {							
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();	
	}	

	public function actiondownloadPDF()
	{
		try {

			$order_uuid = Yii::app()->input->get('order_uuid');
			
			COrders::getContent($order_uuid,Yii::app()->language);
		    $merchant_id = COrders::getMerchantId($order_uuid);
		    $merchant_info = COrders::getMerchant($merchant_id,Yii::app()->language);
		    $items = COrders::getItems();		     
		    $total = COrders::getTotal();
		    $summary = COrders::getSummary();	
		    $order = COrders::orderInfo(Yii::app()->language, date("Y-m-d") );		
		    $client_id = $order?$order['order_info']['client_id']:0;		    
		    $customer = COrders::getClientInfo($client_id);
				    
			$site_data = OptionsTools::find(
			array('website_title','website_address','website_contact_phone','website_contact_email')
			);
			
			$site = array(
				'title'=>isset($site_data['website_title'])?$site_data['website_title']:'',
				'address'=>isset($site_data['website_address'])?$site_data['website_address']:'',
				'contact'=>isset($site_data['website_contact_phone'])?$site_data['website_contact_phone']:'',
				'email'=>isset($site_data['website_contact_email'])?$site_data['website_contact_email']:'',		      
			);

			$print_settings = AOrderSettings::getPrintSettings();

			$data = array(		       
				'site'=>$site,
				'merchant'=>$merchant_info,
				'order'=>$order,		       
				'items'=>$items,
				'total'=>Price_Formatter::formatNumber($total),
				'summary'=>$summary,		
				'customer'=>$customer,
				'receipt_logo'=>isset($print_settings['receipt_logo'])?$print_settings['receipt_logo']:'',
				'receipt_footer'=>isset($print_settings['receipt_footer'])?trim($print_settings['receipt_footer']):'',
				'receipt_thank_you'=>isset($print_settings['receipt_thank_you'])?$print_settings['receipt_thank_you']:'',
			);		

			$path = Yii::getPathOfAlias('backend_webroot')."/twig";		    

			$loader = new \Twig\Loader\FilesystemLoader($path);
		    $twig = new \Twig\Environment($loader, [
			    'cache' => $path."/compilation_cache",
			    'debug'=>true
			]);

			$template = $twig->render('print_order.html',$data);

			$dompdf = new Dompdf();
		    $options = $dompdf->getOptions();
		    $options->setChroot(Yii::getPathOfAlias('home_dir'));
		    $options->setDefaultFont('Courier');		    
		    $dompdf->setOptions($options);
		    
		    $dompdf->loadHtml($template);
		    $dompdf->setPaper('A4', 'portrait');
		    $dompdf->render();
		    $dompdf->stream();

		} catch (Exception $e) {							
		    $this->msg = t($e->getMessage());			
		}		
	}

	public function actiongetPage()
	{
		try {
			$slug =  Yii::app()->input->post('slug');			
			$data = PPages::pageDetailsSlug($slug,Yii::app()->language);			
			$this->code = 1;
			$this->msg = "Ok";
			$this->details  = [
				'title'=>$data->title,
				'long_content'=>$data->long_content,
				'meta_title'=>$data->meta_title,
				'meta_description'=>$data->meta_description,
				'meta_keywords'=>$data->meta_keywords,
			];
		} catch (Exception $e) {			
			try {				
			    $data = PPages::getPageBySlug($slug);
				$this->code = 1;
				$this->msg = "Ok";
				$this->details  = [
					'title'=>$data->title,
					'long_content'=>$data->long_content,
					'meta_title'=>$data->meta_title,
					'meta_description'=>$data->meta_description,
					'meta_keywords'=>$data->meta_keywords,
				];				
			} catch (Exception $e) {			
				$this->msg = t($e->getMessage());			
			}					    
		}		
		$this->responseJson();	
	}

	public function actiongetInfo()
	{
		try {
			
			$merchant_id = Yii::app()->merchant->id;			
			$merchant = CMerchants::get($merchant_id);			
			$slug = $merchant->restaurant_slug;
			$data = CMerchantListingV1::getMerchantInfo($slug,Yii::app()->language); 		
			$opening_hours = CMerchantListingV1::openingHours($merchant_id);

			$data['few_words'] = t("Few words about {{restaurant_name}}",[
				'{{restaurant_name}}'=>$merchant->restaurant_name
			]);

			$this->code = 1;
            $this->msg = "ok";

			$today = strtolower(date("l"));
            $open_start=''; $open_end='';

			if(is_array($opening_hours) && count($opening_hours)>=1){
				foreach ($opening_hours as $items) {
				   if($items['day']==$today){
					  $open_start = Date_Formatter::Time($items['start_time']);
					  $open_end = Date_Formatter::Time($items['end_time']);
				   }
				}
			}        

			$data['ratings'] = number_format($data['ratings'],1,'.','');
		    $data['saved_store'] = false;

			$gallery = CMerchantListingV1::getGallery($merchant_id);

			$map_direction='';
			if($credentials = CMerchants::MapsConfig(Yii::app()->merchant->id)){				
				$credentials['map_provider'] = isset($credentials['provider'])?$credentials['provider']:'';												
				$map_direction = CMerchantListingV1::mapDirection($credentials,$data['latitude'],$data['lontitude']);	   					
			}								
			$data['map_direction'] = $map_direction;
			$this->details = [
			   'data'=>$data,
               'open_at'=>t("Open {open} - {end}",['{open}'=>$open_start,'{end}'=>$open_end]),
               'opening_hours'=>$opening_hours,
			   'gallery'=>$gallery,
			];		
		} catch (Exception $e) {							
		    $this->msg = t($e->getMessage());			
		}		
		$this->responseJson();	
	}

	public function actiongetSettings()
	{
		try {

			$merchant_id = Yii::app()->merchant->id;
			$merchant = CMerchants::get($merchant_id);
			
			$cart_uuid = Yii::app()->input->post('cart_uuid');			
			
			$options = OptionsTools::find([
				'merchant_captcha_site_key','merchant_captcha_lang','merchant_mobilephone_settings_default_country',
				'booking_enabled','booking_enabled_capcha','booking_allowed_choose_table','merchant_menu_type','merchant_enabled_language','merchant_default_language',
				'merchant_signup_resend_counter','merchant_addons_use_checkbox','booking_reservation_custom_message','booking_reservation_terms',				
			],Yii::app()->merchant->id);			
			
			$default_country = isset($options['merchant_mobilephone_settings_default_country'])?$options['merchant_mobilephone_settings_default_country']:'us';
		    $phone_data = ClocationCountry::get($default_country);
			$captcha_enabled = isset($options['booking_enabled_capcha'])?$options['booking_enabled_capcha']:false;
			$booking_enabled = isset($options['booking_enabled'])?$options['booking_enabled']:false;
			$allowed_choose_table = isset($options['booking_allowed_choose_table'])?$options['booking_allowed_choose_table']:false;
			$menu_type = isset($options['merchant_menu_type'])?$options['merchant_menu_type']:1;						
			$resend_counter = isset($options['merchant_signup_resend_counter'])?$options['merchant_signup_resend_counter']:20;
			$resend_counter = $resend_counter>0?$resend_counter:20;
			$addons_use_checkbox = isset($options['merchant_addons_use_checkbox'])?$options['merchant_addons_use_checkbox']:false;
			$addons_use_checkbox = $addons_use_checkbox==1?true:false;

			$enabled_language = isset($options['merchant_enabled_language'])?$options['merchant_enabled_language']:false;			
			$enabled_language = $enabled_language==1?true:false;
			$default_language = isset($options['merchant_default_language'])?$options['merchant_default_language']:KMRS_DEFAULT_LANGUAGE;			
			$default_language = !empty($default_language)?$default_language:KMRS_DEFAULT_LANGUAGE;

			$booking_reservation_custom_message = isset($options['booking_reservation_custom_message'])?$options['booking_reservation_custom_message']:'';
			$booking_reservation_terms = isset($options['booking_reservation_terms'])?$options['booking_reservation_terms']:'';
			
			$language_list = AttributesTools::getLanguageList();

			$money_config = array();
			$format = Price_Formatter::$number_format;
			$money_config = [
				'precision' => $format['decimals'],
				'minimumFractionDigits'=>$format['decimals'],
				'decimal' => $format['decimal_separator'],
				'thousands' => $format['thousand_separator'],
				'separator' => $format['thousand_separator'],
				'prefix'=> $format['position']=='left'?$format['currency_symbol']:'',
				'suffix'=> $format['position']=='right'?$format['currency_symbol']:'',
				'prefill'=>true
			];
			
			$merchant_delivery = false;
			$merchant_services = CCheckout::getMerchantTransactionList($merchant_id,Yii::app()->language);	
			if(array_key_exists('delivery',$merchant_services)){
				$merchant_delivery = true;
			}       		

			$status_allowed_cancelled = COrders::getStatusAllowedToCancel();
		    $status_allowed_review = AOrderSettings::getStatus(array('status_delivered','status_completed'));
			
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'merchant_uuid'=>$merchant->merchant_uuid,
				'booking_enabled'=>$booking_enabled==1?true:false,
				'captcha_enabled'=>$captcha_enabled==1?true:false,
				'allowed_choose_table'=>$allowed_choose_table==1?true:false,
				'menu_type'=>$menu_type>0?$menu_type:1,
				'captcha_site_key'=>isset($options['merchant_captcha_site_key'])?$options['merchant_captcha_site_key']:'',
				'captcha_lang'=>isset($options['merchant_captcha_lang'])?$options['merchant_captcha_lang']:'en',
				'phone_data'=>$phone_data,
				'enabled_language'=>$enabled_language,
				'default_lang'=>$default_language,
				'language_list'=>$language_list,
				'resend_counter'=>$resend_counter,
				'money_config'=>$money_config,			
				'merchant_delivery'=>$merchant_delivery,
				'addons_use_checkbox'=>$addons_use_checkbox,
				'status_allowed_cancelled'=>$status_allowed_cancelled,
				'status_allowed_review'=>$status_allowed_review,
				'booking_reservation_custom_message'=>$booking_reservation_custom_message,
				'booking_reservation_terms'=>$booking_reservation_terms				
			];			
		} catch (Exception $e) {							
		    $this->msg = t($e->getMessage());			
		}		
		$this->responseJson();	
	}

    public function actionupdateAvatar()
    {
        try {

			$upload_path = Yii::app()->input->post('upload_path');
			$upload_uuid = CommonUtility::generateUIID();
			$allowed_extension = explode(",",  Yii::app()->params['upload_type']);
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
				$this->responseJson();
				}
				if($size>$maxsize){
				$this->msg = t("Invalid file size");
				$this->responseJson();
				}

				if(empty($upload_path)){
					$upload_path = "upload/avatar";
				}

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

				$this->code = 1; $this->msg = "OK";
				$this->details = array(
					'url_image'=>CMedia::getImage($filename,$upload_path),
					'filename'=>$filename,
					'id'=>$upload_uuid,
					'upload_path'=>$upload_path
				);

			} else $this->msg = t("Invalid file");
        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

	public function actionsaveTransactionType()
	{
		try {

			$cart_uuid = Yii::app()->input->post('cart_uuid');
			$transaction_type = Yii::app()->input->post('transaction_type');
			
			if(empty($cart_uuid)){
				$cart_uuid = CommonUtility::createUUID("{{cart}}",'cart_uuid');
			}

			CCart::savedAttributes($cart_uuid,'transaction_type',$transaction_type);
			$this->code = 1; $this->msg = "OK";
			$this->details = array(			
				'cart_uuid'=>$cart_uuid		  
			);						

		} catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actionsaveTransactionInfo2()
	{
		try {

			$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
			if(empty($cart_uuid)){
				$cart_uuid = CommonUtility::createUUID("{{cart}}",'cart_uuid');
			}			
			
			$whento_deliver = isset($this->data['whento_deliver'])?$this->data['whento_deliver']:'';
			$delivery_date = isset($this->data['delivery_date'])?$this->data['delivery_date']:'';
			$delivery_time = isset($this->data['delivery_time'])?$this->data['delivery_time']:'';

			if($whento_deliver=="schedule"){
				if(empty($delivery_date)){
					$this->msg = t("Delivery date is required");
					$this->responseJson();
				}				
				if(empty($delivery_time)){
					$this->msg = t("Delivery time is required");
					$this->responseJson();
				}				
			}
						
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
			  'cart_uuid'=>$cart_uuid		  
			);						

		} catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actiongetLocationByIp()
	{
		try {

			$ip_address =  isset($_SERVER['HTTP_CLIENT_IP']) 
			? $_SERVER['HTTP_CLIENT_IP'] 
			: (isset($_SERVER['HTTP_X_FORWARDED_FOR']) 
			? $_SERVER['HTTP_X_FORWARDED_FOR'] 
			: $_SERVER['REMOTE_ADDR']);

		    $credentials = CMerchants::MapsConfig(Yii::app()->merchant->id);
		    if(!$credentials){
			   $this->msg = t("No default map provider, check your settings.");
			   $this->responseJson();
	        }
			
			$lat = ''; $lng = ''; $keys='';
			if($ip_address=="127.0.0.1" || $ip_address=="localhost"){								
				$lat = isset($credentials['default_lat'])?$credentials['default_lat']:'';
				$lng = isset($credentials['default_lng'])?$credentials['default_lng']:'';
			} else {

				$options = OptionsTools::find(['merchant_geolocationdb'],Yii::app()->merchant->id);					
				$keys = isset($options['merchant_geolocationdb'])?$options['merchant_geolocationdb']:'';

				if(!empty($keys)){				
					try {					
						$location_data = Geo_geolocationdb::fecth($keys,$ip_address);					
						$lat = $location_data['latitude'];
						$lng = $location_data['longitude'];
					} catch (Exception $e) {
						//
					}				
			    }
			}
					   
		   
		   MapSdk::$map_provider =  $credentials['provider'];
		   MapSdk::setKeys(array(
		     'google.maps'=>$credentials['key'],
		     'mapbox'=>$credentials['key']
		   ));
		   
		   if(MapSdk::$map_provider=="mapbox"){
			   MapSdk::setMapParameters(array(
			    'types'=>"poi",
			    'limit'=>1
			   ));
		   }
		   
		   $resp = MapSdk::reverseGeocoding($lat,$lng);
		   $this->code = 1;
		   $this->msg = "Ok";
		   $this->details = $resp;

		} catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actionsavedCartDetails()
	{
		try {

			$cart_uuid = Yii::app()->input->post('cart_uuid');			
			if(empty($cart_uuid)){
				$cart_uuid = CommonUtility::createUUID("{{cart}}",'cart_uuid');
			}		

			$address_label = Yii::app()->input->post('address_label');
			$delivery_instructions = Yii::app()->input->post('delivery_instructions');
			$delivery_options = Yii::app()->input->post('delivery_options');
			$location_name = Yii::app()->input->post('location_name');

			CCart::savedAttributes($cart_uuid,'address_label',$address_label);
			CCart::savedAttributes($cart_uuid,'delivery_instructions',$delivery_instructions);
			CCart::savedAttributes($cart_uuid,'delivery_options',$delivery_options);
			CCart::savedAttributes($cart_uuid,'location_name',$location_name);

			$this->code = 1; $this->msg = "OK";
			$this->details = array(			  
			  'cart_uuid'=>$cart_uuid		  
			);			
		} catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actiongetCartDetails()
	{
		try {

			$cart_uuid = Yii::app()->input->post('cart_uuid');			
			if(empty($cart_uuid)){
				$cart_uuid = CommonUtility::createUUID("{{cart}}",'cart_uuid');
			}

			$atts = CCart::getAttributesAll($cart_uuid,[
				'address_label','delivery_instructions','delivery_options','location_name'
			]);
			
			$default_atts = CCheckout::defaultAttrs();
			$address_label = $default_atts['address_label']; 
			$delivery_option = $default_atts['delivery_options']; 			
			
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'address_label'=>isset($atts['address_label'])?$atts['address_label']:$address_label,
				'delivery_instructions'=>isset($atts['delivery_instructions'])?$atts['delivery_instructions']:'',
				'delivery_options'=>isset($atts['delivery_options'])?$atts['delivery_options']:$delivery_option,
				'location_name'=>isset($atts['location_name'])?$atts['location_name']:'',
			];
		} catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actionsaveClientAddress2()
	{
		try {
						
			$data = isset($this->data['data'])?$this->data['data']:'';
			$location_name = isset($this->data['location_name'])?$this->data['location_name']:'';
			$delivery_instructions = isset($this->data['delivery_instructions'])?$this->data['delivery_instructions']:'';
			$delivery_options = isset($this->data['delivery_options'])?$this->data['delivery_options']:'';
			$address_label = isset($this->data['address_label'])?$this->data['address_label']:'';
			
			$address = array(); 			
			$address = isset($data['address'])?$data['address']:'';

			$address_uuid = isset($data['address_uuid'])?$data['address_uuid']:'';
			$new_lat = isset($data['latitude'])?$data['latitude']:''; 
			$new_lng = isset($data['longitude'])?$data['longitude']:'';
			$place_id = isset($data['place_id'])?$data['place_id']:'';
			
			$address1 = isset($address['address1'])?$address['address1']:'';
			$address2 = isset($address['address2'])?$address['address2']:'';
			$country = isset($address['country'])?$address['country']:'';
			$country_code = isset($address['country_code'])?$address['country_code']:'';
			$postal_code = isset($address['postal_code'])?$address['postal_code']:'';

			$model = AR_client_address::model()->find("client_id=:client_id AND place_id=:place_id",[
				':client_id'=>Yii::app()->user->id,
				':place_id'=>$place_id
			]);
			if(!$model){
				$model = new AR_client_address;			
			}			

			$model->client_id = Yii::app()->user->id;
			$model->place_id = $place_id; 
			$model->latitude = $new_lat;
			$model->longitude = $new_lng;
			$model->location_name = $location_name;
			$model->delivery_options = $delivery_options;
			$model->delivery_instructions = $delivery_instructions;
			$model->address_label = $address_label;
			$model->formatted_address = isset($this->data['formatted_address'])?$this->data['formatted_address']:'';
			$model->address1 = $address1;
			$model->address2 = $address2;
			$model->country = $country;
			$model->country_code = $country_code;
			$model->postal_code = $postal_code;

			if($model->save()){
				$this->code = 1;
				$this->msg = "OK";
				$this->details = array(
                   'place_id'=>$place_id
				);
			} else {
				$this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
			}
		} catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
	}
	
	public function actionremoveTips()
	{
		try {
			
			$cart_uuid = Yii::app()->input->post('cart_uuid');			
			$model = AR_cart_attributes::model()->find("cart_uuid=:cart_uuid AND meta_name=:meta_name",[
				':cart_uuid'=>$cart_uuid,
				':meta_name'=>"tips"
			]);
			if($model){				
				$model->meta_id = 0;
				$model->save();
			} 
			$this->code = 1;
			$this->msg = "Ok";			
		} catch (Exception $e) {
			$this->msg = $e->getMessage();		    
		}
		$this->responseJson();	
	}

	public function actiongetNotification()
	{
		try {

			$limit = 20;
			$page = intval(Yii::app()->input->post('page'));				
			$page_raw = intval(Yii::app()->input->post('page'));
			if($page>0){
				$page = $page-1;
			}

			$criteria=new CDbCriteria();
			$criteria->condition = "notication_channel=:notication_channel";
			$criteria->params  = array(		  
			':notication_channel'=>Yii::app()->user->client_uuid
			);
			$criteria->order = "date_created DESC";

		    $count=AR_notifications::model()->count($criteria);
			$pages=new CPagination($count);
			$pages->pageSize=$limit;
			$pages->setCurrentPage( $page );        
			$pages->applyLimit($criteria);
			$page_count = $pages->getPageCount();

			if($page>0){
				if($page_raw>$page_count){
					$this->code = 3;
					$this->msg = t("end of results");
					$this->responseJson();
				}
			}			

			$model = AR_notifications::model()->findAll($criteria);
			if($model){		
				$data = [];
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
					'notification_uuid'=>$item->notification_uuid,
					'notification_type'=>$item->notification_type,
					'message'=>t($item->message,(array)$params),
					'date'=>PrettyDateTime::parse(new DateTime($item->date_created)),				  
					'image_type'=>$item->image_type,
					'image'=>$image,
					'url'=>$url
					);
				}	

				$this->code = 1;
				$this->msg = "ok";
				$this->details = [
					'page_raw'=>$page_raw,
					'page_count'=>$page_count,
					'data'=>$data
				];
				
			} else $this->msg = t("No results");

		} catch (Exception $e) {
			$this->msg = t($e->getMessage());		    
		}
		$this->responseJson();
	}	

	public function actiondeleteNotification()
	{
		try {

			$uuid = Yii::app()->input->post('uuid');
			$model = AR_notifications::model()->find("notification_uuid=:notification_uuid",[
				':notification_uuid'=>$uuid
			]);
			if($model){
				$model->delete();
				$this->code = 1;
				$this->msg = "OK";
				$this->details = [];
			} else $this->msg = t("Record not found");
		} catch (Exception $e) {							
		    $this->msg = t($e->getMessage());		    
		}					
		$this->responseJson();
	}

	public function actiondeleteAllNotification()
	{
		try {

			$notification_uuids = isset($this->data['notification_uuids'])?$this->data['notification_uuids']:'';			
			CNotifications::deleteNotifications(Yii::app()->user->client_uuid,$notification_uuids);
			$this->code = 1;
			$this->msg = "Ok";

		} catch (Exception $e) {
			$this->msg = $e->getMessage();		    
		}
		$this->responseJson();	
	}

	public function actiondeleteNotifications()
	{
		try {
			
			CNotifications::deleteByChannel(Yii::app()->user->client_uuid);
			$this->code = 1;
			$this->msg = "Ok";

		} catch (Exception $e) {
			$this->msg = $e->getMessage();		    
		}
		$this->responseJson();	
	}

	public function actiongetReview2()
	{				
		try {			
			
			$limit = Yii::app()->params->list_limit;			
			$page = intval(Yii::app()->input->post('page'));
			$merchant_slug = trim(Yii::app()->input->post('slug'));
			$page_raw = intval(Yii::app()->input->post('page'));
			if($page>0){
				$page = $page-1;
			}
						
			$merchant_id = Yii::app()->merchant->id;

			$criteria=new CDbCriteria();
			$criteria->alias = "a";
			$criteria->select="
			a.review,a.rating,
			concat(b.first_name,' ',b.last_name) as customer_fullname,
			b.avatar as logo, b.path,
			a.date_created,a.as_anonymous,
			(
			select group_concat(meta_name,';',meta_value)
			from {{review_meta}}
			where review_id = a.id
			) as meta,
			
			(
			select group_concat(upload_uuid,';',filename,';',path)
			from {{media_files}}
			where upload_uuid IN (
				select meta_value from {{review_meta}}
				where review_id = a.id
			)
			) as media
			";
			$criteria->join='LEFT JOIN {{client}} b on a.client_id = b.client_id ';
			$criteria->condition = "a.merchant_id=:merchant_id AND a.status =:status AND parent_id = 0";
			$criteria->params = [
				':merchant_id'=>$merchant_id,
				':status'=>'publish'
			];
			$criteria->order = "a.id DESC";

			$count=AR_review::model()->count($criteria);
			$pages=new CPagination($count);
			$pages->pageSize=$limit;
			$pages->setCurrentPage( $page );
			$pages->applyLimit($criteria);
			$page_count = $pages->getPageCount();

			if($page>0){
				if($page_raw>$page_count){
					$this->code = 3;
					$this->msg = t("end of results");
					$this->responseJson();
				}
			}
			
			$dependency = CCacheData::dependency();
			if($model = AR_review::model()->cache(Yii::app()->params->cache, $dependency)->findAll($criteria)){
				$data = array();
				foreach ($model as $items) {
					
					$meta = !empty($items->meta)?explode(",",$items->meta):'';
				    $media = !empty($items->media)?explode(",",$items->media):'';
				
				    $meta_data = array(); $media_data=array();

					if(is_array($media) && count($media)>=1){
						foreach ($media as $media_val) {
							$_media = explode(";",$media_val);
							$media_data[$_media['0']] = array(
							  'filename'=>$_media[1],
							  'path'=>$_media[2],
							);
						}
					}

					if(is_array($meta) && count($meta)>=1){
						foreach ($meta as $meta_value) {
							$_meta = explode(";",$meta_value);						
							if($_meta[0]=="upload_images"){							 
								 if(isset( $media_data[$_meta[1]] )){									 								    
									$meta_data[$_meta[0]][] = CMedia::getImage(
									  $media_data[$_meta[1]]['filename'],
									  $media_data[$_meta[1]]['path']
									);
								 }
							} else $meta_data[$_meta[0]][] = $_meta[1];						
						}
					}

					$data[]=array(
						'review'=>Yii::app()->input->xssClean($items->review),
						'rating'=>intval($items->rating),
						'fullname'=>Yii::app()->input->xssClean($items->customer_fullname),
						'hidden_fullname'=>CommonUtility::mask($items->customer_fullname),				  
						'url_image'=>CMedia::getImage($items->logo,$items->path,Yii::app()->params->size_image,
						 CommonUtility::getPlaceholderPhoto('customer')),
						'as_anonymous'=>intval($items->as_anonymous),
						'meta'=>$meta_data,
						'date_created'=>Date_Formatter::dateTime($items->date_created)
					  );

				}				

				$this->code = 1; $this->msg = "ok";
				$this->details = [
					'page_raw'=>$page_raw,
					'page_count'=>$page_count,
					'data'=>$data
				];
			} else $this->msg = t("No results");			
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		   		   
		}		
		$this->responseJson();
	}	

	public function actionCategoryItems()
	{
		try {

			$limit = Yii::app()->params->list_limit;			
			$page = intval(Yii::app()->input->post('page'));
			$cat_id = trim(Yii::app()->input->post('cat_id'));
			$page_raw = intval(Yii::app()->input->post('page'));
			if($page>0){
				$page = $page-1;
			}
						
			$merchant_id = Yii::app()->merchant->id;
			$lang = Yii::app()->language;
			
			$criteria=new CDbCriteria();	
			$criteria->alias = "a";
			$criteria->select="a.merchant_id,a.item_id, a.item_token,a.photo,a.path,
			b.item_name,a.item_short_description,

			(
			select GROUP_CONCAT(f.size_uuid,';',f.price,';',f.size_name,';',f.discount,';',f.discount_type,';',
			(
			select count(*) from {{view_item_lang_size}}
			where item_id = a.item_id 
			and size_uuid = f.size_uuid
			and CURDATE() >= discount_start and CURDATE() <= discount_end
			),';',f.item_size_id
			)
			
			from {{view_item_lang_size}} f
			where 
			item_id = a.item_id
			and language IN('',".q($lang).")
			) as prices,
			
			(
			select GROUP_CONCAT(cat_id)
			from {{item_relationship_category}}
			where item_id = a.item_id
			) as group_category
					
			";		
			$criteria->condition = "merchant_id = :merchant_id 
			AND status=:status AND available=:available AND b.language=:language";
			
			if($cat_id>0){
				$criteria->condition = " 
				merchant_id = :merchant_id AND status=:status AND available=:available
				AND b.language=:language
				AND
				a.item_id IN (
				select item_id from {{item_relationship_category}}
				where cat_id = ".q($cat_id)."
				)
				";
			}
			
			$criteria->params = array (
			':merchant_id'=>intval($merchant_id),
			':status'=>'publish',
			':available'=>1,
			':language'=>$lang
			);		    
	    
			$criteria->mergeWith(array(
				'join'=>'LEFT JOIN {{item_translation}} b ON a.item_id = b.item_id',				
			));

			$count=AR_item::model()->count($criteria);
			$pages=new CPagination($count);
			$pages->pageSize=$limit;
			$pages->setCurrentPage( $page );
			$pages->applyLimit($criteria);
			$page_count = $pages->getPageCount();

			if($page>0){
				if($page_raw>$page_count){
					$this->code = 3;
					$this->msg = t("end of results");
					$this->responseJson();
				}
			}

			$data = array();

			if($models=AR_item::model()->findAll($criteria)){				
				foreach ($models as $val) {
					$price = array();
        		    $prices = explode(",",$val->prices);
					$group_category = explode(",",$val->group_category);
					if(is_array($prices) && count($prices)>=1){
						foreach ($prices as $pricesval) {
							$sizes = explode(";",$pricesval);							
							$item_price = isset($sizes[1])?(float)$sizes[1]:0;
							$item_discount = isset($sizes[3])?(float)$sizes[3]:0;
							$discount_type = isset($sizes[4])?$sizes[4]:'';
							$discount_valid = isset($sizes[5])?(integer)$sizes[5]:0;						
													
							$price_after_discount=0;
							if($item_discount>0 && $discount_valid>0){
								if($discount_type=="percentage"){
									$price_after_discount = $item_price - (($item_discount/100)*$item_price);
								} else $price_after_discount = $item_price-$item_discount;
							
							} else $item_discount = 0;
							
							$price[] = array(
							  'size_uuid'=>isset($sizes[0])?$sizes[0]:'',
							  'item_size_id'=>isset($sizes[6])?$sizes[6]:'',
							  'price'=>$item_price,
							  'size_name'=>isset($sizes[2])?$sizes[2]:'',
							  'discount'=>$item_discount,
							  'discount_type'=>$discount_type,
							  'price_after_discount'=>$price_after_discount,
							  'pretty_price'=>Price_Formatter::formatNumber($item_price),
							  'pretty_price_after_discount'=>Price_Formatter::formatNumber($price_after_discount),
							);
						}
					}

					$data[$val['item_id']] = array(  
						'item_id'=>$val['item_id'],
						'item_uuid'=>$val['item_token'],
						'item_name'=>stripslashes($val['item_name']),
						'item_description'=>CommonUtility::formatShortText($val['item_short_description'],130),
						'url_image'=>CMedia::getImage($val['photo'],$val['path'],Yii::app()->params->size_image
						,CommonUtility::getPlaceholderPhoto('item')),
						'category_id'=>$cat_id>0?array($cat_id):$group_category,
						'price'=>$price,					  
					  );
					
				} // end foreach

				$this->code = 1; $this->msg = "Ok";
				$this->details = [
					'page_raw'=>$page_raw,
					'page_count'=>$page_count,
					'data'=>$data
				];					

			} else $this->msg = t("No results");				
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());		   		   
		 }		
		 $this->responseJson();
	}

	public function actiongetFeaturedItems()
	{
		try {

			$limit = Yii::app()->params->list_limit;			
			$manual_limit = intval(Yii::app()->input->post('limit'));
			$limit = $manual_limit>0?$manual_limit:$limit;
			$page = intval(Yii::app()->input->post('page'));
			$tag = trim(Yii::app()->input->post('tag'));
			$page_raw = intval(Yii::app()->input->post('page'));
			if($page>0){
				$page = $page-1;
			}
						
			$merchant_id = Yii::app()->merchant->id;
			$lang = Yii::app()->language;
			
			$criteria=new CDbCriteria();	
			$criteria->alias = "a";
			$criteria->select="a.merchant_id,a.item_id, a.item_token,a.photo,a.path,
			b.item_name,a.item_short_description,

			(
			select GROUP_CONCAT(f.size_uuid,';',f.price,';',f.size_name,';',f.discount,';',f.discount_type,';',
			(
			select count(*) from {{view_item_lang_size}}
			where item_id = a.item_id 
			and size_uuid = f.size_uuid
			and CURDATE() >= discount_start and CURDATE() <= discount_end
			),';',f.item_size_id
			)
			
			from {{view_item_lang_size}} f
			where 
			item_id = a.item_id
			and language IN('',".q($lang).")
			) as prices,
			
			(
			select GROUP_CONCAT(cat_id)
			from {{item_relationship_category}}
			where item_id = a.item_id
			) as group_category
					
			";		
			$criteria->condition = "merchant_id = :merchant_id 
			AND status=:status AND available=:available AND b.language=:language";
					
			if(!empty($tag)){
				$criteria->condition = " 
				merchant_id = :merchant_id AND status=:status AND available=:available
				AND b.language=:language
				AND
				a.item_id IN (
					select item_id from {{item_meta}}
					where item_id = a.item_id
					and meta_name='item_featured'
					and meta_id = ".q($tag)."					
				)
				";
			}
			
			$criteria->params = array (
			':merchant_id'=>intval($merchant_id),
			':status'=>'publish',
			':available'=>1,
			':language'=>$lang
			);		    
	    
			$criteria->mergeWith(array(
				'join'=>'LEFT JOIN {{item_translation}} b ON a.item_id = b.item_id',				
			));
			
			$count=AR_item::model()->count($criteria);
			$pages=new CPagination($count);
			$pages->pageSize=$limit;
			$pages->setCurrentPage( $page );
			$pages->applyLimit($criteria);
			$page_count = $pages->getPageCount();

			if($page>0){
				if($page_raw>$page_count){
					$this->code = 3;
					$this->msg = t("end of results");
					$this->responseJson();
				}
			}

			$data = array();

			if($models=AR_item::model()->findAll($criteria)){				
				foreach ($models as $val) {
					$price = array();
        		    $prices = explode(",",$val->prices);
					$group_category = explode(",",$val->group_category);
					if(is_array($prices) && count($prices)>=1){
						foreach ($prices as $pricesval) {
							$sizes = explode(";",$pricesval);							
							$item_price = isset($sizes[1])?(float)$sizes[1]:0;
							$item_discount = isset($sizes[3])?(float)$sizes[3]:0;
							$discount_type = isset($sizes[4])?$sizes[4]:'';
							$discount_valid = isset($sizes[5])?(integer)$sizes[5]:0;						
													
							$price_after_discount=0;
							if($item_discount>0 && $discount_valid>0){
								if($discount_type=="percentage"){
									$price_after_discount = $item_price - (($item_discount/100)*$item_price);
								} else $price_after_discount = $item_price-$item_discount;
							
							} else $item_discount = 0;
							
							$price[] = array(
							  'size_uuid'=>isset($sizes[0])?$sizes[0]:'',
							  'item_size_id'=>isset($sizes[6])?$sizes[6]:'',
							  'price'=>$item_price,
							  'size_name'=>isset($sizes[2])?$sizes[2]:'',
							  'discount'=>$item_discount,
							  'discount_type'=>$discount_type,
							  'price_after_discount'=>$price_after_discount,
							  'pretty_price'=>Price_Formatter::formatNumber($item_price),
							  'pretty_price_after_discount'=>Price_Formatter::formatNumber($price_after_discount),
							);
						}
					}

					$data[$val['item_id']] = array(  
						'item_id'=>$val['item_id'],
						'item_uuid'=>$val['item_token'],
						'item_name'=>stripslashes($val['item_name']),
						'item_description'=>CommonUtility::formatShortText($val['item_short_description'],130),
						'url_image'=>CMedia::getImage($val['photo'],$val['path'],Yii::app()->params->size_image
						,CommonUtility::getPlaceholderPhoto('item')),
						'category_id'=>$group_category,
						'price'=>$price,					  
					  );
					
				} // end foreach

				$this->code = 1; $this->msg = "Ok";
				$this->details = [
					'page_raw'=>$page_raw,
					'page_count'=>$page_count,
					'data'=>$data
				];					

			} else $this->msg = t("No results");				
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());		   		   
		 }		
		 $this->responseJson();
	}

	public function actiongetPage2()
	{
		try {

			$page_id = Yii::app()->input->post('page_id');			
			$option = OptionsTools::find([$page_id],Yii::app()->merchant->id);
			$id = isset($option[$page_id])?$option[$page_id]:0;									
			$data = PPages::pageDetailsByID($id,Yii::app()->language);
			$this->code = 1;
			$this->msg = "Ok";
			$this->details  = $data;

		} catch (Exception $e) {
			$this->msg = t($e->getMessage());		   		   
		}		
		$this->responseJson();
	}

	public function actiongetReviewOrder()
	{
		try {

			$order_uuid = Yii::app()->input->post('order_uuid');
			$order = COrders::get($order_uuid);			
			$model = AR_review::model()->find("client_id=:client_id AND order_id=:order_id",[
				':client_id'=>Yii::app()->user->id,
				':order_id'=>$order->order_id
			]);
			if($model){
				$this->code = 1;
				$this->msg = "Ok";
				$this->details = [
					'rating'=>$model->rating,
					'review'=>$model->review,
					'status'=>$model->status
				];
			} else $this->msg = t("Review not found");
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());		   		   
		}		
		$this->responseJson();
	}

	public function actionregisterDevice()
	{
		try {
			
			$token = isset($this->data['token'])?$this->data['token']:'';
			$device_uiid = isset($this->data['device_uiid'])?$this->data['device_uiid']:'';
			$platform = isset($this->data['platform'])?$this->data['platform']:'';
			
			$model = AR_device::model()->find("device_token = :device_token",[
				':device_token'=>$token
			]);
			if($model){				
				$model->device_uiid = $device_uiid;
				$model->enabled = 1;				
				$model->date_created = CommonUtility::dateNow();
				$model->date_modified = CommonUtility::dateNow();
				$model->ip_address = CommonUtility::userIp();
				if(!$model->save()){
					$this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
					$this->responseJson();	
				}
			} else {				
				$model = new AR_device;		
				$model->user_type = "client";
				$model->user_id = 0;
				$model->platform = $platform;
				$model->device_token = $token;
				$model->device_uiid = $device_uiid;
				$model->enabled = 1;
				$model->date_created = CommonUtility::dateNow();
				$model->ip_address = CommonUtility::userIp();
				if(!$model->save()){
					$this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
					$this->responseJson();	
				}
			}			
			
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = json_encode($_POST);

		} catch (Exception $e) {
		    $this->msg = $e->getMessage();		    
		}
		$this->responseJson();	
	}	
		
	public function actionupdateDevice()
	{
		try {
			
			$token = isset($this->data['token'])?$this->data['token']:'';
			$device_uiid = isset($this->data['device_uiid'])?$this->data['device_uiid']:'';
			$platform = isset($this->data['platform'])?$this->data['platform']:'';
						
			$model = AR_device::model()->find("device_token = :device_token",[
				':device_token'=>$token
			]);
			if($model){				
				$model->device_uiid = $device_uiid;
				$model->user_id = Yii::app()->user->id;
				$model->enabled = 1;				
				$model->date_created = CommonUtility::dateNow();
				$model->date_modified = CommonUtility::dateNow();
				$model->ip_address = CommonUtility::userIp();
				if(!$model->save()){
					$this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
					$this->responseJson();	
				}
			} else {				
				$model = new AR_device;		
				$model->user_type = "client";
				$model->user_id = Yii::app()->user->id;
				$model->platform = $platform;
				$model->device_token = $token;
				$model->device_uiid = $device_uiid;
				$model->enabled = 1;
				$model->date_created = CommonUtility::dateNow();
				$model->ip_address = CommonUtility::userIp();
				if(!$model->save()){
					$this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
					$this->responseJson();	
				}
			}			
			
			$this->code = 1;
			$this->msg = "Ok";			

		} catch (Exception $e) {
		    $this->msg = $e->getMessage();		    
		}
		$this->responseJson();	
	}

	public function actionuserLoginPhone()
	{
		try {

			$local_id = isset($this->data['local_id'])?$this->data['local_id']:'';
			$mobile_number = isset($this->data['mobile_number'])?$this->data['mobile_number']:'';
			$moble_prefix = isset($this->data['moble_prefix'])?$this->data['moble_prefix']:'';
			$password = isset($this->data['password'])?$this->data['password']:'';

			$options = OptionsTools::find(array('merchant_captcha_enabled','merchant_captcha_secret'),Yii::app()->merchant->id);		
			$signup_enabled_capcha = isset($options['merchant_captcha_enabled'])?$options['merchant_captcha_enabled']:false;
			$merchant_captcha_secret = isset($options['merchant_captcha_secret'])?$options['merchant_captcha_secret']:'';
			$capcha = $signup_enabled_capcha==1?true:false;
			$recaptcha_response = isset($this->data['recaptcha_response'])?$this->data['recaptcha_response']:'';		
			
			$model=new AR_customer_login;			
			$model->username = $moble_prefix.$mobile_number;
			$model->password = $password;
			$model->capcha = $capcha;		
			$model->recaptcha_response = $recaptcha_response;
			$model->captcha_secret = $merchant_captcha_secret;
			$model->merchant_id = Yii::app()->merchant->id;

			if($model->validate() && $model->login() ){
				$this->saveDeliveryAddress($local_id, Yii::app()->user->id );
				$user_data = array(
					'first_name'=>Yii::app()->user->first_name,
					'last_name'=>Yii::app()->user->last_name,
					'email_address'=>Yii::app()->user->email_address,
					'contact_number'=>Yii::app()->user->contact_number,
					'avatar'=>Yii::app()->user->avatar,
				 );			
				 $payload = [
					 'iss'=>Yii::app()->request->getServerName(),
					 'sub'=>Yii::app()->merchant->id,
					 'aud'=>Yii::app()->merchant->website_url,
					 'iat'=>time(),	
					 'token'=>Yii::app()->user->logintoken					
				 ];					

				 $settings = AR_client_meta::getMeta2(['app_push_notifications','promotional_push_notifications'],Yii::app()->user->id);					
				 $user_settings = [ 
					'app_push_notifications'=> isset($settings['app_push_notifications'])?$settings['app_push_notifications']:false ,
					'promotional_push_notifications'=>isset($settings['promotional_push_notifications'])?$settings['promotional_push_notifications']:false ,
				 ];

				 $user_data = JWT::encode($user_data, CRON_KEY, 'HS256');
				 $jwt_token = JWT::encode($payload, CRON_KEY, 'HS256');            
	 
				 $this->code = 1 ;
				 $this->msg = t("Login successful");
				 $this->details = array(				   
				   'user_token'=>$jwt_token,
				   'user_data'=>$user_data,
				   'user_settings'=>$user_settings
				 );						
			} else {
				$this->msg = CommonUtility::parseError( $model->getErrors() );
			}
		} catch (Exception $e) {
		    $this->msg = $e->getMessage();		    
		}
		$this->responseJson();	
	}

	public function actionUpdateaccountnotification()
	{
		try {

			$app_push_notifications = Yii::app()->input->post('app_push_notifications');						
			$app_push_notifications = $app_push_notifications=="true"?1:0;									
			AR_client_meta::saveMeta(Yii::app()->user->id,'app_push_notifications',$app_push_notifications); 

			$this->code = 1;
			$this->msg = t("Setting saved");
			$this->details = [
				'app_push_notifications'=>$app_push_notifications==1?true:false,				
			];

		} catch (Exception $e) {
		    $this->msg = $e->getMessage();		    
		}
		$this->responseJson();	
	}
		
}
// end class