<?php
class PointsController extends CommonController
{
	public $layout='backend';
	
	
	public function beforeAction($action)
	{					
		InlineCSTools::registerStatusCSS();					
		return true;
	}

    public function actionsettings()
    {
        $this->pageTitle = t("Settings");

        $model=new AR_option;		
		$model->scenario='points_settings';

        $options = array(
			'points_enabled','points_earning_rule','points_minimum_purchase','points_earning_points',
			'points_redeemed_points','points_redeemed_value','points_maximum_purchase','points_minimum_redeemed','points_maximum_redeemed',
			'points_redemption_policy','points_cover_cost','points_registration','points_review','points_first_order','points_booking',
			'points_expiry','points_use_thresholds','points_minimum_subtotal','points_bunos_template'
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

		
		$rule_based = AttributesTools::getPointsRuleBased();
		$redemption_policy_list = AttributesTools::redemptionPolicy();		
		$redemption_covered_list = AttributesTools::redemptionCostCovered();
		$currency_symbol = Price_Formatter::$number_format['currency_symbol'];		

		$template_list =  CommonUtility::getDataToDropDown("{{templates}}",'template_id','template_name',
		"","ORDER BY template_name ASC");				

        $this->render("settings",[
            'model'=>$model,
			'rule_based'=>$rule_based,
			'redemption_policy_list'=>$redemption_policy_list,
			'redemption_covered_list'=>$redemption_covered_list,
			'currency_symbol'=>$currency_symbol,
			'template_list'=>$template_list,
            'params'=>[
                'links'=>array(		 
                    t("Loyalty Points")=>array('mobilemerchant/api_access'),          
                      $this->pageTitle,                           
                ),
            ],
			'points_expiry_options'=>AttributesTools::pointsExpiryOptions()
        ]);
    }

	public function actionrewards()
	{
		$this->pageTitle=t("User Reward Points");

		$table_col = array(			
			'card_id'=>array(
			  'label'=>t("Customer"),
			  'width'=>'35%'
			),
			'transaction_amount'=>array(
			  'label'=>t("Balance"),
			  'width'=>'20%'
			),
			'transaction_type'=>array(
				'label'=>t("Actions"),
				'width'=>'15%'
			)			
		  );
		  $columns = array(			
			array('data'=>'card_id'),
			array('data'=>'transaction_amount','orderable'=>false),		
			array('data'=>'transaction_type','orderable'=>false),						
		  );				
				  
		  $this->render('//points/user_rewards',array(
			'table_col'=>$table_col,
			'columns'=>$columns,
			'order_col'=>0,
			'sortby'=>'desc',			
		  ));
	}

	public function actiontransactions()
	{
		$this->pageTitle=t("Transactions History");
		CommonUtility::setMenuActive('.admin_loyalty',".points_rewards");

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
		
		  $card_id = Yii::app()->input->get('card_id');		
		  $customer = CWallet::getCustomer($card_id);		  		  
		  $transaction_type = AttributesTools::transactionTypeList(true);		  

		  $this->render('//points/transaction_logs',array(
			'back_url'=>Yii::app()->CreateUrl("/points/rewards"),
			'card_id'=>$card_id,
			'customer'=>$customer,			
			'transaction_type'=>$transaction_type,			
			'table_col'=>$table_col,
			'columns'=>$columns,
			'order_col'=>0,
			'sortby'=>'desc',	
			'available_label'=>t("Available Points")		
		  ));
	}

	public function actionalltransactions()
	{
		$this->pageTitle=t("All Points Transactions");		

		$table_col = array(
			'transaction_id'=>array(
				'label'=>t("#"),
				'width'=>'5%'
			  ),
			'transaction_date'=>array(
			  'label'=>t("Date"),
			  'width'=>'20%'
			),
			'card_id'=>array(
				'label'=>t("Customer"),
				'width'=>'20%'
			),
			'transaction_description'=>array(
			  'label'=>t("Transaction"),
			  'width'=>'35%'
			),
			'transaction_amount'=>array(
			  'label'=>t("Debit/Credit"),
			  'width'=>'15%'
			),
			'running_balance'=>array(
			   'label'=>t("Running Balance"),
			   'width'=>'15%'
			)			
		  );
		  $columns = array(
			array('data'=>'transaction_id','visible'=>false),
			array('data'=>'transaction_date'),
			array('data'=>'card_id','orderable'=>true),
			array('data'=>'transaction_description'),
			array('data'=>'transaction_amount', 'className'=> "text-right"),			
			array('data'=>'running_balance', 'className'=> "text-right", 'visible'=>false),
		  );				
				  
		  $this->render('//points/all_transactions',array(					
			'table_col'=>$table_col,
			'columns'=>$columns,
			'order_col'=>0,
			'sortby'=>'desc',			
		  ));
	}

	public function actionthresholds()
	{
		$this->pageTitle=t("Redeem thresholds");
		$currency_symbol = Price_Formatter::$number_format['currency_symbol'];
		$table_col = array(			
			'meta_id'=>array(
			  'label'=>t("ID"),
			  'width'=>'15%'
			),
			'meta_value'=>array(
			  'label'=>t("Redeeming Points"),
			  'width'=>'20%'
			),
			'meta_value1'=>array(
				'label'=>t("Redeeming value")." $currency_symbol",
				'width'=>'20%'
			  ),
			'meta_name'=>array(
				'label'=>t("Actions"),
				'width'=>'15%'
			)			
		  );
		  $columns = array(			
			array('data'=>'meta_id'),
			array('data'=>'meta_value'),		
			array('data'=>'meta_value1'),		
			array('data'=>'meta_name','orderable'=>false),						
		  );				
				  
		  $this->render('//points/thresholds',array(
			'actions'=>'pointsThresholds',
			'table_col'=>$table_col,
			'columns'=>$columns,
			'order_col'=>0,
			'sortby'=>'desc',		
			'link'=>Yii::app()->CreateUrl("/points/create_thresholds"),	
		  ));
	}

	public function actionupdate_thresholds()
	{
		$this->actioncreate_thresholds(true);
	}

	public function actioncreate_thresholds($update=false)
	{
		$this->pageTitle=t("Transactions History");
		CommonUtility::setMenuActive('.admin_loyalty',".points_thresholds");

		$this->pageTitle = $update==false? t("Add thresholds") :  t("Update thresholds");		
		
		if($update){
			$id = (integer) Yii::app()->input->get('id');	
			$model = AR_admin_meta::model()->findByPk( $id );				
			if(!$model){				
				$this->render("error");				
				Yii::app()->end();
			}	
		} else $model=new AR_admin_meta;	

		$currency_symbol = Price_Formatter::$number_format['currency_symbol'];

		$model->scenario = 'points_thresholds';
		
		if(isset($_POST['AR_admin_meta'])){
			$model->attributes=$_POST['AR_admin_meta'];
			$model->meta_name = AttributesTools::pointsThresholds();
			if($model->validate()){										
				if($model->save()){
					if(!$update){
					   $this->redirect(array('points/thresholds'));		
					} else {
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
						$this->refresh();
					}
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			} 
		}

		$this->render("thresholds_create",array(
		    'model'=>$model,	
			'currency_symbol'=>$currency_symbol,
			'links'=>array(
				t("All thresholds")=>array('points/thresholds'),        
				$this->pageTitle,
			)
		));
	}

	public function actiondelete_thresholds()
	{
		$id = (integer) Yii::app()->input->get('id');			
		$model = AR_admin_meta::model()->findByPk( $id );
		if($model){
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array('points/thresholds'));			
		} else $this->render("error");
	}

	public function actionmonthly_thresholds()
	{
		$this->pageTitle=t("Monthly thresholds");
		$currency_symbol = Price_Formatter::$number_format['currency_symbol'];
		$table_col = array(			
			'meta_id'=>array(
			  'label'=>t("ID"),
			  'width'=>'15%'
			),
			'meta_value'=>array(
			  'label'=>t("Points earning in month"),
			  'width'=>'20%'
			),
			'meta_value1'=>array(
				'label'=>t("Bunos Points"),
				'width'=>'20%'
			  ),
			'meta_name'=>array(
				'label'=>t("Actions"),
				'width'=>'15%'
			)			
		  );
		  $columns = array(			
			array('data'=>'meta_id'),
			array('data'=>'meta_value'),		
			array('data'=>'meta_value1'),		
			array('data'=>'meta_name','orderable'=>false),						
		  );				
				  
		  $this->render('//points/thresholds',array(
			'actions'=>"monthlyThresholds",
			'table_col'=>$table_col,
			'columns'=>$columns,
			'order_col'=>0,
			'sortby'=>'desc',		
			'link'=>Yii::app()->CreateUrl("/points/create_monthlybunos"),	
		  ));
	}

	public function actionupdate_monthlybunos()
	{
		$this->actioncreate_monthlybunos(true);
	}

	public function actioncreate_monthlybunos($update=false)
	{
		$this->pageTitle=t("Monthly thresholds");
		CommonUtility::setMenuActive('.admin_loyalty',".points_monthly_thresholds");

		$this->pageTitle = $update==false? t("Add Mothly thresholds") :  t("Update Mothly thresholds");		
		
		if($update){
			$id = (integer) Yii::app()->input->get('id');	
			$model = AR_admin_meta::model()->findByPk( $id );				
			if(!$model){				
				$this->render("error");				
				Yii::app()->end();
			}	
		} else $model=new AR_admin_meta;	

		$currency_symbol = Price_Formatter::$number_format['currency_symbol'];

		$model->scenario = 'points_thresholds';
		
		if(isset($_POST['AR_admin_meta'])){
			$model->attributes=$_POST['AR_admin_meta'];
			$model->meta_name = AttributesTools::monthlyThresholds();
			if($model->validate()){										
				if($model->save()){
					if(!$update){
					   $this->redirect(array('points/monthly_thresholds'));		
					} else {
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
						$this->refresh();
					}
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			} 
		}

		$this->render("monthly_create",array(
		    'model'=>$model,	
			'currency_symbol'=>$currency_symbol,
			'links'=>array(
				t("All thresholds")=>array('points/monthly_thresholds'),        
				$this->pageTitle,
			)
		));
	}	

	public function actiondelete_monthlybunos()
	{
		$id = (integer) Yii::app()->input->get('id');			
		$model = AR_admin_meta::model()->findByPk( $id );
		if($model){
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array('points/monthly_thresholds'));			
		} else $this->render("error");
	}

}
// end class