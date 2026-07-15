<?php

use function Clue\StreamFilter\append;

set_time_limit(0);

class TaskexchangerateController extends SiteCommon
{	
	private $runactions_enabled;

	public function beforeAction($action)
	{	        
		$key = Yii::app()->input->get("key");			
		if(CRON_KEY===$key){
           $this->runactions_enabled = isset(Yii::app()->params['settings']['runactions_enabled'])?Yii::app()->params['settings']['runactions_enabled']:false;		
		   return true;
		}
		return false;
	}

    public function actionIndex()
	{		        
		Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->GetRate();
			}		
		} else {
			$this->GetRate();
		}        
	}   

    public function GetRate()
    {                
        $error = ''; $data = [];                

        $options = OptionsTools::find([
            'multicurrency_enabled','multicurrency_provider','multicurrency_apikey'
        ]);        
        $enabled = isset($options['multicurrency_enabled'])?$options['multicurrency_enabled']:false;
        $enabled = $enabled==1?true:false;
        $provider = isset($options['multicurrency_provider'])?$options['multicurrency_provider']:'';
        $apikey = isset($options['multicurrency_apikey'])?$options['multicurrency_apikey']:'';
        if($enabled){            


            $all_currency =  CommonUtility::getDataToDropDown("{{currency}}",'currency_code','currency_code',
            "WHERE status='publish'","ORDER BY currency_code ASC");
                        

            switch ($provider) {
                case 'free_currency':       
                    try {                        
                        
                        if(is_array($all_currency) && count($all_currency)>=1){
                            $currencies = implode(',', $all_currency);                             
                            foreach ($all_currency as $currencycode) {                                
                                if(!empty($currencycode)){
                                    try {                                                                      
                                        $result = self::CurrencyFreaks($currencycode,$apikey,$currencies);                                                                                          
                                        CMulticurrency::clearExchangeTable($provider,$currencycode);                      
                                        $data = [];
                                        foreach ($result as $currency_code => $exchange_rate) {
                                            $data[] = [
                                                'provider'=>$provider,
                                                'base_currency'=>$currencycode,
                                                'currency_code'=>$currency_code,
                                                'exchange_rate'=>$exchange_rate,  
                                                'date_created'=>CommonUtility::dateNow(),
                                                'date_modified'=>CommonUtility::dateNow(),
                                                'ip_address'=>CommonUtility::userIp(),
                                            ];                                                                        
                                        }     
                                        $builder=Yii::app()->db->schema->commandBuilder;
                                        $command=$builder->createMultipleInsertCommand("{{currency_exchangerate}}",$data);
                                        $command->execute();    
                                    } catch (Exception $e) {
                                        $error = $e->getMessage();
                                        dump($error);
                                    }
                                }
                            }
                        }

                    } catch (Exception $e) {
		                $error = $e->getMessage();
                        dump($error);
		            }					                              
                    break;
                
                default:                    
                    break;
            }
        }
    }    

    public function actionsingleupdate()
    {
        Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->singleUpdate();
			}		
		} else {
			$this->singleUpdate();
		}         
    }

    public function singleUpdate()
    {
        $base_currency = Yii::app()->input->get('base_currency');        
        $error = ''; $data = [];   
        
        if(empty($base_currency)){
            die();
        }

        $options = OptionsTools::find([
            'multicurrency_enabled','multicurrency_provider','multicurrency_apikey'
        ]);        
        $enabled = isset($options['multicurrency_enabled'])?$options['multicurrency_enabled']:false;
        $enabled = $enabled==1?true:false;
        $provider = isset($options['multicurrency_provider'])?$options['multicurrency_provider']:'';
        $apikey = isset($options['multicurrency_apikey'])?$options['multicurrency_apikey']:'';
        if($enabled){ 
            switch ($provider) {
                case 'free_currency': 
                    try {

                        $all_currency =  CommonUtility::getDataToDropDown("{{currency}}",'currency_code','currency_code',
                        "WHERE status='publish'","ORDER BY currency_code ASC");

                        $currencies = '';
                        if(is_array($all_currency) && count($all_currency)>=1){
                            $currencies = implode(',', $all_currency);
                        }                    

                        $result = self::CurrencyFreaks($base_currency,$apikey,$currencies);                         
                        CMulticurrency::clearExchangeTable($provider,$base_currency);                      
                        $data = [];
                        foreach ($result as $currency_code => $exchange_rate) {
                            $data[] = [
                                'provider'=>$provider,
                                'base_currency'=>$base_currency,
                                'currency_code'=>$currency_code,
                                'exchange_rate'=>$exchange_rate,  
                                'date_created'=>CommonUtility::dateNow(),
                                'date_modified'=>CommonUtility::dateNow(),
                                'ip_address'=>CommonUtility::userIp(),
                            ];                                                                        
                        }                             
                        $builder=Yii::app()->db->schema->commandBuilder;
                        $command=$builder->createMultipleInsertCommand("{{currency_exchangerate}}",$data);
                        $command->execute(); 
                    } catch (Exception $e) {
		                $error = $e->getMessage();
                        dump($error);
		            }	
                    break;
            }
        }
    }

    public static function CurrencyFreaks($base_currency='', $apikey='',$currencies='')
    {                        
        $params = [
            'apikey'=>$apikey,
            'base_currency'=>$base_currency,            
        ];               
        
        if(!empty($currencies)){
            $params['currencies'] = $currencies;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.freecurrencyapi.com/v1/latest?".http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);        
        if (curl_errno($ch)) {            
            throw new Exception( curl_error($ch) );
        }        
        curl_close($ch);
        if(!empty($result)){
            $data = json_decode($result,true);            
            if(isset($data['data'])){
                return $data['data'];
            } else {
                throw new Exception( $data['message'] );            
            }            
        }
        throw new Exception( "No response from provider" );
    }

}
// end class