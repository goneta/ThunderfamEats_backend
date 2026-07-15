<?php
class AR_offers extends CActiveRecord
{	

	public $applicable_selected;
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
		return '{{offers}}';
	}
	
	public function primaryKey()
	{
	    return 'offers_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		  'offer_name'=>t("Offer Name"),
		  'offer_percentage'=>t("Offer Percentage")." (%)",
		  'min_order'=>t("Minimum Order"),
		  'offer_price'=>t("Orders Over"),
		  'valid_from'=>t("Valid From"),
		  'valid_to'=>t("Valid To"),		
		  'max_discount_cap'=>t("Maximum Discount Cap"),
		);
	}
	
	public function rules()
	{
		return array(
		  array('offer_name,offer_percentage,offer_price,valid_from,valid_to,status', 
		  'required','message'=> t( Helper_field_required ) ),
		  
		  array('offer_percentage,offer_price,valid_from,valid_to,status', 
		  'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')), 		  
		  
		  array('applicable_selected,max_discount_cap','safe'),
		  
		  array('offer_percentage,offer_price', 'numerical', 'integerOnly' => false,	
		  'min'=>1,
		  'message'=>t(Helper_field_numeric)),

		  array('max_discount_cap,min_order', 'numerical', 'integerOnly' => false,	
		  'min'=>0,
		  'message'=>t(Helper_field_numeric)),
		  		  
		);
	}

    protected function beforeSave()
	{
		if(!parent::beforeSave()){
			return false;
		} 
		
		$this->offer_percentage = (float) $this->offer_percentage;
		$this->offer_price = (float) $this->offer_price;
		$this->max_discount_cap = (float) $this->max_discount_cap;
		$this->min_order = (float) $this->min_order;
		
		if($this->applicable_selected){
			$this->applicable_to = json_encode($this->applicable_selected);
		} else $this->applicable_to='';
		
		if($this->isNewRecord){
			$this->date_created = CommonUtility::dateNow();					
		} else {
			$this->date_modified = CommonUtility::dateNow();											
		}
		$this->ip_address = CommonUtility::userIp();	
		
		return true;
	}
	
	protected function afterSave()
	{
		parent::afterSave();
		$this->ProcessData();		
	}

	protected function afterDelete()
	{
		parent::afterDelete();		
		$this->ProcessData();
	}

	private function ProcessData()
	{
		Yii::app()->db->createCommand("
		DELETE FROM {{promos}}
		WHERE id=".q($this->offers_id)."
		AND offer_type = 'offers'
		")->query();		
		//CommonUtility::pushJobs("ProcessPromos",[]);
		$jobs = 'ProcessPromos';
		if (class_exists($jobs)) {
			$jobInstance = new $jobs([]);
			$jobInstance->execute();	
		}     
				
		CCacheData::add();
	}	
		
}
/*end class*/
