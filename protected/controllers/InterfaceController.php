<?php
require 'intervention/vendor/autoload.php';
require 'php-jwt/vendor/autoload.php';

use Intervention\Image\ImageManager;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

define("SOCIAL_STRATEGY", 'mobile');
require_once "php-qrcode/vendor/autoload.php";

use chillerlan\QRCode\{QRCode, QROptions};

class InterfaceController extends InterfaceCommon
{

	public function beforeAction($action)
	{
		$method = Yii::app()->getRequest()->getRequestType();
		if ($method == "POST") {
			$this->data = Yii::app()->input->xssClean(json_decode(file_get_contents('php://input'), true));
		} else if ($method == "GET") {
			$this->data = Yii::app()->input->xssClean($_GET);
		} elseif ($method == "OPTIONS") {
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
			$data = CMerchants::getBannerByRadius(0, 'admin', [
				'latitude' => Yii::app()->request->getParam('latitude'),
				'longitude' => Yii::app()->request->getParam('longitude'),
			]);
			$food_list = CMerchants::BannerItems();
			$merchant_list = CMerchants::BannerMerchant();
			$cuisine_list = CMerchants::BannerCuisine();
			$this->code = 1;
			$this->msg = "OK";
			$this->details = [
				'data' => $data,
				'food_list' => $food_list,
				'merchant_list' => $merchant_list,
				'cuisine_list' => $cuisine_list
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetLocationCountries()
	{
		try {

			$phone_default_country = isset(Yii::app()->params['settings']['mobilephone_settings_default_country']) ? Yii::app()->params['settings']['mobilephone_settings_default_country'] : 'us';
			$phone_country_list = isset(Yii::app()->params['settings']['mobilephone_settings_country']) ? Yii::app()->params['settings']['mobilephone_settings_country'] : '';
			$phone_country_list = !empty($phone_country_list) ? json_decode($phone_country_list, true) : array();

			$filter = array(
				'only_countries' => (array)$phone_country_list
			);
			$data = ClocationCountry::listing($filter);
			$default_data = ClocationCountry::get($phone_default_country);

			$this->code = 1;
			$this->msg = "OK";
			$this->details = [
				'data' => $data,
				'default_data' => $default_data
			];
		} catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actionregisterUser()
	{
		try {

			$options = OptionsTools::find(array('signup_enabled_verification', 'captcha_secret', 'signup_enabled_capcha', 'signup_type'));
			$enabled_verification = isset($options['signup_enabled_verification']) ? $options['signup_enabled_verification'] : false;
			$merchant_captcha_secret = isset($options['captcha_secret']) ? $options['captcha_secret'] : '';
			$signup_type = isset($options['signup_type']) ? $options['signup_type'] : '';
			$verification = $enabled_verification == 1 ? true : false;

			$signup_enabled_capcha = isset($options['signup_enabled_capcha']) ? $options['signup_enabled_capcha'] : false;
			$capcha = $signup_enabled_capcha == 1 ? true : false;

			$digit_code = CommonUtility::generateNumber(4, true);

			$recaptcha_response = isset($this->data['recaptcha_response']) ? $this->data['recaptcha_response'] : '';

			$prefix = isset($this->data['mobile_prefix']) ? $this->data['mobile_prefix'] : '';
			$prefix = str_replace("+", "", $prefix);
			$mobile_number = isset($this->data['mobile_number']) ? $this->data['mobile_number'] : '';
			$redirect = isset($this->data['redirect']) ? $this->data['redirect'] : '';
			$next_url = isset($this->data['next_url']) ? $this->data['next_url'] : '';
			$local_id = isset($this->data['local_id']) ? $this->data['local_id'] : '';

			$model = new AR_clientsignup;
			$model->scenario = 'register';
			$model->capcha = false;
			$model->recaptcha_response = $recaptcha_response;
			$model->captcha_secret = $merchant_captcha_secret;

			$model->first_name = isset($this->data['first_name']) ? $this->data['first_name'] : '';
			$model->last_name = isset($this->data['last_name']) ? $this->data['last_name'] : '';
			$model->email_address = isset($this->data['email_address']) ? $this->data['email_address'] : '';
			$model->contact_phone = $prefix . $mobile_number;
			$model->password = isset($this->data['password']) ? $this->data['password'] : '';
			$password = $model->password;
			$model->cpassword = isset($this->data['cpassword']) ? $this->data['cpassword'] : '';
			$model->phone_prefix = $prefix;
			$model->mobile_verification_code = "$digit_code";
			$model->merchant_id = 0;
			$model->social_strategy = SOCIAL_STRATEGY;

			if ($verification == 1 || $verification == true) {
				$model->status = 'pending';
			}

			// quick fixed
			if ($signup_type == "mobile_phone") {
				$model->scenario = 'registration_phone';
			}

			// CUSTOM FIELDS
			$field_data = [];
			$custom_fields = isset($this->data['custom_fields']) ? $this->data['custom_fields'] : '';
			$field_data = AttributesTools::getCustomFields('customer', 'key2');
			$model->custom_fields = $custom_fields;
			CommonUtility::validateCustomFields($field_data, $custom_fields);

			if ($model->save()) {
				$this->code = 1;

				if ($verification == 1 || $verification == true) {
					$this->msg = t("We sent a code to {{email_address}}.", array(
						'{{email_address}}' => CommonUtility::maskEmail($model->email_address)
					));
					$this->details = array(
						'uuid' => $model->client_uuid,
						'verify' => true
					);
				} else {
					$this->msg = t("Registration successful");
					$this->details = array(
						'verify' => false
					);

					$this->autoLogin($model->email_address, $password);
					//$this->saveDeliveryAddress($local_id,$model->client_id);

				}
			} else {
				$this->msg = CommonUtility::parseError($model->getErrors());
				if ($models = ACustomer::checkEmailExists($model->email_address)) {
					$this->code = 1;
					$this->msg = t("We found your email address in our records. Instructions have been sent to complete sign-up.");
					ACustomer::SendCompleteRegistration($models->client_uuid);
					$this->details = array(
						'uuid' => $models->client_uuid,
						'verify' => true
					);
				}
			}
		} catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	private function autoLogin($username = '', $password = '')
	{
		$login = new AR_customer_login;
		$login->username = $username;
		$login->password = $password;
		$login->merchant_id = 0;
		$login->rememberMe = 1;
		if ($login->validate() && $login->login()) {
			$this->userData();
		}
	}

	private function customerAutoLogin($username = '', $password = '')
	{
		$login = new AR_customer_autologin;
		$login->username = $username;
		$login->password = $password;
		$login->merchant_id = 0;
		$login->rememberMe = 1;
		if ($login->validate() && $login->login()) {
			$this->userData();
		}
	}

	public function actionuserLogin()
	{
		$local_id = isset($this->data['local_id']) ? $this->data['local_id'] : '';
		$_POST['AR_customer_login'] = array(
			'username' => isset($this->data['username']) ? $this->data['username'] : '',
			'password' => isset($this->data['password']) ? $this->data['password'] : '',
		);

		$options = OptionsTools::find(array('signup_enabled_capcha', 'captcha_secret'));
		$signup_enabled_capcha = isset($options['signup_enabled_capcha']) ? $options['signup_enabled_capcha'] : false;
		$merchant_captcha_secret = isset($options['captcha_secret']) ? $options['captcha_secret'] : '';
		$capcha = $signup_enabled_capcha == 1 ? true : false;
		$recaptcha_response = isset($this->data['recaptcha_response']) ? $this->data['recaptcha_response'] : '';

		$model = new AR_customer_login;
		$model->attributes = $_POST['AR_customer_login'];
		$model->capcha = false;
		$model->recaptcha_response = $recaptcha_response;
		$model->captcha_secret = $merchant_captcha_secret;
		$model->merchant_id = 0;

		if ($model->validate() && $model->login()) {
			$this->saveDeliveryAddress($local_id, Yii::app()->user->id );
			$this->userData();
			$this->code = 1;
			$this->msg = t("Login successful");
		} else {
			$this->msg = CommonUtility::parseError($model->getErrors());
		}
		$this->responseJson();
	}

	private function userData()
	{
		$user_data = array(
			'client_uuid' => Yii::app()->user->client_uuid,
			'first_name' => Yii::app()->user->first_name,
			'last_name' => Yii::app()->user->last_name,
			'email_address' => Yii::app()->user->email_address,
			'contact_number' => Yii::app()->user->contact_number,
			'phone_prefix' => Yii::app()->user->phone_prefix,
			'contact_number_noprefix' => str_replace(Yii::app()->user->phone_prefix, "", Yii::app()->user->contact_number),
			'avatar' => Yii::app()->user->avatar,
		);
		$payload = [
			'iss' => Yii::app()->request->getServerName(),
			'sub' => 0,
			'iat' => time(),
			'token' => Yii::app()->user->logintoken,
			'username' => Yii::app()->user->username,
			'hash' => Yii::app()->user->hash,
		];

		$user_data = JWT::encode($user_data, CRON_KEY, 'HS256');
		$jwt_token = JWT::encode($payload, CRON_KEY, 'HS256');

		$settings = AR_client_meta::getMeta2(['app_push_notifications'], Yii::app()->user->id);
		$app_push_notifications = $settings['app_push_notifications'] ?? true;
		$user_settings = [
			'app_push_notifications' => $app_push_notifications == 1 ? true : false,
		];

		$this->details = array(
			'user_token' => $jwt_token,
			'user_data' => $user_data,
			'user_settings' => $user_settings
		);
	}

	public function actionSocialRegister()
	{
		try {

			$digit_code = CommonUtility::generateNumber(4, true);
			$email_address = isset($this->data['email_address']) ? $this->data['email_address'] : '';
			$id = isset($this->data['id']) ? $this->data['id'] : '';
			$social_strategy = isset($this->data['social_strategy']) ? $this->data['social_strategy'] : '';
			$social_token = isset($this->data['social_token']) ? $this->data['social_token'] : '';
			$local_id = isset($this->data['place_id']) ? $this->data['place_id'] : '';

			$verification = isset(Yii::app()->params['settings']['signup_enabled_verification']) ? Yii::app()->params['settings']['signup_enabled_verification'] : 0;
			
			$model = AR_clientsignup::model()->find("email_address=:email_address AND merchant_id=:merchant_id",[
				':email_address'=>$email_address,
				':merchant_id'=>0
			]);
			if (!$model) {
				$model = new AR_clientsignup;
				$model->scenario = 'registration_social';
				$model->social_token = $social_token;
				$model->email_address = $email_address;
				$model->password = $id;
				$model->social_id = $id;
				$model->google_client_id = $id;
				$model->first_name = isset($this->data['first_name']) ? $this->data['first_name'] : '';
				$model->last_name = isset($this->data['last_name']) ? $this->data['last_name'] : '';
				$model->mobile_verification_code = $digit_code;
				$model->status = 'pending';
				$model->social_strategy = $social_strategy;
				$model->account_verified  = $verification == 1 ? 0 : 1;
				$model->merchant_id = 0;
				if ($model->save()) {
					$this->code = 1;
					$this->msg = $verification == 1 ? t("We sent a code to {{email_address}}.", ['{{email_address}' => CommonUtility::maskEmail($model->email_address)]) : t("Registration successful");
					$this->details = [
						'verify' => $verification == 1 ? true : false,
						'uuid' => $model->client_uuid,
						'is_login' => false
					];
				} else $this->msg = CommonUtility::parseError($model->getErrors());
			} else {
				$model->scenario = 'social_login';
				$model->social_strategy = $social_strategy;
				$model->social_token = $social_token;
				if ($model->status == 'pending' && $model->social_id == $id) {
					$model->mobile_verification_code = $digit_code;
					$model->google_client_id = $id;
					if ($model->save()) {
						$this->code = 1;
						$this->msg = $verification == 1 ? t("We sent a code to {{email_address}}.", ['{{email_address}' => CommonUtility::maskEmail($model->email_address)]) : t("Registration successful");
						$this->details = [
							'verify' => $verification == 1 ? true : false,
							'uuid' => $model->client_uuid,
							'is_login' => false
						];
					} else $this->msg = CommonUtility::parseError($model->getErrors());
				} elseif ($model->status == "active") {
					$this->code = 1;
					$this->msg = t("Login successful");
					$this->customerAutoLogin($model->email_address, $model->password);
					$this->details['is_login'] = true;
					$this->details['verify'] = false;
				} else $this->msg = t("Your account is {{status}}", array('{{status}}' => t($model->status)));
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

			$model = AR_clientsignup::model()->find(
				'client_uuid=:client_uuid',
				array(':client_uuid' => $client_uuid)
			);
			if ($model) {
				$digit_code = CommonUtility::generateNumber(4, true);
				$model->mobile_verification_code = $digit_code;
				$model->scenario = 'resend_otp';
				if ($model->save()) {

					$this->code = 1;
					$options = OptionsTools::find(['signup_type']);
					$signup_type = isset($options['signup_type']) ? $options['signup_type'] : '';

					if ($signup_type == "mobile_phone") {
						$this->msg = t("We sent a code to {{contact_phone}}.", array(
							'{{contact_phone}}' => CommonUtility::mask($model->contact_phone)
						));
					} else {
						$this->msg = t("We sent a code to {{email_address}}.", array(
							'{{email_address}}' => CommonUtility::maskEmail($model->email_address)
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
			$model = AR_client::model()->find(
				'client_uuid=:client_uuid',
				array(':client_uuid' => $client_uuid)
			);
			if ($model) {
				$data = [
					'status' => $model->status,
					'account_verified' => $model->account_verified,
					'social_strategy' => $model->social_strategy
				];
				$options = OptionsTools::find(['signup_enabled_verification', 'signup_resend_counter', 'signup_type']);
				$enabled_verification  = isset($options['signup_enabled_verification']) ? $options['signup_enabled_verification'] : '';
				$signup_resend_counter  = isset($options['signup_resend_counter']) ? $options['signup_resend_counter'] : 20;
				$signup_type = isset($options['signup_type']) ? $options['signup_type'] : '';
				$this->code = 1;

				if ($signup_type == "mobile_phone") {
					$this->msg = t("We sent a code to {{contact_phone}}.", array(
						'{{contact_phone}}' => CommonUtility::mask($model->contact_phone)
					));
				} else {
					$this->msg = t("We sent a code to {{email_address}}.", array(
						'{{email_address}}' => CommonUtility::maskEmail($model->email_address)
					));
				}
				$this->details = [
					'data' => $data,
					'settings' => [
						'enabled_verification' => $enabled_verification,
						'signup_resend_counter' => $signup_resend_counter <= 0 ? 20 : $signup_resend_counter
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

			$local_id = isset($this->data['local_id']) ? $this->data['local_id'] : '';
			$client_uuid = isset($this->data['client_uuid']) ? $this->data['client_uuid'] : '';
			$verification_code = isset($this->data['verification_code']) ? intval($this->data['verification_code']) : '';

			$auto_login = isset($this->data['auto_login']) ? $this->data['auto_login'] : '';

			$model = AR_clientsignup::model()->find(
				'client_uuid=:client_uuid',
				array(':client_uuid' => $client_uuid)
			);

			if ($model) {
				$model->scenario = 'complete_standard_registration';
				if ($model->mobile_verification_code == $verification_code) {
					$model->account_verified = 1;

					if ($auto_login == 1) {
						$model->status = 'active';
					}

					if ($model->save()) {
						$this->code = 1;
						$this->msg = "ok";
						$this->details = array();

						if ($auto_login == 1) {
							$this->msg = t("Login successful");

							//AUTO LOGIN
							$login = new AR_customer_autologin;
							$login->username = $model->email_address;
							$login->password = $model->password;
							$login->merchant_id = 0;
							$login->rememberMe = 1;
							if ($login->validate() && $login->login()) {
								$this->userData();
								//$this->saveDeliveryAddress($local_id,$model->client_id);
							}
						}
					} else $this->msg = CommonUtility::parseError($model->getErrors());
				} else $this->msg = t("Invalid verification code");
			} else $this->msg = t("Records not found");
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	private function saveDeliveryAddress($local_id = '', $client_id = '')
	{
		try {

			$location_details = array();
			$credentials = CMaps::config();
			if ($credentials) {
				MapSdk::$map_provider = $credentials['provider'];
				MapSdk::setKeys(array(
					'google.maps' => $credentials['key'],
					'mapbox' => $credentials['key'],
				));
				$location_details = CMaps::locationDetailsNew($local_id);
			}
			CCheckout::saveDeliveryAddress($local_id, $client_id, $location_details);
		} catch (Exception $e) {
			//
		}
	}

	public function actiongetlocationAutocomplete()
	{
		try {

			$q = Yii::app()->input->post('q');

			if (!isset(Yii::app()->params['settings']['map_provider'])) {
				$this->msg = t("No default map provider, check your settings.");
				$this->responseJson();
			}

			MapSdk::$map_provider = Yii::app()->params['settings']['map_provider'];
			MapSdk::setKeys(array(
				'google.maps' => Yii::app()->params['settings']['google_geo_api_key'],
				'mapbox' => Yii::app()->params['settings']['mapbox_access_token'],
				'yandex' => isset(Yii::app()->params['settings']['yandex_geosuggest_api']) ? Yii::app()->params['settings']['yandex_geosuggest_api'] : '',
			));

			if ($country_params = AttributesTools::getSetSpecificCountry()) {
				if (MapSdk::$map_provider != "yandex") {
					MapSdk::setMapParameters(array(
						'country' => $country_params
					));
				}
			}

			if (MapSdk::$map_provider == "yandex") {
				MapSdk::setMapParameters(array(
					'lang' => isset(Yii::app()->params['settings']['yandex_language']) ? Yii::app()->params['settings']['yandex_language'] : '',
				));
			}

			$resp = MapSdk::findPlace($q);
			$this->code = 1;
			$this->msg = "ok";
			$this->details = array(
				'data' => $resp
			);
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetLocationDetails()
	{
		try {

			$address_format_use = isset(Yii::app()->params['settings']['address_format_use']) ? (!empty(Yii::app()->params['settings']['address_format_use']) ? Yii::app()->params['settings']['address_format_use'] : '') : '';
			$address_format_use = !empty($address_format_use) ? $address_format_use : 1;

			CMaps::config();
			$place_id = Yii::app()->input->post('place_id');
			$place_description = Yii::app()->input->post('description');

			//$resp = CMaps::locationDetailsNew($place_id,'');
			$resp = CMaps::locationDetails($place_id, '', '', $place_description);

			$this->code = 1;
			$this->msg = "ok";
			$this->details = array(
				'data' => $resp,
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
			if (!$credentials) {
				$this->msg = t("No default map provider, check your settings.");
				$this->responseJson();
			}

			MapSdk::$map_provider =  $credentials['provider'];
			MapSdk::setKeys(array(
				'google.maps' => $credentials['key'],
				'mapbox' => $credentials['key']
			));

			if (MapSdk::$map_provider == "mapbox") {
				MapSdk::setMapParameters(array(
					//'types'=>"poi",
					'limit' => 1
				));
			}

			$resp = MapSdk::reverseGeocoding($lat, $lng);
			$address_details = $resp['parsed_address'] ?? '';
			$address_details['place_id'] = $resp['place_id'] ?? '';
			$address_details['latitude'] = $resp['latitude'] ?? '';
			$address_details['longitude'] = $resp['longitude'] ?? '';

			$this->code = 1;
			$this->msg = "ok";
			$this->details = array(
				'data' => $resp,
				'address_details' => $address_details
			);
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
			$this->details = array(
				'next_action' => "show_error_msg"
			);
		}
		$this->responseJson();
	}

	public function actiongetAddresses()
	{
		if (!Yii::app()->user->isGuest) {
			if ($data = CClientAddress::getAddressesList(Yii::app()->user->id)) {
				$this->code = 1;
				$this->msg = "OK";
				$this->details = array(
					'data' => $data
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
			CCheckout::saveDeliveryAddress($place_id, Yii::app()->user->id);
			$this->code = 1;
			$this->msg = "ok";
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiondeleteAddress()
	{
		$address_uuid =  Yii::app()->input->post('address_uuid');
		if (!Yii::app()->user->isGuest) {
			try {
				CClientAddress::delete(Yii::app()->user->id, $address_uuid);
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
		$lat = isset($this->data['lat']) ? $this->data['lat'] : '';
		$lng = isset($this->data['lng']) ? $this->data['lng'] : '';
		$new_lat = isset($this->data['new_lat']) ? $this->data['new_lat'] : '';
		$new_lng = isset($this->data['new_lng']) ? $this->data['new_lng'] : '';

		$distance = CMaps::getLocalDistance($unit, $lat, $lng, $new_lat, $new_lng);
		if ($distance == "NaN") {
			$this->code = 1;
			$this->msg = "OK";
		} else if ($distance < 0.2) {
			$this->code = 1;
			$this->msg = "OK";
		} else if ($distance >= 0.2) {
			$this->msg[] = t("Pin location is too far from the address");
		}
		$this->details = array(
			'distance' => $distance
		);
		$this->responseJson();
	}

	public function actionsaveClientAddress()
	{

		try {

			$data = isset($this->data['data']) ? $this->data['data'] : '';
			$location_name = isset($this->data['location_name']) ? $this->data['location_name'] : '';
			$delivery_instructions = isset($this->data['delivery_instructions']) ? $this->data['delivery_instructions'] : '';
			$delivery_options = isset($this->data['delivery_options']) ? $this->data['delivery_options'] : '';
			$address_label = isset($this->data['address_label']) ? $this->data['address_label'] : '';
			$address1  = isset($this->data['address1']) ? $this->data['address1'] : '';
			$formatted_address  = isset($this->data['formatted_address']) ? $this->data['formatted_address'] : '';
			$street_number = isset($this->data['street_number'])?$this->data['street_number']:'';			

			$address = array();
			$address = isset($data['address']) ? $data['address'] : '';

			$address_uuid = isset($data['address_uuid']) ? $data['address_uuid'] : '';
			$new_lat = isset($data['latitude']) ? $data['latitude'] : '';
			$new_lng = isset($data['longitude']) ? $data['longitude'] : '';
			$place_id = isset($data['place_id']) ? $data['place_id'] : '';

			$address2 = isset($address['address2']) ? $address['address2'] : '';
			$country = isset($address['country']) ? $address['country'] : '';
			$country_code = isset($address['country_code']) ? $address['country_code'] : '';
			$postal_code = isset($address['postal_code']) ? $address['postal_code'] : '';
			$city = isset($address['city'])?$address['city']:'';			

			if (!empty($address_uuid)) {
				$model = AR_client_address::model()->find(
					'address_uuid=:address_uuid AND client_id=:client_id',
					array(':address_uuid' => $address_uuid, 'client_id' => Yii::app()->user->id)
				);
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
			$model->street_number = $street_number;
			$model->address2 = $address2;
			$model->country = $country;
			$model->country_code = $country_code;
			$model->city = $city;
			$model->postal_code = $postal_code;

			if ($model->save()) {
				$this->code = 1;
				$this->msg = "OK";
				$this->details = array(
					'place_id' => $place_id
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
			$custom_field_enabled = isset(Yii::app()->params['settings']['custom_field_enabled']) ? Yii::app()->params['settings']['custom_field_enabled'] : false;
			$custom_field_enabled = $custom_field_enabled == 1 ? true : false;
			$this->details = array(
				'delivery_option' => CCheckout::deliveryOption(),
				'address_label' => CCheckout::addressLabel(),
				'maps_config' => CMaps::config(),
				'custom_field_enabled' => $custom_field_enabled,
				'custom_field1_data' => CommonUtility::ArrayToLabelValue(CCheckout::customeFieldList())
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
			$data = CCuisine::getList(Yii::app()->language, $q);
			$data_raw = $data;
			if ($rows > 0) {
				$total = count($data);
			}
			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(
				'data' => $data,
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

			$place_data = CMaps::locationDetails($place_id, '');
			$filters = [
				'lat' => isset($place_data['latitude']) ? $place_data['latitude'] : '',
				'lng' => isset($place_data['longitude']) ? $place_data['longitude'] : '',
				'unit' => Yii::app()->params['settings']['home_search_unit_type'],
				'today_now' => strtolower(date("l", strtotime($todays_date))),
				'time_now' => date("H:i", strtotime($todays_date)),
				'limit' => intval(Yii::app()->params->list_limit),

				'having' => "distance < a.delivery_distance_covered",
				'condition' => "a.status=:status  AND a.is_ready = :is_ready
						AND a.merchant_id IN (
							select merchant_id from {{merchant_meta}}
							where meta_name=:meta_name and meta_value=:meta_value
						)
						",
				'params' => array(
					':status' => 'active',
					':is_ready' => 2,
					':meta_name' => 'featured',
					':meta_value' => $featured
				)
			];

			$data = CMerchantListingV1::getFeed($filters);

			$data_merchant = isset($data['data']) ? $data['data'] : '';
			$rows = 2;
			$total = count($data_merchant);
			$data_merchant = CommonUtility::dataToRow($data_merchant, $total > $rows ? $rows : $total);

			$estimation = CMerchantListingV1::estimation($filters);
			$services = CMerchantListingV1::services($filters);
			try {
				$reviews = CMerchantListingV1::getReviews($data['merchant']);
			} catch (Exception $e) {
				$reviews = [];
			}

			try {
				$cuisine = CMerchantListingV1::getCuisine($data['merchant'], Yii::app()->language);
			} catch (Exception $e) {
				$cuisine = [];
			}

			try {
				$food_items = CMerchantListingV1::getMaxMinItem($data['merchant']);
			} catch (Exception $e) {
				$food_items = [];
			}

			$this->code = 1;
			$this->msg = "ok";
			$this->details = [
				'data' => $data_merchant,
				'cuisine' => $cuisine,
				'estimation' => $estimation,
				'reviews' => $reviews,
				'services' => $services,
				'items_min_max' => $food_items
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetMerchantFeed2()
	{
		$this->actiongetMerchantFeed();
	}

	public function actiongetMerchantFeedAuth()
	{
		$this->actiongetMerchantFeed();
	}

	public function actiongetMerchantFeed()
	{
		try {

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled']) ? Yii::app()->params['settings']['multicurrency_enabled'] : false;
			$multicurrency_enabled = $multicurrency_enabled == 1 ? true : false;
			$base_currency = Price_Formatter::$number_format['currency_code'];

			$list_type = isset($this->data['list_type']) ? $this->data['list_type'] : '';
			$featured_id = isset($this->data['featured_id']) ? $this->data['featured_id'] : '';
			$payload = isset($this->data['payload']) ? $this->data['payload'] : '';
			$place_id = isset($this->data['place_id']) ? $this->data['place_id'] : '';
			$page = isset($this->data['page']) ? intval($this->data['page']) : 0;
			$page_raw = isset($this->data['page']) ? intval($this->data['page']) : 0;
			$rows = isset($this->data['rows']) ? $this->data['rows'] : 0;
			$filters = isset($this->data['filters']) ? $this->data['filters'] : '';
			$filter = $filters;
			$sort_by = isset($this->data['sort_by']) ? $this->data['sort_by'] : '';
			$currency_code = isset($this->data['currency_code']) ? $this->data['currency_code'] : '';
			$coordinates = isset($this->data['coordinates']) ? $this->data['coordinates'] : null;

			$limit = $this->data['limit'] ?? intval(Yii::app()->params->list_limit);

			$cuisine = [];
			$reviews = [];
			$estimation = [];
			$services = [];
			$food_items = [];
			$total_found = 0;

			$todays_date = date("Y-m-d H:i");

			if ($page > 0) {
				$page = $page - 1;
			}

			if ($coordinates) {
				$place_data['latitude'] = $coordinates['lat'];
				$place_data['longitude'] = $coordinates['lng'];
			} else $place_data = CMaps::locationDetails($place_id, '');

			$filters = [
				'lat' => isset($place_data['latitude']) ? $place_data['latitude'] : '',
				'lng' => isset($place_data['longitude']) ? $place_data['longitude'] : '',
				'unit' => Yii::app()->params['settings']['home_search_unit_type'],
				'today_now' => strtolower(date("l", strtotime($todays_date))),
				'time_now' => date("H:i", strtotime($todays_date)),
				'date_now' => $todays_date,
				'limit' => $limit,
				'page' => intval($page),
				'filters' => $filters,
				'client_id' => !Yii::app()->user->isGuest ? Yii::app()->user->id : 0,
			];

			$and = CMerchantListingV1::preFilter($filters);

			if ($list_type == "featured") {
				if ($featured_id == "all") {
					$filters['having'] = 'distance < a.delivery_distance_covered';
					$filters['condition'] = "a.status=:status  AND a.is_ready = :is_ready $and";
					$filters['params'] = array(
						':status' => 'active',
						':is_ready' => 2
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
						':status' => 'active',
						':is_ready' => 2,
						':meta_name' => 'featured',
						':meta_value' => $featured_id
					);
				}
			} elseif ($list_type == "promo") {
				$filters['having'] = "distance < a.delivery_distance_covered";
				$filters['condition'] = "a.status=:status  AND a.is_ready = :is_ready
				AND a.merchant_id IN (
					select merchant_id from {{promos}}
					where valid_from<=:expiration and valid_to>:expiration
					and status='publish'
					and visible=1
				)
				$and";
				$filters['params'] = [
					':status' => 'active',
					':is_ready' => 2,
					':expiration' => CommonUtility::dateOnly()
				];
			} elseif ($list_type == "all") {
				$filters['having'] = "distance < a.delivery_distance_covered";
				$filters['condition'] = "a.status=:status  AND a.is_ready = :is_ready $and";
				$filters['params'] = [
					':status' => 'active',
					':is_ready' => 2
				];
			}

			$data = CMerchantListingV1::getFeed($filters, $sort_by);
			$total_found = isset($data['count']) ? intval($data['count']) : 0;
			$data_merchant = isset($data['data']) ? $data['data'] : '';

			$exchange_rate = 1;
			$currency_code = !empty($currency_code) ? $currency_code : $base_currency;
			// SET CURRENCY
			if (!empty($currency_code) && $multicurrency_enabled) {
				Price_Formatter::init($currency_code);
				if ($currency_code != $base_currency) {
					$exchange_rate = CMulticurrency::getExchangeRate($base_currency, $currency_code);
				}
			}
			CMerchantMenu::setExchangeRate($exchange_rate);
			CPromos::setExchangeRate($exchange_rate);

			$remaining = $total_found - ($page_raw * $limit);
			$is_last_page = $remaining <= 0;

			$query = isset($filter['query']) ? $filter['query'] : null;
			if (in_array('items_search', $payload) && !empty($query)) {
				try {
					$resp_food = CMerchantMenu::searchItems([$query], Yii::app()->language, 100, $currency_code, $multicurrency_enabled);
					$food_items = $resp_food['data'];
					foreach ($food_items as $food_item) {
						$merchantId = $food_item['merchant_id'];
						if (isset($data_merchant[$merchantId])) {
							$data_merchant[$merchantId]['items_list'][] = $food_item;
						}
					}
				} catch (Exception $e) {
				}
			}

			if (in_array('cuisine', $payload)) {
				if ($cuisine_list = CMerchantListingV1::getCuisineNew($data['merchant'], Yii::app()->language)) {
					foreach ($cuisine_list as $cuisine_item) {
						$merchantId = $cuisine_item['merchant_id'];
						if (isset($data_merchant[$merchantId])) {
							$data_merchant[$merchantId]['cuisine'][] = CommonUtility::safeDecode($cuisine_item['cuisine_name']);
						}
					}
				}
			}

			$promos = [];
			if (in_array('promo', $payload)) {
				try {
					$promo_list = CPromos::getAvaialblePromoNew($data['merchant'], CommonUtility::dateOnly());
					foreach ($promo_list as $merchantId => $promo_item) {
						if (isset($data_merchant[$merchantId])) {
							$data_merchant[$merchantId]['promo_list'] = $promo_item;
						}
					}
				} catch (Exception $e) {
				}
			}

			if (in_array('reviews', $payload)) {
				try {
					$reviews_list = CMerchantListingV1::getReviews($data['merchant']);
					foreach ($reviews_list as $merchantId => $reviews_items) {
						if (isset($data_merchant[$merchantId])) {
							$data_merchant[$merchantId]['reviews'] = $reviews_items;
						}
					}
				} catch (Exception $e) {
				}
			}

			if (in_array('services', $payload)) {
				$services_list = CMerchantListingV1::services($filters);
				foreach ($services_list as $merchantId => $services_items) {
					if (isset($data_merchant[$merchantId])) {
						$data_merchant[$merchantId]['services'] = $services_items;
					}
				}
			}

			if (in_array('estimation', $payload)) {
				$estimation_list = CMerchantListingV1::estimationNew($filters, true);
				if(is_array($estimation_list) && count($estimation_list)>=1){
					foreach ($estimation_list as $merchantId => $services_items) {
						if (isset($services_items['delivery'])) {
							if (isset($data_merchant[$merchantId])) {
								$charge_type = $data_merchant[$merchantId]['charge_type'];
								if (isset($services_items['delivery'][$charge_type])) {
									$data_merchant[$merchantId]['estimation'] = isset($services_items['delivery'][$charge_type]['estimation']) ? $services_items['delivery'][$charge_type]['estimation'] : '';
								}
							}
						}
					}
			    }
			}

			$data_merchant = array_values($data_merchant);

			$this->code = 1;
			$this->msg = "ok";
			$this->details = [
				'is_last_page' => $is_last_page,
				'data' => $data_merchant,
				'total_found' => $total_found,
				'total_pretty' => t("{total} Restaurants found", [
					'{total}' => $total_found
				]),
				'cuisine' => $cuisine,
				'reviews' => $reviews,
				'estimation' => $estimation,
				'services' => $services,
				'items_min_max' => $food_items,
				'promos' => $promos,

			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
			$this->details = [
				'total_found' => $total_found
			];
		}
		$this->responseJson();
	}

	public function actionsearchAttributes()
	{

		$currency_code = Yii::app()->input->get('currency_code');
		$base_currency = Price_Formatter::$number_format['currency_code'];

		$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled']) ? Yii::app()->params['settings']['multicurrency_enabled'] : false;
		$multicurrency_enabled = $multicurrency_enabled == 1 ? true : false;

		$currency_code = !empty($currency_code) ? $currency_code : $base_currency;
		if (!empty($currency_code) && $multicurrency_enabled) {
			Price_Formatter::init($currency_code);
		}

		$data = array(
			'price_range' => AttributesTools::SortPrinceRangeWithLabel(),
			'sort_by' => AttributesTools::SortMerchant2(),
			'max_delivery_fee' => AttributesTools::MaxDeliveryFee(),
			'sort_list' => AttributesTools::SortList(),
			'offers_filters' => AttributesTools::OffersFilters(),
			'quick_filters' => AttributesTools::QuickFilters(),
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
			$data = array('all' => t("All Merchants")) + $data;			
			$this->code = 1;
			$this->msg = "OK";
			$this->details = [
				'data' => $data
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetDeliveryDetails()
	{
		try {

			$merchant_id = '';
			$data = array();
			$cart_uuid = Yii::app()->input->post('cart_uuid');
			$slug = Yii::app()->input->post('slug');
			$transactionType = Yii::app()->input->post('transaction_type');
			$query_type = Yii::app()->input->post('query_type');
			$query_type = !empty($query_type) ? $query_type : 'cart';
			$delivery_option = [];
			$time_selection = 1;

			$transaction_type = !empty($transactionType) ? $transactionType : CServices::getSetService($cart_uuid);

			try {
				if ($query_type == "cart") {
					$merchant_id = CCart::getMerchantId($cart_uuid);
				} else {
					$merchant = CMerchantListingV1::getMerchantBySlug($slug);
					$merchant_id = $merchant->merchant_id;
				}

				$merchant_options = OptionsTools::find(['merchant_time_selection'], $merchant_id);
				$time_selection = isset($merchant_options['merchant_time_selection']) ? $merchant_options['merchant_time_selection'] : 1;

				$data = CCheckout::getMerchantTransactionList($merchant_id, Yii::app()->language);
				$delivery_option = CCheckout::deliveryOptionList('', $time_selection);
			} catch (Exception $e) {
				$delivery_option = CCheckout::deliveryOptionList();
				$data = CServices::Listing(Yii::app()->language);
			}

			if (!array_key_exists($transaction_type, (array)$data)) {
				$transaction_type = CServices::getSetService($cart_uuid);
			}

			CCart::savedAttributes($cart_uuid, 'transaction_type', $transaction_type);

			$delivery_date = date("Y-m-d");
			$time_now = date("H:i");
			$attrs_name = ['whento_deliver', 'delivery_date', 'delivery_time', 'merchant_slug'];

			$delivery_date = '';
			$delivery_time = '';
			$merchant_slug = '';
			if ($atts = CCart::getAttributesAll($cart_uuid, $attrs_name)) {
				$delivery_date = isset($atts['delivery_date']) ? $atts['delivery_date'] : $delivery_date;
				$delivery_time = isset($atts['delivery_time']) ? json_decode($atts['delivery_time'], true) : '';
				$time_now = isset($atts['delivery_time']) ? CCheckout::jsonTimeToSingleTime($atts['delivery_time']) : $time_now;
				$merchant_slug = isset($atts['merchant_slug']) ? $atts['merchant_slug'] : null;
			}

			$whento_deliver = CCheckout::getWhenDeliver($cart_uuid);

			try {
				$datetime_to = date("Y-m-d g:i:s a", strtotime("$delivery_date $time_now"));
				CMerchantListingV1::checkCurrentTime(date("Y-m-d g:i:s a"), $datetime_to);
				$time_already_passed = false;
			} catch (Exception $e) {
				$whento_deliver = "now";
				CCart::savedAttributes($cart_uuid, 'whento_deliver', $whento_deliver);
				CCart::savedAttributes($cart_uuid, 'delivery_date', $delivery_date);
				$time_already_passed = true;
				$delivery_date = '';
				$delivery_time = '';
			}


			if ($whento_deliver == "now" && $time_selection == 2) {
				$whento_deliver = "schedule";
			} else if ($whento_deliver == "schedule" && $time_selection == 3) {
				$whento_deliver = "now";
			}

			if (!empty($slug) && !empty($merchant_slug) && $whento_deliver == "schedule") {
				if ($slug != $merchant_slug && array_key_exists('now', (array)$delivery_option)) {
					$whento_deliver = "now";
					CCart::savedAttributes($cart_uuid, 'whento_deliver', $whento_deliver);
				}
			}

			$whento_deliver_pretty = $whento_deliver == 'now' ? t("Now") : t("Schedule");
			if ($whento_deliver == 'now') {
				$delivery_time = '';
				CCart::deleteAttributesAll($cart_uuid, $attrs_name);
			}

			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(
				'transaction_type' => $transaction_type,
				'data' => $data,
				'delivery_option' => $delivery_option,
				'delivery_date' => $delivery_date,
				'delivery_date_pretty' => !empty($delivery_date) ? Date_Formatter::date($delivery_date) : '',
				'delivery_time' => $delivery_time,
				'whento_deliver' => $whento_deliver,
				'whento_deliver_pretty' => $whento_deliver_pretty,
				'time_already_passed' => $time_already_passed
			);
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetDeliveryTimes()
	{
		try {

			$cart_uuid = Yii::app()->request->getPost('cart_uuid', '');
			$merchant_slug = Yii::app()->request->getPost('slug', '');

			if (!empty($merchant_slug)) {
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

			$model = AR_opening_hours::model()->find("merchant_id=:merchant_id", array(
				':merchant_id' => $merchant_id
			));
			if (!$model) {
				$this->msg[] = t("Merchant has not set time opening yet");
				$this->responseJson();
			}

			$options = OptionsTools::find(array('website_time_picker_interval'));
			$interval = isset($options['website_time_picker_interval']) ? $options['website_time_picker_interval'] . " mins" : '20 mins';

			// CHECK IF MERCHANT HAS DIFFERENT TIMEZONE
			$options_merchant = OptionsTools::find(['merchant_time_picker_interval', 'merchant_timezone'], $merchant_id);
			$interval_merchant = isset($options_merchant['merchant_time_picker_interval']) ? (!empty($options_merchant['merchant_time_picker_interval']) ? $options_merchant['merchant_time_picker_interval'] . " mins" : '') : '';
			$interval = !empty($interval_merchant) ? $interval_merchant : $interval;
			$merchant_timezone = isset($options_merchant['merchant_timezone']) ? $options_merchant['merchant_timezone'] : '';
			if (!empty($merchant_timezone)) {
				Yii::app()->timezone = $merchant_timezone;
			}

			$opening_hours = CMerchantListingV1::openHours($merchant_id, $interval);
			$delivery_date = '';
			$delivery_time = '';

			if ($atts = CCart::getAttributesAll($cart_uuid, array('delivery_date', 'delivery_time'))) {
				$delivery_date = isset($atts['delivery_date']) ? $atts['delivery_date'] : '';
				$delivery_time = isset($atts['delivery_time']) ? $atts['delivery_time'] : '';
			}

			$this->code = 1;
			$this->msg = "ok";
			$this->details = array(
				'delivery_option' => $delivery_option,
				'whento_deliver' => $whento_deliver,
				'delivery_date' => $delivery_date,
				'delivery_time' => $delivery_time,
				'opening_hours' => $opening_hours,
			);
		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetDeliveryDateTime()
	{
		try {

			$merchant_id = Yii::app()->request->getPost('merchant_id', null);

			$model = AR_opening_hours::model()->find("merchant_id=:merchant_id", array(
				':merchant_id' => $merchant_id
			));
			if (!$model) {
				$this->msg = t("Merchant has not set time opening yet");
				$this->responseJson();
			}

			$options = OptionsTools::find(array('website_time_picker_interval','website_date_range'));
			$interval = isset($options['website_time_picker_interval']) ? $options['website_time_picker_interval'] . " mins" : '20 mins';
			$date_range = isset($options['website_date_range'])?$options['website_date_range']:7;
			$date_range = $date_range>0?$date_range:7;

			// CHECK IF MERCHANT HAS DIFFERENT TIMEZONE
			$options_merchant = OptionsTools::find(['merchant_time_picker_interval', 'merchant_timezone'], $merchant_id);
			$interval_merchant = isset($options_merchant['merchant_time_picker_interval']) ? (!empty($options_merchant['merchant_time_picker_interval']) ? $options_merchant['merchant_time_picker_interval'] . " mins" : '') : '';
			$interval = !empty($interval_merchant) ? $interval_merchant : $interval;
			$merchant_timezone = isset($options_merchant['merchant_timezone']) ? $options_merchant['merchant_timezone'] : '';
			if (!empty($merchant_timezone)) {
				Yii::app()->timezone = $merchant_timezone;
			}

			$opening_hours = CMerchantListingV1::openHours2($merchant_id, $interval,$date_range);
			if (!$opening_hours) {
				$this->msg = t("Merchant has not set time opening yet");
				$this->responseJson();
			}
			$this->code = 1;
			$this->msg = "ok";
			$this->details = [
				'opening_hours' => $opening_hours
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionsaveTransactionInfo()
	{
		try {

			$cart_uuid = isset($this->data['cart_uuid']) ? $this->data['cart_uuid'] : '';
			if (empty($cart_uuid)) {
				$cart_uuid = CommonUtility::createUUID("{{cart}}", 'cart_uuid');
			}

			$transaction_type = isset($this->data['transaction_type']) ? $this->data['transaction_type'] : '';
			$whento_deliver = isset($this->data['whento_deliver']) ? $this->data['whento_deliver'] : '';
			$delivery_date = isset($this->data['delivery_date']) ? $this->data['delivery_date'] : '';
			$delivery_time = isset($this->data['delivery_time']) ? $this->data['delivery_time'] : '';
			$slug = isset($this->data['slug']) ? $this->data['slug'] : '';

			if ($whento_deliver == "schedule") {
				if (empty($delivery_date)) {
					$this->msg = t("Delivery date is required");
					$this->responseJson();
				}
				if (empty($delivery_time)) {
					$this->msg = t("Delivery time is required");
					$this->responseJson();
				}
			}


			$delivery_time = [
				'start_time' => $delivery_time['start_time'] ?? '',
				'end_time' => $delivery_time['end_time'] ?? '',
				'pretty_time' => $delivery_time['pretty_time'] ?? '',
			];

			CCart::savedAttributes($cart_uuid, 'whento_deliver', $whento_deliver);
			CCart::savedAttributes($cart_uuid, 'delivery_date', $delivery_date);
			CCart::savedAttributes($cart_uuid, 'delivery_time', json_encode($delivery_time));
			CCart::savedAttributes($cart_uuid, 'transaction_type', $transaction_type);
			CCart::savedAttributes($cart_uuid, 'merchant_slug', $slug);

			$delivery_datetime = CCheckout::jsonTimeToFormat($delivery_date, json_encode($delivery_time));

			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(
				'whento_deliver' => $whento_deliver,
				'delivery_date' => $delivery_date,
				'delivery_time' => $delivery_time,
				'delivery_datetime' => $delivery_datetime,
				'cart_uuid' => $cart_uuid
			);
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionTransactionInfo()
	{

		try {

			$whento_deliver = '';
			$delivery_datetime = '';
			$cart_uuid = isset($this->data['cart_uuid']) ? $this->data['cart_uuid'] : '';
			$local_id = isset($this->data['local_id']) ? $this->data['local_id'] : '';
			$local_info = CMaps::locationDetails($local_id, '');

			$delivery_option = CCheckout::deliveryOptionList();

			$data = isset($this->data['choosen_delivery']) ? $this->data['choosen_delivery'] : '';

			$transaction_type = '';
			$services_list = CServices::Listing(Yii::app()->language);

			if (is_array($data) && count($data) >= 1) {
				$whento_deliver = isset($data['whento_deliver']) ? $data['whento_deliver'] : 'now';
				$delivery_date = isset($data['delivery_date']) ? $data['delivery_date'] : date("Y-m-d");
				$delivery_time = isset($data['delivery_time']) ? $data['delivery_time'] : '';
				$transaction_type = isset($data['transaction_type']) ? $data['transaction_type'] : '';
				$delivery_datetime = CCheckout::jsonTimeToFormat($delivery_date, json_encode($delivery_time));
			} else {
				$whento_deliver = CCheckout::getWhenDeliver($cart_uuid);
				$delivery_datetime = CCheckout::getScheduleDateTime($cart_uuid, $whento_deliver);
				$transaction_type = CServices::getSetService($cart_uuid);
			}

			$merchant_adddress = [];
			try {
				$merchant_id = CCart::getMerchantId($cart_uuid);
				if ($merchant = CMerchants::get($merchant_id)) {
					$merchant_adddress = [
						'address' => $merchant->address,
						'latitude' => $merchant->latitude,
						'lontitude' => $merchant->lontitude,
					];
				}
			} catch (Exception $e) {
				//
			}

			$this->code = 1;
			$this->msg = "ok";
			$this->details = array(
				'address1' => $local_info['address']['address1'],
			  'street_number'=>$local_info['address']['street_number'],
				'formatted_address' => $local_info['address']['formatted_address'],
				'latitude' => $local_info['latitude'],
				'longitude' => $local_info['longitude'],
				'delivery_option' => $delivery_option,
				'whento_deliver' => $whento_deliver,
				'delivery_datetime' => $delivery_datetime,
				'transaction_type' => $transaction_type,
				'services_list' => $services_list,
				'merchant_adddress' => $merchant_adddress
			);
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionsaveTransactionType()
	{
		try {

			$cart_uuid = isset($this->data['cart_uuid']) ? $this->data['cart_uuid'] : '';
			$transaction_type = isset($this->data['transaction_type']) ? $this->data['transaction_type'] : '';

			if (empty($cart_uuid)) {
				$cart_uuid = CommonUtility::createUUID("{{cart}}", 'cart_uuid');
			}

			CCart::savedAttributes($cart_uuid, 'transaction_type', $transaction_type);
			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(
				'cart_uuid' => $cart_uuid,
				'transaction_type' => $transaction_type
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

	public function actiongetMerchantInfoAuth()
	{
		$this->actiongetMerchantInfo();
	}

	public function actiongetMerchantInfo()
	{
		try {
			$setttings = Yii::app()->params['settings'];
			$search_mode = $setttings['home_search_mode'] ?? 'address';
			$search_type = isset($setttings['location_searchtype']) ? $setttings['location_searchtype'] : '';

			$slug = Yii::app()->request->getQuery('slug', null);
			$place_id =  Yii::app()->request->getQuery('place_id', null);
			$cart_uuid =  Yii::app()->request->getQuery('cart_uuid', null);
			$currency_code = Yii::app()->input->get('currency_code');
			$base_currency = Price_Formatter::$number_format['currency_code'];

			$latitude =  Yii::app()->request->getQuery('latitude', null);
			$longitude =  Yii::app()->request->getQuery('longitude', null);

			$state_id =  Yii::app()->request->getQuery('state_id', null);
			$city_id =  Yii::app()->request->getQuery('city_id', null);
			$area_id =  Yii::app()->request->getQuery('area_id', null);
			$postal_id =  Yii::app()->request->getQuery('postal_id', null);

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled']) ? Yii::app()->params['settings']['multicurrency_enabled'] : false;
			$multicurrency_enabled = $multicurrency_enabled == 1 ? true : false;

			$data = CMerchantListingV1::getMerchantInfo($slug, Yii::app()->language);
			$merchant_id = intval($data['merchant_id']);
			$opening_hours = CMerchantListingV1::openingHours($merchant_id);

			// CHECK IF MERCHANT HAS DIFFERENT TIMEZONE
			$options_merchant = OptionsTools::find(
				[
					'merchant_timezone',
					'merchant_default_currency',
					'booking_enabled',
					'booking_reservation_custom_message',
					'booking_allowed_choose_table',
					'booking_enabled_capcha',
					'merchant_enabled_age_verification',
					'menu_display_type'
				],
				$merchant_id
			);

			$menu_display_type = $options_merchant['menu_display_type'] ?? 'all';
			$menu_display_type = !empty($menu_display_type) ? $menu_display_type : 'all';
			$merchant_timezone = isset($options_merchant['merchant_timezone']) ? $options_merchant['merchant_timezone'] : '';
			$booking_enabled = isset($options_merchant['booking_enabled']) ? $options_merchant['booking_enabled'] : false;
			$booking_enabled = $booking_enabled == 1 ? true : false;

			$captcha_enabled = isset($options_merchant['booking_enabled_capcha']) ? $options_merchant['booking_enabled_capcha'] : false;
			$captcha_enabled = $captcha_enabled == 1 ? true : false;
			$booking_reservation_custom_message = isset($options_merchant['booking_reservation_custom_message']) ? $options_merchant['booking_reservation_custom_message'] : '';
			$booking_reservation_terms = isset($options_merchant['booking_reservation_terms']) ? $options_merchant['booking_reservation_terms'] : '';
			$allowed_choose_table = isset($options_merchant['booking_allowed_choose_table']) ? $options_merchant['booking_allowed_choose_table'] : false;
			$allowed_choose_table = $allowed_choose_table == 1 ? true : false;

			$enabled_age_verification = isset($options_merchant['merchant_enabled_age_verification']) ? $options_merchant['merchant_enabled_age_verification'] : false;
			$enabled_age_verification = $enabled_age_verification == 1 ? true : false;

			$merchant_default_currency = isset($options_merchant['merchant_default_currency']) ? $options_merchant['merchant_default_currency'] : '';
			$merchant_default_currency = !empty($merchant_default_currency) ? $merchant_default_currency : $base_currency;
			if (!empty($merchant_timezone)) {
				Yii::app()->timezone = $merchant_timezone;
			}

			$currency_code = !empty($currency_code) ? $currency_code : (empty($merchant_default_currency) ? $base_currency : $merchant_default_currency);

			$exchange_rate = 1;
			if (!empty($currency_code) && $multicurrency_enabled) {
				Price_Formatter::init($currency_code);
				if ($currency_code != $merchant_default_currency) {
					$exchange_rate = CMulticurrency::getExchangeRate($merchant_default_currency, $currency_code);
				}
			}

			$this->code = 1;
			$this->msg = "ok";

			$today = strtolower(date("l"));
			$open_start = '';
			$open_end = '';
			$open_at = '';

			if (is_array($opening_hours) && count($opening_hours) >= 1) {
				foreach ($opening_hours as $items) {
					if ($items['day'] == $today) {
						$open_start = Date_Formatter::Time($items['start_time']);
						$open_end = Date_Formatter::Time($items['end_time']);
					}
				}
				$open_at = !empty($open_start) ? t("Open {open} - {end}", ['{open}' => $open_start, '{end}' => $open_end]) : '';
			}

			$data['ratings'] = number_format((float)($data['ratings'] ?? 0), 1, '.', '');
			$data['saved_store'] = false;

			if (!Yii::app()->user->isGuest) {
				try {
					CSavedStore::getStoreReview($merchant_id, Yii::app()->user->id);
					$data['saved_store'] = true;
				} catch (Exception $e) {
					//
				}
			}

			$config = array();
			$format = Price_Formatter::$number_format;
			$config = [
				'precision' => $format['decimals'],
				'minimumFractionDigits' => $format['decimals'],
				'decimal' => $format['decimal_separator'],
				'thousands' => $format['thousand_separator'],
				'separator' => $format['thousand_separator'],
				'prefix' => $format['position'] == 'left' ? $format['currency_symbol'] : '',
				'suffix' => $format['position'] == 'right' ? $format['currency_symbol'] : '',
				'prefill' => true
			];

			$maps_config = CMaps::config();
			$maps_config_raw = $maps_config;
		$maps_config = JWT::encode($maps_config , CRON_KEY, 'HS256');   

			$share = [
				'title' => $data['restaurant_name'],
				'text' => t("Check out the {restaurant_name} delivery order with {website_title}", [
					'{restaurant_name}' => $data['restaurant_name'],
					'{website_title}' => Yii::app()->params['settings']['website_title']
				]),
				'url' => Yii::app()->createAbsoluteUrl($data['restaurant_slug']),
				'dialogTitle' => t("Share")
			];
			$data['share'] = $share;


			$charge_type = OptionsTools::find(array('merchant_delivery_charges_type'), $merchant_id);
			$charge_type = isset($charge_type['merchant_delivery_charges_type']) ? $charge_type['merchant_delivery_charges_type'] : '';

			$servicesList = [];
			$transaction_type = '';
			if ($cart_uuid) {
				$cartAttributes = CCart::getAttributesAll($cart_uuid, ['transaction_type']);
				$transaction_type = $cartAttributes['transaction_type'] ?? 'delivery';
			} else {
				try {
					$servicesList = CCheckout::getMerchantTransactionList($merchant_id, Yii::app()->language, 'services', false);
					$transaction_type = !empty($servicesList) ? array_key_first($servicesList) : 'delivery';
				} catch (Exception $e) {
				}
			}

			// GET DISTANCE
			$distance = 0;
			$place_data = [];
			$unit = $data['distance_unit'];
			$estimation = [];
			$estimation_time = '';

			if ($search_mode == 'address') {
				if(!$latitude && !$longitude){
					try {
						$place_data = CMaps::locationDetails($place_id, '');
						$latitude = $place_data['latitude'] ?? '';
						$longitude = $place_data['longitude'] ?? '';
					} catch (Exception $e) {}
				}
				$merchant_lat = $data['latitude'] ?? '';
				$merchant_lng = $data['lontitude'] ?? '';
				$distance = CMaps::getLocalDistance($unit, $latitude, $longitude, $merchant_lat, $merchant_lng);
				$filter = [
					'merchant_id'=>$merchant_id,
					'shipping_type'=>'standard',
					'charges_type'=>$charge_type,
					'distance'=>$distance,
					'transaction_type'=>$transaction_type
				];
				if($resp_estimation = CMerchantListingV1::estimationMerchantNew($filter)){
					$estimation_time = t("{estimation} mins",[
						'{estimation}'=>$resp_estimation['estimation'] ?? ''
					]);
				}
			} else if ($search_mode == 'location') {
				try {
					$estimation_resp = Clocations::LocationEstimation($merchant_id, [
						'search_type' => $search_type,
						'city_id' => intval($city_id),
						'area_id' => intval($area_id),
						'state_id' => intval($state_id),
						'postal_id' => $postal_id
					]);
					$estimation_time = $estimation_resp[$transaction_type] ?? '';
				} catch (Exception $e) {
				}
			}


			// GET GALLERY
			$gallery = CMerchantListingV1::getGallery($merchant_id);

			// DIRECTIONS
			$map_direction = CMerchantListingV1::mapDirection(
				$maps_config_raw,
				$data['latitude'],
				$data['lontitude']
			);
			$data['map_direction'] = $map_direction;

			$static_maps = CMerchantListingV1::staticMapLocation(
				[
					'api_keys'=>$maps_config['key'] ?? '',
					'map_provider'=>$maps_config['map_provider'] ?? '',
				],
				$data['latitude'],$data['lontitude'],
				'500x200',websiteDomain().Yii::app()->theme->baseUrl."/assets/images/marker2@2x.png"
			);
			$data['static_maps'] = $static_maps;

			// MERCHANT PROMO
			try {
				$promo_list = null;
				CPromos::setExchangeRate($exchange_rate);
				$promo_list = CPromos::getAvaialblePromoNew([$merchant_id], CommonUtility::dateOnly(), false, true);
			} catch (Exception $e) {}


			$review_details = CMerchantListingV1::ReviewDetails($merchant_id);

			try {
				$partial_review = CReviews::reviews($merchant_id,0,5);
			} catch (Exception $e) {}

			$this->details = [
				'data' => $data,
				'open_at' => $open_at,
				'opening_hours' => $opening_hours,
				'gallery' => $gallery,
				'config' => $config,
		  'maps_config'=>$maps_config,
				'charge_type' => $charge_type,
				'estimation' => $estimation,
				'estimation_time' => $estimation_time,
				'distance' => [
					'value' => $distance,
					'label' => t("{{distance} {{unit}} away", [
						'{{distance}' => $distance,
						'{{unit}}' => MapSdk::prettyUnit($unit)
					])
				],
				'booking_settings' => [
					'booking_enabled' => $booking_enabled,
					'booking_reservation_custom_message' => $booking_reservation_custom_message,
					'booking_reservation_terms' => $booking_reservation_terms,
					'allowed_choose_table' => $allowed_choose_table,
					'captcha_enabled' => $captcha_enabled
				],
				'enabled_age_verification' => $enabled_age_verification,
				'promo_list' => $promo_list,
				'review_details'=>$review_details,
				'menu_display_type'=>$menu_display_type,
				'partial_review'=>$partial_review ?? []
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionservicesList()
	{
		try {

			$slug = $this->data['slug'] ?? '';
			$model = CMerchantListingV1::getMerchantBySlug($slug);

			$merchant_id = $model->merchant_id;
			$cart_uuid = isset($this->data['cart_uuid']) ? $this->data['cart_uuid'] : '';
			$place_id = isset($this->data['place_id']) ? $this->data['place_id'] : '';

			$merchant = CMerchants::get($merchant_id);
			$data = CCheckout::getMerchantTransactionList($merchant_id, Yii::app()->language);
			$transaction = CCart::cartTransaction($cart_uuid, Yii::app()->params->local_transtype, $merchant_id);

			$local_info = CMaps::locationDetails($place_id, '');

			$filter = array(
				'merchant_id' => $merchant_id,
				'lat' => isset($local_info['latitude']) ? $local_info['latitude'] : '',
				'lng' => isset($local_info['longitude']) ? $local_info['longitude'] : '',
				'unit' => !empty($merchant->distance_unit) ? $merchant->distance_unit : Yii::app()->params['settings']['home_search_unit_type'],
				'shipping_type' => "standard"
			);

			$estimation  = CMerchantListingV1::estimationMerchant($filter);
			$charge_type = OptionsTools::find(array('merchant_delivery_charges_type'), $merchant_id);
			$charge_type = isset($charge_type['merchant_delivery_charges_type']) ? $charge_type['merchant_delivery_charges_type'] : '';

			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(
				'data' => $data,
				'transaction' => $transaction,
				'charge_type' => $charge_type,
				'estimation' => $estimation,
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
			$page = Yii::app()->request->getQuery('page',1);
			$merchant_id = Yii::app()->request->getQuery('merchant_id', '');
			$page_raw = intval($page);
			if ($page > 0) {
				$page = $page - 1;
			}
			$merchant = CMerchantListingV1::getMerchantBySlug($merchant_slug);			
			$merchant_id = $merchant->merchant_id;

			$criteria = new CDbCriteria();
			$criteria->alias = "a";
			$criteria->select = "
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
			$criteria->join = 'LEFT JOIN {{client}} b on a.client_id = b.client_id ';
			$criteria->condition = "a.merchant_id=:merchant_id AND a.status =:status AND parent_id = 0";
			$criteria->params = [
				':merchant_id' => $merchant_id,
				':status' => 'publish'
			];
			$criteria->order = "a.id DESC";

			$count = AR_review::model()->count($criteria);
			$pages = new CPagination($count);
			$pages->pageSize = $limit;
			$pages->setCurrentPage($page);
			$pages->applyLimit($criteria);
			$page_count = $pages->getPageCount();

			if ($page > 0) {
				if ($page_raw > $page_count) {
					$this->code = 3;
					$this->msg = t("end of results");
					$this->responseJson();
				}
			}

			$dependency = CCacheData::dependency();
			if ($model = AR_review::model()->cache(Yii::app()->params->cache, $dependency)->findAll($criteria)) {
				$data = array();
				foreach ($model as $items) {

					$meta = !empty($items->meta) ? explode(",", $items->meta) : '';
					$media = !empty($items->media) ? explode(",", $items->media) : '';

					$meta_data = array();
					$media_data = array();

					if (is_array($media) && count($media) >= 1) {
						foreach ($media as $media_val) {
							$_media = explode(";", $media_val);
							$media_data[$_media['0']] = array(
								'filename' => $_media[1],
								'path' => $_media[2],
							);
						}
					}

					if (is_array($meta) && count($meta) >= 1) {
						foreach ($meta as $meta_value) {
							$_meta = explode(";", $meta_value);
							if ($_meta[0] == "upload_images") {
								if (isset($media_data[$_meta[1]])) {
									$meta_data[$_meta[0]][] = CMedia::getImage(
										$media_data[$_meta[1]]['filename'],
										$media_data[$_meta[1]]['path']
									);
								}
							} else {
								if (isset($meta_data[$_meta[0]])) {
									$meta_data[$_meta[0]][] = isset($_meta[1]) ? $_meta[1] : '';
								}
							}
						}
					}

					$data[] = array(
						'review' => Yii::app()->input->xssClean($items->review),
						'rating' => intval($items->rating),
						'fullname' => $items->as_anonymous==1 ? CommonUtility::mask($items->customer_fullname) : Yii::app()->input->xssClean($items->customer_fullname),
						'url_image' => CMedia::getImage(
							$items->logo,
							$items->path,
							Yii::app()->params->size_image,
							CommonUtility::getPlaceholderPhoto('customer')
						),
						'as_anonymous' => $items->as_anonymous==1?true:false,
						'meta' => $meta_data,
						'date_created' => Date_Formatter::dateTime($items->date_created)
					);
				}

				$remaining = $count - ($page_raw * $limit);
			    $is_last_page = $remaining <= 0;

				$this->code = 1;
				$this->msg = "ok";
				$this->details = [
					'is_last_page'=>$is_last_page,
					'page_raw' => $page_raw,
					'page_count' => $page_count,
					'data' => $data
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
			$client_uuid = Yii::app()->input->post('client_uuid');
			$base_currency = Price_Formatter::$number_format['currency_code'];

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled']) ? Yii::app()->params['settings']['multicurrency_enabled'] : false;
			$multicurrency_enabled = $multicurrency_enabled == 1 ? true : false;

			$exchange_rate = 1;

			$model = CMerchantListingV1::getMerchantBySlug($slug);
			$merchant_id = $model->merchant_id;

			// CHECK IF MERCHANT HAS DIFFERENT TIMEZONE
			$options_merchant = OptionsTools::find(['merchant_timezone', 'merchant_default_currency'], $merchant_id);
			$merchant_timezone = isset($options_merchant['merchant_timezone']) ? $options_merchant['merchant_timezone'] : '';
			$merchant_default_currency = isset($options_merchant['merchant_default_currency']) ? $options_merchant['merchant_default_currency'] : '';
			$merchant_default_currency = !empty($merchant_default_currency) ? $merchant_default_currency : $base_currency;
			if (!empty($merchant_timezone)) {
				Yii::app()->timezone = $merchant_timezone;
			}

			$currency_code = !empty($currency_code) ? $currency_code : (empty($merchant_default_currency) ? $base_currency : $merchant_default_currency);

			// SET CURRENCY
			if (!empty($currency_code) && $multicurrency_enabled) {
				Price_Formatter::init($currency_code);
				if ($currency_code != $merchant_default_currency) {
					$exchange_rate = CMulticurrency::getExchangeRate($merchant_default_currency, $currency_code);
				}
			}

			CMerchantMenu::setExchangeRate($exchange_rate);

			$category = CMerchantMenu::getCategory($merchant_id, Yii::app()->language);
			$items = CMerchantMenu::getMenu($merchant_id, Yii::app()->language);			
			$promoItems = CMerchantMenu::getPromoItems($merchant_id);
			$items_not_available = CMerchantMenu::getItemAvailability($merchant_id, date("w"), date("H:h:i"));
			$category_not_available = CMerchantMenu::getCategoryAvailability($merchant_id, date("w"), date("H:h:i"));
			$dish = CMerchantMenu::getDish(Yii::app()->language);

			if(is_array($promoItems) && count($promoItems)>=1){
				foreach ($items as &$itemss) {
				    $itemss['promo_data'] = $promoItems[$itemss['item_id']] ?? [];
				}
		    }

			$favorites = [];
			if(!empty($client_uuid)){
				try {
					$customer = ACustomer::getUUID($client_uuid);
					$favorites = CSavedStore::getSaveItemsByCustomer($customer->client_id,$merchant_id);
					$favorites = $favorites['item_ids'];
				} catch (Exception $e) {}
			}

			foreach ($items as &$itemss) {
				$itemss['available']=true;
				$itemss['is_favorite'] = false;
				if(in_array($itemss['item_id'],(array)$items_not_available)){
					$itemss['available']=false;
				}
				if(isset($itemss['dish']) && is_array($itemss['dish'])){
					foreach ($itemss['dish'] as $dish_id) {
						if(isset($dish[$dish_id])){
							$itemss['dish_list'][]= $dish[$dish_id];
						}
					}
				}
				if(in_array($itemss['item_id'],(array)$favorites)){
					$itemss['is_favorite'] = true;
				}
			}

			foreach ($category as &$categories) {
				$categories['available']=true;
				$category_available = true;
				if(in_array($categories['cat_id'],(array)$category_not_available)){
					$categories['available']=false;
					$category_available = false;
				}
				foreach ($categories['items'] as $item_id) {
					if(isset($items[$item_id])){
						$available = $items[$item_id]['available'] ?? false;
						$items[$item_id]['available']  = $available && $category_available;
						$categories['item_list'][] = $items[$item_id];
					}
				}
			}

			$data = array(
				'category' => $category,
				'items' => $items,
				'items_not_available' => $items_not_available,
				'category_not_available' => $category_not_available,
				'dish' => $dish
			);
			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(
				'merchant_id' => $merchant_id,
				'data' => $data,
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
			$model = is_numeric($slug) ? CMerchants::get($slug) : CMerchantListingV1::getMerchantBySlug($slug);
			$merchant_id = $model->merchant_id;
			$item_uuid = Yii::app()->input->post('item_uuid');
			$cat_id = intval(Yii::app()->input->post('cat_id'));
			$currency_code = Yii::app()->input->post('currency_code');
			$base_currency = Price_Formatter::$number_format['currency_code'];
			$cart_row =  Yii::app()->request->getPost('cart_row', '');

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled']) ? Yii::app()->params['settings']['multicurrency_enabled'] : false;
			$multicurrency_enabled = $multicurrency_enabled == 1 ? true : false;
			$exchange_rate = 1;
			$admin_exchange_rate = 1;

			$points_enabled = isset(Yii::app()->params['settings']['points_enabled'])?Yii::app()->params['settings']['points_enabled']:false;
		    $points_enabled = $points_enabled==1?true:false;
			$points_earning_rule = isset(Yii::app()->params['settings']['points_earning_rule'])?Yii::app()->params['settings']['points_earning_rule']:'sub_total';
			$points_earning_points = isset(Yii::app()->params['settings']['points_earning_points'])?Yii::app()->params['settings']['points_earning_points']:0;

			if ($points_enabled && ($meta = AR_merchant_meta::getValue($merchant_id, 'loyalty_points'))) {
				$points_enabled = $meta['meta_value'] == 1;
			}

			$options_merchant = OptionsTools::find(['merchant_default_currency'], $merchant_id);
			$merchant_default_currency = isset($options_merchant['merchant_default_currency']) ? $options_merchant['merchant_default_currency'] : '';
			$merchant_default_currency = !empty($merchant_default_currency) ? $merchant_default_currency : $base_currency;
			$currency_code = !empty($currency_code) ? $currency_code : (empty($merchant_default_currency) ? $base_currency : $merchant_default_currency);

			// SET CURRENCY
			if (!empty($currency_code) && $multicurrency_enabled) {
				Price_Formatter::init($currency_code);
				if ($currency_code != $merchant_default_currency) {
					$exchange_rate = CMulticurrency::getExchangeRate($merchant_default_currency, $currency_code);
				}
				if($currency_code!=$base_currency){
					$admin_exchange_rate = CMulticurrency::getExchangeRate($currency_code,$base_currency);
				}
			}

			CMerchantMenu::setExchangeRate($exchange_rate);
			CMerchantMenu::setAdminExchangeRate($admin_exchange_rate);
			CMerchantMenu::setPointsRate($points_enabled,$points_earning_rule,$points_earning_points);

			$items = CMerchantMenu::getMenuItem($merchant_id, $cat_id, $item_uuid, Yii::app()->language);
			$addons = CMerchantMenu::getItemAddonCategory($merchant_id, $item_uuid, Yii::app()->language);
			$addon_items = CMerchantMenu::getAddonItems($merchant_id, $item_uuid, Yii::app()->language);
			//$meta = CMerchantMenu::getItemMeta($merchant_id,$item_uuid);
			$meta = CMerchantMenu::getItemMeta2($merchant_id, isset($items['item_id']) ? $items['item_id'] : 0);
			$meta_details = CMerchantMenu::getMeta($merchant_id, $item_uuid, Yii::app()->language);

			AppIdentity::getCustomerIdentity();
			$items['save_item']	= false;

			if (!Yii::app()->user->isGuest) {
				try {
					CSavedStore::getSaveItems(Yii::app()->user->id, $items['merchant_id'], $items['item_id']);
					$items['save_item']	= true;
				} catch (Exception $e) {
					//
				}
			}

			$data = array(
				'items' => $items,
				'addons' => $addons,
				'addon_items' => $addon_items,
				'meta' => $meta,
				'meta_details' => $meta_details
			);

			$cart_details = !empty($cart_row) ? (CCart::getCartDetails($cart_row) ?: null) : null;

			$this->code = 1;
			$this->msg = "ok";
			$this->details = array(
				'next_action' => "show_item_details",
				'sold_out_options' => AttributesTools::soldOutOptions(),
				'default_sold_out_options' => [
					'label' => t("Go with merchant recommendation"),
					'value' => "substitute"
				],
				'data' => $data,
				'merchant_id' => $merchant_id,
				'restaurant_name' => Yii::app()->input->xssClean($model->restaurant_name),
				'cart_details' => $cart_details
				//'config'=>$config
			);
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
			$this->details = array(
				'data' => array()
			);
		}
		$this->responseJson();
	}

	public function actionaddCartItems()
	{

		$slug = isset($this->data['slug']) ? $this->data['slug'] : '';
		$model = is_numeric($slug) ? CMerchants::get($slug) : CMerchantListingV1::getMerchantBySlug($slug);
		$merchant_id = $model->merchant_id;
		$uuid = CommonUtility::createUUID("{{cart}}", 'cart_uuid');
		$cart_uuid = $this->data['cart_uuid'] ?? '';
		$transaction_type = $this->data['transaction_type'] ?? '';
		$cartRow = $this->data['cart_row'] ?? null;
		$cart_uuid = !empty($cart_uuid) ? $cart_uuid : $uuid;
		$cart_row = CommonUtility::generateUIID();
		$cart_id = null;
		if ($cartRow) {
			if ($cart_row_data = CCart::getRow($cartRow)) {
				$cart_id = $cart_row_data->id;
			}
			CCart::remove($cart_uuid, $cartRow);
		}

		$model_cart = AR_cart::model()->find("cart_uuid=:cart_uuid", [
			':cart_uuid' => $cart_uuid
		]);
		if ($model_cart) {
			if ($model_cart->merchant_id != $merchant_id) {
				CCart::clear($cart_uuid);
			}
		}

		$cat_id = isset($this->data['cat_id']) ? (int)$this->data['cat_id'] : '';
		$item_token = isset($this->data['item_token']) ? $this->data['item_token'] : '';
		$item_size_id = isset($this->data['item_size_id']) ? (int)$this->data['item_size_id'] : 0;
		$item_qty = isset($this->data['item_qty']) ? (int)$this->data['item_qty'] : 0;
		$special_instructions = isset($this->data['special_instructions']) ? $this->data['special_instructions'] : '';
		$if_sold_out = isset($this->data['if_sold_out']) ? $this->data['if_sold_out'] : '';
		$inline_qty = isset($this->data['inline_qty']) ? (int)$this->data['inline_qty'] : 0;

		$addons = array();
		$item_addons = isset($this->data['item_addons']) ? $this->data['item_addons'] : '';
		if (is_array($item_addons) && count($item_addons) >= 1) {
			foreach ($item_addons as $val) {
				$multi_option = isset($val['multi_option']) ? $val['multi_option'] : '';
				$subcat_id = isset($val['subcat_id']) ? (int)$val['subcat_id'] : 0;
				$sub_items = isset($val['sub_items']) ? $val['sub_items'] : '';
				$sub_items_checked = isset($val['sub_items_checked']) ? (int)$val['sub_items_checked'] : 0;
				if ($multi_option == "one" && $sub_items_checked > 0) {
					$addons[] = array(
						'cart_row' => $cart_row,
						'cart_uuid' => $cart_uuid,
						'subcat_id' => $subcat_id,
						'sub_item_id' => $sub_items_checked,
						'qty' => 1,
						'multi_option' => $multi_option,
					);
				} else {
					foreach ($sub_items as $sub_items_val) {
						if ($sub_items_val['checked'] == 1) {
							$addons[] = array(
								'cart_row' => $cart_row,
								'cart_uuid' => $cart_uuid,
								'subcat_id' => $subcat_id,
								'sub_item_id' => isset($sub_items_val['sub_item_id']) ? (int)$sub_items_val['sub_item_id'] : 0,
								'qty' => isset($sub_items_val['qty']) ? (int)$sub_items_val['qty'] : 0,
								'multi_option' => $multi_option,
							);
						}
					}
				}
			}
		}

		$attributes = array();
		$meta = isset($this->data['meta']) ? $this->data['meta'] : '';
		if (is_array($meta) && count($meta) >= 1) {
			foreach ($meta as $meta_name => $metaval) {
				if ($meta_name != "dish") {
					foreach ($metaval as $val) {
						if ($val['checked'] > 0) {
							$attributes[] = array(
								'cart_row' => $cart_row,
								'cart_uuid' => $cart_uuid,
								'meta_name' => $meta_name,
								'meta_id' => $val['meta_id']
							);
						}
					}
				}
			}
		}

		$items = array(
			'cart_id' => $cart_id,
			'merchant_id' => $merchant_id,
			'cart_row' => $cart_row,
			'cart_uuid' => $cart_uuid,
			'cat_id' => $cat_id,
			'item_token' => $item_token,
			'item_size_id' => $item_size_id,
			'qty' => $item_qty,
			'special_instructions' => $special_instructions,
			'if_sold_out' => $if_sold_out,
			'addons' => $addons,
			'attributes' => $attributes,
			'inline_qty' => $inline_qty
		);

		try {


			CCart::add($items);
			CCart::applyAutoAddPromo($cart_uuid, $merchant_id);

			// quick fixed
			if (!CCart::getAttributes($cart_uuid, Yii::app()->params->local_transtype)) {
				CCart::savedAttributes($cart_uuid, Yii::app()->params->local_transtype, $transaction_type);
			}

			/*SAVE DELIVERY DETAILS*/
			if (!CCart::getAttributes($cart_uuid, 'whento_deliver')) {
				$whento_deliver = isset($this->data['whento_deliver']) ? $this->data['whento_deliver'] : 'now';
				CCart::savedAttributes($cart_uuid, 'whento_deliver', $whento_deliver);
				if ($whento_deliver == "schedule") {
					$delivery_date = isset($this->data['delivery_date']) ? $this->data['delivery_date'] : '';
					$delivery_time_raw = isset($this->data['delivery_time_raw']) ? $this->data['delivery_time_raw'] : '';
					if (!empty($delivery_date)) {
						CCart::savedAttributes($cart_uuid, 'delivery_date', $delivery_date);
					}
					if (!empty($delivery_time_raw)) {
						CCart::savedAttributes($cart_uuid, 'delivery_time', json_encode($delivery_time_raw));
					}
				}
			}

			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(
				'cart_uuid' => $cart_uuid
			);
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
			$this->details = array(
				'data' => array()
			);
		}
		$this->responseJson();
	}

	public function actiongetCartCheckout()
	{
		$this->actiongetCart();
	}

	public function actiongetcart()
	{
		try {

			$setttings = Yii::app()->params['settings'];
			$home_search_mode = $setttings['home_search_mode'] ?? 'address';
			$location_searchtype = $setttings['location_searchtype'] ?? '';
			$search_type = $location_searchtype;			
			$strict_to_wallet = !empty($setttings['strict_to_wallet']) && !empty($setttings['digitalwallet_enabled']);

			$local_id = $this->data['place_id'] ?? null;
			$cart_uuid = $this->data['cart_uuid'] ?? null;
			$payload = $this->data['payload'] ?? null;
			$slug = $this->data['slug'] ?? null;
			$currency_code = $this->data['currency_code'] ?? '';
			$base_currency = Price_Formatter::$number_format['currency_code'];

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled']) ? Yii::app()->params['settings']['multicurrency_enabled'] : false;
			$multicurrency_enabled = $multicurrency_enabled == 1 ? true : false;

			$latitude = $this->data['latitude'] ?? null;
			$longitude = $this->data['longitude'] ?? null;
			$place_data = is_array($this->data['place_data'] ?? null) ? $this->data['place_data'] : [];
			$address_uuid = $place_data['address_uuid'] ?? null;

			$location = $this->data['location'] ?? null;
			$city_id = $location['city_id'] ?? '';
			$area_id = $location['area_id'] ?? '';
			$state_id = $location['state_id'] ?? '';
			$postal_id = $location['postal_id'] ?? '';

			$distance = 0;
			$unit = isset(Yii::app()->params['settings']['home_search_unit_type']) ? Yii::app()->params['settings']['home_search_unit_type'] : 'mi';
			$error = array();
			$minimum_order = 0;
			$maximum_order = 0;
			$merchant_info = array();
			$delivery_fee = 0;
			$distance_covered = 0;
			$merchant_lat = '';
			$merchant_lng = '';
			$out_of_range = false;
			$address_component = array();
			$items_count = 0;
			$resp_opening = array();
			$transaction_info = array();
			$data_transaction = array();
			$tips_data  = array();
			$enabled_tip = false;
			$enabled_voucher = false;
			$exchange_rate = 1;
			$admin_exchange_rate = 1;
			$points_to_earn = 0;
			$points_label = '';
			$free_delivery_on_first_order = false;
			$merchant_id = null;
			$checkout_data = [];
			$data = [];
			$estimation_time = '';
			$client_id = '';

			if (in_array('distance', (array)$payload)) {
				CMaps::config();
			}

			if (!empty($slug)) {
				$merchant = CMerchantListingV1::getMerchantBySlug($slug);
				$merchant_id = $merchant->merchant_id;
			} else {
				$merchant_id = CCart::getMerchantId($cart_uuid);
				$merchant = CMerchantListingV1::getMerchant($merchant_id);
			}

			$merchant_lat = $merchant['latitude'] ?? '';
			$merchant_lng = $merchant['lontitude'] ?? '';
			$merchant_unit = $merchant['distance_unit'] ?? 'mi';

			$services = [];
			$delivery_option = [];
			$delivery_option2 = [];
			$delivery_option_list = [];
			$delivery_type = '';
			$servicesList = [];

			try {
				$servicesList = CCheckout::getMerchantTransactionList($merchant_id, Yii::app()->language, 'services', false);
				$services = CommonUtility::ArrayToLabelValue($servicesList);
			} catch (Exception $e) {
			}

			$options_merchant = OptionsTools::find(
				[
					'merchant_time_selection',
					'merchant_timezone',
					'merchant_default_currency',
					'free_delivery_on_first_order',
					'merchant_close_store',
					'merchant_tip_type',
					'merchant_default_tip',
					'merchant_delivery_charges_type',
					'booking_enabled'
				],
				$merchant_id
			);
			$time_selection = $options_merchant['merchant_time_selection'] ?? 1;
			$merchant_tip_type = $options_merchant['merchant_tip_type'] ?? 'fixed';
			$merchant_default_tip = $options_merchant['merchant_default_tip'] ?? 0;
			$charge_type = $options_merchant['merchant_delivery_charges_type'] ?? '';
			$booking_enabled = $options_merchant['booking_enabled'] ?? false;
			$booking_enabled = $booking_enabled==1?true:false;

			$deliveryOption = CCheckout::deliveryOptionList('', $time_selection);
			if (!empty($deliveryOption)) {
				foreach ($deliveryOption as $items) {
					$delivery_option[$items['value']] = $items['short_name'];
					$delivery_option2[] = [
						'value' => $items['value'],
						'name' => t($items['short_name']),
						'estimation' => ''
					];
				}
			}
			$delivery_option_list = $delivery_option2;

			$merchant_default_currency = isset($options_merchant['merchant_default_currency']) ? $options_merchant['merchant_default_currency'] : '';
			$merchant_default_currency = !empty($merchant_default_currency) ? $merchant_default_currency : $base_currency;
			$merchant_timezone = isset($options_merchant['merchant_timezone']) ? $options_merchant['merchant_timezone'] : '';
			$merchant_close_store = $options_merchant['merchant_close_store'] ?? false;
			$merchant_close_store = $merchant_close_store == 1 ? true : false;
			if (!empty($merchant_timezone)) {
				Yii::app()->timezone = $merchant_timezone;
			}

			$free_delivery_on_first_order = isset($options_merchant['free_delivery_on_first_order']) ? $options_merchant['free_delivery_on_first_order'] : false;
			$free_delivery_on_first_order = $free_delivery_on_first_order == 1 ? true : false;

			$currency_code = !empty($currency_code) ? $currency_code : (empty($merchant_default_currency) ? $base_currency : $merchant_default_currency);

			$points_enabled = isset(Yii::app()->params['settings']['points_enabled']) ? Yii::app()->params['settings']['points_enabled'] : false;
			$points_enabled = $points_enabled == 1 ? true : false;

			if ($points_enabled && ($meta = AR_merchant_meta::getValue($merchant_id, 'loyalty_points'))) {
				$points_enabled = $meta['meta_value'] == 1;
			}

			// HIDE POINTS IF GUEST
			if (!Yii::app()->user->isGuest) {
				if (Yii::app()->user->social_strategy == "guest") {
					$points_enabled = false;
				}
			}

			$points_earning_rule = isset(Yii::app()->params['settings']['points_earning_rule']) ? Yii::app()->params['settings']['points_earning_rule'] : 'sub_total';
			$points_earning_points = isset(Yii::app()->params['settings']['points_earning_points']) ? Yii::app()->params['settings']['points_earning_points'] : 0;
			$points_minimum_purchase = isset(Yii::app()->params['settings']['points_minimum_purchase']) ? Yii::app()->params['settings']['points_minimum_purchase'] : 0;
			$points_maximum_purchase = isset(Yii::app()->params['settings']['points_maximum_purchase']) ? Yii::app()->params['settings']['points_maximum_purchase'] : 0;

			// SET CURRENCY
			if (!empty($currency_code) && $multicurrency_enabled) {
				Price_Formatter::init($currency_code);
				if ($currency_code != $merchant_default_currency) {
					$exchange_rate = CMulticurrency::getExchangeRate($merchant_default_currency, $currency_code);
				}

				if ($currency_code != $base_currency) {
					$admin_exchange_rate = CMulticurrency::getExchangeRate($currency_code, $base_currency);
				}
			}

			CCart::setExchangeRate($exchange_rate);
			CCart::setAdminExchangeRate($admin_exchange_rate);
			CCart::setPointsRate($points_enabled, $points_earning_rule, $points_earning_points, $points_minimum_purchase, $points_maximum_purchase);

			$cart = AR_cart::model()->find("merchant_id=:merchant_id AND cart_uuid=:cart_uuid", [
				':merchant_id' => $merchant_id,
				':cart_uuid' => $cart_uuid
			]);
			if ($cart) {
				$cart_uuid = $cart_uuid ?: $cart->cart_uuid;
				require_once 'get-cart.php';
				try {
					AR_cart::model()->updateAll(array(
						'subtotal' => isset($sub_total) ? floatval($sub_total) : 0,
						'total' => isset($total) ? floatval($total) : 0,
						'currency_code' => $currency_code
					), "cart_uuid=:cart_uuid", [
						":cart_uuid" => $cart_uuid,
					]);

					$total_estimate_time = $preparation_time_estimation + $delivery_time_estimation;
					if ($total_estimate_time > 0  && $delivery_option2) {
						$estimation_time = CommonUtility::addTimeBuffer($total_estimate_time);
						$schedule_delivery_date_time = '';
						$delivery_type = $transaction_info['whento_deliver'] ?? 'now';

						if (in_array('checkout', (array)$payload)  && $delivery_type == "schedule") {
							$deliveryDate = $transaction_info['delivery_date'] ?? null;
							$deliveryTime = $transaction_info['delivery_time'] ?? null;
							if ($deliveryDate && $deliveryTime) {
								$schedule_delivery_date_time = t("{date}, {time}", [
									'{date}' => CommonUtility::DateToPretty($deliveryDate),
									'{time}' => $deliveryTime['pretty_time'] ?? '',
								]);
							}
						}

						foreach ($delivery_option2 as &$item) {
							if ($item["value"] === "now") {
								$item["estimation"] = $estimation_time;
							} else if ($item["value"] === "schedule") {
								$item["estimation"] = $schedule_delivery_date_time;
							}
						}
					}
				} catch (Exception $e) {
				}
			} else {
				CCart::ClearCart($cart_uuid);
				CCacheData::add();
			}

			$cartAttributes = CCart::getAttributesAll($cart_uuid, ['whento_deliver', 'transaction_type', 'estimation', 'delivery_date', 'delivery_time']);
			if ($cartAttributes) {
				$delivery_type = $cartAttributes['whento_deliver'] ?? 'now';
				if (!isset($deliveryOption[$delivery_type])) {
					$delivery_type = array_key_first((array) $deliveryOption);
					$cartAttributes['whento_deliver'] = $delivery_type;
					CCart::savedAttributes($cart_uuid, 'whento_deliver', $delivery_type);
					CCart::savedAttributes($cart_uuid, 'delivery_time', '');
				}

				$transaction_type = $cartAttributes['transaction_type'] ?? 'delivery';
				if(!array_key_exists($transaction_type,(array)$servicesList)){
					$firstServices = array_key_first($servicesList);
					$transaction_type = $firstServices;
				}
				$delivery_type_pretty = isset($deliveryOption[$delivery_type]) ? $deliveryOption[$delivery_type]['short_name'] : t($delivery_type);
				$transaction_type_pretty = isset($servicesList[$transaction_type]) ? $servicesList[$transaction_type] : t($transaction_type);
				$deliveryDate = $cartAttributes['delivery_date'] ?? null;

				$cartAttributes['transaction_type'] = $transaction_type;
				$cartAttributes['delivery_type_pretty'] = $delivery_type_pretty;
				$cartAttributes['transaction_type_pretty'] = $transaction_type_pretty;
				$cartAttributes['delivery_time'] = isset($cartAttributes['delivery_time']) ? json_decode($cartAttributes['delivery_time'], true) : '';
				if($deliveryDate){
					$cartAttributes['scheduled_datetime'] = t("{date}, {time}", [
						'{date}' => CommonUtility::DateToPretty($deliveryDate),
						'{time}' => $cartAttributes['delivery_time']['pretty_time'] ?? ''
				  ]);
				}
				$transaction_info = $cartAttributes;
			} else {
				$transaction_type = !empty($servicesList) ? array_key_first($servicesList) : 'delivery';
				$delivery_type = array_key_exists('now', $deliveryOption) ? 'now' : 'schedule';
				$delivery_type_pretty = isset($deliveryOption[$delivery_type]) ? $deliveryOption[$delivery_type]['short_name'] : t($delivery_type);
				$transaction_type_pretty = isset($servicesList[$transaction_type]) ? $servicesList[$transaction_type] : t($transaction_type);
				$transaction_info = [
					'transaction_type' => $transaction_type,
					'whento_deliver' => $delivery_type,
					'delivery_date_pretty' => '',
					'delivery_time' => '',
					'delivery_type_pretty' => $delivery_type_pretty,
					'transaction_type_pretty' => $transaction_type_pretty,
					'scheduled_datetime' => ''
				];
			}

			// GET STANDARD ESTIMATION
			$standard_estimation_time = '';
			if(in_array('standard_estimation',(array)$payload)){
				if($home_search_mode=="address"){
					$resp_distance = CMaps::getLocalDistance($merchant_unit, $latitude, $longitude, $merchant_lat, $merchant_lng);
					$params_estimation = [
						'merchant_id'=>$merchant_id,
						'shipping_type'=>'standard',
						'charges_type'=>$charge_type,
						'distance'=>$resp_distance,
						'transaction_type'=>$transaction_type
					];
					if($resp_estimation = CMerchantListingV1::estimationMerchantNew($params_estimation)){
						$standard_estimation_time = t("{estimation} mins",[
							'{estimation}'=>$resp_estimation['estimation'] ?? ''
						]);
					}
				} else if ($home_search_mode == 'location') {
					try {
						$estimation_resp = Clocations::LocationEstimation($merchant_id, [
							'search_type' => $search_type,
							'city_id' => intval($city_id),
							'area_id' => intval($area_id),
							'state_id' => intval($state_id),
							'postal_id' => $postal_id
						]);
						$standard_estimation_time = $estimation_resp[$transaction_type] ?? '';
					} catch (Exception $e) {
					}
				}

				foreach ($delivery_option_list as &$item) {
					if ($item["value"] === "now") {
						$item["estimation"] = $standard_estimation_time;
					} else if ($item["value"] === "schedule") {
						$item["estimation"] = $transaction_info['scheduled_datetime'] ?? '';
					}
				}
		    }
			// GET STANDARD ESTIMATION

			$enabled_select_time = $delivery_type == 'schedule' && empty($transaction_info['delivery_time'] ?? null);

			$store_open = true;
			$store_open_message = '';
			$show_schedule = true;
			$time_already_passed = false;
			if (in_array('check_opening', (array)$payload)) {

				if ($merchant_close_store) {
					$store_open = false;
					$show_schedule = false;
					$store_open_message = t('This store is close and not accepting orders.');
					$error[] = $store_open_message;
				}

				$enabled_website_ordering = Yii::app()->params['settings']['enabled_website_ordering'] ?? false;
				$enabled_website_ordering = $enabled_website_ordering == 1 ? true : false;
				if (!$enabled_website_ordering) {
					$store_open = false;
					$show_schedule = false;
					$store_open_message = t('Currently unavailable.');
					$error[] = $store_open_message;
				}

				if ($store_open) {
					$date = date("Y-m-d");
					$time_now = date("H:i");
					if ($delivery_type == "schedule") {
						$date = $transaction_info['delivery_date'] ?? $date;
						$time_now = $transaction_info['delivery_time']['start_time'] ?? ($time ?? null);
					}

					try {
						$datetime_to = date("Y-m-d g:i:s a", strtotime("$date $time_now"));
						CMerchantListingV1::checkCurrentTime(date("Y-m-d g:i:s a"), $datetime_to);
						$time_already_passed = false;
					} catch (Exception $e) {
						$time_already_passed = true;
						$enabled_select_time = true;
					}

					try {
						$respOpening = CMerchantListingV1::checkStoreOpen($merchant_id, $date, $time_now);
						$merchant_open_status = $respOpening['merchant_open_status'] ?? false;
						$holiday_id = $respOpening['holiday_id'] ?? false;
						if (!$merchant_open_status) {
							$store_open = false;
							if($time_selection==3){
								$show_schedule = false;
								$store_open_message = t('This store is close right now.');
							} else $store_open_message = t('This store is close right now, but you can schedulean order later.');
							$error[] = $store_open_message;
						}
						if ($holiday_id) {
							$store_open = false;
							$store_open_message = $respOpening['holiday_reason'] ?? t("Restaurant is close");
							$error[] = $store_open_message;
						}
					} catch (Exception $e) {
					}
				}
			}


			$delivery_address = [];
			$address_details = [];
			if (in_array('checkout', (array)$payload) && !Yii::app()->user->isGuest) {
				if ($home_search_mode == 'address') {
					if ($addressResp = CClientAddress::getAddressByUUID($client_id, $address_uuid)) {
						$delivery_address = [
							'is_address_found' => true,
							'name' => $addressResp['address_label'],
							'address' => $addressResp['formatted_address'],
							'address_details' => $addressResp['street_number'] . " " . $addressResp['street_name'],
							'instructions' => $addressResp['delivery_instructions']
						];
						$address_details = $addressResp;
					} else {
						$delivery_address = [
							'is_address_found' => false,
							'name' => $place_data['place_text'] ?? '',
							'address' => $place_data['formatted_address'] ?? '',
							'address_details' => '',
							'instructions' => '',
						];
						$place_data['address_label'] = t("Home");
						$place_data['latitude'] = $latitude;
						$place_data['longitude'] = $longitude;
						$address_details = $place_data;
					}
				} else if ($home_search_mode == 'location') {
					$address_uuid = $this->data['address_uuid'] ?? null;
					$state_id = $location['state_id'] ?? '';
					$city_id = $location['city_id'] ?? '';
					$area_id = $location['area_id'] ?? '';
					$postal_id = $location['postal_id'] ?? '';
					if (empty($address_uuid)) {

						// FIND FIRST IN ADDRESS IF ADDRESS USE EXIST
						$address_location_found = false;
						try {
							$delivery_address = Clocations::defaultAddress($client_id, [
								'search_type' => $location_searchtype,
								'state_id' => $state_id,
								'city_id' => $city_id,
								'area_id' => $area_id
							]);
							$delivery_address['is_address_found'] = true;
							$address_location_found = true;
						} catch (Exception $e) {
						}

						if (!$address_location_found) {
							try {
								$delivery_address = Clocations::getLocations($location_searchtype, $city_id, $area_id, $state_id, $postal_id);
								$delivery_address['is_address_found'] = false;
								$delivery_address['state_id'] = $state_id;
								$delivery_address['city_id'] = $city_id;
								$delivery_address['area_id'] = $area_id;
								$delivery_address['postal_id'] = $postal_id;
							} catch (Exception $e) {
								$delivery_address['is_address_found'] = false;
							}
						}
					} else {
						// GET SAVED ADDRESS BY UIID
						try {
							$delivery_address = Clocations::getAddressDetails($client_id, $address_uuid);
							$delivery_address['is_address_found'] = true;
						} catch (Exception $e) {
							$delivery_address['is_address_found'] = false;
						}
					}
				}
			}

			// GET BOOKING TABLES
			$room_list = [];
			$table_list = [];
			if(in_array('checkout',(array)$payload) && $transaction_type=='dinein' && $booking_enabled){
				$room_list = CommonUtility::getDataToDropDown("{{table_room}}", "room_uuid", "room_name", "WHERE merchant_id=" . q($merchant_id) . " ", "order by room_name asc");
				if (is_array($room_list) && count($room_list) >= 1) {
					$room_list = CommonUtility::ArrayToLabelValue($room_list);
				}
				try {
					$table_list = CBooking::getTableList($merchant_id);
				} catch (Exception $e) {
				}
			}

			$unit_pretty = !empty($unit) ? MapSdk::prettyUnit($unit) : $unit;
			$order_instructions = $order_instructions ?? null;
			$tip_list = $tip_list ?? null;
			$payment_method = $payment_method ?? null;
			$discountApplied = $discountApplied ?? null;

			// over write prep time with normal estimation time
			// $estimationData = $checkout_data['data']['estimation'] ?? null;
			// if($estimationData && isset($delivery_option2[0])){
			// 	$delivery_option2[0]['estimation'] =  $estimationData;
			// }

			// GET PAYMENT LIST
			$enabled_hide_payment = Yii::app()->params['settings']['multicurrency_enabled_hide_payment'] ?? false;
			$hide_payment = $multicurrency_enabled == true ? ($enabled_hide_payment == 1 ? true : false) : false;

			$merchants = CMerchantListingV1::getMerchant($merchant_id);
			$saved_payment = null;
			$payment_list = [];
			$wallet_balance = 0;
			$amount_due = 0;

			if (in_array('checkout', (array)$payload) && !Yii::app()->user->isGuest) {
				try {
					$saved_payment = CPayments::SavedPaymentList(Yii::app()->user->id, $merchants->merchant_type,
					$merchant_id, $hide_payment, $currency_code,'',true);
				} catch (Exception $e) {}


				try {
					$payment_list = CPayments::PaymentList($merchant_id);
					$payments_credentials = CPayments::getPaymentCredentials($merchant_id, '', $merchants->merchant_type);
					foreach ($payment_list as &$items) {
						$items['saved_payment'] = $saved_payment[$items['payment_code']] ?? null;
						$items['credentials'] = $payments_credentials[$items['payment_code']] ?? null;
					}
				} catch (Exception $e) {}
				
				if($strict_to_wallet){
					if(!isset($total)){
						$total = 0;
					}
					$wallet_balance = CDigitalWallet::getAvailableBalance(Yii::app()->user->id);
			        $wallet_balance = ($wallet_balance*$exchange_rate);
			        $amount_due = CDigitalWallet::calculateAmountDue($total,$wallet_balance);										
				}				
		    }

			$this->code = 1;
			$this->msg = "ok";
			$this->details = [
				'cart_uuid' => $cart_uuid,
		      'payload'=>$payload,
				'error' => $error,
				'merchant_id' => $merchant_id,
				'checkout_data' => $checkout_data,
				'out_of_range' => $out_of_range,
				'address_component' => $address_component,
				'items_count' => $items_count,
				'data' => $data,
				'phone_details' => [],
				'points_data' => [
					'points_enabled' => $points_enabled,
					'points_to_earn' => $points_to_earn,
					'points_label' => $points_label,
				],
				'services' => $services,
				'delivery_option' => !empty($delivery_option) ? CommonUtility::ArrayToLabelValue($delivery_option) : [],
				'delivery_option2' => $delivery_option2,
				'delivery_option_list'=>$delivery_option_list,
				'transaction_info' => $transaction_info,
				'estimation_time' => $estimation_time,
				'estimation_time_pretty' => t("{transaction_type} in {estimate}", [
					'{transaction_type}' => $transaction_type_pretty,
					'{estimate}' => $estimation_time,
				]),
				'standard_estimation_time'=>$standard_estimation_time,
				'order_instructions' => $order_instructions,
				'data_transaction' => '',
				'enabled_tip' => $enabled_tip == 1 ? true : false,
				'tip_list' => $tip_list,
				'tips_data' => $tips_data,
				'enabled_voucher' => $enabled_voucher == 1 ? true : false,
				'enabled_select_time' => $enabled_select_time,
				'store_open' => $store_open,
				'show_schedule' => $show_schedule,
				'store_open_message' => $store_open_message,
				'time_already_passed' => $time_already_passed,
				'distance_pretty' => t("Distance {distance}", [
					'{distance}' => "$distance $unit_pretty"
				]),
				'distance_pretty1' => t("{distance} away", [
					'{distance}' => "$distance $unit_pretty"
				]),
				'delivery_address' => $delivery_address,
				'address_details' => $address_details,
				'payment_method' => $payment_method,
				'discount_applied' => $discountApplied,
				'room_list'=>$room_list,
				'table_list'=>$table_list,
				'payment_list'=>$payment_list,
				'strict_to_wallet'=>$strict_to_wallet,
				'wallet'=>[
					'wallet_balance'=>Price_Formatter::formatNumber($wallet_balance),
					'wallet_balance_raw'=>$wallet_balance,
				    'amount_due'=>Price_Formatter::formatNumber($amount_due),
					'amount_due_raw'=>$amount_due,
				]
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionupdateCartItems()
	{
		try {

			$cart_uuid = Yii::app()->input->post('cart_uuid');
			$cart_row = Yii::app()->input->post('cart_row');
			$item_qty = Yii::app()->input->post('item_qty');

			CCart::update($cart_uuid, $cart_row, $item_qty);

			$merchant_id = CCart::getMerchantId($cart_uuid);
			CCart::applyAutoAddPromo($cart_uuid, $merchant_id);

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = array(
				'data' => array()
			);
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
			$this->details = array(
				'data' => array()
			);
		}
		$this->responseJson();
	}

	public function actionremoveCartItem()
	{
		$cart_uuid = Yii::app()->input->post('cart_uuid');
		$row = Yii::app()->input->post('row');

		try {

			CCart::remove($cart_uuid, $row);

			$total_item = CCart::getCountCart($cart_uuid);
			if ($total_item <= 0) {
				CCart::clear($cart_uuid);
			}

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = array(
				'data' => array()
			);
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
			$this->details = array(
				'data' => array()
			);
		}
		$this->responseJson();
	}

	public function actionclearCart()
	{
		try {
			$cart_uuid = Yii::app()->input->post('cart_uuid');
			CCart::clear($cart_uuid);
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = array(
				'data' => array()
			);
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
			$this->details = array(
				'data' => array()
			);
		}
		$this->responseJson();
	}

	public function actionclientAddresses()
	{
		try {

			$addresses = CClientAddress::getAddresses(Yii::app()->user->id);
			$this->code = 1;
			$this->msg = "ok";
			$this->details = array(
				'addresses' => $addresses,
			);
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetPhone()
	{
		try {

			$options = OptionsTools::find(array('mobilephone_settings_default_country', 'mobilephone_settings_country'));
			$phone_default_country = isset($options['mobilephone_settings_default_country']) ? $options['mobilephone_settings_default_country'] : 'us';
			$phone_country_list = isset($options['mobilephone_settings_country']) ? $options['mobilephone_settings_country'] : '';
			$phone_country_list = !empty($phone_country_list) ? json_decode($phone_country_list, true) : array();

			$data = AttributesTools::countryMobilePrefixWithFilter($phone_country_list);

			$cart_uuid = Yii::app()->input->post('cart_uuid');

			$atts = CCart::getAttributesAll($cart_uuid, array('contact_number', 'contact_number_prefix'));
			$contact_number = isset($atts['contact_number']) ? $atts['contact_number'] : '';
			$default_prefix = isset($atts['contact_number_prefix']) ? $atts['contact_number_prefix'] : '';

			if (empty($contact_number)) {
				$contact_number = Yii::app()->user->contact_number;
				$default_prefix = Yii::app()->user->phone_prefix;
			}

			$contact_number = str_replace($default_prefix, "", $contact_number);
			$contact_number = str_replace("+", "", $contact_number);
			$default_prefix = str_replace("+", "", $default_prefix);

			if (empty($default_prefix)) {
				$default_prefix_array = AttributesTools::getMobileByShortCode($phone_default_country);
			} else $default_prefix_array = AttributesTools::getMobileByPhoneCode($default_prefix);


			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(
				'contact_number_w_prefix' => $default_prefix . $contact_number,
				'contact_number' => $contact_number,
				'default_prefix' => $default_prefix,
				'default_prefix_array' => $default_prefix_array,
				'prefixes' => $data,
				'phone_default_country' => $phone_default_country
			);
		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionRequestEmailCode()
	{
		try {
			if (!Yii::app()->user->isGuest) {
				$model = AR_client::model()->find(
					'client_id=:client_id',
					array(':client_id' => Yii::app()->user->id)
				);
				if ($model) {
					$digit_code = CommonUtility::generateNumber(4, true);
					$model->mobile_verification_code = $digit_code;
					$model->scenario = "resend_otp";
					if ($model->save()) {
						// SEND EMAIL HERE
						$this->code = 1;
						$this->msg = t("We sent a code to {{email_address}}.", array(
							'{{email_address}}' => CommonUtility::maskEmail($model->email_address)
						));
						if (DEMO_MODE == TRUE) {
							$this->details['verification_code'] = t("Your verification code is {{code}}", array('{{code}}' => $digit_code));
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

			$cart_uuid = isset($this->data['cart_uuid']) ? $this->data['cart_uuid'] : '';
			$code = isset($this->data['code']) ? $this->data['code'] : '';
			$mobile_prefix = isset($this->data['phone_prefix']) ? $this->data['phone_prefix'] : '';
			$mobile_number = isset($this->data['phone_number']) ? $this->data['phone_number'] : '';

			$model = AR_client::model()->find(
				'client_id=:client_id AND mobile_verification_code=:mobile_verification_code',
				array(':client_id' => Yii::app()->user->id, ':mobile_verification_code' => CommonUtility::safeTrim($code))
			);
			if ($model) {
				$model->phone_prefix = $mobile_prefix;
				$model->contact_phone = $mobile_prefix . $mobile_number;
				if ($model->save()) {
					CCart::savedAttributes($cart_uuid, 'contact_number', $model->contact_phone);
					CCart::savedAttributes($cart_uuid, 'contact_number_prefix', $mobile_prefix);

					$this->code = 1;
					$this->msg = t("Succesfully change phone number");
					$this->details = array(
						'phone_prefix' => $mobile_prefix,
						'contact_phone' => $model->contact_phone,
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

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled']) ? Yii::app()->params['settings']['multicurrency_enabled'] : false;
			$multicurrency_enabled = $multicurrency_enabled == 1 ? true : false;
			$exchange_rate = 1;

			$options_merchant = OptionsTools::find(['merchant_default_currency'], $merchant_id);
			$merchant_default_currency = isset($options_merchant['merchant_default_currency']) ? $options_merchant['merchant_default_currency'] : '';
			$merchant_default_currency = !empty($merchant_default_currency) ? $merchant_default_currency : $base_currency;
			$currency_code = !empty($currency_code) ? $currency_code : (empty($merchant_default_currency) ? $base_currency : $merchant_default_currency);

			// SET CURRENCY
			if (!empty($currency_code) && $multicurrency_enabled) {
				Price_Formatter::init($currency_code);
				if ($currency_code != $merchant_default_currency) {
					$exchange_rate = CMulticurrency::getExchangeRate($merchant_default_currency, $currency_code);
				}
			}

			CPromos::setExchangeRate($exchange_rate);
			//$data = CPromos::promo($merchant_id,date("Y-m-d"));
			$data = CPromos::getAvaialblePromoNew([$merchant_id], CommonUtility::dateOnly(), false, true);

			$promo_selected = array();
			$atts = CCart::getAttributesAll($cart_uuid, array('promo', 'promo_type', 'promo_id'));
			if ($atts) {
				$saving = '';
				if (isset($atts['promo'])) {
					if ($promo = json_decode($atts['promo'], true)) {
						if ($promo['type'] == "offers") {
							$merchant_id = CCart::getMerchantId($cart_uuid);
							CCart::getContent($cart_uuid, Yii::app()->language);
							$subtotal = CCart::getSubTotal();
							$sub_total = floatval($subtotal['sub_total']);
							$discount_percent = isset($promo['value']) ? CCart::cleanValues($promo['value']) : 0;
							$discount_value = ($discount_percent / 100) * $sub_total;
							$saving = t("You're saving {{discount}}", array(
								'{{discount}}' => Price_Formatter::formatNumber(($discount_value * $exchange_rate))
							));
						} elseif ($promo['type'] == "voucher") {
							$discount_value = isset($promo['value']) ? $promo['value'] : 0;
							$discount_value = $discount_value * -1;
							$saving = t("You're saving {{discount}}", array(
								'{{discount}}' => Price_Formatter::formatNumber(($discount_value * $exchange_rate))
							));
						}
						$promo_selected = [
							'promo_type' => $atts['promo_type'],
							'promo_id' => $atts['promo_id'],
							'savings' => $saving
						];
					}
				}
			}

			if ($data) {
				$this->code = 1;
				$this->msg = "ok";
				$this->details = array(
					'count' => count($data),
					'data' => $data,
					'promo_selected' => $promo_selected
				);
			} else $this->msg = t("no results");
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionapplyPromo()
	{
		$cart_uuid = isset($this->data['cart_uuid']) ? $this->data['cart_uuid'] : '';
		$promo_id = isset($this->data['promo_id']) ? intval($this->data['promo_id']) : '';
		$promo_type = isset($this->data['promo_type']) ? $this->data['promo_type'] : '';
		$currency_code = isset($this->data['currency_code']) ? $this->data['currency_code'] : '';
		$base_currency = Price_Formatter::$number_format['currency_code'];

		$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled']) ? Yii::app()->params['settings']['multicurrency_enabled'] : false;
		$multicurrency_enabled = $multicurrency_enabled == 1 ? true : false;
		$exchange_rate = 1;

		try {

			$merchant_id = CCart::getMerchantId($cart_uuid);

			$options_merchant = OptionsTools::find(['merchant_default_currency'], $merchant_id);
			$merchant_default_currency = isset($options_merchant['merchant_default_currency']) ? $options_merchant['merchant_default_currency'] : '';
			$merchant_default_currency = !empty($merchant_default_currency) ? $merchant_default_currency : $base_currency;
			$currency_code = !empty($currency_code) ? $currency_code : (empty($merchant_default_currency) ? $base_currency : $merchant_default_currency);

			// SET CURRENCY
			if (!empty($currency_code) && $multicurrency_enabled) {
				Price_Formatter::init($currency_code);
				if ($currency_code != $merchant_default_currency) {
					$exchange_rate = CMulticurrency::getExchangeRate($merchant_default_currency, $currency_code);
				}
			}

			CCart::getContent($cart_uuid, Yii::app()->language);
			$subtotal = CCart::getSubTotal();
			$sub_total = floatval($subtotal['sub_total']);

			$now = date("Y-m-d");
			$params = array();

			CPromos::setExchangeRate($exchange_rate);

			if ($promo_type === "voucher") {

				$transaction_type = CCart::cartTransaction($cart_uuid, Yii::app()->params->local_transtype, $merchant_id);
				$resp = CPromos::applyVoucher($merchant_id, $promo_id, Yii::app()->user->id, $now, ($sub_total * $exchange_rate), $transaction_type);
				$less_amount = $resp['less_amount'];

				$params = array(
					'name' => "less voucher",
					'type' => $promo_type,
					'id' => $promo_id,
					'target' => 'subtotal',
					'value' => "-$less_amount",
				);
			} else if ($promo_type == "offers") {

				$transaction_type = CCart::cartTransaction($cart_uuid, Yii::app()->params->local_transtype, $merchant_id);
				$resp = CPromos::applyOffers($merchant_id, $promo_id, $now, ($sub_total * $exchange_rate), $transaction_type);
				$less_amount = $resp['less_amount'];

				$name = array(
					'label' => "Discount {{discount}}%",
					'params' => array(
						'{{discount}}' => Price_Formatter::convertToRaw($less_amount, 0)
					)
				);
				$params = array(
					'name' => json_encode($name),
					'type' => $promo_type,
					'id' => $promo_id,
					'target' => 'subtotal',
					'value' => "-%$less_amount"
				);
			}

			CCart::savedAttributes($cart_uuid, 'promo', json_encode($params));
			CCart::savedAttributes($cart_uuid, 'promo_type', $promo_type);
			CCart::savedAttributes($cart_uuid, 'promo_id', $promo_id);

			$this->code = 1;
			$this->msg = "succesful";
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionremovePromo()
	{

		$cart_uuid = isset($this->data['cart_uuid']) ? $this->data['cart_uuid'] : '';
		$promo_id = isset($this->data['promo_id']) ? intval($this->data['promo_id']) : '';
		$promo_type = isset($this->data['promo_type']) ? $this->data['promo_type'] : '';

		try {

			$merchant_id = CCart::getMerchantId($cart_uuid);
			CCart::deleteAttributesAll($cart_uuid, CCart::CONDITION_RM);
			$this->code = 1;
			$this->msg = "ok";
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionapplyPromoCode()
	{

		$promo_code = isset($this->data['promo_code']) ? CommonUtility::safeTrim($this->data['promo_code']) : '';
		$cart_uuid = isset($this->data['cart_uuid']) ? $this->data['cart_uuid'] : '';
		try {

			if (empty($promo_code)) {
				$this->msg = t("Promo code is required");
				$this->responseJson();
			}

			$merchant_id = CCart::getMerchantId($cart_uuid);
			CCart::getContent($cart_uuid, Yii::app()->language);
			$subtotal = CCart::getSubTotal();
			$sub_total = floatval($subtotal['sub_total']);
			$now = date("Y-m-d");

			$model = AR_voucher::model()->find(
				'voucher_name=:voucher_name',
				array(':voucher_name' => $promo_code)
			);
			if ($model) {

				$promo_id = $model->voucher_id;
				$voucher_owner = $model->voucher_owner;
				$promo_type = 'voucher';

				$resp = CPromos::applyVoucher($merchant_id, $promo_id, Yii::app()->user->id, $now, $sub_total);
				$less_amount = $resp['less_amount'];

				$params = array(
					'name' => "less voucher",
					'type' => $promo_type,
					'id' => $promo_id,
					'target' => 'subtotal',
					'value' => "-$less_amount",
					'voucher_owner' => $voucher_owner,
				);

				CCart::savedAttributes($cart_uuid, 'promo', json_encode($params));
				CCart::savedAttributes($cart_uuid, 'promo_type', $promo_type);
				CCart::savedAttributes($cart_uuid, 'promo_id', $promo_id);

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

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled']) ? Yii::app()->params['settings']['multicurrency_enabled'] : false;
			$multicurrency_enabled = $multicurrency_enabled == 1 ? true : false;

			$exchange_rate = 1;

			$merchant_id = CCart::getMerchantId($cart_uuid);

			//$options_merchant = OptionsTools::find(['merchant_default_currency'],$merchant_id);
			$options_merchant = OptionsTools::find(['merchant_enabled_tip', 'tips_in_transactions', 'merchant_tip_type', 'merchant_default_currency'], $merchant_id);
			$tip_type = isset($options_merchant['merchant_tip_type']) ? $options_merchant['merchant_tip_type'] : 'fixed';
			$merchant_default_currency = isset($options_merchant['merchant_default_currency']) ? $options_merchant['merchant_default_currency'] : '';
			$merchant_default_currency = !empty($merchant_default_currency) ? $merchant_default_currency : $base_currency;

			$currency_code = !empty($currency_code) ? $currency_code : (empty($merchant_default_currency) ? $base_currency : $merchant_default_currency);

			if (!empty($currency_code) && $multicurrency_enabled) {
				Price_Formatter::init($currency_code);
				if ($currency_code != $merchant_default_currency) {
					$exchange_rate = CMulticurrency::getExchangeRate($merchant_default_currency, $currency_code);
				}
			}

			$data = CTips::data('label', $tip_type, $exchange_rate);

			$tips = 0;
			$transaction_type = '';
			if ($resp = CCart::getAttributesAll($cart_uuid, array('tips', 'transaction_type'))) {
				$tips = isset($resp['tips']) ? floatval($resp['tips']) : 0;
				$transaction_type = isset($resp['transaction_type']) ? $resp['transaction_type'] : '';
			}

			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(
				'transaction_type' => $transaction_type,
				'tips' => $tips,
				'data' => $data
			);
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actioncheckoutAddTips()
	{
		try {

			$cart_uuid = isset($this->data['cart_uuid']) ? $this->data['cart_uuid'] : '';
			$value = isset($this->data['value']) ? floatval($this->data['value']) : 0;
			$is_manual = isset($this->data['is_manual']) ? CommonUtility::safeTrim($this->data['is_manual']) : false;
			$is_manual = $is_manual == 1 ? true : false;

			$currency_code = isset($this->data['currency_code']) ? CommonUtility::safeTrim($this->data['currency_code']) : '';
			$base_currency = Price_Formatter::$number_format['currency_code'];

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled']) ? Yii::app()->params['settings']['multicurrency_enabled'] : false;
			$multicurrency_enabled = $multicurrency_enabled == 1 ? true : false;
			$exchange_rate = 1;

			$merchant_id = CCart::getMerchantId($cart_uuid);
			$options_data = OptionsTools::find(['merchant_enabled_tip', 'merchant_tip_type'], $merchant_id);
			$enabled_tip = isset($options_data['merchant_enabled_tip']) ? $options_data['merchant_enabled_tip'] : false;
			$tip_type = isset($options_data['merchant_tip_type']) ? $options_data['merchant_tip_type'] : 'fixed';

			$options_merchant = OptionsTools::find(['merchant_default_currency'], $merchant_id);
			$merchant_default_currency = isset($options_merchant['merchant_default_currency']) ? $options_merchant['merchant_default_currency'] : '';
			$merchant_default_currency = !empty($merchant_default_currency) ? $merchant_default_currency : $base_currency;

			$currency_code = !empty($currency_code) ? $currency_code : (empty($merchant_default_currency) ? $base_currency : $merchant_default_currency);

			// SET CURRENCY
			if (!empty($currency_code) && $multicurrency_enabled) {
				if ($currency_code != $merchant_default_currency) {
					$exchange_rate = CMulticurrency::getExchangeRate($currency_code, $merchant_default_currency);
				}
			}

			if ($tip_type == "percentage" && $enabled_tip == 1 && $is_manual == false) {
				$distance = 0;
				$unit = isset(Yii::app()->params['settings']['home_search_unit_type']) ? Yii::app()->params['settings']['home_search_unit_type'] : 'mi';
				$error = array();
				$minimum_order = 0;
				$maximum_order = 0;
				$merchant_info = array();
				$delivery_fee = 0;
				$distance_covered = 0;
				$merchant_lat = '';
				$merchant_lng = '';
				$out_of_range = false;
				$address_component = array();
				$payload = ['subtotal'];
				try {
					require_once 'get-cart.php';
					$subtotal = $data['subtotal']['raw'];
					$value = ($value / 100) * $subtotal;
				} catch (Exception $e) {
					$this->msg = $e->getMessage();
				}
			} else if ($is_manual == true) {
				$value = $value * $exchange_rate;
			}

			if ($enabled_tip) {
				CCart::savedAttributes($cart_uuid, 'tips', $value);
				$this->code = 1;
				$this->msg = "OK";
				$this->details = array(
					'tips' => $value,
				);
			} else $this->msg = t("Tip are disabled");
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

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled']) ? Yii::app()->params['settings']['multicurrency_enabled'] : false;
			$multicurrency_enabled = $multicurrency_enabled == 1 ? true : false;
			$enabled_hide_payment = isset(Yii::app()->params['settings']['multicurrency_enabled_hide_payment']) ? Yii::app()->params['settings']['multicurrency_enabled_hide_payment'] : false;
			$hide_payment = $multicurrency_enabled == true ? ($enabled_hide_payment == 1 ? true : false) : false;

			$currency_code = Yii::app()->input->post('currency_code');
			$base_currency = Price_Formatter::$number_format['currency_code'];

			$data_merchant = CCart::getMerchantForCredentials($cart_uuid);
			$merchant_id = isset($data_merchant['merchant_id']) ? $data_merchant['merchant_id'] : 0;

			if ($multicurrency_enabled) {
				$options_merchant = OptionsTools::find(['merchant_default_currency'], $merchant_id);
				$merchant_default_currency = isset($options_merchant['merchant_default_currency']) ? $options_merchant['merchant_default_currency'] : '';
				$merchant_default_currency = !empty($merchant_default_currency) ? $merchant_default_currency : $base_currency;
				$currency_code = !empty($currency_code) ? $currency_code : (empty($merchant_default_currency) ? $base_currency : $merchant_default_currency);
			}

			if ($data_merchant['merchant_type'] == 2) {
				$merchant_id = 0;
			}

			$payments_credentials = CPayments::getPaymentCredentialsPublic($merchant_id, '', $data_merchant['merchant_type']);

			$available_payment = [];
			$data = CPayments::SavedPaymentList(Yii::app()->user->id, $data_merchant['merchant_type'], $data_merchant['merchant_id'], $hide_payment, $currency_code);
			foreach ($data as $items) {
				$available_payment[] = $items['payment_code'];
			}

			$model = AR_client_payment_method::model()->find(
				'client_id=:client_id AND as_default=:as_default AND merchant_id=:merchant_id ',
				array(
					':client_id' => Yii::app()->user->id,
					':as_default' => 1,
					':merchant_id' => $merchant_id
				)
			);
			if ($model) {
				$hide_payment_list = [];
				if ($hide_payment) {
					try {
						$hide_payment_list = CMulticurrency::getHidePaymentList($currency_code);
						if (!in_array($model->payment_code, (array)$hide_payment_list)) {
							$default_payment_uuid = $model->payment_uuid;
						}
					} catch (Exception $e) {
						$default_payment_uuid = $model->payment_uuid;
					}
				} else $default_payment_uuid = $model->payment_uuid;

				if (!in_array($model->payment_code, (array)$available_payment)) {
					$default_payment_uuid = '';
				}
			}

			$this->code = 1;
			$this->msg = "ok";
			$this->details = array(
				'default_payment_uuid' => $default_payment_uuid,
				'data' => $data,
				'credentials' => $payments_credentials
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
			$data = CPayments::PaymentList($merchant_id, true);

			$merchants = CMerchantListingV1::getMerchant($merchant_id);
			$payments_credentials = CPayments::getPaymentCredentialsPublic($merchant_id, '', $merchants->merchant_type);

			$this->code = 1;
			$this->msg = "ok";
			$this->details = array(
				'data' => $data,
				'credentials' => $payments_credentials
			);
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiondeleteSavedPaymentMethod()
	{
		try {
			$payment_uuid = isset($this->data['payment_uuid']) ? $this->data['payment_uuid'] : '';
			$payment_code = isset($this->data['payment_code']) ? $this->data['payment_code'] : '';
			CPayments::delete(Yii::app()->user->id, $payment_uuid);
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
			$model = AR_client_payment_method::model()->find(
				'client_id=:client_id AND payment_uuid=:payment_uuid',
				array(
					':client_id' => Yii::app()->user->id,
					':payment_uuid' => $payment_uuid
				)
			);
			if ($model) {
				$model->as_default = 1;
				$model->save();
				$this->code = 1;
				$this->msg = t("Succesful");
			} else $this->msg = t("Record not found");
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}

		$this->responseJson();
	}

	public function actionSavedPaymentProvider()
	{
		try {

			$payment_code = $this->data['payment_code'] ?? null;
			$merchant_id = $this->data['merchant_id'] ?? null;

			if (!$payment_code) {
				$this->msg = t("Invalid payment method");
				$this->responseJson();
			}

			$payment = AR_payment_gateway::model()->find(
				'payment_code=:payment_code',
				array(':payment_code' => $payment_code)
			);

			if ($payment) {
				$model = new AR_client_payment_method;
				$model->scenario = "insert";
				$model->client_id = Yii::app()->user->id;
				$model->payment_code = $payment_code;
				$model->as_default = intval(1);
				$model->attr1 = $payment ? $payment->payment_name : 'unknown';
				$model->merchant_id = intval($merchant_id);
				if ($model->save()) {
					$this->code = 1;
					$this->msg = t("Succesful");
				} else $this->msg = CommonUtility::parseError($model->getErrors());
			} else $this->msg[] = t("Payment provider not found");
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionsavedCards()
	{
		try {

			$expiration_month = '';
			$expiration_yr = '';
			$error_data = array();
			$error = array();
			$card_name = isset($this->data['card_name']) ? $this->data['card_name'] : '';
			$credit_card_number = isset($this->data['credit_card_number']) ? $this->data['credit_card_number'] : '';
			$expiry_date = isset($this->data['expiry_date']) ? $this->data['expiry_date'] : '';
			$cvv = isset($this->data['cvv']) ? $this->data['cvv'] : '';
			$billing_address = isset($this->data['billing_address']) ? $this->data['billing_address'] : '';
			$payment_code = isset($this->data['payment_code']) ? $this->data['payment_code'] : '';
			$card_uuid = isset($this->data['card_uuid']) ? $this->data['card_uuid'] : '';
			$merchant_id = isset($this->data['merchant_id']) ? $this->data['merchant_id'] : '';

			if (empty($card_uuid)) {
				$model = new AR_client_cc;
				$model->scenario = 'add';
			} else {
				$model = AR_client_cc::model()->find(
					'client_id=:client_id AND card_uuid=:card_uuid',
					array(
						':client_id' => Yii::app()->user->id,
						':card_uuid' => $card_uuid
					)
				);
				if (!$model) {
					$this->msg[] = t("Record not found");
					$this->responseJson();
				}
				$model->scenario = 'update';
			}

			$model->client_id = Yii::app()->user->id;
			$model->payment_code = $payment_code;
			$model->card_name = $card_name;
			$model->credit_card_number = str_replace(" ", "", $credit_card_number);
			$model->expiration = $expiry_date;
			$model->cvv = $cvv;
			$model->billing_address = $billing_address;
			$model->merchant_id = $merchant_id;

			if ($model->save()) {
				$this->code = 1;
				$this->msg = "OK";
			} else $this->msg = CommonUtility::parseError($model->getErrors());
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

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled']) ? Yii::app()->params['settings']['multicurrency_enabled'] : false;
			$multicurrency_enabled = $multicurrency_enabled == 1 ? true : false;
			$exchange_rate = 1;

			$options_merchant = OptionsTools::find(['merchant_timezone', 'merchant_default_currency'], $merchant_id);
			$merchant_timezone = isset($options_merchant['merchant_timezone']) ? $options_merchant['merchant_timezone'] : '';
			$merchant_default_currency = isset($options_merchant['merchant_default_currency']) ? $options_merchant['merchant_default_currency'] : '';
			$merchant_default_currency = !empty($merchant_default_currency) ? $merchant_default_currency : $base_currency;
			if (!empty($merchant_timezone)) {
				Yii::app()->timezone = $merchant_timezone;
			}

			$currency_code = !empty($currency_code) ? $currency_code : (empty($merchant_default_currency) ? $base_currency : $merchant_default_currency);

			// SET CURRENCY
			if (!empty($currency_code) && $multicurrency_enabled) {
				Price_Formatter::init($currency_code);
				if ($currency_code != $merchant_default_currency) {
					$exchange_rate = CMulticurrency::getExchangeRate($merchant_default_currency, $currency_code);
				}
			}

			CMerchantMenu::setExchangeRate($exchange_rate);

			$items_not_available = CMerchantMenu::getItemAvailability($merchant_id, date("w"), date("H:h:i"));
			$category_not_available = CMerchantMenu::getCategoryAvailability($merchant_id, date("w"), date("H:h:i"));

			$items = CMerchantMenu::getSimilarItems(intval($merchant_id), Yii::app()->language, 100, '', $items_not_available, $category_not_available);
			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(
				'data' => $items
			);
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionPlaceOrder()
	{

		$setttings = Yii::app()->params['settings'];
		$home_search_mode = $setttings['home_search_mode'] ?? 'address';
		$location_searchtype = $setttings['location_searchtype'] ?? '';		
		$strict_to_wallet = !empty($setttings['strict_to_wallet']) && !empty($setttings['digitalwallet_enabled']);

		$local_id = isset($this->data['local_id']) ? $this->data['local_id'] : '';
		$cart_uuid = isset($this->data['cart_uuid']) ? CommonUtility::safeTrim($this->data['cart_uuid']) : '';
		$payment_uuid = isset($this->data['payment_uuid']) ? CommonUtility::safeTrim($this->data['payment_uuid']) : '';
		$payment_change = isset($this->data['payment_change']) ? floatval($this->data['payment_change']) : 0;
		$currency_code = isset($this->data['currency_code']) ? CommonUtility::safeTrim($this->data['currency_code']) : '';
		$base_currency = Price_Formatter::$number_format['currency_code'];

		$use_digital_wallet = isset($this->data['use_digital_wallet']) ? intval($this->data['use_digital_wallet']) : false;
		$use_digital_wallet = $use_digital_wallet == 1 ? true : false;

		if($strict_to_wallet){
			$use_digital_wallet = true;
		}		

		$room_uuid = isset($this->data['room_uuid']) ? CommonUtility::safeTrim($this->data['room_uuid']) : '';
		$table_uuid = isset($this->data['table_uuid']) ? CommonUtility::safeTrim($this->data['table_uuid']) : '';
		$guest_number = isset($this->data['guest_number']) ? intval($this->data['guest_number']) : 0;

		$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled']) ? Yii::app()->params['settings']['multicurrency_enabled'] : false;
		$multicurrency_enabled = $multicurrency_enabled == 1 ? true : false;
		$enabled_checkout_currency = isset(Yii::app()->params['settings']['multicurrency_enabled_checkout_currency']) ? Yii::app()->params['settings']['multicurrency_enabled_checkout_currency'] : false;
		$enabled_force = $multicurrency_enabled == true ? ($enabled_checkout_currency == 1 ? true : false) : false;

		$address_uuid = isset($this->data['address_uuid']) ? CommonUtility::safeTrim($this->data['address_uuid']) : '';

		$return_url = $this->data['return_url'] ?? null;
		$return_url =  rtrim($return_url, "/");		

		$payload = array(
			'items',
			'merchant_info',
			'service_fee',
			'delivery_fee',
			'packaging',
			'tax',
			'tips',
			'checkout',
			'discount',
			'distance_local_new',
			'summary',
			'total',
			'card_fee',
			'points',
			'points_discount'
		);

		$unit = Yii::app()->params['settings']['home_search_unit_type'];
		$distance = 0;
		$error = array();
		$minimum_order = 0;
		$maximum_order = 0;
		$merchant_info = array();
		$delivery_fee = 0;
		$distance_covered = 0;
		$merchant_lat = '';
		$merchant_lng = '';
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
		$points_earned = 0;
		$sub_total_without_cnd = 0;
		$booking_enabled = false;

		$digital_wallet_balance = 0;
		$amount_due = 0;
		$wallet_use_amount = 0;
		$use_partial_payment = false;
		$free_delivery_on_first_order = false;
		$preparation_time_estimation = 0;
		$delivery_time_estimation = 0;

		// ADD payment
		$payment_method = $this->data['payment_method'] ?? null;
		$payment_method_merchant_id = $this->data['payment_method_merchant_id'] ?? 0;
		$allowed_payment = ['cod','razorpay','bank','paypal'];
		if(empty($payment_uuid) && !empty($payment_method) && in_array($payment_method,$allowed_payment)){
			$payment = AR_payment_gateway::model()->find(
				'payment_code=:payment_code',
				array(':payment_code' => $payment_method)
			);
			if ($payment) {
				$payment_find = AR_client_payment_method::model()->find("payment_code=:payment_code AND client_id=:client_id AND merchant_id=:merchant_id",[
					':payment_code'=>$payment_method,
					':client_id'=>Yii::app()->user->id,
					':merchant_id'=>$payment_method_merchant_id
				]);
				if($payment_find){
					$payment_uuid = $payment_find->payment_uuid;
			    } else {
					$model = new AR_client_payment_method;
					$model->scenario = "insert";
					$model->client_id = Yii::app()->user->id;
					$model->payment_code = $payment_method;
					$model->as_default = intval(1);
					$model->attr1 = $payment ? $payment->payment_name : 'unknown';
					$model->merchant_id = intval($payment_method_merchant_id);
					if ($model->save()) {
						$payment_uuid = $model->payment_uuid;
					}
				}
			}
		} else if ( empty($payment_uuid)){
			$this->msg = t("Please select payment method");
			$this->responseJson();
		}

		/*CHECK IF MERCHANT IS OPEN*/
		try {

			$merchant_id = CCart::getMerchantId($cart_uuid);

			// CHECK IF MERCHANT HAS DIFFERENT TIMEZONE
			$options_merchant = OptionsTools::find(['merchant_timezone', 'booking_enabled', 'free_delivery_on_first_order'], $merchant_id);
			$merchant_timezone = $options_merchant['merchant_timezone'] ?? '';
			$booking_enabled = $options_merchant['booking_enabled'] ?? '';
			$booking_enabled = $booking_enabled == 1 ? true : false;

			$free_delivery_on_first_order = $options_merchant['free_delivery_on_first_order'] ?? false;
			$free_delivery_on_first_order = $free_delivery_on_first_order == 1 ? true : false;

			if (!empty($merchant_timezone)) {
				Yii::app()->timezone = $merchant_timezone;
			}

			$date = date("Y-m-d");
			$time_now = date("H:i");

			$atts_delivery = CCart::getAttributesAll($cart_uuid, array('whento_deliver', 'delivery_date', 'delivery_time'));
			$whento_deliver = $atts_delivery['whento_deliver'] ?? '';
			if ($whento_deliver == "schedule") {
				$date = $atts_delivery['delivery_date'] ?? $date;
				$time_now = isset($atts_delivery['delivery_time']) ? CCheckout::jsonTimeToSingleTime($atts_delivery['delivery_time']) : $time_now;
			}

			$datetime_to = date("Y-m-d g:i:s a", strtotime("$date $time_now"));
			CMerchantListingV1::checkCurrentTime(date("Y-m-d g:i:s a"), $datetime_to);

			$resp = CMerchantListingV1::checkStoreOpen($merchant_id, $date, $time_now);
			if ($resp['merchant_open_status'] <= 0) {
				$this->msg[] = t("This store is close right now, but you can schedulean order later.");
				$this->responseJson();
			}

			CMerchantListingV1::storeAvailableByID($merchant_id);
		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());
			$this->responseJson();
		}

		try {

			if ($credentials = CMerchants::MapsConfig(Yii::app()->merchant->id)) {
				MapSdk::$map_provider = $credentials['provider'];
				MapSdk::setKeys(array(
					'google.maps' => $credentials['key'],
					'mapbox' => $credentials['key'],
				));
			}

			$merchant_id = CCart::getMerchantId($cart_uuid);
			$options_merchant = OptionsTools::find(['merchant_timezone', 'merchant_default_currency'], $merchant_id);
			$merchant_default_currency = isset($options_merchant['merchant_default_currency']) ? $options_merchant['merchant_default_currency'] : '';
			$merchant_default_currency = !empty($merchant_default_currency) ? $merchant_default_currency : $base_currency;
			$currency_code = !empty($currency_code) ? $currency_code : (empty($merchant_default_currency) ? $base_currency : $merchant_default_currency);

			$points_enabled = isset(Yii::app()->params['settings']['points_enabled']) ? Yii::app()->params['settings']['points_enabled'] : false;
			$points_enabled = $points_enabled == 1 ? true : false;
			$points_earning_rule = isset(Yii::app()->params['settings']['points_earning_rule']) ? Yii::app()->params['settings']['points_earning_rule'] : 'sub_total';

			$points_earning_points = isset(Yii::app()->params['settings']['points_earning_points']) ? Yii::app()->params['settings']['points_earning_points'] : 0;
			$points_minimum_purchase = isset(Yii::app()->params['settings']['points_minimum_purchase']) ? Yii::app()->params['settings']['points_minimum_purchase'] : 0;
			$points_maximum_purchase = isset(Yii::app()->params['settings']['points_maximum_purchase']) ? Yii::app()->params['settings']['points_maximum_purchase'] : 0;

			$whatsapp_use_api = isset(Yii::app()->params['settings']['whatsapp_use_api']) ? Yii::app()->params['settings']['whatsapp_use_api'] : false;
			$whatsapp_use_api = $whatsapp_use_api == 1 ? true : false;

			CCart::setExchangeRate($exchange_rate);
			CCart::setPointsRate($points_enabled, $points_earning_rule, $points_earning_points, $points_minimum_purchase, $points_maximum_purchase);

			if ($multicurrency_enabled) {
				if ($merchant_default_currency != $currency_code) {
					$exchange_rate_base_customer = CMulticurrency::getExchangeRate($merchant_default_currency, $currency_code);
					$payment_exchange_rate = CMulticurrency::getExchangeRate($currency_code, $merchant_default_currency);
				}
				if ($merchant_default_currency != $base_currency) {
					$exchange_rate_merchant_to_admin = CMulticurrency::getExchangeRate($merchant_default_currency, $base_currency);
					$exchange_rate_admin_to_merchant = CMulticurrency::getExchangeRate($base_currency, $merchant_default_currency);
				}
				if ($base_currency != $merchant_default_currency) {
					$exchange_rate_use_currency_to_admin = CMulticurrency::getExchangeRate($merchant_default_currency, $base_currency);
				}
			} else {
				$merchant_default_currency = $base_currency;
				$currency_code = $base_currency;
			}

			CCart::setAdminExchangeRate($exchange_rate_use_currency_to_admin);

			// GET CLIENT ADDRESS AND SAVE LOCATION NAME / DELIVERY OPTIONS AND INSTRUCSTIONS
			if ($home_search_mode == "location") {
				try {
					$address_component = Clocations::getAddressDetails(Yii::app()->user->id, $address_uuid);
					$location = [
						'state_id' => $address_component['state_id'] ?? '',
						'city_id' => $address_component['city_id'] ?? '',
						'area_id' => $address_component['area_id'] ?? '',
						'zip_code' => $address_component['zip_code'] ?? '',
					];
				} catch (Exception $e) {
				}
			} else {
				$client_address = AR_client_address::model()->find('address_uuid=:address_uuid AND client_id=:client_id', [
					':address_uuid' => $address_uuid,
					':client_id' => Yii::app()->user->id
				]);
				if(!$client_address){
					$client_address = AR_client_address::model()->find('place_id=:place_id AND client_id=:client_id',
		            array(':place_id'=>$local_id,'client_id'=>Yii::app()->user->id));
				}
				if ($client_address) {
					$latitude = $client_address->latitude;
					$longitude = $client_address->longitude;

					$address_format_use = Yii::app()->params['settings']['address_format_use'] ?? '';
					$address_format_use = !empty($address_format_use) ? $address_format_use : 1;

					$address_component['latitude'] = $client_address->latitude;
					$address_component['longitude'] = $client_address->longitude;
					$address_component['formatted_address'] = $client_address->formatted_address;
					$address_component['formattedAddress'] = $client_address->formattedAddress;
					$address_component['address1'] = $client_address->address1;
					$address_component['address2'] = $client_address->address2;
					$address_component['country_code'] = $client_address->country_code;

					$address_component['location_name']	 = $client_address->location_name;
					$address_component['delivery_options']	 = $client_address->delivery_options;
					$address_component['delivery_instructions']	 = $client_address->delivery_instructions;
					$address_component['address_label']	 = $client_address->address_label;
					$address_component['postal_code']	 = $client_address->postal_code;
					$address_component['company']	 = $client_address->company;
					$address_component['address_format_use'] =  $address_format_use;
					$address_component['custom_field1'] =  $client_address->custom_field1;
					$address_component['custom_field2'] =  $client_address->custom_field2;
				}
			}

			require_once 'get-cart.php';

			// DIGITAL WALLET
			try {
				if ($use_digital_wallet) {
					$digital_wallet_balance = CDigitalWallet::getAvailableBalance(Yii::app()->user->id);
					$digital_wallet_balance = $digital_wallet_balance * $exchange_rate_admin_to_merchant;

					if($strict_to_wallet){
						$amount_due = CDigitalWallet::calculateAmountDue($total,$digital_wallet_balance);
						if($amount_due>0){
							$this->msg[] = t("You don't have enough balance in your wallet to place this order.");
				            $this->responseJson();
						}
						$wallet_use_amount = $total;
					} else {
						$amount_due = CDigitalWallet::canContinueWithWallet($total, $digital_wallet_balance, $payment_uuid);
						if ($amount_due > 0) {
							$wallet_use_amount = $digital_wallet_balance;
							$use_partial_payment = true;
						} else {
							$wallet_use_amount = $total;
						}
					}					
				}
			} catch (Exception $e) {
				$this->msg[] = t($e->getMessage());
				$this->responseJson();
			}


			$include_utensils = isset($this->data['include_utensils']) ? $this->data['include_utensils'] : false;
			$include_utensils = $include_utensils == 1 ? true : false;
			CCart::savedAttributes($cart_uuid, 'include_utensils', $include_utensils);

			if (is_array($error) && count($error) >= 1) {
				$this->msg = $error;
			} else {

				$merchant_type = $data['merchant']['merchant_type'];
				$commision_type = $data['merchant']['commision_type'];
				$merchant_commission = $data['merchant']['commission'];

				$sub_total_based  = CCart::getSubTotal_TobeCommission();
				$tax_total =  CCart::getTotalTax();
				$resp_comm = CCommission::getCommissionValueNew([
					'merchant_id' => $merchant_id,
					'transaction_type' => $transaction_type,
					'merchant_type' => $merchant_type,
					'commision_type' => $commision_type,
					'merchant_commission' => $merchant_commission,
					'sub_total' => $sub_total_based,
					'sub_total_without_cnd' => $sub_total_without_cnd,
					'total' => $total,
					'service_fee' => $service_fee,
					'delivery_fee' => $delivery_fee,
					'tax_settings' => $tax_settings,
					'tax_total' => $tax_total,
					'self_delivery' => $self_delivery,
				]);
				if ($resp_comm) {
					$commission_based = $resp_comm['commission_based'];
					$commission = $resp_comm['commission'];
					$merchant_earning = $resp_comm['merchant_earning'];
					$merchant_commission = $resp_comm['commission_value'];
				}

				$atts = CCart::getAttributesAll($cart_uuid, array(
					'whento_deliver',
					'promo',
					'promo_type',
					'promo_id',
					'tips',
					'delivery_date',
					'delivery_time'
				));


				if ($use_digital_wallet && !$use_partial_payment) {
					$payments = ['payment_code' => CDigitalWallet::transactionName()];
				} else $payments = CPayments::getPaymentMethod($payment_uuid, Yii::app()->user->id);

				
				$sub_total_less_discount  = CCart::getSubTotal_lessDiscount();

				if (is_array($summary) && count($summary) >= 1) {
					foreach ($summary as $summary_item) {
						switch ($summary_item['type']) {
							case "voucher":
								$total_discount = CCart::cleanNumber($summary_item['raw']);
								break;

							case "offers":
								$total_discount = CCart::cleanNumber($summary_item['raw']);
								$offer_total = $total_discount;
								$total_discount = floatval($total_discount) + floatval($total_discount);
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
								$total_tax += CCart::cleanNumber($summary_item['raw']);
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

				if ($tax_enabled) {
					$tax_type = CCart::getTaxType();
					$tax_condition = CCart::getTaxCondition();
					if ($tax_type == "standard" || $tax_type == "euro") {
						if (is_array($tax_condition) && count($tax_condition) >= 1) {
							foreach ($tax_condition as $tax_item_cond) {
								$tax = isset($tax_item_cond['tax_rate']) ? $tax_item_cond['tax_rate'] : 0;
							}
						}
					}
				}

				if ($multicurrency_enabled) {
					$payment_change = $currency_code == $merchant_default_currency ? $payment_change : ($payment_change * $payment_exchange_rate);
				}

				$model = new AR_ordernew;
				$model->scenario = $transaction_type;
				$model->order_uuid = CommonUtility::generateUIID();
				$model->merchant_id = intval($merchant_id);
				$model->client_id = intval(Yii::app()->user->id);
				$model->service_code = $transaction_type;
				$model->payment_code = isset($payments['payment_code']) ? $payments['payment_code'] : '';
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
				$model->amount_due = floatval($amount_due);
				$model->wallet_amount = floatval($wallet_use_amount);

				$model->booking_enabled = $booking_enabled;
				$model->room_id = CommonUtility::safeTrim($room_uuid);
				$model->table_id = CommonUtility::safeTrim($table_uuid);
				$model->guest_number = $guest_number;

				if (is_array($promo_details) && count($promo_details) >= 1) {
					if ($promo_details['promo_type'] == "voucher") {
						$model->promo_code = $promo_details['voucher_name'];
						$model->promo_total = $promo_details['less_amount'];
						$model->promo_owner = $promo_details['voucher_owner'];
					} elseif ($promo_details['promo_type'] == "offers") {
						$offer_cap = $promo_details['max_discount_cap'] ?? 0;
						$model->offer_discount = $promo_details['less_amount'];
						$model->offer_total = floatval($offer_total);
						$model->offer_cap = floatval($offer_cap);
					}
				}

				$delivery_date_time = null;
				$model->whento_deliver = $atts['whento_deliver'] ?? '';
				if ($model->whento_deliver == "now") {
					$model->delivery_date = CommonUtility::dateNow();
					$delivery_date_time = CommonUtility::dateNow();
				} else {
					$model->delivery_date = $atts['delivery_date'] ?? '';
					$model->delivery_time = isset($atts['delivery_time']) ? CCheckout::jsonTimeToSingleTime($atts['delivery_time']) : '';
					$model->delivery_time_end = isset($atts['delivery_time']) ? CCheckout::jsonTimeToSingleTime($atts['delivery_time'], 'end_time') : '';
					$delivery_date_time = "$model->delivery_date $model->delivery_time";
				}
				$model->delivery_date_time = $delivery_date_time;

				$model->commission_type = $commision_type;
				$model->commission_value = $merchant_commission;
				$model->commission_based = $commission_based;
				$model->commission = floatval($commission);
				$model->commission_original = floatval($commission);
				$model->merchant_earning = floatval($merchant_earning);
				$model->merchant_earning_original = floatval($merchant_earning);
				$model->formatted_address = $address_component['formatted_address'] ?? '';

				$metas = CCart::getAttributesAll(
					$cart_uuid,
					array(
						'promo',
						'promo_type',
						'promo_id',
						'tips',
						'cash_change',
						'customer_name',
						'contact_number',
						'contact_email',
						'include_utensils',
						'point_discount'
					)
				);


				$metas['payment_change'] = floatval($payment_change);
				$metas['self_delivery'] = $self_delivery == true ? 1 : 0;
				$metas['points_to_earn'] = floatval($points_to_earn);
				$metas['timezone'] = Yii::app()->timezone;

				if ($transaction_type == "dinein" && $booking_enabled) {
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

				$metas['cart_uuid'] = $cart_uuid;

				if($home_search_mode=="address"){
					$metas['order_version'] = 1;
				}

				/*LINE ITEMS*/
				$model->items = $data['items'] ?? '';
				$model->meta = $metas;
				$model->address_component = $address_component;
				$model->cart_uuid = $cart_uuid;

				$model->tax_use = $tax_settings;
				$model->tax_for_delivery = $tax_delivery;
				$model->payment_uuid  = $payment_uuid;

				$model->base_currency_code = $merchant_default_currency;
				$model->use_currency_code = $currency_code;
				$model->admin_base_currency = $base_currency;

				$model->exchange_rate = floatval($exchange_rate_base_customer);
				$model->exchange_rate_use_currency_to_admin = floatval($exchange_rate_use_currency_to_admin);
				$model->exchange_rate_merchant_to_admin = floatval($exchange_rate_merchant_to_admin);
				$model->exchange_rate_admin_to_merchant = floatval($exchange_rate_admin_to_merchant);

				$model->preparation_time_estimation = $preparation_time_estimation;
				$model->delivery_time_estimation = $delivery_time_estimation;

				$model->request_from = "mobile";

				if ($model->save()) {

					$redirect = Yii::app()->createAbsoluteUrl("orders/index", array(
						'order_uuid' => $model->order_uuid
					));

					/*EXECUTE MODULES*/
					$payment_instructions = Yii::app()->getModule($model->payment_code)->paymentInstructions();
					if ($payment_instructions['method'] == "offline") {
						Yii::app()->getModule($model->payment_code)->savedTransaction($model);
					}

					$order_bw = OptionsTools::find(array('bwusit'));
					$order_bw = isset($order_bw['bwusit']) ? $order_bw['bwusit'] : 0;

					if ($model->amount_due > 0) {
						$total = Price_Formatter::convertToRaw($model->amount_due);
					} else $total = Price_Formatter::convertToRaw($model->total);

					$use_currency_code = $model->use_currency_code;
					$total_exchange = floatval(Price_Formatter::convertToRaw(($total * $exchange_rate_base_customer)));
					if ($enabled_force) {
						if ($force_result = CMulticurrency::getForceCheckoutCurrency($model->payment_code, $use_currency_code)) {
							$use_currency_code = $force_result['to_currency'];
							$total_exchange = Price_Formatter::convertToRaw($total_exchange * $force_result['exchange_rate'], 2);
						}
					}

					$this->code = 1;
					$this->msg = t("Your Order has been place");

					$payment_url = CommonUtility::getHomebaseUrl() . "/$model->payment_code/api/createcheckout?" . http_build_query([
						'order_uuid' => $model->order_uuid,
						'cart_uuid' => $cart_uuid,
						'payment_uuid' => $payment_uuid,
						'request_from' => "app",
						'return_url' => $return_url,
					]);

					$this->details = array(
						'order_uuid' => $model->order_uuid,
						'cart_uuid' => $cart_uuid,
						'redirect' => $redirect,
						'payment_code' => $model->payment_code,
						'payment_uuid' => $payment_uuid,
						'payment_instructions' => $payment_instructions,
						'order_bw' => $order_bw,
						'total' => floatval($model->total),
						'currency' => $model->use_currency_code,
						'payment_url' => $payment_url,
						'force_payment_data' => [
							'enabled_force' => $enabled_force,
							'use_currency_code' => $use_currency_code,
							'total_exchange' => $total_exchange,
							'reference_id' => $model->order_uuid
						],
					);
				} else {
					$this->msg = CommonUtility::parseModelErrorToString($model->getErrors(),'<br/>');
					// if ($error = CommonUtility::parseError($model->getErrors())) {
					// 	$this->msg = $error;
					// } else $this->msg = array('invalid error');
				}
			}
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetOrder()
	{
		try {

			$order_uuid = Yii::app()->input->post('order_uuid');
			$merchant_id = COrders::getMerchantId($order_uuid);
			$merchant_info = COrders::getMerchant($merchant_id, Yii::app()->language);

			COrders::getContent($order_uuid);
			$items = COrders::getItemsOnly();
			$meta  = COrders::orderMeta();
			$order_id = COrders::getOrderID();
			$items_count = COrders::itemCount($order_id);
			$progress = CTrackingOrder::getProgress($order_uuid, date("Y-m-d g:i:s a"));
			$order_info  = COrders::orderInfo(Yii::app()->language, date("Y-m-d"));
			$order_info  = isset($order_info['order_info']) ? $order_info['order_info'] : '';
			$order_type = isset($order_info['order_type']) ? $order_info['order_type'] : '';

			$subtotal = COrders::getSubTotal();
			$subtotal = isset($subtotal['sub_total']) ? $subtotal['sub_total'] : 0;
			$subtotal = Price_Formatter::formatNumber(floatval($subtotal));
			$order_info['sub_total'] = $subtotal;

			$summary = COrders::getSummary();
		    $order_info['summary'] = $summary;

			$instructions = CTrackingOrder::getInstructions($merchant_id, $order_type);
			$favorites = COrders::MerchantFavorites();

			// START WHATSAPP
			$options = OptionsTools::find(['merchant_enabled_whatsapp','merchant_whatsapp_phone_number'],$merchant_id);
		    $charge_type = !empty($charge_type)?$charge_type:"fixed";
		    $enabled_whatsapp = $options['merchant_enabled_whatsapp'] ?? false;
		    $enabled_whatsapp = $enabled_whatsapp==1?true:false;
		    $whatsapp_phone_number = $options['merchant_whatsapp_phone_number'] ?? '';
			$whatsapp_confirm_url = null;

			if($enabled_whatsapp && !empty($whatsapp_phone_number)){
			  $items = COrders::getItems();
			  $order_version = $order_id['order_version'] ?? 0;
			  $order_summary = $order_info['summary'] ?? null;

			  if($order_version==1){
				$delivery_address = $order_info['complete_delivery_address'] ?? '';
			    $delivery_address .= "• " . $order_info['address1'] ?? '';
			    $delivery_address .= " " . $order_info['delivery_address'] ?? '';
			  } else $delivery_address = $order_info['complete_delivery_address'] ?? '';

			  $delivery_fee = 0;
			  if(is_array($order_summary) && count($order_summary)>=1){
				foreach ($order_summary as $item) {
					if (isset($item['type']) && $item['type'] === 'delivery_fee') {
						$delivery_fee = $item['value'] ?? 0;
						break;
					}
				}
			  }

			  $whatsapp_confirm_url = CNotifications::toWhatsappFormat([
				'order_id'=>$order_id,
				'customer_name'=>$order_info['customer_name'] ?? '',
				'total'=>$order_info['pretty_total'] ?? '',
				'phone_number'=>$whatsapp_phone_number,
				'transaction_type'=>$order_info['order_type1'] ?? '',
				'service_code'=>$order_info['service_code'] ?? '',
				'delivery_address'=>$delivery_address,
				'delivery_fee'=>$delivery_fee,
				'whento_deliver'=>$order_info['whento_deliver'] ?? '',
				'schedule_at'=>$order_info['schedule_at'] ?? '',
				'items'=>$items,
			  ]);
		   } else $enabled_whatsapp = false;
		   //  END WHATSAPP


			$this->code = 1;
			$this->msg = "Ok";
			$this->details = array(
				'merchant_info' => $merchant_info,
				'order_info' => $order_info,
				'items_count' => $items_count,
				'items' => $items,
				'meta' => $meta,
				'progress' => $progress,
				'instructions' => $instructions,
				'favorites' => $favorites,
				'enabled_whatsapp'=>$enabled_whatsapp,
			    'whatsapp_confirm_url'=>$whatsapp_confirm_url
			);
		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetRealtime()
	{
		$getevent = Yii::app()->input->post('getevent');
		$realtime = AR_admin_meta::getMeta(array(
			'realtime_app_enabled',
			'realtime_provider',
			'webpush_app_enabled',
			'webpush_provider',
			'pusher_key',
			'pusher_cluster'
		));
		$realtime_app_enabled = isset($realtime['realtime_app_enabled']) ? $realtime['realtime_app_enabled']['meta_value'] : '';
		$realtime_provider = isset($realtime['realtime_provider']) ? $realtime['realtime_provider']['meta_value'] : '';
		$pusher_key = isset($realtime['pusher_key']) ? $realtime['pusher_key']['meta_value'] : '';
		$pusher_cluster = isset($realtime['pusher_cluster']) ? $realtime['pusher_cluster']['meta_value'] : '';

		if ($realtime_app_enabled == 1) {
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'realtime_app_enabled' => $realtime_app_enabled,
				'realtime_provider' => $realtime_provider,
				'pusher_key' => $pusher_key,
				'pusher_cluster' => $pusher_cluster,
				'channel' => Yii::app()->user->client_uuid,
				'event' => $getevent == "tracking" ? Yii::app()->params->realtime['event_tracking_order'] : Yii::app()->params->realtime['notification_event']
			];
		} else $this->msg = t("realtime not enabled");
		$this->responseJson();
	}

	public function actionuploadReview()
	{

		$upload_uuid = CommonUtility::generateUIID();
		$merchant_id = Yii::app()->input->post('merchant_id');
		$allowed_extension = explode(",",  Yii::app()->params['upload_type']);
		$maxsize = (int) Yii::app()->params['upload_size'];

		if (!empty($_FILES)) {

			$title = $_FILES['file']['name'];
			$size = (int)$_FILES['file']['size'];
			$filetype = $_FILES['file']['type'];

			if (isset($_FILES['file']['name'])) {
				$extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
			} else $extension = strtolower(substr($title, -3, 3));

			if (!in_array($extension, $allowed_extension)) {
				$this->msg = t("Invalid file extension");
				$this->responseJson();
			}
			if ($size > $maxsize) {
				$this->msg = t("Invalid file size");
				$this->responseJson();
			}

			$upload_path = "upload/reviews";
			$tempFile = $_FILES['file']['tmp_name'];
			$upload_uuid = CommonUtility::createUUID("{{media_files}}", 'upload_uuid');
			$filename = $upload_uuid . ".$extension";
			$path = CommonUtility::uploadDestination($upload_path) . "/" . $filename;

			$image_set_width = isset(Yii::app()->params['settings']['review_image_resize_width']) ? intval(Yii::app()->params['settings']['review_image_resize_width']) : 0;
			$image_set_width = $image_set_width <= 0 ? 300 : $image_set_width;

			$image_driver = !empty(Yii::app()->params['settings']['image_driver']) ? Yii::app()->params['settings']['image_driver'] : Yii::app()->params->image['driver'];
			$manager = new ImageManager(array('driver' => $image_driver));
			$image = $manager->make($tempFile);
			$image_width = $manager->make($tempFile)->width();

			if ($image_width > $image_set_width) {
				$image->resize(null, $image_set_width, function ($constraint) {
					$constraint->aspectRatio();
				});
				$image->save($path);
			} else {
				$image->save($path, 60);
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

			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(
				'url_image' => CMedia::getImage($filename, $upload_path),
				'filename' => $media->filename,
				'id' => $upload_uuid
			);
		} else $this->msg = t("Invalid file");
		$this->responseJson();
	}

	public function actiondeleteMedia()
	{
		try {

			$id =  Yii::app()->request->getPost('id', null);
			if (!$id) {
				$this->msg = t(HELPER_RECORD_NOT_FOUND);
				$this->responseJson();
			}
			$model = AR_media::model()->find("upload_uuid=:upload_uuid", [
				':upload_uuid' => $id
			]);
			if ($model) {
				$model->delete();
				$this->msg = "Ok";
			} else $this->msg = t(HELPER_RECORD_NOT_FOUND);
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionaddReview()
	{
		try {

			$order_uuid = $this->data['order_uuid'] ?? '';
			$order = COrders::get($order_uuid);

			$find = AR_review::model()->find(
				'merchant_id=:merchant_id AND client_id=:client_id
			AND order_id=:order_id',
				array(
					':merchant_id' => intval($order->merchant_id),
					':client_id' => intval(Yii::app()->user->id),
					':order_id' => intval($order->order_id)
				)
			);

			if (!$find) {
				$model = new AR_review;
				$model->merchant_id  = intval($order->merchant_id);
				$model->order_id  = intval($order->order_id);
				$model->client_id = intval(Yii::app()->user->id);
				$model->review  = isset($this->data['review_content']) ? $this->data['review_content'] : '';
				$model->rating  = isset($this->data['rating_value']) ? (int)$this->data['rating_value'] : 0;
				$model->date_created = CommonUtility::dateNow();
				$model->ip_address = CommonUtility::userIp();
				$model->as_anonymous = isset($this->data['as_anonymous']) ? (int)$this->data['as_anonymous'] : 0;
				$model->scenario = 'insert';
				if ($model->save()) {
					$this->code = 1;
					$this->msg = t("Review has been added. Thank you.");
					CReviews::insertMeta($model->id, 'tags_like', $this->data['tags_like']);
					CReviews::insertMeta($model->id, 'tags_not_like', $this->data['tags_not_like']);
					CReviews::insertMetaImages($model->id, 'upload_images', $this->data['upload_images']);
				} else {
					if ($error = CommonUtility::parseError($model->getErrors())) {
						$this->msg = $error;
					} else $this->msg[] = array('invalid error');
				}
			} else $this->msg[] = t("You already added review for this order");
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionsubmitReview()
	{
		try {

			$order_uuid = $this->data['order_uuid'] ?? null;
			$review_content = $this->data['review_content'] ?? null;
			$upload_images = $this->data['upload_images'] ?? null;
			$rating = $this->data['rating'] ?? 0;
			$as_anonymous = $this->data['as_anonymous'] ?? 0;
			$as_anonymous = $as_anonymous == 1 ? true : false;

			if (!$order_uuid) {
				$this->msg = t("Order id is missing");
				$this->responseJson();
			}

			$order = COrders::get($order_uuid);

			$find = AR_review::model()->find(
				'merchant_id=:merchant_id AND client_id=:client_id
			AND order_id=:order_id',
				array(
					':merchant_id' => intval($order->merchant_id),
					':client_id' => intval(Yii::app()->user->id),
					':order_id' => intval($order->order_id)
				)
			);
			if (!$find) {
				$model = new AR_review;
				$model->merchant_id  = intval($order->merchant_id);
				$model->order_id  = intval($order->order_id);
				$model->client_id = intval(Yii::app()->user->id);
				$model->review  = $review_content;
				$model->rating  = $rating;
				$model->date_created = CommonUtility::dateNow();
				$model->ip_address = CommonUtility::userIp();
				$model->as_anonymous = $as_anonymous;
				$model->scenario = 'insert';
				if ($model->save()) {
					$this->code = 1;
					$this->msg = t("Review has been added. Thank you.");
					$merchant = CMerchants::get($order->merchant_id);
					$is_favourite_added = CMerchantListingV1::isFavoriteAdded('restaurant', Yii::app()->user->id, $order->merchant_id);
					$is_favourite_added = $is_favourite_added ? true : false;
					$this->details = [
						'rating_meaning' => CReviews::getMeaning($rating, $merchant->restaurant_name),
						'is_favourite_added' => $is_favourite_added
					];
					CReviews::insertMetaImages($model->id, 'upload_images', $upload_images);
				} else $this->msg = CommonUtility::parseError($order->getErrors());
			} else $this->msg = t("You already added review for this order");
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

			if ($page > 0) {
				$page = $page - 1;
			}

			$offset = 0;
			$show_next_page = false;
			$limit = Yii::app()->params->list_limit;
			$total_rows = COrders::orderHistoryTotal(Yii::app()->user->id);

			$pages = new CPagination($total_rows);
			$pages->pageSize = $limit;
			$pages->setCurrentPage($page);
			$offset = $pages->getOffset();
			$page_count = $pages->getPageCount();

			if ($page_count > ($page + 1)) {
				$show_next_page = true;
			}

			$status = [];
			if ($order_tab == "active") {
				$status = AOrderSettings::getTabsGroupStatus(['new_order', 'order_processing', 'order_ready']);
			} else if ($order_tab == 'past_order') {
				$status = AOrderSettings::getTabsGroupStatus(['completed_today']);
			} else if ($order_tab == 'cancel_order') {
				$status = AOrderSettings::getStatus(array('status_cancel_order'));
			}

			$data = COrders::getOrderHistory(Yii::app()->user->id, $q, $offset, $limit, Yii::app()->language, 0, $status, true);

			$payment_status = COrders::paymentStatusList2(Yii::app()->language, 'payment');
			$payment_list = AttributesTools::PaymentProvider();

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = array(
				'show_next_page' => $show_next_page,
				'page' => intval($page) + 1,
				'data' => $data,
				'payment_status' => $payment_status,
				'payment_list' => $payment_list
			);
		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionorderdetails()
	{
		try {

			$refund_transaction = array();
			$order_id = 0;
			$summary = array();
			$progress = array();
			$order_status = array();
			$allowed_to_cancel = false;
			$pdf_link = '';
			$delivery_timeline = array();
			$order_delivery_status = array();
			$merchant_info = array();
			$order = array();
			$items = array();

			$label = array(
				'your_order_from' => t("Your order from"),
				'summary' => t("Summary"),
				'track' => t("Track"),
				'buy_again' => t("Buy again"),
			);

			$order_uuid = isset($this->data['order_uuid']) ? $this->data['order_uuid'] : '';
			$payload = isset($this->data['payload']) ? $this->data['payload'] : array();

			$exchange_rate = 1;
			$model_order = COrders::get($order_uuid);
			if ($model_order->base_currency_code != $model_order->use_currency_code) {
				$exchange_rate = $model_order->exchange_rate > 0 ? $model_order->exchange_rate : 1;
				Price_Formatter::init($model_order->use_currency_code);
			} else {
				Price_Formatter::init($model_order->use_currency_code);
			}
			COrders::setExchangeRate($exchange_rate);

			COrders::getContent($order_uuid, Yii::app()->language);
			$merchant_id = COrders::getMerchantId($order_uuid);
			$order_id = COrders::getOrderID();

			if (in_array('merchant_info', $payload)) {
				$merchant_info = COrders::getMerchant($merchant_id, Yii::app()->language);
			}
			if (in_array('items', $payload)) {
				$items = COrders::getItems();
			}

			if (in_array('summary', $payload)) {
				$summary = COrders::getSummary();
			}

			if (in_array('order_info', $payload)) {
				$order = COrders::orderInfo();
			}

			if (in_array('progress', $payload)) {
				$progress = CTrackingOrder::getProgress($order_uuid, date("Y-m-d g:i:s a"));
			}

			if (in_array('refund_transaction', $payload)) {
				try {
					$refund_transaction = COrders::getPaymentTransactionList(Yii::app()->user->id,$order_id,array(
					  'paid'
					),array(
					  'refund',
					  'full_refund',
					  'partial',
					  'partial_refund'
					));
				} catch (Exception $e) {}
			}

			$cancel_order_enabled = isset(Yii::app()->params['settings']['cancel_order_enabled']) ? Yii::app()->params['settings']['cancel_order_enabled'] : false;
			$cancel_order_enabled = $cancel_order_enabled == 1 ? true : false;

			if (in_array('status_allowed_cancelled', $payload) && $cancel_order_enabled) {
				$status_allowed_cancelled = COrders::getStatusAllowedToCancel();
				$order_status = $order['order_info']['status'];
				if (in_array($order_status, (array)$status_allowed_cancelled)) {
					$allowed_to_cancel = true;
				}
			}

			if (in_array('pdf_link', $payload)) {
				$pdf_link = Yii::app()->createAbsoluteUrl("/print/pdfdownload", array('order_uuid' => $order['order_info']['order_uuid']));
			}

			if (in_array('delivery_timeline', $payload)) {
				try {
					$delivery_timeline = AOrders::getOrderHistory($order_uuid);
				} catch (Exception $e) {}
			}

			if (in_array('order_delivery_status', $payload)) {
				$order_delivery_status = AttributesTools::getOrderStatus(Yii::app()->language, 'order_status');
			}

			$allowed_to_review = false;
			if (in_array('allowed_to_review', $payload)) {
				$find = AR_review::model()->find(
					'merchant_id=:merchant_id AND client_id=:client_id
					AND order_id=:order_id',
					array(
						':merchant_id' => intval($order['order_info']['merchant_id']),
						':client_id' => intval(Yii::app()->user->id),
						':order_id' => intval($order_id)
					)
				);

				if (!$find) {
					$status_allowed_review = AOrderSettings::getStatus(array('status_delivered', 'status_completed'));
					if (in_array($order_status, (array)$status_allowed_review)) {
						$allowed_to_review = true;
					}
				}
			}

			$estimation = [];
			if (in_array('estimation', $payload)) {
				try {
					$filter = [
						'merchant_id' => $merchant_id,
						'shipping_type' => "standard"
					];
					$estimation  = CMerchantListingV1::estimationMerchant2($filter);
				} catch (Exception $e) {
					//echo $e->getMessage(); die();
				}
			}

			$charge_type = '';
			if (in_array('charge_type', $payload)) {
				$options_data = OptionsTools::find(array('merchant_delivery_charges_type'), $merchant_id);
				$charge_type = isset($options_data['merchant_delivery_charges_type']) ? $options_data['merchant_delivery_charges_type'] : 'fixed';
			}

			$order_table_data = [];
			$order_type = $order['order_info']['order_type'];
			if ($order_type == "dinein") {
				$order_table_data = COrders::orderMeta(['table_id', 'room_id', 'guest_number']);
				$room_id = isset($order_table_data['room_id']) ? $order_table_data['room_id'] : 0;
				$table_id = isset($order_table_data['table_id']) ? $order_table_data['table_id'] : 0;
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

			$review_status = [];
			if (in_array('review_status', $payload) && $order) {
				$client_id = $order['order_info']['client_id'];
				$service_code = $order['order_info']['service_code'];
				$is_driver_review = true;
				if ($service_code == "delivery") {
					$is_driver_review = CReviews::driverReviewDetails($client_id, $order_id);
				}
				$is_review = CReviews::reviewDetails($client_id, $order_id);
				$is_driver_review = $is_driver_review ? true : false;
				$is_review = $is_review ? true : false;
				$review_status = [
					'is_driver_review' => $is_driver_review,
					'is_review' => $is_review,
				];
				if ($is_driver_review && $is_review) {
					$allowed_to_review = false;
				}
			}

			$data = array(
				'merchant' => $merchant_info,
				'order' => $order,
				'items' => $items,
				'summary' => $summary,
				'label' => $label,
				'refund_transaction' => $refund_transaction,
				'progress' => $progress,
				'allowed_to_cancel' => $allowed_to_cancel,
				'allowed_to_review' => $allowed_to_review,
				'pdf_link' => $pdf_link,
				'delivery_timeline' => $delivery_timeline,
				'order_delivery_status' => $order_delivery_status,
				'estimation' => $estimation,
				'charge_type' => $charge_type,
				'order_table_data' => $order_table_data,
				'review_status' => $review_status,
				'share_experience' => t("Rate Your {restaurant_name} Experience!", [
					'{restaurant_name}' => CommonUtility::safeDecode($merchant_info['restaurant_name'] ?? '')
				]),
			);

			$this->code = 1;
			$this->msg = "ok";
			$this->details = array(
				'data' => $data,
			);
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionorderBuyAgain()
	{
		try {
			$current_cart_uuid = $this->data['cart_uuid'] ?? '';
			CCart::clear($current_cart_uuid);
		} catch (Exception $e) {
			//
		}

		try {

			$order_uuid = $this->data['order_uuid'] ?? '';

			COrders::$buy_again = true;
			COrders::getContent($order_uuid, Yii::app()->language);
			$merchant_id = COrders::getMerchantId($order_uuid);
			$items = COrders::getItems();

			$merchant_info = COrders::getMerchant($merchant_id, Yii::app()->language);
			$restaurant_url = isset($merchant_info['restaurant_url']) ? $merchant_info['restaurant_url'] : '';
			$restaurant_slug = isset($merchant_info['restaurant_slug']) ? $merchant_info['restaurant_slug'] : '';

			$cart_uuid = CCart::addOrderToCart($merchant_id, $items);

			$transaction_type = COrders::orderTransaction($order_uuid, $merchant_id, Yii::app()->language);
			CCart::savedAttributes($cart_uuid, Yii::app()->params->local_transtype, $transaction_type);
			CCart::savedAttributes($cart_uuid, 'whento_deliver', 'now');
			CommonUtility::WriteCookie("cart_uuid_local", $cart_uuid);

			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(
				'cart_uuid' => $cart_uuid,
				'restaurant_url' => $restaurant_url,
				'restaurant_slug' => $restaurant_slug
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
			$cancel_status = isset($cancel['meta_value']) ? $cancel['meta_value'] : 'cancelled';

			$reason = "Customer cancel this order";

			if ($resp['payment_type'] == "online") {
				if ($resp['cancel_status'] == 1 && $resp['refund_status'] == "full_refund") {
					// FULL REFUND
					$order->scenario = "cancel_order";
					if ($order->status == $cancel_status) {
						$this->msg = t("This order has already been cancelled");
						$this->responseJson();
					}
					$order->status = $cancel_status;
					$order->remarks = $reason;
					if ($order->save()) {
						$this->code = 1;
						$this->msg = t("Your order is now cancel. your refund is on its way.");
						if (!empty($reason)) {
							COrders::savedMeta($order->order_id, 'rejetion_reason', $reason);
						}
					} else $this->msg = CommonUtility::parseError($order->getErrors());
				} elseif ($resp['cancel_status'] == 1 && $resp['refund_status'] == "partial_refund") {
					///PARTIAL REFUND
					$refund_amount = floatval($resp['refund_amount']);
					$order->scenario = "customer_cancel_partial_refund";

					$model = new AR_ordernew_summary_transaction;
					$model->scenario = "refund";
					$model->order = $order;
					$model->order_id = $order->order_id;
					$model->transaction_description = "Refund";
					$model->transaction_amount = floatval($refund_amount);

					if ($model->save()) {
						$order->status = $cancel_status;
						$order->remarks = $reason;
						if ($order->save()) {
							$this->code = 1;
							$this->msg = t("Your order is now cancel. your partial refund is on its way.");
							if (!empty($reason)) {
								COrders::savedMeta($order->order_id, 'rejetion_reason', $reason);
							}
						} else $this->msg = CommonUtility::parseError($order->getErrors());
					} else $this->msg = CommonUtility::parseError($order->getErrors());
				} else {
					//REFUND NOT AVAILABLE
					$this->msg = $resp['cancel_msg'];
				}
			} else {
				if ($resp['cancel_status'] == 1 && $resp['refund_status'] == "full_refund") {
					//CANCEL ORDER
					$order->scenario = "cancel_order";
					if ($order->status == $cancel_status) {
						$this->msg = t("This order has already been cancelled");
						$this->responseJson();
					}
					$order->status = $cancel_status;
					$reason = "Customer cancell this order";
					$order->remarks = $reason;
					if ($order->save()) {
						$this->code = 1;
						$this->msg = t("Your order is now cancel.");
						if (!empty($reason)) {
							COrders::savedMeta($order->order_id, 'rejetion_reason', $reason);
						}
					} else $this->msg = CommonUtility::parseError($order->getErrors());
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
			$client_id = intval(Yii::app()->user->id);
			$model = AR_client::model()->find(
				'client_id=:client_id',
				array(':client_id' => $client_id)
			);
			if ($model) {
				$this->code = 1;
				$this->msg = "ok";
				$custom_fields = AttributesTools::getCustomFieldsValue($client_id);
				$this->details = array(
					'first_name' => $model->first_name,
					'last_name' => $model->last_name,
					'email_address' => $model->email_address,
					'mobile_prefix' => $model->phone_prefix,
					'mobile_number' => str_replace($model->phone_prefix, "", $model->contact_phone),
					'avatar' => CMedia::getImage($model->avatar, $model->path, '@thumbnail', CommonUtility::getPlaceholderPhoto('customer')),
					'custom_fields' => $custom_fields
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

			$code = isset($this->data['code']) ? $this->data['code'] : '';
			$email_address = isset($this->data['email_address']) ? $this->data['email_address'] : '';
			$mobile_prefix = isset($this->data['mobile_prefix']) ? $this->data['mobile_prefix'] : '';
			$mobile_number = isset($this->data['mobile_number']) ? $this->data['mobile_number'] : '';
			$contact_number = $mobile_prefix . $mobile_number;

			$filename = isset($this->data['filename']) ? $this->data['filename'] : '';
			$upload_path = isset($this->data['upload_path']) ? $this->data['upload_path'] : '';

			$file_data = isset($this->data['file_data']) ? $this->data['file_data'] : '';
			$image_type = isset($this->data['image_type']) ? $this->data['image_type'] : 'png';

			$model = AR_client::model()->find(
				'client_id=:client_id',
				array(':client_id' => intval(Yii::app()->user->id))
			);
			if ($model) {
				$_change = false;
				if ($model->email_address != $email_address) {
					$_change = true;
				}
				if ($model->contact_phone != $contact_number) {
					$_change = true;
				}
				if ($_change) {
					if ($model->mobile_verification_code != $code) {
						$this->msg[] = t("Invalid verification code");
						$this->responseJson();
						Yii::app()->end();
					}
				}

				$model->first_name = isset($this->data['first_name']) ? $this->data['first_name'] : '';
				$model->last_name = isset($this->data['last_name']) ? $this->data['last_name'] : '';
				$model->email_address = $email_address;
				$model->phone_prefix = $mobile_prefix;
				$model->contact_phone = $contact_number;

				if (!empty($filename) && !empty($upload_path)) {
					$model->avatar = $filename;
					$model->path = $upload_path;
				} else {
					if (!empty($file_data)) {
						$result = [];
						try {
							$result = CImageUploader::saveBase64Image($file_data, $image_type, "upload/avatar");
							$model->avatar = isset($result['filename']) ? $result['filename'] : '';
							$model->path = isset($result['path']) ? $result['path'] : '';
						} catch (Exception $e) {
							$this->msg = t($e->getMessage());
							$this->responseJson();
						}
					}
				}

				// CUSTOM FIELDS
				$field_data = [];
				$custom_fields = isset($this->data['custom_fields']) ? $this->data['custom_fields'] : '';
				$field_data = AttributesTools::getCustomFields('customer', 'key2');
				$model->custom_fields = $custom_fields;
				CommonUtility::validateCustomFields($field_data, $custom_fields);

				if ($model->save()) {

					if (!empty($filename) && !empty($upload_path)) {
						Yii::app()->user->setState('avatar', CMedia::getImage($filename, $upload_path));
					}

					$user_data = array(
						'client_uuid' => Yii::app()->user->client_uuid,
						'first_name' => $model->first_name,
						'last_name' => $model->last_name,
						'email_address' => $model->email_address,
						'contact_number' => $contact_number,
						'phone_prefix' => $mobile_prefix,
						'contact_number_noprefix' => substr($contact_number, strlen($mobile_prefix)),
						'avatar' => CMedia::getImage($model->avatar, $model->path, Yii::app()->params->size_image, CommonUtility::getPlaceholderPhoto('customer'))
					);
					$user_data = JWT::encode($user_data, CRON_KEY, 'HS256');

					$payload = [
						'iss' => Yii::app()->request->getServerName(),
						'sub' => 0,
						'iat' => time(),
						'token' => $model->token,
						'username' => $model->email_address,
						'hash' => $model->password,
					];
					$jwt_token = JWT::encode($payload, CRON_KEY, 'HS256');

					$this->code = 1;
					$this->msg = t("Profile updated");
					$this->details = [
						'user_token' => $jwt_token,
						'user_data' => $user_data,
					];
				} else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
			} else $this->msg = t("User not login or session has expired");
		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionupdatePassword()
	{
		try {

			$model = AR_client::model()->find(
				'client_id=:client_id',
				array(':client_id' => intval(Yii::app()->user->id))
			);
			if ($model) {
				$model->scenario = 'update_password';
				$model->old_password = isset($this->data['old_password']) ? $this->data['old_password'] : '';
				$model->npassword = isset($this->data['new_password']) ? $this->data['new_password'] : '';
				$model->cpassword = isset($this->data['confirm_password']) ? $this->data['confirm_password'] : '';
				$model->password = md5($model->npassword);
				if ($model->save()) {
					$this->code = 1;
					$this->msg = t("Password change");
				} else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
			} else $this->msg[] = t("User not login or session has expired");
		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionMyPayments()
	{
		try {
			
			$exclude =  Yii::app()->request->getPost('exclude', null);
			$default_payment_uuid = '';
			$model = AR_client_payment_method::model()->find(
				'merchant_id=:merchant_id AND client_id=:client_id AND as_default=:as_default',
				array(
					':merchant_id' => 0,
					':client_id' => Yii::app()->user->id,
					':as_default' => 1
				)
			);
			if ($model) {
				$default_payment_uuid = $model->payment_uuid;
			}

			$data = CPayments::SavedPaymentList(Yii::app()->user->id, 0, 0, false, '', $exclude);

			$this->code = 1;
			$this->msg = "ok";
			$this->details = array(
				'default_payment_uuid' => $default_payment_uuid,
				'data' => $data,
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
			CPayments::delete(Yii::app()->user->id, $payment_uuid);
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

			$data = array();
			$payments_credentials = array();
			$cart_uuid = Yii::app()->input->post('cart_uuid');

			if (!empty($cart_uuid)) {
				$merchant_id = CCart::getMerchantId($cart_uuid);
				$merchants = CMerchantListingV1::getMerchant($merchant_id);
				$data = CPayments::PaymentList($merchant_id);
				$payments_credentials = CPayments::getPaymentCredentials($merchant_id, '', $merchants->merchant_type);
			} else {
				$data = CPayments::DefaultPaymentList();
				$payments_credentials = CPayments::getPaymentCredentials(0, '', 2);
			}

			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(
				'data' => $data,
				'credentials' => $payments_credentials
			);
		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionfetchpaymentmethod()
	{
		try {

			$merchants = null;
			$merchant_id =  Yii::app()->request->getQuery('merchant_id', null);
			$currency_code = Yii::app()->request->getQuery('currency_code', '');
			$data = null;
			$payments_credentials = array();

			$merchants = CMerchantListingV1::getMerchant($merchant_id);

			if ($merchant_id) {
				try {
					$data = CPayments::PaymentList($merchant_id);
					$payments_credentials = CPayments::getPaymentCredentials($merchant_id, '', $merchants->merchant_type);
					foreach ($data as &$items) {
						$items['credentials'] = $payments_credentials[$items['payment_code']] ?? null;
					}
				} catch (Exception $e) {
				}
			} else {
				try {
					$data = CPayments::DefaultPaymentList();
					$payments_credentials = CPayments::getPaymentCredentials(0, '', 2);
					foreach ($data as &$items) {
						$items['credentials'] = $payments_credentials[$items['payment_code']] ?? null;
					}
				} catch (Exception $e) {
				}
			}

		    $block_payment_list = ACustomer::getBlockPaymentmethod(Yii::app()->user->id);
		    if(is_array($block_payment_list) && count($block_payment_list)>=1  && is_array($data)  && count($data)>=1 ){
			  $data = array_values(array_filter($data, function($payment) use ($block_payment_list) {
					return !in_array($payment['payment_code'], $block_payment_list);
			  }));
		    }

			$multicurrency_enabled = Yii::app()->params['settings']['multicurrency_enabled'] ?? false;
			$multicurrency_enabled = $multicurrency_enabled == 1 ? true : false;
			$enabled_hide_payment = Yii::app()->params['settings']['multicurrency_enabled_hide_payment'] ?? false;
			$hide_payment = $multicurrency_enabled == true ? ($enabled_hide_payment == 1 ? true : false) : false;
			$base_currency = Price_Formatter::$number_format['currency_code'];

			if ($multicurrency_enabled) {
				$options_merchant = OptionsTools::find(['merchant_default_currency'], $merchant_id);
				$merchant_default_currency = $options_merchant['merchant_default_currency'] ?? '';
				$merchant_default_currency = !empty($merchant_default_currency) ? $merchant_default_currency : $base_currency;
				$currency_code = !empty($currency_code) ? $currency_code : (empty($merchant_default_currency) ? $base_currency : $merchant_default_currency);
			}

			$saved_payment = null;
			try {
				$saved_payment = CPayments::SavedPaymentList(Yii::app()->user->id, $merchants->merchant_type, $merchant_id, $hide_payment, $currency_code);
			} catch (Exception $e) {
			}

			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(
				'data' => $data,
				'saved_payment' => $saved_payment
			);
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionsaveStoreList()
	{
		try {

			$data = CSavedStore::Listing(Yii::app()->user->id);
			$services = CSavedStore::services(Yii::app()->user->id);
			$estimation = CSavedStore::estimation(Yii::app()->user->id);
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = array(
				'data' => $data,
				'services' => $services,
				'estimation' => $estimation
			);
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionSaveStore()
	{
		try {

			if (!Yii::app()->user->isGuest) {

				$merchant_id = Yii::app()->input->post('merchant_id');
				$model = AR_favorites::model()->find(
					'fav_type=:fav_type AND merchant_id=:merchant_id AND client_id=:client_id',
					array(
						':fav_type' => "restaurant",
						':merchant_id' => $merchant_id,
						'client_id' => Yii::app()->user->id
					)
				);

				if ($model) {
					$model->delete();
					$this->code = 1;
					$this->msg = t("Succesfully add to your favourites");
					$this->details = array('found' => false);
				} else {
					$model = new AR_favorites;
					$model->client_id = Yii::app()->user->id;
					$model->merchant_id = $merchant_id;
					if ($model->save()) {
						$this->code = 1;
						$this->msg = t("Succesfully add to your favourites");
						$this->details = array('found' => true);
					} else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
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
			$item = AR_item::model()->find("item_token=:item_token", [
				':item_token' => $item_token
			]);
			if ($item) {
				$model = AR_favorites::model()->find("fav_type=:fav_type AND client_id=:client_id
				AND merchant_id=:merchant_id
				AND item_id=:item_id
				", [
					':fav_type' => 'item',
					':client_id' => intval(Yii::app()->user->id),
					':merchant_id' => intval($item->merchant_id),
					':item_id' => intval($item->item_id)
				]);
				if ($model) {
					$model->delete();
					$this->details = array('found' => false);
				} else {
					$model = new AR_favorites();
					$model->fav_type = 'item';
					$model->client_id = intval(Yii::app()->user->id);
					$model->merchant_id = intval($item->merchant_id);
					$model->cat_id = intval($cat_id);
					$model->item_id = intval($item->item_id);
					$model->save();
					$mode = 'save';
					$this->details = array('found' => true);
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

			$currency_code = Yii::app()->input->post('currency_code');
			$base_currency = Price_Formatter::$number_format['currency_code'];

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled']) ? Yii::app()->params['settings']['multicurrency_enabled'] : false;
			$multicurrency_enabled = $multicurrency_enabled == 1 ? true : false;

			if (!empty($currency_code) && $multicurrency_enabled) {
				Price_Formatter::init($currency_code);
			}

			$money_config = array();
			$format = Price_Formatter::$number_format;
			$money_config = [
				'precision' => $format['decimals'],
				'minimumFractionDigits' => $format['decimals'],
				'decimal' => $format['decimal_separator'],
				'thousands' => $format['thousand_separator'],
				'separator' => $format['thousand_separator'],
				'prefix' => $format['position'] == 'left' ? $format['currency_symbol'] : '',
				'suffix' => $format['position'] == 'right' ? $format['currency_symbol'] : '',
				'prefill' => true
			];

			$data = CSavedStore::getSaveItemsByCustomer(Yii::app()->user->id);
			$items = CMerchantMenu::getMenuByGroupID($data['item_ids'], Yii::app()->language);
			$this->code = 1;
			$this->msg = "OK";
			$this->details = [
				'data' => $data['data'],
				'items' => $items,
				'money_config' => $money_config
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionrequestData()
	{
		$model = AR_client::model()->find(
			'client_id=:client_id',
			array(':client_id' => intval(Yii::app()->user->id))
		);
		if ($model) {
			$gpdr = AR_gpdr_request::model()->find(
				'client_id=:client_id AND request_type=:request_type AND status=:status',
				array(
					':client_id' => intval(Yii::app()->user->id),
					':request_type' => 'request_data',
					':status' => 'pending'
				)
			);
			if (!$gpdr) {
				$gpdr = new AR_gpdr_request;
				$gpdr->request_type = "request_data";
				$gpdr->client_id = intval(Yii::app()->user->id);
				$gpdr->first_name = $model->first_name;
				$gpdr->last_name = $model->last_name;
				$gpdr->email_address = $model->email_address;
				if ($gpdr->save()) {
					$this->code = 1;
					$this->msg = "ok";
				} else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
			} else $this->msg = t("You have already existing request.");
		} else $this->msg = t("User not login or session has expired");
		$this->responseJson();
	}

	public function actionverifyAccountDelete()
	{
		$code = Yii::app()->input->post('code');
		$model = AR_client::model()->find(
			'client_id=:client_id',
			array(':client_id' => intval(Yii::app()->user->id))
		);
		if ($model) {
			if ($model->mobile_verification_code == $code) {
				$this->code = 1;
				$this->msg = "ok";
			} else $this->msg[] = t("Invalid verification code");
		} else $this->msg[] = t("User not login or session has expired");
		$this->responseJson();
	}

	public function actiondeleteAccount()
	{
		$code = Yii::app()->input->post('code');
		$model = AR_client::model()->find(
			'client_id=:client_id',
			array(':client_id' => intval(Yii::app()->user->id))
		);
		if ($model) {
			if ($model->mobile_verification_code == $code) {
				$model->status = "deleted";
				$model->save();
				Yii::app()->user->logout(false);
				$this->code = 1;
				$this->msg = t("Your account is being deleted");
				$this->details = [];
			} else $this->msg[] = t("Invalid verification code");
		} else $this->msg[] = t("User not login or session has expired");
		$this->responseJson();
	}

	public function actionsaveSettings()
	{
		try {

			$app_push_notifications = isset($this->data['app_push_notifications']) ? $this->data['app_push_notifications'] : '';
			$app_sms_notifications = isset($this->data['app_sms_notifications']) ? $this->data['app_sms_notifications'] : '';
			$offers_email_notifications = isset($this->data['offers_email_notifications']) ? $this->data['offers_email_notifications'] : '';
			$app_push_promotional = isset($this->data['app_push_promotional']) ? $this->data['app_push_promotional'] : '';

			AR_client_meta::saveMeta(Yii::app()->user->id, 'app_push_notifications', $app_push_notifications);
			AR_client_meta::saveMeta(Yii::app()->user->id, 'app_sms_notifications', $app_sms_notifications);
			AR_client_meta::saveMeta(Yii::app()->user->id, 'offers_email_notifications', $offers_email_notifications);
			AR_client_meta::saveMeta(Yii::app()->user->id, 'app_push_promotional', $app_push_promotional);

			$this->code = 1;
			$this->msg = t("Setting saved");
			$this->details = [
				'app_push_notifications' => $app_push_notifications == 1 ? true : false
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
			$criteria = new CDbCriteria();
			$criteria->condition = "client_id=:client_id";
			$criteria->params  = array(
				':client_id' => intval($client_id)
			);
			$metas = ['app_push_notifications', 'app_sms_notifications', 'offers_email_notifications', 'app_push_promotional'];
			$criteria->addInCondition('meta1', (array) $metas);
			$model = AR_client_meta::model()->findAll($criteria);
			if ($model) {
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
			$model = AR_clientsignup::model()->find(
				'email_address=:email_address',
				array(':email_address' => $email_address)
			);
			if ($model) {
				if ($model->status == "active") {
					$model->scenario = "reset_password";
					$model->reset_password_request = 1;
					if ($model->save()) {
						$this->code = 1;
						$this->msg = t("Check {{email_address}} for an email to reset your password.", array(
							'{{email_address}}' => $model->email_address
						));
						$this->details = array(
							'uuid' => $model->client_uuid
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

			$model = AR_clientsignup::model()->find(
				'client_uuid=:client_uuid',
				array(':client_uuid' => $client_uuid)
			);
			if ($model) {
				$model->scenario = "reset_password";
				$model->reset_password_request = 1;
				if ($model->save()) {

					$this->code = 1;
					$this->msg = t("Check {{email_address}} for an email to reset your password.", array(
						'{{email_address}}' => $model->email_address
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
			$options_merchant = OptionsTools::find(['merchant_timezone'], $merchant_id);
			$merchant_timezone = isset($options_merchant['merchant_timezone']) ? $options_merchant['merchant_timezone'] : '';
			if (!empty($merchant_timezone)) {
				Yii::app()->timezone = $merchant_timezone;
			}

			$date = date("Y-m-d");
			$time_now = date("H:i");

			$choosen_delivery = isset($this->data['choosen_delivery']) ? $this->data['choosen_delivery'] : '';
			$whento_deliver = isset($choosen_delivery['whento_deliver']) ? $choosen_delivery['whento_deliver'] : '';

			if ($whento_deliver == "schedule") {
				$date = isset($choosen_delivery['delivery_date']) ? $choosen_delivery['delivery_date'] : $date;
				$time_now = isset($choosen_delivery['delivery_time']) ? $choosen_delivery['delivery_time']['start_time'] : $time_now;
			}

			$datetime_to = date("Y-m-d g:i:s a", strtotime("$date $time_now"));
			CMerchantListingV1::checkCurrentTime(date("Y-m-d g:i:s a"), $datetime_to);

			$resp = CMerchantListingV1::checkStoreOpen($merchant_id, $date, $time_now);
			$this->code = 1;
			$this->msg = $resp['merchant_open_status'] > 0 ? "ok" : t("This store is close right now, but you can schedule an order later.");
			$this->details =  $resp;
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actioncheckStoreOpen2()
	{
		try {


			$slug = isset($this->data['slug']) ? $this->data['slug'] : '';
			$merchant = CMerchantListingV1::getMerchantBySlug($slug);
			$merchant_id = $merchant->merchant_id;

			// CHECK IF MERCHANT HAS DIFFERENT TIMEZONE
			$options_merchant = OptionsTools::find(['merchant_timezone'], $merchant_id);
			$merchant_timezone = isset($options_merchant['merchant_timezone']) ? $options_merchant['merchant_timezone'] : '';
			if (!empty($merchant_timezone)) {
				Yii::app()->timezone = $merchant_timezone;
			}

			$date = date("Y-m-d");
			$time_now = date("H:i");

			$choosen_delivery = isset($this->data['choosen_delivery']) ? $this->data['choosen_delivery'] : '';
			$whento_deliver = isset($choosen_delivery['whento_deliver']) ? $choosen_delivery['whento_deliver'] : '';

			if ($whento_deliver == "schedule") {
				$date = isset($choosen_delivery['delivery_date']) ? $choosen_delivery['delivery_date'] : $date;
				$time_now = isset($choosen_delivery['delivery_time']) ? $choosen_delivery['delivery_time']['start_time'] : $time_now;
			}

			try {
				$datetime_to = date("Y-m-d g:i:s a", strtotime("$date $time_now"));
				CMerchantListingV1::checkCurrentTime(date("Y-m-d g:i:s a"), $datetime_to);
				$time_already_passed = false;
			} catch (Exception $e) {
				$time_already_passed = true;
			}

			$resp = CMerchantListingV1::checkStoreOpen($merchant_id, $date, $time_now);
			if ($merchant->close_store == 1) {
				$resp['merchant_open_status'] = 0;
			}
			$this->code = 1;
			$this->msg = $resp['merchant_open_status'] > 0 ? "ok" : t("This store is close right now, but you can schedule an order later.");
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
			$data = CClientAddress::getAddress($place_id, Yii::app()->user->id);

			$maps_config = CMaps::config();
			$maps_config = JWT::encode($maps_config, CRON_KEY, 'HS256');

			$this->code = 1;
			$this->msg = "ok";
			$this->details = [
				'data' => $data,
				'maps_config' => $maps_config
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
			$items = CMerchantMenu::getSimilarItems($merchant_id, Yii::app()->language, 100, $q);
			$this->code = 1;
			$this->msg = "ok";
			$this->details = [
				'slug' => $model->restaurant_slug,
				'data' => $items
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
				'prefix' => $format['position'] == 'left' ? $format['currency_symbol'] : '',
				'suffix' => $format['position'] == 'right' ? $format['currency_symbol'] : ''
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
			$maps_config = JWT::encode($maps_config, CRON_KEY, 'HS256');

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
				'cuisine',
				'reviews',
				'services'
			];

			$q = Yii::app()->input->post('q');
			$place_id = Yii::app()->input->post('place_id');
			$currency_code = Yii::app()->input->post('currency_code');

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled']) ? Yii::app()->params['settings']['multicurrency_enabled'] : false;
			$multicurrency_enabled = $multicurrency_enabled == 1 ? true : false;

			$place_data = CMaps::locationDetails($place_id, '');

			$filters = [
				'lat' => isset($place_data['latitude']) ? $place_data['latitude'] : '',
				'lng' => isset($place_data['longitude']) ? $place_data['longitude'] : '',
				'limit' => 100,
				'unit' => Yii::app()->params['settings']['home_search_unit_type'],
				'today_now' => strtolower(date("l", strtotime($todays_date))),
				'time_now' => date("H:i", strtotime($todays_date)),
				'date_now' => $todays_date,
				'page' => intval($page),
				'client_id' => !Yii::app()->user->isGuest ? Yii::app()->user->id : 0,
			];

			$and = '';

			$filters['having'] = "distance < a.delivery_distance_covered";
			$filters['condition'] = "a.status=:status  AND a.is_ready = :is_ready $and";
			$filters['params'] = [
				':status' => 'active',
				':is_ready' => 2
			];
			$filters['search'] = "a.restaurant_name";
			$filters['search_params'] = $q;

			$merchant_data = [];
			$cuisine = [];
			$items = [];
			$merchant_list = [];

			try {

				$data = CMerchantListingV1::getFeed($filters);
				$merchant_data = $data['data'];

				if (in_array('cuisine', $payload)) {
					try {
						$cuisine = CMerchantListingV1::getCuisine($data['merchant'], Yii::app()->language);
					} catch (Exception $e) {
						$cuisine = [];
					}
				}
			} catch (Exception $e) {
				$merchant_data = [];
			}

			try {

				$search = explode(" ", $q);
				$data = CMerchantMenu::searchItems($search, Yii::app()->language, 100, $currency_code, $multicurrency_enabled);
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
				'prefix' => $format['position'] == 'left' ? $format['currency_symbol'] : '',
				'suffix' => $format['position'] == 'right' ? $format['currency_symbol'] : ''
			];

			$this->code = 1;
			$this->msg = "OK";
			$this->details = [
				'merchant_data' => $merchant_data,
				'cuisine' => $cuisine,
				'food_list' => $items,
				'merchant_list' => $merchant_list,
				'money_config' => $config
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
			$page = Yii::app()->request->getQuery('page', null);
			$page_raw = Yii::app()->request->getQuery('page', 0);

			if (!$page) {
				$page = Yii::app()->request->getPost('page', 0);
				$page_raw = Yii::app()->request->getPost('page', 0);
			}

			$page = intval($page);
			$page_raw = intval($page_raw);

			if ($page > 0) {
				$page = $page - 1;
			}

			$criteria = new CDbCriteria();
			$criteria->condition = "notication_channel=:notication_channel";
			$criteria->params  = array(
				':notication_channel' => Yii::app()->user->client_uuid
			);
			$criteria->order = "date_created DESC";

			$count = AR_notifications::model()->count($criteria);
			$pages = new CPagination($count);
			$pages->pageSize = $limit;
			$pages->setCurrentPage($page);
			$pages->applyLimit($criteria);
			$page_count = $pages->getPageCount();

			if ($page > 0) {
				if ($page_raw > $page_count) {
					$this->code = 3;
					$this->msg = t("end of results");
					$this->responseJson();
				}
			}

			$model = AR_notifications::model()->findAll($criteria);
			if ($model) {
				$data = [];
				foreach ($model as $item) {
					$image = '';
					$url = '';
					if ($item->image_type == "icon") {
						$image = !empty($item->image) ? $item->image : '';
					} else {
						if (!empty($item->image)) {
							$image = CMedia::getImage(
								$item->image,
								$item->image_path,
								Yii::app()->params->size_image_thumbnail,
								CommonUtility::getPlaceholderPhoto('item')
							);
						}
					}

					$params = !empty($item->message_parameters) ? json_decode($item->message_parameters, true) : '';

					$data[] = array(
						'notification_uuid' => $item->notification_uuid,
						'notification_type' => $item->notification_type,
						'message' => t($item->message, (array)$params),
						'date' => PrettyDateTime::parse(new DateTime($item->date_created)),
						'image_type' => $item->image_type,
						'image' => $image,
						'url' => $url
					);
				}

				$this->code = $page_raw == $page_count ? 3 : 1;
				$this->msg = "ok";
				$this->details = [
					'page_raw' => $page_raw,
					'page_count' => $page_count,
					'data' => $data
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

			$uuid = Yii::app()->request->getPost('uuid', null);
			if (!$uuid) {
				$this->msg = t("Invalid notification id");
				$this->responseJson();
			}
			$model = AR_notifications::model()->find("notification_uuid=:notification_uuid", [
				':notification_uuid' => $uuid
			]);
			if ($model) {
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

			$phone_default_country = isset(Yii::app()->params['settings']['mobilephone_settings_default_country']) ? Yii::app()->params['settings']['mobilephone_settings_default_country'] : 'us';
			$phone_country_list = isset(Yii::app()->params['settings']['mobilephone_settings_country']) ? Yii::app()->params['settings']['mobilephone_settings_country'] : '';
			$phone_country_list = !empty($phone_country_list) ? json_decode($phone_country_list, true) : array();

			$filter = array(
				'only_countries' => (array)$phone_country_list
			);
			$data = ClocationCountry::listing($filter);
			$default_data = ClocationCountry::get($phone_default_country);

			$client_uuid = Yii::app()->input->post('client_uuid');
			$model = AR_clientsignup::model()->find(
				'client_uuid=:client_uuid',
				array(':client_uuid' => $client_uuid)
			);
			if ($model) {
				$this->code = 1;
				$this->msg  = "Ok";
				$this->details = array(
					'first_name' => $model->first_name,
					'last_name' => $model->last_name,
					'email_address' => $model->email_address,
					'data' => $data,
					'default_data' => $default_data
				);
			} else $this->msg = t("Records not found");
		} catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actionverifiySocialSignup()
	{
		try {

			$client_uuid = Yii::app()->request->getPost('uuid', null);
			$otp = Yii::app()->request->getPost('otp', null);

			if (!$client_uuid) {
				$this->msg = t("Missing required parameters in the request.");
				$this->responseJson();
			}
			if (!$otp) {
				$this->msg = t("OTP is required");
				$this->responseJson();
			}

			$model = AR_clientsignup::model()->find('client_uuid=:client_uuid', array(':client_uuid' => $client_uuid));
			if (!$model) {
				$this->msg = t(HELPER_RECORD_NOT_FOUND);
				$this->responseJson();
			}
			if (CommonUtility::safeTrim($model->mobile_verification_code) === CommonUtility::safeTrim($otp)) {

				$model->account_verified = 1;
				$model->save();

				$this->code = 1;
				$this->msg = t(Helper_success);
				$this->details = array(
					'uuid' => $model->client_uuid,
				);
			} else $this->msg = t("The OTP code you entered is invalid. Please double-check the code and try again.");
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actioncompleteSocialSignup()
	{
		try {

			$client_uuid = isset($this->data['client_uuid']) ? $this->data['client_uuid'] : '';
			$prefix = isset($this->data['mobile_prefix']) ? $this->data['mobile_prefix'] : '';
			$mobile_number = isset($this->data['mobile_number']) ? $this->data['mobile_number'] : '';
			$custom_fields =  $this->data['custom_fields'] ?? '';

			$model = AR_clientsignup::model()->find(
				'client_uuid=:client_uuid',
				array(':client_uuid' => $client_uuid)
			);
			if ($model) {
				$model->scenario = 'complete_registration';
				if ($model->account_verified == 1) {
					$model->first_name = isset($this->data['first_name']) ? $this->data['first_name'] : '';
					$model->last_name = isset($this->data['last_name']) ? $this->data['last_name'] : '';
					$model->contact_phone = $prefix . $mobile_number;
					$model->phone_prefix = $prefix;
					$model->password = isset($this->data['password']) ? $this->data['password'] : '';
					$model->cpassword = isset($this->data['cpassword']) ? $this->data['cpassword'] : '';
					$password = $model->password;
					$model->status = 'active';
					if ($model->save()) {
						$this->code = 1;
						$this->msg = t("Registration successful");
						$this->autoLogin($model->email_address, $password);
						$this->saveDeliveryAddress($local_id,$model->client_id);
					} else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
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

			$model = AR_device::model()->find("device_token = :device_token", [
				':device_token' => $token
			]);
			if ($model) {
				$model->device_uiid = $device_uiid;
				$model->enabled = 1;
				$model->date_created = CommonUtility::dateNow();
				$model->date_modified = CommonUtility::dateNow();
				$model->ip_address = CommonUtility::userIp();
				if (!$model->save()) {
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
				if (!$model->save()) {
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

			$model = AR_device::model()->find("user_type=:user_type AND user_id=:user_id AND device_uiid = :device_uiid", [
				':user_type' => 'client',
				':user_id' => Yii::app()->user->id,
				':device_uiid' => $device_uiid
			]);
			if ($model) {
				$model->platform = $platform;
				$model->device_token = $token;
				$model->device_uiid = $device_uiid;
				$model->user_id = Yii::app()->user->id;
				$model->enabled = 1;
				$model->date_created = CommonUtility::dateNow();
				$model->date_modified = CommonUtility::dateNow();
				$model->ip_address = CommonUtility::userIp();
				if (!$model->save()) {
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
				if (!$model->save()) {
					$this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
					$this->responseJson();
				}
			}

			Yii::app()->db->createCommand("
            DELETE FROM {{device}}
            WHERE user_type='client'
            AND user_id=0
            AND device_uiid=" . q($device_uiid) . "
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
			if (empty($jwt_token) || $jwt_token == "false") {
				$this->msg = t("Token is empty");
				$this->responseJson();
			}
			$decoded = JWT::decode($jwt_token, new Key(CRON_KEY, 'HS256'));
			$token = isset($decoded->token) ? $decoded->token : '';
			$username = isset($decoded->username) ? $decoded->username : '';
			$hash = isset($decoded->hash) ? $decoded->hash : '';

			$model = AR_client::model()->find("token=:token AND status=:status  AND email_address=:email_address AND password=:password", array(
				':token' => $token,
				':status' => "active",
				':email_address' => $username,
				':password' => $hash,
			));
			if ($model) {
				$settings = AR_client_meta::getMeta2(['app_push_notifications'], $model->client_id);
				$app_push_notifications = $settings['app_push_notifications'] ?? true;
				$user_settings = [
					'app_push_notifications' => $app_push_notifications == 1 ? true : false,
				];
				$this->code = 1;
				$this->msg = "OK";
				$this->details = [
					'user_settings' => $user_settings
				];
			} else $this->msg = t("Hey there! It looks like there have been changes to your account information, which has resulted in a logout. Please log back in using the updated details to continue accessing your account. Thanks!");
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
			$data = CMerchantMenu::searchMenuItems($q, $merchant_id, Yii::app()->language, 100);
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
			$data = CSavedStore::getSaveItemsByCustomer(Yii::app()->user->id, $merchant->merchant_id);
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

			$tips_list = [];
			$phone_prefix_data = [];

			$phone_default_country = isset(Yii::app()->params['settings']['mobilephone_settings_default_country']) ? Yii::app()->params['settings']['mobilephone_settings_default_country'] : 'us';
			$phone_country_list = isset(Yii::app()->params['settings']['mobilephone_settings_country']) ? Yii::app()->params['settings']['mobilephone_settings_country'] : '';
			$phone_country_list = !empty($phone_country_list) ? json_decode($phone_country_list, true) : array();

			$enabled_language = isset(Yii::app()->params['settings']['enabled_language_customer_app']) ? Yii::app()->params['settings']['enabled_language_customer_app'] : '';
			$enabled_language = $enabled_language == 1 ? true : false;

			$addons_use_checkbox = isset(Yii::app()->params['settings']['admin_addons_use_checkbox']) ? Yii::app()->params['settings']['admin_addons_use_checkbox'] : '';
			$addons_use_checkbox = $addons_use_checkbox == 1 ? true : false;

			$category_use_slide = isset(Yii::app()->params['settings']['admin_category_use_slide']) ? Yii::app()->params['settings']['admin_category_use_slide'] : '';
			$category_use_slide = $category_use_slide == 1 ? true : false;

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled']) ? Yii::app()->params['settings']['multicurrency_enabled'] : false;
			$multicurrency_enabled = $multicurrency_enabled == 1 ? true : false;
			$enabled_hide_payment = isset(Yii::app()->params['settings']['multicurrency_enabled_hide_payment']) ? Yii::app()->params['settings']['multicurrency_enabled_hide_payment'] : false;
			$enabled_checkout_currency = isset(Yii::app()->params['settings']['multicurrency_enabled_checkout_currency']) ? Yii::app()->params['settings']['multicurrency_enabled_checkout_currency'] : false;
			$hide_payment = $multicurrency_enabled == true ? ($enabled_hide_payment == 1 ? true : false) : false;
			$enabled_force = $multicurrency_enabled == true ? ($enabled_checkout_currency == 1 ? true : false) : false;

			$points_enabled = isset(Yii::app()->params['settings']['points_enabled']) ? Yii::app()->params['settings']['points_enabled'] : false;
			$points_enabled = $points_enabled == 1 ? true : false;

			$filter = array(
				'only_countries' => (array)$phone_country_list
			);
			$phone_prefix_data = ClocationCountry::listing($filter);
			$phone_default_data = ClocationCountry::get($phone_default_country);


			$tips_list = [];

			$maps_config = CMaps::config('google_maps_api_key_for_mobile');
			$maps_config = JWT::encode($maps_config, CRON_KEY, 'HS256');

			$lang_data = [];
			try {
				$lang_data = ClocationCountry::getLanguageList();
				$lang_data = JWT::encode($lang_data, CRON_KEY, 'HS256');
			} catch (Exception $e) {
				//
			}


			$currency_code	=  Yii::app()->request->getPost('currency_code', null);
			if($currency_code && $currency_code!='null'){
				Price_Formatter::init($currency_code);
			}

			$money_config = array();
			$format = Price_Formatter::$number_format;
			$money_config = [
				'precision' => $format['decimals'],
				'minimumFractionDigits' => $format['decimals'],
				'decimal' => $format['decimal_separator'],
				'thousands' => $format['thousand_separator'],
				'separator' => $format['thousand_separator'],
				'prefix' => $format['position'] == 'left' ? $format['currency_symbol'] : '',
				'suffix' => $format['position'] == 'right' ? $format['currency_symbol'] : '',
				'prefill' => true
			];

			// REALTIME
			$realtime = AR_admin_meta::getMeta(array(
				'realtime_app_enabled',
				'realtime_provider',
				'webpush_app_enabled',
				'webpush_provider',
				'pusher_key',
				'pusher_cluster'
			));
			$realtime_app_enabled = isset($realtime['realtime_app_enabled']) ? $realtime['realtime_app_enabled']['meta_value'] : '';
			$realtime_provider = isset($realtime['realtime_provider']) ? $realtime['realtime_provider']['meta_value'] : '';
			$pusher_key = isset($realtime['pusher_key']) ? $realtime['pusher_key']['meta_value'] : '';
			$pusher_cluster = isset($realtime['pusher_cluster']) ? $realtime['pusher_cluster']['meta_value'] : '';

			$realtime = [
				'realtime_app_enabled' => $realtime_app_enabled,
				'realtime_provider' => $realtime_provider,
				'pusher_key' => $pusher_key,
				'pusher_cluster' => $pusher_cluster,
				'event' => [
					'tracking' => Yii::app()->params->realtime['event_tracking_order'],
					'notification_event' => Yii::app()->params->realtime['notification_event'],
					'booking' => 'booking',
					'cart' => 'cart',
				]
			];
			try {
				$realtime = JWT::encode($realtime, CRON_KEY, 'HS256');
			} catch (Exception $e) {
				$realtime = '';
			}

			$invite_friend_settings = [
				'title' => '',
				'text' => t("Check this app - {site_name}. I use this app to order food from different restaurants. Try them: {site_url}", [
					'{site_name}' => Yii::app()->params['settings']['website_title'],
					'{site_url}' => websiteDomain(),
				]),
				'url' => websiteUrl()
			];

			$fb_flag = isset(Yii::app()->params['settings']['fb_flag']) ? Yii::app()->params['settings']['fb_flag'] : false;
			$fb_flag = $fb_flag == 1 ? true : false;
			$google_login_enabled = isset(Yii::app()->params['settings']['google_login_enabled']) ? Yii::app()->params['settings']['google_login_enabled'] : false;
			$google_login_enabled = $google_login_enabled == 1 ? true : false;

			$app_enabled_google_login = Yii::app()->params['settings']['app_enabled_google_login'] ?? false;
			$app_enabled_google_login = $app_enabled_google_login == 1 ? true : false;

			$app_enabled_fb_login = Yii::app()->params['settings']['app_enabled_fb_login'] ?? false;
			$app_enabled_fb_login = $app_enabled_fb_login == 1 ? true : false;			

			$app_enabled_apple_login = Yii::app()->params['settings']['app_enabled_apple_login'] ?? false;
			$app_enabled_apple_login = $app_enabled_apple_login == 1 ? true : false;

			$app_google_client_id = Yii::app()->params['settings']['app_google_client_id'] ?? '';
			$app_facebook_id = Yii::app()->params['settings']['fb_app_id'] ?? '';
			$app_apple_app_id = Yii::app()->params['settings']['app_apple_app_id'] ?? '';			
			$webpush_certificates = Yii::app()->params['settings']['webpush_certificates'] ?? '';			
			$app_facebook_client_token = Yii::app()->params['settings']['app_facebook_client_token'] ?? '';

			$login_method = Yii::app()->params['settings']['login_method'] ?? 'user';
			$signup_terms = Yii::app()->params['settings']['signup_terms'] ?? null;
			$signup_type = Yii::app()->params['settings']['signup_type'] ?? 'standard';

			$captcha_site_key = isset(Yii::app()->params['settings']['captcha_site_key']) ? Yii::app()->params['settings']['captcha_site_key'] : '';
			$captcha_lang = isset(Yii::app()->params['settings']['captcha_lang']) ? Yii::app()->params['settings']['captcha_lang'] : '';

			$booking_status_list = AttributesTools::bookingStatus();
			$booking_status_list = array_merge([
				'all' => t("All")
			], $booking_status_list);


			$use_thresholds = isset(Yii::app()->params['settings']['points_use_thresholds']) ? Yii::app()->params['settings']['points_use_thresholds'] : false;
			$use_thresholds = $use_thresholds == 1 ? true : false;

			$digitalwallet_enabled = isset(Yii::app()->params['settings']['digitalwallet_enabled']) ? Yii::app()->params['settings']['digitalwallet_enabled'] : false;
			$digitalwallet_enabled = $digitalwallet_enabled == 1 ? true : false;

			$chat_enabled = isset(Yii::app()->params['settings']['chat_enabled']) ? Yii::app()->params['settings']['chat_enabled'] : false;
			$chat_enabled = $chat_enabled == 1 ? true : false;

			$digitalwallet_enabled_topup = isset(Yii::app()->params['settings']['digitalwallet_enabled_topup']) ? Yii::app()->params['settings']['digitalwallet_enabled_topup'] : false;
			$digitalwallet_enabled_topup = $digitalwallet_enabled_topup == 1 ? true : false;

			$enabled_include_utensils = isset(Yii::app()->params['settings']['enabled_include_utensils']) ? Yii::app()->params['settings']['enabled_include_utensils'] : false;
			$enabled_include_utensils = $enabled_include_utensils == 1 ? true : false;

			$android_download_url = isset(Yii::app()->params['settings']['android_download_url']) ? Yii::app()->params['settings']['android_download_url'] : '';
			$ios_download_url = isset(Yii::app()->params['settings']['ios_download_url']) ? Yii::app()->params['settings']['ios_download_url'] : '';
			$mobile_app_version_android = isset(Yii::app()->params['settings']['mobile_app_version_android']) ? Yii::app()->params['settings']['mobile_app_version_android'] : '';
			$mobile_app_version_ios = isset(Yii::app()->params['settings']['mobile_app_version_ios']) ? Yii::app()->params['settings']['mobile_app_version_ios'] : '';

			$enabled_review = isset(Yii::app()->params['settings']['enabled_review']) ? Yii::app()->params['settings']['enabled_review'] : '';
			$enabled_review = $enabled_review == 1 ? true : false;

			$address_format_use = isset(Yii::app()->params['settings']['address_format_use']) ? (!empty(Yii::app()->params['settings']['address_format_use']) ? Yii::app()->params['settings']['address_format_use'] : '') : '';
			$address_format_use = !empty($address_format_use) ? $address_format_use : 1;

			$password_reset_options = isset(Yii::app()->params['settings']['password_reset_options']) ? (!empty(Yii::app()->params['settings']['password_reset_options']) ? Yii::app()->params['settings']['password_reset_options'] : 'email') : 'email';
			$signup_resend_counter = isset(Yii::app()->params['settings']['signup_resend_counter']) ? (!empty(Yii::app()->params['settings']['signup_resend_counter']) ? Yii::app()->params['settings']['signup_resend_counter'] : 20) : 20;

			$cancel_order_enabled = isset(Yii::app()->params['settings']['cancel_order_enabled']) ? Yii::app()->params['settings']['cancel_order_enabled'] : false;
			$cancel_order_enabled = $cancel_order_enabled == 1 ? true : false;

			$enabled_guest = Yii::app()->params['settings']['enabled_guest'] ?? false;
			$enabled_guest = $enabled_guest == 1 ? true : false;

			$search_mode = Yii::app()->params['settings']['home_search_mode'] ?? 'address';
			$location_default_country = Yii::app()->params['settings']['location_default_country'] ?? 'US';
			$location_searchtype = Yii::app()->params['settings']['location_searchtype'] ?? 1;
			$location_enabled_map_selection = Yii::app()->params['settings']['location_enabled_map_selection'] ?? 1;
			$location_enabled_map_selection = $location_enabled_map_selection == 1 ? true : false;
			$country_id = Clocations::getDefaultCountry();

			$online_services = null;
			$default_service = '';
			try {
				$online_services = CServices::Listing(Yii::app()->language, false);
				$keys = array_keys($online_services);
				$default_service = isset($keys[0]) ? $keys[0] : $online_services;
			} catch (Exception $e) {
			}

			$featured_data = AttributesTools::MerchantFeatured();
			$featured_data = array('all' => t("All Restaurant")) + $featured_data;

			$user_avatar = CMedia::getImage(null, null, '@thumbnail', CommonUtility::getPlaceholderPhoto('customer'));

			$default_language = Yii::app()->params['settings']['default_language'] ?? KMRS_DEFAULT_LANGUAGE;
			$deliveryOption = CCheckout::deliveryOptionList('', 1);
			$this->details = [
				'phone_prefix_data' => $phone_prefix_data,
				'phone_default_data' => $phone_default_data,
				'tips_list' => $tips_list,
				'maps_config' => $maps_config,
				'language_data' => $lang_data,
				'money_config' => $money_config,
				'realtime' => $realtime,
				'invite_friend_settings' => $invite_friend_settings,
				'fb_flag' => $fb_flag,
				'google_login_enabled' => $google_login_enabled,				
				'login_method' => $login_method,
				'enabled_language' => $enabled_language,
				'multicurrency_enabled' => $multicurrency_enabled,
				'multicurrency_hide_payment' => $hide_payment,
				'multicurrency_enabled_force' => $enabled_force,
				'default_currency_code' => Price_Formatter::$number_format['currency_code'],
				'currency_list' => $multicurrency_enabled ? CMulticurrency::currencyList() : array(),
				'points_enabled' => $points_enabled,
				'captcha_settings' => [
					'site_key' => $captcha_site_key,
					'language' => $captcha_lang,
				],
				'booking_status_list' => $booking_status_list,
				'addons_use_checkbox' => $addons_use_checkbox,
				'category_use_slide' => $category_use_slide,
				'use_thresholds' => $use_thresholds,
				'chat_enabled' => $chat_enabled,
				'digitalwallet_enabled' => $digitalwallet_enabled,
				'digitalwallet_enabled_topup' => $digitalwallet_enabled_topup,
				'appversion_data' => [
					'android_download_url' => $android_download_url,
					'ios_download_url' => $ios_download_url,
					'mobile_app_version_android' => floatval($mobile_app_version_android),
					'mobile_app_version_ios' => floatval($mobile_app_version_ios),
				],
				'enabled_include_utensils' => $enabled_include_utensils,
				'enabled_review' => $enabled_review,
				'address_format_use' => intval($address_format_use),
				'password_reset_options' => $password_reset_options,
				'signup_resend_counter' => intval($signup_resend_counter),
				'cancel_order_enabled' => $cancel_order_enabled,
				'online_services' => $online_services,
				'default_service' => $default_service,
				'delivery_option_list' => $deliveryOption,
				'delivery_option' => CommonUtility::ArrayToLabelValue(CCheckout::deliveryOption()),
				'data' => [
					'default_language'=>$default_language,
					'signup_terms' => $signup_terms,
					'signup_type' => $signup_type,
					'search_mode' => $search_mode,
					'location_default_country' => $location_default_country,
					'country_id' => $country_id,
					'location_searchtype' => $location_searchtype,
					'location_enabled_map_selection' => $location_enabled_map_selection,
					'featured_data' => $featured_data,
					'user_avatar' => $user_avatar,
					'enabled_guest' => $enabled_guest,
					'menu_display_type' => 'all', // all or by_category
					'app_enabled_google_login'=>$app_enabled_google_login,
					'app_enabled_fb_login'=>$app_enabled_fb_login,
					'app_enabled_apple_login'=>$app_enabled_apple_login,
					'app_google_client_id'=>CommonUtility::safeTrim($app_google_client_id),
					'app_facebook_id'=>CommonUtility::safeTrim($app_facebook_id),
					'app_facebook_client_token'=>CommonUtility::safeTrim($app_facebook_client_token),
					'app_apple_app_id'=>CommonUtility::safeTrim($app_apple_app_id),		
					'webpush_certificates'=>CommonUtility::safeTrim($webpush_certificates),
					'apple_web_redirect_uri' => CommonUtility::getHomebaseUrl() . "/" . $this->getControllerId(__CLASS__) . "/apple_callback",
		            'apple_app_redirect_uri' => CommonUtility::getHomebaseUrl() . "/" . $this->getControllerId(__CLASS__) . "/app_apple_callback",
				]
			];
		} catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function getControllerId($controllerClass)
	{
		return strtolower(preg_replace('/Controller$/', '', $controllerClass));
	}

	public function actionremoveTips()
	{
		try {

			$cart_uuid = Yii::app()->input->post('cart_uuid');
			$model = AR_cart_attributes::model()->find("cart_uuid=:cart_uuid AND meta_name=:meta_name", [
				':cart_uuid' => $cart_uuid,
				':meta_name' => "tips"
			]);
			if ($model) {
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

			$mobile_prefix = isset($this->data['mobile_prefix']) ? $this->data['mobile_prefix'] : '';
			$mobile_prefix = str_replace("+", "", $mobile_prefix);
			$mobile_number = isset($this->data['mobile_number']) ? $this->data['mobile_number'] : '';
			$password = isset($this->data['password']) ? $this->data['password'] : '';
			$local_id = isset($this->data['local_id']) ? $this->data['local_id'] : '';

			$options = OptionsTools::find(array('signup_enabled_capcha', 'captcha_secret'));
			$signup_enabled_capcha = isset($options['signup_enabled_capcha']) ? $options['signup_enabled_capcha'] : false;
			$merchant_captcha_secret = isset($options['captcha_secret']) ? $options['captcha_secret'] : '';
			$capcha = $signup_enabled_capcha == 1 ? true : false;
			$recaptcha_response = isset($this->data['recaptcha_response']) ? $this->data['recaptcha_response'] : '';

			$model = new AR_customer_login;
			$model->capcha = false;
			$model->recaptcha_response = $recaptcha_response;
			$model->captcha_secret = $merchant_captcha_secret;
			$model->merchant_id = 0;
			$model->username = $mobile_prefix . $mobile_number;
			$model->password = $password;
			if ($model->validate() && $model->login()) {
				//$this->saveDeliveryAddress($local_id, Yii::app()->user->id );
				$this->code = 1;
				$this->msg = t("Login successful");
				$this->userData();
			} else $this->msg = CommonUtility::parseError($model->getErrors());
		} catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actionupdateAvatar()
	{
		try {
			if (!Yii::app()->user->isGuest) {

				$upload_uuid = CommonUtility::generateUIID();
				$allowed_extension = explode(",",  Yii::app()->params['upload_type']);
				$maxsize = (int) Yii::app()->params['upload_size'];

				if (!empty($_FILES)) {

					$title = $_FILES['file']['name'];
					$size = (int)$_FILES['file']['size'];
					$filetype = $_FILES['file']['type'];

					if (isset($_FILES['file']['name'])) {
						$extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
					} else $extension = strtolower(substr($title, -3, 3));

					if (!in_array($extension, $allowed_extension)) {
						$this->msg = t("Invalid file extension");
						$this->responseJson();
					}
					if ($size > $maxsize) {
						$this->msg = t("Invalid file size");
						$this->responseJson();
					}

					$upload_path = "upload/avatar";
					$tempFile = $_FILES['file']['tmp_name'];
					$upload_uuid = CommonUtility::createUUID("{{media_files}}", 'upload_uuid');
					$filename = $upload_uuid . ".$extension";
					$path = CommonUtility::uploadDestination($upload_path) . "/" . $filename;

					$image_set_width = isset(Yii::app()->params['settings']['review_image_resize_width']) ? intval(Yii::app()->params['settings']['review_image_resize_width']) : 0;
					$image_set_width = $image_set_width <= 0 ? 300 : $image_set_width;

					$image_driver = !empty(Yii::app()->params['settings']['image_driver']) ? Yii::app()->params['settings']['image_driver'] : Yii::app()->params->image['driver'];
					$manager = new ImageManager(array('driver' => $image_driver));
					$image = $manager->make($tempFile);
					$image_width = $manager->make($tempFile)->width();

					if ($image_width > $image_set_width) {
						$image->resize(null, $image_set_width, function ($constraint) {
							$constraint->aspectRatio();
						});
						$image->save($path);
					} else {
						$image->save($path, 60);
					}

					$this->code = 1;
					$this->msg = "OK";
					$this->details = array(
						'url_image' => CMedia::getImage($filename, $upload_path),
						'filename' => $filename,
						'id' => $upload_uuid,
						'upload_path' => $upload_path
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
			$app_push_notifications = $app_push_notifications == "true" ? 1 : 0;
			AR_client_meta::saveMeta(Yii::app()->user->id, 'app_push_notifications', $app_push_notifications);

			$this->code = 1;
			$this->msg = t("Setting saved");
			$this->details = [
				'app_push_notifications' => $app_push_notifications == 1 ? true : false,
			];
		} catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actionUpdateaccountpromonotification()
	{
		try {

			$app_push_promotional = Yii::app()->input->post('app_push_promotional');
			$app_push_promotional = $app_push_promotional == "true" ? 1 : 0;
			AR_client_meta::saveMeta(Yii::app()->user->id, 'app_push_promotional', $app_push_promotional);

			$this->code = 1;
			$this->msg = t("Setting saved");
			$this->details = [
				'app_push_promotional' => $app_push_promotional == 1 ? true : false,
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
			$order_status = AttributesTools::getOrderStatus(Yii::app()->language, 'delivery_status');

			$progress = CTrackingOrder::getProgress($order_uuid, date("Y-m-d g:i:s a"), [
				'order_info',
				'merchant_info'
			]);

			$this->code = 1;
			$this->msg = "ok";
			$this->details = [
				'data' => $data,
				'order_status' => $order_status,
				'progress' => $progress
			];
		} catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actiondeleteAllNotification()
	{
		try {

			$notification_uuids = isset($this->data['notification_uuids']) ? $this->data['notification_uuids'] : '';
			CNotifications::deleteNotifications(Yii::app()->user->client_uuid, $notification_uuids);
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
			$page_id = Yii::app()->request->getPost('page_id', null);
			$option = OptionsTools::find([$page_id]);
			$id = isset($option[$page_id]) ? $option[$page_id] : 0;
			$data = PPages::pageDetailsByID($id, Yii::app()->language);
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
				'total' => $total,
			];
		} catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

	public function actiongetPointsTransaction()
	{
		$data = array();
		$card_id = 0;
		try {
			$card_id = CWallet::getCardID(Yii::app()->params->account_type['customer_points'], Yii::app()->user->id);
		} catch (Exception $e) {
			$this->msg = t("Invalid card id");
			$this->responseJson();
		}

		$limit = 20;
		$page = intval(Yii::app()->input->post('page'));
		$page_raw = intval(Yii::app()->input->post('page'));
		if ($page > 0) {
			$page = $page - 1;
		}

		$criteria = new CDbCriteria();
		$criteria->addCondition('card_id=:card_id');
		$criteria->params = array(':card_id' => intval($card_id));
		$criteria->order = "transaction_id DESC";

		$count = AR_wallet_transactions::model()->count($criteria);
		$pages = new CPagination($count);
		$pages->pageSize = $limit;
		$pages->setCurrentPage($page);
		$pages->applyLimit($criteria);
		$page_count = $pages->getPageCount();

		if ($page > 0) {
			if ($page_raw > $page_count) {
				$this->code = 3;
				$this->msg = t("end of results");
				$this->responseJson();
			}
		}

		$models = AR_wallet_transactions::model()->findAll($criteria);
		if ($models) {
			foreach ($models as $item) {
				$description = Yii::app()->input->xssClean($item->transaction_description);
				$parameters = json_decode($item->transaction_description_parameters, true);
				if (is_array($parameters) && count($parameters) >= 1) {
					$description = t($description, $parameters);
				}

				$transaction_amount = 0;
				$transaction_type = '';
				switch ($item->transaction_type) {
					case "points_redeemed":
						$transaction_amount = "-" . Price_Formatter::convertToRaw($item->transaction_amount, 0);
						$transaction_type = 'debit';
						break;
					default:
						$transaction_amount = "+" . Price_Formatter::convertToRaw($item->transaction_amount, 0);
						$transaction_type = 'credit';
						break;
				}

				$data[] = [
					'transaction_date' => Date_Formatter::dateTime($item->transaction_date),
					'transaction_type' => $transaction_type,
					'transaction_description' => $description,
					'transaction_amount' => $transaction_amount,
				];
			}
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'page_raw' => $page_raw,
				'page_count' => $page_count,
				'data' => $data
			];
		} else $this->msg = t("No results");
		$this->responseJson();
	}

	public function actiongetPointsTransactionMerchant()
	{
		$data = array();
		$card_id = 0;
		try {
			$card_id = CWallet::getCardID(Yii::app()->params->account_type['customer_points'], Yii::app()->user->id);
		} catch (Exception $e) {
			$this->msg = t("Invalid card id");
			$this->responseJson();
		}

		$limit = 20;
		$page = intval(Yii::app()->input->post('page'));
		$page_raw = intval(Yii::app()->input->post('page'));
		if ($page > 0) {
			$page = $page - 1;
		}

		$criteria = new CDbCriteria();
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

		WHERE a.card_id =" . q($card_id) . "

		GROUP BY
		   a.reference_id1;
		ORDER BY b.restaurant_name ASC
		";

		$criteria->alias = "a";
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
			':card_id' => $card_id
		];
		$criteria->group = "a.reference_id1";
		$criteria->order = "b.restaurant_name ASC";

		$count = AR_wallet_transactions::model()->count($criteria);
		$pages = new CPagination($count);
		$pages->pageSize = $limit;
		$pages->setCurrentPage($page);
		$pages->applyLimit($criteria);
		$page_count = $pages->getPageCount();

		if ($page > 0) {
			if ($page_raw > $page_count) {
				$this->code = 3;
				$this->msg = t("end of results");
				$this->responseJson();
			}
		}

		$models = AR_wallet_transactions::model()->findAll($criteria);
		if ($models) {
			foreach ($models as $item) {
				$merchant_ids[] = $item->merchant_id;
				$total = $item->total_earning;
				if ($item->merchant_id <= 0) {
					$total = $total <= 0 ? (-1 * $total) : $total;
				}
				$data[] = [
					'merchant_id' => $item->merchant_id,
					'restaurant_name' => !empty($item->restaurant_name) ? $item->restaurant_name : t("Global points"),
					'total_earning' => Price_Formatter::convertToRaw($total, 0),
					'transaction_type' => 'credit'
				];
			}
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'page_raw' => $page_raw,
				'page_count' => $page_count,
				'data' => $data
			];
		} else $this->msg = t("No results");
		$this->responseJson();
	}

	public function actiongetcartpoints()
	{
		try {

			$cart_uuid = CommonUtility::safeTrim(Yii::app()->input->post('cart_uuid'));
			$currency_code = CommonUtility::safeTrim(Yii::app()->input->post('currency_code'));
			$base_currency = Price_Formatter::$number_format['currency_code'];
			$exchange_rate = 1;
			$exchange_rate_to_merchant = 1;

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled']) ? Yii::app()->params['settings']['multicurrency_enabled'] : false;
			$multicurrency_enabled = $multicurrency_enabled == 1 ? true : false;
			$merchant_id = CCart::getMerchantId($cart_uuid);
			$options_merchant = OptionsTools::find(['merchant_default_currency'], $merchant_id);
			$merchant_default_currency = isset($options_merchant['merchant_default_currency']) ? $options_merchant['merchant_default_currency'] : '';
			$merchant_default_currency = !empty($merchant_default_currency) ? $merchant_default_currency : $base_currency;
			$currency_code = !empty($currency_code) ? $currency_code : (empty($merchant_default_currency) ? $base_currency : $merchant_default_currency);

			if ($multicurrency_enabled) {
				Price_Formatter::init($currency_code);
				$exchange_rate = CMulticurrency::getExchangeRate($base_currency, $currency_code);
				$exchange_rate_to_merchant = CMulticurrency::getExchangeRate($merchant_default_currency, $currency_code);
			}

			$redemption_policy = isset(Yii::app()->params['settings']['points_redemption_policy']) ? Yii::app()->params['settings']['points_redemption_policy'] : 'universal';

			$total = CPoints::getAvailableBalancePolicy(Yii::app()->user->id, $redemption_policy, $merchant_id);

			$attrs = OptionsTools::find(['points_redeemed_points', 'points_redeemed_value', 'points_maximum_redeemed']);
			$points_maximum_redeemed = isset($attrs['points_maximum_redeemed']) ? floatval($attrs['points_maximum_redeemed']) : 0;

			$points_redeemed_points = isset($attrs['points_redeemed_points']) ? floatval($attrs['points_redeemed_points']) : 0;

			$amount = $points_redeemed_points * (1 / $points_redeemed_points);
			$amount = ($amount * $exchange_rate);

			$redeem_discount = t("Get {amount} off for every {points} points", [
				'{amount}' => Price_Formatter::formatNumber(($amount)),
				'{points}' => $points_redeemed_points
			]);

			$redeem_label = '';
			if ($total > 0) {
				$redeem_label = t("Your available balance is {points} points.", [
					'{points}' => $total
				]);
				if ($points_maximum_redeemed > 0 && $total > $points_maximum_redeemed) {
					$redeem_label = t("Redeem {max} out of {points} points", [
						'{max}' => $points_maximum_redeemed,
						'{points}' => $total
					]);
				}
			}

			$discount = 0;
			$points = 0;
			if ($model = CCart::getAttributes($cart_uuid, 'point_discount')) {
				$discount_raw = !empty($model->meta_id) ? json_decode($model->meta_id, true) : false;
				$discount = floatval($discount_raw['value']) * $exchange_rate_to_merchant;
				$points = floatval($discount_raw['points']);

				CCart::getContent($cart_uuid, Yii::app()->language);
				$subtotal = CCart::getSubTotal();
				$sub_total = floatval($subtotal['sub_total']) * $exchange_rate_to_merchant;
				$total_after_discount = floatval($sub_total) - floatval(CCart::cleanNumber($discount));
				if ($total_after_discount <= 0) {
					CCart::deleteAttributes($cart_uuid, 'point_discount');
					$discount = 0;
					$points = 0;
				}
			}

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'total' => $total,
				'redeem_discount' => $redeem_discount,
				'redeem_label' => $redeem_label,
				'discount' => (-1 * $discount),
				'discount_label' => t(
					"Discount Applied: {amount} off using {points} points.",
					[
						'{amount}' => Price_Formatter::formatNumber($discount),
						'{points}' => $points
					]
				),
				'redeemed_points' => $points_redeemed_points
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionapplyPoints()
	{
		try {

			$cart_uuid = CommonUtility::safeTrim(Yii::app()->input->post('cart_uuid'));
			$currency_code = CommonUtility::safeTrim(Yii::app()->input->post('currency_code'));
			$base_currency = Price_Formatter::$number_format['currency_code'];
			$points = floatval(Yii::app()->input->post('points'));
			$points_id = intval(Yii::app()->input->post('points_id'));
			$merchant_id = 0;

			try {
				$merchant_id = CCart::getMerchantId($cart_uuid);
			} catch (Exception $e) {
			}

			$redemption_policy = isset(Yii::app()->params['settings']['points_redemption_policy']) ? Yii::app()->params['settings']['points_redemption_policy'] : 'universal';
			$balance = CPoints::getAvailableBalancePolicy(Yii::app()->user->id, $redemption_policy, $merchant_id);

			if ($points > $balance) {
				$this->msg = t("Insufficient balance");
				$this->responseJson();
			}

			$attrs = OptionsTools::find(['points_redeemed_points', 'points_redeemed_value', 'points_maximum_redeemed', 'points_minimum_redeemed']);
			$points_maximum_redeemed = isset($attrs['points_maximum_redeemed']) ? floatval($attrs['points_maximum_redeemed']) : 0;
			$points_minimum_redeemed = isset($attrs['points_minimum_redeemed']) ? floatval($attrs['points_minimum_redeemed']) : 0;
			$points_redeemed_points = isset($attrs['points_redeemed_points']) ? floatval($attrs['points_redeemed_points']) : 0;

			if ($points_maximum_redeemed > 0 && $points > $points_maximum_redeemed) {
				$this->msg = t("Maximum points for redemption: {points} points.", [
					'{points}' => $points_maximum_redeemed
				]);
				$this->responseJson();
			}
			if ($points_minimum_redeemed > 0 && $points < $points_minimum_redeemed) {
				$this->msg = t("Minimum points for redemption: {points} points.", [
					'{points}' => $points_minimum_redeemed
				]);
				$this->responseJson();
			}

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled']) ? Yii::app()->params['settings']['multicurrency_enabled'] : false;
			$multicurrency_enabled = $multicurrency_enabled == 1 ? true : false;

			$merchant_id = CCart::getMerchantId($cart_uuid);
			$options_merchant = OptionsTools::find(['merchant_default_currency'], $merchant_id);
			$merchant_default_currency = isset($options_merchant['merchant_default_currency']) ? $options_merchant['merchant_default_currency'] : '';
			$merchant_default_currency = !empty($merchant_default_currency) ? $merchant_default_currency : $base_currency;

			$currency_code = !empty($currency_code) ? $currency_code : (empty($merchant_default_currency) ? $base_currency : $merchant_default_currency);

			$exchange_rate = 1;
			$exchange_rate_to_merchant = 1;
			$admin_exchange_rate = 1;
			if (!empty($currency_code) && $multicurrency_enabled) {
				$exchange_rate = CMulticurrency::getExchangeRate($base_currency, $merchant_default_currency);
				$exchange_rate_to_merchant = CMulticurrency::getExchangeRate($merchant_default_currency, $currency_code);
			}

			$discount = $points * (1 / $points_redeemed_points);

			if ($points_id > 0) {
				if ($points_data = CPoints::getThresholdData($points_id)) {
					$points = $points_data['points'];
					$discount = $points_data['value'];
				}
			}

			$discount = $discount * $exchange_rate;
			$discount2 = $discount * $exchange_rate_to_merchant;

			CCart::setExchangeRate($exchange_rate_to_merchant);
			CCart::getContent($cart_uuid, Yii::app()->language);
			$subtotal = CCart::getSubTotal();
			$sub_total = floatval($subtotal['sub_total']);
			$total = floatval($sub_total) - floatval($discount2);
			if ($total <= 0) {
				$this->msg = t("Discount cannot be applied due to total less than zero after discount");
				$this->responseJson();
			}
			$params = [
				'name' => "Less Points",
				'type' => "points_discount",
				'target' => "subtotal",
				'value' => -$discount,
				'points' => $points
			];
			CCart::savedAttributes($cart_uuid, 'point_discount', json_encode($params));
			$this->code = 1;
			$this->msg = "Ok";
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionremovePoints()
	{
		try {

			$cart_uuid = CommonUtility::safeTrim(Yii::app()->input->post('cart_uuid'));
			CCart::deleteAttributesAll($cart_uuid, ['point_discount']);
			$this->code = 1;
			$this->msg = "ok";
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetpaydelivery()
	{
		try {
			$merchant_id = Yii::app()->request->getPost('merchant_id', 0);
			if ($merchant_id > 0) {
				$data = CPayments::PayondeliveryByMerchant($merchant_id);
			} else $data = CPayments::PayondeliveryList();

			$client_id = Yii::app()->user->id;
			$saved_payment = CPayments::getSavedpayondelivery($client_id, $merchant_id);

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'data' => $data,
				'saved_payment' => $saved_payment
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionsavedPaydelivery()
	{
		try {

			$payment_id = Yii::app()->input->post('payment_id');
			$payment_code = Yii::app()->input->post('payment_code');
			$merchant_id = Yii::app()->input->post('merchant_id');

			if ($payment_id <= 0) {
				$this->msg[] = t("Payment is required");
				$this->jsonResponse();
			}

			$payment = AR_payment_gateway::model()->find(
				'payment_code=:payment_code',
				array(':payment_code' => $payment_code)
			);
			if ($payment) {
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
				if ($model->save()) {
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

			$allergen = CMerchantMenu::getAllergens($merchant_id, $item_id);
			$allergen_data = AttributesTools::adminMetaList('allergens', Yii::app()->language, true);

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'allergen' => $allergen,
				'allergen_data' => $allergen_data,
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
				'order_id' => $order->order_id,
				'total' => Price_Formatter::formatNumber($order->total),
			];

			$model = AR_bank_deposit::model()->find("deposit_type=:deposit_type AND transaction_ref_id=:transaction_ref_id", [
				':deposit_type' => "order",
				':transaction_ref_id' => $order->order_id
			]);

			if ($model) {
				$data  = [
					'account_name' => $model->account_name,
					'amount' => $model->amount > 0 ? Price_Formatter::convertToRaw($model->amount) : 0,
					'reference_number' => $model->reference_number,
				];
			}

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'data' => $data,
				'order_info' => $order_info
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
			$reference_number = $reference_number == "undefined" ? "" : $reference_number;
			$filename = Yii::app()->input->post("filename");


			$file_data = Yii::app()->input->post("file_data");
			$image_type = Yii::app()->input->post("image_type");
			$image_type = !empty($image_type) ? $image_type : 'png';

			$order = COrders::get($order_uuid);

			$model = AR_bank_deposit::model()->find("deposit_type=:deposit_type AND transaction_ref_id=:transaction_ref_id", [
				':deposit_type' => "order",
				':transaction_ref_id' => $order->order_id
			]);
			if (!$model) {
				$model = new AR_bank_deposit;
				$model->scenario = "add";
			} else $model->scenario = "update";

			$file_uuid = CommonUtility::createUUID("{{bank_deposit}}", 'deposit_uuid');

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

			if (!empty($filename)) {
				$model->proof_image = $filename;
			} else {
				if (!empty($file_data)) {
					$result = [];
					try {
						$result = CImageUploader::saveBase64Image($file_data, $image_type, "upload/deposit");
						$model->proof_image = isset($result['filename']) ? $result['filename'] : '';
						$model->path = isset($result['path']) ? $result['path'] : '';
					} catch (Exception $e) {
						$this->msg = t($e->getMessage());
						$this->responseJson();
					}
				}
			}

			if ($model->validate()) {
				if ($model->save()) {
					$this->code = 1;
					$this->msg = t("You succesfully upload bank deposit. Please wait while we validate your payment");
				} else {
					$this->msg = CommonUtility::parseError($model->getErrors());
				}
			} else {
				$this->msg = CommonUtility::parseError($model->getErrors());
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
			$maxsize = (int) Yii::app()->params['upload_size'];

			if (!empty($_FILES)) {

				$title = $_FILES['file']['name'];
				$size = (int)$_FILES['file']['size'];
				$filetype = $_FILES['file']['type'];


				if (isset($_FILES['file']['name'])) {
					$extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
				} else $extension = strtolower(substr($title, -3, 3));

				if (!in_array($extension, $allowed_extension)) {
					$this->msg = t("Invalid file extension");
					$this->responseJson();
				}
				if ($size > $maxsize) {
					$this->msg = t("Invalid file size");
					$this->responseJson();
				}

				$upload_path = "upload/deposit";

				$tempFile = $_FILES['file']['tmp_name'];
				$upload_uuid = CommonUtility::createUUID("{{media_files}}", 'upload_uuid');
				$filename = $upload_uuid . ".$extension";
				$path = CommonUtility::uploadDestination($upload_path) . "/" . $filename;

				$image_set_width = isset(Yii::app()->params['settings']['review_image_resize_width']) ? intval(Yii::app()->params['settings']['review_image_resize_width']) : 0;
				$image_set_width = $image_set_width <= 0 ? 300 : $image_set_width;

				$image_driver = !empty(Yii::app()->params['settings']['image_driver']) ? Yii::app()->params['settings']['image_driver'] : Yii::app()->params->image['driver'];
				$manager = new ImageManager(array('driver' => $image_driver));
				$image = $manager->make($tempFile);
				$image_width = $manager->make($tempFile)->width();

				if ($image_width > $image_set_width) {
					$image->resize(null, $image_set_width, function ($constraint) {
						$constraint->aspectRatio();
					});
					$image->save($path);
				} else {
					$image->save($path, 60);
				}

				$this->code = 1;
				$this->msg = "OK";
				$this->details = array(
					'url_image' => CMedia::getImage($filename, $upload_path),
					'filename' => $filename,
					'id' => $upload_uuid
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

			$options = OptionsTools::find(array('signup_enabled_verification', 'signup_enabled_capcha'));
			$signup_enabled_capcha = isset($options['signup_enabled_capcha']) ? $options['signup_enabled_capcha'] : false;
			$capcha = $signup_enabled_capcha == 1 ? true : false;

			$enabled_verification = isset($options['signup_enabled_verification']) ? $options['signup_enabled_verification'] : false;
			$verification = $enabled_verification == 1 ? true : false;

			$recaptcha_response = isset($this->data['recaptcha_response']) ? $this->data['recaptcha_response'] : '';

			$firstname = isset($this->data['first_name']) ? $this->data['first_name'] : '';
			$lastname = isset($this->data['last_name']) ? $this->data['last_name'] : '';
			$email_address = isset($this->data['email_address']) ? $this->data['email_address'] : '';
			$prefix = isset($this->data['mobile_prefix']) ? $this->data['mobile_prefix'] : '';
			$mobile_number = isset($this->data['mobile_number']) ? $this->data['mobile_number'] : '';
			$password = isset($this->data['password']) ? $this->data['password'] : '';
			$cpassword = isset($this->data['cpassword']) ? $this->data['cpassword'] : '';

			$model = new AR_clientsignup();
			$model->scenario = $social_strategy;
			$model->capcha = false;
			$model->recaptcha_response = $recaptcha_response;

			if (!empty($password) || !empty($email_address)) {

				$model2 = new AR_clientsignup();
				$model2->scenario = "guest_with_account";
				$model2->first_name = $firstname;
				$model2->last_name = $lastname;
				$model2->contact_phone = $prefix . $mobile_number;
				$model2->email_address = $email_address;
				$model2->guest_password = $password;
				$model2->cpassword = $cpassword;
				$model2->password = $password;
				$model2->social_strategy = "web";
				$model2->merchant_id = 0;
				$model2->capcha = $capcha;
				$model2->recaptcha_response = $recaptcha_response;

				$digit_code = CommonUtility::generateNumber(4, true);
				$model2->mobile_verification_code = $digit_code;

				if ($verification == 1 || $verification == true) {
					$model2->status = 'pending';
				}
				if ($model2->save()) {
					if ($verification == 1 || $verification == true) {
						$this->code = 1;
						$this->msg = t("Please wait until we redirect you");
						$this->details = [
							'uuid' => $model2->client_uuid,
						];
					} else {
						$login = new AR_customer_login;
						$login->username = $email_address;
						$login->password = $password;
						$login->rememberMe = 1;
						if ($login->validate() && $login->login()) {
							$this->code = 1;
							$this->msg = t("Registration successful");
							$this->userData();
						} else $this->msg = t("Login failed");
					}
				} else $this->msg = CommonUtility::parseError($model2->getErrors());
			} else {
				$model->first_name = $firstname;
				$model->last_name = $lastname;
				$model->phone_prefix = $prefix;
				$model->contact_phone = $prefix . $mobile_number;

				$username = CommonUtility::uuid() . "@gmail.com";
				$password = CommonUtility::generateAplhaCode(20);
				$model->social_strategy = $social_strategy;
				$model->password = $password;
				$model->email_address = $username;
				if ($model->save()) {
					$login = new AR_customer_login;
					$login->username = $model->email_address;
					$login->password = $password;
					$login->rememberMe = 1;
					if ($login->validate() && $login->login()) {
						$this->code = 1;
						$this->msg = t("Registration successful");
						$this->userData();
					} else $this->msg = t("Login failed");
				} else $this->msg = CommonUtility::parseError($model->getErrors());
			}
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionSaveAddress()
	{
		try {

			$address_format_use = isset(Yii::app()->params['settings']['address_format_use']) ? (!empty(Yii::app()->params['settings']['address_format_use']) ? Yii::app()->params['settings']['address_format_use'] : '') : '';
			$address_format_use = !empty($address_format_use) ? $address_format_use : 1;

			//$address_uuid = isset($this->data['address_uuid'])?trim($this->data['address_uuid']):'';
			$address_uuid = Yii::app()->input->post('address_uuid');
			$address1 = Yii::app()->input->post('address1');
			$street_number = Yii::app()->input->post('street_number');
			$city = Yii::app()->input->post('city');
			$formatted_address = Yii::app()->input->post('formatted_address');
			$location_name = Yii::app()->input->post('location_name');
			$delivery_instructions = Yii::app()->input->post('delivery_instructions');
			$delivery_options = Yii::app()->input->post('delivery_options');
			$address_label = Yii::app()->input->post('address_label');
			$latitude = Yii::app()->input->post('latitude');
			$longitude = Yii::app()->input->post('longitude');
			$place_id = Yii::app()->input->post('place_id');
			$address2 = Yii::app()->input->post('address2');
			$postal_code = Yii::app()->input->post('postal_code');
			$company = Yii::app()->input->post('company');
			$country = Yii::app()->input->post('country');
			$country_code = Yii::app()->input->post('country_code');
			$custom_field1 = Yii::app()->input->post('custom_field1');
			$custom_field2 = Yii::app()->input->post('custom_field2');
			$credentials = CMaps::config();
			MapSdk::$map_provider = $credentials['provider'];
			
			// Try to find place, but allow coordinates-only addresses if geocoding fails
			$place = array();
			if(!empty($formatted_address)){
				try {
					$place = MapSdk::findPlace($formatted_address);
				} catch (Exception $e) {
					// If geocoding fails, allow saving with coordinates only
					$place = array();
				}
			}
			
			// Find existing address by UUID, place_id, or coordinates
			if (!empty($address_uuid)) {
				$model = AR_client_address::model()->find(
					'address_uuid=:address_uuid AND client_id=:client_id',
					array(':address_uuid' => $address_uuid, 'client_id' => Yii::app()->user->id)
					);
			} else if(!empty($place_id)) {
				$model = AR_client_address::model()->find('place_id=:place_id AND client_id=:client_id', 
				array(':place_id'=>$place_id,'client_id'=>Yii::app()->user->id));	
			} else if(!empty($latitude) && !empty($longitude)) {
				// Allow finding by coordinates if place_id is not available
				$model = AR_client_address::model()->find(
					'latitude=:latitude AND longitude=:longitude AND client_id=:client_id', 
					array(
						':latitude'=>$latitude,
						':longitude'=>$longitude,
						'client_id'=>Yii::app()->user->id
					)
				);	
			} else {
				$model = null;
			}			

			if(!$model){		    
				$model = new AR_client_address;		    	
			}
		
			$model->client_id = intval(Yii::app()->user->id);
			$model->address1 = $address1;
			$model->street_number = $street_number;
			$model->city = $city;
			$model->postal_code = $postal_code;
			$model->formatted_address = $formatted_address;
			$model->location_name = $location_name;
			$model->delivery_instructions = $delivery_instructions;
			$model->delivery_options = $delivery_options;
			$model->address_label = $address_label;
			$model->latitude = $latitude;
			$model->longitude = $longitude;
			// Allow empty place_id for coordinates-only addresses (e.g., Ivory Coast)
			$model->place_id = !empty($place_id) ? $place_id : '';
			// Ensure address_type is set for map-based addresses
			$model->address_type = 'map-based';
			$model->country = $country;
			$model->country_code = $country_code;
			$model->address_format_use = $address_format_use;
			$model->custom_field1 = $custom_field1;
			$model->custom_field2 = $custom_field2;

			if ($address_format_use == 2) {
				$model->scenario = "forms2";
				$model->address2 = $address2;
				$model->postal_code = $postal_code;
				$model->company = $company;
			} else {
				$model->address2 = $address2;
				$model->postal_code = $postal_code;
			}

			if ($model->save()) {
				$this->code = 1;
				$this->details = array(
					'place'=>$place,
					// 'place_details'=>$place_details,
				);
				$this->msg = "OK";
			} else $this->msg = CommonUtility::parseError($model->getErrors());
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

			$result = CCart::validateFoodItems($item_id, $cart_uuid, Yii::app()->language);
			$plural1 = "We regret to inform you that the {food_items} you had in your cart is no longer available.";
			$plural2 = "We regret to inform you that the following item(s) {food_items} you had in your cart is no longer available.";
			$message = Yii::t(
				'front',
				"$plural1|$plural2",
				array($result['count'], '{food_items}' => $result['item_line'])
			);
			$this->code = 1;
			$this->msg = $message;
			$this->details = $result;

			// REMOVE ITEM FROM CART
			$model = AR_cart::model()->find("cart_uuid=:cart_uuid AND item_token=:item_token", [
				':cart_uuid' => $cart_uuid,
				':item_token' => $item_id
			]);
			if ($model) {
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

			$atts = OptionsTools::find(['booking_enabled'], $merchant_id);
			$booking_enabled = isset($atts['booking_enabled']) ? $atts['booking_enabled'] : false;
			$booking_enabled = $booking_enabled == 1 ? true : false;

			$room_list = [];
			$room_list = CommonUtility::getDataToDropDown("{{table_room}}", "room_uuid", "room_name", "WHERE merchant_id=" . q($merchant_id) . " ", "order by room_name asc");
			if (is_array($room_list) && count($room_list) >= 1) {
				$room_list = CommonUtility::ArrayToLabelValue($room_list);
			}

			$table_list = [];
			try {
				$table_list = CBooking::getTableList($merchant_id);
			} catch (Exception $e) {
			}

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'room_list' => $room_list,
				'table_list' => $table_list,
				'booking_enabled' => $booking_enabled
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetwalletbalance()
	{
		try {
			$total = CDigitalWallet::getAvailableBalance(Yii::app()->user->id);
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'total' => Price_Formatter::formatNumber($total),
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetWalletTransaction()
	{
		$data = array();
		$card_id = 0;
		try {
			$card_id = CWallet::getCardID(Yii::app()->params->account_type['digital_wallet'], Yii::app()->user->id);
		} catch (Exception $e) {
			$this->msg = t("Invalid card id");
			$this->responseJson();
		}

		$limit = 10;
		$page = intval(Yii::app()->input->post('page'));
		$page_raw = intval(Yii::app()->input->post('page'));
		$transaction_type = Yii::app()->input->post('transaction_type');
		if ($page > 0) {
			$page = $page - 1;
		}

		$criteria = new CDbCriteria();
		$criteria->alias = "a";

		if ($transaction_type == "all") {
			$criteria->addCondition("a.card_id=:card_id");
			$criteria->params = array(
				':card_id' => intval($card_id)
			);
		} else {
			$criteria->addCondition("a.card_id=:card_id AND b.meta_name=:meta_name");
			$criteria->join = "LEFT JOIN {{wallet_transactions_meta}} b on  a.transaction_id = b.transaction_id";
			$criteria->params = array(
				':card_id' => intval($card_id),
				':meta_name' => $transaction_type
			);
		}
		$criteria->order = "a.transaction_id DESC";

		$count = AR_wallet_transactions::model()->count($criteria);
		$pages = new CPagination($count);
		$pages->pageSize = $limit;
		$pages->setCurrentPage($page);
		$pages->applyLimit($criteria);
		$page_count = $pages->getPageCount();

		if ($page > 0) {
			if ($page_raw > $page_count) {
				$this->code = 3;
				$this->msg = t("end of results");
				$this->responseJson();
			}
		}

		$models = AR_wallet_transactions::model()->findAll($criteria);
		if ($models) {
			foreach ($models as $item) {
				$description = Yii::app()->input->xssClean($item->transaction_description);
				$parameters = json_decode($item->transaction_description_parameters, true);
				if (is_array($parameters) && count($parameters) >= 1) {
					$description = t($description, $parameters);
				}

				$transaction_amount = 0;
				$transaction_type = '';
				switch ($item->transaction_type) {
					case "debit":
						$transaction_amount = "-" . Price_Formatter::formatNumber($item->transaction_amount);
						$transaction_type = 'debit';
						break;
					default:
						$transaction_amount = "+" . Price_Formatter::formatNumber($item->transaction_amount);
						$transaction_type = 'credit';
						break;
				}

				$data[] = [
					'transaction_date' => Date_Formatter::dateTime($item->transaction_date),
					'transaction_type' => $transaction_type,
					'transaction_description' => $description,
					'transaction_amount' => $transaction_amount,
				];
			}
			$this->code = $page_raw == $page_count ? 3 : 1;
			$this->msg = "Ok";
			$this->details = [
				'page_raw' => $page_raw,
				'page_count' => $page_count,
				'data' => $data
			];
		} else $this->msg = t("No results");
		$this->responseJson();
	}

	public function actiongetPointsthresholds()
	{
		try {

			$customer_id = Yii::app()->user->id;
			$cart_uuid = CommonUtility::safeTrim(Yii::app()->input->post('cart_uuid'));
			$currency_code = CommonUtility::safeTrim(Yii::app()->input->post('currency_code'));
			$base_currency = Price_Formatter::$number_format['currency_code'];
			$exchange_rate = 1;
			$exchange_rate_to_merchant = 1;

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled']) ? Yii::app()->params['settings']['multicurrency_enabled'] : false;
			$multicurrency_enabled = $multicurrency_enabled == 1 ? true : false;
			$merchant_id = CCart::getMerchantId($cart_uuid);
			$options_merchant = OptionsTools::find(['merchant_default_currency'], $merchant_id);
			$merchant_default_currency = isset($options_merchant['merchant_default_currency']) ? $options_merchant['merchant_default_currency'] : '';
			$merchant_default_currency = !empty($merchant_default_currency) ? $merchant_default_currency : $base_currency;
			$currency_code = !empty($currency_code) ? $currency_code : (empty($merchant_default_currency) ? $base_currency : $merchant_default_currency);

			if ($multicurrency_enabled) {
				Price_Formatter::init($currency_code);
				$exchange_rate = CMulticurrency::getExchangeRate($base_currency, $currency_code);
				$exchange_rate_to_merchant = CMulticurrency::getExchangeRate($merchant_default_currency, $currency_code);
			}

			$data = CPoints::getThresholds($exchange_rate);

			$redemption_policy = isset(Yii::app()->params['settings']['points_redemption_policy']) ? Yii::app()->params['settings']['points_redemption_policy'] : 'universal';
			$balance = CPoints::getAvailableBalancePolicy($customer_id, $redemption_policy, $merchant_id);

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'balance' => $balance,
				'data' => $data
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetDiscount()
	{
		try {

			$transaction_type = Yii::app()->input->post('transaction_type');
			$data = AttributesTools::getDiscount($transaction_type, date("Y-m-d"));
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = $data;
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetCustomerDefaultPayment()
	{
		try {

			$data = [];
			$data = CPayments::defaultPaymentOnline(Yii::app()->user->id);

			$this->code = 1;
			$this->msg = "ok";
			$this->details = array(
				'data' => $data,
			);
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionprepareaddfunds()
	{
		try {

			$topup_minimum = isset(Yii::app()->params['settings']['digitalwallet_topup_minimum']) ? floatval(Yii::app()->params['settings']['digitalwallet_topup_minimum']) : 1;
			$topup_maximum = isset(Yii::app()->params['settings']['digitalwallet_topup_maximum']) ? floatval(Yii::app()->params['settings']['digitalwallet_topup_maximum']) : 10000;

			$merchant_id = 0;
			$merchant_type = 2;
			$payment_details = [];
			$payment_description = '';

			$amount = floatval(Yii::app()->input->post('amount'));
			$isWeb =  Yii::app()->request->getPost('isWeb', null);  
			$request_slug =  Yii::app()->request->getPost('slug', null);  
			

			if ($amount < $topup_minimum) {
				$this->msg = t("Top-up amount should meet the minimum requirement of {{topup_minimum}} for a successful transaction. The maximum allowed is {{topup_maximum}}.", [
					'{{topup_minimum}}' => Price_Formatter::formatNumber($topup_minimum),
					'{{topup_maximum}}' => Price_Formatter::formatNumber($topup_maximum)
				]);
				$this->responseJson();
			}
			if ($amount > $topup_maximum) {
				$this->msg = t("Top-up amount exceeds the maximum limit of {{topup_maximum}} for a single transaction. The minimum required is {{topup_minimum}}.", [
					'{{topup_minimum}}' => Price_Formatter::formatNumber($topup_minimum),
					'{{topup_maximum}}' => Price_Formatter::formatNumber($topup_maximum)
				]);
				$this->responseJson();
			}

			$original_amount = $amount;
			$transaction_amount = $amount;
			//$payment_code = Yii::app()->input->post('payment_code');
			$payment_uuid = Yii::app()->input->post('payment_uuid');
			$currency_code = Yii::app()->input->post('currency_code');
			$base_currency = Price_Formatter::$number_format['currency_code'];
			$currency_code = $base_currency;

			$exchange_rate = 1;
			$merchant_base_currency = $base_currency;
			$admin_base_currency = $base_currency;
			$exchange_rate_merchant_to_admin = $exchange_rate;
			$exchange_rate_admin_to_merchant = $exchange_rate;

			$payment_model = CPayments::getPaymentInfo($payment_uuid);
			$payment_code = $payment_model['payment_code'] ?? '';
			$payment_name = $payment_model['payment_name'] ?? '';

			$payments_credentials = CPayments::getPaymentCredentials(0,'',2);
			$credentials = isset($payments_credentials[$payment_code])?$payments_credentials[$payment_code]:'';

			$payment_description_raw = "Funds Added via {payment_name}";
			$transaction_description_parameters = [
				'{payment_name}' => $payment_name
			];
			$payment_description = t($payment_description_raw, $transaction_description_parameters);

			if ($payment_code == "stripe") {
				$payment_details = CPayments::getPaymentMethodMeta($payment_uuid, Yii::app()->user->id);
			}

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled']) ? Yii::app()->params['settings']['multicurrency_enabled'] : false;
			$multicurrency_enabled = $multicurrency_enabled == 1 ? true : false;
			$enabled_checkout_currency = isset(Yii::app()->params['settings']['multicurrency_enabled_checkout_currency']) ? Yii::app()->params['settings']['multicurrency_enabled_checkout_currency'] : false;
			$enabled_force = $multicurrency_enabled == true ? ($enabled_checkout_currency == 1 ? true : false) : false;

			if ($enabled_force && $multicurrency_enabled) {
				if ($force_result = CMulticurrency::getForceCheckoutCurrency($payment_code, $currency_code)) {
					$currency_code = $force_result['to_currency'];
					$amount = Price_Formatter::convertToRaw($amount * $force_result['exchange_rate'], 2);
				}
			}

			$customer = ACustomer::get(Yii::app()->user->id);
			$customer_details = [
				'client_id' => $customer->client_id,
				'first_name' => $customer->first_name,
				'last_name' => $customer->last_name,
				'email_address' => $customer->email_address,
				"contact_phone" => str_replace($customer->phone_prefix, "", $customer->contact_phone),
			];

			$data = [
				'payment_code' => $payment_code,
				'payment_uuid' => $payment_uuid,
				'payment_name' => $payment_name,
				'merchant_id' => $merchant_id,
				'merchant_type' => $merchant_type,
				'payment_description' => $payment_description,
				'payment_description_raw' => $payment_description_raw,
				'transaction_description_parameters' => $transaction_description_parameters,
				'amount' => $amount,
				'currency_code' => $currency_code,
				'transaction_amount' => $transaction_amount,
				'orig_transaction_amount' => $original_amount,
				'merchant_base_currency' => $currency_code,
				'merchant_base_currency' => $merchant_base_currency,
				'admin_base_currency' => $admin_base_currency,
				'exchange_rate_merchant_to_admin' => $exchange_rate_merchant_to_admin,
				'exchange_rate_admin_to_merchant' => $exchange_rate_admin_to_merchant,
				'payment_details' => $payment_details,
				'customer_details' => $customer_details,
				'payment_type' => "add_funds",
				'reference_id' => CommonUtility::createUUID("{{wallet_transactions}}", 'transaction_uuid'),
				'payment_url' => CommonUtility::getHomebaseUrl() . "/$payment_code/api/processpayment",
				'transaction_date' => Date_Formatter::dateTime(date('c')),
				'successful_url' => CommonUtility::getHomebaseUrl() . "/$payment_code/api/successful",
				'failed_url' => CommonUtility::getHomebaseUrl() . "/$payment_code/api/failed",
				'cancel_url' => CommonUtility::getHomebaseUrl() . "/$payment_code/api/cancel",
				'credentials'=>$credentials
			];
			$details = JWT::encode($data, CRON_KEY, 'HS256');

			$return_url = Yii::app()->request->getPost('return_url', null);
			$return_url =  rtrim($return_url, "/");
			$request_from = Yii::app()->request->getPost('request_from', 'app');
			$params = [
				'request_from' => $request_from,
				'return_url' => $return_url,
				'request_slug'=>$request_slug,
				'data' => $details
			];		
			
			$payment_url = CommonUtility::getHomebaseUrl() . "/$payment_code/api/processpayment?" . http_build_query($params);			

			$payment_instructions = Yii::app()->getModule($payment_code)->paymentInstructions();			

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'payment_code' => $payment_code,
				'data' => $details,
				'amount' => $amount,
				'currency_code' => $currency_code,
				'transaction_amount' => $transaction_amount,
				'payment_uuid' => $payment_uuid,
				'payment_url' => $payment_url,
				'payment_instructions'=>$payment_instructions
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetPaymentCredentials()
	{
		try {

			$payments_credentials = CPayments::getPaymentCredentialsPublic(0, '', 2);
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = $payments_credentials;
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetCartWallet()
	{
		try {

			$cart_uuid = CommonUtility::safeTrim(Yii::app()->input->post('cart_uuid'));
			$currency_code = CommonUtility::safeTrim(Yii::app()->input->post('currency_code'));
			$base_currency = Price_Formatter::$number_format['currency_code'];
			$exchange_rate = 1;
			$exchange_rate_to_merchant = 1;

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled']) ? Yii::app()->params['settings']['multicurrency_enabled'] : false;
			$multicurrency_enabled = $multicurrency_enabled == 1 ? true : false;

			if (!empty($currency_code) && $multicurrency_enabled) {
				Price_Formatter::init($currency_code);
				if ($currency_code != $base_currency) {
					$exchange_rate = CMulticurrency::getExchangeRate($base_currency, $currency_code);
				}
			}

			$atts = CCart::getAttributesAll($cart_uuid, ['use_wallet']);
			$use_wallet = isset($atts['use_wallet']) ? $atts['use_wallet'] : false;
			$use_wallet = $use_wallet == 1 ? true : false;

			$balance_raw = CDigitalWallet::getAvailableBalance(Yii::app()->user->id);
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'balance_raw' => ($balance_raw * $exchange_rate),
				'balance' => Price_Formatter::formatNumber(($balance_raw * $exchange_rate)),
				'use_wallet' => $balance_raw > 0 ? $use_wallet : false
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionapplyDigitalWallet()
	{
		try {

			$cart_uuid = CommonUtility::safeTrim(Yii::app()->input->post('cart_uuid'));
			$use_wallet = intval(Yii::app()->input->post('use_wallet'));
			$use_wallet = $use_wallet == 1 ? true : false;
			$amount_to_pay = floatval(Yii::app()->input->post('amount_to_pay'));
			$currency_code = CommonUtility::safeTrim(Yii::app()->input->post('currency_code'));
			$base_currency = Price_Formatter::$number_format['currency_code'];
			$exchange_rate = 1;
			$exchange_rate_to_merchant = 1;

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled']) ? Yii::app()->params['settings']['multicurrency_enabled'] : false;
			$multicurrency_enabled = $multicurrency_enabled == 1 ? true : false;

			$transaction_limit = isset(Yii::app()->params['settings']['digitalwallet_transaction_limit']) ? floatval(Yii::app()->params['settings']['digitalwallet_transaction_limit']) : 0;

			if (!empty($currency_code) && $multicurrency_enabled) {
				Price_Formatter::init($currency_code);
				if ($currency_code != $base_currency) {
					$exchange_rate = CMulticurrency::getExchangeRate($base_currency, $currency_code);
				}
			}

			$balance_raw = CDigitalWallet::getAvailableBalance(Yii::app()->user->id);
			$balance_raw = ($balance_raw * $exchange_rate);
			$amount_due = CDigitalWallet::calculateAmountDue($amount_to_pay, $balance_raw);

			if ($transaction_limit > 0 && $use_wallet) {
				if ($balance_raw > $transaction_limit) {
					$this->msg = t("Transaction amount exceeds wallet spending limit.");
					$this->responseJson();
				}
			}

			$message = t("Looks like this order is higher than your digital wallet credit.");
			$message .= "\n";
			$message .= t("Please select a payment method below to cover the remaining amount.");

			CCart::savedAttributes($cart_uuid, 'use_wallet', $use_wallet);

			$this->code = 1;
			$this->msg = $amount_due > 0 ? $message : '';
			$this->details = [
				'use_wallet' => $use_wallet,
				'balance_raw' => $balance_raw,
				'amount_to_pay' => $amount_to_pay,
				'amount_due_raw' => $amount_due,
				'amount_due' => Price_Formatter::formatNumber($amount_due),
				'pay_remaining' => t("Pay remaining {amount}", [
					'{amount}' => Price_Formatter::formatNumber($amount_due)
				])
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}
	public function actiongetMerchantSlug(){
		try {
			$merchant_id = isset($this->data['merchant_id'])?$this->data['merchant_id']:'';
			$merchant = CMerchantListingV1::getMerchant($merchant_id);
			$this->code = 1; $this->msg = "ok";			
				$this->details = array(		     
				'merchant'=>$merchant['restaurant_slug'],       
				);
		} catch (Exception $e) {
			$this->msg= t($e->getMessage());				
		}	
		$this->responseJson();
	}


	public function actionqrcode()
	{
		try {
			$data = Yii::app()->input->get("data");
			$qrcode  = new QRCode;
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = $qrcode->render($data);
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionrequestOTP()
	{
		try {

			$validation_type = Yii::app()->input->post("validation_type");
			$email_address = Yii::app()->input->post("email_address");
			$mobile_number = Yii::app()->input->post('mobile_number');
			$mobile_prefix = Yii::app()->input->post('mobile_prefix');
			$mobile_prefix = str_replace("+",'',$mobile_prefix);
			$contact_phone = $mobile_prefix . $mobile_number;
			$success_message = '';

			if ($validation_type == "email") {
				$model = AR_clientsignup::model()->find('email_address=:email_address', array(':email_address' => $email_address));
				$success_message = "An OTP has been sent to your email address {{email_address}}";
			} else {
				$model = AR_clientsignup::model()->find('contact_phone=:contact_phone', array(':contact_phone' => $contact_phone));
				$success_message = "An OTP has been sent to your phone number {{mobile_number}}";
			}

			if ($model) {
				$model->scenario = $validation_type == "email" ? 'request_otp_email' : 'request_otp_sms';
				$model->reset_password_request = 1;
				$model->verify_code_requested = CommonUtility::dateNow();
				$model->mobile_verification_code =  CommonUtility::generateNumber(4, true);
				if ($model->save()) {
					$this->code = 1;
					$this->msg = t($success_message, array(
						'{{mobile_number}}' => CommonUtility::mask($contact_phone),
						'{{email_address}}' => CommonUtility::maskEmail($email_address),
					));
					$this->details = array(
						'uuid' => $model->client_uuid,
					);
				} else $this->msg = CommonUtility::parseError($model->getErrors());
			} else $this->msg = t("We couldn't find any account associated with the provided phone number.");
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionresendOTP()
	{
		try {

			$validation_type = Yii::app()->input->post("validation_type");
			$client_uuid = Yii::app()->input->post("uuid");
			$success_message = '';

			$model = AR_clientsignup::model()->find('client_uuid=:client_uuid', array(':client_uuid' => $client_uuid));
			if ($model) {
				if ($validation_type == "email") {
					$success_message = "An OTP has been sent to your email address {{email_address}}";
				} else {
					$success_message = "An OTP has been sent to your phone number {{mobile_number}}";
				}
				$model->scenario = $validation_type == "email" ? 'request_otp_email' : 'request_otp_sms';
				$model->reset_password_request = 1;
				$model->verify_code_requested = CommonUtility::dateNow();
				$model->mobile_verification_code =  CommonUtility::generateNumber(4, true);
				if ($model->save()) {
					$this->code = 1;
					$this->msg = t($success_message, array(
						'{{mobile_number}}' => CommonUtility::mask($model->contact_phone),
						'{{email_address}}' => CommonUtility::maskEmail($model->email_address),
					));
					$this->details = array(
						'uuid' => $model->client_uuid,
					);
				} else $this->msg = CommonUtility::parseError($model->getErrors());
			} else $this->msg = t("We couldn't find any account associated with the provided phone number.");
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionverifyOTP()
	{
		try {

			$client_uuid = Yii::app()->request->getPost('uuid', null);
			$otp = Yii::app()->request->getPost('otp', null);

			if (!$client_uuid) {
				$this->msg = t("Missing required parameters in the request.");
				$this->responseJson();
			}
			if (!$otp) {
				$this->msg = t("OTP is required");
				$this->responseJson();
			}

			$model = AR_clientsignup::model()->find('client_uuid=:client_uuid', array(':client_uuid' => $client_uuid));
			if ($model) {
				if ($model->reset_password_request == 1) {
					if (CommonUtility::safeTrim($model->mobile_verification_code) === CommonUtility::safeTrim($otp)) {
						$this->code = 1;
						$this->msg = t(Helper_success);
						$this->details = array(
							'uuid' => $model->client_uuid,
						);
					} else $this->msg = t("The OTP code you entered is invalid. Please double-check the code and try again.");
				} else $this->msg = t("Account has no request for OTP");
			} else $this->msg = t(HELPER_RECORD_NOT_FOUND);
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionresetPassword()
	{
		try {

			$client_uuid = Yii::app()->input->post('uuid');
			$password = Yii::app()->input->post('password');
			$cpassword = Yii::app()->input->post('cpassword');

			$model = AR_client::model()->find(
				'client_uuid=:client_uuid',
				array(':client_uuid' => $client_uuid)
			);

			if ($model) {
				if ($model->reset_password_request <= 0) {
					$this->msg = t("It seems that you are attempting to change your password, but there is no record of a password reset request associated with your account.");
					$this->responseJson();
				}
				if ($model->status == "active") {
					$model->scenario = "reset_password";
					$model->npassword =  $password;
					$model->cpassword =  $cpassword;
					$model->password = md5($password);
					$model->reset_password_request = 0;

					if ($model->save()) {
						$this->code = 1;
						$this->msg  = t("Your password is now updated.");
					} else $this->msg =  CommonUtility::parseError($model->getErrors());;
				} else $this->msg = t("Account not active");
			} else $this->msg = t("Records not found");
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionvalidatePayment()
	{
		try {

			require 'stripe/vendor/autoload.php';

			$order_uuid = Yii::app()->input->post('order_uuid');

			Yii::log("KHANA validatePayment => $order_uuid", CLogger::LEVEL_ERROR);

			$order = COrders::get($order_uuid);
			$payment_code = $order->payment_code;
			$merchant_id = $order->merchant_id;
			$merchants = CMerchantListingV1::getMerchant($merchant_id);
			$credentials = CPayments::getPaymentCredentials($merchant_id, $payment_code, $merchants->merchant_type);

			$credentials = isset($credentials[$payment_code]) ? $credentials[$payment_code] : '';
			$secret_key = isset($credentials['attr1']) ? $credentials['attr1'] : '';

			$meta = COrders::getMeta($order->order_id, 'stripe_session_id');
			$stripe_session_id = $meta ? $meta->meta_value : null;

			$stripe = new \Stripe\StripeClient($secret_key);
			$result = $stripe->checkout->sessions->retrieve(
				$stripe_session_id,
				[]
			);

			Yii::log("PAYMENT STATUS => $result->payment_status", CLogger::LEVEL_ERROR);
			Yii::log("ORDER STATUS => $order->status", CLogger::LEVEL_ERROR);

			if ($result->payment_status != 'unpaid') {
				$this->code = 1;
				$this->msg = "Ok";
				$this->details = [
					'order_uuid' => $order->order_uuid,
				];
			} else {
				if ($order->status != "draft") {
					$this->code = 1;
					$this->msg = "Ok";
					$this->details = [
						'order_uuid' => $order->order_uuid,
					];
				} else {
					$this->msg = t("Payment is not succesful with the status of {status}", [
						'{status}' => $result->payment_status
					]);
				}
			}
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}

		Yii::log("MESSAGE => $this->msg", CLogger::LEVEL_ERROR);

		$this->responseJson();
	}

	public function actiontrackOrder()
	{
		try {
			$order_uuid = Yii::app()->input->get('order_uuid');
			$progress = CTrackingOrder::getProgress($order_uuid, date("Y-m-d g:i:s a"));
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'data' => $progress
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionCancelOrder()
	{
		try {

			$order_uuid = Yii::app()->input->post('order_uuid');

			$cancel = AR_admin_meta::getValue('status_cancel_order');
			$cancel_status = isset($cancel['meta_value']) ? $cancel['meta_value'] : 'cancelled';
			$cancel2 = AR_admin_meta::getValue('status_delivery_cancelled');
			$cancel_status2 = isset($cancel2['meta_value']) ? $cancel2['meta_value'] : 'cancelled';
			$reason = "Customer cancel this order";

			$order = COrders::get($order_uuid);
			$resp = COrders::getCancelStatus($order_uuid);

			if ($resp['payment_type'] == "online") {
				$order->scenario = "cancel_order";
				if ($order->status == $cancel_status) {
					$this->msg = t("This order has already been cancelled");
					$this->responseJson();
				}
				$order->status = $cancel_status;
				$order->delivery_status  = $cancel_status2;
				$order->remarks = $reason;
				if ($order->save()) {
					$this->code = 1;
					$this->msg = t("Your order is now cancel. your refund is on its way.");
					if (!empty($reason)) {
						COrders::savedMeta($order->order_id, 'rejetion_reason', $reason);
					}
				} else $this->msg = CommonUtility::parseError($order->getErrors());
			} else {
				$order->scenario = "cancel_order";
				if ($order->status == $cancel_status) {
					$this->msg = t("This order has already been cancelled");
					$this->responseJson();
				}
				$order->status = $cancel_status;
				$order->delivery_status  = $cancel_status2;
				$order->remarks = $reason;
				if ($order->save()) {
					$this->code = 1;
					$this->msg = t("Your order is now cancel.");
					if (!empty($reason)) {
						COrders::savedMeta($order->order_id, 'rejetion_reason', $reason);
					}
				} else $this->msg = CommonUtility::parseError($order->getErrors());
			}
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionreviewattributes()
	{
		try {

			$data = AOrders::rejectionList('delivery_like_options', Yii::app()->language);
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = $data;
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetOrdertoreview()
	{
		try {

			$order_uuid = Yii::app()->input->get('order_uuid');
			$model = COrders::get($order_uuid);
			$this->code = 1;
			$this->msg = "Ok";

			$data_like_options = null;
			try {
				$data_like_options = AOrders::rejectionList('delivery_like_options', Yii::app()->language);
			} catch (Exception $e) {
			}

			$driver_info = null;
			try {
				$driver_info = CDriver::getDriverInfo($model->driver_id);
			} catch (Exception $e) {
			}

			$merchant = null;
			try {
				$merchant = CMerchants::get($model->merchant_id);
			} catch (Exception $e) {
			}

			$order_info = [
				'order_id' => $model->order_id,
				'driver_id' => $model->driver_id,
				'hows_your_order' => t("How was the delivery of your order from {restaurant_name}?", [
					'{restaurant_name}' => $merchant ? $merchant->restaurant_name : ''
				])
			];

			$is_driver_review = true;
			if ($model->service_code == "delivery") {
				$is_driver_review = CReviews::driverReviewDetails($model->client_id, $model->order_id);
			}
			$is_review = CReviews::reviewDetails($model->client_id, $model->order_id);

			$is_driver_review = $is_driver_review ? true : false;
			$is_review = $is_review ? true : false;

			$this->details = [
				'order_info' => $order_info,
				'driver_info' => $driver_info,
				'is_driver_review' => $is_driver_review,
				'is_review' => $is_review,
				'data_like_options' => $data_like_options
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionaddRiderReview()
	{
		try {

			$customer_id = Yii::app()->user->id;
			$rating = Yii::app()->input->post("rating");
			$review_text = Yii::app()->input->post("review_text");
			$order_id = Yii::app()->input->post("order_id");
			$driver_id = Yii::app()->input->post("driver_id");

			COrders::getByID($order_id);

			$model = AR_driver_reviews::model()->find("order_id=:order_id AND customer_id=:customer_id AND driver_id=:driver_id", [
				':order_id' => $order_id,
				':customer_id' => $customer_id,
				':driver_id' => $driver_id,
			]);
			if ($model) {
				$this->msg = t("You already added review for this order");
				$this->responseJson();
			}
			$model = new AR_driver_reviews();
			$model->order_id = $order_id;
			$model->customer_id = $customer_id;
			$model->driver_id = $driver_id;
			$model->rating = $rating;
			$model->review_text = $review_text;
			if ($model->save()) {
				$this->code = 1;
				$this->msg = t("Thank you for sharing your experience!");
			} else $this->msg = CommonUtility::parseError($model->getErrors());
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetCustomFields()
	{
		try {
			if ($fields = AttributesTools::getCustomFields()) {
				$this->code = 1;
				$this->msg = "Ok";
				$this->details = [
					'fields' => $fields
				];
			} else $this->msg = t(HELPER_NO_RESULTS);
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionfetchHomeData()
	{
		try {

			$currency_code = CommonUtility::safeTrim(Yii::app()->input->post('currency_code'));
			$base_currency = Price_Formatter::$number_format['currency_code'];
			$exchange_rate = 1;

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled']) ? Yii::app()->params['settings']['multicurrency_enabled'] : false;
			$multicurrency_enabled = $multicurrency_enabled == 1 ? true : false;

			if ($multicurrency_enabled) {
				Price_Formatter::init($currency_code);
				$exchange_rate = CMulticurrency::getExchangeRate($base_currency, $currency_code);
			}

			$data = [];
			$best_weekly = [];
			$best_monthly = [];
			$best_yearly = [];
			try {
				$data = CPoints::getThresholds($exchange_rate, true);
			} catch (Exception $e) {
			}

			try {
				$today = new DateTime();
				$today->modify('last monday');
				$startOfLastWeek = $today->format('Y-m-d 00:00:00');
				$endOfLastWeek = $today->modify('next sunday')->format('Y-m-d 00:00:00');
				$best_weekly = CPoints::getCustomerPointsbyRange($startOfLastWeek, $endOfLastWeek);
			} catch (Exception $e) {
			}

			try {
				$today = new DateTime();
				$today->modify('first day of this month');
				$startOfLastMonth = $today->format('Y-m-d 00:00:00');
				$endOfLastMonth = $today->modify('last day of this month')->format('Y-m-d 00:00:00');
				$best_monthly = CPoints::getCustomerPointsbyRange($startOfLastMonth, $endOfLastMonth);
			} catch (Exception $e) {
			}

			try {
				$today = new DateTime();
				$startOfThisYear = $today->modify('first day of January this year')->format('Y-m-d 00:00:00');
				$endOfThisYear = $today->modify('last day of December this year')->format('Y-m-d 00:00:00');
				$best_yearly = CPoints::getCustomerPointsbyRange($startOfThisYear, $endOfThisYear);
			} catch (Exception $e) {
			}


			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'threshold_data' => $data,
				'best_weekly' => $best_weekly,
				'best_monthly' => $best_monthly,
				'best_yearly' => $best_yearly
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetuserpointsbalance()
	{
		try {
			$customer_id = Yii::app()->user->id;
			$redemption_policy = 'universal';
			$merchant_id = 0;
			$balance = CPoints::getAvailableBalancePolicy($customer_id, $redemption_policy, $merchant_id);
			$redeem_thresholds = [];
			if ($res =  CPoints::getUserRedeemthresholds($balance, AttributesTools::pointsThresholds())) {
				$redeem_thresholds = $res;
			}

			$card_id = CWallet::createCard(Yii::app()->params->account_type['customer_points'], $customer_id);
			$points_expiry = isset(Yii::app()->params['settings']['points_expiry']) ? Yii::app()->params['settings']['points_expiry'] : null;
			$points_expiry = !$points_expiry ? 1 : $points_expiry;

			$points_earn_expiry = CPoints::getExpiryPoints($points_expiry, $card_id);

			$ranks = [];
			try {
				$ranks = CPoints::getUserRank($customer_id);
			} catch (Exception $e) {
			}

			$card_id = CWallet::createCard(Yii::app()->params->account_type['customer_points'], $customer_id);
			$bunos_points = CPoints::getMonthlyBunosPoints($card_id);

			// GET CUSTOMER SUMMARY TRANSACTIONS
			$points_summary = CPoints::getUserSummaryTransaction($card_id);

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'balance' => $balance,
				'redeem_thresholds' => $redeem_thresholds,
				'ranks' => $ranks,
				'bunos_points' => Price_Formatter::formatDistance($bunos_points),
				'points_earn_expiry' => $points_earn_expiry,
				'points_summary' => $points_summary
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionuploadaudioOld()
	{
		try {
			Yii::log("UPLOAD AUDIO", CLogger::LEVEL_INFO);
			if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['audioFile'])) {
				$upload_path = "upload/audio";
				$uploadDir = CommonUtility::uploadDestination($upload_path);
				$uploadFile = $uploadDir . "/" . basename($_FILES['audioFile']['name']);
				if (move_uploaded_file($_FILES['audioFile']['tmp_name'], $uploadFile)) {
					$this->msg = t("File uploaded successfully!");
				} else {
					$this->msg = t("Failed to upload file.");
				}
			} else $this->msg = "No file uploaded.";
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionuploadaudio()
	{
		try {
			$input = json_decode(file_get_contents('php://input'), true);
			if (!empty($input['audioBase64']) && !empty($input['fileName'])) {
				$audioBase64 = $input['audioBase64'];
				$fileName = $input['fileName'];
				$audioData = base64_decode($audioBase64);

				$fileSizeInBytes = strlen($audioData);
				$fileSizeInMB = $fileSizeInBytes / (1024 * 1024);

				if ($fileSizeInMB > 3) {
					$this->msg = t("File size exceeds the 3MB limit.");
					$this->responseJson();
				}

				$upload_path = "upload/audio";
				$uploadDir = CommonUtility::uploadDestination($upload_path);
				$filePath = "$uploadDir/$fileName";
				if (file_put_contents($filePath, $audioData)) {
					$this->code = 1;
					$this->msg = t("File uploaded successfully!");
					$file_url = Yii::app()->createAbsoluteUrl("/") . "/$upload_path/$fileName";
					$this->details = [
						'filename' => $fileName,
						'file_url' => $file_url,
						'fileSizeInMB' => CommonUtility::HumanFilesize($fileSizeInMB)
					];
				} else {
					$this->msg = t("Failed to save the file.");
				}
			} else $this->msg = t("Invalid input data.");
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionfetchWallettransactions()
	{
		try {

			$transaction_id =  Yii::app()->request->getQuery('transaction_id', '');
			$model = AR_wallet_transactions::model()->find('reference_id=:reference_id', [
				':reference_id' => $transaction_id
			]);
			if ($model) {
				$payment = json_decode($model->transaction_description_parameters, true);
				$this->code = 1;
				$this->msg = "Ok";
				$this->details = [
					'payment_name' => t("{payment_name}", (array)$payment),
					'transaction_id' => $model->reference_id,
					'amount' => Price_Formatter::formatNumber($model->transaction_amount),
					'amount_raw' => $model->transaction_amount,
					'transaction_date' => Date_Formatter::dateTime($model->transaction_date)
				];
			}
			$this->msg = t(HELPER_RECORD_NOT_FOUND);
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionfetchMonthlyData()
	{
		try {

			$client_id = Yii::app()->user->id;
			$currency_code = Yii::app()->request->getPost('currency_code', '');
			$base_currency = Price_Formatter::$number_format['currency_code'];
			$exchange_rate = 1;

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled']) ? Yii::app()->params['settings']['multicurrency_enabled'] : false;
			$multicurrency_enabled = $multicurrency_enabled == 1 ? true : false;

			if ($multicurrency_enabled) {
				Price_Formatter::init($currency_code);
				$exchange_rate = CMulticurrency::getExchangeRate($base_currency, $currency_code);
			}


			$today = new DateTime();
			$monthly_points = 0;
			$monthly_bonus_pts = 0;
			$expiry_date = $today->modify('last day of this month')->format('m/d/Y');

			$todays = new DateTime('now');
			$lastDayOfMonth = new DateTime('last day of this month');
			$remaining_days = $todays->diff($lastDayOfMonth)->days;

			try {
				$card_id = CWallet::createCard(Yii::app()->params->account_type['customer_points'], $client_id);
				$today->modify('first day of this month');
				$startOfLastMonth = $today->format('Y-m-d');
				$endOfLastMonth = $today->modify('last day of this month')->format('Y-m-d');
				$monthly_points = CPoints::getCustomerEarnPointsbyRange($card_id, $startOfLastMonth, $endOfLastMonth);
				$monthly_bonus_pts = CPoints::getBunosPointsByRange($card_id, $startOfLastMonth, $endOfLastMonth);
				$monthly_points = $monthly_points - $monthly_bonus_pts;
				$monthly_points = Price_Formatter::formatDistance($monthly_points);
			} catch (Exception $e) {
			}


			$monthly_bonus_pts = Price_Formatter::formatDistance($monthly_bonus_pts);
			$monthly_bonus_amount = 0;

			if ($redeem_resp =  CPoints::getUserRedeemthresholds($monthly_bonus_pts, AttributesTools::monthlyThresholds())) {
				$monthly_bonus_amount = $redeem_resp['redeeming_value_pretty'];
			}

			$redeem_thresholds = [];
			try {
				$redeem_thresholds = CPoints::getThresholds($exchange_rate);
			} catch (Exception $e) {
			}

			$monthly_thresholds = [];
			$monthly_data = [];
			try {
				$monthly_data = CPoints::getMonthlyThresholds($exchange_rate, true);
				foreach ($monthly_data as $items) {
					if ($items['points'] <= 0) {
						continue;
					}
					$next_reward = $items['points'] - $monthly_points;
					$extra_points = Price_Formatter::formatDistance($items['amount_raw']);
					$points_value = CPoints::checkPointsThreshold($redeem_thresholds, $extra_points);
					$progress = $items['points'] > 0 ? min($monthly_points / $items['points'], 1) : 0;
					$monthly_thresholds[] = [
						'monthly_points' => $monthly_points,
						'points_earning_month' => Price_Formatter::formatDistance($items['points']),
						'extra_points' => $extra_points,
						'next_reward' => $next_reward,
						'next_reward_pretty' => t("{point} pt to Next Rewards", [
							'{point}' => $next_reward
						]),
						'progresss' => $progress,
						'points_value' => $points_value
					];
				}
			} catch (Exception $e) {
			}

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'monthly_expiry_date' => [
					'raw' => $expiry_date,
					'label' => t("expired by {expiry_date}", [
						'{expiry_date}' => $expiry_date
					])
				],
				'remaining_days' => [
					'raw' => $remaining_days,
					'label' => Yii::t('front', 'Remaining {n} day|Remaining {n} days', $remaining_days)
				],
				'monthly_bonus_pts' => $monthly_bonus_pts,
				'monthly_bonus_amount' => $monthly_bonus_amount,
				'monthly_points' => $monthly_points,
				'monthly_data' => $monthly_data,
				'monthly_thresholds' => $monthly_thresholds
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetFeaturedItems()
	{
		try {

			$currency_code =  Yii::app()->request->getQuery('currency_code', '');
			$cart_uuid =  Yii::app()->request->getQuery('cart_uuid', '');
			$lat =  Yii::app()->request->getQuery('lat', null);
			$lng =  Yii::app()->request->getQuery('lng', null);
			$base_currency = Price_Formatter::$number_format['currency_code'];

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled']) ? Yii::app()->params['settings']['multicurrency_enabled'] : false;
			$multicurrency_enabled = $multicurrency_enabled == 1 ? true : false;

			$exchange_rate = 1;
			$currency_code = !empty($currency_code) ? $currency_code : $base_currency;
			// SET CURRENCY
			if (!empty($currency_code) && $multicurrency_enabled) {
				Price_Formatter::init($currency_code);
				if ($currency_code != $base_currency) {
					$exchange_rate = CMulticurrency::getExchangeRate($base_currency, $currency_code);
				}
			}

			$promos = []; $subtotal= 0; $cart_merchant_id ='';
			try {
				$cart_merchant_id = CCart::getMerchantId($cart_uuid);
			    $promos = CMerchantMenu::getItemActivePromo($cart_merchant_id);
				if(is_array($promos) && count($promos)>=1){
					$subtotal = CCart::getCartSubtotal($cart_uuid,$promos);
				}
			} catch (Exception $e) {}

			CMerchantMenu::setExchangeRate($exchange_rate);
			$data = CMerchantMenu::getFeaturedItems(Yii::app()->language, [
				'cart_merchant_id'=>$cart_merchant_id,
				'subtotal'=>$subtotal,
				'lat' => $lat,
				'lng' => $lng,
			]);

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'data' => $data
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionsearchsuggestion()
	{
		try {

			$q =  Yii::app()->request->getQuery('q', '');
			$data = CMerchantListingV1::searchSuggest($q, Yii::app()->language);
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'data' => $data
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionOrderList()
	{
		try {

			CommonUtility::mysqlSetTimezone();
			$page =  Yii::app()->request->getQuery('page', 1);
			$order_type =  Yii::app()->request->getQuery('order_type', 'recent');
			$limit = Yii::app()->params->list_limit;
			$today = date('Y-m-d');

			$criteria = new CDbCriteria();
			$criteria->alias = "order";
			$criteria->select = "
			order.order_id,
			order.order_uuid,
			order.merchant_id,
			order.driver_id,
			restaurant.restaurant_name,
			restaurant.logo,
			restaurant.path,
			order.total,
			order.base_currency_code,
			order.use_currency_code,
			order.exchange_rate,
			order.status,
			order.whento_deliver,
			order.delivery_date,
			order.delivery_time,
			order.delivery_status,
			order.service_code,
			order.whento_deliver,
			IFNULL(review.rating,0) AS ratings,
			IFNULL(ordermeta.meta_value,0) AS earn_points,
			order.date_created,
			IFNULL(driver.rating,0) AS driver_ratings
			";
			$criteria->join = "
			LEFT JOIN {{merchant}} restaurant
			ON order.merchant_id = restaurant.merchant_id

			LEFT JOIN {{review}} review
		    ON
		    order.order_id = review.order_id

			LEFT JOIN {{driver_reviews}} driver
		    ON
		    order.order_id = driver.order_id

			LEFT JOIN {{ordernew_meta}} ordermeta
		    ON
		    order.order_id = ordermeta.order_id
			AND meta_name='points_to_earn'
			";

			$criteria->condition = "order.client_id=:client_id";
			$criteria->params = [
				':client_id' => Yii::app()->user->id,
			];

			$criteria->addNotInCondition("order.status", ['pending', 'draft']);

			if ($order_type == "recent") {
				$criteria->addCondition("order.date_created >= CURDATE() - INTERVAL 6 DAY");
			} else {
				$criteria->addCondition("order.date_created < CURDATE() - INTERVAL 6 DAY");
			}

			$criteria->limit = $limit;
			$criteria->offset = ($page - 1) * $limit;
			$criteria->order = 'date_created DESC';

			$orders = AR_ordernew::model()->findAll($criteria);
			$totalOrders = AR_ordernew::model()->count($criteria);

			if ($orders) {
				$this->code = 1;
				$this->msg = "Ok";
				$price_list_format = [];
				$price_list = CMulticurrency::getAllCurrency();
				$status_list = AttributesTools::StatusList(Yii::app()->language);
				$status_allowed_review = AOrderSettings::getStatus(array('status_delivered', 'status_completed'));
				$failed_status = AOrderSettings::getSettingsStatus([
					'tracking_status_delivery_failed',
					'status_cancel_order',
					'tracking_status_failed',
					'status_rejection',
					'status_delivery_fail',
					'status_failed'
				]);

				$tracking_stats = AR_admin_meta::getMeta(array(
					'tracking_status_process',
					'tracking_status_in_transit',
					'tracking_status_delivered',
					'tracking_status_delivery_failed',
					'status_new_order',
					'status_cancel_order',
					'status_rejection',
					'status_delivered',
					'tracking_status_ready',
					'tracking_status_completed',
					'tracking_status_failed',
					'status_order_pickup'
				));

				$tracking_status_process = isset($tracking_stats['tracking_status_process']) ? AttributesTools::cleanString($tracking_stats['tracking_status_process']['meta_value']) : '';
				$tracking_status_in_transit = isset($tracking_stats['tracking_status_in_transit']) ? AttributesTools::cleanString($tracking_stats['tracking_status_in_transit']['meta_value']) : '';
				$status_new_order = isset($tracking_stats['status_new_order']) ? AttributesTools::cleanString($tracking_stats['status_new_order']['meta_value']) : '';
				$tracking_status_ready = isset($tracking_stats['tracking_status_ready']) ? AttributesTools::cleanString($tracking_stats['tracking_status_ready']['meta_value']) : '';

				$trackingStatus[] = $tracking_status_process;
				$trackingStatus[] = $tracking_status_in_transit;
				$trackingStatus[] = $status_new_order;
				$trackingStatus[] = $tracking_status_ready;

				foreach ($orders as $items) {
					$progress = [];
					$is_order_ongoing = in_array($items->status, (array)$trackingStatus) ? true : false;
					if ($is_order_ongoing) {
						$progress = CTrackingOrder::getProgress($items->order_uuid, date("Y-m-d g:i:s a"));
					}

					$base_currency_code = $items->base_currency_code;
					$use_currency_code = $items->use_currency_code;
					$exchange_rate = 1;
					if ($base_currency_code != $use_currency_code) {
						$exchange_rate = floatval($items->exchange_rate);
					}
					if (isset($price_list[$use_currency_code])) {
						$price_list_format = $price_list[$use_currency_code];
					} else $price_list_format = Price_Formatter::$number_format;


					$show_review = in_array($items->status, (array) $status_allowed_review) && $items->ratings <= 0;
					$show_review_delivery = in_array($items->status, (array) $status_allowed_review) && $items->driver_ratings <= 0  && $items->driver_id > 0;
					$show_status = in_array($items->status, (array)$failed_status);
					$earn_points = $items->earn_points > 0 ? t("+{points} points", ['{points}' => $items->earn_points]) : '';


					$data[] = [
						'order_id' => $items->order_id,
						'merchant_id' => $items->merchant_id,
						'order_uuid' => $items->order_uuid,
						'driver_id' => $items->driver_id,
						'restaurant_name' => CommonUtility::safeDecode($items->restaurant_name),
						'customer_name' => $items->merchant_id,
						'merchant_logo' => CMedia::getImage($items->logo, $items->path, CommonUtility::getPlaceholderPhoto('merchant_logo')),
						'total' => Price_Formatter::formatNumber2(($items->total * $exchange_rate), $price_list_format),
						'status' => isset($status_list[$items->status]) ? $status_list[$items->status] : $items->status,
						'earn_points' => $earn_points,
						'show_review' => $show_review,
						'show_review_delivery' => $show_review_delivery,
						'ratings' => $items->ratings,
						'driver_ratings' => $items->driver_ratings,
						'show_status' => $show_status,
						'date_created' => Date_Formatter::dateTime($items->date_created),
						'is_order_ongoing' => $is_order_ongoing,
						'progress' => $progress,
						'share_experience' => t("Rate Your {restaurant_name} Experience!", [
							'{restaurant_name}' => CommonUtility::safeDecode($items->restaurant_name)
						]),
						'share_driver_experience' => t("How was the delivery of your order from {restaurant_name}?", [
							'{restaurant_name}' => CommonUtility::safeDecode($items->restaurant_name)
						]),
					];
				}
				$this->details = [
					'data' => $data,
					'total' => $totalOrders,
				];
			} else {
				$this->code = $page > 1 ? 3 : 2;
				$this->msg = t(HELPER_NO_RESULTS);
			}
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionSearchOrder()
	{
		try {
			$order_id =  Yii::app()->request->getQuery('q', null);
			$data = COrders::SearchOrder(Yii::app()->user->id, $order_id);
			$this->code = 1;
			$this->msg = "Ok";
			$this->details['data'] = $data;
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionSavedAddress()
	{
		try {

			$address_uuid = $this->data['address_uuid'] ?? null;
			$street_number = $this->data['street_number'] ?? '';
			$street_name = $this->data['street_name'] ?? '';
			$location_name = $this->data['location_name'] ?? '';
			$address_label = $this->data['address_label'] ?? '';
			$delivery_instructions = $this->data['delivery_instructions'] ?? '';
			$place_id = $this->data['place_id'] ?? '';
			$formatted_address = $this->data['formatted_address'] ?? '';
			$city = $this->data['city'] ?? '';
			$state = $this->data['state'] ?? '';
			$postal_code = $this->data['postal_code'] ?? '';
			$country = $this->data['country'] ?? '';
			$latitude = $this->data['latitude'] ?? '';
			$longitude = $this->data['longitude'] ?? '';
			$delivery_options = $this->data['delivery_options'] ?? '';

			if ($address_uuid) {
				$model = AR_client_address::model()->find('client_id=:client_id AND address_uuid=:address_uuid', [
					':client_id' => Yii::app()->user->id,
					':address_uuid' => $address_uuid
				]);
				if (!$model) {
					$model = new AR_client_address();
				}
			} else $model = new AR_client_address();

			$model->client_id = Yii::app()->user->id;
			$model->address_type = 'map-based';
			$model->place_id = $place_id;
			$model->address1 = $street_number;
			$model->city = $city;
			$model->state = $state;
			$model->country = $country;
			$model->formatted_address = $street_name;
			$model->formattedAddress = $formatted_address;
			$model->latitude = $latitude;
			$model->longitude = $longitude;
			$model->location_name = $location_name;
			$model->delivery_options = $delivery_options;
			$model->delivery_instructions = $delivery_instructions;
			$model->address_label = $address_label;
			if ($model->save()) {

				$place_data = [
					'address_uuid' => $model->address_uuid,
					'street_number' => $street_number,
					'street_name' => $street_name,
					'city' => $city,
					'state' => $state,
					'postal_code' => $postal_code,
					'country' => $country,
					'formatted_address' => $formatted_address,
					'address_label' => $address_label,
					'latitude' => $latitude,
					'longitude' => $longitude,
					'place_id' => $place_id,
				];

				$this->code = 1;
				$this->msg = t("Address saved");
				$this->details = [
					'place_data' => $place_data
				];
			} else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionfetchCustomerAddresses()
	{
		try {

			if (Yii::app()->user->isGuest) {
				$this->msg = t("Session has expired");
				$this->responseJson();
			}

			$client_id = Yii::app()->user->id;
			$results = CClientAddress::fecthAddresses($client_id);
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'data' => $results
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionsetDeliveryNow()
	{
		try {

			$cart_uuid =  Yii::app()->request->getPost('cart_uuid', null);
			if (!$cart_uuid) {
				$cart_uuid = CommonUtility::createUUID("{{cart}}", 'cart_uuid');
			}

			CCart::savedAttributes($cart_uuid, 'whento_deliver', 'now');
			CCart::deleteAttributesAll($cart_uuid, [
				'delivery_time',
				'delivery_date'
			]);
			$this->code = 1;
			$this->msg = "OK";
			$this->details = [
				'cart_uuid'=>$cart_uuid
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionsetDeliveryTime()
	{
		try {

			$cart_uuid = $this->data['cart_uuid'] ?? null;
			$delivery_time = $this->data['delivery_time'] ?? null;
			$delivery_date = $this->data['delivery_date'] ?? null;

			if (!$cart_uuid) {
				$cart_uuid = CommonUtility::createUUID("{{cart}}", 'cart_uuid');
			}
			if (!$delivery_time) {
				$this->msg = t("Invalid delivery time");
				$this->responseJson();
			}
			if (!$delivery_date) {
				$this->msg = t("Invalid delivery date");
				$this->responseJson();
			}

			CCart::savedAttributes($cart_uuid, 'whento_deliver', 'schedule');
			CCart::savedAttributes($cart_uuid, 'delivery_date', $delivery_date);
			CCart::savedAttributes($cart_uuid, 'delivery_time', json_encode($delivery_time));
			$this->code = 1;
			$this->msg = "OK";
			$this->details = [
				'cart_uuid'=>$cart_uuid
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionsetTransactionType()
	{
		try {

			$cart_uuid =  Yii::app()->request->getPost('cart_uuid', null);
			$transaction_type =  Yii::app()->request->getPost('transaction_type', null);
			if (!$cart_uuid) {
				$cart_uuid = CommonUtility::createUUID("{{cart}}", 'cart_uuid');
			}
			if (!$transaction_type) {
				$this->msg = t("Invalid Transaction type");
				$this->responseJson();
			}

			CCart::savedAttributes($cart_uuid, 'transaction_type', $transaction_type);
			$this->code = 1;
			$this->msg = "OK";
			$this->details = [
				'cart_uuid'=>$cart_uuid
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionaddTip()
	{
		try {

			$merchant_id =  Yii::app()->request->getPost('merchant_id', null);
			$cart_uuid =  Yii::app()->request->getPost('cart_uuid', null);
			$currency_code =  Yii::app()->request->getPost('currency_code', null);
			$tip_value =  Yii::app()->request->getPost('tip_value', null);

			$base_currency = Price_Formatter::$number_format['currency_code'];
			$multicurrency_enabled = Yii::app()->params['settings']['multicurrency_enabled'] ?? false;
			$multicurrency_enabled = $multicurrency_enabled == 1 ? true : false;
			$exchange_rate = 1;

			$options_merchant = OptionsTools::find(['merchant_enabled_tip', 'merchant_default_currency'], $merchant_id);
			$merchant_default_currency = $options_merchant['merchant_default_currency'] ?? '';
			$merchant_default_currency = !empty($merchant_default_currency) ? $merchant_default_currency : $base_currency;
			$enabled_tip = $options_merchant['merchant_enabled_tip'] ?? false;
			$enabled_tip = $enabled_tip == 1 ? true : false;

			$currency_code = !empty($currency_code) ? $currency_code : (empty($merchant_default_currency) ? $base_currency : $merchant_default_currency);

			// SET CURRENCY
			if (!empty($currency_code) && $multicurrency_enabled) {
				if ($currency_code != $merchant_default_currency) {
					$exchange_rate = CMulticurrency::getExchangeRate($currency_code, $merchant_default_currency);
				}
			}

			if ($enabled_tip) {
				CCart::savedAttributes($cart_uuid, 'tips', $tip_value);
				$this->code = 1;
				$this->msg = "OK";
			} else $this->msg = t("Tip are disabled");
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionfetchpromo()
	{
		try {

			$merchant_id = Yii::app()->request->getPost('merchant_id', null);
			$currency_code = Yii::app()->request->getPost('currency_code', '');
			$client_uuid = Yii::app()->request->getPost('client_uuid', null);
			$client_id = null;
			if (!$merchant_id) {
				$this->msg = t("Invalid merchant id");
				$this->responseJson();
			}

			$points_enabled = Yii::app()->params['settings']['points_enabled'] ?? false;
			$points_enabled = $points_enabled == 1 ? true : false;

			$base_currency = Price_Formatter::$number_format['currency_code'];
			$multicurrency_enabled = Yii::app()->params['settings']['multicurrency_enabled'] ?? false;
			$multicurrency_enabled = $multicurrency_enabled == 1 ? true : false;
			$exchange_rate = 1;

			$options_merchant = OptionsTools::find(['merchant_default_currency'], $merchant_id);
			$merchant_default_currency = $options_merchant['merchant_default_currency'] ?? '';
			$merchant_default_currency = !empty($merchant_default_currency) ? $merchant_default_currency : $base_currency;
			$currency_code = !empty($currency_code) ? $currency_code : (empty($merchant_default_currency) ? $base_currency : $merchant_default_currency);

			if ($points_enabled && ($meta = AR_merchant_meta::getValue($merchant_id, 'loyalty_points'))) {
				$points_enabled = $meta['meta_value'] == 1;
			}

			// SET CURRENCY
			if (!empty($currency_code) && $multicurrency_enabled) {
				Price_Formatter::init($currency_code);
				if ($currency_code != $merchant_default_currency) {
					$exchange_rate = CMulticurrency::getExchangeRate($merchant_default_currency, $currency_code);
				}
			}

			$redemption_policy = Yii::app()->params['settings']['points_redemption_policy'] ?? 'universal';

			try {
				$user = ACustomer::getUUID($client_uuid);
				$client_id = $user->client_id;
			} catch (Exception $e) {
			}

			CPromos::setExchangeRate($exchange_rate);
			$promo = CPromos::promo($merchant_id, date("Y-m-d"));

			$points_balance = 0;
			if ($client_id && $points_enabled) {
				$points_balance = CPoints::getAvailableBalancePolicy($client_id, $redemption_policy, $merchant_id);

				$attrs = OptionsTools::find(['points_redeemed_points', 'points_redeemed_value', 'points_maximum_redeemed', 'points_use_thresholds']);
				$points_use_thresholds = $attrs['points_use_thresholds'] ?? false;
				$points_use_thresholds = $points_use_thresholds == 1 ? true : false;

				$title = null;
				$sub_title = null;
				if ($points_use_thresholds) {
					$results = CPoints::getAvailablethresholds($points_balance);
					$title = $results['discount'] ?? null;
					$sub_title = $results['points_needed'] ?? null;
				} else {
					$points_redeemed_points = floatval($attrs['points_redeemed_points']) ?? 0;
					$amount = $points_redeemed_points * (1 / $points_redeemed_points);
					$amount = ($amount * $exchange_rate);
					$title = t("Get {amount} off for every {points} points", [
						'{amount}' => Price_Formatter::formatNumber(($amount)),
						'{points}' => $points_redeemed_points
					]);
					$sub_title = t("Your available points {balance}", ['{balance}' => $points_balance]);
				}

				if ($title && $sub_title) {
					$points_data = [
						'promo_type' => "points",
						'title' => $title,
						'sub_title' => $sub_title,
						'use_thresholds' => $points_use_thresholds,
						'balance' => $points_balance
					];
					if (!is_array($promo)) {
						$promo = [];
					}
					array_unshift($promo, $points_data);
				}
			}

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'data' => $promo
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionfetchpointsthresholds()
	{
		try {

			$currency_code =  Yii::app()->request->getQuery('currency_code', '');
			$merchant_id =  Yii::app()->request->getQuery('merchant_id', '');

			$base_currency = Price_Formatter::$number_format['currency_code'];
			$exchange_rate = 1;

			$multicurrency_enabled = Yii::app()->params['settings']['multicurrency_enabled'] ?? false;
			$multicurrency_enabled = $multicurrency_enabled == 1 ? true : false;

			$options_merchant = OptionsTools::find(['merchant_default_currency'], $merchant_id);
			$merchant_default_currency = isset($options_merchant['merchant_default_currency']) ? $options_merchant['merchant_default_currency'] : '';
			$merchant_default_currency = !empty($merchant_default_currency) ? $merchant_default_currency : $base_currency;
			$currency_code = !empty($currency_code) ? $currency_code : (empty($merchant_default_currency) ? $base_currency : $merchant_default_currency);

			if ($multicurrency_enabled) {
				Price_Formatter::init($currency_code);
				$exchange_rate = CMulticurrency::getExchangeRate($base_currency, $currency_code);
			}

			$data = CPoints::getThresholds($exchange_rate);

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'data' => $data
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionredeemPoints()
	{
		try {

			$merchant_id =  Yii::app()->request->getPost('merchant_id', null);
			$cart_uuid =  Yii::app()->request->getPost('cart_uuid', null);
			$points_id =  Yii::app()->request->getPost('points_id', null);
			$currency_code = Yii::app()->request->getPost('currency_code', '');
			$base_currency = Price_Formatter::$number_format['currency_code'];

			if (!$merchant_id) {
				$this->msg = t("Invalid merchant id");
				$this->responseJson();
			}
			if (!$cart_uuid) {
				$this->msg = t("Invalid cart id");
				$this->responseJson();
			}
			if (!$points_id) {
				$this->msg = t("Invalid points id");
				$this->responseJson();
			}

			$multicurrency_enabled = Yii::app()->params['settings']['multicurrency_enabled'] ?? false;
			$multicurrency_enabled = $multicurrency_enabled == 1 ? true : false;

			$options_merchant = OptionsTools::find(['merchant_default_currency'], $merchant_id);
			$merchant_default_currency = isset($options_merchant['merchant_default_currency']) ? $options_merchant['merchant_default_currency'] : '';
			$merchant_default_currency = !empty($merchant_default_currency) ? $merchant_default_currency : $base_currency;

			$currency_code = !empty($currency_code) ? $currency_code : (empty($merchant_default_currency) ? $base_currency : $merchant_default_currency);

			$exchange_rate = 1;
			$exchange_rate_to_merchant = 1;
			$admin_exchange_rate = 1;
			if (!empty($currency_code) && $multicurrency_enabled) {
				$exchange_rate = CMulticurrency::getExchangeRate($base_currency, $merchant_default_currency);
				$exchange_rate_to_merchant = CMulticurrency::getExchangeRate($merchant_default_currency, $currency_code);
			}

			$points_data = CPoints::getThresholdData($points_id);
			if (!$points_data) {
				$this->msg = t("Points data not found");
				$this->responseJson();
			}

			$points = $points_data['points'] ?? 0;
			$discount = $points_data['value'] ?? 0;

			$discount = $discount * $exchange_rate;
			$discount2 = $discount * $exchange_rate_to_merchant;

			CCart::setExchangeRate($exchange_rate_to_merchant);
			CCart::getContent($cart_uuid, Yii::app()->language);
			$subtotal = CCart::getSubTotal();
			$sub_total = floatval($subtotal['sub_total']) ?? 0;
			$total = floatval($sub_total) - floatval($discount2);
			if ($total <= 0) {
				$this->msg = t("Discount cannot be applied due to total less than zero after discount");
				$this->responseJson();
			}

			$params = [
				'name' => "Less Points",
				'type' => "points_discount",
				'target' => "subtotal",
				'value' => -$discount,
				'points' => $points
			];

			CCart::savedAttributes($cart_uuid, 'point_discount', json_encode($params));
			$this->code = 1;
			$this->msg = "Ok";
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionfetchPayment()
	{
		try {

			$currency_code = Yii::app()->request->getQuery('currency_code', '');
			$data = null;
			$payments_credentials = array();

			try {
				$data = CPayments::DefaultPaymentList();
				$payments_credentials = CPayments::getPaymentCredentials(0, '', 2);
				foreach ($data as &$items) {
					$items['credentials'] = $payments_credentials[$items['payment_code']] ?? null;
				}
			} catch (Exception $e) {
			}

			$saved_payment = null;
			try {
				$saved_payment = CPayments::SavedPaymentList(Yii::app()->user->id, 0);
			} catch (Exception $e) {
			}

			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(
				'data' => $data,
				'saved_payment' => $saved_payment
			);
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionSavedpayment()
	{
		try {
			$data = CPayments::customerAllPayment(Yii::app()->user->id);
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'data' => $data
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionsetprimarypayment()
	{
		try {

			$payment_uuid =  Yii::app()->request->getPost('payment_uuid', null);
			$as_default =  Yii::app()->request->getPost('as_default', false);

			$model = AR_client_payment_method::model()->find(
				'client_id=:client_id AND payment_uuid=:payment_uuid',
				array(
					':client_id' => Yii::app()->user->id,
					':payment_uuid' => $payment_uuid
				)
			);
			if ($model) {
				$model->as_default = intval($as_default);
				$model->save();
				$this->code = 1;
				$this->msg = t("Succesful");
			} else $this->msg = t(HELPER_RECORD_NOT_FOUND);
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionfetchfavourites()
	{
		try {

			$limit = 20;
			$page = intval(Yii::app()->request->getQuery('page', 1));
			$page_raw =  intval(Yii::app()->request->getQuery('page', 1));
			$latitude = Yii::app()->request->getQuery('lat', null);
			$longitude = Yii::app()->request->getQuery('lng', null);

			if ($page > 0) {
				$page = $page - 1;
			}

			CommonUtility::mysqlSetTimezone();
			$todays_date = date("Y-m-d H:i");
			$today_now = strtolower(date("l", strtotime($todays_date)));
			$time_now = date("H:i", strtotime($todays_date));

			$criteria = new CDbCriteria();
			$criteria->alias = "a";
			$criteria->select = "
			a.id,
			a.merchant_id,
			m.restaurant_name,
			m.restaurant_slug,
			m.logo as photo,
			m.path,
			m.distance_unit,
			m.latitude,
			m.lontitude,
			m.close_store,
			m.disabled_ordering,
			m.pause_ordering,
			(
			    select option_value
				from {{option}}
				where option_name='free_delivery_on_first_order'
				and merchant_id = a.merchant_id
			) as free_delivery,

			(
			select count(*) from
			{{opening_hours}}
			where
			merchant_id = a.merchant_id
			and
			day=" . q($today_now) . "
			and
			status = 'open'
			and

			(
			CAST(" . q($time_now) . " AS TIME)
			BETWEEN CAST(start_time AS TIME) and CAST(end_time AS TIME)

			or

			CAST(" . q($time_now) . " AS TIME)
			BETWEEN CAST(start_time_pm AS TIME) and CAST(end_time_pm AS TIME)

			)
		   ) as merchant_open_status,

		    CASE
			WHEN m.pause_ordering = 1 THEN (
				SELECT mm.meta_value
				FROM {{merchant_meta}} mm
				WHERE mm.merchant_id = a.merchant_id
				AND meta_name='pause_reason'
				LIMIT 0,1
			)
			ELSE " . q(t('Currently unavailable')) . "
			END AS close_reason,

			CASE
			WHEN opening_hours.status = 'open'
			AND STR_TO_DATE(opening_hours.start_time, '%H:%i') > TIME(NOW())
			THEN CONCAT(opening_hours.start_time,';',opening_hours.end_time,';',opening_hours.day_of_week,';',opening_hours.day)

			ELSE (
			SELECT CONCAT(next_oh.start_time,';',next_oh.end_time,';',next_oh.day_of_week,';',next_oh.day)
			FROM {{opening_hours}} next_oh
			WHERE next_oh.merchant_id = a.merchant_id
				AND next_oh.status = 'open'
				AND (
					next_oh.day_of_week > DAYOFWEEK(NOW()) - 1  -- Find next day
					OR (next_oh.day_of_week = 0 AND DAYOFWEEK(NOW()) != 1)  -- Handle Sunday properly
					)
			ORDER BY next_oh.day_of_week ASC
			LIMIT 1
			)
			END AS next_opening_hours

			";
			$criteria->join = "
			LEFT JOIN {{merchant}} m on a.merchant_id = m.merchant_id

			LEFT JOIN {{opening_hours}} opening_hours
			ON a.merchant_id = opening_hours.merchant_id
			AND opening_hours.day_of_week = DAYOFWEEK(NOW()) - 1

			";
			$criteria->addCondition("a.client_id=:client_id AND fav_type=:fav_type");
			$criteria->params = array(
				':client_id' => Yii::app()->user->id,
				':fav_type' => 'restaurant'
			);
			$criteria->order = "a.id DESC";

			$count = AR_favorites::model()->count($criteria);
			$pages = new CPagination($count);
			$pages->pageSize = $limit;
			$pages->setCurrentPage($page);
			$pages->applyLimit($criteria);
			$page_count = $pages->getPageCount();

			$is_last_page = false;
			if ($page_raw > $page_count) {
				$is_last_page = true;
			}

			$merchant_ids = null;
			$delivery_time_estimation = 0;
			$distance = 0;
			$estimation_time = '';

			if ($models = AR_favorites::model()->findAll($criteria)) {
				foreach ($models as $items) {

					if ($distance_resp = CMaps::getLocalDistance($items->distance_unit, $latitude, $longitude, $items->latitude, $items->lontitude)) {
						$distance = floatval($distance_resp);
						$delivery_time_estimation = CommonUtility::distanceToTime($distance);
						$preparation_time_estimation = CCart::totalPreparationTime($items->merchant_id, date("Y-m-d"));
						$total_estimate_time = $preparation_time_estimation + $delivery_time_estimation;
						$estimation_time = CommonUtility::addTimeBuffer($total_estimate_time);
					}

					$available = true;
					if ($items->close_store == 1 || $items->merchant_open_status == 0 || $items->disabled_ordering == 1 || $items->pause_ordering) {
						$available = false;
					}

					/*next_opening*/
					$next_opening = '';
					$today_day_of_week = date('N');
					if ($items->merchant_open_status == 0) {
						$currentDate = new DateTime();
						$next_opening_hours = !empty($items->next_opening_hours) ? explode(';', $items->next_opening_hours) : null;
						if (!empty($next_opening_hours)) {
							$start_time = isset($next_opening_hours[0]) ? $next_opening_hours[0] : '';
							$end_time = isset($next_opening_hours[1]) ? $next_opening_hours[1] : '';
							$day_of_week = isset($next_opening_hours[2]) ? $next_opening_hours[2] : '';

							$daysToAdd = ($day_of_week > $today_day_of_week)
								? ($day_of_week - $today_day_of_week)
								: (7 - $today_day_of_week + $day_of_week);
							$openingDate = Date_Formatter::date($currentDate->modify("+$daysToAdd days")->format('Y-m-d'), "E", true);

							$startTime = DateTime::createFromFormat('H:i', $start_time)->format('h:i A');
							$endTime = DateTime::createFromFormat('H:i', $end_time)->format('h:i A');
							$next_opening = t("Opens [day] at [time]", [
								'[day]' => $openingDate,
								'[time]' => "$startTime - $endTime"
							]);
						}
					}

					$merchant_ids[] = $items->merchant_id;
					$logo_url = !empty($items->photo) ? CMedia::getImage($items->photo, $items->path) : '';
					$data[$items->merchant_id] = [
						'merchant_id' => $items->merchant_id,
						'restaurant_slug' => $items->restaurant_slug,
						'restaurant_name' => $items->restaurant_name,
						'url_logo' => $logo_url,
						'saved_store' => 1,
						'free_delivery' => $items->free_delivery == 1 ? true : false,
						'estimation2' => $estimation_time,
						'distance' => $distance,
						'distance_short' => "$distance $items->distance_unit",
						'distance_pretty' => t("{{distance} {{unit}}", [
							'{{distance}' => $distance,
							'{{unit}}' => MapSdk::prettyUnit($items->distance_unit)
						]),
						'open_status' => true,
						'is_last_page' => $is_last_page,
						'available' => $available,
						'close_reason' => $items->close_reason,
						'next_opening' => $next_opening,
					];
				}

				try {
					$promo_list = CPromos::getAvaialblePromoNew($merchant_ids, CommonUtility::dateOnly());
					foreach ($promo_list as $merchantId => $promo_item) {
						if (isset($data[$merchantId])) {
							$data[$merchantId]['promos'] = $promo_item;
						}
					}
				} catch (Exception $e) {
				}

				if ($cuisine_list = CMerchantListingV1::getCuisineNew($merchant_ids, Yii::app()->language)) {
					foreach ($cuisine_list as $cuisine_item) {
						$merchantId = $cuisine_item['merchant_id'];
						if (isset($data[$merchantId])) {
							$data[$merchantId]['cuisine'][] = CommonUtility::safeDecode($cuisine_item['cuisine_name']);
						}
					}
				}

				try {
					$reviews_list = CMerchantListingV1::getReviews($merchant_ids);
					foreach ($reviews_list as $merchantId => $reviews_items) {
						if (isset($data[$merchantId])) {
							$data[$merchantId]['reviews'] = $reviews_items;
						}
					}
				} catch (Exception $e) {
				}

				$data = array_values($data);

				$this->code = $page_raw == $page_count ? 3 : 1;
				$this->msg = "Ok";
				$this->details = [
					'page_raw' => $page_raw,
					'page_count' => $page_count,
					'data' => $data
				];
			} else $this->msg = t(HELPER_NO_RESULTS);
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionfetchfavouritesitems()
	{
		try {

			$limit = 20;
			$page = intval(Yii::app()->request->getQuery('page', 1));
			$page_raw =  intval(Yii::app()->request->getQuery('page', 1));
			$latitude = Yii::app()->request->getQuery('lat', null);
			$longitude = Yii::app()->request->getQuery('lng', null);
			$exchange_rate = 1;

			if ($page > 0) {
				$page = $page - 1;
			}

			$criteria = new CDbCriteria();
			$criteria->alias = "a";
			$criteria->select = "
			a.id,
			a.cat_id,
			a.item_id,
			a.merchant_id,

			item.item_token,
			item.slug as item_slug,
			item.photo,
			item.path,
			CASE
				WHEN item_trans.item_name IS NOT NULL AND item_trans.item_name != '' THEN item_trans.item_name
				ELSE item.item_name
			END AS item_name,

			CASE
				WHEN item_trans.item_short_description IS NOT NULL AND item_trans.item_short_description != '' THEN item_trans.item_short_description
				ELSE item.item_short_description
			END AS item_description,

			(
				select GROUP_CONCAT(f.size_uuid,';',f.price,';', IF(f.size_name='',f.original_size_name,f.size_name) ,';',f.discount,';',f.discount_type,';',
				(
				select count(*) from {{view_item_lang_size}}
				where item_id = a.item_id
				and size_uuid = f.size_uuid
				and CURDATE() >= discount_start and CURDATE() <= discount_end
				),';',f.item_size_id
			)

			from {{view_item_lang_size}} f
				where
				item_id = a.item_id
				and language IN(''," . q(Yii::app()->language) . ")
			) as prices

			";
			$criteria->join = "
			  LEFT JOIN {{item}} item
		      ON a.item_id = item.item_id

			  LEFT JOIN {{item_translation}} item_trans
		      ON a.item_id = item_trans.item_id AND item_trans.language = " . q(Yii::app()->language) . "
			";
			$criteria->addCondition("a.client_id=:client_id AND fav_type=:fav_type");
			$criteria->params = array(
				':client_id' => Yii::app()->user->id,
				':fav_type' => 'item'
			);
			$criteria->order = "a.id DESC";

			$count = AR_favorites::model()->count($criteria);
			$pages = new CPagination($count);
			$pages->pageSize = $limit;
			$pages->setCurrentPage($page);
			$pages->applyLimit($criteria);
			$page_count = $pages->getPageCount();

			if ($page > 0) {
				if ($page_raw > $page_count) {
					$this->code = 3;
					$this->msg = t("end of results");
					$this->responseJson();
				}
			}

			$data = [];
			$merchant_ids = [];
			if (!$models = AR_favorites::model()->findAll($criteria)) {
				$this->msg = t(HELPER_NO_RESULTS);
				$this->responseJson();
			}

			foreach ($models as $items) {
				$url_image = !empty($items->photo) ? CMedia::getImage($items->photo, $items->path) : '';
				$price = array();
				$prices = CommonUtility::safeExplode(",", $items->prices);
				if (is_array($prices) && count($prices) >= 1) {
					foreach ($prices as $pricesval) {
						$sizes = CommonUtility::safeExplode(";", $pricesval);
						$item_price = isset($sizes[1]) ? (float)$sizes[1] : 0;
						$item_price = ($item_price * $exchange_rate);
						$item_discount = isset($sizes[3]) ? (float)$sizes[3] : 0;
						$discount_type = isset($sizes[4]) ? $sizes[4] : '';
						$discount_valid = isset($sizes[5]) ? (int)$sizes[5] : 0;

						$price_after_discount = 0;
						if ($item_discount > 0 && $discount_valid > 0) {
							if ($discount_type == "percentage") {
								$price_after_discount = $item_price - (($item_discount / 100) * $item_price);
							} else {
								$price_after_discount = $item_price - ($item_discount * $exchange_rate);
							}
						} else $item_discount = 0;

						$item_price2 = $price_after_discount > 0 ? $price_after_discount : $item_price;

						$price[] = array(
							'size_uuid' => isset($sizes[0]) ? $sizes[0] : '',
							'item_size_id' => isset($sizes[6]) ? $sizes[6] : '',
							'price' => $item_price,
							'price2' => $item_price2,
							'size_name' => isset($sizes[2]) ? $sizes[2] : '',
							'discount' => $item_discount,
							'discount_type' => $discount_type,
							'price_after_discount' => $price_after_discount,
							'pretty_price' => Price_Formatter::formatNumber($item_price),
							'pretty_price_after_discount' => Price_Formatter::formatNumber($price_after_discount),
						);
					}
				}

				$lowest_price = '';
				if (is_array($price) && count($price) >= 1) {
					$lowestprices = array_column($price, 'price2');
					$lowest_price = !empty($lowestprices) ? min($lowestprices) : null;
				}

				$merchant_ids[] = $items->merchant_id;
				$data[] = [
					'id' => $items->id,
					'cat_id' => $items->cat_id,
					'merchant_id' => $items->merchant_id,
					'item_id' => $items->item_id,
					'item_uuid' => $items->item_token,
					'item_name' => CommonUtility::safeDecode($items->item_name),
					'item_description' => CommonUtility::safeDecode($items->item_description),
					'item_slug' => $items->item_slug,
					'url_image' => $url_image,
					'lowest_price' => Price_Formatter::formatNumber($lowest_price),
					'lowest_price_raw' => $lowest_price,
					'lowest_price_label' => t("from {lowest_price}", ['{lowest_price}' => Price_Formatter::formatNumber($lowest_price)]),
					'save_fav' => true,
					'item_available' => true
				];
			}

			$items_not_available = CMerchantMenu::getItemAvailability($merchant_ids, date("w"), date("H:h:i"));
			if (!empty($items_not_available)) {
				foreach ($data as &$item) {
					if (in_array($item['item_id'], $items_not_available)) {
						$item['item_available'] = false;
					}
				}
			}

			$category_not_available = CMerchantMenu::getCategoryAvailability($merchant_ids, date("w"), date("H:h:i"));
			if (!empty($category_not_available)) {
				foreach ($data as &$item) {
					if (in_array($item['cat_id'], $category_not_available)) {
						$item['item_available'] = false;
					}
				}
			}

			$this->code = $page_raw == $page_count ? 3 : 1;
			$this->msg = "Ok";
			$this->details = [
				'page_raw' => $page_raw,
				'page_count' => $page_count,
				'data' => $data
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionsavenotifications()
	{
		try {

			$push =  Yii::app()->request->getPost('push', 0);

			AR_client_meta::saveMeta(Yii::app()->user->id, 'app_push_notifications', $push);

			$user_settings = [
				'app_push_notifications' => $push == 1 ? true : false,
			];

			$this->code = 1;
			$this->msg = t("Notification setting saved");
			$this->details = [
				'user_settings' => $user_settings
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionfetchdriver()
	{
		try {

			$driver_id =  Yii::app()->request->getQuery('driver_id', '');
			$data = CDriver::getDriverInfo($driver_id);
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'data' => $data
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionfetchNotification()
	{
		try {

			$channel = Yii::app()->user->client_uuid;
			$alert_count = CNotifications::getTotalNotifications($channel, ['order_update', 'wallet_loading']);
			$chat_count = CNotifications::getTotalNotifications($channel, ['chat-message']);
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'alert_count' => $alert_count,
				'chat_count' => $chat_count,
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionsetViewednotification()
	{
		try {

			$type =  Yii::app()->request->getQuery('type', '');
			$channel = Yii::app()->user->client_uuid;

			if ($type == "chat") {
				$types = ['chat-message'];
			} else $types = ['order_update', 'wallet_loading'];

			$stmt = "
		  UPDATE {{notifications}}
		  SET viewed = 1
		  WHERE notication_channel = " . q($channel) . "
		  AND notification_type IN (" . CommonUtility::arrayToQueryParameters($types) . ")
		  ";
			Yii::app()->db->createCommand($stmt)->query();

			$this->code = 1;
			$this->msg = "Ok";
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionuserloginbyotp()
	{
		try {

			$uuid = Yii::app()->request->getPost('uuid', null);
			$otp = Yii::app()->request->getPost('otp', null);

			if (!$uuid) {
				$this->msg = t("Missing required parameters in the request.");
				$this->responseJson();
			}
			if (!$otp) {
				$this->msg = t("OTP is required");
				$this->responseJson();
			}

			$model = AR_client::model()->find("client_uuid=:client_uuid AND social_strategy!=:social_strategy AND mobile_verification_code=:mobile_verification_code", [
				':client_uuid' => trim($uuid),
				':social_strategy' => 'single',
				':mobile_verification_code' => $otp
			]);

			if (!$model) {
				$this->msg  = t("Incorrect OTP");
				$this->responseJson();
			}

			$login = new AR_customer_autologin;
			$login->username = $model->email_address;
			$login->password = $model->password;
			$login->rememberMe = 1;
			if ($login->validate() && $login->login()) {
				$this->code = 1;
				$this->msg = t("Login successful");
				$this->userData();
			} else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionRegistrationPhone()
	{
		try {

			$mobile_number = Yii::app()->request->getPost('mobile_number', null);
			$mobile_prefix = Yii::app()->request->getPost('mobile_prefix', null);
			$recaptcha_response = Yii::app()->request->getPost('recaptcha_response', '');

			$mobile_prefix = str_replace("+",'',$mobile_prefix);
			$mobile_number = $mobile_prefix . $mobile_number;

			$capcha_enabled = Yii::app()->params['settings']['captcha_customer_signup'] ?? false;
			$capcha_enabled = $capcha_enabled == 1 ? true : false;

			$digit_code = CommonUtility::generateNumber(4, true);
			$model = AR_clientsignup::model()->find('contact_phone=:contact_phone AND social_strategy!=:social_strategy', [
				':contact_phone' => $mobile_number,
				':social_strategy' =>SOCIAL_STRATEGY
			]);
			if (!$model) {
				$model = new AR_clientsignup;
				$model->social_strategy = SOCIAL_STRATEGY;
				$model->capcha = $capcha_enabled;
				$model->recaptcha_response = $recaptcha_response;
				$model->scenario = 'registration_phone';
				$model->phone_prefix = $mobile_prefix;
				$model->contact_phone = $mobile_number;
				$model->mobile_verification_code = $digit_code;
				$model->reset_password_request = 1;
				$model->verify_code_requested = CommonUtility::dateNow();
				$model->status = 'pending';
				$model->merchant_id = 0;
				if ($model->save()) {
					$this->code = 1;
					$this->msg = t("We sent a code to {{contact_phone}}.", array(
						'{{contact_phone}}' => CommonUtility::mask($model->contact_phone)
					));
					$this->details = array(
						'client_uuid' => $model->client_uuid
					);
					if (DEMO_MODE == TRUE) {
						$this->details['verification_code'] = t("Your verification code is {{code}}", array('{{code}}' => $digit_code));
					}
				} else $this->msg = CommonUtility::parseError($model->getErrors());
			} else {
				if ($model->status == 'pending') {
					$model->scenario = 'registration_phone';
					$model->capcha = $capcha_enabled;
					$model->recaptcha_response = $recaptcha_response;
					$model->mobile_verification_code = $digit_code;
					$model->reset_password_request = 1;
					$model->verify_code_requested = CommonUtility::dateNow();
					if ($model->save()) {
						$this->code = 1;
						$this->msg = t("We sent a code to {{contact_phone}}.", array(
							'{{contact_phone}}' => CommonUtility::mask($model->contact_phone)
						));
						$this->details = array(
							'client_uuid' => $model->client_uuid
						);
						if (DEMO_MODE == TRUE) {
							$this->details['verification_code'] = t("Your verification code is {{code}}", array('{{code}}' => $digit_code));
						}
					} else $this->msg = CommonUtility::parseError($model->getErrors());
				} else $this->msg = t("Phone number already exist");
			}
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actioncompleteSignup()
	{
		try {

			$client_uuid =  $this->data['uuid'] ?? '';
			$first_name =  $this->data['first_name'] ?? '';
			$last_name =  $this->data['last_name'] ?? '';
			$email_address =  $this->data['email_address'] ?? '';
			$password =  $this->data['password'] ?? '';
			$cpassword =  $this->data['cpassword'] ?? '';
			$custom_fields =  $this->data['custom_fields'] ?? '';

			$model = AR_clientsignup::model()->find('client_uuid=:client_uuid', array(':client_uuid' => $client_uuid));
			if (!$model) {
				$this->msg = t("Records not found");
				$this->responseJson();
			}
			$model->scenario = 'complete_registration';

			$model->account_verified = 1;
			$model->first_name = $first_name;
			$model->last_name = $last_name;
			$model->email_address = $email_address;
			$model->password = $password;
			$model->cpassword = $cpassword;
			$model->status = 'active';

			// CUSTOM FIELDS
			$field_data = [];
			$custom_fields = $custom_fields;
			$field_data = AttributesTools::getCustomFields('customer', 'key2');
			$model->custom_fields = $custom_fields;
			CommonUtility::validateCustomFields($field_data, $custom_fields);

			if ($model->save()) {
				$this->code = 1;
				$this->msg = t("Registration successful");
				$this->autoLogin($model->email_address, $password);
			} else $this->msg = CommonUtility::parseError($model->getErrors());
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actioncompletesignupwithcode()
	{
		try {

			$client_uuid =  Yii::app()->request->getPost('uuid', null);
			$otp =  Yii::app()->request->getPost('otp', null);

			if (!$client_uuid) {
				$this->msg = t("Missing required parameters in the request.");
				$this->responseJson();
			}
			if (!$otp) {
				$this->msg = t("OTP is required");
				$this->responseJson();
			}

			$model = AR_clientsignup::model()->find('client_uuid=:client_uuid', array(':client_uuid' => $client_uuid));
			if (!$model) {
				$this->msg = t("Records not found");
				$this->responseJson();
			}

			$model->scenario = 'complete_standard_registration';
			if ($model->mobile_verification_code != $otp) {
				$this->msg = t("Invalid verification code");
				$this->responseJson();
			}

			$model->account_verified = 1;
			$model->status = 'active';

			if ($model->save()) {
				$this->code = 1;
				$this->msg = "OK";

				$login = new AR_customer_autologin;
				$login->username = $model->email_address;
				$login->password = $model->password;
				$login->rememberMe = 1;
				if ($login->validate() && $login->login()) {
					$this->code = 1;
					$this->msg = t("Login successful");
					$this->userData();
				} else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
			} else $this->msg = CommonUtility::parseError($model->getErrors());
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionPushSubscribe()
	{
		try {

			$token =  Yii::app()->request->getPost('token', null);
			$platform =  Yii::app()->request->getPost('platform', null);
			$client_id = Yii::app()->user->id;

			if (!$token) {
				$this->msg = t("Invalid token");
				$this->responseJson();
			}

			$user_type = 'client';

			$model = AR_device::model()->find("device_token = :device_token AND user_id=:user_id AND user_type=:user_type AND platform=:platform", [
				':device_token' => $token,
				':user_id' => $client_id,
				':user_type' => $user_type,
				':platform' => $platform
			]);
			if (!$model) {
				$model = new AR_device();
			}
			$model->user_type = $user_type;
			$model->user_id = $client_id;
			$model->platform = $platform;
			$model->device_token = $token;
			if (!$model->save()) {
				$this->msg = CommonUtility::parseError($model->getErrors());
				$this->responseJson();
			}

			AR_device::model()->deleteAll('user_id=:user_id AND user_type=:user_type AND platform=:platform AND device_token!=:device_token', array(
				':user_id' => $client_id,
				':user_type' => $user_type,
				':platform' => $platform,
				':device_token' => $token
			));

			$this->code = 1;
			$this->msg = "Ok";
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionPushUnsubscribe()
	{
		try {

			$platform =  Yii::app()->request->getPost('platform', null);
			$client_id = Yii::app()->user->id;

			$user_type = 'client';

			$model = AR_device::model()->find("user_id=:user_id AND user_type=:user_type AND platform=:platform", [
				':user_id' => $client_id,
				':user_type' => $user_type,
				':platform' => $platform
			]);
			if (!$model) {
				$this->msg = t(HELPER_RECORD_NOT_FOUND);
				$this->responseJson();
			}
			$model->delete();
			$this->code = 1;
			$this->msg = "Ok";
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionPushSuscribeValidate()
	{
		try {

			$token =  Yii::app()->request->getPost('token', null);
			$platform =  Yii::app()->request->getPost('platform', null);
			$client_id = Yii::app()->user->id;
			$user_type = 'client';

			$model = AR_device::model()->find("device_token = :device_token AND user_id=:user_id AND user_type=:user_type AND platform=:platform", [
				':device_token' => $token,
				':user_id' => $client_id,
				':user_type' => $user_type,
				':platform' => $platform
			]);
			if ($model) {
				$this->code = 1;
				$this->msg  = "Token Ok";
			} else $this->msg = t("Web token not found");
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionfetchfavouriteslocation()
	{
		try {

			$limit = 20;
			$page = intval(Yii::app()->request->getQuery('page', 1));
			$page_raw =  intval(Yii::app()->request->getQuery('page', 1));
			$state_id = Yii::app()->request->getQuery('state_id', null);
			$city_id = Yii::app()->request->getQuery('city_id', null);
			$area_id = Yii::app()->request->getQuery('area_id', null);
			$postal_id = Yii::app()->request->getQuery('postal_id', null);

			if ($page > 0) {
				$page = $page - 1;
			}

			$and_estimate = '';
			$setttings = Yii::app()->params['settings'];
			$search_type = $setttings['location_searchtype'] ?? '';
			$transaction_type = 'delivery';

			if ($search_type == 1) {
				if ($city_id > 0 && $area_id > 0) {
					$and_estimate = "AND estimate.city_id = " . q($city_id) . " AND estimate.area_id = " . q($area_id) . " ";
				}
			} elseif ($search_type == 2) {
				if ($state_id > 0 && $city_id > 0) {
					$and_estimate = "AND estimate.city_id = " . q($city_id) . " AND estimate.state_id = " . q($state_id) . "";
				}
			} elseif ($search_type == 3) {
				if (!empty($postal_id)) {
					$city_model = AR_city::model()->find("postal_code=:postal_code", [
						':postal_code' => $postal_id
					]);
					if ($city_model) {
						$city_id = $city_model->city_id;
						$and_estimate = "AND l.city_id = " . q($city_id) . "";
					}
				}
			}


			$todays_date = date("Y-m-d H:i");
			$time_now = date("H:i", strtotime($todays_date));
			$today_now = strtolower(date("l", strtotime($todays_date)));
			$today_day_of_week = date('N');

			CommonUtility::mysqlSetTimezone();

			$criteria = new CDbCriteria();
			$criteria->alias = "a";
			$criteria->select = "
			a.id,
			a.merchant_id,
			m.restaurant_name,
			m.restaurant_slug,
			m.logo as photo,
			m.path,
			m.close_store,
			m.pause_ordering,
			m.disabled_ordering,

			cuisine.cuisines,
			rating.ratings,

			COALESCE( concat(estimate.estimated_time_min,'-',estimate.estimated_time_max) , '') AS estimated_time_min,

			(
				select count(*) from
				{{opening_hours}}
				where
				merchant_id = a.merchant_id
				and
				day=" . q($today_now) . "
				and
				status = 'open'
				and
				(
				CAST(" . q($time_now) . " AS TIME)
				BETWEEN CAST(start_time AS TIME) and CAST(end_time AS TIME)
				)
			) as merchant_open_status,


			CASE
			WHEN opening_hours.status = 'open'
			AND STR_TO_DATE(opening_hours.start_time, '%H:%i') > TIME(NOW())
			THEN CONCAT(opening_hours.start_time,';',opening_hours.end_time,';',opening_hours.day_of_week,';',opening_hours.day)

			ELSE (
			SELECT CONCAT(next_oh.start_time,';',next_oh.end_time,';',next_oh.day_of_week,';',next_oh.day)
			FROM {{opening_hours}} next_oh
			WHERE next_oh.merchant_id = a.merchant_id
				AND next_oh.status = 'open'
				AND (
					next_oh.day_of_week > DAYOFWEEK(NOW()) - 1  -- Find next day
					OR (next_oh.day_of_week = 0 AND DAYOFWEEK(NOW()) != 1)  -- Handle Sunday properly
					)
			ORDER BY next_oh.day_of_week ASC
			LIMIT 1
			)
			END AS next_opening_hours,

			CASE
            WHEN m.pause_ordering = 1 THEN (
                SELECT mm.meta_value
                FROM {{merchant_meta}} mm
                WHERE mm.merchant_id = a.merchant_id
                AND meta_name='pause_reason'
                LIMIT 0,1
            )
            ELSE " . q(t('Currently unavailable')) . "
            END AS close_reason

			";
			$criteria->join = "
			LEFT JOIN {{merchant}} m on a.merchant_id = m.merchant_id

			LEFT JOIN (
              select merchant_id,cuisines from {{view_cuisine_merchant}} where language=" . q(Yii::app()->language) . "
            ) cuisine
            on a.merchant_id = cuisine.merchant_id

			LEFT JOIN (
			  select merchant_id,review_count,ratings from {{view_ratings}}
			) rating
			on a.merchant_id = rating.merchant_id

			LEFT JOIN {{location_time_estimate}} estimate
			ON (
			  m.self_delivery = 1 AND a.merchant_id = estimate.merchant_id AND estimate.service_type=" . q($transaction_type) . " $and_estimate
			)
			OR (
			  m.self_delivery = 0 AND estimate.merchant_id = 0 AND estimate.service_type=" . q($transaction_type) . " $and_estimate
			)

			LEFT JOIN {{opening_hours}} opening_hours
			ON a.merchant_id = opening_hours.merchant_id
			AND opening_hours.day_of_week = DAYOFWEEK(NOW()) - 1
			";
			$criteria->addCondition("a.client_id=:client_id AND fav_type=:fav_type");
			$criteria->params = array(
				':client_id' => Yii::app()->user->id,
				':fav_type' => 'restaurant'
			);
			$criteria->order = "a.id DESC";


			$count = AR_favorites::model()->count($criteria);

			$pages = new CPagination($count);
			$pages->pageSize = $limit;
			$pages->setCurrentPage($page);
			$pages->applyLimit($criteria);
			$page_count = $pages->getPageCount();

			$is_last_page = false;
			if ($page_raw > $page_count) {
				$is_last_page = true;
			}

			$data = [];

			$models = AR_favorites::model()->findAll($criteria);
			if (!$models) {
				$this->msg = t(HELPER_NO_RESULTS);
				$this->responseJson();
			}
			foreach ($models as $items) {
				$logo_url = !empty($items->photo) ? CMedia::getImage($items->photo, $items->path) : '';
				$ratings = isset($items->ratings) ? $items->ratings : 0;
				$ratings = $ratings > 0 ? number_format($items->ratings, 0) : 0;
				$estimated_time =  !empty($items->estimated_time_min) ? t("{estimated} mins", [
					'{estimated}' => $items->estimated_time_min
				]) : '';

				$available = true;
				if ($items->close_store == 1 || $items->pause_ordering == 1 || $items->merchant_open_status == 0  || $items->disabled_ordering == 1) {
					$available = false;
				}

				/*next_opening*/
				$next_opening = '';
				if ($items->merchant_open_status == 0) {
					$currentDate = new DateTime();
					$next_opening_hours = !empty($items->next_opening_hours) ? explode(';', $items->next_opening_hours) : null;
					if (!empty($next_opening_hours)) {
						$start_time = isset($next_opening_hours[0]) ? $next_opening_hours[0] : '';
						$end_time = isset($next_opening_hours[1]) ? $next_opening_hours[1] : '';
						$day_of_week = isset($next_opening_hours[2]) ? $next_opening_hours[2] : '';

						$daysToAdd = ($day_of_week > $today_day_of_week)
							? ($day_of_week - $today_day_of_week)
							: (7 - $today_day_of_week + $day_of_week);
						$openingDate = Date_Formatter::date($currentDate->modify("+$daysToAdd days")->format('Y-m-d'), "E", true);

						$startTime = DateTime::createFromFormat('H:i', $start_time)->format('h:i A');
						$endTime = DateTime::createFromFormat('H:i', $end_time)->format('h:i A');
						$next_opening = t("Opens [day] at [time]", [
							'[day]' => $openingDate,
							'[time]' => "$startTime - $endTime"
						]);
					}
				}

				$merchants[] = $items['merchant_id'];
				$data[] = [
					'merchant_id' => $items->merchant_id,
					'restaurant_slug' => $items->restaurant_slug,
					'restaurant_name' => $items->restaurant_name,
					'url_logo' => $logo_url,
					'saved_store' => 1,
					'free_delivery' => false,
					'estimation' => $estimated_time,
					'cuisines' => CommonUtility::safeDecode($items->cuisines),
					'ratings' => $ratings,
					'available' => $available,
					'pause_ordering' => $items->pause_ordering,
					'close_store' => $items->close_store,
					'close_reason' => $items->close_reason,
					'next_opening' => $next_opening,
					'is_last_page' => $is_last_page
				];
			}

			try {
				$vouchers = [];
				$vouchers = Clocations::getVoucherList($merchants, date("Y-m-d"));
			} catch (Exception $e) {
			}

			try {
				$promos = [];
				$promos = Clocations::getPromoList($merchants, date("Y-m-d"));
			} catch (Exception $e) {
			}

			foreach ($data as &$restaurant) {
				$merchant_id = $restaurant['merchant_id'];
				$restaurant['vouchers'] = isset($vouchers[$merchant_id]) ? $vouchers[$merchant_id] : [];
				$restaurant['promos'] = isset($promos[$merchant_id]) ? $promos[$merchant_id] : [];
				$combined_promos = array_merge($restaurant['vouchers'], $restaurant['promos']);
				$restaurant['promo_list'] = $combined_promos;
			}

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'page_raw' => $page_raw,
				'page_count' => $page_count,
				'is_last_page' => $is_last_page,
				'data' => $data
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actiongetmenucategory()
	{
		try {

			$page = intval(Yii::app()->request->getQuery('page', 1));
			$slug = Yii::app()->request->getQuery('slug', null);
			$limit = Yii::app()->request->getQuery('limit', Yii::app()->params->list_limit);

			$merchant_id = MerchantMenuHelper::getMerchantIdBySlug($slug);
			if (!$merchant_id) {
				throw new Exception("Merchant not found.");
			}

			$result = MerchantMenuHelper::getMenuCategories($merchant_id, Yii::app()->language, $page - 1, $limit);

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = $result;

		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionfetchcategoryitems()
	{
		try {

			$page = intval(Yii::app()->request->getQuery('page', 1));
			$cat_id = Yii::app()->request->getQuery('cat_id', null);
			$currency_code = Yii::app()->request->getQuery('currency_code', '');
			$slug = Yii::app()->request->getQuery('slug', '');
			$client_uuid = Yii::app()->request->getQuery('client_uuid', null);
			$limit = Yii::app()->request->getQuery('limit', Yii::app()->params->list_limit);

			$base_currency = Price_Formatter::$number_format['currency_code'];
			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled']) ? Yii::app()->params['settings']['multicurrency_enabled'] : false;
			$multicurrency_enabled = $multicurrency_enabled == 1 ? true : false;

			$exchange_rate = 1;
			$model = CMerchantListingV1::getMerchantBySlug($slug);
			$merchant_id = $model->merchant_id;

			// CHECK IF MERCHANT HAS DIFFERENT TIMEZONE
			$options_merchant = OptionsTools::find(['merchant_timezone', 'merchant_default_currency'], $merchant_id);
			$merchant_timezone = isset($options_merchant['merchant_timezone']) ? $options_merchant['merchant_timezone'] : '';
			$merchant_default_currency = isset($options_merchant['merchant_default_currency']) ? $options_merchant['merchant_default_currency'] : '';
			$merchant_default_currency = !empty($merchant_default_currency) ? $merchant_default_currency : $base_currency;
			if (!empty($merchant_timezone)) {
				Yii::app()->timezone = $merchant_timezone;
			}

			$currency_code = !empty($currency_code) ? $currency_code : (empty($merchant_default_currency) ? $base_currency : $merchant_default_currency);

			// SET CURRENCY
			if (!empty($currency_code) && $multicurrency_enabled) {
				Price_Formatter::init($currency_code);
				if ($currency_code != $merchant_default_currency) {
					$exchange_rate = CMulticurrency::getExchangeRate($merchant_default_currency, $currency_code);
				}
			}

			$result = MerchantMenuHelper::getCategoryItems($merchant_id,$cat_id, $exchange_rate,null,Yii::app()->language, $page - 1, $limit);
			$is_last_page = $result['is_last_page'] ?? false;
			$items = $result['data'] ?? [];

			$items_not_available = CMerchantMenu::getItemAvailability($merchant_id, date("w"), date("H:h:i"));
			$category_not_available = CMerchantMenu::getCategoryAvailability($merchant_id, date("w"), date("H:h:i"));
			$dish = CMerchantMenu::getDish(Yii::app()->language);
			$favorites = [];
			$promoItems = CMerchantMenu::getPromoItems($merchant_id);

			if(is_array($promoItems) && count($promoItems)>=1){
				foreach ($items as &$itemss) {
				    $itemss['promo_data'] = $promoItems[$itemss['item_id']] ?? [];
				}
		    }

			$category_available = true;
			if(in_array($cat_id,(array)$category_not_available)){
				$category_available =  false;
			}

			if(!empty($client_uuid)){
				try {
					$customer = ACustomer::getUUID($client_uuid);
					$favorites = CSavedStore::getSaveItemsByCustomer($customer->client_id,$merchant_id);
					$favorites = $favorites['item_ids'];
				} catch (Exception $e) {}
			}

			foreach ($items as &$itemss) {
				$itemss['available']=true;
				$itemss['is_favorite'] = false;
				if(in_array($itemss['item_id'],(array)$items_not_available) || !$category_available ){
					$itemss['available']=false;
				}
				if(isset($itemss['dish']) && is_array($itemss['dish'])){
					foreach ($itemss['dish'] as $dish_id) {
						if(isset($dish[$dish_id])){
							$itemss['dish_list'][]= $dish[$dish_id];
						}
					}
				}
				if(in_array($itemss['item_id'],(array)$favorites)){
					$itemss['is_favorite'] = true;
				}
			}

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'is_last_page'=>$is_last_page,
				'merchant_id'=>$merchant_id,
				'cat_id'=>$cat_id,
				'available'=>$category_available,
				'data'=>$items
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionsearchcategoryitems()
	{
		try {

			$merchant_id = Yii::app()->request->getQuery('merchant_id', null);
			$cat_id = Yii::app()->request->getQuery('cat_id', null);
			$search = Yii::app()->request->getQuery('q', null);
			$currency_code = Yii::app()->request->getQuery('currency_code', '');
			$client_uuid = Yii::app()->request->getQuery('client_uuid', null);

			if(!$merchant_id){
				$this->msg = t("Invalid merchant id");
				$this->responseJson();
			}
			if(!$search){
				$this->msg = t("Invalid search input");
				$this->responseJson();
			}

			$limit = 100;
			$exchange_rate = MerchantMenuHelper::getExchangeRate($merchant_id,$currency_code);
			$result = MerchantMenuHelper::getCategoryItems($merchant_id,$cat_id, $exchange_rate,$search,Yii::app()->language, 1, $limit);
			$items = $result['data'] ?? [];

			$items_not_available = CMerchantMenu::getItemAvailability($merchant_id, date("w"), date("H:h:i"));
			$category_not_available = CMerchantMenu::getCategoryAvailability($merchant_id, date("w"), date("H:h:i"));
			$dish = CMerchantMenu::getDish(Yii::app()->language);
			$favorites = [];

			if(!empty($client_uuid)){
				try {
					$customer = ACustomer::getUUID($client_uuid);
					$favorites = CSavedStore::getSaveItemsByCustomer($customer->client_id,$merchant_id);
					$favorites = $favorites['item_ids'];
				} catch (Exception $e) {}
			}

			foreach ($items as &$itemss) {
				$itemss['available']=true;
				$itemss['is_favorite'] = false;

				$category_available = true;
				if(in_array($itemss['cat_id'],(array)$category_not_available)){
					$category_available =  false;
				}

				if(in_array($itemss['item_id'],(array)$items_not_available) || !$category_available){
					$itemss['available']=false;
				}
				if(isset($itemss['dish']) && is_array($itemss['dish'])){
					foreach ($itemss['dish'] as $dish_id) {
						if(isset($dish[$dish_id])){
							$itemss['dish_list'][]= $dish[$dish_id];
						}
					}
				}
				if(in_array($itemss['item_id'],(array)$favorites)){
					$itemss['is_favorite'] = true;
				}
			}


			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'merchant_id'=>$merchant_id,
				'cat_id'=>$cat_id,
				'available'=>true,
				'data'=>$items
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionpromocheck()
	{
		try {

			$subtotal = 0;
			$cart_uuid =  Yii::app()->request->getQuery('cart_uuid', null);
			$merchant_id =  Yii::app()->request->getQuery('merchant_id', null);

			if(!$merchant_id){
				$this->msg = t("Invalid merchant id");
				$this->responseJson();
			}

			if (!is_numeric($merchant_id) || $merchant_id == 0) {
                $this->msg = t("Invalid merchant id");
				$this->responseJson();
			}

			$promos = CMerchantMenu::getItemActivePromo($merchant_id);
			if (!is_array($promos) || empty($promos)) {
				$this->msg = t("No promos");
				$this->responseJson();
			}
			$cart_merchant_id = CCart::getMerchantId($cart_uuid);

			if($cart_merchant_id!=$merchant_id){
				$this->msg = t("Merchant cart invalid");
				$this->responseJson();
			}

			$subtotal = CCart::getCartSubtotal($cart_uuid,$promos);

			$data = CMerchantMenu::PromoItemsCheck($merchant_id,$subtotal);
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'subtotal'=>$subtotal,
				'data'=>$data
			];
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionapple_callback()
	{
		?>
		<!DOCTYPE html>
		<html>
		<body>
			<script>			
			const payload = <?php
				echo json_encode([
				'code' => $_POST['code'] ?? null,
				'id_token' => $_POST['id_token'] ?? null,
				'user' => $_POST['user'] ?? null,
				]);
			?>;

			if (window.opener) {
				window.opener.postMessage({
				source: 'apple-login',
				payload: payload
				}, '*');
			}

			window.close();
			</script>
		</body>
		</html>
		<?php
	}	

	public function actionapp_apple_callback()
	{
		
		$code = $_POST['code'] ?? null;		
		$userData = isset($_POST['user']) ? json_decode($_POST['user'], true) : null;
		
		if(!$code){		
			$message = t('Invalid receive code');
			$this->redirect(APP_CUSTOM_URL_SCHEME."://payment-callback?status=failed&message={$message}");
			Yii::app()->end();
		}

		$options = OptionsTools::find([
			'app_apple_app_id','app_apple_team_id','app_apple_key_id','app_apple_key_crt'
		]);
		$app_apple_app_id = $options['app_apple_app_id'] ?? '';
		$app_apple_team_id = $options['app_apple_team_id'] ?? '';
		$app_apple_key_id = $options['app_apple_key_id'] ?? '';
		$app_apple_key_crt = $options['app_apple_key_crt'] ?? '';

		$clientId = $app_apple_app_id;
		$teamId = $app_apple_team_id;
		$keyId = $app_apple_key_id;
		$path = CommonUtility::uploadPath()."/crt/{$app_apple_key_crt}";		
		if(!file_exists($path)){			
			$message = t('Keys certificate not found');
			$this->redirect(APP_CUSTOM_URL_SCHEME."://payment-callback?status=failed&message={$message}");
			Yii::app()->end();
		}

		$privateKey = file_get_contents($path);

		$token = [
			'iss' => $teamId,
			'iat' => time(),
			'exp' => time() + 86400 * 180, // valid for 180 days
			'aud' => 'https://appleid.apple.com',
			'sub' => $clientId,
		];

		$header = [
			'alg' => 'ES256',
			'kid' => $keyId,
		];

		$jwt = JWT::encode($token, $privateKey, 'ES256', $keyId, $header);		

		$tokenUrl = 'https://appleid.apple.com/auth/token';
		$data = [
			'grant_type' => 'authorization_code',
			'code' => $code,
			'redirect_uri' => CommonUtility::getHomebaseUrl()."/interface/app_apple_callback",
			'client_id' => $clientId,
			'client_secret' => $jwt,
		];		

		$options = [
			CURLOPT_URL => $tokenUrl,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => http_build_query($data),
		];

		$ch = curl_init();
		curl_setopt_array($ch, $options);
		$response = curl_exec($ch);
		curl_close($ch);

		$tokenResponse = json_decode($response, true);		
		if (isset($tokenResponse['id_token'])) {
			 $idToken = $tokenResponse['id_token'];
			 $segments = explode('.', $idToken);
             $payload = json_decode(base64_decode(strtr($segments[1], '-_', '+/')), true);
			 
			 $appleUserId = $payload['sub'];
             $email = $payload['email'] ?? null;             

			 $params = [
				'status'=>'apple_login',
				'id'=>$appleUserId,
				'email'=>$email,
				'first_name'=> $userData ? $userData['name']['firstName'] : '',
				'last_name'=> $userData ? $userData['name']['lastName'] : '',
				'social_strategy'=>'apple',
				'social_token'=>md5($jwt)
			 ];			 
			 $this->redirect(APP_CUSTOM_URL_SCHEME."://payment-callback?".http_build_query($params));
		} else {			
			$message = t('Failed to get ID token from Apple.');
			$this->redirect(APP_CUSTOM_URL_SCHEME."://payment-callback?status=failed&message={$message}");
		}		
	}

	public function actiongetServicesList()
	{
		try {
			$services = CServices::List();
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = $services;
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	/**
	 * POST /interface/firebaseCustomToken
	 * Auth: Authorization: token <client_token> (verifyCustomer). Body: empty.
	 *
	 * Mints a Firebase CUSTOM TOKEN (an RS256 JWT signed with the project's
	 * service-account key) whose uid === the authenticated customer's
	 * client_uuid. The app exchanges it via signInWithCustomToken so Firestore
	 * participant-only rules (firestore.rules) can enforce chat access with
	 * request.auth.uid. Contract: MobileVue/docs/backend/firebase-custom-token.md.
	 *
	 * Response (success): { code:1, msg:"", details:{ token } }.
	 * Config: FIREBASE_SA_JSON_PATH in k-config.php (git-ignored) points to the
	 * service-account JSON. The php-jwt lib is already vendored.
	 */
	public function actionfirebaseCustomToken()
	{
		try {
			$client_uuid = Yii::app()->user->client_uuid;
			if (empty($client_uuid)) {
				$this->msg = t("User not login or session has expired");
				$this->responseJson();
				return;
			}

			$sa = $this->loadFirebaseServiceAccount();
			if (!$sa) {
				$this->msg = t("Chat is not configured");
				$this->responseJson();
				return;
			}

			$now = time();
			$claims = array(
				'iss' => $sa['client_email'],
				'sub' => $sa['client_email'],
				'aud' => 'https://identitytoolkit.googleapis.com/google.identity.identitytoolkit.v1.IdentityToolkit',
				'iat' => $now,
				'exp' => $now + 3600, // Firebase custom tokens live up to 1 hour.
				'uid' => (string) $client_uuid,
			);

			// RS256 signature with the service-account private key.
			$token = JWT::encode($claims, $sa['private_key'], 'RS256');

			$this->code = 1;
			$this->msg = "";
			$this->details = array('token' => $token);
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	/**
	 * Load Firebase service-account credentials from the JSON file at
	 * FIREBASE_SA_JSON_PATH (defined in the git-ignored k-config.php). Keep the
	 * file OUTSIDE the web root. Returns ['client_email','private_key'] or null.
	 */
	private function loadFirebaseServiceAccount()
	{
		if (!defined('FIREBASE_SA_JSON_PATH') || !FIREBASE_SA_JSON_PATH) {
			return null;
		}
		$path = FIREBASE_SA_JSON_PATH;
		if (!is_file($path) || !is_readable($path)) {
			return null;
		}
		$json = json_decode(file_get_contents($path), true);
		if (!is_array($json) || empty($json['client_email']) || empty($json['private_key'])) {
			return null;
		}
		return array(
			'client_email' => $json['client_email'],
			'private_key'  => $json['private_key'],
		);
	}

	/**
	 * POST /interface/aichat
	 * Auth: Authorization: token <client_token> (verifyCustomer).
	 * Body: { message: string, history?: [{role:'user'|'assistant', content}], context?: string }.
	 *
	 * Server-side AI gateway (READ-ONLY). The model API key stays on the server
	 * (TFE_AI_API_KEY in k-config.php); it is NEVER shipped to the app. The
	 * assistant is given the signed-in customer's name plus any read-only
	 * `context` the app already holds (e.g. a recent-orders summary the customer
	 * is authorized to see), and replies in the user's language. It performs NO
	 * write, order or payment action — those stay behind the app's own screens
	 * and the future confirmation-card flow (Phase 4b).
	 *
	 * Response (success): { code:1, msg:"", details:{ reply } }.
	 */
	public function actionaichat()
	{
		try {
			if (!defined('TFE_AI_API_KEY') || !TFE_AI_API_KEY) {
				$this->msg = t("Assistant is not configured");
				$this->responseJson();
				return;
			}

			$message = isset($this->data['message']) ? trim($this->data['message']) : '';
			if ($message === '') {
				$this->msg = t("Empty message");
				$this->responseJson();
				return;
			}
			if (strlen($message) > 2000) {
				$message = substr($message, 0, 2000);
			}

			$first_name = Yii::app()->user->first_name;
			$lang = Yii::app()->language;
			$app_context = isset($this->data['context']) ? substr((string) $this->data['context'], 0, 4000) : '';

			$provider = defined('TFE_AI_PROVIDER') ? TFE_AI_PROVIDER : 'anthropic';
			$model = defined('TFE_AI_MODEL') ? TFE_AI_MODEL : 'claude-sonnet-5';

			$system = "You are the ThunderfamEats assistant, a concise, friendly guide for a multi-service super app "
				. "(food, grocery, hotels & bookings, home services, taxi and more). "
				. "The signed-in customer's first name is \"" . str_replace('"', '', $first_name) . "\". "
				. "Reply in the user's language (current locale: " . $lang . "). "
				. "You are READ-ONLY: never claim to place, modify, cancel, or pay for an order — instead point the user to the right screen "
				. "(Cart, Checkout, Order tracking, Bookings). Keep answers short and helpful. "
				. "Use only the CONTEXT below and the conversation; if you don't know something, say so plainly.\n\n"
				. "CONTEXT:\n" . ($app_context !== '' ? $app_context : "(no extra context provided)");

			$history = isset($this->data['history']) && is_array($this->data['history']) ? $this->data['history'] : array();

			$reply = $this->aiCall($provider, $model, $system, $message, $history);

			if ($reply === null || $reply === '') {
				$this->msg = t("Assistant is unavailable right now");
				$this->responseJson();
				return;
			}

			$this->code = 1;
			$this->msg = "";
			$this->details = array('reply' => $reply);
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	/**
	 * Call the configured LLM provider and return the assistant text, or null on
	 * failure. Uses cURL; the API key never leaves the server. v1 supports the
	 * Anthropic Messages API.
	 */
	private function aiCall($provider, $model, $system, $message, $history)
	{
		$messages = array();
		foreach ($history as $h) {
			$role = isset($h['role']) ? $h['role'] : '';
			$content = isset($h['content']) ? (string) $h['content'] : '';
			if (($role === 'user' || $role === 'assistant') && $content !== '') {
				$messages[] = array('role' => $role, 'content' => substr($content, 0, 2000));
			}
		}
		$messages[] = array('role' => 'user', 'content' => $message);
		// Keep the last 12 turns to bound token use.
		if (count($messages) > 12) {
			$messages = array_slice($messages, -12);
		}

		if ($provider !== 'anthropic') {
			// Only Anthropic is wired in v1; extend here for other providers.
			return null;
		}

		$payload = json_encode(array(
			'model' => $model,
			'max_tokens' => 600,
			'system' => $system,
			'messages' => $messages,
		));

		$ch = curl_init('https://api.anthropic.com/v1/messages');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'content-type: application/json',
			'x-api-key: ' . TFE_AI_API_KEY,
			'anthropic-version: 2023-06-01',
		));
		$raw = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$err = curl_error($ch);
		curl_close($ch);

		if ($raw === false || $httpcode < 200 || $httpcode >= 300) {
			Yii::log('aichat provider error: ' . $httpcode . ' ' . $err, 'error', 'application.interface.aichat');
			return null;
		}

		$data = json_decode($raw, true);
		if (!is_array($data) || empty($data['content'][0]['text'])) {
			return null;
		}
		return trim($data['content'][0]['text']);
	}

}
// end class