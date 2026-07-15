<?php 
class AOrderSettings
{
	public static function getStatus($meta_name = array())
	{		
		$not_in_status = array();
		$criteria=new CDbCriteria();
		$criteria->select = "meta_value";
		$criteria->addInCondition('meta_name', $meta_name );
		
		//$models = AR_admin_meta::model()->findAll($criteria);		
		$dependency = CCacheData::dependency();		
		$models = AR_admin_meta::model()->cache(Yii::app()->params->cache, $dependency)->findAll($criteria); 
		if($models){
			foreach ($models as $items) {
				array_push($not_in_status,$items->meta_value);
			}
		}
		return $not_in_status;
	}
	
	public static function getGroup($status='')
	{
		$criteria=new CDbCriteria();
		$criteria->alias = "a";
		$criteria->select = "group_name";
		$criteria->condition = "stats_id IN  (
		  select stats_id from {{order_status}}
		  where description=:status
		)";
		$criteria->params = array(
		  ':status'=>$status
		);
		$models = AR_order_settings_tabs::model()->find($criteria);	
		if($models){
			return $models->group_name;
		}
		throw new Exception( 'no group buttons' );
	}
	
	public static function getPrintSettings()
	{		
		$data = array();	
		try {
			$model = AR_admin_meta::getMetaTranslation(['receipt_thank_you','receipt_footer'],Yii::app()->language);
			if($model){		
				foreach ($model as $item) {				
					$data[$item->meta_name] = $item->meta_value;
				}								
			}
		} catch (Exception $e) {
		}

		$model2 = AR_admin_meta::getMeta(['receipt_logo']);
		if($model2){				
			$logo = isset($model2['receipt_logo']['meta_value'])?$model2['receipt_logo']['meta_value']:'';
			$path = isset($model2['receipt_logo']['meta_value1'])?$model2['receipt_logo']['meta_value1']:'';
			if(!empty($logo)){
				$image = CMedia::getImage($logo,$path,Yii::app()->params->size_image ,CommonUtility::getPlaceholderPhoto('logo') );				
				$data['receipt_logo'] = $image;

				$logo_path = CMedia::homeDir()."/{$path}/{$logo}";
				if(file_exists($logo_path)){
					$type = pathinfo($logo_path, PATHINFO_EXTENSION);
					$data64 = file_get_contents($logo_path);
					$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data64);				
					$data['receipt_logo_base64'] = $base64;
				}				
			}
		}	  

		if(is_array($data) && count($data)>=1){
			return $data;
		}
		return false;
	}

	public static function getTabsGroup($group_name=array())
	{		
		$data = array();
		$criteria = new CDbCriteria;		
		$criteria->select = "group_name,stats_id";
		$criteria->order = "id ASC";
		// $criteria->addCondition('group_name =:group_name');
		// $criteria->params = array(':group_name' => trim($group_name) );
		$criteria->addInCondition('group_name',(array)$group_name);
		$model=AR_order_settings_tabs::model()->findAll($criteria);		
		if($model){				
			foreach ($model as $items) {
				array_push($data,$items->stats_id);
			}
		}
		return $data;
	}

	public static function getTabsGroupStatus($group_name='')
	{
		$data = array();
		$stats_id = self::getTabsGroup($group_name);				
		$criteria = new CDbCriteria;	
		$criteria->select = "description";
		$criteria->addInCondition('stats_id',(array)$stats_id);
		$model = AR_status::model()->findAll($criteria);	
		if($model){
			foreach ($model as $items) {
				$data[]=$items->description;
			}
		}
		return $data;
	}

	public static function getSettingsStatus($meta_name=[])
	{
		if(empty($meta_name)){
			return [];
		}
		$data = [];
		$status = AR_admin_meta::getMeta($meta_name);				
		if(!empty($status)){
			foreach ($status as $items) {
				$data[]=$items['meta_value'];
			}
		}
		return $data;
	}

}
/*end class*/