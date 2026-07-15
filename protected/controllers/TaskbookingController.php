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
use Psr\Log\Test\DummyTest;

class TaskbookingController extends SiteCommon
{	

	private $runactions_enabled;

	public function beforeAction($action)
	{	
		$key = Yii::app()->input->get("key");			
		if(CRON_KEY===$key){
		   $this->runactions_enabled = isset(Yii::app()->params['settings']['runactions_enabled'])?Yii::app()->params['settings']['runactions_enabled']:false;		
		   return true;
		}
		return false;
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
						$noti->meta_data = [
							"page"=> "booking-view",														
							'reservation_id'=>isset($data['reservation_id'])?$data['reservation_id']:null,
							'reservation_uuid'=>isset($data['reservation_uuid'])?$data['reservation_uuid']:null,
						];										                                         						
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
						$noti->meta_data = [
							"page"=> "booking-view",														
							'reservation_id'=>isset($data['reservation_id'])?$data['reservation_id']:null,
							'reservation_uuid'=>isset($data['reservation_uuid'])?$data['reservation_uuid']:null,
						];										                                         						                      						
					    $push->save();					    
			    	}
			    }
				
			} //in_arra			
		} //foreach
	}    
	
	public function actionafterbooking()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->afterbooking();
			}		
		} else {
			$this->afterbooking();
		}
	}

    public function afterbooking()
    {	
		try {

			$reservation_uuid = trim(Yii::app()->input->get("reservation_uuid"));
			$booking = CBooking::getBookingDetails($reservation_uuid);
			$merchant_id = $booking['merchant_id'];

			try {
				$merchant = CMerchants::get($merchant_id);      
				$merchant_uuid = $merchant->merchant_uuid;      
			} catch (Exception $e) {
				$merchant = [];
				$merchant_uuid = '';
			}

			$options = OptionsTools::find(['booking_tpl_reservation_requested']);            
			$template_id = isset($options['booking_tpl_reservation_requested'])?intval($options['booking_tpl_reservation_requested']):0;                        

			$opts = OptionsTools::find(['merchant_enabled_alert','merchant_email_alert','merchant_mobile_alert'],$merchant_id);
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
				'guest_fullname'=>isset($booking['full_name'])?$booking['full_name']:'', 
				'contact_phone'=>isset($booking['contact_phone'])?$booking['contact_phone']:'', 
				'email_address'=>isset($booking['email_address'])?$booking['email_address']:'', 
				'reservation_id'=>isset($booking['reservation_id'])?$booking['reservation_id']:'', 
				'reservation_uuid'=>isset($booking['reservation_uuid'])?$booking['reservation_uuid']:'', 
				'date_created'=>isset($booking['date_created'])?$booking['date_created']:'', 
				'reservation_datetime'=>isset($booking['reservation_datetime'])?$booking['reservation_datetime']:'', 
				'guest_number'=>isset($booking['guest_number'])?$booking['guest_number']:'', 
				'special_request'=>isset($booking['special_request'])?$booking['special_request']:'', 
				'restaurant_name'=>$merchant?$merchant->restaurant_name:'',
				'manage_reservation_link'=>Yii::app()->createAbsoluteUrl("/backoffice/booking/reservation_overview",['id'=>$reservation_uuid])
			];
			
			if($enabled_alert!=1){
				$phone=''; $email='';
			}
			
			$this->runActions($template_id, $data , array('sms','email','push') , array(
				'phone'=>$phone,
				'email'=>$email,
			),array(
				'channel'=>$merchant_uuid,
				'type'=>'booking',
				'event'=>Yii::app()->params->realtime['notification_event'],
			),array(
				'channel'=>$merchant_uuid,
				'type'=>'booking',			      
			));			

		} catch (Exception $e) {
			echo $e->getMessage();
		}        
    }
	
	public function actionafterupdatebooking()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->afterupdatebooking();
			}		
		} else {
			$this->afterupdatebooking();
		}
	}	

	public function afterupdatebooking()
	{
		try {

			$reservation_uuid = trim(Yii::app()->input->get("reservation_uuid"));
            $booking = CBooking::getBookingDetails($reservation_uuid);
            $merchant_id = $booking['merchant_id'];
			$client_id = $booking['client_id'];

			$merchant = []; 
			try {
				$merchant = CMerchants::get($merchant_id);      				
			 } catch (Exception $e) {
				//				
			 }
			 
			 $customer = []; $client_uuid = '';
			 $phone =''; $email = '';

			 try {
				$customer = ACustomer::get($client_id);
				$client_uuid = $customer->client_uuid;
				$email = $customer->email_address;
				$phone = $customer->phone_prefix.$customer->contact_phone;				
			} catch (Exception $e) {				
			}
			 
			 $options = OptionsTools::find(['booking_tpl_reservation_confirmed','booking_tpl_reservation_canceled',
			   'booking_tpl_reservation_denied','booking_tpl_reservation_finished','booking_tpl_reservation_no_show'
			 ]);            

			 $template_id = '';
			 switch ($booking['status']) {
				case 'confirmed':					
					$template_id = isset($options['booking_tpl_reservation_confirmed'])?intval($options['booking_tpl_reservation_confirmed']):0;                        
					break;
				case 'cancelled':					
					$template_id = isset($options['booking_tpl_reservation_canceled'])?intval($options['booking_tpl_reservation_canceled']):0;                        
					break;
				case 'denied':					
					$template_id = isset($options['booking_tpl_reservation_denied'])?intval($options['booking_tpl_reservation_denied']):0;                        
					break;					
				case 'finished':					
					$template_id = isset($options['booking_tpl_reservation_finished'])?intval($options['booking_tpl_reservation_finished']):0;                        
					break;						
				case 'no_show':					
					$template_id = isset($options['booking_tpl_reservation_no_show'])?intval($options['booking_tpl_reservation_no_show']):0;                        
					break;			
				case 'waitlist':					
					$template_id = isset($options['booking_tpl_reservation_waitlist'])?intval($options['booking_tpl_reservation_waitlist']):0;                        
					break;							
			 }			 

			 $opts = OptionsTools::find(['merchant_enabled_alert','merchant_email_alert','merchant_mobile_alert','booking_reservation_custom_message'],$merchant_id);			 
			 $custom_message = isset($opts['booking_reservation_custom_message'])?$opts['booking_reservation_custom_message']:'';

			 $site = CNotifications::getSiteData();
			 
			 $data = [
				'site'=>$site,
				'logo'=>isset($site['logo'])?$site['logo']:'',
				'facebook'=>isset($site['facebook'])?$site['facebook']:'',
				'twitter'=>isset($site['twitter'])?$site['twitter']:'',
				'instagram'=>isset($site['instagram'])?$site['instagram']:'',
				'whatsapp'=>isset($site['whatsapp'])?$site['whatsapp']:'',
				'youtube'=>isset($site['youtube'])?$site['youtube']:'', 
				'guest_fullname'=>isset($booking['full_name'])?$booking['full_name']:'', 
				'contact_phone'=>isset($booking['contact_phone'])?$booking['contact_phone']:'', 
				'email_address'=>isset($booking['email_address'])?$booking['email_address']:'', 
				'reservation_id'=>isset($booking['reservation_id'])?$booking['reservation_id']:'', 
				'reservation_uuid'=>isset($booking['reservation_uuid'])?$booking['reservation_uuid']:'', 
				'date_created'=>isset($booking['date_created'])?$booking['date_created']:'', 
				'reservation_datetime'=>isset($booking['reservation_datetime'])?$booking['reservation_datetime']:'', 
				'guest_number'=>isset($booking['guest_number'])?$booking['guest_number']:'', 
				'special_request'=>isset($booking['special_request'])?$booking['special_request']:'', 
				'restaurant_name'=>$merchant?$merchant->restaurant_name:'',
				'restaurant_contact_phone'=>$merchant?$merchant->contact_phone:'',
				'restaurant_contact_email'=>$merchant?$merchant->contact_email:'',
				'manage_reservation_link'=>Yii::app()->createAbsoluteUrl("/reservation/details",['id'=>$reservation_uuid]),
				'notes_from_restaurant'=>$custom_message,
				'status'=>$booking['status_pretty1']
			 ];
						 
			 $this->runActions($template_id, $data , array('sms','email','push') , array(
				'phone'=>$phone,
				'email'=>$email,
			),array(
				'channel'=>$client_uuid,
				'type'=>'booking',
				'event'=>Yii::app()->params->realtime['notification_event'],
			),array(
				'channel'=>$client_uuid,
				'type'=>'booking',			      
			));			

		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}

}
// end class