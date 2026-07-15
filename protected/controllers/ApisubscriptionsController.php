<?php
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
                     'getActivePlan','getsubscriptionhistory','setcancelsubscriptions'
                 ),
				 'expression' => array('MerchantIdentitys','verifyMerchant')
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
			'multicurrency_enabled','website_timezone_new','merchant_default_currency'
	    ));

		Yii::app()->params['settings'] = $settings;

		/*SET TIMEZONE*/
		$timezone = Yii::app()->params['settings']['website_timezone_new'];
		if (is_string($timezone) && strlen($timezone) > 0){
		   Yii::app()->timeZone=$timezone;
		}        

        if(!Yii::app()->merchant->isGuest){            
            Yii::app()->params['settings_merchant'] = OptionsTools::find(array(
				'merchant_default_currency','self_delivery','merchant_enabled_continues_alert'
			),Yii::app()->merchant->merchant_id);	            
        }        

        $multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		$multicurrency_enabled = $multicurrency_enabled==1?true:false;		
		$merchant_currency = isset(Yii::app()->params['settings_merchant']['merchant_default_currency'])?Yii::app()->params['settings_merchant']['merchant_default_currency']:'';	

        // if($multicurrency_enabled && !empty($merchant_currency)){            
        //     Price_Formatter::init($merchant_currency);	
        // } else {
        //     Price_Formatter::init();           
        // }		
		Price_Formatter::init();           
	}


}
// end class


class ApisubscriptionsController extends Apimerchantcommon
{

    public $merchant_id;

    public function beforeAction($action)
    {
        if(!Yii::app()->merchant->isGuest){  
            $this->merchant_id = Yii::app()->merchant->merchant_id;
        }           
		$this->initSettings();     
        return true;
    }

    public function actiongetactiveplan()
    {
        try {
            
            $data = Cplans::getSubscriberPlan($this->merchant_id,'merchant',Yii::app()->language);

			$features = AR_merchant_meta::getValue($this->merchant_id,'subscription_features');			
			$subscription_features = isset($features['meta_value'])? json_decode($features['meta_value'],true) :null;			

            $this->code = 1;
            $this->msg = "Ok";
            $this->details = [
                'data'=>$data,
				'features_list'=>AttributesTools::PlansFeatureList(),
				'subscription_features'=>$subscription_features
            ];
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }		
        $this->responseJson();  
    }

	public function actiongetsubscriptionhistory()
	{
		try {
			$data = Cplans::getSubscriptionHistory($this->merchant_id,'merchant',Yii::app()->language);
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

	public function actionsetcancelsubscriptions()
	{
		try {
						
			$subscription_id = Yii::app()->input->post('subscription_id');
			$model = AR_plan_subscriptions::model()->find("subscription_id=:subscription_id",[
				':subscription_id'=>$subscription_id
			]);
			if($model){
				$model->status = 'cancelled';
				if($model->save()){
					$this->code = 1;
					$this->msg = "Ok";
					try {
						$data = Cplans::getSubscriberPlan($this->merchant_id,'merchant',Yii::app()->language);            
					} catch (Exception $e) {
						$data = [];
					}
					$this->details = [
						'data'=>$data
					];
				} else $this->msg = CommonUtility::parseModelErrorToString( $model->getErrors(),"<br/>" );
			} else $this->msg = t(HELPER_RECORD_NOT_FOUND);

		} catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }		
        $this->responseJson();  
	}

	public function actiongetsubscriptionlist()
	{
		try {
			
			$subscription_type = Yii::app()->input->get('subscription_type');
			$package_id = Yii::app()->input->get('package_id');
			$currency_code = Yii::app()->request->getPost('currency_code', '');		
			$base_currency = Price_Formatter::$number_format['currency_code'];		
			
			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
			$multicurrency_enabled = $multicurrency_enabled==1?true:false;		
			$exchange_rate = 1;
			
			if(!empty($currency_code) && $multicurrency_enabled){
				Price_Formatter::init($currency_code);
				if($currency_code!=$base_currency){
					$exchange_rate = CMulticurrency::getExchangeRate($base_currency,$currency_code);					
				}
			}			
			$customer_filter =''; $exlude_free_trial = false;
			if($subscription_type=="updatesubscriptions"){
				$exlude_free_trial = true;
				$customer_filter = "AND a.package_id <> ".q($package_id)."";
			} else if ( $subscription_type=="rewewsubscriptions"){
				$customer_filter = "AND a.package_id = ".q($package_id)."";
			}
			
			$details = array();									
			$data = CPlan::listing( Yii::app()->language , $exlude_free_trial , $customer_filter,$exchange_rate);						
			$details = AttributesTools::PlansFeatureList();				
			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(			
			  'data'=>$data,
			  'plan_details'=>$details,			  
			);							
		} catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }		
        $this->responseJson();  
	}

	public function actiongetpaymentlist()
	{
		try {

			$payment_list = AttributesTools::PaymentPlansProvider(); 
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [				
				'data'=>$payment_list
			];
		} catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }		
        $this->responseJson();  		
	}

	public function actioncreatePaymentPlan()
	{
		try {
			
			$subscriber_id = Yii::app()->input->post('subscriber_id');
			$package_id = Yii::app()->input->post('package_id');
			$subscription_type = Yii::app()->input->post('subscription_type');
			$subscriber_type = Yii::app()->input->post('subscriber_type');
			$success_url = Yii::app()->input->post('success_url');
			$failed_url = Yii::app()->input->post('failed_url');
			$subscription_type = !empty($subscription_type)?$subscription_type:'subscribe';
			$subscriber_type = !empty($subscriber_type)?$subscriber_type:'merchant';

			$jobs = Yii::app()->input->post('jobs');
			$jobs = !empty($jobs)?$jobs:'merchantSubscriptions';
			
			Cplans::clearCreatepaymentplans();			

			$model = AR_plans_create_payment::model()->find("package_id=:package_id AND subscriber_id=:subscriber_id AND subscription_type=:subscription_type",[
				':package_id'=>$package_id,
				':subscriber_id'=>$subscriber_id,
				':subscription_type'=>$subscription_type,
			]);
			if(!$model){
				$model = new AR_plans_create_payment();
			}			
			$model->subscriber_id = $subscriber_id;
			$model->package_id = $package_id;
			$model->subscription_type = $subscription_type;
			$model->subscriber_type = $subscriber_type;
			$model->success_url = $success_url;
			$model->failed_url = $failed_url;
			$model->jobs = $jobs;			

			if($model->save()){
				$this->code = 1;
				$this->msg = "Ok";
				$this->details  = [
					'payment_id'=>$model->payment_id
				];
			} else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());

		} catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }		
        $this->responseJson();  		
	}

	public function actiongetcreatepayment()
	{
		try {
			
			$plan = []; $payment_list = [];
			$payment_id = Yii::app()->input->get("payment_id");
						
			$currency_code = Yii::app()->request->getQuery('currency_code', '');
			$base_currency = Price_Formatter::$number_format['currency_code'];		

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
			$multicurrency_enabled = $multicurrency_enabled==1?true:false;		
			$exchange_rate = 1;

			if(!empty($currency_code) && $multicurrency_enabled){
				Price_Formatter::init($currency_code);
				if($currency_code!=$base_currency){
					$exchange_rate = CMulticurrency::getExchangeRate($base_currency,$currency_code);					
				}
			}			
			
			$model = Cplans::getPaymentCreated($payment_id);			
			$plan = CPlan::getPlanByID($model->package_id,Yii::app()->language, $exchange_rate);		

			try {
				$payment_list = AttributesTools::PaymentPlansProvider(); 
			} catch (Exception $e) {}		

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'plan_details'=>$plan,
				'payment_list'=>$payment_list
			];

		} catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }		
        $this->responseJson();  	
	}

}
// end class