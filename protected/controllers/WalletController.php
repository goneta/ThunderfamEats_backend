<?php
require 'php-jwt/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class WalletController extends SiteCommon
{
	public function beforeAction($action)
	{		

		// SEO 
		CSeo::setPage();
		
		return true;			
	}
		
    public function actionupload_deposit()
    {
        try {            
            	
            $reference_id =  Yii::app()->request->getQuery('reference_id', null); 

            $order = AR_wallet_transactions_meta::model()->find("meta_name=:meta_name",[
                ':meta_name'=>$reference_id
            ]);
            if(!$order){
                $this->render("//store/error",[
                    'message'=>t("Payment reference not found")
                ]);                
            }

            $coded_data = json_decode($order->meta_value,true);
            $data = $coded_data['data'] ?? null;
            $jwt_key = new Key(CRON_KEY, 'HS256');
			$decoded = (array) JWT::decode($data, $jwt_key); 
            
            $merchant_id = $decoded['merchant_id'] ?? 0;            
            $currency_code = $decoded['currency_code'] ?? '';            
            $exchange_rate = $decoded['exchange_rate_merchant_to_admin'] ?? 1;             

            $model = AR_bank_deposit::model()->find("deposit_type=:deposit_type AND transaction_ref_id=:transaction_ref_id",[
				':deposit_type'=>"wallet_loading",
				':transaction_ref_id'=>$reference_id
			]);
			if(!$model){                
				$model = new AR_bank_deposit;
			} else {
                if($model->status!='pending'){
                    $this->render("//store/error",[
                        'message'=>t("This link is no longer available")
                    ]);
                    Yii::app()->end();
                }
            }
            
            if(isset($_POST['AR_bank_deposit'])){
                $model->attributes=$_POST['AR_bank_deposit'];
				$model->proof_image=CUploadedFile::getInstance($model,'proof_image');
                if($model->validate()){
                    $file_uuid = CommonUtility::createUUID("{{bank_deposit}}",'deposit_uuid');										
					$extension = pathinfo($model->proof_image->name, PATHINFO_EXTENSION);
			        $extension = strtolower($extension);									
					$new_filename = $file_uuid.".".$extension;

                    $model->transaction_ref_id = $reference_id;
					$model->path = "upload/deposit";
					$model->deposit_uuid = $file_uuid;			
					$model->merchant_id = $merchant_id;		

					$path = CommonUtility::uploadDestination('upload/deposit/').$new_filename;
                    $model->proof_image->saveAs( $path );
                
					$model->proof_image = $new_filename;	

					$model->use_currency_code = $currency_code;
					$model->base_currency_code = $currency_code;
					$model->admin_base_currency = $currency_code;
					$model->exchange_rate = $exchange_rate;
					$model->exchange_rate_merchant_to_admin = $exchange_rate;
					$model->exchange_rate_admin_to_merchant = $exchange_rate;
                    
                    $model->deposit_type = 'wallet_loading';
                    $model->date_modified = CommonUtility::dateNow();
                    
					if($model->save()){
						Yii::app()->user->setFlash('success',t("You succesfully upload bank deposit. Please wait while we validate your payment."));
						$this->refresh();
					} else {						
						Yii::app()->user->setFlash('error',t(Helper_failed_save));
					}
                } else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString( $model->getErrors(),"<br/>" ));
            }

            $this->render("upload_deposit",[
				'order'=>[
                    'reference_id'=>$reference_id,
                    'amount'=>$decoded['amount'] ?? '',
                ],
				'model'=>$model,
			]);

        } catch (Exception $e) {            
			$this->render("//store/error",[
                'message'=>t($e->getMessage())
            ]);
        }		
    }

}
// end class