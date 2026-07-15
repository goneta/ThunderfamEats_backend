<?php
class AR_client_address_location extends CActiveRecord
{	

	public $complete_address;
	/**
	 * Returns the static model of the specified AR class.
	 * @return static the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{client_address}}';
	}
	
	public function primaryKey()
	{
	    return 'address_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		  'address_uuid'=>t("address_uuid"),
		  'place_id'=>t("place_id"),
		  'location_name'=>t("Location"),
		  'formatted_address'=>t("Street name"),
		  'place_id'=>t("Places ID"),
		  'latitude'=>t("Latitude"),
		  'longitude'=>t("Longitude"),
		  'address1'=>t("Street number"),
		  'postal_code'=>t("State/Region"),
		  'address2'=>t("City"),
		  'custom_field1'=>t("Distric/Area/neighborhood"),
		  'delivery_options'=>t("Delivery options"),
		  'delivery_instructions'=>t("Delivery instructions"),
		  'custom_field2'=>t("House number")
		);
	}
	
	public function rules()
	{
		return array(
		  		  
				  
		  array('client_id,custom_field2,formatted_address,address1,postal_code,address2,custom_field1,country_code,latitude,longitude',
		    'required',
			'message'=>t(Helper_field_required2)
		  ),

		  array('address_type,delivery_options,delivery_instructions,address_label,address_uuid','safe' ),

		  array('postal_code,address2,custom_field1,country_code', 
		  'numerical', 
		  'integerOnly' => true, 
		  'min' => 1, 
		  'tooSmall' => t( Helper_field_required2 )
		  ),		  

		  array('state','validateAddress'),
		  
		  
		);
	}

	public function validateAddress($attribute,$params)
	{
		$criteria=new CDbCriteria();
		$criteria->condition = "client_id=:client_id AND postal_code=:postal_code AND address2=:address2 
		AND custom_field1=:custom_field1
		AND formatted_address=:formatted_address
		AND custom_field2=:custom_field2
		";
		$criteria->params = [
			":client_id"=>$this->client_id,
			":postal_code"=>$this->postal_code,
			":address2"=>$this->address2,
			":custom_field1"=>$this->custom_field1,
			":formatted_address"=>$this->formatted_address,
			":custom_field2"=>$this->custom_field2,
		];
		if(!$this->isNewRecord){
			$criteria->addNotInCondition("address_uuid",[
				$this->address_uuid
			]);
		}
		if(AR_client_address_location::model()->find($criteria)){
			$this->addError($attribute, t("Address already exist") );
		}
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){
			if($this->isNewRecord){
				$this->date_created = CommonUtility::dateNow();									
			} else {
				$this->date_modified = CommonUtility::dateNow();											
			}
			
			if(empty($this->address_uuid)){
				$this->address_uuid = CommonUtility::createUUID("{{client_address}}",'address_uuid');
			}
			
			$this->ip_address = CommonUtility::userIp();			
			
			return true;
		} else return true;
	}
	
	protected function afterSave()
	{
		parent::afterSave();
		CCacheData::add();
	}

	protected function afterDelete()
	{
		parent::afterDelete();		
		CCacheData::add();
	}
		
}
/*end class*/
