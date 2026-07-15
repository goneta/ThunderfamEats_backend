<?php
class AR_merchant extends CActiveRecord
{	
	   
	public $tags;	
	public $cuisine2;
	public $service2;
	public $service3;
	public $tableside_services;
	public $featured;
	
	public $merchant_master_table_boooking;
	public $merchant_master_disabled_ordering;
	public $disabled_single_app_modules;
	
	public $payment_gateway ;
	public $image;
	public $image2;
	
	public $balance;
	public $trial_end;
	public $total;
	public $cuisine_group;

	public $open_status,$distance,$charge_type;	
	public $ratings,$saved_store;	

	public $invoice_terms2;
	public $csv;
	public $role_access;

	public $merchant_about_trans,$merchant_short_about_trans;
	public $commission_type,$commission_value;
	public $merchant_base_currency, $multicurrency_enabled;

	public $plan_id,$plan_name,$plan_amount,$plan_currency_code,$plan_next_due,$plan_status;
	public $subscription_features;

	public $saved_featured=true;

	public $close_reason,$next_opening_hours;
	
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
		return '{{merchant}}';
	}
	
	public function primaryKey()
	{
	    return 'merchant_id';	 
	}
	
	public function relations()
	{
		 return array(
		   'meta'=>array(self::BELONGS_TO, 'AR_merchant_meta', 'merchant_id'),
		   'option'=>array(self::BELONGS_TO, 'AR_option', 'merchant_id'),		   
		 );
	}
	
	/**
	 * Declares the validation rules.	 
	 */
	public function rules()
	{
		 return array(
            array('restaurant_slug,restaurant_name,contact_phone,contact_email,cuisine2,service2,
            delivery_distance_covered,distance_unit,contact_name,status', 
            'required','on'=>'information',
            'message'=> CommonUtility::t(Helper_field_required) ), 
            
            array('restaurant_slug,restaurant_name,contact_phone,contact_email,contact_name,restaurant_phone,
            ,description',
            'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')),         
            
            array('contact_email','email','message'=> t(Helper_field_email) ),              
            
            array('delivery_distance_covered','numerical','integerOnly'=>false ,'on'=>'information'),
            array('delivery_distance_covered','length','min'=>1, 'max'=>14 ,'on'=>'information' ),
            
            array('restaurant_slug,contact_email,contact_phone,merchant_uuid','unique','message'=>t(Helper_field_unique)),
            
            array('tags,restaurant_phone,is_ready,payment_gateway,featured,description,
            short_description,percent_commision,commision_based,path,commision_type,role_access,service3,tableside_services,
			charge_type,free_delivery_on_first_order,saved_featured','safe'),   
            
            array('short_description','length','max'=>1000),
                        
            
            array('merchant_type,package_id,allowed_offline_payment,invoice_terms,delivery_zone_type,delivery_zone_covered,
			invoice_terms2,commission_type,commission_value,orders_added,items_added,self_delivery,package_payment_code',
			'safe'),
            
            array('is_featured','safe','on'=>'featured'),              
            array('disabled_ordering,close_store','safe'),
                                 
            array('address,latitude,lontitude','required','on'=>'address'),              
			array('distance_unit','safe','on'=>'address'),        
            
            array('percent_commision,delivery_distance_covered', 'numerical', 'integerOnly' => false,		    		    
		    'message'=>t(Helper_field_numeric)),
		    
		    array('image,image2', 'file', 'types'=>Helper_imageType, 'safe' => false,
			  'maxSize'=>Helper_maxSize,
			  'tooLarge'=>t(Helper_file_tooLarge),
			  'wrongType'=>t(Helper_file_wrongType),
			  'allowEmpty' => true
			),      
          
			array('restaurant_name,address,contact_email,contact_phone,merchant_type', 
            'required','on'=>'website_registration',
            'message'=> CommonUtility::t(Helper_field_required) ), 

			array('service2','type','type'=>'array','allowEmpty'=>false , 'on'=>'website_registration',
			   'message'=>t("Service is required")
		    ),
                        
            array('merchant_type', 'match', 'not' => false, 'pattern' => '/[^a-zA-Z0]/', 
            'message' => t("Select membership program") , "on"=>"website_registration"),

			array('csv',
				'file', 'types' => 'csv',
				'maxSize'=>5242880,
				'allowEmpty' => false,
				'wrongType'=>t('Only csv allowed.'),
				'tooLarge'=>t('File too large! 5MB is the limit'),
				'on'=>'csv_upload'
		   ),		    
            
         );
	}
		
	/**
	 * Declares attribute labels.
	 */
	public function attributeLabels()
	{
		return array(
		    'restaurant_name'=>t("Restaurant name"),
		    'street'=>t("Street address"),
		    'image'=>t("Merchant Logo"),
		    'image2'=>t("Header Background"),
		    'restaurant_slug'=>t("Restaurant Slug"),
		    'contact_name'=>t("Contact Name"),
		    'contact_phone'=>t("Contact Phone"),
		    'contact_email'=>t("Contact email"),
		    'delivery_distance_covered'=>t("Delivery Distance Covered"),
		    'latitude'=>t("Latitude"),
		    'lontitude'=>t("Lontitude"),
		    'percent_commision'=>t("Commision"),
			'address'=>t("Address")
		);
	}
	
	protected function beforeSave()
	{
		if(parent::beforeSave()){
						
			if(DEMO_MODE && !$this->isNewRecord && in_array($this->merchant_id,DEMO_MERCHANT)){				
				return false;
			}
						
			if( $this->scenario=="website_registration" &&  $this->multicurrency_enabled==true &&  empty($this->merchant_base_currency)){				
				$this->addError('merchant_base_currency', t("Default Currency is required") );	
				return false;
			}
			
			if($this->isNewRecord){
				$this->date_created = CommonUtility::dateNow();						
			} else {
				$this->date_modified = CommonUtility::dateNow();											
			}
			$this->ip_address = CommonUtility::userIp();	
			
			if(empty($this->restaurant_slug) && !empty($this->restaurant_name) ){
			   $this->restaurant_slug = $this->generateSlug($this->restaurant_name);
			}
			
			if(empty($this->merchant_uuid)){
				$this->merchant_uuid = CommonUtility::createUUID("{{merchant}}",'merchant_uuid');
			}

			// if($this->merchant_type==1){
			// 	$this->invoice_terms = intval($this->invoice_terms2);
			// }
						
			return true;
		} else return true;
	}
	
	protected function beforeValidate()
	{
		parent::beforeValidate();
		if(!empty($this->restaurant_slug)){
		   $this->restaurant_slug = CommonUtility::toSeoURL($this->restaurant_slug);			
		}
		return true;
	}
	public function generateSlug($restaurant_name='')
	{
		$slug = CommonUtility::toSeoURL($restaurant_name);
		
		$stmt="SELECT * FROM {{merchant}}
		WHERE restaurant_slug=".q($slug)."
		";			
		if($res = Yii::app()->db->createCommand($stmt)->queryRow()){
			$restaurant_name.="-".CommonUtility::generateAplhaCode(2);
			return self::generateSlug($restaurant_name);
		}
		return $slug;
	}		
	
	protected function afterSave()
	{
		parent::afterSave();		
		
		if(!empty($this->subscription_features)){			
			MerchantTools::UpdateMerchantMeta($this->merchant_id,'subscription_features',$this->subscription_features);
			MerchantTools::setSubscriptionAccess($this->merchant_id,$this->subscription_features);
		}		

		if(is_array($this->payment_gateway) && count($this->payment_gateway)>=1){			
			$data = array();
			foreach ($this->payment_gateway as $val) {								
				if($val!="0"){
				   $data[]=$val;
				}
			}									
			MerchantTools::saveMerchantMeta($this->merchant_id,$data,'payment_gateway');
		}
		
		if(is_array($this->role_access) && count($this->role_access)>=1){
			$data_access = array();
			foreach ($this->role_access as $key => $val) {								
				if($val!="0"){
				   $data_access[]=$key;
				}
			}				
			MerchantTools::saveMerchantMeta($this->merchant_id,$data_access,'menu_access');
		}
						
		if(is_array($this->service2) && count($this->service2)>=1){
			MerchantTools::saveMerchantMeta($this->merchant_id,$this->service2,'services');	
		}		

		if(is_array($this->service3) && count($this->service3)>=1){
			MerchantTools::saveMerchantMeta($this->merchant_id,$this->service3,'services_pos');	
		} else {			
			if($this->scenario=="information"){
				Yii::app()->db->createCommand("DELETE FROM {{merchant_meta}} 
				WHERE merchant_id=".q($this->merchant_id)."  AND  meta_name='services_pos' 
				")->query();
			}			
		}		

		if(is_array($this->tableside_services) && count($this->tableside_services)>=1){
			MerchantTools::saveMerchantMeta($this->merchant_id,$this->tableside_services,'tableside_services');	
		} else {			
			if($this->scenario=="information"){
				Yii::app()->db->createCommand("DELETE FROM {{merchant_meta}} 
				WHERE merchant_id=".q($this->merchant_id)."  AND  meta_name='tableside_services' 
				")->query();
			}			
		}		
			
		if($this->saved_featured){
			if(is_array($this->featured) && count($this->featured)>=1){
				MerchantTools::saveMerchantMeta($this->merchant_id,$this->featured,'featured');	
			} else {
				if($this->scenario=="information"){
					MerchantTools::saveMerchantMeta($this->merchant_id,$this->featured,'featured');	
				}			
			}
		}		
				
		if(is_array($this->cuisine2) && count($this->cuisine2)>=1){
			MerchantTools::insertCuisine($this->merchant_id,$this->cuisine2);
		}		
		
		if(is_array($this->tags) && count($this->tags)>=1){
			MerchantTools::insertTag($this->merchant_id,$this->tags);	
		} else {
			Yii::app()->db->createCommand("DELETE FROM {{option}} WHERE merchant_id=".q($this->merchant_id)."  AND  option_name='tags' ")->query();
		}		

		if(is_array($this->merchant_about_trans) && count($this->merchant_about_trans)>=1){			
			$name = $this->merchant_about_trans;
			$name[KMRS_DEFAULT_LANGUAGE] = $this->description;
			$this->insertTranslation($name,'merchant_about_trans');
		}
		if(is_array($this->merchant_short_about_trans) && count($this->merchant_short_about_trans)>=1){			
			$name = $this->merchant_short_about_trans;
			$name[KMRS_DEFAULT_LANGUAGE] = $this->short_description;		
			$this->insertTranslation($name,'merchant_short_about_trans');
		}

		if(is_array($this->commission_type) && count($this->commission_type)>=1){
			$data = [];
			foreach ($this->commission_type as $transaction_type => $commission_type) {
				$commission = isset($this->commission_value[$transaction_type])?$this->commission_value[$transaction_type]:0;				
				$model_commission = AR_merchant_commission_order::model()->find("merchant_id=:merchant_id AND transaction_type=:transaction_type",[
					':merchant_id'=>$this->merchant_id,
					':transaction_type'=>$transaction_type
				]);
				if(!$model_commission){
					$model_commission = new AR_merchant_commission_order();
				}
				$model_commission->merchant_id = $this->merchant_id;
				$model_commission->transaction_type = $transaction_type;
				$model_commission->commission_type = $commission_type;
				$model_commission->commission = floatval($commission);
				$model_commission->save();
			}					
		}				


		if( $this->scenario=="website_registration" &&  $this->multicurrency_enabled==true && $this->isNewRecord &&  !empty($this->merchant_base_currency)){
			$model_options = new AR_option();
			$model_options->merchant_id = $this->merchant_id;
			$model_options->option_name = 'merchant_default_currency';
			$model_options->option_value = $this->merchant_base_currency;
			$model_options->save();			
		}

		if($this->isNewRecord){
			$model_options = new AR_option();
			$model_options->merchant_id = $this->merchant_id;
			$model_options->option_name = 'merchant_time_selection';
			$model_options->option_value = 1;
			$model_options->save();			
		}
		
			
		Yii::import('ext.runactions.components.ERunActions');	
		$cron_key = CommonUtility::getCronKey();		
		$get_params = array( 
		   'merchant_uuid'=> $this->merchant_uuid,
		   'key'=>$cron_key,
		   'language'=>Yii::app()->language
		);		
				
				
		switch ($this->scenario) {
			case "website_registration":		
				if($this->isNewRecord){
					$this->copyOpeningHours();
				}							
				//CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/aftermerchantsignup?".http_build_query($get_params) );			
				CommonUtility::pushJobs("MerchantAftersignup",[
					'merchant_id'=>$this->merchant_id,
					'language'=>Yii::app()->language
				]);
				CommonUtility::pushJobs("MerchantRegNew",[
					'merchant_id'=>$this->merchant_id,
					'language'=>Yii::app()->language
				]);
				break;		
				
			case "after_payment_validate":				
			    //CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/aftermerchantpayment?".http_build_query($get_params) );			
				break;		
				
			case "trial_will_end":	
			    $get_params['trial_end'] = $this->trial_end;
			    //CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/merchant_trial_end?".http_build_query($get_params) );							
			    break;		
			    
		    case "plan_past_due":				
			    //CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/after_plan_past_due?".http_build_query($get_params) );			
				break;			

		    case "information":	  
			case "csv_upload": 
		       try {
				    CWallet::getCardID( Yii::app()->params->account_type['merchant'] , $this->merchant_id );
				} catch (Exception $e) {
				    $wallet = new AR_wallet_cards;	
				    $wallet->account_type = Yii::app()->params->account_type['merchant'] ;
			        $wallet->account_id = intval($this->merchant_id);
			        $wallet->save();
				}	
			   
			   if($this->isNewRecord){
				  $this->copyOpeningHours();
			   }			   
		       break;			
			   
			case "approved":			
				//CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/aftermerchantapproved?".http_build_query($get_params) );			
				CommonUtility::pushJobs("MerchantRegistrationApproved",[
					'merchant_id'=>$this->merchant_id,
					'language'=>Yii::app()->language
				]);
				break;

			case "registration_denied":								
				CommonUtility::pushJobs("MerchantRegistrationDenied",[
					'merchant_id'=>$this->merchant_id,
					'language'=>Yii::app()->language
				]);
				break;
		}
				
		/*ADD CACHE REFERENCE*/
		CCacheData::add();
	}
	
	protected function beforeDelete()
	{				
		if(DEMO_MODE && in_array($this->merchant_id,DEMO_MERCHANT)){				
		    return false;
		}
		return true;
	}
	
	protected function afterDelete()
	{
	    parent::afterDelete();	    
	    MerchantTools::MerchantDeleteALl($this->merchant_id);
	    	    
		/*ADD CACHE REFERENCE*/
		CCacheData::add();
	}

	protected function copyOpeningHours()
	{				
		$options = OptionsTools::find(['enabled_copy_opening_hours','merchant_default_opening_hours_start','merchant_default_opening_hours_end']);
		$enabled = isset($options['enabled_copy_opening_hours'])?$options['enabled_copy_opening_hours']:false;		
		$start = isset($options['merchant_default_opening_hours_start'])?$options['merchant_default_opening_hours_start']:'';		
		$end = isset($options['merchant_default_opening_hours_end'])?$options['merchant_default_opening_hours_end']:'';		
		if($enabled==1 && !empty($start) && !empty($end)){
			$params = [];
			$start = Date_Formatter::TimeTo24($start);
			$end = Date_Formatter::TimeTo24($end);
			$days = AttributesTools::dayList();			
			foreach ($days as $day => $value) {
				$params[] =[
					'merchant_id'=>$this->merchant_id,
					'day'=>$day,
					'day_of_week'=>$this->getDayOfWeek( strtolower($day) ),
					'start_time'=>$start,
					'end_time'=>$end
				];
			}

			$builder=Yii::app()->db->schema->commandBuilder;
		    $command=$builder->createMultipleInsertCommand('{{opening_hours}}',$params);
		    $command->execute();			
		}		

		$this->copyPaymentGateway();
	}

	public function getDayOfWeek($day='')
	{
		$days = array();
		$days['monday'] =1;
		$days['tuesday'] =2;
		$days['wednesday'] =3;
		$days['thursday'] =4;
		$days['friday'] =5;
		$days['saturday'] =6;
		$days['sunday'] =7;
		return isset($days[$day])?$days[$day]:1;
	}

	public function copyPaymentGateway()
	{		
		$options = OptionsTools::find(['enabled_copy_payment_setting','copy_payment_list']);
		$enabled = isset($options['enabled_copy_payment_setting'])?$options['enabled_copy_payment_setting']:false;		
		$payment_list = isset($options['copy_payment_list'])?json_decode($options['copy_payment_list']):'';				
		if($enabled==1 && is_array($payment_list) && count($payment_list)>0 ){
			$params = [];
			foreach ($payment_list as $key => $items) {
				$params[] = [
					'merchant_id'=>$this->merchant_id,
					'meta_name'=>'payment_gateway',
					'meta_value'=>$items,
					'date_modified'=>CommonUtility::dateNow()
				];
			}			
			$builder=Yii::app()->db->schema->commandBuilder;
		    $command=$builder->createMultipleInsertCommand('{{merchant_meta}}',$params);
		    $command->execute();			
		}		
	}

	public function insertTranslation($data=array(),$meta_name='')
	{				
		$stmt = "DELETE FROM {{merchant_meta}} WHERE merchant_id=".q($this->merchant_id)." 
		AND meta_name=".q($meta_name)."
		";		
		Yii::app()->db->createCommand($stmt)->query();
		$params = [];
		if(is_array($data) && count($data)>=1){
			foreach ($data as $lang => $value) {			
				$params[] =[
					'merchant_id'=>$this->merchant_id,
					'meta_name'=>$meta_name,
					'meta_value'=>$lang,
					'meta_value1'=>$value
				];
			}			
			$builder=Yii::app()->db->schema->commandBuilder;
		    $command=$builder->createMultipleInsertCommand("{{merchant_meta}}",$params);
		    $command->execute();
		}		
	}

	public static function getTranslation($merchant_id=0, $meta_name='')
	{
		$stmt = "SELECT * FROM {{merchant_meta}} 
		WHERE merchant_id=".q($merchant_id)."
		AND meta_name=".q($meta_name)."
		";		
		$dependency = CCacheData::dependency();					
        if($res = Yii::app()->db->cache(Yii::app()->params->cache, $dependency)->createCommand($stmt)->queryAll()){
			$data = [];
			foreach ($res as $items) {
				$data[$items['meta_value']] = $items['meta_value1'];
			}
			return $data;			
		} 
		return false;
	}
		
}
/*end class*/
