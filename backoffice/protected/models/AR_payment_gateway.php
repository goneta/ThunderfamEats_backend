<?php
class AR_payment_gateway extends CActiveRecord
{	

	public $image;
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
		return '{{payment_gateway}}';
	}
	
	public function primaryKey()
	{
	    return 'payment_id';	 
	}
		
	public function attributeLabels()
	{
		return array(		    
		  'payment_name'=>t("Payment name"),
		  'payment_code'=>t("Payment code"),
		  'logo_type'=>t("Logo type"),
		  'logo_class'=>t("Logo class icon"),
		  'logo_image'=>t("Image"),
		  'status'=>t("Status"),
		  'sequence'=>t("Sequence"),
		  'file' => 'Upload File',
		);
	}
	
	public function rules()
	{
		return array(
		  array('payment_name,payment_code,logo_type,status,is_online', 
		  'required','message'=> t( Helper_field_required ) ),
		  
		  array('payment_name,payment_code,logo_type,logo_image', 
		  'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')), 
		  		  
		  array('payment_code','unique','message'=>t(Helper_field_unique)),
		  
		  array('logo_class,logo_image,sequence,attr1,attr2,attr3,attr4,attr5,attr6,attr7,attr8,attr9,attr_json,attr_json1,is_live,is_payout,is_plan,split,capture','safe'),
		  
		  array('payment_code,payment_name,
		  attr1,attr2,attr3,attr4,attr5,attr6,attr7,attr8
		  ','length','max'=>255),

		  array('logo_class','length','max'=>100),
		  
		  array('payment_code','removeSpaces'),

		  array('file', 'file', 'types'=>'jpg, jpeg, png, pem,p12', 'allowEmpty'=>true),
		  
		);
	}

	public function removeSpaces($attribute, $params)
	{
		$this->payment_code = str_replace(" ","",$this->payment_code);
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
		CCacheData::add();	
	}
		
}
/*end class*/
