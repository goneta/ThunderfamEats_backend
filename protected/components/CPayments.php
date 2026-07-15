<?php
class CPayments
{
	public static function paidStatus()
	{
		return 'paid';
	}
	
	public static function umpaidStatus()
	{
		return 'unpaid';
	}

	public static function partialyStatus()
	{
		return 'partially_paid';
	}
	
	public static function getMerchantForCredentials($order_uuid='')
	{
		$stmt="
		SELECT a.merchant_id,a.payment_code,a.total,a.order_id,
		a.client_id,
		b.merchant_type, b.restaurant_name
		FROM {{ordernew}} a
		LEFT JOIN {{merchant}} b
		ON
		a.merchant_id = b.merchant_id
		WHERE 
		a.order_uuid = ".q($order_uuid)."
		";
		if ($res = CCacheData::queryRow($stmt)){
			return $res;
		}
		throw new Exception( 'no results' );
	}
	
	public static function PaymentList($merchant_id='', $with_key=false , $hide_payment = false, $currency_code='',$filter_is_online=false,$is_online=0)
	{
		$merchant = CMerchantListingV1::getMerchant($merchant_id);

		$in_online = '';
		if($filter_is_online){
			$in_online = "AND a.is_online=".q($is_online)."";
		}
		
		$data = array();
		$stmt="
		SELECT a.payment_name,a.payment_code,a.logo_type,a.logo_class,a.logo_image,a.path,a.attr5,a.attr6
		FROM {{payment_gateway}} a	
		WHERE a.payment_code IN (
		  select meta_value from {{merchant_meta}}
		  where meta_name='payment_gateway'
		  and meta_value = a.payment_code
		  and merchant_id = ".q($merchant_id)."
		)	
		AND a.status='active'
		$in_online
		ORDER BY a.sequence ASC
		";		
		if($merchant->merchant_type==1){
		   $stmt="
		   SELECT a.payment_name,a.payment_code,a.logo_type,a.logo_class,a.logo_image,a.path,a.attr5,a.attr6
			FROM {{payment_gateway}} a	
			WHERE a.payment_code IN (
			  select payment_code from {{payment_gateway_merchant}}
			  where merchant_id=".q($merchant_id)."
			  and status='active'
			  and payment_code in (
			      select meta_value from {{merchant_meta}}
				  where meta_name='payment_gateway'
				  and meta_value = a.payment_code
				  and merchant_id = ".q($merchant_id)."
			  )
			)	
			AND a.status='active'
			$in_online
			ORDER BY a.sequence ASC
		   ";
		}				
		if( $res = CCacheData::queryAll($stmt)){		 

		    $hide_payment_list = [];
		    if($hide_payment){
				try {
					$hide_payment_list = CMulticurrency::getHidePaymentList($currency_code);
				} catch (Exception $e) {
					//
				}			
		   }			

		   foreach ($res as $val) {		   	  			  

			//   HIDE PAYMENT GATEWAY
			  if($hide_payment){
				 if(in_array($val['payment_code'],(array)$hide_payment_list)){
					continue;
				 }
			  }

		   	  $logo_image = '';
		   	  if(!empty($val['logo_image'])){
		   	    $logo_image = CMedia::getImage($val['logo_image'],$val['path'],Yii::app()->params->size_image_thumbnail,
				CommonUtility::getPlaceholderPhoto('item'));
		   	  }

			  $card_fee_percent = 0; $card_fee_fixed = 0;
			  switch ($val['payment_code']) {
				case 'paymongo':
				case 'paypal':
				case 'squareup':
					$card_fee_percent =  $val['attr5'];
					$card_fee_fixed =  $val['attr6'];
					break;									
			  }
			
		   	  if($with_key)	{
		   	  	$data[$val['payment_code']] = array(
			   	    'payment_name'=>t($val['payment_name']),
			   	    'payment_code'=>$val['payment_code'],
			   	    'logo_type'=>$val['logo_type'],
			   	    'logo_class'=>$val['logo_class'],
			   	    'logo_image'=>$logo_image,
					'card_fee_percent'=>$card_fee_percent,			
					'card_fee_fixed'=>$card_fee_fixed,
			   	  );
		   	  } else {
			   	  $data[] = array(
			   	    'payment_name'=>t($val['payment_name']),
			   	    'payment_code'=>$val['payment_code'],
			   	    'logo_type'=>$val['logo_type'],
			   	    'logo_class'=>$val['logo_class'],
			   	    'logo_image'=>$logo_image,		
					'card_fee_percent'=>$card_fee_percent,			
					'card_fee_fixed'=>$card_fee_fixed,			
			   	  );
		   	  }
		   }		   
		   return $data;
		} 
		throw new Exception( 'no available payment method' );
	}
	
	public static function getPaymentCredentials($merchant_id='', $payment_code='',$merchant_type='')
	{
		$and = !empty($payment_code)?" AND payment_code =".q($payment_code)." ":'';
					
		if($merchant_type==1){			
			$stmt="
			SELECT payment_uuid as id,merchant_id,payment_code,is_live,attr1,attr2,attr3,attr4,attr5,attr6,attr7,attr8,attr9,split,capture
			FROM {{payment_gateway_merchant}} a
			WHERE merchant_id = ".q($merchant_id)."					
			$and
			ORDER BY sequence ASC
			";						
		} else {			
			$stmt="
			SELECT payment_id as id,payment_code,is_live,attr1,attr2,attr3,attr4,attr5,attr6,attr7,attr8,attr9,split,capture
			FROM {{payment_gateway}} a		
			WHERE 1	
			$and
			ORDER BY sequence ASC
			";
		}						
		if( $res = CCacheData::queryAll($stmt)){
			$data = array();
			foreach ($res as $val) {				
				$data[$val['payment_code']] = array(
				  'id'=>$val['id'],
				  'is_live'=>intval($val['is_live']),
				  'attr1'=>CommonUtility::safeTrim($val['attr1']),
				  'attr2'=>CommonUtility::safeTrim($val['attr2']),
				  'attr3'=>CommonUtility::safeTrim($val['attr3']),
				  'attr4'=>CommonUtility::safeTrim($val['attr4']),
				  'attr4'=>CommonUtility::safeTrim($val['attr4']),
				  'attr5'=> isset($val['attr5']) ? CommonUtility::safeTrim($val['attr5']) :'',
				  'attr6'=> isset($val['attr6']) ? CommonUtility::safeTrim($val['attr6']) :'',
				  'attr7'=> isset($val['attr7']) ? CommonUtility::safeTrim($val['attr7']) :'',
				  'attr8'=> isset($val['attr8']) ? CommonUtility::safeTrim($val['attr8']) :'',
				  'attr9'=> isset($val['attr9']) ? CommonUtility::safeTrim($val['attr9']) :'',
				  'merchant_id'=>isset($val['merchant_id'])?$val['merchant_id']:0,
				  'merchant_type'=>$merchant_type,
				  'split'=> isset($val['split']) ? CommonUtility::safeTrim($val['split']) :'',				  
				  'capture'=> isset($val['capture']) ? CommonUtility::safeTrim($val['capture']) :'',				  
				);
			}			
			return $data;
		}
		throw new Exception( 'no results payment credentials' );
	}	

	public static function getPaymentCredentialsPublic($merchant_id='', $payment_code='',$merchant_type='',$filters_sql='')
	{
		$and = !empty($payment_code)?" AND payment_code =".q($payment_code)." ":'';
								
		if($merchant_type==1){			
			$stmt="
			SELECT merchant_id,payment_code,is_live,attr1,attr2,attr3,attr4
			FROM {{payment_gateway_merchant}} a
			WHERE merchant_id = ".q($merchant_id)."		
			AND status='active'			
			$and
			$filters_sql
			";						
		} else {			
			$stmt="
			SELECT payment_code,is_live,attr1,attr2,attr3,attr4
			FROM {{payment_gateway}} a
			WHERE attr_json!=''
			AND status='active'
			$and
			$filters_sql
			";
		}		
		if( $res = CCacheData::queryAll($stmt)){			
			$data = array(); $keys = '';
			foreach ($res as $val) {				
				switch ($val['payment_code']) {
					case 'paypal':					
					case "razorpay":
					case "mercadopago":
					case "bank":
						$keys = CommonUtility::safeTrim($val['attr1']);
						break;

					case "pixcard":
						$keys = CommonUtility::safeTrim($val['attr4']);
						break;

					case "squareup":
						$keys = [
							'application_id'=>CommonUtility::safeTrim($val['attr1']),
							'access_token'=>CommonUtility::safeTrim($val['attr2']),
							'location_id'=>CommonUtility::safeTrim($val['attr3']),
						];
						break;

					default:
						$keys = CommonUtility::safeTrim($val['attr2']);
						break;
				}
				$data[$val['payment_code']] = array(
				  'is_live'=>intval($val['is_live']),				  
				  'attr2'=>$keys,				
				  'merchant_id'=>isset($val['merchant_id'])?$val['merchant_id']:0,
				  'merchant_type'=>$merchant_type,
				);
			}			
			return $data;
		}
		throw new Exception( 'no results payment credentials' );
	}	
	
	public static function DefaultPaymentList($with_key=false)
	{
		$data = array();
		$stmt="
		SELECT a.payment_name,a.payment_code,a.logo_type,a.logo_class,a.logo_image,a.path
		FROM {{payment_gateway}} a			
		WHERE a.status='active'
		ORDER BY a.sequence ASC
		";		
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
		   foreach ($res as $val) {		   	  
		   	  $logo_image = CMedia::getImage($val['logo_image'],$val['path'],Yii::app()->params->size_image_thumbnail,
				CommonUtility::getPlaceholderPhoto('item'));
			  if($with_key){
				$data[$val['payment_code']] = array(
					'payment_name'=>$val['payment_name'],
					'payment_code'=>$val['payment_code'],
					'logo_type'=>$val['logo_type'],
					'logo_class'=>$val['logo_class'],
					'logo_image'=>$logo_image,
				  );
			  } else {
				$data[] = array(
					'payment_name'=>$val['payment_name'],
					'payment_code'=>$val['payment_code'],
					'logo_type'=>$val['logo_type'],
					'logo_class'=>$val['logo_class'],
					'logo_image'=>$logo_image,
				  );
			  }		   	  
		   }
		   return $data;
		} 
		throw new Exception( 'no available payment method' );
	}
		
	public static function SavedPaymentList($client_id="" , $merchant_type='', $merchant_id='' , 
	$hide_payment=false , $currency_code='' , $exclude='', $with_key='')
	{		
		$and = "";
		$merchant_id_orig = $merchant_id;
		if($merchant_type==1){
			$and = "AND a.merchant_id =".q($merchant_id)." ";
		} else $and = "AND a.merchant_id = 0 ";
		
		$data = array(); $and2=''; $and_exclude ='';

		if($merchant_id_orig>0){
		    $and2 = "
			AND a.payment_code IN (
				select meta_value from {{merchant_meta}}
				where meta_name='payment_gateway'
				and meta_value=a.payment_code 
				and merchant_id =".q($merchant_id_orig)."
			)
			";
		}

		if(!empty($exclude)){
			if($exclude=="offline_payment"){				
				$payment_exclude = AttributesTools::excludePayment();
				$and_exclude = "
				AND a.payment_code NOT IN (".CommonUtility::arrayToQueryParameters($payment_exclude).")
				";
			}
		}

		$stmt="
		SELECT a.payment_uuid,a.payment_code,a.as_default,a.reference_id,
		a.attr1,a.attr2,attr5,attr6,
		b.payment_name, b.logo_type, b.logo_class, b.logo_image, b.path,b.attr1 as attr_required ,b.is_online
		
		FROM {{client_payment_method}} a
		LEFT JOIN {{payment_gateway}} b
		ON
		a.payment_code = b.payment_code
		
		WHERE a.client_id=".q($client_id)."		
		AND b.status = 'active'				
		$and
		$and2
		$and_exclude
		ORDER BY FIELD(as_default, '1') DESC, payment_method_id DESC		
		";						
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){		
			
			$hide_payment_list = [];
			if($hide_payment){
				try {
					$hide_payment_list = CMulticurrency::getHidePaymentList($currency_code);
				} catch (Exception $e) {
					//
				}			
			}
			
			$block_payment_list = ACustomer::getBlockPaymentmethod($client_id);			

			foreach ($res as $val) {

				//  HIDE PAYMENT GATEWAY
				if($hide_payment){
					if(in_array($val['payment_code'],(array)$hide_payment_list)){
					   continue;
					}
				}

				if(is_array($block_payment_list) && count($block_payment_list)>=1){
					if(in_array($val['payment_code'],(array)$block_payment_list)){
					   continue;
					}
				}

				switch ($val['payment_code']) {
					case 'paymongo':
					case 'paypal':
					case 'squareup':
						$val['card_fee_percent'] =  $val['attr5'];
						$val['card_fee_fixed'] =  $val['attr6'];
						break;									
				}

				unset($val['attr5']);
				unset($val['attr6']);

				$logo_image = '';
				if(!empty($val['logo_image'])){
					$logo_image = CMedia::getImage($val['logo_image'],$val['path'],
					Yii::app()->params->size_image_thumbnail,
				    CommonUtility::getPlaceholderPhoto('item'));
				}
				if($val['is_online']==1){
					$val['attr_required']='';
				}
				$val['logo_image']=$logo_image;
				$val['payment_name']= t($val['payment_name']);
				
				if($with_key){
					$data[$val['payment_code']][]=$val;
				} else $data[]=$val;				
			}
			return $data;
		}
		throw new Exception( 'no available saved payment' );
	}
	
	public static function MerchantSavedPaymentList($merchant_id)
	{		
	
		$data = array();
		$stmt="
		SELECT a.payment_uuid,a.payment_code,a.as_default,
		a.attr1,a.attr2,
		b.payment_name, b.logo_type, b.logo_class, b.logo_image, b.path
		
		FROM {{merchant_payment_method}} a
		LEFT JOIN {{payment_gateway}} b
		ON
		a.payment_code = b.payment_code
		
		WHERE a.merchant_id=".q($merchant_id)."		
		AND b.status = 'active'			
		ORDER BY payment_method_id DESC		
		";					
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){			
			foreach ($res as $val) {
				$logo_image = '';
				if(!empty($val['logo_image'])){
					$logo_image = CMedia::getImage($val['logo_image'],$val['path'],
					Yii::app()->params->size_image_thumbnail,
				    CommonUtility::getPlaceholderPhoto('item'));
				}
				$val['logo_image']=$logo_image;
				$data[]=$val;
			}
			return $data;
		}
		throw new Exception( 'no available saved payment' );
	}
	
	public static function delete($client_id='',$payment_uuid='')
	{
		
		$model = AR_client_payment_method::model()->find('client_id=:client_id AND payment_uuid=:payment_uuid', 
	        array(
	         ':client_id'=>$client_id,
	         ':payment_uuid'=>$payment_uuid,
	        )); 		
		        
	    if($model){    			
			if($model->delete()){
				return true;
			}
	    } else throw new Exception( 'record not found.' );
		throw new Exception( 'cannot delete records please try again.' );
	}
	
	public static function defaultPayment($client_id='')
	{
		$model = AR_client_payment_method::model()->find('client_id=:client_id AND as_default=:as_default', 
	        array(
	         ':client_id'=>intval($client_id),
	         ':as_default'=>1,
	        ));
	    if($model){
	    	return array(
	    	  'payment_uuid'=>$model->payment_uuid,
	    	  'payment_code'=>$model->payment_code,
	    	  'reference_id'=>$model->reference_id
	    	);
	    }
	    return false;
	}

	public static function defaultPaymentOnline($client_id='')
	{
		$stmt="
		SELECT a.payment_uuid,a.payment_code,a.as_default,a.reference_id,
		a.attr1,a.attr2,attr5,attr6,
		b.payment_name, b.logo_type, b.logo_class, b.logo_image, b.path,b.attr1 as attr_required ,b.is_online
		
		FROM {{client_payment_method}} a
		LEFT JOIN {{payment_gateway}} b
		ON
		a.payment_code = b.payment_code
		
		WHERE a.client_id=".q($client_id)."		
		AND b.status = 'active'				
		AND a.as_default=1
		AND a.merchant_id=0
		AND b.is_online=1				
		";							
		if($res = Yii::app()->db->createCommand($stmt)->queryRow()){
			$logo_image = '';
			if(!empty($res['logo_image'])){
				$logo_image = CMedia::getImage($res['logo_image'],$res['path'],
				Yii::app()->params->size_image_thumbnail,
				CommonUtility::getPlaceholderPhoto('item'));
			}
			$res['logo_image']=$logo_image;		
			return $res;
		}
	    return false;
	}
	
	public static function getPaymentMethod($payment_uuid='', $client_id='',$return_all=false)
	{
		$model = AR_client_payment_method::model()->find('client_id=:client_id AND payment_uuid=:payment_uuid', 
	        array(
	         ':client_id'=>intval($client_id),
	         ':payment_uuid'=>$payment_uuid,
	        ));
	    if($model){
			if($return_all){
				return $model;
			} else {
				return array(
					'payment_uuid'=>$model->payment_uuid,
					'payment_code'=>$model->payment_code,
					'reference_id'=>$model->reference_id
				  );
			}	    	
	    }
	    return false;
	}
	
	public static function getPaymentMethodMeta($payment_uuid='', $client_id='')
	{
		$stmt="
		SELECT meta_name,meta_value
		FROM {{payment_method_meta}}
		WHERE payment_method_id IN (
		   select payment_method_id
		   from {{client_payment_method}}
		   where payment_uuid = ".q($payment_uuid)."
		   and
		   client_id = ".q( intval($client_id) )."
		)
		";		
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
			$data = array();
			foreach ($res as $val) {
				$data[$val['meta_name']] = $val['meta_value'];
			}
			return $data;
		}
		throw new Exception( 'No payment method meta found' );
	}

	public static function getPaymentTypeOnline($is_online=1)
	{
		$model = AR_payment_gateway::model()->findAll("is_online=:is_online",array(
		  ':is_online'=>intval($is_online)
		));
		if($model){
			$data = array();
			foreach ($model as $items) {
			   	$data[$items->payment_code] = array(
			   	  'payment_code'=>$items->payment_code,
			   	  'payment_name'=>$items->payment_name,
			   	);
			}
			return $data;
		}
		return false;
	}
	
	public static function getPaymentTypeCapture($capture=1)
	{
		$model = AR_payment_gateway::model()->findAll("capture=:capture",array(
		  ':capture'=>intval($capture)
		));
		if($model){
			$data = array();
			foreach ($model as $items) {
			   	$data[$items->payment_code] = array(
			   	  'payment_code'=>$items->payment_code,
			   	  'payment_name'=>$items->payment_name,
			   	);
			}
			return $data;
		}
		return false;
	}

	public static function getPaymentList($is_online=1,$prefix='',$reference='',$payment_codes=array())
	{		
		$data = array(); $and = "";

		if(is_array($payment_codes) && count($payment_codes)>=1){
			$and.=" AND payment_code IN (".CommonUtility::arrayToQueryParameters($payment_codes).")";
		}		

		$stmt="
		SELECT a.payment_name,a.payment_code,a.logo_type,a.logo_class,a.logo_image,a.path
		FROM {{payment_gateway}} a			
		WHERE a.status='active'
		AND a.is_online = ".q(intval($is_online))."
		$and
		ORDER BY a.sequence ASC
		";					
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
		   foreach ($res as $val) {		   	  
		   	  $logo_image = CMedia::getImage($val['logo_image'],$val['path'],Yii::app()->params->size_image_thumbnail,
				CommonUtility::getPlaceholderPhoto('item'));
		   	  $data[] = array(
		   	    'payment_name'=>$val['payment_name'],
		   	    'payment_code'=>$val['payment_code'],
		   	    'logo_type'=>$val['logo_type'],
		   	    'logo_class'=>$val['logo_class'],
		   	    'logo_image'=>$logo_image,
		   	    'prefix'=>$prefix,
		   	    'reference'=>$reference
		   	  );
		   }
		   return $data;
		} 
		throw new Exception( 'no available payment method' );
	}
	
	public static function getMerchantPayment($merchant_id=0, $payment_uuid='')
	{
		$model = AR_merchant_payment_method::model()->find("merchant_id=:merchant_id AND payment_uuid=:payment_uuid",array(
		  ':merchant_id'=>intval($merchant_id),
		  ':payment_uuid'=>$payment_uuid
		));
		if($model){
			return $model;
		}
		throw new Exception( 'payment not found' );
	}
	
	public static function getSplitProvider()
	{
		$model = AR_payment_gateway::model()->findAll("split=:split",[
			':split'=>1
		]);
		if($model){
			$data = [];
			$default = $model[0]->payment_code;
			foreach ($model as $items) {				
				$data[]= [
					'payment_id'=>$items->payment_id,
					'payment_name'=>$items->payment_name,
					'payment_code'=>$items->payment_code,
				];
			}
			return [
				'data'=>$data,
				'default'=>$default,
			];
		}
		throw new Exception( 'No available provider' );
	}	


	/*
	  commission = 2
	  membership = 1
	*/
	public static function getBankDepositInstructions($merchant_type=2,$merchant_id=0)
	{
		if($merchant_type>0){
			if($merchant_type==2){
				$model = AR_payment_gateway::model()->find("payment_code=:payment_code and status=:status",[
					':payment_code'=>"bank",
					':status'=>"active"
				]);
				if($model){
					return [
						'content'=>$model->attr9,
						'subject'=>$model->attr1
					];
				} else throw new Exception( 'cannot find bank deposit' );		
			} else if ($merchant_type==1){				
				$model = AR_payment_gateway_merchant::model()->find("payment_code=:payment_code and status=:status and merchant_id=:merchant_id",[
					':payment_code'=>"bank",
					':status'=>"active",
					':merchant_id'=>$merchant_id
				]);
				if($model){
					return [
						'content'=>$model->attr9,
						'subject'=>$model->attr1
					];
				} else throw new Exception( 'cannot find bank deposit' );		
			}
		} else throw new Exception( 'Invalid merchant type' );		
	}

	public static function getTotalPayments($merchant_id=0,$transaction_type='credit', $status='paid')
	{
		$stmt="SELECT sum(trans_amount) as total
		FROM {{ordernew_transaction}}
		WHERE merchant_id=".q($merchant_id)."
		AND transaction_type=".q($transaction_type)."
		AND status=".q($status)."
		";		
		if($res = Yii::app()->db->createCommand($stmt)->queryRow()){
			return $res['total'];
		} else throw new Exception( HELPER_NO_RESULTS );
	}

	public static function getPaymentByCode($payment_code='')
	{
		$model = AR_payment_gateway::model()->find("payment_code=:payment_code",[
			':payment_code'=>$payment_code
		]);
		if($model){
			return $model;
		} else throw new Exception( t("Payment code not found") );
	}

	public static function getPaymentInfo($payment_uuid='')
	{
		$stmt = "
		SELECT 
		a.payment_code,
		b.payment_name
		FROM {{client_payment_method}} a
		LEFT JOIN {{payment_gateway}} b
		ON
		a.payment_code = b.payment_code
		WHERE
		a.payment_uuid=".q($payment_uuid)."
		";		
		if($res = Yii::app()->db->createCommand($stmt)->queryRow()){
			return $res;
		}
		throw new Exception( t("Payment code not found") );
	}

	public static function getPaymentListByFilter($is_online=0,$payment_codes=array())
	{
		$payment_codes = CommonUtility::arrayToQueryParameters($payment_codes);
		$data = array();
		$stmt="
		SELECT a.payment_name,a.payment_code,a.logo_type,a.logo_class,a.logo_image,a.path
		FROM {{payment_gateway}} a			
		WHERE a.status='active'		
		AND a.payment_code IN (".$payment_codes.")
		ORDER BY a.sequence ASC
		";					
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
		   foreach ($res as $val) {		   	  
		   	  $logo_image = CMedia::getImage($val['logo_image'],$val['path'],Yii::app()->params->size_image_thumbnail,
				CommonUtility::getPlaceholderPhoto('item'));
		   	  $data[] = array(
		   	    'payment_name'=>$val['payment_name'],
		   	    'payment_code'=>$val['payment_code'],
		   	    'logo_type'=>$val['logo_type'],
		   	    'logo_class'=>$val['logo_class'],
		   	    'logo_image'=>$logo_image,
		   	  );
		   }
		   return $data;
		} 
		throw new Exception( 'no available payment method' );
	}

	public static function DriverSavedPaymentList($driver_id="")
	{						
		$data = array();
		
		$stmt="
		SELECT a.payment_uuid,a.payment_code,a.as_default,a.reference_id,
		a.attr1,a.attr2,
		b.payment_name, b.logo_type, b.logo_class, b.logo_image, b.path,b.attr1 as attr_required ,b.is_online
		
		FROM {{driver_payment_method}} a
		LEFT JOIN {{payment_gateway}} b
		ON
		a.payment_code = b.payment_code
		
		WHERE a.driver_id=".q($driver_id)."		
		AND b.status = 'active'			
		ORDER BY payment_method_id DESC		
		";							
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){			
			foreach ($res as $val) {
				$logo_image = '';
				if(!empty($val['logo_image'])){
					$logo_image = CMedia::getImage($val['logo_image'],$val['path'],
					Yii::app()->params->size_image_thumbnail,
				    CommonUtility::getPlaceholderPhoto('item'));
				}
				if($val['is_online']==1){
					$val['attr_required']='';
				}
				$val['logo_image']=$logo_image;
				$data[]=$val;
			}
			return $data;
		}
		throw new Exception( 'no available saved payment' );
	}

	public static function driverDefaultPayment($driver_id='')
	{
		$model = AR_driver_payment_method::model()->find('driver_id=:driver_id AND as_default=:as_default', 
	        array(
	         ':driver_id'=>intval($driver_id),
	         ':as_default'=>1,
	        ));
	    if($model){
	    	return array(
	    	  'payment_uuid'=>$model->payment_uuid,
	    	  'payment_code'=>$model->payment_code,
	    	  'reference_id'=>$model->reference_id
	    	);
	    }
	    return false;
	}

	public static function defaultPaymentByMerchant($client_id='',$merchant_id=0)
	{
		$dependency = CCacheData::dependency(); 
		$model = AR_client_payment_method::model()->cache(Yii::app()->params->cache, $dependency)->find('client_id=:client_id AND merchant_id=:merchant_id AND as_default=:as_default', 
	        array(
	         ':client_id'=>intval($client_id),
			 ':merchant_id'=>intval($merchant_id),
	         ':as_default'=>1,
	        ));
	    if($model){
	    	return array(
	    	  'payment_uuid'=>$model->payment_uuid,
	    	  'payment_code'=>$model->payment_code,
	    	  'reference_id'=>$model->reference_id
	    	);
	    }
	    return false;
	}
	
	public static function getCustomerDefaultPayment($user_id=0, $merchant_id=0)
	{
		$dependency = CCacheData::dependency(); 
		$model = AR_client_payment_method::model()->cache(Yii::app()->params->cache, $dependency)->find(
			'client_id=:client_id AND as_default=:as_default AND merchant_id=:merchant_id ', 
		    array(
		      ':client_id'=>intval($user_id),
		      ':as_default'=>1,
		      ':merchant_id'=>intval($merchant_id)
		    )); 	
		if($model){		    	
			return $model;
		}
		return false;
	}		    

	public static function List()
	{	
		$data = [];
		$criteria=new CDbCriteria();
		$criteria->condition = "status=:status";		    
		$criteria->params  = array(
		   ":status"=>"active"
		);
		$criteria->order = "sequence asc";

		$dependency = CCacheData::dependency();
		$model = AR_payment_gateway::model()->cache(Yii::app()->params->cache, $dependency)->findAll($criteria);		
		if($model){			
			foreach ($model as $item) {
				$data[] = [
					'id'=>$item->payment_id,
					'name'=>$item->payment_name,
					'description'=>$item->payment_code,
					'url_image'=>CMedia::getImage($item->logo_image,$item->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('item')),
					'url_icon'=>CMedia::getImage($item->logo_image,$item->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('item')),
				];
			}
		}
		return $data;
	}

	public static function PayondeliveryList($return_all=true)
	{	
		$data = [];
		$criteria=new CDbCriteria();
		$criteria->condition = "status=:status";		    
		$criteria->params  = array(
		   ":status"=>"publish"
		);
		$criteria->order = "sequence asc";	

		$dependency = CCacheData::dependency();
		$model = AR_paydelivery::model()->cache(Yii::app()->params->cache, $dependency)->findAll($criteria);				
		if($model){			
			foreach ($model as $item) {	
				if(!$return_all){
					$data[$item->payment_name] = [
						'name'=>$item->payment_name,
						'url_image'=>CMedia::getImage($item->photo,$item->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('item')),					
					];
				} else {
					$data[] = [
						'id'=>$item->id,
						'name'=>$item->payment_name,
						'description'=>$item->payment_name,
						'url_image'=>CMedia::getImage($item->photo,$item->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('item')),
						'url_icon'=>CMedia::getImage($item->photo,$item->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('item')),
					];
				}				
			}
			return $data;
		}		
		throw new Exception( HELPER_NO_RESULTS );
	}

	public static function getPayondelivery($payment_id=0)
	{
		$dependency = CCacheData::dependency();
		$model = AR_paydelivery::model()->cache(Yii::app()->params->cache, $dependency)->find("id=:id",[
			':id'=>$payment_id
		]);
		if($model){
			return $model;
		}
		throw new Exception( HELPER_RECORD_NOT_FOUND );
	}

	public static function ordernewTransMeta($order_id=0, $meta_name='')
	{
		$dependency = CCacheData::dependency();
		$model = AR_ordernew_trans_meta::model()->cache(Yii::app()->params->cache, $dependency)->find("order_id=:order_id AND meta_name=:meta_name",[
			":order_id"=>$order_id,
			":meta_name"=>$meta_name
		]);
		if($model){			
			return $model;
		}
		throw new Exception( HELPER_RECORD_NOT_FOUND );
	}

	public static function PayondeliveryByMerchant($merchant_id=0)
	{		
		$stmt = "
		SELECT a.*
		FROM {{paydelivery}} a
		JOIN {{merchant_meta}} b ON a.id = b.meta_value
		WHERE b.merchant_id = ".q($merchant_id)."
		AND b.meta_name = 'payondelivery_data'
		AND a.status='publish'
		ORDER BY a.sequence ASC
		LIMIT 0, 100;
		";
		if ($model = CCacheData::queryAll($stmt)){
			$data = [];
			foreach ($model as $item) {
				$data[] = [
					'id'=>$item['id'],
					'name'=>$item['payment_name'],
					'description'=>$item['payment_name'],
					'url_image'=>CMedia::getImage($item['photo'],$item['path'],'@thumbnail',CommonUtility::getPlaceholderPhoto('item')),
					'url_icon'=>CMedia::getImage($item['photo'],$item['path'],'@thumbnail',CommonUtility::getPlaceholderPhoto('item')),
				];
			}
			return $data;
		}
		throw new Exception( HELPER_NO_RESULTS );
	}

	public static function validatePaydelivery($merchant_type=2,$merchant_id=0,$payment_uuid='')
	{
		$data = [];
		if($merchant_type==1){
			$stmt = "
			SELECT a.id
			FROM {{paydelivery}} a
			JOIN {{merchant_meta}} b ON a.id = b.meta_value
			WHERE b.merchant_id = ".q($merchant_id)."
			AND b.meta_name = 'payondelivery_data'
			AND a.status='publish'
			ORDER BY a.sequence ASC
			LIMIT 0, 100;
			";
		} else {
			$stmt = "
			SELECT id
			FROM {{paydelivery}}
			WHERE status='publish'
			LIMIT 0, 100;
			";
		}
		if ($model = CCacheData::queryAll($stmt)){			
			foreach ($model as $item) {
				$data[] = $item['id'];
			}			
		}

		$model = AR_client_payment_method::model()->find("payment_uuid=:payment_uuid",[
			':payment_uuid'=>$payment_uuid
		]);
		if($model){			
			if(in_array($model->reference_id,(array)$data)){
				return true;
			} 
		}
		return false;
	}

	public static function getCustomerPayment($client_id=0, $merchant_id=0,$orig_merchant_id=0)
	{				
		$stmt = "
		SELECT 
			a.payment_uuid,
			a.payment_code,
			a.as_default,
			a.reference_id,
			a.attr1,
			a.attr2,
			b.payment_name,
			b.logo_type,
			b.logo_class,
			b.logo_image,
			b.path,
			b.attr1 as attr_required,
			b.is_online
		FROM 
			{{client_payment_method}} a
		LEFT JOIN 
			{{payment_gateway}} b ON a.payment_code = b.payment_code
		WHERE 
			a.client_id = ".q($client_id)."	
			AND a.merchant_id = ".q($merchant_id)."	
			AND a.as_default = 1
			AND b.status = 'active'				
			AND a.payment_code IN (
				SELECT 
					meta_value 
				FROM 
					{{merchant_meta}}
				WHERE 
					meta_name = 'payment_gateway'
					AND meta_value = a.payment_code
					AND merchant_id = ".q($orig_merchant_id)."
			);
		";		
		if($res = Yii::app()->db->createCommand($stmt)->queryRow()){
			$logo_image = CMedia::getImage($res['logo_image'],$res['path'],Yii::app()->params->size_image_thumbnail,
				CommonUtility::getPlaceholderPhoto('payment'));
			return [
				'payment_uuid'=>$res['payment_uuid'],
				'payment_code'=>$res['payment_code'],
				'payment_name'=>$res['payment_name'],
				'attr1'=>$res['attr1'],
				'attr2'=>$res['attr2'],
				'logo_type'=>$res['logo_type'],
				'logo_url'=>$logo_image
			];
		}
		throw new Exception( HELPER_NO_RESULTS );
	}		    

	public static function getPaymentAllActive($status='active')
	{
		$model = AR_payment_gateway::model()->findAll("status=:status",array(		  
		  ':status'=>$status
		));
		if($model){
			$data = array();
			foreach ($model as $items) {
				$data[] = $items->payment_code;
			}
			return $data;
		}
		return false;
	}

	public static function getPaymentByOnline($is_online=1,$status='active',$single_return=true)
	{
		$model = AR_payment_gateway::model()->findAll("is_online=:is_online AND status=:status",array(
		  ':is_online'=>intval($is_online),
		  ':status'=>$status
		));
		if($model){
			$data = array();
			foreach ($model as $items) {
				if($single_return){
					$data[] = $items->payment_code;
				} else {
					$data[$items->payment_code] = array(
						'payment_code'=>$items->payment_code,
						'payment_name'=>$items->payment_name,
					);
				}			   	
			}
			return $data;
		}
		return false;
	}
	
	public static function getCustomerPaymentMeta($client_id=0, $meta1='')
	{
		$customer_meta = AR_client_meta::model()->find("client_id=:client_id AND meta1=:meta1",[
			':client_id'=>$client_id,
			':meta1'=>$meta1
		]);    
		if($customer_meta){
			return $customer_meta;
		}
		throw new Exception( HELPER_NO_RESULTS );
	}

	public static function addDefaultPayment($client_id=0,$payment_code='cod')
	{
		 $model = AR_payment_gateway::model()->find("payment_code=:payment_code",[
			':payment_code'=>$payment_code
		 ]);
		 if($model){
			$model_paymemt = new AR_client_payment_method();
			$model_paymemt->client_id = intval($client_id);
			$model_paymemt->payment_code = $model->payment_code;
			$model_paymemt->as_default = 1;
			$model_paymemt->attr1 = $model->payment_name;
			$model_paymemt->save();
		 }
	}

	public static function getDefaultPayment($client_id=0, $merchant_id=0, $merchant_type=2 )
	{		
		$payment_merchantid = $merchant_type==2?0:$merchant_id;		
		$stmt = "
		SELECT 		
		a.payment_uuid,
		a.payment_code,
		b.payment_name,		
		a.attr1,
		a.attr2,
		b.logo_image as logo,
		b.path
		
		FROM {{client_payment_method}} a
		LEFT JOIN {{payment_gateway}} b
		ON a.payment_code = b.payment_code

		WHERE a.client_id = ".q($client_id)."
		AND a.merchant_id = ".q($payment_merchantid)."
		AND a.as_default = 1
		AND b.status='active'
		AND a.payment_code IN 
		(
			SELECT 
				meta_value 
			FROM 
				{{merchant_meta}}
			WHERE 
				meta_name = 'payment_gateway'
				AND meta_value = a.payment_code
				AND merchant_id = ".q($merchant_id)."
		)
		LIMIT 0,1
		";				
		if ($model = CCacheData::queryRow($stmt)){							
			$block_payment_list = ACustomer::getBlockPaymentmethod($client_id);	
			$payment_code = $model['payment_code'];			
			if(is_array($block_payment_list) && count($block_payment_list)>=1){
				if(in_array($payment_code,(array)$block_payment_list)){
					return false;
				}
			}
			$model['logo'] = !empty($model['logo']) ? CMedia::getImage($model['logo'],$model['path']) :'';
			unset($model['path']);
			return $model;
		}		
		return false;
	}

	public static function getSavedpayondelivery($client_id=0,$merchant_id=0,$payment_code='paydelivery')
	{
		$data = [];
		$model = AR_client_payment_method::model()->findAll("client_id=:client_id AND merchant_id=:merchant_id AND payment_code=:payment_code",[
			':client_id'=>$client_id,
			':merchant_id'=>$merchant_id,
			':payment_code'=>$payment_code
		]);
		if($model){
			foreach ($model as $items) {
				$data[] = $items['reference_id'];
			}
		}
		return $data;
	}

	public static function customerAllPayment($client_id=0)
	{
		$data = [];
		$stmt = "
		SELECT 
		a.merchant_id,
		a.payment_uuid,
		a.payment_code,
		a.attr1,
		a.attr2,
		a.as_default,
		b.logo_image as logo,
		b.path,
		CASE 
        WHEN a.merchant_id != 0 THEN 
            (SELECT restaurant_name FROM {{merchant}} WHERE merchant_id = a.merchant_id)
        ELSE 'All'
        END AS restaurant_name    

		FROM {{client_payment_method}} a
		LEFT JOIN {{payment_gateway}} b
		ON a.payment_code = b.payment_code

		WHERE client_id=".q($client_id)."
		ORDER BY as_default DESC,payment_method_id DESC
		";						
		if($model = Yii::app()->db->createCommand($stmt)->queryAll()){		
			$payondelivery = null;
			try {
				$payondelivery = CPayments::PayondeliveryList(false);						
			} catch (Exception $e) {}				
			foreach ($model as $items) {							
				if($items['payment_code']=='paydelivery'){
					$logo_url = $payondelivery[$items['attr2']]['url_image'] ?? '';
				} else $logo_url = !empty($items['logo']) ? CMedia::getImage($items['logo'],$items['path']) :'';				
				$data[$items['restaurant_name']][] = [
					'payment_uuid'=>$items['payment_uuid'],
	    	        'payment_code'=>$items['payment_code'],	   
					'attr1'=>$items['attr1'],
					'attr2'=>$items['attr2'],
					'logo_url'=>$logo_url,
					'as_default'=>$items['as_default']
				];
			}			
			return $data;
		}
		throw new Exception( HELPER_NO_RESULTS );
	}

}
/*end class*/