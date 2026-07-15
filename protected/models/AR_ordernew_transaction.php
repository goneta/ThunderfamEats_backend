<?php
class AR_ordernew_transaction extends CActiveRecord
{	

	public $order_uuid;
	public $total;
	public $used_card;
	public $photo, $path, $order_status;
	
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
		return '{{ordernew_transaction}}';
	}
	
	public function primaryKey()
	{
	    return 'transaction_id';
	}
		
	public function attributeLabels()
	{
		return array(
		 'transaction_id'=>"transaction_id",
		);
	}
	
	public function rules()
	{
		 return array(
            array('order_id,merchant_id,client_id,payment_code,trans_amount,currency_code,status', 
            'required','message'=> t(Helper_field_required2) ),   
                        
            array('date_created,date_modified,ip_address,transaction_uuid,transaction_type,transaction_name,to_currency_code,exchange_rate,
			admin_base_currency,exchange_rate_merchant_to_admin,exchange_rate_admin_to_merchant,order_status','safe'),
                        
         );
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){
			if($this->isNewRecord){				
				$this->date_created = CommonUtility::dateNow();
				$this->transaction_uuid = CommonUtility::createUUID('{{ordernew_transaction}}','transaction_uuid');
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

		if (in_array($this->scenario, ['partial_refund', 'refund', 'full_refund'])) {
			$status_completed = AOrderSettings::getStatus(array('status_delivered','status_completed'));			
			if(!in_array($this->order_status,(array)$status_completed)){				
				return;
			}

			// try {
			// 	if ($this->scenario === 'partial_refund') {
			// 		CEarnings::partialRefund($this->order_id,$this->trans_amount);
			// 	} elseif (in_array($this->scenario, ['refund', 'full_refund'])) {					
			// 		CEarnings::fullRefund($this->order_id);

			// 		// UPDATE STATUS TO REFUNDED
			// 		Yii::app()->db->createCommand("
			// 		UPDATE {{ordernew}}
			// 		SET status='refunded'
			// 		WHERE order_id=".$this->order_id."
			// 		")->query();
			// 	}							

			// } catch (Exception $e) {
			//     //echo $e->getMessage();
			// }	
		}							
	}

	protected function afterDelete()
	{
		parent::afterDelete();		
	}
		
}
/*end class*/