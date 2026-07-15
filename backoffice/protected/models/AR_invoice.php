<?php
class AR_invoice extends CActiveRecord
{	

    public $file,$has_proof_uploaded;
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
		return '{{invoice}}';
	}
	
	public function primaryKey()
	{
	    return 'invoice_number';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'invoice_number	'=>t("Invoice #"),
			'date_from'=>t("Coverage start date"),
			'date_to'=>t("Coverage end date"),
		);
	}
	
	public function rules()
	{
		return array(
		  array('merchant_id,restaurant_name,contact_email,
          invoice_terms,invoice_total,date_from ,date_to', 
		  'required','message'=> t( Helper_field_required )),
		  		  		  
		  array('restaurant_name,contact_email', 'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')), 		            
		  
		  array('amount_paid,invoice_created,due_date,contact_phone,payment_status,invoice_terms,
		  viewed,date_created,date_modified,ip_address,merchant_base_currency,
		  admin_base_currency,exchange_rate_merchant_to_admin,exchange_rate_admin_to_merchant','safe'),		  
          
          array('date_from','validateRecords','on'=>"insert"),

		  array('invoice_created,due_date,date_from,date_to','validateDateTime'),
		  
		  array('contact_email','email','message'=>t(Helper_field_email))
		  
		);
	}

	public function validateDateTime($attribute,$params)
	{
		$format = 'Y-m-d H:i:s';				
		$d = DateTime::createFromFormat($format, $this->$attribute);
		$result = $d && $d->format($format) == $this->$attribute;
		if(!$result){
			$this->addError($attribute, t("Invalid Datetime") );
		}		
	}

    public  function validateRecords()
    {        
		if(!empty($this->date_from) && !empty($this->date_to)){
			$model = AR_invoice::model()->find("merchant_id=:merchant_id AND date_from=:date_from AND date_to=:date_to",[
				':merchant_id'=>$this->merchant_id,
				':date_from'=>$this->date_from,
				':date_to'=>$this->date_to,
			]);        
			if($model){
				$this->addError("date_from","Invoice range already exist");
			}        
		}        
    }

    protected function beforeSave()
	{
		if(parent::beforeSave()){
			if($this->isNewRecord){
				$this->date_created = CommonUtility::dateNow();					
			} else {
				$this->date_modified = CommonUtility::dateNow();											
			}
			$this->ip_address = CommonUtility::userIp();	

            if(empty($this->invoice_uuid)){
				$this->invoice_uuid = CommonUtility::createUUID("{{invoice}}",'invoice_uuid');
			}			
            // if(empty($this->pdf_filename)){
			// 	$this->pdf_filename = CommonUtility::createUUID("{{invoice}}",'pdf_filename').".pdf";
			// }			
			
			return true;
		} else return true;
	}
	
	protected function afterSave()
	{
		parent::afterSave();

		Yii::import('ext.runactions.components.ERunActions');	
		$cron_key = CommonUtility::getCronKey();		
		$get_params = array( 
		   'invoice_uuid'=> $this->invoice_uuid,
		   'key'=>$cron_key,
		   'language'=>Yii::app()->language
		);		
		
		$model = new AR_invoice_meta;
		$model->invoice_number = $this->invoice_number;
		$model->meta_name = 'history';
		$model->meta_value1 =  $this->scenario=="insert" ? "Invoice was created" : "Invoice was updated";
		$model->meta_value2 = CommonUtility::dateNow();
		$model->save();	
		
		if($this->isNewRecord){		
			CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/taskinvoice/afterinvoicecreate?".http_build_query($get_params) );
		}

        /*ADD CACHE REFERENCE*/
		CCacheData::add();
	}

	protected function afterDelete()
	{
		parent::afterDelete();		

        /*ADD CACHE REFERENCE*/
		CCacheData::add();
	}
		
}
/*end class*/
