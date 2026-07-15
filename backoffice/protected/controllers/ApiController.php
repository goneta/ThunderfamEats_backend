<?php
class ApiController extends CommonApi
{			

	public function beforeAction($action)
	{								
		$method = Yii::app()->getRequest()->getRequestType();
		if($method=="PUT"){
			$this->data = Yii::app()->input->xssClean(json_decode(file_get_contents('php://input'), true));
		} else $this->data = Yii::app()->input->xssClean($_POST);				
		return true;
	}
	
	public function actiongetOrderTab()
	{		
		$group_name = isset($this->data['group_name'])?$this->data['group_name']:'';				
		$criteria = new CDbCriteria;		
		$criteria->select = "group_name,stats_id";
		$criteria->order = "id ASC";
		$criteria->addCondition('group_name =:group_name');
		$criteria->params = array(':group_name' => trim($group_name) );
		$model=AR_order_settings_tabs::model()->findAll($criteria);		
		if($model){			
			$data = array();
			foreach ($model as $items) {
				array_push($data,$items->stats_id);
			}			
			$this->code = 1; $this->msg = "ok";
			$this->details = $data;
		} else $this->msg = t("No results");
		$this->responseJson();
	}
	
	public function actionsaveOrderTab()
	{				
		if(DEMO_MODE){
		  $this->msg[] = t("Modification not available in demo");
		  $this->responseJson();
        }
        
		$group_name = isset($this->data['group_name'])?$this->data['group_name']:'';
		$status = isset($this->data['status'])?$this->data['status']:'';		
		Yii::app()->db->createCommand("DELETE FROM {{order_settings_tabs}} 
		WHERE group_name=".q($group_name)." ")->query();
		if(is_array($status) && count($status)>=1){
			$params = array();
			foreach ($status as $val) {
				$params[]=array(
				  'group_name'=>$group_name,
				  'stats_id'=>intval($val),
				  'date_modified'=>CommonUtility::dateNow(),
				  'ip_address'=>CommonUtility::userIp()
				);
			}						
			try {			
				$builder=Yii::app()->db->schema->commandBuilder;
				$command=$builder->createMultipleInsertCommand("{{order_settings_tabs}}",$params);
				$command->execute();		
			} catch (Exception $e) {
			   $this->msg[] = $e->getMessage();
			   $this->responseJson();
			}	
		}
		$this->code = 1; $this->msg = t("Setting saved");
		$this->responseJson();
	}
	
	public function actionsaveOrderButtons()
	{		
		if(DEMO_MODE){
		  $this->msg[] = t("Modification not available in demo");
		  $this->responseJson();
        }
		$group_name = isset($this->data['group_name'])?$this->data['group_name']:'';
		$button_name = isset($this->data['button_name'])?$this->data['button_name']:'';
		$status = isset($this->data['status'])?$this->data['status']:'';	
		$order_type = isset($this->data['order_type'])?$this->data['order_type']:'';	
		$uuid = isset($this->data['uuid'])?$this->data['uuid']:'';	
		$do_actions = isset($this->data['do_actions'])?$this->data['do_actions']:'';
		$class_name = isset($this->data['class_name'])?$this->data['class_name']:'';		
			
		if(!empty($uuid)){
			$model=AR_order_settings_buttons::model()->find("uuid=:uuid",array(
			  ':uuid'=>$uuid
			));
			if(!$model){
				$this->msg = t("Record not found");
				$this->responseJson();
			}
		} else $model = new AR_order_settings_buttons;	
			
		$model->group_name = $group_name;	
		$model->button_name = $button_name;
		$model->stats_id = intval($status);
		$model->order_type = trim($order_type);
		$model->do_actions = $do_actions;
		$model->class_name = $class_name;
		if($model->save()){
			$this->code = 1;
			$this->msg = "ok";
		} else $this->msg = CommonUtility::parseError( $model->getErrors());
		$this->responseJson();
	}
	
	public function actiongetOrderButtonList()
	{					
		$group_name = isset($this->data['group_name'])?$this->data['group_name']:'';
		$criteria = new CDbCriteria;
		$criteria->select = "uuid,button_name,order_type,
		(
		  select description 
		  from {{order_status_translation}}
		  where language=".q(Yii::app()->language)."
		  and stats_id = t.stats_id
		) as status		
		";
		$criteria->order = "id ASC";
		$criteria->addCondition('group_name =:group_name');
		$criteria->params = array(':group_name' => trim($group_name) );
		$model=AR_order_settings_buttons::model()->findAll($criteria);		
		if($model){
			$data = array();
			foreach ($model as $item) {
				$data[]=array(
				  'uuid'=>$item->uuid,
				  'button_name'=>$item->button_name,
				  'order_type'=>$item->order_type,
				  'status'=>$item->status
				);
			}
			$this->code = 1;
			$this->msg = "ok";
			$this->details = $data;
		} else $this->msg[] = t("No results");
		$this->responseJson();
	}
	
	public function actiondeleteButtons()
	{	
		 
		if(DEMO_MODE){
		    $this->msg[] = t("Modification not available in demo");
		    $this->responseJson();
		}
 
		$uuid = isset($this->data['uuid'])?$this->data['uuid']:'';
		$model=AR_order_settings_buttons::model()->find("uuid=:uuid",array(
		  ':uuid'=>$uuid
		));
		if($model){
			$model->delete();
			$this->code = 1;
			$this->msg = "OK";
		} else $this->msg = t("Record not found");
		$this->responseJson();
	}
	
	public function actiongetButtons()
	{
		$uuid = isset($this->data['uuid'])?$this->data['uuid']:'';
		$model=AR_order_settings_buttons::model()->find("uuid=:uuid",array(
		  ':uuid'=>$uuid
		));
		if($model){			
			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(
			  'uuid'=>$model->uuid,
			  'button_name'=>$model->button_name,
			  'stats_id'=>$model->stats_id,
			  'order_type'=>$model->order_type,
			  'do_actions'=>$model->do_actions,
			  'class_name'=>$model->class_name
			);
		} else $this->msg = t("Record not found");
		$this->responseJson();
	}
	
	public function actioncommissionBalance()
	{
	    try {								
	    	$card_id = CWallet::createCard( Yii::app()->params->account_type['admin']);
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
	
	public function actiontransactionHistory()
	{
		$data = array(); $card_id = 0;
		try {	
		    $card_id = CWallet::getCardID(Yii::app()->params->account_type['admin']);	
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
		if($length>0){
			$pages->applyLimit($criteria);        		
		}        		
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
				
		$this->responseTable($datatables);
	}
		
	public function actioncommissionadjustment()
	{		
		try {								
			
			$transaction_description = isset($this->data['transaction_description'])?$this->data['transaction_description']:'';
			$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';
			$transaction_amount = isset($this->data['transaction_amount'])?$this->data['transaction_amount']:0;
			
			$base_currency = Price_Formatter::$number_format['currency_code'];
			$params = array(
			  'transaction_description'=>$transaction_description,			  
			  'transaction_type'=>$transaction_type,
			  'transaction_amount'=>floatval($transaction_amount),
			  'meta_name'=>"adjustment",
			  'meta_value'=>CommonUtility::createUUID("{{admin_meta}}",'meta_value'),
			  'orig_transaction_amount'=>floatval($transaction_amount),
			  'merchant_base_currency'=>$base_currency,
			  'admin_base_currency'=>$base_currency,			  
			);			
			
			$card_id = CWallet::createCard( Yii::app()->params->account_type['admin'] );
			CWallet::inserTransactions($card_id,$params);

			$this->code = 1; $this->msg = t("Successful");
			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());	
		}	
		$this->responseJson();		
	}
	
	public function actionmerchant_earninglist()
	{
		$data = array();								
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
					
		$sortby = "restaurant_name"; $sort = 'ASC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}			
		
		$page = $page>0? intval($page)/intval($length) :0;
		$criteria=new CDbCriteria();
		$criteria->alias = "a";
		$criteria->select = "a.merchant_id, a.merchant_uuid, a.restaurant_name, a.logo, a.path,
		(
		 select concat(running_balance,',',merchant_base_currency,',',admin_base_currency,',',exchange_rate_merchant_to_admin,',',exchange_rate_admin_to_merchant) 
		 from {{wallet_transactions}}
		 where card_id = (
		    select card_id from {{wallet_cards}}
		    where account_type=".q(Yii::app()->params->account_type['merchant'])." and account_id=a.merchant_id		    
		    limit 0,1
		 )
		 order by transaction_id DESC
		 limit 0,1
		) as balance,
		
		(
			select option_value 
			from {{option}}
			where merchant_id=a.merchant_id
			and option_name='merchant_default_currency'
			limit 0,1
		) as merchant_base_currency
		";
		
		$criteria->condition="status=:status";
		$criteria->params = array(
		 ':status'=>'active'
		);

		if(!empty($search)){
		    $criteria->addSearchCondition('a.restaurant_name', $search);
        }
        
		$criteria->order = "$sortby $sort";
		$count = AR_merchant::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
		if($length>0){
           $pages->applyLimit($criteria);        		
		}
				
        $models = AR_merchant::model()->findAll($criteria);
        if($models){						
						
			$base_currency = Price_Formatter::$number_format['currency_code'];
			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		    $multicurrency_enabled = $multicurrency_enabled==1?true:false;		

        	foreach ($models as $item) {			

				$atts_balance = !empty($item->balance)?explode(",",$item->balance):'';
				$balance = isset($atts_balance[0])?floatval($atts_balance[0]):0;

				if($multicurrency_enabled){
					$merchant_base_currency = isset($atts_balance[1])?$atts_balance[1]:$base_currency;
				} else $merchant_base_currency = $base_currency;
				
				$admin_base_currency = isset($atts_balance[2])?$atts_balance[2]:$base_currency;
				$exchange_rate_merchant_to_admin = isset($atts_balance[3])?floatval($atts_balance[3]):1;				
				
				$exchange_rate = 1;	
				if($multicurrency_enabled && $merchant_base_currency!=$admin_base_currency){
					$exchange_rate = $exchange_rate_merchant_to_admin;
				}

				$balance = Price_Formatter::formatNumber(($balance*$exchange_rate));				
				$logo_url = CMedia::getImage($item->logo,$item->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('merchant'));
				
				$view = Yii::app()->createUrl('earnings/transactions',array(
				'merchant_uuid'=>$item->merchant_uuid
				));
         	
        		
$logo_html = <<<HTML
<img src="$logo_url" class="img-60 rounded-circle" />
HTML;

$balance_html = <<<HTML
<b>$balance</b>
HTML;


$actions_html = <<<HTML
<div class="btn-group btn-group-actions" role="group">
 <a href="$view" target="_blank" class="btn btn-light tool_tips"><i class="zmdi zmdi-eye"></i></a>
 <a class="btn btn-light tool_tips"><i class="zmdi zmdi-money-off"></i></a>
</div>
HTML;

        	  $data[]=array(
        		'merchant_id'=>$item->merchant_id,
        		'logo'=>$logo_html,
        		'restaurant_name'=>Yii::app()->input->xssClean($item->restaurant_name),
        		'balance'=>$balance_html,
        		'merchant_uuid'=>$item->merchant_uuid,
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
			
	public function actionmerchant_transactions()
	{
		$data = array(); $card_id=0;
				
		$merchant_uuid = isset($this->data['merchant_uuid'])?$this->data['merchant_uuid']:'';	
		
		try {
		   $merchant = CMerchants::getByUUID($merchant_uuid);		   
		   $card_id = CWallet::getCardID( Yii::app()->params->account_type['merchant'] , $merchant->merchant_id );
		} catch (Exception $e) {		   
			//		   
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
				
		$page = $page>0? intval($page)/intval($length) :0;
		
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
        		
				$exchange_rate = $item->exchange_rate_merchant_to_admin>0?$item->exchange_rate_merchant_to_admin:1;
				
        		$transaction_amount = Price_Formatter::formatNumber($item->transaction_amount*$exchange_rate);        	
        		switch ($item->transaction_type) {
        			case "debit":
        			case "payout":
        				$transaction_amount = "(".Price_Formatter::formatNumber( ($item->transaction_amount*$exchange_rate) ).")";
        				break;        		        			
        		}
        		
        		$data[]=array(
        		  'transaction_date'=>Date_Formatter::date($item->transaction_date),
        		  'transaction_description'=>$description,
        		  'transaction_amount'=>$transaction_amount,
        		  'running_balance'=>Price_Formatter::formatNumber( ($item->running_balance*$exchange_rate) ),
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
	
	public function actiongetordersummary()
	{
		$merchant_uuid = isset($this->data['merchant_uuid'])?$this->data['merchant_uuid']:'';	
		$merchant = AR_merchant::model()->find("merchant_uuid=:merchant_uuid",array(
		  ':merchant_uuid'=>$merchant_uuid
		));		
		
		$merchant_id = 0;
		if($merchant){
		   
			try {	
				
		    	$merchant_id = $merchant->merchant_id;
		    	$initial_status = AttributesTools::initialStatus();
		    	$refund_status = AttributesTools::refundStatus();	
		    	$orders = 0; $order_cancel = 0; $total=0;
		    	
		    	$not_in_status = AOrderSettings::getStatus(array('status_cancel_order','status_rejection'));
		    	array_push($not_in_status,$initial_status);    		    	
		    	$orders = AOrders::getOrdersTotal($merchant_id,array(),$not_in_status);
		    	
		    	$status_cancel = AOrderSettings::getStatus(array('status_cancel_order'));		    	    	
			    $order_cancel = AOrders::getOrdersTotal($merchant_id,$status_cancel);
			    
			    $status_delivered = AOrderSettings::getStatus(array('status_delivered','status_completed'));			    				
			    $total = AOrders::getOrderSummary($merchant_id,$status_delivered,'exchange_rate_merchant_to_admin');
			    $total_refund = AOrders::getTotalRefund($merchant_id,$refund_status,'exchange_rate_merchant_to_admin');
		    	
			    $logo_url = CMedia::getImage($merchant->logo,$merchant->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('merchant'));
			    
		    	$data = array(
		    	 'merchant'=>array(
		    	   'name'=>$merchant->restaurant_name,
		    	   'logo_url'=>$logo_url,
		    	   'contact_phone'=>$merchant->contact_phone,
		    	   'contact_email'=>$merchant->contact_email,
		    	   'member_since'=>Date_Formatter::date($merchant->date_created),
		    	   'merchant_active'=>$merchant->status=='active'?true:false
		    	 ),
			     'orders'=>$orders,
			     'order_cancel'=>$order_cancel,
			     'total'=>Price_Formatter::formatNumberNoSymbol($total),
			     'total_refund'=>Price_Formatter::formatNumberNoSymbol($total_refund),
			     'price_format'=>array(
			       'symbol'=>Price_Formatter::$number_format['currency_symbol'],
			       'decimals'=>Price_Formatter::$number_format['decimals'],
			       'decimal_separator'=>Price_Formatter::$number_format['decimal_separator'],
			       'thousand_separator'=>Price_Formatter::$number_format['thousand_separator'],
			       'position'=>Price_Formatter::$number_format['position'],
			     )
			    );
			    
			    $this->code = 1;
				$this->msg = "OK";
				$this->details = $data;		    
			    
			} catch (Exception $e) {
			   $this->msg = t($e->getMessage());		
			}	
			
		} else $this->msg = t("Merchant not found");
		$this->responseJson();	
	}
	
	public function actionchangeMerchantStatus()
	{
		$merchant_uuid = isset($this->data['merchant_uuid'])?$this->data['merchant_uuid']:'';	
		$status = isset($this->data['status'])?$this->data['status']:0;	
		$merchant = AR_merchant::model()->find("merchant_uuid=:merchant_uuid",array(
		  ':merchant_uuid'=>$merchant_uuid
		));		
		if($merchant){
			$status = $status==1?'active':'blocked';
			$merchant->status = $status;
			if($merchant->save()){
				$this->code = 1;
				$this->msg = "ok";	
				$this->details = array(
				  'merchant_active'=>$status=='active'?true:false
				);
			} else $this->msg = CommonUtility::parseError( $merchant->getErrors());
		} else $this->msg = t("Merchant not found");
		$this->responseJson();	
	}
	
	public function actionmerchantTotalBalance()
	{
		try {								
			$balance = CEarnings::getTotalMerchantBalance();			
			$this->msg = "ok";
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());
		   $balance = 0;		
		}	
				
		$this->code = 1;		
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
	
	public function actionwithdrawalList()
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
		a.exchange_rate_merchant_to_admin,
		b.merchant_id, b.restaurant_name , b.logo , b.path";
		
		$criteria->join="LEFT JOIN {{merchant}} b on a.card_id = 
		(
		 select card_id from {{wallet_cards}}
		 where account_type=".q(Yii::app()->params->account_type['merchant'])." and account_id=b.merchant_id
		)
		";		
		
		$criteria->condition="transaction_type=:transaction_type";
		$criteria->params = array(		 
		 ':transaction_type'=>'payout'
		);
				
		if(is_array($transaction_type) && count($transaction_type)>=1){
			$criteria->addInCondition('a.status',(array) $transaction_type );
		}
		
		if(!empty($search)){
		    $criteria->addSearchCondition('a.restaurant_name', $search);
        }
        
        if(is_array($filter) && count($filter)>=1){
        	$filter_merchant_id = isset($filter['merchant_id'])?$filter['merchant_id']:'';
        	$criteria->addSearchCondition('b.merchant_id', $filter_merchant_id );
        }
        
        if(!empty($date_start) && !empty($date_end)){
			$criteria->addBetweenCondition("DATE_FORMAT(transaction_date,'%Y-%m-%d')", $date_start , $date_end );
		}
                
		$criteria->order = "$sortby $sort";
		$count = AR_wallet_transactions::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);

		if($length>0){
           $pages->applyLimit($criteria);        
		}
				
        $models = AR_wallet_transactions::model()->findAll($criteria);
        
        if($models){			
        	foreach ($models as $item) {
        						

				$logo_url = CMedia::getImage($item->logo,$item->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('merchant'));        	
				$transaction_amount = Price_Formatter::formatNumber( ($item->transaction_amount*$item->exchange_rate_merchant_to_admin) );
				$status = $item->status;
        	        	
        		
$logo_html = <<<HTML
<img src="$logo_url" class="img-60 rounded-circle" />
HTML;

$amount_html = <<<HTML
<p class="m-0"><b>$transaction_amount</b></p>
<p class="m-0"><span class="badge payment $status">$status</span></p>
HTML;


        	  $data[]=array(
        		'merchant_id'=>$item->merchant_id,        		        		
        		'logo'=>$logo_html,        		
        		'transaction_date'=>Date_Formatter::date($item->transaction_date),
        		'restaurant_name'=>Yii::app()->input->xssClean($item->restaurant_name),        		
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

	public function actiongetPayoutDetails()
	{
		try {
			
			$merchant = array(); $merchant_id = 0;
		    $transaction_uuid = isset($this->data['transaction_uuid'])?$this->data['transaction_uuid']:'';		    		    
		    $data = CPayouts::getPayoutDetails($transaction_uuid,true);			
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
	
	public function actionpayoutPaid()
	{
		try {			
			$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'payout';
			$transaction_uuid = isset($this->data['transaction_uuid'])?$this->data['transaction_uuid']:'';
			$model = AR_wallet_transactions::model()->find("transaction_uuid=:transaction_uuid",array(
			 ':transaction_uuid'=>$transaction_uuid
			));			
			if($model){
				//$model->scenario = "payout_paid";
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
				//$model->scenario = "payout_cancel";
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
	
	public function actionapprovedPayout()
	{
		try {			

			$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'payout';
			$transaction_uuid = isset($this->data['transaction_uuid'])?$this->data['transaction_uuid']:'';
			
			$model = AR_wallet_transactions::model()->find("transaction_uuid=:transaction_uuid",array(
			 ':transaction_uuid'=>$transaction_uuid
			));			
			if($model){				
				//$model->scenario = "payout_paid";
				$model->scenario = $transaction_type."_paid";
				$model->status="paid";
				if($model->save()){					
					$this->code = 1; $this->msg = t("Payout will process in a minute or two");
				} else $this->msg = CommonUtility::parseError( $model->getErrors());
			} else $this->msg = t("Transaction not found");
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());		   		   
		}	
		$this->responseJson();
		
	}
	
	public function actionpayoutSummary()
	{
		try {
			
			$data = CPayouts::payoutSummary("payout",0,true);
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
	
	public function actionsearchMerchant()
    {     	 
    	 $search = isset($this->data['search'])?$this->data['search']:''; 
    	 $data = array();
    	 
    	 $criteria=new CDbCriteria();    	 
    	 $criteria->condition = "status=:status";
    	 $criteria->params = array(
    	   ':status'=>'active'
    	 );
    	 if(!empty($search)){
			$criteria->addSearchCondition('restaurant_name', $search );			
		 }
		 $criteria->limit = 10;
		 if($models = AR_merchant::model()->findAll($criteria)){		 	
		 	foreach ($models as $val) {
		 		$data[]=array(
				  'id'=>$val->merchant_id,
				  'text'=>Yii::app()->input->xssClean($val->restaurant_name)
				);
		 	}
		 }
		 
		$result = array(
    	  'results'=>$data
    	);	    	
    	$this->responseSelect2($result);
    }    
    
    public function actionmerchantEarningAdjustment()
    {
    	try {								
			    		
			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
		    $multicurrency_enabled = $multicurrency_enabled==1?true:false;		

    		$merchant_id = isset($this->data['merchant_id'])?$this->data['merchant_id']:0;    		
    		$card_id = CWallet::getCardID( Yii::app()->params->account_type['merchant'] , $merchant_id);
    		
			$transaction_description = isset($this->data['transaction_description'])?$this->data['transaction_description']:'';
			$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';
			$transaction_amount = isset($this->data['transaction_amount'])?$this->data['transaction_amount']:0;

			$base_currency = Price_Formatter::$number_format['currency_code'];
			$attrs = OptionsTools::find(array('merchant_default_currency'),$merchant_id);

			if($multicurrency_enabled){
				$merchant_default_currency = isset($attrs['merchant_default_currency'])?$attrs['merchant_default_currency']:$base_currency;
			} else $merchant_default_currency = $base_currency;

			$exchange_rate_merchant_to_admin = 1; $exchange_rate_admin_to_merchant=1;
			if($merchant_default_currency!=$base_currency){
				$exchange_rate_merchant_to_admin = CMulticurrency::getExchangeRate($merchant_default_currency,$base_currency);
				$exchange_rate_admin_to_merchant = CMulticurrency::getExchangeRate($base_currency,$merchant_default_currency);
			}
						
			$params = array(
			  'card_id'=>intval($card_id),
			  'transaction_description'=>$transaction_description,			  
			  'transaction_type'=>$transaction_type,
			  'transaction_amount'=>floatval($transaction_amount),
			  'meta_name'=>"adjustment",
			  'meta_value'=>CommonUtility::createUUID("{{admin_meta}}",'meta_value'),
			  'merchant_base_currency'=>$merchant_default_currency,
			  'admin_base_currency'=>$base_currency,
			  'exchange_rate_merchant_to_admin'=>$exchange_rate_merchant_to_admin,
			  'exchange_rate_admin_to_merchant'=>$exchange_rate_admin_to_merchant,
			);			
			CWallet::inserTransactions($card_id,$params);
			$this->code = 1; $this->msg = t("Succesful");
			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());	
		}	
		$this->responseJson();		
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
    	 $data = array();
    	 
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
    
	public function actionallOrders()
	{
    	$data = array();		
    	$status = COrders::statusList(Yii::app()->language);    	
    	$services = COrders::servicesList(Yii::app()->language);
    	$payment_gateway = AttributesTools::PaymentProvider();
    	    					
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
				
		$page = $page>0? intval($page)/intval($length) :0;
		
		$criteria=new CDbCriteria();
		$criteria->alias = "a";
		$criteria->select = "a.order_id, a.client_id, a.status, a.order_uuid , a.merchant_id,
		a.payment_code, a.service_code,a.total, a.date_created,
		a.admin_base_currency,a.exchange_rate_merchant_to_admin,
		a.request_from,
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
		
		$criteria->condition = "meta_name=:meta_name ";
		$criteria->params  = array(		  
		  ':meta_name'=>'customer_name'
		);
		
		if(!empty($date_start) && !empty($date_end)){
			$criteria->addBetweenCondition("DATE_FORMAT(a.date_created,'%Y-%m-%d')", $date_start , $date_end );
		}
			
		$filter_order_status = '';
		if(is_array($filter) && count($filter)>=1){
			$filter_merchant_id = isset($filter['merchant_id'])?$filter['merchant_id']:'';
		    $filter_order_status = isset($filter['order_status'])?$filter['order_status']:'';
		    $filter_order_type = isset($filter['order_type'])?$filter['order_type']:'';
		    $filter_client_id = isset($filter['client_id'])?intval($filter['client_id']):'';
			$filter_order_id = isset($filter['order_id'])?intval($filter['order_id']):'';
		    
		    if($filter_merchant_id>0){
		    	$criteria->addSearchCondition('a.merchant_id', $filter_merchant_id );
		    }		    
			if(!empty($filter_order_status)){
				$criteria->addSearchCondition('a.status', $filter_order_status );
			}
			if(!empty($filter_order_type)){
				$criteria->addSearchCondition('a.service_code', $filter_order_type );
			}
			if($filter_client_id>0){
				$criteria->addSearchCondition('a.client_id', intval($filter_client_id) );
			}
			if($filter_order_id>0){
				$criteria->addSearchCondition('a.order_id', intval($filter_order_id) );
			}
		}
		
		if(empty($filter_order_status)){
			$exclude_status = AttributesTools::excludeStatus();	
		    $criteria->addNotInCondition('a.status', (array) $exclude_status );
		}		
				
		$criteria->order = "$sortby $sort";
				
		$count = AR_ordernew::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
		if($length>0){
           $pages->applyLimit($criteria);        
		}		
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
         	
			
			$exchange_rate  = $item->exchange_rate_merchant_to_admin>0?$item->exchange_rate_merchant_to_admin:1;

         	$total = t("Total. {{total}}",array(
         	 '{{total}}'=>Price_Formatter::formatNumber( ($item->total*$exchange_rate) )
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


         		$data[]=array(
         		  'merchant_id'=>$logo_html,
        		  'order_id'=>$item->order_id,
        		  'restaurant_name'=>$item->restaurant_name,
        		  'client_id'=>$item->customer_name,
				  'request_from'=>t($item->request_from),
        		  'status'=>$information,
        		  'order_uuid'=>$item->order_uuid,
        		  'view_order'=>Yii::app()->createAbsoluteUrl('/order/view',array('order_uuid'=>$item->order_uuid)),
        		  'view_pdf'=>Yii::app()->createAbsoluteUrl('/preprint/pdf',array('order_uuid'=>$item->order_uuid)),
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
	
	public function actiongetNotifications()
	{
		$data = [];
		$total_jobs = CNotificationData::getUnprocessJobs();
		try {								
			$data = CNotificationData::getList( Yii::app()->params->realtime['admin_channel']  );						
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	

		if($total_jobs>0){
			$new_data = [
				'notification_type'=>"system",
				'message'=>t("You have {total} pending cron jobs to process. Please run the cron job processing script in your cPanel.",[
					'{total}'=>$total_jobs
				]),
				'date'=>PrettyDateTime::parse(new DateTime(date("c"))),
				'image'=>'',
				'url'=>''
			];

			$data['count'] = (isset($data['count'])?$data['count']:0) +1;
			if (!isset($data['data']) || !is_array($data['data'])) {
				$data['data'] = [];
			}
			array_push($data['data'], $new_data);
		}
		
		if(is_array($data) && count($data)>=1){
			$this->code = 1; 
			$this->msg = "ok";
			$this->details = $data;
		}
		$this->responseJson();		
	}
	
	public function actionclearNotifications()
	{
		try {						
						
			AR_notifications::model()->deleteAll('notication_channel=:notication_channel',array(
			 ':notication_channel'=>Yii::app()->params->realtime['admin_channel']
			));
			$this->code = 1; $this->msg = "ok";
						
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
		$order = isset($this->data['order'])?  isset($this->data['order'][0])?$this->data['order'][0]:''   :'';	
		
		$date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
		$date_end = isset($this->data['date_end'])?$this->data['date_end']:'';
				
		$sortby = "date_created"; $sort = 'DESC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = $page>0? intval($page)/intval($length) :0;
		$criteria=new CDbCriteria();
		$criteria->condition="notication_channel=:notication_channel";
		$criteria->params = array(':notication_channel'=>Yii::app()->params->realtime['admin_channel']);
		
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
			   $user_settings = CNotificationData::getUserSettings(Yii::app()->user->id,'admin');		
			} catch (Exception $e) {
			   //
			}
			
			$data = array(
			  'enabled'=>$enabled,
			  'provider'=>$provider,
			  'pusher_instance_id'=>$pusher_instance_id,			  
			  'onesignal_app_id'=>$onesignal_app_id,
			  'safari_web_id'=>'',
			  'channel'=>Yii::app()->params->realtime['admin_channel'],
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
	
	public function actiongetwebnotifications()
	{
		try {
			
			$data = CNotificationData::getUserSettings(Yii::app()->user->id,'admin');
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
		      ':user_id'=>intval(Yii::app()->user->id),
		      ':user_type'=>"admin"
		    ));
		    if(!$model){
		       $model = new AR_device;			       
		    } 		    		    
		    $model->interest = $interest;
		    $model->user_type = 'admin';
	    	$model->user_id = intval(Yii::app()->user->id);
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
	
	public function actionupdatewebdevice()
	{
		try {
						
			$device_id = isset($this->data['device_id'])?$this->data['device_id']:'';
			
			$model = AR_device::model()->find("user_id=:user_id AND user_type=:user_type",array(
		      ':user_id'=>intval(Yii::app()->user->id),
		      ':user_type'=>"admin"
		    ));
		    if($model){
		    	$model->scenario = "update_device_token";
		    	$model->device_token = $device_id;
		    	if($model->save()){
			    	$this->code = 1;
				    $this->msg = t("device updated");		    
		    	} else $this->msg = CommonUtility::parseError( $model->getErrors());
		    } else $this->msg = t("user device not found");
			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();		
	}
	
	public function actionpushlogs()
	{
		$data = array();		
						
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  isset($this->data['order'][0])?$this->data['order'][0]:''   :'';	
		
		$date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
		$date_end = isset($this->data['date_end'])?$this->data['date_end']:'';
				
		$sortby = "date_created"; $sort = 'DESC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = $page>0? intval($page)/intval($length) :0;
		$criteria=new CDbCriteria();
		if(!empty($search)){
			$criteria->addSearchCondition('platform', $search );
			$criteria->addSearchCondition('body', $search , true , 'OR' );
			$criteria->addSearchCondition('channel_device_id', $search , true , 'OR' );
		 }
		
		if(!empty($date_start) && !empty($date_end)){
			$criteria->addBetweenCondition("DATE_FORMAT(date_created,'%Y-%m-%d')", $date_start , $date_end );
		}
		
		$criteria->order = "$sortby $sort";
		$count = AR_push::model()->count($criteria); 
		
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        
        $models = AR_push::model()->findAll($criteria);
        if($models){
        	foreach ($models as $item) {
        		        		
        		$data[]=array(				 
        		  'date_created'=>Date_Formatter::dateTime($item->date_created),
				  'platform'=>$item->platform,				  
				  'body'=>'<div class="text-truncate" style="max-width:200px;">'.Yii::app()->input->purify($item->body).'</div>',				  
				  'channel_device_id'=>'<div class="text-truncate" style="max-width:200px;">'.Yii::app()->input->purify($item->channel_device_id).'</div>',
				  'delete_url'=>Yii::app()->createUrl("/notifications/delete_push/",array('id'=>$item->push_uuid)),		  
				  'view_id'=>$item->push_uuid,
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
		
	public function actiongetOrderStatusList()
	{		
		if ($data = AttributesTools::getOrderStatusList(Yii::app()->language)){
			$this->code =1; $this->msg = "ok";
			$this->details = $data;
		} else $this->msg = t("No results");
		$this->responseJson();	
	}
	
	public function actiongetGroupname()
	{
		try {
						
			$group_name=''; $modify_order = false;	$filter_buttons = false;		
		    $order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';			    
		    
		    try {
		        $model = COrders::get($order_uuid);		    		    		    
			    $group_name = AOrderSettings::getGroup($model->status);		    
			    if($group_name=="new_order"){
					$modify_order = true;
				}
				if($group_name=="order_ready"){
					$filter_buttons = true;
				}
			} catch (Exception $e) {
		    	//
            }            
			
			$manual_status = isset(Yii::app()->params['settings']['enabled_manual_status'])?Yii::app()->params['settings']['enabled_manual_status']:false;
			
			$merchant_uuid='';
			try {
			  $merchant = CMerchants::get($model->merchant_id);
			  $merchant_uuid = $merchant->merchant_uuid;
			} catch (Exception $e) {
			   
			}
						
			$data = array(
			  'client_id'=>$model->client_id,
			  'merchant_id'=>$model->merchant_id,
			  'merchant_uuid'=>$merchant_uuid,
			  'group_name'=>$group_name,
			  'manual_status'=>$manual_status,
			  'modify_order'=>false,
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
	
	public function actionorderDetails()
	{	
		
		$this->data = Yii::app()->input->xssClean(json_decode(file_get_contents('php://input'), true));		
		$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';			
		$group_name = isset($this->data['group_name'])?$this->data['group_name']:'';		
		$filter_buttons = isset($this->data['filter_buttons'])?$this->data['filter_buttons']:'';
		$payload = isset($this->data['payload'])?$this->data['payload']:'';
		$modify_order = isset($this->data['modify_order'])?intval($this->data['modify_order']):'';
				
				
		try {
										
			$exchange_rate = 1;
			$model_order = COrders::get($order_uuid);			 
			if($model_order->base_currency_code!=$model_order->admin_base_currency){
			   $exchange_rate = $model_order->exchange_rate_merchant_to_admin>0?$model_order->exchange_rate_merchant_to_admin:1;
			   Price_Formatter::init($model_order->admin_base_currency);
			} else {
			   Price_Formatter::init($model_order->admin_base_currency);
			}			 
			COrders::setExchangeRate($exchange_rate);

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

		    $total_order = CMerchants::getTotalOrders($merchant_id);	
		    $merchant_info['order_count'] = $total_order;	    
		    		    
		    $order = COrders::orderInfo(Yii::app()->language, date("Y-m-d") );			
		    $order_type = isset($order['order_info'])?$order['order_info']['order_type']:'';
		    $client_id = $order?$order['order_info']['client_id']:0;		
		    $order_id = $order?$order['order_info']['order_id']:'';		    
		    $customer = COrders::getClientInfo($client_id);				    
		    
			$origin_latitude = $order?$order['order_info']['latitude']:'';
			$origin_longitude = $order?$order['order_info']['longitude']:'';    		    
			$delivery_direction = isset($merchant_info['restaurant_direction'])?$merchant_info['restaurant_direction']:'';
			if($order_type=="delivery"){
				$delivery_direction = isset($merchant_info['restaurant_direction'])?$merchant_info['restaurant_direction']:'';
				$delivery_direction.="&origin="."$origin_latitude,$origin_longitude";
			} 
			$order['order_info']['delivery_direction'] = $delivery_direction;					
		    		    
		    $draft = AttributesTools::initialStatus();
		    $not_in_status = AOrderSettings::getStatus(array('status_cancel_order','status_rejection'));
		    array_push($not_in_status,$draft);    				   		   
		    $orders = ACustomer::getOrdersTotal($client_id,0,array(), (array)$not_in_status );		    

			$customer = is_array($customer) ? $customer : [];
		    $customer['order_count'] = $orders;
		    
		    		    
		    $buttons = array(); $link_pdf = '';  $print_settings = array(); $payment_history = array();
		    
		    if(in_array('buttons',(array)$payload)){		 
		      if($filter_buttons){
		      	 $buttons = AOrders::getOrderButtons($group_name,$order_type);
		      } else $buttons = AOrders::getOrderButtons($group_name);		      
		    }
		    		    
		    if(in_array('print_settings',(array)$payload)){
			    $link_pdf = array(
			      'pdf_a4'=>Yii::app()->CreateUrl("preprint/pdf",array('order_uuid'=>$order_uuid,'size'=>"a4")),
			      'pdf_receipt'=>Yii::app()->CreateUrl("preprint/pdf",array('order_uuid'=>$order_uuid,'size'=>"thermal")),
			    );		    
			    $print_settings = AOrderSettings::getPrintSettings();				
		    }
		    		    
		    if(in_array('payment_history',(array)$payload)){    
		       $payment_history = COrders::paymentHistory($order_id);
		    }
			
			$credit_card_details = '';
			$payment_code = $order['order_info']['payment_code'];
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
						'phone_number'=>$driver->phone_prefix.$driver->phone,
						'email_address'=>$driver->email,
						'photo_url'=>CMedia::getImage($driver->photo,$driver->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('driver')),
						'url'=>Yii::app()->createAbsoluteUrl("/driver/overview",['id'=>$driver->driver_uuid]),
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

			$atts = OptionsTools::find(['self_delivery'],$merchant_id);									
			$self_delivery = isset($atts['self_delivery'])?$atts['self_delivery']:false;			
			$self_delivery = $self_delivery==1?true:false;			

			if(($order_type=="delivery" && !$self_delivery)|| ($order_type=="delivery" || $order_type=="bt")){
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

			$order_status = isset($order['order_info'])? (isset($order['order_info']['status'])?$order['order_info']['status']:null) :null;			

			// ISSUE REFUND CONDITIONS
			$total_credit = COrders::getRefundBalance($order_id);
			$status_completed = AOrderSettings::getStatus(array('status_delivered','status_completed'));
			$can_issue_refund = false;						
			if(in_array($order_status,(array)$status_completed) && $total_credit>0 ){
				$can_issue_refund = true;
			}

			$can_delete_order = false;
			$can_manage_order = false;

			try {
				COrders::getExistingRefund($order_id);								
				$can_delete_order = true;
			    $can_manage_order = true;				
			} catch (Exception $e) {				
				//echo $e->getMessage();
			}

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
			   'total_credit'=>$total_credit,
			   'can_issue_refund'=>$can_issue_refund,
			   'can_delete_order'=>$can_delete_order,
			   'can_manage_order'=>$can_manage_order
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
	
	public function actiongetCustomerDetails()
	{
		try {
					   
		   $this->data = Yii::app()->input->xssClean(json_decode(file_get_contents('php://input'), true));		   
		   $client_id = isset($this->data['client_id'])?intval($this->data['client_id']):0;		   	  
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
	
	public function actiongetCustomerSummary()
	{
		try {		  
					    			
		    $client_id = isset($this->data['client_id'])?$this->data['client_id']:0;		    
		    //$merchant_id = isset($this->data['merchant_id'])?intval($this->data['merchant_id']):0;
		    $merchant_id = 0;

		    $draft = AttributesTools::initialStatus();			    
		    
		    $not_in_status = AOrderSettings::getStatus(array('status_cancel_order','status_rejection'));
		    array_push($not_in_status,$draft);    				   		   
		    $orders = ACustomer::getOrdersTotal($client_id,$merchant_id,array(), (array)$not_in_status );
		    
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
	
	public function actiongetCustomerOrders()
	{
		$this->data = Yii::app()->input->xssClean(json_decode(file_get_contents('php://input'), true));		
		$data = array();				
		//$merchant_id = isset($this->data['merchant_id'])?intval($this->data['merchant_id']):0;
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
		
		
		$page = $page>0? intval($page)/intval($length) :0;
		
		$initial_status = AttributesTools::initialStatus();
		$status = COrders::statusList(Yii::app()->language);		
					
		$criteria=new CDbCriteria();	
		$criteria->alias = "a";
		$criteria->select="a.order_id,a.order_uuid,a.total,a.status, b.restaurant_name";
		$criteria->join='LEFT JOIN {{merchant}} b on  a.merchant_id=b.merchant_id ';
		/*$criteria->condition = "merchant_id=:merchant_id AND client_id=:client_id ";
		$criteria->params  = array(
		  ':merchant_id'=>intval($merchant_id),
		  ':client_id'=>intval($client_id)
		);*/
		$criteria->condition = "client_id=:client_id ";
		$criteria->params  = array(		  
		  ':client_id'=>intval($client_id)
		);
		$criteria->order = "$sortby $sort";
		
		if (is_string($search) && strlen($search) > 0){
		   $criteria->addSearchCondition('order_id', $search );
		   $criteria->addSearchCondition('a.status', $search , true , 'OR' );
		}
		$criteria->addNotInCondition('a.status', array($initial_status) );
				
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
        	Yii::app()->createUrl('/order/view',array('order_uuid'=>$val->order_uuid))
        	,$buttons);
        	
        	$_buttons = str_replace("{{print_pdf}}",
        	Yii::app()->createUrl('/preprint/pdf',array('order_uuid'=>$val->order_uuid))
        	,$_buttons);
        	
        	$data[]=array(
        	 'order_id'=>$val->order_id,
        	 'restaurant_name'=>Yii::app()->input->xssClean($val->restaurant_name),
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
				
		$this->responseTable($datatables);
	}	
	
public function actionblockCustomer()
	{
		try {
						
			$meta_name = 'block_customer';
			$this->data = Yii::app()->input->xssClean(json_decode(file_get_contents('php://input'), true));			
									
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
	
    public function actiongetOrderHistory()
	{
		try {			
			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';			
			$data = AOrders::getOrderHistory($order_uuid);
			$order_status = AttributesTools::getOrderStatus(Yii::app()->language,'delivery_status');

			$order = COrders::get($order_uuid);			
			$meta_proof = AR_driver_meta::getMeta2(0,$order->order_id,array('order_proof'));		

			$meta = AR_admin_meta::getValue('status_delivery_delivered');
            $delivery_status = isset($meta['meta_value'])?$meta['meta_value']:'';

			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(
			  'data'=>$data,
			  'order_status'=>$order_status,
			  'order_proof'=>$meta_proof,
			  'delivery_status'=>$delivery_status
			);			
		} catch (Exception $e) {
		    $this->msg[] = t($e->getMessage());		   
		}	
		$this->responseJson();
	}	
	
	public function actiongetAllOrderSummary()
	{
		try {	
					    	
			$start_date	 = isset($this->data['start_date'])?$this->data['start_date']:'';
			$end_date    = isset($this->data['end_date'])?$this->data['end_date']:'';			

	    	$exclude_status = AttributesTools::excludeStatus();		    					
	    	$refund_status = AttributesTools::refundStatus();	
	    	$orders = 0; $order_cancel = 0; $total=0;
	    	
	    	$not_in_status = AOrderSettings::getStatus(array('status_cancel_order','status_rejection'));
	    	$not_in_status = array_merge((array)$not_in_status,(array)$exclude_status);			
	    	$orders = AOrders::getOrdersTotal(0,array(),$not_in_status,$start_date,$end_date);
	    	
	    	$status_cancel = AOrderSettings::getStatus(array('status_cancel_order'));		    	    	
		    $order_cancel = AOrders::getOrdersTotal(0,$status_cancel,'',$start_date,$end_date);
		    
		    $status_delivered = AOrderSettings::getStatus(array('status_delivered','status_completed'));			
		    $total = AOrders::getOrderSummary(0,$status_delivered,'exchange_rate_merchant_to_admin',$start_date,$end_date);			
		    $total_refund = AOrders::getTotalRefund(0,$refund_status,'exchange_rate_merchant_to_admin',$start_date,$end_date);
	    	
	    	$data = array(	    	
		     'orders'=>$orders,
		     'order_cancel'=>$order_cancel,
		     'total'=>Price_Formatter::convertToRaw($total,2),
		     'total_refund'=>Price_Formatter::convertToRaw($total_refund,2),
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
	
	public function actionplans_features()
	{
		
		$ref_id = isset($this->data['ref_id'])?$this->data['ref_id']:0;	
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  isset($this->data['order'][0])?$this->data['order'][0]:''   :'';	
		
		$date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
		$date_end = isset($this->data['date_end'])?$this->data['date_end']:'';
		$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';		
				
		$data = array();
		$sortby = "meta_id"; $sort = 'DESC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = $page>0? intval($page)/intval($length) :0;
		$criteria=new CDbCriteria();
		$criteria->addCondition('meta_name=:meta_name AND meta_value1=:meta_value1');
		$criteria->params = array(':meta_name'=>'plan_features', ':meta_value1'=>$ref_id );		
		
		$criteria->order = "$sortby $sort";
		$count = AR_admin_meta::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        
        $models = AR_admin_meta::model()->findAll($criteria);
        if($models){
        	foreach ($models as $item) {        		
        		$data[]=array(
        		  'meta_id'=>$item->meta_id,
        		  'meta_value'=>$item->meta_value,
        		  'update_url'=>Yii::app()->createUrl("/plans/feature_update/",array('id'=>$item->meta_value1,'meta_id'=>$item->meta_id)),
        		  'delete_url'=>Yii::app()->createUrl("/plans/feature_delete/",array('id'=>$item->meta_value1,'meta_id'=>$item->meta_id)),
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
	
	
	public function actioncustomerOrderList()
	{
		$data = array();
		$status = COrders::statusList(Yii::app()->language);    	
        $services = COrders::servicesList(Yii::app()->language);
        $payment_gateway = AttributesTools::PaymentProvider();
		$exclude_status = AttributesTools::excludeStatus();
		
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
		
		$criteria->condition = "a.client_id=:client_id AND b.meta_name=:meta_name ";
		$criteria->params  = array(		  
		  ':client_id'=>intval($client_id),
		  ':meta_name'=>'customer_name'
		);    		
		
		if(!empty($date_start) && !empty($date_end)){
			$criteria->addBetweenCondition("DATE_FORMAT(a.date_created,'%Y-%m-%d')", $date_start , $date_end );
		}
		
		$filter_order_status = null;
		if(is_array($filter) && count($filter)>=1){
	        $filter_merchant_id = isset($filter['merchant_id'])?$filter['merchant_id']:'';
	        $filter_order_status = isset($filter['order_status'])?$filter['order_status']:'';
	        $filter_order_type = isset($filter['order_type'])?$filter['order_type']:'';
	        $filter_client_id = isset($filter['client_id'])?intval($filter['client_id']):'';
			$filter_order_id = isset($filter['order_id'])?intval($filter['order_id']):'';
	        
	        if($filter_merchant_id>0){
	            $criteria->addSearchCondition('a.merchant_id', $filter_merchant_id );
	        }		    
	        if(!empty($filter_order_status)){
	            $criteria->addSearchCondition('a.status', $filter_order_status );
	        }
	        if(!empty($filter_order_type)){
	            $criteria->addSearchCondition('a.service_code', $filter_order_type );
	        }
	        if($filter_client_id>0){
	            $criteria->addSearchCondition('a.client_id', intval($filter_client_id) );
	        }
			if($filter_order_id>0){
				$criteria->addSearchCondition('a.order_id', intval($filter_order_id) );
			}
	    }

		if(empty($filter_order_status)){
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
	
        		$data[]=array(
        		  'merchant_id'=>$logo_html,        		  
        		  'client_id'=>$information,
        		  'order_id'=>$item->order_id,
        		  'restaurant_name'=>$item->restaurant_name,
        		  'order_uuid'=>$item->order_uuid,
        		  'view_order'=>Yii::app()->createAbsoluteUrl('/order/view',array('order_uuid'=>$item->order_uuid)),
        		  'view_pdf'=>Yii::app()->createAbsoluteUrl('/preprint/pdf',array('order_uuid'=>$item->order_uuid)),
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
		$criteria->condition = "merchant_id=0";		
		
		if (is_string($search) && strlen($search) > 0){
			$criteria->addSearchCondition('zone_name', $search );			
		}
				
		$criteria->order = "$sortby $sort";
		$count = AR_zones::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);

		if($length>0){
           $pages->applyLimit($criteria);        
		}
                        
        $models = AR_zones::model()->findAll($criteria);
        if($models){        	
        	foreach ($models as $item) {    
        		$data[]=array(
        		  'zone_id'=>$item->zone_id,
        		  'zone_name'=>$item->zone_name,
        		  'description'=>$item->description,
        		  'zone_id'=>$item->zone_id,
        		  'update_url'=>Yii::app()->createUrl("/attributes/zone_update/",array('id'=>$item->zone_uuid)),
        		  'delete_url'=>Yii::app()->createUrl("/attributes/zone_delete/",array('id'=>$item->zone_uuid)),
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

	public function actiondashboardSummary()
	{
		try {
			
			$balance = 0;
			$status_completed = AOrderSettings::getStatus(array('status_delivered','status_completed'));
			
			$total_sales = CReports::totalSales($status_completed);
			$total_merchant = CReports::totalMerchant(array('active'));			
			$total_subscriptions = CReports::totalSubscriptions();			
			
		    try {								
	           $card_id = CWallet::createCard( Yii::app()->params->account_type['admin']);
	           $balance = CWallet::getBalance($card_id);
	        } catch (Exception $e) {
	           //
	        }	
						
			
			$data = array(
			  'total_sales'=>intval($total_sales),
			  'total_merchant'=>intval($total_merchant),
			  'total_commission'=>floatval($balance),
			  'total_subscriptions'=>floatval($total_subscriptions),
			  'price_format'=>AttributesTools::priceUpFormat()
			);
			
			$this->code = 1; $this->msg = "ok";
            $this->details = $data;
           
			
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());				   
		}	
		$this->responseJson();	
	}
	
	public function actioncommissionSummary()
	{
		try {
			
			$card_id = 0;
			try {								
	           $card_id = CWallet::createCard( Yii::app()->params->account_type['admin']);	           
	        } catch (Exception $e) {
	           //
	        }	
	        	        
	        $commission_week = CReports::WalletEarnings($card_id);	        
	        $commission_month = CReports::WalletEarnings($card_id,30);
	        $subscription_month = CReports::PlansEarning(30);			
	        
	        $data = array(
	          'commission_week'=>floatval($commission_week),
	          'commission_month'=>floatval($commission_month),
	          'subscription_month'=>floatval($subscription_month),	          
			  'price_format'=>AttributesTools::priceUpFormat()
	        );			
	        
	        $this->code = 1; $this->msg = "ok";
            $this->details = $data;
			
		} catch (Exception $e) {
		   $this->msg = t($e->getMessage());				   
		}	
		$this->responseJson();	
	}
	
    public function actiongetLastTenOrder()
    {
    	try {
        
    		$data = array(); $order_status = array(); $datetime=date("Y-m-d g:i:s a");    		
    		$filter_by = Yii::app()->input->post('filter_by'); 
    		$limit = Yii::app()->input->post('limit'); 
    		    		  
    		if($filter_by!="all"){
	    		$order_status = AOrders::getOrderTabsStatus($filter_by);			    		
    		}
    				    		
    		$status = COrders::statusList(Yii::app()->language);    	
            $services = COrders::servicesList(Yii::app()->language);
            $payment_status = COrders::paymentStatusList2(Yii::app()->language,'payment');  
            $status_not_in = AOrderSettings::getStatus(array('status_delivered','status_completed',
              'status_cancel_order','status_rejection','status_delivery_fail','status_failed'
            ));						
            $payment_list = AttributesTools::PaymentProvider();	  			
                        
    		$criteria=new CDbCriteria();
		    $criteria->alias = "a";
		    $criteria->select = "a.order_id, a.order_uuid, a.client_id, a.status, a.order_uuid , a.merchant_id,
		    a.payment_code, a.service_code,a.total, a.delivery_date, a.delivery_time, a.date_created, a.payment_code, a.total,
		    a.payment_status, a.is_view, a.is_critical, a.whento_deliver,		
			a.use_currency_code,a.base_currency_code, a.admin_base_currency, a.exchange_rate, a.exchange_rate_merchant_to_admin,   		    		    
			a.delivery_status,
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
		    ) as total_items,
		    
		    c.restaurant_name
		    
		    ";
		    $criteria->join='
		    LEFT JOIN {{ordernew_meta}} b on a.order_id = b.order_id 
		    LEFT JOIN {{merchant}} c on a.merchant_id = c.merchant_id 
		    ';
		    $criteria->condition = "b.meta_name=:meta_name ";
		    $criteria->params  = array(		      
		      ':meta_name'=>'customer_name'
		    );
		    
		    if(is_array($order_status) && count($order_status)>=1){
		    	$criteria->addInCondition('a.status',(array) $order_status );
		    } else {
		    	$exclude_status = AttributesTools::excludeStatus();		    	
		    	$criteria->addNotInCondition('a.status',(array)$exclude_status);
            }

			$criteria->addNotInCondition("a.request_from",['pos']);
		    
		    $criteria->order = "a.order_id DESC";		    
		    $criteria->limit = intval($limit);
		    
		    PrettyDateTime::$category='backend';
		    		    		    		    			
		    $models = AR_ordernew::model()->findAll($criteria);  		    
		    if($models){		    	
				
				$price_list_format = CMulticurrency::getAllCurrency();				

		    	foreach ($models as $item) {
		    		
		    		$status_trans = $item->status;
		            if(array_key_exists($item->status, (array) $status)){
		               $status_trans = $status[$item->status]['status'];
		            }

					if($item->delivery_status=="waiting_for_driver"){
						$status_trans.= "(".t("Waiting for Driver").")";
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
			        } else if ($item->min_diff>10 && !in_array($item->status,(array)$status_not_in) ) {
			        	$is_critical = true;
			        }
										
					$price_format = isset($price_list_format[$item->admin_base_currency])?$price_list_format[$item->admin_base_currency]:Price_Formatter::$number_format;									
			        
		    		$data[]=array(
		    		  'order_id'=>$item->order_id,
		    		  'order_id'=>t("Order #{{order_id}}",array('{{order_id}}'=>$item->order_id)),
		    		  'restaurant_name'=>CommonUtility::safeDecode($item->restaurant_name),
		    		  'order_uuid'=>$item->order_uuid,
		    		  'client_id'=>$item->client_id,
		    		  'customer_name'=>Yii::app()->input->xssClean(t($item->customer_name)),
		    		  'status'=>$status_trans,
		    		  'status_raw'=>str_replace(" ","_",$item->status),
		    		  'order_type'=>$trans_order_type,
		    		  'payment_code'=> $item->payment_code==CDigitalWallet::transactionName()? CDigitalWallet::paymentName() : t($item->payment_code),
		    		  'total'=>Price_Formatter::formatNumber2( ($item->total*$item->exchange_rate_merchant_to_admin) ,$price_format),
		    		  'payment_status'=>$payment_status_name,
		    		  'payment_status_raw'=>str_replace(" ","_",$item->payment_status),		    		  
					  'delivery_status'=>$item->delivery_status,
			          'is_view'=>$item->is_view,
			          'is_critical'=>$is_critical,
			          'min_diff'=>$item->min_diff,
			          'whento_deliver'=>$item->whento_deliver,
			          'delivery_date'=>$item->delivery_date,
			          'delivery_time'=>$item->delivery_time,
			          'view_order'=>Yii::app()->createAbsoluteUrl('/order/view',array('order_uuid'=>$item->order_uuid)),
        		      'print_pdf'=>Yii::app()->createAbsoluteUrl('/preprint/pdf',array('order_uuid'=>$item->order_uuid)),
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
			$language = Yii::app()->language;			
			
			$limit = Yii::app()->input->post('limit'); 
			$status_completed = AOrderSettings::getStatus(array('status_delivered','status_completed'));						
			
			$criteria=new CDbCriteria();
			$criteria->alias = "a";
			$criteria->select="
			a.item_id, 
			a.cat_id, 
			sum(a.qty) as total_sold,			
			m.restaurant_name,			
			IF(COALESCE(NULLIF(item_trans.item_name, ''), '') = '', item.item_name, item_trans.item_name) as item_name,			
			IF(COALESCE(NULLIF(category_trans.category_name, ''), '') = '', category.category_name, category_trans.category_name) as category_name,
			item.photo , item.path
			";
			$criteria->join='			
			LEFT JOIN {{ordernew}} c on a.order_id = c.order_id 
			LEFT JOIN {{merchant}} m on c.merchant_id = m.merchant_id 

			left JOIN (
			   SELECT item_id,item_name,photo,path FROM {{item}} 
			) item 
			 on a.item_id = item.item_id

			left JOIN (
			   SELECT item_id,item_name FROM {{item_translation}} where language='.q($language).'
			) item_trans 
			 on a.item_id = item_trans.item_id

			 left JOIN (
			   SELECT cat_id,category_name FROM {{category}} 
			) category 
			 on a.cat_id = category.cat_id

			 left JOIN (
			   SELECT cat_id,category_name FROM {{category_translation}} 
			) category_trans
			 on a.cat_id = category_trans.cat_id
			';						
						
			if(is_array($status_completed) && count($status_completed)>=1){			
			   $criteria->addInCondition('c.status', (array) $status_completed );
		    }		
						
			$criteria->group="a.item_id,a.cat_id,m.restaurant_name";
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
		       	    'restaurant_name'=>Yii::app()->input->xssClean($item->restaurant_name),
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
		   echo $this->msg;
		   $this->details = array(
		    	  'image_url'=>CMedia::themeAbsoluteUrl()."/assets/images/order-best-food@2x.png"
		    	);
		}
		$this->responseJson();	
    }       
    
    public function actionitemSales()
    {
    	try {
    		    		
    		$data = array();  $items = array(); $data = array();
    		$period = Yii::app()->input->post('period'); 
    		
			Yii::app()->db->createCommand("SET SESSION sql_mode = (SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))")->query();
    		$data = CReports::ItemSales(0,$period);
    		
    		try {
    		   $items = CReports::popularItems(0,$period);
    		} catch (Exception $e) {
    			//
    		}
    		
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
    
    public function actionsalesOverview()
    {
    	try {
    	
    		$data = array();
    		$months = intval(Yii::app()->input->post('months')); 
    		
    		$status_completed = AOrderSettings::getStatus(array('status_delivered','status_completed'));		        		
    		$date_start = date("Y-m-d", strtotime(date("c")." -$months months"));
    		$date_end = date("Y-m-d");

			$table = new TableDataStatus();
			$field_exist = $table->fieldExist("{{ordernew}}",'created_at');
			
    		$criteria=new CDbCriteria();

			if($field_exist){		
				Yii::app()->db->createCommand("SET SESSION sql_mode = (SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))")->query();

				$criteria->select = "
				DATE_FORMAT(`created_at`, '%b') AS month , SUM(total) as monthly_sales
				";    					
				$criteria->group="MONTH(`created_at`)";
				$criteria->order = "created_at DESC";	
				
				if(is_array($status_completed) && count($status_completed)>=1){			
				$criteria->addInCondition('status', (array) $status_completed );
				}				    
				if(!empty($date_start) && !empty($date_end)){
					$criteria->addBetweenCondition("DATE_FORMAT(created_at,'%Y-%m-%d')", $date_start , $date_end );
				}				
			} else {
				$criteria->select = "DATE_FORMAT(date_created, '%b') AS month , SUM(total) as monthly_sales"; 
				 
				$criteria->group="DATE_FORMAT(date_created, '%b')";
				$criteria->order = "date_created DESC";	
				
				if(is_array($status_completed) && count($status_completed)>=1){			
				   $criteria->addInCondition('status', (array) $status_completed );
				}				    
				if(!empty($date_start) && !empty($date_end)){
					$criteria->addBetweenCondition("DATE_FORMAT(date_created,'%Y-%m-%d')", $date_start , $date_end );
				}
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
    
    public function actionmostPopularCustomer()
    {
    	try {
    		
    		$data = array();		
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
			
			$criteria->condition = "b.client_id IS NOT NULL";			
			
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
		        $this->details = $data;
		    } else $this->msg = t("You don't have customer yet");
    		
    	} catch (Exception $e) {
		   $this->msg = t($e->getMessage());			   		   
		}
		$this->responseJson();	
    }    
	
    public function actionOverviewReview()
    {
    	try {
    	
    	    $data = array(); $total = 0;
    		$merchant_id = 0;
    		
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
    		  'link_to_review'=>Yii::app()->createAbsoluteUrl('/buyer/review_list')
    		);    	
    		
    		$this->code = 1; $this->msg = "ok";
		    $this->details = $data;
		        
    	} catch (Exception $e) {
		   $this->msg = t($e->getMessage());			   		   
		}
		$this->responseJson();	
    }
    
    public function actionpopularMerchant()
    {
    	try {
    		
    		$limit = Yii::app()->input->post('limit'); 
    		$data = CReports::PopularMerchant($limit);
    		$this->code = 1; $this->msg = "ok";
		    $this->details = $data;		    		    
    		
    	} catch (Exception $e) {
		   $this->msg = t($e->getMessage());			   		   		   
		}
		$this->responseJson();	
    }
    
    public function actionPopularMerchantByReview()
    {
    	try {
    		
    		$limit = Yii::app()->input->post('limit'); 
    		$data = CReports::PopularMerchantByReview($limit);
						
    		$cuisine_list = AttributesTools::cuisineGroup(Yii::app()->language,$data['merchant_ids']);
    		
    		$this->code = 1; $this->msg = "ok";
		    $this->details = array(
		      'data'=>$data['data'],
		      'cuisine_list'=>$cuisine_list,
		    );		    		    
		    
    	} catch (Exception $e) {
		   $this->msg = t($e->getMessage());			   		   		   		
		}
		$this->responseJson();	
    }
    
    public function actionDailyStatistic()
    {    
    	try	{
    		
    	    $status_new = AOrderSettings::getStatus(array('status_new_order'));								
    	    $status_delivered = AOrderSettings::getStatus(array('status_delivered','status_completed'));								
    	    
    		$order_received = CReports::OrderTotalByStatus(0,$status_new);
    		$today_delivered = CReports::OrderTotalByStatus(0,$status_delivered);
    		$new_customer = CReports::CustomerTotalByStatus(1);
    		$total_refund = CReports::TotalRefund();
    		
    		$data = array(
    		  'order_received'=>$order_received,
    		  'today_delivered'=>$today_delivered,
    		  'new_customer'=>$new_customer,
    		  'total_refund'=>$total_refund,
    		  'price_format'=>AttributesTools::priceUpFormat()
    		);
    		$this->code = 1; $this->msg = "ok";
		    $this->details = $data;
    		
    	} catch (Exception $e) {
		   $this->msg = t($e->getMessage());			   		   		   		
		}
		$this->responseJson();	
    }

    public function actionRecentPayout()   
    {
    	try {
    		
    		$data = array();
    	    $limit = Yii::app()->input->post('limit'); 
    	    $criteria=new CDbCriteria();
    	    $criteria->alias = "a";
    	    $criteria->select = "a.transaction_date, a.transaction_amount, a.status, a.transaction_uuid,
    	    (
    	      select concat(restaurant_name,';',logo,';',path) 
    	      from {{merchant}}
    	      where merchant_id = b.account_id
    	    ) as meta_name
    	    ";
    	    $criteria->join = "LEFT JOIN {{wallet_cards}} b on  a.card_id = b.card_id ";
    	    
    	    $criteria->condition = "a.transaction_type=:transaction_type";
			$criteria->params = array(':transaction_type'=>'payout');
			
			$criteria->addNotInCondition('a.status', array('cancelled') );
			$criteria->limit = intval($limit);
			$criteria->order = "a.transaction_date DESC";
			
    	    
    	    if($model = AR_wallet_transactions::model()->findAll($criteria)){
    	    	foreach ($model as $item) {    	    		    	    
    	    		//$meta_name = explode(";",$item->meta_name);
					$meta_name = explode(";", $item->meta_name ?? '');					
    	    		$restaurant_name = isset($meta_name[0])?$meta_name[0]:'';
    	    		$logo = isset($meta_name[1])?$meta_name[1]:'';
    	    		$path = isset($meta_name[2])?$meta_name[2]:'';
    	    		
    	    		$image_url = CMedia::getImage($logo,$path,'@thumbnail',
		             CommonUtility::getPlaceholderPhoto('merchant'));
		             	    		
	    	    	$data[] = array(
	    	    	  'transaction_uuid'=>$item->transaction_uuid,
	    	    	  'restaurant_name'=>Yii::app()->input->xssClean($restaurant_name),  
	    	    	  'transaction_date'=>Date_Formatter::dateTime($item->transaction_date),
	    	    	  'transaction_amount'=>$item->transaction_amount,
	    	    	  'transaction_amount_pretty'=>Price_Formatter::formatNumber($item->transaction_amount),
	    	    	  'status'=>$item->status,    	    	  
	    	    	  'status_class'=>str_replace(" ","_",$item->status),
	    	    	  'image_url'=>$image_url
	    	    	);
    	    	}
    	    	
    	    	$this->code = 1; $this->msg = "ok";
		        $this->details = $data;		    
    	    } else $this->msg = t("No recent payout request");     	    
    		
    	} catch (Exception $e) {
		   $this->msg = t($e->getMessage());			   		   		   		
		}
		$this->responseJson();	
    }
    
    public function actionReportsMerchantReg()
    {    	
    	$data = array(); 
    	$status_list = AttributesTools::StatusManagement('customer' , Yii::app()->language );    	
    	$merchant_type_list = AttributesTools::ListMerchantType(Yii::app()->language);    	
    	
    	$page = isset($this->data['start'])?$this->data['start']:0;	
	    $length = isset($this->data['length'])?$this->data['length']:0;	
	    $draw = isset($this->data['draw'])?$this->data['draw']:0;	
	    $search = isset($this->data['search'])?$this->data['search']['value']:'';	
	    $columns = isset($this->data['columns'])?$this->data['columns']:'';
	    $order = isset($this->data['order'])?  isset($this->data['order'][0])?$this->data['order'][0]:''   :'';	
	    
	    $date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
	    $date_end = isset($this->data['date_end'])?$this->data['date_end']:'';
	    $transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';		
	    $filter = isset($this->data['filter'])?$this->data['filter']:'';	
	            
	    $sortby = "date_created"; $sort = 'DESC';
	    
	    if(is_array($order) && count($order)>=1){
	        if(array_key_exists($order['column'],(array)$columns)){			
	            $sort = $order['dir'];
	            $sortby = $columns[$order['column']]['data'];
	        }
	    }	    
	    
	    $page = $page>0? intval($page)/intval($length) :0;
	    
	    $criteria=new CDbCriteria();
	    
	    if(is_array($transaction_type) && count($transaction_type)>=1){
           $criteria->addInCondition('status',(array) $transaction_type );
        }		
        if(!empty($date_start) && !empty($date_end)){
			$criteria->addBetweenCondition("DATE_FORMAT(date_created,'%Y-%m-%d')", $date_start , $date_end );
		}				
        if(is_array($filter) && count($filter)>=1){
        	$filter_merchant_id = isset($filter['merchant_id'])?$filter['merchant_id']:'';
        	$criteria->addSearchCondition('merchant_id', $filter_merchant_id );
        }
	    
	    $criteria->order = "$sortby $sort";
	    $count = AR_merchant::model()->count($criteria); 
	    $pages=new CPagination( intval($count) );
	    $pages->setCurrentPage( intval($page) );        
	    $pages->pageSize = intval($length);
	    $pages->applyLimit($criteria);        
	    
	    $models = AR_merchant::model()->findAll($criteria);
	    if($models){
	    	foreach ($models as $item) {
	    		$avatar = CMedia::getImage($item->logo,$item->path,'@thumbnail',
		         CommonUtility::getPlaceholderPhoto('merchant'));
		         
                 $restaurant_name = Yii::app()->input->xssClean($item->restaurant_name);
                 $status = $item->status;
                 if(array_key_exists($item->status,(array)$status_list)){
                 	$status = $status_list[$item->status];
                 }
                 
                 $merchant_type = $item->merchant_type;
                 if(array_key_exists($item->merchant_type,(array)$merchant_type_list)){
                 	$merchant_type = $merchant_type_list[$item->merchant_type];
                 }
                 
                 $view_merchant =  Yii::app()->createUrl('/vendor/edit',array(
				    'id'=>$item->merchant_id
				  ));
                           
$html_resto = <<<HTML
<p class="m-0">$restaurant_name</p>
<div class="badge customer $item->status">$status</div>
HTML;


		    	$data[] = array(		    	 
		    	  'logo'=>'<a href="'.$view_merchant.'"><img class="img-60 rounded-circle" src="'.$avatar.'"></a>',
		    	  'restaurant_name'=>$html_resto,
		    	  'address'=>Yii::app()->input->xssClean($item->address),
		    	  'merchant_type'=>$merchant_type,
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
    
    public function actionReportsMerchantSummary()
    {
    	try {
    		
    		$total_registered = CReports::MerchantTotal(0);    		
    		$commission_total = CReports::MerchantTotal(2, array('active') );
    		$membership_total = CReports::MerchantTotal(1, array('active') );
    		$total_active = CReports::MerchantTotal(0, array('active') );   
    		$total_inactive = CReports::MerchantTotal(0, array('pending','draft','expired') );   
    		
    		$data = array(
    		  'total_registered'=>$total_registered,
    		  'commission_total'=>$commission_total,
    		  'membership_total'=>$membership_total,
    		  'total_active'=>$total_active,
    		  'total_inactive'=>$total_inactive,
    		  'price_format'=>AttributesTools::priceFormat()
    		);    		
    		
    		$this->code = 1; $this->msg = "ok";
		    $this->details = $data;
    		
    	} catch (Exception $e) {
		   $this->msg = t($e->getMessage());			   		   		   		
		}
		$this->responseJson();	
    }
    
    public function actionreportsmerchantplan()
    {
    	
    	$payment_gateway = AttributesTools::PaymentProvider();
        $data = array();    	
        
    	$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  isset($this->data['order'][0])?$this->data['order'][0]:''   :'';	
		
		$date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
		$date_end = isset($this->data['date_end'])?$this->data['date_end']:'';
		$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';		
		$filter = isset($this->data['filter'])?$this->data['filter']:'';	
				
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
		$criteria->select="a.merchant_id, a.invoice_number,a.invoice_ref_number,a.created,a.amount,a.status,a.payment_code,
		b.title , c.restaurant_name , c.logo, c.path
		";
		$criteria->join='
		LEFT JOIN {{plans_translation}} b on  a.package_id=b.package_id 
		LEFT JOIN {{merchant}} c on  a.merchant_id = c.merchant_id 
		';
				
		
		$params = array();
		$criteria->addCondition("b.language=:language and c.restaurant_name IS NOT NULL AND TRIM(c.restaurant_name) <> ''");
		$params['language'] = Yii::app()->language;
		
		if(is_array($filter) && count($filter)>=1){
        	$filter_merchant_id = isset($filter['merchant_id'])?$filter['merchant_id']:'';
        	$criteria->addCondition('a.merchant_id=:merchant_id');
        	$params['merchant_id']  = intval($filter_merchant_id);
        }
        
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
        		  'merchant_id'=>$item->restaurant_name,        	
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
    
    public function actionreportsorderearnings()
    {
    	$data = array(); 
    	$status_completed = AOrderSettings::getStatus(array('status_delivered','status_completed'));
    	
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
	            
	    $sortby = "order_id"; $sort = 'DESC';
	    
	    if(is_array($order) && count($order)>=1){
	        if(array_key_exists($order['column'],(array)$columns)){			
	            $sort = $order['dir'];
	            $sortby = $columns[$order['column']]['data'];
	        }
	    }	    
	    
	    $page = $page>0? intval($page)/intval($length) :0;
	    
	    $criteria=new CDbCriteria();
	    	    
	    if(is_array($status_completed) && count($status_completed)>=1){			
		    $criteria->addInCondition('status', (array) $status_completed );
		}		
		    
        if(!empty($date_start) && !empty($date_end)){
			$criteria->addBetweenCondition("DATE_FORMAT(date_created,'%Y-%m-%d')", $date_start , $date_end );
		}				
		
		if(!empty($search)){
		    $criteria->addSearchCondition('order_id', $search);
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
	    		
	    		$view_order = Yii::app()->createUrl('order/view',array(
				    'order_uuid'=>$item->order_uuid
				));

				$commission_value = $item->commission_type == 'percentage' 
					? rtrim(rtrim(number_format($item->commission_value, 4, '.', ''), '0'), '.') 
					: Price_Formatter::formatNumber($item->commission_value);
				$suffix = $item->commission_type == 'percentage' ? '%' : '';
	    		
		    	$data[] = array(		    	 
		    	  'order_id'=>'<a href="'.$view_order.'">'.$item->order_id."</a>",
				  'commission_value'=>"{$commission_value}{$suffix}",
		    	  'sub_total'=>Price_Formatter::formatNumber($item->sub_total_less_discount),
		    	  'total'=>Price_Formatter::formatNumber($item->total),
		    	  'merchant_earning'=>Price_Formatter::formatNumber($item->merchant_earning),
		    	  'commission'=>Price_Formatter::formatNumber($item->commission),
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
    
    public function actionreportsorderearningsummary()
    {
    	try {
    		
    		$date_start = Yii::app()->input->post('date_start');
		    $date_end = Yii::app()->input->post('date_end');		
    		$status_completed = AOrderSettings::getStatus(array('status_delivered','status_completed'));
    		
    		$total_count = CReports::EarningTotalCount($status_completed , $date_start , $date_end);   
    		$admin_earning = CReports::EarningByOrder('admin',$status_completed , $date_start , $date_end ); 
    		$merchant_earning = CReports::EarningByOrder('merchant',$status_completed, $date_start , $date_end); 
    		$total_sell = CReports::EarningByOrder('sales',$status_completed, $date_start , $date_end);     		
    		
    		$data = array(
    		  'total_count'=>$total_count,    		  
    		  'admin_earning'=>floatval($admin_earning),    		      		  
    		  'merchant_earning'=>floatval($merchant_earning),    		      		  
    		  'total_sell'=>floatval($total_sell),
    		  'price_format'=>AttributesTools::priceFormat()
    		);    		
    		
    		$this->code = 1; $this->msg = "ok";
		    $this->details = $data;		    
    		
    	} catch (Exception $e) {
		   $this->msg = t($e->getMessage());			   		   		   		
		}
		$this->responseJson();	
    }
    
    public function actionEmailLogs()
    {
    	$data = array(); 
    	$status_completed = AOrderSettings::getStatus(array('status_delivered','status_completed'));
    	
    	$page = isset($this->data['start'])?$this->data['start']:0;	
	    $length = isset($this->data['length'])?$this->data['length']:0;	
	    $draw = isset($this->data['draw'])?$this->data['draw']:0;	
	    $search = isset($this->data['search'])?$this->data['search']['value']:'';	
	    $columns = isset($this->data['columns'])?$this->data['columns']:'';
	    $order = isset($this->data['order'])?  isset($this->data['order'][0])?$this->data['order'][0]:''   :'';	
	    
	    $date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
	    $date_end = isset($this->data['date_end'])?$this->data['date_end']:'';
	    $transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';		
	    $filter = isset($this->data['filter'])?$this->data['filter']:'';	
	            
	    $sortby = "id"; $sort = 'DESC';
	    
	    if(is_array($order) && count($order)>=1){
	        if(array_key_exists($order['column'],(array)$columns)){			
	            $sort = $order['dir'];
	            $sortby = $columns[$order['column']]['data'];
	        }
	    }	    
	    
	    $page = $page>0? intval($page)/intval($length) :0;
	    
	    $criteria=new CDbCriteria();
	    	    
        if(!empty($date_start) && !empty($date_end)){
			$criteria->addBetweenCondition("DATE_FORMAT(date_created,'%Y-%m-%d')", $date_start , $date_end );
		}				
		
		if(!empty($search)){
		    $criteria->addSearchCondition('email_address', $search);		    
			$criteria->addSearchCondition('subject', $search , true , 'OR' );
			$criteria->addSearchCondition('content', $search , true , 'OR' );
        }

		$criteria->order = "$sortby $sort"; 
	    	   
	    $count = AR_email_logs::model()->count($criteria); 
	    $pages=new CPagination( intval($count) );
	    $pages->setCurrentPage( intval($page) );        
	    $pages->pageSize = intval($length);

		if($length>0){
	      $pages->applyLimit($criteria);        
		}
	    
	    $models = AR_email_logs::model()->findAll($criteria);
	    if($models){
	    	foreach ($models as $item) {	    
	    		
		    	$data[] = array(		    	 
		    	  'date_created'=>$item->date_created,
		    	  'email_address'=>$item->email_address,		    	  
		    	  'subject'=>'<div class="text-truncate" style="max-width:150px;">'.Yii::app()->input->purify($item->subject).'</div>',
		    	  'sms_message'=>'<div class="text-truncate" style="max-width:150px;">'.Yii::app()->input->purify($item->subject).'</div>',
		    	  'status'=>$item->status,
		    	  'delete_url'=>Yii::app()->createUrl("/notifications/delete_email/",array('id'=>$item->id)),
		    	  'view_id'=>$item->id,
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
		$order = isset($this->data['order'])?  isset($this->data['order'][0])?$this->data['order'][0]:''   :'';	
		
		$date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
		$date_end = isset($this->data['date_end'])?$this->data['date_end']:'';
		$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';		
		$filter = isset($this->data['filter'])?$this->data['filter']:'';	
		$merchant_id = isset($this->data['ref_id'])?$this->data['ref_id']:0;
				
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
    
    public function actionsmslogs()
    {
     	$data = array();		
						
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  isset($this->data['order'][0])?$this->data['order'][0]:''   :'';	
		
		$date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
		$date_end = isset($this->data['date_end'])?$this->data['date_end']:'';
				
		$sortby = "date_created"; $sort = 'DESC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = $page>0? intval($page)/intval($length) :0;
		$criteria=new CDbCriteria();
		
		if(!empty($date_start) && !empty($date_end)){
			$criteria->addBetweenCondition("DATE_FORMAT(date_created,'%Y-%m-%d')", $date_start , $date_end );
		}
		
		if(!empty($search)){
			$criteria->addSearchCondition('contact_phone', $search );
			$criteria->addSearchCondition('sms_message', $search , true , 'OR' );
			$criteria->addSearchCondition('status', $search , true , 'OR' );
		 }
		
		$criteria->order = "$sortby $sort";
				
		//dump($criteria);die();
		$count = AR_sms_broadcast_details::model()->count($criteria); 
		
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
		if($length>0){
           $pages->applyLimit($criteria);                
		}
        $models = AR_sms_broadcast_details::model()->findAll($criteria);
        if($models){
        	foreach ($models as $item) {        
        		$data[]=array(				 
        		  'date_created'=>Date_Formatter::dateTime($item->date_created),
				  'gateway'=>$item->gateway,
				  'contact_phone'=>$item->contact_phone,
				  'sms_message'=>'<div class="text-truncate" style="max-width:150px;">'.Yii::app()->input->purify($item->sms_message).'</div>',
				  'status'=>$item->status,
				  'delete_url'=>Yii::app()->createUrl("/sms/delete/",array('id'=>$item->id)),     
				  'view_id'=>$item->id,
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
    
    public function actiongetSMS()
    {
    	try {
    		
    		$view_id = Yii::app()->input->post('view_id'); 
    		$model = AR_sms_broadcast_details::model()->find("id=:id",array(
    		  ':id'=>intval($view_id)
    		));
    		if($model){
    			$data = array(
    			  'content'=>Yii::app()->input->purify($model->sms_message),
    			  'type'=>"sms"
    			);
    			
    			$this->code = 1; $this->msg = "ok";
		        $this->details = $data;    		
    			
    		} else $this->msg = t(HELPER_RECORD_NOT_FOUND);
    		    		
    	} catch (Exception $e) {
		   $this->msg = t($e->getMessage());			   		   		   		
		}
		$this->responseJson();	
    }
    
    public function actiongetemail()
    {
    	try {
    		
    		$data = array();
    		$view_id = Yii::app()->input->post('view_id');     		
    		$model = AR_email_logs::model()->find("id=:id",array(
    		  ':id'=>intval($view_id)
    		));
    		if($model){    			
    			$data = array(
    			  'content'=>Yii::app()->input->purify($model->content),
    			  'type'=>"email"
    			);    			
    			$this->code = 1; $this->msg = "ok";
 		        $this->details = $data;    		 
    		} else $this->msg = t(HELPER_RECORD_NOT_FOUND);    		    		
    	} catch (Exception $e) {
		   $this->msg = t($e->getMessage());			   		   		   		
		}
		$this->responseJson();	
    }
    
    public function actiongetpush()
    {
    	try {
    		
    		$data = array();
    		$view_id = Yii::app()->input->post('view_id');     		
    		$model = AR_push::model()->find("push_uuid=:push_uuid",array(
    		  ':push_uuid'=>$view_id
    		));
    		if($model){    			
    			$data = array(
    			  'content'=>Yii::app()->input->purify($model->body),
    			  'type'=>"sms"
    			);    			
    			$this->code = 1; $this->msg = "ok";
 		        $this->details = $data;    		 
    		} else $this->msg = t(HELPER_RECORD_NOT_FOUND);    		    		
    	} catch (Exception $e) {
		   $this->msg = t($e->getMessage());			   		   		   		
		}
		$this->responseJson();	
    }
    
    public function actionrefundreports()
    {
        	
    	$status = COrders::statusList(Yii::app()->language);    	
    	$payment_list = AttributesTools::PaymentProvider();       
    	$data = array();		
    	
    	$page = isset($this->data['start'])?$this->data['start']:0;	
	    $length = isset($this->data['length'])?$this->data['length']:0;	
	    $draw = isset($this->data['draw'])?$this->data['draw']:0;	
	    $search = isset($this->data['search'])?$this->data['search']['value']:'';	
	    $columns = isset($this->data['columns'])?$this->data['columns']:'';
	    $order = isset($this->data['order'])?  isset($this->data['order'][0])?$this->data['order'][0]:''   :'';	
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
	    $criteria->select ="a.client_id,a.order_id,a.merchant_id,a.transaction_description,a.payment_code,
	    a.trans_amount, a.status, a.payment_reference, a.date_created,
	    b.logo as photo, b.path,
	    c.order_uuid
	    ";	    
	    $criteria->join='
	    LEFT JOIN {{merchant}} b on  a.merchant_id = b.merchant_id
	    LEFT JOIN {{ordernew}} c on  a.order_id = c.order_id
	    ';	  
	    		
		$criteria->addInCondition('a.transaction_name', array('refund','partial_refund','full_refund') );
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
	    	foreach ($model as $item) {	  

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
		        $transaction_description = t(Yii::app()->input->xssClean($item->transaction_description));
		        $reference = t("Payment reference# {{payment_reference}}",array(
		          '{{payment_reference}}'=>$item->payment_reference
		        ));
		        
		        $view_order = Yii::app()->createUrl('order/view',array(
		           'order_uuid'=>$item->order_uuid
		         ));
	    		    		
$information = <<<HTML
$transaction_description<span class="ml-2 badge payment $status_class">$status_trans</span>
<p class="font12 dim m-0">$date</p>
<p class="font12 dim m-0">$reference</p>
HTML;
		         		         
	    		$data[] = array(	    	
	    		  'date_created'=>$item->date_created,
	    		  'merchant_id'=>'<img class="img-60 rounded-circle" src="'.$avatar.'">',
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
        
    public function actionAllPages()
    {
    	try {
    		
    		$data = PPages::all(Yii::app()->language);
    		$this->code = 1;
    		$this->msg = "ok";
    		$this->details = $data;
    		
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
    			 $model = MMenu::get($menu_id,PPages::menuType());
    		} else $model = new AR_menu();    		
    		
    		$model->scenario = "theme_menu";
    		
    		$model->menu_type = PPages::menuType();
    		$model->menu_name = $menu_name;
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
    
    public function actionsortMenu()
    {
    	try {
    		
    		$menu = isset($this->data['menu'])?$this->data['menu']:'';
    		if(is_array($menu) && count($menu)>=1){
    			foreach ($menu as $index=>$item) {    				
    				if($model = MMenu::get($item['menu_id'],PPages::menuType())){    					
    					$model->sequence= intval($index);
    					$model->save();
    				}
    			}
    			$this->code = 1;
		        $this->msg = t("Sort menu saved");
    		} else $this->msg = t("Invalid data");
    		
    	} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
    }
    
    public function actionMenuList()
    {
    	try {
    		     		
    		$data = array();
			try {
			    $data = MMenu::getMenu(0,PPages::menuType());
			} catch (Exception $e) {
			   //	
            }
    		
    		$current_menu = AR_admin_meta::getValue( PPages::menuActiveKey() );
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
    
    public function actiongetMenuDetails()
    {
    	try {
    		
    		$current_menu = Yii::app()->input->post('current_menu');     		
    		$model = AR_menu::model()->findByPk(intval($current_menu));
    		if($model){
    			
    			$data = array();
    			try {
    			    $data = MMenu::getMenu($current_menu,PPages::menuType());
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
    
    public function actiondeletemenu()
    {
    	try {

    		$menu_id = intval(Yii::app()->input->post('menu_id'));  
    		
    		$model = AR_menu::model()->find("menu_id=:menu_id AND menu_type=:menu_type",array(
			   ':menu_id'=>intval($menu_id),
			   ':menu_type'=>PPages::menuType()
			 ));
			 			
			if($model){			   
			   $model->scenario = "theme_menu";		
			   $model->delete();
			   $this->code = 1;
	    	   $this->msg = t(Helper_success);
			} else $this->msg = t(Helper_not_found);
    		
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
    				$model->menu_type=PPages::menuType();
    				$model->menu_name = $page->title;
    				$model->parent_id = $menu_id;
    				$model->link = '{{site_url}}/'.$page->slug;
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
    
    public function actionaddCustomPageToMenu()
    {
    	try {
    		    		
    		$menu_id = isset($this->data['menu_id'])?intval($this->data['menu_id']):0;
    		$custom_link_text = isset($this->data['custom_link_text'])?trim($this->data['custom_link_text']):'';
    		$custom_link = isset($this->data['custom_link'])?trim($this->data['custom_link']):'';
    		
    		$model = new AR_menu();
    		$model->scenario = "custom_link";
    		$model->menu_type=PPages::menuType();
			$model->menu_name = $custom_link_text;
			$model->parent_id = $menu_id;
			$model->link = $custom_link;

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
    		$model = MMenu::get($menu_id,PPages::menuType());
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

	public function actiongetAddons()
	{
		try {

			$data = array();
			$criteria = new CDbCriteria();
            $criteria->order = 'id DESC'; 
			$model = AR_addons::model()->findAll($criteria);
			if($model){
				foreach ($model as $key => $items) {				
					$data[] = [
                       'id'=>$items->id,					   
					   'uuid'=>$items->uuid,
					   'addon_name'=>CHtml::encode($items->addon_name),
					   'version'=>t("Version {{version}}",['{{version}}'=>$items->version]),
					   'image'=>CMedia::getImage($items->image,$items->path),	
					   'activated'=>$items->activated==1?true:false				   
					];
				}
				$this->code = 1;
				$this->msg = "ok";				
				$this->details = ['data'=>$data];
			} else $this->msg = t("No results");

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}

	public function actionenableddisabledaddon()
	{

		if(DEMO_MODE){
			$this->msg = t("This action is not available in demo");
			$this->responseJson();
		}

		try {
			$uuid = isset($this->data['uuid'])?$this->data['uuid']:'';
			$activated = isset($this->data['activated'])?$this->data['activated']:0;
			$model = AR_addons::model()->find("uuid=:uuid",[':uuid'=>$uuid]);
			if($model){
				$model->activated = intval($activated);
				$model->save();
				$this->code = 1;
				$this->msg = $model->activated ==1? t("Addon activated") : t("Addon de-activated");				
				$this->details = ['title'=>t("Successful") ];
			} else {
				$this->details = ['title'=>t("Failed") ];
				$this->msg = t("Record not found");
			}
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
			$this->details = ['title'=>t("Failed") ];
		}
		$this->responseJson();
	}

	public function actionbannerList()
	{
		$data = array();		
						
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  isset($this->data['order'][0])?$this->data['order'][0]:''   :'';	
						
		$sortby = "date_created"; $sort = 'DESC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = intval($page)/intval($length);		
		$criteria=new CDbCriteria();
		$criteria->condition="owner=:owner";
		$criteria->params = array(':owner'=> 'admin');

		if(!empty($search)){
			$criteria->addSearchCondition('title', $search );
			$criteria->addSearchCondition('banner_type', $search );
		}		
				
		$criteria->order = "$sortby $sort";
		$count = AR_banner::model()->count($criteria); 
		
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
		if($length>0){
           $pages->applyLimit($criteria);        
		}
		
        $models = AR_banner::model()->findAll($criteria);
        if($models){

			$banner_type_list = AttributesTools::BannerType2();

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
				  'banner_type'=>isset($banner_type_list[$item->banner_type])?$banner_type_list[$item->banner_type]:$item->banner_type,
        		  'date_created'=>Date_Formatter::dateTime($item->date_created),
				  'update_url'=>Yii::app()->createUrl("/marketing/banner_update/",array('id'=>$item->banner_uuid)),
        		  'delete_url'=>Yii::app()->createUrl("/marketing/banner_delete/",array('id'=>$item->banner_uuid)),				  
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

	public function actionpushList()
	{
		$data = array();		
						
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  isset($this->data['order'][0])?$this->data['order'][0]:''   :'';	
						
		$sortby = "date_created"; $sort = 'DESC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = intval($page)/intval($length);				
		$criteria=new CDbCriteria();
		$criteria->condition="provider=:provider";
		$criteria->params = array(':provider'=> 'firebase');

		if(!empty($search)){
			$criteria->addSearchCondition('title', $search );
			$criteria->addSearchCondition('body', $search );
		}		
				
		$criteria->order = "$sortby $sort";
		$count = AR_push::model()->count($criteria); 
		
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);

		if($length>0){
           $pages->applyLimit($criteria);        
		}
		
        $models = AR_push::model()->findAll($criteria);
        if($models){
        	foreach ($models as $item) {        	
				
				$photo = CMedia::getImage($item->image,$item->path,'@thumbnail',
		         CommonUtility::getPlaceholderPhoto('customer'));

				 $platform =  $item->platform=='android' ? '<div class="badge badge-info">'.t($item->platform).'</div>' : '<div class="badge badge-warning">'.t($item->platform).'</div>';
				 
        		$data[]=array(				 
				  'push_uuid'=>$item->push_uuid,
			      'title'=>$item->title,
				  'body'=>$item->body,
				  'image'=> !empty($item->image)? '<img class="img-60" src="'.$photo.'">' :'<span class="badge badge-warning">'.t("No image").'</span>',
				  'channel_device_id'=>'<div class="d-inline-block text-truncate" style="max-width: 150px;">'.$item->channel_device_id.'</div>'.$platform,
				  'status'=>$item->status=="process"? '<span class="badge badge-success">'.$item->status.'</span>' : '<span class="badge badge-primary">'.$item->status.'</span>',     
				  'date_created'=>Date_Formatter::dateTime($item->date_created),
				  'update_url'=>Yii::app()->createUrl("/marketing/notification_update/",array('id'=>$item->push_uuid)),
        		  'delete_url'=>Yii::app()->createUrl("/marketing/notification_delete/",array('id'=>$item->push_uuid)),				  
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

		$criteria->condition = "merchant_id=:merchant_id";
		$criteria->params = [
			':merchant_id'=>0
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
				  'view_url'=>Yii::app()->createUrl("/driver/overview/",array('id'=>$item->driver_uuid)),
				  'update_url'=>Yii::app()->createUrl("/driver/update/",array('id'=>$item->driver_uuid)),
        		  'delete_url'=>Yii::app()->createUrl("/driver/delete/",array('id'=>$item->driver_uuid)),				  
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
		$criteria->addCondition("driver_id=0 AND merchant_id=0");

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
				  'update_url'=>Yii::app()->createUrl("/driver/update_car/",array('id'=>$item->vehicle_uuid)),
        		  'delete_url'=>Yii::app()->createUrl("/driver/delete_car/",array('id'=>$item->vehicle_uuid)),				  
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

	public function actionset_car_status()
	{
		try {
			
			$id = Yii::app()->input->post('id'); 
			$status = Yii::app()->input->post('status'); 
			$model = AR_driver_vehicle::model()->find("vehicle_uuid=:vehicle_uuid",['vehicle_uuid'=>$id]);
			if($model){
				$model->active = $status=="active"?1:0;
				if($model->save()){
					$this->code = 1; $this->msg = "ok";
				} else $this->msg = t(Helper_failed_update);
			} else $this->msg = t(HELPER_RECORD_NOT_FOUND);

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
			$merchant_id = isset($this->data['merchant_id'])?$this->data['merchant_id']:0;	
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
			//$criteria->addCondition("driver_id=0");
			$criteria->addCondition("driver_id=0 AND merchant_id=0");
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
				':merchant_id'=>0
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
				$this->responseSelect2($data);
			} else $this->msg = t(HELPER_NO_RESULTS);			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());						
		}		
		$this->responseJson();
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
					$zone = CDriver::getZone($model->zone_id);
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
						
		$sortby = "created_at"; $sort = 'DESC';
		
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
		} else $criteria->condition = "driver.merchant_id <= 0";		
		$criteria->order = "$sortby $sort";				
		
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
				  'client_id'=>CHtml::link($item->customer_fullname, $this->createAbsoluteUrl('buyer/customer_update',array('id'=>$item->customer_id))),
				  'review_text'=>t('<h6>[review] <span class="badge ml-2 post [status]">[status_title]</span></h6>',[
					'[review]'=>$item->review_text,
					'[status]'=>$item->status,
					'[status_title]'=>t($item->status),
				  ]),
				  'rating'=>'<label class="badge btn-green">'.$item->rating.' <i class="zmdi zmdi-star"></i> </label>',
				  'update_url'=>Yii::app()->createUrl("/driver/review_update/",array('id'=>$item->review_id)),
        		  'delete_url'=>Yii::app()->createUrl("/driver/review_delete/",array('id'=>$item->review_id)),				  
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
					'order_id'=>CHtml::link($item->order_id, $this->createAbsoluteUrl('order/view',array('order_uuid'=>$item->order_uuid))),
					'merchant_id'=>CHtml::link($item->restaurant_name, $this->createAbsoluteUrl('vendor/edit',array('id'=>$item->merchant_id))),
					'client_id'=>CHtml::link($item->customer_name, $this->createAbsoluteUrl('buyer/customer_update',array('id'=>$item->client_id))),
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
					'order_id'=>CHtml::link($item->order_id, $this->createAbsoluteUrl('order/view',array('order_uuid'=>$item->order_uuid))),
					'merchant_id'=>CHtml::link($item->restaurant_name, $this->createAbsoluteUrl('vendor/edit',array('id'=>$item->merchant_id))),
					'client_id'=>CHtml::link($item->customer_name, $this->createAbsoluteUrl('buyer/customer_update',array('id'=>$item->client_id))),
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
		   and driver_id IN (
			   select driver_id from {{driver}} where status='active'
		   )
		) as drivers
		";

		$criteria->condition="merchant_id=0";

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
				  'update_url'=>Yii::app()->createUrl("/driver/group_update/",array('id'=>$item->group_uuid)),
        		  'delete_url'=>Yii::app()->createUrl("/driver/group_delete/",array('id'=>$item->group_uuid)),				  
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
	
	public function actiongetgrouplist()
	{
		try {

			$data = CommonUtility::getDataToDropDown("{{driver_group}}","group_id","group_name",
			"WHERE merchant_id=0","order by group_name asc");
			$this->code = 1;
			$this->msg = "OK";
			$this->details = $data;
			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();	
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
				
				$end_shift_url = Yii::app()->createAbsoluteUrl("/driver/endshift",[
					'id'=>$item->schedule_uuid
				]);
				$delete_shift_url = Yii::app()->createAbsoluteUrl("/driver/deleteshift",[
					'id'=>$item->schedule_uuid
				]);
				$label = t("End Shift");


$buttons = <<<HTML
<a href="$end_shift_url" class="btn btn-primary">$label</a>
HTML;

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

	public function actionbankdepositlist()
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

		// $criteria->condition="deposit_type=:deposit_type";
		// $criteria->params = ['deposit_type'=>'order'];
		$criteria->addInCondition('deposit_type',[
			'order','wallet_loading'
		]);

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
		if($length>0){
           $pages->applyLimit($criteria); 
		}
										
        if($models = AR_bank_deposit::model()->findAll($criteria)){			
			foreach ($models as $item) {
			
				$exchange_rate = $item->exchange_rate_merchant_to_admin>0?$item->exchange_rate_merchant_to_admin:1;				
				$amount = Price_Formatter::formatNumber( ($item->amount*$exchange_rate) );

				$link = CMedia::getImage($item->proof_image,$item->path);			 
				$order_link = Yii::app()->CreateUrl("/order/view/",[
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
					'deposit_type'=>t($item->deposit_type),
					'transaction_ref_id'=>$order_ref,
					'account_name'=>$item->account_name,
					'amount'=>$amount,
					'reference_number'=>$item->reference_number,
					'view_url'=>Yii::app()->createUrl("/payment_gateway/bank_deposit_view/",array('id'=>$item->deposit_uuid)),
					'delete_url'=>Yii::app()->createUrl("/payment_gateway/bank_deposit_delete/",array('id'=>$item->deposit_uuid)),
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
						
			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';			
			$printer_id = isset($this->data['printer_id'])?$this->data['printer_id']:'';			
			
			// $model = AR_printer::model()->find("merchant_id=:merchant_id AND printer_id=:printer_id",[
            //     ":merchant_id"=>0,
            //     ':printer_id'=>intval($printer_id)
            // ]);
			$model = AR_printer::model()->find("printer_id=:printer_id",[               
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
				$merchant_id = COrders::getMerchantId($order_uuid);

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

				$order['order_info']['order_table_data'] = $order_table_data;
								
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

	public function actioninvoicebankdepositlist()
	{
		$data = array(); 

		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')  :'';	
		
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
			select invoice_uuid from {{invoice}}
			where invoice_number=a.transaction_ref_id
		) as invoice_uuid
		";

		$criteria->condition="deposit_type=:deposit_type";
		$criteria->params = ['deposit_type'=>'invoice'];

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
		if($length>0){
           $pages->applyLimit($criteria); 
		}
								
        if($models = AR_bank_deposit::model()->findAll($criteria)){

			//$price_format = CMulticurrency::getAllCurrency();			

			foreach ($models as $item) {

				// if($price_format){
				// 	if(isset($price_format[$item->base_currency_code])){
				// 		Price_Formatter::$number_format = $price_format[$item->base_currency_code];
				// 	}						
				// }

				$link = CMedia::getImage($item->proof_image,$item->path);			 
				$order_link = Yii::app()->CreateUrl("/invoice/view/",[
					'invoice_uuid'=>$item->invoice_uuid
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
					'amount'=>Price_Formatter::formatNumber(($item->amount*$item->exchange_rate_merchant_to_admin)),
					'reference_number'=>$item->reference_number,
					'view_url'=>Yii::app()->createUrl("/invoice/bank_deposit_view/",array('id'=>$item->deposit_uuid)),
					'delete_url'=>Yii::app()->createUrl("/invoice/bank_deposit_delete/",array('id'=>$item->deposit_uuid)),
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
		$filter_id = isset($this->data['ref_id'])?$this->data['ref_id']:'';		
		
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
		c.table_name, d.restaurant_name
		";
		$criteria->join='
		LEFT JOIN {{client}} b on  a.client_id = b.client_id 
		LEFT JOIN {{table_tables}} c on  a.table_id = c.table_id 
		LEFT JOIN {{merchant}} d on  a.merchant_id = d.merchant_id 
		';		

		if($filter_id>0){
			$criteria->addCondition("a.client_id=:client_id");			
			$criteria->params = [				
				':client_id'=>$filter_id
			];
		}

		if(!empty($date_start) && !empty($date_end)){
			$criteria->addBetweenCondition("DATE_FORMAT(reservation_date,'%Y-%m-%d')", $date_start , $date_end );
		} 
		
		if(!empty($search)){
			$criteria->addSearchCondition('a.reservation_id', $search);
		}
				
		$data = [];		
		$status_list = AttributesTools::bookingStatus();
		
		$criteria->order = "$sortby $sort";
		$count = AR_table_reservation::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
		if($length>0){
           $pages->applyLimit($criteria);        
		}
			
        $model = AR_table_reservation::model()->findAll($criteria);		
        if($model){
        	foreach ($model as $items) {
        		
        		$edit = Yii::app()->CreateUrl("/reservation/update_reservation",[
					'id'=>$items->reservation_uuid
				]);
				$overview = Yii::app()->CreateUrl("/reservation/reservation_overview",[
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
					$status_action_list.='<a class="dropdown-item" href="'. Yii::app()->CreateUrl("/reservation/update_status",
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
					'merchant_id'=>$items->restaurant_name,
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
				
				$view = Yii::app()->CreateUrl("/printer/print_view",['id'=>$items->id]);

				$badge = 'badge-primary';				
				if($items->status=="process"){
					$badge = 'badge-success';					
				} else {
					$badge = 'badge-danger';					
				}
				
$action = <<<HTML
<div class="btn-group btn-group-actions" role="group">
  <a href="$view" target="_blank" class="btn btn-light tool_tips" data-toggle="tooltip" data-placement="top" title="" data-original-title="Update">
   <i class="zmdi zmdi-eye"></i>
  </a>  
  <a href="javascript:;" data-id="$items->id" class="btn btn-light datatables_delete tool_tips" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete">
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
			'merchant_id'=>0
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
		WHERE merchant_id = 0","ORDER BY zone_name ASC");
		
        if($models){
        	foreach ($models as $item) {  				
        		$data[]=array(				 
				  'shift_id'=>$item->shift_id,
				  'shift_uuid'=>$item->shift_uuid,				  
				  'zone_id'=>isset($zone_list[$item->zone_id])?$zone_list[$item->zone_id]:$item->zone_id,				  
				  'time_start'=>Date_Formatter::dateTime($item->time_start),
				  'time_end'=>Date_Formatter::dateTime($item->time_end),
				  'max_allow_slot'=>$item->max_allow_slot>0?$item->max_allow_slot:t("unlimited"),
				  'update_url'=>Yii::app()->createUrl("/driver/shift_update/",array('id'=>$item->shift_uuid)),
        		  'delete_url'=>Yii::app()->createUrl("/driver/shift_delete/",array('id'=>$item->shift_uuid)),				  
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

	public function actiongetZoneList()
	{
		try {
			
			$zone_list = CommonUtility::getDataToDropDown("{{zones}}",'zone_id','zone_name',"
		    WHERE merchant_id = 0","ORDER BY zone_name ASC"); 
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
				$transaction_amount = Price_Formatter::formatNumber( ($item->transaction_amount*$item->exchange_rate_merchant_to_admin) );        						
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
        		  'running_balance'=>Price_Formatter::formatNumber( ($item->running_balance*$item->exchange_rate_merchant_to_admin) ),
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

	public function actiondriverWalletAdjustment()
	{
		try {

			$transaction_description = isset($this->data['transaction_description'])?$this->data['transaction_description']:'';
			$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';
			$transaction_amount = isset($this->data['transaction_amount'])?$this->data['transaction_amount']:0;

			$base_currency = Price_Formatter::$number_format['currency_code'];		
			$driver_currency = 	$base_currency;
			$exchange_rate_merchant_to_admin = 1; $exchange_rate_admin_to_merchant=1;

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
            $multicurrency_enabled = $multicurrency_enabled==1?true:false;					

			$ref_id = isset($this->data['ref_id'])?$this->data['ref_id']:'';			
			$driver_data = CDriver::getDriverByUUID($ref_id);

			if($multicurrency_enabled && !empty($driver_data->default_currency)){				
				if($driver_data->default_currency!=$base_currency){
					$driver_currency = $driver_data->default_currency;
					$exchange_rate_merchant_to_admin = CMulticurrency::getExchangeRate($driver_currency,$base_currency);
				    $exchange_rate_admin_to_merchant = CMulticurrency::getExchangeRate($base_currency,$driver_currency);
				}
			}
			
			$params = array(
			  'transaction_description'=>$transaction_description,			  
			  'transaction_type'=>$transaction_type,
			  'transaction_amount'=>floatval($transaction_amount),
			  'meta_name'=>"adjustment",
			  'meta_value'=>CommonUtility::createUUID("{{admin_meta}}",'meta_value'),
			  'merchant_base_currency'=>$driver_currency,
			  'admin_base_currency'=>$base_currency,
			  'exchange_rate_merchant_to_admin'=>$exchange_rate_merchant_to_admin,
			  'exchange_rate_admin_to_merchant'=>$exchange_rate_admin_to_merchant,
			);			
		    			
			$driver_id = $driver_data->driver_id;									
			$card_id = CWallet::createCard( Yii::app()->params->account_type['driver'] ,$driver_id);		
			CWallet::inserTransactions($card_id,$params);

			$this->code = 1; $this->msg = t("Successful");

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

	public function actioncashoutSummary()
	{
		try {
			
			$data = CPayouts::payoutSummary('cashout',0,true);
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
		b.driver_id, b.merchant_id, concat(b.first_name,' ',b.last_name) as driver_name , b.photo as logo , b.path";
		
		$criteria->join="LEFT JOIN {{driver}} b on a.card_id = 
		(
		 select card_id from {{wallet_cards}}
		 where account_type=".q(Yii::app()->params->account_type['driver'])." and account_id=b.driver_id
		)
		";		
		
		$criteria->condition="transaction_type=:transaction_type AND b.merchant_id=0";
		$criteria->params = array(		 
		 ':transaction_type'=>'cashout'
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
			':merchant_id'=>0
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
					'view_url'=>Yii::app()->createUrl("/driver/collect_transactions/",array('id'=>$item->collection_uuid)),
					'delete_url'=>Yii::app()->createUrl("/driver/collect_cash_void/",array('id'=>$item->collection_uuid)),				  			
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
						merchant_id=0 and driver_id = a.driver_id 
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
            
            $options = OptionsTools::find(['driver_allowed_number_task','driver_time_allowed_accept_order']);
            $allowed_number_task = isset($options['driver_allowed_number_task'])?$options['driver_allowed_number_task']:0;
			$time_allowed_accept_order = isset($options['driver_time_allowed_accept_order'])?$options['driver_time_allowed_accept_order']:1;
			$time_allowed_accept_order = $time_allowed_accept_order>0?$time_allowed_accept_order:1;
			$dateTime = new DateTime(); 
            $dateTime->modify("+$time_allowed_accept_order minutes");

			$order->scenario = "assign_order";
			$order->on_demand_availability = $on_demand_availability;
            $order->driver_id = intval($driver_id);
            $order->delivered_old_status = $order->delivery_status;
            $order->delivery_status = $status_assigned;
            $order->change_by = Yii::app()->user->first_name;
            $order->date_now = date("Y-m-d");
            $order->allowed_number_task = intval($allowed_number_task);		
			$order->assigned_at = CommonUtility::dateNow();	
			$order->assigned_expired_at = $dateTime->format('Y-m-d H:i:s');

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
            } else {				
				$this->msg = CommonUtility::parseModelErrorToString($order->getErrors());
			}

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

	public function actionupdateOrderStatus()
	{
		try {
						
			$this->data = Yii::app()->input->xssClean(json_decode(file_get_contents('php://input'), true));			
			$uuid = isset($this->data['uuid'])?$this->data['uuid']:'';
			$order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';
			$rejetion_reason = isset($this->data['reason'])?$this->data['reason']:'';
							
			$status = AOrders::getOrderButtonStatus($uuid);
			$do_actions = AOrders::getOrderButtonActions($uuid);

			$cancel2 = AR_admin_meta::getValue('status_delivery_cancelled');			
            $cancel_status2 = isset($cancel2['meta_value'])?$cancel2['meta_value']:'cancelled';

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
				if($model->whento_deliver=="schedule"){
					$scheduled_delivery_time = $model->delivery_date." ".$model->delivery_time;
					$preparationStartTime = CommonUtility::calculatePreparationStartTime($scheduled_delivery_time,($model->preparation_time_estimation+$model->delivery_time_estimation));					
					$currentTime = time();
					if ($currentTime < $preparationStartTime) {						
						$model->order_accepted_at = date("Y-m-d G:i:s", $preparationStartTime);
					} else $model->order_accepted_at = CommonUtility::dateNowAdd();
				} else {
					$model->order_accepted_at = CommonUtility::dateNowAdd();				
				}				
			}
			if($status==$tracking_status_delivering){
				$model->pickup_time = CommonUtility::dateNowAdd();				
			}
			
			
			$model->status = $status;			
			$model->remarks = $rejetion_reason;
			$model->change_by = Yii::app()->user->first_name;

			if($do_actions=="reject_form"){
				$model->delivery_status  = $cancel_status2;
			}
			
			if(!empty($rejetion_reason)){
			   	COrders::savedMeta($model->order_id,'rejetion_reason',$rejetion_reason);
			}
			if($model->save()){
			   $this->code = 1;
			   $this->msg = t("Status Updated");						   
			} else $this->msg = CommonUtility::parseError( $model->getErrors());
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}		
		$this->responseJson();
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

	public function actiondriverearnings()
	{
		$data = array();
		
		$page = isset($this->data['start'])?$this->data['start']:0;	
	    $length = isset($this->data['length'])?$this->data['length']:0;	
	    $draw = isset($this->data['draw'])?$this->data['draw']:0;	
	    $search = isset($this->data['search'])?$this->data['search']['value']:'';	
	    $columns = isset($this->data['columns'])?$this->data['columns']:'';
	    $order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'' )  :'';	
	    $filter = isset($this->data['filter'])?$this->data['filter']:'';
	    
	    $date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
	    $date_end = isset($this->data['date_end'])?$this->data['date_end']:'';
		$date_start = !empty($date_start)?$date_start:date("Y-m-d");
		$date_end = !empty($date_end)?$date_end:date("Y-m-d");
	    
		$sortby = "a.driver_id"; $sort = 'ASC';

		if(is_array($order) && count($order)>=1){
	        if(array_key_exists($order['column'],(array)$columns)){			
	            $sort = $order['dir'];
	            $sortby = $columns[$order['column']]['data'];
	        }
	    }

		if($page>0){
			$page = intval($page)/intval($length);	
		}

	
		$delivery_status = ['payout_delivery_fee','payout_commission','payout_fixed','payout_fixed_and_commission'];		
		$payout_tip = ['payout_tip'];
		$payout_incentives = ['payout_incentives'];
		$adjustment = ['adjustment'];

		$delivery_status = CommonUtility::arrayToQueryParameters($delivery_status);
		$payout_tip = CommonUtility::arrayToQueryParameters($payout_tip);
		$payout_incentives = CommonUtility::arrayToQueryParameters($payout_incentives);
		$adjustment = CommonUtility::arrayToQueryParameters($adjustment);

		$account_type = Yii::app()->params->account_type['driver'];
		$transaction_type = "credit";
		$transaction_type_debit = "debit";

		// $total_credit = CDriver::EarningAdjustment($card_id,$date_start,$date_end,['adjustment']);
		// $total_debit = CDriver::EarningAdjustment($card_id,$date_start,$date_end,['adjustment'],'debit');			
		// $total_adjustment = $total_credit - $total_debit;

		$criteria=new CDbCriteria();
		$criteria->alias="a";
		$criteria->select = "
		a.driver_id, a.first_name, a.last_name,
		(
			 SELECT sum(transaction_amount) as total
			 FROM {{wallet_transactions}} b
			 WHERE b.card_id = (
				select card_id from {{wallet_cards}}
				where account_type=".q($account_type)."
				and account_id = a.driver_id
				AND DATE(transaction_date) BETWEEN  ".q($date_start)." AND ".q($date_end)."
				AND transaction_type= ".q($transaction_type)."
				AND transaction_id IN (
					select transaction_id FROM {{wallet_transactions_meta}}
                    where transaction_id = b.transaction_id
                    and meta_name IN (".$delivery_status.")
				)
			 )		 
		) as delivery_pay,

		(
			SELECT sum(transaction_amount) as total
			FROM {{wallet_transactions}} b
			WHERE b.card_id = (
			   select card_id from {{wallet_cards}}
			   where account_type=".q($account_type)."
			   and account_id = a.driver_id
			   AND DATE(transaction_date) BETWEEN  ".q($date_start)." AND ".q($date_end)."
			   AND transaction_type= ".q($transaction_type)."
			   AND transaction_id IN (
				   select transaction_id FROM {{wallet_transactions_meta}}
				   where transaction_id = b.transaction_id
				   and meta_name IN (".$payout_tip.")
			   )
			)		 
	   ) as total_tips,

	   (
		SELECT sum(transaction_amount) as total
		FROM {{wallet_transactions}} b
		WHERE b.card_id = (
		   select card_id from {{wallet_cards}}
		   where account_type=".q($account_type)."
		   and account_id = a.driver_id
		   AND DATE(transaction_date) BETWEEN  ".q($date_start)." AND ".q($date_end)."
		   AND transaction_type= ".q($transaction_type)."
		   AND transaction_id IN (
			   select transaction_id FROM {{wallet_transactions_meta}}
			   where transaction_id = b.transaction_id
			   and meta_name IN (".$payout_incentives.")
		   )
		)		 
        ) as total_incentives,

		(
			SELECT sum(transaction_amount) as total
			FROM {{wallet_transactions}} b
			WHERE b.card_id = (
			   select card_id from {{wallet_cards}}
			   where account_type=".q($account_type)."
			   and account_id = a.driver_id
			   AND DATE(transaction_date) BETWEEN  ".q($date_start)." AND ".q($date_end)."
			   AND transaction_type= ".q($transaction_type)."
			   AND transaction_id IN (
				   select transaction_id FROM {{wallet_transactions_meta}}
				   where transaction_id = b.transaction_id
				   and meta_name IN (".$adjustment.")
			   )
			)		 
		) as total_credit,

		(
			SELECT sum(transaction_amount) as total
			FROM {{wallet_transactions}} b
			WHERE b.card_id = (
			   select card_id from {{wallet_cards}}
			   where account_type=".q($account_type)."
			   and account_id = a.driver_id
			   AND DATE(transaction_date) BETWEEN  ".q($date_start)." AND ".q($date_end)."
			   AND transaction_type= ".q($transaction_type_debit)."
			   AND transaction_id IN (
				   select transaction_id FROM {{wallet_transactions_meta}}
				   where transaction_id = b.transaction_id
				   and meta_name IN (".$adjustment.")
			   )
			)		 
		) as total_debit

		";
		$criteria->order = "$sortby $sort";	    
	    $count = AR_driver::model()->count($criteria); 
	    $pages=new CPagination( intval($count) );
	    $pages->setCurrentPage( intval($page) );        
	    $pages->pageSize = intval($length);
	    $pages->applyLimit($criteria);     	    
		
		if($model = AR_driver::model()->findAll($criteria)){			
			foreach ($model as $item) {
				$total_adjustment = floatval($item->total_credit) - floatval($item->total_debit);
				$balance = floatval($item->delivery_pay) + floatval($item->total_tips) + floatval($item->total_incentives)  + floatval($total_adjustment);
				$data[] = [
					'first_name'=>"$item->first_name $item->last_name",
					'delivery_pay'=>$item->delivery_pay>0?Price_Formatter::formatNumber($item->delivery_pay):'',
					'tips'=>$item->total_tips>0?Price_Formatter::formatNumber($item->total_tips):'',
					'incentives'=>$item->total_incentives>0?Price_Formatter::formatNumber($item->total_incentives):'',
					'adjustment'=>$total_adjustment>0?Price_Formatter::formatNumber($total_adjustment):'',
					'total_earnings'=>$balance>0?Price_Formatter::formatNumber($balance):'',
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

	public function actiondriverreportwalletbalance()
	{

		$data = array();
		
		$page = isset($this->data['start'])?$this->data['start']:0;	
	    $length = isset($this->data['length'])?$this->data['length']:0;	
	    $draw = isset($this->data['draw'])?$this->data['draw']:0;	
	    $search = isset($this->data['search'])?$this->data['search']['value']:'';	
	    $columns = isset($this->data['columns'])?$this->data['columns']:'';
	    $order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'' )  :'';	
	    $filter = isset($this->data['filter'])?$this->data['filter']:'';
	    
	    $date_start = isset($this->data['date_start'])?$this->data['date_start']:'';
	    $date_end = isset($this->data['date_end'])?$this->data['date_end']:'';
		$date_start = !empty($date_start)?$date_start:date("Y-m-d");
		$date_end = !empty($date_end)?$date_end:date("Y-m-d");
	    
		$sortby = "a.driver_id"; $sort = 'ASC';

		if(is_array($order) && count($order)>=1){
	        if(array_key_exists($order['column'],(array)$columns)){			
	            $sort = $order['dir'];
	            $sortby = $columns[$order['column']]['data'];
	        }
	    }

		if($page>0){
			$page = intval($page)/intval($length);	
		}

		$account_type = Yii::app()->params->account_type['driver'];

		$criteria=new CDbCriteria();
		$criteria->alias="a";
		$criteria->select = "
		a.driver_id, a.first_name, a.last_name,
		(
			 SELECT running_balance
			 FROM {{wallet_transactions}} b
			 WHERE b.card_id = (
				select card_id from {{wallet_cards}}
				where account_type=".q($account_type)."
				and account_id = a.driver_id				
			 )		 
			 order by transaction_id desc
			 limit 0,1
		) as wallet_balance
		";

		$criteria->order = "$sortby $sort";	    
	    $count = AR_driver::model()->count($criteria); 
	    $pages=new CPagination( intval($count) );
	    $pages->setCurrentPage( intval($page) );        
	    $pages->pageSize = intval($length);
	    $pages->applyLimit($criteria);     	    		
		if($model = AR_driver::model()->findAll($criteria)){			
			// dump($criteria);
			// dump($model);die();
			foreach ($model as $item) {				
				$wallet_balance =  $item->wallet_balance>0?Price_Formatter::formatNumber($item->wallet_balance):'<span class="text-danger">'.Price_Formatter::formatNumber($item->wallet_balance).'</span>';
				$data[] = [
					'first_name'=>"$item->first_name $item->last_name",
					'wallet_balance'=>$wallet_balance
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

	public function actiongetMenu()
	{
		try {

			$role_id = Yii::app()->user->role_id;

			$cacheKey  = 'cache_search_menu_data_'.Yii::app()->user->id;
			$items = Yii::app()->cache->get($cacheKey);

			if ($items === false) {
			   $items = AttributesTools::getSearchBarMenu("admin",$role_id);
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

	public function actionexchangerate()
	{
		$data = array();		
						
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'' )  :'';	
						
		$sortby = "date_created"; $sort = 'DESC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
						
		$page = $page>0? intval($page)/intval($length) :0;
		$criteria=new CDbCriteria();		

		if(!empty($search)){
			$criteria->addSearchCondition('currency_code', $search );
			$criteria->addSearchCondition('base_currency', $search , true, "OR" );			
			$criteria->addSearchCondition('provider', $search , true, "OR" );			
		}		
									
		$criteria->order = "$sortby $sort";
		$count = AR_currency_exchangerate::model()->count($criteria); 		
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        
				
        $models = AR_currency_exchangerate::model()->findAll($criteria);		
		
        if($models){
        	foreach ($models as $item) {        									
        		$data[]=array(				 
				  'id'=>$item->id,
				  'provider'=>$item->provider,
				  'base_currency'=>$item->base_currency,
				  'currency_code'=>$item->currency_code,
				  'exchange_rate'=>$item->exchange_rate,
				  'date_created'=>Date_Formatter::dateTime($item->date_created),				  
				  'update_url'=>Yii::app()->createUrl("/multicurrency/update/",array('id'=>$item->id)),
        		  'delete_url'=>Yii::app()->createUrl("/multicurrency/delete/",array('id'=>$item->id)),				  
				);
        	}        	
        }
        
         $datatables = array(
		   'page'=>$page,
		  'draw'=>intval($draw),
		  'recordsTotal'=>intval($count),
		  'recordsFiltered'=>intval($count),
		  'data'=>$data
		);				
		$this->responseTable($datatables);
	}		

	public function actionuserRewardsPoints()
	{
		$data = array();
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
				
		$sortby = "transaction_id"; $sort = 'DESC';

		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = $page>0? intval($page)/intval($length) :0;
		$criteria=new CDbCriteria();	

		$criteria->alias = "a";		
		$criteria->select ="
		a.card_id,b.account_id,c.first_name,c.last_name,
          SUM(
			CASE WHEN a.transaction_type NOT IN  ('points_redeemed','debit') 
            THEN transaction_amount ELSE -transaction_amount END
		  ) AS total_earning
		";

		$criteria->join='
		LEFT JOIN {{wallet_cards}} b on  a.card_id = b.card_id 	
		
		left JOIN (
			SELECT client_id,first_name,last_name FROM {{client}} 
		) c
		on b.account_id = c.client_id
		';		
		
		$criteria->condition = "b.account_type='customer_points'";

		$criteria->group = 'card_id';
		
		$criteria->order = "$sortby $sort";
		$count = AR_wallet_transactions::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria); 		
		
        $models = AR_wallet_transactions::model()->findAll($criteria);
		if($models){			
			foreach ($models as $item) {		

				$view = Yii::app()->createUrl('points/transactions',array(
					'card_id'=>$item->card_id
				));				

$actions_html = <<<HTML
<div class="btn-group btn-group-actions" role="group">
	<a href="$view" class="btn btn-light tool_tips"><i class="zmdi zmdi-eye"></i></a>	
</div>
HTML;

				$data[] = [					
					'card_id'=>ucwords("$item->first_name $item->last_name"),
					'transaction_amount'=>'<b>'.Price_Formatter::convertToRaw($item->total_earning,0).'</b>',
					'transaction_type'=>$actions_html
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

	public function actionPointsTransactionLogs()
	{
		$data = array();
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
				
		$card_id = isset($this->data['ref_id'])? intval($this->data['ref_id']) :'';

		$account_type = 'customer_points';
		try {
			$cart_data = CWallet::getCard($card_id);			
			$account_type = $cart_data->account_type;
		} catch (Exception $e) {}
		
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

				$transaction_amount = 0; 
				switch ($item->transaction_type) {					
        			case "points_redeemed":        			
					case "debit":  
						   if($account_type=="digital_wallet"){
							   $transaction_amount = '<span class="text-danger">'."-".Price_Formatter::formatNumber($item->transaction_amount).'</span>';
						   } else $transaction_amount = '<span class="text-danger">'."-".Price_Formatter::convertToRaw($item->transaction_amount,0).'</span>';        				   
        				break;      			
					default:
					       if($account_type=="digital_wallet"){
							   $transaction_amount =  '<span class="text-success"><b>'."+".Price_Formatter::formatNumber($item->transaction_amount).'</b></span>';
						   } else $transaction_amount =  '<span class="text-success"><b>'."+".Price_Formatter::convertToRaw($item->transaction_amount,0).'</b></span>';					       
					    break;      			
        		} 

				if($account_type=="digital_wallet"){
					$balance = Price_Formatter::formatNumber($item->running_balance);
				} else $balance = Price_Formatter::convertToRaw($item->running_balance,0);				

				$data[] = [
					'transaction_id'=>$item->transaction_id,
					'transaction_date'=>Date_Formatter::dateTime($item->transaction_date),					
					'transaction_description'=>$description,
					'transaction_amount'=>$transaction_amount,
					'running_balance'=>'<b>'.$balance.'</b>',
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

	public function actionpointsadjustment()
	{
		try {

			$card_id = isset($this->data['ref_id'])? intval($this->data['ref_id']) :0;
			$transaction_amount = isset($this->data['transaction_amount'])? floatval($this->data['transaction_amount']) :0;
			$transaction_description = isset($this->data['transaction_description'])?$this->data['transaction_description']:'';
			$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';

			if($transaction_amount<=0){
				$this->msg = t("Invalid amount");
				$this->responseJson();
			}

			$params = [
				'transaction_description'=>$transaction_description,
				'transaction_type'=>$transaction_type=="credit"?'points_earned':'points_redeemed',
				'transaction_amount'=>floatval($transaction_amount),
				'status'=>'paid',
			];			
			$resp = CWallet::inserTransactions($card_id,$params);
			$this->code = 1;
			$this->msg = t("Successful");
			$this->details = $resp;
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}		
		$this->responseJson();
	}

	public function actionwalletadjustment()
	{
		try {
			
			$card_id = isset($this->data['ref_id'])? intval($this->data['ref_id']) :0;
			$transaction_amount = isset($this->data['transaction_amount'])? floatval($this->data['transaction_amount']) :0;
			$transaction_description = isset($this->data['transaction_description'])?$this->data['transaction_description']:'';
			$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';

			if($transaction_amount<=0){
				$this->msg = t("Invalid amount");
				$this->responseJson();
			}

			$params = [
				'transaction_description'=>$transaction_description,
				'transaction_type'=>$transaction_type,
				'transaction_amount'=>floatval($transaction_amount),
				'status'=>'paid',
			];						
			$resp = CWallet::inserTransactions($card_id,$params);
			$this->code = 1;
			$this->msg = t("Successful");
			$this->details = $resp;
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}		
		$this->responseJson();
	}

	public function actiongetpointsbalance()
	{
		try {

			$card_id = Yii::app()->input->post("card_id");		
			$return_format = Yii::app()->input->post("return_format");		
			$balance = CWallet::getBalance($card_id);	
			$this->code = 1; $this->msg = "Ok";
			
			$this->details = [
				'balance'=>$return_format=='money_format'? Price_Formatter::formatNumber($balance) :$balance
			];

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}		
		$this->responseJson();
	}

	public function actionallpointstransaction()
	{

		$data = array();
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
					
		$sortby = "transaction_id"; $sort = 'DESC';

		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = $page>0? intval($page)/intval($length) :0;
		$criteria=new CDbCriteria();				
		$criteria->alias = "a";				
		$criteria->select = "a.*, b.first_name, b.last_name";
		$criteria->join = "
		LEFT JOIN {{wallet_cards}} c 
			ON a.card_id = c.card_id 
			AND c.account_type = ".q(Yii::app()->params->account_type['customer_points'])."
		LEFT JOIN {{client}} b 
			ON c.account_id = b.client_id
		";
		$criteria->addInCondition("a.transaction_type", CPoints::transactionType());
		$criteria->order = "$sortby $sort";
		$count = AR_wallet_transactions::model()->count($criteria); 
		$pages=new CPagination(intval($count));
		$pages->setCurrentPage(intval($page));        
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

				$transaction_amount = 0; 
				switch ($item->transaction_type) {					
        			case "points_redeemed":        			
        				   $transaction_amount = '<span class="text-danger">'."-".Price_Formatter::convertToRaw($item->transaction_amount,0).'</span>';
        				break;      			
					default:
					       $transaction_amount =  '<span class="text-success"><b>'."+".Price_Formatter::convertToRaw($item->transaction_amount,0).'</b></span>';
					    break;      			
        		} 

				$data[] = [
					'transaction_id'=>$item->transaction_id,
					'transaction_date'=>Date_Formatter::dateTime($item->transaction_date),					
					'card_id'=>isset($item->first_name)? ( !empty($item->first_name)? $item->first_name." ".$item->last_name :t("Not found")) :t("Not found"),
					'transaction_description'=>$description,
					'transaction_amount'=>$transaction_amount,
					'running_balance'=>'<b>'.Price_Formatter::convertToRaw($item->running_balance,0).'</b>',
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

	public function actionrequestNewOrder()
	{
		try {
			
			$criteria = new CDbCriteria;	
			$criteria->alias = "a";
			$criteria->select = "a.order_id, concat(first_name,' ',last_name) as customer_name";						
			$criteria->addCondition("is_view=0 AND a.status!=:status AND DATE_FORMAT(a.date_created,'%Y-%m-%d')=:date_created");		
			$criteria->params = [
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

	public function actiondigitalWalletBonusList()
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
		$criteria->condition = "merchant_id=:merchant_id AND transaction_type=:transaction_type";
		$criteria->params = [
			':merchant_id'=>0,
			':transaction_type'=>CDigitalWallet::transactionName()
		];
		
		$criteria->order = "$sortby $sort";
		$count = AR_discount::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria); 				
        $models = AR_discount::model()->findAll($criteria);
		if($models){
			foreach ($models as $item) {								

				if($item->discount_type=="percentage"){
					$amount = '<b>'.t("{amount} %",[
						'{amount}'=>Price_Formatter::convertToRaw($item->amount,0)
					]).'</b>';
				} else $amount = '<b>'.Price_Formatter::formatNumber($item->amount).'</b>';

				$checkbox = Yii::app()->controller->renderPartial('/attributes/html_checkbox',array(
					'id'=>"banner[$item->discount_uuid]",
					'check'=>$item->status==1?true:false,
					'value'=>$item->discount_uuid,
					'label'=>'',		
					'class'=>'set_status'
				),true);
								
				$data[] = [				
					'discount_id'=>$item->discount_id,						
					'status'=>$checkbox,
					'title'=>$item->title,										
					'amount'=>$amount,
					'minimum_amount'=>'<b>'.Price_Formatter::formatNumber($item->minimum_amount).'</b>',
					'expiration_date'=>Date_Formatter::date($item->start_date)." - ".Date_Formatter::date($item->expiration_date),
					'update_url'=>Yii::app()->createUrl("/digitalwallet/bunos_update/",array('id'=>$item->discount_uuid)),
        		    'delete_url'=>Yii::app()->createUrl("/digitalwallet/bunos_delete/",array('id'=>$item->discount_uuid)),					
				    'actions'=>"setBonusStatus",
					'id'=>$item->discount_uuid,
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

	public function actionsetBonusStatus()
	{
		try {
			
			$id = Yii::app()->input->post('id'); 
			$status = Yii::app()->input->post('status'); 
			$model = AR_discount::model()->find("discount_uuid=:discount_uuid",['discount_uuid'=>$id]);
			if($model){
				$model->status = $status=="active"?1:0;
				if($model->save()){
					$this->code = 1; $this->msg = "ok";
				} else $this->msg = t(Helper_failed_update);
			} else $this->msg = t(HELPER_RECORD_NOT_FOUND);

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());	
		}	
		$this->responseJson();	
	}

	public function actiondigitalWalletTransactions()
	{

		$data = array();
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
					
		$sortby = "transaction_id"; $sort = 'DESC';

		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = $page>0? intval($page)/intval($length) :0;
		$criteria=new CDbCriteria();		
		$criteria->alias = "a";				
		$criteria->select = "a.*,b.first_name,b.last_name";

		$criteria->join="LEFT JOIN {{client}} b on a.card_id = 
		(
		  select card_id from {{wallet_cards}}
		  where account_type=".q(Yii::app()->params->account_type['digital_wallet'])." and account_id=b.client_id
		)
		";

		$criteria->addInCondition("a.reference_id1",CDigitalWallet::transactionType());		

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

				$transaction_amount = 0; 
				switch ($item->transaction_type) {					
        			case "debit":        			
        				   $transaction_amount = '<span class="text-danger">'."-".Price_Formatter::formatNumber($item->transaction_amount).'</span>';
        				break;      			
					default:
					       $transaction_amount =  '<span class="text-success"><b>'."+".Price_Formatter::formatNumber($item->transaction_amount,0).'</b></span>';
					    break;      			
        		} 

				$data[] = [
					'transaction_id'=>$item->transaction_id,
					'transaction_date'=>Date_Formatter::dateTime($item->transaction_date),					
					'card_id'=>isset($item->first_name)? ( !empty($item->first_name)? $item->first_name." ".$item->last_name :t("Not found")) :t("Not found"),
					'transaction_description'=>$description,
					'transaction_amount'=>$transaction_amount,
					'running_balance'=>'<b>'.Price_Formatter::convertToRaw($item->running_balance,0).'</b>',
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

	public function actiondigitalwalletadjustment()
	{
		try {

			$card_id = 0;
			$client_id = isset($this->data['client_id'])? intval($this->data['client_id']) :0;
			$transaction_amount = isset($this->data['transaction_amount'])? floatval($this->data['transaction_amount']) :0;
			$transaction_description = isset($this->data['transaction_description'])?$this->data['transaction_description']:'';
			$transaction_type = isset($this->data['transaction_type'])?$this->data['transaction_type']:'';
			$base_currency = Price_Formatter::$number_format['currency_code'];
			$exchange_rate = 1;

			if($transaction_amount<=0){
				$this->msg = t("Invalid amount");
				$this->responseJson();
			}

			$card_id = CWallet::createCard( Yii::app()->params->account_type['digital_wallet'],$client_id);						

			$params = [
				'transaction_description'=>$transaction_description,
				'transaction_type'=>$transaction_type,
				'transaction_amount'=>floatval($transaction_amount),
				'status'=>'paid',
				'reference_id1'=>CDigitalWallet::transactionName(),
				'merchant_base_currency'=>$base_currency,
				'admin_base_currency'=>$base_currency,
				'meta_name'=>"adjustment",
				'meta_value'=>$card_id,
			];										
			$resp = CWallet::inserTransactions($card_id,$params);
			$this->code = 1;
			$this->msg = t("Successful");
			$this->details = $resp;
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}		
		$this->responseJson();
	}	

	public function actioncustomerbooking()
	{		
		$client_id = isset($this->data['ref_id'])?$this->data['ref_id']:0;
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
		$criteria->addCondition("a.client_id=:client_id");			
		$criteria->params = [			
			':client_id'=>$client_id
		];

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

				$special_request = $items->special_request;
				if(!empty($items->cancellation_reason)){
					$special_request.="<p class=\"text-danger\">";
					$special_request.=t("CANCELLATION NOTES = {cancellation_reason}",[
						'{cancellation_reason}'=>$items->cancellation_reason
					]);
					$special_request.="</p>";
				}

				$data[] = [
					'reservation_id'=>$items->reservation_id,
					'client_id'=>'<b>'.$items->full_name.'</b></b><span class="badge ml-2 post '.$badge.'">'.$booking_status.'</span>',
					'guest_number'=>$items->guest_number,
					'table_id'=>$items->table_name,
					'reservation_date'=>Date_Formatter::dateTime($items->reservation_date." ".$items->reservation_time),					
					'special_request'=>$special_request,					
					'status'=>'</b></b><span class="badge ml-2 post '.$badge.'">'.$booking_status.'</span>',
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

	public function actionpointsThresholds()
	{
		$data = array();
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
				
		$sortby = "transaction_id"; $sort = 'DESC';

		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = $page>0? intval($page)/intval($length) :0;
		$criteria=new CDbCriteria();			
		$criteria->condition = "meta_name=:meta_name";
		$criteria->params = [
			':meta_name'=>AttributesTools::pointsThresholds()
		];
		
		$criteria->order = "$sortby $sort";
		$count = AR_admin_meta::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria); 		
        $models = AR_admin_meta::model()->findAll($criteria);		
		if($models){	
			foreach ($models as $item) {
			

				$edit = Yii::app()->createAbsoluteUrl("/points/update_thresholds",[
					'id'=>$item->meta_id
				]);
				$delete = Yii::app()->createAbsoluteUrl("/points/delete_thresholds",[
					'id'=>$item->meta_id
				]);				



$buttons = <<<HTML
<div class="btn-group btn-group-actions" role="group">
 <a href="$edit"  class="btn btn-light tool_tips" data-toggle="tooltip" data-placement="top" >
	<i class="zmdi zmdi-border-color"></i>
</a>
 <a href="$delete"  class="btn btn-light tool_tips"><i class="zmdi zmdi-delete"></i></a> 
</div>
HTML;
	
	
				$data[] = [
					'meta_id'=>$item->meta_id,
					'meta_value'=>$item->meta_value,
					'meta_value1'=>$item->meta_value1,
					'meta_name'=>$buttons
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
			':merchant_id'=>0,
			':subcsribe_type'=>"website"
		];
		$criteria->order = "$sortby $sort";
		$count = AR_subscriber::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);

		if($length>0){
           $pages->applyLimit($criteria);        	
		}
		
        $models = AR_subscriber::model()->findAll($criteria);
		if($models){
			foreach ($models as $item) {				
				$data[] = [
					'id'=>$item->id,
					'email_address'=>$item->email_address,
					'ip_address'=>$item->ip_address,
					'delete_url'=>Yii::app()->createUrl("/buyer/subscriber_delete/",array('id'=>$item->id)),				  
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
							
			$this->data = Yii::app()->input->xssClean(json_decode(file_get_contents('php://input'), true));
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
					$kitchen_uuid = isset($order_info['merchant_uuid'])?$order_info['merchant_uuid']:'';
					$order_reference = isset($order_info['order_id'])?$order_info['order_id']:'';
					$whento_deliver = isset($order_info['whento_deliver'])?$order_info['whento_deliver']:'';
					$merchant_uuid = $kitchen_uuid;
					$merchant_id = isset($order_info['merchant_id'])?$order_info['merchant_id']:'';

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
				
				$exchange_rate = 1;
				$model_order = COrders::get($order_uuid);
				if($model_order->base_currency_code!=$model_order->admin_base_currency){
					$exchange_rate = $model_order->exchange_rate_merchant_to_admin>0?$model_order->exchange_rate_merchant_to_admin:1;
					Price_Formatter::init($model_order->admin_base_currency);
				 } else {
					Price_Formatter::init($model_order->admin_base_currency);
				}			 
				COrders::setExchangeRate($exchange_rate);
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

	public function actionsubscriberList()
	{
		$data = array();								
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	

		$date_start = isset($this->data['date_start'])?$this->data['date_start']:null;
		$date_end = isset($this->data['date_end'])?$this->data['date_end']:null;
		
					
		$sortby = "restaurant_name"; $sort = 'ASC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}			
		
		$page = $page>0? intval($page)/intval($length) :0;
		$criteria=new CDbCriteria();
		$criteria->alias = "a";
		$criteria->select = "
		a.merchant_id,a.restaurant_name,
		a.logo,a.path,
		b.package_id as plan_id,
		b.plan_name,
		b.amount as plan_amount,
		b.currency_code as plan_currency_code,
		b.next_due as plan_next_due,
		b.status as plan_status
		";
				
		$criteria->join = 'LEFT JOIN (
			SELECT ps1.*
			FROM {{plan_subscriptions}} ps1
			JOIN (
				SELECT subscriber_id, MAX(date_created) AS latest_subscription_date
				FROM {{plan_subscriptions}}
				GROUP BY subscriber_id
			) ps2 ON ps1.subscriber_id = ps2.subscriber_id AND ps1.date_created = ps2.latest_subscription_date
		) b ON a.merchant_id = b.subscriber_id';
		
		$criteria->condition="a.merchant_type=:merchant_type";
		$criteria->params = array(
		 ':merchant_type'=>1,		 
		);

		if(!empty($search)){
		    $criteria->addSearchCondition('a.restaurant_name', $search);			
        }
		if($date_start && $date_end){
			$criteria->addBetweenCondition("b.created_at",$date_start,$date_end);			
		}
		
		$criteria->order = "$sortby $sort";
		$count = AR_merchant::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
		if($length>0){
           $pages->applyLimit($criteria);        		
		}
						
        $models = AR_merchant::model()->findAll($criteria);
        if($models){															
        	foreach ($models as $items) {			
			  $status_colors = CommonUtility::getPlanColors($items->plan_status);
			  $logo_url = CMedia::getImage($items->logo,$items->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('merchant'));

			  $view_link= Yii::app()->createAbsoluteUrl("/vendor/subscriptions",[
				'id'=>$items->merchant_id
			  ]);


$logo_html = <<<HTML
<div class="d-flex align-items-start">
	<div class="mr-2"><img src="$logo_url" class="img-60 rounded-circle" /></div>
	<div><b>$items->restaurant_name</b></div>
</div>
HTML;


$view = <<<HTML
<div class="btn-group btn-group-actions" role="group">
	<a href="$view_link" class="normal btn btn-light tool_tips"><i class="zmdi zmdi-eye"></i></a>				
</div>
HTML;


        	  $data[]=array(
        		'merchant_id'=>$items->merchant_id,     
				//'restaurant_name'=>$items->restaurant_name,
				'restaurant_name'=>$logo_html,
				'plan_name'=>"<b>$items->plan_name</b>",
				'plan_amount'=> $items->plan_id>0? Price_Formatter::formatNumber($items->plan_amount) :'',
				'plan_next_due'=>!empty($items->plan_next_due)?Date_Formatter::date($items->plan_next_due):'',
				'plan_status'=>'<span class="badge '.$status_colors.' font12 text-capitalize">'.t($items->plan_status).'</span>',
				'merchant_uuid'=>$view
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

	public function actionsummarySubscriptions()
	{
		try {
						
			$start_date = Yii::app()->input->get('start_date');
			$end_date = Yii::app()->input->get('end_date');

			$data = CPlan::SubscriptionsSummary($start_date,$end_date);

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

	public function actiongetmerchantsubscriptions()
	{
		try {

			$merchant_uuid = Yii::app()->input->get('merchant_uuid');
			$model = CMerchants::getByUUID($merchant_uuid);

			$data = null; $history = null;

			try {
				$data = Cplans::getSubscriberPlan($model->merchant_id,'merchant',Yii::app()->language);
		    } catch (Exception $e) {}

			$history = Cplans::getSubscriptionHistory($model->merchant_id,'merchant',Yii::app()->language);
			
			$credit_limits = [
				'items_added'=>intval($model->items_added),
				'item_limit'=>intval($model->item_limit),
				'orders_added'=>intval($model->orders_added),
				'order_limit'=>intval($model->order_limit),				
			];			

			$features = AR_merchant_meta::getValue($model->merchant_id,'subscription_features');			
			$subscription_features = isset($features['meta_value'])? json_decode($features['meta_value'],true) :null;			

			$this->code = 1;
            $this->msg = "Ok";
            $this->details = [				
                'data'=>$data,
				'history'=>$history,
				'credit_limits'=>$credit_limits,
				'features_list'=>AttributesTools::PlansFeatureList(),
				'subscription_features'=>$subscription_features
            ];
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actionsusbcriptionsupdatelimits()
	{
		try {
			
			$merchant_uuid = Yii::app()->input->post('merchant_uuid');
			$items_added = Yii::app()->input->post('items_added');
			$item_limit = Yii::app()->input->post('item_limit');
			$orders_added = Yii::app()->input->post('orders_added');
			$order_limit = Yii::app()->input->post('order_limit');
			$plan_features = Yii::app()->input->post('plan_features');
			$plan_features = !empty($plan_features)?json_decode($plan_features,true):null;

			$features_List = AttributesTools::PlansFeatureList();
						
			$subscription_features = [];

			foreach ($features_List as $key => $value) {
				$subscription_features[$key] = in_array($key,(array)$plan_features)?true:false;
			}			

			$model = CMerchants::getByUUID($merchant_uuid);
			$model->items_added = intval($items_added);
			$model->item_limit = intval($item_limit);
			$model->orders_added = intval($orders_added);
			$model->order_limit = intval($order_limit);			
			$model->subscription_features = json_encode($subscription_features);
			
			if($model->save()){
				$this->code = 1;
				$this->msg = t(Helper_success);

				$data = null; $history = null;
				try {
					$data = Cplans::getSubscriberPlan($model->merchant_id,'merchant',Yii::app()->language);
				} catch (Exception $e) {}

				$history = Cplans::getSubscriptionHistory($model->merchant_id,'merchant',Yii::app()->language);
				$credit_limits = [
					'items_added'=>intval($model->items_added),
					'item_limit'=>intval($model->item_limit),
					'orders_added'=>intval($model->orders_added),
					'order_limit'=>intval($model->order_limit),
				];
				$this->details = [
					'data'=>$data,
					'history'=>$history,
					'credit_limits'=>$credit_limits,
					'subscription_features'=>!empty($model->subscription_features) ? json_decode($model->subscription_features,true): null
				];
			} else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actionsubscriptiondeposit()
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

		$criteria->condition="deposit_type=:deposit_type";
		$criteria->params = ['deposit_type'=>'subscriptions'];

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
		if($length>0){
           $pages->applyLimit($criteria); 
		}
								
        if($models = AR_bank_deposit::model()->findAll($criteria)){			
			foreach ($models as $item) {
			
				$exchange_rate = $item->exchange_rate_merchant_to_admin>0?$item->exchange_rate_merchant_to_admin:1;				
				$amount = Price_Formatter::formatNumber( ($item->amount*$exchange_rate) );

				$link = CMedia::getImage($item->proof_image,$item->path);			 
				$order_link = Yii::app()->CreateUrl("/vendor/subscriptions",[
					'id'=>$item->merchant_id
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
					'amount'=>$amount,
					'reference_number'=>$item->reference_number,
					'view_url'=>Yii::app()->createUrl("/plans/bank_deposit_view/",array('id'=>$item->deposit_uuid)),
					'delete_url'=>Yii::app()->createUrl("/plans/bank_deposit_delete/",array('id'=>$item->deposit_uuid)),
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
			':merchant_id'=>0
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
        		    'delete_url'=>Yii::app()->createUrl("/admin/delete_location_rate/",array('id'=>$items->rate_id)),				  
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
				'label' => t('Please select'),
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
				'label' => t('Please select'),
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
				'label' => t('Please select'),
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
			$model = AR_location_rate::model()->find("rate_id=:rate_id",[
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

	public function actionsavemerchantlocation()
	{
		try {
			
			$id = intval(Yii::app()->input->post("rate_id"));
			$merchant_id = intval(Yii::app()->input->post("merchant_id"));
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
					'update_url'=>Yii::app()->createUrl("/vendor/update_location/",array('id'=>$items->id)),
        		    'delete_url'=>Yii::app()->createUrl("/vendor/delete_location/",array('id'=>$items->id)),		
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
	
	public function actiongetmanagelocation()
	{
		try {
			$id = Yii::app()->input->post('rate_id');
			$model = AR_merchant_location::model()->find("id=:id",[
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

	public function actionsavelocationestimate()
	{
		try {

			$id = intval(Yii::app()->input->post("rate_id"));
			$merchant_id = intval(Yii::app()->input->post('merchant_id'));
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

	public function actionlocationTimeManagementList()
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
        		    'delete_url'=>Yii::app()->createUrl("/admin/delete_estimate_time/",array('id'=>$items->id)),		
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

	public function actiongetestimate()
	{
		try {
			$id = Yii::app()->input->post('rate_id');
			$model = AR_location_time_estimate::model()->find("id=:id",[
				':id'=>intval($id)
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

	public function actiongetdeliverymanagement()
	{
		$options = ['admin_delivery_charges_type','admin_opt_contact_delivery','admin_free_delivery_on_first_order'];
		$data = OptionsTools::find($options);
		$delivery_charges_type = isset($data['admin_delivery_charges_type'])?$data['admin_delivery_charges_type']:'';
		$opt_contact_delivery = isset($data['admin_opt_contact_delivery'])?$data['admin_opt_contact_delivery']:'';
		$free_delivery_on_first_order = isset($data['admin_free_delivery_on_first_order'])?$data['admin_free_delivery_on_first_order']:'';

		$opt_contact_delivery = $opt_contact_delivery==1?true:false;
		$free_delivery_on_first_order = $free_delivery_on_first_order==1?true:false;
		$delivery_charges_type = !empty($delivery_charges_type)?$delivery_charges_type:'fixed';


		$merchant_id = 0;
		$service_code = 'delivery';
		$charge_type = 'fixed';
		$shipping_type = 'standard';

		$fixed_data = null;
		try {
			$fixed_data = CMerchants::getFixedCharge($merchant_id,$service_code,$charge_type,$shipping_type);
		} catch (Exception $e) {}		

		$base_distance_data = null;
		try {
			$base_distance_data = CMerchants::getBasedistanceCharge($merchant_id,$service_code,'base-distance',$shipping_type);
		} catch (Exception $e) {}		

		$this->code = 1;
		$this->msg = "Ok";
		$this->details = [
			'delivery_charges_type'=>$delivery_charges_type,
			'opt_contact_delivery'=>$opt_contact_delivery,
			'free_delivery_on_first_order'=>$free_delivery_on_first_order,
			'fixed_data'=>$fixed_data,
			'base_distance_data'=>$base_distance_data
		];
		$this->responseJson();
	}

	public function actionsaveDeliveryManagement()
	{
		try {
			
			$merchant_id = 0;
			$options = ['admin_delivery_charges_type','admin_opt_contact_delivery','admin_free_delivery_on_first_order'];

			$model=new AR_option;
		    $model->scenario = 'delivery_management';
			$params = [
				'admin_delivery_charges_type'=>Yii::app()->input->post('delivery_charges_type'),
				'admin_opt_contact_delivery'=>intval(Yii::app()->input->post('opt_contact_delivery')),
				'admin_free_delivery_on_first_order'=>intval(Yii::app()->input->post('free_delivery_on_first_order')),
			];
			$model->attributes = $params;
			if($model->validate()){
				OptionsTools::save($options, $model, $merchant_id);
				CMerchants::updateChargeType($merchant_id,$params);
				$this->code = 1;
				$this->msg = t(Helper_settings_saved);
			} else $this->msg = CommonUtility::parseError( $model->getErrors());
			
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actionsavedeliveryfixedrate()
	{
		try {

			$merchant_id = 0;
			$service_code = 'delivery';
		    $charge_type = 'fixed';
		    $shipping_type = 'standard';
			$data_unit = AttributesTools::getDefaultDistanceUnit();				
		    $distance_unit = $data_unit['unit'];

			$model = AR_shipping_rate::model()->find('merchant_id=:merchant_id AND charge_type=:charge_type
			AND shipping_type=:shipping_type AND service_code=:service_code
			', 
			array(':merchant_id'=>$merchant_id, 
				':charge_type'=>$charge_type,
				':shipping_type'=>$shipping_type,
				':service_code'=>$service_code,
			));
			
			if(!$model){
				$model = new AR_shipping_rate;
			} else {
				$stmt_delete = "DELETE 
				from {{shipping_rate}}
				where
				service_code = ".q($service_code)."
				and charge_type = ".q($charge_type)."
				and shipping_type = ".q($shipping_type)."
				and merchant_id=".q($merchant_id)."
				and id<> ".q($model->id)." ";		
				Yii::app()->db->createCommand($stmt_delete)->query();	
			}

			$fixed_estimation = Yii::app()->input->post('fixed_estimation');
			if (strlen($fixed_estimation) == 4) {
				$fixed_estimation = substr($fixed_estimation, 0, 2) . '-' . substr($fixed_estimation, 2, 2);
			}
			
			$model->scenario = 'fixed';
			$model->charge_type = $charge_type;
			$model->shipping_type = $shipping_type;
			$model->merchant_id = $merchant_id;
			$model->shipping_units = $distance_unit;
			$model->distance_price = floatval(Yii::app()->input->post('fixed_price'));
			$model->estimation = $fixed_estimation;
			$model->minimum_order = floatval(Yii::app()->input->post('fixed_minimum_order'));
			$model->maximum_order = floatval(Yii::app()->input->post('fixed_maximum_order'));
			$model->fixed_free_delivery_threshold = floatval(Yii::app()->input->post('fixed_free_delivery_threshold'));
			if($model->save()){
				$this->code = 1;				
				$this->msg = t(Helper_settings_saved);
			} else $this->msg = CommonUtility::parseError( $model->getErrors());

		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actionsavebasedistance()
	{
		try {
			
			$merchant_id = 0;
			$service_code = 'delivery';
		    $charge_type = 'base-distance';
		    $shipping_type = 'standard';
			$data_unit = AttributesTools::getDefaultDistanceUnit();				
		    $distance_unit = $data_unit['unit'];
			
			$model = AR_shipping_rate::model()->find('merchant_id=:merchant_id AND charge_type=:charge_type
			AND shipping_type=:shipping_type AND service_code=:service_code
			', 
			array(':merchant_id'=>$merchant_id, 
				':charge_type'=>$charge_type,
				':shipping_type'=>$shipping_type,
				':service_code'=>$service_code,
			));
			
			if(!$model){
				$model = new AR_shipping_rate;
			} else {
				$stmt_delete = "DELETE 
				from {{shipping_rate}}
				where
				service_code = ".q($service_code)."
				and charge_type = ".q($charge_type)."
				and shipping_type = ".q($shipping_type)."
				and merchant_id=".q($merchant_id)."
				and id<> ".q($model->id)." ";		
				Yii::app()->db->createCommand($stmt_delete)->query();	
			}
			
			$model->scenario = 'fixed';
			$model->charge_type = $charge_type;
			$model->shipping_type = $shipping_type;
			$model->merchant_id = $merchant_id;
			$model->shipping_units = $distance_unit;

			$model->distance_from = floatval(Yii::app()->input->post('bd_base_distance'));
			$model->distance_price = Yii::app()->input->post('bd_base_delivery_fee');
			$model->distance_to = floatval(Yii::app()->input->post('bd_price_extra_distance'));
			$model->delivery_radius = floatval(Yii::app()->input->post('bd_delivery_radius'));
			$model->minimum_order = floatval(Yii::app()->input->post('bd_minimum_order'));
			$model->maximum_order = floatval(Yii::app()->input->post('bd_maximum_order'));
			$model->fixed_free_delivery_threshold = floatval(Yii::app()->input->post('bd_free_delivery_threshold'));
			$model->cap_delivery_charge = floatval(Yii::app()->input->post('bd_cap_delivery_charge'));
			$model->estimation = floatval(Yii::app()->input->post('bd_base_time_estimate'));
			$model->time_per_additional = floatval(Yii::app()->input->post('bd_base_time_estimate_additional'));
			
			if($model->save()){
				$this->code = 1;				
				$this->msg = t(Helper_settings_saved);
			} else $this->msg = CommonUtility::parseError( $model->getErrors());

		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actionrangedistancelist()
	{
		$data = array();
		$data_unit = AttributesTools::getDefaultDistanceUnit();				
		$distance_unit = $data_unit['pretty'];		
						
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
						
		$sortby = "distance_from"; $sort = 'DESC';
		
		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = intval($page)/intval($length);		
		$criteria=new CDbCriteria();		
		$criteria->condition="merchant_id=:merchant_id AND charge_type=:charge_type";
		$criteria->params = [
			':merchant_id'=>0,
			':charge_type'=>'range-based'
		];	

		if(!empty($search)){			
			//$criteria->addSearchCondition('state_name', $search );			
		}		
						
		$criteria->order = "$sortby $sort";
		$count = AR_shipping_rate::model()->count($criteria); 
		
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria);        		
        $models = AR_shipping_rate::model()->findAll($criteria);		
        if($models){									
        	foreach ($models as $items) { 			

				$items->shipping_type = ucwords(t($items->shipping_type));
				$distance = t("{from} - {to} {unit}",[
					'{from}'=>$items->distance_from,
					'{to}'=>$items->distance_to,
					'{unit}'=>$distance_unit
				]);

				$estimation = t("{estimation} mins",[
					'{estimation}'=>$items->estimation	
				]);


$html = <<<HTML
<h6>$items->shipping_type
<p class="dim mt-1">
$distance<br>
$estimation
</p>
</h6>
HTML;

				$data[] = [
					'id'=>$items->id,
					'distance_from'=>$html,
					'distance_price'=>Price_Formatter::formatNumber($items->distance_price),
					'minimum_order'=>Price_Formatter::formatNumber($items->minimum_order),
					'maximum_order'=>Price_Formatter::formatNumber($items->maximum_order),
					'fixed_free_delivery_threshold'=>Price_Formatter::formatNumber($items->fixed_free_delivery_threshold),	
					'update_url'=>Yii::app()->createUrl("/merchant/update_location_rate/",array('id'=>$items->id)),
        		    'delete_url'=>Yii::app()->createUrl("/admin/deleterangebasedfee/",array('id'=>$items->id)),				  
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

	public function actionsaverangedistance()
	{
		try {
			
			$merchant_id = 0;
			$service_code = 'delivery';
		    $charge_type = 'range-based';
		    $shipping_type = 'standard';
			$data_unit = AttributesTools::getDefaultDistanceUnit();				
		    $distance_unit = $data_unit['unit'];
			
			$id = intval(Yii::app()->input->post('id'));
			$rd_from = Yii::app()->input->post('rd_from');
			$rd_to = Yii::app()->input->post('rd_to');
			$rd_price = Yii::app()->input->post('rd_price');
			$rd_estimation = Yii::app()->input->post('rd_estimation');
			$rd_minimum_order = Yii::app()->input->post('rd_minimum_order');
			$rd_maximum_order = Yii::app()->input->post('rd_maximum_order');
			$rd_free_delivery_threshold = Yii::app()->input->post('rd_free_delivery_threshold');

			if (strlen($rd_estimation) == 4) {
				$rd_estimation = substr($rd_estimation, 0, 2) . '-' . substr($rd_estimation, 2, 2);
			}

			if($id>0){
				$model = AR_shipping_rate::model()->find("id=:id",[
					':id'=>$id
				]);
			} else $model = new AR_shipping_rate();

			$model->scenario = 'dynamic';
			$model->charge_type = $charge_type;
			$model->shipping_type = $shipping_type;
			$model->merchant_id = $merchant_id;
			$model->shipping_units = $distance_unit;

			$model->distance_from = floatval($rd_from);
			$model->distance_to = floatval($rd_to);
			$model->distance_price = floatval($rd_price);
			$model->estimation = $rd_estimation;
			$model->minimum_order = floatval($rd_minimum_order);
			$model->maximum_order = floatval($rd_maximum_order);
			$model->fixed_free_delivery_threshold = floatval($rd_free_delivery_threshold);
			if($model->save()){
				$this->code = 1;				
				$this->msg = t(Helper_settings_saved);
			} else {				
				$this->msg = CommonUtility::parseError( $model->getErrors());
			}

		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actiongetrangerate()
	{
		try {

			$merchant_id = 0;
			$id = intval(Yii::app()->input->post('id'));
			$model = AR_shipping_rate::model()->find("id=:id AND merchant_id=:merchant_id",[
				':id'=>$id,
				':merchant_id'=>$merchant_id
			]);
			if($model){
				$this->code = 1;
				$this->msg = "Ok";
				$this->details = [
					'id'=>$model->id,
					'distance_from'=>$model->distance_from,
					'distance_to'=>$model->distance_to,
					'distance_price'=>$model->distance_price,
					'estimation'=>$model->estimation,
					'minimum_order'=>$model->minimum_order,
					'maximum_order'=>$model->maximum_order,
					'fixed_free_delivery_threshold'=>$model->fixed_free_delivery_threshold,
				];
			} else $this->msg = t(HELPER_RECORD_NOT_FOUND);
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actiongetAutomatedStatus()
	{
		try {			
			$status_list = AttributesTools::getOrderStatus(Yii::app()->language);
			$status_list = $status_list?CommonUtility::ArrayToLabelValue($status_list):[];
			
			$data = AttributesTools::automatedStatus();
			if(!$data){
				$data[] = [
					'from'=>'',
					'to'=>'',
					'time'=>'',
				];
			}
			
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'data'=>$data,
				'status'=>$status_list
			];
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actionsaveautomatedstatus()
	{
		try {

			if(DEMO_MODE){
				$this->msg = t("Modification not available in demo");
				$this->responseJson();
			}

			$params = []; $meta_name='automated_status';
			$data = Yii::app()->input->xssClean(json_decode(file_get_contents('php://input'), true));
			
			AR_admin_meta::model()->deleteAll("meta_name=:meta_name",[
				':meta_name'=>$meta_name
			]);

			if(is_array($data) && count($data)>=1){
				foreach ($data as $items) {
					if(!empty($items['from'])){
						$params[] = [
							'meta_name'=>'automated_status',
							'meta_value'=>$items['from'],
							'meta_value1'=>$items['to'],
							'meta_value2'=>$items['time'],
							'date_modified'=>CommonUtility::dateNow()
						];
					}					
				}

				if(is_array($params) && count($params)>=1){
					$builder=Yii::app()->db->schema->commandBuilder;
					$command=$builder->createMultipleInsertCommand("{{admin_meta}}",$params);
					$command->execute();			
					CCacheData::add();
				}
			}			

			$this->code = 1;
			$this->msg = t(Helper_settings_saved);
				
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actionselectfetchcountry()
	{
		$data = [];
		try {			
			$search = Yii::app()->input->get('q');
			$search = $search=='undefined'?'':$search;	
			$data = Clocations::fetchCountry($search);			
		} catch (Exception $e) {}

		$result = array(
		   'results'=>$data
		);	    	
		$this->responseSelect2($result);		
	}
	
	public function actionselectfetchstate()
	{
		$data = [];
		try {			
			$search = Yii::app()->input->get('q');
			$country_id = Yii::app()->input->get('country_id');
			$search = $search=='undefined'?'':$search;	
			$data = Clocations::fetchState($country_id,$search);			
		} catch (Exception $e) {}

		$result = array(
		   'results'=>$data
		);	    	
		$this->responseSelect2($result);		
	}

	public function actionselectfetchcity()
	{
		$data = [];
		try {			
			$search = Yii::app()->input->get('q');
			$state_id = Yii::app()->input->get('state_id');
			$search = $search=='undefined'?'':$search;	
			$data = Clocations::fetchCity($state_id);	
		} catch (Exception $e) {}

		$result = array(
		   'results'=>$data
		);	    	
		$this->responseSelect2($result);		
	}

	public function actionselectfetcharea()
	{
		$data = [];
		try {			
			$search = Yii::app()->input->get('q');
			$city_id = Yii::app()->input->get('city_id');
			$search = $search=='undefined'?'':$search;	
			$data = Clocations::fetchArea($city_id);	
		} catch (Exception $e) {}

		$result = array(
		   'results'=>$data
		);	    	
		$this->responseSelect2($result);		
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
				CCacheData::add();
			} else $this->msg = CommonUtility::parseModelErrorToString( $model->getErrors());
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actionupdatecustomfields()
	{
		$this->actioncreatecustomfields(true);
	}

	public function actioncreatecustomfields($update=false)
	{
		try {
			
			if(DEMO_MODE){
				$this->msg = t("Modification not available in demo");
				$this->responseJson();
			}

			$field_id = intval(Yii::app()->input->post("field_id"));
			$field_name = Yii::app()->input->post("field_name");
			$field_label = Yii::app()->input->post("field_label");
			$field_type = Yii::app()->input->post("field_type");
			$options = Yii::app()->input->post("options");
			$is_required = Yii::app()->input->post("is_required");
			$entity = Yii::app()->input->post("entity");
			$is_required = $is_required=="true"?1:0;
			$sequence = intval(Yii::app()->input->post("sequence"));

			if($update){
				$model = AR_custom_fields::model()->find("field_id=:field_id",[
					':field_id'=>$field_id
				]);
				if(!$model){
					$this->msg = t(HELPER_RECORD_NOT_FOUND);
					$this->responseJson();
				}
			} else $model = new AR_custom_fields();
			
			$model->field_name = $field_name;
			$model->field_label = $field_label;
			$model->field_type = $field_type;
			$model->options = $options;
			$model->is_required = intval($is_required);
			$model->entity = $entity;			
			if($sequence>0){
				$model->sequence = $sequence;
			}
			if($model->save()){
				$this->code = 1;
				$this->msg = t(Helper_success);				
				CCacheData::add();
			} else $this->msg = CommonUtility::parseModelErrorToString( $model->getErrors());			
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actioncustomfieldslist()
	{
		try {

			$criteria = new CDbCriteria;
			$criteria = new CDbCriteria;				
			$criteria->order = "field_id desc";			
			if($model= AR_custom_fields::model()->findAll($criteria)){
				$data = [];
				foreach ($model as $items) {
					$data[] = [
						'field_id'=>$items->field_id,
						'field_name'=>$items->field_name,
						'field_label'=>$items->field_label,
						'field_type'=>$items->field_type,
						'is_required'=>$items->is_required,
						'entity'=>$items->entity,
						'sequence'=>$items->sequence,
					];
				}
				$this->code = 1;
				$this->msg = "Ok";
				$this->details = $data;
			} else $this->msg = t(HELPER_NO_RESULTS);
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actiondeletecustomfields()
	{
		try {

			$field_id = Yii::app()->input->get('field_id');
			$model = AR_custom_fields::model()->find("field_id=:field_id",[
				':field_id'=>$field_id
			]);
			if($model){
				$model->delete();
				$this->code = 1;
				$this->msg = "Ok";
				$this->actioncustomfieldslist();
			} else $this->msg = t(HELPER_RECORD_NOT_FOUND);
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actiongetcustomfields()
	{
		try {
			
			$field_id = Yii::app()->input->get('field_id');
			$model = AR_custom_fields::model()->find("field_id=:field_id",[
				':field_id'=>$field_id
			]);
			if($model){				
				$this->code = 1;
				$this->msg = "Ok";
				$this->details = [
					'field_id'=>$model->field_id,
					'field_name'=>$model->field_name,
					'field_label'=>$model->field_label,
					'field_type'=>$model->field_type,
					'options'=>$model->options,
					'is_required'=>$model->is_required,
					'entity'=>$model->entity,
					'sequence'=>$model->sequence,
				];
			} else $this->msg = t(HELPER_RECORD_NOT_FOUND);

		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actionmonthlyThresholds()
	{
		$data = array();
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')   :'';	
				
		$sortby = "transaction_id"; $sort = 'DESC';

		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}
				
		$page = $page>0? intval($page)/intval($length) :0;
		$criteria=new CDbCriteria();			
		$criteria->condition = "meta_name=:meta_name";
		$criteria->params = [
			':meta_name'=>AttributesTools::monthlyThresholds()
		];
		
		$criteria->order = "$sortby $sort";
		$count = AR_admin_meta::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
        $pages->setCurrentPage( intval($page) );        
        $pages->pageSize = intval($length);
        $pages->applyLimit($criteria); 		
        $models = AR_admin_meta::model()->findAll($criteria);		
		if($models){	
			foreach ($models as $item) {
			

				$edit = Yii::app()->createAbsoluteUrl("/points/update_monthlybunos",[
					'id'=>$item->meta_id
				]);
				$delete = Yii::app()->createAbsoluteUrl("/points/delete_monthlybunos",[
					'id'=>$item->meta_id
				]);				



$buttons = <<<HTML
<div class="btn-group btn-group-actions" role="group">
 <a href="$edit"  class="btn btn-light tool_tips" data-toggle="tooltip" data-placement="top" >
	<i class="zmdi zmdi-border-color"></i>
</a>
 <a href="$delete"  class="btn btn-light tool_tips"><i class="zmdi zmdi-delete"></i></a> 
</div>
HTML;
	
	
				$data[] = [
					'meta_id'=>$item->meta_id,
					'meta_value'=>$item->meta_value,
					'meta_value1'=>$item->meta_value1,
					'meta_name'=>$buttons
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

	public function actiondeleteAddon()
	{
		try {
			
			$id =  Yii::app()->request->getQuery('id', '');  						
			$model = AR_addons::model()->find("id=:id",[
				':id'=>intval($id)
			]);
			if($model){
				$model->delete();
				$this->code = 1;
				$this->msg = "Ok";
			} else $this->msg = t(HELPER_RECORD_NOT_FOUND);
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actionsaveaddress()
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
						'redirect'=>Yii::app()->createAbsoluteUrl("/buyer/address",[
							'id'=>$client_id
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

	public function actionchangeorderstatus()
	{
		try {

			$order_uuid = Yii::app()->request->getPost('order_uuid', '');
			$status = Yii::app()->request->getPost('status', '');

			$tracking_stats = AR_admin_meta::getMeta(['tracking_status_process','tracking_status_in_transit']);						
			$tracking_status_process = isset($tracking_stats['tracking_status_process'])?$tracking_stats['tracking_status_process']['meta_value']:'accepted'; 			
			$tracking_status_delivering = isset($tracking_stats['tracking_status_in_transit'])?$tracking_stats['tracking_status_in_transit']['meta_value']:'delivery on its way'; 

			$status_cancel = AOrderSettings::getStatus(array('status_cancel_order','status_rejection'));			

			$model = COrders::get($order_uuid);					
			if($model->status==$status){
				$this->msg = t("Order has the same status");
				$this->responseJson();
			}

			if($status==$tracking_status_process){
				if($model->whento_deliver=="schedule"){
					$scheduled_delivery_time = $model->delivery_date." ".$model->delivery_time;
					$preparationStartTime = CommonUtility::calculatePreparationStartTime($scheduled_delivery_time,($model->preparation_time_estimation+$model->delivery_time_estimation));					
					$currentTime = time();
					if ($currentTime < $preparationStartTime) {						
						$model->order_accepted_at = date("Y-m-d G:i:s", $preparationStartTime);
					} else $model->order_accepted_at = CommonUtility::dateNowAdd();
				} else {
					$model->order_accepted_at = CommonUtility::dateNowAdd();				
				}				
			} else if ( $status==$tracking_status_delivering ){
				$model->pickup_time = CommonUtility::dateNowAdd();
			}
			
			if(in_array($status,$status_cancel)){
				$model->scenario = "reject_order";
			} else $model->scenario = "change_status";	

			$model->status = $status;			
			$model->change_by = Yii::app()->user->first_name;

			if($model->save()){
				$this->code = 1;
				$this->msg = t("Status Updated");				
			} else $this->msg = CommonUtility::parseError( $model->getErrors());
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actionOrderIssueRefund()
	{
		try {

			$order_uuid = Yii::app()->request->getPost('order_uuid', '');
			$amount = Yii::app()->request->getPost('amount', '');
			$refund_type = Yii::app()->request->getPost('refund_type', '');						
			$payment_code = Yii::app()->request->getPost('payment_code', '');			
			$reason = Yii::app()->request->getPost('reason', '');			
			
			$order = COrders::get($order_uuid);			
			$model_transaction = AR_ordernew_transaction::model()->find("order_id=:order_id AND transaction_type=:transaction_type",[
                ':order_id'=>$order->order_id,
                ':transaction_type'=>'credit'
            ]);
            if($model_transaction){

				$total_credit = COrders::getRefundBalance($order->order_id);				
				if($amount>$total_credit){
					$this->msg = t("Refund amount exceeds the total paid amount for this order ({amoumt}). Enter a valid refund amount and try again.",[
						'{amoumt}'=>Price_Formatter::formatNumber($order->total)
					]);
					$this->responseJson();
				}

				$all_online = CPayments::getPaymentTypeOnline();
				
				$get_params = [
					'order_uuid'=> $order->order_uuid,
					'refund_type'=>$refund_type,
					'refund_amount'=>$amount,
					'payment_code'=>$payment_code,
				];

				if(array_key_exists($order->payment_code,(array)$all_online)){ 
					CommonUtility::pushJobs("Ordercancel",$get_params);						
				} else {
					try {									
						$jobs = 'Ordercancel';
						$jobInstance = new $jobs($get_params);
						$jobInstance->execute();							
					} catch (Exception $e) { }
				}				
				$this->code = 1;
				$this->msg = t("Refund successful");
				$this->details = [
					'refund_type'=>$refund_type,
					'refund_amount'=>$amount
				];
				$this->responseJson();								
            } else {
                $this->msg = t("There are no succesful payment for this order");
            }            										
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actiondeleteorder()
	{
		try {

			$order_uuid =  Yii::app()->request->getQuery('order_uuid', '');  
			$model = COrders::get($order_uuid);		
			$model->scenario = "order_deleted";
			$model->status = "deleted";	
			$model->remarks = "Order deleted";
			$model->change_by = Yii::app()->user->first_name;
			if($model->save()){
				$this->code = 1;
				$this->msg = "Ok";
				$this->details = [
					'redirect'=>Yii::app()->createAbsoluteUrl("/order/list")
				];
			} else $this->msg = CommonUtility::parseError( $model->getErrors());
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actiongetAllItemList()
	{
		$pageSize = Yii::app()->request->getQuery('page_size', 10);  		
        $page = Yii::app()->request->getQuery('page', 1);  
		$query = Yii::app()->request->getQuery('query', null);
		$filter_by = Yii::app()->request->getQuery('filter_by', null);
		$filter_by = $filter_by?$filter_by:'a.item_name';
		$filter_all_featured = Yii::app()->request->getQuery('filter_all_featured', null);
		$filter_all_featured = $filter_all_featured=="true"?true:false;

		$sort_field = Yii::app()->request->getQuery('sort_field', null);
		$sort_order = Yii::app()->request->getQuery('sort_order', null);

		$sortby = "item_id"; $sort = 'DESC';
		if(!empty($sort_field)){			
			$sortby = $sort_field;
		}
		if(!empty($sort_order)){			
			$sort = $sort_order;
		}

		$criteria=new CDbCriteria;
		$criteria->alias="a";
		$criteria->select = "a.item_id,a.merchant_id,
		a.item_token, a.is_featured, a.featured_priority,a.photo,a.path,
		IF(COALESCE(NULLIF(b.item_name, ''), '') = '', a.item_name, b.item_name) as item_name,
		(SELECT price FROM {{item_relationship_size}} WHERE item_id = a.item_id LIMIT 1) as item_price,
		a.unavailable_until,a.available,a.status,
		merchant.restaurant_name,
		item_relationship_category.cat_id as category_selected,
		(
		  select category_name from {{category}} where cat_id = item_relationship_category.cat_id
		) as group_category
		";
		$criteria->join = "
		left JOIN (
			SELECT item_id,item_name  FROM {{item_translation}} where language=".q(Yii::app()->language)."
		) b 
		ON a.item_id = b.item_id

		LEFT JOIN {{merchant}} merchant
		ON 
		a.merchant_id = merchant.merchant_id	

		LEFT JOIN (
			SELECT item_id, cat_id 
			FROM {{item_relationship_category}}
			WHERE cat_id IS NOT NULL
			GROUP BY item_id 
		) item_relationship_category
		ON a.item_id = item_relationship_category.item_id

		";
		$criteria->condition = "a.status=:status AND a.visible=:visible";
		$criteria->params = [
			':status'=>'publish',
			':visible'=>1,
		];
		
		if($query){			
			if($filter_by=="restaurant_name"){
				$criteria->addSearchCondition('merchant.restaurant_name', $query );
			} else if ( $filter_by=="category"){
				//$criteria->addSearchCondition('group_category', $query );								
			} else {
				$criteria->addSearchCondition('a.item_name', $query );
			}			
		}
		if($filter_all_featured){
			$criteria->addCondition("a.is_featured = 1");
		}
						
		$criteria->order = "$sortby $sort";
		$dataProvider = new CActiveDataProvider('AR_item', [
            'criteria'=>$criteria,
            'pagination' => [
                'pageSize' => $pageSize,
                'currentPage' => $page - 1,
            ],
        ]);
		
		$data = [];		
		if($provider_data = $dataProvider->getData()){
			foreach ($provider_data as $items) {
				$data[] = [
					'item_id'=>$items->item_id,
					'photo'=>CMedia::getImage($items->photo,$items->path,Yii::app()->params->size_image,CommonUtility::getPlaceholderPhoto('item')),
					'merchant'=>CommonUtility::safeDecode($items->restaurant_name),
					'item_name'=>CommonUtility::safeDecode($items->item_name),
					'category'=>CommonUtility::safeDecode($items->group_category),
					'price'=>Price_Formatter::formatNumber($items->item_price),
					'is_featured'=>$items->is_featured==1?true:false,
					'featured_priority'=>$items->featured_priority?$items->featured_priority:'',
				];
			}
		}		

		$totalCount = $dataProvider->totalItemCount;
		$data = [
			'total_display_items'=>Yii::t('backend', '{n} item|{n} items', $totalCount),
            'total_items' => (integer)$totalCount,
            'page_size' => (integer)$pageSize,
            'current_page' => (integer)$page,
            'total_pages' => ceil($totalCount / $pageSize),
            'items' => $data,
        ];
		$this->code;
		$this->code = 1;
		$this->msg = "Ok";
		$this->details = $data;
		$this->responseJson();	
	}

	public function actionupdateFeatureItems()
	{
		try {
			
			$item_id =  Yii::app()->request->getPost('item_id', '');    
			$is_featured =  Yii::app()->request->getPost('is_featured', '');    
			$featured_priority =  Yii::app()->request->getPost('featured_priority', '');    
			$model = AR_item_slug::model()->findByPk(intval($item_id));

			if($model){
				$model->scenario = "update_featured_items";
				$model->is_featured  = intval($is_featured);
				$model->featured_priority  = intval($featured_priority);
				if($model->save()){
					$this->code = 1;
					$this->msg = "Ok";
				} else $this->msg = CommonUtility::parseError( $model->getErrors());
			} else $this->msg = t(HELPER_RECORD_NOT_FOUND);
			
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actiongetitemssuggested()
	{
		$pageSize = Yii::app()->request->getQuery('page_size', 10);  		
        $page = Yii::app()->request->getQuery('page', 1);  
		$query = Yii::app()->request->getQuery('query', null);
		$filter_by = Yii::app()->request->getQuery('filter_by', null);
		$filter_by = $filter_by?$filter_by:'a.item_name';
		$filter_all_featured = Yii::app()->request->getQuery('filter_all_featured', null);
		$filter_all_featured = $filter_all_featured=="true"?true:false;

		$sort_field = Yii::app()->request->getQuery('sort_field', null);
		$sort_order = Yii::app()->request->getQuery('sort_order', null);

		$sortby = "created_at"; $sort = 'ASC';
		if(!empty($sort_field)){			
			$sortby = $sort_field;
		}
		if(!empty($sort_order)){			
			$sort = $sort_order;
		}

		$criteria=new CDbCriteria;
		$criteria->alias="a";
		$criteria->select = "a.id,a.item_id,a.merchant_id,
		a.created_at,a.status,
		item.item_token, item.is_featured, item.featured_priority,item.photo,item.path,
		IF(COALESCE(NULLIF(b.item_name, ''), '') = '', item.item_name, b.item_name) as item_name,
		(SELECT price FROM {{item_relationship_size}} WHERE item_id = a.item_id LIMIT 1) as item_price,
		item.unavailable_until,item.available,
		merchant.restaurant_name,
		item_relationship_category.cat_id as category_selected,
		(
		  select category_name from {{category}} where cat_id = item_relationship_category.cat_id
		) as group_category
		";
		$criteria->join = "
		left JOIN (
			SELECT item_id,item_name  FROM {{item_translation}} where language=".q(Yii::app()->language)."
		) b 
		ON a.item_id = b.item_id

		LEFT JOIN {{merchant}} merchant
		ON 
		a.merchant_id = merchant.merchant_id	

		LEFT JOIN (
			SELECT item_id, cat_id 
			FROM {{item_relationship_category}}
			WHERE cat_id IS NOT NULL
			GROUP BY item_id 
		) item_relationship_category
		ON a.item_id = item_relationship_category.item_id

		LEFT JOIN {{item}} item		
		ON a.item_id = item.item_id
		";
		$criteria->condition = "a.status=:status";
		$criteria->params = [
			':status'=>'pending',			
		];
		
		if($query){			
			if($filter_by=="restaurant_name"){
				$criteria->addSearchCondition('merchant.restaurant_name', $query );
			} else if ( $filter_by=="category"){
				$criteria->addSearchCondition('group_category', $query );
			} else {
				$criteria->addSearchCondition('item.item_name', $query );
			}			
		}
		if($filter_all_featured){
			$criteria->addCondition("a.is_featured = 1");
		}
				
		$criteria->order = "$sortby $sort";			
		// dump($_GET);
		// dump($criteria);
		// die();
		$dataProvider = new CActiveDataProvider('AR_suggested_items', [
            'criteria'=>$criteria,
            'pagination' => [
                'pageSize' => $pageSize,
                'currentPage' => $page - 1,
            ],
        ]);
		
		$data = [];		
		if($provider_data = $dataProvider->getData()){
			foreach ($provider_data as $items) {
				$data[] = [
					'id'=>$items->id,
					'item_id'=>$items->item_id,
					'photo'=>CMedia::getImage($items->photo,$items->path,Yii::app()->params->size_image,CommonUtility::getPlaceholderPhoto('item')),
					'merchant'=>CommonUtility::safeDecode($items->restaurant_name),
					'item_name'=>CommonUtility::safeDecode($items->item_name),
					'category'=>CommonUtility::safeDecode($items->group_category),
					'price'=>Price_Formatter::formatNumber($items->item_price),
					'is_featured'=>$items->is_featured==1?true:false,
					'featured_priority'=>$items->featured_priority?$items->featured_priority:'',
					'created_at'=>Date_Formatter::dateTime($items->created_at),
					'status'=>t($items->status),
					'status_raw'=>$items->status
				];
			}
		}		

		$totalCount = $dataProvider->totalItemCount;
		$data = [
			'total_display_items'=>Yii::t('backend', '{n} item|{n} items', $totalCount),
            'total_items' => (integer)$totalCount,
            'page_size' => (integer)$pageSize,
            'current_page' => (integer)$page,
            'total_pages' => (integer) ceil($totalCount / $pageSize),
            'items' => $data,
        ];
		$this->code;
		$this->code = 1;
		$this->msg = "Ok";
		$this->details = $data;
		$this->responseJson();	
	}

	public function actionApprovedSuggestItems()
	{
		try {			
			$id =  Yii::app()->request->getPost('id', ''); 
			$featured_priority =  Yii::app()->request->getPost('featured_priority', ''); 
			$model = AR_suggested_items::model()->findByPk($id);
			if($model){
				$model->status="approved";
				$model->featured_priority = intval($featured_priority);
				if($model->save()){
					$this->code = 1;
					$this->msg = t("Item with ID#{id} has been approved",[
						'{id}'=>$model->item_id
					]);
				} else $this->msg = CommonUtility::parseError( $model->getErrors());
			} else $this->msg = t(HELPER_RECORD_NOT_FOUND);
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actionrejectsuggestitems()
	{
		try {						
			$id =  Yii::app()->request->getPost('id', ''); 
			$reason =  Yii::app()->request->getPost('reason', ''); 			
			$reason = $reason=="null"?'':$reason;
			$model = AR_suggested_items::model()->findByPk($id);
			if($model){
				$model->status="rejected";
				$model->reason=$reason;
				if($model->save()){
					$this->code = 1;
					$this->msg = "ok";
				} else $this->msg = CommonUtility::parseError( $model->getErrors());
			} else $this->msg = t(HELPER_RECORD_NOT_FOUND);
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actionmarkAllApproved()
	{
		try {

			$this->data = Yii::app()->input->xssClean(json_decode(file_get_contents('php://input'), true));				
			$item_ids = isset($this->data['item_ids'])?$this->data['item_ids']:null;
			Citems::bulkFeatureItemsApproved($item_ids);
			$this->code  = 1;
			$this->msg = t("Items selected succesfully approved");

		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actionmarkAllRejected()
	{
		try {

			$this->data = Yii::app()->input->xssClean(json_decode(file_get_contents('php://input'), true));				
			$item_ids = isset($this->data['item_ids'])?$this->data['item_ids']:null;
			Citems::bulkFeaturedItemsRejected($item_ids);
			$this->code  = 1;
			$this->msg = t("Items selected succesfully updated");

		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actioncustomerDevices()
	{		 

		$client_id = isset($this->data['ref_id'])?$this->data['ref_id']:0;
		$page = isset($this->data['start'])?$this->data['start']:0;	
		$length = isset($this->data['length'])?$this->data['length']:0;	
		$draw = isset($this->data['draw'])?$this->data['draw']:0;	
		$search = isset($this->data['search'])?$this->data['search']['value']:'';	
		$columns = isset($this->data['columns'])?$this->data['columns']:'';
		$order = isset($this->data['order'])?  (isset($this->data['order'][0])?$this->data['order'][0]:'')  :'';	

		$sortby = "reservation_id"; $sort = 'DESC'; 

		if(is_array($order) && count($order)>=1){
			if(array_key_exists($order['column'],(array)$columns)){			
				$sort = $order['dir'];
				$sortby = $columns[$order['column']]['data'];
			}
		}

		$page = intval($page)/intval($length);		
		$criteria=new CDbCriteria();				
		$criteria->addCondition("user_type=:user_type AND user_id=:user_id AND platform=:platform");			
		$criteria->params = [		
			':user_type'=>'client',
			':user_id'=>intval($client_id),
			':platform'=>'pwa'
		];
		$criteria->order = "$sortby $sort";

		$count = AR_device::model()->count($criteria); 
		$pages=new CPagination( intval($count) );
		$pages->setCurrentPage( intval($page) );        
		$pages->pageSize = intval($length);
		$pages->applyLimit($criteria);        

		$data = [];		
		if($model = AR_device::model()->findAll($criteria)){
			foreach ($model as $items) {
								
				$view = Yii::app()->createAbsoluteUrl("buyer/send_push",[
					'id'=>$items->user_id,
					'device_id'=>$items->device_id
				]);				

				$action = <<<HTML
				<div class="btn-group btn-group-actions" role="group">				
				   <a class="normal btn btn-light tool_tips" href="$view"><i class="zmdi zmdi-mail-send"></i></a>
				</div>
				HTML;

				$data[] = [
					'device_id'=>$items->device_id,
					'platform'=>t($items->platform),
					'device_token'=>'<div class="text-truncate" style="width:200px;" >'.$items->device_token.'</div>',
					'enabled'=>$action
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

}
/*end class*/