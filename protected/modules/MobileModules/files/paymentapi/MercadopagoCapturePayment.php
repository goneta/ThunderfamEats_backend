<?php
class MercadopagoCapturePayment extends CAction
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
            
            $merchant_id = isset($this->data['merchant_id'])?$this->data['merchant_id']:0;		
			$payment_code = isset($this->data['payment_code'])?$this->data['payment_code']:'';		
			$merchant_type = isset($this->data['merchant_type'])?$this->data['merchant_type']:'';
			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';
			$cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
			
			$payment_uuid = isset($this->data['payment_uuid'])?$this->data['payment_uuid']:'';
			$card_token = isset($this->data['card_token'])?$this->data['card_token']:'';			
			
			$credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,$merchant_type);
			$credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';
			$is_live = isset($credentials['is_live'])?intval($credentials['is_live']):0;
            
            $data = AR_ordernew::model()->find('order_uuid=:order_uuid',array(':order_uuid'=>$order_uuid));

            if($data){
                $payment_method = CPayments::getPaymentMethodMeta($payment_uuid, Yii::app()->user->id );
				$customer_id = isset($payment_method['customer_id'])?$payment_method['customer_id']:'';
                $acess_token = isset($credentials['attr2'])?trim($credentials['attr2']):'';
                MercadoPago\SDK::setAccessToken($acess_token);				

                $payment = new MercadoPago\Payment();
                $total = floatval(Price_Formatter::convertToRaw($data->total));

                $payment->transaction_amount = $total;
				$payment->token = $card_token;
				$payment->installments = 1;
				$payment->payer = array(
				    "type" => "customer",
				    "id" => $customer_id
				);				
				$payment->save();			
                
                if($payment->status_detail=="accredited" || $payment->status_detail=="pending_contingency"
				|| $payment->status_detail=="pending_review_manual"  ){
                    
                    $transaction_id = $payment->id;
					$payment_status = CPayments::paidStatus();
					if($payment->status!="approved"){
						$payment_status = CPayments::umpaidStatus();
					}
                    $data->scenario = "new_order";
		    	    $data->status = COrders::newOrderStatus();
		    	    $data->payment_status = CPayments::paidStatus();
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
				    $model->status = $payment_status;
				    $model->reason = '';
				    $model->payment_uuid = $payment_uuid;
                    if($model->save()){

                        /*INSERT NOTES FOR PAYMENT*/
						$params = array(  
                            array('transaction_id'=>$model->transaction_id,'order_id'=>$data->order_id, 
                            'meta_name'=>'status', 'meta_value'=>$payment->status ),
                                                   
                            array('transaction_id'=>$model->transaction_id,'order_id'=>$data->order_id, 
                            'meta_name'=>'status_detail', 'meta_value'=>$payment->status_detail ),
                             
                         );
                         $builder=Yii::app()->db->schema->commandBuilder;
                         $command=$builder->createMultipleInsertCommand('{{ordernew_trans_meta}}',$params);
                         $command->execute();  
                             
                         $this->_controller->code = 1;
                         if($payment->status_detail=="accredited"){
                            $this->_controller->msg = t("Payment successful. please wait while we redirect you.");
                         } else if ($payment->status_detail=="pending_contingency") {
                            $this->_controller->msg = t("We are processing your payment. please wait while we redirect you.");
                         } else if ($payment->status_detail=="pending_review_manual") {
                            $this->_controller->msg = t("We are processing your payment. please wait while we redirect you.");
                         } else {
                            $this->_controller->msg = t("Payment successful. please wait while we redirect you.");
                         }
                     
                        $this->_controller->code = 1;
                        $this->_controller->msg = t("Payment successful. please wait while we redirect you.");
                        $this->_controller->details = array(  					  
                            'order_uuid'=>$data->order_uuid
                        );
                    } else $this->_controller->msg = CommonUtility::parseError( $model->getErrors() );
                } else {
                    if(!empty($payment->status_detail)){
                        $this->_controller->msg[] = CMercadopagoError::get($payment->status_detail);
                     } else {
                         if(isset($payment->error->causes)){
                             foreach ($payment->error->causes as $items) {
                                $this->_controller->msg[] = $items->description;
                             }
                         } else $this->_controller->msg[] = t("An error has occured." . json_encode($payment->error));
                     }
                }
            } else $this->_controller->msg[] = t("Order id not found");

		} catch (Exception $e) {
			$this->_controller->msg[] = t($e->getMessage());							
		}			
		$this->_controller->responseJson();
    }
  
}
// end class