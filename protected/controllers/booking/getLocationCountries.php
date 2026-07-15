<?php
class getLocationCountries extends CAction
{
    public $_controller;
    public $_id;
    public $data;

    public function __construct($controller,$id)
    {
       $this->_controller=$controller;
       $this->_id=$id;
    }

    public function run()
    {        
        try {

            $options = OptionsTools::find(array('mobilephone_settings_country','mobilephone_settings_default_country'));		            
            $phone_default_country = isset($options['mobilephone_settings_default_country'])?$options['mobilephone_settings_default_country']:'us';
            $phone_country_list = isset($options['mobilephone_settings_country'])?$options['mobilephone_settings_country']:'';
            $phone_country_list = !empty($phone_country_list)?json_decode($phone_country_list,true):array();        

            $shortcode = CCheckout::getPhoneCodeByUserID(Yii::app()->user->id);
            if($shortcode){
                $phone_default_country = $shortcode;
            }		

            $filter = array(
                'only_countries'=>(array)$phone_country_list
            );

            $data = ClocationCountry::listing($filter);
			$default_data = ClocationCountry::get($phone_default_country);

            $this->_controller->code = 1;
		    $this->_controller->msg = "OK";
            $this->_controller->details = array(		                     
               'data'=>$data,   
               'default_data'=>$default_data            
            );    
            
		} catch (Exception $e) {            
			$this->_controller->msg[] = t($e->getMessage());							
		}			
		$this->_controller->responseJson();
    }
}
// end class