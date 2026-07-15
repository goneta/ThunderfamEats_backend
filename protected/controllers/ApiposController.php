<?php
require 'dompdf/vendor/autoload.php';
require 'twig/vendor/autoload.php';
require 'firebase-php/vendor/autoload.php';
use Dompdf\Dompdf;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;

class Apimerchantcommon extends CController {

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
                     'posAttributes','categorylist','categoryItems','SearchCustomer','getinitialcustomer','createcustomer','getCart',
                     'setTransType','getMenuItem','addCartItems','removeCartItem','updateCartItems','clearCart','cartSetCustomer',
                     'clientAddresses','getlocationAutocomplete','getLocationDetails','saveCartAddress','clearCustomer','applyDiscount',
                     'applyPromoCode','removePromocode','applyTips','removeTips','applyPoints','removePoints','getPointsthresholds',
                     'addTotal','applyHoldOrder','searchfooditems','submitPOSOrder','orderDetails','getavailablepoints','getHoldorders',
                     'deleteHoldorder','OrderList','saveguestnumber','SendToKitchen','getTableStatus','UpdateTranss','clearNewitems',
                     'getPOSorders','deleteorders','Orderhistoryattributes','posorders','sendReceipt','saveaddress','getNotifications',
                     'getTableneworder','getCustomerRequest','setRequestcompleted','clearNotifications','printusingthermal','reverseGeocoding',
                     'wifiPrintOT','FPPrintOT','finditembarcode','testPrintWifi','PromoCheck'
                 ),
				 'expression' => array('MerchantIdentity','verifyMerchant')
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
			'captcha_customer_signup','image_resizing','merchant_specific_country','map_provider','google_geo_api_key','mapbox_access_token',
			'signup_enabled_verification','mobilephone_settings_default_country','mobilephone_settings_country','website_title','merchant_enabled_registration',
            'merchant_specific_country','registration_terms_condition','registration_program','mt_android_download_url','mt_ios_download_url','mt_app_version_android',
            'mt_app_version_ios','enabled_language_merchant_app','multicurrency_enabled','driver_on_demand_availability','default_location_lat','default_location_lng',
            'points_redemption_policy','points_redeemed_points','points_redeemed_value','points_maximum_redeemed','points_minimum_redeemed','site_food_avatar',
            'points_enabled','points_earning_rule','points_earning_points','points_minimum_purchase','points_maximum_purchase','custom_field_enabled'
	    ));

		Yii::app()->params['settings'] = $settings;

		/*SET TIMEZONE*/
		$timezone = Yii::app()->params['settings']['website_timezone_new'];
		if (is_string($timezone) && strlen($timezone) > 0){
		   Yii::app()->timeZone=$timezone;
		}

        if(!Yii::app()->merchant->isGuest){            
            Yii::app()->params['settings_merchant'] = OptionsTools::find(array(
				'merchant_default_currency','self_delivery','merchant_enabled_continues_alert','enabled_barcode'
			),Yii::app()->merchant->merchant_id);	            
        }        

        $multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		$multicurrency_enabled = $multicurrency_enabled==1?true:false;		
		$merchant_currency = isset(Yii::app()->params['settings_merchant']['merchant_default_currency'])?Yii::app()->params['settings_merchant']['merchant_default_currency']:'';	

        if($multicurrency_enabled && !empty($merchant_currency)){            
            Price_Formatter::init($merchant_currency);	
        } else {
            Price_Formatter::init();           
        }		
	}

}
// end class

class ApiposController extends Apimerchantcommon
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

    public function actionposAttributes()
    {
        try {

            $merchant_id = Yii::app()->merchant->merchant_id;              
            
            $data = []; $payment_code='';
            try {
                $data = CPayments::PaymentList($merchant_id);            
                $payment_code = isset($data[0])? $data[0]['payment_code'] : '';		
            } catch (Exception $e) {                
            }
            
            $transaction_list = []; $transaction_type= ''; $order_status = 'new';
            try {
                $transaction_list = CCheckout::getMerchantTransactionList($merchant_id,Yii::app()->language,'services_pos');		
                $keys = array_keys($transaction_list);
                $firstKey = isset($keys[0])?$keys[0]:'';
                $transaction_type = $firstKey;                                
                //$transaction_type = CCheckout::getFirstTransactionType($merchant_id,Yii::app()->language);
            } catch (Exception $e) {
                try {
                    $transaction_list = CCheckout::getMerchantTransactionList($merchant_id,Yii::app()->language);		
                    $keys = array_keys($transaction_list);
                    $firstKey = isset($keys[0])?$keys[0]:'';
                    $transaction_type = $firstKey;                                
                } catch (Exception $e) {}
            }

            $order_status_list = AttributesTools::getOrderStatus(Yii::app()->language,'order_status',true);		   
		    if($order_status_list){			  
			  $order_status = $order_status_list[0]['value'];
		    }

            $room_list = [];
		    $room_list = CommonUtility::getDataToDropDown("{{table_room}}","room_uuid","room_name","WHERE merchant_id=".q($merchant_id)." ","order by sequence asc");
            
             $additional_list = [                
                'delivery_fee'=>t("Delivery Fee"),
                'courier_tip'=>t("Courier Tips"),
             ];

             $preferred_time = CCheckout::deliveryOptionList();

             $options = OptionsTools::find([
                'website_time_picker_interval','points_use_thresholds','whatsapp_enabled'
             ]);             
             $interval = isset($options['website_time_picker_interval'])?$options['website_time_picker_interval']." mins":'20 mins';		   
             $use_thresholds = isset($options['points_use_thresholds'])?$options['points_use_thresholds']:false;
             $use_thresholds = $use_thresholds==1?true:false;

             $whatsapp_enabled = isset($options['whatsapp_enabled'])?$options['whatsapp_enabled']:false;
             $whatsapp_enabled = $whatsapp_enabled==1?true:false;
             

             // CHECK IF MERCHANT HAS DIFFERENT TIMEZONE
             $options_merchant = OptionsTools::find(['merchant_time_picker_interval','merchant_timezone','merchant_enabled_tip'],$merchant_id);             
             $interval_merchant = isset($options_merchant['merchant_time_picker_interval'])? ( !empty($options_merchant['merchant_time_picker_interval']) ? $options_merchant['merchant_time_picker_interval']." mins" :''):'';
             $interval = !empty($interval_merchant)?$interval_merchant:$interval;
             $merchant_timezone = isset($options_merchant['merchant_timezone'])?$options_merchant['merchant_timezone']:'';
             if(!empty($merchant_timezone)){
                Yii::app()->timezone = $merchant_timezone;
             }
             
             $opening_hours = CMerchantListingV1::openHours($merchant_id,$interval);
             //$opening_hours = CMerchantListingV1::openHours2($merchant_id,$interval);             

             $enabled_tip = isset($options_merchant['merchant_enabled_tip'])?$options_merchant['merchant_enabled_tip']:false;
             $enabled_tip = $enabled_tip==1?true:false;

             
             $delivery_option = CommonUtility::ArrayToLabelValue(CCheckout::deliveryOption());
             $address_label = CommonUtility::ArrayToLabelValue(CCheckout::addressLabel());
             $custom_field1_data = CommonUtility::ArrayToLabelValue(CCheckout::customeFieldList());             

             $preferred_times = [];
             foreach ($preferred_time as $items) {
                $preferred_times[] = [
                    'label'=>$items['name'],
                    'value'=>strtolower($items['short_name']),
                ];
             }            

             $view_list = CommonUtility::POSviewlist();   
             
             $default_customer[] = array(
                'value'=>"walkin",
                'label'=>t("Walk-in Customer")
            );

            $create_table_link = Yii::app()->createAbsoluteUrl(BACKOFFICE_FOLDER."/booking/tables");    			
            $create_food_link = Yii::app()->createAbsoluteUrl(BACKOFFICE_FOLDER."/food/category");    			

             $printer_list = [];
             try {                
                $printer_list = FPinterface::getPrinterByMerchant($merchant_id);
             } catch (Exception $e) {
                //
             }
             
             $maps_config = CMaps::config();       
             
             $custom_field_enabled = isset(Yii::app()->params['settings']['custom_field_enabled']) ? Yii::app()->params['settings']['custom_field_enabled'] : false;
			 $custom_field_enabled = $custom_field_enabled==1?true:false;

             $enabled_barcode = Yii::app()->params['settings_merchant']['enabled_barcode'] ?? false;
             $enabled_barcode = $enabled_barcode==1?true:false;
                          
             $this->code = 1;
             $this->msg = "ok";
             $this->details = array(		     
                'data'=>$data,
                'default_payment'=>$payment_code,
                'transaction_list'=>$transaction_list,
                'transaction_type'=>$transaction_type,
                'order_status_list'=>$order_status_list,
                'order_status'=>$order_status,
                'room_list'=>$room_list,
                // 'table_list'=>$table_list,
                // 'table_details'=>$table_details,
                'additional_list'=>$additional_list,
                'preferred_time'=>$preferred_time,
                'preferred_times'=>$preferred_times,
                'opening_hours'=>$opening_hours,
                'delivery_option'=>$delivery_option,
				'address_label'=>$address_label,
                'custom_field1_data'=>$custom_field1_data,
                'custom_field_enabled'=>$custom_field_enabled,
                'use_thresholds'=>$use_thresholds,
                'enabled_tip'=>$enabled_tip,
                'view_list'=>$view_list,
                'default_customer'=>$default_customer,
                'create_table_link'=>$create_table_link,
                'create_food_link'=>$create_food_link,
                'whatsapp_enabled'=>$whatsapp_enabled,
                'printer_list'=>$printer_list,
                'maps_config'=>$maps_config,
                'enabled_barcode'=>$enabled_barcode
            );
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }		
        $this->responseJson();  
    }
    
    public function actioncategorylist()
    {
        try {            
            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $category = CMerchantMenu::getCategory($merchant_id,Yii::app()->language);	
            $this->code = 1; $this->msg = "Ok";
            $this->details = [
                'data'=>$category
            ];
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }	
        $this->responseJson();   
    }

    public function actioncategoryItems()
    {
        try {
            
            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $cat_id = Yii::app()->input->get('cat_id');            
            $items = CMerchantMenu::getCategoryItems($cat_id,$merchant_id,Yii::app()->language);            
            $promoItems = CMerchantMenu::getPromoItems($merchant_id);

            if(is_array($promoItems) && count($promoItems)>=1){
				foreach ($items as &$itemss) {			   			   			   
				    $itemss['promo_data'] = $promoItems[$itemss['item_id']] ?? [];
				}
		    }

            $this->code = 1; $this->msg = "Ok";
            $this->details = [
                'data'=>$items                
            ];            
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }	
        $this->responseJson();   
    }

    public function actionSearchCustomer()
    {
        try {

            $data = [];
            $data[] = array(
                'value'=>"walkin",
                'label'=>t("Walk-in Customer")
            );
            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $search = Yii::app()->input->get('q');       
            
            $criteria=new CDbCriteria();
            $criteria->select = "client_id,first_name,last_name";
            $criteria->condition = "status=:status";
            $criteria->params = array(              
              ':status'=>'active'
            );
            $criteria->addNotInCondition("social_strategy",['guest']);
            if(!empty($search)){                
                $criteria->addCondition('first_name LIKE :search OR last_name LIKE :search');
                $criteria->params[':search'] = "%$search%";
            }
            $criteria->limit = 10;             
            if($models = AR_client::model()->findAll($criteria)){		 	
                foreach ($models as $val) {
                    $data[]=array(
                     'value'=>$val->client_id,
                     'label'=>$val->first_name." ".$val->last_name
                   );
                }
            }

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

    public function actiongetinitialcustomer()
    {
        try {

            $data = [];
            $data[] = array(
                'value'=>"walkin",
                'label'=>t("Walk-in Customer")
            );
            $merchant_id = intval(Yii::app()->merchant->merchant_id);

            $criteria=new CDbCriteria();
            $criteria->select = "client_id,first_name,last_name";
            $criteria->condition = "merchant_id=:merchant_id AND  status=:status";
            $criteria->params = array(
              ':merchant_id'=>$merchant_id,
              ':status'=>'active'
            );            
            $criteria->limit = 10;              
            if($models = AR_client::model()->findAll($criteria)){		 	
                foreach ($models as $val) {
                    $data[]=array(
                     'value'=>$val->client_id,
                     'label'=>$val->first_name." ".$val->last_name
                   );
                }
            }

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

    public function actioncreatecustomer()
    {
        try {            
            $model = new AR_client();            
            $model->merchant_id = Yii::app()->merchant->merchant_id;
            $model->first_name =Yii::app()->input->post('first_name');
            $model->last_name =Yii::app()->input->post('last_name');
            $model->email_address =Yii::app()->input->post('email_address');
            $model->contact_phone =Yii::app()->input->post('contact_number');
            if($model->save()){                
                $this->code = 1;
                $this->msg = t("Customer succesfully created");
                $this->details = array(
                  'client_id'=>$model->client_id,
                  'client_uuid'=>$model->client_uuid,
                  'client_name'=>"$model->first_name $model->last_name"
                );
            } else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }		
        $this->responseJson();  
    }

    public function actiongetCart()
    {
        try {

            $cart_uuid = isset($this->data['cart_uuid'])?trim($this->data['cart_uuid']):'';		
            $local_id = isset($this->data['local_id'])?trim($this->data['local_id']):'';
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
            $points_to_earn = 0; $points_label = '';	
            $exchange_rate = 1;
		    $admin_exchange_rate = 1;

            if($table_data = CCart::getAttributesAll($cart_uuid,['guest_number','table_uuid','room_uuid'])){
                $table_uuid = isset($table_data['table_uuid'])?$table_data['table_uuid']:'';       
                try {
                    $table_details = CBooking::getTable($table_uuid);                               
                    $table_data['table_name'] = $table_details->table_name;
                    $table_data['min_covers'] = $table_details->min_covers;
                    $table_data['max_covers'] = $table_details->max_covers;
                } catch (Exception $e) {}  
            } else $table_data = [];

            Yii::app()->user->logout(false);	

            $customer_data = [];           
            $customer_atts = CCart::getAttributesAll($cart_uuid,[
                'customer_name','last_name','first_name','client_id','pos_address','transaction_type'
            ]);

            $client_id = isset($customer_atts['client_id'])?$customer_atts['client_id']:0;
            $customer_name = isset($customer_atts['customer_name'])?$customer_atts['customer_name']:'';
            $last_name = isset($customer_atts['last_name'])?$customer_atts['last_name']:'';
            $first_name = isset($customer_atts['first_name'])?$customer_atts['first_name']:'';            
            $address_component = isset($customer_atts['pos_address'])?$customer_atts['pos_address']:'';            
            $address_component = !empty($address_component)?json_decode($address_component,true):'';            
             
            $latitude = isset($address_component['latitude']) ?$address_component['latitude']:null;
            $longitude = isset($address_component['longitude']) ?$address_component['longitude']:null;            

            $transaction_type = isset($customer_atts['transaction_type'])?$customer_atts['transaction_type']:'';

            try {
               $model_customer = ACustomer::get($client_id);               
               $social_strategy = $model_customer->social_strategy;
            } catch (Exception $e) {}
            
                        
            $points_enabled = isset(Yii::app()->params['settings']['points_enabled'])?Yii::app()->params['settings']['points_enabled']:false;
		    $points_enabled = $points_enabled==1?true:false;
            if ($points_enabled && ($meta = AR_merchant_meta::getValue(Yii::app()->merchant->merchant_id, 'loyalty_points'))) {                
				$points_enabled = $meta['meta_value'] == 1;
			}            
            
            if($client_id<=0 || $client_id=='walkin'){
                $points_enabled = false;
            }
            
            $points_earning_rule = isset(Yii::app()->params['settings']['points_earning_rule'])?Yii::app()->params['settings']['points_earning_rule']:'sub_total';									
			$points_earning_points = isset(Yii::app()->params['settings']['points_earning_points'])?Yii::app()->params['settings']['points_earning_points']:0;	
			$points_minimum_purchase = isset(Yii::app()->params['settings']['points_minimum_purchase'])?Yii::app()->params['settings']['points_minimum_purchase']:0;	
			$points_maximum_purchase = isset(Yii::app()->params['settings']['points_maximum_purchase'])?Yii::app()->params['settings']['points_maximum_purchase']:0;
            
            CCart::setExchangeRate($exchange_rate);
			CCart::setAdminExchangeRate($admin_exchange_rate);
			CCart::setPointsRate($points_enabled,$points_earning_rule,$points_earning_points,$points_minimum_purchase,$points_maximum_purchase);

            require_once 'get-cart.php';                    
            
            if(!empty($customer_name)){
                $customer_data = [
                    'id'=>$client_id,
                    'data'=>[[
                        'value'=>$client_id>0?$client_id:'walkin',
                        'label'=>t($customer_name),                        
                    ]]
                ];
            } else {
                $customer_name = 'Walk-in Customer';
                CCart::savedAttributes($cart_uuid,'customer_name',$customer_name);   
                $customer_data = [
                    'id'=>0,
                    'data'=>[[
                        'value'=>'walkin',
                        'label'=>t($customer_name)
                    ]]
                ];
            }            

            $cart_model = CCart::get($cart_uuid);   
            //$total_sendorder = CCart::getTotalSendOrder($cart_uuid,1);
            $total_sendorder = 0;
            $total_unsendorder = CCart::getTotalSendOrder($cart_uuid,0);

            AR_cart::model()->updateAll(array(
                'total' =>floatval($total),					
            ), "cart_uuid=:cart_uuid",[
                ":cart_uuid"=>$cart_uuid
            ]);		
            
            // $model = CPos::getCartOrderRefence($cart_uuid);
            // $order_reference = $model?$model->order_reference:null;
            // dump($order_reference);

            if(in_array("viewed",(array)$payload)){
                $cart_model->is_view=1;
                $cart_model->save();
            }
            			
			$this->code = 1; $this->msg = "ok";
		    $this->details = array(			      
              'client_id'=>$client_id,
		      'cart_uuid'=>$cart_uuid,		      
		      'error'=>$error,
		      'checkout_data'=>$checkout_data,
		      'out_of_range'=>$out_of_range,
		      'address_component'=>$address_component,
		      'go_checkout'=>$go_checkout,
		      'items_count'=>$items_count,
		      'data'=>$data,		      
              'customer_data'=>$customer_data,
              'order_reference'=>$cart_model->order_reference,
              'table_data'=>$table_data,              
              'total_sendorder'=>$total_sendorder,
              'total_unsendorder'=>$total_unsendorder,
              'points_data'=>[
				'points_enabled'=>$points_enabled,
			    'points_to_earn'=>$points_to_earn,
			    'points_label'=>$points_label,
			  ]
		    );

        } catch (Exception $e) {
            $cart_row = CommonUtility::generateUIID();

            CCart::deleteAttributesAll($cart_uuid,[
                'client_id','contact_number','contact_email','last_name','first_name','contact_number_prefix'
            ]);
            $customer_name = 'Walk-in Customer';
            CCart::savedAttributes($cart_uuid,'customer_name',$customer_name);   
            $customer_data = [
                'value'=>'walkin',
                'label'=>t($customer_name)
            ];

            $this->msg = t($e->getMessage());		
            $this->details = [
                'cart_uuid'=>$cart_row,
                'table_data'=>$table_data,
                'customer_data'=>$customer_data
            ];
        }		
        $this->responseJson();  
    }

    public function actionsetTransactionType()
    {
        try {
            
            $cart_uuid = Yii::app()->input->post('cart_uuid');
            $transaction_type = Yii::app()->input->post('transaction_type');
            CCart::get($cart_uuid);
            CCart::savedAttributes($cart_uuid,Yii::app()->params->local_transtype,$transaction_type);

            // if($transaction_type!="dinein"){                
            //     CCart::deleteAttributesAll($cart_uuid,['guest_number','table_uuid','room_uuid']);
            // }

            $this->code = 1;
            $this->msg = "ok";

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }		
        $this->responseJson();  
    }    

    public function actiongetMenuItem()
    {
        try {

            $merchant_id = Yii::app()->merchant->merchant_id;
		    $item_uuid = Yii::app()->input->post('item_uuid');
		    $cat_id = intval(Yii::app()->input->post('cat_id'));
            $customer_id =  Yii::app()->request->getPost('customer_id', '');            

            $points_enabled = isset(Yii::app()->params['settings']['points_enabled'])?Yii::app()->params['settings']['points_enabled']:false;
		    $points_enabled = $points_enabled==1?true:false;
			$points_earning_rule = isset(Yii::app()->params['settings']['points_earning_rule'])?Yii::app()->params['settings']['points_earning_rule']:'sub_total';						
			$points_earning_points = isset(Yii::app()->params['settings']['points_earning_points'])?Yii::app()->params['settings']['points_earning_points']:0;
						
			if ($points_enabled && ($meta = AR_merchant_meta::getValue($merchant_id, 'loyalty_points'))) {
				$points_enabled = $meta['meta_value'] == 1;
			}

            if($customer_id=="walkin" || empty($customer_id) || is_null($customer_id) ){                
                $points_enabled = false;
            }

            $exchange_rate = 1;

            $points = [
				'points_enabled'=>$points_enabled,
				'points_earning_rule'=>$points_earning_rule,
				'points_earning_points'=>$points_earning_points,
			];

            CMerchantMenu::setExchangeRate($exchange_rate);
			CMerchantMenu::setAdminExchangeRate($exchange_rate);
			CMerchantMenu::setPointsRate($points_enabled,$points_earning_rule,$points_earning_points);

            $items = CMerchantMenu::getMenuItem($merchant_id,$cat_id,$item_uuid,Yii::app()->language);
			$addons = CMerchantMenu::getItemAddonCategory($merchant_id,$item_uuid,Yii::app()->language);
			$addon_items = CMerchantMenu::getAddonItems($merchant_id,$item_uuid,Yii::app()->language);	
			$meta = CMerchantMenu::getItemMeta($merchant_id,$item_uuid);
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
              'points'=>$points,
			  'config'=>$config
		    );            
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }	
        $this->responseJson();   
    }    

    public function actionaddCartItems()
    {
        try {

            $merchant_id = Yii::app()->merchant->merchant_id;
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
                'inline_qty'=>$inline_qty,
                'transaction_type'=>$transaction_type
            );		
            

            CCart::add($items);
            CCart::applyAutoAddPromo($cart_uuid, $merchant_id);										  
            
            if (!CCart::getAttributes($cart_uuid, Yii::app()->params->local_transtype)) {
			    CCart::savedAttributes($cart_uuid,Yii::app()->params->local_transtype,$transaction_type);			
            }
					  
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

            // if($transaction_type!="dinein"){                
            //     CCart::deleteAttributesAll($cart_uuid,['guest_number','table_uuid','room_uuid']);
            // }        
										
			$this->code = 1 ; $this->msg = "OK";			
			$this->details = array(
			  'cart_uuid'=>$cart_uuid
			);		             

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }	
        $this->responseJson();   
    }

	public function actionremoveCartItem()
	{				
		try {
			
            $cart_uuid = Yii::app()->input->post('cart_uuid');
		    $row = Yii::app()->input->post('row');

			CCart::remove($cart_uuid,$row);
            CCacheData::add();
			$this->code = 1; $this->msg = "Ok";
			$this->details = array(
		      'data'=>array()
		    );		    	   			
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		   
		}		
		$this->responseJson();  
	}        

    public function actionupdateCartItems()
	{		
		$cart_uuid = Yii::app()->input->post('cart_uuid');
		$cart_row = Yii::app()->input->post('row');
		$item_qty = intval(Yii::app()->input->post('item_qty'));
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
		}		
		$this->responseJson();  
	}    

    public function actionclearCart()
    {
        try {

            $merchant_id = Yii::app()->merchant->merchant_id;
            $cart_uuid = Yii::app()->input->post('cart_uuid');

            $attrs = CCart::getAttributesAll($cart_uuid,['table_uuid']);            
            $table_uuid = isset($attrs['table_uuid'])?$attrs['table_uuid']:'';

            CBooking::updateTableStatus($merchant_id,$table_uuid);
            CCart::clear($cart_uuid);            
            
			$this->code = 1; $this->msg = "Ok";			

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }	
        $this->responseJson();  
    }    

    public function actioncartSetCustomer()
    {
        try {

            $client_id = trim(Yii::app()->input->post('client_id'));
            $cart_uuid = Yii::app()->input->post('cart_uuid');

            $cart_model = CCart::get($cart_uuid);
            
            if($client_id>0){                
                CCart::savedAttributes($cart_uuid,'client_id',$client_id);
                try {
                    $customer = ACustomer::get($client_id);
                    $customer_name = $customer->first_name." ".$customer->last_name;				
                    CCart::savedAttributes($cart_uuid,'contact_number',$customer->contact_phone);
                    CCart::savedAttributes($cart_uuid,'contact_email',$customer->email_address);
                } catch (Exception $e) {
                    $customer_name = 'Walk-in Customer';
                }	                
                CCart::savedAttributes($cart_uuid,'customer_name',$customer_name);
            } else if ($client_id=="walkin"){                
                CCart::deleteAttributesAll($cart_uuid,[
                    'client_id','contact_number','contact_email','last_name','first_name','contact_number_prefix'
                ]);
                $customer_name = 'Walk-in Customer';
                CCart::savedAttributes($cart_uuid,'customer_name',$customer_name);        
            } else {                               
                CCart::deleteAttributesAll($cart_uuid,[
                    'client_id','contact_number','contact_email','customer_name'
                ]);                
            }

            if(!empty($customer_name)){
                try {
                    AR_kitchen_order::model()->updateAll(array(
                        'customer_name' =>$customer_name,                    
                    ), "order_reference=:order_reference",[
                        ":order_reference"=>$cart_model->order_reference
                    ]);		
                } catch (Exception $e) {}
            }

            $this->code = 1;
            $this->msg = "ok";
            
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }		
        $this->responseJson();  
    }    

    public function actionclientAddresses()
	{
		try {
            
            $addresses = CClientAddress::getAddresses( Yii::app()->input->get("client_id") );				
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

	public function actiongetlocationAutocomplete()
	{
		try {

		   $q = Yii::app()->input->post('q');

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

			CMaps::config();
			$place_id = Yii::app()->input->post('place_id');
			//$resp = CMaps::locationDetailsNew($place_id,'');
            $resp = CMaps::locationDetails($place_id);	

			$this->code =1; $this->msg = "ok";
			$this->details = array(
			  'data'=>$resp,
			);

		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}
    
    public function actionsaveCartAddress()
    {
        try {
                                           
            $cart_uuid = isset($this->data['cart_uuid'])?trim($this->data['cart_uuid']):null;
            $client_id = isset($this->data['client_id'])?trim($this->data['client_id']):null;
            $place_id = isset($this->data['place_id'])?trim($this->data['place_id']):null;
            
            if($client_id=="walkin" || $client_id<=0){
                $data = isset($this->data['data'])?($this->data['data']):'';            
                $data['address']['address1'] = isset($this->data['address1'])?$this->data['address1']:'';
                $data['address']['formatted_address'] = isset($this->data['formatted_address'])?$this->data['formatted_address']:'';
                $data['latitude'] = isset($this->data['latitude'])?$this->data['latitude']:'';
                $data['longitude'] = isset($this->data['longitude'])?$this->data['longitude']:'';
                $data['attributes'] = [
                    'location_name' =>isset($this->data['location_name'])?$this->data['location_name']:'',
                    'delivery_options'=>isset($this->data['delivery_options'])?$this->data['delivery_options']:'',
                    'delivery_instructions'=>isset($this->data['delivery_instructions'])?$this->data['delivery_instructions']:'',
                    'address_label'=>isset($this->data['address_label'])?$this->data['address_label']:'',
                    'custom_field1'=>isset($this->data['custom_field1'])?$this->data['custom_field1']:'',
                    'custom_field2'=>isset($this->data['custom_field2'])?$this->data['custom_field2']:'',
                ];
                CCart::savedAttributes($cart_uuid,'pos_address',json_encode($data));
                $this->code = 1;
		    	$this->msg = t("Address saved succesfully");
		    	$this->details = array(
		    	  'place_id'=>$place_id
		    	);
                $this->responseJson();
            }             

            ACustomer::get( intval($client_id) );            

            $model = AR_client_address::model()->find('place_id=:place_id AND client_id=:client_id', 
		    array(':place_id'=>$place_id,'client_id'=> $client_id));				
            if(!$model){                
                $model = new AR_client_address();
            }            
            
            $model->client_id = $client_id;
            $model->address_uuid = CommonUtility::generateUIID();		    	
            $model->place_id = $place_id;
            $model->country = isset($this->data['country'])?$this->data['country']:'';
            $model->country_code = isset($this->data['country_code'])?$this->data['country_code']:'';

            $model->location_name = isset($this->data['location_name'])?$this->data['location_name']:'';
	    	$model->delivery_instructions = isset($this->data['delivery_instructions'])?$this->data['delivery_instructions']:'';
	    	$model->delivery_options = isset($this->data['delivery_options'])?$this->data['delivery_options']:'';
	    	$model->address_label = isset($this->data['address_label'])?$this->data['address_label']:'';
	    	$model->latitude = isset($this->data['latitude'])?$this->data['latitude']:'';
	    	$model->longitude = isset($this->data['longitude'])?$this->data['longitude']:'';
	    	$model->address1 = isset($this->data['address1'])?$this->data['address1']:'';			
	    	$model->formatted_address = isset($this->data['formatted_address'])?$this->data['formatted_address']:'';

            $model->custom_field1 = isset($this->data['custom_field1'])?$this->data['custom_field1']:'';
            $model->custom_field2 = isset($this->data['custom_field2'])?$this->data['custom_field2']:'';
            
            $data = isset($this->data['data'])?($this->data['data']):'';            
            $data['address']['address1'] = $model->address1;
            $data['address']['formatted_address'] = $model->formatted_address;
            $data['latitude'] = $model->latitude;
            $data['longitude'] = $model->longitude;
            $data['attributes'] = [
                'location_name' =>$model->location_name,
                'delivery_options'=>$model->delivery_options,
                'delivery_instructions'=>$model->delivery_instructions,
                'address_label'=>$model->address_label,
                'custom_field1'=>$model->custom_field1,
                'custom_field2'=>$model->custom_field2,
            ];
            
            if($model->save()){
                CCart::savedAttributes($cart_uuid,'pos_address',json_encode($data));
                $this->code = 1;
		    	$this->msg = t("Address saved succesfully");
		    	$this->details = array(
		    	  'place_id'=>$model->place_id
		    	);
            } else $this->msg = CommonUtility::parseError( $model->getErrors());
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }		
        $this->responseJson();  
    }    

    public function actionclearCustomer()
    {
        try {

            $cart_uuid = Yii::app()->input->post('cart_uuid'); 
            CCart::deleteAttributesAll($cart_uuid,[
                'client_id','contact_number','contact_email','customer_name'
            ]);
            $this->code = 1;
            $this->msg = "Ok";
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }		
        $this->responseJson();  
    }

    public function actionapplyDiscount()
    {
        try {

            $discount = Yii::app()->input->post('discount');
            $cart_uuid = Yii::app()->input->post('cart_uuid');

            if($discount>0){
                $payload = [
                    'all_orders'
                ];                
                CCart::setPayload($payload);
                CCart::getContent($cart_uuid,Yii::app()->language);	
			    $subtotal = CCart::getSubTotal();
			    $sub_total = floatval($subtotal['sub_total']);
                $less_amount = $sub_total*($discount/100);              
                
                $sub_total_after_less_discount = $sub_total-$less_amount;
                if($sub_total_after_less_discount>0){
                    $name = array(
                        'label'=>"Discount {{discount}}%",
                        'params'=>array(
                         '{{discount}}'=>Price_Formatter::convertToRaw($discount,0)
                        )
                    );
                    $promo_type = 'manual_discount';
                    $params = array(
                        'name'=> json_encode($name),
                        'type'=>$promo_type,                         
                        'target'=>'subtotal',
                        'value'=>"-%$discount"
                    );		
                    
                    CCart::savedAttributes($cart_uuid,'promo',json_encode($params));
			        CCart::savedAttributes($cart_uuid,'promo_type',$promo_type);
                    $this->code = 1; 
			        $this->msg = "succesful";

                } else $this->msg = t("Discount cannot apply due to sub total is less than 1");
            } else $this->msg = t("Discount must be greater than zero");

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }		
        $this->responseJson();  
    }

    public function actionapplyPromoCode()
    {
        try {

            $promo_code = Yii::app()->input->post('promo_code');
            $cart_uuid = Yii::app()->input->post('cart_uuid');            
            
            $payload = [
                'all_orders'
            ];                
            CCart::setPayload($payload);    

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

    public function actionremovePromocode()
    {
        try {

            $cart_uuid = Yii::app()->input->post('cart_uuid');            
			CCart::deleteAttributesAll($cart_uuid,CCart::CONDITION_RM);
			$this->code = 1;
			$this->msg = "ok";            

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }		
        $this->responseJson();  
    }    

    public function actionapplyTips()
    {
        try {

            $tips = floatval(Yii::app()->input->post('tips'));
            $cart_uuid = Yii::app()->input->post('cart_uuid');
            if($tips>0){                
                $payload = [
                    'all_orders'
                ];                
                CCart::setPayload($payload);
                CCart::getContent($cart_uuid,Yii::app()->language);	
                $merchant_id = Yii::app()->merchant->merchant_id;
                $options_data = OptionsTools::find(['merchant_enabled_tip','merchant_tip_type'],$merchant_id);							
			    $enabled_tip = isset($options_data['merchant_enabled_tip'])?$options_data['merchant_enabled_tip']:false;
                if($enabled_tip){
                    CCart::savedAttributes($cart_uuid,'tips',$tips);	
                    $this->code = 1; $this->msg = "OK";
                    $this->details = array(
                       'tips'=>$tips,			  
                    );
                } else $this->msg = t("Tip are disabled");
            } else $this->msg = t("Tips must be greater than zero");
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }		
        $this->responseJson();  
    }

    public function actionremoveTips()
    {
        try {
            $cart_uuid = Yii::app()->input->post('cart_uuid');
            CCart::deleteAttributes($cart_uuid,'tips');
            $this->code = 1; $this->msg = "ok";
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }		
        $this->responseJson();  
    }

    public function actionapplyPoints()
    {
        try {               
            
            $points = Yii::app()->input->post('points');            
            $points_id = intval(Yii::app()->input->post('points_id'));       
            $cart_uuid = Yii::app()->input->post('cart_uuid');
            $customer_id = Yii::app()->input->post('customer_id');

            //$base_currency = Price_Formatter::$number_format['currency_code'];		
            $base_currency = AttributesTools::defaultCurrency(false);
            $merchant_id = Yii::app()->merchant->merchant_id;            

            $redemption_policy = isset(Yii::app()->params['settings']['points_redemption_policy'])?Yii::app()->params['settings']['points_redemption_policy']:'universal';            
            $balance = CPoints::getAvailableBalancePolicy($customer_id,$redemption_policy,$merchant_id);            
                        
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

            $payload = [
                'all_orders'
            ];                
            CCart::setPayload($payload);
            CCart::getContent($cart_uuid,Yii::app()->language);	
			$subtotal = CCart::getSubTotal();
			$sub_total = floatval($subtotal['sub_total']);
			$total = floatval($sub_total) - floatval($discount2);			
			if($total<=0){
				$this->msg = t("Discount cannot be applied due to total less than zero after discount");				
				$this->responseJson();				
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
			$this->msg = t("Points apply to cart");

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
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
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actiongetPointsthresholds()
    {
        try {        
                        
            $customer_id = Yii::app()->input->post('customer_id');            
            $merchant_id = Yii::app()->merchant->merchant_id;
            
            $base_currency = AttributesTools::defaultCurrency();
            $multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
            $multicurrency_enabled = $multicurrency_enabled==1?true:false;

            $options_merchant = OptionsTools::find(['merchant_default_currency'],$merchant_id);						
		    $merchant_default_currency = isset($options_merchant['merchant_default_currency'])?$options_merchant['merchant_default_currency']:'';
			$merchant_default_currency = !empty($merchant_default_currency)?$merchant_default_currency:$base_currency;

            $exchange_rate = 1; $exchange_rate_to_merchant =1;
            $currency_code = !empty($currency_code)?$currency_code: (empty($merchant_default_currency)?$base_currency:$merchant_default_currency);
            if(!empty($currency_code) && $multicurrency_enabled){
				$exchange_rate = CMulticurrency::getExchangeRate($base_currency,$merchant_default_currency);				
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
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionaddTotal()
    {
        try {                               
            
            $cart_uuid = trim(Yii::app()->input->post('cart_uuid'));
            $description = "Total items";
            $total = floatval(Yii::app()->input->post('total'));
            
            $merchant_id = Yii::app()->merchant->merchant_id;

            $model = AR_item::model()->find("visible=:visible",[
                ':visible'=>0
            ]);
            if(!$model){
                $model=new AR_item;
            }            

            if($category_res = Yii::app()->db->createCommand("SELECT cat_id FROM {{category}} WHERE merchant_id=".q($merchant_id)." ")->queryRow()){                
                $category[] = $category_res['cat_id'];
            } else $category[] = 1;
            
			$model->scenario = 'create';
            $model->item_name = $description;
            $model->status = 'publish';
            $model->item_price = $total;
            $model->category_selected = $category;
            $model->merchant_id = intval($merchant_id);
            $model->visible = 0;
            
            if($model->validate()){
                if($model->save()){

                    $item_size_id = 0;
                    $model_size = AR_item_relationship_size::model()->find("item_id=:item_id",[
                        ':item_id'=>$model->item_id
                    ]);
                    if($model_size){                        
                        $model_size->price = $total;
                        $model_size->save();
                        $item_size_id = $model_size->item_size_id;
                    }
                    
                    $uuid = CommonUtility::createUUID("{{cart}}",'cart_uuid');
                    $cart_row = CommonUtility::generateUIID();                    
                    $transaction_type = trim(Yii::app()->input->post('transaction_type'));
                    $cart_uuid = !empty($cart_uuid)?$cart_uuid:$uuid;		
                    $cat_id = $category[0];
                    $item_token = $model->item_token;
                    $item_size_id = $item_size_id;
                    $item_qty = 1;

                    $items = array(
                        'merchant_id'=>$merchant_id,
                        'cart_row'=>$cart_row,
                        'cart_uuid'=>$cart_uuid,
                        'cat_id'=>$cat_id,
                        'item_token'=>$item_token,
                        'item_size_id'=>$item_size_id,
                        'qty'=>$item_qty,
                        'special_instructions'=>'',
                        'if_sold_out'=>'substitute',
                        'addons'=>[],
                        'attributes'=>[],
                        'inline_qty'=>0
                    );		                    
                    
                    CCart::add($items);										  
			        CCart::savedAttributes($cart_uuid,Yii::app()->params->local_transtype,$transaction_type);

                    $this->code = 1 ; 
                    $this->msg = t("Total amount succesfully added");
                    $this->details = array(
                      'cart_uuid'=>$cart_uuid
                    );		 
                } else $this->msg = CommonUtility::parseError( $model->getErrors() );
            } else $this->msg = CommonUtility::parseError( $model->getErrors() );

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }    

    public function actionapplyHoldOrder()
    {
        try {

            $merchant_id = Yii::app()->merchant->merchant_id;            
            $cart_uuid = Yii::app()->input->post('cart_uuid');
            $order_reference = Yii::app()->input->post('order_reference');
            $model = CCart::get($cart_uuid);
            $model->scenario="hold_cart";
            
            if(empty($order_reference)){
                $this->msg = t("Order reference is required");
                $this->responseJson();
            }

            $model->hold_order=1;
            $model->hold_order_reference=$order_reference;
            if($model->save()){
                $this->code = 1;
                $this->msg = t("Order successfully hold");
                $merchant_ids = array();
                $merchant_ids[] = $merchant_id;	                    
                $printers = CMerchants::getPrinterAutoPrinterAll($merchant_ids,[
                    'wifi'
                ]);                                       
                $this->details = [
                    'cart_uuid'=>$cart_uuid,
                    'wifi_printers'=>$printers                    
                ];
            } else $this->msg = CommonUtility::parseError( $model->getErrors() );            
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }		
        $this->responseJson();  
    }        

    public function actionsearchfooditems()
    {
        try {            

            $merchant_id = Yii::app()->merchant->merchant_id;
            $q = Yii::app()->input->post('q');
            $exchange_rate = 1;

            $options_merchant = OptionsTools::find(['merchant_timezone','merchant_default_currency'],$merchant_id);
			$merchant_timezone = isset($options_merchant['merchant_timezone'])?$options_merchant['merchant_timezone']:'';					
			if(!empty($merchant_timezone)){
				Yii::app()->timezone = $merchant_timezone;
			}

            $items_not_available = CMerchantMenu::getItemAvailability($merchant_id,date("w"),date("H:h:i"));	
		    $category_not_available = CMerchantMenu::getCategoryAvailability($merchant_id,date("w"),date("H:h:i"));		 

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
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }    

    public function actionsubmitPOSOrder()
    {
        try {
                                     
            //dump($this->data);die();
            $local_id = isset($this->data['place_id'])?$this->data['place_id']:'';
            $cart_uuid = isset($this->data['cart_uuid'])?trim($this->data['cart_uuid']):'';
            $payment_code = isset($this->data['payment_code'])?trim($this->data['payment_code']):'';
            $order_change = isset($this->data['order_change'])?floatval($this->data['order_change']):0;
            $payment_change = $order_change;
            $whento_deliver = isset($this->data['whento_deliver'])?trim($this->data['whento_deliver']):'now';
            $delivery_date = isset($this->data['delivery_date'])?trim($this->data['delivery_date']):date("Y-m-d");
            $delivery_time = isset($this->data['delivery_time'])?trim($this->data['delivery_time']):'';
            $receive_amount = isset($this->data['receive_amount'])?floatval($this->data['receive_amount']):0;
            $payment_reference = isset($this->data['payment_reference'])?trim($this->data['payment_reference']):'';
            $order_notes = isset($this->data['order_notes'])?trim($this->data['order_notes']):'';    
            $place_data = isset($this->data['place_data'])?$this->data['place_data']:'';    
            $payment_uuid = isset($this->data['payment_uuid'])?$this->data['payment_uuid']:'';                
            $room_id = isset($this->data['room_id'])?intval($this->data['room_id']):0;
            $table_id = isset($this->data['table_id'])?trim($this->data['table_id']):'';            
            $skip_kitchen = isset($this->data['skip_kitchen'])?trim($this->data['skip_kitchen']):'';            
            $skip_kitchen = $skip_kitchen==1?true:false;       
            
            $transaction_type = isset($this->data['transaction_type'])?trim($this->data['transaction_type']):'';
            
            $tracking_stats = AR_admin_meta::getMeta([
				'tracking_status_process','tracking_status_completed','tracking_status_delivered'
			]);						
            $tracking_status_completed = isset($tracking_stats['tracking_status_completed'])?$tracking_stats['tracking_status_completed']['meta_value']:'new';            
            $tracking_status_delivered = isset($tracking_stats['tracking_status_delivered'])?$tracking_stats['tracking_status_delivered']['meta_value']:'new';    
                        

            $guestNumber = 0;
            if($table_data = CCart::getAttributesAll($cart_uuid,['guest_number'])){                
                $guestNumber = isset($table_data['guest_number'])?intval($table_data['guest_number']):0;
            }            
            $guest_number = isset($this->data['guest_number'])?intval($this->data['guest_number']):$guestNumber;
                        
            $currency_code = isset($this->data['currency_code'])?trim($this->data['currency_code']):'';
            //$base_currency = Price_Formatter::$number_format['currency_code'];
            $base_currency = AttributesTools::defaultCurrency();
            
            $multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		    $multicurrency_enabled = $multicurrency_enabled==1?true:false;	
            
            $payload = array(
                'items','merchant_info','service_fee',
                'delivery_fee','packaging','tax','tips','checkout','discount','distance_new',
                'summary','total','card_fee','points','points_discount','manual_discount'
            );
            if(!$skip_kitchen){
                $payload[] = "send_order";
            }
            
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
            $client_id = 0;

            /*CHECK IF MERCHANT IS OPEN*/
            try {

                $merchant_id = Yii::app()->merchant->merchant_id;            
                
                // CHECK IF MERCHANT HAS DIFFERENT TIMEZONE
                $options_merchant = OptionsTools::find(['merchant_timezone'],$merchant_id);
                $merchant_timezone = isset($options_merchant['merchant_timezone'])?$options_merchant['merchant_timezone']:'';
                if(!empty($merchant_timezone)){
                    Yii::app()->timezone = $merchant_timezone;
                }

                $date = date("Y-m-d");
                $time_now = date("H:i");                
						                
                if($whento_deliver=="schedule"){
                    $date = $delivery_date;
                    $time_now  = !empty($delivery_time)?$delivery_time:$time_now;
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
            } else {
                $merchant_default_currency = $base_currency;
				$currency_code = $base_currency;
            }            

            CCart::setAdminExchangeRate($exchange_rate_use_currency_to_admin);

            $atts = CCart::getAttributesAll($cart_uuid,['client_id','promo','pos_address']);
            $client_id = isset($atts['client_id'])?$atts['client_id']:0;            
            $addressComponents = isset($atts['pos_address'])?$atts['pos_address']:'';            
            $addressComponents = !empty($addressComponents)?json_decode($addressComponents,true):''; 

            $latitude = isset($addressComponents['latitude']) ?$addressComponents['latitude']:null;
            $longitude = isset($addressComponents['longitude']) ?$addressComponents['longitude']:null;
                        
            try {
                $model_customer = ACustomer::get($client_id);               
                $social_strategy = $model_customer->social_strategy;
             } catch (Exception $e) {}
            
             
            require_once 'get-cart.php';                             
                    
            $set_status = $transaction_type=="delivery"?$tracking_status_delivered:$tracking_status_completed;
            $order_status = isset($this->data['order_status'])? ( !empty($this->data['order_status'])? trim($this->data['order_status']) : $set_status ) : $set_status;             
                        
            if($transaction_type=="delivery"){                 
                $attributes_data = isset($addressComponents['attributes'])?$addressComponents['attributes']:'';                
                $address_component = [
                    'place_id'=>isset($addressComponents['place_id'])?$addressComponents['place_id']:'',
                    'latitude'=>isset($addressComponents['latitude'])?$addressComponents['latitude']:'',
                    'longitude'=>isset($addressComponents['longitude'])?$addressComponents['longitude']:'',
                    'address1'=>isset($addressComponents['address'])? ($addressComponents['address']['address1']?$addressComponents['address']['address1']:'')  :'',
                    'address2'=>isset($addressComponents['address'])? ($addressComponents['address']['address2']?$addressComponents['address']['address2']:'')  :'',
                    'formatted_address'=>isset($addressComponents['address'])? ($addressComponents['address']['formatted_address']?$addressComponents['address']['formatted_address']:'')  :'',
                    'location_name'=>isset($attributes_data['location_name'])?$attributes_data['location_name']:'',
                    'delivery_options'=>isset($attributes_data['delivery_options'])?$attributes_data['delivery_options']:'',
                    'delivery_instructions'=>isset($attributes_data['delivery_instructions'])?$attributes_data['delivery_instructions']:'',
                    'address_label'=>isset($attributes_data['address_label'])?$attributes_data['address_label']:'',
                    'custom_field1'=>isset($attributes_data['custom_field1'])?$attributes_data['custom_field1']:'',
                    'custom_field2'=>isset($attributes_data['custom_field2'])?$attributes_data['custom_field2']:'',
                ];                                        
            }                                    
                                                
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
                                
                            case "manual_discount":	    
                                $mdd_offer_discount = 0;                                 
                                $manual_discount_data = isset($atts['promo'])?json_decode($atts['promo'],true):null;                                
                                if(is_array($manual_discount_data) && count($manual_discount_data)>=1){                                    
                                    $mdd_offer_discount = isset($manual_discount_data['value'])?$manual_discount_data['value']:0;
                                }
                                $promo_details = [
                                    'promo_type'=>"manual_discount",
                                    'offer_discount'=> floatval(CCart::cleanNumber($mdd_offer_discount)),
                                    'value'=>CCart::cleanNumber($summary_item['raw']),
                                ];
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


                $order_reference = '';
                if($order_ref_model = CPos::getCartOrderRefence($cart_uuid)){
                    $order_reference = $order_ref_model->order_reference;
                }                
                
                $model = new AR_ordernew;
				$model->scenario = 'pos_entry';
				$model->order_uuid = CommonUtility::generateUIID();
                $model->order_reference = $order_reference;
				$model->merchant_id = intval($merchant_id);	
				$model->client_id = intval($client_id);
				$model->service_code = $transaction_type;
				$model->payment_code = $payment_code;
				$model->payment_change = $payment_change;				
				$model->validate_payment_change = false;	
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
                
                if(is_array($promo_details) && count($promo_details)>=1){
					if($promo_details['promo_type']=="voucher"){
						$model->promo_code = $promo_details['voucher_name'];
						$model->promo_total = $promo_details['less_amount'];
					} elseif ( $promo_details['promo_type']=="offers" ){						
						$model->offer_discount = $promo_details['less_amount'];
						$model->offer_total = floatval($offer_total);
					} elseif ( $promo_details['promo_type']=="manual_discount" ){                        
                        $model->offer_discount = floatval($promo_details['offer_discount']);
						$model->offer_total = floatval($promo_details['value']);
                    }
				}

                $model->whento_deliver = $whento_deliver;
                if($model->whento_deliver=="now"){
                    $model->delivery_date = CommonUtility::dateNow();
                } else {
                    $model->delivery_date = $delivery_date;
                    $model->delivery_time = $delivery_time;
                    $model->delivery_time_end = $delivery_time;
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
                                
                if(!empty($order_notes)){
                $metas['order_notes'] = $order_notes;
                }
                if($order_change>0){
                $metas['order_change'] = floatval($order_change);
                }
                if($receive_amount>0){
                   $metas['receive_amount'] = floatval($receive_amount);
                   //$metas['payment_change'] = floatval($receive_amount);
                }                
                
                $metas['payment_change'] = floatval($payment_change);
				$metas['self_delivery'] = $self_delivery==true?1:0;	
				$metas['points_to_earn'] = floatval($points_to_earn);                  
                                
                if($transaction_type=="dinein"){
                    $metas['guest_number'] = intval($guest_number);
                    try {			
                        $model_room = CBooking::getRoom($room_id); 
                        $metas['room_id'] = $model_room->room_id;
                    } catch (Exception $e) {					
                    }

                    try {			
                        $model_table = CBooking::getTable($table_id); 					
                        $metas['table_id'] = $model_table->table_id;
                    } catch (Exception $e) {					
                    }

                    $model->room_id = $room_id;
			        $model->table_id = $table_id;
                } else {
                    $model->table_id = $table_id;
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
				
				$model->request_from = "pos";
                
                $model->payment_reference = $payment_reference;                
                $model->status = $order_status;
                $model->payment_status = CPayments::paidStatus();
                                                          
                if($model->save()){
                    $success_message = t("Your payment has been successfully processed,and your order #{order_id} is complete.",[
                        '{order_id}'=>$model->order_id
                    ]);
                    if($order_change>0){
                        $success_message.="\n";
                        $success_message.= t("This order has change of {amount}",[
                            '{amount}'=>Price_Formatter::formatNumber($order_change)
                        ]);
                    }
                    $this->code = 1;
					$this->msg = t("Your Order has been place");

                    // GET WIFI PRINTERS AUTO PRINT
                    $merchant_ids = array();
                    $merchant_ids[] = $merchant_id;	                    
                    $printers = CMerchants::getPrinterAutoPrinterAll($merchant_ids,[
                        'wifi'
                    ]);                                       

                    $this->details = [
                        'order_id'=>$model->order_id,
                        'order_uuid' => $model->order_uuid,
                        'cart_uuid'=>$cart_uuid,
                        'email_address'=>isset($metas['contact_email'])?$metas['contact_email']:'',
                        'contact_phone'=>isset($metas['contact_number'])?$metas['contact_number']:'',
                        'success_message'=>$success_message,
                        'wifi_printers'=>$printers
                    ];                    
                } else {
                    if ( $error = CommonUtility::parseError( $model->getErrors()) ){				
						$this->msg = $error;						
					} else $this->msg[] = array('invalid error');
                }                
            }            
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }		
        $this->responseJson();  
    }    

    public function actionorderDetails()
    {
        try {
            
             $hide_currency = isset($this->data['hide_currency'])?$this->data['hide_currency']:false;             
             //remove currency fixed for printing             
             if($hide_currency==1){                
                Price_Formatter::$number_format['currency_symbol'] = '';
             }             
             $refund_transaction = array(); $order_id = 0;
			 $summary = array(); $progress = array(); $order_status = array();
			 $allowed_to_cancel = false;
			 $pdf_link = ''; $delivery_timeline=array();
			 $order_delivery_status = array(); $merchant_info=array();
			 $order = array(); $items = array(); $order_type = '';

			 $label = array(
				'summary'=>t("Summary"),
			 );

		     $order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';
			 $payload = isset($this->data['payload'])?$this->data['payload']:array();

		     COrders::getContent($order_uuid,Yii::app()->language);
		     $merchant_id = COrders::getMerchantId($order_uuid);
			 $order_id = COrders::getOrderID();

			 if(in_array('merchant_info',$payload)){
				$merchant_info = COrders::getMerchant($merchant_id,Yii::app()->language);
                $tax_data = OptionsTools::find(['merchant_tax_number'],$merchant_id);
                $merchant_info['merchant_tax_number'] = isset($tax_data['merchant_tax_number'])?$tax_data['merchant_tax_number']:'';
			 }
			 if(in_array('items',$payload)){
		        $items = COrders::getItems();
			 }

			 if(in_array('summary',$payload)){
		        $summary = COrders::getSummary();
			 }

			 if(in_array('order_info',$payload)){
		        $order = COrders::orderInfo(Yii::app()->language);
                $order_type = isset($order['order_info'])?$order['order_info']['order_type']:'';
			 }             

			 if(in_array('progress',$payload)){
			    $progress = CTrackingOrder::getProgress($order_uuid , date("Y-m-d g:i:s a") );
			 }

			 if(in_array('refund_transaction',$payload)){
				try {
					$refund_transaction = COrders::getPaymentTransactionList(Yii::app()->user->id,$order_id,array(
					'paid'
					),array(
					'refund',
					'partial_refund'
					));
				} catch (Exception $e) {
					//echo $e->getMessage(); die();
				}
			 }

			 if(in_array('status_allowed_cancelled',$payload)){
				$status_allowed_cancelled = COrders::getStatusAllowedToCancel();
				$order_status = $order['order_info']['status'];
				if(in_array($order_status,(array)$status_allowed_cancelled)){
					$allowed_to_cancel = true;
				}
			 }

			 if(in_array('pdf_link',$payload)){
			    $pdf_link = Yii::app()->createAbsoluteUrl("/print/pdf",array('order_uuid'=>$order['order_info']['order_uuid']));
			 }

			 if(in_array('delivery_timeline',$payload)){
				$delivery_timeline = AOrders::getOrderHistory($order_uuid);
			 }

			 if(in_array('order_delivery_status',$payload)){
			    $order_delivery_status = AttributesTools::getOrderStatusMany(Yii::app()->language,['order_status','delivery_status']);
			 }             

			 $allowed_to_review = false;
			if(in_array('allowed_to_review',$payload)){
				$find = AR_review::model()->find('merchant_id=:merchant_id AND client_id=:client_id
					AND order_id=:order_id',
					array(
					':merchant_id'=>intval($order['order_info']['merchant_id']),
					':client_id'=>intval(Yii::app()->user->id),
					':order_id'=>intval($order_id)
				));

				if(!$find){
					$status_allowed_review = AOrderSettings::getStatus(array('status_delivered','status_completed'));
					if(in_array($order_status,(array)$status_allowed_review)){
						$allowed_to_review = true;
					}
				}
			}

			$estimation = [];
			if(in_array('estimation',$payload)){
				try {
					$filter = [
						'merchant_id'=>$merchant_id,
						'shipping_type'=>"standard"
					];
					$estimation  = CMerchantListingV1::estimationMerchant2($filter);
				} catch (Exception $e) {
					//echo $e->getMessage(); die();
				}
		    }

            $credit_card_details = '';
            $payment_code = $order['order_info']['payment_code'];
            if(in_array('credit_card',$payload) && $payment_code=="ocr" ){
                try {
                    $credit_card_details = COrders::getCreditCard($order_id);
                    $credit_card_details = JWT::encode($credit_card_details, CRON_KEY, 'HS256');
                } catch (Exception $e) {
                    //
                }
            }

			$charge_type = '';
			if(in_array('charge_type',$payload)){
				$options_data = OptionsTools::find(array('merchant_delivery_charges_type'),$merchant_id);
				$charge_type = isset($options_data['merchant_delivery_charges_type'])?$options_data['merchant_delivery_charges_type']:'';
			}

            $filter_buttons = false; $buttons = array(); $group_name='';
            try {
			    $group_name = AOrderSettings::getGroup($order['order_info']['status']);
				if($group_name=="order_ready"){
					$filter_buttons = true;
				}
                if($filter_buttons){
                    $buttons = AOrders::getOrderButtons($group_name,$order['order_info']['order_type']);
                } else $buttons = AOrders::getOrderButtons($group_name);
			} catch (Exception $e) {
		    	//
            }

            $maps_config = CMaps::config();
		    $maps_config_raw = $maps_config;
            $map_direction = CMerchantListingV1::mapDirection($maps_config_raw, $order['order_info']['longitude'] ,$order['order_info']['latitude']);

            $client_id = $order?$order['order_info']['client_id']:0;
            $customer = COrders::getClientInfo($client_id);				    
		    $count = COrders::getCustomerOrderCount($client_id,$merchant_id);
            $customer = is_array($customer) ? $customer : [];
		    $customer['order_count'] = $count;

            $driver_data = []; $merchant_zone = []; $zone_list=[];

            if(in_array('driver',$payload)){
                $driver_id = $order?$order['order_info']['driver_id']:0;		
                if($driver_id>0){
                    $now = date("Y-m-d");
                    try {
                        $driver = CDriver::getDriver($driver_id);
                        $driver_data = [
                            'uuid'=>$driver->driver_uuid,
                            'driver_name'=>"$driver->first_name $driver->last_name",
                            'phone_number'=>"+".$driver->phone_prefix.$driver->phone,
                            'email_address'=>$driver->email,
                            'photo_url'=>CMedia::getImage($driver->photo,$driver->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('driver')),
                            'url'=>Yii::app()->createAbsoluteUrl("/merchantdriver/overview",['id'=>$driver->driver_uuid]),
                            'active_task'=>CDriver::getCountActiveTask($driver->driver_id,$now)
                        ];
                    } catch (Exception $e) {
                        //
                    }	
                }

                $merchant_zone = CMerchants::getListMerchantZone([$merchant_id]);
                if(!$zone_list = CommonUtility::getDataToDropDown("{{zones}}",'zone_id','zone_name')){
                    $zone_list = [];
                }

                $order_status = isset($order['order_info'])?$order['order_info']['status']:'';
                $order['order_info']['show_assign_driver'] = false;
                $order['order_info']['can_reassign_driver'] = true;
                if($order_type=="delivery"){
                    $status1 = COrders::getStatusTab2(['new_order','order_processing','order_ready']);
                    $status2 = AOrderSettings::getStatus(array('status_delivered','status_completed','status_delivery_fail','status_failed'));
                    $all_status = array_merge((array)$status1,(array)$status2);
                    if(in_array($order_status,(array)$all_status)){
                        $order['order_info']['show_assign_driver'] = true;
                    }
                    if(in_array($order_status,(array)$status2)){
                        $order['order_info']['can_reassign_driver'] = false;
                    }
                }
            }
            
            $order_table_data = [];
			if($order_type=="dinein"){
				$order_table_data = COrders::orderMeta(['table_id','room_id','guest_number']);	
				$room_id = isset($order_table_data['room_id'])?$order_table_data['room_id']:0;							
				$table_id = isset($order_table_data['table_id'])?$order_table_data['table_id']:0;							
				try {
					$table_info = CBooking::getTableByID($table_id);
					$order_table_data['table_name'] = $table_info->table_name;
				} catch (Exception $e) {
					$order_table_data['table_name'] = t("Unavailable");
				}				
				try {
					$room_info = CBooking::getRoomByID($room_id);					
					$order_table_data['room_name'] = $room_info->room_name;
				} catch (Exception $e) {
					$order_table_data['room_name'] = t("Unavailable");
				}				
			}					    		  

		    $data = array(
		       'merchant'=>$merchant_info,
		       'order'=>$order,
		       'items'=>$items,
		       'summary'=>$summary,
		       'label'=>$label,
		       'refund_transaction'=>$refund_transaction,
			   'progress'=>$progress,
			   'allowed_to_cancel'=>$allowed_to_cancel,
			   'allowed_to_review'=>$allowed_to_review,
			   'pdf_link'=>$pdf_link,
			   'delivery_timeline'=>$delivery_timeline,
			   'order_delivery_status'=>$order_delivery_status,
			   'estimation'=>$estimation,
			   'charge_type'=>$charge_type,
               'group_name'=>$group_name,
               'buttons'=>$buttons,
               'map_direction'=>$map_direction,
               'credit_card_details'=>$credit_card_details,
               'customer'=>$customer,
               'driver_data'=>$driver_data,
               'merchant_zone'=>$merchant_zone,
               'zone_list'=>$zone_list,
               'order_table_data'=>$order_table_data
		     );

		     $this->code = 1; $this->msg = "ok";
		     $this->details = array(
		       'data'=>$data,
		     );

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }  
    
    public function actiongetavailablepoints()
	{
		try {

            $customer_id = Yii::app()->input->post('customer_id');            
            if($customer_id<=0){
                $this->msg = t("Invalid Id");
                $this->responseJson();
            }
			$total = CPoints::getAvailableBalance($customer_id);
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
    
    public function actiongetHoldorders()
    {
        try {

            $page = Yii::app()->input->get('page');                                    
            $search = trim(Yii::app()->input->post('q'));           
            $length = Yii::app()->params->list_limit;            

            $sortby = "date_created"; $sort = 'DESC';
            
            $page_raw = intval(Yii::app()->input->get('page'));
            if($page>0){
                $page = $page-1;
            }
            
            $criteria=new CDbCriteria();            
            $criteria->alias = "a";     
            $criteria->select = "a.*,                  
            b.meta_name,b.meta_id as customer_name,
            c.meta_name,c.meta_id as transaction_type,            
            (
                select GROUP_CONCAT(item_token SEPARATOR ';')
                from {{cart}}
                where
                cart_uuid = a.cart_uuid
            ) as items_data
            ";       
            $criteria->join="
            left JOIN (
                SELECT cart_uuid,meta_name, meta_id FROM {{cart_attributes}} where meta_name='customer_name'
            ) b 
            on a.cart_uuid = b.cart_uuid

            left JOIN (
                SELECT cart_uuid,meta_name, meta_id FROM {{cart_attributes}} where meta_name='transaction_type'
            ) c 
            on a.cart_uuid = c.cart_uuid
            ";            
            $criteria->condition = "merchant_id=:merchant_id AND hold_order=:hold_order";
            $criteria->params  = array(
              ':merchant_id'=>intval(Yii::app()->merchant->merchant_id),
              ':hold_order'=>1
            );                        

            if(!empty($search)){
                $criteria->addSearchCondition("order_reference",$search);
            }
                        
            //dump("page=>$page");
            $criteria->order = "$sortby $sort";
            $count = AR_cart::model()->count($criteria); 
            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );        
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);    
            $page_count = $pages->getPageCount();                

            $data = []; $all_items = [];
                                    
            if($model = AR_cart::model()->findAll($criteria)){                 
                $transaction_list = CServices::Listing(Yii::app()->language);                
                foreach ($model as $items) {
                    $items_data = !empty($items->items_data)?explode(";",$items->items_data):[];
                    $data[] = [
                        'cart_uuid'=>$items->cart_uuid,
                        'order_reference'=>$items->order_reference,
                        'transaction_type'=>$items->transaction_type,
                        'transaction_name'=>isset($transaction_list[$items->transaction_type])?$transaction_list[$items->transaction_type]['service_name']:$items->transaction_type ,
                        'customer_name'=>!empty($items->customer_name)?$items->customer_name:'',
                        'qty'=>$items->qty,
                        'date_created'=>Date_Formatter::dateTime($items->date_created),
                        'items_data'=>$items_data,
                        'cart_url'=>Yii::app()->createAbsoluteUrl(BACKOFFICE_FOLDER."/pos/create_order")
                    ];
                    $all_items = array_merge($all_items,(array)$items_data);
                }
                
                $all_items = CCart::getAllItemsByToken($all_items,Yii::app()->language);

                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'page_count'=>$page_count,
                    'data'=>$data,                  
                    'all_items'=>$all_items
                ];
            } else $this->msg = t(HELPER_NO_RESULTS);

        } catch (Exception $e) {
		    $this->msg= t($e->getMessage());		    
		}	
		$this->responseJson();
    }

    public function actiondeleteHoldorder()
    {
        try {
            
            $id = trim(Yii::app()->input->post('id'));            
            $merchant_id = (integer) Yii::app()->merchant->merchant_id;

            $model = AR_cart::model()->find("merchant_id=:merchant_id AND cart_uuid=:cart_uuid",array(		  
                ':merchant_id'=>$merchant_id,
                ':cart_uuid'=>$id
            ));		
            if($model){
                $model->delete(); 
                $this->code = 1;
                $this->msg = "Ok";
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }	
        $this->responseJson();  
    }        

    public function actionOrderList()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);

            $filter_by = Yii::app()->input->post('filter_by');
            $limit = intval(Yii::app()->input->post('limit'));
            $page = intval(Yii::app()->input->post('page'));
            $q = trim(Yii::app()->input->post('q'));
            $request_from = trim(Yii::app()->input->post('request_from'));            

            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
				$page = $page-1;
			}

            $settings = OptionsTools::find(array('merchant_order_critical_mins'),$merchant_id);
    		$critical_mins = isset($settings['merchant_order_critical_mins'])?$settings['merchant_order_critical_mins']:0;
    		$critical_mins = intval($critical_mins);

    		$data = array(); $order_status = array(); $datetime=date("Y-m-d g:i:s a");

    		if($filter_by!="all"){
	    		$order_status = AOrders::getOrderTabsStatus($filter_by);                
                if(!$order_status && $filter_by=='scheduled'){
                    $order_status = AOrders::getOrderTabsStatus('new_order');
                }
    		}            

    		$status = COrders::statusList(Yii::app()->language);            
            $payment_status = COrders::paymentStatusList2(Yii::app()->language,'payment');

            $status_in = AOrders::getOrderTabsStatus('new_order');

            $payment_list = AttributesTools::PaymentProvider();

    		$criteria=new CDbCriteria();
		    $criteria->alias = "a";
		    $criteria->select = "a.order_id, a.order_uuid, a.client_id, a.status, a.order_uuid ,
		    a.payment_code, a.service_code,a.total, a.delivery_date, a.delivery_time, a.date_created, a.payment_code, a.total,
		    a.payment_status, a.is_view, a.is_critical, a.whento_deliver,
		    b.meta_value as customer_name,

		    IF(a.whento_deliver='now',
		      TIMESTAMPDIFF(MINUTE, a.date_created, NOW())
		    ,
		     TIMESTAMPDIFF(MINUTE, concat(a.delivery_date,' ',a.delivery_time), NOW())
		    ) as min_diff

		    ,
		    (
		       select sum(qty)
		       from {{ordernew_item}}
		       where order_id = a.order_id
		    ) as total_items,

            (
                select GROUP_CONCAT(cat_id,';',item_id,';',item_size_id,';',price,';',discount,';',qty)
                from {{ordernew_item}}
                where order_id = a.order_id
            ) as items
		    ";
		    $criteria->join='LEFT JOIN {{ordernew_meta}} b on  a.order_id=b.order_id ';
		    $criteria->condition = "a.merchant_id=:merchant_id AND meta_name=:meta_name ";
		    $criteria->params  = array(
		      ':merchant_id'=>intval($merchant_id),
		      ':meta_name'=>'customer_name'
		    );

		    if(is_array($order_status) && count($order_status)>=1){
		    	$criteria->addInCondition('status',(array) $order_status );
		    } else {
		    	$draft = AttributesTools::initialStatus();
		    	$criteria->addNotInCondition('status', array($draft) );
            }
            
            if(!empty($q)){
                $criteria->addSearchCondition('a.order_id', $q , true , "OR");                
                $criteria->addSearchCondition('b.meta_value', $q , true , "OR");                
            }

            if(!empty($request_from)){
                $criteria->addSearchCondition('a.request_from', $request_from );		        
            }

            switch ($filter_by) {
                case 'new_order':         
                    $criteria->addInCondition('a.whento_deliver',['now']);
                    break;            
                case "scheduled":     
                    $criteria->addInCondition('a.whento_deliver',['schedule']);
                    break;                       
            }

            $criteria->order = "date_created DESC";            
            
            $count=AR_ordernew::model()->count($criteria);
            $pages=new CPagination($count);
			$pages->pageSize=$limit;
			$pages->setCurrentPage( $page );
			$pages->applyLimit($criteria);
			$page_count = $pages->getPageCount();
            
		    $models = AR_ordernew::model()->findAll($criteria);

		    PrettyDateTime::$category='backend';

		    if($models){
		    	foreach ($models as $item) {

                    $items = array();
                    $items_row = explode(",",$item->items);
                    if(is_array($items_row) && count($items_row)>=1){
                        foreach ($items_row as $item_val) {
                            $itemd = explode(";",$item_val);
                            if(count($itemd)>1){
                            $items[] = array(
                              'cat_id'=>$itemd['0'],
                              'item_id'=>$itemd['1'],
                              'item_size_id'=>$itemd['2'],
                              'price'=>isset($itemd['3'])?$itemd['3']:0,
                              'discount'=>isset($itemd['4'])?$itemd['4']:0,
                              'qty'=>isset($itemd['5'])?$itemd['5']:0,
                            );
                            $all_items[]=$itemd['1'];
                            $all_item_size[]=$itemd['2'];
                            }
                        }
                    }

		    		$status_trans = $item->status;
		            if(array_key_exists($item->status, (array) $status)){
		               $status_trans = $status[$item->status]['status'];
		            }
		            
			        $payment_status_name = $item->payment_status;
			        if(array_key_exists($item->payment_status,(array)$payment_status)){
			            $payment_status_name = $payment_status[$item->payment_status]['title'];
			        }

			        if(array_key_exists($item->payment_code,(array)$payment_list)){
			            $item->payment_code = $payment_list[$item->payment_code];
			        }

			        $is_critical =  0;

			        if($item->whento_deliver=="schedule"){
			        	if($item->min_diff>0 && in_array($item->status,(array)$status_in) ){
			        		$is_critical = true;
			        	}
			        } else if ($critical_mins>0 && $item->min_diff>$critical_mins && in_array($item->status,(array)$status_in) ) {
			        	$is_critical = true;
			        }

		    		$data[]=array(
		    		  'order_id_raw'=>$item->order_id,
		    		  'order_id'=>t("Order #{{order_id}}",array('{{order_id}}'=>$item->order_id)),
                      'order_uuid'=>$item->order_uuid,
                      'total'=>Price_Formatter::formatNumber($item->total),
                      'date_created'=>PrettyDateTime::parse(new DateTime($item->date_created)),
                      'date_place'=>Date_Formatter::dateTime($item->date_created),
                      'description'=>t("{count} Items for {first_name}",[
                        '{count}'=>$item->total_items,
                        '{first_name}'=>$item->customer_name
                      ]),
                      'customer_name'=>$item->customer_name,
                      'total_items'=>$item->total_items,
                      'status'=>ucwords($status_trans),
                      'status_raw'=>$item->status,
                      'order_type'=>$item->service_code,
                      'is_view'=>$item->is_view,
                      'is_critical'=>$is_critical,
                      'payment_name'=>$item->payment_code,
                      'payment_status'=>$item->payment_status,
                      'payment_status_name'=>$payment_status_name,
                      'items'=>$items
		    		);
		    	}

                $item_details = COrders::orderItems2($all_items,Yii::app()->language);                
                $settings_tabs = COrders::OrderSettingTabs();
                $order_group_buttons = COrders::OrderGroupButtons();
                $order_buttons = COrders::OrderButtons(Yii::app()->language);
                $services_list = CServices::Listing( Yii::app()->language );                

		    	$this->code = 1; $this->msg = "ok";
		    	$this->details = [
                    'page_count'=>$page_count,
                    'page'=>$page,
                    'data'=>$data,
                    'status_list'=>COrders::statusList(),
                    'item_details'=>$item_details,
                    'settings_tabs'=>$settings_tabs,
                    'order_group_buttons'=>$order_group_buttons,
                    'order_buttons'=>$order_buttons,
                    'services_list'=>$services_list
                ];

		    } else {
		    	$this->msg = t("You don't have current orders.");
		    	$this->details = array(
		    	  'image_url'=>CMedia::themeAbsoluteUrl()."/assets/images/order-best-food@2x.png"
		    	);
		    }

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }    

    public function actionsaveguestnumber()
    {
        try {
            
            $room_uuid = Yii::app()->input->post('room_uuid');
            $table_uuid = Yii::app()->input->post('table_uuid');
            $guest_number = intval(Yii::app()->input->post('guest_number'));
            $cart_uuid = Yii::app()->input->post('cart_uuid');
            
            CCart::savedAttributes($cart_uuid,'room_uuid',$room_uuid);
            CCart::savedAttributes($cart_uuid,'table_uuid',$table_uuid);
            CCart::savedAttributes($cart_uuid,'guest_number',$guest_number);

            $this->code = 1;
            $this->msg = "Ok";
            
        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actionSendToKitchen()
    {
        try {
                                    
            $cart_uuid = Yii::app()->input->post("cart_uuid");
            $client_id = intval(Yii::app()->input->post("client_id"));
            $table_number = Yii::app()->input->post("table_number");
            $transaction_type = Yii::app()->input->post("transaction_type");
            $whento_deliver = Yii::app()->input->post("whento_deliver");
            $delivery_date = Yii::app()->input->post("delivery_date");
            $delivery_time = Yii::app()->input->post("delivery_time");                                           

            if($whento_deliver=="undefined"){
                $this->msg = t("Please select Now or to Schedule for later");
                $this->responseJson();
            }
            
            if($whento_deliver=="schedule"){
                $parser = new CDateTimeParser();
                $dateTime = $parser->parse($delivery_date, 'yyyy-MM-dd');
                if ($dateTime == false) {                                        
                    $this->msg = t("Invalid date");
                    $this->responseJson();
                } 
                                
                $parser = new CDateTimeParser();
                $timeObject = $parser->parse($delivery_time, 'h:mm:ss');
                if ($timeObject === false) {
                    $this->msg = t("Invalid time");
                    $this->responseJson();
                } 
            } else {
                $parser = new CDateTimeParser();
                $dateTime = $parser->parse($delivery_date, 'yyyy-MM-dd');
                if ($dateTime == false) {               
                    $delivery_date = CommonUtility::dateOnly();
                }
                $parser = new CDateTimeParser();
                $timeObject = $parser->parse($delivery_time, 'h:mm:ss');
                if ($timeObject === false) {
                    $delivery_time = '';
                }
            }

            $model = AR_cart::model()->find("cart_uuid=:cart_uuid",[
				":cart_uuid"=>$cart_uuid
			]);            
            if($model){	                

                if(empty($model->order_reference)){
					$order_reference = CommonUtility::createUniqueTransaction("{{cart}}",'order_reference',Yii::app()->params->tableside_prefix,5);
				} else $order_reference = $model->order_reference;   
                
                CCart::savedAttributes($cart_uuid,'timezone',Yii::app()->timezone);
                CCart::savedAttributes($cart_uuid,'whento_deliver',$whento_deliver);
                CCart::savedAttributes($cart_uuid,'delivery_date',$delivery_date);
                CCart::savedAttributes($cart_uuid,'delivery_time',$delivery_time);
                
                $merchant = CMerchants::get($model->merchant_id);                
                $model->kicthen_merchant_uuid = $merchant->merchant_uuid;

                $model->scenario = "send_order";                
                $model->order_reference = $order_reference;
				$model->table_uuid = $table_number; 
                $model->date_created = CommonUtility::dateNow();               
                if($model->save()){

                                    
					AR_cart::model()->updateAll(array(
						'send_order' =>1,					
                        'transaction_type'=>$transaction_type,
                        'table_uuid'=>$table_number,
                        'order_reference'=>$order_reference
					), "cart_uuid=:cart_uuid AND send_order=:send_order",[
						":cart_uuid"=>$cart_uuid,
						":send_order"=>0
					]);																
					
					$this->code = 1;
					$this->msg = t("Your order has been successfully sent to kitchen.");
				} else $this->msg = t(Helper_failed_save);			
            } else $this->msg = t("Cart details not found");			

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actiongetTableStatus()
    {
        try {
                         
             $merchant_id = Yii::app()->merchant->merchant_id;             
             $table_list = CBooking::getTableWithStatus($merchant_id);
             $this->code = 1;
             $this->msg = "Ok";
             $this->details = [
                'table_list'=>$table_list
             ];
        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actionUpdateTransactions()
    {
        try {
            
            $merchant_id = Yii::app()->merchant->merchant_id;             
            $cart_uuid = Yii::app()->input->post("cart_uuid");            
            $transaction_type = Yii::app()->input->post("transaction_type");
            $cart_transaction_type = Yii::app()->input->post("cart_transaction_type");

            $attrs = CCart::getAttributesAll($cart_uuid,['table_uuid']);            
            $table_uuid = isset($attrs['table_uuid'])?$attrs['table_uuid']:'';

            CCart::get($cart_uuid);
            CCart::savedAttributes($cart_uuid,Yii::app()->params->local_transtype,$transaction_type);

            AR_cart::model()->updateAll(array(
                'transaction_type' =>$transaction_type,
                'table_uuid'=>$table_uuid
            ), "cart_uuid=:cart_uuid",[
                ":cart_uuid"=>$cart_uuid
            ]);		

            if($transaction_type!="dinein"){                
                CCart::deleteAttributesAll($cart_uuid,['guest_number','table_uuid','room_uuid']);
                CBooking::updateTableStatus($merchant_id,$table_uuid);

                AR_cart::model()->updateAll(array(
                    'table_uuid' =>''
                ), "cart_uuid=:cart_uuid",[
                    ":cart_uuid"=>$cart_uuid
                ]);		
            }
                    

            $this->code = 1;
            $this->msg = t("Order updated");

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actionclearNewitems()
    {
        try {

            $merchant_id = Yii::app()->merchant->merchant_id;             
            $cart_uuid = Yii::app()->input->post("cart_uuid"); 
            
            CCart::clearNewItems($merchant_id,$cart_uuid);
            $this->code = 1;
            $this->msg = "Ok";

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actiongetPOSorders()
    {
        try {

            $order_type = isset($this->data['order_type'])?$this->data['order_type']:'';
            $transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';            
            $filters = [
                'order_type'=>$order_type
            ];            
            $merchant_id = Yii::app()->merchant->merchant_id;            
            $data = CPos::getSendOrder($transaction_type,$merchant_id,$filters,Yii::app()->language);             
            $this->code = 1;
            $this->msg = "Ok";
            $this->details = [
                'data'=>$data
            ];
        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actiondeleteorders()
    {
        try {            

            $merchant_id = Yii::app()->merchant->merchant_id;
            $cart_uuid = Yii::app()->input->post('cart_uuid');

            $attrs = CCart::getAttributesAll($cart_uuid,['table_uuid']);            
            $table_uuid = isset($attrs['table_uuid'])?$attrs['table_uuid']:'';

            CBooking::updateTableStatus($merchant_id,$table_uuid);

            $model = CPos::getCartOrderRefence($cart_uuid);
            $order_reference = $model?$model->order_reference:null;
                        
            Ckitchen::setKitchenStatus($order_reference,'cancelled',Yii::app()->merchant->merchant_uuid."-kitchen");
                                
            CCart::clear($cart_uuid);            
            
			$this->code = 1; $this->msg = "Ok";		
        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actionOrderhistoryattributes()
    {
        try {

            //$order_status_list = COrders::statusList(Yii::app()->language);    	
            $order_status_list = AttributesTools::statusList2(Yii::app()->language);    	            
            $order_status_list_value = CommonUtility::ArrayToLabelValue($order_status_list);            
            // $keys = array_keys($order_status_list);
            // $first_status = isset($keys[0])?$keys[0]:'';
            
            $data = AttributesTools::OrderTabs();

            $merchant_id = Yii::app()->merchant->merchant_id;            
            
            $printer_list = [];
            try {               
               $printer_list = FPinterface::getPrinterByMerchant($merchant_id);
            } catch (Exception $e) {
               //
            }
                                    
            $this->details = [
                'first_tab'=>$data['first_tab'],
                'tab_list'=>$data['list'],
                'order_status_list'=>$order_status_list,
                'order_status_list_value'=>$order_status_list_value,
                'printer_list'=>$printer_list
            ];
            $this->code = 1;
            $this->msg = "Ok";

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actionposorders()
    {
        try {
                         
            $merchant_id = Yii::app()->merchant->merchant_id;
            $startRow = isset($this->data['startRow'])?$this->data['startRow']:0;
            $fetchCount = isset($this->data['fetchCount'])?$this->data['fetchCount']:0;
            $filter = isset($this->data['filter'])?$this->data['filter']:[];
            $sortBy = isset($this->data['sortBy'])?  (!empty($this->data['sortBy'])?$this->data['sortBy']:'order_id') :'order_id';
            $descending = isset($this->data['descending'])?$this->data['descending']:false;
            $sortByAscDesc = $descending==true?'desc':'asc';
            
            
            $order_type = isset($filter['order_type'])?$filter['order_type']:'in_progress';            
            $search = isset($filter['q'])?$filter['q']:''; 
            $filter_order_status = isset($filter['filter_order_status'])?$filter['filter_order_status']:''; 
            $filter_customer = isset($filter['filter_customer'])?$filter['filter_customer']:''; 
            $filter_date = isset($filter['filter_date'])?$filter['filter_date']:''; 

            $status = AttributesTools::OrderStatusList($order_type);
                        
            $criteria = new CDbCriteria;
            $criteria->alias = "a";
            $criteria->select = "
               a.*,service_code as order_type,
               (
                  select meta_value from {{ordernew_meta}}
                  where order_id=a.order_id 
                  and meta_name ='customer_name'
                  limit 0,1
               ) as customer_name
            ";
            // $criteria->condition = "merchant_id=:merchant_id AND request_from=:request_from";
            // $criteria->params = [
            //     ':merchant_id'=>$merchant_id,
            //     ':request_from'=>'pos'
            // ];
            $criteria->condition = "merchant_id=:merchant_id";
            $criteria->params = [
                ':merchant_id'=>$merchant_id,                
            ];
            $criteria->addInCondition("request_from",['pos','tableside']);

            if(!empty($filter_order_status)){
                $criteria->addSearchCondition("status",$filter_order_status);
            } else {
                if(!is_null($status)){
                    $criteria->addInCondition("status",$status);
                }                        
            }            
            if(!empty($search)){
                $criteria->addSearchCondition("order_id",$search);
            }     
            if(!empty($filter_customer)){
                $criteria->addCondition("order_id IN (
                    select order_id from {{ordernew_meta}}
                    where order_id=a.order_id 
                    and meta_name ='customer_name'
                    and meta_value LIKE ".q("%$filter_customer%")."              
                )");
            }
            
            if(!empty($filter_date)){
                $from = isset($filter_date['from'])? Date_Formatter::date($filter_date['from'],"yyyy-MM-dd",true) :'';
                $to = isset($filter_date['to'])? Date_Formatter::date($filter_date['to'],"yyyy-MM-dd",true) :'';                
                $criteria->addCondition("DATE_FORMAT(date_created, '%Y-%m-%d') BETWEEN :start_date AND :end_date");
                $criteria->params[':start_date']=$from;
                $criteria->params[':end_date']=$to;
            }

            
            // dump($criteria);
            // die();

            $criteria->order = "$sortBy $sortByAscDesc";
            $totalItemCount = AR_ordernew::model()->count($criteria);
            $pagination = new CPagination($totalItemCount);
            $pagination->pageSize = $fetchCount;
            $pagination->currentPage = $startRow;

            $criteria->limit = $fetchCount;
            $criteria->offset =  $startRow;
                        
            if($model = AR_ordernew::model()->findAll($criteria)){
                $data = [];
                $services = COrders::servicesList(Yii::app()->language);       
                $status_list = COrders::statusList(Yii::app()->language);    	                         
                foreach ($model as $items) {
                    $data[] = [
                        'order_id'=>$items->order_id,
                        'order_uuid'=>$items->order_uuid,
                        'order_type'=> isset($services[$items->service_code])?$services[$items->service_code]['service_name']: t($items->service_code) ,                    
                        'customer_name'=>$items->customer_name,
                        'total'=>Price_Formatter::formatNumber($items->total),
                        'date_created'=>Date_Formatter::dateTime($items->date_created),
                        'status'=>isset($status_list[$items->status])?$status_list[$items->status]['status']:t($items->status) ,
                        'status_raw'=>str_replace(" ","-",$items->status),
                    ];
                }
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'total'=>intval($totalItemCount),
                    'order_uuid'=> isset($data[0])?  $data[0]['order_uuid']:'',
                    'data'=>$data,
                ];
            } else $this->msg = t("No available data");
        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actionsendReceipt()
    {
        try {
            
            $sending_type = Yii::app()->input->post('sending_type');
            $email_address = Yii::app()->input->post('email_address');
            $mobile_number = Yii::app()->input->post('mobile_number');
            $order_uuid = Yii::app()->input->post('order_uuid');
                                                
            if($sending_type=="email"){
                CNotifications::sendReceiptByEmail($order_uuid,$email_address);
            } else {
                CNotifications::sendReceiptByWhatsapp($order_uuid,$mobile_number);
            }

            $this->code = 1;
            $this->msg = t("Receipt Sent Successfully");

        } catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
    }

    public function actionsaveaddress()
    {
        try {

            $cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
            $data = isset($this->data['data'])?$this->data['data']:'';            
            CCart::savedAttributes($cart_uuid,'pos_address',json_encode($data));
            $this->code = 1;
            $this->msg = "Ok";
        } catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
    }

    public function actiongetNotifications()
	{
		try {								            
			$data = CNotificationData::getList( Yii::app()->merchant->merchant_uuid );			
			$this->code = 1; $this->msg = "ok";
			$this->details = $data;
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();		
	}

    public function actiongetTableneworder()
    {
        try {

            $data = []; $data_notification = [];
            $merchant_id = Yii::app()->merchant->merchant_id;         
            try {
              $data = CPos::getTableneworder($merchant_id);
            } catch (Exception $e) {}

            try {
                $data_notification = CPos::getPendingTablerequest($merchant_id);
            } catch (Exception $e) {}

            $datas = array_merge($data,$data_notification);
            
            if(is_array($datas) && count($datas)>=1){
                $this->code = 1;
                $this->msg = "Ok";
                $this->details =[
                    'data'=>$datas
                ];
            } else $this->msg = t(HELPER_NO_RESULTS);
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();		
    }

    public function actiongetPendingRequestList()
    {
        try {            
            
            $data = CPos::getPendingRequestList(Yii::app()->merchant->merchant_id);
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

    public function actionsetRequestcompleted()
	{
		try {
			            
            $request_id = isset($this->data['request_id'])?$this->data['request_id']:''; 
            if(is_array($request_id) && count($request_id)>=1){
                $request_id = CommonUtility::arrayToQueryParameters($request_id);
                $stmt = "
                UPDATE {{customer_request}}
                SET request_status = 'completed'
                WHERE request_id IN ($request_id)
                ";            
                Yii::app()->db->createCommand($stmt)->query();
                $this->code = 1;
                $this->msg = "Ok";

                $data = [];
                try {         
                  $data = CPos::getPendingRequestList(Yii::app()->merchant->merchant_id);
                } catch (Exception $e) { 
                }

                $this->details = [
                  'data'=>$data
                ];

            } else $this->msg = t("Invalid request id");
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}					
		$this->responseJson();
	}

    public function actionclearNotifications()
    {
        try {

            AR_notifications::model()->deleteAll('notication_channel=:notication_channel',array(
                ':notication_channel'=> Yii::app()->merchant->merchant_uuid
            ));
            $this->code = 1; $this->msg = "ok";        

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}					
		$this->responseJson();
    }

    public function actionupdatewhendelivery()
    {

    }

    public function actionprintusingthermal()
    {
        try {

            $merchant_id = Yii::app()->merchant->merchant_id;
            $printer_id = Yii::app()->input->post('printer_id');
            $order_uuid = Yii::app()->input->post('order_uuid');

            $model = AR_printer::model()->find("merchant_id=:merchant_id AND printer_id=:printer_id",[
                ":merchant_id"=>$merchant_id,
                ':printer_id'=>intval($printer_id)
            ]);
            if($model){
                $meta = AR_printer_meta::getMeta($printer_id,['printer_user','printer_ukey','printer_sn','printer_key']);				
                $printer_user = isset($meta['printer_user'])?$meta['printer_user']['meta_value1']:'';
                $printer_ukey = isset($meta['printer_ukey'])?$meta['printer_ukey']['meta_value1']:'';
                $printer_sn = isset($meta['printer_sn'])?$meta['printer_sn']['meta_value1']:'';
                $printer_key = isset($meta['printer_key'])?$meta['printer_key']['meta_value1']:'';

                $order_id = 0;
                $summary = array(); $order_status = array();                                
                $order_delivery_status = array(); $merchant_info=array();
                $order = array(); $items = array();

                COrders::getContent($order_uuid,Yii::app()->language);

                $merchant_info = COrders::getMerchant($merchant_id,Yii::app()->language);				
                $items = COrders::getItems();				
                $summary = COrders::getSummary();
                $order = COrders::orderInfo();
				$order_id = $order['order_info']['order_id'];

				$credit_card_details = '';
				$payment_code = $order['order_info']['payment_code'];
				if($payment_code=="ocr"){
					try {
						$credit_card_details = COrders::getCreditCard2($order_id);			
						$order['order_info']['credit_card_details'] = $credit_card_details;		
					} catch (Exception $e) {
						//
					}
				}				
								
               $tpl = FPtemplate::ReceiptTemplate(
                  $model->paper_width,
                  $order['order_info'],
                  $merchant_info,
                  $items,$summary
               );   	
			   $stime = time();
               $sig = sha1($printer_user.$printer_ukey.$stime);               
               $result = FPinterface::Print($printer_user,$stime,$sig,$printer_sn,$tpl);

			   $model = new AR_printer_logs();
			   $model->order_id = intval($order_id);
			   $model->merchant_id = intval($merchant_id);
			   $model->printer_number = $printer_sn;
			   $model->print_content = $tpl;
			   $model->job_id = $result;
			   $model->status = 'process';
			   $model->save();
               
               $this->code = 1;
               $this->msg = t("Request succesfully sent to printer");
               $this->details = $result;		   

                
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}					
		$this->responseJson();
    }

    public function actionreverseGeocoding()
	{						
		try {

            $lat = Yii::app()->input->post('lat');
		    $lng = Yii::app()->input->post('lng');
		    $next_steps =  Yii::app()->input->post('next_steps');
			
		   MapSdk::$map_provider = Yii::app()->params['settings']['map_provider'];		   
		   MapSdk::setKeys(array(
		     'google.maps'=>Yii::app()->params['settings']['google_geo_api_key'],
		     'mapbox'=>Yii::app()->params['settings']['mapbox_access_token'],
		   ));
		   
		   if(MapSdk::$map_provider=="mapbox"){
			   MapSdk::setMapParameters(array(			    
			    'limit'=>1
			   ));
		   }
		   
		   $resp = MapSdk::reverseGeocoding($lat,$lng);		   
		   
		   $this->code =1; $this->msg = "ok";
		   $this->details = array(
		     'next_action'=>$next_steps,		     		     
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

	public function actionwifiPrint()
	{
		try {
			            
			$printer_id = Yii::app()->input->post('printer_id');
			$order_uuid = Yii::app()->input->post('order_uuid');
			$model = AR_printer::model()->find("printer_id=:printer_id",[               
                ':printer_id'=>intval($printer_id)
            ]);            
			if($model){
				
				COrders::getContent($order_uuid,Yii::app()->language);					
				$items = COrders::getItems();				
                $summary = COrders::getSummary();
                $order = COrders::orderInfo();
				$order_info = isset($order['order_info'])?$order['order_info']:[];
				$merchant_id = isset($order_info['merchant_id'])?$order_info['merchant_id']:0;
				$merchant_info = CMerchants::getMerchantInfo($merchant_id);								

				$order_type = $order['order_info']['order_type'];
				$order_table_data = [];
				if($order_type=="dinein"){
					$order_table_data = COrders::orderMeta(['table_id','room_id','guest_number']);	
					$room_id = isset($order_table_data['room_id'])?$order_table_data['room_id']:0;							
					$table_id = isset($order_table_data['table_id'])?$order_table_data['table_id']:0;							
					try {
						$table_info = CBooking::getTableByID($table_id);
						$order_table_data['table_name'] = $table_info->table_name;
					} catch (Exception $e) {
						$order_table_data['table_name'] = t("Unavailable");
					}				
					try {
						$room_info = CBooking::getRoomByID($room_id);					
						$order_table_data['room_name'] = $room_info->room_name;
					} catch (Exception $e) {
						$order_table_data['room_name'] = t("Unavailable");
					}				
				}			
				$order_info['order_table_data'] = $order_table_data;

				ThermalPrinterFormatter::setPrinter([
					'ip_address'=>$model->service_id,
					'port'=>$model->characteristics,
					'print_type'=>$model->print_type,
					'character_code'=>$model->character_code,
					'paper_width'=>$model->paper_width,
				]);
				ThermalPrinterFormatter::setItems($items);
				ThermalPrinterFormatter::setSummary($summary);
				ThermalPrinterFormatter::setOrderInfo($order_info);
				ThermalPrinterFormatter::setMerchant($merchant_info);
				$data = ThermalPrinterFormatter::RawReceipt();				
				
				$this->code = 1;
				$this->msg = t("Request succesfully sent to printer");
				$this->details = [
					'data'=>$data
				];

			} else $this->msg = t(HELPER_RECORD_NOT_FOUND);
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();
	}

    public function actionwifiPrintOT()
    {
        try {
            
            $printer_id = Yii::app()->input->post('printer_id');
            $cart_uuid = Yii::app()->input->post('cart_uuid');            

            $model = AR_printer::model()->find("printer_id=:printer_id",[               
                ':printer_id'=>intval($printer_id)
            ]);  
            if($model){

                ThermalPrinterFormatter::setPrinter([
					'ip_address'=>$model->service_id,
					'port'=>$model->characteristics,
					'print_type'=>$model->print_type,
					'character_code'=>$model->character_code,
					'paper_width'=>$model->paper_width,
				]);

                $printer_settings = ThermalPrinterFormatter::getPrinter();                
                $data = CPos::getPOSOrder($cart_uuid,Yii::app()->language);                                                
                $data = reset($data);                                   
                $results = ThermalPrinterFormatter::RawKitchenPOS_Orders($data,$printer_settings);                

                $this->code = 1;
				$this->msg = t("Request succesfully sent to printer");
				$this->details = [
					'data'=>$results
				];

            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();
    }

    public function actionFPPrintOT()
    {
        try {

            $merchant_id = Yii::app()->merchant->merchant_id;
            $printer_id = Yii::app()->input->post('printer_id');
            $cart_uuid = Yii::app()->input->post('cart_uuid');            
            $model = AR_printer::model()->find("printer_id=:printer_id",[               
                ':printer_id'=>intval($printer_id)
            ]);  
            if($model){
                
                $printer = CommonUtility::getPrinterDetails($merchant_id,$printer_id);                
                $paper_width = isset($printer['paper_width'])?$printer['paper_width']:'';
                $printer_user = isset($printer['printer_user'])?$printer['printer_user']:'';
                $printer_sn = isset($printer['printer_sn'])?$printer['printer_sn']:'';
                $printer_ukey = isset($printer['printer_ukey'])?$printer['printer_ukey']:'';

                $data = CPos::getPOSOrder($cart_uuid,Yii::app()->language);
                $data = reset($data);

                $tpl = FPtemplate::TicketOrder($data,$paper_width);                

                $stime = time();
                $sig = sha1($printer_user.$printer_ukey.$stime);               
                $result = FPinterface::Print($printer_user,$stime,$sig,$printer_sn,$tpl);

                $model = new AR_printer_logs();
                $model->order_id = 0;
                $model->merchant_id = intval($merchant_id);
                $model->printer_number = $printer_sn;
                $model->print_content = $tpl;
                $model->job_id = $result;
                $model->status = 'process';
                $model->save();

                $this->code = 1;
                $this->msg = t("Request succesfully sent to printer");
                $this->details = $result;

            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);
        } catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();
    }

    public function actionfinditembarcode()
    {
        try {            

            $merchant_id = Yii::app()->merchant->merchant_id;
            $barcode =  Yii::app()->request->getPost('barcode', null);            
            $customer_id =  Yii::app()->request->getPost('customer_id', null);  
            if(!$barcode){
                $this->msg = t("Invalid barcode");
                $this->responseJson();
            }
            
            $results = AttributesTools::getItemByBarcode($barcode,$merchant_id,Yii::app()->language);
            if(!$results){
                $this->msg = t("Barcode not found");
                $this->responseJson();
            }
            $results['customer_id'] = $customer_id;
            $this->code = 1;
            $this->msg = "Ok";
            $this->details = [
                'data'=>$results
            ];
        } catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();
    }

    public function actiontestprintwifi()
    {
        try {

            $merchant_id = Yii::app()->merchant->merchant_id;            
            $model = AR_printer::model()->find("merchant_id=:merchant_id AND printer_model=:printer_model AND auto_print=:auto_print",[
                ":merchant_id"=>$merchant_id,		
                ':printer_model'=>'wifi',
                ':auto_print'=>1
		    ]);		

            if(!$model){
                $this->msg = t("There is no default printer");
                $this->responseJson();
            }

            ThermalPrinterFormatter::setPrinter([
                'ip_address'=>$model->service_id,
                'port'=>$model->characteristics,
                'print_type'=>$model->print_type,
                'character_code'=>$model->character_code,
                'paper_width'=>$model->paper_width,
			]);
            $printer_settings = ThermalPrinterFormatter::getPrinter();
            
            $data = array(                
                "hold_order_reference" => time(),                
                "customer_name" => "Walk-in Customer",                
                "transaction_type_raw" => "takeout",
                "transaction_type" => "takeout",
                "timezone" => "Asia/Manila",                
                "date_pretty" => "15.06.2025, 09:57",                
                "items" => array(
                    array(                        
                        "item_name" => "Breakfast Rice Bowl Duo",                        
                        "qty" => 1,                        
                    )
                )
            );
            $results = ThermalPrinterFormatter::RawKitchenPOS_Orders($data,$printer_settings);                            
            $this->code = 1;
            $this->msg = t("Request succesfully sent to printer");
            $this->details = [
                'data'=>$results
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
			$merchant_id = Yii::app()->merchant->merchant_id;  
						
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
			$cart_merchant_id = CCart::getMerchantId($cart_uuid);
			
			if($cart_merchant_id!=$merchant_id){
				$this->msg = t("Merchant cart invalid");
				$this->responseJson();
			}

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
    
}
// end class