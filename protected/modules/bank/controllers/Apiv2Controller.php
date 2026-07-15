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

            $payment_id = Yii::app()->input->post('payment_id');
            $payment_code = BankModule::paymentCode();			

            $payment = Cplans::getPaymentCreated($payment_id);	
			$package_id = $payment->package_id;		
			$subscriber_id = $payment->subscriber_id;
			$subscriber_type = $payment->subscriber_type;
			$sucess_url = $payment->success_url;
			$failed_url = $payment->failed_url;			
			$jobs = $payment->jobs;		

            $plans = Cplans::get($package_id);                        
            $periods = Cplans::getDatebyperiod($plans->package_period);      
			
            $model = new AR_plan_subscriptions();
			$model->payment_id = $payment_id;
			$model->payment_code = $payment_code;
			$model->subscriber_id = $subscriber_id;
			$model->package_id = $plans->package_id;
			$model->amount = $plans->promo_price>0?$plans->promo_price:$plans->price;
			$model->subscriber_type = $subscriber_type;
			$model->subscription_id = CommonUtility::createUniqueTransaction("{{plan_subscriptions}}",'subscription_id',"BANK",5);
            $model->plan_name = $plans->title;
            $model->billing_cycle = $plans->package_period;
            $model->currency_code = AttributesTools::defaultCurrency();
            $model->created_at = CommonUtility::dateNow();
            $model->next_due = isset($periods['next_due'])?$periods['next_due']:null;
            $model->expiration = isset($periods['expiration'])?$periods['expiration']:null;
            $model->current_start = isset($periods['current_start'])?$periods['current_start']:null;
            $model->current_end = isset($periods['current_end'])?$periods['current_end']:null;
			$model->status = 'pending';
			$model->jobs= $jobs;
			$model->sucess_url = $sucess_url;
			$model->failed_url = $failed_url;					
            if($model->save()){

				try {
					$subscriber_model =  Cplans::getSubscriberRecords($subscriber_id,$subscriber_type,'model');
					$subscriber_model->package_id = $package_id;
					$subscriber_model->package_payment_code = $payment_code;
					$subscriber_model->save();
				} catch (Exception $e) {}				

                CommonUtility::pushJobs('Sendbanksubscription',[
                    'plan_subscriptions_id'=>$model->id
                ]);
                $redirect = Yii::app()->createAbsoluteUrl("/merchant/banktransferconfirmation",[
                    'payment_id'=>$payment_id
                ]);    
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [				
                    'redirect'=>$redirect
                ];    
            } else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
        } catch (Exception $e) {
			$this->msg = t($e->getMessage());					
		}		
		$this->responseJson();
    }

	public function actioncancelsubscriptions()
	{
		try {

			$subscription_id = Yii::app()->input->get('subscription_id');			
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

}
// end class