<?php
class SetBooking extends CAction
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
            $reservation_date = Yii::app()->input->post('reservation_date');
            $reservation_time = Yii::app()->input->post('reservation_time');
			$guest = intval(Yii::app()->input->post('guest'));
            $reservation_uuid = trim(Yii::app()->input->post('id'));
            $guest_number = $guest;

            $merchant = CMerchants::getByUUID($merchant_uuid);            
            $merchant_id = $merchant->merchant_id;

            $full_time = "$reservation_date $reservation_time";			
			$full_time = Date_Formatter::dateTime($full_time);

			$guest = Yii::t('front', '{n} person|{n} persons', $guest );

            $options = OptionsTools::find(['booking_reservation_terms','booking_allowed_choose_table'],$merchant_id);            
            $allowed_choose_table = isset($options['booking_allowed_choose_table'])?$options['booking_allowed_choose_table']:false;             
            $allowed_choose_table = $allowed_choose_table==1?true:false;

            $table_list = []; $room_uuid = ''; $table_uuid='';
            if($allowed_choose_table){

                $reservation_id = '';

                if(!empty($reservation_uuid) && strlen($reservation_uuid)>=10){
                    try {
                        $model_reservation = CBooking::get($reservation_uuid);                                            
                        $reservation_id = $model_reservation->reservation_id;
                        
                        try {
                            $model_room = CBooking::getRoomByID($model_reservation->room_id);                            
                            $room_uuid = $model_room->room_uuid;
                        } catch (Exception $e) {                             
                        }

                        try {
                            $model_table = CBooking::getTableByID($model_reservation->table_id);                            
                            $table_uuid = $model_table->table_uuid;
                        } catch (Exception $e) {                             
                        }

                    } catch (Exception $e) { 
                        //
                    }                
                }

                try {                                        
                    $table_list = CBooking::getAvailableTableList($guest_number,$reservation_date,$reservation_time,$merchant_id,$reservation_id);
                } catch (Exception $e) { 
                    //
                }                
            }

            $user_data  = array();
            if(!Yii::app()->user->isGuest){
                $contact_number_without_prefix = str_replace(Yii::app()->user->phone_prefix,"",Yii::app()->user->contact_number);
                $user_data = [
                    'first_name'=>Yii::app()->user->first_name,
                    'last_name'=>Yii::app()->user->last_name,
                    'email_address'=>Yii::app()->user->email_address,
                    'contact_number'=>Yii::app()->user->contact_number,
                    'contact_number_without_prefix'=>$contact_number_without_prefix,
                    'phone_prefix'=>Yii::app()->user->phone_prefix,
                ];
            }

			$this->_controller->code = 1;
			$this->_controller->msg = "OK";
			$this->_controller->details = [
				'full_time'=>$full_time,
				'guest'=>$guest,
                'table_list'=>$table_list,
                'room_uuid'=>$room_uuid,
                'table_uuid'=>$table_uuid,
                'user_data'=>$user_data
			];
          
		} catch (Exception $e) {            
			$this->_controller->msg[] = t($e->getMessage());							
		}			
		$this->_controller->responseJson();
    }
}
// end class