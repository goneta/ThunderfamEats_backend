<?php
class Payment_success extends CAction
{
    public $_controller;
    public $_id;
    public $data;

    public function __construct($controller,$id)
    {       
       Yii::app()->setImport(array(			
            'application.modules.flutterwave.components.*',
       ));
       $this->_controller=$controller;
       $this->_id=$id;
    }

    public function run()
    {        
        try {
                        
            $order_uuid = Yii::app()->input->get('order_uuid');            

		} catch (Exception $e) {
			$this->_controller->msg = t($e->getMessage());            
		}					
    }
  
}
// end class