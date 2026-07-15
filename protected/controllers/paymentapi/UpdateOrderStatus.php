<?php
class UpdateOrderStatus extends CAction
{
    public $_controller;
    public $_id;
    public $data;

    public function __construct($controller,$id)
    {       
    
        Yii::app()->setImport(array(			
            'application.modules.revolut.*',
			'application.modules.revolut.components.*',
		));

       $this->_controller=$controller;
       $this->_id=$id;
    }

    public function run()
    {        
        try {
           
            $this->data = $this->_controller->data;                        
            $order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';
			$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';            
            $data = AR_ordernew::model()->find('order_uuid=:order_uuid',array(':order_uuid'=>$order_uuid));
            if($data){
                
                $model_meta = AR_ordernew_meta::model()->find("order_id=:order_id AND meta_name=:meta_name",[
                    ':order_id'=>$data->order_id,
                    ':meta_name'=>"revolut_order_id"
                ]);

                if($model_meta){
                    $transaction_id = $model_meta->meta_value;

                    $data->scenario = "new_order";
                    $data->status = COrders::newOrderStatus();
                    $data->payment_status = CPayments::umpaidStatus();
                    $data->cart_uuid = $cart_uuid;
                    $data->save();

                    $model = new AR_ordernew_transaction;
                    $model->order_id = $data->order_id;
                    $model->merchant_id = $data->merchant_id;
                    $model->client_id = $data->client_id;
                    $model->payment_code = $data->payment_code;
                    $model->trans_amount = $data->total;
                    $model->currency_code = $data->use_currency_code;				
                    $model->payment_reference = $transaction_id;
                    $model->status = CPayments::umpaidStatus();
                    $model->reason = '';                    
                    $model->save();		

                    $this->_controller->code = 1;
                    $this->_controller->msg = t("Payment successful. please wait while we redirect you.");

                    $this->_controller->details = array(  					  
                        'order_uuid'=>$data->order_uuid
                    );
                } else $this->_controller->msg = t("Payment Order ID not found");

            } else $this->_controller->msg = t("Order id not found");
          
		} catch (Exception $e) {
			$this->_controller->msg = t($e->getMessage());							
		}			
		$this->_controller->responseJson();
    }
  
}
// end class