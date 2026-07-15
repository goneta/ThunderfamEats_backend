<?php
class MercadopagoGetcard extends CAction
{
    public $_controller;
    public $_id;
    public $data;

    public function __construct($controller,$id)
    {
       require_once 'mercadopago/vendor/autoload.php';
       $this->_controller=$controller;
       $this->_id=$id;
    }

    public function run()
    {        
        try {                        
            $this->data = $this->_controller->data;
            $payment_uuid = isset($this->data['payment_uuid'])?$this->data['payment_uuid']:'';			

            $data = AR_client_payment_method::model()->find('payment_uuid=:payment_uuid',
			array(':payment_uuid'=>$payment_uuid));	  

            if($data){
                $payment_method = CPayments::getPaymentMethodMeta($payment_uuid, Yii::app()->user->id );
                $this->_controller->code = 1;
                $this->_controller->msg = "OK";
                $this->_controller->details = array(
                  'card_number'=>$data->attr2,			     
                  'card_id'=>isset($payment_method['card_id'])?$payment_method['card_id']:'',
                  'is_live'=>isset($payment_method['is_live'])?$payment_method['is_live']:'',
                );                
             } else $this->msg = t("Payment id not fond");
             
		} catch (Exception $e) {
			$this->_controller->msg[] = t($e->getMessage());							
		}			
		$this->_controller->responseJson();
    }
  
}
// end class