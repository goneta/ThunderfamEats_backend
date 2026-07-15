<?php
require 'php-jwt/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AppMerchantIdentity extends CUserIdentity
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

    public static function verifyToken()
    {
        $method = Yii::app()->getRequest()->getRequestType();    		                
        if($method=="OPTIONS"){
            return false;
        }                           
        if(self::getIdentity()){            
            return false;
        }  
        return true;
    }

    public static function getIdentity()
    {
        try {

            $token = self::getBearerToken();                                    
            if(empty($token)){
                return false;
            }     
                                        
            self::validateAPIKeys($token);
                        
            $jwt_key = new Key(CRON_KEY, 'HS256');
            $decoded = (array) JWT::decode($token, $jwt_key);
            
            if(is_array($decoded) && count($decoded)){            
                $owner = isset($decoded['iss'])?$decoded['iss']:'';
                $merchant_id = isset($decoded['sub'])?$decoded['sub']:0;                
                $date_issued = isset($decoded['iat'])?$decoded['iat']:'';

                $server_domain = Yii::app()->request->getServerName();  
                $owner = CommonUtility::removeHttp($owner);
                $server_domain = CommonUtility::removeHttp($server_domain);              
                if($owner===$server_domain){                    
                    return true;
                }                
            }            
            return false;

        } catch (Exception $e) {            
            return false;
        }        
    }

    private static function validateAPIKeys($token='')
    {
        $jwt_token = AttributesTools::JwtMerchantTokenID();                    
        $dependency = CCacheData::dependency();        
        $model = AR_option::model()->cache(Yii::app()->params->cache, $dependency)->find("merchant_id=:merchant_id AND 
         option_name=:option_name AND option_value=:option_value",[
            ':merchant_id'=>0,
            ':option_name'=>$jwt_token,
            ':option_value'=>$token
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
                $owner = isset($decoded['iss'])?$decoded['iss']:'';
                $merchant_id = isset($decoded['sub'])?$decoded['sub']:0;                
                $date_issued = isset($decoded['iat'])?$decoded['iat']:'';
                $token = isset($decoded['token'])?$decoded['token']:'';
                
                $server_domain = Yii::app()->request->getServerName();                      
                if($owner!=$server_domain){                    
                    return false;
                } 
                
                                
                $dependency = CCacheData::dependency();
                $user = AR_merchant_user::model()->cache(Yii::app()->params->cache, $dependency)->find("session_token=:session_token AND status=:status",array(
                  ':session_token'=>$token,
                  ':status'=>"active"
                ));                                    
                if($user){      
                   $merchant = CMerchants::get($user->merchant_id);
                   if( $merchant->status=='active' || $merchant->status=='expired') {
                       Yii::app()->merchant->id = $user->merchant_user_id;
                       Yii::app()->merchant->setState('username', $user->username);
                       Yii::app()->merchant->setState('merchant_id',$user->merchant_id);
                       Yii::app()->merchant->setState('merchant_uuid',$merchant->merchant_uuid);
                       Yii::app()->merchant->setState('status',$merchant->status);
                       Yii::app()->merchant->setState('merchant_type',$merchant->merchant_type);
                       Yii::app()->merchant->setState('restaurant_slug',$merchant->restaurant_slug);

                       Yii::app()->merchant->setState('first_name',$user->first_name);
                       Yii::app()->merchant->setState('last_name',$user->last_name);
                       Yii::app()->merchant->setState('email_address',$user->contact_email);
                       Yii::app()->merchant->setState('contact_number',$user->contact_number);                       
                       Yii::app()->merchant->setState('avatar', CMedia::getImage($user->profile_photo,$user->path,Yii::app()->params->size_image_thumbnail,
                        CommonUtility::getPlaceholderPhoto('customer'))
                       );                                                             
                       Yii::app()->merchant->setState('main_account',$user->main_account);
                       Yii::app()->merchant->setState('role_id',$user->role);
                       Yii::app()->merchant->setState('logintoken',$user->session_token);

                       return true;
                   }                   
                }
            }
            return false;
        } catch (Exception $e) {            
            return false;
        }         
    }

}
// end class