<?php
class AR_printer extends CActiveRecord
{	

    //public $printer_uuid;
	public $printer_user,$printer_ukey,$printer_sn,$printer_key,$printer_name;

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
		return '{{printer}}';
	}
	
	public function primaryKey()
	{
	    return 'printer_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'printer_id	'=>t("printer_id"),	
			'printer_name'=>t("Printer name"),
			'printer_user'=>t("User"),
			'printer_ukey'=>t("Ukey"),
			'printer_sn'=>t("Sn"),
			'printer_key'=>t("Key"),	
			'service_id'=>t("Service ID")
		);
	}
	
	public function rules()
	{
		return array(
		  array('printer_name,printer_model,status', 
		  'required','message'=> t( Helper_field_required ) ),
		  		  
          
		  array('printer_name', 'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')), 		  
		  
		  array('platform,merchant_id,device_uuid,paper_width,auto_print,auto_close,date_created,ip_address,device_id,service_id,characteristics','safe'),		  

          array('printer_uuid','checkPrinterUUID','on'=>'feieyun_add,feieyun_update'),

		  array('print_type,character_code,printer_user,printer_ukey,printer_ukey,printer_sn,printer_key,printer_key','safe'),

		  array('printer_user,printer_ukey,printer_sn,printer_key', 
		  'required','message'=> t( Helper_field_required ) ,'on'=>"feieyun_add,feieyun_update" ),

		//   array('printer_user,printer_ukey,printer_sn,printer_key', 
		//   'required','message'=> t( Helper_field_required ) ,'on'=>"feieyun_update" ),

		  array('service_id,characteristics', 
		  'required','message'=> t( Helper_field_required ) ,'on'=>"wifi_add,wifi_update" ),
		  
		);
	}

    public function checkPrinterUUID(){
		if($this->printer_model=="feieyun"){
			$this->addFPprinter();
		} else {
			if(empty($this->printer_uuid)){
				$this->addError('printer_uuid', t("Invalid printer, please choose your printer") );	
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

			if(empty($this->platform)){
				$this->platform = $this->merchant_id>0 ? 'merchant': "admin";
			}
			
			return true;
		} else return true;
	}
	
	protected function afterSave()
	{
		parent::afterSave();

        if($this->printer_model=="feieyun"){
			AR_printer_meta::saveMeta($this->printer_id,'device_uuid',$this->printer_sn,'','insert');        	
			AR_printer_meta::saveMeta($this->printer_id,'printer_user',$this->printer_user,'','insert');        	
			AR_printer_meta::saveMeta($this->printer_id,'printer_ukey',$this->printer_ukey,'','insert');        	
			AR_printer_meta::saveMeta($this->printer_id,'printer_sn',$this->printer_sn,'','insert');        	
			AR_printer_meta::saveMeta($this->printer_id,'printer_key',$this->printer_key,'','insert');        	
		} else {
			AR_printer_meta::saveMeta($this->printer_id,'device_uuid',$this->printer_uuid,'','insert');        	
		}         

		if($this->auto_print==1){
			$stmt = "
			UPDATE {{printer}}
			SET auto_print=0
			WHERE merchant_id=".q($this->merchant_id)."
			AND platform=".q($this->platform)."
			AND printer_id <> ".q($this->printer_id)."
			";
			Yii::app()->db->createCommand($stmt)->query();
		}

        CCacheData::add();
	}

	protected function afterDelete()
	{
		parent::afterDelete();		
        
		if($this->printer_model=="feieyun"){					   
			$meta = AR_printer_meta::getMeta($this->printer_id,['printer_user','printer_ukey','printer_sn','printer_key']);                    
			$printer_user = isset($meta['printer_user'])?$meta['printer_user']['meta_value1']:'';
			$printer_ukey = isset($meta['printer_ukey'])?$meta['printer_ukey']['meta_value1']:'';
			$printer_sn = isset($meta['printer_sn'])?$meta['printer_sn']['meta_value1']:'';
			$printer_key = isset($meta['printer_key'])?$meta['printer_key']['meta_value1']:'';

			$stime = time();
			$sig = sha1($printer_user.$printer_ukey.$stime);

			try {		
			   FPinterface::deletePrinter($printer_user,$stime,$sig,$printer_sn); 
			} catch (Exception $e) {
			   //dump($e->getMessage());
			}
		}

		AR_printer_meta::model()->deleteAll('printer_id=:printer_id',array(
            ':printer_id'=>$this->printer_id            
        ));		

        CCacheData::add();
	}

	public function addFPprinter()
	{
		//$this->addError("printer_user","Test error");
		$stime = time();
		if($this->isNewRecord){			
			try {				
				$sig = sha1($this->printer_user.$this->printer_ukey.$stime);
				$snlist = $this->printer_sn."#".$this->printer_key."#".$this->printer_name;
				FPinterface::addPrinter($this->printer_user,$stime,$sig,$snlist,$this->printer_sn);
			} catch (Exception $e) {
				$this->addError('printer_uuid',$e->getMessage());
			}
		} else {
			try {				
				$sig = sha1($this->printer_user.$this->printer_ukey.$stime);				
				FPinterface::updatePrinter($this->printer_user,$stime,$sig,$this->printer_sn,$this->printer_name,$this->printer_key);
			} catch (Exception $e) {
				$this->addError('printer_uuid',$e->getMessage());
			}
		}		
	}
		
}
/*end class*/
