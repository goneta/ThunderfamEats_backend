<?php

class DigitalwalletController extends CommonController
{		
	public function beforeAction($action)
	{									
		CSeo::setPage();
		return true;
	}
		
	public function actionError()
	{
	    if($error=Yii::app()->errorHandler->error)
	    {
	    	if(Yii::app()->request->isAjaxRequest)
	    		echo $error['message'];
	    	else	    	    
	        	$this->render('error', array(
	        	 'error'=>$error
	        	));
	    }
	}

    public function actionsettings()
    {
        $this->pageTitle = t("Settings");

        $model=new AR_option;		
		$model->scenario='digitalwallet_settings';

        $options = [
            'digitalwallet_enabled','digitalwallet_transaction_limit','digitalwallet_enabled_topup','digitalwallet_topup_minimum','digitalwallet_topup_maximum',
			'digitalwallet_refund_to_wallet','strict_to_wallet'
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
				$model[$name]=$val;
			}		
		}		

        $currency_symbol = Price_Formatter::$number_format['currency_symbol'];		
		
        $this->render("settings",[
            'model'=>$model,			
            'currency_symbol'=>$currency_symbol,
            'params'=>[
                'links'=>array(		 
                    t("Digital Wallet")=>array('digitalwallet/settings'),          
                      $this->pageTitle,                           
                ),
            ]
        ]);        
    }
	
	public function actionbonus_funds()
	{
		$this->pageTitle=t("Bonus Funds");

		$table_col = array(			
			'discount_id'=>array(
			  'label'=>t("ID"),
			  'width'=>'8%'
			),
			'status'=>array(
				'label'=>t("Status"),
				'width'=>'8%'
			  ),
			'title'=>array(
			  'label'=>t("Title"),
			  'width'=>'15%'
			),            
            'amount'=>array(
                'label'=>t("Amount"),
                'width'=>'10%'
            ),
			'minimum_amount'=>array(
                'label'=>t("Min. Amount"),
                'width'=>'10%'
            ),
            'expiration_date'=>array(
                'label'=>t("Expiration"),
                'width'=>'10%'
            ),
			'date_created'=>array(
				'label'=>t("Actions"),
				'width'=>'10%'
			)			
		  );
		  $columns = array(			
			array('data'=>'discount_id'),
			array('data'=>'status','orderable'=>true),					
			array('data'=>'title','orderable'=>true),					
            array('data'=>'amount','orderable'=>true),						
			array('data'=>'minimum_amount','orderable'=>true),						
            array('data'=>'expiration_date','orderable'=>true),						            
            array('data'=>null,'orderable'=>false,
		     'defaultContent'=>'
		     <div class="btn-group btn-group-actions" role="group">
			    <a class="ref_edit normal btn btn-light tool_tips"><i class="zmdi zmdi-border-color"></i></a>
			    <a class="ref_delete normal btn btn-light tool_tips"><i class="zmdi zmdi-delete"></i></a>
			 </div>
		     '
		    ),	  
		  );				
				  
		  $this->render('bonus_funds',array(
			'table_col'=>$table_col,
			'columns'=>$columns,
			'order_col'=>0,
			'sortby'=>'desc',			
            'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/create_bonus"),			
		  ));
	}

    public function actionbunos_update()
    {
        $this->actioncreate_bonus(true);        
    }

    public function actioncreate_bonus($update=false)
    {
        $this->pageTitle=t("Add Bonus");
		CommonUtility::setMenuActive('.admin_wallet','.digitalwallet_bonus_funds');

        $id = Yii::app()->input->get('id');			
		$model = new AR_discount();
		if($update){

            $this->pageTitle=t("Update Bonus");

			$model = AR_discount::model()->find("discount_uuid=:discount_uuid",array(
			 ':discount_uuid'=>$id
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
        if(isset($_POST['AR_discount'])){
			$model->attributes=$_POST['AR_discount'];		
			$model->transaction_type = CDigitalWallet::transactionName();				
            if($model->validate()){										
				if($model->save()){
					if(!$update){
					   $this->redirect(array(Yii::app()->controller->id."/bonus_funds"));		
					} else {
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
						$this->refresh();
					}
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString( $model->getErrors(),"<br/>" ));		
        }

        $model->amount = Price_Formatter::convertToRaw($model->amount,2,true);
        $model->minimum_amount = Price_Formatter::convertToRaw($model->minimum_amount,2,true);

        $this->render('//digitalwallet/bonus_created',array(		  
            'model'=>$model,
            'status'=>(array)AttributesTools::StatusManagement('post'),    
            'type_list'=>AttributesTools::couponType(),        
            'links'=>array(
                  t("Bonus Funds")=>array(Yii::app()->controller->id."/bonus_funds"),        
                  $this->pageTitle,
              )
          ));

    }

    public function actionbunos_delete()
	{
		$id = Yii::app()->input->get('id');							
		$model = AR_discount::model()->find("discount_uuid=:discount_uuid",[
            ':discount_uuid'=>$id
        ]);				
		if($model){			
			$model->delete(); 			
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array(Yii::app()->controller->id.'/bonus_funds'));
		} else $this->render("/admin/error",array(
				 'error'=>array(
				   'message'=>t(HELPER_RECORD_NOT_FOUND)
				 )
				));		
	}

	public function actiontransactions()
	{
		$this->pageTitle=t("Digital Wallet Transactions");		

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
			  'width'=>'20%'
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

		  $transaction_type = AttributesTools::transactionTypeList(true);		  
				  
		  $this->render('//digitalwallet/transactions',array(					
			'table_col'=>$table_col,
			'columns'=>$columns,
			'order_col'=>0,
			'sortby'=>'desc',			
			'transaction_type'=>$transaction_type,			
		  ));
	}	

}
// end class