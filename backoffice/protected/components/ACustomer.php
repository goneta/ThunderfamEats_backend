<?php
class ACustomer{
	
	public static function get($client_id=0)
	{
		$model = AR_client::model()->find("client_id=:client_id",array(
		  ':client_id'=>intval($client_id)
		));
		if($model){
			return $model;
		}
		throw new Exception( 'customer id not found' );
	}

	public static function getUUID($client_uuid=0)
	{
		$model = AR_client::model()->find("client_uuid=:client_uuid",array(
		  ':client_uuid'=>intval($client_uuid)
		));
		if($model){
			return $model;
		}
		throw new Exception( 'customer id not found' );
	}
	
	public static function getAddresses($client_id='')
	{
		$model = AR_client_address::model()->findAll("client_id=:client_id",array(
		 ':client_id'=>intval($client_id)
		));
		if($model){
			$data = array();
			foreach ($model as $items) {
				$data[]=array(
				  'address'=>$items->formatted_address,
				  'latitude'=>$items->latitude,
				  'longitude'=>$items->longitude,
				  'direction'=>"https://www.google.com/maps/dir/?api=1&destination=$items->latitude,$items->longitude"
				);
			}
			return $data;
		}
		throw new Exception( 'customer address not found' );
	}
	
	public static function isBlockFromOrdering($client_id=0, $merchant_id=0)
	{
		$meta_name = 'block_customer';
		$model = AR_merchant_meta::model()->find("merchant_id=:merchant_id AND 
			meta_name=:meta_name AND meta_value=:meta_value",array(
			 ':merchant_id'=>intval($merchant_id),
			 ':meta_name'=>$meta_name,
			 ':meta_value'=>$client_id
			));
		if($model){
			return true;
		}
		return false;
	}
		
	public static function getOrdersTotal($client_id = 0, $merchant_id=0, $status=array(), $not_in_status=array() )
	{				
		$criteria=new CDbCriteria();
	    $criteria->select="order_id,order_uuid,total,status";
	    
	    if($merchant_id>0){
		    $criteria->condition = "merchant_id=:merchant_id AND client_id=:client_id ";		    
			$criteria->params  = array(
			  ':merchant_id'=>intval($merchant_id),
			  ':client_id'=>intval($client_id)
			);
	    } else {	    	
	    	$criteria->condition = "client_id=:client_id ";		    
			$criteria->params  = array(			  
			  ':client_id'=>intval($client_id)
			);
	    }	    
		if(is_array($status) && count($status)>=1){		
			$criteria->addInCondition('status', (array) $status );
		}
		if(is_array($not_in_status) && count($not_in_status)>=1){			
			$criteria->addNotInCondition('status', (array) $not_in_status );
		}		
		$count = AR_ordernew::model()->count($criteria); 
		if($count){
			return $count;
		}
		return 0;
	}
	
	public static function getOrderSummary($client_id = 0, $merchant_id=0, $status=array())
	{
		$criteria=new CDbCriteria();
		$criteria->select="sum(total) as total";
		if($merchant_id>0){
			$criteria->condition = "merchant_id=:merchant_id AND client_id=:client_id ";		    
			$criteria->params  = array(
			  ':merchant_id'=>intval($merchant_id),
			  ':client_id'=>intval($client_id)
			);
		} else {
			$criteria->condition = "client_id=:client_id ";		    
			$criteria->params  = array(			  
			  ':client_id'=>intval($client_id)
			);
		}
		$criteria->addInCondition('status', (array) $status );		
		$model = AR_ordernew::model()->find($criteria); 
		if($model){
			return $model->total;
		}
		return 0;
	}
	
	public static function getOrderRefundSummary($client_id = 0, $merchant_id=0, $status=array())
	{
		$criteria=new CDbCriteria();
		$criteria->select="sum(trans_amount) as total";
		if($merchant_id>0){
			$criteria->condition = "merchant_id=:merchant_id AND client_id=:client_id AND status=:status";		    
			$criteria->params  = array(
			  ':merchant_id'=>intval($merchant_id),
			  ':client_id'=>intval($client_id),
			  ':status'=>"paid"
			);
		} else {
			$criteria->condition = "client_id=:client_id AND status=:status";		    
			$criteria->params  = array(			  
			  ':client_id'=>intval($client_id),
			  ':status'=>"paid"
			);
		}
		$criteria->addInCondition('transaction_name', (array) $status );
		$model = AR_ordernew_transaction::model()->find($criteria); 
		if($model){
			return $model->total;
		}
		return 0;
	}

	public static function getByIDS($client_id=array())
    {
        $criteria=new CDbCriteria();
        $criteria->addCondition('status=:status');		
        $criteria->params = [
            ':status'=>"active"            
        ];
        $criteria->addInCondition('client_id',(array)$client_id);
        $criteria->order = "first_name ASC";
        $model = AR_client::model()->findAll($criteria);
        if($model){
            $data = [];
            foreach ($model as $items) {
                $photo = CMedia::getImage($items->avatar,$items->path,'@thumbnail',
		         CommonUtility::getPlaceholderPhoto('customer'));

                $data[$items->client_id] = [
                    'client_id'=>$items->client_id,
                    'client_uuid'=>$items->client_uuid,                    
                    'first_name'=>$items->first_name,
                    'last_name'=>$items->last_name,
                    'phone_prefix'=>$items->phone_prefix,
                    'contact_phone'=>$items->contact_phone,                    
                    'photo'=>$items->avatar,
                    'photo_url'=>$photo,
                ];
            }            
            return $data;
        }
        throw new Exception(HELPER_NO_RESULTS);  
    }

	public static function searchByName($name='')
	{
		$stmt ="
		SELECT client_id FROM {{client}}
		WHERE 1
		AND ( first_name LIKE ".q("%$name%")."  OR last_name LIKE ".q("%$name%").") 
		";
		if($res = CCacheData::queryAll($stmt)){
			$data = [];
			foreach ($res as $items) {
				$data[]=$items['client_id'];
			}
			return $data;
		}
		return false;
	}

	public static function getByUUID($uuid=array(),$main_user_type='')
    {
        $criteria=new CDbCriteria();
        $criteria->addCondition('status=:status');		
        $criteria->params = [
            ':status'=>"active"            
        ];
        $criteria->addInCondition('client_uuid',(array)$uuid);
        $criteria->order = "first_name ASC";
        $model = AR_client::model()->findAll($criteria);
        if($model){
            $data = [];
            foreach ($model as $items) {
                $photo = CMedia::getImage($items->avatar,$items->path,'@thumbnail',
		         CommonUtility::getPlaceholderPhoto('customer'));

				if($main_user_type=="merchant"){
					$profile_url = Yii::app()->createAbsoluteUrl(BACKOFFICE_FOLDER."/customer/view",[
						'id'=>$items->client_uuid
					]);
				} else $profile_url = Yii::app()->createAbsoluteUrl(BACKOFFICE_FOLDER."/buyer/customer_update",[
					'id'=>$items->client_id
				]);
				
                $data[$items->client_uuid] = [
					'user_type'=>t("Customer"),
					'user_type_raw'=>"customer",
                    'client_id'=>$items->client_id,
                    'client_uuid'=>$items->client_uuid,                    
                    'first_name'=>$items->first_name,
                    'last_name'=>$items->last_name,
                    'phone_prefix'=>$items->phone_prefix,
                    'contact_phone'=>$items->contact_phone, 
					'email_address'=>$items->email_address, 
                    'photo'=>$items->avatar,
                    'photo_url'=>$photo,
					'profile_url'=>$profile_url
                ];
            }            
            return $data;
        }
        throw new Exception(HELPER_NO_RESULTS);  
    }

	public static function SearchCustomer($search_string='')
	{
		$data = [];
		$criteria=new CDbCriteria();
		$criteria->condition = "merchant_id=0 AND status=:status";
		$criteria->params = [
			':status'=>"active"
		];
		$criteria->addSearchCondition('first_name', $search_string);
		$criteria->addSearchCondition('last_name', $search_string , true,"OR" );                
		$model = AR_client::model()->findAll($criteria);
		if($model){
			foreach ($model as $items) {
				$photo = CMedia::getImage($items->avatar,$items->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('customer'));
				$data[] = [
					'merchant_id'=>$items->merchant_id,
					'user_type'=>t("Customer"),
					'user_type_raw'=>"customer",
					'client_id'=>$items->client_id,
					'client_uuid'=>$items->client_uuid,                    
					'first_name'=>$items->first_name,
					'last_name'=>$items->last_name,
					'photo'=>$items->avatar,
					'photo_url'=>$photo,
				];
			}
			return $data;
		}	
		throw new Exception(HELPER_NO_RESULTS);  
	}

	public static function validateUserStatus()
	{		
		$user = AR_client::model()->findByPk(Yii::app()->user->id);
		if($user){					
			return $user->status=='active'?true:false;
		}
		return false;
	}

	public static function checkEmailExists($email_address='')
	{
		$model = AR_client::model()->find("email_address=:email_address AND status=:status AND account_verified=:account_verified ",[
			':email_address'=>trim($email_address),
			':status'=>'pending',
			':account_verified'=>0
		]);
		if($model){
			return $model;
		}
		return false;
	}

	public static function SendCompleteRegistration($client_uuid='')
	{
		Yii::import('ext.runactions.components.ERunActions');	
		$cron_key = CommonUtility::getCronKey();		
		$get_params = array( 
		   'client_uuid'=> $client_uuid,
		   'key'=>$cron_key,
		   'language'=>Yii::app()->language,
		   'time'=>time()
		);				
		CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/SendCompleteRegistration?".http_build_query($get_params) );	
	}

	public static function saveMeta($client_id=0,$params=array(),$meta_name='')
	{			
		Yii::app()->db->createCommand("DELETE FROM {{client_meta}}
		WHERE client_id=".q($client_id)."
		AND meta1=".q($meta_name)."
		")->query();
		if($client_id>0 && is_array($params) && count($params)>=1){
			foreach ($params as $id) {
				$params = array(
				  'client_id'=>(integer)$client_id,
				  'meta1'=>trim($meta_name),
				  'meta2'=>trim($id)
				);
				Yii::app()->db->createCommand()->insert("{{client_meta}}",$params);
			}
		}
	}
	
	public static function getMeta($client_id=0,$meta_name='')
	{
		$stmt="
		SELECT meta2
		FROM {{client_meta}}
		WHERE client_id = ".q($client_id)."
		AND 
		meta1 = ".q($meta_name)."
		ORDER BY id ASC
		";
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
			$data = array();
			foreach ($res as $val) {
				$data[]=$val['meta2'];
			}
			return $data;
		}
		return false;
	}

	public static function getBlockPaymentmethod($client_id=0,$meta_name='block_payment_method')
	{
		$data = [];
		$model = AR_client_meta::model()->findAll("client_id=:client_id AND meta1=:meta1",[
			':client_id'=>$client_id,
			':meta1'=>$meta_name
		]);
		if($model){			
			foreach ($model as $items) {
				$data[] = $items->meta2;
			}
		}
		return $data;
	}

	public static function merchantCustomer($merchant_id=0,$limit = 10)
	{
		$data = array();			
		$not_in_status = AOrderSettings::getStatus(array('status_cancel_order','status_rejection'));

		$criteria=new CDbCriteria();
		$criteria->alias = "a";
		$criteria->select="a.client_id, count(*) as total_sold,
		b.first_name,b.last_name,b.date_created, b.avatar as logo, b.path
		";
		$criteria->join='LEFT JOIN {{client}} b on  a.client_id=b.client_id ';
		$criteria->condition = "a.merchant_id=:merchant_id and b.client_id IS NOT NULL";
		$criteria->params = array(':merchant_id'=>$merchant_id);

		if(is_array($not_in_status) && count($not_in_status)>=1){
			//$criteria->addNotInCondition('a.status', (array) $not_in_status );
		}

		$criteria->group="a.client_id";
		$criteria->order = "count(*) DESC";
		$criteria->limit = intval($limit);		
			
		if($model = AR_ordernew::model()->findAll($criteria)){			
			foreach ($model as $item) {
				$total_sold = number_format($item->total_sold,0,'',',');
				$data[] = array(
					'client_id'=>$item->client_id,
					'first_name'=>$item->first_name,
					'last_name'=>$item->last_name,
					'total_sold'=>t("{{total_sold}} orders", array('{{total_sold}}'=>$total_sold) ),
					'member_since'=> t("Member since {{date_created}}" , array('{{date_created}}'=>Date_Formatter::dateTime($item->date_created)) ),
					'image_url'=>CMedia::getImage($item->logo,$item->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('customer')),
				);
			}
			return $data;
		} 
		return null;
	}

	public static function getTodayCount($merchant_id=0)
    {
       $criteria = new CDbCriteria;        
		$criteria->alias = "customer";
		$criteria->addCondition("DATE(customer.date_created) = CURDATE() AND ordernew.merchant_id=:merchant_id ");
		$criteria->params = [
			':merchant_id'=>$merchant_id
		];
		$criteria->join = 'LEFT JOIN {{ordernew}} ordernew ON customer.client_id = ordernew.client_id';		
		$count = AR_client::model()->count($criteria);		
		return intval($count);
    }

	public static function getLastWeekCount($merchant_id=0)
    {
       $criteria = new CDbCriteria;        
		$criteria->alias = "customer";
		$criteria->addCondition("DATE(customer.date_created) =  CURDATE() - INTERVAL 7 DAY AND ordernew.merchant_id=:merchant_id ");
		$criteria->params = [
			':merchant_id'=>$merchant_id
		];
		$criteria->join = 'LEFT JOIN {{ordernew}} ordernew ON customer.client_id = ordernew.client_id';		
		$count = AR_client::model()->count($criteria);		
		return intval($count);
    }

	public static function customerInsights($merchant_id=0)
	{
		$today = self::getTodayCount($merchant_id);
		$lastWeek = self::getLastWeekCount($merchant_id);

		if ($lastWeek == 0) {
			$trend = $today > 0 ? 100 : 0; 
		} else {
			$trend = round((($today - $lastWeek) / $lastWeek) * 100, 1);
		}
		
		if ($trend > 0) {
			$trendLabel = "increase";
		} elseif ($trend < 0) {
			$trendLabel = "decrease";
		} else {
			$trendLabel = "nochange";
		}

		return [
			'today'       => $today,
			'trend'       => $trend,
			'trend_label' => $trendLabel
		];
	}

	public static function TotalSalesInsight($merchant_id=0)
	{
		$db = Yii::app()->db;
		$status = AOrderSettings::getStatus(array('status_cancel_order','status_rejection'));
		$sql = "
			SELECT SUM(o.total) AS total_spend
			FROM st_ordernew o
			WHERE o.merchant_id = :merchant_id
			AND YEARWEEK(o.date_created, 1) = YEARWEEK(CURDATE(), 1)
			AND o.status NOT IN (".CommonUtility::arrayToQueryParameters($status).")
		";		
		$thisWeekTotal =  (float) Yii::app()->db->createCommand($sql)
			->queryScalar(
				[':merchant_id' => $merchant_id]);		

		$lastWeekSql = "
            SELECT SUM(o.total) as total_spend
            FROM {{ordernew}} o
            WHERE o.merchant_id=:merchant_id
            AND YEARWEEK(o.date_created, 1) = YEARWEEK(CURDATE(), 1) - 1
        ";
        $lastWeekTotal = (float)$db->createCommand($lastWeekSql)
            ->queryScalar([':merchant_id'=>$merchant_id]);
		
		$trend = 0;
        if ($lastWeekTotal > 0) {
            $trend = round((($thisWeekTotal - $lastWeekTotal) / $lastWeekTotal) * 100, 1);
        }
			
		if ($trend > 0) {
			$trendLabel = "increase";
		} elseif ($trend < 0) {
			$trendLabel = "decrease";
		} else {
			$trendLabel = "nochange";
		}
		
		return [
			'total'=>CommonUtility::formatShortNumber($thisWeekTotal,1),
			'trend'=>CommonUtility::formatTrend($trend),
			'trendLabel'=>$trendLabel
		];	
	}

	
}
/*end class*/