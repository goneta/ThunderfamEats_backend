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
use Kreait\Firebase\Messaging\ApnsConfig;

class TaskController extends SiteCommon
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
	
	public function actionAfterPurchase()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->AfterPurchase();
			}		
		} else {
			$this->AfterPurchase();
		}		
	}

	public function AfterPurchase()
	{	    				
		$order_uuid = Yii::app()->input->get("order_uuid");											
		/*SEND NOTIFICATIONS*/
		try {										
			$this->runOrderActions($order_uuid);							
		} catch (Exception $e) {		    	
			$this->msg[] = t($e->getMessage());
		}							
	}
	
		
	public function actionAfterUpdateStatus()
	{		
		$debug = Yii::app()->input->get("debug");
		if($debug=="true"){
			$this->AfterUpdateStatus();
		} else {
			Yii::import('ext.runactions.components.ERunActions');
			if($this->runactions_enabled){
				if (ERunActions::runBackground()) {
					$this->AfterUpdateStatus();
				}		
			} else {
				$this->AfterUpdateStatus();
			}
		}		
	}

	public function AfterUpdateStatus()
	{	    
											
		$order_uuid = Yii::app()->input->get("order_uuid");	
		
		try {						
			/*SEND NOTIFICATIONS*/
			$this->runOrderActions($order_uuid);
			
		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());			    			    			    
		}										
		Yii::log( json_encode($this->msg) , CLogger::LEVEL_ERROR);		
	}
	
		
	public function actionafterordercancel()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->afterordercancel();
			}		
		} else {
			$this->afterordercancel();
		}		
	}

	public function afterordercancel()
	{		
		// Yii::import('ext.runactions.components.ERunActions');
		// if (ERunActions::runBackground()) {
			
			try {

				$points_enabled = isset(Yii::app()->params['settings']['points_enabled'])?Yii::app()->params['settings']['points_enabled']:false;
		        $points_enabled = $points_enabled==1?true:false;

				$digitalwallet_enabled = isset(Yii::app()->params['settings']['digitalwallet_enabled'])?Yii::app()->params['settings']['digitalwallet_enabled']:false;
		        $digitalwallet_enabled = $digitalwallet_enabled==1?true:false;

				$refund_to_wallet = isset(Yii::app()->params['settings']['digitalwallet_refund_to_wallet'])?Yii::app()->params['settings']['digitalwallet_refund_to_wallet']:false;
		        $refund_to_wallet = $refund_to_wallet==1?true:false;

				$cron_key = CommonUtility::getCronKey();
				$order_uuid = Yii::app()->input->get("order_uuid");		
							
				/*SEND NOTIFICATIONS*/
				$this->runOrderActions($order_uuid);				
			    				
				$all_online = CPayments::getPaymentTypeOnline();				
				$all_offline = CPayments::getPaymentTypeOnline(0);
				$order = COrders::get($order_uuid);	
				
				// POINTS REVERSAL
				try {
					if($points_enabled){
						CPoints::reversal($order_uuid);
					}					
				} catch (Exception $e) {
					//
				}

				// DIGITAL WALLET
				if($digitalwallet_enabled){
					$all_online[CDigitalWallet::transactionName()] =[
						'payment_code'=>CDigitalWallet::transactionName(),
						'payment_name'=>CDigitalWallet::paymentName(),
					];
				}

				
				if(array_key_exists($order->payment_code,(array)$all_offline)){
					if($order->amount_due>0 && $order->wallet_amount>0){
						$refund_amount = $order->wallet_amount;							
						$trans = new AR_ordernew_transaction;
						$trans->order_id = intval($order->order_id);
						$trans->merchant_id = intval($order->merchant_id);
						$trans->client_id = intval($order->client_id);
						$trans->payment_code = CDigitalWallet::transactionName();
						$trans->transaction_name = 'refund';
						$trans->transaction_type = 'debit';
						$trans->transaction_description = "Full refund";
						$trans->trans_amount = floatval($refund_amount);
						$trans->currency_code = $order->use_currency_code;
						$trans->to_currency_code = $order->base_currency_code;
						$trans->exchange_rate = $order->exchange_rate;
						$trans->admin_base_currency = $order->admin_base_currency;
						$trans->exchange_rate_merchant_to_admin = $order->exchange_rate_merchant_to_admin;
						$trans->exchange_rate_admin_to_merchant = $order->exchange_rate_admin_to_merchant;						
						if($trans->save()){						   						
							$get_params = array( 
							   'order_uuid'=> $order_uuid,
							   'transaction_id'=>$trans->transaction_id,
							   'key'=>$cron_key,
							   'language'=>Yii::app()->language
							);						  							
							CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/processrefund?".http_build_query($get_params) );
						 }							
					}
				}				
				
				if(array_key_exists($order->payment_code,(array)$all_online)){					
					$model = AR_ordernew_transaction::model()->find("order_id=:order_id AND 
					transaction_name=:transaction_name AND status=:status",array(
					  ':order_id'=>intval($order->order_id),
					  ':transaction_name'=>'payment',
					  ':status'=>'paid'
					));					
					if($model){					
												
						$refund_amount = $order->total_original;		
						
						// REFUND TO DIGITAL WALLET IF PARTIAL PAYMENT
						if($digitalwallet_enabled && $order->amount_due>0 && $order->wallet_amount>0){
							$order->payment_code = CDigitalWallet::transactionName();
						} else if($digitalwallet_enabled && $refund_to_wallet){
							$order->payment_code = CDigitalWallet::transactionName();
						}
						
					    $trans = new AR_ordernew_transaction;
						$trans->order_id = intval($order->order_id);
						$trans->merchant_id = intval($order->merchant_id);
						$trans->client_id = intval($order->client_id);
						$trans->payment_code = $order->payment_code;
						$trans->transaction_name = 'refund';
						$trans->transaction_type = 'debit';
						$trans->transaction_description = "Full refund";
						$trans->trans_amount = floatval($refund_amount);
						$trans->currency_code = $order->use_currency_code;
						$trans->to_currency_code = $order->base_currency_code;
						$trans->exchange_rate = $order->exchange_rate;
						$trans->admin_base_currency = $order->admin_base_currency;
						$trans->exchange_rate_merchant_to_admin = $order->exchange_rate_merchant_to_admin;
						$trans->exchange_rate_admin_to_merchant = $order->exchange_rate_admin_to_merchant;						
									   
						if($trans->save()){						   						
						   $get_params = array( 
							  'order_uuid'=> $order_uuid,
							  'transaction_id'=>$trans->transaction_id,
							  'key'=>$cron_key,
							  'language'=>Yii::app()->language
						   );						  						   
						   CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/processrefund?".http_build_query($get_params) );
						}							
					} else {					
						// CHECK CAPTURE
						$all_split = CPayments::getPaymentTypeCapture();												
						if(array_key_exists($order->payment_code,(array)$all_split)){
							$model = AR_ordernew_transaction::model()->find("order_id=:order_id AND 
							transaction_name=:transaction_name",array(
							':order_id'=>intval($order->order_id),
							':transaction_name'=>'payment',							
							));								
							if($model->status=="unpaid"){
								$get_params = array( 
									'order_uuid'=> $order_uuid,
									'transaction_id'=>$model->transaction_id,
									'key'=>$cron_key,
								 );								 
								 CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/processcancelcapture?".http_build_query($get_params) );
							} elseif ( $model->status=="paid"){
								//
							}
						}
					}
				}				
				
			} catch (Exception $e) {
			    $this->msg[] = t($e->getMessage());			    			   
			}								
			Yii::log( json_encode($this->msg) , CLogger::LEVEL_ERROR);
		//}
	}
	
	public function actionafterdelayorder()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->afterdelayorder();
			}		
		} else {
			$this->afterdelayorder();
		}
	}

	public function afterdelayorder()
	{
		// Yii::import('ext.runactions.components.ERunActions');
		// if (ERunActions::runBackground()) {
			try {
				
				$order_uuid = Yii::app()->input->get("order_uuid");				
				if($template_id = AR_admin_meta::getValue('delayed_template_id')){
					$template_id = $template_id['meta_value'];												
					$this->runOrderSingleAction($order_uuid,$template_id);
				}				
				
			} catch (Exception $e) {
			    $this->msg[] = t($e->getMessage());			    			   
			}								
			Yii::log( json_encode($this->msg) , CLogger::LEVEL_ERROR);
		//}
	}
	
	private function runOrderActions($order_uuid='' , $overwrite_status='' , $additional_data=array())
	{
		try {
			    			
			CCacheData::add();
			
	    	$data = CNotifications::getOrder($order_uuid , array(
	    	 'merchant_info','items','summary','order_info','customer','logo','total'
	    	));				    	
	    	$status = isset($data['order_info']['status'])?$data['order_info']['status']:'';		    	

			if(!empty($overwrite_status)){
				$status = $overwrite_status;
			}
			
	    	$actions = CNotifications::getStatusActions($status);		    	
	    	$templates = CTemplates::getMany($actions['template_ids'], array('email','sms','push'), Yii::app()->language );	    				
	    		    	
	    	$path = Yii::getPathOfAlias('backend_webroot')."/twig"; 
		    $loader = new \Twig\Loader\FilesystemLoader($path);
		    $twig = new \Twig\Environment($loader, [
			    'cache' => $path."/compilation_cache",
			    'debug'=>true
			]);
													
			$order_info = isset($data['order_info'])?$data['order_info']:'';				
			$merchant_id = isset($order_info['merchant_id'])?$order_info['merchant_id']:'';				
			$request_from = isset($order_info['request_from'])?$order_info['request_from']:'';	
			$merchant_uuid = $data['merchant']['merchant_uuid'];
			$customer_name = $order_info['customer_name']?$order_info['customer_name']:'';
			$email_address = $order_info['contact_email']?$order_info['contact_email']:'';
			$contact_phone = $order_info['contact_number']?$order_info['contact_number']:'';
			$client_id = $order_info['client_id']?$order_info['client_id']:'';
			$merchant = isset($data['merchant'])?$data['merchant']:'';					
			$merchant_name = isset($merchant['restaurant_name'])?$merchant['restaurant_name']:'';	

					
			$message_parameters = array(); $sms_vars = [];
			if(is_array($data['order_info']) && count($data['order_info'])>=1){
				foreach ($data['order_info'] as $data_key=>$data_value) {
					if($data_key=="service_code"){
						$data_key='order_type';
					}
					$message_parameters["{{{$data_key}}}"]=$data_value;					
				}
				$sms_vars['order_id'] =  isset($data['order_info'])?  ( isset($data['order_info']['order_id'])?$data['order_info']['order_id']:'' )  : '';
				$sms_vars['customer_name'] = isset($data['order_info'])?  ( isset($data['order_info']['customer_name'])?$data['order_info']['customer_name']:'' )  : '';
				$sms_vars['payment_name'] = isset($data['order_info'])?  ( isset($data['order_info']['payment_name'])?$data['order_info']['payment_name']:'' )  : '';				
				$sms_vars['total'] = isset($data['order_info'])?  ( isset($data['order_info']['pretty_total'])?$data['order_info']['pretty_total']:'' )  : '';
				$sms_vars['order_type'] = isset($data['order_info'])?  ( isset($data['order_info']['order_type'])?$data['order_info']['order_type']:'' )  : '';				
				$sms_vars['place_on'] = isset($data['order_info'])?  ( isset($data['order_info']['place_on'])?$data['order_info']['place_on']:'' )  : '';				
			}
			if(is_array($data['merchant']) && count($data['merchant'])>=1){
				foreach ($data['merchant'] as $data_key=>$data_value) {				
					$message_parameters["{{{$data_key}}}"]=$data_value;					
				}				
				$sms_vars['restaurant_name'] = isset($data['merchant'])? ( isset($data['merchant']['restaurant_name'])?$data['merchant']['restaurant_name']:''  )  :'';
			}

			if(is_array($additional_data) && count($additional_data)>=1){
				foreach ($additional_data as $data_key=>$data_value) {				
					$message_parameters["{{{$data_key}}}"]=$data_value;
					$sms_vars[$data_key] = $data_value;
				}
			}
						
			/*SETTINGS FOR PUSH WEB NOTIFICATIONS*/
			$settings_pushweb = AR_admin_meta::getMeta(array('webpush_app_enabled','webpush_provider','pusher_instance_id','onesignal_app_id'
			));							
			$webpush_app_enabled = isset($settings_pushweb['webpush_app_enabled'])?$settings_pushweb['webpush_app_enabled']['meta_value']:'';						
			$webpush_provider = isset($settings_pushweb['webpush_provider'])?$settings_pushweb['webpush_provider']['meta_value']:'';
			
			$interest = AttributesTools::pushInterest();											
			
	    	foreach ($actions['data'] as $val) {					
	    		$action_value = $val['action_value'];		    		
	    		if(isset($templates[$action_value])){					
	    			
	    			$email_subject = ''; $template = ''; $email_subject=''; $sms_template=''; $push_template=''; $push_title='';
					$sms_template_id = '';
	    								
	    			foreach ($templates[$action_value] as $items) {
	    				if($items['template_type']=="email" && $items['enabled_email']==1 ){
	    							    				
	    					$email_subject = isset($items['title'])?$items['title']:'';
    			    		$template = isset($items['content'])?$items['content']:'';
    			    		$twig_template = $twig->createTemplate($template);
    			    		$template = $twig_template->render($data);    			    		
    			    		
    			    		$twig_subject = $twig->createTemplate($email_subject);
                            $email_subject = $twig_subject->render($data); 
                            		    					                                                                        			    			    		                                                       
	    				} else if ($items['template_type']=="sms" && $items['enabled_sms']==1  ) {
	    					$sms_template = isset($items['content'])?$items['content']:'';
							$sms_template_id = isset($items['sms_template_id'])?$items['sms_template_id']:'';								
		    			    $twig_sms = $twig->createTemplate($sms_template);
                            $sms_template = $twig_sms->render($data);                                          
	    				} else if ($items['template_type']=="push" && $items['enabled_push']==1  ) {
	    					$push_template = isset($items['content'])?$items['content']:'';			    			    
	    					$push_title = isset($items['title'])?$items['title']:'';
	    				}
	    			}
	    					    		
	    										
		    		switch ($val['action_type']) {
		    			case "notification_to_customer":				    			    							

		    			    if(!empty($email_subject) && !empty($template)){
		    			    	$resp = CommonUtility::sendEmail($email_address,$customer_name,$email_subject,$template);
		    			    }
		    			    if(!empty($sms_template)){								
		    			    	$resp = CommonUtility::sendSMS($contact_phone,$sms_template,$client_id,$merchant_id,$customer_name,$sms_template_id,$sms_vars);
		    			    }
		    			    	
							$client_uuid = '';
							if(isset($data['customer']['client_uuid'])){
								$client_uuid = $data['customer']['client_uuid'];
							}		    			    
		    
						    if(!empty($push_template)){		    	
						    	$noti = new AR_notifications;    							
								$noti->notication_channel = $client_uuid;
								$noti->notification_event = Yii::app()->params->realtime['notification_event'] ;
								$noti->notification_type = $interest['order_update'];
								$noti->message = $push_template;				
								$noti->message_parameters = json_encode($message_parameters);
								if(!empty($data['merchant']['logo'])){
									$noti->image_type = 'image';
									$noti->image = $data['merchant']['logo'];
									$noti->image_path = $data['merchant']['path'];
								} else {
									$noti->image_type = 'icon';
									$noti->image = 'zmdi zmdi-shopping-basket';
								}
								$noti->meta_data = [
									"page"=> "order-view",
									'order_uuid'=>$order_uuid,
									'status'=>isset($order_info['status'])?$order_info['status']:'',
									'request_from'=>isset($order_info['request_from'])?$order_info['request_from']:'',
								];
								$noti->save();
						    }
							
							// PWA SEND PUSH
							if(!empty($push_template)){	
								$model_device = AR_device::model()->find("user_type=:user_type AND user_id=:user_id AND platform=:platform",[
									":user_type"=>'client',
									":user_id"=>$client_id,
									":platform"=>"pwa"
								]);
								if($model_device){
									$push = new AR_push;    		
									$push->merchant_id = $request_from=='singleapp' ? $merchant_id : 0;				    
									$push->push_type = $interest['order_update'];									
									$push->provider  = 'firebase';
									$push->channel_device_id = $model_device->device_token;
									$push->platform = "pwa";
									$push->title = t($push_title,$message_parameters);
									$push->body = t($push_template,$message_parameters);
									$push->save();
								}																
							}
																				
							// SEND PUSH NOTIFICATIONS TO DEVICE														
							if(!empty($push_template)){								
								try {
									$push = new AR_push;
									$push->merchant_id = $request_from=='singleapp' ? $merchant_id : 0;
									$push->push_type = 'broadcast';
									$push->provider  = 'firebase';
									$push->channel_device_id = $client_uuid;
									$push->platform = 'android';
									$push->title = t($push_title,$message_parameters);
									$push->body = t($push_template,$message_parameters);																				
									if(isset($data['merchant'])){
										if(!empty($data['merchant']['logo'])){
											$push->image = $data['merchant']['logo'];
											$push->path = $data['merchant']['path'];
										}							
									}				
									$push->save();

									$push = new AR_push;
									$push->merchant_id = $request_from=='singleapp' ? $merchant_id : 0;
									$push->push_type = 'broadcast';
									$push->provider  = 'firebase';
									$push->channel_device_id = $client_uuid;
									$push->platform = 'ios';
									$push->title = t($push_title,$message_parameters);
									$push->body = t($push_template,$message_parameters);																				
									if(isset($data['merchant'])){
										if(!empty($data['merchant']['logo'])){
											$push->image = $data['merchant']['logo'];
											$push->path = $data['merchant']['path'];
										}							
									}				
									$push->save();
								} catch (Exception $e) {
									//dump($e->getMessage());
								}							
							}			
							
							// WEB PUSH
						    if(!empty($push_template) && $webpush_app_enabled){							    
							    $push = new AR_push;    						    
							    $push->push_type = $interest['order_update'];
							    $push->provider  = $webpush_provider;
							    $push->channel_device_id = $client_uuid;
							    $push->platform = "web";
							    $push->title = t($push_title,$message_parameters);
							    $push->body = t($push_template,$message_parameters);
							    $push->save();
							} 
							
		    				break;
		    		
		    			case "notification_to_merchant":			    				
		    				$find = array('merchant_enabled_alert','merchant_email_alert','merchant_mobile_alert');
		    				if($merchant_set = OptionsTools::find($find,$merchant_id)){				    					
		    					$merchant_email = isset($merchant_set['merchant_email_alert'])?$merchant_set['merchant_email_alert']:'';
		    					$merchant_mobile = isset($merchant_set['merchant_mobile_alert'])?$merchant_set['merchant_mobile_alert']:'';
		    					if($merchant_set['merchant_enabled_alert']==1){			    						
		    						if(!empty($email_subject) && !empty($template)){
		    							$resp = CommonUtility::sendEmail($merchant_email,$merchant_name,$email_subject,$template);
		    						}			    						
		    						if(!empty($sms_template)){
		    							$resp = CommonUtility::sendSMS($merchant_mobile,$sms_template,0,$merchant_id,$merchant_name,$sms_template_id,$sms_vars);
		    						}
		    					}
		    				}	
		    						    				
		    				if(!empty($push_template)){		    					
		    					$noti = new AR_notifications;    							
    							$noti->notication_channel = $merchant_uuid;
    							$noti->notification_event = Yii::app()->params->realtime['notification_event'] ;
    							$noti->notification_type = $interest['order_update'];
    							$noti->message = $push_template;
    							$noti->message_parameters = json_encode($message_parameters);
    							if(!empty($data['merchant']['logo'])){
    								$noti->image_type = 'image';
    								$noti->image = $data['merchant']['logo'];
	    							$noti->image_path = $data['merchant']['path'];
    							} else {
	    							$noti->image_type = 'icon';
	    							$noti->image = 'zmdi zmdi-shopping-basket';
    							}
								$noti->meta_data = [
									"page"=> "order-view",
									'order_uuid'=>$order_uuid,
									'status'=>isset($order_info['status'])?$order_info['status']:'',
									'request_from'=>isset($order_info['request_from'])?$order_info['request_from']:'',
								];
    							$noti->save();								
		    				}
		    				
		    				if(!empty($push_template) && $webpush_app_enabled){
		    					$push_title = t($push_title,$message_parameters);
    						    $push_template = t($push_template,$message_parameters);    						    
    						    $push = new AR_push;    						    
    						    $push->push_type = $interest['order_update'];
    						    $push->provider  = $webpush_provider;
    						    $push->channel_device_id = $merchant_uuid;
    						    $push->platform = "web";
    						    $push->title = $push_title;
    						    $push->body = $push_template;
    						    $push->save();
		    				}						
							
							// SEND PUSH NOTIFICATIONS TO DEVICE														
							if(!empty($push_template)){								
								try {
									$push = new AR_push;
									$push->push_type = 'broadcast';
									$push->provider  = 'firebase';																
									$push->channel_device_id = $merchant_uuid;
									$push->platform = 'android';
									$push->title = t($push_title,$message_parameters);
									$push->body = t($push_template,$message_parameters);
									if(isset($data['merchant'])){
										if(!empty($data['merchant']['logo'])){
											$push->image = $data['merchant']['logo'];
											$push->path = $data['merchant']['path'];
										}							
									}											
									$push->save();

									$push = new AR_push;
									$push->push_type = 'broadcast';
									$push->provider  = 'firebase';
									$push->channel_device_id = $merchant_uuid;
									$push->platform = 'ios';
									$push->title = t($push_title,$message_parameters);
									$push->body = t($push_template,$message_parameters);
									if(isset($data['merchant'])){
										if(!empty($data['merchant']['logo'])){
											$push->image = $data['merchant']['logo'];
											$push->path = $data['merchant']['path'];
										}							
									}											
									$push->save();
								} catch (Exception $e) {
									//dump($e->getMessage());
								}							
							}			
															    				
		    				break;
		    				
		    			case "notification_to_admin":
		    				$find = array('admin_enabled_alert','admin_email_alert','admin_mobile_alert');
		    				if($admin_set = OptionsTools::find($find,0)){			    					
		    					$admin_email = isset($admin_set['admin_email_alert'])?$admin_set['admin_email_alert']:'';
		    					$admin_mobile = isset($admin_set['admin_mobile_alert'])?$admin_set['admin_mobile_alert']:'';
		    					$admin_enabled = isset($admin_set['admin_enabled_alert'])?$admin_set['admin_enabled_alert']:'';
		    					if($admin_enabled==1){
		    						if(!empty($email_subject) && !empty($template)){
		    							$resp = CommonUtility::sendEmail($admin_email,"admin",$email_subject,$template);			    							
		    						}
		    						if(!empty($sms_template)){			    							
		    							$resp = CommonUtility::sendSMS($admin_mobile,$sms_template,0,0,'admin',$sms_template_id,$sms_vars);
		    						}		    						
		    					}
		    				}
		    								    					    				
		    				if(!empty($push_template)){				    					
    							$noti = new AR_notifications;    							
    							$noti->notication_channel = Yii::app()->params->realtime['admin_channel'] ;
    							$noti->notification_event = Yii::app()->params->realtime['notification_event'] ;
    							$noti->notification_type = $interest['order_update'];
    							$noti->message = $push_template;
    							$noti->message_parameters = json_encode($message_parameters);
    							if(!empty($data['merchant']['logo'])){
    								$noti->image_type = 'image';
    								$noti->image = $data['merchant']['logo'];
	    							$noti->image_path = $data['merchant']['path'];
    							} else {
	    							$noti->image_type = 'icon';
	    							$noti->image = 'zmdi zmdi-shopping-basket';
    							}
								$noti->meta_data = [
									"page"=> "order-view",
									'order_uuid'=>$order_uuid,
									'status'=>isset($order_info['status'])?$order_info['status']:'',
									'request_from'=>isset($order_info['request_from'])?$order_info['request_from']:'',
								];
    							$noti->save();
    						}
    						    						
    						if(!empty($push_template) && $webpush_app_enabled){
    						    $push_title = t($push_title,$message_parameters);
    						    $push_template = t($push_template,$message_parameters);
    						    $push = new AR_push;    						    
    						    $push->push_type = $interest['order_update'];
    						    $push->provider  = $webpush_provider;
    						    $push->channel_device_id = $interest['order_update'];
    						    $push->platform = "web";
    						    $push->title = $push_title;
    						    $push->body = $push_template;
    						    $push->save();
    						} 
    						
		    				break;

					    case "notification_to_driver":
							try {
								
								$driver_data = CDriver::getDriver($order_info['driver_id']);												
								$driver_phone = $driver_data->phone_prefix.$driver_data->phone;

								if($driver_data->notification==1){
									if(!empty($email_subject) && !empty($template)){										
										$resp = CommonUtility::sendEmail($driver_data->email,$driver_data->first_name,$email_subject,$template);
									}			
									if(!empty($sms_template)){
										$resp = CommonUtility::sendSMS($driver_phone,$sms_template,$driver_data->driver_id,0,$driver_data->first_name,$sms_template_id,$sms_vars);
									}    						

									if(!empty($push_template)){
										$noti = new AR_notifications;    							
										$noti->notication_channel = $driver_data->driver_uuid;
										$noti->notification_event = Yii::app()->params->realtime['notification_event'] ;
										$noti->notification_type = $interest['order_update'];
										$noti->message = $push_template;
										$noti->message_parameters = json_encode($message_parameters);
										$noti->image_type = 'icon';
		                                $noti->image = 'zmdi zmdi zmdi-car';
										$noti->meta_data = [
											"page"=> "order-view",
											'order_uuid'=>$order_uuid,
											'status'=>isset($order_info['status'])?$order_info['status']:'',
											'request_from'=>isset($order_info['request_from'])?$order_info['request_from']:'',
										];										
										$noti->save();
									}
									
									// SEND PUSH NOTIFICATIONS TO DEVICE
									if(!empty($push_template)){										
										$push = new AR_push;										
										$push->push_type = 'broadcast';
										$push->provider  = 'firebase';
										$push->channel_device_id = $driver_data->driver_uuid;
										$push->platform = 'android';
										$push->title = t($push_title,$message_parameters);
										$push->body = t($push_template,$message_parameters);												
										$push->save();

										$push = new AR_push;										
										$push->push_type = 'broadcast';
										$push->provider  = 'firebase';
										$push->channel_device_id = $driver_data->driver_uuid;
										$push->platform = 'ios';
										$push->title = t($push_title,$message_parameters);
										$push->body = t($push_template,$message_parameters);												
										$push->save();
									}

								}
							} catch (Exception $e) {
								$this->msg[] = $e->getMessage();
							}
							break;
		    		}
		    		//end switch
	    		}
	    	} //foreach
	    	
	    } catch (Exception $e) {		    	
		    $this->msg[] = t($e->getMessage());		    
		    dump($this->msg);
		    Yii::log( json_encode($this->msg) , CLogger::LEVEL_ERROR);
		}							
	}

	private function runOrderSingleAction($order_uuid='', $template_id = '' , $additional_data = array() )
	{
		$templates = CTemplates::get($template_id, array('email','sms','push'), Yii::app()->language );		
					
		$data = CNotifications::getOrder($order_uuid , array(
    	 'merchant_info','items','summary','order_info','customer','logo','total'
    	));		

    	$data['additional_data']=$additional_data;
    				    	
    	$path = Yii::getPathOfAlias('backend_webroot')."/twig"; 
	    $loader = new \Twig\Loader\FilesystemLoader($path);
	    $twig = new \Twig\Environment($loader, [
		    'cache' => $path."/compilation_cache",
		    'debug'=>true
		]);
		
		$order_info = isset($data['order_info'])?$data['order_info']:'';
		$merchant_id = isset($order_info['merchant_id'])?$order_info['merchant_id']:'';				
		$request_from = isset($order_info['request_from'])?$order_info['request_from']:'';
		$customer_name = $order_info['customer_name']?$order_info['customer_name']:'';
		$email_address = $order_info['contact_email']?$order_info['contact_email']:'';
		$contact_phone = $order_info['contact_number']?$order_info['contact_number']:'';
		$client_id = $order_info['client_id']?$order_info['client_id']:'';
		$merchant = isset($data['merchant'])?$data['merchant']:'';					
		$merchant_name = isset($merchant['restaurant_name'])?$merchant['restaurant_name']:'';	

		$message_parameters = array(); $sms_vars = [];
		if(is_array($data['order_info']) && count($data['order_info'])>=1){
			foreach ($data['order_info'] as $data_key=>$data_value) {
				if($data_key=="service_code"){
					$data_key='order_type';
				}
				$message_parameters["{{{$data_key}}}"]=$data_value;				
			}
			$sms_vars['order_id'] =  isset($data['order_info'])?  ( isset($data['order_info']['order_id'])?$data['order_info']['order_id']:'' )  : '';
			$sms_vars['customer_name'] = isset($data['order_info'])?  ( isset($data['order_info']['customer_name'])?$data['order_info']['customer_name']:'' )  : '';
			$sms_vars['payment_name'] = isset($data['order_info'])?  ( isset($data['order_info']['payment_name'])?$data['order_info']['payment_name']:'' )  : '';				
			$sms_vars['total'] = isset($data['order_info'])?  ( isset($data['order_info']['pretty_total'])?$data['order_info']['pretty_total']:'' )  : '';
			$sms_vars['order_type'] = isset($data['order_info'])?  ( isset($data['order_info']['order_type'])?$data['order_info']['order_type']:'' )  : '';				
			$sms_vars['place_on'] = isset($data['order_info'])?  ( isset($data['order_info']['place_on'])?$data['order_info']['place_on']:'' )  : '';				
		}
		if(is_array($data['merchant']) && count($data['merchant'])>=1){
			foreach ($data['merchant'] as $data_key=>$data_value) {				
				$message_parameters["{{{$data_key}}}"]=$data_value;
			}
			$sms_vars['restaurant_name'] = isset($data['merchant'])? ( isset($data['merchant']['restaurant_name'])?$data['merchant']['restaurant_name']:''  )  :'';
		}
		
		if(is_array($additional_data) && count($additional_data)>=1){
			foreach ($additional_data as $data_key=>$data_value) {				
				$message_parameters["{{{$data_key}}}"]=$data_value;
				$sms_vars[$data_key] = $data_value;
			}			
		}
						
		/*SETTINGS FOR PUSH WEB NOTIFICATIONS*/
		$settings_pushweb = AR_admin_meta::getMeta(array('webpush_app_enabled','webpush_provider','pusher_instance_id','onesignal_app_id'
		));							
		$webpush_app_enabled = isset($settings_pushweb['webpush_app_enabled'])?$settings_pushweb['webpush_app_enabled']['meta_value']:'';						
		$webpush_provider = isset($settings_pushweb['webpush_provider'])?$settings_pushweb['webpush_provider']['meta_value']:'';	
		
		$interest = AttributesTools::pushInterest();
				
		foreach ($templates as $items) {
			$email_subject = ''; $template = ''; $email_subject=''; $sms_template='';
			$push_template = ''; $push_title=''; $sms_template_id = ''; 
						
			if($items['template_type']=="email" && $items['enabled_email']==1 ){
							    				
				$email_subject = isset($items['title'])?$items['title']:'';
	    		$template = isset($items['content'])?$items['content']:'';
	    		$twig_template = $twig->createTemplate($template);
	    		$template = $twig_template->render($data);    			    		
	    		
	    		$twig_subject = $twig->createTemplate($email_subject);
                $email_subject = $twig_subject->render($data);                 
                		    					                                                                        			    			    		                                                       
			} else if ($items['template_type']=="sms" && $items['enabled_sms']==1  ) {
				$sms_template = isset($items['content'])?$items['content']:'';
				$sms_template_id = isset($items['sms_template_id'])?$items['sms_template_id']:'';
			    $twig_sms = $twig->createTemplate($sms_template);			    
                $sms_template = $twig_sms->render($data);                                          
			} else if ($items['template_type']=="push" && $items['enabled_push']==1  ) {				
				$push_template = isset($items['content'])?$items['content']:'';			    			    
				$push_title = isset($items['title'])?$items['title']:'';
			}
									
			if(!empty($email_subject) && !empty($template)){					
		    	$resp = CommonUtility::sendEmail($email_address,$customer_name,$email_subject,$template);
		    }
		    if(!empty($sms_template)){		    	
		    	$resp = CommonUtility::sendSMS($contact_phone,$sms_template,$client_id,$merchant_id,$customer_name,$sms_template_id,$sms_vars);
		    }
		    		    
		    $client_uuid = $data['customer']['client_uuid'];
		    		    
		    if(!empty($push_template)){				    	
		    	$noti = new AR_notifications;    							
				$noti->notication_channel = $client_uuid;
				$noti->notification_event = Yii::app()->params->realtime['notification_event'] ;
				$noti->notification_type = $interest['order_update'];
				$noti->message = $push_template;				
				$noti->message_parameters = json_encode($message_parameters);
				if(!empty($data['merchant']['logo'])){
					$noti->image_type = 'image';
					$noti->image = $data['merchant']['logo'];
					$noti->image_path = $data['merchant']['path'];
				} else {
					$noti->image_type = 'icon';
					$noti->image = 'zmdi zmdi-shopping-basket';
				}
				$noti->save();
		    }
			
			// WEB PUSH
		    if(!empty($push_template) && $webpush_app_enabled){			    
			    $push = new AR_push;    						    
			    $push->push_type = $interest['order_update'];
			    $push->provider  = $webpush_provider;
			    $push->channel_device_id = $client_uuid;
			    $push->platform = "web";
			    $push->title = t($push_title,$message_parameters);
			    $push->body = t($push_template,$message_parameters);
			    $push->save();
			} 

			// SEND PUSH NOTIFICATIONS TO DEVICE
			if(!empty($push_template)){
				try {
					$push = new AR_push;
					$push->merchant_id = $request_from=='singleapp' ? $merchant_id : 0;
					$push->push_type = 'broadcast';
					$push->provider  = 'firebase';
					$push->channel_device_id = $client_uuid;
					$push->platform = 'android';
					$push->title = t($push_title,$message_parameters);
					$push->body = t($push_template,$message_parameters);
					$push->save();

					$push = new AR_push;
					$push->merchant_id = $request_from=='singleapp' ? $merchant_id : 0;
					$push->push_type = 'broadcast';
					$push->provider  = 'firebase';
					$push->channel_device_id = $client_uuid;
					$push->platform = 'ios';
					$push->title = t($push_title,$message_parameters);
					$push->body = t($push_template,$message_parameters);
					$push->save();
				} catch (Exception $e) {

				}
			}
		    
		} //end foreach
	}
	
	public function actionupdatesummary()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->updatesummary();
			}		
		} else {
			$this->updatesummary();
		}
	}

	public function updatesummary()
	{
		// Yii::import('ext.runactions.components.ERunActions');
		// if (ERunActions::runBackground()) {
			try {
				
			   sleep(2);
			   $order_uuid = Yii::app()->input->get("order_uuid");				   
			   COrders::updateSummary($order_uuid);			   
			   
			} catch (Exception $e) {
			    $this->msg[] = t($e->getMessage());			    			    			    
			}	
			Yii::log( json_encode($this->msg) , CLogger::LEVEL_ERROR);
		//}
	}
	
	public function actionprocessrefund()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->processrefund();
			}		
		} else {
			$this->processrefund();
		}						
	}

	public function processrefund()
	{
		// Yii::import('ext.runactions.components.ERunActions');
		// if (ERunActions::runBackground()) {	
		    $order_uuid = Yii::app()->input->get("order_uuid");	
		    $transaction_id = (integer) Yii::app()->input->get("transaction_id");		
		    		    		    
		    $order = COrders::get($order_uuid);
		    $transaction = AR_ordernew_transaction::model()->find("transaction_id=:transaction_id 
		    AND order_id=:order_id AND status=:status",array(
		      ':transaction_id'=>intval($transaction_id),
		      ':order_id'=>intval($order->order_id),
		      ':status'=>'unpaid'
		    ));		
		   
		    $payment =  AR_ordernew_transaction::model()->find("order_id=:order_id
		     AND transaction_name=:transaction_name AND status=:status",array(		     
		      ':order_id'=>intval($order->order_id),
		      ':transaction_name'=>"payment",
		      ':status'=>'paid'
		    ));				
		    
		    
		    if(!$transaction){
		    	$this->msg[] = "Transaction not found";
		    }
		    if(!$payment){
		    	$this->msg[] = "Payment not found";
		    }
		    		    		   
		    if($transaction && $payment){
		   	   $merchant = AR_merchant::model()->findByPk( $order->merchant_id );
		   	   $merchant_type = $merchant?$merchant->merchant_type:'';		   	  
		   	  
		   	   //$payment_code = $payment->payment_code;
				$payment_code = $transaction->payment_code;			   
		   	  
			   if($payment_code=="digital_wallet"){
				  $credentials =[];
			   } else {
				  $credentials = CPayments::getPaymentCredentials($order->merchant_id,$payment->payment_code,$merchant_type);		   	  
			      $credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';
			      $is_live = isset($credentials['is_live'])?intval($credentials['is_live']):0;		
			   }		   	   
			  
			   $refund_amount = Price_Formatter::convertToRaw($transaction->trans_amount);
			   		   
			   try {
			   	  			   
			      $refund_response = Yii::app()->getModule($payment_code)->refund($credentials,$transaction,$payment);				  
			      //$transaction->scenario = "refund";
			      $transaction->scenario = trim($transaction->transaction_name);
			      $transaction->order_uuid = $order_uuid;
			      
			      $transaction->payment_reference = $refund_response['id'];
				  $transaction->status = "paid";
				  $transaction->payment_uuid = $payment->payment_uuid;
				  $transaction->save();
				  
				  
				  /*SEND REFUND NOTIFICATIONS*/
			      try {		    	
			    	 $template_name = $transaction->transaction_name=="partial_refund"?'partial_refund_template_id':'refund_template_id';		    	
			    	 if($template_id = AR_admin_meta::getValue($template_name)){
			    	 	 $template_id = $template_id['meta_value'];		    		
			    		 $refund_data = array(
			    		   'refund_amount'=>Price_Formatter::formatNumber($transaction->trans_amount),
			    		   'refund_amount_no_sign'=>Price_Formatter::formatNumberNoSymbol($transaction->trans_amount),
			    		 );
			    		 $this->runOrderSingleAction($order_uuid,$template_id,$refund_data);
			    	 }
			      } catch (Exception $e) {
				     $this->msg[] = t($e->getMessage());
			      }
			      				  
			   } catch (Exception $e) {
			   	  $this->msg[] = t($e->getMessage());		
			   	  $transaction->status = "failed";
		          $transaction->reason = $e->getMessage();
		          $transaction->save();	   	  
			   }			  
		    } 		    
		    
		    dump($this->msg);
			Yii::log( json_encode($this->msg) , CLogger::LEVEL_ERROR);
		//}
	}
	
	public function actionsendinvoice()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->sendinvoice();
			}		
		} else {
			$this->sendinvoice();
		}
	}

	public function sendinvoice()
	{				
		try {
			$order_uuid = Yii::app()->input->get("order_uuid");	
			$transaction_id = Yii::app()->input->get("transaction_id");	
							
			$transaction = COrders::getTransactionPayment($transaction_id);				
			$transaction_uuid = $transaction->transaction_uuid;
			
			if($template_id = AR_admin_meta::getValue('invoice_create_template_id')){
				$template_id = $template_id['meta_value'];	
				$data = array(
					'balance'=>Price_Formatter::formatNumberNoSymbol($transaction->trans_amount),
					'invoice_number'=>$transaction_id,
					'payment_link'=>Yii::app()->createAbsoluteUrl('/account/payment_invoice',array('transaction_uuid'=>$transaction_uuid))
				);							
				$this->runOrderSingleAction($order_uuid,$template_id,$data);
			}				
							
		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());			    			    			    
		}					
		dump($this->msg);
		Yii::log( json_encode($this->msg) , CLogger::LEVEL_ERROR);		
	}
	
	public function actionaftercustomersignup()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->aftercustomersignup();
			}		
		} else {
			$this->aftercustomersignup();
		}		
	}

	public function aftercustomersignup()
	{
		// Yii::import('ext.runactions.components.ERunActions');
		// if (ERunActions::runBackground()) {
			try {
				
				$options = OptionsTools::find(array('signup_enabled_verification','signup_verification_tpl'));											
				$signup_enabled_verification = isset($options['signup_enabled_verification'])?$options['signup_enabled_verification']:'';				
				$template_id = isset($options['signup_verification_tpl'])?$options['signup_verification_tpl']:'';
				
				
				$client_uuid = Yii::app()->input->get("client_uuid");		
				$verification_type =  Yii::app()->input->get("verification_type");
					
				$model = AR_client::model()->find("client_uuid=:client_uuid",array(
					 ':client_uuid'=>$client_uuid
				));
				if($model){			

				   // POINTS
				   $points_enabled = isset(Yii::app()->params['settings']['points_enabled'])?Yii::app()->params['settings']['points_enabled']:false;
		           $points_enabled = $points_enabled==1?true:false;
				   $points_registration = isset(Yii::app()->params['settings']['points_registration'])?floatval(Yii::app()->params['settings']['points_registration']):0;
				   if($points_enabled && $points_registration>0){
					  $point_description = CPoints::getDescription('points_signup');
					  try {
						CPoints::globalPoints($client_uuid,'points_signup',$point_description,$points_registration);
					  } catch (Exception $e) {
						//
					  }
				   }

				   $site = CNotifications::getSiteData();						  
				   $data = array(		
				     'first_name'=>$model->first_name,
				     'last_name'=>$model->last_name,
				     'email_address'=>$model->email_address,
				     'code'=>$model->mobile_verification_code,
				     'site'=>$site,
				     'logo'=>isset($site['logo'])?$site['logo']:'',
				     'facebook'=>isset($site['facebook'])?$site['facebook']:'',
				     'twitter'=>isset($site['twitter'])?$site['twitter']:'',
				     'instagram'=>isset($site['instagram'])?$site['instagram']:'',
				     'whatsapp'=>isset($site['whatsapp'])?$site['whatsapp']:'',
				     'youtube'=>isset($site['youtube'])?$site['youtube']:'',
				   );					   

				   if($model->merchant_id>0){
						$options2 = OptionsTools::find(array('merchant_signup_enabled_verification','merchant_captcha_enabled','merchant_captcha_secret'),$model->merchant_id);						
						$signup_enabled_verification = isset($options2['merchant_signup_enabled_verification'])?$options2['merchant_signup_enabled_verification']:false;
					}
				}
									
				if($signup_enabled_verification){					
					if($model){											
					   $this->runActions($template_id, $data , array($verification_type) , array(
					     'phone'=>$model->contact_phone,
					     'email'=>$model->email_address
					   ));
					}
				} else {
					if($model->status=="active" && $model->social_strategy=="web"){
					   $options = OptionsTools::find(array('signupnew_tpl','admin_email_alert','admin_mobile_alert','signup_welcome_tpl'));					   
				       $template_id = isset($options['signupnew_tpl'])?$options['signupnew_tpl']:'';
					   $template_id_welcome = isset($options['signup_welcome_tpl'])?$options['signup_welcome_tpl']:'';
				       $admin_mobile_alert = isset($options['admin_mobile_alert'])?$options['admin_mobile_alert']:'';
				       $admin_email_alert = isset($options['admin_email_alert'])?$options['admin_email_alert']:'';
				       				
					   // SEND WELCOME EMAIL
					   $this->runActions($template_id_welcome, $data , array('sms','email') , array(
							'phone'=>$model->contact_phone,
							'email'=>$model->email_address,
					   ));


					   // SEND NOTIFICATION TO BACKEND
				       $this->runActions($template_id, $data , array('sms','email','push') , array(
					     'phone'=>$admin_mobile_alert,
					     'email'=>$admin_email_alert,
					   ),array(
					      'channel'=>Yii::app()->params->realtime['admin_channel'],
					      'type'=>'customer_new_signup',
					      'event'=>Yii::app()->params->realtime['notification_event'],
					    ),array(
					      'channel'=>Yii::app()->params->realtime['admin_channel'],
					      'type'=>'customer_new_signup',			      
					    ));				
												

						
					}
				}
				
			} catch (Exception $e) {
			    $this->msg[] = t($e->getMessage());			    			    			    
			}	
			dump($this->msg);
			Yii::log( json_encode($this->msg) , CLogger::LEVEL_ERROR);
		//}			
	}
	
	private function runActions($template_id=0, $data=array() , $send_type=array() , $send_to=array() , 
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

												
				if(!empty($email_subject) && !empty($template)){					
					$emails = array_map('trim', explode(',', $email));
					$isMultiple = count($emails) > 1;
					if($isMultiple){
						foreach ($emails as $items_email) {
							$resp = CommonUtility::sendEmail($items_email,'',$email_subject,$template);		    	    
						}
					} else $resp = CommonUtility::sendEmail($email,'',$email_subject,$template);		    	    
			    }
			    if(!empty($sms_template)){			
					$sms_template_id = isset($items['sms_template_id'])?$items['sms_template_id']:'';					
					$sms_vars = $data;
					if(isset($sms_vars['site'])){
						unset($sms_vars['site']);				
					}											
			    	$resp = CommonUtility::sendSMS($phone,$sms_template,0,0,"",$sms_template_id,$sms_vars);
			    }
			    			    			    
			    if(!empty($push_template)){				    	
			    	if($realtime_app_enabled==1){				    		
			    		$noti = new AR_notifications;							
						$noti->notication_channel = isset($noti_channel['channel'])?$noti_channel['channel']:'';
						$noti->notification_event = isset($noti_channel['event'])?$noti_channel['event']:'';
						$noti->notification_type = isset($noti_channel['type'])?$noti_channel['type']:'';
						$noti->message = $push_template;						
						$noti->message_parameters = json_encode($message_parameters);						
						$noti->meta_data = isset($noti_channel['meta_data'])?json_encode($noti_channel['meta_data']):'';
						$noti->image_type = 'icon';						
						$noti->image = isset($noti_channel['icon_name'])?$noti_channel['icon_name']:'zmdi zmdi-face';
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
	
	public function actionafterregistration()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->afterregistration();
			}		
		} else {
			$this->afterregistration();
		}
	}

	public function afterregistration()
	{
		// Yii::import('ext.runactions.components.ERunActions');
		// if (ERunActions::runBackground()) {
			try {
			
				$client_uuid = Yii::app()->input->get("client_uuid");				
				
				$options = OptionsTools::find(array('signup_welcome_tpl'));
				$template_id = isset($options['signup_welcome_tpl'])?$options['signup_welcome_tpl']:'';
				
				$model = AR_client::model()->find("client_uuid=:client_uuid",array(
				 ':client_uuid'=>$client_uuid
				));
				if($model){
									
				   if($model->status=="active"){
				   	   $site = CNotifications::getSiteData();
					   $data = array(		
					      'first_name'=>$model->first_name,
					      'last_name'=>$model->last_name,
					      'email_address'=>$model->email_address,
					      'code'=>$model->mobile_verification_code,
					      'site'=>$site,
					      'logo'=>isset($site['logo'])?$site['logo']:'',
					      'facebook'=>isset($site['facebook'])?$site['facebook']:'',
					      'twitter'=>isset($site['twitter'])?$site['twitter']:'',
					      'instagram'=>isset($site['instagram'])?$site['instagram']:'',
					      'whatsapp'=>isset($site['whatsapp'])?$site['whatsapp']:'',
					      'youtube'=>isset($site['youtube'])?$site['youtube']:'',
					   );							  
					   $this->runActions($template_id, $data , array('sms','email') , array(
					     'phone'=>$model->contact_phone,
					     'email'=>$model->email_address,
					   ));
				   }		
				   
				   if($model->status=="active"){				       
					   $options = OptionsTools::find(array('signupnew_tpl','admin_email_alert','admin_mobile_alert'));					   
				       $template_id = isset($options['signupnew_tpl'])?$options['signupnew_tpl']:'';
				       $admin_mobile_alert = isset($options['admin_mobile_alert'])?$options['admin_mobile_alert']:'';
				       $admin_email_alert = isset($options['admin_email_alert'])?$options['admin_email_alert']:'';
				       				       				      
				       $this->runActions($template_id, $data , array('sms','email','push') , array(
					     'phone'=>$admin_mobile_alert,
					     'email'=>$admin_email_alert,
					   ),array(
					      'channel'=>Yii::app()->params->realtime['admin_channel'],
					      'type'=>'customer_new_signup',
					      'event'=>Yii::app()->params->realtime['notification_event'],
					    ),array(
					      'channel'=>Yii::app()->params->realtime['admin_channel'],
					      'type'=>'customer_new_signup',			      
					    ));					   
				   }
					   
				   			
				} 
			} catch (Exception $e) {
			    $this->msg[] = t($e->getMessage());			    			    			    
			}				
			Yii::log( json_encode($this->msg) , CLogger::LEVEL_ERROR);
		//}
	}
	
	public function actionafter_requestresetpassword()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->after_requestresetpassword();
			}		
		} else {
			$this->after_requestresetpassword();
		}
	}

	public function after_requestresetpassword()
	{
		// Yii::import('ext.runactions.components.ERunActions');
		// if (ERunActions::runBackground()) {
			try {
				
				$client_uuid = Yii::app()->input->get("client_uuid");	
				$options = OptionsTools::find(array('signup_resetpass_tpl'));
				$template_id = isset($options['signup_resetpass_tpl'])?$options['signup_resetpass_tpl']:'';																
				
				$model = AR_client::model()->find("client_uuid=:client_uuid",array(
				 ':client_uuid'=>$client_uuid
				));
				if($model){
					if($model->merchant_id>0){
						$options = OptionsTools::find(array('signup_verification_tpl'));
				        $template_id = isset($options['signup_verification_tpl'])?$options['signup_verification_tpl']:'';					
					}
										
					if($model->status=="active"){
						$site = CNotifications::getSiteData();
						$data = array(		
					      'first_name'=>$model->first_name,
					      'last_name'=>$model->last_name,
					      'email_address'=>$model->email_address,
					      'code'=>$model->mobile_verification_code,
					      'site'=>$site,
					      'logo'=>isset($site['logo'])?$site['logo']:'',
					      'facebook'=>isset($site['facebook'])?$site['facebook']:'',
					      'twitter'=>isset($site['twitter'])?$site['twitter']:'',
					      'instagram'=>isset($site['instagram'])?$site['instagram']:'',
					      'whatsapp'=>isset($site['whatsapp'])?$site['whatsapp']:'',
					      'youtube'=>isset($site['youtube'])?$site['youtube']:'',
					      'reset_password_link'=>websiteUrl()."/account/reset_password?token=".$model->client_uuid
					   );									   
					   if($model->merchant_id>0){						   
						   $jwt_token = AttributesTools::JwtTokenID();
						   $jwt_value = AR_merchant_meta::getValue($model->merchant_id,$jwt_token);						   
						   if($jwt_value){
								try {
									$jwt_value = isset($jwt_value['meta_value'])?$jwt_value['meta_value']:'';
									require_once 'JWTwrapper.php';							  		
									$jwt_value = JWTwrapper::decode($jwt_value);									
									$single_site_url = $jwt_value->aud;									
									$data['reset_password_link'] = $single_site_url."/account/reset_password?token=".$model->client_uuid;
								} catch (Exception $e) {
									//
								}		
						   }						   
					   }					   
					   $this->runActions($template_id, $data , array('email') , array(					     
					     'email'=>$model->email_address,
					   ));
					}
				}
				
			} catch (Exception $e) {
			    $this->msg[] = t($e->getMessage());			    			    			    
			}				
			Yii::log( json_encode($this->msg) , CLogger::LEVEL_ERROR);
		//}
	}
		
	public function actionafter_request_payout()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->after_request_payout();
			}		
		} else {
			$this->after_request_payout();
		}
	}

	public function after_request_payout()
	{
		// Yii::import('ext.runactions.components.ERunActions');
		// if (ERunActions::runBackground()) {
			try {
			
				$transaction_uuid = Yii::app()->input->get("transaction_uuid");	
				
				$options = OptionsTools::find(array('admin_email_alert','admin_mobile_alert','admin_enabled_alert'));
				$enabled = isset($options['admin_enabled_alert'])?$options['admin_enabled_alert']:0;
				$enabled = $enabled==1?true:false;
				$email = isset($options['admin_email_alert'])?$options['admin_email_alert']:'';
				$phone = isset($options['admin_mobile_alert'])?$options['admin_mobile_alert']:'';
				
				if($enabled==false){
					Yii::app()->end();
				}
								
				$data = CPayouts::getPayoutDetails($transaction_uuid);
				$card_id = isset($data['card_id'])?$data['card_id']:'';
				
				$provider = AttributesTools::paymentProviderDetails($data['provider']);
				
				$meta = AR_admin_meta::getValue('payout_new_payout_template_id');
				$template_id = isset($meta['meta_value'])?$meta['meta_value']:0;
					
				$merchant = array();
				/*try {
					$merchant_id = isset($data['merchant_id'])?$data['merchant_id']:'';
					$merchant = CMerchantListingV1::getMerchant(intval($merchant_id));
				} catch (Exception $e) {
					//
				}*/
				try{
			       $merchant_id = CWallet::getAccountID($card_id);		    
			       $merchant = CMerchants::get($merchant_id);				   
			    } catch (Exception $e) {
			    	//
			    }
				
				$site = CNotifications::getSiteData();
				$params = array(					      
			      'site'=>$site,
			      'logo'=>isset($site['logo'])?$site['logo']:'',
			      'facebook'=>isset($site['facebook'])?$site['facebook']:'',
			      'twitter'=>isset($site['twitter'])?$site['twitter']:'',
			      'instagram'=>isset($site['instagram'])?$site['instagram']:'',
			      'whatsapp'=>isset($site['whatsapp'])?$site['whatsapp']:'',
			      'youtube'=>isset($site['youtube'])?$site['youtube']:'',		
			      'transaction_id'=>$data['transaction_id'],	      
			      'transaction_amount'=>$data['transaction_amount'],
				  'payment_method'=>isset($provider['payment_name'])?$provider['payment_name']:$data['provider'],
				  'transaction_description'=>$data['transaction_description'],
				  'transaction_date'=>$data['transaction_date'],
				  'restaurant_name'=>isset($merchant['restaurant_name'])?$merchant['restaurant_name']:'',
			    );			    
				$this->runActions($template_id, $params , array('sms','email','push') , array(
			     'phone'=>$phone,
			     'email'=>$email,
			    ),array(
			      'channel'=>Yii::app()->params->realtime['admin_channel'],
			      'type'=>'payout_request',
			      'event'=>Yii::app()->params->realtime['notification_event'],
			    ),array(
			      'channel'=>Yii::app()->params->realtime['admin_channel'],
			      'type'=>'payout_request',			      
			    ));					   
			} catch (Exception $e) {
			    $this->msg[] = t($e->getMessage());			    			    			    
			}				
			Yii::log( json_encode($this->msg) , CLogger::LEVEL_ERROR);
		//}
	}
	
	public function actionafterpayout_cancel()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->afterpayout_cancel();
			}		
		} else {
			$this->afterpayout_cancel();
		}
	}

	public function afterpayout_cancel()
	{
		// Yii::import('ext.runactions.components.ERunActions');
		// if (ERunActions::runBackground()) {
			try {
				
				$transaction_uuid = Yii::app()->input->get("transaction_uuid");	
				
				$data = CPayouts::getPayoutDetails($transaction_uuid);				
				$provider = AttributesTools::paymentProviderDetails($data['provider']);
				
				$meta = AR_admin_meta::getValue('payout_cancel_template_id');
				$template_id = isset($meta['meta_value'])?$meta['meta_value']:0;
				
				$site = CNotifications::getSiteData();
				$params = array(					      
			      'site'=>$site,
			      'logo'=>isset($site['logo'])?$site['logo']:'',
			      'facebook'=>isset($site['facebook'])?$site['facebook']:'',
			      'twitter'=>isset($site['twitter'])?$site['twitter']:'',
			      'instagram'=>isset($site['instagram'])?$site['instagram']:'',
			      'whatsapp'=>isset($site['whatsapp'])?$site['whatsapp']:'',
			      'youtube'=>isset($site['youtube'])?$site['youtube']:'',			      
			      'transaction_id'=>$data['transaction_id'],
			      'transaction_amount'=>$data['transaction_amount'],
				  'payment_methood'=>isset($provider['payment_name'])?$provider['payment_name']:$data['provider'],
				  'transaction_description'=>$data['transaction_description'],
				  'transaction_date'=>$data['transaction_date']
			    );		
			    			    			    
			    $card_id = isset($data['card_id'])?$data['card_id']:'';		    
			    try{
			       $merchant_id = CWallet::getAccountID($card_id);		    
			       $merchant = CMerchants::get($merchant_id);	
			       $params['restaurant_name'] = Yii::app()->input->xssClean($merchant->restaurant_name);		   
			    } catch (Exception $e) {
			    	//
			    }
			    
			    $options = OptionsTools::find(array('merchant_enabled_alert','merchant_email_alert','merchant_mobile_alert'),$merchant_id);
			    			    
				$enabled = isset($options['merchant_enabled_alert'])?$options['merchant_enabled_alert']:0;
				$enabled = $enabled==1?true:false;
				$email = isset($options['merchant_email_alert'])?$options['merchant_email_alert']:'';
				$phone = isset($options['merchant_mobile_alert'])?$options['merchant_mobile_alert']:'';
			    
				$this->runActions($template_id, $params , array('sms','email','push') , array(
			     'phone'=>$phone,
			     'email'=>$email,
			    ),array(
			      'channel'=>$merchant->merchant_uuid,
			      'type'=>'payout_cancelled',
			      'event'=>Yii::app()->params->realtime['notification_event'],
			    ),array(
			      'channel'=>$merchant->merchant_uuid,
			      'type'=>'payout_cancelled',			      
			    ));					
				
			} catch (Exception $e) {
			    $this->msg[] = t($e->getMessage());			    			    			    
			}	
			dump($this->msg);
			Yii::log( json_encode($this->msg) , CLogger::LEVEL_ERROR);
		//}
	}
	
	public function actionsendnotifications()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->sendnotifications();
			}		
		} else {
			$this->sendnotifications();
		}
	}

	public function sendnotifications()
	{
		// Yii::import('ext.runactions.components.ERunActions');
		// if (ERunActions::runBackground()) {
			try {
				
				$realtime = AR_admin_meta::getMeta(array('realtime_provider','realtime_app_enabled',
				  'pusher_app_id','pusher_key','pusher_secret','pusher_cluster','ably_apikey',
				  'piesocket_clusterid','piesocket_api_key','piesocket_api_secret'
				));						
				
				$realtime_app_enabled = isset($realtime['realtime_app_enabled'])?$realtime['realtime_app_enabled']['meta_value']:'';
				$realtime_provider = isset($realtime['realtime_provider'])?$realtime['realtime_provider']['meta_value']:'';
				
				$pusher_app_id = isset($realtime['pusher_app_id'])?$realtime['pusher_app_id']['meta_value']:'';
				$pusher_key = isset($realtime['pusher_key'])?$realtime['pusher_key']['meta_value']:'';
				$pusher_secret = isset($realtime['pusher_secret'])?$realtime['pusher_secret']['meta_value']:'';
				$pusher_cluster = isset($realtime['pusher_cluster'])?$realtime['pusher_cluster']['meta_value']:'';				
				$ably_apikey = isset($realtime['ably_apikey'])?$realtime['ably_apikey']['meta_value']:'';
				
				$piesocket_clusterid = isset($realtime['piesocket_clusterid'])?$realtime['piesocket_clusterid']['meta_value']:'';
				$piesocket_api_key = isset($realtime['piesocket_api_key'])?$realtime['piesocket_api_key']['meta_value']:'';
				$piesocket_api_secret = isset($realtime['piesocket_api_secret'])?$realtime['piesocket_api_secret']['meta_value']:'';
				
				if($realtime_app_enabled!=1){
					Yii::app()->end();
				}
				
				$notification_uuid = Yii::app()->input->get("notification_uuid");	
				$item = CNotificationData::getData($notification_uuid);
				
				$image=''; $url = '';
				if($item->image_type=="icon"){
					$image = !empty($item->image)?$item->image:'';
				} else {
					if(!empty($item->image)){
						$image = CMedia::getImage($item->image,$item->image_path,
						Yii::app()->params->size_image_thumbnail ,
						CommonUtility::getPlaceholderPhoto('item') );
					}
				}
				
				$params = !empty($item->message_parameters)?json_decode($item->message_parameters,true):'';
				
				$data=array(
				  'notification_type'=>$item->notification_type,
				  'message'=>t($item->message,(array)$params),
				  'date'=>PrettyDateTime::parse(new DateTime($item->date_created)),				  
				  'image_type'=>$item->image_type,
				  'image'=>$image,
				  'url'=>$url,
				  'meta_data'=>!empty($item->meta_data)?json_decode($item->meta_data,true):''
				);
							
				$settings = array(
				  'notication_channel'=>$item->notication_channel,
				  'notification_event'=>$item->notification_event,
				  'app_id'=>$pusher_app_id,
				  'key'=>$pusher_key,
				  'secret'=>$pusher_secret,
				  'cluster'=>$pusher_cluster,	
				  'ably_apikey'=>$ably_apikey,
				  'piesocket_clusterid'=>$piesocket_clusterid,
				  'piesocket_api_key'=>$piesocket_api_key,
				  'piesocket_api_secret'=>$piesocket_api_secret,
				);
								
				CNotifier::send($realtime_provider,$settings,$data);

				$item->status="process";
				$item->save();
				
			} catch (Exception $e) {
			    $this->msg[] = t($e->getMessage());			    			    			    
			}	
			dump($this->msg);
			Yii::log( json_encode($this->msg) , CLogger::LEVEL_ERROR);
		//}
	}
	
	public function actionsendwebpush()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->sendwebpush();
			}		
		} else {
			$this->sendwebpush();
		}
	}

	public function sendwebpush()
	{

		$current_url = CommonUtility::getCurrentURL();					
        CommonUtility::saveCronURL($current_url);

		// Yii::import('ext.runactions.components.ERunActions');
		// if (ERunActions::runBackground()) {
			try {
				
				$settings = AR_admin_meta::getMeta(array('webpush_app_enabled','webpush_provider',
				  'pusher_instance_id','onesignal_app_id','pusher_primary_key','onesignal_rest_apikey'
				));			
				
				$webpush_app_enabled = isset($settings['webpush_app_enabled'])?$settings['webpush_app_enabled']['meta_value']:'';						
				$webpush_app_enabled = $webpush_app_enabled==1?true:false;
				$webpush_provider = isset($settings['webpush_provider'])?$settings['webpush_provider']['meta_value']:'';
				$pusher_instance_id = isset($settings['pusher_instance_id'])?$settings['pusher_instance_id']['meta_value']:'';
				$pusher_primary_key = isset($settings['pusher_primary_key'])?$settings['pusher_primary_key']['meta_value']:'';
				$onesignal_app_id = isset($settings['onesignal_app_id'])?$settings['onesignal_app_id']['meta_value']:'';
				$onesignal_rest_apikey = isset($settings['onesignal_rest_apikey'])?$settings['onesignal_rest_apikey']['meta_value']:'';
				
				$pushweb_config = array(
				  'provider'=>$webpush_provider,
				  'pusher_instance_id'=>$pusher_instance_id,
				  'pusher_instance_id'=>$pusher_instance_id,
				  'pusher_primary_key'=>$pusher_primary_key,
				  'onesignal_app_id'=>$onesignal_app_id,
				  'onesignal_rest_apikey'=>$onesignal_rest_apikey,				  
				);		
								
				$push_uuid = Yii::app()->input->get("push_uuid");	
				$model = AR_push::model()->find("push_uuid=:push_uuid",array(
				 ':push_uuid'=>$push_uuid
				));								
				if($model){							
					$pushweb_config['channel'] = $model->channel_device_id;
					$params = array(					  
					  'title'=>$model->title,
					  'body'=>$model->body,
					);								
					if($model->provider=="onesignal"){
						if($device_ids = CNotificationData::getDeviceInterest(array($model->channel_device_id))){						   				
						   $params['device_ids'] = $device_ids;						
						}
					}	
					try {				
					  $resp = CPushweb::send($pushweb_config,$params);
					} catch (Exception $e) {
						$resp = $e->getMessage();
					}					
					$model->response = $resp;
					$model->status = 'process';
					$model->save();
				} else $this->msg[] = "no results";
				
			} catch (Exception $e) {
			    $this->msg[] = t($e->getMessage());			    			    			    
			}	
			dump($this->msg);
			Yii::log( json_encode($this->msg) , CLogger::LEVEL_ERROR);
		//}
		
		CommonUtility::updateCronURL($current_url);
	}	
	
	public function actionTestPusher()
	{
			$realtime = AR_admin_meta::getMeta(array('realtime_provider','realtime_app_enabled',
			  'pusher_app_id','pusher_key','pusher_secret','pusher_cluster','ably_apikey',
			  'piesocket_clusterid','piesocket_api_key','piesocket_api_secret'
			));						
			
			$realtime_app_enabled = isset($realtime['realtime_app_enabled'])?$realtime['realtime_app_enabled']['meta_value']:'';
			$realtime_provider = isset($realtime['realtime_provider'])?$realtime['realtime_provider']['meta_value']:'';
			
			$pusher_app_id = isset($realtime['pusher_app_id'])?$realtime['pusher_app_id']['meta_value']:'';
			$pusher_key = isset($realtime['pusher_key'])?$realtime['pusher_key']['meta_value']:'';
			$pusher_secret = isset($realtime['pusher_secret'])?$realtime['pusher_secret']['meta_value']:'';
			$pusher_cluster = isset($realtime['pusher_cluster'])?$realtime['pusher_cluster']['meta_value']:'';				
			$ably_apikey = isset($realtime['ably_apikey'])?$realtime['ably_apikey']['meta_value']:'';
			
			$piesocket_clusterid = isset($realtime['piesocket_clusterid'])?$realtime['piesocket_clusterid']['meta_value']:'';
			$piesocket_api_key = isset($realtime['piesocket_api_key'])?$realtime['piesocket_api_key']['meta_value']:'';
			$piesocket_api_secret = isset($realtime['piesocket_api_secret'])?$realtime['piesocket_api_secret']['meta_value']:'';
			
			$settings = array(
			  'notication_channel'=>'7695f9c5-23f7-11ec-bc4b-9c5c8e164c2c',
			  'notification_event'=>'event-tracking-order',
			  'app_id'=>$pusher_app_id,
			  'key'=>$pusher_key,
			  'secret'=>$pusher_secret,
			  'cluster'=>$pusher_cluster,	
			  'ably_apikey'=>$ably_apikey,
			  'piesocket_clusterid'=>$piesocket_clusterid,
			  'piesocket_api_key'=>$piesocket_api_key,
			  'piesocket_api_secret'=>$piesocket_api_secret,
			);
			dump($realtime_provider);
			dump($settings);
			$data = array('order_progress'=>2);	
			dump($data);
							
			CNotifier::send($realtime_provider,$settings,$data);
			
	}
	
	public function actiontrackorder()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->trackorder();
			}		
		} else {
			$this->trackorder();
		}
	}

	public function trackorder()
	{
		// Yii::import('ext.runactions.components.ERunActions');
		// if (ERunActions::runBackground()) {
			try {
				
				$order_uuid = Yii::app()->input->get("order_uuid");							
				$data = CTrackingOrder::getProgress($order_uuid);				
				$client_uuid = isset($data['customer'])?$data['customer']['client_uuid']:'';
				if($data['customer']){unset($data['customer']);}
				$settings = CNotificationData::getRealtimeSettings();
				$provider = isset($settings['provider'])?$settings['provider']:'';				
				
				$settings['notication_channel'] = $client_uuid;
				$settings['notification_event'] = Yii::app()->params->realtime['event_tracking_order'];				
								
				//dump($data);die();				
				CNotifier::send($provider,$settings,$data);
				
			} catch (Exception $e) {
			    $this->msg[] = t($e->getMessage());			    			    			    
			}	
			dump($this->msg);
			Yii::log( json_encode($this->msg) , CLogger::LEVEL_ERROR);
		//}
	}
	
	public function actionaftermerchantsignup()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->aftermerchantsignup();
			}		
		} else {
			$this->aftermerchantsignup();
		}
	}

	public function aftermerchantsignup()
	{
		// Yii::import('ext.runactions.components.ERunActions');
		// if (ERunActions::runBackground()) {
			try {
				
				$merchant_uuid = Yii::app()->input->get("merchant_uuid");
				$merchant = CMerchants::getByUUID($merchant_uuid);
								
		        try {
				    CWallet::getCardID( Yii::app()->params->account_type['merchant'] , $merchant->merchant_id );
				} catch (Exception $e) {
				    $wallet = new AR_wallet_cards;	
				    $wallet->account_type = Yii::app()->params->account_type['merchant'] ;
			        $wallet->account_id = intval($merchant->merchant_id);
			        $wallet->save();
				}	
				
				$options = OptionsTools::find(['enabled_copy_payment_setting']);
				$enabled = isset($options['enabled_copy_payment_setting'])?$options['enabled_copy_payment_setting']:false;
				$enabled = $enabled==1?true:false;

				/*ADD DEFAULT PAYMENT GATEWAY*/
				if(!$enabled){				
					$payment_list = CommonUtility::getDataToDropDown("{{payment_gateway}}",'payment_code','payment_code',
					"WHERE status='active'","ORDER BY sequence ASC");
					if(is_array($payment_list) && count($payment_list)>=1){				
						$payment_data  = array();
						foreach ($payment_list as $payment_item) {
							$payment_data[]=$payment_item;
						}							
						MerchantTools::saveMerchantMeta($merchant->merchant_id,$payment_data,'payment_gateway');
					}
			    }
				
			} catch (Exception $e) {
			    $this->msg[] = t($e->getMessage());			    			    			    
			}	
			dump($this->msg);
			Yii::log( json_encode($this->msg) , CLogger::LEVEL_ERROR);
		//}
	}
	
	public function actionaftermerchantpayment()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->aftermerchantpayment();
			}		
		} else {
			$this->aftermerchantpayment();
		}		
	}

	public function aftermerchantpayment()
	{
		// Yii::import('ext.runactions.components.ERunActions');
		// if (ERunActions::runBackground()) {
			try {
				
				$merchant_uuid = Yii::app()->input->get("merchant_uuid");
				$merchant = CMerchants::getByUUID($merchant_uuid);
				
				$options = OptionsTools::find(array('registration_confirm_account_tpl','merchant_registration_welcome_tpl'));
				$template_id = isset($options['registration_confirm_account_tpl'])?$options['registration_confirm_account_tpl']:'';
				$welcome_template_id = isset($options['merchant_registration_welcome_tpl'])?$options['merchant_registration_welcome_tpl']:'';
				
				$confirm_link = Yii::app()->createAbsoluteUrl("/merchant/confirm_account?uuid=".$merchant->merchant_uuid);
				
				try{
					$plans = Cplans::planDetails($merchant->package_id,Yii::app()->language);								
				} catch (Exception $e) {
					$plans = '';
				}				
				
				$site = CNotifications::getSiteData();
				$params = array(					      
			      'site'=>$site,
			      'logo'=>isset($site['logo'])?$site['logo']:'',
			      'facebook'=>isset($site['facebook'])?$site['facebook']:'',
			      'twitter'=>isset($site['twitter'])?$site['twitter']:'',
			      'instagram'=>isset($site['instagram'])?$site['instagram']:'',
			      'whatsapp'=>isset($site['whatsapp'])?$site['whatsapp']:'',
			      'youtube'=>isset($site['youtube'])?$site['youtube']:'',
			      'restaurant_name'=>$merchant->restaurant_name,
			      'contact_phone'=>$merchant->contact_phone,
			      'contact_email'=>$merchant->contact_email,
			      'address'=>$merchant->address,	
			      'confirm_link'=>$confirm_link,
			      'plan_title'=>$plans?$plans->title:'',
			    );		
			    
			    $email = $merchant->contact_email;
			    $phone = $merchant->contact_phone;
			    
			    $this->runActions($template_id, $params , array('sms','email','push') , array(
			     'phone'=>$phone,
			     'email'=>$email,
			    ),array(
			      'channel'=>$merchant->merchant_uuid,
			      'type'=>'merchant_confirm_account',
			      'event'=>Yii::app()->params->realtime['notification_event'],
			    ),array(
			      'channel'=>$merchant->merchant_uuid,
			      'type'=>'merchant_confirm_account',			      
			    ));		

				// SEND WELCOME EMAIL
				$this->runActions($welcome_template_id, $params , array('sms','email','push') , array(
					'phone'=>$phone,
					'email'=>$email,
				   ),array(
					 'channel'=>$merchant->merchant_uuid,
					 'type'=>'merchant_confirm_account',
					 'event'=>Yii::app()->params->realtime['notification_event'],
				   ),array(
					 'channel'=>$merchant->merchant_uuid,
					 'type'=>'merchant_confirm_account',			      
				));		
			    
			    
			    /*SEND EMAIL TO ADMIN*/
			    
			    $options = OptionsTools::find(array('admin_email_alert','admin_mobile_alert','admin_enabled_alert','merchant_registration_new_tpl'));
				$enabled = isset($options['admin_enabled_alert'])?$options['admin_enabled_alert']:0;
				$enabled = $enabled==1?true:false;
				$email = isset($options['admin_email_alert'])?$options['admin_email_alert']:'';
				$phone = isset($options['admin_mobile_alert'])?$options['admin_mobile_alert']:'';
																
				if($enabled){
					$template_id = isset($options['merchant_registration_new_tpl'])?$options['merchant_registration_new_tpl']:'';								
					$this->runActions($template_id, $params , array('sms','email','push') , array(
				      'phone'=>$phone,
				      'email'=>$email,
				    ),array(
				        'channel'=>Yii::app()->params->realtime['admin_channel'],
				        'type'=>'merchant_new_signup',
				        'event'=>Yii::app()->params->realtime['notification_event'],
				    ),array(
				        'channel'=>Yii::app()->params->realtime['admin_channel'],
				        'type'=>'merchant_new_signup',			      
				    ));			
				}
				
			} catch (Exception $e) {
			    $this->msg[] = t($e->getMessage());			    			    			    
			}	
			dump($this->msg);
			Yii::log( json_encode($this->msg) , CLogger::LEVEL_ERROR);
		//}
	}
	
	public function actionafter_plan_past_due()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->after_plan_past_due();
			}		
		} else {
			$this->after_plan_past_due();
		}
	}

	public function after_plan_past_due()
	{
		// Yii::import('ext.runactions.components.ERunActions');
		// if (ERunActions::runBackground()) {
			try {
				
				$merchant_uuid = Yii::app()->input->get("merchant_uuid");
				$merchant = CMerchants::getByUUID($merchant_uuid);				
				
				$options = OptionsTools::find(array('merchant_plan_expired_tpl'));
                $template_id = isset($options['merchant_plan_expired_tpl'])?$options['merchant_plan_expired_tpl']:'';
                
                $site = CNotifications::getSiteData();
	            $params = array(					      
	              'site'=>$site,
	              'logo'=>isset($site['logo'])?$site['logo']:'',
	              'facebook'=>isset($site['facebook'])?$site['facebook']:'',
	              'twitter'=>isset($site['twitter'])?$site['twitter']:'',
	              'instagram'=>isset($site['instagram'])?$site['instagram']:'',
	              'whatsapp'=>isset($site['whatsapp'])?$site['whatsapp']:'',
	              'youtube'=>isset($site['youtube'])?$site['youtube']:'',
	              'restaurant_name'=>$merchant->restaurant_name,
	              'contact_phone'=>$merchant->contact_phone,
	              'contact_email'=>$merchant->contact_email,
	              'address'=>$merchant->address	              
	            );		
	            
	            $email = $merchant->contact_email;
                $phone = $merchant->contact_phone;
                
                $this->runActions($template_id, $params , array('sms','email','push') , array(
	             'phone'=>$phone,
	             'email'=>$email,
	            ),array(
	              'channel'=>$merchant->merchant_uuid,
	              'type'=>'merchant_plan_expired',
	              'event'=>Yii::app()->params->realtime['notification_event'],
	            ),array(
	              'channel'=>$merchant->merchant_uuid,
	              'type'=>'merchant_plan_expired',			      
	            ));		
            
			} catch (Exception $e) {
			    $this->msg[] = t($e->getMessage());			    			    			    
			}	
			dump($this->msg);
			Yii::log( json_encode($this->msg) , CLogger::LEVEL_ERROR);
		//}
	}
	
	public function actionmerchant_trial_end()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->merchant_trial_end();
			}		
		} else {
			$this->merchant_trial_end();
		}
	}

	public function merchant_trial_end()
	{
		// Yii::import('ext.runactions.components.ERunActions');
		// if (ERunActions::runBackground()) {
			try {
				
				$merchant_uuid = Yii::app()->input->get("merchant_uuid");
				$trial_end = Yii::app()->input->get("trial_end");
				$merchant = CMerchants::getByUUID($merchant_uuid);				
				
				$options = OptionsTools::find(array('merchant_plan_near_expired_tpl'));
                $template_id = isset($options['merchant_plan_near_expired_tpl'])?$options['merchant_plan_near_expired_tpl']:'';
                
                $site = CNotifications::getSiteData();
	            $params = array(					      
	              'site'=>$site,
	              'logo'=>isset($site['logo'])?$site['logo']:'',
	              'facebook'=>isset($site['facebook'])?$site['facebook']:'',
	              'twitter'=>isset($site['twitter'])?$site['twitter']:'',
	              'instagram'=>isset($site['instagram'])?$site['instagram']:'',
	              'whatsapp'=>isset($site['whatsapp'])?$site['whatsapp']:'',
	              'youtube'=>isset($site['youtube'])?$site['youtube']:'',
	              'restaurant_name'=>$merchant->restaurant_name,
	              'contact_phone'=>$merchant->contact_phone,
	              'contact_email'=>$merchant->contact_email,
	              'address'=>$merchant->address,
	              'expiration_date'=>Date_Formatter::date($trial_end)
	            );		
	            	            
	            $email = $merchant->contact_email;
                $phone = $merchant->contact_phone;
                
                $this->runActions($template_id, $params , array('sms','email','push') , array(
	             'phone'=>$phone,
	             'email'=>$email,
	            ),array(
	              'channel'=>$merchant->merchant_uuid,
	              'type'=>'merchant_trial_end',
	              'event'=>Yii::app()->params->realtime['notification_event'],
	            ),array(
	              'channel'=>$merchant->merchant_uuid,
	              'type'=>'merchant_trial_end',			      
	            ));		
				
			} catch (Exception $e) {
			    $this->msg[] = t($e->getMessage());			    			    			    
			}	
			dump($this->msg);
			Yii::log( json_encode($this->msg) , CLogger::LEVEL_ERROR);
		//}
	}
	
	public function actionadminpassword()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->adminpassword();
			}		
		} else {
			$this->adminpassword();
		}
	}

	public function adminpassword()
	{
		// Yii::import('ext.runactions.components.ERunActions');
		// if (ERunActions::runBackground()) {
			try {

				$options = OptionsTools::find(array('backend_forgot_password_tpl'));
				$template_id = isset($options['backend_forgot_password_tpl'])?$options['backend_forgot_password_tpl']:'';				
				
				$admin_token = Yii::app()->input->get("admin_token");
				$model = AR_AdminUser::model()->find("admin_id_token=:admin_id_token AND status=:status",[
					':admin_id_token'=>$admin_token,
					':status'=>'active'
				]);				
				if($model){					
					$site = CNotifications::getSiteData();
					$data = array(		
						'first_name'=>$model->first_name,
						'last_name'=>$model->last_name,						
						'site'=>$site,
						'logo'=>isset($site['logo'])?$site['logo']:'',
						'facebook'=>isset($site['facebook'])?$site['facebook']:'',
						'twitter'=>isset($site['twitter'])?$site['twitter']:'',
						'instagram'=>isset($site['instagram'])?$site['instagram']:'',
						'whatsapp'=>isset($site['whatsapp'])?$site['whatsapp']:'',
						'youtube'=>isset($site['youtube'])?$site['youtube']:'',
						'reset_password_link'=>websiteUrl()."/".BACKOFFICE_FOLDER."/forgotpassword/reset?token=".$model->admin_id_token
					);							
					$this->runActions($template_id, $data , array('email') , array(					     
						'email'=>$model->email_address,
					));
				}
				
			} catch (Exception $e) {
			    $this->msg[] = t($e->getMessage());			    			    			    
			}				
		//}
	}
	
	public function actionmerchantpassword()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->merchantpassword();
			}		
		} else {
			$this->merchantpassword();
		}
	}

	public function merchantpassword()
	{
		// Yii::import('ext.runactions.components.ERunActions');
		// if (ERunActions::runBackground()) {
			try {

				$options = OptionsTools::find(array('backend_forgot_password_tpl'));
				$template_id = isset($options['backend_forgot_password_tpl'])?$options['backend_forgot_password_tpl']:'';				
				
				$user_uuid = Yii::app()->input->get("user_uuid");
				
				$model = AR_merchant_login::model()->find("user_uuid=:user_uuid AND status=:status",[
					':user_uuid'=>$user_uuid,
					':status'=>'active'
				]);	
				if($model){				
					$site = CNotifications::getSiteData();
					$data = array(		
						'first_name'=>$model->first_name,
						'last_name'=>$model->last_name,						
						'site'=>$site,
						'logo'=>isset($site['logo'])?$site['logo']:'',
						'facebook'=>isset($site['facebook'])?$site['facebook']:'',
						'twitter'=>isset($site['twitter'])?$site['twitter']:'',
						'instagram'=>isset($site['instagram'])?$site['instagram']:'',
						'whatsapp'=>isset($site['whatsapp'])?$site['whatsapp']:'',
						'youtube'=>isset($site['youtube'])?$site['youtube']:'',
						'reset_password_link'=>websiteUrl()."/".BACKOFFICE_FOLDER."/resetpswd/reset?token=".$model->user_uuid
					);		
					$this->runActions($template_id, $data , array('email') , array(					     
						'email'=>$model->contact_email,
					));
				}				
			} catch (Exception $e) {
			    $this->msg[] = t($e->getMessage());			    			    			    
			}	
			dump($this->msg);			
		//}
	}

	public function actionresend_otp()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->resend_otp();
			}		
		} else {
			$this->resend_otp();
		}
	}

	public function resend_otp()
	{
		// Yii::import('ext.runactions.components.ERunActions');
		// if (ERunActions::runBackground()) {
			try {
				
				$options = OptionsTools::find(array('signup_verification_tpl'));															
				$template_id = isset($options['signup_verification_tpl'])?$options['signup_verification_tpl']:'';

				$client_uuid = Yii::app()->input->get("client_uuid");		
				$verification_type =  Yii::app()->input->get("verification_type");				
				$model = AR_client::model()->find("client_uuid=:client_uuid",array(
					':client_uuid'=>$client_uuid
			   ));
			   if($model){
					$site = CNotifications::getSiteData();						  
					$data = array(		
					'first_name'=>$model->first_name,
					'last_name'=>$model->last_name,
					'email_address'=>$model->email_address,
					'code'=>$model->mobile_verification_code,
					'site'=>$site,
					'logo'=>isset($site['logo'])?$site['logo']:'',
					'facebook'=>isset($site['facebook'])?$site['facebook']:'',
					'twitter'=>isset($site['twitter'])?$site['twitter']:'',
					'instagram'=>isset($site['instagram'])?$site['instagram']:'',
					'whatsapp'=>isset($site['whatsapp'])?$site['whatsapp']:'',
					'youtube'=>isset($site['youtube'])?$site['youtube']:'',
					);	
					$this->runActions($template_id, $data , array($verification_type) , array(
					   'phone'=>$model->contact_phone,
					   'email'=>$model->email_address
					));
			   }

			} catch (Exception $e) {
			    $this->msg[] = t($e->getMessage());			    			    			    
			}	
			dump($this->msg);
			Yii::log( json_encode($this->msg) , CLogger::LEVEL_ERROR);
		//}
	}

	public function actionsenddevicepush()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->senddevicepush();
			}		
		} else {
			$this->senddevicepush();
		}
	}

	public function senddevicepush()
	{
		$current_url = CommonUtility::getCurrentURL();					
        CommonUtility::saveCronURL($current_url);
		// Yii::import('ext.runactions.components.ERunActions');
		// if (ERunActions::runBackground()) {
			try {

				$json_path = AttributesTools::getPushJsonFile();
				$push_settings = Yii::app()->params['push'];
				
				$push_uuid = Yii::app()->input->get("push_uuid");
				$model = AR_push::model()->find("push_uuid=:push_uuid AND status=:status",[
					':push_uuid'=>$push_uuid,
					':status'=>"pending"
				]);

				$success = false;
				
				if($model){														
					$image = '';					
					$device_id = $model->channel_device_id;
					$platform = $model->platform;
					$target = $model->push_type=='broadcast'?'topic':'token';

					if(!empty($model->image)){
						$image = CMedia::getImage($model->image,$model->path);
					}

					// USE SINGLE APP FIREBASE JSON FILE
					if($model->merchant_id>0){
						$json_path = AttributesTools::getMerchantPushJsonFile($model->merchant_id);
					}

										
					$factory = (new Factory)
					->withServiceAccount($json_path);  
					$auth = $factory->createAuth();
					$cloudMessaging = $factory->createMessaging();

					$config = []; $params = [];
					$data = [
						'dialog_title'=>Yii::app()->params['settings']['website_title']
					];
					if($platform=="android"){
						$params = [
							'ttl'=>'3600s',
							'priority'=>"high",
							'notification'=>[
								'title'=>$model->title,
								'body'=>$model->body,
								'icon' => 'stock_ticker_update',
								'color'=>$push_settings['color'],
								'sound'=>$push_settings['sound'],
								'channelId'=>$push_settings['channel'],
								'image'=>$image
							],
						];			
						$config = AndroidConfig::fromArray($params);						
						$message = CloudMessage::withTarget($target, $device_id)			
					    ->withAndroidConfig($config)
						->withData($data)
						;
					} elseif ($platform=="ios"){
						$params = [
							'headers'=>[
								'apns-priority'=>'10',
							],
							'payload'=>[
								'aps'=>[
									'alert'=>[
										'title'=>$model->title,
										'body'=>$model->body,
									],
									'badge'=>1,
									'sound'=>$push_settings['sound']
								]
							]
						];
						$config = ApnsConfig::fromArray($params);
						$message = CloudMessage::withTarget($target, $device_id)			
					    ->withApnsConfig($config)
						->withData($data)
						;
					}
					
					try {
						$result = $cloudMessaging->send($message);		                						
						$response = json_encode($result);
						$this->msg  = $response;	
						$success = true;					
					} catch (Exception $e) {																
						$response = $e->getMessage();
						$success = false;
					}

					$model->status = "process";
					$model->response = $response;
					$model->save();

					// DELETE DEVICE IF FAILED SENDING PUSH
					if(!$success){						
						$model_device = AR_device::model()->find("device_token=:device_token",[
							':device_token'=>$device_id
						]);
						if($model_device){							
							$model_device->delete();
						}
					}

				} else $this->msg[] = "no results";
				
			} catch (Exception $e) {
			    $this->msg[] = t($e->getMessage());			    			    			    
				if($model){	
					$model->status="failed";
					$model->response = $e->getMessage();
					$model->save();
				}
			}	
			dump($this->msg);
			Yii::log( json_encode($this->msg) , CLogger::LEVEL_ERROR);
		//}
		CommonUtility::updateCronURL($current_url);
	}

	public function actiondosplitpayment()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->dosplitpayment();
			}		
		} else {
			$this->dosplitpayment();
		}
	}

	public function dosplitpayment()
	{
		// Yii::import('ext.runactions.components.ERunActions');
		// if (ERunActions::runBackground()) {
			try {
				
				$order_uuid = Yii::app()->input->get("order_uuid");
				$order = COrders::get($order_uuid);
				
				$delivered_status = CEarnings::getDeliveredStatus();				
				if(!in_array( CommonUtility::cleanString($order->status) ,$delivered_status)){				
					throw new Exception( 'order status is not valid' );
				}			
				
				if(Yii::app()->getModule($order->payment_code)){
				    Yii::app()->getModule($order->payment_code)->doSplitPayment($order);
					CEarnings::splitPayout($order);
				}

			} catch (Exception $e) {
			    $this->msg[] = t($e->getMessage());			    			    			    
			}	
			dump($this->msg);
			Yii::log( json_encode($this->msg) , CLogger::LEVEL_ERROR);
		//}
	}
	
	public function actiondocapturepayment()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->docapturepayment();
			}		
		} else {
			$this->docapturepayment();
		}
	}

	public function docapturepayment()
	{
		// Yii::import('ext.runactions.components.ERunActions');
		// if (ERunActions::runBackground()) {
			try {

				$order_uuid = Yii::app()->input->get("order_uuid");
				$order = COrders::get($order_uuid);	
										
				$tracking_stats = AR_admin_meta::getMeta(['tracking_status_process']);								
				$tracking_status_process = isset($tracking_stats['tracking_status_process'])?AttributesTools::cleanString($tracking_stats['tracking_status_process']['meta_value']):'';				
				$accept_status[] = $tracking_status_process;

				if(!in_array( CommonUtility::cleanString($order->status) ,$accept_status)){				
					throw new Exception( 'order status is not valid' );
				}				

				$all_capture = CPayments::getPaymentTypeCapture();						
				if(array_key_exists($order->payment_code,(array)$all_capture)){			
					if(Yii::app()->getModule($order->payment_code)){				
						
						$total = $order->total*100;
						// CHECK IF AMOUNT IS CHANGE						
						if($trans = AR_ordernew_transaction::model()->find("order_id=:order_id",['order_id'=>$order->order_id])){							
							$trans_total = $trans->trans_amount*100;														
							if($total==$trans_total){
								Yii::app()->getModule($order->payment_code)->doCapturePayment($order , 0);
							} else {								
								Yii::app()->getModule($order->payment_code)->doCapturePaymentWithAmount($order , $trans, $total);
							}							
							$order->payment_status = CPayments::paidStatus();
                            $order->save();														
						} else $this->msg[] = t("Payment not found");						
					}				
				}				
				
			} catch (Exception $e) {
			    $this->msg[] = t($e->getMessage());			    			    			    
			}	
			dump($this->msg);
			Yii::log( json_encode($this->msg) , CLogger::LEVEL_ERROR);
		//}
	}
	
	public function actionprocesscancelcapture()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->processcancelcapture();
			}		
		} else {
			$this->processcancelcapture();
		}
	}

	public function processcancelcapture()
	{
		// Yii::import('ext.runactions.components.ERunActions');
		// if (ERunActions::runBackground()) {
			try {
				
				$order_uuid = Yii::app()->input->get("order_uuid");	
		        $transaction_id = (integer) Yii::app()->input->get("transaction_id");	
				
				$order = COrders::get($order_uuid);
				$transaction = AR_ordernew_transaction::model()->find("transaction_id=:transaction_id 
				AND order_id=:order_id AND status=:status",array(
				':transaction_id'=>intval($transaction_id),
				':order_id'=>intval($order->order_id),
				':status'=>'unpaid'
				));		
				
				if(!$transaction){
					$this->msg[] = "Transaction not found";
				}
				if($transaction){

					$merchant = AR_merchant::model()->findByPk( $order->merchant_id );
					$merchant_type = $merchant?$merchant->merchant_type:'';		   	  
					
					$payment_code = $transaction->payment_code;
					
					$credentials = CPayments::getPaymentCredentials($order->merchant_id,$transaction->payment_code,$merchant_type);		   	  
					$credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';
					$is_live = isset($credentials['is_live'])?intval($credentials['is_live']):0;	
					
					try {

					  $refund_response = Yii::app()->getModule($payment_code)->cancelCapture($credentials,$transaction,$transaction);
					  
					  $transaction->scenario = trim($transaction->transaction_name);
					  $transaction->order_uuid = $order_uuid;
					
					  //$transaction->payment_reference = $refund_response['id'];
					  $transaction->status = "cancelled";
					  $transaction->payment_uuid = $transaction->payment_uuid;
					  $transaction->save();
					
				    } catch (Exception $e) {
						$this->msg[] = t($e->getMessage());		
						$transaction->status = "failed";
						$transaction->reason = $e->getMessage();
						$transaction->save();	   	  
					}
				}				
			} catch (Exception $e) {
			    $this->msg[] = t($e->getMessage());			    			    			    
			}	
			dump($this->msg);
			Yii::log( json_encode($this->msg) , CLogger::LEVEL_ERROR);
		//}
	}
	
	public function actionafterdeliverychangestatus()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->afterdeliverychangestatus();
			}		
		} else {
			$this->afterdeliverychangestatus();
		}
	}

	public function afterdeliverychangestatus()
	{
		
		try {
			

			$driver_data = [];			

			$order_uuid = Yii::app()->input->get('order_uuid');
			$current_status = Yii::app()->input->get('current_status');
			$change_by = Yii::app()->input->get('change_by');
			$remarks = Yii::app()->input->get('remarks');				
			$scenario = Yii::app()->input->get('scenario');

			$order = COrders::get($order_uuid);			
			$driver_data = CDriver::getDriver($order->driver_id);	
							
			$meta = AR_admin_meta::model()->find("meta_value=:meta_value",[
				':meta_value'=>$order->delivery_status
			]);						
			if($meta){										
				$first_name = $driver_data->first_name;					
				$args = [
					'{{order_id}}'=>$order->order_id,
					'{{first_name}}'=>$first_name,
					'{{current_status}}'=>$current_status,
					'{{status}}'=>$order->delivery_status,
					'{{remarks}}'=>$remarks
				];										
				$history = new AR_ordernew_history;
				$history->order_id = $order->order_id;
				$history->status = $order->delivery_status;
				$history->remarks = $meta->meta_value1;
				$history->ramarks_trans = json_encode($args);
				$history->change_by = $change_by;		
				$history->latitude = $driver_data->latitude;		
				$history->longitude = $driver_data->lontitude;					
				if(!$history->save()){
					$this->msg[] = $history->getErrors();
				}

				if($scenario=="delivery_declined"){
					$order->driver_id = 0;
					$order->vehicle_id = 0;
					$order->save();
				}										
			} else $this->msg[] = t("Meta data not found for {{status}}",[
				'{status}'=>$order->delivery_status
			]);							
		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());			    			    			    
		}

		try {				
			$additional_data = [];
			if($driver_data){
				$additional_data = [
					'driver_firstname'=>$driver_data->first_name,
					'driver_last_name'=>$driver_data->last_name,
					'driver_name'=>"$driver_data->first_name $driver_data->last_name",
					'driver_email'=>$driver_data->email,
					'driver_phone_prefix'=>$driver_data->phone_prefix,
					'driver_phone'=>$driver_data->phone,
				];
			}				
			dump($additional_data);
			$this->runOrderActions($order_uuid ,  $order->delivery_status , $additional_data);
			
		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());			    			    			    
		}	
		
		dump("end");
		dump($this->msg);		
	}

	public function actiononassignorder()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->onassignorder();
			}		
		} else {
			$this->onassignorder();
		}
	}

	public function onassignorder()
	{
		try {
			
			$order_uuid = Yii::app()->input->get('order_uuid');		
			//$order = COrders::get($order_uuid);		
			$order = AR_ordernew::model()->find("order_uuid=:order_uuid",[
				':order_uuid'=>$order_uuid
			]);
			
			$meta_activity = AR_admin_meta::getValue('status_assigned');
			$status_assigned = isset($meta_activity['meta_value'])?$meta_activity['meta_value']:'';
			
			if($status_assigned==$order->delivery_status){
				$driver_data = CDriver::getDriver($order->driver_id);		
				// SEND NOTIFICATIONS TO DRIVER APP						
				$noti = new AR_notifications; 
				$noti->notication_channel = $driver_data->driver_uuid;
				$noti->notification_event = Yii::app()->params->realtime['notification_event'];						
				$noti->notification_type = "assign_order";
				$noti->message = json_encode([
					'order_uuid'=>$order->order_uuid
				]);
				$noti->visible = 0;						
				if(!$noti->save()){
					$this->msg[] = $noti->getErrors();
				}	

				// SEND PUSH TO DRIVER APP


				$this->afterdeliverychangestatus();
			}			
		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());			    			    			    
		}	
		dump($this->msg);
	}

	public function actiononthewaytocustomer()
	{
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->onthewaytocustomer();
			}		
		} else {
			$this->onthewaytocustomer();
		}
	}

	public function onthewaytocustomer()
	{
		try {
			
			$order_uuid = Yii::app()->input->get('order_uuid');		
			$order = COrders::get($order_uuid);
			
			$meta = AR_admin_meta::getValue('tracking_status_in_transit');
			$status = isset($meta['meta_value'])?$meta['meta_value']:'';
			
			if(!empty($status)){
				$order->scenario = "change_status";
			    $order->status = $status;
			    $order->save();
				sleep(1);
				$this->afterdeliverychangestatus();
			}

		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());			    			    			    
		}	
		dump($this->msg);
	}

	public function actiononarrivedtocustomer()
	{
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->onarrivedtocustomer();
			}		
		} else {
			$this->onarrivedtocustomer();
		}
	}

	public function onarrivedtocustomer()
	{
		try {
			
			$order_uuid = Yii::app()->input->get('order_uuid');		
			$order = COrders::get($order_uuid);
			
			$option_data = OptionsTools::find(['driver_enabled_delivery_otp','driver_order_otp_tpl']);
			$enabled_delivery_otp = isset($option_data['driver_enabled_delivery_otp'])?$option_data['driver_enabled_delivery_otp']:false;
			$enabled_delivery_otp = $enabled_delivery_otp==1?true:false;				
			$tpl_id = isset($option_data['driver_order_otp_tpl'])?$option_data['driver_order_otp_tpl']:0;

			if($enabled_delivery_otp==TRUE && $tpl_id>0){
				$customer = ACustomer::get($order->client_id);
				$driver_data = CDriver::getDriver($order->driver_id);
				$client_uuid = $customer->client_uuid;
				
				$order_meta = COrders::getMeta($order->order_id,'order_otp');
				$otp_code = $order_meta->meta_value?$order_meta->meta_value:0;
				
				$site = CNotifications::getSiteData();					  
				$data = array(		
					'order_id'=>$order->order_id,
					'driver_name'=>"$driver_data->first_name $driver_data->last_name",
					'code'=>$otp_code,
					'site'=>$site,
					'logo'=>isset($site['logo'])?$site['logo']:'',
					'facebook'=>isset($site['facebook'])?$site['facebook']:'',
					'twitter'=>isset($site['twitter'])?$site['twitter']:'',
					'instagram'=>isset($site['instagram'])?$site['instagram']:'',
					'whatsapp'=>isset($site['whatsapp'])?$site['whatsapp']:'',
					'youtube'=>isset($site['youtube'])?$site['youtube']:'',
				);			
				$meta_data = [
					'order_id'=>$order->order_id,
					'order_uuid'=>$order->order_uuid,
				];

				$this->runActions($tpl_id, $data , array('sms','email','push') , array(
					'phone'=>$customer->contact_phone,
					'email'=>$customer->email_address,
				),array(
					'channel'=>$client_uuid,
					'type'=>'order_update',
					'event'=>Yii::app()->params->realtime['notification_event'],
					'meta_data'=>$meta_data,
					'icon_name'=>'zmdi zmdi-shopping-basket'
				),array(
					'channel'=>$client_uuid,
					'type'=>'order_update',			      
				));			

			}
		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());			    			    			    
		}	
		dump($this->msg);
	}

	public function actionautoassign()
	{		
		$debug = Yii::app()->input->get("debug");
		if($debug=="true"){
			$this->autoassign();
		} else {
			Yii::import('ext.runactions.components.ERunActions');
			if($this->runactions_enabled){
				if (ERunActions::runBackground()) {
					$this->autoassign();
				}		
			} else {
				$this->autoassign();
			}
		}		
	}

	public function autoassign()
	{
		try {
			
			$current_url = CommonUtility::getCurrentURL();					
            CommonUtility::saveCronURL($current_url);
									
			Yii::log( json_encode("autoassign") , CLogger::LEVEL_ERROR);			

			$options = OptionsTools::find(['driver_enabled_auto_assign','driver_assign_when_accepted','driver_allowed_number_task','driver_on_demand_availability']);			
			$enabled_auto_assign = isset($options['driver_enabled_auto_assign'])?$options['driver_enabled_auto_assign']:'';
			$assign_when_accepted = isset($options['driver_assign_when_accepted'])?$options['driver_assign_when_accepted']:'';
			$allowed_number_task = isset($options['driver_allowed_number_task'])?$options['driver_allowed_number_task']:'';				
			$on_demand_availability = isset($options['driver_on_demand_availability'])? ($options['driver_on_demand_availability']==1?true:false) :false;					
			dump("on_demand_availability=>$on_demand_availability");

			$tracking_stats = AR_admin_meta::getMeta(['tracking_status_process','status_new_order','status_delivery_delivered']);											
			$processing_status = isset($tracking_stats['tracking_status_process'])?AttributesTools::cleanString($tracking_stats['tracking_status_process']['meta_value']):'';
			$status_new_order = isset($tracking_stats['status_new_order'])?AttributesTools::cleanString($tracking_stats['status_new_order']['meta_value']):'';
			$status_delivery_delivered = isset($tracking_stats['status_delivery_delivered'])?AttributesTools::cleanString($tracking_stats['status_delivery_delivered']['meta_value']):'';			

			if($enabled_auto_assign!=1){
				die();
			}
			
			$allowed_number_task = 1;
			
			$meta = AR_admin_meta::getValue('status_assigned');
            $status_assigned = isset($meta['meta_value'])?$meta['meta_value']:''; 

			$order_uuid = Yii::app()->input->get('order_uuid');
			$order = COrders::get($order_uuid);
			$order_status = AttributesTools::cleanString($order->status);
						
			if($assign_when_accepted==1){				
				if($order_status!=$processing_status){
					die("Status not accepted by merchant");
				}
			}

			if($order->driver_id>0){
				die("Already have driver assigned");
			}

			$current_url = CommonUtility::getCurrentURL();					
			CommonUtility::saveCronURL($current_url);

			$merchant_id = $order->merchant_id;			
			$merchant_info = COrders::getMerchant($merchant_id,Yii::app()->language);

			$options_merchant = OptionsTools::find(['self_delivery'],$merchant_id);			
			$self_delivery = isset($options_merchant['self_delivery'])?$options_merchant['self_delivery']:0;
			$self_delivery = $self_delivery==1?true:false;
			$merchant_id_owner = $self_delivery==true?$merchant_id:0;
			dump("self_delivery=>$self_delivery");
			dump("merchant_id=>$merchant_id");
			dump("merchant_id_owner=>$merchant_id_owner");
			
			$merchant_zone = []; $zone_query = '';
			if($zone_data = CMerchants::getListMerchantZone([$merchant_id],false)){
				$merchant_zone = isset($zone_data[$merchant_id])?$zone_data[$merchant_id]:'';									
				$zone_query = CommonUtility::arrayToQueryParameters($merchant_zone);
			} else $zone_query = CommonUtility::arrayToQueryParameters([0]);

			$merchant_latitude = isset($merchant_info['latitude'])?$merchant_info['latitude']:'';
			$merchant_longitude = isset($merchant_info['longitude'])?$merchant_info['longitude']:'';

			$unit = Yii::app()->params['settings']['home_search_unit_type'];
			$distance_exp = CMerchantListingV1::getDistanceExp(array('unit'=>$unit));	

			$assigned_group = AOrders::getOrderTabsStatus('assigned');			
			$active_status = CommonUtility::arrayToQueryParameters($assigned_group);
			
			$now = date("Y-m-d");

			$filter = [
				'now'=>$now,			
				'merchant_id'=>$merchant_id,
				'latitude'=>$merchant_latitude,
				'longitude'=>$merchant_longitude,
				'unit'=>$unit,
				'distance_exp'=>$distance_exp
			];

			if(!$on_demand_availability){				
				$stmt = "
				SELECT a.driver_id,a.first_name,
				a.delivery_distance_covered,
				(
					select count(*) from {{ordernew}}
					where driver_id = a.driver_id		
					and delivery_date = ".q($now)."
					and delivery_status IN ($active_status)		
				) as active_task,

				(
					select count(*) from {{ordernew}}
					where driver_id = a.driver_id		
					and delivery_date = ".q($now)."
					and delivery_status IN (".q($status_delivery_delivered).")		
				) as total_delivered
				,
				( $filter[distance_exp] * acos( cos( radians($filter[latitude]) ) * cos( radians( latitude ) ) 
				* cos( radians( lontitude ) - radians($filter[longitude]) ) 
				+ sin( radians($filter[latitude]) ) * sin( radians( latitude ) ) ) ) 
				AS distance
				FROM {{driver}} a				
				WHERE a.status='active'			
				AND a.merchant_id=".q($merchant_id_owner)."		
				AND a.driver_id IN (
					select driver_id from {{driver_schedule}}
					where driver_id = a.driver_id
					and DATE(time_start) BETWEEN ".q($filter['now'])." AND ".q($filter['now'])." 
					and DATE(shift_time_started) IS NOT NULL  
					and DATE(shift_time_ended) IS NULL  
					and active=1
					and zone_id IN ($zone_query)
				)
				HAVING distance < a.delivery_distance_covered
				AND ".$allowed_number_task." > active_task
				ORDER BY active_task ASC, total_delivered ASC , distance ASC
				LIMIT 0,5
				";
			} else {									
				$stmt = "
				SELECT a.driver_id,a.first_name,
				a.delivery_distance_covered,
				(
					select count(*) from {{ordernew}}
					where driver_id = a.driver_id		
					and delivery_date = ".q($now)."
					and delivery_status IN ($active_status)		
				) as active_task,

				(
					select count(*) from {{ordernew}}
					where driver_id = a.driver_id		
					and delivery_date = ".q($now)."
					and delivery_status IN (".q($status_delivery_delivered).")		
				) as total_delivered,

				( $filter[distance_exp] * acos( cos( radians($filter[latitude]) ) * cos( radians( latitude ) ) 
				* cos( radians( lontitude ) - radians($filter[longitude]) ) 
				+ sin( radians($filter[latitude]) ) * sin( radians( latitude ) ) ) ) 
				AS distance
				
				FROM {{driver}} a				
			    WHERE a.status='active'		
				AND a.is_online=1
				AND a.driver_id IN (
					select driver_id from {{driver_schedule}}
					where 
					merchant_id=".q($merchant_id_owner)." and driver_id = a.driver_id 
					and on_demand=1 and zone_id IN ($zone_query)
				)
				HAVING distance < a.delivery_distance_covered
				AND ".$allowed_number_task." > active_task				
				ORDER BY active_task ASC, total_delivered ASC , distance ASC
				LIMIT 0,5	
				";				
			}									
			if($res = Yii::app()->db->createCommand($stmt)->queryAll()){												
				foreach ($res as $items) {					
					$order->scenario = "assign_order";
					$order->on_demand_availability = $on_demand_availability;
					$order->driver_id = intval($items['driver_id']);
					$order->delivered_old_status = $order->delivery_status;
					$order->delivery_status = $status_assigned;
					$order->change_by = $items['first_name'];
					$order->date_now = date("Y-m-d");
					$order->allowed_number_task = intval($allowed_number_task);
					try {						
						$vehicle = CDriver::getVehicleAssign($items['driver_id'],$now);
						$order->vehicle_id = $vehicle->vehicle_id;
					} catch (Exception $e) {
						Yii::log( json_encode($e->getMessage()) , CLogger::LEVEL_ERROR);			    			    			    
					}                        
					if($order->save()){
						Yii::log( json_encode("Successfully assigned") , CLogger::LEVEL_ERROR);
						dump("Successfully assigned");
						break;
					} else {
						$this->msg = $order->getErrors();
						Yii::log( json_encode("failed assigned") , CLogger::LEVEL_ERROR);
					}
				}
			} else {
				$this->msg = "auto assign no results";
			}
		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());		
			Yii::log( json_encode($e->getMessage()) , CLogger::LEVEL_ERROR);			    			    			    
		}	
		dump($this->msg);
		
		CommonUtility::updateCronURL($current_url);
	}

	public function actionafterdriver_register()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->afterdriver_register();
			}		
		} else {
			$this->afterdriver_register();
		}
	}

	public function afterdriver_register()
	{
		// Yii::import('ext.runactions.components.ERunActions');
		// if (ERunActions::runBackground()) {
			try {

				$driver_uuid = Yii::app()->input->get('driver_uuid');
				$model = CDriver::getDriverByUUID($driver_uuid);

				if($model->verification_code<=0){
				   $digit_code = CommonUtility::generateNumber(3,true);
				   $model->verification_code = $digit_code;
			       $model->verify_code_requested = CommonUtility::dateNow();
				   $model->save();
				}	

				$data = [
					'code'=>$model->verification_code
				];				
				$options = OptionsTools::find(['driver_sendcode_via','driver_sendcode_tpl']);				
				$sendcode_via  = isset($options['driver_sendcode_via'])?$options['driver_sendcode_via']:'mobilex';
				$tpl_id  = isset($options['driver_sendcode_tpl'])?$options['driver_sendcode_tpl']:0;				
				
				$to = [
					'email_address'=>$model->email,
					'name'=>$model->first_name,
					'mobile_phone'=>$model->phone_prefix.$model->phone
				];
				$this->processTemplate($tpl_id,array($sendcode_via),$data,$to);
				
			} catch (Exception $e) {
			    $this->msg[] = t($e->getMessage());			    			    			    
			}				
			Yii::log( json_encode($this->msg) , CLogger::LEVEL_ERROR);
		//}
	}

	private function processTemplate($template_id=0, $template_type= array(), $data=array(), $to=array())
	{
		$message_parameters = array(); $sms_vars = [];

		$path = Yii::getPathOfAlias('backend_webroot')."/twig"; 
	    $loader = new \Twig\Loader\FilesystemLoader($path);
	    $twig = new \Twig\Environment($loader, [
		    'cache' => $path."/compilation_cache",
		    'debug'=>true
		]);
		
		$templates = CTemplates::get($template_id, $template_type , Yii::app()->language );
		
		if(is_array($data) && count($data)>=1){
			foreach ($data as $data_key=>$data_value) {				
				$message_parameters["{{{$data_key}}}"]=$data_value;
				$sms_vars[$data_key] = $data_value;
			}
		}		

		foreach ($templates as $items) {
			$email_subject = ''; $template = ''; $email_subject=''; $sms_template='';
			$push_template = ''; $push_title=''; $sms_template_id = '';
			
			if($items['template_type']=="email" && $items['enabled_email']==1 ){
				
				$email_subject = isset($items['title'])?$items['title']:'';
	    		$template = isset($items['content'])?$items['content']:'';
	    		$twig_template = $twig->createTemplate($template);
	    		$template = $twig_template->render($data);    			    		
	    		
	    		$twig_subject = $twig->createTemplate($email_subject);
                $email_subject = $twig_subject->render($data);                 

				$resp = CommonUtility::sendEmail(isset($to['email_address'])?$to['email_address']:'',
				isset($to['name'])?$to['name']:'',
				$email_subject,$template
			    );

			} else if ($items['template_type']=="sms" && $items['enabled_sms']==1  ) {
				$sms_template = isset($items['content'])?$items['content']:'';
				$sms_template_id = isset($items['sms_template_id'])?$items['sms_template_id']:'';
			    $twig_sms = $twig->createTemplate($sms_template);			    
                $sms_template = $twig_sms->render($data);                                          

				$resp = CommonUtility::sendSMS( isset($to['mobile_phone'])?$to['mobile_phone']:'' ,$sms_template,0,0,'',$sms_template_id,$sms_vars);

			} else if ($items['template_type']=="push" && $items['enabled_push']==1  ) {				
				$push_template = isset($items['content'])?$items['content']:'';			    			    
				$push_title = isset($items['title'])?$items['title']:'';
			}		
			

			if($resp){
				return true;
			} else throw new Exception("Sending failed");
			
		}
	}
	
	public function actionsendbankinstructions()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->sendbankinstructions();
			}		
		} else {
			$this->sendbankinstructions();
		}
	}

	public function sendbankinstructions()
	{
		// Yii::import('ext.runactions.components.ERunActions');
		// if (ERunActions::runBackground()) {
			try {
				
				$order_uuid = Yii::app()->input->get("order_uuid");							
				$model = COrders::get($order_uuid);				
				$merchant_type = CMerchants::getMerchantType($model->merchant_id);				
				$tpl_data = CPayments::getBankDepositInstructions($merchant_type,$model->merchant_id);				
				$client = ACustomer::get($model->client_id);

				
				$tpl = isset($tpl_data['content'])?$tpl_data['content']:'';
				$subject = isset($tpl_data['subject'])?$tpl_data['subject']:'';				

				$path = Yii::getPathOfAlias('backend_webroot')."/twig"; 
				$loader = new \Twig\Loader\FilesystemLoader($path);
				$twig = new \Twig\Environment($loader, [
					'cache' => $path."/compilation_cache",
					'debug'=>true
				]);

				$data = [
					'first_name'=>$client->first_name,
					'amount'=>Price_Formatter::formatNumberNoSymbol($model->total),
					'upload_deposit_url'=>Yii::app()->createAbsoluteUrl("/orders/upload_deposit",[
						   'order_uuid'=>$model->order_uuid
					])
				];

				$twig_template = $twig->createTemplate($tpl);
				$template = $twig_template->render($data);				
			
				
				$customer_name = $client->first_name." ".$client->last_name;
				
			    $resp = CommonUtility::sendEmail($client->email_address,$customer_name,$subject,$template);
				
			} catch (Exception $e) {
			    $this->msg[] = t($e->getMessage());			    			    			    
			}				
			Yii::log( json_encode($this->msg) , CLogger::LEVEL_ERROR);
		//}
	}

	public function actionaftercontactfilled()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->aftercontactfilled();
			}		
		} else {
			$this->aftercontactfilled();
		}
	}

	public function aftercontactfilled()
	{
		// Yii::import('ext.runactions.components.ERunActions');
		// if (ERunActions::runBackground()) {
			try {

				$options = OptionsTools::find(["contact_us_tpl"]);
				$template_id = isset($options['contact_us_tpl'])?$options['contact_us_tpl']:0;				
				$id = Yii::app()->input->get('id');				
				$model = AR_contact::model()->findByPk($id);
				if($model && $template_id>0){
					$site = CNotifications::getSiteData();
					$data = [
						'site'=>$site,
						'email_address'=>$model->email_address,
						'fullname'=>$model->fullname,
						'contact_number'=>$model->contact_number,
						'country_name'=>$model->country_name,
						'message'=>$model->message,
					];
					$this->runActions($template_id, $data , array('sms','email','push') , array(
						'phone'=>'',
						'email'=>$model->receiver_email_address,
					  ),array(
						 'channel'=>Yii::app()->params->realtime['admin_channel'],
						 'type'=>'contact_us',
						 'event'=>Yii::app()->params->realtime['notification_event'],
					   ),array(
						 'channel'=>Yii::app()->params->realtime['admin_channel'],
						 'type'=>'contact_us',			      
					));				
				}				
			} catch (Exception $e) {
			    $this->msg[] = t($e->getMessage());			    			    			    
			}	
			dump($this->msg);
			Yii::log( json_encode($this->msg) , CLogger::LEVEL_ERROR);
		//}
	}
	
	public function actionautoprint()
	{
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->autoprint();
			}		
		} else {
			$this->autoprint();
		}
	}

	public function autoprint()
	{
		try {
			
			$current_url = CommonUtility::getCurrentURL();					
            CommonUtility::saveCronURL($current_url);
			
			$order_id = 0;
			$summary = array(); $order_status = array();                                
			$order_delivery_status = array(); $merchant_info=array();
			$order = array(); $order_items = array(); 

			$order_uuid = Yii::app()->input->get('order_uuid');					
			COrders::getContent($order_uuid,Yii::app()->language);                
			$merchant_id = COrders::getMerchantId($order_uuid);
			
			$merchant_info = COrders::getMerchant($merchant_id,Yii::app()->language);				
			$order_items = COrders::getItems();				
			$summary = COrders::getSummary();
			$order = COrders::orderInfo();
			$order_id = $order['order_info']['order_id'];

			$merchant_ids = array();
			$merchant_ids[] = $merchant_id;			
			$merchant_ids[] = 0;			
			$criteria=new CDbCriteria();
			$criteria->addCondition("auto_print=:auto_print");
			$criteria->params = [':auto_print'=>1];
			$criteria->addInCondition('merchant_id',$merchant_ids);
			
			if($model = AR_printer::model()->findAll($criteria)){				
				foreach ($model as $items) {
					$meta = AR_printer_meta::getMeta($items->printer_id,['printer_user','printer_ukey','printer_sn','printer_key']);				
					$printer_user = isset($meta['printer_user'])?$meta['printer_user']['meta_value1']:'';
					$printer_ukey = isset($meta['printer_ukey'])?$meta['printer_ukey']['meta_value1']:'';
					$printer_sn = isset($meta['printer_sn'])?$meta['printer_sn']['meta_value1']:'';
					$printer_key = isset($meta['printer_key'])?$meta['printer_key']['meta_value1']:'';

					$credit_card_details = '';
					$payment_code = $order['order_info']['payment_code'];
					if($payment_code=="ocr"){
						try {
							$credit_card_details = COrders::getCreditCard2($order_id);			
							$order['order_info']['credit_card_details'] = $credit_card_details;		
						} catch (Exception $e) {
							//
						}
					}		
					
					$tpl = FPtemplate::ReceiptTemplate(
						$items->paper_width,
						$order['order_info'],
						$merchant_info,
						$order_items,
						$summary
					);  
					$stime = time();
                    $sig = sha1($printer_user.$printer_ukey.$stime);               
                    $result = FPinterface::Print($printer_user,$stime,$sig,$printer_sn,$tpl);
					dump($result);

					$logs = new AR_printer_logs();
					$logs->order_id = intval($order_id);
					$logs->merchant_id = intval($items->merchant_id);
					$logs->printer_number = $printer_sn;
					$logs->print_content = $tpl;
					$logs->job_id = $result;
					$logs->status = 'process';
					$logs->save();
				}
			}

		} catch (Exception $e) {
			echo $e->getMessage();
		}

		CommonUtility::updateCronURL($current_url); 
	}

	public function actionafterdelivered()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->afterdelivered();
			}		
		} else {
			$this->afterdelivered();
		}		
	}

	public function afterdelivered()
	{
		try {
			
			
			Yii::log( json_encode("afterdelivered") , CLogger::LEVEL_ERROR);
			$current_url = CommonUtility::getCurrentURL();					
            CommonUtility::saveCronURL($current_url);

			$order_uuid = Yii::app()->input->get('order_uuid');			
			$scenario = Yii::app()->input->get('scenario');
			//$order = COrders::get($order_uuid);
			$order = AR_ordernew::model()->find("order_uuid=:order_uuid",[
				':order_uuid'=>$order_uuid
			]);

			/*UPDATE PAYMENT IF DELIVERED USING OFFLINE PAYMENT */
			if($scenario=="orderdelivered"){
				try {			
					Yii::log( "Update order status to paid" , CLogger::LEVEL_ERROR);		
					Yii::app()->db->createCommand("UPDATE {{ordernew}} SET payment_status='paid' WHERE order_uuid=".q($order_uuid)." ")->query();
					AR_ordernew_transaction::model()->updateAll(array('status'=>'paid'),
						'order_id=:order_id',array(':order_id'=>$order->order_id)
					);			
				} catch (Exception $e) {
					//echo $e->getMessage();
				}		
		    }
			
			//CHECK IF ALREADY PROCESS
			$wallet_model = AR_wallet_transactions_meta::model()->find("meta_name=:meta_name AND meta_value=:meta_value",[
				':meta_name'=>"rider_wallet_process",
				':meta_value'=>$order->order_id
			]);
			if($wallet_model){
				CommonUtility::updateCronURL($current_url);
				die("Order already process");
			}			

			if($scenario=="orderdelivered"){
				$meta = AR_admin_meta::getValue('status_delivered');
			} else if ($scenario=="delivery_failed") {
				$meta = AR_admin_meta::getValue('status_delivery_fail');
			}
			$status = isset($meta['meta_value'])?$meta['meta_value']:'';
			if(!empty($status)){
				$order->scenario = "change_status";
			    $order->status = $status;
			    $order->save();			
			}			
			

			//dump($order->payment_code);
			// dump($order->total);			
			// dump($order->courier_tip);
			$amount = $order->total;
			$card_id = CWallet::getCardID( Yii::app()->params->account_type['driver'] , $order->driver_id);	
			$all_online = CPayments::getPaymentTypeOnline();					

			$driver_data = CDriver::getDriver($order->driver_id);
			$employment_type = $driver_data->employment_type;
			$salary_type = $driver_data->salary_type;		

			
			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
            $multicurrency_enabled = $multicurrency_enabled==1?true:false;		

			$exchange_rate = 1; $exchange_rate_merchant_to_admin = 1; $exchange_rate_admin_to_merchant=1;
			$order_base_currency = $order->base_currency_code;
			$admin_base_currency = $order->admin_base_currency;			
			$driver_default_currency = $driver_data->default_currency;
			$driver_default_currency = !empty($driver_default_currency)?$driver_default_currency:$order_base_currency;

			dump("multicurrency_enabled=>$multicurrency_enabled");
			dump("admin_base_currency=>$admin_base_currency");
			dump("driver_default_currency=>$driver_default_currency");		
				
			if($multicurrency_enabled && $admin_base_currency!=$driver_default_currency){
				$exchange_rate_merchant_to_admin = CMulticurrency::getExchangeRate($driver_default_currency,$admin_base_currency);
				$exchange_rate_admin_to_merchant = CMulticurrency::getExchangeRate($admin_base_currency,$driver_default_currency);
			}
			dump("exchange_rate_merchant_to_admin=>$exchange_rate_merchant_to_admin");
			dump("exchange_rate_admin_to_merchant=>$exchange_rate_admin_to_merchant");			
				
			dump("merchant_id=>$driver_data->merchant_id");
			dump("employment_type=>$employment_type");
			dump("salary_type=>$salary_type");			

			$transaction = []; $params = []; $commission_per_delivery = 0; 
			
			$option_data = OptionsTools::find(['driver_commission_per_delivery']);			
			$commission_per_delivery = isset($option_data['driver_commission_per_delivery'])?floatval($option_data['driver_commission_per_delivery']):0;
			$commission_per_delivery = $commission_per_delivery>0?($commission_per_delivery/100):0;			
									

			if(array_key_exists($order->payment_code,(array)$all_online)){			
				// ONLINE	
				//if($employment_type=="contractor"){
					if($salary_type=="delivery_fee"){
						$transaction[] = 'payout_delivery_fee';		
						if($commission_per_delivery>0){
							$transaction[] = 'platform_delivery_commission';
						}				
					} else if ($salary_type=="commission"){
						$transaction[] = 'payout_commission';
					} else if ($salary_type=="fixed"){
						$transaction[] = 'payout_fixed';
					} else if ($salary_type=="fixed_and_commission"){
						$transaction[] = 'payout_fixed_and_commission';
					}

					if($salary_type!="salary"){
						if($order->courier_tip>0){
							$transaction[] = 'payout_tip';
						}
						if($driver_data->incentives_amount>0){
							$transaction[] = 'payout_incentives';
						}
				    }
				// } else {
				// 	//
				// }
			} else {			
				// OFFLINE				
				//if($employment_type=="contractor"){
					$transaction[] = 'collected_payment';
					if($salary_type=="delivery_fee"){
						$transaction[] = 'payout_delivery_fee';
						if($commission_per_delivery>0){
							$transaction[] = 'platform_delivery_commission';
						}
					} else if ($salary_type=="commission"){
						$transaction[] = 'payout_commission';
					} else if ($salary_type=="fixed"){
						$transaction[] = 'payout_fixed';
					} else if ($salary_type=="fixed_and_commission"){
						$transaction[] = 'payout_fixed_and_commission';
					}
					
					if($salary_type!="salary"){
						if($order->courier_tip>0){
							$transaction[] = 'payout_tip';
						}
						if($driver_data->incentives_amount>0){
							$transaction[] = 'payout_incentives';
						}
				    }
				// } else {
				// 	$transaction[] = 'collected_payment';
				// }
			}			

			dump($transaction);
			
			if(is_array($transaction) && count($transaction)>=1){
				foreach ($transaction as $trans_type) {					
					switch ($trans_type) {
						case "collected_payment":
								$params[] = [
									'transaction_description'=>"Collected payment #{{order_id}}",
									'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id), 
									'transaction_type'=>"debit",
									'transaction_amount'=>floatval($amount),
									'meta_name'=>"collected_payment",
									'meta_value'=>$order->order_id,
									'status'=>"paid",
									'merchant_base_currency'=>$driver_default_currency,
									'admin_base_currency'=>$admin_base_currency,
									'exchange_rate_merchant_to_admin'=>$exchange_rate_merchant_to_admin,
									'exchange_rate_admin_to_merchant'=>$exchange_rate_admin_to_merchant,
								];
							 break;
						case "payout_delivery_fee":		
							if($order->delivery_fee>0){
								$params[] = [
									'transaction_description'=>"Payout delivery fee order #{{order_id}}",
									'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id), 
									'transaction_type'=>"credit",
									'transaction_amount'=>floatval($order->delivery_fee),
									'meta_name'=>"payout_delivery_fee",
									'meta_value'=>$order->order_id,
									'status'=>"paid",
									'merchant_base_currency'=>$driver_default_currency,
									'admin_base_currency'=>$admin_base_currency,
									'exchange_rate_merchant_to_admin'=>$exchange_rate_merchant_to_admin,
									'exchange_rate_admin_to_merchant'=>$exchange_rate_admin_to_merchant,
								];					
							}							
							break;
						case "platform_delivery_commission":
							if($order->delivery_fee>0){
								$params[] = [
									'transaction_description'=>"Platform commission delivery fee #{{order_id}}",
									'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id), 
									'transaction_type'=>"debit",
									'transaction_amount'=>floatval($order->delivery_fee*$commission_per_delivery),
									'meta_name'=>"platform_delivery_commission",
									'meta_value'=>$order->order_id,
									'status'=>"paid",
									'merchant_base_currency'=>$driver_default_currency,
									'admin_base_currency'=>$admin_base_currency,
									'exchange_rate_merchant_to_admin'=>$exchange_rate_merchant_to_admin,
									'exchange_rate_admin_to_merchant'=>$exchange_rate_admin_to_merchant,
								];					
							}							
							break;

						case "payout_tip":
							$params[] = [
								'transaction_description'=>"Payout tips order #{{order_id}}",
								'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id), 
								'transaction_type'=>"credit",
								'transaction_amount'=>floatval($order->courier_tip),
								'meta_name'=>"payout_tip",
								'meta_value'=>$order->order_id,
								'status'=>"paid",
								'merchant_base_currency'=>$driver_default_currency,
								'admin_base_currency'=>$admin_base_currency,
								'exchange_rate_merchant_to_admin'=>$exchange_rate_merchant_to_admin,
								'exchange_rate_admin_to_merchant'=>$exchange_rate_admin_to_merchant,
							];
							break;											
						case "payout_incentives":
							$params[] = [
								'transaction_description'=>"Payout Incentives order #{{order_id}}",
								'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id), 
								'transaction_type'=>"credit",
								'transaction_amount'=>floatval($driver_data->incentives_amount),
								'meta_name'=>"payout_incentives",
								'meta_value'=>$order->order_id,
								'status'=>"paid",
								'merchant_base_currency'=>$driver_default_currency,
								'admin_base_currency'=>$admin_base_currency,
								'exchange_rate_merchant_to_admin'=>$exchange_rate_merchant_to_admin,
								'exchange_rate_admin_to_merchant'=>$exchange_rate_admin_to_merchant,
							];
							break;
						case "payout_commission":
							$params[] = [
								'transaction_description'=>"Payout commission order #{{order_id}}",
								'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id), 
								'transaction_type'=>"credit",
								'transaction_amount'=> $amount * ( $driver_data->commission/100 ),
								'meta_name'=>"payout_commission",
								'meta_value'=>$order->order_id,
								'status'=>"paid",
								'merchant_base_currency'=>$driver_default_currency,
								'admin_base_currency'=>$admin_base_currency,
								'exchange_rate_merchant_to_admin'=>$exchange_rate_merchant_to_admin,
								'exchange_rate_admin_to_merchant'=>$exchange_rate_admin_to_merchant,
							];
							break;
						case "payout_fixed":
							$params[] = [
								'transaction_description'=>"Payout fixed order #{{order_id}}",
								'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id), 
								'transaction_type'=>"credit",
								'transaction_amount'=>floatval($driver_data->fixed_amount),
								'meta_name'=>"payout_fixed",
								'meta_value'=>$order->order_id,
								'status'=>"paid",
								'merchant_base_currency'=>$driver_default_currency,
								'admin_base_currency'=>$admin_base_currency,
								'exchange_rate_merchant_to_admin'=>$exchange_rate_merchant_to_admin,
								'exchange_rate_admin_to_merchant'=>$exchange_rate_admin_to_merchant,
							];
							break;
						case "payout_fixed_and_commission":
							$commission = $amount * ( $driver_data->commission/100 );
							$params[] = [
								'transaction_description'=>"Payout commission+fixed order #{{order_id}}",
								'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id), 
								'transaction_type'=>"credit",
								'transaction_amount'=>floatval($commission) + floatval($driver_data->fixed_amount),
								'meta_name'=>"payout_fixed_and_commission",
								'meta_value'=>$order->order_id,
								'status'=>"paid",
								'merchant_base_currency'=>$driver_default_currency,
								'admin_base_currency'=>$admin_base_currency,
								'exchange_rate_merchant_to_admin'=>$exchange_rate_merchant_to_admin,
								'exchange_rate_admin_to_merchant'=>$exchange_rate_admin_to_merchant,
							];
							break;		
					}
				}
			}
									
			if(is_array($params) && count($params)>=1){
				foreach ($params as $items) {				
					CWallet::inserTransactions($card_id,$items);
				}			
			}			

			$meta_model = new AR_wallet_transactions_meta();
			$meta_model->meta_name="rider_wallet_process";
			$meta_model->meta_value = $order->order_id;
			$meta_model->save();

		} catch (Exception $e) {
			echo $e->getMessage();
			Yii::log( json_encode($e->getMessage()) , CLogger::LEVEL_ERROR);
		}

		CommonUtility::updateCronURL($current_url);
	}

	public function actionaftercashout_cancel()
	{
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->aftercashout_cancel();
			}		
		} else {
			$this->aftercashout_cancel();
		}
	}

	public function actioncashout_reversal()
	{
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->cashout_reversal();
			}		
		} else {
			$this->cashout_reversal();
		}
	}

	public function cashout_reversal()
	{
		try {

			$transaction_uuid = Yii::app()->input->get('transaction_uuid');
			$model = AR_wallet_transactions::model()->find("transaction_uuid=:transaction_uuid",[
				':transaction_uuid'=>$transaction_uuid
			]);
			if($model){				
				CWallet::reversalFee($model->card_id,$model->transaction_id);
			}
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}

	public function actionaftertimeoutacceptorder()
	{
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->timeoutacceptorder();
			}		
		} else {
			$this->timeoutacceptorder();
		}
	}

	public function timeoutacceptorder()
	{
		try {

			$order_uuid = Yii::app()->input->get('order_uuid');
			$driver_id = Yii::app()->input->get('driver_id');
			$order = COrders::get($order_uuid);			
			$driver = CDriver::getDriver($driver_id);

			$site = CNotifications::getSiteData();						  
			$data = array(		
			  'order_id'=>$order->order_id,
			  'driver_name'=>"$driver->first_name $driver->last_name",
			  'site'=>$site,
			  'logo'=>isset($site['logo'])?$site['logo']:'',
			  'facebook'=>isset($site['facebook'])?$site['facebook']:'',
			  'twitter'=>isset($site['twitter'])?$site['twitter']:'',
			  'instagram'=>isset($site['instagram'])?$site['instagram']:'',
			  'whatsapp'=>isset($site['whatsapp'])?$site['whatsapp']:'',
			  'youtube'=>isset($site['youtube'])?$site['youtube']:'',
			);					

			$meta_data = [
			   'order_id'=>$order->order_id,
			   'order_uuid'=>$order->order_uuid,
			];
				
			$options = OptionsTools::find(['driver_missed_order_tpl','admin_email_alert','admin_mobile_alert']);
			$template_id = isset($options['driver_missed_order_tpl'])?$options['driver_missed_order_tpl']:'';
			$phone = isset($options['admin_mobile_alert'])?$options['admin_mobile_alert']:'';
			$email = isset($options['admin_email_alert'])?$options['admin_email_alert']:'';
			
			//SEND NOTIFICATIONS TO ADMIN
			$this->runActions($template_id, $data , array('sms','email','push') , array(
				'phone'=>$phone,
				'email'=>$email,
			   ),array(
				 'channel'=>Yii::app()->params->realtime['admin_channel'],
				 'type'=>'order_update',
				 'event'=>Yii::app()->params->realtime['notification_event'],
				 'meta_data'=>$meta_data,
				 'icon_name'=>'zmdi zmdi-shopping-basket'
			   ),array(
				 'channel'=>Yii::app()->params->realtime['admin_channel'],
				 'type'=>'order_update',			      
			));			

			// SEND NOTIFICATOONS TO MERCHANT
			$find = array('merchant_enabled_alert','merchant_email_alert','merchant_mobile_alert');
			if($merchant_opts = OptionsTools::find($find,$order->merchant_id)){
				$merchant_email = isset($merchant_opts['merchant_email_alert'])?$merchant_opts['merchant_email_alert']:'';
		    	$merchant_mobile = isset($merchant_opts['merchant_mobile_alert'])?$merchant_opts['merchant_mobile_alert']:'';
				$merchant_enabled_alert = isset($merchant_opts['merchant_enabled_alert'])?$merchant_opts['merchant_enabled_alert']:'';
				if($merchant_enabled_alert){
					$merchant = CMerchants::get($order->merchant_id);					
					$this->runActions($template_id, $data , array('sms','email','push') , array(
						'phone'=>$merchant_mobile,
						'email'=>$merchant_email,
					),array(
						'channel'=>$merchant->merchant_uuid,
						'type'=>'order_update',
						'event'=>Yii::app()->params->realtime['notification_event'],
						'meta_data'=>$meta_data,
						'icon_name'=>'zmdi zmdi-shopping-basket'
					),array(
						'channel'=>$merchant->merchant_uuid,
						'type'=>'order_update',			      
					));			
			    }
			}
			
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}

	public function actiondeliverychecktimeoutaccepted()
	{
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->deliverychecktimeoutaccepted();
			}		
		} else {
			$this->deliverychecktimeoutaccepted();
		}
	}

	public function deliverychecktimeoutaccepted()
	{
		
		$options = OptionsTools::find(['driver_time_allowed_accept_order','driver_enabled_time_allowed_acceptance']);
		$enabled_acceptance = isset($options['driver_enabled_time_allowed_acceptance'])? ($options['driver_enabled_time_allowed_acceptance']==1?true:false):false;
		$time_acceptance = isset($options['driver_time_allowed_accept_order'])?($options['driver_time_allowed_accept_order']>0?$options['driver_time_allowed_accept_order']:45):45;		
		try {			
			if($enabled_acceptance){				
				sleep($time_acceptance);
				$order_uuid = Yii::app()->input->get('order_uuid');
				$model = COrders::get($order_uuid);			
				$old_driver_id = $model->driver_id;
				if($model->driver_id>0 && $model->delivery_status=="assigned"){				
					$model->driver_id = 0;
					$model->vehicle_id = 0;
					$model->delivery_status = 'unassigned';
					if($model->save()){					
						$_GET['order_uuid'] = $model->order_uuid;
						$_GET['driver_id'] = $old_driver_id;
						$this->timeoutacceptorder();
					}
				} else echo "order is not assigned anymore";			
		    } else echo "timeout acceptance not enabled";
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}

	public function actionRuncron()
	{
		CommonUtility::mysqlSetTimezone();
		
		$stmt="DELETE FROM {{cron}}
		WHERE date_created < now() - interval 2 DAY
		";
		Yii::app()->db->createCommand($stmt)->query();
		$this->runcron();
	}

	public function runcron()
	{
		$criteria=new CDbCriteria();		
		$criteria->addCondition("status=:status");
		$criteria->params = [':status'=>0];
		$criteria->order = "cron_id ASC";
		$criteria->limit = 2;
		if($model = AR_cron::model()->findAll($criteria)){			
			foreach ($model as $items) {
				$new_url = $items->url."&cron=1";
				$new_url = str_replace("&_runaction_touch=1","",$new_url);
				//dump($new_url);
				CommonUtility::runActions($new_url);
				$items->status = 1;
				$items->save();
				sleep(1);
			}
		} else {
			//dump("no results");
			AR_cron::model()->deleteAll('status=:status',array(
				':status'=>1
			));
		}
	}
	
	public function actionafterdriver_requestresetpassword()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->afterdriver_requestresetpassword();
			}		
		} else {
			$this->afterdriver_requestresetpassword();
		}		
	}

	public function afterdriver_requestresetpassword()
	{
		try {
			
			$driver_uuid = Yii::app()->input->get("driver_uuid");	
			$model = CDriver::getDriverByUUID($driver_uuid);
			if($model){

				$options = OptionsTools::find(array('signup_resetpass_tpl'));
				$template_id = isset($options['signup_resetpass_tpl'])?$options['signup_resetpass_tpl']:'';	
				
				if($model->status=="active"){
					$site = CNotifications::getSiteData();
						$data = array(		
					      'first_name'=>$model->first_name,
					      'last_name'=>$model->last_name,
					      'email_address'=>$model->email,
					      'code'=>$model->verification_code,
					      'site'=>$site,
					      'logo'=>isset($site['logo'])?$site['logo']:'',
					      'facebook'=>isset($site['facebook'])?$site['facebook']:'',
					      'twitter'=>isset($site['twitter'])?$site['twitter']:'',
					      'instagram'=>isset($site['instagram'])?$site['instagram']:'',
					      'whatsapp'=>isset($site['whatsapp'])?$site['whatsapp']:'',
					      'youtube'=>isset($site['youtube'])?$site['youtube']:'',
					      'reset_password_link'=>websiteUrl()."/account/rider_reset_password?token=".$model->driver_uuid
					   );									   					   
					   $this->runActions($template_id, $data , array('email') , array(					     
						'email'=>$model->email,
					  ));
				} else $this->msg = "Accout not active $model->status";

			} else $this->msg = HELPER_RECORD_NOT_FOUND;
		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());			    			    			    
		}				
		dump($this->msg);
	}

	public function actionriderearningsrequery()
	{
		// Yii::import('ext.runactions.components.ERunActions');
		// if($this->runactions_enabled){
		// 	if (ERunActions::runBackground()) {
		// 		$this->riderearningsrequery();
		// 	}		
		// } else {
		// 	$this->riderearningsrequery();
		// }		
		$this->riderearningsrequery();
	}

	private function riderearningsrequery()
	{		
		$date_now = date("Y-m-d");
		$cron_key = CommonUtility::getCronKey();		
		$delivery_status = []; $in_delivery_status = '';

		$meta = AR_admin_meta::getMeta(['status_delivery_delivered','status_delivery_failed']);
		$status_delivery_delivered = isset($meta['status_delivery_delivered'])?AttributesTools::cleanString($meta['status_delivery_delivered']['meta_value']):'';			
		$status_delivery_failed = isset($meta['status_delivery_failed'])?AttributesTools::cleanString($meta['status_delivery_failed']['meta_value']):'';			
		if(!empty($status_delivery_delivered)){
			array_push($delivery_status,$status_delivery_delivered);
		}		
		if(!empty($status_delivery_failed)){
			array_push($delivery_status,$status_delivery_failed);
		}				
		$in_delivery_status = CommonUtility::arrayToQueryParameters($delivery_status);
		
		$stmt = "		
		select order_id,order_uuid from {{ordernew}}
		where date(delivery_date)=".q($date_now)."
		and delivery_status IN (".$in_delivery_status.")
		and order_id not in (
		select meta_value from {{wallet_transactions_meta}}
		where meta_name in ('payout_delivery_fee','rider_wallet_process')
		)
		order by order_id desc
		limit 0,1
		";		
		if($model = AR_ordernew::model()->findAllBySql($stmt)){
			foreach ($model as $items) {				
				dump($items->order_id);
				$get_params = [
					'order_uuid'=>$items->order_uuid,
					'key'=>$cron_key,
		            'language'=>Yii::app()->language,
				];
				$cron_url = CommonUtility::getHomebaseUrl()."/task/afterdelivered?".http_build_query($get_params);
				dump($cron_url);
				CommonUtility::runActions($cron_url);
			}
		} else echo HELPER_NO_RESULTS;		
	}
	
	public function actionmerchant_resend_otp()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->merchant_resend_otp();
			}		
		} else {
			$this->merchant_resend_otp();
		}
	}

	public function merchant_resend_otp()
	{
		
		try {
			
			$options = OptionsTools::find(array('signup_verification_tpl'));															
			$template_id = isset($options['signup_verification_tpl'])?$options['signup_verification_tpl']:'';

			$user_uuid = Yii::app()->input->get("user_uuid");		
			$verification_type =  Yii::app()->input->get("verification_type");				
			$model = AR_merchant_user::model()->find("user_uuid=:user_uuid",array(
				':user_uuid'=>$user_uuid
			));			
			if($model){
				$site = CNotifications::getSiteData();						  
				$data = array(		
				'first_name'=>$model->first_name,
				'last_name'=>$model->last_name,
				'email_address'=>$model->contact_email,
				'code'=>$model->verification_code,
				'site'=>$site,
				'logo'=>isset($site['logo'])?$site['logo']:'',
				'facebook'=>isset($site['facebook'])?$site['facebook']:'',
				'twitter'=>isset($site['twitter'])?$site['twitter']:'',
				'instagram'=>isset($site['instagram'])?$site['instagram']:'',
				'whatsapp'=>isset($site['whatsapp'])?$site['whatsapp']:'',
				'youtube'=>isset($site['youtube'])?$site['youtube']:'',
				);					
				$this->runActions($template_id, $data , array($verification_type) , array(
					'phone'=>$model->contact_number,
					'email'=>$model->contact_email
				));
			}

		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());			    			    			    
		}	
		dump($this->msg);
		Yii::log( json_encode($this->msg) , CLogger::LEVEL_ERROR);		
	}

	public function actiontest_runactions()
	{
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->test_runactions();
			}		
		} else {
			$this->test_runactions();
		}				
	}

	public function test_runactions()
	{
		try {			
			$email_address = Yii::app()->input->get('email_address');			
			$options = OptionsTools::find(array('runaction_test_tpl'));
			$template_id = isset($options['runaction_test_tpl'])?$options['runaction_test_tpl']:'';

			$site = CNotifications::getSiteData();
			$data = array(					   
			   'site'=>$site,
			   'logo'=>isset($site['logo'])?$site['logo']:'',
			   'facebook'=>isset($site['facebook'])?$site['facebook']:'',
			   'twitter'=>isset($site['twitter'])?$site['twitter']:'',
			   'instagram'=>isset($site['instagram'])?$site['instagram']:'',
			   'whatsapp'=>isset($site['whatsapp'])?$site['whatsapp']:'',
			   'youtube'=>isset($site['youtube'])?$site['youtube']:'',
			);						
			$this->runActions($template_id, $data , array('email','push') , array(				
				'email'=>$email_address,
			  ),array(
				 'channel'=>Yii::app()->params->realtime['admin_channel'],
				 'type'=>'test_runactions',
				 'event'=>Yii::app()->params->realtime['notification_event'],
			   ),array(
				 'channel'=>Yii::app()->params->realtime['admin_channel'],
				 'type'=>'test_runactions',			      
			));					   
		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());			    			    			    
		}			
		dump($this->msg);
	}

	public function actionaftermerchantregistered()
	{
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->aftermerchantregistered();
			}		
		} else {
			$this->aftermerchantregistered();
		}		
	}

	public function aftermerchantregistered()
	{
		try {

			$merchant_uuid = Yii::app()->input->get("merchant_uuid");
			$merchant = CMerchants::getByUUID($merchant_uuid);
						
			$site = CNotifications::getSiteData();
			$params = array(					      
				'site'=>$site,
				'logo'=>isset($site['logo'])?$site['logo']:'',
				'facebook'=>isset($site['facebook'])?$site['facebook']:'',
				'twitter'=>isset($site['twitter'])?$site['twitter']:'',
				'instagram'=>isset($site['instagram'])?$site['instagram']:'',
				'whatsapp'=>isset($site['whatsapp'])?$site['whatsapp']:'',
				'youtube'=>isset($site['youtube'])?$site['youtube']:'',
				'restaurant_name'=>$merchant->restaurant_name,
				'contact_phone'=>$merchant->contact_phone,
				'contact_email'=>$merchant->contact_email,
				'address'=>$merchant->address,				      			      
			);		

			$options = OptionsTools::find(array('admin_email_alert','admin_mobile_alert','admin_enabled_alert','merchant_registration_new_tpl'));
			$enabled = isset($options['admin_enabled_alert'])?$options['admin_enabled_alert']:0;
			$enabled = $enabled==1?true:false;
			$email = isset($options['admin_email_alert'])?$options['admin_email_alert']:'';
			$phone = isset($options['admin_mobile_alert'])?$options['admin_mobile_alert']:'';

			if($enabled){
				$template_id = isset($options['merchant_registration_new_tpl'])?$options['merchant_registration_new_tpl']:'';								
				$this->runActions($template_id, $params , array('sms','email','push') , array(
				  'phone'=>$phone,
				  'email'=>$email,
				),array(
					'channel'=>Yii::app()->params->realtime['admin_channel'],
					'type'=>'merchant_new_signup',
					'event'=>Yii::app()->params->realtime['notification_event'],
				),array(
					'channel'=>Yii::app()->params->realtime['admin_channel'],
					'type'=>'merchant_new_signup',			      
				));			
			}

		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());			    			    			    
		}	
		dump($this->msg);
	}

	public function actionitemavailability()
	{
		try {
			
			$item_id = Yii::app()->input->get('item_id');
			$item_token = Yii::app()->input->get('item_token');
			$merchant_id = Yii::app()->input->get('merchant_id');
			$available = intval(Yii::app()->input->get('available'));
			$forsale = intval(Yii::app()->input->get('forsale'));
			$available_at_specific = intval(Yii::app()->input->get('available_at_specific'));

			$available = $available==1?true:false;
			$forsale = $forsale==1?true:false;
			$available_at_specific = $available_at_specific==1?true:false;
			$item_available = true;

			if($available_at_specific){
				$items_not_available = CMerchantMenu::getItemAvailability($merchant_id,date("w"),date("H:h:i"));							
				if(in_array($item_id,$items_not_available)){					
					$item_available = false;
				}
			}									
			
			if(!$available || $forsale || !$item_available){	
				$message = [
					'item_id'=>$item_token,
					'merchant_id'=>$merchant_id,
				];
				
				$merchant = CMerchants::get($merchant_id);				

				$noti = new AR_notifications;
				$noti->notication_channel = $merchant->merchant_uuid;
				$noti->notification_event = Yii::app()->params->realtime['event_cart'];
				$noti->notification_type = Yii::app()->params->realtime['car_notification_type'];
				$noti->message = json_encode($message);
				$noti->save();		 
				dump($noti->getErrors());
		    }
			
		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());			    			    			    
		}	
		dump($this->msg);
	}

	public function actionaftermerchantapproved()
	{
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->aftermerchantapproved();
			}		
		} else {
			$this->aftermerchantapproved();
		}		
	}

	public function aftermerchantapproved()
	{
		try {

			$merchant_uuid = Yii::app()->input->get("merchant_uuid");
			$merchant = CMerchants::getByUUID($merchant_uuid);
			
			$options = OptionsTools::find(array('merchant_registration_welcome_tpl'));				
			$template_id = isset($options['merchant_registration_welcome_tpl'])?$options['merchant_registration_welcome_tpl']:'';

			$site = CNotifications::getSiteData();
			$params = array(					      
				'site'=>$site,
				'logo'=>isset($site['logo'])?$site['logo']:'',
				'facebook'=>isset($site['facebook'])?$site['facebook']:'',
				'twitter'=>isset($site['twitter'])?$site['twitter']:'',
				'instagram'=>isset($site['instagram'])?$site['instagram']:'',
				'whatsapp'=>isset($site['whatsapp'])?$site['whatsapp']:'',
				'youtube'=>isset($site['youtube'])?$site['youtube']:'',
				'restaurant_name'=>$merchant->restaurant_name,
				'contact_phone'=>$merchant->contact_phone,
				'contact_email'=>$merchant->contact_email,
				'address'=>$merchant->address,					
			);		
			
			$email = $merchant->contact_email;
			$phone = $merchant->contact_phone;

			$this->runActions($template_id, $params , array('sms','email','push') , array(
				'phone'=>$phone,
				'email'=>$email,
			   ),array(
				 'channel'=>$merchant->merchant_uuid,
				 'type'=>'merchant_confirm_account',
				 'event'=>Yii::app()->params->realtime['notification_event'],
			   ),array(
				 'channel'=>$merchant->merchant_uuid,
				 'type'=>'merchant_confirm_account',			      
			));		

		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());			    			    			    
		}	
		dump($this->msg);
	}

	private function runOrderMerchantActions($order_uuid='' , $template_id='' , $additional_data=array())
	{
		try {

			$templates = CTemplates::get($template_id, array('email','sms','push'), Yii::app()->language );

			$data = CNotifications::getOrder($order_uuid , array(
				'merchant_info','items','summary','order_info','customer','logo','total'
			));			

			$data['additional_data']=$additional_data;

			$path = Yii::getPathOfAlias('backend_webroot')."/twig"; 
			$loader = new \Twig\Loader\FilesystemLoader($path);
			$twig = new \Twig\Environment($loader, [
				'cache' => $path."/compilation_cache",
				'debug'=>true
			]);
			
			$order_info = isset($data['order_info'])?$data['order_info']:'';
			$merchant_id = isset($order_info['merchant_id'])?$order_info['merchant_id']:'';				
			$merchant_uuid = $data['merchant']['merchant_uuid'];
			$customer_name = $order_info['customer_name']?$order_info['customer_name']:'';
			$email_address = $order_info['contact_email']?$order_info['contact_email']:'';
			$contact_phone = $order_info['contact_number']?$order_info['contact_number']:'';
			$client_id = $order_info['client_id']?$order_info['client_id']:'';
			$merchant = isset($data['merchant'])?$data['merchant']:'';					
			$merchant_name = isset($merchant['restaurant_name'])?$merchant['restaurant_name']:'';	

			$message_parameters = array();
			if(is_array($data['order_info']) && count($data['order_info'])>=1){
				foreach ($data['order_info'] as $data_key=>$data_value) {
					if($data_key=="service_code"){
						$data_key='order_type';
					}
					$message_parameters["{{{$data_key}}}"]=$data_value;
				}
			}
			if(is_array($data['merchant']) && count($data['merchant'])>=1){
				foreach ($data['merchant'] as $data_key=>$data_value) {				
					$message_parameters["{{{$data_key}}}"]=$data_value;
				}
			}
			
			if(is_array($additional_data) && count($additional_data)>=1){
				foreach ($additional_data as $data_key=>$data_value) {				
					$message_parameters["{{{$data_key}}}"]=$data_value;
				}
			}
						
			/*SETTINGS FOR PUSH WEB NOTIFICATIONS*/
			$settings_pushweb = AR_admin_meta::getMeta(array('webpush_app_enabled','webpush_provider','pusher_instance_id','onesignal_app_id'
			));							
			$webpush_app_enabled = isset($settings_pushweb['webpush_app_enabled'])?$settings_pushweb['webpush_app_enabled']['meta_value']:'';						
			$webpush_provider = isset($settings_pushweb['webpush_provider'])?$settings_pushweb['webpush_provider']['meta_value']:'';	
			
			$interest = AttributesTools::pushInterest();

			foreach ($templates as $items) {
				$email_subject = ''; $template = ''; $email_subject=''; $sms_template=''; $push_template = ''; $push_title='';
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
				
				if(!empty($push_template)){
					$noti = new AR_notifications;    							
					$noti->notication_channel = $merchant_uuid;
					$noti->notification_event = Yii::app()->params->realtime['notification_event'] ;
					$noti->notification_type = $interest['order_update'];
					$noti->message = $push_template;
					$noti->message_parameters = json_encode($message_parameters);
					if(!empty($data['merchant']['logo'])){
						$noti->image_type = 'image';
						$noti->image = $data['merchant']['logo'];
						$noti->image_path = $data['merchant']['path'];
					} else {
						$noti->image_type = 'icon';
						$noti->image = 'zmdi zmdi-shopping-basket';
					}
					$noti->meta_data = [
						'order_uuid'=>$order_uuid,
						'status'=>isset($order_info['status'])?$order_info['status']:'',
					];							
					$noti->save();						
				}

				// SEND PUSH NOTIFICATIONS TO DEVICE
				if(!empty($push_template)){
					try {
						$push = new AR_push;
						$push->push_type = 'broadcast';
						$push->provider  = 'firebase';																
						$push->channel_device_id = $merchant_uuid;
						$push->platform = 'android';
						$push->title = t($push_title,$message_parameters);
						$push->body = t($push_template,$message_parameters);
						if(isset($data['merchant'])){
							if(!empty($data['merchant']['logo'])){
								$push->image = $data['merchant']['logo'];
								$push->path = $data['merchant']['path'];
							}							
						}											
						$push->save();

						$push = new AR_push;
						$push->push_type = 'broadcast';
						$push->provider  = 'firebase';
						$push->channel_device_id = $merchant_uuid;
						$push->platform = 'ios';
						$push->title = t($push_title,$message_parameters);
						$push->body = t($push_template,$message_parameters);
						if(isset($data['merchant'])){
							if(!empty($data['merchant']['logo'])){
								$push->image = $data['merchant']['logo'];
								$push->path = $data['merchant']['path'];
							}							
						}											
						$push->save();
					} catch (Exception $e) {
						//dump($e->getMessage());
					}							
				}

			}
		} catch (Exception $e) {		    			    
			throw new Exception($e->getMessage());
		}		
	}

	public function areAllOrdersProcessed($last_process_id=0,$table_name='ordernew')
	{
		$next_id = CommonUtility::getNextAutoIncrementID($table_name);		
		return $next_id>$last_process_id?true:false;
	}

	public function resetLastProcessedOrderId($meta_name,$last_id=0)
	{
		$model_admin = AR_admin_meta::model()->find("meta_name=:meta_name",[
			':meta_name'=>$meta_name
		 ]);
		 if(!$model_admin){
			$model_admin = new AR_admin_meta();
		 }			
		 $model_admin->meta_name = $meta_name;
		 $model_admin->meta_value = intval($last_id);
		 $model_admin->save();
	}

	public function actiongetneworder()
	{
		try {

			Yii::log( "START GET NEW ORDER", CLogger::LEVEL_INFO);
		
			CommonUtility::mysqlSetTimezone();			
			$new_status = COrders::newOrderStatus();			

			$criteria=new CDbCriteria();
			$criteria->addCondition("
			status=:status
			AND date_created > now() - interval 30 minute");			
			$criteria->params = [			
				':status'=>$new_status,				
			];						
			$criteria->limit = 30;
			if($model = AR_ordernew::model()->findAll($criteria)){				
				$order_id = ''; $process_id = [];
				$template_id = CNotifications::getStatusActionSingle($new_status,'notification_to_merchant');
				foreach ($model as $items) {							
					$order_id = $items->order_id;
					$process_id[] = $order_id;
					try {																
						$this->runOrderMerchantActions($items->order_uuid,$template_id);
					} catch (Exception $e) {		    	
						$this->msg = t($e->getMessage());
					}	
				}				 				
				$this->msg = "Succesful" ;
			} else $this->msg = HELPER_NO_RESULTS;

		} catch (Exception $e) {
			$this->msg = t($e->getMessage());			    			    			    
		}			
	}

	public function actionorderautoaccept()
	{
		try {
			
			CommonUtility::mysqlSetTimezone();
			$settings = CNotificationData::getRealtimeSettings();
			
			$new_status = COrders::newOrderStatus();		
			$options = OptionsTools::find(['auto_accept_order_status','auto_accept_order_timer','auto_accept_order_enabled']);			
			$status = isset($options['auto_accept_order_status'])?$options['auto_accept_order_status']:'accepted';
			$timer = isset($options['auto_accept_order_timer'])?$options['auto_accept_order_timer']:5;
			$enabled = isset($options['auto_accept_order_enabled'])? ($options['auto_accept_order_enabled']==1?true:false) :false;
			
			if(!$enabled){				
				Yii::app()->end("Disabled");
			}
						
			$criteria=new CDbCriteria();		
			$criteria->alias = "a";	
			$criteria->addCondition("a.status=:status AND TIMESTAMPDIFF(MINUTE, a.date_created, NOW()) > ".$timer."
			 AND a.merchant_id IN (
				select merchant_id from {{option}}
				where merchant_id=a.merchant_id and option_name='merchant_enabled_auto_accept_order'
				and option_value=1
			 )
			");			
			$criteria->params = [				
				':status'=>$new_status,				
			];		
			$criteria->order = "a.order_id ASC";
			$criteria->limit = 2;			
			if($model = AR_ordernew::model()->findAll($criteria)){						
				foreach ($model as $items) {					
					$items->status = $status;
					$items->is_view = 1;	
					$items->scenario = "change_status";			
					$items->save();	
				}
			} else $this->msg[] = HELPER_NO_RESULTS;

		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());			    			    			    
		}			
		//dump($this->msg);
	}

	public function actionassignorder()
	{
		try {

			CommonUtility::mysqlSetTimezone();
			$options = OptionsTools::find(['driver_enabled_auto_assign','driver_assign_when_accepted','driver_allowed_number_task',
			 'driver_on_demand_availability','auto_accept_order_enabled','auto_accept_order_status'
		    ]);					
            $enabled_auto_assign = isset($options['driver_enabled_auto_assign'])?$options['driver_enabled_auto_assign']:'';
			$auto_accept_order_enabled = isset($options['auto_accept_order_enabled'])?$options['auto_accept_order_enabled']:false;
			$auto_accept_order_enabled = $auto_accept_order_enabled==1?true:false;
			$auto_accept_order_status = isset($options['auto_accept_order_status'])?$options['auto_accept_order_status']:'new';
			
			if($enabled_auto_assign!=1){
				dump("auto assign is disabled");
				die();
			}

            $cron_key = CommonUtility::getCronKey();		

			$new_status = COrders::newOrderStatus();			
			$in_status[] = $new_status;
			$meta_name = 'last_assign_order_id';	
			if($auto_accept_order_enabled){
				$in_status[] = $auto_accept_order_status;
			}

			dump($in_status);
			

			$last_process = AR_admin_meta::getMeta([$meta_name]);			
			$last_process_id = isset($last_process[$meta_name])?$last_process[$meta_name]['meta_value']:0;			
			$all_order_process = $this->areAllOrdersProcessed($last_process_id);
			dump("last_process_id=>$last_process_id");
			dump("all_order_process=>$all_order_process");
			if($all_order_process){							
				$this->resetLastProcessedOrderId($meta_name);
			}

			$criteria=new CDbCriteria();
			$criteria->addCondition("
			order_id > ".q($last_process_id)."
			AND driver_id <=0
			AND date_created > now() - interval 30 minute");			
			$criteria->addInCondition('status',$in_status);
			$criteria->limit = 2;	
			dump($criteria);
			if($model = AR_ordernew::model()->findAll($criteria)){
				$order_id = ''; $process_id = [];
				foreach ($model as $items) {
					$order_id = $items->order_id;
					$process_id[] = $order_id;
					$get_params = array( 
						'order_uuid'=> $items->order_uuid,
						'key'=>$cron_key,
						'language'=>Yii::app()->language,
						'time'=>time(),	
						'debug'=>"true"
					);					
					dump(CommonUtility::getHomebaseUrl()."/task/autoassign?".http_build_query($get_params));
					//CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/autoassign?".http_build_query($get_params) );	
					CommonUtility::fastRequest( CommonUtility::getHomebaseUrl()."/task/autoassign?".http_build_query($get_params) );	
				}

				// Update the last processed order ID
				$model_admin = AR_admin_meta::model()->find("meta_name=:meta_name",[
					':meta_name'=>$meta_name
				 ]);
				 if(!$model_admin){
					$model_admin = new AR_admin_meta();
				 }			
				 $model_admin->meta_name = $meta_name;
				 $model_admin->meta_value = $order_id;
				 $model_admin->save();

				$this->msg[]  = "Succesful";
				$this->msg[] = $process_id;

			} else $this->msg[] = HELPER_NO_RESULTS;
		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());			    			    			    
		}	
		dump($this->msg);
	}

	public function actiondebitdiscount()
	{		
		$debug = Yii::app()->input->get("debug");
		if($debug=="true"){
			$this->Debitdiscount();
		} else {
			Yii::import('ext.runactions.components.ERunActions');
			if($this->runactions_enabled){
				if (ERunActions::runBackground()) {
					$this->Debitdiscount();
				}		
			} else {
				$this->Debitdiscount();
			}
		}		
	}

	public function Debitdiscount()
	{		
		$order_uuid = Yii::app()->input->get("order_uuid");			
		$order = []; $card_id = '';

		$status_completed = AOrderSettings::getStatus(array('status_delivered','status_completed'));		
		$all_online = CPayments::getPaymentTypeOnline();	
		
		try {
			$order = COrders::get($order_uuid);	
			$merchant = CMerchants::get($order->merchant_id);			
			$card_id = CWallet::getCardID( Yii::app()->params->account_type['merchant'], $order->merchant_id );

			if(in_array($order->status,(array)$status_completed) && array_key_exists($order->payment_code,$all_online) && $merchant->merchant_type==2 ){
				try {			    	 
					sleep(1);
					CEarnings::debitDiscount($order,$card_id);				
				} catch (Exception $e) {					
					$this->msg[] = t($e->getMessage());			    			    			    			    
				}	
			}
		} catch (Exception $e) {			
			$this->msg[] = t($e->getMessage());			    			    			    			    
		}		
		
		if($order && in_array($order->status,(array)$status_completed) && array_key_exists($order->payment_code,$all_online)){
			try {			    	 		    	 
				$admin_card_id = CWallet::getCardID( Yii::app()->params->account_type['admin'], 0);								
				sleep(1);
				CEarnings::debitDiscount($order,$admin_card_id,'admin');						
			} catch (Exception $e) {					
				$this->msg[] = t($e->getMessage());			    			    			    			    
			}	
		}

		
		if($order && in_array($order->status,(array)$status_completed) && array_key_exists($order->payment_code,$all_online)){			
			if($order->points>0){
				try {						
					$points_amount = $order->points;	 
					$points_cover_cost = isset(Yii::app()->params['settings']['points_cover_cost'])?Yii::app()->params['settings']['points_cover_cost']:'';					
					if($points_cover_cost=="website"){
						if($order->base_currency_code!=$order->admin_base_currency){
							$points_amount = ($points_amount*$order->exchange_rate_use_currency_to_admin);
						}
				    }					
					$card_id = $points_cover_cost=="merchant"?$card_id:$admin_card_id;					
					sleep(1);
					CEarnings::debitPoints($order,$card_id,$points_amount);
				} catch (Exception $e) {					
					$this->msg[] = t($e->getMessage());			    			    			    			    
				}	
			}
		}

		dump($this->msg);
	}

	public function actionsetpointsexpiry()
	{
		try {
						
			Yii::app()->db->createCommand("SET SESSION sql_mode = (SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))")->query();
			
			$year=date('Y-m-d',strtotime("-1 year"));			
			$expired_timedate=date('Y')."12312359";
			$todays=date('YmdHi');		
			$data = [];			
			
			$points_expiry = isset(Yii::app()->params['settings']['points_expiry'])?Yii::app()->params['settings']['points_expiry']:4;
						
			if ( $expired_timedate==$todays){
				if($points_expiry==4){
					$this->msg[] = t("Points does not expired");
				} else if ($points_expiry==1) {
					$stmt = "
					SELECT a.* from 
						{{wallet_transactions}} a
						left join {{wallet_cards}} b
						on 
						a.card_id = b.card_id
						where  b.account_type='customer_points'
						and DATE(a.transaction_date) <=".q($year)."
						and
						a.transaction_id in
						(
							select max(transaction_id)
							FROM {{wallet_transactions}}
							group by card_id
						)
					";				
					$data = Yii::app()->db->createCommand($stmt)->queryAll();
				} else if ($points_expiry==2) {
					$stmt = "
					SELECT a.* from 
						{{wallet_transactions}} a
						left join {{wallet_cards}} b
						on 
						a.card_id = b.card_id
						where  b.account_type='customer_points'
						and DATE(a.transaction_date) <=".q($year)."
						and
						a.transaction_id in
						(
							select max(transaction_id)
							FROM {{wallet_transactions}}
							group by card_id
						)
					";				
					$data = Yii::app()->db->createCommand($stmt)->queryAll();
				}

				$reference_id = 'expired_points_'.date("Ymd");
				$transaction_type = 'points_redeemed';

				if(is_array($data) && count($data)>=1){
					foreach ($data as $items) {				
						$card_id = $items['card_id'];	
						$params = [
							'transaction_description'=>"Expired Rewards",
							'transaction_description_parameters'=>'',
							'transaction_type'=>$transaction_type,
							'transaction_amount'=>$items['running_balance'],
							'status'=>'paid',
							'reference_id'=>$reference_id,						            
						];
						try {											
							CEarnings::findTransaction($card_id,$transaction_type,$reference_id,'');
							CWallet::inserTransactions($card_id,$params);					
						} catch (Exception $e) {
							$this->msg[] = $e->getMessage();
						}
					}
				}			
		    } else $this->msg[] = t("Not end of the year");
									
		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());			    			    			    
		}			
	}

	public function actionsendcompleteregistration()
	{
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->Sendcompleteregistration();
			}		
		} else {
			$this->Sendcompleteregistration();
		}				
	}

	private function Sendcompleteregistration()
	{		
		try {
			$client_uuid = Yii::app()->input->get('client_uuid');
		    $model = ACustomer::getUUID($client_uuid);			
			$email_address = $model->email_address;			
			$contact_phone = $model->contact_phone;
			$site = CNotifications::getSiteData();

			$verification_link = Yii::app()->createAbsoluteUrl("/account/verify",array(
				'uuid'=>$model->client_uuid,				
			));

			$data = array(		
				'first_name'=>$model->first_name,
				'last_name'=>$model->last_name,
				'email_address'=>$model->email_address,
				'code'=>$model->mobile_verification_code,	
				'site'=>$site,			
				'logo'=>isset($site['logo'])?$site['logo']:'',
				'facebook'=>isset($site['facebook'])?$site['facebook']:'',
				'twitter'=>isset($site['twitter'])?$site['twitter']:'',
				'instagram'=>isset($site['instagram'])?$site['instagram']:'',
				'whatsapp'=>isset($site['whatsapp'])?$site['whatsapp']:'',
				'youtube'=>isset($site['youtube'])?$site['youtube']:'',
				'verification_link'=>$verification_link
			);						
			$options = OptionsTools::find(array('signup_complete_registration_tpl'));			
			$template_id = isset($options['signup_complete_registration_tpl'])?$options['signup_complete_registration_tpl']:'';			
			$this->runActions($template_id, $data , array('sms','email') , array(
				'phone'=>$model->contact_phone,
				'email'=>$email_address,
			));			
		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());			    			    			    
		}			
	}

	public function actionrequest_otp_sms()
	{
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->request_otp();
			}		
		} else {
			$this->request_otp();
		}					
	}

	public function actionrequest_otp_email()
	{
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->request_otp(['email']);
			}		
		} else {
			$this->request_otp(['email']);
		}					
	}

	private function request_otp($validation_type=['sms'])
	{
		try {
			
			$client_uuid = Yii::app()->input->get('client_uuid');			

			$model = AR_clientsignup::model()->find('client_uuid=:client_uuid', array(':client_uuid'=>$client_uuid)); 
			if($model){
				
				//if($model->status=="active"){
					$options = OptionsTools::find(array('signup_verification_tpl'));
				    $template_id = isset($options['signup_verification_tpl'])?$options['signup_verification_tpl']:'';						
					$site = CNotifications::getSiteData();
					$data = array(		
						'first_name'=>$model->first_name,
						'last_name'=>$model->last_name,
						'email_address'=>$model->email_address,
						'code'=>$model->mobile_verification_code,
						'site'=>$site,
						'logo'=>isset($site['logo'])?$site['logo']:'',
						'facebook'=>isset($site['facebook'])?$site['facebook']:'',
						'twitter'=>isset($site['twitter'])?$site['twitter']:'',
						'instagram'=>isset($site['instagram'])?$site['instagram']:'',
						'whatsapp'=>isset($site['whatsapp'])?$site['whatsapp']:'',
						'youtube'=>isset($site['youtube'])?$site['youtube']:'',
						'reset_password_link'=>websiteUrl()."/account/reset_password?token=".$model->client_uuid
					);						
					$this->runActions($template_id, $data , $validation_type , array(					     
						'phone'=>$model->contact_phone,
						'email'=>$model->email_address,
					));
				//}
			}
		} catch (Exception $e) {
			$this->msg[] = t($e->getMessage());			    			    			    
		}				
	}

	public function actionSendtest()
	{
		// $noti = new AR_notifications;    							
		// $noti->notication_channel = '7dedd05f-809a-11ec-859e-99479722e411';
		// $noti->notification_event = Yii::app()->params->realtime['notification_event'] ;
		// $noti->notification_type = 'customer_request';
		// $noti->message = "Request - Phone charger";				
		// $noti->message_parameters = '';		
		// $noti->meta_data = [
		// 	'notification_type'=>"call_staff",
		// 	'table_uuid'=>"be6c3449-fca4-11ee-ade3-9c5c8e164c2c",
		// 	'request_id'=>2,
		// 	'title'=>"Table #Main-10"
		// ];
		// $noti->save();

		$noti = new AR_notifications;    							
		//$noti->notication_channel = '7dedd05f-809a-11ec-859e-99479722e411-kitchen';
		$noti->notication_channel = 'be6c3449-fca4-11ee-ade3-9c5c8e164c2c';
		$noti->notification_event = Yii::app()->params->realtime['notification_event'] ;
		$noti->notification_type = 'order_update';
		$noti->message = "Cheeseburger is now in progress";		
		$noti->meta_data = [
			'title'=>t("There have been updates to your order.")
		];
		$noti->save();
		
	}

	public function actiontest_sendsms()
	{
		try {		
			$mobile_number = Yii::app()->input->get('mobile_number');						
			$sms_message = "This is a test SMS";
			$sms_template_id = '';
			$options_data = OptionsTools::find(['runaction_test_tpl']);			
			$template_id = isset($options_data['runaction_test_tpl'])?$options_data['runaction_test_tpl']:'';						
			$templates = CTemplates::get($template_id, array('sms'), Yii::app()->language );
			if(is_array($templates) && count($templates)>=1){				
				$sms_message = isset($templates[0])? ( isset($templates[0]['content'])?$templates[0]['content']:$sms_message ) : $sms_message;
				$sms_template_id = isset($templates[0])? ( isset($templates[0]['sms_template_id'])?$templates[0]['sms_template_id']:$sms_template_id ) : $sms_template_id;
			}			
			$sms_vars = [];
			$resp = CommonUtility::sendSMS($mobile_number,$sms_message,0,0,'Test',$sms_template_id,$sms_vars);			
		} catch (Exception $e) {
			//t($e->getMessage());			    			    			    
		}					
	}

	public function actionkitchenpendingorder()
	{
		try {
								
			$title = "New Order Alert";
			$content = "There is a new order awaiting acknowledgment.";

			$data = Ckitchen::getUnacknowledged();
			foreach ($data as $items) {			
				//dump($items);
				$merchant_uuid = $items['merchant_uuid'];			
				try {
					$push = new AR_push;
					$push->push_type = 'broadcast';
					$push->provider  = 'firebase';							
					$push->channel_device_id = $merchant_uuid;
					$push->platform = 'android';
					$push->title = t($title);
					$push->body = t($content);
					$push->save();
				} catch (Exception $e) {
					//dump($e->getMessage());
				}

				$noti = new AR_notifications;    							
				$noti->notication_channel = $merchant_uuid;
				$noti->notification_event = Yii::app()->params->realtime['notification_event'];
				$noti->notification_type = 'kitchen_pending_order';
				$noti->message = $content;								
				$noti->image_type = 'icon';
				$noti->image = 'zmdi zmdi-shopping-basket';				
				$noti->save();
							
				sleep(1);
			}			
			
		} catch (Exception $e) {
			//echo $e->getMessage();
		}					
	}

	public static function actionMoveOrdersToCurrent()
	{
		try {

			$title = "New Order Move";			
			$content = "There is a new scheduled order move to current with order# {order_reference}.";
			
			$data = Ckitchen::getScheduledOrderTomove();
			foreach ($data as $items) {
				//dump($items);die();
				$merchant_uuid = $items['merchant_uuid'];		
				$message_parameters = [
					'{order_reference}'=>isset($items['order_reference'])?$items['order_reference']:''
				];		
				
				$stmt = "UPDATE {{kitchen_order}}
				SET created_at=".q(CommonUtility::dateNow()).",
				whento_deliver = 'now'
				WHERE order_reference = ".q($items['order_reference'])."
				";
				Yii::app()->db->createCommand($stmt)->query();

				try {
					$push = new AR_push;
					$push->push_type = 'broadcast';
					$push->provider  = 'firebase';							
					$push->channel_device_id = $merchant_uuid."-kitchen";
					$push->platform = 'android';
					$push->title = t($title);
					$push->body = t($content,$message_parameters);
					$push->save();
				} catch (Exception $e) {
					//dump($e->getMessage());
				}

				$noti = new AR_notifications;    							
				$noti->notication_channel = $merchant_uuid."-kitchen";
				$noti->notification_event = Yii::app()->params->realtime['notification_event'];
				$noti->notification_type = 'move_orders_to_current';
				$noti->message = $content;	
				$noti->message_parameters = json_encode($message_parameters);
				$noti->image_type = 'icon';
				$noti->image = 'zmdi zmdi-shopping-basket';				
				$noti->save();

				sleep(1);
			}						

		} catch (Exception $e) {
			//echo $e->getMessage();
		}		
	}

	public function actionsendtokitchen()
	{		
		try {			
			$order_uuid = Yii::app()->input->get('order_uuid');
			$options = OptionsTools::find(['tableside_send_status']);			
			$status = isset($options['tableside_send_status'])? strtolower(trim($options['tableside_send_status'])) :'';			
			$model  = COrders::get($order_uuid);			
			if($status == strtolower(trim($model->status))){				
				Ckitchen::sendtoKitchen($order_uuid);
			}			
		} catch (Exception $e) {
			//echo $e->getMessage();
		}				
	}

	public function actionsendupdatestable()
	{
		try {

			$kitchen_order_id = Yii::app()->input->get('kitchen_order_id');						
			$data = Ckitchen::getKicthenItemsByID($kitchen_order_id,Yii::app()->language);			
			
			$item_name = isset($data['item_name'])?$data['item_name']:'';
			$table_uuid = isset($data['table_uuid'])?$data['table_uuid']:'';
			$item_status = isset($data['item_status'])?$data['item_status']:'';				

			$noti = new AR_notifications;    										
			$noti->notication_channel = $table_uuid;
			$noti->notification_event = Yii::app()->params->realtime['notification_event'] ;
			$noti->notification_type = 'order_update';
			$noti->message = "{item_name} is now {status}";		
			$noti->message_parameters = json_encode([
				'{item_name}'=>$item_name,
				'{status}'=>t($item_status)
			]);
			$noti->meta_data = [
				'title'=>t("There have been updates to your order."),				
			];
			//dump($noti);
			$noti->save();

		} catch (Exception $e) {
			echo $e->getMessage();
		}				
	}

	private function createSlug($slug='')
	{
		$stmt="SELECT count(*) as total FROM {{item}}
		WHERE slug=".q($slug)."
		";					
		if($res = Yii::app()->db->createCommand($stmt)->queryRow()){	
			if($res['total']>0){
				$new_slug = $slug.$res['total'];					
				return self::createSlug($new_slug);
			}
		}
		return $slug;
	}
	
	public function actionfixeditems()
	{
		$stmt = "
		SELECT item_id,item_name ,slug 
		FROM {{item}}
		WHERE slug IN (
			SELECT slug
			FROM {{item}}
			GROUP BY slug
			HAVING COUNT(*) > 1
		)		
		";		
		dump($stmt);
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
			foreach ($res as $items) {
				$item_id = $items['item_id'];
				$item_name = $this->createSlug(CommonUtility::toSeoURL($items['item_name']));												
				$stmt_update = "
				UPDATE {{item}}
				set slug = ".q($item_name)."
				WHERE item_id=".q($item_id)."
				";
				dump($stmt_update);
				dump($items);
				Yii::app()->db->createCommand($stmt_update)->query();
			}
		}
	}

	public function actionsendpwapush()
	{		
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->sendpwapush();
			}		
		} else {
			$this->sendpwapush();
		}
		$this->sendpwapush();
	}

	private function sendpwapush()
	{
		$push_uuid = Yii::app()->input->get("push_uuid");	
		$model = AR_push::model()->find("push_uuid=:push_uuid",array(
			':push_uuid'=>$push_uuid
		));	
		if($model){			
			$message = [
				'message' => [
					'token' => $model->channel_device_id,
					'notification' => [
						'title' => $model->title,
						'body' => $model->body,
					],					
				]
		   ];		   
		   try {
			   $json_path = AttributesTools::getPushJsonFile();
			   $response = CNotifications::SendPwaPush($message,$json_path);			   
			   $model->status='process';
			   $model->response = $response;
		   } catch (Exception $e) {
			   $model->status='process';
			   $model->response = $e->getMessage();			
		   }		   
		   $model->save();		   
		}
	}


}
/*end class*/