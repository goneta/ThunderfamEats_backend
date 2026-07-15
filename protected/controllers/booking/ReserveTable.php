<?php
class ReserveTable extends CAction
{
    public $_controller;
    public $_id;
    public $data;

    public function __construct($controller,$id)
    {
       $this->_controller=$controller;
       $this->_id=$id;
    }

    public function run()
    {        
        try {

            $client_id = Yii::app()->user->id;

            $merchant_uuid = Yii::app()->input->post('merchant_uuid');      
            $id = Yii::app()->input->post('id');               
            $guest = Yii::app()->input->post('guest');
            $reservation_date = Yii::app()->input->post('reservation_date');
            $reservation_time = Yii::app()->input->post('reservation_time');
            $first_name = Yii::app()->input->post('first_name');
            $last_name = Yii::app()->input->post('last_name');
            $email_address = Yii::app()->input->post('email_address');
            $mobile_prefix = Yii::app()->input->post('mobile_prefix');
            $mobile_number = Yii::app()->input->post('mobile_number');
            $room_uuid = Yii::app()->input->post('room_uuid');  
            $table_uuid = Yii::app()->input->post('table_uuid');  
            $special_request = Yii::app()->input->post('special_request');  
            $recaptcha_response = Yii::app()->input->post('recaptcha_response');  
            
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
            

            $booking_tag = Yii::app()->params['booking_tag'];
            
            $model = null;
                        
            if($allowed_choose_table){                
                if(empty($room_uuid)){
                    $this->_controller->msg = t("Please select room");
                    $this->_controller->responseJson();
                }
                if(empty($table_uuid)){
                    $this->_controller->msg = t("Please select table");
                    $this->_controller->responseJson();
                }
            }            

            if(!empty($id) && strlen($id)>10){
                $model = CBooking::get($id);                                  
            } else {                                                
                $model = new AR_table_reservation();
            }

            if(!empty($id) && strlen($id)>10){
                $model->reservation_id_set = $model->reservation_id;
            } else $model->reservation_id_set = 0;
                                  
            $table_id = 0;
            if($allowed_choose_table){
                try {
                    $model_table = CBooking::getTable($table_uuid);                    
                    $table_id = $model_table->table_id;                    
                    $model->table_id_selected = $table_id;
                    $model->room_id = $model_table->room_id;
                } catch (Exception $e) {
                    $this->_controller->msg = t("Table information not found");
                    $this->_controller->responseJson();
                }        
            }            
            $model->is_update_frontend = true;
            $model->client_id = $client_id;
            $model->merchant_id = $merchant_id;
            $model->reservation_date = $reservation_date;
            $model->reservation_time = $reservation_time;
            $model->guest_number = intval($guest);
            $model->special_request = $special_request;

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

                $this->_controller->code = 1;
                $this->_controller->msg = "OK";
                $this->_controller->details = [
                    'reservation_id'=>$model->reservation_id,
                    'reservation_uuid'=>$model->reservation_uuid,
                    'reservation'=>t("Reservation ID: {reservation_id}",[
                        '{reservation_id}'=>$model->reservation_id
                    ]),
                    'full_time'=>$full_time_pretty,
				    'guest'=>$guest,
                    'track_reservation_link'=>Yii::app()->createAbsoluteUrl("/reservation/details",['id'=>$model->reservation_uuid])
                ];
            } else $this->_controller->msg = CommonUtility::parseModelErrorToString($model->getErrors());        

		} catch (Exception $e) {            
			$this->_controller->msg = t($e->getMessage());							
		}			
		$this->_controller->responseJson();
    }
}
// end class