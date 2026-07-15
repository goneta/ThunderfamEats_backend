<?php
class DriverCommon extends CController
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
                     'login','register','forgotpass','getaccountstatus','requestcode','getlocationcountries','addlicense','addlicensephoto','authenticate',
					 'registerdevice','getSettings','updateAvatarx'
                 ),
				 'expression' => array('AppDriverIdentity','verifyToken')
			 ), 
             array('deny',				
                  'actions'=>array(
                    'profile','updateprofile','getlicense','updatelicense','changepassword','getvehicle','getorderlist',
					'getshift','acceptorder','declineorder','onthewayvendor','arrivedatvendor','waitingfororder','orderpickup',
					'onthewaycustomer','arrivedatcustomer','orderdelivered','deliveryfailed','orderpreview',
					'endshift','startshift','getRealtime','getNotification','orderhistory','deliverysummary','shifthistory','deleteNotifications','requestcode2',
					'deleteaccount','setnotifications','getreview','getreviewsummary','deliveriesoverview','attachcarphoto','uploadfile','updatelicensephoto',
					'updatedevice','logLocation','updateLocation','getAvailableShift','takeShift','currentShift','getWalletBalance','walletHistory','getEarnings',
					'getEarningDetails','Addbankaccount','RequestPayout','getCashoutTransaction','getCashoutHistory','PaymentMethod','getPaymentSaved','getPaymentDetails',
					'getCashinTransaction','SavedPaymentProvider','timeoutAcceptOrder','getBankinfo','updateBankInfo','deletePayment','setDefaultPayment','getRemainingCashBalance',
					'getVehicleInfo','updateAvatarx','saveVehicleInfo','getLicensePhoto','getCashDomination','orderdetails','getZone','getOnlineStatus','changeOnlineStatus',
					'setZone','getZoneList','getOndemanzone'
                 ), 
				 'expression' => array('AppDriverIdentity','verifyCustomer')
			 ), 
		 );
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
	
	public function initSettings()
	{	
		$settings = OptionsTools::find(array(
			'website_date_format_new','website_time_format_new','home_search_unit_type','website_timezone_new',
			'captcha_customer_signup','image_resizing','merchant_specific_country','map_provider','google_geo_api_key','mapbox_access_token','driver_enabled_request_break',
			'driver_request_break_limit','driver_enabled_delivery_otp','firebase_apikey','firebase_projectid','multicurrency_enabled','driver_add_proof_photo',
			'driver_on_demand_availability'
	    ));
	    
		Yii::app()->params['settings'] = $settings;

		/*SET TIMEZONE*/
		$timezone = Yii::app()->params['settings']['website_timezone_new'];		
		if (is_string($timezone) && strlen($timezone) > 0){
		   Yii::app()->timeZone=$timezone;		   
		}
		Price_Formatter::init();			
	}

}
// end class