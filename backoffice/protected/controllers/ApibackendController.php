<?php
class ApibackendController extends CommonServices
{		
	public function beforeAction($action)
	{								
		$method = Yii::app()->getRequest()->getRequestType();
		if($method=="PUT"){
			$this->data = Yii::app()->input->xssClean(json_decode(file_get_contents('php://input'), true));			
		} else $this->data = Yii::app()->input->xssClean($_POST);				
		return true;
	}
		
	public function actionorderList()
	{				
		$merchant_id = Yii::app()->merchant->merchant_id;
		$order_status = isset($this->data['order_status'])?$this->data['order_status']:'';
		$schedule = isset($this->data['schedule'])?$this->data['schedule']:'';
		$schedule = $schedule==1?true:false;
		$filter = isset($this->data['filter'])?$this->data['filter']:'';		
							
		try {			 
			$data = AOrders::getOrderAll($merchant_id, $order_status, $schedule , date("Y-m-d") , date("Y-m-d g:i:s a") , $filter );				
			$meta = AOrders::getOrderMeta( $data['all_order'] );	
			$status = COrders::statusList(Yii::app()->language);    	
    	    $services = COrders::servicesList(Yii::app()->language);
    	        	            	    
			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(
			  'data'=>$data['data'],
			  'total'=>$data['total'],
			  'meta'=>$meta,
			  'status'=>$status,
			  'services'=>$services,
			);						
		} catch (Exception $e) {
		   $this->msg[] = t($e->getMessage());
		}		
		$this->responseJson();
	}
	
	public function actionorderDetails()
	{	
		
		$this->data = Yii::app()->input->xssClean(json_decode(file_get_contents('php://input'), true));		
		$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';		
		$group_name = isset($this->data['group_name'])?$this->data['group_name']:'';		
		$filter_buttons = isset($this->data['filter_buttons'])?$this->data['filter_buttons']:'';
		$payload = isset($this->data['payload'])?$this->data['payload']:'';
		$modify_order = isset($this->data['modify_order'])?intval($this->data['modify_order']):'';
				
		try {

			$model_order = COrders::get($order_uuid);
			if(!empty($model_order->base_currency_code)){
				Price_Formatter::init($model_order->base_currency_code);
			}

			if($model_order->is_commission_applied==1){
				COrders::setCommissionapplied(true);
			}
						
			COrders::getContent($order_uuid,Yii::app()->language);
		    $merchant_id = COrders::getMerchantId($order_uuid);
		    $merchant_info = COrders::getMerchant($merchant_id,Yii::app()->language);
		    $items = COrders::getItems();			
		    $summary = COrders::getSummary();			    
		    $summary_total = COrders::getSummaryTotal();
			
		    $summary_changes = array(); $summary_transaction = array();
		    if($modify_order==1){
		       $summary_changes = COrders::getSummaryChanges();
		    } else $summary_transaction = COrders::getSummaryTransaction();			
		    		    
		    $order = COrders::orderInfo(Yii::app()->language, date("Y-m-d") );		
		    $order_type = isset($order['order_info'])?$order['order_info']['order_type']:'';
		    $payment_code = isset($order['order_info'])?$order['order_info']['payment_code']:'';
		    $client_id = $order?$order['order_info']['client_id']:0;		
		    $order_id = $order?$order['order_info']['order_id']:'';
			$origin_latitude = $order?$order['order_info']['latitude']:'';
			$origin_longitude = $order?$order['order_info']['longitude']:'';    
		    
			$delivery_direction = isset($merchant_info['restaurant_direction'])?$merchant_info['restaurant_direction']:'';
			if($order_type=="delivery"){
				$delivery_direction = isset($merchant_info['restaurant_direction'])?$merchant_info['restaurant_direction']:'';
				$delivery_direction.="&origin="."$origin_latitude,$origin_longitude";
			} 

			$order['order_info']['delivery_direction'] = $delivery_direction;			

		    $customer = COrders::getClientInfo($client_id);				    
		    $count = COrders::getCustomerOrderCount($client_id,$merchant_id);
			
			$customer = is_array($customer) ? $customer : [];
		    $customer['order_count'] = $count;
		    		    
		    $buttons = array(); $link_pdf = '';  $print_settings = array(); $payment_history = array();
		    
		    if(in_array('buttons',(array)$payload)){		 
		      if($filter_buttons){
		      	 $buttons = AOrders::getOrderButtons($group_name,$order_type);
		      } else $buttons = AOrders::getOrderButtons($group_name);		      
		    }
		    		    		   
		    if(in_array('print_settings',(array)$payload)){
			    $link_pdf = array(
			      'pdf_a4'=>Yii::app()->CreateUrl("print/pdf",array('order_uuid'=>$order_uuid,'size'=>"a4")),
			      'pdf_receipt'=>Yii::app()->CreateUrl("print/pdf",array('order_uuid'=>$order_uuid,'size'=>"thermal")),
			    );		    
			    $print_settings = AOrderSettings::getPrintSettings();
		    }
		    		    
		    if(in_array('payment_history',(array)$payload)){    
		       $payment_history = COrders::paymentHistory($order_id);			   			   
		    }
		    		    		    
			$credit_card_details = '';			
			if($payment_code=="ocr"){
				try {
					$credit_card_details = COrders::getCreditCard2($order_id);					
				} catch (Exception $e) {
					//
				}
		    }

			$driver_data = [];
			$driver_id = $order['order_info']['driver_id'];		
			if($driver_id>0){
				$now = date("Y-m-d");
				try {
					$driver = CDriver::getDriver($driver_id);
					$driver_data = [
						'uuid'=>$driver->driver_uuid,
						'driver_name'=>"$driver->first_name $driver->last_name",
						'phone_number'=>"+".$driver->phone_prefix.$driver->phone,
						'email_address'=>$driver->email,
						'photo_url'=>CMedia::getImage($driver->photo,$driver->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('driver')),
						'url'=>Yii::app()->createAbsoluteUrl("/merchantdriver/overview",['id'=>$driver->driver_uuid]),
						'active_task'=>CDriver::getCountActiveTask($driver->driver_id,$now)
					];
				} catch (Exception $e) {
					//
				}	
			}

			$merchant_zone = CMerchants::getListMerchantZone([$merchant_id]);
			if(!$zone_list = CommonUtility::getDataToDropDown("{{zones}}",'zone_id','zone_name')){
                $zone_list = [];
            }
			
			$order_status = isset($order['order_info'])?$order['order_info']['status']:'';
			$order['order_info']['show_assign_driver'] = false;
			$order['order_info']['can_reassign_driver'] = true;

			$options = OptionsTools::find(['self_delivery'],$merchant_id);
			$self_delivery = isset($options['self_delivery'])? ($options['self_delivery']==1?true:false) :false;
										
			if($order_type=="delivery" && $self_delivery){
				$status1 = COrders::getStatusTab2(['new_order','order_processing','order_ready']);
				$status2 = AOrderSettings::getStatus(array('status_delivered','status_completed','status_delivery_fail','status_failed'));
				$all_status = array_merge((array)$status1,(array)$status2);
				if(in_array($order_status,(array)$all_status)){
					$order['order_info']['show_assign_driver'] = true;
				}
				if(in_array($order_status,(array)$status2)){
					$order['order_info']['can_reassign_driver'] = false;
				}
			}

			$order_table_data = [];
			if($order_type=="dinein"){
				$order_table_data = COrders::orderMeta(['table_id','room_id','guest_number']);	
				$room_id = isset($order_table_data['room_id'])?$order_table_data['room_id']:0;							
				$table_id = isset($order_table_data['table_id'])?$order_table_data['table_id']:0;							
				try {
					$table_info = CBooking::getTableByID($table_id);
					$order_table_data['table_name'] = $table_info->table_name;
				} catch (Exception $e) {
					//$order_table_data['table_name'] = t("Unavailable");
				}				
				try {
					$room_info = CBooking::getRoomByID($room_id);					
					$order_table_data['room_name'] = $room_info->room_name;
				} catch (Exception $e) {
					//$order_table_data['room_name'] = t("Unavailable");
				}				
			}			

			$found_kitchen = Ckitchen::getByReference($order_id);		
			$kitchen_addon = CommonUtility::checkModuleAddon("Karenderia Kitchen App");	
		    		    			
		    $data = array(
		       'merchant'=>$merchant_info,
		       'order'=>$order,
		       'items'=>$items,
		       'summary'=>$summary,		
		       'summary_total'=>$summary_total,		       
		       'summary_changes'=>$summary_changes,
		       'summary_transaction'=>$summary_transaction,
		       'customer'=>$customer,
		       'buttons'=>$buttons,
		       'sold_out_options'=>AttributesTools::soldOutOptions(),
		       'link_pdf'=>$link_pdf,
		       'print_settings'=>$print_settings,
		       'payment_history'=>$payment_history,
			   'credit_card_details'=>$credit_card_details,
			   'driver_data'=>$driver_data,
			   'zone_list'=>$zone_list,
			   'merchant_zone'=>$merchant_zone,
			   'order_table_data'=>$order_table_data,
			   'kitchen_addon'=>$kitchen_addon,
			   'found_in_kitchen'=>$found_kitchen,			   
		    );		
		    
			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(			 		      
		       'data'=>$data,		      
		    );		  
		    		    
		    $model_order->is_view = 1;
		    $model_order->save();		  
		    		        
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   		    
		}			
		$this->responseJson();
	}
	
	public function actionupdateOrderStatus()
	{
		try {
						
			$uuid = isset($this->data['uuid'])?$this->data['uuid']:'';
			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';
			$rejetion_reason = isset($this->data['reason'])?$this->data['reason']:'';
							
			$status = AOrders::getOrderButtonStatus($uuid);
			$do_actions = AOrders::getOrderButtonActions($uuid);
			$tracking_stats = AR_admin_meta::getMeta(['tracking_status_process','tracking_status_in_transit']);						
			$tracking_status_process = isset($tracking_stats['tracking_status_process'])?$tracking_stats['tracking_status_process']['meta_value']:'accepted'; 			
			$tracking_status_delivering = isset($tracking_stats['tracking_status_in_transit'])?$tracking_stats['tracking_status_in_transit']['meta_value']:'delivery on its way'; 			
								
			$model = COrders::get($order_uuid);							
			
			if($do_actions=="reject_form"){
				$model->scenario = "reject_order";
			} else $model->scenario = "change_status";			
			
			if($model->status==$status){
				$this->msg = t("Order has the same status");
				$this->responseJson();
			}
						
			if($status==$tracking_status_process){				
				$model->order_accepted_at = CommonUtility::dateNowAdd();				
			}
			if($status==$tracking_status_delivering){
				$model->pickup_time = CommonUtility::dateNowAdd();				
			}
						
			$model->status = $status;			
			$model->remarks = $rejetion_reason;
			$model->change_by = Yii::app()->merchant->first_name;
			if($model->save()){
			   $this->code = 1;
			   $this->msg = t("Status Updated");
			   
			   if(!empty($rejetion_reason)){
			   	  COrders::savedMeta($model->order_id,'rejetion_reason',$rejetion_reason);
			   }
			   
			} else $this->msg = CommonUtility::parseModelErrorToString( $model->getErrors());
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}		
		$this->responseJson();
	}
	
	public function actioncreateRefund()
	{
		try {
			
			
			$uuid = isset($this->data['uuid'])?$this->data['uuid']:'';
			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';			
			
			$order = COrders::get($order_uuid);
			COrders::getContent($order_uuid,Yii::app()->language);
			$summary = COrders::getSummary();
			$summary_changes = COrders::getSummaryChanges();			
			$refund_due = isset($summary_changes['refund_due'])?floatval($summary_changes['refund_due']):0;

			$status = AOrders::getOrderButtonStatus($uuid);		
			$order->order_accepted_at = CommonUtility::dateNowAdd();
			$order->scenario = "change_status";			
						
			if($refund_due>0){
				$model = new AR_ordernew_summary_transaction;
				$model->scenario = "refund";
				$model->order = $order;
				$model->order_id = $order->order_id;
				$model->transaction_description = "Refund";
				$model->transaction_amount = floatval($refund_due);
				$model->save();			
				
				$order->status = $status;
				$order->save();
								
				$this->code = 1; $this->msg = "OK";					
			} else $this->msg = t("Amount to refund cannot be less than 0");
			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}		
		$this->responseJson();
	}
	
	public function actioncreateInvoice()
	{
		try {
									
			$uuid = isset($this->data['uuid'])?$this->data['uuid']:'';
			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';			
			
			$order = COrders::get($order_uuid);
			COrders::getContent($order_uuid,Yii::app()->language);
			$summary = COrders::getSummary();
			$summary_changes = COrders::getSummaryChanges();			
			$refund_due = isset($summary_changes['refund_due'])?floatval($summary_changes['refund_due']):0;

			$status = AOrders::getOrderButtonStatus($uuid);		
			$order->scenario = "change_status";							
						
			if($refund_due>0){
				$model = new AR_ordernew_summary_transaction;
				$model->scenario = "invoice";
				$model->order = $order;
				$model->order_id = $order->order_id;
				$model->transaction_type = "credit";
				$model->transaction_description = "Collect payment";
				$model->transaction_amount = floatval($refund_due);
				$model->status = "process";				
				$model->save();			
				
				$order->status = $status;
				//$order->save();
				
				$this->code = 1; $this->msg = "OK";					
			} else $this->msg = t("Amount to refund cannot be less than 0");
			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}		
		$this->responseJson();
	}
	
	public function actionlessOnAccount()
	{
		try {
						
			$uuid = isset($this->data['uuid'])?$this->data['uuid']:'';
			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';			
			
			$order = COrders::get($order_uuid);
			COrders::getContent($order_uuid,Yii::app()->language);
			$summary = COrders::getSummary();
			$summary_changes = COrders::getSummaryChanges();			
			$refund_due = isset($summary_changes['refund_due'])?floatval($summary_changes['refund_due']):0;

			$status = AOrders::getOrderButtonStatus($uuid);		
			$order->scenario = "change_status";		
			
			$card_id = CWallet::getCardID( Yii::app()->params->account_type['merchant'] , $order->merchant_id );
			$balance = CWallet::getBalance($card_id);
			$balance = Price_Formatter::convertToRaw($balance);
			$refund_due = Price_Formatter::convertToRaw($refund_due);
						
			if($balance<$refund_due){
				$this->msg = t("You don't have enough balance in your account. please load your account to process this order.");
			   	$this->responseJson();			   	
            }
									
			if($refund_due>0){
				$model = new AR_ordernew_summary_transaction;
				$model->scenario = "less_account";
				$model->card_id = $card_id;
				$model->order = $order;
				$model->order_id = $order->order_id;
				$model->transaction_type = "debit";
				$model->transaction_description = "Less on account";
				$model->transaction_amount = floatval($refund_due);
				$model->status = "process";
				$model->save();			
				
				$order->status = $status;
				$order->save();
				
				$this->code = 1; $this->msg = "OK";		
			} else $this->msg = t("Amount to less cannot be less than 0");
			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}		
		$this->responseJson();
	}
	
	public function actionlesscashonaccount()
	{
		try {
			
			$uuid = isset($this->data['uuid'])?$this->data['uuid']:'';
			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';			
			
			$order = COrders::get($order_uuid);
			$status = AOrders::getOrderButtonStatus($uuid);		
			$order->scenario = "change_status";		
			
			$amount = floatval($order->commission);
			
			$card_id = CWallet::getCardID( Yii::app()->params->account_type['merchant'] , $order->merchant_id );
			$balance = CWallet::getBalance($card_id);
			$balance = Price_Formatter::convertToRaw($balance);
			
			// if($balance<$amount){
			// 	$this->msg = t("You don't have enough balance in your account. please load your account to process this order.");
			//    	$this->responseJson();			   	
            // }
            
            $card_admin_id = CWallet::getCardID( Yii::app()->params->account_type['admin'] , 0);            
            
            $params = array(
			  'merchant_id'=>$order->merchant_id,					  
			  'transaction_description'=>"Payment to order #{{order_id}}",
			  'transaction_description_parameters'=>array('{{order_id}}'=>$order->order_id),					  
			  'transaction_type'=>"debit",
			  'transaction_amount'=>floatval($amount),
			  'meta_name'=>"order",
			  'meta_value'=>$order->order_id,
			  'status'=>"paid",
			  'orig_transaction_amount'=>floatval($amount),
			  'merchant_base_currency'=>$order->base_currency_code,
			  'admin_base_currency'=>$order->admin_base_currency,
			  'exchange_rate_merchant_to_admin'=>$order->exchange_rate_merchant_to_admin,
			  'exchange_rate_admin_to_merchant'=>$order->exchange_rate_admin_to_merchant,
			);						
			CWallet::inserTransactions($card_id,$params);	
			
			$order->status = $status;
			$order->save();
             
			$this->code = 1; $this->msg = "OK";
                       
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}		
		$this->responseJson();
	}
	
	public function actionupdateOrderStatusManual()
	{		
		try {
			$stats_id = isset($this->data['stats_id'])?intval($this->data['stats_id']):'';
			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';
			
			$model = COrders::get($order_uuid);	
			$model->scenario = "change_status";
			
			$model_status = AR_status::model()->find("stats_id=:stats_id",array(
			 ':stats_id'=>intval($stats_id)
			));
			if($model_status){
				$status = $model_status->description;
				
				if($model->status==$status){
					$this->msg = t("Order has the same status");
					$this->responseJson();
				}
				
				$model->status = $status;				
				if($model->save()){
					$this->code = 1;
			        $this->msg = t("Status Updated");
				} else $this->msg = CommonUtility::parseError( $model->getErrors());
			} else $this->msg =  t("Status not found");
			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}		
		$this->responseJson();
	}
	
	public function actioncancelOrder()
	{		
		try {
												
			$model = AR_admin_meta::model()->find('meta_name=:meta_name', 
			  array(':meta_name'=>'status_cancel_order')
			);			
			if($model){				
				$status_cancelled = $model->meta_value ;
			} else $status_cancelled = 'cancelled';

					
			$reason = isset($this->data['reason'])?trim($this->data['reason']):'';
			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';
			$model = COrders::get($order_uuid);	
			$model->scenario = "cancel_order";
			
			if($model->status==$status_cancelled){
				$this->msg = t("Order has the same status");
				$this->responseJson();
			}
			
			/*CHECK IF HAS EXISTING REFUND*/
			//COrders::getExistingRefund($model->order_id);
							
			$model->status = $status_cancelled;
			$model->remarks = $reason;
			
			if($model->save()){
			   $this->code = 1;
			   $this->msg = t("Order is cancelled");			   
			   if(!empty($reason)){
			   	  COrders::savedMeta($model->order_id,'rejetion_reason',$reason);
			   }			   
			} else $this->msg = CommonUtility::parseError( $model->getErrors());
			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();
	}
	
	public function actionorderRejectionList()
	{
		try {
			$data = AOrders::rejectionList('rejection_list', Yii::app()->language );		
			$this->code = 1;
			$this->msg = "ok";
			$this->details = $data;
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}		
		$this->responseJson();	
	}
	
	public function actiongetOrderStatusList()
	{		
		if ($data = AttributesTools::getOrderStatusList(Yii::app()->language)){
			$this->code =1; $this->msg = "ok";
			$this->details = $data;
		} else $this->msg = t("No results");
		$this->responseJson();	
	}
	
	public function actiongetDelayedMinutes()
	{
		$times = AttributesTools::delayedMinutes();
		$this->code = 1;
		$this->msg = "ok";
		$this->details = $times;
		$this->responseJson();	
	}
	
	public function actionsetDelayToOrder()
	{		
		try {
			
			$time_delay = isset($this->data['time_delay'])?intval($this->data['time_delay']):'';
			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';
			$model = COrders::get($order_uuid);	
			$model->scenario = "delay_order";
			
			//$model->status = "delayed";
			$model->remarks = "Order is delayed by [mins]min(s)";
			$model->ramarks_trans = json_encode(array('[mins]'=>$time_delay));

			COrders::savedMeta($model->order_id,'delayed_order', t($model->remarks,array('[mins]'=>$time_delay)) );			   	   
			COrders::savedMeta($model->order_id,'delayed_order_mins',$time_delay );
			
			if($model->save()){
			   $this->code = 1;
			   $this->msg = t("Customer is notified about the delayed.");					   			   			   			   	   
			} else $this->msg = CommonUtility::parseError( $model->getErrors());
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();
	}
	
	public function actiongetOrderHistory()
	{
		try {			
			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';			
			$data = AOrders::getOrderHistory($order_uuid);
			$order_status = AttributesTools::getOrderStatus(Yii::app()->language);
			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(
			  'data'=>$data,
			  'order_status'=>$order_status
			);						
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		   
		}	
		$this->responseJson();
	}
	
	public function actionitemChanges()
	{		
		try {
						
			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';			
			$item_row = isset($this->data['item_row'])?$this->data['item_row']:'';		
			$out_stock_options = isset($this->data['out_stock_options'])?intval($this->data['out_stock_options']):0;	
			$item_changes = isset($this->data['item_changes'])?$this->data['item_changes']:'';		
								
			$model = COrders::get($order_uuid);			
			
			$items = AR_ordernew_item::model()->find("item_row=:item_row",array(
			 ':item_row'=>$item_row
			));		
												
			$refund_item_details = array();
			if($item_changes=="refund" || $item_changes=="out_stock"){									
				$refund_item_details = COrders::getRefundItemTotal($item_changes,$model->tax,$items->order_id,$items->item_row);				
			}
									
			if($items){				
				$items->scenario = $item_changes;
				$items->item_changes = $item_changes;
				$items->order_uuid = $order_uuid; 		
				$items->refund_item_details = $refund_item_details;	
				if($items->delete()){
					$this->code = 1;
			        $this->msg = t("Succesful");			        
				} else $this->msg = CommonUtility::parseError( $model->getErrors());
			} else $this->msg = t("Item row not found");			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();
	}
	
	public function actionadditionalCharge()
	{
		try {			
			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';			
			$item_row = isset($this->data['item_row'])?$this->data['item_row']:'';		
			$additional_charge = isset($this->data['additional_charge'])?$this->data['additional_charge']:'';		
			$additional_charge_name = isset($this->data['additional_charge_name'])?$this->data['additional_charge_name']:'';
			$additional_charge = floatval($additional_charge);
			
			$additional_charge_name = !empty($additional_charge_name)?$additional_charge_name:'Additional charge applied';
						
			$model = COrders::get($order_uuid);	
			
			if($additional_charge>0){
				$item = new AR_ordernew_additional_charge;
				$item->order_id = $model->order_id;
				$item->item_row = $item_row;								
				$item->charge_name = $additional_charge_name;				
				$item->additional_charge = $additional_charge;
				$item->order_uuid = $order_uuid;
				if($item->save()){
					$this->code = 1;
			        $this->msg = t("Succesful");			        
				} else $this->msg = CommonUtility::parseError( $model->getErrors());
			} else $this->msg = t("Additional charge must be greater than zero");
						
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();
	}
	
	public function actionupdateOrderSummary()
	{
		try {
		   		   
		   $order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';		   
		   COrders::updateSummary($order_uuid);
		   $this->code = 1;
		   $this->msg = "OK";
		   
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();
	}
	
	public function actiongetCategory()
	{
		try {
					   
		   $merchant_id = isset($this->data['merchant_id'])?$this->data['merchant_id']:0;		   
		   $category = CMerchantMenu::getCategory(intval($merchant_id),Yii::app()->language);				   
		   $data = array(
		     'category'=>$category,		     
		   );		   		   
		   $this->code = 1; $this->msg = "OK";
		   $this->details = array(		     		    
		     'data'=>$data
		   );		   
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();	
	}
	
	public function actioncategoryItem()
	{
		try {
											
			$merchant_id = isset($this->data['merchant_id'])?$this->data['merchant_id']:0;	
		    $cat_id = isset($this->data['cat_id'])?intval($this->data['cat_id']):0;
		    $page  = isset($this->data['page'])?(integer)$this->data['page']:0;
		    $search = isset($this->data['q'])?trim($this->data['q']):'';
		    $items = array();		   
		    $items  = CMerchantMenu::CategoryItem(intval($merchant_id),$cat_id,$search,$page,Yii::app()->language);			
		    $this->code = 1; $this->msg = "OK";
		    $this->details = $items;    
		    		    
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		  
		}
		$this->responseJson();	
	}
	
	public function actiongetMenuItem()
	{		
		try  {
			
			$merchant_id = isset($this->data['merchant_id'])?(integer)$this->data['merchant_id']:'';
			$item_uuid = isset($this->data['item_uuid'])?trim($this->data['item_uuid']):'';
			$cat_id = isset($this->data['cat_id'])?(integer)$this->data['cat_id']:0;

			// CHECK IF MERCHANT HAS DIFFERENT TIMEZONE
			$options_merchant = OptionsTools::find(['merchant_timezone'],$merchant_id);
			$merchant_timezone = isset($options_merchant['merchant_timezone'])?$options_merchant['merchant_timezone']:'';			
			if(!empty($merchant_timezone)){
				Yii::app()->timezone = $merchant_timezone;
			}
			
			$items = CMerchantMenu::getMenuItem($merchant_id,$cat_id,$item_uuid,Yii::app()->language);
			$addons = CMerchantMenu::getItemAddonCategory($merchant_id,$item_uuid,Yii::app()->language);
			$addon_items = CMerchantMenu::getAddonItems($merchant_id,$item_uuid,Yii::app()->language);	
			$meta = CMerchantMenu::getItemMeta($merchant_id,$item_uuid);
			$meta_details = CMerchantMenu::getMeta($merchant_id,$item_uuid,Yii::app()->language);	

			$items_not_available = CMerchantMenu::getItemAvailability($merchant_id,date("w"),date("H:h:i"));
			$category_not_available = CMerchantMenu::getCategoryAvailability($merchant_id,date("w"),date("H:h:i"));			

			if(in_array($items['item_id'],(array)$items_not_available)){
				$items['available']=false;
			} else {
				$items['available'] = in_array($items['cat_id'],(array)$category_not_available)? false : true;
			}			
							
			$data = array(
			  'items'=>$items,
			  'addons'=>$addons,
			  'addon_items'=>$addon_items,
			  'meta'=>$meta,
			  'meta_details'=>$meta_details,			  
			);			
			
			$this->code = 1; $this->msg = "ok";
		    $this->details = array(		      
		      'sold_out_options'=>AttributesTools::soldOutOptions(),
		      'data'=>$data
		    );		    		    
		    
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());
		   $this->details = array(
		      'data'=>array()
		    );		    	   
		}
		$this->responseJson();	
	}
	
	public function actionaddCartItems()
	{			
		$cart_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';
		$order_uuid = $cart_uuid;
		$cart_row = CommonUtility::createUUID("{{ordernew_item}}",'item_row');
		
		$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';		
		$merchant_id = isset($this->data['merchant_id'])?(integer)$this->data['merchant_id']:'';
		$cat_id = isset($this->data['cat_id'])?(integer)$this->data['cat_id']:'';
		$item_token = isset($this->data['item_token'])?$this->data['item_token']:'';
		$old_item_token = isset($this->data['old_item_token'])?$this->data['old_item_token']:'';
		$item_row = isset($this->data['item_row'])?$this->data['item_row']:'';
		$item_size_id = isset($this->data['item_size_id'])?(integer)$this->data['item_size_id']:0;
		$item_qty = isset($this->data['item_qty'])?(integer)$this->data['item_qty']:0;
		$special_instructions = isset($this->data['special_instructions'])?$this->data['special_instructions']:'';
		$if_sold_out = isset($this->data['if_sold_out'])?$this->data['if_sold_out']:'';		
		$inline_qty = isset($this->data['inline_qty'])?(integer)$this->data['inline_qty']:0;
				
		if($old_item_token==$item_token){
			$this->msg = t("Cannot replace this item with the same item.");
			$this->responseJson();	
		}
				
		$addons = array();
		$item_addons = isset($this->data['item_addons'])?$this->data['item_addons']:'';
		if(is_array($item_addons) && count($item_addons)>=1){
			foreach ($item_addons as $val) {				
				$multi_option = isset($val['multi_option'])?$val['multi_option']:'';
				$subcat_id = isset($val['subcat_id'])?(integer)$val['subcat_id']:0;
				$sub_items = isset($val['sub_items'])?$val['sub_items']:'';
				$sub_items_checked = isset($val['sub_items_checked'])?(integer)$val['sub_items_checked']:0;				
				
				if($multi_option=="one" && $sub_items_checked>0){
									
					$addon_price = 0;	
					foreach ($sub_items as $sub_items_items) {
						if($sub_items_items['sub_item_id']==$sub_items_checked){
							$addon_price = $sub_items_items['price'];
						}
					}
					
					$addons[] = array(
					  'cart_row'=>$cart_row,
					  'cart_uuid'=>$cart_uuid,
					  'subcat_id'=>$subcat_id,
					  'sub_item_id'=>$sub_items_checked,					 
					  'qty'=>1,
					  'price'=>$addon_price,
					  'multi_option'=>$multi_option,
					);
				} else {
					foreach ($sub_items as $sub_items_val) {
						if($sub_items_val['checked']==1){							
							$addons[] = array(
							  'cart_row'=>$cart_row,
							  'cart_uuid'=>$cart_uuid,
							  'subcat_id'=>$subcat_id,
							  'sub_item_id'=>isset($sub_items_val['sub_item_id'])?(integer)$sub_items_val['sub_item_id']:0,							  
							  'qty'=>isset($sub_items_val['qty'])?(integer)$sub_items_val['qty']:0,
							  'price'=>isset($sub_items_val['price'])?floatval($sub_items_val['price']):0,
							  'multi_option'=>$multi_option,
							);
						}
					}
				}
			}
		}
		
		
		$attributes = array();
		$meta = isset($this->data['meta'])?$this->data['meta']:'';
		if(is_array($meta) && count($meta)>=1){
			foreach ($meta as $meta_name=>$metaval) {				
				if($meta_name!="dish"){
					foreach ($metaval as $val) {
						if($val['checked']>0){	
							$attributes[]=array(
							  'cart_row'=>$cart_row,
							  'cart_uuid'=>$cart_uuid,
							  'meta_name'=>$meta_name,
							  'meta_id'=>$val['meta_id']
							);
						}
					}
				}
			}
		}
		
				
		try {
			
			$model = COrders::get($order_uuid);
			
			$criteria=new CDbCriteria();	
	        $criteria->alias = "a";
	        $criteria->select = "a.item_id,a.item_token,
	        b.item_size_id, b.price as item_price, b.discount, b.discount_type, b.discount_start,
	        b.discount_end,
	        (
		     select count(*) from {{view_item_lang_size}}
		     where item_size_id = b.item_size_id 		  
		     and CURDATE() >= discount_start and CURDATE() <= discount_end
		    ) as discount_valid
	        
	        ";
	        $criteria->condition = "a.merchant_id = :merchant_id AND a.item_token=:item_token
	        AND b.item_size_id=:item_size_id
	        ";
	        $criteria->params = array ( 
	          ':merchant_id'=>$merchant_id,
	          ':item_token'=>$item_token,
	          ':item_size_id'=>$item_size_id
	        );
	        $criteria->mergeWith(array(
			  'join'=>'LEFT JOIN {{item_relationship_size}} b ON a.item_id = b.item_id',				
		    ));
	        $item = AR_item::model()->find($criteria);	      
	        
	        if(!$item){
	        	$this->msg = t("Price is not valid");
	        	$this->responseJson();		
            }
                                    
	        $scenario = 'update_cart';
	        
			$items = array(
			  'order_uuid'=>$order_uuid,
			  'order_id'=>$model->order_id,
			  'merchant_id'=>$merchant_id,
			  'cart_row'=>$cart_row,
			  'cart_uuid'=>$cart_uuid,
			  'cat_id'=>$cat_id,
			  'item_id'=>$item->item_id,
			  'item_token'=>$item_token,
			  'item_size_id'=>$item_size_id,
			  'qty'=>$item_qty,
			  'special_instructions'=>$special_instructions,
			  'if_sold_out'=>$if_sold_out,
			  'addons'=>$addons,
			  'attributes'=>$attributes,
			  'inline_qty'=>$inline_qty,
			  'price'=>floatval($item->item_price),
			  'discount'=>$item->discount_valid>0?$item->discount:0,
			  'discount_type'=>$item->discount_valid>0?$item->discount_type:'',
			  'item_row'=>$item_row,
			  'old_item_token'=>$old_item_token,
			  'scenario'=>$scenario
			);	
			
						
			/*GET TAX*/
			$tax_settings = array(); $tax_use = array();
			try {
				$tax_settings = CTax::getSettings($merchant_id);							
				if($tax_settings['tax_type']=="multiple"){					
					$tax_use = CTax::getItemTaxUse($merchant_id,$item->item_id);
                } else $tax_use = isset($tax_settings['tax']) ? $tax_settings['tax'] : '';			   	
			} catch (Exception $e) {					
				 //echo $e->getMessage();
			}
			$items['tax_use'] = $tax_use;
			
			COrders::add($items);		
			COrders::updateServiceFee($order_uuid,$transaction_type);
			
			$this->code = 1 ; $this->msg = T("Item added to order");			
	        $this->details = array( 
	         'order_uuid'=>$order_uuid
	        );		 	        	        
			
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());
		   $this->details = array(
		      'data'=>array()
		    );		    	   
		}
		$this->responseJson();		
	}
	
	public function actionupdateOrderDeliveryInformation()
	{
				
		try {
					   
		   $order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';
		   $customer_name = isset($this->data['customer_name'])?$this->data['customer_name']:'';
		   $contact_number = isset($this->data['contact_number'])?$this->data['contact_number']:'';

		   $delivery_address = isset($this->data['delivery_address'])?$this->data['delivery_address']:'';
		   $address1 = isset($this->data['address1'])?$this->data['address1']:'';
		   $location_name = isset($this->data['location_name'])?$this->data['location_name']:'';
		   $address2 = isset($this->data['address2'])?$this->data['address2']:'';
		   $postal_code = isset($this->data['postal_code'])?$this->data['postal_code']:'';
		   $company = isset($this->data['company'])?$this->data['company']:'';
		   $address_format_use = isset($this->data['address_format_use'])?$this->data['address_format_use']:1;
		   $address_format_use = !empty($address_format_use)?$address_format_use:1;

		   $latitude = isset($this->data['latitude'])?$this->data['latitude']:'';
		   $longitude = isset($this->data['longitude'])?$this->data['longitude']:'';
		   		   
		   $model = COrders::get($order_uuid);
		   $order_type = $model->service_code;		   
		   
		   $error = array();
		   if(empty($customer_name)){
		   	  $error[] = t("Customer name is requied");
		   }
		   if(empty($contact_number)){
		   	  $error[] = t("Customer contact number is requied");
		   }
		   
		   switch ($order_type) {
		   	case "delivery":
		   	    if(empty($delivery_address)){
			   	   $error[] = t("Delivery address is requied");
			    }
			    if(empty($latitude)){
			   	   $error[] = t("Delivery coordinates is requied");
			    }
			    if(empty($longitude)){
			   	   $error[] = t("Delivery coordinates is requied");
			    }
		   		break;
		   
		   	default:
		   		break;
		   }
		   
		   if(is_array($error) && count($error)>=1){		   	  
		   	  $this->msg = "Error";
		   	  $this->details = $error;
		   	  $this->responseJson();
		   }
		   
		   COrders::savedAttributes($model->order_id,'customer_name',$customer_name);
		   COrders::savedAttributes($model->order_id,'contact_number',$contact_number);

		   COrders::savedAttributes($model->order_id,'formatted_address',$delivery_address);
		   COrders::savedAttributes($model->order_id,'address1',$address1);
		   COrders::savedAttributes($model->order_id,'location_name',$location_name);
		   COrders::savedAttributes($model->order_id,'address2',$address2);
		   COrders::savedAttributes($model->order_id,'postal_code',$postal_code);
		   COrders::savedAttributes($model->order_id,'company',$company);

		   COrders::savedAttributes($model->order_id,'latitude',$latitude);
		   COrders::savedAttributes($model->order_id,'longitude',$longitude);
		   
		   $model->formatted_address = $delivery_address;
		   $model->save();
		   
		   $this->code = 1;
		   $this->msg = t("Order Information updated");
		
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();	
	}
	
	public function actiongetCustomerDetails()
	{
		try {
					   
		   $client_id = isset($this->data['client_id'])?intval($this->data['client_id']):0;
		   //$merchant_id = Yii::app()->merchant->merchant_id;		   
		   $merchant_id = isset($this->data['merchant_id'])?intval($this->data['merchant_id']):0;
		   		   
		   $addresses = array();		   
		   
		   if($data = COrders::getClientInfo($client_id)){
			   try {
			      $addresses = ACustomer::getAddresses($client_id);
			   } catch (Exception $e) {
			   	  //
			   }			   
			   $this->code = 1;
			   $this->msg = "OK";
			   $this->details = array(
			     'customer'=>$data,
			     'block_from_ordering'=>ACustomer::isBlockFromOrdering($client_id,$merchant_id),
			     'addresses'=>$addresses,
			   );		  		   
		   } else $this->msg = t("Client information not found");
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();	
	}
	
	public function actiongetCustomerOrders()
	{
		
		$data = array();		
		//$merchant_id = Yii::app()->merchant->merchant_id;
		$merchant_id = isset($this->data['merchant_id'])?intval($this->data['merchant_id']):0;
		$client_id = isset($this->data['client_id'])?$this->data['client_id']:0;
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?$this->data['order'][0]:'';	
				
		$sortby = "order_id"; $sort = 'DESC';
		if(array_key_exists($order['column'],(array)$columns)){			
			$sort = $order['dir'];
			$sortby = $columns[$order['column']]['data'];
		}
		
		
		$page = intval($page)/intval($length);		
		
		$initial_status = AttributesTools::initialStatus();
		$status = COrders::statusList(Yii::app()->language);		
					
		$criteria=new CDbCriteria();	
		$criteria->alias = "a";
		$criteria->select="order_id,order_uuid,total,status";
		$criteria->condition = "merchant_id=:merchant_id AND client_id=:client_id ";
		$criteria->params  = array(
		  ':merchant_id'=>intval($merchant_id),
		  ':client_id'=>intval($client_id)
		);
		$criteria->order = "$sortby $sort";
		
		if (is_string($search) && strlen($search) > 0){
		   $criteria->addSearchCondition('order_id', $search );
		   $criteria->addSearchCondition('status', $search , true , 'OR' );
		}
		$criteria->addNotInCondition('status', array($initial_status) );
				
		$count = AR_ordernew::model()->count($criteria); 
		$pages=new CPagination($count);
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        
        $models = AR_ordernew::model()->findAll($criteria);
        
$buttons = <<<HTML
<div class="btn-group btn-group-actions small" role="group">
 <a href="{{view_order}}" target="_blank" class="btn btn-light tool_tips"><i class="zmdi zmdi-eye"></i></a>
 <a href="{{print_pdf}}" target="_blank"  class="btn btn-light tool_tips"><i class="zmdi zmdi-download"></i></a>
</div>
HTML;
        foreach ($models as $val) {        	        	
        	$status_html = $val->status;
        	if(array_key_exists($val->status,(array)$status)){
        		$new_status = $status[$val->status]['status'];
        		$inline_style="background:".$status[$val->status]['background_color_hex'].";";
        		$inline_style.="color:".$status[$val->status]['font_color_hex'].";";
        		$status_html = <<<HTML
<span class="badge" style="$inline_style" >$new_status</span>
HTML;
        	}
        	        	
        	$_buttons = str_replace("{{view_order}}",
        	Yii::app()->createUrl('/orders/view',array('order_uuid'=>$val->order_uuid))
        	,$buttons);
        	
        	$_buttons = str_replace("{{print_pdf}}",
        	Yii::app()->createUrl('/print/pdf',array('order_uuid'=>$val->order_uuid))
        	,$_buttons);
        	
        	$data[]=array(
        	 'order_id'=>$val->order_id,
        	 'total'=>Price_Formatter::formatNumber($val->total),
        	 'status'=>$status_html,
        	 'order_uuid'=>$_buttons
        	);
        }        
                
		$datatables = array(
		  'draw'=>intval($draw),
		  'recordsTotal'=>intval($count),
		  'recordsFiltered'=>intval($count),
		  'data'=>$data
		);
		
		/*header('Content-type: application/json');
		echo CJSON::encode($datatables);
		Yii::app()->end();*/
		$this->responseTable($datatables);
	}
	
	public function actionblockCustomer()
	{
		try {
						
			$meta_name = 'block_customer';
						
			//$merchant_id = Yii::app()->merchant->merchant_id;
			$merchant_id = isset($this->data['merchant_id'])?intval($this->data['merchant_id']):0;
			$client_id = isset($this->data['client_id'])?$this->data['client_id']:0;
			$block = isset($this->data['block'])?$this->data['block']:0;
			
			$model = AR_merchant_meta::model()->find("merchant_id=:merchant_id AND 
			meta_name=:meta_name AND meta_value=:meta_value",array(
			 ':merchant_id'=>intval($merchant_id),
			 ':meta_name'=>$meta_name,
			 ':meta_value'=>$client_id
			));
			
			if($model){
				if($block!=1){
					$model->delete();
				}
			} else {				
				if($block==1){
					$model = new AR_merchant_meta;
					$model->merchant_id = $merchant_id;
					$model->meta_name = $meta_name;
					$model->meta_value = $client_id;
					$model->save();
				}
			}
			
			$this->code = 1;
			$this->msg = t("Successful");
			$this->details = intval($block);
			
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		
		}	
		$this->responseJson();	
	}
	
	public function actiongetCustomerSummary()
	{
		try {		  
					    			
		    $client_id = isset($this->data['client_id'])?$this->data['client_id']:0;
		    //$merchant_id = Yii::app()->merchant->merchant_id;
		    $merchant_id = isset($this->data['merchant_id'])?intval($this->data['merchant_id']):0;

		    $initial_status = AttributesTools::initialStatus();			    
		    
		    $not_in_status = AOrderSettings::getStatus(array('status_cancel_order','status_rejection'));	
			array_push($not_in_status,$initial_status); 			
		    $orders = ACustomer::getOrdersTotal($client_id,$merchant_id,array(),$not_in_status);
		    
		    $status_cancel = AOrderSettings::getStatus(array('status_cancel_order'));		    
		    $order_cancel = ACustomer::getOrdersTotal($client_id,$merchant_id,$status_cancel);
		    
		    $status_delivered = AOrderSettings::getStatus(array('status_delivered','status_completed'));			
			
		    $total = ACustomer::getOrderSummary($client_id,$merchant_id,$status_delivered);
		    $total_refund = ACustomer::getOrderRefundSummary($client_id,$merchant_id,AttributesTools::refundStatus());
		    		    
		    $data = array(
		     'orders'=>$orders,
		     'order_cancel'=>$order_cancel,
		     'total'=>Price_Formatter::formatNumberNoSymbol($total),
		     'total_refund'=>Price_Formatter::formatNumberNoSymbol($total_refund),
		     'price_format'=>AttributesTools::priceUpFormat()
		    );
		    
		    $this->code = 1;
		    $this->msg = "OK";
		    $this->details = $data;		    
		    
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		
		}	
		$this->responseJson();	
	}
	
	public function actiongetGroupname()
	{
		try {
						
			$group_name=''; $modify_order = false;	$filter_buttons = false;		
		    $order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';			    
		    
		    $model = COrders::get($order_uuid);		    
			try {
				$group_name = AOrderSettings::getGroup($model->status);		    
			} catch (Exception $e) {
			}		    
			
		    if($group_name=="new_order"){
				$modify_order = true;
			}
			if($group_name=="order_ready"){
				$filter_buttons = true;
			}
			
			$manual_status = isset(Yii::app()->params['settings']['enabled_manual_status'])?Yii::app()->params['settings']['enabled_manual_status']:false;
						
			$data = array(
			  'client_id'=>$model->client_id,
			  'merchant_id'=>$model->merchant_id,
			  'group_name'=>$group_name,
			  'manual_status'=>$manual_status,
			  'modify_order'=>$modify_order,
			  'filter_buttons'=>$filter_buttons
			);
						
			$this->code = 1;
			$this->msg = "OK";
			$this->details = $data;			

		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		
		}	
		$this->responseJson();		
	}
	
	public function actiongetOrdersCount()
	{
		try {
			
			$merchant_id = Yii::app()->merchant->merchant_id;
			$new_order = AOrders::getOrderTabsStatus('new_order');	
			$order_processing = AOrders::getOrderTabsStatus('order_processing');	
			$order_ready = AOrders::getOrderTabsStatus('order_ready');
			$completed_today = AOrders::getOrderTabsStatus('completed_today');			

			
			$status_scheduled = (array) $new_order;				
			
			if($order_processing){				
				foreach ($order_processing as $order_processing_val) {
					array_push($status_scheduled,$order_processing_val);
				}
			}
													
			$new = AOrders::getOrderCountPerStatus($merchant_id,$new_order,date("Y-m-d"));
			$processing = AOrders::getOrderCountPerStatus($merchant_id,$order_processing,date("Y-m-d"));
			$ready = AOrders::getOrderCountPerStatus($merchant_id,$order_ready,date("Y-m-d"));
			$completed = AOrders::getOrderCountPerStatus($merchant_id,$completed_today,date("Y-m-d"));
			$scheduled = AOrders::getOrderCountSchedule($merchant_id,$status_scheduled,date("Y-m-d"));
			$all_orders = AOrders::getAllOrderCount($merchant_id);
			
			$not_viewed = AOrders::OrderNotViewed($merchant_id,$new_order,date("Y-m-d"));			
			
			$data = array(
			  'new_order'=>$new,
			  'order_processing'=>$processing,
			  'order_ready'=>$ready,
			  'completed_today'=>$completed,
			  'scheduled'=>$scheduled,
			  'all_orders'=>$all_orders,
			  'not_viewed'=>$not_viewed,
			);
			
			$this->code = 1;
			$this->msg = "OK";
			$this->details = $data;
					
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		
		}	
		$this->responseJson();		
	}
	
	public function actiontransactionHistory()
	{
		$data = array(); $card_id = 0;	
		try {	
		    $card_id = CWallet::getCardID(Yii::app()->params->account_type['merchant'],Yii::app()->merchant->merchant_id);	
		} catch (Exception $e) {
		    // do nothing    
		}	
				
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
		
		$date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
		$date_end = isset($this->data['date_end'])?$this->data['date_end']:'';
		$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';		
				
		$sortby = "transaction_id"; $sort = 'DESC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = intval($page)/intval($length);		
		$criteria=new CDbCriteria();
		$criteria->condition = "card_id=:card_id";
		$criteria->params  = array(
		  ':card_id'=>intval($card_id),		  
		);
		
		if(!empty($date_start) && !empty($date_end)){
			$criteria->addBetweenCondition("DATE_FORMAT(transaction_date,'%Y-%m-%d')", $date_start , $date_end );
		}
		if(is_array($transaction_type) && count($transaction_type)>=1){
			$criteria->addInCondition('transaction_type',(array) $transaction_type );
		}		
		
		$criteria->order = "$sortby $sort";
		$count = AR_wallet_transactions::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        
        $models = AR_wallet_transactions::model()->findAll($criteria);
        if($models){
        	foreach ($models as $item) {
        		$description = Yii::app()->input->xssClean($item->transaction_description);        		
        		$parameters = json_decode($item->transaction_description_parameters,true);        		
        		if(is_array($parameters) && count($parameters)>=1){        			
        			$description = t($description,$parameters);
        		}
        		
        		$transaction_amount = Price_Formatter::formatNumber($item->transaction_amount);        		
        		switch ($item->transaction_type) {
        			case "debit":
        			case "payout":
        				$transaction_amount = "(".Price_Formatter::formatNumber($item->transaction_amount).")";
        				break;        		        			
        		}
        		
        		
$trans_html = <<<HTML
<p class="m-0 $item->transaction_type">$transaction_amount</p>
HTML;
        		
        		
        		$data[]=array(
        		  'transaction_date'=>Date_Formatter::dateTime($item->transaction_date),
        		  'transaction_description'=>$description,
        		  'transaction_amount'=>$trans_html,
        		  'running_balance'=>Price_Formatter::formatNumber($item->running_balance),
        		);
        	}
        }
        
        $datatables = array(
		  'draw'=>intval($draw),
		  'recordsTotal'=>intval($count),
		  'recordsFiltered'=>intval($count),
		  'data'=>$data
		);
		
		/*header('Content-type: application/json');
		echo CJSON::encode($datatables);
		Yii::app()->end();*/
		$this->responseTable($datatables);
	}		
	
	public function actiongetGetMerchantBalance()
	{
		try {									
			$card_id = CWallet::getCardID( Yii::app()->params->account_type['merchant'] , Yii::app()->merchant->merchant_id );
			$balance = CWallet::getBalance($card_id);
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());
		   $balance = 0;		
		}	
		
		$this->code = 1;
		$this->msg = "OK";
		$this->details = array(
		  'balance_pretty'=>Price_Formatter::formatNumberNoSymbol($balance),
		  'balance'=>Price_Formatter::convertToRaw($balance,2,false,""),
		  'price_format'=>AttributesTools::priceUpFormat()
		);
		
		$this->responseJson();		
    }
    
    public function actionwithdrawalsHistory()
    {
    	$data = array();		
				
		$merchant_id = Yii::app()->merchant->merchant_id;
		$card_id = CWallet::getCardID( Yii::app()->params->account_type['merchant'] ,$merchant_id);
		
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
		
		$date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
		$date_end = isset($this->data['date_end'])?$this->data['date_end']:'';
				
		$sortby = "transaction_id"; $sort = 'DESC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = intval($page)/intval($length);		
		$criteria=new CDbCriteria();
		$criteria->condition = "card_id=:card_id  AND transaction_type=:transaction_type";
		$criteria->params  = array(
		  ':card_id'=>intval($card_id),
		  ':transaction_type'=>"payout"
		);
		
		if(!empty($date_start) && !empty($date_end)){
			$criteria->addBetweenCondition("DATE_FORMAT(transaction_date,'%Y-%m-%d')", $date_start , $date_end );
		}
		
		$status_trans = AttributesTools::statusManagementTranslationList('payment', Yii::app()->language );
		
		$criteria->order = "$sortby $sort";
		$count = AR_wallet_transactions::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        
        $models = AR_wallet_transactions::model()->findAll($criteria);
        if($models){
        	foreach ($models as $item) {
        		$description = Yii::app()->input->xssClean($item->transaction_description);        		
        		$parameters = json_decode($item->transaction_description_parameters,true);        		
        		if(is_array($parameters) && count($parameters)>=1){        			
        			$description = t($description,$parameters);
        		}
        		
        		$transaction_amount = Price_Formatter::formatNumber($item->transaction_amount);
        		if($item->transaction_type=="debit"){
        			$transaction_amount = "(".Price_Formatter::formatNumber($item->transaction_amount).")";
        		}
        		
        		$trans_status = $item->status;
        		if(array_key_exists($item->status,(array)$status_trans)){
        			$trans_status = $status_trans[$item->status];
                }
        		$description = '<p class="m-0">'. $description .'</p>';
        		$description.= '<div class="badge payment '.$item->status.'">'.$trans_status.'</div>';
        		
        		$data[]=array(
        		  'transaction_amount'=>$transaction_amount,
        		  'transaction_description'=>$description,
        		  'transaction_date'=>Date_Formatter::date($item->transaction_date),          		  
        		);
        	}
        }
        
        $datatables = array(
		  'draw'=>intval($draw),
		  'recordsTotal'=>intval($count),
		  'recordsFiltered'=>intval($count),
		  'data'=>$data
		);
		
		/*header('Content-type: application/json');
		echo CJSON::encode($datatables);
		Yii::app()->end();   	*/
		$this->responseTable($datatables);
    }
    
    public function actiongetPayoutSettings()
    {
   
		try {			
			$provider = AttributesTools::PaymentPayoutProvider();
			$country_list = AttributesTools::CountryList();
			$currency_list = AttributesTools::currencyListSelection();
			
			$account_type['individual'] = t("Individual");
			$account_type['company'] = t("Company");
			
			$default_currency = AttributesTools::defaultCurrency();		
			$default_country = OptionsTools::find(array('admin_country_set'));
			
			$data = array(
			  'provider'=>$provider,
			  'country_list'=>$country_list,
			  'account_type'=>$account_type,
			  'currency_list'=>$currency_list,
			  'default_currency'=>$default_currency,
			  'default_country'=>isset($default_country['admin_country_set'])?$default_country['admin_country_set']:'',
			);
			$this->code = 1;
		    $this->msg = "OK";
		    $this->details = $data;
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		   
		}	
		$this->responseJson();		
    }
    
    public function actionSetPayoutAccount()
    {
    	try {
    		
    		$merchant_id = Yii::app()->merchant->merchant_id;
    		$meta_name = 'payout_provider';
    			    	
	    	$payment_provider = isset($this->data['payment_provider'])?$this->data['payment_provider']:'';
	    	$email_address = isset($this->data['email_address'])?$this->data['email_address']:'';
	    	
	    	$model = AR_merchant_meta::model()->find("merchant_id=:merchant_id AND meta_name=:meta_name",array(
	    	  ':merchant_id'=>intval($merchant_id),
	    	  ':meta_name'=>$meta_name,	    	  
	    	));
	    	if(!$model){
	    	    $model = new AR_merchant_meta;	
	    	}	    	
	    	
	    	$model->merchant_id = intval($merchant_id);
	    	$model->meta_name = $meta_name;
	    	$model->meta_value = $payment_provider;
	    		    	
	    	switch ($payment_provider) {
	    		case "paypal":	    
	    		    if(!empty($email_address)){
	    			$model->meta_value1 = $email_address;
	    		    } else {
	    		    	$this->msg[] = t("Invalid email address");
	    		    	$this->responseJson();
	    		    }
	    			break;
	    			
	    		case "stripe":	    	    		    		
	    		   $account_number  = isset($this->data['account_number'])?$this->data['account_number']:'';
	    		   if(empty($account_number)){
	    		   	  $this->msg[] = t("Account number is required");	    		      
	    		   }
	    		   $account_holder_name  = isset($this->data['account_holder_name'])?$this->data['account_holder_name']:'';
	    		   if(empty($account_holder_name)){
	    		   	  $this->msg[] = t("Account name is required");	    		      
	    		   }
	    		   
	    		   if(is_array($this->msg) && count($this->msg)>=1){
	    		   	  $this->responseJson();
	    		   }
	    		   
	    		   $model->meta_value1  = json_encode(array(	    		     
	    		     'account_number'=>isset($this->data['account_number'])?$this->data['account_number']:'',
	    		     'account_holder_name'=>isset($this->data['account_holder_name'])?$this->data['account_holder_name']:'',
	    		     'account_holder_type'=>isset($this->data['account_holder_type'])?$this->data['account_holder_type']:'',
	    		     'currency'=>isset($this->data['currency'])?$this->data['currency']:'',
	    		     'routing_number'=>isset($this->data['routing_number'])?$this->data['routing_number']:'',
	    		     'country'=>isset($this->data['country'])?$this->data['country']:'',
	    		   ));
	    		   break;
	    		   
	    		case "bank":	  
	    		   $account_number_iban  = isset($this->data['account_number_iban'])?$this->data['account_number_iban']:'';
	    		   if(empty($account_number_iban)){
	    		   	  $this->msg[] = t("Account number is required");	    		      
	    		   }  	    		    		
	    		   $account_name  = isset($this->data['account_name'])?$this->data['account_name']:'';
	    		   if(empty($account_name)){
	    		   	  $this->msg[] = t("Account name is required");	    		      
	    		   }  	    		    		
	    		   /*$full_name  = isset($this->data['full_name'])?$this->data['full_name']:'';
	    		   if(empty($full_name)){
	    		   	  $this->msg[] = t("Full name is required");	    		      
	    		   } */
	    		   /*$billing_address1  = isset($this->data['billing_address1'])?$this->data['billing_address1']:'';
	    		   if(empty($billing_address1)){
	    		   	  $this->msg[] = t("Billing address is required");	    		      
	    		   } */
	    		   $bank_name  = isset($this->data['bank_name'])?$this->data['bank_name']:'';
	    		   if(empty($bank_name)){
	    		   	  $this->msg[] = t("Bank name is required");	    		      
	    		   } 
	    		   $swift_code  = isset($this->data['swift_code'])?$this->data['swift_code']:'';
	    		   if(empty($swift_code)){
	    		   	  $this->msg[] = t("Swift code is required");	    		      
	    		   } 
	    		   $country  = isset($this->data['country'])?$this->data['country']:'';
	    		   if(empty($swift_code)){
	    		   	  $this->msg[] = t("Country is required");	    		      
	    		   } 
	    		   
	    		   if(is_array($this->msg) && count($this->msg)>=1){
	    		   	  $this->responseJson();
	    		   }
	    		   
	    		   $model->meta_value1  = json_encode(array(
	    		     /*'full_name'=>isset($this->data['full_name'])?$this->data['full_name']:'',
	    		     'billing_address1'=>isset($this->data['billing_address1'])?$this->data['billing_address1']:'',
	    		     'billing_address2'=>isset($this->data['billing_address2'])?$this->data['billing_address2']:'',
	    		     'city'=>isset($this->data['city'])?$this->data['city']:'',
	    		     'state'=>isset($this->data['state'])?$this->data['state']:'',
	    		     'post_code'=>isset($this->data['post_code'])?$this->data['post_code']:'',
	    		     'country'=>isset($this->data['country'])?$this->data['country']:'',*/
	    		     'account_name'=>isset($this->data['account_name'])?$this->data['account_name']:'',
	    		     'account_number_iban'=>isset($this->data['account_number_iban'])?$this->data['account_number_iban']:'',
	    		     'swift_code'=>isset($this->data['swift_code'])?$this->data['swift_code']:'',
	    		     'bank_name'=>isset($this->data['bank_name'])?$this->data['bank_name']:'',
	    		     'bank_branch'=>isset($this->data['bank_branch'])?$this->data['bank_branch']:'',
	    		   ));
	    		   break;   
	    	}
	    	
	    	if($model->save()){
	    		$this->code = 1; $this->msg = t("Payout account saved");	    		
	    	} else $this->msg = CommonUtility::parseError( $model->getErrors());
	    	
    	} catch (Exception $e) {
		   $this->msg[] = t($e->getMessage());		
		}	
		$this->responseJson();		
    }
    
    public function actiongetPayoutAccount()
    {
       try {
       	
       	  $merchant_id = Yii::app()->merchant->merchant_id;
       	  $account = CPayouts::getPayoutAccont($merchant_id);
       	  $this->code = 1; $this->msg = "OK";
       	  $this->details = $account;
       	  
       } catch (Exception $e) {
		   $this->msg[] = t($e->getMessage());		
	   }	
	   $this->responseJson();		
    }
    
    public function actionrequestPayout()
    {
    	try {
    		        	   
    	   $account = array();
    	   $merchant_id = Yii::app()->merchant->merchant_id;
    	   $amount = isset($this->data['amount'])?floatval($this->data['amount']):0;  

		   if(DEMO_MODE){
			   if($amount>10){
                  $this->msg[] = t("Maximum amount of payout in demo is 10");
				  $this->responseJson();
			   }
		   }
    	       	   
    	   $accounts = CPayouts::getPayoutAccont($merchant_id);    	       	   
    	   $account = isset($accounts['account'])?$accounts['account']:'';
    	       	   
    	   //$transaction_id = CMerchantEarnings::requestPayout($merchant_id,$amount , $account , $accounts );    	   
    	   
    	   $card_id = CWallet::getCardID( Yii::app()->params->account_type['merchant'] , $merchant_id);
    	   $transaction_id = CEarnings::requestPayout($card_id,$amount , $account , $accounts );
    	   
    	   $params = array();
    	   foreach ($accounts as $itemkey=>$item) {    	   	  
    	   	  $params[]=array(
    	   	    'transaction_id'=>intval($transaction_id),
    	   	    'meta_name'=>$itemkey,
    	   	    'meta_value'=>$item,
    	   	    'date_created'=>CommonUtility::dateNow(),
    	   	    'ip_address'=>CommonUtility::userIp(),
    	   	  );
    	   }    	   
    	   $builder=Yii::app()->db->schema->commandBuilder;
		   //$command=$builder->createMultipleInsertCommand('{{merchant_earnings_meta}}', $params );
		   $command=$builder->createMultipleInsertCommand('{{wallet_transactions_meta}}', $params );
		   $command->execute();
    	   
    	   $this->code = 1; $this->msg = t("Payout request successfully logged");
    	   $this->details = $transaction_id;
    	   
    	} catch (Exception $e) {
		   $this->msg[] = t($e->getMessage());		   
	   }	
	   $this->responseJson();		
    }

    public function actionorderHistory()
    {
    	$data = array();		
    	$status = COrders::statusList(Yii::app()->language);    	
    	$services = COrders::servicesList(Yii::app()->language);
    	$payment_list = AttributesTools::PaymentProvider();	
    	    	
		$merchant_id = Yii::app()->merchant->merchant_id;
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
		$filter = isset($this->data['filter'])?$this->data['filter']:'';
		
		$date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
		$date_end = isset($this->data['date_end'])?$this->data['date_end']:'';
				
		$sortby = "order_id"; $sort = 'DESC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = $page>0? intval($page)/intval($length) : 0;	
		$criteria=new CDbCriteria();
		$criteria->alias = "a";
		$criteria->select = "a.order_id, a.client_id, a.status, a.order_uuid , 
		a.payment_code, a.service_code,a.total, a.date_created,a.request_from,
		b.meta_value as customer_name, 
		(
		   select sum(qty)
		   from {{ordernew_item}}
		   where order_id = a.order_id
		) as total_items,
		
		c.avatar as logo, c.path
		";
		$criteria->join='
		LEFT JOIN {{ordernew_meta}} b on  a.order_id=b.order_id 
		LEFT JOIN {{client}} c on  a.client_id = c.client_id 
		';
		$criteria->condition = "a.merchant_id=:merchant_id AND meta_name=:meta_name ";
		$criteria->params  = array(
		  ':merchant_id'=>intval($merchant_id),		  
		  ':meta_name'=>'customer_name'
		);
		if(!empty($date_start) && !empty($date_end)){
			$criteria->addBetweenCondition("DATE_FORMAT(a.date_created,'%Y-%m-%d')", $date_start , $date_end );
		}
		
		$pos_code = AttributesTools::PosCode();	
		$criteria->addNotInCondition('a.service_code', array($pos_code) );
		
		$filter_order_status = null;
		if(is_array($filter) && count($filter)>=1){
		    $filter_order_status = isset($filter['order_status'])?$filter['order_status']:'';
		    $filter_order_type = isset($filter['order_type'])?$filter['order_type']:'';
		    $filter_client_id = isset($filter['client_id'])?intval($filter['client_id']):'';
		    
			if(!empty($filter_order_status)){
				$criteria->addSearchCondition('a.status', $filter_order_status );
			}
			if(!empty($filter_order_type)){
				$criteria->addSearchCondition('a.service_code', $filter_order_type );
			}
			if($filter_client_id>0){
				$criteria->addSearchCondition('a.client_id', intval($filter_client_id) );
			}
		}

		if(!$filter_order_status){
			$exclude_status = AttributesTools::excludeStatus();
		    $criteria->addNotInCondition('a.status', (array) $exclude_status );	
		}		

		// EXCLUDE DRIVER STATUS
		$criteria->addNotInCondition("a.delivery_status",['waiting_for_driver']);
				
		$criteria->order = "$sortby $sort";
		$count = AR_ordernew::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        
                
        $models = AR_ordernew::model()->findAll($criteria);
                
        if($models){
         	foreach ($models as $item) {         		

         	$item->total_items = intval($item->total_items);
         	$item->total_items = t("{{total_items}} items",array(
         	 '{{total_items}}'=>$item->total_items
         	));
         	
         	$trans_order_type = $item->service_code;
         	if(array_key_exists($item->service_code,$services)){
         		$trans_order_type = $services[$item->service_code]['service_name'];
         	}
         	
         	$order_type = t("Order Type.");
         	$order_type.="<span class='ml-2 services badge $item->service_code'>$trans_order_type</span>";
         	
         	$total = t("Total. {{total}}",array(
         	 '{{total}}'=>Price_Formatter::formatNumber($item->total)
         	));
         	$place_on = t("Place on {{date}}",array(
         	 '{{date}}'=>Date_Formatter::dateTime($item->date_created)
         	));
         	
         	$status_trans = $item->status;
         	if(array_key_exists($item->status, (array) $status)){
         		$status_trans = $status[$item->status]['status'];
         	}
         	
         	$view_order = Yii::app()->createUrl('orders/view',array(
         	  'order_uuid'=>$item->order_uuid
         	));
         	
         	$print_pdf = Yii::app()->createUrl('print/pdf',array(
         	  'order_uuid'=>$item->order_uuid
         	));
         	
         	$status_class = str_replace(" ","_",$item->status);
         	         	
         	if(array_key_exists($item->payment_code,(array)$payment_list)){
	            $item->payment_code = $payment_list[$item->payment_code];
	        }
			        
	        $avatar = CMedia::getImage($item->logo,$item->path,'@thumbnail',
		         CommonUtility::getPlaceholderPhoto('customer'));
		         
         		
$information = <<<HTML
$item->total_items<span class="ml-2 badge order_status $status_class">$status_trans</span>
<p class="dim m-0">$item->payment_code</p>
<p class="dim m-0">$order_type</p>
<p class="dim m-0">$total</p>
<p class="dim m-0">$place_on</p>
HTML;

$buttons = <<<HTML
<div class="btn-group btn-group-actions" role="group">
 <a href="$view_order" target="_blank" class="btn btn-light tool_tips"><i class="zmdi zmdi-eye"></i></a>
 <a href="$print_pdf" target="_blank"  class="btn btn-light tool_tips"><i class="zmdi zmdi-download"></i></a>
</div>
HTML;

         		$data[]=array(
         		  'logo'=>'<img class="img-60 rounded-circle" src="'.$avatar.'">',
        		  'order_id'=>$item->order_id,
        		  'client_id'=>$item->customer_name,
        		  'status'=>$information,
				  'request_from'=>t($item->request_from),
        		  'order_uuid'=>$buttons
        		);
         	}
        }	
        
        $datatables = array(
		  'draw'=>intval($draw),
		  'recordsTotal'=>intval($count),
		  'recordsFiltered'=>intval($count),
		  'data'=>$data
		);
		$this->responseTable($datatables);		
    }
    
    public function actiongetFilterData()
    {
    	try {
    	   
    		$data = array(
    		   'status_list'=>AttributesTools::getOrderStatus(Yii::app()->language),
    		   'order_type_list'=>AttributesTools::ListSelectServices(),
    		);
    		$this->code = 1; $this->msg = "OK";
    		$this->details = $data;
    		
    	} catch (Exception $e) {
		   $this->msg[] = t($e->getMessage());		   
	    }	
	    $this->responseJson();		
    }
    
    public function actionsearchCustomer()
    {     	 
    	 $search = isset($this->data['search'])?$this->data['search']:''; 
    	 $is_pos = isset($this->data['POS'])?$this->data['POS']:false;
    	 $is_pos = $is_pos==1?true:false;
    	     	 
    	 if($is_pos && empty($search)){
    	 	$data[] = array(
    	 	  'id'=>"walkin",
    	 	  'text'=>t("Walk-in Customer")
    	 	);
    	 } else $data = array();    	 
    	 
    	 $criteria=new CDbCriteria();
    	 $criteria->select = "client_id,first_name,last_name";
    	 $criteria->condition = "status=:status";
    	 $criteria->params = array(
    	   ':status'=>'active'
    	 );
    	 if(!empty($search)){
			$criteria->addSearchCondition('first_name', $search );
			$criteria->addSearchCondition('last_name', $search , true , 'OR' );
		 }
		 $criteria->limit = 10;
		 if($models = AR_client::model()->findAll($criteria)){		 	
		 	foreach ($models as $val) {
		 		$data[]=array(
				  'id'=>$val->client_id,
				  'text'=>$val->first_name." ".$val->last_name
				);
		 	}
		 }
		 
		$result = array(
    	  'results'=>$data
    	);	       	
    	$this->responseSelect2($result);
    }
    
    public function actiongetordersummary()
    {    	
    	try {	

			$date_now = date("Y-m-d");			
			$date_start =  Yii::app()->request->getPost('date_start', '');            
			$date_end =  Yii::app()->request->getPost('date_end', '');   

	    	$merchant_id = Yii::app()->merchant->merchant_id;
	    	$exclude_status = AttributesTools::excludeStatus();
	    	$refund_status = AttributesTools::refundStatus();	
	    	$orders = 0; $order_cancel = 0; $total=0;
	    	
	    	$not_in_status = AOrderSettings::getStatus(array('status_cancel_order','status_rejection'));
	    	$not_in_status = array_merge( (array) $not_in_status, (array) $exclude_status);		
						
	    	$orders = AOrders::getOrdersTotal($merchant_id,array(),$not_in_status,$date_start,$date_end);			
	    	
	    	$status_cancel = AOrderSettings::getStatus(array('status_cancel_order','status_rejection'));			
		    $order_cancel = AOrders::getOrdersTotal($merchant_id,$status_cancel,'',$date_start,$date_end);
		    
		    $status_delivered = AOrderSettings::getStatus(array('status_delivered','status_completed'));
						
		    $total = AOrders::getOrderSummary($merchant_id,$status_delivered,'',$date_start,$date_end);
		    $total_refund = AOrders::getTotalRefund($merchant_id,$refund_status,'',$date_start,$date_end);
	    									
	    	$data = array(
		     'orders'=>$orders,
		     'order_cancel'=>$order_cancel,
		     'total'=>Price_Formatter::formatNumberNoSymbol($total),
		     'total_refund'=>Price_Formatter::formatNumberNoSymbol($total_refund),
		     'price_format'=>AttributesTools::priceUpFormat()
		    );
		    
		    $this->code = 1;
			$this->msg = "OK";
			$this->details = $data;		    
		    
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		
		}	
		$this->responseJson();	
    	
    }
       
    public function actiongetOrderFilterSettings()
    {
    	try {
    				
    	    $data = array(
    		   'status_list'=>AttributesTools::getOrderStatus(Yii::app()->language),
    		   'order_type_list'=>AttributesTools::ListSelectServicesNew(Yii::app()->language),
    		   'payment_status_list'=>AttributesTools::statusManagementTranslationList('payment',Yii::app()->language),
    		   'sort_list'=>AttributesTools::orderSortList()
    		);    		
    		$this->code = 1; $this->msg = "OK";
    		$this->details = $data;
    		
    	} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		
		}	
		$this->responseJson();		
    }
    
    public function actiongetWebpushSettings()
	{
		try {						
						
			$settings = AR_admin_meta::getMeta(array('webpush_app_enabled','webpush_provider','pusher_instance_id','onesignal_app_id'
			));		
						
			$enabled = isset($settings['webpush_app_enabled'])?$settings['webpush_app_enabled']['meta_value']:'';
			$provider = isset($settings['webpush_provider'])?$settings['webpush_provider']['meta_value']:'';
			$pusher_instance_id = isset($settings['pusher_instance_id'])?$settings['pusher_instance_id']['meta_value']:'';			
			$onesignal_app_id = isset($settings['onesignal_app_id'])?$settings['onesignal_app_id']['meta_value']:'';	
			
			$user_settings = array();
			
			try {
			   $user_settings = CNotificationData::getUserSettings(Yii::app()->merchant->id,'merchant');		
			} catch (Exception $e) {
			   //
			}
			
			$data = array(
			  'enabled'=>$enabled,
			  'provider'=>$provider,
			  'pusher_instance_id'=>$pusher_instance_id,			  
			  'onesignal_app_id'=>$onesignal_app_id,
			  'safari_web_id'=>'',
			  'channel'=>Yii::app()->merchant->id,
			  'user_settings'=>$user_settings,
			);			
			$this->code = 1;
			$this->msg = "OK";
			$this->details = $data;
						
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();		
	}
	
    public function actiongetNotifications()
	{
		try {								
			$data = CNotificationData::getList( Yii::app()->merchant->merchant_uuid );			
			$this->code = 1; $this->msg = "ok";
			$this->details = $data;
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();		
	}
    
	public function actionclearNotifications()
	{
		try {						
						
			AR_notifications::model()->deleteAll('notication_channel=:notication_channel',array(
			 ':notication_channel'=> Yii::app()->merchant->merchant_uuid
			));
			$this->code = 1; $this->msg = "ok";
						
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();		
	}
	
	public function actiongetwebnotifications()
	{
		try {
						
			$data = CNotificationData::getUserSettings(Yii::app()->merchant->id,'merchant');
			$this->code = 1;
		    $this->msg = "OK";
		    $this->details = $data;
			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();		
	}
	
	public function actionsavewebnotifications()
	{
		try {		
					    			
		    $webpush_enabled = isset($this->data['webpush_enabled'])?intval($this->data['webpush_enabled']):0;	
		    $interest = isset($this->data['interest'])?$this->data['interest']:'';
		    $device_id = isset($this->data['device_id'])?$this->data['device_id']:'';
		    		    
		    $model = AR_device::model()->find("user_id=:user_id AND user_type=:user_type",array(
		      ':user_id'=>intval(Yii::app()->merchant->id),
		      ':user_type'=>"merchant"
		    ));
		    if(!$model){
		       $model = new AR_device;			       
		    } 		    		    
		    $model->interest = array(Yii::app()->merchant->merchant_uuid);
		    $model->user_type = 'merchant';
	    	$model->user_id = intval(Yii::app()->merchant->id);
	    	$model->platform = "web";
	    	$model->device_token = $device_id;
	    	$model->browser_agent = $_SERVER['HTTP_USER_AGENT'];
	    	$model->enabled = $webpush_enabled;
	    	if($model->save()){
		   	   $this->code = 1;
			   $this->msg = t("Setting saved");		    
		    } else $this->msg = CommonUtility::parseError( $model->getErrors());
		    		   		    		    
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    		    
		}	
		$this->responseJson();		
	}	
	
	public function actionMerchantOrderingStatus()
	{		
		try {
						
			$data = AR_merchant_meta::getMeta(Yii::app()->merchant->merchant_id,array(
			 'accepting_order','pause_time','pause_interval'
			));
			$accepting_order = isset($data['accepting_order'])?$data['accepting_order']['meta_value']:true;
			$accepting_order = $accepting_order==1?true:false;
			$pause_time = isset($data['pause_time'])?trim($data['pause_time']['meta_value']):'';			
			
			if(!$accepting_order){							
				$pause_time = Date_Formatter::dateTime($pause_time,"yyyy-MM-ddTHH:mm",true);
			} else $pause_time = Date_Formatter::dateTime(date("c"),"yyyy-MM-ddTHH:mm",true);
		
			$this->code = 1; $this->msg = "ok";
			$this->details = array(
			  'accepting_order'=>$accepting_order,
			  'pause_time'=>$pause_time,			  
			);					
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    		    
		}	
		$this->responseJson();		
    }
    
    public function actiongetPauseOptions()
    {
    	try {
    		
    		 $times = AttributesTools::delayedMinutes();
    		 $pause_reason = AOrders::rejectionList('pause_reason',Yii::app()->language);
    		 
    		 $array = array(
    		  'id'=>"other",
    		  'value'=>t("Other")
    		 );
    		 array_push($times,$array);
    		     		 
    		 $this->code = 1;
    		 $this->msg = "ok";
    		 $this->details = array(
    		   'times'=>$times,
    		   'pause_reason'=>$pause_reason
    		 );    		 
    		     		
    	} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    		    
		}	
		$this->responseJson();		
    }
    
    public function actionsetPauseOrder()
    {
    	try {    		
    		
    		$now = time(); $pause_time=0;
    		$time_delay = isset($this->data['time_delay'])?$this->data['time_delay']:0;
    		$pause_hours = isset($this->data['pause_hours'])?intval($this->data['pause_hours']):0;
    		$pause_minutes = isset($this->data['pause_minutes'])?intval($this->data['pause_minutes']):0;
    		$reason = isset($this->data['reason'])?$this->data['reason']:'';    	
    		
			$sleep_in_seconds = 0;
    		if($time_delay=="other"){    			
    			$pause_time = date('Y-m-d H:i:s',strtotime("+$pause_hours hour +$pause_minutes minutes",$now));
				$sleep_in_seconds = (intval($pause_hours)*3600) +  (intval($pause_minutes)*60);
    		} else {
    			$time_delay = intval($time_delay);     			
				$sleep_in_seconds = $time_delay*60;
    		    $pause_time = date("Y-m-d H:i:s", strtotime("+$time_delay minutes", $now));
    		}
			
			$status = 'pause';

    		AR_merchant_meta::saveMeta(Yii::app()->merchant->merchant_id,'pause_time',$pause_time);
    		AR_merchant_meta::saveMeta(Yii::app()->merchant->merchant_id,'pause_reason',$reason);
    		AR_merchant_meta::saveMeta(Yii::app()->merchant->merchant_id,'accepting_order',false);
			AR_merchant_meta::saveMeta(Yii::app()->merchant->merchant_id,'store_status',$status);
    		
    		try {
    		   $merchant = CMerchants::get(Yii::app()->merchant->merchant_id);
    		   $merchant->close_store = false;	
               $merchant->pause_ordering = true;    		   		   
    		   $merchant->save();
    		} catch (Exception $e) {
    		   //	
            }

			$timezone = Yii::app()->timeZone;
			$start_time = date('Y-m-d\TH:i:s', strtotime(date('c')));
            $end_time = date('Y-m-d\TH:i:s', strtotime($pause_time));

			AR_merchant_meta::saveMeta2(
                Yii::app()->merchant->merchant_id,
                'pause_order',
                $start_time,
                $end_time,
                $timezone,
            );            
    		
    		$pause_time = Date_Formatter::dateTime($pause_time,"yyyy-MM-ddTHH:mm",true);
    		
    		$this->code = 1;
    		$this->msg = "ok";
    		$this->details = array(
    		  'pause_time'=>$pause_time,
    		  'accepting_order'=>false,
    		);

			if($sleep_in_seconds>0){
				$this->details['sleep_in_seconds'] = $sleep_in_seconds;				
			}
    		    		    	
    	} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    		    
		}	
		$this->responseJson();		
    }
    
    public function actionUpdateOrderingStatus()
    {
    	try {    		
    		
    		$accepting_order = isset($this->data['accepting_order'])?$this->data['accepting_order']:false;
			$accepting_order = $accepting_order==1?true:false;
			AR_merchant_meta::saveMeta(Yii::app()->merchant->merchant_id,'accepting_order',$accepting_order);
			AR_merchant_meta::saveMeta(Yii::app()->merchant->merchant_id,'pause_time','');
    		AR_merchant_meta::saveMeta(Yii::app()->merchant->merchant_id,'pause_reason','');
    		
			AR_merchant_meta::saveMeta(Yii::app()->merchant->merchant_id,'store_status','open');
            Yii::app()->db->createCommand(
                "DELETE FROM {{merchant_meta}}
                WHERE merchant_id=".Yii::app()->merchant->merchant_id."
                AND meta_name='pause_order'
                "
            )->query();

    		try {
    		   $merchant = CMerchants::get(Yii::app()->merchant->merchant_id);
    		   $merchant->pause_ordering = false;    		   
    		   $merchant->save();
    		} catch (Exception $e) {
    		   //	
            }
    		
			$this->code = 1;
    		$this->msg = "ok";
    		$this->details = array(
    		   'pause_time'=>'',
    		   'accepting_order'=>$accepting_order
    		);
			
    	} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    		    
		}	
		$this->responseJson();		
    }
	
	public function actionallNotifications()
	{
		$data = array();		
						
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'' )  :'';	
		
		$date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
		$date_end = isset($this->data['date_end'])?$this->data['date_end']:'';
				
		$sortby = "date_created"; $sort = 'DESC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = intval($page)/intval($length);		
		$criteria=new CDbCriteria();
		$criteria->condition="notication_channel=:notication_channel";
		$criteria->params = array(':notication_channel'=> Yii::app()->merchant->merchant_uuid );
		
		if(!empty($date_start) && !empty($date_end)){
			$criteria->addBetweenCondition("DATE_FORMAT(date_created,'%Y-%m-%d')", $date_start , $date_end );
		}
		
		$criteria->order = "$sortby $sort";
		$count = AR_notifications::model()->count($criteria); 
		
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        
        $models = AR_notifications::model()->findAll($criteria);
        if($models){
        	foreach ($models as $item) {
        		
        		$params = !empty($item->message_parameters)?json_decode($item->message_parameters,true):'';        		
        		$data[]=array(				 
        		  'date_created'=>Date_Formatter::dateTime($item->date_created),
				  'message'=>t($item->message,(array)$params),				  
				);
        	}        	
        }
        
         $datatables = array(
		  'draw'=>intval($draw),
		  'recordsTotal'=>intval($count),
		  'recordsFiltered'=>intval($count),
		  'data'=>$data
		);				
		$this->responseTable($datatables);
	}
	
	public function actiondefaultPaymentGateway()
	{
		try {
			$merchant_id = Yii::app()->merchant->merchant_id;
			$payment_code = '';
			$meta = AR_merchant_meta::getValue($merchant_id,'subscription_payment_method');
			/*if($meta){				
			} else $this->msg = t("Payment code not set");*/
			$payment_code = isset($meta['meta_value'])?$meta['meta_value']:'';	
			$payment_code = !empty($payment_code)?$payment_code:'stripe';
			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(
			  'payment_code'=>$payment_code
			);
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		
		}
		$this->responseJson();	
    }
    
	public function actiongetmerchantplan()
	{
		try {
			
			$merchant_id = Yii::app()->merchant->merchant_id;
			$merchant = CMerchants::get($merchant_id);
			$plans = Cplans::get($merchant->package_id);
			$this->code = 1;
			$this->msg = "ok";
			$this->details = array(
			 'plan_title'=>Yii::app()->input->xssClean($plans->title)
			);
			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    		    
		}	
		$this->responseJson();		
    }
    
    public function actionplanInvoiceList()
    {
    
    	$data = array();
    	$merchant_id = Yii::app()->merchant->merchant_id;
    	
    	$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
		
		$date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
		$date_end = isset($this->data['date_end'])?$this->data['date_end']:'';
		$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';		
				
		$sortby = "created"; $sort = 'DESC';
    		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = intval($page)/intval($length);		
		$criteria=new CDbCriteria();
		$criteria->alias = "a";
		$criteria->select="a.invoice_number,a.invoice_ref_number,a.created,a.amount,a.status,a.payment_code,b.title";
		$criteria->join='LEFT JOIN {{plans_translation}} b on  a.package_id=b.package_id ';
				
		$criteria->addCondition('a.merchant_id=:merchant_id AND b.language=:language');
		$criteria->params = array( 
		  ':merchant_id'=>intval($merchant_id),
		  ':language'=>Yii::app()->language
		);
		
		if(!empty($date_start) && !empty($date_end)){
			$criteria->addBetweenCondition("DATE_FORMAT(a.created,'%Y-%m-%d')", $date_start , $date_end );
		}
		
		$criteria->order = "$sortby $sort";
		$count = AR_plans_invoice::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        
        $models = AR_plans_invoice::model()->findAll($criteria);        
        if($models){
        	foreach ($models as $item) {
        		$data[]=array(
        		  'payment_code'=>$item->payment_code,
        		  'invoice_number'=>$item->invoice_number,
        		  'invoice_ref_number'=>$item->invoice_ref_number,
        		  'created'=>Date_Formatter::date($item->created),
        		  'package_id'=>Yii::app()->input->xssClean($item->title),
        		  'amount'=>Price_Formatter::formatNumber($item->amount),
        		  'status'=>'<span class="badge payment '.$item->status.' ">'.t($item->status).'</span>',
        		);
        	}
        }
        
        $datatables = array(
		  'draw'=>intval($draw),
		  'recordsTotal'=>intval($count),
		  'recordsFiltered'=>intval($count),
		  'data'=>$data
		);
        
        $this->responseTable($datatables);
    }
    
    public function actiongetPlanList()
    {
    	try {
			
			$details = array(); $package_uuid = ''; $payment_code ='';
			$merchant_id = Yii::app()->merchant->merchant_id;			
			$merhant = CMerchants::get($merchant_id);		
			
			try {
			   $plan = Cplans::get($merhant->package_id);			
			   $package_uuid = $plan->package_uuid;
			} catch (Exception $e) {
				//
			}
			
			$data = CPlan::listing( Yii::app()->language , true);		
			
			$meta = AR_merchant_meta::getValue($merchant_id,'subscription_payment_method');
			if($meta){
				$payment_code = isset($meta['meta_value'])?$meta['meta_value']:'';
			}
			
			/*try {
			    $details = CPlan::Details();		
			} catch (Exception $e) {
				
			}*/
			
			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(			
			  'data'=>$data,
			  'package_uuid'=>$package_uuid,
			  'payment_code'=>!empty($payment_code)?$payment_code:'stripe',
			  //'plan_details'=>$details,			  
			);										
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		
		}
		$this->responseJson();	
    }
    
    public function actiongetplandetails()
    {
    	try {
    		    	   
    	   $package_uuid = isset($this->data['package_uuid'])?$this->data['package_uuid']:'';
    	   $plans = Cplans::getByUUID($package_uuid);	    	   
    	   $data = array(
    	     'title'=>Yii::app()->input->xssClean($plans->title),
    	     'price'=> Price_Formatter::formatNumber($plans->price),
    	     'price_raw'=>$plans->price,
    	     'promo_price'=> Price_Formatter::formatNumber($plans->promo_price),
    	     'promo_price_raw'=>$plans->promo_price,
    	     'package_period'=>$plans->package_period,
    	   );
    	   $this->code = 1;
    	   $this->msg = "OK";
    	   $this->details = $data;
    	   
    	} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		
		}
		$this->responseJson();	
    }
    
    public function actionPaymenPlanList()
	{
		 try {
		 	
		 	$payment_list = AttributesTools::PaymentPlansProvider(); 
		 	$this->code = 1;
		 	$this->msg = "ok";
		 	$this->details = $payment_list; 
		 	
		 } catch (Exception $e) {
		    $this->msg = t($e->getMessage());		
		 }
		 $this->responseJson();	
	}
	
	public function actionmerchantPlanStatus()
	{
		try {
			
			$merchant_id = Yii::app()->merchant->merchant_id;
			
			$merchant = CMerchants::get($merchant_id);
			$status_list = AttributesTools::statusManagementTranslationList('customer',Yii::app()->language);			
			$data = array(
			  'restaurant_name'=>Yii::app()->input->xssClean($merchant->restaurant_name),			  
			  'status'=>isset($status_list[$merchant->status])?$status_list[$merchant->status]:$merchant->status,
			  'status_raw'=>$merchant->status,
			);			
			Yii::app()->merchant->setState("status",$merchant->status);
			$this->code = 1;
			$this->msg = "ok";
			$this->details = $data;
			
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());	
		   Yii::app()->merchant->logout(false);		
		}
		$this->responseJson();	
	}
	
	public function actiontaxlist()
	{
		$data = array(); 
				
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
		
		$date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
		$date_end = isset($this->data['date_end'])?$this->data['date_end']:'';		
		$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';		
				
		$sortby = "tax_id"; $sort = 'DESC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
					
		$page = intval($page)/intval($length);		
		$criteria=new CDbCriteria();		
		
		$criteria->addCondition("merchant_id=:merchant_id AND tax_type=:tax_type");
		$criteria->params = array( 
		   ':merchant_id'=>intval(Yii::app()->merchant->merchant_id),
		   ':tax_type'=>$transaction_type
		);
				
		$criteria->order = "$sortby $sort";
		$count = AR_tax::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        
        $models = AR_tax::model()->findAll($criteria);
        
        $tax_price_list = CommonUtility::taxPriceList();
        
        if($models){
        	foreach ($models as $item) {    
        		$default = "";    		
        		
        		if($item->tax_type=="standard"){
        		if($item->default_tax==1){
        			$default = '<div class="badge badge-light">'.t("Default").'</div>';    	
        			$default.='<div class="font11">'.$tax_price_list[$item->tax_in_price].'</div>';	
        		}
        		}
        		
        		$data[]=array(
        		  'tax_uuid'=>$item->tax_uuid,
        		  'tax_name'=>"<div>$item->tax_name</div>".$default,
        		  'tax_rate'=>$item->tax_rate,
        		  'active'=>$item->active==1? '<span class="badge badge-success">'.t("Active").'</span>' : '<span class="badge badge-danger">'.t("Inactive").'</span>' ,
        		  'tax_id'=>$item->tax_id
        		);
        	}
        }
        
        $datatables = array(
		  'draw'=>intval($draw),
		  'recordsTotal'=>intval($count),
		  'recordsFiltered'=>intval($count),
		  'data'=>$data
		);
				
		$this->responseTable($datatables);
	}			
    
	public function actionsavetax()
	{
		 try {
		 	
		 	$merchant_id = Yii::app()->merchant->merchant_id;
		 	
		 	$tax_uuid = isset($this->data['tax_uuid'])?$this->data['tax_uuid']:'';
		 	$tax_name = isset($this->data['tax_name'])?$this->data['tax_name']:'';
		 	$tax_type = isset($this->data['tax_type'])?$this->data['tax_type']:'';
		 	$tax_rate = isset($this->data['tax_rate'])?$this->data['tax_rate']:'';
		 	$default_tax = isset($this->data['default_tax'])?$this->data['default_tax']:'';
		 	$active = isset($this->data['active'])?$this->data['active']:'';
		 	$tax_in_price = isset($this->data['tax_in_price'])?$this->data['tax_in_price']:'';
		 	
		 	$model = AR_tax::model()->find("tax_uuid=:tax_uuid",array(
		 	 ':tax_uuid'=>$tax_uuid
		 	));
		 	if(!$model){
		 		$model = new AR_tax;
		 	}
		 			 	
		 	$model->merchant_id = intval($merchant_id);
		 	$model->tax_name = $tax_name;
		 	$model->tax_type = $tax_type;
		 	$model->tax_rate = floatval($tax_rate);
		 	$model->default_tax = intval($default_tax);
		 	$model->active = intval($active);
		 	$model->tax_in_price = intval($tax_in_price);
		 	if($model->save()){
		 		$this->code = 1;
		 		$this->msg = t("Successful");
		 	} else $this->msg = CommonUtility::parseError( $model->getErrors());		 	
		 	
		 } catch (Exception $e) {
		   $this->msg[] = t($e->getMessage());			   
		}
		$this->responseJson();	
    }
    
    public function actiongetTax()
    {
    	try {
    		
    		
    		$tax_uuid = Yii::app()->input->post('tax_uuid'); 
    		$model = AR_tax::model()->find("merchant_id=:merchant_id AND tax_uuid=:tax_uuid",array(
    		 ':merchant_id'=>Yii::app()->merchant->merchant_id,
    		 ':tax_uuid'=>$tax_uuid
    		));
    		if($model){
    			$this->code = 1; $this->msg = "ok";
    			$this->details = array(
    			  'tax_uuid'=>$model->tax_uuid,
    			  'tax_name'=>$model->tax_name,
    			  'tax_in_price'=>$model->tax_in_price,
    			  'tax_rate'=>$model->tax_rate,
    			  'default_tax'=>$model->default_tax,
    			  'active'=>$model->active,
    			);
    		} else $this->msg = t("Tax not found");
    		
    	} catch (Exception $e) {
		   $this->msg = t($e->getMessage());			   
		}
		$this->responseJson();	
    }
    
    public function actiontaxDelete()
    {
    	try {
    		
    		$tax_uuid = Yii::app()->input->post('tax_uuid');     	
    		$model = AR_tax::model()->find("tax_uuid=:tax_uuid",array(
    		 ':tax_uuid'=>$tax_uuid
    		));
    		if($model){
    			$model->delete();
    			$this->code = 1; $this->msg = "ok";    			
    		} else $this->msg = t("Tax not found");
    		
    	} catch (Exception $e) {
		   $this->msg = t($e->getMessage());			   
		}
		$this->responseJson();	
    }
    
    public function actiongetLastTenOrder()
    {
    	try {
        
    		$merchant_id = Yii::app()->merchant->merchant_id;
    		$settings = OptionsTools::find(array('merchant_order_critical_mins'),$merchant_id);
    		$critical_mins = isset($settings['merchant_order_critical_mins'])?$settings['merchant_order_critical_mins']:0;
    		$critical_mins = intval($critical_mins);    		
    		
    		$data = array(); $order_status = array(); $datetime=date("Y-m-d g:i:s a");    		
    		$filter_by = Yii::app()->input->post('filter_by'); 
    		$limit = Yii::app()->input->post('limit'); 
    		    		  
    		if($filter_by!="all"){
	    		$order_status = AOrders::getOrderTabsStatus($filter_by);			    						
    		}
    				    		
    		$status = COrders::statusList(Yii::app()->language);    	
            $services = COrders::servicesList(Yii::app()->language);
            $payment_status = COrders::paymentStatusList2(Yii::app()->language,'payment');  
                        
            $status_in = AOrders::getOrderTabsStatus('new_order');              
            $payment_list = AttributesTools::PaymentProvider();	 						
                        
    		$criteria=new CDbCriteria();
		    $criteria->alias = "a";
		    $criteria->select = "a.order_id, a.order_uuid, a.client_id, a.status, a.order_uuid , 
		    a.payment_code, a.service_code,a.total, a.delivery_date, a.delivery_time, a.date_created, a.payment_code, a.total,
		    a.payment_status, a.is_view, a.is_critical, a.whento_deliver,
			a.use_currency_code,a.base_currency_code,a.exchange_rate,
			a.is_commission_applied, a.merchant_total,
		    b.meta_value as customer_name, 
		    
		    IF(a.whento_deliver='now', 
		      TIMESTAMPDIFF(MINUTE, a.date_created, NOW())
		    , 
		     TIMESTAMPDIFF(MINUTE, concat(a.delivery_date,' ',a.delivery_time), NOW())
		    ) as min_diff
		    
		    ,
		    (
		       select sum(qty)
		       from {{ordernew_item}}
		       where order_id = a.order_id
		    ) as total_items
		    ";
		    $criteria->join='LEFT JOIN {{ordernew_meta}} b on  a.order_id=b.order_id ';
		    $criteria->condition = "a.merchant_id=:merchant_id AND meta_name=:meta_name ";
		    $criteria->params  = array(
		      ':merchant_id'=>intval($merchant_id),		  
		      ':meta_name'=>'customer_name'
		    );

			$criteria->addNotInCondition("a.request_from",['pos']);
		    
		    if(is_array($order_status) && count($order_status)>=1){
		    	$criteria->addInCondition('status',(array) $order_status );
		    } else {
		    	$exclude_status = AttributesTools::excludeStatus();		    					
		    	$criteria->addNotInCondition('status', (array) $exclude_status );
            }

			// EXCLUDE WAITING STATUS
			$criteria->addNotInCondition("a.delivery_status",['waiting_for_driver']);
            
            $pos_code = AttributesTools::PosCode();	
		    $criteria->addNotInCondition('a.service_code', array($pos_code) );
            
		    $criteria->order = "date_created DESC";		    
		    $criteria->limit = intval($limit);
		    		    
			
		    $models = AR_ordernew::model()->findAll($criteria);   
		    
		    PrettyDateTime::$category='backend';
		    
		    if($models){		    	
				
		    	foreach ($models as $item) {					
		    		
		    		$status_trans = $item->status;
		            if(array_key_exists($item->status, (array) $status)){
		               $status_trans = $status[$item->status]['status'];
		            }
		            
		            $trans_order_type = $item->service_code;
			        if(array_key_exists($item->service_code,(array)$services)){
			            $trans_order_type = $services[$item->service_code]['service_name'];
			        }
			        			        
			        $payment_status_name = $item->payment_status;
			        if(array_key_exists($item->payment_status,(array)$payment_status)){
			            $payment_status_name = $payment_status[$item->payment_status]['title'];
			        }
			        
			        if(array_key_exists($item->payment_code,(array)$payment_list)){
			            $item->payment_code = $payment_list[$item->payment_code];
			        }
		    					        
			        $is_critical =  0;					        
			        if($item->whento_deliver=="schedule"){
			        	if($item->min_diff>0){
			        		$is_critical = true;
			        	}
			        } else if ($critical_mins>0 && $item->min_diff>$critical_mins && in_array($item->status,(array)$status_in) ) {
			        	$is_critical = true;
			        }				
										
					$total = $item->is_commission_applied==1 ? $item->merchant_total : $item->total;
			        
		    		$data[]=array(
		    		  'order_id'=>$item->order_id,
		    		  'order_id'=>t("Order #{{order_id}}",array('{{order_id}}'=>$item->order_id)),
		    		  'order_uuid'=>$item->order_uuid,
		    		  'client_id'=>$item->client_id,
		    		  'customer_name'=>t($item->customer_name),
		    		  'status'=>$status_trans,
		    		  'status_raw'=>str_replace(" ","_",$item->status),
		    		  'order_type'=>$trans_order_type,
		    		  'payment_code'=>t($item->payment_code),
		    		  'total'=>Price_Formatter::formatNumber($item->total),
		    		  'payment_status'=>$payment_status_name,
		    		  'payment_status_raw'=>str_replace(" ","_",$item->payment_status),
		    		  'view_order'=> Yii::app()->createAbsoluteUrl('orders/view',array(
			         	  'order_uuid'=>$item->order_uuid
			           )),
			          'print_pdf'=>Yii::app()->createAbsoluteUrl('print/pdf',array(
			         	  'order_uuid'=>$item->order_uuid
			           )),
			           'is_view'=>$item->is_view,
			           'is_critical'=>$is_critical,
			           'min_diff'=>$item->min_diff,
			           'whento_deliver'=>$item->whento_deliver,
			           'delivery_date'=>$item->delivery_date,
			           'delivery_time'=>$item->delivery_time,
			           'date_created'=>PrettyDateTime::parse(new DateTime($item->date_created)),
		    		);
		    	}
		    	
		    	$this->code = 1; $this->msg = "ok";
		    	$this->details = $data;				
		    	   	    
		    } else {
		    	$this->msg = t("You don't have current orders.");
		    	$this->details = array(
		    	  'image_url'=>CMedia::themeAbsoluteUrl()."/assets/images/order-best-food@2x.png"
		    	);
		    }    	
    		
    	} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		   
		   $this->details = array(
	    	  'image_url'=>CMedia::themeAbsoluteUrl()."/assets/images/order-best-food@2x.png"
	    	);
		}
		$this->responseJson();	
    }
     
    public function actionmostPopularItems()
	{		
		try {
			
			$data = array();
			$merchant_id = Yii::app()->merchant->merchant_id;
			$limit = Yii::app()->input->post('limit'); 
			$status_completed = AOrderSettings::getStatus(array('status_delivered','status_completed'));						
			
			$criteria=new CDbCriteria();
			$criteria->alias = "a";
			$criteria->select="
			a.item_id, 
			a.cat_id, 			
			sum(qty) as total_sold,
			b.photo , 
			b.path,
			CASE 
				WHEN item_trans.item_name IS NOT NULL AND item_trans.item_name != '' THEN item_trans.item_name
				ELSE item.item_name
			END AS item_name,

			CASE 
				WHEN category_trans.category_name IS NOT NULL AND category_trans.category_name != '' THEN category_trans.category_name
				ELSE category.category_name
			END AS category_name
			";

			$criteria->join='LEFT JOIN {{item}} b on  a.item_id = b.item_id 
			LEFT JOIN {{ordernew}} c on a.order_id = c.order_id 

			LEFT JOIN {{item}} item 
			ON a.item_id = item.item_id

			LEFT JOIN {{item_translation}} item_trans 
		    ON a.item_id = item_trans.item_id AND item_trans.language = '.q(Yii::app()->language).'

			LEFT JOIN {{category}} category 
			ON a.cat_id = category.cat_id

			LEFT JOIN {{category_translation}} category_trans 
		    ON a.cat_id = category_trans.cat_id AND category_trans.language = '.q(Yii::app()->language).'
			';						
			$criteria->condition = "c.merchant_id=:merchant_id";
			$criteria->params = array( 
			   ':merchant_id'=>$merchant_id			   
			);
			if(is_array($status_completed) && count($status_completed)>=1){			
			   $criteria->addInCondition('c.status', (array) $status_completed );
		    }		
			
			$criteria->group="a.item_id,a.cat_id";
			$criteria->order = "sum(qty) DESC";	
			$criteria->limit = intval($limit);
												
		    $model = AR_ordernew_item::model()->findAll($criteria); 
		    
		    if($model){
		       foreach ($model as $item) {		       	  
		       	  $total_sold = number_format($item->total_sold,0,'',',');
		       	  $data[] = array(
		       	    'item_name'=>CommonUtility::safeDecode($item->item_name),
		       	    'category_name'=>CommonUtility::safeDecode($item->category_name),
		       	    'total_sold'=>t("{{total_sold}} sold", array('{{total_sold}}'=>$total_sold) ),
		       	    'image_url'=>CMedia::getImage($item->photo,$item->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('item')),
		       	    'item_link' => Yii::app()->createAbsoluteUrl('/food/item_update',array('item_id'=>$item->item_id)),
		       	  );
		       }
		       
		       $this->code = 1; $this->msg = "ok";
		       $this->details = $data;
		    	   	    
            } else {
            	$this->msg = t("No item solds yet");   
            	$this->details = array(
		    	  'image_url'=>CMedia::themeAbsoluteUrl()."/assets/images/order-best-food@2x.png"
		    	);
            }         
            
			
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());			   		   
		   $this->details = array(
		    	  'image_url'=>CMedia::themeAbsoluteUrl()."/assets/images/order-best-food@2x.png"
		    	);
		}
		$this->responseJson();	
    }   
    
    public function actionmostPopularCustomer()
    {
    	try {
    		
    		$data = array();
			$merchant_id = Yii::app()->merchant->merchant_id;
			$limit = Yii::app()->input->post('limit'); 
			$not_in_status = AOrderSettings::getStatus(array('status_cancel_order','status_rejection'));		    
			$initial_status = AttributesTools::initialStatus();			
			if(is_array($not_in_status) && count($not_in_status)>=1){
				array_push($not_in_status, $initial_status);
			} else {
				$not_in_status = ['cancelled','rejected','draft'];
			}	
			
			$criteria=new CDbCriteria();
			$criteria->alias = "a";
			$criteria->select="a.client_id, count(*) as total_sold,
			b.first_name,b.last_name,b.date_created, b.avatar as logo, b.path
			";
			$criteria->join='LEFT JOIN {{client}} b on  a.client_id=b.client_id ';
			$criteria->condition = "a.merchant_id=:merchant_id and b.client_id IS NOT NULL";
			$criteria->params = array(':merchant_id'=>$merchant_id);
			
			if(is_array($not_in_status) && count($not_in_status)>=1){			
			   $criteria->addNotInCondition('a.status', (array) $not_in_status );
		    }		
			
			$criteria->group="a.client_id";
			$criteria->order = "count(*) DESC";	
			$criteria->limit = intval($limit);		    
			
		    $model = AR_ordernew::model()->findAll($criteria); 
		    if($model){		    	
		    	foreach ($model as $item) {
		    		$total_sold = number_format($item->total_sold,0,'',',');
		    		$data[] = array(
		    		  'client_id'=>$item->client_id,
		    		  'first_name'=>$item->first_name,
		    		  'last_name'=>$item->last_name,
		    		  'total_sold'=>t("{{total_sold}} orders", array('{{total_sold}}'=>$total_sold) ),
		    		  'member_since'=> t("Member since {{date_created}}" , array('{{date_created}}'=>Date_Formatter::dateTime($item->date_created)) ),
		    		  'image_url'=>CMedia::getImage($item->logo,$item->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('customer')),
		    		);
		    	}
		    	$this->code = 1; $this->msg = "ok";
		        $this->details = [
					'data'=>$data,
				];				
		    } else $this->msg = t("You don't have customer yet");
			
			$this->details['data2']=[
				'label'=>t("View All Customer"),
				'link'=>Yii::app()->createAbsoluteUrl("/customer/list")
			];
    		
    	} catch (Exception $e) {
		   $this->msg = t($e->getMessage());			   		   
		}
		$this->responseJson();	
    }
    
    public function actionsalesOverview()
    {
    	try {
    	
    		$data = array();
    		$merchant_id = Yii::app()->merchant->merchant_id;
    		$months = intval(Yii::app()->input->post('months')); 
    		
    		$status_completed = AOrderSettings::getStatus(array('status_delivered','status_completed'));		        		
    		$date_start = date("Y-m-d", strtotime(date("c")." -$months months"));
    		$date_end = date("Y-m-d");
    		
			Yii::app()->db->createCommand("SET SESSION sql_mode = (SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))")->query();
    		$criteria=new CDbCriteria();
    		$criteria->select = "
    		DATE_FORMAT(created_at, '%b') AS month , SUM(total) as monthly_sales
    		";
    		$criteria->group="MONTH(`created_at`)";
			$criteria->order = "created_at DESC";	
			
			$criteria->condition = "merchant_id=:merchant_id";
			$criteria->params = array(':merchant_id'=>$merchant_id);
			
			if(is_array($status_completed) && count($status_completed)>=1){			
			   $criteria->addInCondition('status', (array) $status_completed );
		    }				    
		    if(!empty($date_start) && !empty($date_end)){
				$criteria->addBetweenCondition("DATE_FORMAT(date_created,'%Y-%m-%d')", $date_start , $date_end );
			}
		        				    			
    		$model = AR_ordernew::model()->findAll($criteria); 
    		if($model){
    			$category = array(); $sales = array();
    			foreach ($model as $item) {    				
    				$category[] = t($item->month);
    				$sales[] = floatval($item->monthly_sales);
    			}
    			
    			$data = array(
    			  'category'=>$category,
    			  'data'=>$sales
    			);
    			
    			$this->code = 1; $this->msg = "ok";
		        $this->details = $data;
		        
    		} else {
    			$this->msg = t("You don't have sales yet");
    			$this->details = array(
		    	  'image_url'=>CMedia::themeAbsoluteUrl()."/assets/images/no-results2.png"
		    	);
    		}    	
    		
    	} catch (Exception $e) {
		   $this->msg = t($e->getMessage());			   		   
		   $this->details = array(
		    	  'image_url'=>CMedia::themeAbsoluteUrl()."/assets/images/order-best-food@2x.png"
		    	);
		}
		$this->responseJson();	
    }
    
    public function actionlatestReview()
    {
    	try {
    		
    		$data = array();
    		$merchant_id = Yii::app()->merchant->merchant_id;
    		$limit = intval(Yii::app()->input->post('limit')); 
    		
    		$criteria=new CDbCriteria();
    		$criteria->alias = "a";
    		$criteria->select = "a.client_id, a.review, a.rating,
    		b.first_name,b.last_name,b.date_created, b.avatar as logo, b.path
    		";
    		$criteria->join='LEFT JOIN {{client}} b on  a.client_id=b.client_id ';
    		$criteria->condition = "a.merchant_id=:merchant_id";
			$criteria->params = array(':merchant_id'=>$merchant_id);
    		    		
    		$model = AR_review::model()->findAll($criteria); 
    		if($model){
    			foreach ($model as $item) {    				
    				$data[] = array(
    				   'client_id'=>$item->client_id,
    				   'first_name'=>$item->first_name,
    				   'last_name'=>$item->last_name,
    				   'review'=>$item->review,
    				   'rating'=>$item->rating,
    				   'date_created'=>Date_Formatter::dateTime($item->date_created),
    				   'image_url'=>CMedia::getImage($item->logo,$item->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('customer')),
    				);
    			}
    			$this->code = 1; $this->msg = "ok";
		        $this->details = $data;
    		} else $this->msg = t("You don't have reviews yet");
    		
    	} catch (Exception $e) {
		   $this->msg = t($e->getMessage());			   		   
		}
		$this->responseJson();	
    }
    
    public function actionOverviewReview()
    {
    	try {
    	
    	    $data = array(); $total = 0;
    		$merchant_id = Yii::app()->merchant->merchant_id;
    		
    		$total = CReviews::reviewsCount($merchant_id);
    		$start = date('Y-m-01'); $end = date("Y-m-d");
    		$this_month = CReviews::totalCountByRange($merchant_id,$start,$end);
    		$user = CReviews::userAddedReview($merchant_id,4);
    		$review_summary = CReviews::summaryCount($merchant_id,$total);
    		
    		$data = array(
    		  'total'=>$total,
    		  'this_month'=>$this_month,
    		  'this_month_words'=>t("This month you got {{count}} New Reviews",array('{{count}}'=>$this_month)),
    		  'user'=>$user,
    		  'review_summary'=>$review_summary,
    		  'link_to_review'=>Yii::app()->createAbsoluteUrl('/customer/reviews')
    		);    		
    		
    		$this->code = 1; $this->msg = "ok";
		    $this->details = $data;
		        
    	} catch (Exception $e) {
		   $this->msg = t($e->getMessage());			   		   
		}
		$this->responseJson();	
    }
    
    public function actionitemSales()
    {
    	try {
    		    		
    		$data = array();
    	    $merchant_id = Yii::app()->merchant->merchant_id;
    		$period = Yii::app()->input->post('period'); 
    		
			Yii::app()->db->createCommand("SET SESSION sql_mode = (SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))")->query();
    		$data = CReports::ItemSales($merchant_id,$period);
    		$items = CReports::popularItems($merchant_id,$period);
    		/*dump($data);
    		die();*/
    		
			$this->code = 1; $this->msg = "ok";
	        $this->details = array(
	          'sales'=>$data,
	          'items'=>$items,
	        );
	        
    	} catch (Exception $e) {
		   $this->msg = t($e->getMessage());			   		   		   
		   $this->details = array(
	    	  'image_url'=>CMedia::themeAbsoluteUrl()."/assets/images/order-best-food@2x.png"
	    	);
		}
		$this->responseJson();	
    }
    
    public function actionsalesSummary()
    {
    	try {
    		    	    	   
    	   $card_id = 0;
    	   $merchant_id = Yii::app()->merchant->merchant_id;   
    	   
    	   $sales_week = CReports::SalesThisWeek($merchant_id);
    	   
    	   try {									
			    $card_id = CWallet::getCardID( Yii::app()->params->account_type['merchant'] , Yii::app()->merchant->merchant_id );
				$balance = CWallet::getBalance($card_id);
		   } catch (Exception $e) {			   
			   $balance = 0;		
		   }	
		   
		   $earning_week = CReports::EarningThisWeek($card_id);    
					
		   
    	   $data = array(
    	     'sales_week'=>$sales_week,
    	     'earning_week'=>$earning_week,
    	     'balance'=>$balance,
    	     'price_format'=>AttributesTools::priceUpFormat()
    	   );
    	   
    	   $this->code = 1; $this->msg = "ok";
    	   $this->details = $data;
    		
    	} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
    }
    
    public function actionreportSales()
    {
    	$merchant_id = Yii::app()->merchant->merchant_id;
    	$data = array();		
	    $status = COrders::statusList(Yii::app()->language);    	
	    $services = COrders::servicesList(Yii::app()->language);
	    
	    $payment_list = array();
        try {
           $payment_list = CPayments::PaymentList($merchant_id,true);            
        } catch (Exception $e) {
        	//
        }
	            	    
	    $page = isset($this->data['start'])?$this->data['start']:0;	
	    $length = isset($this->data['length'])?$this->data['length']:0;	
	    $draw = isset($this->data['draw'])?$this->data['draw']:0;	
	    $search = isset($this->data['search'])?$this->data['search']['value']:'';	
	    $columns = isset($this->data['columns'])?$this->data['columns']:'';
	    $order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
	    $filter = isset($this->data['filter'])?$this->data['filter']:'';
	    
	    $date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
	    $date_end = isset($this->data['date_end'])?$this->data['date_end']:'';
	            
	    $sortby = "order_id"; $sort = 'DESC';
	    
	    if(is_array($order) && count($order)>=1){
	        if(array_key_exists($order['column'],(array)$columns)){			
	            $sort = $order['dir'];
	            $sortby = $columns[$order['column']]['data'];
	        }
	    }
	            
	    if($page>0){
	       $page = intval($page)/intval($length);	
	    }
	    $criteria=new CDbCriteria();
	    $criteria->alias = "a";
	    $criteria->select = "a.order_id, a.client_id, a.status, a.order_uuid , 
	    a.payment_code, a.service_code,a.total, a.date_created,
		a.base_currency_code,
	    b.meta_value as customer_name, 
	    (
	       select sum(qty)
	       from {{ordernew_item}}
	       where order_id = a.order_id
	    ) as total_items,
	    
	    c.avatar as logo, c.path
	    ";
	    $criteria->join='LEFT JOIN {{ordernew_meta}} b on  a.order_id=b.order_id 
	    LEFT JOIN {{client}} c on  a.client_id=c.client_id
	    ';	         
	    
	    $criteria->condition = "a.merchant_id=:merchant_id AND b.meta_name=:meta_name ";
	    $criteria->params  = array(
	      ':merchant_id'=>intval($merchant_id),		  
	      ':meta_name'=>'customer_name'
	    );
	    if(!empty($date_start) && !empty($date_end)){
	        $criteria->addBetweenCondition("DATE_FORMAT(a.date_created,'%Y-%m-%d')", $date_start , $date_end );
	    }
	    
		$filter_order_status = null;
	    if(is_array($filter) && count($filter)>=1){
	        $filter_order_status = isset($filter['order_status'])?$filter['order_status']:'';
	        $filter_order_type = isset($filter['order_type'])?$filter['order_type']:'';
	        $filter_client_id = isset($filter['client_id'])?intval($filter['client_id']):'';
	        
	        if(!empty($filter_order_status)){
	            $criteria->addSearchCondition('a.status', $filter_order_status );
	        }
	        if(!empty($filter_order_type)){
	            $criteria->addSearchCondition('a.service_code', $filter_order_type );
	        }
	        if($filter_client_id>0){
	            $criteria->addSearchCondition('a.client_id', intval($filter_client_id) );
	        }
	    }

		if(!$filter_order_status){
			$exclude_status = AttributesTools::excludeStatus();
	        $criteria->addNotInCondition('a.status', (array) $exclude_status );
		}		
	    
	            
	    $criteria->order = "$sortby $sort";	    
	    $count = AR_ordernew::model()->count($criteria); 
	    $pages=new CPagination( intval($count) );
	    $pages->setCurrentPage( intval($page) );        
	    $pages->pageSize = intval($length);
	    $pages->applyLimit($criteria);     	    
	    $models = AR_ordernew::model()->findAll($criteria);   	    
	    if($models){
			 $price_format = CMulticurrency::getAllCurrency();			 
	         foreach ($models as $item) {         		
			         
				if($price_format){
					if(isset($price_format[$item->base_currency_code])){
						Price_Formatter::$number_format = $price_format[$item->base_currency_code];
					}						
				}

		         $trans_order_type = $item->service_code;
		         if(array_key_exists($item->service_code,$services)){
		             $trans_order_type = $services[$item->service_code]['service_name'];
		         }
		         
		         $payment_name = $item->payment_code;
		         if(array_key_exists($item->payment_code,(array)$payment_list)){
		         	$payment_name =$payment_list[$item->payment_code]['payment_name'];
		         }
		         
		         $avatar = CMedia::getImage($item->logo,$item->path,'@thumbnail',
		         CommonUtility::getPlaceholderPhoto('customer'));
		         
		         $item->total_items = intval($item->total_items);
		         $item->total_items = t("{{total_items}} items",array(
		          '{{total_items}}'=>$item->total_items
		         ));
		         
		         $status_trans = $item->status;
		         if(array_key_exists($item->status, (array) $status)){
		             $status_trans = $status[$item->status]['status'];
		         }
         
		         $status_class = str_replace(" ","_",$item->status);
		         
		         $place_on = t("Place on {{date}}",array(
		          '{{date}}'=>Date_Formatter::dateTime($item->date_created)
		         ));
		         
		         $view_order = Yii::app()->createUrl('orders/view',array(
		           'order_uuid'=>$item->order_uuid
		         ));
		         
		         $order_type_class = str_replace(" ","_",$item->service_code);
				         
		         
$information = <<<HTML
$item->total_items<span class="ml-2 badge order_status $status_class">$status_trans</span>
<p class="dim m-0">$place_on</p>
HTML;
		         

	             $data[]=array(	              
	              'client_id'=>'<img class="img-60 rounded-circle" src="'.$avatar.'">',
	              'order_id'=>'<a href="'.$view_order.'">'.$item->order_id.'</a>',
	              'order_uuid'=>$information,
	              'service_code'=>'<span class="badge services '.$order_type_class.' ">'.$trans_order_type.'</span>',
	              'payment_code'=>$payment_name,
	              'total'=>Price_Formatter::formatNumber($item->total),
	            );
	         }
	    }	
	    
	    $datatables = array(
	      'draw'=>intval($draw),
	      'recordsTotal'=>intval($count),
	      'recordsFiltered'=>intval($count),
	      'data'=>$data
	    );
	    $this->responseTable($datatables);		
    }
    
    public function actionreportSalesSummary()
    {
    
    	$merchant_id = Yii::app()->merchant->merchant_id;
    	$data = array();		
    	$status_completed = AOrderSettings::getStatus(array('status_delivered','status_completed'));
    	$size_list = AttributesTools::sizeList($merchant_id,Yii::app()->language);    	    	
    	$item_name_list = AttributesTools::itemNameList($merchant_id,Yii::app()->language);
    	
    	$page = isset($this->data['start'])?$this->data['start']:0;	
	    $length = isset($this->data['length'])?$this->data['length']:0;	
	    $draw = isset($this->data['draw'])?$this->data['draw']:0;	
	    $search = isset($this->data['search'])?$this->data['search']['value']:'';	
	    $columns = isset($this->data['columns'])?$this->data['columns']:'';
	    $order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'' )  :'';	
	    $filter = isset($this->data['filter'])?$this->data['filter']:'';
	    
	    $date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
	    $date_end = isset($this->data['date_end'])?$this->data['date_end']:'';
	    
	    $transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';		
	            
	    $sortby = "b.item_name"; $sort = 'ASC';
	    
	    if(is_array($order) && count($order)>=1){
	        if(array_key_exists($order['column'],(array)$columns)){			
	            $sort = $order['dir'];
	            $sortby = $columns[$order['column']]['data'];
	        }
	    }
	            
	    if($page>0){
	       $page = intval($page)/intval($length);	
	    }
	    $criteria=new CDbCriteria();
	    $criteria->alias = "a";
	    $criteria->select ="
	    a.item_id, a.size_id, a.price,
	    b.item_name, b.photo, b.path,
	    
	    (
	      select 
	      concat(
	        (price * SUM(qty)/SUM(qty)),';',
	        SUM(qty),';',	        
	        ((price * SUM(qty)/SUM(qty)) * SUM(qty))
	      )
	        
	      from {{ordernew_item}}
	      where item_id = a.item_id 
	      and item_size_id = a.item_size_id
	      and order_id IN (
	        select order_id from {{ordernew}}
	        where merchant_id = a.merchant_id
	        and status in (".CommonUtility::arrayToQueryParameters($status_completed).") 	        
	      )
	    ) as item_group
	    
	    ";
	    
	    $criteria->join='LEFT JOIN {{item}} b on  a.item_id = b.item_id	';	  
	    	    
		$criteria->condition = "a.merchant_id=:merchant_id AND b.item_name IS NOT NULL";
		$criteria->params = array(':merchant_id'=>$merchant_id);    
	    
		if(is_array($transaction_type) && count($transaction_type)>=1){
			$criteria->addInCondition('a.item_id',(array) $transaction_type );
		}		
	    
	    $criteria->order = "$sortby $sort";	    
	    $count = AR_item_relationship_size::model()->count($criteria); 
	    $pages=new CPagination( intval($count) );
	    $pages->setCurrentPage( intval($page) );        
	    $pages->pageSize = intval($length);
	    $pages->applyLimit($criteria);     	    
	    
	    if($model = AR_item_relationship_size::model()->findAll($criteria)){	    		    	
	    	foreach ($model as $item) {	    	    		
	    			    		
	    		$item_group = !empty($item->item_group) ? explode(";",$item->item_group) : '';
	    		$average_price = isset($item_group[0])?$item_group[0]:0;
	    		$total_qty = isset($item_group[1])?$item_group[1]:0;	    		
	    		$total = isset($item_group[2])?$item_group[2]:0;
	    		
	    		$photo = CMedia::getImage($item->photo,$item->path,'@thumbnail',
		        CommonUtility::getPlaceholderPhoto('item'));
		        
		        $size_name = '';
		        if(array_key_exists($item->size_id,(array)$size_list)){
		        	$size_name = $size_list[$item->size_id];
		        }
		        
		        $item_name = $item->item_name;
		        if(array_key_exists($item->item_id,(array)$item_name_list)){
		        	$item_name = $item_name_list[$item->item_id];		        	
		        }
	    				
	    		$data[] = array(
	    		  'item_id'=>$item->item_id,	    		  
	    		  'photo'=>'<img class="img-60 rounded-circle" src="'.$photo.'">',
	    		  'item_name'=>$item_name."<p class=\"m-0 text-muted font11\">$size_name</p>",
	    		  'price'=>Price_Formatter::formatNumber($average_price),
	    		  'qty'=>Price_Formatter::convertToRaw($total_qty,0),
	    		  'total'=>Price_Formatter::formatNumber($total)
	    		);
	    	}	    	
	    }
	    	    
	    $datatables = array(
	      'draw'=>intval($draw),
	      'recordsTotal'=>intval($count),
	      'recordsFiltered'=>intval($count),
	      'data'=>$data
	    );
	    $this->responseTable($datatables);		
    }
    
    public function actionitemSalesSummary()
    {
    	try {
    	
    	    $data = array();
    	    $merchant_id = Yii::app()->merchant->merchant_id;
    		$period = Yii::app()->input->post('period'); 
    		
    		$data = CReports::ItemSalesSummary($merchant_id,$period);    		
    		$items = CReports::popularItems($merchant_id,$period);
    		
    		$this->code = 1; $this->msg = "ok";
	        $this->details = array(
	          'sales'=>$data,
	          'items'=>$items,
	        );
    		
    	} catch (Exception $e) {
		   $this->msg = t($e->getMessage());			   		   		   
		   $this->details = array(
	    	  'image_url'=>CMedia::themeAbsoluteUrl()."/assets/images/no-results0.png"
	    	);
		}
		$this->responseJson();	
    }
    
    public function actionsupplierList()
    {
    	$data = array();
    	$merchant_id = Yii::app()->merchant->merchant_id;
	    $page = isset($this->data['start'])?$this->data['start']:0;	
	    $length = isset($this->data['length'])?$this->data['length']:0;	
	    $draw = isset($this->data['draw'])?$this->data['draw']:0;	
	    $search = isset($this->data['search'])?$this->data['search']['value']:'';	
	    $columns = isset($this->data['columns'])?$this->data['columns']:'';
	    $order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
	    $filter = isset($this->data['filter'])?$this->data['filter']:'';
	    
	    $date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
	    $date_end = isset($this->data['date_end'])?$this->data['date_end']:'';
	            
	    $sortby = "supplier_id"; $sort = 'DESC';
	    
	    if(is_array($order) && count($order)>=1){
	        if(array_key_exists($order['column'],(array)$columns)){			
	            $sort = $order['dir'];
	            $sortby = $columns[$order['column']]['data'];
	        }
	    }
	    
	    $page = $page>0? intval($page)/intval($length) : 0;	
        $criteria=new CDbCriteria();
        $criteria->condition = "merchant_id=:merchant_id ";
	    $criteria->params  = array(
	      ':merchant_id'=>intval($merchant_id)	      
	    );
    	
	    if(!empty($date_start) && !empty($date_end)){
	        $criteria->addBetweenCondition("DATE_FORMAT(date_created,'%Y-%m-%d')", $date_start , $date_end );
	    }
	    
	    if (is_string($search) && strlen($search) > 0){
		   $criteria->addSearchCondition('supplier_name', $search );
		   $criteria->addSearchCondition('contact_name', $search , true , 'OR' );
		}
	    
	    $criteria->order = "$sortby $sort";
	    $count = AR_supplier::model()->count($criteria); 
	    $pages=new CPagination( intval($count) );
	    $pages->setCurrentPage( intval($page) );        
	    $pages->pageSize = intval($length);
	    $pages->applyLimit($criteria);        	    
	    if($model = AR_supplier::model()->findAll($criteria)){
	    	foreach ($model as $item) {
	    		

$information = <<<HTML
$item->contact_name
<p class="dim m-0">$item->email</p>
<p class="dim m-0">$item->phone_number</p>
HTML;


	    		$data[] = array(
	    		  'supplier_id'=>$item->supplier_id,
	    		  'supplier_name'=>Yii::app()->input->xssClean($item->supplier_name),
	    		  'contact_name'=>$information,
	    		  'created_at'=>$item->created_at,
	    		  'update_url'=>Yii::app()->createAbsoluteUrl('/supplier/update',array('id'=>$item->supplier_id)),
	    		  'delete_url'=>Yii::app()->createAbsoluteUrl('/supplier/delete',array('id'=>$item->supplier_id)),
	    		);
	    	}
	    }
	    $datatables = array(
	      'draw'=>intval($draw),
	      'recordsTotal'=>intval($count),
	      'recordsFiltered'=>intval($count),
	      'data'=>$data
	    );
	    $this->responseTable($datatables);
    }
    
    public function actionarchiveOrderList()
    {
    	$data = array();
    	$merchant_id = Yii::app()->merchant->merchant_id;
    	$status_list = COrders::statusList(Yii::app()->language);     
	    $services = COrders::servicesList(Yii::app()->language);
	    $payment_list = AttributesTools::PaymentProvider();	    	    
	    
	    $page = isset($this->data['start'])?$this->data['start']:0;	
	    $length = isset($this->data['length'])?$this->data['length']:0;	
	    $draw = isset($this->data['draw'])?$this->data['draw']:0;	
	    $search = isset($this->data['search'])?$this->data['search']['value']:'';	
	    $columns = isset($this->data['columns'])?$this->data['columns']:'';
	    $order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
	    $filter = isset($this->data['filter'])?$this->data['filter']:'';
	    
	    $date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
	    $date_end = isset($this->data['date_end'])?$this->data['date_end']:'';
	    
	    $sortby = "order_id"; $sort = 'DESC';
    
	    if(is_array($order) && count($order)>=1){
	        if(array_key_exists($order['column'],(array)$columns)){			
	            $sort = $order['dir'];
	            $sortby = $columns[$order['column']]['data'];
	        }
	    }
	    
	    $page = $page>0? intval($page)/intval($length) : 0;	
	    $criteria=new CDbCriteria();
	    $criteria->alias = "a";
	    $criteria->select = "
	    a.order_id, a.client_id, a.json_details, a.total_w_tax, a.status, a.trans_type, a.date_created,
	    a.payment_type,
	    b.first_name, b.last_name , b.avatar, b.path,
	    (
	      select count(*) from {{order_details}}
	      where order_id = a.order_id
	    ) as total_items
	    ";
	    
	    $criteria->join='LEFT JOIN {{client}} b on  a.client_id = b.client_id';
	                
	    $criteria->condition = "a.merchant_id=:merchant_id ";
	    $criteria->params  = array(
	      ':merchant_id'=>intval($merchant_id),	      
	    );
    
	    if(!empty($date_start) && !empty($date_end)){
	        $criteria->addBetweenCondition("DATE_FORMAT(a.date_created,'%Y-%m-%d')", $date_start , $date_end );
	    }	    
	    $criteria->addNotInCondition('a.status', array('initial_order') );
	    
	    
	    if(is_array($filter) && count($filter)>=1){	    
		    $filter_order_status = isset($filter['order_status'])?$filter['order_status']:'';
		    $filter_order_type = isset($filter['order_type'])?$filter['order_type']:'';
		    $filter_client_id = isset($filter['client_id'])?intval($filter['client_id']):'';
		    
			if(!empty($filter_order_status)){
				$criteria->addSearchCondition('a.status', $filter_order_status );
			}
			if(!empty($filter_order_type)){
				$criteria->addSearchCondition('a.trans_type', $filter_order_type );
			}
			if($filter_client_id>0){
				$criteria->addSearchCondition('a.client_id', intval($filter_client_id) );
			}
		}
	    
	    
	    $criteria->order = "$sortby $sort";	    
	    $count = AR_order::model()->count($criteria); 
	    $pages=new CPagination( intval($count) );
	    $pages->setCurrentPage( intval($page) );        
	    $pages->pageSize = intval($length);
	    $pages->applyLimit($criteria);     
	    
	    if($model = AR_order::model()->findAll($criteria)){	    	
	    	foreach ($model as $item) { 	    	

	    		$item->total_items = intval($item->total_items);
			    $item->total_items = t("{{total_items}} items",array(
			     '{{total_items}}'=>$item->total_items
			    ));
    
	    		$avatar = CMedia::getImage($item->avatar,$item->path,'@thumbnail',
		         CommonUtility::getPlaceholderPhoto('customer'));
		         
		        $status_class = str_replace(" ","_",$item->status);
		        $status_trans = $item->status;
			    if(array_key_exists($item->status, (array) $status_list)){
			        $status_trans = $status_list[$item->status]['status'];
			    }
			    
			    $trans_order_type = $item->trans_type;
			    if(array_key_exists($item->trans_type,$services)){
			        $trans_order_type = $services[$item->trans_type]['service_name'];
			    }
    
			    $order_type = t("Order Type.");
                $order_type.="<span class='ml-2 services badge $item->trans_type'>$trans_order_type</span>";
                
                $total = t("Total. {{total}}",array(
			     '{{total}}'=>Price_Formatter::formatNumber($item->total_w_tax)
			    ));
			    $place_on = t("Place on {{date}}",array(
			     '{{date}}'=>Date_Formatter::dateTime($item->date_created)
			    ));
			    
			    $payment_type = $item->payment_type;
			    if(array_key_exists($item->payment_type,(array)$payment_list)){
			    	$payment_type = $payment_list[$item->payment_type];
			    }
			    
		         
$information = <<<HTML
$item->total_items<span class="ml-2 badge order_status $status_class">$status_trans</span>
<p class="dim m-0">$payment_type</p>
<p class="dim m-0">$order_type</p>
<p class="dim m-0">$total</p>
<p class="dim m-0">$place_on</p>
HTML;


	    		    		
		    	$data[] = array(		    	  
		    	  'avatar'=>'<img class="img-60 rounded-circle" src="'.$avatar.'">',
		    	  'order_id'=>$item->order_id,
		    	  'client_id'=>$item->first_name." ".$item->last_name,
		    	  'json_details'=>$information,
		    	);
	    	}
	    }
	    	    
	    $datatables = array(
	      'draw'=>intval($draw),
	      'recordsTotal'=>intval($count),
	      'recordsFiltered'=>intval($count),
	      'data'=>$data
	    );
	    $this->responseTable($datatables);		
    }
    
    public function actionDailyStatistic()
    {   
    	try	{
    		
    		$merchant_id = Yii::app()->merchant->merchant_id;
    		$status_new = AOrderSettings::getStatus(array('status_new_order'));
    		$status_delivered = AOrderSettings::getStatus(array('status_delivered','status_completed'));
    		
    		$order_received = CReports::OrderTotalByStatus($merchant_id,$status_new);
    		$today_delivered = CReports::OrderTotalByStatus($merchant_id,$status_delivered);
    		$total_refund = CReports::TotalRefund($merchant_id);
    		$today_sales =  CReports::SalesThisWeek($merchant_id,0,$status_delivered);
    		
    		$data = array(
    		  'order_received'=>$order_received,
    		  'today_delivered'=>$today_delivered,    		  
    		  'total_refund'=>$total_refund,
    		  'today_sales'=>$today_sales,
    		  'price_format'=>AttributesTools::priceUpFormat()
    		);    		
    		$this->code = 1; $this->msg = "ok";
		    $this->details = $data;
    		
    	} catch (Exception $e) {
		   $this->msg = t($e->getMessage());			   		   		   		
		}
		$this->responseJson();	
    }
    
    public function actioncreateOrder()
    {
    	try {	
    		    		
			$merchant_id = Yii::app()->merchant->merchant_id;
			$transaction_type = CCheckout::getFirstTransactionType($merchant_id,Yii::app()->language);			

    		$order_uuid = CPos::createOrder($merchant_id,$transaction_type);		
    		$this->code = 1; $this->msg = "ok";
		    $this->details = array(
		      'order_uuid'=>$order_uuid,
			  'order_type'=>$transaction_type,
		      //'order_type'=>AttributesTools::PosCode(),
		    );
    		
    	} catch (Exception $e) {
		   $this->msg = t($e->getMessage());			   		   		   		
		}
		$this->responseJson();	
    }
    
    public function actionresetPos()
    {
        try {	
        	
        	$order_uuid = Yii::app()->input->post('order_uuid'); 
        	CPos::resetPos($order_uuid);
        	
        	$this->code = 1;
        	$this->msg = "ok";
       	
       	} catch (Exception $e) {
		   $this->msg = t($e->getMessage());			   		   		   		
		}
		$this->responseJson();	
    }
    
    public function actionremoveItem(){
    	try {	
        	
        	$item_row = Yii::app()->input->post('item_row');         	
        	$model = AR_ordernew_item::model()->find("item_row=:item_row",array(
        	 ':item_row'=>$item_row
        	));
        	
        	if($model){
        	   $model->scenario = "remove";
        	   if($model->delete()){
				   $order = COrders::getByID($model->order_id);				   
				   COrders::updateServiceFee($order->order_uuid,$order->service_code);
				   COrders::updateSummary($order->order_uuid);
			   }
        	   $this->code = 1;
        	   $this->msg = "ok";
        	} else $this->msg = t("Item row not found");        	        	
       	
       	} catch (Exception $e) {
		   $this->msg = t($e->getMessage());			   		   		   		
		}
		$this->responseJson();	
    }
    
    public function actionupdatePosQty()
    {
    	try {	
    		        	        	
        	$qty = intval(Yii::app()->input->post('qty'));  
        	$item_row = Yii::app()->input->post('item_row'); 
        	
        	$model = AR_ordernew_item::model()->find("item_row=:item_row",array(
        	 ':item_row'=>$item_row
        	));
        	if($model){  				
        		$model->scenario = "update_item_qty";  		        		
        		$model->qty = $qty;
        		if($model->save()){
        		   $this->code = 1;
        	       $this->msg = "ok";

				   $order = COrders::getByID($model->order_id);				   
				   COrders::updateServiceFee($order->order_uuid,$order->service_code);				   
				   COrders::updateSummary($order->order_uuid);

        		} else $this->msg = CommonUtility::parseError( $model->getErrors());
        	} else $this->msg = t("Item row not found"); 
        	
       	} catch (Exception $e) {
		   $this->msg = t($e->getMessage());			   		   		   		
		}
		$this->responseJson();	
    }
    
    public function actionapplyPromoCode()
    {
    	try {
    		
    		$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';
    		$promo_code = isset($this->data['promo_code'])?$this->data['promo_code']:'';
    		
    		$order = COrders::get($order_uuid);
    		
    		$model = AR_voucher::model()->find("merchant_id=:merchant_id AND voucher_name=:voucher_name",array(
    		  ':merchant_id'=>$order->merchant_id,
    		  ':voucher_name'=>$promo_code
    		));
    		if($model){
    			$promo_id = $model->voucher_id;
    			$now = date("Y-m-d");
    			$sub_total = $order->sub_total;
    			
    			$resp = CPromos::applyVoucher( $order->merchant_id, $promo_id, $order->client_id , $now , $sub_total);
    			
    			$less_amount = $resp['less_amount'];    
    			$promo_type = "voucher";
				$params = array(
				  'name'=>"less voucher",
				  'type'=>$promo_type,
				  'id'=>$promo_id,
				  'target'=>'subtotal',
				  'value'=>"-$less_amount",
				);		
				
				$order->promo_code = isset($resp['voucher_name'])?$resp['voucher_name']:'';
				$order->promo_total = isset($resp['less_amount'])?floatval($resp['less_amount']):0;
				if($order->save()){
				   COrders::savedAttributes($order->order_id,'promo',json_encode($params));
			       COrders::savedAttributes($order->order_id,'promo_type',$promo_type);
			       COrders::savedAttributes($order->order_id,'promo_id',$promo_id);    			
			       $this->code = 1;
        	       $this->msg = "ok";

				   COrders::updateServiceFee($order_uuid,$order->service_code);
				   COrders::updateSummary($order_uuid);

				} else {					 
					 $this->msg = CommonUtility::parseError($order->getErrors());
				}
    		} else $this->msg = t("Voucher code not found");    		    		
    		
    	} catch (Exception $e) {
		   $this->msg = t($e->getMessage());			   		   		   		
		}
		$this->responseJson();	
    }
    
    public function actionremovevoucher()
    {
    	try {
    		
    		$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';
    		$order = COrders::get($order_uuid);
    		$order->promo_code = '';
			$order->promo_total = 0;
			if($order->save()){
			   $this->code = 1;
        	   $this->msg = "ok";
        	           	   
        	   $criteria=new CDbCriteria();
        	   $criteria->condition = "order_id=:order_id";
        	   $criteria->params = array(':order_id'=>$order->order_id);
        	   $criteria->addInCondition('meta_name', array('promo_id','promo_type','promo'));        	  
        	   AR_ordernew_meta::model()->deleteAll($criteria);

			   COrders::updateServiceFee($order_uuid,$order->service_code);
        	   
			} else $this->msg = CommonUtility::parseError($order->getErrors());
    		
    	} catch (Exception $e) {
		   $this->msg = t($e->getMessage());			   		   		   		
		}
		$this->responseJson();	
    }
    
    public function actioncreatecustomer()
    {
    	try {
    		    		
    		$first_name = isset($this->data['first_name'])?$this->data['first_name']:'';
    		$last_name = isset($this->data['last_name'])?$this->data['last_name']:'';
    		$email_address = isset($this->data['email_address'])?$this->data['email_address']:'';
    		$contact_phone = isset($this->data['contact_phone'])?$this->data['contact_phone']:'';
    		
    		$model = new AR_client();
    		$model->first_name = $first_name;
    		$model->last_name = $last_name;
    		$model->email_address = $email_address;
    		$model->contact_phone = $contact_phone;
    		if($model->save()){
    		   $this->code = 1;
        	   $this->msg = t("Customer succesfully created");
        	   $this->details = array(
        	     'client_id'=>$model->client_id,
        	     'client_uuid'=>$model->client_uuid,
        	     'client_name'=>"$first_name $last_name"
        	   );
    		} else {    			
    			$this->msg = CommonUtility::parseModelErrorToString($model->getErrors(),"<br/>");
    		}
    		
    	} catch (Exception $e) {
		   $this->msg = t($e->getMessage());			   		   		   		
		}
		$this->responseJson();	
    }
    
    public function actionpaymentList()
    {
    	try {
					   
		   $data = CPayments::PaymentList(Yii::app()->merchant->merchant_id);		   		   
		   $payment_code = isset($data[0])? $data[0]['payment_code'] : '';		   
		   $this->code = 1;
		   $this->msg = "ok";
		   $this->details = array(		     
		     'data'=>$data,
		     'default_payment'=>$payment_code
		   );		   
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
    }

	public function actionposattributes()
	{
		try {
		
		   $merchant_id = Yii::app()->merchant->merchant_id;
		   $data = CPayments::PaymentList($merchant_id);
		   $payment_code = isset($data[0])? $data[0]['payment_code'] : '';		
		   
		   $transaction_list = []; $transaction_type= ''; $order_status = 'new';
		   try {
		      $transaction_list = CCheckout::getMerchantTransactionList($merchant_id,Yii::app()->language);		
			  $transaction_type = CCheckout::getFirstTransactionType($merchant_id,Yii::app()->language);
		   } catch (Exception $e) {
		   }

		   $order_status_list = AttributesTools::getOrderStatus(Yii::app()->language,'order_status',true);		   
		   if($order_status_list){			  
			  $order_status = $order_status_list[0]['value'];
		   }

		   $room_list = [];
		   $room_list = CommonUtility::getDataToDropDown("{{table_room}}","room_uuid","room_name","WHERE merchant_id=".q($merchant_id)." ","order by room_name asc");                
		   if(is_array($room_list) && count($room_list)>=1){
			  $room_list = CommonUtility::ArrayToLabelValue($room_list);   
		   }		   

		   $table_list = [];
		   try{
			  $table_list = CBooking::getTableList($merchant_id);		
		   } catch (Exception $e) {
		   }
		   
		   $additional_list = [
			  //'service_fee'=>t("Service Fee"),
			  'delivery_fee'=>t("Delivery Fee"),
			  'courier_tip'=>t("Courier Tips"),
		   ];

		   $delivery_option = CCheckout::deliveryOptionList();	

		   $options = OptionsTools::find(array('website_time_picker_interval'));
           $interval = isset($options['website_time_picker_interval'])?$options['website_time_picker_interval']." mins":'20 mins';		   

		   // CHECK IF MERCHANT HAS DIFFERENT TIMEZONE
		   $options_merchant = OptionsTools::find(['merchant_time_picker_interval','merchant_timezone'],$merchant_id);
		   $interval_merchant = isset($options_merchant['merchant_time_picker_interval'])? ( !empty($options_merchant['merchant_time_picker_interval']) ? $options_merchant['merchant_time_picker_interval']." mins" :''):'';
		   $interval = !empty($interval_merchant)?$interval_merchant:$interval;
		   $merchant_timezone = isset($options_merchant['merchant_timezone'])?$options_merchant['merchant_timezone']:'';
		   if(!empty($merchant_timezone)){
			  Yii::app()->timezone = $merchant_timezone;
		   }
		   $opening_hours = CMerchantListingV1::openHours($merchant_id,$interval);
		   
		   $this->code = 1;
		   $this->msg = "ok";
		   $this->details = array(		     
		     'data'=>$data,
		     'default_payment'=>$payment_code,
			 'transaction_list'=>$transaction_list,
			 'transaction_type'=>$transaction_type,
			 'order_status_list'=>$order_status_list,
			 'order_status'=>$order_status,
			 'room_list'=>$room_list,
			 'table_list'=>$table_list,
			 'additional_list'=>$additional_list,
			 'delivery_option'=>$delivery_option,
			 'opening_hours'=>$opening_hours,
		   );		   
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}
    
	public function actionapplydiscount()
	{
		try {
			
			$order_uuid = Yii::app()->input->post('order_uuid');
			$discount = floatval(Yii::app()->input->post('discount'));
			if($discount<=0){
				$this->msg = t("Invalid discount");
				$this->responseJson();
			}
			
			$order = COrders::get($order_uuid);
			$sub_total = $order->sub_total;
					
			$less_amount = $sub_total*($discount/100);
			$order->offer_discount = $discount;
			$order->offer_total = floatval($less_amount);

			if($order->save()){
    			$this->code = 1;
		        $this->msg = t("Discount added successfully");
			
				COrders::updateServiceFee($order_uuid,$order->service_code);
				COrders::updateSummary($order_uuid);

    		} else $this->msg = CommonUtility::parseModelErrorToString($order->getErrors(),"<br/>");

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionremovediscount()
	{
		try {

			$order_uuid = Yii::app()->input->post('order_uuid');
			$order = COrders::get($order_uuid);
			$order->offer_discount = 0;
			$order->offer_total = 0;
			if($order->save()){
    			$this->code = 1;
		        $this->msg = "Ok";
				
				COrders::updateServiceFee($order_uuid,$order->service_code);
				COrders::updateSummary($order_uuid);
				
    		} else $this->msg = CommonUtility::parseModelErrorToString($order->getErrors(),"<br/>");
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionaddadditionalfee()
	{
		try {

			$order_uuid = Yii::app()->input->post('order_uuid');
			$additional_fee = floatval(Yii::app()->input->post('additional_fee'));
			$additional_fee_type = trim(Yii::app()->input->post('additional_fee_type'));
			
			if($additional_fee<=0){
				$this->msg = t("Invalid amount");
				$this->responseJson();
			}
			if(empty($additional_fee_type)){
				$this->msg = t("Select additional fee type");
				$this->responseJson();
			}
			
			$order = COrders::get($order_uuid);		

			if($additional_fee_type=="service_fee"){
				$order->service_fee = $additional_fee;
			} else if($additional_fee_type=="delivery_fee"){
				$order->delivery_fee = $additional_fee;
			} else if($additional_fee_type=="packaging_fee"){
				$order->packaging_fee = $additional_fee;
			} else if($additional_fee_type=="courier_tip"){
				$order->courier_tip = $additional_fee;
			} else {
				$this->msg = t("Invalid additional fee");
				$this->responseJson();
			}
														
			if($order->save()){
    			$this->code = 1;
		        $this->msg = t("Additional fee added successfully");
				COrders::updateServiceFee($order_uuid,$order->service_code);
				COrders::updateSummary($order_uuid);
    		} else $this->msg = CommonUtility::parseModelErrorToString($order->getErrors(),"<br/>");

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionremoveAdditionalFee()
	{
		try {
			
			$order_uuid = Yii::app()->input->post('order_uuid');
			$additional_fee_type = trim(Yii::app()->input->post('additional_fee_type'));
			$additional_fee = 0;

			$order = COrders::get($order_uuid);
			if($additional_fee_type=="service_fee"){
				$order->service_fee = $additional_fee;
			} else if($additional_fee_type=="delivery_fee"){
				$order->delivery_fee = $additional_fee;
			} else if($additional_fee_type=="packaging_fee"){
				$order->packaging_fee = $additional_fee;
			} else if($additional_fee_type=="tip"){
				$order->courier_tip = $additional_fee;
			} else {
				$this->msg = t("Invalid additional fee");
				$this->responseJson();
			}

			if($order->save()){
    			$this->code = 1;
		        $this->msg = t("Additional fee added successfully");
				COrders::updateServiceFee($order_uuid,$order->service_code);
				COrders::updateSummary($order_uuid);
    		} else $this->msg = CommonUtility::parseModelErrorToString($order->getErrors(),"<br/>");

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionudatepostransaction()
	{
		try {			
			$order_uuid = Yii::app()->input->post('order_uuid');
			$transaction_type = trim(Yii::app()->input->post('transaction_type'));
			$order = COrders::get($order_uuid);								
			$order->service_code = $transaction_type;

			if($order->save()){
    			$this->code = 1;
		        $this->msg = "Ok";				
				COrders::updateServiceFee($order_uuid,$transaction_type);
				COrders::updateSummary($order_uuid);			
    		} else $this->msg = CommonUtility::parseModelErrorToString($order->getErrors(),"<br/>");

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

    public function actionsubmitPOSOrder()
    {
    	try {
    		 			
			$atts = OptionsTools::find(['multicurrency_enabled']);
			$multicurrency_enabled = isset($atts['multicurrency_enabled'])?$atts['multicurrency_enabled']:false;
			$multicurrency_enabled = $multicurrency_enabled==1?true:false;        

    		$stats = AOrderSettings::getStatus(array('status_completed'));
    		$status_completed = isset($stats[0])?$stats[0]:'complete';
    		
    		$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';
    		$receive_amount = isset($this->data['receive_amount'])?$this->data['receive_amount']:0;    		
    		$payment_code = isset($this->data['payment_code'])?$this->data['payment_code']:'';
    		$order_notes = isset($this->data['order_notes'])?$this->data['order_notes']:'';    		
    		$client_id = isset($this->data['client_id'])?intval($this->data['client_id']):'';
    		$order_change = isset($this->data['order_change'])?floatval($this->data['order_change']):0;    		

			$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:''; 
			$order_status = isset($this->data['order_status'])?$this->data['order_status']:$status_completed; 
			$guest_number = isset($this->data['guest_number'])?intval($this->data['guest_number']):0;
			$room_id = isset($this->data['room_id'])?$this->data['room_id']:''; 
			$table_id = isset($this->data['table_id'])?$this->data['table_id']:''; 
			$payment_reference = isset($this->data['payment_reference'])?$this->data['payment_reference']:''; 

			$whento_deliver = isset($this->data['whento_deliver'])?$this->data['whento_deliver']:'now'; 
			$delivery_date = isset($this->data['delivery_date'])?$this->data['delivery_date']:''; 
			$delivery_time = isset($this->data['delivery_time'])?$this->data['delivery_time']:''; 

			$admin_base_currency = AttributesTools::defaultCurrency();
			$base_currency_code = Price_Formatter::$number_format['currency_code'];
			$exchange_rate = 1; $exchange_rate_use_currency_to_admin = 1; $exchange_rate_merchant_to_admin = 1;
			$exchange_rate_admin_to_merchant = 1;

			if($multicurrency_enabled){
				if($base_currency_code!=$admin_base_currency){
					$exchange_rate_merchant_to_admin = CMulticurrency::getExchangeRate($base_currency_code,$admin_base_currency);
					$exchange_rate_admin_to_merchant = CMulticurrency::getExchangeRate($admin_base_currency,$base_currency_code);
				}
			}
    		
    		$model = COrders::get($order_uuid);			
    		$model->status = $order_status;
    		$model->payment_status = 'paid';
			$model->service_code = $transaction_type;
    		$model->payment_code  = $payment_code;
    		$model->client_id = intval($client_id);
    		$model->use_currency_code = $base_currency_code;
			$model->base_currency_code = $base_currency_code;
			$model->admin_base_currency = $admin_base_currency;
			$model->exchange_rate = $exchange_rate;	
			$model->exchange_rate_use_currency_to_admin = $exchange_rate_use_currency_to_admin;
			$model->exchange_rate_merchant_to_admin = $exchange_rate_merchant_to_admin;
			$model->exchange_rate_admin_to_merchant = $exchange_rate_admin_to_merchant;

			$model->room_id = $room_id;
			$model->table_id = $table_id;
			$model->payment_reference = $payment_reference;
			
			$args = array(); $customer_name = '';
			try {
				$customer = ACustomer::get($client_id);
				$customer_name = $customer->first_name." ".$customer->last_name;				
			} catch (Exception $e) {
				$customer_name = 'Walk-in Customer';
			}	
			
			$metas = array();
			$metas['customer_name'] = $customer_name;
			if(!empty($order_notes)){
			   $metas['order_notes'] = $order_notes;
			}
			if($order_change>0){
			   $metas['order_change'] = floatval($order_change);
			}
			if($receive_amount>0){
			   $metas['receive_amount'] = floatval($receive_amount);
			}
			if($guest_number>0){
				$metas['guest_number'] = intval($guest_number);
			}

			if($transaction_type=="dinein"){
				$metas['guest_number'] = $guest_number;
				try {			
					$model_room = CBooking::getRoom($room_id); 
					$metas['room_id'] = $model_room->room_id;
				} catch (Exception $e) {					
				}

				try {			
					$model_table = CBooking::getTable($table_id); 					
					$metas['table_id'] = $model_table->table_id;
				} catch (Exception $e) {					
				}
			}			
			
			$model->meta = $metas;	
			$model->remarks = "Order created by {{merchant_user}}";
			$args = array(
			  '{{merchant_user}}'=> Yii::app()->merchant->first_name,
			);
			$model->ramarks_trans = json_encode($args);		
			$model->change_by = Yii::app()->merchant->first_name;
			$model->request_from = AttributesTools::PosCode();
			$model->merchant_earning_original = $model->merchant_earning;
			$model->commission_original = $model->commission;
			$model->total_original = $model->total;
			$model->adjustment_commission = 0;
			$model->adjustment_total = 0;

			$model->whento_deliver = $whento_deliver;
			if($whento_deliver=="now"){
				$model->delivery_date = CommonUtility::dateNow();
			} else {
				$model->delivery_date = $delivery_date;
				$model->delivery_time = $delivery_time;
			}
					
			if($model->service_code=="delivery"){
				if(empty($model->formatted_address)){
					$this->msg = t("Delivery address is required");
					$this->responseJson();
				}
			}			
			$model->scenario = "pos_entry";					

    		if($model->save()){
    			$this->code = 1;
		        $this->msg = "ok";		        		        
    		} else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors(),"<br/>");
    		
    	} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
    }
    
    public function actionposhistory()
    {
    	$data = array();		
    	$status = COrders::statusList(Yii::app()->language);    	
    	$services = COrders::servicesList(Yii::app()->language);
    	$payment_list = AttributesTools::PaymentProvider();	
    	    	
		$merchant_id = Yii::app()->merchant->merchant_id;
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
		$filter = isset($this->data['filter'])?$this->data['filter']:'';
		
		$date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
		$date_end = isset($this->data['date_end'])?$this->data['date_end']:'';
				
		$sortby = "order_id"; $sort = 'DESC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = $page>0? intval($page)/intval($length) : 0;	
		$criteria=new CDbCriteria();
		$criteria->alias = "a";
		$criteria->select = "a.order_id, a.client_id, a.status, a.order_uuid , 
		a.payment_code, a.service_code,a.total, a.date_created,
		a.base_currency_code,
		b.meta_value as customer_name, 
		(
		   select sum(qty)
		   from {{ordernew_item}}
		   where order_id = a.order_id
		) as total_items,
		
		c.avatar as logo, c.path
		";
		$criteria->join='
		LEFT JOIN {{ordernew_meta}} b on  a.order_id=b.order_id 
		LEFT JOIN {{client}} c on  a.client_id = c.client_id 
		';
		$criteria->condition = "a.merchant_id=:merchant_id AND meta_name=:meta_name AND request_from=:request_from";
		$criteria->params  = array(
		  ':merchant_id'=>intval($merchant_id),		  
		  ':meta_name'=>'customer_name',
		  ':request_from'=>AttributesTools::PosCode()
		);				
		
		if(!empty($date_start) && !empty($date_end)){
			$criteria->addBetweenCondition("DATE_FORMAT(a.date_created,'%Y-%m-%d')", $date_start , $date_end );
		}
		$initial_status = AttributesTools::initialStatus();
		$criteria->addNotInCondition('a.status', (array) array($initial_status) );
		
		if(is_array($filter) && count($filter)>=1){
		    $filter_order_status = isset($filter['order_status'])?$filter['order_status']:'';
		    $filter_order_type = isset($filter['order_type'])?$filter['order_type']:'';
		    $filter_client_id = isset($filter['client_id'])?intval($filter['client_id']):'';
		    
			if(!empty($filter_order_status)){
				$criteria->addSearchCondition('a.status', $filter_order_status );
			}
			if(!empty($filter_order_type)){
				$criteria->addSearchCondition('a.service_code', $filter_order_type );
			}
			if($filter_client_id>0){
				$criteria->addSearchCondition('a.client_id', intval($filter_client_id) );
			}
		}
				
		$criteria->order = "$sortby $sort";
		$count = AR_ordernew::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        
                        		
        $models = AR_ordernew::model()->findAll($criteria);
                
        if($models){
			$price_format = CMulticurrency::getAllCurrency();
         	foreach ($models as $item) {         		
		  
			if($price_format){				
				if(isset($price_format[$item->base_currency_code])){
					Price_Formatter::$number_format = $price_format[$item->base_currency_code];
				}						
			}

         	$item->total_items = intval($item->total_items);
         	$item->total_items = t("{{total_items}} items",array(
         	 '{{total_items}}'=>$item->total_items
         	));
         	
         	$trans_order_type = $item->service_code;
         	if(array_key_exists($item->service_code,$services)){
         		$trans_order_type = $services[$item->service_code]['service_name'];
         	}
         	
         	$order_type = t("Order Type.");
         	$order_type.="<span class='ml-2 services badge $item->service_code'>$trans_order_type</span>";
         	
         	$total = t("Total. {{total}}",array(
         	 '{{total}}'=>Price_Formatter::formatNumber($item->total)
         	));
         	$place_on = t("Place on {{date}}",array(
         	 '{{date}}'=>Date_Formatter::dateTime($item->date_created)
         	));
         	
         	$status_trans = $item->status;
         	if(array_key_exists($item->status, (array) $status)){
         		$status_trans = $status[$item->status]['status'];
         	}
         	
         	$view_order = Yii::app()->createUrl('orders/view',array(
         	  'order_uuid'=>$item->order_uuid
         	));
         	
         	$print_pdf = Yii::app()->createUrl('print/pdf',array(
         	  'order_uuid'=>$item->order_uuid
         	));
         	
         	$status_class = str_replace(" ","_",$item->status);
         	         	
         	if(array_key_exists($item->payment_code,(array)$payment_list)){
	            $item->payment_code = $payment_list[$item->payment_code];
	        }
			        
	        $avatar = CMedia::getImage($item->logo,$item->path,'@thumbnail',
		         CommonUtility::getPlaceholderPhoto('customer'));
		         
         		
$information = <<<HTML
$item->total_items<span class="ml-2 badge order_status $status_class">$status_trans</span>
<p class="dim m-0">$item->payment_code</p>
<p class="dim m-0">$order_type</p>
<p class="dim m-0">$total</p>
<p class="dim m-0">$place_on</p>
HTML;

$buttons = <<<HTML
<div class="btn-group btn-group-actions" role="group">
 <a href="$view_order" target="_blank" class="btn btn-light tool_tips"><i class="zmdi zmdi-eye"></i></a>
 <a href="$print_pdf" target="_blank"  class="btn btn-light tool_tips"><i class="zmdi zmdi-download"></i></a>
</div>
HTML;

         		$data[]=array(
         		  'logo'=>'<img class="img-60 rounded-circle" src="'.$avatar.'">',
        		  'order_id'=>$item->order_id,
        		  'client_id'=>$item->customer_name,
        		  'status'=>$information,
        		  'order_uuid'=>$buttons
        		);
         	}
        }	
        
        $datatables = array(
		  'draw'=>intval($draw),
		  'recordsTotal'=>intval($count),
		  'recordsFiltered'=>intval($count),
		  'data'=>$data
		);
		$this->responseTable($datatables);		
    }    
    

    public function actionrefundreport()
    {
    
    	$merchant_id = Yii::app()->merchant->merchant_id;
    	$status = COrders::statusList(Yii::app()->language);    	
    	$payment_list = AttributesTools::PaymentProvider();       
    	 
        
    	$data = array();		
    	
    	$page = isset($this->data['start'])?$this->data['start']:0;	
	    $length = isset($this->data['length'])?$this->data['length']:0;	
	    $draw = isset($this->data['draw'])?$this->data['draw']:0;	
	    $search = isset($this->data['search'])?$this->data['search']['value']:'';	
	    $columns = isset($this->data['columns'])?$this->data['columns']:'';
	    $order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
	    $filter = isset($this->data['filter'])?$this->data['filter']:'';
	    
	    $date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
	    $date_end = isset($this->data['date_end'])?$this->data['date_end']:'';
	    
	    $transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';	    
	            
	    $sortby = "a.date_created"; $sort = 'DESC';
	    
	    if(is_array($order) && count($order)>=1){
	        if(array_key_exists($order['column'],(array)$columns)){			
	            $sort = $order['dir'];
	            $sortby = $columns[$order['column']]['data'];
	        }
	    }
	    
	            
	    if($page>0){
	       $page = intval($page)/intval($length);	
	    }
	    $criteria=new CDbCriteria();
	    $criteria->alias = "a";
	    $criteria->select ="a.client_id,a.order_id,a.transaction_description,a.payment_code,
	    a.trans_amount, a.status, a.payment_reference, a.date_created, a.currency_code,
	    b.avatar as photo, b.path,
	    c.order_uuid
	    ";	    
	    $criteria->join='
	    LEFT JOIN {{client}} b on  a.client_id = b.client_id
	    LEFT JOIN {{ordernew}} c on  a.order_id = c.order_id
	    ';	  
	    
		$criteria->condition = "a.merchant_id=:merchant_id";
		$criteria->params = array(':merchant_id'=>$merchant_id);    
		$criteria->addInCondition('a.transaction_name', array('refund','partial_refund') );
		if(!empty($date_start) && !empty($date_end)){
			$criteria->addBetweenCondition("DATE_FORMAT(a.date_created,'%Y-%m-%d')", $date_start , $date_end );
		}
		if(is_array($transaction_type) && count($transaction_type)>=1){
			$criteria->addInCondition('a.status',(array) $transaction_type );
		}
			    		
	    $criteria->order = "$sortby $sort";	    
	    $count = AR_ordernew_transaction::model()->count($criteria); 
	    $pages=new CPagination( intval($count) );
	    $pages->setCurrentPage( intval($page) );        
	    $pages->pageSize = intval($length);
	    $pages->applyLimit($criteria);     	    
	    	    
	    if($model = AR_ordernew_transaction::model()->findAll($criteria)){	    	
			$price_format = CMulticurrency::getAllCurrency();	    		    				
	    	foreach ($model as $item) {	  

				if($price_format){
					if(isset($price_format[$item->currency_code])){
						Price_Formatter::$number_format = $price_format[$item->currency_code];
					}						
				}
	    		$avatar = CMedia::getImage($item->photo,$item->path,'@thumbnail',
		         CommonUtility::getPlaceholderPhoto('customer'));		         
		        $date = t("Refund on {{date}}",array(
		         '{{date}}'=>Date_Formatter::dateTime($item->date_created)
		        ));
		        $status_class = CommonUtility::removeSpace($item->status);
		        $status_trans = $item->status;
		         if(array_key_exists($item->status, (array) $status)){
		             $status_trans = $status[$item->status]['status'];
		         }
		        $transaction_description = Yii::app()->input->xssClean($item->transaction_description);
		        $reference = t("Payment reference# {{payment_reference}}",array(
		          '{{payment_reference}}'=>$item->payment_reference
		        ));
		        
		        $view_order = Yii::app()->createUrl('orders/view',array(
		           'order_uuid'=>$item->order_uuid
		         ));
	    		    		
$information = <<<HTML
$transaction_description<span class="ml-2 badge payment $status_class">$status_trans</span>
<p class="font12 dim m-0">$date</p>
<p class="font12 dim m-0">$reference</p>
HTML;
		         		         
	    		$data[] = array(	    	
	    		  'date_created'=>$item->date_created,
	    		  'client_id'=>'<img class="img-60 rounded-circle" src="'.$avatar.'">',
	    		  'order_id'=>'<a href="'.$view_order.'">'.$item->order_id.'</a>',
	    		  'transaction_description'=>$information,
	    		  'payment_code'=> isset($payment_list[$item->payment_code])?$payment_list[$item->payment_code]:$item->payment_code ,
	    		  'trans_amount'=>Price_Formatter::formatNumber($item->trans_amount),	    		  
	    		);
	    	}	    	
	    }
	    	    
	    $datatables = array(
	      'draw'=>intval($draw),
	      'recordsTotal'=>intval($count),
	      'recordsFiltered'=>intval($count),
	      'data'=>$data
	    );
	    $this->responseTable($datatables);		
    }    
   
    public function actionMerchantPaymentPlans()
    {
    	
        $data = array();    	
        $payment_gateway = AttributesTools::PaymentProvider();
		
    	$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')  :'';	
		
		$date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
		$date_end = isset($this->data['date_end'])?$this->data['date_end']:'';
		$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';		
		$filter = isset($this->data['filter'])?$this->data['filter']:'';	
		$merchant_id = Yii::app()->merchant->merchant_id;
				
		$sortby = "created"; $sort = 'DESC';
    		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = intval($page)/intval($length);		
		$criteria=new CDbCriteria();
		$criteria->alias = "a";
		$criteria->select="a.merchant_id, a.invoice_number,a.invoice_ref_number,a.created,a.amount,a.status,
		a.payment_code,
		b.title , c.restaurant_name , c.logo, c.path
		";
		$criteria->join='
		LEFT JOIN {{plans_translation}} b on  a.package_id=b.package_id 
		LEFT JOIN {{merchant}} c on  a.merchant_id = c.merchant_id 
		';				
		
		$params = array();
		$criteria->addCondition("b.language=:language and c.restaurant_name IS NOT NULL AND TRIM(c.restaurant_name) <> ''");
		$params['language'] = Yii::app()->language;
		
		$criteria->addCondition('a.merchant_id=:merchant_id');
        $params['merchant_id']  = intval($merchant_id);
        
		$criteria->params = $params;
		
		if(!empty($date_start) && !empty($date_end)){
			$criteria->addBetweenCondition("DATE_FORMAT(a.created,'%Y-%m-%d')", $date_start , $date_end );
		}
		if(is_array($transaction_type) && count($transaction_type)>=1){
			$criteria->addInCondition('a.status',(array) $transaction_type );
		}		       
				
		$criteria->order = "$sortby $sort";
		$count = AR_plans_invoice::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);                
        $models = AR_plans_invoice::model()->findAll($criteria);        
        if($models){
        	foreach ($models as $item) {
        		$avatar = CMedia::getImage($item->logo,$item->path,'@thumbnail',
		         CommonUtility::getPlaceholderPhoto('merchant'));
		         
		         $status = $item->status;
		         $created = t("Created {{date}}",array(
		           '{{date}}'=>Date_Formatter::dateTime($item->created)
		         )); 
		         
		         $plan_title = Yii::app()->input->xssClean($item->title);
		         $amount = Price_Formatter::formatNumber($item->amount);
		         

		         $view_merchant =  Yii::app()->createUrl('/vendor/edit',array(
				    'id'=>$item->merchant_id
				  ));
		         
$invoice = <<<HTML
<p class="m-0">$item->invoice_ref_number</p>
<div class="badge customer $item->status payment">$status</div>
HTML;

$plan = <<<HTML
<p class="m-0">$plan_title</p>
<p class="m-0 text-muted font11">$amount</p>
HTML;


        		$data[]=array(        		  
        		  'logo'=>'<a href="'.$view_merchant.'"><img class="img-60 rounded-circle" src="'.$avatar.'"></a>',
        		  'created'=>Date_Formatter::dateTime($item->created),        		  
        		  'payment_code'=>isset($payment_gateway[$item->payment_code])?$payment_gateway[$item->payment_code]:$item->payment_code,
        		  'invoice_ref_number'=>$invoice,
        		  'package_id'=>$plan,           		  
        		);
        	}
        }
        
        $datatables = array(
		  'draw'=>intval($draw),
		  'recordsTotal'=>intval($count),
		  'recordsFiltered'=>intval($count),
		  'data'=>$data
		);
        
        $this->responseTable($datatables);
    }    
    
	public function actionbannerList()
	{
		$data = array();		
						
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
						
		$sortby = "date_created"; $sort = 'DESC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = intval($page)/intval($length);		
		$criteria=new CDbCriteria();
		$criteria->condition="owner=:owner AND meta_value1=:meta_value1";
		$criteria->params = array(':owner'=> 'merchant',':meta_value1'=>Yii::app()->merchant->merchant_id);

		if(!empty($search)){
			$criteria->addSearchCondition('title', $search );
			$criteria->addSearchCondition('banner_type', $search );
		}		
				
		$criteria->order = "$sortby $sort";
		$count = AR_banner::model()->count($criteria); 
		
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        
        $models = AR_banner::model()->findAll($criteria);
        if($models){
        	foreach ($models as $item) {        	
				
				$photo = CMedia::getImage($item->photo,$item->path,'@thumbnail',
		         CommonUtility::getPlaceholderPhoto('customer'));

				 $checkbox = Yii::app()->controller->renderPartial('/attributes/html_checkbox',array(
					'id'=>"banner[$item->banner_uuid]",
					'check'=>$item->status==1?true:false,
					'value'=>$item->banner_uuid,
					'label'=>'',		
					'class'=>'set_banner_status'
				),true);

        		$data[]=array(				 
			      'banner_id'=>$item->banner_id,
				  'photo'=>'<img class="img-60" src="'.$photo.'">',
				  'status'=>$checkbox, 
				  'title'=>$item->title,				  
				  'banner_type'=>$item->banner_type,				  
        		  'date_created'=>Date_Formatter::dateTime($item->date_created),
				  'update_url'=>Yii::app()->createUrl("/merchant/banner_update/",array('id'=>$item->banner_uuid)),
        		  'delete_url'=>Yii::app()->createUrl("/merchant/banner_delete/",array('id'=>$item->banner_uuid)),				  
				);
        	}        	
        }
        
         $datatables = array(
		  'draw'=>intval($draw),
		  'recordsTotal'=>intval($count),
		  'recordsFiltered'=>intval($count),
		  'data'=>$data
		);				
		$this->responseTable($datatables);
	}

	public function actionpages_list()
	{
		$data = array();		
		$status_list = AttributesTools::StatusManagement('post');
		
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])? ( isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
						
		$sortby = "date_created"; $sort = 'DESC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = intval($page)/intval($length);		
		$criteria=new CDbCriteria();
		$criteria->condition="owner=:owner AND merchant_id=:merchant_id";
		$criteria->params = array(':owner'=> 'merchant',':merchant_id'=>Yii::app()->merchant->merchant_id);

		if(!empty($search)){
			$criteria->addSearchCondition('title', $search );			
		}		
				
		$criteria->order = "$sortby $sort";
		$count = AR_pages::model()->count($criteria); 
		
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        
        $models = AR_pages::model()->findAll($criteria);
        if($models){
        	foreach ($models as $item) {        	
							
				$title = '<h6>[title] <span class="badge ml-2 post [status]">[status_title]</span></h6>
				<p class="dim">[short_content]</p>';
				$title = t($title,[
					'[title]'=>$item->title,
					'[status]'=>$item->status,
					'[status_title]'=>isset($status_list[$item->status])?$status_list[$item->status]:$item->status,
					'[short_content]'=>$item->short_content,
				]);

        		$data[]=array(				 
			      'page_id'=>$item->page_id,
				  'title'=>$title,
				  'update_url'=>Yii::app()->createUrl("/merchant/page_update/",array('id'=>$item->page_id)),
        		  'delete_url'=>Yii::app()->createUrl("/merchant/pages_delete/",array('id'=>$item->page_id)),				  
				);
        	}        	
        }
        
         $datatables = array(
		  'draw'=>intval($draw),
		  'recordsTotal'=>intval($count),
		  'recordsFiltered'=>intval($count),
		  'data'=>$data
		);				
		$this->responseTable($datatables);
	}

	public function actionMenuList()
    {
    	try {
    		     		
    		$data = array();
			try {
			    $data = MMenumerchant::getMenu(0,PPages::menuMerchantType(),Yii::app()->merchant->merchant_id);
			} catch (Exception $e) {
			   //	
            }
    		
    		$current_menu = AR_merchant_meta::getValue(Yii::app()->merchant->merchant_id,PPages::menuActiveKey() );
    		$current_menu = isset($current_menu['meta_value'])?$current_menu['meta_value']:0;
    		
    		$this->code = 1;
    		$this->msg = "ok";
    		$this->details = array(
    		  'data'=>$data,
    		  'current_menu'=>intval($current_menu)
    		);
    		
    	} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
    }

	public function actionAllPages()
    {
    	try {    		
    		$data = PPages::merchantPages(Yii::app()->language,Yii::app()->merchant->merchant_id);
    		$this->code = 1;
    		$this->msg = "ok";
    		$this->details = $data;
    		
    	} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
    }

    public function actiongetMenuDetails()
    {
    	try {
    		
    		$current_menu = Yii::app()->input->post('current_menu');     		
    		$model = AR_menu::model()->findByPk(intval($current_menu));
    		if($model){
    			
    			$data = array();
    			try {
    			    $data = MMenumerchant::getMenu($current_menu,PPages::menuMerchantType(),Yii::app()->merchant->merchant_id);
    			} catch (Exception $e) {
    			   //	
                }
    			
	    		$this->code = 1;
	    		$this->msg = "ok";
	    		$this->details = array(
	    		  'menu_name'=>$model->menu_name,
	    		  'sequence'=>$model->sequence,
	    		  'data'=>$data
	    		);
    		} else $this->msg = t(Helper_not_found);
    		    		
    	} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
    }
    	
    public function actioncreateMenu()
    {
    	try {
    		    		
    		$menu_name = isset($this->data['menu_name'])?$this->data['menu_name']:'';
    		$menu_id = isset($this->data['menu_id'])?intval($this->data['menu_id']):0;
    		$child_menu = isset($this->data['child_menu'])?$this->data['child_menu']:'';
			
    		if($menu_id>0){    			 
    			 $model = MMenumerchant::get($menu_id,PPages::menuMerchantType(),Yii::app()->merchant->merchant_id);
    		} else $model = new AR_menu();    		
    		
    		$model->scenario = "theme_menu_merchant";
    		
    		$model->menu_type = PPages::menuMerchantType();
    		$model->menu_name = $menu_name;
			$model->meta_value1 = intval(Yii::app()->merchant->merchant_id);
    		$model->child_menu = $child_menu;
    		if($model->save()){
    			$this->code = 1;
		        $this->msg = t("Succesful");
    		} else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors(),"<br/>");
    		
    	} catch (Exception $e) {			
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
    }	

    public function actionaddpagetomenu()
    {
    	try {
    		    		
    		$menu_id = isset($this->data['menu_id'])?intval($this->data['menu_id']):0;
    		$pages = isset($this->data['pages'])?$this->data['pages']:array();    		
    		if(is_array($pages) && count($pages)>=1){
    			foreach ($pages as $page_id) {
    				$page = PPages::get($page_id);    
    						
    				$model = new AR_menu();
    				$model->menu_type=PPages::menuMerchantType();
    				$model->menu_name = $page->title;
    				$model->parent_id = $menu_id;
    				$model->link = '{{site_url}}/'.$page->slug;
					$model->meta_value1 = intval(Yii::app()->merchant->merchant_id);
    				$model->save();
    			}
    		}
    		
    		$this->code = 1;
	    	$this->msg = t(Helper_success);
    		
    	} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
    }    

	public function actiondeletemenu()
    {
    	try {

    		$menu_id = intval(Yii::app()->input->post('menu_id'));  
    		
    		$model = AR_menu::model()->find("menu_id=:menu_id AND menu_type=:menu_type AND meta_value1=:meta_value1",array(
			   ':menu_id'=>intval($menu_id),
			   ':menu_type'=>PPages::menuMerchantType(),
			   ':meta_value1'=>intval(Yii::app()->merchant->merchant_id)
			 ));
			 			
			if($model){			   
			   $model->scenario = "theme_menu_merchant";		
			   $model->delete();
			   $this->code = 1;
	    	   $this->msg = t(Helper_success);
			} else $this->msg = t(Helper_not_found);
    		
    	} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
    }
    	
    public function actionaddCustomPageToMenu()
    {
    	try {
    		    		
    		$menu_id = isset($this->data['menu_id'])?intval($this->data['menu_id']):0;
    		$custom_link_text = isset($this->data['custom_link_text'])?trim($this->data['custom_link_text']):'';
    		$custom_link = isset($this->data['custom_link'])?trim($this->data['custom_link']):'';
    		
    		$model = new AR_menu();
    		$model->scenario = "custom_link";
    		$model->menu_type=PPages::menuMerchantType();
			$model->menu_name = $custom_link_text;
			$model->parent_id = $menu_id;
			$model->link = $custom_link;
			$model->meta_value1 = intval(Yii::app()->merchant->merchant_id);

			if($model->save()){
			   $this->code = 1;
	    	   $this->msg = t(Helper_success);
			} else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors(),"<br/>");
    		
    	} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
    }    	

	public function actionremoveChildMenu()
    {
    	try {
    		    		
    		$menu_id = intval(Yii::app()->input->post('menu_id'));  
    		$model = MMenumerchant::get($menu_id,PPages::menuMerchantType(),intval(Yii::app()->merchant->merchant_id));
    		if($model){
    			$model->delete();
    			$this->code = 1;
	    		$this->msg = "ok";
    		} else $this->msg = t(Helper_not_found);
    		
    	} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
    }

	public function actionitemList()
	{
		$data = array();
		
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
		$filter = isset($this->data['filter'])?$this->data['filter']:'';
		
		$date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
		$date_end = isset($this->data['date_end'])?$this->data['date_end']:'';		
				
		$sortby = "zone_id"; $sort = 'DESC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}

		$merchant_id = Yii::app()->merchant->merchant_id;
		
		$page = $page>0? intval($page)/intval($length) :0;
		$criteria=new CDbCriteria();		
		$criteria->alias = "a";
		$criteria->select = "
		a.* ,
		(
			select GROUP_CONCAT(cat_id)
			from {{item_relationship_category}}
			where merchant_id = a.merchant_id
			and item_id = a.item_id
		) as group_category,

		(
			select GROUP_CONCAT(CONCAT_WS(';', size_id, price , discount, discount_type,discount_start,discount_end))
			from {{item_relationship_size}}
			where merchant_id = a.merchant_id
			and item_id = a.item_id
		) as prices,

		IF(COALESCE(NULLIF(b.item_name, ''), '') = '', a.item_name, b.item_name) as item_name

		";
		$criteria->join = "
		left JOIN (
			SELECT item_id,item_name  FROM {{item_translation}} where language=".q(Yii::app()->language)."
		) b 
		ON a.item_id = b.item_id
		";

		$criteria->condition = "a.merchant_id=:merchant_id AND visible=:visible";		
		$criteria->params = [
			':merchant_id'=>intval($merchant_id),
			':visible'=>1
		];
		
		if (is_string($search) && strlen($search) > 0){
			$criteria->addSearchCondition('a.item_name', $search );			
		 }
				
		$criteria->order = "$sortby $sort";
		$count = AR_item::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);      
		
		$fallback_image = CommonUtility::getPlaceholderPhoto('item_photo');
                        		
        $models = AR_item::model()->findAll($criteria);
        if($models){        				
			$category_list = AttributesTools::Category($merchant_id,Yii::app()->language);			
			$size_list = AttributesTools::sizeList($merchant_id,Yii::app()->language);
			$status_list = AttributesTools::StatusManagement('post',Yii::app()->language);			
			$date_now = date("Y-m-d");

			$html_duplicate = '';
			if($addon_available = CommonUtility::checkModuleAddon("Karenderia MenuClone")){
				$html_duplicate = '<a class="ref_duplicate normal btn btn-light tool_tips"><i class="zmdi zmdi-collection-item"></i></a>';
			}

        	foreach ($models as $item) {   								
				$pic = CMedia::getImage($item->photo,$item->path,'@thumbnail',$fallback_image);							
				$checkbox = Yii::app()->controller->renderPartial('/attributes/html_checkbox',array(
					'id'=>"available[$item->item_id]",
					'check'=>$item->available==1?true:false,
					'value'=>$item->item_id,
					'label'=>'',
					'class'=>'set_item_available'
				  ),true);

				$category_group = '';
				//$category = explode(",",$item->group_category);
				$category = explode(",", $item->group_category ?? '');
				if(is_array($category) && count($category)>=1){
					foreach ($category as $cat_id) {
						if(isset($category_list[$cat_id])){
							$category_group.=$category_list[$cat_id].",";
						}						
					}
					$category_group = substr($category_group,0,-1);
				}

				$size_group = '';
				//$size = explode(",",$item->prices);								
				$size = explode(",", $item->prices ?? '');
				if(is_array($size) && count($size)>=1){
					foreach ($size as $size_val) {
						//$size_item = explode(";",$size_val);												
						$size_item = explode(";", $size_val ?? '');
						$size_id = isset($size_item[0])?$size_item[0]:0;
						$price = isset($size_item[1])?$size_item[1]:0;
						$discount = isset($size_item[2])?$size_item[2]:0;
						$discount_type = isset($size_item[3])?$size_item[3]:'';
						$discount_start = isset($size_item[4])?$size_item[4]:'';
						$discount_end = isset($size_item[5])?$size_item[5]:'';

						$price_after_discount = 0;
						if(!empty($discount_start) && !empty($discount_end)){
							if ($date_now >= $discount_start && $date_now <=$discount_end ) {
								if($discount_type=="percentage"){
									$price_after_discount = $price - (($discount/100)*$price);
								} else $price_after_discount = $price-$discount;
							}
						}
						
						if($size_id>0){
							if(isset($size_list[$size_id])){
								if($price_after_discount>0){
									$size_group.='<p class="m-0 text-small">'.$size_list[$size_id].' <del>'.Price_Formatter::formatNumber($price) .'</del>'. Price_Formatter::formatNumber($price_after_discount) .'</p>';								
								} else $size_group.='<p class="m-0 text-small">'.$size_list[$size_id]." ".Price_Formatter::formatNumber($price).'</p>';								
							}
						} else {							
							if($price_after_discount>0){
								$size_group.='<p class="m-0 text-small"><del>'.Price_Formatter::formatNumber($price).'</del></p>';
							} else $size_group.='<p class="m-0 text-small">'.Price_Formatter::formatNumber($price).'</p>';							
						}
					}
				}

				$item_status = isset($status_list[$item->status])?$status_list[$item->status]:t($item->status);				
				$editLink = Yii::app()->createUrl("/food/item_update/",array('item_id'=>$item->item_id));				
				$deletetLink = Yii::app()->createUrl("/food/item_delete/",array('id'=>$item->item_id));

$action = <<<HTML
<div class="btn-group btn-group-actions" role="group">
  <a class="normal btn btn-light tool_tips" href="$editLink" ><i class="zmdi zmdi-border-color"></i></a>
  <a class="ref_delete normal btn btn-light tool_tips" ><i class="zmdi zmdi-delete"></i></a>		
  $html_duplicate
</div>
HTML;

        		$data[]=array(
        		  'item_id'=>'<img src="'.CHtml::encode($pic).'" class="img-60 rounded-circle" />',  
				  'available'=>$checkbox,
				  'item_name'=>'<h6>'.$item->item_name.'<span class="badge ml-2 post '.$item->status.'">'.$item_status.'</span></h6>
				  <p class="dim">				 	   		 	   
					'. t('Last Modified. [date_modified]',[
						'[date_modified]'=>Date_Formatter::dateTime($item->date_modified)
					]) .'<br>
					'.t("SKU#[sku]",[
						'[sku]'=>$item->sku
					]).'
				  </p>
				  ',
				  'category_group'=>'<p class="font-weight-bold ellipsis-3-lines" style="max-width:200px;" >'.$category_group.'</p>',
				  'price'=>$size_group,		
				  'action'=>$action,		
				  'update_url'=>Yii::app()->createUrl("/food/item_update/",array('item_id'=>$item->item_id)),
        		  'delete_url'=>Yii::app()->createUrl("/food/item_delete/",array('id'=>$item->item_id)),				  
				  'duplicate_url'=>Yii::app()->createUrl("/food/item_duplicate/",array('id'=>$item->item_id)),					  
        		);
        	}
        }

        $datatables = array(
		  'draw'=>intval($draw),
		  'recordsTotal'=>intval($count),
		  'recordsFiltered'=>intval($count),
		  'data'=>$data
		);
				
		$this->responseTable($datatables);
	}

	public function actionbankdepositlist()
	{
		$data = array(); 
		$merchant_id = Yii::app()->merchant->merchant_id;

		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
		
		$date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
		$date_end = isset($this->data['date_end'])?$this->data['date_end']:'';
		$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';		
				
		$sortby = "deposit_id"; $sort = 'DESC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}				
		
		if($sortby=="deposit_uuid"){
			$sortby = "deposit_id";
		}

		$page = $page>0? intval($page)/intval($length) :0;
		$criteria=new CDbCriteria();
		$criteria->alias="a";
		$criteria->select ="a.*,
		(
			select order_uuid from {{ordernew}}
			where order_id=a.transaction_ref_id
		) as order_uuid
		";

		$criteria->addCondition("merchant_id=:merchant_id");
		$criteria->params = [
			':merchant_id'=>intval($merchant_id)
		];

		if(!empty($date_start) && !empty($date_end)){
			$criteria->addBetweenCondition("DATE_FORMAT(date_created,'%Y-%m-%d')", $date_start , $date_end );
		}

		if(!empty($search)){
			$criteria->addSearchCondition('transaction_ref_id', $search );
			$criteria->addSearchCondition('account_name', $search , true , 'OR' );
			$criteria->addSearchCondition('reference_number', $search , true , 'OR' );
		}

		$criteria->order = "$sortby $sort";
		$count = AR_bank_deposit::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria); 
								
        if($models = AR_bank_deposit::model()->findAll($criteria)){

			foreach ($models as $item) {

			 $link = CMedia::getImage($item->proof_image,$item->path);			 
			 $order_link = Yii::app()->CreateUrl("/orders/view/",[
				'order_uuid'=>$item->order_uuid
			 ]);

			 $status = t($item->status);
			 $bg_badge = $item->status=="pending"?'badge-warning':'badge-success';

$image = <<<HTML
<a href="$link" class="btn btn-light btn-sm" target="_blank">View</a>
HTML;

$order_ref = <<<HTML
<a href="$order_link"  target="_blank">$item->transaction_ref_id</a>
<span class="badge ml-2 $bg_badge">$status</span>
HTML;

				$data[]=array(
					'deposit_id'=>$item->deposit_id,
					'deposit_uuid'=>$item->deposit_uuid,
					'date_created'=>Date_Formatter::dateTime($item->date_created),
					'proof_image'=>$image,
					'deposit_type'=>$item->deposit_type,
					'transaction_ref_id'=>$order_ref,
					'account_name'=>$item->account_name,
					'amount'=>Price_Formatter::formatNumber($item->amount),
					'reference_number'=>$item->reference_number,
					'view_url'=>Yii::app()->createUrl("/merchant/bank_deposit_view/",array('id'=>$item->deposit_uuid)),
					'delete_url'=>Yii::app()->createUrl("/merchant/bank_deposit_delete/",array('id'=>$item->deposit_uuid)),
				);
			}
		}
		$datatables = array(
			'draw'=>intval($draw),
			'recordsTotal'=>intval($count),
			'recordsFiltered'=>intval($count),
			'data'=>$data
		  );
				  
		  $this->responseTable($datatables);
	}

	public function actionFPprint()
	{
		try {

			$merchant_id = Yii::app()->merchant->merchant_id;
			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';			
			$printer_id = isset($this->data['printer_id'])?$this->data['printer_id']:'';
			
			$model = AR_printer::model()->find("merchant_id=:merchant_id AND printer_id=:printer_id",[
                ":merchant_id"=>$merchant_id,
                ':printer_id'=>intval($printer_id)
            ]);
			if($model){
				$meta = AR_printer_meta::getMeta($printer_id,['printer_user','printer_ukey','printer_sn','printer_key']);				
                $printer_user = isset($meta['printer_user'])?$meta['printer_user']['meta_value1']:'';
                $printer_ukey = isset($meta['printer_ukey'])?$meta['printer_ukey']['meta_value1']:'';
                $printer_sn = isset($meta['printer_sn'])?$meta['printer_sn']['meta_value1']:'';
                $printer_key = isset($meta['printer_key'])?$meta['printer_key']['meta_value1']:'';

				$order_id = 0;
                $summary = array(); $order_status = array();                                
                $order_delivery_status = array(); $merchant_info=array();
                $order = array(); $items = array();

                COrders::getContent($order_uuid,Yii::app()->language);                				

                $merchant_info = COrders::getMerchant($merchant_id,Yii::app()->language);				
                $items = COrders::getItems();				
                $summary = COrders::getSummary();
                $order = COrders::orderInfo();
				$order_id = $order['order_info']['order_id'];

				$credit_card_details = '';
				$payment_code = $order['order_info']['payment_code'];
				if($payment_code=="ocr"){
					try {
						$credit_card_details = COrders::getCreditCard2($order_id);			
						$order['order_info']['credit_card_details'] = $credit_card_details;		
					} catch (Exception $e) {
						//
					}
				}				
								
                $tpl = FPtemplate::ReceiptTemplate(
                  $model->paper_width,
                  $order['order_info'],
                  $merchant_info,
                  $items,$summary
               );   	
			   $stime = time();
               $sig = sha1($printer_user.$printer_ukey.$stime);               
               $result = FPinterface::Print($printer_user,$stime,$sig,$printer_sn,$tpl);

			   $model = new AR_printer_logs();
			   $model->order_id = intval($order_id);
			   $model->merchant_id = intval($merchant_id);
			   $model->printer_number = $printer_sn;
			   $model->print_content = $tpl;
			   $model->job_id = $result;
			   $model->status = 'process';
			   $model->save();
               
               $this->code = 1;
               $this->msg = t("Request succesfully sent to printer");
               $this->details = $result;		   

			} else $this->msg = t(HELPER_RECORD_NOT_FOUND);
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();	
	}

	public function actioninvoiceList()
    {
        $page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')  :'';	
		$date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
		$date_end = isset($this->data['date_end'])?$this->data['date_end']:'';

		$sortby = "invoice_number"; $sort = 'DESC';

		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = intval($page)/intval($length);		
		$criteria=new CDbCriteria();
		$criteria->condition = "merchant_id=:merchant_id";
		$criteria->params  = array(
		  ':merchant_id'=>intval(Yii::app()->merchant->merchant_id),		  
		);
		
		if(!empty($date_start) && !empty($date_end)){
			$criteria->addBetweenCondition("DATE_FORMAT(invoice_created,'%Y-%m-%d')", $date_start , $date_end );
		}

		$data = [];
		
		$criteria->order = "$sortby $sort";
		$count = AR_invoice::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        
        $model = AR_invoice::model()->findAll($criteria);		
		if($model){			
			foreach ($model as $items) {				
				
				$view = Yii::app()->CreateUrl("/invoicemerchant/view",[
					'invoice_uuid'=>$items->invoice_uuid
				]);

				$upload = Yii::app()->CreateUrl("/invoicemerchant/uploaddeposit",[
					'invoice_uuid'=>$items->invoice_uuid
				]);

				$pdf = Yii::app()->CreateUrl("/invoicemerchant/pdf",[
					'invoice_uuid'=>$items->invoice_uuid
				]);

$action = <<<HTML
<div class="btn-group btn-group-actions" role="group">
<a class="normal btn btn-light tool_tips" href="$view"><i class="zmdi zmdi-eye"></i></a>
<a class="normal btn btn-light tool_tips" href="$upload"><i class="zmdi zmdi-cloud-upload"></i></a>
<a class="normal btn btn-light tool_tips" href="$pdf"><i class="zmdi zmdi-download"></i></a>
</div>
HTML;

				$data[] = [					
					'invoice_created'=>Date_Formatter::date($items->invoice_created),
					'invoice_terms'=>t("Invoice#{invoice_number} Commission ({date_from} - {date_to}",[
						'{date_from}'=>Date_Formatter::date($items->date_from),
						'{date_to}'=>Date_Formatter::date($items->date_to),
						'{invoice_number}'=>$items->invoice_number
					]),
					'payment_status'=>"<div class=\"badge payment $items->payment_status\">$items->payment_status</div>
					<div>".t("Due {date}",['{date}'=>Date_Formatter::date($items->due_date)])."</div>",
					'invoice_total'=>Price_Formatter::formatNumber($items->invoice_total),		
					'date_created'=>$action
				];
			}
		}

		$datatables = array(
			'draw'=>intval($draw),
			'recordsTotal'=>intval($count),
			'recordsFiltered'=>intval($count),
			'data'=>$data
		);
		$this->responseTable($datatables);
    }

	public function actionmerchantcustomerlist()
	{

		$merchant_id = Yii::app()->merchant->merchant_id;
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')  :'';	
		$date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
		$date_end = isset($this->data['date_end'])?$this->data['date_end']:'';

		$sortby = "first_name"; $sort = 'ASC';

		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}

		$initial_status = AttributesTools::initialStatus();		
		$not_in_status = AOrderSettings::getStatus(array('status_cancel_order','status_rejection'));
		array_push($not_in_status,$initial_status);
		$not_in_status = CommonUtility::arrayToQueryParameters($not_in_status);		
				
		$page = intval($page)/intval($length);		
		$criteria=new CDbCriteria();
		$criteria->alias = "a";
		$criteria->select = "
		a.*, 
		(
			select count(*) as total
			from {{ordernew}}
			where 
			client_id = a.client_id
			and status NOT IN ($not_in_status)
			and merchant_id = ".q($merchant_id)."
		) as total
		";
		$criteria->condition = "
		a.client_id IN (
			select client_id from {{ordernew}}
			where client_id=a.client_id
			and status NOT IN ($not_in_status)
			and merchant_id = ".q($merchant_id)."
		)
		";		

		$data = [];
		
		$criteria->order = "$sortby $sort";
		$count = AR_client::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);       
		
        $model = AR_client::model()->findAll($criteria);		
		if($model){
			
			foreach ($model as $items) {
				
				$photo = CMedia::getImage($items->avatar,$items->path,'@thumbnail',
		         CommonUtility::getPlaceholderPhoto('customer'));
				
				 $membership_info = t("Member since {date}",[
					'{date}'=>Date_Formatter::dateTime($items->date_created)
				 ]);

				 $email_info = t("Email. {email_address}",['{email_address}'=>$items->email_address]);
				 $phone_info = t("Phone. {contact_phone}",['{contact_phone}'=>$items->contact_phone]);

$avatar = <<<HTML
<img src="$photo" class="img-60 rounded-circle">
HTML;

$view = Yii::app()->CreateUrl("/customer/view",[
	'id'=>$items->client_uuid
]);

$action = <<<HTML
<div class="btn-group btn-group-actions" role="group">
<a class="normal btn btn-light tool_tips" href="$view"><i class="zmdi zmdi-eye"></i></a>
</div>
HTML;
                  
$member_details = <<<HTML
<div>$items->first_name $items->last_name</div>
<div>$email_info</div>
<div>$phone_info</div>
<div>$membership_info</div>
HTML;

				$data[] = [
					'client_id'=>$items->client_id,
					'avatar'=>$avatar,					
					'first_name'=>$member_details,
					'date_created'=>$action,
				];
			}
		}
		
		$datatables = array(
			'draw'=>intval($draw),
			'recordsTotal'=>intval($count),
			'recordsFiltered'=>intval($count),
			'data'=>$data
		);
		$this->responseTable($datatables);
	}

	public function actiontableRoomList()
    {
        $page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')  :'';	
		$date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
		$date_end = isset($this->data['date_end'])?$this->data['date_end']:'';

		$sortby = "room_id"; $sort = 'DESC';

		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = intval($page)/intval($length);		
		$criteria=new CDbCriteria();
		$criteria->alias = "a";
		$criteria->select = "a.*,
		(
			select count(*) from {{table_tables}}
			where room_id = a.room_id
			and available=1
		) as total_tables,
		(
			select concat(sum(min_covers),' - ',sum(max_covers))
			from {{table_tables}}
			where room_id = a.room_id
			and available=1
		) as capacity
		";
		$criteria->condition = "merchant_id=:merchant_id";
		$criteria->params  = array(
		  ':merchant_id'=>intval(Yii::app()->merchant->merchant_id),		  
		);
				
		$data = [];
		
		$criteria->order = "$sortby $sort";
		$count = AR_table_room::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        
        $model = AR_table_room::model()->findAll($criteria);		
		if($model){
			foreach ($model as $items) {

				$edit = Yii::app()->CreateUrl("/booking/update_room",[
					'room_uuid'=>$items->room_uuid
				]);
				
$action = <<<HTML
<div class="btn-group btn-group-actions" role="group">
  <a href="$edit" class="btn btn-light tool_tips" data-toggle="tooltip" data-placement="top" title="" data-original-title="Update">
   <i class="zmdi zmdi-border-color"></i>
  </a>
  <a href="javascript:;" data-id="$items->room_uuid" class="btn btn-light datatables_delete tool_tips" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete">
  <i class="zmdi zmdi-delete"></i>
  </a>
</div>
HTML;

				$data[] = [					
					'room_id'=>$items->room_id,
					'room_name'=>$items->room_name,
					'capacity'=>$items->capacity,
					'total_tables'=>$items->total_tables,
					'status'=>"<div class=\"badge post $items->status\">$items->status</div>",
					'date_created'=>$action
				];
			}
		}

		$datatables = array(
			'draw'=>intval($draw),
			'recordsTotal'=>intval($count),
			'recordsFiltered'=>intval($count),
			'data'=>$data
		);
		$this->responseTable($datatables);
    }	

	public function actiontableTablesList()
    {
        $page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')  :'';	
		$date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
		$date_end = isset($this->data['date_end'])?$this->data['date_end']:'';

		$sortby = "room_id"; $sort = 'DESC';

		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = intval($page)/intval($length);		
		$criteria=new CDbCriteria();
		$criteria->alias = "a";
		$criteria->select = "a.*, b.room_name";
		$criteria->condition = "a.merchant_id=:merchant_id";
		$criteria->params  = array(
		  ':merchant_id'=>intval(Yii::app()->merchant->merchant_id),		  
		);		
		$criteria->join='LEFT JOIN {{table_room}} b on  a.room_id=b.room_id';
				
		$data = [];
		
		$criteria->order = "$sortby $sort";
		$count = AR_table_tables::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        
        $model = AR_table_tables::model()->findAll($criteria);		
		if($model){
			foreach ($model as $items) {

				$edit = Yii::app()->CreateUrl("/booking/update_tables",[
					'id'=>$items->table_uuid
				]);

				$view = Yii::app()->CreateUrl("/booking/table_view",[
					'id'=>$items->table_uuid
				]);
				
				if(CommonUtility::getQrcodeFile($items->table_uuid)){
					$qrcode = CMedia::getImage("$items->table_uuid.png",CMedia::qrcodeFolder());
				} else {
					$qrcode = Yii::app()->CreateUrl("/booking/view_qrcode",[
						'data'=>$items->table_uuid
					]);	
				}

				$tableside_settings =  Yii::app()->CreateUrl("/booking/tableside_config",[
					'id'=>$items->table_uuid
				]);	

				$label_qrcode = t("QrCode");
				$label_table_settings = t("Tableside QrCode Configuration");
				$label_qrcode = t("View Table QrCode");
				$label_update = t("Update");
				$label_delete = t("Delete");
			
				
$action = <<<HTML
<div class="btn-group btn-group-actions" role="group">
  <a href="$qrcode" class="btn btn-light tool_tips" data-toggle="tooltip" data-placement="top" title="" data-original-title="$label_qrcode"  target="_blank" download >
   <i class="fa fa-qrcode"></i>
  </a>

  <a href="$tableside_settings" class="btn btn-light tool_tips" data-toggle="tooltip" data-placement="top" title="" data-original-title="$label_table_settings"   >
   <i class="fa fa-cog"></i>
  </a>

  <a href="$view" class="btn btn-light tool_tips" data-toggle="tooltip" data-placement="top" title="" data-original-title="$label_qrcode">
   <i class="zmdi zmdi-eye"></i>
  </a>

  <a href="$edit" class="btn btn-light tool_tips" data-toggle="tooltip" data-placement="top" title="" data-original-title="$label_update">
   <i class="zmdi zmdi-border-color"></i>
  </a>
  <a href="javascript:;" data-id="$items->table_uuid" class="btn btn-light datatables_delete tool_tips" data-toggle="tooltip" data-placement="top" title="" data-original-title="$label_delete">
  <i class="zmdi zmdi-delete"></i>
  </a>
</div>
HTML;

				$data[] = [					
					'table_id'=>$items->table_id,
					'table_name'=>$items->table_name,
					'room_id'=>$items->room_name,
					'min_covers'=>$items->min_covers,
					'max_covers'=>$items->max_covers,
					'available'=>$items->available==1?'<div class="badge badge-success">'.t("Yes").'</div>':'<div class="badge badge-light">'.t("No").'</div>',
					'date_created'=>$action
				];
			}
		}

		$datatables = array(
			'draw'=>intval($draw),
			'recordsTotal'=>intval($count),
			'recordsFiltered'=>intval($count),
			'data'=>$data
		);
		$this->responseTable($datatables);
    }		

	public function actiontableShift()
    {
        $page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')  :'';	
		$date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
		$date_end = isset($this->data['date_end'])?$this->data['date_end']:'';

		$sortby = "shift_id"; $sort = 'DESC';

		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = intval($page)/intval($length);		
		$criteria=new CDbCriteria();
		$criteria->alias = "a";
		$criteria->select = "a.*		
		";
		$criteria->condition = "merchant_id=:merchant_id";
		$criteria->params  = array(
		  ':merchant_id'=>intval(Yii::app()->merchant->merchant_id),		  
		);
				
		$data = [];
		$intervals = AttributesTools::timeInvertvalue();
		
		$criteria->order = "$sortby $sort";
		$count = AR_table_shift::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        
        $model = AR_table_shift::model()->findAll($criteria);		
		if($model){
			foreach ($model as $items) {

				$edit = Yii::app()->CreateUrl("/booking/update_shift",[
					'id'=>$items->shift_uuid
				]);
				
$action = <<<HTML
<div class="btn-group btn-group-actions" role="group">
  <a href="$edit" class="btn btn-light tool_tips" data-toggle="tooltip" data-placement="top" title="" data-original-title="Update">
   <i class="zmdi zmdi-border-color"></i>
  </a>
  <a href="javascript:;" data-id="$items->shift_uuid" class="btn btn-light datatables_delete tool_tips" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete">
  <i class="zmdi zmdi-delete"></i>
  </a>
</div>
HTML;

				$data[] = [					
					'shift_id'=>$items->shift_id,
					'shift_name'=>$items->shift_name,
					'first_seating'=>Date_Formatter::Time($items->first_seating)." - ".Date_Formatter::Time($items->last_seating),
					'shift_interval'=>isset($intervals[$items->shift_interval])?$intervals[$items->shift_interval]:$items->shift_interval,
					'status'=>"<div class=\"badge post $items->status\">$items->status</div>",
					'date_created'=>$action
				];
			}
		}

		$datatables = array(
			'draw'=>intval($draw),
			'recordsTotal'=>intval($count),
			'recordsFiltered'=>intval($count),
			'data'=>$data
		);
		$this->responseTable($datatables);
    }		

	public function actionreservationList()
	{
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')  :'';	
		$date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
		$date_end = isset($this->data['date_end'])?$this->data['date_end']:'';
		$filter_id = isset($this->data['filter_id'])?$this->data['filter_id']:'';		

		$sortby = "reservation_id"; $sort = 'DESC';

		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = intval($page)/intval($length);		
		$criteria=new CDbCriteria();
		$criteria->alias = "a";
		$criteria->select = "a.*,
		concat(b.first_name,' ',b.last_name) as full_name,
		c.table_name
		";
		$criteria->join='
		LEFT JOIN {{client}} b on  a.client_id = b.client_id 
		LEFT JOIN {{table_tables}} c on  a.table_id = c.table_id 
		';
		$criteria->condition = "a.merchant_id=:merchant_id";
		$criteria->params  = array(
		  ':merchant_id'=>intval(Yii::app()->merchant->merchant_id),		  
		);

		if($filter_id>0){
			$criteria->addCondition("a.merchant_id=:merchant_id AND a.client_id=:client_id");			
			$criteria->params = [
				':merchant_id'=>intval(Yii::app()->merchant->merchant_id),		  
				':client_id'=>$filter_id
			];
		}

		if(!empty($date_start) && !empty($date_end)){
			$criteria->addBetweenCondition("DATE_FORMAT(reservation_date,'%Y-%m-%d')", $date_start , $date_end );
		} 
				
		$data = [];		
		$status_list = AttributesTools::bookingStatus();
		
		$criteria->order = "$sortby $sort";
		$count = AR_table_reservation::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        
		
        $model = AR_table_reservation::model()->findAll($criteria);		
		if($model){
			foreach ($model as $items) {

				$edit = Yii::app()->CreateUrl("/booking/update_reservation",[
					'id'=>$items->reservation_uuid
				]);
				$overview = Yii::app()->CreateUrl("/booking/reservation_overview",[
					'id'=>$items->reservation_uuid
				]);
				$booking_status = isset($status_list[$items->status])?$status_list[$items->status]:$items->status;		
				
				$badge = 'badge-primary';
				$button_color = 'btn-info';
				if($items->status=="confirmed"){
					$badge = 'badge-success';
					$button_color = 'btn-success';
				} else if ( $items->status=="cancelled" ){
					$badge = 'badge-danger';
					$button_color = 'btn-danger';
				} else if ( $items->status=="denied" ){
					$badge = 'badge-danger';
					$button_color = 'btn-danger';
				} else if ( $items->status=="finished" ){
					$badge = 'badge-success';
					$button_color = 'btn-success';
				}


				$status_action_list = '';
				foreach ($status_list as $key => $value) {
					$status_action_list.='<a class="dropdown-item" href="'. Yii::app()->CreateUrl("/booking/update_status",
					[
						'id'=>$items->reservation_uuid,
						'status'=>$key
					]) .'">'.$value.'</a>';
				}

				$special_request = $items->special_request;
				if(!empty($items->cancellation_reason)){
					$special_request.="<p class=\"text-danger\">";
					$special_request.=t("CANCELLATION NOTES = {cancellation_reason}",[
						'{cancellation_reason}'=>$items->cancellation_reason
					]);
					$special_request.="</p>";
				}
				
$action = <<<HTML
<div class="btn-group btn-group-actions" role="group">
  <a href="$overview" class="btn btn-light tool_tips" data-toggle="tooltip" data-placement="top" title="" data-original-title="Update">
   <i class="zmdi zmdi-eye"></i>
  </a>
  <a href="$edit" class="btn btn-light tool_tips" data-toggle="tooltip" data-placement="top" title="" data-original-title="Update">
   <i class="zmdi zmdi-border-color"></i>
  </a>
  <a href="javascript:;" data-id="$items->reservation_uuid" class="btn btn-light datatables_delete tool_tips" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete">
  <i class="zmdi zmdi-delete"></i>
  </a>
</div>

<div class="dropdown">
  <button class="btn $button_color dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
  $booking_status
  </button>  
  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">		
    $status_action_list
  </div>
</div>
HTML;

				$data[] = [
					'reservation_id'=>$items->reservation_id,
					'client_id'=>'<b>'.$items->full_name.'</b></b><span class="badge ml-2 post '.$badge.'">'.$booking_status.'</span>',
					'guest_number'=>$items->guest_number,
					'table_id'=>$items->table_name,
					'reservation_date'=>Date_Formatter::dateTime($items->reservation_date." ".$items->reservation_time),					
					'special_request'=>$special_request,
					'date_created'=>$action
				];
			}			
		}		

		$datatables = array(
			'draw'=>intval($draw),
			'recordsTotal'=>intval($count),
			'recordsFiltered'=>intval($count),
			'data'=>$data
		);
		$this->responseTable($datatables);
	}

	public function actionBookingTimeline()
	{
		try {
			
			$id = Yii::app()->input->post("id");	
			$model = CBooking::get($id);			
			$data = CBooking::getTimeline($model->reservation_id);
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'data'=>$data
			];
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
		}		
		$this->responseJson();
	}

	public function actionprintLogs()
	{
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')  :'';	
		$date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
		$date_end = isset($this->data['date_end'])?$this->data['date_end']:'';
		$filter_id = isset($this->data['filter_id'])?$this->data['filter_id']:'';		

		$sortby = "id"; $sort = 'DESC';

		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = intval($page)/intval($length);		
		$criteria=new CDbCriteria();
		$criteria->alias = "a";
		$criteria->select = "a.*		
		";				
		$criteria->condition = "a.merchant_id=:merchant_id";
		$criteria->params  = array(
		  ':merchant_id'=>intval(Yii::app()->merchant->merchant_id),		  
		);
		if(!empty($date_start) && !empty($date_end)){
			$criteria->addBetweenCondition("DATE_FORMAT(date_created,'%Y-%m-%d')", $date_start , $date_end );
		} 
		
		if(!empty($search)){
			$criteria->addSearchCondition('id', $search);
		}
				
		$data = [];				
		
		$criteria->order = "$sortby $sort";
		$count = AR_printer_logs::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        
				
        $model = AR_printer_logs::model()->findAll($criteria);		
		if($model){
			foreach ($model as $items) {

				$view = Yii::app()->CreateUrl("/printers/print_view",['id'=>$items->id]);

				$badge = 'badge-primary';				
				if($items->status=="process"){
					$badge = 'badge-success';					
				} else {
					$badge = 'badge-danger';					
				}

				$view_label = t("View");
				$delete_label = t("Delete");				


				
$action = <<<HTML
<div class="btn-group btn-group-actions" role="group">
  <a href="$view" target="_blank" class="btn btn-light tool_tips" data-toggle="tooltip" data-placement="top" title="" data-original-title="$view_label">
   <i class="zmdi zmdi-eye"></i>
  </a>  
  <a href="javascript:;" data-id="$items->id" class="btn btn-light datatables_delete tool_tips" data-toggle="tooltip" data-placement="top" title="" data-original-title="$delete_label">
  <i class="zmdi zmdi-delete"></i>
  </a>
</div>
HTML;

				$data[] = [
					'id'=>$items->id,					
					'order_id'=>'<b>'.$items->order_id.'</b>',
					'printer_number'=>$items->printer_number,					
					'job_id'=>'<span class="d-inline-block text-truncate" style="max-width: 150px;">'.$items->job_id.'</span>',					
					'status'=>'<span class="badge ml-2 post '.$badge.'">'.$items->status.'</span>',
					'date_created'=>Date_Formatter::dateTime($items->date_created),
					'ip_address'=>$action,
				];
			}			
		}		

		$datatables = array(
			'draw'=>intval($draw),
			'recordsTotal'=>intval($count),
			'recordsFiltered'=>intval($count),
			'data'=>$data
		);
		$this->responseTable($datatables);
	}		
	
	public function actiongetAvailableDriver()
	{
		try {
          
			$on_demand_availability = isset(Yii::app()->params['settings'])? (isset(Yii::app()->params['settings']['driver_on_demand_availability'])?Yii::app()->params['settings']['driver_on_demand_availability']:false) :false;
			$on_demand_availability = $on_demand_availability==1?true:false;						

			$order_uuid = Yii::app()->input->post("order_uuid");

			$order = COrders::get($order_uuid);			
			$merchant = CMerchants::get($order->merchant_id);			
			$merchant_data = [
				'restaurant_name'=>$merchant->restaurant_name,
				'contact_phone'=>$merchant->contact_phone,
				'contact_email'=>$merchant->contact_email,
				'address'=>$merchant->address,
				'latitude'=>$merchant->latitude,
				'longitude'=>$merchant->lontitude,
			];
			$merchant_zone = CMerchants::getListMerchantZone([$merchant->merchant_id]);
			$merchant_zone = isset($merchant_zone[$merchant->merchant_id])?$merchant_zone[$merchant->merchant_id]:'';

			$group_selected = intval(Yii::app()->input->post("group_selected"));
            $q = Yii::app()->input->post("q");
            $merchant_id = intval(Yii::app()->input->post("merchant_id"));
            $zone_id = intval(Yii::app()->input->post("zone_id"));

			$self_delivery = isset(Yii::app()->params['settings_merchant']['self_delivery'])?Yii::app()->params['settings_merchant']['self_delivery']:false;
		    $self_delivery = $self_delivery==1?true:false;		
			
			$merchant_id = $self_delivery==true?Yii::app()->merchant->merchant_id:$merchant_id; 

            $criteria=new CDbCriteria();
            $criteria->alias = "a";
            $criteria->select = "a.*";        
            if($group_selected>0){
                $criteria->join = "LEFT JOIN {{driver_group_relations}} b ON a.driver_id = b.driver_id";                
                $criteria->addCondition("b.group_id=:group_id");              
            } 
            
            $now = date("Y-m-d"); $and_zone = '';
            if($zone_id>0){
                $and_zone = "AND zone_id = ".q($zone_id)." ";
            }

			if(!$on_demand_availability){
				$criteria->addCondition("a.merchant_id=:merchant_id AND a.latitude !='' AND a.status=:status AND a.driver_id IN (
					select driver_id from {{driver_schedule}}
					where DATE(time_start)=".q($now)."
					AND DATE(shift_time_started) IS NOT NULL  
					AND DATE(shift_time_ended) IS NULL  
					$and_zone                    
				)");
			}

            if($group_selected>0){
                $criteria->params = [
                    ':merchant_id'=>intval($merchant_id),
                    ':group_id'=>$group_selected,
					':status'=>"active"
                ];
            } else {
                $criteria->params = [
                    ':merchant_id'=>intval($merchant_id),
					':status'=>"active"
                ];
            }   
            
            if(!empty($q)){
                $criteria->addSearchCondition('a.first_name', $q );
                $criteria->addSearchCondition('a.last_name', $q , true , 'OR' );            
            }        
			
			// ON DEMAND
			if($on_demand_availability){
				$and_merchant_zone = '';
				if(is_array($merchant_zone) && count($merchant_zone)>=1 || $zone_id>0){
					if($zone_id>0){
						$in_query = CommonUtility::arrayToQueryParameters([$zone_id]);
					} else $in_query = CommonUtility::arrayToQueryParameters($merchant_zone);				
					$and_merchant_zone = "
					AND a.driver_id IN (
						select driver_id from {{driver_schedule}}
						where 
						merchant_id=".q($merchant_id)." and driver_id = a.driver_id 
						and on_demand=1 and zone_id IN ($in_query)
					)
					";
				}
				$criteria->addCondition("a.merchant_id=:merchant_id AND a.is_online=:is_online AND a.status=:status AND a.latitude !='' $and_merchant_zone ");
				$criteria->params = [
					':merchant_id'=>intval($merchant_id),
					':is_online'=>1,
					':status'=>"active"
				];			
				if($group_selected>0){
					$criteria->params[':group_id']=$group_selected;
				}				
			}

            $criteria->order = "a.first_name ASC";
            $criteria->limit = 20;              
            													
            if($model = AR_driver::model()->findAll($criteria)){				
                $data = array(); $driver_ids = [];
                foreach ($model as $items) {
					$photo = CMedia::getImage($items->photo,$items->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('customer'));
					$driver_ids[] = $items->driver_id; 
                    $data[] = [
                      'name'=>$items->first_name." ".$items->last_name,
                      'driver_id'=>$items->driver_id,
					  'photo_url'=>$photo,
					  'latitude'=>$items->latitude,
					  'longitude'=>$items->lontitude,
                    ];
                }				
				
				$active_task = CDriver::getCountActiveTaskAll($driver_ids,date("Y-m-d"));						

                $this->code  = 1;
                $this->msg = "OK";
                $this->details = [
					'data'=>$data,
					'merchant_data'=>$merchant_data,
					'active_task'=>$active_task
				];
            } else {
				$this->msg = t(HELPER_NO_RESULTS);
				$this->details = [			
					'merchant_data'=>$merchant_data
				];
			}			

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();	
	}	

	public function actiongetgrouplist()
	{
		try {

			$self_delivery = isset(Yii::app()->params['settings_merchant']['self_delivery'])?Yii::app()->params['settings_merchant']['self_delivery']:false;
		    $self_delivery = $self_delivery==1?true:false;					

			$merchant_id = $self_delivery==true?Yii::app()->merchant->merchant_id:0;
			$where = "WHERE merchant_id=".q($merchant_id)." ";			

			$data = CommonUtility::getDataToDropDown("{{driver_group}}","group_id","group_name",$where,"order by group_name asc");
			$this->code = 1;
			$this->msg = "OK";
			$this->details = $data;
			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();	
	}	

	public function actiongetZoneList()
	{
		try {
			
			$self_delivery = isset(Yii::app()->params['settings_merchant']['self_delivery'])?Yii::app()->params['settings_merchant']['self_delivery']:false;
		    $self_delivery = $self_delivery==1?true:false;					

			$merchant_id = $self_delivery==true?Yii::app()->merchant->merchant_id:0;
			$where = "WHERE merchant_id=".q($merchant_id)." ";		

			$zone_list = CommonUtility::getDataToDropDown("{{zones}}",'zone_id','zone_name', $where ,"ORDER BY zone_name ASC"); 
			if($zone_list){
				$zone_list = CommonUtility::ArrayToLabelValue($zone_list);
				$this->code = 1;
				$this->msg = "Ok";
				$this->details = $zone_list;
			} else $this->msg = t(HELPER_NO_RESULTS);			

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();	
	}	

	public function actionAssignDriver()
	{
		try {

			$on_demand_availability = isset(Yii::app()->params['settings'])? (isset(Yii::app()->params['settings']['driver_on_demand_availability'])?Yii::app()->params['settings']['driver_on_demand_availability']:false) :false;
			$on_demand_availability = $on_demand_availability==1?true:false;						

			$driver_id = intval(Yii::app()->input->post('driver_id'));
			$order_uuid = trim(Yii::app()->input->post('order_uuid'));
			
			$order = COrders::get($order_uuid);
			$driver = CDriver::getDriver($driver_id);

			$meta = AR_admin_meta::getValue('status_assigned');
            $status_assigned = isset($meta['meta_value'])?$meta['meta_value']:''; 
            
            $options = OptionsTools::find(['driver_allowed_number_task']);
            $allowed_number_task = isset($options['driver_allowed_number_task'])?$options['driver_allowed_number_task']:0;

			//$order->scenario = "delivery_change_status";
			$order->scenario = "assign_order";
			$order->on_demand_availability = $on_demand_availability;
            $order->driver_id = intval($driver_id);
            $order->delivered_old_status = $order->delivery_status;
            $order->delivery_status = $status_assigned;
            $order->change_by = Yii::app()->merchant->first_name;
            $order->date_now = date("Y-m-d");
            $order->allowed_number_task = intval($allowed_number_task);
			$order->assigned_at = CommonUtility::dateNow();

			if(!$on_demand_availability){
				try {
					$now = date("Y-m-d");                
					$vehicle = CDriver::getVehicleAssign($driver_id,$now);
					$order->vehicle_id = $vehicle->vehicle_id;
				} catch (Exception $e) {
					$this->msg = t($e->getMessage());
					$this->responseJson();	
				}              
		    }          
			
            if($order->save()){
                $this->code  = 1;
                $this->msg = t("Order assign to {driver_name}",[
					'{driver_name}'=>"$driver->first_name $driver->first_name"
				]);
            } else $this->msg = CommonUtility::parseModelErrorToString($order->getErrors());

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();	
	}	

	public function actioncustomerOrderList()
	{
		
		$merchant_id = Yii::app()->merchant->merchant_id;
		$data = array();		
		$status = COrders::statusList(Yii::app()->language);    	
        $services = COrders::servicesList(Yii::app()->language);
        $payment_gateway = AttributesTools::PaymentProvider();
		$initial_status = AttributesTools::initialStatus();
		
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
		$filter = isset($this->data['filter'])?$this->data['filter']:'';
		
		$date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
		$date_end = isset($this->data['date_end'])?$this->data['date_end']:'';
		$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';		
		$client_id = isset($this->data['ref_id'])?$this->data['ref_id']:'';		
				
		$sortby = "order_id"; $sort = 'DESC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
		
		$page = $page>0? intval($page)/intval($length) :0;
		$criteria=new CDbCriteria();
		$criteria->alias = "a";
		$criteria->select = "a.order_id, a.client_id, a.status, a.order_uuid , a.merchant_id,
		a.payment_code, a.service_code,a.total, a.date_created,
		b.meta_value as customer_name, 
		c.restaurant_name, c.logo, c.path,
		(
		   select sum(qty)
		   from {{ordernew_item}}
		   where order_id = a.order_id
		) as total_items
		";
		$criteria->join='
		LEFT JOIN {{ordernew_meta}} b on  a.order_id = b.order_id 
		LEFT JOIN {{merchant}} c on  a.merchant_id = c.merchant_id 
		';
		
		$criteria->condition = "a.client_id=:client_id AND a.merchant_id=:merchant_id AND b.meta_name=:meta_name ";
		$criteria->params  = array(		  
		  ':client_id'=>intval($client_id),
		  ':merchant_id'=>intval($merchant_id),
		  ':meta_name'=>'customer_name'
		);    
		$criteria->addNotInCondition('a.status', (array) array($initial_status) );
		
		if(!empty($date_start) && !empty($date_end)){
			$criteria->addBetweenCondition("DATE_FORMAT(a.date_created,'%Y-%m-%d')", $date_start , $date_end );
		}
		
		if(!empty($search)){
			$criteria->addSearchCondition('a.order_id', intval($search) );
		}
		
		$criteria->order = "$sortby $sort";
		$count = AR_ordernew::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        
                        					
        $models = AR_ordernew::model()->findAll($criteria);
        if($models){        	
        	foreach ($models as $item) {    
        		
        		$item->total_items = intval($item->total_items);
		        $item->total_items = t("{{total_items}} items",array(
		          '{{total_items}}'=>$item->total_items
		        ));    		
        		
        		$trans_order_type = $item->service_code;
		         if(array_key_exists($item->service_code,$services)){
		             $trans_order_type = $services[$item->service_code]['service_name'];
		         }
		         
		         $order_type = t("Order Type.");
		         $order_type.="<span class='ml-2 services badge $item->service_code'>$trans_order_type</span>";
		         
		         $total = t("Total. {{total}}",array(
		          '{{total}}'=>Price_Formatter::formatNumber($item->total)
		         ));
		         $place_on = t("Place on {{date}}",array(
		          '{{date}}'=>Date_Formatter::dateTime($item->date_created)
		         ));
		         
		         $status_trans = $item->status;
		         if(array_key_exists($item->status, (array) $status)){
		             $status_trans = $status[$item->status]['status'];
		         }
		         
		        $logo_url = CMedia::getImage($item->logo,$item->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('merchant'));
		         
		        $payment_name = isset($payment_gateway[$item->payment_code])?$payment_gateway[$item->payment_code]:$item->payment_code;		        
		        

$logo_html = <<<HTML
<img src="$logo_url" class="img-60 rounded-circle" />
HTML;


$information = <<<HTML
$item->total_items<span class="ml-2 badge order_status $item->status">$status_trans</span>
<p class="dim m-0">$payment_name</p>
<p class="dim m-0">$order_type</p>
<p class="dim m-0">$total</p>
<p class="dim m-0">$place_on</p>
HTML;


$view = Yii::app()->createAbsoluteUrl("orders/view",[
	'order_uuid'=>$item->order_uuid
]);
$pdf = Yii::app()->createAbsoluteUrl("print/pdf",[
	'order_uuid'=>$item->order_uuid
]);

$action = <<<HTML
<div class="btn-group btn-group-actions" role="group">
<a class="normal btn btn-light tool_tips" href="$view"><i class="zmdi zmdi-eye"></i></a>
<a class="normal btn btn-light tool_tips" href="$pdf"><i class="zmdi zmdi-download"></i></a>
</div>
HTML;
	
        		$data[]=array(
        		  'merchant_id'=>$logo_html,        		  
        		  'client_id'=>$information,
        		  'order_id'=>$item->order_id,
        		  'restaurant_name'=>$item->restaurant_name,
				  'order_uuid'=>$action        		
        		);
        	}
        }
        
        $datatables = array(
		  'draw'=>intval($draw),
		  'recordsTotal'=>intval($count),
		  'recordsFiltered'=>intval($count),
		  'data'=>$data
		);
				
		$this->responseTable($datatables);
	}		

	public function actiongetMenu()
	{
		try {

			$role_id = Yii::app()->merchant->role_id;			
			$access = MerchantTools::hasMerchantSetMenu(Yii::app()->merchant->merchant_id);
			$merchant_id = $access?Yii::app()->merchant->merchant_id:0;

			$cacheKey = 'cache_merchant_search_menu_'.Yii::app()->merchant->id;
            $items = Yii::app()->cache->get($cacheKey);
			if ($items === false) {
				$items = AttributesTools::getSearchBarMenu("merchant",$role_id,$merchant_id);
				Yii::app()->cache->set($cacheKey, $items, CACHE_LONG_DURATION);
			}

			$this->code = 1;
			$this->msg = "OK";
			$this->details = $items;
			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}		
		$this->responseJson();
	}

	public function actiondailyReportSales()
	{
		$merchant_id = Yii::app()->merchant->merchant_id;
    	$data = array(); $date_now = date("Y-m-d");
				
		$page = isset($this->data['start'])?$this->data['start']:0;	
	    $length = isset($this->data['length'])?$this->data['length']:0;	
	    $draw = isset($this->data['draw'])?$this->data['draw']:0;	
	    $search = isset($this->data['search'])?$this->data['search']['value']:'';	
	    $columns = isset($this->data['columns'])?$this->data['columns']:'';
	    $order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
	    $filter = isset($this->data['filter'])?$this->data['filter']:'';
		$date_start = isset($this->data['date_start'])? (!empty($this->data['date_start'])?$this->data['date_start']:$date_now) :$date_now;
	    $date_end = isset($this->data['date_end'])?  (!empty($this->data['date_end'])?$this->data['date_end']:$date_now) :$date_now;
		$transaction_type = $this->data['transaction_type'] ?? null;	

		$sortby = "order_id"; $sort = 'DESC';

		if(is_array($order) && count($order)>=1){
	        if(array_key_exists($order['column'],(array)$columns)){			
	            $sort = $order['dir'];
	            $sortby = $columns[$order['column']]['data'];
	        }
	    }
	            
	    if($page>0){
	       $page = intval($page)/intval($length);	
	    }
		
		$payment_list = AttributesTools::PaymentProvider();
		$services = COrders::servicesList(Yii::app()->language);    	
		$status_completed = AOrderSettings::getStatus(array('status_delivered','status_completed'));
		
		$criteria=new CDbCriteria();
		$criteria->alias = "a";
		$criteria->condition = "a.merchant_id=:merchant_id";
	    $criteria->params  = array(
	      ':merchant_id'=>intval($merchant_id),		  	      
	    );
		$criteria->addBetweenCondition("DATE_FORMAT(a.date_created,'%Y-%m-%d')", $date_start , $date_end );		
		$criteria->addInCondition('a.status', (array) $status_completed );

		if($transaction_type){
			$criteria->addInCondition("a.payment_code",$transaction_type);
		}		

		$criteria->order = "$sortby $sort";			

		$count = AR_ordernew::model()->count($criteria); 
	    $pages=new CPagination( intval($count) );
	    $pages->setCurrentPage( intval($page) );        
	    $pages->pageSize = intval($length);
	    $pages->applyLimit($criteria);
		
	    if($model = AR_ordernew::model()->findAll($criteria)){
			$price_format = CMulticurrency::getAllCurrency();
			foreach ($model as $items) {
				
				if($price_format){
					if(isset($price_format[$items->base_currency_code])){
						Price_Formatter::$number_format = $price_format[$items->base_currency_code];
					}						
				}

				$view_order = Yii::app()->createUrl('orders/view',array(
					'order_uuid'=>$items->order_uuid
				));
				$data[] = [					
					'order_id'=>'<a href="'.$view_order.'">'.$items->order_id.'</a>',
					'service_code'=> isset($services[$items->service_code])?$services[$items->service_code]['service_name']:$items->service_code ,
					'payment_code'=>isset($payment_list[$items->payment_code])?$payment_list[$items->payment_code]:$items->payment_code,
					'sub_total'=>$items->sub_total>0?Price_Formatter::formatNumber($items->sub_total):'',
					'service_fee'=>$items->service_fee>0?Price_Formatter::formatNumber($items->service_fee):'',
					'small_order_fee'=>$items->small_order_fee>0?Price_Formatter::formatNumber($items->small_order_fee):'',
					'delivery_fee'=>$items->delivery_fee>0?Price_Formatter::formatNumber($items->delivery_fee):'',
					'tax_total'=>$items->tax_total>0?Price_Formatter::formatNumber($items->tax_total):'',
					'courier_tip'=>$items->courier_tip>0?Price_Formatter::formatNumber($items->courier_tip):'',
					'total'=>$items->total>0?Price_Formatter::formatNumber($items->total):'',
				];
			}
		}
		$datatables = array(
			'draw'=>intval($draw),
			'recordsTotal'=>intval($count),
			'recordsFiltered'=>intval($count),
			'data'=>$data
		);
		$this->responseTable($datatables);
	}

	public function actiongetDailySummary()
	{
		try {

			$merchant_id = Yii::app()->merchant->merchant_id;
			$status_completed = AOrderSettings::getStatus(array('status_delivered','status_completed'));
			$date_now = date("Y-m-d");

			$date_start = Yii::app()->input->post('date_start');
			$date_end = Yii::app()->input->post('date_end');
			$payment_type =  Yii::app()->request->getPost('transaction_type', null);
			$date_start = !empty($date_start)?$date_start:$date_now;
			$date_end = !empty($date_end)?$date_end:$date_now;

			$payment_list = null;
			if($payment_type){
				$payment_list = explode(",",$payment_type);				
			}

			$data = CReports::dailySalesSummary($merchant_id,$date_start,$date_end,$status_completed,$payment_list);
			$this->code = 1; $this->msg = "Ok";

			$price_format = array(
				'symbol'=>Price_Formatter::$number_format['currency_symbol'],
				'decimals'=>Price_Formatter::$number_format['decimals'],
				'decimal_separator'=>Price_Formatter::$number_format['decimal_separator'],
				'thousand_separator'=>Price_Formatter::$number_format['thousand_separator'],
				'position'=>Price_Formatter::$number_format['position'],
			);

			$this->details = [
				'data'=>$data,
				'price_format'=>$price_format
			];					
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
			$this->details = [			
				'price_format'=>$price_format
			];
		}	
		$this->responseJson();	
	}

	public function actionfpprint_dailysalesreport()
	{
		try {			

			$merchant_id = Yii::app()->merchant->merchant_id;
			$status_completed = AOrderSettings::getStatus(array('status_delivered','status_completed'));
			$date_now = date("Y-m-d");

			$printer_id = Yii::app()->input->post('printerId');
			$date_start = Yii::app()->input->post('date_start');
			$date_end = Yii::app()->input->post('date_end');
			$date_start = !empty($date_start)?$date_start:$date_now;
			$date_end = !empty($date_end)?$date_end:$date_now;

			$model = AR_printer::model()->find("merchant_id=:merchant_id AND printer_id=:printer_id",[
                ":merchant_id"=>$merchant_id,
                ':printer_id'=>intval($printer_id)
            ]);
			if($model){
				$meta = AR_printer_meta::getMeta($printer_id,['printer_user','printer_ukey','printer_sn','printer_key']);					
                $printer_user = isset($meta['printer_user'])?$meta['printer_user']['meta_value1']:'';
                $printer_ukey = isset($meta['printer_ukey'])?$meta['printer_ukey']['meta_value1']:'';
                $printer_sn = isset($meta['printer_sn'])?$meta['printer_sn']['meta_value1']:'';
                $printer_key = isset($meta['printer_key'])?$meta['printer_key']['meta_value1']:'';

				$merchant_info = COrders::getMerchant($merchant_id,Yii::app()->language);
				$data = CReports::dailySalesSummaryPrint($merchant_id,$date_start,$date_end,$status_completed);		
				
				$payment_list = AttributesTools::PaymentProvider();
		        $services = COrders::servicesList(Yii::app()->language);    					
				
				$tpl = FPtemplate::DailySalesReport(
					$model->paper_width,
					$data,
					$merchant_info,
					$date_start,
					$date_end,
					$payment_list,
					$services
				);				
				
				$stime = time();
                $sig = sha1($printer_user.$printer_ukey.$stime);               
                $result = FPinterface::Print($printer_user,$stime,$sig,$printer_sn,$tpl);

				$model = new AR_printer_logs();
			    $model->order_id = 0;
			    $model->merchant_id = intval($merchant_id);
			    $model->printer_number = $printer_sn;
			    $model->print_content = $tpl;
			    $model->job_id = $result;
			    $model->status = 'process';
			    $model->save();

				$result = [];
				$this->code = 1;
                $this->msg = t("Request succesfully sent to printer");
                $this->details = $result;		   

			} else $this->msg = t(HELPER_RECORD_NOT_FOUND);
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			
		}	
		$this->responseJson();	
	}

	public function actiongetOpeningHoursAttribute()
	{
		try {
			$merchant_id = Yii::app()->merchant->merchant_id;			
			$data = CMerchants::getOpeningHours($merchant_id,true,true);			
			$days = AttributesTools::dayList();
			foreach ($days as $day_code => $dayname) {				
				if(!array_key_exists($day_code,(array)$data)){					
					$data[$day_code][] = [
						'id'=>0,
						'status'=>"open",
						'close'=>false,
						'start_time'=>"00:00",
						'end_time'=>"00:00",
						'custom_text'=>""
					];
				}
			}			
			$this->code = 1;
			$this->msg = "Ok";
			$time_range = AttributesTools::createTimeRange("00:00","23:59","15 mins","24","H:i");			
			$this->details = [
				'days'=>$days,
				'data'=>$data,
				'time_range'=>CommonUtility::ArrayToLabelValue($time_range)
			];
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			
		}	
		$this->responseJson();	
	}

	public function actionupdatestorehours()
	{
		try {
			
			$error = []; $error_count = 0; $saved_ids = [];
			$merchant_id = intval(Yii::app()->merchant->merchant_id);
			$data = isset($this->data['data'])?$this->data['data']:'';			
			
			if(is_array($data) && count($data)>=1){
				foreach ($data as $day => $item) {					
					foreach ($item as $items) {									
						$id = isset($items['id'])? intval($items['id']) :0;						
						if($id>0){										
							$model = AR_opening_hours::model()->find("merchant_id=:merchant_id AND id=:id",[
								':merchant_id'=>$merchant_id,
								':id'=>$id
							]);
						} else $model = new AR_opening_hours();
											
						$status = isset($items['close'])?$items['close']:false;						
						$model->merchant_id = $merchant_id;
						$model->day = $day;
						$model->start_time = isset($items['start_time'])?$items['start_time']:'';
						$model->end_time = isset($items['end_time'])?$items['end_time']:'';
						$model->status = $status==1?"close":"open";
						$model->custom_text = isset($items['custom_text'])?$items['custom_text']:'';
						if(!$model->save()){			
							$error_count++;				
							$err = $model->getErrors();
							foreach ($err as $item_error) {
								foreach ($item_error as $item_errors) {
									$error[] = $item_errors;
								}
							}
						} else {
							$saved_ids[] = $model->id;
						}
					}
				}
			}

			AR_opening_hours::removeOpeningHours($saved_ids,$merchant_id);
			$this->code = 1;
			$this->msg = $error_count<=0? t("Store hours updated") : t("Error has occured");
			$this->details = [
				'error_count'=>$error_count,
				'error'=>$error
			];			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			
		}	
		$this->responseJson();	
	}

	public function actiondriverList()
	{
		$data = array();		
						
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
						
		$sortby = "date_created"; $sort = 'DESC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = intval($page)/intval($length);		
		$criteria=new CDbCriteria();		

		$merchant_id = Yii::app()->merchant->merchant_id;
		$criteria->condition = "merchant_id=:merchant_id";
		$criteria->params = [
			':merchant_id'=>$merchant_id
		];

		if(!empty($search)){
			$criteria->addSearchCondition('first_name', $search );
			// $criteria->addSearchCondition('last_name', $search , true, "OR" );
			// $criteria->addSearchCondition('email', $search , true, "OR"  );
		}				

		$criteria->order = "$sortby $sort";
		$count = AR_driver::model()->count($criteria); 
		
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        
						
        $models = AR_driver::model()->findAll($criteria);

		$employment_list = AttributesTools::DriverEmploymentType();
		
        if($models){
        	foreach ($models as $item) {        	
				
				$photo = CMedia::getImage($item->photo,$item->path,'@thumbnail',
		         CommonUtility::getPlaceholderPhoto('customer'));
			
        		$data[]=array(				 
				  'driver_uuid'=>$item->driver_uuid,	
				  'date_created'=>$item->date_created,
				  'first_name'=>'<div class="row"><div class="col-4"><img src="'.$photo.'" class="img-60 rounded-circle" /></div><div class="col">'.
				  $item->first_name." ".$item->last_name."<p>".t("ID")."# $item->driver_id</p>".'</div></div>',
				  'email'=>$item->email,
				  'phone'=>$item->phone_prefix.$item->phone,
				  'employment_type'=>isset($employment_list[$item->employment_type])?$employment_list[$item->employment_type]:$item->employment_type,
        		  'status'=>'<span class="badge ml-2 customer '.$item->status.'">'.t($item->status).'</span>',
				  'view_url'=>Yii::app()->createUrl("/merchantdriver/overview/",array('id'=>$item->driver_uuid)),
				  'update_url'=>Yii::app()->createUrl("/merchantdriver/update/",array('id'=>$item->driver_uuid)),
        		  'delete_url'=>Yii::app()->createUrl("/merchantdriver/delete/",array('id'=>$item->driver_uuid)),				  
				);
        	}        	
        }
        
         $datatables = array(
		  'draw'=>intval($draw),
		  'recordsTotal'=>intval($count),
		  'recordsFiltered'=>intval($count),
		  'data'=>$data
		);				
		$this->responseTable($datatables);
	}		

	public function actiongetDriverOverview()
	{
		try {
						
			$driver_uuid = isset($this->data['driver_uuid'])?$this->data['driver_uuid']:'';
			$driver_data = CDriver::getDriverByUUID($driver_uuid);					
			$driver_id = $driver_data->driver_id;
			$total = CReviews::reviewsCountDriver($driver_id);			
			$review_summary = CReviews::summaryDriver($driver_id,$total);	
			
			$tracking_stats = AR_admin_meta::getMeta(array(
				'tracking_status_delivered','tracking_status_completed'
			));		
			$tracking_status_delivered = isset($tracking_stats['tracking_status_delivered'])?AttributesTools::cleanString($tracking_stats['tracking_status_delivered']['meta_value']):'';			
			$tracking_status_completed = isset($tracking_stats['tracking_status_completed'])?AttributesTools::cleanString($tracking_stats['tracking_status_completed']['meta_value']):'';			

			$total_delivered_percent=0;
			$total_delivered = CDriver::CountOrderStatus($driver_id,$tracking_status_delivered);
			$total_assigned =  CDriver::SummaryCountOrderTotal($driver_id);
			if($total_assigned>0){
			  $total_delivered_percent = round(($total_delivered/$total_assigned)*100);
			}

			$successful_status = array();
			if(!empty($tracking_status_delivered)){
				$successful_status[] = $tracking_status_delivered;
			}			
			if(!empty($tracking_status_completed)){
			   $successful_status[] = $tracking_status_completed;
			}
			
			$total_tip_percent = 0;
			$total_tip = CDriver::TotaLTips($driver_id,$successful_status);
			$summary_tip = CDriver::SummaryTotaLTips($driver_id);
			if($summary_tip>0){
				$total_tip_percent = round(($total_tip/$summary_tip)*100);
			}

			try {																										
				$card_id = CWallet::createCard( Yii::app()->params->account_type['driver'] ,$driver_id);				    	
				$wallet_balance = CWallet::getBalance($card_id);
			} catch (Exception $e) {
			   $this->msg = t($e->getMessage());
			    $wallet_balance = 0;		
			}	

			$data = array(
			  'total'=>$total,				
			  'review_summary'=>$review_summary,	
			  'total_delivered'=>$total_delivered,
			  'total_delivered_percent'=>$total_delivered_percent,
			  'total_tip'=>Price_Formatter::formatNumber($total_tip),
			  'total_tip_percent'=>intval($total_tip_percent),
			  'wallet_balance'=>Price_Formatter::formatNumber($wallet_balance),
			);    	

			$this->code = 1; $this->msg = "ok";
		    $this->details = $data;

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			
		}		
		$this->responseJson();	
	}	

	public function actiongetDriverActivity()
	{
		try {					
			
			$driver_uuid = isset($this->data['driver_uuid'])?$this->data['driver_uuid']:'';
			$date_end = isset($this->data['date_start'])?$this->data['date_start']:date("Y-m-d");
			
			$model = CDriver::getDriverByUUID($driver_uuid);
			$driver_id = $model->driver_id;
			
			$date_start = date('Y-m-d', strtotime('-7 days'));			
			$model = CDriver::getActivity($driver_id,$date_start,$date_end);
			if($model){
                $data = [];                

                foreach ($model as $items) {
                    $args = !empty($items->remarks_args) ?  json_decode($items->remarks_args,true) : array();
                    $data[] = [                        
						'created_at'=>PrettyDateTime::parse(new DateTime($items->created_at)),   
						'order_id'=>$items->order_id,
                        'remarks'=>t($items->remarks,(array)$args),                        
                    ];
                }

                $this->code = 1;
                $this->msg = "OK";
                $this->details = [
                    'data'=>$data                    
                ];
            } else $this->msg = t(HELPER_NO_RESULTS);

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();	
	}
	
	public function actiondriverWalletBalance()
	{
	    try {								
			
			$ref_id = isset($this->data['ref_id'])?$this->data['ref_id']:'';			
			$driver_data = CDriver::getDriverByUUID($ref_id);
			$driver_id = $driver_data->driver_id;									
			$card_id = CWallet::createCard( Yii::app()->params->account_type['driver'] ,$driver_id);				    	
			$balance = CWallet::getBalance($card_id);
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());
		   $balance = 0;		
		}	
				
		$this->code = 1;
		$this->msg = "OK";
		$this->details = array(
		  'balance'=>Price_Formatter::formatNumberNoSymbol($balance),
		  'price_format'=>array(
	         'symbol'=>Price_Formatter::$number_format['currency_symbol'],
	         'decimals'=>Price_Formatter::$number_format['decimals'],
	         'decimal_separator'=>Price_Formatter::$number_format['decimal_separator'],
	         'thousand_separator'=>Price_Formatter::$number_format['thousand_separator'],
	         'position'=>Price_Formatter::$number_format['position'],
	      )
		);		
		$this->responseJson();		
	}

	public function actiondriverWalletTransactions()
	{
		$data = array(); $card_id = 0; $driver_id = 0;
		$ref_id = isset($this->data['ref_id'])?$this->data['ref_id']:'';

		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	

		try {			
			$driver_data = CDriver::getDriverByUUID($ref_id);
			$driver_id = $driver_data->driver_id;			
			$card_id = CWallet::getCardID(Yii::app()->params->account_type['driver'],$driver_id);				
		} catch (Exception $e) {
			//
		}		

		$date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
		$date_end = isset($this->data['date_end'])?$this->data['date_end']:'';
		$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';

		$sortby = "transaction_id"; $sort = 'DESC';

		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = $page>0? intval($page)/intval($length) :0;
		$criteria=new CDbCriteria();
		$criteria->addCondition('card_id=:card_id');
		$criteria->params = array(':card_id'=>intval($card_id));
		
		if(!empty($date_start) && !empty($date_end)){
			$criteria->addBetweenCondition("DATE_FORMAT(transaction_date,'%Y-%m-%d')", $date_start , $date_end );
		}
		if(is_array($transaction_type) && count($transaction_type)>=1){
			$criteria->addInCondition('transaction_type',(array) $transaction_type );
		}		

		$criteria->order = "$sortby $sort";
		$count = AR_wallet_transactions::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        
        $models = AR_wallet_transactions::model()->findAll($criteria);
		if($models){
			foreach ($models as $item) {
				$description = Yii::app()->input->xssClean($item->transaction_description);        		
        		$parameters = json_decode($item->transaction_description_parameters,true);        		
        		if(is_array($parameters) && count($parameters)>=1){        			
        			$description = t($description,$parameters);
        		}
				$transaction_amount = Price_Formatter::formatNumber($item->transaction_amount);        		
        		switch ($item->transaction_type) {
        			case "debit":
        			case "payout":
        				$transaction_amount = "(".Price_Formatter::formatNumber($item->transaction_amount).")";
        				break;        		        			
        		}
        		
$trans_html = <<<HTML
<p class="m-0 $item->transaction_type">$transaction_amount</p>
HTML;


        		$data[]=array(
        		  'transaction_date'=>Date_Formatter::date($item->transaction_date),
        		  'transaction_description'=>$description,
        		  'transaction_amount'=>$trans_html,
        		  'running_balance'=>Price_Formatter::formatNumber($item->running_balance),
        		);
			}
		}

		$datatables = array(
			'draw'=>intval($draw),
			'recordsTotal'=>intval($count),
			'recordsFiltered'=>intval($count),
			'data'=>$data
		);
				  
	    $this->responseTable($datatables);
	}	

	public function actionClearWalletTransactions()
	{
		try {

			if(DEMO_MODE){
				$this->msg = t("This functions is not available in demo");
				$this->responseJson();
			}	
			
			$card_id = 0;
			$ref_id = Yii::app()->input->post('ref_id');			
			try {			
				$driver_data = CDriver::getDriverByUUID($ref_id);
				$driver_id = $driver_data->driver_id;			
				$card_id = CWallet::getCardID(Yii::app()->params->account_type['driver'],$driver_id);				
			} catch (Exception $e) {
				//
			}	
			
			AR_wallet_transactions::model()->deleteAll("card_id=:card_id",[
				':card_id'=>$card_id
			]);
			
			$this->code = 1;
			$this->msg = "Ok";

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}		
		$this->responseJson();
	}

	public function actiondriverWalletAdjustment()
	{
		try {

			$transaction_description = isset($this->data['transaction_description'])?$this->data['transaction_description']:'';
			$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';
			$transaction_amount = isset($this->data['transaction_amount'])?$this->data['transaction_amount']:0;
			
			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
            $multicurrency_enabled = $multicurrency_enabled==1?true:false;		
			$base_currency = Price_Formatter::$number_format['currency_code'];
			$admin_base_currency = AttributesTools::defaultCurrency();
			$exchange_rate_merchant_to_admin = 1; $exchange_rate_admin_to_merchant=1;
			
			if($multicurrency_enabled && $base_currency!=$admin_base_currency){
				$exchange_rate_merchant_to_admin = CMulticurrency::getExchangeRate($base_currency,$admin_base_currency);
				$exchange_rate_admin_to_merchant = CMulticurrency::getExchangeRate($admin_base_currency,$base_currency);
			}

			$params = array(
			  'transaction_description'=>$transaction_description,			  
			  'transaction_type'=>$transaction_type,
			  'transaction_amount'=>floatval($transaction_amount),
			  'meta_name'=>"adjustment",
			  'meta_value'=>CommonUtility::createUUID("{{admin_meta}}",'meta_value'),
			  'merchant_base_currency'=>$base_currency,
			  'admin_base_currency'=>$admin_base_currency,
			  'exchange_rate_merchant_to_admin'=>$exchange_rate_merchant_to_admin,
			  'exchange_rate_admin_to_merchant'=>$exchange_rate_admin_to_merchant,
			);
		    
			$ref_id = isset($this->data['ref_id'])?$this->data['ref_id']:'';			
			$driver_data = CDriver::getDriverByUUID($ref_id);
			$driver_id = $driver_data->driver_id;									
			$card_id = CWallet::createCard( Yii::app()->params->account_type['driver'] ,$driver_id);			
			CWallet::inserTransactions($card_id,$params);

			$this->code = 1; $this->msg = t("Successful");

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());	
		}	
		$this->responseJson();		
	}	

	public function actiondriverCashoutTransactions()
	{
		$driver_id = 0; $card_id=0; $data = [];
		try {											
			$ref_id = isset($this->data['ref_id'])?$this->data['ref_id']:'';			
			$driver_data = CDriver::getDriverByUUID($ref_id);
			$driver_id = $driver_data->driver_id;		
			$card_id = CWallet::getCardID(Yii::app()->params->account_type['driver'],$driver_id);										
		} catch (Exception $e) {		   
		}	
		
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'' )  :'';	

		$date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
		$date_end = isset($this->data['date_end'])?$this->data['date_end']:'';

		$sortby = "transaction_id"; $sort = 'DESC';

		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}

		$page = intval($page)/intval($length);		
		$criteria=new CDbCriteria();
		$criteria->condition = "card_id=:card_id  AND transaction_type=:transaction_type";
		$criteria->params  = array(
		  ':card_id'=>intval($card_id),
		  ':transaction_type'=>"cashout"
		);

		if(!empty($date_start) && !empty($date_end)){
			$criteria->addBetweenCondition("DATE_FORMAT(transaction_date,'%Y-%m-%d')", $date_start , $date_end );
		}

		$status_trans = AttributesTools::statusManagementTranslationList('payment', Yii::app()->language );

		$criteria->order = "$sortby $sort";
		$count = AR_wallet_transactions::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        
        $models = AR_wallet_transactions::model()->findAll($criteria);
		if($models){
			foreach ($models as $item) {
				$description = Yii::app()->input->xssClean($item->transaction_description);        		
        		$parameters = json_decode($item->transaction_description_parameters,true);        		
        		if(is_array($parameters) && count($parameters)>=1){        			
        			$description = t($description,$parameters);
        		}

				$transaction_amount = Price_Formatter::formatNumber($item->transaction_amount);
        		if($item->transaction_type=="debit"){
        			$transaction_amount = "(".Price_Formatter::formatNumber($item->transaction_amount).")";
        		}
        		
        		$trans_status = $item->status;
        		if(array_key_exists($item->status,(array)$status_trans)){
        			$trans_status = $status_trans[$item->status];
                }
        		$description = '<p class="m-0">'. $description .'</p>';
        		$description.= '<div class="badge payment '.$item->status.'">'.$trans_status.'</div>';
        		
        		$data[]=array(
        		  'transaction_amount'=>$transaction_amount,
        		  'transaction_description'=>$description,
        		  'transaction_date'=>Date_Formatter::date($item->transaction_date),          		  
        		);
			}
		}

		$datatables = array(
			'draw'=>intval($draw),
			'recordsTotal'=>intval($count),
			'recordsFiltered'=>intval($count),
			'data'=>$data
		);
		$this->responseTable($datatables);
	}	

	public function actiondriverOrderTransaction()
	{
		
		$data = array();		
						
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])? $this->data['order'][0]  :'';	
		$ref_id = isset($this->data['ref_id'])?$this->data['ref_id']:'';
						
		$sortby = "date_created"; $sort = 'DESC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = intval($page)/intval($length);		
		$criteria=new CDbCriteria();
		$criteria->alias ="a";		
		$criteria->select = "a.*,
		(
			select concat(first_name,' ',last_name)
			from {{client}}
			where client_id = a.client_id
			limit 0,1
		) as customer_name,

		(
			select restaurant_name
			from {{merchant}}
			where merchant_id = a.merchant_id
			limit 0,1
		) as restaurant_name	
		";

		try {			
			$driver_data = CDriver::getDriverByUUID($ref_id);
			$criteria->addCondition('a.driver_id=:driver_id');				
			$criteria->params = array(':driver_id' => $driver_data->driver_id );
		} catch (Exception $e) {
			//
		}
		
		if(!empty($search)){
			$criteria->addSearchCondition('a.order_id', intval($search) );		
			$criteria->addSearchCondition('a.merchant_id', intval($search)  , true , 'OR' );
		}		
				
		$criteria->order = "$sortby $sort";
		$count = AR_ordernew::model()->count($criteria); 
		
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        
										
        $models = AR_ordernew::model()->findAll($criteria);
		
        if($models){
        	foreach ($models as $item) {        						
        		$data[]=array(				 					
					'order_id'=>CHtml::link($item->order_id, $this->createAbsoluteUrl('orders/view',array('order_uuid'=>$item->order_uuid))),
					//'merchant_id'=>CHtml::link($item->restaurant_name, $this->createAbsoluteUrl('vendor/edit',array('id'=>$item->merchant_id))),
					'merchant_id'=>$item->restaurant_name,
					//'client_id'=>CHtml::link($item->customer_name, $this->createAbsoluteUrl('buyer/customer_update',array('id'=>$item->client_id))),
					'client_id'=>$item->customer_name,
					'total'=>Price_Formatter::formatNumber($item->total),
				);
        	}        	
        }
        
         $datatables = array(
		  'draw'=>intval($draw),
		  'recordsTotal'=>intval($count),
		  'recordsFiltered'=>intval($count),
		  'data'=>$data
		);				
		$this->responseTable($datatables);
	}		

	public function actiondriverTipsTransaction()
	{
		$data = array();		
						
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])? $this->data['order'][0]  :'';	
		$ref_id = isset($this->data['ref_id'])?$this->data['ref_id']:'';
						
		$sortby = "date_created"; $sort = 'DESC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = intval($page)/intval($length);		
		$criteria=new CDbCriteria();
		$criteria->alias ="a";		
		$criteria->select = "a.*,
		(
			select concat(first_name,' ',last_name)
			from {{client}}
			where client_id = a.client_id
			limit 0,1
		) as customer_name,

		(
			select restaurant_name
			from {{merchant}}
			where merchant_id = a.merchant_id
			limit 0,1
		) as restaurant_name	
		";

		try {			
			$driver_data = CDriver::getDriverByUUID($ref_id);
			$criteria->addCondition('a.driver_id=:driver_id AND courier_tip>0');				
			$criteria->params = array(':driver_id' => $driver_data->driver_id );
		} catch (Exception $e) {
			//
		}
		
		if(!empty($search)){
			$criteria->addSearchCondition('a.order_id', intval($search) );		
			$criteria->addSearchCondition('a.merchant_id', intval($search)  , true , 'OR' );
		}		
				
		$criteria->order = "$sortby $sort";
		$count = AR_ordernew::model()->count($criteria); 
		
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        
										
        $models = AR_ordernew::model()->findAll($criteria);
		
        if($models){
        	foreach ($models as $item) {        						
        		$data[]=array(				 					
					'order_id'=>CHtml::link($item->order_id, $this->createAbsoluteUrl('orders/view',array('order_uuid'=>$item->order_uuid))),
					'merchant_id'=>$item->restaurant_name,
					'client_id'=>$item->customer_name,
					'courier_tip'=>Price_Formatter::formatNumber($item->courier_tip),
				);
        	}        	
        }
        
         $datatables = array(
		  'draw'=>intval($draw),
		  'recordsTotal'=>intval($count),
		  'recordsFiltered'=>intval($count),
		  'data'=>$data
		);				
		$this->responseTable($datatables);
	}			

	public function actiontimeLogs()
	{
		$data = array();		
						
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
		$ref_id = isset($this->data['ref_id'])?$this->data['ref_id']:'';
						
		$sortby = "date_created"; $sort = 'DESC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = intval($page)/intval($length);		
		$criteria=new CDbCriteria();	
		$criteria->alias = "a";
		$criteria->select ="a.*, b.zone_name";
		$criteria->join='
		LEFT JOIN {{zones}} b on  a.zone_id = b.zone_id 		
		';		
				
		try {			
			$driver_data = CDriver::getDriverByUUID($ref_id);
			$criteria->addCondition('driver_id=:driver_id AND on_demand=0');				
			$criteria->params = array(':driver_id' => $driver_data->driver_id );
		} catch (Exception $e) {
			//
		}

		if(!empty($search)){
			$criteria->addSearchCondition('a.zone_id', $search );			
		}		
						
		$criteria->order = "$sortby $sort";
		$count = AR_driver_schedule::model()->count($criteria); 
		
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        
						
        $models = AR_driver_schedule::model()->findAll($criteria);
        if($models){
        	foreach ($models as $item) {        	
				
				$end_shift_url = Yii::app()->createAbsoluteUrl("/merchantdriver/endshift",[
					'id'=>$item->schedule_uuid
				]);
				$delete_shift_url = Yii::app()->createAbsoluteUrl("/merchantdriver/deleteshift",[
					'id'=>$item->schedule_uuid
				]);
				$label = t("End Shift");


$buttons = <<<HTML
<div class="btn-group btn-group-actions" role="group">
 <a href="$end_shift_url"  class="btn btn-light tool_tips" data-toggle="tooltip" data-placement="top" title="$label" data-original-title="$label" >
	<i class="zmdi zmdi-filter-tilt-shift"></i>
</a>
 <a href="$delete_shift_url"  class="btn btn-light tool_tips"><i class="zmdi zmdi-delete"></i></a> 
</div>
HTML;
                

        		$data[]=array(				 
					'schedule_id'=>$item->schedule_id,
					'zone_id'=>$item->zone_name,
					'date_created'=>Date_Formatter::date($item->time_start),
					'time_start'=>Date_Formatter::Time($item->time_start),
					'time_end'=>Date_Formatter::Time($item->time_end),
					'shift_time_started'=>!empty($item->shift_time_started) ? Date_Formatter::Time($item->shift_time_started) : '',
					'shift_time_ended'=>!empty($item->shift_time_ended)?Date_Formatter::Time($item->shift_time_ended): '',
					'date_modified'=>$item->shift_time_ended==null?$buttons:''
				);
        	}        	
        }
        
         $datatables = array(
		  'draw'=>intval($draw),
		  'recordsTotal'=>intval($count),
		  'recordsFiltered'=>intval($count),
		  'data'=>$data
		);				
		$this->responseTable($datatables);
	}

	public function actiondriverReviewList()
	{
		$data = array();		
						
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
		$ref_id = isset($this->data['ref_id'])?$this->data['ref_id']:'';
						
		$sortby = "date_created"; $sort = 'DESC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = intval($page)/intval($length);		
		$criteria=new CDbCriteria();
		$criteria->alias ="a";
		$criteria->select = "
		a.review_id,
		a.driver_id,		
		concat(driver.first_name,' ',driver.last_name) as driver_fullname,
		driver.driver_uuid,		
		a.customer_id,		
		concat(client.first_name,' ',client.last_name) as customer_fullname,
		a.rating,
		a.review_text,
		a.status
		";
		$criteria->join = "
		LEFT JOIN {{driver}} driver on a.driver_id = driver.driver_id			
		LEFT JOIN {{client}} client on a.customer_id = client.client_id			
		";
		if(!empty($ref_id)){
			$criteria->condition = "driver.driver_uuid=:driver_uuid";		
			$criteria->params = [
				':driver_uuid'=>$ref_id
			];
		} else {
			$criteria->condition = "driver.merchant_id = :merchant_id";		
			$criteria->params = [
				':merchant_id'=>Yii::app()->merchant->merchant_id
			];
		}
		$criteria->order = "$sortby $sort";						
		//dump($criteria);die();
		$count = AR_driver_reviews::model()->count($criteria); 
		
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        							
        $models = AR_driver_reviews::model()->findAll($criteria);		
        if($models){			
        	foreach ($models as $item) {        						
        		$data[]=array(				 
				  'review_id'=>$item->review_id,
				  'driver_id'=>CHtml::link($item->driver_fullname, $this->createAbsoluteUrl('driver/update',array('id'=>$item->driver_uuid))),				  
				  'client_id'=>$item->customer_fullname,
				  'review_text'=>t('<h6>[review] <span class="badge ml-2 post [status]">[status_title]</span></h6>',[
					'[review]'=>$item->review_text,
					'[status]'=>$item->status,
					'[status_title]'=>t($item->status),
				  ]),
				  'rating'=>'<label class="badge btn-green">'.$item->rating.' <i class="zmdi zmdi-star"></i> </label>',
				  'update_url'=>Yii::app()->createUrl("/merchantdriver/review_update/",array('id'=>$item->review_id)),
        		  'delete_url'=>Yii::app()->createUrl("/merchantdriver/review_delete/",array('id'=>$item->review_id)),				  
				);
        	}        	
        }
        
         $datatables = array(
		  'draw'=>intval($draw),
		  'recordsTotal'=>intval($count),
		  'recordsFiltered'=>intval($count),
		  'data'=>$data
		);				
		$this->responseTable($datatables);
	}	

	public function actioncarList()
	{
		$data = array();		
		$merchant_id = Yii::app()->merchant->merchant_id;

		$vehicle_maker = CommonUtility::getDataToDropDown("{{admin_meta}}","meta_id",'meta_value',"WHERE meta_name='vehicle_maker'","Order by meta_name");
		$vehicle_type = CommonUtility::getDataToDropDown("{{admin_meta}}","meta_id",'meta_value',"WHERE meta_name='vehicle_type'","Order by meta_name");
								
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
						
		$sortby = "date_created"; $sort = 'DESC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = intval($page)/intval($length);		
		$criteria=new CDbCriteria();	
		$criteria->addCondition("driver_id=0 AND merchant_id=:merchant_id");
		$criteria->params = [
			':merchant_id'=>$merchant_id
		];

		if(!empty($search)){
			$criteria->addSearchCondition('plate_number', $search );
			$criteria->addSearchCondition('color', $search , true , 'OR' );			
		}

		$criteria->order = "$sortby $sort";
		$count = AR_driver_vehicle::model()->count($criteria); 
		
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        
						
        $models = AR_driver_vehicle::model()->findAll($criteria);
        if($models){
        	foreach ($models as $item) {        	
								
				$checkbox = Yii::app()->controller->renderPartial('/attributes/html_checkbox',array(
					'id'=>"banner[$item->vehicle_uuid]",
					'check'=>$item->active==1?true:false,
					'value'=>$item->vehicle_uuid,
					'label'=>'',		
					'class'=>'set_status'
				),true);

				$photo = CMedia::getImage($item->photo,$item->path,'@thumbnail',
		         CommonUtility::getPlaceholderPhoto('car','car.png'));

        		$data[]=array(				 
				  'vehicle_uuid'=>$item->vehicle_uuid,	
				  'vehicle_id'=>$item->vehicle_id,				  
				  'active'=>$checkbox, 				  		  
				  'plate_number'=>'<div class="row"><div class="col-4"><img src="'.$photo.'" class="img-50 rounded-circle" /></div><div class="col">'.
				  $item->plate_number."<p>".t("ID")."# $item->vehicle_id</p>".'</div></div>',
				  'vehicle_type_id'=>isset($vehicle_type[$item->vehicle_type_id])?$vehicle_type[$item->vehicle_type_id]:'',			
				  'maker'=>isset($vehicle_maker[$item->maker])?$vehicle_maker[$item->maker]:'' ,				  
				  'update_url'=>Yii::app()->createUrl("/merchantdriver/update_car/",array('id'=>$item->vehicle_uuid)),
        		  'delete_url'=>Yii::app()->createUrl("/merchantdriver/delete_car/",array('id'=>$item->vehicle_uuid)),				  
				  'id'=>$item->vehicle_uuid,
				  'actions'=>"set_car_status"				  
				);
        	}        	
        }
        
         $datatables = array(
		  'draw'=>intval($draw),
		  'recordsTotal'=>intval($count),
		  'recordsFiltered'=>intval($count),
		  'data'=>$data
		);				
		$this->responseTable($datatables);
	}	

	public function actiongroupList()
	{
		$data = array();		
						
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
		$ref_id = isset($this->data['ref_id'])?$this->data['ref_id']:'';
						
		$sortby = "date_created"; $sort = 'DESC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = intval($page)/intval($length);		
		$criteria=new CDbCriteria();
		$criteria->alias ="a";		
		$criteria->select = "a.*,
		(
		   select count(*) from {{driver_group_relations}}
		   where group_id = a.group_id
		) as drivers
		";

		$criteria->condition = "merchant_id=:merchant_id";
		$criteria->params = [':merchant_id'=>Yii::app()->merchant->merchant_id];

		if(!empty($search)){
			$criteria->addSearchCondition('a.group_name', $search );			
		}		
				
		$criteria->order = "$sortby $sort";
		$count = AR_driver_group::model()->count($criteria); 
		
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        
								
        $models = AR_driver_group::model()->findAll($criteria);
		
        if($models){
        	foreach ($models as $item) {  				
        		$data[]=array(				 
				  'group_uuid'=>$item->group_uuid,
				  'group_name'=>$item->group_name,
				  'drivers'=>$item->drivers,
				  'update_url'=>Yii::app()->createUrl("/merchantdriver/group_update/",array('id'=>$item->group_uuid)),
        		  'delete_url'=>Yii::app()->createUrl("/merchantdriver/group_delete/",array('id'=>$item->group_uuid)),				  
				);
        	}        	
        }
        
         $datatables = array(
		  'draw'=>intval($draw),
		  'recordsTotal'=>intval($count),
		  'recordsFiltered'=>intval($count),
		  'data'=>$data
		);				
		$this->responseTable($datatables);
	}		

	public function actiongetDriverSched()
	{
		try {

			$data =  [];			
			$start = isset($this->data['start'])? date("Y-m-d",strtotime($this->data['start'])) :null;
			$end = isset($this->data['end'])? date("Y-m-d",strtotime($this->data['end'])) :null;

			$zone_list = CommonUtility::getDataToDropDown("{{zones}}",'zone_id','zone_name',"
		    WHERE merchant_id = 0","ORDER BY zone_name ASC"); 			
		
			$criteria=new CDbCriteria();
			$criteria->select = "a.*,
			(
				select concat(first_name,' ',last_name,'|',color_hex,'|',photo,'|',path)
				from {{driver}}
				where driver_id = a.driver_id
			) as fullname,
			(
				select plate_number
				from {{driver_vehicle}}
				where vehicle_id = a.vehicle_id
			) as plate_number			
			";
			$criteria->alias = "a";
			$criteria->addCondition("active=:active AND merchant_id=:merchant_id
			AND a.driver_id IN (
				select driver_id from {{driver}}
				where employment_type='employee'
			)
			");
		    $criteria->params = array(
				':active' => 1 ,
				':merchant_id'=>Yii::app()->merchant->merchant_id
			);
			$criteria->addBetweenCondition('DATE(time_start)',$start,$end);			
			$criteria->order = "time_start ASC";
			$criteria->limit = 500;
														
			if($model = AR_driver_schedule::model()->findAll($criteria)){								
				foreach ($model as $item) {  
					//$fulldata = explode("|",$item->fullname);
					$fulldata = explode("|", $item->fullname ?? '');
					$fullname = isset($fulldata[0])?$fulldata[0]:'';
					$color_hex = isset($fulldata[1])?$fulldata[1]:'';
					$photo = isset($fulldata[2])?$fulldata[2]:'';
					$path = isset($fulldata[3])?$fulldata[3]:'';					
					$avatar = CMedia::getImage($photo,$path,'@thumbnail',CommonUtility::getPlaceholderPhoto('driver'));					
					$data[] = [
					    'id'=>$item->schedule_uuid,
						'title'=>"$fullname ($item->plate_number)",						
						'start'=>date("c",strtotime($item->time_start)),
						'end'=>date("c",strtotime($item->time_end)),
						'color'=>$color_hex,
						'extendedProps'=>[
							'name'=>$fullname,
							'plate_number'=>$item->plate_number,
							'time'=>Date_Formatter::Time($item->time_start)." - ".Date_Formatter::Time($item->time_end),
							'avatar'=>$avatar,
							'zone_name'=>isset(($zone_list[$item->zone_id]))?$zone_list[$item->zone_id]:''
						]
					];
				}
			}

			$this->responseSelect2($data);
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			
			dump($this->msg);
		}		
	}

	public function actiongetZoneList2()
	{
		try {
			$merchant_id = Yii::app()->merchant->merchant_id;
			$zone_list = CommonUtility::getDataToDropDown("{{zones}}",'zone_id','zone_name',"
		    WHERE merchant_id = ".q($merchant_id)." ","ORDER BY zone_name ASC"); 
			if($zone_list){
				$zone_list = CommonUtility::ArrayToLabelValue($zone_list);
				$this->code = 1;
				$this->msg = "Ok";
				$this->details = $zone_list;
			} else $this->msg = t(HELPER_NO_RESULTS);			

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();	
	}	

	public function actionsearchDriver()
	{
		try {

			$data = [];			
			$search = isset($this->data['search'])?$this->data['search']:'';	
			$merchant_id = Yii::app()->merchant->merchant_id;
			$employment_type = isset($this->data['employment_type'])?$this->data['employment_type']:'';

			$query = 'status=:status AND merchant_id=:merchant_id';
			$criteria=new CDbCriteria();	
			if(!empty($employment_type)){
				$query.=" ";
				$query.="AND employment_type=:employment_type";
			} 			
			$criteria->addCondition($query);
			if(!empty($employment_type)){
				$criteria->params = array(
					':status' => 'active',
					':merchant_id'=>intval($merchant_id),
					':employment_type'=>trim($employment_type),
				);
			} else {
				$criteria->params = array(
					':status' => 'active',
					':merchant_id'=>intval($merchant_id) 
				);
			}
		    
			if(!empty($search)){
				$criteria->addSearchCondition('first_name', $search );
				$criteria->addSearchCondition('last_name', $search , true , 'OR' );
			}

			$criteria->order = "first_name ASC";
			$criteria->limit = 10;
															
			if($model = AR_driver::model()->findAll($criteria)){
				foreach ($model as $item) {  
					$data[] = [
						'id'=>$item->driver_id,
						'text'=>"$item->first_name $item->last_name"
					];
				}
			}			
								
			$result = array(
			  'results'=>$data
			);	    	
			$this->responseSelect2($result);

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			
		}		
	}	

	public function actionsearchCar()
	{
		try {

			$data = [];			
			$search = isset($this->data['search'])?$this->data['search']:'';	

			$criteria=new CDbCriteria();		
			$criteria->addCondition("driver_id=0 AND merchant_id=".Yii::app()->merchant->merchant_id." ");
			if(!empty($search)){
				$criteria->addSearchCondition('plate_number', $search );
				$criteria->addSearchCondition('maker', $search , true , 'OR' );
			}

			$criteria->order = "plate_number ASC";
			$criteria->limit = 10;
			
			if($model = AR_driver_vehicle::model()->findAll($criteria)){
				foreach ($model as $item) {  
					$data[] = [
						'id'=>$item->vehicle_id,
						'text'=>"$item->plate_number"
					];
				}
			}			
								
			$result = array(
			  'results'=>$data
			);	    	
			$this->responseSelect2($result);

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			
		}		
	}	

	public function actionaddSchedule()
	{
		try {
						
			$schedule_uuid = isset($this->data['schedule_uuid'])?trim($this->data['schedule_uuid']):null;			
			$zone_id = isset($this->data['zone_id'])?intval($this->data['zone_id']):0;
			$driver_id = isset($this->data['driver_id'])?intval($this->data['driver_id']):0;
			$vehicle_id = isset($this->data['vehicle_id'])?intval($this->data['vehicle_id']):0;
			$date_start = isset($this->data['date_start'])? date("Y-m-d",strtotime($this->data['date_start'])) :null;
			$time_start = isset($this->data['time_start'])?$this->data['time_start']:null;
			$time_end = isset($this->data['time_end'])?$this->data['time_end']:null;
			$instructions = isset($this->data['instructions'])?$this->data['instructions']:'';			
			
			$model = new AR_driver_schedule;
			if(!empty($schedule_uuid)){
				$model = AR_driver_schedule::model()->find("schedule_uuid=:schedule_uuid",[
					':schedule_uuid'=>$schedule_uuid
				]);
				if(!$model){
					$this->msg = t(HELPER_RECORD_NOT_FOUND);
					$this->responseJson();	
				}
			}
			$model->merchant_id = Yii::app()->merchant->merchant_id;
			$model->zone_id = $zone_id;
			$model->driver_id  = $driver_id;
			$model->vehicle_id  = $vehicle_id;			
			$model->time_start  = "$date_start $time_start";
			$model->time_end  = "$date_start $time_end";
			$model->instructions = $instructions;			
			if($model->save()){
				$this->code = 1;
				$this->msg = !empty($schedule_uuid)? t("Schedule updated") :   t("Schedule added");
			} else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			
		}		
		$this->responseJson();	
	}	

	public function actionshiftList()
	{
		$data = array();		
						
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'' )  :'';	
		$ref_id = isset($this->data['ref_id'])?$this->data['ref_id']:'';
								
		$sortby = "date_created"; $sort = 'DESC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = intval($page)/intval($length);		
		$criteria=new CDbCriteria();
		$criteria->alias ="a";		

		$criteria->condition = "merchant_id=:merchant_id";
		$criteria->params = [
			'merchant_id'=>Yii::app()->merchant->merchant_id
		];

		if(!empty($search)){			
			$criteria->addCondition("a.zone_id IN (
				select zone_id from {{zones}}
				where zone_name LIKE ".q("$search%")."
			)");
		}		
				
		$criteria->order = "$sortby $sort";				
		$count = AR_driver_shift_schedule::model()->count($criteria); 
		
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        
												
        $models = AR_driver_shift_schedule::model()->findAll($criteria);

		$zone_list = CommonUtility::getDataToDropDown("{{zones}}",'zone_id','zone_name',"
		WHERE merchant_id = ".Yii::app()->merchant->merchant_id." ","ORDER BY zone_name ASC");		
		
        if($models){
        	foreach ($models as $item) {  				
        		$data[]=array(				 
				  'shift_id'=>$item->shift_id,
				  'shift_uuid'=>$item->shift_uuid,				  
				  'zone_id'=>isset($zone_list[$item->zone_id])?$zone_list[$item->zone_id]:$item->zone_id,				  
				  'time_start'=>Date_Formatter::dateTime($item->time_start),
				  'time_end'=>Date_Formatter::dateTime($item->time_end),
				  'max_allow_slot'=>$item->max_allow_slot>0?$item->max_allow_slot:t("unlimited"),
				  'update_url'=>Yii::app()->createUrl("/merchantdriver/shift_update/",array('id'=>$item->shift_uuid)),
        		  'delete_url'=>Yii::app()->createUrl("/merchantdriver/shift_delete/",array('id'=>$item->shift_uuid)),				  
				);
        	}        	
        }
		
         $datatables = array(
		  'draw'=>intval($draw),
		  'recordsTotal'=>intval($count),
		  'recordsFiltered'=>intval($count),
		  'data'=>$data
		);				
		$this->responseTable($datatables);
	}			

	public function actiongetSchedule()
	{
		try {
			
			$schedule_uuid	= isset($this->data['schedule_uuid'])? $this->data['schedule_uuid'] :null;
			$model=AR_driver_schedule::model()->find("schedule_uuid	=:schedule_uuid",array(
				':schedule_uuid'=>$schedule_uuid	
			));
			if($model){

				$driver = []; $car = [];
				try {
					$drivers = CDriver::getDriver($model->driver_id);			
					$driver = [
						'id'=>$drivers->driver_id,
						'text'=>"$drivers->first_name $drivers->last_name"
					];
			    } catch (Exception $e) {
					//
				}

				try {
					$cars = CDriver::getVehicle($model->vehicle_id);					
					$car = [
						'id'=>$cars->vehicle_id,
						'text'=>"$cars->plate_number"
					];
				} catch (Exception $e) {
					//
				}


				$zone_list =  [];
				try {
					$zone = CDriver::getZone($model->zone_id , Yii::app()->merchant->merchant_id );
					$zone_list = [
						'label'=>$zone->zone_name,
						'value'=>$zone->zone_id,
					];
				} catch (Exception $e) {}				
				
				$this->code = 1;
				$this->msg = "ok";
				$data['sched'] = [
					'schedule_uuid'=>$model->schedule_uuid,
					'driver_id'=>$model->driver_id,
					'driver_id'=>$model->driver_id,
					'vehicle_id'=>$model->vehicle_id,
					'zone_id'=>$zone_list,
					'date_start'=>Date_Formatter::date($model->time_start,"yyyy-MM-dd",true),
					'time_start'=>Date_Formatter::Time($model->time_start,"HH:mm",true),
					'time_end'=>Date_Formatter::Time($model->time_end,"HH:mm",true),
					'instructions'=>$model->instructions
				];
				$data['driver'] = $driver;
				$data['car'] = $car;
				$this->details = $data;
			} else $this->msg = t(HELPER_RECORD_NOT_FOUND);			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			
		}		
		$this->responseJson();	
	}	

	public function actiondeleteschedule()
	{
		try {

			$schedule_uuid	= isset($this->data['schedule_uuid'])? $this->data['schedule_uuid'] :null;
			$model=AR_driver_schedule::model()->find("schedule_uuid	=:schedule_uuid",array(
				':schedule_uuid'=>$schedule_uuid	
			));
			if($model){
				if($model->delete()){
					$this->code = 1;
			        $this->msg = t("Schedule deleted");
				} else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors(),"<br/>");
			} else $this->msg = t(HELPER_RECORD_NOT_FOUND);	

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			
		}		
		$this->responseJson();	
	}	

	public function actionzoneList()
	{
		$data = array();
		
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
		$filter = isset($this->data['filter'])?$this->data['filter']:'';
		
		$date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
		$date_end = isset($this->data['date_end'])?$this->data['date_end']:'';
		$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';				
				
		$sortby = "zone_id"; $sort = 'DESC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
		
		$page = $page>0? intval($page)/intval($length) :0;
		$criteria=new CDbCriteria();			
		$criteria->condition = "merchant_id=:merchant_id";		
		$criteria->params = [
			':merchant_id'=> Yii::app()->merchant->merchant_id
		];
		
		if (is_string($search) && strlen($search) > 0){
			$criteria->addSearchCondition('zone_name', $search );			
		}
				
		$criteria->order = "$sortby $sort";
		$count = AR_zones::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        
                        
        $models = AR_zones::model()->findAll($criteria);
        if($models){        	
        	foreach ($models as $item) {    
        		$data[]=array(
        		  'zone_id'=>$item->zone_id,
        		  'zone_name'=>$item->zone_name,
        		  'description'=>$item->description,
        		  'zone_id'=>$item->zone_id,
        		  'update_url'=>Yii::app()->createUrl("/merchantdriver/zone_update/",array('id'=>$item->zone_uuid)),
        		  'delete_url'=>Yii::app()->createUrl("/merchantdriver/zone_delete/",array('id'=>$item->zone_uuid)),
        		);
        	}
        }
        
        $datatables = array(
		  'draw'=>intval($draw),
		  'recordsTotal'=>intval($count),
		  'recordsFiltered'=>intval($count),
		  'data'=>$data
		);
				
		$this->responseTable($datatables);
	}		

	public function actioncashoutList()
	{
		$data = array();								
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'') :'';	
		$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';	
		$filter = isset($this->data['filter'])?$this->data['filter']:'';	
		$date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
		$date_end = isset($this->data['date_end'])?$this->data['date_end']:'';
					
		$sortby = "a.transaction_date"; $sort = 'DESC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}			
		
		$page = $page>0? intval($page)/intval($length) :0;
		$criteria=new CDbCriteria();
		$criteria->alias = "a";
		
		$criteria->select="a.transaction_uuid,a.card_id,a.transaction_amount,a.transaction_date, a.status,
		b.driver_id,b.merchant_id, concat(b.first_name,' ',b.last_name) as driver_name , b.photo as logo , b.path";
		
		$criteria->join="LEFT JOIN {{driver}} b on a.card_id = 
		(
		 select card_id from {{wallet_cards}}
		 where account_type=".q(Yii::app()->params->account_type['driver'])." and account_id=b.driver_id
		)		
		";		
		
		$criteria->condition="transaction_type=:transaction_type AND b.merchant_id=:merchant_id";
		$criteria->params = array(		 
		 ':transaction_type'=>'cashout',
		 ':merchant_id'=>Yii::app()->merchant->merchant_id
		);
				
		if(is_array($transaction_type) && count($transaction_type)>=1){
			$criteria->addInCondition('a.status',(array) $transaction_type );
		}
		
		if(!empty($search)){
		    $criteria->addSearchCondition('a.first_name', $search);
        }
        
        if(is_array($filter) && count($filter)>=1){
        	$filter_merchant_id = isset($filter['driver_id'])?$filter['driver_id']:'';
        	$criteria->addSearchCondition('b.driver_id', $filter_merchant_id );
        }
        
        if(!empty($date_start) && !empty($date_end)){
			$criteria->addBetweenCondition("DATE_FORMAT(transaction_date,'%Y-%m-%d')", $date_start , $date_end );
		}
                		
		$criteria->order = "$sortby $sort";
		$count = AR_wallet_transactions::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);     		
        $models = AR_wallet_transactions::model()->findAll($criteria);
        
        if($models){			
        	foreach ($models as $item) {			
        		        	        	
        	$logo_url = CMedia::getImage($item->logo,$item->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('customer'));        	
        	$transaction_amount = Price_Formatter::formatNumber($item->transaction_amount);
        	$status = $item->status;
        	        	
        		
$logo_html = <<<HTML
<img src="$logo_url" class="img-60 rounded-circle" />
HTML;

$amount_html = <<<HTML
<p class="m-0"><b>$transaction_amount</b></p>
<p class="m-0"><span class="badge payment $status">$status</span></p>
HTML;



        	  $data[]=array(
        		'driver_id'=>$item->driver_id,        		        		
        		'photo'=>$logo_html,        		
        		'transaction_date'=>Date_Formatter::date($item->transaction_date),
        		'driver_name'=>Yii::app()->input->xssClean($item->driver_name),        		
        		'transaction_amount'=>$amount_html,
        		'transaction_uuid'=>$item->transaction_uuid,
        	   );
        	}
        }
                 
        $datatables = array(
		  'draw'=>intval($draw),
		  'recordsTotal'=>intval($count),
		  'recordsFiltered'=>intval($count),
		  'data'=>$data
		);
		
        $this->responseTable($datatables);
	}	

	public function actioncashoutSummary()
	{
		try {
			
			$self_delivery = isset(Yii::app()->params['settings_merchant']['self_delivery'])?Yii::app()->params['settings_merchant']['self_delivery']:false;
		    $self_delivery = $self_delivery==1?true:false;				
			$merchant_id = $self_delivery==true?Yii::app()->merchant->merchant_id:0; 			

			$data = CPayouts::payoutSummary('cashout',$merchant_id);
			$this->code = 1;
			$this->msg = "ok";
			$this->details = array(
			  'summary'=>$data,
			  'price_format'=>array(
		         'symbol'=>Price_Formatter::$number_format['currency_symbol'],
		         'decimals'=>Price_Formatter::$number_format['decimals'],
		         'decimal_separator'=>Price_Formatter::$number_format['decimal_separator'],
		         'thousand_separator'=>Price_Formatter::$number_format['thousand_separator'],
		         'position'=>Price_Formatter::$number_format['position'],
		      )
			);
			
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		   		   
		}	
		$this->responseJson();
	}
		
	public function actioncollectCashList()
	{
		$data = array();
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
						
		$sortby = "date_created"; $sort = 'DESC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = intval($page)/intval($length);		
		$criteria=new CDbCriteria();		
		$criteria->alias = "a";
		$criteria->select="a.*, concat(b.first_name,' ',b.last_name) as driver_name";
		
		$criteria->join="LEFT JOIN {{driver}} b on a.driver_id = b.driver_id";		

		$criteria->condition="a.merchant_id=:merchant_id";
		$criteria->params = [
			':merchant_id'=>Yii::app()->merchant->merchant_id
		];

		if(!empty($search)){
			$criteria->addSearchCondition('a.reference_id', $search );			
			$criteria->addSearchCondition('b.first_name', $search , true , 'OR' );
			$criteria->addSearchCondition('b.last_name', $search , true , 'OR' );
		}		
								
		$criteria->order = "$sortby $sort";		
		$count = AR_driver_collect_cash::model()->count($criteria); 
		
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);      
				
		$models = AR_driver_collect_cash::model()->findAll($criteria);
		if($models){
			foreach ($models as $item) { 
				$data[] = [
					'collect_id'=>$item->collect_id,
					'transaction_date'=>Date_Formatter::dateTime($item->transaction_date),
					'driver_id'=>!empty($item->driver_name)?$item->driver_name:t("Not found"),
					'amount_collected'=>Price_Formatter::formatNumber($item->amount_collected),
					'reference_id'=>$item->reference_id,
					'collection_uuid'=>$item->collection_uuid,
					'view_url'=>Yii::app()->createUrl("/merchantdriver/collect_transactions/",array('id'=>$item->collection_uuid)),
					'delete_url'=>Yii::app()->createUrl("/merchantdriver/collect_cash_void/",array('id'=>$item->collection_uuid)),				  			
				];
			}
		}

		$datatables = array(
			'draw'=>intval($draw),
			'recordsTotal'=>intval($count),
			'recordsFiltered'=>intval($count),
			'data'=>$data
		);				
		$this->responseTable($datatables);
	}

	public function actiongetPayoutDetails()
	{
		try {
			
			$merchant = array(); $merchant_id = 0;
		    $transaction_uuid = isset($this->data['transaction_uuid'])?$this->data['transaction_uuid']:'';		    		    
		    $data = CPayouts::getPayoutDetails($transaction_uuid,false);			
		    $provider = AttributesTools::paymentProviderDetails( isset($data['provider'])?$data['provider']:'' );		    
		    $card_id = isset($data['card_id'])?$data['card_id']:'';		    
		    try{
		       $merchant_id = CWallet::getAccountID($card_id);		    
		       $merchant_data = CMerchants::get($merchant_id);
			   $merchant = array(
			      'restaurant_name'=>Yii::app()->input->xssClean($merchant_data->restaurant_name)
			   );
		    } catch (Exception $e) {
		    	//
		    }
		    
		    $this->code = 1;
		    $this->msg = "ok";
		    $this->details = array(
		      'data'=>$data,
		      'merchant'=>$merchant,
		      'provider'=>$provider
		    );		    
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		   		   
		}	
		$this->responseJson();
	}	
	
	public function actioncancelPayout()
	{
		try {
						
			$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'payout';
			$transaction_uuid = isset($this->data['transaction_uuid'])?$this->data['transaction_uuid']:'';
			
			$model = AR_wallet_transactions::model()->find("transaction_uuid=:transaction_uuid",array(
			 ':transaction_uuid'=>$transaction_uuid
			));			
			if($model){							
				$params = array(				  
				  'transaction_description'=>"Cancel payout reference #{{transaction_id}}",
				  'transaction_description_parameters'=>array('{{transaction_id}}'=>$model->transaction_id),					  
				  'transaction_type'=>"credit",
				  'transaction_amount'=>floatval($model->transaction_amount),				  
				);									
				$model->scenario = $transaction_type."_cancel";				

				$model->status="cancelled";		
												
				if($model->save()){
				   CWallet::inserTransactions($model->card_id,$params);					   
				   $this->code = 1;
				   $this->msg = t("Payout cancelled");
				} else $this->msg = CommonUtility::parseError( $model->getErrors());
												
			} else $this->msg = t("Transaction not found");
			
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		   		   
		}	
		$this->responseJson();
	}	

	public function actionpayoutPaid()
	{
		try {			
			$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'payout';
			$transaction_uuid = isset($this->data['transaction_uuid'])?$this->data['transaction_uuid']:'';
			$model = AR_wallet_transactions::model()->find("transaction_uuid=:transaction_uuid",array(
			 ':transaction_uuid'=>$transaction_uuid
			));			
			if($model){				
				$model->scenario = $transaction_type."_paid";
				$model->status = 'paid';
				if($model->save()){
					$this->code = 1;
					$this->msg = t("Payout status set to paid");
				} else $this->msg = CommonUtility::parseError( $model->getErrors());
			} else $this->msg = t("Transaction not found");
			
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		   		   
		}	
		$this->responseJson();
	}	

	public function actionrequestNewOrder()
	{
		try {
			
			$merchant_id = Yii::app()->merchant->merchant_id;			
			$criteria = new CDbCriteria;	
			$criteria->alias = "a";
			$criteria->select = "a.order_id, concat(first_name,' ',last_name) as customer_name";						
			$criteria->addCondition("a.merchant_id=:merchant_id AND is_view=:is_view AND a.status!=:status AND DATE_FORMAT(a.date_created,'%Y-%m-%d')=:date_created");		
			$criteria->params = [
				':is_view'=>0,
				':merchant_id'=>$merchant_id,
				':status'=>AttributesTools::initialStatus(),
				':date_created'=>CommonUtility::dateOnly()
			];
			$criteria->join='
			LEFT JOIN {{client}} b on a.client_id = b.client_id 			
			';	
			$criteria->order = "a.order_id ASC";			
			$model = AR_ordernew::model()->findAll($criteria);					
			if($model){								
				$data = [];
				foreach ($model as $item) {								
					$data[] = [
						'title'=>t("You have new order"),
						'message'=>t("Order#{order_id} from {customer_name}",[
							'{order_id}'=>$item->order_id,
							'{customer_name}'=>$item->customer_name
						])
					];
				}
				$this->code = 1;
				$this->msg = "Ok";
				$this->details = $data;
			} else $this->msg = t("no new order");			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}		
		$this->responseJson();
	}	

	public function actiongetlocationautocomplete()
	{
		try {

			$q = Yii::app()->input->post("q");
			
			if(!isset(Yii::app()->params['settings']['map_provider'])){
				$this->msg = t("No default map provider, check your settings.");
				$this->responseJson();
		    }

		   MapSdk::$map_provider = Yii::app()->params['settings']['map_provider'];		   
		   MapSdk::setKeys(array(
		     'google.maps'=>Yii::app()->params['settings']['google_geo_api_key'],
		     'mapbox'=>Yii::app()->params['settings']['mapbox_access_token'],
		   ));
		   		   
		   if ( $country_params = AttributesTools::getSetSpecificCountry()){			   
		   	   MapSdk::setMapParameters(array(
		        'country'=>$country_params
		       ));
		   }		   
		     		  
		   $resp = MapSdk::findPlace($q);		   		   
		   $this->code =1; $this->msg = "ok";
		   $this->details = array(
		     'data'=>$resp
		   );
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();
	}	

	public function actiongetLocationDetails()
	{
		try {
			
			$place_id = isset($this->data['id'])?trim($this->data['id']):'';			
			$country_name = isset($this->data['country'])?trim($this->data['country']):'';			
			$country_code = isset($this->data['country_code'])?trim($this->data['country_code']):'';
						
			$resp = CMaps::locationDetails($place_id, $country_code ,$country_name);
						
			$this->code =1; $this->msg = "ok";
			$this->details = array(
			  'data'=>$resp,					  
			);
							
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionreverseGeocoding()
	{		
		$lat = isset($this->data['lat'])?$this->data['lat']:'';
		$lng = isset($this->data['lng'])?$this->data['lng']:'';
		$next_steps = isset($this->data['next_steps'])?$this->data['next_steps']:'';
					
		try {
			
		   MapSdk::$map_provider = Yii::app()->params['settings']['map_provider'];		   
		   MapSdk::setKeys(array(
		     'google.maps'=>Yii::app()->params['settings']['google_geo_api_key'],
		     'mapbox'=>Yii::app()->params['settings']['mapbox_access_token'],
		   ));
		   
		   if(MapSdk::$map_provider=="mapbox"){
			   MapSdk::setMapParameters(array(			    
			    'limit'=>1
			   ));
		   }
		   
		   $resp = MapSdk::reverseGeocoding($lat,$lng);		   
		   
		   $this->code =1; $this->msg = "ok";
		   $this->details = array(
		     'next_action'=>$next_steps,		     		     
		     'provider'=>MapSdk::$map_provider,
		     'data'=>$resp
		   );		   		   
		   
		} catch (Exception $e) {		   
		   $this->msg = t($e->getMessage());	
		   $this->details = array(
		     'next_action'=>"show_error_msg"		     
		   );	   
		}
		$this->responseJson();
	}	

	public function actiongetAddressdetails()
	{
		try {

			$order_uuid = Yii::app()->input->post("order_uuid");
			$address = []; $address_data = [];
			try {
				$model = COrders::get($order_uuid);				
			    $data = COrders::getAttributesAll($model->order_id,[
					'address_label','delivery_options','formatted_address','address2','address1','longitude','latitude',
					'place_id','delivery_instructions','location_name'
				]);	
				
				if(is_array($data) && count($data)>=1){
					$address = [
						'address'=>[
							'address1'=>isset($data['address1'])?$data['address1']:'',
							'address2'=>isset($data['address2'])?$data['address2']:'',
							'formatted_address'=>isset($data['formatted_address'])?$data['formatted_address']:'',
						],
						'latitude'=>isset($data['latitude'])?$data['latitude']:'',
						'longitude'=>isset($data['longitude'])?$data['longitude']:'',
						'place_id'=>isset($data['place_id'])?$data['place_id']:'',					
					];		
					$address_data = [
						'address_label'=>isset($data['address_label'])?$data['address_label']:'',
						'delivery_options'=>isset($data['delivery_options'])?$data['delivery_options']:'',
						'delivery_instructions'=>isset($data['delivery_instructions'])?$data['delivery_instructions']:'',
						'location_name'=>isset($data['location_name'])?$data['location_name']:'',
					];
				}				
			} catch (Exception $e) {}			

			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(		  				
				'delivery_option'=>CCheckout::deliveryOption(),
				'address_label'=>CCheckout::addressLabel(),				
				'data'=>$address,
				'address_data'=>$address_data
			);
		} catch (Exception $e) {		   
		   $this->msg = t($e->getMessage());		   
		}
		$this->responseJson();
	}

	public function actionsaveaddress()
	{
		try {

			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';
			$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';
			$address_label = isset($this->data['address_label'])?$this->data['address_label']:'';
			$delivery_options = isset($this->data['delivery_options'])?$this->data['delivery_options']:'';
			$address_data = isset($this->data['address_data'])?$this->data['address_data']:false;			
			$address = isset($address_data['address'])?$address_data['address']:false;

			$address2 = isset($address['address2'])?$address['address2']:'';			
			$address1 = isset($this->data['address1'])?$this->data['address1']:'';
			$formatted_address = isset($this->data['formatted_address'])?$this->data['formatted_address']:'';
			$location_name = isset($this->data['location_name'])?$this->data['location_name']:'';
			$delivery_instructions = isset($this->data['delivery_instructions'])?$this->data['delivery_instructions']:'';

			$latitude = isset($address_data['latitude'])?$address_data['latitude']:'';
			$longitude = isset($address_data['longitude'])?$address_data['longitude']:'';
			$place_id = isset($address_data['place_id'])?$address_data['place_id']:'';
			
			if(empty($latitude) || empty($longitude)){
				$this->msg = t("Address is required");
				$this->responseJson();
			}
			
			$merchant_id = Yii::app()->merchant->merchant_id;
			$unit = Yii::app()->params['settings']['home_search_unit_type']; 
			$merchant_info = CMerchants::get($merchant_id);			
			$unit = !empty($merchant_info->distance_unit)?$merchant_info->distance_unit:$unit;
			$distance_covered = $merchant_info->delivery_distance_covered; 
			$merchant_lat = $merchant_info->latitude; 
			$merchant_lng = $merchant_info->lontitude;			
			
			MapSdk::$map_provider = Yii::app()->params['settings']['map_provider'];		   
			MapSdk::setKeys(array(
			'google.maps'=>Yii::app()->params['settings']['google_geo_api_key'],
			'mapbox'=>Yii::app()->params['settings']['mapbox_access_token'],
			));

			$params = array(
				'from_lat'=>$merchant_lat,
				'from_lng'=>$merchant_lng,
				'to_lat'=>$latitude,
				'to_lng'=>$longitude,  
				'unit'=>$unit,
				'mode'=>'driving'
			);			
			MapSdk::setMapParameters($params);		    
			$distance_data =  MapSdk::distance();						
			$distance = isset($distance_data['distance'])?$distance_data['distance']:0;			
			if($distance_covered>0 && $distance>0){
				if($distance>$distance_covered){										
					$this->msg = t("You're out of range. This restaurant cannot deliver to your location.");
					$this->responseJson();
				}
			}
						
			$options_data = OptionsTools::find(['merchant_delivery_charges_type'],$merchant_id);
			$charge_type = isset($options_data['merchant_delivery_charges_type'])?$options_data['merchant_delivery_charges_type']:'';			
			
			$max_min_estimation = CCart::getMaxMinEstimationOrder($merchant_id,$transaction_type,$charge_type,$distance,$unit);				
			$delivery_fee = isset($max_min_estimation['distance_price'])?floatval($max_min_estimation['distance_price']):0;
			
			$order = COrders::get($order_uuid);
			$order->delivery_fee = $delivery_fee;
			$order->formatted_address = $formatted_address;
			
			$address_component = [
				'address_label'=>$address_label,
				'delivery_options'=>$delivery_options,
				'location_name'=>$location_name,
				'formatted_address'=>$formatted_address,
				'delivery_instructions'=>$delivery_instructions,
				'address2'=>$address2,
				'address1'=>$address1,
				'longitude'=>$longitude,
				'latitude'=>$latitude,
				'place_id'=>$place_id,
			];			

			$order->address_component = $address_component;

			if($order->save()){
    			$this->code = 1;
		        $this->msg = t("Successful");
				COrders::updateServiceFee($order_uuid,$order->service_code);
				COrders::updateSummary($order_uuid);
    		} else $this->msg = CommonUtility::parseModelErrorToString($order->getErrors(),"<br/>");
			
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		 }
		 $this->responseJson();
	}

	public function actionemailSubscriber()
	{

		$data = array();
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
					
		$sortby = "id"; $sort = 'DESC';

		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
		
		$page = $page>0? intval($page)/intval($length) :0;
		$criteria=new CDbCriteria();				
		$criteria->alias = "a";						
		$criteria->condition = "merchant_id=:merchant_id AND subcsribe_type=:subcsribe_type";
		$criteria->params =[
			':merchant_id'=>Yii::app()->merchant->merchant_id,
			':subcsribe_type'=>"merchant"
		];
		$criteria->order = "$sortby $sort";
		$count = AR_subscriber::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        	
		
        $models = AR_subscriber::model()->findAll($criteria);
		if($models){
			foreach ($models as $item) {				
				$data[] = [
					'id'=>$item->id,
					'email_address'=>$item->email_address,
					'ip_address'=>$item->ip_address,
					'delete_url'=>Yii::app()->createUrl("/customer/subscriber_delete/",array('id'=>$item->id)),				  
				];
			}
		}
		$datatables = array(
			'draw'=>intval($draw),
			'recordsTotal'=>intval($count),
			'recordsFiltered'=>intval($count),
			'data'=>$data
		);				  
		$this->responseTable($datatables);		
		
	}		

	public function actionSendToKitchen()
	{
		try {
			
			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';
			$data = isset($this->data['items'])?$this->data['items']:'';
			$order_info = isset($this->data['order_info'])?$this->data['order_info']:'';
			$order_table_data = isset($this->data['order_table_data'])?$this->data['order_table_data']:'';

			   
			$kitchen_uuid = '';
			$order_reference = '';
			$whento_deliver = '';
			$merchant_uuid = '';
			$merchant_id = '';
	
			
			if(is_array($data) && count($data)>=1){
				foreach ($data as $items) {

					$order_reference = isset($order_info['order_id'])?$order_info['order_id']:'';
					$whento_deliver = isset($order_info['whento_deliver'])?$order_info['whento_deliver']:'';
					$merchant_uuid = isset($order_info['merchant_uuid'])?$order_info['merchant_uuid']:'';
					$merchant_id = isset($order_info['merchant_id'])?$order_info['merchant_id']:'';

					$kitchen_uuid = isset($order_info['merchant_uuid'])?$order_info['merchant_uuid']:'';;
					$modelKitchen = new AR_kitchen_order();
					$modelKitchen->order_reference = isset($order_info['order_id'])?$order_info['order_id']:'';
					$modelKitchen->merchant_uuid = $kitchen_uuid;
					$modelKitchen->order_ref_id = $items['item_row'];
					$modelKitchen->merchant_id = isset($order_info['merchant_id'])?$order_info['merchant_id']:'';
					$modelKitchen->table_uuid = isset($order_table_data['table_uuid'])?$order_table_data['table_uuid']:'';
					$modelKitchen->room_uuid = isset($order_table_data['room_uuid'])?$order_table_data['room_uuid']:'';
					$modelKitchen->item_token = $items['item_token'];
					$modelKitchen->qty = $items['qty'];
					$modelKitchen->special_instructions = $items['special_instructions'];
					$modelKitchen->customer_name = isset($order_info['customer_name'])?$order_info['customer_name']:'';
					$modelKitchen->transaction_type = isset($order_info['order_type'])?$order_info['order_type']:'';
					$modelKitchen->timezone =  isset($order_info['timezone'])?$order_info['timezone']:'';
					$modelKitchen->whento_deliver = isset($order_info['whento_deliver'])?$order_info['whento_deliver']:'';
					$modelKitchen->delivery_date = isset($order_info['delivery_date'])?$order_info['delivery_date']:'';
					$modelKitchen->delivery_time = isset($order_info['delivery_time'])?$order_info['delivery_time']:'';

					$addons = []; $attributes =[];
					
					if(is_array($items['addons']) && count($items['addons'])>=1){
						foreach ($items['addons'] as $addons_key=> $addons_items) {								
							$addonItems = isset($addons_items['addon_items'])?$addons_items['addon_items']:'';
							if(is_array($addonItems) && count($addonItems)>=1 ){
								foreach ($addonItems as $addons_items_val) {									
									$addons[] = [
										'subcat_id'=>$addons_items['subcat_id'],
										'sub_item_id'=>$addons_items_val['sub_item_id'],
										'qty'=>$addons_items_val['qty'],
										'multi_option'=>$addons_items_val['multiple'],
									];
								}
							}
						}
					}

					$modelKitchen->addons = json_encode($addons);

					if(is_array($items['attributes_raw']) && count($items['attributes_raw'])>=1){
						foreach ($items['attributes_raw'] as $attributes_key=> $attributes_items) {	
							if(is_array($attributes_items) && count($attributes_items)>=1 ){
								foreach ($attributes_items as $meta_id=> $attributesItems) {									
									$attributes[] = [
										'meta_name'=>$attributes_key,
										'meta_id'=>$meta_id
									];
								}
							}
						}
					}
					
					$modelKitchen->attributes = json_encode($attributes);	
					$modelKitchen->sequence = CommonUtility::getNextAutoIncrementID('kitchen_order');
					$modelKitchen->save();
				}
			}

			 // SEND NOTIFICATIONS
			 if(!empty($kitchen_uuid)){
				AR_kitchen_order::SendNotifications([
				   'kitchen_uuid'=>$kitchen_uuid,
				   'order_reference'=>$order_reference,
				   'whento_deliver'=>$whento_deliver,
				   'merchant_uuid'=>$merchant_uuid,
				   'merchant_id'=>$merchant_id
				]);
			 }
			 // SEND NOTIFICATIONS

			$this->code = 1;
			$this->msg = t("Order was sent to kitchen succesfully");
			
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();
	}

	public function actionwifiPrint()
	{
		try {
						
			$printer_id = Yii::app()->input->post('printerId');
			$order_uuid = Yii::app()->input->post('order_uuid');
			$model = AR_printer::model()->find("printer_id=:printer_id",[               
                ':printer_id'=>intval($printer_id)
            ]);
			if($model){
				
				COrders::getContent($order_uuid,Yii::app()->language);					
				$items = COrders::getItems();				
                $summary = COrders::getSummary();
                $order = COrders::orderInfo();
				$order_info = isset($order['order_info'])?$order['order_info']:[];
				$merchant_id = isset($order_info['merchant_id'])?$order_info['merchant_id']:0;
				$merchant_info = CMerchants::getMerchantInfo($merchant_id);								

				$order_type = $order['order_info']['order_type'];
				$order_table_data = [];
				if($order_type=="dinein"){
					$order_table_data = COrders::orderMeta(['table_id','room_id','guest_number']);	
					$room_id = isset($order_table_data['room_id'])?$order_table_data['room_id']:0;							
					$table_id = isset($order_table_data['table_id'])?$order_table_data['table_id']:0;							
					try {
						$table_info = CBooking::getTableByID($table_id);
						$order_table_data['table_name'] = $table_info->table_name;
					} catch (Exception $e) {
						$order_table_data['table_name'] = t("Unavailable");
					}				
					try {
						$room_info = CBooking::getRoomByID($room_id);					
						$order_table_data['room_name'] = $room_info->room_name;
					} catch (Exception $e) {
						$order_table_data['room_name'] = t("Unavailable");
					}				
				}			
				$order_info['order_table_data'] = $order_table_data;

				ThermalPrinterFormatter::setPrinter([
					'ip_address'=>$model->service_id,
					'port'=>$model->characteristics,
					'print_type'=>$model->print_type,
					'character_code'=>$model->character_code,
					'paper_width'=>$model->paper_width,
				]);
				ThermalPrinterFormatter::setItems($items);
				ThermalPrinterFormatter::setSummary($summary);
				ThermalPrinterFormatter::setOrderInfo($order_info);
				ThermalPrinterFormatter::setMerchant($merchant_info);
				$data = ThermalPrinterFormatter::RawReceipt();				
				
				$this->code = 1;
				$this->msg = t("Request succesfully sent to printer");
				$this->details = [
					'data'=>$data
				];

			} else $this->msg = t(HELPER_RECORD_NOT_FOUND);
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();
	}

	public function actionWIFIprint_dailysalesreport()
	{
		try {			
			
			$merchant_id = Yii::app()->merchant->merchant_id;
			$status_completed = AOrderSettings::getStatus(array('status_delivered','status_completed'));
			$date_now = date("Y-m-d");

			$printer_id = Yii::app()->input->post('printerId');
			$date_start = Yii::app()->input->post('date_start');
			$date_end = Yii::app()->input->post('date_end');
			$date_start = !empty($date_start)?$date_start:$date_now;
			$date_end = !empty($date_end)?$date_end:$date_now;

			$model = AR_printer::model()->find("merchant_id=:merchant_id AND printer_id=:printer_id",[
                ":merchant_id"=>$merchant_id,
                ':printer_id'=>intval($printer_id)
            ]);
			if($model){				

				$merchant_info = COrders::getMerchant($merchant_id,Yii::app()->language);
				$items = CReports::dailySalesSummaryPrint($merchant_id,$date_start,$date_end,$status_completed);		
				
				$payment_list = AttributesTools::PaymentProvider();
		        $services = COrders::servicesList(Yii::app()->language);    					

			
				ThermalPrinterFormatter::setPrinter([
					'ip_address'=>$model->service_id,
					'port'=>$model->characteristics,
					'print_type'=>$model->print_type,
					'character_code'=>$model->character_code,
					'paper_width'=>$model->paper_width,
				]);
				
				ThermalPrinterFormatter::setItems($items);
				ThermalPrinterFormatter::setMerchant($merchant_info);
				$data = ThermalPrinterFormatter::RawDailySales($date_start,$date_end,$services,$payment_list);				
								
				$this->code = 1;
                $this->msg = t("Request succesfully sent to printer");      
				$this->details = [
					'data'=>$data
				];          

			} else $this->msg = t(HELPER_RECORD_NOT_FOUND);
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			
		}	
		$this->responseJson();	
	}	

	public function actionmerchantlocation()
	{
		$data = array();		
						
		$ref_id = isset($this->data['ref_id'])?$this->data['ref_id']:null;	
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
						
		$sortby = "date_created"; $sort = 'DESC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = intval($page)/intval($length);		
		$criteria=new CDbCriteria();
		$criteria->condition="merchant_id=:merchant_id";
		$criteria->params = [
			':merchant_id'=>$ref_id
		];	

		if(!empty($search)){			
			$criteria->addSearchCondition('state_name', $search );			
		}		
						
		$criteria->order = "$sortby $sort";
		$count = AR_view_merchant_location::model()->count($criteria); 
		
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        
        $models = AR_view_merchant_location::model()->findAll($criteria);		
        if($models){			
        	foreach ($models as $items) { 			
				$data[] = [
					'id'=>$items->id,
					'country_name'=>$items->country_name,
					'state_name'=>$items->state_name,
					'city_name'=>$items->city_name,
					'area_name'=>$items->area_name,					
					'update_url'=>Yii::app()->createUrl("/merchant/update_location/",array('id'=>$items->id)),
        		    'delete_url'=>Yii::app()->createUrl("/merchant/delete_location/",array('id'=>$items->id)),		
					'rate_id'=>$items->id,		  
				];
        	}        	
        }
        
        $datatables = array(
		  'draw'=>intval($draw),
		  'recordsTotal'=>intval($count),
		  'recordsFiltered'=>intval($count),
		  'data'=>$data
		);				
		$this->responseTable($datatables);
	}		

	public function actionfetchCountry()
	{
		try {

			$location_default_country = Clocations::getDefaultCountry();
						
			$search = Yii::app()->input->post('q');
			$search = $search=='null'?'':$search;	
			$data = Clocations::fetchCountry($search);
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'default_country'=>$location_default_country,
				'data'=>$data,				
			];
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
			$this->detail = [
				'default_country'=>$location_default_country
			];
		}
		$this->responseJson();	
	}

	public function actionfetchstate()
	{
		try {
						
			$search = Yii::app()->input->post('q');
			$search = $search=='null'?'':$search;	
			$country_id = Yii::app()->input->post('country_id');
			$data = Clocations::fetchState($country_id,$search);
			array_unshift($data, [
				'value' => '',
				'label' => t('Please Select'),
			]);			
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'data'=>$data
			];
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actionfetchcity()
	{
		try {
									
			$state_id = Yii::app()->input->post('state_id');
			$data = Clocations::fetchCity($state_id);
			array_unshift($data, [
				'value' => '',
				'label' => t('Please Select'),
			]);			
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'data'=>$data
			];
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actionfetcharea()
	{
		try {
									
			$city_id = Yii::app()->input->post('city_id');
			$data = Clocations::fetchArea($city_id);
			array_unshift($data, [
				'value' => '',
				'label' => t('Please Select'),
			]);			
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'data'=>$data
			];
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}	

	public function actionsavemerchantlocation()
	{
		try {
						
			$merchant_id = Yii::app()->merchant->merchant_id;
			$id = intval(Yii::app()->input->post("rate_id"));						
			$country_id = intval(Yii::app()->input->post("country_id"));
			$state_id = intval(Yii::app()->input->post("state_id"));
			$city_id = intval(Yii::app()->input->post("city_id"));
			$area_id = intval(Yii::app()->input->post("area_id"));
			
			$model = new AR_merchant_location();
			if($id>0){
				$model = AR_merchant_location::model()->find("id=:id",[
					":id"=>$id
				]);				
				if(!$model){
					$this->msg = t(HELPER_RECORD_NOT_FOUND);
					$this->responseJson();
				}
				$model->id = $id;
			}

			$model->merchant_id = $merchant_id;
			$model->country_id = $country_id;
			$model->state_id = $state_id;
			$model->city_id = $city_id;
			$model->area_id = $area_id;

			if($model->save()){
				$this->code = 1;
				$this->msg = t(Helper_success);
			} else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
			
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actiongetmanagelocation()
	{
		try {
			$id = Yii::app()->input->post('rate_id');
			$model = AR_merchant_location::model()->find("merchant_id=:merchant_id AND id=:id",[
				':merchant_id'=>Yii::app()->merchant->merchant_id,
				':id'=>intval($id)
			]);
			if($model){
				$this->code = 1;
				$this->msg = "Ok";
				$this->details = [
					'rate_id'=>$model->id,
					'country_id'=>$model->country_id,
					'state_id'=>$model->state_id,
					'city_id'=>$model->city_id,
					'area_id'=>$model->area_id,				
				];
			} else $this->msg = t(HELPER_RECORD_NOT_FOUND);
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actionlocationRateList()
	{
		$data = array();		
						
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
						
		$sortby = "date_created"; $sort = 'DESC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = intval($page)/intval($length);		
		$criteria=new CDbCriteria();
		$criteria->condition="merchant_id=:merchant_id";
		$criteria->params = [
			':merchant_id'=>Yii::app()->merchant->merchant_id
		];	

		if(!empty($search)){			
			$criteria->addSearchCondition('state_name', $search );			
		}		
						
		$criteria->order = "$sortby $sort";
		$count = AR_view_location_rates::model()->count($criteria); 
		
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        
        $models = AR_view_location_rates::model()->findAll($criteria);		
        if($models){			
        	foreach ($models as $items) { 			
				$data[] = [
					'rate_id'=>$items->rate_id,
					'country_name'=>$items->country_name,
					'state_name'=>$items->state_name,
					'city_name'=>$items->city_name,
					'area_name'=>$items->area_name,
					'delivery_fee'=>Price_Formatter::formatNumber($items->delivery_fee),
					'update_url'=>Yii::app()->createUrl("/merchant/update_location_rate/",array('id'=>$items->rate_id)),
        		    'delete_url'=>Yii::app()->createUrl("/merchant/delete_location_rate/",array('id'=>$items->rate_id)),				  
				];
        	}        	
        }
        
        $datatables = array(
		  'draw'=>intval($draw),
		  'recordsTotal'=>intval($count),
		  'recordsFiltered'=>intval($count),
		  'data'=>$data
		);				
		$this->responseTable($datatables);
	}

	public function actionsaveLocationRates()
	{
		try {
			
			$rate_id = intval(Yii::app()->input->post("rate_id"));
			$fee = floatval(Yii::app()->input->post("fee"));
			$country_id = intval(Yii::app()->input->post("country_id"));
			$state_id = intval(Yii::app()->input->post("state_id"));
			$city_id = intval(Yii::app()->input->post("city_id"));
			$area_id = intval(Yii::app()->input->post("area_id"));
			$minimum_order = floatval(Yii::app()->input->post("minimum_order"));
			$maximum_amount = floatval(Yii::app()->input->post("maximum_amount"));
			$free_above_subtotal = floatval(Yii::app()->input->post("free_above_subtotal"));
			
			$model = new AR_location_rate();
			if($rate_id>0){
				$model = AR_location_rate::model()->find("rate_id=:rate_id",[
					":rate_id"=>$rate_id
				]);				
				if(!$model){
					$this->msg = t(HELPER_RECORD_NOT_FOUND);
					$this->responseJson();
				}
				$model->rate_id = $rate_id;
			}

			$model->merchant_id = Yii::app()->merchant->merchant_id;
			$model->country_id = $country_id;
			$model->state_id = $state_id;
			$model->city_id = $city_id;
			$model->area_id = $area_id;
			$model->fee = $fee;
			$model->minimum_order = $minimum_order;
			$model->maximum_amount = $maximum_amount;
			$model->free_above_subtotal = $free_above_subtotal;			
			if($model->save()){
				$this->code = 1;
				$this->msg = t(Helper_success);
			} else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actiongetrate()
	{
		try {
			
			$rate_id = Yii::app()->input->post('rate_id');
			$model = AR_location_rate::model()->find("merchant_id=:merchant_id AND rate_id=:rate_id",[
				':merchant_id'=>Yii::app()->merchant->merchant_id,
				':rate_id'=>intval($rate_id)
			]);
			if($model){
				$this->code = 1;
				$this->msg = "Ok";
				$this->details = [
					'rate_id'=>$model->rate_id,
					'country_id'=>$model->country_id,
					'state_id'=>$model->state_id,
					'city_id'=>$model->city_id,
					'area_id'=>$model->area_id,
					'fee'=>$model->fee,
					'minimum_order'=>$model->minimum_order,
					'maximum_amount'=>$model->maximum_amount,
					'free_above_subtotal'=>$model->free_above_subtotal,
				];
			} else $this->msg = t(HELPER_RECORD_NOT_FOUND);
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}	

	public function actionlocationTimeManagementList()
	{
		$data = array();		
								
		//$ref_id = isset($this->data['ref_id'])?$this->data['ref_id']:null;	
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
						
		$sortby = "date_created"; $sort = 'DESC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = intval($page)/intval($length);		
		$criteria=new CDbCriteria();
		$criteria->condition="merchant_id=:merchant_id";
		$criteria->params = [
			':merchant_id'=>Yii::app()->merchant->merchant_id
		];	

		if(!empty($search)){			
			$criteria->addSearchCondition('state_name', $search );			
		}		
						
		$criteria->order = "$sortby $sort";
		$count = AR_view_location_time_estimate::model()->count($criteria); 
		
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        
        $models = AR_view_location_time_estimate::model()->findAll($criteria);		
        if($models){			
        	foreach ($models as $items) { 			
				$data[] = [
					'id'=>$items->id,
					'service_type'=>t($items->service_type),
					'country_name'=>$items->country_name,
					'state_name'=>$items->state_name,
					'city_name'=>$items->city_name,
					'area_name'=>$items->area_name,					
					'estimated_time_min'=>t("{estimation} mins",[
						'{estimation}'=>$items->estimated_time_min."-".$items->estimated_time_max
					]),					
					'update_url'=>Yii::app()->createUrl("/vendor/update_location/",array('id'=>$items->id)),
        		    'delete_url'=>Yii::app()->createUrl("/merchant/delete_estimate_time/",array('id'=>$items->id)),		
					'rate_id'=>$items->id,		  
				];
        	}        	
        }
        
        $datatables = array(
		  'draw'=>intval($draw),
		  'recordsTotal'=>intval($count),
		  'recordsFiltered'=>intval($count),
		  'data'=>$data
		);				
		$this->responseTable($datatables);
	}

	public function actionsavelocationestimate()
	{
		try {

			$merchant_id = Yii::app()->merchant->merchant_id;
			$id = intval(Yii::app()->input->post("rate_id"));			
			$country_id = intval(Yii::app()->input->post('country_id'));
			$state_id = intval(Yii::app()->input->post('state_id'));
			$city_id = intval(Yii::app()->input->post('city_id'));
			$area_id = intval(Yii::app()->input->post('area_id'));
			$estimated_time_min = intval(Yii::app()->input->post('estimated_time_min'));
			$estimated_time_max = intval(Yii::app()->input->post('estimated_time_max'));
			$service_type = Yii::app()->input->post('service_type');

			$model = new AR_location_time_estimate();
			if($id>0){
				$model = AR_location_time_estimate::model()->find("id=:id",[
					":id"=>$id
				]);				
				if(!$model){
					$this->msg = t(HELPER_RECORD_NOT_FOUND);
					$this->responseJson();
				}
				$model->id = $id;
			}

			$model->merchant_id = $merchant_id;
			$model->country_id = $country_id;
			$model->state_id = $state_id;
			$model->city_id = $city_id;
			$model->area_id = $area_id;
			$model->estimated_time_min = $estimated_time_min;
			$model->estimated_time_max = $estimated_time_max;
			$model->service_type = $service_type;

			if($model->save()){
				$this->code = 1;
				$this->msg = t(Helper_success);
			} else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());

		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actiongetestimate()
	{
		try {
			$id = Yii::app()->input->post('rate_id');
			$model = AR_location_time_estimate::model()->find("id=:id AND merchant_id=:merchant_id",[
				':id'=>intval($id),
				':merchant_id'=>Yii::app()->merchant->merchant_id,
			]);
			if($model){
				$this->code = 1;
				$this->msg = "Ok";
				$this->details = [
					'rate_id'=>$model->id,
					'service_type'=>$model->service_type,
					'country_id'=>$model->country_id,
					'state_id'=>$model->state_id,
					'city_id'=>$model->city_id,
					'area_id'=>$model->area_id,				
					'estimated_time_min'=>$model->estimated_time_min,	
					'estimated_time_max'=>$model->estimated_time_max,	
				];
			} else $this->msg = t(HELPER_RECORD_NOT_FOUND);
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actionupdatepreparationtime()
	{
		try {
			
			$order_uuid = Yii::app()->input->post("order_uuid");
			$estimate = intval(Yii::app()->input->post("estimate"));

			$model = COrders::get($order_uuid);
			$model->preparation_time_estimation = $estimate;
			if($model->order_accepted_at){
                $model->order_accepted_at = CommonUtility::dateNowAdd();
            }            
			$model->scenario = "update_preparation_time";
			if($model->save()){
				$this->code = 1;
				$this->msg = t("Preparation time updated");
				$this->details = CommonUtility::convertMinutesToReadableTime($estimate);
			} else $this->msg = CommonUtility::parseModelErrorToString( $model->getErrors());
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actionSaveHoliday()
	{
		try {

			$merchant_id = Yii::app()->merchant->merchant_id;
			$holiday_name =  Yii::app()->request->getPost('holiday_name', ''); 
			$holiday_date =  Yii::app()->request->getPost('holiday_date', ''); 
			$reason =  Yii::app()->request->getPost('reason', ''); 
			
			$model = new AR_holidays();
			$model->merchant_id = $merchant_id;
			$model->holiday_name = $holiday_name;
			$model->holiday_date = $holiday_date;
			$model->reason = $reason;
			if($model->save()){
				$this->code = 1;
				$this->msg = t("Event added");
			} else $this->msg = CommonUtility::parseModelErrorToString( $model->getErrors());
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actionfetchholidays()
	{
		try {

			$merchant_id = Yii::app()->merchant->merchant_id;
			$data = CPromos::fetchHolidays($merchant_id);
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = $data;
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actiondeleteevent()
	{
		try {

			$merchant_id = Yii::app()->merchant->merchant_id;
			$id =  Yii::app()->request->getQuery('id', '');  
			$model = AR_holidays::model()->find("id=:id",[
				":id"=>intval($id)
			]);
			if($model){
				$model->delete();
				$this->code = 1;
				$this->msg = "Ok";
				try {
					$data = CPromos::fetchHolidays($merchant_id);			
					$this->details = $data;
			    } catch (Exception $e) {
					$this->details = null;
				}		   
			} else $this->msg = CommonUtility::parseModelErrorToString( $model->getErrors());
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actiongetlocationaddress()
	{
		try {
						
			$address_id =  Yii::app()->request->getQuery('address_id', '');  
			$model = AR_client_address_location::model()->find("address_id=:address_id",[
				':address_id'=>$address_id
			]);
			if($model){							
				$data = [
					'address_uuid'=>$model->address_uuid,
					'formatted_address'=>$model->formatted_address,
					'address1'=>$model->address1,
					'location_name'=>$model->location_name,
					'state_id'=>$model->postal_code,
					'city_id'=>$model->address2,
					'area_id'=>$model->custom_field1,
					'house_number'=>$model->custom_field2,
					'country_id'=>$model->country_code,
					'delivery_options'=>$model->delivery_options,
					'delivery_instructions'=>$model->delivery_instructions,
					'address_label'=>$model->address_label,
					'latitude'=>$model->latitude,
					'longitude'=>$model->longitude,
					'zip_code'=>$model->company,					
				];
				$this->code = 1;
				$this->msg = "Ok";
				$this->details = [
					'data'=>$data
				];
			} else $this->msg = t(HELPER_RECORD_NOT_FOUND);

		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actionsaveaddresslocation()
	{
		try {
									
			$address_id =  Yii::app()->request->getPost('address_id', ''); 
			$address_id = $address_id=="null"?'':$address_id;
			$formatted_address =  Yii::app()->request->getPost('formatted_address', '');    
			$address1 =  Yii::app()->request->getPost('address1', '');    
			$location_name =  Yii::app()->request->getPost('location_name', '');    
			$delivery_options =  Yii::app()->request->getPost('delivery_options', '');    
			$delivery_instructions = Yii::app()->request->getPost('delivery_instructions', '');
			$address_label = Yii::app()->request->getPost('address_label', '');
			$house_number = Yii::app()->request->getPost('house_number', '');

			$state_id = intval(Yii::app()->request->getPost('state_id', ''));
			$city_id = intval(Yii::app()->request->getPost('city_id', ''));
			$area_id = intval(Yii::app()->request->getPost('area_id', ''));
			$zip_code = Yii::app()->request->getPost('zip_code', '');

			$latitude = Yii::app()->request->getPost('latitude', '');
			$longitude = Yii::app()->request->getPost('longitude', '');			
			$country_id = Yii::app()->request->getPost('country_id', '');
			$client_id = Yii::app()->request->getPost('client_id', '');

			$model_client = AR_client::model()->findByPk($client_id);
			if(!$model_client){
				$this->msg = t("Customer record not found");
				$this->responseJson();
			}
			$client_uuid = $model_client->client_uuid;			

			$model = new AR_client_address_location();			
			if($address_id>0){
				$model = AR_client_address_location::model()->find("address_id=:address_id",[
					':address_id'=>$address_id
				]);							
			}
					
			$model->address_type = 'location-based';
			$model->client_id = $client_id;
			$model->formatted_address = $formatted_address;
			$model->address1 = $address1;
			$model->location_name = $location_name;
			$model->postal_code = $state_id;
			$model->address2 = $city_id;
			$model->custom_field1 = $area_id;
			$model->country_code = $country_id;
			$model->company = $zip_code;
			$model->delivery_options = $delivery_options;
			$model->delivery_instructions = $delivery_instructions;
			$model->address_label = $address_label;			
			$model->latitude = $latitude;		
			$model->longitude = $longitude;	
			$model->custom_field2 = $house_number;			
			
			if($model->validate()){
				if($model->save()){
					$this->code = 1;
					$this->msg = t(Helper_success);
					$this->details = [
						'redirect'=>Yii::app()->createAbsoluteUrl("/customer/addresses",[
							'id'=>$client_uuid
						])
					];
				} else {				
					$this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
				}
			} else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());						
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}	

	public function actiongetItemList()
	{
		
		$merchant_id = Yii::app()->merchant->merchant_id;
		$pageSize = Yii::app()->request->getQuery('page_size', 10);  
        $page = Yii::app()->request->getQuery('page', 1);  
		$query = Yii::app()->request->getQuery('query', null);
		$filter_pause = Yii::app()->request->getQuery('filter_pause', null);
		$filter_pause = $filter_pause=="true"?true:false;

		$sortby = "item_id"; $sort = 'DESC';
				
		$criteria=new CDbCriteria;
		$criteria->alias="a";
		$criteria->select = "a.item_id,a.merchant_id,
		a.item_token,
		IF(COALESCE(NULLIF(b.item_name, ''), '') = '', a.item_name, b.item_name) as item_name,
		(SELECT price FROM {{item_relationship_size}} WHERE item_id = a.item_id LIMIT 1) as item_price,
		a.unavailable_until,a.available,a.status
		";
		$criteria->join = "
		left JOIN (
			SELECT item_id,item_name  FROM {{item_translation}} where language=".q(Yii::app()->language)."
		) b 
		ON a.item_id = b.item_id

		
		";
		$criteria->condition = "a.merchant_id=:merchant_id AND a.visible=:visible";
		$criteria->params = [
			':merchant_id'=>$merchant_id,
			':visible'=>1,
		];
		
		if($query){
			$criteria->addSearchCondition('a.item_name', $query );
		} else if ($filter_pause){
			$criteria->addCondition("unavailable_until IS NOT NULL OR status='draft' OR available=0");
		}

		$criteria->order = "$sortby $sort";
		$dataProvider = new CActiveDataProvider('AR_item', [
            'criteria'=>$criteria,
            'pagination' => [
                'pageSize' => $pageSize,
                'currentPage' => $page - 1,  // Page starts from 0 in CActiveDataProvider
            ],
        ]);
		
		$data = [];
		$provider_data = $dataProvider->getData();
		if($provider_data){
			foreach ($provider_data as $items) {
				$status = 'pause';
				if($items->unavailable_until){
					$status = 'resume';
				} else if (!$items->unavailable_until && $items->available==0 ){
					$status = 'unavailable';
				} else if (!$items->unavailable_until && $items->available==0 && $items->status=="draft" ){
					$status = 'indefinitely';
				}

				$whenAvailable = '';

				if($status=="resume"){
					$whenAvailable = t("Resume on {date}",[
						'{date}'=>Date_Formatter::dateTime($items->unavailable_until)
					]) ;
				} else if ( $status=="unavailable"){
					$status = 'resume';		
					$whenAvailable  = t("Unavailable");	
				} else if ($status=="indefinitely"){
					$whenAvailable = t("Item is unavailable indefinitely.");
				}

				$data[] = [
					'item_id'=>$items->item_id,
					'item_token'=>$items->item_token,
					'merchant_id'=>$items->merchant_id,
					'item_name'=>CommonUtility::safeDecode($items->item_name),
					'item_price'=>Price_Formatter::formatNumber($items->item_price),
					'unavailable_until'=>$whenAvailable,
					'status'=>$status
				];
			}
		}

        $totalCount = $dataProvider->totalItemCount;
		$data = [
			'total_display_items'=>Yii::t('backend', '{n} item|{n} items', $totalCount),
            'total_items' => (integer)$totalCount,
            'page_size' => (integer)$pageSize,
            'current_page' => (integer)$page,
            'total_pages' => (integer)ceil($totalCount / $pageSize),
            'items' => $data,
        ];
		$this->code;
		$this->code = 1;
		$this->msg = "Ok";
		$this->details = $data;
		$this->responseJson();	
	}

	public function actionPauseItem()
	{
		try {

			$merchant_id = Yii::app()->merchant->merchant_id;

			if(DEMO_MODE && in_array($this->merchant_id,DEMO_MERCHANT)){		
				$this->msg = t("Modification not available in demo");
				$this->responseJson();
			}

			$item_token =  Yii::app()->request->getPost('item_token', '');
			$unavailable_until =  Yii::app()->request->getPost('unavailable_until', '');
			$days =  Yii::app()->request->getPost('days', '');
			$hours =  Yii::app()->request->getPost('hours', '');			


			$model = AR_item_availability::model()->find("item_token=:item_token AND merchant_id=:merchant_id",[
				':item_token'=>$item_token,
				':merchant_id'=>$merchant_id
			]);
			if(!$model){
				$this->msg = t(HELPER_RECORD_NOT_FOUND);
				$this->responseJson();
			}
						
			if($unavailable_until=="indefinitely"){
				$model->available = 0;
				$model->unavailable_until = null;
				$model->status = 'draft';
			} else {
				$date_unavailable = AttributesTools::getAddedTime($unavailable_until,$days,$hours);
				$model->available = 0;
				$model->unavailable_until = $date_unavailable;
				$model->status = 'publish';
			}		
	
			$status = 'pause';
			if($model->unavailable_until){
				$status = 'resume';
			} else if (!$model->unavailable_until && $model->available==0 ){
				$status = 'unavailable';
			} else if (!$model->unavailable_until && $model->available==0 && $model->status=="draft" ){
				$status = 'indefinitely';
			}

			$whenAvailable = '';
			if($status=="resume"){
				$whenAvailable = t("Resume on {date}",[
					'{date}'=>Date_Formatter::dateTime($model->unavailable_until)
				]) ;
			} else if ( $status=="unavailable"){
				$status = 'resume';		
				$whenAvailable  = t("Unavailable");	
			} else if ($status=="indefinitely"){
				$whenAvailable = t("Item is unavailable indefinitely.");
			}

			$item_price = [];
			if($items = CMerchantMenu::getItemPrice($model->item_id)){
				$item_price = reset($items);								    
			}								
			
			$details[] = [
				'item_id'=>$model->item_id,
				'item_token'=>$model->item_token,
				'merchant_id'=>$model->merchant_id,
				'item_name'=>$model->item_name,
				'item_price'=>isset($item_price['pretty_price'])?$item_price['pretty_price']:'',
				'unavailable_until'=>$whenAvailable,
				'status'=>$status,
			];

			if($model->save()){
				$this->code = 1;
				$this->msg = "Ok";			
				$this->details = $details;
			} else $this->msg = CommonUtility::parseModelErrorToString( $model->getErrors());			
						
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}
	
	public function actionresumeitem()
	{
		try {

			$merchant_id = Yii::app()->merchant->merchant_id;
			$item_token =  Yii::app()->request->getPost('item_token', '');
			$model = AR_item_availability::model()->find("item_token=:item_token AND merchant_id=:merchant_id",[
				':item_token'=>$item_token,
				':merchant_id'=>$merchant_id
			]);
			if($model){
				$model->available = 1;
				$model->unavailable_until = null;
				$model->status = 'publish';
				$model->save();

				$status = 'pause';
				if($model->unavailable_until){
					$status = 'resume';
				} else if (!$model->unavailable_until && $model->available==0 ){
					$status = 'unavailable';
				} else if (!$model->unavailable_until && $model->available==0 && $model->status=="draft" ){
					$status = 'indefinitely';
				}

				$whenAvailable = '';

				if($status=="resume"){
					$whenAvailable = t("Resume on {date}",[
						'{date}'=>Date_Formatter::dateTime($model->unavailable_until)
					]) ;
				} else if ( $status=="unavailable"){
					$status = 'resume';		
					$whenAvailable  = t("Unavailable");	
				} else if ($status=="indefinitely"){
					$whenAvailable = t("Item is unavailable indefinitely.");
				}

				$item_price = [];
				if($items = CMerchantMenu::getItemPrice($model->item_id)){
					$item_price = reset($items);								    
				}								
				
				$details[] = [
					'item_id'=>$model->item_id,
					'item_token'=>$model->item_token,
					'merchant_id'=>$model->merchant_id,
					'item_name'=>$model->item_name,
					'item_price'=>isset($item_price['pretty_price'])?$item_price['pretty_price']:'',
					'unavailable_until'=>$whenAvailable,
					'status'=>$status,
				];

				$this->code = 1;
				$this->msg = t("Successful");
				$this->details = $details;
			} else $this->msg = t(HELPER_RECORD_NOT_FOUND);
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actionItemsList()
	{
		$merchant_id = Yii::app()->merchant->merchant_id;
		$pageSize = Yii::app()->request->getQuery('page_size', 10);  
        $page = Yii::app()->request->getQuery('page', 1);  
		$query = Yii::app()->request->getQuery('query', null);		

		$sortby = "item_id"; $sort = 'DESC';

		$criteria=new CDbCriteria;
		$criteria->alias="a";
		$criteria->select = "a.item_id,a.merchant_id,
		a.item_token,
		IF(COALESCE(NULLIF(b.item_name, ''), '') = '', a.item_name, b.item_name) as item_name,
		(SELECT price FROM {{item_relationship_size}} WHERE item_id = a.item_id LIMIT 1) as item_price,
		a.unavailable_until,a.available,a.status
		";
		$criteria->join = "
		left JOIN (
			SELECT item_id,item_name  FROM {{item_translation}} where language=".q(Yii::app()->language)."
		) b 
		ON a.item_id = b.item_id

		
		";
		$criteria->condition = "a.status=:status AND a.merchant_id=:merchant_id AND a.visible=:visible";
		$criteria->params = [
			':status'=>'publish',
			':merchant_id'=>$merchant_id,
			':visible'=>1,
			//':available'=>1
		];
		
		if($query){
			$criteria->addSearchCondition('a.item_name', $query );
		} 

		$criteria->order = "$sortby $sort";
		$dataProvider = new CActiveDataProvider('AR_item', [
            'criteria'=>$criteria,
            'pagination' => [
                'pageSize' => $pageSize,
                'currentPage' => $page - 1,  // Page starts from 0 in CActiveDataProvider
            ],
        ]);

		$data = [];
		if($provider_data = $dataProvider->getData()){
			foreach ($provider_data as $items) {
				$data[] = [
					'item_id'=>$items->item_id,
					'item_token'=>$items->item_token,
					'merchant_id'=>$items->merchant_id,
					'item_name'=>CommonUtility::safeDecode($items->item_name),
					'item_price'=>Price_Formatter::formatNumber($items->item_price),					
				];
			}
		}

		$totalCount = $dataProvider->totalItemCount;
		$data = [
			'total_display_items'=>Yii::t('backend', '{n} item|{n} items', $totalCount),
            'total_items' => (integer)$totalCount,
            'page_size' => (integer)$pageSize,
            'current_page' => (integer)$page,
            'total_pages' => (integer)ceil($totalCount / $pageSize),
            'items' => $data,
        ];
		$this->code;
		$this->code = 1;
		$this->msg = "Ok";
		$this->details = $data;
		$this->responseJson();	
	}

	public function actionSaveSuggestedItems()
	{
		try {
			
			$merchant_id = Yii::app()->merchant->merchant_id;
								
			if(DEMO_MODE && in_array($this->merchant_id,DEMO_MERCHANT)){		
				$this->msg = t("This is disabled in this demo");
				$this->responseJson();	
				Yii::app()->end();
			}


			$this->data = Yii::app()->input->xssClean(json_decode(file_get_contents('php://input'), true));	
			$items = isset($this->data['items'])?$this->data['items']:null;
			if(!$items){
				$this->msg = t("Please select items");
				$this->responseJson();
			}

			Citems::SuggestedItemsValidate($merchant_id,count($items));

			$params = null;
			foreach ($items as $item_id) {
				if(!Citems::findSuggestemItems($merchant_id,$item_id)){
					$params[] = [
						'merchant_id'=>$merchant_id,
						'item_id'=>$item_id,
						'created_at'=>CommonUtility::dateNow()
					];				
				}				
			}

			if (!is_array($params) || count($params) <= 0) {
				$this->msg = t("No items to insert or your selected items already added.");
				$this->responseJson();
			}

			$builder=Yii::app()->db->schema->commandBuilder;		   
		    $command=$builder->createMultipleInsertCommand('{{suggested_items}}', $params );
		    $command->execute();

			try {        
				$merchant = CMerchants::get($merchant_id);
				$restaurant_name = $merchant->restaurant_name;						
				$noti = new AR_notifications;                
				$noti->notication_channel = Yii::app()->params->realtime['admin_channel'];
				$noti->notification_event = Yii::app()->params->realtime['notification_event'] ;
				$noti->notification_type = 'suggested_items';
				$noti->message = "You have new suggested feature items submitted by {restaurant_name}";
				$noti->message_parameters = json_encode([
					'{restaurant_name}'=>$restaurant_name
				]);
				$noti->image_type = 'icon';
				$noti->image = 'zmdi zmdi-info-outline';
				$noti->meta_data = [
					'page'=>'suggested_items',
					'page_url'=>Yii::app()->createAbsoluteUrl("/marketing/suggested_items")
				];         			
               $noti->save();                           
		    } catch (Exception $e) {}

			$this->code = 1;
			$this->msg = t(Helper_success);
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actionSuggestedItems()
	{
		$merchant_id = Yii::app()->merchant->merchant_id;
		$pageSize = Yii::app()->request->getQuery('page_size', 10);  
        $page = Yii::app()->request->getQuery('page', 1);  
		$query = Yii::app()->request->getQuery('query', null);		

		$sortby = "id"; $sort = 'DESC';

		$criteria=new CDbCriteria;
		$criteria->alias="a";
		$criteria->select = "a.id,a.item_id,a.merchant_id,a.status,a.reason,a.created_at,
		IF(COALESCE(NULLIF(b.item_name, ''), '') = '', item.item_name, b.item_name) as item_name,
		(SELECT price FROM {{item_relationship_size}} WHERE item_id = a.item_id LIMIT 1) as item_price		
		";
		$criteria->join = "
		LEFT JOIN {{item}} item
		ON
		a.item_id = item.item_id

		left JOIN (
			SELECT item_id,item_name  FROM {{item_translation}} where language=".q(Yii::app()->language)."
		) b 
		ON a.item_id = b.item_id		
		";
		$criteria->condition = "a.merchant_id=:merchant_id";
		$criteria->params = [
			':merchant_id'=>$merchant_id,			
		];
		
		if($query){
			$criteria->addSearchCondition('a.item_name', $query );
		} 

		$criteria->order = "$sortby $sort";
		$dataProvider = new CActiveDataProvider('AR_suggested_items', [
            'criteria'=>$criteria,
            'pagination' => [
                'pageSize' => $pageSize,
                'currentPage' => $page - 1,  // Page starts from 0 in CActiveDataProvider
            ],
        ]);

		$data = [];
		if($provider_data = $dataProvider->getData()){
			foreach ($provider_data as $items) {				
				$data[] = [
				    'id'=>$items->id,
					'item_id'=>$items->item_id,
					'item_name'=>CommonUtility::safeDecode($items->item_name),
					'item_price'=>Price_Formatter::formatNumber($items->item_price),
					'status'=>t($items->status),
					'status_raw'=>$items->status,
					'reason'=>$items->reason?CommonUtility::safeDecode($items->reason):'',
					'created_at'=>Date_Formatter::dateTime($items->created_at)
				];
			}
		}

		$totalCount = $dataProvider->totalItemCount;
		$data = [
			'total_display_items'=>Yii::t('backend', '{n} item|{n} items', $totalCount),
            'total_items' => (integer)$totalCount,
            'page_size' => (integer)$pageSize,
            'current_page' => (integer)$page,
            'total_pages' => (integer)ceil($totalCount / $pageSize),
            'items' => $data,
        ];
		$this->code;
		$this->code = 1;
		$this->msg = "Ok";
		$this->details = $data;
		$this->responseJson();	
	}

	public function actiondeletesuggested()
	{
		try {			
			$merchant_id = Yii::app()->merchant->merchant_id;
			$id =  Yii::app()->request->getQuery('id', '');
			$model = AR_suggested_items::model()->find("id=:id AND merchant_id=:merchant_id",[
				':id'=>$id,
				':merchant_id'=>$merchant_id,
			]);
			if($model){
				$model->delete();
				$this->code = 1;
				$this->msg = t("Successfully Deleted");
			} else $this->msg = t(HELPER_RECORD_NOT_FOUND);
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actiondeletesuggestedrows()
	{
		try {

			$merchant_id = Yii::app()->merchant->merchant_id;
			$this->data = Yii::app()->input->xssClean(json_decode(file_get_contents('php://input'), true));	
			$items = isset($this->data['items'])?$this->data['items']:null;
			
			if (!is_array($items) || count($items) <= 0) {
				$this->msg = t("No items selected to delete");
				$this->responseJson();
			}

			foreach ($items as $item) {
				$row_id = $item['id'];
				$model = AR_suggested_items::model()->find("id=:id AND merchant_id=:merchant_id",[
					':id'=>intval($row_id),
					':merchant_id'=>intval($merchant_id)
				]);
				if($model){
					$model->delete();
				}				
			}
			$this->code = 1;
			$this->msg = t(Helper_success);
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actionsaveTaxrate()
	{
		try {
			
			$tax_uuid =  Yii::app()->request->getPost('tax_uuid', null);
			$tax_name =  Yii::app()->request->getPost('tax_name', null);
			$tax_rate =  Yii::app()->request->getPost('tax_rate', 0);
			$active =  Yii::app()->request->getPost('active', 1);
			$tax_type = 'multiple';

			$data = AR_merchant_meta::getMeta(Yii::app()->merchant->merchant_id,array('price_included_tax'));
			$price_included_tax = $data['price_included_tax'] ?? 0;
			$price_included_tax = $price_included_tax['meta_value'] ?? 0;			
			
			$model = AR_tax::model()->find("tax_uuid=:tax_uuid",[
				':tax_uuid'=>$tax_uuid
			]);
			if(!$model){
				$model = new AR_tax();
			}			
			$model->merchant_id = intval(Yii::app()->merchant->merchant_id);
			$model->tax_type = $tax_type;
			$model->tax_name = $tax_name;
			$model->tax_rate = floatval($tax_rate);
			$model->tax_in_price = intval($price_included_tax);
			$model->active = intval($active);
			if($model->save()){
				$this->code = 1;
				$this->msg = t("Successful");
			} else $this->msg = CommonUtility::parseError( $model->getErrors());
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actionfecthTaxList()
	{
		try {

			$tax_type = 'multiple';			
			$criteria=new CDbCriteria();	
			$criteria->addCondition("merchant_id=:merchant_id AND tax_type=:tax_type");
			$criteria->params = [
				':merchant_id'=>intval(Yii::app()->merchant->merchant_id),
				':tax_type'=>$tax_type
			];			
			$criteria->order = 'tax_id DESC';
			$model = AR_tax::model()->findAll($criteria);
			if($model){
				$data = [];
				foreach ($model as $items) {
					$data[] = [
						'tax_name'=>$items->tax_name,
						'tax_rate'=>$items->tax_rate,
						'active'=>$items->active,
						'tax_uuid'=>$items->tax_uuid,
					];
				}
				$this->code = 1;
				$this->msg = "Ok";
				$this->details = [
					'data'=>$data
				];
			} else $this->msg = t(HELPER_NO_RESULTS);

		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actionfetchOpeningHours()
	{
		try {
						
			$time_config_type =  Yii::app()->request->getQuery('time_config_type', null);  
			$transaction_type =  Yii::app()->request->getQuery('transaction_type', null);  			
			
			$merchant_id = Yii::app()->merchant->merchant_id;			
			$days = AttributesTools::dayList();
			$data = CMerchants::getOpeningHours($merchant_id,true,true,$time_config_type,$transaction_type);			
			$new_data = [];
			foreach ($days as $day_code => $dayname){
				if(!array_key_exists($day_code,(array)$data)){					
					$new_data[$day_code][] = [
						'id'=>0,
						'status'=>"open",
						'close'=>false,
						'start_time'=>null,
						'end_time'=>null,
						'custom_text'=>""
					];
				} else $new_data[$day_code] = $data[$day_code] ?? '';	
			}
			
			$this->code = 1;
			$this->msg = "Ok";
			$time_range = AttributesTools::createTimeRange("00:00","23:59","15 mins","24","H:i");			
			$this->details = [
				'days'=>$days,
				'data'=>$new_data,
				'time_range'=>CommonUtility::ArrayToLabelValue($time_range)
			];
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actionupdateregularhours()
	{
		try {
			
			$error = []; $error_count = 0; $saved_ids = [];
			$raw_data = Yii::app()->input->xssClean(json_decode(file_get_contents('php://input'), true));			
			$merchant_id = intval(Yii::app()->merchant->merchant_id);												
			$data = $raw_data['data'] ?? null;
			$time_config_type = $raw_data['time_config_type'] ?? null;
			$transaction_type = $raw_data['transaction_type'] ?? null;			
			
			if(!$data){
				$this->msg = t("Invalid data");
				$this->responseJson();
			}
			if(!$time_config_type){
				$this->msg = t("Invalid Time Configuration Type");
				$this->responseJson();
			}
			
			if(is_array($data) && count($data)>=1){
				foreach ($data as $day => $item) {					
					foreach ($item as $items) {									
						$id = isset($items['id'])? intval($items['id']) :0;						
						if($id>0){										
							$model = AR_opening_hours::model()->find("merchant_id=:merchant_id AND id=:id",[
								':merchant_id'=>$merchant_id,
								':id'=>$id
							]);
							if(!$model){
								$model = new AR_opening_hours();	
							}
						} else $model = new AR_opening_hours();						
											
						$status = isset($items['close'])?$items['close']:false;						
						$model->merchant_id = $merchant_id;
						$model->time_config_type = $time_config_type;
						if($time_config_type=="custom_hours_transaction"){
							$model->transaction_type = $transaction_type;
						} else $model->transaction_type = null;
						$model->day = $day;
						$model->start_time = isset($items['start_time'])?$items['start_time']:'';
						$model->end_time = isset($items['end_time'])?$items['end_time']:'';
						$model->status = $status==1?"close":"open";
						$model->custom_text = isset($items['custom_text'])?$items['custom_text']:'';
						if(!$model->save()){			
							$error_count++;				
							$err = $model->getErrors();
							foreach ($err as $item_error) {
								foreach ($item_error as $item_errors) {
									$error[] = $item_errors;
								}
							}
						} else {
							$saved_ids[] = $model->id;
						}
					}
				}
			}

			OptionsTools::saveOptions($merchant_id,'time_config_type',$time_config_type);
			AR_opening_hours::removeOpeningHours($saved_ids,$merchant_id,$time_config_type,$transaction_type);
			$this->code = $error_count > 0 ? 2 : 1;
			$this->msg = $error_count<=0? t("Store hours updated") : t("Error has occured");
			$this->details = [
				'error_count'=>$error_count,
				'error'=>$error
			];			
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actionfetchscheduleday()
	{
		try {
			
			$merchant_id = intval(Yii::app()->merchant->merchant_id);
			$days = AttributesTools::dayList();
			$every_day = CMerchants::getOpeningHours($merchant_id,true,false,'every_day');
			$schedule_day = CMerchants::getOpeningHours($merchant_id,true,false,'schedule_day');
			
			$everyDayData = []; $scheduleData = [];
			foreach ($days as $day_code => $dayname){				
				if(array_key_exists($day_code,(array)$every_day)){					
					$everyDayData[$day_code] = $every_day[$day_code] ?? '';	
				} else {
					$everyDayData[$day_code] = [];
				}				
			}
								
			$time_range = AttributesTools::createTimeRange("00:00","23:59","15 mins","24","H:i");			
			$options = OptionsTools::find(['days_advance'],$merchant_id);			
			$days_advance = $options['days_advance'] ?? 0;

			$this->code = 1;
			$this->msg = "Ok";			
			$this->details = [
				'days_advance'=>intval($days_advance),
				'days'=>$days,
				'every_day'=>$everyDayData,		
				'schedule_day'=>$schedule_day,
				'time_range'=>CommonUtility::ArrayToLabelValue($time_range)
			];

		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actionupdatescheduleday()
	{
		try {

			$error = null; $error_count = 0; $saved_ids = []; $saved_ids2 = [];
			$raw_data = Yii::app()->input->xssClean(json_decode(file_get_contents('php://input'), true));			
			$merchant_id = intval(Yii::app()->merchant->merchant_id);									
			$data = $raw_data['data'] ?? null;
			$schedule_day = $raw_data['schedule_day'] ?? null;
			$time_config_type = $raw_data['time_config_type'] ?? null;		
			$days_advance = $raw_data['days_advance'] ?? 0;
			
			if(!$time_config_type){
				$this->msg = t("Invalid Time Configuration Type");
				$this->responseJson();
			}

			//dump($data);
			if(is_array($data) && count($data)>=1){
				foreach ($data as $day => $item) {					
					if (empty($item) || !is_array($item)) {								
						AR_opening_hours::removeOpeningHoursBytype($merchant_id,'every_day',$day);
					}
					foreach ($item as $items) {	
						$id = isset($items['id'])? intval($items['id']) :0;	
						if($id>0){										
							$model = AR_opening_hours::model()->find("merchant_id=:merchant_id AND id=:id AND time_config_type=:time_config_type",[
								':merchant_id'=>$merchant_id,
								':id'=>$id,
								':time_config_type'=>'every_day'
							]);
						} else $model = new AR_opening_hours();

						if(!$model){
							$model = new AR_opening_hours();
						}

						$status = isset($items['close'])?$items['close']:false;						
						$model->merchant_id = $merchant_id;
						$model->time_config_type = 'every_day';
						$model->day = $day;
						$model->start_time = isset($items['start_time'])?$items['start_time']:'';
						$model->end_time = isset($items['end_time'])?$items['end_time']:'';
						$model->status = $status==1?"close":"open";
						$model->custom_text = isset($items['custom_text'])?$items['custom_text']:'';
						if(!$model->save()){
							$error_count++;	
							$err = $model->getErrors();
							foreach ($err as $item_error) {
								foreach ($item_error as $item_errors) {
									$error.= $item_errors;
									$error.="\n";
								}
							}
						} else {
							$saved_ids[] = $model->id;
						}
					}
				}
			} 
			
			if(is_array($schedule_day) && count($schedule_day)>=1){
				foreach ($schedule_day as $day => $items) {					
					$id = isset($items['id'])? intval($items['id']) :0;	
					if($id>0){								
						$model = AR_opening_hours::model()->find("merchant_id=:merchant_id AND id=:id AND time_config_type=:time_config_type",[
							':merchant_id'=>$merchant_id,
							':id'=>$id,
							':time_config_type'=>'schedule_day'
						]);						
					} else $model = new AR_opening_hours();					

					if(!$model){
						$model = new AR_opening_hours();
					}

					$status = isset($items['close'])?$items['close']:false;								
					$model->merchant_id = $merchant_id;
					$model->time_config_type = 'schedule_day';			
					$model->day = 0;
					$model->start_time = isset($items['start_time'])?$items['start_time']:'';
					$model->end_time = isset($items['end_time'])?$items['end_time']:'';
					$model->status = $status==1?"close":"open";
					$model->custom_text = isset($items['custom_text'])?$items['custom_text']:'';
					if(!$model->save()){
						$error_count++;	
						$err = $model->getErrors();
						foreach ($err as $item_error) {
							foreach ($item_error as $item_errors) {
								$error.= $item_errors;
								$error.="\n";
							}
						}
					} else {
						$saved_ids2[] = $model->id;
					}					
				}
			} else {
				AR_opening_hours::removeOpeningHoursBytype($merchant_id,'schedule_day');
			}

			OptionsTools::saveOptions($merchant_id,'time_config_type',$time_config_type);
			OptionsTools::saveOptions($merchant_id,'days_advance',intval($days_advance));
			AR_opening_hours::removeOpeningHours($saved_ids,$merchant_id,'every_day');
			AR_opening_hours::removeOpeningHours($saved_ids2,$merchant_id,'schedule_day');
			$this->code = $error_count > 0 ? 2  : 1;
			$this->msg = $error_count<=0? t("Store hours updated") : t("Error has occured");
			$this->details = [
				'error_count'=>$error_count,
				'error'=>$error
			];			

		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

}
/*end class*/