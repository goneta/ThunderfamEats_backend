<?php
// CLEAR OLD CART DATA
Yii::app()->db->createCommand("DELETE FROM {{cart}} WHERE date_created < now() - interval 2 DAY")->query();
Yii::app()->db->createCommand("DELETE FROM {{cart_addons}} WHERE created_at < now() - interval 2 DAY")->query();
Yii::app()->db->createCommand("DELETE FROM {{cart_attributes}} WHERE last_update < now() - interval 2 DAY")->query();

$setttings = Yii::app()->params['settings'];
$home_search_mode = isset($setttings['home_search_mode'])?$setttings['home_search_mode']:'address';	
$location_searchtype = isset($setttings['location_searchtype'])?$setttings['location_searchtype']:'';

if(!isset($is_pos)){
	$is_pos = false;
}

if(!isset($exchange_rate)){
	$exchange_rate = 1;
}
if(!Yii::app()->user->isGuest && !$is_pos){	
	$client_id = Yii::app()->user->id;
	$social_strategy = Yii::app()->user->social_strategy;
}
if(!isset($client_id)){
	$client_id = null;
}
if(!isset($social_strategy)){
	$social_strategy = null;
}
if(!isset($latitude)){
	$latitude = null;
}
if(!isset($longitude)){
	$longitude = null;
}
if(!isset($delivery_time_estimation)){
	$delivery_time_estimation = 0;
}
if(!isset($preparation_time_estimation)){
	$preparation_time_estimation = 0;
}

$order_instructions = $order_instructions ?? null;

$merchant_id = CCart::getMerchantId($cart_uuid);
$found_transaction_type = isset($transaction_type)? (!empty($transaction_type)?true:false) : false;
if(!$found_transaction_type){
	$transaction_type = CCart::cartTransaction($cart_uuid,Yii::app()->params->local_transtype,$merchant_id);						
}
$options_data = OptionsTools::find(array('merchant_delivery_charges_type','merchant_tax',
'merchant_default_tip','merchant_enabled_tip','merchant_enabled_voucher','tips_in_transactions','merchant_tip_type','self_delivery'),$merchant_id);

/*GET TAX*/
$tax_settings = array(); $tax_delivery = array();
try {
	$tax_settings = CTax::getSettings($merchant_id);		
	$tax_enabled = true;
	CCart::addTaxCondition($tax_settings['tax']);
	CCart::setTaxType($tax_settings['tax_type']);
			
	if($tax_settings['tax_type']=="multiple"){
		$tax_delivery = CTax::taxForDelivery($merchant_id,$tax_settings['tax_type']);		
	} else $tax_delivery = $tax_settings['tax'];
	
} catch (Exception $e) {	
	$tax_enabled = false;	
}

$charge_type = isset($options_data['merchant_delivery_charges_type'])?$options_data['merchant_delivery_charges_type']:'';
$self_delivery = isset($options_data['self_delivery'])? ($options_data['self_delivery']==1?true:false) :false;

if(in_array('merchant_info',(array)$payload)){				
	$merchant_info = CCart::getMerchant($merchant_id,Yii::app()->language);		
	$unit = isset($merchant_info['distance_unit'])?$merchant_info['distance_unit']:$unit;
	$distance_covered = isset($merchant_info['delivery_distance_covered'])?floatval($merchant_info['delivery_distance_covered']):0;
	$merchant_lat = isset($merchant_info['latitude'])?$merchant_info['latitude']:'';
	$merchant_lng = isset($merchant_info['lontitude'])?$merchant_info['lontitude']:'';
	$delivery_zone_covered = isset($merchant_info['delivery_zone_covered'])?$merchant_info['delivery_zone_covered']:'';
	$delivery_zone_type = isset($merchant_info['delivery_zone_type'])?$merchant_info['delivery_zone_type']:'';
			
	$merchant_info['slug']=$merchant_info['restaurant_slug'];
	$merchant_info['restaurant_slug']=Yii::app()->createAbsoluteUrl($merchant_info['restaurant_slug']);		
	$merchant_info['logo']=CMedia::getImage($merchant_info['logo'],
	$merchant_info['path'],Yii::app()->params->size_image_thumbnail,
				CommonUtility::getPlaceholderPhoto('merchant'));
}		

if($home_search_mode=="address"){
	
	if(in_array('distance_local',(array)$payload) && $transaction_type=="delivery" ){				
		if($distance_resp = CCart::getLocalDistance($local_id,$unit,$merchant_lat,$merchant_lng)){												
			$distance = floatval($distance_resp['distance']);
			$address_component = $distance_resp['address_component'];
			$delivery_time_estimation = CommonUtility::distanceToTime($distance);
			if($distance_covered>0 && $distance>0){
				if($distance>$distance_covered){
					$out_of_range = true;
					$error[] = t("You're out of range");
					$error[] = t("This restaurant cannot deliver to your location.");
				}
			}					
		}
	}		

	if(in_array('distance_local_new',(array)$payload) && $transaction_type=="delivery" ){
		$is_in_delivery_area = false;
		
		// Check polygon delivery area first
		if(!empty($delivery_zone_covered) && !empty($delivery_zone_type) && $delivery_zone_type == 'POLYGON' && !empty($latitude) && !empty($longitude)){
			$stmt = "
			SELECT ST_Within(ST_PointFromText(CONCAT('POINT(', ".q($longitude).", ' ', ".q($latitude).", ')')), 
							ST_PolyFromText(CONCAT('POLYGON', ".q($delivery_zone_covered)."))) as is_within
			FROM {{merchant}}
			WHERE merchant_id = ".q($merchant_id)."
			";
			if($res = Yii::app()->db->createCommand($stmt)->queryRow()){
				$is_in_delivery_area = isset($res['is_within']) && $res['is_within'] == 1;
			}
		}
		
		// If not in polygon, check radius distance
		if(!$is_in_delivery_area){
			if($distance_resp = CMaps::getLocalDistance($unit,$latitude,$longitude,$merchant_lat,$merchant_lng)){		
				$distance = floatval($distance_resp);		
				if($distance_covered>0 && $distance>0){
					if($distance>$distance_covered){
						$out_of_range = true;
						$error[] = t("You're out of range");
						$error[] = t("This restaurant cannot deliver to your location.");
					} else {
						$is_in_delivery_area = true;
					}
				}
			}
		}
		
		// If still not in delivery area and we have polygon, show error
		if(!$is_in_delivery_area && !empty($delivery_zone_covered) && $delivery_zone_type == 'POLYGON'){
			$out_of_range = true;
			$error[] = t("You're out of range");
			$error[] = t("This restaurant cannot deliver to your location.");
		}
	}
				
	if(in_array('distance',(array)$payload) && $transaction_type=="delivery" ){	
		try {
			$distance_resp = CCart::getDistance( $client_id,$local_id,$unit,$merchant_lat,$merchant_lng);
			$distance = floatval($distance_resp['distance']);
			$address_component = $distance_resp['address_component'];
			if($distance_covered>0 && $distance>0){
				if($distance>$distance_covered){
					$out_of_range = true;
					$error[] = t("You're out of range");
					$error[] = t("This restaurant cannot deliver to your location.");
				}
			}
		} catch (Exception $e) {
			$error[] = $e->getMessage();		
		}
	}

	if(in_array('distance_new',(array)$payload) && $transaction_type=="delivery" ){	
		try {	    
			if(!empty(MapSdk::$map_provider)){			
			} else {
			MapSdk::$map_provider = Yii::app()->params['settings']['map_provider'];		   			
			if(MapSdk::$map_provider=="yandex"){			   
				MapSdk::$map_provider = 'mapbox';
			}			
			MapSdk::setKeys(array(
				'google.maps'=>Yii::app()->params['settings']['google_geo_api_key'],
				'mapbox'=>Yii::app()->params['settings']['mapbox_access_token'],
				'yandex'=>isset(Yii::app()->params['settings']['yandex_distance_api'])?Yii::app()->params['settings']['yandex_distance_api']:'',
			));
			}		 				   
			$params_distance = array(
				'from_lat'=>$merchant_lat,
				'from_lng'=>$merchant_lng,
				'to_lat'=>$latitude,
				'to_lng'=>$longitude,		      
				'unit'=>$unit,
				'mode'=>'driving'
			);		    		    		
			MapSdk::setMapParameters($params_distance);		    
			$distance_resp =  MapSdk::distance();				
			$distance = floatval($distance_resp['distance']);
			if($distance_covered>0 && $distance>0){
				if($distance>$distance_covered){
					$out_of_range = true;
					$error[] = t("You're out of range");
					$error[] = t("This restaurant cannot deliver to your location.");
				}
			}
		} catch (Exception $e) {
			$error[] = $e->getMessage();		
		}
	}
				
	$max_min_estimation = CCart::getMaxMinEstimationOrder($merchant_id,$transaction_type,$charge_type,$distance,$unit);	
	if($max_min_estimation){		 
		if( in_array('delivery_fee',(array)$payload) && $transaction_type=="delivery"  ){
			$delivery_fee = (float)$max_min_estimation['distance_price'];	
		}				 
		$minimum_order = floatval($max_min_estimation['minimum_order']) * $exchange_rate;
		$maximum_order = floatval($max_min_estimation['maximum_order']) * $exchange_rate;	
		CCart::savedAttributes($cart_uuid,'estimation', $max_min_estimation['estimation'] );	          	 	          
	}
} else if($home_search_mode=="location") {		 
	 $distance_found = false;
	 if(in_array('distance_local',(array)$payload)){
		$distance_found = true;
	 }
	 if(in_array('distance',(array)$payload)){
		$distance_found = true;
	 }	
	if($transaction_type=="delivery" && $distance_found){				
		try {			
			$locationFeeData = Clocations::getLocationFee($merchant_id,[
				'search_type'=>$location_searchtype,
				'merchant_id'=>$merchant_id,
				'city_id'=>isset($location['city_id'])?$location['city_id']:'',
				'area_id'=>isset($location['area_id'])?$location['area_id']:'',
				'state_id'=>isset($location['state_id'])?$location['state_id']:'',
			]);								
			if( in_array('delivery_fee',(array)$payload) && $transaction_type=="delivery"  ){
				$delivery_fee = (float)$locationFeeData['fee'];	
			}				 
			$min_order_free_delivery = floatval($locationFeeData['free_above_subtotal']) * $exchange_rate;
			$minimum_order = floatval($locationFeeData['minimum_order']) * $exchange_rate;
			$maximum_order = floatval($locationFeeData['maximum_amount']) * $exchange_rate;			
		} catch (Exception $e) {
			$error[] = $e->getMessage();				
		}
    }
}


/*GET CART*/
CCart::setPayload($payload);
CCart::getContent($cart_uuid,Yii::app()->language);	
$temp_subtotal = CCart::getSubTotal();
$temp_sub_total = floatval($temp_subtotal['sub_total']);
$sub_total_without_cnd = floatval($temp_subtotal['sub_total_without_cnd']);

$preparation_time_estimation = CCart::totalPreparationTime($merchant_id,date("Y-m-d"));
//dump($preparation_time_estimation);die();

// GET SERVICE CHARGE
$service_charge = [];
if(in_array('service_fee',(array)$payload)){
	$merchant_type = isset($merchant_info['merchant_type'])?$merchant_info['merchant_type']:2;
	$service_charge = CCheckout::getServiceFeeCharge($merchant_id,$merchant_type,$transaction_type);	
}

/*SERVICE FEE*/
if(in_array('service_fee',(array)$payload)){	
	if($service_charge){		
		$chargeType = isset($service_charge['charge_type'])?$service_charge['charge_type']:'';		
		$servicefee = isset($service_charge['service_fee'])?floatval($service_charge['service_fee']):0;
		if($servicefee>0){
			$service_fee = $chargeType=="percentage"? (($servicefee/100) * $temp_sub_total) : ($servicefee*$exchange_rate) ;			
			CCart::addCondition(array(
				'name'=>t("Service fee"),
				'type'=>"service_fee",
				'target'=>"total",
				'value'=>$service_fee,
				'taxable'=>isset($tax_settings['tax_service_fee'])?$tax_settings['tax_service_fee']:false,
				'tax'=>$tax_delivery,		  
			));
		}		
		
		$smallorder_fee = isset($service_charge['small_order_fee'])?floatval($service_charge['small_order_fee']):0;
		$small_less_order_based = isset($service_charge['small_less_order_based'])?floatval($service_charge['small_less_order_based']):0;
		$small_less_order_based = $small_less_order_based>0? ($small_less_order_based*$exchange_rate) : $small_less_order_based;		
		if($temp_sub_total<=$small_less_order_based){			
			$small_order_fee = $smallorder_fee*$exchange_rate;
			CCart::addCondition(array(
				'name'=>t("Small order fee"),
				'type'=>"small_order_fee",
				'target'=>"total",
				'value'=>$small_order_fee,
				'taxable'=>$tax_settings['tax_small_order_fee'] ?? false,
				'tax'=>$tax_delivery,		  
			));
		}		
	}
}				


// MIN FREE ORDER
$min_order_free_delivery = isset($min_order_free_delivery)?$min_order_free_delivery:0;
$min_order_free_meet = false; $minOrderFreeDelivery = [];
if($min_order_free_delivery>0){
	$minOrderFreeDelivery['min_free_delivery'] = $min_order_free_delivery;	
	if($min_order_free_delivery>0 && $temp_sub_total>0){
		if ($temp_sub_total >=  $min_order_free_delivery) {
			$minOrderFreeDelivery['percentage'] = 0;	
			$minOrderFreeDelivery['amount_needed'] = 0;	
			$minOrderFreeDelivery['label'] = '';
			$min_order_free_meet = true;
			$delivery_fee = 0;
		} else {
			$amount_needed = $min_order_free_delivery-$temp_sub_total;
			$discount_needed = ($amount_needed/$min_order_free_delivery) * 100;
			$minOrderFreeDelivery['percentage'] =  100-$discount_needed;	
			$minOrderFreeDelivery['amount_needed'] = $amount_needed;
			$minOrderFreeDelivery['label'] = t("Add {amount} to Get Free Delivery!",[
				'{amount}'=>Price_Formatter::formatNumber($amount_needed)
			]);
		}		
	} else {
		$minOrderFreeDelivery['percentage'] = 0;
		$minOrderFreeDelivery['amount_needed'] = 0;
		$minOrderFreeDelivery['label'] = '';
	}	
}


/*DELIVERY FEE*/
if(in_array('delivery_fee',(array)$payload)){
	if($delivery_fee>0 && $transaction_type=="delivery"){		

		if(!isset($free_delivery_on_first_order)){
			$free_delivery_on_first_order = false;
		}

		$user_total_orders = 0; $add_delivery_fee = true;		
		if($free_delivery_on_first_order && $social_strategy!="guest"){
			$draft_status = AttributesTools::initialStatus();		
			$not_in_status = AOrderSettings::getStatus(array('status_cancel_order','status_rejection'));
			array_push($not_in_status,$draft_status);    
			$user_total_orders = ACustomer::getOrdersTotal($client_id,0,array(), (array)$not_in_status );		
			if($user_total_orders<=0){				
				$add_delivery_fee = false;
			    $delivery_fee = 0;
				CCart::addCondition(array(
					'name'=>t("Delivery Fee: Free (First Order)"),
					'type'=>"free_delivery_fee",
					'target'=>"total",
					'value'=>0,
					'taxable'=>isset($tax_settings['tax_delivery_fee'])?$tax_settings['tax_delivery_fee']:false,
					'tax'=>$tax_delivery,				
				));				
			}
		}
		
		if($min_order_free_meet){
			$add_delivery_fee  = false;
		}
				
		if($add_delivery_fee){
			CCart::addCondition(array(
				'name'=>t("Delivery Fee"),
				'type'=>"delivery_fee",
				'target'=>"total",
				'value'=>$delivery_fee*$exchange_rate,
				'taxable'=>isset($tax_settings['tax_delivery_fee'])?$tax_settings['tax_delivery_fee']:false,
				'tax'=>$tax_delivery,		  
			));		
		}

	}
}

/*PACKAGING*/
if(in_array('packaging',(array)$payload)){
	if( $packaging_fee = CCart::getPackagingFee()){
		CCart::addCondition(array(
		  'name'=>t("Packaging fee"),
		  'type'=>"packaging_fee",
		  'target'=>"total",
		  'value'=>$packaging_fee*$exchange_rate,
		  'taxable'=>isset($tax_settings['tax_packaging'])?$tax_settings['tax_packaging']:false,
		  'tax'=>$tax_delivery,
		  //'tax'=>isset($tax_settings['tax'])?$tax_settings['tax']:'',
		));
	}
}

/*TAX*/
if(in_array('tax',(array)$payload) && $tax_enabled==true){	
	foreach ($tax_settings['tax'] as $tax_item) {
		$tax_rate = floatval($tax_item['tax_rate']);
		$tax_name = $tax_item['tax_name'];
		$tax_label = $tax_item['tax_in_price']==false?'{{tax_name}} {{tax}}%' : 'Total {{tax_name}} ({{tax}}%)';
		CCart::addCondition(array(
		  'name'=>t($tax_label,array(
			 '{{tax_name}}'=>t($tax_name),
			 '{{tax}}'=>$tax_rate
		  )),
		  'type'=>"tax",
		  'target'=>"total",
		  'taxable'=>false,
		  'value'=>"$tax_rate%",
		  'tax_id'=>$tax_item['tax_id'],
		  'value1'=>$tax_rate,
		));
	}	
}

/*TIP*/
if(in_array('tips',(array)$payload)){

	$enabled_voucher = isset($options_data['merchant_enabled_voucher'])?$options_data['merchant_enabled_voucher']:false;
	$tips_in_transactions = isset($options_data['tips_in_transactions'])?json_decode($options_data['tips_in_transactions']):array();
	$tip_type = isset($options_data['merchant_tip_type'])?$options_data['merchant_tip_type']:'fixed';
	
	//if($transaction_type=="delivery"){
	if(in_array($transaction_type,(array)$tips_in_transactions)){
		$default_tip = isset($options_data['merchant_default_tip'])?$options_data['merchant_default_tip']:0;
		$enabled_tip = isset($options_data['merchant_enabled_tip'])?$options_data['merchant_enabled_tip']:false;
		if($enabled_tip==1){

			if($tip_type=="percentage"){
				// $temp_subtotal = CCart::getSubTotal();
                // $temp_sub_total = floatval($temp_subtotal['sub_total']);								
				$default_tip = ($default_tip/100)*$temp_sub_total;				
			}

			if ( $tips = CCart::getTips($cart_uuid,$merchant_id,$default_tip)){								
				CCart::addCondition(array(
				'name'=>t("Courier tip"),
				'type'=>"tip",
				'target'=>"total",
				'value'=>floatval($tips)*$exchange_rate
				));
				CCart::savedAttributes($cart_uuid,'tips',$tips);	
			}			
			$tips_data = [
				'default_tip'=>$default_tip,
				'tips'=>floatval($tips),
			];			
	    }
	} else CCart::deleteAttributes($cart_uuid,'tips');
}

// $subtotal = CCart::getSubTotal();
// $sub_total = floatval($subtotal['sub_total']);
$sub_total = floatval($temp_subtotal['sub_total']);
$merchant_subtotal = isset($temp_subtotal['sub_total_original']) ? floatval($temp_subtotal['sub_total_original']) : 0;

// CARD FEE
if(!isset($card_fee)){
	$card_fee = 0;
}
if(in_array('card_fee',(array)$payload)){
	try {		
		$merchantType = $merchant_info['merchant_type'] ?? 2;
		$merchant_type = isset($merchant_info['merchant_type'])?$merchant_info['merchant_type']:2;		
		$merchant_type = $merchant_type==2?0:$merchant_type;
		$payments_credentials = CPayments::getPaymentCredentials($merchant_id,'',$merchant_type);					
		if($payment_method = CPayments::getDefaultPayment($client_id, $merchant_id, $merchantType )){			
			$payment_code = $payment_method['payment_code'] ?? '';
			$default_payment = isset($payments_credentials[$payment_code])?$payments_credentials[$payment_code]:'';			
			$payment_method['credentials'] = $default_payment;
			if(is_array($default_payment) && count($default_payment)>=1){
				switch ($payment_code) {
					case 'paymongo':	
					case 'paypal':	
					case "squareup":
						$card_fee_percent = isset($default_payment['attr5'])?floatval($default_payment['attr5']):0;
						$card_fee_fixed = isset($default_payment['attr6'])?floatval($default_payment['attr6']):0;						
						if($card_fee_percent>0 && $card_fee_fixed>0){
							$card_fee = ($sub_total*($card_fee_percent/100)) + $card_fee_fixed;
						} else if ( $card_fee_percent >0){							
							$card_fee = ($sub_total*($card_fee_percent/100));							
						} else if ( $card_fee_fixed >0){
							$card_fee = $card_fee_fixed;
						}						
						CCart::addCondition(array(
							'name'=>t("Card fee"),
							'type'=>"card_fee",
							'target'=>"total",
							'value'=>$card_fee,
							'taxable'=>isset($tax_settings['tax_card_fee'])?$tax_settings['tax_card_fee']:false,
							'tax'=>$tax_delivery,		  
						));
						break;					
				}
			}
		}		
	} catch (Exception $e) {
		//
	}
}

$send_order_amount = 0;
if(in_array('tableside',(array)$payload)){
	$table_number = isset($table_number)?$table_number:'';
	$send_order_amount = CCart::getSendOrderTotal($merchant_id,$cart_uuid,$table_number);	
}

$new_subtotal = $sub_total+$send_order_amount;

/*CHECK IF MAX AND MIN IS SATISFY*/
if($minimum_order>0){	
	if($minimum_order>$new_subtotal){
		$error[] = t("minimum order is {{minimum_order}}",array(
		 '{{minimum_order}}'=>Price_Formatter::formatNumber($minimum_order)
		));
	}
}
if($maximum_order>0){
	if($new_subtotal>$maximum_order){
		$error[] = t("maximum order is {{maximum_order}}",array(
		 '{{maximum_order}}'=>Price_Formatter::formatNumber($maximum_order)
		));
	}
}

/*PROMO AND DISCOUNT*/						
if(in_array('discount',(array)$payload)){
   $now = date("Y-m-d");
   if($cart_condition = CCart::cartCondition($cart_uuid)){
   	  foreach ($cart_condition as $condition) {
   	  	  if ( $meta_value = json_decode($condition['meta_value'],true) ){
   	  	  	  //dump($meta_value);
   	  	  	  $name = t($meta_value['name']);
			  if( $isjson = json_decode($meta_value['name'],true) ){							
					$name = t($isjson['label'],$isjson['params']);
			  }	
			  
			  if($meta_value['type']=="voucher"){							
				try {
					CPromos::setExchangeRate($exchange_rate);															
					$promo_details = CPromos::applyVoucher($merchant_id, $meta_value['id'] , $client_id , $now , $sub_total,$transaction_type);										
					$meta_value['value'] = - $promo_details['less_amount'];
				} catch (Exception $e) {	
					//dump($e->getMessage());
					break;
				}
			  } elseif ( $meta_value['type']=="offers" ){
				try {
					CPromos::setExchangeRate($exchange_rate);	
				    $promo_details = CPromos::applyOffers($merchant_id, $meta_value['id'], $now , $sub_total , $transaction_type);					
					$meta_value['max_discount_cap'] =  $promo_details['max_discount_cap'] ?? 0;
				} catch (Exception $e) {		
					//dump($e->getMessage());						
					break;
				}
			  }			
			  						  
			  CCart::addCondition(array(
			   'name'=>$name,
			   'type'=>$meta_value['type'],
			   'target'=>$meta_value['target'],
			   'value'=>$meta_value['value'],
			   'voucher_owner'=>isset($meta_value['voucher_owner'])?$meta_value['voucher_owner']:'',
			   'max_discount_cap'=>isset($meta_value['max_discount_cap'])?$meta_value['max_discount_cap']:null
			  ));
			  				  
   	  	  }
   	  }
   }
}

// POINTS DISCOUNT
if(in_array('points_discount',(array)$payload)){
	if($cart_condition = CCart::cartCondition($cart_uuid,['point_discount'])){		
		foreach ($cart_condition as $condition) {			
			if ( $meta_value = json_decode($condition['meta_value'],true) ){								
				$name = t($meta_value['name']);								
				$points_discount = floatval($meta_value['value'])*$exchange_rate;
				$total_after_discount = $sub_total - CCart::cleanNumber($points_discount);				
				if($total_after_discount>0){
					CCart::addCondition(array(
						'name'=>$name,
						'type'=>$meta_value['type'],
						'target'=>$meta_value['target'],					
						'value'=>$meta_value['value']*$exchange_rate,
					));				
					$points_earned = $meta_value['value'];				
				} else {
					//CCart::deleteAttributes($cart_uuid,'point_discount');
				}								
		    }
	    }
    }
}


/*SAVE IF THERE IS ERROR*/
if(is_array($error) && count($error)>=1){
	CCart::savedAttributes($cart_uuid,'error', json_encode($error) );
} else CCart::deleteAttributes($cart_uuid,'error');

$data  = array();
if(in_array('merchant_info',(array)$payload)){
   if(isset($merchant_info['restaurant_name'])){
	  $merchant_info['confirm_add_item'] = t("Your order contains items from {restaurant_name}. Create a new order to add items.",[
		'{restaurant_name}'=>$merchant_info['restaurant_name']
	  ]);
   }   
   $data['merchant']=$merchant_info;
}
if(in_array('items',(array)$payload)){
   $data['items']=CCart::getItems();   
   //dump($data['items']);die();
}
if(in_array('summary',(array)$payload)){
   $summary = CCart::getSummary();
   $data['summary']=$summary;   
   //dump($summary);
}

if(in_array('subtotal',(array)$payload)){
	$data['subtotal']=array(
	  'value'=>Price_Formatter::formatNumber($sub_total),
	  'raw'=>$sub_total
	);
}
if(in_array('total',(array)$payload)){
   $total = CCart::getTotal();      
   $data['total']=array(
     'value'=>Price_Formatter::formatNumber($total),
     'raw'=>Price_Formatter::convertToRaw($total),     
   );
}

$data['min_order_free_delivery'] = $minOrderFreeDelivery;
$data['address_needed'] = $transaction_type=="delivery"?true:false;

// POINTS
if(in_array('points',(array)$payload)){	
	$points_to_earn = CCart::getTotalPoints(
		isset($temp_subtotal['sub_total'])?$temp_subtotal['sub_total']:0,
		isset($total)?$total:0
	);	
	$points_label = $points_to_earn>0? t("This order will earns you {points} points!",['{points}'=>$points_to_earn]) :'';
}

/*CHECKOUT DATA*/
$checkout_data = array();
if(in_array('checkout',(array)$payload)){
	$checkout_data = array(
	  'transaction_type'=>$transaction_type,
	  'data'=>CCheckout::getTransactionData($cart_uuid,$transaction_type)
	);
	if(!Yii::app()->user->isGuest && !$is_pos){						
		CCart::savedAttributes($cart_uuid,'contact_number', Yii::app()->user->contact_number );
		CCart::savedAttributes($cart_uuid,'contact_number_prefix', Yii::app()->user->phone_prefix );
		CCart::savedAttributes($cart_uuid,'contact_email', Yii::app()->user->email_address );
		CCart::savedAttributes($cart_uuid,'customer_name', Yii::app()->user->first_name." ".Yii::app()->user->last_name );
		CCart::savedAttributes($cart_uuid,'first_name', Yii::app()->user->first_name);
		CCart::savedAttributes($cart_uuid,'last_name', Yii::app()->user->last_name);
	}	
}

/*GET CHECKOUT LINK*/
$go_checkout = array();
if(in_array('go_checkout',(array)$payload)){
	if(Yii::app()->user->isGuest && !$is_pos){
		$go_checkout = array(
		  'link'=>Yii::app()->createAbsoluteUrl("account/login?redirect=". Yii::app()->createAbsoluteUrl("/account/checkout") )
		);
	} else {
		$go_checkout = array(
		  'link'=>Yii::app()->createAbsoluteUrl("account/checkout")
		);
	}
}

/*GET ITEM COUNT*/
$items_count = 0;
if(in_array('items_count',(array)$payload)){
	$items_count = CCart::itemCount($cart_uuid);
}

// TRANSACTION INFORMATION
if(in_array('transaction_info',(array)$payload)){	
	$transaction_info = CCart::getAttributesAll($cart_uuid,['whento_deliver','transaction_type','estimation','delivery_date','delivery_time']);	
	$transaction_info['delivery_date_pretty'] = isset($transaction_info['delivery_date'])?Date_Formatter::date($transaction_info['delivery_date']):'';
	$transaction_info['delivery_time'] = isset($transaction_info['delivery_time'])?json_decode($transaction_info['delivery_time'],true):'';	
	$data_transaction = CServices::Listing(  Yii::app()->language );		
}

$order_instructions = [];
if(in_array('checkout',(array)$payload)){  
  if($transaction_type=="pickup"){
	$order_instructions = [
	  'title'=>t("This is pickup"),
	  'subtitle'=>t("Remember to collect your food at the restaurant when it's ready."),
	];
  } else if ($transaction_type=="dinein"){
	$order_instructions = [
	  'title'=>t("This is dinein"),
	  'subtitle'=>t("Remember to go to restaurant address."),
	];
  } else {
	$order_instructions = [
	  'title'=>t("This is")." ".$transaction_type,
	  'subtitle'=>t("You will be notified when order is ready."),
	];
  }
}

$merchant_tip_type = $merchant_tip_type ?? null;
$merchant_default_tip = $merchant_default_tip ?? null;
if(in_array('tips',(array)$payload) && $merchant_tip_type){ 	
	$tip_added = $tips_data['tips'] ?? 0;
	$tip_list = CTips::List($tip_added,$sub_total,$merchant_tip_type,$exchange_rate);	
}

$discountApplied = [];
if(in_array('points_discount',(array)$payload)){  
	$cart_discount = CCart::getAttributesAll($cart_uuid,['promo','promo_type','promo_id','point_discount']);	
	$pointDiscount = $cart_discount['point_discount'] ?? null;
	$pointDiscount = !empty($pointDiscount)?json_decode($pointDiscount,true):null;	
	
	$promoDiscount = $cart_discount['promo'] ?? null;
	$promoDiscount = !empty($promoDiscount)?json_decode($promoDiscount,true):null;	
	if(!empty($pointDiscount)){
		$discount = $pointDiscount['value'] ?? 0;
		$discount = ( floatval($discount) *-1);
		$points = $pointDiscount['points'] ?? 0;	    
		$discountApplied[] = [
			'discount_type'=>'points_discount',
			'label'=>t("{amount} off using {points} points.",[
				'{amount}'=>Price_Formatter::formatNumber($discount),
				'{points}'=>$points
			])
		];		
	}		
	if(!empty($promoDiscount)){
		$promoDiscountType = $promoDiscount['type'] ?? null;
		$promoDiscountValue = $promoDiscount['value'] ?? 0;		
		$promoDiscountId = $promoDiscount['id'] ?? null;
		if($promoDiscountType=="voucher"){
			$voucherFound = array_filter($summary, function ($item) {
				return $item['type'] === 'voucher';
			});
			if($voucherFound){
				$promoDiscountValue = ( floatval($promoDiscountValue) *-1) * $exchange_rate;
				$discountApplied[] = [
					'discount_type'=>$promoDiscountType,
					'discount_id'=>$promoDiscountId,
					'label'=>t("{amount} off",[
						'{amount}'=>Price_Formatter::formatNumber($promoDiscountValue),					
					])
				];		
			}			
		} elseif ( $promoDiscountType=="offers" ){					
			$offersFound = array_filter($summary, function ($item) {
				return $item['type'] === 'offers';
			});
			if($offersFound){
				$promoDiscountPercentage = CCart::cleanValues($promoDiscountValue);			
				$discountValue = ($promoDiscountPercentage/100) * $sub_total;						
				$discountApplied[] = [
					'discount_type'=>$promoDiscountType,
					'discount_id'=>$promoDiscountId,
					'label'=>t("{amount} off",[
						'{amount}'=>Price_Formatter::formatNumber($discountValue),					
					])
				];		
			}			
		}
	}		
}