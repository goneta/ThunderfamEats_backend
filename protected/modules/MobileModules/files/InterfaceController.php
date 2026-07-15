<?php
require 'intervention/vendor/autoload.php';
require 'php-jwt/vendor/autoload.php';
use Intervention\Image\ImageManager;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
define("SOCIAL_STRATEGY", 'mobile');

class InterfaceController extends InterfaceCommon
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
				
		$this->initSettings();
		return true;
	}

    public function actionIndex()
    {
		echo "API Index";
    }

	public function actiongetBanner()
	{
		try {						
			$data = CMerchants::getBanner(0,'admin');			
			$food_list = CMerchants::BannerItems();
			$merchant_list = CMerchants::BannerMerchant();
			$cuisine_list = CMerchants::BannerCuisine();			
			$this->code = 1; $this->msg = "OK";
			$this->details = [ 
			   'data'=>$data,
			   'food_list'=>$food_list,
			   'merchant_list'=>$merchant_list,
			   'cuisine_list'=>$cuisine_list
			];
		} catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
	}

	public function actiongetLocationCountries()
	{
		try {
						
			$phone_default_country = isset(Yii::app()->params['settings']['mobilephone_settings_default_country'])?Yii::app()->params['settings']['mobilephone_settings_default_country']:'us';
	        $phone_country_list = isset(Yii::app()->params['settings']['mobilephone_settings_country'])?Yii::app()->params['settings']['mobilephone_settings_country']:'';
	        $phone_country_list = !empty($phone_country_list)?json_decode($phone_country_list,true):array();        

			$filter = array(
				'only_countries'=>(array)$phone_country_list
			);			
			$data = ClocationCountry::listing($filter);
			$default_data = ClocationCountry::get($phone_default_country);

			$this->code = 1;
			$this->msg = "OK";
			$this->details = [
				'data'=>$data,
				'default_data'=>$default_data
			];

		} catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actionregisterUser()
	{	
		try {
									
			$options = OptionsTools::find(array('signup_enabled_verification','captcha_secret','signup_enabled_capcha','signup_type'));
			$enabled_verification = isset($options['signup_enabled_verification'])?$options['signup_enabled_verification']:false;
			$merchant_captcha_secret = isset($options['captcha_secret'])?$options['captcha_secret']:'';
			$signup_type = isset($options['signup_type'])?$options['signup_type']:'';
			$verification = $enabled_verification==1?true:false;			
			
			$signup_enabled_capcha = isset($options['signup_enabled_capcha'])?$options['signup_enabled_capcha']:false;
			$capcha = $signup_enabled_capcha==1?true:false;
		
			$digit_code = CommonUtility::generateNumber(3,true);			
						
			$recaptcha_response = isset($this->data['recaptcha_response'])?$this->data['recaptcha_response']:'';			
			
			$prefix = isset($this->data['mobile_prefix'])?$this->data['mobile_prefix']:'';
			$prefix = str_replace("+","",$prefix);
			$mobile_number = isset($this->data['mobile_number'])?$this->data['mobile_number']:'';
			$redirect = isset($this->data['redirect'])?$this->data['redirect']:'';
			$next_url = isset($this->data['next_url'])?$this->data['next_url']:'';		
			$local_id = isset($this->data['local_id'])?$this->data['local_id']:'';		
			
			$model=new AR_clientsignup;
			$model->scenario = 'register';
			$model->capcha = false;
			$model->recaptcha_response = $recaptcha_response;
			$model->captcha_secret = $merchant_captcha_secret;
			
			$model->first_name = isset($this->data['first_name'])?$this->data['first_name']:'';
			$model->last_name = isset($this->data['last_name'])?$this->data['last_name']:'';
			$model->email_address = isset($this->data['email_address'])?$this->data['email_address']:'';
			$model->contact_phone = $prefix.$mobile_number;
			$model->password = isset($this->data['password'])?$this->data['password']:'';		
			$password = $model->password;
			$model->cpassword = isset($this->data['cpassword'])?$this->data['cpassword']:'';
			$model->phone_prefix = $prefix;			
			$model->mobile_verification_code = "$digit_code";
			$model->merchant_id = 0;
			$model->social_strategy = SOCIAL_STRATEGY;			
			
			if($verification==1 || $verification==true){
				$model->status='pending';
			}

			// quick fixed
			if($signup_type=="mobile_phone"){
				$model->scenario = 'registration_phone';
			}
									
			if ($model->save()){
				$this->code = 1 ;
				
				if($verification==1 || $verification==true){			
					$this->msg = t("We sent a code to {{email_address}}.",array(
						'{{email_address}}'=> CommonUtility::maskEmail($model->email_address)
					));			  
					$this->details = array(
					  'uuid'=>$model->client_uuid,
					  'verify'=>true
					);			
				} else {
					$this->msg = t("Registration successful");				
					$this->details = array(
					  'verify'=>false
					);																
					
					$this->autoLogin($model->email_address,$password);	
					$this->saveDeliveryAddress($local_id,$model->client_id);

				}
			} else {				
				$this->msg = CommonUtility::parseError( $model->getErrors() );
			}		
			
		} catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	private function autoLogin($username='',$password='')
	{		
	  $login=new AR_customer_login;	
	  $login->username = $username;
	  $login->password = $password;
	  $login->merchant_id = 0;
	  $login->rememberMe = 1;
	  if($login->validate() && $login->login() ){ 		 
		 $this->userData();		
	  } 
   } 	

   private function customerAutoLogin($username='',$password='')
	{		
	  $login=new AR_customer_autologin;	
	  $login->username = $username;
	  $login->password = $password;
	  $login->merchant_id = 0;
	  $login->rememberMe = 1;
	  if($login->validate() && $login->login() ){
		 $this->userData();	
	  } 
   } 	

   public function actionuserLogin()
   {						   
		$local_id = isset($this->data['local_id'])?$this->data['local_id']:'';
		$_POST['AR_customer_login'] = array(
			'username'=>isset($this->data['username'])?$this->data['username']:'',
			'password'=>isset($this->data['password'])?$this->data['password']:'',		  
		);		
		
		$options = OptionsTools::find(array('signup_enabled_capcha','captcha_secret'));		
		$signup_enabled_capcha = isset($options['signup_enabled_capcha'])?$options['signup_enabled_capcha']:false;
		$merchant_captcha_secret = isset($options['captcha_secret'])?$options['captcha_secret']:'';
		$capcha = $signup_enabled_capcha==1?true:false;
		$recaptcha_response = isset($this->data['recaptcha_response'])?$this->data['recaptcha_response']:'';			
		
		$model=new AR_customer_login;
		$model->attributes=$_POST['AR_customer_login'];		
		$model->capcha = false;		
		$model->recaptcha_response = $recaptcha_response;
		$model->captcha_secret = $merchant_captcha_secret;
		$model->merchant_id = 0;
			
		if($model->validate() && $model->login() ){						
			$this->saveDeliveryAddress($local_id, Yii::app()->user->id );
			$this->userData();
			$this->code = 1 ;
			$this->msg = t("Login successful");			
		} else {			
			$this->msg = CommonUtility::parseError( $model->getErrors() );
		}		
		$this->responseJson();
	}   

	private function userData()
	{
		$user_data = array(
			 'client_uuid'=>Yii::app()->user->client_uuid,
			'first_name'=>Yii::app()->user->first_name,
			'last_name'=>Yii::app()->user->last_name,
			'email_address'=>Yii::app()->user->email_address,
			'contact_number'=>Yii::app()->user->contact_number,
			'phone_prefix'=>Yii::app()->user->phone_prefix,
			'contact_number_noprefix'=> str_replace(Yii::app()->user->phone_prefix,"",Yii::app()->user->contact_number) ,
			'avatar'=>Yii::app()->user->avatar,
		);					
		$payload = [
			'iss'=>Yii::app()->request->getServerName(),
			'sub'=>0,				
			'iat'=>time(),	
			'token'=>Yii::app()->user->logintoken					
		];					
				
		$settings = AR_client_meta::getMeta(['app_push_notifications','promotional_push_notifications']);		
		$user_settings = [
			'app_push_notifications'=> isset($settings['app_push_notifications'])?$settings['app_push_notifications']:false ,
			'promotional_push_notifications'=>isset($settings['promotional_push_notifications'])?$settings['promotional_push_notifications']:false ,
		];
		$user_data = JWT::encode($user_data, CRON_KEY, 'HS256');
		$jwt_token = JWT::encode($payload, CRON_KEY, 'HS256'); 		

		$this->details = array(			 
			'user_token'=>$jwt_token,
			'user_data'=>$user_data,
			'user_settings'=>$user_settings
		);						
	}

	public function actionSocialRegister()
	{
		try {
			
			$digit_code = CommonUtility::generateNumber(3,true);						
			$email_address = isset($this->data['email_address'])?$this->data['email_address']:'';
			$id = isset($this->data['id'])?$this->data['id']:'';						
			$social_strategy = isset($this->data['social_strategy'])?$this->data['social_strategy']:'';	
			$social_token = isset($this->data['social_token'])?$this->data['social_token']:'';	
			$local_id = isset($this->data['place_id'])?$this->data['place_id']:'';

			$verification = isset(Yii::app()->params['settings']['signup_enabled_verification'])?Yii::app()->params['settings']['signup_enabled_verification']:0;
			
			$model = AR_clientsignup::model()->find('email_address=:email_address', 
		    array(':email_address'=>$email_address)); 
			if(!$model){
				$model = new AR_clientsignup;		
		    	$model->scenario = 'registration_social';		    	
		    	$model->social_token = $social_token;
		    	$model->email_address = $email_address;
		    	$model->password = $id;		    	
		    	$model->social_id = $id;
				$model->google_client_id = $id;
		    	$model->first_name = isset($this->data['first_name'])?$this->data['first_name']:'';
		    	$model->last_name = isset($this->data['last_name'])?$this->data['last_name']:'';
		    	$model->mobile_verification_code = $digit_code;
		    	$model->status = $verification==1?'pending':'active';
		    	$model->social_strategy = $social_strategy;		    	
		    	$model->account_verified  = $verification==1?0:1;
				$model->merchant_id = 0;
		    	
		    	if ($model->save()){			    					    	
		    		$this->code = 1;
					$this->msg = $verification==1?t("Account verifications"):t("Registration successful");					
					$this->details = [
						'verification'=>$verification,
						'uuid'=>$model->client_uuid,	
						'complete_registration'=>true
					];		
					if($model->status=="active"){																	
						$this->customerAutoLogin($model->email_address,md5($id));						
						$this->saveDeliveryAddress($local_id,$model->client_id);
					}
		    	} else $this->msg = CommonUtility::parseError( $model->getErrors() );
			} else {				
				$model->scenario = 'social_login';		
		    	$model->social_strategy = $social_strategy;	
		    	$model->social_token = $social_token;    		    	
		    	if($model->status=='pending' && $model->social_id==$id){					
		    		$model->mobile_verification_code = $digit_code;
					$model->google_client_id = $id;
		    		if ($model->save()){
		    			$this->code = 1;
						$this->msg = $verification==1?t("Account verifications"):t("Registration successful");
						$this->details = [
							'verification'=>$verification,
							'uuid'=>$model->client_uuid,							
						];
		    		} else $this->msg = CommonUtility::parseError( $model->getErrors() );
		    	} elseif ( $model->status=="active" ){		 						
					$this->code = 1;
					$this->msg = t("Login successful");
					$this->customerAutoLogin($model->email_address,$model->password);
		    	} else $this->msg= t("Your account is {{status}}",array('{{status}}'=> t($model->status) ) );
			}		
		} catch (Exception $e) {							
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionrequestCode()
	{
		try {
			
		   $client_uuid = Yii::app()->input->post('client_uuid');
		   
		   $model = AR_clientsignup::model()->find('client_uuid=:client_uuid', 
		   array(':client_uuid'=>$client_uuid)); 
		   if($model){
		   	  $digit_code = CommonUtility::generateNumber(3,true);			  
		   	  $model->mobile_verification_code = $digit_code;
			  $model->scenario = 'resend_otp';
		   	  if($model->save()){			   	  	 		   	 
				
				   $this->code = 1;
				   $options = OptionsTools::find(['signup_type']);
				   $signup_type = isset($options['signup_type'])?$options['signup_type']:'';				   

				   if($signup_type=="mobile_phone"){
					 $this->msg = t("We sent a code to {{contact_phone}}.",array(
						'{{contact_phone}}'=> CommonUtility::mask($model->contact_phone )
					  ));			          
				   } else {
					  $this->msg = t("We sent a code to {{email_address}}.",array(
						'{{email_address}}'=> CommonUtility::maskEmail($model->email_address)
					  ));			          
				   }						   	  			           
		   	  } else $this->msg = CommonUtility::parseError($model->getErrors());		   	  
		   } else $this->msg[] = t("Records not found");
		   
		} catch (Exception $e) {							
		    $this->msg[] = t($e->getMessage());
		}
		$this->responseJson();	
	}

	public function actiongetAccountStatus()
	{
		try {
			
			$client_uuid = Yii::app()->input->post('client_uuid');
			$model = AR_client::model()->find('client_uuid=:client_uuid', 
			array(':client_uuid'=> $client_uuid )); 				  		  
			if($model){
				$data =[
					'status'=>$model->status,
					'account_verified'=>$model->account_verified,
					'social_strategy'=>$model->social_strategy
				];
				$options = OptionsTools::find(['signup_enabled_verification','signup_resend_counter','signup_type']  );				
				$enabled_verification  = isset($options['signup_enabled_verification'])?$options['signup_enabled_verification']:'';
				$signup_resend_counter  = isset($options['signup_resend_counter'])?$options['signup_resend_counter']:20;			  
				$signup_type = isset($options['signup_type'])?$options['signup_type']:'';
				$this->code = 1;

				if($signup_type=="mobile_phone"){
					$this->msg = t("We sent a code to {{contact_phone}}.",array(
						'{{contact_phone}}'=> CommonUtility::mask($model->contact_phone )
					  ));	
				} else {
					$this->msg = t("We sent a code to {{email_address}}.",array(
						'{{email_address}}'=> CommonUtility::maskEmail($model->email_address)
					));			           
				}				
				$this->details = [
					'data'=>$data,
					'settings'=>[
					  'enabled_verification'=>$enabled_verification,
					  'signup_resend_counter'=>$signup_resend_counter<=0?20:$signup_resend_counter
					]
				];
			} else $this->msg = t("account not found");
  
		} catch (Exception $e) {							
			$this->msg[] = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionverifyCodeSignup()
	{		
		try {
			
			$local_id = isset($this->data['local_id'])?$this->data['local_id']:'';
			$client_uuid = isset($this->data['client_uuid'])?$this->data['client_uuid']:'';
			$verification_code = isset($this->data['verification_code'])?intval($this->data['verification_code']):'';
						
			$auto_login = isset($this->data['auto_login'])?$this->data['auto_login']:'';
			
			$model = AR_clientsignup::model()->find('client_uuid=:client_uuid', 
		    array(':client_uuid'=>$client_uuid)); 
		    		    		   
		    if($model){
		    	$model->scenario = 'complete_standard_registration';
		    	if($model->mobile_verification_code==$verification_code){
		    		$model->account_verified = 1;
		    		
		    		if($auto_login==1){
		    			$model->status='active';
		    		}
		    				    		
		    		if($model->save()){
			    		$this->code = 1;
			    		$this->msg = "ok";
						$this->details = array();
			    		
			    		if($auto_login==1){
			    			$this->msg = t("Login successful");

							//AUTO LOGIN							
							$login=new AR_customer_autologin;
							$login->username = $model->email_address;
							$login->password = $model->password;
							$login->merchant_id = 0;
							$login->rememberMe = 1;
							if($login->validate() && $login->login() ){																						
								$this->userData();								
								$this->saveDeliveryAddress($local_id,$model->client_id);
							} 									
			    		}
		    		} else $this->msg = CommonUtility::parseError( $model->getErrors() );
		    		
		    	} else $this->msg = t("Invalid verification code");
		    } else $this->msg = t("Records not found");
			
		} catch (Exception $e) {							
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();	
	}
			
	private function saveDeliveryAddress($local_id='',$client_id='')
	{
		try {

			$location_details = array();
			$credentials = CMaps::config();		
			if($credentials){
				MapSdk::$map_provider = $credentials['provider'];
				MapSdk::setKeys(array(
					'google.maps'=>$credentials['key'],
					'mapbox'=>$credentials['key'],
				));
				$location_details = CMaps::locationDetailsNew($local_id);				
			}
			CCheckout::saveDeliveryAddress($local_id , $client_id ,$location_details);

		} catch (Exception $e) {
			//
		}
	}

	public function actiongetlocationAutocomplete()
	{						
		try {
					   		
		   $q = Yii::app()->input->post('q');		   
		   
		   if(!isset(Yii::app()->params['settings']['map_provider'])){
					$this->msg = t("No default map provider, check your settings.");
					$this->responseJson();
			}
			
			MapSdk::$map_provider = Yii::app()->params['settings']['map_provider'];		   
			MapSdk::setKeys(array(
			'google.maps'=>Yii::app()->params['settings']['google_geo_api_key'],
			'mapbox'=>Yii::app()->params['settings']['mapbox_access_token'],
			));
						
			if ( $country_params = AttributesTools::getSetSpecificCountry()){
					MapSdk::setMapParameters(array(
				'country'=>$country_params
				));
			}		   
						
			$resp = MapSdk::findPlace($q);		   
			$this->code =1; $this->msg = "ok";
			$this->details = array(
			 'data'=>$resp
			);		   
		   		   		   		   
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetLocationDetails()
	{
		try {
			
			CMaps::config();
			$place_id = Yii::app()->input->post('place_id');
			$resp = CMaps::locationDetailsNew($place_id,'');

			$this->code =1; $this->msg = "ok";
			$this->details = array(
			  'data'=>$resp,					  
			);
							
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionreverseGeocoding()
	{				
				
		try {

		   $lat = Yii::app()->input->post('lat');
		   $lng = Yii::app()->input->post('lng');		
		   
			
		   $credentials = CMaps::config();
		   if(!$credentials){
			  $this->msg = t("No default map provider, check your settings.");
			  $this->responseJson();
	       }
		   		   
		   MapSdk::$map_provider =  $credentials['provider'];
		   MapSdk::setKeys(array(
		     'google.maps'=>$credentials['key'],
		     'mapbox'=>$credentials['key']
		   ));
		   
		   if(MapSdk::$map_provider=="mapbox"){
			   MapSdk::setMapParameters(array(
			    //'types'=>"poi",
			    'limit'=>1
			   ));
		   }
		   
		   $resp = MapSdk::reverseGeocoding($lat,$lng);
		   
		   $this->code =1; $this->msg = "ok";
		   $this->details = array(		     		     
		     'data'=>$resp
		   );		   		   
		   
		} catch (Exception $e) {		   
		   $this->msg = t($e->getMessage());	
		   $this->details = array(
		     'next_action'=>"show_error_msg"		     
		   );	   
		}
		$this->responseJson();
	}		

	public function actiongetAddresses()
	{				
		if(!Yii::app()->user->isGuest){			
			if ( $data = CClientAddress::getAddresses(Yii::app()->user->id)){

				$this->code = 1;
				$this->msg = "OK";
				$this->details = array(
				  'data'=>$data				  
				);			
			} else $this->msg[] = t("No results");
		} else {		
			$this->msg = "not login";
		}
		$this->responseJson();
	}

	public function actionSavePlaceByID()
	{
		try {
			$place_id = Yii::app()->input->post('place_id');			
			CCheckout::saveDeliveryAddress($place_id , Yii::app()->user->id);		    		
			$this->code = 1; $this->msg = "ok";		
		} catch (Exception $e) {							
		    $this->msg = t($e->getMessage());		    
		}					
		$this->responseJson();
	}

	public function actiondeleteAddress()
	{				
		$address_uuid =  Yii::app()->input->post('address_uuid');		
		if(!Yii::app()->user->isGuest){			
			try {
				CClientAddress::delete(Yii::app()->user->id,$address_uuid);
				$this->code = 1; 
				$this->msg = "OK";
			} catch (Exception $e) {
			    $this->msg = t($e->getMessage());
			}
		} else $this->msg = t("User not login or session has expired");
		$this->responseJson();		
	}

	public function actionvalidateCoordinates()
	{		
		$unit = Yii::app()->params['settings']['home_search_unit_type'];		
		$lat = isset($this->data['lat'])?$this->data['lat']:'';
		$lng = isset($this->data['lng'])?$this->data['lng']:'';
		$new_lat = isset($this->data['new_lat'])?$this->data['new_lat']:'';
		$new_lng = isset($this->data['new_lng'])?$this->data['new_lng']:'';
		
		$distance = CMaps::getLocalDistance($unit,$lat,$lng,$new_lat,$new_lng);				
		if($distance=="NaN"){
			$this->code = 1;
			$this->msg = "OK";
		} else if ($distance<0.2) {	
			$this->code = 1;
			$this->msg = "OK";
		} else if ($distance>=0.2) {
			$this->msg[] = t("Pin location is too far from the address");
		}		
		$this->details = array(
		  'distance'=>$distance
		);		
		$this->responseJson();
	}

	public function actionsaveClientAddress()
	{
									
		try {
			
			$data = isset($this->data['data'])?$this->data['data']:'';
			$location_name = isset($this->data['location_name'])?$this->data['location_name']:'';
			$delivery_instructions = isset($this->data['delivery_instructions'])?$this->data['delivery_instructions']:'';
			$delivery_options = isset($this->data['delivery_options'])?$this->data['delivery_options']:'';
			$address_label = isset($this->data['address_label'])?$this->data['address_label']:'';		
			$address1  = isset($this->data['address1'])?$this->data['address1']:'';		
			$formatted_address  = isset($this->data['formatted_address'])?$this->data['formatted_address']:'';	
						
			$address = array(); 			
			$address = isset($data['address'])?$data['address']:'';

			$address_uuid = isset($data['address_uuid'])?$data['address_uuid']:'';
			$new_lat = isset($data['latitude'])?$data['latitude']:''; 
			$new_lng = isset($data['longitude'])?$data['longitude']:'';
			$place_id = isset($data['place_id'])?$data['place_id']:'';
						
			$address2 = isset($address['address2'])?$address['address2']:'';
			$country = isset($address['country'])?$address['country']:'';
			$country_code = isset($address['country_code'])?$address['country_code']:'';
			$postal_code = isset($address['postal_code'])?$address['postal_code']:'';			
								
			if(!empty($address_uuid)){				
				$model = AR_client_address::model()->find('address_uuid=:address_uuid AND client_id=:client_id', 
		        array(':address_uuid'=>$address_uuid,'client_id'=>Yii::app()->user->id)); 
			} else $model = new AR_client_address;			
		    
			$model->client_id = Yii::app()->user->id;
			$model->place_id = $place_id; 
			$model->latitude = $new_lat;
			$model->longitude = $new_lng;
			$model->location_name = $location_name;
			$model->delivery_options = $delivery_options;
			$model->delivery_instructions = $delivery_instructions;
			$model->address_label = $address_label;
			$model->formatted_address = $formatted_address;
			$model->address1 = $address1;
			$model->address2 = $address2;
			$model->country = $country;
			$model->country_code = $country_code;
			$model->postal_code = $postal_code;
						
			if($model->save()){
				$this->code = 1;
				$this->msg = "OK";
				$this->details = array(
                   'place_id'=>$place_id
				);
			} else {
				$this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
			}
					
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}		
		$this->responseJson();
	}

	public function actionaddressAtttibues()
	{
		try {
			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(			  
			  'delivery_option'=>CCheckout::deliveryOption(),
			  'address_label'=>CCheckout::addressLabel(),
			  'maps_config'=>CMaps::config()
			);				
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());		
		}			
		$this->responseJson();
	}

	public function actionCuisineList()
	{
		try {		

			$rows = Yii::app()->input->post('rows');
			$q = Yii::app()->input->post('q');			
		    $data = CCuisine::getList( Yii::app()->language , $q);
			$data_raw = $data;
			if($rows>0){
			   $total = count($data);			   
			}			
		    $this->code = 1;
		    $this->msg = "OK";
		    $this->details = array(
		      'data'=>$data,			  
		    );
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();
	}

	public function actiongetFeaturedMerchant()
	{		
		try {

			$todays_date = date("Y-m-d H:i");

			$featured = Yii::app()->input->post('featured');
		    $place_id = Yii::app()->input->post('place_id');

			$place_data = CMaps::locationDetails($place_id,'');			
			$filters = [
				'lat'=>isset($place_data['latitude'])?$place_data['latitude']:'',
				'lng'=>isset($place_data['longitude'])?$place_data['longitude']:'',
				'unit'=>Yii::app()->params['settings']['home_search_unit_type'],				
				'today_now'=>strtolower(date("l",strtotime($todays_date))),
		        'time_now'=>date("H:i",strtotime($todays_date)),
				'limit'=>intval(Yii::app()->params->list_limit),
				
				'having'=>"distance < a.delivery_distance_covered",
				'condition'=>"a.status=:status  AND a.is_ready = :is_ready
						AND a.merchant_id IN (
							select merchant_id from {{merchant_meta}}
							where meta_name=:meta_name and meta_value=:meta_value
						)
						",
				'params'=>array(
					':status'=>'active',
					':is_ready'=>2,
					':meta_name'=>'featured',
					':meta_value'=>$featured
				)
			];				

			$data = CMerchantListingV1::getFeed($filters);	

			$data_merchant = isset($data['data'])?$data['data']:'';			
			$rows = 2 ;
			$total = count($data_merchant);
			$data_merchant = CommonUtility::dataToRow($data_merchant,$total>$rows?$rows:$total);
			
			$estimation = CMerchantListingV1::estimation( $filters );			
			$services = CMerchantListingV1::services( $filters );	
			try {
				$reviews = CMerchantListingV1::getReviews( $data['merchant'] );				
			} catch (Exception $e) {
				$reviews = [];
			}

			try {
				$cuisine = CMerchantListingV1::getCuisine( $data['merchant'] , Yii::app()->language );			
			} catch (Exception $e) {
				$cuisine = [];
			}

			try {
				$food_items = CMerchantListingV1::getMaxMinItem( $data['merchant']);			
			} catch (Exception $e) {
			 	$food_items = [];
			}
			
			$this->code = 1;
			$this->msg = "ok";
			$this->details = [
				'data'=>$data_merchant,
				'cuisine'=>$cuisine,
				'estimation'=>$estimation,
				'reviews'=>$reviews,
				'services'=>$services,	
				'items_min_max'=>$food_items			
			];		

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());						
			dump($this->msg);
		}	
		$this->responseJson();
	}

  public function actiongetMerchantFeed2()
  {
    $this->actiongetMerchantFeed();
  }

	public function actiongetMerchantFeed()
	{		
		try {
			
			$list_type = isset($this->data['list_type'])?$this->data['list_type']:'';
			$featured_id = isset($this->data['featured_id'])?$this->data['featured_id']:'';
			$payload = isset($this->data['payload'])?$this->data['payload']:'';
			$place_id = isset($this->data['place_id'])?$this->data['place_id']:'';
			$page = isset($this->data['page'])?intval($this->data['page']):0;
			$page_raw = isset($this->data['page'])?intval($this->data['page']):0;
			$rows = isset($this->data['rows'])?$this->data['rows']:0;
			$filters = isset($this->data['filters'])?$this->data['filters']:'';
			$sort_by = isset($this->data['sort_by'])?$this->data['sort_by']:'';
			
			$cuisine = []; $reviews = []; $estimation = []; $services=[]; $food_items = [];	
			$total_found = 0;
			
			$todays_date = date("Y-m-d H:i");

			if($page>0){
				$page = $page-1;
			}
			
			$place_data = CMaps::locationDetails($place_id,'');

			if(isset($filters['transaction_type'])){				
				$filters['transaction_type'] = $filters['transaction_type']=='undefined'?"delivery":$filters['transaction_type'];
				$filters['transaction_type'] = !empty($filters['transaction_type'])?$filters['transaction_type']:'delivery';
			}			

			$filters = [
				'lat'=>isset($place_data['latitude'])?$place_data['latitude']:'',
				'lng'=>isset($place_data['longitude'])?$place_data['longitude']:'',
				'unit'=>Yii::app()->params['settings']['home_search_unit_type'],				
				'today_now'=>strtolower(date("l",strtotime($todays_date))),
				'time_now'=>date("H:i",strtotime($todays_date)),
				'date_now'=>$todays_date,
				'limit'=>intval(Yii::app()->params->list_limit),				
				'page'=>intval($page),	
				'filters'=>$filters,
				'client_id'=>!Yii::app()->user->isGuest?Yii::app()->user->id:0,
			];		
	
			$and = CMerchantListingV1::preFilter($filters);
							
			if($list_type=="featured"){
				if($featured_id=="all"){
					$filters['having'] = 'distance < a.delivery_distance_covered';
					$filters['condition'] = "a.status=:status  AND a.is_ready = :is_ready $and";																			
					$filters['params'] = array(
						':status'=>'active',
						':is_ready'=>2						
					);
				} else {
					$filters['having'] = 'distance < a.delivery_distance_covered';
					$filters['condition'] = "a.status=:status  AND a.is_ready = :is_ready
											AND a.merchant_id IN (
												select merchant_id from {{merchant_meta}}
												where meta_name=:meta_name and meta_value=:meta_value
											)
											$and
											";																			
					$filters['params'] = array(
						':status'=>'active',
						':is_ready'=>2,
						':meta_name'=>'featured',
						':meta_value'=>$featured_id
					);
			    }
			} elseif ($list_type=="promo") {
				$filters['having'] = "distance < a.delivery_distance_covered";		
				$filters['condition'] = "a.status=:status  AND a.is_ready = :is_ready 
				AND a.merchant_id IN (
					select merchant_id from {{view_offers}}
					where valid_from<=:expiration and valid_to>:expiration
					and status='publish'
				)
				$and";
				$filters['params'] = [
					':status'=>'active',
					':is_ready'=>2,
					':expiration'=>CommonUtility::dateOnly()
				];				
			} elseif ($list_type=="all") {
				$filters['having'] = "distance < a.delivery_distance_covered";		
				$filters['condition'] = "a.status=:status  AND a.is_ready = :is_ready $and";
				$filters['params'] = [
					':status'=>'active',
					':is_ready'=>2
				];				
			}
												
			$data = CMerchantListingV1::getFeed($filters,$sort_by);				
			$total_found = isset($data['count'])?intval($data['count']):0;			
			$data_merchant = isset($data['data'])?$data['data']:'';			
			
			if($rows>0){
				$total = count($data_merchant);
			    $data_merchant = CommonUtility::dataToRow($data_merchant,$total>$rows?$rows:$total);
			}

			if(in_array('estimation',$payload)){
			   $estimation = CMerchantListingV1::estimation( $filters );			
			}

			if(in_array('services',$payload)){
			   $services = CMerchantListingV1::services( $filters );	
			}

			if(in_array('reviews',$payload)){
				try {
					$reviews = CMerchantListingV1::getReviews( $data['merchant'] );				
				} catch (Exception $e) {
					$reviews = [];
				}
		    }

			if(in_array('cuisine',$payload)){
				try {
					$cuisine = CMerchantListingV1::getCuisine( $data['merchant'] , Yii::app()->language );			
				} catch (Exception $e) {
					$cuisine = [];
				}
		    }

			if(in_array('items_min_max',$payload)){
				try {
					$food_items = CMerchantListingV1::getMaxMinItem( $data['merchant']);			
				} catch (Exception $e) {
					$food_items = [];
				}
		    }

			$promos = [];
			if(in_array('promo',$payload)){
				try {
				   $promos = CPromos::getAvaialblePromo($data['merchant'],CommonUtility::dateOnly());
				} catch (Exception $e) {
					$promos = [];
				}
			}
			
			$page_count = $data['page_count'];
			if($page>0){
				if($page_raw>$page_count){
					$this->code = 3;
					$this->msg = t("end of results");
					$this->details = [
						'total_found'=>$total_found
					];
					$this->responseJson();
				}
			}	
									
			$this->code = 1; 
			$this->msg = "ok";
			$this->details = [
				'continue'=>$data['continue'],
				'data'=>$data_merchant,
				'total_found'=>$total_found,
				'cuisine'=>$cuisine,
				'reviews'=>$reviews,
				'estimation'=>$estimation,
				'services'=>$services,
				'items_min_max'=>$food_items,
				'promos'=>$promos
			];			
			
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());		
			$this->details = [
				'total_found'=>$total_found
			];		
		}	
		$this->responseJson();
	}

	public function actionsearchAttributes()
	{
		$data = array(
		  'price_range'=>AttributesTools::SortPrinceRangeWithLabel(),
		  'sort_by'=>AttributesTools::SortMerchant2(),
		  'max_delivery_fee'=>AttributesTools::MaxDeliveryFee(),
		  'sort_list'=>AttributesTools::SortList(),
		);
		$this->code = 1;
		$this->msg = "OK";
		$this->details = $data;
		$this->responseJson();
	}

	public function actiongetFeaturedList()
	{
		try {

			$data = AttributesTools::MerchantFeatured();
			$data = array('all' => t("All Restaurant")) + $data;			
			$this->code = 1;
			$this->msg = "OK";
			$this->details = [
				'data'=>$data
			];

		} catch (Exception $e) {
			$this->msg = t($e->getMessage());					
		}	
		$this->responseJson();
	}

	public function actiongetDeliveryDetails()
	{		
		try {

			$merchant_id=''; $data = array();
			$cart_uuid = Yii::app()->input->post('cart_uuid');
			$slug = Yii::app()->input->post('slug');
			$transactionType = Yii::app()->input->post('transaction_type');
			$query_type = Yii::app()->input->post('query_type');
			$query_type = !empty($query_type)?$query_type:'cart';
			$delivery_option = CCheckout::deliveryOptionList();
		
			$transaction_type = !empty($transactionType)? $transactionType : CServices::getSetService($cart_uuid);
			
			try {				
				if($query_type=="cart"){
					$merchant_id = CCart::getMerchantId($cart_uuid);	
				} else {					
					$merchant = CMerchantListingV1::getMerchantBySlug($slug);
					$merchant_id = $merchant->merchant_id;
				}											    
				$data = CCheckout::getMerchantTransactionList($merchant_id,Yii::app()->language);								
			} catch (Exception $e) {		
				$data = CServices::Listing(  Yii::app()->language );
			}			
						
			if(!array_key_exists($transaction_type,(array)$data)){
				$transaction_type = CServices::getSetService($cart_uuid);				
			}			
			
			CCart::savedAttributes($cart_uuid,'transaction_type',$transaction_type);			

			$delivery_date = date("Y-m-d");
			$time_now = date("H:i");
			$attrs_name = ['whento_deliver','delivery_date','delivery_time'];

			$delivery_date = ''; $delivery_time=''; 
			if($atts = CCart::getAttributesAll($cart_uuid,$attrs_name)){				
				$delivery_date = isset($atts['delivery_date'])? $atts['delivery_date'] :$delivery_date;
				$delivery_time = isset($atts['delivery_time'])?json_decode($atts['delivery_time'],true):'';
				$time_now = isset($atts['delivery_time'])?CCheckout::jsonTimeToSingleTime($atts['delivery_time']):$time_now;				
			}
			
			$whento_deliver = CCheckout::getWhenDeliver($cart_uuid);

			try {
				$datetime_to = date("Y-m-d g:i:s a",strtotime("$delivery_date $time_now"));
				CMerchantListingV1::checkCurrentTime( date("Y-m-d g:i:s a") , $datetime_to);
				$time_already_passed = false;
			} catch (Exception $e) {
				$whento_deliver = "now";
				CCart::savedAttributes($cart_uuid,'whento_deliver',$whento_deliver);
				CCart::savedAttributes($cart_uuid,'delivery_date',$delivery_date);
				$time_already_passed = true;				
				$delivery_date = '';
				$delivery_time = '';				
			}			

			$this->code = 1; $this->msg = "OK";
			$this->details = array(
			  'transaction_type'=>$transaction_type,
			  'data'=>$data,
			  'delivery_option'=>$delivery_option,
			  'delivery_date'=>$delivery_date,	
			  'delivery_date_pretty'=>!empty($delivery_date)?Date_Formatter::date($delivery_date):'',	
			  'delivery_time'=>$delivery_time,
			  'whento_deliver'=>$whento_deliver,
			  'time_already_passed'=>$time_already_passed
			);						
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}
		$this->responseJson();
	}

	public function actiongetDeliveryTimes()
	{
		try {
			
			$cart_uuid = trim(Yii::app()->input->post('cart_uuid'));
			$merchant_slug = trim(Yii::app()->input->post('slug'));
						
			if(!empty($merchant_slug)){
				try {
					$merchant = CMerchantListingV1::getMerchantBySlug($merchant_slug);			
					$merchant_id = $merchant->merchant_id;
				} catch (Exception $e) {
					$merchant_id = 0;		
				}
			} else {
				try {
					$merchant_id = CCart::getMerchantId($cart_uuid);
				} catch (Exception $e) {
					$merchant_id = 0;		
				}
			}							
			
			$delivery_option = CCheckout::deliveryOptionList();
			$whento_deliver = CCheckout::getWhenDeliver($cart_uuid);
						
			$model = AR_opening_hours::model()->find("merchant_id=:merchant_id",array(
			  ':merchant_id'=>$merchant_id
			));
			if(!$model){
				$this->msg[] = t("Merchant has not set time opening yet");
				$this->responseJson();
			}			

		    $options = OptionsTools::find(array('website_time_picker_interval'));			
            $interval = isset($options['website_time_picker_interval'])?$options['website_time_picker_interval']." mins":'20 mins';		   

			// CHECK IF MERCHANT HAS DIFFERENT TIMEZONE
			$options_merchant = OptionsTools::find(['merchant_time_picker_interval','merchant_timezone'],$merchant_id);			
			$interval_merchant = isset($options_merchant['merchant_time_picker_interval'])? ( !empty($options_merchant['merchant_time_picker_interval']) ? $options_merchant['merchant_time_picker_interval']." mins" :''):'';
			$interval = !empty($interval_merchant)?$interval_merchant:$interval;
			$merchant_timezone = isset($options_merchant['merchant_timezone'])?$options_merchant['merchant_timezone']:'';
			if(!empty($merchant_timezone)){
				Yii::app()->timezone = $merchant_timezone;
			}
									
			$opening_hours = CMerchantListingV1::openHours($merchant_id,$interval);		
			$delivery_date = ''; $delivery_time='';

			if($atts = CCart::getAttributesAll($cart_uuid,array('delivery_date','delivery_time'))){				
				$delivery_date = isset($atts['delivery_date'])?$atts['delivery_date']:'';
				$delivery_time = isset($atts['delivery_time'])?$atts['delivery_time']:'';
			}
						
			$this->code = 1; $this->msg = "ok";			
		    $this->details = array(		     
		       'delivery_option'=>$delivery_option,
		       'whento_deliver'=>$whento_deliver,
		       'delivery_date'=>$delivery_date,
		       'delivery_time'=>$delivery_time,
		       'opening_hours'=>$opening_hours,		       
		    );
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		    		    
		}
		$this->responseJson();
	}

	public function actionsaveTransactionInfo()
	{
		try {
						
			$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
			if(empty($cart_uuid)){
				$cart_uuid = CommonUtility::createUUID("{{cart}}",'cart_uuid');
			}			

			$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';
			$whento_deliver = isset($this->data['whento_deliver'])?$this->data['whento_deliver']:'';
			$delivery_date = isset($this->data['delivery_date'])?$this->data['delivery_date']:'';
			$delivery_time = isset($this->data['delivery_time'])?$this->data['delivery_time']:'';

			if($whento_deliver=="schedule"){
				if(empty($delivery_date)){
					$this->msg = t("Delivery date is required");
					$this->responseJson();
				}				
				if(empty($delivery_time)){
					$this->msg = t("Delivery time is required");
					$this->responseJson();
				}				
			}
						
			CCart::savedAttributes($cart_uuid,'whento_deliver',$whento_deliver);			  
			CCart::savedAttributes($cart_uuid,'delivery_date',$delivery_date);
			CCart::savedAttributes($cart_uuid,'delivery_time',json_encode($delivery_time));
			CCart::savedAttributes($cart_uuid,'transaction_type',$transaction_type);			
								
			$delivery_datetime = CCheckout::jsonTimeToFormat($delivery_date,json_encode($delivery_time));
			
			$this->code = 1; $this->msg = "OK";
			$this->details = array(
			  'whento_deliver'=>$whento_deliver,
			  'delivery_date'=>$delivery_date,
			  'delivery_time'=>$delivery_time,
			  'delivery_datetime'=>$delivery_datetime,	
			  'cart_uuid'=>$cart_uuid		  
			);						
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    		    
		}
		$this->responseJson();
	}

	public function actionTransactionInfo()
	{
			
		try {
			
			$whento_deliver = ''; $delivery_datetime='';
			$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
			$local_id = isset($this->data['local_id'])?$this->data['local_id']:'';			
			$local_info = CMaps::locationDetails($local_id,'');	
			
			$delivery_option = CCheckout::deliveryOptionList();
												
			$data = isset($this->data['choosen_delivery'])?$this->data['choosen_delivery']:'';

			$transaction_type='';
			$services_list = CServices::Listing( Yii::app()->language );
			
			if(is_array($data) && count($data)>=1){				
				$whento_deliver = isset($data['whento_deliver'])?$data['whento_deliver']:'now';
				$delivery_date = isset($data['delivery_date'])?$data['delivery_date']:date("Y-m-d");
				$delivery_time = isset($data['delivery_time'])?$data['delivery_time']:'';			
				$transaction_type = isset($data['transaction_type'])?$data['transaction_type']:'';				
				$delivery_datetime = CCheckout::jsonTimeToFormat($delivery_date,json_encode($delivery_time));
			} else {
				$whento_deliver = CCheckout::getWhenDeliver($cart_uuid);
				$delivery_datetime = CCheckout::getScheduleDateTime($cart_uuid,$whento_deliver);	
				$transaction_type = CServices::getSetService($cart_uuid);
			}

			$merchant_adddress=[];
			try {
				$merchant_id = CCart::getMerchantId($cart_uuid);			
				if($merchant = CMerchants::get($merchant_id)){
					$merchant_adddress = [
						'address'=>$merchant->address,
						'latitude'=>$merchant->latitude,
						'lontitude'=>$merchant->lontitude,
					];
				}
			} catch (Exception $e) {
				//
			}
												
			$this->code = 1; $this->msg ="ok";
			$this->details = array(
			  'address1'=>$local_info['address']['address1'],
			  'formatted_address'=>$local_info['address']['formatted_address'],
			  'latitude'=>$local_info['latitude'],
			  'longitude'=>$local_info['longitude'],
			  'delivery_option'=>$delivery_option,
			  'whento_deliver'=>$whento_deliver,
			  'delivery_datetime'=>$delivery_datetime,
			  'transaction_type'=>$transaction_type,
			  'services_list'=>$services_list,
			  'merchant_adddress'=>$merchant_adddress
			);						
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    		    
		}
		$this->responseJson();
	}

	public function actionsaveTransactionType()
	{
		try {

			$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
			$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';

			if(empty($cart_uuid)){
				$cart_uuid = CommonUtility::createUUID("{{cart}}",'cart_uuid');
			}

			CCart::savedAttributes($cart_uuid,'transaction_type',$transaction_type);
			$this->code = 1; $this->msg = "OK";
			$this->details = array(				
				'cart_uuid'=>$cart_uuid,
				'transaction_type'=>$transaction_type
			  );						

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    		    
		}
		$this->responseJson();
	}

  
  public function actiongetMerchantInfo2()
  {
	 $this->actiongetMerchantInfo();
  }

  public function actiongetMerchantInfo()
  {
     try {
		
        $slug = Yii::app()->input->get('slug');
		$place_id = Yii::app()->input->get('place_id');		
		$currency_code = Yii::app()->input->get('currency_code');	
		$base_currency = Price_Formatter::$number_format['currency_code'];	
		
		$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		$multicurrency_enabled = $multicurrency_enabled==1?true:false;		

        $data = CMerchantListingV1::getMerchantInfo($slug,Yii::app()->language); 		
        $merchant_id = intval($data['merchant_id']);
        $opening_hours = CMerchantListingV1::openingHours($merchant_id);

		// CHECK IF MERCHANT HAS DIFFERENT TIMEZONE
		$options_merchant = OptionsTools::find(['merchant_timezone','merchant_default_currency','booking_enabled',
		'booking_reservation_custom_message','booking_allowed_choose_table','booking_enabled_capcha'],
		$merchant_id);				
		$merchant_timezone = isset($options_merchant['merchant_timezone'])?$options_merchant['merchant_timezone']:'';		
		$booking_enabled = isset($options_merchant['booking_enabled'])?$options_merchant['booking_enabled']:false;		
		$booking_enabled = $booking_enabled==1?true:false;		

		$captcha_enabled = isset($options_merchant['booking_enabled_capcha'])?$options_merchant['booking_enabled_capcha']:false;
		$captcha_enabled = $captcha_enabled==1?true:false;
		$booking_reservation_custom_message = isset($options_merchant['booking_reservation_custom_message'])?$options_merchant['booking_reservation_custom_message']:'';
		$booking_reservation_terms = isset($options_merchant['booking_reservation_terms'])?$options_merchant['booking_reservation_terms']:'';
		$allowed_choose_table = isset($options_merchant['booking_allowed_choose_table'])?$options_merchant['booking_allowed_choose_table']:false;
		$allowed_choose_table = $allowed_choose_table==1?true:false;

		$merchant_default_currency = isset($options_merchant['merchant_default_currency'])?$options_merchant['merchant_default_currency']:'';
		$merchant_default_currency = !empty($merchant_default_currency)?$merchant_default_currency:$base_currency;
		if(!empty($merchant_timezone)){
			Yii::app()->timezone = $merchant_timezone;
		}			

		$currency_code = !empty($currency_code)?$currency_code: (empty($merchant_default_currency)?$base_currency:$merchant_default_currency) ;		

		if(!empty($currency_code) && $multicurrency_enabled){
			Price_Formatter::init($currency_code);			
	    }

        $this->code = 1;
        $this->msg = "ok";

        $today = strtolower(date("l"));
        $open_start=''; $open_end='';

        if(is_array($opening_hours) && count($opening_hours)>=1){
          foreach ($opening_hours as $items) {
             if($items['day']==$today){
                $open_start = Date_Formatter::Time($items['start_time']);
                $open_end = Date_Formatter::Time($items['end_time']);
             }
          }
        }        

        $data['ratings'] = number_format($data['ratings'],1,'.','');
		$data['saved_store'] = false;

		if(!Yii::app()->user->isGuest){
			try{
				CSavedStore::getStoreReview($merchant_id,Yii::app()->user->id);
				$data['saved_store'] = true;
			} catch (Exception $e) {
				//
			}			
		} 					

		$config = array();
		$format = Price_Formatter::$number_format;
		$config = [				
			'precision' => $format['decimals'],
			'minimumFractionDigits'=>$format['decimals'],
			'decimal' => $format['decimal_separator'],
			'thousands' => $format['thousand_separator'],
			'separator' => $format['thousand_separator'],
			'prefix'=> $format['position']=='left'?$format['currency_symbol']:'',
			'suffix'=> $format['position']=='right'?$format['currency_symbol']:'',
			'prefill'=>true
		];	

		$maps_config = CMaps::config();
		$maps_config_raw = $maps_config;
		$maps_config = JWT::encode($maps_config , CRON_KEY, 'HS256');   
		
		$share = [
			'title'=>$data['restaurant_name'],
			'text'=>t("Check out the {restaurant_name} delivery order with {website_title}",[
				'{restaurant_name}'=>$data['restaurant_name'],
				'{website_title}'=>Yii::app()->params['settings']['website_title']
			]),
			'url'=>Yii::app()->createAbsoluteUrl("/restaurant_slug"),
			'dialogTitle'=>t("Share")
		];
		$data['share']=$share;


		// GET delivery estimation
		$estimation = [];
		try {		
			$filter = [
				'merchant_id'=>$merchant_id,
				'shipping_type'=>"standard"
			];
			$estimation  = CMerchantListingV1::estimationMerchant2($filter);
		} catch (Exception $e) {
			//			
		}

		$charge_type = OptionsTools::find(array('merchant_delivery_charges_type'),$merchant_id);
		$charge_type = isset($charge_type['merchant_delivery_charges_type'])?$charge_type['merchant_delivery_charges_type']:'';

		// GET DISTANCE
		$distance = 0;
		$unit = $data['distance_unit'];
		try {			
			$place_data = CMaps::locationDetails($place_id,'');			
			$distance = CMaps::getLocalDistance($data['distance_unit'],$place_data['latitude'],$place_data['longitude'],
			$data['latitude'],$data['lontitude']);			
		} catch (Exception $e) {
			//			
		}

		// GET GALLERY
		$gallery = CMerchantListingV1::getGallery($merchant_id);

		// DIRECTIONS							
		$map_direction = CMerchantListingV1::mapDirection($maps_config_raw,
			$data['latitude'],$data['lontitude']
		);	   	
		$data['map_direction'] = $map_direction;
		
        $this->details = [
          'data'=>$data,
          'open_at'=>t("Open {open} - {end}",['{open}'=>$open_start,'{end}'=>$open_end]),
          'opening_hours'=>$opening_hours,
		  'gallery'=>$gallery,
		  'config'=>$config,
		  'maps_config'=>$maps_config,
		  'charge_type'=>$charge_type,
		  'estimation'=>$estimation,		  
		  'distance'=>[
			'value'=>$distance,
			'label'=>t("{{distance} {{unit}} away",[
				'{{distance}'=>$distance,
				'{{unit}}'=>MapSdk::prettyUnit($unit)
			])
		  ],		
		  'booking_settings'=>[
			'booking_enabled'=>$booking_enabled,
		    'booking_reservation_custom_message'=>$booking_reservation_custom_message,
		    'booking_reservation_terms'=>$booking_reservation_terms,
			'allowed_choose_table'=>$allowed_choose_table,
			'captcha_enabled'=>$captcha_enabled
		  ]		  
        ];				
     } catch (Exception $e) {
        $this->msg = t($e->getMessage());		    		    
     }
     $this->responseJson();
  }

  public function actionservicesList()
	{			
		try {
			
     		$slug = isset($this->data['slug'])?trim($this->data['slug']):'';    
			$model = CMerchantListingV1::getMerchantBySlug($slug);
				
			$merchant_id = $model->merchant_id;
			$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
			$place_id = isset($this->data['place_id'])?$this->data['place_id']:'';
      
			$merchant = CMerchants::get($merchant_id);			
			$data = CCheckout::getMerchantTransactionList($merchant_id,Yii::app()->language);
			$transaction = CCart::cartTransaction($cart_uuid,Yii::app()->params->local_transtype,$merchant_id);
						
			$local_info = CMaps::locationDetails($place_id,'');
						
			$filter = array(
			    'merchant_id'=>$merchant_id,
			    'lat'=>isset($local_info['latitude'])?$local_info['latitude']:'',
			    'lng'=>isset($local_info['longitude'])?$local_info['longitude']:'',
			    'unit'=> !empty($merchant->distance_unit)?$merchant->distance_unit:Yii::app()->params['settings']['home_search_unit_type'],
			    'shipping_type'=>"standard"
		    );				    
		    
		    $estimation  = CMerchantListingV1::estimationMerchant($filter);
		    $charge_type = OptionsTools::find(array('merchant_delivery_charges_type'),$merchant_id);
		    $charge_type = isset($charge_type['merchant_delivery_charges_type'])?$charge_type['merchant_delivery_charges_type']:'';
			
			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(
			  'data'=>$data,
			  'transaction'=>$transaction,
			  'charge_type'=>$charge_type,
			  'estimation'=>$estimation,
			);						      
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		   
		}		
		$this->responseJson();
	}  

	public function actiongetReview()
	{				
		try {			
			
			$limit = Yii::app()->params->list_limit;			
			$page = intval(Yii::app()->input->post('page'));
			$merchant_slug = trim(Yii::app()->input->post('slug'));
			$page_raw = intval(Yii::app()->input->post('page'));
			if($page>0){
				$page = $page-1;
			}
						
			$merchant = CMerchantListingV1::getMerchantBySlug($merchant_slug);			
			$merchant_id = $merchant->merchant_id;

			$criteria=new CDbCriteria();
			$criteria->alias = "a";
			$criteria->select="
			a.review,a.rating,
			concat(b.first_name,' ',b.last_name) as customer_fullname,
			b.avatar as logo, b.path,
			a.date_created,a.as_anonymous,
			(
			select group_concat(meta_name,';',meta_value)
			from {{review_meta}}
			where review_id = a.id
			) as meta,
			
			(
			select group_concat(upload_uuid,';',filename,';',path)
			from {{media_files}}
			where upload_uuid IN (
				select meta_value from {{review_meta}}
				where review_id = a.id
			)
			) as media
			";
			$criteria->join='LEFT JOIN {{client}} b on a.client_id = b.client_id ';
			$criteria->condition = "a.merchant_id=:merchant_id AND a.status =:status AND parent_id = 0";
			$criteria->params = [
				':merchant_id'=>$merchant_id,
				':status'=>'publish'
			];
			$criteria->order = "a.id DESC";

			$count=AR_review::model()->count($criteria);
			$pages=new CPagination($count);
			$pages->pageSize=$limit;
			$pages->setCurrentPage( $page );
			$pages->applyLimit($criteria);
			$page_count = $pages->getPageCount();

			if($page>0){
				if($page_raw>$page_count){
					$this->code = 3;
					$this->msg = t("end of results");
					$this->responseJson();
				}
			}
			
			$dependency = CCacheData::dependency();
			if($model = AR_review::model()->cache(Yii::app()->params->cache, $dependency)->findAll($criteria)){
				$data = array();
				foreach ($model as $items) {
					
					$meta = !empty($items->meta)?explode(",",$items->meta):'';
				    $media = !empty($items->media)?explode(",",$items->media):'';
				
				    $meta_data = array(); $media_data=array();

					if(is_array($media) && count($media)>=1){
						foreach ($media as $media_val) {
							$_media = explode(";",$media_val);
							$media_data[$_media['0']] = array(
							  'filename'=>$_media[1],
							  'path'=>$_media[2],
							);
						}
					}

					if(is_array($meta) && count($meta)>=1){
						foreach ($meta as $meta_value) {
							$_meta = explode(";",$meta_value);						
							if($_meta[0]=="upload_images"){							 
								 if(isset( $media_data[$_meta[1]] )){									 								    
									$meta_data[$_meta[0]][] = CMedia::getImage(
									  $media_data[$_meta[1]]['filename'],
									  $media_data[$_meta[1]]['path']
									);
								 }
							} else $meta_data[$_meta[0]][] = $_meta[1];						
						}
					}

					$data[]=array(
						'review'=>Yii::app()->input->xssClean($items->review),
						'rating'=>intval($items->rating),
						'fullname'=>Yii::app()->input->xssClean($items->customer_fullname),
						'hidden_fullname'=>CommonUtility::mask($items->customer_fullname),				  
						'url_image'=>CMedia::getImage($items->logo,$items->path,Yii::app()->params->size_image,
						 CommonUtility::getPlaceholderPhoto('customer')),
						'as_anonymous'=>intval($items->as_anonymous),
						'meta'=>$meta_data,
						'date_created'=>Date_Formatter::dateTime($items->date_created)
					  );

				}				

				$this->code = 1; $this->msg = "ok";
				$this->details = [
					'page_raw'=>$page_raw,
					'page_count'=>$page_count,
					'data'=>$data
				];
			} else $this->msg = t("No results");			
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		   		   
		}		
		$this->responseJson();
	}

	public function actiongeStoreMenu()
	{						
		try {
						
		   $slug = Yii::app()->input->post('slug'); 	
		   $currency_code = Yii::app()->input->post('currency_code');	
		   $base_currency = Price_Formatter::$number_format['currency_code'];		
			
		   $multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		   $multicurrency_enabled = $multicurrency_enabled==1?true:false;		

		   $exchange_rate = 1;

		   $model = CMerchantListingV1::getMerchantBySlug($slug);
		   $merchant_id = $model->merchant_id;
		   
		    // CHECK IF MERCHANT HAS DIFFERENT TIMEZONE
			$options_merchant = OptionsTools::find(['merchant_timezone','merchant_default_currency'],$merchant_id);				
			$merchant_timezone = isset($options_merchant['merchant_timezone'])?$options_merchant['merchant_timezone']:'';		
			$merchant_default_currency = isset($options_merchant['merchant_default_currency'])?$options_merchant['merchant_default_currency']:'';
			$merchant_default_currency = !empty($merchant_default_currency)?$merchant_default_currency:$base_currency;
			if(!empty($merchant_timezone)){
				Yii::app()->timezone = $merchant_timezone;
			}			

			$currency_code = !empty($currency_code)?$currency_code: (empty($merchant_default_currency)?$base_currency:$merchant_default_currency) ;		
			
			// SET CURRENCY
			if(!empty($currency_code) && $multicurrency_enabled){
				Price_Formatter::init($currency_code);
				if($currency_code!=$merchant_default_currency){
					$exchange_rate = CMulticurrency::getExchangeRate($merchant_default_currency,$currency_code);					
				}
		   }
		   
		   CMerchantMenu::setExchangeRate($exchange_rate);
		   
		   $category = CMerchantMenu::getCategory($merchant_id,Yii::app()->language);		
		   $items = CMerchantMenu::getMenu($merchant_id,Yii::app()->language);		   		   
		   $items_not_available = CMerchantMenu::getItemAvailability($merchant_id,date("w"),date("H:h:i"));	
		   $category_not_available = CMerchantMenu::getCategoryAvailability($merchant_id,date("w"),date("H:h:i"));
		   $data = array(
		     'category'=>$category,
		     'items'=>$items,
			 'items_not_available'=>$items_not_available,
			 'category_not_available'=>$category_not_available
		   );		   				   
		   $this->code = 1; $this->msg = "OK";
		   $this->details = array(		     		      
		     'merchant_id'=>$merchant_id,
		     'data'=>$data,			 
		   );		   
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		   
		}		
		$this->responseJson();
	}	

	public function actiongetMenuItem2()
	{		
		$this->actiongetMenuItem();
	}

	public function actiongetMenuItem()
	{		
		try {
									
			$slug = Yii::app()->input->post('slug'); 			
		    $model = CMerchantListingV1::getMerchantBySlug($slug);	
			$merchant_id = $model->merchant_id;
			$item_uuid = Yii::app()->input->post('item_uuid');
			$cat_id = intval(Yii::app()->input->post('cat_id'));
			$currency_code = Yii::app()->input->post('currency_code');
		    $base_currency = Price_Formatter::$number_format['currency_code'];					

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		    $multicurrency_enabled = $multicurrency_enabled==1?true:false;		
		    $exchange_rate = 1;

			$options_merchant = OptionsTools::find(['merchant_default_currency'],$merchant_id);
			$merchant_default_currency = isset($options_merchant['merchant_default_currency'])?$options_merchant['merchant_default_currency']:'';
			$merchant_default_currency = !empty($merchant_default_currency)?$merchant_default_currency:$base_currency;
			$currency_code = !empty($currency_code)?$currency_code: (empty($merchant_default_currency)?$base_currency:$merchant_default_currency) ;
			
			// SET CURRENCY			
			if(!empty($currency_code) && $multicurrency_enabled){
				Price_Formatter::init($currency_code);		
				if($currency_code!=$merchant_default_currency){
					$exchange_rate = CMulticurrency::getExchangeRate($merchant_default_currency,$currency_code);					
				}
			}

			CMerchantMenu::setExchangeRate($exchange_rate);
			
			$items = CMerchantMenu::getMenuItem($merchant_id,$cat_id,$item_uuid,Yii::app()->language);			
			$addons = CMerchantMenu::getItemAddonCategory($merchant_id,$item_uuid,Yii::app()->language);
			$addon_items = CMerchantMenu::getAddonItems($merchant_id,$item_uuid,Yii::app()->language);	
			//$meta = CMerchantMenu::getItemMeta($merchant_id,$item_uuid);
			$meta = CMerchantMenu::getItemMeta2($merchant_id, isset($items['item_id'])?$items['item_id']:0 );
			$meta_details = CMerchantMenu::getMeta($merchant_id,$item_uuid,Yii::app()->language);	
						
			AppIdentity::getCustomerIdentity();
			$items['save_item']	= false;
						
			if(!Yii::app()->user->isGuest){
				try {
					CSavedStore::getSaveItems(Yii::app()->user->id,$items['merchant_id'],$items['item_id']);
					$items['save_item']	= true;
				} catch (Exception $e) {
					//
				}
			}
									
			$data = array(
			  'items'=>$items,
			  'addons'=>$addons,
			  'addon_items'=>$addon_items,
			  'meta'=>$meta,
			  'meta_details'=>$meta_details			  
			);

			$config = array();
			$format = Price_Formatter::$number_format;
			$config = [				
				'precision' => $format['decimals'],
				'decimal' => $format['decimal_separator'],
				'thousands' => $format['thousand_separator'],
				'prefix'=> $format['position']=='left'?$format['currency_symbol']:'',
				'suffix'=> $format['position']=='right'?$format['currency_symbol']:''
			];			
			$this->code = 1; $this->msg = "ok";
		    $this->details = array(
		      'next_action'=>"show_item_details",
		      'sold_out_options'=>AttributesTools::soldOutOptions(),
			  'default_sold_out_options'=>[
				  'label'=>t("Go with merchant recommendation"),
				  'value'=>"substitute"
			  ],
		      'data'=>$data,
			  'merchant_id'=>$merchant_id,
			  'restaurant_name'=> Yii::app()->input->xssClean($model->restaurant_name),
			  'config'=>$config
		    );		    				
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());
		   $this->details = array(
		      'data'=>array()
		    );		    	   
		}		
		$this->responseJson();
	}

	public function actionaddCartItems()
	{
			
		$slug = isset($this->data['slug'])?$this->data['slug']:'';
		$model = CMerchantListingV1::getMerchantBySlug($slug);			
		$merchant_id = $model->merchant_id;
		$uuid = CommonUtility::createUUID("{{cart}}",'cart_uuid');
		$cart_row = CommonUtility::generateUIID();
		$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';		
		$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';		
		$cart_uuid = !empty($cart_uuid)?$cart_uuid:$uuid;		
		$cat_id = isset($this->data['cat_id'])?(integer)$this->data['cat_id']:'';
		$item_token = isset($this->data['item_token'])?$this->data['item_token']:'';
		$item_size_id = isset($this->data['item_size_id'])?(integer)$this->data['item_size_id']:0;
		$item_qty = isset($this->data['item_qty'])?(integer)$this->data['item_qty']:0;
		$special_instructions = isset($this->data['special_instructions'])?$this->data['special_instructions']:'';
		$if_sold_out = isset($this->data['if_sold_out'])?$this->data['if_sold_out']:'';
		$inline_qty = isset($this->data['inline_qty'])?(integer)$this->data['inline_qty']:0;

		$addons = array();
		$item_addons = isset($this->data['item_addons'])?$this->data['item_addons']:'';
		if(is_array($item_addons) && count($item_addons)>=1){
			foreach ($item_addons as $val) {				
				$multi_option = isset($val['multi_option'])?$val['multi_option']:'';
				$subcat_id = isset($val['subcat_id'])?(integer)$val['subcat_id']:0;
				$sub_items = isset($val['sub_items'])?$val['sub_items']:'';
				$sub_items_checked = isset($val['sub_items_checked'])?(integer)$val['sub_items_checked']:0;				
				if($multi_option=="one" && $sub_items_checked>0){
					$addons[] = array(
					  'cart_row'=>$cart_row,
					  'cart_uuid'=>$cart_uuid,
					  'subcat_id'=>$subcat_id,
					  'sub_item_id'=>$sub_items_checked,					 
					  'qty'=>1,
					  'multi_option'=>$multi_option,
					);
				} else {
					foreach ($sub_items as $sub_items_val) {
						if($sub_items_val['checked']==1){							
							$addons[] = array(
							  'cart_row'=>$cart_row,
							  'cart_uuid'=>$cart_uuid,
							  'subcat_id'=>$subcat_id,
							  'sub_item_id'=>isset($sub_items_val['sub_item_id'])?(integer)$sub_items_val['sub_item_id']:0,							  
							  'qty'=>isset($sub_items_val['qty'])?(integer)$sub_items_val['qty']:0,
							  'multi_option'=>$multi_option,
							);
						}
					}
				}
			}
		}
		
		$attributes = array();
		$meta = isset($this->data['meta'])?$this->data['meta']:'';
		if(is_array($meta) && count($meta)>=1){
			foreach ($meta as $meta_name=>$metaval) {				
				if($meta_name!="dish"){
					foreach ($metaval as $val) {
						if($val['checked']>0){	
							$attributes[]=array(
							  'cart_row'=>$cart_row,
							  'cart_uuid'=>$cart_uuid,
							  'meta_name'=>$meta_name,
							  'meta_id'=>$val['meta_id']
							);
						}
					}
				}
			}
		}

		$items = array(
			'merchant_id'=>$merchant_id,
			'cart_row'=>$cart_row,
			'cart_uuid'=>$cart_uuid,
			'cat_id'=>$cat_id,
			'item_token'=>$item_token,
			'item_size_id'=>$item_size_id,
			'qty'=>$item_qty,
			'special_instructions'=>$special_instructions,
			'if_sold_out'=>$if_sold_out,
			'addons'=>$addons,
			'attributes'=>$attributes,
			'inline_qty'=>$inline_qty
		);		
				 		
		try {
			
			CCart::add($items);
			
			// quick fixed 
			if(!CCart::getAttributes($cart_uuid,Yii::app()->params->local_transtype)){			   
				CCart::savedAttributes($cart_uuid,Yii::app()->params->local_transtype,$transaction_type);
			}				
					  
			/*SAVE DELIVERY DETAILS*/
			if(!CCart::getAttributes($cart_uuid,'whento_deliver')){		     
			   $whento_deliver = isset($this->data['whento_deliver'])?$this->data['whento_deliver']:'now';
			   CCart::savedAttributes($cart_uuid,'whento_deliver',$whento_deliver);
			   if($whento_deliver=="schedule"){
				  $delivery_date = isset($this->data['delivery_date'])?$this->data['delivery_date']:'';
				  $delivery_time_raw = isset($this->data['delivery_time_raw'])?$this->data['delivery_time_raw']:'';
				  if(!empty($delivery_date)){
					  CCart::savedAttributes($cart_uuid,'delivery_date',$delivery_date);
				  }
				  if(!empty($delivery_time_raw)){
					  CCart::savedAttributes($cart_uuid,'delivery_time',json_encode($delivery_time_raw));
				  }
			   }
			}
										
			$this->code = 1 ; $this->msg = "OK";			
			$this->details = array(
			  'cart_uuid'=>$cart_uuid
			);		 
			  
		  } catch (Exception $e) {
			 $this->msg = t($e->getMessage());
			 $this->details = array(
				'data'=>array()
			  );		    	   
		  }		
		$this->responseJson();
	}	

	public function actiongetCartCheckout()
	{		
		$this->actiongetCart();
	}

	public function actiongetCart()
	{						
				
		$local_id = isset($this->data['place_id'])?trim($this->data['place_id']):'';				
		$cart_uuid = isset($this->data['cart_uuid'])?trim($this->data['cart_uuid']):'';		
		$payload = isset($this->data['payload'])?$this->data['payload']:'';	
		$currency_code = isset($this->data['currency_code'])?$this->data['currency_code']:'';
		$base_currency = Price_Formatter::$number_format['currency_code'];		
		
		$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		$multicurrency_enabled = $multicurrency_enabled==1?true:false;
		
		$distance = 0; 
		$unit = isset(Yii::app()->params['settings']['home_search_unit_type'])?Yii::app()->params['settings']['home_search_unit_type']:'mi';
		$error = array(); 
		$minimum_order = 0; 
		$maximum_order=0;
		$merchant_info = array(); 
		$delivery_fee = 0; 
		$distance_covered=0;
		$merchant_lat = ''; 
		$merchant_lng=''; 
		$out_of_range = false;
		$address_component = array();
		$items_count=0;
		$resp_opening = array();
		$transaction_info = array();
		$data_transaction = array();
		$tips_data  = array();
		$enabled_tip = false;
		$enabled_voucher = false;
		$exchange_rate = 1;
		$admin_exchange_rate = 1;
		$points_to_earn = 0; $points_label = '';
		
		try {
							
			
			if(in_array('distance',(array)$payload)){				
				CMaps::config();				
			}

			$merchant_id = CCart::getMerchantId($cart_uuid);
		 	$options_merchant = OptionsTools::find(['merchant_timezone','merchant_default_currency'],$merchant_id);						
		    $merchant_default_currency = isset($options_merchant['merchant_default_currency'])?$options_merchant['merchant_default_currency']:'';
			$merchant_default_currency = !empty($merchant_default_currency)?$merchant_default_currency:$base_currency;

			$currency_code = !empty($currency_code)?$currency_code: (empty($merchant_default_currency)?$base_currency:$merchant_default_currency) ;			

			$points_enabled = isset(Yii::app()->params['settings']['points_enabled'])?Yii::app()->params['settings']['points_enabled']:false;
		    $points_enabled = $points_enabled==1?true:false;
		    $points_earning_rule = isset(Yii::app()->params['settings']['points_earning_rule'])?Yii::app()->params['settings']['points_earning_rule']:'sub_total';									
			$points_earning_points = isset(Yii::app()->params['settings']['points_earning_points'])?Yii::app()->params['settings']['points_earning_points']:0;	
			$points_minimum_purchase = isset(Yii::app()->params['settings']['points_minimum_purchase'])?Yii::app()->params['settings']['points_minimum_purchase']:0;	
			$points_maximum_purchase = isset(Yii::app()->params['settings']['points_maximum_purchase'])?Yii::app()->params['settings']['points_maximum_purchase']:0;

			// SET CURRENCY
			if(!empty($currency_code) && $multicurrency_enabled){
				Price_Formatter::init($currency_code);
				if($currency_code!=$merchant_default_currency){
					$exchange_rate = CMulticurrency::getExchangeRate($merchant_default_currency,$currency_code);					
				}
				if($currency_code!=$base_currency){							
					$admin_exchange_rate = CMulticurrency::getExchangeRate($currency_code,$base_currency);						
				}
			}
			
			CCart::setExchangeRate($exchange_rate);
			CCart::setAdminExchangeRate($admin_exchange_rate);
			CCart::setPointsRate($points_enabled,$points_earning_rule,$points_earning_points,$points_minimum_purchase,$points_maximum_purchase);
						
			require_once 'get-cart.php';			

			$atts = CCart::getAttributesAll($cart_uuid,array('contact_number','contact_number_prefix'));						
			$contact_number = isset($atts['contact_number'])?$atts['contact_number']:'';
			$default_prefix = isset($atts['contact_number_prefix'])?$atts['contact_number_prefix']:'';	
			
			
			if(empty($contact_number) && !Yii::app()->user->isGuest ){
				try {					
					$contact_number = Yii::app()->user->contact_number;
					$default_prefix = Yii::app()->user->phone_prefix;			
			    } catch (Exception $e) {
					//dump($e->getMessage());die();
				}
			}

			$contact_number = str_replace($default_prefix,"",$contact_number);
			$default_prefix = str_replace("+","",$default_prefix);			


			if(in_array('check_opening',(array)$payload) && $transaction_type=="delivery" ){
				try {
					$date = date("Y-m-d");
			        $time_now = date("H:i");

					$choosen_delivery = isset($this->data['choosen_delivery'])?$this->data['choosen_delivery']:'';		
					$whento_deliver = isset($choosen_delivery['whento_deliver'])?$choosen_delivery['whento_deliver']:'';
					
					if($whento_deliver=="schedule"){						
						if(isset($choosen_delivery['delivery_date'])){
							$date = !empty($choosen_delivery['delivery_date'])?$choosen_delivery['delivery_date']:$date;
						}

						if(isset($choosen_delivery['delivery_time'])){
							if(isset($choosen_delivery['delivery_time']['start_time'])){
								$time_now  = !empty($choosen_delivery['delivery_time']['start_time'])?$choosen_delivery['delivery_time']['start_time']:$time_now;
							}
						}						
					}
								
					$datetime_to = date("Y-m-d g:i:s a",strtotime("$date $time_now"));
					CMerchantListingV1::checkCurrentTime( date("Y-m-d g:i:s a") , $datetime_to);		
										
					$resp_opening = CMerchantListingV1::checkStoreOpen($merchant_id,$date,$time_now);					
					if($resp_opening['merchant_open_status']<=0){
						$error[] = t("This store is closed right now, but you can schedule an order later.");
					}

				} catch (Exception $e) {
					//
				}
			}

			$this->code = 1; $this->msg = "ok";
		    $this->details = array(			      
		      'cart_uuid'=>$cart_uuid,
		      'payload'=>$payload,
		      'error'=>$error,
		      'checkout_data'=>$checkout_data,
		      'out_of_range'=>$out_of_range,
		      'address_component'=>$address_component,
		      'go_checkout'=>$go_checkout,
		      'items_count'=>$items_count,
		      'data'=>$data,		      
			  'phone_details'=>[
				'contact_number_w_prefix'=>$default_prefix.$contact_number,
				'contact_number'=>$contact_number,
				'default_prefix'=>$default_prefix,
			  ],
			  'resp_opening'=>$resp_opening,
			  'transaction_info'=>$transaction_info,
			  'data_transaction'=>$data_transaction,
			  'tips_data'=>$tips_data,
			  'enabled_tip'=>$enabled_tip,
			  'enabled_voucher'=>$enabled_voucher,
			  'points_data'=>[
				'points_enabled'=>$points_enabled,
			    'points_to_earn'=>$points_to_earn,
			    'points_label'=>$points_label,
			  ]
		    );						
		} catch (Exception $e) {
		   $error[] = t($e->getMessage());		
		   $this->details = array('items_count'=>$items_count);	   		   
		}						
		$this->responseJson();
	}

	public function actionupdateCartItems()
	{				
		$cart_uuid = Yii::app()->input->post('cart_uuid');
		$cart_row = Yii::app()->input->post('cart_row');
		$item_qty = Yii::app()->input->post('item_qty');
		try {
			
			CCart::update($cart_uuid,$cart_row,$item_qty);
			$this->code = 1; $this->msg = "Ok";
			$this->details = array(
		      'data'=>array()
		    );		    	   			
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());
		   $this->details = array(
		      'data'=>array()
		    );		    	   
		}		
		$this->responseJson();
	}

	public function actionremoveCartItem()
	{		
		$cart_uuid = Yii::app()->input->post('cart_uuid');
		$row = Yii::app()->input->post('row');
		
		try {
			
			CCart::remove($cart_uuid,$row);
			$this->code = 1; $this->msg = "Ok";
			$this->details = array(
		      'data'=>array()
		    );		    	   			
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());
		   $this->details = array(
		      'data'=>array()
		    );		    	   
		}		
		$this->responseJson();
	}

	public function actionclearCart()
	{						
		try {			
			$cart_uuid = Yii::app()->input->post('cart_uuid');
			CCart::clear($cart_uuid);
			$this->code = 1; $this->msg = "Ok";
			$this->details = array(
		      'data'=>array()
		    );		    	   			
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());
		   $this->details = array(
		      'data'=>array()
		    );		    	   
		}		
		$this->responseJson();
	}

	public function actionclientAddresses()
	{
	   	try {
			   			 			
			$addresses = CClientAddress::getAddresses( Yii::app()->user->id );				
			$this->code = 1;
			$this->msg = "ok";
			$this->details = array(
				'addresses'=>$addresses,			  
			);
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}			    
		$this->responseJson();
	}

	public function actiongetPhone()
	{
		try {

			$options = OptionsTools::find(array('mobilephone_settings_default_country','mobilephone_settings_country'));			
			$phone_default_country = isset($options['mobilephone_settings_default_country'])?$options['mobilephone_settings_default_country']:'us';
            $phone_country_list = isset($options['mobilephone_settings_country'])?$options['mobilephone_settings_country']:'';
            $phone_country_list = !empty($phone_country_list)?json_decode($phone_country_list,true):array();        
			
			$data = AttributesTools::countryMobilePrefixWithFilter($phone_country_list);

			$cart_uuid = Yii::app()->input->post('cart_uuid');
			
			$atts = CCart::getAttributesAll($cart_uuid,array('contact_number','contact_number_prefix'));			
			$contact_number = isset($atts['contact_number'])?$atts['contact_number']:'';
			$default_prefix = isset($atts['contact_number_prefix'])?$atts['contact_number_prefix']:'';	
						
			if(empty($contact_number)){
				$contact_number = Yii::app()->user->contact_number;
				$default_prefix = Yii::app()->user->phone_prefix;			
			}
												
			$contact_number = str_replace($default_prefix,"",$contact_number);
			$contact_number = str_replace("+","",$contact_number);
			$default_prefix = str_replace("+","",$default_prefix);
			
			if(empty($default_prefix)){
				$default_prefix_array = AttributesTools::getMobileByShortCode($phone_default_country);
			} else $default_prefix_array = AttributesTools::getMobileByPhoneCode($default_prefix);
			
						
			$this->code = 1;
			$this->msg = "OK";			
			$this->details = array(
			  'contact_number_w_prefix'=>$default_prefix.$contact_number,
			  'contact_number'=>$contact_number,
			  'default_prefix'=>$default_prefix,
			  'default_prefix_array'=>$default_prefix_array,
			  'prefixes'=>$data,
			  'phone_default_country'=>$phone_default_country
			);		
			
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());
		}
		$this->responseJson();
	}	

	public function actionRequestEmailCode()
	{
		try {		    					
		    if(!Yii::app()->user->isGuest){		    
		    	$model = AR_client::model()->find('client_id=:client_id', 
		        array(':client_id'=>Yii::app()->user->id)); 	
		        if($model){		           
		           $digit_code = CommonUtility::generateNumber(5);
		           $model->mobile_verification_code = $digit_code;
				   $model->scenario="resend_otp";
		           if($model->save()){		   
		           	   // SEND EMAIL HERE         
			           $this->code = 1;
			           $this->msg = t("We sent a code to {{email_address}}.",array(
			             '{{email_address}}'=> CommonUtility::maskEmail($model->email_address)
			           ));			           
                       if(DEMO_MODE==TRUE){
		    			  $this->details['verification_code']=t("Your verification code is {{code}}",array('{{code}}'=>$digit_code));
		    		   }
		           } else $this->msg = CommonUtility::parseError($model->getErrors());
		        } else $this->msg[] = t("Record not found");
		    } else $this->msg[] = t("Your session has expired please relogin");
		    
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionChangePhone()
	{
		try {
									
			$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
			$code = isset($this->data['code'])?$this->data['code']:'';
			$mobile_prefix = isset($this->data['phone_prefix'])?$this->data['phone_prefix']:'';
			$mobile_number = isset($this->data['phone_number'])?$this->data['phone_number']:'';
					   
			$model = AR_client::model()->find('client_id=:client_id AND mobile_verification_code=:mobile_verification_code', 
			array(':client_id'=>Yii::app()->user->id,':mobile_verification_code'=>trim($code) )); 		
			if($model){
				   $model->phone_prefix = $mobile_prefix;
				   $model->contact_phone = $mobile_prefix.$mobile_number;
				   if($model->save()){	
					   CCart::savedAttributes($cart_uuid,'contact_number', $model->contact_phone );
					   CCart::savedAttributes($cart_uuid,'contact_number_prefix', $mobile_prefix );

					   $this->code = 1;
					   $this->msg = t("Succesfully change phone number");
					   $this->details = array(
						 'phone_prefix'=>$mobile_prefix,
						 'contact_phone'=>$model->contact_phone,						 
					   );
				   } else $this->msg = CommonUtility::parseError($model->getErrors()); 
			} else $this->msg[] = t("Invalid verification code");
			
		 } catch (Exception $e) {
			 $this->msg[] = t($e->getMessage());
		 }		
		 $this->responseJson();
	}	

	public function actionloadPromo()
	{
		
		try {
			
			$merchant_id = intval(Yii::app()->input->post('merchant_id'));		
			$currency_code = Yii::app()->input->post('currency_code');				
			$base_currency = Price_Formatter::$number_format['currency_code'];			
			$cart_uuid = Yii::app()->input->post('cart_uuid');			

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		    $multicurrency_enabled = $multicurrency_enabled==1?true:false;		
			$exchange_rate = 1;

			$options_merchant = OptionsTools::find(['merchant_default_currency'],$merchant_id);							
			$merchant_default_currency = isset($options_merchant['merchant_default_currency'])?$options_merchant['merchant_default_currency']:'';
			$merchant_default_currency = !empty($merchant_default_currency)?$merchant_default_currency:$base_currency;
			$currency_code = !empty($currency_code)?$currency_code: (empty($merchant_default_currency)?$base_currency:$merchant_default_currency) ;

			// SET CURRENCY
			if(!empty($currency_code) && $multicurrency_enabled){
				Price_Formatter::init($currency_code);
				if($currency_code!=$merchant_default_currency){
					$exchange_rate = CMulticurrency::getExchangeRate($merchant_default_currency,$currency_code);					
				}
		    }

			CPromos::setExchangeRate($exchange_rate);
			$data = CPromos::promo($merchant_id,date("Y-m-d"));			
			
			$promo_selected = array();
			$atts = CCart::getAttributesAll($cart_uuid,array('promo','promo_type','promo_id'));
			if($atts){
				$saving = '';
				if(isset($atts['promo'])){
					if ($promo = json_decode($atts['promo'],true)){																		
						if($promo['type']=="offers"){
							$merchant_id = CCart::getMerchantId($cart_uuid);
			                CCart::getContent($cart_uuid,Yii::app()->language);	
							$subtotal = CCart::getSubTotal();							
			                $sub_total = floatval($subtotal['sub_total']);
							$discount_percent = isset($promo['value'])? CCart::cleanValues($promo['value']):0;							
							$discount_value = ($discount_percent/100) * $sub_total;
							$saving = t("You're saving {{discount}}",array(
								'{{discount}}'=>Price_Formatter::formatNumber( ($discount_value*$exchange_rate) )
							));
						} elseif ( $promo['type']=="voucher" ){
							$discount_value = isset($promo['value'])?$promo['value']:0;
							$discount_value = $discount_value*-1;	
							$saving = t("You're saving {{discount}}",array(
							  '{{discount}}'=>Price_Formatter::formatNumber( ($discount_value*$exchange_rate) )
							));
						}
						$promo_selected = [
							'promo_type'=>$atts['promo_type'],
							'promo_id'=>$atts['promo_id'],
							'savings'=>$saving
						];
					}
				}				
			}
				
			if($data){
				$this->code = 1; $this->msg = "ok";	
				$this->details = array(
				  'count'=>count($data),
				  'data'=>$data,
				  'promo_selected'=>$promo_selected
				);				
			} else $this->msg = t("no results");	
			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}	

	public function actionapplyPromo()
	{
		$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
		$promo_id = isset($this->data['promo_id'])?intval($this->data['promo_id']):'';
		$promo_type = isset($this->data['promo_type'])?$this->data['promo_type']:'';
		$currency_code = isset($this->data['currency_code'])?$this->data['currency_code']:'';		
		$base_currency = Price_Formatter::$number_format['currency_code'];		

		$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		$multicurrency_enabled = $multicurrency_enabled==1?true:false;
		$exchange_rate = 1;
		
		try {

			$merchant_id = CCart::getMerchantId($cart_uuid);

			$options_merchant = OptionsTools::find(['merchant_default_currency'],$merchant_id);							
			$merchant_default_currency = isset($options_merchant['merchant_default_currency'])?$options_merchant['merchant_default_currency']:'';
			$merchant_default_currency = !empty($merchant_default_currency)?$merchant_default_currency:$base_currency;
			$currency_code = !empty($currency_code)?$currency_code: (empty($merchant_default_currency)?$base_currency:$merchant_default_currency) ;

			// SET CURRENCY
			if(!empty($currency_code) && $multicurrency_enabled){
				Price_Formatter::init($currency_code);
				if($currency_code!=$merchant_default_currency){
					$exchange_rate = CMulticurrency::getExchangeRate($merchant_default_currency,$currency_code);					
				}
		    }

			CCart::getContent($cart_uuid,Yii::app()->language);	
			$subtotal = CCart::getSubTotal();
			$sub_total = floatval($subtotal['sub_total']);
			
			$now = date("Y-m-d");			
			$params = array();

			CPromos::setExchangeRate($exchange_rate);
				   
			if($promo_type==="voucher"){
																
				$transaction_type = CCart::cartTransaction($cart_uuid,Yii::app()->params->local_transtype,$merchant_id);								
				$resp = CPromos::applyVoucher( $merchant_id, $promo_id, Yii::app()->user->id , $now , ($sub_total*$exchange_rate) , $transaction_type);
				$less_amount = $resp['less_amount'];
				
				$params = array(
				  'name'=>"less voucher",
				  'type'=>$promo_type,
				  'id'=>$promo_id,
				  'target'=>'subtotal',
				  'value'=>"-$less_amount",
				);		
				
				
			} else if ($promo_type=="offers") {		
				
				$transaction_type = CCart::cartTransaction($cart_uuid,Yii::app()->params->local_transtype,$merchant_id);							
				$resp = CPromos::applyOffers( $merchant_id, $promo_id, $now , ($sub_total*$exchange_rate) , $transaction_type);				
				$less_amount = $resp['less_amount'];
				
				$name = array(
				  'label'=>"Discount {{discount}}%",
				  'params'=>array(
				   '{{discount}}'=>Price_Formatter::convertToRaw($less_amount,0)
				  )
				);
				$params = array(
				  'name'=> json_encode($name),
				  'type'=>$promo_type,
				  'id'=>$promo_id,
				  'target'=>'subtotal',
				  'value'=>"-%$less_amount"
				);													
			}
			
			CCart::savedAttributes($cart_uuid,'promo',json_encode($params));
			CCart::savedAttributes($cart_uuid,'promo_type',$promo_type);
			CCart::savedAttributes($cart_uuid,'promo_id',$promo_id);
								
			$this->code = 1; 
			$this->msg = "succesful";

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}	

	public function actionremovePromo()
	{
				
		$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
		$promo_id = isset($this->data['promo_id'])?intval($this->data['promo_id']):'';
		$promo_type = isset($this->data['promo_type'])?$this->data['promo_type']:'';
				
		try {
			
			$merchant_id = CCart::getMerchantId($cart_uuid);			
			CCart::deleteAttributesAll($cart_uuid,CCart::CONDITION_RM);
			$this->code = 1;
			$this->msg = "ok";
			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}	

	public function actionapplyPromoCode()
	{		
		
		$promo_code = isset($this->data['promo_code'])?trim($this->data['promo_code']):'';
		$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
		try {

			if(empty($promo_code)){
				$this->msg = t("Promo code is required");
				$this->responseJson();
			}
			
			$merchant_id = CCart::getMerchantId($cart_uuid);
			CCart::getContent($cart_uuid,Yii::app()->language);	
			$subtotal = CCart::getSubTotal();
			$sub_total = floatval($subtotal['sub_total']);
			$now = date("Y-m-d");	
			
			$model = AR_voucher::model()->find('voucher_name=:voucher_name', 
		    array(':voucher_name'=>$promo_code)); 		
		    if($model){
		    	
		    	$promo_id = $model->voucher_id;
		    	$voucher_owner = $model->voucher_owner;
		    	$promo_type = 'voucher';
		    	
		    	$resp = CPromos::applyVoucher( $merchant_id, $promo_id, Yii::app()->user->id , $now , $sub_total);
		    	$less_amount = $resp['less_amount'];
		    	
		    	$params = array(
				  'name'=>"less voucher",
				  'type'=>$promo_type,
				  'id'=>$promo_id,
				  'target'=>'subtotal',
				  'value'=>"-$less_amount",
				  'voucher_owner'=>$voucher_owner,
				);						
				
				CCart::savedAttributes($cart_uuid,'promo',json_encode($params));
			    CCart::savedAttributes($cart_uuid,'promo_type',$promo_type);
			    CCart::savedAttributes($cart_uuid,'promo_id',$promo_id);
			    
			    $this->code = 1; 
			    $this->msg = "succesful";
			    
		    } else $this->msg = t("Voucher code not found");
					
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionloadTips()
	{
		
		try {
						
			$cart_uuid = Yii::app()->input->post('cart_uuid');
			$currency_code = Yii::app()->input->post('currency_code');	
			$base_currency = Price_Formatter::$number_format['currency_code'];

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		    $multicurrency_enabled = $multicurrency_enabled==1?true:false;		

			$exchange_rate = 1;

			$merchant_id = CCart::getMerchantId($cart_uuid);	
			
			//$options_merchant = OptionsTools::find(['merchant_default_currency'],$merchant_id);							
			$options_merchant = OptionsTools::find(['merchant_enabled_tip','tips_in_transactions','merchant_tip_type','merchant_default_currency'],$merchant_id);										
			$tip_type = isset($options_merchant['merchant_tip_type'])?$options_merchant['merchant_tip_type']:'fixed';			
			$merchant_default_currency = isset($options_merchant['merchant_default_currency'])?$options_merchant['merchant_default_currency']:'';
			$merchant_default_currency = !empty($merchant_default_currency)?$merchant_default_currency:$base_currency;

			$currency_code = !empty($currency_code)?$currency_code: (empty($merchant_default_currency)?$base_currency:$merchant_default_currency) ;		
			
			if(!empty($currency_code) && $multicurrency_enabled){
				Price_Formatter::init($currency_code);
				if($currency_code!=$merchant_default_currency){
					$exchange_rate = CMulticurrency::getExchangeRate($merchant_default_currency,$currency_code);					
				}
		    }
			
			$data = CTips::data('label',$tip_type,$exchange_rate);
			
			$tips = 0; $transaction_type = '';			
			if ( $resp = CCart::getAttributesAll($cart_uuid,array('tips','transaction_type')) ){				
				$tips = isset($resp['tips'])?floatval($resp['tips']):0;
				$transaction_type = isset($resp['transaction_type'])?$resp['transaction_type']:'';				
			}
						
			$this->code = 1; $this->msg = "OK";
			$this->details = array(
			  'transaction_type'=>$transaction_type,
			  'tips'=>$tips,
			  'data'=>$data
			);
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actioncheckoutAddTips()
	{		
		try {
			
			$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
			$value = isset($this->data['value'])?floatval($this->data['value']):0;
			$currency_code = isset($this->data['currency_code'])?trim($this->data['currency_code']):'';		
			$manual_tip = isset($this->data['manual_tip'])?floatval($this->data['manual_tip']):0;		
		    $base_currency = Price_Formatter::$number_format['currency_code'];		

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
			$multicurrency_enabled = $multicurrency_enabled==1?true:false;		
			$exchange_rate = 1;		   
					    
			$merchant_id = CCart::getMerchantId($cart_uuid);
			$options_merchant = OptionsTools::find(['merchant_default_currency'],$merchant_id);	
			$merchant_default_currency = isset($options_merchant['merchant_default_currency'])?$options_merchant['merchant_default_currency']:'';
            $merchant_default_currency = !empty($merchant_default_currency)?$merchant_default_currency:$base_currency;

			$currency_code = !empty($currency_code)?$currency_code: (empty($merchant_default_currency)?$base_currency:$merchant_default_currency) ;		
			 // SET CURRENCY
			 if(!empty($currency_code) && $multicurrency_enabled){
				if($currency_code!=$merchant_default_currency){
					$exchange_rate = CMulticurrency::getExchangeRate($currency_code,$merchant_default_currency);
				}
			}
						
			if($manual_tip>0){				
				$value = ($value*$exchange_rate);				
			}			

			CCart::savedAttributes($cart_uuid,'tips',$value);	
			$this->code = 1; $this->msg = "OK";
			$this->details = array(
			  'tips'=>$value,			  
			);
			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionSavedPaymentList()
	{		
		try {
						
			$default_payment_uuid = '';
			$cart_uuid = Yii::app()->input->post('cart_uuid');
			
			$data_merchant = CCart::getMerchantForCredentials($cart_uuid);			
			$merchant_id = isset($data_merchant['merchant_id'])?$data_merchant['merchant_id']:0;
						
			if($data_merchant['merchant_type']==2){	
				$merchant_id=0;			
			}
			
			$payments_credentials = CPayments::getPaymentCredentialsPublic($merchant_id,'',$data_merchant['merchant_type']);
												
			$model = AR_client_payment_method::model()->find(
			'client_id=:client_id AND as_default=:as_default AND merchant_id=:merchant_id ', 
		    array(
		      ':client_id'=>Yii::app()->user->id,		      
		      ':as_default'=>1,
		      ':merchant_id'=>$merchant_id
		    )); 	
		    if($model){		    	
		    	$default_payment_uuid=$model->payment_uuid;
		    }
		    
			$data = CPayments::SavedPaymentList( Yii::app()->user->id , $data_merchant['merchant_type'] , 			
			$data_merchant['merchant_id'] );					
						
			$this->code = 1;
		    $this->msg = "ok";
		    $this->details = array(
		      'default_payment_uuid'=>$default_payment_uuid,
		      'data'=>$data,
			  'credentials'=>$payments_credentials
		    );		    
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actionPaymentList()
	{
		try {
			
		   $cart_uuid = Yii::app()->input->post('cart_uuid');
		   $merchant_id = CCart::getMerchantId($cart_uuid);		   
		   $data = CPayments::PaymentList($merchant_id,true);		   
		   
		   $merchants = CMerchantListingV1::getMerchant( $merchant_id );
		   $payments_credentials = CPayments::getPaymentCredentialsPublic($merchant_id,'',$merchants->merchant_type);			
		   
		   $this->code = 1;
		   $this->msg = "ok";
		   $this->details = array(		     
		     'data'=>$data,
			 'credentials'=>$payments_credentials
		   );		   
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiondeleteSavedPaymentMethod()
	{
		try {		   
		   $payment_uuid = isset($this->data['payment_uuid'])?$this->data['payment_uuid']:'';
		   $payment_code = isset($this->data['payment_code'])?$this->data['payment_code']:'';
		   CPayments::delete(Yii::app()->user->id,$payment_uuid);
		   $this->code = 1;
		   $this->msg = "ok";
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actionSetDefaultPayment()
	{			
		try {	
			$payment_uuid = Yii::app()->input->post('payment_uuid');
			$model = AR_client_payment_method::model()->find('client_id=:client_id AND payment_uuid=:payment_uuid', 
			array(
			  ':client_id'=>Yii::app()->user->id,
			  ':payment_uuid'=>$payment_uuid
			)); 		
			if($model){
				$model->as_default = 1;
				$model->save();
				$this->code = 1;
		    	$this->msg = t("Succesful");
			} else $this->msg = t("Record not found");			
		    
	    } catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		    
		}	
		
		$this->responseJson();
	}

	public function actionSavedPaymentProvider()
	{		
		try {
						
			$payment_code = isset($this->data['payment_code'])?$this->data['payment_code']:'';
			$merchant_id = isset($this->data['merchant_id'])?$this->data['merchant_id']:'';
			
			$payment = AR_payment_gateway::model()->find('payment_code=:payment_code', 
		    array(':payment_code'=>$payment_code)); 	
		    
		    if($payment){		    	
				$model = new AR_client_payment_method;
				$model->scenario = "insert";
				$model->client_id = Yii::app()->user->id;
				$model->payment_code = $payment_code;
				$model->as_default = intval(1);
				$model->attr1 = $payment?$payment->payment_name:'unknown';	
				$model->merchant_id = intval($merchant_id);
				if($model->save()){
					$this->code = 1;
		    		$this->msg = t("Succesful");
				} else $this->msg = CommonUtility::parseError($model->getErrors());
		    } else $this->msg[] = t("Payment provider not found");
			
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		    
		}				
		$this->responseJson();
	}

	public function actionsavedCards()
	{		
		try {
						
			$expiration_month='';$expiration_yr=''; $error_data = array(); $error = array();
			$card_name = isset($this->data['card_name'])?$this->data['card_name']:'';
			$credit_card_number = isset($this->data['credit_card_number'])?$this->data['credit_card_number']:'';
			$expiry_date = isset($this->data['expiry_date'])?$this->data['expiry_date']:'';
			$cvv = isset($this->data['cvv'])?$this->data['cvv']:'';
			$billing_address = isset($this->data['billing_address'])?$this->data['billing_address']:'';
			$payment_code = isset($this->data['payment_code'])?$this->data['payment_code']:'';
			$card_uuid = isset($this->data['card_uuid'])?$this->data['card_uuid']:'';
			$merchant_id = isset($this->data['merchant_id'])?$this->data['merchant_id']:'';
					
			if(empty($card_uuid)){
				$model=new AR_client_cc;
				$model->scenario='add';
			} else {
				$model = AR_client_cc::model()->find('client_id=:client_id AND card_uuid=:card_uuid', 				
			    array(
			      ':client_id'=>Yii::app()->user->id,
			      ':card_uuid'=>$card_uuid
			    )); 	
			    if(!$model){
			    	$this->msg[] = t("Record not found");
			    	$this->responseJson();
			    }
			    $model->scenario='update';
			}
						
			$model->client_id = Yii::app()->user->id;
			$model->payment_code = $payment_code;
			$model->card_name = $card_name;
			$model->credit_card_number = str_replace(" ","",$credit_card_number);
			$model->expiration = $expiry_date;
			$model->cvv = $cvv;
			$model->billing_address = $billing_address;
			$model->merchant_id = $merchant_id;

			if($model->save()){
	    		$this->code = 1;
		    	$this->msg = "OK";	
	    	} else $this->msg = CommonUtility::parseError( $model->getErrors());
			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}				
		$this->responseJson();
	}

	public function actionSimilarItems()
	{				
		try {		   		  
		   
		   $merchant_id = Yii::app()->input->post('merchant_id');	
		   $currency_code = Yii::app()->input->post('currency_code');	
		   $base_currency = Price_Formatter::$number_format['currency_code'];		

		   $multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		   $multicurrency_enabled = $multicurrency_enabled==1?true:false;		
		   $exchange_rate = 1;

		   $options_merchant = OptionsTools::find(['merchant_timezone','merchant_default_currency'],$merchant_id);				
		   $merchant_timezone = isset($options_merchant['merchant_timezone'])?$options_merchant['merchant_timezone']:'';		
		   $merchant_default_currency = isset($options_merchant['merchant_default_currency'])?$options_merchant['merchant_default_currency']:'';
		   $merchant_default_currency = !empty($merchant_default_currency)?$merchant_default_currency:$base_currency;
		   if(!empty($merchant_timezone)){
			   Yii::app()->timezone = $merchant_timezone;
		   }		
		   
		   $currency_code = !empty($currency_code)?$currency_code: (empty($merchant_default_currency)?$base_currency:$merchant_default_currency) ;		

		   // SET CURRENCY
			if(!empty($currency_code) && $multicurrency_enabled){
				Price_Formatter::init($currency_code);
				if($currency_code!=$merchant_default_currency){
					$exchange_rate = CMulticurrency::getExchangeRate($merchant_default_currency,$currency_code);					
				}
		   }

		   CMerchantMenu::setExchangeRate($exchange_rate);			   

		   $items_not_available = CMerchantMenu::getItemAvailability($merchant_id,date("w"),date("H:h:i"));	
		   $category_not_available = CMerchantMenu::getCategoryAvailability($merchant_id,date("w"),date("H:h:i"));		   
		   	   		   		   
		   $items = CMerchantMenu::getSimilarItems(intval($merchant_id),Yii::app()->language,100,'',$items_not_available,$category_not_available);			
		   $this->code = 1; $this->msg = "OK";
		   $this->details = array(		     
		     'data'=>$items
		   );		   
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		   		   
		}		
		$this->responseJson();
	}

	public function actionPlaceOrder()
	{
	
		$local_id = isset($this->data['local_id'])?$this->data['local_id']:'';
		$cart_uuid = isset($this->data['cart_uuid'])?trim($this->data['cart_uuid']):'';
		$payment_uuid = isset($this->data['payment_uuid'])?trim($this->data['payment_uuid']):'';
		$payment_change = isset($this->data['payment_change'])?floatval($this->data['payment_change']):0;
		$currency_code = isset($this->data['currency_code'])?trim($this->data['currency_code']):'';		
		$base_currency = Price_Formatter::$number_format['currency_code'];		
		
		$room_uuid = isset($this->data['room_uuid'])?trim($this->data['room_uuid']):'';	
		$table_uuid = isset($this->data['table_uuid'])?trim($this->data['table_uuid']):'';	
		$guest_number = isset($this->data['guest_number'])?intval($this->data['guest_number']):0;

		$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		$multicurrency_enabled = $multicurrency_enabled==1?true:false;	
		$enabled_checkout_currency = isset(Yii::app()->params['settings']['multicurrency_enabled_checkout_currency'])?Yii::app()->params['settings']['multicurrency_enabled_checkout_currency']:false;
		$enabled_force = $multicurrency_enabled==true? ($enabled_checkout_currency==1?true:false) :false;		

		$payload = array(
			'items','merchant_info','service_fee',
			'delivery_fee','packaging','tax','tips','checkout','discount','distance',
			'summary','total','card_fee','points','points_discount'
		 );		
		 
		 
		 $unit = Yii::app()->params['settings']['home_search_unit_type']; 
		 $distance = 0; 	    
		 $error = array(); 
		 $minimum_order = 0; 
		 $maximum_order=0;
		 $merchant_info = array(); 
		 $delivery_fee = 0; 
		 $distance_covered=0;
		 $merchant_lat = ''; 
		 $merchant_lng=''; 
		 $out_of_range = false;
		 $address_component = array();
		 $commission = 0;
		 $commission_based = ''; 
		 $merchant_id = 0; 
		 $merchant_earning = 0; 
		 $total_discount = 0; 
		 $service_fee = 0; 
		 $delivery_fee = 0; 
		 $packagin_fee = 0; 
		 $tip = 0;
		 $total_tax = 0;
		 $tax = 0;
		 $promo_details = array();
		 $summary = array();
		 $offer_total = 0;
		 $tax_type = '';
		 $tax_condition = '';
		 $small_order_fee = 0;
		 $self_delivery = false;	
		 $card_fee = 0;			
		 $exchange_rate = 1;		
		 $exchange_rate_use_currency_to_admin = 1;
		 $exchange_rate_merchant_to_admin = 1; 
		 $exchange_rate_base_customer = 1;
		 $exchange_rate_admin_to_merchant = 1;		
		 $payment_exchange_rate = 1;
		 $points_to_earn = 0; 
		 $points_label = ''; 
		 $points_earned=0;
		 $sub_total_without_cnd = 0;
		 $booking_enabled = false;
		 
		 	/*CHECK IF MERCHANT IS OPEN*/
		try {
			$merchant_id = CCart::getMerchantId($cart_uuid);		
			
			// CHECK IF MERCHANT HAS DIFFERENT TIMEZONE
			$options_merchant = OptionsTools::find(['merchant_timezone','booking_enabled'],$merchant_id);
			$merchant_timezone = isset($options_merchant['merchant_timezone'])?$options_merchant['merchant_timezone']:'';
			$booking_enabled = isset($options_merchant['booking_enabled'])?$options_merchant['booking_enabled']:'';
			$booking_enabled = $booking_enabled==1?true:false;			

			if(!empty($merchant_timezone)){
				Yii::app()->timezone = $merchant_timezone;
			}

			$date = date("Y-m-d");
			$time_now = date("H:i");

            $attributes_data = CCart::getAttributesAll($cart_uuid,['whento_deliver','delivery_time','delivery_date']);             
						
            $whento_deliver = isset($attributes_data['whento_deliver'])?$attributes_data['whento_deliver']:'';
            if($whento_deliver=="schedule"){
                $date = isset($attributes_data['delivery_date'])?$attributes_data['delivery_date']:$date;
                $att_delivery_time = isset($attributes_data['delivery_time'])?json_decode($attributes_data['delivery_time'],true):false;
                if(is_array($att_delivery_time) && count($att_delivery_time)>=1){                    
                    $time_now  = isset($att_delivery_time['end_time'])?$att_delivery_time['end_time']:$time_now;
                }                
            }

						
			$datetime_to = date("Y-m-d g:i:s a",strtotime("$date $time_now"));
			CMerchantListingV1::checkCurrentTime( date("Y-m-d g:i:s a") , $datetime_to);
			            
			$resp = CMerchantListingV1::checkStoreOpen($merchant_id,$date,$time_now);			
			if($resp['merchant_open_status']<=0){
				$this->msg[] = t("This store is close right now, but you can schedulean order later.");
				$this->responseJson();
			}					
						
			CMerchantListingV1::storeAvailableByID($merchant_id);
			
									
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		    
		    $this->responseJson();
		}	

		try {
			
			if($credentials = CMerchants::MapsConfig(Yii::app()->merchant->id)){
				MapSdk::$map_provider = $credentials['provider'];
				MapSdk::setKeys(array(
				  'google.maps'=>$credentials['key'],
				  'mapbox'=>$credentials['key'],
				));				 
			}

			$merchant_id = CCart::getMerchantId($cart_uuid);
		 	$options_merchant = OptionsTools::find(['merchant_timezone','merchant_default_currency'],$merchant_id);						
		    $merchant_default_currency = isset($options_merchant['merchant_default_currency'])?$options_merchant['merchant_default_currency']:'';
			$merchant_default_currency = !empty($merchant_default_currency)?$merchant_default_currency:$base_currency;			
			$currency_code = !empty($currency_code)?$currency_code: (empty($merchant_default_currency)?$base_currency:$merchant_default_currency) ;

			$points_enabled = isset(Yii::app()->params['settings']['points_enabled'])?Yii::app()->params['settings']['points_enabled']:false;
		    $points_enabled = $points_enabled==1?true:false;
		    $points_earning_rule = isset(Yii::app()->params['settings']['points_earning_rule'])?Yii::app()->params['settings']['points_earning_rule']:'sub_total';									
			$points_earning_points = isset(Yii::app()->params['settings']['points_earning_points'])?Yii::app()->params['settings']['points_earning_points']:0;	
			$points_minimum_purchase = isset(Yii::app()->params['settings']['points_minimum_purchase'])?Yii::app()->params['settings']['points_minimum_purchase']:0;	
            $points_maximum_purchase = isset(Yii::app()->params['settings']['points_maximum_purchase'])?Yii::app()->params['settings']['points_maximum_purchase']:0;
									
			CCart::setExchangeRate($exchange_rate);		
			CCart::setPointsRate($points_enabled,$points_earning_rule,$points_earning_points,$points_minimum_purchase,$points_maximum_purchase);
			
			if($multicurrency_enabled){
				if($merchant_default_currency!=$currency_code){
					$exchange_rate_base_customer = CMulticurrency::getExchangeRate($merchant_default_currency,$currency_code);
					$payment_exchange_rate = CMulticurrency::getExchangeRate($currency_code,$merchant_default_currency);
				}
				if($merchant_default_currency!=$base_currency){
					$exchange_rate_merchant_to_admin = CMulticurrency::getExchangeRate($merchant_default_currency,$base_currency);
					$exchange_rate_admin_to_merchant = CMulticurrency::getExchangeRate($base_currency,$merchant_default_currency);
				}
				if($base_currency!=$merchant_default_currency){					
					$exchange_rate_use_currency_to_admin = CMulticurrency::getExchangeRate($merchant_default_currency,$base_currency);
				}						
			} else {
				$merchant_default_currency = $base_currency;
				$currency_code = $base_currency;
			}
						
			CCart::setAdminExchangeRate($exchange_rate_use_currency_to_admin);
			
			require_once 'get-cart.php';

			// GET CLIENT ADDRESS AND SAVE LOCATION NAME / DELIVERY OPTIONS AND INSTRUCSTIONS
			$client_address = AR_client_address::model()->find('place_id=:place_id AND client_id=:client_id', 
		    array(':place_id'=>$local_id,'client_id'=>Yii::app()->user->id)); 
			if($client_address){
				$address_component['location_name']	 = $client_address->location_name;
				$address_component['delivery_options']	 = $client_address->delivery_options;
				$address_component['delivery_instructions']	 = $client_address->delivery_instructions;
				$address_component['address_label']	 = $client_address->address_label;
			}
			
			$include_utensils = isset($this->data['include_utensils'])?$this->data['include_utensils']:false;
		    $include_utensils = $include_utensils==1?true:false;			
		    CCart::savedAttributes($cart_uuid,'include_utensils',$include_utensils);
						
			if(is_array($error) && count($error)>=1){				
				$this->msg = $error;
			} else {					
												
				$merchant_type = $data['merchant']['merchant_type'];
				$commision_type = $data['merchant']['commision_type'];				
				$merchant_commission = $data['merchant']['commission'];				
												
				$sub_total_based  = CCart::getSubTotal_TobeCommission();				
				$tax_total =  CCart::getTotalTax();					
				$resp_comm = CCommission::getCommissionValueNew([
					'merchant_id'=>$merchant_id,
					'transaction_type'=>$transaction_type,
					'merchant_type'=>$merchant_type,
					'commision_type'=>$commision_type,
					'merchant_commission'=>$merchant_commission,
					'sub_total'=>$sub_total_based,
					'sub_total_without_cnd'=>$sub_total_without_cnd,
					'total'=>$total,
					'service_fee'=>$service_fee,
					'delivery_fee'=>$delivery_fee,
					'tax_settings'=>$tax_settings,
					'tax_total'=>$tax_total,
					'self_delivery'=>$self_delivery,					
				]);				
				if($resp_comm){					
					$commission_based = $resp_comm['commission_based'];
					$commission = $resp_comm['commission'];
					$merchant_earning = $resp_comm['merchant_earning'];
					$merchant_commission = $resp_comm['commission_value'];
				}				
				
				$atts = CCart::getAttributesAll($cart_uuid,array('whento_deliver',
				  'promo','promo_type','promo_id','tips','delivery_date','delivery_time'
				));						
				
				$payments = CPayments::getPaymentMethod( $payment_uuid, Yii::app()->user->id );
				$sub_total_less_discount  = CCart::getSubTotal_lessDiscount();				
												
				if(is_array($summary) && count($summary)>=1){	
					foreach ($summary as $summary_item) {						
						switch ($summary_item['type']) {
							case "voucher":
								$total_discount = CCart::cleanNumber($summary_item['raw']);
								break;
						
							case "offers":	
							    $total_discount = CCart::cleanNumber($summary_item['raw']);
							    $offer_total = $total_discount;
							    $total_discount = floatval($total_discount)+ floatval($total_discount);
								break;
								
							case "service_fee":
								$service_fee = CCart::cleanNumber($summary_item['raw']);
								break;
								
							case "delivery_fee":
								$delivery_fee = CCart::cleanNumber($summary_item['raw']);
								break;	
							
							case "packaging_fee":
								$packagin_fee = CCart::cleanNumber($summary_item['raw']);
								break;			
								
							case "tip":
								$tip = CCart::cleanNumber($summary_item['raw']);
								break;				
								
							case "tax":
								$total_tax+= CCart::cleanNumber($summary_item['raw']);
								break;		
								
							case "points_discount":								
								$total_discount += CCart::cleanNumber($summary_item['raw']);
								$points_earned = CCart::cleanNumber($summary_item['raw']);
								break;					
									
							default:
								break;
						}
					}				
				}
				
				if($tax_enabled){					
					$tax_type = CCart::getTaxType();									
					$tax_condition = CCart::getTaxCondition();					
					if($tax_type=="standard" || $tax_type=="euro"){			
						if(is_array($tax_condition) && count($tax_condition)>=1){
							foreach ($tax_condition as $tax_item_cond) {
								$tax = isset($tax_item_cond['tax_rate'])?$tax_item_cond['tax_rate']:0;
							}
						}
					}									
				}			

				if($multicurrency_enabled){
				   $payment_change = $currency_code==$merchant_default_currency ? $payment_change : ($payment_change*$payment_exchange_rate);
				}
																	
				$model = new AR_ordernew;
				$model->scenario = $transaction_type;
				$model->order_uuid = CommonUtility::generateUIID();
				$model->merchant_id = intval($merchant_id);	
				$model->client_id = intval(Yii::app()->user->id);
				$model->service_code = $transaction_type;
				$model->payment_code = isset($payments['payment_code'])?$payments['payment_code']:'';									
				$model->payment_change = $payment_change;				
				$model->validate_payment_change = true;	
				$model->total_discount = floatval($total_discount);
				$model->points = floatval($points_earned);
				$model->sub_total = floatval($sub_total);
				$model->sub_total_less_discount = floatval($sub_total_less_discount);
				$model->service_fee = floatval($service_fee);
				$model->small_order_fee = floatval($small_order_fee);
				$model->delivery_fee = floatval($delivery_fee);
				$model->packaging_fee = floatval($packagin_fee);
				$model->card_fee = floatval($card_fee);
				$model->tax_type = $tax_type;
				$model->tax = floatval($tax);
				$model->tax_total = floatval($total_tax);				
				$model->courier_tip = floatval($tip);				
				$model->total = floatval($total);
				$model->total_original = floatval($total);		
								
				$model->booking_enabled = $booking_enabled;
				$model->room_id = trim($room_uuid);
			    $model->table_id = trim($table_uuid);
				$model->guest_number = $guest_number;				
				
				if(is_array($promo_details) && count($promo_details)>=1){
					if($promo_details['promo_type']=="voucher"){
						$model->promo_code = $promo_details['voucher_name'];
						$model->promo_total = $promo_details['less_amount'];
					} elseif ( $promo_details['promo_type']=="offers" ){						
						$model->offer_discount = $promo_details['less_amount'];
						$model->offer_total = floatval($offer_total);
					}
				}
				
				$model->whento_deliver = isset($atts['whento_deliver'])?$atts['whento_deliver']:'';
				if($model->whento_deliver=="now"){
					$model->delivery_date = CommonUtility::dateNow();
				} else {
					$model->delivery_date = isset($atts['delivery_date'])?$atts['delivery_date']:'';
					$model->delivery_time = isset($atts['delivery_time'])?CCheckout::jsonTimeToSingleTime($atts['delivery_time']):'';
					$model->delivery_time_end = isset($atts['delivery_time'])?CCheckout::jsonTimeToSingleTime($atts['delivery_time'],'end_time'):'';
				}
												
				$model->commission_type = $commision_type;
				$model->commission_value = $merchant_commission;
				$model->commission_based = $commission_based;
				$model->commission = floatval($commission);
				$model->commission_original = floatval($commission);
				$model->merchant_earning = floatval($merchant_earning);	
				$model->merchant_earning_original = floatval($merchant_earning);	
				$model->formatted_address = isset($address_component['formatted_address'])?$address_component['formatted_address']:'';
				
				$metas = CCart::getAttributesAll($cart_uuid,
				  array('promo','promo_type','promo_id','tips',
				  'cash_change','customer_name','contact_number','contact_email','include_utensils','point_discount'
				  )
				);

				$metas['payment_change'] = floatval($payment_change);
				$metas['self_delivery'] = $self_delivery==true?1:0;	
				$metas['points_to_earn'] = floatval($points_to_earn);			
				
				if($transaction_type=="dinein" && $booking_enabled){
					$metas['guest_number'] = $guest_number;
					try {									
						$model_room = CBooking::getRoom($room_uuid); 
						$metas['room_id'] = $model_room->room_id;
					} catch (Exception $e) {											
					}
	
					try {			
						$model_table = CBooking::getTable($table_uuid); 					
						$metas['table_id'] = $model_table->table_id;
					} catch (Exception $e) {					
					}
				}			
				
				/*LINE ITEMS*/
				$model->items = $data['items'];				
				$model->meta = $metas;
				$model->address_component = $address_component;
				$model->cart_uuid = $cart_uuid;

				$model->base_currency_code = $merchant_default_currency;
				$model->use_currency_code = $currency_code;		
				$model->admin_base_currency = $base_currency;
				
				$model->exchange_rate = floatval($exchange_rate_base_customer);
				$model->exchange_rate_use_currency_to_admin = floatval($exchange_rate_use_currency_to_admin);
				$model->exchange_rate_merchant_to_admin = floatval($exchange_rate_merchant_to_admin);												
				$model->exchange_rate_admin_to_merchant = floatval($exchange_rate_admin_to_merchant);				
				
				$model->tax_use = $tax_settings;				
				$model->tax_for_delivery = $tax_delivery;				
				$model->payment_uuid  = $payment_uuid;	
				
				$model->request_from = "mobile";
											
				//dump($model);die();
				if($model->save()){
											
					$redirect = Yii::app()->createAbsoluteUrl("orders/index",array(
					   'order_uuid'=>$model->order_uuid
					));					
									
					/*EXECUTE MODULES*/							
					$payment_instructions = Yii::app()->getModule($model->payment_code)->paymentInstructions();					
					if($payment_instructions['method']=="offline"){
						Yii::app()->getModule($model->payment_code)->savedTransaction($model);							
					}									
					
					$order_bw = OptionsTools::find(array('bwusit'));
					$order_bw = isset($order_bw['bwusit'])?$order_bw['bwusit']:0;

					$use_currency_code = $model->use_currency_code;					
		            $total_exchange = floatval(Price_Formatter::convertToRaw( ($total*$exchange_rate_base_customer) ));											
					if($enabled_force){
						if($force_result = CMulticurrency::getForceCheckoutCurrency($model->payment_code,$use_currency_code)){						   						   
						   $use_currency_code = $force_result['to_currency'];
						   $total_exchange = Price_Formatter::convertToRaw($total_exchange*$force_result['exchange_rate'],2);
						}
					}
																		
					$this->code = 1;
					$this->msg = t("Your Order has been place");
					$this->details = array(  
					  'order_uuid' => $model->order_uuid,
					  'cart_uuid'=>$cart_uuid,
					  'redirect'=>$redirect,
					  'payment_code'=>$model->payment_code,
					  'payment_uuid'=>$payment_uuid,
					  'payment_instructions'=>$payment_instructions,		
					  'order_bw'=>$order_bw,
					  'total'=>floatval($model->total),
					  'currency'=>$model->use_currency_code,
					  'url'=>CommonUtility::getHomebaseUrl(),
					  'force_payment_data'=>[		
						'enabled_force'=>$enabled_force,
						'use_currency_code'=>$use_currency_code,
						'total_exchange'=>$total_exchange,
					 ],
					);								
				} else {					
					if ( $error = CommonUtility::parseError( $model->getErrors()) ){				
						$this->msg = $error;						
					} else $this->msg[] = array('invalid error');
				}				
			}		
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		    
		}					
		$this->responseJson();
	}	
	
	public function actiongetOrder()
	{		
		try {
			
		   $order_uuid = Yii::app()->input->post('order_uuid');
		   $merchant_id = COrders::getMerchantId($order_uuid);
		   $merchant_info = COrders::getMerchant($merchant_id,Yii::app()->language);	
		   
		   COrders::getContent($order_uuid);
		   $items = COrders::getItemsOnly();		   
		   $meta  = COrders::orderMeta();
		   $order_id = COrders::getOrderID();
		   $items_count = COrders::itemCount($order_id);
		   $progress = CTrackingOrder::getProgress($order_uuid , date("Y-m-d g:i:s a") );		   
		   $order_info  = COrders::orderInfo(Yii::app()->language,date("Y-m-d"));
		   $order_info  = isset($order_info['order_info'])?$order_info['order_info']:'';
		   $order_type = isset($order_info['order_type'])?$order_info['order_type']:'';    			   
		   
		   $subtotal = COrders::getSubTotal();
		   $subtotal = isset($subtotal['sub_total'])?$subtotal['sub_total']:0;
		   $subtotal = Price_Formatter::formatNumber(floatval($subtotal));
		   $order_info['sub_total'] = $subtotal;
		   
		   $instructions = CTrackingOrder::getInstructions($merchant_id,$order_type);
		   		   
		   $this->code = 1;
		   $this->msg = "Ok";
		   $this->details = array(
		     'merchant_info'=>$merchant_info,
		     'order_info'=>$order_info,
		     'items_count'=>$items_count,		     
		     'items'=>$items,
		     'meta'=>$meta,		    
		     'progress'=>$progress,
		     'instructions'=>$instructions,
		     'maps_config'=>CMaps::config()
		   );		   
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());
		}	
		$this->responseJson();
	}

	public function actiongetRealtime()
	{
		$getevent = Yii::app()->input->post('getevent');
		$realtime = AR_admin_meta::getMeta(array('realtime_app_enabled','realtime_provider',
		'webpush_app_enabled','webpush_provider','pusher_key','pusher_cluster'));						
		$realtime_app_enabled = isset($realtime['realtime_app_enabled'])?$realtime['realtime_app_enabled']['meta_value']:'';
		$realtime_provider = isset($realtime['realtime_provider'])?$realtime['realtime_provider']['meta_value']:'';
		$pusher_key = isset($realtime['pusher_key'])?$realtime['pusher_key']['meta_value']:'';
		$pusher_cluster = isset($realtime['pusher_cluster'])?$realtime['pusher_cluster']['meta_value']:'';

		if($realtime_app_enabled==1){
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'realtime_app_enabled'=>$realtime_app_enabled,
				'realtime_provider'=>$realtime_provider,
				'pusher_key'=>$pusher_key,
				'pusher_cluster'=>$pusher_cluster,
				'channel'=>Yii::app()->user->client_uuid,
				'event'=>$getevent=="tracking"?Yii::app()->params->realtime['event_tracking_order']:Yii::app()->params->realtime['notification_event']
			];
		} else $this->msg = t("realtime not enabled");
		$this->responseJson();
	}	

	public function actionuploadReview()
	{
		$upload_uuid = CommonUtility::generateUIID();
		$merchant_id = Yii::app()->input->post('merchant_id');
		$allowed_extension = explode(",",  Yii::app()->params['upload_type']);
		$maxsize = (integer) Yii::app()->params['upload_size'] ;
							
		if (!empty($_FILES)) {
			
			$title = $_FILES['file']['name'];   
			$size = (integer)$_FILES['file']['size'];   
			$filetype = $_FILES['file']['type'];   								
			
			if(isset($_FILES['file']['name'])){
			   $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
			} else $extension = strtolower(substr($title,-3,3));
			
			if(!in_array($extension,$allowed_extension)){			
				$this->msg = t("Invalid file extension");
				$this->responseJson();
			}
			if($size>$maxsize){
				$this->msg = t("Invalid file size");
				$this->responseJson();
			}
			
			$upload_path = "upload/reviews";
			$tempFile = $_FILES['file']['tmp_name'];   								
			$upload_uuid = CommonUtility::createUUID("{{media_files}}",'upload_uuid');
			$filename = $upload_uuid.".$extension";						
			$path = CommonUtility::uploadDestination($upload_path)."/".$filename;						
			
			$image_set_width = isset(Yii::app()->params['settings']['review_image_resize_width']) ? intval(Yii::app()->params['settings']['review_image_resize_width']) : 0;
			$image_set_width = $image_set_width<=0?300:$image_set_width;
						
			$image_driver = !empty(Yii::app()->params['settings']['image_driver'])?Yii::app()->params['settings']['image_driver']:Yii::app()->params->image['driver'];			
			$manager = new ImageManager(array('driver' => $image_driver ));								
			$image = $manager->make($tempFile);
			$image_width = $manager->make($tempFile)->width();
						
			if($image_width>$image_set_width){
				$image->resize(null, $image_set_width, function ($constraint) {
				    $constraint->aspectRatio();
				});
				$image->save($path);
			} else {
				$image->save($path,60);
			}				
			
			//move_uploaded_file($tempFile,$path);
			
			$media = new AR_media;		
			$media->merchant_id = intval($merchant_id);
			$media->title = $title;			
			$media->path = $upload_path;
			$media->filename = $filename;
			$media->size = $size;
			$media->media_type = $filetype;						
			$media->meta_name = AttributesTools::metaReview();		
			$media->upload_uuid = $upload_uuid;
			$media->save();
			
			$this->code = 1; $this->msg = "OK";			
			$this->details = array(			   			   
			   'url_image'=>CMedia::getImage($filename,$upload_path),
			   'filename'=>$media->filename,
			   'id'=>$upload_uuid			   
			);			
			
		} else $this->msg = t("Invalid file");
		$this->responseJson();		
	}

	public function actionaddReview()
	{		
		try {
			
			$order_uuid = isset($this->data['order_uuid'])?trim($this->data['order_uuid']):'';
			$order = COrders::get($order_uuid);
						
			$find = AR_review::model()->find('merchant_id=:merchant_id AND client_id=:client_id
			AND order_id=:order_id', 
		    array( 
		      ':merchant_id'=>intval($order->merchant_id),
		      ':client_id'=>intval(Yii::app()->user->id),
		      ':order_id'=>intval($order->order_id)
		    )); 	
			
		    if(!$find){
				$model = new AR_review;	
				$model->merchant_id  = intval($order->merchant_id);
				$model->order_id  = intval($order->order_id);
				$model->client_id = intval(Yii::app()->user->id) ;
				$model->review  = isset($this->data['review_content'])?$this->data['review_content']:'';		
				$model->rating  = isset($this->data['rating_value'])?(integer)$this->data['rating_value']:0;
				$model->date_created = CommonUtility::dateNow();
				$model->ip_address = CommonUtility::userIp();
				$model->as_anonymous = isset($this->data['as_anonymous'])?(integer)$this->data['as_anonymous']:0;		
				$model->scenario = 'insert';
				if ($model->save()){
					$this->code = 1; $this->msg = t("Review has been added. Thank you.");
					CReviews::insertMeta($model->id,'tags_like',$this->data['tags_like']);
					CReviews::insertMeta($model->id,'tags_not_like',$this->data['tags_not_like']);
					CReviews::insertMetaImages($model->id,'upload_images',$this->data['upload_images']);
				} else {												
					if ( $error = CommonUtility::parseError( $model->getErrors()) ){
						$this->msg = $error;
					} else $this->msg[] = array('invalid error');				
				}				
		    }else $this->msg[] = t("You already added review for this order");
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}	
		$this->responseJson();
	}	

	public function actionorderHistory()
	{	     
	     try {
	     	  	     	  
	     	  $page = Yii::app()->input->post('page');
			  $q = Yii::app()->input->post('q');	
			  $order_tab = Yii::app()->input->post('order_tab');	

			  if($page>0){
				$page = $page-1;
			  }	     	  		  
	     	     	  
	     	  $offset = 0; $show_next_page = false;
	     	  $limit = Yii::app()->params->list_limit;			  	     	  
			  $total_rows = COrders::orderHistoryTotal(Yii::app()->user->id);    	
	     	  	          
	          $pages = new CPagination($total_rows);
			  $pages->pageSize = $limit;
			  $pages->setCurrentPage($page);
			  $offset = $pages->getOffset();	
			  $page_count = $pages->getPageCount();
			  						
			  if($page_count > ($page+1) ){
				  $show_next_page = true;
			  }   

			  $status = [];
			  if($order_tab=="active"){
				 $status = AOrderSettings::getTabsGroupStatus(['new_order','order_processing','order_ready']);							 			 
			  } else if ( $order_tab=='past_order'){
				 $status = AOrderSettings::getTabsGroupStatus(['completed_today']);				 				 				 
			  } else if ( $order_tab=='cancel_order'){
				$status = AOrderSettings::getStatus(array('status_cancel_order'));				
			  }
			  					  
			  $data = COrders::getOrderHistory(Yii::app()->user->id,$q,$offset,$limit,Yii::app()->language,0,$status);					  
			  $payment_status = COrders::paymentStatusList2(Yii::app()->language,'payment');	
			  $payment_list = AttributesTools::PaymentProvider();	  						  
			  	          	 	                   	       
	          $this->code = 1;
	          $this->msg = "Ok";	        
	          $this->details = array(
			     'show_next_page'=>$show_next_page,
			     'page'=>intval($page)+1,
			     'data'=>$data,
				 'payment_status'=>$payment_status,
				 'payment_list'=>$payment_list
			  );			  
	     } catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		    			
		 }	
		 $this->responseJson();
	}

	public function actionorderdetails()
	{
		try {		 	
			 			
			 $refund_transaction = array(); $order_id = 0;
			 $summary = array(); $progress = array(); $order_status = array(); 
			 $allowed_to_cancel = false;
			 $pdf_link = ''; $delivery_timeline=array();
			 $order_delivery_status = array(); $merchant_info=array();
			 $order = array(); $items = array();

			 $label = array(		       
				'your_order_from'=>t("Your order from"),
				'summary'=>t("Summary"),	
				'track'=>t("Track"),
				'buy_again'=>t("Buy again"),
			 );

		     $order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';
			 $payload = isset($this->data['payload'])?$this->data['payload']:array();	
			 
			 $exchange_rate = 1;
			 $model_order = COrders::get($order_uuid);			 			 
			 if($model_order->base_currency_code!=$model_order->use_currency_code){
				$exchange_rate = $model_order->exchange_rate>0?$model_order->exchange_rate:1;
				Price_Formatter::init($model_order->use_currency_code);
			 } else {
				Price_Formatter::init($model_order->use_currency_code);
			 }			 
			 COrders::setExchangeRate($exchange_rate);
		     
		     COrders::getContent($order_uuid,Yii::app()->language);
		     $merchant_id = COrders::getMerchantId($order_uuid);
			 $order_id = COrders::getOrderID();	
			 
			 if(in_array('merchant_info',$payload)){
				$merchant_info = COrders::getMerchant($merchant_id,Yii::app()->language);
			 }		     
			 if(in_array('items',$payload)){
		        $items = COrders::getItems();		    
			 } 

			 if(in_array('summary',$payload)){
		        $summary = COrders::getSummary();	
			 }

			 if(in_array('order_info',$payload)){
		        $order = COrders::orderInfo();	
			 }	   
			 
			 if(in_array('progress',$payload)){
			    $progress = CTrackingOrder::getProgress($order_uuid , date("Y-m-d g:i:s a") );	
			 }
		     		     
			 if(in_array('refund_transaction',$payload)){
				try {			     	     
					$refund_transaction = COrders::getPaymentTransactionList(Yii::app()->user->id,$order_id,array(
					'paid'
					),array(
					'refund',
					'partial_refund'
					));					     
				} catch (Exception $e) {
					//echo $e->getMessage(); die();
				}
			 }		    		   

			 if(in_array('status_allowed_cancelled',$payload)){
				$status_allowed_cancelled = COrders::getStatusAllowedToCancel();		     
				$order_status = $order['order_info']['status'];				
				if(in_array($order_status,(array)$status_allowed_cancelled)){
					$allowed_to_cancel = true;
				}			 
			 }
			 
			 if(in_array('pdf_link',$payload)){
			    $pdf_link = Yii::app()->createAbsoluteUrl("/print/pdf",array('order_uuid'=>$order['order_info']['order_uuid']));
			 }
			 
			 if(in_array('delivery_timeline',$payload)){
				$delivery_timeline = AOrders::getOrderHistory($order_uuid);				
			 }

			 if(in_array('order_delivery_status',$payload)){
			    $order_delivery_status = AttributesTools::getOrderStatus(Yii::app()->language,'order_status');
			 }

			 $allowed_to_review = false;
			if(in_array('allowed_to_review',$payload)){
				$find = AR_review::model()->find('merchant_id=:merchant_id AND client_id=:client_id
					AND order_id=:order_id', 
					array( 
					':merchant_id'=>intval($order['order_info']['merchant_id']),
					':client_id'=>intval(Yii::app()->user->id),
					':order_id'=>intval($order_id)
				)); 				

				if(!$find){
					$status_allowed_review = AOrderSettings::getStatus(array('status_delivered','status_completed'));			 				
					if(in_array($order_status,(array)$status_allowed_review)){
						$allowed_to_review = true;
					}			 
				}			 
			}

			$estimation = [];
			if(in_array('estimation',$payload)){				
				try {
					$filter = [
						'merchant_id'=>$merchant_id,
						'shipping_type'=>"standard"
					];
					$estimation  = CMerchantListingV1::estimationMerchant2($filter);
				} catch (Exception $e) {
					//echo $e->getMessage(); die();
				}
		    }

			$charge_type = '';
			if(in_array('charge_type',$payload)){
				$options_data = OptionsTools::find(array('merchant_delivery_charges_type'),$merchant_id);		
				$charge_type = isset($options_data['merchant_delivery_charges_type'])?$options_data['merchant_delivery_charges_type']:'fixed';
			}

			$order_table_data = [];
			$order_type = $order['order_info']['order_type'];
			if($order_type=="dinein"){
			   $order_table_data = COrders::orderMeta(['table_id','room_id','guest_number']);	
			   $room_id = isset($order_table_data['room_id'])?$order_table_data['room_id']:0;							
			   $table_id = isset($order_table_data['table_id'])?$order_table_data['table_id']:0;							
			   try {
				   $table_info = CBooking::getTableByID($table_id);
				   $order_table_data['table_name'] = $table_info->table_name;
			   } catch (Exception $e) {				   
			   }				
			   try {
				   $room_info = CBooking::getRoomByID($room_id);					
				   $order_table_data['room_name'] = $room_info->room_name;
			   } catch (Exception $e) {				   
			   }
			}			 
		     
		     $data = array(
		       'merchant'=>$merchant_info,
		       'order'=>$order,
		       'items'=>$items,
		       'summary'=>$summary,	
		       'label'=>$label,
		       'refund_transaction'=>$refund_transaction,
			   'progress'=>$progress,
			   'allowed_to_cancel'=>$allowed_to_cancel,
			   'allowed_to_review'=>$allowed_to_review,
			   'pdf_link'=>$pdf_link,
			   'delivery_timeline'=>$delivery_timeline,
			   'order_delivery_status'=>$order_delivery_status,
			   'estimation'=>$estimation,
			   'charge_type'=>$charge_type,
			   'order_table_data'=>$order_table_data
		     );		     
		    		     		     
		     $this->code = 1; $this->msg = "ok";
		     $this->details = array(			 		      
		       'data'=>$data,		      
		     );
		     		     		     		     		
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		   
		}	
		$this->responseJson();
	}	

	public function actionorderBuyAgain()
	{	
		try {
		    $current_cart_uuid = isset($this->data['cart_uuid'])?trim($this->data['cart_uuid']):'';
		    CCart::clear($current_cart_uuid);
		} catch (Exception $e) {
			//
		}
		
		try {
			
		   $order_uuid = isset($this->data['order_uuid'])?trim($this->data['order_uuid']):'';		   		  
		   
		   COrders::$buy_again = true;
		   COrders::getContent($order_uuid,Yii::app()->language);
		   $merchant_id = COrders::getMerchantId($order_uuid);
		   $items = COrders::getItems();
		   
		   $merchant_info = COrders::getMerchant($merchant_id,Yii::app()->language);
		   $restaurant_url = isset($merchant_info['restaurant_url'])?$merchant_info['restaurant_url']:'';
		   $restaurant_slug = isset($merchant_info['restaurant_slug'])?$merchant_info['restaurant_slug']:'';
		   	 
		   $cart_uuid = CCart::addOrderToCart($merchant_id,$items);
		   
		   $transaction_type = COrders::orderTransaction($order_uuid,$merchant_id,Yii::app()->language);
		   CCart::savedAttributes($cart_uuid,Yii::app()->params->local_transtype,$transaction_type);	
		   CCart::savedAttributes($cart_uuid,'whento_deliver','now');
		   CommonUtility::WriteCookie( "cart_uuid_local" ,$cart_uuid);	
		   
		   $this->code = 1 ; $this->msg = "OK";			
	       $this->details = array(
	         'cart_uuid'=>$cart_uuid,
	         'restaurant_url'=>$restaurant_url,
			 'restaurant_slug'=>$restaurant_slug
	       );			   
		   
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		 		    
		}	
		$this->responseJson();
	}

	public function actioncancelOrderStatus()
	{
		try {

			$order_uuid = Yii::app()->input->post('order_uuid');
			$resp = COrders::getCancelStatus($order_uuid);					
			$this->code = 1;
		    $this->msg = "OK";
		    $this->details = $resp;
			
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		    
		}	
		$this->responseJson();
	}	

	public function actionapplycancelorder()
	{
		try {			
			$order_uuid = Yii::app()->input->post('order_uuid');
			$order = COrders::get($order_uuid);
			$resp = COrders::getCancelStatus($order_uuid);			
			
			$cancel = AR_admin_meta::getValue('status_cancel_order');			
			$cancel_status = isset($cancel['meta_value'])?$cancel['meta_value']:'cancelled';
			
			$reason = "Customer cancel this order";
			
			if($resp['payment_type']=="online"){
				if($resp['cancel_status']==1 && $resp['refund_status']=="full_refund"){
					// FULL REFUND
					$order->scenario = "cancel_order";
					if($order->status==$cancel_status){
						$this->msg = t("This order has already been cancelled");
				        $this->responseJson();
					}					
					$order->status = $cancel_status;					
			        $order->remarks = $reason;
					if($order->save()){
					   $this->code = 1;
					   $this->msg = t("Your order is now cancel. your refund is on its way.");			   
					   if(!empty($reason)){
					   	  COrders::savedMeta($order->order_id,'rejetion_reason',$reason);
					   }			   
					} else $this->msg = CommonUtility::parseError( $order->getErrors());
					
				} elseif ( $resp['cancel_status']==1 && $resp['refund_status']=="partial_refund" ){
					///PARTIAL REFUND
					$refund_amount = floatval($resp['refund_amount']);
					$order->scenario = "customer_cancel_partial_refund";
					
					$model = new AR_ordernew_summary_transaction;
					$model->scenario = "refund";
					$model->order = $order;
					$model->order_id = $order->order_id;
					$model->transaction_description = "Refund";
					$model->transaction_amount = floatval($refund_amount);
					
					if($model->save()){					
						$order->status = $cancel_status;
						$order->remarks = $reason;
						if($order->save()){
						   $this->code = 1;
						   $this->msg = t("Your order is now cancel. your partial refund is on its way.");			   
						   if(!empty($reason)){
						   	  COrders::savedMeta($order->order_id,'rejetion_reason',$reason);
						   }			   
						} else $this->msg = CommonUtility::parseError( $order->getErrors());					
					} else $this->msg = CommonUtility::parseError( $order->getErrors());
										
				} else {
					//REFUND NOT AVAILABLE
					$this->msg = $resp['cancel_msg'];
				}
			} else {				
				if($resp['cancel_status']==1 && $resp['refund_status']=="full_refund"){
					//CANCEL ORDER
					$order->scenario = "cancel_order";
					if($order->status==$cancel_status){
						$this->msg = t("This order has already been cancelled");
				        $this->responseJson();
					}					
					$order->status = $cancel_status;
					$reason = "Customer cancell this order";
			        $order->remarks = $reason;
					if($order->save()){
					   $this->code = 1;
					   $this->msg = t("Your order is now cancel.");			   
					   if(!empty($reason)){
					   	  COrders::savedMeta($order->order_id,'rejetion_reason',$reason);
					   }			   
					} else $this->msg = CommonUtility::parseError( $order->getErrors());
					
				} else {
					$this->msg = $resp['cancel_msg'];
				}
			}						
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		    
		}	
		$this->responseJson();
	}	

	public function actiongetProfile()
	{
		try {
			
			$model = AR_client::model()->find('client_id=:client_id', 
		    array(':client_id'=> intval(Yii::app()->user->id) )); 		
			if($model){
				$this->code = 1; $this->msg = "ok";
				$this->details = array(
				  'first_name'=>$model->first_name,
				  'last_name'=>$model->last_name,
				  'email_address'=>$model->email_address,
				  'mobile_prefix'=>$model->phone_prefix,
				  'mobile_number'=>str_replace($model->phone_prefix,"",$model->contact_phone),
				  'avatar'=>Yii::app()->user->avatar
				);
			} else $this->msg = t("User not login or session has expired");
		} catch (Exception $e) {							
		    $this->msg[] = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionsaveProfile()
	{
		try {
			
			$code = isset($this->data['code'])?$this->data['code']:'';
		    $email_address = isset($this->data['email_address'])?$this->data['email_address']:'';
		    $mobile_prefix = isset($this->data['mobile_prefix'])?$this->data['mobile_prefix']:'';
		    $mobile_number = isset($this->data['mobile_number'])?$this->data['mobile_number']:'';
		    $contact_number = $mobile_prefix.$mobile_number;

			$filename = isset($this->data['filename'])?$this->data['filename']:'';
			$upload_path = isset($this->data['upload_path'])?$this->data['upload_path']:'';
			
			$file_data = isset($this->data['file_data'])?$this->data['file_data']:'';
			$image_type = isset($this->data['image_type'])?$this->data['image_type']:'png';
		    
		    $model = AR_client::model()->find('client_id=:client_id', 
		    array(':client_id'=> intval(Yii::app()->user->id) )); 	
		    if($model){
		    	$_change = false;
		    	if ($model->email_address!=$email_address){
		    		$_change = true;
		    	}
		    	if ($model->contact_phone!=$contact_number){
		    		$_change = true;
		    	}
		    	if($_change){
		    		if($model->mobile_verification_code!=$code){
		    			$this->msg[] = t("Invalid verification code");
		    			$this->responseJson();
		    			Yii::app()->end();
		    		}
		    	}

		    	$model->first_name = isset($this->data['first_name'])?$this->data['first_name']:'';
		    	$model->last_name = isset($this->data['last_name'])?$this->data['last_name']:'';
		    	$model->email_address = $email_address;
		    	$model->phone_prefix = $mobile_prefix;
		    	$model->contact_phone = $contact_number;

				if(!empty($filename) && !empty($upload_path)){
					$model->avatar = $filename;
					$model->path = $upload_path;
				} else {
					if(!empty($file_data)){
						$result = [];
						try {
							$result = CImageUploader::saveBase64Image($file_data,$image_type,"upload/avatar");				
							$model->avatar = isset($result['filename'])?$result['filename']:'';
							$model->path = isset($result['path'])?$result['path']:'';			
						} catch (Exception $e) {
							$this->msg = t($e->getMessage());
							$this->responseJson();
						}
					}
				}

		    	if($model->save()){

					if(!empty($filename) && !empty($upload_path)){
						Yii::app()->user->setState('avatar', CMedia::getImage($filename,$upload_path) );
					}
					
					$user_data = array(
						'client_uuid'=>Yii::app()->user->client_uuid,
						'first_name'=>$model->first_name,
						'last_name'=>$model->last_name,
						'email_address'=>$model->email_address,
						'contact_number'=>$contact_number,
						'phone_prefix'=>$mobile_prefix,
						'contact_number_noprefix'=>str_replace($mobile_prefix,"",$contact_number),						
						'avatar'=>CMedia::getImage($model->avatar,$model->path,Yii::app()->params->size_image,CommonUtility::getPlaceholderPhoto('customer'))
					);								
					$user_data = JWT::encode($user_data, CRON_KEY, 'HS256');

		    		$this->code = 1;
		    		$this->msg = t("Profile updated");					
					$this->details = $user_data;

		    	} else $this->msg = CommonUtility::parseModelErrorToString( $model->getErrors() );
		    		    	
		    } else $this->msg = t("User not login or session has expired");
		    		    
		} catch (Exception $e) {							
		    $this->msg[] = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionupdatePassword()
	{
		try {
					   
		   $model = AR_client::model()->find('client_id=:client_id', 
		   array(':client_id'=> intval(Yii::app()->user->id) )); 	
		   if($model){		   	   
		   	   $model->scenario = 'update_password';
		   	   $model->old_password = isset($this->data['old_password'])?$this->data['old_password']:'';
		   	   $model->npassword = isset($this->data['new_password'])?$this->data['new_password']:'';
		   	   $model->cpassword = isset($this->data['confirm_password'])?$this->data['confirm_password']:'';
		   	   $model->password = md5($model->npassword);
		   	   if($model->save()){
		    	  $this->code = 1;
		    	  $this->msg = t("Password change");
		      } else $this->msg = CommonUtility::parseModelErrorToString( $model->getErrors() );		   	   
		   } else $this->msg[] = t("User not login or session has expired");
		   		   
		} catch (Exception $e) {							
		    $this->msg[] = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionMyPayments()
	{
		try {
			
			
			$default_payment_uuid = '';			
			$model = AR_client_payment_method::model()->find('merchant_id=:merchant_id AND client_id=:client_id AND as_default=:as_default', 
		    array(
			  ':merchant_id'=>0,
		      ':client_id'=>Yii::app()->user->id,
		      ':as_default'=>1
		    )); 							
		    if($model){		    	
		    	$default_payment_uuid=$model->payment_uuid;
		    }
		    
			$data = CPayments::SavedPaymentList( Yii::app()->user->id , 0);
			
			$this->code = 1;
		    $this->msg = "ok";
		    $this->details = array(
		      'default_payment_uuid'=>$default_payment_uuid,
		      'data'=>$data,
		    );					
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		    
		}	
		$this->responseJson();
	}
	
	public function actiondeletePayment()
	{		
		try {
						
			$payment_uuid = Yii::app()->input->post('payment_uuid');
			CPayments::delete(Yii::app()->user->id,$payment_uuid);
			$this->code = 1;
		    $this->msg = "ok";
			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}	
		$this->responseJson();
	}	

	public function actionPaymentMethod()
	{
		try {
			
		   $data = array(); $payments_credentials=array();
		   $cart_uuid = Yii::app()->input->post('cart_uuid');		   

		   if(!empty($cart_uuid)){			  
			  $merchant_id = CCart::getMerchantId($cart_uuid);		   			  
			  $merchants = CMerchantListingV1::getMerchant( $merchant_id );	
			  $data = CPayments::PaymentList($merchant_id);
			  $payments_credentials = CPayments::getPaymentCredentials($merchant_id,'',$merchants->merchant_type);			
		   } else {
			   $data = CPayments::DefaultPaymentList();
			   $payments_credentials = CPayments::getPaymentCredentials(0,'',2);	
		   }		   

		   $this->code = 1;
		   $this->msg = "OK";		  
		   $this->details = array(
		     'data'=>$data,
			 'credentials'=>$payments_credentials			 
		   );		   		   
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		    		    
		}	
		$this->responseJson();
	}	

	public function actionsaveStoreList()
	{
		try {	
			
		   $data = CSavedStore::Listing( Yii::app()->user->id );		   
		   $services = CSavedStore::services( Yii::app()->user->id  );
		   $estimation = CSavedStore::estimation( Yii::app()->user->id  );					   
		   $this->code = 1;
		   $this->msg = "Ok";		   
		   $this->details = array(
		     'data'=>$data,
		     'services'=>$services,
		     'estimation'=>$estimation
		   );		   				   
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		  		      		   		   
		}
		$this->responseJson();
	}

	public function actionSaveStore()
	{
		try {						
					   
		   if(!Yii::app()->user->isGuest){			   

			   $merchant_id = Yii::app()->input->post('merchant_id');			   
			   $model = AR_favorites::model()->find('fav_type=:fav_type AND merchant_id=:merchant_id AND client_id=:client_id', 
		       array(
				   ':fav_type'=>"restaurant",
				   ':merchant_id'=>$merchant_id ,
				   'client_id'=> Yii::app()->user->id  
				)); 		
		       
		       if($model){
		       	  $model->delete();
		       	  $this->code = 1;
				  $this->msg = "OK";	
				  $this->details = array('found'=>false);
		       } else {			   
				   $model = new AR_favorites;
				   $model->client_id = Yii::app()->user->id;
				   $model->merchant_id = $merchant_id;
				   if($model->save()){
				   	  $this->code = 1;
				      $this->msg = "OK";	
				      $this->details = array('found'=>true);	   
				   } else $this->msg = CommonUtility::parseModelErrorToString( $model->getErrors());
		       }
		   } else $this->msg = t("You must login to save this store");
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		  		      		   
		}	
		$this->responseJson();
	}

	public function actionaddTofav()
	{
		try {	
						
			$item_token = Yii::app()->input->post('item_token');						
			$cat_id = Yii::app()->input->post('cat_id');
			$item = AR_item::model()->find("item_token=:item_token",[
				':item_token'=>$item_token
			]);
			if($item){
				$model = AR_favorites::model()->find("fav_type=:fav_type AND client_id=:client_id 
				AND merchant_id=:merchant_id 
				AND item_id=:item_id
				",[
					':fav_type'=>'item',
					':client_id'=>intval(Yii::app()->user->id),
					':merchant_id'=>intval($item->merchant_id),
					':item_id'=>intval($item->item_id)
				]);
				if($model){
					$model->delete();
					$this->details = array('found'=>false);
				} else {
					$model = new AR_favorites();
				    $model->fav_type='item';
				    $model->client_id = intval(Yii::app()->user->id);
				    $model->merchant_id = intval($item->merchant_id);
					$model->cat_id = intval($cat_id);
					$model->item_id = intval($item->item_id);
					$model->save();
					$mode = 'save';
					$this->details = array('found'=>true);
				}
				$this->code = 1;
				$this->msg = "OK";				
			} else $this->msg = t("Item not found");			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}	
		$this->responseJson();
	}		

	public function actiongetsaveitems()
	{
		try {
			 			
			$data = CSavedStore::getSaveItemsByCustomer(Yii::app()->user->id);						
			$items = CMerchantMenu::getMenuByGroupID($data['item_ids'],Yii::app()->language);					
			$this->code = 1;
			$this->msg = "OK";
			$this->details = [
				'data'=>$data['data'],
				'items'=>$items				
			];
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actionrequestData()
	{		
		$model = AR_client::model()->find('client_id=:client_id', 
		array(':client_id'=> intval(Yii::app()->user->id) )); 	
		if($model){
			$gpdr = AR_gpdr_request::model()->find('client_id=:client_id AND request_type=:request_type AND status=:status', 
		    array( 
		      ':client_id'=> intval(Yii::app()->user->id),
		      ':request_type'=> 'request_data',
		      ':status'=> 'pending'
		    )); 			    
		    if(!$gpdr){
				$gpdr = new AR_gpdr_request;
				$gpdr->request_type = "request_data";
				$gpdr->client_id = intval(Yii::app()->user->id);
				$gpdr->first_name = $model->first_name;
				$gpdr->last_name = $model->last_name;
				$gpdr->email_address = $model->email_address;
				if($gpdr->save()){
					$this->code = 1;
				   	$this->msg = "ok";
				} else $this->msg = CommonUtility::parseModelErrorToString( $model->getErrors() );
		    } else $this->msg = t("You have already existing request.");
		} else $this->msg = t("User not login or session has expired");
		$this->responseJson();
	}

	public function actionverifyAccountDelete()
	{
		$code = Yii::app()->input->post('code');
		$model = AR_client::model()->find('client_id=:client_id', 
		array(':client_id'=> intval(Yii::app()->user->id) )); 	
		if($model){
			if($model->mobile_verification_code==$code){
			   	$this->code = 1;
			   	$this->msg = "ok";			   	
			} else $this->msg[] = t("Invalid verification code");
		} else $this->msg[] = t("User not login or session has expired");
		$this->responseJson();
	}		

	public function actiondeleteAccount()
	{		
		$code = Yii::app()->input->post('code');
		$model = AR_client::model()->find('client_id=:client_id', 
		array(':client_id'=> intval(Yii::app()->user->id) )); 	
		if($model){
			if($model->mobile_verification_code==$code){
			   	//$model->delete();
			   	Yii::app()->user->logout(false);
			   	$this->code = 1;
			   	$this->msg = t("Your account is being deleted");
			   	$this->details = [];
			} else $this->msg[] = t("Invalid verification code");
		} else $this->msg[] = t("User not login or session has expired");
		$this->responseJson();
	}

	public function actionsaveSettings(){
		try {
						
			$app_push_notifications = isset($this->data['app_push_notifications'])?$this->data['app_push_notifications']:'';
			$app_sms_notifications = isset($this->data['app_sms_notifications'])?$this->data['app_sms_notifications']:'';
			$offers_email_notifications = isset($this->data['offers_email_notifications'])?$this->data['offers_email_notifications']:'';
			$promotional_push_notifications = isset($this->data['promotional_push_notifications'])?$this->data['promotional_push_notifications']:'';
			
			AR_client_meta::saveMeta(Yii::app()->user->id,'app_push_notifications',$app_push_notifications);
			AR_client_meta::saveMeta(Yii::app()->user->id,'app_sms_notifications',$app_sms_notifications);
			AR_client_meta::saveMeta(Yii::app()->user->id,'offers_email_notifications',$offers_email_notifications);
			AR_client_meta::saveMeta(Yii::app()->user->id,'promotional_push_notifications',$promotional_push_notifications);

			$this->code = 1;
			$this->msg = t("Setting saved");
			$this->details = [
				'app_push_notifications'=>$app_push_notifications==1?true:false
			];
		
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actiongetSettings()
	{
		try {

			$data = array();
			$client_id = Yii::app()->user->id;
			$criteria=new CDbCriteria();
			$criteria->condition = "client_id=:client_id";		    
			$criteria->params  = array(			  
			  ':client_id'=>intval($client_id)
			);
			$metas = ['app_push_notifications','app_sms_notifications','offers_email_notifications','promotional_push_notifications'];
			$criteria->addInCondition('meta1', (array) $metas );
			$model = AR_client_meta::model()->findAll($criteria); 
			if($model){				
				foreach ($model as $item) {					
					$data[$item->meta1] = $item->meta2;
				}			
			} 
			$this->code = 1;
			$this->msg = "OK";
			$this->details = $data;
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actionrequestResetPassword()
	{
		try {

			$email_address = Yii::app()->input->post('email_address');		   						
			$model = AR_clientsignup::model()->find('email_address=:email_address', 
		    array(':email_address'=>$email_address)); 
		    if($model){
		    	if($model->status=="active"){
		    		$model->scenario = "reset_password";
		    		$model->reset_password_request = 1;
		    		if($model->save()){											
						$this->code = 1;
						$this->msg = t("Check {{email_address}} for an email to reset your password.",array(
						'{{email_address}}'=>$model->email_address
						));
						$this->details = array(
						'uuid'=>$model->client_uuid
						);
					} else {
						$this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
					}							    				    	
		    	} else $this->msg = t("Your account is either inactive or not verified.");
		    } else $this->msg = t("No email address found in our records. please verify your email.");

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actionresendResetEmail()
	{
		try {
			
		   $client_uuid = Yii::app()->input->post('client_uuid');
		   
		   $model = AR_clientsignup::model()->find('client_uuid=:client_uuid', 
		   array(':client_uuid'=>$client_uuid)); 
		   if($model){		   	  
			  $model->scenario = "reset_password";
		   	  $model->reset_password_request = 1;		    		
		   	  if($model->save()){			   	  	 
		   	  	      	  	   	   	  
		   	  	   $this->code = 1;
		           $this->msg = t("Check {{email_address}} for an email to reset your password.",array(
		    		  '{{email_address}}'=>$model->email_address
		    	   ));

		   	  } else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());		   	  
		   } else $this->msg = t("Records not found");
		   
		} catch (Exception $e) {							
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();	
	}	

	public function actioncheckStoreOpen()
	{
		try {

			$cart_uuid = Yii::app()->input->post('cart_uuid');
			$merchant_id = CCart::getMerchantId($cart_uuid);	
			
			// CHECK IF MERCHANT HAS DIFFERENT TIMEZONE
			$options_merchant = OptionsTools::find(['merchant_timezone'],$merchant_id);
			$merchant_timezone = isset($options_merchant['merchant_timezone'])?$options_merchant['merchant_timezone']:'';
			if(!empty($merchant_timezone)){
				Yii::app()->timezone = $merchant_timezone;
			}
			
			$date = date("Y-m-d");
			$time_now = date("H:i");
			
			$choosen_delivery = isset($this->data['choosen_delivery'])?$this->data['choosen_delivery']:'';		
			$whento_deliver = isset($choosen_delivery['whento_deliver'])?$choosen_delivery['whento_deliver']:'';
			
			if($whento_deliver=="schedule"){
				$date = isset($choosen_delivery['delivery_date'])?$choosen_delivery['delivery_date']:$date;
				$time_now = isset($choosen_delivery['delivery_time'])?$choosen_delivery['delivery_time']['start_time']:$time_now;
			}
						
			$datetime_to = date("Y-m-d g:i:s a",strtotime("$date $time_now"));
			CMerchantListingV1::checkCurrentTime( date("Y-m-d g:i:s a") , $datetime_to);		
			
			$resp = CMerchantListingV1::checkStoreOpen($merchant_id,$date,$time_now);
			$this->code = 1;
			$this->msg = $resp['merchant_open_status']>0?"ok":t("This store is close right now, but you can schedulean order later.");
			$this->details =  $resp;

		} catch (Exception $e) {							
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();	
	}

	public function actioncheckStoreOpen2()
	{
		try {

			
			$slug = isset($this->data['slug'])?$this->data['slug']:'';
			$merchant = CMerchantListingV1::getMerchantBySlug($slug);			
			$merchant_id = $merchant->merchant_id;

			// CHECK IF MERCHANT HAS DIFFERENT TIMEZONE
			$options_merchant = OptionsTools::find(['merchant_timezone'],$merchant_id);			
			$merchant_timezone = isset($options_merchant['merchant_timezone'])?$options_merchant['merchant_timezone']:'';
			if(!empty($merchant_timezone)){
				Yii::app()->timezone = $merchant_timezone;
			}
						
			$date = date("Y-m-d");
			$time_now = date("H:i");
			
			$choosen_delivery = isset($this->data['choosen_delivery'])?$this->data['choosen_delivery']:'';					
			$whento_deliver = isset($choosen_delivery['whento_deliver'])?$choosen_delivery['whento_deliver']:'';			
			
			if($whento_deliver=="schedule"){
				$date = isset($choosen_delivery['delivery_date'])?$choosen_delivery['delivery_date']:$date;
				$time_now = isset($choosen_delivery['delivery_time'])?$choosen_delivery['delivery_time']['start_time']:$time_now;
			}
			
			try {
				$datetime_to = date("Y-m-d g:i:s a",strtotime("$date $time_now"));
				CMerchantListingV1::checkCurrentTime( date("Y-m-d g:i:s a") , $datetime_to);		
				$time_already_passed = false;
			} catch (Exception $e) {
				$time_already_passed = true;
			}
			
			$resp = CMerchantListingV1::checkStoreOpen($merchant_id,$date,$time_now);			
			if($merchant->close_store==1){
				$resp['merchant_open_status'] = 0;			
			}
			$this->code = 1;
			$this->msg = $resp['merchant_open_status']>0?"ok":t("This store is close right now, but you can schedulean order later.");
			$this->details =  $resp;
			$this->details['time_already_passed'] = $time_already_passed;

		} catch (Exception $e) {							
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();	
	}

	public function actioncheckoutAddress()
	{		
		try {
			$place_id = Yii::app()->input->post('place_id');					
			$data = CClientAddress::getAddress($place_id,Yii::app()->user->id);

			$maps_config = CMaps::config();
			$maps_config = JWT::encode($maps_config , CRON_KEY, 'HS256');       

			$this->code =1;
			$this->msg = "ok";
			$this->details = [
				'data'=>$data,
				'maps_config'=>$maps_config
			];
	    } catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}				

	public function actionmenuSearch()
	{
		try {						
			$q = Yii::app()->input->post('q');
			$slug = Yii::app()->input->post('slug');
			$model = CMerchantListingV1::getMerchantBySlug($slug);
			$merchant_id = $model->merchant_id;						
			$items = CMerchantMenu::getSimilarItems($merchant_id,Yii::app()->language,100,$q);			
			$this->code = 1; $this->msg = "ok";			
			$this->details = [
				'slug'=>$model->restaurant_slug,
				'data'=>$items				
			];
		} catch (Exception $e) {							
		    $this->msg = t($e->getMessage());		    
		}					
		$this->responseJson();
	}        

	public function actiongetMoneyConfig()
	{
		try {

			$config = array();
			$format = Price_Formatter::$number_format;
			$config = [				
				'precision' => $format['decimals'],
				'decimal' => $format['decimal_separator'],
				'thousands' => $format['thousand_separator'],
				'prefix'=> $format['position']=='left'?$format['currency_symbol']:'',
				'suffix'=> $format['position']=='right'?$format['currency_symbol']:''
			];	

			$this->code = 1;
			$this->msg = "ok";
			$this->details = $config;

		} catch (Exception $e) {							
		    $this->msg = t($e->getMessage());		    
		}					
		$this->responseJson();
	}

	public function actiongetMapconfig()
	{
		try {

		    $maps_config = CMaps::config();
			$maps_config = JWT::encode($maps_config , CRON_KEY, 'HS256');   
			
			$this->code = 1;
			$this->msg = "ok";
			$this->details = $maps_config;

		} catch (Exception $e) {							
		    $this->msg = t($e->getMessage());		    
		}					
		$this->responseJson();
	}

	public function actionSearch()
	{
		try {

			$page = 0;
			$todays_date = date("Y-m-d H:i");						
			
			$payload = [
				'cuisine','reviews','services'
			];

		    $q = Yii::app()->input->post('q');
			$place_id = Yii::app()->input->post('place_id');
			$currency_code = Yii::app()->input->post('currency_code');

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		    $multicurrency_enabled = $multicurrency_enabled==1?true:false;			

			$place_data = CMaps::locationDetails($place_id,'');			

			$filters = [
				'lat'=>isset($place_data['latitude'])?$place_data['latitude']:'',
				'lng'=>isset($place_data['longitude'])?$place_data['longitude']:'',
				'limit'=>100,
				'unit'=>Yii::app()->params['settings']['home_search_unit_type'],
				'today_now'=>strtolower(date("l",strtotime($todays_date))),
				'time_now'=>date("H:i",strtotime($todays_date)),
				'date_now'=>$todays_date,
				'page'=>intval($page),
				'client_id'=>!Yii::app()->user->isGuest?Yii::app()->user->id:0,
			];

			$and = '';

			$filters['having'] = "distance < a.delivery_distance_covered";		
			$filters['condition'] = "a.status=:status  AND a.is_ready = :is_ready $and";
			$filters['params'] = [
				':status'=>'active',
				':is_ready'=>2
			];			
			$filters['search'] = "a.restaurant_name";
			$filters['search_params'] = $q;

			$merchant_data = []; $cuisine = []; $items = []; $merchant_list = [];

			try {
				
				$data = CMerchantListingV1::getFeed($filters);					
				$merchant_data = $data['data'];

				if(in_array('cuisine',$payload)){
					try {
						$cuisine = CMerchantListingV1::getCuisine( $data['merchant'] , Yii::app()->language );			
					} catch (Exception $e) {
						$cuisine = [];
					}
				}

			} catch (Exception $e) {							
				$merchant_data = [];
			}			

			try {
				
				$search = explode(" ",$q);
				$data = CMerchantMenu::searchItems($search,Yii::app()->language,100,$currency_code,$multicurrency_enabled);				
				$items = $data['data'];
				$merchant_ids = $data['merchant_ids'];
				$merchant_list = CMerchantListingV1::getMerchantList($merchant_ids);

			} catch (Exception $e) {							
				$items = [];
			}			

			$config = array();
			$format = Price_Formatter::$number_format;
			$config = [				
				'precision' => $format['decimals'],
				'decimal' => $format['decimal_separator'],
				'thousands' => $format['thousand_separator'],
				'prefix'=> $format['position']=='left'?$format['currency_symbol']:'',
				'suffix'=> $format['position']=='right'?$format['currency_symbol']:''
			];	
			
			$this->code = 1;
			$this->msg = "OK";
			$this->details = [
			   'merchant_data'=>$merchant_data,
			   'cuisine'=>$cuisine,
			   'food_list'=>$items,
			   'merchant_list'=>$merchant_list,
			   'money_config'=>$config
			];

		} catch (Exception $e) {							
		    $this->msg = t($e->getMessage());		    
		}					
		$this->responseJson();
	}
	
	public function actiongetNotification()
	{
		try {

			$limit = 20;
			$page = intval(Yii::app()->input->post('page'));				
			$page_raw = intval(Yii::app()->input->post('page'));
			if($page>0){
				$page = $page-1;
			}
						
			$criteria=new CDbCriteria();
			$criteria->condition = "notication_channel=:notication_channel";
			$criteria->params  = array(		  
			':notication_channel'=>Yii::app()->user->client_uuid
			);
			$criteria->order = "date_created DESC";

		    $count=AR_notifications::model()->count($criteria);
			$pages=new CPagination($count);
			$pages->pageSize=$limit;
			$pages->setCurrentPage( $page );        
			$pages->applyLimit($criteria);
			$page_count = $pages->getPageCount();

			if($page>0){
				if($page_raw>$page_count){
					$this->code = 3;
					$this->msg = t("end of results");
					$this->responseJson();
				}
			}			

			$model = AR_notifications::model()->findAll($criteria);
			if($model){		
				$data = [];
				foreach ($model as $item) {
					$image=''; $url = '';
					if($item->image_type=="icon"){
						$image = !empty($item->image)?$item->image:'';
					} else {
						if(!empty($item->image)){
							$image = CMedia::getImage($item->image,$item->image_path,
							Yii::app()->params->size_image_thumbnail ,
							CommonUtility::getPlaceholderPhoto('item') );
						}
					}
					
					$params = !empty($item->message_parameters)?json_decode($item->message_parameters,true):'';
					
					$data[]=array(
					'notification_uuid'=>$item->notification_uuid,
					'notification_type'=>$item->notification_type,
					'message'=>t($item->message,(array)$params),
					'date'=>PrettyDateTime::parse(new DateTime($item->date_created)),				  
					'image_type'=>$item->image_type,
					'image'=>$image,
					'url'=>$url
					);
				}	

				$this->code = 1;
				$this->msg = "ok";
				$this->details = [
					'page_raw'=>$page_raw,
					'page_count'=>$page_count,
					'data'=>$data
				];
				
			} else $this->msg = t("No results");

		} catch (Exception $e) {
			$this->msg = t($e->getMessage());		    
		}
		$this->responseJson();
	}

	public function actiondeleteNotification()
	{
		try {

			$uuid = Yii::app()->input->post('uuid');
			$model = AR_notifications::model()->find("notification_uuid=:notification_uuid",[
				':notification_uuid'=>$uuid
			]);
			if($model){
				$model->delete();
				$this->code = 1;
				$this->msg = "OK";
				$this->details = [];
			} else $this->msg = t("Record not found");
		} catch (Exception $e) {							
		    $this->msg = t($e->getMessage());		    
		}					
		$this->responseJson();
	}

	public function actiongetCustomerInfo()
	{
		try {
			
			$phone_default_country = isset(Yii::app()->params['settings']['mobilephone_settings_default_country'])?Yii::app()->params['settings']['mobilephone_settings_default_country']:'us';
	        $phone_country_list = isset(Yii::app()->params['settings']['mobilephone_settings_country'])?Yii::app()->params['settings']['mobilephone_settings_country']:'';
	        $phone_country_list = !empty($phone_country_list)?json_decode($phone_country_list,true):array();        

			$filter = array(
				'only_countries'=>(array)$phone_country_list
			);			
			$data = ClocationCountry::listing($filter);
			$default_data = ClocationCountry::get($phone_default_country);

			$client_uuid = Yii::app()->input->post('client_uuid');
			$model = AR_clientsignup::model()->find('client_uuid=:client_uuid', 
		    array(':client_uuid'=>$client_uuid)); 
		    if($model){
		    	$this->code = 1;
		    	$this->msg  = "Ok";
		    	$this->details = array(
		    	  'first_name'=>$model->first_name,
		    	  'last_name'=>$model->last_name,
		    	  'email_address'=>$model->email_address,
				  'data'=>$data,
				  'default_data'=>$default_data
		    	);
		    } else $this->msg = t("Records not found");						
		} catch (Exception $e) {
		    $this->msg = $e->getMessage();		    
		}
		$this->responseJson();	
	}

	public function actioncompleteSocialSignup()
	{
		try {
			
			$client_uuid = isset($this->data['client_uuid'])?$this->data['client_uuid']:'';			
			$prefix = isset($this->data['mobile_prefix'])?$this->data['mobile_prefix']:'';
		    $mobile_number = isset($this->data['mobile_number'])?$this->data['mobile_number']:'';
			$local_id = isset($this->data['local_id'])?$this->data['local_id']:'';		

			$model = AR_clientsignup::model()->find('client_uuid=:client_uuid', 
		    array(':client_uuid'=>$client_uuid)); 
			if($model){
				$model->scenario = 'complete_registration';
		    	if($model->account_verified==1){
					$model->first_name = isset($this->data['first_name'])?$this->data['first_name']:'';
			    	$model->last_name = isset($this->data['last_name'])?$this->data['last_name']:'';
		    		$model->contact_phone = $prefix.$mobile_number;
		    		$model->phone_prefix = $prefix;		    		
					$model->password = isset($this->data['password'])?$this->data['password']:'';
					$model->cpassword = isset($this->data['cpassword'])?$this->data['cpassword']:'';
					$password = $model->password;
		    		$model->status='active';
					if ($model->save()){
						$this->code = 1;
			    		$this->msg = t("Registration successful");						
						$this->autoLogin($model->email_address,$password);	
						$this->saveDeliveryAddress($local_id,$model->client_id);
					} else $this->msg = CommonUtility::parseModelErrorToString( $model->getErrors() );
				} else $this->msg[] = t("Accout not verified");	
			} else $this->msg = t("Records not found");			
		} catch (Exception $e) {
		    $this->msg = $e->getMessage();		    
		}
		$this->responseJson();	
	}

	public function actionregisterDevice()
	{
		try {


            $this->code = 1;
			$this->msg = "Ok";
            $this->responseJson();

			$token = Yii::app()->input->post('token');
			$device_uiid = Yii::app()->input->post('device_uiid');
			$platform = Yii::app()->input->post('platform');
			
			$model = AR_device::model()->find("device_token = :device_token",[
				':device_token'=>$token
			]);
			if($model){				
				$model->device_uiid = $device_uiid;
				$model->enabled = 1;				
				$model->date_created = CommonUtility::dateNow();
				$model->date_modified = CommonUtility::dateNow();
				$model->ip_address = CommonUtility::userIp();
				if(!$model->save()){
					$this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
					$this->responseJson();	
				}
			} else {				
				$model = new AR_device;		
				$model->user_type = "client";
				$model->user_id = 0;
				$model->platform = $platform;
				$model->device_token = $token;
				$model->device_uiid = $device_uiid;
				$model->enabled = 1;
				$model->date_created = CommonUtility::dateNow();
				$model->ip_address = CommonUtility::userIp();
				if(!$model->save()){
					$this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
					$this->responseJson();	
				}
			}			
			
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = $model->device_id;

		} catch (Exception $e) {
		    $this->msg = $e->getMessage();		    
		}
		$this->responseJson();	
	}

	public function actionupdateDevice()
	{
		try {

			$token = Yii::app()->input->post('token');
			$device_uiid = Yii::app()->input->post('device_uiid');
			$platform = Yii::app()->input->post('platform');
								
			$model = AR_device::model()->find("user_type=:user_type AND user_id=:user_id AND device_uiid = :device_uiid",[
				':user_type'=>'client',
				':user_id'=>Yii::app()->user->id,
				':device_uiid'=>$device_uiid
			]);
			if($model){				
				$model->platform = $platform;
				$model->device_token = $token;
				$model->device_uiid = $device_uiid;
				$model->user_id = Yii::app()->user->id;
				$model->enabled = 1;				
				$model->date_created = CommonUtility::dateNow();
				$model->date_modified = CommonUtility::dateNow();
				$model->ip_address = CommonUtility::userIp();
				if(!$model->save()){
					$this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
					$this->responseJson();	
				}
			} else {				
				$model = new AR_device;		
				$model->user_type = "client";
				$model->user_id = Yii::app()->user->id;
				$model->platform = $platform;
				$model->device_token = $token;
				$model->device_uiid = $device_uiid;
				$model->enabled = 1;
				$model->date_created = CommonUtility::dateNow();
				$model->ip_address = CommonUtility::userIp();
				if(!$model->save()){
					$this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
					$this->responseJson();	
				}
			}			
			
            Yii::app()->db->createCommand("
            DELETE FROM {{device}}
            WHERE user_type='client'
            AND user_id=0
            AND device_uiid=".q($device_uiid)."
            ")->query();


			$this->code = 1;
			$this->msg = "Ok";	
            $this->details = $model->device_id;
                        

		} catch (Exception $e) {
		    $this->msg = $e->getMessage();		    
		}
		$this->responseJson();	
	}

	public function actionauthenticate()
	{
		try {					
			
			$jwt_token = Yii::app()->input->post('token');						
			$decoded = JWT::decode($jwt_token, new Key(CRON_KEY, 'HS256'));			
			$token = isset($decoded->token)?$decoded->token:'';
			$model = AR_client::model()->find('token=:token',array(':token'=>$token));
			if($model){
				$this->code = 1;
				$this->msg = "ok";
			} else $this->msg = t("Token is not valid");
			
		} catch (Exception $e) {
			$this->msg = $e->getMessage();		    
		}
		$this->responseJson();	
	}

	public function actionsearchItems()
	{
		try {
			$q = Yii::app()->input->post('q');
			$slug = Yii::app()->input->post('slug');
			$merchant = CMerchantListingV1::getMerchantBySlug($slug);			
			$merchant_id = $merchant->merchant_id;
			$data = CMerchantMenu::searchMenuItems($q,$merchant_id,Yii::app()->language,100);
			$this->code = 1;
			$this->msg = "ok";
			$this->details = $data;			
		} catch (Exception $e) {
			$this->msg = $e->getMessage();		    
		}
		$this->responseJson();	
	}

	public function actiongetItemFavorites()
	{
		try {
			
			$slug = Yii::app()->input->post('slug');			
			$merchant = CMerchantListingV1::getMerchantBySlug($slug);
			$data = CSavedStore::getSaveItemsByCustomer(Yii::app()->user->id,$merchant->merchant_id);
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = $data;

		} catch (Exception $e) {
			$this->msg = $e->getMessage();		    
		}
		$this->responseJson();	
	}

	public function actiongetAttributes()
	{
		try {

			$this->code = 1;
			$this->msg = "Ok";					

			$tips_list= []; $phone_prefix_data= [];

			$phone_default_country = isset(Yii::app()->params['settings']['mobilephone_settings_default_country'])?Yii::app()->params['settings']['mobilephone_settings_default_country']:'us';
	        $phone_country_list = isset(Yii::app()->params['settings']['mobilephone_settings_country'])?Yii::app()->params['settings']['mobilephone_settings_country']:'';
	        $phone_country_list = !empty($phone_country_list)?json_decode($phone_country_list,true):array();        

			$enabled_language = isset(Yii::app()->params['settings']['enabled_language_customer_app'])?Yii::app()->params['settings']['enabled_language_customer_app']:'';
			$enabled_language = $enabled_language==1?true:false;

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		    $multicurrency_enabled = $multicurrency_enabled==1?true:false;		
			$enabled_hide_payment = isset(Yii::app()->params['settings']['multicurrency_enabled_hide_payment'])?Yii::app()->params['settings']['multicurrency_enabled_hide_payment']:false;		   
			$enabled_checkout_currency = isset(Yii::app()->params['settings']['multicurrency_enabled_checkout_currency'])?Yii::app()->params['settings']['multicurrency_enabled_checkout_currency']:false;		
		    $hide_payment = $multicurrency_enabled==true? ($enabled_hide_payment==1?true:false) :false;
			$enabled_force = $multicurrency_enabled==true? ($enabled_checkout_currency==1?true:false) :false;		

			$points_enabled = isset(Yii::app()->params['settings']['points_enabled'])?Yii::app()->params['settings']['points_enabled']:false;
		    $points_enabled = $points_enabled==1?true:false;

			$filter = array(
				'only_countries'=>(array)$phone_country_list
			);			
			$phone_prefix_data = ClocationCountry::listing($filter);
			$phone_default_data = ClocationCountry::get($phone_default_country);
		
			
			try {
				$tips_list = CTips::data('label');
			} catch (Exception $e) {
				//
			}

			$maps_config = CMaps::config();
			$maps_config = JWT::encode($maps_config , CRON_KEY, 'HS256');   

			$lang_data = [];
			try {
				$lang_data = ClocationCountry::getLanguageList();
				$lang_data = JWT::encode($lang_data, CRON_KEY, 'HS256');
			} catch (Exception $e) {
				//
			}				

			$money_config = array();
			$format = Price_Formatter::$number_format;
			$money_config = [				
				'precision' => $format['decimals'],
				'minimumFractionDigits'=>$format['decimals'],
				'decimal' => $format['decimal_separator'],
				'thousands' => $format['thousand_separator'],
				'separator' => $format['thousand_separator'],
				'prefix'=> $format['position']=='left'?$format['currency_symbol']:'',
				'suffix'=> $format['position']=='right'?$format['currency_symbol']:'',
				'prefill'=>true
			];	

			// REALTIME
			$realtime = AR_admin_meta::getMeta(array('realtime_app_enabled','realtime_provider',
			'webpush_app_enabled','webpush_provider','pusher_key','pusher_cluster'));						
			$realtime_app_enabled = isset($realtime['realtime_app_enabled'])?$realtime['realtime_app_enabled']['meta_value']:'';
			$realtime_provider = isset($realtime['realtime_provider'])?$realtime['realtime_provider']['meta_value']:'';
			$pusher_key = isset($realtime['pusher_key'])?$realtime['pusher_key']['meta_value']:'';
			$pusher_cluster = isset($realtime['pusher_cluster'])?$realtime['pusher_cluster']['meta_value']:'';

			$realtime = [
				'realtime_app_enabled'=>$realtime_app_enabled,
				'realtime_provider'=>$realtime_provider,
				'pusher_key'=>$pusher_key,
				'pusher_cluster'=>$pusher_cluster,
				'event'=>[
					'tracking'=>Yii::app()->params->realtime['event_tracking_order'],
					'notification_event'=>Yii::app()->params->realtime['notification_event'],
					'booking'=>'booking',
					'cart'=>'cart',
				]
				];
			try {
				$realtime = JWT::encode($realtime, CRON_KEY, 'HS256');
			} catch (Exception $e) {
				$realtime = '';
			}				
			
			$invite_friend_settings = [
				'title'=>'',
				'text'=>t("Check this app - {site_name}. I use this app to order food from different restaurants. Try them: {site_url}",[
					'{site_name}'=>Yii::app()->params['settings']['website_title'],
					'{site_url}'=>websiteDomain(),
				]),
				'url'=>websiteUrl()
			];

			$fb_flag = isset(Yii::app()->params['settings']['fb_flag'])?Yii::app()->params['settings']['fb_flag']:false;
			$fb_flag = $fb_flag==1?true:false;
			$google_login_enabled = isset(Yii::app()->params['settings']['google_login_enabled'])?Yii::app()->params['settings']['google_login_enabled']:false;
			$google_login_enabled = $google_login_enabled==1?true:false;

			$captcha_site_key = isset(Yii::app()->params['settings']['captcha_site_key'])?Yii::app()->params['settings']['captcha_site_key']:'';
			$captcha_lang = isset(Yii::app()->params['settings']['captcha_lang'])?Yii::app()->params['settings']['captcha_lang']:'';

			$booking_status_list = AttributesTools::bookingStatus();
			$booking_status_list = array_merge([
				'all'=>t("All")
			], $booking_status_list);	          

			$this->details = [
				'phone_prefix_data'=>$phone_prefix_data,
				'phone_default_data'=>$phone_default_data,
				'tips_list'=>$tips_list,
				'maps_config'=>$maps_config,
				'language_data'=>$lang_data,
				'money_config'=>$money_config,
				'realtime'=>$realtime,
				'invite_friend_settings'=>$invite_friend_settings,
				'fb_flag'=>$fb_flag,
				'google_login_enabled'=>$google_login_enabled,
				'enabled_language'=>$enabled_language,
				'multicurrency_enabled'=>$multicurrency_enabled,
				'multicurrency_hide_payment'=>$hide_payment,
				'multicurrency_enabled_force'=>$enabled_force,
				'default_currency_code'=>Price_Formatter::$number_format['currency_code'],
				'currency_list'=>$multicurrency_enabled?CMulticurrency::currencyList():array(),
				'points_enabled'=>$points_enabled,
				'captcha_settings'=>[
					'site_key'=>$captcha_site_key,
					'language'=>$captcha_lang,
				],
				'booking_status_list'=>$booking_status_list
			];						
		} catch (Exception $e) {
			$this->msg = $e->getMessage();		    
		}
		$this->responseJson();	
	}

	public function actionremoveTips()
	{
		try {
			
			$cart_uuid = Yii::app()->input->post('cart_uuid');			
			$model = AR_cart_attributes::model()->find("cart_uuid=:cart_uuid AND meta_name=:meta_name",[
				':cart_uuid'=>$cart_uuid,
				':meta_name'=>"tips"
			]);
			if($model){				
				$model->meta_id = 0;
				$model->save();
			} 
			$this->code = 1;
			$this->msg = "Ok";			
		} catch (Exception $e) {
			$this->msg = $e->getMessage();		    
		}
		$this->responseJson();	
	}

	public function actionuserLoginPhone()
	{
		try {

			$mobile_prefix = isset($this->data['mobile_prefix'])?$this->data['mobile_prefix']:'';
			$mobile_prefix = str_replace("+","",$mobile_prefix);
			$mobile_number = isset($this->data['mobile_number'])?$this->data['mobile_number']:'';
			$password = isset($this->data['password'])?$this->data['password']:'';
			$local_id = isset($this->data['local_id'])?$this->data['local_id']:'';

			$options = OptionsTools::find(array('signup_enabled_capcha','captcha_secret'));		
		    $signup_enabled_capcha = isset($options['signup_enabled_capcha'])?$options['signup_enabled_capcha']:false;
		    $merchant_captcha_secret = isset($options['captcha_secret'])?$options['captcha_secret']:'';
		    $capcha = $signup_enabled_capcha==1?true:false;
		    $recaptcha_response = isset($this->data['recaptcha_response'])?$this->data['recaptcha_response']:'';			

			$model=new AR_customer_login;
			$model->capcha = false;
			$model->recaptcha_response = $recaptcha_response;
		    $model->captcha_secret = $merchant_captcha_secret;
		    $model->merchant_id = 0;
			$model->username = $mobile_prefix.$mobile_number;
			$model->password = $password;			
			if($model->validate() && $model->login() ){
				$this->saveDeliveryAddress($local_id, Yii::app()->user->id );				
				$this->code = 1 ;
				$this->msg = t("Login successful");
				$this->userData();				
			} else $this->msg = CommonUtility::parseError( $model->getErrors() );

		} catch (Exception $e) {
			$this->msg = $e->getMessage();		    
		}
		$this->responseJson();	
	}

	public function actionupdateAvatar()
	{
		try {			
			if(!Yii::app()->user->isGuest){			
			   
			   $upload_uuid = CommonUtility::generateUIID();			   
			   $allowed_extension = explode(",",  Yii::app()->params['upload_type']);
			   $maxsize = (integer) Yii::app()->params['upload_size'] ;

			   if (!empty($_FILES)) {

				  $title = $_FILES['file']['name'];   
			      $size = (integer)$_FILES['file']['size'];   
			      $filetype = $_FILES['file']['type'];   								

				  if(isset($_FILES['file']['name'])){
					$extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
				  } else $extension = strtolower(substr($title,-3,3));

				  if(!in_array($extension,$allowed_extension)){			
					$this->msg = t("Invalid file extension");
					$this->responseJson();
				  }
				  if($size>$maxsize){
					$this->msg = t("Invalid file size");
					$this->responseJson();
				  }

				  $upload_path = "upload/avatar";
				  $tempFile = $_FILES['file']['tmp_name'];   								
				  $upload_uuid = CommonUtility::createUUID("{{media_files}}",'upload_uuid');
				  $filename = $upload_uuid.".$extension";						
				  $path = CommonUtility::uploadDestination($upload_path)."/".$filename;						

				  $image_set_width = isset(Yii::app()->params['settings']['review_image_resize_width']) ? intval(Yii::app()->params['settings']['review_image_resize_width']) : 0;
				  $image_set_width = $image_set_width<=0?300:$image_set_width;
							
				  $image_driver = !empty(Yii::app()->params['settings']['image_driver'])?Yii::app()->params['settings']['image_driver']:Yii::app()->params->image['driver'];			
				  $manager = new ImageManager(array('driver' => $image_driver ));								
				  $image = $manager->make($tempFile);
				  $image_width = $manager->make($tempFile)->width();
							
				  if($image_width>$image_set_width){
				 	$image->resize(null, $image_set_width, function ($constraint) {
						$constraint->aspectRatio();
					});
					$image->save($path);
				  } else {
					$image->save($path,60);
				  }				

				  $this->code = 1; $this->msg = "OK";			
				  $this->details = array(			   			   
					'url_image'=>CMedia::getImage($filename,$upload_path),
					'filename'=>$filename,
					'id'=>$upload_uuid,
					'upload_path'=>$upload_path
				  );			

			   } else $this->msg = t("Invalid file");

		    } else $this->msg = t("User not login or session has expired");
		} catch (Exception $e) {
			$this->msg = $e->getMessage();		    
		}
		$this->responseJson();	
	}

	public function actionUpdateaccountnotification()
	{
		try {
			
			$app_push_notifications = Yii::app()->input->post('app_push_notifications');			
			$app_push_notifications = $app_push_notifications=="true"?1:0;			
			AR_client_meta::saveMeta(Yii::app()->user->id,'app_push_notifications',$app_push_notifications); 

			$this->code = 1;
			$this->msg = t("Setting saved");
			$this->details = [
				'app_push_notifications'=>$app_push_notifications==1?true:false,				
			];

		} catch (Exception $e) {
			$this->msg = $e->getMessage();		    
		}
		$this->responseJson();	
	}

	public function actionUpdateaccountpromonotification()
	{
		try {
						
			$promotional_push_notifications = Yii::app()->input->post('promotional_push_notifications');
			$promotional_push_notifications = $promotional_push_notifications=="true"?1:0;			
			AR_client_meta::saveMeta(Yii::app()->user->id,'promotional_push_notifications',$promotional_push_notifications); 

			$this->code = 1;
			$this->msg = t("Setting saved");
			$this->details = [
				'promotional_push_notifications'=>$promotional_push_notifications==1?true:false,				
			];

		} catch (Exception $e) {
			$this->msg = $e->getMessage();		    
		}
		$this->responseJson();	
	}

	public function actionorderDeliveryDetails()
	{
		try {
			
			$order_uuid = Yii::app()->input->post('order_uuid');			
			$data = AOrders::getOrderHistory($order_uuid);
			$order_status = AttributesTools::getOrderStatus(Yii::app()->language,'delivery_status');
			
			$progress = CTrackingOrder::getProgress($order_uuid , date("Y-m-d g:i:s a") , [
				'order_info','merchant_info'
			]);	

			$this->code = 1;
			$this->msg = "ok";
			$this->details = [
				'data'=>$data,
				'order_status'=>$order_status,
				'progress'=>$progress
			];

		} catch (Exception $e) {
			$this->msg = $e->getMessage();		    
		}
		$this->responseJson();	
	}

	public function actiondeleteAllNotification()
	{
		try {

			$notification_uuids = isset($this->data['notification_uuids'])?$this->data['notification_uuids']:'';			
			CNotifications::deleteNotifications(Yii::app()->user->client_uuid,$notification_uuids);
			$this->code = 1;
			$this->msg = "Ok";

		} catch (Exception $e) {
			$this->msg = $e->getMessage();		    
		}
		$this->responseJson();	
	}

	public function actiondeleteNotifications()
	{
		try {
			
			CNotifications::deleteByChannel(Yii::app()->user->client_uuid);
			$this->code = 1;
			$this->msg = "Ok";

		} catch (Exception $e) {
			$this->msg = $e->getMessage();		    
		}
		$this->responseJson();	
	}

	public function actiongetPage()
	{
		try {			
			$page_id = Yii::app()->input->post('page_id');			
			$option = OptionsTools::find([$page_id]);
			$id = isset($option[$page_id])?$option[$page_id]:0;									
			$data = PPages::pageDetailsByID($id,Yii::app()->language);
			$this->code = 1;
			$this->msg = "Ok";
			$this->details  = $data;
		} catch (Exception $e) {
			$this->msg = $e->getMessage();		    
		}
		$this->responseJson();	
	}

	public function actiongetAvailablePoints()
	{
		try {
			$total = CPoints::getAvailableBalance(Yii::app()->user->id);
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'total'=>$total,				
			];
		} catch (Exception $e) {
			$this->msg = $e->getMessage();		    
		}
		$this->responseJson();	
	}

	public function actiongetPointsTransaction()
	{
		$data = array(); $card_id = 0;
		try {	
		    $card_id = CWallet::getCardID(Yii::app()->params->account_type['customer_points'],Yii::app()->user->id);				
		} catch (Exception $e) {
		    $this->msg = t("Invalid card id");
			$this->responseJson();
		}
				
		$limit = 20;
		$page = intval(Yii::app()->input->post('page'));				
		$page_raw = intval(Yii::app()->input->post('page'));
		if($page>0){
			$page = $page-1;
		}
		
		$criteria=new CDbCriteria();
		$criteria->addCondition('card_id=:card_id');
		$criteria->params = array(':card_id'=>intval($card_id));
		$criteria->order = "transaction_id DESC";

		$count = AR_wallet_transactions::model()->count($criteria); 
		$pages=new CPagination($count);
		$pages->pageSize=$limit;
		$pages->setCurrentPage( $page );        
		$pages->applyLimit($criteria);
		$page_count = $pages->getPageCount();

		if($page>0){
			if($page_raw>$page_count){
				$this->code = 3;
				$this->msg = t("end of results");
				$this->responseJson();
			}
		}
		
        $models = AR_wallet_transactions::model()->findAll($criteria);
		if($models){			
			foreach ($models as $item) {
				$description = Yii::app()->input->xssClean($item->transaction_description);        		
        		$parameters = json_decode($item->transaction_description_parameters,true);        		
        		if(is_array($parameters) && count($parameters)>=1){        			
        			$description = t($description,$parameters);
        		}        		       
				
				$transaction_amount = 0; $transaction_type = '';
				switch ($item->transaction_type) {					
        			case "points_redeemed":        			
        				   $transaction_amount = "-".Price_Formatter::convertToRaw($item->transaction_amount,0);
						   $transaction_type = 'debit';
        				break;      			
					default:
					       $transaction_amount = "+".Price_Formatter::convertToRaw($item->transaction_amount,0);		  		        			
						   $transaction_type = 'credit';
					    break;      			
        		} 

				$data[] = [
					'transaction_date'=>Date_Formatter::dateTime($item->transaction_date),
					'transaction_type'=>$transaction_type,
					'transaction_description'=>$description,
					'transaction_amount'=>$transaction_amount,
				];
			}
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'page_raw'=>$page_raw,
				'page_count'=>$page_count,
				'data'=>$data
			];
		} else $this->msg = t("No results");
		$this->responseJson();
	}

	public function actiongetPointsTransactionMerchant()
	{
		$data = array(); $card_id = 0;
		try {	
		    $card_id = CWallet::getCardID(Yii::app()->params->account_type['customer_points'],Yii::app()->user->id);				
		} catch (Exception $e) {
		    $this->msg = t("Invalid card id");
			$this->responseJson();
		}
				
		$limit = 20;
		$page = intval(Yii::app()->input->post('page'));				
		$page_raw = intval(Yii::app()->input->post('page'));
		if($page>0){
			$page = $page-1;
		}
		
		$criteria=new CDbCriteria();		
		$sql = "
		SELECT
        a.reference_id1 as merchant_id, b.restaurant_name,
			SUM(CASE WHEN a.transaction_type = 'points_earned' THEN a.transaction_amount ELSE -transaction_amount END) AS total_earning
		FROM
			{{wallet_transactions}} a

		left JOIN (
		  SELECT merchant_id,restaurant_name FROM {{merchant}}
		) b 
		on a.reference_id1 = b.merchant_id

		WHERE a.card_id =".q($card_id)."

		GROUP BY
		   a.reference_id1;	
		ORDER BY b.restaurant_name ASC
		";

		$criteria->alias="a";
		$criteria->select = "
		a.reference_id1 as merchant_id, b.restaurant_name,
		SUM(CASE WHEN a.transaction_type = 'points_earned' THEN a.transaction_amount ELSE -transaction_amount END) AS total_earning
		";
		$criteria->join = "
		left JOIN (
			SELECT merchant_id,restaurant_name FROM {{merchant}}
		) b 
		ON a.reference_id1 = b.merchant_id
		";
		$criteria->condition = "a.card_id = :card_id";
		$criteria->params = [
			':card_id'=>$card_id
		];
		$criteria->group = "a.reference_id1";
		$criteria->order = "b.restaurant_name ASC";
		
		$count = AR_wallet_transactions::model()->count($criteria); 
		$pages=new CPagination($count);
		$pages->pageSize=$limit;
		$pages->setCurrentPage( $page );        
		$pages->applyLimit($criteria);
		$page_count = $pages->getPageCount();

		if($page>0){
			if($page_raw>$page_count){
				$this->code = 3;
				$this->msg = t("end of results");
				$this->responseJson();
			}
		}
		
        $models = AR_wallet_transactions::model()->findAll($criteria);		
		if($models){			
			foreach ($models as $item) {
				$merchant_ids[]=$item->merchant_id;
				$total = $item->total_earning;
				if($item->merchant_id<=0){
					$total = $total<=0? (-1*$total) :$total;
				}
				$data[] = [					
					'merchant_id'=>$item->merchant_id,
					'restaurant_name'=>!empty($item->restaurant_name)?$item->restaurant_name:t("Global points"),
					'total_earning'=>Price_Formatter::convertToRaw($total,0),					
				];			
			}
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'page_raw'=>$page_raw,
				'page_count'=>$page_count,
				'data'=>$data
			];
		} else $this->msg = t("No results");
		$this->responseJson();
	}

	public function actiongetcartpoints()
	{		
		try {		

			$cart_uuid = trim(Yii::app()->input->post('cart_uuid'));
			$currency_code = trim(Yii::app()->input->post('currency_code'));
			$base_currency = Price_Formatter::$number_format['currency_code'];
			$exchange_rate = 1; $exchange_rate_to_merchant = 1;

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
            $multicurrency_enabled = $multicurrency_enabled==1?true:false;		
			$merchant_id = CCart::getMerchantId($cart_uuid);
			$options_merchant = OptionsTools::find(['merchant_default_currency'],$merchant_id);						
		    $merchant_default_currency = isset($options_merchant['merchant_default_currency'])?$options_merchant['merchant_default_currency']:'';
			$merchant_default_currency = !empty($merchant_default_currency)?$merchant_default_currency:$base_currency;
			$currency_code = !empty($currency_code)?$currency_code: (empty($merchant_default_currency)?$base_currency:$merchant_default_currency) ;		
						
			if($multicurrency_enabled){
				Price_Formatter::init($currency_code);
				$exchange_rate = CMulticurrency::getExchangeRate($base_currency,$currency_code);	
				$exchange_rate_to_merchant = CMulticurrency::getExchangeRate($merchant_default_currency,$currency_code);								
		    }

			$redemption_policy = isset(Yii::app()->params['settings']['points_redemption_policy'])?Yii::app()->params['settings']['points_redemption_policy']:'universal';			

			$total = CPoints::getAvailableBalancePolicy(Yii::app()->user->id,$redemption_policy,$merchant_id);

			$attrs = OptionsTools::find(['points_redeemed_points','points_redeemed_value','points_maximum_redeemed']);			
			$points_maximum_redeemed = isset($attrs['points_maximum_redeemed'])? floatval($attrs['points_maximum_redeemed']) :0;			

			$points_redeemed_points = isset($attrs['points_redeemed_points'])? floatval($attrs['points_redeemed_points']) :0;			
			
			$amount = $points_redeemed_points * (1/$points_redeemed_points);			
			$amount = ($amount*$exchange_rate);
									
			$redeem_discount = t("Get {amount} off for every {points} points",[
				'{amount}'=>Price_Formatter::formatNumber( ($amount) ),
				'{points}'=>$points_redeemed_points
			]);												

			$redeem_label = '';
			if($total>0){
				$redeem_label = t("Your available balance is {points} points.",[
					'{points}'=>$total
				]);
				if($points_maximum_redeemed>0 && $total>$points_maximum_redeemed){
					$redeem_label = t("Redeem {max} out of {points} points",[
						'{max}'=>$points_maximum_redeemed,
						'{points}'=>$total
					]);
				}
			}			

			$discount = 0; $points = 0;
			if($model = CCart::getAttributes($cart_uuid,'point_discount')){
				$discount_raw = !empty($model->meta_id)?json_decode($model->meta_id,true):false;				
				$discount = floatval($discount_raw['value'])*$exchange_rate_to_merchant;
				$points = floatval($discount_raw['points']);

				CCart::getContent($cart_uuid,Yii::app()->language);	
				$subtotal = CCart::getSubTotal();
				$sub_total = floatval($subtotal['sub_total']) * $exchange_rate_to_merchant;
				$total_after_discount = floatval($sub_total) - floatval( CCart::cleanNumber($discount) );				
				if($total_after_discount<=0){
					CCart::deleteAttributes($cart_uuid,'point_discount');
					$discount = 0; $points = 0;
				}
			}

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'total'=>$total,
				'redeem_discount'=>$redeem_discount,				
				'redeem_label'=>$redeem_label,
				'discount'=>(-1*$discount),
				'discount_label'=>t("Discount Applied: {amount} off using {points} points.",
				  [
					'{amount}'=>Price_Formatter::formatNumber($discount),
					'{points}'=>$points
				]),
				'redeemed_points'=>$points_redeemed_points
			];
		} catch (Exception $e) {
		    $this->msg= t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actionapplyPoints()
	{
		try {

			$cart_uuid = trim(Yii::app()->input->post('cart_uuid'));
			$currency_code = trim(Yii::app()->input->post('currency_code'));	
			$base_currency = Price_Formatter::$number_format['currency_code'];		
			$points = floatval(Yii::app()->input->post('points'));
			$merchant_id = 0;

			try {
				$merchant_id = CCart::getMerchantId($cart_uuid);
			} catch (Exception $e) {}			
						
			$redemption_policy = isset(Yii::app()->params['settings']['points_redemption_policy'])?Yii::app()->params['settings']['points_redemption_policy']:'universal';			
			$balance = CPoints::getAvailableBalancePolicy(Yii::app()->user->id,$redemption_policy,$merchant_id);			
			
			if($points>$balance){
				$this->msg = t("Insufficient balance");
				$this->responseJson();		
			}
			
			$attrs = OptionsTools::find(['points_redeemed_points','points_redeemed_value','points_maximum_redeemed','points_minimum_redeemed']);			
			$points_maximum_redeemed = isset($attrs['points_maximum_redeemed'])? floatval($attrs['points_maximum_redeemed']) :0;
			$points_minimum_redeemed = isset($attrs['points_minimum_redeemed'])? floatval($attrs['points_minimum_redeemed']) :0;			
			$points_redeemed_points = isset($attrs['points_redeemed_points'])? floatval($attrs['points_redeemed_points']) :0;
			
			if($points_maximum_redeemed>0 && $points>$points_maximum_redeemed){
				$this->msg = t("Maximum points for redemption: {points} points.",[
					'{points}'=>$points_maximum_redeemed
				]);
				$this->responseJson();				
			} 
			if($points_minimum_redeemed>0 && $points<$points_minimum_redeemed){
				$this->msg = t("Minimum points for redemption: {points} points.",[
					'{points}'=>$points_minimum_redeemed
				]);
				$this->responseJson();				
			} 

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
            $multicurrency_enabled = $multicurrency_enabled==1?true:false;		

			$merchant_id = CCart::getMerchantId($cart_uuid);
		 	$options_merchant = OptionsTools::find(['merchant_default_currency'],$merchant_id);						
		    $merchant_default_currency = isset($options_merchant['merchant_default_currency'])?$options_merchant['merchant_default_currency']:'';
			$merchant_default_currency = !empty($merchant_default_currency)?$merchant_default_currency:$base_currency;
			
			$currency_code = !empty($currency_code)?$currency_code: (empty($merchant_default_currency)?$base_currency:$merchant_default_currency) ;	

			$exchange_rate = 1; $exchange_rate_to_merchant = 1; $admin_exchange_rate=1;
			if(!empty($currency_code) && $multicurrency_enabled){
				$exchange_rate = CMulticurrency::getExchangeRate($base_currency,$merchant_default_currency);				
				$exchange_rate_to_merchant = CMulticurrency::getExchangeRate($merchant_default_currency,$currency_code);												
			}
															
			$discount = $points * (1/$points_redeemed_points);
			$discount = $discount *$exchange_rate;
			$discount2 = $discount *$exchange_rate_to_merchant;

			CCart::setExchangeRate($exchange_rate_to_merchant);
			CCart::getContent($cart_uuid,Yii::app()->language);	
			$subtotal = CCart::getSubTotal();
			$sub_total = floatval($subtotal['sub_total']);
			$total = floatval($sub_total) - floatval($discount2);			
			if($total<=0){
				$this->msg = t("Discount cannot be applied due to total less than zero after discount");				
				$this->responseJson();				
			}			
			$params = [
				'name'=>"Less Points",
				'type'=>"points_discount",
				'target'=>"subtotal",
				'value'=>-$discount,
				'points'=>$points
			];			
			CCart::savedAttributes($cart_uuid,'point_discount', json_encode($params));
			$this->code = 1;
			$this->msg = "Ok";
			
		} catch (Exception $e) {
		    $this->msg= t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actionremovePoints()
	{
		try {

			$cart_uuid = trim(Yii::app()->input->post('cart_uuid'));
			CCart::deleteAttributesAll($cart_uuid,['point_discount']);
			$this->code = 1;
			$this->msg = "ok";

		} catch (Exception $e) {
		    $this->msg= t($e->getMessage());		    
		}	
		$this->responseJson();
	}	

	public function actiongetpaydelivery()
	{
		try {
			$data = CPayments::PayondeliveryList();			
			$this->code = 1;
            $this->msg = "Ok";
            $this->details = [
                'data'=>$data
            ];
		} catch (Exception $e) {
		    $this->msg= t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actionsavedPaydelivery()
	{
		try {

			$payment_id = Yii::app()->input->post('payment_id');
			$payment_code = Yii::app()->input->post('payment_code');
			$merchant_id = Yii::app()->input->post('merchant_id');
			
			if($payment_id<=0){
                $this->msg[] = t("Payment is required");
                $this->jsonResponse();
            }

			$payment = AR_payment_gateway::model()->find('payment_code=:payment_code', 
		    array(':payment_code'=>$payment_code)); 
            if($payment){
                $data = CPayments::getPayondelivery($payment_id);
                $model = new AR_client_payment_method;
                $model->scenario = "insert";
                $model->client_id = Yii::app()->user->id;
				$model->payment_code = $payment_code;
				$model->as_default = intval(1);
                $model->reference_id = $payment_id;
                $model->attr1 = $payment->payment_name;
                $model->attr2 = $data->payment_name;
                $model->merchant_id = intval($merchant_id);                
                if($model->save()){
					$this->code = 1;
		    		$this->msg = t("Succesful");
				} else $this->msg = CommonUtility::parseError($model->getErrors());                
            } else $this->msg = t("Payment already exist");

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actiongetAllergenInfo()
	{
		try {

			$item_id = Yii::app()->input->post("item_id");
			$merchant_id = Yii::app()->input->post("merchant_id");
			
			$allergen = CMerchantMenu::getAllergens($merchant_id, $item_id );	
			$allergen_data = AttributesTools::adminMetaList('allergens',Yii::app()->language,true);			

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'allergen'=>$allergen,
				'allergen_data'=>$allergen_data,
			];

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actiongetBankDeposit()
	{
		try {

			$data = [];
			$order_uuid = Yii::app()->input->post("order_uuid");			
			$order = COrders::get($order_uuid);			
			Price_Formatter::init($order->use_currency_code);

			$order_info = [
				'order_id'=>$order->order_id,
				'total'=>Price_Formatter::formatNumber($order->total),
			];

			$model = AR_bank_deposit::model()->find("deposit_type=:deposit_type AND transaction_ref_id=:transaction_ref_id",[
				':deposit_type'=>"order",
				':transaction_ref_id'=>$order->order_id
			]);

			if($model){
				$data  = [
					'account_name'=>$model->account_name,
					'amount'=>$model->amount>0?Price_Formatter::convertToRaw($model->amount):0,
					'reference_number'=>$model->reference_number,
				];
			} 

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'data'=>$data,
				'order_info'=>$order_info
			];

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actionuploadBankDeposit()
	{
		try {

			$client_id = Yii::app()->user->id;	
			$order_uuid = Yii::app()->input->post("order_uuid");
			$account_name = Yii::app()->input->post("account_name");
			$amount = Yii::app()->input->post("amount");
			$reference_number = Yii::app()->input->post("reference_number");
			$reference_number = $reference_number=="undefined"?"":$reference_number;
			$filename = Yii::app()->input->post("filename");
			

			$file_data = Yii::app()->input->post("file_data");
			$image_type = Yii::app()->input->post("image_type");
			$image_type = !empty($image_type)?$image_type:'png';
			
			$order = COrders::get($order_uuid);
			
			$model = AR_bank_deposit::model()->find("deposit_type=:deposit_type AND transaction_ref_id=:transaction_ref_id",[
				':deposit_type'=>"order",
				':transaction_ref_id'=>$order->order_id
			]);
			if(!$model){
				$model = new AR_bank_deposit;
				$model->scenario = "add";
			} else $model->scenario = "update";

			$file_uuid = CommonUtility::createUUID("{{bank_deposit}}",'deposit_uuid');						

			$model->account_name = $account_name;
			$model->amount = $amount;
			$model->reference_number = $reference_number;
			$model->transaction_ref_id = $order->order_id;			
			$model->deposit_uuid = $file_uuid;
			$model->merchant_id = $order->merchant_id;	

			$model->path = "upload/deposit";			
			
			$model->use_currency_code = $order->use_currency_code;
			$model->base_currency_code = $order->base_currency_code;
			$model->admin_base_currency = $order->admin_base_currency;
			$model->exchange_rate = $order->exchange_rate;
			$model->exchange_rate_merchant_to_admin = $order->exchange_rate_merchant_to_admin;
			$model->exchange_rate_admin_to_merchant = $order->exchange_rate_admin_to_merchant;
			
			if(!empty($filename)){
				$model->proof_image = $filename;
			} else {
				if(!empty($file_data)){
					$result = [];
					try {
						$result = CImageUploader::saveBase64Image($file_data,$image_type,"upload/deposit");				
						$model->proof_image = isset($result['filename'])?$result['filename']:'';
						$model->path = isset($result['path'])?$result['path']:'';			
					} catch (Exception $e) {
						$this->msg = t($e->getMessage());
						$this->responseJson();
					}
				}
			}
			
			if($model->validate()){	
				if($model->save()){
					$this->code = 1;
					$this->msg = t("You succesfully upload bank deposit. Please wait while we validate your payment");
				} else {
					$this->msg = CommonUtility::parseError( $model->getErrors() );
				}
	   	    } else {				
				$this->msg = CommonUtility::parseError( $model->getErrors() );
			}

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actionuploadProofPayment()
	{
		try {

			$upload_uuid = CommonUtility::generateUIID();
			$allowed_extension = explode(",",  Yii::app()->params['upload_type']);
		    $maxsize = (integer) Yii::app()->params['upload_size'] ;

			if (!empty($_FILES)) {

				$title = $_FILES['file']['name'];   
			    $size = (integer)$_FILES['file']['size'];   
			    $filetype = $_FILES['file']['type'];  


				if(isset($_FILES['file']['name'])){
					$extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
				} else $extension = strtolower(substr($title,-3,3));
				
				if(!in_array($extension,$allowed_extension)){			
					$this->msg = t("Invalid file extension");
					$this->responseJson();
				}
				if($size>$maxsize){
					$this->msg = t("Invalid file size");
					$this->responseJson();
				}

				$upload_path = "upload/deposit";

				$tempFile = $_FILES['file']['tmp_name'];   								
			    $upload_uuid = CommonUtility::createUUID("{{media_files}}",'upload_uuid');
			    $filename = $upload_uuid.".$extension";						
			    $path = CommonUtility::uploadDestination($upload_path)."/".$filename;	
				
				$image_set_width = isset(Yii::app()->params['settings']['review_image_resize_width']) ? intval(Yii::app()->params['settings']['review_image_resize_width']) : 0;
			    $image_set_width = $image_set_width<=0?300:$image_set_width;

				$image_driver = !empty(Yii::app()->params['settings']['image_driver'])?Yii::app()->params['settings']['image_driver']:Yii::app()->params->image['driver'];			
			    $manager = new ImageManager(array('driver' => $image_driver ));								
			    $image = $manager->make($tempFile);
			    $image_width = $manager->make($tempFile)->width();

				if($image_width>$image_set_width){
					$image->resize(null, $image_set_width, function ($constraint) {
						$constraint->aspectRatio();
					});
					$image->save($path);
				} else {
					$image->save($path,60);
				}				

				$this->code = 1; $this->msg = "OK";			
				$this->details = array(			   			   
				  'url_image'=>CMedia::getImage($filename,$upload_path),
				  'filename'=>$filename,
				  'id'=>$upload_uuid			   
				);					
			} else $this->msg = t("Invalid file");

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actionregisterGuestUser()
	{
		try {

			$social_strategy  = "guest";

			$options = OptionsTools::find(array('signup_enabled_verification','signup_enabled_capcha'));			
			$signup_enabled_capcha = isset($options['signup_enabled_capcha'])?$options['signup_enabled_capcha']:false;
			$capcha = $signup_enabled_capcha==1?true:false;
			
			$enabled_verification = isset($options['signup_enabled_verification'])?$options['signup_enabled_verification']:false;
			$verification = $enabled_verification==1?true:false;

			$recaptcha_response = isset($this->data['recaptcha_response'])?$this->data['recaptcha_response']:'';
									
			$firstname = isset($this->data['first_name'])?$this->data['first_name']:'';			
			$lastname = isset($this->data['last_name'])?$this->data['last_name']:'';
			$email_address = isset($this->data['email_address'])?$this->data['email_address']:'';			
			$prefix = isset($this->data['mobile_prefix'])?$this->data['mobile_prefix']:'';
			$mobile_number = isset($this->data['mobile_number'])?$this->data['mobile_number']:'';
			$password = isset($this->data['password'])?$this->data['password']:'';
			$cpassword = isset($this->data['cpassword'])?$this->data['cpassword']:'';

			$model = new AR_clientsignup();
		    $model->scenario = $social_strategy;
			$model->capcha = false;
			$model->recaptcha_response = $recaptcha_response;

			if(!empty($password) || !empty($email_address)){
				
				$model2 = new AR_clientsignup();
				$model2->scenario = "guest_with_account";
				$model2->first_name = $firstname;
				$model2->last_name = $lastname;
				$model2->contact_phone = $prefix.$mobile_number;
				$model2->email_address = $email_address;
				$model2->guest_password = $password;
				$model2->cpassword = $cpassword;
				$model2->password = $password;
				$model2->social_strategy = "web";		
				$model2->merchant_id = 0;	
				$model2->capcha = $capcha;
			    $model2->recaptcha_response = $recaptcha_response;		
				
				$digit_code = CommonUtility::generateNumber(3,true);			
				$model2->mobile_verification_code = $digit_code;
				
				if($verification==1 || $verification==true){
					$model2->status='pending'; 
				} 			
				if($model2->save()){
					if($verification==1 || $verification==true){
						$this->code = 1;
						$this->msg = t("Please wait until we redirect you");	
						$this->details = [
							'uuid'=>$model2->client_uuid,							
						];
					} else {
						$login=new AR_customer_login;
						$login->username = $email_address;
						$login->password = $password;
						$login->rememberMe = 1;	
						if($login->validate() && $login->login() ){
							$this->code = 1;
							$this->msg = t("Registration successful");
							$this->userData();
						} else $this->msg = t("Login failed");
					}
				} else $this->msg = CommonUtility::parseError( $model2->getErrors() );
			} else {
				$model->first_name = $firstname;
				$model->last_name = $lastname;
				$model->phone_prefix = $prefix;
				$model->contact_phone = $prefix.$mobile_number;
				
				$username = CommonUtility::uuid()."@gmail.com";				
				$password = CommonUtility::generateAplhaCode(20);
				$model->social_strategy = $social_strategy;
				$model->password = $password;
				$model->email_address = $username;				
				if($model->save()){
					$login=new AR_customer_login;
					$login->username = $model->email_address;
					$login->password = $password;
					$login->rememberMe = 1;						
					if($login->validate() && $login->login() ){
						$this->code = 1;
						$this->msg = t("Registration successful");
						$this->userData();			
					} else $this->msg = t("Login failed");
				} else $this->msg = CommonUtility::parseError( $model->getErrors() );
			}
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actionSaveAddress()
	{
		try {
			
			$address_uuid = isset($this->data['address_uuid'])?trim($this->data['address_uuid']):'';
			$address1 = Yii::app()->input->post('address1');
			$formatted_address = Yii::app()->input->post('formatted_address');
			$location_name = Yii::app()->input->post('location_name');
			$delivery_instructions = Yii::app()->input->post('delivery_instructions');
			$delivery_options = Yii::app()->input->post('delivery_options');
			$address_label = Yii::app()->input->post('address_label');
			$latitude = Yii::app()->input->post('latitude');
			$longitude = Yii::app()->input->post('longitude');
			$place_id = Yii::app()->input->post('place_id');
			
			if(!empty($address_uuid)){
				$model = AR_client_address::model()->find('address_uuid=:address_uuid AND client_id=:client_id', 
		        array(':address_uuid'=>$address_uuid,'client_id'=>Yii::app()->user->id)); 
			} else {
				$model = AR_client_address::model()->find('place_id=:place_id AND client_id=:client_id', 
		        array(':place_id'=>$place_id,'client_id'=>Yii::app()->user->id));				
			}			
			
			if(!$model){		    
				$model = new AR_client_address;		    	
			}

			$model->client_id = intval(Yii::app()->user->id);
			$model->address1 = $address1;
			$model->formatted_address = $formatted_address;
			$model->location_name = $location_name;
			$model->delivery_instructions = $delivery_instructions;
			$model->delivery_options = $delivery_options;
			$model->address_label = $address_label;
			$model->latitude = $latitude;
			$model->longitude = $longitude;
			$model->place_id = $place_id;
			if($model->save()){
				$this->code = 1;
				$this->msg = "OK";
			} else $this->msg = CommonUtility::parseError( $model->getErrors() );			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actionvalidateCartItems()
	{
		try {

			$item_id = Yii::app()->input->post("item_id");
			$cart_uuid = Yii::app()->input->post("cart_uuid");

			$result = CCart::validateFoodItems($item_id,$cart_uuid,Yii::app()->language);	
			$plural1 = "We regret to inform you that the {food_items} you had in your cart is no longer available.";		
			$plural2 = "We regret to inform you that the following item(s) {food_items} you had in your cart is no longer available.";		
			$message = Yii::t('front', "$plural1|$plural2",
			  array($result['count'], '{food_items}' => $result['item_line'])
		    );
			$this->code = 1;
			$this->msg = $message;
			$this->details = $result;

			// REMOVE ITEM FROM CART
			$model = AR_cart::model()->find("cart_uuid=:cart_uuid AND item_token=:item_token",[
				':cart_uuid'=>$cart_uuid,
				':item_token'=>$item_id
			]);
			if($model){
				$model->delete();
			}

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actiongetTableAttributes()
	{
		try {
			
			$merchant_uuid = Yii::app()->input->post('merchant_uuid');			
			$merchant = CMerchants::getByUUID($merchant_uuid);
			$merchant_id = $merchant->merchant_id;

			$atts = OptionsTools::find(['booking_enabled'],$merchant_id);
			$booking_enabled = isset($atts['booking_enabled'])?$atts['booking_enabled']:false;
			$booking_enabled = $booking_enabled==1?true:false;

			$room_list = [];
 		    $room_list = CommonUtility::getDataToDropDown("{{table_room}}","room_uuid","room_name","WHERE merchant_id=".q($merchant_id)." ","order by room_name asc");                			
		    if(is_array($room_list) && count($room_list)>=1){
			   $room_list = CommonUtility::ArrayToLabelValue($room_list);   
		    }	

			$table_list = [];
			try{
			   $table_list = CBooking::getTableList($merchant_id);		
			} catch (Exception $e) {
			}

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'room_list'=>$room_list,
				'table_list'=>$table_list,
				'booking_enabled'=>$booking_enabled
			];
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}	
		$this->responseJson();
	}

}
// end class