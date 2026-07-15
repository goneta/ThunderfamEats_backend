<?php
class InterfacesubscriptionController extends CController
{
    public $code=2,$msg,$details,$data;

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

    public function responseJson()
    {
		header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST");
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])){
		   header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
	    }
    	header('Content-type: application/json');
		$resp=array('code'=>$this->code,'msg'=>$this->msg,'details'=>$this->details);
		echo CJSON::encode($resp);
		Yii::app()->end();
    }
    
    public function actions()
    {		
        return array(
            'createmerchantaccount'=>'application.controllers.plan.Createmerchantaccount',            
            'stripeubscribeaccount'=>'application.controllers.plan.Stripeubscribeaccount',  
			'stripevalidatepayment'=>'application.controllers.plan.Stripevalidatepayment', 
        );
    }
}
// end class