<?php
class AR_food_item_reviews extends CActiveRecord
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
		return '{{food_item_reviews}}';
	}
	
	public function primaryKey()
	{
	    return 'review_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'review_id'=>t("Review ID"),		    			
		);
	}
	
	public function rules()
	{
		return array(
		  array('review_id,merchant_id,item_id,order_id,client_id,rating', 
		  'required','message'=> t( Helper_field_required2 ) ),		  
		);
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){
			if(DEMO_MODE){				
				return false;
			}
			if($this->isNewRecord){				
				$this->created_at = CommonUtility::dateNow();					
			} 									
			return true;
		} else return true;
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
