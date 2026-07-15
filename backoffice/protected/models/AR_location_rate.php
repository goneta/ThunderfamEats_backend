<?php
class AR_location_rate extends CActiveRecord
{	

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
		return '{{location_rate}}';
	}
	
	public function primaryKey()
	{
	    return 'rate_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'rate_id'=>t("rate id"),		    			
		);
	}
	
	public function rules()
	{
		return array(
		  array('merchant_id,country_id,state_id,city_id,area_id,fee', 
		  'required','message'=> t( Helper_field_required2 ) ),		
		  
		  array('country_id,state_id,city_id,area_id', 
		   'numerical', 
		   'integerOnly' => true, 
		   'min' => 1, 
		   'tooSmall' => t( Helper_field_required2 )
		  ),		  

		  array('fee', 'numerical', 
			'integerOnly' => false, 
			'min' => 0.01, 
			'tooSmall' => 
			t("Delivery fee is required")
		  ),			
		  
		  array('country_id', 'validateRates', 'on'=>"insert,update" ),

		);
	}

	public function validateRates($attribute, $params)
	{
		$criteria=new CDbCriteria();	
		$criteria->addCondition("
		merchant_id=:merchant_id 
		AND country_id=:country_id 
		AND state_id=:state_id 
		AND city_id=:city_id 
		AND area_id=:area_id 
		");
		$criteria->params = array(		
			':merchant_id'=>$this->merchant_id,
			':country_id'=>$this->country_id,
			':state_id'=>$this->state_id,
			':city_id'=>$this->city_id,
			':area_id'=>$this->area_id,
		);		
		if(!$this->isNewRecord){			
			$criteria->addNotInCondition("rate_id",array($this->rate_id));
		}								
		if(AR_location_rate::model()->findAll($criteria)){		
			$this->addError('country_id', t("Rates already exist") );	
		} 
	}

    protected function beforeSave()
	{
		if(!parent::beforeSave()){
			return false;
		} 
		
		return true;
	}
	
	protected function afterSave()
	{
		if(!parent::afterSave()){
			return false;
		}
		
		/*ADD CACHE REFERENCE*/
		CCacheData::add();
	}

	protected function afterDelete()
	{
		if(!parent::afterDelete()){
			return false;
		}
		
		/*ADD CACHE REFERENCE*/
		CCacheData::add();
	}
		
}
/*end class*/
