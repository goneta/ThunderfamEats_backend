<?php
class DriverIdentity extends CUserIdentity
{
	const LOGIN_TOKEN="logintoken";	
	private $_id;	
	
	//Must need to add
	public function __construct($username, $password)
	{
		$this->username = $username;
		$this->password = $password;		
	}

	public function authenticate()
	{										
		
        $user=AR_driver_login::model()->find('LOWER(email)=?',array(strtolower($this->username) ));
		
		if($user===null)
			$this->errorCode=self::ERROR_USERNAME_INVALID;
		else if(!$user->validatePassword($this->password))
			$this->errorCode=self::ERROR_PASSWORD_INVALID;
		elseif ($user->status!="active"){			
			$this->errorCode=self::ERROR_UNKNOWN_IDENTITY;
		} else {			
			$this->_id=$user->driver_id;
			$this->username=$user->email;			
			$this->setState('driver_uuid', $user->driver_uuid);
			$this->setState('first_name', $user->first_name);
			$this->setState('last_name', $user->last_name);
			$this->setState('email_address', $user->email);
			$this->setState('phone_prefix', $user->phone_prefix);
            $this->setState('phone', $user->phone);
			$this->setState('notification', $user->notification);
			$this->setState('employment_type', $user->employment_type);
						
			$this->setState('avatar', CMedia::getImage($user->photo,$user->path,Yii::app()->params->size_image_thumbnail,
				CommonUtility::getPlaceholderPhoto('driver'))
			);
			$this->errorCode=self::ERROR_NONE;
			
			if(empty($user->token)){
				$user->token = CommonUtility::generateToken("{{driver}}",'token');	
			}			
			$user->last_seen = CommonUtility::dateNow();
			$user->ip_address = CommonUtility::userIp();
			
			$this->setState(self::LOGIN_TOKEN, $user->token);
						
			$user->save();			
			
		}
		return $this->errorCode==self::ERROR_NONE;
	}
	
	/**
	 * @return integer the ID of the user record
	 */
	public function getId()
	{
		return $this->_id;
	}	
	
}
/*end class*/