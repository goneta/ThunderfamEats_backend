<?php
class AR_client_booking extends CActiveRecord
{	
	
	public $cpassword;
	public $old_password;
	public $image;
	public $capcha;
	public $recaptcha_response;

	public $google_client_id;
	public $captcha_secret;
	public $merchant_id;
		
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
		    'image'=>t("Profile Photo")
		);
	}
	
	public function rules()
	{		
        return array(
            array('merchant_id,first_name,last_name,email_address,phone_prefix,contact_phone', 
		     'required','message'=> t( Helper_field_required ) ),

             array('first_name,last_name,email_address,phone_prefix,contact_phone', 
             'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')), 

             array('email_address,contact_phone','ext.UniqueAttributesValidator','with'=>'merchant_id',
		      'message'=>t(Helper_field_unique)
		     ),            

             array('contact_phone','length', 'min'=>8, 'max'=>15,
               'tooShort'=>t("{attribute} number is too short (minimum is 8 characters).")               
             ),

             array('email_address','email'),

             array('recaptcha_response','validateCapcha'),	

             array('contact_phone','validateBlockPhone'),

             array('email_address','validateBlockEmail')

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
		
    protected function beforeSave()
	{
		if(parent::beforeSave()){			
			if($this->isNewRecord){
				$this->social_strategy = Yii::app()->params['booking_tag'];
				$this->date_created = CommonUtility::dateNow();						
			} else {
				$this->date_modified = CommonUtility::dateNow();				
			}
			$this->ip_address = CommonUtility::userIp();	
			
			if(empty($this->client_uuid)){
				$this->client_uuid = CommonUtility::createUUID("{{client}}",'client_uuid');
			}            
			
			return true;
		} else return true;
	}
	
	protected function afterSave()
	{
		parent::afterSave();        		

        /*ADD CACHE REFERENCE*/
		CCacheData::add();
	}

	protected function afterDelete()
	{
		parent::afterDelete();		
        
        /*ADD CACHE REFERENCE*/
		CCacheData::add();
	}
	
		
}
/*end class*/
