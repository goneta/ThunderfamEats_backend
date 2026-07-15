<?php class CCommission
{
	
	public static function getCommissionValue($merchant_type='',$commision_type='',$merchant_commission=0,$sub_total=0,$total=0)
	{
		$data = array(); $commission = 0; $merchant_earning = 0;
		$merchant_commission_raw = $merchant_commission;
								
		$model = AR_merchant_type::model()->find('type_id=:type_id', 
		array(':type_id'=>$merchant_type)); 		
		if($model){
			
			if($model->based_on=="subtotal"){
				$total_based = $sub_total;	
			} else $total_based = $total;		
						
			if($commision_type=="fixed"){
				$commission = $merchant_commission;
				$merchant_earning = floatval($total_based) - floatval($commission);
			} else if ( $commision_type=='percentage' ) {
				$merchant_commission = floatval($merchant_commission)/100;
				$commission = floatval($total_based) * $merchant_commission;
				$merchant_earning = floatval($total_based) - floatval($commission);
			} else return false;
					
			return array(
				'commission_value'=>$merchant_commission_raw,
				'commission_based'=>$model->based_on,
				'commission'=>floatval($commission),
				'merchant_earning'=>floatval($merchant_earning)
			);
		}	
		return false;
	}

	public static function getCommissionValueNew($data=[])
	{		
						
		$commission = 0; $merchant_earning = 0; $merchant_commission_raw = 0;

		$points_enabled = isset(Yii::app()->params['settings']['points_enabled'])?Yii::app()->params['settings']['points_enabled']:false;
		$points_enabled = $points_enabled==1?true:false;
		$points_cover_cost = isset(Yii::app()->params['settings']['points_cover_cost'])?Yii::app()->params['settings']['points_cover_cost']:'website';
		
		$merchant_id = isset($data['merchant_id'])?intval($data['merchant_id']):'';
		$transaction_type = isset($data['transaction_type'])?$data['transaction_type']:'';
		$merchant_type = isset($data['merchant_type'])?$data['merchant_type']:2;
		$commission_type = isset($data['commision_type'])?$data['commision_type']:'';
		$merchant_commission = isset($data['merchant_commission'])?floatval($data['merchant_commission']):0;
		$sub_total = isset($data['sub_total'])?floatval($data['sub_total']):0;		
		$sub_total_without_cnd = isset($data['sub_total_without_cnd'])?floatval($data['sub_total_without_cnd']):0;
		$total = isset($data['total'])?floatval($data['total']):0;
		$service_fee = isset($data['service_fee'])?floatval($data['service_fee']):0;
		$delivery_fee = isset($data['delivery_fee'])?floatval($data['delivery_fee']):0;
		$tax_settings = isset($data['tax_settings'])?$data['tax_settings']:'';
		$tax_total = isset($data['tax_total'])?floatval($data['tax_total']):0;
		$self_delivery = isset($data['self_delivery'])?$data['self_delivery']:false;
		

		if($new_commission = CMerchants::getCommissionByTransaction($merchant_id,$transaction_type)){						
			$commission_type = $new_commission['commission_type'];
			$merchant_commission = $new_commission['commission'];			
		}
		
		$merchant_commission_raw = $merchant_commission;
		
		if($commission_type=="fixed"){
			$commission = $merchant_commission;
			$merchant_earning = floatval($sub_total) - floatval($commission);
		} else if ( $commission_type=="percentage"){
			$merchant_commission = floatval($merchant_commission)/100;						
			$commission = floatval($sub_total) * $merchant_commission;
			$merchant_earning = floatval($sub_total) - floatval($commission);
		}		
		return array(
			'commission_value'=>$merchant_commission_raw,
			'commission_based'=>'subtotal',				
			'merchant_earning'=>floatval($merchant_earning),
			'commission'=>floatval($commission),
			'commission_type'=>$commission_type
		);
	}
	
}
/*end class*/