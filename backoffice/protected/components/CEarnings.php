<?php
class CEarnings
{	
	public static function creditCommission($order_uuid='')
	{
		$order = COrders::get($order_uuid);	
		if($order){
			$exchange_rate = $order->exchange_rate_merchant_to_admin>0?$order->exchange_rate_merchant_to_admin:1;
			$params = array(					  		 
			  'transaction_description'=>"Commission on order #{{order_id}}",
			  'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id),					  
			  'transaction_type'=>"credit",
			  'transaction_amount'=>($order->commission*$exchange_rate),
			  'status'=>'paid',
			  'meta_name'=>"order",
			  'meta_value'=>$order->order_id,
			  'orig_transaction_amount'=>$order->commission,
			  'merchant_base_currency'=>$order->base_currency_code,
			  'admin_base_currency'=>$order->admin_base_currency,			  
			  'exchange_rate_merchant_to_admin'=>$order->exchange_rate_merchant_to_admin,
			  'exchange_rate_admin_to_merchant'=>$order->exchange_rate_admin_to_merchant,
			);						
			
			$card_id = CWallet::getCardID( Yii::app()->params->account_type['admin'], 0);
									
			$criteria = new CDbCriteria;
			$criteria->alias = 'a';		
			$criteria->join='LEFT JOIN {{wallet_transactions}} b ON a.transaction_id = b.transaction_id';
			$criteria->addCondition('meta_name=:meta_name AND meta_value=:meta_value 
			AND b.transaction_type=:transaction_type AND card_id=:card_id ');
			$criteria->params = array( 
			  ':meta_name' => 'order', 
			  ':meta_value'=> $order->order_id,
			  ':transaction_type'=> 'credit',
			  ':card_id'=>$card_id
			);
			$models=AR_wallet_transactions_meta::model()->find($criteria);			
			if($models){
				throw new Exception( 'Transaction already exist' );
			}
			
			$resp = self::saveTransactions($order,$params,$card_id);
			return $resp;
		}
		throw new Exception( 'Order not found' );
	}
	
	public static function saveTransactions($order=null ,$params=array() , $card_id = 0)
	{		
		$all_online = CPayments::getPaymentTypeOnline();
		$digita_wallet[CDigitalWallet::transactionName()] = [
			'payment_code'=>CDigitalWallet::transactionName(),
			'payment_name'=>CDigitalWallet::paymentName()
		];
		$all_online = $all_online+$digita_wallet;
				
		$merchant = CMerchants::get($order->merchant_id);
		if($merchant && $order){
			if($order->payment_status=='paid' && $merchant->merchant_type==2 ){				
				if(array_key_exists($order->payment_code,(array)$all_online)){					
					$resp = CWallet::inserTransactions($card_id,$params);
					return $resp;
				} else throw new Exception( 'Payment is not online' );
			} else throw new Exception( 'Either merchant type or payment status not valid' );
		} 
		throw new Exception( 'Merchant not found' );
	}
	
	public static function autoApproval()
	{
		/*$models = AR_admin_meta::model()->find("meta_name=:meta_name",array(
		  'meta_name'=>'earning_auto_approval',		  
		));
		if($models){
			if($models->meta_value==1){
				return true;
			}
		}
		return false;*/
		return true;
	}
	
	public static function getDeliveredStatus()
	{
		$status = array();
		$model_status = AR_admin_meta::getMeta(array('status_delivered','status_completed'));
		if($model_status){
			foreach ($model_status as $item) {								
				$status[] = CommonUtility::cleanString($item['meta_value']);
			}
		}
		return $status;
	}
	
	public static function creditMerchant($order_uuid='')
	{
		$order = COrders::get($order_uuid);	
		if($order){
						
			$delivered_status = self::getDeliveredStatus();			
			if(!in_array( CommonUtility::cleanString($order->status) ,$delivered_status)){				
				throw new Exception( 'order status is not valid' );
			}			
						
			$transaction_amount = $order->merchant_earning;
						
			$meta = AR_ordernew_meta::model()->find("order_id=:order_id AND meta_name=:meta_name AND meta_value=:meta_value",array(
			  ':order_id'=>$order->order_id,
			  ':meta_name'=>'order_revision',
			  ':meta_value'=>'less_account'
			));			
			if($meta){
				$transaction_amount = $order->merchant_earning_original;
			}
			
			$params = array(					  		 
			  'transaction_description'=>"Sales on order #{{order_id}}",
			  'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id),					  
			  'transaction_type'=>"credit",
			  'transaction_amount'=>$transaction_amount,
			  'status'=>'paid',
			  'meta_name'=>"order",
			  'meta_value'=>$order->order_id,
			  'reference_id'=>$order->order_id,
			  'orig_transaction_amount'=>$transaction_amount,
			  'merchant_base_currency'=>$order->base_currency_code,
			  'admin_base_currency'=>$order->admin_base_currency,
			  'exchange_rate_merchant_to_admin'=>$order->exchange_rate_merchant_to_admin,
			  'exchange_rate_admin_to_merchant'=>$order->exchange_rate_admin_to_merchant
			);					
						
			$card_id = CWallet::getCardID( Yii::app()->params->account_type['merchant'], $order->merchant_id );
									
			// $criteria = new CDbCriteria;
			// $criteria->alias = 'a';		
			// $criteria->join='LEFT JOIN {{wallet_transactions}} b ON a.transaction_id = b.transaction_id';
			// $criteria->addCondition('meta_name=:meta_name AND meta_value=:meta_value 
			// AND b.transaction_type=:transaction_type AND card_id=:card_id ');
			// $criteria->params = array( 
			//   ':meta_name' => 'order', 
			//   ':meta_value'=> $order->order_id,
			//   ':transaction_type'=> 'credit',
			//   ':card_id'=>$card_id
			// );
			// $models=AR_wallet_transactions_meta::model()->find($criteria);			
			// if($models){
			// 	throw new Exception( 'Transaction already exist' );
			// }			
			// $resp = self::saveTransactions($order,$params,$card_id);
			// return $resp;

			try {												
				self::findTransaction($card_id,'credit',$order->order_id,'');			
				$resp = self::saveTransactions($order,$params,$card_id);
				return $resp;					
			} catch (Exception $e) {									
				throw new Exception($e->getMessage());
			}			
		}
		throw new Exception( 'Order not found' );
	}
	
	public static function fullRefund($order_id='',$description='Refund for Order #{{order_id}}')
	{
	    $order = COrders::getByID($order_id);	    
	    if($order){	    	
			
			$all_offline = CPayments::getPaymentTypeOnline(0);
			$cash_collected_by_merchant = false;
			$order_type = $order->service_code;
			if($order_type!="delivery"){
				if(array_key_exists($order->payment_code,(array)$all_offline)){
					$cash_collected_by_merchant = true;					
				}
			}

			$refund_amount = $order->total;
			if($order->base_currency_code!=$order->admin_base_currency){
				$refund_amount = ($refund_amount * $order->exchange_rate_merchant_to_admin);
			}

	    	$params = array(					  		 
			   'transaction_description'=>$description,
			   'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id),					  
			   'transaction_type'=>"debit",			   
			   'transaction_amount'=>$refund_amount,
			   'orig_transaction_amount'=>$order->total,
			   'merchant_base_currency'=>$order->base_currency_code,
			   'admin_base_currency'=>$order->admin_base_currency,
			   'exchange_rate_merchant_to_admin'=>$order->exchange_rate_merchant_to_admin,
			   'exchange_rate_admin_to_merchant'=>$order->exchange_rate_admin_to_merchant,
			   'status'=>'paid',
			   'meta_name'=>"order",			   
			   'meta_value'=>$order->order_id
			);
			try {
			   if(!$cash_collected_by_merchant){
				 $card_id = CWallet::getCardID( Yii::app()->params->account_type['admin'], 0);			   
				 CWallet::inserTransactions($card_id,$params);			   
			   }
			} catch (Exception $e) {		       
		       throw new Exception($e->getMessage());
	        }

			// MERCHANT
			try {
			    $merchant_card_id = CWallet::getCardID( Yii::app()->params->account_type['merchant'], $order->merchant_id );				
				if(!$cash_collected_by_merchant){
					$refund_amount = self::getRefundBalance($merchant_card_id,$order->order_id);				
				} else $refund_amount = $order->total;
				
				if($order->base_currency_code!=$order->admin_base_currency){
					$refund_amount = ($refund_amount * $order->exchange_rate_merchant_to_admin);
				}
				$params = array(					  		 
					'transaction_description'=>$description,
					'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id),					  
					'transaction_type'=>"debit",			   
					'transaction_amount'=>$refund_amount,
					'orig_transaction_amount'=>$order->total,
					'merchant_base_currency'=>$order->base_currency_code,
					'admin_base_currency'=>$order->admin_base_currency,
					'exchange_rate_merchant_to_admin'=>$order->exchange_rate_merchant_to_admin,
					'exchange_rate_admin_to_merchant'=>$order->exchange_rate_admin_to_merchant,
					'status'=>'paid',
					'meta_name'=>"order",			   
					'meta_value'=>$order->order_id
				 );
				 CWallet::inserTransactions($merchant_card_id,$params);
		    } catch (Exception $e) {					
				throw new Exception($e->getMessage());
			}
			return true;
	    }
	    throw new Exception( 'Order not found' );
	}
	
	public static function partialRefund($order_id='',$refund_amount=0)
	{
	    $order = COrders::getByID($order_id);
	    if($order){	    	
			
			$orig_transaction_amount = $refund_amount;
			if($order->base_currency_code!=$order->admin_base_currency){
				$refund_amount = ($refund_amount * $order->exchange_rate_merchant_to_admin);
			}
	    	$params = array(					  		 
			   'transaction_description'=>"Partial Refund for Order #{{order_id}}",
			   'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id),					  
			   'transaction_type'=>"debit",
			   'transaction_amount'=>($refund_amount),
			   'orig_transaction_amount'=>$orig_transaction_amount,
			   'merchant_base_currency'=>$order->base_currency_code,
			   'admin_base_currency'=>$order->admin_base_currency,
			   'exchange_rate_merchant_to_admin'=>$order->exchange_rate_merchant_to_admin,
			   'exchange_rate_admin_to_merchant'=>$order->exchange_rate_admin_to_merchant,
			   'status'=>'paid',
			   'meta_name'=>"order",			   
			   'meta_value'=>$order->order_id
			);
	    	try {
			   $card_id = CWallet::getCardID( Yii::app()->params->account_type['admin'], 0);
			    CWallet::inserTransactions($card_id,$params);	    				   
			} catch (Exception $e) {		       
		       throw new Exception($e->getMessage());
	        }

			try {
			   $merchant_card_id = CWallet::getCardID( Yii::app()->params->account_type['merchant'], $order->merchant_id );
			   CWallet::inserTransactions($merchant_card_id,$params);
		    } catch (Exception $e) {		       
			  throw new Exception($e->getMessage());
		    }
			return true;
	    }
	    throw new Exception( 'Order not found' );
	}
	
	public static function requestPayout($card_id=0, $amount=0 , $account='' , $to_account=array() , $status='unpaid')
	{
		$balance = CWallet::getBalance($card_id);
				
		if($amount<=0){
			throw new Exception( 'Amount must be greater than 0' );
		}
		
		$payout_settings = AdminTools::getPayoutSettings();		
		$minimum_amount = isset($payout_settings['minimum_amount'])?floatval($payout_settings['minimum_amount']):0;
		$enabled = isset($payout_settings['enabled'])?floatval($payout_settings['enabled']):false;

		if(!$enabled){
			throw new Exception( t("Payout is disabled by admin"));
		}
		
		if($minimum_amount>0){
			if($minimum_amount>$amount){
				throw new Exception( t("Payout minimum amount is {{minimum_amount}}",
				array('{{minimum_amount}}'=>Price_Formatter::formatNumber($minimum_amount))) );
			}
		}
		
		if($amount<=$balance){
			$exchange_rate_merchant_to_admin = 1; $exchange_rate_admin_to_merchant=1;
			$admin_base_currency = AttributesTools::defaultCurrency();			
			$base_currency_code  = Price_Formatter::$number_format['currency_code'];

			if($admin_base_currency!=$base_currency_code){
				$exchange_rate_merchant_to_admin = CMulticurrency::getExchangeRate($base_currency_code,$admin_base_currency);
				$exchange_rate_admin_to_merchant = CMulticurrency::getExchangeRate($admin_base_currency,$base_currency_code);
			}
			
		   	$params = array(			  
			  'transaction_description'=>"Payout to {{account}}",
			  'transaction_description_parameters'=>array('{{account}}'=>$account),			  
			  'transaction_type'=>"payout",
			  'transaction_amount'=>floatval($amount),		
			  'status'=>$status,
			  'orig_transaction_amount'=>floatval($amount),
			  'merchant_base_currency'=>Price_Formatter::$number_format['currency_code'],
			  'admin_base_currency'=>$admin_base_currency,
			  'exchange_rate_merchant_to_admin'=>$exchange_rate_merchant_to_admin,
			  'exchange_rate_admin_to_merchant'=>$exchange_rate_admin_to_merchant
			);					
			$resp = CWallet::inserTransactions($card_id,$params);
			return $resp;		
		} else throw new Exception( t("You don't have enought balance.",array('{balance}'=>$balance)) );		
	}
	
	public static function getTotalMerchantBalance()
	{		
		$running_balance = 0;
		$criteria = new CDbCriteria;
		$criteria->select = "sum(meta_value) as running_balance";
		$criteria->addCondition('meta_name=:meta_name');	
		$criteria->params = array(':meta_name' => 'running_balance');			
		$model=AR_merchant_meta::model()->find($criteria);			
		if($model){			
			$running_balance = $model->running_balance;
		}
		return floatval($running_balance);
	}

	public static function splitPayout($order)
	{	    
	    if($order){	    	
	    	$params = array(					  		 
			   'transaction_description'=>"Payment to order #{{order_id}}",
			   'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id),					  
			   'transaction_type'=>"debit",
			   'transaction_amount'=>$order->merchant_earning,
			   'status'=>'paid',
			   'meta_name'=>"order",			   
			   'meta_value'=>$order->order_id
			);
	    	try {
			   $card_id = CWallet::getCardID( Yii::app()->params->account_type['merchant'],$order->merchant_id);
			   $resp = CWallet::inserTransactions($card_id,$params);	    	
			   return $resp;
			} catch (Exception $e) {		       
		       throw new Exception($e->getMessage());
	        }
	    }
	    throw new Exception( 'Order not found' );
	}

	public static function debitDiscount($order,$card_id=0,$owner='merchant',$meta_name='promo_id')
	{

		if(!$order){	
			throw new Exception( 'Order not found' );
		}

		$params = [];
		$promo_total = $order->promo_total;
		//$offer_discount = $order->offer_discount;		
		$offer_discount = $order->offer_total;		

		$find_promo = false; $promo_id = '';
		if($promo_total>0){
			$find_promo = AOrders::findUseDiscount($order->order_id,$owner,$meta_name);
			$promo_id = isset($find_promo['voucher_id'])?$find_promo['voucher_id']:'';
		}		
		
		if($order->base_currency_code!=$order->admin_base_currency && $owner=="admin"){
			$promo_total = ($promo_total*$order->exchange_rate_use_currency_to_admin);
		}

		if($promo_total>0 && $find_promo){
			$params[] = array(					  		 
				'transaction_description'=>"Coupon Expense on order #{{order_id}}",
				'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id),					  
				'transaction_type'=>"debit",
				'transaction_amount'=>$promo_total,
				'status'=>'paid',
				'meta_name'=>"order",
				'meta_value'=>$order->order_id,
				'reference_id'=>$order->order_id,
				'reference_id1'=>$promo_id,
				'orig_transaction_amount'=>$promo_total,
				'merchant_base_currency'=>$order->base_currency_code,
				'admin_base_currency'=>$order->admin_base_currency,
				'exchange_rate_merchant_to_admin'=>$order->exchange_rate_merchant_to_admin,
				'exchange_rate_admin_to_merchant'=>$order->exchange_rate_admin_to_merchant
			);			
		}

		if($offer_discount>0 && $owner=="merchant"){
			$params[] = array(					  		 
				'transaction_description'=>"Offers Expense on order #{{order_id}}",
				'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id),					  
				'transaction_type'=>"debit",
				'transaction_amount'=>$offer_discount,
				'status'=>'paid',
				'meta_name'=>"order",
				'meta_value'=>$order->order_id,
				'reference_id'=>$order->order_id,
				'reference_id1'=>$promo_id,
				'orig_transaction_amount'=>$offer_discount,
				'merchant_base_currency'=>$order->base_currency_code,
				'admin_base_currency'=>$order->admin_base_currency,
				'exchange_rate_merchant_to_admin'=>$order->exchange_rate_merchant_to_admin,
				'exchange_rate_admin_to_merchant'=>$order->exchange_rate_admin_to_merchant
			);
		}
						
		if(is_array($params) && count($params)>=1){						
			foreach ($params as $items) {				
				try {								
					//self::findTransaction('order',$order->order_id,'debit',$card_id);
					self::findTransaction($card_id,'debit',$order->order_id,$promo_id);
					CWallet::inserTransactions($card_id,$items);					
				} catch (Exception $e) {					
					//dump($e->getMessage());
				}
			}
			return true;
		}		
		throw new Exception( 'No available discount' );
	}	

	public static function findTransaction($card_id='',$transaction_type='',$reference_id=0,$reference_id1='')
	{
		$model = AR_wallet_transactions::model()->find("card_id=:card_id AND transaction_type=:transaction_type AND 
		reference_id=:reference_id AND reference_id1=:reference_id1",[
			':card_id'=>$card_id,
			':transaction_type'=>$transaction_type,
			':reference_id'=>$reference_id,
			':reference_id1'=>$reference_id1,
		]);
		if($model){
			throw new Exception( 'Transaction already exist' );
		}
		return true;
	}

	public static function findTransactions($meta_name='',$order_id=0,$transaction_type='',$card_id=0)
	{
		$criteria = new CDbCriteria;
		$criteria->alias = 'a';		
		$criteria->join='LEFT JOIN {{wallet_transactions}} b ON a.transaction_id = b.transaction_id';
		$criteria->addCondition('meta_name=:meta_name AND meta_value=:meta_value 
		AND b.transaction_type=:transaction_type AND card_id=:card_id ');
		$criteria->params = array( 
			':meta_name' => $meta_name, 
			':meta_value'=> $order_id,
			':transaction_type'=> $transaction_type,
			':card_id'=>$card_id
		);		
		$models=AR_wallet_transactions_meta::model()->find($criteria);			
		if($models){
			throw new Exception( 'Transaction already exist' );
		}			
		return true;
	}

	public static function debitPoints($order,$card_id=0,$points_amount=0)
	{
		if(!$order){	
			throw new Exception( 'Order not found' );
		}
		$reference_id1 = 'points_expense';
		$params = array(					  		 
			'transaction_description'=>"Points Expense on order #{{order_id}}",
			'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id),					  
			'transaction_type'=>"debit",
			'transaction_amount'=>$points_amount,
			'status'=>'paid',
			'meta_name'=>"order",
			'meta_value'=>$order->order_id,
			'reference_id'=>$order->order_id,			
			'reference_id1'=>$reference_id1,	
			'orig_transaction_amount'=>$points_amount,
			'merchant_base_currency'=>$order->base_currency_code,
			'admin_base_currency'=>$order->admin_base_currency,
			'exchange_rate_merchant_to_admin'=>$order->exchange_rate_merchant_to_admin,
			'exchange_rate_admin_to_merchant'=>$order->exchange_rate_admin_to_merchant
		);				
		try {											
			self::findTransaction($card_id,'debit',$order->order_id,$reference_id1);
			CWallet::inserTransactions($card_id,$params);					
		} catch (Exception $e) {								
			throw new Exception($e->getMessage());
		}
	}

	public static function CreditOrderCommission($order=null)
	{		
		$params = [];
		$merchant_id = $order->merchant_id;
		$merchant_type = CMerchants::getMerchantType($merchant_id);
		if($merchant_type==1){
			return;
		}
		$exchange_rate = $order->exchange_rate_merchant_to_admin>0?$order->exchange_rate_merchant_to_admin:1;

		$admin_card_id = CWallet::getCardID( Yii::app()->params->account_type['admin'], 0);
		$merchant_card_id = CWallet::getCardID( Yii::app()->params->account_type['merchant'], $merchant_id );

		$self_delivery = OptionsTools::find(['self_delivery'],$merchant_id);			
		$self_delivery = isset($self_delivery['self_delivery']) ? ($self_delivery['self_delivery'] == 1 ? true : false) : false;	

		$settings = OptionsTools::find(['points_cover_cost']);			
		$points_cover_cost = isset($settings['points_cover_cost'])?$settings['points_cover_cost']:'website';		

		$order_type = $order->service_code;

		$params = [];
		$params_merchant = [];

		$all_offline = CPayments::getPaymentTypeOnline(0);

		$params[] = array(					  		 
			'transaction_description'=>"Commission on order #{{order_id}}",
			'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id),					  
			'transaction_type'=>"credit",
			'transaction_amount'=>($order->commission*$exchange_rate),
			'status'=>'paid',
			'meta_name'=>"order",
			'meta_value'=>$order->order_id,
			'orig_transaction_amount'=>$order->commission,
			'merchant_base_currency'=>$order->base_currency_code,
			'admin_base_currency'=>$order->admin_base_currency,			  
			'exchange_rate_merchant_to_admin'=>$order->exchange_rate_merchant_to_admin,
			'exchange_rate_admin_to_merchant'=>$order->exchange_rate_admin_to_merchant,
			'reference_id'=>$order->order_id,
			'reference_id1'=>'commission',
		);

		$params_merchant[] = [
			'transaction_description'=>"Order #{{order_id}} (Food Sale)",
			'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id),					  
			'transaction_type'=>"credit",
			'transaction_amount'=>($order->sub_total*$exchange_rate),
			'status'=>'paid',			
			'orig_transaction_amount'=>$order->sub_total,
			'merchant_base_currency'=>$order->base_currency_code,
			'admin_base_currency'=>$order->admin_base_currency,			  
			'exchange_rate_merchant_to_admin'=>$order->exchange_rate_merchant_to_admin,
			'exchange_rate_admin_to_merchant'=>$order->exchange_rate_admin_to_merchant,
			'reference_id'=>$order->order_id,
			'reference_id1'=>'food_sale',
		];

		$params_merchant[] = [
			'transaction_description'=>"Commission Fee for Order#{{order_id}}",
			'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id),					  
			'transaction_type'=>"debit",
			'transaction_amount'=>($order->commission*$exchange_rate),
			'status'=>'paid',			
			'orig_transaction_amount'=>$order->commission,
			'merchant_base_currency'=>$order->base_currency_code,
			'admin_base_currency'=>$order->admin_base_currency,			  
			'exchange_rate_merchant_to_admin'=>$order->exchange_rate_merchant_to_admin,
			'exchange_rate_admin_to_merchant'=>$order->exchange_rate_admin_to_merchant,
			'reference_id'=>$order->order_id,
			'reference_id1'=>'commission_fee',
		];

		if($order_type=="delivery"){		
			if(array_key_exists($order->payment_code,(array)$all_offline) && $self_delivery ){	
				unset($params_merchant);
				$params_merchant[] = [
					'transaction_description'=>"Cash Collected for Order#{{order_id}}",
					'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id),					  
					'transaction_type'=>"debit",
					'transaction_amount'=>($order->sub_total*$exchange_rate),
					'status'=>'paid',			
					'orig_transaction_amount'=>$order->sub_total,
					'merchant_base_currency'=>$order->base_currency_code,
					'admin_base_currency'=>$order->admin_base_currency,			  
					'exchange_rate_merchant_to_admin'=>$order->exchange_rate_merchant_to_admin,
					'exchange_rate_admin_to_merchant'=>$order->exchange_rate_admin_to_merchant,
					'reference_id'=>$order->order_id,
					'reference_id1'=>'cash_collected',
				];			
				$params_merchant[] = [
					'transaction_description'=>"Commission Fee for Order#{{order_id}}",
					'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id),					  
					'transaction_type'=>"debit",
					'transaction_amount'=>($order->commission*$exchange_rate),
					'status'=>'paid',			
					'orig_transaction_amount'=>$order->commission,
					'merchant_base_currency'=>$order->base_currency_code,
					'admin_base_currency'=>$order->admin_base_currency,			  
					'exchange_rate_merchant_to_admin'=>$order->exchange_rate_merchant_to_admin,
					'exchange_rate_admin_to_merchant'=>$order->exchange_rate_admin_to_merchant,
					'reference_id'=>$order->order_id,
					'reference_id1'=>'commission_fee',
				];
			}				
		} else {
			if(array_key_exists($order->payment_code,(array)$all_offline)){				
				unset($params_merchant);
				$params_merchant[] = [
					'transaction_description'=>"Cash Collected for Order#{{order_id}}",
					'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id),					  
					'transaction_type'=>"debit",
					'transaction_amount'=>($order->sub_total*$exchange_rate),
					'status'=>'paid',			
					'orig_transaction_amount'=>$order->sub_total,
					'merchant_base_currency'=>$order->base_currency_code,
					'admin_base_currency'=>$order->admin_base_currency,			  
					'exchange_rate_merchant_to_admin'=>$order->exchange_rate_merchant_to_admin,
					'exchange_rate_admin_to_merchant'=>$order->exchange_rate_admin_to_merchant,
					'reference_id'=>$order->order_id,
					'reference_id1'=>'cash_collected',
				];			
				$params_merchant[] = [
					'transaction_description'=>"Commission Fee for Order#{{order_id}}",
					'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id),					  
					'transaction_type'=>"debit",
					'transaction_amount'=>($order->commission*$exchange_rate),
					'status'=>'paid',			
					'orig_transaction_amount'=>$order->commission,
					'merchant_base_currency'=>$order->base_currency_code,
					'admin_base_currency'=>$order->admin_base_currency,			  
					'exchange_rate_merchant_to_admin'=>$order->exchange_rate_merchant_to_admin,
					'exchange_rate_admin_to_merchant'=>$order->exchange_rate_admin_to_merchant,
					'reference_id'=>$order->order_id,
					'reference_id1'=>'commission_fee',
				];
		    }			
		}

		// ========================== FEES ===========================

		if($order->delivery_fee>0){
			if(!$self_delivery){
				$params[] = array(					  		 
					'transaction_description'=>"Delivery Fee from Order #{{order_id}}",
					'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id),					  
					'transaction_type'=>"credit",
					'transaction_amount'=>($order->delivery_fee*$exchange_rate),
					'status'=>'paid',						
					'orig_transaction_amount'=>$order->delivery_fee,
					'merchant_base_currency'=>$order->base_currency_code,
					'admin_base_currency'=>$order->admin_base_currency,			  
					'exchange_rate_merchant_to_admin'=>$order->exchange_rate_merchant_to_admin,
					'exchange_rate_admin_to_merchant'=>$order->exchange_rate_admin_to_merchant,
					'reference_id'=>$order->order_id,
					'reference_id1'=>'delivery_fee',
				);			
		    } else {
				$params_merchant[] = array(					  		 
					'transaction_description'=>"Delivery Fee from Order #{{order_id}}",
					'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id),					  
					'transaction_type'=>"credit",
					'transaction_amount'=>($order->delivery_fee*$exchange_rate),
					'status'=>'paid',						
					'orig_transaction_amount'=>$order->delivery_fee,
					'merchant_base_currency'=>$order->base_currency_code,
					'admin_base_currency'=>$order->admin_base_currency,			  
					'exchange_rate_merchant_to_admin'=>$order->exchange_rate_merchant_to_admin,
					'exchange_rate_admin_to_merchant'=>$order->exchange_rate_admin_to_merchant,
					'reference_id'=>$order->order_id,
					'reference_id1'=>'delivery_fee',
				);							
			}
		} 

		if($order->packaging_fee>0){
			$params_merchant[] = array(					  		 
				'transaction_description'=>"Packaging Fee for Order#{{order_id}}",
				'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id),					  
				'transaction_type'=>"credit",
				'transaction_amount'=>($order->packaging_fee*$exchange_rate),
				'status'=>'paid',						
				'orig_transaction_amount'=>$order->packaging_fee,
				'merchant_base_currency'=>$order->base_currency_code,
				'admin_base_currency'=>$order->admin_base_currency,			  
				'exchange_rate_merchant_to_admin'=>$order->exchange_rate_merchant_to_admin,
				'exchange_rate_admin_to_merchant'=>$order->exchange_rate_admin_to_merchant,
				'reference_id'=>$order->order_id,
				'reference_id1'=>'packaging_fee',
			);			
		}

		if($order->service_fee>0){
			$params[] = array(					  		 
				'transaction_description'=>"Service Fee from Order #{{order_id}}",
				'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id),					  
				'transaction_type'=>"credit",
				'transaction_amount'=>($order->service_fee*$exchange_rate),
				'status'=>'paid',						
				'orig_transaction_amount'=>$order->service_fee,
				'merchant_base_currency'=>$order->base_currency_code,
				'admin_base_currency'=>$order->admin_base_currency,			  
				'exchange_rate_merchant_to_admin'=>$order->exchange_rate_merchant_to_admin,
				'exchange_rate_admin_to_merchant'=>$order->exchange_rate_admin_to_merchant,
				'reference_id'=>$order->order_id,
				'reference_id1'=>'service_fee',
			);			
		}

		if($order->small_order_fee>0){
			$params[] = array(					  		 
				'transaction_description'=>"Small Order Fee from Order #{{order_id}}",
				'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id),					  
				'transaction_type'=>"credit",
				'transaction_amount'=>($order->small_order_fee*$exchange_rate),
				'status'=>'paid',						
				'orig_transaction_amount'=>$order->small_order_fee,
				'merchant_base_currency'=>$order->base_currency_code,
				'admin_base_currency'=>$order->admin_base_currency,			  
				'exchange_rate_merchant_to_admin'=>$order->exchange_rate_merchant_to_admin,
				'exchange_rate_admin_to_merchant'=>$order->exchange_rate_admin_to_merchant,
				'reference_id'=>$order->order_id,
				'reference_id1'=>'small_order_fee',
			);			
		}
		
		// POINTS
		if($order->points>0){
			if($points_cover_cost=="website"){
				$params[] = array(					  		 
					'transaction_description'=>"Points Discount for order #{{order_id}}",
					'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id),					  
					'transaction_type'=>"debit",
					'transaction_amount'=>($order->points*$exchange_rate),
					'status'=>'paid',						
					'orig_transaction_amount'=>$order->points,
					'merchant_base_currency'=>$order->base_currency_code,
					'admin_base_currency'=>$order->admin_base_currency,			  
					'exchange_rate_merchant_to_admin'=>$order->exchange_rate_merchant_to_admin,
					'exchange_rate_admin_to_merchant'=>$order->exchange_rate_admin_to_merchant,
					'reference_id'=>$order->order_id,
					'reference_id1'=>'points',
				);			
			} else if ($points_cover_cost=="merchant") {
				$params_merchant[] = array(					  		 
					'transaction_description'=>"Points Discount for order #{{order_id}}",
					'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id),					  
					'transaction_type'=>"debit",
					'transaction_amount'=>($order->points*$exchange_rate),
					'status'=>'paid',						
					'orig_transaction_amount'=>$order->points,
					'merchant_base_currency'=>$order->base_currency_code,
					'admin_base_currency'=>$order->admin_base_currency,			  
					'exchange_rate_merchant_to_admin'=>$order->exchange_rate_merchant_to_admin,
					'exchange_rate_admin_to_merchant'=>$order->exchange_rate_admin_to_merchant,
					'reference_id'=>$order->order_id,
					'reference_id1'=>'points',
				);			
			}
		}

		// VOUCHER
		if($order->promo_total>0){			
			if($order->promo_owner=="admin"){
				$params[] = array(					  		 
					'transaction_description'=>"Coupon Discount for order #{{order_id}}",
					'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id),					  
					'transaction_type'=>"debit",
					'transaction_amount'=>($order->promo_total*$exchange_rate),
					'status'=>'paid',						
					'orig_transaction_amount'=>$order->promo_total,
					'merchant_base_currency'=>$order->base_currency_code,
					'admin_base_currency'=>$order->admin_base_currency,			  
					'exchange_rate_merchant_to_admin'=>$order->exchange_rate_merchant_to_admin,
					'exchange_rate_admin_to_merchant'=>$order->exchange_rate_admin_to_merchant,
					'reference_id'=>$order->order_id,
					'reference_id1'=>'coupon',
				);			
			} else if ( $order->promo_owner=="merchant"){
				$params_merchant[] = array(					  		 
					'transaction_description'=>"Coupon Discount for order #{{order_id}}",
					'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id),					  
					'transaction_type'=>"debit",
					'transaction_amount'=>($order->promo_total*$exchange_rate),
					'status'=>'paid',						
					'orig_transaction_amount'=>$order->promo_total,
					'merchant_base_currency'=>$order->base_currency_code,
					'admin_base_currency'=>$order->admin_base_currency,			  
					'exchange_rate_merchant_to_admin'=>$order->exchange_rate_merchant_to_admin,
					'exchange_rate_admin_to_merchant'=>$order->exchange_rate_admin_to_merchant,
					'reference_id'=>$order->order_id,
					'reference_id1'=>'coupon',
				);			
			}
		}

		// PROMO

		if(is_array($params) && count($params)>=1){
			foreach ($params as $items) {				
				try {
					self::findTransaction($admin_card_id,$items['transaction_type'],$items['reference_id'],$items['reference_id1']);
				    CWallet::inserTransactions($admin_card_id,$items);
				} catch (Exception $e) {}			
			}
		}

		if(is_array($params_merchant) && count($params)>=1){
			foreach ($params_merchant as $items) {				
				try {
					self::findTransaction($merchant_card_id,$items['transaction_type'],$items['reference_id'],$items['reference_id1']);
				    CWallet::inserTransactions($merchant_card_id,$items);
				} catch (Exception $e) {}			
			}
		}
	}

	public static function getRefundBalance($card_id=0,$order_id=0)
	{
		$balance = 0;
		$stmt = "
		SELECT 
			SUM(CASE WHEN transaction_type = 'credit' THEN transaction_amount ELSE 0 END) -
			SUM(CASE WHEN transaction_type = 'debit' THEN transaction_amount ELSE 0 END) AS total_balance
		FROM {{wallet_transactions}}
		WHERE card_id=".q($card_id)."
		AND reference_id=".q($order_id)."
		AND status='paid'
		";
		if($res = Yii::app()->db->createCommand($stmt)->queryRow()){
			$balance = $res['total_balance'];
		}
		return $balance;
	}
	
}
/*end class*/