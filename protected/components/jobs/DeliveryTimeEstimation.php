<?php
class DeliveryTimeEstimation 
{
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }
    
    public function execute()
    {        
        try { 

            $logs = '';            
            $order_uuid  = isset($this->data['order_uuid'])?$this->data['order_uuid']:null;
            $language = isset($this->data['language'])?$this->data['language']:null;
            if($language){
                Yii::app()->language = $language;
            } 

            CCacheData::add();
            $order = COrders::get($order_uuid);
            $driver = CDriver::getDriver($order->driver_id);   
            $merchant = CMerchants::get($order->merchant_id);            
            
            $options  = OptionsTools::find([
                'map_provider','google_geo_api_key','mapbox_access_token','yandex_distance_api','default_distance_unit'
            ]);
            $map_provider = isset($options['map_provider'])?$options['map_provider']:'';
            $google_geo_api_key = isset($options['google_geo_api_key'])?$options['google_geo_api_key']:'';
            $mapbox_access_token = isset($options['mapbox_access_token'])?$options['mapbox_access_token']:'';
            $yandex_distance_api = isset($options['yandex_distance_api'])?$options['yandex_distance_api']:'';
            $default_distance_unit = isset($options['default_distance_unit'])?$options['default_distance_unit']:'';

            if(empty($map_provider)){
                return false;
            }
            MapSdk::$map_provider = $map_provider;
			if(MapSdk::$map_provider=="yandex"){			   
				MapSdk::$map_provider = 'mapbox';
			}			
            MapSdk::setKeys(array(
				'google.maps'=>$google_geo_api_key,
				'mapbox'=>$mapbox_access_token,
				'yandex'=>$yandex_distance_api
			));
                        
            $order_meta = COrders::getAttributesAll($order->order_id,[
                'longitude','latitude'
            ]);
            $longitude = isset($order_meta['longitude'])?$order_meta['longitude']:null;
            $latitude = isset($order_meta['latitude'])?$order_meta['latitude']:null;   
                        
            $unit = isset($merchant['distance_unit'])?$merchant['distance_unit']:$default_distance_unit;
            $unit = !empty($unit)?$unit:$default_distance_unit;
            $params_distance = array(
				'from_lat'=>$driver->latitude,
				'from_lng'=>$driver->lontitude,
				'to_lat'=>$latitude,
				'to_lng'=>$longitude,		      
				'unit'=>$unit,
				'mode'=>'driving'
			);		                
            MapSdk::setMapParameters($params_distance);		    
			$distance_resp =  MapSdk::distance();			
			$delivery_time_estimation =  $distance_resp['duration_value'];            
            $order->delivery_time_estimation = $delivery_time_estimation;
            $order->save();

            $jobs = 'Trackorder'; $jobInstance = new $jobs(['order_uuid'=>$order_uuid]); $jobInstance->execute();

        } catch (Exception $e) {                                            
            $logs = $e->getMessage();            
        }        
        //Yii::log( $logs, CLogger::LEVEL_ERROR);
    }
}
// end class