<?php
require 'firebase-php/vendor/autoload.php';
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;

class CNotifications
{
	
	public static function getOrder($order_uuid='', $payload = array() )
	{	
			
		$merchant_info = array(); 
		$items = array(); 
		$summary = array(); 
		$total = 0;
		$summary = array(); 
		$order = array(); 
		$meta = array();
		$order_info = array(); 
		$customer = array();
		$site_data = array(); 
		$print_settings = array();		
		$logo = ''; 
		$total = '';
		
		$exchange_rate = 1;
		//$model_order = COrders::get($order_uuid);
		$model_order = COrders::getWithoutCache($order_uuid);
		
		if($model_order->base_currency_code!=$model_order->use_currency_code){
			$exchange_rate = $model_order->exchange_rate>0?$model_order->exchange_rate:1;
			Price_Formatter::init($model_order->use_currency_code);
		}
		Price_Formatter::init($model_order->use_currency_code);			 
		COrders::setExchangeRate($exchange_rate);

		COrders::getContent($order_uuid,Yii::app()->language);
        //$merchant_id = COrders::getMerchantId($order_uuid);
		$merchant_id = $model_order->merchant_id;
        if (in_array('merchant_info',$payload)){
           $merchant_info = COrders::getMerchant($merchant_id,Yii::app()->language);
        }
        
        if (in_array('items',$payload)){
           $items = COrders::getItems();		    
        } 
        
        if (in_array('total',$payload)){
            $total = COrders::getTotal();
        }
        
        if (in_array('summary',$payload)){
            $summary = COrders::getSummary();	
        }
        
        if (in_array('order_info',$payload)){
          $order = COrders::orderInfo(Yii::app()->language, date("Y-m-d"),true );	
          $order_info = isset($order['order_info'])?$order['order_info']:'';
          
          if (in_array('customer',$payload)){
	          $client_id = $order?$order['order_info']['client_id']:0;		    
	          $customer = COrders::getClientInfo($client_id);
          }
        }        
        		                
		if (in_array('logo',$payload)){	     
			$print_settings = AOrderSettings::getPrintSettings();   			
		}
				
		if (in_array('meta',$payload)){	     
			$meta  = COrders::orderMeta();
		}
    
		$site_data = OptionsTools::find(
          array('website_title','website_address','website_contact_phone','website_contact_email')
        );
	    $site = array(
	      'title'=>isset($site_data['website_title'])?$site_data['website_title']:'',
	      'address'=>isset($site_data['website_address'])?$site_data['website_address']:'',
	      'contact'=>isset($site_data['website_contact_phone'])?$site_data['website_contact_phone']:'',
	      'email'=>isset($site_data['website_contact_email'])?$site_data['website_contact_email']:'',		      
	    );
	    
	    $label = array(
	      'date'=>t("Delivery Date/Time"),
	      'items_ordered'=>t("ITEMS ORDERED"),
	      'qty'=>t("QTY"),
	      'price'=>t("PRICE"),
	      'delivery_address'=>t("DELIVERY ADDRESS"),
	      'summary'=>t("SUMMARY")
	    );	    
	    if($order_info['service_code']=="pickup"){
	    	$label['date']=t("Pickup Date/Time");
	    } elseif ( $order_info['service_code']=="dinein" ){
	    	$label['date']=t("Dinein Date/Time");
	    }
	    	    	   
	    $order_type=''; 
	    $services = isset($order['services'])?$order['services']:'';
	    $service_code = $order['order_info']['service_code'];	    
	    if(isset($services[$service_code])){
	    	$order['order_info']['order_type'] = $services[$service_code]['service_name'] ?? '';
	    }
	    
						
		if (php_sapi_name() !== 'cli') {
			$theme_url = websiteDomain()."/".Yii::app()->theme->baseUrl;
		} else {								
			$theme_url = websiteUrl()."/themes/".Yii::app()->params['theme'];
		}		
	    	   	    		                		      
	    $data = array(		       
	       'site'=>$site,
	       'merchant'=>$merchant_info,
	       'order'=>$order,		 
	       'order_info'=>$order_info,
	       'meta'=>$meta,
	       'items'=>$items,
	       'total'=>Price_Formatter::formatNumber($total),
	       'summary'=>$summary,		
	       'label'=>$label,
	       'customer'=>$customer,
	       'logo'=>isset($print_settings['receipt_logo'])?$print_settings['receipt_logo']:'',			       
	       'facebook'=>$theme_url."/assets/images/facebook.png",
	       'twitter'=>$theme_url."/assets/images/twitter.png",
	       'instagram'=>$theme_url."/assets/images/instagram.png",
	       'whatsapp'=>$theme_url."/assets/images/whatsapp.png",
	       'youtube'=>$theme_url."/assets/images/youtube.png",
	    );			
	    return $data;    
	}
	
	public static function getStatusActions($status='')
	{
		$criteria=new CDbCriteria();
		$criteria->alias="a";
		$criteria->select = "a.stats_id, a.action_type , a.action_value , b.description";
		$criteria->join='LEFT JOIN {{order_status}} b on  a.stats_id=b.stats_id ';
		$criteria->condition = "b.description = :description";
		$criteria->params = array(
		  ':description'=>$status,		 
		);
		$model=AR_order_status_actions::model()->findAll($criteria);
		if($model){
			$data = array(); $template_ids = array();
			foreach ($model as $item) {				
				$data[] = array(
				   'action_type'=>$item->action_type,
				   'action_value'=>$item->action_value,
				);
				$template_ids[]=$item->action_value;
			}
			return array(
			  'data'=>$data,
			  'template_ids'=>$template_ids
			);
		}
		throw new Exception( 'no results' );
	}

	public static function getStatusActionSingle($status='',$action_type='')
	{
		$criteria=new CDbCriteria();
		$criteria->alias="a";
		$criteria->select = "a.stats_id, a.action_type , a.action_value , b.description";
		$criteria->join='LEFT JOIN {{order_status}} b on  a.stats_id=b.stats_id ';
		$criteria->condition = "a.action_type=:action_type AND b.description = :description";
		$criteria->params = array(
		  ':action_type'=>$action_type,
		  ':description'=>$status,		 
		);
		$model=AR_order_status_actions::model()->find($criteria);
		if($model){			
			return $model->action_value;
		}
		throw new Exception( 'no results' );
	}
	
	public static function getSiteData()
	{
		$site_data = OptionsTools::find(
	      array('website_title','website_address','website_contact_phone','website_contact_email')
	    );
	    
	    $print_settings = AOrderSettings::getPrintSettings();   

		$url = '';
		if (php_sapi_name() !== 'cli') {
			$url = websiteDomain()."/".Yii::app()->theme->baseUrl;
		} else {								
			$url = websiteUrl()."/themes/".Yii::app()->params['theme'];
		}
	    $site = array(
	      'title'=>isset($site_data['website_title'])?$site_data['website_title']:'',
	      'site_name'=>isset($site_data['website_title'])?$site_data['website_title']:'',
	      'address'=>isset($site_data['website_address'])?$site_data['website_address']:'',
	      'contact'=>isset($site_data['website_contact_phone'])?$site_data['website_contact_phone']:'',
	      'email'=>isset($site_data['website_contact_email'])?$site_data['website_contact_email']:'',		
	      'logo'=>isset($print_settings['receipt_logo'])?$print_settings['receipt_logo']:'',	
		  //'receipt_logo_base64'=> $print_settings['receipt_logo_base64'] ?? null,
		  'receipt_logo_base64'=> null,
	      'facebook'=>$url."/assets/images/facebook.png",
	      'twitter'=>$url."/assets/images/twitter.png",
	      'instagram'=>$url."/assets/images/instagram.png",
	      'whatsapp'=>$url."/assets/images/whatsapp.png",
	      'youtube'=>$url."/assets/images/youtube.png",      
	    );				
	    return $site;	
	}

	public static function deleteNotifications($channel='',$ids='')
    {
        $criteria=new CDbCriteria;
        $criteria->addCondition("notication_channel=:notication_channel");
        $criteria->params = [':notication_channel'=>trim($channel)];
        $criteria->addInCondition("notification_uuid",$ids);
        $model = AR_notifications::model()->deleteAll($criteria);
        if($model){
            return true;
        }
        throw new Exception("Error deleting records."); 
    }

	public static function deleteByChannel($channel='',$ids='')
    {
        $criteria=new CDbCriteria;
        $criteria->addCondition("notication_channel=:notication_channel");
        $criteria->params = [':notication_channel'=>trim($channel)];        
        $model = AR_notifications::model()->deleteAll($criteria);
        if($model){
            return true;
        }
        throw new Exception("Error deleting records."); 
    }

	public static function sendReceiptByEmail($order_uuid='',$to='')
	{
		$template_id = 5;
		$templates = CTemplates::get($template_id, array('email'), Yii::app()->language );		

		$data = CNotifications::getOrder($order_uuid , array(
			'merchant_info','items','summary','order_info','customer','logo','total'
		));		

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
		}
		if(is_array($data['merchant']) && count($data['merchant'])>=1){
			foreach ($data['merchant'] as $data_key=>$data_value) {				
				$message_parameters["{{{$data_key}}}"]=$data_value;
			}			
		}				
		
		$items = isset($templates[0])?$templates[0]:'';
		if($items){
			$email_subject = isset($items['title'])?$items['title']:'';
			$template = isset($items['content'])?$items['content']:'';
			$twig_template = $twig->createTemplate($template);
			$template = $twig_template->render($data);    			    	
			$twig_subject = $twig->createTemplate($email_subject);
            $email_subject = $twig_subject->render($data);                 			
			if(empty($email_subject)){
				throw new Exception("Email subject is empty"); 
			}
			if(empty($template)){
				throw new Exception("Email template is empty"); 
			}
			if(empty($to)){
				throw new Exception("Email address is empty"); 
			}
			if(CommonUtility::sendEmail($to,$customer_name,$email_subject,$template)){
				return true;
			} else throw new Exception("Failed to send email."); 					
		} else {
			throw new Exception("Template not found"); 		
		}		
		throw new Exception("Undefined error"); 
	}

	public static function sendReceiptByWhatsapp($order_uuid='',$mobile_number='',$message_type=1, $send_api=true)
	{
		$data = CNotifications::getOrder($order_uuid , array(
			'merchant_info','items','summary','order_info','customer','logo','total'
		));				
		$merchant = isset($data['merchant'])?$data['merchant']:'';		
		$order_info = isset($data['order'])? ($data['order']?$data['order']['order_info']:'') :'';
		$order_items = isset($data['items'])?$data['items']:'';
		$total = isset($data['total'])?$data['total']:'';		
		$summary =  isset($data['summary'])?$data['summary']:'';		
		$customer_data = isset($data['customer'])?$data['customer']:'';

		$customer_name = isset($order_info['customer_name'])?$order_info['customer_name']:'';
		$order_id = isset($order_info['order_id'])?$order_info['order_id']:'';
		$restaurant_name = isset($merchant['restaurant_name'])?$merchant['restaurant_name']:'';
		$restaurant_name = isset($merchant['restaurant_name'])?$merchant['restaurant_name']:'';
		$merchant_address = isset($merchant['address'])?$merchant['address']:'';
		$merchant_contact = isset($merchant['contact_phone'])?$merchant['contact_phone']:'';

		$service_code = isset($order_info['service_code'])?$order_info['service_code']:'';
		$order_type = isset($order_info['order_type'])?$order_info['order_type']:'';
		
		$line_break = $send_api? "\\n" : "\n";

		if($service_code=="delivery"){
			$delivery_address = t("Delivery Address:");
			$delivery_address.=$line_break;
			$delivery_address.= isset($order_info['complete_delivery_address'])?$order_info['complete_delivery_address']:'';
		} else {
		   $delivery_address = t("{transaction_type} Address:",[
			'{transaction_type}'=>$order_type
		   ]);
		   $delivery_address.=$line_break;
		   $delivery_address.= $merchant_address;
		}		

		$delivery_time = ''; $total_lines = ''; $customer_details='';		
		if($order_info['whento_deliver']=="now"){
			$delivery_time = $order_info['schedule_at'];			
		} else {
			$delivery_time = $order_info['delivery_date']." ".$order_info['delivery_time'];
		}

		if(is_array($summary) && count($summary)>=1){
			foreach ($summary as $summary_items) {
				$total_lines.=$summary_items['name'].": ".$summary_items['value'].$line_break;
			}
		}

		if(is_array($customer_data) && count($customer_data)>=1){
			$customer_details = t("Name: {first_name} {last_name}",[
				'{first_name}'=>isset($customer_data['first_name'])?$customer_data['first_name']:'',
				'{last_name}'=>isset($customer_data['last_name'])?$customer_data['last_name']:'',
			]);
			$customer_details.=$line_break;
			$customer_details.= t("Email: {email_address}",[
				'{email_address}'=>isset($customer_data['email_address'])?$customer_data['email_address']:'',				
			]);
			$customer_details.=$line_break;
			$customer_details.= t("Mobile Number: {contact_phone}",[
				'{contact_phone}'=>isset($customer_data['contact_phone'])?$customer_data['contact_phone']:'',				
			]);
			$customer_details.=$line_break;

			if($service_code=="delivery"){
				$complete_delivery_address = isset($order_info['complete_delivery_address'])?$order_info['complete_delivery_address']:'';
				if($order_info['address_format_use']==1){
					$address_label = isset($order_info['address_label'])?$order_info['address_label']:'';				
					$location_name = isset($order_info['location_name'])?$order_info['location_name']:'';				
					$customer_details.= t("Address: {address_label}: {complete_delivery_address}",[
						'{address_label}'=>$address_label,
						'{complete_delivery_address}'=>$complete_delivery_address
					]);
					$customer_details.=$line_break;
					$customer_details.=$location_name;

				} else {				
					$customer_details.= t("Address: {complete_delivery_address}",[
						'{complete_delivery_address}'=>$complete_delivery_address
					]);
				}			
		    }
		}

		
		$line_items = '';
		//$line_break = '<br/>';		
		if(is_array($order_items) && count($order_items)>=1){
			foreach ($order_items as $items) {
				$price = isset($items['price'])?$items['price']:'';
				$size_name = isset($price['size_name'])?$price['size_name']:'';				
				$line_items.= $items['qty']."x ".$items['item_name'];				
				if(!empty($size_name)){
					$line_items.=" ($size_name)";
				}				
				$line_items.=$line_break;
				$attributes = isset($items['attributes'])?$items['attributes']:'';
				if(is_array($attributes) && count($attributes)>=1){
					foreach ($attributes as $attributes_val) {
						if(is_array($attributes_val) && count($attributes_val)>=1){							
							foreach ($attributes_val as $indexKey=> $attributesVal) {								
								$line_items.=$attributesVal;								
								if($indexKey!==count($attributes_val)-1){
									$line_items.=",";
								}
							}
							$line_items.=$line_break;
					    }
					}
				}

				$addons = isset($items['addons'])?$items['addons']:'';
				if(is_array($addons) && count($addons)>=1){						
					foreach ($addons as $addonsVal) {
						$line_items.= $addonsVal['subcategory_name'];
						$addon_items = isset($addonsVal['addon_items'])?$addonsVal['addon_items']:'';
						$line_items.=$line_break;
						if(is_array($addon_items) && count($addon_items)>=1){	
							foreach ($addon_items as $addon_itemsVal) {
								$line_items.= "- ".$addon_itemsVal['qty']."x ".$addon_itemsVal['sub_item_name'];
								$line_items.=$line_break;
							}
						}
					}
				}
				//$line_items.=$line_break;
			}
			// end each items
		}
		
		$parameters = null;
		if($message_type==1){
			$parameters = [
				'1' => $customer_name, 
				'2' => $restaurant_name,
				'3' => $order_id, 
				'4' => $line_items,
				'5' => $total, 
				'6' => $delivery_address,
				'7' => $merchant_contact
			];
		} elseif ($message_type==2) {
			$parameters = [
				'1' => $restaurant_name, 
				'2' => $order_id,
				'3' => $order_type, 
				'4' => $delivery_time,
				'5' => $line_items, 
				'6' => $total_lines,
				'7' => $customer_details
			];
		}				
		
		if(!$send_api){
			$inline_message = '';
			foreach ($parameters as $item_params) {
				$inline_message.="$item_params\n";
			}
			return $inline_message;
		}

		$options = OptionsTools::find(['whatsapp_phone_number','whatsapp_token',
		'whatsapp_receipt_templatename','whatsapp_receipt_templatename_merchant','whatsapp_language']);

		$whatsapp_phone_number = isset($options['whatsapp_phone_number'])?$options['whatsapp_phone_number']:'';
		$whatsapp_token = isset($options['whatsapp_token'])?$options['whatsapp_token']:'';
		$template_name = isset($options['whatsapp_receipt_templatename'])?$options['whatsapp_receipt_templatename']:'';
		$template_name2 = isset($options['whatsapp_receipt_templatename_merchant'])?$options['whatsapp_receipt_templatename_merchant']:'';
		$whatsapp_language = isset($options['whatsapp_language'])?$options['whatsapp_language']:'';
		$whatsapp_language = !empty($whatsapp_language)?$whatsapp_language:'en_US';

		$template_name = $message_type==1?$template_name:$template_name2;		
				
		$params = [
			'messaging_product'=>'whatsapp',
			'to'=>$mobile_number,
			'type'=>'template',
			'template'=>[
				'name'=>$template_name,
				'language'=>[
					'code'=>$whatsapp_language
				],
				'components'=>[
					[
						'type' => 'body',
						'parameters' => array_map(function($value) {
							return ['type' => 'text', 'text' => $value];
						}, array_values($parameters))
					]
				]
			],
		];
		
		CWhatsApp::setPhone($whatsapp_phone_number);
		CWhatsApp::setToken($whatsapp_token);
		CWhatsApp::sendMessage($params);

	}

	public static function runTemplates($template_id=0, $data=[], $to=[], $language=KMRS_DEFAULT_LANGUAGE)
	{		
		require 'twig/vendor/autoload.php';
		$path = Yii::getPathOfAlias('backend_webroot')."/twig"; 
		$loader = new \Twig\Loader\FilesystemLoader($path);
		$twig = new \Twig\Environment($loader, [
			'cache' => $path."/compilation_cache",
			'debug'=>true
		]);

		$email_subject = ''; $template = ''; $email_subject=''; $sms_template='';
		$push_template = ''; $push_title=''; $sms_template_id = '';   
		$pusher_status = 'pending';
		$webpush_resp = 'pending';
		
		$message_parameters = array(); $sms_vars = [];
		if(is_array($data) && count($data)>=1){
			foreach ($data as $key => $value) {
				$message_parameters["{{{$key}}}"]=$value;			
				$sms_vars[$key]	=$value;
			}
		}
		

		//SETTINGS
		$meta_data = AR_admin_meta::getMeta(array('realtime_provider','realtime_app_enabled',
			'pusher_app_id','pusher_key','pusher_secret','pusher_cluster','ably_apikey',
			'piesocket_clusterid','piesocket_api_key','piesocket_api_secret','webpush_app_enabled','webpush_provider',
			'pusher_instance_id','pusher_primary_key'
		));		
					
		$realtime_app_enabled = isset($meta_data['realtime_app_enabled'])?$meta_data['realtime_app_enabled']['meta_value']:'';
		$realtime_provider = isset($meta_data['realtime_provider'])?$meta_data['realtime_provider']['meta_value']:'';		
		$pusher_app_id = isset($meta_data['pusher_app_id'])?$meta_data['pusher_app_id']['meta_value']:'';
		$pusher_key = isset($meta_data['pusher_key'])?$meta_data['pusher_key']['meta_value']:'';
		$pusher_secret = isset($meta_data['pusher_secret'])?$meta_data['pusher_secret']['meta_value']:'';
		$pusher_cluster = isset($meta_data['pusher_cluster'])?$meta_data['pusher_cluster']['meta_value']:'';		
		$webpush_app_enabled = isset($meta_data['webpush_app_enabled'])?$meta_data['webpush_app_enabled']['meta_value']:'';						
		$webpush_provider = isset($meta_data['webpush_provider'])?$meta_data['webpush_provider']['meta_value']:'';	
		$pusher_instance_id = isset($meta_data['pusher_instance_id'])?$meta_data['pusher_instance_id']['meta_value']:'';	
		$pusher_primary_key = isset($meta_data['pusher_primary_key'])?$meta_data['pusher_primary_key']['meta_value']:'';		

		$push_settings = Yii::app()->params['push'];		
		
		$templates = CTemplates::get($template_id, array('email','sms','push'), $language );   		
		foreach ($templates as $items) {		
			$template_type = $items['template_type'];
			if($items['template_type']=="email" && $items['enabled_email']==1 ){
				$email_subject = isset($items['title'])?$items['title']:'';
				$template = isset($items['content'])?$items['content']:'';
				$twig_template = $twig->createTemplate($template);
				$template = $twig_template->render($data);    			    						
				$twig_subject = $twig->createTemplate($email_subject);
				$email_subject = $twig_subject->render($data);				
				
				// EMAIL
				if(!empty($email_subject) && !empty($template)){
					$to_email = isset($to['email']['email_address'])?$to['email']['email_address']:'';				
					$to_name = isset($to['email']['name'])?$to['email']['name']:'';								
					CommonUtility::sendEmail($to_email,$to_name,$email_subject,$template);				
				}
				
			} else if ($items['template_type']=="sms" && $items['enabled_sms']==1){				
				$sms_template = isset($items['content'])?$items['content']:'';
				$sms_template_id = isset($items['sms_template_id'])?$items['sms_template_id']:'';
			    $twig_sms = $twig->createTemplate($sms_template);			    
                $sms_template = $twig_sms->render($data); 

				// SMS			
				if(!empty($sms_template)){
					$to_name = isset($to['email']['name'])?$to['email']['name']:'';	
					$to_number = isset($to['sms']['mobile_number'])?$to['sms']['mobile_number']:'';								
					CommonUtility::sendSMS($to_number,$sms_template,0,0,$to_name,$sms_template_id,$sms_vars);
				}

			} else if ($items['template_type']=="push" && $items['enabled_push']==1){
				$push_template = isset($items['content'])?$items['content']:'';			    			    
				$push_title = isset($items['title'])?$items['title']:'';		
				
				// PUSHER
				if($template_type=="push" && !empty($push_template) && $realtime_app_enabled){
					$settings = array(
						'notication_channel'=>isset($to['pusher']['notication_channel'])?$to['pusher']['notication_channel']:'',
						'notification_event'=>isset($to['pusher']['notification_event'])?$to['pusher']['notification_event']:'',
						'app_id'=>$pusher_app_id,
						'key'=>$pusher_key,
						'secret'=>$pusher_secret,
						'cluster'=>$pusher_cluster,					
					);
					$noti = new AR_notifications;  
					$noti->scenario = 'send';
					$noti->notication_channel = isset($to['pusher']['notication_channel'])?$to['pusher']['notication_channel']:'';
					$noti->notification_event = isset($to['pusher']['notification_event'])?$to['pusher']['notification_event']:'';
					$noti->notification_type = isset($to['pusher']['notification_type'])?$to['pusher']['notification_type']:'';
					$noti->message = $push_template;				
					$noti->message_parameters = json_encode($message_parameters);	
					$noti->meta_data = isset($data['meta_data'])?$data['meta_data']:'';
					$noti->image_type = isset($data['image_type'])?$data['image_type']:'';
					$noti->image = isset($data['image'])?$data['image']:'';
					$noti->image_path = isset($data['image_path'])?$data['image_path']:'';		
					$noti->settings = $settings;	
					$noti->realtime_provider = $realtime_provider;	
					$noti->save();				
				}
				// END PUSHER

				// WEB PUSH
				if($template_type=="push" && !empty($push_template) && $webpush_app_enabled){	
					$pushweb_config = array(
						'provider'=>$webpush_provider,
						'channel'=>isset($to['webpush']['channel_device_id'])?$to['webpush']['channel_device_id']:'',
						'pusher_instance_id'=>$pusher_instance_id,
						'pusher_instance_id'=>$pusher_instance_id,
						'pusher_primary_key'=>$pusher_primary_key,					
					);		
					$push = new AR_push;    	
					$push->scenario = 'send';	
					$push->merchant_id = isset($to['webpush']['merchant_id'])?$to['webpush']['merchant_id']:'';					    
					$push->push_type = isset($to['webpush']['push_type'])?$to['webpush']['push_type']:'';
					$push->provider  = $webpush_provider;
					$push->channel_device_id = isset($to['webpush']['channel_device_id'])?$to['webpush']['channel_device_id']:'';
					$push->platform = "web";
					$push->title = t($push_title,$message_parameters);
					$push->body = t($push_template,$message_parameters);	
					$push->settings = $pushweb_config;			
					$push->save();
				}
				// END WEB PUSH

				// SEND PUSH NOTIFICATIONS TO DEVICE
				if($template_type=="push" && !empty($push_template)){
					$push_settings = Yii::app()->params['push'];
					$dialog_title =  isset($to['firebase']['dialog_title'])?$to['firebase']['dialog_title']:'';
					$push_settings['dialog_title'] = $dialog_title;

					$push = new AR_push;
					$push->scenario = 'send';
					$push->merchant_id = isset($to['firebase']['merchant_id'])?$to['firebase']['merchant_id']:'';
					$push->push_type = isset($to['firebase']['push_type'])?$to['firebase']['push_type']:'';
					$push->provider  = 'firebase';
					$push->channel_device_id = isset($to['firebase']['channel_device_id'])?$to['firebase']['channel_device_id']:'';
					$push->platform = 'android';
					$push->title = t($push_title,$message_parameters);
					$push->body = t($push_template,$message_parameters);
					$push->image = isset($to['firebase']['image'])?$to['firebase']['image']:'';																				
					$push->path = isset($to['firebase']['image_path'])?$to['firebase']['image_path']:'';
					$push->settings = $push_settings;
					$push->save();

					$push = new AR_push;
					$push->scenario = 'send';
					$push->merchant_id = isset($to['firebase']['merchant_id'])?$to['firebase']['merchant_id']:'';
					$push->push_type = isset($to['firebase']['push_type'])?$to['firebase']['push_type']:'';
					$push->provider  = 'firebase';
					$push->channel_device_id = isset($to['firebase']['channel_device_id'])?$to['firebase']['channel_device_id']:'';
					$push->platform = 'ios';
					$push->title = t($push_title,$message_parameters);
					$push->body = t($push_template,$message_parameters);
					$push->image = isset($to['firebase']['image'])?$to['firebase']['image']:'';																				
					$push->path = isset($to['firebase']['image_path'])?$to['firebase']['image_path']:'';
					$push->settings = $push_settings;
					$push->save();
				}
				// END SEND PUSH NOTIFICATIONS TO DEVICE				
			}													
		}
		// end foreach
	}

	public static function sendFirebasePush($platform='',$params_message=[], $json_path='')
	{
		$message  = null;
		$factory = (new Factory)
		->withServiceAccount($json_path);  
		$auth = $factory->createAuth();
		$cloudMessaging = $factory->createMessaging();
		$data = [
			'dialog_title'=>$params_message['dialog_title']
		];
		if($platform=="android"){
			$params = [
				'ttl'=>'3600s',
				'priority'=>"high",
				'notification'=>[
					'title'=>$params_message['title'],
					'body'=>$params_message['body'],
					'icon' => 'stock_ticker_update',
					'color'=>$params_message['color'],
					'sound'=>$params_message['sound'],
					'channelId'=>$params_message['channelId'],
					'image'=>$params_message['image'],
				]
			];	
			$config = AndroidConfig::fromArray($params);						
			$message = CloudMessage::withTarget($params_message['target'], $params_message['device_id'])			
			->withAndroidConfig($config)
			->withData($data);							
		} else {
			$params = [
				'headers'=>[
					'apns-priority'=>'10',
				],
				'payload'=>[
					'aps'=>[
						'alert'=>[
							'title'=>$params_message['title'],
							'body'=>$params_message['body'],
						],
						'badge'=>1,
						'sound'=>$params_message['sound'],
					]
				]
			];
			$config = ApnsConfig::fromArray($params);
			$message = CloudMessage::withTarget($params_message['target'], $params_message['device_id'])			
			->withApnsConfig($config)
			->withData($data);
		}		

		try {
			$result = $cloudMessaging->send($message);		                						
			$response = json_encode($result);			
		} catch (Exception $e) {																
			$response = $e->getMessage();			
		}		
		return $response;
	}	

	public static function getTotalNotifications($channel='',$notification_type=[])
	{
		  $criteria=new CDbCriteria();
		  $criteria->condition = "notication_channel=:notication_channel AND viewed=:viewed";
		  $criteria->params = [
			':notication_channel'=>$channel,
			':viewed'=>0
		  ];
		  $criteria->addInCondition("notification_type",$notification_type);		  
		  $count = AR_notifications::model()->count($criteria);
		  return intval($count);
	}
	
	public static function getAccessToken($serviceAccount) {

		function base64url_encode($data) {
			return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
		}
	
		$now = time();
		$jwtHeader = ['alg' => 'RS256', 'typ' => 'JWT'];
		$jwtClaim = [
			'iss' => $serviceAccount['client_email'],
			'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
			'aud' => 'https://oauth2.googleapis.com/token',
			'iat' => $now,
			'exp' => $now + 3600,
		];
	
		$jwtHeaderEncoded = base64url_encode(json_encode($jwtHeader));
		$jwtClaimEncoded = base64url_encode(json_encode($jwtClaim));
		$signatureInput = $jwtHeaderEncoded . '.' . $jwtClaimEncoded;
	
		// Sign the JWT
		openssl_sign($signatureInput, $signature, $serviceAccount['private_key'], 'sha256WithRSAEncryption');
		$jwt = $signatureInput . '.' . base64url_encode($signature);
	
		// Request access token
		$postFields = http_build_query([
			'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
			'assertion' => $jwt,
		]);
	
		$ch = curl_init('https://oauth2.googleapis.com/token');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
	
		$response = json_decode(curl_exec($ch), true);
		curl_close($ch);
	
		return $response['access_token'] ?? null;
	}

	public static function SendPwaPush($params_message=[], $json_path=null)
	{
								
		$serviceAccount = json_decode(file_get_contents($json_path), true);		
		$projectId = $serviceAccount['project_id'] ?? null;
		if(!$projectId){
			throw new Exception( "Invalid Project id" );
			return ;
		}

		$accessToken = self::getAccessToken($serviceAccount);		
		if (!$accessToken) {
			throw new Exception( "Failed to get access token." );
			return ;
		}
				
		$ch = curl_init("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send");
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Authorization: Bearer ' . $accessToken,
			'Content-Type: application/json'
		]);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params_message));

		$response = curl_exec($ch);
		if (curl_errno($ch)) {
			throw new Exception( curl_error($ch) );
			return ;
		}
		curl_close($ch);

		$json_response = json_decode($response,true);
		
		$name = $json_response['name'] ?? null;
		$error = $json_response['error'] ?? null;
		$error = $error['message'] ?? null;

		if($error){
			throw new Exception($error);
		}		
		return $name;
	}

	public static function toWhatsappFormat($data=[])
	{
		
		if(!is_array($data) && count($data)<=1){
			return '';
		}
		

		$order_items = ""; $attributes_items = ''; $addons_items = '';
		foreach ($data['items'] as $item) {
			$item_name = trim($item['item_name']);
			$attributes = $item['attributes'] ?? null;
			$addons = $item['addons'] ?? null;
			$order_items .= "{$item['qty']} x {$item_name}\n";
			if(!empty($item['special_instructions'])){
				$order_items .= t("Special Instructions: {special_instructions}",['{special_instructions}'=>$item['special_instructions']]) . "\n";
			}			
			if(is_array($attributes) && count($attributes)>=1){
				foreach ($attributes as $attributes_item) {
					if(is_array($attributes_item) && count($attributes_item)>=1){
						$check_item = $attributes_item[0] ?? null;
						if(!empty($check_item)){
							$attributes_items .="  • ";
							foreach ($attributes_item as $attributes_value) {
								$attributes_items .="{$attributes_value},";
							}						
							$attributes_items .="\n";
					    }
					}					
				}
				$order_items .= "$attributes_items";
			}

			if(is_array($addons) && count($addons)>=1){
				foreach ($addons as $addons_cat) {
					$order_items .=" ➤ {$addons_cat['subcategory_name']}\n";
					$addon_items = $addons_cat['addon_items'] ?? null;
					$addons_items='';
					if(is_array($addon_items) && count($addon_items)>=1){
						foreach ($addon_items as $addon_val) {
							$addons_items .="  • {$addon_val['qty']}x {$addon_val['sub_item_name']}\n";
						}
						$order_items .= "$addons_items";
					}
				}
			}
		}

		$order_items = rtrim($order_items, ', ');		

		$phone_number = $data['phone_number'] ?? '';
		$customer_name = $data['customer_name'] ?? '';
		$order_id = $data['order_id'] ?? '';
		$total = $data['total'] ?? '';
		$transaction_type = $data['transaction_type'] ?? '';
		$service_code = $data['service_code'] ?? '';	
		$delivery_address = $data['delivery_address'] ?? '';	
		$delivery_fee = $data['delivery_fee'] ?? 0;
		$whento_deliver = $data['whento_deliver'] ?? 'now';
		$schedule_at = $data['schedule_at'] ?? '';

		$delivery_address_line = '';	
		if($service_code=='delivery'){
			$delivery_address_line .= t("Delivery Address: {delivery_address}",['{delivery_address}'=>$delivery_address])  . "\n";
		}
		
		$message = t("Hello, I just placed an order.") . "\n"
         . t("Name: {name}", ['{name}' => $customer_name]) . "\n"
         . t("Order ID: {id}", ['{id}' => $order_id]) . "\n"
		 . t("Transaction Type: {transaction_type}", ['{transaction_type}' => $transaction_type]) . "\n";

		 if($service_code=='delivery'){
			 $message .= t("Delivery Address: {address}", ['{address}' => $delivery_address]) . "\n";
             $message .= t("Delivery Fee: {delivery_fee}", ['{delivery_fee}' => $delivery_fee]) . "\n";
		 }

		 $message .= t("{transaction_type} Date/Time: {schedule_at}", [ '{transaction_type}'=>$transaction_type,'{schedule_at}' => $schedule_at]) . "\n";
		 
		 $message .= "\n"
         . t("Items:") . "\n" . $order_items . "\n"
         . t("Total: {total}", ['{total}' => $total]) . "\n"
         . t("Please confirm my order.");
				 		 
		$encoded_message = urlencode($message);
		return "https://api.whatsapp.com/send?phone=$phone_number&text=$encoded_message";
	}

}
/*end class*/