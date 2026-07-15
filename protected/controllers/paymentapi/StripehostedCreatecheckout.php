<?php
class StripehostedCreatecheckout extends CAction
{
    public $_controller;
    public $_id;
    public $data;

    public function __construct($controller,$id)
    {       
       Yii::app()->setImport(array(			
            'application.modules.stripehosted.components.*',
       ));
       $this->_controller=$controller;
       $this->_id=$id;

       require 'stripe/vendor/autoload.php';
    }

    public function run()
    {        
        try {

            $this->data = $this->_controller->data;               
            $cart_uuid = isset($this->data['cart_uuid'])?$this->data['cart_uuid']:'';
            $order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';		
            $absoluteURL = isset($this->data['absoluteURL'])?$this->data['absoluteURL']:'';            
                        
            $order = COrders::get($order_uuid);					
			$merchant_id = $order->merchant_id;
			$payment_code = $order->payment_code;
			
			$merchant = CMerchantListingV1::getMerchant( $merchant_id );			
			$credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,$merchant->merchant_type);						
            $credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';			            
            $secret = isset($credentials['attr1'])?trim($credentials['attr1']):'';		
        

            $total = floatval(Price_Formatter::convertToRaw($order->total));
            $payment_description = t("Payment to merchant [merchant]. Order#[order_id]",
			array('[merchant]'=>$merchant->restaurant_name,'[order_id]'=>$order->order_id ));

			$total = floatval(Price_Formatter::convertToRaw($order->total));
            $payment_description = t("Payment to merchant [merchant]. Order#[order_id]",
			array('[merchant]'=>$merchant->restaurant_name,'[order_id]'=>$order->order_id ));


            $customer = ACustomer::get($order->client_id);

            $stripe = new \Stripe\StripeClient($secret);

            $param_url = [
                'order_uuid'=>$order_uuid,
                'cart_uuid'=>$cart_uuid,
                'absoluteURL'=>$absoluteURL
            ];
            
            $result =$stripe->checkout->sessions->create([
				'success_url' => Yii::app()->createAbsoluteUrl($payment_code."/apimobile/verify?".http_build_query($param_url)),
				'cancel_url'=>  Yii::app()->createAbsoluteUrl($payment_code."/apimobile/cancelpayment?".http_build_query($param_url)),
				'line_items' => [
				  [
					'price_data'=>[
						'currency'=>$order->use_currency_code,
						'unit_amount'=>($total*100),
						'product_data'=>[
							'name'=>$payment_description
						]
					],
					'quantity' => 1,
				  ],
				],
				'mode' => 'payment',
				'client_reference_id'=>$order_uuid,
				'customer_email'=>$customer->email_address,
				'metadata'=>[
					'order_id'=>$order->order_id,
					'order_uuid'=>$order_uuid,
					'cart_uuid'=>$cart_uuid
				]
			]);
            
            $redirect_url = $result['url'];

            $this->_controller->code = 1;
            $this->_controller->msg = "ok";
            $this->_controller->details = [
                'redirect_url'=>$redirect_url,				
            ];                        
            
		} catch (Exception $e) {
			$this->_controller->msg[] = t($e->getMessage());							
		}			
		$this->_controller->responseJson();
    }
  
}
// end class