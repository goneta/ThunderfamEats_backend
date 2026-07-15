<?php
class CMaps{
	
	public static function config($keys='google_geo_api_key')
	{		
		
		if(isset(Yii::app()->params['settings']['map_provider'])){
			MapSdk::$map_provider = Yii::app()->params['settings']['map_provider'];	
			MapSdk::setKeys(array(
		     'google.maps'=>isset(Yii::app()->params['settings'][$keys])? (!empty(Yii::app()->params['settings'][$keys])?Yii::app()->params['settings'][$keys]:'') :'',
		     'mapbox'=>isset(Yii::app()->params['settings']['mapbox_access_token'])?Yii::app()->params['settings']['mapbox_access_token']:'',
			 'yandex'=>isset(Yii::app()->params['settings']['yandex_javascript_api'])?Yii::app()->params['settings']['yandex_javascript_api']:'',
		    ));

			$default_location_lat = '34.04703'; 
			$default_location_lng ='-118.246860';

			if(isset(Yii::app()->params['settings'])){
				$default_location_lat = isset(Yii::app()->params['settings']['default_location_lat'])?
				( !empty(Yii::app()->params['settings']['default_location_lat'])?Yii::app()->params['settings']['default_location_lat']: $default_location_lat )
				:$default_location_lat;

				$default_location_lng = isset(Yii::app()->params['settings']['default_location_lng'])?
				( !empty(Yii::app()->params['settings']['default_location_lng'])?Yii::app()->params['settings']['default_location_lng']: $default_location_lng )
				:$default_location_lng;
			}

			$address_format_use = isset(Yii::app()->params['settings']['address_format_use'])? (!empty(Yii::app()->params['settings']['address_format_use'])?Yii::app()->params['settings']['address_format_use']:'') :'';
			$address_format_use = !empty($address_format_use)?$address_format_use:1;
					    
			$language = isset(Yii::app()->params['settings']['yandex_language'])? (!empty(Yii::app()->params['settings']['yandex_language'])?Yii::app()->params['settings']['yandex_language']:'') :'';			
			$map_custom_style = isset(Yii::app()->params['settings']['map_custom_style'])? (!empty(Yii::app()->params['settings']['map_custom_style'])?Yii::app()->params['settings']['map_custom_style']:'') :'';

			return array(
			  'provider'=>MapSdk::$map_provider,
			  'map_provider'=>MapSdk::$map_provider,
			  'key'=>MapSdk::$api_key,
			  'zoom'=>15,		  
			  'icon'=>websiteDomain().Yii::app()->theme->baseUrl."/assets/images/marker2@2x.png",
			  'icon_merchant'=>websiteDomain().Yii::app()->theme->baseUrl."/assets/images/restaurant-icon1.png",
			  'icon_destination'=>websiteDomain().Yii::app()->theme->baseUrl."/assets/images/home-icon1.png",
			  'icon_rider'=>websiteDomain().Yii::app()->theme->baseUrl."/assets/images/rider-icon.png",
			  'default_lat'=> $default_location_lat,
			  'default_lng'=> $default_location_lng,
			  'address_format_use'=>$address_format_use,
			  'language'=>$language,
			  'map_custom_style'=>$map_custom_style
			);
		}
		return false;
	}
	
	public static function locationDetails($place_id='',$country_code='',$country_name='',$place_description='')
	{
		try {
			
			if(empty($place_id)){
				throw new Exception( 'Place ID is empty' );
			}
			
			if(!isset(Yii::app()->params['settings']['map_provider'])){
				throw new Exception( 'Map provider not set' );
			}
			
			$resp = array();
			if(empty(MapSdk::$map_provider)){
				MapSdk::$map_provider = Yii::app()->params['settings']['map_provider'];	
				MapSdk::setKeys(array(
					'google.maps'=>isset(Yii::app()->params['settings']['google_geo_api_key'])?Yii::app()->params['settings']['google_geo_api_key']:'',
					'mapbox'=>isset(Yii::app()->params['settings']['mapbox_access_token'])?Yii::app()->params['settings']['mapbox_access_token']:'',
					'yandex'=>isset(Yii::app()->params['settings']['yandex_geocoder_api'])?Yii::app()->params['settings']['yandex_geocoder_api']:''
				));
			}			
			$address_format_use = isset(Yii::app()->params['settings']['address_format_use'])? (!empty(Yii::app()->params['settings']['address_format_use'])?Yii::app()->params['settings']['address_format_use']:'') :'';
			$address_format_use = !empty($address_format_use)?$address_format_use:1;
			 
			$reference_type = MapSdk::$map_provider;
			
			// Check if place_id is in coordinate format (lng,lat) - common for Ivory Coast
			$is_coordinate_format = preg_match('/^-?\d+\.?\d*,-?\d+\.?\d*$/', $place_id);
			$lat = null;
			$lng = null;
			
			if($is_coordinate_format){
				// Handle coordinate-based place_id
				list($lng, $lat) = explode(',', $place_id);
				$model = AR_map_places::model()->find('reference_type=:reference_type AND ABS(latitude - :latitude) < 0.0001 AND ABS(longitude - :longitude) < 0.0001', 
				array(':reference_type'=>$reference_type, ':latitude'=>$lat, ':longitude'=>$lng)); 
			} else {
				// Standard place_id lookup
				$model = AR_map_places::model()->find('reference_type=:reference_type AND reference_id=:reference_id', 
				array(':reference_type'=>$reference_type, ':reference_id'=>$place_id)); 
			}
			
		    if($model){		 
			   $complete_delivery_address = '';
			   $parsed_address = !empty($model->parsed_address)?json_decode($model->parsed_address,true):[];			   
			   $complete_delivery_address = "$model->address1 $model->formatted_address";
		       $resp = array(
		   	    'address'=>array(
		   	       'address1'=>$model->address1,
		   	       'city'=>$model->city,
				   'street_number'=>$model->street_number,
		   	       'address2'=>$model->address2,
		   	       'country'=>$model->country,
		   	       'country_code'=>$model->country_code,
		   	       'postal_code'=>$model->postal_code,
		   	       'formatted_address'=>$model->formatted_address,
				   'company'=>'',
				   'address_format_use'=>$address_format_use,
				   'complete_delivery_address'=>$complete_delivery_address
		   	    ),
		   	    'latitude'=>$model->latitude,
		   	    'longitude'=>$model->longitude,
		   	    'place_id'=>$model->reference_id,
		   	    'reference'=>$model->reference_id,
				'parsed_address'=>$parsed_address,
				'from_db'=>true
		   	  );		   	  		   	
		   	  $reference_id=$model->reference_id;
		    } else {				
				
		    	// If place_id is coordinate format, use reverse geocoding instead of placeDetails
		    	if($is_coordinate_format && $lat !== null && $lng !== null){
		    		$resp = MapSdk::reverseGeocoding($lat, $lng);
		    		// Set reference_id to the coordinate format for consistency
		    		$resp['reference'] = $place_id;
		    		$resp['place_id'] = $place_id;
		    	} else {
		    		$parameters = array();				   
			    	MapSdk::setMapParameters($parameters);			   			   			  				   
			    	$resp = MapSdk::placeDetails($place_id,$place_description);
		    	}

		    	if(MapSdk::$map_provider=="mapbox"){					
		    		$resp['address']['country'] = $country_name;				
		    		$resp['address']['country_code'] = $country_code;				
		    		$resp['country'] = $country_name;				
		    		$resp['country_code'] = $country_code;			
		    	}

		    	$reference_id=isset($resp['reference'])?$resp['reference']:'';
	
		    	/*SAVE PLACE DETAILS*/		   
		    	$model = new AR_map_places;
		    	$address_components = isset($resp['address'])?$resp['address']:'';				
						   
		    	$model->reference_type = $reference_type;
		    	// For coordinate-based place_ids, use the coordinate string as reference_id
		    	$model->reference_id = isset($resp['reference']) && !empty($resp['reference']) ? $resp['reference'] : ($is_coordinate_format ? $place_id : '');
		    	$model->latitude = isset($resp['latitude'])?$resp['latitude']:'';
		    	$model->longitude = isset($resp['longitude'])?$resp['longitude']:'';
		    	$model->address1 = isset($address_components['address1'])?$address_components['address1']:'';
		    	$model->city = isset($address_components['city'])?$address_components['city']:'';
		    	$model->street_number = isset($address_components['street_number'])?$address_components['street_number']:'';
		    	$model->address2 = isset($address_components['street_name'])?$address_components['street_name']:'';
		    	$model->country = isset($address_components['country'])?$address_components['country']:'';
		    	$model->country_code = isset($address_components['country_code'])?$address_components['country_code']:'';
		    	$model->postal_code = isset($address_components['postal_code'])?$address_components['postal_code']:'';
		    	$model->formatted_address = isset($address_components['formatted_address'])?$address_components['formatted_address']:'';				
		    	$model->parsed_address = isset($resp['parsed_address'])?json_encode($resp['parsed_address']):'';
		    	$model->save();
		    }
		    
	        return $resp;
	    } catch (Exception $e) {	    	
	    	throw new Exception( $e->getMessage() );
	    }
	}
	
	public static function getLocalDistance($unit='', $lat1='',$lon1='', $lat2='', $lon2='')
    {    	  
    	  if(!is_numeric($lat1)){
    	  	 return 0;
    	  }
    	  if(!is_numeric($lon1)){
    	  	 return false;
    	  }
    	  if(!is_numeric($lat2)){
    	  	 return 0;
    	  }
    	  if(!is_numeric($lon2)){
    	  	 return 0;
    	  }
    	  $theta = $lon1 - $lon2;
    	  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    	 
    	  $dist = acos($dist);
		  $dist = rad2deg($dist);
		  $miles = $dist * 60 * 1.1515;
		  $unit = strtoupper($unit);
		  
		  $resp = 0;
		  			  
		  if ($unit == "KM") {			
		      $resp = ($miles * 1.609344);
		  } else if ($unit == "N") {
		      $resp = ($miles * 0.8684);
		  } else {
		      $resp = $miles;
		  }		  
		  
		  if($resp>0){
		  	 $resp = number_format($resp,2,".","");
		  }
		  
		  return $resp;
    }	
	
    public static function reverseGeocoding($lat='', $lng='')
    {
    	try {    		
    		MapSdk::$map_provider = Yii::app()->params['settings']['map_provider'];		   
			MapSdk::setKeys(array(
			 'google.maps'=>Yii::app()->params['settings']['google_geo_api_key'],
			 'mapbox'=>Yii::app()->params['settings']['mapbox_access_token'],
			));
    		$resp = MapSdk::reverseGeocoding($lat,$lng);
    		return $resp;
    	} catch (Exception $e) {
	    	//return t($e->getMessage());	
	    	throw new Exception( $e->getMessage() );
	    }
    }

	public static function locationDetailsNew($place_id='',$country_code='',$custom_data=array())
	{
		try {
			
			if(empty($place_id)){
				throw new Exception( 'Place ID is empty' );
			}
			
			if(!isset(MapSdk::$map_provider)){
				throw new Exception( 'Map provider not set' );
			}
			
			$resp = array();
			 
			$reference_type = MapSdk::$map_provider;
		    $model = AR_map_places::model()->find('reference_type=:reference_type AND reference_id=:reference_id', 
		    array(':reference_type'=>$reference_type, ':reference_id'=>$place_id )); 
		    if($model){				   
		       $resp = array(
		   	    'address'=>array(
		   	       'address1'=>$model->address1,
					'street_number'=>$model->street_number,
					'city'=>$model->city,
		   	       'address2'=>$model->address2,
		   	       'country'=>$model->country,
		   	       'country_code'=>$model->country_code,
		   	       'postal_code'=>$model->postal_code,
		   	       'formatted_address'=>$model->formatted_address,
		   	    ),
		   	    'latitude'=>$model->latitude,
		   	    'longitude'=>$model->longitude,
		   	    'place_id'=>$model->reference_id,
		   	    'reference'=>$model->reference_id,
				'parsed_address'=>$model->parsed_address,
		   	  );
		   	  		   	
		   	  $reference_id=$model->reference_id;
		    
		    } else {
		    	// If place_id is coordinate format, use reverse geocoding instead of placeDetails
		    	if($is_coordinate_format && $lat !== null && $lng !== null){
		    		$resp = MapSdk::reverseGeocoding($lat, $lng);
		    		// Set reference_id to the coordinate format for consistency
		    		$resp['reference'] = $place_id;
		    		$resp['place_id'] = $place_id;
		    	} else {
		    		$parameters = array();
		    	}
		    	if(MapSdk::$map_provider=="mapbox"){
		    		//  $place_id = isset($custom_data['longitude'])?$custom_data['longitude']:'';
					//  $place_id.=",";
					//  $place_id.=isset($custom_data['latitude'])?$custom_data['latitude']:'';						  
					//  $parameters = array(
					//     'country'=>$country_code,
					//     'types'=>isset($custom_data['place_type'])?$custom_data['place_type']:'',
					//     'limit'=>1,			    
					//  );
		    	}
		    			    	   
			    MapSdk::setMapParameters($parameters);			   			   			  
				   
			    $resp = MapSdk::placeDetails($place_id);				
			   			   
			    $reference_id=isset($resp['reference'])?$resp['reference']:'';
	
			    /*SAVE PLACE DETAILS*/		   
			    $model = new AR_map_places;
			    $address_components = isset($resp['address'])?$resp['address']:'';			   
			   			   
			    $model->reference_type = $reference_type;
			    $model->reference_id = isset($resp['reference'])?$resp['reference']:'';
			    $model->latitude = isset($resp['latitude'])?$resp['latitude']:'';
			    $model->longitude = isset($resp['longitude'])?$resp['longitude']:'';
			    $model->address1 = isset($address_components['address1'])?$address_components['address1']:'';
			    $model->city = isset($address_components['city'])?$address_components['city']:'';
				$model->street_number = isset($address_components['street_number'])?$address_components['street_number']:'';
			    $model->address2 = isset($address_components['street_name'])?$address_components['street_name']:'';
			    $model->country = isset($address_components['country'])?$address_components['country']:'';
			    $model->country_code = isset($address_components['country_code'])?$address_components['country_code']:'';
			    $model->postal_code = isset($address_components['postal_code'])?$address_components['postal_code']:'';
			    $model->formatted_address = isset($address_components['formatted_address'])?$address_components['formatted_address']:'';
			    $model->save();
		    }
		    
	        return $resp;
	    } catch (Exception $e) {	    	
	    	throw new Exception( $e->getMessage() );
	    }
	}	
    
}
/*end class*/