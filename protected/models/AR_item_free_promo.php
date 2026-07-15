<?php
class AR_item_free_promo extends CActiveRecord
{	

	public $item_name;
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
		return '{{item_free_promo}}';
	}
	
	public function primaryKey()
	{
	    return 'promo_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'promo_id'=>t("Promo ID"),
			'merchant_id'=>t("Merchant"),
			'free_item_id'=>t("Free Item"),
			'minimum_cart_total'=>t("Minimum Cart Total"),
			'max_free_quantity'=>t("Max Free Quantity"),
			'auto_add'=>t("Auto Add to Cart"),
			'status'=>t("Statu"),
		);
	}
	
	public function rules()
	{
		return array(

		  array('merchant_id,free_item_id,minimum_cart_total,max_free_quantity,status', 
		  'required','message'=> t( Helper_field_required2 )),
		  
		  array('item_token,item_size_id,cat_id,created_at,updated_at','safe'),

		  array('merchant_id, free_item_id, max_free_quantity', 'numerical', 'integerOnly' => true, 'min' => 1),

		  array('minimum_cart_total', 'numerical', 'min' => 0.01),

		  array('auto_add', 'in', 'range' => array(0, 1)),

		   array('free_item_id','ext.UniqueAttributesValidator','with'=>'merchant_id',
		   'message'=>t("Free item already exist")
		  ),            
		  		  
		);
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){
			if($this->isNewRecord){
				$this->created_at = CommonUtility::dateNow();	
			} else {
				$this->updated_at = CommonUtility::dateNow();	
			}						
			return true;
		} else return true;
	}
	
	protected function afterSave()
	{
		parent::afterSave();
		$is_promo_free_item = $this->status=='publish'?1:0;

		Yii::app()->db->createCommand("
		UPDATE {{item}}
		SET is_promo_free_item = ".q($is_promo_free_item)."
		WHERE item_id = ".q($this->free_item_id)."
		")->query();

        CCacheData::add();
	}

	protected function afterDelete()
	{
		parent::afterDelete();	

		Yii::app()->db->createCommand("
		UPDATE {{item}}
		SET is_promo_free_item = 0
		WHERE item_id = ".q($this->free_item_id)."
		")->query();

        CCacheData::add();	
	}
			
}
/*end class*/