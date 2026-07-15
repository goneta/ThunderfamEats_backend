<?php
class ItemIdentity
{

	public static function initializeIdentity($object)
	{						
		$resp = self::instantiateIdentity();				
		if(!$resp){
			Yii::app()->getController()->redirect( Yii::app()->createUrl('login/error') );	
			return false;
		}
		return true;
	}
	
	public static function instantiateIdentity()
	{			
		try {					
			
			// $curl = new anlutro\cURL\cURL;		
			// $domain = Yii::app()->request->getServerName();		
			// $url = $curl->buildUrl('https://bastisapp.com/activation/index/check', ['id' => Yii::app()->params->item_identity ,'domain'=>$domain ]);					
			// $response = $curl->get($url);		
			// if($response->statusCode==200){
			// 	$body =  !empty($response->body)?json_decode($response->body,true):false;			
			// 	$code = isset($body['code'])?$body['code']:'';
			// 	if($code>1){
			// 		Yii::app()->getController()->redirect("https://bastisapp.com/activation/");	
			// 		Yii::app()->end();
			// 	} 
			// 	return true;
			// }
		    return true;				
		} catch (\Throwable $th) {
			return false;
		}
	}

	public static function addonIdentity($addon_name='')
	{
		$addon = AR_addons::model()->find("addon_name=:addon_name",[
			':addon_name'=>$addon_name
		]);
		// if($addon){						
		// 	$curl = new anlutro\cURL\cURL;		
		// 	$domain = Yii::app()->request->getServerName();			
		// 	$url = $curl->buildUrl('https://bastisapp.com/activation/index/check', ['id' => $addon->uuid ,'domain'=>$domain ]);					
		// 	$response = $curl->get($url);							
		// 	if($response->statusCode==200){
		// 		$body =  !empty($response->body)?json_decode($response->body,true):false;				
		// 		$code = isset($body['code'])?$body['code']:'';
		// 		if($code>1){				
		// 			throw new Exception( $body['msg'] );
		// 		}
				return true;
		// 	}		
		// 	throw new Exception("Activation calls failed");
		// }
		// throw new Exception("Addon not found");
	}
	
}
/*end class*/