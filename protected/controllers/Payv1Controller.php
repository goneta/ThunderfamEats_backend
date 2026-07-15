<?php
class Payv1Controller extends InterfaceCommon
{
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
    
    public function actions()
    {		
		$settings = OptionsTools::find(array(
			'website_date_format_new','website_time_format_new','home_search_unit_type','website_timezone_new',
			'captcha_customer_signup','image_resizing','merchant_specific_country','map_provider','google_geo_api_key','mapbox_access_token',
			'signup_enabled_verification','mobilephone_settings_default_country','mobilephone_settings_country','website_title','fb_flag','google_login_enabled',
			'enabled_language_customer_app','multicurrency_enabled','multicurrency_enabled_hide_payment','multicurrency_enabled_checkout_currency',
			'points_enabled','points_earning_rule','points_earning_points','points_minimum_purchase','points_maximum_purchase','captcha_site_key','captcha_secret',
			'captcha_lang','admin_addons_use_checkbox','admin_category_use_slide'
		));		
		
		Yii::app()->params['settings'] = $settings;

		/*SET TIMEZONE*/
		$timezone = Yii::app()->params['settings']['website_timezone_new'];		
		if (is_string($timezone) && strlen($timezone) > 0){
		Yii::app()->timeZone=$timezone;		   
		}
		Price_Formatter::init();		
		
        return array(
            'stripecreatecustomer'=>'application.controllers.paymentapi.Stripecreatecustomer',
            'stripesavepayment'=>'application.controllers.paymentapi.StripeSavePayment',
            'stripecreateintent'=>'application.controllers.paymentapi.StripeCreateIntent',            
			'stripepaymentintent'=>'application.controllers.paymentapi.StripePaymentIntent', 
			'paypalverifypayment'=>'application.controllers.paymentapi.PaypalVerifyPayment', 
			'razorpaycreatecustomer'=>'application.controllers.paymentapi.RazorpayCreateCustomer', 
			'razorpaycreateorder'=>'application.controllers.paymentapi.RazorpayCreateOrder', 
			'razorpayverifypayment'=>'application.controllers.paymentapi.RazorpayVerifyPayment', 
			'mercadopagocustomer'=>'application.controllers.paymentapi.MercadopagoCustomer', 
			'mercadopagoaddcard'=>'application.controllers.paymentapi.MercadopagoAddcard', 
			'mercadopagogetcard'=>'application.controllers.paymentapi.MercadopagoGetcard', 
			'mercadopagocapturepayment'=>'application.controllers.paymentapi.MercadopagoCapturePayment', 
			'stripeprocesspayment'=>'application.controllers.paymentapi.StripeProcesspayment',
			'razorpaycreateneworder'=>'application.controllers.paymentapi.RazorpayCreateneworder',
			'razorpayprocesspayment'=>'application.controllers.paymentapi.RazorpayProcesspayment',			
			'paypalprocesspayment'=>'application.controllers.paymentapi.PaypalProcesspayment',	
			'mercadopagogetcardid'=>'application.controllers.paymentapi.MercadopagoGetcardid',	
			'mercadopagoprocesspayment'=>'application.controllers.paymentapi.MercadopagoProcesspayment',
			'vivawalletcheckout'=>'application.controllers.paymentapi.Vivawalletcheckout',							
			'touchpaycheckout'=>'application.controllers.paymentapi.Touchpaycheckout',							
        );
    }
}
// end class