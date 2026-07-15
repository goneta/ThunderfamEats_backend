<?php
require 'php-jwt/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;


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
  
	public function actioncreatesubscriptions()
	{
		try {

			$payment_code = PaypalModule::paymentCode();			
			$payment_id = Yii::app()->input->post('payment_id');			
			$payment = Cplans::getPaymentCreated($payment_id);	
			$package_id = $payment->package_id;	
			$subscriber_id = $payment->subscriber_id;
			$subscriber_type = $payment->subscriber_type;

			$meta_name = "plan_price_$payment_code";						
			$price = Cplans::planPriceID($meta_name,$package_id);
			$price_id = $price->meta_value;	

			
			$callback_url = Yii::app()->createAbsoluteUrl($payment_code."/apiv2/subscription_callback?payment_id=".$payment_id);	

			try {
				$subscriber_model =  Cplans::getSubscriberRecords($subscriber_id,$subscriber_type,'model');
				$subscriber_model->package_id = $package_id;
				$subscriber_model->package_payment_code = $payment_code;
				$subscriber_model->save();
			} catch (Exception $e) {}	

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'price_id'=>$price_id,
				'callback_url'=>$callback_url
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());		
		}		
		$this->responseJson();
	}

	public function actionsubscription_callback()
	{
		try {
						
			$payment_id = Yii::app()->input->get('payment_id');			
			$subscription_id = Yii::app()->input->get('subscription_id');
			$payment_code = PaypalModule::paymentCode();

			$payment = Cplans::getPaymentCreated($payment_id);	
			$package_id = $payment->package_id;		
			$subscriber_id = $payment->subscriber_id;
			$subscriber_type = $payment->subscriber_type;
			$sucess_url = $payment->success_url;
			$failed_url = $payment->failed_url;		
			$jobs = $payment->jobs;		
			
			$plans = Cplans::get($package_id);			

			$credentials = CPayments::getPaymentCredentials(0,$payment_code);
			$credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';			
			$is_live = isset($credentials['is_live'])?intval($credentials['is_live']):0;
			
			CPaypalTokens::setProduction($is_live);
			CPaypalTokens::setCredentials($credentials,$payment_code);
			$token = CPaypalTokens::getToken(date("c"));			
			
			CPaypal::setProduction($is_live);
			CPaypal::setToken($token);		
			
			$resp = CPaypal::getSubscriptionDetails($subscription_id);
			$start_time = isset($resp['start_time'])?  date("Y-m-d",strtotime($resp['start_time']))  :'';			
			$create_time = isset($resp['create_time'])?  date("Y-m-d",strtotime($resp['create_time']))  :'';			
			$billing_info = isset($resp['billing_info'])?$resp['billing_info']:null;
			$next_due = isset($billing_info['next_billing_time'])?   date("Y-m-d",strtotime($billing_info['next_billing_time'])):null;
			$last_payment = isset($billing_info['last_payment'])?$billing_info['last_payment']:null;
			$data_amount = isset($last_payment['amount'])?$last_payment['amount']:null;
			$amount = isset($data_amount['value'])?$data_amount['value']:0;
			$currency_code = isset($data_amount['currency_code'])?$data_amount['currency_code']:0;
						
			$model = new AR_plan_subscriptions();
			$model->payment_id = $payment_id;
			$model->payment_code = $payment_code;
			$model->subscriber_id = $subscriber_id;
			$model->package_id = $package_id;
			$model->plan_name = $plans->title;
			$model->billing_cycle = $plans->package_period;
			$model->amount = floatval($amount);
			$model->currency_code = $currency_code;
			$model->subscriber_type = $subscriber_type;
			$model->subscription_id = $subscription_id;		
			$model->status = 'active';
			$model->jobs = $jobs;
			$model->sucess_url = $sucess_url;
			$model->failed_url = $failed_url;		
			$model->created_at = $create_time;				
			$model->next_due = $next_due;			
			$model->expiration = $next_due;
			$model->current_start = $start_time;
			$model->current_end = $next_due;			
			if($model->save()){				
				$this->redirect($sucess_url);
			} else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());			
		} catch (Exception $e) {
			$this->msg = $e->getMessage();			
		}			
		$this->redirect($failed_url);
	}

	public function actioncancelsubscriptions()
	{
		try {

			$subscription_id = Yii::app()->input->get('subscription_id');
			$payment_code = PaypalModule::paymentCode();
			$credentials = CPayments::getPaymentCredentials(0,$payment_code);
			$credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';			
			$is_live = isset($credentials['is_live'])?intval($credentials['is_live']):0;
			
			CPaypalTokens::setProduction($is_live);
			CPaypalTokens::setCredentials($credentials,$payment_code);
			$token = CPaypalTokens::getToken(date("c"));			
			
			CPaypal::setProduction($is_live);
			CPaypal::setToken($token);	
			
			$resp = CPaypal::CancelSubscriptions($subscription_id);

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

			$payment_id = Yii::app()->input->post('payment_id');
			$payment_code = PaypalModule::paymentCode();

			$payment = Cplans::getPaymentCreated($payment_id);	
			$package_id = $payment->package_id;		
			$subscriber_id = $payment->subscriber_id;
			$subscriber_type = $payment->subscriber_type;
			$sucess_url = $payment->success_url;
			$failed_url = $payment->failed_url;	
			$jobs = $payment->jobs;	
						
			$meta_name = "plan_price_$payment_code";						
			$price = Cplans::planPriceID($meta_name,$package_id);
			$price_id = $price->meta_value;						
			
			$credentials = CPayments::getPaymentCredentials(0,$payment_code);
			$credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';			
			$is_live = isset($credentials['is_live'])?intval($credentials['is_live']):0;
			
			CPaypalTokens::setProduction($is_live);
			CPaypalTokens::setCredentials($credentials,$payment_code);
			$token = CPaypalTokens::getToken(date("c"));			
			
			CPaypal::setProduction($is_live);
			CPaypal::setToken($token);	

			$next_actions = '';
			$subscription_id = '';
			$active_plan = null;

			try {
				$active_plan =  Cplans::getActiveSubscriptions2($subscriber_id,$subscriber_type,$payment_code);				
				$subscription_id = $active_plan->subscription_id;						    
			} catch (Exception $e) {	
				$next_actions = 'subscribe';
			}						

			if($active_plan){								
				CPaypal::UpdateSubscriptions($subscription_id,$price_id);		
				$active_plan->status = "active";
				$active_plan->package_id = $package_id;
				$active_plan->save();		
				
				// $jobs_data = [
				// 	'subscription_id'=>$subscription_id,
				// 	'package_id'=>$package_id,
				// 	'subscriber_type'=>$subscriber_type,
				// 	'subscriber_id'=>$subscriber_id
				// ];
				// if (class_exists($jobs)) {						
				// 	$jobInstance = new $jobs($jobs_data);
				//     $jobInstance->execute();		
				// }
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

			$logs = '';
			$payment_code = PaypalModule::paymentCode();			
			
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
									
			$event = isset($payload['event_type'])?$payload['event_type']:null;						
			$webhook_id = isset($payload['id'])?$payload['id']:null;
			
			if(Cplans::isWebhookFound($webhook_id)){
				Yii::log( "Webhook even already exist $webhook_id" , CLogger::LEVEL_INFO);
				http_response_code(200);
				Yii::app()->end();
			}			
		
			switch ($event) {
				case "BILLING.SUBSCRIPTION.ACTIVATED":
					$resource = isset($payload['resource'])?$payload['resource']:'';
					$subscription_id = isset($resource['id'])?$resource['id']:null;
					$model = Cplans::getSubscriptionByID($subscription_id);	
					CommonUtility::pushJobs("MerchantRegWelcome",[
						'merchant_id'=>$model->subscriber_id,
						'language'=>Yii::app()->language
					]);
					break;

				case "BILLING.SUBSCRIPTION.CREATED":
					$resource = isset($payload['resource'])?$payload['resource']:'';
					$subscription_id = isset($resource['id'])?$resource['id']:null;
					$start_time = isset($resource['start_time'])?  date("Y-m-d",strtotime($resource['start_time']))  :'';			
			        $create_time = isset($resource['create_time'])?  date("Y-m-d",strtotime($resource['create_time']))  :'';			
					
					$billing_info = isset($resource['billing_info'])?$resource['billing_info']:null;
					$next_due = isset($billing_info['next_billing_time'])?   date("Y-m-d",strtotime($billing_info['next_billing_time'])):null;
					$last_payment = isset($billing_info['last_payment'])?$billing_info['last_payment']:null;
					$data_amount = isset($last_payment['amount'])?$last_payment['amount']:null;
					$amount = isset($data_amount['value'])?$data_amount['value']:0;
					$currency_code = isset($data_amount['currency_code'])?$data_amount['currency_code']:0;
					
					$model = Cplans::getSubscriptionByID($subscription_id);					
					if($model){						
						$jobs = $model->jobs;
						$jobs_data = [
							'subscription_id'=>$subscription_id,
							'package_id'=>$model->package_id,
							'subscriber_type'=>$model->subscriber_type,
							'subscriber_id'=>$model->subscriber_id,		
							'is_new'=>false
						];
						if (!class_exists($jobs)) {				
							Yii::log( "Job class $jobs does not exist." , CLogger::LEVEL_INFO);
							http_response_code(200);
							Yii::app()->end();										
						}
						$jobInstance = new $jobs($jobs_data);
                        $jobInstance->execute();	
						
						// CANCEL OLD SUBSCRIPTIONS
						Cplans::cancelPaymentSubscriptions($model->subscriber_type,$model->subscriber_id,$model->payment_code);

					}
					break;
					
					case "BILLING.SUBSCRIPTION.CANCELLED":
						$resource = isset($payload['resource'])?$payload['resource']:'';
					    $subscription_id = isset($resource['id'])?$resource['id']:null;						
						$model = Cplans::getSubscriptionByID($subscription_id);							
						$model->status = 'cancelled';
					    $model->save();						

						CommonUtility::pushJobs("SubscriptionsCancelled",[
							'id'=>$model->id,								
							'language'=>Yii::app()->language
						]);

						$logs = "Paypal subscription cancelled $subscription_id";						
						break;

					case "BILLING.SUBSCRIPTION.PAYMENT.FAILED":
						$resource = isset($payload['resource'])?$payload['resource']:'';
					    $subscription_id = isset($resource['id'])?$resource['id']:null;						
						$model = Cplans::getSubscriptionByID($subscription_id);							
						$model->status = 'payment failed';
					    $model->save();						
						$logs = "Paypal subscription payment failed $subscription_id";
						CommonUtility::pushJobs("SubscriptionsPaymentFailed",[
							'id'=>$model->id,								
							'language'=>Yii::app()->language
						]);								
						break;

					default:
					break;
			}			

			if(!empty($webhook_id)){
				$model_webhooks = new AR_plans_webhooks();
				$model_webhooks->id	 = $webhook_id;
				$model_webhooks->event_type	 = $event;			
				$model_webhooks->save();			
			}

		} catch (Exception $e) {
			$logs =  $e->getMessage();
		}			
		    		
		Yii::log( json_encode($logs) , CLogger::LEVEL_ERROR);
		http_response_code(200);
	}	

} 
// end class