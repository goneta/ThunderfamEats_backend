<?php
require 'stripe/vendor/autoload.php';
class StripeCreateIntent extends CAction
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
            
            $customer_id = isset($this->data['customer_id'])?$this->data['customer_id']:'';		
			$merchant_id = isset($this->data['merchant_id'])?$this->data['merchant_id']:0;
			$payment_code = isset($this->data['payment_code'])?$this->data['payment_code']:'';
			$merchant_type = isset($this->data['merchant_type'])?$this->data['merchant_type']:2;
			
            $merchant_id = intval($merchant_id);

			$credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,$merchant_type);
			$credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';
			$is_live = isset($credentials['is_live'])?intval($credentials['is_live']):0;

            \Stripe\Stripe::setApiKey( isset($credentials['attr1'])?$credentials['attr1']:'' );
			
			$intent = \Stripe\SetupIntent::create([
			    'customer' => $customer_id
			]);

            $client_secret = $intent->client_secret;

            $this->_controller->code = 1;
		    $this->_controller->msg = "OK";
		    $this->_controller->details = array(
		      'client_secret'=>$client_secret,		      
		    );		    

        } catch (Exception $e) {
            $this->_controller->msg = t($e->getMessage());							
        }			
        $this->_controller->responseJson();	
    }
}