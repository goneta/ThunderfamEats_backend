<?php
class AR_review extends CActiveRecord
{		

	public $reply_comment;
	public $first_name, $last_name, $logo, $path , $total;
	public $customer_fullname, $driver_fullname;
	public $meta,$media, $items;
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
		return '{{review}}';
	}
	
	public function primaryKey()
	{
	    return 'id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'review'=>t("review"),
		    'rating'=>t("ratings"),
		);
	}
	
	public function rules()
	{
		return array(
		  array('review,rating,status', 
		  'required','message'=> t( Helper_field_required ),
		    'on'=>"insert,update"
		   ),		  
		  
		  
		  array('reply_comment', 
		  'required','message'=> t( Helper_field_required ),
		  'on'=>"reply"
		   ),		  		  
		  
		  array('review,rating,status',
            'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')),         
		  
           array('rating', 'numerical', 'integerOnly' => false, 'min'=>1,'max'=>5,
             'tooSmall'=>t(Helper_field_numeric_tooSmall),
             'tooBig'=>t(Helper_field_numeric_tooBig),
		     'message'=>t(Helper_field_numeric),
		     'on'=>"insert,update"
		  ),  
		  		  
		  array('as_anonymous,merchant_id,order_id','safe')
            
		);
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){
			
		     if(DEMO_MODE){			
				if($this->scenario!="insert"){
					return false;
				}		        
		     }
			
			if($this->isNewRecord){
				$this->date_created = CommonUtility::dateNow();					
			} else {
				$this->date_modified = CommonUtility::dateNow();											
			}
			$this->ip_address = CommonUtility::userIp();	
			
			return true;
		} else return true;
	}
	
	protected function afterSave()
	{
		parent::afterSave();
						
		switch ($this->scenario) {
			case 'add_review':
			case 'insert':
				CommonUtility::pushJobs("AfterReview",[
					'id'=>$this->id
				]);		   
				
				// ADD FOOD ITEM REVIEW
				$params = [];
				if (!empty($this->items) || is_array($this->items)) {
					foreach ($this->items as $items) {
						$params[] = [
							'merchant_id'=>$this->merchant_id,
							'item_id'=>$items['item_id'] ?? 0,
							'order_id'=>$this->order_id,
							'client_id'=>$this->client_id,
							'rating'=>$items['rating'] ?? 0,
							'created_at'=>CommonUtility::dateNow()
						];
					}	
					$builder=Yii::app()->db->schema->commandBuilder;
					$command=$builder->createMultipleInsertCommand('{{food_item_reviews}}',$params);
					$command->execute();
				}				
				break;			
		}

		/*ADD CACHE REFERENCE*/
		CCacheData::add();
	}
	
    protected function beforeDelete()
	{				
	    if(DEMO_MODE){				
		    return false;
		}
	    return true;
	}

	
	protected function afterDelete()
	{
		parent::afterDelete();		
		
		if($this->parent_id<=0){
		    $model = AR_review::model()->deleteAll("parent_id=:parent_id",array(
			  ':parent_id'=>$this->id			  
			));
		    
		}		
		
		/*ADD CACHE REFERENCE*/
		CCacheData::add();
	}
		
}
/*end class*/
