<?php
class Vivawalletcheckout extends CAction
{
    public $_controller;
    public $_id;
    public $data;

    public function __construct($controller,$id)
    {       
        Yii::app()->setImport(array(			
            'application.modules.vivawallet.components.*',
       ));
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
            $place_data = isset($this->data['place_data'])?$this->data['place_data']:'';
			
            $credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,2);                        
            $credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';         			
            
            $is_live = isset($credentials['is_live'])?intval($credentials['is_live']):0;
			$client_id = isset($credentials['attr1'])?trim($credentials['attr1']):'';
            $secret = isset($credentials['attr2'])?trim($credentials['attr2']):'';
            $soure_code = isset($credentials['attr7'])?trim($credentials['attr7']):'';

            $data = COrders::get($order_uuid);
			$merchant = CMerchantListingV1::getMerchant($data->merchant_id);

			$total = floatval(Price_Formatter::convertToRaw($data->total));
            $payment_description = t("Payment to merchant [merchant]. Order#[order_id]",
			array('[merchant]'=>$merchant->restaurant_name,'[order_id]'=>$data->order_id ));
			
			$customer = ACustomer::get($data->client_id);      

			$country_code='';            
            if(is_array($place_data) && count($place_data)>=1){
                $country_code = isset($place_data['address'])?$place_data['address']['country_code']:'US';
            }
            
            $params = [
				'amount'              => ($total*100),
				'customerTrns'        => $payment_description,
				'customer'            => [
					'email'       => $customer->email_address,
					'fullName'    => $customer->first_name." ".$customer->last_name,
					'phone'       => $customer->contact_phone,
					'countryCode' =>  !empty($country_code)?$country_code:'US',
					'requestLang' => 'en-GB'
				],
				'paymentTimeout'      => 1800,
				'preauth'             => false,
				'allowRecurring'      => false,
				'maxInstallments'     => 12,
				'paymentNotification' => true,
				'tipAmount'           => 1,
				'disableExactAmount'  => false,
				'disableCash'         => false,
				'disableWallet'       => false,    				
				'sourceCode'          => $soure_code,
				'merchantTrns'        => $order_uuid
			];            
			
            $tokens = PaymentVivaWallet::getTokens($client_id,$secret,$is_live);                
			$order_code = PaymentVivaWallet::SmartCheckout($params,$tokens,$is_live);   
            $redirect_url = PaymentVivaWallet::getRedirectURL($is_live);                                
            $redirect_url.="?ref=$order_code";

			$order_model = new AR_ordernew_meta();
			$order_model->order_id = $data->order_id;
			$order_model->meta_name = "vivawallet_tokens";
			$order_model->meta_value = json_encode([
				'tokens'=>$tokens,
				'is_live'=>$is_live,
				'cart_uuid'=>$cart_uuid,
				'payment_uuid'=>$payment_uuid,				
				'soure_code'=>$soure_code
			]);
			$order_model->save();

			$order_model = new AR_ordernew_meta();
			$order_model->order_id = $data->order_id;
			$order_model->meta_name = "vivawallet_ordercode";
			$order_model->meta_value = $order_code;
			$order_model->save();

            $this->_controller->code = 1;
            $this->_controller->msg = "OK";
            $this->_controller->details = [
                'redirect_url'=>$redirect_url
            ];

		} catch (Exception $e) {
			$this->_controller->msg[] = t($e->getMessage());							
		}			
		$this->_controller->responseJson();
    }
  
}
// end class