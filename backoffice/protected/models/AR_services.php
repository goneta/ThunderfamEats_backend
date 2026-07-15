<?php
class AR_services extends CActiveRecord
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
				
		$name = array();
		/*if($this->multi_language){						
			$name  = $this->service_name_trans;
			if(isset($name[KMRS_DEFAULT_LANGUAGE])){
				$name[KMRS_DEFAULT_LANGUAGE] = !empty($name[KMRS_DEFAULT_LANGUAGE])?$name[KMRS_DEFAULT_LANGUAGE]:$this->service_name;
			}						
		} else {
			$name[KMRS_DEFAULT_LANGUAGE] = $this->service_name;
		}*/
		
		$name = $this->service_name_trans;		
		$name[KMRS_DEFAULT_LANGUAGE] = $this->service_name;
		
		Item_translation::insertTranslation( 
		(integer) $this->service_id ,
		'service_id',
		'service_name',
		'',
		array(	                  
		  'service_name'=>$name,			  
		),"{{services_translation}}");
		
				
		$fee = AR_services_fee::model()->find('merchant_id=:merchant_id AND service_id=:service_id', 
		array(':merchant_id'=>0, ':service_id'=>intval($this->service_id) ));
		if(!$fee){
			$fee = new AR_services_fee;			
		} 
		
		$fee->service_id = intval($this->service_id);
		$fee->merchant_id = 0;
		$fee->service_fee = floatval($this->service_fee);
		$fee->charge_type = $this->charge_type;
		$fee->small_order_fee = floatval($this->small_order_fee);
		$fee->small_less_order_based = floatval($this->small_less_order_based);
		$fee->save();		
		
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
		Item_translation::deleteTranslation($this->service_id,'service_id','services_translation');
		
		$fee = AR_services_fee::model()->find('merchant_id=:merchant_id AND service_id=:service_id', 
		array(':merchant_id'=>0, ':service_id'=>$this->service_id ));
		if($fee){
			$fee->delete();
		}
		
		/*ADD CACHE REFERENCE*/
		CCacheData::add();
	}
	
	public static function getID($service_code='')
	{
		$stmt="
		SELECT service_id FROM {{services}}
		WHERE service_code = ".q($service_code)."
		LIMIT 0,1
		";
		if($res = Yii::app()->db->createCommand($stmt)->queryRow()){
			return $res['service_id'];
		}
		return false;
	}
	
	public static function getTranslation($service_code='',$lang='')
	{
		
		$stmt="
		SELECT a.service_code,
		b.service_name
		
		FROM {{services}} a
		LEFT JOIN {{services_translation}} b
		ON 
		a.service_id = b.service_id
		
		WHERE a.service_code = ".q($service_code)."
		AND b.language = ".q($lang)."
		";				
		if($res = Yii::app()->db->createCommand($stmt)->queryRow()){
			return $res['service_name'];
		}
		return $service_code;
	}
		
}
/*end class*/
