<?php
class AR_payment_gateway_merchant extends CActiveRecord
{	

	public $image,$payment_name,$logo_type,$logo_image,$path;
	public $file;
	
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
		return '{{payment_gateway_merchant}}';
	}
	
	public function primaryKey()
	{
	    return 'payment_uuid';	 
	}
		
	public function attributeLabels()
	{
		return array(		    
		  'payment_name'=>t("Payment name"),
		  'payment_code'=>t("Payment code"),		  
		  'status'=>t("Status"),
		  'sequence'=>t("Sequence"),
		);
	}
	
	public function rules()
	{
		return array(
		  array('payment_id,status', 
		  'required','message'=> t( Helper_field_required ) ),
		  
		  array('payment_id,merchant_id,payment_id,status', 
		  'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')), 
		  		  		  
		  array('sequence,attr1,attr2,attr3,attr4,attr5,attr6,attr7,attr8,attr9,attr_json,is_live,split,capture,file','safe'),
		  
		  array('payment_code,attr2,attr3,attr4,attr5,attr6,attr7,attr8','length','max'=>255),		  
		  
		   array('payment_id', 'ext.UniqueAttributesValidator', 'with'=>'merchant_id' , 
		   'message'=>t("Payment gateway already added.") ),		 
		  
		   array('file', 'file', 'types'=>'jpg, jpeg, png, pem,p12', 'allowEmpty'=>true),

		);
	}
		
    protected function beforeSave()
	{
		if(parent::beforeSave()){
			if($this->isNewRecord){
				$this->date_created = CommonUtility::dateNow();		
				$this->payment_uuid = CommonUtility::createUUID("{{payment_gateway_merchant}}",'payment_uuid');
								
				$model = AR_payment_gateway::model()->find('payment_id=:payment_id', 
		        array(':payment_id'=>$this->payment_id)); 				        
		        if($model){
		        	$this->payment_code = $model->payment_code;					
					if($model->payment_code=="viva"){
						$model->attr_json = '{"attr1":{"label":"Merchant ID"},"attr2":{"label":"Language"},"attr3":{"label":"Source Code"},"attr5":{"label":"Api key"}}';
					}										
		        	$this->attr_json = $model->attr_json;
					$this->attr4 = $model->attr4;
		        }
				
			} else {
				$this->date_modified = CommonUtility::dateNow();	
				if(empty($this->payment_uuid)){
					$this->payment_uuid = CommonUtility::createUUID("{{payment_gateway_merchant}}",'payment_uuid');
				}
			}
			$this->ip_address = CommonUtility::userIp();	
			
			return true;
		} else return true;
	}
	
	protected function afterSave()
	{
		parent::afterSave();
		CCacheData::add();
		if($this->payment_code=="viva"){						
			AR_merchant_meta::saveMeta($this->merchant_id,'viva_merchant_id',$this->attr1);
			AR_merchant_meta::saveMeta($this->merchant_id,'viva_merchant_lang',$this->attr2);
			AR_merchant_meta::saveMeta($this->merchant_id,'viva_merchant_source',$this->attr3);			
			AR_merchant_meta::saveMeta($this->merchant_id,'viva_api_key',$this->attr5);	
		}
	}

	protected function afterDelete()
	{
		parent::afterDelete();	
		CCacheData::add();	
	}
		
}
/*end class*/