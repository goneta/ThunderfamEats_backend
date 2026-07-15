<?php
class CClientAddress
{
	
	public static function delete($client_id='', $address_uuid='')
	{
		$model = AR_client_address::model()->find('address_uuid=:address_uuid AND client_id=:client_id', 
		array(':address_uuid'=>$address_uuid,'client_id'=>$client_id)); 
		if($model){
			$model->delete();
			return true;
		}
		throw new Exception( 'Address not found' );
	}
	
	public static function find($client_id='', $address_uuid='')
	{
		$model = AR_client_address::model()->find('address_uuid=:address_uuid AND client_id=:client_id', 
		array(':address_uuid'=>$address_uuid,'client_id'=>$client_id)); 
		if($model){			
			return array(
			   'address_uuid'=>$model->address_uuid,
			   'address' => array(
			     'address1'=>$model->address1,
				 'street_number'=>$model->street_number,
			     'address2'=>$model->address2,
			     'city'=>$model->city,
			     'country'=>$model->country,
			     'country_code'=>$model->country_code,
			     'postal_code'=>$model->postal_code,
			     'formatted_address'=>$model->formatted_address,
				 'formattedAddress'=>$model->formattedAddress,
				 'company'=>$model->company,
			   ),
			   'latitude'=>$model->latitude,
			   'longitude'=>$model->longitude,
			   'place_id'=>$model->place_id,
			   'reference'=>$model->place_id,
			   'attributes'=>array(
			     'location_name'=>$model->location_name,
			     'delivery_options'=>$model->delivery_options,
			     'delivery_instructions'=>$model->delivery_instructions,
			     'address_label'=>$model->address_label,
				 'custom_field1'=>$model->custom_field1,
				 'custom_field2'=>$model->custom_field2,
			   )
			);
		}
		throw new Exception( 'Address not found' );
	}
	
	public static function getAddress($place_id='',$client_id='')
	{
		$model = AR_client_address::model()->find('place_id=:place_id AND client_id=:client_id', 
		array(':place_id'=>$place_id,'client_id'=>$client_id)); 
		if($model){			
			$complete_delivery_address = '';
			if($model->address_format_use==2){
				$complete_delivery_address = "$model->address1 $model->formatted_address";
				if(!empty($model->location_name)){
					$complete_delivery_address.=", $model->location_name";
				}
				if(!empty($model->address2)){
					$complete_delivery_address.=", $model->address2";
				}
				if(!empty($model->city)){
					$complete_delivery_address.=", $model->city";
				}
				if(!empty($model->postal_code)){
					$complete_delivery_address.=", $model->postal_code";
				}
				if(!empty($model->company)){
					$complete_delivery_address.=", $model->company";
				}								
			} else {
				$complete_delivery_address = "$model->address1 $model->formatted_address";
			}
			return array(
			   'address_uuid'=>$model->address_uuid,
			   'address' => array(
			     'address1'=>$model->address1,
				 'street_number'=>$model->street_number,
			     'address2'=>$model->address2,
			     'country'=>$model->country,
			     'city'=>$model->city,
			     'country_code'=>$model->country_code,
			     'postal_code'=>$model->postal_code,
			     'formatted_address'=>$model->formatted_address,
				 'formattedAddress'=> !empty($model->formattedAddress) ? $model->formattedAddress :  $model->formatted_address,
				 'company'=>$model->company,
				 'address_format_use'=>$model->address_format_use,
				 'complete_delivery_address'=>$complete_delivery_address
			   ),
			   'latitude'=>$model->latitude,
			   'longitude'=>$model->longitude,
			   'place_id'=>$model->place_id,
			   'reference'=>$model->place_id,
			   'attributes'=>array(
			     'location_name'=>$model->location_name,
			     'delivery_options'=>$model->delivery_options,
			     'delivery_instructions'=>$model->delivery_instructions,
			     'address_label'=>$model->address_label,
				 'custom_field1'=>$model->custom_field1,
				 'custom_field2'=>$model->custom_field2,
			   )
			);
		} else throw new Exception( 'Address not found' );
	}
		
	public static function getAddresses($client_id='')
	{
		$data = array();
		$model = AR_client_address::model()->findAll('client_id=:client_id AND address_type=:address_type order by address_id DESC',[
			':client_id'=>$client_id,
			':address_type'=>"map-based"
		]); 
		if($model){
			foreach ($model as $val) {
				$complete_delivery_address = '';
				if($val->address_format_use==2){
					$complete_delivery_address = "$val->address1 $val->formatted_address";
					if(!empty($val->location_name)){
						$complete_delivery_address.=", $val->location_name";
					}
					if(!empty($val->address2)){
						$complete_delivery_address.=", $val->address2";
					}
					if(!empty($val->city)){
						$complete_delivery_address.=", $val->city";
					}
					if(!empty($val->postal_code)){
						$complete_delivery_address.=", $val->postal_code";
					}
					if(!empty($val->company)){
						$complete_delivery_address.=", $val->company";
					}								
				} else {
					$complete_delivery_address = "$val->address1 $val->formatted_address";
				}			
				$data[]=array(
				  'address_uuid'=>$val->address_uuid,
				  'address'=>array(
				    'address1'=>$val->address1,
					'street_number'=>$val->street_number,
				    'address2'=>$val->address2,
				    'country'=>$val->country,
				    'city'=>$val->city,
				    'country_code'=>$val->country_code,
				    'postal_code'=>$val->postal_code,
				    'formatted_address'=> $val->formatted_address,
					'formattedAddress'=>!empty($val->formattedAddress)?$val->formattedAddress:$complete_delivery_address,
					'company'=>$val->company,
					'complete_delivery_address'=>$complete_delivery_address
				  ),
				  'latitude'=>$val->latitude,
				  'longitude'=>$val->longitude,
				  'place_id'=>$val->place_id,
				  'reference'=>$val->place_id,
				  'parsed_address'=>array(
					'city'=>$val->city,
					'street_number'=>$val->street_number,
				    'postal_code'=>$val->postal_code,
				    'street_name'=>$val->address1,
				  ),
				  'attributes'=>array(
				    'location_name'=>$val->location_name,
				    'delivery_options'=>$val->delivery_options,
				    'delivery_instructions'=>$val->delivery_instructions,
				    'address_label'=>t($val->address_label),
					'custom_field1'=>t($val->custom_field1),
					'custom_field2'=>t($val->custom_field2),
				  )
				);
			}
			return $data;
		}
		return false;
	}

	public static function countAddress($client_id='')
	{
		$criteria=new CDbCriteria();	    
	    $criteria->condition = "client_id=:client_id";
		$criteria->params  = array(
		  ':client_id'=>intval($client_id),		  		  
		);		
		$count = AR_client_address::model()->count($criteria); 		
		return intval($count);
	}
	
	public static function getFirstAddress($client_id='')
	{		
		$criteria=new CDbCriteria();	    
		$criteria->condition = "client_id=:client_id";
		$criteria->params  = array(
		  ':client_id'=>intval($client_id),		  		  
		);		
		$criteria->order = "date_created DESC";
		$model = AR_client_address::model()->find($criteria); 		
		if($model){
			return array(
			   'address_uuid'=>$model->address_uuid,
			   'address' => array(
			     'address1'=>$model->address1,
				 'street_number'=>$model->street_number,
			     'address2'=>$model->address2,
			     'country'=>$model->country,
			     'city'=>$model->city,
			     'country_code'=>$model->country_code,
			     'postal_code'=>$model->postal_code,
			     'formatted_address'=>$model->formatted_address,
			   ),
			   'latitude'=>$model->latitude,
			   'longitude'=>$model->longitude,
			   'place_id'=>$model->place_id,
			   'reference'=>$model->place_id,
			   'attributes'=>array(
			     'location_name'=>$model->location_name,
			     'delivery_options'=>$model->delivery_options,
			     'delivery_instructions'=>$model->delivery_instructions,
			     'address_label'=>$model->address_label,
			   )
			);
		} else throw new Exception( 'Address not found' );
	}

	public static function getAddressByUUID($client_id=0, $address_uuid='',$address_type='map-based')
	{
		$model = AR_client_address::model()->find("client_id=:client_id AND address_uuid=:address_uuid AND  address_type=:address_type",[
			':client_id'=>$client_id,
			':address_uuid'=>$address_uuid,			
			':address_type'=>$address_type
		]);
		if($model){		
			return [
				'address_uuid'=>$model->address_uuid,
				'street_number'=>$model->address1,
				'street_name'=>$model->formatted_address,
				'city'=>$model->city,
				'state'=>$model->state,
				'postal_code'=>$model->postal_code,
				'location_name'=>$model->location_name,
				'country'=>$model->country,
				'formatted_address'=>$model->formattedAddress,
				'address_label'=>$model->address_label,
				'latitude'=>$model->latitude,
				'longitude'=>$model->longitude,
				'place_id'=>$model->place_id,
				'delivery_instructions'=>$model->delivery_instructions,
				'delivery_options'=>$model->delivery_options,
			];
		}
		return false;
	}

	public static function getAddressByCoordinates($client_id=0, $lat='', $lng='', $address_type='map-based')
	{
		$model = AR_client_address::model()->find("client_id=:client_id AND latitude=:latitude AND longitude=:longitude AND address_type=:address_type",[
			':client_id'=>$client_id,
			':latitude'=>$lat,		
			':longitude'=>$lng,		
			':address_type'=>$address_type
		]);
		if($model){		
			return [
				'address_uuid'=>$model->address_uuid,
				'street_number'=>$model->address1,
				'street_name'=>$model->formatted_address,
				'city'=>$model->city,
				'state'=>$model->state,
				'postal_code'=>$model->postal_code,
				'location_name'=>$model->location_name,
				'country'=>$model->country,
				'formatted_address'=>$model->formattedAddress,
				'address_label'=>$model->address_label,
				'latitude'=>$model->latitude,
				'longitude'=>$model->longitude,
				'place_id'=>$model->place_id,
				'delivery_instructions'=>$model->delivery_instructions,
				'delivery_options'=>$model->delivery_options,
			];
		}
		return false;
	}

	public static function fecthAddresses($client_id='',$address_type='map-based')
	{
		$model = AR_client_address::model()->findAll("client_id=:client_id AND address_type=:address_type AND formattedAddress!= '' order by address_id DESC",[
			':client_id'=>$client_id,
			':address_type'=>$address_type
		]); 
		if($model){
			$data = [];
			foreach ($model as $items) {
				$data[] = [
					'address_uuid'=>$items->address_uuid,
					'street_number'=>$items->address1,
					'street_name'=>$items->formatted_address,
					'city'=>$items->city,
					'state'=>$items->state,
					'postal_code'=>$items->postal_code,
					'country'=>$items->country,
					'formatted_address'=>$items->formattedAddress,
					'address_label'=>$items->address_label,
					'latitude'=>$items->latitude,
					'longitude'=>$items->longitude,
					'place_id'=>$items->place_id,
					'delivery_instructions'=>$items->delivery_instructions,
					'delivery_options'=>$items->delivery_options,
				];
			}
			return $data;
		}
		throw new Exception( t(HELPER_NO_RESULTS) );
	}

	public static function getAddressesList($client_id='')
	{
		$data = array();
		$model = AR_client_address::model()->findAll("client_id=:client_id AND address_type=:address_type AND formattedAddress!='' order by address_id DESC",[
			':client_id'=>$client_id,
			':address_type'=>"map-based"
		]); 
		if($model){
			$data = [];
			foreach ($model as $items) {
				$data[] = [
					'address_uuid'=>$items->address_uuid,
					'street_number'=>$items->address1,
					'street_name'=>$items->formatted_address,					
					'location_name'=>$items->location_name,
					'city'=>$items->city,
					'state'=>$items->state,
					'postal_code'=>$items->postal_code,
					'country'=>$items->country,					
					'formatted_address'=>$items->formattedAddress,
					'complete_address'=>"$items->address1 $items->formatted_address",
					'address_label'=>t($items->address_label),
					'latitude'=>$items->latitude,
					'longitude'=>$items->longitude,
					'place_id'=>$items->place_id,
					'delivery_instructions'=>$items->delivery_instructions,
					'delivery_options'=>$items->delivery_options,
				];
			}			
			return $data;
		}
		return false;
	}

}
/*end class*/