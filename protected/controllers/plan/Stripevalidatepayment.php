<?php
require 'stripe/vendor/autoload.php';

class Stripevalidatepayment extends CAction
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
            
            $merchant_uuid = isset($this->data['merchant_uuid'])?$this->data['merchant_uuid']:'';
			$payment_code = isset($this->data['payment_code'])?$this->data['payment_code']:'';
			$package_uuid = isset($this->data['package_uuid'])?$this->data['package_uuid']:'';
			$payment_intent = isset($this->data['payment_intent'])?$this->data['payment_intent']:'';      

            $merchant = CMerchants::getByUUID($merchant_uuid);
			$plans = Cplans::getByUUID($package_uuid);			

            $credentials = CPayments::getPaymentCredentials(0,$payment_code,0);			
			$credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';
			$is_live = isset($credentials['is_live'])?intval($credentials['is_live']):0;		

            $stripe = new \Stripe\StripeClient(isset($credentials['attr1'])?$credentials['attr1']:'');
			$resp = $stripe->paymentIntents->retrieve($payment_intent,[]);            
            
            $message = '';

            switch ($resp->status) {
                case "succeeded":				
				    $merchant->scenario = "after_payment_validate";
					$merchant->status = 'active';
					$merchant->package_id = intval($plans->package_id);
					$merchant->save();			                    
					break;
                case "processing":
                    $message = t("Payment processing");
                    break;
                case "requires_payment_method":
                    $message = t("Payment failed. Please try another payment method.");
                    break;
            }

            $this->_controller->code = 1;
            $this->_controller->msg = "OK";
            $this->_controller->details	 = [
                'status'=>$resp->status,
                'message'=>$message
            ];

		} catch (Exception $e) {
			$this->_controller->msg[] = t($e->getMessage());							
		}			
		$this->_controller->responseJson();
    }
}