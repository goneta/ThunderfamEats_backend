<?php
class Getbookingdetails extends CAction
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

            $id = Yii::app()->input->post('id');
            $default_date = date('Y-m-d');
            $data_booking = [];
            $reservation_id = '';

            $data_booking = CBooking::getBookingDetails($id);            
            $default_date = $data_booking['reservation_date_raw'];
            $reservation_id = $data_booking['reservation_id'];

            $merchant_id = $data_booking['merchant_id'];
            $merchant = CMerchants::get($merchant_id);            

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
                $not_available_time = [];
            }

            $options = OptionsTools::find(['booking_reservation_terms','booking_allowed_choose_table'],$merchant_id);
            $tc = isset($options['booking_reservation_terms'])?$options['booking_reservation_terms']:'';             
            $allowed_choose_table = isset($options['booking_allowed_choose_table'])?$options['booking_allowed_choose_table']:false;             
            $allowed_choose_table = $allowed_choose_table==1?true:false;

            $room_list = [];
            if($allowed_choose_table){
                $room_list = CommonUtility::getDataToDropDown("{{table_room}}","room_uuid","room_name","WHERE merchant_id=".q($merchant_id)." ","order by room_name asc");                
                $room_list = CommonUtility::ArrayToLabelValue($room_list);   
            }            
            
            $this->_controller->code = 1;
		    $this->_controller->msg = "OK";
            $this->_controller->details = array(		      
               'guest_list'=>$guest,
               'date_list'=>$date_list,
               'time_slot'=>$time_slot,
               'all_time_slot'=>$all_time_slot,
               'default_date'=>$default_date,
               'tc'=>$tc,
               'allowed_choose_table'=>$allowed_choose_table,
               'room_list'=>$room_list,
               'not_available_time'=>$not_available_time,
               'default_guest'=>$guest_count,
               'data_booking'=>$data_booking,
               'details_link'=>Yii::app()->createAbsoluteUrl("/reservation/details",['id'=>$id]),
               'merchant_uuid'=>$merchant->merchant_uuid
            );			         
           
		} catch (Exception $e) {            
			$this->_controller->msg[] = t($e->getMessage());							
		}			
		$this->_controller->responseJson();
    }
}
// end class