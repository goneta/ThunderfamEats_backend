<?php
class PartnerCommon extends CController
{	
	public $code=2,$msg,$details,$data;
	    
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
                     'menuSearch','subscribeNews','getBanner',
					 'getDeliveryDetails','TransactionInfo','getFooter'
                 ),
				 'expression' => array('AppUserIdentity','verifyMerchant')
			 ), 
             array('deny',				
                  'actions'=>array(
                    'saveClientAddress','clientAddresses','deleteAddress','checkoutAddress','getPhone',
                    'RequestEmailCode','verifyCode','ChangePhone','applyPromo','removePromo','applyPromoCode',
                    'checkoutAddTips','PaymentList','SavedPaymentProvider','SavedPaymentList',
                    'SetDefaultPayment','deleteSavedPaymentMethod','savedCards','','PlaceOrder',
                    'getOrder','orderHistory','orderDetails','uploadReview','addReview','getProfile','saveProfile',
                    'updatePassword','getAddresses','MyPayments','deletePayment','PaymentMethod','addTofav',
                    'getsaveitems','getCartCheckout','getRealtime','SavePlaceByID','orderBuyAgain',
					'StripePaymentIntent','paypalverifypayment','razorpaycreatecustomer','razorpaycreateorder','razorpayverifypayment',
					'mercadopagocustomer','mercadopagoaddcard','mercadopagogetcard','mercadopagocapturepayment','getMenuItem2'
                 ), 
				 'expression' => array('AppUserIdentity','verifyCustomer')
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
			'captcha_customer_signup','image_resizing','merchant_specific_country'
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