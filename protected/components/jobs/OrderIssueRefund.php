<?php
class OrderIssueRefund 
{
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }
    
    public function execute()
    {        
        try { 
                        
            $order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:null;
            $amount = isset($this->data['amount'])?$this->data['amount']:null;
            $refund_type = isset($this->data['refund_type'])?$this->data['refund_type']:null;
            $payment_code = isset($this->data['payment_code'])?$this->data['payment_code']:null;
            $order = COrders::get($order_uuid);	
                      
            die();
        } catch (Exception $e) {                                            
            throw new Exception($e->getMessage());        
        }                
    }
}
// end class