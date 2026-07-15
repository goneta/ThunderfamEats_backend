<?php
require 'intervention/vendor/autoload.php';
require 'php-jwt/vendor/autoload.php';
use Intervention\Image\ImageManager;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
require 'dompdf/vendor/autoload.php';
require 'ar-php/vendor/autoload.php';
require 'twig/vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;
use ArPHP\I18N\Arabic;
use Symfony\Component\Translation\Dumper\DumperInterface;

use function Clue\StreamFilter\append;

class TableCommon extends CController{
    public $code=2,$msg,$details,$data;

	public function __construct($id,$module=null){
		parent::__construct($id,$module);				
		// Set the application language if provided by GET, session or cookie
		if(isset($_GET['language'])) {
			Yii::app()->language = $_GET['language'];
			Yii::app()->user->setState('language', $_GET['language']); 
			$cookie = new CHttpCookie('language', $_GET['language']);
			$cookie->expire = time() + (60*60*24*365); // (1 year)
			Yii::app()->request->cookies['language'] = $cookie; 
		} else if (Yii::app()->user->hasState('language')){
			Yii::app()->language = Yii::app()->user->getState('language');			
		} else if(isset(Yii::app()->request->cookies['language'])){
			Yii::app()->language = Yii::app()->request->cookies['language']->value;			
			if(!empty(Yii::app()->language) && strlen(Yii::app()->language)>=10){
				Yii::app()->language = KMRS_DEFAULT_LANGUAGE;
			}
		} else {
			$options = OptionsTools::find(['default_language']);
			$default_language = isset($options['default_language'])?$options['default_language']:'';			
			if(!empty($default_language)){
				Yii::app()->language = $default_language;
			} else Yii::app()->language = KMRS_DEFAULT_LANGUAGE;
		}		
	}
	    
	public function filters()
    {
        return array(
            'accessControl',
        );
    }

    public function accessRules()
	{						
		return array(
			array('deny',			
                 'actions'=>array(
                     'getSettings','Login','saveTabledevice'
                 ),
				 'expression' => array('AppTablesideIdentity','verifyToken')
			 ), 
             array('deny',				
                  'actions'=>array(
                    'getTables','getMerchantSettings','geStoreMenu','getMenuItem','addCartItems','getCart',
					'updateCartItems','sendOrder','requestBilling','preparePayment','PaymentList','PlaceOrder',
					'OrderDetails','callStaffRequest','callStaff',
                 ), 
				 'expression' => array('AppTablesideIdentity','verifyMerchant')
			 ), 
		 );
	}
	
	public function responseJson()
    {
		header("Access-Control-Allow-Origin: *");          
        header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    	header('Content-type: application/json'); 
		$resp=array('code'=>$this->code,'msg'=>$this->msg,'details'=>$this->details);
		echo CJSON::encode($resp);
		Yii::app()->end();
    } 
	
	public function initSettings()
	{	
		$settings = OptionsTools::find(array(
			'website_date_format_new','website_time_format_new','home_search_unit_type','website_timezone_new',
			'captcha_customer_signup','image_resizing','merchant_specific_country','map_provider','site_user_avatar','site_merchant_avatar','site_food_avatar',
			'default_location_lat','default_location_lng','digitalwallet_topup_minimum','digitalwallet_topup_maximum','multicurrency_enabled',
			'multicurrency_enabled_checkout_currency','points_enabled','points_use_thresholds','points_earning_rule','points_earning_points','points_minimum_purchase',
			'points_maximum_purchase','digitalwallet_transaction_limit','enabled_include_utensils','digitalwallet_enabled','chat_enabled',
			'signup_enabled_terms','signup_terms','digitalwallet_enabled_topup','address_format_use'
	    ));

		$settings_merchant = OptionsTools::find(array(			
			'merchant_default_currency'
	    ),Yii::app()->merchant->id);
		
		$merchant_default_currency = isset($settings_merchant['merchant_default_currency'])?$settings_merchant['merchant_default_currency']:'';			
			    		
		Yii::app()->params['settings'] = $settings;

		/*SET TIMEZONE*/
		$timezone = Yii::app()->params['settings']['website_timezone_new'];		
		if (is_string($timezone) && strlen($timezone) > 0){
		   Yii::app()->timeZone=$timezone;		   
		}
				
		if(!empty($merchant_default_currency)){			
			Price_Formatter::init($merchant_default_currency);			
		} else  Price_Formatter::init();				
	}

};
// end class

class ApitableController extends TableCommon
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

	public function actiongetsettings()
	{
		try {
			
			$enabled_language = true;
			$default_language = KMRS_DEFAULT_LANGUAGE;

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
			
			$language_list = AttributesTools::getLanguageList();
			
			$call_staff_menu = [];
			try {
				$call_staff_menu = AOrders::rejectionList('call_staff_menu', Yii::app()->language );
			} catch (Exception $e) {}

			 // REALTIME
			 $realtime = AR_admin_meta::getMeta(array('realtime_app_enabled','realtime_provider',
			 'webpush_app_enabled','webpush_provider','pusher_key','pusher_cluster'));						
			 $realtime_app_enabled = isset($realtime['realtime_app_enabled'])?$realtime['realtime_app_enabled']['meta_value']:'';
			 $realtime_provider = isset($realtime['realtime_provider'])?$realtime['realtime_provider']['meta_value']:'';
			 $pusher_key = isset($realtime['pusher_key'])?$realtime['pusher_key']['meta_value']:'';
			 $pusher_cluster = isset($realtime['pusher_cluster'])?$realtime['pusher_cluster']['meta_value']:'';
 
			 $realtime = [
				 'realtime_app_enabled'=>$realtime_app_enabled,
				 'realtime_provider'=>$realtime_provider,
				 'pusher_key'=>$pusher_key,
				 'pusher_cluster'=>$pusher_cluster,				
			 ];       			 
			 try {
				 $realtime_settings = JWT::encode($realtime, CRON_KEY, 'HS256');
			 } catch (Exception $e) {
				 $realtime_settings = '';
			 }				
		
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'money_config'=>$money_config,
				'enabled_language'=>$enabled_language,
				'default_lang'=>$default_language,
				'language_list'=>$language_list,				
				'call_staff_menu'=>$call_staff_menu,
				'realtime_settings'=>$realtime_settings,
			];
			
		} catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }		
        $this->responseJson();
	}

	public function actiongetMerchantSettings()
	{
		try {

			$merchant_id = Yii::app()->merchant->id;
			
			$services_list = [];
			try {
				$services_list = CServices::getMerchantServices($merchant_id,Yii::app()->language,'','tableside_services');			  			  
			} catch (Exception $e) {
				$services_list = CServices::getMerchantServices($merchant_id,Yii::app()->language,'','services');			  			  
			}	  					

			try {
			    $table_list = CBooking::TableList($merchant_id);
		    } catch (Exception $e) {}

			$this->code = 1;
			$this->msg = "Ok";
			$this->details =[
				'services_list'=>$services_list,			
				'table_list'=>$table_list,
			];
		} catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }		
        $this->responseJson();
	}
	
	public function actionLogin()
	{
		try {
			
			$username = Yii::app()->input->post('username');
			$password = Yii::app()->input->post('password');

			$model=new AR_merchant_login;
			$model->scenario='login';

			$model->username = trim($username);
			$model->password = trim($password);

			if($model->validate() && $model->login() ){
				$this->code = 1;
				$this->msg = t("Login Succesful",[],'tableside');

				$user_data = array(
					'id'=>Yii::app()->merchant->id,
					'merchant_id'=>Yii::app()->merchant->merchant_id,
					'first_name'=>Yii::app()->merchant->first_name,
					'last_name'=>Yii::app()->merchant->last_name,
					'email_address'=>Yii::app()->merchant->email_address,
					'contact_number'=>Yii::app()->merchant->contact_number,
					'avatar'=>Yii::app()->merchant->avatar,					
				);
				$payload = [
					'iss'=>Yii::app()->request->getServerName(),
					'sub'=>Yii::app()->merchant->merchant_uuid,
					'iat'=>time(),					
				];
				
				$user_data = JWT::encode($user_data, CRON_KEY, 'HS256');
                $jwt_token = JWT::encode($payload, CRON_KEY, 'HS256');		
				
				$this->details = array(
					'user_token'=>$jwt_token,
					'user_data'=>$user_data,					
				);				
			} else $this->msg = CommonUtility::parseError( $model->getErrors() );
		} catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }		
        $this->responseJson();
	}

	public function actionsaveTabledevice()
	{
		try {
			
			$table_uuid = Yii::app()->input->post('table_id');
			$device_id = Yii::app()->input->post('device_id');
			$device_info = Yii::app()->input->post('device_info');

			$model = AR_table_device::model()->find("device_id=:device_id",[
				':device_id'=>$device_id
			]);
			if(!$model){
				$model = new AR_table_device();
			}

			$model->table_uuid = $table_uuid;
			$model->device_id = $device_id;
			$model->device_info = $device_info;
			if($model->save()){
				
				$table_list = [];
				try{
				  $table_info = CBooking::getTable($table_uuid);								  
				  $table_list = CBooking::getTableListWithDevice($table_info->merchant_id);		
				} catch (Exception $e) {}

				$this->code = 1;
				$this->msg = t("Setting save",[],'tableside');
				$this->details = [
					'table_list'=>$table_list,				
				];
			} else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors(),"<br/>");
		} catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }		
        $this->responseJson();
	}

	public function actiongetTables()
	{
		try {

			$merchant_id = Yii::app()->merchant->id;
					
			$room_list = [];
 		    $room_list = CommonUtility::getDataToDropDown("{{table_room}}","room_uuid","room_name","WHERE merchant_id=".q($merchant_id)." ","order by sequence asc");                			
		    if(is_array($room_list) && count($room_list)>=1){
			   $room_list = CommonUtility::ArrayToLabelValue($room_list);   
		    }	
			$table_list = [];
			try{
			   $table_list = CBooking::getTableListWithDevice($merchant_id);		
			} catch (Exception $e) {}

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'room_list'=>$room_list,
				'table_list'=>$table_list,
			];
		} catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }		
        $this->responseJson();
	}	

    public function actiongeStoreMenu()
    {
        try {
			
           $currency_code = Yii::app()->input->post('currency_code');	
		   $base_currency = Price_Formatter::$number_format['currency_code'];
		   
		   $multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		   $multicurrency_enabled = $multicurrency_enabled==1?true:false;
		   $exchange_rate = 1;		  

           $currency_code = !empty($currency_code)?$currency_code: (empty($base_currency)?$base_currency:$base_currency) ;
		   // SET CURRENCY
			if(!empty($currency_code) && $multicurrency_enabled){
				Price_Formatter::init($currency_code);
				if($currency_code!=$base_currency){
					$exchange_rate = CMulticurrency::getExchangeRate($base_currency,$currency_code);					
				}
		   }

           CMerchantMenu::setExchangeRate($exchange_rate);

           $merchant_id = Yii::app()->merchant->id;
		   $category = CMerchantMenu::getCategory($merchant_id,Yii::app()->language);				   
		   $items = CMerchantMenu::getMenu($merchant_id,Yii::app()->language);		   		   
		   $items_not_available = CMerchantMenu::getItemAvailability($merchant_id,date("w"),date("H:h:i"));
		   $category_not_available = CMerchantMenu::getCategoryAvailability($merchant_id,date("w"),date("H:h:i"));		
		   $dish = CMerchantMenu::getDish(Yii::app()->language);

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
		
		$merchant_id = Yii::app()->merchant->id;
		$item_uuid = Yii::app()->input->get('item_uuid');		
		$cat_id = intval(Yii::app()->input->get('cat_id'));
		$currency_code = Yii::app()->input->get('currency_code');
		$base_currency = Price_Formatter::$number_format['currency_code'];
				
		try {

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

			CMerchantMenu::setExchangeRate($exchange_rate);

			$items = CMerchantMenu::getMenuItem($merchant_id,$cat_id,$item_uuid,Yii::app()->language);
			$addons = CMerchantMenu::getItemAddonCategory($merchant_id,$item_uuid,Yii::app()->language);
			$addon_items = CMerchantMenu::getAddonItems($merchant_id,$item_uuid,Yii::app()->language);					
			$meta = CMerchantMenu::getItemMeta2($merchant_id, isset($items['item_id'])?$items['item_id']:0 );			
			$meta_details = CMerchantMenu::getMeta($merchant_id,$item_uuid,Yii::app()->language);							

			$items_not_available = CMerchantMenu::getItemAvailability($merchant_id,date("w"),date("H:h:i"));
			$category_not_available = CMerchantMenu::getCategoryAvailability($merchant_id,date("w"),date("H:h:i"));
							
			$data = array(
			  'items'=>$items,
			  'addons'=>$addons,
			  'addon_items'=>$addon_items,
			  'meta'=>$meta,
			  'meta_details'=>$meta_details,
			  'items_not_available'=>$items_not_available,
			  'category_not_available'=>$category_not_available,
			  'dish'=>CMerchantMenu::getDish(Yii::app()->language)
			);
			
			$this->code = 1; $this->msg = "ok";
		    $this->details = array(
		      'next_action'=>"show_item_details",
		      'sold_out_options'=>AttributesTools::soldOutOptions(),
			  'default_sold_out_options'=>[
				  'label'=>t("Go with merchant recommendation",[],'tableside'),
				  'value'=>"substitute"
			  ],
		      'data'=>$data			  
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
		$guest_number = isset($this->data['guest_number'])?(integer)$this->data['guest_number']:0;

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
			CCart::savedAttributes($cart_uuid,"guest_number",$guest_number);						
					  
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

	public function actiongetCart()
	{						
		$merchant_id = Yii::app()->merchant->id;
		$local_id = isset($this->data['local_id'])?trim($this->data['local_id']):'';				
		$cart_uuid = isset($this->data['cart_uuid'])?trim($this->data['cart_uuid']):'';		
		$payload = isset($this->data['payload'])?$this->data['payload']:'';
		$currency_code = isset($this->data['currency_code'])?$this->data['currency_code']:'';			
		$base_currency = AttributesTools::defaultCurrency(false);		
		
		$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		$multicurrency_enabled = $multicurrency_enabled==1?true:false;

		$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';		
		$table_number = isset($this->data['table_number'])?$this->data['table_number']:'';	

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
		$resp_opening = array();
		$transaction_info = array();
		$data_transaction = array();
		$tips_data  = array();
		$enabled_tip = false;
		$enabled_voucher = false;
		$exchange_rate = 1;
		$admin_exchange_rate = 1;
		$points_to_earn = 0; $points_label = '';
		$free_delivery_on_first_order = false;	
		
		try {
						
			// CHECK IF CART BELONGS TO MERCHANT			
			$model_checkcart = AR_cart::model()->find("cart_uuid=:cart_uuid AND merchant_id=:merchant_id",[
				':cart_uuid'=>$cart_uuid,
				':merchant_id'=>$merchant_id,
			]);
			if(!$model_checkcart){
				$this->msg = t("Cart details is not belong to merchant",[],'tableside');
				$this->responseJson();
			}			
			
			if(in_array('distance',(array)$payload)){
				if($credentials = CMerchants::MapsConfig($merchant_id)){
					MapSdk::$map_provider = $credentials['provider'];
					MapSdk::setKeys(array(
					  'google.maps'=>$credentials['key'],
					  'mapbox'=>$credentials['key'],
					));				 
				}
			}			

			$options_merchant = OptionsTools::find(['merchant_timezone','merchant_default_currency','free_delivery_on_first_order'],$merchant_id);						
		    $merchant_default_currency = isset($options_merchant['merchant_default_currency'])?$options_merchant['merchant_default_currency']:'';
			$merchant_default_currency = !empty($merchant_default_currency)?$merchant_default_currency:$base_currency;

			$free_delivery_on_first_order = isset($options_merchant['free_delivery_on_first_order'])?$options_merchant['free_delivery_on_first_order']:false;
			$free_delivery_on_first_order = $free_delivery_on_first_order==1?true:false;			

			$currency_code = !empty($currency_code)?$currency_code: (empty($merchant_default_currency)?$base_currency:$merchant_default_currency) ;			

			$points_enabled = isset(Yii::app()->params['settings']['points_enabled'])?Yii::app()->params['settings']['points_enabled']:false;
		    $points_enabled = $points_enabled==1?true:false;
		    $points_earning_rule = isset(Yii::app()->params['settings']['points_earning_rule'])?Yii::app()->params['settings']['points_earning_rule']:'sub_total';									
			$points_earning_points = isset(Yii::app()->params['settings']['points_earning_points'])?Yii::app()->params['settings']['points_earning_points']:0;	
			$points_minimum_purchase = isset(Yii::app()->params['settings']['points_minimum_purchase'])?Yii::app()->params['settings']['points_minimum_purchase']:0;	
			$points_maximum_purchase = isset(Yii::app()->params['settings']['points_maximum_purchase'])?Yii::app()->params['settings']['points_maximum_purchase']:0;
			$points_activated = false;
			if($meta = AR_merchant_meta::getValue($merchant_id,'loyalty_points')){
				$points_activated = $meta['meta_value']==1?true:false;
			}	

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
			
			try {				
				CClientAddress::getAddress($local_id,Yii::app()->user->id);
				$address_found = true;
			} catch (Exception $e) {
				$address_found = false;
			}

			require_once 'get-cart.php';		
			
			$item_kitchen_status = [];
			if(in_array("kitchen_status",(array)$payload)){									
				//$item_kitchen_status = Ckitchen::getItemStatus($merchant_id,$table_number);				
				$item_kitchen_status = CPos::getItemStatus($model_checkcart->order_reference);
			}
			
						
			if(in_array("send_order",(array)$payload)){
				try {									
					AR_cart::model()->updateAll(array(
						'total' =>$total,					
					), "cart_uuid=:cart_uuid AND table_uuid=:table_uuid",[
						":cart_uuid"=>$cart_uuid,
						":table_uuid"=>$table_number
					]);				
				} catch (Exception $e) {}			
			}			
						
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
			  'points_data'=>[
				'points_enabled'=>$points_enabled,
			    'points_to_earn'=>$points_to_earn,
			    'points_label'=>$points_label,
				'points_activated'=>$points_activated
			  ],
			  'address_found'=>$address_found,
			  'item_kitchen_status'=>$item_kitchen_status,
			  'total'=>$total
		    );				    			
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		
		   $this->details = array('items_count'=>$items_count);	   		   
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

	public function actionsendOrder()
	{

		/*CHECK IF MERCHANT IS OPEN*/
		try {
			
			$merchant_id = Yii::app()->merchant->id;
			
			// CHECK IF MERCHANT HAS DIFFERENT TIMEZONE			
			$options_merchant = OptionsTools::find(['merchant_timezone','booking_enabled','free_delivery_on_first_order'],$merchant_id);
			$merchant_timezone = isset($options_merchant['merchant_timezone'])?$options_merchant['merchant_timezone']:'';			
			
			if(!empty($merchant_timezone)){
				Yii::app()->timezone = $merchant_timezone;
			}
			
			$date = date("Y-m-d");
			$time_now = date("H:i");
            
			$attributes_data = isset($this->data['attributes_data'])?$this->data['attributes_data']:null;										
            $whento_deliver = isset($attributes_data['whento_deliver'])?$attributes_data['whento_deliver']:'';			
            if($whento_deliver=="schedule"){
                $delivery_date = isset($attributes_data['delivery_date'])?$attributes_data['delivery_date']:$date;                
				$delivery_time_data = isset($attributes_data['delivery_time'])?$attributes_data['delivery_time']:false;				
                if(is_array($delivery_time_data) && count($delivery_time_data)>=1){                    
                    $delivery_time  = isset($delivery_time_data['end_time'])?$delivery_time_data['end_time']:$time_now;
                }                
            } else {
				$delivery_date = $date;
				$delivery_time = $time_now;
			}				

			$datetime_to = date("Y-m-d g:i:s a",strtotime("$delivery_date $delivery_time"));
			CMerchantListingV1::checkCurrentTime( date("Y-m-d g:i:s a") , $datetime_to);
			            						
			$resp = CMerchantListingV1::checkStoreOpen($merchant_id,$delivery_date,$delivery_time);			
			if($resp['merchant_open_status']<=0){
				$this->msg[] = t("We are close right now",[],'tableside');
				$this->responseJson();
			}					
						
			CMerchantListingV1::storeAvailableByID($merchant_id);			
									
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		    $this->responseJson();
		}	
		
		$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
		$table_number = isset($this->data['table_number'])?$this->data['table_number']:'';
		
		try {
			CBooking::isTableAvailable($table_number);
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		    $this->responseJson();
		}			

		// GET CART TOTAL		
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
		
		$payload =  [
			"items",
			"merchant_info",
			"service_fee",
			"delivery_fee",
			"packaging",
			"tax",
			"tips",
			"checkout",
			"discount",
			"distance_local_new",
			"summary",
			"total",
			"subtotal",
			"items_count",
			"points",
			"points_discount"
		];

		Yii::app()->user->logout(false);
		require_once 'get-cart.php';  				

		try {
			
			$table_number = isset($this->data['table_number'])?$this->data['table_number']:'';
			$room_uuid = isset($this->data['room_uuid'])?$this->data['room_uuid']:'';
			$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';			
			
			$model = AR_cart::model()->find("cart_uuid=:cart_uuid",[
				":cart_uuid"=>$cart_uuid
			]);
			if($model){			

				$table_info = CBooking::getTableDetails(Yii::app()->merchant->id,$table_number);
				$room_name = isset($table_info['room_name'])?$table_info['room_name']:'';
				$table_name = isset($table_info['table_name'])?$table_info['table_name']:'';

				if(empty($model->order_reference)){
					$order_reference = CommonUtility::createUniqueTransaction("{{cart}}",'order_reference', Yii::app()->params->tableside_prefix,5 );
				} else $order_reference = $model->order_reference;
				

				$model->scenario = "send_order";   
				$model->room_name = $room_name;   				
				$model->table_name = $table_name;
				$model->merchant_uuid = Yii::app()->merchant->merchant_uuid;
                $model->order_reference = $order_reference;
				$model->table_uuid = $table_number; 
                $model->date_created = CommonUtility::dateNow();               
				$model->transaction_type = $transaction_type;
				$model->total = floatval($total);				
				if($model->save()){

					CCart::savedAttributes($cart_uuid,'timezone',Yii::app()->timezone);
										
					CCart::savedAttributes($cart_uuid,"table_uuid",$table_number);
					CCart::savedAttributes($cart_uuid,"room_uuid",$room_uuid);

					$customer_name = 'Walk-in Customer';
					CCart::savedAttributes($cart_uuid,'customer_name',$customer_name);

					CCart::savedAttributes($cart_uuid,Yii::app()->params->local_transtype,$transaction_type);			       

					AR_cart::model()->updateAll(array(
						'send_order' =>1,		
						'order_reference'=>$order_reference,
						'table_uuid'=>$table_number,
						'transaction_type'=>$transaction_type
					), "cart_uuid=:cart_uuid AND send_order=:send_order",[
						":cart_uuid"=>$cart_uuid,
						":send_order"=>0
					]);									
					
					$this->code = 1;
					$this->msg = t("Your order has been successfully sent to our kitchen.",[],'tableside');
					$this->details = [
						'total'=>$total,
						'transaction_type'=>$model->transaction_type
					];
				} else $this->msg = t(Helper_failed_save);			
			} else $this->msg = t("Cart details not found",[],'tableside');			

		} catch (Exception $e) {
			$this->msg = t($e->getMessage());			
		}		
		$this->responseJson();
	}

	public function actionrequestBilling()
	{
		try {
			
			$table_number = Yii::app()->input->post('table_number');
			$cart_uuid = Yii::app()->input->post('cart_uuid');
			$model = AR_cart::model()->find("cart_uuid=:cart_uuid AND table_uuid=:table_uuid",[
				':cart_uuid'=>$cart_uuid,
				':table_uuid'=>$table_number
			]);
			if($model){

				$table_info = CBooking::getTableDetails(Yii::app()->merchant->id,$table_number);
				$room_name = isset($table_info['room_name'])?$table_info['room_name']:'';
				$table_name = isset($table_info['table_name'])?$table_info['table_name']:'';
								
				$model->scenario = "request_bill";								
				$model->payment_status = 1;
				$model->merchant_uuid = Yii::app()->merchant->merchant_uuid;
				$model->room_name = $room_name;
				$model->table_name = $table_name;
				if($model->save()){
					$this->code = 1;
			        $this->msg = "Ok";
				} else $this->msg = CommonUtility::parseError( $model->getErrors());
			} else $this->msg = t("Order not found",[],'tableside');
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());			
		}		
		$this->responseJson();
	}

	public function actionupdateGuestnumber()
	{
		try {
						
			$cart_uuid = Yii::app()->input->post('cart_uuid');
			$guest_number = Yii::app()->input->post('guest_number');
			CCart::get($cart_uuid);
			CCart::savedAttributes($cart_uuid,"guest_number",$guest_number);
			$this->code = 1;
			$this->msg = "Ok";
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());			
		}		
		$this->responseJson();
	}

	public function actionPaymentList()
	{
		try {			
			
			$merchant_id = Yii::app()->merchant->id;			
			$merchants = CMerchantListingV1::getMerchant( $merchant_id );			
									
			if($merchants->merchant_type==2){
				$merchant_id=0;			
			}

			//$payments_credentials = CPayments::getPaymentCredentialsPublic($merchant_id,'',$merchants->merchant_type);			
			$payments_credentials = CPayments::getPaymentCredentials($merchant_id,'',$merchants->merchant_type);
			$payment_list = CPayments::PaymentList(Yii::app()->merchant->id,true,false,'',true,1);	
			
			$payments_credentials = JWT::encode($payments_credentials, CRON_KEY, 'HS256');            

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'payment_list'=>$payment_list,
				'payments_credentials'=>$payments_credentials
			];									
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());			
		}		
		$this->responseJson();
	}

	public function actioncallStaffRequest($call=false)
	{
		try {
						
			$merchant_id = Yii::app()->merchant->id;
			$data = isset($this->data['data'])?$this->data['data']:'';			
			$table_number = isset($this->data['table_number'])?$this->data['table_number']:'';			
			$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';			
			
			$uuid = CommonUtility::createUUID("{{cart}}",'cart_uuid');
			$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';					
			$cart_uuid = !empty($cart_uuid)?$cart_uuid:$uuid;		

			$request_items = [];
			if(is_array($data) && count($data)>=1){
				foreach ($data as $items) {
					$request_items[] = $items['request_item'];
				}
			}
			
			if(is_array($data) && count($data)>=1){				
				$model = CPos::getTableExistingRequest($merchant_id,$cart_uuid,$table_number,$request_items);
				if($model){										
					$today = date("Y-m-d g:i:s a"); 							
					$deliveryDate = date("Y-m-d g:i:s a",strtotime($model->request_time));				
					$date_diff = CommonUtility::dateDifference($deliveryDate,$today);											
					if(is_array($date_diff) && count($date_diff)>=1){
						$days = isset($date_diff['days'])?$date_diff['days']:0;
						$hours = isset($date_diff['hours'])?$date_diff['hours']:0;
						$minutes = isset($date_diff['minutes'])?$date_diff['minutes']:0;
						if($days<=0 && $hours<=0 && $minutes<=10){
							$remaining = 5-$minutes;
							if($remaining>0){
								$this->msg = t("You already had a request and we already inform our staff. you can request another after {remaining}mins",[
									'{remaining}'=>$remaining
								],'tableside');
								$this->responseJson();
							}							
						}
					}
				}				


				$title = '';
				$table_info = CBooking::getTableDetails(Yii::app()->merchant->id,$table_number);
				$room_name = isset($table_info['room_name'])?$table_info['room_name']:'';
				$table_name = isset($table_info['table_name'])?$table_info['table_name']:'';
				$title = t("Table #{room_name}-{table_name}",[
					'{room_name}'=>$room_name,
					'{table_name}'=>$table_name,
				],'tableside');

				foreach ($data as $items) {
					$model_insert = new AR_customer_request();				
					$model_insert->title = $title;
					$model_insert->room_name = $room_name;
					$model_insert->table_name = $table_name;
					$model_insert->merchant_uuid = Yii::app()->merchant->merchant_uuid;
					$model_insert->merchant_id = Yii::app()->merchant->id;
					$model_insert->cart_uuid = $cart_uuid;
					$model_insert->table_uuid = $table_number;
					$model_insert->transaction_type = $transaction_type;
					$model_insert->timezone = Yii::app()->timeZone;
					$model_insert->request_item = $items['request_item'];
					$model_insert->qty = $items['qty'];
					$model_insert->request_time = CommonUtility::dateNow();					
					$model_insert->save();
				}
								
				$this->code = 1;
				$this->msg = $call?t("We notify out staff when your concern.",[],'tableside'): t("Your request has been successfully sent to our staff.",[],'tableside');
				$this->details = [
					'cart_uuid'=>$cart_uuid
				];

			} else $this->msg = t("No selected request",[],'tableside');
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());			
		}		
		$this->responseJson();
	}	

	public function actioncallStaff()
	{
		try {
			
			$this->data['data'][] = [
				'request_item'=>"Call Staff",
				'qty'=>1
			];			
			$this->actioncallStaffRequest(true);

		} catch (Exception $e) {
			$this->msg = t($e->getMessage());			
		}		
		$this->responseJson();
	}

	public function actionpreparePayment()
	{
		try {
			
			$merchant_id = Yii::app()->merchant->id;			
			$cart_uuid = Yii::app()->input->get("cart_uuid");			
			$table_number = Yii::app()->input->get("table_number");			
			$currency_code = Yii::app()->input->get("currency_code");			
			$base_currency = AttributesTools::defaultCurrency(false);		

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		    $multicurrency_enabled = $multicurrency_enabled==1?true:false;

			$options_merchant = OptionsTools::find(['merchant_default_currency'],$merchant_id);						
		    $merchant_default_currency = isset($options_merchant['merchant_default_currency'])?$options_merchant['merchant_default_currency']:'';
			$merchant_default_currency = !empty($merchant_default_currency)?$merchant_default_currency:$base_currency;

			$currency_code = !empty($currency_code)?$currency_code: (empty($merchant_default_currency)?$base_currency:$merchant_default_currency) ;	
			
			$model = AR_cart::model()->find("merchant_id=:merchant_id AND cart_uuid=:cart_uuid AND table_uuid=:table_uuid",[
				':merchant_id'=>$merchant_id,
				':cart_uuid'=>$cart_uuid,
				':table_uuid'=>$table_number,
			]);
			if($model){
				$total = Price_Formatter::convertToRaw($model->total,2);
				
				$payment_description = t("Payment for Service: Tableside Dining",[],'tableside');

				$merchant = CMerchants::get($merchant_id);
				$name = $merchant->restaurant_name;

				$data = [
					'total'=>$total,	
					'total_pretty'=>Price_Formatter::formatNumber($total),	
					'name'=>$name,
					'payment_description'=>$payment_description,		
					'currency_code'=>$currency_code,
					'payment_reference'=>$cart_uuid,
					'payment_type'=>"tableside",
				];
				$this->code = 1;
				$this->msg = "Ok";
				$this->details = $data;
			} else $this->msg = t("Cart cannot be found",[],'tableside');
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
		$currency_code = isset($this->data['currency_code'])?trim($this->data['currency_code']):'';				
		$base_currency = AttributesTools::defaultCurrency(false);		

		$use_digital_wallet = isset($this->data['use_digital_wallet'])?intval($this->data['use_digital_wallet']):false;
		$use_digital_wallet = $use_digital_wallet==1?true:false;
		
		$room_uuid = isset($this->data['room_uuid'])?trim($this->data['room_uuid']):'';	
		$table_uuid = isset($this->data['table_uuid'])?trim($this->data['table_uuid']):'';	
		$guest_number = isset($this->data['guest_number'])?intval($this->data['guest_number']):0;

		$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		$multicurrency_enabled = $multicurrency_enabled==1?true:false;	
		$enabled_checkout_currency = isset(Yii::app()->params['settings']['multicurrency_enabled_checkout_currency'])?Yii::app()->params['settings']['multicurrency_enabled_checkout_currency']:false;
		$enabled_force = $multicurrency_enabled==true? ($enabled_checkout_currency==1?true:false) :false;		
		
		$transaction_type = isset($this->data['transaction_type'])?trim($this->data['transaction_type']):'';
		$payment_code = isset($this->data['payment_code'])?trim($this->data['payment_code']):'';

		$transaction_id = isset($this->data['transaction_id'])?trim($this->data['transaction_id']):'';
				
		$payload = array(
			'items','merchant_info','service_fee',
			'delivery_fee','packaging','tax','tips','checkout','discount','distance',
			'summary','total','card_fee','points','points_discount','send_order'
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
		 $card_fee = 0;			
		 $exchange_rate = 1;		
		 $exchange_rate_use_currency_to_admin = 1;
		 $exchange_rate_merchant_to_admin = 1; 
		 $exchange_rate_base_customer = 1;
		 $exchange_rate_admin_to_merchant = 1;		
		 $payment_exchange_rate = 1;
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
		 $whento_deliver = 'now';
		 $delivery_date = '';
		 $delivery_time = '';
		 $delivery_time_data = [];
		 $room_name = '';
		 $table_name = '';
		 $payment_name = '';

		
		if($table_info = CBooking::getTableDetails(Yii::app()->merchant->id,$table_uuid)){			
			$room_uuid = isset($table_info['room_uuid'])?$table_info['room_uuid']:'';
			$room_name = isset($table_info['room_name'])?$table_info['room_name']:'';
			$table_name = isset($table_info['table_name'])?$table_info['table_name']:'';
		} else {
			$this->msg = t("Table number not found",[],'tableside');		    
		    $this->responseJson();
		}

		try {
			$payment = CPayments::getPaymentByCode($payment_code);
			$payment_name = $payment->payment_name;
		} catch (Exception $e) {
			$payment_name = $payment_code;
		}		

		/*CHECK IF MERCHANT IS OPEN*/
		try {
			
			$merchant_id = Yii::app()->merchant->id;
			
			// CHECK IF MERCHANT HAS DIFFERENT TIMEZONE			
			$options_merchant = OptionsTools::find(['merchant_timezone','booking_enabled','free_delivery_on_first_order'],$merchant_id);
			$merchant_timezone = isset($options_merchant['merchant_timezone'])?$options_merchant['merchant_timezone']:'';
			$booking_enabled = isset($options_merchant['booking_enabled'])?$options_merchant['booking_enabled']:'';
			$booking_enabled = $booking_enabled==1?true:false;			

			$free_delivery_on_first_order = isset($options_merchant['free_delivery_on_first_order'])?$options_merchant['free_delivery_on_first_order']:false;
			$free_delivery_on_first_order = $free_delivery_on_first_order==1?true:false;

			if(!empty($merchant_timezone)){
				Yii::app()->timezone = $merchant_timezone;
			}
			
			$date = date("Y-m-d");
			$time_now = date("H:i");

            
			$attributes_data = isset($this->data['attributes_data'])?$this->data['attributes_data']:null;										
            $whento_deliver = isset($attributes_data['whento_deliver'])?$attributes_data['whento_deliver']:'';			
            if($whento_deliver=="schedule"){
                $delivery_date = isset($attributes_data['delivery_date'])?$attributes_data['delivery_date']:$date;                
				$delivery_time_data = isset($attributes_data['delivery_time'])?$attributes_data['delivery_time']:false;				
                if(is_array($delivery_time_data) && count($delivery_time_data)>=1){                    
                    $delivery_time  = isset($delivery_time_data['end_time'])?$delivery_time_data['end_time']:$time_now;
                }                
            } else {
				$delivery_date = $date;
				$delivery_time = $time_now;
			}				

			$datetime_to = date("Y-m-d g:i:s a",strtotime("$delivery_date $delivery_time"));
			CMerchantListingV1::checkCurrentTime( date("Y-m-d g:i:s a") , $datetime_to);
			            			
			$resp = CMerchantListingV1::checkStoreOpen($merchant_id,$delivery_date,$delivery_time);			
			if($resp['merchant_open_status']<=0){
				$this->msg[] = t("This store is close right now, but you can schedulean order later.",[],'tableside');
				$this->responseJson();
			}					
						
			CMerchantListingV1::storeAvailableByID($merchant_id);
			
									
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
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
					$exchange_rate_use_currency_to_admin = CMulticurrency::getExchangeRate($merchant_default_currency,$base_currency);
				}						
			}			
						
			CCart::setAdminExchangeRate($exchange_rate_use_currency_to_admin);			
												
			require_once 'get-cart.php';			
			
			// DIGITAL WALLET
			try {
				if($use_digital_wallet){
					$digital_wallet_balance = CDigitalWallet::getAvailableBalance(Yii::app()->user->id);
					$digital_wallet_balance = $digital_wallet_balance*$exchange_rate_admin_to_merchant;					
					$amount_due = CDigitalWallet::canContinueWithWallet($total,$digital_wallet_balance,$payment_uuid);															
					if($amount_due>0){						
						$wallet_use_amount = $digital_wallet_balance;
						$use_partial_payment = true;
					} else {
						$wallet_use_amount = $total;
					}					
				}					
			} catch (Exception $e) {
				$this->msg[] = t($e->getMessage());		    
				$this->responseJson();
			}					
			
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
				}
								
				$atts = CCart::getAttributesAll($cart_uuid,array(
					'promo','promo_type','promo_id','tips'
				));																		
				
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

				$tracking_stats = AR_admin_meta::getMeta([
					'tracking_status_process','tracking_status_completed','tracking_status_delivered'
				]);						
				$tracking_status_completed = isset($tracking_stats['tracking_status_completed'])?$tracking_stats['tracking_status_completed']['meta_value']:'new';            
				$tracking_status_delivered = isset($tracking_stats['tracking_status_delivered'])?$tracking_stats['tracking_status_delivered']['meta_value']:'new';    
				
				$set_status = $transaction_type=="delivery"?$tracking_status_delivered:$tracking_status_completed;

																										
				$model = new AR_ordernew;
				//$model->scenario = $transaction_type;				
				$model->scenario = "tableside_ordering";				
				$model->order_uuid = CommonUtility::generateUIID();
				$model->merchant_id = intval($merchant_id);	
				$model->client_id = intval(Yii::app()->user->id);
				$model->service_code = $transaction_type;
				$model->payment_code = $payment_code;
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
				$model->tax_type = $tax_type;
				$model->tax = floatval($tax);
				$model->tax_total = floatval($total_tax);				
				$model->courier_tip = floatval($tip);				
				$model->total = floatval($total);
				$model->total_original = floatval($total);	
				$model->amount_due = floatval($amount_due);
				$model->wallet_amount = floatval($wallet_use_amount);				
				
				$model->booking_enabled = $booking_enabled;
				$model->room_id = trim($room_uuid);
			    $model->table_id = trim($table_uuid);
				$model->guest_number = $guest_number;				
				
				if(is_array($promo_details) && count($promo_details)>=1){
					if($promo_details['promo_type']=="voucher"){
						$model->promo_code = $promo_details['voucher_name'];
						$model->promo_total = $promo_details['less_amount'];
					} elseif ( $promo_details['promo_type']=="offers" ){						
						$model->offer_discount = $promo_details['less_amount'];
						$model->offer_total = floatval($offer_total);
					}
				}
				
				$model->whento_deliver = $whento_deliver;
				if($model->whento_deliver=="now"){
					$model->delivery_date = CommonUtility::dateNow();
				} else {
					$model->delivery_date = $delivery_date;
					$model->delivery_time = isset($delivery_time_data['start_time'])?$delivery_time_data['start_time']:'';
					$model->delivery_time_end = isset($delivery_time_data['end_time'])?$delivery_time_data['end_time']:'';
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
				  'cash_change','customer_name','contact_number','contact_email','include_utensils','point_discount'
				  )
				);

				$metas['payment_change'] = floatval($payment_change);
				$metas['self_delivery'] = $self_delivery==true?1:0;	
				$metas['points_to_earn'] = floatval($points_to_earn);	
				
				if($transaction_type=="dinein" && $booking_enabled){
					$metas['guest_number'] = $guest_number;
					try {									
						$model_room = CBooking::getRoom($room_uuid); 
						$metas['room_id'] = $model_room->room_id;
					} catch (Exception $e) {}
	
					try {			
						$model_table = CBooking::getTable($table_uuid); 					
						$metas['table_id'] = $model_table->table_id;
					} catch (Exception $e) {}
				}
				
				/*LINE ITEMS*/
				$model->items = $data['items'];				
				$model->meta = $metas;
				$model->address_component = $address_component;
				$model->cart_uuid = $cart_uuid;
				
				$model->base_currency_code = $merchant_default_currency;
				$model->use_currency_code = $currency_code;		
				$model->admin_base_currency = $base_currency;				

				$model->exchange_rate = floatval($exchange_rate_base_customer);				
				$model->exchange_rate_use_currency_to_admin = floatval($exchange_rate_use_currency_to_admin);
				$model->exchange_rate_merchant_to_admin = floatval($exchange_rate_merchant_to_admin);												
				$model->exchange_rate_admin_to_merchant = floatval($exchange_rate_admin_to_merchant);				
							
				$model->tax_use = $tax_settings;				
				$model->tax_for_delivery = $tax_delivery;
				$model->payment_uuid  = $payment_uuid;

				$model->request_from = "tableside";				
								
				$model->status = $set_status;
				$model->payment_status = CPayments::paidStatus();
				$model->cart_uuid = $cart_uuid;		
								
				$model->room_name = $room_name;   				
				$model->table_name = $table_name;
				$model->payment_name = $payment_name;
				$model->merchant_uuid = Yii::app()->merchant->merchant_uuid;				
				
				if($model->save()){					
					
					CBooking::updateTableStatus($merchant_id,$table_uuid);
				
					
					$model_trans = new AR_ordernew_transaction;
					$model_trans->order_id = $model->order_id;
					$model_trans->merchant_id = $model->merchant_id;
					$model_trans->client_id = $model->client_id;
					$model_trans->payment_code = $model->payment_code;
					$model_trans->trans_amount = $model->amount_due>0? $model->amount_due: $model->total;
					$model_trans->currency_code = $model->base_currency_code;
					$model_trans->to_currency_code = $model->use_currency_code;
					$model_trans->exchange_rate = $model->exchange_rate;
					$model_trans->admin_base_currency = $model->admin_base_currency;
					$model_trans->exchange_rate_merchant_to_admin = $model->exchange_rate_merchant_to_admin;
					$model_trans->exchange_rate_admin_to_merchant = $model->exchange_rate_admin_to_merchant;		
					$model_trans->payment_reference = $transaction_id;
					$model_trans->status = CPayments::paidStatus();
					$model_trans->reason = '';
					$model_trans->payment_uuid = $payment_uuid;
					$model_trans->save();
					
					
					$this->code = 1;
					$this->msg = t("Your Order has been place",[],'tableside');		
					$this->details = [
						'order_uuid'=>$model->order_uuid
					];		
				} else {					
					if ( $error = CommonUtility::parseError( $model->getErrors()) ){				
						$this->msg = $error;						
					} else $this->msg = array('invalid error');
				}				
			}		
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}					
		$this->responseJson();
	}

	public function actionOrderDetails()
	{
		try {

			$order_uuid = Yii::app()->input->get('order_uuid');			
			$model = COrders::get($order_uuid);
			Price_Formatter::init($model->use_currency_code);

			$payment_reference = '';

			try {
				$payment = COrders::getPaymentTransaction($model->order_id);   
				$payment_reference = $payment->payment_reference;
			} catch (Exception $e) {}
			
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'order_id'=>$model->order_id,
				'total'=>Price_Formatter::formatNumber($model->total),
				'payment_reference'=>$payment_reference
			];
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}					
		$this->responseJson();
	}

	public function actionclearItems()
	{
		try {

			$merchant_id = Yii::app()->merchant->id;
			$cart_uuid = Yii::app()->input->get("cart_uuid");

			CCart::clearItems($cart_uuid,$merchant_id);
								
			$this->code = 1;
			$this->msg = "Ok";

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}					
		$this->responseJson();
	}

	public function actiongetSendOrders()
	{
		try {
			
			$cart_uuid = Yii::app()->input->get('cart_uuid');
			if($model = CPos::getCartOrderRefence($cart_uuid)){
				$this->code = 1;
				$this->msg = "Ok";
				$this->details = [
					'change_trans'=>$model->change_trans,
					'transaction_type'=>$model->transaction_type
				];
			} else $this->msg = t(HELPER_NO_RESULTS);
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}					
		$this->responseJson();
	}

	public function actionupdateTransactionType()
	{
		try {			
			$cart_uuid = Yii::app()->input->post('cart_uuid');
			$transaction_type = Yii::app()->input->post('transaction_type');			
			if($model = CPos::getCartOrderRefence($cart_uuid)){
				if($model->change_trans>0){
					$this->msg = t("Sorry you cannot change transaction type anymore, you can change only once.",[],'tableside');
					$this->responseJson();
				}				
				$model->transaction_type = $transaction_type;
				$model->change_trans = 1;
				if($model->save()){
					$this->code = 1;
					$this->msg = "Ok";
				} else $this->msg = t(Helper_failed_update);
			} else $this->msg = t("Cart not found",[],'tableside');

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}					
		$this->responseJson();
	}

}
// end class