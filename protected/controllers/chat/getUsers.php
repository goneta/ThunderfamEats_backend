<?php
class getUsers extends CAction
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
                        
            $this->data = $this->_controller->data;            
            $users = isset($this->data['users'])?$this->data['users']:null;
            $main_user_type = isset($this->data['main_user_type'])?$this->data['main_user_type']:null;
            
            $data = []; $data_user = []; $data_admin = []; $data_merchant = [];
            
            try {
               $data_user = ACustomer::getByUUID($users,$main_user_type);                           
            } catch (Exception $e) {	             
            }

            try {
                $data_admin = AdminTools::getByUUID($users);
            } catch (Exception $e) {	             
            }

            try {
                $data_merchant = CMerchants::getAllByUUID($users);                                
            } catch (Exception $e) {	             
            }            
                        
            $data = array_merge($data_user,$data_admin,$data_merchant);            
            
            $this->_controller->code = 1;
            $this->_controller->msg = "Ok";
            $this->_controller->details = $data;
            
		} catch (Exception $e) {			
            $this->_controller->msg = t($e->getMessage());	
		}					
        $this->_controller->responseJson();
    }
  
}
// end class