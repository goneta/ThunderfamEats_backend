<?php
require 'php-jwt/vendor/autoload.php';
require 'stripe/vendor/autoload.php';
require 'stripe2/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\Translation\Dumper\DumperInterface;

class Apiv2Controller extends CController
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
		
		return true;
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

    public function actioncreateIntent()
    {
        try {
            
			$key = isset($this->data['key'])?trim($this->data['key']):'';
			$payment_code = isset($this->data['payment_code'])?trim($this->data['payment_code']):'';
            $total = isset($this->data['total'])?$this->data['total']:0;
			$amount = $total;
            $payment_description = isset($this->data['payment_description'])?$this->data['payment_description']:'';
            $currency_code = isset($this->data['currency_code'])?$this->data['currency_code']:'';
			$payment_reference = isset($this->data['payment_reference'])?$this->data['payment_reference']:'';						
			$payment_type = isset($this->data['payment_type'])?$this->data['payment_type']:'';						

			$options = OptionsTools::find([
				'multicurrency_enabled','multicurrency_enabled_checkout_currency'
			]);			
			$multicurrency_enabled = $options['multicurrency_enabled']?$options['multicurrency_enabled']:false;
			$multicurrency_enabled = $multicurrency_enabled==1?true:false;		   	
			$enabled_checkout_currency = $options['multicurrency_enabled_checkout_currency']?$options['multicurrency_enabled_checkout_currency']:false;
			$enabled_force = $multicurrency_enabled==true? ($enabled_checkout_currency==1?true:false) :false;
			
			if($enabled_force){
				if($force_result = CMulticurrency::getForceCheckoutCurrency($payment_code,$currency_code)){					 					 				   
				   $currency_code = $force_result['to_currency'];
				   $amount = Price_Formatter::convertToRaw($total*$force_result['exchange_rate'],2);
				}
		    } 

            $stripe = new \Stripe\StripeClient($key);
			$paymentIntent = $stripe->paymentIntents->create([
				'amount'=>($amount*100),
				'currency' => $currency_code,				
				'description'=>$payment_description,
				'automatic_payment_methods' => [
					'enabled' => true,
				],
				'metadata'=>[
					'payment_reference'=>$payment_reference,					
					'payment_type'=>$payment_type
				 ]
			]);
			$this->code = 1;
			$this->msg = "Ok";		
			$this->details =[
				'client_secret'=>$paymentIntent->client_secret
			];
        } catch (Exception $e) {
			$this->msg = t($e->getMessage());		
		}		
		$this->responseJson();
    }

	public function actioncreatesubscriptions()
	{
		try {
			
			$payment_code = StripeModule::paymentCode();			
			$payment_id = Yii::app()->input->post('payment_id');			
			$payment = Cplans::getPaymentCreated($payment_id);				
			$package_id = $payment->package_id;	
			$subscriber_id = $payment->subscriber_id;
			$subscriber_type = $payment->subscriber_type;			

			$customer = Cplans::getSubscriberInformation($subscriber_id,$subscriber_type);			

			$meta_name = "plan_price_$payment_code";						
			$price = Cplans::planPriceID($meta_name,$package_id);
			$price_id = $price->meta_value;	
			
			//$success_url = Yii::app()->createAbsoluteUrl($payment_code."/apiv2/subscription_callback?payment_id=".$payment_id);
			$success_url = $payment->success_url;
			$cancel_url = Yii::app()->createAbsoluteUrl("/paymentplan?payment_id=".$payment_id);
			
			$credentials = CPayments::getPaymentCredentials(0,$payment_code);
			$credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';									
			$secret = isset($credentials['attr1'])?$credentials['attr1']:'';
			$is_live = isset($credentials['is_live'])?$credentials['is_live']:0;

			$customer_id = null;			
			
			// CREATE CUSTOMER
			if($customer_data = Cplans::getCustomerID($payment_code,$subscriber_id,$subscriber_type,$is_live)){				
				$customer_id = $customer_data->customer_id;
			} else {				
				$stripe = new \Stripe\StripeClient($secret);							
				$resp_customer  = $stripe->customers->create([
					'name' => $customer['full_name'],
					'email' => $customer['contact_email'],
				]);						
				$customer_id = $resp_customer->id;
				$livemode = $resp_customer->livemode?1:0;
				$model_customer = new AR_plans_customer();
				$model_customer->payment_code = $payment_code;
				$model_customer->subscriber_id = $subscriber_id;
				$model_customer->subscriber_type = $subscriber_type;
				$model_customer->customer_id = $customer_id;
				$model_customer->livemode = $livemode;
				$model_customer->save();
			}			
						

			//https://stackoverflow.com/questions/64732447/trial-period-in-checkout-session-in-stripe

			$plans = Cplans::get($package_id);
			$trial_period = $plans->trial_period;

			$params = [
				'success_url' => $success_url,
				'cancel_url' => $cancel_url,
				'customer'=>$customer_id,
				'mode' => 'subscription',
				'metadata'=>[
					'payment_id'=>$payment_id
				],
				'line_items' => [[
				  'price' => $price_id,				  
				  'quantity' => 1,
				]],				
			];			
			if($trial_period>0){
				$params['subscription_data'] = [
					'trial_period_days'=>$trial_period
				];
			}

			\Stripe\Stripe::setApiKey($secret);
			$session = \Stripe\Checkout\Session::create($params);


			try {
				$subscriber_model =  Cplans::getSubscriberRecords($subscriber_id,$subscriber_type,'model');
				$subscriber_model->package_id = $package_id;
				$subscriber_model->package_payment_code = $payment_code;
				$subscriber_model->save();
			} catch (Exception $e) {}	

			$this->code = 1;
			$this->msg = "Ok";			
			$this->details = [
				'checkout_url'=>$session->url
			];

		} catch (Exception $e) {
			$this->msg = t($e->getMessage());					
		}		
		$this->responseJson();
	}

	public function actioncancelsubscriptions()
	{
		try {

			$subscription_id = Yii::app()->input->get('subscription_id');

			$payment_code = StripeModule::paymentCode();
		    $credentials = CPayments::getPaymentCredentials(0,$payment_code);
		    $credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';											   
		    $secret = isset($credentials['attr1'])?$credentials['attr1']:'';
		    $is_live = isset($credentials['is_live'])?$credentials['is_live']:0;		    
			
			$stripe = new \Stripe\StripeClient($secret);
			$resp = $stripe->subscriptions->cancel($subscription_id, []);			

			$model = Cplans::getSubscriptionByID($subscription_id);							
			$model->status = 'cancelled';
			$model->save();			
			$this->code = 1;
			$this->msg = "Ok";

		} catch (Exception $e) {
			$this->msg = t($e->getMessage());		
		}		
		$this->responseJson();
	}

	public function actionupdatesubscriptions()
	{
		try {

			//https://docs.stripe.com/billing/subscriptions/upgrade-downgrade?lang=php#see-also

			$payment_id = Yii::app()->input->post('payment_id');
			$payment_code = StripeModule::paymentCode();

			$payment = Cplans::getPaymentCreated($payment_id);				
			$package_id = $payment->package_id;		
			$subscriber_id = $payment->subscriber_id;
			$subscriber_type = $payment->subscriber_type;
			$sucess_url = $payment->success_url;
			$failed_url = $payment->failed_url;	

			$meta_name = "plan_price_$payment_code";						
			$price = Cplans::planPriceID($meta_name,$package_id);
			$price_id = $price->meta_value;						
			
			$credentials = CPayments::getPaymentCredentials(0,$payment_code);
			$credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';			
			$secret = isset($credentials['attr1'])?$credentials['attr1']:'';			
			$is_live = isset($credentials['is_live'])?$credentials['is_live']:0;

			$next_actions = '';
			$subscription_id = '';
			$active_plan = null;

			try {
				$active_plan =  Cplans::getActiveSubscriptions2($subscriber_id,$subscriber_type,$payment_code);				
				$subscription_id = $active_plan->subscription_id;						    
			} catch (Exception $e) {	
				$next_actions = 'subscribe';
			}				
			
			$stripe = new \Stripe\StripeClient($secret);		

			$subscription_item_id = null;

			if($active_plan){	
				$customer = Cplans::getCustomerID($payment_code,$subscriber_id,$subscriber_type,$is_live);
				if($customer){
					$customer_id = $customer->customer_id;				
					$resp_customer = $stripe->subscriptions->all(['customer' => $customer_id]);				
					$data = isset($resp_customer['data']) ? $resp_customer['data'] : null;
					if(is_array($data) && count($data)>=1){
						$data = isset($data[0]) ? $data[0] : null;
						$items = isset($data['items']) ? $data['items']['data'][0] : null;
						$subscription_item_id = $items['id'];
					} else {					
						$items = isset($data['items'])?$data['items']:null;
						$subscription_item_id = isset($items['data'])? (isset($items['data']['id'])?$items['data']['id']:null) :null;
					}			
				} else {
					$this->msg = t("Invalid Customer Id");
					$this->responseJson();
				}				
		    }			

			if($active_plan){				
				$resp = $stripe->subscriptions->update(
					$subscription_id,
					[
					  'items' => [
						[
						  //'id' => 'si_QTbhHMD8WNwbvN',
						  'id'=>$subscription_item_id,
						  'price' => $price_id,
						],
					  ],
					  'metadata'=>[
						'payment_id'=>$payment_id
					  ]
					],					
				);			
				$active_plan->status = "active";
				$active_plan->package_id = $package_id;
				$active_plan->save();				
			}

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'next_actions'=>$next_actions,
				'redirect_url'=>$sucess_url
			];			
			
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());		
		}		
		$this->responseJson();
	}

	public function actionWebhooksplans()
	{
		try {
			
		   $logs = [];
		   $payment_code = StripeModule::paymentCode();
		   $credentials = CPayments::getPaymentCredentials(0,$payment_code);
		   $credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';											   
		   $secret = isset($credentials['attr1'])?$credentials['attr1']:'';
		   $is_live = isset($credentials['is_live'])?$credentials['is_live']:0;
		   $webhook_secret = isset($credentials['attr3'])?$credentials['attr3']:'';

		   $sig_header = isset($_SERVER['HTTP_STRIPE_SIGNATURE'])?$_SERVER['HTTP_STRIPE_SIGNATURE']:null;
		   $payload = file_get_contents("php://input");		   
		   $payload = json_decode($payload,true);
		   		   

		   if (is_array($payload) && !empty($payload)) {
				//echo "The response is a non-empty array.";
			} elseif (is_array($payload)) {				
				Yii::log( "The response is an empty array." , CLogger::LEVEL_INFO);
				http_response_code(200);
				Yii::app()->end();
			} else {				
				Yii::log( "The response is not an array." , CLogger::LEVEL_INFO);
				http_response_code(200);
				Yii::app()->end();
			}            
		
		   		   
		   $event = isset($payload['type'])?$payload['type']:null;
		   $webhook_id = isset($payload['id'])?$payload['id']:null;		   		   
		   		
		   if(Cplans::isWebhookFound($webhook_id)){
			    Yii::log( "Webook even already exist $webhook_id" , CLogger::LEVEL_INFO);
				http_response_code(200);
				Yii::app()->end();
		   }
		   
		   switch ($event) {
			 case "checkout.session.completed":

					$data = isset($payload['data'])?  (isset($payload['data']['object']) ?$payload['data']['object'] :null )  :null;		   		   
					$payment_id = isset($data['metadata'])? (isset($data['metadata'])?$data['metadata']['payment_id']:'') : null;
					$subscription_id = isset($data['subscription'])?$data['subscription']:'';
					$currency_code = isset($data['currency'])?$data['currency']:'';
					$amount = isset($data['amount_total'])?$data['amount_total']:'';
					$amount = $amount>0? ($amount/100) : 0;
					
					$payment = Cplans::getPaymentCreated($payment_id);	
					$package_id = $payment->package_id;		
					$subscriber_id = $payment->subscriber_id;
					$subscriber_type = $payment->subscriber_type;	
					$jobs =	$payment->jobs;

					$stripe = new \Stripe\StripeClient($secret);
					$resp = $stripe->subscriptions->retrieve($subscription_id, []);				
					$created_at = date("Y-m-d",$resp->created);
					$current_start = date("Y-m-d",$resp->current_period_start);
					$current_end = date("Y-m-d",$resp->current_period_end);
					$next_due = date("Y-m-d",$resp->current_period_end);							

					$new_item_limit  = 0; $new_order_limit =0;
					$remaining_items = 0; $remaining_orders =0;
					
					$plans = Cplans::get($package_id);				
					$new_item_limit = $plans->item_limit;
					$new_order_limit = $plans->order_limit;
									
					$model = new AR_plan_subscriptions();
					$model->payment_id = $payment_id;
					$model->payment_code = $payment_code;
					$model->subscriber_id = $subscriber_id;
					$model->package_id = $package_id;
					$model->plan_name = $plans->title;
					$model->billing_cycle = $plans->package_period;
					$model->amount = floatval($amount);
					$model->currency_code = strtoupper($currency_code);
					$model->subscriber_type = $subscriber_type;
					$model->subscription_id = $subscription_id;		
					$model->status = 'active';				
					$model->created_at = $created_at;				
					$model->next_due = $next_due;			
					$model->expiration = $next_due;
					$model->current_start = $current_start;
					$model->current_end = $current_end;	
					$model->jobs = $jobs;
					$model->save();						
					
					$jobs = $model->jobs;
					$jobs_data = [
						'subscription_id'=>$subscription_id,
						'package_id'=>$model->package_id,
						'subscriber_type'=>$model->subscriber_type,
						'subscriber_id'=>$model->subscriber_id,		
						'is_new'=>1
					];					
					if (!class_exists($jobs)) {				
						Yii::log( "Job class $jobs does not exist." , CLogger::LEVEL_INFO);
						http_response_code(200);
						Yii::app()->end();										
					}
					$jobInstance = new $jobs($jobs_data);
					$jobInstance->execute();	

					// CANCEL OLD SUBSCRIPTIONS
					Cplans::cancelPaymentSubscriptions($subscriber_type,$subscriber_id,$payment_code);

					$logs[] = "STRIPE SUCCESS SESSION COMPLETED";
				break;
				
				case "invoice.paid":					
					$data = isset($payload['data'])?  (isset($payload['data']['object']) ?$payload['data']['object'] :null )  :null;		   		   					
					$amount = isset($data['amount_paid']) ? $data['amount_paid'] : 0;
					$amount = $amount>0? ($amount/100) : 0;			
					$currency_code = isset($data['currency'])?$data['currency']:'';
					
					$subscription_details = isset($data['subscription_details'])?$data['subscription_details']:null;
					$metadata = isset($subscription_details['metadata'])?$subscription_details['metadata']:null;
					$payment_id = isset($metadata['payment_id'])?$metadata['payment_id']:null;
					$subscription_id = isset($data['subscription'])?$data['subscription']:null;
															
					$payment = Cplans::getSubscriptionByID($subscription_id);
					$package_id = $payment->package_id;		
					$subscriber_id = $payment->subscriber_id;
					$subscriber_type = $payment->subscriber_type;			
					$jobs = $payment->jobs;
					
					$subscriber_model =  Cplans::getSubscriberRecords($subscriber_id,$subscriber_type,'model');
					if($subscriber_model){
												
						$title = ''; $billing_cycle='';
						try {
							$plan_model = Cplans::get($package_id);		
							$title = $plan_model->title;
							$billing_cycle = $plan_model->package_period;																										
						} catch (Exception $e) {}
						
						try {
							$model = Cplans::getSubscriptionByID($subscription_id);
						} catch (Exception $e) {
							$model = new AR_plan_subscriptions();
						}												
						
						$stripe = new \Stripe\StripeClient($secret);
						$resp = $stripe->subscriptions->retrieve($subscription_id, []);										
						$created_at = date("Y-m-d",$resp->created);
						$current_start = date("Y-m-d",$resp->current_period_start);
						$current_end = date("Y-m-d",$resp->current_period_end);
						$next_due = date("Y-m-d",$resp->current_period_end);	
												
						$model->package_id = $package_id;
						$model->plan_name = $title;
						$model->billing_cycle = $billing_cycle;
						$model->amount = floatval($amount);
						$model->currency_code = strtoupper($currency_code);
						$model->status = 'active';				
						$model->created_at = $created_at;				
						$model->next_due = $next_due;			
						$model->expiration = $next_due;
						$model->current_start = $current_start;
						$model->current_end = $current_end;	
						$model->save();			
												
						$jobs_data = [
							'subscription_id'=>$subscription_id,
							'package_id'=>$package_id,
							'subscriber_type'=>$subscriber_type,
							'subscriber_id'=>$subscriber_id,
							'is_new'=>false
						];
						if (!class_exists($jobs)) {				
							Yii::log( "Job class $jobs does not exist." , CLogger::LEVEL_INFO);
							http_response_code(200);
							Yii::app()->end();										
						}
						$jobInstance = new $jobs($jobs_data);
                        $jobInstance->execute();	
						
						$logs[] = "STRIPE SUCCESS INVOICE PAID";

					} else $logs[] = "Susbcriptions id not found";									
					break;

				 case "invoice.payment_failed":
					$data = isset($payload['data'])?  (isset($payload['data']['object']) ?$payload['data']['object'] :null )  :null;					
					$subscription_details = isset($data['subscription_details'])?$data['subscription_details']:null;
					$subscription_id = isset($data['subscription'])?$data['subscription']:null;					
					$model = Cplans::getSubscriptionByID($subscription_id);
					$model->status = 'payment failed';
					$model->save();
					$logs[] = "STRIPE PAYMENT FAILED";	
					CommonUtility::pushJobs("SubscriptionsPaymentFailed",[
						'id'=>$model->id,
						'language'=>Yii::app()->language
					]);	
					break;

				case "customer.subscription.deleted":
				case "subscription_schedule.canceled":
					$data = isset($payload['data'])?  (isset($payload['data']['object']) ?$payload['data']['object'] :null )  :null;					
					$subscription_id = isset($data['id'])?$data['id']:null;					
					$model = Cplans::getSubscriptionByID($subscription_id);
					$model->status = 'cancelled';
					$model->save();

					CommonUtility::pushJobs("SubscriptionsCancelled",[
						'id'=>$model->id,								
						'language'=>Yii::app()->language
					]);

					$logs[] = "STRIPE SUBSCRIPTIONS DELETED";					
					break;					
		   }

		   if(!empty($webhook_id)){
				$model_webhooks = new AR_plans_webhooks();
				$model_webhooks->id	 = $webhook_id;
				$model_webhooks->event_type	 = $event;			
				$model_webhooks->save();			
		   }

		} catch (Exception $e) {
			$logs[] = $e->getMessage();
		}
		
		Yii::log( json_encode($logs) , CLogger::LEVEL_INFO);
		http_response_code(200);
	}
	

} 
// end class