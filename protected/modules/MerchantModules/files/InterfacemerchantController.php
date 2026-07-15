<?php
require 'intervention/vendor/autoload.php';
require 'php-jwt/vendor/autoload.php';
use Intervention\Image\ImageManager;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class InterfacemerchantCommon extends CController {

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
                     'getAttributes','registerDevice','ForgotPassword','resendResetEmail','getlocationAutocomplete','getLocationDetails','CreateAccountMerchant',
                     'getMerchant','CreateMerchantUser','getPlan','PaymenPlanList'
                 ),
				 'expression' => array('AppMerchantIdentity','verifyToken')
			 ),
             array('deny',
                  'actions'=>array(
                    'UpdateProfile','updateAvatar','updatePassword','Updateaccountnotification','RequestEmailCode','deleteAccount',
                    'geStoreMenu','addCategory','deleteCategory','getCategory','geStoreAddonMenu','getAddonCategory','addAddonCategory',
                    'deleteAddonCategory','getAddonCategoryList','addAddonItem','getAddonItem','deleteAddonItem','setAddonItemAvailable',
                    'getCategoryList','createItem','getItem','deleteItem','getPriceList','getPriceAttributes','createItemPrice','deletePrice'
                    ,'getPrice','getAddonlist','itemAddonCreate','deleteItemAddon','getItemAddon','getItemAttributes','saveItemAttributes','saveAvailability',
                    'saveInventory','getSupplier','getSalePromotion','deletePromotion','getItemList','createPromotion','getPromotion','getItemGallery',
                    'deleteItemGallery','createGallery','updateItemSeo','getItemSeo','setItemAvailable','getReview','getNotification',
                    'deleteAllNotification','deleteNotifications','getEarningSummary','getTotalOrders','getLastOrder','getTopCustomer','salesOverview',
                    'OverviewReview','updateOrderStatus','lessCashOnAccount','setOrderViewed','OrderList','orderDetails','setDelayToOrder','getMerchantDashboard',
                    'transactionHistory','payoutHistory','requestPayout','SetPayoutAccount','getPayoutSettings','getInformation','updateInformation','saveAddress',
                    'getSettings','saveSettings','getOpeningHours','deleteHours','createHours','getCustomerDetails','getCustomerSummary','getCustomerOrders',
                    'blockCustomer','getCustomerReview','reviewAddreply','search','SearchMenu','storeAvailable','setStoreAvailable','getSizeList','deleteSize','addSize',
                    'getSize','IngredientList','addIngredients','getIngredients','deleteIngredients','CookingList','deleteCooking','addCooking','getCooking',
                    'registerDevice','updateDevice','SavePrinter','PrintersList','PrinterDetails','deletePrinter','getAutoPrinter','getCustomerReply','reviewDeleteReply',
                    'SortCategory','Updatealocalnotification','storeSettings','FPtestprint','FPprint','SortAddonCategory','SortAddonItems','SortItems','getAddonSort',
                    'SortAddonItemsSort','getAvailableDriver','AssignDriver','getgrouplist','getZoneList','getPaymentHistory','reservationList','reservationSummary',
                    'getBookingDetails','BookingCustomerSummary','CustomerReservationList','UpdateBookingStatus','SearchCustomer','UpdateBooking','BookingSettings',
                    'SaveBookingSettings','tableShift','CreateShift','deleteShift','UpdateShift','tableRoomList','deleteRooms','CreateRoom','getRoom','UpdateRoom',
                    'tableList','searchTableroom','CreateTable','deleteTable','getTable','UpdateTable','getTableList','AddBooking','CategoryList','categoryItems',
                    'getMenuItem','addCartItems','getCart','posAttributes','createCustomer','submitPOSOrder','applyTips','onHoldOrders','deleteHoldorder','cashoutSummary',
                    'cashoutList','getPayoutDetails','cancelPayout','collectCashList','collectTransactions','driverList','deleteDriver','AddDriver','getDriverInfo',
                    'UpdateDriver','getDriverOverview','getDriverActivity','AddLicense','getVehicle','AddVehicle','getBankInfo','AddDriverBankInfo',
                    'driverWalletBalance','driverWalletTransactions','driverWalletAdjustment','UpdateReviews','carList','AddCar','UpdateCar','GroupList','SelectDriverList',
                    'AddGroup','UpdateGroup','ZoneList','AddZone','UpdateZone','ScheduleList','ScheduleAttributes','UpdateSchedule','AddSchedule','ShiftList',
                    'AddShiftSchedule','getShiftSchedule','UpdateShiftSchedule','ReviewList','getDeliverySettings','SaveDeliverySettings','ShippingRateList',
                    'AddDynamicRates','deleteShippingRate','getDynamicRates','UpdateDynamicRates','getOrderTypeSettings','saveOrderTypeSettings','couponList',
                    'deleteCoupon','PromoAttributes','AddCoupon','getCoupon','UpdateCoupon','offerList','AddOffers','deleteOffers','getOffers','UpdateOffers',
                    'galleryList','deleteGallery','AddGallery','UpdateGallery','getGallery','mediaList','AddMedia','UpdateMedia','getMedia','deleteMedia','getMerchantBalance',
                    'withdrawalsHistory','getPayoutAccount','getMerchantLogin','saveMerchantlogin','getTimezonedata','saveTimezonedata','getMerchantzone','saveMerchantzone',
                    'invoiceList','invoiceDetails','invoiceProofpayment','AddCollectCash','getDailySummary','dailyReportSales','FPprintdailysales','GetDailysales',
                    'getOrderSummary','SaleReport','ItemSummary','searchfooditems','paymentlist','deletePayment','PaymentProviderByMerchant','AddPayment',
                    'getPayment','UpdatePayment','bankdepositlist','deleteBankDeposit','getBankDeposit','UpdateBankDeposit','saveCartAddress','getRefreshAccess',
                    'getCountOrder'
                 ),
				 'expression' => array('AppMerchantIdentity','verifyMerchant')
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
			'captcha_customer_signup','image_resizing','merchant_specific_country','map_provider','google_geo_api_key','mapbox_access_token',
			'signup_enabled_verification','mobilephone_settings_default_country','mobilephone_settings_country','website_title','merchant_enabled_registration',
            'merchant_specific_country','registration_terms_condition','registration_program','mt_android_download_url','mt_ios_download_url','mt_app_version_android',
            'mt_app_version_ios','enabled_language_merchant_app','multicurrency_enabled','driver_on_demand_availability'
	    ));

		Yii::app()->params['settings'] = $settings;

		/*SET TIMEZONE*/
		$timezone = Yii::app()->params['settings']['website_timezone_new'];
		if (is_string($timezone) && strlen($timezone) > 0){
		   Yii::app()->timeZone=$timezone;
		}

        if(!Yii::app()->merchant->isGuest){            
            Yii::app()->params['settings_merchant'] = OptionsTools::find(array(
				'merchant_default_currency','self_delivery','merchant_enabled_continues_alert'
			),Yii::app()->merchant->merchant_id);	            
        }        

        $multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		$multicurrency_enabled = $multicurrency_enabled==1?true:false;		
		$merchant_currency = isset(Yii::app()->params['settings_merchant']['merchant_default_currency'])?Yii::app()->params['settings_merchant']['merchant_default_currency']:'';	

        if($multicurrency_enabled && !empty($merchant_currency)){            
            Price_Formatter::init($merchant_currency);	
        } else {
            Price_Formatter::init();           
        }		
	}

}
// end class

class InterfacemerchantController extends InterfacemerchantCommon
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

    public function actionLogin()
    {
        $username = Yii::app()->input->post('username');
        $password = Yii::app()->input->post('password');

        $model=new AR_merchant_login;
		$model->scenario='login';

        $model->username = trim($username);
        $model->password = trim($password);

        if($model->validate() && $model->login() ){
            $this->code = 1;
            $this->msg = t("Login Succesful");
            $this->userData();
        } else $this->msg = CommonUtility::parseError( $model->getErrors() );

        $this->responseJson();
    }

    private function userData()
    {
        $user_data = array(
           'id'=>Yii::app()->merchant->id,
           'merchant_id'=>Yii::app()->merchant->merchant_id,
           'first_name'=>Yii::app()->merchant->first_name,
           'last_name'=>Yii::app()->merchant->last_name,
           'email_address'=>Yii::app()->merchant->email_address,
           'contact_number'=>Yii::app()->merchant->contact_number,
           'avatar'=>Yii::app()->merchant->avatar,
           'merchant_uuid'=>Yii::app()->merchant->merchant_uuid,
           'main_account'=>Yii::app()->merchant->main_account,
           'merchant_type'=>Yii::app()->merchant->merchant_type,
           'status'=>Yii::app()->merchant->status,
       );
       $payload = [
           'iss'=>Yii::app()->request->getServerName(),
           'sub'=>0,
           'iat'=>time(),
           'token'=>Yii::app()->merchant->logintoken
       ];

       $menu_access = [];
       $main_account = Yii::app()->merchant->getState("main_account");       
       if($main_account==1){
          if(MerchantTools::hasMerchantSetMenu(Yii::app()->merchant->merchant_id)){
            $menu_access = MerchantTools::getMerchantMeta(Yii::app()->merchant->merchant_id,'menu_access');
          }
        } else {
			try {
				$menu_access = MerchantTools::getMerchantMenuRolesAccess(Yii::app()->merchant->id,Yii::app()->merchant->merchant_id);
			} catch (Exception $e) {}
	   }		    

       $user_data = JWT::encode($user_data, CRON_KEY, 'HS256');
       $jwt_token = JWT::encode($payload, CRON_KEY, 'HS256');
       $menu_access = JWT::encode($menu_access, CRON_KEY, 'HS256');       

       $this->details = array(
           'user_token'=>$jwt_token,
           'user_data'=>$user_data,
           'menu_access'=>$menu_access
       );
    }

    public function actionForgotPassword()
    {
        try {

            $email_address = Yii::app()->input->post('email_address');

            $model = AR_merchant_login::model()->find('contact_email=:contact_email', array(':contact_email'=>$email_address));
            if($model){
                $model->scenario = "send_forgot_password";
				$model->date_modified = CommonUtility::dateNow();
				if($model->save()){
                    $this->code = 1;
                    $this->msg = t("Check {{email_address}} for an email to reset your password.",array(
                    '{{email_address}}'=>$model->contact_email
                    ));
                    $this->details = array(
                       'uuid'=>$model->user_uuid
                    );
                } else $this->msg = CommonUtility::parseError( $model->getErrors() );
            } else $this->msg = t("Email address not found");
        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actionresendResetEmail()
    {
        try {

            $uuid = Yii::app()->input->post('uuid');
            $model = AR_merchant_login::model()->find('user_uuid=:user_uuid',[
                ':user_uuid'=>$uuid
            ]);
            if($model){
                $model->scenario = "send_forgot_password";
                $model->date_modified = CommonUtility::dateNow();
                if($model->save()){
                    $this->code = 1;
                    $this->msg = t("Check {{email_address}} for an email to reset your password.",array(
                        '{{email_address}}'=>$model->contact_email
                    ));
             } else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
            } else $this->msg = t(HELPER_NO_RESULTS);
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
			$model = AR_merchant_user::model()->find('session_token=:session_token',array(':session_token'=>$token));
			if($model){
				$this->code = 1;
				$this->msg = "ok";
			} else $this->msg = t("Token is not valid");

		} catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actionUpdateProfile()
    {
        try {

           $first_name = isset($this->data['first_name'])?$this->data['first_name']:'';
           $last_name = isset($this->data['last_name'])?$this->data['last_name']:'';
           $email_address = isset($this->data['email_address'])?$this->data['email_address']:'';
           $contact_number = isset($this->data['contact_number'])?$this->data['contact_number']:'';

           $filename = isset($this->data['filename'])?$this->data['filename']:'';
		   $upload_path = isset($this->data['upload_path'])?$this->data['upload_path']:'';

		   $file_data = isset($this->data['file_data'])?$this->data['file_data']:'';
		   $image_type = isset($this->data['image_type'])?$this->data['image_type']:'png';

           $model = AR_merchant_user::model()->findByPk(Yii::app()->merchant->id);
           if($model){
              $model->first_name = $first_name;
              $model->last_name = $last_name;
              $model->contact_email = $email_address;
              $model->contact_number = $contact_number;

              if(!empty($filename) && !empty($upload_path)){
                $model->profile_photo = $filename;
                $model->path = $upload_path;
              } else {
                if(!empty($file_data)){
                    $result = [];
                    try {
                        $result = CImageUploader::saveBase64Image($file_data,$image_type,"upload/avatar");
                        $model->profile_photo = isset($result['filename'])?$result['filename']:'';
                        $model->path = isset($result['path'])?$result['path']:'';
                    } catch (Exception $e) {
                        $this->msg = t($e->getMessage());
                        $this->responseJson();
                    }
                }
             }

              if($model->save()){
                $user_data = array(
                    'id'=>Yii::app()->merchant->id,
                    'merchant_id'=>Yii::app()->merchant->merchant_id,
                    'first_name'=>$first_name,
                    'last_name'=>$last_name,
                    'email_address'=>$email_address,
                    'contact_number'=>$contact_number,
                    'avatar'=>CMedia::getImage($model->profile_photo,$model->path,Yii::app()->params->size_image,CommonUtility::getPlaceholderPhoto('customer')),
                    'merchant_uuid'=>Yii::app()->merchant->merchant_uuid,
                    'main_account'=>Yii::app()->merchant->main_account,
                    'merchant_type'=>Yii::app()->merchant->merchant_type,
                    'status'=>Yii::app()->merchant->status,
                    'avatar'=>CMedia::getImage($model->profile_photo,$model->path,Yii::app()->params->size_image,CommonUtility::getPlaceholderPhoto('customer'))
                );
                $user_data = JWT::encode($user_data, CRON_KEY, 'HS256');
                $this->code = 1;
		    	$this->msg = t("Profile updated");
				$this->details = [
                    'user_data'=>$user_data
                ];
              } else $this->msg = CommonUtility::parseError( $model->getErrors() );

           } else $this->msg = t("Record not found");
        } catch (Exception $e) {
            $this->msg = $e->getMessage();
        }
        $this->responseJson();
    }

    public function actionupdateAvatar()
    {
        try {
            if(Yii::app()->merchant->id>0){
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

                  $upload_path = "upload/avatar";
                  if(!empty($upload_path)){
                    $upload_path = "upload/".Yii::app()->merchant->merchant_id;
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
					'upload_path'=>$upload_path,
                    'title'=>$title,
                    'size'=>$size
				  );

               } else $this->msg = t("Invalid file");
            } else $this->msg = t("User not login or session has expired");
        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actionupdatePassword()
    {
        try {
            $old_password = isset($this->data['old_password'])?$this->data['old_password']:'';
            $new_password = isset($this->data['new_password'])?$this->data['new_password']:'';
            $confirm_password = isset($this->data['confirm_password'])?$this->data['confirm_password']:'';
            $model = AR_merchant_user::model()->findByPk(Yii::app()->merchant->id);
            if($model){
                $model->scenario = 'update_password';
                $model->old_password = $old_password;
                $model->new_password = $new_password;
                $model->repeat_password = $confirm_password;
                $model->password = md5($model->new_password);
                if($model->save()){
                    $this->code = 1;
                    $this->msg = t("Password change");
                } else $this->msg = CommonUtility::parseModelErrorToString( $model->getErrors() );
            } else $this->msg[] = t("User not login or session has expired");
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
			AR_merchant_meta::saveMeta(Yii::app()->merchant->id,'app_push_notifications',$app_push_notifications);

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

    public function actionUpdatealocalnotification()
	{
		try {

			$local_notification = Yii::app()->input->post('local_notification');            
			$local_notification = $local_notification=="true"?1:0;            
			AR_merchant_meta::saveMeta(Yii::app()->merchant->id,'local_notification',$local_notification);

			$this->code = 1;
			$this->msg = t("Setting saved");
			$this->details = [
				'local_notification'=>$local_notification==1?true:false,
			];

		} catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

    public function actiongetAttributes()
    {
        try {

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
					'notification_event'=>Yii::app()->params->realtime['notification_event']
				]
				];
			try {
				$realtime = JWT::encode($realtime, CRON_KEY, 'HS256');
			} catch (Exception $e) {
				$realtime = '';
			}

            $legal_menu = [
                'merchant_page_privacy_policy'=>t("Privacy Policy"),
                'merchant_page_terms'=>t("Terms and condition"),
                'merchant_page_aboutus'=>t("About us"),
            ];

            $status_list = AttributesTools::StatusManagement('post',Yii::app()->language);            
            $dish_list = AttributesTools::Dish();
            $last_order = AttributesTools::lastOrderTab();
            $booking_status_list = AttributesTools::bookingStatus();               

            $this->code = 1;
			$this->msg = "Ok";

            if(!$dish_list = CommonUtility::ArrayToLabelValue($dish_list)){
                $dish_list = array();
            }

            // REGISTRATION SETTINGS
            $enabled_registration = Yii::app()->params['settings']['merchant_enabled_registration'];
            $terms = Yii::app()->params['settings']['registration_terms_condition'];
            $specific_country = Yii::app()->params['settings']['merchant_specific_country'];
            $registration_program = Yii::app()->params['settings']['registration_program'];
            $program = !empty($registration_program)?json_decode($registration_program,true):false;

            $membership_commission = [];
            try {
			    $membership_list = CMerchantSignup::membershipProgram( Yii::app()->language , (array)$program );
                foreach ($membership_list as $items) {					
					$membership_commission[$items['type_id']] = $items['commission_data'];
				}				
			} catch (Exception $e) {
				$membership_list = array();
			}            

            $services_list = [];
			try {
				$services_list = CServices::Listing(  Yii::app()->language );
			} catch (Exception $e) {
			}

            $currency_list = [];
            try {
                $currency_list = CMulticurrency::currencyList();
                $select = [''=>t("Please select")];
                $currency_list = $select+$currency_list;
            } catch (Exception $e) {
            }            

            $multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
            $multicurrency_enabled = $multicurrency_enabled==1?true:false;					

            $registration_settings = [
                'enabled_registration'=>$enabled_registration==1?true:false,
                'specific_country'=>!empty($specific_country)?json_decode($specific_country,true):array(),
                'registration_program'=>!empty($registration_program)?json_decode($registration_program,true):array(),
                'membership_list'=>$membership_list,
                'terms'=>$terms,
                'services_list'=>$services_list,
                'currency_list'=>CommonUtility::ArrayToLabelValue($currency_list),
                'membership_commission'=>$membership_commission,
                'site_name'=>isset(Yii::app()->params['settings'])?Yii::app()->params['settings']['website_title']:'',
                'multicurrency_enabled'=>$multicurrency_enabled
            ];

            $phone_prefix_data= [];
			$phone_default_country = isset(Yii::app()->params['settings']['mobilephone_settings_default_country'])?Yii::app()->params['settings']['mobilephone_settings_default_country']:'us';
	        $phone_country_list = isset(Yii::app()->params['settings']['mobilephone_settings_country'])?Yii::app()->params['settings']['mobilephone_settings_country']:'';
	        $phone_country_list = !empty($phone_country_list)?json_decode($phone_country_list,true):array();
			$filter = array(
				'only_countries'=>(array)$phone_country_list
			);
			$phone_prefix_data = ClocationCountry::listing($filter);
			$phone_default_data = ClocationCountry::get($phone_default_country);

            $android_download_url = isset(Yii::app()->params['settings']['mt_android_download_url'])?Yii::app()->params['settings']['mt_android_download_url']:'';
            $ios_download_url = isset(Yii::app()->params['settings']['mt_ios_download_url'])?Yii::app()->params['settings']['mt_ios_download_url']:'';
            $app_version_android = isset(Yii::app()->params['settings']['mt_app_version_android'])? floatval(Yii::app()->params['settings']['mt_app_version_android']) :0;
            $app_version_ios = isset(Yii::app()->params['settings']['mt_app_version_ios'])? floatval(Yii::app()->params['settings']['mt_app_version_ios']) :0;
            $enabled_language = isset(Yii::app()->params['settings']['enabled_language_merchant_app'])? floatval(Yii::app()->params['settings']['enabled_language_merchant_app']) :false;
            $enabled_language = $enabled_language==1?true:false;

            $time_range = AttributesTools::createTimeRange("00:00","24:00","15 mins","24","H:i");            
            $time_interval = AttributesTools::timeInvertvalue();       
            
            $maps_config = CMaps::config();
			$maps_config = JWT::encode($maps_config , CRON_KEY, 'HS256'); 

            $this->details = [
				'language_data'=>$lang_data,
				'money_config'=>$money_config,
				'realtime'=>$realtime,
                'legal_menu'=>$legal_menu,
                'status_list_raw'=>$status_list,
                'status_list'=>(array)CommonUtility::ArrayToLabelValue($status_list),
                'dish_list'=>(array)$dish_list,
                'language_list'=>AttributesTools::getLanguage(),
                'multi_option'=>(array)CommonUtility::ArrayToLabelValue(AttributesTools::MultiOption()),
                'two_flavor_properties'=>(array)CommonUtility::ArrayToLabelValue(AttributesTools::TwoFlavor()),
                'promo_type'=>(array)CommonUtility::ArrayToLabelValue(AttributesTools::ItemPromoType2()),
                'last_order'=>$last_order,
                'rejection_list'=>AOrders::rejectionList('rejection_list', Yii::app()->language ),
                'delayed_min_list'=>AttributesTools::delayedMinutes(),
                'cuisine'=>(array) CommonUtility::ArrayToLabelValue((array)AttributesTools::ListSelectCuisine()) ,
                'services'=>(array)CommonUtility::ArrayToLabelValue((array)AttributesTools::ListSelectServices()),
                'tags'=>(array)CommonUtility::ArrayToLabelValue((array)AttributesTools::ListSelectTags()),
                'unit'=>(array)CommonUtility::ArrayToLabelValue(AttributesTools::unit()),
                'featured'=>(array)CommonUtility::ArrayToLabelValue(AttributesTools::MerchantFeatured()),
                'two_flavor_options'=>(array)CommonUtility::ArrayToLabelValue(AttributesTools::twoFlavorOptions()),
                'tips'=>(array)CommonUtility::ArrayToLabelValue(AttributesTools::Tips()),
                'tip_type'=>(array)CommonUtility::ArrayToLabelValue(AttributesTools::TipType()),
                'day_list'=>(array)CommonUtility::ArrayToLabelValue(AttributesTools::dayList()),
                'day_week'=>(array)CommonUtility::ArrayToLabelValue(AttributesTools::dayWeekList()),
                'registration_settings'=>$registration_settings,
                'phone_prefix_data'=>$phone_prefix_data,
				'phone_default_data'=>$phone_default_data,
                'printer_list'=>CommonUtility::PrinterList(),
                'android_download_url'=>$android_download_url,
                'ios_download_url'=>$ios_download_url,
                'app_version_android'=>$app_version_android,
                'app_version_ios'=>$app_version_ios,
                'enabled_language'=>$enabled_language,
                'time_range'=>CommonUtility::ArrayToLabelValue($time_range),
                'time_interval'=>$time_interval,
                'time_interval_list'=>CommonUtility::ArrayToLabelValue($time_interval),
                'booking_status_list'=>$booking_status_list,
                'booking_status_list_value'=>CommonUtility::ArrayToLabelValue($booking_status_list),
                'maps_config'=>$maps_config,
                'salary_type'=>CommonUtility::ArrayToLabelValue(AttributesTools::DriverSalaryType()),
                'employment_type'=>CommonUtility::ArrayToLabelValue(AttributesTools::DriverEmploymentType()),
			    'commission_type'=>CommonUtility::ArrayToLabelValue(AttributesTools::DriverCommissionType()),
                'customer_status'=>CommonUtility::ArrayToLabelValue(AttributesTools::StatusManagement('customer')),
                'bank_status_list'=>CommonUtility::ArrayToLabelValue(AttributesTools::BankStatusList()),
                // 'address_label'=>CCheckout::addressLabel(),
                // 'delivery_option'=>CCheckout::deliveryOption(),
			];                        

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actionRequestEmailCode()
    {
        try {
            $merchant_user_id = Yii::app()->merchant->id;
            if($merchant_user_id>0){
                $model = AR_merchant_user::model()->findByPk($merchant_user_id);
                if($model){
                    $digit_code = CommonUtility::generateNumber(5);
                    $model->verification_code = $digit_code;
				    $model->scenario="resend_otp";
                    if($model->save()){
                       // SEND EMAIL HERE
			           $this->code = 1;
			           $this->msg = t("We sent a code to {{email_address}}.",array(
			             '{{email_address}}'=> CommonUtility::maskEmail($model->contact_email)
			           ));
                    } else $this->msg = CommonUtility::parseError($model->getErrors());
                } else $this->msg = t("Record not found");
            } else $this->msg = t("Your session has expired please relogin");
        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actiondeleteAccount()
    {
        try {
            $merchant_user_id = Yii::app()->merchant->id;
            $code = Yii::app()->input->post('code');
            $model = AR_merchant_user::model()->findByPk($merchant_user_id);
            if($model){
                if($model->verification_code==$code){
                    $model->status = "deleted";
				    $model->save();				
                    Yii::app()->merchant->logout(false);
                    $this->code = 1;
                    $this->msg = t("Your request to delete your account has been submitted");
                    $this->details = [];
                } else $this->msg[] = t("Invalid verification code");
            } else $this->msg[] = t("User not login or session has expired");
            $this->responseJson();

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

    public function actiongeStoreMenu()
    {
        try {
           $merchant_id = Yii::app()->merchant->merchant_id;
		   $category = CMerchantMenu::getCategory2($merchant_id,Yii::app()->language);


           try {
                $uncat = CMerchantMenu::getUncategorizeitem($merchant_id);
                $uncategorize = [
                    'cat_id'=>0,
                    'category_name'=>t("Uncategorized"),
                    'category_uiid'=>"uncategorized",
                    'category_description'=>"",
                    'items'=>$uncat,
                    'url_image'=>CMedia::getImage('','',Yii::app()->params->size_image_thumbnail
                    ,CommonUtility::getPlaceholderPhoto('item')),
                    'url_icon'=>CMedia::getImage('','',Yii::app()->params->size_image_thumbnail
                    ,CommonUtility::getPlaceholderPhoto('item')),
                ];
                array_push($category,$uncategorize);
           } catch (Exception $e) {
                //
           }

           try {
		     $items = CMerchantMenu::getMenu2($merchant_id,Yii::app()->language);
           } catch (Exception $e) {
             $items = [];
           }

		   $data = array(
		     'category'=>$category,
		     'items'=>$items
		   );
		   $this->code = 1; $this->msg = "OK";
		   $this->details = array(
		     'merchant_id'=>$merchant_id,
		     'data'=>$data
		   );
        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actionaddCategory()
    {
        try {

            $merchant_id = (integer) Yii::app()->merchant->merchant_id;
            $cat_id = isset($this->data['cat_id'])?intval($this->data['cat_id']):0;

            if($cat_id>0){
                $model = AR_category::model()->findByPk($cat_id);
            } else $model = new AR_category();

            $model->merchant_id = intval($merchant_id);
            $model->category_name = isset($this->data['category_name'])?$this->data['category_name']:'';
            $model->category_description = isset($this->data['category_description'])?$this->data['category_description']:'';
            $model->status = isset($this->data['status'])?$this->data['status']:'';
            $model->dish_selected = isset($this->data['dish'])?$this->data['dish']:'';

            $model->photo = isset($this->data['featured_filename'])?$this->data['featured_filename']:'';
            $model->path = isset($this->data['upload_path'])?$this->data['upload_path']:'';

            $file_data = isset($this->data['file_data'])?$this->data['file_data']:'';
            $image_type = isset($this->data['image_type'])?$this->data['image_type']:'png';

            if(!empty($file_data)){
                $result = [];
                try {
                    $result = CImageUploader::saveBase64Image($file_data,$image_type,"upload/".Yii::app()->merchant->merchant_id);
                    $model->photo = isset($result['filename'])?$result['filename']:'';
                    $model->path = isset($result['path'])?$result['path']:'';
                } catch (Exception $e) {
                    $this->msg = t($e->getMessage());
                    $this->responseJson();
                }
            }

            $translation_data = isset($this->data['translation_data'])?$this->data['translation_data']:'';
            if(is_array($translation_data) && count($translation_data)>=1){
                $name = isset($translation_data[0])? (isset($translation_data[0]['name'])?$translation_data[0]['name']:array()) :array();
                $description = isset($translation_data[1])? (isset($translation_data[1]['description'])?$translation_data[1]['description']:array())  :array();

                $model->category_translation=$name;
                $model->category_description_translation=$description;
            }

            if($model->save()){
                $this->code = 1;
                $this->msg = t("Category succesfully added");
            } else $this->msg = CommonUtility::parseError($model->getErrors());

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actiondeleteCategory()
    {
        try {

            $cat_id = Yii::app()->input->post('cat_id');
            $merchant_id = (integer) Yii::app()->merchant->merchant_id;

            $model = AR_category::model()->find('merchant_id=:merchant_id AND cat_id=:cat_id',
		    array(':merchant_id'=>$merchant_id, ':cat_id'=>$cat_id ));
            if($model){
                $model->delete();
                $this->code = 1;
                $this->msg = "Ok";
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actiongetCategory()
    {
        try {

            $cat_id = intval(Yii::app()->input->post('cat_id'));
            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $model = AR_category::model()->find("merchant_id=:merchant_id AND cat_id=:cat_id",[
                ':merchant_id'=>$merchant_id,
                ':cat_id'=>$cat_id
            ]);
            if($model){
                $this->code = 1;
                $this->msg = "Ok";

                $dish_selected = array();
                $find = AR_category_relationship_dish::model()->findAll(
                    'cat_id=:cat_id',
                    array(':cat_id'=> intval($model->cat_id) )
                );
                if($find){
                    foreach ($find as $items) {
                        $dish_selected[]=$items->dish_id;
                    }
                }

                $translation = AttributesTools::GetFromTranslation($cat_id,'{{category}}',
                    '{{category_translation}}',
                    'cat_id',
                    array('cat_id','category_name','category_description'),
                    array(
                    'category_name'=>'category_translation',
                    'category_description'=>'category_description_translation'
                    )
  			   );
               if(is_array($translation) && count($translation)>=1){
                  $translation['name'] = $translation['category_name'];
                  $translation['description'] = $translation['category_description'];
                  unset($translation['category_name']);
                  unset($translation['category_description']);
               }


                $this->details = [
                    'cat_id'=>$model->cat_id,
                    'category_name'=>$model->category_name,
                    'category_description'=>$model->category_description,
                    'dish_selected'=>$dish_selected,
                    'photo'=>$model->photo,
                    'icon'=>$model->icon,
                    'path'=>$model->path,
                    'photo_url'=>CMedia::getImage($model->photo,$model->path,Yii::app()->params->size_image,CommonUtility::getPlaceholderPhoto('item')),
                    'icon_url'=>CMedia::getImage($model->icon,$model->path,Yii::app()->params->size_image,CommonUtility::getPlaceholderPhoto('item')),
                    'status'=>$model->status,
                    'translation'=>$translation
                ];
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actiongeStoreAddonMenu()
    {
        try {
            $sort='ORDER BY a.sequence ASC';
            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $data = CMerchantMenu::getStoreAddon($merchant_id,Yii::app()->language,$sort);

            try {
                $uncat = CMerchantMenu::getUncategorizeAddonitem($merchant_id);
                $uncategorize = [
                    'subcat_id'=>0,
                    'subcategory_name'=>t("Uncategorized"),
                    'subcategory_description'=>"",
                    'items'=>$uncat,
                    'url_image'=>CMedia::getImage('','',Yii::app()->params->size_image_thumbnail
					,CommonUtility::getPlaceholderPhoto('item')),
                ];
                array_push($data,$uncategorize);
            } catch (Exception $e) {
                //
            }

            try {
               $sort='ORDER BY sub_item_id DESC';
               $items = CMerchantMenu::getStoreAddonItems($merchant_id,Yii::app()->language,$sort);
            } catch (Exception $e) {
               $items = [];
            }

            $this->code = 1; $this->msg = "Ok";
            $this->details = [
                'data'=>$data,
                'items'=>$items
            ];

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actiongetAddonCategory()
    {
        try {

            $subcat_id = intval(Yii::app()->input->post('subcat_id'));
            $merchant_id = intval(Yii::app()->merchant->merchant_id);

            $model = AR_subcategory::model()->find("merchant_id=:merchant_id AND subcat_id=:subcat_id",[
                ':merchant_id'=>$merchant_id,
                ':subcat_id'=>$subcat_id
            ]);
            if($model){
                $this->code = 1;
                $this->msg = "Ok";

                $translation = AttributesTools::GetFromTranslation($subcat_id,'{{subcategory}}',
                        '{{subcategory_translation}}',
                        'subcat_id',
                        array('subcat_id','subcategory_name','subcategory_description'),
                        array(
                        'subcategory_name'=>'subcategory_name_translation',
                        'subcategory_description'=>'subcategory_description_translation'
                        )
                    );
                if(is_array($translation) && count($translation)>=1){
                    $translation['name'] = $translation['subcategory_name'];
                    $translation['description'] = $translation['subcategory_description'];
                    unset($translation['subcategory_name']);
                    unset($translation['subcategory_description']);
                }

                $this->details = [
                    'subcat_id '=>$model->subcat_id ,
                    'subcategory_name'=>$model->subcategory_name,
                    'subcategory_description'=>$model->subcategory_description,
                    'photo'=>$model->featured_image,
                    'path'=>$model->path,
                    'photo_url'=>CMedia::getImage($model->featured_image,$model->path,Yii::app()->params->size_image,CommonUtility::getPlaceholderPhoto('item')),
                    'status'=>$model->status,
                    'translation'=>$translation
                ];
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);
        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actionaddAddonCategory()
    {
        try {

            $merchant_id = (integer) Yii::app()->merchant->merchant_id;
            $subcat_id = isset($this->data['subcat_id'])?intval($this->data['subcat_id']):0;
            $translation_data = isset($this->data['translation_data'])?$this->data['translation_data']:'';

            if($subcat_id>0){
                $model = AR_subcategory::model()->findByPk($subcat_id);
            } else $model = new AR_subcategory();

            $model->merchant_id = intval($merchant_id);
            $model->subcategory_name = isset($this->data['subcategory_name'])?$this->data['subcategory_name']:'';
            $model->subcategory_description = isset($this->data['subcategory_description'])?$this->data['subcategory_description']:'';
            $model->status = isset($this->data['status'])?$this->data['status']:'';

            $model->featured_image = isset($this->data['featured_filename'])?$this->data['featured_filename']:'';
            $model->path = isset($this->data['upload_path'])?$this->data['upload_path']:'';

            $file_data = isset($this->data['file_data'])?$this->data['file_data']:'';
            $image_type = isset($this->data['image_type'])?$this->data['image_type']:'png';
            if(!empty($file_data)){
                $result = [];
                try {
                    $result = CImageUploader::saveBase64Image($file_data,$image_type,"upload/".Yii::app()->merchant->merchant_id);
                    $model->featured_image = isset($result['filename'])?$result['filename']:'';
                    $model->path = isset($result['path'])?$result['path']:'';
                } catch (Exception $e) {
                    $this->msg = t($e->getMessage());
                    $this->responseJson();
                }
            }

            if(is_array($translation_data) && count($translation_data)>=1){
                $name = isset($translation_data[0])? (isset($translation_data[0]['name'])?$translation_data[0]['name']:array()) :array();
                $description = isset($translation_data[1])? (isset($translation_data[1]['description'])?$translation_data[1]['description']:array())  :array();

                $model->subcategory_translation=$name;
                $model->subcategory_description_translation=$description;
            }

            if($model->save()){
                $this->code = 1;
                $this->msg =  $subcat_id>0?t("Category updated"):t("Category succesfully added");
            } else $this->msg = CommonUtility::parseError($model->getErrors());

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actiondeleteAddonCategory()
    {
        try {

            $subcat_id = Yii::app()->input->post('subcat_id');
            $merchant_id = (integer) Yii::app()->merchant->merchant_id;

            $model = AR_subcategory::model()->find('merchant_id=:merchant_id AND subcat_id=:subcat_id',
		    array(':merchant_id'=>$merchant_id, ':subcat_id'=>$subcat_id ));
            if($model){
                $model->delete();
                $this->code = 1;
                $this->msg = "Ok";
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actiongetAddonCategoryList()
    {
        try {

            $merchant_id = (integer) Yii::app()->merchant->merchant_id;
            $data = CDataFeed::subcategoryList($merchant_id,Yii::app()->language);
            $this->code = 1;
            $this->msg = "Ok";
            $this->details = [
                'data'=>$data,
            ];

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actionaddAddonItem()
    {
        try {

            $merchant_id = (integer) Yii::app()->merchant->merchant_id;
            $id = isset($this->data['id'])?intval($this->data['id']):0;

            if($id>0){
                $model = AR_subcategory_item::model()->findByPk($id);
            } else $model = new AR_subcategory_item();

            $model->merchant_id = intval($merchant_id);
            $model->sub_item_name = isset($this->data['name'])?$this->data['name']:'';
            $model->item_description = isset($this->data['description'])?$this->data['description']:'';
            $model->price = isset($this->data['price'])?floatval($this->data['price']):0;
            $model->status = isset($this->data['status'])?$this->data['status']:'';
            $model->category_selected = isset($this->data['category'])?$this->data['category']:'';

            $model->photo = isset($this->data['featured_filename'])?$this->data['featured_filename']:'';
            $model->path = isset($this->data['upload_path'])?$this->data['upload_path']:'';

            $file_data = isset($this->data['file_data'])?$this->data['file_data']:'';
            $image_type = isset($this->data['image_type'])?$this->data['image_type']:'png';
            if(!empty($file_data)){
                $result = [];
                try {
                    $result = CImageUploader::saveBase64Image($file_data,$image_type,"upload/".Yii::app()->merchant->merchant_id);
                    $model->photo = isset($result['filename'])?$result['filename']:'';
                    $model->path = isset($result['path'])?$result['path']:'';
                } catch (Exception $e) {
                    $this->msg = t($e->getMessage());
                    $this->responseJson();
                }
            }

            $translation_data = isset($this->data['translation_data'])?$this->data['translation_data']:'';
            if(is_array($translation_data) && count($translation_data)>=1){
                $name = isset($translation_data[0])? (isset($translation_data[0]['name'])?$translation_data[0]['name']:array()) :array();
                $description = isset($translation_data[1])? (isset($translation_data[1]['description'])?$translation_data[1]['description']:array())  :array();

                $model->sub_item_name_translation=$name;
                $model->item_description_translation=$description;
            }

            if($model->save()){
                $this->code = 1;
                $this->msg =  $id>0?t("Addon item updated"):t("Addon item succesfully added");
            } else $this->msg = CommonUtility::parseError($model->getErrors());

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actiongetAddonItem()
    {
        try {

            $merchant_id = (integer) Yii::app()->merchant->merchant_id;
            $id = Yii::app()->input->post('id');
            $model = AR_subcategory_item::model()->find("merchant_id=:merchant_id AND sub_item_id=:sub_item_id",[
                ':merchant_id'=>$merchant_id,
                ':sub_item_id'=>$id
            ]);
            if($model){

                $this->code = 1;
                $this->msg = "Ok";

                $find = AR_subcategory_item_relationships::model()->findAll(
                    'sub_item_id=:sub_item_id',
                    array(':sub_item_id'=> intval($model->sub_item_id) )
                );
                if($find){
                    $selected = array();
                    foreach ($find as $items) {
                        $selected[]=$items->subcat_id;
                    }
                    $model->category_selected = $selected;
                }

                $translation = AttributesTools::GetFromTranslation($id,'{{subcategory_item}}',
                        '{{subcategory_item_translation}}',
                        'sub_item_id',
                        array('sub_item_id','sub_item_name','item_description'),
                        array(
                        'sub_item_name'=>'sub_item_name_translation',
                        'item_description'=>'item_description_translation'
                        )
                    );
                if(is_array($translation) && count($translation)>=1){
                    $translation['name'] = $translation['sub_item_name'];
                    $translation['description'] = $translation['item_description'];
                    unset($translation['sub_item_name']);
                    unset($translation['item_description']);
                }

                $this->details = [
                    'id '=>$model->sub_item_id ,
                    'sub_item_name'=>$model->sub_item_name,
                    'item_description'=>$model->item_description,
                    'price'=>$model->price,
                    'photo'=>$model->photo,
                    'path'=>$model->path,
                    'photo_url'=>CMedia::getImage($model->photo,$model->path,Yii::app()->params->size_image,CommonUtility::getPlaceholderPhoto('item')),
                    'status'=>$model->status,
                    'category'=>$model->category_selected,
                    'translation'=>$translation
                ];

            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);
        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actiondeleteAddonItem()
    {
        try {

            $id = Yii::app()->input->post('id');
            $merchant_id = (integer) Yii::app()->merchant->merchant_id;

            $model = AR_subcategory_item::model()->find('merchant_id=:merchant_id AND sub_item_id=:sub_item_id',
		    array(':merchant_id'=>$merchant_id, ':sub_item_id'=>$id ));
            if($model){
                $model->delete();
                $this->code = 1;
                $this->msg = "Ok";
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actionsetAddonItemAvailable()
    {
        try {

            $id = Yii::app()->input->post('id');
            $active = Yii::app()->input->post('active');
            $merchant_id = (integer) Yii::app()->merchant->merchant_id;

            $model = AR_subcategory_item::model()->find('merchant_id=:merchant_id AND sub_item_id=:sub_item_id',
		    array(':merchant_id'=>$merchant_id, ':sub_item_id'=>$id ));

            if($model){
                $model->scenario = 'setactive';
                $model->status=$active=="true"?'publish':'pending';
                if($model->save()){
                    $this->code = 1;
                    $this->msg = "Ok";
                } else $this->msg = CommonUtility::parseError( $model->getErrors() );
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actiongetCategoryList()
    {
        try {

            $merchant_id = (integer) Yii::app()->merchant->merchant_id;
            $data = CDataFeed::categoryList($merchant_id,Yii::app()->language);            
            $this->code = 1;
            $this->msg = "Ok";
            $this->details = [
                'data'=>$data,
                'unit'=>AttributesTools::Size( $merchant_id ),
                'item_featured'=>AttributesTools::ItemFeatured()
            ];

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actioncreateItem()
    {
        try {

            $merchant_id = (integer) Yii::app()->merchant->merchant_id;
            $id = isset($this->data['id'])?trim($this->data['id']):'';

            if(!empty($id)){
                $model = AR_item::model()->find("merchant_id=:merchant_id AND  item_token=:item_token",[
                    ':merchant_id'=>$merchant_id,
                    ':item_token'=>$id
                ]);
                $model->scenario = 'update';
            } else {
                $model = new AR_item();
                $model->scenario = 'create';
            }

            $multi_language = CommonUtility::MultiLanguage();
            $model->multi_language = $multi_language;

            $model->merchant_id = intval($merchant_id);
            $model->item_name = isset($this->data['item_name'])?$this->data['item_name']:'';
            $model->item_short_description = isset($this->data['item_short_description'])?$this->data['item_short_description']:'';
            $model->item_description = isset($this->data['item_description'])?$this->data['item_description']:'';
            $model->item_price = isset($this->data['item_price'])?floatval($this->data['item_price']):0;
            $model->item_unit = isset($this->data['item_unit'])?$this->data['item_unit']:'';
            $model->category_selected = isset($this->data['category_selected'])?$this->data['category_selected']:'';
            $model->item_featured = isset($this->data['item_featured'])?$this->data['item_featured']:'';
            $model->status = isset($this->data['status'])?$this->data['status']:'';

            $model->photo = isset($this->data['featured_filename'])?$this->data['featured_filename']:'';
            $model->path = isset($this->data['upload_path'])?$this->data['upload_path']:'';

            $file_data = isset($this->data['file_data'])?$this->data['file_data']:'';
            $image_type = isset($this->data['image_type'])?$this->data['image_type']:'png';

            if(!empty($file_data)){
                $result = [];
                try {
                    $result = CImageUploader::saveBase64Image($file_data,$image_type,"upload/".Yii::app()->merchant->merchant_id);
                    $model->photo = isset($result['filename'])?$result['filename']:'';
                    $model->path = isset($result['path'])?$result['path']:'';
                } catch (Exception $e) {
                    $this->msg = t($e->getMessage());
                    $this->responseJson();
                }
            }

            $translation_data = isset($this->data['translation_data'])?$this->data['translation_data']:'';            
            if(is_array($translation_data) && count($translation_data)>=1){
                $name = isset($translation_data[0])? (isset($translation_data[0]['name'])?$translation_data[0]['name']:array()) :array();
                $description = isset($translation_data[1])? (isset($translation_data[1]['description'])?$translation_data[1]['description']:array())  :array();

                $short_description = isset($translation_data[1])? (isset($translation_data[2]['short_description'])?$translation_data[2]['short_description']:array())  :array();
                if(is_array($short_description) && count($short_description)<=1){
                    $short_description = $description;
                }                

                $model->item_name_translation=$name;
                $model->item_description_translation=$description;
                $model->item_short_description_translation = $short_description;                
            }

            if($model->save()){
                $this->code = 1;
                $this->msg =  $id>0?t("Item updated"):t("Item succesfully added");
            } else $this->msg = CommonUtility::parseError($model->getErrors());

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actiongetItem()
    {
        try {

            $merchant_id = (integer) Yii::app()->merchant->merchant_id;
            $id = Yii::app()->input->post('id');
            $model = AR_item::model()->find("merchant_id=:merchant_id AND item_token=:item_token",[
                ':merchant_id'=>$merchant_id,
                ':item_token'=>$id
            ]);
            if($model){
                $this->code = 1;
                $this->msg = "Ok";

                $category_selected = '';
                try {
                    $category_selected = CDataFeed::getCategorySelected($merchant_id, $model->item_id , Yii::app()->language );
                } catch (Exception $e) {
                    //
                }

                $item_featured = '';
                try {
                    $item_featured = CDataFeed::getFeaturedSelected($merchant_id,$model->item_id);
                } catch (Exception $e) {
                    //
                }

                $price_list = ''; $price_range = '';
                try {
                    $price_list = CDataFeed::getItemPrice($merchant_id,$model->item_id,Yii::app()->language);
                    if(count($price_list)>1){
                        $end_price = count($price_list)-1;
                        $price = isset($price_list[0])?$price_list[0]:'';
                        $start_range = Price_Formatter::formatNumber($price_list[0]['raw_price']);
                        $end_range = Price_Formatter::formatNumber($price_list[$end_price]['raw_price']);
                        $price_range = "$start_range - $end_range";
                    } else {
                        $price = isset($price_list[0])?$price_list[0]:'';
                        if(isset($price['price'])){
                            $price_range = Price_Formatter::formatNumber($price_list[0]['raw_price']);
                            if( !empty($price_list[0]['size_name']) ){
                                $price_range = t("{price} {size_name}",[
                                    '{price}'=>$price_range,
                                    '{size_name}'=>$price_list[0]['size_name']
                                ]);
                            }
                        }
                    }
                } catch (Exception $e) {
                    //
                }

                $total_addon = 0;
                try {
                    $total_addon = CDataFeed::getTotalAddon($merchant_id,$model->item_id);
                } catch (Exception $e) {
                    //
                }


                $translation = AttributesTools::GetFromTranslation($model->item_id,'{{item}}',
                        '{{item_translation}}',
                        'item_id',
                        array('item_id','item_name','item_description'),
                        array(
                        'item_name'=>'item_name_translation',
                        'item_description'=>'item_description_translation'
                        )
                    );
                if(is_array($translation) && count($translation)>=1){
                    $translation['name'] = $translation['item_name'];
                    $translation['description'] = $translation['item_description'];
                    unset($translation['item_name']);
                    unset($translation['item_description']);
                }

                $availability_data = AR_availability::getValue($model->merchant_id,'item',$model->item_id);                
                
                $this->details = [
                    'id '=>$model->item_id ,
                    'item_name'=>$model->item_name,
                    'item_short_description'=>$model->item_short_description,
                    'item_description'=>$model->item_description,
                    'item_price'=>$model->item_price,
                    'photo'=>$model->photo,
                    'path'=>$model->path,
                    'photo_url'=>CMedia::getImage($model->photo,$model->path,Yii::app()->params->size_image,CommonUtility::getPlaceholderPhoto('item')),
                    'status'=>$model->status,
                    'category_selected'=>is_array($category_selected)?$category_selected:array(),
                    'item_featured'=>is_array($item_featured)?$item_featured:array(),
                    'price_range'=>$price_range,
                    'price_list'=>$price_list,
                    'translation'=>$translation,
                    'total_addon_raw'=>$total_addon,
                    'total_addon'=>Yii::t('mobile', '{n} addon|{n} addons', $total_addon),
                    'available'=>$model->available==1?true:false,
                    'not_for_sale'=>$model->not_for_sale==1?true:false,
                    'available_at_specific'=>$model->available_at_specific==1?true:false,
                    'track_stock'=>$model->track_stock==1?true:false,
                    'sku'=>$model->sku,
                    'supplier_id'=>$model->supplier_id,
                    'availability_data'=>$availability_data
                ];

            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);
        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actiondeleteItem()
    {
        try {

            $id = Yii::app()->input->post('id');
            $merchant_id = (integer) Yii::app()->merchant->merchant_id;

            $model = AR_item::model()->find('merchant_id=:merchant_id AND item_token=:item_token',
		    array(':merchant_id'=>$merchant_id, ':item_token'=>$id ));
            if($model){
                $model->delete();
                $this->code = 1;
                $this->msg = "Ok";
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actiongetPriceList()
    {
        try {
            $id = Yii::app()->input->post('id');
            $merchant_id = (integer) Yii::app()->merchant->merchant_id;
            $model = AR_item::model()->find('merchant_id=:merchant_id AND item_token=:item_token',
		    array(':merchant_id'=>$merchant_id, ':item_token'=>$id ));
            if($model){
                $price_list = CDataFeed::getItemPrice($merchant_id,$model->item_id,Yii::app()->language);
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'item_name'=>$model->item_name,
                    'price_list'=>$price_list
                ];
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);
        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actiongetPriceAttributes()
    {
        try {

            $merchant_id = (integer) Yii::app()->merchant->merchant_id;
            $this->code = 1;
            $this->msg = "Ok";
            $this->details = [
                'unit'=>AttributesTools::Size( $merchant_id ),
                'discount_type'=> AttributesTools::CommissionType(),
            ];

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actioncreateItemPrice()
    {
        try {


            $merchant_id = (integer) Yii::app()->merchant->merchant_id;
            $item_uuid = isset($this->data['item_uuid'])?trim($this->data['item_uuid']):'';
            $item_size_id = isset($this->data['item_size_id'])?intval($this->data['item_size_id']):0;

            $item = AR_item::model()->find('merchant_id=:merchant_id AND item_token=:item_token',
		    array(':merchant_id'=>$merchant_id, ':item_token'=>$item_uuid ));

            if(!$item){
                $this->msg = t(HELPER_RECORD_NOT_FOUND);
                $this->responseJson();
            }

            if($item_size_id>0){
                $model = AR_item_relationship_size::model()->find("merchant_id=:merchant_id AND  item_size_id=:item_size_id",[
                    ':merchant_id'=>$merchant_id,
                    ':item_size_id'=>$item_size_id
                ]);
            } else $model = new AR_item_relationship_size;

            $model->scenario = "add_price";

            $model->merchant_id = (integer) $merchant_id;
            $model->item_token = $item_uuid;
            $model->item_id = (integer) $item->item_id;
            $model->size_id = isset($this->data['size_id'])?intval($this->data['size_id']):0;
            $model->price = isset($this->data['price'])?floatval($this->data['price']):0;
            $model->cost_price = isset($this->data['cost_price'])?floatval($this->data['cost_price']):0;
            $model->discount = isset($this->data['discount'])?floatval($this->data['discount']):0;
            $model->discount_type = isset($this->data['discount_type'])?trim($this->data['discount_type']):'';
            $model->sku = isset($this->data['sku'])?trim($this->data['sku']):'';

            $discount_start = isset($this->data['discount_start'])?trim($this->data['discount_start']):'';
            $discount_end = isset($this->data['discount_end'])?trim($this->data['discount_end']):'';

            if(!empty($discount_start)){
                $model->discount_start = $discount_start;
            }
            if(!empty($discount_end)){
                $model->discount_end = $discount_end;
            }

            if($model->save()){
                $this->code = 1;
                $this->msg =  $item_size_id>0?t("Price updated"):t("Price succesfully added");
            } else $this->msg = CommonUtility::parseError($model->getErrors());

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actiondeletePrice()
    {
        try {

            $merchant_id = (integer) Yii::app()->merchant->merchant_id;
            $item_uuid = Yii::app()->input->post('item_uuid');
            $item_size_id = Yii::app()->input->post('item_size_id');

            $model = AR_item_relationship_size::model()->find("merchant_id=:merchant_id AND item_size_id=:item_size_id",[
                ':merchant_id'=>$merchant_id,
                ':item_size_id'=>$item_size_id
            ]);
            if($model){
                $model->delete();
                $this->code = 1;
                $this->msg = "Ok";
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actiongetPrice()
    {
        try {

            $merchant_id = (integer) Yii::app()->merchant->merchant_id;
            $item_size_id = Yii::app()->input->post('item_size_id');
            $model = AR_item_relationship_size::model()->find("merchant_id=:merchant_id AND item_size_id=:item_size_id",[
                ':merchant_id'=>$merchant_id,
                ':item_size_id'=>$item_size_id
            ]);
            if($model){
                $this->code = 1;
                $this->msg = "Ok";

                $size_list = AttributesTools::Size( $merchant_id );
                $discount_type_list = AttributesTools::CommissionType();

                $size ='';
                if($model->size_id>0){
                    $size = [
                        'label'=>$size_list[$model->size_id],
                        'value'=>$model->size_id
                    ];
                }


                $discounttype='';
                if(!empty($model->discount_type)){
                    $discounttype = [
                        'label'=>$discount_type_list[$model->discount_type],
                        'value'=>$model->discount_type
                    ];
                }

                $this->details = [
                    'price'=>Price_Formatter::formatNumberNoSymbol($model->price),
                    'size_id'=>$size ,
                    'cost_price'=>Price_Formatter::formatNumberNoSymbol($model->cost_price),
                    'discount'=>Price_Formatter::formatNumberNoSymbol($model->discount),
                    'discount_type'=>$discounttype ,
                    'discount_start'=>$model->discount_start ,
                    'discount_end'=>$model->discount_end ,
                    'sku'=>$model->sku ,
                ];
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actiongetAddonlist()
    {
        try {

            $id = Yii::app()->input->post('id');
            $merchant_id = (integer) Yii::app()->merchant->merchant_id;
            $model = AR_item::model()->find('merchant_id=:merchant_id AND item_token=:item_token',
		    array(':merchant_id'=>$merchant_id, ':item_token'=>$id ));
            if($model){
                $this->code = 1;
                $this->msg = "Ok";
                $list = CDataFeed::getItemAddon($merchant_id,$model->item_id,Yii::app()->language);
                $this->details = [
                    'item_name'=>$model->item_name,
                    'list'=>$list
                ];
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);
        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actiondeleteItemAddon()
    {
        try {

            $merchant_id = (integer) Yii::app()->merchant->merchant_id;
            $id = Yii::app()->input->post('id');

            $model = AR_item_addon::model()->find("merchant_id=:merchant_id AND id=:id",[
                ':merchant_id'=>intval($merchant_id),
                ':id'=>intval($id)
            ]);
            if($model){
                $model->delete();
                $this->code = 1;
                $this->msg = "Ok";
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actionitemAddonCreate()
    {
        try {


            $merchant_id = (integer) Yii::app()->merchant->merchant_id;
            $item_uuid = isset($this->data['item_uuid'])?trim($this->data['item_uuid']):'';
            $id = isset($this->data['id'])?intval($this->data['id']):0;
            $item_size_id = isset($this->data['item_size_id'])?intval($this->data['item_size_id']):0;
            $subcat_id = isset($this->data['subcat_id'])?intval($this->data['subcat_id']):0;
            $multi_option_value = isset($this->data['multi_option_value'])?intval($this->data['multi_option_value']):'';
            $multi_option = isset($this->data['multi_option'])?trim($this->data['multi_option']):'';
            $multi_option_value_selection = isset($this->data['multi_option_value_selection'])?trim($this->data['multi_option_value_selection']):'';
            $require_addon = isset($this->data['require_addon'])?intval($this->data['require_addon']):'';
            $pre_selected = isset($this->data['pre_selected'])?trim($this->data['pre_selected']):'';

            $item = AR_item::model()->find('merchant_id=:merchant_id AND item_token=:item_token',
		    array(':merchant_id'=>$merchant_id, ':item_token'=>$item_uuid ));

            if($item){
               if($id>0){
                 $model = AR_item_addon::model()->find("merchant_id=:merchant_id AND id=:id",[
                    ":merchant_id"=>$merchant_id,
                    ":id"=>$id
                 ]);
               } else $model = new AR_item_addon;

               $model->merchant_id = $merchant_id;
               $model->merchantid = $merchant_id;
               $model->item_id = $item->item_id;
               $model->itemid = $item->item_id;
               $model->item_size_id = $item_size_id;
               $model->subcat_id = $subcat_id;
               $model->multi_option = $multi_option;
               $model->multi_option_value_selection = $multi_option_value_selection;
               $model->multi_option_value_text = $multi_option_value>0?$multi_option_value:'';
               $model->require_addon = $require_addon;
               $model->pre_selected = $pre_selected;

               if($model->save()){
                 $this->code = 1;
                 $this->msg =  $id>0?t("Item Addon updated"):t("Item Addon succesfully added");
              } else $this->msg = CommonUtility::parseError($model->getErrors());

            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actiongetItemAddon()
    {
        try {

            $merchant_id = (integer) Yii::app()->merchant->merchant_id;
            $item_uuid = isset($this->data['item_uuid'])?trim($this->data['item_uuid']):'';
            $id = isset($this->data['id'])?intval($this->data['id']):0;

            $item = AR_item::model()->find('merchant_id=:merchant_id AND item_token=:item_token',
		    array(':merchant_id'=>$merchant_id, ':item_token'=>$item_uuid ));

            if($item){
                $model = AR_item_addon::model()->find("merchant_id=:merchant_id AND id=:id",[
                    ':merchant_id'=>$merchant_id,
                    ':id'=>$id
                ]);
                if($model){
                    $this->code  = 1;
                    $this->msg = "Ok";
                    $this->details = [
                        'item_id'=>$model->item_id,
                        'item_size_id'=>$model->item_size_id,
                        'subcat_id'=>$model->subcat_id,
                        'multi_option'=>$model->multi_option,
                        'multi_option_value'=>$model->multi_option_value,
                        'require_addon'=>$model->require_addon==1?true:false,
                        'pre_selected'=>$model->pre_selected==1?true:false,
                    ];
                } else $this->msg = t(HELPER_RECORD_NOT_FOUND);
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actiongetItemAttributes()
    {
        try {

            $merchant_id = (integer) Yii::app()->merchant->merchant_id;
            $item_uuid = Yii::app()->input->post('item_uuid');

            $item = AR_item_attributes::model()->find('merchant_id=:merchant_id AND item_token=:item_token',
		    array(':merchant_id'=>$merchant_id, ':item_token'=>$item_uuid ));
            if($item){

                $cooking_selected = []; $ingredients_selected = []; $dish_selected=[];
                $delivery_options_selected = [];

                $cooking_selected = CDataFeed::getItemMeta($merchant_id,$item->item_id,'cooking_ref');
                $ingredients_selected = CDataFeed::getItemMeta($merchant_id,$item->item_id,'ingredients');
                $dish_selected = CDataFeed::getItemMeta($merchant_id,$item->item_id,'dish');
                $delivery_options_selected = CDataFeed::getItemMeta($merchant_id,$item->item_id,'delivery_options');

                $allergens = AttributesTools::adminMetaList('allergens',Yii::app()->language,true);			                
                $allergens = is_array($allergens) && count($allergens)>=1? CommonUtility::ArrayToLabelValue($allergens) : '';  
                $allergen_selected = CMerchantMenu::getAllergens($merchant_id, $item->item_id );

                $data = [
                    'points_enabled'=>$item->points_enabled==1?true:false,
                    'packaging_incremental'=>$item->packaging_incremental==1?true:false,
                    'cooking_ref_required'=>$item->cooking_ref_required==1?true:false,
                    'ingredients_preselected'=>$item->ingredients_preselected==1?true:false,
                    'points_earned'=>Price_Formatter::convertToRaw($item->points_earned,2),
                    'packaging_fee'=>Price_Formatter::convertToRaw($item->packaging_fee,2) ,
                    'cooking_selected'=>$cooking_selected,
                    'ingredients_selected'=>$ingredients_selected,
                    'dish_selected'=>$dish_selected,
                    'delivery_options_selected'=>$delivery_options_selected,
                    'allergen_selected'=>$allergen_selected
                ];


                $this->code  = 1;
                $this->msg = "Ok";
                $this->details = [
                    'data' => $data,
                    'cooking_ref'=>AttributesTools::Cooking($merchant_id),
                    'ingredients'=>AttributesTools::Ingredients($merchant_id),
                    'dish'=>AttributesTools::Dish(),
                    'transport'=>AttributesTools::transportType(),
                    'allergens'=>$allergens
                ];
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actionsaveItemAttributes()
    {
        try {

            $merchant_id = (integer) Yii::app()->merchant->merchant_id;
            $item_uuid = isset($this->data['item_uuid'])?trim($this->data['item_uuid']):'';

            $model = AR_item_attributes::model()->find('merchant_id=:merchant_id AND item_token=:item_token',
		    array(':merchant_id'=>$merchant_id, ':item_token'=>$item_uuid ));
            if($model){

                $model->scenario = "item_attributes";

                $model->points_enabled = isset($this->data['points_enabled'])?intval($this->data['points_enabled']):0;
                $model->packaging_incremental = isset($this->data['packaging_incremental'])?intval($this->data['packaging_incremental']):0;
                $model->cooking_ref_required = isset($this->data['cooking_ref_required'])?intval($this->data['cooking_ref_required']):0;
                $model->ingredients_preselected = isset($this->data['ingredients_preselected'])?intval($this->data['ingredients_preselected']):0;

                $model->points_earned = isset($this->data['points_earned'])?floatval($this->data['points_earned']):0;
                $model->packaging_fee = isset($this->data['packaging_fee'])?floatval($this->data['packaging_fee']):0;

                $model->cooking_selected  = isset($this->data['cooking_selected'])?($this->data['cooking_selected']):array();
                $model->ingredients_selected  = isset($this->data['ingredients_selected'])?($this->data['ingredients_selected']):array();
                $model->dish_selected  = isset($this->data['dish_selected'])?($this->data['dish_selected']):array();
                $model->delivery_options_selected  = isset($this->data['delivery_options_selected'])?($this->data['delivery_options_selected']):array();
                $model->allergens_selected  = isset($this->data['allergens_selected'])?($this->data['allergens_selected']):array();                

                if($model->save()){
                    $this->code  = 1;
                    $this->msg = t(Helper_update);
                } else $this->msg = CommonUtility::parseError($model->getErrors());

            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actionsaveAvailability()
    {
        try {
            
            $merchant_id = (integer) Yii::app()->merchant->merchant_id;
            $item_uuid = isset($this->data['item_uuid'])?trim($this->data['item_uuid']):'';
            $available_days = isset($this->data['available_days'])?$this->data['available_days']:'';            
            $available_day = []; $available_time_start = []; $available_time_end = [];

            if(is_array($available_days) && count($available_days)>=1){
                foreach ($available_days as $items) {
                    $available_day[$items['value']] = $items['checked']==1?1:0;
                    $available_time_start[$items['value']] = isset($items['start'])?$items['start']:'';
                    $available_time_end[$items['value']] = isset($items['end'])?$items['end']:'';
                }
            }
            
            $model = AR_item_attributes::model()->find('merchant_id=:merchant_id AND item_token=:item_token',
		    array(':merchant_id'=>$merchant_id, ':item_token'=>$item_uuid ));
            if($model){
                $model->scenario = 'availability';
                $model->available = isset($this->data['available'])?intval($this->data['available']):0;
                $model->not_for_sale = isset($this->data['not_for_sale'])?intval($this->data['not_for_sale']):0;
                $model->available_at_specific = isset($this->data['available_at_specific'])?intval($this->data['available_at_specific']):0;
                
                $model->available_day = $available_day;
				$model->available_time_start = $available_time_start;
				$model->available_time_end = $available_time_end;

                if($model->save()){
                    $this->code  = 1;
                    $this->msg = t(Helper_update);
                } else $this->msg = CommonUtility::parseError($model->getErrors());
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);
        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actionsaveInventory()
    {
        try {

            $merchant_id = (integer) Yii::app()->merchant->merchant_id;
            $item_uuid = isset($this->data['item_uuid'])?trim($this->data['item_uuid']):'';

            $model = AR_item_attributes::model()->find('merchant_id=:merchant_id AND item_token=:item_token',
		    array(':merchant_id'=>$merchant_id, ':item_token'=>$item_uuid ));
            if($model){
                $model->scenario = 'item_inventory';
                $model->track_stock = isset($this->data['track_stock'])?intval($this->data['track_stock']):0;
                $model->sku = isset($this->data['sku'])?trim($this->data['sku']):'';
                $model->supplier_id = isset($this->data['supplier_id'])?intval($this->data['supplier_id']):0;
                if($model->save()){
                    $this->code  = 1;
                    $this->msg = t(Helper_update);
                } else $this->msg = CommonUtility::parseError($model->getErrors());
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);
        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actiongetSupplier()
    {
        try {
            $merchant_id = (integer) Yii::app()->merchant->merchant_id;
            $this->code = 1; $this->msg = "Ok";
            $this->details = [
                'data'=>AttributesTools::Supplier($merchant_id)
            ];
        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actiongetSalePromotion()
    {
        try {
            $merchant_id = (integer) Yii::app()->merchant->merchant_id;
            $item_uuid = Yii::app()->input->post('id');

            $model = AR_item::model()->find('merchant_id=:merchant_id AND item_token=:item_token',
		    array(':merchant_id'=>$merchant_id, ':item_token'=>$item_uuid ));

            if($model){
                $data = CDataFeed::getSalePromotion($merchant_id,$model->item_id);
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'data'=>$data
                ];
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actiondeletePromotion()
    {
        try {

            $merchant_id = (integer) Yii::app()->merchant->merchant_id;
            $item_uuid = Yii::app()->input->post('item_uuid');
            $promo_id = Yii::app()->input->post('promo_id');

            $model = AR_item::model()->find('merchant_id=:merchant_id AND item_token=:item_token',
		    array(':merchant_id'=>$merchant_id, ':item_token'=>$item_uuid ));
            if($model){
                $model = AR_item_promo::model()->find("merchant_id=:merchant_id AND promo_id=:promo_id",[
                    ":merchant_id"=>intval($merchant_id),
                    ':promo_id'=>intval($promo_id)
                ]);
                if($model){
                    $model->delete();
                    $this->code = 1;
                    $this->msg = "Ok";
                } else $this->msg = t(HELPER_RECORD_NOT_FOUND);
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actioncreatePromotion()
    {
        try {

            $merchant_id = (integer) Yii::app()->merchant->merchant_id;
            $promo_id = isset($this->data['promo_id'])?intval($this->data['promo_id']):0;
            $item_uuid = isset($this->data['item_uuid'])?intval($this->data['item_uuid']):'';
            $model = new AR_item_promo;
            if($promo_id>0){
                $model = AR_item_promo::model()->find("merchant_id=:merchant_id AND promo_id=:promo_id",[
                    ':merchant_id'=>$merchant_id,
                    ':promo_id'=>$promo_id
                ]);
            }

            $item = AR_item_attributes::model()->find('merchant_id=:merchant_id AND item_token=:item_token',
		    array(':merchant_id'=>$merchant_id, ':item_token'=>$item_uuid ));

            if($item){
                $model->merchant_id = $merchant_id;
                $model->item_id = $item->item_id;
                $model->promo_type = isset($this->data['promo_type'])?$this->data['promo_type']:'';
                $model->buy_qty = isset($this->data['buy_qty'])?intval($this->data['buy_qty']):0;
                $model->get_qty = isset($this->data['get_qty'])?intval($this->data['get_qty']):0;
                $model->item_id_promo = isset($this->data['item_id_promo'])?intval($this->data['item_id_promo']):0;
                $model->discount_start = isset($this->data['discount_start'])?$this->data['discount_start']:'';
                $model->discount_end = isset($this->data['discount_end'])?$this->data['discount_end']:'';

                if($model->save()){
                    $this->code = 1;
                    $this->msg =  $promo_id>0?t("Item Promo updated"):t("Item Promo succesfully added");
                } else $this->msg = CommonUtility::parseError($model->getErrors());
           } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actiongetItemList()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $page = Yii::app()->input->post('page');
            $length = 10;

            $criteria=new CDbCriteria();
            $criteria->condition = "merchant_id=:merchant_id AND status=:status";
            $criteria->params  = array(
            ':merchant_id'=>intval($merchant_id),
            ':status'=>"publish"
            );
            $criteria->order = "item_name ASC";
            $count = AR_item::model()->count($criteria);
            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);
            $page_count = $pages->getPageCount();
            $models = AR_item::model()->findAll($criteria);
            if($models){
                $data = [];
                foreach ($models as $items) {
                    $data[]=[
                        'item_id'=>$items->item_id,
                        'item_name'=>$items->item_name,
                    ];
                    $data_value[]=[
                        'value'=>$items->item_id,
                        'label'=>$items->item_name,
                    ];
                }
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'count'=>$page_count,
                    'data'=>$data,
                    'data_value'=>$data_value
                ];
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actiongetPromotion()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $promo_id = Yii::app()->input->post('promo_id');

            $model = AR_item_promo::model()->find("merchant_id=:merchant_id AND promo_id=:promo_id",[
                ':merchant_id'=>$merchant_id,
                ':promo_id'=>intval($promo_id)
            ]);
            if($model){
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'promo_type'=>$model->promo_type,
                    'buy_qty'=>$model->buy_qty,
                    'get_qty'=>$model->get_qty,
                    'item_id_promo'=>$model->item_id_promo,
                    'discount_start'=>$model->discount_start,
                    'discount_end'=>$model->discount_end,
                ];
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actiongetItemGallery()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $item_uuid = Yii::app()->input->post('id');

            $item = AR_item::model()->find('merchant_id=:merchant_id AND item_token=:item_token',
		    array(':merchant_id'=>$merchant_id, ':item_token'=>$item_uuid ));

            if($item){
                $model = AR_item_meta::model()->findAll("merchant_id=:merchant_id AND item_id=:item_id AND meta_name=:meta_name",[
                    ':merchant_id'=>$merchant_id,
                    ':item_id'=>$item->item_id,
                    ':meta_name'=>"item_gallery"
                ]);
                if($model){
                    $data = [];
                    foreach ($model as $items) {
                        $data[] = [
                            'id'=>$items->id,
                            'image_url'=>CMedia::getImage($items->meta_id,$items->meta_value,Yii::app()->params->size_image,CommonUtility::getPlaceholderPhoto('item')),
                        ];
                    }
                    $this->code = 1;
                    $this->msg = "Ok";
                    $this->details = $data;
                } else $this->msg = t(HELPER_RECORD_NOT_FOUND);
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actiondeleteItemGallery()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $id = Yii::app()->input->post('id');
            $model = AR_item_meta::model()->find("merchant_id=:merchant_id AND id=:id AND meta_name=:meta_name",[
                ":merchant_id"=>intval($merchant_id),
                ':id'=>intval($id),
                ':meta_name'=>"item_gallery"
            ]);
            if($model){
                $model->delete();
                $this->code = 1;
                $this->msg = "Ok";
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actioncreateGallery()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $item_uuid = isset($this->data['item_uuid'])?$this->data['item_uuid']:'';

            $item = AR_item::model()->find('merchant_id=:merchant_id AND item_token=:item_token',
		    array(':merchant_id'=>$merchant_id, ':item_token'=>$item_uuid ));

            if($item){
                $model = new AR_item_meta;
                $model->merchant_id = intval($merchant_id);
                $model->item_id = intval($item->item_id);
                $model->meta_name = "item_gallery";
                $model->meta_id = isset($this->data['featured_filename'])?$this->data['featured_filename']:'';
                $model->meta_value = isset($this->data['upload_path'])?$this->data['upload_path']:'';

                $file_data = isset($this->data['file_data'])?$this->data['file_data']:'';
                $image_type = isset($this->data['image_type'])?$this->data['image_type']:'png';
                if(!empty($file_data)){
                    $result = [];
                    try {
                        $result = CImageUploader::saveBase64Image($file_data,$image_type,"upload/".Yii::app()->merchant->merchant_id);
                        $model->meta_id = isset($result['filename'])?$result['filename']:'';
                        $model->meta_value = isset($result['path'])?$result['path']:'';
                    } catch (Exception $e) {
                        $this->msg = t($e->getMessage());
                        $this->responseJson();
                    }
                }

                if($model->save()){
                    $this->code = 1;
                    $this->msg =  t("Image added");
                } else $this->msg = CommonUtility::parseError($model->getErrors());
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);
        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actionupdateItemSeo()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $item_uuid = isset($this->data['item_uuid'])?$this->data['item_uuid']:'';

            $model = AR_item_seo::model()->find('merchant_id=:merchant_id AND item_token=:item_token',
		    array(':merchant_id'=>$merchant_id, ':item_token'=>$item_uuid ));

            if($model){
                $model->meta_title = isset($this->data['meta_title'])?$this->data['meta_title']:'';
                $model->meta_description = isset($this->data['meta_description'])?$this->data['meta_description']:'';
                $model->meta_keywords = isset($this->data['meta_keywords'])?$this->data['meta_keywords']:'';
                $model->meta_image = isset($this->data['featured_filename'])?$this->data['featured_filename']:'';
                $model->meta_image_path = isset($this->data['upload_path'])?$this->data['upload_path']:'';

                $file_data = isset($this->data['file_data'])?$this->data['file_data']:'';
                $image_type = isset($this->data['image_type'])?$this->data['image_type']:'png'; 
                if(!empty($file_data)){
                    $result = [];
                    try {
                        $result = CImageUploader::saveBase64Image($file_data,$image_type,"upload/".Yii::app()->merchant->merchant_id);
                        $model->meta_image = isset($result['filename'])?$result['filename']:'';
                        $model->meta_image_path = isset($result['path'])?$result['path']:'';
                    } catch (Exception $e) {
                        $this->msg = t($e->getMessage());
                        $this->responseJson();
                    }
                }

                if($model->save()){
                    $this->code = 1;
                    $this->msg =  t(Helper_update);
                } else $this->msg = CommonUtility::parseError($model->getErrors());

            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actiongetItemSeo()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $item_uuid = Yii::app()->input->post('item_uuid');

            $model = AR_item::model()->find('merchant_id=:merchant_id AND item_token=:item_token',
		    array(':merchant_id'=>$merchant_id, ':item_token'=>$item_uuid ));

            if($model){
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'meta_title'=>$model->meta_title,
                    'meta_description'=>$model->meta_description,
                    'meta_keywords'=>$model->meta_keywords,
                    'meta_image'=>$model->meta_image,
                    'meta_image_path'=>$model->meta_image_path,
                    'url_image'=>CMedia::getImage($model->meta_image,$model->meta_image_path,Yii::app()->params->size_image,CommonUtility::getPlaceholderPhoto('item')),
                ];
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actionsetItemAvailable()
    {
        try {

            $item_uuid = Yii::app()->input->post('item_uuid');
            $active = Yii::app()->input->post('active');
            $merchant_id = (integer) Yii::app()->merchant->merchant_id;

            $model = AR_item::model()->find('merchant_id=:merchant_id AND item_token=:item_token',
		    array(':merchant_id'=>$merchant_id, ':item_token'=>$item_uuid ));

            if($model){
                $model->scenario = 'setactive';
                $model->available=$active=="true"?1:0;
                if($model->save()){
                    $this->code = 1;
                    $this->msg = "Ok";
                } else $this->msg = CommonUtility::parseError( $model->getErrors() );
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

	public function actiongetReview()
	{
		try {

			$limit = Yii::app()->params->list_limit;
			$page = intval(Yii::app()->input->post('page'));
			$page_raw = intval(Yii::app()->input->post('page'));
			if($page>0){
				$page = $page-1;
			}

			$merchant_id = Yii::app()->merchant->merchant_id;

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
		   dump($this->msg);
		}
		$this->responseJson();
	}

	public function actiongetNotification()
	{
		try {

			$limit = 20;
            $merchant_id = intval(Yii::app()->merchant->merchant_id);

			$page = intval(Yii::app()->input->post('page'));
			$page_raw = intval(Yii::app()->input->post('page'));
			if($page>0){
				$page = $page-1;
			}

			$criteria=new CDbCriteria();
			$criteria->condition = "notication_channel=:notication_channel";
			$criteria->params  = array(
			':notication_channel'=>Yii::app()->merchant->merchant_uuid
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

    public function actiondeleteAllNotification()
	{
		try {

			$notification_uuids = isset($this->data['notification_uuids'])?$this->data['notification_uuids']:'';
			CNotifications::deleteNotifications(Yii::app()->merchant->merchant_uuid,$notification_uuids);
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

			CNotifications::deleteByChannel(Yii::app()->merchant->merchant_uuid);
			$this->code = 1;
			$this->msg = "Ok";

		} catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
	}

    public function actiongetEarningSummary()
    {
        try {

            $card_id = 0;
            $merchant_id = intval(Yii::app()->merchant->merchant_id);

            $sales_week = CReports::SalesThisWeek($merchant_id);

            try {
			    $card_id = CWallet::getCardID( Yii::app()->params->account_type['merchant'] , $merchant_id );
				$balance = CWallet::getBalance($card_id);
		    } catch (Exception $e) {
			   $balance = 0;
		    }

            $earning_week = CReports::EarningThisWeek($card_id);

            $data = [];
            $data['sales_week'] = Price_Formatter::formatNumber($sales_week);
            $data['earning_week'] = Price_Formatter::formatNumber($earning_week);
            $data['balance'] = Price_Formatter::formatNumber($balance);

            $sales[] = [
                'label'=>t("Sales this week"),
                'value'=>Price_Formatter::formatNumber($sales_week),
                'color'=>'#49c3a1'
            ];
            $sales[] = [
                'label'=>t("Earning this week"),
                'value'=>Price_Formatter::formatNumber($earning_week),
                'color'=>'#9689e7'
            ];
            $sales[] = [
                'label'=>t("Your balance"),
                'value'=>Price_Formatter::formatNumber($balance),
                'color'=>'#fab54d'
            ];
            $this->code = 1;
            $this->msg = "Ok";
            $this->details = [
                'data'=>$data,
                'sales'=>$sales
            ];

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actiongetTotalOrders()
    {
        try {

            $merchant_id = Yii::app()->merchant->merchant_id;
	    	$initial_status = AttributesTools::initialStatus();
	    	$refund_status = AttributesTools::refundStatus();
	    	$orders = 0; $order_cancel = 0; $total=0;

	    	$not_in_status = AOrderSettings::getStatus(array('status_cancel_order','status_rejection'));
	    	array_push($not_in_status,$initial_status);
	    	$orders = AOrders::getOrdersTotal($merchant_id,array(),$not_in_status);

	    	$status_cancel = AOrderSettings::getStatus(array('status_cancel_order'));
		    $order_cancel = AOrders::getOrdersTotal($merchant_id,$status_cancel);

		    $status_delivered = AOrderSettings::getStatus(array('status_delivered','status_completed'));

		    $total = AOrders::getOrderSummary($merchant_id,$status_delivered);
		    $total_refund = AOrders::getTotalRefund($merchant_id,$refund_status);

            $data = [];
            $data[] = [
                'label'=>t("Total Orders"),
                'value'=>Price_Formatter::formatNumber($orders),
                'color'=>'#c3b5d3'
            ];
            $data[] = [
                'label'=>t("Total Cancel"),
                'value'=>Price_Formatter::formatNumber($order_cancel),
                'color'=>'#e99a9e'
            ];
            $data[] = [
                'label'=>t("Total refund"),
                'value'=>Price_Formatter::formatNumber($total_refund),
                'color'=>'#45adc9'
            ];
            $data[] = [
                'label'=>t("Total Sales"),
                'value'=>Price_Formatter::formatNumber($total),
                'color'=>'#ffbf49'
            ];

		    $this->code = 1;
			$this->msg = "OK";
			$this->details = $data;

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actionOrderList()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);

            $filter_by = Yii::app()->input->post('filter_by');
            $limit = intval(Yii::app()->input->post('limit'));
            $page = intval(Yii::app()->input->post('page'));
            $q = trim(Yii::app()->input->post('q'));
            $request_from = trim(Yii::app()->input->post('request_from'));            

            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
				$page = $page-1;
			}

            $settings = OptionsTools::find(array('merchant_order_critical_mins'),$merchant_id);
    		$critical_mins = isset($settings['merchant_order_critical_mins'])?$settings['merchant_order_critical_mins']:0;
    		$critical_mins = intval($critical_mins);

    		$data = array(); $order_status = array(); $datetime=date("Y-m-d g:i:s a");

    		if($filter_by!="all"){
	    		$order_status = AOrders::getOrderTabsStatus($filter_by);                
                if(!$order_status && $filter_by=='scheduled'){
                    $order_status = AOrders::getOrderTabsStatus('new_order');
                }
    		}            

    		$status = COrders::statusList(Yii::app()->language);            
            $payment_status = COrders::paymentStatusList2(Yii::app()->language,'payment');

            $status_in = AOrders::getOrderTabsStatus('new_order');

            $payment_list = AttributesTools::PaymentProvider();

    		$criteria=new CDbCriteria();
		    $criteria->alias = "a";
		    $criteria->select = "a.order_id, a.order_uuid, a.client_id, a.status, a.order_uuid ,
		    a.payment_code, a.service_code,a.total, a.delivery_date, a.delivery_time, a.date_created, a.payment_code, a.total,
		    a.payment_status, a.is_view, a.is_critical, a.whento_deliver,
		    b.meta_value as customer_name,

		    IF(a.whento_deliver='now',
		      TIMESTAMPDIFF(MINUTE, a.date_created, NOW())
		    ,
		     TIMESTAMPDIFF(MINUTE, concat(a.delivery_date,' ',a.delivery_time), NOW())
		    ) as min_diff

		    ,
		    (
		       select sum(qty)
		       from {{ordernew_item}}
		       where order_id = a.order_id
		    ) as total_items,

            (
                select GROUP_CONCAT(cat_id,';',item_id,';',item_size_id,';',price,';',discount,';',qty)
                from {{ordernew_item}}
                where order_id = a.order_id
            ) as items
		    ";
		    $criteria->join='LEFT JOIN {{ordernew_meta}} b on  a.order_id=b.order_id ';
		    $criteria->condition = "a.merchant_id=:merchant_id AND meta_name=:meta_name ";
		    $criteria->params  = array(
		      ':merchant_id'=>intval($merchant_id),
		      ':meta_name'=>'customer_name'
		    );

		    if(is_array($order_status) && count($order_status)>=1){
		    	$criteria->addInCondition('status',(array) $order_status );
		    } else {
		    	$draft = AttributesTools::initialStatus();
		    	$criteria->addNotInCondition('status', array($draft) );
            }
            
            if(!empty($q)){
                $criteria->addSearchCondition('a.order_id', $q );		        
            }

            if(!empty($request_from)){
                $criteria->addSearchCondition('a.request_from', $request_from );		        
            }

            switch ($filter_by) {
                case 'new_order':         
                    $criteria->addInCondition('a.whento_deliver',['now']);
                    break;            
                case "scheduled":     
                    $criteria->addInCondition('a.whento_deliver',['schedule']);
                    break;                       
            }

            $criteria->order = "date_created DESC";
            
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
					$this->responseJson();
				}
			}

		    $models = AR_ordernew::model()->findAll($criteria);

		    PrettyDateTime::$category='backend';

		    if($models){
		    	foreach ($models as $item) {

                    $items = array();
                    $items_row = explode(",",$item->items);
                    if(is_array($items_row) && count($items_row)>=1){
                        foreach ($items_row as $item_val) {
                            $itemd = explode(";",$item_val);
                            if(count($itemd)>1){
                            $items[] = array(
                              'cat_id'=>$itemd['0'],
                              'item_id'=>$itemd['1'],
                              'item_size_id'=>$itemd['2'],
                              'price'=>isset($itemd['3'])?$itemd['3']:0,
                              'discount'=>isset($itemd['4'])?$itemd['4']:0,
                              'qty'=>isset($itemd['5'])?$itemd['5']:0,
                            );
                            $all_items[]=$itemd['1'];
                            $all_item_size[]=$itemd['2'];
                            }
                        }
                    }

		    		$status_trans = $item->status;
		            if(array_key_exists($item->status, (array) $status)){
		               $status_trans = $status[$item->status]['status'];
		            }
		            
			        $payment_status_name = $item->payment_status;
			        if(array_key_exists($item->payment_status,(array)$payment_status)){
			            $payment_status_name = $payment_status[$item->payment_status]['title'];
			        }

			        if(array_key_exists($item->payment_code,(array)$payment_list)){
			            $item->payment_code = $payment_list[$item->payment_code];
			        }

			        $is_critical =  0;

			        if($item->whento_deliver=="schedule"){
			        	if($item->min_diff>0 && in_array($item->status,(array)$status_in) ){
			        		$is_critical = true;
			        	}
			        } else if ($critical_mins>0 && $item->min_diff>$critical_mins && in_array($item->status,(array)$status_in) ) {
			        	$is_critical = true;
			        }

		    		$data[]=array(
		    		  'order_id_raw'=>$item->order_id,
		    		  'order_id'=>t("Order #{{order_id}}",array('{{order_id}}'=>$item->order_id)),
                      'order_uuid'=>$item->order_uuid,
                      'total'=>Price_Formatter::formatNumber($item->total),
                      'date_created'=>PrettyDateTime::parse(new DateTime($item->date_created)),
                      'description'=>t("{count} Items for {first_name}",[
                        '{count}'=>$item->total_items,
                        '{first_name}'=>$item->customer_name
                      ]),
                      'customer_name'=>$item->customer_name,
                      'total_items'=>$item->total_items,
                      'status'=>ucwords($status_trans),
                      'status_raw'=>$item->status,
                      'order_type'=>$item->service_code,
                      'is_view'=>$item->is_view,
                      'is_critical'=>$is_critical,
                      'payment_name'=>$item->payment_code,
                      'payment_status'=>$item->payment_status,
                      'payment_status_name'=>$payment_status_name,
                      'items'=>$items
		    		);
		    	}

                $item_details = COrders::orderItems2($all_items,Yii::app()->language);                
                $settings_tabs = COrders::OrderSettingTabs();
                $order_group_buttons = COrders::OrderGroupButtons();
                $order_buttons = COrders::OrderButtons(Yii::app()->language);
                $services_list = CServices::Listing( Yii::app()->language );                

		    	$this->code = 1; $this->msg = "ok";
		    	$this->details = [
                    'page_count'=>$page_count,
                    'page'=>$page,
                    'data'=>$data,
                    'status_list'=>COrders::statusList(),
                    'item_details'=>$item_details,
                    'settings_tabs'=>$settings_tabs,
                    'order_group_buttons'=>$order_group_buttons,
                    'order_buttons'=>$order_buttons,
                    'services_list'=>$services_list
                ];

		    } else {
		    	$this->msg = t("You don't have current orders.");
		    	$this->details = array(
		    	  'image_url'=>CMedia::themeAbsoluteUrl()."/assets/images/order-best-food@2x.png"
		    	);
		    }

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actiongetTopCustomer()
    {
        try {

            $data = array();
			$merchant_id = Yii::app()->merchant->merchant_id;
			$limit = Yii::app()->input->post('limit');
			$not_in_status = AOrderSettings::getStatus(array('status_cancel_order','status_rejection'));

			$criteria=new CDbCriteria();
			$criteria->alias = "a";
			$criteria->select="a.client_id, count(*) as total_sold,
			b.first_name,b.last_name,b.date_created, b.avatar as logo, b.path
			";
			$criteria->join='LEFT JOIN {{client}} b on  a.client_id=b.client_id ';
			$criteria->condition = "a.merchant_id=:merchant_id and b.client_id IS NOT NULL";
			$criteria->params = array(':merchant_id'=>$merchant_id);

			if(is_array($not_in_status) && count($not_in_status)>=1){
			   $criteria->addNotInCondition('a.status', (array) $not_in_status );
		    }

			$criteria->group="a.client_id";
			$criteria->order = "count(*) DESC";
			$criteria->limit = intval($limit);

		    $model = AR_ordernew::model()->findAll($criteria);
		    if($model){
		    	foreach ($model as $item) {
		    		$total_sold = number_format($item->total_sold,0,'',',');
		    		$data[] = array(
		    		  'client_id'=>$item->client_id,
		    		  'first_name'=>$item->first_name,
		    		  'last_name'=>$item->last_name,
		    		  'total_sold'=>t("{{total_sold}} orders", array('{{total_sold}}'=>$total_sold) ),
		    		  'member_since'=> t("Member since {{date_created}}" , array('{{date_created}}'=>Date_Formatter::dateTime($item->date_created)) ),
		    		  'image_url'=>CMedia::getImage($item->logo,$item->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('customer')),
		    		);
		    	}
		    	$this->code = 1; $this->msg = "ok";
		        $this->details = $data;
		    } else $this->msg = t("You don't have customer yet");

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actionsalesOverview()
    {
    	try {

    		$data = array();
    		$merchant_id = Yii::app()->merchant->merchant_id;
    		$months = intval(Yii::app()->input->post('months'));

    		$status_completed = AOrderSettings::getStatus(array('status_delivered','status_completed'));
    		$date_start = date("Y-m-d", strtotime(date("c")." -$months months"));
    		$date_end = date("Y-m-d");

    		$criteria=new CDbCriteria();
    		$criteria->select = "
    		DATE_FORMAT(date_created, '%b') AS month , SUM(total) as monthly_sales
    		";
    		$criteria->group="DATE_FORMAT(date_created, '%b')";
			$criteria->order = "date_created DESC";

			$criteria->condition = "merchant_id=:merchant_id";
			$criteria->params = array(':merchant_id'=>$merchant_id);

			if(is_array($status_completed) && count($status_completed)>=1){
			   $criteria->addInCondition('status', (array) $status_completed );
		    }
		    if(!empty($date_start) && !empty($date_end)){
				$criteria->addBetweenCondition("DATE_FORMAT(date_created,'%Y-%m-%d')", $date_start , $date_end );
			}

    		$model = AR_ordernew::model()->findAll($criteria);
    		if($model){
    			$category = array(); $sales = array();
    			foreach ($model as $item) {
    				$category[] = t($item->month);
    				$sales[] = floatval($item->monthly_sales);
    			}

    			$data = array(
    			  'category'=>$category,
    			  'data'=>$sales
    			);

    			$this->code = 1; $this->msg = "ok";
		        $this->details = $data;

    		} else {
    			$this->msg = t("You don't have sales yet");
    			$this->details = array(
		    	  'image_url'=>CMedia::themeAbsoluteUrl()."/assets/images/no-results2.png"
		    	);
    		}

    	} catch (Exception $e) {
		   $this->msg = t($e->getMessage());
		}
		$this->responseJson();
    }

    public function actionOverviewReview()
    {
    	try {

    	    $data = array(); $total = 0;
    		$merchant_id = Yii::app()->merchant->merchant_id;

    		$total = CReviews::reviewsCount($merchant_id);
    		$start = date('Y-m-01'); $end = date("Y-m-d");
    		$this_month = CReviews::totalCountByRange($merchant_id,$start,$end);
    		$user = CReviews::userAddedReview($merchant_id,4);
    		$review_summary = CReviews::summaryCount($merchant_id,$total);

    		$data = array(
    		  'total'=>$total,
    		  'this_month'=>$this_month,
    		  'this_month_words'=>t("This month you got {{count}} New Reviews",array('{{count}}'=>$this_month)),
    		  'user'=>$user,
    		  'review_summary'=>$review_summary,
    		);

    		$this->code = 1; $this->msg = "ok";
		    $this->details = $data;

    	} catch (Exception $e) {
		   $this->msg = t($e->getMessage());
		}
		$this->responseJson();
    }

    public function actionupdateOrderStatus()
    {
        try {

            $uuid = isset($this->data['uuid'])?$this->data['uuid']:'';
			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';
			$rejetion_reason = isset($this->data['reason'])?$this->data['reason']:'';

            $status = AOrders::getOrderButtonStatus($uuid);
			$do_actions = AOrders::getOrderButtonActions($uuid);

            $model = COrders::get($order_uuid);

			if($do_actions=="reject_form"){
				$model->scenario = "reject_order";
			} else $model->scenario = "change_status";

			if($model->status==$status){
				$this->msg = t("Order has the same status");
				$this->responseJson();
			}

            $model->status = $status;
			$model->remarks = $rejetion_reason;
			$model->change_by = Yii::app()->merchant->first_name;
			if($model->save()){
			   $this->code = 1;
			   $this->msg = t("Status Updated");

			   if(!empty($rejetion_reason)){
			   	  COrders::savedMeta($model->order_id,'rejetion_reason',$rejetion_reason);
			   }

			} else $this->msg = CommonUtility::parseError( $model->getErrors());
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actiongetOrderSummaryChanges()
    {
        try {

            $order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';
            $group_name = isset($this->data['group_name'])?$this->data['group_name']:'';

            COrders::getContent($order_uuid,Yii::app()->language);
            $merchant_id = COrders::getMerchantId($order_uuid);
            $merchant_info = COrders::getMerchant($merchant_id,Yii::app()->language);
            $summary_changes = array();
            $summary_changes = COrders::getSummaryChanges();
            $order = COrders::orderInfo(Yii::app()->language, date("Y-m-d") );
            $payment_code = isset($order['order_info'])?$order['order_info']['payment_code']:'';

            $commission = floatval($order['order_info']['commission']);

            // $all_offline = CPayments::getPaymentTypeOnline(0);
            // if(!$summary_changes && $group_name=="new_order" && array_key_exists($payment_code,(array)$all_offline) && $merchant_info['merchant_type']==2 ){
		    // 	$summary_changes = array(
		    // 	  'method'=>'less_on_account'
		    // 	);
            // }

            /*CHECK IF ORDER IS NEW AND OFFLINE PAYMENT AND MERCHANT IS COMMISSION*/		    
		    $all_offline = CPayments::getPaymentTypeOnline(0);
		    if(!$summary_changes && $group_name=="new_order" && array_key_exists($payment_code,(array)$all_offline) && $merchant_info['merchant_type']==2 ){
		    	$summary_changes = array(
		    	  'method'=>'less_on_account'				  
		    	);				
				$allowed_offline_payment = isset($merchant_info['allowed_offline_payment'])?$merchant_info['allowed_offline_payment']:0;
				if($allowed_offline_payment==1){
					$summary_changes = array(						
						'method'=>''
				    );				
				}
            }

            $this->code = 1;
            $this->msg = "Ok";
            $this->details = [
                'summary_changes'=>$summary_changes,
                'commission'=> t("By accepting this order we will less the commission total {commission} to your account.",[
                    '{commission}'=>Price_Formatter::formatNumber($commission)
                ])
            ];
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actionlessCashOnAccount()
    {
        try {

            $uuid = isset($this->data['uuid'])?$this->data['uuid']:'';
			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';

			$order = COrders::get($order_uuid);
			$status = AOrders::getOrderButtonStatus($uuid);
			$order->scenario = "change_status";

			$amount = floatval($order->commission);

            $card_id = CWallet::getCardID( Yii::app()->params->account_type['merchant'] , $order->merchant_id );
			$balance = CWallet::getBalance($card_id);
			$balance = Price_Formatter::convertToRaw($balance);


			if($balance<$amount){
				$this->msg = t("You don't have enough balance in your account. please load your account to process this order.");
			   	$this->responseJson();
            }

            $params = array(
			  'merchant_id'=>$order->merchant_id,
			  'transaction_description'=>"Payment to order #{{order_id}}",
			  'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id),
			  'transaction_type'=>"debit",
			  'transaction_amount'=>floatval($amount),
			  'meta_name'=>"order",
			  'meta_value'=>$order->order_id,
			  'status'=>"paid"
			);
			CWallet::inserTransactions($card_id,$params);

			$order->status = $status;
			$order->save();

			$this->code = 1; $this->msg = "OK";

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actionsetOrderViewed()
    {
        try {

            $order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';
            $model = COrders::get($order_uuid);

            if($model->is_view==1){
                $this->code = 1;
                $this->msg = "Already viewed";
                $this->responseJson();
            }

            $model->is_view = 1;
            if($model->save()){
                $this->code = 1;
                $this->msg = "Ok";
            } else $this->msg = CommonUtility::parseError($model->getErrors());
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actionorderDetails()
    {
        try {

             $hide_currency = isset($this->data['hide_currency'])?$this->data['hide_currency']:false;             
             //remove currency fixed for printing             
             if($hide_currency==1){                
                Price_Formatter::$number_format['currency_symbol'] = '';
             }             
             $refund_transaction = array(); $order_id = 0;
			 $summary = array(); $progress = array(); $order_status = array();
			 $allowed_to_cancel = false;
			 $pdf_link = ''; $delivery_timeline=array();
			 $order_delivery_status = array(); $merchant_info=array();
			 $order = array(); $items = array(); $order_type = '';

			 $label = array(
				'summary'=>t("Summary"),
			 );

		     $order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';
			 $payload = isset($this->data['payload'])?$this->data['payload']:array();

		     COrders::getContent($order_uuid,Yii::app()->language);
		     $merchant_id = COrders::getMerchantId($order_uuid);
			 $order_id = COrders::getOrderID();

			 if(in_array('merchant_info',$payload)){
				$merchant_info = COrders::getMerchant($merchant_id,Yii::app()->language);
                $tax_data = OptionsTools::find(['merchant_tax_number'],$merchant_id);
                $merchant_info['merchant_tax_number'] = isset($tax_data['merchant_tax_number'])?$tax_data['merchant_tax_number']:'';
			 }
			 if(in_array('items',$payload)){
		        $items = COrders::getItems();
			 }

			 if(in_array('summary',$payload)){
		        $summary = COrders::getSummary();
			 }

			 if(in_array('order_info',$payload)){
		        $order = COrders::orderInfo(Yii::app()->language);
                $order_type = isset($order['order_info'])?$order['order_info']['order_type']:'';
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
			    $order_delivery_status = AttributesTools::getOrderStatusMany(Yii::app()->language,['order_status','delivery_status']);
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

            $credit_card_details = '';
            $payment_code = $order['order_info']['payment_code'];
            if(in_array('credit_card',$payload) && $payment_code=="ocr" ){
                try {
                    $credit_card_details = COrders::getCreditCard($order_id);
                    $credit_card_details = JWT::encode($credit_card_details, CRON_KEY, 'HS256');
                } catch (Exception $e) {
                    //
                }
            }

			$charge_type = '';
			if(in_array('charge_type',$payload)){
				$options_data = OptionsTools::find(array('merchant_delivery_charges_type'),$merchant_id);
				$charge_type = isset($options_data['merchant_delivery_charges_type'])?$options_data['merchant_delivery_charges_type']:'';
			}

            $filter_buttons = false; $buttons = array(); $group_name='';
            try {
			    $group_name = AOrderSettings::getGroup($order['order_info']['status']);
				if($group_name=="order_ready"){
					$filter_buttons = true;
				}
                if($filter_buttons){
                    $buttons = AOrders::getOrderButtons($group_name,$order['order_info']['order_type']);
                } else $buttons = AOrders::getOrderButtons($group_name);
			} catch (Exception $e) {
		    	//
            }

            $maps_config = CMaps::config();
		    $maps_config_raw = $maps_config;
            $map_direction = CMerchantListingV1::mapDirection($maps_config_raw, $order['order_info']['longitude'] ,$order['order_info']['latitude']);

            $client_id = $order?$order['order_info']['client_id']:0;
            $customer = COrders::getClientInfo($client_id);				    
		    $count = COrders::getCustomerOrderCount($client_id,$merchant_id);
		    $customer['order_count'] = $count;

            $driver_data = []; $merchant_zone = []; $zone_list=[];

            if(in_array('driver',$payload)){
                $driver_id = $order?$order['order_info']['driver_id']:0;		
                if($driver_id>0){
                    $now = date("Y-m-d");
                    try {
                        $driver = CDriver::getDriver($driver_id);
                        $driver_data = [
                            'uuid'=>$driver->driver_uuid,
                            'driver_name'=>"$driver->first_name $driver->last_name",
                            'phone_number'=>"+".$driver->phone_prefix.$driver->phone,
                            'email_address'=>$driver->email,
                            'photo_url'=>CMedia::getImage($driver->photo,$driver->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('driver')),
                            'url'=>Yii::app()->createAbsoluteUrl("/merchantdriver/overview",['id'=>$driver->driver_uuid]),
                            'active_task'=>CDriver::getCountActiveTask($driver->driver_id,$now)
                        ];
                    } catch (Exception $e) {
                        //
                    }	
                }

                $merchant_zone = CMerchants::getListMerchantZone([$merchant_id]);
                if(!$zone_list = CommonUtility::getDataToDropDown("{{zones}}",'zone_id','zone_name')){
                    $zone_list = [];
                }

                $order_status = isset($order['order_info'])?$order['order_info']['status']:'';
                $order['order_info']['show_assign_driver'] = false;
                $order['order_info']['can_reassign_driver'] = true;
                if($order_type=="delivery"){
                    $status1 = COrders::getStatusTab2(['new_order','order_processing','order_ready']);
                    $status2 = AOrderSettings::getStatus(array('status_delivered','status_completed','status_delivery_fail','status_failed'));
                    $all_status = array_merge((array)$status1,(array)$status2);
                    if(in_array($order_status,(array)$all_status)){
                        $order['order_info']['show_assign_driver'] = true;
                    }
                    if(in_array($order_status,(array)$status2)){
                        $order['order_info']['can_reassign_driver'] = false;
                    }
                }
            }
            
            $order_table_data = [];
			if($order_type=="dinein"){
				$order_table_data = COrders::orderMeta(['table_id','room_id','guest_number']);	
				$room_id = isset($order_table_data['room_id'])?$order_table_data['room_id']:0;							
				$table_id = isset($order_table_data['table_id'])?$order_table_data['table_id']:0;							
				try {
					$table_info = CBooking::getTableByID($table_id);
					$order_table_data['table_name'] = $table_info->table_name;
				} catch (Exception $e) {
					$order_table_data['table_name'] = t("Unavailable");
				}				
				try {
					$room_info = CBooking::getRoomByID($room_id);					
					$order_table_data['room_name'] = $room_info->room_name;
				} catch (Exception $e) {
					$order_table_data['room_name'] = t("Unavailable");
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
               'group_name'=>$group_name,
               'buttons'=>$buttons,
               'map_direction'=>$map_direction,
               'credit_card_details'=>$credit_card_details,
               'customer'=>$customer,
               'driver_data'=>$driver_data,
               'merchant_zone'=>$merchant_zone,
               'zone_list'=>$zone_list,
               'order_table_data'=>$order_table_data
		     );

		     $this->code = 1; $this->msg = "ok";
		     $this->details = array(
		       'data'=>$data,
		     );

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actionsetDelayToOrder()
	{
		try {

			$time_delay = isset($this->data['time_delay'])?intval($this->data['time_delay']):'';
			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';
			$model = COrders::get($order_uuid);
			$model->scenario = "delay_order";

			$model->remarks = "Order is delayed by [mins]min(s)";
			$model->ramarks_trans = json_encode(array('[mins]'=>$time_delay));
			if($model->save()){
			   $this->code = 1;
			   $this->msg = t("Customer is notified about the delayed.");
			   COrders::savedMeta($model->order_id,'delayed_order', t($model->remarks,array('[mins]'=>$time_delay)) );
			   COrders::savedMeta($model->order_id,'delayed_order_mins',$time_delay );
			} else $this->msg = CommonUtility::parseError( $model->getErrors());
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

    public function actiongetMerchantDashboard()
    {
        try {

            $card_id = 0; $total_food_orders = 0; $total_payments = 0;
            $merchant_id = intval(Yii::app()->merchant->merchant_id);

            try {
			    $card_id = CWallet::getCardID( Yii::app()->params->account_type['merchant'] , $merchant_id );
				$balance = CWallet::getBalance($card_id);
		    } catch (Exception $e) {
			   $balance = 0;
		    }

            $order_status = AOrders::getOrderTabsStatus('completed_today');

            try {
                $total_food_orders = COrders::getFoodOrders($merchant_id,(array)$order_status);
            } catch (Exception $e) {
                $total_food_orders = 0;
            }

            try {
                $total_payments = CPayments::getTotalPayments($merchant_id);
            } catch (Exception $e) {
                $total_payments = 0;
            }

            try {
                $payout_account = CPayouts::getPayoutAccont($merchant_id);
            } catch (Exception $e) {
                $payout_account = '';
            }

            $this->code = 1;
            $this->msg = "Ok";
            $this->details = [
                'balance'=>Price_Formatter::formatNumber($balance),
                'balance_raw'=>$balance,
                'total_food_orders'=>Price_Formatter::convertToRaw($total_food_orders,0),
                'total_payments'=>CommonUtility::shortNumber($total_payments),
                'payout_account'=>$payout_account
            ];

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
    }

    public function actiontransactionHistory()
    {
        try {

            $data = array(); $card_id = 0;
            try {
                $card_id = CWallet::getCardID(Yii::app()->params->account_type['merchant'],Yii::app()->merchant->merchant_id);
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
            $criteria->params  = array(
            ':card_id'=>intval($card_id),
            );
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
                        'transaction_date'=>Date_Formatter::date($item->transaction_date),
                        'transaction_description'=>$description,
                        'transaction_amount'=>$transaction_amount,
                        'running_balance'=>Price_Formatter::formatNumber($item->running_balance),
                        'transaction_type'=>$item->transaction_type
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

    public function actionpayoutHistory()
    {
        try {

            $limit = Yii::app()->params->list_limit;
            $merchant_id = Yii::app()->merchant->merchant_id;
            $card_id = 0;

            try {
                $card_id = CWallet::getCardID( Yii::app()->params->account_type['merchant'] ,$merchant_id);
            } catch (Exception $e) {
                // do nothing
            }

            $page = intval(Yii::app()->input->post('page'));
			$page_raw = intval(Yii::app()->input->post('page'));
			if($page>0){
				$page = $page-1;
			}

            $criteria=new CDbCriteria();
			$criteria=new CDbCriteria();
            $criteria->condition = "card_id=:card_id  AND transaction_type=:transaction_type";
            $criteria->params  = array(
            ':card_id'=>intval($card_id),
            ':transaction_type'=>"payout"
            );
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

            $status_trans = AttributesTools::statusManagementTranslationList('payment', Yii::app()->language );

            if($model = AR_wallet_transactions::model()->findAll($criteria)){
                $data = [];
                foreach ($model as $item) {
                    $description = Yii::app()->input->xssClean($item->transaction_description);
                    $parameters = json_decode($item->transaction_description_parameters,true);
                    if(is_array($parameters) && count($parameters)>=1){
                        $description = t($description,$parameters);
                    }

                    $transaction_amount = Price_Formatter::formatNumber($item->transaction_amount);
                    if($item->transaction_type=="debit"){
                        $transaction_amount = "(".Price_Formatter::formatNumber($item->transaction_amount).")";
                    }

                    $trans_status = $item->status;
                    if(array_key_exists($item->status,(array)$status_trans)){
                        $trans_status = $status_trans[$item->status];
                    }

                    $data[]=array(
                        'transaction_amount'=>$transaction_amount,
                        'transaction_description'=>$description,
                        'transaction_date'=>Date_Formatter::date($item->transaction_date),
                        'status'=>$trans_status
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

    public function actionrequestPayout()
    {
    	try {

    	   $account = array();
    	   $merchant_id = Yii::app()->merchant->merchant_id;
    	   $amount = floatval(Yii::app()->input->post('amount'));

		   if(DEMO_MODE){
			   if($amount>10){
                  $this->msg[] = t("Maximum amount of payout in demo is 10");
				  $this->responseJson();
			   }
		   }

    	   $accounts = CPayouts::getPayoutAccont($merchant_id);
    	   $account = isset($accounts['account'])?$accounts['account']:'';

    	   $card_id = CWallet::getCardID( Yii::app()->params->account_type['merchant'] , $merchant_id);
    	   $transaction_id = CEarnings::requestPayout($card_id,$amount , $account , $accounts );

    	   $params = array();
    	   foreach ($accounts as $itemkey=>$item) {
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

    	   $this->code = 1; $this->msg = t("Payout request successfully logged");
    	   $this->details = $transaction_id;

    	} catch (Exception $e) {
		   $this->msg[] = t($e->getMessage());
	   }
	   $this->responseJson();
    }

    public function actionSetPayoutAccount()
    {
    	try {

    		$merchant_id = Yii::app()->merchant->merchant_id;
    		$meta_name = 'payout_provider';

	    	$payment_provider = isset($this->data['payment_provider'])?$this->data['payment_provider']:'';
	    	$email_address = isset($this->data['email_address'])?$this->data['email_address']:'';


	    	$model = AR_merchant_meta::model()->find("merchant_id=:merchant_id AND meta_name=:meta_name",array(
	    	  ':merchant_id'=>intval($merchant_id),
	    	  ':meta_name'=>$meta_name,
	    	));
	    	if(!$model){
	    	    $model = new AR_merchant_meta;
	    	}

	    	$model->merchant_id = intval($merchant_id);
	    	$model->meta_name = $meta_name;
	    	$model->meta_value = $payment_provider;

	    	switch ($payment_provider) {
	    		case "paypal":

                    if(!CommonUtility::checkEmail($email_address)){
                        $this->msg[] = t("Invalid email address");
	    		    	$this->responseJson();
                    }

	    		    if(!empty($email_address)){
	    			    $model->meta_value1 = $email_address;
	    		    } else {
	    		    	$this->msg[] = t("Invalid email address");
	    		    	$this->responseJson();
	    		    }
	    			break;

	    		case "stripe":
	    		   $account_number  = isset($this->data['account_number'])?$this->data['account_number']:'';
	    		   if(empty($account_number)){
	    		   	  $this->msg[] = t("Account number is required");
	    		   }
	    		   $account_holder_name  = isset($this->data['account_holder_name'])?$this->data['account_holder_name']:'';
	    		   if(empty($account_holder_name)){
	    		   	  $this->msg[] = t("Account name is required");
	    		   }

	    		   if(is_array($this->msg) && count($this->msg)>=1){
	    		   	  $this->responseJson();
	    		   }

	    		   $model->meta_value1  = json_encode(array(
	    		     'account_number'=>isset($this->data['account_number'])?$this->data['account_number']:'',
	    		     'account_holder_name'=>isset($this->data['account_holder_name'])?$this->data['account_holder_name']:'',
	    		     'account_holder_type'=>isset($this->data['account_holder_type'])?$this->data['account_holder_type']:'',
	    		     'currency'=>isset($this->data['currency'])?$this->data['currency']:'',
	    		     'routing_number'=>isset($this->data['routing_number'])?$this->data['routing_number']:'',
	    		     'country'=>isset($this->data['country'])?$this->data['country']:'',
	    		   ));
	    		   break;

	    		case "bank":
	    		   $account_number_iban  = isset($this->data['account_number_iban'])?$this->data['account_number_iban']:'';
	    		   if(empty($account_number_iban)){
	    		   	  $this->msg[] = t("Account number is required");
	    		   }
	    		   $account_name  = isset($this->data['account_name'])?$this->data['account_name']:'';
	    		   if(empty($account_name)){
	    		   	  $this->msg[] = t("Account name is required");
	    		   }

	    		   $bank_name  = isset($this->data['bank_name'])?$this->data['bank_name']:'';
	    		   if(empty($bank_name)){
	    		   	  $this->msg[] = t("Bank name is required");
	    		   }
	    		   $swift_code  = isset($this->data['swift_code'])?$this->data['swift_code']:'';
	    		   if(empty($swift_code)){
	    		   	  $this->msg[] = t("Swift code is required");
	    		   }
	    		   $country  = isset($this->data['country'])?$this->data['country']:'';
	    		   if(empty($swift_code)){
	    		   	  $this->msg[] = t("Country is required");
	    		   }

	    		   if(is_array($this->msg) && count($this->msg)>=1){
	    		   	  $this->responseJson();
	    		   }

	    		   $model->meta_value1  = json_encode(array(
	    		     'account_name'=>isset($this->data['account_name'])?$this->data['account_name']:'',
	    		     'account_number_iban'=>isset($this->data['account_number_iban'])?$this->data['account_number_iban']:'',
	    		     'swift_code'=>isset($this->data['swift_code'])?$this->data['swift_code']:'',
	    		     'bank_name'=>isset($this->data['bank_name'])?$this->data['bank_name']:'',
	    		     'bank_branch'=>isset($this->data['bank_branch'])?$this->data['bank_branch']:'',
	    		   ));
	    		   break;
	    	}

	    	if($model->save()){
	    		$this->code = 1; $this->msg = t("Payout account saved");
	    	} else $this->msg = CommonUtility::parseError( $model->getErrors());

    	} catch (Exception $e) {
		   $this->msg[] = t($e->getMessage());
		}
		$this->responseJson();
    }

    public function actiongetPayoutSettings()
    {

		try {
			$provider = AttributesTools::PaymentPayoutProvider();
			$country_list = AttributesTools::CountryList();
			$currency_list = AttributesTools::currencyListSelection();

			$account_type['individual'] = t("Individual");
			$account_type['company'] = t("Company");

			$default_currency = AttributesTools::defaultCurrency();
			$default_country = OptionsTools::find(array('admin_country_set'));

			$data = array(
			  'provider'=>$provider,
			  'country_list'=>$country_list,
			  'account_type'=>$account_type,
			  'currency_list'=>$currency_list,
			  'default_currency'=>$default_currency,
			  'default_country'=>isset($default_country['admin_country_set'])?$default_country['admin_country_set']:'',
			);
			$this->code = 1;
		    $this->msg = "OK";
		    $this->details = $data;
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());
		}
		$this->responseJson();
    }

    public function actiongetInformation()
    {
        try {

            $merchant_id = Yii::app()->merchant->merchant_id;
            $model = AR_merchant::model()->findByPk( intval($merchant_id) );
            $this->code = 1;
            $this->msg = "Ok";

            $this->details = [
                'merchant_uuid'=>$model->merchant_uuid,
                'restaurant_slug'=>$model->restaurant_slug,
                'restaurant_name'=>$model->restaurant_name,
                'contact_name'=>$model->contact_name,
                'restaurant_phone'=>$model->restaurant_phone,
                'contact_phone'=>$model->contact_phone,
                'contact_email'=>$model->contact_email,
                'description'=>$model->description,
                'short_description'=>$model->short_description,
                'distance_unit'=>$model->distance_unit,
                'delivery_distance_covered'=>$model->delivery_distance_covered,
                'is_ready'=>$model->is_ready==2?true:false,
                'cuisine'=>  (array)CommonUtility::ArrayToSingleValue(MerchantTools::getCuisine($merchant_id),'integer'),
                'services'=>(array)MerchantTools::getMerchantMeta($merchant_id,'services'),
                'featured'=>(array)MerchantTools::getMerchantMeta($merchant_id,'featured'),
                'tags'=>(array) CommonUtility::ArrayToSingleValue(MerchantTools::getMerchantOptions($merchant_id,'tags'),'integer'),
                'logo'=>$model->logo,
                'path'=>$model->path,
                'logo_url'=>CMedia::getImage($model->logo,$model->path,Yii::app()->params->size_image,CommonUtility::getPlaceholderPhoto('merchant_logo')),
                'header_image'=>$model->header_image,
                'path2'=>$model->path2,
                'header_image_url'=>CMedia::getImage($model->header_image,$model->path,Yii::app()->params->size_image,CommonUtility::getPlaceholderPhoto('merchant_logo')),
                'address'=>$model->address,
                'latitude'=>$model->latitude,
                'lontitude'=>$model->lontitude,
                'delivery_distance_covered'=>$model->delivery_distance_covered,
                'distance_unit'=>$model->distance_unit,
            ];
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actionupdateInformation()
    {
        try {

            $merchant_id = Yii::app()->merchant->merchant_id;
            $model = AR_merchant::model()->findByPk( intval($merchant_id) );
            $model->scenario='information';
		    $upload_path = CMedia::merchantFolder();

            $logo = isset($this->data['logo'])?$this->data['logo']:'';
            $upload_path = isset($this->data['upload_path'])?$this->data['upload_path']:$upload_path;

            $header_image = isset($this->data['header_image'])?$this->data['header_image']:'';
            $path2 = isset($this->data['path2'])?$this->data['path2']:$upload_path;
            
            $model->logo = $logo;
            $model->path = $upload_path;

            $model->header_image = $header_image;
            $model->path2 = $path2;

            $file_data = isset($this->data['featured_data'])?$this->data['featured_data']:'';
            $image_type = isset($this->data['image_type'])?$this->data['image_type']:'png';
            if(!empty($file_data)){
                $result = [];
                try {
                    $result = CImageUploader::saveBase64Image($file_data,$image_type,"upload/".Yii::app()->merchant->merchant_id);
                    $model->logo = isset($result['filename'])?$result['filename']:'';
                    $model->path = isset($result['path'])?$result['path']:'';
                } catch (Exception $e) {
                    $this->msg = t($e->getMessage());
                    $this->responseJson();
                }
            }

            $header_image_data = isset($this->data['header_image_data'])?$this->data['header_image_data']:'';
            $header_image_type = isset($this->data['header_image_type'])?$this->data['header_image_type']:'png';
            if(!empty($header_image_data)){
                $result = [];
                try {
                    $result = CImageUploader::saveBase64Image($header_image_data,$header_image_type,"upload/".Yii::app()->merchant->merchant_id);
                    $model->header_image = isset($result['filename'])?$result['filename']:'';
                    $model->path2 = isset($result['path'])?$result['path']:'';
                } catch (Exception $e) {
                    $this->msg = t($e->getMessage());
                    $this->responseJson();
                }
            }

            if($model){
                $model->is_ready = isset($this->data['is_ready'])? intval($this->data['is_ready']) :1;
                $model->restaurant_name = isset($this->data['restaurant_name'])?$this->data['restaurant_name']:'';
                $model->restaurant_slug = isset($this->data['restaurant_slug'])?$this->data['restaurant_slug']:'';
                $model->contact_name = isset($this->data['contact_name'])?$this->data['contact_name']:'';
                $model->contact_email = isset($this->data['contact_email'])?$this->data['contact_email']:'';
                $model->description = isset($this->data['description'])?$this->data['description']:'';
                $model->short_description = isset($this->data['short_description'])?$this->data['short_description']:'';
                $model->cuisine2 = isset($this->data['cuisine'])?$this->data['cuisine']:'';
                $model->tags = isset($this->data['tags'])?$this->data['tags']:'';
                $model->service2 = isset($this->data['services'])?$this->data['services']:'';
                $model->featured = isset($this->data['featured'])?$this->data['featured']:'';
                $model->delivery_distance_covered = isset($this->data['delivery_distance_covered'])?floatval($this->data['delivery_distance_covered']):0;
                $model->distance_unit = isset($this->data['distance_unit'])?$this->data['distance_unit']:'';
                if($model->save()){
                    $this->code = 1;
                    $this->msg = t(CommonUtility::t(Helper_update));
                } else $this->msg = CommonUtility::parseError( $model->getErrors() );
            } else $this->msg = t("Record not found");

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actionsaveAddress()
    {
        try {

            $merchant_id = Yii::app()->merchant->merchant_id;
            $model = AR_merchant::model()->findByPk( intval($merchant_id) );
            $model->scenario='address';

            if($model){
                $model->address = isset($this->data['address'])?$this->data['address']:'';
                $model->latitude = isset($this->data['latitude'])?$this->data['latitude']:'';
                $model->lontitude = isset($this->data['lontitude'])?$this->data['lontitude']:'';
                $model->delivery_distance_covered = isset($this->data['delivery_distance_covered'])?$this->data['delivery_distance_covered']:'';
                $model->distance_unit = isset($this->data['distance_unit'])?$this->data['distance_unit']:'';
                if($model->save()){
                    $this->code = 1;
                    $this->msg = t(CommonUtility::t(Helper_update));
                } else $this->msg = CommonUtility::parseError( $model->getErrors() );
            } else $this->msg = t("Record not found");

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actiongetSettings()
    {
        try {

            $merchant_id = Yii::app()->merchant->merchant_id;
            $model = AR_merchant::model()->findByPk( intval($merchant_id) );
            if($model){

                $options = array(
                    'enabled_private_menu','merchant_two_flavor_option','merchant_tax_number',
                    'merchant_extenal','merchant_enabled_voucher',
                    'merchant_enabled_tip','merchant_default_tip',
                    'merchant_close_store','merchant_disabled_ordering',
                    'tips_in_transactions','merchant_tip_type'
                );

                $data = array();
                if($resp = OptionsTools::find($options,$merchant_id)){
                    foreach ($resp as $name=>$val) {
                        if($name=="merchant_close_store" || $name=="merchant_enabled_voucher"
                        || $name=="merchant_enabled_tip"
                        ){
                            $val = $val==1?true:false;
                        }

                        if($name=="tips_in_transactions"){
                            $val = !empty($val) ?json_decode($val) : array();
                        }
                        $data[$name]=$val;
                    }
                }

                $data['merchant_close_store'] = $model->close_store==1?true:false;
                $data['merchant_enabled_voucher'] = isset($data['merchant_enabled_voucher'])?$data['merchant_enabled_voucher']:false;
                $data['merchant_enabled_tip'] = isset($data['merchant_enabled_tip'])?$data['merchant_enabled_tip']:false;                

                $this->code = 1;
                $this->msg = "OK";
                $this->details = $data;
            } else $this->msg = t("Record not found");

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actionsaveSettings()
    {
        try {

            $merchant_id = Yii::app()->merchant->merchant_id;
            $merchant = AR_merchant::model()->findByPk( intval($merchant_id) );
            if($merchant){

                $options = array(
                    'merchant_two_flavor_option','merchant_tax_number',
                    'merchant_extenal','merchant_enabled_voucher',
                    'merchant_enabled_tip','merchant_default_tip',
                    'merchant_close_store','merchant_disabled_ordering',
                    'tips_in_transactions','merchant_tip_type'
                );

                $model=new AR_option;
		        $model->scenario = 'settings';
                OptionsTools::$merchant_id = $merchant_id;

                $this->data['tips_in_transactions'] = isset($this->data['tips_in_transactions'])?json_encode($this->data['tips_in_transactions']):'';
                $this->data['merchant_two_flavor_option'] = isset($this->data['merchant_two_flavor_option'])?$this->data['merchant_two_flavor_option']:'';
                $this->data['merchant_tax_number'] = isset($this->data['merchant_tax_number'])?$this->data['merchant_tax_number']:'';
                $this->data['merchant_extenal'] = isset($this->data['merchant_extenal'])?$this->data['merchant_extenal']:'';
                $this->data['merchant_enabled_voucher'] = isset($this->data['merchant_enabled_voucher'])?$this->data['merchant_enabled_voucher']:'';
                $this->data['merchant_enabled_tip'] = isset($this->data['merchant_enabled_tip'])?$this->data['merchant_enabled_tip']:'';
                $this->data['merchant_default_tip'] = isset($this->data['merchant_default_tip'])?$this->data['merchant_default_tip']:'';
                $this->data['merchant_close_store'] = isset($this->data['merchant_close_store'])?$this->data['merchant_close_store']:'';
                $this->data['merchant_disabled_ordering'] = isset($this->data['merchant_disabled_ordering'])?$this->data['merchant_disabled_ordering']:'';
                $this->data['merchant_tip_type'] = isset($this->data['merchant_tip_type'])?$this->data['merchant_tip_type']:'';

                if(OptionsTools::save($options, $this->data, $merchant_id)){
                    $merchant->close_store = isset($this->data['merchant_close_store'])? intval($this->data['merchant_close_store']):0;
					$merchant->disabled_ordering = isset($this->data['merchant_disabled_ordering'])?  intval($this->data['merchant_disabled_ordering']) :0;
					$merchant->save();
                    $this->code = 1;
                    $this->msg = t(CommonUtility::t(Helper_update));
                } else Yii::app()->user->setFlash('error',t(Helper_failed_update));
            } else $this->msg = t("Record not found");

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actiongetOpeningHours()
    {
        try {

            $merchant_id = Yii::app()->merchant->merchant_id;
            $criteria=new CDbCriteria();
			$criteria->condition = "merchant_id=:merchant_id";
			$criteria->params  = array(
			':merchant_id'=>intval($merchant_id)
			);
			$criteria->order = "day_of_week ASC";
            $model = AR_opening_hours::model()->findAll($criteria);
            if($model){
                $data = [];
                foreach ($model as $items) {
                    $data[]=[
                        'id'=>$items->id,
                        'day'=>$items->day,
                        'status'=>t($items->status),
                        'opening_hours'=>t("{start} - {end_time}",[
                            '{start}'=>Date_Formatter::Time($items->start_time),
                            '{end_time}'=>Date_Formatter::Time($items->end_time),
                        ])
                    ];
                }
                $this->code = 1; $this->msg = "Ok";
                $this->details = $data;
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actiondeleteHours()
    {
        try {

            $merchant_id = (integer) Yii::app()->merchant->merchant_id;
            $id = Yii::app()->input->post('id');

            $model = AR_opening_hours::model()->find("merchant_id=:merchant_id AND id=:id",[
                ':merchant_id'=>intval($merchant_id),
                ':id'=>intval($id)
            ]);
            if($model){
                $model->delete();
                $this->code = 1;
                $this->msg = "Ok";
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actioncreateHours()
    {
        try {

            $merchant_id = (integer) Yii::app()->merchant->merchant_id;
            $id = isset($this->data['id'])?$this->data['id']:'';

            if($id>0){
                $model = AR_opening_hours::model()->findByPk( intval($id) );
                if(!$model){
                    $this->msg = t(HELPER_RECORD_NOT_FOUND);
                    $this->responseJson();
                }
            } else $model = new AR_opening_hours;

            $model->mtid = $merchant_id;
            $model->merchant_id = $merchant_id;
            $model->day = isset($this->data['day'])?$this->data['day']:'' ;
            $model->status = isset($this->data['status'])? ($this->data['status']==1?'open':'close')  :'close' ;
            $model->start_time = isset($this->data['start_time'])?$this->data['start_time']:'' ;
            $model->end_time = isset($this->data['end_time'])?$this->data['end_time']:'' ;
            $model->start_time_pm = isset($this->data['start_time_pm'])?$this->data['start_time_pm']:'' ;
            $model->end_time_pm = isset($this->data['end_time_pm'])?$this->data['end_time_pm']:'' ;
            $model->custom_text = isset($this->data['custom_text'])?$this->data['custom_text']:'' ;

            if($model->save()){
	    		$this->code = 1;
                $this->msg =  $id>0?t("Store hours updated"):t("Store hours succesfully added");
	    	} else $this->msg = CommonUtility::parseError( $model->getErrors());

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actiongetHours()
    {
        try {

            $id = intval(Yii::app()->input->post('id'));
            $model = AR_opening_hours::model()->findByPk($id);
            if($model){
                $this->code = 1; $this->msg = "Ok";
                $this->details = [
                    'day'=>$model->day,
                    'status'=>$model->status=="open"?true:false,
                    'start_time'=> Date_Formatter::Time($model->start_time,'hh:mm a',true),
                    'end_time'=> Date_Formatter::Time($model->end_time,'hh:mm a',true),
                    'start_time_pm'=> Date_Formatter::Time($model->start_time_pm,'hh:mm a',true),
                    'end_time_pm'=> Date_Formatter::Time($model->end_time_pm,'hh:mm a',true),
                    'custom_text'=>$model->custom_text,
                ];
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actiongetCustomerDetails()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $client_id = intval(Yii::app()->input->post('client_id'));

            $addresses = array();

		   if($data = COrders::getClientInfo($client_id)){
			   try {
			      $addresses = ACustomer::getAddresses($client_id);
			   } catch (Exception $e) {
			   	  //
			   }
			   $this->code = 1;
			   $this->msg = "OK";
			   $this->details = array(
			     'customer'=>$data,
			     'block_from_ordering'=>ACustomer::isBlockFromOrdering($client_id,$merchant_id),
			     'addresses'=>$addresses,
			   );
		   } else $this->msg = t("Client information not found");

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actiongetCustomerSummary()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $client_id = intval(Yii::app()->input->post('client_id'));

		    $not_in_status = AOrderSettings::getStatus(array('status_cancel_order','status_rejection'));
		    $orders = ACustomer::getOrdersTotal($client_id,$merchant_id,array(),$not_in_status);

		    $status_cancel = AOrderSettings::getStatus(array('status_cancel_order'));
		    $order_cancel = ACustomer::getOrdersTotal($client_id,$merchant_id,$status_cancel);

		    $status_delivered = AOrderSettings::getStatus(array('status_delivered'));
		    $total = ACustomer::getOrderSummary($client_id,$merchant_id,$status_delivered);
		    $total_refund = ACustomer::getOrderRefundSummary($client_id,$merchant_id,AttributesTools::refundStatus());

		    $data = array(
		     'orders'=>$orders,
		     'order_cancel'=>$order_cancel,
		     'total'=>Price_Formatter::formatNumber($total),
		     'total_refund'=>Price_Formatter::formatNumber($total_refund),
		    );

		    $this->code = 1;
		    $this->msg = "OK";
		    $this->details = $data;

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actiongetCustomerOrders()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $client_id = intval(Yii::app()->input->post('client_id'));
            $limit = Yii::app()->params->list_limit;
            $initial_status = AttributesTools::initialStatus();
            $status = COrders::statusList(Yii::app()->language);

            $page = intval(Yii::app()->input->post('page'));
			$page_raw = intval(Yii::app()->input->post('page'));
			if($page>0){
				$page = $page-1;
			}

            $criteria=new CDbCriteria();
            $criteria->alias = "a";
            $criteria->select="order_id,order_uuid,total,status,date_created";
            $criteria->condition = "merchant_id=:merchant_id AND client_id=:client_id ";
            $criteria->params  = array(
            ':merchant_id'=>intval($merchant_id),
            ':client_id'=>intval($client_id)
            );
			$criteria->order = "date_created DESC";
            $criteria->addNotInCondition('status', array($initial_status) );

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
					$this->responseJson();
				}
			}

            if($model = AR_ordernew::model()->findAll($criteria)){
                $data = [];
                foreach ($model as $items) {
                    $data[] = [
                        'order_id'=>$items->order_id,
                        'order_uuid'=>$items->order_uuid,
                        'total'=>Price_Formatter::formatNumber($items->total),
                        'status_raw'=>$items->status,
                        'status'=>isset($status[$items->status])?$status[$items->status]:$items->status,
                        'date_created'=>Date_Formatter::dateTime($items->date_created)
                    ];
                }

                $this->code = 1; $this->msg = "Ok";
                $this->details = $data;
            } else $this->msg = t(HELPER_NO_RESULTS);

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actionblockCustomer()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $client_id = intval(Yii::app()->input->post('client_id'));

            $meta_name = 'block_customer';
			$block = intval(Yii::app()->input->post('block'));

			$model = AR_merchant_meta::model()->find("merchant_id=:merchant_id AND
			meta_name=:meta_name AND meta_value=:meta_value",array(
			 ':merchant_id'=>intval($merchant_id),
			 ':meta_name'=>$meta_name,
			 ':meta_value'=>$client_id
			));

			if($model){
				if($block!=1){
					$model->delete();
				}
			} else {
				if($block==1){
					$model = new AR_merchant_meta;
					$model->merchant_id = $merchant_id;
					$model->meta_name = $meta_name;
					$model->meta_value = $client_id;
					$model->save();
				}
			}

            $block = intval($block);
			$this->code = 1;
			$this->msg = t("Successful");
			$this->details = $block==0?false:true;


        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actiongetCustomerReview()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $limit = Yii::app()->params->list_limit;
            $status_list = AttributesTools::StatusManagement('post');

            $page = intval(Yii::app()->input->post('page'));
			$page_raw = intval(Yii::app()->input->post('page'));
			if($page>0){
				$page = $page-1;
			}

            $criteria=new CDbCriteria();
            $criteria->alias = "a";
            $criteria->select="
            a.id,a.client_id,a.review,a.rating,a.status,a.date_created,
            concat(b.first_name,' ',b.last_name) as customer_fullname,
            b.avatar as logo,b.path
            ";
            $criteria->join='LEFT JOIN {{client}} b on a.client_id = b.client_id ';
			$criteria->condition = "a.merchant_id=:merchant_id";
			$criteria->params  = array(
			':merchant_id'=>$merchant_id
			);
			$criteria->order = "date_created DESC";

		    $count=AR_review::model()->count($criteria);
			$pages=new CPagination($count);
			$pages->pageSize=$limit;
			$pages->setCurrentPage( $page );
			$pages->applyLimit($criteria);
			$page_count = $pages->getPageCount();            
            $total_items = $pages->getItemCount();

			if($page>0){
				if($page_raw>$page_count){
					$this->code = 3;
					$this->msg = t("end of results");
					$this->responseJson();
				}
			}

            if($model = AR_review::model()->findAll($criteria)){
                $data = [];
                foreach ($model as $items) {
                    $data[] = [
                        'id'=>$items->id,
                        'client_id'=>$items->client_id,
                        'customer_fullname'=>$items->customer_fullname,
                        'review'=>$items->review,
                        'rating'=>$items->rating,
                        'status'=> isset($status_list[$items->status])?$status_list[$items->status]:$items->status,
                        'date_created'=>PrettyDateTime::parse(new DateTime($items->date_created)),
                        'avatar'=>CMedia::getImage($items->logo,$items->path,Yii::app()->params->size_image,CommonUtility::getPlaceholderPhoto('customer')),
                    ];
                }
                $this->code = 1; $this->msg = "Ok";
                $this->details = [
                    'total_items'=>$total_items,
                    'data'=>$data,
                    'status_list'=>$status_list
                ];
            } else $this->msg = t(HELPER_NO_RESULTS);

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actionreviewAddreply()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $reply_comment = isset($this->data['reply_comment'])?$this->data['reply_comment']:'';
            $parent_id = isset($this->data['id'])?$this->data['id']:'';

            $merchant = AR_merchant::model()->findByPk( intval($merchant_id) );
            if($merchant){
                $model = new AR_review;
		        $model->scenario = 'reply';
                $model->parent_id = intval($parent_id);
				$model->reply_from = $merchant->restaurant_name;
                $model->reply_comment = $reply_comment;
				$model->review = $reply_comment;
                if($model->save()){
                    $this->code = 1;
                    $this->msg = t("Reply to review added");
                } else $this->msg = CommonUtility::parseError( $model->getErrors() );
            } else $this->msg  = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actionSearchMenu()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $q = trim(Yii::app()->input->post('q'));

            try {
                $category = CMerchantMenu::searchCategory($merchant_id,$q,Yii::app()->language);
            } catch (Exception $e) {
                $category = [];
            }

            try {
                $items = CMerchantMenu::searchByItems($merchant_id,$q,Yii::app()->language);
            } catch (Exception $e) {
                $items = [];
            }

            try {
                $addons = CMerchantMenu::searchAddons($merchant_id,$q,Yii::app()->language);
            } catch (Exception $e) {
                $addons = [];
            }

            try {
                $addon_item = CMerchantMenu::searchAddonItem($merchant_id,$q,Yii::app()->language);
            } catch (Exception $e) {
                $addon_item = [];
            }

            $this->code = 1;
            $this->msg = "Ok";
            $this->details = [
                'category'=>$category,
                'items'=>$items,
                'addons'=>$addons,
                'addon_item'=>$addon_item
            ];

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actionstoreSettings()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $merchant = AR_merchant::model()->findByPk( intval($merchant_id) );            
            if($merchant){

                $meta = AR_merchant_meta::getMeta($merchant_id,[
                    'local_notification',
                    'app_push_notifications'
                ]);            
                $app_push_notifications = isset($meta['app_push_notifications'])?$meta['app_push_notifications']['meta_value']:false;
                $local_notification = isset($meta['local_notification'])?$meta['local_notification']['meta_value']:false;

                $self_delivery = isset(Yii::app()->params['settings_merchant']['self_delivery'])?Yii::app()->params['settings_merchant']['self_delivery']:false;
		        $self_delivery = $self_delivery==1?true:false;					
                
                $this->code = 1;
                $this->msg  = "Ok";
                $this->details = [
                    'store_available'=>$merchant->close_store==1?false:true,
                    'self_delivery'=>$self_delivery,
                    'push_notifications'=>$app_push_notifications==1?true:false,
                    'local_notification'=>$local_notification==1?true:false,                    
                ];
            } else $this->msg = t(Helper_not_found);
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actionsetStoreAvailable()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $available = intval(Yii::app()->input->post('available'));
            $merchant = AR_merchant::model()->findByPk( intval($merchant_id) );
            if($merchant){
                $merchant->close_store = $available;
                if($merchant->save()){
                    $this->code = 1;
                    $this->msg  = "Ok";
                    $this->details = [
                        'store_available'=>$merchant->close_store==1?false:true
                    ];
                } else $this->msg = CommonUtility::parseError( $merchant->getErrors() );
            } else $this->msg = t(Helper_not_found);
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actiongetSizeList()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $limit = Yii::app()->params->list_limit;

            $page = intval(Yii::app()->input->post('page'));
			$page_raw = intval(Yii::app()->input->post('page'));
			if($page>0){
				$page = $page-1;
			}

			$criteria=new CDbCriteria();
            $criteria->alias="a";
            $criteria->select = "a.size_id ,b.size_name, a.status,a.date_created";
            $criteria->join='LEFT JOIN {{size_translation}} b on a.size_id = b.size_id ';
			$criteria->condition = "merchant_id=:merchant_id AND b.language=:language";
			$criteria->params  = array(
			':merchant_id'=>$merchant_id,
            ':language'=>Yii::app()->language
			);
			$criteria->order = "date_created DESC";

		    $count=AR_size::model()->count($criteria);
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

            if($model = AR_size::model()->findAll($criteria)){
                $data = [];
                foreach ($model as $items) {
                    $data[] = [
                        'size_id'=>$items->size_id,
                        'size_name'=>$items->size_name,
                        'status'=>$items->status,
                        'date_created'=>Date_Formatter::dateTime($items->date_created),
                    ];
                }
                $this->code = 1;
				$this->msg = "ok";
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

    public function actiondeleteSize()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $size_id = intval(Yii::app()->input->post('size_id'));
            $model = AR_size::model()->find('merchant_id=:merchant_id AND size_id=:size_id',
		    array(':merchant_id'=>$merchant_id, ':size_id'=>$size_id ));
            if($model){
                $model->delete();
                $this->code = 1;
                $this->msg = "Ok";
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actionaddSize()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $size_id = isset($this->data['size_id'])?intval($this->data['size_id']):0;
            $translation_data = isset($this->data['translation_data'])?$this->data['translation_data']:'';

            if($size_id>0){
                $model = AR_size::model()->findByPk($size_id);
            } else $model = new AR_size();

            $model->merchant_id = intval($merchant_id);
            $model->size_name = isset($this->data['size_name'])?$this->data['size_name']:'';
            $model->status = isset($this->data['status'])?$this->data['status']:'';

            if(is_array($translation_data) && count($translation_data)>=1){
                $name = isset($translation_data[0])? (isset($translation_data[0]['name'])?$translation_data[0]['name']:array()) :array();
                $model->size_name_translation=$name;
            }

            if($model->save()){
                $this->code = 1;
                $this->msg =  $size_id>0?t("Size updated"):t("Size succesfully added");
            } else $this->msg = CommonUtility::parseError($model->getErrors());

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actiongetSize()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $size_id = intval(Yii::app()->input->post('size_id'));
            $model = AR_size::model()->findByPk($size_id);
            if($model){

                $translation = AttributesTools::GetFromTranslation($size_id,'{{size}}',
                        '{{size_translation}}',
                        'size_id',
                        array('size_id','size_name'),
                        array(
                        'size_name'=>'size_name_translation',
                        )
                    );
                if(is_array($translation) && count($translation)>=1){
                    $translation['name'] = $translation['size_name'];
                    $translation['description'] = array();
                    unset($translation['size_name']);
                }

                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'size_name'=>$model->size_name,
                    'status'=>$model->status,
                    'translation'=>$translation
                ];
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actionIngredientList()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $limit = Yii::app()->params->list_limit;

            $page = intval(Yii::app()->input->post('page'));
			$page_raw = intval(Yii::app()->input->post('page'));
			if($page>0){
				$page = $page-1;
			}

			$criteria=new CDbCriteria();
            $criteria->alias="a";
            $criteria->select = "a.ingredients_id ,b.ingredients_name, a.status,a.date_created";
            $criteria->join='LEFT JOIN {{ingredients_translation}} b on a.ingredients_id = b.ingredients_id ';
			$criteria->condition = "merchant_id=:merchant_id AND b.language=:language";
			$criteria->params  = array(
			':merchant_id'=>$merchant_id,
            ':language'=>Yii::app()->language
			);
			$criteria->order = "date_created DESC";

		    $count=AR_ingredients::model()->count($criteria);
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

            if($model = AR_ingredients::model()->findAll($criteria)){
                $data = [];
                foreach ($model as $items) {
                    $data[] = [
                        'ingredients_id'=>$items->ingredients_id,
                        'ingredients_name'=>$items->ingredients_name,
                        'status'=>$items->status,
                        'date_created'=>Date_Formatter::dateTime($items->date_created),
                    ];
                }
                $this->code = 1;
				$this->msg = "ok";
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

    public function actionaddIngredients()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $id = isset($this->data['id'])?intval($this->data['id']):0;
            $translation_data = isset($this->data['translation_data'])?$this->data['translation_data']:'';

            if($id>0){
                $model = AR_ingredients::model()->findByPk($id);
            } else $model = new AR_ingredients();

            $model->merchant_id = intval($merchant_id);
            $model->ingredients_name = isset($this->data['name'])?$this->data['name']:'';
            $model->status = isset($this->data['status'])?$this->data['status']:'';

            if(is_array($translation_data) && count($translation_data)>=1){
                $name = isset($translation_data[0])? (isset($translation_data[0]['name'])?$translation_data[0]['name']:array()) :array();
                $model->ingredients_translation=$name;
            }

            if($model->save()){
                $this->code = 1;
                $this->msg =  $id>0?t("Ingredients updated"):t("Ingredients succesfully added");
            } else $this->msg = CommonUtility::parseError($model->getErrors());

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actiongetIngredients()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $id = intval(Yii::app()->input->post('id'));
            $model = AR_ingredients::model()->findByPk($id);
            if($model){

                $translation = AttributesTools::GetFromTranslation($id,'{{ingredients}}',
                        '{{ingredients_translation}}',
                        'ingredients_id',
                        array('ingredients_id','ingredients_name'),
                        array(
                        'ingredients_name'=>'ingredients_translation',
                        )
                    );
                if(is_array($translation) && count($translation)>=1){
                    $translation['name'] = $translation['ingredients_name'];
                    $translation['description'] = array();
                    unset($translation['ingredients_name']);
                }

                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'name'=>$model->ingredients_name,
                    'status'=>$model->status,
                    'translation'=>$translation
                ];
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actiondeleteIngredients()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $id = intval(Yii::app()->input->post('id'));
            $model = AR_ingredients::model()->find('merchant_id=:merchant_id AND ingredients_id=:ingredients_id',
		    array(':merchant_id'=>$merchant_id, ':ingredients_id'=>$id ));
            if($model){
                $model->delete();
                $this->code = 1;
                $this->msg = "Ok";
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actionCookingList()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $limit = Yii::app()->params->list_limit;

            $page = intval(Yii::app()->input->post('page'));
			$page_raw = intval(Yii::app()->input->post('page'));
			if($page>0){
				$page = $page-1;
			}

			$criteria=new CDbCriteria();
            $criteria->alias="a";
            $criteria->select = "a.cook_id ,b.cooking_name, a.status,a.date_created";
            $criteria->join='LEFT JOIN {{cooking_ref_translation}} b on a.cook_id = b.cook_id ';
			$criteria->condition = "merchant_id=:merchant_id AND b.language=:language";
			$criteria->params  = array(
			':merchant_id'=>$merchant_id,
            ':language'=>Yii::app()->language
			);
			$criteria->order = "date_created DESC";

		    $count=AR_cookingref::model()->count($criteria);
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

            if($model = AR_cookingref::model()->findAll($criteria)){
                $data = [];
                foreach ($model as $items) {
                    $data[] = [
                        'cook_id'=>$items->cook_id,
                        'cooking_name'=>$items->cooking_name,
                        'status'=>$items->status,
                        'date_created'=>Date_Formatter::dateTime($items->date_created),
                    ];
                }
                $this->code = 1;
				$this->msg = "ok";
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

    public function actiondeleteCooking()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $id = intval(Yii::app()->input->post('id'));
            $model = AR_cookingref::model()->find('merchant_id=:merchant_id AND cook_id=:cook_id',
		    array(':merchant_id'=>$merchant_id, ':cook_id'=>$id ));
            if($model){
                $model->delete();
                $this->code = 1;
                $this->msg = "Ok";
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actionaddCooking()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $id = isset($this->data['id'])?intval($this->data['id']):0;
            $translation_data = isset($this->data['translation_data'])?$this->data['translation_data']:'';

            if($id>0){
                $model = AR_cookingref::model()->findByPk($id);
            } else $model = new AR_cookingref();

            $model->merchant_id = intval($merchant_id);
            $model->cooking_name = isset($this->data['name'])?$this->data['name']:'';
            $model->status = isset($this->data['status'])?$this->data['status']:'';

            if(is_array($translation_data) && count($translation_data)>=1){
                $name = isset($translation_data[0])? (isset($translation_data[0]['name'])?$translation_data[0]['name']:array()) :array();
                $model->cooking_translation=$name;
            }

            if($model->save()){
                $this->code = 1;
                $this->msg =  $id>0?t("Cooking updated"):t("Cooking succesfully added");
            } else $this->msg = CommonUtility::parseError($model->getErrors());

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actiongetCooking()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $id = intval(Yii::app()->input->post('id'));
            $model = AR_cookingref::model()->findByPk($id);
            if($model){

                $translation = AttributesTools::GetFromTranslation($id,'{{cooking_ref}}',
                        '{{cooking_ref_translation}}',
                        'cook_id',
                        array('cook_id','cooking_name'),
                        array(
                        'cooking_name'=>'cooking_translation',
                        )
                    );
                if(is_array($translation) && count($translation)>=1){
                    $translation['name'] = $translation['cooking_name'];
                    $translation['description'] = array();
                    unset($translation['cooking_name']);
                }

                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'name'=>$model->cooking_name,
                    'status'=>$model->status,
                    'translation'=>$translation
                ];
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

	public function actionregisterDevice()
	{
		try {

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
				$model->user_type = "merchant";
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
			$this->details = json_encode($_POST);

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


			$model = AR_device::model()->find("device_token = :device_token",[
				':device_token'=>$token
			]);
			if($model){
				$model->device_uiid = $device_uiid;
				$model->user_id = Yii::app()->merchant->id;
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
				$model->user_type = "merchant";
				$model->user_id = Yii::app()->merchant->id;
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

    public function actionCreateAccountMerchant()
    {
        try {

            $address = isset($this->data['address'])?$this->data['address']:'';
            $contact_email = isset($this->data['contact_email'])?$this->data['contact_email']:'';
            $membership_type = isset($this->data['membership_type'])?$this->data['membership_type']:'';
            $restaurant_name = isset($this->data['restaurant_name'])?$this->data['restaurant_name']:'';
            $latitude = isset($this->data['latitude'])?$this->data['latitude']:'';
            $lontitude = isset($this->data['lontitude'])?$this->data['lontitude']:'';
            $phone = isset($this->data['phone'])?$this->data['phone']:'';
            $mobile_prefix = isset($phone['mobile_prefix'])?$phone['mobile_prefix']:'';
            $mobile_number = isset($phone['mobile_number'])?$phone['mobile_number']:'';            

            $model = new AR_merchant;
            $model->scenario = 'website_registration';
            $model->restaurant_name = $restaurant_name;
            $model->address = $address;
            $model->contact_email = $contact_email;
            $mobile_prefix = $mobile_prefix;
            $model->contact_phone = $mobile_number;
            $model->merchant_type = $membership_type;
            $model->latitude = $latitude;
            $model->lontitude = $lontitude;
            $model->service2 =  isset($this->data['services'])?$this->data['services']:'';
		    $model->merchant_base_currency = isset($this->data['currency'])?$this->data['currency']:'';

            $multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
            $multicurrency_enabled = $multicurrency_enabled==1?true:false;					
		    $model->multicurrency_enabled = $multicurrency_enabled;

            if($program = CMerchantSignup::get($model->merchant_type)){
                if($program->type_id==2){
                    $model->commision_type = trim($program->commision_type);
                    $model->percent_commision = floatval($program->commission);
                    $model->commision_based = $program->based_on;
                }
            }

            $commission_type = []; $commission_value = [];

            if($model->merchant_type==2){
                $model_merchant_type = AR_merchant_type::model()->find("type_id=:type_id",[
                    ":type_id"=>$model->merchant_type
                ]);
                if($model_merchant_type){
                    $commission_data = !empty($model_merchant_type->commission_data)?json_decode($model_merchant_type->commission_data,true):false;
                    if(is_array($commission_data) && count($commission_data)>=1){				
                        foreach ($commission_data as $items) {
                            $commission_type[$items['transaction_type']] = $items['commission_type'];
                            $commission_value[$items['transaction_type']] = $items['commission'];
                        }				
                        $model->commission_type = $commission_type;
                        $model->commission_value = $commission_value;
                    }			
                }		
            }		
            
            if ($model->save()){
                $this->code = 1; $this->msg = t("Registration successful");
                $this->details = [
                    'merchant_uuid'=>$model->merchant_uuid
                ];
            } else $this->msg = CommonUtility::parseError( $model->getErrors() );

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actiongetMerchant()
    {
        try {

            $merchant_uuid = Yii::app()->input->post('merchant_uuid');
            $model = CMerchants::getByUUID($merchant_uuid);
            $this->code = 1;
            $this->msg = "Ok";

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actionCreateMerchantUser()
    {
        try {

            $phone = isset($this->data['phone'])?$this->data['phone']:'';
            $mobile_prefix = isset($phone['mobile_prefix'])?$phone['mobile_prefix']:'';
            $mobile_number = isset($phone['mobile_number'])?$phone['mobile_number']:'';
            $merchant_uuid =  isset($this->data['merchant_uuid'])?$this->data['merchant_uuid']:'';
            $merchant = CMerchants::getByUUID($merchant_uuid);

            $model =  AR_merchant_user::model()->find("merchant_id=:merchant_id AND main_account=:main_account",array(
                ':merchant_id'=>intval($merchant->merchant_id),
                ':main_account'=>1
            ));

            if(!$model){
                    $model = new AR_merchant_user;
                    $model->scenario = 'register';
            }

           $model->username = isset($this->data['username'])?$this->data['username']:'';
		   $model->password = isset($this->data['password'])?trim($this->data['password']):'';
		   $model->new_password = isset($this->data['password'])?trim($this->data['password']):'';
		   $model->repeat_password = isset($this->data['cpassword'])?trim($this->data['cpassword']):'';

		   if($model->scenario=="update"){
		   	  $model->password = md5($model->password);
		   }

		   $model->first_name = isset($this->data['first_name'])?$this->data['first_name']:'';
		   $model->last_name = isset($this->data['last_name'])?$this->data['last_name']:'';
		   $model->contact_email = isset($this->data['contact_email'])?$this->data['contact_email']:'';
		   $mobile_prefix = $mobile_prefix;
		   $model->contact_number = $mobile_number;
		   $model->merchant_id = $merchant->merchant_id;
		   $model->main_account = 1;

           if($model->save()){
               $this->code = 1;
		   	   $this->msg = t("Registration successful");

               $redirect = '';
               if($merchant->merchant_type==1){
					$redirect = 'choose_plan';
			   } elseif ($merchant->merchant_type==2){
					$redirect = 'getbacktoyou';
			   }

			   $this->details = array(
                  'merchant_uuid'=>$merchant_uuid,
				  'redirect'=>$redirect
			   );

           } else $this->msg =  CommonUtility::parseError( $model->getErrors());
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actiongetPlan()
    {
        try {

            $merchant_uuid = Yii::app()->input->post('merchant_uuid');
            $model = CMerchants::getByUUID($merchant_uuid);

            $data = CPlan::listing( Yii::app()->language );
			try {
			    $details = CPlan::Details();
			} catch (Exception $e) {
				//
			}

            $this->code = 1;
            $this->msg = "Ok";
            $this->details = array(
                'data'=>$data,
                'plan_details'=>$details,
            );

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actionPaymenPlanList()
    {
        try {

            $payment_list = AttributesTools::PaymentPlansProvider();
		 	$this->code = 1;
		 	$this->msg = "ok";
		 	$this->details = $payment_list;

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actionSavePrinter()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $printer_id = isset($this->data['printer_id'])? intval($this->data['printer_id']) :0;

            if($printer_id>0){
                $model = AR_printer::model()->find("merchant_id=:merchant_id AND  printer_id=:printer_id",[
                    ':merchant_id'=>$merchant_id,
                    ':printer_id'=>$printer_id
                ]);
                if(!$model){
                    $this->msg = t("Printer record not found");
                    $this->responseJson();
                }
            } else $model = new AR_printer;

             //dump($this->data);
            // die();

            $model->merchant_id = $merchant_id;
            $model->device_uuid = isset($this->data['device_uuid'])?$this->data['device_uuid']:'';
            $model->printer_name = isset($this->data['printer_name'])?$this->data['printer_name']:'';
            $model->printer_bt_name = isset($this->data['printer_bt_name'])?$this->data['printer_bt_name']:'';
            
            $model->printer_model = isset($this->data['printer_model'])?$this->data['printer_model']:'';
            $model->paper_width = isset($this->data['paper_width'])? intval($this->data['paper_width']) :58;
            $model->auto_print = isset($this->data['auto_print'])? intval($this->data['auto_print']) :0;
            $model->service_id = isset($this->data['service_id'])?$this->data['service_id']:'';
            $model->characteristics = isset($this->data['characteristics'])?$this->data['characteristics']:'';

            $model->printer_uuid = isset($this->data['printer_uuid'])?$this->data['printer_uuid']:'';

            $model->printer_user = isset($this->data['printer_user'])?$this->data['printer_user']:'';
            $model->printer_ukey = isset($this->data['printer_ukey'])?$this->data['printer_ukey']:'';
            $model->printer_sn = isset($this->data['printer_sn'])?$this->data['printer_sn']:'';
            $model->printer_key = isset($this->data['printer_key'])?$this->data['printer_key']:'';
            
            if($model->printer_model=="feieyun"){
                $model->scenario = $printer_id>0?"feieyun_update": "feieyun_add";
            }           
        
            if($model->save()){
	    		$this->code = 1; $this->msg = t("Printer successfully added");
	    	} else $this->msg = CommonUtility::parseError( $model->getErrors());

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actionPrintersList()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);            
            $limit = Yii::app()->params->list_limit;

            $page = intval(Yii::app()->input->post('page'));
            $device_uuid = Yii::app()->input->post('device_uuid');
			$page_raw = intval(Yii::app()->input->post('page'));
			if($page>0){
				$page = $page-1;
			}

			$criteria=new CDbCriteria();
            $criteria->alias="a";
            $criteria->select = "a.printer_id,a.printer_name,a.printer_bt_name,a.printer_model,a.paper_width,
            a.service_id, a.characteristics,
            b.meta_value1 as printer_uuid
            ";
            $criteria->join='LEFT JOIN {{printer_meta}} b on a.printer_id = b.printer_id ';
			$criteria->condition = "merchant_id=:merchant_id AND  device_uuid=:device_uuid AND b.meta_name=:meta_name ";
			$criteria->params  = array(
			':merchant_id'=>$merchant_id,
            ':device_uuid'=>$device_uuid,
            ':meta_name'=>'device_uuid'
			);
			$criteria->order = "date_created DESC";

		    $count=AR_printer::model()->count($criteria);
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

            if($model = AR_printer::model()->findAll($criteria)){                
                $data = [];
                foreach ($model as $items) {
                    $data[] = [
                        'printer_id'=>$items->printer_id,
                        'printer_name'=>$items->printer_name,
                        'printer_bt_name'=>$items->printer_bt_name,
                        'printer_model'=>$items->printer_model,
                        'paper_width'=>$items->paper_width,
                        'printer_uuid'=>$items->printer_uuid,
                        'service_id'=>$items->service_id,
                        'characteristics'=>$items->characteristics,
                        'date_created'=>Date_Formatter::dateTime($items->date_created),
                    ];
                }
                $this->code = 1;
				$this->msg = "ok";
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

    public function actionPrinterDetails()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $printer_id = Yii::app()->input->post('printer_id');

            $model = AR_printer::model()->find("merchant_id=:merchant_id AND printer_id=:printer_id",[
                ":merchant_id"=>$merchant_id,
                ':printer_id'=>intval($printer_id)
            ]);
            if($model){
                $this->code = 1; $this->msg = "Ok";

                $printer_uuid='';
                //if($model->printer_model=="bluetooth" || $model->printer_model=="sunmi"){
                    $data = AR_printer_meta::getValue($printer_id,'device_uuid');
                    $printer_uuid = isset($data['meta_value1'])?$data['meta_value1']:'';
                //}

                $printer_user='';
                $printer_ukey='';
                $printer_sn='';
                $printer_key='';

                if($model->printer_model=="feieyun"){
                    $meta = AR_printer_meta::getMeta($printer_id,['printer_user','printer_ukey','printer_sn','printer_key']);                    
                    $printer_user = isset($meta['printer_user'])?$meta['printer_user']['meta_value1']:'';
                    $printer_ukey = isset($meta['printer_ukey'])?$meta['printer_ukey']['meta_value1']:'';
                    $printer_sn = isset($meta['printer_sn'])?$meta['printer_sn']['meta_value1']:'';
                    $printer_key = isset($meta['printer_key'])?$meta['printer_key']['meta_value1']:'';
                }

                $services_list = [];
                if(!empty($model->service_id)){
                    $services_list[] = [
                        'label'=>$model->service_id,
                        'value'=>$model->service_id,
                    ];
                }

                $characteristics_list = [];
                if(!empty($model->characteristics)){
                    $characteristics_list[] = [
                        'label'=>$model->characteristics,
                        'value'=>$model->characteristics,
                    ];
                }
                
                $this->details = [
                    'printer_name'=>$model->printer_name,
                    'printer_bt_name'=>$model->printer_bt_name,
                    'printer_model'=>$model->printer_model,
                    'service_id'=>$model->service_id,
                    'characteristics'=>$model->characteristics,
                    'services_list'=>$services_list,
                    'characteristics_list'=>$characteristics_list,
                    'paper_width'=>$model->paper_width,
                    'auto_print'=>$model->auto_print==1?true:false,
                    'printer_uuid'=>$printer_uuid,
                    'printer_user'=>$printer_user,
                    'printer_ukey'=>$printer_ukey,
                    'printer_sn'=>$printer_sn,
                    'printer_key'=>$printer_key,
                ];
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actiondeletePrinter()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $printer_id = Yii::app()->input->post('printer_id');

            $model = AR_printer::model()->find('merchant_id=:merchant_id AND printer_id=:printer_id',
		    array(':merchant_id'=>$merchant_id, ':printer_id'=>$printer_id ));
            if($model){
                $model->delete();
                $this->code = 1;
                $this->msg = "Ok";
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actiongetAutoPrinter()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);   
            $device_uuid = Yii::app()->input->post('device_uuid');                     

            $criteria=new CDbCriteria();
            $criteria->alias="a";
            $criteria->select = "a.printer_id,a.printer_name,a.printer_bt_name,a.printer_model,a.paper_width,
            a.service_id,a.characteristics,
            b.meta_value1 as printer_uuid
            ";
            $criteria->join='LEFT JOIN {{printer_meta}} b on a.printer_id = b.printer_id ';
			$criteria->condition = "merchant_id=:merchant_id AND  device_uuid=:device_uuid AND auto_print=:auto_print AND b.meta_name=:meta_name ";
			$criteria->params  = array(
			':merchant_id'=>$merchant_id,
            ':device_uuid'=>$device_uuid,
            ':auto_print'=>1,
            ':meta_name'=>'device_uuid'
			);            
            if($model = AR_printer::model()->find($criteria)){
                $this->code = 1; $this->msg = "Ok";
                $this->details = [
                    'printer_name'=>$model->printer_name,
                    'printer_bt_name'=>$model->printer_bt_name,
                    'printer_model'=>$model->printer_model,
                    'printer_uuid'=>$model->printer_uuid,
                    'paper_width'=>$model->paper_width,
                    'service_id'=>$model->service_id,
                    'characteristics'=>$model->characteristics
                ];
            } else $this->msg = t("No default printer");
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actiongetCustomerReply()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $id = intval(Yii::app()->input->post('id'));
            
            $model = AR_review::model()->findAll("merchant_id=:merchant_id AND parent_id=:parent_id",[
                ':merchant_id'=>0,
                ':parent_id'=>$id
            ]);
            if($model){
                $data = [];
                foreach ($model as $items) {                    
                    $data[] = [
                        'id'=>$items->id,
                        'review'=>$items->review,
                        'reply_from'=>$items->reply_from,
                    ];
                }
                $this->code = 1; $this->msg = "Ok";
                $this->details = $data;
            } else $this->msg = t(HELPER_NO_RESULTS);

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }
    
    public function actionreviewDeleteReply()
    {
        try {
            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $id = intval(Yii::app()->input->post('id'));
            $model = AR_review::model()->find("id=:id",[
                ':id'=>$id
            ]);
            if($model){
                $model->delete();
                $this->code = 1; $this->msg = "Ok";                
            } else $this->msg = t(HELPER_NO_RESULTS);
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actionSortCategory()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $category = isset($this->data['category'])?$this->data['category']:'';
            if(is_array($category) && count($category)>=1){
                foreach ($category as $index => $cat_id) {
                    $model = AR_category_sort::model()->find("merchant_id=:merchant_id AND cat_id=:cat_id",[
						':merchant_id'=>intval($merchant_id),
						':cat_id'=>intval($cat_id)
					]);
					if($model){
						$model->sequence = $index;
						if(!$model->save()){							
						}
					}
                }
            }
            $this->code = 1;
            $this->msg = t(Helper_update);

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actionFPtestprint()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $printer_id = intval(Yii::app()->input->post('printer_id'));
            
            $model = AR_printer::model()->find("merchant_id=:merchant_id AND printer_id=:printer_id",[
                ":merchant_id"=>$merchant_id,
                ':printer_id'=>intval($printer_id)
            ]);
            if($model){
                $this->code = 1; $this->msg = "Ok";

                $printer_uuid='';                
                $printer_user='';
                $printer_ukey='';
                $printer_sn='';
                $printer_key='';

                if($model->printer_model=="feieyun"){
                    $meta = AR_printer_meta::getMeta($printer_id,['printer_user','printer_ukey','printer_sn','printer_key']);                                        
                    $printer_user = isset($meta['printer_user'])?$meta['printer_user']['meta_value1']:'';
                    $printer_ukey = isset($meta['printer_ukey'])?$meta['printer_ukey']['meta_value1']:'';
                    $printer_sn = isset($meta['printer_sn'])?$meta['printer_sn']['meta_value1']:'';
                    $printer_key = isset($meta['printer_key'])?$meta['printer_key']['meta_value1']:'';
                    
                    $tpl = FPinterface::TestTemplate($model->paper_width);    
                    $stime = time();
                    $sig = sha1($printer_user.$printer_ukey.$stime);				    
                    FPinterface::Print($printer_user,$stime,$sig,$printer_sn,$tpl);

                    $this->code = 1;
                    $this->msg = t("Request succesfully sent to printer");

                } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actionFPprint()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $printer_id = intval(Yii::app()->input->post('printer_id'));
            $order_uuid = trim(Yii::app()->input->post('order_uuid'));

            $model = AR_printer::model()->find("merchant_id=:merchant_id AND printer_id=:printer_id",[
                ":merchant_id"=>$merchant_id,
                ':printer_id'=>intval($printer_id)
            ]);
            if($model){

                $meta = AR_printer_meta::getMeta($printer_id,['printer_user','printer_ukey','printer_sn','printer_key']);                                                                        
                $printer_user = isset($meta['printer_user'])?$meta['printer_user']['meta_value1']:'';
                $printer_ukey = isset($meta['printer_ukey'])?$meta['printer_ukey']['meta_value1']:'';
                $printer_sn = isset($meta['printer_sn'])?$meta['printer_sn']['meta_value1']:'';
                $printer_key = isset($meta['printer_key'])?$meta['printer_key']['meta_value1']:'';
                
                $order_id = 0;
                $summary = array(); $order_status = array();                                
                $order_delivery_status = array(); $merchant_info=array();
                $order = array(); $items = array();

                COrders::getContent($order_uuid,Yii::app()->language);                

                $merchant_info = COrders::getMerchant($merchant_id,Yii::app()->language);
                $items = COrders::getItems();
                $summary = COrders::getSummary();
                $order = COrders::orderInfo();

                $tpl = FPtemplate::ReceiptTemplate(
                  $model->paper_width,
                  $order['order_info'],
                  $merchant_info,
                  $items,$summary
               );               
               $stime = time();
               $sig = sha1($printer_user.$printer_ukey.$stime);               
               $result = FPinterface::Print($printer_user,$stime,$sig,$printer_sn,$tpl);
               
               $this->code = 1;
               $this->msg = t("Request succesfully sent to printer");
               $this->details = $result;

            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }
    
    public function actionSortAddonCategory()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $addoncategory = isset($this->data['addoncategory'])?$this->data['addoncategory']:'';
            
            if(is_array($addoncategory) && count($addoncategory)>=1){
                foreach ($addoncategory as $index => $id) {
                    $model = AR_subcategory_sort::model()->find("merchant_id=:merchant_id AND subcat_id=:subcat_id",[
						':merchant_id'=>intval($merchant_id),
						':subcat_id'=>intval($id)
					]);
                    if($model){
                        $model->sequence = $index;
						if(!$model->save()){							
						}
                    }
                }
            }

            $this->code = 1;            
            $this->msg = t(Helper_update);
            CCacheData::add();

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }   

    public function actionSortAddonItems()
    {
        try {
            
            $addonitems = isset($this->data['addonitems'])?$this->data['addonitems']:'';            
            if(is_array($addonitems) && count($addonitems)>=1){
                foreach ($addonitems as $subcategory) {                    
                    $subcat_id = isset($subcategory['subcat_id'])?$subcategory['subcat_id']:'';
                    $items = isset($subcategory['items'])?$subcategory['items']:'';
                    if(is_array($items) && count($items)>=1){
                        foreach ($items as $index => $item_id) {
                            $model = AR_subcategory_item_relationships::model()->find("subcat_id=:subcat_id AND sub_item_id=:sub_item_id",[
								':subcat_id'=>$subcat_id,
								':sub_item_id'=>intval($item_id),								
							]);
                            if($model){
                                $model->sequence = $index;
								$model->save();
                            }
                        }
                    }
                }
            }

            $this->code = 1;            
            $this->msg = t(Helper_update);
            CCacheData::add();

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actionSortItems()
    {
        try {
            
            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $items = isset($this->data['items'])?$this->data['items']:'';            
            if(is_array($items) && count($items)>=1){
                foreach ($items as $category) {                    
                    $cat_id = isset($category['cat_id'])?$category['cat_id']:'';
                    $items = isset($category['items'])?$category['items']:'';
                    if(is_array($items) && count($items)>=1){
                        foreach ($items as $index => $item_id) {
                            $model = AR_item_relationship_category::model()->find("merchant_id=:merchant_id AND item_id=:item_id AND cat_id=:cat_id",[
								':merchant_id'=>$merchant_id,
								':item_id'=>intval($item_id),
								':cat_id'=>intval($cat_id),
							]);
                            if($model){                                
                                $model->sequence = $index;
								$model->save();
                            }
                        }
                    }
                }
            }

            $this->code = 1;            
            $this->msg = t(Helper_update);
            CCacheData::add();

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actiongetAddonSort()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $id = Yii::app()->input->post('id');
            $item_model = AR_item::model()->find("item_token=:item_token",[
                ':item_token'=>$id
            ]);
            if($item_model){                
                $item_id = $item_model->item_id;

                try {			
                    $addon_category = CDataFeed::subcategoryList($merchant_id,Yii::app()->language,'publish',true);			
                } catch (Exception $e) {
                    $addon_category = [];						
                }				
                try {
                    $size_list = CDataFeed::getAddoncategorySize($merchant_id,$item_id);
                } catch (Exception $e) {
                    $size_list = [];
                }
                try {
                    $size = CDataFeed::getSizeList($merchant_id);
                } catch (Exception $e) {
                    $size = [];
                }

                $this->code = 1; $this->msg = "Ok";
                $this->details = [
                    'addon_category'=>$addon_category,
                    'size_list'=>$size_list,
                    'size'=>$size
                ];

            } else $this->msg = t(Helper_not_found);
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actionSortAddonItemsSort()
    {
        try {
            
            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $id = isset($this->data['id'])?$this->data['id']:'';
            $items = isset($this->data['items'])?$this->data['items']:'';
            //dump($items);            
            $item_model = AR_item::model()->find("item_token=:item_token",[
                ':item_token'=>$id
            ]);
            if($item_model){
                $item_id = $item_model->item_id;                
                if(is_array($items) && count($items)>=1){
                    foreach ($items as $key => $item) {                        
                        $item_size_id = isset($item['item_size_id'])?$item['item_size_id']:'';
                        $addoncategory = isset($item['addoncategory'])?$item['addoncategory']:'';
                        if(is_array($addoncategory) && count($addoncategory)>=1){
                            foreach ($addoncategory as $index => $subcat_id) {                                 
                                $model = AR_item_addon::model()->find("merchant_id=:merchant_id AND item_id=:item_id AND item_size_id=:item_size_id
                                AND subcat_id=:subcat_id
                                ",[
                                    ':merchant_id'=>$merchant_id,
                                    ':item_id'=>intval($item_id),
                                    ':item_size_id'=>intval($item_size_id),
                                    ':subcat_id'=>intval($subcat_id),
                                ]);
                                if($model){
                                    $model->scenario="sort";
                                    $model->sequence = $index;
                                    $model->save();
                                }
                            }
                        }
                    }
                }

                $this->code = 1;            
                $this->msg = t(Helper_update);
                CCacheData::add();            
            } else $this->msg = t(Helper_not_found);

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actiongetgrouplist()
	{
		try {

			$self_delivery = isset(Yii::app()->params['settings_merchant']['self_delivery'])?Yii::app()->params['settings_merchant']['self_delivery']:false;
		    $self_delivery = $self_delivery==1?true:false;					

			$merchant_id = $self_delivery==true?Yii::app()->merchant->merchant_id:0;
			$where = "WHERE merchant_id=".q($merchant_id)." ";			

			$data = CommonUtility::getDataToDropDown("{{driver_group}}","group_id","group_name",$where,"order by group_name asc");
			$this->code = 1;
			$this->msg = "OK";
			$this->details = $data;
			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();	
	}	

    public function actiongetZoneList()
	{
		try {
			
			$self_delivery = isset(Yii::app()->params['settings_merchant']['self_delivery'])?Yii::app()->params['settings_merchant']['self_delivery']:false;
		    $self_delivery = $self_delivery==1?true:false;					

			$merchant_id = $self_delivery==true?Yii::app()->merchant->merchant_id:0;
			$where = "WHERE merchant_id=".q($merchant_id)." ";		

			$zone_list = CommonUtility::getDataToDropDown("{{zones}}",'zone_id','zone_name', $where ,"ORDER BY zone_name ASC"); 
			if($zone_list){
				$zone_list = CommonUtility::ArrayToLabelValue($zone_list);
				$this->code = 1;
				$this->msg = "Ok";
				$this->details = $zone_list;
			} else $this->msg = t(HELPER_NO_RESULTS);			

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();	
	}	

    public function actiongetAvailableDriver()
    {
        try {

            $on_demand_availability = isset(Yii::app()->params['settings'])? (isset(Yii::app()->params['settings']['driver_on_demand_availability'])?Yii::app()->params['settings']['driver_on_demand_availability']:false) :false;
			$on_demand_availability = $on_demand_availability==1?true:false;	
            
            $merchant_zone = CMerchants::getListMerchantZone([Yii::app()->merchant->merchant_id]);
			$merchant_zone = isset($merchant_zone[Yii::app()->merchant->merchant_id])?$merchant_zone[Yii::app()->merchant->merchant_id]:'';            
            
            $merchant_id = intval(Yii::app()->input->post("merchant_id"));
            $group_selected = intval(Yii::app()->input->post("group_selected"));
            $q = Yii::app()->input->post("q");
            $zone_id = intval(Yii::app()->input->post("zone_id"));            

            $self_delivery = isset(Yii::app()->params['settings_merchant']['self_delivery'])?Yii::app()->params['settings_merchant']['self_delivery']:false;
		    $self_delivery = $self_delivery==1?true:false;		            

            $merchant_id = $self_delivery==true?Yii::app()->merchant->merchant_id:$merchant_id;             


            $criteria=new CDbCriteria();
            $criteria->alias = "a";
            $criteria->select = "a.*";        
            if($group_selected>0){
                $criteria->join = "LEFT JOIN {{driver_group_relations}} b ON a.driver_id = b.driver_id";                
                $criteria->addCondition("b.group_id=:group_id");              
            } 

            $now = date("Y-m-d"); $and_zone = '';
            if($zone_id>0){
                $and_zone = "AND zone_id = ".q($zone_id)." ";
            }

            if(!$on_demand_availability){
                $criteria->addCondition("a.merchant_id=:merchant_id AND a.latitude !='' AND a.status=:status AND a.driver_id IN (
                    select driver_id from {{driver_schedule}}
                    where DATE(time_start)=".q($now)."
                    AND DATE(shift_time_started) IS NOT NULL  
                    AND DATE(shift_time_ended) IS NULL  
                    $and_zone                    
                )");            
            }

            if($group_selected>0){
                $criteria->params = [
                    ':merchant_id'=>intval($merchant_id),
                    ':group_id'=>$group_selected,
					':status'=>"active"
                ];
            } else {
                $criteria->params = [
                    ':merchant_id'=>intval($merchant_id),
					':status'=>"active"
                ];
            }   
            
            if(!empty($q) && !$on_demand_availability){
                $criteria->addSearchCondition('a.first_name', $q );
                $criteria->addSearchCondition('a.last_name', $q , true , 'OR' );            
            }                     
            
            	// ON DEMAND
			if($on_demand_availability){
				$and_merchant_zone = '';
				if(is_array($merchant_zone) && count($merchant_zone)>=1 || $zone_id>0){
					if($zone_id>0){
						$in_query = CommonUtility::arrayToQueryParameters([$zone_id]);
					} else $in_query = CommonUtility::arrayToQueryParameters($merchant_zone);				
					$and_merchant_zone = "
					AND a.driver_id IN (
						select driver_id from {{driver_schedule}}
						where 
						merchant_id=".q($merchant_id)." and driver_id = a.driver_id 
						and on_demand=1 and zone_id IN ($in_query)
					)
					";
				}
				$criteria->addCondition("a.merchant_id=:merchant_id AND a.is_online=:is_online AND a.status=:status AND a.latitude !='' $and_merchant_zone ");
				$criteria->params = [
					':merchant_id'=>intval($merchant_id),
					':is_online'=>1,
					':status'=>"active"
				];			
				if($group_selected>0){
					$criteria->params[':group_id']=$group_selected;
				}				
                if(!empty($q)){
                    $criteria->addSearchCondition('a.first_name', $q );
                    $criteria->addSearchCondition('a.last_name', $q , true , 'OR' );            
                }                     
			}

            $criteria->order = "a.first_name ASC";
            $criteria->limit = 20;      
            
            //dump($criteria);die();
            if($model = AR_driver::model()->findAll($criteria)){
                $data = array(); $driver_ids = [];
                foreach ($model as $items) {
					$photo = CMedia::getImage($items->photo,$items->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('customer'));
					$driver_ids[] = $items->driver_id; 
                    $data[] = [
                      'name'=>$items->first_name." ".$items->last_name,
                      'driver_id'=>$items->driver_id,
					  'photo_url'=>$photo,
					  'latitude'=>$items->latitude,
					  'longitude'=>$items->lontitude,
                      'selected'=>false
                    ];
                }			
                
                $active_task = CDriver::getCountActiveTaskAll($driver_ids,date("Y-m-d"));						
                $this->code  = 1;
                $this->msg = "OK";
                $this->details = [
					'data'=>$data,				
					'active_task'=>$active_task
				];
            } else $this->msg = t(HELPER_NO_RESULTS);
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actionAssignDriver()
    {
        try {
            
            $on_demand_availability = isset(Yii::app()->params['settings'])?Yii::app()->params['settings']['driver_on_demand_availability']:false;
			$on_demand_availability = $on_demand_availability==1?true:false;						

			$driver_id = intval(Yii::app()->input->post('driver_id'));
			$order_uuid = trim(Yii::app()->input->post('order_uuid'));

            $order = COrders::get($order_uuid);
			$driver = CDriver::getDriver($driver_id);

			$meta = AR_admin_meta::getValue('status_assigned');
            $status_assigned = isset($meta['meta_value'])?$meta['meta_value']:''; 
            
            $options = OptionsTools::find(['driver_allowed_number_task']);
            $allowed_number_task = isset($options['driver_allowed_number_task'])?$options['driver_allowed_number_task']:0;

			//$order->scenario = "delivery_change_status";
            $order->scenario = "assign_order";
            $order->on_demand_availability = $on_demand_availability;
            $order->driver_id = intval($driver_id);
            $order->delivered_old_status = $order->delivery_status;
            $order->delivery_status = $status_assigned;
            $order->change_by = Yii::app()->merchant->first_name;
            $order->date_now = date("Y-m-d");
            $order->allowed_number_task = intval($allowed_number_task);

            if(!$on_demand_availability){
                try {
                    $now = date("Y-m-d");                
                    $vehicle = CDriver::getVehicleAssign($driver_id,$now);
                    $order->vehicle_id = $vehicle->vehicle_id;
                } catch (Exception $e) {
                    $this->msg = t($e->getMessage());
                    $this->responseJson();	
                }      
            }                  

            if($order->save()){
                $this->code  = 1;
                $this->msg = t("Order assign to {driver_name}",[
					'{driver_name}'=>"$driver->first_name $driver->first_name"
				]);
            } else $this->msg = CommonUtility::parseModelErrorToString($order->getErrors());

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
    }

    public function actiongetPaymentHistory()
    {
        try {

            $order_uuid = Yii::app()->input->post('order_uuid');		
            $model = COrders::get($order_uuid );
            if($data = COrders::paymentHistory($model->order_id)){
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

    public function actioncancelOrder()
	{		
		try {
												
			$model = AR_admin_meta::model()->find('meta_name=:meta_name', 
			  array(':meta_name'=>'status_cancel_order')
			);			
			if($model){				
				$status_cancelled = $model->meta_value ;
			} else $status_cancelled = 'cancelled';

					
			$reason = Yii::app()->input->post('reason');
			$order_uuid = Yii::app()->input->post('order_uuid');
			$model = COrders::get($order_uuid);	
			$model->scenario = "cancel_order";
			
			if($model->status==$status_cancelled){
				$this->msg = t("Order has the same status");
				$this->responseJson();
			}
							
			$model->status = $status_cancelled;
			$model->remarks = $reason;
			
			if($model->save()){
			   $this->code = 1;
			   $this->msg = t("Order is cancelled");			   
			   if(!empty($reason)){
			   	  COrders::savedMeta($model->order_id,'rejetion_reason',$reason);
			   }			   
			} else $this->msg = CommonUtility::parseError( $model->getErrors());
			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();
	}

    public function actionreservationList()
    {
        try {

            $page = intval(Yii::app()->input->post('page'));            
            $search = trim(Yii::app()->input->post('q')); 
            $length =Yii::app()->params->list_limit;

            $sortby = "reservation_id"; $sort = 'DESC';
            
            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
				$page = $page-1;
			}

            $page = intval($page)/intval($length);		
            $criteria=new CDbCriteria();
            $criteria->alias = "a";
            $criteria->select = "a.*,
            concat(b.first_name,' ',b.last_name) as full_name,
            c.table_name
            ";
            $criteria->join='
            LEFT JOIN {{client}} b on  a.client_id = b.client_id 
            LEFT JOIN {{table_tables}} c on  a.table_id = c.table_id 
            ';
            $criteria->condition = "a.merchant_id=:merchant_id";
            $criteria->params  = array(
            ':merchant_id'=>intval(Yii::app()->merchant->merchant_id),		  
            );

            if(!empty($search)){                                
                if($customer_id = ACustomer::searchByName($search)){                    
                    $criteria->addInCondition("a.client_id",$customer_id);
                } else {
                    $criteria->addSearchCondition('reservation_id', $search ); 
                }
            }

            $criteria->order = "$sortby $sort";
            $count = AR_table_reservation::model()->count($criteria); 
            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );        
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);    
            $page_count = $pages->getPageCount();            
            
            if($page>0){
				if($page_raw>$page_count){
					$this->code = 3;
					$this->msg = t("end of results");
					$this->responseJson();
				}
			}

            $data = [];
            $status_list = AttributesTools::bookingStatus();
            
            if($model = AR_table_reservation::model()->findAll($criteria)){                
                foreach ($model as $items) {
                    $data[] = [
                        'reservation_id'=>$items->reservation_id,
                        'reservation_uuid'=>$items->reservation_uuid,
                        'full_name'=>$items->full_name,
                        'guest_number'=>$items->guest_number,
                        'table_id'=>$items->table_name,
                        'reservation_date'=>Date_Formatter::dateTime($items->reservation_date." ".$items->reservation_time),
                        'reservation_date_raw'=>[
                            'day'=>date("d",strtotime($items->reservation_date)),
                            'month'=>date("M",strtotime($items->reservation_date)),
                            'year'=>date("Y",strtotime($items->reservation_date)),
                            'dayname'=>date("l",strtotime($items->reservation_date)),
                            'time'=>date("h:i a",strtotime($items->reservation_time)),
                        ],
                        'cancellation_reason'=>$items->cancellation_reason,
                        'status'=>isset($status_list[$items->status])?$status_list[$items->status]:$items->status,
                    ];
                }
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'data'=>$data,
                    'status_list'=>$status_list
                ];
            } else $this->msg = t(HELPER_NO_RESULTS);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();     
    }

    public function actionreservationSummary()
    {
        try {
            
            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            try {
                $summary = CBooking::reservationSummary($merchant_id,date("Y-m-d"));
            } catch (Exception $e) {}

            $new_summary['total_upcoming'] = [
                'label'=>t("Upcoming"),
                'value'=>isset($summary['total_upcoming'])?$summary['total_upcoming']:0,
                'color'=>'#49c3a1'
            ];
            $new_summary['total_reservation'] = [
                'label'=>t("Total"),
                'value'=>isset($summary['total_reservation'])?$summary['total_reservation']:0,
                'color'=>'#9689e7'
            ];
            $new_summary['total_denied'] = [
                'label'=>t("Denied"),
                'value'=>isset($summary['total_denied'])?$summary['total_denied']:0,
                'color'=>'#fab54d'
            ];
            $new_summary['total_cancelled'] = [
                'label'=>t("Cancelled"),
                'value'=>isset($summary['total_cancelled'])?$summary['total_cancelled']:0,
                'color'=>'#c3b5d3'
            ];
            $new_summary['total_noshow'] = [
                'label'=>t("No show"),
                'value'=>isset($summary['total_noshow'])?$summary['total_noshow']:0,
                'color'=>'#e99a9e'
            ];
            $new_summary['total_waitlist'] = [
                'label'=>t("Wait List"),
                'value'=>isset($summary['total_waitlist'])?$summary['total_waitlist']:0,
                'color'=>'#45adc9'
            ];

            $this->code = 1;
            $this->msg = "Ok";
            $this->details = [
                'data'=>$new_summary
            ];
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();     
    }

    public function actiongetBookingDetails()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $id = Yii::app()->input->post('id');                        
            $payload = Yii::app()->input->post('payload');             

            $data = CBooking::getBookingDetails($id);
            $client_id = isset($data['client_id'])?$data['client_id']:0;
            $room_names = CommonUtility::getDataToDropDown("{{table_room}}","room_id","room_name","WHERE merchant_id=".q($merchant_id)." ");
			$table_names = CommonUtility::getDataToDropDown("{{table_tables}}","table_id","table_name","WHERE merchant_id=".q($merchant_id)." ");
            
            $status_list = []; $customer_list = [];
            if($payload=="update"){
                $table_names = CommonUtility::ArrayToLabelValue($table_names);
                $status_list = AttributesTools::bookingStatus();
                $status_list = CommonUtility::ArrayToLabelValue($status_list);
                
                $model_customer = ACustomer::get($client_id);
                $customer_list[] = [
                    'value'=>$client_id,
                    'label'=>$model_customer->first_name." ".$model_customer->last_name
                ];
            }
            
            $reservation_id = isset($data['reservation_id'])?$data['reservation_id']:0;

            try {
                $timeline = CBooking::getTimeline($reservation_id);
            } catch (Exception $e) {
                $timeline = [];
            }            

            $customer_data = [];
            try {
                $customer = ACustomer::get($client_id);
                $customer_data['first_name'] = $customer->first_name;
                $customer_data['last_name'] = $customer->last_name;
                $customer_data['email_address'] = $customer->email_address;
                $customer_data['contact_phone'] = $customer->contact_phone;
                $customer_data['avatar']=CMedia::getImage($customer->avatar,$customer->path,Yii::app()->params->size_image,CommonUtility::getPlaceholderPhoto('customer'));                
            } catch (Exception $e) {}            

            $this->code = 1; $this->msg = "Ok";

            $data['reservation_date_raw'] = isset($data['reservation_date_raw'])? date("Y/m/d",strtotime($data['reservation_date_raw'])):'';
            $data['reservation_time_raw'] = isset($data['reservation_time_raw'])? date("G:i",strtotime($data['reservation_time_raw'])):'';
            $this->details = [
                'customer_data'=>$customer_data,
                'data'=>$data,
                'room_names'=>$room_names,
                'table_names'=>$table_names,
                'timeline'=>$timeline,
                'status_list'=>$status_list,
                'customer_list'=>$customer_list
            ];

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();     
    }

    public function actionBookingCustomerSummary()
    {
        try {
            
            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $id = Yii::app()->input->post('id');     
            $model = CBooking::get($id);                   
            $summary = CBooking::customerSummary($model->client_id,$merchant_id,date("Y-m-d"));
            $new_summary = [];

            $new_summary['total_upcoming'] = [
                'label'=>t("Upcoming"),
                'value'=>isset($summary['total_upcoming'])?$summary['total_upcoming']:0,
                'color'=>'#49c3a1'
            ];
            $new_summary['total_reservation'] = [
                'label'=>t("Total"),
                'value'=>isset($summary['total_reservation'])?$summary['total_reservation']:0,
                'color'=>'#9689e7'
            ];
            $new_summary['total_denied'] = [
                'label'=>t("Denied"),
                'value'=>isset($summary['total_denied'])?$summary['total_denied']:0,
                'color'=>'#fab54d'
            ];
            $new_summary['total_cancelled'] = [
                'label'=>t("Cancelled"),
                'value'=>isset($summary['total_cancelled'])?$summary['total_cancelled']:0,
                'color'=>'#c3b5d3'
            ];
            $new_summary['total_noshow'] = [
                'label'=>t("No show"),
                'value'=>isset($summary['total_noshow'])?$summary['total_noshow']:0,
                'color'=>'#e99a9e'
            ];
            $new_summary['total_waitlist'] = [
                'label'=>t("Wait List"),
                'value'=>isset($summary['total_waitlist'])?$summary['total_waitlist']:0,
                'color'=>'#45adc9'
            ];

            $this->code = 1; $this->msg = "OK";
            $this->details = [
                'data'=>$new_summary
            ];
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();     
    }

    public function actionCustomerReservationList()
    {
        try {

            $id = Yii::app()->input->post('id');                 
            $model = CBooking::get($id);    
            
            $page = intval(Yii::app()->input->post('page'));            
            $length =Yii::app()->params->list_limit;

            $sortby = "reservation_id"; $sort = 'DESC';
            
            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
				$page = $page-1;
			}

            $page = intval($page)/intval($length);		
            $criteria=new CDbCriteria();
            $criteria->alias = "a";
            $criteria->select = "a.*,
            concat(b.first_name,' ',b.last_name) as full_name,
            c.table_name
            ";
            $criteria->join='
            LEFT JOIN {{client}} b on  a.client_id = b.client_id 
            LEFT JOIN {{table_tables}} c on  a.table_id = c.table_id 
            ';
            $criteria->condition = "a.client_id = :client_id AND a.merchant_id=:merchant_id";
            $criteria->params  = array(
               ':client_id'=>intval($model->client_id),  
               ':merchant_id'=>intval(Yii::app()->merchant->merchant_id),  
            );

            $criteria->order = "$sortby $sort";
            $count = AR_table_reservation::model()->count($criteria); 
            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );        
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);    
            $page_count = $pages->getPageCount();
            
            if($page>0){
				if($page_raw>$page_count){
					$this->code = 3;
					$this->msg = t("end of results");
					$this->responseJson();
				}
			}

            $data = [];
            $status_list = AttributesTools::bookingStatus();
            
            if($model = AR_table_reservation::model()->findAll($criteria)){                
                foreach ($model as $items) {
                    $data[] = [
                        'reservation_id'=>$items->reservation_id,
                        'reservation_uuid'=>$items->reservation_uuid,
                        'full_name'=>$items->full_name,
                        'guest_number'=>$items->guest_number,
                        'table_id'=>$items->table_name,
                        'reservation_date'=>Date_Formatter::dateTime($items->reservation_date." ".$items->reservation_time),
                        'reservation_date_raw'=>[
                            'day'=>date("d",strtotime($items->reservation_date)),
                            'month'=>date("M",strtotime($items->reservation_date)),
                            'year'=>date("Y",strtotime($items->reservation_date)),
                            'dayname'=>date("l",strtotime($items->reservation_date)),
                            'time'=>date("h:i a",strtotime($items->reservation_time)),
                        ],
                        'cancellation_reason'=>$items->cancellation_reason,
                        'status'=>$items->status
                    ];
                }
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'data'=>$data,
                    'status_list'=>$status_list
                ];
            } else $this->msg = t(HELPER_NO_RESULTS);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();     
    }

    public function actionUpdateBookingStatus()
    {
        try {

            $id = Yii::app()->input->post('id');
            $status = Yii::app()->input->post('status');                 

            $model = CBooking::get($id);         
            $model->is_update_frontend = false;
            $model->status = $status;
            $model->change_by = Yii::app()->merchant->first_name;
            if($model->save()){
                $this->code = 1;
                $this->msg = t("Booking status updated");
            } else $this->msg = CommonUtility::parseError( $model->getErrors() );

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionSearchCustomer()
    {
        try {

            $search = Yii::app()->input->post('q');
            
            $is_pos = Yii::app()->input->post('POS');
            $is_pos = $is_pos==1?true:false;
                    
            if($is_pos && empty($search)){
                $data[] = array(
                'value'=>"walkin",
                'label'=>t("Walk-in Customer")
                );
            } else $data = array();    	 

            $criteria=new CDbCriteria();
            $criteria->select = "client_id,first_name,last_name";
            $criteria->condition = "status=:status";
            $criteria->params = array(
            ':status'=>'active'
            );

            if($is_pos){
                $criteria->addNotInCondition("social_strategy",['guest','booking']);
            }            

            if(!empty($search)){
                $criteria->addSearchCondition('first_name', $search );
                $criteria->addSearchCondition('last_name', $search , true , 'OR' );
            }
            $criteria->limit = 10;
            
            if($models = AR_client::model()->findAll($criteria)){		 	
                foreach ($models as $val) {
                    $data[]=array(
                     'value'=>$val->client_id,
                     'label'=>$val->first_name." ".$val->last_name
                   );
                }
            }

            $this->code = 1;
            $this->msg = "Ok";
            $this->details = [
                'data'=>$data
            ];

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionUpdateBooking()
    {
        $this->actionAddBooking(true);
    }

    public function actionAddBooking($update=false)
    {
        try {
            
            $id = isset($this->data['id'])?$this->data['id']:'';
            $merchant_id = Yii::app()->merchant->merchant_id;

            $model = new AR_table_reservation();
            if($update){
                $model = CBooking::get($id);         
            }            
            
            $reservation_time = isset($this->data['reservation_time'])?  Date_Formatter::TimeTo24($this->data['reservation_time']) :'';

            $model->merchant_id = $merchant_id;
            $model->client_id = isset($this->data['client_id'])?$this->data['client_id']:0;
            $model->reservation_date = isset($this->data['reservation_date'])?$this->data['reservation_date']:'';
            $model->guest_number = isset($this->data['guest_number'])? intval($this->data['guest_number']) :0;
            $model->reservation_time = $reservation_time;
            $model->table_id = isset($this->data['table_id'])? intval($this->data['table_id']) :0;
            $model->special_request = isset($this->data['special_request'])? trim($this->data['special_request']) :'';
            $model->status = isset($this->data['status'])? trim($this->data['status']) :'pending';
            
            if($model->save()){
                $this->code = 1;
                $this->msg = $update? t("Booking status updated") : t("Booking saved");
                $this->details = [
                    'id'=>$model->reservation_uuid
                ];
            } else $this->msg = CommonUtility::parseError( $model->getErrors() );
            
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actiondeleteBooking(){
        try {
            
            $id = Yii::app()->input->post('id');
            $model = CBooking::get($id);    
            $model->delete();
            $this->code = 1;
            $this->msg = t("Booking deleted");

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionBookingSettings()
    {
        try {

            $options = [
                'booking_enabled','booking_enabled_capcha','booking_allowed_choose_table','booking_reservation_custom_message',
                'booking_reservation_terms'
            ];
            $merchant_id = Yii::app()->merchant->merchant_id;
            $data = OptionsTools::find($options,$merchant_id);
            
            $new_data = [];
            $new_data['booking_enabled'] = isset($data['booking_enabled'])? ($data['booking_enabled']==1?true:false) : false;
            $new_data['booking_enabled_capcha'] = isset($data['booking_enabled_capcha'])? ($data['booking_enabled_capcha']==1?true:false) : false;
            $new_data['booking_allowed_choose_table'] = isset($data['booking_allowed_choose_table'])? ($data['booking_allowed_choose_table']==1?true:false) : false;
            $new_data['booking_reservation_custom_message'] = isset($data['booking_reservation_custom_message'])?$data['booking_reservation_custom_message']:'';
            $new_data['booking_reservation_terms'] = isset($data['booking_reservation_terms'])?$data['booking_reservation_terms']:'';

            $this->code = 1;
            $this->msg = "Ok";
            $this->details = [
                'data'=>$new_data
            ];
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionSaveBookingSettings()
    {
        try {

            $merchant_id = Yii::app()->merchant->merchant_id;
            $options = [
                'booking_enabled','booking_enabled_capcha','booking_allowed_choose_table','booking_reservation_custom_message',
                'booking_reservation_terms'
            ];          
            
            $model=new AR_option;
            $model->booking_enabled = isset($this->data['booking_enabled'])?$this->data['booking_enabled']:0;
            $model->booking_enabled_capcha = isset($this->data['booking_enabled_capcha'])?$this->data['booking_enabled_capcha']:0;
            $model->booking_allowed_choose_table = isset($this->data['booking_allowed_choose_table'])?$this->data['booking_allowed_choose_table']:0;
            $model->booking_reservation_custom_message = isset($this->data['booking_reservation_custom_message'])?$this->data['booking_reservation_custom_message']:'';
            $model->booking_reservation_terms = isset($this->data['booking_reservation_terms'])?$this->data['booking_reservation_terms']:'';

            OptionsTools::$merchant_id = $merchant_id;
            if(OptionsTools::save($options, $model, $merchant_id)){
                $this->code = 1;
                $this->msg = CommonUtility::t(Helper_settings_saved);
            } else  $this->msg = t(Helper_failed_update);
            
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actiontableShift()
    {
        try {
            $page = intval(Yii::app()->input->post('page'));
            $search = trim(Yii::app()->input->post('q'));             
            $length =Yii::app()->params->list_limit;

            $sortby = "shift_id"; $sort = 'DESC';
            
            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
                $page = $page-1;
            }

            $page = intval($page)/intval($length);
            $criteria=new CDbCriteria();
            $criteria->alias = "a";
            $criteria->select = "a.*		
            ";
            $criteria->condition = "merchant_id=:merchant_id";
            $criteria->params  = array(
              ':merchant_id'=>intval(Yii::app()->merchant->merchant_id),		  
            );

            if(!empty($search)){
                $criteria->addSearchCondition('a.shift_name', $search ); 
            }

            $criteria->order = "$sortby $sort";
            $count = AR_table_shift::model()->count($criteria); 
            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );        
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);    
            $page_count = $pages->getPageCount();

            if($page>0){
                if($page_raw>$page_count){
                    $this->code = 3;
                    $this->msg = t("end of results");
                    $this->responseJson();
                }
            }

            $data = [];            

            if($model = AR_table_shift::model()->findAll($criteria)){ 
                $intervals = AttributesTools::timeInvertvalue();
                $status_list = AttributesTools::StatusManagement('post',Yii::app()->language);                
                foreach ($model as $items) {
                    $data[] = [
                       'shift_id'=>$items->shift_id,
                       'shift_uuid'=>$items->shift_uuid,
					   'shift_name'=>$items->shift_name,
                       'first_seating'=>Date_Formatter::Time($items->first_seating)." - ".Date_Formatter::Time($items->last_seating),
                       'shift_interval'=>isset($intervals[$items->shift_interval])?$intervals[$items->shift_interval]:$items->shift_interval,
                       'status'=>isset($status_list[$items->status])?$status_list[$items->status]:$items->status,
                    ];
                }
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'data'=>$data,
                    'status_list'=>$status_list
                ];
            } else $this->msg = t(HELPER_NO_RESULTS);

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }	
        $this->responseJson();     
    }

    public function actionUpdateShift(){
        $this->actionCreateShift(true);
    }

    public function actionCreateShift($update=false)
    {
        try {

            $id = isset($this->data['id'])?$this->data['id']:'';
            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $model=new AR_table_shift;

            if($update){
                $model = AR_table_shift::model()->find('merchant_id=:merchant_id AND shift_uuid=:shift_uuid', 
                array(':merchant_id'=>$merchant_id, ':shift_uuid'=>$id ));			
                if(!$model){
                    $this->msg = t(HELPER_RECORD_NOT_FOUND);
                    $this->responseJson();  
                }
            }

            $model->merchant_id = $merchant_id;
            $model->days_of_week = isset($this->data['days_of_week'])?$this->data['days_of_week']:'';
            $model->shift_name = isset($this->data['shift_name'])?$this->data['shift_name']:'';
            $model->first_seating = isset($this->data['first_seating'])?$this->data['first_seating']:'';
            $model->last_seating = isset($this->data['last_seating'])?$this->data['last_seating']:'';
            $model->shift_interval = isset($this->data['shift_interval'])?$this->data['shift_interval']:'';
            $model->status = isset($this->data['status'])?$this->data['status']:'';
            if($model->validate()){
                if($model->save()){
                    if(!$update){
                        $this->msg = CommonUtility::t(Helper_success);
                    } else {
                        $this->msg = CommonUtility::t(Helper_update);
                    }
                    $this->code = 1; 
                } else $this->msg = CommonUtility::parseError( $model->getErrors() );
            } else $this->msg = CommonUtility::parseError( $model->getErrors() );
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }	
        $this->responseJson();     
    }

    public function actiondeleteShift()
    {
        try {

            $id = trim(Yii::app()->input->post('id'));            
            $merchant_id = (integer) Yii::app()->merchant->merchant_id;

            $model = AR_table_shift::model()->find("merchant_id=:merchant_id AND shift_uuid=:shift_uuid",array(		  
                ':merchant_id'=>$merchant_id,
                ':shift_uuid'=>$id
            ));		
            if($model){
                $model->delete(); 
                $this->code = 1;
                $this->msg = "Ok";
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }	
        $this->responseJson();     
    }

    public function actiongetShift()
    {
        try {

            $id = trim(Yii::app()->input->post('id'));             
            $model = AR_table_shift::model()->find("shift_uuid=:shift_uuid",[
                ':shift_uuid'=>$id
            ]);
            if($model){                
                $days_of_week_new = [];
                $days_of_week = !empty($model->days_of_week)?json_decode($model->days_of_week,true):'';
                if(is_array($days_of_week) && count($days_of_week)>=1){
                    foreach ($days_of_week as $key => $items) {
                        $days_of_week_new[]=$items;
                    }
                }
                $data  = [
                    'shift_uuid'=>$model->shift_uuid,
                    'shift_name'=>$model->shift_name,
                    'days_of_week'=>$days_of_week_new,
                    'first_seating'=>date("G:i",strtotime($model->first_seating)),
                    'last_seating'=>date("G:i",strtotime($model->last_seating)),
                    'shift_interval'=>$model->shift_interval,
                    'status'=>$model->status,
                ];
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = $data;
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }	
        $this->responseJson();     
    }

    public function actiontableRoomList()
    {
        try {
            $page = intval(Yii::app()->input->post('page'));  
            $search = trim(Yii::app()->input->post('q'));           
            $length =Yii::app()->params->list_limit;

            $sortby = "room_id"; $sort = 'DESC';
            
            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
                $page = $page-1;
            }

            $page = intval($page)/intval($length);
            $criteria=new CDbCriteria();            
            $criteria->alias = "a";
            $criteria->select = "a.*,
            (
                select count(*) from {{table_tables}}
                where room_id = a.room_id
                and available=1
            ) as total_tables,
            (
                select concat(sum(min_covers),' - ',sum(max_covers))
                from {{table_tables}}
                where room_id = a.room_id
                and available=1
            ) as capacity
            ";
            $criteria->condition = "merchant_id=:merchant_id";
            $criteria->params  = array(
              ':merchant_id'=>intval(Yii::app()->merchant->merchant_id),		  
            );

            if(!empty($search)){
                $criteria->addSearchCondition('a.room_name', $search ); 
            }

            $criteria->order = "$sortby $sort";
            $count = AR_table_room::model()->count($criteria); 
            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );        
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);    
            $page_count = $pages->getPageCount();

            if($page>0){
                if($page_raw>$page_count){
                    $this->code = 3;
                    $this->msg = t("end of results");
                    $this->responseJson();
                }
            }

            $data = [];
                        
            if($model = AR_table_room::model()->findAll($criteria)){ 
                $status_list = AttributesTools::StatusManagement('post',Yii::app()->language); 
                foreach ($model as $items) {
                    $data[] = [
                        'room_id'=>$items->room_id,
                        'room_uuid'=>$items->room_uuid,
                        'room_name'=>$items->room_name,
                        'capacity'=>$items->capacity,
                        'total_tables'=>$items->total_tables,                        
                        'status'=>isset($status_list[$items->status])?$status_list[$items->status]:$items->status,
                        'date_created'=>Date_Formatter::dateTime($items->date_created)
                    ];
                }
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'data'=>$data,                  
                ];
            } else $this->msg = t(HELPER_NO_RESULTS);

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }	
        $this->responseJson();     
    }

    public function actiondeleteRooms()
    {
        try {
            
            $id = trim(Yii::app()->input->post('id'));            
            $merchant_id = (integer) Yii::app()->merchant->merchant_id;

            $model = AR_table_room::model()->find("merchant_id=:merchant_id AND room_uuid=:room_uuid",array(		  
                ':merchant_id'=>$merchant_id,
                ':room_uuid'=>$id
            ));		
            if($model){
                $model->delete(); 
                $this->code = 1;
                $this->msg = "Ok";
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }	
        $this->responseJson();   
    }

    public function actionUpdateRoom()
    {
       $this->actionCreateRoom(true);
    }

    public function actionCreateRoom($update=false)
    {
        try {

            $id = isset($this->data['id'])?$this->data['id']:'';
            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $model=new AR_table_room();

            if($update){
                $model = AR_table_room::model()->find('merchant_id=:merchant_id AND room_uuid=:room_uuid', 
                array(':merchant_id'=>$merchant_id, ':room_uuid'=>$id ));			
                if(!$model){
                    $this->msg = t(HELPER_RECORD_NOT_FOUND);
                    $this->responseJson();  
                }
            }

            $model->merchant_id = $merchant_id;
            $model->room_name = isset($this->data['room_name'])?$this->data['room_name']:'';
            $model->status = isset($this->data['status'])?$this->data['status']:'';

            if($model->validate()){
                if($model->save()){
                    if(!$update){
                        $this->msg = CommonUtility::t(Helper_success);
                    } else {
                        $this->msg = CommonUtility::t(Helper_update);
                    }
                    $this->code = 1; 
                } else $this->msg = CommonUtility::parseError( $model->getErrors() );
            } else $this->msg = CommonUtility::parseError( $model->getErrors() );

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }	
        $this->responseJson();   
    }

    public function actiongetRoom()
    {
        try {

            $id = Yii::app()->input->post('id');            
            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $model = AR_table_room::model()->find("room_uuid=:room_uuid AND merchant_id=:merchant_id",[
                ':room_uuid'=>$id,
                ':merchant_id'=>$merchant_id
            ]);
            if($model){
                $this->code = 1; $this->msg = "Ok";
                $this->details = [
                    'room_uuid'=>$model->room_uuid,
                    'room_name'=>$model->room_name,
                    'status'=>$model->status,
                ];
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }	
        $this->responseJson();   
    }    

    public function actiontableList()
    {
        try {
            $page = intval(Yii::app()->input->post('page'));   
            $search = trim(Yii::app()->input->post('q'));          
            $length =Yii::app()->params->list_limit;

            $sortby = "table_id"; $sort = 'DESC';
            
            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
                $page = $page-1;
            }

            $page = intval($page)/intval($length);
            $criteria=new CDbCriteria();
            $criteria->alias = "a";
            $criteria->select = "a.*, b.room_name";
            $criteria->condition = "a.merchant_id=:merchant_id";
            $criteria->params  = array(
              ':merchant_id'=>intval(Yii::app()->merchant->merchant_id),		  
            );		
            $criteria->join='LEFT JOIN {{table_room}} b on  a.room_id=b.room_id';

            if(!empty($search)){ 
                $criteria->addSearchCondition('a.table_name', $search ); 
            }

            $criteria->order = "$sortby $sort";
            $count = AR_table_tables::model()->count($criteria); 
            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );        
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);    
            $page_count = $pages->getPageCount();

            if($page>0){
                if($page_raw>$page_count){
                    $this->code = 3;
                    $this->msg = t("end of results");
                    $this->responseJson();
                }
            }

            $data = [];
                        
            if($model = AR_table_tables::model()->findAll($criteria)){ 
                foreach ($model as $items) {
                    $data[] = [
                        'table_id'=>$items->table_id,
                        'table_uuid'=>$items->table_uuid,
                        'room_id'=>$items->room_id,
                        'table_name'=>$items->table_name,
                        'min_covers'=>$items->min_covers,
                        'max_covers'=>$items->max_covers,                        
                        'available_raw'=>$items->available,
                        'available'=>$items->available==1?t("Yes"):t("No"),
                        'date_created'=>Date_Formatter::dateTime($items->date_created)
                    ];
                }
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'data'=>$data,                  
                ];
            } else $this->msg = t(HELPER_NO_RESULTS);

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }	
        $this->responseJson();     
    }    

    public function actiondeleteTable()
    {
        try {
            
            $id = trim(Yii::app()->input->post('id'));            
            $merchant_id = (integer) Yii::app()->merchant->merchant_id;

            $model = AR_table_tables::model()->find("merchant_id=:merchant_id AND table_uuid=:table_uuid",array(		  
                ':merchant_id'=>$merchant_id,
                ':table_uuid'=>$id
            ));		
            if($model){
                $model->delete(); 
                $this->code = 1;
                $this->msg = "Ok";
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }	
        $this->responseJson();   
    }

    public function actionsearchTableroom()
    {
        try {

            $merchant_id = (integer) Yii::app()->merchant->merchant_id;
            $model = AR_table_room::model()->findAll("merchant_id=:merchant_id",[
                ':merchant_id'=>$merchant_id
            ]);
            if($model){
                $data = []; $data2 = [];
                foreach ($model as $key => $items) {
                    $data2[$items->room_id] = $items->room_name;
                    $data[] = [
                        'value'=>intval($items->room_id),
                        'label'=>$items->room_name,                        
                    ];
                }
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'data'=>$data,
                    'data2'=>$data2,
                ];
            } else $this->msg = t(HELPER_NO_RESULTS);
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }	
        $this->responseJson();   
    }

    public function actionUpdateTable()
    {
        $this->actionCreateTable(true);
    }

    public function actionCreateTable($update=false)
    {
        try {

            $id = isset($this->data['id'])?$this->data['id']:'';
            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $model=new AR_table_tables();

            if($update){
                $model = AR_table_tables::model()->find('merchant_id=:merchant_id AND table_uuid=:table_uuid', 
                array(':merchant_id'=>$merchant_id, ':table_uuid'=>$id ));			
                if(!$model){
                    $this->msg = t(HELPER_RECORD_NOT_FOUND);
                    $this->responseJson();  
                }
            }

            $model->merchant_id = $merchant_id;
            $model->table_name = isset($this->data['table_name'])?$this->data['table_name']:'';
            $model->room_id = isset($this->data['room_id'])?intval($this->data['room_id']):0;
            $model->min_covers = isset($this->data['min_covers'])?intval($this->data['min_covers']):0;
            $model->max_covers = isset($this->data['max_covers'])?intval($this->data['max_covers']):0;
            $model->available = isset($this->data['available'])?intval($this->data['available']):0;            

            if($model->validate()){
                if($model->save()){
                    if(!$update){
                        $this->msg = CommonUtility::t(Helper_success);
                    } else {
                        $this->msg = CommonUtility::t(Helper_update);
                    }
                    $this->code = 1; 
                } else $this->msg = CommonUtility::parseError( $model->getErrors() );
            } else $this->msg = CommonUtility::parseError( $model->getErrors() );


        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }	
        $this->responseJson();   
    }

    public function actiongetTable()
    {
        try {

            $id = Yii::app()->input->post('id');            
            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $model = AR_table_tables::model()->find("table_uuid=:table_uuid AND merchant_id=:merchant_id",[
                ':table_uuid'=>$id,
                ':merchant_id'=>$merchant_id
            ]);
            if($model){
                $this->code = 1; $this->msg = "Ok";
                $this->details = [
                    'table_uuid'=>$model->table_uuid,
                    'table_name'=>$model->table_name,
                    'room_id'=>$model->room_id,
                    'min_covers'=>$model->min_covers,
                    'max_covers'=>$model->max_covers,
                    'available'=>$model->available,
                ];
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }	
        $this->responseJson();   
    }

    public function actiongetTableList()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $table_names = CommonUtility::getDataToDropDown("{{table_tables}}","table_id","table_name","WHERE merchant_id=".q($merchant_id)." ");
            $table_names = CommonUtility::ArrayToLabelValue($table_names);
            $this->code = 1;
            $this->msg = "Ok";
            $this->details = [
                'data'=>$table_names
            ];
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }	
        $this->responseJson();   
    }

    public function actionCategoryList()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $category = CMerchantMenu::getCategory($merchant_id,Yii::app()->language);	
            $this->code = 1; $this->msg = "Ok";
            $this->details = [
                'data'=>$category
            ];
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }	
        $this->responseJson();   
    }

    public function actioncategoryItems()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $cat_id = Yii::app()->input->post('cat_id');            
            $items = CMerchantMenu::getCategoryItems($cat_id,$merchant_id,Yii::app()->language);            
            $this->code = 1; $this->msg = "Ok";
            $this->details = [
                'data'=>$items
            ];            
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }	
        $this->responseJson();   
    }

    public function actiongetMenuItem()
    {
        try {

            $merchant_id = Yii::app()->merchant->merchant_id;
		    $item_uuid = Yii::app()->input->post('item_uuid');
		    $cat_id = intval(Yii::app()->input->post('cat_id'));

            $items = CMerchantMenu::getMenuItem($merchant_id,$cat_id,$item_uuid,Yii::app()->language);
			$addons = CMerchantMenu::getItemAddonCategory($merchant_id,$item_uuid,Yii::app()->language);
			$addon_items = CMerchantMenu::getAddonItems($merchant_id,$item_uuid,Yii::app()->language);	
			$meta = CMerchantMenu::getItemMeta($merchant_id,$item_uuid);
			$meta_details = CMerchantMenu::getMeta($merchant_id,$item_uuid,Yii::app()->language);	

            $items_not_available = CMerchantMenu::getItemAvailability($merchant_id,date("w"),date("H:h:i"));
			$category_not_available = CMerchantMenu::getCategoryAvailability($merchant_id,date("w"),date("H:h:i"));

            $data = array(
                'items'=>$items,
                'addons'=>$addons,
                'addon_items'=>$addon_items,
                'meta'=>$meta,
                'meta_details'=>$meta_details,
                'items_not_available'=>$items_not_available,
                'category_not_available'=>$category_not_available
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
			  'config'=>$config
		    );            
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }	
        $this->responseJson();   
    }

    public function actionaddCartItems()
    {
        try {

            $merchant_id = Yii::app()->merchant->merchant_id;
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

            CCart::add($items);
										  
			CCart::savedAttributes($cart_uuid,Yii::app()->params->local_transtype,$transaction_type);			
					  
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
        }	
        $this->responseJson();   
    }

    public function actiongetCart()
    {
        try {
            
            $cart_uuid = isset($this->data['cart_uuid'])?trim($this->data['cart_uuid']):'';		
            $local_id = isset($this->data['local_id'])?trim($this->data['local_id']):'';
		    $payload = isset($this->data['payload'])?$this->data['payload']:'';

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

            require_once 'get-cart.php';

            $customer_data = [];
            if($client_id = CCart::getAttributes($cart_uuid,'client_id')){            
                try {
                    $model_customer = ACustomer::get($client_id->meta_id);                    
                    $customer_data = [
                        'id'=>$model_customer->client_id,
                        'data'=>[[
                            'label'=>"$model_customer->first_name $model_customer->last_name",
                            'value'=>$model_customer->client_id,
                        ]]
                    ];
                } catch (Exception $e) {}                 
            }

            $cart_model = CCart::get($cart_uuid);            
            			
			$this->code = 1; $this->msg = "ok";
		    $this->details = array(			      
		      'cart_uuid'=>$cart_uuid,		      
		      'error'=>$error,
		      'checkout_data'=>$checkout_data,
		      'out_of_range'=>$out_of_range,
		      'address_component'=>$address_component,
		      'go_checkout'=>$go_checkout,
		      'items_count'=>$items_count,
		      'data'=>$data,		      
              'customer_data'=>$customer_data,
              'order_reference'=>$cart_model->order_reference
		    );			            

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }	
        $this->responseJson();  
    }

    public function actionclearCart()
    {
        try {

            $cart_uuid = Yii::app()->input->post('cart_uuid');
            CCart::clear($cart_uuid);
			$this->code = 1; $this->msg = "Ok";			

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }	
        $this->responseJson();  
    }

	public function actionremoveCartItem()
	{				
		try {
			
            $cart_uuid = Yii::app()->input->post('cart_uuid');
		    $row = Yii::app()->input->post('row');

			CCart::remove($cart_uuid,$row);
            CCacheData::add();
			$this->code = 1; $this->msg = "Ok";
			$this->details = array(
		      'data'=>array()
		    );		    	   			
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		   
		}		
		$this->responseJson();  
	}    

    public function actionupdateCartItems()
	{		
		$cart_uuid = Yii::app()->input->post('cart_uuid');
		$cart_row = Yii::app()->input->post('row');
		$item_qty = intval(Yii::app()->input->post('item_qty'));
		try {
			            
			CCart::update($cart_uuid,$cart_row,$item_qty);
			$this->code = 1; $this->msg = "Ok";
			$this->details = array(
		      'data'=>array()
		    );		    	   			
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		   
		}		
		$this->responseJson();  
	}
	
    public function actionapplyPromoCode()
    {
        try {

            $promo_code = Yii::app()->input->post('promo_code');
            $cart_uuid = Yii::app()->input->post('cart_uuid');            
            
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
		    	
				$transaction_type = CCart::cartTransaction($cart_uuid,Yii::app()->params->local_transtype,$merchant_id);
		    	$resp = CPromos::applyVoucher( $merchant_id, $promo_id, Yii::app()->user->id , $now , $sub_total , $transaction_type);
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
    
    public function actionremovePromocode()
    {
        try {

            $cart_uuid = Yii::app()->input->post('cart_uuid');
            $merchant_id = CCart::getMerchantId($cart_uuid);			
			CCart::deleteAttributesAll($cart_uuid,CCart::CONDITION_RM);
			$this->code = 1;
			$this->msg = "ok";            

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }		
        $this->responseJson();  
    }

    public function actionapplyDiscount()
    {
        try {

            $discount = Yii::app()->input->post('discount');
            $cart_uuid = Yii::app()->input->post('cart_uuid');

            if($discount>0){

                CCart::getContent($cart_uuid,Yii::app()->language);	
			    $subtotal = CCart::getSubTotal();
			    $sub_total = floatval($subtotal['sub_total']);
                $less_amount = $sub_total*($discount/100);              
                
                $sub_total_after_less_discount = $sub_total-$less_amount;
                if($sub_total_after_less_discount>0){
                    $name = array(
                        'label'=>"Discount {{discount}}%",
                        'params'=>array(
                         '{{discount}}'=>Price_Formatter::convertToRaw($discount,0)
                        )
                    );
                    $promo_type = 'manual_discount';
                    $params = array(
                        'name'=> json_encode($name),
                        'type'=>$promo_type,                         
                        'target'=>'subtotal',
                        'value'=>"-%$discount"
                    );		
                    
                    CCart::savedAttributes($cart_uuid,'promo',json_encode($params));
			        CCart::savedAttributes($cart_uuid,'promo_type',$promo_type);
                    $this->code = 1; 
			        $this->msg = "succesful";

                } else $this->mgs = t("Discount cannot apply due to sub total is less than 1");
            } else $this->mgs = t("Discount must be greater than zero");

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }		
        $this->responseJson();  
    }

    public function actionposAttributes()
    {
        try {

            $merchant_id = Yii::app()->merchant->merchant_id;        
            
            $data = []; $payment_code='';
            try {
                $data = CPayments::PaymentList($merchant_id);            
                $payment_code = isset($data[0])? $data[0]['payment_code'] : '';		
            } catch (Exception $e) {                
            }
            
            $transaction_list = []; $transaction_type= ''; $order_status = 'new';
            try {
                $transaction_list = CCheckout::getMerchantTransactionList($merchant_id,Yii::app()->language);		
                $transaction_type = CCheckout::getFirstTransactionType($merchant_id,Yii::app()->language);
            } catch (Exception $e) {
            }

            $order_status_list = AttributesTools::getOrderStatus(Yii::app()->language,'order_status',true);		   
		    if($order_status_list){			  
			  $order_status = $order_status_list[0]['value'];
		    }

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

             $additional_list = [                
                'delivery_fee'=>t("Delivery Fee"),
                'courier_tip'=>t("Courier Tips"),
             ];

             $delivery_option = CCheckout::deliveryOptionList();

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

             $this->code = 1;
             $this->msg = "ok";
             $this->details = array(		     
                'data'=>$data,
                'default_payment'=>$payment_code,
                'transaction_list'=>$transaction_list,
                'transaction_type'=>$transaction_type,
                'order_status_list'=>$order_status_list,
                'order_status'=>$order_status,
                'room_list'=>$room_list,
                'table_list'=>$table_list,
                'additional_list'=>$additional_list,
                'preferred_time'=>$delivery_option,
                'opening_hours'=>$opening_hours,
                'delivery_option'=>CCheckout::deliveryOption(),
				'address_label'=>CCheckout::addressLabel(),				
            );
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }		
        $this->responseJson();  
    }

    public function actionsetTransactionType()
    {
        try {

            $cart_uuid = Yii::app()->input->post('cart_uuid');
            $transaction_type = Yii::app()->input->post('transaction_type');
            CCart::get($cart_uuid);
            CCart::savedAttributes($cart_uuid,Yii::app()->params->local_transtype,$transaction_type);

            $this->code = 1;
            $this->msg = "ok";

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }		
        $this->responseJson();  
    }

    public function actioncreateCustomer()
    {
        try {
            $model = new AR_client();
            $model->first_name =Yii::app()->input->post('first_name');
            $model->last_name =Yii::app()->input->post('last_name');
            $model->email_address =Yii::app()->input->post('email_address');
            $model->contact_phone =Yii::app()->input->post('contact_number');
            if($model->save()){
                $this->code = 1;
                $this->msg = t("Customer succesfully created");
                $this->details = array(
                  'client_id'=>$model->client_id,
                  'client_uuid'=>$model->client_uuid,
                  'client_name'=>"$model->first_name $model->last_name"
                );
            } else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
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
			
		   MapSdk::$map_provider = Yii::app()->params['settings']['map_provider'];		   
		   MapSdk::setKeys(array(
		     'google.maps'=>Yii::app()->params['settings']['google_geo_api_key'],
		     'mapbox'=>Yii::app()->params['settings']['mapbox_access_token'],
		   ));
		   
		   if(MapSdk::$map_provider=="mapbox"){
			   MapSdk::setMapParameters(array(			    
			    'limit'=>1
			   ));
		   }
		   
		   $resp = MapSdk::reverseGeocoding($lat,$lng);		   
		   
		   $this->code =1; $this->msg = "ok";
		   $this->details = array(		     	     		     
		     'provider'=>MapSdk::$map_provider,
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

    public function actioncartSetCustomer()
    {
        try {

            $client_id = trim(Yii::app()->input->post('client_id'));
            $cart_uuid = Yii::app()->input->post('cart_uuid');

            CCart::get($cart_uuid);
            
            if($client_id>0){                
                CCart::savedAttributes($cart_uuid,'client_id',$client_id);
                try {
                    $customer = ACustomer::get($client_id);
                    $customer_name = $customer->first_name." ".$customer->last_name;				
                    CCart::savedAttributes($cart_uuid,'contact_number',$customer->contact_phone);
                    CCart::savedAttributes($cart_uuid,'contact_email',$customer->email_address);
                } catch (Exception $e) {
                    $customer_name = 'Walk-in Customer';
                }	                
                CCart::savedAttributes($cart_uuid,'customer_name',$customer_name);
            } else if ($client_id=="walkin"){                
                CCart::deleteAttributesAll($cart_uuid,[
                    'client_id','contact_number','contact_email'
                ]);
                $customer_name = 'Walk-in Customer';
                CCart::savedAttributes($cart_uuid,'customer_name',$customer_name);        
            } else {                               
                CCart::deleteAttributesAll($cart_uuid,[
                    'client_id','contact_number','contact_email','customer_name'
                ]);                
            }

            $this->code = 1;
            $this->msg = "ok";
            
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }		
        $this->responseJson();  
    }

    public function actionapplyTips()
    {
        try {

            $tips = floatval(Yii::app()->input->post('tips'));
            $cart_uuid = Yii::app()->input->post('cart_uuid');
            if($tips>0){                
                CCart::getContent($cart_uuid,Yii::app()->language);	
                $merchant_id = Yii::app()->merchant->merchant_id;
                $options_data = OptionsTools::find(['merchant_enabled_tip','merchant_tip_type'],$merchant_id);							
			    $enabled_tip = isset($options_data['merchant_enabled_tip'])?$options_data['merchant_enabled_tip']:false;
                if($enabled_tip){
                    CCart::savedAttributes($cart_uuid,'tips',$tips);	
                    $this->code = 1; $this->msg = "OK";
                    $this->details = array(
                       'tips'=>$tips,			  
                    );
                } else $this->msg = t("Tip are disabled");
            } else $this->mgs = t("Tips must be greater than zero");
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }		
        $this->responseJson();  
    }

    public function actionremoveTips()
    {
        try {
            $cart_uuid = Yii::app()->input->post('cart_uuid');
            CCart::deleteAttributes($cart_uuid,'tips');
            $this->code = 1; $this->msg = "ok";
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }		
        $this->responseJson();  
    }

    public function actionapplyHoldOrder()
    {
        try {

            $cart_uuid = Yii::app()->input->post('cart_uuid');
            $order_reference = Yii::app()->input->post('order_reference');
            $model = CCart::get($cart_uuid);

            $model->hold_order=1;
            $model->order_reference=$order_reference;
            if($model->save()){
                $this->code = 1;
                $this->msg = t("Order successfully hold");
            } else $this->msg = CommonUtility::parseError( $model->getErrors() );            
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }		
        $this->responseJson();  
    }

    public function actionsaveCartAddress()
    {
        try {
            
            $client_id = isset($this->data['client_id'])?intval($this->data['client_id']):null;
            $place_id = isset($this->data['place_id'])?trim($this->data['place_id']):null;
            $user = ACustomer::get($client_id);            

            $model = AR_client_address::model()->find('place_id=:place_id AND client_id=:client_id', 
		    array(':place_id'=>$place_id,'client_id'=> $client_id));				
            if(!$model){                
                $model = new AR_client_address();
            }            
            
            $model->client_id = $client_id;
            $model->address_uuid = CommonUtility::generateUIID();		    	
            $model->place_id = $place_id;
            $model->country = isset($this->data['country'])?$this->data['country']:'';
            $model->country_code = isset($this->data['country_code'])?$this->data['country_code']:'';

            $model->location_name = isset($this->data['location_name'])?$this->data['location_name']:'';
	    	$model->delivery_instructions = isset($this->data['delivery_instructions'])?$this->data['delivery_instructions']:'';
	    	$model->delivery_options = isset($this->data['delivery_options'])?$this->data['delivery_options']:'';
	    	$model->address_label = isset($this->data['address_label'])?$this->data['address_label']:'';
	    	$model->latitude = isset($this->data['latitude'])?$this->data['latitude']:'';
	    	$model->longitude = isset($this->data['longitude'])?$this->data['longitude']:'';
	    	$model->address1 = isset($this->data['address1'])?$this->data['address1']:'';			
	    	$model->formatted_address = isset($this->data['formatted_address'])?$this->data['formatted_address']:'';
            
            if($model->save()){
                $this->code = 1;
		    	$this->msg = t("Address saved succesfully");
		    	$this->details = array(
		    	  'place_id'=>$model->place_id
		    	);
            } else $this->msg = CommonUtility::parseError( $model->getErrors());
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }		
        $this->responseJson();  
    }

    public function actionsubmitPOSOrder()
    {
        try {

            $local_id = isset($this->data['place_id'])?$this->data['place_id']:'';
            $cart_uuid = isset($this->data['cart_uuid'])?trim($this->data['cart_uuid']):'';
            $payment_code = isset($this->data['payment_code'])?trim($this->data['payment_code']):'';
            $order_change = isset($this->data['order_change'])?floatval($this->data['order_change']):0;
            $payment_change = $order_change;
            $whento_deliver = isset($this->data['whento_deliver'])?trim($this->data['whento_deliver']):'now';
            $delivery_date = isset($this->data['delivery_date'])?trim($this->data['delivery_date']):'';
            $delivery_time = isset($this->data['delivery_time'])?trim($this->data['delivery_time']):'';
            $receive_amount = isset($this->data['receive_amount'])?floatval($this->data['receive_amount']):0;
            $payment_reference = isset($this->data['payment_reference'])?trim($this->data['payment_reference']):'';
            $order_notes = isset($this->data['order_notes'])?trim($this->data['order_notes']):'';    
            $place_data = isset($this->data['place_data'])?$this->data['place_data']:'';    
            $payment_uuid = isset($this->data['payment_uuid'])?$this->data['payment_uuid']:'';    
            $guest_number = isset($this->data['guest_number'])?intval($this->data['guest_number']):0;
            $room_id = isset($this->data['room_id'])?intval($this->data['room_id']):0;
            $table_id = isset($this->data['table_id'])?trim($this->data['table_id']):'';

            $currency_code = isset($this->data['currency_code'])?trim($this->data['currency_code']):'';
            $base_currency = Price_Formatter::$number_format['currency_code'];
            
            $multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		    $multicurrency_enabled = $multicurrency_enabled==1?true:false;	

            $payload = array(
                'items','merchant_info','service_fee',
                'delivery_fee','packaging','tax','tips','checkout','discount','distance',
                'summary','total','card_fee','points','points_discount','manual_discount'
            );
            //distance_local
            
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
            $client_id = 0;

            /*CHECK IF MERCHANT IS OPEN*/
            try {

                $merchant_id = Yii::app()->merchant->merchant_id;            
                
                // CHECK IF MERCHANT HAS DIFFERENT TIMEZONE
                $options_merchant = OptionsTools::find(['merchant_timezone'],$merchant_id);
                $merchant_timezone = isset($options_merchant['merchant_timezone'])?$options_merchant['merchant_timezone']:'';
                if(!empty($merchant_timezone)){
                    Yii::app()->timezone = $merchant_timezone;
                }

                $date = date("Y-m-d");
                $time_now = date("H:i");                
						                
                if($whento_deliver=="schedule"){
                    $date = $delivery_date;
                    $time_now  = !empty($delivery_time)?$delivery_time:$time_now;
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

            // if($credentials = CMerchants::MapsConfig($merchant_id)){                
			// 	MapSdk::$map_provider = $credentials['provider'];
			// 	MapSdk::setKeys(array(
			// 	  'google.maps'=>$credentials['key'],
			// 	  'mapbox'=>$credentials['key'],
			// 	));				 
			// }

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

            $atts = CCart::getAttributesAll($cart_uuid,['client_id','promo']);
            $client_id = isset($atts['client_id'])?$atts['client_id']:0;            
            
            // LOGIN USER
            $dependency = CCacheData::dependency();
            $user = AR_client::model()->cache(Yii::app()->params->cache, $dependency)->find("client_id=:client_id",array(
                  ':client_id'=>$client_id
            ));  
            if($user){
                Yii::app()->user->id = $client_id;
                Yii::app()->user->setState('client_uuid', $user->client_uuid);                
                Yii::app()->user->setState('first_name', $user->first_name);
                Yii::app()->user->setState('last_name', $user->last_name);
                Yii::app()->user->setState('email_address', $user->email_address);
                Yii::app()->user->setState('contact_number', $user->contact_phone);                     
                Yii::app()->user->setState('phone_prefix', $user->phone_prefix); 
            }            

            require_once 'get-cart.php';
            
            if($transaction_type=="delivery"){                
                $address_component['location_name']	 = isset($place_data['location_name'])?$place_data['location_name']:'';
				$address_component['delivery_options']	 = isset($place_data['delivery_options'])? ( isset($place_data['delivery_options']['value']) ? $place_data['delivery_options']['value'] : $place_data['delivery_options'] )  :'';
				$address_component['delivery_instructions']	 = isset($place_data['delivery_instructions'])?$place_data['delivery_instructions']:'';
				$address_component['address_label']	 = isset($place_data['address_label'])?$place_data['address_label']:'';                
            }     
                                                
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
                
                // $atts = CCart::getAttributesAll($cart_uuid,array('whento_deliver',
				//   'promo','promo_type','promo_id','tips','delivery_date','delivery_time'
				// ));						
                // $atts = CCart::getAttributesAll($cart_uuid,['client_id']);
                // $client_id = isset($atts['client_id'])?$atts['client_id']:0;

                
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
                                
                            case "manual_discount":	    
                                $mdd_offer_discount = 0;                                 
                                $manual_discount_data = isset($atts['promo'])?json_decode($atts['promo'],true):null;                                
                                if(is_array($manual_discount_data) && count($manual_discount_data)>=1){                                    
                                    $mdd_offer_discount = isset($manual_discount_data['value'])?$manual_discount_data['value']:0;
                                }
                                $promo_details = [
                                    'promo_type'=>"manual_discount",
                                    'offer_discount'=> floatval(CCart::cleanNumber($mdd_offer_discount)),
                                    'value'=>CCart::cleanNumber($summary_item['raw']),
                                ];
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
				$model->scenario = 'pos_entry';
				$model->order_uuid = CommonUtility::generateUIID();
				$model->merchant_id = intval($merchant_id);	
				$model->client_id = intval($client_id);
				$model->service_code = $transaction_type;
				$model->payment_code = $payment_code;
				$model->payment_change = $payment_change;				
				$model->validate_payment_change = false;	
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
                
                if(is_array($promo_details) && count($promo_details)>=1){
					if($promo_details['promo_type']=="voucher"){
						$model->promo_code = $promo_details['voucher_name'];
						$model->promo_total = $promo_details['less_amount'];
					} elseif ( $promo_details['promo_type']=="offers" ){						
						$model->offer_discount = $promo_details['less_amount'];
						$model->offer_total = floatval($offer_total);
					} elseif ( $promo_details['promo_type']=="manual_discount" ){                        
                        $model->offer_discount = floatval($promo_details['offer_discount']);
						$model->offer_total = floatval($promo_details['value']);
                    }
				}

                $model->whento_deliver = $whento_deliver;
                if($model->whento_deliver=="now"){
                    $model->delivery_date = CommonUtility::dateNow();
                } else {
                    $model->delivery_date = $delivery_date;
                    $model->delivery_time = $delivery_time;
                    $model->delivery_time_end = $delivery_time;
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
                                
                if(!empty($order_notes)){
                $metas['order_notes'] = $order_notes;
                }
                if($order_change>0){
                $metas['order_change'] = floatval($order_change);
                }
                if($receive_amount>0){
                $metas['receive_amount'] = floatval($receive_amount);
                }
                
                $metas['payment_change'] = floatval($payment_change);
				$metas['self_delivery'] = $self_delivery==true?1:0;	
				$metas['points_to_earn'] = floatval($points_to_earn);	
                                
                if($transaction_type=="dinein"){
                    $metas['guest_number'] = intval($guest_number);
                    try {			
                        $model_room = CBooking::getRoom($room_id); 
                        $metas['room_id'] = $model_room->room_id;
                    } catch (Exception $e) {					
                    }

                    try {			
                        $model_table = CBooking::getTable($table_id); 					
                        $metas['table_id'] = $model_table->table_id;
                    } catch (Exception $e) {					
                    }

                    $model->room_id = $room_id;
			        $model->table_id = $table_id;
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
				
				$model->request_from = "pos";
                
                $model->payment_reference = $payment_reference;
                $model->status = COrders::newOrderStatus();
                $model->payment_status = CPayments::paidStatus();
                                      
                //dump($model);die();
                if($model->save()){
                    try {
                        CCart::clear($cart_uuid);
                    } catch (Exception $e) {
                        //
                    }			
                    $this->code = 1;
					$this->msg = t("Your Order has been place");
                    $this->details = [
                        'order_id'=>$model->order_id,
                        'order_uuid' => $model->order_uuid,
                        'cart_uuid'=>$cart_uuid,
                    ];
                } else {
                    if ( $error = CommonUtility::parseError( $model->getErrors()) ){				
						$this->msg = $error;						
					} else $this->msg[] = array('invalid error');
                }                
            }            
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }		
        $this->responseJson();  
    }

    public function actiononHoldOrders()
    {
        try {

            $page = intval(Yii::app()->input->post('page'));  
            $search = trim(Yii::app()->input->post('q'));           
            $length =Yii::app()->params->list_limit;

            $sortby = "date_created"; $sort = 'DESC';
            
            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
                $page = $page-1;
            }

            $page = intval($page)/intval($length);
            $criteria=new CDbCriteria();            
            $criteria->alias = "a";     
            $criteria->select = "a.*,                  
            b.meta_name,b.meta_id as customer_name,
            c.meta_name,c.meta_id as transaction_type,
            (
                select sum(qty)
                from {{cart}}
                where cart_uuid = a.cart_uuid
            ) as qty
            ";       
            $criteria->join="
            left JOIN (
                SELECT cart_uuid,meta_name, meta_id FROM {{cart_attributes}} where meta_name='customer_name'
            ) b 
            on a.cart_uuid = b.cart_uuid

            left JOIN (
                SELECT cart_uuid,meta_name, meta_id FROM {{cart_attributes}} where meta_name='transaction_type'
            ) c 
            on a.cart_uuid = c.cart_uuid
            ";            
            $criteria->condition = "merchant_id=:merchant_id AND hold_order=:hold_order";
            $criteria->params  = array(
              ':merchant_id'=>intval(Yii::app()->merchant->merchant_id),
              ':hold_order'=>1
            );            

            if(!empty($search)){
                $criteria->addSearchCondition("order_reference",$search);
            }
            
            $criteria->order = "$sortby $sort";
            $count = AR_cart::model()->count($criteria); 
            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );        
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);    
            $page_count = $pages->getPageCount();

            if($page>0){
                if($page_raw>$page_count){
                    $this->code = 3;
                    $this->msg = t("end of results");
                    $this->responseJson();
                }
            }

            $data = [];
                                    
            if($model = AR_cart::model()->findAll($criteria)){                 
                $transaction_list = CServices::Listing(Yii::app()->language);                
                foreach ($model as $items) {
                    $data[] = [
                        'cart_uuid'=>$items->cart_uuid,
                        'order_reference'=>$items->order_reference,
                        'transaction_type'=>$items->transaction_type,
                        'transaction_name'=>isset($transaction_list[$items->transaction_type])?$transaction_list[$items->transaction_type]['service_name']:$items->transaction_type ,
                        'customer_name'=>!empty($items->customer_name)?$items->customer_name:'',
                        'qty'=>$items->qty,
                        'date_created'=>Date_Formatter::dateTime($items->date_created),
                    ];
                }
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'data'=>$data,                  
                ];
            } else $this->msg = t(HELPER_NO_RESULTS);

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }	
        $this->responseJson();  
    }

    public function actiondeleteHoldorder()
    {
        try {

            $id = trim(Yii::app()->input->post('id'));            
            $merchant_id = (integer) Yii::app()->merchant->merchant_id;

            $model = AR_cart::model()->find("merchant_id=:merchant_id AND cart_uuid=:cart_uuid",array(		  
                ':merchant_id'=>$merchant_id,
                ':cart_uuid'=>$id
            ));		
            if($model){
                $model->delete(); 
                $this->code = 1;
                $this->msg = "Ok";
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }	
        $this->responseJson();  
    }

    public function actioncashoutSummary()
    {
        try {
            
            $self_delivery = isset(Yii::app()->params['settings_merchant']['self_delivery'])?Yii::app()->params['settings_merchant']['self_delivery']:false;
		    $self_delivery = $self_delivery==1?true:false;				
			$merchant_id = $self_delivery==true?Yii::app()->merchant->merchant_id:0; 	

            $data = CPayouts::payoutSummary('cashout',$merchant_id);
            $new_data = [];
            $new_data['unpaid']  = [
                'label'=>t("Unpaid"),
                'value'=>isset($data['unpaid'])?$data['unpaid']:0
            ];
            $new_data['paid']  = [
                'label'=>t("Paid"),
                'value'=>isset($data['paid'])?$data['paid']:0
            ];
            $new_data['cancelled']  = [
                'label'=>t("Cancelled"),
                'value'=>isset($data['cancelled'])?$data['cancelled']:0
            ];
            $new_data['total_unpaid']  = [
                'label'=>t("Total Unpaid"),
                'value'=>isset($data['total_unpaid'])?Price_Formatter::formatNumber($data['total_unpaid']):0
            ];
            $new_data['total_paid']  = [
                'label'=>t("Total Paid"),
                'value'=>isset($data['total_paid'])?Price_Formatter::formatNumber($data['total_paid']):0
            ];
            
            $this->code = 1;
			$this->msg = "ok";
            $this->details = [
                'data'=>$new_data
            ];            
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }	
        $this->responseJson();  
    }

    public function actioncashoutList()
    {
        try {

            $page = intval(Yii::app()->input->post('page'));            
            $search = trim(Yii::app()->input->post('q')); 
            $length =Yii::app()->params->list_limit;

            $sortby = "a.transaction_date"; $sort = 'DESC';
            
            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
				$page = $page-1;
			}

            $page = intval($page)/intval($length);		
            $criteria=new CDbCriteria();
            $criteria->alias = "a";
            
            $criteria->select="a.transaction_uuid,a.card_id,a.transaction_amount,a.transaction_date, a.status,
            b.driver_id,b.merchant_id, concat(b.first_name,' ',b.last_name) as driver_name , b.photo as logo , b.path";
            
            $criteria->join="LEFT JOIN {{driver}} b on a.card_id = 
            (
            select card_id from {{wallet_cards}}
            where account_type=".q(Yii::app()->params->account_type['driver'])." and account_id=b.driver_id
            )		
            ";		
            
            $criteria->condition="transaction_type=:transaction_type AND b.merchant_id=:merchant_id";
            $criteria->params = array(		 
            ':transaction_type'=>'cashout',
            ':merchant_id'=>Yii::app()->merchant->merchant_id
            );
            
            $criteria->order = "$sortby $sort";
            $count = AR_wallet_transactions::model()->count($criteria); 
            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );        
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);    
            $page_count = $pages->getPageCount();            
            
            if($page>0){
				if($page_raw>$page_count){
					$this->code = 3;
					$this->msg = t("end of results");
					$this->responseJson();
				}
			}

            $data = [];            
            $payment_status = AttributesTools::paymentStatus();
            
            if($model = AR_wallet_transactions::model()->findAll($criteria)){                
                foreach ($model as $item) {
                    $avatar = CMedia::getImage($item->logo,$item->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('customer')); 
                    $data[] = [
                        'driver_id'=>$item->driver_id,
                        'avatar'=>$avatar,
                        'transaction_date'=>Date_Formatter::dateTime($item->transaction_date),
                        'driver_name'=>Yii::app()->input->xssClean($item->driver_name), 
                        'transaction_amount'=>Price_Formatter::formatNumber($item->transaction_amount),
                        'transaction_uuid'=>$item->transaction_uuid,
                        'status'=>$item->status,
                        'payment_status'=>isset($payment_status[$item->status])?$payment_status[$item->status]:$item->status
                    ];
                }
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'data'=>$data,                    
                ];
            } else $this->msg = t(HELPER_NO_RESULTS);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();     
    }  
    
    public function actiongetPayoutDetails()
    {
        try {
            
            $merchant = array(); 
		    $transaction_uuid =  trim(Yii::app()->input->post('transaction_uuid')); 
		    $data = CPayouts::getPayoutDetails($transaction_uuid,false);			
		    $provider = AttributesTools::paymentProviderDetails( isset($data['provider'])?$data['provider']:'' );		    
		    
		    try{		       
		       $merchant_data = CMerchants::get(Yii::app()->merchant->merchant_id);
			   $merchant = array(
			      'restaurant_name'=>Yii::app()->input->xssClean($merchant_data->restaurant_name)
			   );
		    } catch (Exception $e) {
		    	//
		    }
		    
		    $this->code = 1;
		    $this->msg = "ok";
		    $this->details = array(
		      'data'=>$data,
		      'merchant'=>$merchant,
		      'provider'=>$provider
		    );		    

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();     
    }

    public function actioncancelPayout()
    {
        try {

            $transaction_uuid =  trim(Yii::app()->input->post('transaction_uuid'));             		
			$transaction_type = trim(Yii::app()->input->post('transaction_type'));
			
			$model = AR_wallet_transactions::model()->find("transaction_uuid=:transaction_uuid",array(
			 ':transaction_uuid'=>$transaction_uuid
			));			
			if($model){							
				$params = array(				  
				  'transaction_description'=>"Cancel payout reference #{{transaction_id}}",
				  'transaction_description_parameters'=>array('{{transaction_id}}'=>$model->transaction_id),					  
				  'transaction_type'=>"credit",
				  'transaction_amount'=>floatval($model->transaction_amount),				  
				);									
				$model->scenario = $transaction_type."_cancel";				

				$model->status="cancelled";		
												
				if($model->save()){
				   CWallet::inserTransactions($model->card_id,$params);					   
				   $this->code = 1;
				   $this->msg = t("Payout cancelled");
				} else $this->msg = CommonUtility::parseError( $model->getErrors());
												
			} else $this->msg = t("Transaction not found");

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();     
    }

    public function actionpayoutPaid()
    {
        try {

            $transaction_uuid =  trim(Yii::app()->input->post('transaction_uuid'));             		
			$transaction_type = trim(Yii::app()->input->post('transaction_type'));

            $model = AR_wallet_transactions::model()->find("transaction_uuid=:transaction_uuid",array(
                ':transaction_uuid'=>$transaction_uuid
               ));			
            if($model){				
                   $model->scenario = $transaction_type."_paid";
                   $model->status = 'paid';
                   if($model->save()){
                       $this->code = 1;
                       $this->msg = t("Payout status set to paid");
                   } else $this->msg = CommonUtility::parseError( $model->getErrors());
            } else $this->msg = t("Transaction not found");

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();         
    }

    public function actioncollectCashList()
    {
        try {

            $page = intval(Yii::app()->input->post('page'));                        
            $length =Yii::app()->params->list_limit;

            $sortby = "a.transaction_date"; $sort = 'DESC';
            
            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
				$page = $page-1;
			}

            $page = intval($page)/intval($length);		
            $criteria=new CDbCriteria();
            $criteria->alias = "a";
            
            $criteria->select="a.*, concat(b.first_name,' ',b.last_name) as driver_name";
		
            $criteria->join="LEFT JOIN {{driver}} b on a.driver_id = b.driver_id";		

            $criteria->condition="a.merchant_id=:merchant_id";
            $criteria->params = [
                ':merchant_id'=>Yii::app()->merchant->merchant_id
            ];      
                        
            $criteria->order = "$sortby $sort";
            $count = AR_driver_collect_cash::model()->count($criteria); 
            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );        
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);    
            $page_count = $pages->getPageCount();            
            
            if($page>0){
				if($page_raw>$page_count){
					$this->code = 3;
					$this->msg = t("end of results");
					$this->responseJson();
				}
			}

            $data = [];                        
            
            if($model = AR_driver_collect_cash::model()->findAll($criteria)){                
                foreach ($model as $item) {
                    $data[] = [
                        'collect_id'=>$item->collect_id,
                        'transaction_date'=>Date_Formatter::dateTime($item->transaction_date),
                        'driver_name'=>!empty($item->driver_name)?$item->driver_name:t("Not found"),
                        'amount_collected'=>Price_Formatter::formatNumber($item->amount_collected),
                        'reference_id'=>$item->reference_id,
                        'collection_uuid'=>$item->collection_uuid,                        
                    ];
                }
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'data'=>$data,                    
                ];
            } else $this->msg = t(HELPER_NO_RESULTS);
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();         
    }

    public function actioncollectTransactions()
    {
        try {

            $collection_uuid = trim(Yii::app()->input->post('collection_uuid'));             

            $employment_type = AttributesTools::DriverEmploymentType();
            $data = CDriver::getCollectCashDetails($collection_uuid);
            $data['transaction_date'] = Date_Formatter::dateTime($data['transaction_date']);            
            
            $card_id = 0; $balance = 0;		
			$driver_id = isset($data['driver_id'])?$data['driver_id']:0;
			try {
			    $card_id = CWallet::getCardID( Yii::app()->params->account_type['driver'] , $driver_id );				
				$balance = CDriver::cashCollectedBalance($card_id);				
		    } catch (Exception $e) {			    				
		    }		
            
            $this->code = 1;
            $this->msg = "Ok";
            $this->details = [
                'data'=>$data,
                'balance'=>$balance,
                'employment_type'=>$employment_type
            ];

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();   
    }

    public function actiondriverList()
    {
        try {

            $page = intval(Yii::app()->input->post('page'));                        
            $length =Yii::app()->params->list_limit;

            $sortby = "date_created"; $sort = 'DESC';
            
            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
				$page = $page-1;
			}

            $page = intval($page)/intval($length);		
            $criteria=new CDbCriteria();
            $merchant_id = Yii::app()->merchant->merchant_id;
            $criteria->condition = "merchant_id=:merchant_id";
            $criteria->params = [
                ':merchant_id'=>$merchant_id
            ];
                        
            $criteria->order = "$sortby $sort";
            $count = AR_driver::model()->count($criteria); 
            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );        
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);    
            $page_count = $pages->getPageCount();            
            
            if($page>0){
				if($page_raw>$page_count){
					$this->code = 3;
					$this->msg = t("end of results");
					$this->responseJson();
				}
			}

            $data = [];                        
            
            if($model = AR_driver::model()->findAll($criteria)){                            
                $employment_list = AttributesTools::DriverEmploymentType();                
                foreach ($model as $item) {

                    $photo = CMedia::getImage($item->photo,$item->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('customer'));
                    $status = AttributesTools::StatusManagement('customer');                    

                    $data[] = [
                        'driver_id'=>$item->driver_id,
                        'driver_uuid'=>$item->driver_uuid,	
                        'date_created'=>$item->date_created,
                        'avatar'=>$photo,                        
                        'first_name'=> $item->first_name,
                        'last_name'=> $item->last_name,
                        'email'=>$item->email,
                        'phone'=>$item->phone_prefix.$item->phone,
                        'employment_type'=>isset($employment_list[$item->employment_type])?$employment_list[$item->employment_type]:$item->employment_type,
                        'status_raw'=>$item->status,
                        'status'=>isset($status[$item->status])?$status[$item->status]:$item->status
                    ];
                }
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'data'=>$data,                    
                ];
            } else $this->msg = t(HELPER_NO_RESULTS);
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();         
    }

    public function actiondeleteDriver()
    {
        try {

            $id = trim(Yii::app()->input->post('id'));		
            $merchant_id = Yii::app()->merchant->merchant_id;
            $model = AR_driver::model()->find("driver_uuid=:driver_uuid AND merchant_id=:merchant_id",[
                ':driver_uuid'=>$id,
                ':merchant_id'=>$merchant_id
            ]);
            if($model){
                $model->delete();
                $this->code = 1;
                $this->msg = "Ok";
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();         
    }

    public function actionAddDriver()
    {
        try {

            $merchant_id = Yii::app()->merchant->merchant_id;
            $model = new AR_driver;

            $model->merchant_id = $merchant_id;
            $model->first_name = isset($this->data['first_name'])?$this->data['first_name']:'';
            $model->last_name = isset($this->data['last_name'])?$this->data['last_name']:'';
            $model->email = isset($this->data['email'])?$this->data['email']:'';

            $phone = isset($this->data['phone'])?$this->data['phone']:'';
            $model->phone_prefix = isset($phone['mobile_prefix'])?$phone['mobile_prefix']:'';
            $model->phone = isset($phone['mobile_number'])?$phone['mobile_number']:'';

            $model->address = isset($this->data['address'])?$this->data['address']:'';
            $model->new_password = isset($this->data['new_password'])?$this->data['new_password']:'';
            $model->confirm_password = isset($this->data['confirm_password'])?$this->data['confirm_password']:'';

            $model->employment_type = isset($this->data['employment_type'])?$this->data['employment_type']:'';
            $model->salary_type = isset($this->data['salary_type'])?$this->data['salary_type']:'';
            $model->salary = isset($this->data['salary'])?floatval($this->data['salary']):0;
            $model->fixed_amount = isset($this->data['fixed_amount'])?floatval($this->data['fixed_amount']):0;
            $model->commission = isset($this->data['commission'])?floatval($this->data['commission']):0;
            $model->commission_type = isset($this->data['commission_type'])?$this->data['commission_type']:'';
            $model->incentives_amount = isset($this->data['incentives_amount'])?floatval($this->data['incentives_amount']):0;
            $model->allowed_offline_amount = isset($this->data['allowed_offline_amount'])?floatval($this->data['allowed_offline_amount']):0;
            $model->status = isset($this->data['status'])?$this->data['status']:'active';

            $model->photo = isset($this->data['photo'])?$this->data['photo']:'';
            $model->path = isset($this->data['upload_path'])?$this->data['upload_path']:'';            

            $file_data = isset($this->data['file_data'])?$this->data['file_data']:'';
            $image_type = isset($this->data['image_type'])?$this->data['image_type']:'png';
            if(!empty($file_data)){
                $result = [];
                try {
                    $result = CImageUploader::saveBase64Image($file_data,$image_type,"upload/".Yii::app()->merchant->merchant_id);
                    $model->photo = isset($result['filename'])?$result['filename']:'';
                    $model->path = isset($result['path'])?$result['path']:'';
                } catch (Exception $e) {
                    $this->msg = t($e->getMessage());
                    $this->responseJson();
                }
            }
            
            if($model->save()){
                $this->code = 1;
                $this->msg = t("Driver succesfully added");
            } else $this->msg = CommonUtility::parseError( $model->getErrors() );

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();         
    }

    public function actiongetDriverInfo()
    {
        try {

            $id = trim(Yii::app()->input->post('id'));            
            $merchant_id = Yii::app()->merchant->merchant_id;
            $model = AR_driver::model()->find("driver_uuid=:driver_uuid AND merchant_id=:merchant_id",[
                ':driver_uuid'=>$id,
                ':merchant_id'=>$merchant_id
            ]);
            if($model){
                $avatar = CMedia::getImage($model->photo,$model->path,Yii::app()->params->size_image,CommonUtility::getPlaceholderPhoto('customer'));
                $license_front_photo_url = CMedia::getImage($model->license_front_photo,$model->path_license,Yii::app()->params->size_image,CommonUtility::getPlaceholderPhoto('customer'));
                $license_back_photo_url = CMedia::getImage($model->license_back_photo,$model->path_license,Yii::app()->params->size_image,CommonUtility::getPlaceholderPhoto('customer'));
                $data = [
                    'first_name'=>$model->first_name,
                    'last_name'=>$model->last_name,
                    'email'=>$model->email,
                    'phone_prefix'=>$model->phone_prefix,
                    'phone'=>$model->phone,
                    'address'=>$model->address,
                    'employment_type'=>$model->employment_type,
                    'salary_type'=>$model->salary_type,
                    'salary'=>$model->salary,
                    'fixed_amount'=>$model->fixed_amount,
                    'commission'=>$model->commission,
                    'commission_type'=>$model->commission_type,
                    'incentives_amount'=>$model->incentives_amount,
                    'allowed_offline_amount'=>$model->allowed_offline_amount,
                    'status'=>$model->status,
                    'photo'=>$model->photo,
                    'path'=>$model->path,
                    'avatar'=>$avatar,
                    'license_number'=>$model->license_number,
                    'license_expiration'=>$model->license_expiration,
                    'path_license'=>$model->path_license,
                    'license_front_photo'=>$model->license_front_photo,
                    'license_back_photo'=>$model->license_back_photo,
                    'license_front_photo_url'=>$license_front_photo_url,
                    'license_back_photo_url'=>$license_back_photo_url
                ];
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = $data;
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionUpdateDriver()
    {
        try {

            $id = isset($this->data['id'])?$this->data['id']:'';
            $merchant_id = Yii::app()->merchant->merchant_id;            
            $model = AR_driver::model()->find("driver_uuid=:driver_uuid AND merchant_id=:merchant_id",[
                ':driver_uuid'=>$id,
                ':merchant_id'=>$merchant_id
            ]);
            if($model){
                $model->merchant_id = $merchant_id;
                $model->first_name = isset($this->data['first_name'])?$this->data['first_name']:'';
                $model->last_name = isset($this->data['last_name'])?$this->data['last_name']:'';
                $model->email = isset($this->data['email'])?$this->data['email']:'';
    
                $phone = isset($this->data['phone'])?$this->data['phone']:'';
                $model->phone_prefix = isset($phone['mobile_prefix'])?$phone['mobile_prefix']:'';
                $model->phone = isset($phone['mobile_number'])?$phone['mobile_number']:'';
    
                $model->address = isset($this->data['address'])?$this->data['address']:'';

                $new_password = isset($this->data['new_password'])?$this->data['new_password']:'';
                $confirm_password = isset($this->data['confirm_password'])?$this->data['confirm_password']:'';

                if(!empty($new_password)){
                    $model->new_password =  $new_password;
                }                
                if(!empty($confirm_password)){
                    $model->confirm_password =  $confirm_password;
                }                                
    
                $model->employment_type = isset($this->data['employment_type'])?$this->data['employment_type']:'';
                $model->salary_type = isset($this->data['salary_type'])?$this->data['salary_type']:'';
                $model->salary = isset($this->data['salary'])?floatval($this->data['salary']):0;
                $model->fixed_amount = isset($this->data['fixed_amount'])?floatval($this->data['fixed_amount']):0;
                $model->commission = isset($this->data['commission'])?floatval($this->data['commission']):0;
                $model->commission_type = isset($this->data['commission_type'])?$this->data['commission_type']:'';
                $model->incentives_amount = isset($this->data['incentives_amount'])?floatval($this->data['incentives_amount']):0;
                $model->allowed_offline_amount = isset($this->data['allowed_offline_amount'])?floatval($this->data['allowed_offline_amount']):0;
                $model->status = isset($this->data['status'])?$this->data['status']:'active';
                
                $model->photo = isset($this->data['photo'])?$this->data['photo']:'';
                $model->path = isset($this->data['upload_path'])?$this->data['upload_path']:'';
    
                $file_data = isset($this->data['file_data'])?$this->data['file_data']:'';
                $image_type = isset($this->data['image_type'])?$this->data['image_type']:'png';
                if(!empty($file_data)){
                    $result = [];
                    try {
                        $result = CImageUploader::saveBase64Image($file_data,$image_type,"upload/".Yii::app()->merchant->merchant_id);
                        $model->photo = isset($result['filename'])?$result['filename']:'';
                        $model->path = isset($result['path'])?$result['path']:'';
                    } catch (Exception $e) {
                        $this->msg = t($e->getMessage());
                        $this->responseJson();
                    }
                }

                if($model->save()){
                    $this->code = 1;
                    $this->msg = t("Driver succesfully updated");
                } else $this->msg = CommonUtility::parseError( $model->getErrors() );

            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actiongetDriverOverview()
    {
        try {

            $driver_uuid = trim(Yii::app()->input->post('id'));		
            $merchant_id = Yii::app()->merchant->merchant_id;     
            $data = [];
            
            $driver_data = CDriver::getDriverByUUID($driver_uuid);					
			$driver_id = $driver_data->driver_id;
			$total = CReviews::reviewsCountDriver($driver_id);			
			$review_summary = CReviews::summaryDriver($driver_id,$total);	            

            $tracking_stats = AR_admin_meta::getMeta(array(
				'tracking_status_delivered','tracking_status_completed'
			));		
			$tracking_status_delivered = isset($tracking_stats['tracking_status_delivered'])?AttributesTools::cleanString($tracking_stats['tracking_status_delivered']['meta_value']):'';			
			$tracking_status_completed = isset($tracking_stats['tracking_status_completed'])?AttributesTools::cleanString($tracking_stats['tracking_status_completed']['meta_value']):'';			

            $total_delivered_percent=0;
			$total_delivered = CDriver::CountOrderStatus($driver_id,$tracking_status_delivered);
			$total_assigned =  CDriver::SummaryCountOrderTotal($driver_id);
			if($total_assigned>0){
			  $total_delivered_percent = round(($total_delivered/$total_assigned)*100);
			}

            $successful_status = array();
			if(!empty($tracking_status_delivered)){
				$successful_status[] = $tracking_status_delivered;
			}			
			if(!empty($tracking_status_completed)){
			   $successful_status[] = $tracking_status_completed;
			}

            $total_tip_percent = 0;
			$total_tip = CDriver::TotaLTips($driver_id,$successful_status);
			$summary_tip = CDriver::SummaryTotaLTips($driver_id);
			if($summary_tip>0){
				$total_tip_percent = round(($total_tip/$summary_tip)*100);
			}

            try {																										
				$card_id = CWallet::createCard( Yii::app()->params->account_type['driver'] ,$driver_id);				    	
				$wallet_balance = CWallet::getBalance($card_id);
			} catch (Exception $e) {
			   $this->msg = t($e->getMessage());
			    $wallet_balance = 0;		
			}	

            $data = array(
                'first_name'=>$driver_data->first_name,
                'last_name'=>$driver_data->last_name,
                'total'=>$total,				
                'review_summary'=>$review_summary,	
                'total_delivered'=>$total_delivered,
                'total_delivered_percent'=>$total_delivered_percent,
                'total_tip'=>Price_Formatter::formatNumber($total_tip),
                'total_tip_percent'=>intval($total_tip_percent),
                'wallet_balance'=>Price_Formatter::formatNumber($wallet_balance),
            );    	

            $this->code = 1; $this->msg = "ok";
		    $this->details = $data;
        
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actiongetDriverActivity()
    {
        try {

            $driver_uuid = trim(Yii::app()->input->post('id'));		
            $merchant_id = Yii::app()->merchant->merchant_id;     
            $date_end = Yii::app()->input->post('date_end');
            $date_end = !empty($date_end)?$date_end:date("Y-m-d");

            $model = CDriver::getDriverByUUID($driver_uuid);
			$driver_id = $model->driver_id;
			
			$date_start = date('Y-m-d', strtotime('-7 days'));			
			$model = CDriver::getActivity($driver_id,$date_start,$date_end);
			if($model){
                $data = [];                

                foreach ($model as $items) {
                    $args = !empty($items->remarks_args) ?  json_decode($items->remarks_args,true) : array();
                    $data[] = [                        
						'created_at'=>PrettyDateTime::parse(new DateTime($items->created_at)),   
						'order_id'=>$items->order_id,
                        'remarks'=>t($items->remarks,(array)$args),                        
                    ];
                }

                $this->code = 1;
                $this->msg = "OK";
                $this->details = [
                    'data'=>$data                    
                ];
            } else $this->msg = t(HELPER_NO_RESULTS);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }
          
    public function actionAddLicense()
    {
        try {

            $merchant_id = Yii::app()->merchant->merchant_id;     
            $driver_uuid = isset($this->data['id'])?$this->data['id']:'';
            $license_expiration = isset($this->data['license_expiration'])?$this->data['license_expiration']:'';
            $license_number = isset($this->data['license_number'])?$this->data['license_number']:'';

            $model = CDriver::getDriverByUUID($driver_uuid);
            $model->license_number = $license_number;
            $model->license_expiration = $license_expiration;

            $model->license_front_photo = isset($this->data['photo'])?$this->data['photo']:'';
            $model->path_license = isset($this->data['upload_path'])?$this->data['upload_path']:'';            

            $file_data = isset($this->data['file_data'])?$this->data['file_data']:'';
            $image_type = isset($this->data['image_type'])?$this->data['image_type']:'png';

            $upload_path2 = isset($this->data['upload_path2'])?$this->data['upload_path2']:'';            
            $model->license_back_photo = isset($this->data['photo2'])?$this->data['photo2']:'';
            $file_data2 = isset($this->data['file_data2'])?$this->data['file_data2']:'';
            $image_type2 = isset($this->data['image_type2'])?$this->data['image_type2']:'png';

            $model->path_license = !empty($model->path_license)?$model->path_license:$upload_path2;

            if(!empty($file_data)){
                $result = [];
                try {
                    $result = CImageUploader::saveBase64Image($file_data,$image_type,"upload/".Yii::app()->merchant->merchant_id);
                    $model->license_front_photo = isset($result['filename'])?$result['filename']:'';
                    $model->path_license = isset($result['path'])?$result['path']:'';
                } catch (Exception $e) {
                    $this->msg = t($e->getMessage());
                    $this->responseJson();
                }
            }
            if(!empty($file_data2)){
                $result = [];
                try {
                    $result = CImageUploader::saveBase64Image($file_data2,$image_type2,"upload/".Yii::app()->merchant->merchant_id);
                    $model->license_back_photo = isset($result['filename'])?$result['filename']:'';
                    $model->path_license = isset($result['path'])?$result['path']:'';
                } catch (Exception $e) {
                    $this->msg = t($e->getMessage());
                    $this->responseJson();
                }
            }
                        
            if($model->save()){
                $this->code = 1;
                $this->msg = t("License succesfully updated");
            } else $this->msg = CommonUtility::parseError( $model->getErrors() );

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actiongetVehicle()
    {
        try {

            $id = trim(Yii::app()->input->post('id'));            
            $merchant_id = Yii::app()->merchant->merchant_id;
            $model = CDriver::getDriverByUUID($id);
            if($model){                

                $photo = ''; $path=''; $photo_url='';
                $vehicle_type_id = ''; $plate_number = '';
                $maker = ''; $car_model = ''; $color = '';
                $model_vehicle = AR_driver_vehicle::model()->find("driver_id=:driver_id",[
                    ':driver_id'=>$model->driver_id
                ]);
                if($model_vehicle){                    
                    $photo_url = CMedia::getImage($model_vehicle->photo,$model_vehicle->path,Yii::app()->params->size_image,CommonUtility::getPlaceholderPhoto('customer'));
                    $photo = $model_vehicle->photo;
                    $path = $model_vehicle->path;
                    $vehicle_type_id = $model_vehicle->vehicle_type_id;
                    $plate_number = $model_vehicle->plate_number;
                    $maker = $model_vehicle->maker;
                    $car_model = $model_vehicle->model;
                    $color = $model_vehicle->color;
                }                

                $data = [
                    'first_name'=>$model->first_name,
                    'last_name'=>$model->last_name,  
                    'photo_url'=>$photo_url, 
                    'photo'=>$photo,
                    'path'=>$path,                    
                    'vehicle_type_id'=>$vehicle_type_id,
                    'plate_number'=>$plate_number,
                    'maker'=>$maker,
                    'model'=>$car_model,
                    'color'=>$color,
                ];
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = $data;
            } else $this->msg = t(HELPER_NO_RESULTS);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionVehicleAttributes()
    {
        try {
            
            $vehicle_maker = CommonUtility::getDataToDropDown("{{admin_meta}}","meta_id",'meta_value',"WHERE meta_name='vehicle_maker'","Order by meta_name");
            $vehicle_maker = $vehicle_maker?CommonUtility::ArrayToLabelValue($vehicle_maker):'';

		    $vehicle_type = CommonUtility::getDataToDropDown("{{admin_meta}}","meta_id",'meta_value',"WHERE meta_name='vehicle_type'","Order by meta_name");		
            $vehicle_type = $vehicle_type?CommonUtility::ArrayToLabelValue($vehicle_type):'';

            $this->code = 1; $this->msg = "Ok";
            $this->details = [
                'vehicle_maker'=>$vehicle_maker,
                'vehicle_type'=>$vehicle_type,
            ];

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionAddVehicle()
    {
        try {

            $id = isset($this->data['id'])?$this->data['id']:'';
            $merchant_id = Yii::app()->merchant->merchant_id;
            $driver = CDriver::getDriverByUUID($id);
            if($driver){                
                $model = AR_driver_vehicle::model()->find("driver_id=:driver_id",[
                    ':driver_id'=>$driver->driver_id
                ]);
                if(!$model){
                    $model = new AR_driver_vehicle();
                } 

                $model->driver_id = $driver->driver_id;
                $model->merchant_id =$merchant_id;
                $model->vehicle_type_id = isset($this->data['vehicle_type_id'])? intval($this->data['vehicle_type_id']) :0;
                $model->plate_number = isset($this->data['plate_number'])?$this->data['plate_number']:'';
                $model->maker = isset($this->data['maker'])? intval($this->data['maker']) :0;
                $model->model = isset($this->data['model'])?$this->data['model']:'';
                $model->color = isset($this->data['color'])?$this->data['color']:'';
                
                $model->photo = isset($this->data['photo'])?$this->data['photo']:'';
                $model->path = isset($this->data['upload_path'])?$this->data['upload_path']:'';      
                
                if($model->save()){
                    $this->code = 1;
                    $this->msg = t("Vehicle succesfully updated");
                } else $this->msg = CommonUtility::parseError( $model->getErrors() );

            } else $this->msg = t(HELPER_NO_RESULTS);
            
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actiongetBankInfo()
    {
        try {

            $id = trim(Yii::app()->input->post('id'));            
            $merchant_id = Yii::app()->merchant->merchant_id;
            $model = CDriver::getDriverByUUID($id);
            if($model){ 

                $account_name = ''; $account_number_iban=''; $swift_code=''; $bank_name=''; $bank_branch='';

                $meta = AR_driver_meta::model()->find("reference_id=:reference_id AND meta_name=:meta_name",[
                    ':reference_id'=>$model->driver_id,
                    ':meta_name'=>'bank_information'
                ]);
                if($meta){
                    $data = !empty($meta->meta_value1)?json_decode($meta->meta_value1,true):'';
                    $account_name = isset($data['account_name'])?$data['account_name']:'';
                    $account_number_iban = isset($data['account_number_iban'])?$data['account_number_iban']:'';
                    $swift_code = isset($data['swift_code'])?$data['swift_code']:'';
                    $bank_name = isset($data['bank_name'])?$data['bank_name']:'';
                    $bank_branch = isset($data['bank_branch'])?$data['bank_branch']:'';
                }

                $data = [
                    'first_name'=>$model->first_name,
                    'last_name'=>$model->last_name,     
                    'account_name'=>$account_name,
                    'account_number_iban'=>$account_number_iban,
                    'swift_code'=>$swift_code,
                    'bank_name'=>$bank_name,
                    'bank_branch'=>$bank_branch,
                ];
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = $data;
            } else $this->msg = t(HELPER_NO_RESULTS);
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionAddDriverBankInfo()
    {
        try {
            
            $id = isset($this->data['id'])?$this->data['id']:'';
            $merchant_id = Yii::app()->merchant->merchant_id;
            $driver = CDriver::getDriverByUUID($id);   
            if($driver){
                
                $model = AR_driver_meta::model()->find("reference_id=:reference_id AND meta_name=:meta_name",[
                    ':reference_id'=>$driver->driver_id,
                    ':meta_name'=>'bank_information'
                ]);
                if(!$model){
                    $model = new AR_driver_meta();
                }

                $model->scenario = 'bank_information';
                $model->merchant_id = $merchant_id;
                $model->reference_id = $driver->driver_id;
                $model->meta_name = 'bank_information';
                $model->account_name = isset($this->data['account_name'])?$this->data['account_name']:'';
                $model->account_number_iban = isset($this->data['account_number_iban'])?$this->data['account_number_iban']:'';
                $model->swift_code = isset($this->data['swift_code'])?$this->data['swift_code']:'';
                $model->bank_name = isset($this->data['bank_name'])?$this->data['bank_name']:'';
                $model->bank_branch = isset($this->data['bank_branch'])?$this->data['bank_branch']:'';

                $model->meta_value1 = json_encode(array(		
                    'provider'=>"bank",
                    'account_name'=>isset($this->data['account_name'])?$this->data['account_name']:'',
                    'account_number_iban'=>isset($this->data['account_number_iban'])?$this->data['account_number_iban']:'',
                    'swift_code'=>isset($this->data['swift_code'])?$this->data['swift_code']:'',
                    'bank_name'=>isset($this->data['bank_name'])?$this->data['bank_name']:'',
                    'bank_branch'=>isset($this->data['bank_branch'])?$this->data['bank_branch']:'',
                ));
                if($model->save()){
                    $this->code = 1;
                    $this->msg = T(Helper_success);
                } else $this->msg = CommonUtility::parseError( $model->getErrors() );
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actiondriverWalletBalance()
    {
        $first_name = ''; $last_name='';
        try {											
			$id = Yii::app()->input->post('id');
			$driver_data = CDriver::getDriverByUUID($id);            
			$driver_id = $driver_data->driver_id;				
            $first_name = $driver_data->first_name;					
            $last_name = $driver_data->last_name;

			$card_id = CWallet::createCard( Yii::app()->params->account_type['driver'] ,$driver_id);				    	
			$balance = CWallet::getBalance($card_id);
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		    $balance = 0;		
		}	
				
		$this->code = 1;
		$this->msg = "OK";
		$this->details = array(
          'first_name'=>$first_name,
          'last_name'=>$last_name,     	  
          'balance'=>Price_Formatter::formatNumber($balance),
		  'balance_raw'=>Price_Formatter::formatNumberNoSymbol($balance),	          
		);		
		$this->responseJson();		
    }

    public function actiondriverWalletTransactions()
    {
        $card_id ='';
        try {			
            $id = Yii::app()->input->post('id');
            $driver_data = CDriver::getDriverByUUID($id);
            $driver_id = $driver_data->driver_id;			
            $card_id = CWallet::getCardID(Yii::app()->params->account_type['driver'],$driver_id);				
        } catch (Exception $e) {
            //
        }		

        try {          

            $page = intval(Yii::app()->input->post('page'));  
            $search = trim(Yii::app()->input->post('q'));           
            $length =Yii::app()->params->list_limit;

            $sortby = "transaction_id"; $sort = 'DESC';
            
            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
                $page = $page-1;
            }

            $page = intval($page)/intval($length);
            $criteria=new CDbCriteria();       
            
            $criteria->addCondition('card_id=:card_id');
		    $criteria->params = array(':card_id'=>intval($card_id));
           
            $criteria->order = "$sortby $sort";
            $count = AR_wallet_transactions::model()->count($criteria); 
            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );        
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);    
            $page_count = $pages->getPageCount();

            if($page>0){
                if($page_raw>$page_count){
                    $this->code = 3;
                    $this->msg = t("end of results");
                    $this->responseJson();
                }
            }

            $data = [];
                        
            if($model = AR_wallet_transactions::model()->findAll($criteria)){ 
                foreach ($model as $items) {
                    $description = Yii::app()->input->xssClean($items->transaction_description);        		
                    $parameters = json_decode($items->transaction_description_parameters,true);        		
                    if(is_array($parameters) && count($parameters)>=1){        			
                        $description = t($description,$parameters);
                    }
                    $data[]=array(
                        'transaction_date'=>Date_Formatter::dateTime($items->transaction_date),
                        'transaction_description'=>$description,
                        'transaction_type'=>$items->transaction_type,
                        'transaction_amount'=>Price_Formatter::formatNumber($items->transaction_amount),
                        'running_balance'=>Price_Formatter::formatNumber($items->running_balance),
                    );
                }
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'data'=>$data,                  
                ];
            } else $this->msg = t(HELPER_NO_RESULTS);

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }	
        $this->responseJson();     
    }
    
    public function actiondriverWalletAdjustment()
    {
        try {

            $id = isset($this->data['id'])?$this->data['id']:'';
            $transaction_description = isset($this->data['transaction_description'])?$this->data['transaction_description']:'';
			$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';
			$transaction_amount = isset($this->data['transaction_amount'])?$this->data['transaction_amount']:0;

            $multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
            $multicurrency_enabled = $multicurrency_enabled==1?true:false;		
			$base_currency = Price_Formatter::$number_format['currency_code'];
			$admin_base_currency = AttributesTools::defaultCurrency();
			$exchange_rate_merchant_to_admin = 1; $exchange_rate_admin_to_merchant=1;
			
			if($multicurrency_enabled && $base_currency!=$admin_base_currency){
				$exchange_rate_merchant_to_admin = CMulticurrency::getExchangeRate($base_currency,$admin_base_currency);
				$exchange_rate_admin_to_merchant = CMulticurrency::getExchangeRate($admin_base_currency,$base_currency);
			}

            $params = array(
                'transaction_description'=>$transaction_description,			  
                'transaction_type'=>$transaction_type,
                'transaction_amount'=>floatval($transaction_amount),
                'meta_name'=>"adjustment",
                'meta_value'=>CommonUtility::createUUID("{{admin_meta}}",'meta_value'),
                'merchant_base_currency'=>$base_currency,
                'admin_base_currency'=>$admin_base_currency,
                'exchange_rate_merchant_to_admin'=>$exchange_rate_merchant_to_admin,
                'exchange_rate_admin_to_merchant'=>$exchange_rate_admin_to_merchant,
            );
                        
			$driver_data = CDriver::getDriverByUUID($id);
            $driver_id = $driver_data->driver_id;									
			$card_id = CWallet::createCard( Yii::app()->params->account_type['driver'] ,$driver_id);			
			CWallet::inserTransactions($card_id,$params);

			$this->code = 1; $this->msg = t("Successful");

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionclearDriverWallet()
    {
        try {            

            if(DEMO_MODE){
				$this->msg = t("This functions is not available in demo");
				$this->responseJson();
			}	

            $id = Yii::app()->input->post("id");            
            $card_id = 0;			
			try {			
				$driver_data = CDriver::getDriverByUUID($id);
				$driver_id = $driver_data->driver_id;			
				$card_id = CWallet::getCardID(Yii::app()->params->account_type['driver'],$driver_id);				
			} catch (Exception $e) {
				//
			}	
			
			AR_wallet_transactions::model()->deleteAll("card_id=:card_id",[
				':card_id'=>$card_id
			]);
			
			$this->code = 1;
			$this->msg = "Ok";

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }
    
    public function actiondriverCashoutTransactions()
    {
        $driver_id = 0; $card_id=0; $data = [];
		try {											
			$ref_id = Yii::app()->input->post('id');
			$driver_data = CDriver::getDriverByUUID($ref_id);
			$driver_id = $driver_data->driver_id;		
			$card_id = CWallet::getCardID(Yii::app()->params->account_type['driver'],$driver_id);										
		} catch (Exception $e) {		   
		}	

        try {          

            $page = intval(Yii::app()->input->post('page'));  
            $search = trim(Yii::app()->input->post('q'));           
            $length =Yii::app()->params->list_limit;

            $sortby = "transaction_id"; $sort = 'DESC';
            
            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
                $page = $page-1;
            }

            $page = intval($page)/intval($length);
            $criteria=new CDbCriteria();     
            $criteria->condition = "card_id=:card_id  AND transaction_type=:transaction_type";
            $criteria->params  = array(
            ':card_id'=>intval($card_id),
            ':transaction_type'=>"cashout"
            );                       
           
            $criteria->order = "$sortby $sort";
            $count = AR_wallet_transactions::model()->count($criteria); 
            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );        
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);    
            $page_count = $pages->getPageCount();

            if($page>0){
                if($page_raw>$page_count){
                    $this->code = 3;
                    $this->msg = t("end of results");
                    $this->responseJson();
                }
            }

            $data = [];
                        
            if($model = AR_wallet_transactions::model()->findAll($criteria)){ 
                foreach ($model as $items) {
                    $description = Yii::app()->input->xssClean($items->transaction_description);        		
                    $parameters = json_decode($items->transaction_description_parameters,true);        		
                    if(is_array($parameters) && count($parameters)>=1){        			
                        $description = t($description,$parameters);
                    }
                    $data[]=array(
                        'transaction_date'=>Date_Formatter::dateTime($items->transaction_date),
                        'transaction_description'=>$description,
                        'transaction_type'=>$items->transaction_type,
                        'transaction_amount'=>Price_Formatter::formatNumber($items->transaction_amount),
                        'running_balance'=>Price_Formatter::formatNumber($items->running_balance),
                    );
                }
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'data'=>$data,                  
                ];
            } else $this->msg = t(HELPER_NO_RESULTS);

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }	
        $this->responseJson();     
    }

    public function actiondriverOrderTransaction()
    {
        $driver_id = 0;  $data = [];
		try {											
			$ref_id = Yii::app()->input->post('id');
			$driver_data = CDriver::getDriverByUUID($ref_id);
			$driver_id = $driver_data->driver_id;			
		} catch (Exception $e) {		   
		}	

        try {          

            $page = intval(Yii::app()->input->post('page'));  
            $search = trim(Yii::app()->input->post('q'));           
            $length =Yii::app()->params->list_limit;

            $sortby = "date_created"; $sort = 'DESC';
            
            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
                $page = $page-1;
            }

            $page = intval($page)/intval($length);
            $criteria=new CDbCriteria();     
            $criteria->alias ="a";		
            $criteria->select = "a.*,
            (
                select concat(first_name,' ',last_name)
                from {{client}}
                where client_id = a.client_id
                limit 0,1
            ) as customer_name,

            (
                select restaurant_name
                from {{merchant}}
                where merchant_id = a.merchant_id
                limit 0,1
            ) as restaurant_name	
            ";

            $criteria->addCondition('a.driver_id=:driver_id');				
			$criteria->params = array(':driver_id' => $driver_id );
           
            $criteria->order = "$sortby $sort";
            $count = AR_ordernew::model()->count($criteria); 
            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );        
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);    
            $page_count = $pages->getPageCount();

            if($page>0){
                if($page_raw>$page_count){
                    $this->code = 3;
                    $this->msg = t("end of results");
                    $this->responseJson();
                }
            }

            $data = [];
                        
            if($model = AR_ordernew::model()->findAll($criteria)){ 
                foreach ($model as $items) {                    
                    $data[]=array(
                        'transaction_date'=>Date_Formatter::date($items->date_created),
                        'order_id'=>$items->order_id,
                        'restaurant_name'=>$items->restaurant_name,
                        'customer_name'=>$items->customer_name,
                        'total'=>Price_Formatter::formatNumber($items->total),
                    );
                }
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'data'=>$data,                  
                ];
            } else $this->msg = t(HELPER_NO_RESULTS);

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }	
        $this->responseJson();     
    }

    public function actiondriverTipsTransaction()
    {
        $driver_id = 0;  $data = [];
		try {											
			$ref_id = Yii::app()->input->post('id');
			$driver_data = CDriver::getDriverByUUID($ref_id);
			$driver_id = $driver_data->driver_id;			
		} catch (Exception $e) {		   
		}	

        try {          

            $page = intval(Yii::app()->input->post('page'));  
            $search = trim(Yii::app()->input->post('q'));           
            $length =Yii::app()->params->list_limit;

            $sortby = "date_created"; $sort = 'DESC';
            
            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
                $page = $page-1;
            }

            $page = intval($page)/intval($length);
            $criteria=new CDbCriteria();     
            $criteria->alias ="a";		
            $criteria->select = "a.*,
            (
                select concat(first_name,' ',last_name)
                from {{client}}
                where client_id = a.client_id
                limit 0,1
            ) as customer_name,
    
            (
                select restaurant_name
                from {{merchant}}
                where merchant_id = a.merchant_id
                limit 0,1
            ) as restaurant_name	
            ";

            $criteria->addCondition('a.driver_id=:driver_id AND courier_tip>0');				
			$criteria->params = array(':driver_id' => $driver_id);
           
            $criteria->order = "$sortby $sort";
            $count = AR_ordernew::model()->count($criteria); 
            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );        
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);    
            $page_count = $pages->getPageCount();

            if($page>0){
                if($page_raw>$page_count){
                    $this->code = 3;
                    $this->msg = t("end of results");
                    $this->responseJson();
                }
            }

            $data = [];
                        
            if($model = AR_ordernew::model()->findAll($criteria)){ 
                foreach ($model as $items) {                    
                    $data[]=array(
                        'transaction_date'=>Date_Formatter::date($items->date_created),
                        'order_id'=>$items->order_id,
                        'restaurant_name'=>$items->restaurant_name,
					    'customer_name'=>$items->customer_name,
					    'total'=>Price_Formatter::formatNumber($items->courier_tip),
                    );
                }
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'data'=>$data,                  
                ];
            } else $this->msg = t(HELPER_NO_RESULTS);

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }	
        $this->responseJson();     
    }

    public function actiontimeLogs()
    {
        $driver_id = 0;  $data = [];
		try {											
			$ref_id = Yii::app()->input->post('id');
			$driver_data = CDriver::getDriverByUUID($ref_id);
			$driver_id = $driver_data->driver_id;			
		} catch (Exception $e) {		   
		}	

        try {          

            $page = intval(Yii::app()->input->post('page'));  
            $search = trim(Yii::app()->input->post('q'));           
            $length =Yii::app()->params->list_limit;

            $sortby = "date_created"; $sort = 'DESC';
            
            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
                $page = $page-1;
            }

            $page = intval($page)/intval($length);
            $criteria=new CDbCriteria();     
            $criteria->alias = "a";
            $criteria->select ="a.*, b.zone_name";
            $criteria->join='
            LEFT JOIN {{zones}} b on  a.zone_id = b.zone_id 		
            ';		

            $criteria->addCondition('driver_id=:driver_id AND on_demand=0');				
			$criteria->params = array(':driver_id' => $driver_id );
           
            $criteria->order = "$sortby $sort";
            $count = AR_driver_schedule::model()->count($criteria); 
            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );        
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);    
            $page_count = $pages->getPageCount();

            if($page>0){
                if($page_raw>$page_count){
                    $this->code = 3;
                    $this->msg = t("end of results");
                    $this->responseJson();
                }
            }

            $data = [];
                                    
            if($model = AR_driver_schedule::model()->findAll($criteria)){ 
                foreach ($model as $item) {                    
                    $data[]=array(
                        'schedule_id'=>$item->schedule_id,
                        'zone_id'=>$item->zone_name,
                        'date_created'=>Date_Formatter::date($item->time_start),
                        'time_start'=>Date_Formatter::Time($item->time_start),
                        'time_end'=>Date_Formatter::Time($item->time_end),
                        'shift_time_started'=>!empty($item->shift_time_started) ? Date_Formatter::Time($item->shift_time_started) : '',
                        'shift_time_ended'=>!empty($item->shift_time_ended)?Date_Formatter::Time($item->shift_time_ended): '',
                    );
                }
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'data'=>$data,                  
                ];
            } else $this->msg = t(HELPER_NO_RESULTS);

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }	
        $this->responseJson();     
    }
     
    public function actiondriverReviewList()
    {
        $driver_id = 0;  $data = [];
		try {											
			$ref_id = Yii::app()->input->post('id');
			$driver_data = CDriver::getDriverByUUID($ref_id);
			$driver_id = $driver_data->driver_id;			
		} catch (Exception $e) {		   
		}	

        try {          

            $page = intval(Yii::app()->input->post('page'));  
            $search = trim(Yii::app()->input->post('q'));           
            $length =Yii::app()->params->list_limit;

            $sortby = "date_created"; $sort = 'DESC';
            
            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
                $page = $page-1;
            }

            $page = intval($page)/intval($length);
            $criteria=new CDbCriteria();     
            $criteria->alias ="a";
            $criteria->select = "a.*,
            (
                select concat(first_name,' ',last_name)
                from {{client}}
                where client_id = a.client_id
                limit 0,1
            ) as customer_fullname,

            (
                select concat(first_name,' ',last_name,'|',driver_uuid)
                from {{driver}}
                where driver_id = a.driver_id
                limit 0,1
            ) as driver_fullname
            ";

            $criteria->addCondition('driver_id=:driver_id');				
			$criteria->params = array(':driver_id' => $driver_id );
           
            $criteria->order = "$sortby $sort";
            $count = AR_review::model()->count($criteria); 
            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );        
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);    
            $page_count = $pages->getPageCount();

            if($page>0){
                if($page_raw>$page_count){
                    $this->code = 3;
                    $this->msg = t("end of results");
                    $this->responseJson();
                }
            }

            $data = [];
                                    
            if($model = AR_review::model()->findAll($criteria)){ 
                $status_list = AttributesTools::StatusManagement('post');                
                foreach ($model as $item) {                    
                    $driver_name = !empty($item->driver_fullname)?explode("|",$item->driver_fullname):'';                    
                    $data[]=array(
                        'id'=>$item->id,
                        'driver_id'=>$item->driver_id,
                        'client_id'=>$item->client_id,
                        'customer_fullname'=>$item->customer_fullname,
                        'driver_fullname'=>isset($driver_name[0])?$driver_name[0]:t("Not available"),
                        'driver_uuid'=>isset($driver_name[1])?$driver_name[1]:'',
                        'review'=>$item->review,
                        'status_raw'=>$item->status,
                        'status'=>isset($status_list[$item->status])?$status_list[$item->status]:$item->status,
                        'rating'=>$item->rating,
                        'date_created'=>Date_Formatter::dateTime($item->date_created),
                    );
                }
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'data'=>$data,                  
                ];
            } else $this->msg = t(HELPER_NO_RESULTS);

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }	
        $this->responseJson();     
    }
    
    public function actionUpdateReviews()
    {
        try {            
            
            $id = isset($this->data['id'])?$this->data['id']:0;
            $review = isset($this->data['review'])?$this->data['review']:'';
            $rating = isset($this->data['rating'])?$this->data['rating']:0;
            $status = isset($this->data['status'])?$this->data['status']:'pending';

            $model = AR_review::model()->find("id=:id",[
                ':id'=>intval($id)
            ]);
            if($model){
                $model->review = $review;
                $model->rating = intval($rating);
                $model->status = $status;
                if($model->save()){
                    $this->code = 1;
                    $this->msg = t(Helper_success);
                } else $this->msg = CommonUtility::parseError($model->getErrors());
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actiondeleteReview()
    {
        try {            
            $id = Yii::app()->input->post('id');
            $model = AR_review::model()->find("id=:id",[
                ':id'=>intval($id)
            ]);
            if($model){
                $model->delete();
                $this->code = 1;
                $this->msg = "Ok";
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actioncarList()
    {
        try {
            $page = intval(Yii::app()->input->post('page'));
            $search = trim(Yii::app()->input->post('q'));             
            $length =Yii::app()->params->list_limit;

            $sortby = "date_created"; $sort = 'DESC';
            
            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
                $page = $page-1;
            }

            $page = intval($page)/intval($length);
            $criteria=new CDbCriteria();        
            $criteria->addCondition("driver_id=0 AND merchant_id=:merchant_id");
            $criteria->params = [
                ':merchant_id'=>intval(Yii::app()->merchant->merchant_id),		  
            ];
            
            $criteria->order = "$sortby $sort";
            $count = AR_driver_vehicle::model()->count($criteria); 
            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );        
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);    
            $page_count = $pages->getPageCount();

            if($page>0){
                if($page_raw>$page_count){
                    $this->code = 3;
                    $this->msg = t("end of results");
                    $this->responseJson();
                }
            }

            $data = [];            

            if($model = AR_driver_vehicle::model()->findAll($criteria)){ 
                $vehicle_maker = CommonUtility::getDataToDropDown("{{admin_meta}}","meta_id",'meta_value',"WHERE meta_name='vehicle_maker'","Order by meta_name");
		        $vehicle_type = CommonUtility::getDataToDropDown("{{admin_meta}}","meta_id",'meta_value',"WHERE meta_name='vehicle_type'","Order by meta_name");
                foreach ($model as $item) {
                    $avatar = CMedia::getImage($item->photo,$item->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('car','car.png'));
                    $data[] = [
                       'vehicle_uuid'=>$item->vehicle_uuid,	
                       'vehicle_id'=>$item->vehicle_id,		
                       'avatar'=>$avatar,				       
                       'plate_number'=>$item->plate_number,
                       'vehicle_type'=>isset($vehicle_type[$item->vehicle_type_id])?$vehicle_type[$item->vehicle_type_id]:'',			
                       'maker'=>isset($vehicle_maker[$item->maker])?$vehicle_maker[$item->maker]:'' ,				                        
                       'date_created'=>Date_Formatter::dateTime($item->date_created),
                    ];
                }
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
    
    public function actiondeleteCar()
    {
        try {            

            $id = Yii::app()->input->post('id');
            $model = AR_driver_vehicle::model()->find("vehicle_uuid=:vehicle_uuid",[
                ':vehicle_uuid'=>$id
            ]);
            if($model){
                $model->delete();
                $this->code = 1;
                $this->msg = "Ok";
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionUpdateCar(){
        $this->actionAddCar(true);
    }

    public function actionAddCar($update=false)
    {
        try {         

            if($update){
                $id = isset($this->data['id'])?trim($this->data['id']):'';
                $model = AR_driver_vehicle::model()->find('vehicle_uuid=:vehicle_uuid',[
                    ':vehicle_uuid'=>$id
                ]);                          
            } else $model = new AR_driver_vehicle();

            $model->merchant_id = Yii::app()->merchant->merchant_id;          
            $model->vehicle_type_id = isset($this->data['vehicle_type_id'])?intval($this->data['vehicle_type_id']):0;
            $model->plate_number = isset($this->data['plate_number'])?trim($this->data['plate_number']):'';            
            $model->maker = isset($this->data['maker'])?intval($this->data['maker']):0;
            $model->model = isset($this->data['model'])?trim($this->data['model']):'';
            $model->color = isset($this->data['color'])?trim($this->data['color']):'';
            $model->active = isset($this->data['active'])? ($this->data['active']==1?1:0) :0;

            $model->photo = isset($this->data['photo'])?$this->data['photo']:'';
            $model->path = isset($this->data['upload_path'])?$this->data['upload_path']:'';

            $file_data = isset($this->data['file_data'])?$this->data['file_data']:'';
		    $image_type = isset($this->data['image_type'])?$this->data['image_type']:'png';

            if(!empty($file_data)){
                $result = [];
                try {
                    $result = CImageUploader::saveBase64Image($file_data,$image_type,"upload/".Yii::app()->merchant->merchant_id);
                    $model->photo = isset($result['filename'])?$result['filename']:'';
                    $model->path = isset($result['path'])?$result['path']:'';
                } catch (Exception $e) {
                    $this->msg = t($e->getMessage());
                    $this->responseJson();
                }
            }

            if($model->save()){
                $this->code = 1;
                $this->msg = $update? t(Helper_update) : t(Helper_success);
            } else $this->msg = CommonUtility::parseError($model->getErrors());

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }   

    public function actiongetCarInfo()
    {
        try {         
                        
            $id = Yii::app()->input->post('id');
            $model = AR_driver_vehicle::model()->find("vehicle_uuid=:vehicle_uuid",[
                ':vehicle_uuid'=>$id
            ]);
            if($model){
                $this->code = 1;
                $this->msg = "Ok";

                $avatar = CMedia::getImage($model->photo,$model->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('car','car.png'));

                $this->details = [
                    'vehicle_type_id'=>intval($model->vehicle_type_id),
                    'avatar'=>$avatar,
                    'plate_number'=>$model->plate_number,
                    'maker'=>intval($model->maker),
                    'model'=>$model->model,
                    'color'=>$model->color,
                    'photo'=>$model->photo,
                    'path'=>$model->path,
                    'active'=>$model->active==1?true:false,
                ];
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }
    
    public function actionGroupList()
    {
        try {
            $page = intval(Yii::app()->input->post('page'));
            $search = trim(Yii::app()->input->post('q'));             
            $length =Yii::app()->params->list_limit;

            $sortby = "date_created"; $sort = 'DESC';
            
            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
                $page = $page-1;
            }

            $page = intval($page)/intval($length);
            $criteria=new CDbCriteria();        
            
            $criteria->alias ="a";		
            $criteria->select = "a.*,
            (
            select count(*) from {{driver_group_relations}}
            where group_id = a.group_id
            ) as drivers
            ";

            $criteria->condition = "merchant_id=:merchant_id";
            $criteria->params = [':merchant_id'=>Yii::app()->merchant->merchant_id];
            
            $criteria->order = "$sortby $sort";
            $count = AR_driver_group::model()->count($criteria); 
            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );        
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);    
            $page_count = $pages->getPageCount();

            if($page>0){
                if($page_raw>$page_count){
                    $this->code = 3;
                    $this->msg = t("end of results");
                    $this->responseJson();
                }
            }

            $data = [];            

            if($model = AR_driver_group::model()->findAll($criteria)){                 
                foreach ($model as $item) {                    
                    $data[] = [
                       'group_id'=>$item->group_id,
                       'group_uuid'=>$item->group_uuid,
				       'group_name'=>$item->group_name,
				       'drivers'=>$item->drivers,
                       'date_created'=>Date_Formatter::dateTime($item->date_created),
                    ];
                }
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

    public function actiondeleteGroup()
    {
        try {            

            $id = Yii::app()->input->post('id');
            $model = AR_driver_group::model()->find("group_uuid=:group_uuid",[
                ':group_uuid'=>$id
            ]);
            if($model){
                $model->delete();
                $this->code = 1;
                $this->msg = "Ok";
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionSelectDriverList()
    {
        try {           
            
            $drivers = CommonUtility::getDataToDropDown("{{driver}}",'driver_id',"concat(first_name,' ',last_name)",
            "WHERE status='active' AND merchant_id=".q(Yii::app()->merchant->merchant_id)."
            ","ORDER BY first_name");	

            if(is_array($drivers) && count($drivers)>=1){
                $drivers = CommonUtility::ArrayToLabelValue($drivers);
            }
            
            $this->code = 1;
            $this->msg = "Ok";
            $this->details = $drivers;

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionUpdateGroup(){
        $this->actionAddGroup(true);
    }

    public function actionAddGroup($update=false)
    {
        try {            
                        
            if($update){
                $id = isset($this->data['id'])?trim($this->data['id']):'';
                $model = AR_driver_group::model()->find('group_uuid=:group_uuid',[
                    ':group_uuid'=>$id
                ]);                          
            } else $model = new AR_driver_group();

            $model->merchant_id = Yii::app()->merchant->merchant_id;
            $model->group_name = isset($this->data['group_name'])?$this->data['group_name']:'';
            $model->drivers = isset($this->data['drivers'])?$this->data['drivers']:'';

            if($model->save()){
                $this->code = 1;
                $this->msg = $update? t(Helper_update) : t(Helper_success);
            } else $this->msg = CommonUtility::parseError($model->getErrors());

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actiongetGroup()
    {
        try {            

            $id = Yii::app()->input->post('id');            
            $model = AR_driver_group::model()->find('group_uuid=:group_uuid',[
                ':group_uuid'=>$id
            ]);               
            if($model){                
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'group_name'=>$model->group_name,
                    'drivers'=>CDriver::GetGroups($model->group_id),
                    'color_hex'=>$model->color_hex,                    
                ];
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }
   
    public function actionZoneList()
    {
        try {
            $page = intval(Yii::app()->input->post('page'));
            $search = trim(Yii::app()->input->post('q'));             
            $length =Yii::app()->params->list_limit;

            $sortby = "date_created"; $sort = 'DESC';
            
            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
                $page = $page-1;
            }

            $page = intval($page)/intval($length);
            $criteria=new CDbCriteria();        
            
            $criteria=new CDbCriteria();			
            $criteria->condition = "merchant_id=:merchant_id";		
            $criteria->params = [
                ':merchant_id'=> Yii::app()->merchant->merchant_id
            ];
            
            $criteria->order = "$sortby $sort";
            $count = AR_zones::model()->count($criteria); 
            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );        
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);    
            $page_count = $pages->getPageCount();

            if($page>0){
                if($page_raw>$page_count){
                    $this->code = 3;
                    $this->msg = t("end of results");
                    $this->responseJson();
                }
            }

            $data = [];            

            if($model = AR_zones::model()->findAll($criteria)){                 
                foreach ($model as $item) {                    
                    $data[] = [
                        'zone_id'=>$item->zone_id,
                        'zone_uuid'=>$item->zone_uuid,
                        'zone_name'=>$item->zone_name,
                        'description'=>$item->description,                        
                        'date_created'=>Date_Formatter::dateTime($item->date_created),
                    ];
                }
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

    public function actionUpdateZone(){
        $this->actionAddZone(true);
    }

    public function actionAddZone($update=false)
    {
        try {            
            
            if($update){
                $id = isset($this->data['id'])?trim($this->data['id']):'';
                $model = AR_zones::model()->find('zone_uuid=:zone_uuid',[
                    ':zone_uuid'=>$id
                ]);                          
            } else $model = new AR_zones();

            $model->merchant_id = Yii::app()->merchant->merchant_id;
            $model->zone_name = isset($this->data['zone_name'])?$this->data['zone_name']:'';
            $model->description = isset($this->data['description'])?$this->data['description']:'';

            if($model->save()){
                $this->code = 1;
                $this->msg = $update? t(Helper_update) : t(Helper_success);
            } else $this->msg = CommonUtility::parseError($model->getErrors());

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actiondeleteZones()
    {
        try {            

            $id = Yii::app()->input->post('id');
            $model = AR_zones::model()->find('zone_uuid=:zone_uuid',[
                ':zone_uuid'=>$id
            ]);       
            if($model){
                $model->delete();
                $this->code = 1;
                $this->msg = "Ok";
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actiongetZone()
    {
        try {       
            
            $id = Yii::app()->input->post('id');
            $model = AR_zones::model()->find('zone_uuid=:zone_uuid',[
                ':zone_uuid'=>$id
            ]);
            if($model){
                $this->code = 1; 
                $this->msg = "Ok";
                $this->details = [
                    'zone_name'=>$model->zone_name,
                    'description'=>$model->description,
                ];
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionScheduleList()
    {
        try {
            $page = intval(Yii::app()->input->post('page'));
            $search = trim(Yii::app()->input->post('q'));             
            $length =Yii::app()->params->list_limit;

            $sortby = "date_created"; $sort = 'DESC';
            
            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
                $page = $page-1;
            }

            $page = intval($page)/intval($length);
            $criteria=new CDbCriteria();                  
                    		
            $criteria->select = "a.*,
			(
				select concat(first_name,' ',last_name,'|',color_hex,'|',photo,'|',path)
				from {{driver}}
				where driver_id = a.driver_id
			) as fullname,
			(
				select plate_number
				from {{driver_vehicle}}
				where vehicle_id = a.vehicle_id
			) as plate_number			
			";
			$criteria->alias = "a";
			$criteria->addCondition("active=:active AND merchant_id=:merchant_id
			AND a.driver_id IN (
				select driver_id from {{driver}}
				where employment_type='employee'
			)
			");
		    $criteria->params = array(
				':active' => 1 ,
				':merchant_id'=>Yii::app()->merchant->merchant_id
			);
                        
            $criteria->order = "$sortby $sort";
            $count = AR_driver_schedule::model()->count($criteria); 
            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );        
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);    
            $page_count = $pages->getPageCount();

            if($page>0){
                if($page_raw>$page_count){
                    $this->code = 3;
                    $this->msg = t("end of results");
                    $this->responseJson();
                }
            }

            $data = [];            
            
            if($model = AR_driver_schedule::model()->findAll($criteria)){                           
                $driver_list = CommonUtility::getDataToDropDown("{{driver}}",'driver_id',"concat(first_name,' ',last_name)",
                "WHERE status='active' AND merchant_id=".q(Yii::app()->merchant->merchant_id)."
                ","ORDER BY first_name");	
                $zone_list = CommonUtility::getDataToDropDown("{{zones}}","zone_id","zone_name","WHERE merchant_id=".q(Yii::app()->merchant->merchant_id)." ");                
                foreach ($model as $item) {                    
                    $data[] = [
                        'schedule_id'=>$item->schedule_id,
                        'schedule_uuid'=>$item->schedule_uuid,
                        'driver_name'=>isset($driver_list[$item->driver_id])?$driver_list[$item->driver_id]:t("Not available"),
                        'zone_name'=>isset($zone_list[$item->zone_id])?$zone_list[$item->zone_id]:t("Not available"),
                        'time_start'=>Date_Formatter::Time($item->time_start),
                        'time_end'=>Date_Formatter::Time($item->time_end),
                        'date_created'=>Date_Formatter::dateTime($item->date_created),
                    ];
                }                
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

    public function actionScheduleAttributes()
    {
        try {            

            $employment_type = Yii::app()->input->post('employment_type');

            $driver_list = CommonUtility::getDataToDropDown("{{driver}}",'driver_id',"concat(first_name,' ',last_name)",
                "WHERE status='active' AND merchant_id=".q(Yii::app()->merchant->merchant_id)."
                AND employment_type=".q($employment_type)."
                ","ORDER BY first_name");	
            $zone_list = CommonUtility::getDataToDropDown("{{zones}}","zone_id","zone_name","WHERE merchant_id=".q(Yii::app()->merchant->merchant_id)." ");

            $time_range = AttributesTools::createTimeRange("00:00","24:00");

            $vehicle_list = CommonUtility::getDataToDropDown("{{driver_vehicle}}","vehicle_id","plate_number",
              "WHERE driver_id=0 AND merchant_id=".q(Yii::app()->merchant->merchant_id)." "
            );

            if(is_array($driver_list) && count($driver_list)>=1){
                $driver_list = CommonUtility::ArrayToLabelValue($driver_list);
            }
            if(is_array($zone_list) && count($zone_list)>=1){
                $zone_list = CommonUtility::ArrayToLabelValue($zone_list);
            }
            if(is_array($time_range) && count($time_range)>=1){
                $time_range = CommonUtility::ArrayToLabelValue($time_range);
            }
            if(is_array($vehicle_list) && count($vehicle_list)>=1){
                $vehicle_list = CommonUtility::ArrayToLabelValue($vehicle_list);
            }

            $this->code = 1; $this->msg = "Ok";
            $this->details = [
                'driver_list'=>$driver_list,
                'zone_list'=>$zone_list,
                'time_range'=>$time_range,
                'vehicle_list'=>$vehicle_list
            ];
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionUpdateSchedule()
    {
        $this->actionAddSchedule(true);
    }

    public function actionAddSchedule($update=false)
    {
        try {            
            
            $id = isset($this->data['id'])?trim($this->data['id']):null;	
            $zone_id = isset($this->data['zone_id'])?intval($this->data['zone_id']):0;		
            $driver_id = isset($this->data['driver_id'])?intval($this->data['driver_id']):0;
			$vehicle_id = isset($this->data['vehicle_id'])?intval($this->data['vehicle_id']):0;
			$date_start = isset($this->data['date_start'])? date("Y-m-d",strtotime($this->data['date_start'])) :null;
			$time_start = isset($this->data['time_start'])?$this->data['time_start']:null;
			$time_end = isset($this->data['time_end'])?$this->data['time_end']:null;
			$instructions = isset($this->data['instructions'])?$this->data['instructions']:'';			

            if($update){
                $model = AR_driver_schedule::model()->find("schedule_uuid=:schedule_uuid",[
					':schedule_uuid'=>$id
				]);
				if(!$model){
					$this->msg = t(HELPER_RECORD_NOT_FOUND);
					$this->responseJson();	
				}
            } else $model = new AR_driver_schedule;      
            
            $model->merchant_id = Yii::app()->merchant->merchant_id;
			$model->zone_id = $zone_id;
			$model->driver_id  = $driver_id;
			$model->vehicle_id  = $vehicle_id;			
			$model->time_start  = "$date_start $time_start";
			$model->time_end  = "$date_start $time_end";
			$model->instructions = $instructions;			
			if($model->save()){
				$this->code = 1;
				$this->msg = !empty($schedule_uuid)? t("Schedule updated") :   t("Schedule added");
			} else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actiondeleteSchedule()
    {
        try {            

            $id = Yii::app()->input->post('id');
            $model = AR_driver_schedule::model()->find('schedule_uuid=:schedule_uuid',[
                ':schedule_uuid'=>$id
            ]);       
            if($model){
                $model->delete();
                $this->code = 1;
                $this->msg = "Ok";
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actiongetSchedule()
    {
        try {            

            $id = Yii::app()->input->post('id');
            $model = AR_driver_schedule::model()->find('schedule_uuid=:schedule_uuid',[
                ':schedule_uuid'=>$id
            ]);       
            if($model){                
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'zone_id'=>intval($model->zone_id),
                    'driver_id'=>intval($model->driver_id),
                    'vehicle_id'=>intval($model->vehicle_id),
                    'date_start'=>date("Y/m/d",strtotime($model->time_start)),
                    'time_start'=>date("H:i:s",strtotime($model->time_start)),
                    'time_end'=>date("H:i:s",strtotime($model->time_end)),
                    'instructions'=>$model->instructions,
                ];
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionShiftList()
    {
        try {
            $page = intval(Yii::app()->input->post('page'));
            $search = trim(Yii::app()->input->post('q'));             
            $length =Yii::app()->params->list_limit;

            $sortby = "date_created"; $sort = 'DESC';
            
            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
                $page = $page-1;
            }

            $page = intval($page)/intval($length);            
            $criteria=new CDbCriteria();			
            $criteria->condition = "merchant_id=:merchant_id";		
            $criteria->params = [
                ':merchant_id'=> Yii::app()->merchant->merchant_id
            ];
            
            $criteria->order = "$sortby $sort";
            $count = AR_driver_shift_schedule::model()->count($criteria); 
            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );        
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);    
            $page_count = $pages->getPageCount();

            if($page>0){
                if($page_raw>$page_count){
                    $this->code = 3;
                    $this->msg = t("end of results");
                    $this->responseJson();
                }
            }

            $data = [];            

            if($model = AR_driver_shift_schedule::model()->findAll($criteria)){                                           
                $zone_list = CommonUtility::getDataToDropDown("{{zones}}","zone_id","zone_name","WHERE merchant_id=".q(Yii::app()->merchant->merchant_id)." ");                
                $status_list = AttributesTools::StatusManagement('post');
                foreach ($model as $item) {                    
                    $data[] = [
                        'shift_id'=>$item->shift_id,
                        'shift_uuid'=>$item->shift_uuid,
                        'zone_name'=>isset($zone_list[$item->zone_id])?$zone_list[$item->zone_id]:t("Not available"),
                        'time_start'=>Date_Formatter::dateTime($item->time_start),
                        'time_end'=>Date_Formatter::dateTime($item->time_end),
                        'max_allow_slot'=>$item->max_allow_slot>0?$item->max_allow_slot:t("unlimited"),
                        'status_raw'=>$item->status,
                        'status'=>isset($status_list[$item->status])?$status_list[$item->status]:$item->status,
                        'date_created'=>Date_Formatter::dateTime($item->date_created),
                    ];
                }                
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

    public function actiondeleteShiftSchedule()
    {
        try {            

            $id = Yii::app()->input->post('id');
            $model = AR_driver_shift_schedule::model()->find('shift_uuid=:shift_uuid',[
                ':shift_uuid'=>$id
            ]);       
            if($model){
                $model->delete();
                $this->code = 1;
                $this->msg = "Ok";
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionUpdateShiftSchedule()
    {
        $this->actionAddShiftSchedule(true);
    }
    
    public function actionAddShiftSchedule($update=false)
    {
        try {            
            
            if($update){
                $id = isset($this->data['id'])?trim($this->data['id']):null;	
                $model = AR_driver_shift_schedule::model()->find("shift_uuid=:shift_uuid",[
					':shift_uuid'=>$id
				]);
				if(!$model){
					$this->msg = t(HELPER_RECORD_NOT_FOUND);
					$this->responseJson();	
				}
            } else $model = new AR_driver_shift_schedule();      
            
            $date_shift = isset($this->data['date_shift'])?$this->data['date_shift']:null; 
            $time_start = isset($this->data['time_start'])?$this->data['time_start']:null; 
            
            $date_shift_end = isset($this->data['date_shift_end'])?$this->data['date_shift_end']:null; 
            $time_end = isset($this->data['time_end'])?$this->data['time_end']:null; 

            $model->merchant_id = Yii::app()->merchant->merchant_id;
			$model->zone_id = isset($this->data['zone_id'])?$this->data['zone_id']:0;
            $model->date_shift = $date_shift;
            $model->date_shift_end = $date_shift_end;
            $model->time_start = $time_start;
            $model->time_end = $time_end;
            $model->max_allow_slot = isset($this->data['max_allow_slot'])?intval($this->data['max_allow_slot']):0;
            $model->status = isset($this->data['status'])?$this->data['status']:'active';
            
			if($model->save()){
				$this->code = 1;
				$this->msg = !empty($schedule_uuid)? t("Schedule updated") :   t("Schedule added");
			} else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actiongetShiftSchedule()
    {
        try {            

            $id = Yii::app()->input->post('id');
            $model = AR_driver_shift_schedule::model()->find('shift_uuid=:shift_uuid',[
                ':shift_uuid'=>$id
            ]);       
            if($model){            
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'zone_id'=>intval($model->zone_id),
                    'date_shift'=>date("Y/m/d",strtotime($model->time_start)),
                    'date_shift_end'=>date("Y/m/d",strtotime($model->time_end)),
                    'time_start'=>date("H:i:s",strtotime($model->time_start)),
                    'time_end'=>date("H:i:s",strtotime($model->time_end)),
                    'max_allow_slot'=>intval($model->max_allow_slot),
                    'status'=>$model->status
                ];
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionReviewList()
    {
        try {
            $page = intval(Yii::app()->input->post('page'));
            $search = trim(Yii::app()->input->post('q'));             
            $length =Yii::app()->params->list_limit;

            $sortby = "date_created"; $sort = 'DESC';
            
            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
                $page = $page-1;
            }

            $page = intval($page)/intval($length);
            $criteria=new CDbCriteria();                  
            $criteria->alias ="a";
            $criteria->select = "a.*,
            (
                select concat(first_name,' ',last_name)
                from {{client}}
                where client_id = a.client_id
                limit 0,1
            ) as customer_fullname,
    
            (
                select concat(first_name,' ',last_name,'|',driver_uuid)
                from {{driver}}
                where driver_id = a.driver_id
                limit 0,1
            ) as driver_fullname
            ";            
            
            $criteria->condition = "merchant_id=:merchant_id AND a.driver_id>0";		
            $criteria->params = [
                ':merchant_id'=> Yii::app()->merchant->merchant_id
            ];
            
            $criteria->order = "$sortby $sort";
            $count = AR_review::model()->count($criteria); 
            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );        
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);    
            $page_count = $pages->getPageCount();

            if($page>0){
                if($page_raw>$page_count){
                    $this->code = 3;
                    $this->msg = t("end of results");
                    $this->responseJson();
                }
            }

            $data = [];            
            
            if($model = AR_review::model()->findAll($criteria)){                                                           
                $status_list = AttributesTools::StatusManagement('post');
                foreach ($model as $item) {                    
                    $data[] = [                        
                        'id'=>$item->id,
                        'driver_id'=>$item->driver_id,
                        'client_id'=>$item->client_id,
                        'customer_fullname'=>$item->customer_fullname,
                        'driver_fullname'=>isset($driver_name[0])?$driver_name[0]:t("Not available"),
                        'driver_uuid'=>isset($driver_name[1])?$driver_name[1]:'',
                        'review'=>$item->review,
                        'status_raw'=>$item->status,
                        'status'=>isset($status_list[$item->status])?$status_list[$item->status]:$item->status,
                        'rating'=>$item->rating,
                        'date_created'=>Date_Formatter::dateTime($item->date_created),
                    ];
                }                
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

    public function actiongetReviewDetails()
    {
        try {            

            $id = Yii::app()->input->post('id');
            $model = AR_review::model()->find('id=:id',[
                ':id'=>$id
            ]);       
            if($model){            
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'review'=>$model->review,
                    'rating'=>$model->rating,
                    'status'=>$model->status,
                ];
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actiongetDeliverySettings()
    {
        try {            

            $merchant_id = Yii::app()->merchant->merchant_id;

            $options = array('merchant_delivery_charges_type',
		      'merchant_opt_contact_delivery','free_delivery_on_first_order','merchant_charge_type','merchant_small_order_fee','merchant_small_less_order_based',
              'merchant_service_fee'
		    );

            $data = OptionsTools::find($options,$merchant_id);            
            $merchant_opt_contact_delivery = isset($data['merchant_opt_contact_delivery'])?$data['merchant_opt_contact_delivery']:'';
            $free_delivery_on_first_order = isset($data['free_delivery_on_first_order'])?$data['free_delivery_on_first_order']:'';
            $merchant_delivery_charges_type = isset($data['merchant_delivery_charges_type'])?$data['merchant_delivery_charges_type']:'';

            $merchant_charge_type = isset($data['merchant_charge_type'])?$data['merchant_charge_type']:'';
            $merchant_service_fee = isset($data['merchant_service_fee'])?floatval($data['merchant_service_fee']):0;
            $merchant_small_order_fee =  isset($data['merchant_small_order_fee'])?floatval($data['merchant_small_order_fee']):0;
            $merchant_small_less_order_based =  isset($data['merchant_small_less_order_based'])?floatval($data['merchant_small_less_order_based']):0;

            $service_code = 'delivery';
            $charge_type = 'fixed';
            $shipping_type = 'standard';

            $distance_price = 0;
            $estimation = '';
            $minimum_order = 0;
            $maximum_order = 0;
                        
            $model = AR_shipping_rate::model()->find('merchant_id=:merchant_id AND charge_type=:charge_type
            AND shipping_type=:shipping_type AND service_code=:service_code
            ', 
            array(':merchant_id'=>$merchant_id, 
                ':charge_type'=>$charge_type,
                ':shipping_type'=>$shipping_type,
                ':service_code'=>$service_code,
            ));
            if($model){
                $distance_price = $model->distance_price>0? Price_Formatter::convertToRaw($model->distance_price,2) :'';
		        $minimum_order = $model->minimum_order>0? Price_Formatter::convertToRaw($model->minimum_order,2) :'';
		        $maximum_order = $model->maximum_order>0? Price_Formatter::convertToRaw($model->maximum_order,2) :'';
                $estimation = $model->estimation;
            }           

            $this->code = 1; $this->msg = "Ok";
            $this->details = [
                'merchant_opt_contact_delivery'=>$merchant_opt_contact_delivery==1?true:false,
                'free_delivery_on_first_order'=>$free_delivery_on_first_order==1?true:false,
                'merchant_delivery_charges_type'=>$merchant_delivery_charges_type,
                'distance_price'=>$distance_price,
                'estimation'=>$estimation,
                'minimum_order'=>$minimum_order,
                'maximum_order'=>$maximum_order,
                'merchant_charge_type'=>$merchant_charge_type,
                'merchant_service_fee'=>$merchant_service_fee,
                'merchant_small_order_fee'=>$merchant_small_order_fee,
                'merchant_small_less_order_based'=>$merchant_small_less_order_based,
            ];
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionSaveDeliverySettings()
    {
        try {            

            $model=new AR_option;
		    $model->scenario = 'delivery_settings';

            $merchant_id = Yii::app()->merchant->merchant_id;
            $options = ['merchant_opt_contact_delivery',
              'free_delivery_on_first_order', 'merchant_delivery_charges_type','merchant_charge_type','merchant_service_fee',
              'merchant_small_order_fee','merchant_small_less_order_based'
            ];
            
            $model->merchant_opt_contact_delivery = isset($this->data['merchant_opt_contact_delivery'])?$this->data['merchant_opt_contact_delivery']:'';
            $model->free_delivery_on_first_order = isset($this->data['free_delivery_on_first_order'])?$this->data['free_delivery_on_first_order']:'';
            $model->merchant_delivery_charges_type = isset($this->data['merchant_delivery_charges_type'])?$this->data['merchant_delivery_charges_type']:'';

            $merchant_charge_type = isset($this->data['merchant_charge_type'])?$this->data['merchant_charge_type']:'';
            $merchant_service_fee = isset($this->data['merchant_service_fee'])?floatval($this->data['merchant_service_fee']):0;
            $merchant_small_order_fee = isset($this->data['merchant_small_order_fee'])?floatval($this->data['merchant_small_order_fee']):0;
            $merchant_small_less_order_based = isset($this->data['merchant_small_less_order_based'])?floatval($this->data['merchant_small_less_order_based']):0;                    

            $model->merchant_charge_type = $merchant_charge_type;
            $model->merchant_service_fee = $merchant_service_fee;
            $model->merchant_small_order_fee = $merchant_small_order_fee;
            $model->merchant_small_less_order_based = $merchant_small_less_order_based;

            $merchant_type = MerchantAR::getMerchantType();
            if($merchant_type==1 || $merchant_type==3){
                $service_id = AR_services::getID('delivery');		 
                $fee = AR_services_fee::model()->find('merchant_id=:merchant_id AND service_id=:service_id', 
                           array(':merchant_id'=>$merchant_id, ':service_id'=>$service_id ));
                if($fee){         	
                    //$model->merchant_service_fee = Price_Formatter::convertToRaw($fee->service_fee,2,true);
                }
            }

            OptionsTools::$merchant_id = $merchant_id;
            if(OptionsTools::save($options, $model, $merchant_id)){

                if($merchant_type==1 || $merchant_type==3){
                    if(!$fee){
                        $fee = new AR_services_fee;			
                    } 
                    $fee->service_id = intval($service_id);
                    $fee->merchant_id = intval($merchant_id);
                    $fee->charge_type = $merchant_charge_type;
                    $fee->service_fee = $merchant_service_fee;
                    $fee->small_order_fee = $merchant_small_order_fee;
                    $fee->small_less_order_based = $merchant_small_less_order_based;
                    $fee->save();	
                }


                $service_code = 'delivery';
                $charge_type = 'fixed';
                $shipping_type = 'standard';
                                
                $model_shipping = AR_shipping_rate::model()->find('merchant_id=:merchant_id AND charge_type=:charge_type
                AND shipping_type=:shipping_type AND service_code=:service_code
                ', 
                array(':merchant_id'=>$merchant_id, 
                    ':charge_type'=>$charge_type,
                    ':shipping_type'=>$shipping_type,
                    ':service_code'=>$service_code,
                ));                
                if(!$model_shipping){                    
                    $model_shipping = new AR_shipping_rate;
                }                                
                $model_shipping->scenario = 'fixed';
                $model_shipping->distance_price = isset($this->data['distance_price'])?$this->data['distance_price']:0;
                $model_shipping->estimation = isset($this->data['estimation'])?$this->data['estimation']:'';
                $model_shipping->minimum_order = isset($this->data['minimum_order'])?$this->data['minimum_order']:0;
                $model_shipping->maximum_order = isset($this->data['maximum_order'])?$this->data['maximum_order']:0;
                if($model_shipping->validate()){
                   $model_shipping->merchant_id = $merchant_id;				
				   $model_shipping->charge_type = $charge_type;
                   if($model_shipping->save()){
                   }
                }

                $this->code = 1; 
                $this->msg = t(Helper_settings_saved);
            } else $this->msg = t(Helper_failed_save);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionShippingRateList()
    {
        try {
            $page = intval(Yii::app()->input->post('page'));
            $search = trim(Yii::app()->input->post('q'));             
            $length =Yii::app()->params->list_limit;

            $sortby = "distance_from"; $sort = 'DESC';
            
            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
                $page = $page-1;
            }

            $page = intval($page)/intval($length);            
            $criteria=new CDbCriteria();			
            $criteria->condition = "merchant_id=:merchant_id AND charge_type=:charge_type AND service_code=:service_code";		
            $criteria->params = [
                ':merchant_id'=> Yii::app()->merchant->merchant_id,
                ':charge_type'=>'dynamic',
                ':service_code'=>'delivery'
            ];
            
            $criteria->order = "$sortby $sort";
            $count = AR_shipping_rate::model()->count($criteria); 
            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );        
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);    
            $page_count = $pages->getPageCount();

            if($page>0){
                if($page_raw>$page_count){
                    $this->code = 3;
                    $this->msg = t("end of results");
                    $this->responseJson();
                }
            }

            $data = [];            

            if($model = AR_shipping_rate::model()->findAll($criteria)){             
                $units = AttributesTools::unit();                                              
                foreach ($model as $item) {                    
                    $data[] = [
                        'id'=>$item->id,           
                        'shipping_type'=>$item->shipping_type,
                        'distance'=>t("{from} - {to} {unit}",[
                            '{from}'=>intval($item->distance_from),
                            '{to}'=>intval($item->distance_to),
                            '{unit}'=>isset($units[$item->shipping_units])?$units[$item->shipping_units]:$item->shipping_units,
                        ]),
                        'distance_price'=>Price_Formatter::formatNumber($item->distance_price),
                        'estimation'=>t("{estimation} mins",['{estimation}'=>$item->estimation]),
                        'last_update'=>Date_Formatter::dateTime($item->last_update),
                    ];
                }                
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

    public function actionUpdateDynamicRates()
    {
        $this->actionAddDynamicRates(true);
    }

    public function actionAddDynamicRates($update=false)
    {
        try {            

            $merchant_id = Yii::app()->merchant->merchant_id;
            $id = isset($this->data['id'])?$this->data['id']:null;

            $model=new AR_shipping_rate;
            if($update){
                $model = AR_shipping_rate::model()->find('merchant_id=:merchant_id AND id=:id', 
                array(':merchant_id'=>$merchant_id, ':id'=>$id ));
                if(!$model){				
                    $this->msg = t(HELPER_RECORD_NOT_FOUND);
                    $this->responseJson();  
                }						
            }

            $units = 'mi';
            $merchant = AR_merchant::model()->findByPk( $merchant_id );
            if($merchant){			
                if(!empty($merchant->distance_unit)){
                    $units = $merchant->distance_unit;
                }
            }            

            $model->scenario = 'dynamic';
            $model->merchant_id = $merchant_id;
            $model->shipping_type = isset($this->data['shipping_type'])?$this->data['shipping_type']:'';
            $model->distance_from = isset($this->data['distance_from'])?intval($this->data['distance_from']):0;
            $model->distance_to = isset($this->data['distance_to'])?intval($this->data['distance_to']):0;
            $model->distance_price = isset($this->data['distance_price'])?floatval($this->data['distance_price']):0;
            $model->estimation = isset($this->data['estimation'])?$this->data['estimation']:'';
            $model->minimum_order = isset($this->data['minimum_order'])?floatval($this->data['minimum_order']):0;
            $model->maximum_order = isset($this->data['maximum_order'])?floatval($this->data['maximum_order']):0;
            $model->shipping_units = $units;
            
            if($model->validate()){				
                if($model->save()){
                    $this->code = 1;
                    $this->msg = $update?t(Helper_update):t(Helper_success);
                } else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
            } else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actiondeleteShippingRate()
    {
        try {            
    
            $id = Yii::app()->input->post('id');
            $merchant_id = (integer) Yii::app()->merchant->merchant_id;

            $model = AR_shipping_rate::model()->find('merchant_id=:merchant_id AND id=:id',
		    array(':merchant_id'=>$merchant_id, ':id'=>$id ));
            if($model){
                $model->delete();
                $this->code = 1;
                $this->msg = "Ok";
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actiongetDynamicRates()
    {
        try {            

            $id = Yii::app()->input->post('id');
            $merchant_id = (integer) Yii::app()->merchant->merchant_id;

            $model = AR_shipping_rate::model()->find('merchant_id=:merchant_id AND id=:id',
		    array(':merchant_id'=>$merchant_id, ':id'=>$id ));
            if($model){                
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'shipping_type'=>$model->shipping_type,
                    'distance_from'=>$model->distance_from,
                    'distance_to'=>$model->distance_to,
                    'distance_price'=>Price_Formatter::convertToRaw($model->distance_price),
                    'estimation'=>$model->estimation,
                    'minimum_order'=>Price_Formatter::convertToRaw($model->minimum_order),
                    'maximum_order'=>Price_Formatter::convertToRaw($model->maximum_order),
                ];
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actiongetOrderTypeSettings()
    {
        try {            

            $merchant_id = (integer) Yii::app()->merchant->merchant_id;

            $service_code = Yii::app()->input->post('service_code');
            $instructions_type = Yii::app()->input->post('instructions_type');

            $estimation = ''; $minimum_order=0; $maximum_order=0; $instructions='';
				
            $model = AR_shipping_rate::model()->find('merchant_id=:merchant_id AND service_code=:service_code', 
            array(':merchant_id'=>$merchant_id, 		    
                ':service_code'=>$service_code,
            ));
            if($model){
                $estimation = $model->estimation;
                $minimum_order = $model->minimum_order;
                $maximum_order = $model->maximum_order;
            }

            $meta_name = $instructions_type;
            $model_meta = AR_merchant_meta::model()->find("merchant_id=:merchant_id AND meta_name=:meta_name",array(
            ':merchant_id'=>intval($merchant_id),
            ':meta_name'=>$meta_name
            ));
            if($model_meta){                
                $instructions = $model_meta->meta_value;
            }

            // SERVICE FEE
            $merchant_service_fee = 0;
            $merchant_type = MerchantAR::getMerchantType();
            if($merchant_type==1 || $merchant_type==3){
                $service_id = AR_services::getID($service_code); 
                $fee = AR_services_fee::model()->find('merchant_id=:merchant_id AND service_id=:service_id', 
                            array(':merchant_id'=>$merchant_id, ':service_id'=>$service_id ));
                if($fee){
                    $merchant_service_fee = Price_Formatter::convertToRaw($fee->service_fee,2,true);
                }
            }

            $this->code = 1; 
            $this->msg = "Ok";
            $this->details = [
                'estimation'=>$estimation,
                'minimum_order'=>$minimum_order>0?Price_Formatter::convertToRaw($minimum_order):'',
                'maximum_order'=>$maximum_order>0?Price_Formatter::convertToRaw($maximum_order):'',
                'instructions'=>$instructions,
                'merchant_service_fee'=>$merchant_service_fee>0?Price_Formatter::convertToRaw($merchant_service_fee):'',
            ];

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionsaveOrderTypeSettings()
    {
        try {            

            $merchant_id = (integer) Yii::app()->merchant->merchant_id;
            $service_code = isset($this->data['service_code'])?$this->data['service_code']:'';
            $instructions_type = isset($this->data['instructions_type'])?$this->data['instructions_type']:'';            

            $model = AR_shipping_rate::model()->find('merchant_id=:merchant_id AND service_code=:service_code', 
		    array(':merchant_id'=>$merchant_id, 		    
		    ':service_code'=>$service_code,
		   ));

           if(!$model){		
			  $model = new AR_shipping_rate; 
		   } 

           $merchant_type = MerchantAR::getMerchantType();
           if($merchant_type==1 || $merchant_type==3){                 
                $service_id = AR_services::getID($service_code);                
                $fee = AR_services_fee::model()->find('merchant_id=:merchant_id AND service_id=:service_id', 
                            array(':merchant_id'=>$merchant_id, ':service_id'=>$service_id ));
                if($fee){         	
                    $model->merchant_service_fee = Price_Formatter::convertToRaw($fee->service_fee,2,true);
                }
            }

            if($merchant_type==1 || $merchant_type==3){
                $model->scenario = 'fixed';
            }
            $model->merchant_id = $merchant_id;				
			$model->service_code = $service_code;
			$model->charge_type = 'fixed';		
            
            $model->estimation = isset($this->data['estimation'])?$this->data['estimation']:'';
            $model->minimum_order = isset($this->data['minimum_order'])?floatval($this->data['minimum_order']):0;
            $model->maximum_order = isset($this->data['maximum_order'])?floatval($this->data['maximum_order']):0;
            $instructions = isset($this->data['instructions'])?$this->data['instructions']:'';
            $model->merchant_service_fee = isset($this->data['merchant_service_fee'])?floatval($this->data['merchant_service_fee']):0;

            if($model->save()){

                if($merchant_type==1 || $merchant_type==3){		
                    if(!$fee){
                        $fee = new AR_services_fee;			
                    } 					
                    $fee->service_id = intval($service_id);
                    $fee->merchant_id = intval($merchant_id);
                    $fee->service_fee = floatval($model->merchant_service_fee);
                    $fee->save();	
                }

                $meta_name = $instructions_type;                
                $model_meta = AR_merchant_meta::model()->find("merchant_id=:merchant_id AND meta_name=:meta_name",array(
                ':merchant_id'=>intval($merchant_id),
                ':meta_name'=>$meta_name
                ));
                if(!$model_meta){                            
                    $model_meta = new AR_merchant_meta;
                }
                $model_meta->merchant_id = $merchant_id;
			    $model_meta->meta_name = $meta_name;                          
                $model_meta->meta_value = $instructions;    
                $model_meta->save();

                $this->code = 1; 
                $this->msg = t(Helper_settings_saved);

            } else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
            //dump($model->getErrors());
		  
		if(!$model){		
			$model = new AR_shipping_rate;
		} 

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actioncouponList()
    {
        try {
            $page = intval(Yii::app()->input->post('page'));
            $search = trim(Yii::app()->input->post('q'));             
            $length =Yii::app()->params->list_limit;

            $sortby = "a.date_created"; $sort = 'DESC';
            
            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
                $page = $page-1;
            }

            $page = intval($page)/intval($length);            
            $criteria=new CDbCriteria();	
            $criteria->alias = "a";
            $criteria->select="
            a.*, (
                select count(*) 
                from
                {{ordernew}}
                where
                promo_code=a.voucher_name			
            ) as total_used
            ";
            $criteria->condition = "a.merchant_id=:merchant_id AND a.voucher_owner=:voucher_owner";		
            $criteria->params = [
                ':merchant_id'=> Yii::app()->merchant->merchant_id,
                ':voucher_owner'=>"merchant"
            ];
            
            $criteria->order = "$sortby $sort";
            $count = AR_voucher_new::model()->count($criteria); 
            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );        
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);    
            $page_count = $pages->getPageCount();

            if($page>0){
                if($page_raw>$page_count){
                    $this->code = 3;
                    $this->msg = t("end of results");
                    $this->responseJson();
                }
            }

            $data = [];            

            if($model = AR_voucher_new::model()->findAll($criteria)){                                                           
                $status_list = AttributesTools::StatusManagement('post');                      
                $voucher_list = AttributesTools::couponType();
                foreach ($model as $item) {                    
                    $data[] = [                 
                        'id'=>$item->voucher_id,
                        'status_raw'=>$item->status,
                        'voucher_name'=>$item->voucher_name,
                        'voucher_type'=>isset($voucher_list[$item->voucher_type])?$voucher_list[$item->voucher_type]:$item->voucher_type,
                        'amount'=>$item->voucher_type=="percentage"? Price_Formatter::convertToRaw($item->amount,1)."%" :Price_Formatter::formatNumber($item->amount),
                        'status'=>isset($status_list[$item->status])?$status_list[$item->status]:$item->status,
                        'expiration'=>Date_Formatter::date($item->expiration),
                        'total_used'=>$item->total_used,
                        'date_created'=>Date_Formatter::dateTime($item->date_created),
                    ];
                }                
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

    public function actiondeleteCoupon()
    {
        try {            

            $id = Yii::app()->input->post('id');
            $merchant_id = (integer) Yii::app()->merchant->merchant_id;

            $model = AR_voucher_new::model()->find('merchant_id=:merchant_id AND voucher_id=:voucher_id',
		    array(':merchant_id'=>$merchant_id, ':voucher_id'=>$id ));
            if($model){
                $model->delete();
                $this->code = 1;
                $this->msg = "Ok";
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionPromoAttributes()
    {
        try {            

            $voucher_type = AttributesTools::couponType();
		    $coupon_options = AttributesTools::couponOoptions();            
			$transaction_list= AttributesTools::ListSelectServices();
            $days = AttributesTools::dayList();		

            $this->code = 1; $this->msg = "Ok";
            $this->details = [
                'voucher_type'=>CommonUtility::ArrayToLabelValue($voucher_type),
                'coupon_options'=>CommonUtility::ArrayToLabelValue($coupon_options),
                'transaction_list'=>CommonUtility::ArrayToLabelValue($transaction_list),
                'days'=>CommonUtility::ArrayToLabelValue($days),
                'services'=>CommonUtility::ArrayToLabelValue(AttributesTools::ListSelectServices())
            ];
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionUpdateCoupon()
    {
        $this->actionAddCoupon(true);
    }

    public function actionAddCoupon($update=false)
    {
        try {            
            
            $merchant_id = Yii::app()->merchant->merchant_id;
            $id = isset($this->data['id'])?$this->data['id']:null;

            $model=new AR_voucher();
            if($update){
                $model = AR_voucher::model()->find('merchant_id=:merchant_id AND voucher_id=:voucher_id', 
                array(':merchant_id'=>$merchant_id, ':voucher_id'=>$id ));
                if(!$model){				
                    $this->msg = t(HELPER_RECORD_NOT_FOUND);
                    $this->responseJson();  
                }						
            }

            $model->voucher_owner = 'merchant';
			$model->merchant_id = $merchant_id;
            $model->voucher_name = isset($this->data['voucher_name'])?$this->data['voucher_name']:'';
            $model->voucher_type = isset($this->data['voucher_type'])?$this->data['voucher_type']:'';
            $model->amount = isset($this->data['amount'])?floatval($this->data['amount']):0;
            $model->min_order = isset($this->data['min_order'])?floatval($this->data['min_order']):0;
            $days_available = isset($this->data['days_available'])?$this->data['days_available']:'';
            $model->days_available = $days_available;
            $model->expiration = isset($this->data['expiration'])?date("Y-m-d",strtotime($this->data['expiration'])):'';
            $model->transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';
            $model->used_once = isset($this->data['used_once'])?floatval($this->data['used_once']):0;
            $model->visible = isset($this->data['visible'])?floatval($this->data['visible']):0;
            $model->status = isset($this->data['status'])?$this->data['status']:'';

            $model->max_number_use = isset($this->data['max_number_use'])?intval($this->data['max_number_use']):0;
            $model->selected_customer = isset($this->data['selected_customer'])?json_encode($this->data['selected_customer']):'';
            
            $days = AttributesTools::dayList();		
            foreach ($days as $day => $dayname) {
                if(in_array($day,$days_available)){
                    $model[$day] = 1;
                } else $model[$day] = 0;
            }
                             
            if($model->validate()){				
                if($model->save()){
                    $this->code = 1;
                    $this->msg = $update?t(Helper_update):t(Helper_success);
                } else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
            } else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }
    
    public function actiongetCoupon()
    {
        try {            

            $id = Yii::app()->input->post('id');
            $merchant_id = (integer) Yii::app()->merchant->merchant_id;

            $model = AR_voucher::model()->find('merchant_id=:merchant_id AND voucher_id=:voucher_id',
		    array(':merchant_id'=>$merchant_id, ':voucher_id'=>$id ));
            if($model){                
                $this->code = 1;
                $this->msg = "Ok";

                $days = AttributesTools::dayList();		
                foreach ($days as $day=>$dayval) {
                    if($model[$day]==1){
                        $selected_days[]=$day;
                    }
                }			

                $transaction_selected = [];
                $model_trans=AR_merchant_meta::model()->findAll("merchant_id=:merchant_id AND meta_name=:meta_name AND meta_value=:meta_value",array(
                    ':merchant_id'=>intval($merchant_id),
                    ':meta_name'=>'coupon',
                    ':meta_value'=>$id
                ));
                if($model_trans){
                    foreach ($model_trans as $value) {
                        $transaction_selected[] = $value->meta_value1;
                    }                    
                }			

                $customer_selected = [];
                $selected_merchant = !empty($model->joining_merchant) ? json_decode(stripslashes($model->joining_merchant)): '';
                $selected_customer = !empty($model->selected_customer) ? json_decode(stripslashes($model->selected_customer)): '';               

                $this->details = [
                    'voucher_name'=>$model->voucher_name,
                    'voucher_type'=>$model->voucher_type,
                    'amount'=>$model->amount>0?Price_Formatter::convertToRaw($model->amount):0,
                    'min_order'=>$model->min_order>0?Price_Formatter::convertToRaw($model->min_order):0,
                    'expiration'=>date("Y/m/d",strtotime($model->expiration)),
                    'used_once'=>intval($model->used_once),
                    'visible'=>$model->visible,
                    'status'=>$model->status,
                    'days_available'=>$selected_days,
                    'transaction_type'=>$transaction_selected,
                    'selected_merchant'=>$selected_merchant,
                    'selected_customer'=>$selected_customer,
                    'max_number_use'=>$model->max_number_use
                ];
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionofferList()
    {
        try {
            $page = intval(Yii::app()->input->post('page'));
            $search = trim(Yii::app()->input->post('q'));             
            $length =Yii::app()->params->list_limit;

            $sortby = "date_created"; $sort = 'DESC';
            
            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
                $page = $page-1;
            }

            $page = intval($page)/intval($length);            
            $criteria=new CDbCriteria();	            
            $criteria->condition = "merchant_id=:merchant_id";		
            $criteria->params = [
                ':merchant_id'=> Yii::app()->merchant->merchant_id                
            ];
            
            $criteria->order = "$sortby $sort";
            $count = AR_offers::model()->count($criteria); 
            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );        
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);    
            $page_count = $pages->getPageCount();

            if($page>0){
                if($page_raw>$page_count){
                    $this->code = 3;
                    $this->msg = t("end of results");
                    $this->responseJson();
                }
            }

            $data = [];            

            if($model = AR_offers::model()->findAll($criteria)){                                                           
                $status_list = AttributesTools::StatusManagement('post');
                foreach ($model as $item) {                    
                    $data[] = [                 
                        'id'=>$item->offers_id,                        
                        'offer_percentage'=>Price_Formatter::convertToRaw($item->offer_price,1)."%",
                        'offer_price'=>Price_Formatter::formatNumber($item->offer_percentage),
                        'valid_from'=>Date_Formatter::date($item->valid_from),
                        'valid_to'=>Date_Formatter::date($item->valid_to),
                        'status_raw'=>$item->status,                        
                        'status'=>isset($status_list[$item->status])?$status_list[$item->status]:$item->status,                                                
                        'date_created'=>Date_Formatter::dateTime($item->date_created),
                    ];
                }                
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

    public function actionUpdateOffers()
    {
        $this->actionAddOffers(true);
    }

    public function actionAddOffers($update=false)
    {
        try {            

            $merchant_id = Yii::app()->merchant->merchant_id;
            $id = isset($this->data['id'])?$this->data['id']:null;

            $model=new AR_offers();
            if($update){
                $model = AR_offers::model()->find('merchant_id=:merchant_id AND offers_id=:offers_id', 
                array(':merchant_id'=>$merchant_id, ':offers_id'=>$id ));
                if(!$model){				
                    $this->msg = t(HELPER_RECORD_NOT_FOUND);
                    $this->responseJson();  
                }						
            }

            $model->merchant_id = $merchant_id;
            $model->offer_percentage = isset($this->data['offer_percentage'])?$this->data['offer_percentage']:0;
            $model->offer_price = isset($this->data['offer_price'])?$this->data['offer_price']:0;
            $model->valid_from =  isset($this->data['valid_from'])?date("Y-m-d",strtotime($this->data['valid_from'])):null;
            $model->valid_to = isset($this->data['valid_to'])?date("Y-m-d",strtotime($this->data['valid_to'])):null;
            $model->applicable_selected = isset($this->data['applicable_selected'])?$this->data['applicable_selected']:'';
            $model->status = isset($this->data['status'])?$this->data['status']:'';
                        
            if($model->validate()){				
                if($model->save()){
                    $this->code = 1;
                    $this->msg = $update?t(Helper_update):t(Helper_success);
                } else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
            } else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actiondeleteOffers()
    {
        try {            

            $id = Yii::app()->input->post('id');
            $merchant_id = (integer) Yii::app()->merchant->merchant_id;

            $model = AR_offers::model()->find('merchant_id=:merchant_id AND offers_id=:offers_id',
		    array(':merchant_id'=>$merchant_id, ':offers_id'=>$id ));
            if($model){
                $model->delete();
                $this->code = 1;
                $this->msg = "Ok";
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actiongetOffers()
    {
        try {            

            $id = Yii::app()->input->post('id');
            $merchant_id = (integer) Yii::app()->merchant->merchant_id;

            $model = AR_offers::model()->find('merchant_id=:merchant_id AND offers_id=:offers_id',
		    array(':merchant_id'=>$merchant_id, ':offers_id'=>$id ));
            if($model){                
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'offer_percentage'=>Price_Formatter::convertToRaw($model->offer_percentage,1),
                    'offer_price'=>Price_Formatter::convertToRaw($model->offer_price,2),
                    'valid_from'=>!empty($model->valid_from)? date("Y/m/d",strtotime($model->valid_from)) :'',
                    'valid_to'=>!empty($model->valid_to)? date("Y/m/d",strtotime($model->valid_to)) :'',
                    'applicable_to'=>!empty($model->applicable_to)?json_decode($model->applicable_to,true):'',
                    'status'=>$model->status,
                ];
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }  

    public function actiongalleryList()
    {
        try {
            $page = intval(Yii::app()->input->post('page'));
            $search = trim(Yii::app()->input->post('q'));             
            $length =Yii::app()->params->list_limit;

            $sortby = "date_modified"; $sort = 'DESC';
            
            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
                $page = $page-1;
            }

            $page = intval($page)/intval($length);            
            $criteria=new CDbCriteria();	            
            $criteria->condition = "merchant_id=:merchant_id AND meta_name=:meta_name";		
            $criteria->params = [
                ':merchant_id'=> Yii::app()->merchant->merchant_id,                      
		        ':meta_name'=>AttributesTools::metaMedia()
            ];
            
            $criteria->order = "$sortby $sort";
            $count = AR_merchant_meta::model()->count($criteria); 
            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );        
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);    
            $page_count = $pages->getPageCount();

            if($page>0){
                if($page_raw>$page_count){
                    $this->code = 3;
                    $this->msg = t("end of results");
                    $this->responseJson();
                }
            }

            $data = [];            

            if($model = AR_merchant_meta::model()->findAll($criteria)){                                                                           
                foreach ($model as $item) {                                        
                    $data[] = [                 
                        'id'=>$item->meta_id,                   
                        'image_url'=>CMedia::getImage($item->meta_value,$item->meta_value1,'@thumbnail',CommonUtility::getPlaceholderPhoto('item')),                             
                        'date_created'=>Date_Formatter::dateTime($item->date_modified),
                    ];
                }                
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

    public function actiondeleteGallery()
    {
        try {            

            $id = Yii::app()->input->post('id');
            $merchant_id = (integer) Yii::app()->merchant->merchant_id;

            $model = AR_merchant_meta::model()->find('merchant_id=:merchant_id AND meta_id=:meta_id',
		    array(':merchant_id'=>$merchant_id, ':meta_id'=>$id ));
            if($model){
                $model->delete();
                $this->code = 1;
                $this->msg = "Ok";
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionUpdateGallery()
    {
        $this->actionAddGallery(true);
    }
    
    public function actionAddGallery($update=false)
    {
        try {         
            
            $merchant_id = (integer) Yii::app()->merchant->merchant_id;		    

            $id = isset($this->data['id'])?$this->data['id']:null;
            
            $model=new AR_merchant_meta();
            if($update){
                $model = AR_merchant_meta::model()->find('merchant_id=:merchant_id AND meta_id=:meta_id', 
                array(':merchant_id'=>$merchant_id, ':meta_id'=>$id ));
                if(!$model){				
                    $this->msg = t(HELPER_RECORD_NOT_FOUND);
                    $this->responseJson();  
                }						
            }


            $model->meta_value = isset($this->data['photo'])?$this->data['photo']:'';
            $model->meta_value1 = isset($this->data['upload_path'])?$this->data['upload_path']:'';

            $file_data = isset($this->data['file_data'])?$this->data['file_data']:'';
            $image_type = isset($this->data['image_type'])?$this->data['image_type']:'png';

            if(!empty($file_data)){
                $result = [];
                try {
                    $result = CImageUploader::saveBase64Image($file_data,$image_type,"upload/".Yii::app()->merchant->merchant_id);
                    $model->meta_value = isset($result['filename'])?$result['filename']:'';
                    $model->meta_value1 = isset($result['path'])?$result['path']:'';
                } catch (Exception $e) {
                    $this->msg = t($e->getMessage());
                    $this->responseJson();
                }
            }

            $model->merchant_id = $merchant_id;
            $model->meta_name = AttributesTools::metaMedia();
            if($model->save()){
                $this->code = 1;
                $this->msg = $update?t(Helper_update):t(Helper_success);
            } else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }
    
    public function actiongetGallery()
    {
        try {            

            $id = Yii::app()->input->post('id');
            $merchant_id = (integer) Yii::app()->merchant->merchant_id;

            $model = AR_merchant_meta::model()->find('merchant_id=:merchant_id AND meta_id=:meta_id',
		    array(':merchant_id'=>$merchant_id, ':meta_id'=>$id ));
            if($model){                                
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'avatar'=>CMedia::getImage($model->meta_value,$model->meta_value1,'@thumbnail',CommonUtility::getPlaceholderPhoto('item')),
                    'photo'=>$model->meta_value,
                    'upload_path'=>$model->meta_value1,
                ];
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionmediaList()
    {
        try {
            $page = intval(Yii::app()->input->post('page'));
            $search = trim(Yii::app()->input->post('q'));             
            $length =Yii::app()->params->list_limit;

            $sortby = "id"; $sort = 'DESC';
            
            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
                $page = $page-1;
            }

            $page = intval($page)/intval($length);            
            $criteria=new CDbCriteria();	            
            $criteria->condition = "merchant_id=:merchant_id";		
            $criteria->params = [
                ':merchant_id'=> Yii::app()->merchant->merchant_id,                      		        
            ];
            
            $criteria->order = "$sortby $sort";
            $count = AR_media::model()->count($criteria); 
            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );        
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);    
            $page_count = $pages->getPageCount();

            if($page>0){
                if($page_raw>$page_count){
                    $this->code = 3;
                    $this->msg = t("end of results");
                    $this->responseJson();
                }
            }

            $data = [];            

            if($model = AR_media::model()->findAll($criteria)){                                                                           
                foreach ($model as $item) {                                        
                    $data[] = [                 
                        'id'=>$item->upload_uuid,                   
                        'image_url'=>CMedia::getImage($item->filename,$item->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('item')),                             
                        'title'=>$item->title,
                        'size'=>CommonUtility::HumanFilesize($item->size),  
                        'date_created'=>Date_Formatter::dateTime($item->date_created),
                    ];
                }                
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

    public function actionUpdateMedia()
    {
        $this->actionAddMedia(true);
    }

    public function actionAddMedia($update=false)
    {
        try {         
            
            $merchant_id = (integer) Yii::app()->merchant->merchant_id;		    

            $id = isset($this->data['id'])?$this->data['id']:null;
            
            $model=new AR_media();
            if($update){
                $model = AR_media::model()->find('merchant_id=:merchant_id AND upload_uuid=:upload_uuid', 
                array(':merchant_id'=>$merchant_id, ':upload_uuid'=>$id ));
                if(!$model){				
                    $this->msg = t(HELPER_RECORD_NOT_FOUND);
                    $this->responseJson();  
                }						
            }

            $upload_uuid = CommonUtility::createUUID("{{media_files}}",'upload_uuid');
            if(!$update){
                $model->upload_uuid = $upload_uuid;
            }
            $model->filename = isset($this->data['photo'])?$this->data['photo']:'';
            $model->path = isset($this->data['upload_path'])?$this->data['upload_path']:'';
            $model->title = isset($this->data['title'])?$this->data['title']:'';
            $model->size = isset($this->data['size'])?$this->data['size']:'';

            $file_data = isset($this->data['file_data'])?$this->data['file_data']:'';
            $image_type = isset($this->data['image_type'])?$this->data['image_type']:'png';

            if(!empty($file_data)){
                $result = [];
                try {
                    $result = CImageUploader::saveBase64Image($file_data,$image_type,"upload/".Yii::app()->merchant->merchant_id);
                    $model->filename = isset($result['filename'])?$result['filename']:'';
                    $model->path = isset($result['path'])?$result['path']:'';
                } catch (Exception $e) {
                    $this->msg = t($e->getMessage());
                    $this->responseJson();
                }
            }

            $model->merchant_id = $merchant_id;            
            if($model->save()){
                $this->code = 1;
                $this->msg = $update?t(Helper_update):t(Helper_success);
            } else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actiondeleteMedia()
    {
        try {            

            $id = Yii::app()->input->post('id');
            $merchant_id = (integer) Yii::app()->merchant->merchant_id;

            $model = AR_media::model()->find('merchant_id=:merchant_id AND upload_uuid=:upload_uuid',
		    array(':merchant_id'=>$merchant_id, ':upload_uuid'=>$id ));
            if($model){
                $model->delete();
                $this->code = 1;
                $this->msg = "Ok";
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actiongetMedia()
    {
        try {            

            $id = Yii::app()->input->post('id');
            $merchant_id = (integer) Yii::app()->merchant->merchant_id;

            $model = AR_media::model()->find('merchant_id=:merchant_id AND upload_uuid=:upload_uuid',
		    array(':merchant_id'=>$merchant_id, ':upload_uuid'=>$id ));
            if($model){                                
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'avatar'=>CMedia::getImage($model->filename,$model->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('item')),
                    'photo'=>$model->filename,
                    'upload_path'=>$model->path,
                    'title'=>$model->title,
                    'size'=>$model->size,
                ];
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actiongetMerchantBalance()
    {
        try {            

            $merchant_id = (integer) Yii::app()->merchant->merchant_id;
            try {									
                $card_id = CWallet::getCardID( Yii::app()->params->account_type['merchant'] , Yii::app()->merchant->merchant_id );
                $balance = CWallet::getBalance($card_id);
            } catch (Exception $e) {
               $this->msg = t($e->getMessage());
               $balance = 0;		
            }	

            $this->code = 1;
		    $this->msg = "OK";
            $this->details = [
              'balance'=>Price_Formatter::formatNumber($balance),
		      'balance_raw'=>Price_Formatter::convertToRaw($balance,2,false,""), 
            ];

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionwithdrawalsHistory()
    {
        try {

            $data = array(); $card_id = 0;
            try {
                $card_id = CWallet::getCardID(Yii::app()->params->account_type['merchant'],Yii::app()->merchant->merchant_id);
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
			$criteria->condition = "card_id=:card_id  AND transaction_type=:transaction_type";
            $criteria->params  = array(
              ':card_id'=>intval($card_id),
              ':transaction_type'=>"payout"
            );
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
                $payment_status = AttributesTools::paymentStatus();                
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
                        'transaction_date'=>Date_Formatter::date($item->transaction_date),
                        'transaction_description'=>$description,
                        'transaction_amount'=>$transaction_amount,
                        'running_balance'=>Price_Formatter::formatNumber($item->running_balance),
                        'transaction_type'=>$item->transaction_type,
                        'status'=>isset($payment_status[$item->status])?$payment_status[$item->status]:$item->status,
                        'status_raw'=>$item->status
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
    
    public function actiongetPayoutAccount()
    {
        try {            

            $merchant_id = Yii::app()->merchant->merchant_id;
            $account = CPayouts::getPayoutAccont($merchant_id);
            $this->code = 1; $this->msg = "OK";
            $this->details = $account;

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actiongetMerchantLogin()
    {
        try {            

            $merchant_id = Yii::app()->merchant->merchant_id;
            $model = AR_merchant_user::model()->find("merchant_id=:merchant_id AND main_account=:main_account",array(
                ':merchant_id'=>$merchant_id,
                ':main_account'=>1
            ));		

            if($model){
                $this->details = [
                    'first_name'=>$model->first_name,
                    'last_name'=>$model->last_name,
                    'contact_email'=>$model->contact_email,
                    'contact_number'=>$model->contact_number,
                    'username'=>$model->username,
                ];
            } else $this->details = [];

            $this->code = 1;
            $this->msg = "Ok";

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    
    public function actionsaveMerchantlogin()
    {
        try {            

            $merchant_id = Yii::app()->merchant->merchant_id;
            $model = AR_merchant_user::model()->find("merchant_id=:merchant_id AND main_account=:main_account",array(
                ':merchant_id'=>$merchant_id,
                ':main_account'=>1
            ));		

            $update = true;
            if(!$model){
                $update = false;
                $model = new AR_merchant_user();
            }
            
            $model->first_name = isset($this->data['first_name'])?$this->data['first_name']:'';
            $model->last_name = isset($this->data['last_name'])?$this->data['last_name']:'';
            $model->contact_email = isset($this->data['contact_email'])?$this->data['contact_email']:'';
            $model->contact_number = isset($this->data['contact_number'])?$this->data['contact_number']:'';
            $model->username = isset($this->data['username'])?$this->data['username']:'';

            $new_password = isset($this->data['new_password'])?$this->data['new_password']:'';
            $repeat_password = isset($this->data['repeat_password'])?$this->data['repeat_password']:'';

            if(!empty($new_password) && !empty($repeat_password)){
                $model->new_password = $new_password;
                $model->repeat_password = $repeat_password;
                $model->password = md5($new_password);
            }

            $model->main_account = 1;
            $model->status = 'active';

            if($model->save()){
                $this->code = 1;
                $this->msg = $update?t(Helper_update):t(Helper_success);
            } else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actiongetTimezonedata()
    {
        try {            

            $merchant_id = Yii::app()->merchant->merchant_id;
            $options = array('merchant_timezone','merchant_time_picker_interval');

            $merchant_timezone = ''; $merchant_time_picker_interval='';
            if($data = OptionsTools::find($options,$merchant_id)){                
                $merchant_timezone = isset($data['merchant_timezone'])?$data['merchant_timezone']:'';
                $merchant_time_picker_interval = isset($data['merchant_time_picker_interval'])?$data['merchant_time_picker_interval']:'';
            }

            $this->code = 1; $this->msg = "Ok";
            $this->details = [
                'timelist'=>CommonUtility::ArrayToLabelValue(AttributesTools::timezoneList()),
                'merchant_timezone'=>$merchant_timezone,
                'merchant_time_picker_interval'=>$merchant_time_picker_interval,
            ];
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionsaveTimezonedata()
    {
        try {            

            $merchant_id = Yii::app()->merchant->merchant_id;
            
            $model=new AR_option;
            $model->merchant_timezone = isset($this->data['merchant_timezone'])?$this->data['merchant_timezone']:'';
            $model->merchant_time_picker_interval = isset($this->data['merchant_time_picker_interval'])?$this->data['merchant_time_picker_interval']:'';

            $options = array('merchant_timezone','merchant_time_picker_interval');

            OptionsTools::$merchant_id = $merchant_id;
            if(OptionsTools::save($options, $model, $merchant_id)){
                $this->code = 1;
                $this->msg = t(Helper_settings_saved);
            } else $this->msg = t(Helper_failed_save);
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }
    
    public function actiongetMerchantzone()
    {
        try {            
        
            $self_delivery = isset(Yii::app()->params['settings_merchant']['self_delivery'])?Yii::app()->params['settings_merchant']['self_delivery']:false;
		    $self_delivery = $self_delivery==1?true:false;								

            $merchant_id = Yii::app()->merchant->merchant_id;
            $merchant_id_owner = $self_delivery?$merchant_id:0;

            $zone_list = CommonUtility::getDataToDropDown("{{zones}}",'zone_id','zone_name',
            "where merchant_id=".q($merchant_id_owner)." ","Order by zone_name asc");
            
            $zone_data = [];
            $data = CommonUtility::getDataToDropDown("{{merchant_meta}}",'meta_value','meta_value',
            "where merchant_id=".q($merchant_id)." AND meta_name='zone' " );
            if($data){                
                foreach ($data as  $value) {
                    $zone_data[] = intval($value);
                }
            }

            $this->code = 1;
            $this->msg = "Ok";
            $this->details = [
                'zone'=>$zone_data,
                'zone_list'=>CommonUtility::ArrayToLabelValue($zone_list)
            ];

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionsaveMerchantzone()
    {
        try {            

            $merchant_id = Yii::app()->merchant->merchant_id;
            $zone = isset($this->data['zone'])?$this->data['zone']:'';        
            
            AR_merchant_meta::model()->deleteAll('merchant_id=:merchant_id AND meta_name=:meta_name',array(
             ':merchant_id'=> $merchant_id,
             ':meta_name'=>'zone'
            ));
            
            if(is_array($zone) && count($zone)>=1){
                foreach ($zone as $zone_id) {
                    $meta = new AR_merchant_meta;
                    $meta->merchant_id = intval($merchant_id);
                    $meta->meta_name = 'zone';
                    $meta->meta_value = intval($zone_id);
                    $meta->save();
                }		    		
            }	
            
            $this->code = 1;
            $this->msg = t(Helper_settings_saved);
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actioninvoiceList()
    {
        try {
            $page = intval(Yii::app()->input->post('page'));
            $search = trim(Yii::app()->input->post('q'));             
            $length =Yii::app()->params->list_limit;

            $sortby = "a.invoice_number"; $sort = 'DESC';
            
            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
                $page = $page-1;
            }

            $page = intval($page)/intval($length);            
            $criteria=new CDbCriteria();	            
            $criteria->alias = "a";
            $criteria->select = "a.*,(
                select count(*) from {{bank_deposit}}
                where deposit_type='invoice'
                and transaction_ref_id=a.invoice_number
            ) as has_proof_uploaded
            ";            
            $criteria->condition = "a.merchant_id=:merchant_id";		
            $criteria->params = [
                ':merchant_id'=> Yii::app()->merchant->merchant_id,                      		        
            ];
            
            $criteria->order = "$sortby $sort";
            $count = AR_invoice::model()->count($criteria); 
            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );        
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);    
            $page_count = $pages->getPageCount();

            if($page>0){
                if($page_raw>$page_count){
                    $this->code = 3;
                    $this->msg = t("end of results");
                    $this->responseJson();
                }
            }

            $data = [];            

            if($model = AR_invoice::model()->findAll($criteria)){                      
                $payment_status = AttributesTools::paymentStatus();
                foreach ($model as $item) {                                        
                    $data[] = [                 
                        'invoice_number'=>$item->invoice_number,
                        'id'=>$item->invoice_uuid,             
                        'due_date'=>Date_Formatter::date($item->due_date),
                        'date_from'=>Date_Formatter::date($item->date_from),
                        'date_to'=>Date_Formatter::date($item->date_to),
                        'status_raw'=>$item->payment_status,
                        'status'=>isset($payment_status[$item->payment_status])?$payment_status[$item->payment_status]:$item->payment_status,
                        'total_raw'=>$item->invoice_total,
                        'total'=>Price_Formatter::formatNumber($item->invoice_total),
                        'date_created'=>Date_Formatter::dateTime($item->date_created),
                        'has_proof_uploaded'=>$item->has_proof_uploaded==1?true:false
                    ];
                }                
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

    public function actioninvoiceDetails()
    {
        try {            

            $id = trim(Yii::app()->input->post('id'));   
            $model = CMerchantInvoice::getInvoice($id);     

            $data = [
                'invoice_number'=>$model->invoice_number,
                'invoice_uuid'=>$model->invoice_uuid,
                'restaurant_name'=>$model->restaurant_name,
                'business_address'=>$model->business_address,
                'contact_email'=>$model->contact_email,
                'contact_phone'=>$model->contact_phone,
                'invoice_terms'=>$model->invoice_terms,
                'invoice_total'=>Price_Formatter::formatNumber($model->invoice_total),
                'amount_paid'=>Price_Formatter::formatNumber($model->amount_paid),                
                'amount_due'=>Price_Formatter::formatNumber($model->invoice_total-$model->amount_paid),
                'invoice_created'=>Date_Formatter::date($model->invoice_created),
                'due_date'=>Date_Formatter::date($model->due_date),
                'date_from'=>Date_Formatter::date($model->date_from),
                'date_to'=>Date_Formatter::date($model->date_to),
                'payment_status_raw'=>$model->payment_status,
                'payment_status'=>$model->payment_status,
            ];

            $this->code = 1; $this->msg = "Ok";
            $this->details = $data;

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actiongetInvoicePaymentInformation()
    {
        try {            

            $id = Yii::app()->input->post('id');		        
            $payment_info = AttributesTools::getInvoicePaymentInformation();
            $this->code = 1; $this->msg = "Ok";

            $model = CMerchantInvoice::getInvoice($id);

            $is_due = false;            
            $today = gmdate("Y-m-d g:i:s a");	
            $date_diff = CommonUtility::dateDifference($model->due_date,$today);
            if(is_array($date_diff) && count($date_diff)>=1 && $model->payment_status !='paid' ){                
                if($date_diff['days']>0){
                    $is_due = true;
                }
            }

            $total = Price_Formatter::formatNumber($model->invoice_total-$model->amount_paid);
            $message = t("The amount {total} should be deposited before {due_date} into our account",[
                '{total}'=>$total,
                '{due_date}'=>Date_Formatter::date($model->due_date)
            ]);

            $this->details = [
                'payment_info'=>$payment_info,
                'is_due'=>$is_due,
                'total'=>$total,
                'due_date'=>Date_Formatter::date($model->due_date),
                'message'=>$message
            ];

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actioninvoiceActivity()
    {
        try {            

            $id = Yii::app()->input->post('id');
            $model = CMerchantInvoice::getInvoice($id);

            try {
                $history = CMerchantInvoice::getHistory($model->invoice_number);
            } catch (Exception $e) {
                $history = [];                
            }
            
            $this->code = 1; $this->msg = "Ok";
            $this->details = $history;

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actiongetInvoice()
    {
        try {            

            $id = Yii::app()->input->post('id');
            $model = CMerchantInvoice::getInvoice($id);

            $total = Price_Formatter::formatNumber($model->invoice_total-$model->amount_paid);

            $account_name = ''; $amount= 0; 
            $path=''; $photo=''; $avatar = ''; $reference_number='';

            $model_bank = AR_bank_deposit::model()->find("deposit_type=:deposit_type AND transaction_ref_id=:transaction_ref_id",[
                ':deposit_type'=>'invoice',
                ':transaction_ref_id'=>$model->invoice_number
            ]);
            if($model_bank){
                $account_name = $model_bank->account_name;
                $amount = Price_Formatter::convertToRaw($model_bank->amount,2);
                $reference_number = $model_bank->reference_number;
                $path = $model_bank->path;
                $photo = $model_bank->proof_image;
                $avatar = CMedia::getImage($model_bank->proof_image,$model_bank->path,Yii::app()->params->size_image,CommonUtility::getPlaceholderPhoto('item'));
            }

            $this->code = 1; $this->msg = "Ok";
            $this->details = [
                'invoice_number'=>$model->invoice_number,                
                'total'=>$total,
                'account_name'=>$account_name,
                'amount'=>$amount,
                'reference_number'=>$reference_number,
                'upload_path'=>$path,
                'photo'=>$photo,
                'avatar'=>$avatar,
            ];
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actioninvoiceProofpayment()
    {
        try {            
            
            $id = isset($this->data['id'])?$this->data['id']:null;
            $invoice = CMerchantInvoice::getInvoice($id);

            $exchange_rate = 1; $exchange_rate_merchant_to_admin=1; $exchange_rate_admin_to_merchant=1;
            $multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
            $multicurrency_enabled = $multicurrency_enabled==1?true:false;   
            
            $admin_base_currency = AttributesTools::defaultCurrency();
            $merchant_default_currency = isset(Yii::app()->params['settings_merchant']['merchant_default_currency'])?Yii::app()->params['settings_merchant']['merchant_default_currency']:$admin_base_currency;            
            $merchant_default_currency = !empty($merchant_default_currency)?$merchant_default_currency:$admin_base_currency;
            if(!$multicurrency_enabled){
                $merchant_default_currency = $admin_base_currency;
            }
            $merchant_id = Yii::app()->merchant->merchant_id;

            $model = AR_bank_deposit::model()->find("deposit_type=:deposit_type AND merchant_id=:merchant_id AND transaction_ref_id=:transaction_ref_id",[
				':deposit_type'=>"invoice",
				':merchant_id'=>$merchant_id,
                ':transaction_ref_id'=>$invoice->invoice_number
			]);
            if(!$model){
                $model = new AR_bank_deposit;
            }

            $model->scenario = "upload_deposit";
            $model->deposit_type = "invoice";
            $model->account_name = isset($this->data['account_name'])?trim($this->data['account_name']):'';
            $model->reference_number = isset($this->data['reference_number'])?trim($this->data['reference_number']):'';
            $model->amount = isset($this->data['amount'])?floatval($this->data['amount']):0;

            $model->proof_image = isset($this->data['photo'])?$this->data['photo']:'';
            $model->path = isset($this->data['upload_path'])?$this->data['upload_path']:'';
            $file_data = isset($this->data['file_data'])?$this->data['file_data']:'';
            $image_type = isset($this->data['image_type'])?$this->data['image_type']:'png';
            
            if(!empty($file_data)){
                $result = [];
                try {
                    $result = CImageUploader::saveBase64Image($file_data,$image_type,"upload/".Yii::app()->merchant->merchant_id);
                    $model->proof_image = isset($result['filename'])?$result['filename']:'';
                    $model->path = isset($result['path'])?$result['path']:'';
                } catch (Exception $e) {
                    $this->msg = t($e->getMessage());
                    $this->responseJson();
                }
            }

            if(floatval($invoice->invoice_total)!=floatval($model->amount)){
                $this->msg = t("Amount is not exact as invoice amount");
                $this->responseJson();
            } else {                
                if($model->validate()){

                    $file_uuid = CommonUtility::createUUID("{{bank_deposit}}",'deposit_uuid');
                    $model->transaction_ref_id = $invoice->invoice_number;                    
                    $model->deposit_uuid = $file_uuid;			
                    $model->merchant_id = $merchant_id;

                    $model->use_currency_code = $merchant_default_currency;
                    $model->base_currency_code = $merchant_default_currency;
                    $model->admin_base_currency = $admin_base_currency;

                    if($multicurrency_enabled){
                        if($merchant_default_currency!=$admin_base_currency){
                            $exchange_rate_merchant_to_admin = CMulticurrency::getExchangeRate($merchant_default_currency,$admin_base_currency);
                            $exchange_rate_admin_to_merchant = CMulticurrency::getExchangeRate($admin_base_currency,$merchant_default_currency);
                        }                        
                    }

                    $model->exchange_rate = $exchange_rate;
                    $model->exchange_rate_merchant_to_admin = $exchange_rate_merchant_to_admin;
                    $model->exchange_rate_admin_to_merchant = $exchange_rate_admin_to_merchant;
                                                      
                    if($model->save()){
                        $this->code = 1; 
                        $this->msg = t("You succesfully upload bank deposit. Please wait while we validate your payment.");
                    } else $this->msg = CommonUtility::parseError( $model->getErrors() );
                } else $this->msg = CommonUtility::parseError( $model->getErrors() );
            }
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionAddCollectCash()
    {
        try {            
            
            $model = new AR_driver_collect_cash();
            $model->driver_id = isset($this->data['driver_id'])? intval($this->data['driver_id']) :0;
            $model->amount_collected = isset($this->data['amount_collected'])? floatval($this->data['amount_collected']) :0;
            $model->reference_id = isset($this->data['reference_id'])? trim($this->data['reference_id']) :'';

            $card_id = 0;
			try {
			    $card_id = CWallet::getCardID( Yii::app()->params->account_type['driver'] , $model->driver_id );								
		    } catch (Exception $e) {			    				
		    }						
			$model->card_id = $card_id;

            if($model->validate()){
                
                $exchange_rate_merchant_to_admin = 1;$exchange_rate_admin_to_merchant= 1;
                $multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
                $multicurrency_enabled = $multicurrency_enabled==1?true:false;       

				$merchant_base_currency = Price_Formatter::$number_format['currency_code'];
				$admin_base_currency = AttributesTools::defaultCurrency();							

				if($multicurrency_enabled && $merchant_base_currency!=$admin_base_currency){
					$exchange_rate_merchant_to_admin = CMulticurrency::getExchangeRate($merchant_base_currency,$admin_base_currency);
					$exchange_rate_admin_to_merchant = CMulticurrency::getExchangeRate($admin_base_currency,$merchant_base_currency);
				}

				$model->merchant_id = Yii::app()->merchant->merchant_id;	
				$model->merchant_base_currency = $merchant_base_currency;		
				$model->admin_base_currency = $admin_base_currency;
				$model->exchange_rate_merchant_to_admin = $exchange_rate_merchant_to_admin;
				$model->exchange_rate_admin_to_merchant = $exchange_rate_admin_to_merchant;

                if($model->save()){
                    $this->code = 1; $this->msg = t(Helper_success);
                } else $this->msg = CommonUtility::parseError( $model->getErrors() );
            } else $this->msg = CommonUtility::parseError( $model->getErrors() );
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actiongetDailySummary()
    {
        try {            

            $merchant_id = Yii::app()->merchant->merchant_id;
			$status_completed = AOrderSettings::getStatus(array('status_delivered','status_completed'));
			$date_now = date("Y-m-d");

			$date_start = Yii::app()->input->post('date_start');
			$date_end = Yii::app()->input->post('date_end');
			$date_start = !empty($date_start)?$date_start:$date_now;
			$date_end = !empty($date_end)?$date_end:$date_now;

			$result = CReports::dailySalesSummary($merchant_id,$date_start,$date_end,$status_completed);
			$this->code = 1; $this->msg = "Ok";

            $data = [];
            $data[] = [
                'label'=>t("Total Sales"),
                'value'=>isset($result['total_sales'])? Price_Formatter::formatNumber($result['total_sales']) :0,
            ];
            $data[] = [
                'label'=>t("Delivery Fee"),
                'value'=>isset($result['total_delivery_fee'])? Price_Formatter::formatNumber($result['total_delivery_fee']) :0,
            ];
            $data[] = [
                'label'=>t("Total Tax"),
                'value'=>isset($result['tax_total'])? Price_Formatter::formatNumber($result['tax_total']) :0,
            ];
            $data[] = [
                'label'=>t("Total Tips"),
                'value'=>isset($result['total_tips'])? Price_Formatter::formatNumber($result['total_tips']) :0,
            ];
            $data[] = [
                'label'=>t("Total"),
                'value'=>isset($result['total'])? Price_Formatter::formatNumber($result['total']) :0,
            ];            
			
			$this->details = [
				'data'=>$data,				
			];					
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actiondailyReportSales()
    {
        try {

            $date_now = date("Y-m-d");
            $page = intval(Yii::app()->input->post('page'));
            $search = trim(Yii::app()->input->post('q'));    
            
            $date_start = trim(Yii::app()->input->post('date_start'));    
            $date_start = !empty($date_start)?$date_start:$date_now;
	        $date_end = trim(Yii::app()->input->post('date_end'));    
            $date_end = !empty($date_end)?$date_end:$date_now;

            $length =Yii::app()->params->list_limit;
            $merchant_id = Yii::app()->merchant->merchant_id;
            
            $status_completed = AOrderSettings::getStatus(array('status_delivered','status_completed'));

            $sortby = "order_id"; $sort = 'DESC';
            
            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
                $page = $page-1;
            }

            $page = intval($page)/intval($length);            
            $criteria=new CDbCriteria();	            
            $criteria->alias = "a";
            $criteria->condition = "a.merchant_id=:merchant_id";
            $criteria->params  = array(
            ':merchant_id'=>intval($merchant_id),		  	      
            );
            $criteria->addBetweenCondition("DATE_FORMAT(a.date_created,'%Y-%m-%d')", $date_start , $date_end );		
            $criteria->addInCondition('a.status', (array) $status_completed );
            
            $criteria->order = "$sortby $sort";
            $count = AR_ordernew::model()->count($criteria); 
            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );        
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);    
            $page_count = $pages->getPageCount();
            
            if($page>0){
                if($page_raw>$page_count){
                    $this->code = 3;
                    $this->msg = t("end of results");
                    $this->responseJson();
                }
            }

            $data = [];            

            if($model = AR_ordernew::model()->findAll($criteria)){                
                $price_format = CMulticurrency::getAllCurrency();          
                $payment_list = AttributesTools::PaymentProvider();
		        $services = COrders::servicesList(Yii::app()->language);    	            

                foreach ($model as $items) {            
                    if($price_format){
                        if(isset($price_format[$items->base_currency_code])){
                            Price_Formatter::$number_format = $price_format[$items->base_currency_code];
                        }						
                    }                            
                    $data[] = [          
                        'order_id'=>$items->order_id,
                        'order_uuid'=>$items->order_uuid,
                        'service_code'=>$items->service_code,
                        'service_name'=> isset($services[$items->service_code])?$services[$items->service_code]['service_name']:$items->service_code ,
                        'payment_code'=>$items->payment_code,
                        'payment_name'=>isset($payment_list[$items->payment_code])?$payment_list[$items->payment_code]:$items->payment_code,
                        'sub_total'=>$items->sub_total>0?Price_Formatter::formatNumber($items->sub_total):'',
                        'delivery_fee'=>$items->delivery_fee>0?Price_Formatter::formatNumber($items->delivery_fee):'',
                        'tax_total'=>$items->tax_total>0?Price_Formatter::formatNumber($items->tax_total):'',
					    'courier_tip'=>$items->courier_tip>0?Price_Formatter::formatNumber($items->courier_tip):'',
					    'total'=>$items->total>0?Price_Formatter::formatNumber($items->total):'',
                        'date_created'=>Date_Formatter::dateTime($items->date_created)
                    ];
                }                

                
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

    public function actionFPprintdailysales()
    {
        try {            

            $merchant_id = Yii::app()->merchant->merchant_id;
            $status_completed = AOrderSettings::getStatus(array('status_delivered','status_completed'));
			$date_now = date("Y-m-d");

            $printer_id = Yii::app()->input->post('printer_id');
			$date_start = Yii::app()->input->post('date_start');
			$date_end = Yii::app()->input->post('date_end');
			$date_start = !empty($date_start)?$date_start:$date_now;
			$date_end = !empty($date_end)?$date_end:$date_now;

            $model = AR_printer::model()->find("merchant_id=:merchant_id AND printer_id=:printer_id",[
                ":merchant_id"=>$merchant_id,
                ':printer_id'=>intval($printer_id)
            ]);
            if($model){
                $meta = AR_printer_meta::getMeta($printer_id,['printer_user','printer_ukey','printer_sn','printer_key']);					
                $printer_user = isset($meta['printer_user'])?$meta['printer_user']['meta_value1']:'';
                $printer_ukey = isset($meta['printer_ukey'])?$meta['printer_ukey']['meta_value1']:'';
                $printer_sn = isset($meta['printer_sn'])?$meta['printer_sn']['meta_value1']:'';
                $printer_key = isset($meta['printer_key'])?$meta['printer_key']['meta_value1']:'';

				$merchant_info = COrders::getMerchant($merchant_id,Yii::app()->language);
				$data = CReports::dailySalesSummaryPrint($merchant_id,$date_start,$date_end,$status_completed);		
				
				$payment_list = AttributesTools::PaymentProvider();
		        $services = COrders::servicesList(Yii::app()->language);    	
                
                $tpl = FPtemplate::DailySalesReport(
					$model->paper_width,
					$data,
					$merchant_info,
					$date_start,
					$date_end,
					$payment_list,
					$services
				);

                $stime = time();
                $sig = sha1($printer_user.$printer_ukey.$stime);               
                $result = FPinterface::Print($printer_user,$stime,$sig,$printer_sn,$tpl);

				$model = new AR_printer_logs();
			    $model->order_id = 0;
			    $model->merchant_id = intval($merchant_id);
			    $model->printer_number = $printer_sn;
			    $model->print_content = $tpl;
			    $model->job_id = $result;
			    $model->status = 'process';
			    $model->save();

				$result = [];
				$this->code = 1;
                $this->msg = t("Request succesfully sent to printer");
                $this->details = $result;		   
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);            
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionGetDailysales()
    {
        try {            

            $merchant_id = Yii::app()->merchant->merchant_id;
            $status_completed = AOrderSettings::getStatus(array('status_delivered','status_completed'));
			$date_now = date("Y-m-d");
            
			$date_start = Yii::app()->input->post('date_start');
			$date_end = Yii::app()->input->post('date_end');
			$date_start = !empty($date_start)?$date_start:$date_now;
			$date_end = !empty($date_end)?$date_end:$date_now;    

            $range = t("Date from: {start} to {end}",[
                '{start}'=>$date_start,
                '{end}'=>$date_end,
            ]);
            
            $data = [];
            $result = CReports::dailySalesSummaryPrint($merchant_id,$date_start,$date_end,$status_completed);
            
            $price_format = CMulticurrency::getAllCurrency();          
            $payment_list = AttributesTools::PaymentProvider();
		    $services = COrders::servicesList(Yii::app()->language);  
            
            $hide_currency = isset($this->data['hide_currency'])?$this->data['hide_currency']:false;             
             

            foreach ($result as $items) {
                if($price_format){
                    if(isset($price_format[$items['base_currency_code']])){
                        Price_Formatter::$number_format = $price_format[$items['base_currency_code']];
                    }						
                }         

                if($hide_currency==1){                
                    Price_Formatter::$number_format['currency_symbol'] = '';
                }             

                $data[] = [
                    'order_id'=>$items['order_id'],
                    'order_uuid'=>$items['order_uuid'],
                    'service_code'=>$items['service_code'],
                    'service_name'=> isset($services[$items['service_code']])?$services[$items['service_code']]['service_name']:$items['service_code'] ,
                    'payment_code'=>$items['payment_code'],
                    'payment_name'=>isset($payment_list[$items['payment_code']])?$payment_list[$items['payment_code']]:$items['payment_code'],
                    'sub_total'=>$items['sub_total']>0?Price_Formatter::formatNumber($items['sub_total']):'',
                    'delivery_fee'=>$items['delivery_fee']>0?Price_Formatter::formatNumber($items['delivery_fee']):'',
                    'tax_total'=>$items['tax_total']>0?Price_Formatter::formatNumber($items['tax_total']):'',
                    'courier_tip'=>$items['courier_tip']>0?Price_Formatter::formatNumber($items['courier_tip']):'',
                    'total'=>$items['total']>0?Price_Formatter::formatNumber($items['total']):'',
                    'date_created'=>Date_Formatter::dateTime($items['date_created'])
                ];
            }            

            $model_merchant = CMerchants::get($merchant_id);
            $this->code = 1; 
            $this->msg = "Ok";
            $this->details = [
                'data'=>$data,
                'restaurant_name'=>$model_merchant->restaurant_name,
                'range'=>$range
            ];

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actiongetOrderSummary()
    {
        try {            

            $merchant_id = Yii::app()->merchant->merchant_id;
	    	$initial_status = AttributesTools::initialStatus();
	    	$refund_status = AttributesTools::refundStatus();	
	    	$orders = 0; $order_cancel = 0; $total=0;
	    	
	    	$not_in_status = AOrderSettings::getStatus(array('status_cancel_order','status_rejection'));
	    	array_push($not_in_status,$initial_status);    		    	
	    	$orders = AOrders::getOrdersTotal($merchant_id,array(),$not_in_status);
	    	
	    	$status_cancel = AOrderSettings::getStatus(array('status_cancel_order'));		    	    	
		    $order_cancel = AOrders::getOrdersTotal($merchant_id,$status_cancel);
		    
		    $status_delivered = AOrderSettings::getStatus(array('status_delivered','status_completed'));
						
		    $total = AOrders::getOrderSummary($merchant_id,$status_delivered);
		    $total_refund = AOrders::getTotalRefund($merchant_id,$refund_status);
	    		    
            $data = [];
            $data[] = [
                'label'=>t("Orders"),
                'value'=>$orders
            ];
            $data[] = [
                'label'=>t("Cancel"),
                'value'=>$order_cancel
            ];
            $data[] = [
                'label'=>t("Total Orders"),
                'value'=>Price_Formatter::formatNumber($total)
            ];
            $data[] = [
                'label'=>t("Total refund"),
                'value'=>Price_Formatter::formatNumber($total_refund)
            ];
		    
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

    public function actionSaleReport()
    {
        try {

            $date_now = date("Y-m-d");
            $page = intval(Yii::app()->input->post('page'));
            $search = trim(Yii::app()->input->post('q'));    
            
            $date_start = trim(Yii::app()->input->post('date_start'));    
	        $date_end = trim(Yii::app()->input->post('date_end'));                

            $length =Yii::app()->params->list_limit;
            $merchant_id = Yii::app()->merchant->merchant_id;
                        

            $sortby = "order_id"; $sort = 'DESC';
            
            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
                $page = $page-1;
            }

            $page = intval($page)/intval($length);            
            $criteria=new CDbCriteria();	            
            $criteria->alias = "a";
            $criteria->select = "a.order_id, a.client_id, a.status, a.order_uuid , 
            a.payment_code, a.service_code,a.total, a.date_created,
            a.base_currency_code,
            b.meta_value as customer_name, 
            (
               select sum(qty)
               from {{ordernew_item}}
               where order_id = a.order_id
            ) as total_items,
            
            c.avatar as logo, c.path
            ";
            $criteria->join='LEFT JOIN {{ordernew_meta}} b on  a.order_id=b.order_id 
            LEFT JOIN {{client}} c on  a.client_id=c.client_id
            ';	         

            $criteria->condition = "a.merchant_id=:merchant_id AND b.meta_name=:meta_name ";
            $criteria->params  = array(
            ':merchant_id'=>intval($merchant_id),		  
            ':meta_name'=>'customer_name'
            );
            if(!empty($date_start) && !empty($date_end)){
                $criteria->addBetweenCondition("DATE_FORMAT(a.date_created,'%Y-%m-%d')", $date_start , $date_end );
            }
            $initial_status = AttributesTools::initialStatus();
            $criteria->addNotInCondition('a.status', (array) array($initial_status) );
            
            $criteria->order = "$sortby $sort";
            $count = AR_ordernew::model()->count($criteria); 
            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );        
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);    
            $page_count = $pages->getPageCount();
            
            if($page>0){
                if($page_raw>$page_count){
                    $this->code = 3;
                    $this->msg = t("end of results");
                    $this->responseJson();
                }
            }

            $data = [];            

            if($model = AR_ordernew::model()->findAll($criteria)){                         
                $payment_list = array();
                $status = COrders::statusList(Yii::app()->language);    	
                $services = COrders::servicesList(Yii::app()->language);                            
                try {
                   $payment_list = CPayments::PaymentList($merchant_id,true);            
                } catch (Exception $e) {                    
                }                               
                foreach ($model as $items) {                               
                   $data[] = [
                      'order_id'=>$items->order_id,
                      'order_uuid'=>$items->order_uuid,
                      'client_id'=>$items->client_id,
                      'customer_name'=>$items->customer_name,
                      'status'=>isset($status[$items->status])?$status[$items->status]['status']:$items->status,
                      'payment_code'=>$items->payment_code,
                      'payment_name'=>isset($payment_list[$items->payment_code])?$payment_list[$items->payment_code]['payment_name']:$items->payment_code,
                      'transaction_type'=>isset($services[$items->service_code])?$services[$items->service_code]['service_name']:$items->service_code,
                      'service_code'=>$items->service_code,
                      'total'=>Price_Formatter::formatNumber($items->total),
                      'date_created'=>Date_Formatter::dateTime($items['date_created'])
                   ];
                }                

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

    public function actionItemSummary()
    {
        try {
            $page = intval(Yii::app()->input->post('page'));
            $search = trim(Yii::app()->input->post('q'));             
            $length =Yii::app()->params->list_limit;

            $merchant_id = Yii::app()->merchant->merchant_id;
            $sortby = "b.item_name"; $sort = 'ASC';
            
            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
                $page = $page-1;
            }

            $status_completed = AOrderSettings::getStatus(array('status_delivered','status_completed'));

            $page = intval($page)/intval($length);            
            $criteria=new CDbCriteria();	            
            $criteria->alias = "a";
            $criteria->select ="
            a.item_id, a.size_id, a.price,
            b.item_name, b.photo, b.path,
            
            (
            select 
            concat(
                (price * SUM(qty)/SUM(qty)),';',
                SUM(qty),';',	        
                ((price * SUM(qty)/SUM(qty)) * SUM(qty))
            )
                
            from {{ordernew_item}}
            where item_id = a.item_id 
            and item_size_id = a.item_size_id
            and order_id IN (
                select order_id from {{ordernew}}
                where merchant_id = a.merchant_id
                and status in (".CommonUtility::arrayToQueryParameters($status_completed).") 	        
            )
            ) as item_group
            
            ";
            
            $criteria->join='LEFT JOIN {{item}} b on  a.item_id = b.item_id	';	  
	    	    
            $criteria->condition = "a.merchant_id=:merchant_id AND b.item_name IS NOT NULL";
            $criteria->params = array(':merchant_id'=>$merchant_id);    
                
            $criteria->order = "$sortby $sort";
            $count = AR_item_relationship_size::model()->count($criteria); 
            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );        
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);    
            $page_count = $pages->getPageCount();

            if($page>0){
                if($page_raw>$page_count){
                    $this->code = 3;
                    $this->msg = t("end of results");
                    $this->responseJson();
                }
            }

            $data = [];            

            if($model = AR_item_relationship_size::model()->findAll($criteria)){                                      
                foreach ($model as $item) {                                        
                    
                    $item_group = explode(";",$item->item_group);
                    $average_price = isset($item_group[0])?$item_group[0]:0;
                    $total_qty = isset($item_group[1])?$item_group[1]:0;	    		
                    $total = isset($item_group[2])?$item_group[2]:0;

                    $photo = CMedia::getImage($item->photo,$item->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('item'));
                    $data[] = [
                        'item_id'=>$item->item_id,
                        'photo'=>$photo,
                        'item_name'=>$item->item_name,
                        'price'=>Price_Formatter::formatNumber($average_price),
	    		        'qty'=>Price_Formatter::convertToRaw($total_qty,0),
	    		        'total'=>Price_Formatter::formatNumber($total)
                    ];
                }                    
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

    public function actionsearchfooditems()
    {
        try {            

            $merchant_id = Yii::app()->merchant->merchant_id;
            $q = Yii::app()->input->post('q');
            $exchange_rate = 1;

            $options_merchant = OptionsTools::find(['merchant_timezone','merchant_default_currency'],$merchant_id);
			$merchant_timezone = isset($options_merchant['merchant_timezone'])?$options_merchant['merchant_timezone']:'';					
			if(!empty($merchant_timezone)){
				Yii::app()->timezone = $merchant_timezone;
			}

            $items_not_available = CMerchantMenu::getItemAvailability($merchant_id,date("w"),date("H:h:i"));	
		    $category_not_available = CMerchantMenu::getCategoryAvailability($merchant_id,date("w"),date("H:h:i"));		 

            CMerchantMenu::setExchangeRate($exchange_rate);

            $items = CMerchantMenu::getSimilarItems($merchant_id,Yii::app()->language,100,$q,$items_not_available,$category_not_available);			
			$this->code = 1; $this->msg = "ok";			
			$this->details = [				
				'data'=>$items,			
			];

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionpaymentlist()
    {
        try {
            $page = intval(Yii::app()->input->post('page'));
            $search = trim(Yii::app()->input->post('q'));             
            $length =Yii::app()->params->list_limit;

            $sortby = "a.date_created"; $sort = 'DESC';
            
            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
                $page = $page-1;
            }

            $page = intval($page)/intval($length);            
            $criteria=new CDbCriteria();	            
            $criteria->alias = "a";
            $criteria->select = "a.*,
            b.payment_name,
            b.logo_class,
            b.logo_type,
            b.logo_image,
            b.path
            ";            
            $criteria->join='LEFT JOIN {{payment_gateway}} b on a.payment_id = b.payment_id ';
            $criteria->condition = "a.merchant_id=:merchant_id 
            AND b.payment_code IN (
                select meta_value from {{merchant_meta}}
                where meta_name='payment_gateway'
                and meta_value = a.payment_code
                and merchant_id =:merchant_id
            )
            ";		
            $criteria->params = [
                ':merchant_id'=> Yii::app()->merchant->merchant_id,
            ];
            
            $criteria->order = "$sortby $sort";
            $count = AR_payment_gateway_merchant::model()->count($criteria); 
            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );        
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);    
            $page_count = $pages->getPageCount();

            if($page>0){
                if($page_raw>$page_count){
                    $this->code = 3;
                    $this->msg = t("end of results");
                    $this->responseJson();
                }
            }

            $data = [];            

            if($model = AR_payment_gateway_merchant::model()->findAll($criteria)){     
                $payment_status = COrders::paymentStatusList2(Yii::app()->language,'gateway');                                                                 
                foreach ($model as $item) {                                                            
                    $logo = CMedia::getImage($item->logo_image,$item->path,Yii::app()->params->size_image,CommonUtility::getPlaceholderPhoto('payment'));
                    $data[] = [                                        
                        'payment_uuid'=>$item->payment_uuid,
                        'payment_name'=>$item->payment_name,          
                        'logo_type'=>$item->logo_type,          
                        'logo'=>$logo,
                        'status_raw'=>$item->status,
                        'status'=>isset($payment_status[$item->status])?$payment_status[$item->status]['title']:$item->status,          
                        'date_created'=>Date_Formatter::dateTime($item->date_created),                       
                    ];
                }                
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

    public function actiondeletePayment()
    {
        try {            

            $id = Yii::app()->input->post('id');
            $merchant_id = (integer) Yii::app()->merchant->merchant_id;

            $model = AR_payment_gateway_merchant::model()->find('merchant_id=:merchant_id AND payment_uuid=:payment_uuid',
		    array(':merchant_id'=>$merchant_id, ':payment_uuid'=>$id ));
            if($model){
                $model->delete();
                $this->code = 1;
                $this->msg = "Ok";
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionPaymentProviderByMerchant()
    {
        try {            

            if($data = AttributesTools::PaymentProviderByMerchant(Yii::app()->merchant->merchant_id)){
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = CommonUtility::ArrayToLabelValue($data);
            } else $this->msg = t(HELPER_NO_RESULTS);
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionUpdatePayment()
    {
        $this->actionAddPayment(true);        
    }

    public function actionAddPayment($update=false)
    {
        try {            

            $id = isset($this->data['id'])?$this->data['id']:null;
            $payment_id = isset($this->data['payment_id'])?$this->data['payment_id']:0;
            $status = isset($this->data['status'])?$this->data['status']:'';
            $is_live = isset($this->data['is_live'])?intval($this->data['is_live']):0;
            $field_data = isset($this->data['field_data'])?$this->data['field_data']:'';

            $attr_json = array(); $instructions = array();
            

            if($update){
                $model = AR_payment_gateway_merchant::model()->findByPk( $id );
                $attr_json = !empty($model->attr_json)?json_decode($model->attr_json,true):array();	
                $instructions=!empty($model->attr4)?json_decode($model->attr4,true):array();
                $model->scenario = "update";
                if(!$model){				
                    $this->msg = t(HELPER_RECORD_NOT_FOUND);
                    $this->responseJson();  
                }	                
            } else {
                $model=new AR_payment_gateway_merchant;	
			    $model->scenario = "create";
            }

            $model->status = $status;            
            if($update){                
                $model->is_live = $is_live;
                $model->attr1 = isset($field_data['attr1'])?$field_data['attr1']:'';
                $model->attr2 = isset($field_data['attr2'])?$field_data['attr2']:'';
                $model->attr3 = isset($field_data['attr3'])?$field_data['attr3']:'';
                $model->attr4 = isset($field_data['attr4'])?$field_data['attr4']:'';
                $model->attr5 = isset($field_data['attr5'])?$field_data['attr5']:'';
                $model->attr6 = isset($field_data['attr6'])?$field_data['attr6']:'';
                $model->attr7 = isset($field_data['attr7'])?$field_data['attr7']:'';
                $model->attr8 = isset($field_data['attr8'])?$field_data['attr8']:'';
                $model->attr9 = isset($field_data['attr9'])?$field_data['attr9']:'';
            } else {
                $model->payment_id = $payment_id;
                $model->merchant_id = Yii::app()->merchant->merchant_id;          
            }                                 
            if($model->save()){
                $this->code = 1;
                $this->msg = t(Helper_success);
                $this->details = [
                    'payment_uuid'=>$model->payment_uuid
                ];
            } else $this->msg = CommonUtility::parseError( $model->getErrors() );
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }
    
    public function actiongetPayment()
    {
        try {            

            $id = Yii::app()->input->post('id');            
            $merchant_id = (integer) Yii::app()->merchant->merchant_id;
            $model = AR_payment_gateway_merchant::model()->find('merchant_id=:merchant_id AND payment_uuid=:payment_uuid',
		    array(':merchant_id'=>$merchant_id, ':payment_uuid'=>$id ));            
            if($model){                
                $this->code = 1;
                $this->msg = "Ok";
                $attr_json = !empty($model->attr_json)?json_decode($model->attr_json,true):'';	
			    $instructions=!empty($model->attr4)?json_decode($model->attr4,true):'';		
                $new_instructions = [];                
                if(is_array($instructions) && count($instructions)>=1){
                    foreach ($instructions as $key => $items) {     
                        if($model->payment_code=="stripe"){
                            $items.="/?merchant_id=$model->merchant_id";
                        }
                        $new_instructions[$key]=t($items,[
                            '{{site_url}}'=>CommonUtility::getHomebaseUrl()
                        ]);
                    }
                }                

                $field_value = [
                    'attr1'=>$model->attr1,
                    'attr2'=>$model->attr2,
                    'attr3'=>$model->attr3,
                    'attr4'=>$model->attr4,
                    'attr5'=>$model->attr5,
                    'attr6'=>$model->attr6,
                    'attr7'=>$model->attr7,
                    'attr8'=>$model->attr8,
                    'attr9'=>$model->attr9,
                ];
                $this->details = [
                    'payment_code'=>$model->payment_code,
                    'status'=>$model->status,
                    'attr_json'=>$attr_json,
                    'is_live'=>$model->is_live==1?true:false,
                    'instructions'=>$new_instructions,
                    'field_value'=>$field_value
                ];
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionbankdepositlist()
    {
        try {
            $page = intval(Yii::app()->input->post('page'));
            $search = trim(Yii::app()->input->post('q'));             
            $length =Yii::app()->params->list_limit;

            $sortby = "deposit_id"; $sort = 'DESC';
            
            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
                $page = $page-1;
            }

            $page = intval($page)/intval($length);            
            $criteria=new CDbCriteria();	            
            $criteria->alias="a";
            $criteria->select ="a.*,
            (
                select order_uuid from {{ordernew}}
                where order_id=a.transaction_ref_id
            ) as order_uuid
            ";
    
            $criteria->addCondition("merchant_id=:merchant_id");
            $criteria->params = [
                ':merchant_id'=>intval(Yii::app()->merchant->merchant_id)
            ];
            
            $criteria->order = "$sortby $sort";
            $count = AR_bank_deposit::model()->count($criteria); 
            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );        
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);    
            $page_count = $pages->getPageCount();

            if($page>0){
                if($page_raw>$page_count){
                    $this->code = 3;
                    $this->msg = t("end of results");
                    $this->responseJson();
                }
            }

            $data = [];            

            if($model = AR_bank_deposit::model()->findAll($criteria)){                                      
                foreach ($model as $item) {            
                    $status_list = AttributesTools::BankStatusList();                                                
                    $data[] = [                     
                        'deposit_id'=>$item->deposit_id,
                        'deposit_uuid'=>$item->deposit_uuid,      
                        'transaction_ref_id'=>$item->transaction_ref_id,                  
                        'account_name'=>$item->account_name,
                        'amount'=>Price_Formatter::formatNumber($item->amount),
                        'reference_number'=>$item->reference_number,
                        'proof_image'=>CMedia::getImage($item->proof_image,$item->path,Yii::app()->params->size_image,CommonUtility::getPlaceholderPhoto('item')),
                        'status_raw'=>$item->status,
                        'status'=>isset($status_list[$item->status])?$status_list[$item->status]:$item->status,
                        'date_created'=>Date_Formatter::dateTime($item->date_created),                        
                    ];
                }                
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
    
    public function actiondeleteBankDeposit()
    {
        try {                      

            $id = Yii::app()->input->post('id');
            $merchant_id = (integer) Yii::app()->merchant->merchant_id;

            $model = AR_bank_deposit::model()->find('merchant_id=:merchant_id AND deposit_uuid=:deposit_uuid',
		    array(':merchant_id'=>$merchant_id, ':deposit_uuid'=>$id ));
            if($model){
                $model->delete();
                $this->code = 1;
                $this->msg = "Ok";
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actiongetBankDeposit()
    {
        try {                      

            $id = Yii::app()->input->post('id');
            $merchant_id = (integer) Yii::app()->merchant->merchant_id;

            $model = AR_bank_deposit::model()->find('merchant_id=:merchant_id AND deposit_uuid=:deposit_uuid',
		    array(':merchant_id'=>$merchant_id, ':deposit_uuid'=>$id ));
            if($model){                
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'order_id'=>$model->transaction_ref_id,
                    'account_name'=>$model->account_name,
                    'amount'=>Price_Formatter::convertToRaw($model->amount),
                    'reference_number'=>$model->reference_number,
                    'status'=>$model->status,                    
                ];
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actionUpdateBankDeposit()
    {
        try {                      
                        
            $merchant_id = (integer) Yii::app()->merchant->merchant_id;
            $id = isset($this->data['id'])?$this->data['id']:0;            

            $model = AR_bank_deposit::model()->find('merchant_id=:merchant_id AND deposit_uuid=:deposit_uuid',
		    array(':merchant_id'=>$merchant_id, ':deposit_uuid'=>$id ));
            if($model){ 
                $model->account_name = isset($this->data['account_name'])?$this->data['account_name']:'';
                $model->amount = isset($this->data['amount'])?floatval($this->data['amount']):0;
                $model->reference_number = isset($this->data['reference_number'])?$this->data['reference_number']:'';
                $model->status = isset($this->data['status'])?$this->data['status']:'';
                if($model->save()){
                    $this->code = 1;
                    $this->msg = t(Helper_update);
                } else $this->msg = CommonUtility::parseModelErrorToString( $model->getErrors() );
            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }
    
    public function actiongetRefreshAccess()
    {
        try {             
            
            $menu_access = [];
            $main_account = Yii::app()->merchant->getState("main_account");
            if($main_account==1){
                if(MerchantTools::hasMerchantSetMenu(Yii::app()->merchant->merchant_id)){
                  $menu_access = MerchantTools::getMerchantMeta(Yii::app()->merchant->merchant_id,'menu_access');
                }
            } else {                
                try {
                    $menu_access = MerchantTools::getMerchantMenuRolesAccess(Yii::app()->merchant->id,Yii::app()->merchant->merchant_id);
                } catch (Exception $e) {}
            }	
            
            $menu_access = JWT::encode($menu_access, CRON_KEY, 'HS256'); 

            $this->code = 1;
            $this->msg = "Ok";
            $this->details = [
                'menu_access'=>$menu_access
            ];

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actiongetCountOrder()
    {
        try {                      

            $merchant_id = Yii::app()->merchant->merchant_id;
			$new_order = AOrders::getOrderTabsStatus('new_order');	
			$order_processing = AOrders::getOrderTabsStatus('order_processing');	
			$order_ready = AOrders::getOrderTabsStatus('order_ready');
			$completed_today = AOrders::getOrderTabsStatus('completed_today');			

			
			$status_scheduled = (array) $new_order;				
			
			if($order_processing){				
				foreach ($order_processing as $order_processing_val) {
					array_push($status_scheduled,$order_processing_val);
				}
			}
													
			$new = AOrders::getOrderCountPerStatus($merchant_id,$new_order,date("Y-m-d"),false);
			$processing = AOrders::getOrderCountPerStatus($merchant_id,$order_processing,date("Y-m-d"),false);
			$ready = AOrders::getOrderCountPerStatus($merchant_id,$order_ready,date("Y-m-d"),false);
			$completed = AOrders::getOrderCountPerStatus($merchant_id,$completed_today,date("Y-m-d"),false);
			$scheduled = AOrders::getOrderCountSchedule($merchant_id,$status_scheduled,date("Y-m-d"),false);
			$all_orders = AOrders::getAllOrderCount($merchant_id);
			
			$not_viewed = AOrders::OrderNotViewed($merchant_id,$new_order,date("Y-m-d"));			
			
			$data = array(
			  'new_order'=>$new,
			  'order_processing'=>$processing,
			  'order_ready'=>$ready,
			  'completed_today'=>$completed,
			  'scheduled'=>$scheduled,
			  'all_orders'=>$all_orders,
              'all'=>$all_orders,
			  'not_viewed'=>$not_viewed,
			);
			
			$this->code = 1;
			$this->msg = "OK";
			$this->details = $data;

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

    public function actiontest()
    {
        try {               
            
            

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();  
    }

}
// end controller