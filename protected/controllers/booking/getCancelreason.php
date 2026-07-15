<?php
class getCancelreason extends CAction
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
            
            $model = AR_table_reservation::model()->find("reservation_uuid=:reservation_uuid",[
                ":reservation_uuid"=>$id
            ]);
            if($model){

                $data = [];
                $model = AR_admin_meta::model()->findAll("meta_name=:meta_name",[
                    ':meta_name'=>'reason_cancel_booking'
                ]);
                if($model){
                    foreach ($model as $items) {
                        $data[] = $items->meta_value;
                    }
                }

                $this->_controller->code = 1;
                $this->_controller->msg = "OK";
                $this->_controller->details = [
                    'data'=>$data
                ];           
            } else $this->msg = t(Helper_not_found);
		} catch (Exception $e) {            
			$this->_controller->msg[] = t($e->getMessage());							
		}			
		$this->_controller->responseJson();
    }
}
// end class