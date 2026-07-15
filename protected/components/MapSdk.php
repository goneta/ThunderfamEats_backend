<?php 
class MapSdk
{
	public static $api_key;
	public static $map_provider;
	public static $map_parameters;
	public static $http_code;
	public static $mapbox_coords=null;
	
	public static $map_api = array(
	  'google'=>array(
	    'place'=>'https://maps.googleapis.com/maps/api/place/autocomplete/json',
	    'place_detail'=>'https://maps.googleapis.com/maps/api/place/details/json',
	    'reverse_geocoding'=>'https://maps.googleapis.com/maps/api/geocode/json',
	    'distance'=>"https://maps.googleapis.com/maps/api/distancematrix/json"
	  ),
	  'mapbox'=>array(
	    'place'=>'https://api.mapbox.com/geocoding/v5/mapbox.places',
	    'place_detail'=>'https://api.mapbox.com/geocoding/v5/mapbox.places',
	    'reverse_geocoding'=>'https://api.mapbox.com/geocoding/v5/mapbox.places',
		'distance'=>"https://api.mapbox.com/directions-matrix/v1/mapbox"
	  ),
	  'yandex'=>array(
		'place'=>"https://suggest-maps.yandex.ru/v1/suggest",
		'place_detail'=>"https://geocode-maps.yandex.ru/1.x",
		'reverse_geocoding'=>"https://geocode-maps.yandex.ru/1.x",
		'distance'=>"https://api.routing.yandex.net/v2/distancematrix"
	  ),
	);
	
	public static function setKeys($keys=array())
	{		
		if(array_key_exists(self::$map_provider,(array)$keys)){
			self::$api_key=$keys[self::$map_provider];
		} 
		//} else  throw new Exception('Invalid google api keys');
	}
	
	public static function setMapParameters($parameters=array())
	{
		self::$map_parameters = $parameters;
	}
	
	private static function getMapParameters($separator='')
	{
		$components='';
		if(is_array(self::$map_parameters) && count(self::$map_parameters)>=1){
			foreach (self::$map_parameters as $key=>$val) {
				if($separator=="="){
					$components.= "&".$key.$separator.$val;
				} else $components.= $key.$separator.$val;				
			}			
		}
		return $components;
	}
	
	public static function getData($api_url='')
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $api_url );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        $result = curl_exec($ch);        
        if (curl_errno($ch)) {		    
		    throw new Exception( curl_error($ch) );
		}
		self::$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);		
		curl_close($ch);
		
		return $result;
	}
		
	/**
	 * find place api
	 *
	 * @param $query  = address or location name
	 * @return array
	 * //'types'=>'geocode', //possible values geocode,address,establishment
	 */
	public static function findPlace($query='')
	{		
				
		$resp = array(); $data=array(); $components = '';		
				
		switch (self::$map_provider) {
			case "google.maps":			
			    $components = self::getMapParameters(":");
			    			    
			    $api_url = self::$map_api['google']['place']."?".http_build_query(array(
				  'key'=>self::$api_key,
				  'input'=>CommonUtility::safeTrim($query),			  
				  'components'=>$components 
				));
								
				$result = self::getData($api_url);						
				if (is_string($result) && strlen($result) > 0){
					if ($resp = json_decode($result,true)){				
						$data = MapSdk::parseGoogleResponse($resp,'place');
					}
				}				
			    
				break;
		
			case "mapbox":	
			    $api_url = self::$map_api['mapbox']['place']."/".urlencode($query).".json?".http_build_query(array(			  
				  'access_token'=>self::$api_key			  
				));
				$api_url.= self::getMapParameters("=");					
				
				$result = self::getData($api_url);												
				if (is_string($result) && strlen($result) > 0){
					if ($resp = json_decode($result,true)){				
						$data = MapSdk::parseMapboxResponse($resp,'place');
					}
				}				
			    break;

			case "yandex":
				$params = [
					'apikey'=>self::$api_key,
					'text'=>$query,
					'print_address'=>1
				];
				$api_url = "https://suggest-maps.yandex.ru/v1/suggest?".http_build_query($params);				
				$api_url.= self::getMapParameters("=");					
				$result = self::getData($api_url);		
				if(self::$http_code==200){
					if (is_string($result) && strlen($result) > 0){
						if ($resp = json_decode($result,true)){				
							$data = MapSdk::parseYandexResponse($resp,'place');
						}
					}				
				} else if ( self::$http_code==400 )	{
					throw new Exception ( 'The request is missing a required parameter or an invalid parameter value is specified. The message contains additional information about the error.' ); 
				} else if ( self::$http_code==403 )	{
					throw new Exception ( "The request doesn't contain the apikey parameter or an invalid key was specified." ); 
				} else if ( self::$http_code==429 )	{
					throw new Exception ( "There are too many requests in a short time." ); 
				}
				break;
			    
			default:
				throw new Exception ( 'undefined map provider' ); 
				break;
		}
				
		return $data;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $place_id
	 * @return unknown
	 */
	public static function placeDetails($place_id='',$place_description='')
	{
		if(empty($place_id)){
			throw new Exception("invalid place id parameters");
		}
		
		$resp = array(); $data=array();		
		
		switch (self::$map_provider) {
			case "google.maps":
												
				$api_url = self::$map_api['google']['place_detail']."?".http_build_query(array(
				  'key'=>self::$api_key,
				  'placeid'=>CommonUtility::safeTrim($place_id)
				));
								
				$result = self::getData($api_url);		
				
				if (is_string($result) && strlen($result) > 0){
					if ($resp = json_decode($result,true)){				
						$data = MapSdk::parseGoogleResponse($resp,'place_detail');
					}
				}								
				break;
		
			case "mapbox":	
			    	
				$place_id = !empty($place_id)?$place_id:urlencode($place_description);
			    $api_url = self::$map_api['mapbox']['place_detail']."/$place_id.json?".http_build_query(array(
				  'access_token'=>self::$api_key				  
				));									
								
				if($results_coords = CommonUtility::parseCoordinates($place_id)){										
					self::setMapboxCoordinates($results_coords);
				}				

				$api_url.= self::getMapParameters("=");																
				$result = self::getData($api_url);				
				
				if (is_string($result) && strlen($result) > 0){
					if ($resp = json_decode($result,true)){							
						$data = self::parseMapboxResponse($resp,'place_detail');
					}
				}					
			    break;

			case "yandex":	
				$language = isset(Yii::app()->params['settings']['yandex_language'])?Yii::app()->params['settings']['yandex_language']:'';		
				$place_id = !empty($place_description)? $place_description :$place_id;				
				$api_url = self::$map_api['yandex']['place_detail']."?".http_build_query(array(
					'apikey'=>self::$api_key,
					'geocode'=>$place_id,
					'format'=>'json',
					'results'=>1,
					'lang'=>$language
				));						
				$result = self::getData($api_url);			
				if(self::$http_code==200){
					if (is_string($result) && strlen($result) > 0){
						if ($resp = json_decode($result,true)){							
							$data = self::parseYandexResponse($resp,'place_detail');
						}
					}							
				} else if ( self::$http_code==400 )	{
					throw new Exception ( 'The request is missing a required parameter or an invalid parameter value is specified. The message contains additional information about the error.' ); 
				} else if ( self::$http_code==403 )	{
					throw new Exception ( "The request doesn't contain the apikey parameter or an invalid key was specified." ); 
				} else if ( self::$http_code==429 )	{
					throw new Exception ( "There are too many requests in a short time." ); 
				}
				break;
			    
			default:
				throw new Exception ( 'undefined map provider' );
				break;
		}			
		
		if(is_array($data) && count($data)>=1){
		   return $data;
		} else throw new Exception ( 'no results' );
	}
	
	public static function parse_address_components($address_components) {
		$street_number = '';
		$street_name = '';
		$city = '';
		$state = '';
		$postal_code = '';
		$country = '';
		$place_text='';
				
		$priority = ['locality', 'administrative_area_level_1', 'country'];
		foreach ($priority as $type) {
			foreach ($address_components as $component) {
				if (in_array($type, $component['types'])) {
					$place_text.= $component['long_name'];
					$place_text.=" ";
				}
			}
		}
		
		foreach ($address_components as $component) {
			foreach ($component['types'] as $component_type) {
				if (strpos($component_type, 'street_number') !== false) {
					$street_number = $component['long_name'];
				} elseif ($component_type == 'route') {
					$street_name = $component['long_name'];
				} elseif ($component_type == 'postal_town' || $component_type == 'locality') {
					$city = $component['long_name'];
				} elseif ($component_type == 'administrative_area_level_1') {
					$state = $component['short_name'];
				} elseif ($component_type == 'postal_code') {
					$postal_code = $component['long_name'];
				} elseif ($component_type == 'country') {
					$country = $component['long_name'];
				}
			}
		}
		
		return array(
			'place_text'=>$place_text,
			'street_number' => $street_number,
			'street_name' => $street_name,
			'city' => $city,
			'state' => $state,
			'postal_code' => $postal_code,
			'country' => $country
		);
	}

	public static function parseGoogleResponse($resp=array(),$parse_method='place')
	{
		$data = array();
		$status = isset($resp['status']) ? CommonUtility::safeTrim($resp['status']) :'';
		switch ($status) {
			case "OK":
				if($parse_method==="place"){
					if(isset($resp['predictions'])){
						foreach ($resp['predictions'] as $val) {	
							$place_type = '';
							foreach ($val['types'] as $types){
								$place_type.="$types,";
							}							
							$place_type = !empty($place_type)?substr($place_type,0,-1):'';							
							$data[]=array(
							  'id'=>isset($val['place_id'])?$val['place_id']:'',
							  'provider'=>self::$map_provider,
							  'addressLine1'=>$val['structured_formatting']['main_text'] ?? '',							  
							  'addressLine2'=>isset($val['structured_formatting']['secondary_text'])?$val['structured_formatting']['secondary_text']:'',
							  'place_type'=>$place_type,
							  'description'=>isset($val['description'])?$val['description']:''
							);
						}		
					}				
				} elseif ( $parse_method==="place_detail"){		
																		
					$address_components = $resp['result']['address_components'];										
					$parsed_address = self::parse_address_components($address_components);
					$parsed_address['formatted_address'] = $resp['result']['formatted_address'];					
										
					$address1 = array('route');
					$street_number = array('street_number');
					$city = array('postal_town','locality');
					$address2 = array('locality','route','administrative_area_level_1','administrative_area_level_2');
					$country  =  array('country');
					$postal_code = array('postal_code');
					
					$address_out['address1']='';
					$address_out['street_number']='';
					$address_out['city']='';
					$address_out['address2']='';
					$address_out['country']='';
					$address_out['country_code']='';
					$address_out['postal_code']='';
					$address_out['formatted_address'] = $resp['result']['formatted_address'];
					
					$latitude = isset($resp['result']['geometry']['location']['lat']) ? $resp['result']['geometry']['location']['lat'] : '';
					$longitude = isset($resp['result']['geometry']['location']['lng']) ? $resp['result']['geometry']['location']['lng'] : '';
					// Extract place_id - Google Geocoding API includes place_id in reverse geocoding results
					$place_id = isset($resp['result']['place_id']) ? $resp['result']['place_id'] : '';
					$reference = isset($resp['result']['reference'])?$resp['result']['reference']:'';
					// If place_id is not available, try to use the formatted address as a fallback identifier
					if(empty($place_id) && isset($resp['result']['formatted_address'])){
						// For reverse geocoding, we can use coordinates as place_id if no place_id is available
						// This will be handled by the calling code
					}
					
					$name = isset($resp['result']['name'])?$resp['result']['name']:'';
					
					if(is_array($address_components) && count($address_components)>=1){
						foreach ($address_components as $val) {									
							foreach ($val['types'] as $types) {														
								if(in_array($types,$address1)){		
									if(!empty($address_out['address1'])){
										$address_out['address1'].= ", ".$val['long_name'];
									} else $address_out['address1'].= $val['long_name'];									
								}
								if(in_array($types,$street_number)){		
									if(!empty($address_out['street_number'])){
										$address_out['street_number'].= ", ".$val['long_name'];
									} else $address_out['street_number'].= $val['long_name'];									
								}
								if(in_array($types,$address2)){
									if(!empty($address_out['address2'])){
										$address_out['address2'].= ", ".$val['long_name'];
									} else $address_out['address2'].= $val['long_name'];									
								}
								if(in_array($types,$country)){
									$address_out['country'].= $val['long_name'];
									$address_out['country_code'].= $val['short_name'];
								}
								if(in_array($types,$postal_code)){
									$address_out['postal_code'].= $val['long_name'];
								}
								if(in_array($types,$city)){
									$address_out['city'].= $val['long_name'];
								}
							}															
						}
												
						if(!empty($name)){
						   $address_out['address1'] = $name;
						}
					}
					
					$place_type = '';
					if(isset($resp['result']['types'])){
						foreach ($resp['result']['types'] as $types){
							$place_type.="$types,";
						}
						$place_type = !empty($place_type)?substr($place_type,0,-1):'';
					}					
										
					$data = array(
					  'address'=>$address_out,
					  'latitude'=>$latitude,
					  'longitude'=>$longitude,
					  'place_id'=>$place_id,
					  'reference'=>$reference,
					  'place_type'=>$place_type,
					  'parsed_address'=>$parsed_address
					);								
				} elseif ( $parse_method==="distance"){						
					$elements = $resp['rows'][0]['elements'][0];
					$elements_status = $resp['rows'][0]['elements'][0]['status'];									
					if($elements_status=="ZERO_RESULTS"){
					   throw new Exception($elements_status);
					}
					if($elements_status=="NOT_FOUND"){
					   throw new Exception($elements_status);
					}
					
					$parameters = self::$map_parameters;		
					$unit = $parameters['unit'] ?? 'mi';
					$distance = 0; $duration = 0; $duration_unit = ''; $duration_value = 0;
					
					$distance_value = $elements['distance']['value'] ?? 0;
					$duration_value = $elements['duration']['value'] ?? 0;
					$duration_text = $elements['duration']['text'] ?? null;
					
					$distance = self::convertDistance($distance_value,$unit);		
					
					if($duration_text){
						preg_match('/(\d+)\s*(\w+)/', $duration_text, $matches);					    
						$duration = $matches[1] ?? 0;
						$duration_unit = $matches[2] ?? 'mins';
					}					
																			
					$data = array(
					  'distance'=>$distance,
					  'unit'=>$unit,
					  'pretty_unit'=>MapSdk::prettyUnit($unit),
					  'duration'=>$duration,
					  'duration_unit'=>$duration_unit,
					  'duration_value'=>ceil($duration_value/60)
					);					
				}
				break;
			
			case "ZERO_RESULTS":	
			    //throw new Exception("zero search found");
			    break;							
		
			case "OVER_QUERY_LIMIT":	
			    throw new Exception("over query limit");
			    break;
			        
			case "REQUEST_DENIED":  				
			    throw new Exception( isset($resp['error_message'])?$resp['error_message']:"request denied" );  
			    break;
			        
			case "INVALID_REQUEST":						     				
				throw new Exception( isset($resp['error_message'])?$resp['error_message']:"input parameter is missing" );  
			    break;					    					   			
			    
			case "UNKNOWN_ERROR":
				throw new Exception("unknow error");
				break;
				
			case "NOT_FOUND":
				throw new Exception("place id not found");
				break;	
				
			default:
				throw new Exception("undefined error");
				break;
		}
		return $data;
	}

	public static function convertDistance($distance_value, $unit_system) {
		if ($unit_system == 'mi') {
			// Convert meters to miles			
			$miles = $distance_value / 1609.34;		
			return number_format($miles,1,'.','');
		} else {
			// Convert meters to kilometers
			$km = $distance_value / 1000;
			return number_format($km, 1);
		}
	}	
	
	public static function parseAddressComponents($data) {		
		$features = $data['features'] ?? [];		
		
		$coordinates = self::getMapboxCoordinates();							
		$lat = $coordinates['latitude'] ?? null;
		$lng = $coordinates['longitude'] ?? null;		
		
		// Step 1: Find best matching feature by coordinates
		$bestMatch = null;
		foreach ($features as $feature) {
			if (!empty($feature['center']) &&
				abs($feature['center'][0] - $lng) < 0.00001 &&
				abs($feature['center'][1] - $lat) < 0.00001) {
				$bestMatch = $feature;
				break;
			}
		}

		// Step 2: Fallback if no exact match
		if (!$bestMatch && isset($features[0])) {			
			$bestMatch = $features[0];
		}

		if (!$bestMatch) {
			return null; // No address found
		}

		// Step 3: Initialize variables
		$streetNumber = '';
		$streetName = '';
		$city = '';
		$state = '';
		$postalCode = '';
		$country = '';
		$formattedAddress = $bestMatch['place_name'] ?? '';

		// Extract street info
		$type = $bestMatch['place_type'][0] ?? '';
		if ($type === 'address') {
			$streetName = $bestMatch['text'] ?? '';
			$streetNumber = $bestMatch['address'] ?? '';
		} elseif ($type === 'street' || $type === 'road') {
			$streetName = $bestMatch['text'] ?? '';
		}

		// Step 4: Extract from context array
		$contexts = $bestMatch['context'] ?? [];

		// Sometimes the bestMatch itself contains some fields like 'place' or 'region'
		array_unshift($contexts, $bestMatch); // Ensure we also check the main feature

		foreach ($contexts as $context) {
			$id = $context['id'] ?? '';
			if (strpos($id, 'place.') === 0) {
				$city = $context['text'] ?? '';
			} elseif (strpos($id, 'region.') === 0) {
				$state = $context['text'] ?? '';
			} elseif (strpos($id, 'postcode.') === 0) {
				$postalCode = $context['text'] ?? '';
			} elseif (strpos($id, 'country.') === 0) {
				$country = $context['text'] ?? '';
			}
		}

		$place_text = $bestMatch['text'] ?? '';
		return [
			'place_text'=>$place_text,
			'street_number'     => $streetNumber,
			'street_name'       => $streetName,
			'city'              => $city,
			'state'             => $state,
			'postal_code'       => $postalCode,
			'country'           => $country,
			'formatted_address' => $formattedAddress,
		];
	}

	public static function parseMapboxResponse($resp=array(),$parse_method='place')
	{		
		$data = array();
		
		if($parse_method=="place"){			
			if(is_array($resp) && count($resp)>=1){								
				if(isset($resp['features'])){
					foreach ($resp['features'] as $val) {						

						$country_name = ''; $country_code = '';
						if(isset($val['context'])){
							foreach ($val['context'] as $items_country) {
								if(isset($items_country['short_code'])){									
									$country_name = $items_country['text'];
									$country_code = $items_country['short_code'];
								}
							}
						}																

						$data[]=array(
						  'id'=>$val['id'],
						  'provider'=>self::$map_provider,
						  'addressLine1'=>$val['text'],
						  'addressLine2'=>$val['place_name'],						
						  'latitude'=>$val['center'][1],
						  'longitude'=>$val['center'][0],
						  'place_type'=>$val['place_type'][0],
						  'description'=>$val['place_name'],
						  'country'=>$country_name,
						  'country_code'=>$country_code
						);
					}
				} else {
					$error = self::$http_code." ";
					$error.= isset($resp['message'])?$resp['message']:'';
					throw new Exception( $error );
				}
			}
		} elseif ($parse_method=="place_detail"){
			
			
			$address1 = array('poi','address','neighborhood');
			$address2 = array('locality','place','district','region');
			$country  =  array('country');
			$postal_code = array('postcode');
			
			$address_out['address1']='';
			$address_out['address2']='';
			$address_out['country']='';
			$address_out['postal_code']='';
			$address_out['formatted_address'] = '';
			$address_out['country_code']='';
			
			$parsed_address = [];
			
			if(is_array($resp) && count($resp)>=1){
				if(isset($resp['features'])){								
					$parsed_address = self::parseAddressComponents($resp);					

					foreach ($resp['features'] as $val) {		
																				
						$address_out['formatted_address'] =isset($val['place_name'])?$val['place_name']:'';
						$address_out['address1'] =  isset($val['text'])?$val['text']:'';
						
						if(isset($val['context'])){
							if(is_array($val['context']) && count($val['context'])>=1){
								foreach ($val['context'] as $context) {									
									$id = isset($context['id'])? substr($context['id'],0, strpos($context['id'],".") ) :'';
									$text=isset($context['text'])?$context['text']:'';									
									
									if (is_string($address_out['address1']) && strlen($address_out['address1']) <= 0){
									if(in_array($id,$address1)){		
										if(!empty($address_out['address1'])){
											$address_out['address1'].= ", ".$text;
										} else $address_out['address1'].= $text;
									}
									}
									
									if(in_array($id,$address2)){
										if(!empty($address_out['address2'])){
											$address_out['address2'].= ", ".$text;
										} else $address_out['address2'].= $text;
									}
									if(in_array($id,$country)){
										$address_out['country'].= $text;
									}
									if(in_array($id,$postal_code)){
										$address_out['postal_code'].= $text;
									}
									
								}
							}
														
						}
						
						$place_type = '';
						if(isset($val['place_type'])){
							foreach ($val['place_type'] as $types){
								$place_type.="$types,";
							}
							$place_type = !empty($place_type)?substr($place_type,0,-1):'';
						}					
						
						$data=array(
						  'address'=>$address_out,
						  'place_id'=>$val['id'],
						  'reference'=>$val['id'],						  						  
						  'latitude'=>$val['center'][1],
						  'longitude'=>$val['center'][0],
						  'place_type'=>$place_type,
						  'country'=>'',
						  'country_code'=>'',
						  'parsed_address'=>$parsed_address
						);
						break;
					}
				} else {
					$error = self::$http_code." ";
				    $error.= isset($resp['message'])?$resp['message']:'';
				    throw new Exception( $error );
				}
			} 
		} elseif ($parse_method=="distance"){			
			$code = isset($resp['code'])?$resp['code']:'';						
			if($code=="Ok"){				
				$distances = isset($resp['distances'])?$resp['distances']:'';
				$durations = isset($resp['durations'])?$resp['durations']:'';
				if(is_array($distances) && count($distances)>=1){
					$distance = isset($distances[0][1])?$distances[0][1]:0;					
					$unit = self::$map_parameters['unit'];
					$convertion = $unit=="mi"?0.000621371:0.001;
					$distance = $distance*$convertion;					

					$duration_unit = 'min'; $duration = '';
					$duration_resp = isset($durations[0][1])?$durations[0][1]:0;						
					$duration_value = $duration_resp;
					$duration_resp = CommonUtility::seconds2human($duration_resp);					
					if($duration_resp['hour']>0){
						$duration = $duration_resp['hour'].":".$duration_resp['minute'];
					} else $duration = $duration_resp['minute'];

					$data = [
						'distance'=>round( floatval($distance),2),
						'unit'=>$unit,
						'pretty_unit'=>MapSdk::prettyUnit($unit),
						'duration'=>$duration,
						'duration_unit'=>$duration_unit,						
						'duration_value'=>ceil($duration_value/60)
					];						
				} else throw new Exception( "Route not available" );				
			} else if ($code=="NoRoute"){
				throw new Exception( "Route not available" );
			} else {
				throw new Exception( "Route returns null" );
			}
		}		
		return $data;
	}
	
	public static function prettyUnit($unit='')
	{
		switch ($unit) {
			case "M":		
			case "mi":	
			    return t("miles");
				break;
				
			case "K":			
			case "km":	
			    return t("km");
				break;	
				
			case "m":			
			    return t("m");
				break;		
				
			case "ft":			
			    return t("ft");
				break;			
		
			default:
				return $unit;
				break;
		}
	}
	
	public static function reverseGeocoding($lat='', $lng='')
	{
		if(empty($lat)){
			throw new Exception("invalid latitude parameters");
		}
		if(empty($lng)){
			throw new Exception("invalid longitude parameters");
		}

		$data = array();
		
		switch (self::$map_provider) {
			case "google.maps":	
			   $api_url = self::$map_api['google']['reverse_geocoding']."?".http_build_query(array(
			      'latlng'=>"$lat,$lng",
			      //'location_type'=>"ROOFTOP",
				  'key'=>self::$api_key,			  
			   ));					   			   
			   $result = self::getData($api_url);
			   if (is_string($result) && strlen($result) > 0){
				   if ($resp = json_decode($result,true)){					   	   
					   $new_resp = array(
					    'error_message'=>isset($resp['error_message'])?$resp['error_message']:'',
					    'result'=>isset($resp['results'][0])?$resp['results'][0]:array(),
					    'status'=>isset($resp['status'])?$resp['status']:''
					   );					   
					   $data = MapSdk::parseGoogleResponse($new_resp,'place_detail');					   
				   }
			   }	
			break;
			
			case "mapbox":
			   $api_url = self::$map_api['mapbox']['reverse_geocoding']."/$lng,$lat".".json?".http_build_query(array(
			      'access_token'=>self::$api_key	
			   ));			   

			   self::setMapboxCoordinates([
				 'latitude' => CommonUtility::safeTrim($lat),
			     'longitude' => CommonUtility::safeTrim($lng)				
			   ]);

			   $api_url.= self::getMapParameters("=");			   
			   $result = self::getData($api_url);
			   if (is_string($result) && strlen($result) > 0){
				   if ($resp = json_decode($result,true)){				   	   
					   try {
						   $data = MapSdk::parseMapboxResponse($resp,'place_detail');
						   // Preserve place_id if it exists (even for generic addresses)
						   $preserved_place_id = isset($data['place_id']) ? $data['place_id'] : '';
						   $preserved_reference = isset($data['reference']) ? $data['reference'] : '';
						   
						   // Check if result is too generic or empty
						   if(empty($data['formatted_address']) || 
							  (isset($data['address']['country']) && empty($data['address']['city']) && empty($data['address']['address1']))){
							   $data['formatted_address'] = !empty($data['formatted_address']) ? $data['formatted_address'] : t("Current Location");
							   // Only clear place_id if it wasn't set in the first place
							   if(empty($preserved_place_id)){
							   	$data['place_id'] = '';
							   } else {
							   	$data['place_id'] = $preserved_place_id;
							   	$data['reference'] = $preserved_reference;
							   }
						   } else {
						   	// Ensure place_id and reference are preserved
						   	if(!empty($preserved_place_id)){
						   		$data['place_id'] = $preserved_place_id;
						   	}
						   	if(!empty($preserved_reference)){
						   		$data['reference'] = $preserved_reference;
						   	}
						   }
					   } catch (Exception $e) {
						   // Fallback if parsing fails
						   $data = array(
							   'latitude' => $lat,
							   'longitude' => $lng,
							   'formatted_address' => t("Current Location"),
							   'place_id' => '',
							   'address' => array(
								   'formatted_address' => t("Current Location"),
								   'country' => '',
								   'city' => '',
								   'address1' => ''
							   )
						   );
					   }
					   $data['latitude'] = $lat;
					   $data['longitude'] = $lng;
				   }
			   } else {
				   // Fallback if API call fails
				   $data = array(
					   'latitude' => $lat,
					   'longitude' => $lng,
					   'formatted_address' => t("Current Location"),
					   'place_id' => '',
					   'address' => array(
						   'formatted_address' => t("Current Location"),
						   'country' => '',
						   'city' => '',
						   'address1' => ''
					   )
				   );
			   }
			break;

			case "yandex":
				$language = isset(Yii::app()->params['settings']['yandex_language'])?Yii::app()->params['settings']['yandex_language']:'';
				$api_url = self::$map_api['yandex']['reverse_geocoding']."/?".http_build_query([
					'apikey'=>self::$api_key,
					'geocode'=>"$lng,$lat",
					'results'=>1,
					'lang'=>$language,
					'format'=>"json",
				]);				
				$result = self::getData($api_url);
				if(self::$http_code==200){
					if (is_string($result) && strlen($result) > 0){
						if ($resp = json_decode($result,true)){									
							$data = MapSdk::parseYandexResponse($resp,'place_detail');
						}
					}							
				} else if ( self::$http_code==400 )	{
					throw new Exception ( 'The request is missing a required parameter or an invalid parameter value is specified. The message contains additional information about the error.' ); 
				} else if ( self::$http_code==403 )	{
					throw new Exception ( "The request doesn't contain the apikey parameter or an invalid key was specified." ); 
				} else if ( self::$http_code==429 )	{
					throw new Exception ( "There are too many requests in a short time." ); 
				}								
				break;
		}
		
		// Final fallback: ensure we always return at least coordinates
		if(empty($data) || (!isset($data['latitude']) || !isset($data['longitude']))){
			$data = array(
				'latitude' => CommonUtility::safeTrim($lat),
				'longitude' => CommonUtility::safeTrim($lng),
				'formatted_address' => t("Current Location"),
				'place_id' => '',
				'address' => array(
					'formatted_address' => t("Current Location"),
					'country' => '',
					'city' => '',
					'address1' => ''
				)
			);
		}
				
		return $data;
	}

	
	/*
	how to use
	
	MapSdk::$map_provider = Yii::app()->params['settings']['map_provider'];		   
    MapSdk::setKeys(array(
     'google.maps'=>Yii::app()->params['settings']['google_geo_api_key'],
     'mapbox'=>Yii::app()->params['settings']['mapbox_access_token'],
    ));
    		    		    		    		    		  
    MapSdk::setMapParameters(array(
      'from_lat'=>$merchant_lat,
      'from_lng'=>$merchant_lng,
      'to_lat'=>$customer_lat,
      'to_lng'=>$customer_lng,
      'place_id'=>$place_id,
      'unit'=>$unit,
      'mode'=>'driving'
    ));*/
		    
	public static function distance()
	{
		$data=array(); $parameters = self::$map_parameters;		
		
		if(is_array($parameters) && count($parameters)>=1){
			
			if(empty($parameters['to_lng']) || empty($parameters['to_lat'])){
			   throw new Exception ( 'Address is required' );
			}

			switch (MapSdk::$map_provider) {
				case "google.maps":					
					$params = array(
					  'origins'=>CommonUtility::safeTrim($parameters['from_lat']).",".CommonUtility::safeTrim($parameters['from_lng']),
					  'units'=>$parameters['unit']=="mi"?"imperial":"metric",
					  'key'=>MapSdk::$api_key,
					);
					if(isset($parameters['mode'])){
						$params['mode'] = CommonUtility::safeTrim($parameters['mode']);
					}
					if(isset($parameters['place_id'])){						
						$params['destinations']="place_id:".$parameters['place_id'];
					} else $params['destinations']=CommonUtility::safeTrim($parameters['to_lat']).",".CommonUtility::safeTrim($parameters['to_lng']);
					
					$api_url = MapSdk::$map_api['google']['distance']."?".http_build_query($params);									
										
					$result = self::getData($api_url);											
					if (is_string($result) && strlen($result) > 0){
						if ($resp = json_decode($result,true)){								
							$data = MapSdk::parseGoogleResponse($resp,'distance');
						}						
					}						
				
					break;
			
				case "mapbox":					
					$params = [						
						'annotations'=>"distance,duration",
						'access_token'=>MapSdk::$api_key
					];					
					$coordinates = CommonUtility::safeTrim($parameters['from_lng']).",".CommonUtility::safeTrim($parameters['from_lat']);
					$coordinates.=";";
					$coordinates.= CommonUtility::safeTrim($parameters['to_lng']).",".CommonUtility::safeTrim($parameters['to_lat']);
					$api_url = MapSdk::$map_api['mapbox']['distance']."/".CommonUtility::safeTrim($parameters['mode'])."/".$coordinates."?".http_build_query($params);										
					$result = self::getData($api_url);
					if (is_string($result) && strlen($result) > 0){
						if ($resp = json_decode($result,true)){								
							$data = MapSdk::parseMapboxResponse($resp,'distance');
						}
					}									
					break;
						
				default:
					break;
			}
		} 
		
		if(is_array($data) && count($data)>=1){
		   return $data;
		} else throw new Exception ( 'no results' );
	}

	public static function parseYandexResponse($resp=array(),$parse_method='place')
	{
		$data = array();
		if($parse_method=="place"){	
			$results = isset($resp['results'])?$resp['results']:'';						
			if(is_array($results) && count($results)>=1){	
				foreach ($results as $items) {
					$title = isset($items['title'])? (isset($items['title']['text'])?$items['title']['text']:'') :'';
					$subtitle = isset($items['subtitle'])?$items['subtitle']['text']:'';
					$address = isset($items['address'])? (isset($items['address']['formatted_address'])?$items['address']['formatted_address']:'') :'';					
					$component = isset($items['address'])? (isset($items['address']['component'])?$items['address']['component']:'') :'';					

					$country = ''; $country_code = '';

					if(is_array($component) && count($component)>=1){
						foreach ($component as $component_items) {							
							if(in_array("COUNTRY",$component_items['kind'])){
								$country = $component_items['name'];
							}
						}
					}
					$data[] = [
						'id'=>$address,
						'provider'=>self::$map_provider,
						'addressLine1'=>$title,
						'addressLine2'=>$subtitle,
						'latitude'=>'',
						'longitude'=>'',
						'description'=>$address,
						'country'=>$country,
						'country_code'=>$country_code
					];				
				}				
			} else {

			}
		} else if ( $parse_method=="place_detail"){
			$response = isset($resp['response'])? (isset($resp['response']['GeoObjectCollection'])?$resp['response']['GeoObjectCollection']:'') :'';									
			if(is_array($response) && count($response)>=1){
				$metaDataProperty = isset($response['metaDataProperty'])? (isset($response['metaDataProperty']['GeocoderResponseMetaData'])?$response['metaDataProperty']['GeocoderResponseMetaData']:'') :'';												
				$found = isset($metaDataProperty['found'])? ($metaDataProperty['found']==1?true:false) :false;
				if($found){
					$featureMember = isset($response['featureMember'])? (isset($response['featureMember'][0])?$response['featureMember'][0]['GeoObject']:'') :'';									
					$GeocoderMetaData = isset($featureMember['metaDataProperty'])?$featureMember['metaDataProperty']['GeocoderMetaData']:'';
					$Address = isset($GeocoderMetaData['Address'])?$GeocoderMetaData['Address']:'';
					$place_type = isset($GeocoderMetaData['kind'])?$GeocoderMetaData['kind']:'';
					
					$address_line1 = isset($featureMember['name'])?$featureMember['name']:'';
					$address_line2 = isset($featureMember['description'])?$featureMember['description']:'';
					$Point = isset($featureMember['Point'])?$featureMember['Point']['pos']:'';
					$coordinates = !empty($Point)?explode(" ",$Point):'';
					$longitude = isset($coordinates[0])?$coordinates[0]:'';
					$latitude = isset($coordinates[1])?$coordinates[1]:'';

					$formatted_address = isset($Address['formatted'])?$Address['formatted']:'';					

					$Components = isset($Address['Components'])?$Address['Components']:'';					
					$country_code = isset($Address['country_code'])?$Address['country_code']:'';
					$streetNumber = ''; $streetName = ''; $city = ''; $state = ''; $country = ''; $postal_code = '';
					if(is_array($Components) && count($Components)>=1){
						foreach ($Components as $component) {
							switch ($component['kind']) {
								case 'street':									
									$streetName = $component['name'];
									break;
								case 'locality':									
									$city = $component['name'];
									break;
								case 'district':									
									$state = $component['name'];
									break;
								case 'country':									
									$country = $component['name'];
									break;
								case 'region':									
									$postal_code = $component['name'];
									break;
								case 'district':									
								case 'house':									
								case 'entrance':
								case 'level':
								case 'apartment':
									$postal_code = $component['name'];
									break;
							}
						}
					}	
					
					$address_out = [
						'address1'=>$address_line1,
						'address2'=>$address_line2,
						'country'=>$country,
						'country_code'=>$country_code,
						'postal_code'=>$postal_code,
						'formatted_address'=>$formatted_address
					];
					$parsed_address = [
						'street_number'=>$streetNumber,
						'street_name'=>$streetName,
						'city'=>$city,
						'state'=>$state,
						'postal_code'=>$postal_code,
						'country'=>$country,
						'formatted_address'=>$formatted_address
					];					
					$data = [
						'address'=>$address_out,
						'latitude'=>$latitude,
						'longitude'=>$longitude,
						'place_id'=>$address_line1,
						'reference'=>$address_line1,
						'place_type'=>$place_type,
						'parsed_address'=>$parsed_address
					];					
				} else {

				}
			} else {

			}
		}
		return $data;
	}

	public static function setMapboxCoordinates($value='')
	{
		self::$mapbox_coords = $value;	
	}

	public static function getMapboxCoordinates()
	{
		return self::$mapbox_coords ?? null;
	}
	
}
/*end class*/