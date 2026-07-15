<?php
class MercadopagoAddcard extends CAction
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
			$card_name = isset($this->data['card_name'])?$this->data['card_name']:'';
			$email_address = isset($this->data['email_address'])?$this->data['email_address']:'';
			$card_token = isset($this->data['id'])?$this->data['id']:'';
			$customer_id = isset($this->data['customer_id'])?$this->data['customer_id']:'';
						
			$credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,$merchant_type);
			$credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';
			$is_live = isset($credentials['is_live'])?intval($credentials['is_live']):0;
			
			$acess_token = isset($credentials['attr2'])?trim($credentials['attr2']):'';
            MercadoPago\SDK::setAccessToken($acess_token);

            if(!empty($customer_id) && strlen($customer_id)>5){
                $card = new MercadoPago\Card();
                $card->token = $card_token;
                $card->customer_id = $customer_id;
                $card->save();     
                if($card->id>0){ 
                    $mask_card = CommonUtility::mask("111111111111".$card->last_four_digits);

                    $model_method = new AR_client_payment_method;
				    $model_method->client_id = intval(Yii::app()->user->id);
				    $model_method->payment_code = $payment_code;
				    $model_method->as_default = 1;				    
				    $model_method->attr1 = $card->issuer->name;
				    $model_method->attr2 = $mask_card;			
				    $model_method->merchant_id = isset($credentials['merchant_id'])? intval($credentials['merchant_id']) :0;
                    				    
				    $model_method->method_meta = array(
				      array(
				        'meta_name'=>'customer_id',
				        'meta_value'=>$customer_id,
				        'date_created'=>CommonUtility::dateNow(),
				      ),
				      array(
				        'meta_name'=>'card_id',
				        'meta_value'=>$card->id,
				        'date_created'=>CommonUtility::dateNow(),
				      ),
				      array(
				        'meta_name'=>'is_live',
				        'meta_value'=>$is_live,
				        'date_created'=>CommonUtility::dateNow(),
				      ),
				    );
				    
				    if($model_method->save()){
				    	$this->_controller->code = 1; 
				    	$this->_controller->msg = "OK";	
                        $this->_controller->details = array();
				    } else $this->_controller->msg = CommonUtility::parseError( $model_method->getErrors());	

                } else {
                	if(isset($card->error->causes)){
						foreach ($card->error->causes as $items) {
							$this->_controller->msg[] = $items->description;
						}
					} else $this->_controller->msg[] = t("An error has occured." . json_encode($card->error));
                }              

            } else $this->_controller->msg[] = t("Invalid customer id");
          
		} catch (Exception $e) {
			$this->_controller->msg[] = t($e->getMessage());							
		}			
		$this->_controller->responseJson();
    }
  
}
// end class