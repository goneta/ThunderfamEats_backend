<?php
class PartnerCommon extends CController
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
                     'itemfeatured','Category','MenuCategory','geStoreMenu','SimilarItems','getMenuItem',
                     'addCartItems','getCart','clearCart','removeCartItem','updateCartItems','getlocationAutocomplete',
                     'reverseGeocoding','getLocationDetails','getDeliveryTimes','saveTransInfo','TransInfo','addressAtttibues',
                     'loadPromo','loadTips','getMapsConfig','getReview','getLocationCountries','getSignupSettings','RegistrationPhone',
                     'verifyCodeSignup','requestCode','completeSignup','registerUser','userLogin','authenticate','SocialRegister',
                     'getAccountStatus','getCustomerInfo','completeSocialSignup','autoLogin','getAddressAttributes','storeAvailable',
                     'menuSearch','subscribeNews','getBanner','getPage','getInfo',
					 'getDeliveryDetails','TransactionInfo','getFooter','getTipSettings','checkStoreOpen','requestResetPassword','resendResetEmail',
					 'getSettings','resetPassword','saveTransactionType','saveTransactionInfo2','getLocationByIp','savedCartDetails','getRealtime2','getReview2',
					 'CategoryItems','getFeaturedItems','getPage2','registerDevice','userLoginPhone','getpaydelivery','getcurrencylist','formatTransaction',
					 'SavePlaceByID','getAllergenInfo','registerGuest','getCurrencySettings','getPages',
                 ),
				 'expression' => array('AppUserIdentity','verifyMerchant')
			 ), 
             array('deny',				
                  'actions'=>array(
                    'saveClientAddress','clientAddresses','deleteAddress','checkoutAddress','getPhone','placeDetails',
                    'RequestEmailCode','verifyCode','ChangePhone','applyPromo','removePromo','applyPromoCode',
                    'checkoutAddTips','PaymentList','SavedPaymentProvider','SavedPaymentList',
                    'SetDefaultPayment','deleteSavedPaymentMethod','savedCards','','PlaceOrder',
                    'getOrder','orderHistory','orderHistory2','orderDetails','uploadReview','addReview','getProfile','saveProfile',
                    'updatePassword','getAddresses','MyPayments','deletePayment','PaymentMethod','addTofav',
                    'getsaveitems','getsaveitems2','getCartCheckout','getRealtime','orderBuyAgain',
					'StripePaymentIntent','paypalverifypayment','razorpaycreatecustomer','razorpaycreateorder','razorpayverifypayment',
					'mercadopagocustomer','mercadopagoaddcard','mercadopagogetcard','mercadopagocapturepayment','getMenuItem2',
					'CloverCheckout','VivaCheckout','saveClientAddress2','removeTips','getNotification','deleteNotification','deleteAllNotification',
					'deleteNotifications','deleteAccount','getReviewOrder','updateDevice','Updateaccountnotification','savedPaydelivery','getPointsTransaction',
					'getPointsTransactionMerchant','getAvailablePoints','getWalletBalance','getWalletTransaction','getCustomerDefaultPayment','prepareaddfunds',
					'getPaymentCredentials','getCartWallet','applyDigitalWallet','uploadBankDeposit','getBankDeposit','getcartpoints','applyPoints',
					'removePoints','getPointsthresholds','defaultPaymentMethod','paymentListnew','MyPaymentsNew'
                 ), 
				 'expression' => array('AppUserIdentity','verifyCustomer')
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
			'website_date_format_new','website_time_format_new','home_search_unit_type','website_timezone_new',
			'captcha_customer_signup','image_resizing','merchant_specific_country','map_provider','site_user_avatar','site_merchant_avatar','site_food_avatar',
			'default_location_lat','default_location_lng','digitalwallet_topup_minimum','digitalwallet_topup_maximum','multicurrency_enabled',
			'multicurrency_enabled_checkout_currency','points_enabled','points_use_thresholds','points_earning_rule','points_earning_points','points_minimum_purchase',
			'points_maximum_purchase','digitalwallet_transaction_limit','enabled_include_utensils','digitalwallet_enabled','chat_enabled',
			'signup_enabled_terms','signup_terms','digitalwallet_enabled_topup'
	    ));

		$settings_merchant = OptionsTools::find(array(			
			'merchant_default_currency'
	    ),Yii::app()->merchant->id);

		$merchant_default_currency = isset($settings_merchant['merchant_default_currency'])?$settings_merchant['merchant_default_currency']:'';		
		
	    		
		Yii::app()->params['settings'] = $settings;

		/*SET TIMEZONE*/
		$timezone = Yii::app()->params['settings']['website_timezone_new'];		
		if (is_string($timezone) && strlen($timezone) > 0){
		   Yii::app()->timeZone=$timezone;		   
		}

		$currency_code = Yii::app()->input->get('currency_code');		

		if(!empty($currency_code)){
			Price_Formatter::init($currency_code);			
		} else if (!empty($merchant_default_currency) ){
			Price_Formatter::init($merchant_default_currency);			
		} else Price_Formatter::init();				
		
		// if(!empty($merchant_default_currency)){			
		// 	Price_Formatter::init($merchant_default_currency);			
		// } else  Price_Formatter::init();				
	}

}
// end class
