<?php
require 'intervention/vendor/autoload.php';
require 'php-jwt/vendor/autoload.php';
use Intervention\Image\ImageManager;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class DriverController extends DriverCommon
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

	public function missingAction($actionID)
	{
		$this->msg = t("Method not found {method}",[
			'{method}'=>$actionID
		]);
		$this->responseJson();
	}

    public function actionIndex()
    {
		echo "API Index";
    }

	public function actiongetsettings()
	{
		try {

			$maps_config = CMaps::config();
			$maps_config['note_icon']=websiteDomain().Yii::app()->theme->baseUrl."/assets/icons/note.png";
			$maps_config = JWT::encode($maps_config, CRON_KEY, 'HS256');

			$phone_settings = [];
			$phone_data = ClocationCountry::getPhoneSettings();

			try {
				$phone_settings = JWT::encode($phone_data, CRON_KEY, 'HS256');
			} catch (Exception $e) {
				//
			}

			$lang_data = [];
			try {
				$lang_data = ClocationCountry::getLanguageList();
				$lang_data = JWT::encode($lang_data, CRON_KEY, 'HS256');
			} catch (Exception $e) {
				//
			}

			$options = OptionsTools::find([
				'driver_registration_process','driver_sendcode_interval','driver_task_take_pic','driver_time_allowed_accept_order','driver_enabled_time_allowed_acceptance',
				'driver_enabled_delivery_otp','driver_enabled_end_shift','enabled_language_rider_app','driver_add_proof_photo','driver_on_demand_availability'
			]);
			$data = [
				'registration_process'=>isset($options['driver_registration_process'])?$options['driver_registration_process']:'need_approval',
				'sendcode_interval'=>isset($options['driver_sendcode_interval'])?$options['driver_sendcode_interval']:15,
				'task_take_pic'=>isset($options['driver_task_take_pic'])? ($options['driver_task_take_pic']==1?true:false)  :false,
				'enabled_acceptance'=>isset($options['driver_enabled_time_allowed_acceptance'])? ($options['driver_enabled_time_allowed_acceptance']==1?true:false)  :false,
				'time_acceptance'=>isset($options['driver_time_allowed_accept_order'])?($options['driver_time_allowed_accept_order']>0?$options['driver_time_allowed_accept_order']:45):45,
				'enabled_delivery_otp'=>isset($options['driver_enabled_delivery_otp'])? ($options['driver_enabled_delivery_otp']==1?true:false)  :false,
				'enabled_end_shift'=>isset($options['driver_enabled_end_shift'])? ($options['driver_enabled_end_shift']==1?true:false)  :false,
				'enabled_language'=>isset($options['enabled_language_rider_app'])? ($options['enabled_language_rider_app']==1?true:false)  :false,
				'add_proof_photo'=>isset($options['driver_add_proof_photo'])? ($options['driver_add_proof_photo']==1?true:false)  :false,
				'on_demand_availability'=>isset($options['driver_on_demand_availability'])? ($options['driver_on_demand_availability']==1?true:false)  :false,
			];

			$calendar = AttributesTools::generateCalendarData();
			$break_duration = AttributesTools::breakDuration();
			$break_duration = CommonUtility::ArrayToLabelValue($break_duration);

			// PUSHER
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
					'event'=>Yii::app()->params->realtime['notification_event']
				];
			try {
				$realtime = JWT::encode($realtime, CRON_KEY, 'HS256');
			} catch (Exception $e) {
				$realtime = '';
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

			try {
				$cashin_denomination = CDriver::getDenomination('cashin_denomination');
				$cashin_denomination = CommonUtility::ArrayToLabelValue($cashin_denomination);
			} catch (Exception $e) {
				$cashin_denomination = [];
			}

			$vehicle_maker = CommonUtility::getDataToDropDown("{{admin_meta}}","meta_id",'meta_value',"WHERE meta_name='vehicle_maker'","Order by meta_name");
		    $vehicle_type = CommonUtility::getDataToDropDown("{{admin_meta}}","meta_id",'meta_value',"WHERE meta_name='vehicle_type'","Order by meta_name");
			$vehicle_maker = is_array($vehicle_maker)?CommonUtility::ArrayToLabelValue($vehicle_maker):array();
			$vehicle_type = is_array($vehicle_type)?CommonUtility::ArrayToLabelValue($vehicle_type):array();

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'maps_config'=>$maps_config,
				'phone_settings'=>$phone_settings,
				'lang_data'=>$lang_data,
				'date_now'=>date("c"),
				'data'=>$data,
				'calendar_data'=>$calendar,
				'timezone'=>Yii::app()->timeZone,
				'break_duration'=>$break_duration,
				'realtime_data'=>$realtime,
				'money_config'=>$money_config,
				'cashin_denomination'=>$cashin_denomination,
				'legal_menu'=>CDriver::getLegalMenu(),
				'vehicle_maker'=>$vehicle_maker,
				'vehicle_type'=>$vehicle_type,
			];
		} catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actionLogin()
	{
		$username = isset($this->data['username'])?$this->data['username']:'';
		$password = isset($this->data['password'])?$this->data['password']:'';

		$model=new AR_driver_login();
		$model->username = $username;
		$model->password = $password;

		if($model->validate() && $model->login() ){

			$user_data = array(
				'driver_id'=>Yii::app()->driver->id,
				'driver_uuid'=>Yii::app()->driver->driver_uuid,
				'first_name'=>Yii::app()->driver->first_name,
				'last_name'=>Yii::app()->driver->last_name,
				'email_address'=>Yii::app()->driver->email_address,
				'phone_prefix'=>Yii::app()->driver->phone_prefix,
				'phone'=>Yii::app()->driver->phone,
				'avatar'=>Yii::app()->driver->avatar,
				'notification'=>Yii::app()->driver->notification,
				'employment_type'=>Yii::app()->driver->employment_type
			);

			$payload = [
				'iss'=>Yii::app()->request->getServerName(),
				'sub'=>0,
				'iat'=>time(),
				'token'=>Yii::app()->driver->logintoken
			];

			$user_data = JWT::encode($user_data, CRON_KEY, 'HS256');
			$jwt_token = JWT::encode($payload, CRON_KEY, 'HS256');

			$this->code = 1;
			$this->msg = t("Login successful");
			$this->details = array(
				'user_token'=>$jwt_token,
				'user_data'=>$user_data
			);
		} else $this->msg = CommonUtility::parseError( $model->getErrors() );

		$this->responseJson();
	}

	public function actionauthenticate()
	{
		try {

			//$jwt_token = isset($this->data['token'])?$this->data['token']:'';
			$jwt_token = Yii::app()->input->post('token');
			$decoded = JWT::decode($jwt_token, new Key(CRON_KEY, 'HS256'));
			$token = isset($decoded->token)?$decoded->token:'';
			$model = AR_driver::model()->find('token=:token',array(':token'=>$token));
			if($model){
				$this->code = 1;
				$this->msg = "Token is valid";
			} else $this->msg = t("Token is not valid");

		} catch (Exception $e) {
			$this->msg = $e->getMessage();
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

	public function actionregister()
	{
		try {

			$model = new AR_driver;
			$model->myscenario = "register";
			$model->first_name = isset($this->data['first_name'])?$this->data['first_name']:'';
			$model->last_name = isset($this->data['last_name'])?$this->data['last_name']:'';
			$model->email = isset($this->data['email'])?$this->data['email']:'';
			$model->phone_prefix = isset($this->data['phone_prefix'])?$this->data['phone_prefix']:'';
			$model->phone = isset($this->data['phone'])?$this->data['phone']:'';
			$model->new_password = isset($this->data['password'])?$this->data['password']:'';
			$model->confirm_password = isset($this->data['confirm_password'])?$this->data['confirm_password']:'';
			$model->address = isset($this->data['address'])?$this->data['address']:'';
			$model->status = 'pending';			
			$model->default_currency = Price_Formatter::$number_format['currency_code'];

			if($model->save()){
				$this->code = 1;
				$this->msg = t("Registration successful");
				$this->details = [
					'uuid'=>$model->driver_uuid
				];
			} else $this->msg = CommonUtility::parseError( $model->getErrors() );

		} catch (Exception $e) {
		    $this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actionrequestresetpassword()
	{
		try {

			$email_address = isset($this->data['email_address'])?$this->data['email_address']:'';

			$model = AR_driver::model()->find('email=:email',
		    array(':email'=>$email_address));
		    if($model){
		    	if($model->status=="active"){
		    		$model->scenario = "request_resetpassword";
					$model->reset_password_request = 1;
		    		if($model->save()){
						$this->code = 1;
						$this->msg = t("Check {{email_address}} for an email to reset your password.",array(
						'{{email_address}}'=>$model->email
						));
						$this->details = array(
						'uuid'=>$model->driver_uuid
						);
					} else {
						$this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
					}
		    	} else $this->msg = t("Your account is either inactive or not verified.");
		    } else $this->msg = t("No email address found in our records. please verify your email.");

		} catch (Exception $e) {
		    $this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actionresendresetPassword()
	{
		try {

		   $uuid = Yii::app()->input->post('uuid');

		   $model = AR_driver::model()->find('driver_uuid=:driver_uuid',
		   array(':driver_uuid'=>$uuid));
		   if($model){
			  $model->scenario = "resend_resetpassword";
		   	  $model->reset_password_request = 1;
		   	  if($model->save()){

		   	  	   $this->code = 1;
		           $this->msg = t("Check {{email_address}} for an email to reset your password.",array(
		    		  '{{email_address}}'=>$model->email
		    	   ));

		   	  } else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
		   } else $this->msg = t("Records not found");

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionprofile()
	{
		try {

			$user_data = array(
				'driver_id'=>Yii::app()->driver->id,
				'driver_uuid'=>Yii::app()->driver->driver_uuid,
				'first_name'=>Yii::app()->driver->first_name,
				'last_name'=>Yii::app()->driver->last_name,
				'email_address'=>Yii::app()->driver->email_address,
				'phone_prefix'=>Yii::app()->driver->phone_prefix,
				'phone'=>Yii::app()->driver->phone,
				'avatar'=>Yii::app()->driver->avatar,
				'employment_type'=>Yii::app()->driver->employment_type
			);

			$this->code = 1;
			$this->msg = "OK";
			$this->details = [
				'data'=>$user_data
			];

		} catch (Exception $e) {
		    $this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actionupdateprofile()
	{
		try {

			$model = CDriver::getDriver(Yii::app()->driver->id);
			$model->first_name = isset($this->data['first_name'])?$this->data['first_name']:'';
			$model->last_name = isset($this->data['last_name'])?$this->data['last_name']:'';
			$model->phone_prefix = isset($this->data['phone_prefix'])?$this->data['phone_prefix']:'';
			$model->phone = isset($this->data['phone'])?$this->data['phone']:'';

			$file_data = isset($this->data['file_data'])?$this->data['file_data']:'';
			$image_type = isset($this->data['image_type'])?$this->data['image_type']:'png';

			if(!empty($file_data)){
				$result = [];
				try {
					$result = CImageUploader::saveBase64Image($file_data,$image_type,"upload/avatar");
					$model->photo = isset($result['filename'])?$result['filename']:'';
				    $model->path = isset($result['path'])?$result['path']:'';
				} catch (Exception $e) {
					$this->msg = t($e->getMessage());
					$this->responseJson();
				}
			} else {
				$featured_filename = isset($this->data['featured_filename'])?$this->data['featured_filename']:'';
				$upload_path = isset($this->data['upload_path'])?$this->data['upload_path']:'';
				if(!empty($featured_filename) && !empty($upload_path) ){
					$model->photo = $featured_filename;
				    $model->path = $upload_path;
				}
			}

			if($model->save()){
				$this->code = 1;
				$this->msg = t("Profile Succesfully updated.");

				$avatar = CMedia::getImage($model->photo,$model->path,Yii::app()->params->size_image_thumbnail,
				CommonUtility::getPlaceholderPhoto('driver'));

				$user_data = array(
					'driver_id'=>$model->driver_id,
					'driver_uuid'=>$model->driver_uuid,
					'first_name'=>$model->first_name,
					'last_name'=>$model->last_name,
					'email_address'=>$model->email,
					'phone_prefix'=>$model->phone_prefix,
					'phone'=>$model->phone,
					'avatar'=>$avatar,
					'notification'=>$model->notification,
					'employment_type'=>$model->employment_type
				);
				$user_data = JWT::encode($user_data, CRON_KEY, 'HS256');
				$this->details = [
					'user_data'	=>$user_data
				];
			} else $this->msg = CommonUtility::parseError( $model->getErrors() );

		} catch (Exception $e) {
		    $this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actiongetlicense()
	{
		try {

			if(!Yii::app()->driver->isGuest){
				$data = CDriver::getDriver(Yii::app()->driver->id);
				$this->code = 1;
				$this->msg = "OK";
				$this->details = [
					'license_number'=>$data->license_number,
					'license_expiration'=>$data->license_expiration,
					'license_front_photo'=> !empty($data->license_front_photo) ? CMedia::getImage($data->license_front_photo,$data->path,Yii::app()->params->size_image_thumbnail,CommonUtility::getPlaceholderPhoto('driver')) :'',
					'license_back_photo'=> !empty($data->license_back_photo) ? CMedia::getImage($data->license_back_photo,$data->path,Yii::app()->params->size_image_thumbnail,CommonUtility::getPlaceholderPhoto('driver')) :'',
				];
			} else $this->msg = t("Not login");

		} catch (Exception $e) {
		    $this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actionaddlicense()
	{
		try {

			$driver_uuid = isset($this->data['driver_uuid'])?$this->data['driver_uuid']:'';
			$license_number = isset($this->data['license_number'])?$this->data['license_number']:'';
			$license_expiration = isset($this->data['license_expiration'])?$this->data['license_expiration']:'';

			$model = CDriver::getDriverByUUID($driver_uuid);
			$model->scenario='attach_license';
			$model->license_number = $license_number;
			$model->license_expiration = $license_expiration;
			if($model->save()){
				$this->code = 1;
				$this->msg = t("Add license succesful");
			} else $this->msg = CommonUtility::parseError( $model->getErrors() );

		} catch (Exception $e) {
		    $this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actionaddlicensephoto()
	{
		try {

			$driver_uuid = isset($this->data['driver_uuid'])?$this->data['driver_uuid']:'';
			$model = CDriver::getDriverByUUID($driver_uuid);

			$front_photo_filename = isset($this->data['front_photo_filename'])?$this->data['front_photo_filename']:'';
			$upload_path = isset($this->data['upload_path'])?$this->data['upload_path']:'';
			$file_data = isset($this->data['file_data'])?$this->data['file_data']:'';
			$image_type = isset($this->data['image_type'])?$this->data['image_type']:'';


			$back_photo_filename = isset($this->data['back_photo_filename'])?$this->data['back_photo_filename']:'';
			$photo_data_back = isset($this->data['photo_data_back'])?$this->data['photo_data_back']:'';
			$image_type_back = isset($this->data['image_type_back'])?$this->data['image_type_back']:'';

			$result = [];

			if(!empty($file_data)){
				$result = [];
				try {
					$result = CImageUploader::saveBase64Image($file_data,$image_type,"upload/license");
					$model->license_front_photo = isset($result['filename'])?$result['filename']:'';
				    $model->path_license = isset($result['path'])?$result['path']:'';
				} catch (Exception $e) {
					$this->msg = t($e->getMessage());
					$this->responseJson();
				}
			} else {
				$model->license_front_photo = $front_photo_filename;
				$model->path_license = $upload_path;
			}

			if(!empty($photo_data_back)){
				$result = [];
				try {
					$result = CImageUploader::saveBase64Image($photo_data_back,$image_type,"upload/license");
					$model->license_back_photo = isset($result['filename'])?$result['filename']:'';
				    $model->path_license = isset($result['path'])?$result['path']:'';
				} catch (Exception $e) {
					$this->msg = t($e->getMessage());
					$this->responseJson();
				}
			} else {
				$model->license_back_photo = $back_photo_filename;
				$model->path_license = $upload_path;
			}

			$options = OptionsTools::find(['driver_registration_process']);
			$registration_process = isset($options['driver_registration_process'])?$options['driver_registration_process']:'need_approval';
			if($registration_process=="activate_account"){
				$model->status = 'active';
			}

			if($model->save()){
				$this->code = 1;
				$this->msg = t("Driver license photo succesfully updated.");
				$this->details = [
					'driver_id'=>$model->driver_id,
					'result'=>$result,
				];
			} else $this->msg = CommonUtility::parseError( $model->getErrors() );

		} catch (Exception $e) {
		    $this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actionupdatelicense()
	{
		try {

			if(!Yii::app()->driver->isGuest){
				$model = CDriver::getDriver(Yii::app()->driver->id);
				$model->license_number = isset($this->data['license_number'])?$this->data['license_number']:'';
				$model->license_expiration = isset($this->data['license_expiration'])?$this->data['license_expiration']:'';

				if($model->save()){
					$this->code = 1;
					$this->msg = t("Update Succesfully");
				} else $this->msg = CommonUtility::parseError( $model->getErrors() );

			} else $this->msg = t("Not login");

		} catch (Exception $e) {
		    $this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actionrequestcode()
	{
		try {

		   $driver_uuid = isset($this->data['driver_uuid'])?$this->data['driver_uuid']:'';

		   $model = AR_driver::model()->find('driver_uuid=:driver_uuid',
		   array(':driver_uuid'=>$driver_uuid));
		   if($model){
		   	  $digit_code = CommonUtility::generateNumber(3,true);
		   	  $model->verification_code = $digit_code;
			  $model->verify_code_requested = CommonUtility::dateNow();
			  $model->scenario = 'send_otp';
		   	  if($model->save()){
		   	  	   $this->code = 1;
		           $this->msg = t("We sent a code to {{email_address}}.",array(
		             '{{email_address}}'=> CommonUtility::maskEmail($model->email)
		           ));
				   if(DEMO_MODE){
					  $this->details = [
						'otp'=>t("Your OTP is {{otp}}",['{{otp}}'=>$model->verification_code])
					  ];
				   }
		   	  } else $this->msg = CommonUtility::parseError($model->getErrors());
		   } else $this->msg[] = t("Records not found");

		} catch (Exception $e) {
		    $this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actionrequestcode2()
	{
		try {

			$model = CDriver::getDriver(Yii::app()->driver->id);
			$digit_code = CommonUtility::generateNumber(3,true);
			$model->verification_code = $digit_code;
			$model->verify_code_requested = CommonUtility::dateNow();
			$model->scenario = 'send_otp';
			if($model->save()){
				$this->code = 1;
				$this->msg = t("We sent a code to {{email_address}}.",array(
					'{{email_address}}'=> CommonUtility::maskEmail($model->email)
				));
				if(DEMO_MODE){
					$this->details = [
					  'otp'=>t("Your OTP is {{otp}}",['{{otp}}'=>$model->verification_code])
					];
				}
			} else $this->msg = CommonUtility::parseError($model->getErrors());

		} catch (Exception $e) {
		    $this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actiongetAccountStatus()
	{
		try {

			$driver_uuid = Yii::app()->input->post('uuid');
			$model = AR_driver::model()->find('driver_uuid=:driver_uuid',
			array(':driver_uuid'=> $driver_uuid ));

			if($model){
				$data =[
					'status'=>$model->status,
					'account_verified'=>$model->account_verified,
				];
				$options = OptionsTools::find(['driver_sendcode_via','signup_enabled_verification','driver_sendcode_interval']  );
				$sendcode_via  = isset($options['driver_sendcode_via'])?$options['driver_sendcode_via']:'email';
				$signup_resend_counter  = isset($options['driver_sendcode_interval'])?$options['driver_sendcode_interval']:20;

				$this->code = 1;
				if($sendcode_via=="email"){
					$this->msg = t("We sent a code to {{email_address}}.",array(
						'{{email_address}}'=> CommonUtility::maskEmail($model->email)
					  ));
				} else {
					$this->msg = t("We sent a code to {{phone}}.",array(
						'{{phone}}'=> CommonUtility::mask($model->phone)
					  ));
				}
				$this->details = [
					'data'=>$data,
					'settings'=>[
					  'signup_resend_counter'=>$signup_resend_counter<=0?20:$signup_resend_counter
					]
				];
				if(DEMO_MODE){
					$this->details['otp'] = t("Your OTP is {{otp}}",['{{otp}}'=>$model->verification_code]);
				}
			} else $this->msg = t("account not found");

		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionverifycode()
	{
		try {

			$driver_uuid = isset($this->data['driver_uuid'])?$this->data['driver_uuid']:'';
			$code = isset($this->data['code'])?intval($this->data['code']):'';
			$model = CDriver::getDriverByUUID($driver_uuid);
			if($model->verification_code==$code){
				$model->account_verified = 1;
				if($model->save()){
					$this->code = 1;
				    $this->msg = t("Code verified");
				} else $this->msg = CommonUtility::parseError($model->getErrors());
			} else $this->msg = t("Invalid 4 digit code");

		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionchangepassword()
	{
		try {

			if(!Yii::app()->driver->isGuest){
				$model = CDriver::getDriver(Yii::app()->driver->id);
				$old_password = isset($this->data['old_password'])? trim($this->data['old_password']) :'';
				$old_password = md5($old_password);
				if($model->password==$old_password){
					$model->new_password = isset($this->data['new_password'])? trim($this->data['new_password']) :'';
					$model->confirm_password = isset($this->data['confirm_password'])? trim($this->data['confirm_password']) :'';
					if($model->save()){
						$this->code = 1;
						$this->msg = t("Password successfully updated");
					} else $this->msg = CommonUtility::parseError($model->getErrors());
				} else $this->msg = t("Old password does not match");
			} else $this->msg = t("Not login");

		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetvehicle()
	{
		try {

			if(!Yii::app()->driver->isGuest){
				$model = CDriver::getDriver(Yii::app()->driver->id);
				$now = date("Y-m-d");
                $vehicle = CDriver::getVehicleAssign($model->driver_id,$now);
				$this->code = 1;
				$this->msg = "Ok";

				$vehicle_maker = CommonUtility::getDataToDropDown("{{admin_meta}}","meta_id",'meta_value',"WHERE meta_name='vehicle_maker'","Order by meta_name");
		        $vehicle_type = CommonUtility::getDataToDropDown("{{admin_meta}}","meta_id",'meta_value',"WHERE meta_name='vehicle_type'","Order by meta_name");

				$data =  [
					'vehicle_uuid'=>$vehicle->vehicle_uuid,
					'vehicle_type_id'=>$vehicle->vehicle_type_id,
					'plate_number'=>$vehicle->plate_number,
					'maker'=>$vehicle->maker,
					'model'=>$vehicle->model,
					'color'=>$vehicle->color,
					'photo'=>CMedia::getImage($vehicle->photo,$vehicle->path,Yii::app()->params->size_image_thumbnail,CommonUtility::getPlaceholderPhoto('car','car.png')),
					'active'=>$vehicle->active,
				];
				$this->details = [
					'data'=>$data,
					'vehicle_maker'=>$vehicle_maker,
					'vehicle_type'=>$vehicle_type,
				];
			} else $this->msg = t("Not login");

		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetorderlist()
	{
		try {
			if(!Yii::app()->driver->isGuest){
				$this->setDefaultCurrency();
				$date = isset($this->data['date'])?$this->data['date']:'';

				$order_status = AttributesTools::getOrderStatus(Yii::app()->language,'delivery_status');
				//$payment_list = AttributesTools::PaymentProvider();
				$payment_list = CommonUtility::getDataToDropDown("{{payment_gateway}}",'payment_code','is_online',
		       "WHERE status='active'","ORDER BY sequence ASC");

			    $exlude_status = AOrders::getOrderTabsStatus('completed');

				$result = CDriver::getOrdersByDriverID(Yii::app()->driver->id,$date,'delivery',$exlude_status);
				$data = []; $merchant_ids = []; $order_ids = [];
				foreach ($result as $key => $items) {
					$avatar = '';
					$meta = !empty($items->meta) ? explode("|",$items->meta) : '';
					if(is_array($meta) && count($meta)>=1){
						$avatar = CMedia::getImage($meta[0],$meta[1],'@thumbnail',CommonUtility::getPlaceholderPhoto('customer'));
					}

					$merchant_ids[]=$items->merchant_id;
					$order_ids[]=$items->order_id;

					$delivery_steps = CDriver::getDeliverySteps($items->delivery_status);

					$data[$items->order_id]=[
						'order_id'=>$items->order_id,
						'order_uuid'=>$items->order_uuid,
						'merchant_id'=>$items->merchant_id,
						'client_id'=>$items->client_id,
						'total_raw'=>$items->total,
						'total'=>Price_Formatter::formatNumber($items->total),
						'delivery_fee'=>$items->delivery_fee,
						'payment_code'=>$items->payment_code,
						'full_name'=>$items->customer_name,
						'address'=>$items->formatted_address,
						'delivery_status_raw'=>$items->delivery_status,
						'delivery_status'=>isset($order_status[$items->delivery_status])?$order_status[$items->delivery_status]:$items->delivery_status,
						'avatar'=>$avatar,
						'delivery_steps'=>$delivery_steps,
					];
				}

				$merchant = CMerchants::getListByID($merchant_ids);
				$order_meta = COrders::getAttributesAll2($order_ids,['longitude',
				'latitude',
				'contact_email',
				'contact_number',
				'customer_name',
				'delivery_instructions',
				'delivery_options',
				'address_label',
				'address1',
				'location_name'
			  ]);

				$this->code = 1;
				$this->msg = "OK";
				$this->details = [
					'data'=>$data,
					'merchant'=>$merchant,
					'order_meta'=>$order_meta,
					'payment_list'=>$payment_list
				];

		    } else $this->msg = t("Not login");
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionorderhistory()
	{
		try {

			$this->setDefaultCurrency();

			$data = [];	$limit = 10; $order_ids = []; $merchant_ids = [];

			$meta = AR_admin_meta::getValue('status_delivery_delivered');
            $delivery_status = isset($meta['meta_value'])?$meta['meta_value']:'';

			$page = intval(Yii::app()->input->post('page'));
			$date_start = trim(Yii::app()->input->post('date_start'));
			$date_end = trim(Yii::app()->input->post('date_end'));
			$date_range =  $date_start==$date_end?Date_Formatter::date($date_start): Date_Formatter::date($date_start)." - ".Date_Formatter::date($date_end);

			$page_raw = intval(Yii::app()->input->post('page'));
			if($page>0){
				$page = $page-1;
			}

			$driver_id = Yii::app()->driver->id;
			$criteria=new CDbCriteria();
			$criteria->addCondition("driver_id=:driver_id");
			$criteria->params = [
				':driver_id'=>intval($driver_id),
			];
			$criteria->addBetweenCondition("delivery_date",$date_start,$date_end);
			$criteria->addInCondition("delivery_status",(array)$delivery_status);
			$criteria->order = "order_id DESC";

			$count=AR_ordernew::model()->count($criteria);
			$pages=new CPagination($count);
			$pages->pageSize=$limit;
			$pages->setCurrentPage( $page );
			$pages->applyLimit($criteria);
			$page_count = $pages->getPageCount();

			if($page>0){
				if($page_raw>$page_count){
					$this->code = 3;
					$this->msg = t("end of results");
					$this->details = [
						'date_range'=>$date_range,
					];
					$this->responseJson();
				}
			}

			if($model = AR_ordernew::model()->findAll($criteria)){
				foreach ($model as $items) {
					$order_ids[]=$items->order_id;
					$merchant_ids[]=$items->merchant_id;
				}

				$order_meta = COrders::getAttributesAll2($order_ids,[
					'customer_name',
					'formatted_address'
				]);
				$merchant = CMerchants::getListByID($merchant_ids);
				$payment_list = AttributesTools::PaymentProvider();

				$all_offline = CPayments::getPaymentTypeOnline(0);

				foreach ($model as $items) {
					$online_payment = true;
					if(array_key_exists($items->payment_code,(array)$all_offline)){
						$online_payment = false;
					}
					$delivery_pay = $items->delivery_fee+$items->courier_tip;
					$delivery_time = !empty($items->delivery_time)?Date_Formatter::Time($items->delivery_time,"h:mm a",true):Date_Formatter::dateTime($items->date_created,"h:mm a",true);
					$data[]=[
						'order_id'=>$items->order_id,
						'merchant_id'=>$items->merchant_id,
						'payment_code'=>$items->payment_code,
						'online_payment'=>$online_payment,
						'total_raw'=>$items->total,
						'total'=>Price_Formatter::formatNumber($items->total),
						'courier_tip'=>Price_Formatter::formatNumber($items->courier_tip),
						'courier_tip_raw'=>$items->courier_tip,
						'delivery_fee'=>Price_Formatter::formatNumber($items->delivery_fee),
						'delivery_pay'=>Price_Formatter::formatNumber($delivery_pay),
						'delivered_at'=> !empty($items->delivered_at)? Date_Formatter::time($items->delivered_at):'',
						'pickup_address'=>$items->formatted_address,
						'order_meta'=>isset($order_meta[$items->order_id])?$order_meta[$items->order_id]:'',
						'merchant'=>isset($merchant[$items->merchant_id])?$merchant[$items->merchant_id]:'',
						'payment_type'=>isset($payment_list[$items->payment_code])?$payment_list[$items->payment_code]:'',
						'date_created'=>Date_Formatter::dateTime($items->date_created),
						'delivery_time'=>$delivery_time,
					];
				}

				$this->code = 1;
				$this->msg = "OK";
				$this->details = [
					'page_raw'=>$page_raw,
					'page_count'=>$page_count,
					'date_range'=>$date_range,
					'data'=>$data
				];
			}
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetshift()
	{
		try {

			if(!Yii::app()->driver->isGuest){
				$employment_type = Yii::app()->driver->employment_type;
				$date = isset($this->data['date'])?$this->data['date']:'';
				$vehicle_data = [];
				$now = date("Y-m-d g:i:s a");
				$date_now = date("Y-m-d");
			    $data = CDriver::getDriverSchedule(Yii::app()->driver->id,$date,$date,$now);

				try {
				   $data_break = CDriver::getBreak(Yii::app()->driver->id,$date_now);
				} catch (Exception $e) {
				   $data_break = [];
				}

				$can_request_break = false;
				$request_break_limit = isset(Yii::app()->params['settings']['driver_enabled_request_break'])?Yii::app()->params['settings']['driver_enabled_request_break']:false;
				$request_break_limit = $request_break_limit==1?true:false;
				if($request_break_limit){
					$can_request_break = CDriver::canRequestBreak(Yii::app()->driver->id,$date_now);
				}

				$vehicle_maker = CommonUtility::getDataToDropDown("{{admin_meta}}","meta_id",'meta_value',"WHERE meta_name='vehicle_maker'","Order by meta_name");
                $vehicle_type = CommonUtility::getDataToDropDown("{{admin_meta}}","meta_id",'meta_value',"WHERE meta_name='vehicle_type'","Order by meta_name");

				try {
					$vehicle_data = CDriver::getVehicleByIDs($data['vehicles']);
				} catch (Exception $e) {
					//
				}

				$vehicle_documents = array();
				try {
					$vehicle_documents = CDriver::getMetaAll($data['vehicles'],'car_documents');
				} catch (Exception $e) {
					//dump($e->getMessage());
				}

				$this->code  = 1;
				$this->msg = "OK";
				$this->details = [
					'date_now'=>date("c",strtotime($now)),
					'data'=>$data,
					'data_break'=>$data_break,
					'can_request_break'=>$can_request_break,
					'vehicle_data'=>$vehicle_data,
					'vehicle_maker'=>$vehicle_maker,
					'vehicle_type'=>$vehicle_type,
					'vehicle_documents'=>$vehicle_documents
				];
			} else $this->msg = t("Not login");
		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionstartshift()
	{
		try {

			$schedule_uuid = isset($this->data['schedule_uuid'])?$this->data['schedule_uuid']:'';
			$model = AR_driver_schedule::model()->find("schedule_uuid=:schedule_uuid",[
				':schedule_uuid'=>$schedule_uuid
			]);
			if($model){
				$model->scenario="start_shift";
				if(!is_null($model->shift_time_started)){
					$this->msg = t("Already started working");
					$this->responseJson();
				}
				$model->shift_time_started = CommonUtility::dateNow();
				if($model->save()){
					$this->code  = 1;
					$this->msg = t("Successful");
				} else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
			} else $this->msg = t("Schedule not found");

		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionendshift()
	{
		try {

			$schedule_uuid = isset($this->data['schedule_uuid'])?$this->data['schedule_uuid']:'';
			$model = AR_driver_schedule::model()->find("schedule_uuid=:schedule_uuid",[
				':schedule_uuid'=>$schedule_uuid
			]);
			if($model){
				if(!is_null($model->shift_time_ended)){
					$this->msg = t("Shift already ended");
					$this->responseJson();
				}
				$model->scenario="end_shift";
				$model->shift_time_ended = CommonUtility::dateNow();
				if($model->save()){
					$this->code  = 1;
					$this->msg = t("Successful");
				} else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
			} else $this->msg = t("Schedule not found");

		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionorderpreview()
	{
		try {

			$this->setDefaultCurrency();
			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:0;
			$order = COrders::get($order_uuid);

			$order_meta =  COrders::getAttributesAll($order->order_id,[
				'longitude','latitude','customer_name','address1'
			]);
			

			$merchant = CMerchants::get($order->merchant_id);

			$customer_name = $order_meta?$order_meta['customer_name']:'';
			$latitude = $order_meta?$order_meta['latitude']:'';
			$longitude = $order_meta?$order_meta['longitude']:'';
			$address1 = $order_meta['address1']?$order_meta['address1']:'';

			// $maps_config = CMaps::config();
			// $maps_config = JWT::encode($maps_config, CRON_KEY, 'HS256');

			$data = [
				'order_id'=>$order->order_id,
				'total_raw'=>$order->total,
				'total'=>Price_Formatter::formatNumber($order->total),
				'pickup'=>[
					'restaurant_name'=>$merchant->restaurant_name,
					'address'=>$merchant->address,
					'latitude'=>$merchant->latitude,
					'longitude'=>$merchant->lontitude,
				],
				'dropoff'=>[
					'customer_name'=>$customer_name,
					'address'=>!empty($address1)? "$address1 $order->formatted_address":$order->formatted_address,
					'latitude'=>$latitude,
					'longitude'=>$longitude,
				],
				//'maps_config'=>$maps_config
			];

			$status_data = [];
			try {
				$meta = AR_admin_meta::getValue('status_acknowledged');
                $status = isset($meta['meta_value'])?$meta['meta_value']:'';
				$status_data = AttributesTools::getStatusWithColor("delivery_status",$status);
				$data['status_data'] = $status_data;
			} catch (Exception $e) {
				$data['status_data'] = [
					'font_color'=>'#f8af01',
				    'bg_color'=>"white"
				];
			}

			$this->code = 1;
			$this->msg = "OK";
			$this->details = $data;

		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionorderdetails()
	{
		try {

			$this->setDefaultCurrency();
			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:0;
			COrders::getContent($order_uuid,Yii::app()->language);
			$items = COrders::getItems();
			$item_count = COrders::itemCount(COrders::getOrderID());
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'items'=>$items,
				'item_count'=>$item_count
			];

		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actioncompleteorderdetails()
	{
		try {

			$payload = isset($this->data['payload'])?$this->data['payload']:'';
			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:0;

			COrders::getContent($order_uuid,Yii::app()->language);
		    $merchant_id = COrders::getMerchantId($order_uuid);
		    $merchant_info = COrders::getMerchant($merchant_id,Yii::app()->language);
		    $items = COrders::getItems();
		    $summary = COrders::getSummary();
		    $summary_total = COrders::getSummaryTotal();

		    $summary_changes = array(); $summary_transaction = array();
		    $summary_transaction = COrders::getSummaryTransaction();

		    $total_order = CMerchants::getTotalOrders($merchant_id);
		    $merchant_info['order_count'] = $total_order;

		    $order = COrders::orderInfo(Yii::app()->language, date("Y-m-d") );
		    $order_type = isset($order['order_info'])?$order['order_info']['order_type']:'';
		    $client_id = $order?$order['order_info']['client_id']:0;
		    $order_id = $order?$order['order_info']['order_id']:'';
		    $customer = COrders::getClientInfo($client_id);

			$origin_latitude = $order?$order['order_info']['latitude']:'';
			$origin_longitude = $order?$order['order_info']['longitude']:'';
			$delivery_direction = isset($merchant_info['restaurant_direction'])?$merchant_info['restaurant_direction']:'';
			if($order_type=="delivery"){
				$delivery_direction = isset($merchant_info['restaurant_direction'])?$merchant_info['restaurant_direction']:'';
				$delivery_direction.="&origin="."$origin_latitude,$origin_longitude";
			}
			$order['order_info']['delivery_direction'] = $delivery_direction;

		    $draft = AttributesTools::initialStatus();
		    $not_in_status = AOrderSettings::getStatus(array('status_cancel_order','status_rejection'));
		    array_push($not_in_status,$draft);
		    $orders = ACustomer::getOrdersTotal($client_id,0,array(), (array)$not_in_status );
		    $customer['order_count'] = $orders;

		    $payment_history = array();

		    if(in_array('payment_history',(array)$payload)){
		       $payment_history = COrders::paymentHistory($order_id);
		    }

		    $data = array(
		       'merchant'=>$merchant_info,
		       'order'=>$order,
		       'items'=>$items,
		       'summary'=>$summary,
		       'summary_total'=>$summary_total,
		       'summary_changes'=>$summary_changes,
		       'summary_transaction'=>$summary_transaction,
		       'customer'=>$customer,
		       'sold_out_options'=>AttributesTools::soldOutOptions(),
		       'payment_history'=>$payment_history
		    );

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

	public function actionacceptorder()
	{
		try {

			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:0;
			$meta = AR_admin_meta::getValue('status_acknowledged');
            $delivery_status = isset($meta['meta_value'])?$meta['meta_value']:'';
			$this->changeOrderStatus($order_uuid,$delivery_status,'','delivery_accept_order');

		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiondeclineorder()
	{
		try {

			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:0;
			$reason = isset($this->data['reason'])?$this->data['reason']:'';
			$meta = AR_admin_meta::getValue('status_delivery_declined');
            $delivery_status = isset($meta['meta_value'])?$meta['meta_value']:'';
			$data = [
				'remarks'=>$reason
			];
			$this->changeOrderStatus($order_uuid,$delivery_status,$data,'delivery_declined');

		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiononthewayvendor()
	{
		try {

			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:0;
			$meta = AR_admin_meta::getValue('status_driver_to_restaurant');
            $delivery_status = isset($meta['meta_value'])?$meta['meta_value']:'';
			$this->changeOrderStatus($order_uuid,$delivery_status);

		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionarrivedatvendor()
	{
		try {

			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:0;
			$meta = AR_admin_meta::getValue('status_arrived_at_restaurant');
            $delivery_status = isset($meta['meta_value'])?$meta['meta_value']:'';
			$this->changeOrderStatus($order_uuid,$delivery_status);

		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionwaitingfororder()
	{
		try {

			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:0;
			$meta = AR_admin_meta::getValue('status_waiting_for_order');
            $delivery_status = isset($meta['meta_value'])?$meta['meta_value']:'';
			$this->changeOrderStatus($order_uuid,$delivery_status);

		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionorderpickup()
	{
		try {

			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:0;
			$meta = AR_admin_meta::getValue('status_order_pickup');
            $delivery_status = isset($meta['meta_value'])?$meta['meta_value']:'';
			$this->changeOrderStatus($order_uuid,$delivery_status,'','delivery_orderpickup');

		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiononthewaycustomer()
	{
		try {

			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:0;
			$meta = AR_admin_meta::getValue('status_delivery_started');
            $delivery_status = isset($meta['meta_value'])?$meta['meta_value']:'';
			$this->changeOrderStatus($order_uuid,$delivery_status,'','delivery_onthewaycustomer');

		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionarrivedatcustomer()
	{
		try {

			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:0;
			$meta = AR_admin_meta::getValue('status_arrived_at_customer');
            $delivery_status = isset($meta['meta_value'])?$meta['meta_value']:'';
			$this->changeOrderStatus($order_uuid,$delivery_status,'','delivery_arrivedatcustomer');

		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionorderdelivered()
	{
		try {

			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:0;
			$meta = AR_admin_meta::getValue('status_delivery_delivered');
            $delivery_status = isset($meta['meta_value'])?$meta['meta_value']:'';

			$file_data = isset($this->data['file_data'])?$this->data['file_data']:'';
			$image_type = isset($this->data['image_type'])?$this->data['image_type']:'png';
			$otp_code = isset($this->data['otp_code'])?$this->data['otp_code']:'';

			$enabled_delivery_otp = isset(Yii::app()->params['settings']['driver_enabled_delivery_otp']) ? intval(Yii::app()->params['settings']['driver_enabled_delivery_otp']) : 0;

			$result = [];
			try {

				if(!empty($file_data)){
					try {
						$result = CImageUploader::saveBase64Image($file_data,$image_type,"upload/order_proof");
					} catch (Exception $e) {
						$this->msg = t($e->getMessage());
						$this->responseJson();
					}
				} else {
					$result['path'] = isset($this->data['upload_path'])?$this->data['upload_path']:'';
					$result['filename'] = isset($this->data['featured_filename'])?$this->data['featured_filename']:'';
				}

				$order = COrders::get($order_uuid);

				if($enabled_delivery_otp==1){
					$order_meta = COrders::getMeta($order->order_id,'order_otp');
					if($order_meta){
						if($otp_code!=$order_meta->meta_value){
							$this->msg = t("Invalid OTP");
							$this->responseJson();
						}
					} else {
						$this->msg = t("OTP order not found");
						$this->responseJson();
					}
				}
				// CHECK OTP
				
				$add_proof_photo = isset(Yii::app()->params['settings'])? (isset(Yii::app()->params['settings']['driver_add_proof_photo'])?Yii::app()->params['settings']['driver_add_proof_photo']:false) :false;				
				$add_proof_photo = $add_proof_photo==1?true:false;				

				if($add_proof_photo){				
					$meta = new AR_driver_meta;
					$meta->reference_id = $order->order_id;
					$meta->meta_name = "order_proof";
					$meta->meta_value1 = isset($result['filename'])?$result['filename']:'';
					$meta->meta_value2 = isset($result['path'])?$result['path']:'';
					if(!$meta->save()){					
						$this->msg = CommonUtility::parseError( $meta->getErrors() );
						$this->responseJson();
					}
			    }
			} catch (Exception $e) {
				$this->msg = t($e->getMessage());
				$this->responseJson();
			}
			$this->changeOrderStatus($order_uuid,$delivery_status,array(),'orderdelivered');
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiondeliveryfailed()
	{
		try {

			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:0;
			$reason = isset($this->data['reason'])?$this->data['reason']:'';
			$meta = AR_admin_meta::getValue('status_delivery_failed');
            $delivery_status = isset($meta['meta_value'])?$meta['meta_value']:'';
			$data = [
				'remarks'=>$reason
			];
			$this->changeOrderStatus($order_uuid,$delivery_status,$data,'delivery_failed');

		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	private function changeOrderStatus($order_uuid='', $delivery_status='' , $params=array(), $scenario='delivery_order_process' )
	{
		if(Yii::app()->driver->isGuest){
			$this->msg = t("Not login");
			$this->responseJson();
		}

		if(empty($delivery_status)){
			$this->msg = t("Delivery status is not properly configure");
			$this->responseJson();
		}

		$order = COrders::get($order_uuid);
		if(Yii::app()->driver->id!=$order->driver_id){
			$this->msg = t("This order has assigned to different driver");
			$this->responseJson();
		}

		if($order->delivery_status==$delivery_status){
			$this->msg = t("Delivery status cannot be the same as current status");
			$this->responseJson();
		}

		$options = OptionsTools::find(['driver_allowed_number_task']);
        $allowed_number_task = isset($options['driver_allowed_number_task'])?$options['driver_allowed_number_task']:0;

		$order->scenario = $scenario;
		$order->driver_id = intval(Yii::app()->driver->id);
		$order->delivered_old_status = $order->delivery_status;
		$order->delivery_status = $delivery_status;
		$order->change_by = Yii::app()->driver->first_name;
		$order->date_now = date("Y-m-d");
		$order->allowed_number_task = intval($allowed_number_task);
		$order->remarks = isset($params['remarks'])?$params['remarks']:'';

		if($order->save()){
			$delivery_steps = CDriver::getDeliverySteps($delivery_status);
			$this->code  = 1;
			$this->msg = "OK";
			$this->details = [
				'order_uuid'=>$order_uuid,
				'delivery_steps'=>$delivery_steps,
			];
		} else $this->msg = CommonUtility::parseModelErrorToString($order->getErrors());
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
				'channel'=>Yii::app()->driver->driver_uuid,
				'event'=>$getevent=="tracking"?Yii::app()->params->realtime['event_tracking_order']:Yii::app()->params->realtime['notification_event']
			];
		} else $this->msg = t("realtime not enabled");
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
			$criteria->condition = "notication_channel=:notication_channel AND visible=1";
			$criteria->params  = array(
			':notication_channel'=>Yii::app()->driver->driver_uuid
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

	public function actiondeleteNotifications()
	{
		try {

			$data = isset($this->data['data'])?$this->data['data']:'';
			CDriver::deleteNotifications(Yii::app()->driver->driver_uuid,$data);
			$this->code = 1;
			$this->msg = "Ok";

		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiondeliverysummary()
	{
		try {

			$this->setDefaultCurrency();

			$meta = AR_admin_meta::getMeta(['status_delivery_delivered']);
			$status = isset($meta['status_delivery_delivered'])?strtolower($meta['status_delivery_delivered']['meta_value']):'';

			$offline_payment_codes = CommonUtility::getDataToDropDown("{{payment_gateway}}",'payment_code','payment_code','WHERE is_online=0');

			$date_start = Yii::app()->input->post('date_start');
			$date_end = Yii::app()->input->post('date_end');
			$total_deliveries = CDriver::getTotalDeliveries(Yii::app()->driver->id,[$status],$date_start,$date_end);
			$total_delivery_pay = CDriver::getTotalDeliveryFee(Yii::app()->driver->id,[$status],$date_start,$date_end);
			$total_tips = CDriver::getTotalTips(Yii::app()->driver->id,[$status],$date_start,$date_end);
			$cash_collected = CDriver::getCashCollected(Yii::app()->driver->id,[$status],$date_start,$date_end,$offline_payment_codes);

			$this->code =1;
			$this->msg = "Ok";
			$this->details = [
				'total_deliveries'=>intval($total_deliveries),
				'total_delivery_pay_raw'=>$total_delivery_pay,
				'total_delivery_pay'=>Price_Formatter::formatNumber($total_delivery_pay),
				'total_tips_raw'=>$total_tips,
				'total_tips'=>Price_Formatter::formatNumber($total_tips),
				'cash_collected_raw'=>$cash_collected,
				'cash_collected'=>Price_Formatter::formatNumber($cash_collected)
			];

		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionshifthistory()
	{
		try {

			$limit = 20; $data = [];  $now = date("Y-m-d");

			$page = intval(Yii::app()->input->post('page'));
			$page_raw = intval(Yii::app()->input->post('page'));
			if($page>0){
				$page = $page-1;
			}

			$criteria=new CDbCriteria();
			$criteria->condition = "driver_id=:driver_id";
			$criteria->params  = array(
			   ':driver_id'=>Yii::app()->driver->id
			);
			$criteria->order = "time_start DESC";

		    $count=AR_driver_schedule::model()->count($criteria);
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

			$model = AR_driver_schedule::model()->findAll($criteria);
			if($model){
				$vehicle_ids = []; $vehicle_data = []; $dates = []; $total = [];
				foreach ($model as $items) {
					$vehicle_ids[$items->vehicle_id] = $items->vehicle_id;
					$dates[$items->time_start] = date("Y-m-d",strtotime($items->time_start));
				}

				$vehicle_maker = CommonUtility::getDataToDropDown("{{admin_meta}}","meta_id",'meta_value',"WHERE meta_name='vehicle_maker'","Order by meta_name");
                $vehicle_type = CommonUtility::getDataToDropDown("{{admin_meta}}","meta_id",'meta_value',"WHERE meta_name='vehicle_type'","Order by meta_name");

				try {
					$vehicle_data = CDriver::getVehicleByIDs($vehicle_ids);
				} catch (Exception $e) {
					//
				}

				$meta = AR_admin_meta::getMeta(['status_delivery_delivered']);
			    $status = isset($meta['status_delivery_delivered'])?strtolower($meta['status_delivery_delivered']['meta_value']):'';
				try {
					$total = CDriver::getSummaryDeliveries(Yii::app()->driver->id,$status,$dates);
				} catch (Exception $e) {
					//
				}

				foreach ($model as $items) {
					$time_start = date("Y-m-d",strtotime($items->time_start));
					$vehicle_ids[$items->vehicle_id] = $items->vehicle_id;
					$vehicle = isset($vehicle_data[$items->vehicle_id])?$vehicle_data[$items->vehicle_id]:'';
					$data[] = [
						'schedule_id'=>$items->schedule_id,
						'schedule_uuid'=>$items->schedule_uuid,
						//'date_start'=>Date_Formatter::date($items->date_start),
						'time_start'=>Date_Formatter::time($items->time_start),
						'time_end'=>Date_Formatter::time($items->time_end),
						//'shift_time_started'=>Date_Formatter::date($items->shift_date_started),
						'shift_time_started'=>Date_Formatter::time($items->shift_time_started),
						'shift_time_ended'=>Date_Formatter::time($items->shift_time_ended),
						'instructions'=>$items->instructions,
						'vehicle'=>$vehicle,
						'maker'=>isset($vehicle_maker[$vehicle['maker']])?$vehicle_maker[$vehicle['maker']]:'',
						'vehicle_type'=>isset($vehicle_type[$vehicle['vehicle_type_id']])?$vehicle_type[$vehicle['vehicle_type_id']]:'',
						'total_delivered'=>isset($total[$time_start])?$total[$time_start]:0
					];
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

	public function actiondeleteaccount()
	{
		try {

			$code = intval(Yii::app()->input->post('code'));
			$model = CDriver::getDriver(Yii::app()->driver->id);
			if($model->verification_code==$code){
				$this->code = 1;
				$this->msg = t("Ok");
				$model->status = "deleted";
				$model->save();				
			} else $this->msg = t("Invalid verification code");
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionsetnotifications()
	{
		try {

			$enabled = $this->data['enabled']?$this->data['enabled']:0;
			$model = CDriver::getDriver(Yii::app()->driver->id);
			$model->notification = intval($enabled);
			if($model->save()){
				$this->code = 1;
				$this->msg = t("Ok");

				$avatar = CMedia::getImage($model->photo,$model->path,Yii::app()->params->size_image_thumbnail,
				CommonUtility::getPlaceholderPhoto('driver'));

				$user_data = array(
					'driver_id'=>$model->driver_id,
					'driver_uuid'=>$model->driver_uuid,
					'first_name'=>$model->first_name,
					'last_name'=>$model->last_name,
					'email_address'=>$model->email,
					'phone_prefix'=>$model->phone_prefix,
					'phone'=>$model->phone,
					'avatar'=>$avatar,
					'notification'=>$model->notification,
					'employment_type'=>$model->employment_type
				);
				$user_data = JWT::encode($user_data, CRON_KEY, 'HS256');
				$this->details = [
					'user_data'	=>$user_data
				];

			} else $this->msg = CommonUtility::parseError( $model->getErrors() );
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetreview()
	{
		try {

			$limit = 20;
			$page = intval(Yii::app()->input->post('page'));
			$page_raw = intval(Yii::app()->input->post('page'));
			if($page>0){
				$page = $page-1;
			}

			$criteria=new CDbCriteria();
			$criteria->alias="a";
			$criteria->select="a.*, b.path as path, avatar as logo";
			$criteria->condition = "driver_id=:driver_id AND a.status=:status";
			$criteria->join='LEFT JOIN {{client}} b on a.client_id = b.client_id ';
			$criteria->params  = array(
			':driver_id'=>Yii::app()->driver->id,
			':status'=>"publish"
			);
			$criteria->order = "date_created DESC";

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

			$model = AR_review::model()->findAll($criteria);
			if($model){
				$data = [];
				foreach ($model as $items) {
					$avatar = CMedia::getImage($items->logo,$items->path,Yii::app()->params->size_image_thumbnail,CommonUtility::getPlaceholderPhoto('customer'));
					$data[] = [
						'review'=>$items->review,
						'rating'=>intval($items->rating),
						'date_created'=>PrettyDateTime::parse(new DateTime($items->date_created)),
						'avatar'=>$avatar
					];
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

	public function actiongetreviewsummary()
	{
		try {

			$driver_id = Yii::app()->driver->id;
			$total = CReviews::reviewsCountDriver($driver_id);
			$review_summary = CReviews::summaryDriver($driver_id,$total);

			$data = array(
			  'total'=>$total,
			  'review_summary'=>$review_summary
			);

			$this->code = 1; $this->msg = "ok";
		    $this->details = $data;

		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiondeliveriesoverview()
	{
		try {

			$driver_id = Yii::app()->driver->id;

			$meta = AR_admin_meta::getValue('status_delivery_delivered');
            $delivery_status = isset($meta['meta_value'])?$meta['meta_value']:'';

			$total_delivered_percent=0;
			$total_delivered = CDriver::CountOrderStatus($driver_id,$delivery_status);
			$total_assigned =  CDriver::SummaryCountOrderTotal($driver_id);
			if($total_assigned>0){
			  $total_delivered_percent = round(($total_delivered/$total_assigned)*100);
			}

			$total_tip_percent = 0;
			$total_tip = CDriver::TotaLTips($driver_id,[$delivery_status]);
			$summary_tip = CDriver::SummaryTotaLTips($driver_id);
			if($summary_tip>0){
				$total_tip_percent = round(($total_tip/$summary_tip)*100);
			}

			$data = array(
				'total_delivered'=>$total_delivered,
				'total_delivered_percent'=>$total_delivered_percent,
				'total_tip'=>Price_Formatter::formatNumber($total_tip),
				'total_tip_percent'=>intval($total_tip_percent)
			);

			$this->code = 1; $this->msg = "ok";
			$this->details = $data;

		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionattachcarphoto()
	{
		try {

			$schedule_uuid = isset($this->data['schedule_uuid'])?$this->data['schedule_uuid']:'';
			$file_data = isset($this->data['file_data'])?$this->data['file_data']:'';
			$image_type = isset($this->data['image_type'])?$this->data['image_type']:'png';

			$result = [];
			if(!empty($file_data)){
				try {
					$result = CImageUploader::saveBase64Image($file_data,$image_type,"upload/car_proof");
				} catch (Exception $e) {
					$this->msg = t($e->getMessage());
					$this->responseJson();
				}
			} else {
				$result['path'] = isset($this->data['upload_path'])?$this->data['upload_path']:'';
				$result['filename'] = isset($this->data['featured_filename'])?$this->data['featured_filename']:'';
			}

			$model = AR_driver_schedule::model()->find("schedule_uuid=:schedule_uuid",[
				':schedule_uuid'=>$schedule_uuid
			]);
			if($model){

				$meta = new AR_driver_meta;
				$meta->reference_id = $model->schedule_id;
				$meta->meta_name = 'car_proof';
				$meta->meta_value1 = isset($result['filename'])?$result['filename']:'';
				$meta->meta_value2 = isset($result['path'])?$result['path']:'';

				if($meta->save()){
					$this->code = 1;
				    $this->msg = t("Ok");
				} else $this->msg = CommonUtility::parseError( $meta->getErrors() );

			} else $this->msg = t("Somethig went wrong the schedule not found");

		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionattachCarMultiplePhoto()
	{
		try {

			$data = isset($this->data['data'])?$this->data['data']:'';
			$schedule_uuid = isset($this->data['schedule_uuid'])?$this->data['schedule_uuid']:'';

			$model = AR_driver_schedule::model()->find("schedule_uuid=:schedule_uuid",[
				':schedule_uuid'=>$schedule_uuid
			]);

			$result = [];

			if(is_array($data) && count($data)>=1  && $model){
				foreach ($data as $key => $items) {
					try {
						$result[] = CImageUploader::saveBase64Image($items,"png","upload/car_proof");
					} catch (Exception $e) {
						$this->msg = t($e->getMessage());
						$this->responseJson();
					}
				}

				if(is_array($result) && count($result)>=1){
					foreach ($result as $items) {
						$meta = new AR_driver_meta;
						$meta->reference_id = $model->schedule_id;
						$meta->meta_name = 'car_proof';
						$meta->meta_value1 = isset($items['filename'])?$items['filename']:'';
				        $meta->meta_value2 = isset($items['path'])?$items['path']:'';
						$meta->save();
					}
					$this->code = 1;
				    $this->msg = t("Ok");
				} else $this->msg = t("Invalid data");

			} else $this->msg = t("Something went wrong the schedule not found");

		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionuploadfile()
	{
		try {

			$data = isset($this->data['data'])?$this->data['data']:'';
			$imageType = isset($this->data['imageType'])?$this->data['imageType']:'png';


			$upload_path = "upload/avatar";
			$upload_uuid = CommonUtility::createUUID("{{media_files}}",'upload_uuid');
			$filename = "$upload_uuid.$imageType";
			$path = CommonUtility::uploadDestination($upload_path)."/".$filename;

			$image_set_width = isset(Yii::app()->params['settings']['review_image_resize_width']) ? intval(Yii::app()->params['settings']['review_image_resize_width']) : 0;
			$image_set_width = $image_set_width<=0?500:$image_set_width;

			$image_driver = !empty(Yii::app()->params['settings']['image_driver'])?Yii::app()->params['settings']['image_driver']:Yii::app()->params->image['driver'];
			$manager = new ImageManager(array('driver' => $image_driver ));
			$image = $manager->make($data);
			$image_width = $manager->make($data)->width();
			if($image_width>$image_set_width){
				$image->resize(null, $image_set_width, function ($constraint) {
				    $constraint->aspectRatio();
				});
				$image->save($path);
			} else {
				$image->save($path,60);
			}

			$this->code = 1;
			$this->msg = "ok";
			$this->details = [
				'filename'=>$filename,
				'path'=>$upload_path
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionupdatelicensephoto()
	{
	    try {

			$model = CDriver::getDriver(Yii::app()->driver->id);

			$front_photo_filename = isset($this->data['front_photo_filename'])?$this->data['front_photo_filename']:'';
			$upload_path = isset($this->data['upload_path'])?$this->data['upload_path']:'';
			$file_data = isset($this->data['file_data'])?$this->data['file_data']:'';
			$image_type = isset($this->data['image_type'])?$this->data['image_type']:'';


			$back_photo_filename = isset($this->data['back_photo_filename'])?$this->data['back_photo_filename']:'';
			$photo_data_back = isset($this->data['photo_data_back'])?$this->data['photo_data_back']:'';
			$image_type_back = isset($this->data['image_type_back'])?$this->data['image_type_back']:'';

			$result = [];

			if(!empty($file_data)){
				$result = [];
				try {
					$result = CImageUploader::saveBase64Image($file_data,$image_type,"upload/license");
					$model->license_front_photo = isset($result['filename'])?$result['filename']:'';
				    $model->path_license = isset($result['path'])?$result['path']:'';
				} catch (Exception $e) {
					$this->msg = t($e->getMessage());
					$this->responseJson();
				}
			} else {
				$model->license_front_photo = $front_photo_filename;
				$model->path_license = $upload_path;
			}

			if(!empty($photo_data_back)){
				$result = [];
				try {
					$result = CImageUploader::saveBase64Image($photo_data_back,$image_type,"upload/license");
					$model->license_back_photo = isset($result['filename'])?$result['filename']:'';
				    $model->path_license = isset($result['path'])?$result['path']:'';
				} catch (Exception $e) {
					$this->msg = t($e->getMessage());
					$this->responseJson();
				}
			} else {
				$model->license_back_photo = $back_photo_filename;
				$model->path_license = $upload_path;
			}

			if($model->save()){
				$this->code = 1;
				$this->msg = t("Driver license photo succesfully updated.");
				$this->details = [
					'driver_id'=>$model->driver_id,
					'result'=>$result,
				];
			} else $this->msg = CommonUtility::parseError( $model->getErrors() );

		} catch (Exception $e) {
		    $this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actiongetLicensePhoto()
	{
		try {

			$model = CDriver::getDriver(Yii::app()->driver->id);
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'license_front_photo'=>$model->license_front_photo,
				'front_photo_url'=>CMedia::getImage($model->license_front_photo,$model->path_license),
				'license_back_photo'=>$model->license_back_photo,
				'back_photo_url'=>CMedia::getImage($model->license_back_photo,$model->path_license),
				'upload_path'=>$model->path_license
			];

		} catch (Exception $e) {
		    $this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actionregisterDevice()
	{
		try {

			$token = isset($this->data['token'])?$this->data['token']:'';
			$device_uiid =  isset($this->data['device_uiid'])?$this->data['device_uiid']:'';
			$platform = isset($this->data['platform'])?$this->data['platform']:'';

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
				$model->user_type = "driver";
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

		} catch (Exception $e) {
		    $this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actionupdateDevice()
	{
		try {

			$token = isset($this->data['token'])?$this->data['token']:'';
			$device_uiid =  isset($this->data['device_uiid'])?$this->data['device_uiid']:'';
			$platform = isset($this->data['platform'])?$this->data['platform']:'';

			$model = AR_device::model()->find("device_token = :device_token",[
				':device_token'=>$token
			]);
			if($model){
				$model->device_uiid = $device_uiid;
				$model->user_id = Yii::app()->driver->id;
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
				$model->user_type = "driver";
				$model->user_id = Yii::app()->driver->id;
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

		} catch (Exception $e) {
		    $this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actionupdateLocation()
	{

		$lat = Yii::app()->input->get('latitude');
		$lng = Yii::app()->input->get('longitude');
		$accuracy = Yii::app()->input->get('accuracy');
		$altitude = Yii::app()->input->get('altitude');
		$altitudeAccuracy = Yii::app()->input->get('altitudeAccuracy');
		$speed = Yii::app()->input->get('speed');
		$bearing = Yii::app()->input->get('bearing');
		$time = Yii::app()->input->get('time');
		$simulated = Yii::app()->input->get('simulated');

		try {

			$model = CDriver::getDriver(Yii::app()->driver->id);
			$model->latitude = $lat;
			$model->lontitude = $lng;
			$model->last_seen = CommonUtility::dateNow();

			if($model->save()){
				$this->code = 1;
				$this->msg = "Ok";
				$this->details = [
					'latitude'=>$lat,
					'longitude'=>$lng,
				];
			} else $this->msg = CommonUtility::parseError( $model->getErrors() );

		} catch (Exception $e) {
		    $this->msg = $e->getMessage();
		}

		try {

			$firebase_apikey = isset(Yii::app()->params['settings']['firebase_apikey']) ? trim(Yii::app()->params['settings']['firebase_apikey']) : '';
			$firebase_projectid = isset(Yii::app()->params['settings']['firebase_projectid']) ? trim(Yii::app()->params['settings']['firebase_projectid']) : '';

			$params  = [
				"driver_id" => ["doubleValue" => Yii::app()->driver->id ],
				"lat" => ["doubleValue" => $lat ],
				"lng" => ["doubleValue" => $lng ],
				"accuracy" => ["stringValue" => $accuracy ],
				"altitude" => ["stringValue" => $accuracy ],
				"altitudeAccuracy" => ["stringValue" => $altitudeAccuracy ],
				"speed" => ["stringValue" => $speed ],
				"bearing" => ["stringValue" => $bearing ],
				"time" => ["stringValue" => $time ],
				"simulated" => ["stringValue" => $simulated ],
				"created_at" => ["stringValue" => date("c") ],
			];

			CFirebase::addDocument($firebase_apikey,$firebase_projectid,Yii::app()->driver->driver_uuid,$params);

		} catch (Exception $e) {
		    $this->msg = $e->getMessage();
		}

		$this->responseJson();
	}

	public function actionlogLocation()
	{
		try {

			$latitude = Yii::app()->input->post('latitude');
			$longitude = Yii::app()->input->post('longitude');
			$accuracy = Yii::app()->input->post('accuracy');
			$altitude = Yii::app()->input->post('altitude');
			$altitudeAccuracy = Yii::app()->input->post('altitudeAccuracy');
			$speed = Yii::app()->input->post('speed');
			$bearing = Yii::app()->input->post('bearing');
			$time = Yii::app()->input->post('time');

			$model = new AR_driver_location;
			$model->driver_id = Yii::app()->driver->id;
			$model->latitude = $latitude;
			$model->longitude = $longitude;
			$model->accuracy = $accuracy;
			$model->altitude = $altitude;
			$model->altitudeAccuracy = $altitudeAccuracy;
			$model->speed = $speed;
			$model->bearing = $bearing;
			$model->time = $time;
			$model->created_at = CommonUtility::dateNow();
			$model->created_timestamp = CommonUtility::dateNow();

			if($model->save()){
				$this->code = 1;
				$this->msg = "Ok";
				$this->details = [
					'latitude'=>$latitude,
					'longitude'=>$longitude,
				];
			} else $this->msg = CommonUtility::parseError( $model->getErrors() );

		} catch (Exception $e) {
		    $this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actiongetpage()
	{
		try {

			$page = Yii::app()->input->post('page_id');
			$meta = AR_admin_meta::getValue($page);
			$page_id = isset($meta['meta_value'])?$meta['meta_value']:0;
			$model = PPages::pageDetailsByID($page_id, Yii::app()->language);
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'title'=>$model->title,
				'long_content'=>$model->long_content,
			];

		} catch (Exception $e) {
		    $this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actiondeclinereasonlist()
	{
		try {

			$model = AOrders::rejectionList('order_decline_reason',  Yii::app()->language);
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = $model;

		} catch (Exception $e) {
		    $this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actionorderhelplist()
	{
		try {

			$model = AOrders::rejectionList('order_help' ,  Yii::app()->language);
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = $model;

		} catch (Exception $e) {
		    $this->msg = $e->getMessage();
		}
		$this->responseJson();
	}
	public function actionorderhelppickup()
	{
		try {

			$model = AOrders::rejectionList('order_help_pickup',  Yii::app()->language);
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = $model;

		} catch (Exception $e) {
		    $this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actiongetAvailableShift()
	{
		try {

			$driver_id = Yii::app()->driver->id;		
			$model_driver = CDriver::getDriver($driver_id);
			$merchant_id = $model_driver->merchant_id;			
			
			$date_shift = isset($this->data['date'])?$this->data['date']:'';
			$page = isset($this->data['page'])?$this->data['page']:1;
			$zone_ids = isset($this->data['zone_ids'])?$this->data['zone_ids']:array();

			$month_label = Date_Formatter::date($date_shift,"MMM yyyy",true);
			$time_now = date("H:i:s");
			$date_now = date("Y-m-d H:i:s");

			$limit = 20;
			$page = intval($page);
			$page_raw = intval($page);
			if($page>0){
				$page = $page-1;
			}

			$criteria=new CDbCriteria();
			$criteria->alias = "a";
			$criteria->condition = "merchant_id=:merchant_id AND DATE(time_start)=:time_start AND status=:status
			AND ".q($date_now)." <= time_end
			AND shift_id NOT IN (
				select shift_id from {{driver_schedule}}
				where driver_id = ".q($driver_id)."
			)
			";
			$criteria->params  = array(
				':merchant_id'=>$merchant_id,				
				':time_start'=>$date_shift,
				':status'=>'active'
			);
			if(is_array($zone_ids) && count($zone_ids)>=1){
				$criteria->addInCondition("zone_id",(array)$zone_ids);
			}

			$criteria->order = "zone_id ASC,time_start ASC";
			
			$count = AR_driver_shift_schedule::model()->count($criteria);
			$pages=new CPagination($count);
			$pages->pageSize=$limit;
			$pages->setCurrentPage( $page );
			$pages->applyLimit($criteria);
			$page_count = $pages->getPageCount();

			if($page>0){
				if($page_raw>$page_count){
					$this->code = 3;
					$this->msg = t("end of results");
					$this->details = [
						'month_label'=>$month_label,
					];
					$this->responseJson();
				}
			}

			if($model = AR_driver_shift_schedule::model()->findAll($criteria)){				

				$zone_list = CommonUtility::getDataToDropDown("{{zones}}",'zone_id','zone_name',"
		        WHERE merchant_id = ".q($merchant_id)." ","ORDER BY zone_name ASC");

				$data = [];
				foreach ($model as $items) {
					$data[] = [
						'shift_uuid'=>$items->shift_uuid,
						'zone_id'=>$items->zone_id,
						'zone_name'=>isset($zone_list[$items->zone_id])?$zone_list[$items->zone_id]:t("uknown"),
						'date_shift'=>Date_Formatter::date($items->time_start,"yyyy-MM-dd",true),
						'time_start'=>Date_Formatter::Time($items->time_start),
						'time_end'=>Date_Formatter::Time($items->time_end),
					];
				}
				$this->code = 1;
				$this->msg = "ok";
				$this->details = [
					'page_raw'=>$page_raw,
					'page_count'=>$page_count,
					'count'=>$count,
					'month_label'=>$month_label,
					'data'=>$data
				];
			} else {
				$this->msg = t("No results");
				$this->details = [
					'month_label'=>$month_label,
				];
			}

		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetZone()
	{
		try {

			$driver_id = Yii::app()->driver->id;
			$model_driver = CDriver::getDriver($driver_id);
			$merchant_id = $model_driver->merchant_id;						

			$zone_list = CommonUtility::getDataToDropDown("{{zones}}",'zone_id','zone_name',"
		    WHERE merchant_id = ".q($merchant_id)."","ORDER BY zone_name ASC");

			if(is_array($zone_list) && count($zone_list)>=1){
				$data = CommonUtility::ArrayToLabelValue($zone_list);
				$this->code = 1;
				$this->msg = "Ok";
				$this->details = [
					'data'=>$data
				];
			} else $this->msg = t(HELPER_NO_RESULTS);

		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiontakeShift()
	{
		try {

			$shift_uuid = Yii::app()->input->post('shift_uuid');
			$driver_id = Yii::app()->driver->id;
			$model_driver = CDriver::getDriver($driver_id);
			$merchant_id = $model_driver->merchant_id;						

			$data = CDriver::getShiftSchedule($shift_uuid);

			try {
				$vehicle_data = CDriver::getVehicleByDriverID($driver_id);
				$model = new AR_driver_schedule();
				$model->merchant_id = $merchant_id;
				$model->driver_id = $driver_id;
				$model->vehicle_id = $vehicle_data->vehicle_id;
				$model->zone_id = $data->zone_id;
				$model->shift_id = $data->shift_id;
				$model->time_start = $data->time_start;
				$model->time_end = $data->time_end;
				if($model->save()){
					$this->code = 1;
				    $this->msg = t("Succesfully created shift");
				} else $this->msg = CommonUtility::parseError( $model->getErrors() );
			} catch (Exception $e) {
				$this->msg = t("You don't have vehicle added, add your vehicle first.");
			}
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actioncurrentShift()
	{
		try {

			$model_driver = CDriver::getDriver(Yii::app()->driver->id);
            $merchant_id = $model_driver->merchant_id;						

			$date = Yii::app()->input->post('date');
			$criteria=new CDbCriteria();
			$criteria->addCondition("DATE(time_start)=:time_start AND driver_id=:driver_id");
			$criteria->params = [
				':time_start'=>$date,
				':driver_id'=>Yii::app()->driver->id
			];
			$total_shift = AR_driver_schedule::model()->count($criteria);
			if($model = AR_driver_schedule::model()->findAll($criteria)){
				$data = [];
				$zone_list = CommonUtility::getDataToDropDown("{{zones}}",'zone_id','zone_name',"
		        WHERE merchant_id = ".q($merchant_id)." ","ORDER BY zone_name ASC");

				foreach ($model as $items) {
					$shift_status = 'pending'; $can_end_shift = false;
					if(!empty($items->shift_time_started) && !empty($items->shift_time_ended)){
						$shift_status = "ended";
					} else if ( !empty($items->shift_time_started) && empty($items->shift_time_ended) ){
						$time_start = date("Y-m-d g:i:s a",strtotime($items->time_start));
						$time_end = date("Y-m-d g:i:s a",strtotime($items->time_end));
						$diff = CommonUtility::dateDifference($time_start,$time_end);

						$now = date("Y-m-d g:i:s a");
						$shift_time_started = date("Y-m-d g:i:s a",strtotime($items->shift_time_started));
						$diff2 = CommonUtility::dateDifference($shift_time_started,$now);

						$ended = false;
						if(is_array($diff2) && count($diff2)>=1){
							if($diff2['days']>$diff['days']){
								$ended = true;
							} else {
								if($diff2['hours']>$diff['hours']){
									$ended = true;
								}
							}
						}
						$shift_status = $ended?"ended":"on_going";

						if(is_null($items->shift_time_ended)){							
							if(is_array($diff2) && count($diff2)>=1){
								if($diff2['days']>1 || $diff2['hours']>1){
									$can_end_shift = true;
								}
							}							
						}
					}
					$data[] = [
						'schedule_id'=>$items->schedule_uuid,
						'zone_id'=>$items->zone_id,
						'zone_name'=>isset($zone_list[$items->zone_id])?$zone_list[$items->zone_id]:'',
						'date_start'=>Date_Formatter::date($items->time_start,"yyyy-MM-dd",true),
						'time_start'=>Date_Formatter::Time($items->time_start),
						'time_end'=>Date_Formatter::Time($items->time_end),
						'shift_status'=>$shift_status,
						'can_end_shift'=>$can_end_shift
					];
				}
				$this->code = 1;
				$this->msg = "Ok";
				$this->details = [
					'data'=>$data,
					'total_shift'=>$total_shift
				];
			} else $this->msg = t(HELPER_NO_RESULTS);

		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiondeleteSchedule()
	{
		try {

			$schedule_uuid = Yii::app()->input->post('schedule_id');
			$model = AR_driver_schedule::model()->find("schedule_uuid=:schedule_uuid",[
				':schedule_uuid'=>$schedule_uuid
			]);
			if($model){
				$model->delete();
				$this->code = 1;
				$this->msg = t("Schedule deleted succesfully");
			} else $this->msg = t(HELPER_RECORD_NOT_FOUND);
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionrequestBreak()
	{
		try {

			$schedule_uuid = Yii::app()->input->post('schedule_uuid');
			$duration = Yii::app()->input->post('duration');
			$model = AR_driver_schedule::model()->find("schedule_uuid=:schedule_uuid",[
				':schedule_uuid'=>$schedule_uuid
			]);
			if($model){
				$model->scenario = 'request_break';
				$model->break_duration = $duration;
				$model->break_started = CommonUtility::dateNow();
				if($model->save()){
					$this->code = 1;
				    $this->msg = t("Your break request is succesful, You can cancel or end your break ealier.");
				} else $this->msg = CommonUtility::parseError( $model->getErrors() );
			} else $this->msg = t(HELPER_RECORD_NOT_FOUND);
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionendBreak()
	{
		try {

			$id = Yii::app()->input->post('id');
			$model = AR_driver_break::model()->find("id=:id",[
				":id"=>intval($id)
			]);
			if($model){
				$model->break_ended = CommonUtility::dateNow();
				if($model->save()){
					$this->code = 1;
				    $this->msg = "Ok";
				} else $this->msg = CommonUtility::parseError( $model->getErrors() );
			} else $this->msg = t(HELPER_RECORD_NOT_FOUND);
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetWalletBalance()
	{
		try {

			$this->setDefaultCurrency();

			$bank_accout = []; $cashout_fee = 0; $cashout_miximum = 0;
			$payload = isset($this->data['payload'])?$this->data['payload']:'';

			try {
			    $card_id = CWallet::getCardID( Yii::app()->params->account_type['driver'] , Yii::app()->driver->id );
				$balance = CWallet::getBalance($card_id);
		    } catch (Exception $e) {
			    $balance = 0;
		    }

			if(in_array('get_bank_account',(array)$payload)){
				try {
				     $bank_accout = CDriver::getBankAccount(Yii::app()->driver->id);
					 $bank_accout = [
						'account_type'=>t("Bank account"),
						'account_number'=>CommonUtility::mask($bank_accout['account_number_iban'])
					 ];
			    } catch (Exception $e) {
				}
			}

			if(in_array('processing_fee',(array)$payload)){
				$options = OptionsTools::find(['driver_cashout_fee']);
				$cashout_fee = isset($options['driver_cashout_fee'])?$options['driver_cashout_fee']:0;
			}

			if(in_array('cashout_miximum',(array)$payload)){
				$options = OptionsTools::find(['driver_cashout_miximum']);
				$cashout_miximum = isset($options['driver_cashout_miximum'])?$options['driver_cashout_miximum']:0;
			}

			$total_cashout = floatval($balance)- floatval($cashout_fee);

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

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'balance'=>[
					'raw'=>$balance,
					'pretty'=>Price_Formatter::formatNumber($balance),
				],
				'bank_accout'=>$bank_accout,
				'cashout_fee'=>[
					'value'=>floatval($cashout_fee),
					'pretty'=>Price_Formatter::formatNumber($cashout_fee),
				],				
				'cashout_miximum'=>[
					'value'=>$cashout_miximum,
					'pretty'=>Price_Formatter::formatNumber($cashout_miximum)
				],
				'money_config'=>$money_config
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

    public function actionwalletHistory()
    {
        try {

			$this->setDefaultCurrency();
					
            $data = array(); $card_id = 0;

			$transaction_date = trim(Yii::app()->input->post('date'));
			$date_start = trim(Yii::app()->input->post('date_start'));
			$date_end = trim(Yii::app()->input->post('date_end'));

            try {
                $card_id = CWallet::getCardID(Yii::app()->params->account_type['driver'], Yii::app()->driver->id );
            } catch (Exception $e) {
                // do nothing
            }

            $limit = Yii::app()->params->list_limit;

            $page = intval(Yii::app()->input->post('page'));
			$page_raw = intval(Yii::app()->input->post('page'));
			if($page>0){
				$page = $page-1;
			}

            $criteria=new CDbCriteria();
			$criteria->condition = "card_id=:card_id";
			if(!empty($transaction_date)){
				$criteria->addCondition("DATE(transaction_date)=:transaction_date");
				$criteria->params  = array(
					':card_id'=>intval($card_id),
					':transaction_date'=>$transaction_date,
				);
			} else if ( !empty($date_start)) {
				$criteria->params  = array(
					':card_id'=>intval($card_id),
				);
				$criteria->addBetweenCondition("DATE(transaction_date)",$date_start,$date_end);
			} else {
				$criteria->params  = array(
					':card_id'=>intval($card_id),
				);
			}
			$criteria->order = "transaction_id DESC";

		    $count=AR_wallet_transactions::model()->count($criteria);
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

            if($model = AR_wallet_transactions::model()->findAll($criteria)){
                $data = array();
                foreach ($model as $item) {
                    $description = Yii::app()->input->xssClean($item->transaction_description);
                    $parameters = json_decode($item->transaction_description_parameters,true);
                    if(is_array($parameters) && count($parameters)>=1){
                        $description = t($description,$parameters);
                    }

                    $transaction_amount = Price_Formatter::formatNumber($item->transaction_amount);
                    switch ($item->transaction_type) {
                        case "debit":
                        case "payout":
                            $transaction_amount = "(".Price_Formatter::formatNumber($item->transaction_amount).")";
                            break;
                    }

                    $data[] = [
                        'transaction_date'=>Date_Formatter::date($item->transaction_date,"E, MMMM dd, yyyy",true),
                        'transaction_description'=>$description,
                        'transaction_amount'=>$transaction_amount,
                        'running_balance'=>Price_Formatter::formatNumber($item->running_balance),
                    ];
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

	public function actiongetEarnings()
	{
		try {

			$this->setDefaultCurrency();
			$driver_id = Yii::app()->driver->id;
			$date_start = trim(Yii::app()->input->post('date_start'));
			$date_end = trim(Yii::app()->input->post('date_end'));
			$chart_type = trim(Yii::app()->input->post('chart_type'));

			$card_id = 0; $balance = 0; $max_cashout_amount = 0;
			try {
                $card_id = CWallet::getCardID(Yii::app()->params->account_type['driver'], Yii::app()->driver->id );
				$balance = CDriver::getEarningsByRange($card_id,$date_start,$date_end);
            } catch (Exception $e) {
                // do nothing
            }

			$charts_data = CDriver::EarningCharts($card_id,$date_start,$date_end);

			$meta = AR_admin_meta::getValue('status_delivery_delivered');
			$status_delivered = isset($meta['meta_value'])?$meta['meta_value']:'delivered';
			$total_trips = CDriver::getTotalTrips($driver_id,$date_start,$date_end,[$status_delivered]);

			$date_range = $chart_type=='daily'?Date_Formatter::date($date_start): Date_Formatter::date($date_start)." - ".Date_Formatter::date($date_end);

			$options = OptionsTools::find(['driver_cashout_miximum']);
			$max_cashout_amount = isset($options['driver_cashout_miximum'])?$options['driver_cashout_miximum']:0;

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'date_range'=>$date_range,
				'balance'=>[
					'value'=>$balance,
					'pretty'=>Price_Formatter::formatNumber($balance)
				],
				'charts_data'=>$charts_data,
				'max_cashout'=>$max_cashout_amount>0?t("{amount} is the max you can cash out each day",['{amount}'=>Price_Formatter::formatNumber($max_cashout_amount)]):'',
				'summary'=>[
					'total_trip'=>$total_trips
				]
			];
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetEarningDetails()
	{
		try {

			$this->setDefaultCurrency();
			
			$driver_id = Yii::app()->driver->id;
			$date_start = trim(Yii::app()->input->post('date_start'));
			$date_end = trim(Yii::app()->input->post('date_end'));
			$chart_type = trim(Yii::app()->input->post('chart_type'));

			$card_id = 0; $balance = 0; $max_cashout_amount = 0;
			try {
                $card_id = CWallet::getCardID(Yii::app()->params->account_type['driver'], Yii::app()->driver->id );
				//$balance = CDriver::getEarningsByRange($card_id,$date_start,$date_end);
            } catch (Exception $e) {
            }

			//$total_collected_cash = CDriver::EarningByMeta($card_id,$date_start,$date_end,['collected_payment'],'debit');
			$total_tips = CDriver::EarningByMeta($card_id,$date_start,$date_end,['payout_tip']);
			$total_delivery = CDriver::EarningByMeta($card_id,$date_start,$date_end,['payout_delivery_fee','payout_commission','payout_fixed','payout_fixed_and_commission']);
			$total_incentives = CDriver::EarningByMeta($card_id,$date_start,$date_end,['payout_incentives']);
			$total_credit = CDriver::EarningAdjustment($card_id,$date_start,$date_end,['adjustment']);
			$total_debit = CDriver::EarningAdjustment($card_id,$date_start,$date_end,['adjustment'],'debit');
			$total_adjustment = $total_credit - $total_debit;
			//$total_cashout = CDriver::GetTotalCashout($card_id,$date_start,$date_end);

			$charts_data = CDriver::EarningCharts($card_id,$date_start,$date_end);

			$date_range = $chart_type=='daily'?Date_Formatter::date($date_start): Date_Formatter::date($date_start)." - ".Date_Formatter::date($date_end);

			$balance = floatval($total_delivery) + floatval($total_tips) + floatval($total_incentives)  + floatval($total_adjustment);

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'date_range'=>$date_range,
				'balance'=>Price_Formatter::formatNumber($balance),
				'summary'=>[
					'total_tips'=>Price_Formatter::formatNumber($total_tips),
					'total_delivery'=>Price_Formatter::formatNumber($total_delivery),
					'total_incentives'=>Price_Formatter::formatNumber($total_incentives),
					'total_adjustment'=>Price_Formatter::formatNumber($total_adjustment),
					//'total_collected_cash'=>Price_Formatter::formatNumber($total_collected_cash),
					//'total_cashout'=>Price_Formatter::formatNumber($total_cashout)
				],
				'charts_data'=>$charts_data
			];

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionAddbankaccount()
	{
		try {
			$model = new AR_driver_meta;
			$model->reference_id = Yii::app()->driver->id;
			$model->meta_name = 'bank_information';
			$model->meta_value1  = json_encode(array(
				'provider'=>"bank",
				'account_name'=> isset($this->data['account_name'])?$this->data['account_name']:'',
				'account_number_iban'=> isset($this->data['account_number_iban'])?$this->data['account_number_iban']:'',
				'swift_code'=> isset($this->data['swift_code'])?$this->data['swift_code']:'',
				'bank_name'=> isset($this->data['bank_name'])?$this->data['bank_name']:'',
				'bank_branch'=> isset($this->data['bank_branch'])?$this->data['bank_branch']:''
			));
			if($model->save()){
				$this->code = 1;
				$this->msg = t("Bank succesfully added");
			} else $this->msg = CommonUtility::parseError( $model->getErrors() );
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionRequestPayout()
	{
		try {

			//$balance = 0;
			$driver_id = Yii::app()->driver->id;
			$cashout_amount = floatval(Yii::app()->input->post('amount'));

			$card_id = CWallet::getCardID( Yii::app()->params->account_type['driver'] , $driver_id );
			//$balance = CWallet::getBalance($card_id);
			$bank_accout = CDriver::getBankAccount(Yii::app()->driver->id);

			$options = OptionsTools::find(['driver_cashout_fee']);
			$cashout_fee = isset($options['driver_cashout_fee'])?$options['driver_cashout_fee']:0;

			$total_cashout = $cashout_amount;
			$bank_account = CommonUtility::mask($bank_accout['account_number_iban']);

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
            $multicurrency_enabled = $multicurrency_enabled==1?true:false;       
			
			$model_driver = CDriver::getDriver(Yii::app()->driver->id);
            $merchant_id = $model_driver->merchant_id;
			$admin_base_currency = Price_Formatter::$number_format['currency_code'];
			$attr = OptionsTools::find(['merchant_default_currency'],$merchant_id);
			if($multicurrency_enabled){
				$merchant_base_currency = isset($attr['merchant_default_currency'])? (!empty($attr['merchant_default_currency'])?$attr['merchant_default_currency']:$admin_base_currency) :$admin_base_currency;						
			} else $merchant_base_currency = $admin_base_currency;

			$driver_default_currency = !empty($model_driver->default_currency)?$model_driver->default_currency:$merchant_base_currency;	            
			
			$exchange_rate_merchant_to_admin = 1; $exchange_rate_admin_to_merchant=1;
			if($multicurrency_enabled && $admin_base_currency!=$driver_default_currency){
                $exchange_rate_merchant_to_admin = CMulticurrency::getExchangeRate($driver_default_currency,$admin_base_currency);
                $exchange_rate_admin_to_merchant = CMulticurrency::getExchangeRate($admin_base_currency,$driver_default_currency);
            }  			
			
			$transaction_id = CDriver::requestPayout([
				'reference_id'=>$merchant_id,
				'admin_base_currency'=>$admin_base_currency,
				'driver_default_currency'=>$driver_default_currency,
				'exchange_rate_merchant_to_admin'=>$exchange_rate_merchant_to_admin,
				'exchange_rate_admin_to_merchant'=>$exchange_rate_admin_to_merchant
			],$card_id,$total_cashout,$cashout_fee,$bank_account);

		    $params = array();
    	    foreach ($bank_accout as $itemkey=>$item) {
    	   	  $params[]=array(
    	   	    'transaction_id'=>intval($transaction_id),
    	   	    'meta_name'=>$itemkey,
    	   	    'meta_value'=>$item,
    	   	    'date_created'=>CommonUtility::dateNow(),
    	   	    'ip_address'=>CommonUtility::userIp(),
    	   	  );
    	    }
    	    $builder=Yii::app()->db->schema->commandBuilder;
		    $command=$builder->createMultipleInsertCommand('{{wallet_transactions_meta}}', $params );
		    $command->execute();

    	    $this->code = 1; $this->msg = t("Cashout request successfully logged");
    	    $this->details = $transaction_id;

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetCashoutTransaction()
	{
		try {

			$date_now = date("Y-m-d");
			$transaction_id = intval(Yii::app()->input->post('transaction_id'));
			$model = AR_wallet_transactions::model()->findByPk($transaction_id);

			if($model){
				$card_id = CWallet::getCardID( Yii::app()->params->account_type['driver'] , Yii::app()->driver->id );
				$cashout_left = CDriver::getCashoutRemaining($card_id,$date_now);
				$this->code = 1;
				$this->msg = "Ok";
				$this->details = [
					'cashout'=>t("{amount} is on its way",['{amount}'=> Price_Formatter::formatNumber($model->transaction_amount) ]),
					'cashout_left'=>$cashout_left>0?t("You have {count} cash out(s) left today",['{count}'=>$cashout_left]):''
				];
			} else $this->msg = t("Transaction not found");

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetCashoutHistory()
	{
		try {

			$this->setDefaultCurrency();
			
			$data = [];	$limit = 10;

			$page = isset($this->data['page'])?intval($this->data['page']):0;
			$page_raw = isset($this->data['page'])?intval($this->data['page']):0;

			$date_start = isset($this->data['date_start'])?trim($this->data['date_start']):'';
			$date_end = isset($this->data['date_end'])?trim($this->data['date_end']):'';

			if($page>0){
				$page = $page-1;
			}


			$driver_id = Yii::app()->driver->id;
			$card_id = CWallet::getCardID( Yii::app()->params->account_type['driver'] , $driver_id );

			$criteria=new CDbCriteria();
			$criteria->addCondition("card_id=:card_id AND transaction_type=:transaction_type");
			$criteria->params = [
				':card_id'=>intval($card_id),
				':transaction_type'=>"cashout"
			];
			$criteria->order = "transaction_id DESC";
			//$criteria->addInCondition("status",['paid','unpaid']);

			if(!empty($date_start) && !empty($date_end) ){
				$criteria->addBetweenCondition("DATE(transaction_date)",$date_start,$date_end);
			}

			$count=AR_wallet_transactions::model()->count($criteria);
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

			if($model = AR_wallet_transactions::model()->findAll($criteria)){
				$data = [];
				foreach ($model as $items) {
					$description = Yii::app()->input->xssClean($items->transaction_description);
                    $parameters = json_decode($items->transaction_description_parameters,true);
                    if(is_array($parameters) && count($parameters)>=1){
                        $description = t($description,$parameters);
                    }
					$data[] = [
						'amount'=>Price_Formatter::formatNumber($items->transaction_amount),
						'description'=>$description,
						'status'=>t($items->status),
						'status_raw'=>$items->status,
						'date'=>Date_Formatter::date($items->transaction_date),
						'is_paid'=>$items->status=='paid'?true:false
					];
				}
				$this->code = 1;
				$this->msg = "OK";
				$this->details = [
					'page_raw'=>$page_raw,
					'page_count'=>$page_count,
					'data'=>$data
				];
			} else $this->msg = t(HELPER_NO_RESULTS);
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionPaymentMethod()
	{
		try {

		   $data = array(); $payments_credentials=array();
		   $data = CPayments::getPaymentListByFilter(1,['stripe','paypal']);
		   $payments_credentials = CPayments::getPaymentCredentials(0,'',2);

		   $this->code = 1;
		   $this->msg = "OK";
		   $this->details = array(
		     'data'=>$data,
			 'credentials'=>$payments_credentials
		   );
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetPaymentSaved()
	{
		try {

			$driver_id = Yii::app()->driver->id;
			$default_payment_uuid = '';

			$data = CPayments::DriverSavedPaymentList($driver_id);
			$default_payment_uuid = CPayments::driverDefaultPayment($driver_id);

			$this->code = 1;
		    $this->msg = "ok";
		    $this->details = array(
		      'default_payment_uuid'=>$default_payment_uuid,
		      'data'=>$data,
		    );

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetPaymentDetails()
	{
		try {

			$this->setDefaultCurrency();
			$driver_id = Yii::app()->driver->id;
			$payment_uuid = Yii::app()->input->post('payment_uuid');

			$model = AR_driver_payment_method::model()->find("driver_id=:driver_id AND payment_uuid=:payment_uuid",[
				'driver_id'=>intval($driver_id),
				':payment_uuid'=>$payment_uuid,
			]);
			if($model){
				$this->code = 1;
				$this->msg = "Ok";
				//$currency = AttributesTools::defaultCurrency();
				$currency = Price_Formatter::$number_format['currency_code'];
				$this->details = [
					'payment_uuid'=>$model->payment_uuid,
					'payment_code'=>$model->payment_code,
					'currency'=>$currency
				];				
			} else $this->msg = t("Payment details not found");
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetCashinTransaction()
	{
		try {

			$this->setDefaultCurrency();

			$driver_id = Yii::app()->driver->id;
			$transaction_id = Yii::app()->input->post('transaction_id');

			$stmt = "
			SELECT a.transaction_amount
			FROM {{wallet_transactions}} a
			WHERE a.transaction_id IN (
				select transaction_id from {{wallet_transactions_meta}}
				where meta_name='cashin_payment_reference'
				and meta_value = ".q($transaction_id)."
			)
			LIMIT 0,1
			";
			if($res = Yii::app()->db->createCommand($stmt)->queryRow()){
				$this->code = 1	;
				$this->msg = "Ok";
				$this->details = [
					'message'=>t("You have successfully cash in with the amount {amount} with payment reference #{transaction_id}",
					[
						'{amount}'=> Price_Formatter::formatNumber($res['transaction_amount']),
						'{transaction_id}'=>$transaction_id
				    ]),
				];
			} else $this->msg = t(HELPER_NO_RESULTS);
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
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
				$model = new AR_driver_payment_method();
				$model->scenario = "insert";
				$model->driver_id =  Yii::app()->driver->id;
				$model->payment_code = $payment_code;
				$model->as_default = intval(1);
				$model->attr1 = $payment?$payment->payment_name:'unknown';
				$model->merchant_id = intval($merchant_id);
				if($model->save()){
					$this->code = 1;
		    		$this->msg = t("Succesful");
				} else $this->msg = CommonUtility::parseError($model->getErrors());
		    } else $this->msg = t("Payment provider not found");

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiontimeoutAcceptOrder()
	{
		try {

			$driver_id = Yii::app()->driver->id;
			$order_uuid = Yii::app()->input->post('order_uuid');
			$model = COrders::get($order_uuid);
			if($model){
				$model->scenario = "timeout_accept_order";
				$model->old_driver_id = $model->driver_id;
				$model->driver_id = 0;
				$model->vehicle_id = 0;
				$model->delivery_status = 'unassigned';
				if($model->save()){
					$this->code = 1;
					$this->msg = "Ok";
				} else $this->msg = CommonUtility::parseError( $model->getErrors() );
			} else $this->msg = t(HELPER_RECORD_NOT_FOUND);

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetBankinfo()
	{
		try {

			$driver_id = Yii::app()->driver->id;
			$model = AR_driver_meta::model()->find("reference_id=:reference_id AND meta_name=:meta_name",[
				':reference_id'=>$driver_id,
				':meta_name'=>"bank_information"
			]);
			if($model){
				$data = json_decode($model->meta_value1,true);
				$this->code = 1;
				$this->msg = "Ok";
				$this->details = $data;
			} else $this->msg = t(HELPER_NO_RESULTS);
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionupdateBankInfo()
	{
		try {

			$driver_id = Yii::app()->driver->id;
			$model = AR_driver_meta::model()->find("reference_id=:reference_id AND meta_name=:meta_name",[
				':reference_id'=>$driver_id,
				':meta_name'=>"bank_information"
			]);
			if(!$model){
				$model = new AR_driver_meta();
			}
			$data = [
				'provider'=>"bank",
				'account_name'=>isset($this->data['account_name'])?$this->data['account_name']:'',
				'account_number_iban'=>isset($this->data['account_number_iban'])?$this->data['account_number_iban']:'',
				'swift_code'=>isset($this->data['swift_code'])?$this->data['swift_code']:'',
				'bank_name'=>isset($this->data['bank_name'])?$this->data['bank_name']:'',
				'bank_branch'=>isset($this->data['bank_branch'])?$this->data['bank_branch']:''
			];
			$model->reference_id = $driver_id;
			$model->meta_name = "bank_information";
			$model->meta_value1 = json_encode($data);
			if($model->save()){
				$this->code = 1;
				$this->msg = t(Helper_settings_saved);
			} else $this->msg = CommonUtility::parseError( $model->getErrors() );
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiondeletePayment()
	{
		try {

			$driver_id = Yii::app()->driver->id;
			$payment_uuid = Yii::app()->input->post('payment_uuid');
			$model = CDriver::getPayment($driver_id,$payment_uuid);
			$model->delete();
			$this->code = 1;
			$this->msg = "Ok";
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionsetDefaultPayment()
	{
		try {

			$driver_id = Yii::app()->driver->id;
			$payment_uuid = Yii::app()->input->post('payment_uuid');
			$model = CDriver::getPayment($driver_id,$payment_uuid);
			$model->as_default = 1;
			if($model->save()){
				$this->code = 1;
				$this->msg = t(Helper_settings_saved);
			} else $this->msg = CommonUtility::parseError( $model->getErrors() );
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetRemainingCashBalance()
	{
		try {

			$driver_id = Yii::app()->driver->id;

			$allowed_amount  = 0;
			try {
				$driver = CDriver::getDriver($driver_id);
				$allowed_amount = $driver->allowed_offline_amount;
			} catch (Exception $e) {

			}

			if($allowed_amount>0){
				try {
					$card_id = CWallet::getCardID( Yii::app()->params->account_type['driver'] ,$driver_id );
					$balance = CWallet::getBalance($card_id);
				} catch (Exception $e) {
					$balance = 0;
				}
				$balance = $balance<=0?$balance*-1:$balance;
				$balance_total = $allowed_amount - $balance;
				// dump("allowed_amount=>$allowed_amount");
				// dump("balance=>$balance");
				// dump("balance_total=>$balance_total");
				$this->code = 1;
				$this->msg = "Ok";
				$this->details = [
					'balance'=>Price_Formatter::formatNumber($balance_total),
					'balance_raw'=>$balance_total,
				];
			} else $this->msg = "unlimited";

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetVehicleInfo()
	{
		try {

			$driver_id = Yii::app()->driver->id;
			$model = AR_driver_vehicle::model()->find("driver_id=:driver_id",[
				':driver_id'=>intval($driver_id)
			]);

			if($model){
				$this->code = 1;
				$this->msg = "Ok";
				$this->details = [
					'vehicle_uuid'=>$model->vehicle_uuid,
					'vehicle_type_id'=>intval($model->vehicle_type_id),
					'plate_number'=>$model->plate_number,
					'maker'=>intval($model->maker),
					'model'=>$model->model,
					'color'=>$model->color,
					'has_photo'=>!empty($model->photo)?true:false,
					'url_photo'=>CMedia::getImage($model->photo,$model->path),
					'photo'=>$model->photo,
					'path'=>$model->path
				];
			} else $this->msg = t(HELPER_RECORD_NOT_FOUND);

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

    public function actionupdateAvatar()
    {
        try {

			$upload_path = Yii::app()->input->post('upload_path');
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

				if(empty($upload_path)){
					$upload_path = "upload/all";
				}

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
        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

	public function actionsaveVehicleInfo()
	{
		try {

			$model_driver = CDriver::getDriver(Yii::app()->driver->id);
			$merchant_id = $model_driver->merchant_id;

			$vehicle_uuid = isset($this->data['vehicle_uuid'])?$this->data['vehicle_uuid']:'';
			$model = AR_driver_vehicle::model()->find("vehicle_uuid=:vehicle_uuid AND driver_id=:driver_id",[
				':vehicle_uuid'=>$vehicle_uuid,
				':driver_id'=>Yii::app()->driver->id
			]);
			if(!$model){
				$model = new AR_driver_vehicle();
			}

			$file_data = isset($this->data['file_data'])?$this->data['file_data']:'';
			$image_type = isset($this->data['image_type'])?$this->data['image_type']:'png';

			if(!empty($file_data)){
				$result = [];
				try {
					$result = CImageUploader::saveBase64Image($file_data,$image_type,"upload/vehicle");
					$model->photo = isset($result['filename'])?$result['filename']:'';
				    $model->path = isset($result['path'])?$result['path']:'';
				} catch (Exception $e) {
					$this->msg = t($e->getMessage());
					$this->responseJson();
				}
			} else {
				$model->path = isset($this->data['upload_path'])?$this->data['upload_path']:'';
			    $model->photo = isset($this->data['featured_filename'])?$this->data['featured_filename']:'';
			}

			$model->merchant_id = intval($merchant_id);
			$model->driver_id = Yii::app()->driver->id;
			$model->vehicle_type_id = isset($this->data['vehicle_type_id'])? intval($this->data['vehicle_type_id']) :0;
			$model->plate_number = isset($this->data['plate_number'])?$this->data['plate_number']:'';
			$model->maker = isset($this->data['maker'])? intval($this->data['maker']) :0;
			$model->model = isset($this->data['model'])?$this->data['model']:'';
			$model->color = isset($this->data['color'])?$this->data['color']:'';
			
			if($model->save()){
				$this->code = 1;
				$this->msg = t(Helper_success);
			} else $this->msg = CommonUtility::parseError( $model->getErrors() );
		} catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function setDefaultCurrency()
	{
		$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
        $multicurrency_enabled = $multicurrency_enabled==1?true:false;       

		$model_driver = CDriver::getDriver(Yii::app()->driver->id);
		$merchant_id = $model_driver->merchant_id;		

		$admin_base_currency = Price_Formatter::$number_format['currency_code'];		
		$attr = OptionsTools::find(['merchant_default_currency'],$merchant_id);
		if($multicurrency_enabled){
			$merchant_base_currency = isset($attr['merchant_default_currency'])? (!empty($attr['merchant_default_currency'])?$attr['merchant_default_currency']:$admin_base_currency) :$admin_base_currency;						
		} else $merchant_base_currency = $admin_base_currency;

		$driver_default_currency = !empty($model_driver->default_currency)?$model_driver->default_currency:$merchant_base_currency;		

		if(!empty($driver_default_currency)){
			Price_Formatter::init($model_driver->default_currency);
		}
	}

	public function actiongetCashDomination()
	{
		try {

			$this->setDefaultCurrency();

			try {
				$cashin_denomination = CDriver::getDenomination('cashin_denomination');
				$cashin_denomination = CommonUtility::ArrayToLabelValue($cashin_denomination);
			} catch (Exception $e) {
				$cashin_denomination = [];
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

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'data'=>$cashin_denomination,
				'money_config'=>$money_config
			];

		} catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actiongetOnlineStatus()
	{
		try {

			$model_driver = CDriver::getDriver(Yii::app()->driver->id);						
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'is_online'=>$model_driver->is_online==1?true:false
			];
		} catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actionchangeOnlineStatus()
	{
		try {

			$is_online = Yii::app()->input->post('is_online');			
			$model = CDriver::getDriver(Yii::app()->driver->id);			
			$model->myscenario = 'change_online_status';
			$model->is_online = intval($is_online);			
			if($model->save()){
			   $this->code = 1;
			   $this->msg = "Ok";
			   $this->details = [
				  'is_online'=>$model->is_online==1?true:false
			   ];
			} else $this->msg = CommonUtility::parseError( $model->getErrors() );			
		} catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actiongetZoneList()
	{
		try {

			$model_driver = CDriver::getDriver(Yii::app()->driver->id);			
            $merchant_id = $model_driver->merchant_id;		
			$data = CDriver::zoneList($merchant_id);

			$this->code = 1; $this->msg = "Ok";
			$this->details = [
				'data'=>$data
			];
		} catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actiongetOndemanzone()
	{
		try {

			$model_driver = CDriver::getDriver(Yii::app()->driver->id);			
            $merchant_id = $model_driver->merchant_id;		
			$data = CDriver::zoneList($merchant_id);
			if(!$driver_zone = CDriver::getZoneSelected($merchant_id,Yii::app()->driver->id)){
				$driver_zone = [];
			}

			$this->code = 1; $this->msg = "Ok";
			$this->details = [
				'data'=>$data,
				'driver_zone'=>$driver_zone
			];

		} catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actionsetZone()
	{
		try {
			
			$model_driver = CDriver::getDriver(Yii::app()->driver->id);
		    $merchant_id = $model_driver->merchant_id;					
			$zone = isset($this->data['zone_id'])?$this->data['zone_id']:'';
			
			//if(is_array($zone) && count($zone)>=1){
				$criteria = new CDbCriteria();
				$criteria->condition = "merchant_id=:merchant_id AND driver_id=:driver_id AND on_demand=1";
				$criteria->params = [
					':merchant_id'=>$merchant_id,
					':driver_id'=>Yii::app()->driver->id,					
				];				
				AR_driver_sched::model()->deleteAll($criteria);			

				if(is_array($zone) && count($zone)>=1){
					foreach ($zone as $zone_id) {
						$model = new AR_driver_sched;
						$model->merchant_id = $merchant_id;
						$model->driver_id = Yii::app()->driver->id;
						$model->zone_id = $zone_id;
						$model->on_demand = 1;
						$model->save();					
					}
			    }
				$this->code = 1;
				$this->msg = t(Helper_success);
			//} else $this->msg = t("Please select zone");
		} catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

}
// end class