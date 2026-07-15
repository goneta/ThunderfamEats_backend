<?php
class touchpayComponents extends CWidget 
{
	public $data;
	public $credentials;
	
	public function run() {				
		$this->render('components-touchpay',array(
		    'payment_code'=>$this->data['payment_code'],
		    'credentials'=>$this->credentials,
			'ajaxurl'=>Yii::app()->createAbsoluteUrl($this->data['payment_code']."/api"),
			'redirect'=>''
		));
	}
	
}
/*end class*/