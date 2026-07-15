<?php
require_once 'LeagueCSV/vendor/autoload.php';
require 'php-jwt/vendor/autoload.php';
use League\Csv\Writer;
use League\Csv\Reader;
use League\Csv\Statement;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class MerchantdriverController extends Commonmerchant
{
		
	public function beforeAction($action)
	{				
		
		InlineCSTools::registerStatusCSS();
		InlineCSTools::registerOrder_StatusCSS();

		$self_delivery = isset(Yii::app()->params['settings_merchant']['self_delivery'])?Yii::app()->params['settings_merchant']['self_delivery']:false;
		$self_delivery = $self_delivery==1?true:false;
		if(!$self_delivery){
			$this->render('//tpl/error',array(
				'error'=>array(
				  'message'=>t("Delivery management is not enabled in your account.")
				)
			));
			return false;
		}			

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
		
	public function actionaccess_denied()
	{
		$error =array(
		  'code'=>404,
	      'message'=>t(HELPER_ACCESS_DENIED)	    
		);
	    $this->render('//tpl/error',array(
	     'error'=>$error
	    ));
	}

    public function actionList()
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
          'ajax_url'=>Yii::app()->createUrl("/apibackend"),
		  'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/add")
		));
    }

	public function actionadd($update=false)
	{
		$this->pageTitle =  $update==true? t("Update driver information"): t("Add driver");
		CommonUtility::setMenuActive('.merchant_driver','.merchantdriver_list');		
		$upload_path_avatar = 'upload/avatar';
		$upload_path = "upload/license";
		$selected_item = array();
		$phone_default_country = ''; $driver_fullname ='';

        $merchant_id = Yii::app()->merchant->merchant_id;
		
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
                $model->merchant_id = $merchant_id;           
				$model->default_currency = Price_Formatter::$number_format['currency_code'];
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
				
		if($update){			
			$avatar = CMedia::getImage($model->photo,$model->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('customer'));
			$this->render("//tpl/submenu_tpl",array(
				'model'=>$model,		    
				'template_name'=>"//driver/driver_add",
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
				   'multicurrency_enabled'=>false,
				   'links'=>array(
						t("Driver")=>array('merchantdriver/list'),        
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
			'multicurrency_enabled'=>false,
			'links'=>array(
					t("Driver")=>array('merchantdriver/list'),        
					$this->pageTitle,
				)
			));
	    }
	}    
    
	public function actionupdate()
	{
		$this->actionadd(true);
	}

    public function actiondelete()
	{
		$id = Yii::app()->input->get('id');		
        $merchant_id = Yii::app()->merchant->merchant_id;

		$model = AR_driver::model()->find("driver_uuid=:driver_uuid AND merchant_id=:merchant_id",[
            ':driver_uuid'=>$id,
            ':merchant_id'=>$merchant_id
        ]);
		if($model){			
			$model->delete(); 			
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array(Yii::app()->controller->id.'/list'));
		} else $this->render("/tpl/error",array(
				 'error'=>array(
				   'message'=>t(HELPER_RECORD_NOT_FOUND)
				 )
				));		
	}

    public function actionoverview()
	{
		  $this->pageTitle = t("Driver Overview");
		  CommonUtility::setMenuActive('.merchant_driver','.merchantdriver_list');

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
				'template_name'=>"//driver/driver_overview",
				'widget'=>'WidgetDriverMenu',		
				'avatar'=>$avatar,
				'title'=>$driver_fullname,
				'params'=>array(  
				   'model'=>$model,				   
				   'hide_nav'=>true,		   
				   'links'=>array(
						t("Driver")=>array('merchantdriver/list'),        
						$this->pageTitle,
					 ),
					'driver_uuid'=>$id, 	
                    'ajax_url'=>Yii::app()->createUrl("/apibackend")				
				 )
			));
	}

	public function actionlicense()
	{
		$update = true;
		$this->pageTitle =  t("License");
		CommonUtility::setMenuActive('.merchant_driver','.merchantdriver_list');
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
			'template_name'=>"//driver/driver_license",
			'widget'=>'WidgetDriverMenu',		
			'avatar'=>$avatar,
			'title'=>$driver_fullname,
			'params'=>array(  
			   'model'=>$model,
			   'upload_path'=>$upload_path,
			   'status'=>(array)AttributesTools::StatusManagement('customer'),			   
			   'hide_nav'=>true,			   
			   'links'=>array(
					t("Driver")=>array('merchantdriver/list'),        
					$this->pageTitle,
				),					
			 )
		));
	}

	public function actionvehicle()
	{
		$this->pageTitle = t("Vehicle");
		CommonUtility::setMenuActive('.merchant_driver','.merchantdriver_list');

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
			'template_name'=>"//driver/car_add_with_driver",
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
					t("Driver")=>array('merchantdriver/list'),        
					$this->pageTitle,
				),				
			 )
		));
	}    

	public function actionbank_info()
	{
		$this->pageTitle = t("Bank information");
		CommonUtility::setMenuActive('.merchant_driver','.merchantdriver_list');

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
			'template_name'=>"//driver/bank_information",
			'widget'=>'WidgetDriverMenu',		
			'avatar'=>$avatar,
			'title'=>$driver_fullname,
			'params'=>array(  
			   'model'=>$model,				   
			   'hide_nav'=>true,			   
			   'links'=>array(
					t("Driver")=>array('merchantdriver/list'),        
					$this->pageTitle,
				),				
			 )
		));
	}

	public function actionwallet()
	{
		$this->pageTitle = t("Wallet");
		CommonUtility::setMenuActive('.merchant_driver','.merchantdriver_list');

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
			'template_name'=>"//driver/wallet_transaction",
			'widget'=>'WidgetDriverMenu',		
			'avatar'=>$avatar,
			'title'=>$driver_fullname,
			'params'=>array(  
			   'model'=>$model,				   
			   'hide_nav'=>true,		   
			   'links'=>array(
					t("Driver")=>array('merchantdriver/list'),        
					$this->pageTitle,
				),
				'table_col'=>$table_col,
				'columns'=>$columns,
				'order_col'=>1,
				'sortby'=>'desc',		  
				'ajax_url'=>Yii::app()->createUrl("/api"),
				'driver_uuid'=>$id,
				'transaction_type'=>$transaction_type,
                'ajax_url'=>Yii::app()->createUrl("/apibackend")				
			 )
		));		  
	}    

	public function actioncashout_transactions()
	{
		$this->pageTitle = t("Wallet");
		CommonUtility::setMenuActive('.merchant_driver','.merchantdriver_list');

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
			'template_name'=>"//driver/cashout_transactions",
			'widget'=>'WidgetDriverMenu',		
			'avatar'=>$avatar,
			'title'=>$driver_fullname,
			'params'=>array(  
			   'model'=>$model,				   
			   'hide_nav'=>true,		   
			   'links'=>array(
					t("Driver")=>array('merchantdriver/list'),        
					$this->pageTitle,
				),
				'table_col'=>$table_col,
				'columns'=>$columns,
				'order_col'=>1,
				'sortby'=>'desc',		  
				'ajax_url'=>Yii::app()->createUrl("/apibackend"),
				'driver_uuid'=>$id,				
			 )
		));		  
	}

	public function actiondelivery_transactions()
	{
		  $this->pageTitle = t("Delivery transactions");
		  CommonUtility::setMenuActive('.merchant_driver','.merchantdriver_list');

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
				'template_name'=>"//driver/delivery_transactions",
				'widget'=>'WidgetDriverMenu',		
				'avatar'=>$avatar,
				'title'=>$driver_fullname,
				'params'=>array(  
				   'model'=>$model,				   
				   'hide_nav'=>true,		   
				   'links'=>array(
						t("Driver")=>array('merchantdriver/list'),        
						$this->pageTitle,
					),
					'table_col'=>$table_col,
					'columns'=>$columns,
					'order_col'=>1,
					'sortby'=>'desc',		  
					'ajax_url'=>Yii::app()->createUrl("/apibackend"),
					'driver_uuid'=>$id,
				 )
			));
	}    

	public function actionorder_tips()
	{
		  $this->pageTitle = t("Order Tips");
		  CommonUtility::setMenuActive('.merchant_driver','.merchantdriver_list');

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
				'template_name'=>"//driver/order_tips",
				'widget'=>'WidgetDriverMenu',		
				'avatar'=>$avatar,
				'title'=>$driver_fullname,
				'params'=>array(  
				   'model'=>$model,				   
				   'hide_nav'=>true,		   
				   'links'=>array(
						t("Driver")=>array('merchantdriver/list'),        
						$this->pageTitle,
					),
					'table_col'=>$table_col,
					'columns'=>$columns,
					'order_col'=>1,
					'sortby'=>'desc',		  
					'ajax_url'=>Yii::app()->createUrl("/apibackend"),
					'driver_uuid'=>$id,
				 )
			));
	}

	public function actiontime_logs()
	{
		  $this->pageTitle = t("Time logs");
		  CommonUtility::setMenuActive('.merchant_driver','.merchantdriver_list');

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
				'template_name'=>"//driver/time_logs",
				'widget'=>'WidgetDriverMenu',		
				'avatar'=>$avatar,
				'title'=>$driver_fullname,
				'params'=>array(  
				   'model'=>$model,				   
				   'hide_nav'=>true,		   
				   'links'=>array(
						t("Driver")=>array('merchantdriver/list'),        
						$this->pageTitle,
					),
					'table_col'=>$table_col,
					'columns'=>$columns,
					'order_col'=>2,
					'sortby'=>'desc',		  
					'ajax_url'=>Yii::app()->createUrl("/apibackend"),
					'driver_uuid'=>$id
				 )
			));
	}    

	public function actionreview_ratings()
	{
		  $this->pageTitle = t("Reviews");
		  CommonUtility::setMenuActive('.merchant_driver','.merchantdriver_list');

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
				'template_name'=>"//driver/driver_review",
				'widget'=>'WidgetDriverMenu',		
				'avatar'=>$avatar,
				'title'=>$driver_fullname,
				'params'=>array(  
				   'model'=>$model,				   
				   'hide_nav'=>true,		   
				   'links'=>array(
						t("Driver")=>array('merchantdriver/list'),        
						$this->pageTitle,
					),
					'table_col'=>$table_col,
					'columns'=>$columns,
					'order_col'=>1,
					'sortby'=>'desc',		  
					'ajax_url'=>Yii::app()->createUrl("/apibackend"),
					'driver_uuid'=>$id,
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
          'ajax_url'=>Yii::app()->createUrl("/apibackend"),
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
		CommonUtility::setMenuActive('.merchant_driver','.merchantdriver_carlist');

		$model = new AR_driver_vehicle();
		$item_gallery = array();
        $merchant_id = Yii::app()->merchant->merchant_id;

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
			':merchant_id'=>$merchant_id,
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
            $model->merchant_id = $merchant_id;
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
						':merchant_id' => $merchant_id,
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
				  t("Car registration")=>array('merchantdriver/carlist'),        
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
		} else $this->render("/tpl/error",array(
				 'error'=>array(
				   'message'=>t(HELPER_RECORD_NOT_FOUND)
				 )
				));		
	}    

	public function actiongroup_list()
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
          'ajax_url'=>Yii::app()->createUrl("/apibackend"),
		  'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/addgroup")
		));
	}	    

	public function actionaddgroup($update=false)
	{			
		$this->pageTitle = t("Add Group");
		CommonUtility::setMenuActive('.merchant_driver','.merchantdriver_group_list');	

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
            $model->merchant_id = Yii::app()->merchant->merchant_id;	
			if($model->validate()){						
				if($model->save()){
					if(!$update){
					   $this->redirect(array(Yii::app()->controller->id."/group_list"));		
					} else {
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
						$this->refresh();
					}
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString( $model->getErrors(),"<br/>" ));
		}

		$drivers = CommonUtility::getDataToDropDown("{{driver}}",'driver_id',"concat(first_name,' ',last_name)",
        "WHERE status='active' AND merchant_id=".q(Yii::app()->merchant->merchant_id)."
        ","ORDER BY first_name");		
		
		$this->render('//driver/group_add',[
			'model'=>$model,
			'drivers'=>$drivers,
			'links'=>array(
			    t("Groups")=>array(Yii::app()->controller->id.'/group_list'),        
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
			$this->redirect(array(Yii::app()->controller->id.'/group_list'));
		} else $this->render("/tpl/error",array(
				 'error'=>array(
				   'message'=>t(HELPER_RECORD_NOT_FOUND)
				 )
				));		
	}    

	public function actionschedule_list()
	{
		$this->pageTitle = t("Schedule");
		
		$this->render('//driver/driver_schedule',[
			'links'=>array(
			    t("Driver")=>array('driver/list'),        
			    $this->pageTitle,
			),
			'ajax_url'=>Yii::app()->createUrl("/apibackend"),
			'schedule_bulk_url'=>Yii::app()->createUrl("/merchantdriver/schedule_bulk"),
			'merchant_id'=>Yii::app()->merchant->merchant_id
		]);
	}

	public function actionschedule_bulk()
	{
		$this->pageTitle = t("Import schedule file");
		CommonUtility::setMenuActive('.merchant_driver','.merchantdriver_schedule_list');	

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
							$model->merchant_id = Yii::app()->merchant->merchant_id;
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

		$this->render("//driver/schedule_import",[
			'model'=>$model,
			'links'=>array(
				t("Driver"),
	            t("Schedule")=>array(Yii::app()->controller->id.'/schedule_list'), 
	            $this->pageTitle,
		      ),	    		    
		]);
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
          'ajax_url'=>Yii::app()->createUrl("/apibackend"),
		  'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/shift_add"),
		  'shift_bulkupload'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/shift_bulkupload"),
		));
	}	

	public function actionshift_update()
	{
		$this->actionshift_add(true);
	}	

	public function actionshift_add($update=false)
	{
		$this->pageTitle = t("Add Shift");
		CommonUtility::setMenuActive('.merchant_driver','.merchantdriver_shift_list');	

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
				$model->merchant_id = Yii::app()->merchant->merchant_id;					
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
		WHERE merchant_id = ".Yii::app()->merchant->merchant_id." ","ORDER BY zone_name ASC");

		$this->render('//driver/addshift',[
			'model'=>$model,			
			'zone_list'=>$zone_list,
			'time_range'=>AttributesTools::createTimeRange("00:00","24:00"),
			'status'=>(array)AttributesTools::StatusManagement('customer'),		  
			'links'=>array(
			    t("Shifts Schedule")=>array(Yii::app()->controller->id.'/shift_list'),        
			    $this->pageTitle,
			)
		]);

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
		} else $this->render("/tpl/error",array(
				 'error'=>array(
				   'message'=>t(HELPER_RECORD_NOT_FOUND)
				 )
				));		
	}	

	public function actionshift_bulkupload()
	{
		$this->pageTitle = t("Import schedule file");
		CommonUtility::setMenuActive('.merchant_driver','.merchantdriver_shift_list');	

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
							$model->merchant_id = Yii::app()->merchant->merchant_id;
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

		$this->render("//driver/shift_bulkupload",[
			'model'=>$model,
			'links'=>array(
				t("Driver"),
	            t("Shift Schedule")=>array(Yii::app()->controller->id.'/shift_list'), 
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
          'ajax_url'=>Yii::app()->createUrl("/apibackend"),
		  'link'=>''
		));
	}

	public function actionzone_list()
	{
		$this->pageTitle=t("Zones list");
		
		$table_col = array(
		 'zone_id'=>array(
		    'label'=>t("ID"),
		    'width'=>'5%'
		  ),
		  'zone_name'=>array(
		    'label'=>t("Name"),
		    'width'=>'20%'
		  ),
		  'description'=>array(
		    'label'=>t("Description"),
		    'width'=>'20%'
		  ),
		  'zone_uuid'=>array(
		    'label'=>t("Actions"),
		    'width'=>'20%'
		  ),		  
		);
		$columns = array(
		  array('data'=>'zone_id'),
		  array('data'=>'zone_name'),
		  array('data'=>'description'),
		  array('data'=>null,'orderable'=>false,
		     'defaultContent'=>'
		     <div class="btn-group btn-group-actions" role="group">
			    <a class="ref_edit normal btn btn-light tool_tips"><i class="zmdi zmdi-border-color"></i></a>
			    <a class="ref_delete normal btn btn-light tool_tips"><i class="zmdi zmdi-delete"></i></a>
			 </div>
		     '
		  ),	  
		);				
				
		$this->render('//attributes/zone_list',array(
		  'table_col'=>$table_col,
		  'columns'=>$columns,
		  'order_col'=>0,
          'sortby'=>'desc',
		  'transaction_type'=>array(),		  
		  'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/zone_create"),
		  'ajax_url'=>Yii::app()->createUrl("/apibackend"),
		));
	}	

	public function actionzone_create($update=false)
	{
		$this->pageTitle=t("Create Zones");
		CommonUtility::setMenuActive('.merchant_driver','.merchantdriver_zone_list');
		
		$id = Yii::app()->input->get('id');			
		$model = new AR_zones;
		if($update){
			$model = AR_zones::model()->find("zone_uuid=:zone_uuid",array(
			 ':zone_uuid'=>$id
			));
			if(!$model){
				$this->render('//tpl/error',array(
				 'error'=>array(
				   'message'=>t(Helper_not_found)
				 )
				));
				return ;
			}
		}
		
		if(isset($_POST['AR_zones'])){

			if($update){
				if(DEMO_MODE){
					if($model->zone_id==1){
						$this->render('//tpl/error',array(  
							'error'=>array(
							  'message'=>t("Modification not available in demo")
							)
						  ));	
					    return false;
					}
				}
			}

			$model->attributes=$_POST['AR_zones'];			
			if($model->validate()){			
				$model->merchant_id = Yii::app()->merchant->merchant_id;			
				if($model->save()){
					if(!$update){
					   $this->redirect(array(Yii::app()->controller->id."/zone_list"));		
					} else {
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
						$this->refresh();
					}
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString( $model->getErrors() ));
		}
		
		$this->render('//attributes/zone_create',array(		  
		  'model'=>$model,
		  'links'=>array(
			    t("Zones list")=>array(Yii::app()->controller->id.'/zone_list'),        
			    $this->pageTitle,
			)
		));
	}
	
	public function actionzone_update()
	{
		$this->actionzone_create(true);
	}	

	public function actionzone_delete()
	{
		if(DEMO_MODE){			
			$this->render('//tpl/error',array(  
				  'error'=>array(
					'message'=>t("Modification not available in demo")
				  )
				));	
			return false;
		}
		
		$id =  Yii::app()->input->get('id');			
		$model = AR_zones::model()->find("zone_uuid=:zone_uuid",array(
		  ':zone_uuid'=>$id
		));
		if($model){
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array('/merchantdriver/zone_list'));			
		} else $this->render("//tpl/error");
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
		  'transaction_type'=>$transaction_type,
		  'ajax_url'=>Yii::app()->createUrl("/apibackend"),
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
		  'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/collect_cash_add"),
		  'ajax_url'=>Yii::app()->createUrl("/apibackend"),
		));
	}

	public function actioncollect_cash_add()
	{
		$this->pageTitle=t("Add");	
		CommonUtility::setMenuActive('.merchant_driver','.merchantdriver_collect_cash');

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

				$merchant_base_currency = Price_Formatter::$number_format['currency_code'];
				$admin_base_currency = AttributesTools::defaultCurrency();							

				$exchange_rate_merchant_to_admin = 1;
				$exchange_rate_admin_to_merchant = 1;
				if($multicurrency_enabled && $merchant_base_currency!=$admin_base_currency){
					$exchange_rate_merchant_to_admin = CMulticurrency::getExchangeRate($merchant_base_currency,$admin_base_currency);
					$exchange_rate_admin_to_merchant = CMulticurrency::getExchangeRate($admin_base_currency,$merchant_base_currency);
				}

				$model->merchant_id = Yii::app()->merchant->merchant_id;	
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
		CommonUtility::setMenuActive('.merchant_driver','.merchantdriver_collect_cash');
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

			$this->render("//driver/collect_transactions",[
				'data'=>$data,				
				'employment_type'=>$employment_type,
				'balance'=>$balance,
				'links'=>array(
					t("Collect cash")=>array(Yii::app()->controller->id."/collect_cash"),        
					$this->pageTitle,
				),
				'driver_link'=>Yii::app()->createUrl("/merchantdriver/overview",[
					'id'=>isset($data['driver_uuid'])?$data['driver_uuid']:''
				]),
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

	public function actionreview_delete()
	{
		$id = intval(Yii::app()->input->get('id'));												
		$model = AR_driver_reviews::model()->findByPk($id);
		if($model){			
			$model->delete(); 			
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array(Yii::app()->controller->id.'/review_list'));
		} else $this->render("/merchant/error",array(
				 'error'=>array(
				   'message'=>t(HELPER_RECORD_NOT_FOUND)
				 )
				));		
	}

	public function actionreview_update()
	{
		
		$this->pageTitle=t("Update review");				
		  CommonUtility::setMenuActive('.merchant_driver','.merchantdriver_review_list');

		
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
				  t("Reviews")=>array('merchantdriver/review_list'),        
				  $this->pageTitle,
			  )
		  ));
	}

}
// end class