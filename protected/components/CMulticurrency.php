<?php
class CMulticurrency
{

    public static function updateRates($provider='',$default_currency='')
    {
        $stmt="
		UPDATE {{currency}} a
		SET exchange_rate = IFNULL((
		   (  
		      select exchange_rate  from {{currency_exchangerate}} 
		      where currency_code = a.currency_code
		      and provider=".q($provider)."
		   )
		   /
		   ( 
		     select exchange_rate  from {{currency_exchangerate}} 
		     where currency_code = ".q($default_currency)."
		     and provider=".q($provider)."
		   )
		),0)
		";        
        if(Yii::app()->db->createCommand($stmt)->query()){		
			return true;
		}
		throw new Exception( "Failed to update rates" );
    }

	public static function clearExchangeTable($provider='',$base_currency='')
    {
        $criteria = new CDbCriteria();
        $criteria->condition = "provider=:provider AND base_currency=:base_currency";
        $criteria->params = [
			':provider'=>$provider,
			':base_currency'=>$base_currency
		];    
        AR_currency_exchangerate::model()->deleteAll($criteria);        
    }

	public static function currencyList()
	{
		$criteria = new CDbCriteria();
        $criteria->condition = "status=:status";
        $criteria->params = [':status'=>'publish'];    
		$criteria->order = "as_default DESC, currency_code ASC";
        if($model = AR_currency::model()->findAll($criteria)){
			$data = [];
			foreach ($model as $items) {
				$data[$items->currency_code] = "$items->description ($items->currency_symbol)";
			}
			return $data;
		}
		throw new Exception( t(HELPER_NO_RESULTS) );
	}	

	public static function getAllmerchantCurrency()
	{
		$model = AR_option::model()->findAll("option_name=:option_name",[
			':option_name'=>'merchant_default_currency'
		]);
		if($model){
			foreach ($model as $items) {
				$data[]=$items->option_value;
			}
			return $data;
		}
		return false;
	}

	public static function getExchangeRate($base_currency='',$to_currency='')
	{
		$exchange_rate = 1;		
		$model = AR_currency_exchangerate::model()->find("base_currency=:base_currency AND currency_code=:currency_code",[
			':base_currency'=>$base_currency,
			':currency_code'=>$to_currency
		]);
		if($model){
			$exchange_rate = $model->exchange_rate>0?$model->exchange_rate:1;
		} else {
			//
		}
		return $exchange_rate;
	}

	public static function getAllExchangeRate()
	{
		$data = [];
		$dependency = CCacheData::dependency();         
		$model = AR_currency_exchangerate::model()->cache(Yii::app()->params->cache, $dependency)->findAll();
		if($model){
			foreach ($model as $items) {
				$keys = $items->base_currency.$items->currency_code;
				$data[$keys] = $items->exchange_rate;
			}
		}
		return $data;		
	}

	public static function getAllCurrency($currency_code='')
	{		
		$dependency = CCacheData::dependency();

		if(empty($currency_code)){		
			$model = AR_currency::model()->cache(Yii::app()->params->cache, $dependency)->findAll("status=:status",[
				':status'=>"publish"
			]);
		} else {			
			$model = AR_currency::model()->cache(Yii::app()->params->cache, $dependency)->findAll("status=:status AND currency_code=:currency_code",[
				':status'=>"publish",
				':currency_code'=>$currency_code
			]);
		}
		if($model){
			$data = [];
			foreach ($model as $items) {
				$spacer = ""; $currency_position = $items->currency_position;
				switch ($items->currency_position) {
					case "left_space":				
						$spacer = " ";
						$currency_position = "left";
					break;
					
					case "right_space":	
						$spacer = " ";
						$currency_position = "right";
						break;				
				}
				$data[$items->currency_code] = [
					'decimals'=>$items->number_decimal,
					'decimal_separator'=>$items->decimal_separator,
					'thousand_separator'=>$items->thousand_separator,
					'position'=>$currency_position,
					'spacer'=>$spacer,
					'currency_symbol'=>$items->currency_symbol,
					'currency_code'=>$items->currency_code,
					'exchange_rate'=>$items->exchange_rate>0?$items->exchange_rate:0,
				];
			}
			return $data;
		}
		return false;
	}

	public static function validateCurrencyByAPI()
	{
		if(DEMO_MODE){
			throw new Exception( "This actions is not allowed in demo" );
		}

		$currency_list = self::currencyList();		

		$options = OptionsTools::find([
		  'multicurrency_apikey'
		]);        
		$apikey = isset($options['multicurrency_apikey'])?$options['multicurrency_apikey']:'';              
		$data = CMulticurrency::getCurrencyByAPI($apikey);              		
		if(is_array($currency_list) && count($currency_list)){
			foreach ($currency_list as $code=>$items) {				
				if(!array_key_exists($code,(array)$data)){
					throw new Exception( t("Currency code {currency_code} is not supported.",['{currency_code}'=>$code]) );
				}
			}
			return $data;
		} else throw new Exception( "No available currency code added in attributes -> currency" );
	}

	public static function getCurrencyByAPI($apikey='')
	{
		$params = [
            'apikey'=>$apikey,        
        ];        

		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.freecurrencyapi.com/v1/currencies?".http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);        
        if (curl_errno($ch)) {            
            throw new Exception( curl_error($ch) );
        }        
        curl_close($ch);
        if(!empty($result)){
            $data = json_decode($result,true);            
            if(isset($data['data'])){                
				$result_data = [];
				foreach ($data['data'] as $items) {
					$result_data[$items['code']] = $items['name_plural'];
				}
				return $result_data;
            } else {
                throw new Exception( $data['message'] );            
            }            
        }
        throw new Exception( "No response from provider" );
	}

	public static function getHidePaymentList($currency_code='')
	{		
		$model  = AR_admin_meta::model()->findAll("meta_name=:meta_name AND meta_value=:meta_value",[
			':meta_name'=>"multicurrency_hide_payment",
			':meta_value'=>$currency_code
		]);
		if($model){
			$data = [];
			foreach ($model as $items) {
				$data[] = $items->meta_value1;
			}
			return $data;
		}
		throw new Exception( HELPER_NO_RESULTS );
	}

	public static function getForceCheckoutCurrency($payment_code='',$currency_code='')
	{
		$model  = AR_admin_meta::model()->find("meta_name=:meta_name AND meta_value=:meta_value AND meta_value1!=:meta_value1",[
			':meta_name'=>"multicurrency_checkout_currency",
			':meta_value'=>$payment_code,
			':meta_value1'=>$currency_code
		]);
		if($model){
			$to_currency = $model->meta_value1;						
			$exchange_rate =CMulticurrency::getExchangeRate($currency_code,$to_currency);
			return [
				'currency_code'=>$currency_code,
				'to_currency'=>$to_currency,
				'exchange_rate'=>$exchange_rate>0?$exchange_rate:1,
			];
		}
		return false;
	}

}
// end class