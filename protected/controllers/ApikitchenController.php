<?php
require 'intervention/vendor/autoload.php';
require 'php-jwt/vendor/autoload.php';
use Intervention\Image\ImageManager;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

define("LANG_CATEGORY","kitchen");

class ApikitchenCommon extends CController {

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
                     'getSettings','Login','ForgotPassword','resendResetEmail'
                 ),
				 'expression' => array('AppKitchenIdentity','verifyToken')
			 ),
             array('deny',
                  'actions'=>array(
                    'getOrders','updateitemstatus','setOrderBump','OrderHistory','Recall','clearOrders','moveOrder','getOrdersCount',
                    'setRepeatNotification','saveScheduledTransitionTime','getNotifications','notificationList','clearNotification','requestcode',
                    'deleteAccount','SavePrinter','PrinterList','GetPrinter','DeletePrinter','getTicket','TestPrintFP','PrintTicketFP'
                 ),
				 'expression' => array('AppKitchenIdentity','verifyMerchant')
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
			'captcha_customer_signup','image_resizing','merchant_specific_country','map_provider','google_geo_api_key','mapbox_access_token',
			'signup_enabled_verification','mobilephone_settings_default_country','mobilephone_settings_country','website_title','merchant_enabled_registration',
            'merchant_specific_country','registration_terms_condition','registration_program','mt_android_download_url','mt_ios_download_url','mt_app_version_android',
            'mt_app_version_ios','enabled_language_merchant_app','multicurrency_enabled','driver_on_demand_availability','default_location_lat','default_location_lng',
            'points_redemption_policy','points_redeemed_points','points_redeemed_value','points_maximum_redeemed','points_minimum_redeemed','site_food_avatar',
            'points_enabled','points_earning_rule','points_earning_points','points_minimum_purchase','points_maximum_purchase'
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

class ApikitchenController extends ApikitchenCommon  {

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

    
    public function actiongetSettings()
    {
        try {

            $enabled_language = true;
			$default_language = KMRS_DEFAULT_LANGUAGE;

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
			
			$language_list = AttributesTools::getLanguageList();
            $kitchen_status = AttributesTools::kitchenStatus();
            $kitchen_status_list = CommonUtility::ArrayToLabelValue($kitchen_status);

            $order_status_list = AttributesTools::statusList2(Yii::app()->language); 
            $order_status_list_value = CommonUtility::ArrayToLabelValue($order_status_list); 
            $services = COrders::servicesList(Yii::app()->language,'','publish');            
            
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
			];            
			try {
				$realtime_settings = JWT::encode($realtime, CRON_KEY, 'HS256');
			} catch (Exception $e) {
				$realtime_settings = '';
			}				

            $legal_menu = [
                'privacy-policy'=>t("Privacy policy",[],LANG_CATEGORY),
                'terms-condition'=>t("Terms and conditions",[],LANG_CATEGORY),
                'aboutus'=>t("About us",[],LANG_CATEGORY),
            ];

            $this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'money_config'=>$money_config,
				'enabled_language'=>$enabled_language,
				'default_lang'=>$default_language,
				'language_list'=>$language_list,	
                'kitchen_status'=>$kitchen_status,
                'kitchen_status_list'=>$kitchen_status_list,
                'order_status_list'=>$order_status_list,
                'order_status_list_value'=>$order_status_list_value,
                'services'=>$services,
                'realtime_settings'=>$realtime_settings,
                'legal_menu'=>$legal_menu,
                'printer_list'=>CommonUtility::PrinterList(),
			];

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
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
            $this->msg = t("Login Succesful",[],LANG_CATEGORY);
            $this->userData();
        } else $this->msg = CommonUtility::parseError( $model->getErrors() );

        $this->responseJson();
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
            } else $this->msg = t("Email address not found",[],LANG_CATEGORY);
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
                    ),LANG_CATEGORY);
             } else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
            } else $this->msg = t(HELPER_NO_RESULTS,[],LANG_CATEGORY);
        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
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
           'token'=>Yii::app()->merchant->logintoken,
           'username'=>Yii::app()->merchant->username,
           'hash'=>Yii::app()->merchant->hash,
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

    public function actiongetOrders()
    {
        try {                              


            $merchant_id = Yii::app()->merchant->merchant_id;            
            $filters = isset($this->data['filters'])?$this->data['filters']:[];              
            $whento_deliver = isset($filters['whento_deliver'])?$filters['whento_deliver']:'now';
            $data = Ckitchen::getNewKitchenOrders($merchant_id,$filters,Yii::app()->language);             
            
            $this->code = 1;
            $this->msg = "Ok";
            $this->details = [
                'count'=>count($data),
                'count_display'=> $whento_deliver=="now" ? Yii::t(LANG_CATEGORY, '{n} open order|{n} open orders', count($data)) : Yii::t(LANG_CATEGORY, '{n} scheduled order|{n} scheduled orders', count($data)),
                'data'=>$data,                
            ];
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }
        $this->responseJson();
    }

    public function actionupdateitemstatus()
    {
        try {
            
            $status = isset($this->data['status'])?$this->data['status']:'';
            $items = isset($this->data['items'])?$this->data['items']:'';
            if(is_array($items) && count($items)>=1){
                foreach ($items as $item) {                    
                    if($item['checked']==1){
                        $model = AR_kitchen_order::model()->find("kitchen_order_id=:kitchen_order_id",[
                            ':kitchen_order_id'=>$item['kitchen_order_id']
                        ]);
                        if($model){
                            $model->merchant_uuid = Yii::app()->merchant->merchant_uuid;
                            $model->scenario = "item_status_update";
                            $model->item_status = $status;
                            $model->save();
                        }
                    }
                }
                $this->code = 1;
                $this->msg = t("Line items status updated",[],LANG_CATEGORY);    
                $this->getOrders();

            } else $this->msg = t("No items available",[],LANG_CATEGORY);
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }
        $this->responseJson();
    }

    private function getOrders(){
        $data = [];
        try {            
            $merchant_id = Yii::app()->merchant->merchant_id;                 
            $filters = isset($this->data['filters'])?$this->data['filters']:[];                                 
            $data = Ckitchen::getNewKitchenOrders($merchant_id,$filters,Yii::app()->language);            
        } catch (Exception $e) {}      
        $this->details = [
            'count'=>count($data),
            'count_display'=>Yii::t(LANG_CATEGORY, '{n} open order|{n} open orders', count($data)),
            'data'=>$data,                
        ];
    }

    public function actionsetOrderBump()
    {
        try {
            
            $merchant_id = Yii::app()->merchant->merchant_id;
            //$order_reference = Yii::app()->input->post('order_reference');
            $order_reference = isset($this->data['order_reference'])?$this->data['order_reference']:'';
            $model = AR_kitchen_order::model()->find("merchant_id=:merchant_id AND order_reference=:order_reference",[
                ':merchant_id'=>$merchant_id,
                ':order_reference'=>$order_reference,
            ]);
            if($model){
                AR_kitchen_order::model()->updateAll(array(
                    'is_completed' =>1,
                    'date_completed'=>CommonUtility::dateNow()
                ), "order_reference=:order_reference",[
                    ":order_reference"=>$order_reference
                ]);		
                $this->code = 1;
                $this->msg = "Ok";
                $this->getOrders();
            } else $this->msg = t("Kitchen order not found",[],LANG_CATEGORY);
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }
        $this->responseJson();
    }

    public function actionOrderHistory()
    {
        try {
            
            CommonUtility::mysqlSetTimezone();
            $merchant_id = Yii::app()->merchant->merchant_id;
            $startRow = isset($this->data['startRow'])?$this->data['startRow']:0;
            $fetchCount = isset($this->data['fetchCount'])?$this->data['fetchCount']:0;
            $filter = isset($this->data['filter'])?$this->data['filter']:[];
            $sortBy = isset($this->data['sortBy'])?  (!empty($this->data['sortBy'])?$this->data['sortBy']:'order_reference') :'order_reference';
            $descending = isset($this->data['descending'])?$this->data['descending']:false;
            $sortByAscDesc = $descending==true?'desc':'asc';
                                
            $filter = isset($this->data['filter'])?$this->data['filter']:'';
            $q = isset($filter['q'])?$filter['q']:'';
            $order_type = isset($filter['order_type'])?$filter['order_type']:'';            

            $criteria = new CDbCriteria;
            $criteria->alias = "a";
            $criteria->select = "
            a.*,
            CASE
            WHEN TIMESTAMPDIFF(MINUTE, date_completed, NOW()) > 30 THEN 0
            ELSE 1
            END AS can_recall
            ";
            $criteria->condition = "a.merchant_id=:merchant_id AND a.is_completed=:is_completed";
            $criteria->params = [
                ':merchant_id'=>$merchant_id,
                ':is_completed'=>1
            ];            
            if(is_array($order_type) && count($order_type)>=1){
                $criteria->addInCondition("a.transaction_type",$order_type);
            }
            if(!empty($q)){
               $criteria->addSearchCondition("order_reference",$q);
            }
            $criteria->group = "a.order_reference";
            $criteria->order = "$sortBy $sortByAscDesc";            

            $totalItemCount = AR_kitchen_order::model()->count($criteria);
            $pagination = new CPagination($totalItemCount);
            $pagination->pageSize = $fetchCount;
            $pagination->currentPage = $startRow;

            $criteria->limit = $fetchCount;
            $criteria->offset =  $startRow;
            
            if($model = AR_kitchen_order::model()->findAll($criteria)){                
                $order_reference_list = [];
                foreach ($model as $items) { 
                    $order_reference_list[] = $items->order_reference;
                }                

                $items = Ckitchen::getKicthenItems($order_reference_list);                

                $data = [];
                foreach ($model as $value) {                    
                    $data[] = [
                        'order_reference'=>$value->order_reference,
                        'customer_name'=>$value->customer_name,
                        'transaction_type'=>t($value->transaction_type),
                        'created_at'=>Date_Formatter::dateTime($value->created_at,"dd.MM.yyyy, HH:mm",true),	
                        'items'=>isset($items[$value->order_reference])?$items[$value->order_reference]['items']:[],
                        'can_recall'=>$value->can_recall==1?true:false
                    ];
                }
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'total'=>intval($totalItemCount),                  
                    'data'=>$data,
                ];
            } else $this->msg = t("No available data",[],LANG_CATEGORY);
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }
        $this->responseJson();
    }

    public function actionRecall()
    {
        try {
            
            $order_reference = Yii::app()->input->post('order_reference');
            $this->code = 1;
            $this->msg = "Ok";      
            
            AR_kitchen_order::model()->updateAll(array(
                'is_completed' =>0
            ), "order_reference=:order_reference",[
                ":order_reference"=>$order_reference
            ]);		
            $this->code = 1;
            $this->msg = "Ok";

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }
        $this->responseJson();
    }

    public function actionclearOrders()
    {
        try {
            
            AR_kitchen_order::model()->deleteAll('is_completed = :is_completed',[
                ':is_completed'=>1
            ]);
            $this->code = 1;
            $this->msg = "Ok";
            
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }
        $this->responseJson();
    }

    public function actionmoveOrder()
    {
        try {

            $merchant_id = Yii::app()->merchant->merchant_id;
            $order_reference = $this->data['order_reference']?$this->data['order_reference']:null;            
            $model = AR_kitchen_order::model()->find("merchant_id=:merchant_id AND order_reference=:order_reference",[
                ':merchant_id'=>$merchant_id,
                ':order_reference'=>$order_reference,
            ]);
            if($model){
                AR_kitchen_order::model()->updateAll(array(
                    'whento_deliver' =>'now',               
                    'request_time'=>CommonUtility::dateNow(),
                    'updated_at'=>CommonUtility::dateNow()
                ), "order_reference=:order_reference",[
                    ":order_reference"=>$order_reference
                ]);		
                $this->code = 1;
                $this->msg = "Ok";
                $this->getOrders();
            } else $this->msg = t("Kitchen order not found",[],LANG_CATEGORY);
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }
        $this->responseJson();
    }

    public function actiongetOrdersCount()
    {
        try {

            $merchant_id = Yii::app()->merchant->merchant_id;
            $current = Ckitchen::getCurrentCount($merchant_id);
            $scheduled = Ckitchen::getScheduledCount($merchant_id);
            $this->code = 1;
            $this->msg = "Ok";
            $this->details = [
                'current'=>$current,
                'scheduled'=>$scheduled,
                'current_display'=>Yii::t(LANG_CATEGORY, '{n} open order|{n} open orders', $current),
                'scheduled_display'=>Yii::t(LANG_CATEGORY, '{n} open order|{n} open orders', $scheduled),
            ];            
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }
        $this->responseJson();
    }

    public function actionsetRepeatNotification()
    {
        try {

            $merchant_id = Yii::app()->merchant->merchant_id;
            $value = Yii::app()->input->post("value");
            AR_merchant_meta::saveMeta($merchant_id,'kitchen_repeat_notification',$value);

            $this->code = 1;
            $this->msg = "Ok";
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }
        $this->responseJson();
    }

    public function actionsaveScheduledTransitionTime()
    {
        try {

            $merchant_id = Yii::app()->merchant->merchant_id;
            $time = Yii::app()->input->post("time");
            AR_merchant_meta::saveMeta($merchant_id,'scheduled_order_transition_time',$time);
            $this->code = 1;
            $this->msg = "Ok";
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }
        $this->responseJson();
    }

    public function actiongetNotifications()
    {
        try {
            $channel = Yii::app()->merchant->merchant_uuid."-kitchen";
            $data = CNotificationData::getList( $channel );			
			$this->code = 1; $this->msg = "ok";
			$this->details = $data;
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }
        $this->responseJson();
    }

    public function actionnotificationList()
    {
        try {
                        
            $page = Yii::app()->input->get('page');            
            $page_size = 10;
            $page = ($page-1)*$page_size;
            $channel = Yii::app()->merchant->merchant_uuid."-kitchen";

            $criteria = new CDbCriteria;
            $criteria->alias = "a";

            $criteria->condition = "notication_channel=:notication_channel";
            $criteria->params = [
                ':notication_channel'=>$channel,                
            ];                        
            $criteria->order = "date_created DESC";            

            $totalItemCount = AR_notifications::model()->count($criteria);
            $pagination = new CPagination($totalItemCount);
            $pagination->pageSize = $page_size;
            $pagination->currentPage = $page;

            $criteria->limit = $page_size;
            $criteria->offset =  $page;            
            if($model = AR_notifications::model()->findAll($criteria)){
                $data = [];
                foreach ($model as $items) {                    
                    $data[] = [
                        'notification_uuid'=>$items->notification_uuid,
                        'message'=>t($items->message,json_decode($items->message_parameters)),
                        'date_created'=>Date_Formatter::dateTime($items->date_created)
                    ];
                }
                $this->code = 1;
                $this->msg = "Ok";
                $this->details = [
                    'data'=>$data,
                    'page'=>$page
                ];
            } else {
                $this->msg = t(HELPER_NO_RESULTS);
            }
        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }
        $this->responseJson();
    }

    public function actionclearNotification()
    {
        try {

            $channel = Yii::app()->merchant->merchant_uuid."-kitchen";      
            $criteria = new CDbCriteria();			
            $criteria->condition = "notication_channel=:notication_channel";
            $criteria->params = [
                ':notication_channel'=>$channel
            ];
			AR_notifications::model()->deleteAll($criteria);		
            $this->code = 1;
            $this->msg = "Ok";      

        } catch (Exception $e) {
            $this->msg = t($e->getMessage());		   
        }
        $this->responseJson();
    }

    public function actiongetPage()
    {
        try {
            $page_id = Yii::app()->input->post('page_id');            
            $legal_menu = [
                'privacy-policy'=>'kitchen_page_privacy_policy',
                'terms-condition'=>'kitchen_page_terms',
                'aboutus'=>'kitchen_page_aboutus',
            ];
            $page_id = isset($legal_menu[$page_id])?$legal_menu[$page_id]:null;            

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

    public function actionrequestcode()
    {
        try {

            $merchant_id = Yii::app()->merchant->id;            
            $model = AR_merchant_user::model()->findByPk($merchant_id);
            if($model){
                $digit_code = CommonUtility::generateNumber(3,true);
                $model->verification_code = $digit_code;
                $model->scenario="resend_otp";
                if($model->save()){
                    // SEND EMAIL HERE
                    $this->code = 1;
                    $this->msg = t("We sent a code to {{email_address}}.",array(
                        '{{email_address}}'=> CommonUtility::maskEmail($model->contact_email)
                    ),LANG_CATEGORY);
                } else $this->msg = CommonUtility::parseError($model->getErrors());
            } else $this->msg = t("Record not found",[],LANG_CATEGORY);
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
                    $this->msg = t("Your request to delete your account has been submitted",[],LANG_CATEGORY);
                    $this->details = [];
                } else $this->msg[] = t("Invalid verification code",[],LANG_CATEGORY);
            } else $this->msg[] = t("User not login or session has expired",[],LANG_CATEGORY);
            $this->responseJson();

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actionSavePrinter()
    {
        try {
            
            $merchant_id = intval(Yii::app()->merchant->merchant_id);   

            $printer_id = intval(Yii::app()->input->post('printer_id'));        
            $printer_model = Yii::app()->input->post('printer_model');
            $printer_name = Yii::app()->input->post('printer_name');
            $paper_width = Yii::app()->input->post('paper_width');
            $auto_print = Yii::app()->input->post('auto_print');
            $auto_close = Yii::app()->input->post('auto_close');

            $printer_user = Yii::app()->input->post('printer_user');
            $printer_ukey = Yii::app()->input->post('printer_ukey');
            $printer_sn = Yii::app()->input->post('printer_sn');
            $printer_key = Yii::app()->input->post('printer_key');

            $printer_bt_name = Yii::app()->input->post('printer_bt_name');
            $device_id = Yii::app()->input->post('device_id');
            $service_id = Yii::app()->input->post('service_id');
            $characteristic = Yii::app()->input->post('characteristic');
            $print_type = Yii::app()->input->post('print_type');

            if($printer_id>0){
                $model = AR_printer::model()->find("merchant_id=:merchant_id AND  printer_id=:printer_id",[
                    ':merchant_id'=>$merchant_id,
                    ':printer_id'=>$printer_id
                ]);
                if(!$model){
                    $this->msg = t("Printer record not found",[],LANG_CATEGORY);
                    $this->responseJson();
                }
            } else $model = new AR_printer;

            $model->platform = LANG_CATEGORY;
            $model->merchant_id = $merchant_id;
            $model->printer_model = $printer_model;
            $model->printer_name = $printer_name;
            $model->paper_width = $paper_width;
            $model->auto_print = $auto_print;            
            $model->auto_close = $auto_close;   

            $model->printer_bt_name = $printer_bt_name;
            $model->device_id = $device_id;
            $model->service_id = $service_id;
            $model->characteristics = $characteristic;
            $model->print_type = $print_type;

            $model->printer_uuid =  '';
            $model->printer_user = $printer_user;
            $model->printer_ukey = $printer_ukey;
            $model->printer_sn = $printer_sn;
            $model->printer_key = $printer_key;            

            if($model->printer_model=="feieyun"){
                $model->scenario = $printer_id>0?"feieyun_update": "feieyun_add";
            }       

            if($model->save()){
	    		$this->code = 1; 
                $this->msg = $printer_id>0? t("Printer successfully updated",[],LANG_CATEGORY): t("Printer successfully added",[],LANG_CATEGORY);
	    	} else $this->msg = CommonUtility::parseError( $model->getErrors());            

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actionPrinterList()
    {
        try {

            $merchant_id = Yii::app()->merchant->merchant_id;       
            
            $limit = Yii::app()->params->list_limit;

            $page = intval(Yii::app()->input->post('page'));            
			$page_raw = intval(Yii::app()->input->post('page'));
			if($page>0){
				$page = $page-1;
			}

			$criteria=new CDbCriteria();                        
			$criteria->condition = "merchant_id=:merchant_id AND platform=:platform";
			$criteria->params  = array(
			   ':merchant_id'=>$merchant_id,
               ':platform'=>LANG_CATEGORY
			);
            $criteria->addNotInCondition("printer_model",['wifi']);
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
					$this->msg = t("end of results",[],LANG_CATEGORY);
					$this->responseJson();
				}
			}
            
            if($model = AR_printer::model()->findAll($criteria)){                
                $data = [];
                foreach ($model as $items) {
                    $data[] = [
                        'printer_id'=>$items->printer_id,
                        'printer_name'=>$items->printer_name,                        
                        'printer_model'=>$items->printer_model,                  
                        'service_id'=>$items->service_id, 
                        'characteristics'=>$items->characteristics, 
                        'paper_width'=>$items->paper_width, 
                        'print_type'=>$items->print_type, 
                        'auto_print'=>$items->auto_print==1?true:false,
                        'auto_close'=>$items->auto_close==1?true:false,
                        'paper_width'=>$items->paper_width.t("mm")                   
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
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actionGetPrinter()
    {
        try {
                        
            $printer_id = Yii::app()->input->get('printer_id');            
            $merchant_id = intval(Yii::app()->merchant->merchant_id);      
            
            $model = AR_printer::model()->find("merchant_id=:merchant_id AND printer_id=:printer_id",[
                ":merchant_id"=>$merchant_id,
                ':printer_id'=>intval($printer_id)
            ]);
            if($model){
                
                $data = [];
                $printer_user='';
                $printer_ukey='';
                $printer_sn='';
                $printer_key='';
                
                switch ($model->printer_model) {                    
                    case 'bluetooth':
                    case 'bleutooth':            
                        $data = [
                            'printer_id'=>$model->printer_id,
                            'printer_name'=>$model->printer_name,
                            'printer_model'=>$model->printer_model,
                            'paper_width'=>$model->paper_width,
                            'auto_print'=>$model->auto_print,
                            'auto_close'=>$model->auto_close,
                            'printer_bt_name'=>$model->printer_bt_name,
                            'device_id'=>$model->device_id,
                            'service_id'=>$model->service_id,
                            'characteristic'=>$model->characteristics,
                            'print_type'=>$model->print_type,
                        ];
                        break;
                    
                    case "feieyun":
                        $meta = AR_printer_meta::getMeta($printer_id,['printer_user','printer_ukey','printer_sn','printer_key']);                    
                        $printer_user = isset($meta['printer_user'])?$meta['printer_user']['meta_value1']:'';
                        $printer_ukey = isset($meta['printer_ukey'])?$meta['printer_ukey']['meta_value1']:'';
                        $printer_sn = isset($meta['printer_sn'])?$meta['printer_sn']['meta_value1']:'';
                        $printer_key = isset($meta['printer_key'])?$meta['printer_key']['meta_value1']:'';
                        $data = [
                            'printer_id'=>$model->printer_id,
                            'printer_name'=>$model->printer_name,
                            'printer_model'=>$model->printer_model,
                            'paper_width'=>$model->paper_width,
                            'auto_print'=>$model->auto_print,
                            'printer_user'=>$printer_user,
                            'printer_ukey'=>$printer_ukey,
                            'printer_sn'=>$printer_sn,
                            'printer_key'=>$printer_key,
                        ];
                        break;
                }
                
                $this->details = $data;
                $this->code = 1; $this->msg = "Ok";

            } else $this->msg = t(HELPER_RECORD_NOT_FOUND);
        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actionDeletePrinter()
    {
        try {

            $printer_id = Yii::app()->input->get('printer_id');            
            $merchant_id = intval(Yii::app()->merchant->merchant_id);     
            
            $model = AR_printer::model()->find("merchant_id=:merchant_id AND printer_id=:printer_id",[
                ":merchant_id"=>$merchant_id,
                ':printer_id'=>intval($printer_id)
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

    public function actiongetTicket()
    {
        try {
            
            $merchant_id = intval(Yii::app()->merchant->merchant_id);               
            $order_reference = Yii::app()->input->get('order_reference');
            $printer_id = Yii::app()->input->get('printer_id');            

            $filters = [
                'q'=>$order_reference,
                'single_search'=>true
            ];
            $data = Ckitchen::getNewKitchenOrders($merchant_id,$filters,Yii::app()->language); 
            $data = isset($data[0])?$data[0]:null;

            $printer = CommonUtility::getPrinterDetails($merchant_id,$printer_id);             
            $printer_settings = [
                'print_type'=>isset($printer['print_type'])?$printer['print_type']:'',
                'page_width'=>isset($printer['paper_width'])?$printer['paper_width']:'',
                'printer_bt_name'=>isset($printer['printer_bt_name'])?$printer['printer_bt_name']:'',
                'device_id'=>isset($printer['device_id'])?$printer['device_id']:'',
                'service_id'=>isset($printer['service_id'])?$printer['service_id']:'',
                'characteristics'=>isset($printer['characteristic'])?$printer['characteristic']:'',
                'auto_close'=>isset($printer['auto_close'])?$printer['auto_close']:'',
            ];
            $results = ThermalPrinterFormatter::RawKitchenTicket($data,$printer_settings);            
            $this->code = 1;
            $this->msg = "Ok";            
            $this->details = [
                'data'=>$results
            ];
        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actionTestPrintFP()
    {
        try {
            
            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $printer_id = Yii::app()->input->post("printer_id");
            $printer = CommonUtility::getPrinterDetails($merchant_id,$printer_id);
            $paper_width = isset($printer['paper_width'])?$printer['paper_width']:'';
            $tpl = FPtemplate::TestTemplate($paper_width);
            
            $printer_user = isset($printer['printer_user'])?$printer['printer_user']:'';
            $printer_sn = isset($printer['printer_sn'])?$printer['printer_sn']:'';
            $printer_ukey = isset($printer['printer_ukey'])?$printer['printer_ukey']:'';
            
            $stime = time();
            $sig = sha1($printer_user.$printer_ukey.$stime);               
            $result = FPinterface::Print($printer_user,$stime,$sig,$printer_sn,$tpl);
                        
            $this->code = 1;
            $this->msg = t("Request succesfully sent to printer",[],LANG_CATEGORY);
            $this->details = $result;

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

    public function actionPrintTicketFP()
    {
        try {

            $merchant_id = intval(Yii::app()->merchant->merchant_id);
            $printer_id = Yii::app()->input->post('printer_id');
            $order_reference = Yii::app()->input->post('order_reference');

            $printer = CommonUtility::getPrinterDetails($merchant_id,$printer_id);

            $paper_width = isset($printer['paper_width'])?$printer['paper_width']:'';
            $printer_user = isset($printer['printer_user'])?$printer['printer_user']:'';
            $printer_sn = isset($printer['printer_sn'])?$printer['printer_sn']:'';
            $printer_ukey = isset($printer['printer_ukey'])?$printer['printer_ukey']:'';
            
            $filters = [
                'q'=>$order_reference,
                'single_search'=>true
            ];
            $data = Ckitchen::getNewKitchenOrders($merchant_id,$filters,Yii::app()->language);             
            $data = isset($data[0])?$data[0]:null;

            $tpl = FPtemplate::Ticket($data,$paper_width);             
            
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

            $this->code = 1;
            $this->msg = t("Request succesfully sent to printer",[],LANG_CATEGORY);
            $this->details = $result;

        } catch (Exception $e) {
			$this->msg = $e->getMessage();
		}
		$this->responseJson();
    }

}
// end class