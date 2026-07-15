<?php
class CustomerController extends Commonmerchant
{
		
	public function beforeAction($action)
	{				
		
		InlineCSTools::registerStatusCSS();
		InlineCSTools::registerOrder_StatusCSS();
			
		return true;
	}
		
	public function actionIndex()
	{	
		$this->redirect(array(Yii::app()->controller->id.'/reviews'));		
	}		
	
	public function actionsubscriber()
	{
		$this->pageTitle=t("Subscriber List");
		$action_name='subscriber_list';
		$delete_link = Yii::app()->CreateUrl(Yii::app()->controller->id."/subscriber_delete");
		
		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
		),'action_name');
		
		
		if(Yii::app()->params['isMobile']==TRUE){
			$tpl = '//tpl/lazy_list';
		} else $tpl = '//tpl/list';

		$this->render($tpl);	
	}
	
	public function actionreviews()
	{
		$this->pageTitle=t("Customer reviews");
		$action_name='customer_review';
		$delete_link = Yii::app()->CreateUrl(Yii::app()->controller->id."/customerreview_delete");
		
		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
		),'action_name');

		$tpl = '//merchant/review_list';				
		
		$this->render($tpl);	
	}
	
	public function actionreviews_update()
	{
		$this->pageTitle = t("Update Review");
		CommonUtility::setMenuActive('.buyer','.customer_reviews');
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;
		$id = (integer) Yii::app()->input->get('id');			
				
		$can_edit_reviews = isset(Yii::app()->params['settings']['merchant_can_edit_reviews'])?Yii::app()->params['settings']['merchant_can_edit_reviews']:'';
		if($can_edit_reviews!=1){
			$this->render('//tpl/error',array(
			 'error'=>array(
			   'message'=>t("Your not allowed to access this page")
			 )
			));
			Yii::app()->end();
		}
		
		
		$model = AR_review::model()->find('merchant_id=:merchant_id AND id=:id', 
		array(':merchant_id'=>$merchant_id, ':id'=>$id ));
		
		if(!$model){				
			$this->render("error");				
			Yii::app()->end();
		}		 
		
		if(isset($_POST['AR_review'])){
			$model->attributes=$_POST['AR_review'];
			if($model->validate()){					
				if($model->save()){
					Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
					$this->refresh();
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
				
			} else Yii::app()->user->setFlash('error',t(HELPER_CORRECT_FORM));
		}
		
		$this->render("//merchant/reviews_create",array(
		  'model'=>$model,
		  'status'=>(array)AttributesTools::StatusManagement('post'),		  
		  'links'=>array(
	            t("All Review")=>array(Yii::app()->controller->id.'/reviews'),        
                $this->pageTitle,
		    ),	 
		));		
	}
	
	public function actionreview_reply()
	{
	    $this->pageTitle = t("Update Review");
		CommonUtility::setMenuActive('.buyer','.customer_reviews');
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;
		
		$id = (integer) Yii::app()->input->get('id');	
		
		$find = AR_review::model()->find('merchant_id=:merchant_id AND id=:id', 
		array(':merchant_id'=>$merchant_id, ':id'=>$id ));
		
		if(!$find){				
			$this->render("error");				
			Yii::app()->end();
		}		 
		
		$model = new AR_review;
		$model->scenario = 'reply';
		
		if(isset($_POST['AR_review'])){
			$model->attributes=$_POST['AR_review'];
			if($model->validate()){
								
				$merchant = AR_merchant::model()->findByPk( $merchant_id);
								
				$model->parent_id = $id;
				$model->reply_from = $merchant->restaurant_name;
				$model->review = $model->reply_comment;
								
				if($model->save()){
					$this->redirect(array(Yii::app()->controller->id.'/reviews'));
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			} else Yii::app()->user->setFlash('error',t(HELPER_CORRECT_FORM));
		}
		
		$this->render("//merchant/review_reply",array(
		  'model'=>$model,
		  'find'=>$find,
		  'status'=>(array)AttributesTools::StatusManagement('post'),		  
		  'links'=>array(
	            t("All Review")=>array(Yii::app()->controller->id.'/reviews'),        
                $this->pageTitle,
		    ),	 
		));				
	}
	
	public function actionreview_reply_update()
	{
	    $this->pageTitle = t("Update Review");
		CommonUtility::setMenuActive('.buyer','.customer_reviews');
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;
		
		$id = (integer) Yii::app()->input->get('id');			
		$model = AR_review::model()->findByPk( $id );
		
		if(!$model){				
			$this->render("error");				
			Yii::app()->end();
		}		
		
		$model->scenario = 'reply';
		$model->reply_comment = $model->review;
		
		$find = AR_review::model()->find('id=:id', 
		array(':id'=>$model->parent_id));
		
		if(isset($_POST['AR_review'])){
			$model->attributes=$_POST['AR_review'];
			if($model->validate()){
				$model->review = $model->reply_comment;
				if($model->save()){
					$this->redirect(array(Yii::app()->controller->id.'/reviews'));
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			} else Yii::app()->user->setFlash('error',t(HELPER_CORRECT_FORM));
		}
		
		$this->render("//merchant/review_reply",array(
		  'model'=>$model,
		  'find'=>$find,
		  'status'=>(array)AttributesTools::StatusManagement('post'),		  
		  'links'=>array(
	            t("All Review")=>array(Yii::app()->controller->id.'/reviews'),        
                $this->pageTitle,
		    ),	 
		));		
	}
	
	public function actioncustomerreview_delete()
	{
		$can_edit_reviews = isset(Yii::app()->params['settings']['merchant_can_edit_reviews'])?Yii::app()->params['settings']['merchant_can_edit_reviews']:'';
		if($can_edit_reviews!=1){
			$this->render('//tpl/error',array(
			 'error'=>array(
			   'message'=>t("Your not allowed to access this page")
			 )
			));
			Yii::app()->end();
		}
		
		$id = (integer) Yii::app()->input->get('id');			
		$model = AR_review::model()->findByPk( $id );		
		if($model){
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array(Yii::app()->controller->id.'/reviews'));			
		} else $this->render("error");
	}

	public function actionlist()
    {
        $this->pageTitle=t("Customer list");

        $table_col = array(            
            'client_id'=>array(
              'label'=>t("client_id"),
              'width'=>'1%'
            ),
            'avatar'=>array(
              'label'=>t(""),
              'width'=>'15%'
            ),
            'first_name'=>array(
              'label'=>t("Name"),
              'width'=>'40%'
            ),
            'date_created '=>array(
              'label'=>t("Actions"),
              'width'=>'15%'
            ),            
          );
          $columns = array(            
            array('data'=>'client_id','visible'=>false),
            array('data'=>'avatar'),
            array('data'=>'first_name'),            
            array('data'=>'date_created','orderable'=>false),
          );		

         $this->render('customer_list',array(
            'table_col'=>$table_col,
            'columns'=>$columns,
            'order_col'=>2,
            'sortby'=>'asc',                        
        ));
    }    

	public function actionview()
	{
		$this->pageTitle = t("Customer Details");
		CommonUtility::setMenuActive('.buyer',".customer_list");

		$client_uuid = Yii::app()->input->get('id');
		$model = AR_client::model()->find("client_uuid=:client_uuid",[
			':client_uuid'=>$client_uuid
		]);			
		if(!$model){				
			$this->render("//tpl/error",[
				'error'=>[
					'message'=>t(HELPER_RECORD_NOT_FOUND)
				]
			]);				
			Yii::app()->end();
		}	
		
		$custom_fields = AttributesTools::getCustomFieldsValue($model->client_id);

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
			'template_name'=>"//buyer/customer_create",
			'widget'=>'WidgetCustomerMerchant',		
			'avatar'=>$avatar,
			'params'=>array(  
			    'model'=>$model,
			    'upload_path'=>$upload_path,
			    'status_list'=>(array)AttributesTools::StatusManagement('customer'),	
			    'hide_nav'=>true,
			    'links'=>array(
					t("All Customer")=>array('customer/list'),  
					ucwords($model->first_name." ".$model->last_name),
					$this->pageTitle,
				),
				'custom_fields'=>$custom_fields
			)
		));		
	}

	public function actionaddresses()
	{
		$this->pageTitle = t("Address list");
		CommonUtility::setMenuActive('.buyer',".customer_list");

		$client_uuid = Yii::app()->input->get('id');
		$model = AR_client::model()->find("client_uuid=:client_uuid",[
			':client_uuid'=>$client_uuid
		]);			
		if(!$model){				
			$this->render("//tpl/error",[
				'error'=>[
					'message'=>t(HELPER_RECORD_NOT_FOUND)
				]
			]);				
			Yii::app()->end();
		}			

		$action_name='address_list';
		$delete_link = Yii::app()->CreateUrl("customer/address_delete");			
		
		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
		),'action_name');
		

		$avatar = CMedia::getImage($model->avatar,$model->path,Yii::app()->params->size_image_thumbnail,
				CommonUtility::getPlaceholderPhoto('customer'));
				
		$this->render("//tpl/submenu_tpl",array(
		    'model'=>$model,
			'template_name'=>'//buyer/address_list',
			'widget'=>'WidgetCustomerMerchant',		
			'avatar'=>$avatar,
			'params'=>array(  
			   'model'=>$model,			   
			   'country_list'=>AttributesTools::CountryList(),
			   'hide_nav'=>false,
			   'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/address_create",array(
			    'create'=>1,
	            'id'=>$model->client_uuid
			   )),
			   'links'=>array(
		            t("All Customer")=>array(Yii::app()->controller->id.'/list'),  
					ucwords($model->first_name." ".$model->last_name),
	                $this->pageTitle,
			    ),
			   'links2'=>array(
		            t("All Address")=>array(Yii::app()->controller->id.'/addresses','id'=>$model->client_id),
	                $this->pageTitle,
			    ), 
			 )
		));

	}

	public function actionaddress_delete()
	{
		$id = (integer) Yii::app()->input->get('id');					
		$model = AR_client_address::model()->findByPk( $id );
		if($model){			
			$client = AR_client::model()->findByPk($model->client_id);			
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array(Yii::app()->controller->id.'/addresses','id'=>$client->client_uuid));			
		} else $this->render("error");
	}

	public function actionaddress_update()
	{
		$this->actionaddress_create(true);
	}

	public function actionaddress_create($update=false)
	{		
		$this->pageTitle = $update==false? t("Add Address") :  t("Update Address");
		CommonUtility::setMenuActive('.buyer',".customer_list");		
		CommonUtility::setSubMenuActive('.customer-menu','.customer-address');		
						
        $client_uuid = Yii::app()->input->get('id');		
		$model = AR_client::model()->find("client_uuid=:client_uuid",[
			':client_uuid'=>$client_uuid
		]);			
		if(!$model){					
			$this->render("//tpl/error",[
				'error'=>[
					'message'=>t(HELPER_RECORD_NOT_FOUND)
				]
			]);				
			Yii::app()->end();
		}	

	    $client_uuid = $model->client_uuid;
		$client_id = $model->client_id;
				
        $avatar = CMedia::getImage($model->avatar,$model->path,'@thumbnail',
		CommonUtility::getPlaceholderPhoto('customer'));
						
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
					   $this->redirect(array(Yii::app()->controller->id.'/addresses','id'=>$client_uuid));		
					} else {
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
						$this->refresh();
					}					
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString($model->getErrors(),'<br/>') );			
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
								
		$this->render("//tpl/submenu_tpl",array(
		    'model'=>$model,
			'template_name'=>"//buyer/$tpl",
			'widget'=>'WidgetCustomerMerchant',		
			'avatar'=>$avatar,
			'params'=>array(  
			   'model'=>$model,			   			   
			   'delivery_option'=>CCheckout::deliveryOption(),
			   'address_label'=>CCheckout::addressLabel(),
			   'hide_nav'=>false,
			   'links'=>array(
		            t("All Customer")=>array(Yii::app()->controller->id.'/list'),  
		            t("Address")=>array(Yii::app()->controller->id.'/addresses','id'=>$client_uuid),
	                $this->pageTitle,
			    ),
			   'links2'=>array(		            
	                $this->pageTitle,
			    ), 
			 )
		));
	}	

	public function actionorder_history()
	{
		$this->pageTitle=t("Order list");
		CommonUtility::setMenuActive('.buyer',".customer_list");
				
		$action_name='customer_order_list';
		$delete_link = Yii::app()->CreateUrl("customer/order_delete");			
		
		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
		),'action_name');
		
		
        $client_uuid = Yii::app()->input->get('id');		
		$model = AR_client::model()->find("client_uuid=:client_uuid",[
			':client_uuid'=>$client_uuid
		]);			
		if(!$model){				
			$this->render('//tpl/error',array(
			 'error'=>array(
			   'message'=>t(Helper_not_found)
			 )
			));
			return ;
		}	

		$client_id = $model->client_id;
				
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
		  array('data'=>'order_uuid','orderable'=>false),		
		  array('data'=>null,'orderable'=>false,'visible'=>false),
		);						
		
		$this->render("//tpl/submenu_tpl",array(
		    'model'=>$model,
			'template_name'=>'customer_order_list',
			'widget'=>'WidgetCustomerMerchant',		
			'avatar'=>$avatar,
			'params'=>array(  
			   'model'=>$model,				   
			   'hide_nav'=>false,
			   'api'=>Yii::app()->createUrl("/apibackend"),
			   'links'=>array(
		            t("All Customer")=>array(Yii::app()->controller->id.'/list'),  
					ucwords($model->first_name." ".$model->last_name),
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
						
		$this->render('//buyer/email_subscriber',array(			
		'ajax_url'=>Yii::app()->createUrl("/apibackend"),		
		'table_col'=>$table_col,
		'columns'=>$columns,
		'order_col'=>0,
		'sortby'=>'desc',			
		));
	}		

	public function actionsubscriber_delete()
	{
		$id = (integer) Yii::app()->input->get('id');			
		$merchant_id = Yii::app()->merchant->merchant_id;
		$model = AR_subscriber::model()->find("id=:id AND merchant_id=:merchant_id AND subcsribe_type=:subcsribe_type",[
			':id'=>$id,
			':merchant_id'=>$merchant_id,
			':subcsribe_type'=>'merchant'
		]);
		if($model){
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array('customer/email_subscriber'));			
		} else $this->render("error");
	}	
	
}
/*end class*/