<?php
class WebUserMerchant extends CWebUser
{
	const LOGIN_TOKEN="logintoken";
	
	protected function beforeLogin($id,$states,$fromCookie)
	{			
		if(!$fromCookie) {
			return true;
		}
		
		//The cookie isn't here, we refuse the login
		if(!isset($states[self::LOGIN_TOKEN])){
			return false;
		}
					
		$role_id=0;
		$user = AR_merchant_login::model()->findbyPk($id);				

		if($user){
			if($user->main_account!=1){
				$role_id = $user->role;
			}			
			$cookieLogintoken = $states[self::LOGIN_TOKEN];						
			if($cookieLogintoken==$user->session_token) {		
				return true;
			}		
		}				
		return false;		
	}	
}