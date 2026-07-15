<?php
class BookingDetails extends CAction
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
            
            $id = Yii::app()->input->post("id");  

            $merchant = [];  
            $cancel_reservation_stats = ['cancelled','denied','no_show','confirmed','finished'];        
            $cancel_reservation_stats2 = ['cancelled','denied','no_show'];        
            $pending_reservation_stats = ['pending','waitlist'];        
            $confirm_reservation_stats = ['confirmed'];        
            $completed_reservation_stats = ['finished'];        

            $data = CBooking::getBookingDetails($id);            
                        
            try {
                $merchant_data = CMerchants::get($data['merchant_id']);
                $merchant['logo'] = CMedia::getImage($merchant_data['logo'],$merchant_data['path'],'',CommonUtility::getPlaceholderPhoto('item'));
                $merchant['menu_url'] = Yii::app()->createAbsoluteUrl("/".$merchant_data['restaurant_slug']);
                $merchant['restaurant_name'] = $merchant_data['restaurant_name'];
                $merchant['address'] = $merchant_data['address'];
            } catch (Exception $e) {            
            }            
            
            $this->_controller->code = 1;
            $this->_controller->msg = "OK";
            $this->_controller->details = [
                'data'=>$data,
                'merchant'=>$merchant,
                'cancel_link'=>Yii::app()->createAbsoluteUrl("/reservation/cancel",['id'=>$id]),
                'update_link'=>Yii::app()->createAbsoluteUrl("/reservation/update",['id'=>$id]),
                'cancel_reservation_stats'=>$cancel_reservation_stats,
                'cancel_reservation_stats2'=>$cancel_reservation_stats2,
                'pending_reservation_stats'=>$pending_reservation_stats,
                'confirm_reservation_stats'=>$confirm_reservation_stats,
                'completed_reservation_stats'=>$completed_reservation_stats,
            ];           
		} catch (Exception $e) {            
			$this->_controller->msg[] = t($e->getMessage());							
		}			
		$this->_controller->responseJson();
    }
}
// end class