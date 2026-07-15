<?php
require_once 'LeagueCSV/vendor/autoload.php';
require 'php-jwt/vendor/autoload.php';
use League\Csv\Writer;
use League\Csv\Reader;
use League\Csv\Statement;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class DriverController extends CommonController
{
		
	public function beforeAction($action)
	{						
		InlineCSTools::registerStatusCSS();
		InlineCSTools::registerOrder_StatusCSS();

		$calendar = Ccalendar::elementPLus();
		$calendar = json_encode($calendar);
		
		ScriptUtility::registerScript(array(
			"var calendar_translation='".CJavaScript::quote($calendar)."';",			
		),'calendar_translation');	

		$include  = array(				
			Yii::app()->baseUrl."/assets/js/elementplus-calendar.js"				
		);
		ScriptUtility::registerJS($include,CClientScript::POS_BEGIN);
			
		return true;
	}

	public function actionsettings()
	{
		$this->pageTitle = t("Settings");		
		$model=new AR_option;

		$options = array('driver_enabled_alert','driver_alert_time',
		'driver_enabled_auto_assign','driver_allowed_number_task','driver_assign_when_accepted','driver_map_enabled_cluster','driver_task_take_pic','driver_enabled_request_break',
		'driver_request_break_limit','driver_enabled_delivery_otp','driver_maximum_cash_amount','driver_time_allowed_accept_order','driver_enabled_time_allowed_acceptance',
		'driver_missed_order_tpl','driver_commission_per_delivery','driver_order_otp_tpl','driver_enabled_end_shift',
		'driver_app_name','driver_android_download_url','driver_ios_download_url','driver_app_version_android','driver_app_version_ios',
		'driver_add_proof_photo','driver_on_demand_availability','driver_threshold_meters',
		'driver_auto_assign_retry','driver_assign_max_retry'
	   );

		if(isset($_POST['AR_option'])){
						
			if(DEMO_MODE){			
			    $this->render('//tpl/error',array(  
			          'error'=>array(
			            'message'=>t("Modification not available in demo")
			          )
			        ));	
			    return false;
			}
			
			$model->attributes=$_POST['AR_option'];
			if($model->validate()){				
				if(OptionsTools::save($options, $model)){
					Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
					$this->refresh();
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			}
		}
		
		if($data = OptionsTools::find($options)){
			foreach ($data as $name=>$val) {
				$model[$name]=$val;				
			}			
		}		

		$template_list =  CommonUtility::getDataToDropDown("{{templates}}",'template_id','template_name',
		"","ORDER BY template_name ASC");
		
		$this->render("//tpl/submenu_tpl",array(
		    'model'=>$model,
			'template_name'=>"//driver/settings",
			'widget'=>'WidgetDriverSettings',					
			'params'=>array(  
			   'model'=>$model,
			   'template_list'=>$template_list,
			   'links'=>array(		 
			      t("Delivery")=>array('driver/settings'),        
                  $this->pageTitle,                           
			   ),
			 )
		));					
		
	}

	public function actionfirebase_settings()
	{
		$this->pageTitle = t("Firebase settings");		
		CommonUtility::setMenuActive('.delivery_driver','.driver_settings');			

		$model=new AR_option;
		$options = ['firebase_apikey','firebase_domain','firebase_projectid','firebase_storagebucket','firebase_messagingid','firebase_appid'];

		if(isset($_POST['AR_option'])){
						
			if(DEMO_MODE){			
			    $this->render('//tpl/error',array(  
			          'error'=>array(
			            'message'=>t("Modification not available in demo")
			          )
			        ));	
			    return false;
			}
			
			$model->attributes=$_POST['AR_option'];
			if($model->validate()){				
				if(OptionsTools::save($options, $model)){
					Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
					$this->refresh();
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			}
		}
		
		if($data = OptionsTools::find($options)){
			foreach ($data as $name=>$val) {
				if(DEMO_MODE){
					$model[$name] = CommonUtility::mask(date("Ymjhs"));
				} else $model[$name]=$val;				
			}			
		}		
		
		$this->render("//tpl/submenu_tpl",array(
		    'model'=>$model,
			'template_name'=>"//driver/firebase_settings",
			'widget'=>'WidgetDriverSettings',					
			'params'=>array(  
			   'model'=>$model,			   
			   'links'=>array(		 
			      t("Delivery")=>array('driver/settings'),        
                  $this->pageTitle,                           
			   ),
			 )
		));					
	}

	public function actionwithdrawal_settings()
	{
		$this->pageTitle = t("Withdrawal Settings");	
		CommonUtility::setMenuActive('.delivery_driver','.driver_settings');
		$model=new AR_option;

		$options = array('driver_cashout_fee','driver_cashout_minimum','driver_cashout_miximum','driver_cashout_request_limit');

		if(isset($_POST['AR_option'])){
						
			if(DEMO_MODE){			
			    $this->render('//tpl/error',array(  
			          'error'=>array(
			            'message'=>t("Modification not available in demo")
			          )
			        ));	
			    return false;
			}
			
			$model->attributes=$_POST['AR_option'];
			if($model->validate()){				
				if(OptionsTools::save($options, $model)){
					Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
					$this->refresh();
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			}
		}
		
		if($data = OptionsTools::find($options)){
			foreach ($data as $name=>$val) {
				if(DEMO_MODE){
					$model[$name] = CommonUtility::mask(date("Ymjhs"));
				} else $model[$name]=$val;				
			}			
		}		

		$this->render("//tpl/submenu_tpl",array(
		    'model'=>$model,
			'template_name'=>"//driver/withdrawal_settings",
			'widget'=>'WidgetDriverSettings',					
			'params'=>array(  
			   'model'=>$model,			   
			   'links'=>array(		 
			      t("Delivery")=>array('driver/settings'),        
                  $this->pageTitle,                           
			   ),
			 )
		));					
	}

	public function actionsignup_settings()
	{
		$this->pageTitle = t("Signup Settings");		
		CommonUtility::setMenuActive('.delivery_driver','.driver_settings');
		$model=new AR_option;
		
		$options = [
			'driver_sendcode_via','driver_sendcode_interval','driver_sendcode_tpl','driver_signup_terms_condition',
			'driver_employment_type','driver_salary_type','driver_salary','driver_fixed_amount','driver_commission',
			'driver_commission_type','driver_registration_process','driver_enabled_registration'
		];

		if(isset($_POST['AR_option'])){
						
			if(DEMO_MODE){			
			    $this->render('//tpl/error',array(  
			          'error'=>array(
			            'message'=>t("Modification not available in demo")
			          )
			        ));	
			    return false;
			}
			
			$model->attributes=$_POST['AR_option'];
			if($model->validate()){				
				if(OptionsTools::save($options, $model)){
					Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
					$this->refresh();
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			}
		}
		
		if($data = OptionsTools::find($options)){
			foreach ($data as $name=>$val) {
				if(DEMO_MODE){
					$model[$name] = CommonUtility::mask(date("Ymjhs"));
				} else $model[$name]=$val;				
			}			
		}		


		$template_list =  CommonUtility::getDataToDropDown("{{templates}}",'template_id','template_name',
		"","ORDER BY template_name ASC");
		
		$this->render("//tpl/submenu_tpl",array(
		    'model'=>$model,
			'template_name'=>"//driver/signup_settings",
			'widget'=>'WidgetDriverSettings',					
			'params'=>array(  
			   'model'=>$model,
			   'template_list'=>$template_list,
			   'links'=>array(		 
			      t("Delivery")=>array('driver/settings'),        
                  $this->pageTitle,                           
			   ),
			   'salary_type'=>AttributesTools::DriverSalaryType(),
			   'employment_type'=>AttributesTools::DriverEmploymentType(),
			   'commission_type'=>AttributesTools::DriverCommissionType(),
			   'registration_process'=>AttributesTools::DriverAfterRegistationProcess(),
			 )
		));					
	}

	public function actionapi_access()
	{
		
		try {
			ItemIdentity::addonIdentity('Driver app');
		} catch (Exception $e) {
			$this->render('//tpl/error',[
				'error'=>[
					'message'=>$e->getMessage()
				]
			]);
			Yii::app()->end();
		}

		$this->pageTitle = t("API Access");	
		CommonUtility::setMenuActive('.delivery_driver','.driver_settings');			

		$jwt_token = AttributesTools::JwtDriverTokenID();

		$model=new AR_option;
		$model->scenario=Yii::app()->controller->action->id;
		
		$options = array($jwt_token);		
		
		if(isset($_POST['AR_option'])){

			if(DEMO_MODE){			
			    $this->render('//tpl/error',array(  
			          'error'=>array(
			            'message'=>t("Modification not available in demo")
			          )
			        ));	
			    return false;
			}

			$model->attributes=$_POST['AR_option'];			
			if($model->validate()){		
				$payload = [
					'iss'=>Yii::app()->request->getServerName(),
					'sub'=>CommonUtility::generateUIID(),					
					'iat'=>time(),						
				];						
				$jwt = JWT::encode($payload, CRON_KEY, 'HS256');
				$model->driver_jwt_token = $jwt;				
				if(OptionsTools::save($options, $model)){
					Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
					$this->refresh();
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			} else dump($model->getErrors());
		}
		
		$data_found = false;
		if($data = OptionsTools::find($options)){
			$data_found = true;
			foreach ($data as $name=>$val) {
				$model[$name]=$val;
			}		
		}		
				
		$this->render("//tpl/submenu_tpl",array(
		    'model'=>$model,
			'template_name'=>"//driver/api_access",
			'widget'=>'WidgetDriverSettings',					
			'params'=>array(  
			   'model'=>$model,
			   'data_found'=>$data_found,
			   'links'=>array(		 
			      t("Delivery")=>array('driver/settings'),        
                  $this->pageTitle,                           
			   ),
			 )
		));				
	}

	public function actionpush_notifications()
	{
		$this->pageTitle = t("API Access");
		CommonUtility::setMenuActive('.admin_site_information',".admin_site_information");
		
		$model = AR_admin_meta::model()->find("meta_name=:meta_name",[
			':meta_name'=>'push_json_file'
		]);
		if(!$model){
			$model = new AR_admin_meta; 
		}		
		
		if(isset($_POST['AR_admin_meta'])){

			if(DEMO_MODE){			
			    $this->render('//tpl/error',array(  
			          'error'=>array(
			            'message'=>t("Modification not available in demo")
			          )
			        ));	
			    return false;
			}

			$model->file = CUploadedFile::getInstance($model,'file');
			if($model->file){				
				$path = CommonUtility::uploadDestination('upload/all/').$model->file->name;				
				$extension = strtolower($model->file->getExtensionName());				
				if($extension=="json"){
					$model->meta_name = "push_json_file";
					$model->meta_value = $model->file->name;
					if($model->save()){
						$model->file->saveAs( $path );					
				        Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
				        $this->refresh();				
					} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString($model->getErrors()) );							
				} else Yii::app()->user->setFlash('error', t("Invalid file extension must be a .json file") );			
			} else {
				Yii::app()->user->setFlash('error', t("an error has occured") );
			}
		}

		$this->render('//tpl/submenu_tpl',array( 
		  'model'=>$model,
		  'template_name'=>"//admin/push_notifications",
		  'widget'=>'WidgetDriverSettings',	
		  'params'=>array(  
		     'model'=>$model,	
			 'links'=>array(		 
				t("Mobile Merchant")=>array('mobilemerchant/api_access'),        
                  $this->pageTitle,                           
			   ),	   
		  )
		));
	}

	public function actiondelete_apikeys()
	{
		if(DEMO_MODE){			
			$this->render('//tpl/error',array(  
				  'error'=>array(
					'message'=>t("Modification not available in demo")
				  )
				));	
			return false;
		}
		
		$jwt_token = AttributesTools::JwtDriverTokenID();
		$model = AR_option::model()->find("option_name=:option_name",[':option_name'=>$jwt_token]);
		if($model){
			$model->delete();
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array('driver/api_access'));			
		} else $this->render("//tpl/error");
	}

	public function actionorder_status()
	{
		$this->pageTitle = t("Settings");		
		CommonUtility::setMenuActive('.delivery_driver','.driver_settings');		
		$status_list = AttributesTools::getOrderStatus(Yii::app()->language,'delivery_status');

		$model = new AR_admin_meta;			
		
		if(isset($_POST['AR_admin_meta'])){			
						
			if(DEMO_MODE){			
			    $this->render('//tpl/error',array(  
			          'error'=>array(
			            'message'=>t("Modification not available in demo")
			          )
			        ));	
			    return false;
			}
					
			$post = $_POST['AR_admin_meta'];			

			$save_post = [
				'status_unassigned','status_assigned','status_acknowledged','status_delivery_declined','status_driver_to_restaurant',
				'status_arrived_at_restaurant','status_waiting_for_order','status_order_pickup','status_delivery_started','status_arrived_at_customer',
				'status_delivery_delivered','status_delivery_failed','status_delivery_cancelled'
			];
			
			foreach ($save_post as $items) {
				 $model->saveMeta($items, 
			      isset($post[$items])?$post[$items]['meta_value']:'',
			      isset($post[$items])?$post[$items]['meta_value1']:''
			     );			 
			}									
			
			Yii::app()->user->setFlash('success',CommonUtility::t(Helper_success));
			$this->refresh();			
		} else {
			$criteria = new CDbCriteria;
			$criteria->addInCondition('meta_name',(array) array('status_unassigned','status_assigned','status_acknowledged',
			'status_driver_to_restaurant','status_arrived_at_restaurant','status_waiting_for_order','status_order_pickup','status_delivery_started',
			'status_arrived_at_customer','status_delivery_declined','status_delivery_delivered','status_delivery_failed','status_delivery_cancelled'
			) );
			$find = AR_admin_meta::model()->findAll($criteria);		
			if($find){
				foreach ($find as $items) {										
					$model[$items->meta_name] = [
						'meta_value'=>$items->meta_value,
						'meta_value1'=>$items->meta_value1,
					];
				}
			}						
		}

		$fields = [];
		$fields['status_unassigned']['meta_value'] = t("Status for Unassigned");
		$fields['status_unassigned']['meta_value1'] = t("Description for Unassigned");

		$fields['status_assigned']['meta_value'] = t("Status for Assigned");
		$fields['status_assigned']['meta_value1'] = t("Description for Assigned");

		$fields['status_acknowledged']['meta_value'] = t("Status for Acknowledged");
		$fields['status_acknowledged']['meta_value1'] = t("Description for Acknowledged");

		$fields['status_delivery_declined']['meta_value'] = t("Status for Declined");
		$fields['status_delivery_declined']['meta_value1'] = t("Description for Declined");

		$fields['status_driver_to_restaurant']['meta_value'] = t("Status for Driver on the way to restaurant");
		$fields['status_driver_to_restaurant']['meta_value1'] = t("Description for Driver on the way to restaurant");

		$fields['status_arrived_at_restaurant']['meta_value'] = t("Status for Driver Arrived at the restaurant");
		$fields['status_arrived_at_restaurant']['meta_value1'] = t("Description for Driver Arrived at the restaurant");

		$fields['status_waiting_for_order']['meta_value'] = t("Status for Waiting for order");
		$fields['status_waiting_for_order']['meta_value1'] = t("Description for Waiting for order");

		$fields['status_order_pickup']['meta_value'] = t("Status for Order Pickup");
		$fields['status_order_pickup']['meta_value1'] = t("Description for Order Pickup");

		$fields['status_delivery_started']['meta_value'] = t("Status for Delivery Started");
		$fields['status_delivery_started']['meta_value1'] = t("Description for Delivery Started");

		$fields['status_arrived_at_customer']['meta_value'] = t("Status for Arrived at customer");
		$fields['status_arrived_at_customer']['meta_value1'] = t("Description for Arrived at customer");

		$fields['status_delivery_delivered']['meta_value'] = t("Status for Delivered");
		$fields['status_delivery_delivered']['meta_value1'] = t("Description for Delivered");

		$fields['status_delivery_failed']['meta_value'] = t("Status for Delivery Failed");
		$fields['status_delivery_failed']['meta_value1'] = t("Description for Delivery Failed");		

		$fields['status_delivery_cancelled']['meta_value'] = t("Status for Delivery Cancelled");
		$fields['status_delivery_cancelled']['meta_value1'] = t("Description for Delivery Cancelled");		

		$this->render("//tpl/submenu_tpl",array(
		    'model'=>$model,
			'template_name'=>"//driver/delivery_settings",
			'widget'=>'WidgetDriverSettings',					
			'params'=>array(  
			   'model'=>$model,					   
			   'status_list'=>$status_list,
			   'fields'=>$fields,
			   'links'=>array(		 
			      t("Delivery")=>array('driver/settings'),        
                  $this->pageTitle,                           
			   ),
			 )
		));						
	}

	public function actionsettings_tabs()
	{
		$this->pageTitle = t("Settings");
		CommonUtility::setMenuActive('.delivery_driver','.driver_settings');		

		$status = AttributesTools::getOrderStatusList(Yii::app()->language,'delivery_status');
		if($status){
			$status = AttributesTools::formatAsSelect2($status);
		}		
		
		$model = new AR_admin_meta;		
		
		$this->render("//tpl/submenu_tpl",array(
		    'model'=>$model,
			'template_name'=>"//driver/delivery_settings_tabs",
			'widget'=>'WidgetDriverSettings',					
			'params'=>array(  
			   'model'=>$model,					   
			   'status'=>$status,			   
			   'links'=>array(		 
			      t("Delivery")=>array('driver/settings'),        
                  $this->pageTitle,                           
			   ),
			 )
		));						
	}

    public function actionlist()
    {
        $this->pageTitle=t("Driver list");		
				
		$table_col = array(		  
		  'driver_uuid'=>array(
		    'label'=>t(""),
		    'width'=>'15%'
		  ),		  
		  'date_created'=>array(
		    'label'=>t(""),
		    'width'=>'15%'
		  ),		  
		  'first_name'=>array(
		    'label'=>t("Name"),
		    'width'=>'20%'
		  ),
		  'email'=>array(
		    'label'=>t("Email"),
		    'width'=>'15%'
		  ),
          'phone'=>array(
		    'label'=>t("Phone"),
		    'width'=>'15%'
		  ),
		  'employment_type'=>array(
		    'label'=>t("Employment"),
		    'width'=>'15%'
		  ),
		  'status'=>array(
		    'label'=>t("Status"),
		    'width'=>'15%'
		  ),		  		  
		  'driver_id'=>array(
		    'label'=>t("Actions"),
		    'width'=>'15%'
		  ),
		);
		$columns = array(
			array('data'=>'driver_uuid', 'visible'=>false),
			array('data'=>'date_created', 'visible'=>false),
			array('data'=>'first_name','orderable'=>true),
			array('data'=>'email'),
            array('data'=>'phone'),
			array('data'=>'employment_type'),
			array('data'=>'status'),								
			array('data'=>null,'orderable'=>false,
			   'defaultContent'=>'
			   <div class="btn-group btn-group-actions" role="group">
			      <a class="ref_view_url normal btn btn-light tool_tips"><i class="zmdi zmdi-eye"></i></a>
				  <a class="ref_edit normal btn btn-light tool_tips"><i class="zmdi zmdi-border-color"></i></a>
				  <a class="ref_delete normal btn btn-light tool_tips"><i class="zmdi zmdi-delete"></i></a>
			   </div>
			   '
			),	  
		);		
		
		$this->render('//driver/driver_list',array(
		  'table_col'=>$table_col,
		  'columns'=>$columns,
		  'order_col'=>1,
          'sortby'=>'desc',		  
          'ajax_url'=>Yii::app()->createUrl("/api"),
		  'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/add")
		));
    }

	public function actionadd($update=false)
	{
		$this->pageTitle =  $update==true? t("Update driver information"): t("Add driver");
		CommonUtility::setMenuActive('.delivery_driver','.driver_list');
		//$upload_path = CMedia::adminFolder();
		$upload_path_avatar = 'upload/avatar';
		$upload_path = "upload/license";
		$selected_item = array();
		$phone_default_country = ''; $driver_fullname ='';
		
		$id = Yii::app()->input->get('id');			
		$model = new AR_driver;
		if($update){
			$model = AR_driver::model()->find("driver_uuid=:driver_uuid",array(
			 ':driver_uuid'=>$id
			));
			if(!$model){
				$this->render('//tpl/error',array(
				 'error'=>array(
				   'message'=>t(Helper_not_found)
				 )
				));
				return ;
			}			
			$driver_fullname = "$model->first_name $model->last_name";
		}        
        
		if(isset($_POST['AR_driver'])){			
			$model->attributes=$_POST['AR_driver'];	
						
			if($phoneinfo = AttributesTools::getMobileByPhoneCodeInfo($model->phone_prefix)){				
				$phone_default_country = $phoneinfo->shortcode;
			}			
									
			if(isset($_POST['photo'])){
				if(!empty($_POST['photo'])){
					$model->photo = $_POST['photo'];
					$model->path = isset($_POST['path'])?$_POST['path']:$upload_path_avatar;
				} else $model->photo = '';
			} else $model->photo = '';		
			
			if($model->validate()){						
				if($model->save()){
					if(!$update){
					   $this->redirect(array(Yii::app()->controller->id."/list"));		
					} else {
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
						$this->refresh();
					}
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString( $model->getErrors(),"<br/>" ));
		}


		$options = OptionsTools::find(array('mobilephone_settings_country','mobilephone_settings_default_country'));

		if(empty($phoneinfo->shortcode)){
			$phone_default_country = isset($options['mobilephone_settings_default_country'])?$options['mobilephone_settings_default_country']:'us';
		}		
        $phone_country_list = isset($options['mobilephone_settings_country'])?$options['mobilephone_settings_country']:'';
        $phone_country_list = !empty($phone_country_list)?json_decode($phone_country_list,true):array();        

		$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
        $multicurrency_enabled = $multicurrency_enabled==1?true:false;		
		$currency_list = AttributesTools::CurrencyList();					
		$select = [''=>t("Please select")];
		$currency_list = $select+$currency_list;	
				
		if($update){						
			
			if(!empty($model->phone_prefix)){
				$model_country = AR_location_countries::model()->find("phonecode=:phonecode",[
					':phonecode'=>$model->phone_prefix
				]);
				if($model_country){					
					$phone_default_country = $model_country = $model_country->shortcode;
				}
			}			

			$avatar = CMedia::getImage($model->photo,$model->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('customer'));
			$this->render("//tpl/submenu_tpl",array(
				'model'=>$model,		    
				'template_name'=>"driver_add",
				'widget'=>'WidgetDriverMenu',		
				'avatar'=>$avatar,
				'title'=>$driver_fullname,
				'params'=>array(  
				   'model'=>$model,
				   'upload_path'=>$upload_path,
				   'status'=>(array)AttributesTools::StatusManagement('customer'),	
				   'phone_default_country'=>$phone_default_country,
				   'phone_country_list'=>$phone_country_list,		
				   'hide_nav'=>true,		   
				   'salary_type'=>AttributesTools::DriverSalaryType(),
				   'employment_type'=>AttributesTools::DriverEmploymentType(),
				   'commission_type'=>AttributesTools::DriverCommissionType(),
				   'currency_list'=>$currency_list,
				   'multicurrency_enabled'=>$multicurrency_enabled,
				   'links'=>array(
						t("Driver")=>array('driver/list'),        
						$this->pageTitle,
					),									
				 )
			));
		} else {
			$this->render('//driver/driver_add',array(		  
			'model'=>$model,
			'status'=>(array)AttributesTools::StatusManagement('customer'),		  
			'upload_path'=>$upload_path,		
			'phone_default_country'=>$phone_default_country,
			'phone_country_list'=>$phone_country_list,
			'hide_nav'=>false,
			'salary_type'=>AttributesTools::DriverSalaryType(),
			'employment_type'=>AttributesTools::DriverEmploymentType(),
			'commission_type'=>AttributesTools::DriverCommissionType(),
			'currency_list'=>$currency_list,
			'multicurrency_enabled'=>$multicurrency_enabled,
			'links'=>array(
					t("Driver")=>array('driver/list'),        
					$this->pageTitle,
				)
			));
	    }
	}

	public function actionupdate()
	{
		$this->actionadd(true);
	}

	public function actionlicense()
	{
		$update = true;
		$this->pageTitle =  t("License");
		CommonUtility::setMenuActive('.delivery_driver','.driver_add');
		$upload_path = "upload/license";

		$id = Yii::app()->input->get('id');			
		$model = new AR_driver;
		if($update){
			$model = AR_driver::model()->find("driver_uuid=:driver_uuid",array(
			 ':driver_uuid'=>$id
			));
			if(!$model){
				$this->render('//tpl/error',array(
				 'error'=>array(
				   'message'=>t(Helper_not_found)
				 )
				));
				return ;
			}			
			$driver_fullname = "$model->first_name $model->last_name";
		}        
		
		if(isset($_POST['AR_driver'])){
			$model->attributes=$_POST['AR_driver'];	
						
			if(isset($_POST['license_front_photo'])){
				if(!empty($_POST['license_front_photo'])){
					$model->license_front_photo = $_POST['license_front_photo'];
					$model->path_license = isset($_POST['path'])?$_POST['path']:$upload_path;
				} else $model->license_front_photo = '';
			} else $model->license_front_photo = '';

			if(isset($_POST['license_back_photo'])){
				if(!empty($_POST['license_back_photo'])){
					$model->license_back_photo = $_POST['license_back_photo'];
					$model->path_license = isset($_POST['path'])?$_POST['path']:$upload_path;
				} else $model->license_back_photo = '';
			} else $model->license_back_photo = '';

			if($model->validate()){					
				if($model->save()){
					if(!$update){
					   $this->redirect(array(Yii::app()->controller->id."/list"));		
					} else {
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
						$this->refresh();
					}
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString( $model->getErrors(),"<br/>" ));
		}

		$avatar = CMedia::getImage($model->photo,$model->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('customer'));
		$this->render("//tpl/submenu_tpl",array(
			'model'=>$model,		    
			'template_name'=>"driver_license",
			'widget'=>'WidgetDriverMenu',		
			'avatar'=>$avatar,
			'title'=>$driver_fullname,
			'params'=>array(  
			   'model'=>$model,
			   'upload_path'=>$upload_path,
			   'status'=>(array)AttributesTools::StatusManagement('customer'),			   
			   'hide_nav'=>true,			   
			   'links'=>array(
					t("Driver")=>array('driver/list'),        
					$this->pageTitle,
				),					
			 )
		));
	}

	public function actiondelete()
	{
		$id = Yii::app()->input->get('id');							
		$model = AR_driver::model()->find("driver_uuid=:driver_uuid",['driver_uuid'=>$id]);
		if($model){			
			$model->delete(); 			
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array(Yii::app()->controller->id.'/list'));
		} else $this->render("/admin/error",array(
				 'error'=>array(
				   'message'=>t(HELPER_RECORD_NOT_FOUND)
				 )
				));		
	}

	public function actioncarlist()
	{
		$this->pageTitle=t("Car registration");		
				
		$table_col = array(		  
		  'vehicle_uuid'=>array(
		    'label'=>t(""),
		    'width'=>'15%'
		  ),		  
		  'vehicle_id'=>array(
		    'label'=>t("#"),
		    'width'=>'5%'
		  ),		  
		  'active'=>array(
		    'label'=>t(""),
		    'width'=>'10%'
		  ),		  
		  'plate_number'=>array(
		    'label'=>t("Plate #"),
		    'width'=>'20%'
		  ),		  
		  'vehicle_type_id'=>array(
		    'label'=>t("Type"),
		    'width'=>'20%'
		  ),		  
		  'maker'=>array(
		    'label'=>t("Maker"),
		    'width'=>'20%'
		  ),		  
		  'date_created'=>array(
		    'label'=>t("Actions"),
		    'width'=>'20%'
		  ),
		);
		$columns = array(
			array('data'=>'vehicle_uuid', 'visible'=>false),
			array('data'=>'vehicle_id', 'orderable'=>true,'visible'=>false),
			array('data'=>'active','orderable'=>true),			
			array('data'=>'plate_number','orderable'=>true),			
			array('data'=>'vehicle_type_id','orderable'=>true),						
			array('data'=>'maker','orderable'=>true),			
			array('data'=>null,'orderable'=>false,
			   'defaultContent'=>'
			   <div class="btn-group btn-group-actions" role="group">
				  <a class="ref_edit normal btn btn-light tool_tips"><i class="zmdi zmdi-border-color"></i></a>
				  <a class="ref_delete normal btn btn-light tool_tips"><i class="zmdi zmdi-delete"></i></a>
			   </div>
			   '
			),	  
		);		
		
		$this->render('//driver/car_list',array(
		  'table_col'=>$table_col,
		  'columns'=>$columns,
		  'order_col'=>1,
          'sortby'=>'desc',		  
          'ajax_url'=>Yii::app()->createUrl("/api"),
		  'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/addcar")
		));
	}

	public function actionupdate_car()
	{
		$this->actionaddcar(true);
	}

	public function actionaddcar($update=false)
	{
		$this->pageTitle=t("Add Car");
		CommonUtility::setMenuActive('.delivery_driver','.driver_carlist');

		$model = new AR_driver_vehicle();
		$item_gallery = array();

		$id = Yii::app()->input->get('id');					
		if($update){
			$model = AR_driver_vehicle::model()->find("vehicle_uuid=:vehicle_uuid",array(
			 ':vehicle_uuid'=>$id
			));
			if(!$model){
				$this->render('//tpl/error',array(
				 'error'=>array(
				   'message'=>t(Helper_not_found)
				 )
				));
				return ;
			}			
			
			$meta = AR_driver_meta::model()->findAll("merchant_id=:merchant_id AND reference_id=:reference_id  AND meta_name=:meta_name",
			array(
			':merchant_id'=>0,
			':reference_id'=>$model->vehicle_id,
			':meta_name'=>"car_documents"
			));
			if($meta){
				foreach ($meta as $item) {				
					$item_gallery[] = $item->meta_value1;
				}			
			}
		}        		

		if(isset($_POST['AR_driver_vehicle'])){			
			$model->attributes=$_POST['AR_driver_vehicle'];	
			if($model->validate()){		
				
				if(isset($_POST['photo'])){
					if(!empty($_POST['photo'])){														
						$model->photo = $_POST['photo'];
						$model->path = CMedia::adminFolder();
					} else $model->photo = '';
				} else $model->photo = '';

				if($model->save()){

					AR_driver_meta::model()->deleteAll('merchant_id=:merchant_id 
					AND reference_id=:reference_id
					AND meta_name=:meta_name', 
					array(
						':merchant_id' => 0,
						':reference_id'=>$model->vehicle_id,
						':meta_name'=>"car_documents",
					));
					
					if(isset($_POST['item_gallery'])){	
						$params = array();				
						foreach ($_POST['item_gallery'] as $key=> $items) {
							$params[]=array(							
							'meta_name'=>"car_documents",
							'reference_id'=>$model->vehicle_id,
							'meta_value1'=>$items,
							'meta_value2'=>CMedia::adminFolder()
							);				
						}								
						$builder=Yii::app()->db->schema->commandBuilder;
						$command=$builder->createMultipleInsertCommand('{{driver_meta}}',$params);
						$command->execute();	
					}
					
					if(!$update){
					   $this->redirect(array(Yii::app()->controller->id."/carlist"));		
					} else {
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
						$this->refresh();
					}
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString( $model->getErrors(),"<br/>" ));
		}
		
		$vehicle_maker = CommonUtility::getDataToDropDown("{{admin_meta}}","meta_id",'meta_value',"WHERE meta_name='vehicle_maker'","Order by meta_name");
		$vehicle_type = CommonUtility::getDataToDropDown("{{admin_meta}}","meta_id",'meta_value',"WHERE meta_name='vehicle_type'","Order by meta_name");		
	
		
		$upload_path = CMedia::adminFolder();

		$this->render('//driver/car_add',array(		  
			'model'=>$model,			
			'vehicle_maker'=>$vehicle_maker,
			'vehicle_type'=>$vehicle_type,
			'upload_path'=>$upload_path,
			'item_gallery'=>$item_gallery,	 
			'links'=>array(
				  t("Car registration")=>array('driver/carlist'),        
				  $this->pageTitle,
			  )
		  ));
	}

	public function actiondelete_car()
	{
		$id = Yii::app()->input->get('id');							
		$model = AR_driver_vehicle::model()->find("vehicle_uuid=:vehicle_uuid",['vehicle_uuid'=>$id]);
		if($model){			
			$model->delete(); 			
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array(Yii::app()->controller->id.'/carlist'));
		} else $this->render("/admin/error",array(
				 'error'=>array(
				   'message'=>t(HELPER_RECORD_NOT_FOUND)
				 )
				));		
	}

	public function actionschedule()
	{
		$this->pageTitle = t("Schedule");		
		
		$this->render('//driver/driver_schedule',[
			'links'=>array(
			    t("Driver")=>array('driver/list'),        
			    $this->pageTitle,
			)
		]);
	}

	public function actionschedule_bulk()
	{
		$this->pageTitle = t("Import schedule file");
		CommonUtility::setMenuActive('.delivery_driver',".driver_schedule");

		$model = new AR_driver_schedule;
		$model->scenario="csv_upload";

		if(isset($_POST['AR_driver_schedule'])){
			if(DEMO_MODE){			
				$this->render('//tpl/error',array(  
					  'error'=>array(
						'message'=>t("Modification not available in demo")
					  )
					));	
				return false;
			}

			$model->attributes=$_POST['AR_driver_schedule'];
			if ($model->validate()){						
				if (!ini_get("auto_detect_line_endings")) {
					ini_set("auto_detect_line_endings", '1');
				}
				$model->csv=CUploadedFile::getInstance($model,'csv');							
				$csv = Reader::createFromPath($model->csv->tempName, 'r');
				$csv->setHeaderOffset(0);
				$header = $csv->getHeader(); 
				$records = $csv->getRecords();
				$error= ''; $pass = 0; $data = [];
				$total_header = count($header);				
				if($total_header>5){
					foreach ($records as $key => $items) {										
						try {
							
							$zone_id = isset($items['zone_id'])?intval($items['zone_id']):0;
							$driver_id = isset($items['driver_id'])?intval($items['driver_id']):0;
							$vehicle_id = isset($items['vehicle_id'])?intval($items['vehicle_id']):0;						
							$time_start = isset($items['time_start'])?trim($items['time_start']):null;
							$time_end = isset($items['time_end'])?trim($items['time_end']):null;	

							$model = new AR_driver_schedule();
							$model->zone_id = $zone_id;
							$model->driver_id = $driver_id;
							$model->vehicle_id = $vehicle_id;
							$model->time_start = $time_start;
							$model->time_end = $time_end;

							if($model->validate()){
								if($model->save()){
									$pass++;
								} else {
									$error.= t("Error on line {line} : ",array('{line}'=>$key)). t("Saved failed");		    
									$error.="<br/>";
								}
							} else {
								$error.= t("Error on line {line} : ",array('{line}'=>$key)). CommonUtility::parseModelErrorToString($model->getErrors());
								$error.="<br/>";
							}
						} catch (Exception $e) {
							$error.= t("Error on line {line} : ",array('{line}'=>$key)). t($e->getMessage());		    
							$error.="<br/>";
						}		
					}
			    } else {
					$error.= "Invalid CSV data please fixed before uploading";
					$error.="<br/>";
				}
				
				if(!empty($error)){
					Yii::app()->user->setFlash('error', $error);				
				} else {
					Yii::app()->user->setFlash('success', t("{count} inserted succesfully",array('{count}'=>$pass) ) );
				}				
		    } else {
				Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString( $model->getErrors(),"<br/>" ));
			}			
		}

		$this->render("schedule_import",[
			'model'=>$model,
			'links'=>array(
				t("Driver"),
	            t("Schedule")=>array('driver/schedule'), 
	            $this->pageTitle,
		      ),	    		    
		]);
	}

	public function actionreview_list()
	{
		$this->pageTitle=t("Reviews");		
				
		$table_col = array(		  		  
		  'review_id'=>array(
		    'label'=>t("#"),
		    'width'=>'5%'
		  ),		  	
		  'driver_id'=>array(
		    'label'=>t("Driver"),
		    'width'=>'16%'
		  ),		  	  
		  'client_id'=>array(
		    'label'=>t("Customer"),
		    'width'=>'16%'
		  ),		  
		  'review_text'=>array(
		    'label'=>t("Review"),
		    'width'=>'23%'
		  ),		  
		  'rating'=>array(
		    'label'=>t("Rating"),
		    'width'=>'15%'
		  ),		  
		  'date_created'=>array(
		    'label'=>t("Actions"),
		    'width'=>'10%'
		  ),
		);
		$columns = array(			
			array('data'=>'review_id', 'orderable'=>true),
			array('data'=>'driver_id','orderable'=>true),			
			array('data'=>'client_id','orderable'=>true),			
			array('data'=>'review_text','orderable'=>true),			
			array('data'=>'rating','orderable'=>true),									
			array('data'=>null,'orderable'=>false,
			   'defaultContent'=>'
			   <div class="btn-group btn-group-actions" role="group">
				  <a class="ref_edit normal btn btn-light tool_tips"><i class="zmdi zmdi-border-color"></i></a>
				  <a class="ref_delete normal btn btn-light tool_tips"><i class="zmdi zmdi-delete"></i></a>
			   </div>
			   '
			),	  
		);		
		
		$this->render('//driver/review_list',array(
		  'table_col'=>$table_col,
		  'columns'=>$columns,
		  'order_col'=>0,
          'sortby'=>'desc',		  
          'ajax_url'=>Yii::app()->createUrl("/api"),
		  'link'=>''
		));
	}

	public function actionreview_update()
	{
		
		$this->pageTitle=t("Update review");
		CommonUtility::setMenuActive('.delivery_driver','.driver_review_list');

		
		$id = intval(Yii::app()->input->get('id'));	
		$model = AR_driver_reviews::model()->findByPk($id);
		if(!$model){
			$this->render('//tpl/error',array(
			'error'=>array(
				'message'=>t(Helper_not_found)
			)
			));
			return ;
		}			

		if(isset($_POST['AR_driver_reviews'])){			
			$model->attributes=$_POST['AR_driver_reviews'];	
			if($model->validate()){						
				if($model->save()){
					Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
					$this->refresh();
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString( $model->getErrors(),"<br/>" ));
		}
				
		$this->render('//driver/review_add',array(		  
			'model'=>$model,						
			'status_list'=>(array)AttributesTools::StatusManagement('post'),		  
			'links'=>array(
				  t("Reviews")=>array('driver/review_list'),        
				  $this->pageTitle,
			  )
		  ));
	}

	public function actionreview_delete()
	{
		$id = intval(Yii::app()->input->get('id'));									
		$model = AR_driver_reviews::model()->findByPk($id);
		if($model){			
			$model->delete(); 			
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array(Yii::app()->controller->id.'/review_list'));
		} else $this->render("/admin/error",array(
				 'error'=>array(
				   'message'=>t(HELPER_RECORD_NOT_FOUND)
				 )
				));		
	}

	private function includeJS()
	{
		
		AssetsFrontBundle::includeMaps();

		$option_data = OptionsTools::find(['firebase_apikey','firebase_domain','firebase_projectid','firebase_storagebucket','firebase_messagingid','firebase_appid']);
		$firebase_apikey = isset($option_data['firebase_apikey'])?$option_data['firebase_apikey']:'';
		$firebase_domain = isset($option_data['firebase_domain'])?$option_data['firebase_domain']:'';
		$firebase_projectid = isset($option_data['firebase_projectid'])?$option_data['firebase_projectid']:'';
		$firebase_storagebucket = isset($option_data['firebase_storagebucket'])?$option_data['firebase_storagebucket']:'';
		$firebase_messagingid = isset($option_data['firebase_messagingid'])?$option_data['firebase_messagingid']:'';
		$firebase_appid = isset($option_data['firebase_appid'])?$option_data['firebase_appid']:'';		

		$firebase_config = json_encode([
			'firebase_apikey'=>$firebase_apikey,
			'firebase_domain'=>$firebase_domain,
			'firebase_projectid'=>$firebase_projectid,
			'firebase_storagebucket'=>$firebase_storagebucket,
			'firebase_messagingid'=>$firebase_messagingid,
			'firebase_appid'=>$firebase_appid,
		]);

		ScriptUtility::registerScript(array(
		  "var firebase_configuration='".CJavaScript::quote($firebase_config)."';",		  
		),'firebase_configuration');

		$cs = Yii::app()->getClientScript();
		Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl."/assets/js/task.js?time=".time(),CClientScript::POS_END,[
			'type'=>"module"
		]);
	}

	public function actionmapview()
	{
		$this->layout = 'backend_full';		
		$this->pageTitle = t("Map view");
		
		$uunassigned_group = AOrders::getOrderTabsStatus('unassigned');
		$assigned_group = AOrders::getOrderTabsStatus('assigned');
		$completed_group = AOrders::getOrderTabsStatus('completed');

		$status_merge = array_merge((array)$uunassigned_group, (array) $assigned_group, (array) $completed_group);		
			
		$maps_config = CMaps::config();	
		$provider = isset($maps_config['provider'])?$maps_config['provider']:'';				
		if($provider=="google.maps"){
			$include  = array(				
				Yii::app()->baseUrl."/assets/vendor/markerclusterer.js"				
			);
			ScriptUtility::registerJS($include);
		}
		$this->includeJS();		
				
		$this->render('//driver/map_view',[
			'ajax_url'=>Yii::app()->createUrl("/api"),
			'task_url'=>Yii::app()->createUrl("/apitask"),
			'apibackend'=>Yii::app()->createUrl("/apibackend"),
			'links'=>array(
			    t("Map")=>array('driver/mapview'),        
			    t("view"),
			),
			'uunassigned_group'=>$uunassigned_group,
			'assigned_group'=>$assigned_group,
			'completed_group'=>$completed_group,
			'status_merge'=>$status_merge,
			'maps_config'=>$maps_config
		]);
	}

	public function actiondelivery_transactions()
	{
		  $this->pageTitle = t("Delivery transactions");
		  CommonUtility::setMenuActive('.delivery_driver','.driver_list');

		  $id = trim(Yii::app()->input->get('id'));		  
		  $model = AR_driver::model()->find("driver_uuid=:driver_uuid",array(
			':driver_uuid'=>$id
		   ));
		   if(!$model){
			   $this->render('//tpl/error',array(
				'error'=>array(
				  'message'=>t(Helper_not_found)
				)
			   ));
			   return ;
		   }
		   
		   $avatar = CMedia::getImage($model->photo,$model->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('customer'));
		   $driver_fullname = "$model->first_name $model->last_name";

		   $table_col = array(		  		  
			'order_id'=>array(
			  'label'=>t("Order ID"),
			  'width'=>'15%'
			),		  				
			'merchant_id'=>array(
			  'label'=>t("Merchant"),
			  'width'=>'20%'
			),		  
			'client_id'=>array(
			  'label'=>t("Customer"),
			  'width'=>'20%'
			),		  
			'total'=>array(
			  'label'=>t("Total"),
			  'width'=>'10%'
			),
		  );
		  $columns = array(			
			  array('data'=>'order_id', 'orderable'=>true),			  
			  array('data'=>'merchant_id','orderable'=>true),			
			  array('data'=>'client_id','orderable'=>true),									
			  array('data'=>'total','orderable'=>true),				  
		  );				  

		   $this->render("//tpl/submenu_tpl",array(
				'model'=>$model,		    
				'template_name'=>"delivery_transactions",
				'widget'=>'WidgetDriverMenu',		
				'avatar'=>$avatar,
				'title'=>$driver_fullname,
				'params'=>array(  
				   'model'=>$model,				   
				   'hide_nav'=>true,		   
				   'links'=>array(
						t("Driver")=>array('driver/list'),        
						$this->pageTitle,
					),
					'table_col'=>$table_col,
					'columns'=>$columns,
					'order_col'=>1,
					'sortby'=>'desc',		  
					'ajax_url'=>Yii::app()->createUrl("/api"),
					'driver_uuid'=>$id,
				 )
			));
	}

	public function actionorder_tips()
	{
		  $this->pageTitle = t("Order Tips");
		  CommonUtility::setMenuActive('.delivery_driver','.driver_list');

		  $id = trim(Yii::app()->input->get('id'));		  
		  $model = AR_driver::model()->find("driver_uuid=:driver_uuid",array(
			':driver_uuid'=>$id
		   ));
		   if(!$model){
			   $this->render('//tpl/error',array(
				'error'=>array(
				  'message'=>t(Helper_not_found)
				)
			   ));
			   return ;
		   }
		   
		   $avatar = CMedia::getImage($model->photo,$model->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('customer'));
		   $driver_fullname = "$model->first_name $model->last_name";

		   $table_col = array(		  		  
			'order_id'=>array(
			  'label'=>t("Order ID"),
			  'width'=>'15%'
			),		  				
			'merchant_id'=>array(
			  'label'=>t("Merchant"),
			  'width'=>'20%'
			),		  
			'client_id'=>array(
			  'label'=>t("Customer"),
			  'width'=>'20%'
			),		  
			'total'=>array(
			  'label'=>t("Tips"),
			  'width'=>'10%'
			),
		  );
		  $columns = array(			
			  array('data'=>'order_id', 'orderable'=>true),			  
			  array('data'=>'merchant_id','orderable'=>true),			
			  array('data'=>'client_id','orderable'=>true),									
			  array('data'=>'courier_tip','orderable'=>true),				  
		  );				  

		   $this->render("//tpl/submenu_tpl",array(
				'model'=>$model,		    
				'template_name'=>"order_tips",
				'widget'=>'WidgetDriverMenu',		
				'avatar'=>$avatar,
				'title'=>$driver_fullname,
				'params'=>array(  
				   'model'=>$model,				   
				   'hide_nav'=>true,		   
				   'links'=>array(
						t("Driver")=>array('driver/list'),        
						$this->pageTitle,
					),
					'table_col'=>$table_col,
					'columns'=>$columns,
					'order_col'=>1,
					'sortby'=>'desc',		  
					'ajax_url'=>Yii::app()->createUrl("/api"),
					'driver_uuid'=>$id,
				 )
			));
	}

	public function actiontime_logs()
	{
		  $this->pageTitle = t("Time logs");
		  CommonUtility::setMenuActive('.delivery_driver','.driver_list');

		  $id = trim(Yii::app()->input->get('id'));		  
		  $model = AR_driver::model()->find("driver_uuid=:driver_uuid",array(
			':driver_uuid'=>$id
		   ));
		   if(!$model){
			   $this->render('//tpl/error',array(
				'error'=>array(
				  'message'=>t(Helper_not_found)
				)
			   ));
			   return ;
		   }

		   $table_col = array(		  
			'schedule_id'=>array(
			  'label'=>t(""),
			  'width'=>'15%'
			),		  						
			'zone_id'=>array(
				'label'=>t("Zone"),
				'width'=>'15%'
			),		  
			'date_created'=>array(
				'label'=>t("Date"),
				'width'=>'15%'
			  ),		  
			'time_start'=>array(
				'label'=>t("Shift start"),
				'width'=>'10%'
			),
			'time_end'=>array(
				'label'=>t("Shift end"),
				'width'=>'10%'
			),
			'shift_time_started'=>array(
				'label'=>t("Time in"),
				'width'=>'10%'
			),
			'shift_time_ended'=>array(
				'label'=>t("Time out"),
				'width'=>'10%'
			),
			'date_modified'=>array(
				'label'=>t("Actions"),
				'width'=>'10%'
			),
		  );
		  $columns = array(
			  array('data'=>'schedule_id', 'visible'=>false),
			  array('data'=>'zone_id'),			  
			  array('data'=>'date_created'),			  
			  array('data'=>'time_start'),
			  array('data'=>'time_end'),			  
			  array('data'=>'shift_time_started'),
			  array('data'=>'shift_time_ended'),
			  array('data'=>'date_modified','orderable'=>false),
		  );	
		   
		   $avatar = CMedia::getImage($model->photo,$model->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('customer'));
		   $driver_fullname = "$model->first_name $model->last_name";

		   $this->render("//tpl/submenu_tpl",array(
				'model'=>$model,		    
				'template_name'=>"time_logs",
				'widget'=>'WidgetDriverMenu',		
				'avatar'=>$avatar,
				'title'=>$driver_fullname,
				'params'=>array(  
				   'model'=>$model,				   
				   'hide_nav'=>true,		   
				   'links'=>array(
						t("Driver")=>array('driver/list'),        
						$this->pageTitle,
					),
					'table_col'=>$table_col,
					'columns'=>$columns,
					'order_col'=>2,
					'sortby'=>'desc',		  
					'ajax_url'=>Yii::app()->createUrl("/api"),
					'driver_uuid'=>$id
				 )
			));
	}

	public function actionreview_ratings()
	{
		  $this->pageTitle = t("Reviews");
		  CommonUtility::setMenuActive('.delivery_driver','.driver_list');

		  $id = trim(Yii::app()->input->get('id'));		  
		  $model = AR_driver::model()->find("driver_uuid=:driver_uuid",array(
			':driver_uuid'=>$id
		   ));
		   if(!$model){
			   $this->render('//tpl/error',array(
				'error'=>array(
				  'message'=>t(Helper_not_found)
				)
			   ));
			   return ;
		   }
		   
		   $avatar = CMedia::getImage($model->photo,$model->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('customer'));
		   $driver_fullname = "$model->first_name $model->last_name";

		   $table_col = array(		  		  
			'review_id'=>array(
			  'label'=>t("#"),
			  'width'=>'5%'
			),		  				
			'review_text'=>array(
			  'label'=>t("Review"),
			  'width'=>'23%'
			),		  
			'rating'=>array(
			  'label'=>t("Rating"),
			  'width'=>'15%'
			),		  
			'date_created'=>array(
			  'label'=>t("Actions"),
			  'width'=>'10%'
			),
		  );
		  $columns = array(			
			  array('data'=>'review_id', 'orderable'=>true),			  
			  array('data'=>'review_text','orderable'=>true),			
			  array('data'=>'rating','orderable'=>true),									
			  array('data'=>null,'orderable'=>false,
				 'defaultContent'=>'
				 <div class="btn-group btn-group-actions" role="group">
					<a class="ref_edit normal btn btn-light tool_tips"><i class="zmdi zmdi-border-color"></i></a>
					<a class="ref_delete normal btn btn-light tool_tips"><i class="zmdi zmdi-delete"></i></a>
				 </div>
				 '
			  ),	  
		  );				  

		   $this->render("//tpl/submenu_tpl",array(
				'model'=>$model,		    
				'template_name'=>"driver_review",
				'widget'=>'WidgetDriverMenu',		
				'avatar'=>$avatar,
				'title'=>$driver_fullname,
				'params'=>array(  
				   'model'=>$model,				   
				   'hide_nav'=>true,		   
				   'links'=>array(
						t("Driver")=>array('driver/list'),        
						$this->pageTitle,
					),
					'table_col'=>$table_col,
					'columns'=>$columns,
					'order_col'=>0,
					'sortby'=>'desc',		  
					'ajax_url'=>Yii::app()->createUrl("/api"),
					'driver_uuid'=>$id,
				 )
			));
	}

	public function actionoverview()
	{
		  $this->pageTitle = t("Driver Overview");
		  CommonUtility::setMenuActive('.delivery_driver','.driver_list');

		  $id = trim(Yii::app()->input->get('id'));		  
		  $model = AR_driver::model()->find("driver_uuid=:driver_uuid",array(
			':driver_uuid'=>$id
		   ));
		   if(!$model){
			   $this->render('//tpl/error',array(
				'error'=>array(
				  'message'=>t(Helper_not_found)
				)
			   ));
			   return ;
		   }
		   
		   $avatar = CMedia::getImage($model->photo,$model->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('customer'));
		   $driver_fullname = "$model->first_name $model->last_name";
		   $this->render("//tpl/submenu_tpl",array(
				'model'=>$model,		    
				'template_name'=>"driver_overview",
				'widget'=>'WidgetDriverMenu',		
				'avatar'=>$avatar,
				'title'=>$driver_fullname,
				'params'=>array(  
				   'model'=>$model,				   
				   'hide_nav'=>true,		   
				   'links'=>array(
						t("Driver")=>array('driver/list'),        
						$this->pageTitle,
					 ),
					'driver_uuid'=>$id, 					
					'ajax_url'=>Yii::app()->createUrl("/api")
				 )
			));
	}

	public function actiongroup()
	{
		$this->pageTitle=t("Groups");		
				
		$table_col = array(		  		  
		  'group_uuid'=>array(
		 	 'label'=>t(""),
			 'width'=>'5%'
		  ),		  	
		  'group_name'=>array(
		    'label'=>t("Name"),
		    'width'=>'20%'
		  ),		  	
		  'drivers'=>array(
		    'label'=>t("Drivers"),
		    'width'=>'20%'
		  ),		  	  		  
		  'date_created'=>array(
		    'label'=>t("Actions"),
		    'width'=>'10%'
		  ),
		);
		$columns = array(			
			array('data'=>'group_uuid', 'visible'=>false),
			array('data'=>'group_name','orderable'=>true),			
			array('data'=>'drivers','orderable'=>false),					
			array('data'=>null,'orderable'=>false,
			   'defaultContent'=>'
			   <div class="btn-group btn-group-actions" role="group">
				  <a class="ref_edit normal btn btn-light tool_tips"><i class="zmdi zmdi-border-color"></i></a>
				  <a class="ref_delete normal btn btn-light tool_tips"><i class="zmdi zmdi-delete"></i></a>
			   </div>
			   '
			),	  
		);		
		
		$this->render('//driver/group_list',array(
		  'table_col'=>$table_col,
		  'columns'=>$columns,
		  'order_col'=>1,
          'sortby'=>'desc',		  
          'ajax_url'=>Yii::app()->createUrl("/api"),
		  'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/addgroup")
		));
	}	

	public function actionaddgroup($update=false)
	{			
		$this->pageTitle = t("Add Group");
		CommonUtility::setMenuActive('.delivery_driver','.driver_group');

		$id = Yii::app()->input->get('id');
		$model = new AR_driver_group();		
		if($update){
			$model = AR_driver_group::model()->find("group_uuid=:group_uuid",array(
			 ':group_uuid'=>$id
			));
			if(!$model){
				$this->render('//tpl/error',array(
				 'error'=>array(
				   'message'=>t(Helper_not_found)
				 )
				));
				return ;
			}						
			$model->drivers = CDriver::GetGroups($model->group_id);
		}  

		if(isset($_POST['AR_driver_group'])){
			$model->attributes=$_POST['AR_driver_group'];	
			if($model->validate()){						
				if($model->save()){
					if(!$update){
					   $this->redirect(array(Yii::app()->controller->id."/group"));		
					} else {
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
						$this->refresh();
					}
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString( $model->getErrors(),"<br/>" ));
		}

		$drivers = CommonUtility::getDataToDropDown("{{driver}}",'driver_id',"concat(first_name,' ',last_name)",
		"WHERE status='active' AND merchant_id=0","ORDER BY first_name");		
		
		$this->render('//driver/group_add',[
			'model'=>$model,
			'drivers'=>$drivers,
			'links'=>array(
			    t("Groups")=>array('driver/group'),        
			    $this->pageTitle,
			)
		]);
	}

	public function actiongroup_update()
	{
		$this->actionaddgroup(true);
	}

	public function actiongroup_delete()
	{
		$id = Yii::app()->input->get('id');							
		$model = AR_driver_group::model()->find("group_uuid=:group_uuid",['group_uuid'=>$id]);
		if($model){			
			$model->delete(); 			
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array(Yii::app()->controller->id.'/group'));
		} else $this->render("/admin/error",array(
				 'error'=>array(
				   'message'=>t(HELPER_RECORD_NOT_FOUND)
				 )
				));		
	}

	public function actionorders()
	{
		$this->layout = 'backend_full';		
		$this->pageTitle = t("Orders List");

		$delivery_status = [];
		if($data = AttributesTools::getOrderStatus(Yii::app()->language,'delivery_status')){
			$delivery_status = CommonUtility::ArrayToLabelValue($data);
		}		

		$this->includeJS();			
		
		$this->render('//driver/orders_list',array(
			'ajax_url'=>Yii::app()->createUrl("/api"),
			'task_url'=>Yii::app()->createUrl("/apitask"),
			'apibackend'=>Yii::app()->createUrl("/apibackend"),
			'delivery_status'=>$delivery_status
		));
	}

	public function actionsettings_page()
	{
		$this->pageTitle = t("Settings");		
		CommonUtility::setMenuActive('.delivery_driver','.driver_settings');	

		$model = new AR_admin_meta;
		$save_post = [
			'page_driver_privacy','page_driver_terms_condition','page_driver_about_us'
		];

		if(isset($_POST['AR_admin_meta'])){
			if(DEMO_MODE){			
			    $this->render('//tpl/error',array(  
			          'error'=>array(
			            'message'=>t("Modification not available in demo")
			          )
			        ));	
			    return false;
			}
					
			$post = $_POST['AR_admin_meta'];						
			
			foreach ($save_post as $items) {				  
				 $model->saveMeta($items, isset($post[$items])?$post[$items]['meta_value']:'');			 
			}															
			
			Yii::app()->user->setFlash('success',CommonUtility::t(Helper_success));
			$this->refresh();			
		} else {
			$criteria = new CDbCriteria;
			$criteria->addInCondition('meta_name',(array) $save_post );
			$find = AR_admin_meta::model()->findAll($criteria);		
			if($find){
				foreach ($find as $items) {										
					$model[$items->meta_name] = [
						'meta_value'=>$items->meta_value						
					];
				}
			}	
		}		

		$fields = [];
		$fields['page_driver_privacy']['meta_value'] = t("Privacy Policy Page");
		$fields['page_driver_terms_condition']['meta_value'] = t("Terms and condition");
		$fields['page_driver_about_us']['meta_value'] = t("About Us");

		$page_list = CommonUtility::getDataToDropDown("{{pages}}",'page_id','title',"Where owner='admin' AND status='publish'");
		
		$this->render("//tpl/submenu_tpl",array(
		    'model'=>$model,
			'template_name'=>"//driver/settings_page",
			'widget'=>'WidgetDriverSettings',					
			'params'=>array(  
			   'model'=>$model,		
			   'fields'=>$fields,	
			   'page_list'=>$page_list,
			   'links'=>array(		 
			      t("Settings")=>array('driver/settings'),        
                  $this->pageTitle,                           
			   ),
			 )
		));						
	}

	public function actioncronjobs()
	{		
		$this->pageTitle = t("Cron jobs");		
		CommonUtility::setMenuActive('.delivery_driver','.driver_settings');	

		$cron_key = CommonUtility::getCronKey();		
		$params = ['key'=>$cron_key];

		$cron_link = [
			[
				'link'=> "curl ".CommonUtility::getHomebaseUrl()."/task/assignorder?".http_build_query($params)." >/dev/null 2>&1",
				'label'=>t("run every (2)minutes")
			],
			[
				'link'=> "curl ".CommonUtility::getHomebaseUrl()."/task/runcron?".http_build_query($params)." >/dev/null 2>&1",
				'label'=>t("run every (1)minute")
			],
			[
				'link'=> "curl ".CommonUtility::getHomebaseUrl()."/task/riderearningsrequery?".http_build_query($params) ." >/dev/null 2>&1 ",
				'label'=>t("run every (5)minutes")
			]
		];

		$this->render("//tpl/submenu_tpl",array(		    
			'template_name'=>"//driver/cronjobs",
			'widget'=>'WidgetDriverSettings',					
			'params'=>array(  			
			   'links'=>array(		 
			      t("Settings")=>array('driver/settings'),        
                  $this->pageTitle,                           
			   ),
			   'cron_link'=>$cron_link
			 )
		));					
	}

	public function actionshift_list()
	{
		$this->pageTitle=t("Shift Schedule");		
				
		$table_col = array(		  		  
			'shift_uuid'=>array(
				'label'=>t(""),
			   'width'=>'5%'
			),		  	
		  'shift_id'=>array(
			 'label'=>t(""),
			 'width'=>'5%'
		  ),		  			  		  
		  'zone_id'=>array(
		    'label'=>t("Zone"),
		    'width'=>'20%'
		  ),		  			  
		  'time_start'=>array(
		    'label'=>t("Shift Start"),
		    'width'=>'15%'
		  ),		  	  		  
		  'time_end'=>array(
		    'label'=>t("Shift Ends"),
		    'width'=>'15%'
		  ),		  	  		  
		  'max_allow_slot'=>array(
		    'label'=>t("Slot"),
		    'width'=>'15%'
		  ),		  	  		  
		  'date_created'=>array(
		    'label'=>t("Actions"),
		    'width'=>'10%'
		  ),
		);
		$columns = array(			
			array('data'=>'shift_uuid', 'visible'=>false),
			array('data'=>'shift_id', 'visible'=>false),						
			array('data'=>'zone_id','orderable'=>true),						
			array('data'=>'time_start','orderable'=>true),					
			array('data'=>'time_end','orderable'=>true),					
			array('data'=>'max_allow_slot','orderable'=>true),	
			array('data'=>null,'orderable'=>false,
			   'defaultContent'=>'
			   <div class="btn-group btn-group-actions" role="group">
				  <a class="ref_edit normal btn btn-light tool_tips"><i class="zmdi zmdi-border-color"></i></a>
				  <a class="ref_delete normal btn btn-light tool_tips"><i class="zmdi zmdi-delete"></i></a>
			   </div>
			   '
			),	  
		);		
		
		$this->render('//driver/shift_list',array(
		  'table_col'=>$table_col,
		  'columns'=>$columns,
		  'order_col'=>1,
          'sortby'=>'desc',		  
          'ajax_url'=>Yii::app()->createUrl("/api"),
		  'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/addshift")
		));
	}

	public function actionaddshift($update=false)
	{
		$this->pageTitle = t("Add Shift");
		CommonUtility::setMenuActive('.delivery_driver','.driver_shift_list');

		$date_now = date("Y-m-d"); 

		$id = Yii::app()->input->get('id');
		$model = new AR_driver_shift_schedule();		
		if($update){
			$model = AR_driver_shift_schedule::model()->find("shift_uuid=:shift_uuid",array(
			 ':shift_uuid'=>$id
			));
			if(!$model){
				$this->render('//tpl/error',array(
				 'error'=>array(
				   'message'=>t(Helper_not_found)
				 )
				));
				return ;
			}				
			
			$model->date_shift = Date_Formatter::date($model->time_start,"yyyy-MM-dd",true);
			$model->date_shift_end = Date_Formatter::date($model->time_end,"yyyy-MM-dd",true);			
			$model->time_start = Date_Formatter::Time($model->time_start,"HH:mm:ss",true);			
			$model->time_end = Date_Formatter::Time($model->time_end,"HH:mm:ss",true);			
		} else {
			$model->date_shift = $date_now;
			$model->date_shift_end =  $date_now;
		}

		if(isset($_POST['AR_driver_shift_schedule'])){
			$model->attributes=$_POST['AR_driver_shift_schedule'];				
			if($model->validate()){							
				if($model->save()){
					if(!$update){
					   $this->redirect(array(Yii::app()->controller->id."/shift_list"));		
					} else {
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
						$this->refresh();
					}
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString( $model->getErrors(),"<br/>" ));
		}
		
		$zone_list = CommonUtility::getDataToDropDown("{{zones}}",'zone_id','zone_name',"
		WHERE merchant_id = 0","ORDER BY zone_name ASC");

		$this->render('//driver/addshift',[
			'model'=>$model,			
			'zone_list'=>$zone_list,
			'time_range'=>AttributesTools::createTimeRange("00:00","24:00"),
			'status'=>(array)AttributesTools::StatusManagement('customer'),		  
			'links'=>array(
			    t("Shifts Schedule")=>array('driver/shift_list'),        
			    $this->pageTitle,
			)
		]);

	}

	public function actionshift_update()
	{
		$this->actionaddshift(true);
	}

	public function actionshift_delete()
	{
		$id = trim(Yii::app()->input->get('id'));							
		$model = AR_driver_shift_schedule::model()->find("shift_uuid=:shift_uuid",[
			':shift_uuid'=>$id
		]);
		if($model){			
			$model->delete(); 			
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array(Yii::app()->controller->id.'/shift_list'));
		} else $this->render("/admin/error",array(
				 'error'=>array(
				   'message'=>t(HELPER_RECORD_NOT_FOUND)
				 )
				));		
	}

	public function actionshift_bulkupload()
	{
		$this->pageTitle = t("Import schedule file");
		CommonUtility::setMenuActive('.delivery_driver',".driver_shift_list");

		$model = new AR_driver_shift_schedule;
		$model->scenario="csv_upload";
		if(isset($_POST['AR_driver_shift_schedule'])){
			if(DEMO_MODE){			
				$this->render('//tpl/error',array(  
					  'error'=>array(
						'message'=>t("Modification not available in demo")
					  )
					));	
				return false;
			}
			$model->attributes=$_POST['AR_driver_shift_schedule'];
			if ($model->validate()){
				if (!ini_get("auto_detect_line_endings")) {
					ini_set("auto_detect_line_endings", '1');
				}
				$model->csv=CUploadedFile::getInstance($model,'csv');							
				$csv = Reader::createFromPath($model->csv->tempName, 'r');
				$csv->setHeaderOffset(0);
				$header = $csv->getHeader(); 
				$records = $csv->getRecords();
				$error= ''; $pass = 0; $data = [];
				$total_header = count($header);
				if($total_header>3){
					foreach ($records as $key => $items) {
						try {

							$zone_id = isset($items['zone_id'])?intval($items['zone_id']):0;
							$time_start = isset($items['time_start'])?trim($items['time_start']):null;
							$time_end = isset($items['time_end'])?trim($items['time_end']):null;
							$max_allow_slot = isset($items['max_allow_slot'])?intval($items['max_allow_slot']):0;
							
							$model = new AR_driver_shift_schedule();
							$model->zone_id = $zone_id;
							$model->time_start = date("H:i:s",strtotime($time_start));
							$model->time_end = date("H:i:s",strtotime($time_end));
							$model->max_allow_slot = $max_allow_slot;
							$model->date_shift = Date_Formatter::date($model->time_start,"yyyy-MM-dd",true);
			                $model->date_shift_end = Date_Formatter::date($model->time_end,"yyyy-MM-dd",true);			
														
							if($model->validate()){								
								if($model->save()){
									$pass++;
								} else {
									$error.= t("Error on line {line} : ",array('{line}'=>$key)). t("Saved failed");		    
									$error.="<br/>";
								}
							} else {								
								$error.= t("Error on line {line} : ",array('{line}'=>$key)). CommonUtility::parseModelErrorToString($model->getErrors());
								$error.="<br/>";
							}							
						} catch (Exception $e) {
							$error.= t("Error on line {line} : ",array('{line}'=>$key)). t($e->getMessage());		    
							$error.="<br/>";
						}		
					}
				} else {
					$error.= "Invalid CSV data please fixed before uploading";
					$error.="<br/>";
				}				

				if(!empty($error)){
					Yii::app()->user->setFlash('error', $error);				
				} else {
					Yii::app()->user->setFlash('success', t("{count} inserted succesfully",array('{count}'=>$pass) ) );
				}
			} else {				
				Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString( $model->getErrors(),"<br/>" ));
			}
		}

		$this->render("shift_bulkupload",[
			'model'=>$model,
			'links'=>array(
				t("Driver"),
	            t("Shift Schedule")=>array('driver/shift_list'), 
	            $this->pageTitle,
		      ),	    		    
		]);
	}

	public function actionvehicle()
	{
		$this->pageTitle = t("Vehicle");
		CommonUtility::setMenuActive('.delivery_driver','.driver_list');

		$id = trim(Yii::app()->input->get('id'));		  
		$model = AR_driver::model()->find("driver_uuid=:driver_uuid",array(
		':driver_uuid'=>$id
		));
		if(!$model){
			$this->render('//tpl/error',array(
			'error'=>array(
				'message'=>t(Helper_not_found)
			)
			));
			return ;
		}

		$item_gallery = array(); $driver_fullname = '';
		$driver_id = $model->driver_id;
		$driver_fullname = "$model->first_name $model->last_name";

		$avatar = CMedia::getImage($model->photo,$model->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('customer'));		
		$model = AR_driver_vehicle::model()->find("driver_id=:driver_id",[
			':driver_id'=>$driver_id
		]);
		if($model){
			$meta = AR_driver_meta::model()->findAll("merchant_id=:merchant_id AND reference_id=:reference_id  AND meta_name=:meta_name",
			array(
			':merchant_id'=>0,
			':reference_id'=>$model->vehicle_id,
			':meta_name'=>"car_documents"
			));
			if($meta){
				foreach ($meta as $item) {				
					$item_gallery[] = $item->meta_value1;
				}			
			}			
		} else $model = new AR_driver_vehicle();

		if(isset($_POST['AR_driver_vehicle'])){
			$model->attributes=$_POST['AR_driver_vehicle'];	
			$model->driver_id = intval($driver_id);
			if($model->validate()){

				if(isset($_POST['photo'])){
					if(!empty($_POST['photo'])){														
						$model->photo = $_POST['photo'];
						$model->path = CMedia::adminFolder();
					} else $model->photo = '';
				} else $model->photo = '';

				if($model->save()){

					AR_driver_meta::model()->deleteAll('merchant_id=:merchant_id 
					AND reference_id=:reference_id
					AND meta_name=:meta_name', 
					array(
						':merchant_id' => 0,
						':reference_id'=>$model->vehicle_id,
						':meta_name'=>"car_documents",
					));

					if(isset($_POST['item_gallery'])){
						$params = array();				
						foreach ($_POST['item_gallery'] as $key=> $items) {
							$params[]=array(							
							'meta_name'=>"car_documents",
							'reference_id'=>$model->vehicle_id,
							'meta_value1'=>$items,
							'meta_value2'=>CMedia::adminFolder()
							);				
						}								
						$builder=Yii::app()->db->schema->commandBuilder;
						$command=$builder->createMultipleInsertCommand('{{driver_meta}}',$params);
						$command->execute();	
					}

					Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
					$this->refresh();

				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString( $model->getErrors(),"<br/>" ));
		}

		$vehicle_maker = CommonUtility::getDataToDropDown("{{admin_meta}}","meta_id",'meta_value',"WHERE meta_name='vehicle_maker'","Order by meta_name");
		$vehicle_type = CommonUtility::getDataToDropDown("{{admin_meta}}","meta_id",'meta_value',"WHERE meta_name='vehicle_type'","Order by meta_name");		
		$upload_path = CMedia::adminFolder();
		
		$this->render("//tpl/submenu_tpl",array(
			'model'=>$model,		    
			'template_name'=>"car_add_with_driver",
			'widget'=>'WidgetDriverMenu',		
			'avatar'=>$avatar,
			'title'=>$driver_fullname,
			'params'=>array(  
			   'model'=>$model,				   
			   'hide_nav'=>true,		   
			   'vehicle_maker'=>$vehicle_maker,
			   'vehicle_type'=>$vehicle_type,
			   'upload_path'=>$upload_path,
			   'item_gallery'=>$item_gallery,	 
			   'links'=>array(
					t("Driver")=>array('driver/list'),        
					$this->pageTitle,
				),				
			 )
		));
	}

	public function actionvehicle_delete()
	{
		$id = trim(Yii::app()->input->get('id'));							
		$model = AR_driver_vehicle::model()->find("vehicle_uuid=:vehicle_uuid",[
			':vehicle_uuid'=>$id
		]);
		if($model){						
			$driver_data = CDriver::getDriver($model->driver_id);			
			$model->delete(); 			
			$this->redirect(array(Yii::app()->controller->id.'/vehicle','id'=>$driver_data->driver_uuid ));
		} else $this->render("/admin/error",array(
				 'error'=>array(
				   'message'=>t(HELPER_RECORD_NOT_FOUND)
				 )
				));		
	}

	public function actionwallet()
	{
		$this->pageTitle = t("Wallet");
		CommonUtility::setMenuActive('.delivery_driver','.driver_list');

		$id = trim(Yii::app()->input->get('id'));		  
		$model = AR_driver::model()->find("driver_uuid=:driver_uuid",array(
			':driver_uuid'=>$id
		   ));
		   if(!$model){
			   $this->render('//tpl/error',array(
				'error'=>array(
				  'message'=>t(Helper_not_found)
				)
			   ));
			   return ;
		}		   
		$avatar = CMedia::getImage($model->photo,$model->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('customer'));
		$driver_fullname = "$model->first_name $model->last_name";

		$table_col = array(
			'transaction_date'=>array(
			  'label'=>t("Date"),
			  'width'=>'15%'
			),
			'transaction_description'=>array(
			  'label'=>t("Transaction"),
			  'width'=>'30%'
			),
			'transaction_amount'=>array(
			  'label'=>t("Debit/Credit"),
			  'width'=>'20%'
			),
			'running_balance'=>array(
			  'label'=>t("Running Balance"),
			  'width'=>'20%'
			),
		);
		$columns = array(
			array('data'=>'transaction_date'),
			array('data'=>'transaction_description'),
			array('data'=>'transaction_amount'),
			array('data'=>'running_balance'),
		);				

		$transaction_type = AttributesTools::transactionTypeList(true);

		$this->render("//tpl/submenu_tpl",array(
			'model'=>$model,		    
			'template_name'=>"wallet_transaction",
			'widget'=>'WidgetDriverMenu',		
			'avatar'=>$avatar,
			'title'=>$driver_fullname,
			'params'=>array(  
			   'model'=>$model,				   
			   'hide_nav'=>true,		   
			   'links'=>array(
					t("Driver")=>array('driver/list'),        
					$this->pageTitle,
				),
				'table_col'=>$table_col,
				'columns'=>$columns,
				'order_col'=>1,
				'sortby'=>'desc',		  
				'ajax_url'=>Yii::app()->createUrl("/api"),
				'driver_uuid'=>$id,
				'transaction_type'=>$transaction_type,
			 )
		));		  
	}

	public function actionbank_info()
	{
		$this->pageTitle = t("Bank information");
		CommonUtility::setMenuActive('.delivery_driver','.driver_list');

		$id = trim(Yii::app()->input->get('id'));		  
		$model = AR_driver::model()->find("driver_uuid=:driver_uuid",array(
			':driver_uuid'=>$id
		   ));
		   if(!$model){
			   $this->render('//tpl/error',array(
				'error'=>array(
				  'message'=>t(Helper_not_found)
				)
			   ));
			   return ;
		}		   
		$avatar = CMedia::getImage($model->photo,$model->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('customer'));
		$driver_id = $model->driver_id;
		$driver_fullname = "$model->first_name $model->last_name";

		$model = AR_driver_meta::model()->find("reference_id=:reference_id AND meta_name=:meta_name",[
			':reference_id'=>$driver_id,
			':meta_name'=>'bank_information'
		]);
		if($model){
			$data = !empty($model->meta_value1)?json_decode($model->meta_value1,true):'';
			$model->account_name = isset($data['account_name'])?$data['account_name']:'';
			$model->account_number_iban = isset($data['account_number_iban'])?$data['account_number_iban']:'';
			$model->swift_code = isset($data['swift_code'])?$data['swift_code']:'';
			$model->bank_name = isset($data['bank_name'])?$data['bank_name']:'';
			$model->bank_branch = isset($data['bank_branch'])?$data['bank_branch']:'';
		} else $model = new AR_driver_meta();

		$model->scenario = 'bank_information';

		if(isset($_POST['AR_driver_meta'])){
			$model->attributes=$_POST['AR_driver_meta'];	
			$model->reference_id = $driver_id;			
			$model->meta_name = 'bank_information';
			$model->meta_value1  = json_encode(array(		
				'provider'=>"bank",
				'account_name'=>$model->account_name,
				'account_number_iban'=>$model->account_number_iban,
				'swift_code'=>$model->swift_code,
				'bank_name'=>$model->bank_name,
				'bank_branch'=>$model->bank_branch
			  ));
			if($model->validate()){
				if($model->save()){
					Yii::app()->user->setFlash('success',CommonUtility::t(Helper_success));
					$this->refresh();
				} else Yii::app()->user->setFlash('error',t(Helper_failed_save));
			} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString( $model->getErrors(),"<br/>" ));
		}

		$this->render("//tpl/submenu_tpl",array(
			'model'=>$model,		    
			'template_name'=>"bank_information",
			'widget'=>'WidgetDriverMenu',		
			'avatar'=>$avatar,
			'title'=>$driver_fullname,
			'params'=>array(  
			   'model'=>$model,				   
			   'hide_nav'=>true,			   
			   'links'=>array(
					t("Driver")=>array('driver/list'),        
					$this->pageTitle,
				),				
			 )
		));
	}

	public function actioncashout_transactions()
	{
		$this->pageTitle = t("Wallet");
		CommonUtility::setMenuActive('.delivery_driver','.driver_list');

		$id = trim(Yii::app()->input->get('id'));		  
		$model = AR_driver::model()->find("driver_uuid=:driver_uuid",array(
			':driver_uuid'=>$id
		   ));
		   if(!$model){
			   $this->render('//tpl/error',array(
				'error'=>array(
				  'message'=>t(Helper_not_found)
				)
			   ));
			   return ;
		}		   
		$avatar = CMedia::getImage($model->photo,$model->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('customer'));
		$driver_fullname = "$model->first_name $model->last_name";

		$table_col = array(
			'transaction_amount'=>array(
			  'label'=>t("Amount"),
			  'width'=>'15%'
			),
			'transaction_description'=>array(
			  'label'=>t("Transaction"),
			  'width'=>'30%'
			),		  
			'transaction_date'=>array(
			  'label'=>t("Date Processed"),
			  'width'=>'20%'
			),
		  );
		$columns = array(
		array('data'=>'transaction_amount'),
		array('data'=>'transaction_description'),
		array('data'=>'transaction_date'),		  
		);				

		$this->render("//tpl/submenu_tpl",array(
			'model'=>$model,		    
			'template_name'=>"cashout_transactions",
			'widget'=>'WidgetDriverMenu',		
			'avatar'=>$avatar,
			'title'=>$driver_fullname,
			'params'=>array(  
			   'model'=>$model,				   
			   'hide_nav'=>true,		   
			   'links'=>array(
					t("Driver")=>array('driver/list'),        
					$this->pageTitle,
				),
				'table_col'=>$table_col,
				'columns'=>$columns,
				'order_col'=>1,
				'sortby'=>'desc',		  
				'ajax_url'=>Yii::app()->createUrl("/api"),
				'driver_uuid'=>$id,				
			 )
		));		  
	}

	public function actioncashout_list()
	{
		$this->pageTitle=t("Cashout list");
		
		$transaction_type = AttributesTools::paymentStatus();
		
		$table_col = array(		  
		  'photo'=>array(
		    'label'=>t(""),
		    'width'=>'10%'
		  ),		  
		  'transaction_date'=>array(
		    'label'=>t("Date"),
		    'width'=>'15%'
		  ),		  
		  'driver_name'=>array(
		    'label'=>t("Name"),
		    'width'=>'30%'
		  ),
		  'transaction_amount'=>array(
		    'label'=>t("Amount"),
		    'width'=>'20%'
		  ),
		  'transaction_uuid'=>array(
		    'label'=>t("Actions"),
		    'width'=>'15%'
		  ),
		);
		$columns = array(		  
		  array('data'=>'photo','orderable'=>false),
		  array('data'=>'transaction_date'),
		  array('data'=>'driver_name'),
		  array('data'=>'transaction_amount'),		  
		  array('data'=>null,'orderable'=>false,
		     'defaultContent'=>'
		     <div class="btn-group btn-group-actions" role="group">
			    <a class="ref_payout normal btn btn-light tool_tips"><i class="zmdi zmdi-eye"></i></a>			    
			 </div>
		     '
		  ),
		);		
		
		$this->render('//driver/cashout_list',array(
		  'table_col'=>$table_col,
		  'columns'=>$columns,
		  'order_col'=>1,
          'sortby'=>'desc',
		  'transaction_type'=>$transaction_type
		));
	}

	public function actioncollect_cash()
	{
		$this->pageTitle=t("Collect list");
		
		$transaction_type = AttributesTools::paymentStatus();
		
		$table_col = array(		  
		  'collect_id'=>array(
		    'label'=>t("ID"),
		    'width'=>'10%'
		  ),		  
		  'transaction_date'=>array(
		    'label'=>t("Date"),
		    'width'=>'15%'
		  ),		  
		  'driver_id'=>array(
		    'label'=>t("Collected from"),
		    'width'=>'25%'
		  ),
		  'amount_collected'=>array(
		    'label'=>t("Amount"),
		    'width'=>'20%'
		  ),
		  'reference_id'=>array(
		    'label'=>t("Reference"),
		    'width'=>'20%'
		  ),
		  'collection_uuid'=>array(
		    'label'=>t("Actions"),
		    'width'=>'15%'
		  ),
		);
		$columns = array(		  
		  array('data'=>'collect_id'),
		  array('data'=>'transaction_date'),
		  array('data'=>'driver_id'),
		  array('data'=>'amount_collected'),		  
		  array('data'=>'reference_id'),		
		  array('data'=>null,'orderable'=>false,
		     'defaultContent'=>'
		     <div class="btn-group btn-group-actions" role="group">
			    <a class="ref_view_url normal btn btn-light tool_tips"><i class="zmdi zmdi-eye"></i></a>
				<a class="ref_delete normal btn btn-light tool_tips"><i class="zmdi zmdi-delete"></i></a>
			 </div>
		     '
		  ),
		);		
		
		$this->render('//driver/collect_cash_list',array(
		  'table_col'=>$table_col,
		  'columns'=>$columns,
		  'order_col'=>1,
          'sortby'=>'desc',
		  'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/collect_cash_add")
		));
	}

	public function actioncollect_cash_add()
	{
		$this->pageTitle=t("Add");	
		CommonUtility::setMenuActive('.delivery_driver','.driver_collect_cash');

		$model = new AR_driver_collect_cash();

		if(isset($_POST['AR_driver_collect_cash'])){	
			$model->attributes=$_POST['AR_driver_collect_cash'];				
			$card_id = 0;
			try {
			    $card_id = CWallet::getCardID( Yii::app()->params->account_type['driver'] , $model->driver_id );								
		    } catch (Exception $e) {			    
				//echo $e->getMessage();
		    }						
			$model->card_id = $card_id;			
			if($model->validate()){		
				
				$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
                $multicurrency_enabled = $multicurrency_enabled==1?true:false; 								
				$driver_data = CDriver::getDriver($model->driver_id);				

				$admin_base_currency = Price_Formatter::$number_format['currency_code'];					    
				$merchant_base_currency = !empty($driver_data->default_currency)?$driver_data->default_currency:$admin_base_currency;
			    $exchange_rate_merchant_to_admin = 1; $exchange_rate_admin_to_merchant=1;

				if($multicurrency_enabled && $merchant_base_currency!=$admin_base_currency){
					$exchange_rate_merchant_to_admin = CMulticurrency::getExchangeRate($merchant_base_currency,$admin_base_currency);
				    $exchange_rate_admin_to_merchant = CMulticurrency::getExchangeRate($admin_base_currency,$merchant_base_currency);
				}

				$model->merchant_base_currency = $merchant_base_currency;		
				$model->admin_base_currency = $admin_base_currency;
				$model->exchange_rate_merchant_to_admin = $exchange_rate_merchant_to_admin;
				$model->exchange_rate_admin_to_merchant = $exchange_rate_admin_to_merchant;				

				if($model->save()){
					$this->redirect(array(Yii::app()->controller->id."/collect_cash"));		
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString( $model->getErrors(),"<br/>" ));
		}

		$this->render('//driver/collect_cash_add',array(		  
			'model'=>$model,						
			'links'=>array(
				  t("Collect cash")=>array(Yii::app()->controller->id."/collect_cash"),        
				  $this->pageTitle,
			  )
		  ));
	}

	public function actioncollect_transactions()
	{
		$this->pageTitle = t("Transaction information");
		CommonUtility::setMenuActive('.delivery_driver','.driver_collect_cash');
		$employment_type = AttributesTools::DriverEmploymentType();

		$id = Yii::app()->input->get('id');				
		$data = CDriver::getCollectCashDetails($id);		

		try {
			
			$card_id = 0; $balance = 0;		
			$driver_id = isset($data['driver_id'])?$data['driver_id']:0;
			try {
			    $card_id = CWallet::getCardID( Yii::app()->params->account_type['driver'] , $driver_id );				
				$balance = CDriver::cashCollectedBalance($card_id);				
		    } catch (Exception $e) {			    
				//echo $e->getMessage();
		    }			

			$this->render("collect_transactions",[
				'data'=>$data,				
				'employment_type'=>$employment_type,
				'balance'=>$balance,
				'links'=>array(
					t("Collect cash")=>array(Yii::app()->controller->id."/collect_cash"),        
					$this->pageTitle,
				)
			]);			
		} catch (Exception $e) {
			$this->render("//tpl/error");
		}		
	}

	public function actioncollect_cash_void()
	{

		$message = '';
		
		try {

			$id = Yii::app()->input->get("id");
			$model = AR_driver_collect_cash::model()->find("collection_uuid=:collection_uuid",[
				':collection_uuid'=>$id
			]);
			if($model){
				$card_id = 0;
				try {
					$card_id = CWallet::getCardID( Yii::app()->params->account_type['driver'] , $model->driver_id );								
				} catch (Exception $e) {			    
					//echo $e->getMessage();
				}						
				$model->card_id = $card_id;
				$model->delete();
				$this->redirect(array(Yii::app()->controller->id."/collect_cash"));		
			} else $message = t(Helper_not_found);
		} catch (Exception $e) {
			$message = $e->getMessage();			
		}		

		if(!empty($msg)){
			$this->render("//tpl/error",[
				'error'=>[
					'message'=>$message
				]
			]);
		}
	}

	public function actiondeleteshift()
	{
		$message = '';
		try {

			$schedule_uuid = Yii::app()->input->get('id');			
			$model = AR_driver_schedule::model()->find("schedule_uuid=:schedule_uuid",[
				':schedule_uuid'=>$schedule_uuid
			]);
			if($model){
				$driver_model = CDriver::getDriver($model->driver_id);					
				if($model->delete()){
						$this->redirect(array(Yii::app()->controller->id."/time_logs?id=".$driver_model->driver_uuid));							
				} else $message = CommonUtility::parseModelErrorToString($model->getErrors());
			} else$message = t("Schedule not found");

		} catch (Exception $e) {
			$message = $e->getMessage();			
		}		

		if(!empty($message)){
			$this->render("//tpl/errors",[
				'error'=>[
					'message'=>$message
				]
			]);
		}
	}

	public function actionendshift()
	{
		$message = '';
		try {

			$schedule_uuid = Yii::app()->input->get('id');			
			$model = AR_driver_schedule::model()->find("schedule_uuid=:schedule_uuid",[
				':schedule_uuid'=>$schedule_uuid
			]);
			if($model){
				if(!is_null($model->shift_time_ended)){
					$message = t("Shift already ended");					
				} else {

					$driver_model = CDriver::getDriver($model->driver_id);

					$model->scenario="end_shift";
					$model->shift_time_ended = CommonUtility::dateNow();
					if($model->shift_time_started==null){
						$model->shift_time_started = CommonUtility::dateNow();
					}
					if($model->save()){
						$this->redirect(array(Yii::app()->controller->id."/time_logs?id=".$driver_model->driver_uuid));							
					} else $message = CommonUtility::parseModelErrorToString($model->getErrors());
				}				
			} else$message = t("Schedule not found");

		} catch (Exception $e) {
			$message = $e->getMessage();			
		}		

		if(!empty($message)){
			$this->render("//tpl/errors",[
				'error'=>[
					'message'=>$message
				]
			]);
		}
	}

}
// end class