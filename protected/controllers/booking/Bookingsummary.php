<?php
class Bookingsummary extends CAction
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
            
            try {				
                $summary = CBooking::customerSummary( Yii::app()->user->id,0,date("Y-m-d"));
            } catch (Exception $e) {
                $summary  = [];
            }	

            $this->_controller->code = 1;
            $this->_controller->msg  = "Ok";
            $this->_controller->details = [
                'summary'=>$summary
            ];

		} catch (Exception $e) {            
			$this->_controller->msg = t($e->getMessage());							
		}			
		$this->_controller->responseJson();
    }
}
// end class