<?php
class CPayouts{
	
	
	public static function paymentForms()
	{
		$models = AR_payment_gateway::model()->findAll("is_payout=:is_payout AND status=:status",array(
		  ':is_payout'=>1,
		  ':status'=>"active"
		));
		if($models){
			$data = array();
			foreach ($models as $items) {								
				$data[$items->payment_code] = json_decode($items->attr_json1,true);
			}			
			return $data;
		}		
		return false;
	}
	
	public static function getPayoutAccont($merchant_id='')
	{
		 $meta_name = 'payout_provider';
		 $model = AR_merchant_meta::model()->find("merchant_id=:merchant_id AND meta_name=:meta_name",array(
		   ':merchant_id'=>intval($merchant_id),
		   ':meta_name'=>$meta_name
		 ));
		 if($model){
		 	 $data = array();
		 	 $provider = $model->meta_value;
		 	 $meta_value = $model->meta_value1;
		 	 if($provider=="paypal"){
		 	 	$data  = array(
		 	 	   'provider'=>$provider,
		 	 	   'account'=>CommonUtility::maskEmail($meta_value),
		 	 	   'email_address'=>$meta_value
		 	 	 );
		 	 } elseif ( $provider=="stripe" ){
		 	 	 if($meta_value = json_decode($meta_value,true)){		 	 	 	
		 	 	 	$data = array(
		 	 	 	  'provider'=>$provider,
		 	 	 	  'account'=>CommonUtility::mask($meta_value['account_number']),
		 	 	 	  'account_number'=>$meta_value['account_number'],
		 	 	 	  'account_holder_name'=>$meta_value['account_holder_name'],
		 	 	 	  'account_holder_type'=>$meta_value['account_holder_type'],
		 	 	 	  'currency'=>$meta_value['currency'],
		 	 	 	  'routing_number'=>$meta_value['routing_number'],
		 	 	 	  'country'=>$meta_value['country'],
		 	 	 	);
		 	 	 }
		 	 } elseif ( $provider=="bank" ){
		 	 	if($meta_value = json_decode($meta_value,true)){		 	 		
		 	 		$data = array(
		 	 		  'provider'=>$provider,
		 	 	 	  'account'=>CommonUtility::mask($meta_value['account_number_iban']),
		 	 	 	  'account_number_iban'=>$meta_value['account_number_iban'],
		 	 	 	  //'full_name'=>$meta_value['full_name'],
		 	 	 	  //'billing_address1'=>$meta_value['billing_address1'],
		 	 	 	  //'billing_address2'=>$meta_value['billing_address2'],
		 	 	 	  //'city'=>$meta_value['city'],
		 	 	 	  //'state'=>$meta_value['state'],
		 	 	 	  //'post_code'=>$meta_value['post_code'],
		 	 	 	  //'country'=>$meta_value['country'],
		 	 	 	  'account_name'=>$meta_value['account_name'],
		 	 	 	  'swift_code'=>$meta_value['swift_code'],
		 	 	 	  'bank_name'=>$meta_value['bank_name'],
		 	 	 	  'bank_branch'=>$meta_value['bank_branch'],
		 	 	 	);
		 	 	}
		 	 }
		 	 
		 	 if(is_array($data) && count($data)>=1){
		 	 	return $data;
		 	 }
		 }
		 throw new Exception( 'No payment accounts' );
	}
	
	public static function getPayoutDetails($transaction_uuid='',$with_exchange_rate=true)
	{
		/*$criteria=new CDbCriteria();
		$criteria->alias = "a";
		$criteria->select="b.transaction_id, b.merchant_id, b.transaction_date, b.transaction_amount,
		b.transaction_description,b.transaction_description_parameters,
		b.status, a.meta_name, a.meta_value";
		$criteria->join='LEFT JOIN {{merchant_earnings}} b on a.transaction_id=b.transaction_id ';				
		$criteria->condition="b.transaction_uuid=:transaction_uuid";
		$criteria->params = array(		 
		 ':transaction_uuid'=>$transaction_uuid
		);
		$model = AR_merchant_earnings_meta::model()->findAll($criteria);*/
		
		$criteria=new CDbCriteria();
		$criteria->alias = "a";
		$criteria->select="b.transaction_id, b.card_id, b.transaction_date, b.transaction_amount,
		b.transaction_description,b.transaction_description_parameters, b.exchange_rate_merchant_to_admin, b.merchant_base_currency,b.admin_base_currency,
		b.status, a.meta_name, a.meta_value";
		
		$criteria->join='LEFT JOIN {{wallet_transactions}} b on a.transaction_id=b.transaction_id ';				
		
		$criteria->condition="b.transaction_uuid=:transaction_uuid";
		$criteria->params = array(		 
		 ':transaction_uuid'=>$transaction_uuid
		);		
		$model = AR_wallet_transactions_meta::model()->findAll($criteria);
		
		if($model){
			$item = array();			
			$price_list_format = CMulticurrency::getAllCurrency();
			foreach ($model as $items) {							
				$item['card_id']=$items->card_id;
				$item['status']=$items->status;
				$item['transaction_date'] = Date_Formatter::dateTime($items->transaction_date);		
				
				if($with_exchange_rate){
					$item['transaction_amount']=Price_Formatter::formatNumber( ($items->transaction_amount*$items->exchange_rate_merchant_to_admin) );
				} else $item['transaction_amount']=Price_Formatter::formatNumber($items->transaction_amount);

				$price_format = isset($price_list_format[$items->merchant_base_currency])?$price_list_format[$items->merchant_base_currency]:Price_Formatter::$number_format;									
				$item['transaction_amount_original']=Price_Formatter::formatNumber2( ($items->transaction_amount),$price_format );
				
				$params = !empty($items->transaction_description_parameters)?json_decode($items->transaction_description_parameters,true):'';
				$item['transaction_description'] = t($items->transaction_description,(array)$params);
															
				$item['transaction_id'] = $items->transaction_id;
				$item['merchant_base_currency'] = $items->merchant_base_currency;
				$item['admin_base_currency'] = $with_exchange_rate?$items->admin_base_currency:$items->merchant_base_currency;
				$item['exchange_rate_merchant_to_admin'] = $with_exchange_rate==true?$items->exchange_rate_merchant_to_admin:1;
								
				switch ($items->meta_name) {
					case "provider":		
					    $item['provider'] = $items->meta_value;
						break;
					case "account":									
					    $item['account'] = $items->meta_value;
					    break;
					default:    
					    $item[$items->meta_name] = $items->meta_value;
					    break;
				}				
			}				
			return $item;
		}
		throw new Exception( 'Payout details not found' );
	}
	
	public static function countSummary($transaction_type='',$status='')
	{
		$criteria=new CDbCriteria();
		$criteria->condition="transaction_type=:transaction_type AND status=:status";
		$criteria->params = array(		 
		 ':transaction_type'=>$transaction_type,
		 ':status'=>$status
		);		
		$count = AR_wallet_transactions::model()->count($criteria); 		
		return intval($count);
	}
	
	public static function sumSummary($transaction_type='',$status='',$reference_id=0, $use_exchange = false)
	{		
		$transaction_amount = 0;
		$criteria=new CDbCriteria();		
		if($use_exchange){
			$criteria->select = "sum((transaction_amount*exchange_rate_merchant_to_admin)) as transaction_amount";
		} else $criteria->select = "sum(transaction_amount) as transaction_amount";
		$criteria->condition="transaction_type=:transaction_type AND status=:status AND reference_id=:reference_id";
		$criteria->params = array(		 
		 ':transaction_type'=>$transaction_type,
		 ':status'=>$status,
		 ':reference_id'=>$reference_id
		);						
		$model = AR_wallet_transactions::model()->find($criteria);
		if($model){
			$transaction_amount = $model->transaction_amount;
		}
		return floatval($transaction_amount);
	}
	
	public static function payoutSummary($transaction_type='payout',$reference_id=0 , $use_exchange=false)
	{		
		$unpaid = self::countSummary($transaction_type,'unpaid');
		$paid = self::countSummary($transaction_type,'paid');
		$cancelled = self::countSummary($transaction_type,'cancelled');
		
		$total_unpaid = self::sumSummary($transaction_type,'unpaid',$reference_id,$use_exchange);
		$total_paid = self::sumSummary($transaction_type,'paid',$reference_id,$use_exchange);
		
		$data = array(
		   'unpaid'=>intval($unpaid),
		   'paid'=>intval($paid),
		   'cancelled'=>$cancelled,
		   'total_unpaid'=>$total_unpaid,
		   'total_paid'=>$total_paid,
		);
		return $data;
	}
	
}
/*end class*/
