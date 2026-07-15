<?php
class ApiController extends SiteCommon
{
	
	public function beforeAction($action)
	{								
		$method = Yii::app()->getRequest()->getRequestType();
		if($method!="POST"){
			//return false;
		}
		
		$action_name = Yii::app()->controller->action->id;

		if($action_name!="postpayment"){
			if(Yii::app()->user->isGuest){
				return false;
			}
		}		
						
		if($method=="PUT"){            
			$this->data = Yii::app()->input->xssClean(json_decode(file_get_contents('php://input'), true));
		} else $this->data = Yii::app()->input->xssClean($_POST);				
				
		return true;
	}	

    public function actiongetpaydelivery()
    {        
        try {            
            $merchant_id = intval(Yii::app()->input->post('merchant_id'));               
            if($merchant_id>0){
                $data = CPayments::PayondeliveryByMerchant($merchant_id); 
            } else $data = CPayments::PayondeliveryList();                        
            $this->code = 1;
            $this->msg = "Ok";
            $this->details = [
                'data'=>$data
            ];
        } catch (Exception $e) {
			$this->msg[] = t($e->getMessage());							
		}
        $this->responseJson();
    }

    public function actionsavedPaydelivery()
    {        
        try {
            
            $payment_code = Yii::app()->input->post('payment_code');            
            $merchant_id = Yii::app()->input->post('merchant_id');
            $payment_id = Yii::app()->input->post('payment_id');

            if($payment_id<=0){
                $this->msg[] = t("Payment is required");
                $this->jsonResponse();
            }
            
            $payment = AR_payment_gateway::model()->find('payment_code=:payment_code', 
		    array(':payment_code'=>$payment_code)); 
            if($payment){
                $data = CPayments::getPayondelivery($payment_id);
                $model = new AR_client_payment_method;
                $model->scenario = "insert";
                $model->client_id = Yii::app()->user->id;
				$model->payment_code = $payment_code;
				$model->as_default = intval(1);
                $model->reference_id = $payment_id;
                $model->attr1 = $payment->payment_name;
                $model->attr2 = $data->payment_name;
                $model->merchant_id = intval($merchant_id);                
                if($model->save()){
					$this->code = 1;
		    		$this->msg = t("Succesful");
				} else $this->msg[] = CommonUtility::parseError($model->getErrors());                
            } else $this->msg[] = t("Payment already exist");
        } catch (Exception $e) {
			$this->msg[] = t($e->getMessage());							
		}
        $this->responseJson();
    }
    
}
// end class