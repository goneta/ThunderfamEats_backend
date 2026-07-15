<?php
//https://www.yiiframework.com/doc/guide/1.1/en/basics.controller
//https://www.yiiframework.com/doc/api/1.1/CAction
class Pv1Controller extends PartnerCommon
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
		$this->initSettings();
		return true;
	}
    
    public function actions()
    {		       
	   return require_once('payment-actions.php');
    }
}
// end class