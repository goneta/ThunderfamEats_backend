<?php
class Touchpaycheckout extends CAction
{
    public $_controller;
    public $_id;
    public $data;

    public function __construct($controller,$id)
    {       
        Yii::app()->setImport(array(			
            'application.modules.touchpay.components.*',
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
			$agency_code = isset($credentials['attr1'])?trim($credentials['attr1']):'';
            $secure_code = isset($credentials['attr7'])?trim($credentials['attr7']):'';
            $soure_code = isset($credentials['attr3'])?trim($credentials['attr3']):'';

            $data = COrders::get($order_uuid);
			$merchant = CMerchantListingV1::getMerchant($data->merchant_id);
			$order_meta = COrders::getAttributesAll($data->order_id,['place_id']);
			$total = floatval(Price_Formatter::convertToRaw($data->total));
            $payment_description = t("Payment to merchant [merchant]. Order#[order_id]",
			array('[merchant]'=>$merchant->restaurant_name,'[order_id]'=>$data->order_id ));
			$client_city = isset($order_meta['place_id'])?CClientAddress::getAddress($order_meta['place_id'],$data->client_id):"Abidjan";
			$customer = ACustomer::get($data->client_id);      
			$city = isset($client_city['address']['city'])&&!empty($client_city['address']['city'])?$client_city['address']['city']:"";

			$country_code='';            
            if(is_array($place_data) && count($place_data)>=1){
                $country_code = isset($place_data['address'])?$place_data['address']['country_code']:'US';
            }
            
            $params = [
				"order_number"=>$data->order_uuid,
				"agency_code"=>$agency_code,
				"secure_code"=>$secure_code,
				"domain_name"=>$_SERVER['SERVER_NAME'],
				"url_redirection_success"=>Yii::app()->createAbsoluteUrl('touchpay/apiapp/verifypayment', array(
					'order_uuid'=>$order_uuid,
					'cart_uuid'=>$cart_uuid
				)),
				"url_redirection_failed"=>Yii::app()->createAbsoluteUrl('touchpay/apiapp/failed', array(
					'order_uuid'=>$order_uuid,
					'cart_uuid'=>$cart_uuid
				)),
				"amount"=>($total),
				"email"=>$customer->email_address,
				"clientFirstName"=>$customer->first_name,
				"clientLastName"=>$customer->last_name,
				"clientPhone"=>$customer->contact_phone,
				"city"=>$city,
			];      
                  
			$order_model = new AR_ordernew_meta();
			$order_model->order_id = $data->order_id;
			$order_model->meta_name = "touchpay_tokens";
			$order_model->meta_value = json_encode([
				'cart_uuid'=>$cart_uuid,
				'payment_uuid'=>$payment_uuid,				
				'soure_code'=>$soure_code
			]);
			$order_model->save();

			$order_model = new AR_ordernew_meta();
			$order_model->order_id = $data->order_id;
			$order_model->meta_name = "touchpay_ordercode";
			$order_model->meta_value = $order_uuid;
			$order_model->save();
			
            $this->_controller->code = 1;
            $this->_controller->msg = "OK";
            $this->_controller->details = [
				'data'=>$params
            ];

		} catch (Exception $e) {
			$this->_controller->msg[] = t($e->getMessage());							
		}			
		$this->_controller->responseJson();
    }
  
}
// end class