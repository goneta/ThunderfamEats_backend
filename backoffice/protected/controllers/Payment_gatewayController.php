<?php
class Payment_gatewayController extends CommonController
{
	
	public function beforeAction($action)
	{										
		InlineCSTools::registerStatusCSS();
		return true;
	}
		
	public function actionlist()
	{		
				
		$this->pageTitle=t("Payment gateway list");
		$action_name='payment_gateway_list';
		$delete_link = Yii::app()->CreateUrl("payment_gateway/delete");
		
		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
		),'action_name');
				
		$this->render('list',array(
		  'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/create"),
		  'sort_link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/sort"),
		));	
	}
	
	public function actionupdate()
	{
		$this->actioncreate(true);
	}
	
	public function actioncreate($update=false)
	{
		$this->pageTitle = $update==false? t("Add Gateway") :  t("Update Gateway");
		CommonUtility::setMenuActive('.payment_gateway',".payment_gateway_list");
		
		$multi_language = CommonUtility::MultiLanguage();
		$attr_json = ''; $instructions = array();
		$upload_path = CMedia::adminFolder();
		
		if($update){
			$id = (integer) Yii::app()->input->get('id');	
			$model = AR_payment_gateway::model()->findByPk( $id );			
			$model->scenario = "update";			
			$attr_json = !empty($model->attr_json)?json_decode($model->attr_json,true):'';		
			$instructions=!empty($model->attr4)?json_decode($model->attr4,true):'';						
			if(!$model){				
				$this->render("error");				
				Yii::app()->end();
			}	
		} else {
			$model=new AR_payment_gateway;	
			$model->scenario = "create";
		}
				
		if(isset($_POST['AR_payment_gateway'])){
			$model->attributes=$_POST['AR_payment_gateway'];
			$model->file = CUploadedFile::getInstance($model,'file');
			
			if($model->validate()){				
				
				if ($model->file) {
					$path = CommonUtility::uploadDestination('upload/crt/').$model->file->name;                					
                    $model->file->saveAs( $path );
					$model->attr3 = $path;
				}

				if(isset($_POST['photo'])){
					if(!empty($_POST['photo'])){
						$model->logo_image = $_POST['photo'];
						$model->path = isset($_POST['path'])?$_POST['path']:$upload_path;
					} else $model->logo_image = '';
				} else $model->logo_image = '';
				
				if($model->save()){
					if(!$update){
					   $this->redirect(array('payment_gateway/list'));		
					} else {
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
						$this->refresh();
					}
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			}
		}	
						
		$this->render("create",array(
		    'model'=>$model,		   		    		    
		    'attr_json'=>$attr_json,
		    'upload_path'=>$upload_path,
		    'status'=>(array)AttributesTools::StatusManagement('gateway'),
		    'instructions'=>$instructions,
		    'protocol'=> isset($_SERVER["HTTPS"]) ? 'https' : 'http',
			'site_url'=>CommonUtility::getHomebaseUrl()
		));
	}	
	
	public function actionDelete()
	{
		$id = (integer) Yii::app()->input->get('id');			
		$model = AR_payment_gateway::model()->findByPk( $id );
		if($model){
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array('payment_gateway/list'));			
		} else $this->render("error");
	}

	public function actionbank_deposit()
	{
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
		  array('data'=>'deposit_type','visible'=>true),
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
				
		$this->render('//payment_gateway/bank_deposit_list',array(
		  'table_col'=>$table_col,
		  'columns'=>$columns,
		  'order_col'=>1,
          'sortby'=>'desc',		  
		));
	}
	
	public function actionbank_deposit_delete()
	{
		$id =  Yii::app()->input->get('id');			
		$model = AR_bank_deposit::model()->find("deposit_uuid=:deposit_uuid",array(
		  ':deposit_uuid'=>trim($id)
		));
		if($model){
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array('payment_gateway/bank_deposit'));			
		} else $this->render("error");
	}

	public function actionbank_deposit_view()
	{
		$this->pageTitle = t("Edit");
		CommonUtility::setMenuActive('.payment_gateway',".payment_gateway_bank_deposit");

		$atts = OptionsTools::find(['multicurrency_enabled']);
        $multicurrency_enabled = isset($atts['multicurrency_enabled'])?$atts['multicurrency_enabled']:false;
        $multicurrency_enabled = $multicurrency_enabled==1?true:false;        

        $price_list_format = CMulticurrency::getAllCurrency();

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
			$model->amount = Price_Formatter::formatNumberNoSymbol($model->amount);
			$this->render("//payment_gateway/bank_deposit",[
				'model'=>$model,
				'status'=>AttributesTools::BankStatusList(),
				'links'=>array(
					t("Bank Deposit")=>array('payment_gateway/bank_deposit'),        
					$this->pageTitle,
				),
				'multicurrency_enabled'=>$multicurrency_enabled,
				'price_list_format'=>$price_list_format
			]);
		} else $this->render("error");
	}

	public function actionsort()
	{
		$this->pageTitle=t("Sort");
		CommonUtility::setMenuActive('.payment_gateway',".payment_gateway_list");	
				
		
		$model = new AR_payment_gateway();

		if(isset($_POST['id'])){
			$data = $_POST['id'];
			if(is_array($data) && count($data)>=1){				
				foreach ($data as $index=> $payment_id) {						
					$model = AR_payment_gateway::model()->find("payment_id=:payment_id",[						
						':payment_id'=>intval($payment_id)
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
			$category = CPayments::List();
		} catch (Exception $e) {
			$category = [];
		}				

		$this->render('//tpl/sort',[
			'data'=>$category,
			'model'=>$model,
			'links'=>array(
	            t("All Payment")=>array(Yii::app()->controller->id.'/list'),        
                $this->pageTitle,
		    ),	    	
		]);
	}	

	public function actionpaydelivery_list()
	{
		$this->pageTitle=t("Pay on delivery");
		$action_name='paydelivery_list';
		$delete_link = Yii::app()->CreateUrl("payment_gateway/paydelivery_delete");
		
		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
		),'action_name');
				
		
		$this->render( "//tpl/list" ,array(
			'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/paydelivery_create"),
			'sort_link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/paydelivery_sort"),
		));
	}

	public function actionpaydelivery_update()
	{
		$this->actionpaydelivery_create(true);
	}

	public function actionpaydelivery_create($update=false)
	{
		$this->pageTitle = $update==false? t("Add") : t("Update");
		CommonUtility::setMenuActive('.payment_gateway',".payment_gateway_paydelivery_list");
		
		$id='';		
		$upload_path = CMedia::adminFolder();
		
		if($update){
			$id = (integer) Yii::app()->input->get('id');	
			$model = AR_paydelivery::model()->findByPk( $id );				
			if(!$model){				
				$this->render("error");				
				Yii::app()->end();
			}	
			
		} else {
			$model=new AR_paydelivery;		
			$model->status = 'publish';	
		}		
		
		if(isset($_POST['AR_paydelivery'])){
			$model->attributes=$_POST['AR_paydelivery'];
			if(isset($_POST['photo'])){
				if(!empty($_POST['photo'])){
					$model->photo = $_POST['photo'];
					$model->path = isset($_POST['path'])?$_POST['path']:$upload_path;
				} else $model->photo = '';
			} else $model->photo = '';
			
			if($model->validate()){
																									
				if($model->save()){
					if(!$update){
					   $this->redirect(array('payment_gateway/paydelivery_list'));		
					} else {
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
						$this->refresh();
					}
				} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString($model->getErrors()) );	
			} else {				
				Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString($model->getErrors()) );	
			}
		}	
				
						
		$this->render("paydelivery_create",array(
		    'model'=>$model,		    		    
		    'status'=>(array)AttributesTools::StatusManagement('post'),		
		    'upload_path'=>$upload_path,
		));
	}

	public function actionpaydelivery_delete()
	{
		$id = (integer) Yii::app()->input->get('id');			
		$model = AR_paydelivery::model()->findByPk( $id );
		if($model){
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array('payment_gateway/paydelivery_list'));			
		} else $this->render("error");
	}
		
	public function actionpaydelivery_sort()
	{
		$this->pageTitle=t("Sort");
		CommonUtility::setMenuActive('.payment_gateway',".payment_gateway_paydelivery_list");	
				
		
		$model = new AR_paydelivery();

		if(isset($_POST['id'])){
			$data = $_POST['id'];
			if(is_array($data) && count($data)>=1){				
				foreach ($data as $index=> $payment_id) {						
					$model = AR_paydelivery::model()->find("id=:id",[						
						':id'=>intval($payment_id)
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
			$category = CPayments::PayondeliveryList();
		} catch (Exception $e) {
			$category = [];
		}			

		$this->render('//tpl/sort',[
			'data'=>$category,
			'model'=>$model,
			'links'=>array(
	            t("Pay on delivery")=>array(Yii::app()->controller->id.'/paydelivery_list'),        
                $this->pageTitle,
		    ),	    	
		]);
	}	

}
/*end class*/