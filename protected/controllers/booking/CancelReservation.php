<?php
class CancelReservation extends CAction
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

            $accepted_status = ['pending'];
            
            $id = Yii::app()->input->post("id");  
            $reason = Yii::app()->input->post("reason");              
            
            $model = AR_table_reservation::model()->find("reservation_uuid=:reservation_uuid",[
                ":reservation_uuid"=>$id
            ]);
            if($model){                              
                if(in_array($model->status,$accepted_status)){
                    $model->status = "cancelled";
                    $model->cancellation_reason = $reason;
                    if($model->save()){                    
                        $this->_controller->code = 1;
                        $this->_controller->msg = t("Your reservation ID {reservation_id} has been cancelled",[
                            '{reservation_id}'=>$model->reservation_id
                        ]);
                        $this->_controller->details = [                            
                            'redirect_url'=>Yii::app()->createAbsoluteUrl("/reservation/details",['id'=>$id]),
                        ];
                    } else $this->_controller->msg = CommonUtility::parseModelErrorToString($model->getError());
                } else {
                    $this->_controller->msg = t("Reservation cancellation failed, status is not anymore pending");
                }                
            } else $this->msg = t(Helper_not_found);
		} catch (Exception $e) {            
			$this->_controller->msg[] = t($e->getMessage());							
		}			
		$this->_controller->responseJson();
    }
}
// end class