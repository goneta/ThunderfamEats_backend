<?php
class AR_ordernewtable extends CActiveRecord
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
		return '{{ordernew}}';
	}
	
	public function primaryKey()
	{
	    return 'order_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'order_id'=>t("order_id"),		    
		);
	}
	
	public function rules()
	{
		return array(

		  array('order_id,status', 
		  'required','message'=> t( Helper_field_required ) ,'on'=>'registration_phone' ),
		  
		  array('total_discount,points,service_fee,delivery_fee,packaging_fee,tax,courier_tip,
            promo_code,promo_total,delivery_time,delivery_time_end,cash_change,commission_type,
            commission_based,commission,merchant_earning,use_currency,base_currency,
            exchange_rate,is_critical,date_created,date_modified,ip_address,driver_id,vehicle_id,delivered_at,request_from
			','safe'),
		  		  
		);
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){			
			return true;
		} else return true;
	}
	
	protected function afterSave()
	{
		parent::afterSave();
        CCacheData::add();
	}

	protected function afterDelete()
	{
		parent::afterDelete();	
        CCacheData::add();	
	}
			
}
/*end class*/