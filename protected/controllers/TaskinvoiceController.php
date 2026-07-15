<?php
set_time_limit(0);
require 'dompdf/vendor/autoload.php';
require 'twig/vendor/autoload.php';
require 'firebase-php/vendor/autoload.php';
use Dompdf\Dompdf;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;

class TaskinvoiceController extends SiteCommon
{	
	
    private $runactions_enabled;

	public function beforeAction($action)
	{			
        $this->runactions_enabled = isset(Yii::app()->params['settings']['runactions_enabled'])?Yii::app()->params['settings']['runactions_enabled']:false;		    
        return true;
	}

    public function actionIndex(){
        $this->actionGenerate();
    }

	public function runActions($template_id=0, $data=array() , $send_type=array() , $send_to=array() , 
	  $noti_channel = array(), $push_channel = array() )
	{
		$templates = CTemplates::get($template_id, array('email','sms','push'), Yii::app()->language );		
		$path = Yii::getPathOfAlias('backend_webroot')."/twig"; 
	    $loader = new \Twig\Loader\FilesystemLoader($path);
	    $twig = new \Twig\Environment($loader, [
		    'cache' => $path."/compilation_cache",
		    'debug'=>true
		]);
		
		$phone = isset($send_to['phone'])?$send_to['phone']:'';
		$email = isset($send_to['email'])?$send_to['email']:'';
		
		/*SETTINGS FOR PUSH WEB NOTIFICATIONS*/
		$settings_app = AR_admin_meta::getMeta(array('realtime_app_enabled','webpush_app_enabled','webpush_provider'));				
		$realtime_app_enabled = isset($settings_app['realtime_app_enabled'])?$settings_app['realtime_app_enabled']['meta_value']:'';
		$webpush_app_enabled = isset($settings_app['webpush_app_enabled'])?$settings_app['webpush_app_enabled']['meta_value']:'';
		$webpush_provider = isset($settings_app['webpush_provider'])?$settings_app['webpush_provider']['meta_value']:'';
				
		$interest = AttributesTools::pushInterest();
		$message_parameters = array();
		if(is_array($data) && count($data)>=1){
			foreach ($data as $data_key=>$data_value) {				
				if(is_array($data[$data_key])){
					//
				} else $message_parameters["{{{$data_key}}}"]=$data_value;
			}
		}
		if(is_array($data['site']) && count($data['site'])>=1){
			foreach ($data['site'] as $data_key=>$data_value) {				
				$message_parameters["{{{$data_key}}}"]=$data_value;
			}
		}
		        
		foreach ($templates as $items) {			
			$email_subject = ''; $template = ''; $email_subject=''; $sms_template=''; $push_template='';$push_title='';
			if(in_array($items['template_type'],$send_type)){
				if($items['template_type']=="email" && $items['enabled_email']==1 ){					
				    $email_subject = isset($items['title'])?$items['title']:'';
	    		    $template = isset($items['content'])?$items['content']:'';
	    		    $twig_template = $twig->createTemplate($template);
	    		    $template = $twig_template->render($data);    			    		
	    		
	    		    $twig_subject = $twig->createTemplate($email_subject);
                    $email_subject = $twig_subject->render($data);                     					
				} else if ($items['template_type']=="sms" && $items['enabled_sms']==1  ) {
					$sms_template = isset($items['content'])?$items['content']:'';
			        $twig_sms = $twig->createTemplate($sms_template);
                    $sms_template = $twig_sms->render($data);                                          
				} else if ($items['template_type']=="push" && $items['enabled_push']==1  ) {
					$push_template = isset($items['content'])?$items['content']:'';			    			    
					$push_title = isset($items['title'])?$items['title']:'';
				}

												            
				if(!empty($email_subject) && !empty($template) && !empty($email)){		                    
		    	    $resp = CommonUtility::sendEmail($email,'',$email_subject,$template);
			    }
			    if(!empty($sms_template) && !empty($phone) ){			    				    				    	                
			    	$resp = CommonUtility::sendSMS($phone,$sms_template);
			    }
			    			    			    
			    if(!empty($push_template)){				    	
			    	if($realtime_app_enabled==1){				    		
			    		$noti = new AR_notifications;							
						$noti->notication_channel = isset($noti_channel['channel'])?$noti_channel['channel']:'';
						$noti->notification_event = isset($noti_channel['event'])?$noti_channel['event']:'';
						$noti->notification_type = isset($noti_channel['type'])?$noti_channel['type']:'';
						$noti->message = $push_template;						
						$noti->message_parameters = json_encode($message_parameters);						
						$noti->image_type = 'icon';
						$noti->image = 'zmdi zmdi-face';                        
						$noti->save();		    							
			    	}
			    	if($webpush_app_enabled==1){
			    		$push_title = t($push_title,$message_parameters);
					    $push_template = t($push_template,$message_parameters);					    
					    $push = new AR_push;    					    
					    $push->push_type = isset($noti_channel['type'])?$noti_channel['type']:'';
					    $push->provider  = $webpush_provider;
					    $push->channel_device_id = isset($noti_channel['channel'])?$noti_channel['channel']:'';
					    $push->platform = "web";
					    $push->title = $push_title;
					    $push->body = $push_template;                        
					    $push->save();					    
			    	}
			    }
				
			} //in_arra			
		} //foreach
	}    
        
    public function actionGenerate()
    {        
        try {
          
            $currentHour = date('H');
            $currentMinute = date('i');
            $currentMinutes = $currentHour * 60 + $currentMinute;
            $elevenPMTimestamp = 23 * 60; // 11:00 PM in minutes
            $elevenFiftyNinePMTimestamp = 23 * 60 + 59; // 11:59 PM in minutes
            if ($elevenPMTimestamp <= $currentMinutes && $currentMinutes <= $elevenFiftyNinePMTimestamp) {
                //echo "The current time is between 11:00 PM and 11:59 PM.";
            } else {
                //echo "The current time is not between 11:00 PM and 11:59 PM.";
                //die();
            }


            $terms = intval(Yii::app()->input->get("terms"));
            $period = CMerchantInvoice::generateRange($terms);            
            $data = CMerchantInvoice::getSummary($terms,$period['start'],$period['end']);            

            $date = date("Y-m-d");
            $due_date = date('Y-m-d 23:59:00',(strtotime ( '+2 day' , strtotime ( $date) ) ));

            $admin_base_currency = AttributesTools::defaultCurrency();            

            foreach ($data as $items) {                
                $exchange_rate_merchant_to_admin = 1; $exchange_rate_admin_to_merchant =1;
                $options_merchant = OptionsTools::find(['merchant_default_currency'],$items['merchant_id']);						                
                $merchant_default_currency = isset($options_merchant['merchant_default_currency'])?$options_merchant['merchant_default_currency']:'';                
                $merchant_default_currency = !empty($merchant_default_currency)?$merchant_default_currency:$admin_base_currency;                                
                
                if($merchant_default_currency!=$admin_base_currency){
                    $exchange_rate_merchant_to_admin = CMulticurrency::getExchangeRate($merchant_default_currency,$admin_base_currency);
                    $exchange_rate_admin_to_merchant = CMulticurrency::getExchangeRate($admin_base_currency,$merchant_default_currency);
                }
                
                $model = new AR_invoice();   
                $model->merchant_id = $items['merchant_id'];
                $model->invoice_total = $items['invoice_total'];
                $model->restaurant_name = $items['restaurant_name'];
                $model->business_address = $items['business_address'];
                $model->contact_phone = $items['contact_phone'];
                $model->contact_email = $items['contact_email'];
                $model->invoice_terms = $terms;
                $model->invoice_created = date("Y-m-d H:i:s");
                $model->due_date = $due_date;
                $model->date_from = $period['start'];
                $model->date_to = $period['end'];   
                $model->merchant_base_currency = $merchant_default_currency;
                $model->admin_base_currency = $admin_base_currency;
                $model->exchange_rate_merchant_to_admin = floatval($exchange_rate_merchant_to_admin);
                $model->exchange_rate_admin_to_merchant = floatval($exchange_rate_admin_to_merchant);
                
                if(!$model->save()){
                    //dump($model->getErrors());
                }
            }
        } catch (Exception $e) {
          //echo $e->getMessage();
        }
    }

	public function actionafterinvoicecreate()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->afterinvoicecreate();
			}		
		} else {
			$this->afterinvoicecreate();
		}
	}

    public function afterinvoicecreate()
    {
        // Yii::import('ext.runactions.components.ERunActions');
        // if (ERunActions::runBackground()) {
            try {

                $path = Yii::getPathOfAlias('backend_webroot')."/twig"; 
                $loader = new \Twig\Loader\FilesystemLoader($path);
                $twig = new \Twig\Environment($loader, [
                    'cache' => $path."/compilation_cache",
                    'debug'=>true
                ]);
                
                $invoice_uuid = trim(Yii::app()->input->get("invoice_uuid"));
                $model = CMerchantInvoice::getInvoice($invoice_uuid);
                                        
                $options = OptionsTools::find(['invoice_created']);            
                $template_id = isset($options['invoice_created'])?intval($options['invoice_created']):0;                        
                
                $opts = OptionsTools::find(['merchant_enabled_alert','merchant_email_alert','merchant_mobile_alert'],$model->merchant_id);

                $enabled_alert = isset($opts['merchant_enabled_alert'])?$opts['merchant_enabled_alert']:false;
                $email = isset($opts['merchant_email_alert'])?$opts['merchant_email_alert']:'';
                $phone = isset($opts['merchant_mobile_alert'])?$opts['merchant_mobile_alert']:'';

                $site = CNotifications::getSiteData();
                $data = [
                    'site'=>$site,
                    'logo'=>isset($site['logo'])?$site['logo']:'',
                    'facebook'=>isset($site['facebook'])?$site['facebook']:'',
                    'twitter'=>isset($site['twitter'])?$site['twitter']:'',
                    'instagram'=>isset($site['instagram'])?$site['instagram']:'',
                    'whatsapp'=>isset($site['whatsapp'])?$site['whatsapp']:'',
                    'youtube'=>isset($site['youtube'])?$site['youtube']:'',
                    'invoice_number'=>$model->invoice_number,
                    'restaurant_name'=>$model->restaurant_name,
                    'business_address'=>$model->business_address,
                    'contact_email'=>$model->contact_email,
                    'invoice_terms'=>$model->invoice_terms,
                    'invoice_total'=>Price_Formatter::formatNumber($model->invoice_total),
                    'amount_paid'=>Price_Formatter::formatNumber($model->amount_paid),
                    'invoice_created'=>Date_Formatter::dateTime($model->invoice_created),
                    'due_date'=>Date_Formatter::date($model->due_date),
                    'date_from'=>Date_Formatter::date($model->date_from),
                    'date_to'=>Date_Formatter::date($model->date_to),
                    'payment_status'=>$model->payment_status,
                ];

                $merchant = CMerchants::get($model->merchant_id);            
                $merchant_uuid = $merchant->merchant_uuid;

                if($enabled_alert!=1){
                    $phone=''; $email='';
                }
                                
                //if($enabled_alert==1){
                    $this->runActions($template_id, $data , array('sms','email','push') , array(
                        'phone'=>$phone,
                        'email'=>$email,
                    ),array(
                        'channel'=>$merchant_uuid,
                        'type'=>'invoice',
                        'event'=>Yii::app()->params->realtime['notification_event'],
                    ),array(
                        'channel'=>$merchant_uuid,
                        'type'=>'invoice',			      
                    ));			
                //}
                        

            } catch (Exception $e) {
                echo $e->getMessage();
            }
        //}
    }

	public function actionafteruploaddeposit()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->afteruploaddeposit();
			}		
		} else {
			$this->afteruploaddeposit();
		}
	}

    public function afteruploaddeposit()
    {
        // Yii::import('ext.runactions.components.ERunActions');
        // if (ERunActions::runBackground()) {
            try {
                
                $invoice_number = trim(Yii::app()->input->get("invoice_number"));
                $model = CMerchantInvoice::getInvoiceByID($invoice_number);            
                
                $options = OptionsTools::find(['admin_enabled_alert','admin_email_alert','admin_mobile_alert','invoice_new_upload_deposit']);                                    
                $template_id = isset($options['invoice_new_upload_deposit'])?intval($options['invoice_new_upload_deposit']):0;                        
                $enabled_alert = isset($options['admin_enabled_alert'])?$options['admin_enabled_alert']:false;
                $email = isset($options['admin_email_alert'])?$options['admin_email_alert']:'';
                $phone = isset($options['admin_mobile_alert'])?$options['admin_mobile_alert']:'';

                $site = CNotifications::getSiteData();
                $data = [
                    'site'=>$site,
                    'logo'=>isset($site['logo'])?$site['logo']:'',
                    'facebook'=>isset($site['facebook'])?$site['facebook']:'',
                    'twitter'=>isset($site['twitter'])?$site['twitter']:'',
                    'instagram'=>isset($site['instagram'])?$site['instagram']:'',
                    'whatsapp'=>isset($site['whatsapp'])?$site['whatsapp']:'',
                    'youtube'=>isset($site['youtube'])?$site['youtube']:'',
                    'invoice_number'=>$model->invoice_number,
                    'restaurant_name'=>$model->restaurant_name,
                    'business_address'=>$model->business_address,
                    'contact_email'=>$model->contact_email,
                    'invoice_terms'=>$model->invoice_terms,
                    'invoice_total'=>Price_Formatter::formatNumber($model->invoice_total),
                    'amount_paid'=>Price_Formatter::formatNumber($model->amount_paid),
                    'invoice_created'=>Date_Formatter::dateTime($model->invoice_created),
                    'due_date'=>Date_Formatter::date($model->due_date),
                    'date_from'=>Date_Formatter::date($model->date_from),
                    'date_to'=>Date_Formatter::date($model->date_to),
                    'payment_status'=>$model->payment_status,
                ];            

                if($enabled_alert!=1){
                    $phone=''; $email='';
                }
                
                //if($enabled_alert==1){
                    $this->runActions($template_id, $data , array('sms','email','push') , array(
                        'phone'=>$phone,
                        'email'=>$email,
                    ),array(
                        'channel'=>Yii::app()->params->realtime['admin_channel'],
                        'type'=>'invoice',
                        'event'=>Yii::app()->params->realtime['notification_event'],
                    ),array(
                        'channel'=>Yii::app()->params->realtime['admin_channel'],
                        'type'=>'invoice',			      
                    ));			
                //}

            } catch (Exception $e) {
                echo $e->getMessage();
            }
        //}
    }

}
// end class