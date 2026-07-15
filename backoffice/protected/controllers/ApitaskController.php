<?php
class ApitaskController extends CommonApi
{			

	public function beforeAction($action)
	{								
		$method = Yii::app()->getRequest()->getRequestType();
		if($method=="PUT"){
			$this->data = Yii::app()->input->xssClean(json_decode(file_get_contents('php://input'), true));
		} else $this->data = Yii::app()->input->xssClean($_POST);				
		return true;
	}

    public function actiongetDriverGroups()
    {
        try {
            
            $q = isset($this->data['q'])?$this->data['q']:'';            
            $merchant_id = isset($this->data['merchant_id'])?$this->data['merchant_id']:0; 

            $criteria=new CDbCriteria();            
            $criteria->addCondition("merchant_id=:merchant_id");
            $criteria->params = [
                ':merchant_id'=>intval($merchant_id)
            ];
            if(!empty($q)){
                $criteria->addSearchCondition('group_name',$q);
            }
            $criteria->order = "group_name ASC";
            $criteria->limit = 20;
            
            $model = AR_driver_group::model()->findAll($criteria);
            if($model){
                $default_group = $model[0]->group_id;
                foreach ($model as $items) {
                    $data[]  = [
                        'label'=>$items->group_name,
                        'value'=>$items->group_id,
                    ];
                }
                $this->code  = 1;
                $this->msg = "OK";
                $this->details = [
                    'default_group'=>$default_group,
                    'groups'=>$data    
                ];
            } else $this->msg = t(HELPER_NO_RESULTS);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();	
    }

    public function actiongetZoneList()
    {
        try {

            $q = isset($this->data['q'])? trim($this->data['q']) : '';

            $data = [];            
            $criteria=new CDbCriteria();  
            $criteria->addCondition("merchant_id=0");
            if(!empty($q)){
                $criteria->addSearchCondition("zone_name",$q);
            }
            $model = AR_zones::model()->findAll($criteria);
            if($model){
                foreach ($model as $items) {
                    $data[] = [
                        'label'=>$items->zone_name,
                        'value'=>$items->zone_id,
                    ];
                }         
                $this->code  = 1;
                $this->msg = "OK";
                $this->details = $data;               
            } else $this->msg = t(HELPER_NO_RESULTS);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();	
    }

    public function actiongetdriverlist()
    {
        try {

            $on_demand_availability = isset(Yii::app()->params['settings'])? (isset(Yii::app()->params['settings']['driver_on_demand_availability'])?Yii::app()->params['settings']['driver_on_demand_availability']:false) :false;
			$on_demand_availability = $on_demand_availability==1?true:false;			

            
            $group_selected = isset($this->data['group_selected'])? intval($this->data['group_selected']) : 0;
            $q = isset($this->data['q'])? trim($this->data['q']) : '';
            $merchant_id = isset($this->data['merchant_id'])? trim($this->data['merchant_id']) : 0;
            $zone_id = isset($this->data['zone_id'])? intval($this->data['zone_id']) : 0;            

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
                $criteria->addCondition("a.merchant_id=:merchant_id AND a.status=:status AND a.driver_id IN (
                    select driver_id from {{driver_schedule}}
                    where DATE(time_start)=".q($now)."
                    AND DATE(shift_time_started	) IS NOT NULL  
                    AND DATE(shift_time_ended) IS NULL  
                    $and_zone                    
                )");
            }

            if($group_selected>0){
                $criteria->params = [
                    ':merchant_id'=>intval($merchant_id),
                    ':group_id'=>$group_selected,
                    ':status'=>'active'
                ];
            } else {
                $criteria->params = [
                    ':merchant_id'=>intval($merchant_id),
                    ':status'=>'active'
                ];
            }            
            
            if(!empty($q)){
                $criteria->addSearchCondition('a.first_name', $q );
                $criteria->addSearchCondition('a.last_name', $q , true , 'OR' );            
            }                         

            // ON DEMAND
			if($on_demand_availability){
				$and_merchant_zone = '';		
                if($zone_id>0){
                    $in_query = CommonUtility::arrayToQueryParameters([$zone_id]);
                    $and_merchant_zone = "
					AND a.driver_id IN (
						select driver_id from {{driver_schedule}}
						where 
						merchant_id=0 and driver_id = a.driver_id 
						and on_demand=1 and zone_id IN ($in_query)
					)
					";
                }		
				$criteria->addCondition("a.merchant_id=:merchant_id AND a.is_online=:is_online AND a.status=:status $and_merchant_zone ");
				$criteria->params = [
					':merchant_id'=>intval($merchant_id),
					':is_online'=>1,
					':status'=>"active"
				];			
				if($group_selected>0){
					$criteria->params[':group_id']=$group_selected;
				}				
			}

            $criteria->order = "first_name ASC";
            $criteria->limit = 20;              
            
            //dump($criteria);die();
            if($model = AR_driver::model()->findAll($criteria)){
                $data = array();
                foreach ($model as $items) {
                    $data[] = [
                      'label'=>$items->first_name." ".$items->last_name,
                      'value'=>$items->driver_id,
                    ];
                }
                $this->code  = 1;
                $this->msg = "OK";
                $this->details = $data;
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

            $order_uuid = isset($this->data['order_uuid'])? trim($this->data['order_uuid']) : 0;
            $driver_id = isset($this->data['driver_id'])? intval($this->data['driver_id']) : 0;          

            $order = COrders::get($order_uuid);

            $meta = AR_admin_meta::getValue('status_assigned');
            $status_assigned = isset($meta['meta_value'])?$meta['meta_value']:''; 
            
            $options = OptionsTools::find(['driver_allowed_number_task']);
            $allowed_number_task = isset($options['driver_allowed_number_task'])?$options['driver_allowed_number_task']:0;


            $order->scenario = "assign_order";
            $order->on_demand_availability = $on_demand_availability;
            $order->driver_id = intval($driver_id);
            $order->delivered_old_status = $order->delivery_status;
            $order->delivery_status = $status_assigned;
            $order->change_by = Yii::app()->user->first_name;
            $order->date_now = date("Y-m-d");
            $order->allowed_number_task = intval($allowed_number_task);
            
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
                $this->msg = "OK";
            } else $this->msg = CommonUtility::parseModelErrorToString($order->getErrors());
        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();	
    }

    public function actionDriverInformation()
    {
        try {
            
            $driver_id = isset($this->data['driver_id'])? intval($this->data['driver_id']) : 0;
            $order_uuid = isset($this->data['order_uuid'])? trim($this->data['order_uuid']) : '';
            if($driver_id<=0){
                $order = COrders::get($order_uuid);
                $driver_id = $order->driver_id;
            }
            $model = CDriver::getDriver($driver_id);
            if($model){                

                $vehicle = [];
                $vehicle_maker = CommonUtility::getDataToDropDown("{{admin_meta}}","meta_id",'meta_value',"WHERE meta_name='vehicle_maker'","Order by meta_name");
		        $vehicle_type = CommonUtility::getDataToDropDown("{{admin_meta}}","meta_id",'meta_value',"WHERE meta_name='vehicle_type'","Order by meta_name");

                try {
                    $now = date("Y-m-d");
                    //$vehicle_model = CDriver::getVehicleAssign($driver_id,$now);
                    $vehicle_model = CDriver::getVehicle($order->vehicle_id);                       
                    $vehicle = [
                        'vehicle_uuid'=>$vehicle_model->vehicle_uuid,
                        'plate_number'=>$vehicle_model->plate_number,
                        'maker'=>$vehicle_model->maker,
                        'model'=>$vehicle_model->model,
                        'color'=>$vehicle_model->color,
                        'photo'=>$vehicle_model->photo,
                        'photo_url'=>CMedia::getImage($vehicle_model->photo,$vehicle_model->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('car','car.png')),
                        'active'=>$vehicle_model->active,
                    ];
                } catch (Exception $e) {
                }

                $license_photo = array();
                $license_photo[] = CMedia::getImage($model->license_front_photo,$model->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('customer'));
                $license_photo[] = CMedia::getImage($model->license_back_photo,$model->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('customer'));

                $data = [
                    'full_name'=>"$model->first_name $model->last_name",
                    'email'=>$model->email,
                    'phone_prefix'=>$model->phone_prefix,
                    'phone'=>$model->phone,
                    'license_number'=>$model->license_number,
                    'photo'=>CMedia::getImage($model->photo,$model->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('customer')),
                    'license_photo'=>$license_photo
                ];
                $this->code = 1;
                $this->msg = "OK";
                $this->details = [
                    'driver_info'=>$data,
                    'vehicle_info'=>$vehicle,
                    'vehicle_maker'=>$vehicle_maker,
                    'vehicle_type'=>$vehicle_type
                ];
            } else $this->msg = t(HELPER_NO_RESULTS);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();	
    }

    public function actionGetDriverInfo()
    {
        try {
            
            $driver_id = isset($this->data['driver_id'])? intval($this->data['driver_id']) : 0;
            $model = CDriver::getDriver($driver_id);

            $license_photo = array();
            $license_photo[] = CMedia::getImage($model->license_front_photo,$model->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('customer'));
            $license_photo[] = CMedia::getImage($model->license_back_photo,$model->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('customer'));

            $data = [
                'full_name'=>"$model->first_name $model->last_name",
                'email'=>$model->email,
                'phone_prefix'=>$model->phone_prefix,
                'phone'=>$model->phone,
                'license_number'=>$model->license_number,
                'photo'=>CMedia::getImage($model->photo,$model->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('customer')),
                'license_photo'=>$license_photo
            ];
            $this->code = 1;
            $this->msg = "OK";
            $this->details = [
                'driver_info'=>$data,                
            ];

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();
    }

    public function actionCustomerInformation()
    {
        try {

            dump($this->data);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();	
    }

    public function actiongetattributestatus()
    {
        try {

            $data = [];
            try{
                $data = CDriver::deliveryStatusList();
            } catch (Exception $e) {
                //
            }

            $delivery_status = '';
            try {
                $order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';
                $order = COrders::get($order_uuid);
                $delivery_status = $order->delivery_status;                
            } catch (Exception $e) {
                //
            }            
            
            //$delivery_status_list = AttributesTools::getOrderStatus(Yii::app()->language,'delivery_status');
            $delivery_status_list = AttributesTools::getOrderStatusWithColor(Yii::app()->language,'delivery_status');
            
            $this->code = 1;
            $this->msg = "OK";
            $this->details = [
                'delivery_status_list'=>$data,
                'delivery_status_data'=>$delivery_status_list,
                'delivery_status'=>$delivery_status
            ];

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();	
    }

    public function actionchangestatus()
    {
        try {
                        
            $delivery_status = isset($this->data['delivery_status'])?$this->data['delivery_status']:'';
            $order_uuid = isset($this->data['order_uuid'])?$this->data['order_uuid']:'';
            $model = COrders::get($order_uuid);         
            
            $meta = AR_admin_meta::getValue('status_unassigned');
            $status_unassigned = isset($meta['meta_value'])?$meta['meta_value']:'';              
            
            if($model){
                $model->scenario = "delivery_change_status";
                $model->delivered_old_status = $model->delivery_status;
                $model->delivery_status = $delivery_status;
                $model->change_by = Yii::app()->user->first_name;

                if($status_unassigned==$delivery_status){
                    $model->driver_id = 0;
                    $model->vehicle_id = 0;
                }

                if($model->save()){
                    $this->code  = 1;
                    $this->msg = t("Successful");
                } else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
            } else $this->msg = t(HELPER_NO_RESULTS);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();	
    }

    public function actiongetordertotal()
    {
        try {

            $q = isset($this->data['q'])?$this->data['q']:'';        
            $uunassigned_group = AOrders::getOrderTabsStatus('unassigned');
            $assigned_group = AOrders::getOrderTabsStatus('assigned');
            $completed_group = AOrders::getOrderTabsStatus('completed');
            
            $now = date("Y-m-d");
            $uunassigned_group_count = CDriver::getOrdersByStatusCount($uunassigned_group,$now,$q);
            $assigned_group_count = CDriver::getOrdersByStatusCount($assigned_group,$now,$q);
            $completed_group_count = CDriver::getOrdersByStatusCount($completed_group,$now,$q);

            $this->code = 1;
            $this->msg = "OK";
            $this->details = [
                'uunassigned_group'=>$uunassigned_group_count,
                'assigned_group'=>$assigned_group_count,
                'completed_group'=>$completed_group_count,
            ];

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();	
    }

    public function actiongetDriverOrdersList()
	{
		try {
			                      
            $driver_id = isset($this->data['driver_id'])? intval($this->data['driver_id']) :0;
            $date_now = isset($this->data['date_now'])? trim($this->data['date_now']) :'';            

            //$order_status = AttributesTools::getOrderStatus(Yii::app()->language,'delivery_status');            
            $order_status = AttributesTools::getOrderStatusWithColor(Yii::app()->language,'delivery_status');            
            
            $result = CDriver::getOrdersByDriverID($driver_id,$date_now);            
            $data = [];
            foreach ($result as $key => $items) {                
                $avatar = '';
                $meta = !empty($items->meta) ? explode("|",$items->meta) : '';                         
                if(is_array($meta) && count($meta)>=1){
                    $avatar = CMedia::getImage($meta[0],$meta[1],'@thumbnail',CommonUtility::getPlaceholderPhoto('customer')); 
                }                
                $data[]=[
                    'order_id'=>$items->order_id,
                    'full_name'=>$items->customer_name,
                    'address'=>$items->formatted_address,
                    'delivery_status_raw'=>$items->delivery_status,
                    'delivery_status'=>isset($order_status[$items->delivery_status])?$order_status[$items->delivery_status]['label']:$items->delivery_status,
                    'avatar'=>$avatar
                ];
            }            
            
            $this->code = 1;
            $this->msg = "OK";
            $this->details = [
                'data'=>$data,
                'order_status'=>$order_status
            ];
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();		
	}

    public function actiongetOrders()
	{
		try {
			            
			$now = date("Y-m-d");
			$status = isset($this->data['status'])?$this->data['status']:'';
            $q = isset($this->data['q'])?$this->data['q']:'';
			$result = CDriver::getOrdersByStatus($status,$now,$q);	   
            //dump($result);die();

            $orders_location = CDriver::getCoordinatesByOrderID($result['order_ids']);

			$merchant = $result['merchant'];
			$drivers = $result['drivers'];
			$total = intval($result['total']);
			$merchant_list = CMerchants::getListByID($merchant);
			$data = $result['data'];            

			$order_status = AttributesTools::getOrderStatusWithColor(Yii::app()->language,'delivery_status');
            //$order_status = AttributesTools::getOrderStatus(Yii::app()->language,'delivery_status');

			$group_name = 'new_order';
		    $status_new = AOrders::getOrderTabsStatus($group_name);			

			$drivers_data = [];
			try {
				$drivers_data = CDriver::getDriverListByIDS($drivers);
			} catch (Exception $e) {
				//
			}
            
            $merchant_zone = CMerchants::getListMerchantZone($merchant);
            if(!$zone_list = CommonUtility::getDataToDropDown("{{zones}}",'zone_id','zone_name')){
                $zone_list = [];
            }
            
			$this->code = 1;
			$this->msg = "OK";
			$this->details = [
				'total'=>$total,
				'data'=>$data,
				'merchant_list'=>$merchant_list,
				'order_status'=>$order_status,
				'status_new'=>$status_new,
				'drivers_data'=>$drivers_data,
                'orders_location'=>$orders_location,
                'merchant_zone'=>$merchant_zone,
                'zone_list'=>$zone_list
			];
			
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();	
	}	

    public function actiongetdriverbysched()
	{
		try {
						            
            $on_demand_availability = isset(Yii::app()->params['settings'])? (isset(Yii::app()->params['settings']['driver_on_demand_availability'])?Yii::app()->params['settings']['driver_on_demand_availability']:false) :false;
            $on_demand_availability = $on_demand_availability==1?true:false;			            
            CDriver::setOndemand($on_demand_availability);

            $status = isset($this->data['status'])?$this->data['status']:'';            
            $q = isset($this->data['q'])?$this->data['q']:''; 

			$now = date("Y-m-d");
			$driver_data = []; $total_task = [];
			$data = CDriver::getDriverTabs($now,$now,$status,$q);		
            //dump($data);die();
            
			try {
				$driver_data = CDriver::getDriverListByIDS($data['drivers']);
			} catch (Exception $e) {
				//
			}
			
			try {
				$assigned_group = AOrders::getOrderTabsStatus('assigned');
                //$completed = AOrders::getOrderTabsStatus('completed');                
                //$all_status = array_merge((array)$assigned_group,(array)$completed);                
				$total_task = CDriver::getTotalTaskByDriverIDS($data['drivers'],$assigned_group,$now);
			} catch (Exception $e) {
				//
			}

            if(!$zone_list = CommonUtility::getDataToDropDown("{{zones}}",'zone_id','zone_name')){
                $zone_list = [];
            }
            
			$this->code = 1;
			$this->msg = "OK";
			$this->details = [
			   'data'=>$data,
			   'driver_data'=>$driver_data,
			   'total_task'=>$total_task,
               'zone_list'=>$zone_list,
               'date_now'=>date("c"),        
               'on_demand_availability'=>$on_demand_availability
			];			

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();	
	}	

    public function actiongetdriveractivity()
    {
        try {
            
            $driver_id = isset($this->data['driver_id'])?$this->data['driver_id']:'';
            $date_now = isset($this->data['date_now'])?$this->data['date_now']:date("Y-m-d");
            $model = CDriver::getActivity($driver_id,$date_now,$date_now);
            if($model){
                $data = []; $reference = array();
                $status_data = AttributesTools::getOrderStatus(Yii::app()->language,'delivery_status');

                foreach ($model as $items) {
                    $args = !empty($items->remarks_args) ?  json_decode($items->remarks_args,true) : array();
                    if($items->reference_id>0){
                        $reference[] = $items->reference_id;
                    }                    
                    $data[] = [
                        'created_at'=>Date_Formatter::dateTime($items->created_at),
                        'driver_id'=>$items->driver_id,
                        'order_id'=>$items->order_id,
                        'status'=>isset($status_data[$items->status])?$status_data[$items->status]:$items->status,
                        'remarks'=>t($items->remarks,(array)$args),
                        'latitude'=>$items->latitude,
                        'longitude'=>$items->longitude,
                        'reference_id'=>$items->reference_id
                    ];
                }

                $meta = array();
                try {
                    $meta = Cdriver::getMetaAll($reference,'car_proof');
                } catch (Exception $e) {}                

                $this->code = 1;
                $this->msg = "OK";
                $this->details = [
                    'data'=>$data,
                    'status_data'=>$status_data,
                    'meta'=>$meta
                ];
            } else $this->msg = t(HELPER_NO_RESULTS);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();	
    }

    public function actiongettotalorders()
    {
        try {

            $length = isset($this->data['lenght'])?$this->data['lenght']:10;            
            $initial_status = AttributesTools::initialStatus();
            $transaction_type = 'delivery';
            $criteria=new CDbCriteria();
            $criteria->alias="a";
            $criteria->select="a.*,b.meta_value as customer_name";
            $criteria->join = "LEFT JOIN {{ordernew_meta}} b ON a.order_id = b.order_id";
            $criteria->addCondition("a.service_code=:service_code AND b.meta_name=:meta_name");
            $criteria->params = [
                ':service_code'=>$transaction_type,
                ':meta_name'=>"customer_name"
            ];
            $criteria->addNotInCondition('a.status',(array)$initial_status);            
		    $count = AR_ordernew::model()->count($criteria);
            $pages=new CPagination( intval($count) );            
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);      
            $page_count = $pages->getPageCount();       
            $this->code = 1;
            $this->msg = "OK";
            $this->details = $page_count;

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();	
    }

    public function actiongetorderlist()
    {
        try {
                        
            $sortby = isset($this->data['sortBy'])?$this->data['sortBy']:'order_id';
            $sortby = !empty($sortby)?$sortby:'order_id';
            $sort =  isset($this->data['descending'])?$this->data['descending']:false;
            $sort = $sort==true?"asc":"desc";
            $length = isset($this->data['rowsPerPage'])?$this->data['rowsPerPage']:10;	
            $page = isset($this->data['page'])?intval($this->data['page']):1;	
            $q = isset($this->data['q'])?trim($this->data['q']):'';	

            $initial_status = AttributesTools::initialStatus();
            $transaction_type = 'delivery';
            $criteria=new CDbCriteria();
            $criteria->alias="a";
            $criteria->select="a.*,b.meta_value as customer_name";
            $criteria->join = "LEFT JOIN {{ordernew_meta}} b ON a.order_id = b.order_id";
            $criteria->addCondition("a.service_code=:service_code AND b.meta_name=:meta_name");
            $criteria->params = [
                ':service_code'=>$transaction_type,
                ':meta_name'=>"customer_name"
            ];
            $criteria->addNotInCondition('a.status',(array)$initial_status);

            if(!empty($q)){
                $criteria->addSearchCondition('a.order_id',$q);
            }

            $criteria->order = "$sortby $sort";            
		    $count = AR_ordernew::model()->count($criteria); 

            $pages=new CPagination( intval($count) );
            $pages->setCurrentPage( intval($page) );              
            $pages->pageSize = intval($length);
            $pages->applyLimit($criteria);      
            $page_count = $pages->getPageCount();        
            
            $model = AR_ordernew::model()->findAll($criteria);            
            if($model){
                $data = []; $driver_ids = []; $client_ids = [];
                foreach ($model as $items) {
                    $time = !empty($items->delivery_time)? Date_Formatter::Time($items->delivery_time) :t("Asap");
                    if($items->driver_id>0){
                        $driver_ids[$items->driver_id] = $items->driver_id;
                    }                    
                    if($items->client_id>0){
                        $client_ids[$items->client_id] = $items->client_id;
                    }                    
                    $data[] = [
                        'order_id'=>$items->order_id,
                        'service_code'=>$items->service_code,
                        'client_id'=>$items->client_id,
                        'customer_name'=>$items->customer_name,
                        'formatted_address'=>$items->formatted_address,
                        'delivery_date'=>Date_Formatter::date($items->delivery_date) ." $time",
                        'driver_id'=>$items->driver_id,
                        'rating'=>0,
                        'delivery_status'=>$items->delivery_status,
                        'link'=>Yii::app()->createAbsoluteUrl("/order/view",[
                            'order_uuid'=>$items->order_uuid
                        ])
                    ];
                }

                $driver_data = [];
                try {
                    $driver_data = CDriver::getDriverListByIDS($driver_ids);
                } catch (Exception $e) {
                    //                    
                }   
                
                $client_data = [];
                try {
                    $client_data = ACustomer::getByIDS($client_ids);
                } catch (Exception $e) {
                    //                    
                }   

                $delivery_status_list = AttributesTools::getOrderStatusWithColor(Yii::app()->language,'delivery_status');

                $this->code = 1;
                $this->msg = "OK";
                $this->details = [
                    'data'=>$data,
                    'driver_data'=>$driver_data,
                    'client_data'=>$client_data,
                    'delivery_status_list'=>$delivery_status_list,
                    'pagination'=>[                        
                        'page_count'=>$page_count,
                        'length'=>$length,
                        'page'=>$page,
                        'sort'=>$sort=="asc"?false:true
                    ]
                ];
            } else $this->msg  = t(HELPER_NO_RESULTS);

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();	
    }

    public function actiongetTotalDriversByTabs()
    {
        try {

            $on_demand_availability = isset(Yii::app()->params['settings'])? (isset(Yii::app()->params['settings']['driver_on_demand_availability'])?Yii::app()->params['settings']['driver_on_demand_availability']:false) :false;
			$on_demand_availability = $on_demand_availability==1?true:false;			

            $now = date("Y-m-d");
            $q = isset($this->data['q'])?trim($this->data['q']):'';	

            CDriver::setOndemand($on_demand_availability);
            $duty = CDriver::TotalDriversByTabs($now,$now,'duty',$q);
            $busy = CDriver::TotalDriversByTabs($now,$now,'busy',$q);
            $this->code = 1;
            $this->msg = "OK";
            $this->details = [
                'duty'=>intval($duty),
                'busy'=>intval($busy)
            ];            

        } catch (Exception $e) {
		    $this->msg = t($e->getMessage());			    
		}	
		$this->responseJson();	
    }

}
// end class