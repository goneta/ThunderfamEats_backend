<?php
require 'php-jwt/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AppUserIdentity extends CUserIdentity
{   
    public static function getAuthorizationHeader(){
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } else if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER["REDIRECT_HTTP_AUTHORIZATION"]);    
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));            
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }

    public static function getBearerToken() {
        $headers = self::getAuthorizationHeader();        
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    public static function getToken() {
        $headers = self::getAuthorizationHeader();        
        if (!empty($headers)) {
            if (preg_match('/token\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    public static function getMerchantIdentity()
    {
        try {        
                
            $token = self::getBearerToken();                        
            if(empty($token)){
                return false;
            }            

            AppUserIdentity::validateAPIKeys($token);
            
            $jwt_key = new Key(CRON_KEY, 'HS256');
            $decoded = (array) JWT::decode($token, $jwt_key);                          
            if(is_array($decoded) && count($decoded)){            
                $owner = isset($decoded['iss'])?$decoded['iss']:'';
                $merchant_id = isset($decoded['sub'])?$decoded['sub']:0;
                $domain = isset($decoded['aud'])?CommonUtility::removeHttp($decoded['aud']):'';
                $date_issued = isset($decoded['iat'])?$decoded['iat']:'';

                //$domain_from = CommonUtility::removeHttp($_SERVER['HTTP_ORIGIN']);
                $origin = isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];                
                $domain_from = CommonUtility::removeHttp($origin);
                $server_domain = Yii::app()->request->getServerName();

                $merchant = CMerchants::get($merchant_id);                                    
                
                $validated_domain = CommonUtility::validateDomain($domain,$domain_from);                               
                //if($merchant && $validated_domain && $owner==$server_domain ){                           
                if($merchant){                           
                    Yii::app()->merchant->id = $merchant->merchant_id;
                    Yii::app()->merchant->setState('merchant_uuid', $merchant->merchant_uuid);
                    Yii::app()->merchant->setState('status', $merchant->status);
                    Yii::app()->merchant->setState('merchant_type', $merchant->merchant_type);                
                    Yii::app()->merchant->setState('website_url', $domain);
                    return true;
                }                  
            }            
            return false;
        } catch (Exception $e) {
            //dump($e->getMessage()); 
            return false;
        }             
    }

    private static function validateAPIKeys($token='')
    {
        $jwt_token = AttributesTools::JwtTokenID();        
        $dependency = CCacheData::dependency();        
        $model = AR_merchant_meta::model()->cache(Yii::app()->params->cache, $dependency)->find("meta_name=:meta_name AND meta_value=:meta_value",[
            ':meta_name'=>$jwt_token,
            ':meta_value'=>$token
        ]);
        if($model){
            return true;
        }        
        throw new Exception( t("Invalid or API keys not found") ); 
    }

    public static function verifyMerchant()
    {                                        
        $method = Yii::app()->getRequest()->getRequestType();    		                
        if($method=="OPTIONS"){
            return false;
        }                           
        if(self::getMerchantIdentity()){
            return false;
        }  
        return true;
    }

    public static function verifyCustomer()
    {                
        $method = Yii::app()->getRequest()->getRequestType();    		                
        if($method=="OPTIONS"){
            return false;
        }                           
        if(self::getCustomerIdentity()){
            return false;
        }                           
        return true;
    }

    public static function getCustomerIdentity()
    {
        try {

            $jwt_token = self::getToken();             
            if(empty($jwt_token)){
                return false;
            }            
            $jwt_key = new Key(CRON_KEY, 'HS256');
            $decoded = (array) JWT::decode($jwt_token, $jwt_key);             
            if(is_array($decoded) && count($decoded)){                                
                $merchant_id = isset($decoded['sub'])?$decoded['sub']:0; 
                $domain = isset($decoded['aud'])?CommonUtility::removeHttp($decoded['aud']):'';           
                $token = isset($decoded['token'])?$decoded['token']:'';         
                
                $dependency = CCacheData::dependency();
                $user = AR_client::model()->cache(Yii::app()->params->cache, $dependency)->find("token=:token AND status=:status",array(
                  ':token'=>$token,
                  ':status'=>"active",
                ));                    
                if($user){
                    Yii::app()->user->id = $user->client_id;
                    Yii::app()->user->setState('client_uuid', $user->client_uuid);
                    Yii::app()->user->setState('first_name', $user->first_name);
                    Yii::app()->user->setState('last_name', $user->last_name);
                    Yii::app()->user->setState('email_address', $user->email_address);
                    Yii::app()->user->setState('contact_number', $user->contact_phone);                     
                    Yii::app()->user->setState('phone_prefix', $user->phone_prefix); 
                    Yii::app()->user->setState('avatar', CMedia::getImage($user->avatar,$user->path,Yii::app()->params->size_image_thumbnail,
                     CommonUtility::getPlaceholderPhoto('customer'))
                   );                   
                   Yii::app()->user->setState('social_strategy', $user->social_strategy);

                   $merchant = CMerchants::get($merchant_id); 
                   if($merchant){
                        Yii::app()->merchant->id = $merchant->merchant_id;
                        Yii::app()->merchant->setState('merchant_uuid', $merchant->merchant_uuid);
                        Yii::app()->merchant->setState('status', $merchant->status);
                        Yii::app()->merchant->setState('merchant_type', $merchant->merchant_type);                
                        Yii::app()->merchant->setState('website_url', $domain);
                   }
                   
                   return true;
                }
            }
            return false;
        } catch (Exception $e) {            
            return false;
        }         
    }

}
// end class