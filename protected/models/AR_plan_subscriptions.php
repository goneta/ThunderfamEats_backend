<?php
class AR_plan_subscriptions extends CActiveRecord
{	

	public $plan_title , $item_limit , $order_limit, 
	$remaining_orders_display ,$remaining_items_display,
	$remaining_orders_percentage, $remaining_items_percentage,
	$price,$package_period
	;
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
		return '{{plan_subscriptions}}';
	}
	
	public function primaryKey()
	{
	    return 'id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'subscriber_id'=>t("Subscriber ID"),		    
		);
	}
	
	public function rules()
	{
		return array(

			array('subscription_id','unique','message'=>t("Subscription ID already exist")),

			array('subscriber_id,package_id,subscription_id,amount', 
			'required','message'=> t( Helper_field_required ) ),

			array('payment_id,subscriber_type,status,date_created,
			updated_at,sucess_url,failed_url,payment_code,
			created_at,next_due,expiration,current_start,current_end,currency_code,plan_name,billing_cycle,jobs
			',
			'safe'),
		  		  
		);
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){
			if($this->isNewRecord){
				$this->date_created = CommonUtility::dateNow();	
			} else {
				$this->updated_at = CommonUtility::dateNow();	
			}						
			return true;
		} else return true;
	}
	
	protected function afterSave()
	{
		parent::afterSave();		        
		
		switch($this->status){
			case "active":
				if($this->subscriber_type=="merchant"){				
					$merchant = CMerchants::get($this->subscriber_id);					
					$merchant->status = 'active';
					$merchant->package_id = intval($this->package_id);
					$merchant->save();
				} else {
					//
				}
				break;

			case "cancelled":
			case "halted":
			case "payment failed":
				if($this->subscriber_type=="merchant"){				
					$merchant = CMerchants::get($this->subscriber_id);				
					$merchant->status = 'expired';		
					$merchant->orders_added = 0;
					$merchant->items_added = 0;		
					$merchant->order_limit = 0;
					$merchant->item_limit = 0;
					$merchant->save();		
				} else {
					//
				}
				break;			
		}

		CCacheData::add();
	}

	protected function afterDelete()
	{
		parent::afterDelete();	
        CCacheData::add();	
	}
			
}
/*end class*/