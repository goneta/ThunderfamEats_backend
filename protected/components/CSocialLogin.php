<?php
class CSocialLogin{
	
	
	public static function validateAccessToken($access_token='')
	{		
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/me?access_token='.$access_token);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		$result = curl_exec($ch);
		if (curl_errno($ch)) {		    
		    throw new Exception( curl_error($ch) );
		}
		curl_close($ch);
		
		if ($json = json_decode($result,true)){			
			$id = isset($json['id'])?$json['id']:'';
			$error = isset($json['error'])?$json['error']:'';
			if($id>0){
				return true;
			} elseif ( !empty($error)){
				$error = isset($json['error']['message'])?$json['error']['message']:'undefined facebook error';
				throw new Exception( $error );
			}
		}
		throw new Exception( "Undefined facebook response" );
	}

	public static function validateIDToken($id_token='',$client_id='')
	{				
		
		$url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($id_token);
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$response = curl_exec($ch);
		if (curl_errno($ch)) {			
			throw new Exception( curl_error($ch) );
		}
		curl_close($ch);		

		if ($response === false) {
			throw new Exception("Failed to validate ID token");
		}

		$data = json_decode($response, true);

		// Validate audience
		$expectedClientId = $client_id;
		if (!isset($data['aud']) || $data['aud'] !== $expectedClientId) {
			throw new Exception("Invalid audience in ID token");
		}		
		return $data;
	}
}
/*end class*/