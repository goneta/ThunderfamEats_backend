<?php
require_once 'LeagueCSV/vendor/autoload.php';
require 'php-jwt/vendor/autoload.php';
use League\Csv\Writer;
use League\Csv\Reader;
use League\Csv\Statement;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class VendorController extends CommonController
{
	
	public function beforeAction($action)
	{									
		InlineCSTools::registerStatusCSS();	
		return true;
	}
		
	public function actionIndex()
	{
		$this->redirect(array(Yii::app()->controller->id.'/list'));
	}
	
	public function actionList()
	{		
		$this->pageTitle=t("All Merchant");
		$action_name='merchant_list';
		$delete_link = Yii::app()->CreateUrl(Yii::app()->controller->id."/delete");

		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
		),'action_name');		
			
		if(Yii::app()->params['isMobile']==TRUE){
			$tpl = 'merchant_list_app';
		} else $tpl = 'merchant_list';
		
		$this->render( $tpl ,array(
			'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/create"),
			'bulk_link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/bulkupload"),
		));
	}	

	public function actionpending_list()
	{
		$this->pageTitle=t("New signup");
		$action_name='merchant_new_signup';
		$delete_link = Yii::app()->CreateUrl(Yii::app()->controller->id."/delete");

		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
		),'action_name');		
			
		if(Yii::app()->params['isMobile']==TRUE){
			$tpl = 'merchant_list_app';
		} else $tpl = 'merchant_list';
		
		$this->render( $tpl ,array(
			'link'=>'',
			'bulk_link'=>'',
		));
	}
	
	private function setMenuActive($class_name='.vendor_list')
	{
		ScriptUtility::registerScript(array(
		  '$(".siderbar-menu li.merchant").addClass("active")',		 
		  '$(".siderbar-menu li.merchant ul li'.$class_name.'").addClass("active")',		 
		),'menu_active',CClientScript::POS_END);
		
		$this->pageTitle = t("Add Merchant");		
	}
	
	public function actionCreate()
	{
		$this->setMenuActive(".vendor_list");
		$model=new AR_merchant;
		$model->scenario='information';
		$upload_path = CMedia::adminFolder();		
				
		if(isset($_POST['AR_merchant'])){
		    $model->attributes=$_POST['AR_merchant'];			    	
		    if($model->validate()){						    				    	
		    			    	
		    	if(isset($_POST['photo'])){
					if(!empty($_POST['photo'])){
						$model->logo = $_POST['photo'];
						$model->path = isset($_POST['path'])?$_POST['path']:$upload_path;
					} else $model->logo = '';
				} else $model->logo = '';
				
				if(isset($_POST['header_image'])){
					if(!empty($_POST['header_image'])){
						$model->header_image = $_POST['header_image'];
						$model->path2 = isset($_POST['path2'])?$_POST['path2']:$upload_path;
					} else $model->header_image = '';
				} else $model->header_image = '';
			    
		    	if($model->save()){					    				    		
					Yii::app()->user->setFlash('success', t(Helper_success) );					
					$this->redirect(array('vendor/edit','id'=>$model->merchant_id));
					Yii::app()->end();
				} else {					
					Yii::app()->user->setFlash('error',CommonUtility::t(Helper_failed_update));
				}				
			} else Yii::app()->user->setFlash('error',CommonUtility::t(HELPER_CORRECT_FORM));
		}
		
		$cuisine = CommonUtility::getDataToDropDown("{{cuisine}}",'cuisine_id','cuisine_name',"
		WHERE status = 'publish'","ORDER BY cuisine_name ASC");
		
		$tags = CommonUtility::getDataToDropDown("{{tags}}",'tag_id','tag_name',"","ORDER BY tag_name ASC");

		$this->render("merchant_create",array(
		  'model'=>$model,
		  'status'=>(array)AttributesTools::StatusManagement('customer'),
		  'cuisine'=>(array)AttributesTools::ListSelectCuisine(),
		  'tags'=>(array)AttributesTools::ListSelectTags(),
		  'services'=>(array)AttributesTools::ListSelectServices(),
		  'unit'=>AttributesTools::unit(),
		  'zone_type'=>AttributesTools::zoneType(),
		  'featured'=>AttributesTools::MerchantFeatured(),
		  'upload_path'=>$upload_path,
			'download_qrcode_pickup'=>"",
			'download_qrcode_dinein'=>"",
		  'links'=>array(
			    'links'=>array(
			        t("All Merchant")=>array(Yii::app()->controller->id.'/list'),        
			        $model->isNewRecord?t("Add new"):t("Edit Merchant"),
			    ),
			    'homeLink'=>false,
			    'separator'=>'<span class="separator">
			    <i class="zmdi zmdi-chevron-right"></i><i class="zmdi zmdi-chevron-right"></i></span>'
			)
	    ));
	}
	
	public function actionEdit()
	{
		$this->setMenuActive('.vendor_list');
		$this->pageTitle = t("Edit Merchant - information");	
		
		$id = (integer) Yii::app()->input->get('id');	
		$upload_path = CMedia::adminFolder();
				
		if($id>0){
										
			$model = AR_merchant::model()->findByPk( $id );					
			
			if(!$model){				
				$this->render("error");				
				Yii::app()->end();
			}
			
			$model->scenario='information';
			
			if(isset($_POST['AR_merchant'])){
				$post = Yii::app()->input->post('AR_merchant');				
			    $model->merchant_about_trans = isset($post['merchant_about_trans'])?$post['merchant_about_trans']:'';
			    $model->merchant_short_about_trans = isset($post['merchant_short_about_trans'])?$post['merchant_short_about_trans']:'';			
			    $model->attributes=$_POST['AR_merchant'];			
			    if($model->validate()){			    	
			    			    			    	    
		    	    if(isset($_POST['photo'])){
						if(!empty($_POST['photo'])){
							$model->logo = $_POST['photo'];
							$model->path = isset($_POST['path'])?$_POST['path']:$upload_path;
						} else $model->logo = '';
					} else $model->logo = '';
					
					if(isset($_POST['header_image'])){
						if(!empty($_POST['header_image'])){
							$model->header_image = $_POST['header_image'];
							$model->path2 = isset($_POST['path2'])?$_POST['path2']:$upload_path;
						} else $model->header_image = '';
					} else $model->header_image = '';
			    	
					if($model->save()){																					
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
						$this->refresh();
					} else {
						Yii::app()->user->setFlash('error',CommonUtility::t(Helper_failed_update));
					}				
				} else Yii::app()->user->setFlash('error',CommonUtility::t(HELPER_CORRECT_FORM));
			}
			
			
			if(!isset($_POST['AR_merchant'])){
												
				/*if(isset($model->cuisine)){				
				   $model->cuisine2 = json_decode($model->cuisine,true);
				}*/				
				$find = AR_cuisine_merchant::model()->findAll(
				    'merchant_id=:merchant_id',
				    array(':merchant_id'=> intval($model->merchant_id) )
				);
				if($find){
					$selected = array();
					foreach ($find as $items) {					
						$selected[]=$items->cuisine_id;
					}
					$model->cuisine2 = $selected;
				}		
			
				if($services = MerchantTools::getMerchantMeta($model->merchant_id,'services')){
					$model->service2=$services;
				}											

				if($service3 = MerchantTools::getMerchantMeta($model->merchant_id,'services_pos')){
					$model->service3=$service3;
				}											

				if($tableside_services = MerchantTools::getMerchantMeta($model->merchant_id,'tableside_services')){
					$model->tableside_services=$tableside_services;
				}											
				
				if($services = MerchantTools::getMerchantMeta($model->merchant_id,'featured')){
					$model->featured=$services;
				}											
				
				if($tags = MerchantTools::getMerchantOptions($model->merchant_id,'tags')){					
					$model->tags=$tags;
				}											
			}
			
			$model->delivery_distance_covered = Price_Formatter::formatDistance($model->delivery_distance_covered);
			
			$model->restaurant_name = stripslashes($model->restaurant_name);
			
			$avatar = CMedia::getImage($model->logo,$model->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));

			$data =[];
			if(!isset($_POST['AR_merchant'])){
				if($merchant_about_trans = AR_merchant::getTranslation($id,'merchant_about_trans')){
					$data['merchant_about_trans']=$merchant_about_trans;				
				}			
				if($merchant_about_trans = AR_merchant::getTranslation($id,'merchant_short_about_trans')){
					$data['merchant_short_about_trans']=$merchant_about_trans;				
				}			
			}
			$merchant = CMerchants::get($id);	
			$user_token = [
				'iss'=>Yii::app()->request->getServerName(),
				'sub'=>$merchant->merchant_uuid,
				'iat'=>time(),
			];
			$user_token = JWT::encode($user_token, CRON_KEY, 'HS256');
				
			$payload_pickup = [		
				'user_token'=>$user_token,
				'merchant_id'=>$id,
				'transaction_type'=>'pickup',
			];					
			$payload_dinein = [		
				'user_token'=>$user_token,
				'merchant_id'=>$id,
				'transaction_type'=>'dinein',
			];					
						
			$qrcode_pickup = JWT::encode($payload_pickup, CRON_KEY, 'HS256');						
			$qrcode_dinein = JWT::encode($payload_dinein, CRON_KEY, 'HS256');						
			
			
			$download_qrcode_pickup = Yii::app()->CreateUrl("/merchant/view_qrcode",[
				'data'=>$qrcode_pickup
			]);	
			$download_qrcode_dinein = Yii::app()->CreateUrl("/merchant/view_qrcode",[
				'data'=>$qrcode_dinein
			]);	
			$fields[]=array(
				'name'=>'merchant_about_trans',
				'placeholder'=>"About - [lang]",
				'type'=>"textarea"
			);
			$fields[]=array(
				'name'=>'merchant_short_about_trans',
				'placeholder'=>"Short About - [lang]",
				'type'=>"textarea"
			);
			
			$params_model = array(		
				'model'=>$model,	
				'status'=>(array)AttributesTools::StatusManagement('customer'),	    
				'cuisine'=>(array)AttributesTools::ListSelectCuisine(Yii::app()->language),
				'services'=>(array)AttributesTools::ListSelectServicesNew(Yii::app()->language),
				'tags'=>(array)AttributesTools::ListSelectTags(),
				'unit'=>AttributesTools::unit(),		
				'zone_type'=>AttributesTools::zoneType(),
				'featured'=>AttributesTools::MerchantFeatured(),
				'ctr'=>'/vendor',
				'upload_path'=>$upload_path,
				'download_qrcode_pickup'=>isset($download_qrcode_pickup)?$download_qrcode_pickup:"",
				'download_qrcode_dinein'=>isset($download_qrcode_dinein)?$download_qrcode_dinein:"",
				'links'=>array(
				   t("All Merchant")=>array(Yii::app()->controller->id.'/list'),        
			       $model->isNewRecord?t("Add new"):t("Edit Merchant"),
			       isset($model->restaurant_name)?stripslashes(ucwords($model->restaurant_name)):''
				),	    		
				'show_status'=>true,
				'language'=>AttributesTools::getLanguage(),
			    'fields'=>$fields,
			    'data'=>$data,
				'is_admin'=>true,
			);	
			
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantInfoMenu;		   			   
			   $menu->merchant_id = $id;
			   $menu->init();    
			}
						
			$this->render("//tpl/submenu_tpl",array(		    
				'template_name'=>"//vendor/merchant_info",
				'widget'=>'WidgetMerchantInfoMenu',		
				'avatar'=>$avatar,
				'params'=>$params_model,
				'custom_link'=>CHtml::link('Auto login',Yii::app()->createUrl("vendor/autologin",[
					'merchant_uuid'=>$model->merchant_uuid,
				]),[
					'class'=>"btn btn-link",
					'target'=>"_blank"
				]),
				'menu'=>$menu,
				'params_widget'=>array(			   
				   'merchant_id'=>$id,
				   'merchant_type'=>$model->merchant_type,
				   'self_delivery'=>$model->self_delivery
				)
			));		
			
		} else {
			$this->render("error");
		}
	}
	
	public function actiondelete_logo()
	{		
		$id = (integer) Yii::app()->input->get('id');			
		$page = Yii::app()->input->get('page');			
		$model = AR_merchant::model()->findByPk( $id );				
		if($model){		
			$model->logo='';
			$model->save();					
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array($page."/id/$id"));
		} else $this->render("error");
	}
	
    public function actionlogin()
    {
    	$this->setMenuActive();		
		$this->pageTitle = t("Edit Merchant - login");
		
		$merchant_id = intval(Yii::app()->input->get('id'));		
    	$merchant = AR_merchant::model()->findByPk( $merchant_id );	
    	if($merchant){
	    	$avatar = CMedia::getImage($merchant->logo,$merchant->path,'@thumbnail',
	        CommonUtility::getPlaceholderPhoto('merchant_logo'));
	        
	        $model = AR_merchant_user::model()->find("merchant_id=:merchant_id AND main_account=:main_account",array(
			  ':merchant_id'=>$merchant_id,
			  ':main_account'=>1
			));	
			
			if(!$model){
	            $model = new AR_merchant_user;
	            $model->scenario = "register";
			} 			
					
			if(isset($_POST['AR_merchant_user'])){
				$model->attributes=$_POST['AR_merchant_user'];
				if($model->validate()){	
					$model->status = 'active';
					$model->main_account = 1;
					$model->merchant_id = intval($merchant->merchant_id);
					
					if($model->scenario=="register"){
						$model->password = trim($model->new_password);
					} else {
						if( !empty($model->new_password) && !empty($model->repeat_password) ){
							$model->password = md5(trim($model->new_password));
						}
					}
					
					if($model->save()){
					   Yii::app()->user->setFlash('success', t(Helper_success) );		
			       	   $this->refresh();			
					} else Yii::app()->user->setFlash('error',CommonUtility::t(Helper_failed_update));
				} else Yii::app()->user->setFlash('error',CommonUtility::t(HELPER_CORRECT_FORM));
			}

			if($model){
				$model->password = '';
			}
				
        	$params_model = array(		
				'model'=>$model,	
				'links'=>array(
				   t("All Merchant")=>array(Yii::app()->controller->id.'/list'),        
				   $model->isNewRecord?t("Add new"):t("Edit Merchant"),
				   isset($merchant->restaurant_name)?stripslashes(ucwords($merchant->restaurant_name)):''
				),	    	
				'status'=>(array)AttributesTools::StatusManagement('customer'),	   
			);	
				
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantInfoMenu;		   			   
			   $menu->merchant_id = $merchant_id;
			   $menu->init();    
			}
						
			$this->render("//tpl/submenu_tpl",array(		    
				'template_name'=>"merchant_login",
				'widget'=>'WidgetMerchantInfoMenu',		
				'avatar'=>$avatar,
				'params'=>$params_model,
				'custom_link'=>CHtml::link('Auto login',Yii::app()->createUrl("vendor/autologin",[
					'merchant_uuid'=>$merchant->merchant_uuid,
				]),[
					'class'=>"btn btn-link",
					'target'=>"_blank"
				]),
				'menu'=>$menu,
				'params_widget'=>array(			   
				   'merchant_id'=>$merchant_id,
				   'merchant_type'=>$merchant->merchant_type,
				   'self_delivery'=>$merchant->self_delivery
				),				
			));		
	        
    	} else $this->render("error");	
    }
	
	public function actionmembership()
	{		
		$this->setMenuActive();		
		$this->pageTitle = t("Edit Merchant - Merchant type");
		
		$id = (integer) Yii::app()->input->get('id');		
		$model = AR_merchant::model()->findByPk( $id );
		if($model){
			
			//$model->scenario='membership';

			if(isset($_POST['AR_merchant'])){
		       $model->attributes=$_POST['AR_merchant'];				       
			    if($model->validate()){						    				    	
			    	$model->percent_commision = (float)$model->percent_commision;			    	
			    	if($model->save()){						    					    	
						Yii::app()->user->setFlash('success', t(Helper_success) );
						$this->refresh();						
					} else {					
						Yii::app()->user->setFlash('error',CommonUtility::t(Helper_failed_update));
					}				
				} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString($model->getErrors(),"<br/>") );
			}		
			
			$model->percent_commision = number_format( (float) $model->percent_commision,2);
			// if($model->merchant_type==1){
			// 	$model->invoice_terms2 = $model->invoice_terms;
			// } else {
			// 	if($getdata = CMerchants::getCommissionData($id)){
			// 		$model->commission_type = $getdata['commission_type'];
			// 		$model->commission_value = $getdata['commission_value'];
			// 	} else {					
			// 		if($getdata = CMerchants::getOldCommissionData($model->commision_type,$model->percent_commision)){
			// 			$model->commission_type = $getdata['commission_type'];
			// 		    $model->commission_value = $getdata['commission_value'];
			// 		}
			// 	}				
			// }

			//$model->invoice_terms2 = $model->invoice_terms;
			if($getdata = CMerchants::getCommissionData($id)){
				$model->commission_type = $getdata['commission_type'];
				$model->commission_value = $getdata['commission_value'];
			} else {					
				if($getdata = CMerchants::getOldCommissionData($model->commision_type,$model->percent_commision)){
					$model->commission_type = $getdata['commission_type'];
					$model->commission_value = $getdata['commission_value'];
				}
			}				
			
            $avatar = CMedia::getImage($model->logo,$model->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));

			$params_model = array(		
				'model'=>$model,	
				'links'=>array(
				   t("All Merchant")=>array(Yii::app()->controller->id.'/list'),        
				   $model->isNewRecord?t("Add new"):t("Edit Merchant"),
				   isset($model->restaurant_name)?stripslashes(ucwords($model->restaurant_name)):''
				),	    
				'merchant_type'=>AttributesTools::ListMerchantType(),
			    'package'=>AttributesTools::ListPlans(),
			    'commision_type'=>AttributesTools::CommissionType(),
			    'invoice_terms_type'=>AttributesTools::InvoiceTerms(),	
				'commission_type_list'=>AttributesTools::CommissionType(),
				'service_list'=>CServices::Listing(Yii::app()->language)					
			);	
			
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantInfoMenu;		   			   
			   $menu->merchant_id = $id;
			   $menu->init();    
			}
						
			$this->render("//tpl/submenu_tpl",array(		    
				'template_name'=>"//vendor/membership",
				'widget'=>'WidgetMerchantInfoMenu',		
				'avatar'=>$avatar,
				'custom_link'=>CHtml::link('Auto login',Yii::app()->createUrl("vendor/autologin",[
					'merchant_uuid'=>$model->merchant_uuid,
				]),[
					'class'=>"btn btn-link",
					'target'=>"_blank"
				]),
				'params'=>$params_model,
				'menu'=>$menu,
				'params_widget'=>array(			   
				   'merchant_id'=>$id,
				   'merchant_type'=>$model->merchant_type,
				   'self_delivery'=>$model->self_delivery
				),				
			));		
			
		} else $this->render("error");
	}
	
	public function actionfeatured()
	{
		$this->setMenuActive();		
		$this->pageTitle = t("Edit Merchant - Featured");
		
		$id = (integer) Yii::app()->input->get('id');		
		$model = AR_merchant::model()->findByPk( $id );
		if($model){
			
			$model->scenario='featured';

			if(isset($_POST['AR_merchant'])){
		       $model->attributes=$_POST['AR_merchant'];				       
			    if($model->validate()){						    				    	
			    				    	
			    	if($model->save()){						    					    	
						Yii::app()->user->setFlash('success', t(Helper_success) );
						$this->refresh();						
					} else {					
						Yii::app()->user->setFlash('error',CommonUtility::t(Helper_failed_update));
					}				
				}
			}		
							
            $avatar = CMedia::getImage($model->logo,$model->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));
			
			$params_model = array(		
				'model'=>$model,	
				'links'=>array(
				   t("All Merchant")=>array(Yii::app()->controller->id.'/list'),        
				   $model->isNewRecord?t("Add new"):t("Edit Merchant"),
				   isset($model->restaurant_name)?stripslashes(ucwords($model->restaurant_name)):''
				),	    		   
			);	
			
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantInfoMenu;		   			   
			   $menu->merchant_id = $id;
			   $menu->init();    
			}
						
			$this->render("//tpl/submenu_tpl",array(		    
				'template_name'=>"//vendor/featured",
				'widget'=>'WidgetMerchantInfoMenu',		
				'avatar'=>$avatar,
				'params'=>$params_model,
				'custom_link'=>CHtml::link('Auto login',Yii::app()->createUrl("vendor/autologin",[
					'merchant_uuid'=>$model->merchant_uuid,
				]),[
					'class'=>"btn btn-link",
					'target'=>"_blank"
				]),
				'menu'=>$menu,
				'params_widget'=>array(			   
				   'merchant_id'=>$id,
				   'merchant_type'=>$model->merchant_type,
				   'self_delivery'=>$model->self_delivery
				)
			));		
			
		} else $this->render("error");
	}
	
	public function actionaddress()
	{
		$this->setMenuActive();		
		$this->pageTitle = t("Edit Merchant - Address");
		
		$id = (integer) Yii::app()->input->get('id');		
		$model = AR_merchant::model()->findByPk( $id );
		if($model){
			
			$model->scenario='address';

			if(isset($_POST['AR_merchant'])){
		       $model->attributes=$_POST['AR_merchant'];				       
			    if($model->validate()){						    				    				    				    
			    	if($model->save()){						    					    	
						Yii::app()->user->setFlash('success', t(Helper_success) );
						$this->refresh();						
					} else {					
						Yii::app()->user->setFlash('error',CommonUtility::t(Helper_failed_update));
					}				
					} else Yii::app()->user->setFlash('error',CommonUtility::t(HELPER_CORRECT_FORM));
			}		

			//$country_list = require_once 'CountryCode.php';
						
            $avatar = CMedia::getImage($model->logo,$model->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));
			
			$params_model = array(		
				'model'=>$model,	
				'unit'=>AttributesTools::unit(),		
				'zone_type'=>AttributesTools::zoneType(),
				'links'=>array(
				   t("All Merchant")=>array(Yii::app()->controller->id.'/list'),        
				   $model->isNewRecord?t("Add new"):t("Edit Merchant"),
				   isset($model->restaurant_name)?stripslashes(ucwords($model->restaurant_name)):''
				),	    		   
			);	
			
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantInfoMenu;		   			   
			   $menu->merchant_id = $id;
			   $menu->init();    
			}
						
			$this->render("//tpl/submenu_tpl",array(		    
				'template_name'=>"address",
				'widget'=>'WidgetMerchantInfoMenu',		
				'avatar'=>$avatar,
				'params'=>$params_model,
				'custom_link'=>CHtml::link('Auto login',Yii::app()->createUrl("vendor/autologin",[
					'merchant_uuid'=>$model->merchant_uuid,
				]),[
					'class'=>"btn btn-link",
					'target'=>"_blank"
				]),
				'menu'=>$menu,
				'params_widget'=>array(			   
				   'merchant_id'=>$id,
				   'merchant_type'=>$model->merchant_type,
				   'self_delivery'=>$model->self_delivery
				)
			));		
			
		} else $this->render("error");
	}
	
	public function actionzone()
	{
		$this->setMenuActive();		
		$this->pageTitle = t("Edit Merchant - Zone");
		
		$id = (integer) Yii::app()->input->get('id');		
		$model = AR_merchant::model()->findByPk( $id );
		$meta_name = 'zone';
		
		if($model){
			
			$avatar = CMedia::getImage($model->logo,$model->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));
		    
		    $models = new AR_merchant_meta;
		   
		    if(isset($_POST['AR_merchant_meta'])){
		    			    	
		    	AR_merchant_meta::model()->deleteAll('merchant_id=:merchant_id AND meta_name=:meta_name',array(
				 ':merchant_id'=> $id,
				 ':meta_name'=>$meta_name
				));
				
		    	$post = Yii::app()->input->xssClean($_POST); 
		    	$zone = isset($post['AR_merchant_meta']['zone'])?$post['AR_merchant_meta']['zone']:'';
		    	if(is_array($zone) && count($zone)>=1){
		    		foreach ($zone as $zone_id) {
		    			$meta = new AR_merchant_meta;
		    			$meta->merchant_id = intval($id);
		    			$meta->meta_name = $meta_name;
		    			$meta->meta_value = intval($zone_id);
		    			$meta->save();
		    		}		    		
		    	}	
		    	Yii::app()->user->setFlash('success', t(Helper_success) );
				$this->refresh();							    	
		    } else if ( isset($_POST['yt0']) ) {
		    	AR_merchant_meta::model()->deleteAll('merchant_id=:merchant_id AND meta_name=:meta_name',array(
				 ':merchant_id'=> $id,
				 ':meta_name'=>$meta_name
				));		   		    	
				Yii::app()->user->setFlash('success', t(Helper_success) );
				$this->refresh();							    	
		    }
		    
		    $zone_data = CommonUtility::getDataToDropDown("{{merchant_meta}}",'meta_value','meta_value',"where merchant_id=".q($id)." AND meta_name='zone' " );		    
		    $models->zone = (array)$zone_data;
		    
			$params_model = array(		
				'model'=>$models,				
				'links'=>array(
				   t("All Merchant")=>array(Yii::app()->controller->id.'/list'),        
				   $model->isNewRecord?t("Add new"):t("Edit Merchant"),
				   isset($model->restaurant_name)?stripslashes(ucwords($model->restaurant_name)):''
				),	    		   
				'zone_list'=>CommonUtility::getDataToDropDown("{{zones}}",'zone_id','zone_name','where merchant_id=0',"Order by zone_name asc"),				
			);	
								
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantInfoMenu;		   			   
			   $menu->merchant_id = $id;
			   $menu->init();    
			}
						
			$this->render("//tpl/submenu_tpl",array(		    
				'template_name'=>"zone",
				'widget'=>'WidgetMerchantInfoMenu',		
				'avatar'=>$avatar,
				'params'=>$params_model,
				'custom_link'=>CHtml::link('Auto login',Yii::app()->createUrl("vendor/autologin",[
					'merchant_uuid'=>$model->merchant_uuid,
				]),[
					'class'=>"btn btn-link",
					'target'=>"_blank"
				]),
				'menu'=>$menu,
				'params_widget'=>array(			   
				   'merchant_id'=>$id,
				   'merchant_type'=>$model->merchant_type,
				   'self_delivery'=>$model->self_delivery
				),				
			));		
			
		} else $this->render("error");
	}
	
	public function actionpayment_history()
	{
		$this->setMenuActive();		
		$this->pageTitle = t("Payment history");		
		
		$id = (integer) Yii::app()->input->get('id');		
		$model = AR_merchant::model()->findByPk( $id );
		if($model){
			
			$action_name='payment_history';
			$delete_link = Yii::app()->CreateUrl(Yii::app()->controller->id."/delete");
			
			ScriptUtility::registerScript(array(
			  "var action_name='$action_name';",
			  "var delete_link='$delete_link';",
			),'action_name');
									
            $avatar = CMedia::getImage($model->logo,$model->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));
			
			/*if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantInfoMenu;		   			   
			   $menu->merchant_id = $id;
			   $menu->init();    
			   $tpl = '//tpl/lazy_list';
			} else $tpl = "//vendor/payment_history";*/
			
			$table_col = array(		  			 
			 'created'=>array(
			    'label'=>t("Created"),
			    'width'=>'20%'
			  ),			 
			  'payment_code'=>array(
			    'label'=>t("Payment"),
			    'width'=>'10%'
			  ),		  
			  'invoice_ref_number'=>array(
			    'label'=>t("Invoice #"),
			    'width'=>'20%'
			  ),		  
			  'package_id'=>array(
			    'label'=>t("Plan"),
			    'width'=>'20%'
			  ),		  
			);
			$columns = array(		  			  
			  array('data'=>'created'),			  
			  array('data'=>'payment_code'),		  
			  array('data'=>'invoice_ref_number'),		  
			  array('data'=>'package_id','orderable'=>false),		  
			);		
			
			$params_model = array(		
				'model'=>$model,	
				'links'=>array(
				   t("All Merchant")=>array(Yii::app()->controller->id.'/list'),        
				   $model->isNewRecord?t("Add new"):t("Edit Merchant"),
				   isset($model->restaurant_name)?stripslashes(ucwords($model->restaurant_name)):''
				),	    		  
				'table_col'=>$table_col,
			    'columns'=>$columns,
			    'order_col'=>1,
	            'sortby'=>'desc', 
	            'merchant_id'=>$id,
	            'ajax_url'=>Yii::app()->createUrl("/api")
			);							
						
			$this->render("//tpl/submenu_tpl",array(		    
				'template_name'=>'payment_history',
				'widget'=>'WidgetMerchantInfoMenu',		
				'avatar'=>$avatar,
				'params'=>$params_model,
				'custom_link'=>CHtml::link('Auto login',Yii::app()->createUrl("vendor/autologin",[
					'merchant_uuid'=>$model->merchant_uuid,
				]),[
					'class'=>"btn btn-link",
					'target'=>"_blank"
				]),
				'menu'=>array(),
				'params_widget'=>array(			   
				   'merchant_id'=>$id,
				   'merchant_type'=>$model->merchant_type,
				   'self_delivery'=>$model->self_delivery
				),				
			));		
					
		} else $this->render("error");
	}
	
	public function actionpayment_settings()
	{
		$this->setMenuActive();		
		$this->pageTitle = t("Merchant - Payment Settings");
		
		$id = (integer) Yii::app()->input->get('id');		
		$model = AR_merchant::model()->findByPk( $id );
		if($model){
			
			if(isset($_POST['AR_merchant'])){
		        $model->attributes=$_POST['AR_merchant'];					
			    if($model->validate()){						    				    				    	
			    	if($model->save()){						    					    	
						Yii::app()->user->setFlash('success', t(Helper_success) );
						$this->refresh();						
					} else {					
						Yii::app()->user->setFlash('error',CommonUtility::t(Helper_failed_update));
					}				
				} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString($model->getErrors(),"<br/>") );
			}		
			
			if($payment_gateway = MerchantTools::getMerchantMeta($model->merchant_id,'payment_gateway')){			
				$model->payment_gateway=$payment_gateway;
			}		
			
			$avatar = CMedia::getImage($model->logo,$model->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));

			$params_model = array(		
				'model'=>$model,	
				'links'=>array(
				   t("All Merchant")=>array(Yii::app()->controller->id.'/list'),        
				   $model->isNewRecord?t("Add new"):t("Edit Merchant"),
				   isset($model->restaurant_name)?stripslashes(ucwords($model->restaurant_name)):''
				),	    		   
				'provider'=>AttributesTools::PaymentProvider()
			);	
			
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantInfoMenu;		   			   
			   $menu->merchant_id = $id;
			   $menu->init();    
			}
						
			$this->render("//tpl/submenu_tpl",array(		    
				'template_name'=>"//vendor/payment_settings",
				'widget'=>'WidgetMerchantInfoMenu',		
				'avatar'=>$avatar,
				'params'=>$params_model,
				'custom_link'=>CHtml::link('Auto login',Yii::app()->createUrl("vendor/autologin",[
					'merchant_uuid'=>$model->merchant_uuid,
				]),[
					'class'=>"btn btn-link",
					'target'=>"_blank"
				]),
				'menu'=>$menu,
				'params_widget'=>array(			   
				   'merchant_id'=>$id,
				   'merchant_type'=>$model->merchant_type,
				   'self_delivery'=>$model->self_delivery
				)
			));		
		} else $this->render("error");
	}	
	
	public function actionothers()
	{
		$this->setMenuActive();		
		$this->pageTitle = t("Merchant - Others");
		
		$id = (integer) Yii::app()->input->get('id');		
		$merchant = AR_merchant::model()->findByPk( $id );
		if($merchant){
			
			$model=new AR_option;
		    $model->scenario = "settings";			
										
            $avatar = CMedia::getImage($merchant->logo,$merchant->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));


			$options = array(
				'enabled_private_menu','merchant_two_flavor_option','merchant_tax_number',
				'merchant_extenal','merchant_enabled_voucher',
				'merchant_enabled_tip','merchant_default_tip',
				'merchant_close_store','merchant_disabled_ordering',
				'tips_in_transactions','merchant_tip_type','merchant_enabled_language','merchant_default_language','merchant_default_currency',
				'self_delivery','merchant_default_currency','merchant_disabled_pos_earnings','merchant_enabled_auto_accept_order',
				'merchant_time_selection','merchant_enabled_age_verification','merchant_enabled_whatsapp','auto_print_status',
				'merchant_default_preparation_time','merchant_whatsapp_phone_number',
				'enabled_barcode','enabled_barcode_search'
			);

			if($data = OptionsTools::find($options,$id)){
				foreach ($data as $name=>$val) {
					$model[$name]=$val;
				}			
			}

			if(isset($_POST['AR_option'])){
				$model->attributes=$_POST['AR_option'];
				if(DEMO_MODE && in_array($id,DEMO_MERCHANT)){
					$this->render('//tpl/error',array(  
						 'error'=>array(
						   'message'=>t("Modification not available in demo")
						 )
					   ));	
				   return false;
			   }		
			   if($model->validate()){
				 OptionsTools::$merchant_id = $id;		
				 $model->tips_in_transactions = json_encode($model->tips_in_transactions);	
				 if(OptionsTools::save($options, $model, $id)){
					Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
					$merchant->close_store = intval($model->merchant_close_store);					
					$merchant->self_delivery = intval($model->self_delivery);					
					$merchant->save();		
					$this->refresh();				
				 }
			   }						   
			}
			
			$model->merchant_close_store = $merchant->close_store;
			if(!empty($model->tips_in_transactions)){
				$model->tips_in_transactions = json_decode($model->tips_in_transactions,true);
			}
					
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantInfoMenu;		   			   
			   $menu->merchant_id = $id;
			   $menu->init();    
			}

			$service_list = array();
			try {
				$serviceList = CCheckout::getMerchantTransactionList($id,Yii::app()->language);
				foreach ($serviceList as $key => $value) {
					$service_list[$value['service_code']] = $value['service_name'];
				}				
			} catch (Exception $e) {
				//
			}	
			
			$tips = AttributesTools::Tips();
			$tips=array("0"=>t("Please select")) + $tips; 

			$tip_type = AttributesTools::TipType();
			
			$currency_list = AttributesTools::CurrencyList();					
			$select = [''=>t("Please select")];
			$currency_list = $select+$currency_list;	
			
			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
            $multicurrency_enabled = $multicurrency_enabled==1?true:false;					

			$allowed_merchant_choose_currency = isset(Yii::app()->params['settings']['multicurrency_allowed_merchant_choose_currency'])?Yii::app()->params['settings']['multicurrency_allowed_merchant_choose_currency']:false;
            $allowed_merchant_choose_currency = $allowed_merchant_choose_currency==1?true:false;					

			$status_list = AttributesTools::getOrderStatus(Yii::app()->language);
		    $new_status = ['none' => t("Please select") ];
		    $status_list = array_merge($new_status, $status_list);
						
			$this->render("//tpl/submenu_tpl",array(		    
				'template_name'=>"//vendor/others",
				'widget'=>'WidgetMerchantInfoMenu',		
				'avatar'=>$avatar,				
				'menu'=>$menu,
				'params_widget'=>array(			   
				   'merchant_id'=>$id,
				   'merchant_type'=>$merchant->merchant_type,
				   'self_delivery'=>$model->self_delivery
				),
				'params'=>array(
				  'model'=>$model,	
					'links'=>array(
					   t("All Merchant")=>array(Yii::app()->controller->id.'/list'),        
					   $merchant->isNewRecord?t("Add new"):t("Edit Merchant"),
					   isset($merchant->restaurant_name)?stripslashes(ucwords($merchant->restaurant_name)):''
					),	    
					'food_option_listing'=>AttributesTools::foodOptionsListing(),
				   'two_flavor_options'=>AttributesTools::twoFlavorOptions(),
				   'unit'=>AttributesTools::unit(),	
				   'zone_type'=>AttributesTools::zoneType(),
				   'tips'=>$tips,
				   'service_list'=>$service_list,
				   'tip_type'=>$tip_type,	
				   'currency_list'=>$currency_list,
				   'multicurrency_enabled'=>$multicurrency_enabled,
				   'allowed_merchant_choose_currency'=>$allowed_merchant_choose_currency,
				   'status_list'=>$status_list
				),
				'custom_link'=>CHtml::link('Auto login',Yii::app()->createUrl("vendor/autologin",[
					'merchant_uuid'=>$merchant->merchant_uuid,
				]),[
					'class'=>"btn btn-link",
					'target'=>"_blank"
				]),
			));						
		} else $this->render("error");
	}
	
	public function actionaccess()
	{
		$this->setMenuActive();		
		$this->pageTitle = t("Merchant - Access");
		
		$id = (integer) Yii::app()->input->get('id');		
		$model = AR_merchant::model()->findByPk( $id );
		if($model){					
									
            $avatar = CMedia::getImage($model->logo,$model->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));
			
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantInfoMenu;		   			   
			   $menu->merchant_id = $id;
			   $menu->init();    
			}
						
			$this->render("//tpl/submenu_tpl",array(		    
				'template_name'=>"//vendor/access",
				'widget'=>'WidgetMerchantInfoMenu',		
				'avatar'=>$avatar,				
				'menu'=>$menu,
				'params_widget'=>array(			   
				   'merchant_id'=>$id
				),
				'params'=>array(
				  'model'=>$model,	
					'links'=>array(
					   t("All Merchant")=>array(Yii::app()->controller->id.'/list'),        
					   $model->isNewRecord?t("Add new"):t("Edit Merchant"),
					   isset($model->restaurant_name)?stripslashes(ucwords($model->restaurant_name)):''
					),	    		   
				)
			));						
		} else $this->render("error");
	}
	
	public function actiondelete()
	{
				
		if(DEMO_MODE){
			$this->render('//tpl/error',array(  
				'error'=>array(
				'message'=>t("Modification not available in demo")
				)
			));	
		    return false;
		}

		$id = (integer) Yii::app()->input->get('id');		
		$model = AR_merchant::model()->findByPk( $id );
		if($model){
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array(Yii::app()->controller->id.'/list'));			
		} else $this->render("error");
	}
	
	public function actioncsvlist()
	{		
		$this->pageTitle=t("All CSV Upload");
		$action_name='csv_list';
		$delete_link = Yii::app()->CreateUrl(Yii::app()->controller->id."/delete_csv");
		
		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
		),'action_name');
		
		if(Yii::app()->params['isMobile']==TRUE){
			$tpl = 'csv_list_app';
		} else $tpl = 'csv_list';
		
		
		$this->render($tpl,array(
		  'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/csv_upload")
		));		
	}
	
	public function actioncsv_upload()
	{
		$this->setMenuActive(".vendor_csvlist");
		$this->pageTitle = t("Upload");
		
		
		$model=new AR_csv;
		if(isset($_POST['AR_csv'])){
			$model->attributes=$_POST['AR_csv'];
			
			if($model->validate()){		
				$model->image=CUploadedFile::getInstance($model,'image');
				$model->filename =  $model->image->name;
				$extension = substr($model->image->name,-3,3);		
				$path = CommonUtility::uploadDestination('csv')."/".$model->filename;
				$model->file_path = $path;						
				$model->image->saveAs( $path );
				if($model->save()){										
					$this->redirect(array(Yii::app()->controller->id.'/csvlist'));		
				} else {
					Yii::app()->user->setFlash('error',CommonUtility::t(Helper_failed_update));
				}
			}
		}
		
		$this->render("csv_upload",array(		  
		  'model'=>$model,
		  'links'=>array(
			    'links'=>array(
			        t("All CSV Upload")=>array(Yii::app()->controller->id.'/csvlist'),        
			        $this->pageTitle,
			    ),
			    'homeLink'=>false,
			    'separator'=>'<span class="separator">
			    <i class="zmdi zmdi-chevron-right"></i><i class="zmdi zmdi-chevron-right"></i></span>'
			)
	    ));
	}
	
	public function actioncsv_view()
	{				
		$this->setMenuActive(".vendor_csvlist");
		$this->pageTitle=t("CSV details");
				
		$action_name='csv_list_details';
		$delete_link = Yii::app()->CreateUrl(Yii::app()->controller->id."/delete_csv_details");
		
		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
		),'action_name');
		
		$id = (integer) Yii::app()->input->get('id');
		$model = AR_csv::model()->findByPk( $id );
		
		if($model){
			
			if(Yii::app()->params['isMobile']==TRUE){
				$tpl = 'csv_list_details_app';
			} else $tpl = 'csv_list_details';
				
			$this->render( $tpl ,array(
			  'id'=>$id,
			  'back_link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/csvlist"),
			  'links'=>array(
				    'links'=>array(
				        t("All CSV Upload")=>array(Yii::app()->controller->id.'/csvlist'),        
				        t("View"),
				        t("#[id]",array('[id]'=>$id))
				    ),
				    'homeLink'=>false,
				    'separator'=>'<span class="separator">
				    <i class="zmdi zmdi-chevron-right"></i><i class="zmdi zmdi-chevron-right"></i></span>'
				)			  
			));		
		} else $this->render("error");
	}
	
	public function actiondelete_csv()
	{
		$id = (integer) Yii::app()->input->get('id');		
		$model = AR_csv::model()->findByPk( $id );
		if($model){
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array(Yii::app()->controller->id.'/csvlist'));			
		} else $this->render("error");
	}
	
	public function actionSponsored()
	{
		$this->pageTitle=t("All Sponsored");
		$action_name='sponsored_list';
		$delete_link = Yii::app()->CreateUrl(Yii::app()->controller->id."/delete_sponsored");
		
		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
		),'action_name');
		
		if(Yii::app()->params['isMobile']==TRUE){
			$tpl = 'sponsored_list_app';
		} else $tpl = 'sponsored_list';
		
		
		$this->render($tpl,array(
		  'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/create_sponsored")
		));		
	}
	
	public function actioncreate_sponsored()
	{		
		$this->setMenuActive(".vendor_sponsored");
		$this->pageTitle=t("Add sponsored");
		
		$model=new AR_sponsored;		
		if(isset($_POST['AR_sponsored'])){
			$model->attributes=$_POST['AR_sponsored'];
			if($model->validate()){				
				$id = (integer) $model->merchant_id;		
				$model2 = AR_sponsored::model()->findByPk( $id );					
				$model2->is_sponsored = 2;					
				$model2->sponsored_expiration = $model->sponsored_expiration; 				
				if($model2->save()){
					Yii::app()->user->setFlash('success',t(Helper_success));
					$this->redirect(array(Yii::app()->controller->id.'/sponsored'));					
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			}
		}
				
		$this->render("sponsored_add",array(
		   'model'=>$model,
		   'merchant_list'=>AttributesTools::MerchantList(),
		   'selected_merchant'=>array(),
		   'links'=>array(
			    'links'=>array(
			        t("All Sponsored")=>array(Yii::app()->controller->id.'/sponsored'),        
			        $this->pageTitle,
			    ),
			    'homeLink'=>false,
			    'separator'=>'<span class="separator">
			    <i class="zmdi zmdi-chevron-right"></i><i class="zmdi zmdi-chevron-right"></i></span>'
			)
		));	
	}
	
	public function actionedit_sponsored()
	{
		$this->setMenuActive(".vendor_sponsored");
		$this->pageTitle=t("Add sponsored");
		$selected_merchant = array();
		
		$id = (integer) Yii::app()->input->get('id');				
		$model = AR_sponsored::model()->findByPk( $id );				
		if(!$model){				
			$this->render("error");				
			Yii::app()->end();
		}	
		
		if($model->is_sponsored==2){
			$selected_merchant[$model->merchant_id] = stripslashes($model->restaurant_name);
		}
				
		if(isset($_POST['AR_sponsored'])){
			$model->attributes=$_POST['AR_sponsored'];			
			if($model->validate()){				
				if($model->save()){
					Yii::app()->user->setFlash('success',t(Helper_update));
					$this->refresh();					
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			}
		}
				
		$this->render("sponsored_add",array(
		   'model'=>$model,
		   'merchant_list'=>AttributesTools::MerchantList(),
		   'selected_merchant'=>$selected_merchant,
		   'links'=>array(
			    'links'=>array(
			        t("All Sponsored")=>array(Yii::app()->controller->id.'/sponsored'),        
			        $this->pageTitle,
			    ),
			    'homeLink'=>false,
			    'separator'=>'<span class="separator">
			    <i class="zmdi zmdi-chevron-right"></i><i class="zmdi zmdi-chevron-right"></i></span>'
			)
		));	
	}
	
	public function actiondelete_sponsored()
	{
		$id = (integer) Yii::app()->input->get('id');		
		$model = AR_sponsored::model()->findByPk( $id );
		if($model){
			$model->is_sponsored = 1;
			$model->save();
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array(Yii::app()->controller->id.'/sponsored'));			
		} else $this->render("error");
	}

	public function actionapi_access()
	{

		try {
			ItemIdentity::addonIdentity('Single app');
		} catch (Exception $e) {			
			try {
			    ItemIdentity::addonIdentity('Single Mobile App');
			} catch (Exception $e) {
				$this->render('//tpl/error',[
					'error'=>[
						'message'=>$e->getMessage()
					]
				]);
				Yii::app()->end();
			}
		}
				
		$this->setMenuActive();		
		$this->pageTitle = t("Merchant - API Access");
		$jwt_token = AttributesTools::JwtTokenID();
		
		$id = (integer) Yii::app()->input->get('id');		
		$model = AR_merchant::model()->findByPk( $id );
		if($model){
			
			$model2 = AR_merchant_meta::model()->find("merchant_id=:merchant_id AND meta_name=:meta_name",[
				':merchant_id'=>intval($id),
				':meta_name'=>$jwt_token
			]);
			if(!$model2){
				$model2 = new  AR_merchant_meta;
			}			

			$model2->scenario = 'create_api_access';
			
			if(isset($_POST['AR_merchant_meta'])){				
		        $model2->attributes=$_POST['AR_merchant_meta'];			       		       		        
				$model2->merchant_id = $id;
				$model2->meta_name = $jwt_token;				
			    if($model2->validate()){		
					$payload = [
						'iss'=>Yii::app()->request->getServerName(),
						'sub'=>$id,
						'aud'=>$model2->website_url,
						'iat'=>time(),						
					];					
					$jwt = JWT::encode($payload, CRON_KEY, 'HS256');					
					$model2->meta_value = $jwt;					
			    	if($model2->save()){						    					    	
						Yii::app()->user->setFlash('success', t(Helper_success) );
						$this->refresh();						
					} else {					
						Yii::app()->user->setFlash('error',CommonUtility::t(Helper_failed_update));
					}				
				} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString($model2->getErrors(),"<br/>") );
			}		
										
            $avatar = CMedia::getImage($model->logo,$model->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));
					
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantInfoMenu;		   			   
			   $menu->merchant_id = $id;
			   $menu->init();    
			}
						
			$this->render("//tpl/submenu_tpl",array(		    
				'template_name'=>"//vendor/api_access",
				'widget'=>'WidgetMerchantInfoMenu',		
				'avatar'=>$avatar,				
				'menu'=>$menu,				
				'params_widget'=>array(			   
				   'merchant_id'=>$id,
				   'merchant_type'=>$model->merchant_type,
				   'self_delivery'=>$model->self_delivery
				),
				'params'=>array(
				   'id'=>$id,
				  'model'=>$model2,	
					'links'=>array(
					   t("All Merchant")=>array(Yii::app()->controller->id.'/list'),        
					   $model->isNewRecord?t("Add new"):t("Edit Merchant"),
					   isset($model->restaurant_name)?stripslashes(ucwords($model->restaurant_name)):''
					),	    		   
				),
				'custom_link'=>CHtml::link('Auto login',Yii::app()->createUrl("vendor/autologin",[
					'merchant_uuid'=>$model->merchant_uuid,
				]),[
					'class'=>"btn btn-link",
					'target'=>"_blank"
				]),
			));						
		} else $this->render("error");
	}

	public function actiondelete_apikeys()
	{
		$jwt_token = AttributesTools::JwtTokenID();
		$id = (integer) Yii::app()->input->get('id');
		
		if(DEMO_MODE && in_array($id,DEMO_MERCHANT)){
			$this->render('//tpl/error',array(  
				 'error'=>array(
				   'message'=>t("Modification not available in demo")
				 )
			   ));	
		   return false;
	   }
	   
		$model = AR_merchant_meta::model()->find("merchant_id=:merchant_id AND meta_name=:meta_name",[
			':merchant_id'=>intval($id),
			':meta_name'=>$jwt_token
		]);
		if($model){
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array(Yii::app()->controller->id.'/api_access','id'=>$id ));			
		} else $this->render("error");
	}

	public function actionsearch_mode()
	{
				
		$this->setMenuActive();		
		$this->pageTitle = t("Merchant - Search Mode");
		$jwt_token = AttributesTools::JwtTokenID();
		
		$id = (integer) Yii::app()->input->get('id');		
		$model = AR_merchant::model()->findByPk( $id );
		if($model){
			
			$avatar = CMedia::getImage($model->logo,$model->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));

			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantInfoMenu;		   			   
			   $menu->merchant_id = $id;
			   $menu->init();    
			}
													
			$model2 = new AR_option;

			$options = array(
				'merchant_set_default_country'
			 );
					 
			 if($data = OptionsTools::find($options,$id)){
				 foreach ($data as $name=>$val) {
					 $model2[$name]=$val;
				 }			
			 }
				 
			 if(isset($_POST['AR_option'])){
				 if(DEMO_MODE && in_array($id,DEMO_MERCHANT)){
					  $this->render('//tpl/error',array(  
						   'error'=>array(
							 'message'=>t("Modification not available in demo")
						   )
						 ));	
					 return false;
				 }
				 $model2->attributes=$_POST['AR_option'];				
				 if($model2->validate()){				
					 OptionsTools::$merchant_id = $id;					
					 if(OptionsTools::save($options, $model2, $id)){
						 Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
						 $this->refresh();
					 } else Yii::app()->user->setFlash('error',t(Helper_failed_update));
				 } 
			 }
           
						
			$this->render("//tpl/submenu_tpl",array(		    
				'template_name'=>"//merchant/search_settings",
				'widget'=>'WidgetMerchantInfoMenu',		
				'avatar'=>$avatar,				
				'menu'=>$menu,				
				'params_widget'=>array(			   
				   'merchant_id'=>$id,
				   'merchant_type'=>$model->merchant_type,
				   'self_delivery'=>$model->self_delivery
				),
				'custom_link'=>CHtml::link('Auto login',Yii::app()->createUrl("vendor/autologin",[
					'merchant_uuid'=>$model->merchant_uuid,
				]),[
					'class'=>"btn btn-link",
					'target'=>"_blank"
				]),
				'params'=>array(
				  'id'=>$id,
				  'model'=>$model2,	
				  'country_list'=>AttributesTools::CountryList(),		   
					'links'=>array(
					   t("All Merchant")=>array(Yii::app()->controller->id.'/list'),        
					   $model->isNewRecord?t("Add new"):t("Edit Merchant"),
					   isset($model->restaurant_name)?stripslashes(ucwords($model->restaurant_name)):''
					),	    		   
				)
			));						
		} else $this->render("error");
	}	

	public function actionlogin_sigup()
	{				
		$this->setMenuActive();		
		$this->pageTitle = t("Merchant - Login & Signup");
		$jwt_token = AttributesTools::JwtTokenID();
		
		$id = (integer) Yii::app()->input->get('id');		
		$model = AR_merchant::model()->findByPk( $id );
		if($model){
			
			$avatar = CMedia::getImage($model->logo,$model->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));
								
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantInfoMenu;		   			   
			   $menu->merchant_id = $id;
			   $menu->init();    
			}
													
			$model2 = new AR_option;

			$options = array(
				'merchant_signup_enabled_verification','merchant_signup_resend_counter','merchant_signup_enabled_terms',
				'merchant_signup_terms','merchant_enabled_guest'
			 );
					 
			 if($data = OptionsTools::find($options,$id)){
				 foreach ($data as $name=>$val) {
					 $model2[$name]=$val;
				 }			
			 }
				 
			 if(isset($_POST['AR_option'])){
				 if(DEMO_MODE && in_array($id,DEMO_MERCHANT)){
					  $this->render('//tpl/error',array(  
						   'error'=>array(
							 'message'=>t("Modification not available in demo")
						   )
						 ));	
					 return false;
				 }
				 $model2->attributes=$_POST['AR_option'];				
				 if($model2->validate()){				
					 OptionsTools::$merchant_id = $id;					
					 if(OptionsTools::save($options, $model2, $id)){
						 Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
						 $this->refresh();
					 } else Yii::app()->user->setFlash('error',t(Helper_failed_update));
				 } 
			 }
           
						
			$this->render("//tpl/submenu_tpl",array(		    
				'template_name'=>"//merchant/login_signup",
				'widget'=>'WidgetMerchantInfoMenu',		
				'avatar'=>$avatar,				
				'menu'=>$menu,				
				'params_widget'=>array(			   
				   'merchant_id'=>$id,
				   'merchant_type'=>$model->merchant_type,
				   'self_delivery'=>$model->self_delivery
				),
				'custom_link'=>CHtml::link('Auto login',Yii::app()->createUrl("vendor/autologin",[
					'merchant_uuid'=>$model->merchant_uuid,
				]),[
					'class'=>"btn btn-link",
					'target'=>"_blank"
				]),
				'params'=>array(
				  'id'=>$id,
				  'model'=>$model2,					  
					'links'=>array(
					   t("All Merchant")=>array(Yii::app()->controller->id.'/list'),        
					   $model->isNewRecord?t("Add new"):t("Edit Merchant"),
					   isset($model->restaurant_name)?stripslashes(ucwords($model->restaurant_name)):''
					),	    		   
				)
			));						
		} else $this->render("error");
	}		

	public function actionphone_settings()
	{				
		$this->setMenuActive();		
		$this->pageTitle = t("Merchant - Phone Settings");
		$jwt_token = AttributesTools::JwtTokenID();
		
		$id = (integer) Yii::app()->input->get('id');		
		$model = AR_merchant::model()->findByPk( $id );
		if($model){
			
			$avatar = CMedia::getImage($model->logo,$model->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));
								
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantInfoMenu;		   			   
			   $menu->merchant_id = $id;
			   $menu->init();    
			}
													
			$model2 = new AR_option;

			$options = array(
				'merchant_mobilephone_settings_country','merchant_mobilephone_settings_default_country'
			 );
					 
			 if($data = OptionsTools::find($options,$id)){
				 foreach ($data as $name=>$val) {
					 $model2[$name]=$val;
				 }			
			 }
				 
			 if(isset($_POST['AR_option'])){
				 if(DEMO_MODE && in_array($id,DEMO_MERCHANT)){
					  $this->render('//tpl/error',array(  
						   'error'=>array(
							 'message'=>t("Modification not available in demo")
						   )
						 ));	
					 return false;
				 }
				 $model2->attributes=$_POST['AR_option'];				
				 if($model2->validate()){				
					 OptionsTools::$merchant_id = $id;					
					 if(OptionsTools::save($options, $model2, $id)){
						 Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
						 $this->refresh();
					 } else Yii::app()->user->setFlash('error',t(Helper_failed_update));
				 } 
			 }
           
						
			$this->render("//tpl/submenu_tpl",array(		    
				'template_name'=>"//merchant/phone_settings",
				'widget'=>'WidgetMerchantInfoMenu',		
				'avatar'=>$avatar,				
				'menu'=>$menu,				
				'params_widget'=>array(			   
				   'merchant_id'=>$id,
				   'merchant_type'=>$model->merchant_type,
				   'self_delivery'=>$model->self_delivery
				),
				'custom_link'=>CHtml::link('Auto login',Yii::app()->createUrl("vendor/autologin",[
					'merchant_uuid'=>$model->merchant_uuid,
				]),[
					'class'=>"btn btn-link",
					'target'=>"_blank"
				]),
				'params'=>array(
				  'id'=>$id,
				  'model'=>$model2,	
				  'country_list'=>AttributesTools::CountryList(),		   
					'links'=>array(
					   t("All Merchant")=>array(Yii::app()->controller->id.'/list'),        
					   $model->isNewRecord?t("Add new"):t("Edit Merchant"),
					   isset($model->restaurant_name)?stripslashes(ucwords($model->restaurant_name)):''
					),	    		   
				)
			));						
		} else $this->render("error");
	}			

	public function actionsocial_settings()
	{				
		$this->setMenuActive();		
		$this->pageTitle = t("Merchant - Social Settings");
		$jwt_token = AttributesTools::JwtTokenID();
		
		$id = (integer) Yii::app()->input->get('id');		
		$model = AR_merchant::model()->findByPk( $id );
		if($model){
			
			$avatar = CMedia::getImage($model->logo,$model->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));
								
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantInfoMenu;		   			   
			   $menu->merchant_id = $id;
			   $menu->init();    
			}
													
			$model2 = new AR_option;

			$options = array(
			 'facebook_page','twitter_page','google_page','instagram_page',
			 'merchant_fb_flag','merchant_fb_app_id','merchant_fb_app_secret','merchant_google_login_enabled','merchant_google_client_id','merchant_google_client_secret',
			 'merchant_app_enabled_apple_login','merchant_app_apple_app_id','merchant_app_apple_team_id','merchant_app_apple_key_id','merchant_app_apple_service_file',
			 'merchant_fb_client_token'
			);
					 
			 if($data = OptionsTools::find($options,$id)){
				 foreach ($data as $name=>$val) {
					 $model2[$name]=$val;
				 }			
			 }
				 
			 if(isset($_POST['AR_option'])){
				 if(DEMO_MODE && in_array($id,DEMO_MERCHANT)){
					  $this->render('//tpl/error',array(  
						   'error'=>array(
							 'message'=>t("Modification not available in demo")
						   )
						 ));	
					 return false;
				 }
				 $model2->attributes=$_POST['AR_option'];				
				 if($model2->validate()){				
					 OptionsTools::$merchant_id = $id;					
					 if(OptionsTools::save($options, $model2, $id)){
						 Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
						 $this->refresh();
					 } else Yii::app()->user->setFlash('error',t(Helper_failed_update));
				 } 
			 }
           
						
			$this->render("//tpl/submenu_tpl",array(		    
				'template_name'=>"//merchant/social_settings",
				'widget'=>'WidgetMerchantInfoMenu',		
				'avatar'=>$avatar,				
				'menu'=>$menu,				
				'params_widget'=>array(			   
				   'merchant_id'=>$id,
				   'merchant_type'=>$model->merchant_type,
				   'self_delivery'=>$model->self_delivery
				),
				'custom_link'=>CHtml::link('Auto login',Yii::app()->createUrl("vendor/autologin",[
					'merchant_uuid'=>$model->merchant_uuid,
				]),[
					'class'=>"btn btn-link",
					'target'=>"_blank"
				]),
				'params'=>array(
				  'id'=>$id,
				  'model'=>$model2,					  
					'links'=>array(
					   t("All Merchant")=>array(Yii::app()->controller->id.'/list'),        
					   $model->isNewRecord?t("Add new"):t("Edit Merchant"),
					   isset($model->restaurant_name)?stripslashes(ucwords($model->restaurant_name)):''
					),	    		
					'web_redirect_uri'=>CommonUtility::getHomebaseUrl()."/partnerapi/apple_callback",
    		        'app_redirect_uri'=>CommonUtility::getHomebaseUrl()."/partnerapi/app_apple_callback",	      
				)
			));						
		} else $this->render("error");
	}			

	public function actionrecaptcha_settings()
	{				
		$this->setMenuActive();		
		$this->pageTitle = t("Merchant - Recaptcha Settings");
		$jwt_token = AttributesTools::JwtTokenID();
		
		$id = (integer) Yii::app()->input->get('id');		
		$model = AR_merchant::model()->findByPk( $id );
		if($model){
			
			$avatar = CMedia::getImage($model->logo,$model->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));
								
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantInfoMenu;		   			   
			   $menu->merchant_id = $id;
			   $menu->init();    
			}
													
			$model2 = new AR_option;

			$options = ['merchant_captcha_enabled','merchant_captcha_site_key','merchant_captcha_secret','merchant_captcha_lang'];
					 
			 if($data = OptionsTools::find($options,$id)){
				 foreach ($data as $name=>$val) {
					 $model2[$name]=$val;
				 }			
			 }
				 
			 if(isset($_POST['AR_option'])){
				 if(DEMO_MODE && in_array($id,DEMO_MERCHANT)){
					  $this->render('//tpl/error',array(  
						   'error'=>array(
							 'message'=>t("Modification not available in demo")
						   )
						 ));	
					 return false;
				 }
				 $model2->attributes=$_POST['AR_option'];				
				 if($model2->validate()){				
					 OptionsTools::$merchant_id = $id;					
					 if(OptionsTools::save($options, $model2, $id)){
						 Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
						 $this->refresh();
					 } else Yii::app()->user->setFlash('error',t(Helper_failed_update));
				 } 
			 }
           
						
			$this->render("//tpl/submenu_tpl",array(		    
				'template_name'=>"//vendor/recaptcha_settings",
				'widget'=>'WidgetMerchantInfoMenu',		
				'avatar'=>$avatar,				
				'menu'=>$menu,				
				'params_widget'=>array(			   
				   'merchant_id'=>$id,
				   'merchant_type'=>$model->merchant_type,
				   'self_delivery'=>$model->self_delivery
				),
				'custom_link'=>CHtml::link('Auto login',Yii::app()->createUrl("vendor/autologin",[
					'merchant_uuid'=>$model->merchant_uuid,
				]),[
					'class'=>"btn btn-link",
					'target'=>"_blank"
				]),
				'params'=>array(
				  'id'=>$id,
				  'model'=>$model2,					  
					'links'=>array(
					   t("All Merchant")=>array(Yii::app()->controller->id.'/list'),        
					   $model->isNewRecord?t("Add new"):t("Edit Merchant"),
					   isset($model->restaurant_name)?stripslashes(ucwords($model->restaurant_name)):''
					),	    		   
				)
			));						
		} else $this->render("error");
	}			

	public function actionmap_keys()
	{				
		$this->setMenuActive();		
		$this->pageTitle = t("Merchant - Map API Keys");
		$jwt_token = AttributesTools::JwtTokenID();
		
		$id = (integer) Yii::app()->input->get('id');		
		$model = AR_merchant::model()->findByPk( $id );
		if($model){
			
			$avatar = CMedia::getImage($model->logo,$model->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));
								
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantInfoMenu;		   			   
			   $menu->merchant_id = $id;
			   $menu->init();    
			}
													
			$model2 = new AR_option;

			$options = array(
			  'merchant_map_provider','merchant_google_geo_api_key','merchant_google_maps_api_key','merchant_mapbox_access_token',
			  'merchant_geolocationdb','merchant_google_maps_api_key_for_mobile'
			);
					 
			 if($data = OptionsTools::find($options,$id)){
				 foreach ($data as $name=>$val) {
					if(DEMO_MODE && in_array($id,DEMO_MERCHANT)){
						$model2[$name] = CommonUtility::mask(date("Ymjhs"));
					} else $model2[$name]=$val;					 
				 }			
			 }
				 
			 if(isset($_POST['AR_option'])){
				 if(DEMO_MODE && in_array($id,DEMO_MERCHANT)){
					  $this->render('//tpl/error',array(  
						   'error'=>array(
							 'message'=>t("Modification not available in demo")
						   )
						 ));	
					 return false;
				 }
				 $model2->attributes=$_POST['AR_option'];				
				 if($model2->validate()){				
					 OptionsTools::$merchant_id = $id;					
					 if(OptionsTools::save($options, $model2, $id)){
						 Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
						 $this->refresh();
					 } else Yii::app()->user->setFlash('error',t(Helper_failed_update));
				 } 
			 }
           
						
			$this->render("//tpl/submenu_tpl",array(		    
				'template_name'=>"//merchant/map_keys",
				'widget'=>'WidgetMerchantInfoMenu',		
				'avatar'=>$avatar,				
				'menu'=>$menu,				
				'params_widget'=>array(			   
				   'merchant_id'=>$id,
				   'merchant_type'=>$model->merchant_type,
				   'self_delivery'=>$model->self_delivery
				),
				'custom_link'=>CHtml::link('Auto login',Yii::app()->createUrl("vendor/autologin",[
					'merchant_uuid'=>$model->merchant_uuid,
				]),[
					'class'=>"btn btn-link",
					'target'=>"_blank"
				]),
				'params'=>array(
				  'id'=>$id,
				  'model'=>$model2,	
				  'map_provider'=>AttributesTools::mapsProvider(),		   				  
					'links'=>array(
					   t("All Merchant")=>array(Yii::app()->controller->id.'/list'),        
					   $model->isNewRecord?t("Add new"):t("Edit Merchant"),
					   isset($model->restaurant_name)?stripslashes(ucwords($model->restaurant_name)):''
					),	    		   
				)
			));						
		} else $this->render("error");
	}		
	
	public function actionautologin()
	{
		$merchant_uuid = Yii::app()->input->get('merchant_uuid');  
		$model = AR_merchant::model()->find("merchant_uuid=:merchant_uuid",[
			':merchant_uuid'=>trim($merchant_uuid)
		]);
		if($model){						
			$user = AR_merchant_user::model()->find("merchant_id=:merchant_id AND main_account=:main_account",[
				':merchant_id'=>$model->merchant_id,
				':main_account'=>1
			]);
			if($user){				
				Yii::app()->merchant->id = $user->merchant_user_id;
				Yii::app()->merchant->setState('merchant_id', $model->merchant_id);
				Yii::app()->merchant->setState('merchant_uuid', $model->merchant_uuid);
				Yii::app()->merchant->setState('status', $model->status);
				Yii::app()->merchant->setState('merchant_type', $model->merchant_type);                				
				Yii::app()->merchant->setState('restaurant_slug', $model->restaurant_slug);     

				Yii::app()->merchant->setState('first_name', $user->first_name);     
				Yii::app()->merchant->setState('last_name', $user->last_name); 
				Yii::app()->merchant->setState('email_address', $user->contact_email); 
				Yii::app()->merchant->setState('contact_number', $user->contact_number); 
				Yii::app()->merchant->setState('profile_photo', $user->profile_photo); 
				Yii::app()->merchant->setState('login_type', 'merchant'); 
				Yii::app()->merchant->setState('main_account', $user->main_account); 				
				Yii::app()->merchant->setState('role_id', $user->role); 
				Yii::app()->merchant->setState('logintoken', $user->session_token); 
				Yii::app()->merchant->setState('avatar',  CMedia::getImage($user->profile_photo,$user->path,Yii::app()->params->size_image_thumbnail,CommonUtility::getPlaceholderPhoto('customer'))  ); 				

				Yii::app()->cache->delete('cache_merchant_menu_'.Yii::app()->merchant->id);
				$this->redirect(Yii::app()->createUrl('/merchant/dashboard'));

			} else $this->render("//tpl/error",[
				'error'=>[
					'message'=>t("Merchant has no user please create one first")
				]
			]);
		} else $this->render("error");
	}		

	public function actionbank_registration()
	{
				
		$this->setMenuActive();		
		$this->pageTitle = t("Merchant - Bank Registration");

		$id = (integer) Yii::app()->input->get('id');		
		$model = AR_merchant::model()->findByPk( $id );
		if($model){
			
			$avatar = CMedia::getImage($model->logo,$model->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));

			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantInfoMenu;		   			   
			   $menu->merchant_id = $id;
			   $menu->init();    
			}

			$provider = []; $provider_selected = []; $payments_credentials = array();
			try {
				$list = CPayments::getSplitProvider();				
				$provider = $list['data'];				
				$provider_selected = $list['default'];	
				
				$merchants = CMerchantListingV1::getMerchant( $id );				
				$payments_credentials = CPayments::getPaymentCredentials($merchants->merchant_id,'',$merchants->merchant_type);				

				CComponentsManager::RegisterBundle($provider,'bank-');								
				
			} catch(Exception $e) {
				//dump($e->getMessage());				
			}
			
			$payment_code = Yii::app()->input->get('payment_code');
			if(!empty($payment_code)){
				$provider_selected = $payment_code;
			}

			ScriptUtility::registerScript(array(
				"var provider_selected='$provider_selected';",				
			  ),'provider_selected');		
			  		

			$this->render("//tpl/submenu_tpl",array(		    
				'template_name'=>"//vendor/bank_registration",
				'widget'=>'WidgetMerchantInfoMenu',		
				'avatar'=>$avatar,				
				'menu'=>$menu,				
				'params_widget'=>array(			   
				   'merchant_id'=>$id
				),
				'params'=>array(
				  'id'=>$id,
				  'merchant_type'=>$model->merchant_type,
				  'payment_provider'=> $provider,
				  'provider_selected'=>$provider_selected,
				  'payments_credentials'=>$payments_credentials,
				  'map_provider'=>AttributesTools::mapsProvider(),		   				  
					'links'=>array(
					   t("All Merchant")=>array(Yii::app()->controller->id.'/list'),        
					   $model->isNewRecord?t("Add new"):t("Edit Merchant"),
					   isset($model->restaurant_name)?stripslashes(ucwords($model->restaurant_name)):''
					),	    		   
				)
			));						

		} else $this->render("//tpl/error");
	}

	public function actionbulkupload()
	{
		$this->pageTitle = t("Bulk upload merchant");
		CommonUtility::setMenuActive('.merchant',".vendor_list");
		$model = new AR_merchant();
		$model->scenario="csv_upload";

		if(isset($_POST['AR_merchant'])){
			if(DEMO_MODE){			
				$this->render('//tpl/error',array(  
					  'error'=>array(
						'message'=>t("Modification not available in demo")
					  )
					));	
				return false;
			}
			$model->attributes=$_POST['AR_merchant'];			
			if ($model->validate()){
				if (!ini_get("auto_detect_line_endings")) {
					//ini_set("auto_detect_line_endings", '1');
				}
				$model->csv=CUploadedFile::getInstance($model,'csv');							
				$csv = Reader::createFromPath($model->csv->tempName, 'r');
				$csv->setHeaderOffset(0);
				$header = $csv->getHeader(); 
				$records = $csv->getRecords();
				$error= ''; $pass = 0; $data = [];
				$total_header = count($header);								
				if($total_header>14){
					foreach ($records as $key => $items) {
						try {
							$list_cuisine_id = isset($items['list_cuisine_id'])?trim($items['list_cuisine_id']):'';
							$list_cuisine_id = !empty($list_cuisine_id)?explode("-",$list_cuisine_id):'';
							
							$list_service_id = isset($items['list_service_id'])?trim($items['list_service_id']):'';
							$list_service_id = !empty($list_service_id)?explode("-",$list_service_id):'';

							$model = new AR_merchant();		
							$model->scenario="csv_upload";					

							$model->restaurant_name = isset($items['restaurant_name'])?trim($items['restaurant_name']):'';
							$model->restaurant_slug = isset($items['restaurant_slug'])?trim($items['restaurant_slug']):'';
							$model->contact_name = isset($items['contact_name'])?trim($items['contact_name']):'';
							$model->contact_phone = isset($items['contact_phone'])?trim($items['contact_phone']):'';
							$model->contact_email = isset($items['contact_email'])?trim($items['contact_email']):'';
							$model->address = isset($items['address'])?trim($items['address']):'';
							$model->latitude = isset($items['latitude'])?trim($items['latitude']):'';
							$model->lontitude = isset($items['lontitude'])?trim($items['lontitude']):'';
							$model->cuisine2 = $list_cuisine_id;
							$model->service2 = $list_service_id;
							$model->delivery_distance_covered = isset($items['distance_covered'])?floatval($items['distance_covered']):0;
							$model->distance_unit = isset($items['distance_unit'])?trim($items['distance_unit']):'mi';
							$model->merchant_type = isset($items['merchant_type'])?trim($items['merchant_type']):2;
							$model->is_ready = isset($items['is_ready'])?trim($items['is_ready']):0;
							$model->status = isset($items['status'])?trim($items['status']):'pending';

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
				//dump($model->getErrors());die();
				Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString( $model->getErrors(),"<br/>" ));
			}
		}

		$this->render("merchant_bulkupload",[
			'model'=>$model,
			'links'=>array(
				t("Merchant"),
	            t("List")=>array('vendor/list'), 
	            $this->pageTitle,
		      ),	    		    
		]);
	}

	public function actionaccess_settings()
	{
		$this->setMenuActive();		
		$this->pageTitle = t("Merchant - Access Settings");		
		
		$id = intval(Yii::app()->input->get('id'));
		$model = AR_merchant::model()->findByPk( $id );
		if($model){

			if(isset($_POST['AR_merchant'])){
				$model->attributes=$_POST['AR_merchant'];
				if($model->validate()){						    				    				    	
			    	if($model->save()){						    					    	
						Yii::app()->user->setFlash('success', t(Helper_success) );
						$this->refresh();						
					} else {					
						Yii::app()->user->setFlash('error',CommonUtility::t(Helper_failed_update));
					}				
				} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString($model->getErrors()) );	
			}

			$role_access = [];
			if($get_access = MerchantTools::getMerchantMeta($model->merchant_id,'menu_access')){			
				$role_access = $get_access;
			}		

			$avatar = CMedia::getImage($model->logo,$model->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));

			BuildMenu::createMenu(0,true,0,'merchant');
			$params_model = array(		
				'model'=>$model,	
				'links'=>array(
				   t("All Merchant")=>array(Yii::app()->controller->id.'/list'),        
				   $model->isNewRecord?t("Add new"):t("Edit Merchant"),
				   isset($model->restaurant_name)?stripslashes(ucwords($model->restaurant_name)):''
				),	    		   
				'menu'=>BuildMenu::$items,
				'role_access'=>$role_access
			);	

			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantInfoMenu;		   			   
			   $menu->merchant_id = $id;
			   $menu->init();    
			}
						
			$this->render("//tpl/submenu_tpl",array(		    
				'template_name'=>"//vendor/access_settings",
				'widget'=>'WidgetMerchantInfoMenu',		
				'avatar'=>$avatar,
				'params'=>$params_model,
				'custom_link'=>CHtml::link('Auto login',Yii::app()->createUrl("vendor/autologin",[
					'merchant_uuid'=>$model->merchant_uuid,
				]),[
					'class'=>"btn btn-link",
					'target'=>"_blank"
				]),
				'menu'=>$menu,
				'params_widget'=>array(			   
				   'merchant_id'=>$id,
				   'merchant_type'=>$model->merchant_type,
				   'self_delivery'=>$model->self_delivery
				)
			));		
		} else $this->render("error");
	}

	public function actionapproved()
	{
		$merchant_uuid = Yii::app()->input->get('merchant_uuid');		
		$model = CMerchants::getByUUID($merchant_uuid);
		if($model){
			$model->scenario = "approved";
			$model->status = 'active';
			$model->save();
			$this->redirect(array(Yii::app()->controller->id.'/pending_list'));
		} else $this->render("error");
	}

	public function actiondenied()
	{
		$merchant_uuid = Yii::app()->input->get('merchant_uuid');		
		$model = CMerchants::getByUUID($merchant_uuid);
		if($model){
			$model->scenario = "registration_denied";
			$model->status = 'blocked';
			$model->save();
			$this->redirect(array(Yii::app()->controller->id.'/pending_list'));
		} else $this->render("error");
	}

	public function actionpush_notifications()
	{
		$this->setMenuActive();		
		$this->pageTitle = t("Merchant - Push notifications");
		$jwt_token = AttributesTools::JwtTokenID();
		
		$id = (integer) Yii::app()->input->get('id');		
		$model = AR_merchant::model()->findByPk( $id );
		if($model){
			
			$avatar = CMedia::getImage($model->logo,$model->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));
								
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantInfoMenu;		   			   
			   $menu->merchant_id = $id;
			   $menu->init();    
			}
								
			
			$model2 = AR_merchant_meta::model()->find("meta_name=:meta_name",[
				':meta_name'=>'push_json_file'
			]);
			if(!$model2){
				$model2 = new AR_merchant_meta; 
			}

			$model2->scenario = 'push_notifications';

			if(isset($_POST['AR_merchant_meta'])){
				if(DEMO_MODE){			
					$this->render('//tpl/error',array(  
						  'error'=>array(
							'message'=>t("Modification not available in demo")
						  )
						));	
					return false;
				}
				$model2->file = CUploadedFile::getInstance($model2,'file');
				if($model2->file){				
					$model2->attributes=$_POST['AR_merchant_meta'];			    	

					$path = CommonUtility::uploadDestination('upload/all/').$model2->file->name;				
					$model2->meta_name = "push_json_file";
					$model2->merchant_id = $id;
					$model2->meta_value = $model2->file->name;
					if($model2->validate()){	
						if($model2->save()){
							$model2->file->saveAs( $path );					
							Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
							$this->refresh();				
						} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString($model2->getErrors()) );	
					} 
				} else {
					Yii::app()->user->setFlash('error', t("Please select file") );
				}
			}
						
			$this->render("//tpl/submenu_tpl",array(		    
				'template_name'=>"//merchant/push_notifications",
				'widget'=>'WidgetMerchantInfoMenu',		
				'avatar'=>$avatar,				
				'menu'=>$menu,				
				'params_widget'=>array(			   
				   'merchant_id'=>$id,
				   'merchant_type'=>$model->merchant_type,
				   'self_delivery'=>$model->self_delivery
				),
				'custom_link'=>CHtml::link('Auto login',Yii::app()->createUrl("vendor/autologin",[
					'merchant_uuid'=>$model->merchant_uuid,
				]),[
					'class'=>"btn btn-link",
					'target'=>"_blank"
				]),
				'params'=>array(
				  'id'=>$id,
				  'model'=>$model2,	
				  'links'=>array(
					   t("All Merchant")=>array(Yii::app()->controller->id.'/list'),        
					   $model->isNewRecord?t("Add new"):t("Edit Merchant"),
					   isset($model->restaurant_name)?stripslashes(ucwords($model->restaurant_name)):''
				  ),	    		   
				)
			));						
		} else $this->render("error");
	}

	public function actionmobile_settings()
	{
				
		$this->setMenuActive();		
		$this->pageTitle = t("Merchant - Mobile Settings");
		$jwt_token = AttributesTools::JwtTokenID();
		
		$id = (integer) Yii::app()->input->get('id');		
		$model = AR_merchant::model()->findByPk( $id );
		if($model){
			
			$avatar = CMedia::getImage($model->logo,$model->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));

			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantInfoMenu;		   			   
			   $menu->merchant_id = $id;
			   $menu->init();    
			}
													
			$model2 = new AR_option;

			$options = array(
				'merchant_android_download_url',
				'merchant_ios_download_url',
				'merchant_mobile_app_version_android',
				'merchant_mobile_app_version_ios',
				'merchant_mobile_webpush_certificates',
			 );
					 
			 if($data = OptionsTools::find($options,$id)){
				 foreach ($data as $name=>$val) {
					 $model2[$name]=$val;
				 }			
			 }
				 
			 if(isset($_POST['AR_option'])){
				 if(DEMO_MODE && in_array($id,DEMO_MERCHANT)){
					  $this->render('//tpl/error',array(  
						   'error'=>array(
							 'message'=>t("Modification not available in demo")
						   )
						 ));	
					 return false;
				 }
				 $model2->attributes=$_POST['AR_option'];				
				 if($model2->validate()){				
					 OptionsTools::$merchant_id = $id;					
					 if(OptionsTools::save($options, $model2, $id)){
						 Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
						 $this->refresh();
					 } else Yii::app()->user->setFlash('error',t(Helper_failed_update));
				 } 
			 }
           
						
			$this->render("//tpl/submenu_tpl",array(		    
				'template_name'=>"//vendor/mobile_settings",
				'widget'=>'WidgetMerchantInfoMenu',		
				'avatar'=>$avatar,				
				'menu'=>$menu,				
				'params_widget'=>array(			   
				   'merchant_id'=>$id,
				   'merchant_type'=>$model->merchant_type,
				   'self_delivery'=>$model->self_delivery
				),
				'custom_link'=>CHtml::link('Auto login',Yii::app()->createUrl("vendor/autologin",[
					'merchant_uuid'=>$model->merchant_uuid,
				]),[
					'class'=>"btn btn-link",
					'target'=>"_blank"
				]),
				'params'=>array(
				  'id'=>$id,
				  'model'=>$model2,	
				  'country_list'=>AttributesTools::CountryList(),		   
					'links'=>array(
					   t("All Merchant")=>array(Yii::app()->controller->id.'/list'),        
					   $model->isNewRecord?t("Add new"):t("Edit Merchant"),
					   isset($model->restaurant_name)?stripslashes(ucwords($model->restaurant_name)):''
					),	    		   
				)
			));						
		} else $this->render("error");
	}	

	public function actiontableside_settings()
	{
		$this->setMenuActive();		
		$this->pageTitle = t("Merchant - Others");
		
		$id = (integer) Yii::app()->input->get('id');		
		$merchant = AR_merchant::model()->findByPk( $id );
		if($merchant){

			$model=new AR_option;
		    $model->scenario = "settings";		

			$avatar = CMedia::getImage($merchant->logo,$merchant->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));
			
			$options = array(
			  'tableside_services'
			);

			if($data = OptionsTools::find($options,$id)){
				foreach ($data as $name=>$val) {
					$model[$name]=$val;
				}			
			}
			
			if(Yii::app()->request->isPostRequest) {
				$model->attributes=isset($_POST['AR_option'])?$_POST['AR_option']:'';
				if(DEMO_MODE && in_array($id,DEMO_MERCHANT)){
					$this->render('//tpl/error',array(  
						 'error'=>array(
						   'message'=>t("Modification not available in demo")
						 )
					   ));	
				   return false;
			   }		
			   if($model->validate()){
				 OptionsTools::$merchant_id = $id;		
				 $tableside_services = $model->tableside_services;
				 
				 $model->tableside_services = json_encode($model->tableside_services);	
				 if(OptionsTools::save($options, $model, $id)){
					Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));		
					MerchantTools::saveMerchantMeta($id,$tableside_services,'tableside_services');							
					$this->refresh();				
				 }
			   }						   
			}

			if(!empty($model->tableside_services)){
				$model->tableside_services = json_decode($model->tableside_services,true);
			}
					
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantInfoMenu;		   			   
			   $menu->merchant_id = $id;
			   $menu->init();    
			}

			$service_list = AttributesTools::ListSelectServicesNew(Yii::app()->language);

			$this->render("//tpl/submenu_tpl",array(		    
				'template_name'=>"//vendor/tableside_settings",
				'widget'=>'WidgetMerchantInfoMenu',		
				'avatar'=>$avatar,				
				'menu'=>$menu,
				'params_widget'=>array(			   
				   'merchant_id'=>$id,
				   'merchant_type'=>$model->merchant_type,
				   'self_delivery'=>$model->self_delivery
				),
				'params'=>array(
				  'model'=>$model,	
					'links'=>array(
					   t("All Merchant")=>array(Yii::app()->controller->id.'/list'),        
					   $merchant->isNewRecord?t("Add new"):t("Edit Merchant"),
					   isset($merchant->restaurant_name)?stripslashes(ucwords($merchant->restaurant_name)):''
					),	    
					'food_option_listing'=>AttributesTools::foodOptionsListing(),
				   'two_flavor_options'=>AttributesTools::twoFlavorOptions(),
				   'unit'=>AttributesTools::unit(),				   	
				   'zone_type'=>AttributesTools::zoneType(),
				   'service_list'=>$service_list,				   				   
				),
				'custom_link'=>CHtml::link('Auto login',Yii::app()->createUrl("vendor/autologin",[
					'merchant_uuid'=>$merchant->merchant_uuid,
				]),[
					'class'=>"btn btn-link",
					'target'=>"_blank"
				]),
			));						
			
		} else $this->render("error");
	}

	public function actionsubscriptions()
	{
		$this->setMenuActive();		
		$this->pageTitle = t("Subscriptions");		
		
		$id = (integer) Yii::app()->input->get('id');		
		$model = AR_merchant::model()->findByPk( $id );
		if($model){			

			ScriptUtility::registerScript(array(
				"var merchant_uuid='$model->merchant_uuid';",				
			),'action_name');

			$avatar = CMedia::getImage($model->logo,$model->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));

			$params_model = array(		
				'model'=>$model,	
				'links'=>array(
				   t("All Merchant")=>array(Yii::app()->controller->id.'/list'),        
				   $model->isNewRecord?t("Add new"):t("Edit Merchant"),
				   isset($model->restaurant_name)?stripslashes(ucwords($model->restaurant_name)):'',
				   t("Subscriptions")
				),	 				
	            'ajax_url'=>Yii::app()->createUrl("/api")
			);										
			
			$this->render("//tpl/submenu_tpl",array(		    
				'template_name'=>'subscriptions',
				'widget'=>'WidgetMerchantInfoMenu',		
				'avatar'=>$avatar,
				'params'=>$params_model,
				'custom_link'=>CHtml::link('Auto login',Yii::app()->createUrl("vendor/autologin",[
					'merchant_uuid'=>$model->merchant_uuid,
				]),[
					'class'=>"btn btn-link",
					'target'=>"_blank"
				]),
				'menu'=>array(),
				'params_widget'=>array(			   
				   'merchant_id'=>$id,
				   'merchant_type'=>$model->merchant_type,
				   'self_delivery'=>$model->self_delivery
				),				
			));		
		} else $this->render("error");
	}

	public function actionlocation()
	{
		$this->setMenuActive();		
		$this->pageTitle = t("Edit Merchant - Location");
		
		$id = (integer) Yii::app()->input->get('id');		
		$model = AR_merchant::model()->findByPk( $id );		

		$menu = array();
		if(Yii::app()->params['isMobile']==TRUE){
			$menu = new WidgetMerchantInfoMenu;		   			   
			$menu->merchant_id = $id;
			$menu->init();    
		}

		if($model){
			$avatar = CMedia::getImage($model->logo,$model->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));

			$table_col = array(
				'id'=>array(
				  'label'=>t("ID"),
				  'width'=>'1%'
				),			
				'country_name'=>array(
				  'label'=>t("Country"),
				  'width'=>'10%'
				),			
				'state_name'=>array(
				  'label'=>t("State"),
				  'width'=>'10%'
				),			
				'city_name'=>array(
				  'label'=>t("City"),
				  'width'=>'10%'
				),			
				'area_name'=>array(
				  'label'=>t("Area"),
				  'width'=>'10%'
				),							
				'date_created'=>array(
					'label'=>t("Actions"),
					'width'=>'15%'
				),		  
			  );
			  $columns = array(			
				array('data'=>'id','visible'=>true),
				array('data'=>'country_name'),
				array('data'=>'state_name'),		  
				array('data'=>'city_name'),		  
				array('data'=>'area_name'),		  				
				array('data'=>null,'orderable'=>false,
				   'defaultContent'=>'
				   <div class="btn-group btn-group-actions" role="group">
					  <a class="ref_edit_popup normal btn btn-light tool_tips"><i class="zmdi zmdi-border-color"></i></a>
					  <a class="ref_delete normal btn btn-light tool_tips"><i class="zmdi zmdi-delete"></i></a>
				  </div>
				   '
				),
			);	
			
			$params_model = array(								
				'table_col'=>$table_col,
			    'columns'=>$columns,
			    'order_col'=>0,
			    'sortby'=>'desc',
				'merchant_id'=>$id,
				'links'=>array(
				   t("All Merchant")=>array(Yii::app()->controller->id.'/list'),        
				   $model->isNewRecord?t("Add new"):t("Edit Merchant"),
				   isset($model->restaurant_name)?stripslashes(ucwords($model->restaurant_name)):''
				),	    
				'ajax_url'=>Yii::app()->createUrl("/api"),
			);	
			
			$action_name = 'getManageLocation';
			ScriptUtility::registerScript(array(
				"var action_get='$action_name';",				
			),'location_manage');		
			
			$this->render("//tpl/submenu_tpl",array(		    
				'template_name'=>"location_management",
				'widget'=>'WidgetMerchantInfoMenu',		
				'avatar'=>$avatar,		
				'params'=>$params_model,		
				'custom_link'=>CHtml::link('Auto login',Yii::app()->createUrl("vendor/autologin",[
					'merchant_uuid'=>$model->merchant_uuid,
				]),[
					'class'=>"btn btn-link",
					'target'=>"_blank"
				]),
				'menu'=>$menu,
				'params_widget'=>array(			   
				   'merchant_id'=>$id,
				   'merchant_type'=>$model->merchant_type,
				   'self_delivery'=>$model->self_delivery
				),				
			));		
		} else $this->render("error");		
	}
	
	public function actiondelete_location()
	{
		if(DEMO_MODE){			
		    $this->render('//tpl/error',array(  
		          'error'=>array(
		            'message'=>t("Modification not available in demo")
		          )
		        ));	
		    return false;
		}
		$id = Yii::app()->input->get('id');		
		$model = AR_merchant_location::model()->find("id=:id",[
			':id'=>$id
		]);
		if($model){
			$model->delete();
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array('vendor/location?id='.$model->merchant_id));			
		} else $this->render("error");
	}
	
}
/*end class*/