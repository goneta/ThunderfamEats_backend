<?php
class plansrazorpayComponents extends CWidget 
{
	public $data;
	public $credentials;
	
	public function run() {	        

		ScriptUtility::registerJS([
			'https://checkout.razorpay.com/v1/checkout.js'
		],CClientScript::POS_HEAD);
		
		$key_id = isset($this->credentials['attr1'])?$this->credentials['attr1']:'';

		$this->render('razorpay-plans-components',array(
		   'payment_code'=>$this->data['payment_code'],		
		   'key_id'=>$key_id,		
		));
	}
	
}
/*end class*/