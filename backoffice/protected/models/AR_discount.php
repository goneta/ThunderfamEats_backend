<?php

use Symfony\Component\Translation\Dumper\DumperInterface;

class AR_discount extends CActiveRecord
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
		return '{{discount}}';
	}
	
	public function primaryKey()
	{
	    return 'discount_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'discount_id'=>t("ID"),
            'title'=>t("Title"),            
			'amount'=>t("Amount"),    
			'minimum_amount'=>t("Minimum amount"),    
			'maximum_amount'=>t("Maximum amount"), 
			'start_date'=>t("Start date"),
			'expiration_date'=>t("Expirtion date"),    
		);
	}
	
	public function rules()
	{
		return array(
		  array('title,discount_type,amount,start_date,expiration_date,transaction_type', 
		  'required','message'=> t( Helper_field_required ) ),		 
		
		  array('merchant_id,minimum_amount,maximum_amount,status,date_created,date_modified,ip_address','safe'),		  	
                    
          array('title','length', 'min'=>0, 'max'=>255,
            'tooShort'=>t("title is too short"),
            'tooLong'=>t("title is too long"),                           
        ),

          array('amount,minimum_amount', 'numerical', 'integerOnly' => false,
		  'min'=>1,		  
		  'tooSmall'=>t("You must enter at least greater than 1"),		  
		  'message'=>t(Helper_field_numeric)),

		  array('maximum_amount', 'numerical', 'integerOnly' => false,
		  'min'=>0,		  
		  'tooSmall'=>t("You must enter at least greater than 1"),		  
		  'message'=>t(Helper_field_numeric)),
		  
		  array('start_date','overLappingDateCheck'),

		);
	}

	public function overLappingDateCheck($attribute,$params)
	{		
		$stmt = "SELECT * FROM {{discount}}
		WHERE DATE(expiration_date) BETWEEN ".q($this->start_date)." AND ".q($this->expiration_date)."
		AND status = 1
		";		
		if($this->scenario=="update"){
			$stmt.=" AND discount_id!= ".q($this->discount_id)."";
		}		
		if($res = Yii::app()->db->createCommand($stmt)->queryRow()){			
			$this->addError($attribute, t("overlapping date ranges with existing promotions") );
		}		
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

			if(empty($this->discount_uuid)){
				$this->discount_uuid = CommonUtility::createUUID("{{discount}}",'discount_uuid');
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

	protected function afterDelete()
	{
		parent::afterDelete();		
		CCacheData::add();
	}

	protected function beforeDelete()
	{				
	    if(DEMO_MODE){				
            return false;
        }
	    return true;
	}
		
}
/*end class*/