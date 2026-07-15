<?php
class ApilocationsController extends CController {

    public $code=2,$msg,$details,$data;

    public function __construct($id,$module=null){
		parent::__construct($id,$module);				
		// Set the application language if provided by GET, session or cookie
		if(isset($_GET['language'])) {
			Yii::app()->language = $_GET['language'];
			Yii::app()->user->setState('language', $_GET['language']); 
			$cookie = new CHttpCookie('language', $_GET['language']);
			$cookie->expire = time() + (60*60*24*365); // (1 year)
			Yii::app()->request->cookies['language'] = $cookie; 
		} else if (Yii::app()->user->hasState('language')){
			Yii::app()->language = Yii::app()->user->getState('language');			
		} else if(isset(Yii::app()->request->cookies['language'])){
			Yii::app()->language = Yii::app()->request->cookies['language']->value;			
			if(!empty(Yii::app()->language) && strlen(Yii::app()->language)>=10){
				Yii::app()->language = KMRS_DEFAULT_LANGUAGE;
			}
		} else {
			$options = OptionsTools::find(['default_language']);
			$default_language = isset($options['default_language'])?$options['default_language']:'';			
			if(!empty($default_language)){
				Yii::app()->language = $default_language;
			} else Yii::app()->language = KMRS_DEFAULT_LANGUAGE;
		}	
	}

	public function init()
	{
		Yii::app()->params['settings'] = OptionsTools::find([
			'website_timezone_new','home_search_mode','location_searchtype'
		]);
		$setttings = Yii::app()->params['settings'];	

		/*SET TIMEZONE*/
		$timezone = Yii::app()->params['settings']['website_timezone_new'];		
		if (is_string($timezone) && strlen($timezone) > 0){
		   Yii::app()->timeZone=$timezone;		   
		}
		Price_Formatter::init();	
	}

    public function responseJson()
    {
		header("Access-Control-Allow-Origin: *");          
        header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    	header('Content-type: application/json'); 
		$resp=array('code'=>$this->code,'msg'=>$this->msg,'details'=>$this->details);
		echo CJSON::encode($resp);
		Yii::app()->end();
    } 

	public function filters()
    {
        return array(
            'accessControl',
        );
    }

	public function accessRules()
	{						
		return array(			
             array('deny',				
                  'actions'=>array(
                    'fetchlocationaddress','handleAddressRequirement',"deleteAddress"
                 ), 
				 'expression' => array('AppIdentity','verifyCustomer')
			 ), 
		 );
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
									
            $country_id = Clocations::getDefaultCountry();
            $state_list = Clocations::locationStateList($country_id);            

			$state_id = Yii::app()->input->post('state_id');
			$data = Clocations::fetchCity($state_id,'',$state_list);
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

	public function actionfetchzip()
	{
		try {
			$area_id = Yii::app()->input->get('area_id');	
			$data = Clocations::fetchZip($area_id);
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

    public function actionfetchpostal()
	{
		try {
									
            $country_id = Clocations::getDefaultCountry();
            $state_list = Clocations::locationStateList($country_id);            			
			$data = Clocations::fetchPostal($state_list);
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

	public function actionfetchlocationaddress()
	{
		try {
			
			$data = Clocations::getAddress(Yii::app()->user->id);			
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'data'=>$data,
				'count'=>count($data)
			];
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actionsaveaddress()
	{
		try {
			
			$address_uuid = Yii::app()->input->post('address_uuid');
			$formatted_address = Yii::app()->input->post('formatted_address');
			$address1 = Yii::app()->input->post('address1');
			$location_name = Yii::app()->input->post('location_name');
			$delivery_options = Yii::app()->input->post('delivery_options');
			$delivery_instructions = Yii::app()->input->post('delivery_instructions');
			$address_label = Yii::app()->input->post('address_label');
			$house_number = Yii::app()->input->post('house_number');

			$state_id = intval(Yii::app()->input->post('state_id'));
			$city_id = intval(Yii::app()->input->post('city_id'));
			$area_id = intval(Yii::app()->input->post('area_id'));
			$zip_code = trim(Yii::app()->input->post('zip_code'));

			$latitude = trim(Yii::app()->input->post('latitude'));
			$longitude = trim(Yii::app()->input->post('longitude'));
			
			$country_id = Clocations::getDefaultCountry();		

			$model = new AR_client_address_location();
			if(!empty($address_uuid)){				
				$model = AR_client_address_location::model()->find("address_uuid=:address_uuid",[
					':address_uuid'=>$address_uuid
				]);
				$model->address_uuid = $address_uuid;
			}
					
			$model->address_type = 'location-based';
			$model->client_id = Yii::app()->user->id;
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
				} else {				
					$this->msg = CommonUtility::parseModelErrorToString($model->getErrors());
				}
			} else $this->msg = CommonUtility::parseModelErrorToString($model->getErrors());						
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actiondeleteaddress()
	{
		try {			
			
			$address_uuid = Yii::app()->input->post('address_uuid');
			$client_id = Yii::app()->user->id;			
			$model = AR_client_address_location::model()->find("client_id=:client_id AND address_uuid=:address_uuid",[
				':client_id'=>$client_id,
				':address_uuid'=>$address_uuid
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

	public function actiongetaddress()
	{
		try {
			
			$address_uuid = Yii::app()->input->post('address_uuid');
			$model = AR_client_address_location::model()->find("address_uuid=:address_uuid",[
				':address_uuid'=>$address_uuid
			]);
			if($model){				
				$data = [
					'address_uuid'=>$model->address_uuid,
					'formatted_address'=>$model->formatted_address,
					'address1'=>$model->address1,
					'location_name'=>$model->location_name,
					'postal_code'=>$model->postal_code,
					'address2'=>$model->address2,
					'custom_field1'=>$model->custom_field1,
					'house_number'=>$model->custom_field2,
					'country_code'=>$model->country_code,
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

	public function actiongetlocationfilters()
	{
		try {

			$currency_code = Yii::app()->input->get("currency_code");
		    $base_currency = Price_Formatter::$number_format['currency_code'];		

			$multicurrency_enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
			$multicurrency_enabled = $multicurrency_enabled==1?true:false;		

			$currency_code = !empty($currency_code)?$currency_code:$base_currency;			
			if(!empty($currency_code) && $multicurrency_enabled){
				Price_Formatter::init($currency_code);
			}		

			try {
				$cuisine = CCuisine::getList( Yii::app()->language );
			} catch (Exception $e) {
				$cuisine = null;
			}

			$transaction_type = 'delivery';
			$services_list = null;
			try {
			   $services_list = CServices::Listing(Yii::app()->language );			   
			   if(array_key_exists($transaction_type,(array)$services_list)){
				  $keys = array_keys($services_list);				  
				  $transaction_type = isset($keys[0])?$keys[0]:$transaction_type;				  
			   }
		    } catch (Exception $e) {}

			$data = array(		  
			  'price_range'=>AttributesTools::SortPrinceRange(),
			  'quick_filters'=>AttributesTools::QuickFilters(),
			  'offers_filters'=>AttributesTools::OffersFilters(),
			  'transaction_type'=>$transaction_type,
			  'services_list'=>$services_list,
			  'cuisine'=>$cuisine,			  
			);			

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = $data;

		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actiongetBanner()
	{
		try {
			$data = CMerchants::getBanner(0,'admin',true);				
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

	public function actionrestaurantfeatured()
	{
		try {
		   			
		   $query = Yii::app()->input->get('query');
		   $transaction_type = Yii::app()->input->get('transaction_type');
		   $city_id = Yii::app()->input->get('city_id');
		   $area_id = Yii::app()->input->get('area_id');

		   $setttings = Yii::app()->params['settings'];
		   $search_type = isset($setttings['location_searchtype'])?$setttings['location_searchtype']:'';		   

		   if($transaction_type=="undefined"){
			  $transaction_type = '';
		   }
		   if(empty($transaction_type)){
			   $transaction_type = CServices::getFirstService();
		   }		   		   
		   
		   $data = Clocations::getFeatured([
				'search_type'=>$search_type,
				'transaction_type'=>$transaction_type,
				'city_id'=>$city_id,
				'area_id'=>$area_id,
		   ],Yii::app()->language);

		   $vouchers = [];
		   try {
		     $vouchers = Clocations::getVoucherList($data['merchants'],date("Y-m-d"));		   
		   } catch (Exception $e) {	}

		   $promos = [];
		   try {
		     $promos = Clocations::getPromoList($data['merchants'],date("Y-m-d"));		   
		   } catch (Exception $e) {	}
		   
		   $this->code = 1;
		   $this->msg = "Ok";
		   $this->details = [
			 'data'=>$data['data'],		
			 'available_vouchers'=>$vouchers,
			 'available_promos'=>$promos,
		   ];
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actioncuisineList()
	{
		try {
			$cuisine = CCuisine::getList( Yii::app()->language );
			$this->code = 1;
			$this->details = $cuisine;
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());		   
		}
		$this->responseJson();	
	}

	public function actiongetfeedv1()
	{
		try {
						   
		   $filters = Yii::app()->input->get('filters');		   
		   $featured = Yii::app()->input->get('featured');
		   $transaction_type = Yii::app()->input->get('transaction_type');
		   $city_id = intval(Yii::app()->input->get('city_id'));
		   $area_id = intval(Yii::app()->input->get('area_id'));
		   $state_id = intval(Yii::app()->input->get('state_id'));
		   $postal_id = intval(Yii::app()->input->get('postal_id'));

		   $query = Yii::app()->input->get('query');
		   $page = Yii::app()->request->getParam('page', 1);
		   $limit = Yii::app()->request->getParam('limit', 10);
		   $offset = ($page - 1) * $limit;		   

		   $setttings = Yii::app()->params['settings'];
		   $search_type = isset($setttings['location_searchtype'])?$setttings['location_searchtype']:'';		   

		   if($transaction_type=="undefined" || $transaction_type=="null"){
			  $transaction_type = '';
		   }		   
		   if($query=="undefined" || $query=="null"){
			  $query = '';
		   }		   
		   if(empty($transaction_type)){
			   $transaction_type = CServices::getFirstService();
		   }		   		   

		   $filters = !empty($filters)?json_decode($filters,true):false;		   
		   $featured = !empty($featured)?json_decode($featured,true):false;		   
		   
		   $todays_date = date("Y-m-d H:i");
		   $day_of_week = strtolower(date("N",strtotime($todays_date)));

		   $data = Clocations::getFeatured([
				'search_type'=>$search_type,
				'limit'=>$limit,
				'offset'=>$offset,
				'query'=>$query,
				'transaction_type'=>$transaction_type,
				'featured'=>$featured,
				'city_id'=>$city_id,
				'area_id'=>$area_id,
				'state_id'=>$state_id,
				'postal_id'=>$postal_id,
				'cuisine'=>isset($filters['cuisine'])?$filters['cuisine']:'',
				'price_range'=>isset($filters['price_range'])?$filters['price_range']:'',
				'quick_filters'=>isset($filters['quick_filters'])?$filters['quick_filters']:'',
				'offers_filters'=>isset($filters['offers_filters'])?$filters['offers_filters']:'',
				'today_now'=>strtolower(date("l",strtotime($todays_date))),
				'time_now'=>date("H:i",strtotime($todays_date)),
				'day_of_week'=>$day_of_week>6?1:$day_of_week,
				'date_now'=>$todays_date				
		   ],Yii::app()->language);

		   $vouchers = [];
		   try {
		     $vouchers = Clocations::getVoucherList($data['merchants'],date("Y-m-d"));		   
		   } catch (Exception $e) {	}

		   $promos = [];
		   try {
		     $promos = Clocations::getPromoList($data['merchants'],date("Y-m-d"));		   
		   } catch (Exception $e) {	}

		   $total = intval($data['total']);
		   $merchant_data = $data['data'];
		   		   
		   foreach ($merchant_data as &$restaurant) {
				$merchant_id = $restaurant['merchant_id'];				
				$restaurant['vouchers'] = isset($vouchers[$merchant_id])?$vouchers[$merchant_id]:[];
				$restaurant['promos'] = isset($promos[$merchant_id]) ? $promos[$merchant_id] : [];
		   }
		   
		   $this->code = 1;
		   $this->msg = "Ok";
		   $this->details = [
			 'total_pretty'=>t("{total} Restaurants found",[
				'{total}'=>$total
			 ]),
			 'total'=>$total,
			 'page' => intval($page),
			 'limit' => intval($limit),
			 'data'=>$merchant_data			 
		   ];

		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());					   
		}
		$this->responseJson();	
	}

	public function actionsearchsuggestion()
	{		
		$q = Yii::app()->input->get('q');
		$select = [
			'type'=>"items",
			'name'=>$q,
		];
		try {									
			$data = Clocations::searchSuggestion($q);			
			array_unshift($data,$select);			
			$this->code = 1; $this->msg = "Ok";
			$this->details = $data;
		} catch (Exception $e) {
			$this->code = 1; $this->msg = "Ok";						
			$this->details[] = $select;
		}	
		$this->responseJson();
	}

	public function actiongetlocationcurrentaddress()
	{
		try {
						
			$setttings = Yii::app()->params['settings'];        
		    $search_type = isset($setttings['location_searchtype'])?$setttings['location_searchtype']:'';			

			$cart_uuid = Yii::app()->input->get('cart_uuid');
			$city_id = intval(Yii::app()->input->get('city_id'));
			$area_id = intval(Yii::app()->input->get('area_id'));						
			$state_id = intval(Yii::app()->input->get('state_id'));
			$postal_id = intval(Yii::app()->input->get('postal_id'));

			$transaction_type = Yii::app()->input->get('transaction_type');
			$merchant_id = intval(Yii::app()->input->get('merchant_id'));
			
			$data = []; 
			$address_needed = false;
			$transaction_type_valid = true;
			try {
			   $data = Clocations::getLocations($search_type,$city_id,$area_id,$state_id,$postal_id);
			   $this->code = 1;
		    } catch (Exception $e) {				
			}			
			
			if(empty($transaction_type) && $merchant_id>0){				
				$transaction_type = CMerchants::getService($merchant_id);
			} elseif ($merchant_id>0) {
				if(!CMerchants::validateService($merchant_id,$transaction_type)){
					$transaction_type_valid = false;
					$transaction_type = CMerchants::getService($merchant_id);
				}				
			}
						
			if($transaction_type=="delivery"){		
				if(is_array($data) && count($data)>=1){
					//
				} else {
					$address_needed = true;
				}
			}
						
			$this->msg = "Ok";
			$this->details = [
				'transaction_type'=>$transaction_type,
				'address_needed'=>$address_needed,		
				'transaction_type_valid'=>$transaction_type_valid,
				'data'=>$data
			];						
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());					   
		}
		$this->responseJson();	
	}

	public function actionchecklocations()
	{
		try {

			$transaction_type = Yii::app()->input->get('transaction_type');
			$search_type = Yii::app()->input->get('search_type');
			$merchant_id = intval(Yii::app()->input->get('merchant_id'));
			$city_id = intval(Yii::app()->input->get('city_id'));
			$area_id = intval(Yii::app()->input->get('area_id'));
			$state_id = intval(Yii::app()->input->get('state_id'));
			$postal_id = intval(Yii::app()->input->get('postal_id'));
						
			if($transaction_type=="delivery"){
				Clocations::checkLocations($merchant_id,[
					'search_type'=>$search_type,
					'merchant_id'=>$merchant_id,
					'city_id'=>$city_id,
					'area_id'=>$area_id,
					'state_id'=>$state_id,
					'postal_id'=>$postal_id,
				]);
				$this->code = 1;
				$this->msg = "Ok";
			} else {
				$this->code = 1;
				$this->msg = "Ok";
			}		

		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());					   
		}
		$this->responseJson();	
	}

	public function actiongetlocationestimation()
	{
		try {
			
			$search_type = Yii::app()->input->get('search_type');
			$merchant_id = Yii::app()->input->get('merchant_id');
			$city_id = Yii::app()->input->get('city_id');
			$area_id = Yii::app()->input->get('area_id');
			$state_id = Yii::app()->input->get('state_id');
			$postal_id = trim(Yii::app()->input->get('postal_id'));

			$cart_uuid = Yii::app()->input->get('cart_uuid');
			
			$list = CCheckout::getMerchantTransactionList($merchant_id,Yii::app()->language);			
			$transaction_type = CCart::cartTransaction($cart_uuid,Yii::app()->params->local_transtype,$merchant_id);

			try {
				$data = Clocations::LocationEstimation($merchant_id,[
					'search_type'=>$search_type,				
					'city_id'=>intval($city_id),
					'area_id'=>intval($area_id),
					'state_id'=>intval($state_id),
					'postal_id'=>$postal_id
				]);
		    } catch (Exception $e) {}

			foreach ($list as &$items) {
				$items['estimation'] = isset($data[$items['service_code']]) ? $data[$items['service_code']] : '';
			}			
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'transaction_type'=>$transaction_type,
				'data'=>$list
			];
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());					   
		}
		$this->responseJson();	
	}

	public function actionhandleaddressrequirement()
	{
		try {
						
			$setttings = Yii::app()->params['settings'];			
			$search_type = isset($setttings['location_searchtype'])?$setttings['location_searchtype']:'';								

			$merchant_id = intval(Yii::app()->input->get('merchant_id'));
			$cart_uuid = trim(Yii::app()->input->get('cart_uuid'));

			$city_id = intval(Yii::app()->input->get('city_id'));			
			$area_id = intval(Yii::app()->input->get('area_id'));			
			$state_id = intval(Yii::app()->input->get('state_id'));	

			$address_needed = false; $data = []; $default_address = [];
			try {
				$transaction = CCart::cartTransaction($cart_uuid,Yii::app()->params->local_transtype,$merchant_id);			
				$address_needed = $transaction=="delivery"?true:false;
 		    } catch (Exception $e) {}

			if($address_needed){
				try {
					$data = Clocations::getAddress(Yii::app()->user->id);
				 } catch (Exception $e) {}
	 
				 try {
					 $default_address = Clocations::defaultAddress(Yii::app()->user->id,[
						 'search_type'=>$search_type,
						 'city_id'=>$city_id,
						 'area_id'=>$area_id,
						 'state_id'=>$state_id
					 ]);				
				 } catch (Exception $e) {}
			}			
			
			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'address_needed'=>$address_needed,
				'default_address'=>$default_address,
				'data'=>$data
			];
		} catch (Exception $e) {		   
			$this->msg = t($e->getMessage());					   
		}
		$this->responseJson();	
	}

}
// end class