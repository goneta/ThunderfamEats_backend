<?php
class CWallet
{
	public static function getCardID($account_type='', $account_id=0)
	{
		$model = AR_wallet_cards::model()->find("account_type=:account_type AND account_id=:account_id",array(
		  ':account_type'=>$account_type,
		  ':account_id'=>intval($account_id)
		));
		if($model){
			return $model->card_id;
		}
		throw new Exception( 'card not found' );
	}
	
	public static function getCardUUID($account_type='', $account_id=0)
	{
		$model = AR_wallet_cards::model()->find("account_type=:account_type AND account_id=:account_id",array(
		  ':account_type'=>$account_type,
		  ':account_id'=>intval($account_id)
		));
		if($model){
			return $model->card_uuid;
		}
		throw new Exception( 'card uuid not found' );
	}
	
	public static function getAccountID($card_id=0)
	{
		$model = AR_wallet_cards::model()->find("card_id=:card_id",array(
		  ':card_id'=>intval($card_id),		  
		));
		if($model){
			return $model->account_id;
		}
		throw new Exception( 'card uuid not found' );
	}
	
	public static function createCard($account_type='', $account_id=0)
	{		
		try {
			$card_id = self::getCardID($account_type,$account_id);
			return $card_id;
		} catch (Exception $e) {
			$model = new AR_wallet_cards;
			$model->account_type = trim($account_type);
			$model->account_id = intval($account_id);
			if($model->save()){
				return $model->card_id;
			}						
			throw new Exception( CommonUtility::parseModelErrorToString($model->getErrors()) );
		}		
	}
	
	public static function inserTransactions($card_id=0,$data=array())
	{
		$transaction_description = isset($data['transaction_description'])?$data['transaction_description']:'';
		$transaction_description_parameters = isset($data['transaction_description_parameters'])?$data['transaction_description_parameters']:'';
		$transaction_type = isset($data['transaction_type'])?$data['transaction_type']:'';
		$transaction_amount = isset($data['transaction_amount'])?$data['transaction_amount']:0;
		$status = isset($data['status'])?$data['status']:'';
		
		$meta_name = isset($data['meta_name'])?$data['meta_name']:'';
		$meta_value = isset($data['meta_value'])?$data['meta_value']:'';
		$meta_array = isset($data['meta_array'])?$data['meta_array']:'';


		$orig_transaction_amount = isset($data['orig_transaction_amount'])?$data['orig_transaction_amount']:$transaction_amount;		
		$merchant_base_currency = isset($data['merchant_base_currency'])?$data['merchant_base_currency']:'';
		$admin_base_currency = isset($data['admin_base_currency'])?$data['admin_base_currency']:'';
		$exchange_rate_merchant_to_admin = isset($data['exchange_rate_merchant_to_admin'])?$data['exchange_rate_merchant_to_admin']:1;
		$exchange_rate_admin_to_merchant = isset($data['exchange_rate_admin_to_merchant'])?$data['exchange_rate_admin_to_merchant']:1;
		$reference_id = isset($data['reference_id'])?$data['reference_id']:0;
		$reference_id1 = isset($data['reference_id1'])?$data['reference_id1']:'';
		$reference_id2 = isset($data['reference_id2'])?$data['reference_id2']:'';
		$expiry_date = isset($data['expiry_date'])?$data['expiry_date']:null;

		$last_balance = self::getBalance($card_id);					
		
		// if($transaction_type=="credit" || $transaction_type=="cashin" ||  $transaction_type=="points_earned" ){
		// 	$running_balance = floatval($last_balance) + floatval($transaction_amount);
		// } else $running_balance = floatval($last_balance) - floatval($transaction_amount);

		switch ($transaction_type) {
			case "points_redeemed":
			case "debit":
			case "points_expired":
			case "payout":
			case "cashout":
				  $running_balance = floatval($last_balance) - floatval($transaction_amount);
				break;
			
			default:
			      $running_balance = floatval($last_balance) + floatval($transaction_amount);			       
				break;
		}

		// if($transaction_type=="credit" || $transaction_type=="cashin"){
		// 	$running_balance = floatval($last_balance) + floatval($transaction_amount);
		// } else {			
		// 	if($last_balance<0){
		// 		$running_balance = floatval(($last_balance*-1)) - floatval($transaction_amount);
		// 		$running_balance = $running_balance*-1;
		// 	} else {
		// 		$running_balance = floatval($last_balance) - floatval($transaction_amount);
		// 	}						
		// }
		
		$earnings = new AR_wallet_transactions;		
		$earnings->scenario = $transaction_type;	
		$earnings->card_id = $card_id; 	
		$earnings->transaction_date = CommonUtility::dateNow();
		$earnings->transaction_description = $transaction_description;
		$earnings->transaction_description_parameters = json_encode($transaction_description_parameters);
		$earnings->transaction_type = $transaction_type;
		$earnings->transaction_amount = floatval($transaction_amount);
		$earnings->running_balance = floatval($running_balance);
		$earnings->status = $status;

		$earnings->orig_transaction_amount = floatval($orig_transaction_amount);
		$earnings->merchant_base_currency = $merchant_base_currency;
		$earnings->admin_base_currency = $admin_base_currency;
		$earnings->exchange_rate_merchant_to_admin = floatval($exchange_rate_merchant_to_admin);
		$earnings->exchange_rate_admin_to_merchant = floatval($exchange_rate_admin_to_merchant);
		//$earnings->reference_id = floatval($reference_id);
		$earnings->reference_id = CommonUtility::safeTrim($reference_id);
		$earnings->reference_id1 = CommonUtility::safeTrim($reference_id1);
		$earnings->reference_id2 = CommonUtility::safeTrim($reference_id2);
		$earnings->expiry_date = $expiry_date;

		$earnings->ip_address = CommonUtility::userIp();
		$earnings->meta_name = $meta_name;
		$earnings->meta_value = $meta_value;	
		$earnings->meta_array = $meta_array;		
		if($earnings->save()){			
			return $earnings->transaction_id;
		} else throw new Exception( CommonUtility::parseModelErrorToString( $earnings->getErrors()) );				
	}
	
	public static function getBalance($card_id=0)
	{
		$running_balance = 0;
		$criteria = new CDbCriteria;		
		$criteria->addCondition("card_id=:card_id");
		$criteria->params=array(':card_id'=>intval($card_id));
		$criteria->select = "transaction_id,running_balance";		
		$criteria->order = "transaction_id DESC";			
		$model=AR_wallet_transactions::model()->find($criteria);		
		if($model){			
			$running_balance = $model->running_balance;
		}
		return floatval($running_balance);
	}

	public static function reversalFee($card_id=0,$transaction_id=0)
	{

		$stmt = "
		SELECT a.transaction_id,a.transaction_amount 
		FROM {{wallet_transactions}} a
		WHERE transaction_id IN (
			select transaction_id from {{wallet_transactions_meta}}
			where meta_name='cashout_fee'
			and meta_value=".q($transaction_id)."
		)
		";				
		if($res = Yii::app()->db->createCommand($stmt)->queryRow()){			
			$new_transaction_id = isset($res['transaction_id'])?$res['transaction_id']:0;			
			$transaction_amount = isset($res['transaction_amount'])?$res['transaction_amount']:0;
			
			$model  = AR_wallet_transactions_meta::model()->find("meta_name=:meta_name AND meta_value=:meta_value",[
				":meta_name"=>'cashout_reversal',
				':meta_value'=>$new_transaction_id
			]);
			if(!$model){
				$params = array(				  
					'transaction_description'=>"Cashout fee reversal",
					'transaction_description_parameters'=>'',
					'transaction_type'=>"credit",
					'transaction_amount'=>floatval($transaction_amount),
					'status'=>"paid",
					'meta_name'=>"cashout_reversal",
					'meta_value'=>$new_transaction_id
				);									
				return CWallet::inserTransactions($card_id,$params);
		    } else throw new Exception( t("Reversal already exist") );
		}
		throw new Exception(t(HELPER_NO_RESULTS));
	}

	public static function getCustomer($card_id=0)
	{
		$stmt = "		
		SELECT a.first_name,a.last_name,a.email_address,
		concat(a.first_name,' ',a.last_name) as customer_name
		FROM {{client}} a
		LEFT JOIN {{wallet_cards}} b
		ON
		a.client_id = b.account_id
		WHERE
		b.card_id = ".q( intval($card_id) )."
		";
		if ( $res = CCacheData::queryRow($stmt)){
			return $res;
		}
		return false;
	}

	public static function getCard($card_id=0)
	{
		$model = AR_wallet_cards::model()->find("card_id=:card_id",array(
		  ':card_id'=>intval($card_id),		  
		));
		if($model){
			return $model;
		}
		throw new Exception( 'card uuid not found' );
	}

}
/*end class*/