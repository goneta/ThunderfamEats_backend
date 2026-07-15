<?php
class CCheckout
{
	
	public static function deliveryOption()
	{		
		return array(		  
		  'Leave it at my door'=>t("Leave it at my door"),
		  'Hand it to me'=>t("Hand it to me"),
		  'Meet outside'=>t("Meet outside"),
		);
	}
	
	public static function addressLabel()
	{
		return array(
		  "Home"=>t("Home"),
		  "Work"=>t("Work"),
		  "School"=>t("School"),
		  //"Friend house"=>t("Friend house"),
		  "Other"=>t("Other")
		);
	}
	
	public static function defaultAttrs()
	{
		return array(
		  'delivery_options'=>"Leave it at my door",
		  'address_label'=>"Home"
		);
	}
	
	public static function deliveryOptionList($format_type='',$time_selection=1)
	{		
		$delivery_option = array();
		if($format_type=="label_value"){
			$delivery_option[] = array('value'=>'now','label'=> t('Now') ,'short_name'=>t("Now") );
			$delivery_option[] = array('value'=>'schedule','label'=>t('Schedule for later'), 'short_name'=>t("Schedule") );
		} else {
			$delivery_option['now'] = array('value'=>'now','name'=> t('Now') ,'short_name'=>t("Now") );
		    $delivery_option['schedule'] = array('value'=>'schedule','name'=>t('Schedule for later'), 'short_name'=>t("Schedule") );
		}		
		
		if($time_selection==2){
			unset($delivery_option['now']);
		} else if ( $time_selection==3){
			unset($delivery_option['schedule']);
		}

		return $delivery_option;
	}
	
	public static function saveDeliveryAddress($place_id='',$client_id='',$resp = array())
	{
		if(empty($place_id)){
			return false;
		}
		
		$address_uuid = '';
		
		if(is_array($resp) && count($resp)>=1){
			//
		} else {
			try {
				$resp = CMaps::locationDetails($place_id);
			} catch (Exception $e) {
				$resp = '';
			}
		}
					
		if(is_array($resp) && count($resp)>=1){			
			$model = AR_client_address::model()->find('place_id=:place_id AND client_id=:client_id', 
		    array(':place_id'=>$place_id,'client_id'=>$client_id)); 		
		    if($model){
		    	$address_uuid =  $model->address_uuid;
		    } else {		  				
		    	$address_uuid = CommonUtility::generateUIID();
		    	$model = new AR_client_address;
		    	$model->client_id=intval($client_id);
		    	$model->address_uuid = $address_uuid;
				$model->address_label = 'Home';
		    	$model->place_id = $resp['place_id'];
		    	$model->address1 = $resp['address']['address1'];
				$model->street_number = $resp['address']['street_number'];
		    	$model->address2 = isset($resp['address'])? ( isset($resp['address']['address2'])?$resp['address']['address2']:'' ) : '';
		    	$model->postal_code = $resp['address']['postal_code'];
		    	$model->country = $resp['address']['country'];
		    	$model->country_code = isset($resp['address']['country_code'])?$resp['address']['country_code']:'' ;
		    	$model->formatted_address = $resp['address']['formatted_address'];
		    	$model->latitude = $resp['latitude'];
		    	$model->longitude = $resp['longitude'];
		    	if(!$model->save()){
		    		//dump($model->getErrors());
		    	}		    	
		    }
		}
		return $address_uuid;
	}
		
	public static function getMerchantTransactionList($merchant_id='',$lang='',$meta_name='services',$return_all=true)
	{
		$stmt="
		SELECT 
		a.service_id,
		a.service_code,
		
		CASE 
			WHEN b.service_name IS NOT NULL AND b.service_name != '' THEN b.service_name
			ELSE a.service_name
		END AS service_name

		FROM {{services}} a		
		left JOIN (
		   SELECT service_id, service_name FROM {{services_translation}} where language = ".q($lang)."
		) b 
		on a.service_id = b.service_id

		WHERE 
		a.status ='publish'
		and 
		a.service_code IN (
		  select meta_value from {{merchant_meta}}
		  where meta_name=".q($meta_name)."
		  and merchant_id = ".q($merchant_id)."
		)
		order by a.sequence asc
		";				
		$dependency = CCacheData::dependency();		
		$res = Yii::app()->db->cache(Yii::app()->params->cache, $dependency)->createCommand($stmt)->queryAll();									
		if($res){			
			$data = array();
			foreach ($res as $val) {		
				if($return_all){
					$val['service_name'] =  CHtml::decode($val['service_name']) ?? '';
				    $data[$val['service_code']] = $val;
				} else {
					$data[$val['service_code']] = CHtml::decode($val['service_name']) ?? '';
				}			
			}
			return $data;
		}
		throw new Exception( 'no results' );
	}
	
	public static function getFirstTransactionType($merchant_id='',$lang='')
	{
		if ( $data = self::getMerchantTransactionList($merchant_id,$lang)){
			$service_code = '';
			foreach ($data as $val) {
				$service_code = $val['service_code'];
				break;
			}
			return $service_code;
		}
		return false;
	}
	
	public static function getTransactionData($cart_uuid='',$transaction_type='')
	{
        $data = array();
        
		if($transaction_type=="delivery"){
		    $data = CCart::getAttributesAll($cart_uuid,array(
		     'delivery_distance','delivery_distance_unit','shipping_rate_id',
		     'whento_deliver','delivery_date','delivery_time','opt_contact_delivery','estimation','error'
		    ));		
		} elseif ( $transaction_type=="pickup"){
			$data = CCart::getAttributesAll($cart_uuid,array(		     
		     'whento_deliver','delivery_date','delivery_time','opt_contact_delivery','estimation','error'
		    ));		
		} elseif ( $transaction_type=="dinein" ){
			$data = CCart::getAttributesAll($cart_uuid,array(		     
		     'whento_deliver','delivery_date','delivery_time','opt_contact_delivery','estimation','error'
		    ));		
		} elseif ( $transaction_type=="takeout" ){
			$data = CCart::getAttributesAll($cart_uuid,array(		     
		     'whento_deliver','delivery_date','delivery_time','opt_contact_delivery','estimation','error'
		    ));		
		}

		if($data){ 		
		   
		   $whento_deliver = isset($data['whento_deliver'])?$data['whento_deliver']:'';
		   if($whento_deliver=="now"){
		   	  if(isset($data['delivery_time'])){
		   	     unset($data['delivery_time']);
		   	  }
		   }
		   
		   if(array_key_exists('delivery_date',$data)){
		   	   if(!empty($data['delivery_date'])){
		   	      $data['pretty_delivery_date'] = Date_Formatter::date($data['delivery_date']);
		   	   }
		   }
		   if(array_key_exists('delivery_time',$data)){
		   	   if(!empty($data['delivery_time'])){
		   	      //$data['pretty_delivery_time'] = Date_Formatter::dateTime($data['delivery_date']." ".$data['delivery_time']);
		   	      $data['pretty_delivery_time'] = self::jsonTimeToFormat($data['delivery_date'],$data['delivery_time']);
		   	   }
		   }
		   
		   
		   if(array_key_exists('estimation',$data)){
		   	   if(!empty($data['estimation'])){
		   	      $data['estimation'] = t("{{estimation}} mins",array(
		   	       '{{estimation}}'=>$data['estimation']
		   	      ));
		   	   }
		   }
		   
		   if(array_key_exists('delivery_distance',$data) ){
		   	  $data['delivery_distance'] = t("{{distance}} {{unit}} delivery distance",array(
		   	    '{{distance}}'=>$data['delivery_distance'],
		   	    '{{unit}}'=>isset($data['delivery_distance_unit'])?$data['delivery_distance_unit']:'',
		   	  ));
		   }
		   
		   if(array_key_exists('error',$data) ){
		   	  if ( $error = json_decode($data['error'],true)){
		   	  	   $data['error']=$error;
		   	  }
		   }
		   
		   
		} //if data
				
		return $data;
	}
	
	public static function shippingRateDetails($id='')
	{		
		$stmt="
		SELECT estimation FROM {{shipping_rate}}
		WHERE id=".q( intval($id) )."
		";		
		if(Yii::app()->params->db_cache_enabled){
		  $stmt_cache = "SELECT DISTINCT count(*),MAX(last_update) FROM {{shipping_rate}}";
		  $dependency = new CDbCacheDependency($stmt_cache);
		  $res = Yii::app()->db->cache(Yii::app()->params->cache, $dependency)->createCommand($stmt)->queryRow();		  
		} else $res = Yii::app()->db->createCommand($stmt)->queryRow();	
		if($res){
			return $res;
		}
		return false;
	}
	
	public static function getServiceFee($merchant_id='', $service_code='')
	{
		$stmt="
		SELECT service_fee FROM {{view_services_fee}}
		WHERE merchant_id=".q( intval($merchant_id) )."
		AND service_code =".q($service_code)."
		";				
		if ( $res = CCacheData::queryRow($stmt)){
			return floatval($res['service_fee']);
		}
		return false;				
	}

	public static function getServiceFeeCharge($merchant_id=0, $merchant_type=2,$transaction_type=0)
	{		
		$merchant_id = $merchant_type==1?$merchant_id:0;

		$stmt = "		
		select 
		a.merchant_id,
		b.service_code,
		a.charge_type,
		a.service_fee,
		a.small_order_fee,
		a.small_less_order_based
		from {{services_fee}} a
		left join {{services}} b
		on
		a.service_id = b.service_id

		where merchant_id=".q($merchant_id)."
		and service_code=".q($transaction_type)."
		";		
		if ( $res = CCacheData::queryRow($stmt)){
			return $res;
		}
		return false;
	}
		
	public static function getWhenDeliver($cart_uuid=''){
		$atts = CCart::getAttributesAll($cart_uuid,array('whento_deliver'));
		if($atts){
			$whento_deliver = isset($atts['whento_deliver'])?$atts['whento_deliver']:'';				
		} else {
			$whento_deliver = "now";
		}
		return $whento_deliver;
	}
	
	public static function getScheduleDateTime($cart_uuid='',$whento_deliver='')
	{
		$delivery_datetime = '';
		if($whento_deliver=="schedule"){
			if($atts = CCart::getAttributesAll($cart_uuid,array('delivery_date','delivery_time'))){	
				$delivery_datetime = CCheckout::jsonTimeToFormat($atts['delivery_date'],$atts['delivery_time']);
			}
		}
		return $delivery_datetime;
	}
	
	public static function jsonTimeToFormat($delivery_date='',$delivery_time='')
	{		
		$delivery_datetime='';
		if($_delivery_time = json_decode($delivery_time,true)){	
			$start_date = $delivery_date." ".$_delivery_time['start_time'];
			$end_date = $delivery_date." ".$_delivery_time['end_time'];
			$delivery_datetime = Date_Formatter::dateTime($start_date,"ccc,LLL dd, hh:mm a");
   	  	 	$delivery_datetime.=" - ";
   	  	 	$delivery_datetime.= Date_Formatter::dateTime($end_date,"hh:mm a");
		}
		return $delivery_datetime;
	}
	
	public static function jsonTimeToSingleTime($data='',$time='start_time')
	{
		$delivery_time = '';
		if($_delivery_time = json_decode($data,true)){			
			$delivery_time = $_delivery_time[$time];
		}
		return $delivery_time;
	}

	public static function getPhoneCodeByUserID($client_id='')
	{		
		$dependency = CCacheData::dependency();
		$criteria=new CDbCriteria();
		$criteria->alias = "a";
		$criteria->select="a.phone_prefix , b.shortcode";
		$criteria->condition = "a.client_id=:client_id ";		    
		$criteria->join='LEFT JOIN {{location_countries }} b on a.phone_prefix = b.phonecode ';
		$criteria->params  = array(			  
			':client_id'=>intval($client_id)
		);
		$model = AR_client::model()->cache(Yii::app()->params->cache, $dependency)->find($criteria);
		if($model){			
			if(!empty($model->shortcode)){
				return $model->shortcode;
			}
		}		
		return false;
	}
	
	public static function addressIsNeeded($merchant_id='', $local_id='', $cart_uuid='')
	{
		try {
			$data = CCheckout::getMerchantTransactionList($merchant_id,Yii::app()->language);			
			$has_delivery = isset($data['delivery'])?true:false;			
			$address_needed = empty($local_id)?true:false;
			if($address_needed){
				$address_needed = $has_delivery;
			}			
			if(!empty($cart_uuid) && $address_needed===true){
				$transaction = CCart::cartTransaction($cart_uuid,Yii::app()->params->local_transtype,$merchant_id);
				$address_needed = $transaction=="delivery"?true:false;
			}			
			return $address_needed;
		} catch (Exception $e) {
			throw new Exception( $e->getMessage() );
		}
	}

	public static function customeFieldList()
	{
		$data = [];
		$data['one'] = t("One");
		$data['two'] = t("Two");
		$data['three'] = t("Three");		
		return $data;
	}
	
}
/*end class*/