<?php
class DeliveryboyController extends SiteCommon
{
  
	public function beforeAction($action)
	{				
		if(isset($_GET)){			
			$_GET = Yii::app()->input->xssClean($_GET);			
		}

		// CHECK MAINTENANCE MODE
		$this->maintenanceMode();

		// SEO 
		CSeo::setPage();		

		return true;
	}

    public function actionIndex()
    {
        $this->actionSignup();
    }

	public function actionSignup()
	{

		$setttings = Yii::app()->params['settings'];
		$driver_enabled_registration = isset($setttings['driver_enabled_registration'])?$setttings['driver_enabled_registration']:false;
		if(!$driver_enabled_registration){
			$this->render("//store/404-page");
			Yii::app()->end();
		}

		$options = OptionsTools::find(array('signup_type','signup_enabled_capcha','signup_enabled_terms',
		'signup_terms','signup_resend_counter','captcha_site_key','fb_app_id','google_client_id',
		'mobilephone_settings_country','mobilephone_settings_default_country','captcha_lang','driver_signup_terms_condition'
		));

		$phone_default_country = isset($options['mobilephone_settings_default_country'])?$options['mobilephone_settings_default_country']:'us';
        $phone_country_list = isset($options['mobilephone_settings_country'])?$options['mobilephone_settings_country']:'';
        $phone_country_list = !empty($phone_country_list)?json_decode($phone_country_list,true):array();        

		$terms_condition = isset($options['driver_signup_terms_condition'])?trim($options['driver_signup_terms_condition']):'';

		$this->render('rider_signup',[
			'phone_country_list'=>$phone_country_list,
			'phone_default_country'=>$phone_default_country,
			'terms_condition'=>$terms_condition
		]);
	}

	public function actionverification()
	{
		$error = '';
		try {
			$driver_uuid = Yii::app()->input->get('uuid');
			$model = AR_driver::model()->find('driver_uuid=:driver_uuid',
			array(':driver_uuid'=> $driver_uuid ));
			if($model){
				$data =[
					'status'=>$model->status,
					'account_verified'=>$model->account_verified,
				];				
				$options = OptionsTools::find(['driver_sendcode_via','signup_enabled_verification','driver_sendcode_interval']  );
				$sendcode_via  = isset($options['driver_sendcode_via'])?$options['driver_sendcode_via']:'email';
				$signup_resend_counter  = isset($options['driver_sendcode_interval'])?$options['driver_sendcode_interval']:20;

				$message = '';
				if($sendcode_via=="email"){
					$message = t("We sent a code to {{email_address}}.",array(
						'{{email_address}}'=> CommonUtility::maskEmail($model->email)
					  ));
				} else {
					$message = t("We sent a code to {{phone}}.",array(
						'{{phone}}'=> CommonUtility::mask($model->phone)
					  ));
				}

				$model->scenario = 'verify_code';
				
				if(isset($_POST['AR_driver'])){
					$model->attributes=$_POST['AR_driver'];
					if($model->validate()){					
						$model->account_verified = 1;													
						if($model->save()){							
							$this->redirect(Yii::app()->createUrl("/deliveryboy/attach_license",[
								'uuid'=>$driver_uuid
							]));		
							Yii::app()->end();
						} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString( $model->getErrors(),"<br/>" ));
					} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString( $model->getErrors(),"<br/>" ));
				}				
				$this->render("rider_verification",[
					'message'=>$message,
					'resend_counter'=>$signup_resend_counter,
					'driver_uuid'=>$driver_uuid,
					'model'=>$model
				]);
			} else $error = t("account not found");
		} catch (Exception $e) {
			$error = t($e->getMessage());									
		}	

		if(!empty($error)){
			$this->render("//store/404-page");
		}		
	}    

    public function actionattach_license()
    {
        try {
            $upload_path = "upload/license";
            $options = OptionsTools::find(['driver_registration_process']);
			$registration_process = isset($options['driver_registration_process'])?$options['driver_registration_process']:'need_approval';            
            
            $driver_uuid = Yii::app()->input->get('uuid');
			$model = AR_driver::model()->find('driver_uuid=:driver_uuid',
			array(':driver_uuid'=> $driver_uuid ));
            if($model){
                $model->scenario = "attach_license_website";
                if(isset($_POST['AR_driver'])){
					$model->attributes=$_POST['AR_driver'];
                    $model->license_front_photo=CUploadedFile::getInstance($model,'license_front_photo');
                    $model->license_back_photo=CUploadedFile::getInstance($model,'license_back_photo');
                    $model->path = $upload_path;
                    $model->path_license = $upload_path;
					if($model->validate()){				                                            
                        
                        $extension = pathinfo($model->license_front_photo->name, PATHINFO_EXTENSION);
			            $extension = strtolower($extension);									
					    $license_front_photo = CommonUtility::generateUIID().".".$extension;                                                
                        
                        $path = CommonUtility::uploadDestination('upload/license/').$license_front_photo;
                        $model->license_front_photo->saveAs( $path );
					    $model->license_front_photo = $license_front_photo;									
                        
                        $extension = pathinfo($model->license_back_photo->name, PATHINFO_EXTENSION);
			            $extension = strtolower($extension);									
					    $license_back_photo = CommonUtility::generateUIID().".".$extension;                                                
                        
                        $path = CommonUtility::uploadDestination('upload/license/').$license_back_photo;
                        $model->license_back_photo->saveAs( $path );
					    $model->license_back_photo = $license_back_photo;	                        

                        if($registration_process=="activate_account"){
                            $model->status = 'active';
                        }

						if($model->save()){
							Yii::app()->user->setFlash('success',t("Successful"));
                            $ty_url =  $registration_process=="activate_account" ? "/deliveryboy/signup_ty" : "/deliveryboy/verified_ty";
							$this->redirect(Yii::app()->createUrl($ty_url,[
								'uuid'=>$driver_uuid
							]));		
							Yii::app()->end();
						} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString( $model->getErrors(),"<br/>" ));
					} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString( $model->getErrors(),"<br/>" ));
				}				
                
                $this->render("attach_license",[
                    'model'=>$model
                ]);
            } else $error = t("account not found");
        } catch (Exception $e) {
			$error = t($e->getMessage());									
            dump($error);die();
		}	

		if(!empty($error)){
			$this->render("//store/404-page");
		}		
    }

    public function actionsignup_ty()
    {
        $options = OptionsTools::find(['driver_app_name','driver_android_download_url','driver_ios_download_url']);
        $rider_app_name = isset($options['driver_app_name'])?$options['driver_app_name']:'';
		$android_download_url = isset($options['driver_android_download_url'])?$options['driver_android_download_url']:'';
		$ios_download_url = isset($options['driver_ios_download_url'])?$options['driver_ios_download_url']:'';
        $this->render("signup_ty",[
            'rider_app_name'=>$rider_app_name,
			'android_download_url'=>$android_download_url,
			'ios_download_url'=>$ios_download_url
        ]);
    }

    public function actionverified_ty()
    {
        $options = OptionsTools::find(['driver_app_name']);
        $rider_app_name = isset($options['driver_app_name'])?$options['driver_app_name']:'';
        $this->render("verified_ty",[
            'rider_app_name'=>$rider_app_name
        ]);
    }
	
}
// end class