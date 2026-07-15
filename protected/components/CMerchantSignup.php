<?php
class CMerchantSignup
{
	public static function membershipProgram($lang = KMRS_DEFAULT_LANGUAGE , $filter=array() )
	{		
		
		$and = '';
		if(is_array($filter) && count($filter)>=1){						
			if($in = CommonUtility::arrayToQueryParameters((array)$filter)){
				$and = "AND a.type_id IN ($in)";
			}			
		}

		$criteria = "
		SELECT 
		a.type_id,
		a.type_name as original_type_name , 
		a.description,
		a.commision_type,
		a.commission,
		a.based_on,
		a.commission_data, 
		b.type_name	
		FROM {{merchant_type}} a 
		left JOIN (
		   SELECT type_id,type_name FROM {{merchant_type_translation}} where language=".q($lang)."
		) b 
		on a.type_id = b.type_id
		WHERE a.status='publish'
		$and
		order by a.type_id asc
		";		
		$dependency = CCacheData::dependency();
		$model=AR_merchant_type::model()->cache(Yii::app()->params->cache, $dependency)->findAllBySql($criteria);
		if($model){
			$data = array();
			foreach ($model as $items) {				
				$type_name = !empty($items->type_name)?$items->type_name:$items->original_type_name;		
				$data[] = array(
				  'type_id'=>$items->type_id,
				  'type_name'=>$type_name,
				  'description'=>$type_name,
				  'commision_type'=>$items->commision_type,
				  'commission'=>$items->commission,
				  'based_on'=>$items->based_on,
				  'commission_data'=>!empty($items->commission_data)?json_decode($items->commission_data,true):false
				);
			}
			return $data;
		}
		throw new Exception( 'no memberhisp program' );
	}
	
	public static function get($type_id=0)
	{
		$model = AR_merchant_type::model()->find('type_id=:type_id', 
		array(':type_id'=> intval($type_id) )); 	
		if($model){
			return $model;
		}
		return false;
	}
	
}
/*end class*/