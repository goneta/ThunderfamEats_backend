<?php
class MercadopagoCustomer extends CAction
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

            $credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,$merchant_type);
			$credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';
			$is_live = isset($credentials['is_live'])?intval($credentials['is_live']):0;
						
			$email_address = Yii::app()->user->email_address;
			if($credentials['is_live']<=0){
				$email_address = "test".Yii::app()->user->id."_".$email_address;
			}

            $acess_token = isset($credentials['attr2'])?trim($credentials['attr2']):'';
            MercadoPago\SDK::setAccessToken($acess_token);

            $customer_id = '';
			try {
				$customer_id = $this->searchCustomer($email_address,$acess_token);
			} catch (Exception $e) {
				//
			}

            if(!empty($customer_id) && strlen($customer_id)>5){
				// already created
			} else {
				$customer = new MercadoPago\Customer();
			    $customer->email = $email_address ;
			    $customer->first_name = Yii::app()->user->first_name ;
			    $customer->last_name = Yii::app()->user->last_name ;
			    $customer->save();			
			    $customer_id = $customer->id;
			    if(empty($customer_id)){
			    	if(isset($customer->error->causes)){
						foreach ($customer->error->causes as $items) {
							$this->_controller->msg[] = $items->description;
						}
					} else $this->_controller->msg[] = t("An error has occured." . json_encode($customer->error));
			    }
			}
			
			if(!empty($customer_id) && strlen($customer_id)>5){
				$this->_controller->code = 1;
				$this->_controller->msg = "OK";
				$this->_controller->details = array(
				  'customer_id'=>$customer_id,
				  'test_card'=>false
				);				
                if(DEMO_MODE){
					$this->_controller->details['test_card'] = array(
					  'card_number'=>'5031755734530604',
					  'expiry'=>'11/2022',
					  'cvv'=>'123',
					  'identification_type'=>'DNI',
					  'identification_number'=>'12334566'
					);
				}
			}            
		} catch (Exception $e) {
			$this->_controller->msg = t($e->getMessage());							
		}			
		$this->_controller->responseJson();
    }

    private function searchCustomer($email_address='',$access_token=array())
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, 'https://api.mercadopago.com/v1/customers/search?email='.$email_address);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		
		$headers = array();
		$headers[] = 'Authorization: Bearer '.$access_token;
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		$result = curl_exec($ch);
		if (curl_errno($ch)) {
		    //echo 'Error:' . curl_error($ch);
		    throw new Exception( 'Error:' . curl_error($ch) );
		}
		curl_close($ch);
		
		if($json=json_decode($result,true)){			
			if($json['paging']['total']>0){
				foreach ($json['results'] as $items) {
					$customer_id = $items['id'];
					break;
				}
				return $customer_id;
			}
		} 
		throw new Exception( 'no results' );
	}

}
// end class