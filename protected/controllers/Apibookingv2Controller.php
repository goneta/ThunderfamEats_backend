<?php
require 'php-jwt/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Apibookingv2Controller extends CController
{
    public $code=2,$msg,$details,$data;

	public function __construct($id,$module=null){
		parent::__construct($id,$module);
		// If there is a post-request, redirect the application to the provided url of the selected language 
		if(isset($_POST['language'])) {
			$lang = $_POST['language'];
			$MultilangReturnUrl = $_POST[$lang];
			$this->redirect($MultilangReturnUrl);
		}
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

	public static function getAuthorizationHeader(){
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } else if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER["REDIRECT_HTTP_AUTHORIZATION"]);    
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));            
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }

	public static function getToken() {
        $headers = self::getAuthorizationHeader();        
        if (!empty($headers)) {
            if (preg_match('/token\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
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
                     'BookingSummary','BookingList','CancelReservation','updatebooking','reservetable'
                 ),
				 'expression' => array('AppIdentity','verifyCustomer')
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

	public function actionattributes()
	{
		try {

			$merchant_uuid =  Yii::app()->request->getQuery('merchant_uuid', null);  
			if(!$merchant_uuid){
				$this->msg = t("Invalid merchant UUID");
				$this->responseJson();
			}

			$merchant = CMerchants::getByUUID($merchant_uuid);            			
            $merchant_id = $merchant->merchant_id;

			$options_merchant = OptionsTools::find(['merchant_timezone'],$merchant_id);				
		    $merchant_timezone = isset($options_merchant['merchant_timezone'])?$options_merchant['merchant_timezone']:'';		
            if(!empty($merchant_timezone)){
                Yii::app()->timezone = $merchant_timezone;
            }

			$guest = []; $guest_list = []; $date_list = [];

			try {
                $guest = CBooking::getGuestList($merchant_id);
                $guest_list = $guest;
                $guest = CommonUtility::ArrayToLabelValue($guest);            
            } catch (Exception $e) {                
            }

			try {
			    $date_list = CBooking::getDateList($merchant_id);
			} catch (Exception $e) {                
			}

			$default_date = date('Y-m-d');
			$day_week_default = date("w",strtotime($default_date));            
            $day_week_today = date("w");
			
			if(!array_key_exists($default_date,(array)$date_list) && is_array($date_list) && count($date_list)>=1 ){                    
				$default_date = array_keys($date_list)[0];
				$day_week_default = date("w",strtotime($default_date));            
				$day_week_today = date("w");
			}
			$date_list = CommonUtility::ArrayToLabelValue($date_list); 

			$atts = OptionsTools::find(['booking_time_format']);            
            $booking_time_format = isset($atts['booking_time_format'])? ($atts['booking_time_format']==1?24:12) :12;            

			try {
                $time_slot = CBooking::getTimeSlot($day_week_default,$day_week_today,$merchant_id,'publish',$booking_time_format);				
                $all_time_slot = [];
                foreach ($time_slot as $items) {
                    $all_time_slot = array_merge($items,$all_time_slot );
                }
            } catch (Exception $e) {
                $time_slot = [];
                $all_time_slot = [];
            }

			$not_available_time = [];            
            $guest_count = 0;                                    
            if(is_array($guest_list) && count($guest_list)>=1){
                $guest_count = min(array_keys($guest_list));
            }            

			try {                
                $not_available_time = CBooking::getNotAvailableTime($merchant_id,$default_date,$guest_count , null);                
            } catch (Exception $e) {                
            }

			$options = OptionsTools::find(['booking_reservation_terms',
			  'booking_allowed_choose_table',
			  'booking_reservation_custom_message',
			  'booking_enabled_capcha'
		    ],$merchant_id);
            $tc = isset($options['booking_reservation_terms'])?t($options['booking_reservation_terms']):'';             
            $allowed_choose_table = isset($options['booking_allowed_choose_table'])?$options['booking_allowed_choose_table']:false;             
            $allowed_choose_table = $allowed_choose_table==1?true:false;
			$custom_message = $options['booking_reservation_custom_message'] ?? '';
			$enabled_capcha = $options['booking_enabled_capcha'] ?? false;
			$enabled_capcha = $enabled_capcha==1?true:false;

            $room_list = [];
            if($allowed_choose_table){
                $room_list = CommonUtility::getDataToDropDown("{{table_room}}","room_id","room_name","WHERE merchant_id=".q($merchant_id)." ","order by room_name asc");                
                $room_list = CommonUtility::ArrayToLabelValue($room_list);   
            }       
						
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'guest_list'=>$guest,
				'date_list'=>$date_list,
				'time_slot'=>$time_slot,
				'all_time_slot'=>$all_time_slot,
				'all_time_slot'=>$all_time_slot,
                'default_date'=>$default_date,
				'tc'=>$tc,
				'allowed_choose_table'=>$allowed_choose_table,
				'room_list'=>$room_list,
                'not_available_time'=>$not_available_time,
                'default_guest'=>$guest_count,
				'custom_message'=>$custom_message,
				'enabled_capcha'=>$enabled_capcha
			];
		} catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
	}
   
	public function actionbookingsummary()
	{
		try {
			$summary = CBooking::customerSummary( Yii::app()->user->id,0,date("Y-m-d"));			
			$cancel_reason = CBooking::getCancelationReason();
            $this->code = 1;
            $this->msg  = "Ok";
            $this->details = [
                'summary'=>$summary,
				'cancel_reason'=>$cancel_reason
            ];
		} catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
	}

	public function actionBookingList()
	{
		try {
						
			$modify_status = CBooking::modifyStatus();			

			$limit = 20;
			$page = intval(Yii::app()->request->getQuery('page', 1)); 
			$page_raw =  intval(Yii::app()->request->getQuery('page', 1)); 			
			$status = Yii::app()->request->getQuery('status', 'all');
			$merchant_id = Yii::app()->request->getQuery('merchant_id', 0);
			$q = Yii::app()->request->getQuery('q', null);
			$client_id = Yii::app()->user->id;			
			
			if($page>0){
				$page = $page-1;
			}		

			$criteria=new CDbCriteria();
		    $criteria->alias = "a";
			$criteria->select = "			
			a.reservation_id,
			a.reservation_uuid,
			a.reservation_date,
			a.reservation_time,
			a.merchant_id,
			a.status,
			a.guest_number,
			m.restaurant_name as restaurant_name,			
			m.logo as photo,
			m.path as path,
			concat(client.first_name,' ',client.last_name) as full_name,			
			client.email_address,
			client.email_address,client.contact_phone,
			a.special_request   
			";

			$criteria->join = "
			LEFT JOIN {{merchant}} m on a.merchant_id = m.merchant_id 
			LEFT JOIN {{client}} client on a.client_id = client.client_id 
			";
			
			if($merchant_id>0){ 
				$criteria->addCondition('a.client_id=:client_id AND merchant_id=:merchant_id');
                $criteria->params = array(
                    ':client_id'=>intval($client_id),
                    ':merchant_id'=>$merchant_id
                );
			} else {
				$criteria->addCondition('a.client_id=:client_id');
                $criteria->params = array(':client_id'=>intval($client_id));
			}

			if(!empty($q)){
                $criteria->addSearchCondition("reservation_id",$q);
            }

			if($status!="all"){
                $criteria->addInCondition("a.status",[$status]);
            }

			$criteria->order = "a.reservation_id DESC";	
			$count = AR_table_reservation::model()->count($criteria);
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

			if(!$models = AR_table_reservation::model()->findAll($criteria)){
				$this->msg = t(HELPER_NO_RESULTS);
				$this->responseJson();
			}

			$data = [];
			foreach ($models as $items) {	
				$logo_url = !empty($items->photo) ? CMedia::getImage($items->photo,$items->path) :'';		
				$guest_number = Yii::t('front', '{n} person|{n} persons', $items->guest_number );			
				$data[] = [
					'reservation_id'=>$items->reservation_id,
					'reservation_uuid'=>$items->reservation_uuid,					
					'restaurant_name'=>CommonUtility::safeDecode($items->restaurant_name),
					'logo_url'=>$logo_url,
					'booking_id'=>t("Booking ID #{reservation_id}",[
                            '{reservation_id}'=>$items->reservation_id          
                    ]),
					'guest_number'=>$guest_number,
					'full_name'=>$items->full_name,
					'email_address'=>$items->email_address,
					'contact_phone'=>$items->contact_phone,
					'status'=>t($items->status),
					'status_raw'=>$items->status,
					'reservation_date'=>Date_Formatter::dateTime($items->reservation_date." ".$items->reservation_time),
					'special_request'=>$items->special_request,
					'can_modify'=>in_array($items->status,$modify_status)?true:false,
				];
			}
			
			$remaining = $count - ($page_raw * $limit);			
			$is_last_page = $remaining <= 0;

			$this->code = $page_raw == $page_count ? 3 : 1;
			$this->msg = "Ok";
			$this->details = [
				'is_last_page'=>$is_last_page,
				'page_raw'=>$page_raw,
				'page_count'=>$page_count,
				'data'=>$data
			];
		} catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
	}

	public function actionCancelReservation()
	{
		try {

			$id =  Yii::app()->request->getPost('id', null);
			$reason =  Yii::app()->request->getPost('reason', null);
			$accepted_status = ['pending'];

			if(!$id){
				$this->msg = t("Invalid reservation id");
				$this->responseJson();
			}

			$model = AR_table_reservation::model()->find("reservation_uuid=:reservation_uuid",[
                ":reservation_uuid"=>$id
            ]);
			if(!$model){
				$this->msg =  t('Reservation id not found');
				$this->responseJson();
			}

			if(!in_array($model->status,$accepted_status)){
				$this->msg = t("Reservation cancellation failed, status is not anymore pending");
				$this->responseJson();
			}

			$model->status = "cancelled";
            $model->cancellation_reason = $reason;
			if($model->save()){			
				$this->code = 1;
				$this->msg = t("Your reservation ID {reservation_id} has been cancelled",[
					'{reservation_id}'=>$model->reservation_id
				]);
				$this->details = [
					'data'=>CBooking::getBooking($id)
				];
			} else $this->msg = CommonUtility::parseModelErrorToString($model->getError());

		} catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
	}
	
	public function actionfetchbookingdetails()
	{
		try {
			
			$reservation_uuid =  Yii::app()->request->getQuery('reservation_uuid', null);
			if(!$reservation_uuid){
				$this->msg = t("Reservation id is missing");
				$this->responseJson();
			}

			$default_date = date('Y-m-d');

			$data_booking = CBooking::getBookingDetails($reservation_uuid);  
			$default_date = $data_booking['reservation_date_raw'];
            $reservation_id = $data_booking['reservation_id'];

			$merchant_id = $data_booking['merchant_id'];
            $merchant = CMerchants::get($merchant_id);

			$modify_status = CBooking::modifyStatus();
			$data_booking['restaurant_name'] = CommonUtility::safeDecode($merchant->restaurant_name);
			$data_booking['logo_url'] = CMedia::getImage($merchant->logo,$merchant->path);			
			$data_booking['can_modify'] = in_array($data_booking['status'],$modify_status)?true:false;
			$data_booking['reservation_date'] = $data_booking['reservation_date']." ".$data_booking['reservation_time'];			

			$day_week_default = date("w",strtotime($default_date));            
            $day_week_today = date("w");

			try {
                $guest = CBooking::getGuestList($merchant_id);
                $guest_list = $guest;
                $guest = CommonUtility::ArrayToLabelValue($guest);            
            } catch (Exception $e) {
                $guest = []; $guest_list = [];
            }

            $date_list = CBooking::getDateList($merchant_id);
            if(!empty($id) && strlen($id)>10){
                //
            } else {                
                if(!array_key_exists($default_date,$date_list)){                    
                    $default_date = array_keys($date_list)[0];
                    $day_week_default = date("w",strtotime($default_date));            
                    $day_week_today = date("w");
                }
            }

			$date_list = CommonUtility::ArrayToLabelValue($date_list); 
			$atts = OptionsTools::find(['booking_time_format']);            
            $booking_time_format = isset($atts['booking_time_format'])? ($atts['booking_time_format']==1?24:12) :12; 

			try {
                $time_slot = CBooking::getTimeSlot($day_week_default,$day_week_today,$merchant_id,'publish',$booking_time_format);
                $all_time_slot = [];
                foreach ($time_slot as $items) {
                    $all_time_slot = array_merge($items,$all_time_slot );
                }
            } catch (Exception $e) {
                $time_slot = [];
                $all_time_slot = [];
            }			

			$not_available_time = [];            
            $guest_count = 0;                                    
            if(is_array($guest_list) && count($guest_list)>=1){
                $guest_count = min(array_keys($guest_list));
            }
			
			try {                
                $not_available_time = CBooking::getNotAvailableTime($merchant_id,$default_date,$guest_count , $reservation_id);                
            } catch (Exception $e) {                
            }

			$options = OptionsTools::find(['booking_reservation_terms','booking_allowed_choose_table'],$merchant_id);
            $tc = isset($options['booking_reservation_terms'])?$options['booking_reservation_terms']:'';             
            $allowed_choose_table = isset($options['booking_allowed_choose_table'])?$options['booking_allowed_choose_table']:false;             
            $allowed_choose_table = $allowed_choose_table==1?true:false;

			$room_list = [];
            if($allowed_choose_table){
                $room_list = CommonUtility::getDataToDropDown("{{table_room}}","room_id","room_name","WHERE merchant_id=".q($merchant_id)." ","order by room_name asc");                
                $room_list = CommonUtility::ArrayToLabelValue($room_list);   
            }

			$table_list = [];
			try {                  
				$guest_number = $data_booking['guest_number_raw'] ?? 0;
				$reservation_date = $data_booking['reservation_date_raw'] ?? '';
				$reservation_time = $data_booking['reservation_time_raw'] ?? '';
				$table_list = CBooking::getAvailableTableList($guest_number,$reservation_date,$reservation_time,$merchant_id,$reservation_id,false);
			} catch (Exception $e) { 				
			}                

			$cancel_reason = CBooking::getCancelationReason();
			
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'guest_list'=>$guest,
				'date_list'=>$date_list,
				'time_slot'=>$time_slot,
				'all_time_slot'=>$all_time_slot,
				'default_date'=>$default_date,
				'tc'=>$tc,
				'allowed_choose_table'=>$allowed_choose_table,
				'room_list'=>$room_list,
				'table_list'=>$table_list,
				'not_available_time'=>$not_available_time,
				'default_guest'=>$guest_count,
				'data_booking'=>$data_booking,
				'details_link'=>Yii::app()->createAbsoluteUrl("/reservation/details",['id'=>$reservation_id]),
				'merchant_uuid'=>$merchant->merchant_uuid,
				'merchant_slug'=>$merchant->restaurant_slug,
				'cancel_reason'=>$cancel_reason
			];
		} catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
	}

	public function actionfetchtimeslot()
	{
		try {
			
			$merchant_uuid =  Yii::app()->request->getQuery('merchant_uuid', null);
			$reservation_date =  Yii::app()->request->getQuery('reservation_date', null);
			$guest =  Yii::app()->request->getQuery('guest', 0);
			
			if(!$merchant_uuid){
				$this->msg = t("Invalid merchant id");
				$this->responseJson();
			}

			$merchant = CMerchants::getByUUID($merchant_uuid);            			
            $merchant_id = $merchant->merchant_id;

			$options_merchant = OptionsTools::find(['merchant_timezone'],$merchant_id);				
		    $merchant_timezone = isset($options_merchant['merchant_timezone'])?$options_merchant['merchant_timezone']:'';		
            if(!empty($merchant_timezone)){
                Yii::app()->timezone = $merchant_timezone;
            }            

			$day_week_default = date("w",strtotime($reservation_date));            
            $day_week_today = date("w");

			$atts = OptionsTools::find(['booking_time_format']);            
            $booking_time_format = isset($atts['booking_time_format'])? ($atts['booking_time_format']==1?24:12) :12;

			try {
                $time_slot = CBooking::getTimeSlot($day_week_default,$day_week_today,$merchant_id,'publish',$booking_time_format);
                $all_time_slot = [];
                foreach ($time_slot as $items) {
                    $all_time_slot = array_merge($items,$all_time_slot );
                }
            } catch (Exception $e) {				
                $time_slot = [];
                $all_time_slot = [];
            }

			$reservation_id = '';
            if(!empty($id) && strlen($id)>10){
                $data_booking = CBooking::getBookingDetails($id);                            
                $reservation_id = $data_booking['reservation_id'];
            }            

			try {                
                $not_available_time = CBooking::getNotAvailableTime($merchant_id,$reservation_date,$guest,$reservation_id);                
            } catch (Exception $e) {
                $not_available_time = [];
            }

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'time_slot'=>$time_slot,   
               'all_time_slot'=>$all_time_slot,
			   'not_available_time'=>$not_available_time
			];
		} catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
	}
   
	public function actionreservetable()
	{
		try {			
			$merchant_uuid =  Yii::app()->request->getPost('merchant_uuid', null); 
			$guest =  Yii::app()->request->getPost('guest', 0); 
			$reservation_date =  Yii::app()->request->getPost('reservation_date', null); 
			$reservation_time =  Yii::app()->request->getPost('reservation_time', null); 
			$room_id =  Yii::app()->request->getPost('room_id', 0); 
			$table_id =  Yii::app()->request->getPost('table_id', 0); 
			$special_request =  Yii::app()->request->getPost('special_request', ''); 
			$recaptcha_response = Yii::app()->request->getPost('recaptcha_response', null); 

			if(!$merchant_uuid){
				$this->msg = t("Invalid merchant UUID");
				$this->responseJson();
			}

			$merchant = CMerchants::getByUUID($merchant_uuid);            
            $merchant_id = $merchant->merchant_id;

			$options_merchant = OptionsTools::find(['merchant_timezone'],$merchant_id);				
		    $merchant_timezone = isset($options_merchant['merchant_timezone'])?$options_merchant['merchant_timezone']:'';		
            if(!empty($merchant_timezone)){
                Yii::app()->timezone = $merchant_timezone;
            }

			$options = OptionsTools::find(['captcha_secret']);
			$captcha_secret = isset($options['captcha_secret'])?$options['captcha_secret']:''; 

			$options = OptionsTools::find([
                'booking_enabled','booking_enabled_capcha','booking_allowed_choose_table'
            ],$merchant_id);            
            $booking_enabled_capcha = isset($options['booking_enabled_capcha'])?$options['booking_enabled_capcha']:false;
            $booking_enabled_capcha = $booking_enabled_capcha==1?true:false;
            $allowed_choose_table = isset($options['booking_allowed_choose_table'])?$options['booking_allowed_choose_table']:false;             
            $allowed_choose_table = $allowed_choose_table==1?true:false;

			if($allowed_choose_table){                
                if(empty($room_id)){
                    $this->msg = t("Please select room");
                    $this->responseJson();
                }
                if(empty($table_id)){
                    $this->msg = t("Please select table");
                    $this->responseJson();
                }
            }            
			

			$client_id = Yii::app()->user->id;			
			$model = new AR_table_reservation();
			
			$model->is_update_frontend = true;
            $model->client_id = $client_id;
            $model->merchant_id = $merchant_id;
            $model->reservation_date = $reservation_date;
            $model->reservation_time = $reservation_time;
            $model->guest_number = intval($guest);
            $model->special_request = $special_request;

			if($allowed_choose_table){
				$model->room_id = intval($room_id);
				$model->table_id = intval($table_id);
			}

			if($booking_enabled_capcha){                
                $model->capcha = true;
                $model->recaptcha_response = $recaptcha_response;
                $model->captcha_secret = $captcha_secret;
            }
			
			if($model->save()){
				$full_time = "$reservation_date $reservation_time";	                
                $full_time_pretty = t("{date} at {time}",[
                    '{date}'=>Date_Formatter::dateTime($full_time,"EEEE, MMMM d, y",true),
                    '{time}'=>Date_Formatter::dateTime($full_time,"h:mm:ss a",true),
                ]);
                $guest = Yii::t('front', '{n} person|{n} persons', $guest );
				$this->code = 1;
                $this->msg = "OK";
                $this->details = [
                    'reservation_id'=>$model->reservation_id,
                    'reservation_uuid'=>$model->reservation_uuid,
                    'reservation'=>t("Reservation ID: {reservation_id}",[
                        '{reservation_id}'=>$model->reservation_id
                    ]),
                    'full_time'=>$full_time_pretty,
				    'guest'=>$guest,
                    'track_reservation_link'=>Yii::app()->createAbsoluteUrl("/reservation/details",['id'=>$model->reservation_uuid])
                ];
			} else $this->msg =  CommonUtility::parseModelErrorToString($model->getErrors()); 
		} catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
	}

	public function actionupdatebooking()
	{
		try {
			
			$reservation_uuid =  Yii::app()->request->getPost('reservation_uuid', null);
			$first_name =  Yii::app()->request->getPost('first_name', '');
			$last_name =  Yii::app()->request->getPost('last_name', '');
			$email_address =  Yii::app()->request->getPost('email_address', '');
			$mobile_number =  Yii::app()->request->getPost('mobile_number', '');
			$mobile_prefix =  Yii::app()->request->getPost('mobile_prefix', '');
			$mobile_prefix =  Yii::app()->request->getPost('mobile_prefix', '');
			$guest =  Yii::app()->request->getPost('guest', 0);
			$reservation_date =  Yii::app()->request->getPost('reservation_date', '');
			$reservation_time =  Yii::app()->request->getPost('reservation_time', '');
			$room_id =  Yii::app()->request->getPost('room_id', 0);
			$table_id =  Yii::app()->request->getPost('table_id', 0);
			$special_request =  Yii::app()->request->getPost('special_request', '');
			$recaptcha_response =  Yii::app()->request->getPost('recaptcha_response', '');			
						
			$model = CBooking::get($reservation_uuid);		

			$options = OptionsTools::find(['captcha_secret']);
			$captcha_secret = $options['captcha_secret'] ?? '';

			$options = OptionsTools::find([
                'booking_enabled','booking_enabled_capcha','booking_allowed_choose_table'
            ],$model->merchant_id);            
            $booking_enabled_capcha = $options['booking_enabled_capcha'] ?? false;
            $booking_enabled_capcha = $booking_enabled_capcha==1?true:false;
            $allowed_choose_table = $options['booking_allowed_choose_table'] ?? false;
            $allowed_choose_table = $allowed_choose_table==1?true:false;

			$model->is_update_frontend = true;			
			$model->reservation_date = $reservation_date;
			$model->reservation_time = $reservation_time;
			$model->guest_number = intval($guest);
			$model->special_request = trim($special_request);

			if($allowed_choose_table){
				$model->room_id = intval($room_id);
			    $model->table_id = intval($table_id);
			}						

			if($booking_enabled_capcha){                
                $model->capcha = true;
                $model->recaptcha_response = $recaptcha_response;
                $model->captcha_secret = $captcha_secret;
            }

			if($model->save()){
				$this->code = 1;
				$this->msg = t("Your reservation successfully updated.");
			} else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());

		} catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
	}

	public function actionfetchTableList()
	{
		try {
			$merchant_uuid =  Yii::app()->request->getQuery('merchant_uuid', null);
			$reservation_date =  Yii::app()->request->getQuery('reservation_date', null);
			$reservation_time =  Yii::app()->request->getQuery('reservation_time', null);
			$guest_number =  Yii::app()->request->getQuery('guest', 0);

			if(!$merchant_uuid){
				$this->msg = t("Invalid merchant UUID");
				$this->responseJson();
			}

			if(!$reservation_date){
				$this->msg = t("Invalid reservation date");
				$this->responseJson();
			}
			if(!$reservation_time){
				$this->msg = t("Invalid reservation time");
				$this->responseJson();
			}

			$merchant = CMerchants::getByUUID($merchant_uuid);            
            $merchant_id = $merchant->merchant_id;

			$table_list = [];
			$table_list = CBooking::getAvailableTableList($guest_number,$reservation_date,$reservation_time,$merchant_id,null,false);
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'table_list'=>$table_list
			];
		} catch (Exception $e) {
            $this->msg = t($e->getMessage());
        }
        $this->responseJson();
	}

}
// end class