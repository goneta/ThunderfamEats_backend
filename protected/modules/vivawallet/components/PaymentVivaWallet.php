<?php
class PaymentVivaWallet
{
    public static function getUrl($mode='')
	{		
		$api = '';
		if($mode){
			$api = "https://api.vivapayments.com";
		} else $api = "https://demo-api.vivapayments.com";
		return $api;
	}

    public static function getAccountsUrl($mode='')
	{		
		$api = '';
		if($mode){
			$api = "https://accounts.vivapayments.com";
		} else $api = "https://demo-accounts.vivapayments.com";
		return $api;
	}

    public static function getRefundURL($mode='')
	{		
		$api = '';
		if($mode){
			$api = "https://www.vivapayments.com";
		} else $api = "https://demo.vivapayments.com";
		return $api;
	}
    
    public static function getRedirectURL($mode='')
	{		
		$api = '';
		if($mode){
			$api = "https://www.vivapayments.com/web/checkout";
		} else $api = "https://demo.vivapayments.com/web/checkout";
		return $api;
	}

    public static function getTokens($client_id='',$secret='',$is_live=0)
    {
        $api = self::getAccountsUrl($is_live);
        $auth_token = base64_encode("$client_id:$secret");    
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $api.'/connect/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");

        $headers = array();
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        $headers[] = 'Authorization: basic '.$auth_token;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);        
        if (curl_errno($ch)) {
            throw new Exception ( curl_error($ch) );
        }
        curl_close($ch);
        if(!empty($result)){
            if ( $json = json_decode($result,true)){                                                
                if(isset($json['access_token'])){
                    return $json['access_token'];
                } elseif ( isset($json['error']) ){
                    throw new Exception( t( $json['error'] ) );
                } else throw new Exception( t("invalid credentials, please try again later") );
            } else throw new Exception( t("invalid response from api, please try again later") );
        } else throw new Exception( t("empty response from api, please try again later") );
    }

    public static function SmartCheckout($data=array(),$tokens='',$is_live=0)
    {             
        $api = self::getUrl($is_live);        
        $ch = curl_init();        
        curl_setopt_array($ch, array(
            CURLOPT_URL            => "$api/checkout/v2/orders",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_HTTPHEADER     => array(
                "Authorization: Bearer $tokens",
                'Content-Type: application/json'
            ),
        ));
        $result = curl_exec($ch);                
        if (curl_errno($ch)) {
            throw new Exception ( curl_error($ch) );
        }
        curl_close($ch);
        if(!empty($result)){
            if ( $json = json_decode($result,true)){                                                
                if(isset($json['orderCode'])){
                    return $json['orderCode'];
                } elseif ( isset($json['error']) ){
                    throw new Exception( t( $json['error'] ) );
                } elseif ( isset($json['message']) ){   
                    throw new Exception( $json['message'] );
                } else throw new Exception( t("invalid credentials, please try again later") );
            } else throw new Exception( t("invalid response from api, please try again later") );
        } else throw new Exception( t("empty response from api, please try again later") );
    }

    public static function retrieveTransaction($tokens='',$transaction_uuid,$is_live)
    {
        $api = self::getUrl($is_live);
        $api .="/checkout/v2/transactions/".$transaction_uuid;        
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $api);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');


        $headers = array();
        $headers[] = 'Authorization: Bearer '.$tokens;
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception ( curl_error($ch) );
        }
        curl_close($ch);
        if(!empty($result)){
            if ( $json = json_decode($result,true)){                                                                
                if(isset($json['orderCode'])){
                    return $json;
                } elseif ( isset($json['error']) ){
                    throw new Exception( t( $json['error'] ) );
                } else throw new Exception( t("invalid credentials, please try again later") );
            } else throw new Exception( t("invalid response from api, please try again later") );
        } else throw new Exception( t("empty response from api, please try again later") );
    }

    public static function ISVCheckout($data=array(),$merchantID='',$tokens='',$is_live=0)
    {     
        $api = self::getUrl($is_live);        
        $ch = curl_init();        
        curl_setopt_array($ch, array(
            CURLOPT_URL            => "$api/checkout/v2/isv/orders?merchantId=".$merchantID,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_HTTPHEADER     => array(
                "Authorization: Bearer $tokens",
                'Content-Type: application/json'
            ),
        ));
        $result = curl_exec($ch);        
        if (curl_errno($ch)) {
            throw new Exception ( curl_error($ch) );
        }
        curl_close($ch);
        if(!empty($result)){
            if ( $json = json_decode($result,true)){                                                
                if(isset($json['orderCode'])){
                    return $json['orderCode'];
                } elseif ( isset($json['error']) ){
                    throw new Exception( t( $json['error'] ) );
                } else throw new Exception( t("invalid credentials, please try again later") );
            } else throw new Exception( t("invalid response from api, please try again later") );
        } else throw new Exception( t("empty response from api, please try again later") );
    }    

    public static function retrieveISVTransaction($tokens='',$transaction_uuid,$merchantID='',$is_live)
    {
        $api = self::getUrl($is_live);
        $api .="/checkout/v2/isv/transactions/$transaction_uuid?merchantId=$merchantID";  
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $api);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');


        $headers = array();
        $headers[] = 'Authorization: Bearer '.$tokens;
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
                
        if (curl_errno($ch)) {
            throw new Exception ( curl_error($ch) );
        }
        curl_close($ch);
        if(!empty($result)){
            if ( $json = json_decode($result,true)){                                                                
                if(isset($json['orderCode'])){
                    return $json;
                } elseif ( isset($json['error']) ){
                    throw new Exception( t( $json['error'] ) );
                } else throw new Exception( t("invalid credentials, please try again later") );
            } else throw new Exception( t("invalid response from api, please try again later") );
        } else throw new Exception( t("empty response from api, please try again later") );
    }

    public static function refund($tokens='',$transaction_id,$amount='',$source_code='',$is_live)
    {
        $api = self::getRefundURL($is_live);
        $api .="/api/transactions/$transaction_id/?amount=$amount&sourceCode=$source_code";     
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $api);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

        $headers = array();
        $headers[] = 'Authorization: Basic '.$tokens;
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
                
        if (curl_errno($ch)) {
            throw new Exception ( curl_error($ch) );
        }
        curl_close($ch);
        
        if(!empty($result)){
            if ( $json = json_decode($result,true)){                                                                                                          
                if(isset($json['StatusId'])){
                    if($json['StatusId']=="F"){
                        return $json['TransactionId'];
                    } else {
                        if(isset($json['ErrorText'])){                            
                            throw new Exception( t( $json['ErrorText'] ) );                                        
                        }
                    }                     
                } elseif ( isset($json['ErrorText']) ){
                    throw new Exception( t( $json['ErrorText'] ) );
                } else throw new Exception( t("invalid credentials, please try again later") );
            } else throw new Exception( t("invalid response from api, please try again later") );
        } else throw new Exception( t("empty response from api, please try again later") );
    }

    public static function verificationToken($auth_token='',$is_live)
    {
        $api = self::getRefundURL($is_live);
        $api .="/api/messages/config/token";
        $ch = curl_init();        
        curl_setopt_array($ch, array(
            CURLOPT_URL => $api,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
              'Authorization: Basic '.$auth_token
            ),
        ));

        $result = curl_exec($ch);                
        if (curl_errno($ch)) {
            throw new Exception ( curl_error($ch) );
        }
        curl_close($ch);                       
        if(!empty($result)){
            if ( $json = json_decode($result,true)){                 
                if(isset($json['Key'])){
                    return $json;
                } elseif ( isset($json['Message']) ){
                    throw new Exception( t( $json['Message'] ) );
                } else throw new Exception( t("invalid credentials, please try again later") );
            } else throw new Exception( t("invalid response from api, please try again later") );
        } else throw new Exception( t("empty response from api, please try again later") );
    }

}
// end class