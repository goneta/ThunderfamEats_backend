<?php
class AR_voucher extends CActiveRecord
{	
	   				
	public $days_available,$apply_to_merchant,$apply_to_customer,$transaction_type;
	
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
		return '{{voucher_new}}';
	}
	
	public function primaryKey()
	{
	    return 'voucher_id';	 
	}
		
	public function attributeLabels()
	{
		return array(		    
		  'voucher_name'=>t("Coupon name"),
		  'max_number_use'=>t("Define max number of use"),
		  'voucher_name'=>t("Coupon name"),		  
		  'amount'=>t("Discount"),		 
		  'min_order'=>t("Minimum Order"),		
		  'max_order'=>t("Maximum Order"),	
		  'expiration'=>t("Expiration"),	
		  'max_discount_cap'=>t("Maximum Discount Cap")			   		  
		);
	}
	
	public function rules()
	{
		return array(		  
		   array('voucher_name,voucher_type,amount,days_available,status,expiration', 
		  'required','message'=> t( Helper_field_required ) ),
		  
		  array('voucher_name,voucher_type,apply_to_merchant', 
		  'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')), 
		  
		  array('apply_to_merchant,min_order,max_order,apply_to_customer,used_once,max_number_use,transaction_type,visible,
		  selected_customer,applicable_to
		  ','safe'),
		  
		  array('expiration', 'date', 'format'=>'yyyy-M-d'),
		  
		  array('amount', 'numerical', 'integerOnly' => false,
		    'min'=>1,
		    'tooSmall'=>t(Helper_field_numeric_tooSmall),
		    'message'=>t(Helper_field_numeric)),
		    
		  array('amount,min_order,max_order,max_discount_cap', 'numerical', 'integerOnly' => false,		    
		    'message'=>t(Helper_field_numeric)),
		    
		  array('max_number_use', 'numerical', 'integerOnly' => true,
		    'message'=>t(Helper_field_numeric)),  
		    
		  array('voucher_name','unique','message'=>t(Helper_field_unique)),

		//   array('max_discount_cap,max_order', 'numerical', 'integerOnly' => false,	
		//   'min'=>0,
		//   'message'=>t(Helper_field_numeric)),
		    
		);
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){
			if($this->isNewRecord){
				$this->date_created = CommonUtility::dateNow();					
			} else {
				$this->date_modified = CommonUtility::dateNow();											
			}
			$this->ip_address = CommonUtility::userIp();	
			$this->applicable_to = json_encode($this->transaction_type);

			$this->min_order = floatval($this->min_order);
			$this->max_order = floatval($this->max_order);
			$this->max_discount_cap = floatval($this->max_discount_cap);
			
			return true;
		} else return true;
	}
	
	protected function afterSave()
	{
		parent::afterSave();		
		$this->ProcessData();		
				
		$this->removeCoupon($this->voucher_id);
		if(is_array($this->transaction_type) && count($this->transaction_type)>=1){
			//dump($this->transaction_type);die();
			$params = [];			
			foreach ($this->transaction_type as $items) {
				$params[] = [
					'merchant_id'=>$this->merchant_id,
					'meta_name'=>"coupon",
					'meta_value'=>$this->voucher_id,
					'meta_value1'=>$items,
				];
			}
			$builder=Yii::app()->db->schema->commandBuilder;
			$command=$builder->createMultipleInsertCommand("{{merchant_meta}}",$params);
			$command->execute();
		}		
		
		/*ADD CACHE REFERENCE*/
		CCacheData::add();						
	}

	protected function afterDelete()
	{
		parent::afterDelete();
		$this->ProcessData();			
		
		/*ADD CACHE REFERENCE*/
		CCacheData::add();
	}

	public function removeCoupon($voucher_id='')
    {
        $criteria = new CDbCriteria();
        $criteria->condition = "meta_name=:meta_name AND meta_value=:meta_value";
        $criteria->params = [
			':meta_name'=>'coupon',
			':meta_value'=>$voucher_id
		];    
        AR_merchant_meta::model()->deleteAll($criteria);        
    }

	private function ProcessData()
	{
		Yii::app()->db->createCommand("
		DELETE FROM {{promos}}
		WHERE id=".q($this->voucher_id)."
		AND offer_type = 'voucher'
		")->query();		
		//CommonUtility::pushJobs("ProcessPromos",[]);
		$jobs = 'ProcessPromos';
		if (class_exists($jobs)) {
			$jobInstance = new $jobs([]);
			$jobInstance->execute();	
		}     
	}
		
}
/*end class*/
