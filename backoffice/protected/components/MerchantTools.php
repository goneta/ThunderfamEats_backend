<?php
class MerchantTools
{
	
	public static function displayAdminName()
	{		
		$name = Yii::app()->merchant->first_name." ".Yii::app()->merchant->last_name;
		return $name;
	}
	
	public static function getProfilePhoto()
	{								
		$upload_path = CMedia::merchantFolder();		
		if(isset(Yii::app()->merchant->profile_photo)){
			$avatar = CMedia::getImage(Yii::app()->merchant->profile_photo,$upload_path,'@thumbnail',
		CommonUtility::getPlaceholderPhoto('customer'));
		} else $avatar = Yii::app()->merchant->avatar;
		return $avatar;
	}
	
	public static function getLogo($filename='')
	{						
		return websiteDomain()."/".Yii::app()->theme->baseUrl."/assets/images/sample-merchant-logo@2x.png";		
	}
	
	
	/*
	$services = integer example = 1
	*/
	public static function legacyServices($services=array())
	{
		$service_id = 1; $delivery=false;$pickup=false;$dinein=false;
		if(is_array($services) && count($services)>=1){
			foreach ($services as $id) {
				switch ($id) {
					case 1:
						$delivery=true;
						break;
				
					case 2:
						$pickup=true;
						break;
						
					case 3:
						$dinein=true;
						break;
								
					default:
						break;
				}
			}
			if($delivery && $pickup && $dinein){
				$service_id=4;
			} elseif ( $delivery && $pickup){
				$service_id=1;
			} elseif ( $delivery && $dinein){
				$service_id=5;
			} elseif ( $pickup && $dinein){
				$service_id=6;
			} elseif ( $delivery){
				$service_id=2;
			} elseif ( $pickup){
				$service_id=3;
			} elseif ( $dinein){
				$service_id=7;
			}
		}
		return $service_id;
	}
	
	/*
	@parametes 
	merchant_id = merchant id
	params = array() possible values are
	Array
	(
	    [0] => 1
	    [1] => 3
	)	
	$meta_name = different meta name
	*/
	public static function saveMerchantMeta($merchant_id=0,$params=array(),$meta_name='')
	{			
		Yii::app()->db->createCommand("DELETE FROM {{merchant_meta}}
		WHERE merchant_id=".q($merchant_id)."
		AND meta_name=".q($meta_name)."
		")->query();
		if($merchant_id>0 && is_array($params) && count($params)>=1){
			foreach ($params as $id) {
				$params = array(
				  'merchant_id'=>(integer)$merchant_id,
				  'meta_name'=>trim($meta_name),
				  'meta_value'=>trim($id)
				);
				Yii::app()->db->createCommand()->insert("{{merchant_meta}}",$params);
			}
		}
	}
	
	public static function getMerchantMeta($merchant_id=0,$meta_name='')
	{
		$stmt="
		SELECT meta_value
		FROM {{merchant_meta}}
		WHERE merchant_id = ".q($merchant_id)."
		AND 
		meta_name = ".q($meta_name)."
		ORDER BY meta_id ASC
		";
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
			$data = array();
			foreach ($res as $val) {
				$data[]=$val['meta_value'];
			}
			return $data;
		}
		return false;
	}
	
	
	/*
	@parametes 
	$cuisine = array()
	Array
	(
	    [0] => 1
	    [1] => 11
	    [2] => 16	 
	)
	*/
	public static function insertCuisine($merchant_id='', $cuisine=array())
	{		
		$merchant_id = (integer)$merchant_id;
		
		Yii::app()->db->createCommand("DELETE FROM 
		{{cuisine_merchant}} WHERE merchant_id=".q($merchant_id)." ")->query();
		
		if(is_array($cuisine) && count($cuisine)>=1){
			foreach ($cuisine as $cuisine_id) {
				Yii::app()->db->createCommand()->insert("{{cuisine_merchant}}",array(
				  'merchant_id'=>(integer)$merchant_id,
				  'cuisine_id'=>(integer)$cuisine_id
				));
			}
		}
	}
	
	public static function getCuisine($merchant_id='')
	{		
		$data = CommonUtility::getDataToDropDown("{{cuisine_merchant}}",'cuisine_id','cuisine_id',"
		WHERE merchant_id=".q(intval($merchant_id))."
		");	
		return $data;
	}
	
	/*
	@parametes 
	$tag_id = array()
	
	*/
	public static function insertTag($merchant_id=0, $tag_id = array())
	{
		$merchant_id = (integer)$merchant_id;		
		Yii::app()->db->createCommand("DELETE FROM 
		{{option}} WHERE merchant_id=".q($merchant_id)."  AND  option_name='tags' ")->query();
		
		if(is_array($tag_id) && count($tag_id)>=1 && $merchant_id>0){
		   foreach ($tag_id as $tagid) {
		      Yii::app()->db->createCommand()->insert("{{option}}",array(
				  'merchant_id'=>(integer)$merchant_id,
				  'option_name'=>'tags',
				  'option_value'=>(integer)$tagid
				));
		   }
		}
	}
	
	public static function saveMerchantUser($merchant_id=0, $params=array())
	{
		$merchant_id = (integer)$merchant_id;		
		Yii::app()->db->createCommand("DELETE FROM 
		{{merchant_user}} WHERE merchant_id=".q($merchant_id)."  AND  main_account='1' ")->query();
		
		Yii::app()->db->createCommand()->insert("{{merchant_user}}",$params);
	}
		
	public static function getMerchantOptions($merchant_id=0,$option_name='')
	{
		$stmt="
		SELECT option_value
		FROM {{option}}
		WHERE merchant_id = ".q($merchant_id)."
		AND 
		option_name = ".q($option_name)."
		ORDER BY id ASC
		";
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
			$data = array();
			foreach ($res as $val) {
				$data[]=$val['option_value'];
			}
			return $data;
		}
		return false;
	}
	
	/*
	@params = 
	Array
	(
	    [merchant_master_table_boooking] => 1
	    [merchant_master_disabled_ordering] => 1
	    [disabled_single_app_modules] => 1
	)
	*/
	public static function savedOptions($merchant_id=0,$params=array())
	{
		if($merchant_id>0 && is_array($params) && count($params)>=1){
			foreach ($params as $key=>$val) {				
				$option=AR_option::model()->find('merchant_id=:merchant_id and option_name=:option_name', 
				array(
				  ':merchant_id'=>$merchant_id,
				  ':option_name'=>$key
				));						
				if(!$option){
					$option=new AR_option;
				}
				$option->merchant_id = $merchant_id;
				$option->option_name=$key;
				$option->option_value=$val;				
				$option->save();
			}			
		}
	}
	
	/*
	@params = 
	array(
	 'merchant_master_table_boooking','merchant_master_disabled_ordering','disabled_single_app_modules'
	)
	*/
	public static function getOptions($merchant_id=0, $params=array())
	{
		$data = array();
		$criteria = new CDbCriteria();
		$criteria->condition='merchant_id=:merchant_id';
        $criteria->params=array(':merchant_id'=>$merchant_id);
		$criteria->addInCondition('option_name', (array)$params);
		if($option = AR_option::model()->findAll($criteria)){
			foreach ($option as $val) {		
				$data[$val->option_name]  = $val->option_value;
			}		
			return $data;		
		}
		return false;
	}
	
	public static function MerchantDeleteALl($merchant_id=0)
	{
		$merchant_id = (integer)$merchant_id;
		if($merchant_id>0){
			
			$sql_delete = "
			SET @merchant_id = ".q($merchant_id).";

			delete from {{merchant where}}
			merchant_id=@merchant_id;

			delete from {{item}}
			where merchant_id = @merchant_id;

			delete from {{item_meta}}
			where merchant_id = @merchant_id;

			delete from {{category}}
			where merchant_id = @merchant_id;

			delete from {{item_relationship_category}}
			where merchant_id = @merchant_id;

			delete from {{item_relationship_size}}
			where merchant_id = @merchant_id;

			delete from {{size}}
			where merchant_id = @merchant_id;

			delete from {{item_relationship_subcategory}}
			where merchant_id = @merchant_id;

			delete from {{subcategory}}
			where merchant_id = @merchant_id;
			
			delete from {{cuisine_merchant}}
			where merchant_id = @merchant_id;

			delete from {{subcategory_item_relationships}}
            where merchant_id = @merchant_id; 

			delete from {{subcategory_item}}
            where merchant_id = @merchant_id;        

			delete from {{item_relationship_subcategory_item}}
            where merchant_id = @merchant_id;        

			delete from {{cooking_ref}}
            where merchant_id = @merchant_id;        

			delete from {{availability}}
			where merchant_id = @merchant_id;        
			
			delete from {{item_promo}}
			where merchant_id = @merchant_id;        

			delete from {{banner}}
			where meta_value1 = @merchant_id;        

			delete from {{pages}}
			where merchant_id = @merchant_id;        

			delete from {{menu}}
			where meta_value1 = @merchant_id and menu_type='website_merchant'; 

			delete from {{ingredients}}
            where merchant_id = @merchant_id;        

			delete from {{option}}
			where merchant_id = @merchant_id;

			delete from {{merchant_user}}
			where merchant_id = @merchant_id;

			delete from {{merchant_meta}}
			where merchant_id = @merchant_id;

			delete from {{opening_hours}}
			where merchant_id = @merchant_id;

			delete from {{item_translation}}
			where merchant_id = @merchant_id;        
	
			delete from {{category_translation}}
			where merchant_id = @merchant_id;        
	
			delete from {{subcategory_translation}}
			where merchant_id = @merchant_id;        
	
			delete from {{subcategory_item_translation}}
			where merchant_id = @merchant_id;        
	
			delete from {{category_relationship_dish}}
			where merchant_id = @merchant_id;        

			delete from {{merchant_user}}
            where merchant_id = @merchant_id;        

			delete from {{size_translation}}
            where merchant_id = @merchant_id;        

			delete from {{opening_hours}}
            where merchant_id = @merchant_id;        

			delete from {{tax}}
            where merchant_id = @merchant_id;        
			";
			Yii::app()->db->createCommand($sql_delete)->query();			
		}
	}

	public static function hasMerchantSetMenu($merchant_id=0)
	{
		$model = AR_merchant_meta::model()->find("merchant_id=:merchant_id AND meta_name=:meta_name",[
			':merchant_id'=>intval($merchant_id),
			':meta_name'=>'menu_access'
		]);
		if($model){
			return true;
		}
		return false;
	}

	public static function getMerchantMenuRolesAccess($user_id=0,$merchant_id=0)
	{
		$criteria=new CDbCriteria();
		$criteria->alias="a";              
		$criteria->condition = "b.merchant_user_id=:merchant_user_id";
		$criteria->join='LEFT JOIN {{merchant_user}} b on a.role_id = b.role';
		$criteria->params = array(			  
			':merchant_user_id'=>intval($user_id)
		);            		
		$dependency = CCacheData::dependency();
		if($model = AR_role_access::model()->cache(Yii::app()->params->cache, $dependency)->findAll($criteria)){
			$data  = []; $dashboard_access = []; $has_set = false;
			if(MerchantTools::hasMerchantSetMenu($merchant_id)){
				$has_set = true;
				$dashboard_access = MerchantTools::getMerchantMeta($merchant_id,'menu_access');				
			} 			
			foreach ($model as $items) {
				if( $has_set){	
					if(in_array($items->action_name,$dashboard_access)){
						$data[] = $items->action_name;
					}					
				} else $data[] = $items->action_name;				
			}			
			return $data;
		}
		throw new Exception( t(HELPER_NO_RESULTS) );
	}

	public static function deleteMerchantMeta($merchant_id=0,$meta_name='')
	{
		Yii::app()->db->createCommand("DELETE FROM {{merchant_meta}}
		WHERE merchant_id=".q($merchant_id)."
		AND meta_name=".q($meta_name)."
		")->query();
	}

	public static function UpdateMerchantMeta($merchant_id=0, $meta_name='',$meta_value='')
	{
		$model = AR_merchant_meta::model()->find("merchant_id=:merchant_id AND meta_name=:meta_name",[
			':merchant_id'=>intval($merchant_id),
			':meta_name'=>$meta_name,
		]);
		if(!$model){
			$model = new AR_merchant_meta();
		}
		$model->merchant_id = intval($merchant_id);
		$model->meta_name = $meta_name;
		$model->meta_value = $meta_value;
		$model->save();
	}

	public static function getAllAccess($menu_type='')
	{
		$data = [];
		$model = AR_menu::model()->findAll("menu_type=:menu_type",[
			':menu_type'=>$menu_type
		]);
		if($model){
			foreach ($model as $items) {
				$data[] = $items->action_name;
				if(!empty($items->role_create)){
					$data[] = $items->role_create;
				}
				if(!empty($items->role_update)){
					$data[] = $items->role_update;
				}
				if(!empty($items->role_delete)){
					$data[] = $items->role_delete;
				}
				if(!empty($items->role_view)){
					$data[] = $items->role_view;
				}
			}
		}
		return $data;
	}

	public static function setSubscriptionAccess($merchant_id=0,$subscription_features='')
	{
		
		$access_features['pos'] = [
			'pos','pos.createorder','pos.orderhistory','pos.create_order','pos.order_history'
		];
		$access_features['self_delivery'] = [
			'merchant.driver','merchantdriver.cashout_list','merchantdriver.collect_cash','merchantdriver.collect_cash_add',
			'merchantdriver.list','merchantdriver.add','merchantdriver.carlist','merchantdriver.addcar','merchantdriver.group_list',
			'merchantdriver.addgroup','merchantdriver.zone_list','merchantdriver.zone_create','merchantdriver.schedule_list',
			'merchantdriver.shift_list','merchantdriver.shift_add','merchantdriver.review_list','merchantdriver.collect_cash_delete',
			'merchantdriver.collect_transactions','merchantdriver.update','merchantdriver.delete','merchantdriver.overview',
			'merchantdriver.license','merchantdriver.vehicle','merchantdriver.bank_info','merchantdriver.wallet','merchantdriver.cashout_transactions',
			'merchantdriver.order_tips','merchantdriver.time_logs','merchantdriver.review_ratings','merchantdriver.update_car','merchantdriver.delete_car',
			'merchantdriver.group_update','merchantdriver.group_delete','merchantdriver.zone_update','merchantdriver.zone_delete','merchantdriver.schedule_add',
			'merchantdriver.schedule_update','merchantdriver.schedule_delete','merchantdriver.schedule_bulk','merchantdriver.shift_update','merchantdriver.shift_delete',
			'merchantdriver.shift_bulkupload','merchantdriver.review_create','merchantdriver.review_update','merchantdriver.review_delete','merchantdriver.delivery_transactions'
		];
		$access_features['chat'] = [
			'merchant.communication','communications.chats','communications.framechat'
		];
		$access_features['loyalty_program'] = [
			'merchant.campaigns','merchant.loyalty_points'
		];
		$access_features['table_reservation'] = [
			'table.booking','booking.table_view','booking.tableside_config','booking.generate_table','booking.tables',
			'booking.room','booking.shifts','booking.settings','booking.update_status','booking.list',
			'booking.create_reservation','booking.update_reservation','booking.reservation_delete','booking.reservation_overview',
			'booking.create_shift','booking.update_shift','booking.shift_delete','booking.create_room','booking.update_room',
			'booking.room_delete','booking.create_table','booking.update_tables','booking.tables_delete'
		];
		$access_features['marketing_tools'] = [
			'sms','smsmerchant.broadcast','smsmerchant.sms_settings','customer.email_subscriber','smsmerchant.smsbroadcast_create','smsmerchant.broadcast_details',
			'smsmerchant.smsbroadcast_delete'
		];
		$access_features['mobile_app'] = [
			'merchant.banner','merchant.banner_create','merchant.pages_list','merchant.pages_create','merchant.pages_menu',
			'merchant.banner_update','merchant.banner_delete','merchant.page_update','merchant.pages_delete'
		];
		$access_features['customer_feedback'] = [
			'customer.reviews','customer.review_reply','customer.review_reply_update','customer.customerreview_delete','customer.esubscriber_delete'
		];
		$access_features['coupon_creation'] = [
			'promo','merchant.coupon','merchant.coupon_create','merchant.offers','merchant.offer_create','merchant.coupon_update',
			'merchant.coupon_delete','merchant.offer_update','merchant.offer_delete'
		];
		
		$access_to_remove = [];
		$subscription_features = !empty($subscription_features)?json_decode($subscription_features,true):null;		
		if(is_array($subscription_features) && count($subscription_features)>=1){
			foreach ($subscription_features as $features_key=>$feature_value) {				
				if(!$feature_value){					
					if(isset($access_features[$features_key])){
						foreach ($access_features[$features_key] as $key => $value) {
							$access_to_remove[]=$value;
						}
					}
				}
			}
		}

		$allow_payment = [];
		$payment_processing = isset($subscription_features['payment_processing'])?$subscription_features['payment_processing']:false;
		if($payment_processing){
			$allow_payment = CPayments::getPaymentAllActive();
		} else {			
			$allow_payment = CPayments::getPaymentByOnline(0);
		}		

		$self_delivery = isset($subscription_features['self_delivery'])?$subscription_features['self_delivery']:false;
		$self_delivery = $self_delivery==true?1:0;
		OptionsTools::saveOptions($merchant_id,'self_delivery',$self_delivery);

		$all_access = self::getAllAccess('merchant');
		
		$final_access = array_diff($all_access, $access_to_remove);
		$final_access = array_values($final_access);
		MerchantTools::saveMerchantMeta($merchant_id,$final_access,'menu_access');					
		MerchantTools::saveMerchantMeta($merchant_id,$allow_payment,'payment_gateway');	
	}
		
}
/*end class*/