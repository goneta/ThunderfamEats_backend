<?php
class AR_driver extends CActiveRecord
{	
    public $new_password,$confirm_password;
	public $myscenario;
	public $delivery_pay , $total_tips , $total_incentives, $total_credit ,$total_debit ,$wallet_balance;
	public $code;
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
		return '{{driver}}';
	}
	
	public function primaryKey()
	{
	    return 'driver_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'driver_id'=>t("ID"),
            'first_name'=>t("First name"),
            'last_name'=>t("Last name"),
            'phone_prefix'=>t("Phone prefix"),
			'new_password'=>t("Password"),
            'password'=>t("Password"),
            'photo'=>t("Photo"),
            'team_id'=>t("Team ID"),
            'license_number'=>t("License number"),
            'license_expiration'=>t("License expiration  (YYYY/mm/dd)"),
            'license_front_photo'=>t("License front photo"),
            'license_back_photo'=>t("License back photo"),
            'status'=>t("Status"),
            'confirm_password'=>t("Confirm password"),
			'address'=>t("Complete address"),
			'salary_type'=>t("Salary type"),
			'salary'=>t("Salary amount"),
			'commission'=>t("Commission amount"),
			'employment_type'=>t("Employment Type"),
			'fixed_amount'=>t("Fixed amount"),
			'commission_type'=>t("Commission type"),
			'incentives_amount'=>t("Incentives amount (per delivery)"),
			'allowed_offline_amount'=>t("Maximum cash amount that can collect"),
			'code'=>t("Enter verification code")
		);
	}
	
	public function rules()
	{
		return array(
		  array('first_name,last_name,email,phone_prefix,phone,status,salary_type,employment_type,address', 
		  'required','message'=> t( Helper_field_required2 ) ),	
          
          array('license_number,license_expiration,license_front_photo,license_back_photo', 
		  'required','message'=> t( Helper_field_required2 ) ,'on'=>'add_license' ),	

		  array('license_number,license_expiration,license_front_photo,license_back_photo,path,path_license,is_online', 
		  'safe','on'=>'insert,update' ),	
		  
		  array('first_name,last_name,email', 'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')), 		  
		  
		  array('driver_uuid,color_hex,verification_code,account_verified,verify_code_requested,reset_password_request,
		  date_created,date_modified,ip_address,notification,salary,commission,commission_type,fixed_amount,incentives_amount,allowed_offline_amount,default_currency','safe'),		  

          array('email,phone','unique','message'=>t(Helper_field_unique)),

          array('new_password,confirm_password', 
		  'required','message'=> t( Helper_field_required2 ) ,'on'=>'insert' ),	

          array('confirm_password', 'compare', 'compareAttribute'=>'new_password'),       
		  
		  array('new_password, confirm_password', 'length', 'min'=>4, 'max'=>40,
              'tooShort'=>t("{attribute} is too short (minimum is 4 characters).")  
           ),    
            
		  array('license_expiration','validateExpiration' , 'on'=>"attach_license,attach_license_website" ),

		  array('salary,commission,fixed_amount,incentives_amount,allowed_offline_amount', 'numerical', 'integerOnly'=>false),

		  array('salary,commission,fixed_amount,incentives_amount,allowed_offline_amount', 
		  'required','message'=> t( Helper_field_required2 ) ),	
		  
		  array('new_password,confirm_password', 
		  'required','message'=> t( Helper_field_required2 ) ,'on'=>'reset_password' ),	
		  
		  array('license_expiration', 'type', 'type' => 'date', 'message' => '{attribute}: is not a valid!', 
			'dateFormat' => 'yyyy/MM/dd',
			'on'=>"attach_license" 
		  ),

		  array('code', 'required','message'=> t( "Verification code is required" ) ,'on'=>'verify_code' ),	
		  array('code','numerical','integerOnly'=>true, 'message'=>  t("Verification code must be a number.") ),
		  array('code','validateCode' , 'on'=>"verify_code" ),

		  array('license_number,license_expiration', 
		  'required','message'=> t( Helper_field_required2 ),'on'=>'attach_license_website' ),	

		  array('license_front_photo,license_back_photo,path,path_license', 
		  'safe','on'=>'attach_license_website' ),	

		  array('license_front_photo,license_back_photo', 'file', 'types'=>'jpg, gif, png,jpeg',  'safe' => false , 'on'=>"attach_license_website"),
		  
		);
	}

	public function validateCode($attribute, $params)
	{
		if($this->code != $this->verification_code){
			$this->addError('code',t("Invalid 4 digit code"));
		}
	}
	
	public function validateExpiration($attribute, $params)
	{
		$now = date("Y-m-d");		
		if($now>$this->license_expiration){
			$this->addError('license_expiration',t("Expiration is invalid"));
		}
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){

			// if(DEMO_MODE){				
			// 	return false;
			// }

			if($this->isNewRecord){
				$this->password = md5($this->new_password);
				$this->date_created = CommonUtility::dateNow();	
				
				$options = OptionsTools::find([
					'driver_employment_type','driver_salary_type','driver_salary','driver_fixed_amount','driver_commission',
					'driver_commission_type','driver_incentives_amount','driver_maximum_cash_amount_limit'
				]);

				if($this->myscenario=="register"){				
					$this->employment_type = isset($options['driver_employment_type'])?$options['driver_employment_type']:'employee';
					$this->salary_type = isset($options['driver_salary_type'])?$options['driver_salary_type']:'salary';
					$this->salary = isset($options['driver_salary'])? floatval($options['driver_salary']) :0;
					$this->fixed_amount = isset($options['driver_fixed_amount'])? floatval($options['driver_fixed_amount']) :0;
					$this->commission = isset($options['driver_commission'])? floatval($options['driver_commission']) :0;
					$this->commission_type = isset($options['driver_commission_type'])?$options['driver_commission_type']:'percentage';
					$this->incentives_amount = isset($options['driver_incentives_amount'])? floatval($options['driver_incentives_amount']) :0;
					$this->allowed_offline_amount = isset($options['driver_maximum_cash_amount_limit'])? floatval($options['driver_maximum_cash_amount_limit']) :0;
				}
				
			} else {				
				if(!empty($this->new_password)){
					$this->password = md5($this->new_password);
				}
				$this->date_modified = CommonUtility::dateNow();
			}

			if(empty($this->driver_uuid)){
				$this->driver_uuid = CommonUtility::createUUID("{{driver}}",'driver_uuid');
			}

			if(empty($this->default_currency)){
				$this->default_currency = Price_Formatter::$number_format['currency_code'];
			}
			
			$this->ip_address = CommonUtility::userIp();	

			if(!empty($this->phone_prefix)){
				$this->phone_prefix = str_replace("+","",$this->phone_prefix);
			}			
			
			if(empty($this->license_expiration)){
				$this->license_expiration = null;
			}
			
			return true;
		} else return true;
	}
	
	protected function afterSave()
	{
		parent::afterSave();

		Yii::import('ext.runactions.components.ERunActions');	
		$cron_key = CommonUtility::getCronKey();		
		$get_params = array( 
		   'driver_uuid'=> $this->driver_uuid,
		   'key'=>$cron_key,
		   'language'=>Yii::app()->language
		);								

		// ADD WALLET CARDS
		try {
			CWallet::createCard( Yii::app()->params->account_type['driver'] , $this->driver_id );
		} catch (Exception $e) {			
		}	
				
		switch ($this->myscenario) {
			case 'register':				
				CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/afterdriver_register?".http_build_query($get_params) );				
				break;
			case "change_online_status":				
				$noti = new AR_notifications; 
				$noti->notication_channel = Yii::app()->params->realtime['admin_channel'] ;
				$noti->notification_event = Yii::app()->params->realtime['notification_event'];						
				$noti->notification_type = "silent";
				$noti->message = 'driver change online status';
				$noti->visible = 0;						
				if(!$noti->save()){
					//dump($noti->getErrors());
				}							
				break;
		}

		switch ($this->scenario) {
			case 'request_resetpassword':	
			case "resend_resetpassword":				
				CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/afterdriver_requestresetpassword?".http_build_query($get_params) );				
				break;	

			case "send_otp":						
				CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/afterdriver_register?".http_build_query($get_params) );				
				break;	
		}

		CCacheData::add();
	}

	protected function afterDelete()
	{
		parent::afterDelete();		

		AR_driver_break::model()->deleteAll("driver_id=:driver_id",array(
			':driver_id'=> (integer) $this->driver_id ,			
		));

		AR_driver_meta::model()->deleteAll("reference_id=:reference_id",array(
			':reference_id'=> (integer) $this->driver_id ,			
		));

		AR_driver_payment_method::model()->deleteAll("driver_id=:driver_id",array(
			':driver_id'=> (integer) $this->driver_id ,			
		));
		
		AR_driver_schedule::model()->deleteAll("driver_id=:driver_id",array(
			':driver_id'=> (integer) $this->driver_id ,			
		));

		AR_driver_vehicle::model()->deleteAll("driver_id=:driver_id",array(
			':driver_id'=> (integer) $this->driver_id ,			
		));

		//
		CCacheData::add();
	}

	protected function beforeDelete()
	{				
	    if(DEMO_MODE){	
		    return false;
		}
	    return true;
	}
		
}
/*end class*/