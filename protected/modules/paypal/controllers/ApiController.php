<?php
require 'php-jwt/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ApiController extends CController
{
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
		
        $this->init();
		return true;
	}

	public function init()
	{
		$settings = OptionsTools::find(array(
			'website_date_format_new','website_time_format_new','home_search_unit_type','website_timezone_new',	
			'multicurrency_enabled','multicurrency_enabled_checkout_currency'
		));		
		
		Yii::app()->params['settings'] = $settings;

		/*SET TIMEZONE*/
		$timezone = Yii::app()->params['settings']['website_timezone_new'];		
		if (is_string($timezone) && strlen($timezone) > 0){
		Yii::app()->timeZone=$timezone;		   
		}
		Price_Formatter::init();		
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

    public function actionIndex()
	{
		//
	}  

    public function actioncreatecheckout()
    {
        try {

            $message = '';
            $message = null; 
            $redirect_url = null;

            $order_uuid = Yii::app()->request->getQuery('order_uuid', '');
			$cart_uuid = Yii::app()->request->getQuery('cart_uuid', '');
			$payment_uuid = Yii::app()->request->getQuery('payment_uuid', ''); 						
			$request_from = Yii::app()->request->getQuery('request_from', 'web');                          
            $return_url = Yii::app()->request->getQuery('return_url', null);                         
            $return_url =  rtrim($return_url, "/");            
            
            if($request_from=="app"){
                if(!empty($return_url)){
                    $redirect_url = $return_url."/checkout";
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
            
            $credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';            
			$is_live = isset($credentials['is_live'])?$credentials['is_live']:'';			            
			$is_live = $is_live==1?true:false;
			$paypal_client_id = $credentials['attr1'] ?? null;
			$paypal_secret_id = $credentials['attr2'] ?? null;
            
            $exchange_rate = $order->exchange_rate>0?$order->exchange_rate:1;			  		        
            if($order->amount_due>0){
                $total = floatval(Price_Formatter::convertToRaw( ($order->amount_due*$exchange_rate) ));	
            } else $total = floatval(Price_Formatter::convertToRaw( ($order->total*$exchange_rate) ));			  
                                    
            $multicurrency_enabled = Yii::app()->params['settings']['multicurrency_enabled'] ?? false;
            $multicurrency_enabled = $multicurrency_enabled==1?true:false;		   	
            $enabled_checkout_currency = Yii::app()->params['settings']['multicurrency_enabled_checkout_currency'] ?? false;
            $enabled_force = $multicurrency_enabled==true? ($enabled_checkout_currency==1?true:false) :false;        
            $use_currency_code = $order->use_currency_code;            
            if($enabled_force){
                if($force_result = CMulticurrency::getForceCheckoutCurrency($order->payment_code,$use_currency_code)){					 					 
                    $use_currency_code = $force_result['to_currency'];
                    $total = Price_Formatter::convertToRaw($total*$force_result['exchange_rate'],2);
                }
            }

            $payment_processing = CommonUtility::safeTranslate("Payment processing, please don't close this window");
                               
            $transaction_type = 'purchase_food';
            $verifyMethod = "verifyPayment";   
            $ajax_url = Yii::app()->createAbsoluteUrl("/$payment_code/api");                    

		     ScriptUtility::registerScript(array(                            
             "var verifyMethod='".CJavaScript::quote($verifyMethod)."';",               
              "var order_uuid='".CJavaScript::quote($order_uuid)."';",  
              "var cart_uuid='".CJavaScript::quote($cart_uuid)."';",  
              "var amount='".CJavaScript::quote($total)."';",  
              "var request_from='".CJavaScript::quote($request_from)."';",  
              "var return_url='".CJavaScript::quote($return_url)."';",  
              "var payment_processing='".CJavaScript::quote($payment_processing)."';",  
              "var ajax_url='".CJavaScript::quote($ajax_url)."';",                
              "var redirect_url='".CJavaScript::quote($redirect_url)."';",  
              "var transaction_type='".CJavaScript::quote($transaction_type)."';",  
             ),'paypal');	        
                        
            ScriptUtility::registerJS([
                Yii::app()->baseUrl."/assets/vendor/axios.min.js"
            ],CClientScript::POS_END);

            $params = [
                'client-id'=>$paypal_client_id,
                'currency'=>$use_currency_code,
                'disable-funding'=>'credit,card'
            ];

            ScriptUtility::registerCSS([
                'https://cdn.jsdelivr.net/npm/quasar@2.14.5/dist/quasar.prod.css',
                'https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900|Material+Icons'
             ]);             

            ScriptUtility::registerJS([
                Yii::app()->baseUrl."/assets/vendor/axios.min.js", 
                'https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.prod.js',
                'https://cdn.jsdelivr.net/npm/quasar@2.14.5/dist/quasar.umd.prod.js',
                'https://www.paypal.com/sdk/js?'.http_build_query($params),
                Yii::app()->baseUrl."/protected/modules/$payment_code/assets/js/payment-paypal.js?version=".time()
            ],CClientScript::POS_END);
                    
            $this->pageTitle = t("Paypal");
             
            $this->render('create-checkout',[
                'redirect_url'=>$redirect_url,  
                'page_title'=>t("Paypal")
            ]);
            Yii::app()->end();
        } catch (Exception $e) {
			$message = $e->getMessage();
		}        
        
        $this->redirect(CommonUtility::failedRedirect($request_from,$return_url,$message));
    }    

    public function actionverifyPayment()
    {
        try {

            $redirect_url = null;               
            $transaction_id = Yii::app()->request->getPost('transaction_id', null); 
            $order_id = Yii::app()->request->getPost('order_id', null); 

            $order_uuid = Yii::app()->request->getPost('order_uuid', null); 
            $cart_uuid = Yii::app()->request->getPost('cart_uuid', null); 
            $request_from = Yii::app()->request->getPost('request_from', null); 
            $return_url = Yii::app()->request->getPost('return_url', null);     
            
            $data = AR_ordernew::model()->find('order_uuid=:order_uuid',array(':order_uuid'=>$order_uuid));
            if(!$data){                             
                $this->msg = t("Order not found"); 
                $this->details = [
                    'redirect_url'=>CommonUtility::failedRedirect($request_from,$return_url,$this->msg)
                ];
                $this->responseJson();
                Yii::app()->end();
            }

            $merchant = CMerchantListingV1::getMerchant($data->merchant_id);
            $credentials = CPayments::getPaymentCredentials($data->merchant_id,$data->payment_code,
            $merchant->merchant_type);
            $credentials = isset($credentials[$data->payment_code])?$credentials[$data->payment_code]:'';
            $is_live = isset($credentials['is_live'])?intval($credentials['is_live']):0;

            Yii::import('application.modules.paypal.components.*');

            CPaypalTokens::setProduction($is_live);
			CPaypalTokens::setCredentials($credentials,$data->payment_code);
			$token = CPaypalTokens::getToken(date("c"));

            CPaypal::setProduction($is_live);
            CPaypal::setToken($token);		    	
            $resp = CPaypal::getOrders($order_id);     
            
            $data->scenario = "new_order";
            $data->status = COrders::newOrderStatus();
            $data->payment_status = CPayments::paidStatus();
            $data->cart_uuid = $cart_uuid;
            $data->save();

            $model_payment = AR_ordernew_transaction::model()->find("payment_reference=:payment_reference",[
            ':payment_reference'=>$transaction_id
            ]);       
            if($model_payment){                        
                $model_payment->status = CPayments::paidStatus();
                $model_payment->save(); 
            } else {
                $model = new AR_ordernew_transaction;
                $model->order_id = $data->order_id;
                $model->merchant_id = $data->merchant_id;
                $model->client_id = $data->client_id;
                $model->payment_code = $data->payment_code;				
                $model->trans_amount = $data->amount_due>0? $data->amount_due: $data->total;
                $model->currency_code = $data->use_currency_code;
                $model->payment_reference = $transaction_id;
                $model->status = CPayments::paidStatus();
                $model->reason = $resp['status'] ?? '';
            }            

            if($model->save()){
                $params = array(  
                array('transaction_id'=>$model->transaction_id,'order_id'=>$data->order_id, 
                    'meta_name'=>'order_id', 'meta_value'=>$order_uuid ),
                                            
                    array('transaction_id'=>$model->transaction_id,'order_id'=>$data->order_id, 
                    'meta_name'=>'transaction_id', 'meta_value'=>$transaction_id ),                    
                );                    
                $builder=Yii::app()->db->schema->commandBuilder;
                $command=$builder->createMultipleInsertCommand('{{ordernew_trans_meta}}',$params);
                $command->execute();		

                $this->code = 1;
                $this->msg = t("Payment successful. please wait while we redirect you.");
                                
                if($request_from=="app"){
                    if(!empty($return_url)){
                        $redirect_url = $return_url."/account/trackorder?".http_build_query(['order_uuid'=>$order_uuid]);
                    } else $redirect_url = APP_CUSTOM_URL_SCHEME."://payment-callback?".http_build_query(['status'=>'successful','order_id'=>$order_uuid]);
                } else $redirect_url = Yii::app()->createAbsoluteUrl("/orders/index?".http_build_query(['order_uuid'=>$order_uuid]));
                $this->details = [
                    'redirect_url'=>$redirect_url
                ];
                $this->responseJson();
                Yii::app()->end();
            } else $this->msg = $model->getErrors();

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
        
        $this->details = [
            'redirect_url'=>CommonUtility::failedRedirect($request_from,$return_url,$this->msg)
        ];
        $this->responseJson();
    }

    // WALLET LOADING
    public function actionprocesspayment()
    {
        try {
            $request_from = null;
            $return_url = null;
            $message = null;

            $request_from = Yii::app()->request->getQuery('request_from', '');            
            $data = Yii::app()->request->getQuery('data', '');            
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
            $is_live = isset($credentials['is_live'])?$credentials['is_live']:'';			            
			$is_live = $is_live==1?true:false;
			$paypal_client_id = $credentials['attr1'] ?? null;
			$paypal_secret_id = $credentials['attr2'] ?? null;      
            
            $payment_processing = CommonUtility::safeTranslate("Payment processing, please don't close this window");
            $transaction_type = 'wallet_loading';
            $ajax_url = Yii::app()->createAbsoluteUrl("/$payment_code/api");

            if($request_from=="app"){
                if(!empty($return_url)){
                    $redirect_url = $return_url;
                } else $redirect_url = APP_CUSTOM_URL_SCHEME."://payment-callback?status=cancel";
            } else {
                $redirect_url = Yii::app()->createAbsoluteUrl("/account/wallet");
            }                 

            $order_uuid = $reference_id;
            $cart_uuid = null;
            $total = Price_Formatter::convertToRaw($amount);
            $verifyMethod = "verifywallet";    
            
            ScriptUtility::registerScript(array(                    
                "var verifyMethod='".CJavaScript::quote($verifyMethod)."';",                      
                "var order_uuid='".CJavaScript::quote($order_uuid)."';",  
                "var cart_uuid='".CJavaScript::quote($cart_uuid)."';",  
                "var amount='".CJavaScript::quote($total)."';",  
                "var request_from='".CJavaScript::quote($request_from)."';",  
                "var return_url='".CJavaScript::quote($return_url)."';",  
                "var payment_processing='".CJavaScript::quote($payment_processing)."';",  
                "var ajax_url='".CJavaScript::quote($ajax_url)."';",                
                "var redirect_url='".CJavaScript::quote($redirect_url)."';",  
                "var transaction_type='".CJavaScript::quote($transaction_type)."';",  
            ),'paypal');	        

            ScriptUtility::registerJS([
                Yii::app()->baseUrl."/assets/vendor/axios.min.js"
            ],CClientScript::POS_END);

            $params = [
                'client-id'=>$paypal_client_id,
                'currency'=>$currency_code,
                'disable-funding'=>'credit,card'
            ];            
            ScriptUtility::registerCSS([
                'https://cdn.jsdelivr.net/npm/quasar@2.14.5/dist/quasar.prod.css',
                'https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900|Material+Icons'
             ]);             

            ScriptUtility::registerJS([
                Yii::app()->baseUrl."/assets/vendor/axios.min.js", 
                'https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.prod.js',
                'https://cdn.jsdelivr.net/npm/quasar@2.14.5/dist/quasar.umd.prod.js',
                'https://www.paypal.com/sdk/js?'.http_build_query($params),
                Yii::app()->baseUrl."/protected/modules/$payment_code/assets/js/payment-paypal.js?version=".time()
            ],CClientScript::POS_END);
                    
            $this->pageTitle = t("Paypal");      
            
             $model = AR_wallet_transactions_meta::model()->find("meta_name=:meta_name",[
                ':meta_name'=>$reference_id
             ]);
             if(!$model){
                $model = new AR_wallet_transactions_meta();
             }
             $model->meta_name = $reference_id;
             $model->meta_value = json_encode([
                'reference_id'=>$reference_id,                
                'data'=>$data,
             ]);
             $model->save();
             
            $this->render('create-checkout',[
                'redirect_url'=>$redirect_url,  
                'page_title'=>t("Paypal")
            ]);
            Yii::app()->end();            
        } catch (Exception $e) {
			$message = $e->getMessage();
            $this->redirect(CommonUtility::failedRedirectWallet($request_from,$return_url,$message));
		}                
    }

    public function actionverifywallet()
    {
        try {

            $redirect_url=''; $message ='';            
            $payment_code = PaypalModule::paymentCode();         
            $reference_id = Yii::app()->request->getPost('order_uuid', null);
            $request_from = Yii::app()->request->getPost('request_from', 'web');                          
            
            $return_url = Yii::app()->request->getPost('return_url', '');
            $return_url =  rtrim($return_url, "/"); 
            $return_url = (empty($return_url) || $return_url === 'null') ? '' : $return_url; 

            $transaction_id = Yii::app()->request->getPost('transaction_id', null);
            $order_id = Yii::app()->request->getPost('order_id', null);
            $order_uuid = Yii::app()->request->getPost('order_uuid', null);            

            if($request_from=="app"){
                if(!empty($return_url)){
                    $redirect_url = $return_url;
                } else $redirect_url = APP_CUSTOM_URL_SCHEME."://payment-callback?status=cancel";
            } else {
                $redirect_url = Yii::app()->createAbsoluteUrl("/account/wallet");
            }

            $model = AR_wallet_transactions_meta::model()->find("meta_name=:meta_name",[
				':meta_name'=>$reference_id
			]);    
            if(!$model){
                $message = t("Payment reference not found");
                $this->redirect(CommonUtility::failedRedirectWallet($request_from,$return_url,$message));
            }

            $meta_value = json_decode($model->meta_value,true);                                        
            $meta_data = $meta_value['data'] ?? null;
                        
            $jwt_key = new Key(CRON_KEY, 'HS256');
			$decoded = (array) JWT::decode($meta_data, $jwt_key);                                                                                             
            $merchant_id = $decoded['merchant_id'] ?? null;
			$merchant_type = $decoded['merchant_type'] ?? null;
            $credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,$merchant_type);
            $credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';            
            $is_live = isset($credentials['is_live'])?$credentials['is_live']:'';			            
			$is_live = $is_live==1?true:false;			
            
            $amount = $decoded['amount'] ?? 0;
            $currency_code = $decoded['currency_code'] ?? null;            
            $payment_name = $decoded['payment_name'] ?? '';

            $transaction_amount = $decoded['transaction_amount'] ?? 0;
            $payment_description_raw = $decoded['payment_description_raw'] ?? '';
            $transaction_description_parameters = $decoded['transaction_description_parameters'] ?? '';  

            $customer_details = $decoded['customer_details'] ?? null;
            if(!$customer_details){                
                $message = t("Customer details invalid");
                $this->msg = $message;                
                $this->details = [
                    'redirect_url'=>CommonUtility::failedRedirectWallet($request_from,$return_url,$message)
                ];
                $this->responseJson();
            }   

            $customer_id = $customer_details->client_id;                                
            $card_id = CWallet::createCard( Yii::app()->params->account_type['digital_wallet'],$customer_id);
            $meta_array = [];
            
            Yii::import('application.modules.paypal.components.*');

            CPaypalTokens::setProduction($is_live);
			CPaypalTokens::setCredentials($credentials,$payment_code);
			$token = CPaypalTokens::getToken(date("c"));
            CPaypal::setProduction($is_live);
            CPaypal::setToken($token);		    	
            CPaypal::getOrders($order_id); 
            
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

            $payment_id = $transaction_id;
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
            $this->code = 1;                 

            if($request_from=="app"){
                if(!empty($return_url)){
                    $redirect_url = "{$return_url}?".http_build_query(['message'=>$message]);
                } else $redirect_url = APP_CUSTOM_URL_SCHEME."://payment-callback?".http_build_query([
                    'status'=>'wallet_succesful',
                    'amount'=>$transaction_amount,
                    'payment_name'=>$payment_name,
                    'transaction_id'=>$payment_id,
                    'transaction_date'=>Date_Formatter::dateTime(date('c'))
                ]);
            } else {
                if(!empty($return_url)){
                    $redirect_url = "{$return_url}?".http_build_query(['message'=>$message]);
                } else $redirect_url = Yii::app()->createAbsoluteUrl("/account/wallet?".http_build_query(['message'=>$message]));                 
            }           
            $this->msg = $message;
            $this->details = [
                'redirect_url'=>$redirect_url
            ];        
            $this->responseJson();

        } catch (Exception $e) {
			$message = $e->getMessage();                                
		}                        

        $this->msg = $message;
        $this->details = [
            'redirect_url'=>CommonUtility::failedRedirectWallet($request_from,$return_url,$message)
        ];        
        $this->responseJson();
    }
    
    public function actionWebhooks()
    {
        try {

            $logs = null;    
            $payment_code = PaypalModule::paymentCode();

            $payload = file_get_contents("php://input");            
            // $payload = '{"id":"WH-61M909644L610512C-0S095638PN5901447","event_version":"1.0","create_time":"2025-04-15T03:02:22.518Z","resource_type":"capture","resource_version":"2.0","event_type":"PAYMENT.CAPTURE.COMPLETED","summary":"Payment completed for $ 1.0 USD","resource":{"amount":{"value":"1.00","currency_code":"USD"},"seller_protection":{"dispute_categories":["ITEM_NOT_RECEIVED","UNAUTHORIZED_TRANSACTION"],"status":"ELIGIBLE"},"create_time":"2025-04-15T03:02:18Z","custom_id":"wallet_loading|","payee":{"email_address":"kmrss1848-facilitator@gmail.com","merchant_id":"JNF65DHLD5CEL"},"supplementary_data":{"related_ids":{"order_id":"6V370045E39648248"}},"update_time":"2025-04-15T03:02:18Z","final_capture":true,"seller_receivable_breakdown":{"paypal_fee":{"value":"0.52","currency_code":"USD"},"gross_amount":{"value":"1.00","currency_code":"USD"},"net_amount":{"value":"0.48","currency_code":"USD"}},"invoice_id":"f4bbc746-19a5-11f0-a3fd-9c5c8e164c2c","links":[{"method":"GET","rel":"self","href":"https://api.sandbox.paypal.com/v2/payments/captures/0B015291W1566400G"},{"method":"POST","rel":"refund","href":"https://api.sandbox.paypal.com/v2/payments/captures/0B015291W1566400G/refund"},{"method":"GET","rel":"up","href":"https://api.sandbox.paypal.com/v2/checkout/orders/6V370045E39648248"}],"id":"0B015291W1566400G","status":"COMPLETED"},"links":[{"href":"https://api.sandbox.paypal.com/v1/notifications/webhooks-events/WH-61M909644L610512C-0S095638PN5901447","rel":"self","method":"GET"},{"href":"https://api.sandbox.paypal.com/v1/notifications/webhooks-events/WH-61M909644L610512C-0S095638PN5901447/resend","rel":"resend","method":"POST"}]}';
            // $payload = '{"id":"WH-42132346K0794251T-4G6240426U2751945","event_version":"1.0","create_time":"2025-04-15T08:30:19.952Z","resource_type":"capture","resource_version":"2.0","event_type":"PAYMENT.CAPTURE.COMPLETED","summary":"Payment completed for $ 40.0 USD","resource":{"amount":{"value":"40.00","currency_code":"USD"},"seller_protection":{"dispute_categories":["ITEM_NOT_RECEIVED","UNAUTHORIZED_TRANSACTION"],"status":"ELIGIBLE"},"create_time":"2025-04-15T08:30:16Z","custom_id":"purchase_food|969d5b49-1920-11f0-8f6e-9c5c8e164c2c","payee":{"email_address":"kmrss1848-facilitator@gmail.com","merchant_id":"JNF65DHLD5CEL"},"supplementary_data":{"related_ids":{"order_id":"3K186277DF901073L"}},"update_time":"2025-04-15T08:30:16Z","final_capture":true,"seller_receivable_breakdown":{"paypal_fee":{"value":"1.89","currency_code":"USD"},"gross_amount":{"value":"40.00","currency_code":"USD"},"net_amount":{"value":"38.11","currency_code":"USD"}},"invoice_id":"cf339f78-19d3-11f0-a100-9c5c8e164c2c","links":[{"method":"GET","rel":"self","href":"https://api.sandbox.paypal.com/v2/payments/captures/16970890US393610P"},{"method":"POST","rel":"refund","href":"https://api.sandbox.paypal.com/v2/payments/captures/16970890US393610P/refund"},{"method":"GET","rel":"up","href":"https://api.sandbox.paypal.com/v2/checkout/orders/3K186277DF901073L"}],"id":"16970890US393610P","status":"COMPLETED"},"links":[{"href":"https://api.sandbox.paypal.com/v1/notifications/webhooks-events/WH-42132346K0794251T-4G6240426U2751945","rel":"self","method":"GET"},{"href":"https://api.sandbox.paypal.com/v1/notifications/webhooks-events/WH-42132346K0794251T-4G6240426U2751945/resend","rel":"resend","method":"POST"}]}';
			$payload = json_decode($payload,true);	            
            
            if(empty($payload)){                
                Yii::log( "$payment_code invalid payload ." , CLogger::LEVEL_INFO);
				http_response_code(200);
				Yii::app()->end();
            }       

            $event_type = $payload['event_type'] ?? null;
            if(!$event_type){             
                Yii::log( "Invalid event." , CLogger::LEVEL_INFO);   
				http_response_code(200);
				Yii::app()->end();
            }
                                    
			$webhook_id = isset($payload['id'])?$payload['id']:null;
            $resource = $payload['resource'] ?? null;
            $transaction_id = $resource['id'] ?? null;
            $status = $resource['status'] ?? null;     
            $status = strtolower($status) ?? '';
            $custom_id = $resource['custom_id'] ?? null;     
            $customId = !empty($custom_id) ? explode("|",$custom_id) : null;
            $transaction_type = $customId[0] ?? null;
            $cart_uuid = $customId[1] ?? null;
            $invoice_id = $resource['invoice_id'] ?? null;     

            if($status!="completed"){
                Yii::log( "Invalid paypal payment status $status." , CLogger::LEVEL_INFO);   
				http_response_code(200);
				Yii::app()->end();
            }

            if(!$transaction_type){
                Yii::log( "Invalid transaction type => $transaction_type." , CLogger::LEVEL_INFO);   
				http_response_code(200);
				Yii::app()->end();
            }                                

            $merchant_type=0; $merchant_id=0;
            $merchantID = Yii::app()->request->getQuery('merchant_id', 0);
            $merchantID = intval($merchantID);
            if($merchantID>0){
                $merchant_id = $merchantID;
                $merchant_type = CMerchants::getMerchantType($merchant_id);
            }

            $credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,$merchant_type);
            $credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';          
            $is_live = $credentials['is_live'] ?? false;
            $is_live = $is_live==1?true:false;       
            
            $payment_status = CPayments::paidStatus();            
            
            if($event_type=="PAYMENT.CAPTURE.COMPLETED"){
                switch ($transaction_type) {
                    case 'wallet_loading':
                        
                        $find = AR_wallet_transactions::model()->find("reference_id=:reference_id",[
                            ':reference_id'=>$transaction_id
                        ]);
                        if($find){
                            Yii::log( "Payment already exist $transaction_id" , CLogger::LEVEL_INFO);   
                            http_response_code(200);
                            Yii::app()->end();                            
                        }

                        $model = AR_wallet_transactions_meta::model()->find("meta_name=:meta_name",[
                            ':meta_name'=>$invoice_id
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
                                'reference_id'=>$transaction_id,
                                'reference_id1'=>CDigitalWallet::transactionName(),
                                'merchant_base_currency'=>$currency_code,
                                'admin_base_currency'=>$currency_code,
                                'meta_name'=>'topup',
                                'meta_value'=>$card_id,
                                'meta_array'=>$meta_array,			
                           ]);
                           $logs = "$payment_code wallet loading succesful => $transaction_id";

                        } else $logs = "Payment reference not found";  
                        break;
                    
                    case 'purchase_food':
                        $order = COrders::get($invoice_id); 
                        $order_id = $order->order_id;            
                        $order->scenario = "update_payment_status";					
                        $order->payment_status = $payment_status;
                        $order->cart_uuid = $cart_uuid;
                        $order->save();

                        $model_payment = AR_ordernew_transaction::model()->find("payment_reference=:payment_reference",[
                            ':payment_reference'=>$transaction_id
                        ]);       
                        if($model_payment){ 
                            $model_payment->status = CPayments::paidStatus();
                            $model_payment->save();
                        } else {
                            $model = new AR_ordernew_transaction;
                            $model->order_id = $order->order_id;
                            $model->merchant_id = $order->merchant_id;
                            $model->client_id = $order->client_id;
                            $model->payment_code = $order->payment_code;
                            $model->trans_amount = $order->total;
                            $model->currency_code = $order->use_currency_code;
                            $model->payment_reference = $transaction_id;
                            $model->status = $payment_status;
                            $model->reason = '';                                
                            $model->save();		   
                        }
                        $order_model = COrders::getWithoutCache($invoice_id);                
                        $order_status = $order_model->status;
                        $order_model->scenario = "new_order";
                        $order_model->status = COrders::newOrderStatus();   
                        if($order_status=='draft'){                        
                            $order_model->save();                            
                        }           
                        $logs = "Successful payment $payment_code - OrderID#$order_id";                     
                        break;                    
                }
            } else $logs = "Invalid even type $event_type";
        } catch (Exception $e) {
			$logs =  $e->getMessage();
            Yii::log( $logs , CLogger::LEVEL_INFO); 
		}                  
    }

}
// end class