<?php
class AR_merchant_location extends CActiveRecord
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
		return '{{merchant_location}}';
	}
	
	public function primaryKey()
	{
	    return 'id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'id'=>t("ID"),		    			
		);
	}
	
	public function rules()
	{
		return array(
		  array('merchant_id,country_id,state_id,city_id,area_id', 
		  'required','message'=> t( Helper_field_required ) ),
		  
		  array('country_id', 'validateLocation', 'on'=>"insert,update" ),

		  array('country_id,state_id,city_id,area_id', 
		   'numerical', 
		   'integerOnly' => true, 
		   'min' => 1, 
		   'tooSmall' => t( Helper_field_required2 )
		  ),		  

		);
	}

	public function validateLocation($attribute, $params)
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
			$criteria->addNotInCondition("id",array($this->id));
		}								
		if(AR_merchant_location::model()->findAll($criteria)){		
			$this->addError('country_id', t("Location already exist") );	
		} 
	}

    protected function beforeSave()
	{
		if(!parent::beforeSave()){
			return false;
		} 

		if($this->isNewRecord){
			$this->created_at = CommonUtility::dateNow();	
		} else {
			$this->updated_at = CommonUtility::dateNow();	
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
