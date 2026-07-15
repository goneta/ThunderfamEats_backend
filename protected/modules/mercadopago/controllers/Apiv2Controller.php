<?php
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

    public function actionprocesspayment()
    {
        try {
            						
			$payment_details = isset($this->data['payment_details'])?$this->data['payment_details']:'';			
			$key = isset($payment_details['key'])?trim($payment_details['key']):'';			
			$payment_code = isset($payment_details['payment_code'])?trim($payment_details['payment_code']):'';

            $total = isset($payment_details['total'])?floatval($payment_details['total']):0;
			$amount = $total;
            $payment_description = isset($payment_details['payment_description'])?$payment_details['payment_description']:'';
            $currency_code = isset($payment_details['currency_code'])?$payment_details['currency_code']:'';
			$payment_reference = isset($payment_details['payment_reference'])?$payment_details['payment_reference']:'';
			$payment_type = isset($payment_details['payment_type'])?$payment_details['payment_type']:'';						

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

			$token =  isset($this->data['token'])?$this->data['token']:'';
			$issuer_id =  isset($this->data['issuer_id'])?$this->data['issuer_id']:'';
			$payment_method_id =  isset($this->data['payment_method_id'])?$this->data['payment_method_id']:'';
			$transaction_amount =  isset($this->data['transaction_amount'])?$this->data['transaction_amount']:'';
			$installments =  isset($this->data['installments'])?$this->data['installments']:'';
			$payer =  isset($this->data['payer'])?$this->data['payer']:'';
			$email =  isset($payer['email'])?$payer['email']:'';
			$identification =  isset($payer['identification'])?$payer['identification']:'';
			$identificationType =  isset($identification['type'])?$identification['type']:'';
			$identificationNumber =  isset($identification['number'])?$identification['number']:'';
            			
			require_once 'mercadopago/vendor/autoload.php';
			MercadoPago\SDK::setAccessToken($key);
			$payment = new MercadoPago\Payment();

			$payment->transaction_amount = $transaction_amount;
			$payment->token = $token;
			$payment->description = $payment_description;
			$payment->installments = $installments;
			$payment->payment_method_id = $payment_method_id;
			$payment->payer = array(
			   "email" => $email
			);
			$payment->save();			

			if($payment->status_detail=="accredited" || $payment->status_detail=="pending_contingency"
				|| $payment->status_detail=="pending_review_manual"  ){

				$this->code = 1;
				$this->msg = "Ok";		
				$this->details =[
					'payment_id'=>$payment->id
				];
			} else {				
				if(!empty($payment->status_detail)){
					$this->msg = CMercadopagoError::get($payment->status_detail);
				 } else {
					 if(isset($payment->error->causes)){
						 $error_line = '';
						 foreach ($payment->error->causes as $items) {
							 $error_line = $items->description."<br/>";
						 }
						 $this->msg = $error_line;
					 } else $this->msg = t("An error has occured." . json_encode($payment->error));
				 }
			}
        } catch (Exception $e) {
			$this->msg = t($e->getMessage());		
		}		
		$this->responseJson();
    }

} 
// end class