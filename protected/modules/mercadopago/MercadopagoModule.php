<?php
require_once "mercadopago/vendor/autoload.php";
require_once "mercadopago/vendor/autoload.php";
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentRefundClient;

class MercadopagoModule extends CWebModule
{	
	
	public function init()
	{
		$this->setImport(array(			
			'mercadopago.components.*',
			'mercadopago.models.*'
		));
	}

	public static function paymentCode()
	{
		return 'mercadopago';
	}
	
		
	public function beforeControllerAction($controller, $action)
	{									
		if(parent::beforeControllerAction($controller, $action))
		{
			// this method is called before any module controller action is performed
			// you may place customized code here									
			return true;
		}
		else
			return false;
	}
	
	public function paymentInstructions()
	{
		return array(
		  'method'=>"online",
		  'redirect'=>''
		);
	}
	
	public function savedTransaction($data)
	{					
		
	}
	
	public function delete($data)
	{		
		AR_payment_method_meta::model()->deleteAll("payment_method_id=:payment_method_id",array(
		  ':payment_method_id'=>$data->payment_method_id
		));		
	}
	
	public function refund($credentials=array(), $transaction=array(), $payment = array())
	{
		try {
			
			$refund_amount = Price_Formatter::convertToRaw($transaction->trans_amount) * 100;
			$access_token = isset($credentials['attr2'])?$credentials['attr2']:'';            
            $is_live = isset($credentials['is_live'])?$credentials['is_live']:false;
            $is_live = $is_live==1?true:false;
			
			MercadoPagoConfig::setAccessToken($access_token);            
            MercadoPagoConfig::setRuntimeEnviroment( $is_live? MercadoPagoConfig::SERVER : MercadoPagoConfig::LOCAL);

			$client = new PaymentRefundClient(); 			
			$refund = $client->refund($payment->payment_reference, $refund_amount ); 						
			return array(
				'id'=>$refund->id
			);									
		} catch (MPApiException $e) {                
			$err_model = $e->getApiResponse()->getContent();			
			$error = isset($err_model['message']) ? $err_model['message'] : t("Undefined Errors");
		} catch (\Exception $e) {
			$error =  $e->getMessage();			
		}                        
		throw new Exception( $error );
	}
	
	private function getPayment($payment_reference='',$access_token='')
	{				
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://api.mercadopago.com/v1/payments/'.$payment_reference);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
				
		$headers = array();
		$headers[] = 'Authorization: Bearer '.$access_token;
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		$result = curl_exec($ch);		
		if (curl_errno($ch)) {		   
		    throw new Exception( 'Error:' . curl_error($ch) );
		}
		curl_close($ch);
		
		if($json=json_decode($result,true)){			
			$status = isset($json['status'])?$json['status']:'';
			$message = isset($json['message'])?$json['message']:'';			
			if(!empty($message) && $status>0){
				throw new Exception( $message );
			} else return $json;
		}
		throw new Exception( 'no results' );
	}
}
/*end class*/