<?php
class AR_clientsignup extends CActiveRecord
{	
	
	public $cpassword;
	public $old_password;
	public $image;
	public $capcha;
	public $recaptcha_response;

	public $google_client_id;
	public $captcha_secret;
	public $merchant_id;
	public $local_id;
	public $guest_email_address,$guest_password;

	public $custom_fields;
	public $validation_type;
		
	//public $social_token;
	
	/**
	 * Returns the static model of the specified AR class.
	 * @return static the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{client}}';
	}
	
	public function primaryKey()
	{
	    return 'client_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'first_name'=>t("First Name"),
		    'last_name'=>t("Last Name"),
		    'email_address'=>t("Email Address"),
		    'contact_phone'=>t("Contact Phone"),		    
		    'cpassword'=>t("Confirm Password"),
		    'image'=>t("Profile Photo"),
			'password'=>t("Password"),
			'guest_password'=>t("Password"),
			'guest_email_address'=>t("Email Address"),
		);
	}
	
	public function rules()
	{
		return array(
		  array('phone_prefix,contact_phone,mobile_verification_code,status', 
		  'required','message'=> t( Helper_field_required ) ,'on'=>'registration_phone' ),
		  
		  array('phone_prefix,contact_phone,mobile_verification_code,status,
		  first_name,last_name,email_address', 
		  'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')), 
		  		 
		  array('phone_prefix,contact_phone,mobile_verification_code,status', 
		  'required','message'=> t( Helper_field_required ) , 'on'=>"complete_registration" ),
		  
		  //array('email_address,contact_phone','unique','message'=>t(Helper_field_unique)),
		  array('email_address,contact_phone','ext.UniqueAttributesValidator','with'=>'merchant_id',
		   'message'=>t(Helper_field_unique),'on'=>"register,guest_with_account,complete_registration"
		  ),            

		  array('contact_phone','length', 'min'=>8, 'max'=>15,
               'tooShort'=>t("{attribute} number is too short (minimum is 8 characters).")               
             ),
		  
		  array('email_address','email'),
		  		  
		  array('password', 'compare', 'compareAttribute'=>'cpassword',
              'message'=> t("Password and confirm password does not match") ,'on'=>'register' ),
              
          array('password', 'compare', 'compareAttribute'=>'cpassword',
              'message'=> t("Password and confirm password does not match") ,'on'=>'complete_registration' ),    
              
          array('password, cpassword', 'length', 'min'=>4, 'max'=>100,
              'tooShort'=>t("{attribute} is too short (minimum is 4 characters).")  
           ),    
            
          array('email_address,social_id,first_name,last_name,status', 
		  'required','message'=> t( Helper_field_required ) ,'on'=>'registration_social' ),  
		  
		  array('password','safe','on'=>'registration_social' ),
		  
		  array('email_address,first_name,last_name,status,password', 
		  'required','message'=> t( Helper_field_required ) ,'on'=>'register' ),  

		  array('recaptcha_response','validateCapcha'),	  
		  
		  array('contact_phone','validateBlockPhone'),
		  
		  array('social_token','required','on'=>"social_login,registration_social",'message'=>t("Social token is empty")),
		  
		  array('social_token','validateSocialToken','on'=>'social_login,registration_social'),
		  
		  array('email_address','validateBlockEmail'),

		//   GUEST CHECKOUT
		  array('first_name,last_name,contact_phone', 
		  'required','message'=> t( Helper_field_required ) ,'on'=>'guest' ),

		  array('guest_email_address,guest_password,cpassword,custom_fields','safe' ),

		  array('email_address,first_name,last_name,guest_password,cpassword', 
		  'required','message'=> t( Helper_field_required ) ,'on'=>'guest_with_account' ),  

		  array('email_address,contact_phone','ext.UniqueAttributesValidator','with'=>'merchant_id',
		   'message'=>t(Helper_field_unique),'on'=>'guest_with_account'
		  ),            

		  array('guest_password', 'compare', 'compareAttribute'=>'cpassword',
              'message'=> t("Password and confirm password does not match") ,'on'=>'guest_with_account' ),
		  		  
		  //   GUEST CHECKOUT
		);
	}
	
	public function validateCapcha()
	{		
		if($this->capcha==1 || $this->capcha==TRUE){
			if(!empty($this->recaptcha_response)){
				try {						
															
					if(empty($this->captcha_secret)){
						$options = OptionsTools::find(array('captcha_secret'));
					    $captcha_secret = isset($options['captcha_secret'])?$options['captcha_secret']:'';													
					} else $captcha_secret = $this->captcha_secret;

					$resp = CRecaptcha::verify($captcha_secret,$this->recaptcha_response);					
				} catch (Exception $e) {
					$err = CRecaptcha::getError();
					if($err == "timeout-or-duplicate"){
						$this->addError('recaptcha_response',  t("Captcha expired please re-validate captcha") );
					} else $this->addError('recaptcha_response', $err );					
				}
			} else $this->addError('recaptcha_response', t("Please validate captcha") );
		}				
	}
	
	public function validateBlockPhone()
	{
		if($this->scenario=="registration_phone"){
			$options = OptionsTools::find(array('blocked_mobile'));
			$blocked_mobile = isset($options['blocked_mobile'])?$options['blocked_mobile']:'';
			$blocked_mobile = explode(",",$blocked_mobile);
			if(!empty($this->contact_phone)){
			if(in_array($this->contact_phone, (array) $blocked_mobile)){				
				$this->addError('contact_phone', t("Your phone number is not allowed to register.") );
			}
			}
		}
	}
	
	public function validateBlockEmail()
	{
		$options = OptionsTools::find(array('blocked_email_add'));
		$blocked = isset($options['blocked_email_add'])?$options['blocked_email_add']:'';
		$blocked = explode(",",$blocked);
		if(!empty($this->email_address)){
		if(in_array($this->email_address, (array) $blocked)){				
			$this->addError('email_address', t("Your email address is not allowed to register.") );
		}
		}
	}
	
	public function validateSocialToken()
	{			
		if($this->social_strategy=="facebook"){
			try {
				CSocialLogin::validateAccessToken( $this->social_token );
			} catch (Exception $e) {
				$this->addError('social_token', t($e->getMessage()) );
			}
		} else if ($this->social_strategy=="google") {
			try {
				if(!empty($this->google_client_id)){
					//
				} else {
					$options = OptionsTools::find(array('google_client_id'));
					$google_client_id = isset($options['google_client_id'])?$options['google_client_id']:''; 
					CSocialLogin::validateIDToken( $this->social_token , $google_client_id );
				}
			} catch (Exception $e) {
				$this->addError('social_token', t($e->getMessage()) );
			}
		}
	}
	
    protected function beforeSave()
	{
		if(parent::beforeSave()){			
			if($this->isNewRecord){
				$this->date_created = CommonUtility::dateNow();		
				$this->password = md5($this->password);				
			} else {
				$this->date_modified = CommonUtility::dateNow();
				if($this->scenario=="complete_registration"){
					$this->password = md5($this->password);
				}
			}
			$this->ip_address = CommonUtility::userIp();	
			
			if(empty($this->client_uuid)){
				$this->client_uuid = CommonUtility::createUUID("{{client}}",'client_uuid');
			}

			if(!empty($this->phone_prefix)){
				$this->phone_prefix = str_replace("+",'',$this->phone_prefix);
			}			
			if(!empty($this->contact_phone)){
			    $this->contact_phone = str_replace("+",'',$this->contact_phone);
			}
			
			return true;
		} else return true;
	}
	
	protected function afterSave()
	{
		parent::afterSave();	
				
		AR_client_meta::saveMeta($this->client_id,'app_push_notifications',1); 
		AR_client_meta::saveMeta($this->client_id,'promotional_push_notifications',1); 

		// ADD DEFAULT PAYMENT
		if($this->isNewRecord){
		   CPayments::addDefaultPayment($this->client_id);
		}

		// CUSTOM FIELDS		
		if(is_array($this->custom_fields) && count($this->custom_fields)>=1){
			foreach ($this->custom_fields as $key => $value) {
				$custom_custom_values = AR_user_custom_field_values::model()->find("user_id=:user_id AND field_id=:field_id",[
					':user_id'=>$this->client_id,
					':field_id'=>$key
				]);
				if($custom_custom_values){
					$custom_custom_values->value = is_array($value)?json_encode($value):$value;
					$custom_custom_values->save();
				} else {
					$custom_custom_values = new AR_user_custom_field_values();
					$custom_custom_values->user_id = $this->client_id;
					$custom_custom_values->field_id = $key;
					$custom_custom_values->value = is_array($value)?json_encode($value):$value;
					$custom_custom_values->save();
				}
			}			
		}

		Yii::import('ext.runactions.components.ERunActions');	
		$cron_key = CommonUtility::getCronKey();		
		

		if(!empty($this->validation_type)){
			$verification_type = $this->validation_type;
		} else {
			$verification_type = 'email';
			$options = OptionsTools::find(['signup_type']);
			$signup_type = isset($options['signup_type'])?$options['signup_type']:'';
			if($signup_type=="mobile_phone" && $this->scenario!='registration_social'){
				$verification_type = 'sms';
			}
	    }  		

		// SAVE LOCATION ADDRESS 
		if($this->scenario=="complete_registration" || $this->scenario=="register"){
			if(!empty($this->local_id)){
				try {
					// $location_data = CMaps::locationDetails($this->local_id,'' ,'');				
					// CCheckout::saveDeliveryAddress($this->local_id , $this->client_id , $location_data);				
				} catch (Exception $e) {}	
			}			
		}
		
		$get_params = array( 
		   'client_uuid'=> $this->client_uuid,
		   'key'=>$cron_key,
		   'verification_type'=>$verification_type,
		   'language'=>Yii::app()->language
		);		
								
		switch ($this->scenario) {
			case 'registration_phone':
			case 'resend_otp':	
			case 'register':
			case "registration_social":		
			case "guest_with_account":						
				CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/aftercustomersignup?".http_build_query($get_params) );
				break;	
				
			case 'complete_registration':		
			case 'complete_standard_registration':		
			case 'complete_social_registration':
			    CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/afterregistration?".http_build_query($get_params) );
			    break;	
			    
			case 'reset_password':  				
			    CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/after_requestresetpassword?".http_build_query($get_params) );
			    break;			
			case "reset_password_sms":
			case 'request_otp_sms':  						
				CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/request_otp_sms?".http_build_query($get_params) );
				break;			
			case 'request_otp_email':  					
				CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/request_otp_email?".http_build_query($get_params) );
				break;			
		}

		if($this->isNewRecord){
			$this->SaveNotificationSettings($this);
		}
			
	}

	protected function afterDelete()
	{
		parent::afterDelete();				
	}

	public function SaveNotificationSettings($model)
	{		
		AR_client_meta::saveMeta($model->client_id,'app_push_notifications',1);
		AR_client_meta::saveMeta($model->client_id,'app_sms_notifications',1);
		AR_client_meta::saveMeta($model->client_id,'offers_email_notifications',1);
		AR_client_meta::saveMeta($model->client_id,'promotional_push_notifications',1);
	}
		
}
/*end class*/
