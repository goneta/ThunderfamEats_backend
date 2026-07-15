<?php
class AdminTools
{	
	public static function displayAdminName()
	{		
		$name = Yii::app()->user->first_name." ".Yii::app()->user->last_name;
		return $name;
	}
	
	public static function getProfilePhoto()
	{								
		$upload_path = CMedia::adminFolder();
		if(isset(Yii::app()->user->avatar_path)){			
			$upload_path = Yii::app()->user->avatar_path;
		}		
		$avatar = CMedia::getImage(Yii::app()->user->avatar,$upload_path,'@thumbnail',
		CommonUtility::getPlaceholderPhoto('customer'));
		return $avatar;
	}
	
	public static function getLogo($filename='')
	{						
		return websiteDomain()."/".Yii::app()->theme->baseUrl."/assets/images/sample-merchant-logo@2x.png";		
	}
	
	public static function getMeta($meta_name='')
	{
		$model = AR_admin_meta::model()->find("meta_name=:meta_name",array(
		  'meta_name'=>$meta_name,		  
		));
		if($model){
			return $model;
		}
		return false;
	}
	
	public static function getPayoutSettings()
     {
     	$options = AR_admin_meta::getMeta(array('payout_request_enabled','payout_minimum_amount'));		
		$payout_request_enabled = isset($options['payout_request_enabled'])?$options['payout_request_enabled']['meta_value']:'';
		$payout_minimum_amount = isset($options['payout_minimum_amount'])?$options['payout_minimum_amount']['meta_value']:'';
		$payout_request_enabled = $payout_request_enabled==1?true:false;		
		return array(
		   'enabled'=>$payout_request_enabled,
		   'minimum_amount'=>$payout_minimum_amount,
		);
     }

	public static function getByUUID($uuid=array())
    {
        $criteria=new CDbCriteria();
        $criteria->addCondition('status=:status');		
        $criteria->params = [
            ':status'=>"active"            
        ];
        $criteria->addInCondition('admin_id_token',(array)$uuid);
        $criteria->order = "first_name ASC";		
        $model = AR_AdminUser::model()->findAll($criteria);		
        if($model){
            $data = [];
            foreach ($model as $items) {
                $photo = CMedia::getImage($items->profile_photo,$items->path,'@thumbnail',
		         CommonUtility::getPlaceholderPhoto('customer'));

                $data[$items->admin_id_token] = [
					'user_type'=>t("Admin"),
					'user_type_raw'=>"admin",
                    'client_id'=>$items->admin_id,
                    'client_uuid'=>$items->admin_id_token,                    
                    'first_name'=>$items->first_name,
                    'last_name'=>$items->last_name,
                    'phone_prefix'=>'',
                    'contact_phone'=>$items->contact_number,  
					'email_address'=>$items->email_address,
                    'photo'=>$items->profile_photo,
                    'photo_url'=>$photo,
					'profile_url'=>''
                ];
            }            
            return $data;
        }
        throw new Exception(HELPER_NO_RESULTS);  
    }

	public static function SearchAdmin($search_string='')
	{
		$data = [];
		$criteria=new CDbCriteria();
		$criteria->condition = "status=:status";
		$criteria->params = [
			':status'=>"active"
		];
		$criteria->addSearchCondition('first_name', $search_string);				
		$model = AR_AdminUser::model()->findAll($criteria);				
		if($model){
			foreach ($model as $items) {				
				$photo = CMedia::getImage($items->profile_photo,$items->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('customer'));
				$data[] = [
					'admin_id'=>$items->admin_id,
					'user_type'=>t("Admin"),
					'user_type_raw'=>"admin",
					'client_id'=>$items->admin_id,
					'client_uuid'=>$items->admin_id_token,                    
					'first_name'=>$items->first_name,
					'last_name'=>$items->last_name,
					'photo'=>$items->profile_photo,
					'photo_url'=>$photo,
				];
			}
			return $data;
		}	
		throw new Exception(HELPER_NO_RESULTS);  
	}
		
}
/*end class*/