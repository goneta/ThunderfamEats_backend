<?php
class CTips{
		
	public static function data($label='name',$tip_type='fixed',$exchange_rate=1)
	{
		$criteria=new CDbCriteria();
		$criteria->condition  = 'meta_name=:meta_name';		
		$criteria->params = array(':meta_name'=>'tips');
		$criteria->order="meta_value ASC";
		$dependency = CCacheData::dependency();		
		$model = AR_admin_meta::model()->cache(Yii::app()->params->cache, $dependency)->findAll($criteria);
		if($model){
			$data = array();
			$data[] = array(
				'value'=>0,
				$label=>t("Not now")
			); 
			foreach ($model as $item) {
				$tip = floatval($item->meta_value);			
				$data[] = array(
				 'value'=>$tip,
				 $label=>$tip_type=="fixed"?Price_Formatter::formatNumber( ($item->meta_value*$exchange_rate) ): Price_Formatter::convertToRaw($item->meta_value,0). "%"
				); 
			}
			$data[] = array(
			  'value'=>'fixed',
			  $label=>t("Other")
			); 
			return $data;
		}		
		throw new Exception( 'no results' );
	}

	public static function List($tip_added=0,$subtotal=0,$tip_type='fixed',$exchange_rate=1)
	{
		$data = [];
		$criteria=new CDbCriteria();
		$criteria->condition  = 'meta_name=:meta_name';		
		$criteria->params = array(':meta_name'=>'tips');
		$criteria->order="meta_value ASC";
		$dependency = CCacheData::dependency();		
		if($model = AR_admin_meta::model()->cache(Yii::app()->params->cache, $dependency)->findAll($criteria)){						
			$tip_added_found = false;
			foreach ($model as $items) {	
				$label = $tip_type=="fixed"?Price_Formatter::formatNumber( ($items->meta_value*$exchange_rate) ): Price_Formatter::convertToRaw($items->meta_value,0). "%";
				if($tip_type=="fixed"){
					$price = ($items->meta_value*$exchange_rate);
				} else $price = ($items->meta_value/100)*$subtotal;
				$data[] = [
					'value'=>$items->meta_value,
					'label'=>$label,
					'price'=>$price,
					'price_pretty'=>Price_Formatter::formatNumber($price),
					'tip_type'=>$tip_type
				];
				if(intval($tip_added)==intval($price)){
					$tip_added_found = true;
				}
			}
			$data[] = array(
				'value'=>'fixed',
				'label'=>$tip_added_found?t("Other"):Price_Formatter::formatNumber($tip_added),
				'price'=>'fixed',
			); 
		}
		return $data;
	}
}
/*end class*/