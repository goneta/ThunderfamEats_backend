<?php
class BuyerController extends CommonController
{
	
	public function beforeAction($action)
	{										
		InlineCSTools::registerStatusCSS();
		InlineCSTools::registerOrder_StatusCSS();
		return true;
	}
	
	public function actioncustomers()
	{		
		$this->pageTitle=t("Customer list");
		$action_name='customer_list';
		$delete_link = Yii::app()->CreateUrl("buyer/customer_delete");
		$datatable_export = true;
		
		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
		  "var datatable_export='$datatable_export';",
		),'action_name');
		
		
		if(Yii::app()->params['isMobile']==TRUE){
			$tpl = 'customer_list_app';
		} else $tpl = 'customer_list';
		
		$this->render( $tpl ,array(
			'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/customer_create")
		));
	}	
	
    public function actioncustomer_create($update=false)
	{
		$this->pageTitle = $update==false? t("Add Customer") : t("Update Customer");
		CommonUtility::setMenuActive('.buyer',".buyer_customers");
		
		$id='';		
		
		if($update){
			$id = (integer) Yii::app()->input->get('id');	
			$model = AR_client::model()->findByPk( $id );				
			if(!$model){				
				$this->render("error");				
				Yii::app()->end();
			}	
			$model->password='';
		} else {			
			$model=new AR_client;				
		}

		if(isset($_POST['AR_client'])){
			$model->attributes=$_POST['AR_client'];
			if($model->validate()){
				if(!empty($model->password)){
				    $model->password = md5($model->password);
				}
				if($model->save()){
					if(!$update){
					   $this->redirect(array('buyer/customers'));		
					} else {
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
						$this->refresh();
					}
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			}
		}

		$upload_path = CMedia::adminFolder();		
		
		$this->render("customer_create",array(
		    'model'=>$model,	
		    'hide_nav'=>false,
		    'links'=>array(
	            t("All Customer")=>array('buyer/customers'),        
                $this->pageTitle,
		    ),	    		    
		    'status_list'=>(array)AttributesTools::StatusManagement('customer'),
		    'upload_path'=>$upload_path,
		));		
	}
	
	public function actioncustomer_update()
	{		
		$this->pageTitle = t("Update Customer");
		CommonUtility::setMenuActive('.buyer',".buyer_customers");
		
		$id='';		
		$custom_fields = [];
		
		$id = (integer) Yii::app()->input->get('id');	
		$model = AR_client::model()->findByPk( $id );				
		if(!$model){				
			$this->render("error");				
			Yii::app()->end();
		}	

		$custom_fields = AttributesTools::getCustomFieldsValue($id);
		
		if(isset($_POST['AR_client'])){
			$model->attributes=$_POST['AR_client'];		
			
			try {

				// CUSTOM FIELDS
				$field_data = [];				
				$custom_fields = Yii::app()->input->post('custom_fields');								
				$field_data = AttributesTools::getCustomFields('customer','key2'); 							
				$model->custom_fields = $custom_fields;			
				CommonUtility::validateCustomFields($field_data,$custom_fields);

				if($model->validate()){												
					if(isset($_POST['avatar'])){
						if(!empty($_POST['avatar'])){
							$model->avatar = isset($_POST['avatar'])?$_POST['avatar']:'';
							$model->path = isset($_POST['path'])?$_POST['path']:'';						
						} else $model->avatar = '';
					} else $model->avatar = '';
									
					if(!empty($model->npassword)){					
						$model->password = md5($model->npassword);				    
					}
									
					if($model->save()){
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
						$this->refresh();
					} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
				} else {
					Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString($model->getErrors(),"<br/>") );
				}				
			} catch (Exception $e) {				
				Yii::app()->user->setFlash('error', $e->getMessage() );
			}
					
		}
				
		$avatar = CMedia::getImage($model->avatar,$model->path,Yii::app()->params->size_image_thumbnail,
				CommonUtility::getPlaceholderPhoto('customer'));
				
		$upload_path = CMedia::adminFolder();		
									
		$this->render("//tpl/submenu_tpl",array(
		    'model'=>$model,		    
			'template_name'=>"customer_create",
			'widget'=>'WidgetCustomer',		
			'avatar'=>$avatar,
			'params'=>array(  
			   'model'=>$model,
			   'upload_path'=>$upload_path,
			   'status_list'=>(array)AttributesTools::StatusManagement('customer'),	
			   'hide_nav'=>true,
			   'links'=>array(
		            t("All Customer")=>array('buyer/customers'),  
	                $this->pageTitle,
			    ),
				'custom_fields'=>$custom_fields
			 )
		));
	}	
	
    public function actioncustomer_delete()
	{
		$id = (integer) Yii::app()->input->get('id');			
		$model = AR_client::model()->findByPk( $id );
		if($model){
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array('buyer/customers'));			
		} else $this->render("error");
	}	
	
	public function actionsubscribers_list()
	{		
		$this->pageTitle=t("Subscriber List");
		$action_name='subscribers_list';
		$delete_link = Yii::app()->CreateUrl("buyer/subscriber_delete");
		
		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
		),'action_name');
				
		if(Yii::app()->params['isMobile']==TRUE){
			$tpl = 'subscribers_list_app';
		} else $tpl = 'subscribers_list';
		
		$this->render( $tpl ,array(
			'link'=>''
		));
	}	
		
	public function actionaddress()
	{
		$this->pageTitle=t("Address list");
		CommonUtility::setMenuActive('.buyer',".buyer_customers");
				
		$action_name='address_list';
		$delete_link = Yii::app()->CreateUrl("buyer/address_delete");			
		
		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
		),'action_name');
		
		
        $client_id = (integer) Yii::app()->input->get('id');	
		$model = AR_client::model()->findByPk( $client_id );				
		if(!$model){				
			$this->render("error");				
			Yii::app()->end();
		}	
		
		$avatar = CMedia::getImage($model->avatar,$model->path,Yii::app()->params->size_image_thumbnail,
				CommonUtility::getPlaceholderPhoto('customer'));
				
		$this->render("//tpl/submenu_tpl",array(
		    'model'=>$model,
			'template_name'=>'address_list',
			'widget'=>'WidgetCustomer',		
			'avatar'=>$avatar,
			'params'=>array(  
			   'model'=>$model,			   
			   'country_list'=>AttributesTools::CountryList(),
			   'hide_nav'=>false,
			   'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/address_create",array(
			    'create'=>1,
	            'id'=>$model->client_id
			   )),
			   'links'=>array(
		            t("All Customer")=>array('buyer/customers'),  
	                $this->pageTitle,
			    ),
			   'links2'=>array(
		            t("All Address")=>array('buyer/address','id'=>$model->client_id),
	                $this->pageTitle,
			    ), 
			 )
		));
	}
	
	public function actionaddress_create($update=false)
	{		
		$this->pageTitle = $update==false? t("Add Address") :  t("Update Address");
		CommonUtility::setMenuActive('.buyer',".buyer_customers");		
		CommonUtility::setSubMenuActive('.customer-menu','.customer-address');		
						
        $client_id = (integer) Yii::app()->input->get('id');	
		$model = AR_client::model()->findByPk( $client_id );				
		if(!$model){				
			$this->render("error");				
			Yii::app()->end();
		}	
				
       $avatar = CMedia::getImage($model->avatar,$model->path,'@thumbnail',
		CommonUtility::getPlaceholderPhoto('customer'));
				
		$page_title = t("Add Address");
		$model = new AR_client_address;

		$ad_id = '';
		
		if($update){
			$ad_id = (integer) Yii::app()->input->get('ad_id');	
			$model = AR_client_address::model()->findByPk( $ad_id );	
			if(!$model){				
				 $this->render('//tpl/error',array(
				 'error'=>array(
				   'message'=>t(Helper_not_found)
				 )
				));
				return ;
			}	
		}

		if(isset($_POST['AR_client_address'])){
			$model->attributes=$_POST['AR_client_address'];
			if($model->validate()){								
				$model->client_id = $client_id;				
				if($model->save()){					
					if(!$update){
					   $this->redirect(array('buyer/address','id'=>$client_id));		
					} else {
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
						$this->refresh();
					}					
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString($model->getErrors(),'<br/>') );	
			//} else Yii::app()->user->setFlash('error',t(HELPER_CORRECT_FORM));
		} 

		$setttings = Yii::app()->params['settings'];
		$home_search_mode = isset($setttings['home_search_mode'])?$setttings['home_search_mode']:'address';					
		$home_search_mode = !empty($home_search_mode)?$home_search_mode:'address';			

		$tpl = $home_search_mode=='location'?'address_create_location':'address_create';

		if($home_search_mode){
			$delivery_option = CCheckout::deliveryOption();
			$address_label = CCheckout::addressLabel();						
			$delivery_option_first_value = reset($delivery_option);			
			$address_label_first_value = reset($address_label);						
			$delivery_option_first_value = 'Hand it to me';		

			$delivery_option = CommonUtility::ArrayToLabelValue($delivery_option);			
			$address_label = CommonUtility::ArrayToLabelValue($address_label);			
			$delivery_option = json_encode($delivery_option);
			$address_label = json_encode($address_label);	
			ScriptUtility::registerScript(array(
				"var delivery_option_first_value='".CJavaScript::quote($delivery_option_first_value)."';",	      
				"var address_label_first_value='".CJavaScript::quote($address_label_first_value)."';",	      
				"var address_id='".CJavaScript::quote($ad_id)."';",	      
				"var client_id='".CJavaScript::quote($client_id)."';",	      
				"var delivery_option='".CJavaScript::quote($delivery_option)."';",	      
				"var address_label='".CJavaScript::quote($address_label)."';",	      
			),'location_script');					
		}		
								
		$this->render("submenu_tpl",array(
		    'model'=>$model,
			//'template_name'=>'address_create',
			'template_name'=>$tpl,
			'widget'=>'WidgetCustomer',		
			'avatar'=>$avatar,
			'params'=>array(  
			   'model'=>$model,			   
			   /*'country_list'=>AttributesTools::CountryList(),
			   'location_nickname'=>AttributesTools::locationNickName(),*/
			   'delivery_option'=>CCheckout::deliveryOption(),
			   'address_label'=>CCheckout::addressLabel(),
			   'hide_nav'=>false,
			   'links'=>array(
		            t("All Customer")=>array('buyer/customers'),  
		            t("All Address")=>array('buyer/address','id'=>$client_id),
	                $this->pageTitle,
			    ),
			   'links2'=>array(		            
	                $this->pageTitle,
			    ), 
			 )
		));
	}	
	
	public function actionaddress_update()
	{		
		$this->actionaddress_create(true);
	}
	
	public function actionaddress_delete()
	{
		$id = (integer) Yii::app()->input->get('id');					
		$model = AR_client_address::model()->findByPk( $id );
		if($model){			
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array('buyer/address','id'=>$model->client_id));			
		} else $this->render("error");
	}
	
	public function actionorder_history()
	{
		$this->pageTitle=t("Order list");
		CommonUtility::setMenuActive('.buyer',".buyer_customers");
				
		$action_name='customer_order_list';
		$delete_link = Yii::app()->CreateUrl("buyer/order_delete");			
		
		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
		),'action_name');
		
		
        $client_id = (integer) Yii::app()->input->get('id');	
		$model = AR_client::model()->findByPk( $client_id );				
		if(!$model){				
			$this->render('//tpl/error',array(
			 'error'=>array(
			   'message'=>t(Helper_not_found)
			 )
			));
			return ;
		}	
				
		$avatar = CMedia::getImage($model->avatar,$model->path,Yii::app()->params->size_image_thumbnail,
				CommonUtility::getPlaceholderPhoto('customer'));
				
				
		$table_col = array(
		  'merchant_id'=>array(
		    'label'=>"",
		    'width'=>'10%'
		  ),		  
		  'client_id'=>array(
		    'label'=>t("Order Information"),
		    'width'=>'25%'
		  ),
		  'order_id'=>array(
		    'label'=>t("Order ID"),
		    'width'=>'12%'
		  ),
		  'restaurant_name'=>array(
		    'label'=>t("Merchant"),
		    'width'=>'15%'
		  ),
		  'order_uuid'=>array(
		    'label'=>t("Actions"),
		    'width'=>'10%'
		  ),
		  'view'=>array(
		    'label'=>t("view"),
		    'width'=>'10%'
		  ),
		);
		$columns = array(
		  array('data'=>'merchant_id','orderable'=>false),
		  array('data'=>'client_id','orderable'=>false),		  
		  array('data'=>'order_id'),
		  array('data'=>'restaurant_name','orderable'=>false),
		  array('data'=>null,'orderable'=>false,
		     'defaultContent'=>'
		     <div class="btn-group btn-group-actions" role="group">
			    <a class="ref_view_order normal btn btn-light tool_tips"><i class="zmdi zmdi-eye"></i></a>
			    <a class="ref_pdf_order normal btn btn-light tool_tips"><i class="zmdi zmdi-download"></i></a>
			 </div>
		     '
		  ),
		  array('data'=>null,'orderable'=>false,'visible'=>false),
		);						
		
		$this->render("//tpl/submenu_tpl",array(
		    'model'=>$model,
			'template_name'=>'customer_order_list',
			'widget'=>'WidgetCustomer',		
			'avatar'=>$avatar,
			'params'=>array(  
			   'model'=>$model,				   
			   'hide_nav'=>false,
			   'links'=>array(
		            t("All Customer")=>array('buyer/customers'),  
	                $this->pageTitle,
			    ),
			   'links2'=>array(
		            t("All Customer")=>array('buyer/address','id'=>$model->client_id),
	                $this->pageTitle,
			    ), 
			   'table_col'=>$table_col,
			   'columns'=>$columns,
			   'order_col'=>2,
	           'sortby'=>'desc',
	           'transaction_type'=>array(),
	           'client_id'=>$client_id
			 )
		));
	}

	public function actionbooking_historyOLD()
	{
		$this->pageTitle=t("Booking list");
		CommonUtility::setMenuActive('.buyer',".buyer_customers");
				
		$action_name='customer_booking';
		$delete_link = Yii::app()->CreateUrl("buyer/booking_delete");			
		
		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
		),'action_name');
		
		
        $client_id = (integer) Yii::app()->input->get('id');	
		$model = AR_client::model()->findByPk( $client_id );				
		if(!$model){				
			$this->render("error");				
			Yii::app()->end();
		}	
		
		$avatar = CMedia::getImage($model->avatar,$model->path,Yii::app()->params->size_image_thumbnail,
				CommonUtility::getPlaceholderPhoto('customer'));
				
		$this->render("submenu_tpl",array(
		    'model'=>$model,
			'template_name'=>'customer_booking',
			'widget'=>'WidgetCustomer',		
			'avatar'=>$avatar,
			'params'=>array(  
			   'model'=>$model,			   
			   'country_list'=>AttributesTools::CountryList(),
			   'hide_nav'=>false,
			   'links'=>array(
		            t("All Customer")=>array('buyer/customers'),  
	                $this->pageTitle,
			    ),
			   'links2'=>array(
		            t("All Address")=>array('buyer/address','id'=>$model->client_id),
	                $this->pageTitle,
			    ), 
			 )
		));
	}
		
	public function actionbooking_delete()
	{
		$id = (integer) Yii::app()->input->get('id');					
		$model = AR_booking::model()->findByPk( $id );
		if($model){			
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array('buyer/booking_history','id'=>$model->client_id));			
		} else $this->render("error");
	}
	
	public function actionbooking_update($update=true)
	{
		$this->pageTitle = $update==false? t("Add Booking") : t("Update Booking");
		CommonUtility::setMenuActive('.buyer',".buyer_customers");
		CommonUtility::setSubMenuActive('.customer-menu','.customer-booking-history');		
		
		$client_id = (integer) Yii::app()->input->get('id');	
		$model = AR_client::model()->findByPk( $client_id );				
		if(!$model){				
			$this->render("error");				
			Yii::app()->end();
		}	
		
		
        $avatar = CMedia::getImage($model->avatar,$model->path,'@thumbnail',
		CommonUtility::getPlaceholderPhoto('item'));
		
		if($update){
			$bk_id = (integer) Yii::app()->input->get('bk_id');	
			$model = AR_booking::model()->findByPk( $bk_id );	
			if(!$model){				
				$this->render("error");				
				Yii::app()->end();
			}	
		}
		
		if(isset($_POST['AR_booking'])){
			$model->attributes=$_POST['AR_booking'];
			if($model->validate()){					
				if($model->save()){					
					if(!$update){
					   $this->redirect(array('buyer/booking_history','id'=>$client_id));		
					} else {
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
						$this->refresh();
					}					
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			}
		} 
		
		$this->render("submenu_tpl",array(
		    'model'=>$model,
			'template_name'=>'booking_create',
			'widget'=>'WidgetCustomer',		
			'avatar'=>$avatar,
			'params'=>array(  
			   'model'=>$model,
			   'status_list'=>(array)AttributesTools::StatusManagement('booking'),
			   'hide_nav'=>false,
			   'links'=>array(
		            t("All Customer")=>array('buyer/customers'),  
	                $this->pageTitle,
			    ),
			   'links2'=>array(
		            t("All Booking")=>array('buyer/booking_history','id'=>$client_id),
	                $this->pageTitle,
			    ), 
			 )
		));
	}
	
	public function actionreview_list()
	{		
		$this->pageTitle=t("Reviews");
		$action_name='review_list';
		$delete_link = Yii::app()->CreateUrl("buyer/review_delete");
		
		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
		),'action_name');
				
		
		if(Yii::app()->params['isMobile']==TRUE){
			$tpl = 'review_list_app';
		} else $tpl = 'review_list';
		
		$this->render( $tpl ,array(
			'link'=>''
		));
	}	
	
	public function actionreview_delete()
	{
		$id = (integer) Yii::app()->input->get('id');					
		$model = AR_review::model()->findByPk( $id );
		if($model){			
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array('buyer/review_list'));			
		} else $this->render("error");
	}
	
	public function actionreview_update()
	{
	
		$this->pageTitle =  t("Update Review");
		CommonUtility::setMenuActive('.buyer',".buyer_review_list");
		
		$id='';	$role_access = array();
		
		$id = (integer) Yii::app()->input->get('id');				
		$model = AR_review::model()->findByPk( $id );				
		if(!$model){				
			$this->render("/admin/error",array(
			 'error'=>array(
			   'message'=>t(HELPER_RECORD_NOT_FOUND)
			 )
			));				
			Yii::app()->end();
		}				
	
		if(isset($_POST['AR_review'])){
		    $model->attributes=$_POST['AR_review'];			    	
		    if($model->validate()){						    				    	
				if($model->save()){						
					Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
					$this->refresh();
				} else {					
					Yii::app()->user->setFlash('error',CommonUtility::t(Helper_failed_update));
				}				
			}
		}
				
		$this->render("review_create",array(
		  'model'=>$model,	
		  'status_list'=>(array)AttributesTools::StatusManagement('post'),
		  'links'=>array(
	            t("All Review")=>array('buyer/review_list'),  
                $this->pageTitle,
		    ),	
	    ));
	}

	public function actionpoints_history()
	{
		$this->pageTitle=t("Points history");
		CommonUtility::setMenuActive('.buyer',".buyer_customers");

		$client_id = (integer) Yii::app()->input->get('id');	
		$model = AR_client::model()->findByPk( $client_id );				
		if(!$model){				
			$this->render('//tpl/error',array(
			 'error'=>array(
			   'message'=>t(Helper_not_found)
			 )
			));
			return ;
		}	
				
		$avatar = CMedia::getImage($model->avatar,$model->path,Yii::app()->params->size_image_thumbnail,CommonUtility::getPlaceholderPhoto('customer'));

		$table_col = array(
			'transaction_id'=>array(
				'label'=>t("#"),
				'width'=>'5%'
			  ),
			'transaction_date'=>array(
			  'label'=>t("Date"),
			  'width'=>'20%'
			),
			'transaction_description'=>array(
			  'label'=>t("Transaction"),
			  'width'=>'35%'
			),
			'transaction_amount'=>array(
			  'label'=>t("Debit/Credit"),
			  'width'=>'20%'
			),
			'running_balance'=>array(
			   'label'=>t("Running Balance"),
			   'width'=>'20%'
			)			
		  );
		  $columns = array(
			array('data'=>'transaction_id','visible'=>false),
			array('data'=>'transaction_date'),
			array('data'=>'transaction_description'),
			array('data'=>'transaction_amount','className'=> "text-right"),			
			array('data'=>'running_balance','className'=> "text-right"),
		  );
		
		try {
			$card_id = CWallet::createCard( Yii::app()->params->account_type['customer_points'],$client_id);
			
			$customer = CWallet::getCustomer($card_id);		  		  
		    $transaction_type = AttributesTools::transactionTypeList(true);		  

			$this->render("//tpl/submenu_tpl",array(
				'model'=>$model,
				'template_name'=>'//points/transaction_logs',
				'widget'=>'WidgetCustomer',		
				'avatar'=>$avatar,
				'params'=>array(  
				   'model'=>$model,				   
				   'hide_nav'=>false,
				   'links'=>array(
						t("All Customer")=>array('buyer/customers'),  
						$this->pageTitle,
					),
				   'links2'=>array(
						t("All Customer")=>array('buyer/address','id'=>$model->client_id),
						$this->pageTitle,
					), 
				   'table_col'=>$table_col,
				   'columns'=>$columns,
				   'order_col'=>0,
				   'sortby'=>'desc',
				   'transaction_type'=>array(),
				   'client_id'=>$client_id,
				   'card_id'=>$card_id,
				   'customer'=>$customer,			
			       'transaction_type'=>$transaction_type,
				   'available_label'=>t("Available Points")
				 )
			));
		} catch (Exception $e) {             			
			$this->render('//tpl/error',array(
				'error'=>array(
				  'message'=>$e->getMessage()
				)
			));
        }				
	}

	public function actionwallet_history()
	{
		$this->pageTitle=t("Wallet history");
		CommonUtility::setMenuActive('.buyer',".buyer_customers");

		$client_id = (integer) Yii::app()->input->get('id');	
		$model = AR_client::model()->findByPk( $client_id );				
		if(!$model){				
			$this->render('//tpl/error',array(
			 'error'=>array(
			   'message'=>t(Helper_not_found)
			 )
			));
			return ;
		}	
				
		$avatar = CMedia::getImage($model->avatar,$model->path,Yii::app()->params->size_image_thumbnail,CommonUtility::getPlaceholderPhoto('customer'));

		$table_col = array(
			'transaction_id'=>array(
				'label'=>t("#"),
				'width'=>'5%'
			  ),
			'transaction_date'=>array(
			  'label'=>t("Date"),
			  'width'=>'20%'
			),
			'transaction_description'=>array(
			  'label'=>t("Transaction"),
			  'width'=>'35%'
			),
			'transaction_amount'=>array(
			  'label'=>t("Debit/Credit"),
			  'width'=>'20%'
			),
			'running_balance'=>array(
			   'label'=>t("Running Balance"),
			   'width'=>'20%'
			)			
		  );
		  $columns = array(
			array('data'=>'transaction_id','visible'=>false),
			array('data'=>'transaction_date'),
			array('data'=>'transaction_description'),
			array('data'=>'transaction_amount','className'=> "text-right"),			
			array('data'=>'running_balance','className'=> "text-right"),
		  );
		
		try {
			$card_id = CWallet::createCard( Yii::app()->params->account_type['digital_wallet'],$client_id);
			
			$customer = CWallet::getCustomer($card_id);		  		  
		    $transaction_type = AttributesTools::transactionTypeList(true);		  

			$this->render("//tpl/submenu_tpl",array(
				'model'=>$model,
				'template_name'=>'//points/transaction_logs',
				'widget'=>'WidgetCustomer',		
				'avatar'=>$avatar,
				'params'=>array(  
				   'model'=>$model,				   
				   'hide_nav'=>false,
				   'links'=>array(
						t("All Customer")=>array('buyer/customers'),  
						$this->pageTitle,
					),
				   'links2'=>array(
						t("All Customer")=>array('buyer/address','id'=>$model->client_id),
						$this->pageTitle,
					), 
				   'table_col'=>$table_col,
				   'columns'=>$columns,
				   'order_col'=>0,
				   'sortby'=>'desc',
				   'transaction_type'=>array(),
				   'client_id'=>$client_id,
				   'card_id'=>$card_id,
				   'customer'=>$customer,			
			       'transaction_type'=>$transaction_type,	
				   'available_label'=>t("Available Balance"),
				   'return_format'=>"money_format",
				   'action_name'=>'walletadjustment'
				 )
			));
		} catch (Exception $e) {             			
			$this->render('//tpl/error',array(
				'error'=>array(
				  'message'=>$e->getMessage()
				)
			));
        }				
	}	

	public function actionbooking_history()
	{
		$this->pageTitle=t("Booking history");
		CommonUtility::setMenuActive('.buyer',".buyer_customers");

		$client_id = (integer) Yii::app()->input->get('id');	
		$model = AR_client::model()->findByPk( $client_id );				
		if(!$model){				
			$this->render('//tpl/error',array(
			 'error'=>array(
			   'message'=>t(Helper_not_found)
			 )
			));
			return ;
		}	
				
		$avatar = CMedia::getImage($model->avatar,$model->path,Yii::app()->params->size_image_thumbnail,CommonUtility::getPlaceholderPhoto('customer'));

		$table_col = array(            
			'reservation_id'=>array(
			  'label'=>t("ID"),
			  'width'=>'8%'
			),
			'client_id'=>array(
			  'label'=>t("Name"),
			  'width'=>'20%'
			),
			'guest_number'=>array(
				'label'=>t("Guest"),
				'width'=>'15%'
			),            
			'table_id'=>array(
			  'label'=>t("Table"),
			  'width'=>'15%'
			),
			'reservation_date'=>array(
			  'label'=>t("Date/Time"),
			  'width'=>'15%'
			),            			
			'special_request'=>array(
				'label'=>t("Request"),
				'width'=>'10%'
			),            
			'status '=>array(
				'label'=>t("Status"),
				'width'=>'10%'
			  ),
		  );
		$columns = array(            
			array('data'=>'reservation_id'),
			array('data'=>'client_id','visible'=>false),
			array('data'=>'guest_number'),            
			array('data'=>'table_id'),
			array('data'=>'reservation_date'),            			
			array('data'=>'special_request'),            
			array('data'=>'status'),
		);	

		try {				
			$summary = CBooking::customerSummary($client_id,0,date("Y-m-d"));
		} catch (Exception $e) {
			$summary  = [];
		}		

		$this->render("//tpl/submenu_tpl",array(
		    'model'=>$model,
			'template_name'=>'customer-booking-history',
			'widget'=>'WidgetCustomer',		
			'avatar'=>$avatar,
			'params'=>array(  
			   'model'=>$model,				   
			   'hide_nav'=>false,
			   'links'=>array(
		            t("All Customer")=>array('buyer/customers'),  
	                $this->pageTitle,
			    ),
			   'links2'=>array(
		            t("All Customer")=>array('buyer/address','id'=>$model->client_id),
	                $this->pageTitle,
			    ), 
			   'table_col'=>$table_col,
			   'columns'=>$columns,
			   'order_col'=>0,
	           'sortby'=>'desc',
	           'transaction_type'=>array(),
	           'client_id'=>$client_id,
			   'summary'=>$summary
			 )
		));
		
	}

	public function actionemail_subscriber()
	{
		$this->pageTitle=t("Email Subscribers");		

		$table_col = array(
			'id'=>array(
				'label'=>t("#"),
				'width'=>'5%'
			  ),
			'email_address'=>array(
			  'label'=>t("Email address"),
			  'width'=>'30%'
			),			
			'ip_address'=>array(
			   'label'=>t("Actions"),
			   'width'=>'5%'
			)			
		);

		$columns = array(
			array('data'=>'id'),
			array('data'=>'email_address'),				
			array('data'=>null,'orderable'=>false,
				'defaultContent'=>'
				<div class="btn-group btn-group-actions" role="group">				  
					<a class="ref_delete normal btn btn-light tool_tips"><i class="zmdi zmdi-delete"></i></a>
				</div>
				'
			),	  
		);				
						
		$this->render('email_subscriber',array(				
			'ajax_url'=>Yii::app()->createUrl("/api"),
			'table_col'=>$table_col,
			'columns'=>$columns,
			'order_col'=>0,
			'sortby'=>'desc',			
		));
	}	

	public function actionsubscriber_delete()
	{
		$id = (integer) Yii::app()->input->get('id');			
		$model = AR_subscriber::model()->find("id=:id AND merchant_id=:merchant_id AND subcsribe_type=:subcsribe_type",[
			':id'=>$id,
			':merchant_id'=>0,
			':subcsribe_type'=>'website'
		]);
		if($model){
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array('buyer/email_subscriber'));			
		} else $this->render("error");
	}	

	public function actionpush_test()
	{
		$this->pageTitle=t("Push Debug / Test Push");
		CommonUtility::setMenuActive('.buyer',".buyer_customers");

		$client_id = (integer) Yii::app()->input->get('id');	
		$model = AR_client::model()->findByPk( $client_id );				
		if(!$model){				
			$this->render('//tpl/error',array(
			 'error'=>array(
			   'message'=>t(Helper_not_found)
			 )
			));
			return ;
		}	

		$avatar = CMedia::getImage($model->avatar,$model->path,Yii::app()->params->size_image_thumbnail,CommonUtility::getPlaceholderPhoto('customer'));

		$table_col = array(            
			'device_id'=>array(
			  'label'=>t("ID"),
			  'width'=>'5%'
			),
			'platform'=>array(
			  'label'=>t("Platform"),
			  'width'=>'15%'
			),
			'device_token'=>array(
				'label'=>t("Token"),
				'width'=>'20%'
			),            						
			'enabled'=>array(
				'label'=>t("Actions"),
				'width'=>'10%'
			  ),
		  );
		$columns = array(            
			array('data'=>'device_id'),
			array('data'=>'platform',),
			array('data'=>'device_token'),            
			array('data'=>'enabled'),			
		);	

		$this->render("//tpl/submenu_tpl",array(
		    'model'=>$model,
			'template_name'=>'push_test',
			'widget'=>'WidgetCustomer',		
			'avatar'=>$avatar,
			'params'=>array(  
			   'model'=>$model,				   
			   'hide_nav'=>false,
			   'links'=>array(
		            t("All Customer")=>array('buyer/customers'),  
	                $this->pageTitle,
			    ),
			   'links2'=>array(
		            t("All Customer")=>array('buyer/address','id'=>$model->client_id),
	                $this->pageTitle,
			    ), 
			   'table_col'=>$table_col,
			   'columns'=>$columns,
			   'order_col'=>0,
	           'sortby'=>'desc',
	           'transaction_type'=>array(),
	           'client_id'=>$client_id,			   
			 )
		));

	}

	public function actionsend_push()
	{
		$this->pageTitle=t("Send Push");				
		CommonUtility::setMenuActive('.buyer',".buyer_customers");
		CommonUtility::setSubMenuActive('.customer-menu','.customer-push-test');		

		$client_id = (integer) Yii::app()->input->get('id');	
		$model = AR_client::model()->findByPk( $client_id );				
		if(!$model){				
			$this->render('//tpl/error',array(
			 'error'=>array(
			   'message'=>t(Helper_not_found)
			 )
			));
			return ;
		}	

		$device_id = Yii::app()->request->getQuery('device_id', null);  
		if(!$device_id){
			$this->render('//tpl/error',array(
				'error'=>array(
				  'message'=>t(Helper_not_found)
				)
			));
			return;
		}

		$avatar = CMedia::getImage($model->avatar,$model->path,Yii::app()->params->size_image_thumbnail,CommonUtility::getPlaceholderPhoto('customer'));

		$device_model = AR_device::model()->findByPk($device_id);				
		if(!$device_model){
			$this->render('//tpl/error',array(
				'error'=>array(
				  'message'=>t(Helper_not_found)
				)
			));
			return;
		}
		
		$device_model->scenario = 'test_push';
		if(isset($_POST['AR_device'])){			
			$device_model->attributes=$_POST['AR_device'];			
			if($device_model->validate()){
				try {
					if($device_model->platform=="pwa"){
						$message = [
							'message' => [
								'token' => $device_model->device_token,
								'notification' => [
									'title' => $device_model->title,
									'body' => $device_model->message,
								],
								'webpush' => [
									'fcm_options' => [
										'link' => 'https://your-pwa-url.com/orders'
									]
								]
							]
						];
						$json_path = AttributesTools::getPushJsonFile();
						CNotifications::SendPwaPush($message,$json_path);
						Yii::app()->user->setFlash('success', t("Succesful") );
						$this->refresh();
					} else {
						$customer_model = ACustomer::get($device_model->user_id);						
						$push = new AR_push;
                        $push->scenario = 'send';
                        $push->merchant_id = 0;
                        $push->push_type = 'broadcast';
                        $push->provider  = 'firebase';
                        $push->channel_device_id = $customer_model->client_uuid;
                        $push->platform = $device_model->platform;
                        $push->title = $device_model->title;
                        $push->body = $device_model->message;
                        $push->settings = Yii::app()->params['push'];
                        $push->save();      
						Yii::app()->user->setFlash('success', t("Succesful") );
						$this->refresh();
					}
				} catch (Exception $e) {								
					Yii::app()->user->setFlash('error', $e->getMessage() );
				}				
			} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString($device_model->getErrors(),'<br/>') );	
		}

		$this->render("//tpl/submenu_tpl",array(
		    'model'=>$model,
			'template_name'=>'send_push',
			'widget'=>'WidgetCustomer',		
			'avatar'=>$avatar,
			'params'=>array(  
			   'model'=>$device_model,				   
			   'hide_nav'=>false,
			   'links'=>array(
		            t("All Customer")=>array('buyer/customers'),  
	                $this->pageTitle,
			    ),
			   'links2'=>array(
		            t("All Customer")=>array('buyer/address','id'=>$model->client_id),
	                $this->pageTitle,
			    ), 			   
			 )
		));		
	}

	public function actionpayment_settings()
	{
		$this->pageTitle = t("Blocked Payments");
		CommonUtility::setMenuActive('.buyer',".buyer_customers");
		CommonUtility::setSubMenuActive('.customer-menu','.customer-payment-settings');	

		$id = (integer) Yii::app()->input->get('id');	
		$model = AR_client::model()->findByPk( $id );				
		if(!$model){				
			$this->render("error");				
			Yii::app()->end();
		}	

		$model->scenario = 'block_payment_method';
		if(isset($_POST['AR_client'])){
			$model->attributes=$_POST['AR_client'];		
			if($model->validate()){						    				    				    	
				if($model->save()){						    					    	
					Yii::app()->user->setFlash('success', t(Helper_success) );
					$this->refresh();						
				} else {					
					Yii::app()->user->setFlash('error',CommonUtility::t(Helper_failed_update));
				}				
			} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString($model->getErrors(),"<br/>") );
		}

		if($payment_gateway = ACustomer::getMeta($id,'block_payment_method')){
			$model->block_payment_method = $payment_gateway;			
		}

		$avatar = CMedia::getImage($model->avatar,$model->path,Yii::app()->params->size_image_thumbnail,CommonUtility::getPlaceholderPhoto('customer'));
		
		$this->render("//tpl/submenu_tpl",array(
		    'model'=>$model,
			'template_name'=>'customer_payment_settings',
			'widget'=>'WidgetCustomer',		
			'avatar'=>$avatar,
			'params'=>array(  
			   'model'=>$model,				   
			   'provider'=>AttributesTools::PaymentProvider(),
			   'links'=>array(
		            t("All Customer")=>array('buyer/customers'),  
	                $this->pageTitle,
			    ),
			   'links2'=>array(
		            t("All Customer")=>array('buyer/address','id'=>$model->client_id),
	                $this->pageTitle,
			    ), 			   
			 )
		));		
	}

}
/*end class*/