<?php
class MulticurrencyController extends CommonController
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
        $model->scenario='multicurrency';

        $options = array(
          'multicurrency_enabled','multicurrency_allowed_merchant_choose_currency','multicurrency_apikey','multicurrency_provider'
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
            if(DEMO_MODE){
              $model[$name]=CommonUtility::mask($val);
            } else $model[$name]=$val;            
          }		
        }			

        $cron_key = CommonUtility::getCronKey();		
        $params = ['key'=>$cron_key];

        $cron_link = [
          [
            'link'=> "curl ".CommonUtility::getHomebaseUrl()."/taskexchangerate/?".http_build_query($params)." >/dev/null 2>&1",
            'label'=>t("run end of the day")
          ],			
        ];

        $this->render("//tpl/submenu_tpl",array(
          'model'=>$model,
          'template_name'=>"settings",
          'widget'=>'WidgetMultiCurrency',					
          'params'=>array(  
            'model'=>$model,
            'cron_link'=>$cron_link,             
            'links'=>array(		 
              t("Multi Currency")=>array('multicurrency/settings'),          
                $this->pageTitle,                           
            ),
          )
       ));		       
    }

    public function actionexchangerate()
    {
        $this->pageTitle=t("Exchange Rate");		
        
        $table_col = array(		  
            'id'=>array(
              'label'=>t("ID"),
              'width'=>'5%'
            ),		              
            'provider'=>array(
              'label'=>t("Provider"),
              'width'=>'15%'
            ),
            'base_currency'=>array(
              'label'=>t("Base Currency"),
              'width'=>'13%'
            ),
            'currency_code'=>array(
              'label'=>t("Currency"),
              'width'=>'13%'
            ),
            'exchange_rate'=>array(
              'label'=>t("Exchange Rate"),
              'width'=>'15%'
            ),           
            'date_created'=>array(
                'label'=>t("Date"),
                'width'=>'15%'
              ),           
            'date_modified'=>array(
              'label'=>t("Actions"),
              'width'=>'15%'
            ),
          );
          $columns = array(
              array('data'=>'id', 'visible'=>true),              
              array('data'=>'provider' ,'visible'=>false),
              array('data'=>'base_currency'),
              array('data'=>'currency_code'),
              array('data'=>'exchange_rate'),              
              array('data'=>'date_created'),     
              array('data'=>null,'orderable'=>false,
                 'defaultContent'=>'
                 <div class="btn-group btn-group-actions" role="group">                    
                    <a class="ref_edit normal btn btn-light tool_tips"><i class="zmdi zmdi-border-color"></i></a>
                    <a class="ref_delete normal btn btn-light tool_tips"><i class="zmdi zmdi-delete"></i></a>
                 </div>
                 '
              ),	  
          );		        
          
          $this->render('exchange_rate',array(
            'table_col'=>$table_col,
            'columns'=>$columns,
            'order_col'=>0,
            'sortby'=>'DESC',		  
            'ajax_url'=>Yii::app()->createUrl("/api"),
            'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/add"),
            'exchange_rate_link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/updateechangerate"),
            'clear_link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/clearexchangerate"),
          ));
    }

    public function actionupdateechangerate()
    {
         $this->pageTitle =  t("Update exchange rate");
		     CommonUtility::setMenuActive('.multi_currency','.multicurrency_exchangerate');		
         
         $return_url = Yii::app()->createUrl("/multicurrency/exchangerate");

         if(DEMO_MODE){			
              $this->render('//tpl/error',array(  
                  'error'=>array(
                  'message'=>t("Modification not available in demo")
                  )
                ));	
              return false;
        } 

         try {
          
              CMulticurrency::validateCurrencyByAPI();             

              Yii::import('ext.runactions.components.ERunActions');	
              $cron_key = CommonUtility::getCronKey();		
              $params = ['key'=>$cron_key];                           
              CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/taskexchangerate/?".http_build_query($params) );
              $this->render("updateechangerate",[
                   'class'=>'alert-success',
                  'msg'=>t("Exchange rate update has been executed, it make take a while depends on api response."),
                  'return_url'=>$return_url,
                  'links'=>array(
                      t("Exchange Rates")=>array('multicurrency/exchangerate'),        
                      $this->pageTitle,
                  )
              ]);        

          } catch (Exception $e) {
              $this->render("updateechangerate",[
                'class'=>'alert-warning',
                'msg'=>t($e->getMessage()),
                'return_url'=>$return_url,
                'links'=>array(
                  t("Exchange Rates")=>array('multicurrency/exchangerate'),        
                  $this->pageTitle,
                 )
              ]);              
         }                          
    }

    public function actionupdate()
    {
        $this->actionAdd(true);
    }

    public function actionAdd($update=false)
    {
        $this->pageTitle =  $update==true? t("Update"): t("Add");
		    CommonUtility::setMenuActive('.multi_currency','.multicurrency_exchangerate');

        $options = OptionsTools::find(['multicurrency_provider']);        
        $provider = isset($options['multicurrency_provider'])?$options['multicurrency_provider']:'';

        $model = new AR_currency_exchangerate();

        $id = Yii::app()->input->get('id');					
        if($update){
            $model = AR_currency_exchangerate::model()->findByPk($id);
            if(!$model){
              $this->render('//tpl/error',array(
              'error'=>array(
                'message'=>t(Helper_not_found)
              )
              ));
              return ;
            }			              
        }        
        
        if(isset($_POST['AR_currency_exchangerate'])){
            $model->attributes=$_POST['AR_currency_exchangerate'];	
            $model->provider = $provider;
            if($model->validate()){              
              if($model->save()){
                if(!$update){
                    $this->redirect(array('multicurrency/exchangerate'));		
                } else {
                  Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
                  $this->refresh();
                }                
              } else Yii::app()->user->setFlash('error',t(Helper_failed_update));
            } else {              
              Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString( $model->getErrors(),"<br/>" ));
            }
        }
                
        $this->render("exchange_rate_add",[
            'model'=>$model,
            //'list'=>CMulticurrency::currencyList(),
            'list'=>AttributesTools::currencyListSelection(),
            'links'=>array(
              t("Exchange Rates")=>array('multicurrency/exchangerate'),        
              $this->pageTitle,
            )
        ]);
    }

    public function actiondelete()
    {
      $id = (integer) Yii::app()->input->get('id');			
      $model = AR_currency_exchangerate::model()->findByPk( $id );
      if($model){
        $model->delete(); 
        Yii::app()->user->setFlash('success', t("Succesful") );					
        $this->redirect(array('multicurrency/exchangerate'));			
      } else $this->render("error");
    }

    public function actionclearexchangerate()
    {
        if(DEMO_MODE){			
              $this->render('//tpl/error',array(  
                  'error'=>array(
                  'message'=>t("Modification not available in demo")
                  )
                ));	
              return false;
        }          
        Yii::app()->db->createCommand()->truncateTable("{{currency_exchangerate}}");
        $this->redirect(array('multicurrency/exchangerate'));			
    }

    public function actionpayment_gateway()
    {
        
        $this->pageTitle = t("Payment gateway");
        CommonUtility::setMenuActive('.multi_currency','.multicurrency_settings');

        $model=new AR_option;		
        $model->scenario='multicurrency';        
        $admin_model = new AR_admin_meta;

        $options = array(
            'multicurrency_enabled_hide_payment',
        );

        $key_name = 'multicurrency_hide_payment';

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
              AR_admin_meta::model()->deleteAll('meta_name=:meta_name',array(
                ':meta_name'=>$key_name
              ));	              
              if(is_array($model->multicurrency_currency_list) && count($model->multicurrency_currency_list)>=1){
                foreach ($model->multicurrency_currency_list as $currency_code => $payment_gateway) {                    
                    if(is_array($payment_gateway) && count($payment_gateway)>=1){
                        foreach ($payment_gateway as $payment_code) {                            
                            $admin_model->saveMetaWithID2($key_name, $currency_code, $payment_code);                            
                        }
                    }                    
                }
              }                           
              if(OptionsTools::save($options, $model)){
                Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
                $this->refresh();
              } else Yii::app()->user->setFlash('error',t(Helper_failed_update));
            } 
        } else {
            $model_find = AR_admin_meta::model()->findAll("meta_name=:meta_name",[
              ':meta_name'=>$key_name
            ]);
            if($model_find){
               $selected_payment = [];
               foreach ($model_find as $items) {                   
                   $selected_payment[$items->meta_value][] = $items->meta_value1;
               }
               $model['multicurrency_currency_list'] = $selected_payment;
            }                        
        }

        if($data = OptionsTools::find($options)){			
          foreach ($data as $name=>$val) {
            $model[$name]=$val;
          }		
        }			
        
        $this->render("//tpl/submenu_tpl",array(
          'model'=>$model,
          'template_name'=>"payment_gateway",
          'widget'=>'WidgetMultiCurrency',					
          'params'=>array(  
            'model'=>$model,     
            'currency_list'=>CMulticurrency::currencyList(),
            'payment_list'=>AttributesTools::PaymentProvider(),
            'links'=>array(		 
              t("Multi Currency")=>array('multicurrency/settings'),          
                $this->pageTitle,                           
            ),
          )
       ));		       
    }

    public function actioncheckout_currency()
    {
         
        $this->pageTitle = t("Checkout currencies");
        CommonUtility::setMenuActive('.multi_currency','.multicurrency_settings');

        $model=new AR_option;
        $admin_model = new AR_admin_meta;
        $model->scenario='multicurrency';      

        $options = array(
          'multicurrency_enabled_checkout_currency',
        );

        $key_name = 'multicurrency_checkout_currency';

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
              AR_admin_meta::model()->deleteAll('meta_name=:meta_name',array(
                ':meta_name'=>$key_name
              ));	              
              if(is_array($model->multicurrency_currency_list) && count($model->multicurrency_currency_list)>=1){
                foreach ($model->multicurrency_currency_list as $payment_gateway => $currency_code) {                                        
                    if(!empty($currency_code)){
                       $admin_model->saveMetaWithID2($key_name, $payment_gateway, $currency_code);                            
                    }                    
                }
              }                         
              if(OptionsTools::save($options, $model)){
                Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
                $this->refresh();
              } else Yii::app()->user->setFlash('error',t(Helper_failed_update));
            } 
        } else {
            $model_find = AR_admin_meta::model()->findAll("meta_name=:meta_name",[
              ':meta_name'=>$key_name
            ]);
            if($model_find){
              $selected_payment = [];
              foreach ($model_find as $items) {                   
                  $selected_payment[$items->meta_value] = $items->meta_value1;
              }
              $model['multicurrency_currency_list'] = $selected_payment;
            }            
        }

        if($data = OptionsTools::find($options)){			
          foreach ($data as $name=>$val) {
            $model[$name]=$val;
          }		
        }			

        
        $currency_list = CMulticurrency::currencyList();					
        $select = [''=>t("Please select")];
        $currency_list = $select+$currency_list;	

        $list = CommonUtility::getDataToDropDown("{{payment_gateway}}",'payment_code','payment_name',
		    "WHERE status='active' AND is_online=1 ","ORDER BY sequence ASC");        

        $this->render("//tpl/submenu_tpl",array(
          'model'=>$model,
          'template_name'=>"checkout_currency",
          'widget'=>'WidgetMultiCurrency',					
          'params'=>array(  
            'model'=>$model,     
            'currency_list'=>$currency_list,
            'payment_list'=>$list,
            'links'=>array(		 
              t("Multi Currency")=>array('multicurrency/settings'),          
                $this->pageTitle,                           
            ),
          )
       ));		       

    }

}
// end class