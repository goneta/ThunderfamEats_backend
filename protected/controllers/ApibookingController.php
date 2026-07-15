<?php
require 'php-jwt/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ApibookingController extends CController
{
    public $code=2,$msg,$details,$data;

	public function __construct($id,$module=null){
		parent::__construct($id,$module);
		// If there is a post-request, redirect the application to the provided url of the selected language 
		if(isset($_POST['language'])) {
			$lang = $_POST['language'];
			$MultilangReturnUrl = $_POST[$lang];
			$this->redirect($MultilangReturnUrl);
		}
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

	public static function getAuthorizationHeader(){
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } else if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER["REDIRECT_HTTP_AUTHORIZATION"]);    
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));            
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }

	public static function getToken() {
        $headers = self::getAuthorizationHeader();        
        if (!empty($headers)) {
            if (preg_match('/token\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
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
		
		$action_name = $action->_id;		
		$deny = ['setbooking','reservetable','bookingdetails','cancelreservation','getbookingdetails',
		 'bookinglist','bookingsummary','bookingsearch'
	   ];

		if(in_array($action_name,$deny)){
			if(!self::getCustomerIdentity()){		
				$this->msg = t("Please log in to your account to proceed");
				$this->responseJson();				
				return false;
			}
		}		
		
		return true;
	}

	public static function getCustomerIdentity()
	{
		try {
			$jwt_token = self::getToken();		
			if(empty($jwt_token)){
				return false;
			}    			
			$jwt_key = new Key(CRON_KEY, 'HS256');
			$decoded = (array) JWT::decode($jwt_token, $jwt_key);  
			$token = isset($decoded['token'])?$decoded['token']:'';
			
			$dependency = CCacheData::dependency();
			$user = AR_client::model()->cache(Yii::app()->params->cache, $dependency)->find("token=:token",array(
				':token'=>$token
			));           			
			if($user){                    
				Yii::app()->user->id = $user->client_id;
				Yii::app()->user->setState('client_uuid', $user->client_uuid);
				Yii::app()->user->setState('first_name', $user->first_name);
				Yii::app()->user->setState('last_name', $user->last_name);
				Yii::app()->user->setState('email_address', $user->email_address);
				Yii::app()->user->setState('contact_number', $user->contact_phone);                     
				Yii::app()->user->setState('phone_prefix', $user->phone_prefix); 
				Yii::app()->user->setState('avatar', CMedia::getImage($user->avatar,$user->path,Yii::app()->params->size_image_thumbnail,
					CommonUtility::getPlaceholderPhoto('customer'))
				);                                      
				return true;
			}			
		} catch (Exception $e) {            
			return false;
		}                          
	}

    public function responseJson()
    {
		header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST");
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])){
		   header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
	    }
    	header('Content-type: application/json');
		$resp=array('code'=>$this->code,'msg'=>$this->msg,'details'=>$this->details);
		echo CJSON::encode($resp);
		Yii::app()->end();
    }
    
    public function actions()
    {		
        return array(
            'getbookingattributes'=>'application.controllers.booking.Getbookingattributes',            
			'setbooking'=>'application.controllers.booking.SetBooking',            
			'gettimeslot'=>'application.controllers.booking.GetTimeslot', 
			'getlocationcountries'=>'application.controllers.booking.getLocationCountries', 
			'reservetable'=>'application.controllers.booking.ReserveTable', 
			'bookingdetails'=>'application.controllers.booking.BookingDetails', 			
			'getcancelreason'=>'application.controllers.booking.getCancelreason', 
			'cancelreservation'=>'application.controllers.booking.CancelReservation',
			'getbookingdetails'=>'application.controllers.booking.Getbookingdetails',
			'bookinglist'=>'application.controllers.booking.Bookinglist',
			'bookingsummary'=>'application.controllers.booking.Bookingsummary',
			'bookingsearch'=>'application.controllers.booking.Bookingsearch',
        );
    }
}
// end class