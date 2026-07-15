<?php
class PaymentplanController extends SiteCommon
{
	
	public function beforeAction($action)
	{									
		return true;
	}

    public function actionIndex()
    {
        $payment_id = Yii::app()->input->get('payment_id');
        $model = AR_plans_create_payment::model()->find("payment_id=:payment_id",[
            ':payment_id'=>$payment_id
        ]);        
        if($model){            
            ScriptUtility::registerScript(array(			
				"var payment_id='".CJavaScript::quote($model->payment_id)."';",	
				// "var subscriber_id='".CJavaScript::quote($model->subscriber_id)."';",											
				"var subscription_type='".CJavaScript::quote($model->subscription_type)."';",
                // "var success_url='".CJavaScript::quote($model->success_url)."';",
                // "var failed_url='".CJavaScript::quote($model->failed_url)."';",
			),'plan_registration');

            //try {											
				$payments = AttributesTools::PaymentPlansProvider();
				$payments_credentials = CPayments::getPaymentCredentials(0,'',0);
				CComponentsManager::RegisterBundle($payments ,'plans-');				
				$this->render('payment-list',[
					'payments'=>$payments,
					'payments_credentials'=>$payments_credentials
				]);				
			// } catch (Exception $e) {
			// 	$this->render("//store/404-page");
			// }				

        } else $this->render("//store/404-page");
    }

}
// end class