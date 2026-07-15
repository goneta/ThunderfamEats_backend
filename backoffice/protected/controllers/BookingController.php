<?php
require 'php-jwt/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class BookingController extends Commonmerchant
{
		
	public function beforeAction($action)
	{				
		
		InlineCSTools::registerStatusCSS();
		InlineCSTools::registerOrder_StatusCSS();
			
		return true;
	}		

	public function actionsettings()
	{
		$this->pageTitle = t("Booking Settings");
		$id = (integer) Yii::app()->merchant->merchant_id;

		$model=new AR_option;	
		
		$options = [
			'booking_enabled','booking_enabled_capcha','booking_allowed_choose_table','booking_reservation_custom_message',
			'booking_reservation_terms'
		];
		
		if(isset($_POST['AR_option'])){
			if(DEMO_MODE && in_array($id,DEMO_MERCHANT)){
				 $this->render('//tpl/error',array(  
					  'error'=>array(
						'message'=>t("Modification not available in demo")
					  )
					));	
				return false;
			}
			$model->attributes=$_POST['AR_option'];				
			if($model->validate()){			
				$model->booking_days_of_week = json_encode($model->booking_days_of_week);				
				OptionsTools::$merchant_id = $id;									
				if(OptionsTools::save($options, $model, $id)){
					Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
					$this->refresh();
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			} 
		}

		if($data = OptionsTools::find($options,$id)){
			foreach ($data as $name=>$val) {
				if($name=="booking_days_of_week"){
					$model[$name]=!empty($val)?json_decode($val,true):array();
				} else $model[$name]=$val;
			}					
		}

		$template_list =  CommonUtility::getDataToDropDown("{{templates}}",'template_id','template_name',
		"","ORDER BY template_name ASC");

		$this->render("booking_settings",[
			'model'=>$model,			
			'links'=>array(	
				t("Booking Settings")
			),				   
			'template_list'=>$template_list
		]);
	}

	public function actionshifts()
	{
		$this->pageTitle = t("Shift list");

		$action_name='table_layout';
		$delete_link = Yii::app()->CreateUrl("booking/shift_delete");
		
		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
		),$action_name);

		$table_col = array(            
            'shift_id'=>array(
              'label'=>t("Shift ID"),
              'width'=>'8%'
            ),
            'shift_name'=>array(
              'label'=>t("Name"),
              'width'=>'20%'
            ),
			'first_seating'=>array(
				'label'=>t("First/Last Seating"),
				'width'=>'15%'
			),
			'shift_interval'=>array(
				'label'=>t("Interval"),
				'width'=>'15%'
			),
            'status'=>array(
              'label'=>t("Status"),
              'width'=>'15%'
            ),            
            'date_created '=>array(
                'label'=>t("Action"),
                'width'=>'10%'
              ),
          );
          $columns = array(            
            array('data'=>'shift_id','visible'=>false),
            array('data'=>'shift_name'),
			array('data'=>'first_seating'),
			array('data'=>'shift_interval'),
            array('data'=>'status'),            
            array('data'=>'date_created','orderable'=>false),
        );		

		$this->render("booking_shift_list",[
			'table_col'=>$table_col,
            'columns'=>$columns,
            'order_col'=>0,
            'sortby'=>'desc',
			'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/create_shift"),			
		]);
	}

	public function actionupdate_shift()
	{
		$this->actioncreate_shift(true);
	}

	public function actioncreate_shift($update=false)
	{
		$this->pageTitle = $update==false? t("Add Shift") : t("Update Shift");
		CommonUtility::setMenuActive('.table_booking','.booking_shifts');		
		
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;
		
		if($update){
			$id = trim(Yii::app()->input->get('id'));
			
			$model = AR_table_shift::model()->find('merchant_id=:merchant_id AND shift_uuid=:shift_uuid', 
		    array(':merchant_id'=>$merchant_id, ':shift_uuid'=>$id ));			
		    			
			if(!$model){				
				$this->render("//tpl/error",array(
				 'error'=>array( 'message'=>t(HELPER_RECORD_NOT_FOUND))
				));			
				Yii::app()->end();
			}					
			$model->days_of_week = json_decode($model->days_of_week,true);
		} else $model=new AR_table_shift;

		if(isset($_POST['AR_table_shift'])){
			$model->attributes=$_POST['AR_table_shift'];
			$model->merchant_id = $merchant_id;
			if($model->validate()){						
				if($model->save()){
					if(!$update){
					   $this->redirect(array(Yii::app()->controller->id.'/shifts'));		
					} else {
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
						$this->refresh();
					}
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			} else {				
				Yii::app()->user->setFlash('error',t(HELPER_CORRECT_FORM));
			}
		}

		$model->status = $model->isNewRecord?'publish':$model->status;			

		$this->render("shift_create",array(
		    'model'=>$model,			    
		    'status'=>(array)AttributesTools::StatusManagement('post'),
			'day_list'=>AttributesTools::dayList(),
			'time_range'=>AttributesTools::createTimeRange("00:00","24:00"),
			'time_interval'=>AttributesTools::timeInvertval(),
		    'links'=>array(
	            t("Shift")=>array(Yii::app()->controller->id.'/shifts'),        
                $this->pageTitle,
		    ),	    		    
		));
	}

	public function actionshift_delete()
	{
		$id = trim(Yii::app()->input->get('id'));			
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;
		
		$model = AR_table_shift::model()->find("merchant_id=:merchant_id AND shift_uuid=:shift_uuid",array(		  
		  ':merchant_id'=>$merchant_id,
		  ':shift_uuid'=>$id
		));		
						
		if($model){
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array(Yii::app()->controller->id.'/shifts'));			
		} else $this->render("//tpl/error",array(
			'error'=>array( 'message'=>t(HELPER_RECORD_NOT_FOUND))
		   ));			
	}


	public function actionroom()
	{
		$this->pageTitle = t("Room list");

		$action_name='table_layout';
		$delete_link = Yii::app()->CreateUrl("booking/room_delete");
		
		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
		),$action_name);

		$table_col = array(            
            'room_id'=>array(
              'label'=>t("Room ID"),
              'width'=>'12%'
            ),
            'room_name'=>array(
              'label'=>t("Room Name"),
              'width'=>'25%'
            ),
			'capacity'=>array(
				'label'=>t("Capacity"),
				'width'=>'15%'
			),
			'total_tables'=>array(
				'label'=>t("Total tables"),
				'width'=>'15%'
			),
            'status'=>array(
              'label'=>t("Status"),
              'width'=>'15%'
            ),            
            'date_created '=>array(
                'label'=>t("Action"),
                'width'=>'10%'
              ),
          );
          $columns = array(            
            array('data'=>'room_id'),
            array('data'=>'room_name'),
			array('data'=>'capacity'),
			array('data'=>'total_tables'),
            array('data'=>'status'),            
            array('data'=>'date_created','orderable'=>false),
        );		

		$this->render("booking_room_list",[
			'table_col'=>$table_col,
            'columns'=>$columns,
            'order_col'=>0,
            'sortby'=>'desc',
			'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/create_room"),
			'link1'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/create_table"),
		]);
	}

	public function actionroom_delete()
	{
		$id = trim(Yii::app()->input->get('id'));			
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;
		
		$model = AR_table_room::model()->find("merchant_id=:merchant_id AND room_uuid=:room_uuid",array(		  
		  ':merchant_id'=>$merchant_id,
		  ':room_uuid'=>$id
		));		
						
		if($model){
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array(Yii::app()->controller->id.'/room'));			
		} else $this->render("//tpl/error",array(
			'error'=>array( 'message'=>t(HELPER_RECORD_NOT_FOUND))
		   ));			
	}

	public function actionupdate_room()
	{
		$this->actioncreate_room(true);
	}

	public function actioncreate_room($update=false)
	{
		
		$this->pageTitle = $update==false? t("Add Room") : t("Update Room");
		CommonUtility::setMenuActive('.table_booking','.booking_room');		
		
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;		
		
		if($update){
			$id = trim(Yii::app()->input->get('room_uuid'));
			
			$model = AR_table_room::model()->find('merchant_id=:merchant_id AND room_uuid=:room_uuid', 
		    array(':merchant_id'=>$merchant_id, ':room_uuid'=>$id ));			
		    			
			if(!$model){				
				$this->render("//tpl/error",array(
				 'error'=>array( 'message'=>t(HELPER_RECORD_NOT_FOUND))
				));			
				Yii::app()->end();
			}												
		} else $model=new AR_table_room;

		if(isset($_POST['AR_table_room'])){
			$model->attributes=$_POST['AR_table_room'];
			if($model->validate()){		
				$model->merchant_id = $merchant_id;
				if($model->save()){
					if(!$update){
					   $this->redirect(array(Yii::app()->controller->id.'/room'));		
					} else {
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
						$this->refresh();
					}
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			} else Yii::app()->user->setFlash('error',t(HELPER_CORRECT_FORM));
		}

		$model->status = $model->isNewRecord?'publish':$model->status;	

		$this->render("room_create",array(
		    'model'=>$model,			    
		    'status'=>(array)AttributesTools::StatusManagement('post'),
		    'links'=>array(
	            t("Room List")=>array(Yii::app()->controller->id.'/room'),        
                $this->pageTitle,
		    ),	    		    
		));
	}

	public function actionupdate_tables()
	{
		$this->actioncreate_table(true);
	}

	public function actioncreate_table($update=false)
	{
		$this->pageTitle = $update==false? t("Add Table") : t("Update Table");
		CommonUtility::setMenuActive('.table_booking','.booking_tables');		
		
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;
		
		if($update){
			$id = trim(Yii::app()->input->get('id'));			
			
			$model = AR_table_tables::model()->find('merchant_id=:merchant_id AND table_uuid=:table_uuid', 
		    array(':merchant_id'=>$merchant_id, ':table_uuid'=>$id ));			
											
			if(!$model){				
				$this->render("//tpl/error",array(
				 'error'=>array( 'message'=>t("".HELPER_RECORD_NOT_FOUND))
				));			
				Yii::app()->end();
			}					
						
			$model->room_id_selected = CommonUtility::getDataToDropDown("{{table_room}}",'room_id','room_name',
			"WHERE room_id=".q($model->room_id)."");	

			$table_model = AR_table_status::model()->find("table_uuid=:table_uuid",[
				':table_uuid'=>$id
			]);
			if($table_model){				
				$model->table_status = $table_model->status;
			}

		} else $model=new AR_table_tables;

		if(isset($_POST['AR_table_tables'])){
			$model->attributes=$_POST['AR_table_tables'];
			if($model->validate()){		
				$model->merchant_id = $merchant_id;
				if($model->save()){
					if(!$update){
					   $this->redirect(array(Yii::app()->controller->id.'/tables'));		
					} else {
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
						$this->refresh();
					}
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			} else Yii::app()->user->setFlash('error',t(HELPER_CORRECT_FORM));
		}

		$model->available = $model->isNewRecord?1:$model->available;	

		$table_status_list = CommonUtility::tableStatus();

		$this->render("table_create",array(
		    'model'=>$model,			    		    
		    'links'=>array(
	            t("Table List")=>array(Yii::app()->controller->id.'/tables'),        
                $this->pageTitle,
		    ),	    		    
			'table_status_list'=>$table_status_list
		));
	}

	public function actiontables()
	{
		$this->pageTitle = t("Table list");

		$action_name='table_layout';
		$delete_link = Yii::app()->CreateUrl("booking/tables_delete");
		
		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
		),$action_name);

		$table_col = array(            
            'table_id'=>array(
              'label'=>t("Table ID"),
              'width'=>'10%'
            ),
            'table_name'=>array(
              'label'=>t("Table name"),
              'width'=>'20%'
            ),
			'room_id'=>array(
              'label'=>t("Room"),
              'width'=>'15%'
            ),
            'min_covers'=>array(
              'label'=>t("Min Covers"),
              'width'=>'15%'
            ),            
			'max_covers'=>array(
				'label'=>t("Max Covers"),
				'width'=>'15%'
			),            
			'available'=>array(
				'label'=>t("Available"),
				'width'=>'10%'
			),            
            'date_created '=>array(
                'label'=>t("Action"),
                'width'=>'10%'
              ),
          );
          $columns = array(            
            array('data'=>'table_id','visible'=>false),
            array('data'=>'table_name'),
			array('data'=>'room_id'),
            array('data'=>'min_covers'),            
			array('data'=>'max_covers'),            
			array('data'=>'available'),            
            array('data'=>'date_created','orderable'=>false),
        );		

		$this->render("booking_tables_list",[
			'table_col'=>$table_col,
            'columns'=>$columns,
            'order_col'=>0,
            'sortby'=>'desc',
			'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/create_table"),			
			'link2'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/generate_table"),			
		]);
	}

	public function actiontables_delete()
	{
		$id = trim(Yii::app()->input->get('id'));			
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;
		
		$model = AR_table_tables::model()->find("merchant_id=:merchant_id AND table_uuid=:table_uuid",array(		  
		  ':merchant_id'=>$merchant_id,
		  ':table_uuid'=>$id
		));		
						
		if($model){
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array(Yii::app()->controller->id.'/tables'));			
		} else $this->render("//tpl/error",array(
			'error'=>array( 'message'=>t(HELPER_RECORD_NOT_FOUND))
		   ));			
	}
	
	public function actionlist()
	{
		$this->pageTitle = t("Reservation list");
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;

		$action_name='reservation_list';
		$delete_link = Yii::app()->CreateUrl("booking/reservation_delete");
		
		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
		),$action_name);

		$table_col = array(            
            'reservation_id'=>array(
              'label'=>t("ID"),
              'width'=>'8%'
            ),
            'client_id'=>array(
              'label'=>t("Name"),
              'width'=>'20%'
            ),
			'guest_number'=>array(
				'label'=>t("Guest"),
				'width'=>'15%'
			),            
			'table_id'=>array(
              'label'=>t("Table"),
              'width'=>'15%'
            ),
            'reservation_date'=>array(
              'label'=>t("Date/Time"),
              'width'=>'15%'
            ),            			
			'special_request'=>array(
				'label'=>t("Request"),
				'width'=>'10%'
			),            
            'date_created '=>array(
                'label'=>t("Action"),
                'width'=>'10%'
              ),
          );
          $columns = array(            
            array('data'=>'reservation_id'),
            array('data'=>'client_id'),
			array('data'=>'guest_number'),            
			array('data'=>'table_id'),
            array('data'=>'reservation_date'),            			
			array('data'=>'special_request'),            
            array('data'=>'date_created','orderable'=>false),
        );		

		try {
			$summary = CBooking::reservationSummary($merchant_id,date("Y-m-d"));
		} catch (Exception $e) {
			$summary  = [];
		}
		

		$this->render("booking_list",[
			'ajax_url'=>Yii::app()->createUrl("/apibackend"),
			'actions'=>"reservationList",
			'table_col'=>$table_col,
            'columns'=>$columns,
            'order_col'=>0,
            'sortby'=>'desc',
			'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/create_reservation"),	
			'summary'=>$summary
		]);				
	}

	public function actionupdate_reservation()
	{
		$this->actioncreate_reservation(true);
	}

	public function actioncreate_reservation($update=false)
	{
		$this->pageTitle = $update==false? t("Add Booking") : t("Update Booking");
		CommonUtility::setMenuActive('.table_booking','.booking_list');		
		
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;

		if($update){
			$id = trim(Yii::app()->input->get('id'));			
			
			$model = AR_table_reservation::model()->find('merchant_id=:merchant_id AND reservation_uuid=:reservation_uuid', 
		    array(':merchant_id'=>$merchant_id, ':reservation_uuid'=>$id ));			
											
			if(!$model){				
				$this->render("//tpl/error",array(
				 'error'=>array( 'message'=>t("".HELPER_RECORD_NOT_FOUND))
				));			
				Yii::app()->end();
			}		
			
			$model->client_id_selected = CommonUtility::getDataToDropDown("{{client}}",'client_id','first_name',
			"WHERE client_id=".q($model->client_id)."");
			
			$model->old_status = $model->status;
											
		} else $model=new AR_table_reservation;

		if(isset($_POST['AR_table_reservation'])){
			$model->attributes=$_POST['AR_table_reservation'];			
			$model->merchant_id = intval($merchant_id);
			if($model->validate()){				
				$model->reservation_time = !empty($model->reservation_time)? Date_Formatter::TimeTo24($model->reservation_time):'';												
				if($model->save()){
					if(!$update){
					   $this->redirect(array(Yii::app()->controller->id.'/list'));		
					} else {
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
						$this->refresh();
					}
				} else {						
					Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString($model->getErrors())  );
				}					
			} else Yii::app()->user->setFlash('error',t(HELPER_CORRECT_FORM));
		}

		$table_list = CommonUtility::getDataToDropDown("{{table_tables}}",'table_id','table_name',"where available=1 and merchant_id=".q($merchant_id)." ");		
		$status_list = AttributesTools::bookingStatus();

		$this->render("reservation_create",array(
		    'model'=>$model,			
			'table_list'=>$table_list,
			'status_list'=>$status_list,
		    'links'=>array(
	            t("Reservation List")=>array(Yii::app()->controller->id.'/list'),        
                $this->pageTitle,
		    ),	    		    
		));
	}

	public function actionreservation_delete()
	{
		$id = trim(Yii::app()->input->get('id'));			
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;
		
		$model = AR_table_reservation::model()->find("merchant_id=:merchant_id AND reservation_uuid=:reservation_uuid",array(		  
		  ':merchant_id'=>$merchant_id,
		  ':reservation_uuid'=>$id
		));		
						
		if($model){
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array(Yii::app()->controller->id.'/list'));			
		} else $this->render("//tpl/error",array(
			'error'=>array( 'message'=>t(HELPER_RECORD_NOT_FOUND))
		   ));			
	}

	public function actionupdate_status()
	{
		$id = CommonUtility::safeTrim(Yii::app()->input->get('id'));			
		$status = CommonUtility::safeTrim(Yii::app()->input->get('status'));
		$redirect = CommonUtility::safeTrim(Yii::app()->input->get('redirect'));
		$merchant_id = (integer) Yii::app()->merchant->merchant_id;	
		
		$model = AR_table_reservation::model()->find("merchant_id=:merchant_id AND reservation_uuid=:reservation_uuid",array(		  
			':merchant_id'=>$merchant_id,
			':reservation_uuid'=>$id
		));		
		if($model){
			$model->is_update_frontend = false;
			$model->status = $status;
			$model->change_by = Yii::app()->merchant->first_name;
			$model->save();
			if(!empty($redirect) && strlen($redirect)>=2 ){
				$this->redirect($redirect);
			} else $this->redirect(array(Yii::app()->controller->id.'/list'));						
		} else $this->render("//tpl/error",array(
			'error'=>array( 'message'=>t(HELPER_RECORD_NOT_FOUND))
		   ));			
	}

	public function actionreservation_overview()
	{
		$this->pageTitle = t("Overview");
		CommonUtility::setMenuActive('.table_booking','.booking_list');		
			
		$id = trim(Yii::app()->input->get('id'));		
		$merchant_id = intval(Yii::app()->merchant->merchant_id);	

		try {			

		    $data = CBooking::getBookingDetails($id);
			$room_names = CommonUtility::getDataToDropDown("{{table_room}}","room_id","room_name","WHERE merchant_id=".q($merchant_id)." ");
			$table_names = CommonUtility::getDataToDropDown("{{table_tables}}","table_id","table_name","WHERE merchant_id=".q($merchant_id)." ");			
			
			/*
			LIST
			*/
			$action_name='reservation_list';
			$delete_link = Yii::app()->CreateUrl("booking/reservation_delete");
			
			ScriptUtility::registerScript(array(
			"var action_name='$action_name';",
			"var delete_link='$delete_link';",
			),$action_name);

			$table_col = array(            
				'reservation_id'=>array(
				  'label'=>t("ID"),
				  'width'=>'8%'
				),
				'client_id'=>array(
				  'label'=>t("Name"),
				  'width'=>'20%'
				),
				'guest_number'=>array(
					'label'=>t("Guest"),
					'width'=>'15%'
				),            
				'table_id'=>array(
				  'label'=>t("Table"),
				  'width'=>'15%'
				),
				'reservation_date'=>array(
				  'label'=>t("Date/Time"),
				  'width'=>'15%'
				),            			
				'special_request'=>array(
					'label'=>t("Request"),
					'width'=>'10%'
				),            
				'date_created '=>array(
					'label'=>t("Action"),
					'width'=>'10%'
				  ),
			  );
			$columns = array(            
				array('data'=>'reservation_id'),
				array('data'=>'client_id','visible'=>false),
				array('data'=>'guest_number'),            
				array('data'=>'table_id'),
				array('data'=>'reservation_date'),            			
				array('data'=>'special_request'),            
				array('data'=>'date_created','orderable'=>false),
			);		

			$status_list = AttributesTools::bookingStatus();
			try {				
				$summary = CBooking::customerSummary($data['client_id'],$data['merchant_id'],date("Y-m-d"));
			} catch (Exception $e) {
				$summary  = [];
			}

			$this->render('reservation_overview',[
				'links'=>array(
					t("Reservation list")=>array(Yii::app()->controller->id.'/list'),        
					$this->pageTitle,
				),	    		    
				'edit_link'=>Yii::app()->CreateUrl("/booking/update_reservation",['id'=>$id]),
				'data'=>$data,
				'status_list'=>$status_list,				
				'summary'=>$summary,
				'table_col'=>$table_col,
                'columns'=>$columns,
				'order_col'=>0,
                'sortby'=>'desc',
				'room_names'=>$room_names,
				'table_names'=>$table_names
			]);

		} catch (Exception $e) {
			$this->render("//tpl/error",array(
				'error'=>array( 'message'=>t(HELPER_RECORD_NOT_FOUND))
			));
		}			
	}

	public function actiongenerate_table()
	{
		
		$this->pageTitle = t("Generate Table");
		CommonUtility::setMenuActive('.table_booking','.booking_tables');		

		$merchant_id = (integer) Yii::app()->merchant->merchant_id;
		$model=new AR_table_generate;		

		if(isset($_POST['AR_table_generate'])){						
			$model->attributes=$_POST['AR_table_generate'];			
			
			if(DEMO_MODE && in_array($merchant_id,DEMO_MERCHANT)){
				$this->render('//tpl/error',array(  
					 'error'=>array(
					   'message'=>t("Modification not available in demo")
					 )
				   ));	
			   return false;
		    }			
			
			if($model->validate()){		
				$model->merchant_id = $merchant_id;				
				$params = [];
				for ($x = 1; $x <= $model->number_of_tables; $x++) {					
					$new = new AR_table_tables();
					$new->merchant_id = $model->merchant_id;
					$new->room_id = $model->room_id;
					$new->table_name = $x;
					$new->min_covers = $model->min_covers;
					$new->max_covers = $model->max_covers;
					$new->sequence = $x;
					$new->save();
				}				
				$this->redirect(array(Yii::app()->controller->id.'/tables'));		
			} else {												
				Yii::app()->user->setFlash('error',t(HELPER_CORRECT_FORM));			
			}
		}

		$this->render("table_generate",array(
		    'model'=>$model,			    		
			'error'=>$model->getErrors(),
		    'links'=>array(
	            t("Table list")=>array(Yii::app()->controller->id.'/tables'),        
                $this->pageTitle,
		    ),	    		    
		));
	}

	public function actionview_qrcode()
	{
		try {						
			$data = Yii::app()->input->get("data");				
			$model = CMerchants::get(Yii::app()->merchant->merchant_id);			
			$data = CommonUtility::getHomebaseUrl()."/restaurant/$model->restaurant_slug/?table=$data";			
			CommonUtility::viewQrcode($data);
		} catch (Exception $e) {		
			dump($e->getMessage());
		}			
	}

	public function actiontable_view()
	{
		$this->pageTitle = t("View");
		CommonUtility::setMenuActive('.table_booking','.booking_tables');		

		$merchant_id = (integer) Yii::app()->merchant->merchant_id;
		$table_uuid = Yii::app()->input->get("id");	

		$model = CBooking::getTableDetails($merchant_id,$table_uuid);
		if($model){			
			$qrcode = '';
			if(CommonUtility::getQrcodeFile($model['table_uuid'])){
				$qrcode = CMedia::getImage("$model[table_uuid].png",CMedia::qrcodeFolder());
			} else {

			}
			$this->render("table-view",[
				'qrcode'=>$qrcode,
				'model'=>$model,
				'links'=>array(
					t("Table List")=>array(Yii::app()->controller->id.'/tables'),        
					$this->pageTitle,
				),	    		    
			]);
		} else $this->render("//tpl/error",array(
			'error'=>array( 'message'=>t(HELPER_RECORD_NOT_FOUND))
		));			
	}

	public function actiontableside_config()
	{
		$this->pageTitle = t("Tableside QrCode Configuration");
		CommonUtility::setMenuActive('.table_booking','.booking_tables');		

		$merchant_id = (integer) Yii::app()->merchant->merchant_id;
		$table_uuid = Yii::app()->input->get("id");	
		// $model = AR_table_tables::model()->find("merchant_id=:merchant_id AND table_uuid=:table_uuid",array(		  
		// 	':merchant_id'=>$merchant_id,
		// 	':table_uuid'=>$table_uuid
		// ));		
		$model = CBooking::getTableDetails($merchant_id,$table_uuid);
		if($model){

			$merchant = CMerchants::get($merchant_id);			

			$user_token = [
				'iss'=>Yii::app()->request->getServerName(),
				'sub'=>$merchant->merchant_uuid,
				'iat'=>time(),
			];
			$user_token = JWT::encode($user_token, CRON_KEY, 'HS256');
				
			$payload = [		
				'user_token'=>$user_token,
				'merchant_id'=>$merchant_id,
				'table_uuid'=>isset($model['table_uuid'])?$model['table_uuid']:'',				
				'table_name'=>isset($model['table_name'])?$model['table_name']:'',				
				'room_uuid'=>isset($model['room_uuid'])?$model['room_uuid']:'',
				'room_name'=>isset($model['room_name'])?$model['room_name']:'',
			];					
						
			$qrcode = JWT::encode($payload, CRON_KEY, 'HS256');						
			
			
			$download_qrcode = Yii::app()->CreateUrl("/booking/view_qrcode",[
				'data'=>$qrcode
			]);	

			$this->render('tableside-config',[
				'model'=>$model,
				'qrcode'=>$qrcode,
				'download_qrcode'=>$download_qrcode,
				'links'=>array(
					t("Table List")=>array(Yii::app()->controller->id.'/tables'),        
					$this->pageTitle,
				),	    		    
			]);
		} else $this->render("//tpl/error",array(
			'error'=>array( 'message'=>t(HELPER_RECORD_NOT_FOUND))
		));			
	}

}
/*end class*/