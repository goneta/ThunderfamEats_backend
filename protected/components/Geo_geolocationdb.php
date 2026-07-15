<?php
class Geo_geolocationdb {
	
	public static $provider = 'geolocation-db';
	
	public static function fecth($access_key='',$ip='')
	{								
		if(!empty($access_key)){
			$url= "https://geolocation-db.com/json/".$access_key."/$ip";
		} else $url= "https://geolocation-db.com/json/$ip";
		
		if($ip=="127.0.0.1"){
			throw new Exception( "Geolocation does not work in localhost" );
		}
			
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		$result = curl_exec($ch);
		if (curl_errno($ch)) {		    
		    throw new Exception( curl_error($ch) );
		}
		curl_close($ch);		
						
		if(!empty($result)){
			$data = json_decode($result,true);						
			if(is_array($data) && count($data)>=1){
				if(isset($data['country_code'])){						
					if($data['country_code']!="Not found"){
						return $data;
					} else throw new Exception( $data['country_code'] );
				} else{
					if(isset($data['message'])){
						throw new Exception( $data['message'] );
					}
				}
			}
		}		
		throw new Exception( "An error has occured" );
	}
	
}
/*end class*/