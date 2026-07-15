<?php
require 'php-jwt/vendor/autoload.php';
require 'twig/vendor/autoload.php';
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

    public function actionprocesspayment()
    {
        try {

            $request_from = null;
            $return_url = null;
            $message = null;

            $request_from = Yii::app()->request->getQuery('request_from', '');
            $return_url = Yii::app()->request->getQuery('return_url', '');
            $return_url =  rtrim($return_url, "/"); 
            $return_url = (empty($return_url) || $return_url === 'null') ? '' : $return_url; 
            $data = Yii::app()->request->getQuery('data', '');

            $jwt_key = new Key(CRON_KEY, 'HS256');
			$decoded = (array) JWT::decode($data, $jwt_key);    
            
            $payment_code = isset($decoded['payment_code'])?$decoded['payment_code']:'';
			$payment_name = isset($decoded['payment_name'])?$decoded['payment_name']:'';
            $reference_id = isset($decoded['reference_id'])?$decoded['reference_id']:'';	
            $merchant_id = isset($decoded['merchant_id'])?$decoded['merchant_id']:'';
			$merchant_type = isset($decoded['merchant_type'])?$decoded['merchant_type']:'';
            $customer_details = isset($decoded['customer_details'])?(array)$decoded['customer_details']:'';            
            $client_id = $customer_details['client_id'] ?? null;
            $first_name = $customer_details['first_name'] ?? null;
            $last_name = $customer_details['last_name'] ?? null;
            $email_address = $customer_details['email_address'] ?? null;
            $amount = isset($decoded['amount'])?$decoded['amount']:'';			

            if($request_from=="app"){
                if(!empty($return_url)){
                    $redirect_url = $return_url."/account/wallet";
                } else $redirect_url = APP_CUSTOM_URL_SCHEME."://payment-callback?status=cancel";
            } else {
                $redirect_url = Yii::app()->createAbsoluteUrl("/account/wallet");
            } 

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
                          
			 $tpl_data = CPayments::getBankDepositInstructions(2,$merchant_id);							 
             
             $tpl = isset($tpl_data['content'])?$tpl_data['content']:'';
			 $subject = isset($tpl_data['subject'])?$tpl_data['subject']:'';
             
             $path = Yii::getPathOfAlias('backend_webroot')."/twig"; 
             $loader = new \Twig\Loader\FilesystemLoader($path);
                $twig = new \Twig\Environment($loader, [
                    'cache' => $path."/compilation_cache",
                    'debug'=>true
             ]);

             $data = [
                'first_name'=>$first_name,
                'amount'=>Price_Formatter::formatNumberNoSymbol($amount),
                'upload_deposit_url'=>Yii::app()->createAbsoluteUrl("/wallet/upload_deposit",[
                       'reference_id'=>$reference_id
                ])
             ];
             
             $twig_template = $twig->createTemplate($tpl);
			 $template = $twig_template->render($data);								
			 $customer_name = "$first_name $last_name";
                          
             CommonUtility::sendEmail($email_address,$customer_name,$subject,$template);

             ScriptUtility::registerCSS([
                'https://cdn.jsdelivr.net/npm/quasar@2.14.5/dist/quasar.prod.css',
                'https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900|Material+Icons'
             ]);             

            ScriptUtility::registerJS([
                Yii::app()->baseUrl."/assets/vendor/axios.min.js", 
                'https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.prod.js',
                'https://cdn.jsdelivr.net/npm/quasar@2.14.5/dist/quasar.umd.prod.js',                
                Yii::app()->baseUrl."/protected/modules/$payment_code/assets/js/payment-bank.js?version=".time()
            ],CClientScript::POS_END);
                    
            $this->pageTitle = t($payment_name);

             $this->render("upload-deposit",[
                'redirect_url'=>$redirect_url,  
                'page_title'=>t($payment_name),
                'email_address'=>$email_address
             ]);
        } catch (Exception $e) {
			$message = $e->getMessage();            
            $this->redirect(CommonUtility::failedRedirectWallet($request_from,$return_url,$message));
		}                
    }

}
// end class