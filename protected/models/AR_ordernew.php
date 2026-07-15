<?php
class AR_ordernew extends CActiveRecord
{	

	public $items;
	public $meta;
	public $address_component;
	public $cart_uuid;
	public $total_items;
	
	public $remarks;
	public $ramarks_trans;
	public $change_by;
	public $customer_name;
	
	public $restaurant_name,$logo,$path;
	public $tax_use, $tax_for_delivery;
	public $total_sold , $first_name, $last_name,$month, $monthly_sales , $min_diff,
	$ratings,
	$delivered_old_status,	
	$diff_days,
	$diff_hours,
	$diff_minutes,
	$diff_days1,
	$diff_hours1,
	$diff_minutes1,
	$date_now,
	$allowed_number_task,
	$payment_uuid,
	$validate_payment_change,
	$payment_change,
	$old_driver_id,
	$room_id,$table_id,$payment_reference,
	$on_demand_availability,$guest_number,
	$booking_enabled,$tracking_data,
	$merchant_uuid,
	$room_name,
	$table_name,
	$payment_name,
	$refund_amount,
	$earn_points,$driver_ratings,
	$kot_items_array
	;

	public $item_id, $qty, $item_name,$void_reason,
	$kot_status,$voided_by,$voided_at,$amount_entered,$payment_type,$split_payment_type,
	$split_data
	;

	public $order_day,$total_orders,$total_sales,$avg_order_value;
	
	/**
	 * Returns the static model of the specified AR class.
	 * @return static the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{ordernew}}';
	}
	
	public function primaryKey()
	{
	    return 'order_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		  'order_id'=>t("Order ID"),
		  'order_uuid'=>t("Order UUID"),		  
		);
	}
	
	public function rules()
	{
		 return array(
            array('order_uuid,merchant_id,client_id,status,payment_status,service_code,payment_code,
            sub_total,total,whento_deliver,delivery_date', 
            'required','message'=> t(Helper_field_required2) ),   
            
            array('order_uuid','unique','message'=>t(Helper_field_unique)),
            
            array('total_discount,points,service_fee,delivery_fee,packaging_fee,tax,courier_tip,
            promo_code,promo_total,delivery_time,delivery_time_end,cash_change,commission_type,
            commission_based,commission,merchant_earning,use_currency,base_currency,
            exchange_rate,is_critical,date_created,date_modified,ip_address,driver_id,vehicle_id,delivered_at,request_from,
			use_currency_code,base_currency_code,
			admin_base_currency,exchange_rate_merchant_to_admin,exchange_rate_admin_to_merchant,exchange_rate_use_currency_to_admin,on_demand_availability,created_at,order_reference,
			order_accepted_at,preparation_time_estimation,delivery_time_estimation,pickup_time,late_notification_sent,preparation_late_sent,delivering_late_sent,assigned_at,
			promo_cap,offer_cap,retry_attempts,last_retry,assigned_expired_at,delivery_date_time,refund_amount,flow_status,
			is_commission_applied,merchant_subtotal,merchant_total
			','safe'),
            
            array('formatted_address','required','on'=>'delivery' ,'message'=>t("Delivery address is required") ),
			

			array('client_id','valdidateCanOrder','on'=>"delivery,pickup,dinein"),

			array('payment_code','validatePaymentChange'),

			//array('driver_id','checkAssignOrder','on'=>"delivery_change_status"),
			// array('driver_id','checkMaxAllowedTask','on'=>"delivery_order_process"),
			 array('driver_id','canCollectOrder','on'=>"delivery_order_process"),

			array('driver_id','checkAssignOrder','on'=>"assign_order"),
			array('driver_id','checkMaxAllowedTask','on'=>"delivery_accept_order"),
			array('driver_id','canCollectOrder','on'=>"delivery_accept_order"),

			array('room_id','validateBookingPOS','on'=>"pos_entry,dinein,pos_entry_items"),

			array('guest_number','validateBookingGuest','on'=>"dinein,pos_entry_items"),

			array('order_id','ValidateCanAdd','on'=>"change_status"),
                        
         );
	}

	public function ValidateCanAdd($attribute,$params)
	{	
		// PLANS		
		try {
			$merchant = CMerchants::get($this->merchant_id);
			if($merchant->merchant_type==1){				
				$status_accepted = Cplans::getStatusAccepted();				
				if(!$status_accepted){
					return true;
				}
				
				if($this->status!=$status_accepted){
					return true;
				}				
				
				try {
					Cplans::canAddOrder($this->merchant_id,$merchant);										
				} catch (Exception $e) {
					$this->addError($attribute, $e->getMessage() );
				}
			}			
		} catch (Exception $e) {
			$this->addError($attribute, $e->getMessage() );
		}						
	}

	public function validatePaymentChange($attribute,$params)
	{		
			
		if($this->validate_payment_change){
			$model = AR_payment_gateway::model()->find("payment_code=:payment_code",[
				':payment_code'=>$this->payment_code
			]);
			if($model){							
				$amount = $this->amount_due>0?$this->amount_due:$this->total;				
				if($model->payment_code=="cod"){
					if($model->attr1==1){				
						if($this->payment_change<=0){
							$this->addError($attribute, t("Please enter change amount") );
						} else {							
							if($amount>$this->payment_change){					
								$this->addError($attribute, t("Change must not lower than total amount") );
							}
						}					
					}
					if($model->attr2>0 && is_numeric($model->attr2)){						
						//$total_exchange = ($this->total*$this->exchange_rate_merchant_to_admin);
						$total_exchange = $amount;
						if(floatval($total_exchange)>floatval($model->attr2)){		
							try {
								$payment = CPayments::getPaymentByCode($this->payment_code);
							} catch (Exception $e) {
								$payment = [];
							}
							$this->addError($attribute, t("Maximum limit for {payment_name} is {maximum}",[
								'{payment_name}'=>$payment?$payment->payment_name:$this->payment_code,
								'{maximum}'=>Price_Formatter::formatNumber(floatval($model->attr2))
							]) );
						}					
					}
			    }
			}	

			if($model){
				if($model->payment_code=="paydelivery"){			
					$merchant_type = CMerchants::getMerchantType($this->merchant_id);					
					if(!CPayments::validatePaydelivery($merchant_type,$this->merchant_id,$this->payment_uuid)){
						$this->addError($attribute, t("Payment is no longer available, please choose another payment method") );				
					}
				}		
		    }
			
	    }			
	}

	public function valdidateCanOrder($attribute,$params)
	{
		if($this->isNewRecord){		
			$options = OptionsTools::find(['restrict_order_by_status']);
			$restrict_order_by_status = isset($options['restrict_order_by_status'])?json_decode($options['restrict_order_by_status'],true):'';			
			if(is_array($restrict_order_by_status) && count($restrict_order_by_status)>=1){
				$criteria=new CDbCriteria();
				$criteria->addCondition("client_id=:client_id");
				$criteria->params = ['client_id'=>$this->client_id];
				$criteria->addInCondition('status',$restrict_order_by_status);
				$criteria->order = "order_id DESC";
				$criteria->limit="0,1";				
				if($model = AR_ordernew::model()->find($criteria)){
					$this->addError($attribute, t("You have previous order that is still processing") );
				}				
			}			
			
			$stmt = "SELECT * FROM {{merchant_meta}}
			WHERE  merchant_id=".q($this->merchant_id)."
			AND meta_name ='block_customer'
			AND meta_value=".q($this->client_id)."	
			LIMIT 0,1
			";			
			if(CCacheData::queryRow($stmt)){				
				$this->addError($attribute, t("Your account is blocked by merchant") );
			}

			// CHECK ORDER LIMIT
			$now = CommonUtility::dateNow(); 
			$dateOnly = CommonUtility::dateOnly(); 
            $current_time = date("H:i", strtotime($now)); 
            $current_day = strtolower(date("l", strtotime($now))); 
			$stmt = "
			SELECT id,number_order_allowed,order_status,start_time,end_time
			FROM {{order_time_management}}
			WHERE 
			merchant_id=".q($this->merchant_id)."
			AND transaction_type = ".q($this->service_code)."
			AND days = ".q($current_day)."
			AND STR_TO_DATE(start_time, '%H:%i') <= STR_TO_DATE(" . q($current_time) . ", '%H:%i')
			AND STR_TO_DATE(end_time, '%H:%i') >= STR_TO_DATE(" . q($current_time) . ", '%H:%i')
			AND status='publish'
			";			
			if($rows =  CCacheData::queryRow($stmt)){					
				$slot_start = $rows['start_time']; 		
				$slot_end = $rows['end_time']; 		
				$number_order_allowed = $rows['number_order_allowed'];
				$order_status = !empty($rows['order_status']) ? json_decode($rows['order_status'],true) : null;				
				
				$params = array(
					':merchant_id'=>$this->merchant_id,
					':service_code' => $this->service_code,
					':created_at'  => $dateOnly,
					':slot_start' => $slot_start,
                    ':slot_end'  => $slot_end,
				);
				$sql = "SELECT COUNT(*) as total
				FROM {{ordernew}} 
				WHERE 
				merchant_id=:merchant_id
				AND service_code=:service_code 
				AND created_at=:created_at				
				";
				 if (strtotime($slot_start) > strtotime($slot_end)) {
					 $sql .= " AND ( TIME(date_created) >= STR_TO_DATE(:slot_start, '%H:%i')
                     OR TIME(date_created) <= STR_TO_DATE(:slot_end,   '%H:%i') )";
				 } else {
					 $sql .= " AND TIME(date_created) BETWEEN STR_TO_DATE(:slot_start, '%H:%i')
                                           AND STR_TO_DATE(:slot_end,   '%H:%i')";
				 }
				if($order_status){
					$sql.="AND status IN (" . CommonUtility::arrayToQueryParameters($order_status) . ")";
				}
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValues($params);				
				//echo strtr($command->getText(), $params);
				if($res = $command->queryRow()){
					$total = $res['total'];					
					if($total>=$number_order_allowed){
						$this->addError($attribute, t("Ordering is currently unavailable. Please try again later.") );				
					}
				}
			}			

			//$this->addError($attribute, t("xx") );				
		} 
	}

	public function checkAssignOrder()
	{		
			
		if($this->driver_id>0){
			$driver = CDriver::getDriver($this->driver_id);
			
			$now = date("Y-m-d g:i:s a");
			$date_now = date("Y-m-d");

			if(!$this->on_demand_availability){
				try {
					$data = CDriver::getDriverScheduleToday($this->driver_id,$date_now,$date_now,$now);			
					if(empty($data['shift_time_started']) || is_null($data['shift_time_started'])){
						$this->addError('driver_id', t("Driver has not started his/her shift") );				
					}
				} catch (Exception $e) {
					$this->addError('driver_id', t("Driver has no recent schedule") );
				}		
		    }							

			$assigned_group = AOrders::getOrderTabsStatus('assigned');				

			$criteria=new CDbCriteria();
			$criteria->addCondition("delivery_date=:delivery_date AND driver_id=:driver_id");
			$criteria->params = [
				':delivery_date'=>$this->date_now,
				':driver_id'=>intval($this->driver_id)
			];		
			$criteria->addInCondition('delivery_status',(array)$assigned_group); 
			
			$count = AR_ordernew::model()->count($criteria);		
			if($this->allowed_number_task>0){			
				if($count>=$this->allowed_number_task){				
					$this->addError('driver_id', t("Cannot assign order to driver, the max allowed per order has been reach.") );
				}
			}		
	    }
	}

	public function validateBookingPOS()
	{						
		if($this->service_code=="dinein" && $this->booking_enabled ){
			if(empty($this->room_id)){
				$this->addError('room_id', t("Room name is required") );			
			}
					
			if(empty($this->table_id)){
				$this->addError('table_id', t("Table name is required") );			
			}			
		}				
	}

	public function validateBookingGuest($attribute,$params)
	{
		if($this->service_code=="dinein" && $this->booking_enabled ){
			$guest_number = intval($this->guest_number);
			if($guest_number<=0){
				$this->addError($attribute, t("Guest number is required") );
			}
	    }
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){			
			if($this->isNewRecord){
				$this->date_created = CommonUtility::dateNow();							
				$this->created_at = CommonUtility::dateNow();							
			} else {
				$this->date_modified = CommonUtility::dateNow();	
				
				if($this->scenario=="delivery_declined"){
					// $this->driver_id = 0;					
					// $this->vehicle_id = 0;
				} elseif ( $this->scenario=="orderdelivered"){
					$this->delivered_at = CommonUtility::dateNow();
				}
			}
			$this->ip_address = CommonUtility::userIp();						
			return true;
		} else return true;
	}

	public function checkMaxAllowedTask()
	{		
		//if($this->delivered_old_status=="assigned"){
			$date_now = date("Y-m-d");
			$option = OptionsTools::find(['driver_allowed_number_task']);		
			$allowed_number_task = isset($option['driver_allowed_number_task'])?intval($option['driver_allowed_number_task']):0;		
			
			if($allowed_number_task>0){	
				$active_task_count = CDriver::getCountActiveTask($this->driver_id,$date_now);
				if($active_task_count>=$allowed_number_task){		
					$this->addError('driver_id', t("You have reach the maximum allowed to handle orders, if you have current active order finish it first.") );
					return false;
				} 		
			}
	    //}
	}

	public function canCollectOrder()
	{		

		
		//if($this->delivered_old_status=="assigned"){		
			try {
				$card_id = CWallet::getCardID( Yii::app()->params->account_type['driver'] , $this->driver_id );				
				$balance = CWallet::getBalance($card_id);			
			} catch (Exception $e) {
				$balance = 0;
			}

			$allowed_amount  = 0;
			try {
				$driver = CDriver::getDriver($this->driver_id);
				$allowed_amount = $driver->allowed_offline_amount;
			} catch (Exception $e) {

			}	
			
			$all_offline = CPayments::getPaymentTypeOnline(0);	
			$is_offline = array_key_exists($this->payment_code,(array)$all_offline)?true:false;		

			if($allowed_amount>0 && $is_offline==TRUE){
				$balance = $balance<=0?$balance*-1:$balance;			
				$balance_total = $allowed_amount - $balance;								
				if($this->total>=$balance_total){
					$this->addError('driver_id', t("You have reach the maximum allowed amount to collect cash.") );
					return false;
				} 
			}		
	    //}
	}
	
	protected function afterSave()
	{									
		
		parent::afterSave();
		
		if ( is_array($this->items) && count($this->items)>=1 ){
			foreach ($this->items as $items) {				
				$line_item = new AR_ordernew_item;
				$line_item->order_id = intval($this->order_id);				
				$line_item->item_row = $items['cart_row'];
				$line_item->cat_id = intval($items['cat_id']);
				$line_item->item_id = intval($items['item_id']);
				$line_item->item_token = $items['item_token'];
				$line_item->item_size_id = intval($items['price']['item_size_id']);
				$line_item->qty = intval($items['qty']);
				$line_item->special_instructions = $items['special_instructions'];
				$line_item->if_sold_out = $items['if_sold_out'];
				$line_item->price = floatval($items['price']['price']);
				$line_item->discount = floatval($items['price']['discount']);
				$line_item->discount_type = $items['price']['discount_type'];
				$line_item->tax_use = isset($items['tax']) ? json_encode($items['tax']) : '';
				$line_item->is_free = $items['is_free'] ?? 0;

				$line_item->merchant_price =  $items['price']['merchant_price'] ?? 0;
				$line_item->commission_amount = $items['price']['commission_amount'] ?? 0;
				$line_item->merchant_earning = $items['price']['merchant_earning'] ?? 0;

				if($line_item->save()){
					$this->kot_items_array[] = [
						'order_item_id'=>$items['item_id'],
						'qty'=>$line_item->qty,
						'note'=>$line_item->special_instructions
					];
					/*ADDONS*/
					if(isset($items['addons']) && count($items['addons'])>=1){
						foreach ($items['addons'] as $addons) {
							$subcat_id = $addons['subcat_id'];
							foreach ($addons['addon_items'] as $addon_items) {								
								$addon = new AR_ordernew_addons;
		                        $addon->order_id = intval($this->order_id);
		                        $addon->item_row = $items['cart_row'];
		                        $addon->subcat_id = intval($subcat_id);
		                        $addon->sub_item_id = intval($addon_items['sub_item_id']);
		                        $addon->qty = floatval($addon_items['qty']);
		                        $addon->price = floatval($addon_items['price']);
		                        $addon->addons_total = floatval($addon_items['addons_total']);
		                        $addon->multi_option = $addon_items['multiple'];

								$addon->merchant_price = $addon_items['merchant_price'] ?? 0;
								$addon->commission_amount = $addon_items['commission_amount'] ?? 0;
								$addon->merchant_earning = $addon_items['merchant_earning'] ?? 0;

		                        $addon->save();
							}
						}
					}
					/*END ADDONS*/
					
					/*ATTRIBUTES*/
					if(isset($items['attributes_raw']) && count($items['attributes_raw'])>=1){
						if(isset($items['attributes_raw']['cooking_ref']) && count($items['attributes_raw']['cooking_ref'])>=1){
							foreach ($items['attributes_raw']['cooking_ref'] as $cooking_id=>$cooking_ref) {
								$attributes = new AR_ordernew_attributes;
								$attributes->order_id = intval($this->order_id);;
								$attributes->item_row = $items['cart_row'];
								$attributes->meta_name = 'cooking_ref';
								$attributes->meta_value = $cooking_id;
								$attributes->save();
							}
						}
						
						if(isset($items['attributes_raw']['ingredients']) && count($items['attributes_raw']['ingredients'])>=1){
							foreach ($items['attributes_raw']['ingredients'] as $ingredients_id=>$ingredients) {
								$attributes = new AR_ordernew_attributes;
								$attributes->order_id = intval($this->order_id);;
								$attributes->item_row = $items['cart_row'];
								$attributes->meta_name = 'ingredients';
								$attributes->meta_value = $ingredients_id;
								$attributes->save();
							}
						}						
					}
					/*END ATTRIBUTES*/																				
				} else {
					//dump($line_item->getErrors());					
				}
			} /*end foreach*/
			
		} /*end item*/
		
		/*META*/
		if(is_array($this->meta) && count($this->meta)>=1){
			foreach ($this->meta as $meta_key=>$meta_value) {					
				$meta = new AR_ordernew_meta;
				$meta->order_id = intval($this->order_id);
				$meta->meta_name = $meta_key;
				$meta->meta_value = $meta_value;
				$meta->save();
			}
		}
		
		/*ADDRESS COMPONENTS*/
		if(is_array($this->address_component) && count($this->address_component)>=1){
			foreach ($this->address_component as $meta_key=>$meta_value) {					
				$meta = new AR_ordernew_meta;
				$meta->order_id = intval($this->order_id);
				$meta->meta_name = $meta_key;
				$meta->meta_value = $meta_value;
				$meta->save();
			}
		}
		
		if(is_array($this->tax_use) && count($this->tax_use)>=1){
			$meta = new AR_ordernew_meta;
			$meta->order_id = intval($this->order_id);
			$meta->meta_name = 'tax_use';
			$meta->meta_value = json_encode($this->tax_use);
			$meta->save();
		}
		
		if(is_array($this->tax_for_delivery) && count($this->tax_for_delivery)>=1){
			$meta = new AR_ordernew_meta;
			$meta->order_id = intval($this->order_id);
			$meta->meta_name = 'tax_for_delivery';
			$meta->meta_value = json_encode($this->tax_for_delivery);
			$meta->save();
		}

		// ADD OTP
		if($this->isNewRecord){
			$meta = new AR_ordernew_meta;
			$meta->order_id = intval($this->order_id);
			$meta->meta_name = 'order_otp';
			$meta->meta_value = CommonUtility::generateNumber(6);
			$meta->save();
		}						

		// PLANS				
		if($this->scenario=="change_status"){
			try {				
				$merchant = CMerchants::get($this->merchant_id);
				if($merchant->merchant_type==1){	
					$status_accepted = Cplans::getStatusAccepted();	
					if($this->status==$status_accepted){
						Cplans::UpdateOrdersAdded($this->merchant_id);
					}				
				}
			} catch (Exception $e) {			
			}
		}		
		
		Yii::import('ext.runactions.components.ERunActions');	
		$cron_key = CommonUtility::getCronKey();		
		$get_params = array( 
		   'order_uuid'=> $this->order_uuid,
		   'key'=>$cron_key,
		   'language'=>Yii::app()->language,
		   'time'=>time()
		);								
					
		if ( $this->scenario=="new_order"){
			
			$args = array();
			try {
				$customer = ACustomer::get($this->client_id);
				$args = array(
				  '{{customer_name}}'=> $customer->first_name." ".$customer->last_name
				);
			} catch (Exception $e) {
				//
			}			
			
			$history = new AR_ordernew_history;
			$history->order_id = $this->order_id;
			$history->status = $this->status;
			$history->remarks = "Order placed by {{customer_name}}";			
			$history->ramarks_trans = json_encode($args);
			$history->save();
			
			/*CLEAR CART*/
			try {
				CCart::clear($this->cart_uuid);
			} catch (Exception $e) {
				//
			}			

			//CommonUtility::pushJobs("AfterPurchase",$get_params);
			try {            
				$jobs = 'AfterPurchase';
				$jobInstance = new $jobs($get_params);
				$jobInstance->execute();      
		    } catch (Exception $e) { }

			CommonUtility::pushJobs("AutoPrint",$get_params);
			CommonUtility::pushJobs("SendTokitchen",$get_params);
			CommonUtility::pushJobs("SendOrderWhatsapp",$get_params);			

			CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/afterpurchase?".http_build_query($get_params) );
			if($this->service_code=="delivery"){				
				CommonUtility::pushJobs("AutoAssign",$get_params);
			}			
		} elseif ($this->scenario=="change_status"){
						
			$this->insertHistory();			

			CommonUtility::pushJobs("SendTokitchen",$get_params);
			CommonUtility::pushJobs("Afterupdatestatus",$get_params);				
			CommonUtility::pushJobs("DebitDiscount",$get_params);
			CommonUtility::pushJobs("AutoPrint",$get_params);
			if($this->service_code=="delivery"){	
			    CommonUtility::pushJobs("AutoAssign",$get_params);
			}
			CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/afterupdatestatus?".http_build_query($get_params) );		
			
			try {            
				$jobs = 'Trackorder';
				$jobInstance = new $jobs($get_params);
				$jobInstance->execute();      
		    } catch (Exception $e) { }
			
		} elseif ($this->scenario=="cancel_order" || $this->scenario=="reject_order" ){
			
			$this->insertHistory();						
			CommonUtility::pushJobs("Ordercancel",[
				'order_uuid'=> $this->order_uuid,
				'refund_type'=>'full_refund',
			]);
			
			CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/afterupdatestatus?".http_build_query($get_params) );
			
			try {            
				$jobs = 'Trackorder';
				$jobInstance = new $jobs($get_params);
				$jobInstance->execute();      
		    } catch (Exception $e) { }
						
		} elseif ($this->scenario=="delay_order"){
			
			$this->insertHistory();						
			CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/afterdelayorder?".http_build_query($get_params) );
			
		} elseif ($this->scenario=="adjustment"){
			CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/afteradjustment?".http_build_query($get_params) );
			
		} elseif ($this->scenario=="customer_cancel_partial_refund"){
			$this->insertHistory();	
			CommonUtility::pushJobs("Ordercancel",[
				'order_uuid'=> $this->order_uuid,
				'refund_type'=>'partial_refund',
				'refund_amount'=>$this->refund_amount
			]);
			CommonUtility::pushJobs("Trackorder",$get_params);
			
		} elseif ($this->scenario=="pos_entry"){

			CCart::clear($this->cart_uuid);

			$this->insertHistory();	

			$payment_ref = $this->payment_reference;
			if(empty($payment_ref)){
				$payment_ref = CommonUtility::generateToken("{{ordernew_transaction}}",'payment_reference',CommonUtility::generateAplhaCode(10));
			}			

			$model = new AR_ordernew_transaction;
			$model->order_id = $this->order_id;
			$model->merchant_id = $this->merchant_id;
			$model->client_id = $this->client_id;
			$model->payment_code = $this->payment_code;
			$model->trans_amount = $this->total;
			$model->currency_code = $this->use_currency_code;
			$model->payment_reference = $payment_ref;
			$model->status = "paid";
			$model->save();		
			
			if(!empty($this->table_id)){								
				CBooking::updateTableStatus($this->merchant_id,$this->table_id);				
			}						
			
			try {            
				$jobs = 'AfterPurchase';
				$jobInstance = new $jobs($get_params);
				$jobInstance->execute();      
		    } catch (Exception $e) { }

			CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/afterpurchase?".http_build_query($get_params) );
			
			$delivered_status = CEarnings::getDeliveredStatus();
			if(in_array( CommonUtility::cleanString($this->status) ,$delivered_status)){					
				CommonUtility::pushJobs("Afterupdatestatus",$get_params);	
			}			
			
		} elseif ($this->scenario=="delivery_change_status"){		
			$get_params['current_status'] = $this->delivered_old_status;
			$get_params['change_by'] = $this->change_by;
			$get_params['remarks'] = $this->remarks;		
			$get_params['scenario'] = $this->scenario;
			CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/afterdeliverychangestatus?".http_build_query($get_params) );			

		} elseif ($this->scenario=="assign_order"){
			$get_params['current_status'] = $this->delivered_old_status;
			$get_params['change_by'] = $this->change_by;
			$get_params['remarks'] = $this->remarks;		
			$get_params['scenario'] = $this->scenario;		

			try {            
				$jobs = 'OnassignOrder';
				$jobInstance = new $jobs($get_params);
				$jobInstance->execute();
		    } catch (Exception $e) { }

			try {			
				$jobs = 'DeliveryChangeStatus';
				$jobInstance = new $jobs($get_params);
				$jobInstance->execute();	
		    } catch (Exception $e) { }
			
			try {
				$jobs = 'DeliveryRunStatus';
				$jobInstance = new $jobs($get_params);
				$jobInstance->execute();
		    } catch (Exception $e) { }
			
			
		} elseif ($this->scenario=="delivery_order_process"){		
			$get_params['current_status'] = $this->delivered_old_status;
			$get_params['change_by'] = $this->change_by;
			$get_params['remarks'] = $this->remarks;	
			$get_params['scenario'] = $this->scenario;	
			
			try {
				$jobs = 'DeliveryChangeStatus'; $jobInstance = new $jobs($get_params); $jobInstance->execute();
		    } catch (Exception $e) { }
			CommonUtility::pushJobs("DeliveryRunStatus",$get_params);

			
		} elseif ($this->scenario=="delivery_accept_order"){		
			$get_params['current_status'] = $this->delivered_old_status;
			$get_params['change_by'] = $this->change_by;
			$get_params['remarks'] = $this->remarks;	
			$get_params['scenario'] = $this->scenario;		

			try{
			   $jobs = 'DeliveryChangeStatus'; $jobInstance = new $jobs($get_params); $jobInstance->execute();
		    } catch (Exception $e) { }
			CommonUtility::pushJobs("DeliveryRunStatus",$get_params);			

		} elseif ($this->scenario=="delivery_orderpickup"){		
			$get_params['current_status'] = $this->delivered_old_status;
			$get_params['change_by'] = $this->change_by;
			$get_params['remarks'] = $this->remarks;	
			$get_params['scenario'] = $this->scenario;		
			
			Yii::app()->db->createCommand("
			UPDATE {{ordernew}} SET pickup_time=".q(CommonUtility::dateNow())."
			WHERE order_id=".q($this->order_id)."
			")->query();

			try {
			   $jobs = 'DeliveryChangeStatus'; $jobInstance = new $jobs($get_params); $jobInstance->execute();
		    } catch (Exception $e) { }

			CommonUtility::pushJobs("DeliveryRunStatus",$get_params);
			CommonUtility::pushJobs("DeliverySendProgress",$get_params);			

		} elseif ($this->scenario=="delivery_onthewaycustomer"){		
			$get_params['current_status'] = $this->delivered_old_status;
			$get_params['change_by'] = $this->change_by;
			$get_params['remarks'] = $this->remarks;	
			$get_params['scenario'] = $this->scenario;			

			$meta_stats = AR_admin_meta::getValue('tracking_status_in_transit');
			$tracking_status_in_transit = isset($meta_stats['meta_value'])?$meta_stats['meta_value']:'';			

			Yii::app()->db->createCommand("
			UPDATE {{ordernew}} SET status=".q($tracking_status_in_transit)."
			WHERE order_id=".q($this->order_id)."
			")->query();

			try {
			  $jobs = 'DeliveryChangeStatus'; $jobInstance = new $jobs($get_params); $jobInstance->execute();
		    } catch (Exception $e) { }

			CommonUtility::pushJobs("DeliveryRunStatus",$get_params);
			CommonUtility::pushJobs("DeliveryTimeEstimation",$get_params);			

		} elseif ($this->scenario=="delivery_arrivedatcustomer"){		
			$get_params['current_status'] = $this->delivered_old_status;
			$get_params['change_by'] = $this->change_by;
			$get_params['remarks'] = $this->remarks;	
			$get_params['scenario'] = $this->scenario;							
			CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/onarrivedtocustomer?".http_build_query($get_params) );	

			try {
			  $jobs = 'DeliveryChangeStatus'; $jobInstance = new $jobs($get_params); $jobInstance->execute();
		    } catch (Exception $e) { }

			CommonUtility::pushJobs("DeliveryRunStatus",$get_params);
			CommonUtility::pushJobs("DeliverySendProgress",$get_params);			
				
		} elseif ($this->scenario=="delivery_declined"){
			$get_params['current_status'] = $this->delivered_old_status;
			$get_params['change_by'] = $this->change_by;
			$get_params['remarks'] = $this->remarks;		
			$get_params['scenario'] = $this->scenario;			


			try {
			  $jobs = 'DeliveryChangeStatus'; $jobInstance = new $jobs($get_params); $jobInstance->execute();
		    } catch (Exception $e) { }

			CommonUtility::pushJobs("DeliveryRunStatus",$get_params);					
			
		} elseif ($this->scenario=="delivery_failed"){
			$get_params['current_status'] = $this->delivered_old_status;
			$get_params['change_by'] = $this->change_by;
			$get_params['remarks'] = $this->remarks;	
			$get_params['scenario'] = $this->scenario;				
			CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/afterdelivered?".http_build_query($get_params) );

		} elseif ($this->scenario=="orderdelivered"){
			$get_params['current_status'] = $this->delivered_old_status;
			$get_params['change_by'] = $this->change_by;
			$get_params['remarks'] = $this->remarks;	
			$get_params['scenario'] = $this->scenario;							
			CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/afterdelivered?".http_build_query($get_params) );			

		} elseif ($this->scenario=="timeout_accept_order"){
			// $get_params['driver_id'] = $this->old_driver_id;			
			// CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/aftertimeoutacceptorder?".http_build_query($get_params) );			

		} elseif ($this->scenario=="tableside_ordering"){
			
			CCart::clear($this->cart_uuid);

			$noti = new AR_notifications;    							
			$noti->notication_channel = $this->merchant_uuid;
			$noti->notification_event = Yii::app()->params->realtime['notification_event'] ;
			$noti->notification_type = 'table_payment';
			$noti->message = "Table #{room_name}-{table_name} has paid Order #{order_id} using {payment_name}";				
			$noti->message_parameters = json_encode([				
				'{room_name}'=>$this->room_name,
				'{table_name}'=>$this->table_name,
				'{payment_name}'=>$this->payment_name,
				'{order_id}'=>$this->order_id,
			]);
			$meta_data = [
				'notification_type'=>"table_payment",								
				'title'=>'',
				'url'=>Yii::app()->createAbsoluteUrl(BACKOFFICE_FOLDER."/orders/view",[
					'order_uuid'=>$this->order_uuid
				])
			];
			$noti->meta_data = json_encode($meta_data);
			$noti->save();

		} elseif ($this->scenario=="update_preparation_time"){

			try {            
				$jobs = 'Trackorder';
				$jobInstance = new $jobs($get_params);
				$jobInstance->execute();      
		    } catch (Exception $e) { }
			
		} elseif ($this->scenario=="order_deleted"){
			try {				
				//CEarnings::fullRefund($this->order_id,"Deleted Order #{{order_id}} (Reversal)");
				$this->insertHistory();						
				CommonUtility::pushJobs("Ordercancel",[
					'order_uuid'=> $this->order_uuid,
					'refund_type'=>'full_refund',
				]);
				CommonUtility::pushJobs("Trackorder",$get_params);
			} catch (Exception $e) {}	

		} elseif ($this->scenario=="pos_entry_items"){

			if($this->isNewRecord){

				if($this->service_code=='dinein'){
					CBooking::setTableStatus($this->table_id,$this->order_id,'occupied');
				}

				$kot_number = Ckitchen::getLastKOTIncrement($this->merchant_id);			
				$model_kot = new AR_kot_tickets();			
				$model_kot->kot_number = $kot_number;
				$model_kot->order_id = $this->order_id;
				$model_kot->merchant_id = $this->merchant_id;
				$model_kot->save();
								
				if(is_array($this->kot_items_array) && count($this->kot_items_array)){
					foreach ($this->kot_items_array as &$kotItems) {
						$kotItems['kot_id'] = $model_kot->kot_id;						
					}					
					$builder=Yii::app()->db->schema->commandBuilder;
					$command=$builder->createMultipleInsertCommand('{{kot_items}}',$this->kot_items_array);
					$command->execute();
				}
				
				try {
					CCart::clear($this->cart_uuid);
				} catch (Exception $e) { }
			}			
		} elseif ($this->scenario=="order_voided"){

			COrders::savedAttributes($this->order_id,'void_reason',$this->void_reason);	 
			 
			 Yii::app()->db->createCommand("
			  UPDATE {{ordernew_item}}
			  SET kot_status='voided',
			  voided_by=".q($this->change_by).",
			  voided_at=".q(CommonUtility::dateNow()).",
			  void_reason=".q($this->void_reason)."
			 ")->query();

			 $this->insertHistory();
			 	
		} elseif ($this->scenario=="add_payment"){
			$payment_ref = $this->payment_reference;
			if(empty($payment_ref)){
				$payment_ref = CommonUtility::generateToken("{{ordernew_transaction}}",'payment_reference',CommonUtility::generateAplhaCode(10));
			}				
				
			// dump("flow_status=>$this->flow_status");
			// dump("order_id=>$this->order_id");
			if($this->flow_status=='paid' && $this->service_code=='dinein'){
				CBooking::setTableStatusByOrder($this->order_id,'cleaning');
			}

			if (empty($this->split_data) || !is_array($this->split_data)) {
				$model = new AR_ordernew_transaction;
				$model->order_id = $this->order_id;
				$model->merchant_id = $this->merchant_id;
				$model->client_id = $this->client_id;
				$model->payment_code = $this->payment_type;
				$model->trans_amount = $this->amount_entered;
				$model->currency_code = $this->use_currency_code;
				$model->payment_reference = $payment_ref;
				$model->status = "paid";	
				$model->save();		
			} else {
				foreach ($this->split_data as $value) {
					$model = new AR_ordernew_transaction;
					$model->order_id = $this->order_id;
					$model->merchant_id = $this->merchant_id;
					$model->client_id = $this->client_id;
					$model->payment_code = $value['payment_method'] ?? ''; 
					$model->trans_amount = $value['amount'] ?? 0; 
					$model->currency_code = $this->use_currency_code;
					$model->payment_reference = $payment_ref;
					$model->status = "paid";	
					$model->save();		
				}
			}			
		}

		CCacheData::add();
	}

	protected function afterDelete()
	{
		parent::afterDelete();
		
		
		if($this->scenario == "reset_cart"){			
			AR_ordernew_item::model()->deleteAll('order_id=:order_id',array(
			   ':order_id'=>$this->order_id,			   
			));
			
			AR_ordernew_additional_charge::model()->deleteAll('order_id=:order_id',array(
			   ':order_id'=>$this->order_id,			   
			));
			
			AR_ordernew_addons::model()->deleteAll('order_id=:order_id',array(
			   ':order_id'=>$this->order_id,			   
			));
			
			AR_ordernew_attributes::model()->deleteAll('order_id=:order_id',array(
			   ':order_id'=>$this->order_id,			   
			));
			
			AR_ordernew_meta::model()->deleteAll('order_id=:order_id',array(
			   ':order_id'=>$this->order_id,			   
			));			
		}

		CCacheData::add();
	}
	
	public function insertHistory()
	{
		$history = new AR_ordernew_history;
		$history->order_id = $this->order_id;
		$history->status = $this->status;
		$history->remarks = $this->remarks;
		$history->ramarks_trans = $this->ramarks_trans;
		$history->change_by = $this->change_by;
		$history->save();
	}
		
}
/*end class*/