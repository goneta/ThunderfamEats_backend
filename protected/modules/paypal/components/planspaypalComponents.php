<?php
class planspaypalComponents extends CWidget 
{
	public $data;
	public $credentials;
	
	public function run() {	        
				
		$client_id = isset($this->credentials['attr1'])?$this->credentials['attr1']:'';

		ScriptUtility::registerJS([
			'https://www.paypal.com/sdk/js?client-id='.$client_id.'&vault=true&intent=subscription'
		],CClientScript::POS_HEAD);
		

		$this->render('paypal-plans-components',array(
		   'payment_code'=>$this->data['payment_code'],			
		   'client_id'=>$client_id
		));
	}
	
}
/*end class*/