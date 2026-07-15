<?php
class AR_table_reservation extends CActiveRecord
{	

	public $captcha_secret;
    public $capcha;
    public $recaptcha_response;		
	public $full_name, $table_name,$client_id_selected,$contact_phone,$email_address,$phone_prefix,$contact_phone_without_prefix,
	$first_name,$last_name,$avatar,$path;

	public $change_by,$table_id_selected,$is_update_frontend,$reservation_id_set;
	public $old_status,$restaurant_name, $merchant_id_selected;

	public $photo;

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
		return '{{table_reservation}}';
	}
	
	public function primaryKey()
	{
	    return 'reservation_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'client_id'=>t("Client ID"),
            'merchant_id'=>t("Merchant ID"),
            'reservation_date'=>t("Reservation date"),
            'reservation_time'=>t("Reservation time"),
            'guest_number'=>t("Guest number"),
            'special_request'=>t("Special requests"),            
		);
	}
	
	public function rules()
	{
		return array(
		  array('client_id,merchant_id,reservation_date,reservation_time,guest_number', 
		  'required','message'=> t( Helper_field_required ) ),

		  array('guest_number', 'numerical', 'integerOnly' => true,		  
		  'min'=>1,'max'=>100,
		  'tooSmall'=>t("Minimum value is 1"),
		  'message'=>t(Helper_field_numeric)),

		  array('client_id', 'numerical', 'integerOnly' => true,		  
		  'min'=>1,
		  'tooSmall'=>t("Customer name is required"),
		  'message'=>t("Customer name is required")),
		  
		  array('special_request', 'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')),	

		  array('table_id,room_id,status,date_created,date_modified,ip_address,cancellation_reason','safe'),
		  
		  array('recaptcha_response','validateCapcha'),	

		  array('special_request','validateAvailableTable'),	

		);
	}

	public function validateCapcha()
	{				
		if($this->capcha==1 || $this->capcha==TRUE){
			if(!empty($this->recaptcha_response)){
				try {						
															
					if(empty($this->captcha_secret)){
						$options = OptionsTools::find(array('captcha_secret'));
					    $captcha_secret = isset($options['captcha_secret'])?$options['captcha_secret']:'';													
					} else $captcha_secret = $this->captcha_secret;

					$resp = CRecaptcha::verify($captcha_secret,$this->recaptcha_response);					
				} catch (Exception $e) {
					$err = CRecaptcha::getError();
					if($err == "timeout-or-duplicate"){
						$this->addError('recaptcha_response',  t("Captcha expired please re-validate captcha") );
					} else $this->addError('recaptcha_response', $err );					
				}
			} else $this->addError('recaptcha_response', t("Please validate captcha") );
		}				
	}

	public function validateAvailableTable($attribute,$params)
	{
		$allowed_modification = ['pending','waitlist'];
		if($this->isNewRecord){			
			try {
				CBooking::getAvailableTable($this->guest_number , $this->reservation_date, $this->reservation_time, $this->merchant_id);			
			} catch (Exception $e) {						
				$this->addError('special_request', t($e->getMessage()) );
			}		
	    } else {
			if($this->is_update_frontend){				
				if(!in_array($this->status,$allowed_modification)){
					$this->addError('special_request', t("Updating to this booking is not allowed anymore") );					
				} else {					
					try {
						CBooking::getAvailableTable($this->guest_number , $this->reservation_date, $this->reservation_time, $this->merchant_id , $this->reservation_id_set);			
					} catch (Exception $e) {						
						$this->addError('special_request', t($e->getMessage()) );
					}		
				}
			}
		}
	}
	
    protected function beforeSave()
	{
		if(!parent::beforeSave()){
			return false;
		} 
		
		if($this->isNewRecord){			
			$this->date_created = CommonUtility::dateNow();											
		} else {
			$this->date_modified = CommonUtility::dateNow();			
		}
		$this->ip_address = CommonUtility::userIp();	

		if(empty($this->reservation_uuid)){
			$this->reservation_uuid = CommonUtility::createUUID("{{table_reservation}}",'reservation_uuid');
		}			
						
		if($this->isNewRecord){					
			if($this->table_id_selected>0){			
				$this->table_id = intval($this->table_id_selected);
			} else {				
				try {
					$table_id = CBooking::getAvailableTable($this->guest_number , $this->reservation_date, $this->reservation_time , $this->merchant_id);
					$this->table_id = intval($table_id);				
										
					$model_room = CBooking::getTableByID($table_id);
					$this->room_id = $model_room->room_id;

				} catch (Exception $e) {			
					//$this->addError('special_request', t($e->getMessage()) );
				}			
			}		
	    } else {
			//
		}
				
		return true;
	}
	
	protected function afterSave()
	{
		parent::afterSave();
		
		$model = new AR_table_reservation_history();
		$model->reservation_id = $this->reservation_id;
		$model->status = $this->status;
		$model->change_by = $this->change_by;
		if($this->status==$this->old_status){
			$model->remarks = "Reservation updated";
		}
		$model->save();

		Yii::import('ext.runactions.components.ERunActions');	
		$cron_key = CommonUtility::getCronKey();		
		$get_params = array( 
			'reservation_uuid'=> $this->reservation_uuid,
			'key'=>$cron_key,
			'language'=>Yii::app()->language
		);						

		if($this->isNewRecord){					
		    CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/taskbooking/afterbooking?".http_build_query($get_params) );							
		} else {			
			if(!$this->is_update_frontend){			   
			   CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/taskbooking/afterupdatebooking?".http_build_query($get_params) );							
			}			
			if($this->status=='finished'){
				CommonUtility::pushJobs("AfterUpdatebooking",[
					'reservation_id'=>$this->reservation_id
				]);
			}						
		}

		CCacheData::add();
	}

	protected function afterDelete()
	{
		parent::afterDelete();		
		CCacheData::add();
	}
		
}
/*end class*/
