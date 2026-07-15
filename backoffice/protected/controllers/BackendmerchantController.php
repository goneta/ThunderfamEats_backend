<?php
class BackendmerchantController extends CommonServices
{	
	public $code=2;
	public $msg;
	public $details;
	public $data;
	public $next_action,$page,$search,$limit,$resp_data = array(), $post_data=array();
	
	public function beforeAction($action)
	{						
		//if(Yii::app()->request->isAjaxRequest){	
		   $this->data = Yii::app()->input->xssClean($_POST);		   
		   return true;
		//} 		
		return false;
	}
	
	private function preparePaginate()
	{
		$this->limit = (integer)Yii::app()->params['list_limit'];
		$this->page = (integer)Yii::app()->input->get('page') - 1; 
		if($this->page>0){
			$this->page = ($this->page*$this->limit);
		}
		$this->search = Yii::app()->input->get('search'); 	
		
		$this->post_data = array(
		  'start'=>$this->page,
		  'search'=>$this->search,
		  'length'=>$this->limit,
		  'order'=>array(
		    array('column'=>0,'dir'=>'DESC')
		  )
		);			
	}
	
	private function outputListingData($cols=array(), $fields=array(), $stmt='' ,$filter_stmt='' )
	{
		
		if ( $data = AppListing::Listing($cols, $fields, $stmt, $this->post_data , $filter_stmt)){			
			$this->code = 1; $this->msg= "OK";
			$this->details = array(
			  'next_action'=>$this->next_action,
			  'is_search'=>!empty($this->search)?true:false,
			  'data'=>$data,
			  'page'=>(integer)$this->page
			);			
		} else {
			$this->msg = t(HELPER_NO_RESULTS);
			$this->details = array(
			   'next_action'=>$this->next_action,
			  'is_search'=>!empty($this->search)?true:false,
			  'page'=>(integer)$this->page
			);
		}
		$this->responseJson();
	}
		
	public function actionpayment_history()
	{
		    $id = (integer) Yii::app()->merchant->merchant_id;
		    	
		 	$cols[]=array(
		 	  'key'=>'package_id',
		 	  'value'=>'package_name',		 	  
		 	);		 
		 	$cols[]=array(
		 	  'key'=>'price',
		 	  'value'=>'price',
		 	  'action'=>"price"		 	  
		 	);		 
		 			
		 	$cols[]=array(
		 	  'key'=>'membership_expired',
		 	  'value'=>'membership_expired',
		 	  'action'=>"date"
		 	);		 
		 			 	
		 	$cols[]=array(
		 	  'key'=>'payment_type',
		 	  'value'=>'payment_type'		 	  
		 	);		 
		 	
		 	$cols[]=array(
		 	  'key'=>'status',
		 	  'value'=>'status'		 	  
		 	);	
		 	
		 	$cols[]=array(
		 	  'key'=>'date_created',
		 	  'value'=>'date_created',
		 	  'action'=>"datetime"		 	  
		 	);	
		 	
		 	$stmt = "
		 	 SELECT SQL_CALC_FOUND_ROWS
			 a.package_id,
			 a.price, a.membership_expired, a.payment_type, a.status, a.date_created,			 
			 IFNULL(b.title, a.package_id ) as package_name
			 			 
			 FROM {{package_trans}} a
			 LEFT JOIN {{packages}} b
			 ON
			 a.package_id = b.package_id
			 
			 WHERE a.merchant_id = ".q($id)."
			 [and]
			 [order]
			 [limit]
		 	";		 			 	
		 			 			 			 	
		 	if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data)){		 		
		 		$this->DataTablesData($feed_data);
		 		Yii::app()->end();
		 	}		 
		 $this->DataTablesNodata();		
	}	
	
	public function actionstore_hours()
	{
		$id = (integer) Yii::app()->merchant->merchant_id;
		    	
	 	$cols[]=array(
	 	  'key'=>'id',
	 	  'value'=>'id',		 	  
	 	);		 
	 	
	 	$cols[]=array(
	 	  'key'=>'day',
	 	  'value'=>'day',
	 	  'action'=>"format",
	 	  'format'=>'<h6>[day] <span class="badge ml-2 store_hours_[status_raw]">[status]</span></h6>
	 	  <p class="dim">	 	  
	 	  '.t("[start_time] - [end_time]").'<br/>
	 	  '.t("[start_time_pm] - [end_time_pm]").'<br/>
	 	  [custom_text]
	 	  </p>
	 	  ',
	 	  'format_value'=>array(
	 	    //'[day]'=>'day',
			 '[day]'=>array(
	 	       'value'=>'day',
		 	   'display'=>"t"
	 	     ),
	 	    '[status]'=>'status',
			'[status_raw]'=>'status',
	 	    '[custom_text]'=>'custom_text',
	 	    '[start_time]'=>array(
	 	       'value'=>'start_time',
		 	   'display'=>"time"
	 	     ),
	 	    '[end_time]'=>array(
	 	       'value'=>'end_time',
		 	   'display'=>"time"
	 	     ),
	 	    '[start_time_pm]'=>array(
	 	       'value'=>'start_time_pm',
		 	   'display'=>"time"
	 	     ),
	 	    '[end_time_pm]'=>array(
	 	       'value'=>'end_time_pm',
		 	   'display'=>"time"
	 	     ),
	 	  )
	 	);		
	 				 		 	
	 	$cols[]=array(
	 	  'key'=>'id',
	 	  'value'=>'id',
	 	  'action'=>"editdelete",
	 	  'id'=>'id'
	 	);
	 	
	 	$stmt = "
	 	 SELECT SQL_CALC_FOUND_ROWS
		 a.*
		 			 
		 FROM {{opening_hours}} a		 
		 WHERE a.merchant_id = ".q($id)."
		 [and]
		 [search]
		 ORDER BY day_of_week ASC
		 [limit]
	 	";		

	 	DatatablesTools::$action_edit_path = "merchant/store_hours_update";
	 	 	
	 	if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data)){		 		
	 		$this->DataTablesData($feed_data);
	 		Yii::app()->end();
	 	}		 
		$this->DataTablesNodata();		
	}
	
	public function actioncredit_card_list()
	{
		    $id = (integer) Yii::app()->merchant->merchant_id;
		    	
		 	$cols[]=array(
		 	  'key'=>'mt_id',
		 	  'value'=>'mt_id',		 	  
		 	);		 		 	
		 	
		 	$cols[]=array(
		 	  'key'=>'card_name',
		 	  'value'=>'card_name',		 
		 	  'action'=>"format",	
		 	  'format'=>'<h6>[card_name]</h6>
		 	  <p class="dim">
		 	   [credit_card_number]<br/>
		 	   '.t("Date Created. [date_created]").'
		 	  </p>
		 	  ',
		 	  'format_value'=>array(
		 	    '[card_name]'=>'card_name',
		 	    '[credit_card_number]'=>'credit_card_number',
		 	    '[date_created]'=>array(
	 	         'value'=>'date_created',
		 	     'display'=>"datetime"
	 	        )
		 	  )
		 	);		 	
		 	
		 	$cols[]=array(
		 	  'key'=>'date_created',
		 	  'value'=>'date_created',
		 	  'action'=>"editdelete",
		 	  'id'=>'mt_id'
		 	);	 	
		 	
		 	$stmt = "
		 	 SELECT SQL_CALC_FOUND_ROWS
			 a.*			 			
			 FROM {{merchant_cc}} a			 
			 WHERE a.merchant_id = ".q($id)."
			 [and]
			 [search]
			 [order]
			 [limit]
		 	";		 			 	
		 			 					 	
		DatatablesTools::$action_edit_path = "merchant/credit_card_update";
		 	 			 	
	 	if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data)){		 		
	 		$this->DataTablesData($feed_data);
	 		Yii::app()->end();
	 	}		 
		 $this->DataTablesNodata();		
	}		
	
	public function actionorder_list()
	{
	
		$cols=array(
		  array(
		   'key'=>'a.order_id',
	 	   'value'=>'logo',		 	  
	 	   'action'=>"merchant_logo", 	  
		  ),
		  array(
		   'key'=>'a.order_id',
	 	   'value'=>'order_id',		 	  	 	   	 
		  ),
		  array(
		   'key'=>'b.restaurant_name',
	 	   'value'=>'restaurant_name',		 	  	 	   
		  ),
		  array(
		   'key'=>'a.client_id',
	 	   'value'=>'customer_name',		
	 	   'action'=>"format",
	 	   'format'=>'<h6>[customer_name]</h6',
	 	   'format_value'=>array(
	 	     '[status]'=>'status',
	 	     '[customer_name]'=>'customer_name'
	 	   )
		  ),		  
		  array(
		   'key'=>'sub_total',
	 	   'value'=>'sub_total',		 	   
	 	   'action'=>"format",
	 	   'format'=>t("[total_items] items").' <span class="badge ml-2 order_status [status]">[status_title]</span>
	 	    <p class="dim">[payment_type]
	 	    <br>'.t("Transactions. [service_name]").'
	 	    <br>'.t("Total. [total_w_tax]").'	 	    
	 	    <br>'.t("Date. [date_created]").'	 	    
	 	    </p>
	 	  ',
	 	  'format_value'=>array(	
	 	     '[total_items]'=>'total_items',	 	     
	 	     '[payment_type]'=>'payment_name',	 
	 	     '[status]'=>'status',
	 	     '[status_title]'=>'status_title',
	 	     '[service_name]'=>'service_name',
	 	     '[total_w_tax]'=>array(
	 	       'value'=>'total_w_tax',
		 	   'display'=>"price"
	 	     ),
	 	     '[date_created]'=>array(
	 	       'value'=>'date_created',
		 	   'display'=>"datetime"
	 	     )
	 	    )	 	  
		  ),		  
		  array(
		   'key'=>'a.order_id',
	 	   'value'=>'order_id',		 	  
	 	   'action'=>"orders",
	 	   'id'=>'order_id'
		  ),
		);
	 	
	 	$stmt = "
	 	 SELECT SQL_CALC_FOUND_ROWS
		 a.order_id,a.order_id_token,a.merchant_id,a.client_id,a.sub_total,a.payment_type,a.total_w_tax,a.status,
		 a.date_created,
		 b.restaurant_name,b.logo,b.path,
		 concat(c.first_name,' ',c.last_name) as customer_name,
		 
		 IFNULL(d.payment_name, a.payment_type ) as payment_name,
		 
		 (
		  select count(*)
		  from {{order_details}}
		  where order_id=a.order_id
		 ) as total_items,
		 
		 IFNULL((
		 select description_trans from {{view_order_status}}
		 where
		 description=a.status 
		 and language=".q(Yii::app()->language)."
		 ),a.status) as status_title,

		 IFNULL(s.service_name,a.trans_type) as service_name		
	 		 
		 
		 FROM {{order}} a
		 LEFT JOIN {{merchant}} b
		 ON
		 a.merchant_id = b.merchant_id
		 
		 LEFT JOIN {{order_delivery_address}} c
		 ON
		 a.order_id = c.order_id
		 
		 LEFT JOIN {{payment_gateway}} d
		 ON
		 a.payment_type = d.payment_code
		 
		 LEFT JOIN {{services}} s
		 ON
		 a.trans_type = s.service_code
		 
		 WHERE 
		 a.merchant_id = ".q( Yii::app()->merchant->merchant_id )."
		 AND 
		 a.status NOT IN (".q( AttributesTools::initialStatus() ).")
		 [and]
		 [search]
		 [order]
		 [limit]
	 	";		 			 
	 		 		 	
	 	DatatablesTools::$action_edit_path = "merchant/order_update";
	 	DatatablesTools::$action_view_path = "merchant/order_view";
	 			 			 
	 	if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data)){		 		
	 		$this->DataTablesData($feed_data);
	 		Yii::app()->end();
	 	}
	 	$this->DataTablesNodata();	
		
	}
	
	public function actionorder_list_cancel()
	{
		$cols=array(
		  array(
		   'key'=>'logo',
	 	   'value'=>'logo',		 	  
	 	   'action'=>"merchant_logo", 	  
		  ),
		  array(
		   'key'=>'a.order_id',
	 	   'value'=>'order_id',		 	  	 	   	 
		  ),
		  array(
		   'key'=>'b.restaurant_name',
	 	   'value'=>'restaurant_name',		 	  	 	   
		  ),
		    array(
		   'key'=>'a.client_id',
	 	   'value'=>'customer_name',		
	 	   'action'=>"format",
	 	   'format'=>'<h6>[customer_name]</h6',
	 	   'format_value'=>array(
	 	     '[status]'=>'status',
	 	     '[customer_name]'=>'customer_name'
	 	   )
		  ),		  
		   array(
		   'key'=>'sub_total',
	 	   'value'=>'sub_total',		 	   
	 	   'action'=>"format",
	 	   'format'=>t("[total_items] items").' <span class="badge ml-2 order_status [status]">[status_title]</span>
	 	    <p class="dim">[payment_type]
	 	    <br>'.t("Transactions. [service_name]").'
	 	    <br>'.t("Total. [total_w_tax]").'	 	    
	 	    <br>'.t("Date. [date_created]").'	 	    
	 	    </p>
	 	  ',
	 	  'format_value'=>array(	
	 	     '[total_items]'=>'total_items',	 	     
	 	     '[payment_type]'=>'payment_name',	 
	 	     '[status]'=>'status',
	 	     '[status_title]'=>'status_title',
	 	     '[service_name]'=>'service_name',
	 	     '[total_w_tax]'=>array(
	 	       'value'=>'total_w_tax',
		 	   'display'=>"price"
	 	     ),
	 	     '[date_created]'=>array(
	 	       'value'=>'date_created',
		 	   'display'=>"datetime"
	 	     )
	 	    )	 	  
		  ),
		  array(
		   'key'=>'a.order_id',
	 	   'value'=>'order_id',		 	  
	 	   'action'=>"orders",
	 	   'id'=>'order_id'
		  ),
		);
	 	
	 	$stmt = "
	 	 SELECT SQL_CALC_FOUND_ROWS
		 a.order_id,a.order_id_token,a.merchant_id,a.client_id,a.sub_total,a.payment_type,a.total_w_tax,a.status,
		 a.request_cancel,a.request_cancel_status,
		 b.restaurant_name,b.logo,
		 concat(c.first_name,' ',c.last_name) as customer_name,
		 IFNULL(d.payment_name,".q(t('Not available')).") as payment_name,
		 
		 (
		  select count(*)
		  from {{order_details}}
		  where order_id=a.order_id
		 ) as total_items,
		 
		 IFNULL((
		 select description_trans from {{view_order_status}}
		 where
		 description=a.status 
		 and language=".q(Yii::app()->language)."
		 ),a.status) as status_title,

		 IFNULL(s.service_name,a.trans_type) as service_name	
		 		 
		 
		 FROM {{order}} a
		 LEFT JOIN {{merchant}} b
		 ON
		 a.merchant_id = b.merchant_id
		 
		 LEFT JOIN {{order_delivery_address}} c
		 ON
		 a.order_id = c.order_id
		 
		 LEFT JOIN {{payment_gateway}} d
		 ON
		 a.payment_type = d.payment_code
		 
		 LEFT JOIN {{services}} s
		 ON
		 a.trans_type = s.service_code
		 
		 WHERE a.status NOT IN (".q( AttributesTools::initialStatus() ).")
		 AND ( 
		  request_cancel=1
		  OR 
		  request_cancel_status='approved'
		 )
		 [and]
		 [search]
		 [order]
		 [limit]
	 	";		 			 
	 		 		 	
	 	DatatablesTools::$action_edit_path = "merchant/order_update";
	 	DatatablesTools::$action_view_path = "merchant/order_view";
	 			 			 
	 	if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data)){		 		
	 		$this->DataTablesData($feed_data);
	 		Yii::app()->end();
	 	}
	 	$this->DataTablesNodata();	
	}	
	
	public function actionorder_history()
	{		
		$id = Yii::app()->input->post('id');
		try {
			$resp = Orders::getHistory($id);
			$data = array();
			
			foreach ($resp as $val) {	
				$remarks='';			
				if(!empty($val['remarks2']) && !empty($val['remarks_args']) ){
					$args = json_decode($val['remarks_args'],true);		           	   
	           	    if(is_array($args) && count($args)>=1){
	           	   	  $new_arrgs = array();
	           	   	  foreach ($args as $remarks_args_key=>$remarks_args_val) {
	           	   	  	$new_arrgs[$remarks_args_key] = Yii::t("default",$remarks_args_val);
	           	   	  }	           	      
	           	      $remarks = t($val['remarks2'],$new_arrgs);
	           	    }
				}
				$data[]=array(
				 'status'=>t($val['status']),
				 'remarks'=>$remarks,
				 'date_created'=>Date_Formatter::dateTime($val['date_created'])
				);
			}			
			$this->code = 1; $this->msg = "ok";
			$this->details = array(
			  'next_action'=>"order_history",
			  'data'=>$data
			);
		} catch (Exception $e) {
			$this->msg = t($e->getMessage());
			$this->details = array(
			  'next_action'=>"clear_order_history"			  
			);
		}
		$this->responseJson();
	}	

	public function actiontime_managment_list()
	{
	    $merchant_id = (integer) Yii::app()->merchant->merchant_id;
	    	
	 	$cols[]=array(
	 	  'key'=>'group_id',
	 	  'value'=>'group_id',		 	  
	 	);		 		 	
	 	$cols[]=array(
	 	  'key'=>'transaction_type',
	 	  'value'=>'transaction_type',		
	 	  'action'=>"format",
	 	  'format'=>'<h6>[transaction_type] <span class="badge ml-2 post [status]">[status_title]</span></h6>
	 	  <p class="dim">
	 	  '.t("[start_time] to [end_time]").'<br/>
	 	  '.t("Nos. Order allowed. [number_order_allowed]").'<br/>
	 	  [days]<br/>
	 	  [order_status]
	 	  </p>
	 	  ',
	 	  'format_value'=>array(
	 	    '[transaction_type]'=>'transaction_type',
	 	    '[status]'=>'status',
		 	 '[status_title]'=>'status_title',		 
	 	    '[start_time]'=>array(
	 	       'value'=>'start_time',
		 	   'display'=>"time"
	 	     ),
	 	     '[end_time]'=>array(
	 	       'value'=>'end_time',
		 	   'display'=>"time"
	 	     ),
	 	     '[number_order_allowed]'=>'number_order_allowed',
	 	     '[days]'=>array(
	 	       'value'=>'days',
		 	   'display'=>"explode"
	 	     ),
	 	     '[order_status]'=>array(
	 	       'value'=>'order_status',
		 	   'display'=>"json"
	 	     ),
	 	  )
	 	);		 

	 	$cols[]=array(
	 	  'key'=>'group_id',
	 	  'value'=>'group_id',
	 	  'action'=>"editdelete",
	 	  'id'=>'group_id'
	 	); 	
	 			 
	 	$stmt = "
	 	SELECT a.id,a.group_id,a.transaction_type,a.start_time,
	 	a.end_time,a.number_order_allowed,a.order_status,a.status,
		GROUP_CONCAT(a.days) as days,
		
		IFNULL((
		 select title_trans from {{view_status_management}}
		 where
		 status=a.status and group_name='post'
		 and language=".q(Yii::app()->language)."
		 limit 0,1
		),a.status) as status_title	
		
		FROM
		{{order_time_management}} a
		WHERE
		merchant_id= ".q( $merchant_id )."
		[and]
		[search]
		GROUP BY a.group_id
		[order]
		[limit]
	 	"; 		
	 	 			 			
		DatatablesTools::$action_edit_path = "merchant/time_management_update";
		 	 			 	
	 	if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data)){	 		
	 		$this->DataTablesData($feed_data);
	 		Yii::app()->end();
	 	}		 
		 $this->DataTablesNodata();		
	}		
	
	public function actionbooking_list()
	{
		    $id = (integer) Yii::app()->merchant->merchant_id;
		    	
		 	$cols[]=array(
		 	  'key'=>'booking_id',
		 	  'value'=>'booking_id',		 	  
		 	);		 		 	
		 	
		 	$cols[]=array(
		 	  'key'=>'booking_name',
		 	  'value'=>'booking_name',		 
		 	  'action'=>"format",	
		 	  'format'=>'<h6>[booking_name] <span class="badge ml-2 booking [status]">[status_title]</span></h6>
		 	  <p class="dim">
		 	   '.t("Number Of Guests [number_guest]").'<br/>
		 	   '.t("Date Of Booking. [date_created]").'
		 	  </p>
		 	  ',
		 	  'format_value'=>array(
		 	    '[booking_name]'=>'booking_name',		 	    
		 	    '[number_guest]'=>'number_guest',	
		 	    '[status]'=>'status',		 	    
		 	    '[status_title]'=>'status_title',		 
		 	    '[date_created]'=>array(
	 	         'value'=>'date_created',
		 	     'display'=>"datetime"
	 	        )
		 	  )
		 	);		 	
		 	
		 	$cols[]=array(
		 	  'key'=>'date_created',
		 	  'value'=>'date_created',
		 	  'action'=>"editdelete",
		 	  'id'=>'booking_id'
		 	);	 	
		 	
		 	$stmt = "
		 	 SELECT SQL_CALC_FOUND_ROWS
			 a.*,

			 IFNULL((
			 select title_trans from {{view_status_management}}
			 where
			 status=a.status and group_name='booking'
			 and language=".q(Yii::app()->language)."
			 limit 0,1
			 ),a.status) as status_title	
			 			
			 FROM {{bookingtable}} a			 
			 WHERE a.merchant_id = ".q($id)."
			 [and]
			 [search]
			 [order]
			 [limit]
		 	";		 			 	
		 			 			
		DatatablesTools::$action_edit_path = "booking/update";
		 	 			 	
	 	if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data)){		 		
	 		$this->DataTablesData($feed_data);
	 		Yii::app()->end();
	 	}		 
		 $this->DataTablesNodata();		
	}			
	
    public function actiontimeslot_booking()
	{
		    $id = (integer) Yii::app()->merchant->merchant_id;
		    	
		 	$cols[]=array(
		 	  'key'=>'id',
		 	  'value'=>'id',		 	  
		 	);		 		 	
		 	
		 	$cols[]=array(
		 	  'key'=>'card_name',
		 	  'value'=>'days',		 
		 	  'action'=>"format",	
		 	  'format'=>'<h6>[days]</h6>
		 	  <p class="dim">
		 	   '.t("[start_time] - [end_time]").'<br/>
		 	   '.t("Nos. Order allowed. [number_order_allowed]").'<br/>
		 	  </p>
		 	  ',
		 	  'format_value'=>array(
		 	    '[days]'=>'days',	
		 	    '[number_order_allowed]'=>'number_order_allowed',
		 	    '[start_time]'=>array(
	 	          'value'=>'start_time',
			 	   'display'=>"time"
		 	     ),
		 	    '[end_time]'=>array(
		 	       'value'=>'end_time',
			 	   'display'=>"time"
		 	     ),
		 	    //	 	    
		 	  )
		 	);		 	
		 	
		 	$cols[]=array(
		 	  'key'=>'date_created',
		 	  'value'=>'date_created',
		 	  'action'=>"editdelete",
		 	  'id'=>'id'
		 	);	 	
		 	
		 	$stmt = "
		 	 SELECT SQL_CALC_FOUND_ROWS
			 a.*			 			
			 FROM {{timeslot_booking}} a			 
			 WHERE a.merchant_id = ".q($id)."
			 [and]
			 [search]
			 [order]
			 [limit]
		 	";		 			 	
		 			 			
		DatatablesTools::$action_edit_path = "booking/timeslot_update";
		 	 			 	
	 	if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data)){		 		
	 		$this->DataTablesData($feed_data);
	 		Yii::app()->end();
	 	}		 
		 $this->DataTablesNodata();		
	}			
	
	public function actionsize_list()
	{
		    $id = (integer) Yii::app()->merchant->merchant_id;
		    	
		 	$cols[]=array(
		 	  'key'=>'size_id',
		 	  'value'=>'size_id',		 	  
		 	);		 		 	
		 	
		 	$cols[]=array(
		 	  'key'=>'new_size_name',
		 	  'value'=>'new_size_name',		 
		 	  'action'=>"format",	
		 	  'format'=>'<h6>[new_size_name] <span class="badge ml-2 post [status]">[status_title]</span></h6>
		 	  <p class="dim">		 	   
		 	  </p>
		 	  ',
		 	  'format_value'=>array(
		 	    '[new_size_name]'=>'new_size_name',
		 	    '[status]'=>'status',
		 	    '[status_title]'=>'status_title',
		 	  )
		 	);		 	
		 	
		 	$cols[]=array(
		 	  'key'=>'date_created',
		 	  'value'=>'date_created',
		 	  'action'=>"editdelete",
		 	  'id'=>'size_id'
		 	);	 	
		 	
		 	$stmt = "
		 	 SELECT SQL_CALC_FOUND_ROWS
			 a.*,
			 
			  
			 IFNULL((
			 select title_trans from {{view_status_management}}
			 where
			 status=a.status and group_name='post'
			 and language=".q(Yii::app()->language)."
			 limit 0,1
			 ),a.status) as status_title,

			 IF(COALESCE(NULLIF(b.size_name, ''), '') = '', a.size_name, b.size_name) as new_size_name
			 	
			 FROM {{size}} a			 
			 left JOIN (
				SELECT size_name,size_id FROM {{size_translation}} where language=".q(Yii::app()->language)."
			 ) b 
			 ON a.size_id = b.size_id
			
			 WHERE a.merchant_id = ".q($id)."
			 [and]
			 [search]
			 [order]
			 [limit]
		 	";		 			 	
		 			 			
		DatatablesTools::$action_edit_path = "attrmerchant/size_update";
		 	 			 	
	 	if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data)){		 		
	 		$this->DataTablesData($feed_data);
	 		Yii::app()->end();
	 	}		 
		 $this->DataTablesNodata();		
	}			
	
	public function actioningredients_list()
	{
		    $id = (integer) Yii::app()->merchant->merchant_id;
		    	
		 	$cols[]=array(
		 	  'key'=>'ingredients_id',
		 	  'value'=>'ingredients_id',		 	  
		 	);		 		 	
		 	
		 	$cols[]=array(
		 	  'key'=>'ingredients_name',
		 	  'value'=>'ingredients_name',		 
		 	  'action'=>"format",	
		 	  'format'=>'<h6>[ingredients_name] <span class="badge ml-2 post [status]">[status_title]</span></h6>
		 	  <p class="dim">		 	   
		 	  </p>
		 	  ',
		 	  'format_value'=>array(
		 	    '[ingredients_name]'=>'ingredients_name',
		 	    '[status]'=>'status',
		 	    '[status_title]'=>'status_title',
		 	  )
		 	);		 	
		 	
		 	$cols[]=array(
		 	  'key'=>'date_created',
		 	  'value'=>'date_created',
		 	  'action'=>"editdelete",
		 	  'id'=>'ingredients_id'
		 	);	 	
		 	
		 	$stmt = "
		 	 SELECT SQL_CALC_FOUND_ROWS
			 a.*,
			 
			  
			 IFNULL((
			 select title_trans from {{view_status_management}}
			 where
			 status=a.status and group_name='post'
			 and language=".q(Yii::app()->language)."
			 limit 0,1
			 ),a.status) as status_title,

			 IF(COALESCE(NULLIF(b.ingredients_name , ''), '') = '', a.ingredients_name , b.ingredients_name) as ingredients_name 
			 	
			 FROM {{ingredients}} a			 

			 left JOIN (
				SELECT ingredients_id,ingredients_name FROM {{ingredients_translation}} where language=".q(Yii::app()->language)."
			 ) b 
			 ON a.ingredients_id = b.ingredients_id

			 WHERE a.merchant_id = ".q($id)."
			 [and]
			 [search]
			 [order]
			 [limit]
		 	";		 	
		 			 			
		DatatablesTools::$action_edit_path = "attrmerchant/ingredients_update";
		 	 			 	
	 	if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data)){		 		
	 		$this->DataTablesData($feed_data);
	 		Yii::app()->end();
	 	}		 
		 $this->DataTablesNodata();		
	}				
	
	public function actioncookingref_list()
	{
		    $id = (integer) Yii::app()->merchant->merchant_id;
		    	
		 	$cols[]=array(
		 	  'key'=>'cook_id',
		 	  'value'=>'cook_id',		 	  
		 	);		 		 	
		 	
		 	$cols[]=array(
		 	  'key'=>'cooking_name',
		 	  'value'=>'cooking_name',		 
		 	  'action'=>"format",	
		 	  'format'=>'<h6>[cooking_name] <span class="badge ml-2 post [status]">[status_title]</span></h6>
		 	  <p class="dim">		 	   
		 	  </p>
		 	  ',
		 	  'format_value'=>array(
		 	    '[cooking_name]'=>'cooking_name',
		 	    '[status]'=>'status',
		 	    '[status_title]'=>'status_title',
		 	  )
		 	);		 	
		 	
		 	$cols[]=array(
		 	  'key'=>'date_created',
		 	  'value'=>'date_created',
		 	  'action'=>"editdelete",
		 	  'id'=>'cook_id'
		 	);	 	
		 	
		 	$stmt = "
		 	 SELECT SQL_CALC_FOUND_ROWS
			 a.*,	
			  
			 IFNULL((
			 select title_trans from {{view_status_management}}
			 where
			 status=a.status and group_name='post'
			 and language=".q(Yii::app()->language)."
			 limit 0,1
			 ),a.status) as status_title,

			 IF(COALESCE(NULLIF(b.cooking_name, ''), '') = '', a.cooking_name, b.cooking_name) as cooking_name
			 	
			 FROM {{cooking_ref}} a			 
			 left JOIN (
				SELECT cook_id,cooking_name FROM {{cooking_ref_translation}} where language=".q(Yii::app()->language)."
			 ) b 
			 ON a.cook_id = b.cook_id

			 WHERE a.merchant_id = ".q($id)."
			 [and]
			 [search]
			 [order]
			 [limit]
		 	";		 
		 			 			
		DatatablesTools::$action_edit_path = "attrmerchant/cookingref_update";
		 	 			 	
	 	if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data)){		 		
	 		$this->DataTablesData($feed_data);
	 		Yii::app()->end();
	 	}		 
		 $this->DataTablesNodata();		
	}					
	
	public function actioncategory_list()
	{
		    $id = (integer) Yii::app()->merchant->merchant_id;
		    	
		 	$cols[]=array(
		 	  'key'=>'a.cat_id',
		 	  'value'=>'photo',
		 	  'action'=>"item_photo", 	  
		 	);		 		 	

			 $cols[]=array(
				'key'=>'a.available',
				'value'=>'available',
				'action'=>"checkbox", 	  
				'id'=>'cat_id',
				'class'=>"set_category_available"
			  );		 		 	
		 	
		 	$cols[]=array(
		 	  'key'=>'a.category_name',
		 	  'value'=>'category_name',		 
		 	  'action'=>"format",	
		 	  'format'=>'<h6>[category_name] <span class="badge ml-2 post [status]">[status_title]</span></h6>
		 	  <p class="dim">		
		 	  '.t("[items] Items").'<br/>
		 	  '.t("Date. [date_created]").'<br/>
		 	  [category_description]
		 	  </p>
		 	  ',
		 	  'format_value'=>array(
		 	    '[category_name]'=>'category_name',
		 	    '[status]'=>'status',
		 	    '[status_title]'=>'status_title',
		 	    '[items]'=>'items',
		 	    '[category_description]'=>array(
		 	       'value'=>'category_description',
			 	   'display'=>"short_text"
		 	    ),
		 	    '[date_created]'=>array(
		 	       'value'=>'date_created',
			 	   'display'=>"datetime"
		 	     )
		 	  )
		 	);		 	
		 	
		 	$cols[]=array(
		 	  'key'=>'date_created',
		 	  'value'=>'date_created',
		 	  'action'=>"editdelete",
		 	  'id'=>'cat_id'
		 	);	 	
		 	
		 	$stmt = "
		 	 SELECT SQL_CALC_FOUND_ROWS
			 a.*,			 
			 
			 (
			 select count(*) from {{item_relationship_category}}
			 where cat_id = a.cat_id
			 and merchant_id = a.merchant_id
			 and item_id in (
			   select item_id from {{item}}
			 )
			 ) as items,
			  
			 IFNULL((
			 select title_trans from {{view_status_management}}
			 where
			 status=a.status and group_name='post'
			 and language=".q(Yii::app()->language)."
			 limit 0,1
			 ),a.status) as status_title,

			 IF(COALESCE(NULLIF(b.category_name, ''), '') = '', a.category_name, b.category_name) as category_name
			 	
			 FROM {{category}} a			 
			 left JOIN (
				SELECT cat_id,category_name FROM {{category_translation}} where language=".q(Yii::app()->language)."
			 ) b 
			 ON a.cat_id = b.cat_id

			 WHERE a.merchant_id = ".q($id)."
			 [and]
			 [search]
			 [order]
			 [limit]
		 	";		 			 	
		 			 					 	
		DatatablesTools::$action_edit_path = "food/category_update";		
		 	 			 			
	 	if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data)){		 		
	 		$this->DataTablesData($feed_data);
	 		Yii::app()->end();
	 	}		 
		 $this->DataTablesNodata();		
	}					
	
	public function actionaddoncategory_list()
	{
		    $id = (integer) Yii::app()->merchant->merchant_id;
		    	
		 	$cols[]=array(
		 	  'key'=>'a.subcat_id',
		 	  'value'=>'featured_image',
		 	  'action'=>"item_photo", 	  
		 	);		 		 
			
			 $cols[]=array(
				'key'=>'a.available',
				'value'=>'available',
				'action'=>"checkbox", 	  
				'id'=>'subcat_id',
				'class'=>"set_subcategory_available"
			  );		 
		 	
		 	$cols[]=array(
		 	  'key'=>'a.subcategory_name',
		 	  'value'=>'subcategory_name',		 
		 	  'action'=>"format",	
		 	  'format'=>'<h6>[subcategory_name] <span class="badge ml-2 post [status]">[status_title]</span></h6>
		 	  <p class="dim">		
		 	  '.t("[items] Items").'<br/>
		 	  '.t("Date. [date_created]").'<br/>	
		 	  [subcategory_description]
		 	  </p>
		 	  ',
		 	  'format_value'=>array(
		 	    '[subcategory_name]'=>'subcategory_name',
		 	    '[status]'=>'status',
		 	    '[status_title]'=>'status_title',
		 	    '[items]'=>'items',
		 	    '[subcategory_description]'=>array(
		 	       'value'=>'subcategory_description',
			 	   'display'=>"short_text"
		 	    ),
		 	    '[date_created]'=>array(
		 	       'value'=>'date_created',
			 	   'display'=>"datetime"
		 	     )
		 	  )
		 	);		 	
		 	
		 	$cols[]=array(
		 	  'key'=>'date_created',
		 	  'value'=>'date_created',
		 	  'action'=>"editdelete",
		 	  'id'=>'subcat_id'
		 	);	 	
		 	
		 	$stmt = "
		 	 SELECT SQL_CALC_FOUND_ROWS
			 a.*,
			 
			 (
			 select count(*) from {{subcategory_item_relationships}}
			 where subcat_id = a.subcat_id
			 ) as items,			 
			  
			 IFNULL((
			 select title_trans from {{view_status_management}}
			 where
			 status=a.status and group_name='post'
			 and language=".q(Yii::app()->language)."
			 limit 0,1
			 ),a.status) as status_title,

			 IF(COALESCE(NULLIF(b.subcategory_name, ''), '') = '', a.subcategory_name, b.subcategory_name) as subcategory_name
			 	
			 FROM {{subcategory}} a			
			 left JOIN (
				SELECT subcategory_name,subcat_id FROM {{subcategory_translation}} where language=".q(Yii::app()->language)."
			 ) b 
			 ON a.subcat_id = b.subcat_id

			 WHERE a.merchant_id = ".q($id)."
			 [and]
			 [search]
			 [order]
			 [limit]
		 	";		 			 	
		 			 			
		DatatablesTools::$action_edit_path = "food/addoncategory_update";
		 	 			 		
	 	if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data)){		 		
	 		$this->DataTablesData($feed_data);
	 		Yii::app()->end();
	 	}		 
		 $this->DataTablesNodata();		
	}					
		
	public function actionaddonitem_list()
	{
		    $merchant_id = (integer) Yii::app()->merchant->merchant_id;
		    	
		 	$cols[]=array(
		 	  'key'=>'a.sub_item_id',
		 	  'value'=>'photo',
		 	  'action'=>"item_photo", 	  
		 	);	

			 $cols[]=array(
				'key'=>'a.available',
				'value'=>'available',
				'action'=>"checkbox", 	  
				'id'=>'sub_item_id',
				'class'=>"set_subcategoryitem_available"
			  );		 		 			 	
		 	
		 	$cols[]=array(
		 	  'key'=>'a.sub_item_name',
		 	  'value'=>'sub_item_name',		 
		 	  'action'=>"format",	
		 	  'format'=>'<h6>[sub_item_name] <span class="badge ml-2 post [status]">[status_title]</span></h6>
		 	  <p class="dim">				 	   		 	   
		 	   '.t("Date. [date_created]").'<br/>	
		 	   [item_description]
		 	  </p>
		 	  ',
		 	  'format_value'=>array(
		 	    '[sub_item_name]'=>'sub_item_name',		
		 	    '[status]'=>'status',
		 	    '[status_title]'=>'status_title',
		 	    '[addon_category]'=>'addon_category',
		 	    '[item_description]'=>array(
		 	       'value'=>'item_description',
			 	   'display'=>"short_text"
		 	    ),
		 	    '[date_created]'=>array(
	 	         'value'=>'date_created',
		 	     'display'=>"datetime"
	 	        ),
		 	    '[price]'=>array(
	 	         'value'=>'price',
		 	     'display'=>"price"
	 	        )
		 	  )
		 	);		 	
		 	
		 	$cols[]=array(
		 	  'key'=>'category',
		 	  'value'=>'addon_category',	
		 	  'action'=>"format",		 
		 	  'format'=>'<p>[addon_category]</p>',
		 	  'format_value'=>array(
		 	    '[addon_category]'=>'addon_category'
		 	  )
		 	);	
		 	
		 	$cols[]=array(
		 	  'key'=>'price',
		 	  'value'=>'price',	
		 	  'action'=>"format",		 
		 	  'format'=>'<b>[price]</b>',
		 	  'format_value'=>array(
		 	     '[price]'=>array(
	 	         'value'=>'price',
		 	     'display'=>"price"
	 	        )
		 	  )
		 	);	
		 	
		 	$cols[]=array(
		 	  'key'=>'date_created',
		 	  'value'=>'date_created',
		 	  'action'=>"editdelete",
		 	  'id'=>'sub_item_id'
		 	);	 	
		 	
		 $stmt = "
	 	 SELECT SQL_CALC_FOUND_ROWS
		 a.*,
		 			  
		 IFNULL((
		 select title_trans from {{view_status_management}}
		 where
		 status=a.status and group_name='post'
		 and language=".q(Yii::app()->language)."
		 limit 0,1
		 ),a.status) as status_title,
		 
		 (
		 select GROUP_CONCAT(subcategory_name)
		 from {{subcategory}} b where subcat_id IN 
		  (
		    select subcat_id from {{subcategory_item_relationships}}
		    where subcat_id = b.subcat_id and sub_item_id = a.sub_item_id
		  )
		 ) as addon_category,

		 IF(COALESCE(NULLIF(b.sub_item_name, ''), '') = '', a.sub_item_name, b.sub_item_name) as sub_item_name
		 	
		 FROM {{subcategory_item}} a			 

		 left JOIN (
			SELECT sub_item_name,sub_item_id FROM {{subcategory_item_translation}} where language=".q(Yii::app()->language)."
		 ) b 
		 ON a.sub_item_id = b.sub_item_id
		 
		 WHERE a.merchant_id = ".q($merchant_id)."
		 [and]
		 [search]
		 [order]
		 [limit]
	 	";		 	
		//dump($stmt); 
		 			 			
		DatatablesTools::$action_edit_path = "food/addonitem_update";		

	 	if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data)){		 		
	 		$this->DataTablesData($feed_data);
	 		Yii::app()->end();
	 	}		 
		 $this->DataTablesNodata();		
	}			
	
	public function actionitem_list()
	{
		    $merchant_id = (integer) Yii::app()->merchant->merchant_id;
		    	
		 	$cols[]=array(
		 	  'key'=>'item_id',
		 	  'value'=>'photo',		 	  
		 	  'action'=>"item_photo", 	  
		 	);	
		 	
		 	$cols[]=array(
		 	   'key'=>'available',
		 	   'value'=>'available',
		 	   'id'=>'item_id',
		 	   'action'=>'checkbox',
		 	   'class'=>"set_item_available"
		 	);	
		 			 	
		 	$cols[]=array(
		 	  'key'=>'item_name',
		 	  'value'=>'item_name',		 
		 	  'action'=>"format",	
		 	  'format'=>'<h6>[item_name] <span class="badge ml-2 post [status]">[status_title]</span></h6>
		 	  <p class="dim">				 	   		 	   
		 	   '.t("Last Modified. [date_modified]").'<br/>
		 	   '.t("SKU#[sku]").'	   
		 	  </p>
		 	  ',
		 	  'format_value'=>array(
		 	    '[item_name]'=>'item_name',		
		 	    '[status]'=>'status',
		 	    '[status_title]'=>'status_title',		 	    
		 	    '[sku]'=>'sku',
		 	    '[item_short_description]'=>array(
		 	       'value'=>'item_short_description',
			 	   'display'=>"short_text"
		 	    ),
		 	    '[date_modified]'=>array(
	 	         'value'=>'date_modified',
		 	     'display'=>"datetime"
	 	        )		 	    
		 	  )
		 	);		 	
		 	
		 	$cols[]=array(
		 	  'key'=>'item_id',
		 	  'value'=>'category_group',		 	  		 	  
		 	  'action'=>"format",	
		 	  'format'=>'<p class="font-weight-bold">[category_group]</p>',
		 	  'format_value'=>array(
		 	    '[category_group]'=>array(
		 	       'value'=>'category_group',
			 	   'display'=>"short_text"
		 	    ),
		 	  )
		 	);	
		 	
		 	$cols[]=array(
		 	  'key'=>'item_id',
		 	  'value'=>'item_price',		 	  		 	  
		 	  'action'=>"format",	
		 	  'format'=>'[item_price]',
		 	  'format_value'=>array(
		 	    '[item_price]'=>array(
		 	       'value'=>'item_price',
			 	   'display'=>"price"
		 	    ),
		 	  )
		 	);	
		 			 	
		 	$cols[]=array(
		 	  'key'=>'date_created',
		 	  'value'=>'date_created',
		 	  'action'=>"editdelete",
		 	  'primary_key'=>"item_id",
		 	  'id'=>'item_id'
		 	);	 	
		 	
		 $stmt = "
	 	 SELECT SQL_CALC_FOUND_ROWS
		 a.*,
		 
		 (
		 select GROUP_CONCAT(category_name)
		 from {{category}} b where cat_id IN 
		  (
		    select cat_id from {{item_relationship_category}}
		    where cat_id = b.cat_id and item_id = a.item_id
		  )
		 ) as category_group,
		 
		 (
		 select max(price) from {{item_relationship_size}}
		 where item_id = a.item_id
		 limit 0,1
		 ) as item_price,
		 			  
		 IFNULL((
		 select title_trans from {{view_status_management}}
		 where
		 status=a.status and group_name='post'
		 and language=".q(Yii::app()->language)."
		 limit 0,1
		 ),a.status) as status_title	
		 	
		 FROM {{item}} a			 
		 WHERE a.merchant_id = ".q($merchant_id)."
		 [and]
		 [search]
		 [order]
		 [limit]
	 	";		 	
		 			 			
		DatatablesTools::$action_edit_path = "food/item_update";		
		 	 			 			
	 	if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data)){		 		
	 		$this->DataTablesData($feed_data);
	 		Yii::app()->end();
	 	}		 
		 $this->DataTablesNodata();		
	}	
	
	public function actionitemprice_list()
	{
		    $merchant_id = (integer) Yii::app()->merchant->merchant_id;
		    $item_id = (integer) Yii::app()->input->post('item_id');
		    			 	
		 	$cols[]=array(
		 	  'key'=>'price',
		 	  'value'=>'price',		 
		 	  'action'=>"format",	
		 	  'format'=>'<h6>'.t("[price] [size_name]").'</h6>		 	   
		 	  <p class="dim">
		 	   [item_available]<br/>
		 	   '.t("SKU#[sku]").'
		 	  </p>
		 	  ',
		 	  'format_value'=>array(
		 	    '[size_name]'=>'size_name',
		 	    '[item_available]'=>'item_available',
		 	    '[sku]'=>'sku',
		 	    '[price]'=>array(
		 	       'value'=>'price',
			 	   'display'=>"price"
		 	     ),
		 	  )
		 	);		 
		 	
		 	$cols[]=array(
		 	  'key'=>'cost_price',
		 	  'value'=>'cost_price',		 
		 	  'action'=>"format",	
		 	  'format'=>'<h6>[cost_price]</h6>
		 	   <p class="dim">		 	   
		 	   </p>
		 	  ',
		 	  'format_value'=>array(		 	    
		 	    '[cost_price]'=>array(
		 	       'value'=>'cost_price',
			 	   'display'=>"price"
		 	     ),
		 	  )
		 	);		
		 	
		 	$cols[]=array(
		 	  'key'=>'discount',
		 	  'value'=>'discount',		 
		 	  'action'=>"format",	
		 	  'format'=>'<h6>[discount]</h6>
		 	   <p class="dim">		 	   
		 	   </p>
		 	  ',
		 	  'format_value'=>array(		 	    
		 	    '[discount]'=>array(
		 	       'value'=>'discount',
		 	       'discount_type'=>"discount_type",
			 	   'display'=>"fixed_discount"
		 	     ),
		 	  )
		 	);		 		 		
		 	
		 	$cols[]=array(
		 	  'key'=>'created_at',
		 	  'value'=>'created_at',
		 	  'action'=>"editdelete",
		 	  'id'=>'item_size_id'
		 	);	 	
		 	
		 	$stmt = "
		 	 SELECT SQL_CALC_FOUND_ROWS
			 a.*,
			 IF(a.available>0,'".t("Available")."', '".t("Not Available")."') as item_available,
			 
			 IFNULL(b.size_name,'') as size_name  
			 			 			
			 FROM {{item_relationship_size}} a
			 LEFT JOIN {{size}} b
			 ON
			 a.size_id = b.size_id
			 
			 
			 WHERE a.merchant_id = ".q($merchant_id)."
			 AND item_id = ".q($item_id)."
			 [and]
			 [search]
			 [order]
			 [limit]
		 	";		 			 	
		 			 			
		DatatablesTools::$action_edit_path = "food/itemprice_update/item_id/$item_id";
		 	 			 	
	 	if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data)){		 		
	 		$this->DataTablesData($feed_data);
	 		Yii::app()->end();
	 	}		 
		 $this->DataTablesNodata();		
	}			
		
	public function actionitemaddon_list()
	{
		    $merchant_id = (integer) Yii::app()->merchant->merchant_id;
		    $item_id = (integer) Yii::app()->input->post('item_id');
		    		    
		 	$cols[]=array(
		 	  'key'=>'id',
		 	  'value'=>'subcategory_name',		 	  
		 	  'action'=>'format',
		 	  'format'=>'<h6>[subcategory_name]</h6><p>[price] [size_name]</p>',
		 	  'format_value'=>array(
		 	    '[subcategory_name]'=>'subcategory_name',
		 	    '[size_name]'=>'size_name',
		 	    '[price]'=>array(
		 	      'value'=>'price',
		 	      'display'=>'price'
		 	    )
		 	  )
		 	);		 		 	
		 	
		 	$cols[]=array(
		 	  'key'=>'multi_option',
		 	  'value'=>'multi_option',	
			  'action'=>'format',	
			  'format'=>'[multi_option]',
			  'format_value'=>[
				'[multi_option]'=>[
					'value'=>'multi_option',
					'display'=>'t'
			    ],
			  ]
		 	);	
		 	
		 	$cols[]=array(
		 	  'key'=>'multi_option_value',
		 	  'value'=>'multi_option_value',		 	  
		 	);	
		 	
		 	$cols[]=array(
		 	  'key'=>'require_addon',
		 	  'value'=>'require_addon',		 	  
		 	);	
		 			 	
		 	$cols[]=array(
		 	  'key'=>'id',
		 	  'value'=>'id',
		 	  'action'=>"editdelete",
		 	  'id'=>'id'
		 	);	 	
		 	
		 	$stmt = "
		 	 SELECT SQL_CALC_FOUND_ROWS
			 a.*,	
			 IF(a.require_addon>0,".q(t("Yes")).",".q(t("No")).") as require_addon,
			 IFNULL(b.subcategory_name, '' ) as subcategory_name,
			 
			 IFNULL(c.price,'') as price,
			 IFNULL(c.size_name,'') as size_name
			 
			 FROM {{item_relationship_subcategory}} a	
			 JOIN {{subcategory}} b
			 ON 
			 a.subcat_id  = b.subcat_id
			 
			 JOIN {{view_item_size}} c
			 ON 
			 a.item_size_id  = c.item_size_id
			 
			 WHERE a.merchant_id = ".q($merchant_id)."			 
			 AND a.item_id = ".q($item_id)."
			 [and]
			 [search]
			 [order]
			 [limit]
		 	";		 			 	
		 			 			
		DatatablesTools::$action_edit_path = "food/itemaddon_update/item_id/$item_id";
		 	 			 	
	 	if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data)){		 		
	 		$this->DataTablesData($feed_data);
	 		Yii::app()->end();
	 	}		 
		 $this->DataTablesNodata();		
	}		
	
	public function actionupdate_item_available()
	{
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;
		$item_id = (integer) Yii::app()->input->post('id');
		$checked = (integer) Yii::app()->input->post('checked');
		
		$model = AR_item::model()->find('merchant_id=:merchant_id AND item_id=:item_id', 
		array(':merchant_id'=>$merchant_id, ':item_id'=>$item_id ));
		
		$model->scenario = 'update_item_available';
		
		if($model){
			$model->available = (integer)$checked;
			$model->save();
			$this->code = 1; $this->msg = "OK";
			$this->details = array(
			  'next_action'=>"silent",			  
			);
		} else $this->msg = t(HELPER_RECORD_NOT_FOUND);
		$this->responseJson();
	}
	
	public function actioncharges_table()
	{
		    $merchant_id = (integer) Yii::app()->merchant->merchant_id;		    
		    		    
		 	$cols[]=array(
		 	  'key'=>'distance_from',
		 	  'value'=>'shipping_type',	
		 	  'action'=>"format",		 
		 	  'format'=>'<h6>'.t("[shipping_type]").'
		 	  <p class="dim mt-1">
		 	  '.t("[distance_from] - [distance_to] [shipping_units]").'<br/>
		 	  '.t("[estimation] mins").'
		 	  </p>
		 	  </h6>',
		 	  'format_value'=>array(		 	     	 	    
		 	     '[estimation]'=>'estimation',
		 	     '[distance_from]'=>array(
	 	           'value'=>'distance_from',
		 	       'display'=>"distance"
	 	         ),
	 	         '[distance_to]'=>array(
	 	           'value'=>'distance_to',
		 	       'display'=>"distance"
	 	         ),
	 	         '[shipping_units]'=>array(
	 	           'value'=>'shipping_units',
		 	       'display'=>"pattern",
		 	       'data'=>AttributesTools::unit()
	 	         ),
	 	         '[shipping_type]'=>array(
	 	           'value'=>'shipping_type',
		 	       'display'=>"pattern",
		 	       'data'=>AttributesTools::ShippingType()
	 	         )
		 	  )
		 	);	
		 	
		 	$cols[]=array(
		 	  'key'=>'distance_price',
		 	  'value'=>'distance_price',	
		 	  'action'=>"format",		 
		 	  'format'=>'<b>[distance_price]</b>',
		 	  'format_value'=>array(
		 	     '[distance_price]'=>array(
	 	           'value'=>'distance_price',
		 	       'display'=>"price"
	 	          )
		 	  )
		 	);	
		 	
		 	$cols[]=array(
		 	  'key'=>'minimum_order',
		 	  'value'=>'minimum_order',	
		 	  'action'=>"format",		 
		 	  'format'=>'<b>[minimum_order]</b>',
		 	  'format_value'=>array(
		 	     '[minimum_order]'=>array(
	 	           'value'=>'minimum_order',
		 	       'display'=>"price"
	 	          )
		 	  )
		 	);			 	
		 	
		 	$cols[]=array(
		 	  'key'=>'maximum_order',
		 	  'value'=>'maximum_order',	
		 	  'action'=>"format",		 
		 	  'format'=>'<b>[maximum_order]</b>',
		 	  'format_value'=>array(
		 	     '[maximum_order]'=>array(
	 	           'value'=>'maximum_order',
		 	       'display'=>"price"
	 	          )
		 	  )
		 	);		
		 			 	
		 	$cols[]=array(
		 	  'key'=>'id',
		 	  'value'=>'id',
		 	  'action'=>"editdelete",
		 	  'id'=>'id'
		 	);	 	
		 	
		 	$cols[]=array(
		 	  'key'=>'shipping_type',
		 	  'value'=>'shipping_type',		 	  
		 	  'shipping_type'=>'shipping_type'
		 	);	 	
		 	
		 	$stmt = "
		 	 SELECT SQL_CALC_FOUND_ROWS
			 a.*			 
			 FROM {{shipping_rate}} a				 
			 WHERE a.merchant_id = ".q($merchant_id)."	
			 and a.charge_type='dynamic'	
			 and service_code='delivery'
			 [and]
			 [search]
			 [order]
			 [limit]
		 	";		 			 	
		 			 					
		DatatablesTools::$action_edit_path = "services/chargestable_update/";
		//DatatablesTools::$debug = true;
		 	 			 	
	 	if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data)){		 		
	 		$this->DataTablesData($feed_data);
	 		Yii::app()->end();
	 	}		 
		 $this->DataTablesNodata();		
	}			
	
	public function actioncoupon_list()
	{
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;		    
		
		$cols=array(
		  array(
		   'key'=>'voucher_id',
	 	   'value'=>'voucher_id'	 	   
		  ),		  
		   array(
		    'key'=>'voucher_name',
	 	    'value'=>'voucher_name',	
	 	    'action'=>"format",	 	  
	 	    'format'=>'<h6>[voucher_name] <span class="badge ml-2 post [status]">[status_title]</span></h6>	 	    	 	    
	 	    <p class="dim">'.t("Voucher Type").'. 
	 	    [voucher_type]<br>'.
	 	    t("Discount").'. [amount]<br>'.
	 	    t("Expiration").'. [expiration]<br/>
	 	    '.t("Last Update. [date_modified]").'
	 	    </p>
	 	    ',
	 	    'format_value'=>array(
	 	     '[voucher_name]'=>'voucher_name',
	 	     '[voucher_type]'=>[
				'value'=>'voucher_type',
				'display'=>'t'
			 ],
	 	     '[status]'=>'status',
	 	     '[status_title]'=>'status_title',
	 	     '[amount]'=>array(
	 	       'value'=>'amount',
	 	       'display'=>"price"
	 	     ),	
	 	     '[expiration]'=>array(
	 	       'value'=>'expiration',
	 	       'display'=>"date"
	 	     ),	
	 	      '[date_modified]'=>array(
	 	         'value'=>'date_modified',
		 	     'display'=>"datetime"
	 	      ),	
	 	    )
		  ),
		  array(
		   'key'=>'voucher_id',
	 	   'value'=>'total_used',	
		  ),
		  array(
		   'key'=>'voucher_id',
	 	   'value'=>'voucher_id',		 	  
	 	   'action'=>"editdelete",
	 	   'id'=>'voucher_id'
		  ),
		);
	 	
	 	$stmt = "
	 	 SELECT SQL_CALC_FOUND_ROWS
		 a.*,

		 (
		 select count(*) 
		 from
		 {{ordernew}}
		 where
		 promo_code=a.voucher_name			
		 ) as total_used,
		 
		 IFNULL((
		 select title_trans from {{view_status_management}}
		 where
		 status=a.status and group_name='post'
		 and language=".q(Yii::app()->language)."
		 limit 0,1
		 ),a.status) as status_title
		 		
		 FROM {{voucher_new}} a
		 WHERE voucher_owner = 'merchant'		 
		 AND merchant_id = ".q($merchant_id)."
		 [and]
		 [search]
		 [order]
		 [limit]
	 	";		 			 
	 		 		 	
	 	DatatablesTools::$action_edit_path = "merchant/coupon_update";
	 			 			 
	 	if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data)){		 		
	 		$this->DataTablesData($feed_data);
	 		Yii::app()->end();
	 	}
	 	$this->DataTablesNodata();	
	}
	
    public function actionsearch_customer()
	{
		$res = array();
		try {
			$search = isset($this->data['search'])?$this->data['search']:'';			
			$res  = CustomerAR::search($search);						
		} catch (Exception $e) {
			//echo $e->getMessage();
		}		
		$result = array(
    	  'results'=>$res
    	);	    	
		header('Content-type: application/json');		
		echo CJSON::encode($result);
		Yii::app()->end();    	
	}
	
	public function actionoffer_list()
	{
		    $merchant_id = (integer) Yii::app()->merchant->merchant_id;
		    	
		 	$cols[]=array(
		 	  'key'=>'offers_id',
		 	  'value'=>'offers_id',		 	  
		 	);		 		 	
		 	
		 	$cols[]=array(
		 	  'key'=>'offer_percentage',
		 	  'value'=>'offer_percentage',		 
		 	  'action'=>"format",	
		 	  'format'=>'<h6>[offer_name] [offer_percentage] <span class="badge ml-2 post [status]">[status_title]</span></h6>
		 	  <p class="dim">		 
		 	   '.t("Over [offer_price]").'<br/>	   
		 	   '.t("Last Update. [date_modified]").'
		 	  </p>
		 	  ',
		 	  'format_value'=>array(		 	    
		 	    '[offer_percentage]'=>array(		 	       
		 	       'display'=>'percentage',
		 	       'value'=>'offer_percentage',
		 	     ),
				 '[offer_name]'=>array(		 	       		 	       
					'value'=>'offer_name',
	 	            'display'=>"short_text"
		 	     ),
		 	    '[status]'=>'status',
	 	        '[status_title]'=>'status_title',
		 	    '[date_modified]'=>array(
	 	         'value'=>'date_modified',
		 	     'display'=>"datetime"
	 	        ),
	 	        '[offer_price]'=>array(
	 	          'value'=>'offer_price',
	 	          'display'=>"price"
	 	        )
		 	  )
		 	);		 
		 	
		 	$cols[]=array(
		 	  'key'=>'valid_from',
		 	  'value'=>'valid_from',		 
		 	  'action'=>"format",	
		 	  'format'=>t('[valid_from] to [valid_to]'),
		 	  'format_value'=>array(		 	    
		 	    '[valid_from]'=>array(
	 	         'value'=>'valid_from',
		 	     'display'=>"date"
	 	        ),
	 	        '[valid_to]'=>array(
	 	         'value'=>'valid_to',
		 	     'display'=>"date"
	 	        )
		 	  )
		 	);		 		 			 
		 			 	
		 	$cols[]=array(
		 	  'key'=>'date_created',
		 	  'value'=>'date_created',
		 	  'action'=>"editdelete",
		 	  'id'=>'offers_id'
		 	);	 	
		 	
		 	$stmt = "
		 	 SELECT SQL_CALC_FOUND_ROWS
			 a.*,
			 
			 IFNULL((
			 select title_trans from {{view_status_management}}
			 where
			 status=a.status and group_name='post'
			 and language=".q(Yii::app()->language)."
			 limit 0,1
			 ),a.status) as status_title
			 
			 FROM {{offers}} a			 
			 WHERE a.merchant_id = ".q($merchant_id)."
			 [and]
			 [search]
			 [order]
			 [limit]
		 	";		 			 	
		 			 			
		DatatablesTools::$action_edit_path = "merchant/offer_update";
		 	 			 	
	 	if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data)){		 		
	 		$this->DataTablesData($feed_data);
	 		Yii::app()->end();
	 	}		 
		 $this->DataTablesNodata();		
	}			
	
	public function actiongallery_list()
	{
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;
		$this->preparePaginate();		
		$cols = array();
		
		$cols[]=array(
	 	  'key'=>'cuisine_id',
	 	  'value'=>'filename',	
	 	  'action'=>'gallery' 	  
	 	);		 	 	

	 	$cols[]=array(
	 	  'key'=>'title',
	 	  'value'=>'title',	 	  
	 	  'action'=>"format",
	 	  'format'=>'<h5 class="card-title mb-1">[title]</h5><p class="card-text mb-1">[size]</p>',
	 	  'format_value'=>array(
	 	    '[title]'=>array(
	 	       'value'=>'title',
		 	   'display'=>"short_text"
	 	     ),
	 	    '[size]'=>array(
	 	       'value'=>'size',
		 	   'display'=>"filesize"
	 	     ),
	 	  )
	 	); 	
	 			 	
	 	$cols[]=array(
	 	  'key'=>'id',
	 	  'value'=>'id',
	 	  'action'=>"editdelete",
	 	  'id'=>'id'
	 	);
	 	
	 	$fields = array(
	 	  array('key'=>'id'),	
	 	  array('key'=>'filename'),	 	   	 
	 	  array('key'=>'title'),	 
	 	);	 	
	 		 	 
		$stmt = "
	 	 SELECT SQL_CALC_FOUND_ROWS
		 a.*		 
		 FROM {{media_files}} a
		 WHERE merchant_id=".q($merchant_id)."	
		 AND meta_name = ".q(AttributesTools::metaMedia())."
		 [and]
		 [search]
		 [order]
		 [limit]
	 	";		 	
		
		$this->next_action = "display_gallery";
		AppListing::$action_edit_path = "attributes/cuisine_update";		
		$this->outputListingData($cols,$fields,$stmt);		
	}				
	
	public function actionmedia_list()
	{
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;
		$this->preparePaginate();		
		$cols = array();
		
		$cols[]=array(
	 	  'key'=>'cuisine_id',
	 	  'value'=>'filename',	
	 	  'action'=>'gallery' 	  
	 	);		 	 	

	 	$cols[]=array(
	 	  'key'=>'title',
	 	  'value'=>'title',	 	  
	 	  'action'=>"format",
	 	  'format'=>'<h5 class="card-title mb-1">[title]</h5><p class="card-text mb-1">[size]</p>',
	 	  'format_value'=>array(
	 	    '[title]'=>array(
	 	       'value'=>'title',
		 	   'display'=>"short_text"
	 	     ),
	 	    '[size]'=>array(
	 	       'value'=>'size',
		 	   'display'=>"filesize"
	 	     ),
	 	  )
	 	); 	
	 			 	
	 	$cols[]=array(
	 	  'key'=>'id',
	 	  'value'=>'id',
	 	  'action'=>"editdelete",
	 	  'id'=>'id'
	 	);
	 	
	 	$fields = array(
	 	  array('key'=>'id'),	
	 	  array('key'=>'filename'),	 	   	 
	 	  array('key'=>'title'),	 
	 	);	 	
	 		 	 
		$stmt = "
	 	 SELECT SQL_CALC_FOUND_ROWS
		 a.*		 
		 FROM {{media_files}} a
		 WHERE merchant_id=".q($merchant_id)."			 
		 [and]
		 [search]
		 [order]
		 [limit]
	 	";		 	
		
		$this->next_action = "display_gallery";
		AppListing::$action_edit_path = "attributes/cuisine_update";		
		$this->outputListingData($cols,$fields,$stmt);		
	}	

	public function actionsmsbroadcast_list()
	{
		    $merchant_id = (integer) Yii::app()->merchant->merchant_id;
		    
		    $view_link = Yii::app()->createUrl("/smsmerchant/broadcast_details",array(		  
			  'broadcast_id'=>'-id-',				 
			));   

			$html='
			<div class="btn-group btn-group-actions" role="group" >				
					  
			  <a href="'.$view_link.'" class="btn btn-light tool_tips"
			  data-toggle="tooltip" data-placement="top" title="'.t("View").'"
			  >
			  <i class="zmdi zmdi-eye"></i>
			  </a>		
			  
			 <a href="javascript:;" data-id="-id-" 
			  class="btn btn-light datatables_delete tool_tips"
			  data-toggle="tooltip" data-placement="top" title="'.t("Delete").'"
			  >
			  <i class="zmdi zmdi-delete"></i>
			  </a>
			  
			</div>
			';	
		    	
		 	$cols[]=array(
		 	  'key'=>'broadcast_id',
		 	  'value'=>'broadcast_id',		 	  
		 	);		 		 	
		 	
		 	$cols[]=array(
		 	  'key'=>'send_to',
		 	  'value'=>'send_to',		 
		 	  'action'=>"format",	
		 	  'format'=>'<h6>[send_to] <span class="badge ml-2 post [status]">[status_title]</span></h6>
		 	  <p class="dim">		 	   
		 	  '.t("Last Update. [date_modified]").'
		 	  </p>
		 	  ',
		 	  'format_value'=>array(		 	    
		 	    '[status]'=>'status',
	 	        '[status_title]'=>'status_title',	 	   
	 	        '[send_to]'=>array(
	 	           'value'=>'send_to',
		 	       'display'=>"pattern",
		 	       'data'=>AttributesTools::SMSBroadcastType()
	 	         ),
		 	    '[date_modified]'=>array(
	 	         'value'=>'date_modified',
		 	     'display'=>"datetime"
	 	        )
		 	  )
		 	);		 	
		 	
		 	$cols[]=array(
		 	  'key'=>'sms_alert_message',
		 	  'value'=>'sms_alert_message',		 	  
		 	  'action'=>'short_text'
		 	);	
		 	
		 	$cols[]=array(
		 	   'key'=>'a.broadcast_id',
		 	   'value'=>'broadcast_id',		 	  
		 	   'action'=>"format",
		 	   'format'=>$html,	 	 
		 	   'format_value'=>array(	 	   
		 	     '-id-'=>'broadcast_id',
		 	   )  
		 	);	 	
		 	
		 	$stmt = "
		 	 SELECT SQL_CALC_FOUND_ROWS
			 a.*,

			 IFNULL((
			 select description_trans from {{view_order_status}}
			 where
			 description=a.status 
			 and language=".q(Yii::app()->language)."
			 ),a.status) as status_title
			 			
			 FROM {{sms_broadcast}} a			 
			 WHERE a.merchant_id = ".q($merchant_id)."
			 [and]
			 [search]
			 [order]
			 [limit]
		 	";		 			 	
		 			 			
		DatatablesTools::$action_edit_path = "merchant/credit_card_update";
		 	 			 	
	 	if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data)){		 		
	 		$this->DataTablesData($feed_data);
	 		Yii::app()->end();
	 	}		 
		 $this->DataTablesNodata();		
	}	

	public function actionbroadcast_details()
	{
		    $merchant_id = (integer) Yii::app()->merchant->merchant_id;
		    $broadcast_id = (integer)Yii::app()->input->post('broadcast_id');
		    	
		 	$cols[]=array(
		 	  'key'=>'id',
		 	  'value'=>'id',		 	  
		 	);		 		 	
		 	
		 	$cols[]=array(
		 	  'key'=>'client_name',
		 	  'value'=>'client_name',		 
		 	  'action'=>"format",	
		 	  'format'=>'<h6>[client_name] <span class="badge ml-2 post [status]">[status_title]</span></h6>
		 	  <p class="dim">	
		 	   [contact_phone]<br/>
		 	   '.t("Date Created. [date_created]").'
		 	  </p>
		 	  ',
		 	  'format_value'=>array(
		 	    '[client_name]'=>'client_name',		 
		 	    '[contact_phone]'=>'contact_phone',
		 	    '[status]'=>'status',
	 	        '[status_title]'=>'status_title',	 	   
		 	    '[date_created]'=>array(
	 	         'value'=>'date_created',
		 	     'display'=>"datetime"
	 	        )
		 	  )
		 	);		 	
		 	
		 	$cols[]=array(
		 	  'key'=>'sms_message',
		 	  'value'=>'sms_message',	
		 	  'action'=>'short_text'
		 	);	 	
		 	
		 	$stmt = "
		 	 SELECT SQL_CALC_FOUND_ROWS
			 a.*,
			 
			 IFNULL((
			 select description_trans from {{view_order_status}}
			 where
			 description=a.status 
			 and language=".q(Yii::app()->language)."
			 ),a.status) as status_title
			 
			 FROM {{sms_broadcast_details}} a			 
			 WHERE a.merchant_id = ".q($merchant_id)."
			 AND broadcast_id=".q($broadcast_id)."
			 [and]
			 [search]
			 [order]
			 [limit]
		 	";		 		 	
		 			 			
		DatatablesTools::$action_edit_path = "merchant/credit_card_update";
		 	 			 	
	 	if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data)){		 		
	 		$this->DataTablesData($feed_data);
	 		Yii::app()->end();
	 	}		 
		 $this->DataTablesNodata();		
	}			
	
	public function actioncustomer_review()
	{
		    $merchant_id = (integer) Yii::app()->merchant->merchant_id;
		    
		    
		    $update_link = Yii::app()->createUrl("/customer/reviews_update",array(		  
			  'id'=>'-id-',				 
			));   
			
			$view_link = Yii::app()->createUrl("/customer/review_reply",array(		  
			  'id'=>'-id-',				 
			));   

			$can_edit_reviews = isset(Yii::app()->params['settings']['merchant_can_edit_reviews'])?Yii::app()->params['settings']['merchant_can_edit_reviews']:'';			
			
			$edit_link=''; $delete_link='';
			
			if($can_edit_reviews){
				$edit_link = '<a href="'.$update_link.'" class="btn btn-light tool_tips"
				  data-toggle="tooltip" data-placement="top" title="'.t("Update").'"
				  >
				  <i class="zmdi zmdi-border-color"></i>
				  </a>';
			
				$delete_link='
				<a href="javascript:;" data-id="-id-" 
				  class="btn btn-light datatables_delete tool_tips"
				  data-toggle="tooltip" data-placement="top" title="'.t("Delete").'"
				  >
				  <i class="zmdi zmdi-delete"></i>
				  </a>
				';	
			}
			
			$html='
			<div class="btn-group btn-group-actions" role="group" >				
					
			  '.$edit_link.'
			  
			  <a href="'.$view_link.'" class="btn btn-light tool_tips"
			  data-toggle="tooltip" data-placement="top" title="'.t("Reply").'"
			  >
			  <i class="zmdi zmdi-mail-reply-all"></i>
			  </a>		
			  
			  '.$delete_link.'
			  			  
			</div>
			';	
		    
		 	$cols[]=array(
		 	  'key'=>'id',
		 	  'value'=>'id',		 	  
		 	);		 		 	
		 	
		 	$cols[]=array(
		 	  'key'=>'client_id',
		 	  'value'=>'client_id',		 
		 	  'action'=>"format",	
		 	  'format'=>'<h6>[customer_name] <span class="badge ml-2 post [status]">[status_title]</span></h6>
		 	  <p class="dim">			 	   
		 	   '.t("Date Created. [date_created]").'<br/>
		 	   <a href="javascript:;" class="review_viewcomments" data-id="[id]">'.t("Comments ([number_comments])").'</a>
		 	  </p>
		 	  ',
		 	  'format_value'=>array(
		 	    '[customer_name]'=>'customer_name',		 		 	    
		 	    '[status]'=>'status',
	 	        '[status_title]'=>'status_title',
	 	        '[number_comments]'=>'number_comments',
	 	        '[id]'=>'id',
		 	    '[date_created]'=>array(
	 	         'value'=>'date_created',
		 	     'display'=>"datetime"
	 	        )
		 	  )
		 	);		 	
		 	
		 	$cols[]=array(
		 	  'key'=>'review',
		 	  'value'=>'review',	
		 	  'action'=>"format",
		 	  'format'=>'<b>[rating]</b><br/>[review]',
		 	  'format_value'=>array(
		 	    '[rating]'=>'rating',
		 	    '[review]'=>array(
		 	      'value'=>'review',
		 	      'display'=>"short_text"
		 	    )
		 	  )
		 	);	 	
		 	
		 	$cols[]=array(
		 	   'key'=>'a.id',
		 	   'value'=>'id',		 	  
		 	   'action'=>"format",
		 	   'format'=>$html,	 	 
		 	   'format_value'=>array(	 	   
		 	     '-id-'=>'id',		 	     
		 	   )  
		 	);	 	
		 	
		 	$stmt = "
		 	 SELECT SQL_CALC_FOUND_ROWS
			 a.*,
			 
			 IFNULL((
			  select concat(first_name,' ',last_name)
			  from {{client}}
			  where client_id = a.client_id
			 ),a.client_id) as customer_name,
			 
			 IFNULL((
			 select description_trans from {{view_order_status}}
			 where
			 description=a.status 
			 and language=".q(Yii::app()->language)."
			 ),a.status) as status_title,
			 
			 (
			 select count(*) from {{review}}
			 where parent_id = a.id
			 ) as number_comments
			 
			 FROM {{review}} a			 
			 WHERE a.merchant_id = ".q($merchant_id)."	
			 AND a.parent_id=0		 
			 [and]
			 [search]
			 [order]
			 [limit]
		 	";		 		 	
		 			 			
		DatatablesTools::$action_edit_path = "backendmerchant/customerreview_update";
		 	 			 	
	 	if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data)){		 		
	 		$this->DataTablesData($feed_data);
	 		Yii::app()->end();
	 	}		 
		 $this->DataTablesNodata();		
	}				
		
	public function actioncustomer_reply()
	{		
		$parent_id = (integer) Yii::app()->input->post('parent_id');
		
		if($parent_id<=0){
			$this->msg = t("No results");
			$this->details = array(
			   'next_action'=>'silent',			   
			);
			$this->responseJson();
		}
			
		$stmt="SELECT id,review,reply_from FROM 
		{{review}} 
		WHERE 
		parent_id=".q($parent_id)."		
		";				
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
			$data = array();
			foreach ($res as $val) {
				$val['edit_link']=websiteDomain().Yii::app()->CreateUrl("/customer/review_reply_update/",array('id'=>$val['id']));
				$data[]=$val;
			}				
			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(
			   'next_action'=>'review_reply',
			   'data'=>$data
			);
		} else {
			$this->msg = t("No results");
			$this->details = array(
			   'next_action'=>'silent',			   
			);
		}
		$this->responseJson();
	}
	
    public function actionuser_list()
	{
		    $merchant_id = (integer) Yii::app()->merchant->merchant_id;
		    	
		 	$cols[]=array(
		 	   'key'=>'merchant_user_id',
	 	       'value'=>'profile_photo',
	 	       'action'=>"customer",
		 	);		 		 	
		 	
		 	$cols[]=array(
		 	  'key'=>'first_name',
		 	  'value'=>'first_name',
		 	  'action'=>"format",
		 	  'format'=>'<h6>[fullname] <span class="badge ml-2 customer [status]">[status]</span></h6>
		 	    <p class="dim">'.t("E").'. [contact_email]<br>'.t("M").'. [contact_number]</p>
		 	  ',
		 	  'format_value'=>array(
		 	    '[fullname]'=>'fullname',
		 	    '[contact_email]'=>'contact_email',
		 	    '[contact_number]'=>'contact_number',
		 	    '[status]'=>'status',
		 	  )
		 	);		
		 	
		 	$cols[]=array(
		 	   'key'=>'role',
	 	       'value'=>'role_name',	
	 	       'action'=>"format",
	 	       'format'=>'<h6>[role_name]</h6>
	 	       <p class="dim">'.t("[access_count] permission").'</p>
	 	       ',
	 	       'format_value'=>array(
	 	         '[role_name]'=>'role_name',
	 	         '[access_count]'=>'access_count'
	 	       )
		 	);		 	
		 	
		 	$cols[]=array(
		 	  'key'=>'date_created',
		 	  'value'=>'date_created',
		 	  'action'=>"editdelete",
		 	  'id'=>'user_uuid'
		 	);	 	
		 	
		 	$stmt = "
		 	 SELECT SQL_CALC_FOUND_ROWS
			 a.*,
			 concat(first_name,' ',last_name) as fullname,

			 IFNULL((
			 select title_trans from {{view_status_management}}
			 where
			 status=a.status and group_name='customer'
			 and language=".q(Yii::app()->language)."
			 limit 0,1
			 ),a.status) as status_title,
			 
			 IFNULL(b.role_name,'') as role_name,
			 
			 IFNULL((
			 select count(*) from {{role_access}}
			 where role_id = a.role
			 ),0) as access_count
			 			
			 FROM {{merchant_user}} a			 
			 LEFT JOIN {{role}} b
			 ON
			 a.role = b.role_id
				 
			 
			 WHERE a.merchant_id = ".q($merchant_id)."
			 AND main_account=0
			 [and]
			 [search]
			 [order]
			 [limit]
		 	";		 			 	
		 			 			
		DatatablesTools::$action_edit_path = "usermerchant/user_update";
		 	 			 	
	 	if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data)){		 		
	 		$this->DataTablesData($feed_data);
	 		Yii::app()->end();
	 	}		 
		 $this->DataTablesNodata();		
	}			
	
	public function actionrole_list()
	{		
		    $merchant_id = (integer) Yii::app()->merchant->merchant_id;
		    
		 	$cols[]=array(
		 	  'key'=>'role_id',
		 	  'value'=>'role_id'		 	  
		 	);		 
		 	$cols[]=array(
		 	  'key'=>'role_name',
		 	  'value'=>'role_name'		 	  
		 	);		 
		 	$cols[]=array(
		 	  'key'=>'role_id',
		 	  'value'=>'access_count',
		 	  'action'=>"format",
		 	  'format'=>'<h6>[access_count]</h6><p class="dim">'.t("permission").'</span>',
		 	  'format_value'=>array(
		 	    '[access_count]'=>'access_count',
		 	  )
		 	);		 
		 	$cols[]=array(
		 	  'key'=>'date_created',
		 	  'value'=>'date_created',
		 	  'action'=>"editdelete",
		 	  'id'=>'role_id'
		 	);
		 	
		 	$stmt = "
		 	 SELECT SQL_CALC_FOUND_ROWS
			 role_id,role_name,date_created,
			 (
			 select count(*) from {{role_access}}
			 where role_id = a.role_id
			 ) as access_count
			 FROM {{role}} a
			 WHERE merchant_id = ".q($merchant_id)."
			 AND role_type='merchant'
			 [and]
			 [search]
			 [order]
			 [limit]
		 	";		 	
		 			 			 	
		 	DatatablesTools::$action_edit_path = "usermerchant/role_update";
		 	
		 	if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data)){		 		
		 		$this->DataTablesData($feed_data);
		 		Yii::app()->end();
		 	}		 
		 $this->DataTablesNodata();
	}		
	
	public function actionsales_report()
	{
			
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;
		
		$cols=array(
			  array(
			   'key'=>'a.order_id',
		 	   'value'=>'order_id',		 	  	 	   
			  ),
			  array(
			    'key'=>'a.client_id',
	 	        'value'=>'customer_avatar',		 	  
	 	        'action'=>"customer", 	  
			  ),			 			  
			  array(
			   'key'=>'a.client_id',
		 	   'value'=>'customer_name',
		 	   'action'=>"format",	
		 	   'format'=>'<h6>[customer_name] <span class="badge ml-2 order_status [status]">[status_title]</span></h6>
		 	   <p class="dim">[date_created]</p>
		 	   ',
		 	   'format_value'=>array(
		 	     '[customer_name]'=>'customer_name',
		 	     '[status]'=>'status',
		 	     '[status_title]'=>'status_title',
		 	     '[date_created]'=>array(
		 	      'display'=>'datetime',
		 	      'value'=>'date_created'
		 	     )
		 	   )
			  ),
			  array(
			   'key'=>'a.merchant_id',
		 	   'value'=>'items',		 	   
			  ),			 
			  array(
			   'key'=>'a.trans_type',
		 	   'value'=>'service_name',		 	   
			  ),			 
			  array(
			   'key'=>'a.payment_type',
		 	   'value'=>'payment_name',		 	   
			  ),			 			 
			  array(
			   'key'=>'a.total_w_tax',
		 	   'value'=>'total_w_tax',
		 	   'action'=>"price"		 	   
			  ),			  
			);
		 
			$stmt = "
		 	 SELECT SQL_CALC_FOUND_ROWS
			 a.*,
			 IFNULL(b.avatar,'') as customer_avatar,			 
			 IFNULL(concat(c.first_name,' ',c.last_name),a.client_id) as customer_name,
			 
			 IFNULL(s.service_name,a.trans_type) as service_name,
			 
			 IFNULL(p.payment_name,a.payment_type) as payment_name,
			 
			 IFNULL((
			 select description_trans from {{view_order_status}}
			 where
			 description=a.status 
			 and language=".q(Yii::app()->language)."
			 ),a.status) as status_title,
			 
			 (
			 select 
			 IF( GROUP_CONCAT(item_name) IS NULL or item_name = '',
			 
			 (
			   select GROUP_CONCAT(item_name) from {{order_details}}
			   where order_id = a.order_id
			 )
			 
			 ,  GROUP_CONCAT(item_name) )
			 
			 from {{item_translation}}
			 where
			 language = ".q(Yii::app()->language)."
			 and
			 item_id IN (
			       select item_id from {{order_details}}
			       where order_id = a.order_id
			   )
			 ) as items
			 
			 FROM {{order}} a			
			 LEFT JOIN {{client}} b
			 ON
			 a.client_id = b.client_id
			 
			 LEFT JOIN {{order_delivery_address}} c
			 ON
			 a.client_id = c.client_id
			 
			 LEFT JOIN {{services}} s
			 ON
			 a.trans_type = s.service_code
			 
			 LEFT JOIN {{payment_gateway}} p
			 ON
			 a.payment_type = p.payment_code
			 
			 WHERE a.merchant_id = ".q($merchant_id)."
			 AND a.status NOT IN (".q( AttributesTools::initialStatus() ).")
			 AND a.order_id = c.order_id
			 [and]
			 [search]
			 [order]
			 [limit]
		 	";		
			
		 	$filter_stmt='';
		 	$filter_date=isset($this->data['date_filter'])?$this->data['date_filter']:'';
		 	$status_filter=isset($this->data['status_filter'])?$this->data['status_filter']:'';		 	
		 	
		 	$filter_stmt = DatatablesTools::FilterDates($filter_date,'a.date_created');
		 	$filter_stmt.= DatatablesTools::FilterStatus($status_filter,'a.status');
		 			 	
		 	
		 	if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data,$filter_stmt)){		 		
		 		$this->DataTablesData($feed_data);
		 		Yii::app()->end();
		 	}		 
		 $this->DataTablesNodata();
	}
	
    public function actionsummary_report()
	{
		    $merchant_id = (integer) Yii::app()->merchant->merchant_id;
		    	
		 	$cols[]=array(
		 	  'key'=>'a.item_id',
		 	  'value'=>'item_id',		 	  
		 	);		 		 	
		 	
		 	$cols[]=array(
		 	  'key'=>'a.item_id',
		 	  'value'=>'item_photo',
		 	  'action'=>'item_photo'
		 	);		 		 	
		 	
		 	$cols[]=array(
		 	  'key'=>'a.item_name',
		 	  'value'=>'item_name',		 
		 	  'action'=>"format",	
		 	  'format'=>'<h6>[item_name]</h6>
		 	  <p class="dim">		 	
		 	   [size]  
		 	  </p>
		 	  ',
		 	  'format_value'=>array(
		 	    '[item_name]'=>'item_name',		 			 	    
		 	    '[size]'=>'size'
		 	  )
		 	);		 	
		 	
		 	$cols[]=array(
		 	  'key'=>'discounted_price',
		 	  'value'=>'net_sale',		
		 	  'action'=>"format",	 	
		 	  'format'=>'[average_price]',
		 	  'format_value'=>array(
		 	    '[net_sale]'=>'net_sale',
		 	    '[average_price]'=>array(
		 	      'value'=>"average_price",
		 	      'display'=>'price'
		 	    )
		 	  )
		 	);	 	
		 	
		 	$cols[]=array(
		 	  'key'=>'qty',
		 	  'value'=>'qty_sold',		 	  
		 	);	 	
		 	
		 	$cols[]=array(
		 	  'key'=>'discounted_price',
		 	  'value'=>'total',	
		 	  'action'=>'price'
		 	);	 	
		 	
		 	$stmt = "
		 	 SELECT SQL_CALC_FOUND_ROWS
			 a.*,
			 SUM(a.qty) as qty_sold,
			 (a.discounted_price * SUM(a.qty)) as net_sale,
			 (a.discounted_price * SUM(a.qty)/SUM(a.qty)) as average_price,
			 ((a.discounted_price * SUM(a.qty)/SUM(a.qty)) * SUM(a.qty)) as total,
			 
			 IFNULL(b.photo,'') as item_photo
			 			
			 FROM {{view_order_details}} a			 
			 LEFT JOIN {{item}} b
			 ON
			 a.item_id = b.item_id
			 
			 WHERE a.merchant_id = ".q($merchant_id)."			 
			 AND a.status NOT IN (".q( AttributesTools::initialStatus() ).")	
			 [and]
			 [search]
			 GROUP BY a.item_id,a.size	 
			 [order]
			 [limit]
		 	";		 			 	
		 			 					 	
			$filter_stmt='';
		 	$filter_date=isset($this->data['date_filter'])?$this->data['date_filter']:'';
		 	$status_filter=isset($this->data['status_filter'])?$this->data['status_filter']:'';		 	
		 	
		 	$filter_stmt = DatatablesTools::FilterDates($filter_date,'a.date_created');
		 	$filter_stmt.= DatatablesTools::FilterStatus($status_filter,'a.status');
		 			 			 			 	
		 	if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data,$filter_stmt)){		 		
		 		$this->DataTablesData($feed_data);
		 		Yii::app()->end();
		 	}		 
		    $this->DataTablesNodata();	
	}			
	
	public function actionbooking_summary()
	{
	
	     $merchant_id = (integer) Yii::app()->merchant->merchant_id;
	     
		 $cols=array(			  
		 array(
		    'key'=>'total_approved',
 	        'value'=>'total_approved',		 	  	 	        
		  ),
		  array(
		   'key'=>'total_denied',
	 	   'value'=>'total_denied',		 	  	 	   
		  ),			  
		  array(
		   'key'=>'total_pending',
	 	   'value'=>'total_pending',		 	  	 	   
		  ),			  
		);
	 
	    $stmt = "
	 	SELECT SQL_CALC_FOUND_ROWS
		a.merchant_id,
        b.restaurant_name as merchant_name,
        b.logo as merchant_logo,
         
        IFNULL((
		select sum(number_guest) from {{bookingtable}}
		where 
		merchant_id = a.merchant_id 
		and status='approved'
		),0) as total_approved,
		
		IFNULL((
		select sum(number_guest) from {{bookingtable}}
		where 
		merchant_id = a.merchant_id 
		and status='denied'
		),0) as total_denied,
		
		IFNULL((
		select sum(number_guest) from {{bookingtable}}
		where 
		merchant_id = a.merchant_id 
		and status='pending'
		),0) as total_pending
		
		from {{bookingtable}} a
		left join {{merchant}} b
		on
		a.merchant_id = b.merchant_id

		
		 WHERE a.merchant_id=".q($merchant_id)."
		 [and]
		 [search]
		 group by a.merchant_id
		 [order]			 
		 [limit]
	 	";		 		
					
	 	$filter_stmt='';
	 		 			 			 			 		
	 	DatatablesTools::$action_edit_path = "vendor/edit";
	 	
	 	$filter_date=isset($this->data['date_filter'])?$this->data['date_filter']:'';
	 	$status_filter=isset($this->data['status_filter'])?$this->data['status_filter']:'';
	 	$filter_merchant=isset($this->data['filter_merchant'])?(integer)$this->data['filter_merchant']:0;
	 	
	 	$filter_stmt = DatatablesTools::FilterDates($filter_date,'a.date_created');
	 	$filter_stmt.= DatatablesTools::FilterStatus($status_filter,'a.status');
	 	
	 	if($filter_merchant>0){
	 		$filter_stmt.="\n AND a.merchant_id=".q($filter_merchant)."";
	 	}
	 	
	 	if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data,$filter_stmt)){		 		
	 		$this->DataTablesData($feed_data);
	 		Yii::app()->end();
	 	}		 
	    $this->DataTablesNodata();		
	}		
		
    public function actionsearch_item() 
	{
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;
		$res = array();
		try {			
			$search = isset($this->data['search'])?$this->data['search']:'';			
			$model = AR_item::model()->findAll(
			    'merchant_id=:merchant_id AND item_name LIKE :item_name',
			    array(':merchant_id'=>$merchant_id,':item_name' => "%$search%")
			);
			if($model){
				foreach ($model as $val) {
					$res[]=array(
					  'id'=>$val->item_id,
					  'text'=>CHtml::decode($val->item_name)
					);
				}
			}			
		} catch (Exception $e) {
			//echo $e->getMessage();
		}		
		$result = array(
    	  'results'=>$res
    	);	    	
    	$this->responseSelect2($result);
	}
	
	public function actionitem_promo()
	{
		    $merchant_id = (integer) Yii::app()->merchant->merchant_id;
		    $item_id = (integer) Yii::app()->input->post('item_id');
		    			 	
		 	$cols[]=array(
		 	  'key'=>'promo_id',
		 	  'value'=>'promo_type',		 
		 	  'action'=>"promo_type",			 	  
		 	);		 	
		 	
		 	$cols[]=array(
		 	  'key'=>'date_created',
		 	  'value'=>'date_created',
		 	  'action'=>"editdelete",
		 	  'id'=>'promo_id'
		 	);	 	
		 	
		 	$stmt = "
		 	 SELECT SQL_CALC_FOUND_ROWS
			 a.*,
			 IFNULL(b.item_name,'') as item_name
			  			 			
			 FROM {{item_promo}} a			 
			 LEFT JOIN {{item}} b
			 ON
			 a.item_id_promo = b.item_id
			 
			 WHERE a.merchant_id = ".q($merchant_id)."
			 AND a.item_id = ".q($item_id)."
			 [and]
			 [search]
			 [order]
			 [limit]
		 	";		 			 	
		 			 					 			
		DatatablesTools::$action_edit_path = "food/itempromo_update/item_id/$item_id";
		 	 			 	
	 	if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data)){		 		
	 		$this->DataTablesData($feed_data);
	 		Yii::app()->end();
	 	}		 
		 $this->DataTablesNodata();		
	}	
		
	public function actionsupplier_list()
	{
		    $id = (integer) Yii::app()->merchant->merchant_id;
		    	
		 	$cols[]=array(
		 	  'key'=>'supplier_id',
		 	  'value'=>'supplier_id',		 	  
		 	);		 		 	
		 	
		 	$cols[]=array(
		 	  'key'=>'supplier_name',
		 	  'value'=>'supplier_name',		 
		 	  'action'=>"format",	
		 	  'format'=>'<h6>[supplier_name]</h6>
		 	  <p class="dim">		
		 	   [email]<br/>
		 	   '.t("Last Update. [updated_at]").'
		 	  </p>
		 	  ',
		 	  'format_value'=>array(
		 	    '[supplier_name]'=>'supplier_name',		 	    
		 	    '[contact_name]'=>'contact_name',
		 	    '[email]'=>'email',
		 	    '[updated_at]'=>array(
	 	         'value'=>'updated_at',
		 	     'display'=>"datetime"
	 	        )
		 	  )
		 	);		 	
		 	
		 	$cols[]=array(
		 	  'key'=>'updated_at',
		 	  'value'=>'updated_at',
		 	  'action'=>"editdelete",
		 	  'id'=>'supplier_id'
		 	);	 	
		 	
		 	$stmt = "
		 	 SELECT SQL_CALC_FOUND_ROWS
			 a.*			 			
			 FROM {{inventory_supplier}} a			 
			 WHERE a.merchant_id = ".q($id)."
			 [and]
			 [search]
			 [order]
			 [limit]
		 	";		 			 	
		 			 					 	
		DatatablesTools::$action_edit_path = "supplier/update";
		 	 			 	
	 	if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data)){		 		
	 		$this->DataTablesData($feed_data);
	 		Yii::app()->end();
	 	}		 
		 $this->DataTablesNodata();		
	}			
	
 	public function actionpayment_list()
	{		
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;
		$cols=array(
		  array(
		   'key'=>'id',
	 	   'value'=>'logo_image',	
	 	   'action'=>'photo_or_class',
	 	   'logo_type'=>'logo_type',
	 	   'logo_class'=>'logo_class'
		  ),
		   array(
		   'key'=>'a.status',
	 	   'value'=>'status',
	 	   'id'=>'payment_uuid',
	 	   'action'=>'checkbox_active',
	 	   'class'=>"set_payment_provider"
		  ),
		  array(
		    'key'=>'payment_name',
	 	    'value'=>'payment_name',	
	 	    'action'=>"format",	 	  
	 	    'format'=>'<h6>[payment_name] <span class="badge ml-2 gateway [status]">[status]</span></h6>	 	    
	 	    ',
	 	    'format_value'=>array(
	 	     '[payment_name]'=>'payment_name',	 	     
	 	     '[status]'=>'status',	 	     	 	     
	 	     '[status]'=>'status',	 	
	 	    )
		  ),
		  array(
		   'key'=>'payment_uuid',
	 	   'value'=>'payment_uuid',		 	  
	 	   'action'=>"editdelete",
	 	   'id'=>'payment_uuid'
		  ),
		);
	 	
	 	$stmt = "
	 	 SELECT SQL_CALC_FOUND_ROWS
		 a.*,
		 b.payment_name,
		 b.logo_class,
		 b.logo_type,
		 b.logo_image,
		 b.path
		 		
		 FROM {{payment_gateway_merchant}} a
		 LEFT JOIN {{payment_gateway}} b
		 ON
		 a.payment_id = b.payment_id
		 
		 WHERE merchant_id = ".q($merchant_id)."
		 
		 AND b.payment_code IN (
		  select meta_value from {{merchant_meta}}
		  where meta_name='payment_gateway'
		  and meta_value = a.payment_code
		  and merchant_id = ".q($merchant_id)."
		 )	
		 
		 [and]
		 [search]
		 [order]
		 [limit]
	 	";		 			 		 	
	 	DatatablesTools::$action_edit_path = "merchant/payment_update";
	 			 			 
	 	if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data)){		 		
	 		$this->DataTablesData($feed_data);
	 		Yii::app()->end();
	 	}
	 	$this->DataTablesNodata();	
	}
	
	public function actionset_payment_provider()
	{		
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;
		$id = isset($this->data['id'])?(integer)$this->data['id']:0;		
		$status = isset($this->data['status'])?$this->data['status']:'';		
		$model = AR_payment_gateway_merchant::model()->find("payment_uuid=:payment_uuid AND 
		merchant_id=:merchant_id",array(
		  ':payment_uuid'=>$id,
		  ':merchant_id'=>$merchant_id
		));		
		if($model){					
			$model->status=$status;
			if($model->save()){
				$this->code = 1;
				$this->msg = t(Helper_success);
				$this->details = array(
				);
			} else $this->msg = t(Helper_failed_update);
		} else $this->msg =  t(Helper_not_found);
		$this->responseJson();
	}
	
	public function actionorder_list_new()
	{
		$cols=array(
		  array(
		   'key'=>'a.order_id',
	 	   'value'=>'logo',		 	  
	 	   'action'=>"merchant_logo", 	  
		  ),
		  /*array(
		   'key'=>'a.order_id',
	 	   'value'=>'order_id',		 	  	 	   	 
		  ),*/
		  array(
		   'key'=>'a.order_id',
	 	   'value'=>'order_id',		
	 	   'action'=>"format",
	 	   'format'=>'<span class="btn badge order_status [status] badge-pill">#[order_id]</span>',
	 	   'format_value'=>array(
	 	     '[order_id]'=>'order_id',	 	     
	 	     '[status]'=>'status',
	 	   )
		  ),
		  array(
		   'key'=>'b.restaurant_name',
	 	   'value'=>'restaurant_name',		 	  	 	   
		  ),
		  array(
		   'key'=>'a.client_id',
	 	   'value'=>'customer_name',		
	 	   'action'=>"format",
	 	   'format'=>'<h6>[customer_name]</h6>',
	 	   'format_value'=>array(
	 	     '[status]'=>'status',
	 	     '[customer_name]'=>'customer_name'
	 	   )
		  ),		  
		  array(
		   'key'=>'sub_total',
	 	   'value'=>'sub_total',		 	   
	 	   'action'=>"format",
	 	   'format'=>t("[total_items] items").' <span class="badge ml-2 order_status [status]">[status_title]</span>
	 	    <p class="dim">[payment_type]
	 	    <br>'.t("Transactions").' <span class="badge services [service_code] ">[service_name]</span>
	 	    <br>'.t("Total").': <b class="text-dark">[total]</b> 	 	    
	 	    <br>'.t("Place on [date_created]").'	 	    
	 	    </p>
	 	  ',
	 	  'format_value'=>array(	
	 	     '[total_items]'=>'total_items',	 	     
	 	     '[payment_type]'=>'payment_name',	 
	 	     '[status]'=>'status',
	 	     '[status_title]'=>'status_title',
	 	     '[service_name]'=>'service_name',
	 	     '[service_code]'=>'service_code',
	 	     '[total]'=>array(
	 	       'value'=>'total',
		 	   'display'=>"price"
	 	     ),
	 	     '[date_created]'=>array(
	 	       'value'=>'date_created',
		 	   'display'=>"datetime"
	 	     )
	 	    )	 	  
		  ),		  
		  array(
		   'key'=>'a.order_id',
	 	   'value'=>'order_id',		 	  
	 	   'action'=>"orders_actions",
	 	   'id'=>'order_id'
		  ),
		);
		
		$stmt = "
	 	 SELECT SQL_CALC_FOUND_ROWS
		 a.order_id,
		 a.order_uuid,
		 a.merchant_id,
		 a.client_id,
		 a.sub_total,
		 a.payment_code,
		 a.service_code,
		 a.total,
		 a.status,
		 a.date_created,
		 b.restaurant_name,b.logo,b.path,
		 		 
		 (
		   select DISTINCT meta_value from {{ordernew_meta}}
		   where order_id = a.order_id 
		   and meta_name='customer_name'		   
		 ) as customer_name ,
		 
		 IFNULL(d.payment_name, a.payment_code ) as payment_name,
		 
		 (
		  select sum(qty)
		  from {{ordernew_item}}
		  where order_id=a.order_id
		 ) as total_items,
		 
		 IFNULL((
		 select description from {{order_status_translation}}
		 where language = ".q(Yii::app()->language)."
		 and stats_id = (
		     select stats_id from {{order_status}}
		     where description = a.status		     
		   )
		 ),a.status) as status_title,
		 
		 (
		 select service_name
		 from {{services_translation}}
		 where language=".q(Yii::app()->language)."
		 and service_id = (
		      select service_id from {{services}}
		      where service_code = a.service_code
		   )
		 ) as service_name
		 		 
		 
		 FROM {{ordernew}} a
		 LEFT JOIN {{merchant}} b
		 ON
		 a.merchant_id = b.merchant_id
		 		 
		 LEFT JOIN {{payment_gateway}} d
		 ON
		 a.payment_code = d.payment_code
				 
		 WHERE 
		 a.merchant_id = ".q( intval(Yii::app()->merchant->merchant_id) )."
		 AND 
		 a.status NOT IN (".q( AttributesTools::initialStatus() ).")
		 [and]
		 [search]
		 [order]
		 [limit]
	 	";		 			 
	 		 		 	
	 	DatatablesTools::$action_edit_path = "merchant/order_edit";
	 	DatatablesTools::$action_view_path = "merchant/order_view";
	 			 			 	 	
	 	if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data)){		 		
	 		$this->DataTablesData($feed_data);
	 		Yii::app()->end();
	 	}
	 	$this->DataTablesNodata();	
	}

	public function actionset_banner_status()
	{
		try {

			$id = Yii::app()->input->post('id');
			$checked = Yii::app()->input->post('checked');
			$model = AR_banner::model()->find("banner_uuid=:banner_uuid AND meta_value1=:meta_value1",[
				':banner_uuid'=>$id,
				':meta_value1'=>Yii::app()->merchant->merchant_id
			]);
			if($model){
				$model->status = intval($checked);
				if($model->save()){
					$this->code = 1; $this->msg = "ok";
				} else $this->msg = CommonUtility::parseError( $model->getErrors());
			} else $this->msg = t("Record not found");
			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());
		}
		$this->responseJson();
	}	

	public function actionprinters_list()
	{
		$id = (integer) Yii::app()->merchant->merchant_id;
		    	
		$cols[]=array(
		  'key'=>'printer_id',
		  'value'=>'printer_id',		  
		);		 		 	
				
		$cols[]=array(
		  'key'=>'printer_id',
		  'value'=>'printer_name',		 
		  'action'=>"format",	
		  'format'=>'<h6>[printer_name]</h6>	
		  <p class="dim">				  
		   [printer_model] - [auto_print]
		  </p>		  	  
		  ',
		  'format_value'=>array(
			'[printer_name]'=>'printer_name',			
			'[status]'=>'status',		
			'[printer_model]'=>'printer_model',					
			'[auto_print]'=>'auto_print',								
		  )
		);		 	
		
		$cols[]=array(
		  'key'=>'date_created',
		  'value'=>'date_created',
		  'action'=>"editdelete",
		  'id'=>'printer_id'
		);	 	
		
		$stmt = "
		 SELECT SQL_CALC_FOUND_ROWS
		a.*	
		FROM {{printer}} a			 
		WHERE a.merchant_id = ".q($id)."
		AND printer_model IN ('feieyun','wifi')
		AND platform='merchant'
		[and]
		[search]
		[order]
		[limit]
		";		 			 	
							
	    DatatablesTools::$action_edit_path = "printers/update";		
									
		if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data)){		 		
			$this->DataTablesData($feed_data);
			Yii::app()->end();
		}		 
		$this->DataTablesNodata();		
	}

	public function actionsearchTableroom()
    {     	 
    	 $search = isset($this->data['search'])?$this->data['search']:''; 
    	 $data = array();
    	 
    	 $criteria=new CDbCriteria();    	 
    	 $criteria->condition = "merchant_id=:merchant_id AND status=:status";
    	 $criteria->params = array(
		   ':merchant_id'=>Yii::app()->merchant->merchant_id,
    	   ':status'=>'publish'
    	 );
    	 if(!empty($search)){
			$criteria->addSearchCondition('room_name', $search );			
		 }
		 $criteria->limit = 10;
		 if($models = AR_table_room::model()->findAll($criteria)){		 	
		 	foreach ($models as $val) {
		 		$data[]=array(
				  'id'=>$val->room_id,
				  'text'=>Yii::app()->input->xssClean($val->room_name)
				);
		 	}
		 }
		 
		$result = array(
    	  'results'=>$data
    	);	    	
    	$this->responseSelect2($result);
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

	public function actionaddress_list()
	{
		$id = (integer) Yii::app()->input->post('id');		
		$setttings = Yii::app()->params['settings'];
		$home_search_mode = isset($setttings['home_search_mode'])?$setttings['home_search_mode']:'address';					
		$home_search_mode = !empty($home_search_mode)?$home_search_mode:'address';			
				
		if($home_search_mode=="address"){
			$cols=array(
				array(
				 'key'=>'address_id',
				  'value'=>'address_id',		 	   
				),		   				
				array(
				  'key'=>'formatted_address',
				   'value'=>'formatted_address',	
				   'action'=>"format",	 	  
				   'format'=>'<h6>[full_address]</h6>	 	    
				   ',
				   'format_value'=>array(
					'[full_address]'=>'full_address',	 	     
					'[date_created]'=>array(
						'value'=>'date_created',
						'display'=>"date"
					 )
				   )
				),
				array(
				 'key'=>'address_id',
				  'value'=>'address_id',		 	  
				  'action'=>"editdelete",
				  'id'=>'address_id',	 
				  'primary_key'=>'ad_id'
				),
			);
			$stmt = "
			SELECT SQL_CALC_FOUND_ROWS
			a.*,
			concat(a.address1,' ',a.formatted_address,' ',a.location_name) as full_address
			FROM {{client_address}} a
			WHERE client_id = ".q(intval($id))." 
			AND address_type='map-based'
			[and]
			[search]
			[order]
			[limit]
			";		 			
		} else {
			$cols=array(
				array(
				 'key'=>'address_id',
				  'value'=>'address_id',		 	   
				),		   				
				array(
				  'key'=>'full_address',
				   'value'=>'full_address',	
				   'action'=>"format",	 	  
				   'format'=>'<h6>[full_address]</h6>	 	    
				   ',
				   'format_value'=>array(
					'[full_address]'=>'full_address',	 	     
					'[date_created]'=>array(
						'value'=>'date_created',
						'display'=>"date"
					 )
				   )
				),
				array(
				 'key'=>'address_id',
				  'value'=>'address_id',		 	  
				  'action'=>"editdelete",
				  'id'=>'address_id',	 
				  'primary_key'=>'ad_id'
				),
			);
			$stmt = "
			SELECT SQL_CALC_FOUND_ROWS
			a.*,
			Concat(a.house_number,' ',a.street_number,' ',a.street,' ',a.area_name,' ',a.city_name,' ',a.zip_code,' ',a.state_name,' ',a.country_name) as full_address
			FROM {{view_client_address_locations}} a
			WHERE client_id = ".q(intval($id))." 			
			[and]
			[search]
			[order]
			[limit]
			";		 			
		}

		$model = AR_client::model()->findByPk($id);	
		if(!$model){
			$this->DataTablesNodata();	
		}
				
	 	DatatablesTools::$action_edit_path = "customer/address_update/id/$model->client_uuid";
	 			 			 
	 	if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data)){		 
	 		$this->DataTablesData($feed_data);
	 		Yii::app()->end();
	 	}
	 	$this->DataTablesNodata();	
	}

	public function actiongetLocationCountries()
	{
		//try {
			
			$this->data = Yii::app()->input->xssClean(json_decode(file_get_contents('php://input'), true));			
			$default_country = isset($this->data['default_country'])?$this->data['default_country']:'';
			$only_countries = isset($this->data['only_countries'])?$this->data['only_countries']:array();
			$only_countries = explode(",",$only_countries);
			
			$filter = array(
			  'only_countries'=>(array)$only_countries
			);
			
			$data = ClocationCountry::listing($filter);						
			$default_data = ClocationCountry::get($default_country);			
			
			$this->code = 1;
			$this->msg = "OK";
			$this->details = array(
			  'data'=>$data,
			  'default_data'=>$default_data,			  
			);			
		// } catch (Exception $e) {
		//     $this->msg = t($e->getMessage());		    		    
		// }
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

	public function actionbackofficemenu()
	{
		try {
			$list = AttributesTools::BackOfficeMenu('merchant');			
			$indexedArray = array_values($list);						
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = $indexedArray;
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			
		}		
		$this->responseJson();
	}

	public function actionuploadaudio()
	{
		try {			
			$input = json_decode(file_get_contents('php://input'), true);
			if (!empty($input['audioBase64']) && !empty($input['fileName'])) {
				$audioBase64 = $input['audioBase64'];
                $fileName = $input['fileName'];
				$audioData = base64_decode($audioBase64);

				$fileSizeInBytes = strlen($audioData);
				$fileSizeInMB = $fileSizeInBytes / (1024 * 1024);

				if ($fileSizeInMB > 3) {
					$this->msg = t("File size exceeds the 3MB limit.");
					$this->responseJson();
				}

				$upload_path = "upload/audio";
				$uploadDir = CommonUtility::uploadDestination($upload_path);
				$filePath = "$uploadDir/$fileName";
				if (file_put_contents($filePath, $audioData)) {
					$this->code = 1;
					$this->msg = t("File uploaded successfully!");					
					$file_url = CommonUtility::getHomebaseUrl()."/$upload_path/$fileName";
					$this->details = [
						'filename'=>$fileName,
						'file_url'=>$file_url,
						'fileSizeInMB'=>CommonUtility::HumanFilesize($fileSizeInMB)
					];
				} else {
					$this->msg = t("Failed to save the file.");
				}
			} else $this->msg = t("Invalid input data.");
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		    
		}	
		$this->responseJson();
	}

	public function actionset_category_available()
	{
		$merchant_id = intval(Yii::app()->merchant->merchant_id);		
		$id =  Yii::app()->request->getPost('id', null);  
		$checked =  Yii::app()->request->getPost('checked', 0);  		
		$model = AR_category_sort::model()->find("cat_id=:cat_id AND merchant_id=:merchant_id",[
			':cat_id'=>intval($id),
			':merchant_id'=>intval($merchant_id)
		]);
		if($model){			
			$model->available = intval($checked);
			$model->save();
			$this->code = 1; $this->msg = "OK";
			$this->details = array(
			  'next_action'=>"silent",			  
			);
		} else $this->msg = t(HELPER_RECORD_NOT_FOUND);
		$this->responseJson();
	}

	public function actionset_subcategory_available()
	{
		$merchant_id = intval(Yii::app()->merchant->merchant_id);		
		$id =  Yii::app()->request->getPost('id', null);  
		$checked =  Yii::app()->request->getPost('checked', 0);  		
		$model = AR_subcategory_sort::model()->find("subcat_id=:subcat_id AND merchant_id=:merchant_id",[
			':subcat_id'=>intval($id),
			':merchant_id'=>intval($merchant_id)
		]);
		if($model){			
			$model->available = intval($checked);
			$model->save();
			$this->code = 1; $this->msg = "OK";
			$this->details = array(
			  'next_action'=>"silent",			  
			);
		} else $this->msg = t(HELPER_RECORD_NOT_FOUND);
		$this->responseJson();
	}

	public function actionset_subcategoryitem_available()
	{
		$merchant_id = intval(Yii::app()->merchant->merchant_id);		
		$id =  Yii::app()->request->getPost('id', null);  
		$checked =  Yii::app()->request->getPost('checked', 0);  		
		$model = AR_subcategory_item_sort::model()->find("sub_item_id=:sub_item_id AND merchant_id=:merchant_id",[
			':sub_item_id'=>intval($id),
			':merchant_id'=>intval($merchant_id)
		]);
		if($model){						
			$model->available = intval($checked);			
			$model->save();			
			$this->code = 1; $this->msg = "OK";
			$this->details = array(
			  'next_action'=>"silent",			  
			);
		} else $this->msg = t(HELPER_RECORD_NOT_FOUND);
		$this->responseJson();
	}

	public function actionfree_item_list()
	{
		$id = (integer) Yii::app()->merchant->merchant_id;

	    $cols[]=array(
			'key'=>'a.promo_id',
			'value'=>'promo_id',
			//'action'=>"item_photo", 	  
		);		 		 			

		$cols[]=array(
			'key'=>'a.free_item_id',
			'value'=>'item_name',			
		);		 		 	

		$cols[]=array(
			'key'=>'a.minimum_cart_total',
			'value'=>'minimum_cart_total',			
		);		 		 	
		
		$cols[]=array(
			'key'=>'a.max_free_quantity',
			'value'=>'max_free_quantity',			
		);		 		 	

		$cols[]=array(
			'key'=>'a.auto_add',
			'value'=>'auto_add',			
		);		 		 	

		$cols[]=array(
			'key'=>'a.status',
			'value'=>'status',
			'action'=>"format",	
			'format'=>'<span class="badge ml-2 post [status]">[status]</span>',
			'format_value'=>[
				'[status]'=>[
					'value'=>'status',
					'display'=>"t"
				]
			]
		);		 		 	
					
		$cols[]=array(
			'key'=>'a.created_at',
			'value'=>'created_at',
			'action'=>"editdelete",
			'id'=>'promo_id'
		);	 	
		
		$stmt = "	
		SELECT SQL_CALC_FOUND_ROWS 
		a.*,		
		IF(COALESCE(NULLIF(item_translation.item_name, ''), '') = '', item.item_name, item_translation.item_name) as item_name
	
		FROM {{item_free_promo}} a	

		LEFT JOIN {{item}} item
		ON
		a.free_item_id = item.item_id

		LEFT JOIN {{item_translation}} item_translation
		ON
		a.free_item_id = item_translation.item_id
		AND item_translation.language=".q(Yii::app()->language)."

		WHERE a.merchant_id = ".q($id)."
		[and]
		[search]
		[order]
		[limit]
		";		 			 	
		 			 					 	
		DatatablesTools::$action_edit_path = "merchant/free_item_update";		
		 	 			 			
	 	if( $feed_data = DatatablesTools::getTables($cols,$stmt,$this->data)){		 		
	 		$this->DataTablesData($feed_data);
	 		Yii::app()->end();
	 	}		 
		$this->DataTablesNodata();		
	}

}
/*end class*/