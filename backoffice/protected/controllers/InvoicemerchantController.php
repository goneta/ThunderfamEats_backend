<?php
set_time_limit(0);
require 'dompdf/vendor/autoload.php';
require 'twig/vendor/autoload.php';
use Dompdf\Dompdf;

class InvoicemerchantController extends Commonmerchant
{
		
	public function beforeAction($action)
	{							
		InlineCSTools::registerStatusCSS();	
		return true;
	}

    public function actionlist()
    {
        $this->pageTitle=t("Invoice");

        $table_col = array(            
            'invoice_created'=>array(
              'label'=>t("Date"),
              'width'=>'15%'
            ),
            'invoice_terms'=>array(
              'label'=>t("Description"),
              'width'=>'25%'
            ),
            'payment_status'=>array(
              'label'=>t("Status"),
              'width'=>'15%'
            ),
            'invoice_total '=>array(
              'label'=>t("Amount"),
              'width'=>'15%'
            ),
            'date_created '=>array(
                'label'=>t("Action"),
                'width'=>'10%'
              ),
          );
          $columns = array(            
            array('data'=>'invoice_created'),
            array('data'=>'invoice_terms'),
            array('data'=>'payment_status'),
            array('data'=>'invoice_total'),            
            array('data'=>'date_created','orderable'=>false),
          );		

         $this->render('invoice_list',array(
            'table_col'=>$table_col,
            'columns'=>$columns,
            'order_col'=>1,
            'sortby'=>'desc',                        
        ));
    }    

    public function actionview()
    {
        try {

            CommonUtility::setMenuActive('.invoice','.invoicemerchant_list');
            $invoice_uuid = Yii::app()->input->get('invoice_uuid');		        
            $model = CMerchantInvoice::getInvoice($invoice_uuid);        
            
            try {
                $history = CMerchantInvoice::getHistory($model->invoice_number);
            } catch (Exception $e) {
                $history = [];                
            }
            
            $is_due = false;            
            $today = gmdate("Y-m-d g:i:s a");	
            $date_diff = CommonUtility::dateDifference($model->due_date,$today);
            if(is_array($date_diff) && count($date_diff)>=1 && $model->payment_status !='paid' ){                
                if($date_diff['days']>0){
                    $is_due = true;
                }
            }
            
            CMerchantInvoice::SetViewed($model->invoice_number);

            $payment_info = AttributesTools::getInvoicePaymentInformation();
            
            $model->invoice_total = Price_Formatter::convertToRaw($model->invoice_total,2);
            $model->amount_paid = Price_Formatter::convertToRaw($model->amount_paid,2);            

            $this->render("invoice_view",[
                'model'=>$model,
                'history'=>$history,
                'is_due'=>$is_due,
                'links'=>array(
                    t("Invoice list")=>array(Yii::app()->controller->id.'/list'),
                    t("View")=>array(Yii::app()->controller->id.'/view','invoice_uuid'=>$model->invoice_uuid),
                    "#".$model->invoice_number
                ),	    	
                'payment_info'=>$payment_info
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
            $order_details = CMerchantInvoice::getInvoiceDetails($model->merchant_id,$model->invoice_terms,$model->date_from,$model->date_to);
            
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
            

            $amount_due = $model->invoice_total - $model->amount_paid; 
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
                    '{invoice_total}'=>Price_Formatter::formatNumberNoSymbol($model->invoice_total),
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

            $dompdf = new Dompdf();
            $options = $dompdf->getOptions();
            $options->setChroot(Yii::getPathOfAlias('home_dir'));
            $options->setDefaultFont('Courier');		    
            $dompdf->setOptions($options);
            
            $dompdf->loadHtml($template);            
            $dompdf->setPaper('A4', $commission_based=='subtotal' ? 'portrait' :'landscape');
            $dompdf->render();
            $dompdf->stream();

        } catch (Exception $e) {
            $this->render("//tpl/error",[
                'error'=>[
                    'message'=>t($e->getMessage())
                ]
            ]);        
        }  
    }    

    public function actionuploaddeposit()
    {
        try {

            CommonUtility::setMenuActive('.invoice','.invoicemerchant_list');
            $invoice_uuid = Yii::app()->input->get('invoice_uuid');		        
            $invoice = CMerchantInvoice::getInvoice($invoice_uuid);
            
            $exchange_rate = 1; $exchange_rate_merchant_to_admin=1; $exchange_rate_admin_to_merchant=1;
            $multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
            $multicurrency_enabled = $multicurrency_enabled==1?true:false;   
            
            $admin_base_currency = AttributesTools::defaultCurrency();
            $merchant_default_currency = isset(Yii::app()->params['settings_merchant']['merchant_default_currency'])?Yii::app()->params['settings_merchant']['merchant_default_currency']:$admin_base_currency;            
            $merchant_default_currency = !empty($merchant_default_currency)?$merchant_default_currency:$admin_base_currency;
            if(!$multicurrency_enabled){
                $merchant_default_currency = $admin_base_currency;
            }
            $merchant_id = Yii::app()->merchant->merchant_id;

            $model = AR_bank_deposit::model()->find("deposit_type=:deposit_type AND merchant_id=:merchant_id AND transaction_ref_id=:transaction_ref_id",[
				':deposit_type'=>"invoice",
				':merchant_id'=>$merchant_id,
                ':transaction_ref_id'=>$invoice->invoice_number
			]);
            if(!$model){
                $model = new AR_bank_deposit;
                $model->amount = Price_Formatter::convertToRaw($model->amount,2);                
            } else {
                $model->amount = Price_Formatter::convertToRaw($model->amount,2);                
            }

            if(isset($_POST['AR_bank_deposit'])){
                $model->attributes=$_POST['AR_bank_deposit'];
				$model->proof_image=CUploadedFile::getInstance($model,'proof_image');
                $model->deposit_type = "invoice";
                
                $invoice_amount = Price_Formatter::convertToRaw($invoice->invoice_total,2);
                $amount_input = Price_Formatter::convertToRaw($model->amount,2);                
                if(floatval($invoice_amount)!=floatval($amount_input)){
                    Yii::app()->user->setFlash('error',t("Amount is not exact as invoice amount"));
                } else {
                    if($model->validate()){	
                        $file_uuid = CommonUtility::createUUID("{{bank_deposit}}",'deposit_uuid');
                        $extension = pathinfo($model->proof_image->name, PATHINFO_EXTENSION);
                        $extension = strtolower($extension);									
                        $new_filename = $file_uuid.".".$extension;

                        $model->transaction_ref_id = $invoice->invoice_number;
                        $model->path = "upload/invoice_deposit";
                        $model->deposit_uuid = $file_uuid;			
                        $model->merchant_id = $merchant_id;

                        $path = CommonUtility::uploadDestination('upload/invoice_deposit/').$new_filename;
                        $model->proof_image->saveAs( $path );

                        $model->proof_image = $new_filename;
                        
                        $model->use_currency_code = $merchant_default_currency;
                        $model->base_currency_code = $merchant_default_currency;
                        $model->admin_base_currency = $admin_base_currency;

                        if($multicurrency_enabled){
                            if($merchant_default_currency!=$admin_base_currency){
                                $exchange_rate_merchant_to_admin = CMulticurrency::getExchangeRate($merchant_default_currency,$admin_base_currency);
                                $exchange_rate_admin_to_merchant = CMulticurrency::getExchangeRate($admin_base_currency,$merchant_default_currency);
                            }                        
                        }

                        $model->exchange_rate = $exchange_rate;
                        $model->exchange_rate_merchant_to_admin = $exchange_rate_merchant_to_admin;
                        $model->exchange_rate_admin_to_merchant = $exchange_rate_admin_to_merchant;
                                                
                        if($model->save()){                        
                            Yii::app()->user->setFlash('success',t("You succesfully upload bank deposit. Please wait while we validate your payment."));
                            $this->refresh();
                        } else {						
                            Yii::app()->user->setFlash('error',t(Helper_failed_save));
                        }
                    }
                }
            }

            $this->render("invoice_deposit",[
                'model'=>$model,
                'invoice'=>$invoice,
                'links'=>array(
                    t("Invoice list")=>array(Yii::app()->controller->id.'/list'),
                    t("View")=>array(Yii::app()->controller->id.'/view','invoice_uuid'=>$invoice->invoice_uuid),
                    t("Upload deposit"),
                    "#".$invoice->invoice_number
                ),	    	
            ]);
        } catch (Exception $e) {
            $this->render("//tpl/error",[
                'error'=>[
                    'message'=>t($e->getMessage())
                ]
            ]);        
        }  
    }

}
// end class