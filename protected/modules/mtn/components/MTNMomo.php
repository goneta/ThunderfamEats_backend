<?php
class MTNMomo
{
     public static $apikey = null;
     public static $subsription_key = null;
     public static $is_live = false;
     public static $api_uuid = null;
     public static $key_uuid = 'mtn_uuid';
     public static $key_apikey = 'mtn_apikey';

     private static function generateUuidV4() {        
        $data = random_bytes(16);            
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);                
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);                
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
     }

     public static function setSandbox($is_live=false)
     {
        self::$is_live = $is_live;
     }
     
     public static function getUUID()
     {
        $model = AR_admin_meta::model()->find("meta_name=:meta_name AND meta_value=:meta_value",[
            ':meta_name'=>self::$key_uuid,
            ':meta_value'=>self::$subsription_key
        ]);
        if($model){
            return $model->meta_value1;
        }
        throw new Exception( t("UUID not found") );
     }

     public static function getApikey()
     {
        $model_key = AR_admin_meta::model()->find("meta_name=:meta_name AND meta_value=:meta_value",[
            ':meta_name'=>self::$key_apikey,
            ':meta_value'=>self::$subsription_key
        ]);

        if($model_key){
            return $model_key->meta_value1;
        } else {
            $model = AR_admin_meta::model()->find("meta_name=:meta_name AND meta_value=:meta_value",[
                ':meta_name'=>self::$key_uuid,
                ':meta_value'=>self::$subsription_key
            ]);
            if($model){                        
                try { 
                    $resp = self::generateAPIKey($model->meta_value1);
                    $resp = json_decode($resp,true);
                    $api_key = isset($resp['apiKey'])?$resp['apiKey']:'';
                    $model = new AR_admin_meta();
                    $model->meta_name = self::$key_apikey;
                    $model->meta_value = self::$subsription_key;
                    $model->meta_value1 = $api_key;
                    $model->save();                
                } catch(Exception $e) {
                    throw new Exception($e->getMessage());
                }
            } else {            
                self::generateUUID();
                self::getApikey();
            }
        }        
     }

     public static function generateAPIKey($uuid='')
     {                  
        try { 
            $url =  "https://sandbox.momodeveloper.mtn.com/v1_0/apiuser/$uuid/apikey";      
            if(self::$is_live){
                $url = str_replace("sandbox.","",$url);
            }         
            $data = self::sendRequest($url,'','',201,true);
            return $data;            
        } catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
     }

     public static function generateUUID()
     {
        $subsription_key = self::$subsription_key;
        $uuid = self::generateUuidV4();
        $payment_code = MtnModule::paymentCode();
        $call_back = Yii::app()->createAbsoluteUrl("$payment_code/api/callback");        
        $params = [
            'providerCallbackHost'=>$call_back,
        ];        
        
        try {
            $url = 'https://sandbox.momodeveloper.mtn.com/v1_0/apiuser';
            if(self::$is_live){
                $url = str_replace("sandbox.","",$url);
            }
            self::sendRequest($url,$params,$uuid,201);
            $model = new AR_admin_meta();
            $model->meta_name = self::$key_uuid;
            $model->meta_value = $subsription_key;
            $model->meta_value1 = $uuid;
            $model->save();
            return true;
        } catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
     }

     public static function generateToken($uuid='',$api_key='')
     {
        $payment_code = MtnModule::paymentCode();
        $call_back = Yii::app()->createAbsoluteUrl("$payment_code/api/callback");        
        $params = [
            'providerCallbackHost'=>$call_back,
        ];             
        try {
            $url = 'https://sandbox.momodeveloper.mtn.com/collection/token/';
            if(self::$is_live){
                $url = str_replace("sandbox.","",$url);
            }
            $json = self::sendRequest($url,$params,$uuid,200,true,$api_key);   
            $json = json_decode($json,true);
            $token = isset($json['access_token'])?$json['access_token']:null;
            if($token){
               return $token;
            } else throw new Exception( t("Invalid Token") );
        } catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
     }

     public static function sendRequest($url='',$params=[], $uuid='',$expected_code=201,$with_data=false,$apikey='')
     {

        $httpCode = null; $error= '';
        $subsription_key = self::$subsription_key;

        $ch = curl_init();

        if(!empty($apikey)){
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            curl_setopt($ch, CURLOPT_USERPWD, $uuid . ':' . $apikey);
            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = "Ocp-Apim-Subscription-Key : $subsription_key";
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            if(is_array($params) && count($params)>=1){
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            }        

            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = "X-Reference-Id : $uuid";
            $headers[] = "Ocp-Apim-Subscription-Key : $subsription_key";
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }        

        $result = curl_exec($ch);        
        if (curl_errno($ch)) {            
            $error = t("Error:"). curl_error($ch);
        } else {
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);            
        }
        curl_close($ch);

        if($httpCode==$expected_code){
            return $with_data?$result:true;
        } else {
            if(!empty($error)){
                throw new Exception( $error );
            } else throw new Exception( "An error has occured ".$httpCode);            
        }
     }
     
          
     public static function requestPay($subsription_key='',$params=[])
     {
          try {

            $api_key = null;
            self::$subsription_key = $subsription_key;
            if(!self::$apikey){
                $api_key = self::getApikey();
            }
            $uuid = self::getUUID();
            $token =  self::generateToken($uuid,$api_key);
            $transaction_uuid = self::generateUuidV4();            
            
            $url = "https://sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay";
            if(self::$is_live){
                $url = str_replace("sandbox.","",$url);
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url );
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));

            $environment = self::$is_live?"live":'sandbox';            
            
            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = "Authorization : Bearer $token";
            $headers[] = "X-Reference-Id : $transaction_uuid";            
            $headers[] = "X-Target-Environment : $environment";
            $headers[] = "Ocp-Apim-Subscription-Key : $subsription_key";            
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);                
            $json_results = !empty($result)? json_decode($result,true) : false;

            if (curl_errno($ch)) {                            
                $error = t("Error:"). curl_error($ch);
            } else {
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);                
            }
            curl_close($ch);

            if($httpCode==202){
                return $transaction_uuid;
            } else {
                if(is_array($json_results) && count($json_results)>=1){
                    $message = isset($json_results['message'])?$json_results['message']: $error ;
                    throw new Exception( $message );
                } else {
                    if(!empty($error)){
                        throw new Exception( $error );
                    } else {
                        throw new Exception( self::ErrorDescriptions($httpCode) );            
                    }
                }                
            }

          } catch(Exception $e) {
            throw new Exception($e->getMessage());
          }
     }

     private static function ErrorDescriptions($code='')
     {
        $list[409] = t("Duplicated Reference ID");
        $list[401] = t("Authentication failed.Credentials invalid");        
        $list[404] = t("Reference ID not found.");        
        $list[400] = t("Bad request. Request does not follow the specification.");        
        $list[403] = t("Authorization failed. IP not authorized to utilize Disbursement API.");        
        $list[500] = t("Authorization failed. User does not have permission.The account authenticated with the Request via Token is restricted.");        
        $list[500] = t("Value passed in header X-Target-Environment is incorrect");        
        $list[503] = t("Service temporary unavailable, try again later"); 
        return isset($list[$code])?$list[$code]:"An error has occured ".$code;
     }

     public static function paymentStatus($subsription_key='',$transacion_uuid='')
     {
            try {
                $api_key = null;
                self::$subsription_key = $subsription_key;
                if(!self::$apikey){
                    $api_key = self::getApikey();
                }
                $uuid = self::getUUID();
                $access_token =  self::generateToken($uuid,$api_key);                

                $url = "https://sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay/$transacion_uuid";     
                if(self::$is_live){
                    $url = str_replace("sandbox.","",$url);
                }           

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                $headers = array();
                $headers[] = 'Content-Type: application/json';
                $headers[] = "Authorization : Bearer $access_token";
                $headers[] = "X-Target-Environment : sandbox";
                $headers[] = "Ocp-Apim-Subscription-Key : $subsription_key";     
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);                  

                $result = curl_exec($ch);
                $json_results = !empty($result)? json_decode($result,true) : false;
                
                if (curl_errno($ch)) {                            
                    $error = t("Error:"). curl_error($ch);
                } else {
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);                
                }
                curl_close($ch);
    
                if($httpCode==200){
                    return $json_results;
                } else {
                    if(is_array($json_results) && count($json_results)>=1){
                        $message = isset($json_results['message'])?$json_results['message']: $error ;
                        throw new Exception( $message );
                    } else {
                        if(!empty($error)){
                            throw new Exception( $error );
                        } else throw new Exception( "An error has occured ".$httpCode);            
                    }                
                }

            } catch(Exception $e) {
              throw new Exception($e->getMessage());
            }
     }
}
//