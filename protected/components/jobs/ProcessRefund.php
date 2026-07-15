<?php
class ProcessRefund 
{
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }
    
    public function execute()
    {        
        try { 

            $logs = '';            
            $order_uuid  = isset($this->data['order_uuid'])?$this->data['order_uuid']:null;
            $transaction_id  = isset($this->data['transaction_id'])?$this->data['transaction_id']:null;
            $language = isset($this->data['language'])?$this->data['language']:null;
            if($language){
                Yii::app()->language = $language;
            } 
            
            $order = COrders::get($order_uuid);
		    $transaction = AR_ordernew_transaction::model()->find("transaction_id=:transaction_id 
		    AND order_id=:order_id AND status=:status",array(
		      ':transaction_id'=>intval($transaction_id),
		      ':order_id'=>intval($order->order_id),
		      ':status'=>'unpaid'
		    ));		
		   
		    $payment =  AR_ordernew_transaction::model()->find("order_id=:order_id
		     AND transaction_name=:transaction_name AND status=:status",array(		     
		      ':order_id'=>intval($order->order_id),
		      ':transaction_name'=>"payment",
		      ':status'=>'paid'
		    ));				
		    
		    
		    if(!$transaction){
		    	$logs = "Transaction not found";                
                return true;
		    }
		    if(!$payment){
		    	$logs = "Payment not found";                
                return true;
		    }

            $merchant = AR_merchant::model()->findByPk( $order->merchant_id );
		   	$merchant_type = $merchant?$merchant->merchant_type:'';		   	  
            $payment_code = $transaction->payment_code;

            if($payment_code=="digital_wallet"){
                $credentials =[];
             } else {
                $credentials = CPayments::getPaymentCredentials($order->merchant_id,$payment->payment_code,$merchant_type);		   	  
                $credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';                
             }		   	   
                         
             try {                
                
                
                if($order->payment_code!="digital_wallet"){                    
                    $refund_response = Yii::app()->getModule($payment_code)->refund($credentials,$transaction,$payment);
                    $transaction->payment_reference = $refund_response['id'];
                } 
                $transaction->order_uuid = $order_uuid;                                
                $transaction->status = "paid";
                $transaction->payment_uuid = $payment->payment_uuid;
                $transaction->save();      
                
                /*SEND REFUND NOTIFICATIONS*/
                $template_name = $transaction->transaction_name=="partial_refund"?'partial_refund_template_id':'refund_template_id';		 
                if($template_id = AR_admin_meta::getValue($template_name)){
                   $template_id = $template_id['meta_value'];		    		
                   $refund_data = array(
                    'refund_amount'=>Price_Formatter::formatNumber($transaction->trans_amount),
                    'refund_amount_no_sign'=>Price_Formatter::formatNumberNoSymbol($transaction->trans_amount),                     
                  );                       
                  try {			
                        $jobs = 'RunTemplate';
                        $jobInstance = new $jobs([
                            'language'=>Yii::app()->language,
                            'template_id'=>$template_id,
                            'order_uuid'=>$order_uuid,
                            'refund_data'=>$refund_data
                        ]);
                        $jobInstance->execute();	
                   } catch (Exception $e) { }                   
                }                
             } catch (Exception $e) { 
                $logs = $e->getMessage();          
                $transaction->status = "failed";
                $transaction->reason = $e->getMessage();
                $transaction->save();	   	        
             }            
        } catch (Exception $e) {                                            
            $logs = $e->getMessage();
        }        
        //Yii::log( $logs, CLogger::LEVEL_ERROR);
    }
}
// end class