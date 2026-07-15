<?php
class searchChats extends CAction
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
                 
            $data = []; $data_customer = []; $data_merchant = []; $data_admin = [];
            $this->data = $this->_controller->data;                        
            $search = isset($this->data['search'])?$this->data['search']:'';
            $search_type = isset($this->data['search_type'])?$this->data['search_type']:'';            
                               
            if(in_array('customer',$search_type)){
                try {
                   $data_customer = ACustomer::SearchCustomer($search);
                } catch (Exception $e) {}
            }
            if(in_array('merchant',$search_type)){
                try {
                    $data_merchant = CMerchants::SearchMerchant($search);
                 } catch (Exception $e) {}
            }
            if(in_array('admin',$search_type)){
                 try {
                    $data_admin = AdminTools::SearchAdmin($search);
                 } catch (Exception $e) {}
            }

            $data = array_merge($data_customer,$data_merchant,$data_admin);            
            
            if(is_array($data) && count($data)>=1){
                $this->_controller->code = 1;
                $this->_controller->msg = "Ok";
                $this->_controller->details = $data;
            } else $this->_controller->msg = t(HELPER_NO_RESULTS);
            
		} catch (Exception $e) {			
            $this->_controller->msg = t($e->getMessage());	
		}					
        $this->_controller->responseJson();
    }
  
}
// end class