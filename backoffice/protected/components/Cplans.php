<?php
// require_once 'razorpay/vendor/autoload.php';
// use Razorpay\Api\Api;

class Cplans
{
	
	public static function get($package_id='')
	{		
		$dependency = CCacheData::dependency();
		$model = AR_plans::model()->cache(Yii::app()->params->cache, $dependency)->find('package_id=:package_id', 
		array(':package_id'=>intval($package_id))); 
		if($model){
			return $model;
		}
		throw new Exception( 'Plans not found' );
	}
	
	public static function getByUUID($package_uuid='')
	{		
		$dependency = new CDbCacheDependency('SELECT MAX(date_modified) FROM {{cache}}');
		$model = AR_plans::model()->cache(Yii::app()->params->cache, $dependency)->find('package_uuid=:package_uuid', 
		array(':package_uuid'=>trim($package_uuid))); 
		if($model){
			return $model;
		}
		throw new Exception( 'Plans not found' );
	}
	
	public static function planDetails($package_id='', $lang='')
	{		
		$criteria=new CDbCriteria();
		$criteria->alias="a";
		$criteria->select="a.package_id,a.package_uuid,a.plan_type,b.title,b.description,a.price,a.promo_price,
		a.package_period,a.ordering_enabled,a.item_limit,a.order_limit,a.trial_period,a.status
		";
		$criteria->join='LEFT JOIN {{plans_translation}} b on  a.package_id=b.package_id ';
		$criteria->condition = "a.package_id=:package_id AND b.language=:language";
		$criteria->params = array(
		  ':package_id'=>intval($package_id),
		  ':language'=>$lang,		  
		);
		
		$dependency = CCacheData::dependency();
		$model=AR_plans::model()->cache(Yii::app()->params->cache, $dependency)->find($criteria);
		if($model){
			return $model;
		}
		throw new Exception( 'Plans not found' );
	}
	
	public static function planPriceID($meta_name='', $package_id=0)
	{
		$dependency = CCacheData::dependency();
		$model = AR_admin_meta::model()->cache(Yii::app()->params->cache, $dependency)->find('meta_name=:meta_name AND meta_value1=:meta_value1 ', 
		  array(
		    ':meta_name'=>trim($meta_name),
		    ':meta_value1'=>intval($package_id)
		)); 
		if($model){
			return $model;
		}
		throw new Exception( 'Plans price not found' );
	}
	
	public static function getMechantSubcriptions($merchant_id=0,$is_live=0,$meta_name='subscription_stripe')
	{
		$meta = AR_merchant_meta::model()->find("merchant_id=:merchant_id AND meta_name=:meta_name AND meta_value1=:meta_value1 ",array(
		     ':merchant_id'=>intval($merchant_id),
		     ':meta_name'=>$meta_name,	
		     ':meta_value1'=>$is_live
		   ));
	    if($meta){
	    	return $meta;
	    }
	    throw new Exception( 'Subscriber account not found' );
	}
	
	public static function getMerchantCustomerID($merchant_id=0,$is_live=0,$meta_name='stripe')
	{
		$meta = AR_merchant_meta::model()->find("merchant_id=:merchant_id AND meta_name=:meta_name AND meta_value=:meta_value ",array(
		     ':merchant_id'=>intval($merchant_id),
		     ':meta_name'=>$meta_name,	
		     ':meta_value'=>$is_live
		   ));
	    if($meta){
	    	return $meta;
	    }
	    throw new Exception( 'Subscriber account not found' );
	}

	public static function getSubscriptionPlans($subscriber_id=0,$package_id=0,$status='created',$subscriber_type='merchant')
	{		
		$model = AR_plan_subscriptions::model()->find("subscriber_id=:subscriber_id AND package_id=:package_id AND status=:status AND subscriber_type=:subscriber_type",[
			':subscriber_id'=>$subscriber_id,
			':package_id'=>$package_id,
			':status'=>$status,
			':subscriber_type'=>$subscriber_type,
		]);		
		if($model){
			return $model;
		}
		throw new Exception( 'Subscription plans not found' );
	}

	public static function getSubscriptionPlans2($subscriber_id=0,$package_id=0,$subscriber_type='merchant')
	{		
		$model = AR_plan_subscriptions::model()->find("subscriber_id=:subscriber_id AND package_id=:package_id AND subscriber_type=:subscriber_type",[
			':subscriber_id'=>$subscriber_id,
			':package_id'=>$package_id,			
			':subscriber_type'=>$subscriber_type,
		]);		
		if($model){
			return $model;
		}
		throw new Exception( 'Subscription plans not found' );
	}

	public static function getSubscriptionID($id=0)
	{
		$model = AR_plan_subscriptions::model()->find("id=:id",[
			':id'=>$id,			
		]);
		if($model){
			return $model;
		}
		throw new Exception( 'Subscription plans not found' );
	}

	public static function getSubscriptionPaymentId($payment_id=0)
	{
		$model = AR_plan_subscriptions::model()->find("payment_id=:payment_id",[
			':payment_id'=>$payment_id,			
		]);
		if($model){
			return $model;
		}
		throw new Exception( 'Subscription plans not found' );
	}

	public static function getSubscriberSubscriptions($subscriber_id=0,$subscriber_type='')
	{
		$model = AR_plan_subscriptions::model()->find("subscriber_id=:subscriber_id AND subscriber_type=:subscriber_type",[
			':subscriber_id'=>$subscriber_id,			
			':subscriber_type'=>$subscriber_type
		]);
		if($model){
			return $model;
		}
		throw new Exception( 'Subscription plans not found' );
	}

	public static function getSubscriberLatestSubscriptions($subscriber_id=0,$subscriber_type='')
	{		
		$criteria=new CDbCriteria();
		$criteria->condition = "subscriber_id=:subscriber_id AND subscriber_type=:subscriber_type";
		$criteria->params = [
			':subscriber_id'=>$subscriber_id,
			':subscriber_type'=>$subscriber_type,
		];
		$criteria->order = "id DESC";	
		$model=AR_plan_subscriptions::model()->find($criteria);		
		if($model){
			return $model;
		}
		throw new Exception( 'Subscription plans not found' );
	}

	public static function getActiveSubscriptions($subscriber_id=0,$subscriber_type='',$status='active')
	{
		$model = AR_plan_subscriptions::model()->find("subscriber_id=:subscriber_id AND subscriber_type=:subscriber_type AND status=:status",[
			':subscriber_id'=>$subscriber_id,			
			':subscriber_type'=>$subscriber_type,
			':status'=>$status
		]);
		if($model){
			return $model;
		}
		throw new Exception( 'Subscription plans not found' );
	}

	public static function getActiveSubscriptions2($subscriber_id=0,$subscriber_type='',$payment_code='', $status='active')
	{
		$model = AR_plan_subscriptions::model()->find("payment_code=:payment_code AND subscriber_id=:subscriber_id AND subscriber_type=:subscriber_type AND status=:status",[
			':payment_code'=>$payment_code,
			':subscriber_id'=>$subscriber_id,			
			':subscriber_type'=>$subscriber_type,
			':status'=>$status
		]);
		if($model){
			return $model;
		}
		throw new Exception( 'Subscription plans not found' );
	}

	public static function getSubscriberPlan($subscriber_id=0,$subscriber_type='merchant',$language='',$status='active')
	{
		$criteria=new CDbCriteria();
		$Unlimited = t("Unlimited");		

		$criteria->alias="a";
		$criteria->select="
		    a.id,
			a.payment_code,
		    a.subscription_id, 
			a.status, 						
			a.package_id,
			a.created_at,
			a.next_due,
			a.expiration,
			IF(COALESCE(NULLIF(c.title, ''), '') = '', b.title, c.title) as plan_title,		
			IF(b.promo_price>0,b.promo_price,b.price) as price,	
			b.package_period,					
			CONCAT(
				 d.orders_added, '/', 
                 IF(d.order_limit = 0,".q($Unlimited).", d.order_limit)
			) AS remaining_orders_display,

			 CONCAT(
				d.items_added, '/', 
				IF(d.item_limit = 0, ".q($Unlimited).", d.item_limit)
			) AS remaining_items_display,

			 IF(d.order_limit = 0, 'Unlimited', d.order_limit) AS order_limit,
			CONCAT(
				ROUND((d.orders_added / NULLIF(d.order_limit, 0)) * 100),
				'%'
			) AS remaining_orders_percentage,

			IF(d.item_limit = 0, 'Unlimited', d.item_limit) AS order_limit,
			CONCAT(
				ROUND((d.items_added / NULLIF(d.item_limit, 0)) * 100),
				'%'
			) AS remaining_items_percentage

		";
		$criteria->join="
		LEFT JOIN {{plans}} b on a.package_id=b.package_id 

		left JOIN (
		   SELECT package_id, title FROM {{plans_translation}} where language=".q($language)."
		) c 
		on a.package_id = c.package_id

		left JOIN (
		   SELECT merchant_id,orders_added,order_limit,items_added,item_limit
		   FROM {{merchant}}
		) d 
		on a.subscriber_id = d.merchant_id
		";		

		$criteria->condition = "a.subscriber_id=:subscriber_id AND a.subscriber_type=:subscriber_type ";
		//$criteria->condition = "a.subscriber_id=:subscriber_id AND a.subscriber_type=:subscriber_type";
		$criteria->params = array(
		  ':subscriber_id'=>$subscriber_id,
		  ':subscriber_type'=>$subscriber_type,
		  //':status'=>$status
		);					
		$criteria->order = "id DESC";	
		$model=AR_plan_subscriptions::model()->find($criteria);		
		if($model){			
			return [
				'id'=>$model->id,
				'payment_code'=>$model->payment_code,
				'plan_title'=>$model->plan_title,				
				'subscription_id'=>$model->subscription_id,
				'status'=>$model->status,
				'status_pretty'=>ucwords(t($model->status)),
				'package_id'=>$model->package_id,
				'remaining_orders_display'=>$model->remaining_orders_display,
				'remaining_items_display'=>$model->remaining_items_display,
				'remaining_orders_percentage'=>intval($model->remaining_orders_percentage),
				'remaining_items_percentage'=>intval($model->remaining_items_percentage),								
				'created_at'=>Date_Formatter::date($model->created_at),
				'next_due'=>Date_Formatter::date($model->next_due),
				'expiration'=>Date_Formatter::date($model->expiration),
				'price'=>Price_Formatter::formatNumber($model->price),
				'package_period'=>$model->package_period,
				'package_period_pretty'=>t($model->package_period),
			];
		}
		throw new Exception( 'Plans not found' );
	}

	public static function getSubscriptionHistory($subscriber_id='',$subscriber_type='merchant',$language=KMRS_DEFAULT_LANGUAGE)
	{
		$criteria=new CDbCriteria();
		$criteria->alias="a";
		$criteria->select="
		    a.id,
			a.payment_id,
			a.payment_code,
			a.subscription_id, 
			a.status, 			
			a.current_start,
			a.current_end,			
			a.amount,			
			a.plan_name,
			IF(COALESCE(NULLIF(c.title, ''), '') = '', b.title, c.title) as plan_title	
	    ";
		$criteria->join="
			LEFT JOIN {{plans}} b on a.package_id=b.package_id 

			left JOIN (
			SELECT package_id, title FROM {{plans_translation}} where language=".q($language)."
			) c 
			on a.package_id = c.package_id		
		";		

		$criteria->condition = "a.subscriber_id=:subscriber_id AND a.subscriber_type=:subscriber_type";
		$criteria->params = array(
		':subscriber_id'=>$subscriber_id,
		':subscriber_type'=>$subscriber_type,	  
		);			
		// $criteria->addInCondition("a.status",[
		// 	'active','paused','cancelled','expired','pending','payment failed'
		// ]);
		$criteria->order = "a.id DESC";
	    $model=AR_plan_subscriptions::model()->findAll($criteria);		
		if($model){			
			$data = [];
			foreach ($model as $items) {
				$payment_link = '';
				if($items->payment_code=="bank"){
					$payment_link = Yii::app()->createAbsoluteUrl("merchant/upload_subscription_deposit",[
						'id'=>$items->payment_id
					]);
				}
				$data[] = [
					'id'=>$items->id,
					'payment_code'=>$items->payment_code,
					'payment_code_pretty'=>t($items->payment_code),
					'plan_title'=> !empty($items->plan_title) ? $items->plan_title : $items->plan_name ,
					'amount'=>Price_Formatter::formatNumber($items->amount),
					'current_start'=>Date_Formatter::date($items->current_start),
					'current_end'=>Date_Formatter::date($items->current_end),
					'status'=>t($items->status),
					'status_raw'=>$items->status,
					'payment_link'=>$payment_link
				];
			}
			return $data;
		}
		throw new Exception( t(HELPER_NO_RESULTS) );
	}

	public static function getPaymentCreated($payment_id='')
	{
		$model = AR_plans_create_payment::model()->find("payment_id=:payment_id",[
			':payment_id'=>$payment_id
		]);
		if($model){
			return $model;
		}
		throw new Exception( t("Payment ID not found") );
	}

	public static function getSubscriberInformation($subscriber_id=0, $subscriber_type='',$return = 'information')
	{
		if($subscriber_type=="merchant"){
			$model = AR_merchant_user::model()->find("merchant_id=:merchant_id AND role=:role AND status=:status",[
				':merchant_id'=>$subscriber_id,
				':role'=>0,
				':status'=>'active'
			]);
			if($model){
				if($return=="model"){
					return $model;
				} else {
					return [
						//'uuid'=>$model->merchant_uuid,
						'uuid'=>$model->user_uuid,
						'first_name'=>$model->first_name,
						'last_name'=>$model->last_name,
						'full_name'=>"$model->first_name $model->last_name",
						'contact_email'=>$model->contact_email,
						'contact_number'=>$model->contact_number
					];
				}				
			}			
		} elseif ($subscriber_type=="customer") {
			
		}
		throw new Exception( t("Subscriber records not found") );
	}

	public static function getSubscriberRecords($subscriber_id=0, $subscriber_type='')
	{
		if($subscriber_type=="merchant"){
			$model = AR_merchant::model()->find("merchant_id=:merchant_id",[
				':merchant_id'=>$subscriber_id,				
			]);
			if($model){
				return $model;
			}			
		} elseif ($subscriber_type=="customer") {
			
		}
		throw new Exception( t("Subscriber records not found") );
	}

	public static function getSubscriptionByID($subscription_id='')
	{
		$model = AR_plan_subscriptions::model()->find("subscription_id=:subscription_id",[
			':subscription_id'=>$subscription_id
		]);
		if($model){
			return $model;
		}
		throw new Exception( t("Subscription Id not found") );
	}
	
	public static function isWebhookFound($id='')
	{
		$model = AR_plans_webhooks::model()->find("id=:id",[
			':id'=>trim($id)
		]);
		if($model){
			return $model;
		}
		return false;
	}

	public static function getCustomerID($payment_code='',$subscriber_id='',$subscriber_type='',$livemode=0)
	{
		$model = AR_plans_customer::model()->find("payment_code=:payment_code AND subscriber_id=:subscriber_id 
		AND subscriber_type=:subscriber_type AND livemode=:livemode",[
			':payment_code'=>$payment_code,
			':subscriber_id'=>$subscriber_id,
			':subscriber_type'=>$subscriber_type,
			':livemode'=>intval($livemode)
		]);
		if($model){
			return $model;
		}
		return false;
	}

	public static function cancelPaymentSubscriptions($subscriber_type='',$subscriber_id='',$payment_code='',$status='active')
	{
		try {
			$stmt = "
			SELECT * FROM {{plan_subscriptions}}
			WHERE subscriber_type=".q($subscriber_type)."
			AND subscriber_id=".q($subscriber_id)."
			AND status=".q($status)."
			AND payment_code <> ".q($payment_code)."
			ORDER BY id DESC
			LIMIT 0,1
			";					
			if($res = Yii::app()->db->createCommand($stmt)->queryRow()){
				$id = $res['id'];
				$payment_code = $res['payment_code'];
				$subscription_id = $res['subscription_id'];
				
				// dump("id=>$id");
				// dump("payment_code=>$payment_code");
				// dump("subscription_id=>$subscription_id");
				switch ($payment_code) {

					case "paypal":
						    Yii::import("modules_dir.paypal.*");
							Yii::import("modules_dir.paypal.components.*");
							$payment_code = PaypalModule::paymentCode();
							$credentials = CPayments::getPaymentCredentials(0,$payment_code);
							$credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';			
							$is_live = isset($credentials['is_live'])?intval($credentials['is_live']):0;
							
							CPaypalTokens::setProduction($is_live);
							CPaypalTokens::setCredentials($credentials,$payment_code);
							$token = CPaypalTokens::getToken(date("c"));			
							CPaypal::setProduction($is_live);
			                CPaypal::setToken($token);	
							$resp = CPaypal::CancelSubscriptions($subscription_id);							
							$model = Cplans::getSubscriptionID($id);
							$model->status = 'cancelled';
							$model->save();
						break;

					case 'stripe':
							require 'stripe2/vendor/autoload.php';
							$credentials = CPayments::getPaymentCredentials(0,$payment_code);				
							$credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';									
							$secret = isset($credentials['attr1'])?$credentials['attr1']:'';					
							$stripe = new \Stripe\StripeClient($secret);
							$stripe->subscriptions->cancel($subscription_id, []);

							$model = Cplans::getSubscriptionID($id);
							$model->status = 'cancelled';
							$model->save();
						break;

					case "razorpay":						
							$credentials = CPayments::getPaymentCredentials(0,$payment_code);			
							$credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:'';							
							$api = new Api($credentials['attr1'], $credentials['attr2']);
							$resp = $api->subscription->fetch($subscription_id)->cancel(array('cancel_at_cycle_end' => 0));						
							$model = Cplans::getSubscriptionID($id);
							$model->status = 'cancelled';
							$model->save();
						break;
									
				}
			}
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}
	
	public static function clearCreatepaymentplans()
	{
		CommonUtility::mysqlSetTimezone();
		$stmt="DELETE FROM {{plans_create_payment}}
		WHERE created_at < now() - interval 1 DAY
		";
		Yii::app()->db->createCommand($stmt)->query();
	}

	public static function getDatebyperiod($period='')
	{		
		$next_due = ''; $current_start = date("Y-m-d");
		$currentDate = new DateTime();
		switch ($period) {
			case 'daily':				
				$currentDate->modify("+1 days");				
				break;
			case 'weekly':			
				$currentDate->modify('+1 week');								
				break;					
			case 'monthly':				
				$currentDate->modify('+1 month');
				break;	
			case 'anually':				
				$currentDate->modify("+1 year");
				break;			
			default:		
			    $currentDate->modify('+1 month');
			   break;			
		}

		$next_due = $currentDate->format('Y-m-d');				

		return [
			'next_due'=>$next_due,
			'expiration'=>$next_due,
			'current_start'=>$current_start,
			'current_end'=>$next_due,
		];
	}

	public static function UpdateItemsAdded($merchant_id=0,$count=1)
	{
		try {
			$model = CMerchants::get($merchant_id);
			if($model->merchant_type!=1){
				return false;
			}
			$items_added = $model->items_added+$count;			
			$model->items_added = intval($items_added);
			if(!$model->save()){
				//dump($model->getErrors());
			}
		} catch (Exception $e) {
			//echo $e->getMessage();
		}
	}

	public static function canAddItems($merchant_id=0)
	{
		try {			
			$model = CMerchants::get($merchant_id);		

			if($model->item_limit==0){
				return true;
			}

			if($model->items_added >= $model->item_limit){
				throw new Exception( t("You have reached your maximum limit of {items_added} food items. Please upgrade your subscription to add more items.",[
					'{items_added}'=>$model->items_added
				]));
			}
			return true;
		} catch (Exception $e) {
			throw new Exception( $e->getMessage() );
		}
	}

	public static function UpdateAddedItems($merchant_id=0)
	{
		$stmt = "
		UPDATE {{merchant}} m
		JOIN (
			SELECT merchant_id, COUNT(*) AS item_count
			FROM {{item}}
			WHERE merchant_id = ".q($merchant_id)."
			GROUP BY merchant_id
		) fi ON m.merchant_id = fi.merchant_id
		SET m.items_added = fi.item_count
		WHERE m.merchant_id = ".q($merchant_id)."
		";
		Yii::app()->db->createCommand($stmt)->query();
		CCacheData::add();
	}

	public static function canAddOrder($merchant_id=0,$merchant=null)
	{
		try {			
			if(!$merchant){				
				$model = CMerchants::get($merchant_id);		
			} else $model = $merchant;
				
			if($model->order_limit==0){
				return true;
			}
						
			if($model->orders_added >= $model->order_limit){
				throw new Exception( t("You have reached your maximum order limit of {orders_added} orders. Please upgrade your subscription to continue receiving orders.",[
					'{orders_added}'=>$model->orders_added
				]));
			}
			return true;
		} catch (Exception $e) {
			throw new Exception( $e->getMessage() );
		}
	}

	public static function UpdateOrdersAdded($merchant_id=0,$count=1)
	{
		try {
			$model = CMerchants::get($merchant_id);
			if($model->merchant_type!=1){
				return false;
			}
			$items_added = $model->orders_added+$count;	
			//dump($items_added);die();
			$model->orders_added = intval($items_added);
			if(!$model->save()){
				//dump($model->getErrors());
			}
		} catch (Exception $e) {
			//echo $e->getMessage();
		}
	}

	public static function getStatusAccepted()
	{
		$status = AR_admin_meta::getValue('tracking_status_process');
		$status_accepted = isset($status['meta_value'])?$status['meta_value']:null;
		$status_accepted = !empty($status_accepted)?$status_accepted:null;
		return $status_accepted;
	}

}
/*end class*/