<?php
class GetTimeslot extends CAction
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
            
            $merchant_uuid = Yii::app()->input->post('merchant_uuid');
            $id = Yii::app()->input->post('id');

            $reservation_date = Yii::app()->input->post('reservation_date');
            $guest_count = Yii::app()->input->post('guest');

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

            // try {
            //     $guest_list = CBooking::getGuestList($merchant_id);
            //     $guest_list = CommonUtility::ArrayToLabelValue($guest_list);            
            // } catch (Exception $e) {
            //     $guest_list = [];
            // }

            $reservation_id = '';
            if(!empty($id) && strlen($id)>10){
                $data_booking = CBooking::getBookingDetails($id);                            
                $reservation_id = $data_booking['reservation_id'];
            }            

            try {                
                $not_available_time = CBooking::getNotAvailableTime($merchant_id,$reservation_date,$guest_count,$reservation_id);                
            } catch (Exception $e) {
                $not_available_time = [];
            }
                        
            $this->_controller->code = 1;
		    $this->_controller->msg = "OK";
            $this->_controller->details = array(		                     
               'time_slot'=>$time_slot,   
               'all_time_slot'=>$all_time_slot,
               //'guest_list'=>$guest_list, 
               'not_available_time'=>$not_available_time
            );    
		} catch (Exception $e) {            
			$this->_controller->msg[] = t($e->getMessage());							
		}			
		$this->_controller->responseJson();
    }
}
// end class