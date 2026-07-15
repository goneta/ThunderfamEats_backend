<?php
Class CPlan
{

	public static function listing($lang = KMRS_DEFAULT_LANGUAGE , $exclude_free_trial=false , $customer_filter='', $exchange_rate=1)
	{
		$and = '';
		if($exclude_free_trial){
			$and = " AND trial_period <=0";
		}
	    $stmt="
		SELECT 
		a.package_id, 
		a.package_uuid,
		IF(COALESCE(NULLIF(b.title, ''), '') = '', a.title, b.title) as title,		
		IF(COALESCE(NULLIF(b.description, ''), '') = '', a.description, b.description) as description,
		a.price, 
		a.price as price_raw, 
		a.promo_price, 
		a.promo_price as promo_price_raw,
		a.package_period,
		a.item_limit,
		a.order_limit,
		a.trial_period,
		a.pos,
		a.self_delivery,
		a.chat,
		a.loyalty_program,
		a.table_reservation,
		a.inventory_management,
		a.marketing_tools,
		a.mobile_app,
		a.payment_processing,
		a.customer_feedback,
		a.coupon_creation
		
		FROM {{plans}} a		
		left JOIN (
		    SELECT package_id, title, description FROM {{plans_translation}} where language=".q($lang)."
		) b 
		on a.package_id = b.package_id
		WHERE a.status = 'publish'
		$and
		$customer_filter
		ORDER BY a.sequence ASC
		";					
	    $dependency = CCacheData::dependency();
	    if($res = Yii::app()->db->cache(Yii::app()->params->cache, $dependency)->createCommand($stmt)->queryAll() ){
	    	$data = array();
	    	foreach ($res as $val) {	    		
	    		$val['title'] = Yii::app()->input->xssClean($val['title']);	    		
	    		$val['price'] = Price_Formatter::formatNumber(($val['price']*$exchange_rate));
	    		$val['promo_price'] = Price_Formatter::formatNumber(($val['promo_price']*$exchange_rate));	    	
	    		$val['package_period'] = t($val['package_period']);
				$val['plan_url']=Yii::app()->createAbsoluteUrl("/merchant/payment_plan",[
					'id'=>$val['package_uuid']
				]);
				$val['plan_url2']=Yii::app()->createAbsoluteUrl("/plan/payment",[
					'id'=>$val['package_uuid']
				]);
				$val['billed'] = t("Billed {period}",['{period}'=> ucwords($val['package_period']) ]);
				$val['label'] = $val['trial_period']>0 ? t("{day}-Day Free Trial",['{day}'=>$val['trial_period']])  : t("Get Started");
	    		$data[]=$val;
	    	}	    	
	    	return $data;
	    }
	    throw new Exception( 'no results' );
	}
	
	public static function Details()
	{
		$model = AR_admin_meta::model()->findAll("meta_name=:meta_name",array(
		  ':meta_name'=>'plan_features'
		));
		if($model){
			$data = array();
			foreach ($model as $item) {				
				$data[$item->meta_value1][] =  Yii::app()->input->xssClean($item->meta_value);
			}
			return $data;
		}
		throw new Exception( 'no results' );
	}
	
	public static function get($package_id=0)
	{
		$model = AR_plans::model()->find('package_id=:package_id', 
		array(':package_id'=> intval($package_id) )); 	
		if($model){
			return $model;
		}
		return false;
	}

	public static function getPlanByID($package_id='',$lang=KMRS_DEFAULT_LANGUAGE, $exchange_rate=1)
	{
		$stmt = "
		SELECT a.package_id,
		a.package_uuid,
		IF(COALESCE(NULLIF(b.title, ''), '') = '', a.title, b.title) as title,
		IF(COALESCE(NULLIF(b.description, ''), '') = '', a.description, b.description) as description,
		a.price,
		a.price as price_raw, 
		a.promo_price,
		a.promo_price as promo_price_raw,
		a.package_period,
		a.ordering_enabled,
		a.item_limit,
		a.order_limit
		FROM {{plans}} a
		left JOIN (
		   SELECT package_id, title, description FROM {{plans_translation}} where language=".q(KMRS_DEFAULT_LANGUAGE)."
		) b 
		on a.package_id = b.package_id
		WHERE a.package_id = ".q($package_id)."
		";
		if($res=CCacheData::queryRow($stmt)){
			$res['price'] = Price_Formatter::formatNumber(($res['price']*$exchange_rate));
			$res['promo_price'] = Price_Formatter::formatNumber(($res['promo_price']*$exchange_rate));
			return $res;
		}
		throw new Exception(  t(HELPER_NO_RESULTS) );
	}

	public static function getPlanByUUID($plan_uuid='',$lang=KMRS_DEFAULT_LANGUAGE)
	{
		$stmt = "
		SELECT a.package_id,
		a.package_uuid,
		IF(COALESCE(NULLIF(b.title, ''), '') = '', a.title, b.title) as title,
		IF(COALESCE(NULLIF(b.description, ''), '') = '', a.description, b.description) as description,
		a.price,
		a.price as price_raw, 
		a.promo_price,
		a.promo_price as promo_price_raw,
		a.package_period,
		a.ordering_enabled,
		a.item_limit,
		a.order_limit
		FROM {{plans}} a
		left JOIN (
		   SELECT package_id, title, description FROM {{plans_translation}} where language=".q(KMRS_DEFAULT_LANGUAGE)."
		) b 
		on a.package_id = b.package_id
		WHERE package_uuid = ".q($plan_uuid)."
		";
		if($res=CCacheData::queryRow($stmt)){
			$res['price'] = Price_Formatter::formatNumber($res['price']);
			$res['promo_price'] = Price_Formatter::formatNumber($res['promo_price']);
			return $res;
		}
		throw new Exception(  t(HELPER_NO_RESULTS) );
	}

	public static function SubscriptionsSummary($start_date='',$end_date='')
	{
		$date_filter = ''; $interval_days = 30;
		$new_start_date = ''; $new_end_date = '';
		if(!empty($start_date) && !empty($end_date)){
			$date_filter = " 
			AND created_at BETWEEN ".q($start_date)." AND ".q($end_date)."
			";			
			$new_start_date = $start_date;
			$new_end_date = $end_date;
		} else {
			$currentDate = new DateTime();
			$new_start_date = $currentDate->modify('first day of this month')->format('Y-m-d');
			$new_end_date = $currentDate->modify('last day of this month')->format('Y-m-d');			
		}

		$stmt = "
		SELECT 
			(SELECT COUNT(*) FROM {{plan_subscriptions}} WHERE status NOT IN ('created') $date_filter ) AS total_subscriptions,
			(SELECT COUNT(*) FROM {{plan_subscriptions}} WHERE status = 'active' $date_filter ) AS active_subscriptions,
			(SELECT COUNT(*) FROM {{plan_subscriptions}} WHERE status = 'expired' $date_filter) AS expired_subscriptions,
			(SELECT COUNT(*) FROM {{plan_subscriptions}} WHERE status = 'cancelled' $date_filter ) AS cancelled_subscriptions,
			(SELECT COUNT(*) FROM {{plan_subscriptions}} 
			   WHERE status = 'active'
			   AND created_at BETWEEN ".q($new_start_date)." AND ".q($new_end_date)."
			)
			AS new_subscriptions
		";						
		if($res = Yii::app()->db->createCommand($stmt)->queryRow()){
			return $res;
		}
		throw new Exception(  t(HELPER_NO_RESULTS) );
	}

	public static function TotalSubscriptions()
	{

	}

	public static function ActiveSubscriptions()
	{

	}

	public static function ExpiredSubscriptions()
	{

	}

	public static function NewSubscriptions()
	{

	}
	
}
/*end class*/