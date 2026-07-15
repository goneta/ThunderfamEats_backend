<?php
spl_autoload_unregister(array('YiiBase','autoload'));
require 'razorpay/vendor/autoload.php';
require 'php-jwt/vendor/autoload.php';
use Razorpay\Api\Api;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Square\Models\BreakType;

spl_autoload_register(array('YiiBase','autoload'));

class ApiController extends CController
{

    const META_NAME = 'RAZORPAY';    
    public $code=2,$msg,$details,$data;		

    
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
						
		Price_Formatter::init();	        
		if($method=="PUT"){            
			$this->data = Yii::app()->input->xssClean(json_decode(file_get_contents('php://input'), true));
		} else $this->data = Yii::app()->input->xssClean($_POST);						

		return true;
	}
	
	public function actionIndex()
	{
		//
	}  

    public function actioncreatecheckout()
    {
        try {

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
			$key_id = $credentials['attr1'] ?? null;
			$key_secret = $credentials['attr2'] ?? null;

            $customer_model = ACustomer::get($client_id);     
            $full_name = $customer_model->first_name." ".$customer_model->last_name; 
            $api = new Api($key_id,$key_secret);

            $model = AR_client_meta::model()->find('client_id=:client_id AND meta1=:meta1 AND meta2=:meta2 
			AND meta3=:meta3 ', 
		    array( 
		      //':client_id'=>intval(Yii::app()->user->id),
              ':client_id'=>intval($client_id),
		      ':meta1'=>$payment_code,
		      ':meta2'=>$is_live,
		      ':meta3'=>isset($credentials['merchant_id'])?$credentials['merchant_id']:'',
		    )); 

            $create = false; $customer_id='';
		    if($model){
		    	if(empty($model->meta4)){
		    		$create = true;
		    	} else $customer_id = $model->meta4;
		    } else $create = true;
            
            if($create){
                try {										
					$customer = $api->customer->create(array(
					   'name' => $full_name,
					   'email' => $customer_model->email_address,
					   'contact'=> $customer_model->contact_phone,
					   'fail_existing'=>0,
					   'notes'=> array(
					          'client_id'=> $client_id
					       )
					));					
					$customer_id = $customer->id;
                    $model = new AR_client_meta;
			    	$model->client_id = intval(Yii::app()->user->id);
			    	$model->meta1 = $payment_code;
			    	$model->meta2 = $is_live;
			    	$model->meta3 = $credentials['merchant_id'];
			    	$model->meta4 = $customer_id;
			    	$model->save();		
				} catch (Exception $e) {
					$message = $e->getMessage();                    
                    $this->redirect(CommonUtility::failedRedirect($request_from,$return_url,$message));
				}
            }
            

            $data = AR_ordernew::model()->find('order_uuid=:order_uuid',array(':order_uuid'=>$order_uuid));
            if(!$data){
                $message = t("Order not found");
                $this->redirect(CommonUtility::failedRedirect($request_from,$return_url,$message));
                Yii::app()->end();
            }   

            $exchange_rate = $data->exchange_rate>0?$data->exchange_rate:1;			  		        
            if($data->amount_due>0){
                $total = floatval(Price_Formatter::convertToRaw( ($data->amount_due*$exchange_rate) ));	
            } else $total = floatval(Price_Formatter::convertToRaw( ($data->total*$exchange_rate) ));			  

            $merchant = CMerchantListingV1::getMerchant($data->merchant_id);
            $restaurant_name = $merchant->restaurant_name;            
            $payment_description = t("Payment to merchant [merchant]. Order#[order_id]",
                array('[merchant]'=>$merchant->restaurant_name,'[order_id]'=>$data->order_id ));	

            $options = OptionsTools::find(['multicurrency_enabled','multicurrency_enabled_checkout_currency']);
            $multicurrency_enabled = $options['multicurrency_enabled'] ?? false;
            $multicurrency_enabled = $multicurrency_enabled==1?true:false;		   	
            $enabled_checkout_currency = $options['multicurrency_enabled_checkout_currency'] ?? false;
            $enabled_force = $multicurrency_enabled==true? ($enabled_checkout_currency==1?true:false) :false;        
            $use_currency_code = $data->use_currency_code;            
            if($enabled_force){
                if($force_result = CMulticurrency::getForceCheckoutCurrency($data->payment_code,$use_currency_code)){					 					 
                    $use_currency_code = $force_result['to_currency'];
                    $total = Price_Formatter::convertToRaw($total*$force_result['exchange_rate'],2);
                }
            }			  					
                                        
            $amount_in_paise = intval(round($total * 100));               
            $order = $api->order->create([
                'receipt'=>$order_uuid,
                'amount'=>$amount_in_paise,
                'currency'=>$use_currency_code,
                'notes'=>array(
                    'reference_id'=>$order_uuid,
                    'transaction_type'=>'purchase_food'
                )
             ]);           
                         
             $transaction_type = 'purchase_food';
             $ajax_url = Yii::app()->createAbsoluteUrl("/$payment_code/api");                    
             $params = [
                'order_uuid'=>$order_uuid,
                'cart_uuid'=>$cart_uuid,
                'request_from'=>$request_from,
                'return_url'=>$return_url,
             ];
             $callback_url = Yii::app()->createAbsoluteUrl("/$payment_code/api/verifyPayment?".http_build_query($params));              

             $payment_processing = CommonUtility::safeTranslate("Payment processing, please don't close this window");
                                       
             $verifyMethod = "verifyPayment";
		     ScriptUtility::registerScript(array(
              "var verifyMethod='".CJavaScript::quote($verifyMethod)."';",
              "var key_id='".CJavaScript::quote($key_id)."';",
              "var customer_id='".CJavaScript::quote($customer_id)."';",
              "var restaurant_name='".CJavaScript::quote($restaurant_name)."';",
              "var payment_description='".CJavaScript::quote($payment_description)."';",
              "var order_id='".CJavaScript::quote($order->id)."';",
              "var currency_code='".CJavaScript::quote($use_currency_code)."';",
              "var amount='".CJavaScript::quote($amount_in_paise)."';",              
              "var transaction_type='".CJavaScript::quote($transaction_type)."';",  
              "var ajax_url='".CJavaScript::quote($ajax_url)."';",                
              "var callback_url='".CJavaScript::quote($callback_url)."';",  
              "var order_uuid='".CJavaScript::quote($order_uuid)."';",  
              "var cart_uuid='".CJavaScript::quote($cart_uuid)."';",  
              "var request_from='".CJavaScript::quote($request_from)."';",  
              "var return_url='".CJavaScript::quote($return_url)."';",  
              "var payment_processing='".CJavaScript::quote($payment_processing)."';",  
              "var redirect_url='".CJavaScript::quote($redirect_url)."';",  
             ),'razorpay');	
            
             ScriptUtility::registerCSS([
                'https://cdn.jsdelivr.net/npm/quasar@2.14.5/dist/quasar.prod.css',
                'https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900|Material+Icons'
             ]);             
             ScriptUtility::registerJS([
                 Yii::app()->baseUrl."/assets/vendor/axios.min.js", 
                'https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.prod.js',
                'https://cdn.jsdelivr.net/npm/quasar@2.14.5/dist/quasar.umd.prod.js',
                'https://checkout.razorpay.com/v1/checkout.js',
                Yii::app()->baseUrl."/protected/modules/$payment_code/assets/js/payment-razorpay.js?version=".time()
             ],CClientScript::POS_END);             

             $this->pageTitle = t("Razorpay");
             
             $this->render('create-checkout',[
                'redirect_url'=>$redirect_url,                
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
                                    
            $message = null; $redirect_url = null;            
            $razorpay_payment_id = Yii::app()->request->getPost('razorpay_payment_id', null);
            $razorpay_order_id = Yii::app()->request->getPost('razorpay_order_id', null);
            $razorpay_signature = Yii::app()->request->getPost('razorpay_signature', null);

            $order_uuid = Yii::app()->request->getPost('order_uuid', null); 
            $cart_uuid = Yii::app()->request->getPost('cart_uuid', null); 
            $request_from = Yii::app()->request->getPost('request_from', null); 
            $return_url = Yii::app()->request->getPost('return_url', null);             
            
            $data = AR_ordernew::model()->find('order_uuid=:order_uuid',array(':order_uuid'=>$order_uuid));
            if(!$data){
                $message = t("Order not found");                
                $this->msg = $message;
                $this->details = [
                    'redirect_url'=>CommonUtility::failedRedirect($request_from,$return_url,$message)
                ];
                $this->responseJson();
                Yii::app()->end();
            }
            
            $order_id = $data->order_id;
            $merchant_id = $data->merchant_id;
            $payment_code = $data->payment_code;
            $merchants = CMerchantListingV1::getMerchant( $merchant_id );			
			$credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,$merchants->merchant_type);
            
            $credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';            
			$is_live = isset($credentials['is_live'])?$credentials['is_live']:'';			            
			$is_live = $is_live==1?true:false;
			$key_id = $credentials['attr1'] ?? null;
			$key_secret = $credentials['attr2'] ?? null;
            
            $api = new Api($key_id,$key_secret);

            $attributes  = array(
                'razorpay_signature'  => $razorpay_signature,
                'razorpay_payment_id'  => $razorpay_payment_id,
                'razorpay_order_id' => $razorpay_order_id
            );			            
            $api->utility->verifyPaymentSignature($attributes);

            $exchange_rate = $data->exchange_rate>0?$data->exchange_rate:1;			  
		    $total = Price_Formatter::convertToRaw( ($data->total*$exchange_rate) );
            
            $options = OptionsTools::find(['multicurrency_enabled','multicurrency_enabled_checkout_currency']);
            $multicurrency_enabled = $options['multicurrency_enabled'] ?? false;
            $multicurrency_enabled = $multicurrency_enabled==1?true:false;		   	
            $enabled_checkout_currency = $options['multicurrency_enabled_checkout_currency'] ?? false;
            $enabled_force = $multicurrency_enabled==true? ($enabled_checkout_currency==1?true:false) :false;        
            $use_currency_code = $data->use_currency_code;                        	
            if($enabled_force){
                if($force_result = CMulticurrency::getForceCheckoutCurrency($data->payment_code,$use_currency_code)){					 					 
                    $use_currency_code = $force_result['to_currency'];
                    $total = Price_Formatter::convertToRaw($total*$force_result['exchange_rate'],2);
                }
            }
                        
            /*CAPTURE PAYMENT*/
            $api->payment->fetch($razorpay_payment_id)->capture(array(
                'amount'=>($total*100),
                'currency' => $use_currency_code
            ));

            $transaction_id = $razorpay_payment_id;
            $data->scenario = "new_order";
            $data->status = COrders::newOrderStatus();
            $data->payment_status = CPayments::paidStatus();
            $data->cart_uuid = $cart_uuid;
            $data->save();

            $model = new AR_ordernew_transaction;
            $model->order_id = $data->order_id;
            $model->merchant_id = $data->merchant_id;
            $model->client_id = $data->client_id;
            $model->payment_code = $data->payment_code;                
            $model->trans_amount = $data->amount_due>0? $data->amount_due: $data->total;
            $model->currency_code = $data->use_currency_code;		
            $model->payment_reference = $transaction_id;
            $model->status = CPayments::paidStatus();
            $model->reason = '';
            if($model->save()){
                $params = array(  
                    array('transaction_id'=>$model->transaction_id,'order_id'=>$data->order_id, 
                    'meta_name'=>'razorpay_payment_id', 'meta_value'=>$razorpay_payment_id ),
                                           
                    array('transaction_id'=>$model->transaction_id,'order_id'=>$data->order_id, 
                    'meta_name'=>'razorpay_order_id', 'meta_value'=>$razorpay_order_id ),
                    
                    array('transaction_id'=>$model->transaction_id,'order_id'=>$data->order_id, 
                    'meta_name'=>'razorpay_signature', 'meta_value'=>$razorpay_signature ),
                     
                 );
                 $builder=Yii::app()->db->schema->commandBuilder;
                 $command=$builder->createMultipleInsertCommand('{{ordernew_trans_meta}}',$params);
                 $command->execute();                 
                 $message = t("Payment successful. please wait while we redirect you.");

                 if($request_from=="app"){
                    if(!empty($return_url)){
                        $redirect_url = $return_url."/account/trackorder?".http_build_query(['order_uuid'=>$order_uuid]);
                    } else $redirect_url = APP_CUSTOM_URL_SCHEME."://payment-callback?".http_build_query(['status'=>'successful','order_id'=>$order_uuid]);
                 }                 
                 $this->code = 1;
                 $this->msg = "Ok";
                 $this->details = [
                    'redirect_url'=>$redirect_url
                 ];
                 $this->responseJson();
                 Yii::app()->end();
            } else $message = $model->getErrors();            

        } catch (Exception $e) {
			$message = $e->getMessage();
		}                
        $this->msg = $message;
        $this->details = [
            'redirect_url'=>CommonUtility::failedRedirect($request_from,$return_url,$message)
        ];
        $this->responseJson();
    }

    // WALLET LOADING
    public function actionprocesspayment()
    {
        try {

            $message = null; 
            $redirect_url = null;

            $request_from = Yii::app()->request->getQuery('request_from', '');
            $return_url = Yii::app()->request->getQuery('return_url', '');
            $return_url =  rtrim($return_url, "/"); 
            $return_url = (empty($return_url) || $return_url === 'null') ? '' : $return_url; 
            $data = Yii::app()->request->getQuery('data', '');            

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
			$key_id = $credentials['attr1'] ?? null;
			$key_secret = $credentials['attr2'] ?? null;
            
            $first_name = $decoded['customer_details']->first_name;
            $last_name = $decoded['customer_details']->last_name;
            $full_name = "$first_name $last_name";
            $email_address = $decoded['customer_details']->email_address;
            $contact_phone = $decoded['customer_details']->contact_phone;            

            $model = AR_client_meta::model()->find('client_id=:client_id AND meta1=:meta1 AND meta2=:meta2 
			AND meta3=:meta3 ', 
		    array( 
		      //':client_id'=>intval(Yii::app()->user->id),
              ':client_id'=>intval($client_id),
		      ':meta1'=>$payment_code,
		      ':meta2'=>$is_live,
		      ':meta3'=>isset($credentials['merchant_id'])?$credentials['merchant_id']:'',
		    ));             

            $create = false; $customer_id='';
		    if($model){
		    	if(empty($model->meta4)){
		    		$create = true;
		    	} else $customer_id = $model->meta4;
		    } else $create = true;
            
            $api = new Api($key_id,$key_secret);

            if($model){
		    	if(empty($model->meta4)){
		    		$create = true;
		    	} else $customer_id = $model->meta4;
		    } else $create = true;

            if($create){
                try {										
					$customer = $api->customer->create(array(
					   'name' => $full_name,
					   'email' => $email_address,
					   'contact'=> $contact_phone,
					   'fail_existing'=>0,
					   'notes'=> array(
					          'client_id'=> $client_id
					       )
					));					
					$customer_id = $customer->id;
                    $model = new AR_client_meta;
			    	$model->client_id = intval(Yii::app()->user->id);
			    	$model->meta1 = $payment_code;
			    	$model->meta2 = $is_live;
			    	$model->meta3 = $credentials['merchant_id'];
			    	$model->meta4 = $customer_id;
			    	$model->save();		
				} catch (Exception $e) {
					$message = $e->getMessage();                                        
                    $this->redirect(CommonUtility::failedRedirectWallet($request_from,$return_url,$message));
				}
            }

            $amount_in_paise = intval(round($amount * 100));                      
            
            $transaction_type = 'wallet_loading';

            if($request_from=="app"){
                if(!empty($return_url)){
                    $redirect_url = $return_url;
                } else $redirect_url = APP_CUSTOM_URL_SCHEME."://payment-callback?status=cancel";
            } else {
                $redirect_url = Yii::app()->createAbsoluteUrl("/account/wallet");
            }                 
             
            $order = $api->order->create([
                'receipt'=>$reference_id,
                'amount'=>$amount_in_paise,
                'currency'=>$currency_code,
                'notes'=>array(
                    'reference_id'=>$reference_id,
                    'transaction_type'=>$transaction_type
                )
             ]); 
             
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
                          
             $ajax_url = Yii::app()->createAbsoluteUrl("/$payment_code/api");                    
             $params = [
                'reference_id'=>$reference_id,                
                'request_from'=>$request_from,
                'return_url'=>$redirect_url,
             ];

             $callback_url = Yii::app()->createAbsoluteUrl("/$payment_code/api/verifywallet?".http_build_query($params));              
             $payment_processing = CommonUtility::safeTranslate("Payment processing, please don't close this window");             
             
             $options = OptionsTools::find(['website_title']);            
             $restaurant_name = $options['website_title'] ?? '';
             $cart_uuid = null;
             $verifyMethod = "verifywallet";                          
             
             ScriptUtility::registerScript(array(
                "var verifyMethod='".CJavaScript::quote($verifyMethod)."';",
                "var key_id='".CJavaScript::quote($key_id)."';",
                "var customer_id='".CJavaScript::quote($customer_id)."';",
                "var restaurant_name='".CJavaScript::quote($restaurant_name)."';",
                "var payment_description='".CJavaScript::quote($payment_description)."';",
                "var order_id='".CJavaScript::quote($order->id)."';",
                "var currency_code='".CJavaScript::quote($currency_code)."';",
                "var amount='".CJavaScript::quote($amount_in_paise)."';",              
                "var transaction_type='".CJavaScript::quote($transaction_type)."';",  
                "var ajax_url='".CJavaScript::quote($ajax_url)."';",                
                "var callback_url='".CJavaScript::quote($callback_url)."';",  
                "var order_uuid='".CJavaScript::quote($reference_id)."';",  
                "var cart_uuid='".CJavaScript::quote($cart_uuid)."';",  
                "var request_from='".CJavaScript::quote($request_from)."';",  
                "var return_url='".CJavaScript::quote($return_url)."';",  
                "var payment_processing='".CJavaScript::quote($payment_processing)."';",  
                "var redirect_url='".CJavaScript::quote($redirect_url)."';",  
               ),'razorpay');	

               ScriptUtility::registerCSS([
                'https://cdn.jsdelivr.net/npm/quasar@2.14.5/dist/quasar.prod.css',
                'https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900|Material+Icons'
             ]);             
             ScriptUtility::registerJS([
                 Yii::app()->baseUrl."/assets/vendor/axios.min.js", 
                'https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.prod.js',
                'https://cdn.jsdelivr.net/npm/quasar@2.14.5/dist/quasar.umd.prod.js',
                'https://checkout.razorpay.com/v1/checkout.js',
                Yii::app()->baseUrl."/protected/modules/$payment_code/assets/js/payment-razorpay.js?version=".time()
             ],CClientScript::POS_END);             

            $this->pageTitle = t("Razorpay");
             
            $this->render('create-checkout',[
               'redirect_url'=>$redirect_url,                
            ]);

        } catch (Exception $e) {
			$message = $e->getMessage();
            $this->redirect(CommonUtility::failedRedirectWallet($request_from,$return_url,$message));
		}                
    }

//     Array
// (
//     [razorpay_payment_id] => pay_QIvdJTLejNsF5i
//     [razorpay_order_id] => order_QIvdAxstHVllQq
//     [razorpay_signature] => f22524b12704ada9970548af463cac6950c87ad1d7cf46b1296602084f788d38
//     [order_uuid] => 5839194d-1927-11f0-8f6e-9c5c8e164c2c
//     [cart_uuid] => 
//     [request_from] => app
//     [return_url] => http://localhost:9000/#
// )

    public function actionverifywallet()
    {
        try {
                        
            $redirect_url=''; $message ='';
            $payment_code = RazorpayModule::paymentCode();         
            $reference_id = Yii::app()->request->getPost('order_uuid', null);
            $request_from = Yii::app()->request->getPost('request_from', 'web');                          
            $return_url = Yii::app()->request->getPost('return_url', '');                         
            $return_url =  rtrim($return_url, "/");    
            $razorpay_signature = Yii::app()->request->getPost('razorpay_signature', null);
            $razorpay_payment_id = Yii::app()->request->getPost('razorpay_payment_id', null);
            $razorpay_order_id = Yii::app()->request->getPost('razorpay_order_id', null);
            
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
			$key_id = $credentials['attr1'] ?? null;
			$key_secret = $credentials['attr2'] ?? null;

            $api = new Api($key_id,$key_secret);
            $attributes  = array(
                'razorpay_signature'  => $razorpay_signature,
                'razorpay_payment_id'  => $razorpay_payment_id,
                'razorpay_order_id' => $razorpay_order_id
            );			            

            $api->utility->verifyPaymentSignature($attributes);
            
            $amount = $decoded['amount'] ?? 0;
            $currency_code = $decoded['currency_code'] ?? null;
            $amount_in_paise = intval(round($amount * 100));   
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

            /*CAPTURE PAYMENT*/
            $api->payment->fetch($razorpay_payment_id)->capture(array(
                'amount'=>$amount_in_paise,
                'currency' => $currency_code
            ));

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

            $payment_id = $razorpay_payment_id;
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
                    //$redirect_url = $return_url."/account/wallet?".http_build_query(['message'=>$message]);
                    $redirect_url = "{$return_url}?".http_build_query(['message'=>$message]);
                } else $redirect_url = APP_CUSTOM_URL_SCHEME."://payment-callback?".http_build_query([
                    'status'=>'wallet_succesful',
                    'amount'=>$transaction_amount,
                    'payment_name'=>$payment_name,
                    'transaction_id'=>$payment_id,
                    'transaction_date'=>Date_Formatter::dateTime(date('c'))
                ]);
            } else $redirect_url = Yii::app()->createAbsoluteUrl("/account/wallet?".http_build_query(['message'=>$message]));            
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

            $logs = '';
			$payment_code = RazorpayModule::paymentCode();			

			$payload = file_get_contents("php://input");            
			$payload = json_decode($payload,true);		            

            if(empty($payload)){                
                Yii::log( "The response is an empty array." , CLogger::LEVEL_INFO);
				http_response_code(200);
				Yii::app()->end();
            }            

            $event_type = $payload['event'] ?? null;
            if(!$event_type){             
                Yii::log( "Invalid event." , CLogger::LEVEL_INFO);   
				http_response_code(200);
				Yii::app()->end();
            }
                        
            $payload_data = $payload['payload'] ?? null;            
            $payment_reference = $payload_data['payment']['entity']['id'] ?? null;
            $reference_id = $payload_data['payment']['entity']['notes']['reference_id'] ?? null;
            $transaction_type = $payload_data['payment']['entity']['notes']['transaction_type'] ?? null;
            $cart_uuid = $payload_data['payment']['entity']['notes']['cart_uuid'] ?? null;            

            // GET CREDENTIALS
            $merchant_type=0; $merchant_id=0;
            $merchantID = Yii::app()->request->getQuery('merchant_id', 0);
            $merchantID = intval($merchantID);
            if($merchantID>0){
                $merchant_id = $merchantID;
                $merchant_type = CMerchants::getMerchantType($merchant_id);
            }

            $credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,$merchant_type);
            $credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';          
            $is_live = isset($credentials['is_live'])?$credentials['is_live']:'';			            
			$is_live = $is_live==1?true:false;
			$key_id = $credentials['attr1'] ?? null;
			$key_secret = $credentials['attr2'] ?? null;
                        
            $payment_status = CPayments::paidStatus();
            
            switch ($event_type) {
                case "payment.captured":
                      if($transaction_type=="purchase_food"){
                          $order = COrders::get($reference_id);                          
                          $order_id = $order->order_id;            
                          $order->scenario = "update_payment_status";					
                          $order->payment_status = $payment_status;
                          $order->cart_uuid = $cart_uuid;
                          $order->save();

                          $model_payment = AR_ordernew_transaction::model()->find("payment_reference=:payment_reference",[
                            ':payment_reference'=>$payment_reference
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
                            $model->payment_reference = $payment_reference;
                            $model->status = $payment_status;
                            $model->reason = '';                                
                            $model->save();		   
                          }       

                          $order_model = COrders::getWithoutCache($reference_id);                
                          $order_status = $order_model->status;
                          $order_model->scenario = "new_order";
                          $order_model->status = COrders::newOrderStatus();   
                          if($order_status=='draft'){                        
                                $order_model->save();                            
                          }                                
                          $logs = "Successful payment $payment_code - OrderID#$order_id";
                      } else if ($transaction_type=="wallet_loading"){                        
                         $model = AR_wallet_transactions_meta::model()->find("meta_name=:meta_name",[
                            ':meta_name'=>$reference_id
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
                                'reference_id'=>$payment_reference,
                                'reference_id1'=>CDigitalWallet::transactionName(),
                                'merchant_base_currency'=>$currency_code,
                                'admin_base_currency'=>$currency_code,
                                'meta_name'=>'topup',
                                'meta_value'=>$card_id,
                                'meta_array'=>$meta_array,			
                           ]);
                           $logs = "$payment_code wallet loading succesful => $reference_id";

                         } else $logs = "Payment reference not found";  
                      } else {                         
                         $logs = "Invalid transaction type=> $transaction_type";
                      }
                    break;

                default:
                   $logs = "Invalid event type => $event_type";
                   break;
            }
            
        } catch (Exception $e) {
			$logs =  $e->getMessage();
		}
        
        Yii::log( json_encode($logs) , CLogger::LEVEL_ERROR);
		http_response_code(200);
    }

}
// end class