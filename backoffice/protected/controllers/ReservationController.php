<?php
class ReservationController extends CommonController
{
		
	public function beforeAction($action)
	{				
		
		InlineCSTools::registerStatusCSS();
		InlineCSTools::registerOrder_StatusCSS();
			
		return true;
	}		

    public function actionsettings()
    {
        $this->pageTitle = t("Table reservation settings");		

		$model=new AR_option;	
		
		$options = [
			'booking_tpl_reservation_requested','booking_tpl_reservation_confirmed','booking_tpl_reservation_canceled',
            'booking_tpl_reservation_denied','booking_tpl_reservation_finished','booking_tpl_reservation_no_show','booking_time_format'
		];
		
		if(isset($_POST['AR_option'])){			

			if(DEMO_MODE){			
			    $this->render('//tpl/error',array(  
			          'error'=>array(
			            'message'=>t("Modification not available in demo")
			          )
			        ));	
			    return false;
			}
			
			$model->attributes=$_POST['AR_option'];				
			if($model->validate()){							
				if(OptionsTools::save($options, $model)){
					Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
					$this->refresh();
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			} 
		}

		if($data = OptionsTools::find($options)){
			foreach ($data as $name=>$val) {
				$model[$name]=$val;
			}					
		}

		$template_list =  CommonUtility::getDataToDropDown("{{templates}}",'template_id','template_name',
		"","ORDER BY template_name ASC");

		$this->render("//booking/reservation_settings",[
			'model'=>$model,			
			'links'=>array(	
				t("Booking Settings")
			),				   
			'template_list'=>$template_list
		]);
    }

	public function actionlist()
	{
		$this->pageTitle = t("Reservation list");

		$action_name='reservation_list';
		$delete_link = Yii::app()->CreateUrl(Yii::app()->controller->id."/reservation_delete");
		
		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
		),$action_name);

		$table_col = array(            
            'reservation_id'=>array(
              'label'=>t("ID"),
              'width'=>'8%'
            ),
			'merchant_id'=>array(
				'label'=>t("Merchant"),
				'width'=>'15%'
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
			array('data'=>'merchant_id'),
            array('data'=>'client_id'),
			array('data'=>'guest_number'),            
			array('data'=>'table_id'),
            array('data'=>'reservation_date'),            			
			array('data'=>'special_request'),            
            array('data'=>'date_created','orderable'=>false),
        );		

		try {				
			$summary = CBooking::reservationSummary(0,date("Y-m-d"));
		} catch (Exception $e) {
			$summary  = [];
		}

		$this->render("//booking/booking_list",[
			'ajax_url'=>Yii::app()->createUrl("/api"),
			'actions'=>"reservationList",
			'table_col'=>$table_col,
            'columns'=>$columns,
            'order_col'=>0,
            'sortby'=>'desc',
			'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/create_reservation"),	
			'summary'=>$summary
		]);				
	}

	public function actionreservation_delete()
	{
		$id = trim(Yii::app()->input->get('id'));					
		
		$model = AR_table_reservation::model()->find("reservation_uuid=:reservation_uuid",array(		  		  
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
		$id = trim(Yii::app()->input->get('id'));			
		$status = trim(Yii::app()->input->get('status'));
		$redirect = CommonUtility::safeTrim(Yii::app()->input->get('redirect'));		
		
		$model = AR_table_reservation::model()->find("reservation_uuid=:reservation_uuid",array(		  			
			':reservation_uuid'=>$id
		));		
		if($model){
			$model->is_update_frontend = false;
			$model->status = $status;
			$model->change_by = Yii::app()->user->first_name;
			$model->save();
			if(!empty($redirect) && strlen($redirect)>=2 ){
				$this->redirect($redirect);
			} else $this->redirect(array(Yii::app()->controller->id.'/list'));						
		} else $this->render("//tpl/error",array(
			'error'=>array( 'message'=>t(HELPER_RECORD_NOT_FOUND))
		   ));			
	}

	public function actionupdate_reservation()
	{
		$this->actioncreate_reservation(true);
	}

	public function actioncreate_reservation($update=false)
	{
		$this->pageTitle = $update==false? t("Add Booking") : t("Update Booking");
		CommonUtility::setMenuActive('.table_reservation','.reservation_list');		

		$create_reservation_link = Yii::app()->createAbsoluteUrl(Yii::app()->controller->id."/create_reservation/?");
		$action_name = "create_reservation";
		
		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var create_reservation_link='$create_reservation_link';",
		),$action_name);
				
		$merchant_id = Yii::app()->input->get('merchant_id');		
		

		if($update){
			$id = trim(Yii::app()->input->get('id'));			
			
			$model = AR_table_reservation::model()->find('reservation_uuid=:reservation_uuid', 
		    array( ':reservation_uuid'=>$id ));			
											
			if(!$model){				
				$this->render("//tpl/error",array(
				 'error'=>array( 'message'=>t("".HELPER_RECORD_NOT_FOUND))
				));			
				Yii::app()->end();
			}		
			
			$model->client_id_selected = CommonUtility::getDataToDropDown("{{client}}",'client_id','first_name',
			"WHERE client_id=".q($model->client_id)."");
			
			$model->old_status = $model->status;
			$merchant_id = $model->merchant_id;
											
		} else $model=new AR_table_reservation;
		
		if($merchant_id>0){
			$model->merchant_id_selected = CommonUtility::getDataToDropDown("{{merchant}}",'merchant_id','restaurant_name',
			"WHERE merchant_id=".q($merchant_id)."");			
		}

		if(isset($_POST['AR_table_reservation'])){
			$model->attributes=$_POST['AR_table_reservation'];			
			if($model->validate()){				
				$model->reservation_time = !empty($model->reservation_time)? Date_Formatter::TimeTo24($model->reservation_time):'';								
				$model->merchant_id = intval($merchant_id);
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

		$this->render("//booking/reservation_create_admin",array(
		    'model'=>$model,			
			'table_list'=>$table_list,
			'status_list'=>$status_list,
		    'links'=>array(
	            t("Reservation List")=>array(Yii::app()->controller->id.'/list'),        
                $this->pageTitle,
		    ),	    		    
		));
	}

	public function actionreservation_overview()
	{
		$this->pageTitle = t("Overview");
		CommonUtility::setMenuActive('.table_reservation','.reservation_list');	
			
		$id = trim(Yii::app()->input->get('id'));		
		$merchant_id = 0;

		try {			

		    $data = CBooking::getBookingDetails($id);			
			$merchant_id = $data['merchant_id'];			
			
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
				$summary = CBooking::customerSummary($data['client_id'],0,date("Y-m-d"));
			} catch (Exception $e) {
				$summary  = [];
			}

			$this->render('//booking/reservation_overview_admin',[
				'links'=>array(
					t("Reservation list")=>array(Yii::app()->controller->id.'/list'),        
					$this->pageTitle,
				),	    		    
				'edit_link'=>Yii::app()->CreateUrl("/reservation/update_reservation",['id'=>$id]),
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

}
// end class