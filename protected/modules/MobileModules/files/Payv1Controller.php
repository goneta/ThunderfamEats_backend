<?php
class Payv1Controller extends InterfaceCommon
{
    public function beforeAction($action)
	{								
		$method = Yii::app()->getRequest()->getRequestType();    		
		if($method=="POST"){
			$this->data = Yii::app()->input->xssClean(json_decode(file_get_contents('php://input'), true));
		} else if($method=="GET"){
		   $this->data = Yii::app()->input->xssClean($_GET);				
		} elseif ($method=="OPTIONS" ){
			$this->responseJson();
		} else $this->data = Yii::app()->input->xssClean($_POST);		
		return true;
	}
    
    public function actions()
    {		
        return array(
            'stripecreatecustomer'=>'application.controllers.paymentapi.Stripecreatecustomer',
            'stripesavepayment'=>'application.controllers.paymentapi.StripeSavePayment',
            'stripecreateintent'=>'application.controllers.paymentapi.StripeCreateIntent',            
			'stripepaymentintent'=>'application.controllers.paymentapi.StripePaymentIntent', 
			'paypalverifypayment'=>'application.controllers.paymentapi.PaypalVerifyPayment', 
			'razorpaycreatecustomer'=>'application.controllers.paymentapi.RazorpayCreateCustomer', 
			'razorpaycreateorder'=>'application.controllers.paymentapi.RazorpayCreateOrder', 
			'razorpayverifypayment'=>'application.controllers.paymentapi.RazorpayVerifyPayment', 
			'mercadopagocustomer'=>'application.controllers.paymentapi.MercadopagoCustomer', 
			'mercadopagoaddcard'=>'application.controllers.paymentapi.MercadopagoAddcard', 
			'mercadopagogetcard'=>'application.controllers.paymentapi.MercadopagoGetcard', 
			'mercadopagocapturepayment'=>'application.controllers.paymentapi.MercadopagoCapturePayment', 
        );
    }
}
// end class