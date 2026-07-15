<?php
require 'php-jwt/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use Symfony\Component\Translation\Dumper\DumperInterface;

class MerchantController extends Commonmerchant
{
		
	public function beforeAction($action)
	{				
		
		InlineCSTools::registerStatusCSS();
		InlineCSTools::registerOrder_StatusCSS();
			
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
	
	public function actionlogout()
	{
		Yii::app()->merchant->logout(false);		
		$this->redirect(Yii::app()->merchant->loginUrl);		
	}
	
	public function actionIndex()
	{			
		$this->redirect(array(Yii::app()->controller->id.'/dashboard'));		
	}	
	
	public function actiondashboard()
	{				
		$this->pageTitle = t("Dashboard");
		$merchant_type = CMerchants::getMerchantType(Yii::app()->merchant->merchant_id);		
		$main_account = Yii::app()->merchant->getState("main_account");	
		
		$dashboard_access = [
			'merchant.dashboard.order_summary','merchant.dashboard.week_sales','merchant.dashboard.today_summary',
			'merchant.dashboard.last_5_orders','merchant.dashboard.popular_items','merchant.dashboard.sales_overview',
			'merchant.dashboard.top_customer','merchant.dashboard.review_overview'
		];
		if($main_account==1){
			if(MerchantTools::hasMerchantSetMenu(Yii::app()->merchant->merchant_id)){
				$dashboard_access = MerchantTools::getMerchantMeta(Yii::app()->merchant->merchant_id,'menu_access');
			}
		} else {
			try {
				$dashboard_access = MerchantTools::getMerchantMenuRolesAccess(Yii::app()->merchant->id,Yii::app()->merchant->merchant_id);
			} catch (Exception $e) {}
		}		
				
		$dashboard_access = json_encode($dashboard_access);

		ScriptUtility::registerScript(array(
			"var dashboard_access='$dashboard_access';"			
		 ),'dashboard_access');

		// PLANS
		if(Yii::app()->merchant->merchant_type==1){
			Cplans::UpdateAddedItems(Yii::app()->merchant->merchant_id);
		}
		
		$this->render('dashboard',array(
		  'orders_tab'=>AttributesTools::dashboardOrdersTab(),
		  'item_tab'=>AttributesTools::dashboardItemTab(),
		  'limit'=>5,
		  'months'=>6,
		  'merchant_id'=>Yii::app()->merchant->merchant_id,
		  'ajax_url'=>Yii::app()->createUrl("/apibackend"),
		  'merchant_type'=>$merchant_type,
		  'dashboard_access'=>$dashboard_access
		));
	}
	
	public function actionprofile()
	{
		$this->pageTitle = t("Profile");
		$id = Yii::app()->merchant->merchant_id;
		$upload_path = CMedia::merchantFolder();
				
		$model = AR_merchant_user::model()->findByPk( Yii::app()->merchant->id );		
		if(!$model){
			$this->render('//tpl/error',array(
			 'error'=>array(
			   'message'=>t("There is a problem with the page your viewing.")
			 )
			));
			Yii::app()->end();
		}
		
		if(isset($_POST['AR_merchant_user'])){
			$model->attributes=$_POST['AR_merchant_user'];			
			if($model->validate()){																		
				if(isset($_POST['photo'])){
					if(!empty($_POST['photo'])){
						$model->profile_photo = $_POST['photo'];
						Yii::app()->merchant->profile_photo = $_POST['photo'];
						$model->path = isset($_POST['path'])?$_POST['path']:$upload_path;
					} else {
						$model->profile_photo = '';
						Yii::app()->merchant->profile_photo  ='';
					}
				} else {
					$model->profile_photo = '';	
					Yii::app()->merchant->profile_photo  ='';
				}
				
				if($model->save()){
					Yii::app()->user->setFlash('success',CommonUtility::t("Profile updated"));
					$this->refresh();
				} else {
					Yii::app()->user->setFlash('error',CommonUtility::t(Helper_failed_update));
				}				
			} 
		}
				
		$avatar = CMedia::getImage($model->profile_photo,$model->path,'@thumbnail',
		CommonUtility::getPlaceholderPhoto('customer'));
		
		$settings = AR_admin_meta::getMeta(array('webpush_app_enabled'));			
		$webpush_app_enabled = isset($settings['webpush_app_enabled'])?$settings['webpush_app_enabled']['meta_value']:'';
		
		WidgetUserMenu::$ctr[0] = Yii::app()->controller->id."/profile";
		WidgetUserMenu::$ctr[1] = Yii::app()->controller->id."/change_password";
		if($webpush_app_enabled){
		   WidgetUserMenu::$ctr[2] = Yii::app()->controller->id."/web_notifications";
		}
		
		$this->render("//admin/submenu_tpl",array(
		    'model'=>$model,
			'template_name'=>"//merchant/profile",
			'widget'=>'WidgetUserMenu',		
			'avatar'=>$avatar,			
			'params'=>array(  
			   'model'=>$model,			   
			   'upload_path'=>$upload_path,
			   'links'=>array(		            
			   ),
			 )
		));
	}
	
	public function actionchange_password()
	{
		$this->pageTitle = t("Profile");
		$id = Yii::app()->merchant->merchant_id;
		
		$model = AR_merchant_user::model()->findByPk( Yii::app()->merchant->id );		
		if(!$model){
			$this->render('//tpl/error',array(
			 'error'=>array(
			   'message'=>t("There is a problem with the page your viewing.")
			 )
			));
			Yii::app()->end();
		}
		
		$model->scenario = 'update_password';
		
		if(isset($_POST['AR_merchant_user'])){
			$model->attributes=$_POST['AR_merchant_user'];
			if($model->validate()){				
								
				if(!empty($model->new_password) && !empty($model->new_password)){					
					$model->password = md5(trim($model->new_password));
				}
												
				if($model->save()){
					Yii::app()->user->setFlash('success',CommonUtility::t("Password updated"));
					$this->refresh();
				} else {
					Yii::app()->user->setFlash('error',CommonUtility::t(Helper_failed_update));
				}				
			} 
		}
				
		$avatar = CMedia::getImage($model->profile_photo,$model->path,'@thumbnail',
		CommonUtility::getPlaceholderPhoto('customer'));		
		
		$settings = AR_admin_meta::getMeta(array('webpush_app_enabled'));			
		$webpush_app_enabled = isset($settings['webpush_app_enabled'])?$settings['webpush_app_enabled']['meta_value']:'';
		
		WidgetUserMenu::$ctr[0] = Yii::app()->controller->id."/profile";
		WidgetUserMenu::$ctr[1] = Yii::app()->controller->id."/change_password";
		if($webpush_app_enabled){
		   WidgetUserMenu::$ctr[2] = Yii::app()->controller->id."/web_notifications";
		}
		
		$this->render("//admin/submenu_tpl",array(
		    'model'=>$model,
			'template_name'=>"//merchant/change_password",
			'widget'=>'WidgetUserMenu',		
			'avatar'=>$avatar,
			'params'=>array(  
			   'model'=>$model,			   
			   'links'=>array(		            
			   ),
			 )
		));
	}
	
		public function actionweb_notifications()
	{
		$this->pageTitle = t("Change Password");
		
		$model = AR_merchant_user::model()->findByPk( Yii::app()->merchant->id );		
		if(!$model){
			$this->render("error");
			Yii::app()->end();
		}
		
		$avatar = CMedia::getImage($model->profile_photo,$model->path,'@thumbnail',
		CommonUtility::getPlaceholderPhoto('customer'));		
		
		WidgetUserMenu::$ctr[0] = Yii::app()->controller->id."/profile";
		WidgetUserMenu::$ctr[1] = Yii::app()->controller->id."/change_password";		
		
		$settings = AR_admin_meta::getMeta(array('webpush_provider','pusher_instance_id','webpush_app_enabled'));			
		$webpush_provider = isset($settings['webpush_provider'])?$settings['webpush_provider']['meta_value']:'';
		$pusher_instance_id = isset($settings['pusher_instance_id'])?$settings['pusher_instance_id']['meta_value']:'';
		$webpush_app_enabled = isset($settings['webpush_app_enabled'])?$settings['webpush_app_enabled']['meta_value']:'';
		$webpush_app_enabled = $webpush_app_enabled==1?true:false;
		
		if($webpush_app_enabled){
		   WidgetUserMenu::$ctr[2] = Yii::app()->controller->id."/web_notifications";
		}

		if($webpush_app_enabled){
		$this->render("//admin/submenu_tpl",array(
		    'model'=>$model,
			'template_name'=>"merchant_webpush",
			'widget'=>'WidgetUserMenu',		
			'avatar'=>$avatar,
			'params'=>array(  
			   'model'=>$model,			   
			   'iterest_list'=>AttributesTools::pushInterestList(),
			   'pusher_instance_id'=>$pusher_instance_id,
			   'webpush_provider'=>$webpush_provider,			   
			   'links'=>array(		            
			   ),
			 )
		));
		} else $this->render('//tpl/error',array(  
		  'error'=>array(
		    'message'=>t("Web push is not enabled")
		  )
		));
	}
	
	public function actionprofile_remove_image()
	{
	    $merchant_id =  (integer)  Yii::app()->merchant->id;					
		$model = AR_merchant_user::model()->findByPk($merchant_id);
		if($model){
			Yii::app()->merchant->setState("avatar",'');
			$model->profile_photo = '';
			$model->save();
			$this->redirect(array(Yii::app()->controller->id.'/profile'));			
		} else $this->render("error");
	}
	
	public function actionedit()
	{
		CommonUtility::setMenuActive('.vendor_list');
		$this->pageTitle = t("Merchant - information");	
		
		$id = Yii::app()->merchant->merchant_id;		
		$model = AR_merchant::model()->findByPk( $id );
		
		if(!$model){				
			$this->render("error");				
			Yii::app()->end();
		}
		
		$model->scenario='information';
		$upload_path = CMedia::merchantFolder();
		
		if(isset($_POST['AR_merchant'])){
			$post = Yii::app()->input->post('AR_merchant');			
			$model->merchant_about_trans = isset($post['merchant_about_trans'])?$post['merchant_about_trans']:'';
			$model->merchant_short_about_trans = isset($post['merchant_short_about_trans'])?$post['merchant_short_about_trans']:'';			
		    $model->attributes=$_POST['AR_merchant'];			
		    if($model->validate()){			    	
				$model->saved_featured = false;		    		    	    	    	    
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
			} else Yii::app()->user->setFlash('error',t(HELPER_CORRECT_FORM));
		}
		
		if(!isset($_POST['AR_merchant'])){
															
			$model->cuisine2 = MerchantTools::getCuisine($model->merchant_id);
			
			if($services = MerchantTools::getMerchantMeta($model->merchant_id,'services')){
				$model->service2=$services;
			}				
			
			if($service3 = MerchantTools::getMerchantMeta($model->merchant_id,'services_pos')){
				$model->service3=$service3;
			}											

			if($tableside_services = MerchantTools::getMerchantMeta($model->merchant_id,'tableside_services')){
				$model->tableside_services=$tableside_services;
			}											
			
			// if($featured = MerchantTools::getMerchantMeta($model->merchant_id,'featured')){
			// 	$model->featured=$featured;
			// }											
			
			if($tags = MerchantTools::getMerchantOptions($model->merchant_id,'tags')){					
				$model->tags=$tags;
			}											
		}
		
		$model->delivery_distance_covered = Price_Formatter::convertToRaw($model->delivery_distance_covered,0);
		
		$model->restaurant_name = stripslashes($model->restaurant_name);
				
		$avatar = CMedia::getImage($model->logo,$model->path,'@thumbnail',
		CommonUtility::getPlaceholderPhoto('merchant_logo'));
		
		$nav = array(
		   t("Update Information")=>array(Yii::app()->controller->id.'/edit'),		        
		   isset($model->restaurant_name)?stripslashes(ucwords($model->restaurant_name)):''
		);		

		$data =[];
		if(!isset($_POST['AR_merchant'])){
			if($merchant_about_trans = AR_merchant::getTranslation(Yii::app()->merchant->merchant_id,'merchant_about_trans')){
				$data['merchant_about_trans']=$merchant_about_trans;				
			}			
			if($merchant_about_trans = AR_merchant::getTranslation(Yii::app()->merchant->merchant_id,'merchant_short_about_trans')){
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
		    'ctr'=>'/merchant',		    
		    'upload_path'=>$upload_path,
			'qrcode_pickup'=>$qrcode_pickup,
			'qrcode_dinein'=>$qrcode_dinein,
			'download_qrcode_pickup'=>isset($download_qrcode_pickup)?$download_qrcode_pickup:"",
			'download_qrcode_dinein'=>isset($download_qrcode_dinein)?$download_qrcode_dinein:"",
		    'links'=>array(
	           t("Update Information")=>array(Yii::app()->controller->id.'/edit'),		        
		       isset($model->restaurant_name)?stripslashes(ucwords($model->restaurant_name)):''  
		    ),	    	
		    'show_status'=>false,
			'language'=>AttributesTools::getLanguage(),
			'fields'=>$fields,
			'data'=>$data,
			'is_admin'=>false,
		);	
		
		$menu = array();
		if(Yii::app()->params['isMobile']==TRUE){
		   $menu = new WidgetMerchantAttMenu;		   
		   $menu->merchant_type = isset($model->merchant_type)?$model->merchant_type:'';
		   $menu->main_account = Yii::app()->merchant->getState("main_account");
           $menu->init();    
		}		
				
		$this->render("//tpl/submenu_tpl",array(		    
			'template_name'=>"//vendor/merchant_info",
			'widget'=>'WidgetMerchantAttMenu',		
			'avatar'=>$avatar,
			'params'=>$params_model,
			'menu'=>$menu,			
			'params_widget'=>array(			   
	           'merchant_type'=>isset($model->merchant_type)?$model->merchant_type:'',
	           'main_account'=>Yii::app()->merchant->getState("main_account")
			)
		));		
	}

	public function actionview_qrcode()
	{
		try {						
			$data = Yii::app()->input->get("data");		
			// $model = CMerchants::get(Yii::app()->merchant->merchant_id);			
			// $data = CommonUtility::getHomebaseUrl()."/restaurant/$model->restaurant_slug/?table=$data";			
			CommonUtility::viewQrcode($data);
		} catch (Exception $e) {		
			dump($e->getMessage());
		}			
	}
	
	public function actiondelete_logo()
	{		
		$id = (integer) Yii::app()->merchant->merchant_id;
		$page = Yii::app()->input->get('page');			
		$model = AR_merchant::model()->findByPk( $id );				
		if($model){		
			$filename = $model->logo;
			$model->logo='';
			$model->save();			
			
			/*DELETE IMAGE FROM UPLOAD FOLDER AND MEDIA*/	
			CommonUtility::deleteMediaFile($filename);
					
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array($page));
		} else $this->render("error");
	}
	
	public function actiondelete_headerbg()
	{		
		$id = (integer) Yii::app()->merchant->merchant_id;
		$page = Yii::app()->input->get('page');			
		$model = AR_merchant::model()->findByPk( $id );				
		if($model){		
			$filename = $model->header_image;
			$model->header_image='';
			$model->save();				
			
			/*DELETE IMAGE FROM UPLOAD FOLDER AND MEDIA*/	
			CommonUtility::deleteMediaFile($filename);
			
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array($page));
		} else $this->render("error");
	}
	
	public function actionlogin()
	{
		CommonUtility::setMenuActive('.merchant','.merchant_edit');
		$this->pageTitle = t("Merchant - login");
		
		$id = (integer)Yii::app()->merchant->merchant_id;		
				
		$model = AR_merchant_user::model()->find("merchant_id=:merchant_id AND main_account=:main_account",array(
		  ':merchant_id'=>$id,
		  ':main_account'=>1
		));		
		
		$main_account =  Yii::app()->merchant->getState("main_account");
		if($main_account<=0){
			$this->render('//tpl/error',array(
			 'error'=>array(
			   'message'=>t("This page is not available in your account.")
			 )
			));
			return false;
		}
				
		if($model){														
			if(isset($_POST['AR_merchant_user'])){				
		        $model->attributes=$_POST['AR_merchant_user'];			    	
			    if($model->validate()){			    
			    					       
			       if(isset($_POST['AR_merchant_user']['new_password'])){
				       if(!empty($_POST['AR_merchant_user']['new_password'])){
					       $model->password = md5($_POST['AR_merchant_user']['new_password']);
					       $model->main_account = 1;
				       }
			       }
			       
			       $model->status = 'active';
			       
			       if($model->save()){			       				       				       	  			       				       	  
			       	  Yii::app()->user->setFlash('success', t(Helper_success) );		
			       	  $this->refresh();			
			       } else {
			       	  Yii::app()->user->setFlash('error',CommonUtility::t(Helper_failed_update));
			       }
			    }
			}
			
			$model->password='';
						
			$merchant = AR_merchant::model()->findByPk( $id );	
			
			$avatar = CMedia::getImage($merchant->logo,$merchant->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));
						
			$params_model = array(		
				'model'=>$model,					
				'links'=>array(
				   t("Update Information")=>array(Yii::app()->controller->id.'/login'),		        
				   isset($merchant->restaurant_name)?stripslashes(ucwords($merchant->restaurant_name)):''  
				),	    		 
				'status'=>(array)AttributesTools::StatusManagement('customer'),	  
			);			
			
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantAttMenu;		   
			   $menu->merchant_type = isset($model->merchant_type)?$model->merchant_type:'';
			   $menu->main_account = Yii::app()->merchant->getState("main_account");
			   $menu->init();    
			}
			
			
			$this->render("//tpl/submenu_tpl",array(		    
				'template_name'=>"//vendor/merchant_login",
				'widget'=>'WidgetMerchantAttMenu',		
				'avatar'=>$avatar,
				'params'=>$params_model,
				'menu'=>$menu,
				'params_widget'=>array(			   
				   'merchant_type'=>isset($merchant->merchant_type)?$merchant->merchant_type:'',
				   'main_account'=>Yii::app()->merchant->getState("main_account")
				)
			));		
								
		} else {
			
			$models = AR_merchant::model()->findByPk( $id );
			if($models){
			
				$model = new AR_merchant_user;
				$model->merchant_id=$id;
				
				$model->scenario='register';
				
				if(isset($_POST['AR_merchant_user'])){
			        $model->attributes=$_POST['AR_merchant_user'];			    	
				    if($model->validate()){								    					       
				       $model->password = $_POST['AR_merchant_user']['new_password'];
				       $model->main_account = 1;
				       if($model->save()){						    				       
			       					       		       	
				       	  Yii::app()->user->setFlash('success', t(Helper_success) );		
				       	  $this->redirect(array(Yii::app()->controller->id.'/login','id'=>$model->merchant_id));				       	  
				       } else {
				       	  Yii::app()->user->setFlash('error',CommonUtility::t(Helper_failed_update));
				       }
				    }
				}
								
				$merchant = AR_merchant::model()->findByPk( $model->merchant_id );				
				$avatar='';
				if($merchant){					
					$avatar = CMedia::getImage($merchant->logo,$merchant->path,'@thumbnail',
			        CommonUtility::getPlaceholderPhoto('merchant_logo'));
				}

				
				$params_model = array(		
					'model'=>$model,					
					'links'=>array(
					   t("Update Information")=>array(Yii::app()->controller->id.'/login'),		        
					   isset($model->restaurant_name)?stripslashes(ucwords($model->restaurant_name)):''  
					),	    	
					'status'=>(array)AttributesTools::StatusManagement('customer'),		   
				);	
				
				$menu = array();
				if(Yii::app()->params['isMobile']==TRUE){
				   $menu = new WidgetMerchantAttMenu;		   
				   $menu->merchant_type = isset($model->merchant_type)?$model->merchant_type:'';
				   $menu->main_account = Yii::app()->merchant->getState("main_account");
				   $menu->init();    
				}
					
				$this->render("//tpl/submenu_tpl",array(		    
					'template_name'=>"//vendor/merchant_login",
					'widget'=>'WidgetMerchantAttMenu',		
					'avatar'=>$avatar,
					'params'=>$params_model,
					'menu'=>$menu,
					'params_widget'=>array(			   
					   'merchant_type'=>isset($model->merchant_type)?$model->merchant_type:'',
					   'main_account'=>Yii::app()->merchant->getState("main_account")
					)
				));			
					
			} else $this->render("error");	
		}
	}	
	
	public function actionaddress()
	{
		CommonUtility::setMenuActive('.merchant','.merchant_edit');
		$this->pageTitle = t("Edit Merchant - Address");
		
		$id = Yii::app()->merchant->merchant_id;
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
				} else Yii::app()->user->setFlash('error', t(HELPER_CORRECT_FORM) );	
			}		

			$country_list = require_once 'CountryCode.php';
			
			$avatar = CMedia::getImage($model->logo,$model->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));
			
			$params_model = array(		
				'model'=>$model,
				'country' => $country_list,					
				'links'=>array(
				   t("Update Information")=>array(Yii::app()->controller->id.'/edit'),		        
				   isset($model->restaurant_name)?stripslashes(ucwords($model->restaurant_name)):''  
				),	    		   
				'unit'=>AttributesTools::unit(),	
				'zone_type'=>AttributesTools::zoneType(),
			);	
			
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantAttMenu;		   
			   $menu->merchant_type = isset($model->merchant_type)?$model->merchant_type:'';
			   $menu->main_account = Yii::app()->merchant->getState("main_account");
			   $menu->init();    
			}
				
			$this->render("//tpl/submenu_tpl",array(		    
				'template_name'=>"//vendor/address",
				'widget'=>'WidgetMerchantAttMenu',		
				'avatar'=>$avatar,
				'params'=>$params_model,
				'menu'=>$menu,
				'params_widget'=>array(			   
				   'merchant_type'=>isset($model->merchant_type)?$model->merchant_type:'',
				   'main_account'=>Yii::app()->merchant->getState("main_account")
				)
			));		
			
		} else $this->render("error");
	}

	public function actionmembership()
	{		
		CommonUtility::setMenuActive('.merchant','.merchant_edit');
		$this->pageTitle = t("Edit Merchant - Merchant type");
		
		$id = (integer) Yii::app()->merchant->merchant_id;
		$model = AR_merchant::model()->findByPk( $id );
		if($model){
			
			$model->scenario='membership';

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
				}
			}		
			
			$model->percent_commision = number_format( (float) $model->percent_commision,2);
			
			$avatar = CMedia::getImage($model->logo,$model->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));

			$params_model = array(		
				'model'=>$model,					
				'links'=>array(
				    t("Update Information")=>array(Yii::app()->controller->id.'/edit'),		        
			        isset($model->restaurant_name)?stripslashes(ucwords($model->restaurant_name)):''
				),	    		   
			);	
			
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantAttMenu;		   
			   $menu->merchant_type = isset($model->merchant_type)?$model->merchant_type:'';
			   $menu->main_account = Yii::app()->merchant->getState("main_account");
			   $menu->init();    
			}
				
			$this->render("//tpl/submenu_tpl",array(		    
				'template_name'=>"membership",
				'widget'=>'WidgetMerchantAttMenu',		
				'avatar'=>$avatar,
				'params'=>$params_model,
				'menu'=>$menu,
				'params_widget'=>array(			   
				   'merchant_type'=>isset($model->merchant_type)?$model->merchant_type:'',
				   'main_account'=>Yii::app()->merchant->getState("main_account")
				)
			));		
			
		} else $this->render("error");
	}
	

	public function actionfeatured()
	{
		CommonUtility::setMenuActive('.merchant','.merchant_edit');
		$this->pageTitle = t("Edit Merchant - Featured");
		
		$id = (integer) Yii::app()->merchant->merchant_id;
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
				   t("Update Information")=>array(Yii::app()->controller->id.'/edit'),		        
				   isset($model->restaurant_name)?stripslashes(ucwords($model->restaurant_name)):''  
				),	    		   
			);	
			
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantAttMenu;		   
			   $menu->merchant_type = isset($model->merchant_type)?$model->merchant_type:'';
			   $menu->main_account = Yii::app()->merchant->getState("main_account");
			   $menu->init();    
			}
				
			$this->render("//tpl/submenu_tpl",array(		    
				'template_name'=>"//vendor/featured",
				'widget'=>'WidgetMerchantAttMenu',		
				'avatar'=>$avatar,
				'params'=>$params_model,
				'menu'=>$menu,
				'params_widget'=>array(			   
				   'merchant_type'=>isset($model->merchant_type)?$model->merchant_type:'',
				   'main_account'=>Yii::app()->merchant->getState("main_account")
				)
			));				
			
		} else $this->render("error");
	}
	
	public function actionpayment_history()
	{
		CommonUtility::setMenuActive('.merchant','.merchant_edit');
		$this->pageTitle = t("Merchant - Payment history");
		
		$id = (integer) Yii::app()->merchant->merchant_id;
		$model = AR_merchant::model()->findByPk( $id );
		if($model){
			
			/*$action_name='payment_history';
			$delete_link = Yii::app()->CreateUrl(Yii::app()->controller->id."/delete");
			
			ScriptUtility::registerScript(array(
			  "var action_name='$action_name';",
			  "var delete_link='$delete_link';",
			),'action_name');*/
			
									
			$avatar = CMedia::getImage($model->logo,$model->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));
			
			$nav = array(
			   t("Update Information")=>array(Yii::app()->controller->id.'/edit'),		        
			   isset($model->restaurant_name)?stripslashes(ucwords($model->restaurant_name)):''
			);	
			
			
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
			
			/*$this->render("//tpl/submenu_tpl",array(		    
				'template_name'=>"//vendor/featured",
				'widget'=>'WidgetMerchantAttMenu',		
				'avatar'=>$avatar,
				'params'=>$params_model,
				'menu'=>$menu,
				'params_widget'=>array(			   
				   'merchant_type'=>isset($model->merchant_type)?$model->merchant_type:'',
				   'main_account'=>Yii::app()->merchant->getState("main_account")
				)
			));		*/
			
			$this->render("//tpl/submenu_tpl",array(
			  'template_name'=>"//vendor/payment_history",
			  'widget'=>'WidgetMerchantAttMenu',		
			  'model'=>$model,			  
			  'avatar'=>$avatar,
			  'nav'=>$nav,	
			  'ctr'=>Yii::app()->controller->id,		
			  'params'=>array(
			    'model'=>$model,			    
			    'table_col'=>$table_col,
			    'columns'=>$columns,
			    'order_col'=>1,
	            'sortby'=>'desc', 
	            'merchant_id'=>$id,
	            'ajax_url'=>Yii::app()->createUrl("/apibackend"),	
	            'links'=>array(
				   t("Update Information")=>array(Yii::app()->controller->id.'/edit'),		        
				   isset($model->restaurant_name)?stripslashes(ucwords($model->restaurant_name)):''  
				),	             
			  ),
			  'params_widget'=>array(			   
				   'merchant_type'=>isset($model->merchant_type)?$model->merchant_type:'',
				   'main_account'=>Yii::app()->merchant->getState("main_account")
				)
		    ));
						
		} else $this->render("error");
	}	
	
	public function actionsettings()
	{		
		$this->pageTitle=t("Basic Settings");
		$id = (integer) Yii::app()->merchant->merchant_id;
		$merchant = AR_merchant::model()->findByPk( $id );
		
		if($merchant){
			
			$avatar = CMedia::getImage($merchant->logo,$merchant->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));
			
			$model=new AR_option;
		    $model->scenario=Yii::app()->controller->action->id;			
			
		    $options = array(
				'enabled_private_menu','merchant_two_flavor_option','merchant_tax_number',
				'merchant_extenal','merchant_enabled_voucher',
				'merchant_enabled_tip','merchant_default_tip',
				'merchant_close_store','merchant_disabled_ordering',
				'tips_in_transactions','merchant_tip_type','merchant_enabled_language','merchant_default_language','merchant_default_preparation_time',
				'merchant_whatsapp_phone_number','merchant_enabled_whatsapp','auto_print_status','merchant_enabled_age_verification',
				'merchant_time_selection'
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
						$merchant->disabled_ordering = intval($model->merchant_disabled_ordering);
						$merchant->save();								
												
						Yii::import('ext.runactions.components.ERunActions');	
						$cron_key = CommonUtility::getCronKey();		
						$params = [
							'key'=>$cron_key,
							'base_currency'=>$model->merchant_default_currency
						];												
						if(!empty($model->merchant_default_currency)){
							CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/taskexchangerate/singleupdate?".http_build_query($params) );
						}						
						
						$this->refresh();
					} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
				} else Yii::app()->user->setFlash('error',t(HELPER_CORRECT_FORM));
			}
			
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantSettings;
	           $menu->init();    
			}
			
			$model->merchant_disabled_ordering = $merchant->disabled_ordering;
			$model->merchant_close_store = $merchant->close_store;

			if(!empty($model->tips_in_transactions)){
				$model->tips_in_transactions = json_decode($model->tips_in_transactions,true);
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
			
			$multi_currency_enabled =  isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
			$multi_currency_enabled = $multi_currency_enabled==1?true:false;			

			$allowed_merchant_choose_currency =  isset(Yii::app()->params['settings']['multicurrency_allowed_merchant_choose_currency'])?Yii::app()->params['settings']['multicurrency_allowed_merchant_choose_currency']:false;
			$allowed_merchant_choose_currency = $allowed_merchant_choose_currency==1?true:false;			
			
			$status_list = AttributesTools::getOrderStatus(Yii::app()->language);
		    $new_status = ['none' => t("Please select") ];
		    $status_list = array_merge($new_status, $status_list);					
						
			$this->render("//tpl/submenu_tpl",array(
			    'model'=>$merchant,
				'template_name'=>"settings",
				'widget'=>'WidgetMerchantSettings',		
				'avatar'=>$avatar,
				'params'=>array(  
				   'model'=>$model,			   
				   'links'=>array(	
				     t("Settings")=>array(Yii::app()->controller->id.'/settings'),		        
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
				   'multi_currency_enabled'=>$multi_currency_enabled,
				   'allowed_merchant_choose_currency'=>$allowed_merchant_choose_currency,
				   'status_list'=>$status_list
				 ),
				 'menu'=>$menu
			));
			
		} else $this->render("error");
	}
	
	public function actionstore_hours()
	{
		InlineCSTools::registerStoreHours();
		
		$this->pageTitle=t("Regular Menu Hours");
		CommonUtility::setMenuActive('.merchant','.merchant_settings');
		
		$id = (integer) Yii::app()->merchant->merchant_id;
		$model = AR_merchant::model()->findByPk( $id );
		
		if($model){
			
			$avatar = CMedia::getImage($model->logo,$model->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));
									
			$menu=array();
			if(Yii::app()->params['isMobile']==TRUE){
				$tpl = '//tpl/lazy_list';
				$menu = new WidgetMerchantSettings;
	            $menu->init();    
			} else $tpl = 'store_hours_new';		
			
			$data = CMerchants::getOpeningHours($id);			
			
			$this->render("//tpl/submenu_tpl",array(
			    'model'=>$model,
				'template_name'=>$tpl,
				'widget'=>'WidgetMerchantSettings',		
				'avatar'=>$avatar,
				'params'=>array(  
				   'model'=>$model,			
				   'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/store_hours_create"),
				   'links'=>array(	
				     t("Settings")=>array(Yii::app()->controller->id.'/settings'),		        
		             isset($model->restaurant_name)?stripslashes(ucwords($model->restaurant_name)):''	            
				   ),
				   'days'=>AttributesTools::dayList(),
				   'data'=>(array)$data
				 ),
				 'menu'=>$menu,				 
			));
			
		} else $this->render("error");
	}
	
	public function actionstore_hours_create($update=false)
	{
		$this->pageTitle = t("Update Store Hours");
		CommonUtility::setMenuActive('.merchant','.merchant_settings');	
		CommonUtility::setSubMenuActive(".merchant-settings",'.store-hours');	
		
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;
		$merchant = AR_merchant::model()->findByPk( $merchant_id );
		if($merchant){
			
			$avatar = CMedia::getImage($merchant->logo,$merchant->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));
					
			
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantSettings;
	           $menu->init();    
			}
		
			$this->render("//tpl/submenu_tpl",array(
			    'model'=>$merchant,
				'template_name'=>"store_hours_create_new",
				'widget'=>'WidgetMerchantSettings',		
				'avatar'=>$avatar,
				'params'=>array(  
				   'model'=>$merchant,							   
				   'links'=>array(	
				     t("Settings")=>array(Yii::app()->controller->id.'/settings'),		        
		             isset($merchant->restaurant_name)?stripslashes(ucwords($merchant->restaurant_name)):''	            
				   ),				   
				 ),
				 'menu'=>$menu		
			));
			
		} else 	$this->render("//admin/error",array(
				 'error'=>array(
				   'message'=>t(HELPER_RECORD_NOT_FOUND)
				 )
		));		
	}

	public function actionstore_hours_update()
	{
		$this->actionstore_hours_create(true);
	}
	
	public function actionstore_hours_delete()
	{
		$id = (integer) Yii::app()->input->get('id');
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;			
		
		$model = AR_opening_hours::model()->find("id=:id AND merchant_id=:merchant_id",array(
		  ':id'=>$id,
		  ':merchant_id'=>$merchant_id
		));		

		if($model){
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array(Yii::app()->controller->id.'/store_hours'));			
		} else $this->render("error");
	}
	
    public function actiontracking_estimation()
	{		
		$this->pageTitle = t("Tracking initial estimation");
		CommonUtility::setMenuActive('.merchant','.merchant_settings');		
		$id = (integer) Yii::app()->merchant->merchant_id;
		$merchant = AR_merchant::model()->findByPk( $id );
		
		if($merchant){
			
			$avatar = CommonUtility::getPhoto($merchant->logo, CommonUtility::getPlaceholderPhoto('merchant_logo'));			
						
			$model=new AR_option;
		    $model->scenario = 'tracking_estimation';
			
		    $options = array(
			 'tracking_estimation_delivery1','tracking_estimation_delivery2'
			);
				
			if($data = OptionsTools::find($options,$id)){
				foreach ($data as $name=>$val) {
					$model[$name]=$val;
				}			
			}
					
		    if(isset($_POST['AR_option'])){
				$model->attributes=$_POST['AR_option'];				
				if($model->validate()){				
					OptionsTools::$merchant_id = $id;					
					if(OptionsTools::save($options, $model, $id)){
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
						$this->refresh();
					} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
				} 
			}
			
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantSettings;
	           $menu->init();    
			}
			
			$this->render("//tpl/submenu_tpl",array(
			    'model'=>$merchant,
				'template_name'=>"tracking_estimation",
				'widget'=>'WidgetMerchantSettings',		
				'avatar'=>$avatar,
				'params'=>array(  
				   'model'=>$model,			   
				   'links'=>array(	
				     t("Settings")=>array(Yii::app()->controller->id.'/settings'),		        
		             isset($merchant->restaurant_name)?stripslashes(ucwords($merchant->restaurant_name)):''	            
				   ),				   
				 ),
				 'menu'=>$menu		
			));
			
		} else $this->render("error");
	}		
	
	public function actiontaxes()
	{
		$this->pageTitle = t("Taxes");
		
		Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl."/assets/js/tax.js?time=".time(),CClientScript::POS_END,[
			'type'=>"module",
			'defer'=>'defer'
		]);

		CommonUtility::setMenuActive('.merchant','.merchant_settings');		
		$merchant_id = intval(Yii::app()->merchant->merchant_id);
		$merchant = AR_merchant::model()->findByPk( $merchant_id );
		
		if(!$merchant){				
			$this->render("//tpl/error",array('error'=>array('message'=>t("merchant not found"))));
			Yii::app()->end();
		}		
				
		$avatar = CMedia::getImage($merchant->logo,$merchant->path,'@thumbnail',
		CommonUtility::getPlaceholderPhoto('merchant_logo'));
		
		$menu = array();
		if(Yii::app()->params['isMobile']==TRUE){
		   $menu = new WidgetMerchantSettings;
           $menu->init();    
		}
			
		$model = new AR_merchant_meta;

		$model_standard = AR_tax::model()->find("merchant_id=:merchant_id AND tax_type=:tax_type",[
			':merchant_id'=>$merchant_id,
			':tax_type'=>'standard'
		]);
		
	    if(isset($_POST['AR_merchant_meta'])){

			if(DEMO_MODE){			
			    $this->render('//tpl/error',array(  
			          'error'=>array(
			            'message'=>t("Modification not available in demo")
			          )
			        ));	
			    return false;
			}

			$post=$_POST['AR_merchant_meta'];			
			AR_merchant_meta::saveMeta($merchant_id,'tax_enabled', isset($post['tax_enabled'])? intval($post['tax_enabled']) :0 );			
			AR_merchant_meta::saveMeta($merchant_id,'tax_type', isset($post['tax_type'])? trim($post['tax_type']) :'' );						
			AR_merchant_meta::saveMeta($merchant_id,'tax_on_delivery_fee', isset($post['tax_on_delivery_fee'])? floatval($post['tax_on_delivery_fee']) :0 );									
			AR_merchant_meta::saveMeta($merchant_id,'tax_service_fee', isset($post['tax_service_fee'])? intval($post['tax_service_fee']) :0 );
			AR_merchant_meta::saveMeta($merchant_id,'tax_packaging', isset($post['tax_packaging'])? intval($post['tax_packaging']) :0 );	
			AR_merchant_meta::saveMeta($merchant_id,'tax_small_order_fee', isset($post['tax_small_order_fee'])? intval($post['tax_small_order_fee']) :0 );

			AR_merchant_meta::saveMeta($merchant_id,'price_included_tax', isset($post['price_included_tax'])? intval($post['price_included_tax']) :0 );	
			$price_included_tax = $post['price_included_tax'] ?? 0;
						
			AR_tax::model()->updateAll(array(
				'tax_in_price' =>$price_included_tax,					
			), "merchant_id=:merchant_id AND tax_type=:tax_type",[
				":merchant_id"=>$merchant_id,
				':tax_type'=>'multiple'
			]);						

			AR_merchant_meta::model()->deleteAll('merchant_id=:merchant_id AND meta_name=:meta_name ',array(
			':merchant_id'=> $merchant_id,
			':meta_name'=>'tax_for_delivery'
			));
			
			if(isset($post['tax_for_delivery'])){
				if(is_array($post['tax_for_delivery']) && count($post['tax_for_delivery'])>=1){
					foreach ($post['tax_for_delivery'] as $tax_delivery) {
						$models = new AR_merchant_meta;
						$models->merchant_id = $merchant_id;
						$models->meta_name = 'tax_for_delivery';
						$models->meta_value = intval($tax_delivery);
						$models->save();
					}                    
				}
			}
			
			if(!$model_standard){
				$model_standard = new AR_tax();
			}
			$model_standard->merchant_id = $merchant_id;
			$model_standard->tax_type = 'standard';
			$model_standard->tax_name = $post['standard_tax_label'] ?? 'Tax';
			$model_standard->tax_in_price = $post['standard_tax_inclusive'] ?? 0;
			$model_standard->tax_rate = $post['standard_tax_value'] ?? 0;
			$model_standard->default_tax = 1;
			$model_standard->save();			
						
			Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
			$this->refresh();
		}
		
		$data = AR_merchant_meta::getMeta($merchant_id,array('tax_enabled','tax_on_delivery_fee','tax_type','tax_service_fee','tax_packaging','tax_small_order_fee','price_included_tax'));
		$model->tax_enabled = isset($data['tax_enabled'])?$data['tax_enabled']['meta_value']:false;				
		$model->tax_on_delivery_fee = isset($data['tax_on_delivery_fee'])?$data['tax_on_delivery_fee']['meta_value']:false;
		$model->tax_type = isset($data['tax_type'])?$data['tax_type']['meta_value']:'';
		$model->tax_service_fee = isset($data['tax_service_fee'])?$data['tax_service_fee']['meta_value']:false;				
		$model->tax_small_order_fee = isset($data['tax_small_order_fee'])?$data['tax_small_order_fee']['meta_value']:false;	
		$model->tax_packaging = isset($data['tax_packaging'])?$data['tax_packaging']['meta_value']:false;				
		$model->tax_for_delivery = CommonUtility::getDataToDropDown("{{merchant_meta}}",'meta_value','meta_value',
		"where merchant_id=".q($merchant_id)." and meta_name='tax_for_delivery' ");		

		$model->price_included_tax = isset($data['price_included_tax'])?$data['price_included_tax']['meta_value']:false;				


		if($model_standard){
			$model->standard_tax_label = $model_standard->tax_name;
			$model->standard_tax_value = $model_standard->tax_rate;
			$model->standard_tax_inclusive = $model_standard->tax_in_price;
		}		

		ScriptUtility::registerScript(array(
			"var tax_type='$model->tax_type';",			
		  ),'tax_script');

		$table_col = array(
		  'tax_uuid'=>array(
		    'label'=>t("ID"),
		    'width'=>'10%'
		  ),		  
		  'tax_name'=>array(
		    'label'=>t("Name"),
		    'width'=>'20%'
		  ),
		  'tax_rate'=>array(
		    'label'=>t("Rate"),
		    'width'=>'15%'
		  ),
		  'active'=>array(
		    'label'=>t("Status"),
		    'width'=>'15%'
		  ),
		  'date_created'=>array(
		    'label'=>t("Actions"),
		    'width'=>'15%'
		  ),
		);
		$columns = array(
		  array('data'=>'tax_uuid','visible'=>false),		  
		  array('data'=>'tax_name'),
		  array('data'=>'tax_rate'),
		  array('data'=>'active'),
		  array('data'=>null,'orderable'=>false,
		     'defaultContent'=>'
		     <div class="btn-group btn-group-actions" role="group">
			    <a class="ref_tax_edit normal btn btn-light tool_tips"><i class="zmdi zmdi-border-color"></i></a>			    
			    <a class="ref_tax_delete normal btn btn-light tool_tips"><i class="zmdi zmdi-delete"></i></a>			    
			 </div>
		     '
		  ),
		);					
		
		$this->render("//tpl/submenu_tpl",array(
		    'model'=>$merchant,
			'template_name'=>"tax_settings",
			'widget'=>'WidgetMerchantSettings',		
			'avatar'=>$avatar,			
			'params'=>array(  
			   'model'=>$model,			   
			   'table_col'=>$table_col,
		       'columns'=>$columns,
		       'order_col'=>1,
               'sortby'=>'desc',  
		       'tax_type_list'=>CommonUtility::taxType(),
		       'tax_in_price_list'=>CommonUtility::taxPriceList(),
		       'mutilple_tax_list'=>CommonUtility::getDataToDropDown("{{tax}}",'tax_id','tax_name',
			   "WHERE tax_type='multiple' and merchant_id=".q($merchant_id)."
			   "),
			   'links'=>array(	
			     t("Settings")=>array(Yii::app()->controller->id.'/settings'),		        
	             isset($merchant->restaurant_name)?stripslashes(ucwords($merchant->restaurant_name)):''	            
			   ),				   
			 ),
			 'menu'=>$menu
		));
	}
	
    public function actionsocial_settings()
	{		
		$this->pageTitle = t("Social Settings");
		CommonUtility::setMenuActive('.merchant','.merchant_settings');		
		$id = (integer) Yii::app()->merchant->merchant_id;
		$merchant = AR_merchant::model()->findByPk( $id );
		
		if($merchant){
						
			$avatar = CMedia::getImage($merchant->logo,$merchant->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));
									
			$model=new AR_option;
		    $model->scenario = 'social_settings';
			
		    $options = array(
			 'facebook_page','twitter_page','google_page','instagram_page',
			 'merchant_fb_flag','merchant_fb_app_id','merchant_fb_app_secret','merchant_google_login_enabled','merchant_google_client_id','merchant_google_client_secret',
			 'merchant_app_enabled_apple_login','merchant_app_apple_app_id','merchant_app_apple_team_id','merchant_app_apple_key_id','merchant_app_apple_service_file',
			 'merchant_fb_client_token'
			);
					
			if($data = OptionsTools::find($options,$id)){
				foreach ($data as $name=>$val) {
					$model[$name]=$val;
				}			
			}
				
		    if(isset($_POST['AR_option'])){
				$model->attributes=$_POST['AR_option'];			
				$model->file_crt = CUploadedFile::getInstance($model,'file_crt');	

				if(DEMO_MODE && in_array($id,DEMO_MERCHANT)){
					$this->render('//tpl/error',array(  
						 'error'=>array(
						   'message'=>t("Modification not available in demo")
						 )
					   ));	
				   return false;
			   }			

				if($model->validate()){		
										
					if($model->file_crt){
						$path = CommonUtility::uploadDestination('upload/crt/').$model->file_crt->name;
						$extension = strtolower($model->file_crt->getExtensionName());				   
						if($extension=="p8"){
							$model->merchant_app_apple_service_file = $model->file_crt->name;
							$model->file_crt->saveAs( $path );	
						} else {
							Yii::app()->user->setFlash('error', t("Invalid file extension must be a .json file") );					
							return false;
						}
					}	
					
					OptionsTools::$merchant_id = $id;					
					if(OptionsTools::save($options, $model, $id)){
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
						$this->refresh();
					} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
				} 
			}
						
			
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantSettings;
	           $menu->init();    
			}
			
			$this->render("//tpl/submenu_tpl",array(
			    'model'=>$merchant,
				'template_name'=>"social_settings",
				'widget'=>'WidgetMerchantSettings',		
				'avatar'=>$avatar,
				'params'=>array(  
				   'model'=>$model,			   
				   'links'=>array(	
				     t("Settings")=>array(Yii::app()->controller->id.'/settings'),		        
		             isset($merchant->restaurant_name)?stripslashes(ucwords($merchant->restaurant_name)):''	            
				   ),			
				   'web_redirect_uri'=>CommonUtility::getHomebaseUrl()."/partnerapi/apple_callback",
    		       'app_redirect_uri'=>CommonUtility::getHomebaseUrl()."/partnerapi/app_apple_callback",	   
				 ),
				 'menu'=>$menu,
			));
			
		} else $this->render("error");
	}			
	
    public function actionnotification_settings()
	{		
		$this->pageTitle = t("Notification Settings");
		CommonUtility::setMenuActive('.merchant','.merchant_settings');		
		$id = (integer) Yii::app()->merchant->merchant_id;
		$merchant = AR_merchant::model()->findByPk( $id );
		
		if($merchant){
						
			$avatar = CMedia::getImage($merchant->logo,$merchant->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));
						
			$model=new AR_option;
		    $model->scenario = 'social_settings';
			
		    $options = array(
			 'merchant_enabled_alert',
			 'merchant_email_alert',
			 'merchant_mobile_alert',
			 'merchant_enabled_continues_alert',
			 'merchant_continues_alert_interval',
			 'merchant_enabled_tableside_alert',
			 'merchant_enable_new_order_alert',
			 'merchant_new_order_alert_interval'
			);
					
			if($data = OptionsTools::find($options,$id)){
				foreach ($data as $name=>$val) {
					$model[$name]=$val;
				}			
			}
				
		    if(isset($_POST['AR_option'])){
				$model->attributes=$_POST['AR_option'];				
				if($model->validate()){				
					OptionsTools::$merchant_id = $id;					
					if(OptionsTools::save($options, $model, $id)){
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
						$this->refresh();
					} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
				} 
			}
						
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantSettings;
	           $menu->init();    
			}
		
			$this->render("//tpl/submenu_tpl",array(
			    'model'=>$merchant,
				'template_name'=>"notification_settings",
				'widget'=>'WidgetMerchantSettings',		
				'avatar'=>$avatar,
				'params'=>array(  
				   'model'=>$model,			   
				   'links'=>array(	
				     t("Settings")=>array(Yii::app()->controller->id.'/settings'),		        
		             isset($merchant->restaurant_name)?stripslashes(ucwords($merchant->restaurant_name)):''	            
				   ),				   
				 ),
				 'menu'=>$menu		
			));
			
		} else $this->render("error");
	}			
	
    public function actionorders_settings()
	{		
		$this->pageTitle = t("Orders Settings");
		CommonUtility::setMenuActive('.merchant','.merchant_settings');		
		$id = (integer) Yii::app()->merchant->merchant_id;
		$merchant = AR_merchant::model()->findByPk( $id );
		
		if($merchant){
						
			$avatar = CMedia::getImage($merchant->logo,$merchant->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));
						
			$model=new AR_option;		    
			
		    $options = array(
			 'merchant_order_critical_mins','merchant_order_reject_mins'
			);
					
			if($data = OptionsTools::find($options,$id)){
				foreach ($data as $name=>$val) {
					$model[$name]=$val;
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
				$model->attributes=$_POST['AR_option'];				
				if($model->validate()){				
					OptionsTools::$merchant_id = $id;					
					if(OptionsTools::save($options, $model, $id)){
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
						$this->refresh();
					} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
				} 
			}
						
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantSettings;
	           $menu->init();    
			}
		
			$this->render("//tpl/submenu_tpl",array(
			    'model'=>$merchant,
				'template_name'=>"order_settings",
				'widget'=>'WidgetMerchantSettings',		
				'avatar'=>$avatar,
				'params'=>array(  
				   'model'=>$model,			   
				   'links'=>array(	
				     t("Settings")=>array(Yii::app()->controller->id.'/settings'),		        
		             isset($merchant->restaurant_name)?stripslashes(ucwords($merchant->restaurant_name)):''	            
				   ),				   
				 ),
				 'menu'=>$menu		
			));
			
		} else $this->render("error");
	}				

	public function actioncredit_card()
	{		
		$this->pageTitle=t("Manage Credit Card");
		$action_name='credit_card_list';
		$delete_link = Yii::app()->CreateUrl(Yii::app()->controller->id."/credit_card_delete");
		
		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
		),'action_name');
		
		if(Yii::app()->params['isMobile']==TRUE){
			$tpl = '//tpl/lazy_list';
		} else $tpl = 'credit_card_list';
		
		$this->render($tpl,array(
		  'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/credit_card_create")
		));	
	}
	
	public function actioncredit_card_create($update=false)
	{
		$this->pageTitle = $update==false? t("Add Credit Card") : t("Update Credit Card");
		CommonUtility::setMenuActive('.merchant','.merchant_credit_card');			
		
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;
		$id='';		
		
		if($update){
			$id = (integer) Yii::app()->input->get('id');	
			$model = AR_merchant_cards::model()->findByPk( $id );				
			if(!$model){				
				$this->render("error");				
				Yii::app()->end();
			}			
						
			$model->expiration = $model->expiration_month."/".$model->expiration_yr;
			
			try {
				$model->credit_card_number = CreditCardWrapper::decryptCard($model->encrypted_card);				
			} catch (Exception $e) {
				//
			}								
			
		} else {			
			$model=new AR_merchant_cards;			
		}

		if(isset($_POST['AR_merchant_cards'])){
			$model->attributes=$_POST['AR_merchant_cards'];
			if($model->validate()){		
				$model->merchant_id = $merchant_id;
				if($model->save()){
					if(!$update){
					   $this->redirect(array(Yii::app()->controller->id.'/credit_card'));		
					} else {
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
						$this->refresh();
					}
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			}
		}
							
		$this->render("credit_card_create",array(
		    'model'=>$model,	
		    'links'=>array(
	            t("All Credit Card")=>array(Yii::app()->controller->id.'/credit_card'),        
                $this->pageTitle,
		    ),	    		    
		));
	}	
	
	public function actioncredit_card_update()
	{
		$this->actioncredit_card_create(true);
	}
	
	public function actioncredit_card_delete()
	{
		$id = (integer) Yii::app()->input->get('id');			
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;
		
		$model = AR_merchant_cards::model()->find("mt_id=:mt_id AND merchant_id=:merchant_id",array(
		  ':mt_id'=>$id,
		  ':merchant_id'=>$merchant_id
		));	
		if($model){
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array(Yii::app()->controller->id.'/credit_card'));			
		} else $this->render("error");
	}
	
	public function actionall_order()
	{
		$this->pageTitle=t("All Orders");
		$action_name='order_list_new';
		$delete_link = Yii::app()->CreateUrl(Yii::app()->controller->id."/delete");
		
		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
		),'action_name');
		
		if(Yii::app()->params['isMobile']==TRUE){
			$tpl = 'list_app';
		} else $tpl = 'list_new';
		
		
		InlineCSTools::registerServicesCSS();
		
		$this->render("//order/$tpl",array(
		  'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/create_order")
		));	
	}
	
	public function actionorder_view()
	{
		$this->pageTitle = t("View Order");
		
		CommonUtility::setMenuActive('.merchant_orders','.merchant_all_order');			
		
		$id = Yii::app()->input->get('id');		
		require Yii::getPathOfAlias('frontend')."/models/AR_ordernew.php";
						
		$model = AR_ordernew::model()->cache(Yii::app()->params->cache, 
		CCacheData::dependency() )->find('order_uuid=:order_uuid', 
		array(':order_uuid'=>$id));
		
		if($model){
			$this->pageTitle = t("View Order #[order_id]",array(
			  '[order_id]'=>$model->order_id
			));
			$this->render("//order/view",array(
			  'links'=>array(
	            t("All Orders")=>array(Yii::app()->controller->id.'/all_order'),        
                $this->pageTitle,
		      ),	    	
			));	
		} else {
			$this->render("//tpl/error",array(
			 'error'=>array(
			   'message'=>t(HELPER_RECORD_NOT_FOUND)
			 )
			));		
		}
	}
	
	public function actionorder_edit()
	{
		CommonUtility::setMenuActive('.merchant_orders','.merchant_all_order');			
		
		$id = Yii::app()->input->get('id');		
		require Yii::getPathOfAlias('frontend')."/models/AR_ordernew.php";
						
		$model = AR_ordernew::model()->cache(Yii::app()->params->cache, 
		CCacheData::dependency() )->find('order_uuid=:order_uuid', 
		array(':order_uuid'=>$id));
		
		if($model){
			$this->render("//order/view",array(
			  
			));	
		} else {
			$this->render("//tpl/error",array(
			 'error'=>array(
			   'message'=>t(HELPER_RECORD_NOT_FOUND)
			 )
			));		
		}
	}
	
	public function actionarchive_order()
	{
		$this->pageTitle=t("All Orders");
		$action_name='order_list';
		$delete_link = Yii::app()->CreateUrl(Yii::app()->controller->id."/delete");
		
		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
		),'action_name');
		
		if(Yii::app()->params['isMobile']==TRUE){
			$tpl = 'list_app';
		} else $tpl = 'list';
		
		$this->render("//order/$tpl",array(
		  'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/create_order")
		));	
	}
	
	public function actiondelete()
	{
		$id = Yii::app()->input->get('id');		
		$model = AR_orders::model()->find('order_id_token=:order_id_token', array(':order_id_token'=>$id));
		if($model){				
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array(Yii::app()->controller->id.'/all_order'));			
		} else $this->render("error");
	}

    public function actionorder_cancel_list()
	{
		$this->pageTitle=t("Cancel Orders");
		$action_name='order_list_cancel';
		$delete_link = Yii::app()->CreateUrl("order/delete");
		
		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
		),'action_name');
				
		if(Yii::app()->params['isMobile']==TRUE){
			$tpl = 'list_app';
		} else $tpl = 'list';
		
		$this->render("//order/$tpl",array(
		  'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/create_order")
		));	
	}	
	
	public function actiontime_management()
	{
		$this->pageTitle=t("Order limit");
		$action_name='time_managment_list';
		$delete_link = Yii::app()->CreateUrl(Yii::app()->controller->id."/time_mgt_delete");
		
		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
		),'action_name');
				
		if(Yii::app()->params['isMobile']==TRUE){
			$tpl = '//tpl/lazy_list';
		} else $tpl = 'time_managment_list';
		
		$this->render($tpl,array(
		  'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/time_management_create")
		));	
	}
	
	public function actiontime_management_create($update=false)
	{
		$this->pageTitle = $update==false? t("Add Time Management") : t("Update Time Management");
		CommonUtility::setMenuActive('.merchant','.merchant_time_management');			
		
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;
		$id='';		
		
		if($update){
			$id = (integer) Yii::app()->input->get('id');	
			
			$stmt="
			SELECT id,group_id,transaction_type,start_time,end_time,number_order_allowed,
			order_status,status,
			GROUP_CONCAT(days) as days
			 FROM
			{{order_time_management}}
			WHERE
			merchant_id=:merchant_id
			and group_id =:group_id
			GROUP BY group_id			
			";		
				
			$model = AR_order_time_mgt::model()->findBySql($stmt,array(
			  ':merchant_id'=>$merchant_id,
			  ':group_id'=>$id
			));		
						
			if(!$model){				
				$this->render("error");				
				Yii::app()->end();
			}	
			
			$day_selected = explode(",",$model->days);
			$model->days_selected = (array) $day_selected;	
			
			if(!empty($model->order_status)){
				if($order_status = json_decode($model->order_status,true)){
				   $model->order_status_selected = $order_status;
				}
			}
					
		} else {			
			$model=new AR_order_time_mgt;			
		}

		if(isset($_POST['AR_order_time_mgt'])){
			$model->attributes=$_POST['AR_order_time_mgt'];
			if($model->validate()){		
				$model->merchant_id = $merchant_id;
				if($model->save()){
					if(!$update){
					   $this->redirect(array(Yii::app()->controller->id.'/time_management'));		
					} else {
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
						$this->refresh();
					}
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			} else Yii::app()->user->setFlash('error',t(HELPER_CORRECT_FORM));
		}
							
		$this->render("time_management_create",array(
		    'model'=>$model,	
		    'services'=>AttributesTools::ListSelectServicesNew(Yii::app()->language),
		    'days'=>AttributesTools::dayList(),
		    'order_status'=>AttributesTools::StatusList(Yii::app()->language),
		    'status'=>(array)AttributesTools::StatusManagement('post',Yii::app()->language),
		    'links'=>array(
	            t("All Time")=>array(Yii::app()->controller->id.'/time_management'),        
                $this->pageTitle,
		    ),	    		    
		));
	}			
	
	public function actiontime_management_update()
	{
		$this->actiontime_management_create(true);
	}
	
	public function actiontime_mgt_delete()
	{
		$id = (integer) Yii::app()->input->get('id');	
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;
						
		$model = AR_order_time_mgt::model()->deleteAll("merchant_id=:merchant_id AND group_id=:group_id",array(
		  ':merchant_id'=>$merchant_id,
		  ':group_id'=>$id
		));
				
		Yii::app()->user->setFlash('success', t("Succesful") );					
		$this->redirect(array(Yii::app()->controller->id.'/time_management'));			
	}
	
	public function actioncoupon()
	{
		$this->pageTitle=t("Coupon list");
		$action_name='coupon_list';
		$delete_link = Yii::app()->CreateUrl("merchant/coupon_delete");
		
		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
		),'action_name');
				
		if(Yii::app()->params['isMobile']==TRUE){
			$tpl = '//tpl/lazy_list';
		} else $tpl = '//promo/coupon_list';
		
		$this->render( $tpl ,array(
			'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/coupon_create")
		));
	}
	
	public function actioncoupon_create($update=false)
	{
		$this->pageTitle = $update==false? t("Add Coupon") : t("Update Coupon");
		CommonUtility::setMenuActive('.promo',".merchant_coupon");
			
		$id='';	$days = AttributesTools::dayList();		
		$selected_days = array(); $selected_merchant = array();
		$selected_customer = array();		
		
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;
						
		if($update){
			$id = (integer) Yii::app()->input->get('id');	
			$model = AR_voucher::model()->findByPk( $id );						
			if(!$model){				
				$this->render("error");				
				Yii::app()->end();
			}	
						
			foreach ($days as $day=>$dayval) {
				if($model[$day]==1){
					$selected_days[]=$day;
				}
			}				
			
			$model->days_available = $selected_days;	
			$selected_merchant = !empty($model->joining_merchant) ? json_decode(stripslashes($model->joining_merchant)): '';			
			$model->apply_to_merchant = $selected_merchant; 
			$selected_merchant = MerchantAR::getSelected($selected_merchant);			
			
			$selected_customer = !empty($model->selected_customer) ? json_decode(stripslashes($model->selected_customer)): '';			
			$model->apply_to_customer = $selected_customer; 
			$selected_customer = CustomerAR::getSelected($selected_customer);	
			
			$transaction_selected = [];
			$model_trans=AR_merchant_meta::model()->findAll("merchant_id=:merchant_id AND meta_name=:meta_name AND meta_value=:meta_value",array(
				':merchant_id'=>intval($merchant_id),
				':meta_name'=>'coupon',
				':meta_value'=>$id
			));
			if($model_trans){
				foreach ($model_trans as $value) {
					$transaction_selected[] = $value->meta_value1;
				}
				$model->transaction_type = $transaction_selected;			
			}			
		} else {			
			$model=new AR_voucher;							
		}			
		
		if(isset($_POST['AR_voucher'])){
			$model->attributes=$_POST['AR_voucher'];			
			$model->transaction_type = isset($_POST['AR_voucher']['transaction_type'])?$_POST['AR_voucher']['transaction_type']:'';
			if($model->validate()){										
				foreach ($days as $day=>$dayval) {					
					if(in_array($day,$model->days_available)){
						$model[$day]=1;
					} else $model[$day]=0;
				}											
				
				$model->voucher_owner = 'merchant';
				$model->merchant_id = $merchant_id;
				$model->selected_customer = !empty($model->apply_to_customer) ? json_encode($model->apply_to_customer): '';
								
				$model->amount = (float) $model->amount;			
				$model->min_order = (float) $model->min_order;
				$model->max_number_use = (integer) $model->max_number_use;
								
				if($model->save()){
					if(!$update){
					   $this->redirect(array(Yii::app()->controller->id.'/coupon'));		
					} else {
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
						$this->refresh();
					}
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			} 
		}
		
		$model->amount = Price_Formatter::convertToRaw($model->amount,2,true);
		$model->min_order = Price_Formatter::convertToRaw($model->min_order,2,true);
		$model->max_number_use = Price_Formatter::convertToRaw($model->max_number_use,0);
		
		if($model->isNewRecord){
			$model->status = 'publish';
		}
						
		$this->render("coupon_create",array(
		    'model'=>$model,
		    'voucher_type'=>array(),		    
		    'coupon_options'=>array(),
		    'status'=>(array)AttributesTools::StatusManagement('post'),
		    'voucher_type'=>AttributesTools::couponType(),
		    'coupon_options'=>AttributesTools::couponOoptions(),
			'transaction_list'=>AttributesTools::ListSelectServices(),
		    'days'=>$days,		    
		    'selected_customer'=>$selected_customer,
		    'links'=>array(	
		      t("All Coupon")=>array(Yii::app()->controller->id.'/coupon'),		        
              $this->pageTitle
		    ),
		));
	}	
	
	public function actioncoupon_update()
	{
	    $this->actioncoupon_create(true);
	}
		
	public function actioncoupon_delete()
	{
		$id = (integer) Yii::app()->input->get('id');					
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;
		
		$model = AR_voucher::model()->find("merchant_id=:merchant_id AND merchant_id=:merchant_id",array(
		  ':merchant_id'=>$merchant_id,
		  ':voucher_id'=>$id
		));		
		if($model){
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array(Yii::app()->controller->id.'/coupon'));			
		} else $this->render("error");
	}
	
	public function actionoffers()
	{
		$this->pageTitle=t("Offers list");
		$action_name='offer_list';
		$delete_link = Yii::app()->CreateUrl("merchant/offer_delete");
		
		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
		),'action_name');
				
		if(Yii::app()->params['isMobile']==TRUE){
			$tpl = '//tpl/lazy_list';
		} else $tpl = '//merchant/offer_list';
		
		$this->render( $tpl ,array(
			'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/offer_create")
		));
	}
	
	public function actionoffer_create($update=false)
	{
		$this->pageTitle = $update==false? t("Add Offers") : t("Update Offers");
		CommonUtility::setMenuActive('.promo',".merchant_offers");
	
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;
		
		if($update){
			$id = (integer) Yii::app()->input->get('id');
			$model = AR_offers::model()->find("merchant_id=:merchant_id AND offers_id=:offers_id",array(
			  ':merchant_id'=>$merchant_id,
			  ':offers_id'=>$id
			));					
			if(!$model){				
				$this->render("//tpl/error",array(
				 'error'=>array(
				   'message'=>t(HELPER_RECORD_NOT_FOUND)
				 )
				));		
				Yii::app()->end();
			}	
			
			if($model->applicable_to){
				$model->applicable_selected = json_decode($model->applicable_to,true);
			}
			
		} else $model=new AR_offers;	
		
		if(isset($_POST['AR_offers'])){
			$model->attributes=$_POST['AR_offers'];
			if($model->validate()){
				
				$model->merchant_id = $merchant_id;				
				
				if($model->save()){
					if(!$update){						
					   $this->redirect(array(Yii::app()->controller->id.'/offers'));		
					} else {
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
						$this->refresh();
					}
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
				
			} else Yii::app()->user->setFlash('error',t(HELPER_CORRECT_FORM));
		}
		
		$model->status = $model->isNewRecord?'publish':$model->status;	
		$model->offer_percentage = Price_Formatter::convertToRaw($model->offer_percentage,0,true);
		$model->offer_price = Price_Formatter::convertToRaw($model->offer_price,2,true);
				
		$this->render("offers_create",array(
		    'model'=>$model,			    
		    'status'=>(array)AttributesTools::StatusManagement('post'),
		    'services'=>(array)AttributesTools::ListSelectServicesNew(Yii::app()->language),
		    'links'=>array(
	            t("All Offers")=>array(Yii::app()->controller->id.'/offers'),        
	            $this->pageTitle,
		    ),	    
		));			
	}
	
	public function actionoffer_update()
	{
		$this->actionoffer_create(true);
	}
	
	public function actionoffer_delete()
	{
		$id = (integer) Yii::app()->input->get('id');	
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;
						
		$model = AR_offers::model()->find("merchant_id=:merchant_id AND offers_id=:offers_id",array(
		  ':merchant_id'=>$merchant_id,
		  ':offers_id'=>$id
		));	
		if($model){
			$model->delete(); 
		}
		Yii::app()->user->setFlash('success', t("Succesful") );					
		$this->redirect(array(Yii::app()->controller->id.'/offers'));	
	}
	
	public function actionpayment_list()
	{		

		if(Yii::app()->merchant->merchant_type==2){
			$this->render('//tpl/error',array(
			 'error'=>array(
			   'message'=>t("This page is not available in your account.")
			 )
			));
			return ;
		}
			
		$this->pageTitle=t("Merchant Type");
		$action_name='payment_list';
		$delete_link = Yii::app()->CreateUrl("merchant/payment_delete");
		
		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
		),'action_name');
		
		if(Yii::app()->params['isMobile']==TRUE){
			$tpl = 'payment_list_app';
		} else $tpl = 'payment_list';
		
		$this->render( $tpl ,array(
			'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/payment_create")
		));
	}
	
    public function actionpayment_create($update=false)
	{
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;
		
		$this->pageTitle = $update==false? t("Add Gateway") :  t("Update Gateway");
		CommonUtility::setMenuActive('.payment_gateway',".merchant_payment_list");
		
		$multi_language = CommonUtility::MultiLanguage();
		$attr_json = ''; $instructions = array();
		$upload_path = CMedia::adminFolder();
		
		if($update){
			$id =  Yii::app()->input->get('id');	
			$model = AR_payment_gateway_merchant::model()->findByPk( $id );									
			$attr_json = !empty($model->attr_json)?json_decode($model->attr_json,true):'';	
			$instructions=!empty($model->attr4)?json_decode($model->attr4,true):'';											
			if(!$model){				
				$this->render("error");				
				Yii::app()->end();
			}	
			$model->scenario = "update";
		} else {
			$model=new AR_payment_gateway_merchant;	
			$model->scenario = "create";
		}
				
		if(isset($_POST['AR_payment_gateway_merchant'])){
			$model->attributes=$_POST['AR_payment_gateway_merchant'];			
			$model->file = CUploadedFile::getInstance($model,'file');

			if($model->validate()){
				$model->merchant_id = $merchant_id;				

				if($model->scenario=="create"){
					$model_payment = AR_payment_gateway::model()->find("payment_id=:payment_id",[
						':payment_id'=>$model->payment_id
					]);
					if($model_payment){						
						if($model_payment->payment_code=="sumup"){
							$connect_url =  CommonUtility::getHomebaseUrl()."/{$model_payment->payment_code}/api/connect?merchant_id={$merchant_id}";
							$this->redirect($connect_url);							
						}						
					}				
				}				
				
				if ($model->file) {					
					$path = CommonUtility::uploadDestination('upload/crt/').$model->file->name;                					
                    $model->file->saveAs( $path );
					$model->attr3 = $path;
				}

				if($model->save()){
					if(!$update){
					   $this->redirect(array('merchant/payment_update','id'=>$model->payment_uuid));		
					} else {
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
						$this->refresh();
					}
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			} else Yii::app()->user->setFlash('error',t(HELPER_CORRECT_FORM));
		}	
								
		$this->render("payment_create",array(
		    'model'=>$model,		   		    		    
		    'attr_json'=>$attr_json,
		    'provider'=>AttributesTools::PaymentProviderByMerchant($merchant_id),
		    'status'=>(array)AttributesTools::StatusManagement('gateway'),
			'instructions'=>$instructions,
		    'protocol'=> isset($_SERVER["HTTPS"]) ? 'https' : 'http',
			'site_url'=>CommonUtility::getHomebaseUrl(),
			'merchant_id'=>Yii::app()->merchant->merchant_id,
		));
	}		
	
	public function actionpayment_update()
	{
		$this->actionpayment_create(true);
	}
	
	public function actionpayment_delete()
	{
		$id = Yii::app()->input->get('id');		
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;
			
		$model = AR_payment_gateway_merchant::model()->find("payment_uuid=:payment_uuid AND 
		merchant_id=:merchant_id",array(
		  ':payment_uuid'=>$id,
		  ':merchant_id'=>$merchant_id
		));				
		if($model){
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array('merchant/payment_list'));			
		} else $this->render("error");
	}
	
	public function actionall_notification()
	{
		$this->pageTitle=t("All notifications");
				
		$table_col = array(		  
		  'date_created'=>array(
		    'label'=>t("Date"),
		    'width'=>'15%'
		  ),		  
		  'message'=>array(
		    'label'=>t("Message"),
		    'width'=>'60%'
		  ),
		);
		$columns = array(
		  array('data'=>'date_created'),	
		  array('data'=>'message'),		  
		);				
		
		$this->render('//notifications/notifications_all',array(
		  'table_col'=>$table_col,
		  'columns'=>$columns,
		  'order_col'=>1,
          'sortby'=>'desc',		  
          'ajax_url'=>Yii::app()->createUrl("/apibackend"),
		));
	}

	public function actionbanner()
	{
				
		$this->pageTitle=t("Banner");		
				
		$table_col = array(		  
		  'banner_id'=>array(
		    'label'=>t("ID"),
		    'width'=>'15%'
		  ),		  
		  'photo'=>array(
		    'label'=>t("Banner"),
		    'width'=>'15%'
		  ),
		  'status'=>array(
		    'label'=>t("Status"),
		    'width'=>'20%'
		  ),
		  'title'=>array(
		    'label'=>t("Title"),
		    'width'=>'20%'
		  ),		  
		  'banner_type'=>array(
		    'label'=>t("Type"),
		    'width'=>'20%'
		  ),		  
		  'banner_uuid'=>array(
		    'label'=>t("Actions"),
		    'width'=>'20%'
		  ),
		);
		$columns = array(
			array('data'=>'banner_id', 'visible'=>false),
			array('data'=>'photo'),
			array('data'=>'status'),
			array('data'=>'title'),			
			array('data'=>'banner_type'),			
			array('data'=>null,'orderable'=>false,
			   'defaultContent'=>'
			   <div class="btn-group btn-group-actions" role="group">
				  <a class="ref_edit normal btn btn-light tool_tips"><i class="zmdi zmdi-border-color"></i></a>
				  <a class="ref_delete normal btn btn-light tool_tips"><i class="zmdi zmdi-delete"></i></a>
			   </div>
			   '
			),	  
		);		
		
		$this->render('//marketing/banner_list',array(
		  'table_col'=>$table_col,
		  'columns'=>$columns,
		  'order_col'=>1,
          'sortby'=>'desc',		  
          'ajax_url'=>Yii::app()->createUrl("/apibackend"),
		  'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/banner_create"),
		  'sort_link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/banner_sort"),
		));
	}

	public function actionbanner_create($update=false)
	{
		$this->pageTitle=t("Create Banner");
		CommonUtility::setMenuActive('.merchant','.merchant_banner');
		$upload_path = CMedia::merchantFolder();
		$selected_item = array();
		
		$id = Yii::app()->input->get('id');			
		$model = new AR_banner;
		if($update){
			$model = AR_banner::model()->find("banner_uuid=:banner_uuid",array(
			 ':banner_uuid'=>$id
			));
			if(!$model){
				$this->render('//tpl/error',array(
				 'error'=>array(
				   'message'=>t(Helper_not_found)
				 )
				));
				return ;
			}

			if($model->banner_type=="custom_link"){
               $model->custom_link = $model->meta_value3;
			}

			$selected_item = CommonUtility::getDataToDropDown("{{item}}",'item_id','item_name',
			"WHERE item_id=".q($model->meta_value2)."");			
		}

		$model->scenario = 'merchant_banner';
		
		if(isset($_POST['AR_banner'])){
			$model->attributes=$_POST['AR_banner'];			
			
			switch ($model->banner_type) {
				case 'food':
					$model->scenario ="food_banner";
					break;
				case 'custom_link':			
					$model->scenario ="merchant_custom_link";				
					$model->meta_value3 = $model->custom_link;	
					break;
			}

			$model->owner="merchant";
			$model->meta_value1=Yii::app()->merchant->merchant_id;				

			if(isset($_POST['photo'])){
				if(!empty($_POST['photo'])){
					$model->photo = $_POST['photo'];
					$model->path = isset($_POST['path'])?$_POST['path']:$upload_path;
				} else $model->photo = '';
			} else $model->photo = '';
						
			if($model->validate()){														
				if($model->save()){
					if(!$update){
					   $this->redirect(array(Yii::app()->controller->id."/banner"));		
					} else {
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
						$this->refresh();
					}
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString( $model->getErrors() ));
		}
		
		$this->render('//vendor/banner_create',array(		  
		  'model'=>$model,
		  'status'=>(array)AttributesTools::StatusManagement('post'),
		  'banner_type'=>AttributesTools::BannerType(),
		  'upload_path'=>$upload_path,
		  'items'=>$selected_item,
		  'links'=>array(
			    t("Banner")=>array('merchant/banner'),        
			    $this->pageTitle,
			)
		));
	}
	
	public function actionbanner_update()
	{
		$this->actionbanner_create(true);
	}	

	public function actionbanner_delete()
	{
		$id = Yii::app()->input->get('id');	
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;		
				
		$model = AR_banner::model()->find('meta_value1=:meta_value1 AND banner_uuid=:banner_uuid AND owner=:owner', 
		array(':meta_value1'=>$merchant_id, ':banner_uuid'=>$id , 'owner'=>'merchant' ));
		
		if($model){
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array(Yii::app()->controller->id.'/banner'));			
		} else $this->render("error");
	}

	public function actionbanner_sort()
	{
		$this->pageTitle=t("Banner Sort");
		CommonUtility::setMenuActive('.merchant',".merchant_banner");

		$data = [];
		$model = new AR_banner_sort();

		if(isset($_POST['id'])){
			$data = $_POST['id'];
			if(is_array($data) && count($data)>=1){				
				foreach ($data as $index=> $banner_id) {					
					$model = AR_banner_sort::model()->find("banner_id=:banner_id",[						
						':banner_id'=>intval($banner_id)
					]);
					if($model){
						$model->sequence = $index;
						if(!$model->save()){							
						}
					}
				}
				CCacheData::add();
				Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
				$this->refresh();
			}						
		}
		
		try {
			$data_item = CMerchants::getBanner(Yii::app()->merchant->merchant_id,'merchant');
			foreach ($data_item as $items) {
				$data[] = [
					'id'=>$items['banner_id'],
					'name'=>$items['title'],
					'url_image'=>$items['image'],
					'url_icon'=>$items['image'],
				];
			}
		} catch (Exception $e) {			
		}		
				
		$this->render('//tpl/sort',[
			'data'=>$data,
			'model'=>$model,
			'links'=>array(
	            t("All Banner")=>array(Yii::app()->controller->id.'/banner'),        
                $this->pageTitle,
		    ),	    	
		]);
	}

	public function actionpages_list()
	{
		$this->pageTitle=t("Pages list");		
				
		$table_col = array(		  
		  'banner_id'=>array(
		    'label'=>t("#"),
		    'width'=>'10%'
		  ),		  
		  'photo'=>array(
		    'label'=>t("Title"),
		    'width'=>'40%'
		  ),		
		  'banner_uuid'=>array(
		    'label'=>t("Actions"),
		    'width'=>'15%'
		  ),
		);
		$columns = array(
			array('data'=>'page_id'),
			array('data'=>'title'),			
			array('data'=>null,'orderable'=>false,
			   'defaultContent'=>'
			   <div class="btn-group btn-group-actions" role="group">
				  <a class="ref_edit normal btn btn-light tool_tips"><i class="zmdi zmdi-border-color"></i></a>
				  <a class="ref_delete normal btn btn-light tool_tips"><i class="zmdi zmdi-delete"></i></a>
			   </div>
			   '
			),	  
		);		
		
		$this->render('//vendor/pages_list',array(
		  'table_col'=>$table_col,
		  'columns'=>$columns,
		  'order_col'=>1,
          'sortby'=>'desc',		  
          'ajax_url'=>Yii::app()->createUrl("/apibackend"),
		  'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/pages_create")
		));
	}

	public function actionpages_create($update=false)
	{
		$this->pageTitle = $update==false? t("Add Page") : t("Update Page");
		CommonUtility::setMenuActive('.merchant','.merchant_pages_list');
		
		$id='';
		$multi_language = CommonUtility::MultiLanguage();
		$upload_path = CMedia::merchantFolder();
		$merchant_id = intval(Yii::app()->merchant->merchant_id);		
		
		if($update){
			$id = (integer) Yii::app()->input->get('id');	
			$model = AR_pages::model()->find("owner=:owner AND merchant_id=:merchant_id AND page_id=:page_id",[
				':owner'=>"merchant",
				':merchant_id'=>$merchant_id,
				':page_id'=>$id
			]);								
			if(!$model){				
				$this->render("error");				
				Yii::app()->end();
			}				
		} else {			
			$model=new AR_pages;							
		}

		$model->multi_language = $multi_language;
		
		if(isset($_POST['AR_pages'])){
			$model->attributes=$_POST['AR_pages'];
			if($model->validate()){
				$model->slug = CommonUtility::SeoURL($model->slug);
				$model->owner = "merchant";
				$model->merchant_id=Yii::app()->merchant->merchant_id;
								
				if(isset($_POST['photo'])){
					if(!empty($_POST['photo'])){
						$model->meta_image = $_POST['photo'];
						$model->path = isset($_POST['path'])?$_POST['path']:$upload_path;
					} else $model->meta_image = '';
				} else $model->meta_image = '';
												
				if($model->save()){
					if(!$update){
					   $this->redirect(array('merchant/pages_list'));		
					} else {
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
						$this->refresh();
					}
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			} else {
				//dump($model);die();
			}
		}
		
		$data  = array();
		if($update){
			$translation = AttributesTools::pagesTranslation($id);
			$data['title_translation'] = isset($translation['title'])?$translation['title']:'';
			$data['long_content_translation'] = isset($translation['long_content'])?$translation['long_content']:'';
		}		
		
		$fields[]=array(
		  'name'=>'title_translation',
		  'placeholder'=>"Enter [lang] title here"
		);
		$fields[]=array(
		  'name'=>'long_content_translation',
		  'placeholder'=>"Enter [lang] content here",
		  'type'=>"textarea",
		  'class'=>"summernote"
		);
					
		$this->render("//attributes/pages_create",array(
		    'model'=>$model,
		    'upload_path'=>$upload_path,
		    'links'=>array(
	            t("All Pages")=>array('merchant/pages_list'),        
                $this->pageTitle,
		    ),
		    'status_list'=>(array)AttributesTools::StatusManagement('post'),
		    'multi_language'=>$multi_language,
		    'language'=>AttributesTools::getLanguage(),
		    'fields'=>$fields,
		    'data'=>$data,		    
		));
	}	

	public function actionpage_update()
	{
	    $this->actionpages_create(true);
	}

	public function actionpages_delete()
	{
		$merchant_id = intval(Yii::app()->merchant->merchant_id);
		$id = (integer) Yii::app()->input->get('id');			
		$model = AR_pages::model()->find("owner=:owner AND merchant_id=:merchant_id AND page_id=:page_id ",[
			'owner'=>"merchant",
			'merchant_id'=>$merchant_id,
			'page_id'=>$id
		]);
		if($model){
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array('merchant/pages_list'));			
		} else $this->render("error");
	}

	public function actionrecaptcha_settings()
	{
		$this->pageTitle = t("Recaptcha Settings");
		CommonUtility::setMenuActive('.merchant','.merchant_settings');		
		$id = (integer) Yii::app()->merchant->merchant_id;
		$merchant = AR_merchant::model()->findByPk( $id );
		
		if($merchant){
						
			$avatar = CMedia::getImage($merchant->logo,$merchant->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));
									
			$model=new AR_option;
		    $model->scenario = 'recaptcha_settings';
			
		    $options = ['merchant_captcha_enabled','merchant_captcha_site_key','merchant_captcha_secret','merchant_captcha_lang'];
					
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
					if(OptionsTools::save($options, $model, $id)){
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
						$this->refresh();
					} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
				} 
			}
						
			
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantSettings;
	           $menu->init();    
			}
			
			$this->render("//tpl/submenu_tpl",array(
			    'model'=>$merchant,
				'template_name'=>"//vendor/recaptcha_settings",
				'widget'=>'WidgetMerchantSettings',		
				'avatar'=>$avatar,
				'params'=>array(  
				   'model'=>$model,			   
				   'links'=>array(	
				     t("Settings")=>array(Yii::app()->controller->id.'/settings'),		        
		             isset($merchant->restaurant_name)?stripslashes(ucwords($merchant->restaurant_name)):''	            
				   ),				   
				 ),
				 'menu'=>$menu
			));
			
		} else $this->render("error");
	}

	public function actionpages_menu()
	{
		$this->pageTitle=t("Theme menu");
		CommonUtility::setMenuActive('.sales_channel',".theme_changer");
		$this->render("//theme/theme-menu",array(
		   'ajax_url'=>Yii::app()->createUrl("/apibackend"),		   
		   'links'=>array(
				'links'=>array(
				    t("Menu")=>array('merchant/pages_menu'),        				    
				),
				'homeLink'=>false,
				'separator'=>'<span class="separator">
				<i class="zmdi zmdi-chevron-right"></i><i class="zmdi zmdi-chevron-right"></i></span>'
			)
		));		
	}

    public function actionmap_keys()
	{		
		$this->pageTitle = t("Map API Keys");
		CommonUtility::setMenuActive('.merchant','.merchant_settings');		
		$id = (integer) Yii::app()->merchant->merchant_id;
		$merchant = AR_merchant::model()->findByPk( $id );
		
		if($merchant){
						
			$avatar = CMedia::getImage($merchant->logo,$merchant->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));
						
			$model=new AR_option;		    
			
		    $options = array(
			 'merchant_map_provider','merchant_google_geo_api_key','merchant_google_maps_api_key','merchant_mapbox_access_token','merchant_geolocationdb',
			 'merchant_google_maps_api_key_for_mobile'
			);
					
			if($data = OptionsTools::find($options,$id)){
				foreach ($data as $name=>$val) {
					if(DEMO_MODE && in_array($id,DEMO_MERCHANT)){
						$model[$name] = CommonUtility::mask(date("Ymjhs"));
					} else $model[$name]=$val;					
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
				$model->attributes=$_POST['AR_option'];				
				if($model->validate()){				
					OptionsTools::$merchant_id = $id;					
					if(OptionsTools::save($options, $model, $id)){
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
						$this->refresh();
					} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
				} 
			}
						
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantSettings;
	           $menu->init();    
			}
		
			$this->render("//tpl/submenu_tpl",array(
			    'model'=>$merchant,
				'template_name'=>"map_keys",
				'widget'=>'WidgetMerchantSettings',		
				'avatar'=>$avatar,
				'params'=>array(  
				   'model'=>$model,	
				   'map_provider'=>AttributesTools::mapsProvider(),		   
				   'links'=>array(	
				     t("Settings")=>array(Yii::app()->controller->id.'/settings'),		        
		             isset($merchant->restaurant_name)?stripslashes(ucwords($merchant->restaurant_name)):''	            
				   ),				   
				 ),
				 'menu'=>$menu		
			));
			
		} else $this->render("error");
	}			
	
    public function actionlogin_sigup()
	{		
		$this->pageTitle = t("Login & Signup");
		CommonUtility::setMenuActive('.merchant','.merchant_settings');		
		$id = (integer) Yii::app()->merchant->merchant_id;
		$merchant = AR_merchant::model()->findByPk( $id );
		
		if($merchant){
						
			$avatar = CMedia::getImage($merchant->logo,$merchant->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));
						
			$model=new AR_option;		    
			
		    $options = array(
			   'merchant_signup_enabled_verification','merchant_signup_resend_counter','merchant_signup_enabled_terms',
			   'merchant_signup_terms','merchant_enabled_guest'
			);
					
			if($data = OptionsTools::find($options,$id)){
				foreach ($data as $name=>$val) {
					$model[$name]=$val;
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
				$model->attributes=$_POST['AR_option'];				
				if($model->validate()){				
					OptionsTools::$merchant_id = $id;					
					if(OptionsTools::save($options, $model, $id)){
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
						$this->refresh();
					} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
				} 
			}
						
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantSettings;
	           $menu->init();    
			}
		
			$this->render("//tpl/submenu_tpl",array(
			    'model'=>$merchant,
				'template_name'=>"login_signup",
				'widget'=>'WidgetMerchantSettings',		
				'avatar'=>$avatar,
				'params'=>array(  
				   'model'=>$model,			   
				   'links'=>array(	
				     t("Settings")=>array(Yii::app()->controller->id.'/settings'),		        
		             isset($merchant->restaurant_name)?stripslashes(ucwords($merchant->restaurant_name)):''	            
				   ),				   
				 ),
				 'menu'=>$menu		
			));
			
		} else $this->render("error");
	}					

    public function actionphone_settings()
	{		
		$this->pageTitle = t("Phone Settings");
		CommonUtility::setMenuActive('.merchant','.merchant_settings');		
		$id = (integer) Yii::app()->merchant->merchant_id;
		$merchant = AR_merchant::model()->findByPk( $id );
		
		if($merchant){
						
			$avatar = CMedia::getImage($merchant->logo,$merchant->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));
						
			$model=new AR_option;		    
			
		    $options = array(
			   'merchant_mobilephone_settings_country','merchant_mobilephone_settings_default_country'
			);
					
			if($data = OptionsTools::find($options,$id)){
				foreach ($data as $name=>$val) {
					$model[$name]=$val;
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
				$model->attributes=$_POST['AR_option'];				
				if($model->validate()){				
					OptionsTools::$merchant_id = $id;					
					if(OptionsTools::save($options, $model, $id)){
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
						$this->refresh();
					} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
				} 
			}
						
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantSettings;
	           $menu->init();    
			}
		
			$this->render("//tpl/submenu_tpl",array(
			    'model'=>$merchant,
				'template_name'=>"phone_settings",
				'widget'=>'WidgetMerchantSettings',		
				'avatar'=>$avatar,
				'params'=>array(  
				   'model'=>$model,	
				   'country_list'=>AttributesTools::CountryList(),		   
				   'links'=>array(	
				     t("Settings")=>array(Yii::app()->controller->id.'/settings'),		        
		             isset($merchant->restaurant_name)?stripslashes(ucwords($merchant->restaurant_name)):''	            
				   ),				   
				 ),
				 'menu'=>$menu		
			));
			
		} else $this->render("error");
	}						

    public function actionsearch_settings()
	{		
		$this->pageTitle = t("Search Mode");
		CommonUtility::setMenuActive('.merchant','.merchant_settings');		
		$id = (integer) Yii::app()->merchant->merchant_id;
		$merchant = AR_merchant::model()->findByPk( $id );
		
		if($merchant){
						
			$avatar = CMedia::getImage($merchant->logo,$merchant->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));
						
			$model=new AR_option;		    
			
		    $options = array(
			   'merchant_set_default_country'
			);
					
			if($data = OptionsTools::find($options,$id)){
				foreach ($data as $name=>$val) {
					$model[$name]=$val;
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
				$model->attributes=$_POST['AR_option'];				
				if($model->validate()){				
					OptionsTools::$merchant_id = $id;					
					if(OptionsTools::save($options, $model, $id)){
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
						$this->refresh();
					} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
				} 
			}
						
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantSettings;
	           $menu->init();    
			}
		
			$this->render("//tpl/submenu_tpl",array(
			    'model'=>$merchant,
				'template_name'=>"search_settings",
				'widget'=>'WidgetMerchantSettings',		
				'avatar'=>$avatar,
				'params'=>array(  
				   'model'=>$model,	
				   'country_list'=>AttributesTools::CountryList(),		   
				   'links'=>array(	
				     t("Settings")=>array(Yii::app()->controller->id.'/settings'),		        
		             isset($merchant->restaurant_name)?stripslashes(ucwords($merchant->restaurant_name)):''	            
				   ),				   
				 ),
				 'menu'=>$menu		
			));
			
		} else $this->render("error");
	}				
	
	public function actionbank_deposit()
	{
		if(Yii::app()->merchant->merchant_type==2){
			$this->render('//tpl/error',array(
			 'error'=>array(
			   'message'=>t("This page is not available in your account.")
			 )
			));
			return ;
		}

		$this->pageTitle=t("Bank Deposit");
				
		$table_col = array(
		  'deposit_id'=>array(
			'label'=>t("ID"),
			'width'=>'1%'
			),
		  'deposit_uuid'=>array(
			'label'=>t("ID"),
			'width'=>'1%'
		  ),
		  'date_created'=>array(
		    'label'=>t("Date"),
		    'width'=>'5%'
		  ),
		  'proof_image'=>array(
		    'label'=>t("Deposit"),
		    'width'=>'5%'
		  ),
		  'deposit_type'=>array(
		    'label'=>t("Type"),
		    'width'=>'10%'
		  ),
		  'transaction_ref_id'=>array(
		    'label'=>t("Order#"),
		    'width'=>'10%'
		  ),
		  'account_name'=>array(
		    'label'=>t("Account name"),
		    'width'=>'10%'
		  ),
		  'amount'=>array(
		    'label'=>t("Amount"),
		    'width'=>'10%'
		  ),
		  'reference_number'=>array(
		    'label'=>t("Reference Number"),
		    'width'=>'10%'
		  ),
		  'actions'=>array(
		    'label'=>t("Actions"),
		    'width'=>'10%'
		  ),
		);
		$columns = array(
		  array('data'=>'deposit_id','visible'=>false),
		  array('data'=>'deposit_uuid','visible'=>false),
		  array('data'=>'date_created'),
		  array('data'=>'proof_image'),
		  array('data'=>'deposit_type','visible'=>false),
		  array('data'=>'transaction_ref_id'),
		  array('data'=>'account_name'),
		  array('data'=>'amount'),
		  array('data'=>'reference_number'),
		  array('data'=>null,'orderable'=>false,
		     'defaultContent'=>'
		     <div class="btn-group btn-group-actions" role="group">
			    <a class="ref_view_url normal btn btn-light tool_tips"><i class="zmdi zmdi-edit"></i></a>
			    <a class="ref_delete normal btn btn-light tool_tips"><i class="zmdi zmdi-delete"></i></a>
			 </div>
		     '
		  ),	   		  
		);				
				
		$this->render('//merchant/bank_deposit_list',array(
		  'table_col'=>$table_col,
		  'columns'=>$columns,
		  'order_col'=>1,
          'sortby'=>'desc',		  
		));
	}

	public function actionbank_deposit_delete()
	{
		if(Yii::app()->merchant->merchant_type==2){
			$this->render('//tpl/error',array(
			 'error'=>array(
			   'message'=>t("This page is not available in your account.")
			 )
			));
			return ;
		}

		$id =  Yii::app()->input->get('id');			
		$model = AR_bank_deposit::model()->find("deposit_uuid=:deposit_uuid",array(
		  ':deposit_uuid'=>trim($id)
		));
		if($model){
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array('merchant/bank_deposit'));			
		} else $this->render("error");
	}

	public function actionbank_deposit_view()
	{
		if(Yii::app()->merchant->merchant_type==2){
			$this->render('//tpl/error',array(
			 'error'=>array(
			   'message'=>t("This page is not available in your account.")
			 )
			));
			return ;
		}
		
		$this->pageTitle = t("Bank Deposit");
		CommonUtility::setMenuActive('.payment_gateway',".merchant_bank_deposit");

		$id =  Yii::app()->input->get('id');
		$model = AR_bank_deposit::model()->find("deposit_uuid=:deposit_uuid",array(
			':deposit_uuid'=>trim($id)
		));
		
		if(isset($_POST['AR_bank_deposit'])){
			$model->attributes=$_POST['AR_bank_deposit'];
			if($model->validate()){				
				if($model->save()){
					Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
					$this->refresh();
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			} else {				
				Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString($model->getErrors()) );
			}
		}

		if($model){
			$multi_currency_enabled =  isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
			$multi_currency_enabled = $multi_currency_enabled==1?true:false;
			$this->render("//payment_gateway/bank_deposit",[
				'model'=>$model,
				'status'=>AttributesTools::BankStatusList(),
				'multicurrency_enabled'=>$multi_currency_enabled,
				'links'=>array(
					t("Bank Deposit")=>array('merchant/bank_deposit'),        
					$this->pageTitle,
				)
			]);
		} else $this->render("error");
	}

    public function actionmenu_options()
	{		
		$this->pageTitle = t("Menu Options");
		CommonUtility::setMenuActive('.merchant','.merchant_settings');		
		$id = (integer) Yii::app()->merchant->merchant_id;
		$merchant = AR_merchant::model()->findByPk( $id );
		
		if($merchant){
						
			$avatar = CMedia::getImage($merchant->logo,$merchant->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));
						
			$model=new AR_option;		    
			
		    $options = array(
			   'merchant_menu_type','merchant_addons_use_checkbox','menu_display_type'
			);			
					
			if($data = OptionsTools::find($options,$id)){
				foreach ($data as $name=>$val) {
					$model[$name]=$val;
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
				$model->attributes=$_POST['AR_option'];				
				if($model->validate()){				
					OptionsTools::$merchant_id = $id;					
					if(OptionsTools::save($options, $model, $id)){
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
						$this->refresh();
					} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
				} 
			}
						
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantSettings;
	           $menu->init();    
			}
		
			$this->render("//tpl/submenu_tpl",array(
			    'model'=>$merchant,
				'template_name'=>"menu_options",
				'widget'=>'WidgetMerchantSettings',		
				'avatar'=>$avatar,
				'params'=>array(  
				   'model'=>$model,			   
				   'links'=>array(	
				     t("Settings")=>array(Yii::app()->controller->id.'/settings'),		        
		             isset($merchant->restaurant_name)?stripslashes(ucwords($merchant->restaurant_name)):''	            
				   ),				   
				 ),
				 'menu'=>$menu		
			));
			
		} else $this->render("error");
	}					
	
	public function actionmobilepage()
	{
		$this->pageTitle = t("Menu Options");
		CommonUtility::setMenuActive('.merchant','.merchant_settings');		
		$id = (integer) Yii::app()->merchant->merchant_id;
		$merchant = AR_merchant::model()->findByPk( $id );
		
		if($merchant){
						
			$avatar = CMedia::getImage($merchant->logo,$merchant->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));
						
			$model=new AR_option;		    
			
		    $options = array(
			   'merchant_page_privacy_policy','merchant_page_terms','merchant_page_aboutus'
			);			
					
			if($data = OptionsTools::find($options,$id)){
				foreach ($data as $name=>$val) {
					$model[$name]=$val;
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
				$model->attributes=$_POST['AR_option'];				
				if($model->validate()){				
					OptionsTools::$merchant_id = $id;					
					if(OptionsTools::save($options, $model, $id)){
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
						$this->refresh();
					} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
				} 
			}
						
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantSettings;
	           $menu->init();    
			}

			$page_list =  CommonUtility::getDataToDropDown("{{pages}}",'page_id','title',
		    "WHERE merchant_id=".q($id)."","ORDER BY title ASC");
		
			$this->render("//tpl/submenu_tpl",array(
			    'model'=>$merchant,
				'template_name'=>"mobile_page",
				'widget'=>'WidgetMerchantSettings',		
				'avatar'=>$avatar,
				'params'=>array(  
				   'model'=>$model,			   
				   'links'=>array(	
				     t("Settings")=>array(Yii::app()->controller->id.'/settings'),		        
		             isset($merchant->restaurant_name)?stripslashes(ucwords($merchant->restaurant_name)):''	            
				   ),				   
				   'page_list'=>$page_list
				 ),
				 'menu'=>$menu		
			));
			
		} else $this->render("error");
	}

	public function actiontimezone()
	{
		$this->pageTitle = t("Search Mode");
		CommonUtility::setMenuActive('.merchant','.merchant_settings');		
		$id = (integer) Yii::app()->merchant->merchant_id;
		$merchant = AR_merchant::model()->findByPk( $id );
		
		if($merchant){
						
			$avatar = CMedia::getImage($merchant->logo,$merchant->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));
						
			$model=new AR_option;		    
			
		    $options = array(
			   'merchant_timezone','merchant_time_picker_interval'
			);
					
			if($data = OptionsTools::find($options,$id)){
				foreach ($data as $name=>$val) {
					$model[$name]=$val;
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
				$model->attributes=$_POST['AR_option'];				
				if($model->validate()){				
					OptionsTools::$merchant_id = $id;					
					if(OptionsTools::save($options, $model, $id)){
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
						$this->refresh();
					} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
				} 
			}
						
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantSettings;
	           $menu->init();    
			}
		
			$this->render("//tpl/submenu_tpl",array(
			    'model'=>$merchant,
				'template_name'=>"merchant_timezone",
				'widget'=>'WidgetMerchantSettings',		
				'avatar'=>$avatar,
				'params'=>array(  
				   'model'=>$model,	
				   'timezone'=>AttributesTools::timezoneList(),
				   'links'=>array(	
				     t("Settings")=>array(Yii::app()->controller->id.'/settings'),		        
		             isset($merchant->restaurant_name)?stripslashes(ucwords($merchant->restaurant_name)):''	            
				   ),				   
				 ),
				 'menu'=>$menu		
			));
			
		} else $this->render("error");
	}

	public function actionzone_settings()
	{
		$this->pageTitle = t("Zone Settings");
		CommonUtility::setMenuActive('.merchant','.merchant_settings');		
		$id = (integer) Yii::app()->merchant->merchant_id;
		$merchant = AR_merchant::model()->findByPk( $id );
		$meta_name = 'zone';
		
		if($merchant){
						
			$avatar = CMedia::getImage($merchant->logo,$merchant->path,'@thumbnail',
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
		
			$this->render("//tpl/submenu_tpl",array(
			    'model'=>$merchant,
				'template_name'=>"//vendor/zone",
				'widget'=>'WidgetMerchantSettings',		
				'avatar'=>$avatar,
				'params'=>array(  
				   'model'=>$models,	
				   'zone_list'=>CommonUtility::getDataToDropDown("{{zones}}",'zone_id','zone_name',
				           "where merchant_id=".Yii::app()->merchant->merchant_id." ","Order by zone_name asc"),				
				   'links'=>array(	
				     t("Settings")=>array(Yii::app()->controller->id.'/settings'),		        
		             isset($merchant->restaurant_name)?stripslashes(ucwords($merchant->restaurant_name)):''	            
				   ),				   
				 ),				 
			));
			
		} else $this->render("error");
	}

	public function actionseo()
	{
		$this->pageTitle = t("SEO");
		CommonUtility::setMenuActive('.merchant','.merchant_settings');		
		$merchant_id = Yii::app()->merchant->merchant_id;

		$merchant = AR_merchant::model()->findByPk( $merchant_id );
		if($merchant){
			$avatar = CMedia::getImage($merchant->logo,$merchant->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));
		}

		$id = 0; $update=false;

		$model = AR_pages_seo::model()->find("owner=:owner AND merchant_id=:merchant_id",[
			':owner'=>"merchant_seo",
			':merchant_id'=>$merchant_id
		]);
		if($model){
			$update = true;	
			$id = $model->page_id;
		} else $model=new AR_pages_seo;

		if(isset($_POST['AR_pages_seo'])){
            if(DEMO_MODE){			
                $this->render('//tpl/error',array(  
                      'error'=>array(
                        'message'=>t("Modification not available in demo")
                      )
                    ));	
                return false;
            }

            $model->attributes=$_POST['AR_pages_seo'];
            $model->slug = CommonUtility::toSeoURL($model->meta_title);
            if($model->validate()){                                
				$model->owner = "merchant_seo";
				$model->merchant_id = $merchant_id;
                if($model->save()){
					Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
					$this->refresh();
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
            } else Yii::app()->user->setFlash('error',  CommonUtility::parseModelErrorToString($model->getErrors()) );
        }


		$data  = array();
        if($update){
			$translation = AttributesTools::pagesTranslation2($id);            
			$data['meta_title_translation'] = isset($translation['meta_title'])?$translation['meta_title']:'';
			$data['meta_description_translation'] = isset($translation['meta_description'])?$translation['meta_description']:'';
            $data['meta_keywords_translation'] = isset($translation['meta_keywords'])?$translation['meta_keywords']:'';            
		}

        $fields = [];
        $fields[]=array(
            'name'=>'meta_title_translation',
            'placeholder'=>"Enter [lang] meta title here"
        );
        $fields[]=array(
            'name'=>'meta_description_translation',
            'placeholder'=>"Enter [lang] meta description here"
        );
        $fields[]=array(
            'name'=>'meta_keywords_translation',
            'placeholder'=>"Enter [lang] meta keywords here"
        );


		$this->render("//tpl/submenu_tpl",array(
			'model'=>$merchant,
			'template_name'=>"//website/pages_create",
			'widget'=>'WidgetMerchantSettings',		
			'avatar'=>$avatar,
			'params'=>array(  
			   'model'=>$model,				 
			   'fields'=>$fields,  
			   'language'=>AttributesTools::getLanguage(),
			   'status_list'=>(array)AttributesTools::StatusManagement('post'),
			   'data'=>$data,
			   'links'=>[]		   
			 ),				 
		));		
	}

	public function actionpayondelivery()
	{
		$this->pageTitle = t("Pay on delivery");		
		$merchant_id = Yii::app()->merchant->merchant_id;
		$meta_name = 'payondelivery';
		
		$model = AR_merchant_meta::model()->find("merchant_id=:merchant_id AND meta_name=:meta_name",[
			':merchant_id'=>intval($merchant_id),
			':meta_name'=>$meta_name
		]);
		if(!$model){			
			$model = new AR_merchant_meta();
		}				

		$model->scenario = 'payondelivery';

		if(isset($_POST['AR_merchant_meta'])){
			$model->attributes=$_POST['AR_merchant_meta'];	
			$model->meta_name=$meta_name;
			$model->merchant_id = $merchant_id;
			$model->meta_value = json_encode($model->payondelivery_data);			
			if($model->validate()){						    				    				    	
				if($model->save()){						    					    	
					Yii::app()->user->setFlash('success', t(Helper_success) );
					$this->refresh();						
				} else {					
					Yii::app()->user->setFlash('error',CommonUtility::t(Helper_failed_update));
				}				
			} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString($model->getErrors()) );
		}		

		$model->payondelivery_data =  !empty($model->meta_value)? json_decode($model->meta_value,true) : [];		

		$payment_list = [];
		try {
			$payment_list = CPayments::PayondeliveryList();
		} catch (Exception $e) {}				

		$this->render("payondelivery_settings",[
			'model'=>$model,
			'payment_list'=>$payment_list,
			'links'=>array(	
				$this->pageTitle=>array(Yii::app()->controller->id.'/payondelivery')				
			 ),				   
		]);
	}

	public function actionloyalty_points()
	{
		$this->pageTitle = t("Loyalty Points");		
		$merchant_id = Yii::app()->merchant->merchant_id;

		$meta_name = 'loyalty_points';
		
		$model = AR_merchant_meta::model()->find("merchant_id=:merchant_id AND meta_name=:meta_name",[
			':merchant_id'=>intval($merchant_id),
			':meta_name'=>$meta_name
		]);
		if(!$model){			
			$model = new AR_merchant_meta();
		}				

		if(isset($_POST['AR_merchant_meta'])){
			$model->attributes=$_POST['AR_merchant_meta'];	
			$model->meta_name=$meta_name;
			$model->merchant_id = $merchant_id;
			$model->meta_value = $model->loyalty_points;
			if($model->validate()){						    				    				    	
				if($model->save()){						    					    	
					Yii::app()->user->setFlash('success', t(Helper_success) );
					$this->refresh();						
				} else {					
					Yii::app()->user->setFlash('error',CommonUtility::t(Helper_failed_update));
				}				
			} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString($model->getErrors()) );
		}		

		$this->render('loyalty_points',[
			'model'=>$model,
			'links'=>array(	
				$this->pageTitle=>array(Yii::app()->controller->id.'/loyalty_points')				
			 ),				   
		]);
	}

	public function actionlocation_management()
	{

		$this->pageTitle = t("Location Management");
		CommonUtility::setMenuActive('.merchant','.merchant_settings');		
		$id = (integer) Yii::app()->merchant->merchant_id;
		$merchant = AR_merchant::model()->findByPk( $id );

		if($merchant){

			$avatar = CMedia::getImage($merchant->logo,$merchant->path,'@thumbnail',
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
					t("Settings")=>array(Yii::app()->controller->id.'/location_management'),		        
					isset($merchant->restaurant_name)?stripslashes(ucwords($merchant->restaurant_name)):''	            
				),	
				'ajax_url'=>Yii::app()->createUrl("/apibackend"),
			);	

			$action_name = 'getManageLocation';
			ScriptUtility::registerScript(array(
				"var action_get='$action_name';",				
			),'location_manage');		
			
			$this->render("//tpl/submenu_tpl",array(
			    'model'=>$merchant,
				'template_name'=>"//vendor/location_management",
				'widget'=>'WidgetMerchantSettings',		
				'avatar'=>$avatar,
				'params'=>$params_model,				
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
		$model = AR_merchant_location::model()->find("merchant_id=:merchant_id AND id=:id",[
			':merchant_id'=>Yii::app()->merchant->merchant_id,
			':id'=>$id
		]);
		if($model){
			$model->delete();
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array(Yii::app()->controller->id.'/location_management'));			
		} else $this->render("error");
	}

	public function actiondelivery_management()
	{

		$this->pageTitle = t("Delivery Management");
		CommonUtility::setMenuActive('.merchant','.merchant_settings');		
		$id = (integer) Yii::app()->merchant->merchant_id;
		$merchant = AR_merchant::model()->findByPk( $id );

		if($merchant){

			$avatar = CMedia::getImage($merchant->logo,$merchant->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));

			$table_col = array(
				'rate_id'=>array(
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
				'delivery_fee'=>array(
				  'label'=>t("Fee"),
				  'width'=>'10%'
				),			
				'date_created'=>array(
					'label'=>t("Actions"),
					'width'=>'15%'
				),		  
			  );
			  $columns = array(			
				array('data'=>'rate_id','visible'=>true),
				array('data'=>'country_name'),
				array('data'=>'state_name'),		  
				array('data'=>'city_name'),		  
				array('data'=>'area_name'),		  
				array('data'=>'delivery_fee'),		  
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
					t("Settings")=>array(Yii::app()->controller->id.'/location_management'),		        
					isset($merchant->restaurant_name)?stripslashes(ucwords($merchant->restaurant_name)):''	            
				),	
				'ajax_url'=>Yii::app()->createUrl("/apibackend"),
			);	

			$action_name = 'getRate';
			ScriptUtility::registerScript(array(
				"var action_get='$action_name';",				
			),'location_manage');		
			
			$this->render("//tpl/submenu_tpl",array(
			    'model'=>$merchant,
				'template_name'=>"//admin/delivery_management_list",
				'widget'=>'WidgetMerchantSettings',		
				'avatar'=>$avatar,
				'params'=>$params_model,				
			));

		} else $this->render("error");
	}

	public function actiondelete_location_rate()
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
		$model = AR_location_rate::model()->find("merchant_id=:merchant_id AND rate_id=:rate_id",[
			':merchant_id'=>Yii::app()->merchant->merchant_id,
			':rate_id'=>$id
		]);
		if($model){
			$model->delete();
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array(Yii::app()->controller->id.'/delivery_management'));			
		} else $this->render("error");
	}

	public function actionestimate_management()
	{

		$this->pageTitle = t("Time Estimates Management");
		CommonUtility::setMenuActive('.merchant','.merchant_settings');		
		$id = (integer) Yii::app()->merchant->merchant_id;
		$merchant = AR_merchant::model()->findByPk( $id );

		if($merchant){

			$avatar = CMedia::getImage($merchant->logo,$merchant->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));
			
			$table_col = array(
				'id'=>array(
				  'label'=>t("ID"),
				  'width'=>'1%'
				),			
				'service_type'=>array(
				  'label'=>t("Services"),
				  'width'=>'10%'
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
				'estimated_time_min'=>array(
				  'label'=>t("Estimations"),
				  'width'=>'10%'
				),			
				'date_created'=>array(
					'label'=>t("Actions"),
					'width'=>'15%'
				),		  
			  );
			  $columns = array(			
				array('data'=>'id','visible'=>true),
				array('data'=>'service_type'),
				array('data'=>'country_name'),
				array('data'=>'state_name'),		  
				array('data'=>'city_name'),		  
				array('data'=>'area_name'),		  
				array('data'=>'estimated_time_min'),		  
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
					t("Settings")=>array(Yii::app()->controller->id.'/location_management'),		        
					isset($merchant->restaurant_name)?stripslashes(ucwords($merchant->restaurant_name)):''	            
				),	
				'ajax_url'=>Yii::app()->createUrl("/apibackend"),
				'services'=>CommonUtility::ArrayToLabelValue((array)AttributesTools::ListSelectServices()),
			);	

			$action_name = 'getEstimate';
			ScriptUtility::registerScript(array(
				"var action_get='$action_name';",				
			),'location_manage');		
			
			$this->render("//tpl/submenu_tpl",array(
			    'model'=>$merchant,
				'template_name'=>"//admin/times_management_list",
				'widget'=>'WidgetMerchantSettings',		
				'avatar'=>$avatar,
				'params'=>$params_model,				
			));

		} else $this->render("error");
	}	
	

	public function actiondelete_estimate_time()
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
		$model = AR_location_time_estimate::model()->find("merchant_id=:merchant_id AND id=:id",[
			':merchant_id'=>Yii::app()->merchant->merchant_id,
			':id'=>$id
		]);
		if($model){
			$model->delete();
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array(Yii::app()->controller->id.'/estimate_management'));			
		} else $this->render("error");
	}

	public function actionkitchen_settings()
	{
		$this->pageTitle = t("Kitchen Workload");
		CommonUtility::setMenuActive('.merchant','.merchant_settings');		
		$id = (integer) Yii::app()->merchant->merchant_id;
		$merchant = AR_merchant::model()->findByPk( $id );
		
		if($merchant){
						
			$avatar = CMedia::getImage($merchant->logo,$merchant->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));
						
			$model  = AR_kitchen_workload_settings::model()->find("merchant_id=:merchant_id",[
				':merchant_id'=>$id
			]);
			if(!$model){
				$model = new AR_kitchen_workload_settings();
			}
		    	
		    if(isset($_POST['AR_kitchen_workload_settings'])){
		    	if(DEMO_MODE && in_array($id,DEMO_MERCHANT)){
		    		 $this->render('//tpl/error',array(  
				          'error'=>array(
				            'message'=>t("Modification not available in demo")
				          )
				        ));	
				    return false;
		    	}
				
				$model->attributes=$_POST['AR_kitchen_workload_settings'];				
				$model->merchant_id = $id;

				if($model->validate()){			
					if($model->save()){
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
					    $this->refresh();						
					} else Yii::app()->user->setFlash('error',t(Helper_failed_save));				
				} else Yii::app()->user->setFlash('error', t(HELPER_CORRECT_FORM) );				
			}
						
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantSettings;
	           $menu->init();    
			}
		
			$this->render("//tpl/submenu_tpl",array(
			    'model'=>$merchant,
				'template_name'=>"kitchen_settings",
				'widget'=>'WidgetMerchantSettings',		
				'avatar'=>$avatar,
				'params'=>array(  
				   'model'=>$model,	
				   'country_list'=>AttributesTools::CountryList(),		   
				   'links'=>array(	
				     t("Settings")=>array(Yii::app()->controller->id.'/settings'),		        
		             isset($merchant->restaurant_name)?stripslashes(ucwords($merchant->restaurant_name)):''	            
				   ),				   
				 ),
				 'menu'=>$menu		
			));
			
		} else $this->render("error");
	}

	public function actionsuggested_items()
	{
		$this->pageTitle = t("Suggested Items");   
				
		Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl."/assets/js/items_modal.js?time=".time(),CClientScript::POS_END,[
			'type'=>"module",
			'defer'=>'defer'
		]);
		Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl."/assets/js/suggested_items.js?time=".time(),CClientScript::POS_END,[
			'type'=>"module",
			'defer'=>'defer'
		]);
				
		$this->render("suggested_items");
	}

	public function actionfree_item()
	{
		$this->pageTitle=t("Spend-Based Free Item");
		$action_name='free_item_list';
		$delete_link = Yii::app()->CreateUrl(Yii::app()->controller->id."/free_item_delete");
		
		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
		),'action_name');
				
		$this->render('free_item_list',array(
		  'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/free_item_create"),
		  'sort_link'=>''
		));	
	}

	public function actionfree_item_update()
	{
		$this->actionfree_item_create(true);
	}

	public function actionfree_item_create($update=false)
	{
		$this->pageTitle = $update==false? t("Add Free Item") : t("Update Free Item");
		CommonUtility::setMenuActive('.merchant_campaigns','.merchant_free_item');			
			
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;		
		$id='';		
		$selected_item = [];
		
		if($update){
			$id = (integer) Yii::app()->input->get('id');				
			$model = AR_item_free_promo::model()->find('merchant_id=:merchant_id AND promo_id=:promo_id', 
		    array(':merchant_id'=>$merchant_id, ':promo_id'=>$id ));			
		    			
			if(!$model){				
				$this->render("//tpl/error",array(
				 'error'=>array( 'message'=>t(HELPER_RECORD_NOT_FOUND))
				));			
				Yii::app()->end();
			}					
			
			$selected_item = CommonUtility::getDataToDropDown("{{item}}",'item_id','item_name',
			"WHERE item_id=".q($model->free_item_id)."");
			
		} else $model=new AR_item_free_promo;		

		if(isset($_POST['AR_item_free_promo'])){
			$model->attributes=$_POST['AR_item_free_promo'];
			$model->merchant_id = $merchant_id;						
			if(!$update){
				$selected_item = CommonUtility::getDataToDropDown("{{item}}",'item_id','item_name',
				"WHERE item_id=".q($model->free_item_id)."");									
			}
			if($model->validate()){						
				$model_item =  AR_item::model()->find("item_id=:item_id AND merchant_id=:merchant_id",[
					':item_id'=>$model->free_item_id,
					':merchant_id'=>$merchant_id
				]);
				if($model_item){
					$model->item_token = $model_item->item_token;
				}
				
				$model_category = AR_item_relationship_category::model()->find("item_id=:item_id AND merchant_id=:merchant_id",[
					':item_id'=>$model->free_item_id,
					':merchant_id'=>$merchant_id
				]);
				if($model_category){				
					$model->cat_id = $model_category->cat_id;
				}
				$model_size = AR_item_relationship_size::model()->find("item_id=:item_id AND merchant_id=:merchant_id",[
                    ':item_id'=>$model->free_item_id,
					':merchant_id'=>$merchant_id
				]);
				if($model_size){
					$model->item_size_id = $model_size->item_size_id;
				}
				if($model->save()){
					if(!$update){
					   $this->redirect(array(Yii::app()->controller->id.'/free_item'));		
					} else {
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
						$this->refresh();
					}
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			} else {								
				Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString($model->getErrors()));	
			}
		}
		
								
		$model->status = $model->isNewRecord?'publish':$model->status;	
		
		$this->render("//merchant/free_items_create",array(
		    'model'=>$model,			    
		    'language'=>AttributesTools::getLanguage(),		    	
			'status'=>(array)AttributesTools::StatusManagement('post'),	    		    
			'items'=>$selected_item,
		    'links'=>array(
	            t("All Free Item")=>array(Yii::app()->controller->id.'/free_item'),        
                $this->pageTitle,
		    ),	    		    
		));
	}	

	public function actionfree_item_delete()
	{
		$id = (integer) Yii::app()->input->get('id');
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;			
		
		$model = AR_item_free_promo::model()->find("promo_id=:promo_id AND merchant_id=:merchant_id",array(
		  ':promo_id'=>$id,
		  ':merchant_id'=>$merchant_id
		));		

		if($model){
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array(Yii::app()->controller->id.'/free_item'));			
		} else $this->render("error");
	}

	public function actionhubrise()
	{
		
		$this->pageTitle = t("Hubrise Integration");
		
		$id = (integer) Yii::app()->merchant->merchant_id;
		$importcatalog =  Yii::app()->request->getQuery('importcatalog', null);  
		$resynccatalog =  Yii::app()->request->getQuery('resynccatalog', null);  
		$fecthcallback = Yii::app()->request->getQuery('fecthcallback', null); 
		$url = Yii::app()->request->getQuery('url', null); 
		$failed_message = '';

		$model = AR_merchant::model()->findByPk( $id );
		if($model){
			
			$model->scenario='featured';

	
			if($importcatalog){
				if($importcatalog=='failed'){
					Yii::app()->user->setFlash('error',t('There are already active proccessing of catalog, its processing on the background This may take a few minutes depending on the size of the catalog.'));	
				} else {
					Yii::app()->user->setFlash('success',				
   				     t('HubRise catalog is now syncing in the background. This may take a few minutes depending on the size of the catalog. You will see the updated products, categories, and addons shortly.')
			       );		
				}				
			}
			if($resynccatalog){
				if($model->hubrise_catalog_sync==0){
					$failed_message = t("Cannot re-sync no imported catalog yet. click import Full Catalog first.");
					Yii::app()->user->setFlash('error',$failed_message);		
				} else if ($model->hubrise_catalog_sync==1){
					$failed_message = t("Cannot re-sync there is pending import catalog that is processing.");
					Yii::app()->user->setFlash('error',$failed_message);		
				} else {
					if($model->hubrise_catalog_resync==1){
						Yii::app()->user->setFlash('error',"re-syncing is already processing");
					} else {
						Yii::app()->user->setFlash('success','HubRise catalog is now re-syncing in the background.');
					}					
				}
			}

			if($fecthcallback && $url){
				Yii::app()->user->setFlash('success', t("Your active callback url is {url}",[
					'{url}'=>$url
				]));
			}
							
            $avatar = CMedia::getImage($model->logo,$model->path,'@thumbnail',
		    CommonUtility::getPlaceholderPhoto('merchant_logo'));
			
			$options = OptionsTools::find(['hubrise_data'],$id);			
			$hubrise_data = $options['hubrise_data'] ?? null;
			$connetion_data = $hubrise_data ? json_decode($hubrise_data,true): null;			
			
			$options2 = OptionsTools::find([$model->hubrise_access_token]);
			$webhook_url = $options2[$model->hubrise_access_token] ?? null;		
			
			$meta = AR_merchant_meta::getMeta($id,['hubrise_last_import','hubrise_total_category','hubrise_options_list','hubrise_total_product']);			
			$meta_data =  [
				'hubrise_last_import'=>$meta['hubrise_last_import']['meta_value'] ?? '',
				'hubrise_options_list'=>$meta['hubrise_options_list']['meta_value'] ?? '',
				'hubrise_total_category'=>$meta['hubrise_total_category']['meta_value'] ?? '',
				'hubrise_total_product'=>$meta['hubrise_total_product']['meta_value'] ?? '',
			];			
			$params_model = array(		
				'model'=>$model,	
				'links'=>array(
				   t("All Merchant")=>array(Yii::app()->controller->id.'/list'),        
				   $model->isNewRecord?t("Add new"):t("Edit Merchant"),
				   isset($model->restaurant_name)?stripslashes(ucwords($model->restaurant_name)):''
				),	    		   
				'hubrise_connected'=>$model->hubrise_connected==1?true:false,			   
				'connetion_data'=>$connetion_data,
				'webhook_url'=>$webhook_url,
				'meta_data'=>$meta_data,
				'connection_url'=>Yii::app()->createAbsoluteUrl("/merchanthubrise/connect",[
					'id'=>$model->merchant_uuid
				]),
				'discconnection_url'=>Yii::app()->createAbsoluteUrl("/merchanthubrise/disconnect",[
					'id'=>$model->merchant_uuid
				]),
				'callback_url'=>Yii::app()->createAbsoluteUrl("/merchanthubrise/registercallback",[
					'id'=>$model->merchant_uuid
				]),
				'resync_callback_url'=>Yii::app()->createAbsoluteUrl("/merchanthubrise/resynccallback",[
					'id'=>$model->merchant_uuid
				]),
				'removecallback_url'=>Yii::app()->createAbsoluteUrl("/merchanthubrise/removecallback",[
					'id'=>$model->merchant_uuid
				]),
				'inmportcatalog_url'=>Yii::app()->createAbsoluteUrl("/merchanthubrise/importcatalog",[
					'id'=>$model->merchant_uuid
				]),
				'resynccatalog_url'=>Yii::app()->createAbsoluteUrl("/merchanthubrise/resynccatalog",[
					'id'=>$model->merchant_uuid
				]),
				'viewcallback_url'=>Yii::app()->createAbsoluteUrl("/merchanthubrise/fetchcalllback",[
					'id'=>$model->merchant_uuid
				]),
			);	
			
			$menu = array();
			if(Yii::app()->params['isMobile']==TRUE){
			   $menu = new WidgetMerchantSettings;
	           $menu->init();    
			}
						
			$this->render("//tpl/submenu_tpl",array(
			    'model'=>$model,
				'template_name'=>"//vendor/hubrise_integration",
				'widget'=>'WidgetMerchantSettings',		
				'avatar'=>$avatar,
				'params'=>$params_model,
				'menu'=>$menu		
			));
			
		} else $this->render("//tpl/error");
	}	

}
/*end class*/