<?php
set_time_limit(0);
require 'dompdf/vendor/autoload.php';
require 'twig/vendor/autoload.php';
require 'firebase-php/vendor/autoload.php';
require 'ar-php/vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;

class InvoiceController extends CommonController
{
		
	public function beforeAction($action)
	{				
        InlineCSTools::registerStatusCSS();	
		return true;
	}
	
	public function actionList()
	{
        $this->pageTitle=t("Invoice list");
		$action_name='invoiceList';
		$delete_link = Yii::app()->CreateUrl("invoice/delete");
        $datatable_export = true;
		
		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
          "var datatable_export='$datatable_export';",
		),'action_name');
		
		if(Yii::app()->params['isMobile']==TRUE){
			$tpl = 'invoice_list';
		} else $tpl = 'invoice_list';
		
		$this->render($tpl,array(
		  'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/create")
		));	        
	}

    public function actionview()
    {
        try {
            CommonUtility::setMenuActive('.invoice_list','.invoice_list');			
            $invoice_uuid = Yii::app()->input->get('invoice_uuid');		        
            $model = CMerchantInvoice::getInvoice($invoice_uuid);        
            
            try {
                $history = CMerchantInvoice::getHistory($model->invoice_number);
            } catch (Exception $e) {
                $history = [];                
            }

            try {
                $payment_history = CMerchantInvoice::getPaymentHistory($model->invoice_number);
            } catch (Exception $e) {
                $payment_history = [];                
            }
            
            $is_due = false;            
            $today = gmdate("Y-m-d g:i:s a");	
            $date_diff = CommonUtility::dateDifference($model->due_date,$today);
            if(is_array($date_diff) && count($date_diff)>=1 && $model->payment_status !='paid' ){                
                if($date_diff['days']>0){
                    $is_due = true;
                }
            }            

                        
            $payment_info = AttributesTools::getInvoicePaymentInformation();                        
            $exchange_rate = $model->exchange_rate_merchant_to_admin>0?$model->exchange_rate_merchant_to_admin:1;
                        
            $model->invoice_total = Price_Formatter::convertToRaw($model->invoice_total,2);
            $model->amount_paid = Price_Formatter::convertToRaw($model->amount_paid,2);            

            $this->render("invoice_view",[
                'model'=>$model,
                'history'=>$history,
                'payment_history'=>$payment_history,
                'is_due'=>$is_due,
                'links'=>array(
                    t("Invoice list")=>array(Yii::app()->controller->id.'/list'),
                    t("View"),
                    "#".$model->invoice_number
                ),                
                'payment_info'=>$payment_info,
                'exchange_rate'=>$exchange_rate
                //'price_list_format'=>$price_list_format
            ]);
        } catch (Exception $e) {
            $this->render("//tpl/error",[
                'error'=>[
                    'message'=>t($e->getMessage())
                ]
            ]);        
        }        
    }

    public function actionpdf()
    {
        try {

            $invoice_uuid = Yii::app()->input->get('invoice_uuid');            
            $path = Yii::getPathOfAlias('webroot')."/twig";		                        
            $loader = new \Twig\Loader\FilesystemLoader($path);
            $twig = new \Twig\Environment($loader, [
                'cache' => $path."/compilation_cache",
                'debug'=>true
            ]);

            $model = CMerchantInvoice::getInvoice($invoice_uuid);
            $order_details = CMerchantInvoice::getInvoiceDetails($model->merchant_id,$model->invoice_terms,$model->date_from,$model->date_to,'admin');
            
            $site_data = OptionsTools::find(array('website_title','website_address','website_contact_phone','website_contact_email','website_logo'));
            $site = array(
                'logo'=>'',
                'title'=>isset($site_data['website_title'])?$site_data['website_title']:'',
                'address'=>isset($site_data['website_address'])?$site_data['website_address']:'',
                'contact'=>isset($site_data['website_contact_phone'])?$site_data['website_contact_phone']:'',
                'email'=>isset($site_data['website_contact_email'])?$site_data['website_contact_email']:'',		      
            );
            
            $website_logo = ''; $logo_path = '';

            if($model_logo = AR_admin_meta::getValue('receipt_logo')){                
                $website_logo = $model_logo['meta_value'];
                $logo_path = $model_logo['meta_value1'];
            }
            
            if($reslogo = CMerchantInvoice::imageBase64($website_logo,$logo_path)){
                $site['logo']=$reslogo;
            }                                      

            $exchange_rate = $model->exchange_rate_merchant_to_admin>0?$model->exchange_rate_merchant_to_admin:1;            

            $amount_due = ($model->invoice_total*$exchange_rate) - ($model->amount_paid*$exchange_rate); 
            $item = [
                'invoice_number'=>$model->invoice_number,
                'invoice_date'=>Date_Formatter::date($model->date_created),
                'due_date'=>Date_Formatter::date($model->due_date),
                'restaurant_name'=>$model->restaurant_name,
                'business_address'=>$model->business_address,
                'contact_phone'=>$model->contact_phone,
                'description'=>t("Commission ({from} - {to})",[
                    '{from}'=>Date_Formatter::date($model->date_from,"dd MMM yyyy",true),
                    '{to}'=>Date_Formatter::date($model->date_to,"dd MMM yyyy",true),
                ]),
                'invoice_total'=>Price_Formatter::formatNumberNoSymbol($model->invoice_total),
                'subtotal'=>Price_Formatter::formatNumberNoSymbol($model->invoice_total),
                'total'=>Price_Formatter::formatNumberNoSymbol($model->invoice_total),
                'amount_paid'=>Price_Formatter::formatNumberNoSymbol($model->amount_paid),
                'amount_due'=>Price_Formatter::formatNumberNoSymbol($amount_due),
                'payment_status'=>strtoupper($model->payment_status),
                'period'=>CMerchantInvoice::getTermsLabel($model->invoice_terms),
                'period_words'=> t("Invoice We hereby send you an invoice for our services in the period {start} - {end}",[
                    '{start}'=>Date_Formatter::date($model->date_from,"MMM dd,yyyy",true),
                    '{end}'=>Date_Formatter::date($model->date_to,"MMM dd,yyyy",true),
                ]),       
                'period_covered'=>t("Orders Covered from {start} - {end}",[
                    '{start}'=>Date_Formatter::date($model->date_from,"MMM dd,yyyy",true),
                    '{end}'=>Date_Formatter::date($model->date_to,"MMM dd,yyyy",true),
                ]),
                'the_invoice_amount'=>t("The invoice amount of {invoice_total} should be deposited before {due_date}, into our account.",[
                    '{invoice_total}'=>Price_Formatter::formatNumberNoSymbol($amount_due),
                    '{due_date}'=>Date_Formatter::date($model->due_date,"MMM dd,yyyy",true)
                ]),                
            ];       
            $label = [                
                'invoice_number'=>t("Invoice No#"),
                'invoice_number2'=>t("Invoice number"),
                'invoice_data'=>t("Invoice Date"),
                'due_date'=>t("Due Date"),
                'amount_due'=>t("AMOUNT DUE"),
                'billto'=>t("BILL TO"),
                'description'=>t("Description"),
                'total'=>t("Total"),
                'subtotal'=>t("Sub total"),
                'total'=>t("TOTAL"),
                'amount_paid'=>t("Amount paid"),
                'amount_due'=>t("AMOUNT DUE"),
                'period'=>t("Period"),       
                'payment_information'=>t("Payment information"),
                'the_balance'=>t("The balance will be taken from your account after your invoice generated."),
                'sincerely'=>t("Sincerely"),
                'order_id'=>t("Order ID"),
                'date'=>t("Date"),
                'amount'=>t("Amount"),
                'commission'=>t("Commission"), 
                'order_amount'=>t("Order Amount"),   
                'commission_rate'=>t("Commission Rate"),   
                'commission_amount'=>t("Commission Amount"),   
                'commission_tax'=>t("Commission + Tax"),   
                'service_fee'=>t("Service Fee"),   
                'delivery_fee'=>t("Delivery Fee"),   
                'total'=>t("Total"),   
                'tax_service_fee'=>t("Tax Service Fee"),        
            ];
            $data = [
                'status'=>t($model->payment_status),
                'status_raw'=>$model->payment_status,
                'site'=>$site,
                'items'=>$item,     
                'label'=>$label,
                'order_details'=>$order_details
            ];
            
            $commission_based = isset($order_details['commission_based'])?$order_details['commission_based']:'subtotal';
                                    
            $template = $twig->render('invoice-new.html',$data);            

            // $options = new Options();
			// $options->set('isRemoteEnabled', true);
			// $options->set('defaultFont', 'DejaVu Sans');
			// $dompdf = new Dompdf($options);
			// $arabic = new \ArPHP\I18N\Arabic();		
			// $p = $arabic->arIdentify($template);
			// $htmlOutput = $template;
			// for ($i = count($p)-1; $i >= 0; $i-=2) {
			// 	$utf8ar = $arabic->utf8Glyphs(substr($htmlOutput, $p[$i-1], $p[$i] - $p[$i-1]));
			// 	$htmlOutput   = substr_replace($htmlOutput, $utf8ar, $p[$i-1], $p[$i] - $p[$i-1]);
			// }

            $dompdf = new Dompdf();
            $options = $dompdf->getOptions();
            $options->setChroot(Yii::getPathOfAlias('home_dir'));
            $options->setDefaultFont('Courier');		    
            $dompdf->setOptions($options);

			$dompdf->loadHtml($template);
			$dompdf->setPaper('A4', $commission_based=='subtotal' ? 'portrait' :'landscape');
			$dompdf->render();
			$filename = "Invoice#".$model->invoice_number;	                    
			$dompdf->stream($filename, array('Attachment' => true));	

        } catch (Exception $e) {
            $this->render("//tpl/error",[
                'error'=>[
                    'message'=>t($e->getMessage())
                ]
            ]);        
        }  
    }

    public function actioncancel()
    {
        try {

            $invoice_uuid = Yii::app()->input->get('invoice_uuid'); 
            $model = CMerchantInvoice::getInvoice($invoice_uuid);
            $model->payment_status="cancelled";
            $model->save();
            $this->redirect(array(Yii::app()->controller->id.'/view','invoice_uuid'=>$model->invoice_uuid));
        } catch (Exception $e) {
            $this->render("//tpl/error",[
                'error'=>[
                    'message'=>t($e->getMessage())
                ]
            ]);        
        }  
    }

    public function actiondelete()
    {
        try {
            $invoice_uuid = Yii::app()->input->get('invoice_uuid'); 
            if(empty($invoice_uuid)){
                $invoice_uuid = Yii::app()->input->get('id'); 
            }
            $model = CMerchantInvoice::getInvoice($invoice_uuid);
            $model->delete(); 			
			$this->redirect(array(Yii::app()->controller->id.'/list'));			
        } catch (Exception $e) {
            $this->render("//tpl/error",[
                'error'=>[
                    'message'=>t($e->getMessage())
                ]
            ]);        
        }  
    }

    public function actionupdate(){
        $this->actioncreate(true);
    }

    public function actioncreate($update=false)
    {
        $this->pageTitle = $update==false? t("Add Invoice") :  t("Update Invoice");
		CommonUtility::setMenuActive('.invoice_list','.invoice_list');

        $invoice_uuid = Yii::app()->input->get('invoice_uuid');
        $merchant_selected = [];
        
        $atts = OptionsTools::find(['multicurrency_enabled']);
        $multicurrency_enabled = isset($atts['multicurrency_enabled'])?$atts['multicurrency_enabled']:false;
        $multicurrency_enabled = $multicurrency_enabled==1?true:false;        

        $admin_base_currency = AttributesTools::defaultCurrency();
        $price_list_format = CMulticurrency::getAllCurrency();
        $exchange_rate_merchant_to_admin = 1; 
        $exchange_rate_admin_to_merchant = 1;

        if($update){
			$id = (integer) Yii::app()->input->get('id');	
			$model = AR_invoice::model()->find("invoice_uuid=:invoice_uuid",[
                ':invoice_uuid'=>$invoice_uuid
            ]);				
			if(!$model){				
				$this->render("error");				
				Yii::app()->end();
			}	

            $merchant_selected = CommonUtility::getDataToDropDown("{{merchant}}",'merchant_id','restaurant_name',
			"WHERE merchant_id=".q($model->merchant_id)."");	
            
            $model->invoice_created = date("Y-m-d H:i:s",strtotime($model->invoice_created));
            $model->due_date = date("Y-m-d H:i:s",strtotime($model->due_date));
            $model->date_from = date("Y-m-d H:i:s",strtotime($model->date_from));
            $model->date_to = date("Y-m-d H:i:s",strtotime($model->date_to));
            $model->invoice_total = Price_Formatter::convertToRaw($model->invoice_total,2);
            $model->amount_paid = Price_Formatter::convertToRaw($model->amount_paid,2);            
            
		} else $model=new AR_invoice;	

        if(isset($_POST['AR_invoice'])){
            $model->attributes=$_POST['AR_invoice'];
            try {
                $merchant = CMerchants::get($model->merchant_id);
                $model->restaurant_name = $merchant->restaurant_name;
            } catch (Exception $e) {
                //
            }       
            if($model->validate()){         
                
                if($model->isNewRecord){                    
                    $attr = OptionsTools::find(['merchant_default_currency'],$model->merchant_id);
			        $merchant_base_currency = isset($attr['merchant_default_currency'])? (!empty($attr['merchant_default_currency'])?$attr['merchant_default_currency']:$admin_base_currency) :$admin_base_currency;
                    $model->admin_base_currency = $admin_base_currency;
                    if($multicurrency_enabled){
                        $model->merchant_base_currency = $merchant_base_currency; 
                    } else $model->merchant_base_currency = $admin_base_currency; 
                    if($multicurrency_enabled){
                        $exchange_rate_merchant_to_admin = CMulticurrency::getExchangeRate($merchant_base_currency,$admin_base_currency);
                        $exchange_rate_admin_to_merchant = CMulticurrency::getExchangeRate($admin_base_currency,$merchant_base_currency);
                    }
                }
                $model->exchange_rate_merchant_to_admin = $exchange_rate_merchant_to_admin;
                $model->exchange_rate_admin_to_merchant = $exchange_rate_admin_to_merchant;                
                
                if($model->save()){
					if(!$update){
					   $this->redirect(array('invoice/list'));		
					} else {
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
						$this->refresh();
					}
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
            } else {                
                Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString($model->getErrors()) );
            }
        }
       
        $this->render("invoice_create",array(
		    'model'=>$model,		  
            'merchant_selected'=>$merchant_selected ,
		    'status'=>(array)AttributesTools::StatusManagement('payment'),
            'invoice_terms'=>AttributesTools::InvoiceTerms(),
            'multicurrency_enabled'=>$multicurrency_enabled,
            'price_list_format'=>$price_list_format
		));        
    }

    public function actiondeposit()
	{
		$this->pageTitle=t("Inovice Bank Deposit");
				
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
		    'label'=>t("Invoice#"),
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
				
		$this->render('bank_deposit_list',array(
		  'table_col'=>$table_col,
		  'columns'=>$columns,
		  'order_col'=>1,
          'sortby'=>'desc',		  
		));
	}

    public function actionbank_deposit_view()
	{
		$this->pageTitle = t("Bank Deposit");
		CommonUtility::setMenuActive('.invoice',".invoice_deposit");
        
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
			$this->render("//payment_gateway/bank_deposit",[
				'model'=>$model,
				'status'=>AttributesTools::BankStatusList(),
				'links'=>array(
					t("Bank Deposit")=>array('invoice/deposit'),        
					t("Invoice #{invoice_number}",['{invoice_number}'=>$model->transaction_ref_id]),
                ),
                'multicurrency_enabled'=>$multicurrency_enabled,
                'price_list_format'=>$price_list_format
			]);
		} else $this->render("error");
	}

    public function actionbank_deposit_delete()
    {
        try {
            $deposit_uuid = Yii::app()->input->get('id');             
            $model = AR_bank_deposit::model()->find("deposit_uuid=:deposit_uuid",[
                ':deposit_uuid'=>$deposit_uuid
            ]);
            $model->delete(); 			
			$this->redirect(array(Yii::app()->controller->id.'/deposit'));			
        } catch (Exception $e) {
            $this->render("//tpl/error",[
                'error'=>[
                    'message'=>t($e->getMessage())
                ]
            ]);        
        }  
    }

    public function actionsettings()
    {
        $this->pageTitle = t("Invoice settings");
		CommonUtility::setMenuActive('.invoice',".invoice.settings");

        $model=new AR_option;
		$model->scenario=Yii::app()->controller->action->id;		

        $options = array(
            'invoice_created','invoice_new_upload_deposit','invoice_payment_bank_name','invoice_payment_bank_account_name',
            'invoice_payment_bank_account_number','invoice_payment_bank_custom_template'
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

        $cron_key = CommonUtility::getCronKey();
                
        $cronjobs = [
            1=>[
                'label'=>t("run every end of the day"),
                'value'=>CommonUtility::getHomebaseUrl()."/taskinvoice?terms=1&key=".$cron_key
            ],
            7=>[
                'label'=>t("run every 7 days"),
                'value'=>CommonUtility::getHomebaseUrl()."/taskinvoice?terms=7&key=".$cron_key
            ],
            15=>[
                'label'=>t("run every 15 days"),
                'value'=>CommonUtility::getHomebaseUrl()."/taskinvoice?terms=15&key=".$cron_key
            ],
            30=>[
                'label'=>t("run every 30 days"),
                'value'=>CommonUtility::getHomebaseUrl()."/taskinvoice?terms=30&key=".$cron_key
            ],
        ];

        $this->render("invoice_settings",[
            'model'=>$model,		   
            'template_list'=>$template_list,
            'cronjobs'=>$cronjobs,
            'links'=>array(
                t("Invoice list")=>array('invoice/list'),
                t("Settings")
            )
        ]);
    }
	
} 
/*end class*/