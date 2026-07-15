<?php
require_once "mercadopago/vendor/autoload.php";
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Net\MPSearchRequest;

require 'php-jwt/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ApiController extends CController
{

    public $code=2,$msg,$details,$data;			
    
    // public function responseJson()
    // {
    // 	header('Content-type: application/json');
	// 	$resp=array('code'=>$this->code,'msg'=>$this->msg,'details'=>$this->details);
	// 	echo CJSON::encode($resp);
	// 	Yii::app()->end();
    // }        

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
	
	public function beforeAction($action)
	{								
		$method = Yii::app()->getRequest()->getRequestType();							    
		if($method=="PUT"){            
			$this->data = Yii::app()->input->xssClean(json_decode(file_get_contents('php://input'), true));
		} else $this->data = Yii::app()->input->xssClean($_POST);						
		return true;
	}

    public function init()
    {        
        Yii::app()->params['settings'] = OptionsTools::find(array(
                'website_date_format_new','website_time_format_new','map_provider','google_geo_api_key','google_maps_api_key',
                'mapbox_access_token','home_search_unit_type','website_timezone_new','website_title',
                'captcha_customer_signup','website_address','website_contact_phone','website_contact_email',
                'image_resizing','image_driver','website_logo','review_image_resize_width','merchant_specific_country',
                'merchant_enabled_registration_capcha','registration_program','registration_terms_condition',
                'enabled_language_bar_front','allow_return_home','enabled_google_analytics','google_analytics_tracking_id',
                'enabled_fb_pixel','fb_pixel_id','enabled_auto_pwa_redirect','pwa_url','android_download_url','ios_download_url',
                'enabled_home_steps','enabled_home_promotional','enabled_signup_section','enabled_mobileapp_section','enabled_social_links',
                'facebook_page','twitter_page','google_page','instagram_page','linkedin_page','enabled_auto_detect_address','runactions_enabled',
                'cookie_consent_enabled','menu_layout','category_position','merchant_enabled_registration','merchant_allow_login_afterregistration',
                'multicurrency_enabled','multicurrency_enabled_hide_payment','multicurrency_enabled_checkout_currency','points_enabled','points_earning_rule',
                'points_earning_points','points_minimum_purchase','points_maximum_purchase','points_redeemed_points','points_redeemed_value',
                'points_minimum_redeemed','points_maximum_redeemed','points_redemption_policy','points_registration','points_review','points_first_order',
                'points_booking','booking_time_format','points_cover_cost','site_user_avatar','site_merchant_avatar','site_food_avatar',
                'default_location_lat','default_location_lng','digitalwallet_enabled','digitalwallet_enabled_topup',
                'digitalwallet_topup_minimum','digitalwallet_topup_maximum','digitalwallet_transaction_limit','digitalwallet_refund_to_wallet',
                'chat_enabled','chat_enabled_merchant_delete_chat','points_use_thresholds','points_expiry','enabled_include_utensils','enabled_review','address_format_use',
                'password_reset_options','mobilephone_settings_default_country','mobilephone_settings_country','yandex_javascript_api','yandex_language',
                'yandex_geosuggest_api','yandex_geocoder_api','yandex_static_api','yandex_distance_api','cancel_order_enabled','custom_field_enabled',
                'home_search_mode','location_default_country','location_searchtype','location_enabled_map_selection'
        ));			

        $setttings = Yii::app()->params['settings'];				                                        
        
        /*SET TIMEZONE*/
        $timezone = Yii::app()->params['settings']['website_timezone_new'];		
        if (is_string($timezone) && strlen($timezone) > 0){
            Yii::app()->timeZone=$timezone;		   
        }
        Price_Formatter::init();	                
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
                    'app_addPayment'
                 ), 
				 'expression' => array('AppIdentity','verifyCustomer')
			 ), 
		 );
	}

    public function actiongetIdentificationList()
    {
        try {

            $payment_code = MercadopagoModule::paymentCode();
            $merchant_id = Yii::app()->request->getQuery('merchant_id', 0);
            $merchant_type = Yii::app()->request->getQuery('merchant_type', '');
            $credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,$merchant_type);	
            $credentials = $credentials[$payment_code] ?? '';
            $access_token = $credentials['attr2'] ?? '';
            $is_live = $credentials['is_live'] ?? false;
            $is_live = $is_live==1?true:false;            

            $app = Yii::app()->request->getQuery('app', false);
            $app = $app==1?true:false;            

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.mercadopago.com/v1/identification_types');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Authorization: Bearer '.$access_token;
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                $this->msg = t("Error : ")." ".curl_error($ch);
                $this->responseJson();
            }
            curl_close($ch);

            $json = !empty($result)? json_decode($result,true):false;
            if($json){
                $status = $json['status'] ?? null;
                $message = $json['message'] ?? null;
                if($status==404){
                    throw new Exception($message);
                }
                $default = isset($json[0])? (isset($json[0])?$json[0]['id']:null) :null;
                if($app){
                    $result = [];
                    foreach ($json as $items) {
                        $result[] = [
                            'label'=>$items['name'],
                            'value'=>$items['id'],
                        ];
                    }
                }                
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'data'=>$result,
                    'default'=>$default
                ];
            } else $this->msg = t("Failed ".$json);
        } catch (Exception $e) {
            $this->msg = $e->getMessage();  
        }
        $this->responseJson();
    }

    public function actionapp_addPayment()
    {
        if(Yii::app()->user->isGuest){
            $this->msg = t("Invalid login token");
            $this->responseJson();
        }
        $this->actionaddPayment();
    }

    public function actionaddPayment()
    {
        try {
            
            $first_name = Yii::app()->input->post('first_name');
            $last_name = Yii::app()->input->post('last_name');
            $email_address = Yii::app()->input->post('email_address');
            $identification_type = Yii::app()->input->post('identification_type');
            $identification_number = Yii::app()->input->post('identification_number');
            $merchant_id = Yii::app()->input->post('merchant_id');

            if(empty($email_address)){
                $this->msg = t("Email address is required");
                $this->responseJson();
            }

            $payment_code = MercadopagoModule::paymentCode();

            $find = AR_client_payment_method::model()->find("client_id=:client_id AND payment_code=:payment_code AND merchant_id=:merchant_id AND attr2=:attr2",[
                ':client_id'=>intval(Yii::app()->user->id),
                ':payment_code'=>$payment_code,
                ':merchant_id'=>$merchant_id,
                ':attr2'=>$email_address
            ]);
            if($find){
                $this->msg = t("Payment and email already exist. choose another email address");
                $this->responseJson();
            }
            
            $gateway =  AR_payment_gateway::model()->find('payment_code=:payment_code', array(':payment_code'=>$payment_code)); 

            $model_method = new AR_client_payment_method;
            $model_method->client_id = intval(Yii::app()->user->id);
            $model_method->payment_code = $payment_code;
            $model_method->as_default = 1;
            $model_method->attr1 = $gateway?$gateway->payment_name:'';
            $model_method->attr2 = $email_address;			
            $model_method->merchant_id = intval($merchant_id);

            $model_method->method_meta = array(
                array(
                  'meta_name'=>'first_name',
                  'meta_value'=>$first_name,
                  'date_created'=>CommonUtility::dateNow(),
                ),                
                array(
                    'meta_name'=>'last_name',
                    'meta_value'=>$last_name,
                    'date_created'=>CommonUtility::dateNow(),
                ),                
                array(
                    'meta_name'=>'email_address',
                    'meta_value'=>$email_address,
                    'date_created'=>CommonUtility::dateNow(),
                ),                
                array(
                    'meta_name'=>'identification_type',
                    'meta_value'=>$identification_type,
                    'date_created'=>CommonUtility::dateNow(),
                ),                
                array(
                    'meta_name'=>'identification_number',
                    'meta_value'=>$identification_number,
                    'date_created'=>CommonUtility::dateNow(),
                ),                
            );
            
            if($model_method->save()){
                $this->code = 1; 
                $this->msg = "OK";			    	
            } else $this->msg = CommonUtility::parseError( $model_method->getErrors());	
            
        } catch (Exception $e) {
            $this->msg = $e->getMessage();  
        }
        $this->responseJson();
    }

    public function actioncreatecheckout()
    {
        try {
                                
            $error = '';
            $redirect_url = null;
            $order_uuid = Yii::app()->request->getQuery('order_uuid', '');
			$cart_uuid = Yii::app()->request->getQuery('cart_uuid', '');
			$payment_uuid = Yii::app()->request->getQuery('payment_uuid', ''); 						
			$request_from = Yii::app()->request->getQuery('request_from', 'web');                          
            $return_url = Yii::app()->request->getQuery('return_url', '');
            $return_url =  rtrim($return_url, "/"); 
            $return_url = (empty($return_url) || $return_url === 'null') ? '' : $return_url;         
            
            if($request_from=="app"){
                if(!empty($return_url)){
                    $redirect_url = $return_url;
                } else $redirect_url = APP_CUSTOM_URL_SCHEME."://payment-callback?status=cancel";
            } else {
                $redirect_url = Yii::app()->createAbsoluteUrl("/account/checkout");
            }

            $order = COrders::get($order_uuid);					            
			$merchant_id = $order->merchant_id;
			$payment_code = $order->payment_code;
			$client_id = $order->client_id;			
			$merchants = CMerchantListingV1::getMerchant( $merchant_id );			
			$credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,$merchants->merchant_type);						
            
			$credentials = $credentials[$payment_code] ?? null;
            $access_token = $credentials['attr2'] ?? null;
            $public_key = $credentials['attr1'] ?? null;
            $is_live = $credentials['is_live'] ?? null;
            $is_live = $is_live==1?true:false;
            
            $exchange_rate = $order->exchange_rate>0?$order->exchange_rate:1;

            if($order->amount_due>0){
				$total = Price_Formatter::convertToRaw( ($order->amount_due*$exchange_rate),2);
			 } else $total = Price_Formatter::convertToRaw( ($order->total*$exchange_rate),2);

			 $multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
             $multicurrency_enabled = $multicurrency_enabled==1?true:false;		   	
             $enabled_checkout_currency = isset(Yii::app()->params['settings']['multicurrency_enabled_checkout_currency'])?Yii::app()->params['settings']['multicurrency_enabled_checkout_currency']:false;
             $enabled_force = $multicurrency_enabled==true? ($enabled_checkout_currency==1?true:false) :false;

             $use_currency_code = $order->use_currency_code;					  
             if($enabled_force){                
                if($force_result = CMulticurrency::getForceCheckoutCurrency($order->payment_code,$use_currency_code)){					 					 
                   $use_currency_code = $force_result['to_currency'];
                   $total = Price_Formatter::convertToRaw($total*$force_result['exchange_rate'],2);
                }                
             }			  	
             
             $return_params = [
                'order_uuid'=>$order_uuid,
                'cart_uuid'=>$cart_uuid,
                'request_from'=>$request_from,
                'return_url'=>$return_url
            ];

            $ipn_params = [
                'merchant_id'=>$merchant_id,
                'payment_type'=>"purchase_items"
            ];

            $payment_info = CPayments::getPaymentMethodMeta($payment_uuid,$client_id);     
            $payer = [                
                'name'=>isset($payment_info['first_name'])?$payment_info['first_name']:'',
                'surname'=>isset($payment_info['last_name'])?$payment_info['last_name']:'',                    
                "email"=>isset($payment_info['email_address'])?$payment_info['email_address']:'',
            ];
            if(!empty($payment_info['identification_number'])){
                $payer['identification'] = [
                    'type'=>isset($payment_info['identification_type'])?$payment_info['identification_type']:'',
                    'number'=>isset($payment_info['identification_number'])?$payment_info['identification_number']:'',
                ];                
            }            
            
            $params = [
                "items" => [
                    [
                        "title" => t("Payment to order #{order_id}",['{order_id}'=>$order->order_id]),
                        "quantity" => 1,
                        "unit_price" =>  floatval($total)
                    ]
                ],       
                "payer"=>$payer,                     
                "back_urls" => [
                    "success" => Yii::app()->createAbsoluteUrl("/$payment_code/api/verifypayment?".http_build_query($return_params)),
                    "failure" => Yii::app()->createAbsoluteUrl("/$payment_code/api/verifypayment?".http_build_query($return_params)),
                    "pending" => Yii::app()->createAbsoluteUrl("/$payment_code/api/verifypayment?".http_build_query($return_params)),
                ],
                "auto_return" => "approved",
                'installments' => 1,
                'notification_url'=>Yii::app()->createAbsoluteUrl("/$payment_code/api/webhook?".http_build_query($ipn_params)),                
                "external_reference"=> $order->order_uuid,
                'payment_methods'=>[
                    'installments'=>1
                ]
            ];                   
            if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
                $params['notification_url'] = 'https://bastisapp.com/testcode/mercadopago.php?'.http_build_query($ipn_params);
            }             

            MercadoPagoConfig::setAccessToken($access_token);            
            MercadoPagoConfig::setRuntimeEnviroment( $is_live? MercadoPagoConfig::SERVER : MercadoPagoConfig::LOCAL);
    
            $unique_key = CommonUtility::generateUIID();
            $client = new PreferenceClient();
            $request_options = new RequestOptions();
            $request_options->setCustomHeaders(["X-Idempotency-Key: $unique_key"]);

            try {
                                
                $preference = $client->create($params,$request_options);            
                $payment_reference_id =  $preference->id;                        
                $this->render("payment-form",[
                    'payment_reference_id'=>$payment_reference_id,
                    'public_key'=>$public_key,
                    'amount_to_pay'=>Price_Formatter::formatNumber($total),
                    'redirect_url'=>$redirect_url
                ]);
                Yii::app()->end();
            } catch (MPApiException $e) {                
                $err_model = $e->getApiResponse()->getContent();
                $error = isset($err_model['message']) ? $err_model['message'] : t("Undefined Errors");
            } catch (\Exception $e) {
                $error =  $e->getMessage();
            }                        
        } catch (Exception $e) {
            $error = $e->getMessage();  
        }

        $this->redirect(CommonUtility::failedRedirect($request_from,$return_url,$error));
    }
    
    public function actionverifypayment()
    {
        try {
                                    
            $error = ''; $payment_code = MercadopagoModule::paymentCode();
            $order_uuid = Yii::app()->request->getQuery('order_uuid', null); 
            $cart_uuid = Yii::app()->request->getQuery('cart_uuid', null); 
            $payment_uuid = Yii::app()->request->getQuery('payment_uuid', null); 
            $order_uuid = Yii::app()->request->getQuery('order_uuid', null); 
            $order_uuid = Yii::app()->request->getQuery('order_uuid', null); 
            $request_from = Yii::app()->request->getQuery('request_from', null); 
            $return_url = Yii::app()->request->getQuery('return_url', null);     
            

            $payment_id = Yii::app()->request->getQuery('payment_id', null);
            $status = Yii::app()->request->getQuery('status', null);
            $preference_id = Yii::app()->request->getQuery('preference_id', null);

            if($request_from=="app"){
                if(!empty($return_url)){
                    $redirect_url = $return_url;
                } else $redirect_url = APP_CUSTOM_URL_SCHEME."://payment-callback?status=cancel";
            } else {
                $redirect_url = Yii::app()->createAbsoluteUrl("/account/checkout");
            }
           
            if( $status=="null" || empty($status)){                
                $this->redirect($redirect_url);
                Yii::app()->end();
            } else if ($status === 'rejected' || $status=='failure') {                
                $error = $status;
                $this->redirect(CommonUtility::failedRedirect($request_from,$return_url,$error));
                Yii::app()->end();
            } elseif ( $status=='pending' || $status=='in_process'){                                

                $back_url = null;
                if($request_from=="app"){
                    if(!empty($return_url)){
                        $back_url = $return_url;
                    } else $back_url = APP_CUSTOM_URL_SCHEME."://payment-callback?status=home";
                } else {
                    $back_url = Yii::app()->createAbsoluteUrl("/");
                }

                try {
                    CCart::clear($cart_uuid);
                } catch (Exception $e) { }			                                
                $this->render('paymen-pending',[
                    'redirect_url'=>$back_url
                ]);
                return;
            }            
                        
            $data  = COrders::getWithoutCache($order_uuid); 
            $payment_code = $data->payment_code;
            $merchant_id = $data->merchant_id;
            $merchant = CMerchantListingV1::getMerchant( $merchant_id );			
			$credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,$merchant->merchant_type);									            
			$credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';
                        
            $access_token = isset($credentials['attr2'])?$credentials['attr2']:'';
            $public_key = isset($credentials['attr1'])?$credentials['attr1']:'';
            $is_live = isset($credentials['is_live'])?$credentials['is_live']:false;
            $is_live = $is_live==1?true:false;
                     
            MercadoPagoConfig::setAccessToken($access_token);
            MercadoPagoConfig::setRuntimeEnviroment( $is_live? MercadoPagoConfig::SERVER : MercadoPagoConfig::LOCAL);
                        
            $client = new PaymentClient();            
            $payment = $client->get($payment_id);                        
            if($payment->status!="approved"){
                $this->redirect($redirect_url);
                return;
            }
            
            $order_status = $data->status;
            $payment_code = $data->payment_code;
            $data->scenario = "new_order";                
            $data->status = COrders::newOrderStatus();
            $data->payment_status = CPayments::paidStatus();
            $data->cart_uuid = $cart_uuid;        
            $data->save();            
           

            if($request_from=="app"){
                if(!empty($return_url)){                    
                    $redirect_url = $return_url."/account/trackorder?".http_build_query(['order_uuid'=>$order_uuid]);                    
                } else $redirect_url = APP_CUSTOM_URL_SCHEME."://payment-callback?".http_build_query(['status'=>'successful','order_id'=>$order_uuid]);
            } else $redirect_url = Yii::app()->createAbsoluteUrl("/orders/index?".http_build_query(['order_uuid'=>$order_uuid]));

            $model = new AR_ordernew_transaction;
            $model->order_id = $data->order_id;
            $model->merchant_id = $data->merchant_id;
            $model->client_id = $data->client_id;
            $model->payment_code = $data->payment_code;
            $model->trans_amount = $data->amount_due>0? $data->amount_due: $data->total;
            $model->currency_code = $data->base_currency_code;
            $model->to_currency_code = $data->use_currency_code;
            $model->exchange_rate = $data->exchange_rate;
            $model->admin_base_currency = $data->admin_base_currency;
            $model->exchange_rate_merchant_to_admin = $data->exchange_rate_merchant_to_admin;
            $model->exchange_rate_admin_to_merchant = $data->exchange_rate_admin_to_merchant;
            $model->payment_reference = $payment_id;
            $model->status = CPayments::paidStatus();            
            $model->reason = '';   
            if($order_status=='draft'){
                $model->save();    
            }            
            $this->redirect($redirect_url);		
            Yii::app()->end();
        } catch (MPApiException $e) {            
            $err_model = $e->getApiResponse()->getContent();
            $error = isset($err_model['message']) ? $err_model['message'] : t("Undefined Errors");
        } catch (\Exception $e) {
            $error = $e->getMessage();              
        }        

        $this->redirect(CommonUtility::failedRedirect($request_from,$return_url,$error));
    }   


    // WALLET

    public function actionprocesspayment()
    {
        try {
            $error = '';        
            
            $data = Yii::app()->input->get("data");
            $request_from = Yii::app()->request->getQuery('request_from', 'web');                          
            $return_url = Yii::app()->request->getQuery('return_url', '');                         
            $return_url =  rtrim($return_url, "/"); 
            $return_url = (empty($return_url) || $return_url === 'null') ? '' : $return_url; 

            $jwt_key = new Key(CRON_KEY, 'HS256');
			$decoded = (array) JWT::decode($data, $jwt_key);                         
            
            $payment_code = isset($decoded['payment_code'])?$decoded['payment_code']:'';
			$payment_name = isset($decoded['payment_name'])?$decoded['payment_name']:'';
			$merchant_id = isset($decoded['merchant_id'])?$decoded['merchant_id']:'';
			$merchant_type = isset($decoded['merchant_type'])?$decoded['merchant_type']:'';
			$payment_description = isset($decoded['payment_description'])?$decoded['payment_description']:'';
			$payment_description_raw = isset($decoded['payment_description_raw'])?$decoded['payment_description_raw']:'';
			$transaction_description_parameters = isset($decoded['transaction_description_parameters'])?$decoded['transaction_description_parameters']:'';
			$amount = isset($decoded['amount'])?$decoded['amount']:'';			
			$transaction_amount = isset($decoded['transaction_amount'])?$decoded['transaction_amount']:0;			
			$currency_code = isset($decoded['currency_code'])?$decoded['currency_code']:'';
			$payment_type = isset($decoded['payment_type'])?$decoded['payment_type']:'';		
			$reference_id = isset($decoded['reference_id'])?$decoded['reference_id']:'';	
            $back_url = isset($decoded['failed_url'])?$decoded['failed_url']:'';	            
            $successful_url = isset($decoded['successful_url'])?$decoded['successful_url']:'';	
            $payment_uuid = isset($decoded['payment_uuid'])?$decoded['payment_uuid']:'';
            $customer_details = isset($decoded['customer_details'])?(array)$decoded['customer_details']:'';            
            $client_id = isset($customer_details['client_id'])?$customer_details['client_id']:null;            

			$payment_details = isset($decoded['payment_details'])?(array)$decoded['payment_details']:'';
			$payment_customer_id = isset($payment_details['payment_customer_id'])?$payment_details['payment_customer_id']:'';
			$payment_method_id = isset($payment_details['payment_method_id'])?$payment_details['payment_method_id']:'';					            
			
			$credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,$merchant_type);			            
			$credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';            
            $access_token = isset($credentials['attr2'])?$credentials['attr2']:'';
            $public_key = isset($credentials['attr1'])?$credentials['attr1']:'';
            $is_live = isset($credentials['is_live'])?$credentials['is_live']:false;
            $is_live = $is_live==1?true:false;
            
            $return_params = [
                'payment_ref'=>$reference_id,
                'request_from'=>$request_from,
                'return_url'=>$return_url
            ];

            $ipn_params = [
                'merchant_id'=>$merchant_id,
                'payment_type'=>"load_wallet"
            ];
            
            $payment_info = CPayments::getPaymentMethodMeta($payment_uuid,$client_id);
            $payer = [                
                'name'=>isset($payment_info['first_name'])?$payment_info['first_name']:'',
                'surname'=>isset($payment_info['last_name'])?$payment_info['last_name']:'',                    
                "email"=>isset($payment_info['email_address'])?$payment_info['email_address']:'',
            ];
            if(!empty($payment_info['identification_number'])){
                $payer['identification'] = [
                    'type'=>isset($payment_info['identification_type'])?$payment_info['identification_type']:'',
                    'number'=>isset($payment_info['identification_number'])?$payment_info['identification_number']:'',
                ];                
            }            
            
            $params = [
                "items" => [
                    [
                        "title" => $payment_description,
                        "quantity" => 1,
                        "unit_price" =>  floatval($amount)
                    ]
                ],       
                "payer"=>$payer,                     
                "back_urls" => [
                    "success" => Yii::app()->createAbsoluteUrl("/$payment_code/api/verifywallet?".http_build_query($return_params)),
                    "failure" => Yii::app()->createAbsoluteUrl("/$payment_code/api/verifywallet?".http_build_query($return_params)),
                    "pending" => Yii::app()->createAbsoluteUrl("/$payment_code/api/verifywallet?".http_build_query($return_params)),
                ],
                "auto_return" => "approved",
                'installments' => 1,
                'notification_url'=>Yii::app()->createAbsoluteUrl("/$payment_code/api/webhook?".http_build_query($ipn_params)),                
                "external_reference"=> $reference_id,
                'payment_methods'=>[
                    'installments'=>1
                ]
            ];         
            if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
                $params['notification_url'] = 'https://bastisapp.com/testcode/mercadopago.php?'.http_build_query($ipn_params);
            }             
            
            MercadoPagoConfig::setAccessToken($access_token);            
            MercadoPagoConfig::setRuntimeEnviroment( $is_live? MercadoPagoConfig::SERVER : MercadoPagoConfig::LOCAL);

            $unique_key = CommonUtility::generateUIID();
            $client = new PreferenceClient();
            $request_options = new RequestOptions();
            $request_options->setCustomHeaders(["X-Idempotency-Key: $unique_key"]);

            try {
                
                if($request_from=="app"){
                    if(!empty($return_url)){
                        $redirect_url = $return_url;
                    } else $redirect_url = APP_CUSTOM_URL_SCHEME."://payment-callback?status=cancel";
                } else {
                    $redirect_url = Yii::app()->createAbsoluteUrl("/account/wallet");
                }

                $preference = $client->create($params,$request_options);            
                $payment_reference_id =  $preference->id;       
                
                $model = new AR_wallet_transactions_meta();
                $model->meta_name = $reference_id;
                $model->meta_value = json_encode([
                    'reference_id'=>$reference_id,                
                    'data'=>$data,
                ]);
                $model->save();
                
                $this->render("payment-form",[
                    'payment_reference_id'=>$payment_reference_id,
                    'public_key'=>$public_key,
                    'amount_to_pay'=>Price_Formatter::formatNumber($amount),
                    'redirect_url'=>$redirect_url
                ]);
                Yii::app()->end();
            } catch (MPApiException $e) {                
                $err_model = $e->getApiResponse()->getContent();
                $error = isset($err_model['message']) ? $err_model['message'] : t("Undefined Errors");
            } catch (\Exception $e) {
                $error =  $e->getMessage();
            }                                
        } catch (Exception $e) {
			$error = t($e->getMessage());							            
		}  

        $this->redirect(CommonUtility::failedRedirectWallet($request_from,$return_url,$error));        
    }

    public function actionverifywallet()
    {
        try {

            $error = '';  $failed_url = ''; $cancel_url = '' ;  $redirect_url='';  
            $payment_code = MercadopagoModule::paymentCode();         
            $id = Yii::app()->input->get('id');
            $request_from = Yii::app()->request->getQuery('request_from', 'web');                          
            $return_url = Yii::app()->request->getQuery('return_url', '');                         
            $return_url =  rtrim($return_url, "/");            
            $return_url = (empty($return_url) || $return_url === 'null') ? '' : $return_url; 

            if($request_from=="app"){
                if(!empty($return_url)){
                    $redirect_url = $return_url;
                } else $redirect_url = APP_CUSTOM_URL_SCHEME."://payment-callback?status=cancel";
            } else {
                $redirect_url = Yii::app()->createAbsoluteUrl("/account/wallet");
            }

            $payment_id = Yii::app()->input->get('payment_id');
            $status = Yii::app()->input->get('status');
            $preference_id = Yii::app()->input->get('preference_id');
            $reference_id = Yii::app()->input->get('external_reference');
            $status = Yii::app()->input->get('status');            

            $model = AR_wallet_transactions_meta::model()->find("meta_name=:meta_name",[
				':meta_name'=>$reference_id
			]);            
            if($model){
                $meta_value = json_decode($model->meta_value,true);                
                $stripe_session_id = isset($meta_value['session_id'])?$meta_value['session_id']:null;
                $meta_data = isset($meta_value['data'])?$meta_value['data']:null;

                $jwt_key = new Key(CRON_KEY, 'HS256');
			    $decoded = (array) JWT::decode($meta_data, $jwt_key);                                                                                             
                $successful_url = isset($decoded['successful_url'])?$decoded['successful_url']:'';	
                $failed_url = isset($decoded['failed_url'])?$decoded['failed_url']:'';	
                $cancel_url = isset($decoded['cancel_url'])?$decoded['cancel_url']:'';

                if( $status=="null" || empty($status)){                    
                    $this->redirect($redirect_url);
                    Yii::app()->end();
                } else if ($status === 'rejected' || $status=='failure') {
                    $error = $status;
                    //$this->redirect($failed_url."?error=".$error);
                    $this->redirect($redirect_url);
                    Yii::app()->end();
                } elseif ( $status=='pending' || $status=='in_process'){
                    $this->render('paymen-pending',[
                        'redirect_url'=>$cancel_url
                    ]);
                    return;
                }
                
                $merchant_id = isset($decoded['merchant_id'])?$decoded['merchant_id']:'';
			    $merchant_type = isset($decoded['merchant_type'])?$decoded['merchant_type']:'';
                $credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,$merchant_type);
                $credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';			                        
                
                $access_token = isset($credentials['attr2'])?$credentials['attr2']:'';
                $public_key = isset($credentials['attr1'])?$credentials['attr1']:'';
                $is_live = isset($credentials['is_live'])?$credentials['is_live']:false;
                $is_live = $is_live==1?true:false;

                MercadoPagoConfig::setAccessToken($access_token);
                MercadoPagoConfig::setRuntimeEnviroment( $is_live? MercadoPagoConfig::SERVER : MercadoPagoConfig::LOCAL);

                $client = new PaymentClient();                        
                $payment = $client->get($payment_id);                 
                $status = $payment->status;                

                if($payment->status!="approved"){
                    $error = $status;
                    $this->redirect($failed_url."?error=".$error);
                    return;
                }
                
                $payment_name = isset($decoded['payment_name'])?$decoded['payment_name']:'';
                $transaction_amount = isset($decoded['transaction_amount'])?$decoded['transaction_amount']:0;			
                $payment_description_raw = isset($decoded['payment_description_raw'])?$decoded['payment_description_raw']:'';
                $transaction_description_parameters = isset($decoded['transaction_description_parameters'])?$decoded['transaction_description_parameters']:'';
                $currency_code = isset($decoded['currency_code'])?$decoded['currency_code']:'';

                $customer_details = isset($decoded['customer_details'])?$decoded['customer_details']:'';	
                $customer_id = $customer_details->client_id;                                
                $card_id = CWallet::createCard( Yii::app()->params->account_type['digital_wallet'],$customer_id);
                $meta_array = [];
                try {
                    $date_now = date("Y-m-d");
                    $bonus = AttributesTools::getDiscountToApply($transaction_amount,CDigitalWallet::transactionName(),$date_now);
                    $transaction_amount = floatval($transaction_amount)+floatval($bonus);				  
                    $meta_array[]=[
                        'meta_name'=>'topup_bonus',
                        'meta_value'=>floatval($bonus)
                    ];
                    $payment_description_raw = "Funds Added via {payment_name} with {bonus} Bonus";
                    $transaction_description_parameters = [
                        '{payment_name}'=>$payment_name,
                        '{bonus}'=>Price_Formatter::formatNumber($bonus),
                    ];
                } catch (Exception $e) {}

                $model_transaction = AR_wallet_transactions::model()->find("reference_id=:reference_id",[
                    ':reference_id'=>$payment_id
                ]);

                if(!$model_transaction){
                    CPaymentLogger::afterAdddFunds($card_id,[				    
                        'transaction_description'=>$payment_description_raw,
                        'transaction_description_parameters'=>$transaction_description_parameters,
                        'transaction_type'=>'credit',
                        'transaction_amount'=>$transaction_amount,
                        'status'=>CPayments::paidStatus(),
                        'reference_id'=>$payment_id,
                        'reference_id1'=>CDigitalWallet::transactionName(),
                        'merchant_base_currency'=>$currency_code,
                        'admin_base_currency'=>$currency_code,
                        'meta_name'=>'topup',
                        'meta_value'=>$card_id,
                        'meta_array'=>$meta_array,			
                    ]);
                }         
                
                $message = t("Payment successful.");                

                if($request_from=="app"){
                    if(!empty($return_url)){
                        $redirect_url = $return_url;
                    } else $redirect_url = APP_CUSTOM_URL_SCHEME."://payment-callback?".http_build_query([
                        'status'=>'wallet_succesful',
                        'amount'=>$transaction_amount,
                        'payment_name'=>$payment_name,
                        'transaction_id'=>$payment_id,
                        'transaction_date'=>Date_Formatter::dateTime(date('c'))
                    ]);
                } else {
                    if(!empty($return_url)){
                        $this->redirect($return_url);
                    }
                    $redirect_url = Yii::app()->createAbsoluteUrl("/account/wallet?".http_build_query(['message'=>$message]));
                }
                $this->redirect($redirect_url);
                Yii::app()->end();                
            } else $error = t("Payment reference not found");
        } catch (\Exception $e) {
            $error = $e->getMessage();              
        }        
        $this->redirect(CommonUtility::failedRedirectWallet($request_from,$return_url,$error));
    }

    public function actionwebhook()
    {
        try {

            $logs = '';
            $paid_status = CPayments::paidStatus();
            $merchant_id = Yii::app()->input->get('merchant_id');
            $payment_type = Yii::app()->input->get('payment_type');            
            
            $payment_code = MercadopagoModule::paymentCode();
            $merchants = CMerchantListingV1::getMerchant( $merchant_id );			
			$credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,$merchants->merchant_type);	
            $credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';                                    
            $access_token = isset($credentials['attr2'])?$credentials['attr2']:'';
            $public_key = isset($credentials['attr1'])?$credentials['attr1']:'';
            $is_live = isset($credentials['is_live'])?$credentials['is_live']:false;
            $is_live = $is_live==1?true:false;
            
            $http_body = file_get_contents("php://input");            

            $http_body = json_decode($http_body,true);            
            $type = isset($http_body['type'])?$http_body['type']:null;
            $id = isset($http_body['data'])?$http_body['data']['id']:null;

            switch ($type) {
                case 'payment':                    
                    try {

                        MercadoPagoConfig::setAccessToken($access_token);
                        MercadoPagoConfig::setRuntimeEnviroment( $is_live? MercadoPagoConfig::SERVER : MercadoPagoConfig::LOCAL);
                        
                        $client = new PaymentClient();
                        $payment = $client->get($id);                                                
                        $external_reference = $payment->external_reference;
                        $status = $payment->status;                        

                        if( $status=="null" || empty($status)){
                            return;
                        } else if ($status === 'rejected' || $status=='failure'){
                            return;
                        } elseif ( $status=='pending' || $status=='in_process'){                            
                            return;
                        }
                        
                        if($payment_type=="purchase_items"){
                            $order = COrders::get($external_reference);     
                            $order_id = $order->order_id;                       
                            $payment_status = $payment->status=='approved' ?  $paid_status : $payment->status;                            
                            if($order->payment_status=!$paid_status){
                                $order->scenario = "update_payment_status";	
                                $order->payment_status = $payment_status;                                
                                $order->save();
                            }              
                            
                            $model_payment = AR_ordernew_transaction::model()->find("payment_reference=:payment_reference",[
                                ':payment_reference'=>$id
                            ]);       
                            if($model_payment){ 
                                $model_payment->status = $paid_status;
                                $model_payment->save(); 
                            } else {
                                $model = new AR_ordernew_transaction;
                                $model->order_id = $order->order_id;
                                $model->merchant_id = $order->merchant_id;
                                $model->client_id = $order->client_id;
                                $model->payment_code = $order->payment_code;
                                $model->trans_amount = $order->total;
                                $model->currency_code = $order->use_currency_code;
                                $model->payment_reference = $id;
                                $model->status = $paid_status;
                                $model->reason = '';                                
                                $model->save();		   
                            }

                            $order_model = COrders::getWithoutCache($order->order_uuid);                
                            $order_status = $order_model->status;
                            $order_model->scenario = "new_order";
                            $order_model->status = COrders::newOrderStatus();   
                            if($order_status=='draft'){                        
                                $order_model->save();                            
                            }                                                        
                            $logs = "Successful payment mercadopago - OrderID#$order_id";                            

                        } else if ( $payment_type=="load_wallet" ){

                            $model = AR_wallet_transactions_meta::model()->find("meta_name=:meta_name",[
                               ':meta_name'=>$external_reference
                            ]);   
                            if($model){ 
                                $meta_value = json_decode($model->meta_value,true);								
                                $data = isset($meta_value['data'])?$meta_value['data']:null;
                                $jwt_key = new Key(CRON_KEY, 'HS256');
                                $decoded = (array) JWT::decode($data, $jwt_key); 
                                
                                $payment_name = isset($decoded['payment_name'])?$decoded['payment_name']:'';
                                $transaction_amount = isset($decoded['transaction_amount'])?$decoded['transaction_amount']:0;			
                                $payment_description_raw = isset($decoded['payment_description_raw'])?$decoded['payment_description_raw']:'';
                                $transaction_description_parameters = isset($decoded['transaction_description_parameters'])?$decoded['transaction_description_parameters']:'';
                                $currency_code = isset($decoded['currency_code'])?$decoded['currency_code']:'';

                                $customer_details = isset($decoded['customer_details'])?$decoded['customer_details']:'';	
                                $customer_id = $customer_details->client_id; 
                                
                                $card_id = CWallet::createCard( Yii::app()->params->account_type['digital_wallet'],$customer_id);				   
                                $meta_array = [];

                                try {
                                    $date_now = date("Y-m-d");
                                    $bonus = AttributesTools::getDiscountToApply($transaction_amount,CDigitalWallet::transactionName(),$date_now);
                                    $transaction_amount = floatval($transaction_amount)+floatval($bonus);				  
                                    $meta_array[]=[
                                        'meta_name'=>'topup_bonus',
                                        'meta_value'=>floatval($bonus)
                                    ];
                                    $payment_description_raw = "Funds Added via {payment_name} with {bonus} Bonus";
                                    $transaction_description_parameters = [
                                        '{payment_name}'=>$payment_name,
                                        '{bonus}'=>Price_Formatter::formatNumber($bonus),
                                    ];
                                } catch (Exception $e) {}

                                CPaymentLogger::afterAdddFunds($card_id,[				    
                                    'transaction_description'=>$payment_description_raw,
                                    'transaction_description_parameters'=>$transaction_description_parameters,
                                    'transaction_type'=>'credit',
                                    'transaction_amount'=>$transaction_amount,
                                    'status'=>CPayments::paidStatus(),
                                    'reference_id'=>$id,
                                    'reference_id1'=>CDigitalWallet::transactionName(),
                                    'merchant_base_currency'=>$currency_code,
                                    'admin_base_currency'=>$currency_code,
                                    'meta_name'=>'topup',
                                    'meta_value'=>$card_id,
                                    'meta_array'=>$meta_array,			
                               ]);
                               $logs = "Mercadopago wallet loading succesful";
                            } else $logs = "Payment reference not found";                         
                        } else {
                            $logs = 'Invalid Payment type';
                        }        
                    } catch (MPApiException $e) { 
                        $err_model = $e->getApiResponse()->getContent();
                        $logs = isset($err_model['message']) ? $err_model['message'] : t("Undefined Errors");
                    } catch (\Exception $e) {
                        $logs = $e->getMessage();                          
                    }
                    break;         

                default:                    
                    break;
            }
            
        } catch (Exception $e) {
            $logs = $e->getMessage();  
        }
                
        Yii::log( $logs , CLogger::LEVEL_INFO);

        $response = [
            'statusCode'=>200,
            'statusMsg'=>'Success',            
        ];        
        header("Access-Control-Allow-Origin: *");          
        header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    	header('Content-type: application/json'); 		
		echo CJSON::encode($response);
		Yii::app()->end();
    }

}
// end class