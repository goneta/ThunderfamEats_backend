<?php
require 'php-jwt/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class PlanController extends Commonmerchant
{

    public function beforeAction($action)
    {
        
        $front_api = CommonUtility::getHomebaseUrl();
        $plan_api = CommonUtility::getHomebaseUrl()."/apisubscriptions";
            
        if(empty(Yii::app()->merchant->logintoken)){        
            $merchant = AR_merchant_user::model()->find("merchant_user_id=:merchant_user_id",[
                ':merchant_user_id'=>Yii::app()->merchant->id
            ]);
            if($merchant){                
                $session_token = CommonUtility::createUUID("{{merchant_user}}",'session_token');	
                $merchant->session_token = $session_token;
                $merchant->save();
                Yii::app()->merchant->logintoken = $session_token;
            }
        }

        $payload = [
			'iss'=>Yii::app()->request->getServerName(),
			'sub'=>0,
			'iat'=>time(),
			'token'=>Yii::app()->merchant->logintoken			
		];        
		$jwt_token = JWT::encode($payload, CRON_KEY, 'HS256');	
        
        ScriptUtility::registerScript(array(            
            "var plan_api='$plan_api';",            
            "var front_api='$front_api';", 
            "var token='".CJavaScript::quote($jwt_token)."';",		 
			"var language='".CJavaScript::quote(Yii::app()->language)."';",		
        ),'plan_script');

        $include  = array(				
			Yii::app()->baseUrl."/assets/js/manage-plan.js?version=".time()			
		);
		ScriptUtility::registerJS($include,CClientScript::POS_END);

        return true;
    }

    public function actionIndex(){

    }

    public function actionmanage()
    {                            

        $subscriber_id = Yii::app()->merchant->merchant_id;         
        
        $success_url = Yii::app()->createAbsoluteUrl("/plan/subscription_successful");
		$failed_url =  CommonUtility::getHomebaseUrl()."/merchant/signup-failed";
		$payment_url = CommonUtility::getHomebaseUrl()."/paymentplan";                    

		ScriptUtility::registerScript(array(						
			"var subscriber_id='".CJavaScript::quote($subscriber_id)."';",			
			"var success_url='".CJavaScript::quote($success_url)."';",
			"var failed_url='".CJavaScript::quote($failed_url)."';",
			"var payment_url='".CJavaScript::quote($payment_url)."';",
		),'plan_registration');

        $this->render("manage-plan",[
            'params'=>[
                'links'=>array(	
                   t("Manage Plan")=>array(Yii::app()->controller->id.'/manage'),
                   t("Subscriptions")
                ),
            ]
        ]);
    }

    public function actionsubscription_successful()
    {        
        $success_url = CommonUtility::getHomebaseUrl()."/merchant/subscription_successful";        
        Yii::app()->merchant->logout(false);		
		$this->redirect($success_url);		
    }

    // public function actionpayment()
    // {

    //     $payments = array(); $payments_credentials = array();
	// 	try {					
	// 		$payments = AttributesTools::PaymentPlansProvider();			
	// 		$payments_credentials = CPayments::getPaymentCredentials(0,'',0);            
	// 		AComponentsManager::RegisterBundle($payments ,'subs-','home_modules_dir');
	// 	} catch (Exception $e) {
	// 	    //
	// 	}	

    //     try {
    //         $error = '';
    //         $id = Yii::app()->input->get('id');
    //         $data = Cplans::getByUUID($id);          
    //         $amount = $data->promo_price>0? $data->promo_price : $data->price;            
    //         $this->render("payment-plan",[
    //             'params'=>[
    //                 'links'=>array(	
    //                    t("Manage Plan")=>array(Yii::app()->controller->id.'/manage'),
    //                    t("Choose Payment")
    //                 ),
    //             ],
    //             'amount'=>Price_Formatter::formatNumber($amount),
    //             'payments'=>$payments,
    //             'payments_credentials'=>$payments_credentials,
    //         ]);  
    //     } catch (Exception $e) {
    //         $error = t($e->getMessage());		   
    //         dump($error);
    //     }		
    // }

}
// end class