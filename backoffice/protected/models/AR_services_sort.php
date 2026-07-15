<?php
class AR_services_sort extends CActiveRecord
{	
	   			
	public $multi_language,$service_name_trans,$service_fee,$charge_type,
	$small_order_fee,$small_less_order_based;	
	
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
		return '{{services}}';
	}
	
	public function primaryKey()
	{
	    return 'service_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'service_code'=>t("Code"),
		    'service_name'=>t("Title"),
		    'service_fee'=>t("Service fee"),
			'small_order_fee'=>t("Small order fee"),
			'small_less_order_based'=>t("Less than subtotal"),
		);
	}
	
	public function rules()
	{
		return array(
		  array('service_code,service_name,status', 
		  'required','message'=> t( Helper_field_required ) ),
		  array('service_code,service_name,color_hex,font_color_hex,status,description', 'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')), 
		  array('color_hex,font_color_hex,service_name_trans,description','safe'),
		  array('service_code','unique','message'=>t(Helper_field_unique)),
		  array('service_fee,small_order_fee,small_less_order_based', 'numerical', 'integerOnly' => false,		  
		  'message'=>t(Helper_field_numeric)),
		  array('charge_type','safe')
		);
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){
			
			if(DEMO_MODE){				
			    return false;
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
		/*ADD CACHE REFERENCE*/
		CCacheData::add();
	}	
}
/*end class*/
