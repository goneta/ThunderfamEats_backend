<?php
require 'php-jwt/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ApiController extends SiteCommon
{

    const META_NAME = 'stripe_session_id';
	
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
			
			$order_uuid = Yii::app()->input->get('order_uuid'); 
			$cart_uuid = Yii::app()->input->get('cart_uuid'); 
			$payment_uuid = Yii::app()->input->get('payment_uuid');
            $mobile_number = Yii::app()->input->get('phone');            
			$app = Yii::app()->input->get('app');
			$app = $app==1?true:false;         
            $absoluteURL = Yii::app()->input->get('absoluteURL');

			$order = COrders::get($order_uuid);					
			$merchant_id = $order->merchant_id;
			$payment_code = $order->payment_code;
			$client_id = $order->client_id;			
			$merchants = CMerchantListingV1::getMerchant( $merchant_id );			
			$credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,$merchants->merchant_type);			
			$customer = ACustomer::get($client_id);
			
			$address = AR_client_address::model()->find("client_id=:client_id",[
				':client_id'=>$client_id
			]);			

			$credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';			                        
            $subsription_key = isset($credentials['attr1'])?$credentials['attr1']:'';
            $is_live = isset($credentials['is_live'])?$credentials['is_live']:false;
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
                                    
            $params = [
                'amount'=>$total,
                'currency'=>$use_currency_code,                
                'externalId'=>time(),
                'payer'=>[
                    'partyIdType' => 'MSISDN',
                    'partyId' => $mobile_number
                ],
                'payerMessage'=> t("Payment to order number {order_id}",['{order_id}'=>$order->order_id]),
                'payeeNote'=> t("Payment to merchant {restaurant_name}",['{restaurant_name}'=>$merchants->restaurant_name]),
            ];
            
            MTNMomo::setSandbox($is_live);
            $transaction_uuid = MTNMomo::requestPay($subsription_key,$params);
            $params_url = [
                'transaction_uuid'=>$transaction_uuid,
                'order_uuid'=>$order_uuid,
                'cart_uuid'=>$cart_uuid,
                'app'=>$app==true?1:0
            ];            
            $successful_url = Yii::app()->createAbsoluteUrl("$payment_code/api/verifypayment?".http_build_query($params_url));
            $this->redirect($successful_url);
            Yii::app()->end();
		} catch (Exception $e) {
			$message = t($e->getMessage());		                        
			if($app){
                if(!empty($absoluteURL)){
                    $this->redirect( $absoluteURL."#checkout?message=".$message );
                } else $this->redirect( Yii::app()->createAbsoluteUrl($payment_code.'/api/failed?error='.$message ));				
			} else $this->redirect( Yii::app()->createAbsoluteUrl('account/checkout',['error'=>$message]) );			
		}			                		
	}    

    public function actioncancelpayment()
    {
        $app = Yii::app()->input->get('app');
        $absoluteURL = Yii::app()->input->get('absoluteURL');
        $payment_code = KlarnaModule::paymentCode();
        $app = $app==1?true:false;        
        if($app){
            if(!empty($absoluteURL)){
                $redirect = $absoluteURL."#checkout";
            } else $redirect = Yii::app()->createAbsoluteUrl("$payment_code/api/cancel");            
        } else $redirect = Yii::app()->createAbsoluteUrl("account/checkout");	
		$this->redirect($redirect);		
    }

    public function actionverifypayment()
    {
        try {
            
            $error = ''; $payment_code = MtnModule::paymentCode();
            $app = Yii::app()->input->get('app'); 
            $absoluteURL = Yii::app()->input->get('absoluteURL');            

            $transaction_uuid = Yii::app()->input->get('transaction_uuid');
            $order_uuid = Yii::app()->input->get('order_uuid');
            $cart_uuid = Yii::app()->input->get('cart_uuid');

            $this->layout = 'layout';
            $api_url = Yii::app()->createAbsoluteUrl("$payment_code/api/");       
            ScriptUtility::registerScript([
                "var mtn_api='$api_url';",
                "var transaction_uuid='".$transaction_uuid."';",		  
                "var order_uuid='".$order_uuid."';",		  
                "var cart_uuid='".$cart_uuid."';",		  
                "var is_app='".$app."';",		
            ],'mtn_api');     

            $this->render('verify-payment');
        } catch (Exception $e) {
            $error = t($e->getMessage());            
            if($app){
				$this->redirect( Yii::app()->createAbsoluteUrl("$payment_code/api/failed",['error'=>$error]) );
			} else $this->redirect( Yii::app()->createAbsoluteUrl('account/checkout',['error'=>$error]) );			
        }        
    }

    public function actiongetpaymentstatus()
    {
        try {
                        
            $code = 2; $msg = ''; $details = [];
            $transaction_uuid = Yii::app()->input->get('transaction_uuid');
            $order_uuid = Yii::app()->input->get('order_uuid');
            $cart_uuid = Yii::app()->input->get('cart_uuid'); 
            $app = Yii::app()->input->get('is_app');
            $app = $app==1?true:false;        
            
            $order = COrders::get($order_uuid);
            
            $payment_code = $order->payment_code;
            $merchant_id = $order->merchant_id;				
			$merchants = CMerchantListingV1::getMerchant( $merchant_id );			            
			$credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,$merchants->merchant_type);        
            $credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';
            $subsription_key = isset($credentials['attr1'])?$credentials['attr1']:'';
            $is_live = isset($credentials['is_live'])?$credentials['is_live']:false;
            $is_live = $is_live==1?true:false;
            
            MTNMomo::setSandbox($is_live);
            $resp = MTNMomo::paymentStatus($subsription_key,$transaction_uuid);
            $status = isset($resp['status'])? strtolower($resp['status'])  :'';
            $transaction_id = isset($resp['financialTransactionId'])? trim($resp['financialTransactionId'])  :'';            
                        
            switch ($status) {
                case 'successful':
                    $code = 1;
                    break;                  
                default:
                   $code = 3;
                    break;
            }

            $msg = "Ok";        
            $redirect = '';    
            
            if($code==1){
                $order->scenario = "new_order";
				$order->status = COrders::newOrderStatus();
				$order->payment_status = CPayments::paidStatus();
				$order->cart_uuid = $cart_uuid;
			    $order->save();                

                $model = new AR_ordernew_transaction;
				$model->order_id = $order->order_id;
				$model->merchant_id = $order->merchant_id;
				$model->client_id = $order->client_id;
				$model->payment_code = $order->payment_code;
				$model->trans_amount = $order->total;
				$model->currency_code = $order->use_currency_code;
				$model->payment_reference = $transaction_id;
				$model->status = CPayments::paidStatus();
				$model->reason = '';            
				$model->save();

                if($app){
                    $message = t("Payment Successful please wait we redirect you...");
                    $redirect = Yii::app()->createAbsoluteUrl("$payment_code/api/successful",['message'=>$message]);
                } else {
                    $redirect = Yii::app()->createAbsoluteUrl("orders/index",array(
                        'order_uuid'=>$order->order_uuid
                    ));					                  
                }                
            }            

            $details = [
                'status'=>$status,
                'transaction_id'=>$transaction_id,
                'redirect'=>$redirect
            ];
        } catch (Exception $e) {        
            $msg = t($e->getMessage());  
            if($app){
                $redirect = Yii::app()->createAbsoluteUrl("$payment_code/api/failed",array(
                    'error'=>$msg
                ));					
            } else {
                $redirect = Yii::app()->createAbsoluteUrl("account/checkout",array(
                    'error'=>$msg
                ));					
            }            
            $details = [
                'redirect'=>$redirect
            ];
        }

        header('Content-type: application/json');
		$resp=array('code'=>$code,'msg'=>$msg,'details'=>$details);
		echo CJSON::encode($resp);
		Yii::app()->end();
    }
    
    // WALLET LOADING
    public function actionprocesspayment()
    {
        try {

            $error = '';

            $data = Yii::app()->input->get("data");
            $app = Yii::app()->input->get("app");
            $absoluteURL = Yii::app()->input->get('absoluteURL');

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

            $customer_details = isset($decoded['customer_details'])?(array)$decoded['customer_details']:'';            

			$payment_details = isset($decoded['payment_details'])?(array)$decoded['payment_details']:'';
			$payment_customer_id = isset($payment_details['payment_customer_id'])?$payment_details['payment_customer_id']:'';
			$payment_method_id = isset($payment_details['payment_method_id'])?$payment_details['payment_method_id']:'';					            
			
			$credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,$merchant_type);
			$credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';
            $secret_key = isset($credentials['attr1'])?$credentials['attr1']:'';
            
            $params_success = [
                'payment_ref'=>$reference_id,
                'app'=>$app,
                'absoluteURL'=>$absoluteURL
            ];
            $params_cancel = [
                'app'=>$app,
                'absoluteURL'=>$absoluteURL
            ];
            $success_url = Yii::app()->createAbsoluteUrl("$payment_code/api/verifywalletpayment?".http_build_query($params_success));
            $cancel_url = Yii::app()->createAbsoluteUrl("$payment_code/api/cancelwalletpayment?".http_build_query($params_cancel));

            $params = [
                'payment_method_types' => ['card','klarna'],
                'line_items' => [[
                  'price_data' => [                    
                    'currency' => $currency_code,
                    'product_data' => [
                        'name' => $payment_description
                    ],
                    'unit_amount' => Price_Formatter::convertToRaw($amount)*100,
                  ],
                  'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $success_url,
                'cancel_url' => $cancel_url,
                'client_reference_id'=>$reference_id,
                'customer_email'=> isset($customer_details['email_address'])?$customer_details['email_address']:'',
                'metadata'=>[					
                    'order_type'=>"wallet_loading",
                    'reference_id'=>$reference_id
				]
            ];           
            
            $stripe = new \Stripe\StripeClient($secret_key);
            $result = $stripe->checkout->sessions->create($params);
            $session_id = isset($result['id'])?$result['id']:null;
                                    
            $model = new AR_wallet_transactions_meta();
			$model->meta_name = $reference_id;
			$model->meta_value = json_encode([
				'reference_id'=>$reference_id,
                'session_id'=>$session_id,
				'data'=>$data,
			]);
			$model->save();
            
            $this->redirect($result['url']);
            Yii::app()->end();

        } catch (Exception $e) {
			$error = t($e->getMessage());							            
		}        

        if($app==1){
            $redirect = Yii::app()->createAbsoluteUrl("$payment_code/api/failed",array(
                'error'=>$error
            ));	
        } else if ( !empty($absoluteURL) ){
            $redirect  = $absoluteURL;        
        } else {
            $redirect = Yii::app()->createAbsoluteUrl("account/wallet",array(
                'error'=>$error
            ));	
        }		
        $this->redirect($redirect);		
    }

    public function actionverifywalletpayment()
    {
        try {

            CommonUtility::mysqlSetTimezone();
            $stmt = "DELETE FROM {{wallet_transactions_meta}} 
            WHERE meta_name=".q(self::META_NAME)."
            AND date_created < NOW() - INTERVAL 1 HOUR;";
            Yii::app()->db->createCommand($stmt)->query();


            $error = '';  $failed_url = ''; $cancel_url = '' ;
            $payment_code = KlarnaModule::paymentCode();            
            $reference_id = Yii::app()->input->get('payment_ref');
            $absoluteURL = Yii::app()->input->get('absoluteURL');
            $back_url = Yii::app()->input->get('back_url');            
            $app = Yii::app()->input->get('app');
            $app = $app==1?true:false;            
                                    
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

                $merchant_id = isset($decoded['merchant_id'])?$decoded['merchant_id']:'';
			    $merchant_type = isset($decoded['merchant_type'])?$decoded['merchant_type']:'';
                $credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,$merchant_type);
                $credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';			                        
                $secret_key = isset($credentials['attr1'])?$credentials['attr1']:'';			
                                                
                
                $stripe = new \Stripe\StripeClient($secret_key);
                $result = $stripe->checkout->sessions->retrieve(
                    $stripe_session_id,
                    []
                );                     
                if ($result->payment_status != 'unpaid') {

                    $transaction_id = $result->payment_intent;

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
                        ':reference_id'=>$transaction_id
                    ]);

                    if(!$model_transaction){
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
                    }
                    $message = t("Payment successful. please wait while we redirect you.");                    
                    if($app){
                        if(!empty($absoluteURL)){                            
                            $this->redirect($absoluteURL."#account/wallet");
                        } else {
                            $this->redirect( Yii::app()->createAbsoluteUrl("$payment_code/api/successful",[
                                'message'=>$message
                            ]));
                        }                        
                    } else $this->redirect($successful_url."?message=$message");                    
                    Yii::app()->end();
                } else $error = t("Payment is not succesful with the status of {status}",[
                    '{status}'=>$result->payment_status
                ]);
            } else $error = t("Payment reference not found");
        } catch (Exception $e) {
            $error = t($e->getMessage());          
        }                
        $this->redirect($failed_url."/?error=".$error);
    }

    public function actioncancelwalletpayment()
    {
        $app = Yii::app()->input->get('app');
        $app = $app==1?true:false;        
        $payment_code = KlarnaModule::paymentCode();   
        $absoluteURL = Yii::app()->input->get('absoluteURL');         
        
        if($app){
            if(!empty($absoluteURL)){
                $redirect = $absoluteURL."#account/wallet";
            } else $redirect = Yii::app()->createAbsoluteUrl("$payment_code/api/cancel");	            
        } else if ( !empty($absoluteURL) ){
            $redirect  = $absoluteURL;        
        } else {
            $redirect = Yii::app()->createAbsoluteUrl("account/wallet");	
        }		
        $this->redirect($redirect);		
    }

    public function actionsuccessful()
	{
		$message = Yii::app()->input->get('message');
		echo $message;
	}

	public function actionfailed()
	{
		$error = Yii::app()->input->get('error');
		echo $error;
	}

	public function actioncancel()
	{
		echo t("Payment cancelled");
	}
        
    public function actionwebhooks()
    {
        try {

            $merchant_type=0; $merchant_id=0;
            $merchantID = intval(Yii::app()->input->get('merchant_id')); 
            if($merchantID>0){
                $merchant_id = $merchantID;
                $merchant_type = CMerchants::getMerchantType($merchant_id);
            }

            $logs = '';
            $payment_code = KlarnaModule::paymentCode();

            $credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,$merchant_type);
            $credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';            
            $endpoint_secret = isset($credentials['attr3'])?$credentials['attr3']:'';            

            $payload = @file_get_contents('php://input');            
            $sig_header = isset($_SERVER['HTTP_STRIPE_SIGNATURE'])?$_SERVER['HTTP_STRIPE_SIGNATURE']:'';

            try {
                \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
            } catch(\UnexpectedValueException $e) {               
               Yii::log( "Invalid payload" , CLogger::LEVEL_INFO);
               Yii::log( json_encode($e->getMessage()) , CLogger::LEVEL_ERROR);
               http_response_code(200);
               Yii::app()->end();
            } catch(\Stripe\Exception\SignatureVerificationException $e) {                              
               Yii::log( "Invalid signature" , CLogger::LEVEL_INFO);
               http_response_code(200);
               Yii::app()->end();
            }
                       
            $payload =  !empty($payload)?json_decode($payload,true):null;
            
            if (is_array($payload) && !empty($payload)) {                
                //
            } elseif (is_array($payload)) {
                Yii::log( "The response is an empty array." , CLogger::LEVEL_INFO);
                http_response_code(200);
                Yii::app()->end();
            } else {                
                Yii::log( "The response is not an array." , CLogger::LEVEL_INFO);
                http_response_code(200);
                Yii::app()->end();
            }                     
            
            $event_id = isset($payload['id'])?$payload['id']:null;
            $event_type = isset($payload['type'])?$payload['type']:null;
            $data = isset($payload['data']) ? (isset($payload['data']['object'])?$payload['data']['object']:null) : null;
            $metadata = isset($data['metadata'])?$data['metadata']:null;            
            $status = isset($data['status'])?$data['status']:null;                        
            $transaction_id = isset($data['payment_intent'])?$data['payment_intent']:null;            
            
            if ($status != 'unpaid') {
                $payment_status = CPayments::paidStatus();
            } else {
                $payment_status = $status;
            }            

            switch ($event_type) {
                case 'checkout.session.completed':
                case 'checkout.session.async_payment_succeeded':
                    $order_type = isset($metadata['order_type'])?$metadata['order_type']:null;                     

                    if($order_type=="purchase_food"){
                        $order_uuid = isset($metadata['order_uuid'])?$metadata['order_uuid']:null;
                        $cart_uuid = isset($metadata['cart_uuid'])?$metadata['cart_uuid']:null;
    
                        $order = COrders::get($order_uuid);
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
    
                        $order_model = COrders::getWithoutCache($order_uuid);                
                        $order_status = $order_model->status;
                        $order_model->scenario = "new_order";
                        $order_model->status = COrders::newOrderStatus();   
                        if($order_status=='draft'){                        
                            $order_model->save();                            
                        }                                
                        $logs = "Successful payment Stripehosted - OrderID#$order_id";

                    } else if  ($order_type=="wallet_loading"){       
                        
                        if ($status != 'unpaid') {
                            //
                        } else {
                            Yii::log( "Stripehosted payment status failed #$status" , CLogger::LEVEL_INFO);
		                    http_response_code(200);
                            Yii::app()->end();
                        }
                        
                        $reference_id = isset($metadata['reference_id'])?$metadata['reference_id']:null;                        

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
                                'reference_id'=>$transaction_id,
                                'reference_id1'=>CDigitalWallet::transactionName(),
                                'merchant_base_currency'=>$currency_code,
                                'admin_base_currency'=>$currency_code,
                                'meta_name'=>'topup',
                                'meta_value'=>$card_id,
                                'meta_array'=>$meta_array,			
                           ]);
                           $logs = "Stripehosted wallet loading succesful";

                        } else $logs = "Payment reference not found";
                    } else {
                        $logs = "Invalid order type => $order_type";
                    }                
                    break;                            
            }
        } catch (Exception $e) {
            $logs = $e->getMessage();	
        }
                
        Yii::log( $logs , CLogger::LEVEL_INFO);
		http_response_code(200);
    }    

} 
// end class