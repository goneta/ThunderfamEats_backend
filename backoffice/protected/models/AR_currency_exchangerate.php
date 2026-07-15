<?php
class AR_currency_exchangerate extends CActiveRecord
{	

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
		return '{{currency_exchangerate}}';
	}
	
	public function primaryKey()
	{
	    return 'id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'provider'=>t("Provider"),		    
		);
	}
	
	public function rules()
	{
		return array(
		  array('provider,base_currency,currency_code,exchange_rate', 
		  'required','message'=> t( Helper_field_required )),		
		  
		  array('base_currency','ext.UniqueAttributesValidator','with'=>'currency_code',
		    'message'=>t("Exchange rate already exist")
		  ), 
		  
		  array('exchange_rate', 'numerical', 'allowEmpty' => true,
          'integerOnly' => false, 'min' => 0, 'max' => 9999999999, 'message'=>t(Helper_field_numeric)),
		  
		  array('date_created,date_modified,ip_address','safe'),		  
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
	}
		
}
/*end class*/