<?php
class AR_suggested_items extends CActiveRecord
{	

	public $item_name,$item_price,$photo,$path,$restaurant_name,$group_category,$is_featured,$featured_priority;
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
		return '{{suggested_items}}';
	}
	
	public function primaryKey()
	{
	    return 'id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'id'=>t("ID"),		    		    
			'merchant_id'=>t("Merchant ID"),
			'item_id'=>t("Item ID"),
		);
	}
	
	public function rules()
	{
		return array(
		  array('merchant_id,item_id,status', 
		  'required','message'=> t( Helper_field_required ) ),		  		  
		);
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
		/*ADD CACHE REFERENCE*/
		CCacheData::add();

		if($this->status=="rejected"){
			$model = AR_item_slug::model()->findByPk($this->item_id);
			if($model){
				$model->is_featured = 0;
				$model->featured_priority = 0;
				$model->save();				
				$jobs = 'SuggestedItems';
                if (class_exists($jobs)) {
                    $jobInstance = new $jobs([
                        'status'=>$this->status,
						'merchant_id'=>$this->merchant_id,
						'item_id'=>$this->item_id,
						'language'=>Yii::app()->language
                    ]);
					$jobInstance->execute();	
                }    
			}			
		} else if ($this->status=="approved"){
			$model = AR_item_slug::model()->findByPk($this->item_id);
			if($model){
				$model->is_featured = 1;
				$model->featured_priority = $this->featured_priority;
				$model->save();					
				$jobs = 'SuggestedItems';
                if (class_exists($jobs)) {
                    $jobInstance = new $jobs([
                        'status'=>$this->status,
						'merchant_id'=>$this->merchant_id,
						'item_id'=>$this->item_id,
						'language'=>Yii::app()->language
                    ]);
					$jobInstance->execute();	
                }    
			}			
		}
	}

	protected function afterDelete()
	{
		/*ADD CACHE REFERENCE*/
		CCacheData::add();
		
		$model = AR_item_slug::model()->findByPk($this->item_id);
		if($model){
			$model->is_featured = 0;
			$model->save();
		}
	}
		
}
/*end class*/
