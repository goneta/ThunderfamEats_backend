<?php
class AR_contact extends CActiveRecord
{	

	public $recaptcha_response,$capcha;
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
		return '{{contact}}';
	}
	
	public function primaryKey()
	{
	    return 'id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'email_address'=>t("Email address"),
		    'fullname'=>t("Full name"),
		    'contact_number'=>t("Contact number"),		    
            'country_name'=>t("Country name"),		    
            'message'=>t("Message"),		    
		);
	}
	
	public function rules()
	{
		return array(		  
		  array('email_address,fullname', 'required','message'=> t( Helper_field_required ) ),
		  array('email_address,fullname,contact_number,country_name,message', 'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')), 
		  array('fullname,contact_number,country_name,message','safe'),		  
		  array('email_address','email','message'=>t(Helper_field_email)),
		  array('recaptcha_response','validateCapcha'),	  
		);
	}

	public function validateCapcha()
	{		
		if($this->capcha==1 || $this->capcha==TRUE){
			if(!empty($this->recaptcha_response)){
				try {						
															
					if(empty($this->captcha_secret)){
						$options = OptionsTools::find(array('captcha_secret'));
					    $captcha_secret = isset($options['captcha_secret'])?$options['captcha_secret']:'';													
					} else $captcha_secret = $this->captcha_secret;

					$resp = CRecaptcha::verify($captcha_secret,$this->recaptcha_response);					
				} catch (Exception $e) {
					$err = CRecaptcha::getError();
					// dump($err);die();
					// if($err == "timeout-or-duplicate"){
					// 	$this->addError('recaptcha_response',  t("Captcha expired please re-validate captcha") );
					// } else $this->addError('recaptcha_response', $err );					
				}
			} else $this->addError('recaptcha_response', t("Please validate captcha") );
		}				
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){
			if($this->isNewRecord){
				$this->date_created = CommonUtility::dateNow();					
			}
			$this->ip_address = CommonUtility::userIp();	
						
			return true;
		} else return true;
	}
	
	protected function afterSave()
	{
		parent::afterSave();
        CCacheData::add();

        Yii::import('ext.runactions.components.ERunActions');	
		$cron_key = CommonUtility::getCronKey();		

        $get_params = array( 
            'id'=> $this->id,
            'key'=>$cron_key,            
            'language'=>Yii::app()->language
        );		
        
        if($this->scenario=="insert"){         
            CommonUtility::runActions( CommonUtility::getHomebaseUrl()."/task/aftercontactfilled?".http_build_query($get_params) );
        }

	}

	protected function afterDelete()
	{
		parent::afterDelete();		
        CCacheData::add();
	}
		
}
/*end class*/