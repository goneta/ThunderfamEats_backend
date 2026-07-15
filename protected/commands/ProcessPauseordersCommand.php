<?php
class ProcessPauseordersCommand extends CConsoleCommand
{
    public function actionIndex()
    {        
        set_time_limit(0);                 
        CommonUtility::SetTimezone();
        CommonUtility::mysqlSetTimezone(); 
        //Yii::log('Starting pause orders', 'info');

        $criteria = new CDbCriteria;
        $criteria->addCondition('meta_name=:meta_name');
		$criteria->params = [
			':meta_name'=>'pause_order',			
		];
        $criteria->limit = 100;
        $model = AR_merchant_meta::model()->findAll($criteria);	
        if($model){
            foreach ($model as $items) {                
                $merchant_id = $items->merchant_id;
                $timezone = $items->meta_value2;
                if(!empty($timezone)){                                        
                    Yii::app()->timezone = $timezone;
                }            
                $start_time = new DateTime($items->meta_value);
                $end_time = new DateTime($items->meta_value1);
                $end_time->add(new DateInterval('PT1M'));
                $current_time = new DateTime();                
                
                // echo 'Start Time: ' . $start_time->format('Y-m-d H:i:s') . "\n";
                // echo 'End Time: ' . $end_time->format('Y-m-d H:i:s') . "\n";
                // echo 'Current Time: ' . $current_time->format('Y-m-d H:i:s') . "\n";

                if ($current_time > $end_time) {                    
                    //Yii::log('has already passed.', 'info');
                    $this->setStoreStatus($merchant_id);
                } else {
                    //Yii::log('not yet passed', 'info');                    
                }            
            }
        } else {            
            //Yii::log('Pause orders no results', 'info');    
        }
        //Yii::log('Finished pause orders', 'info');
    }    

    public function setStoreStatus($merchant_id=0)
    {
            $accepting_order = true;
			AR_merchant_meta::saveMeta($merchant_id,'accepting_order',$accepting_order);
			AR_merchant_meta::saveMeta($merchant_id,'pause_time','');
    		AR_merchant_meta::saveMeta($merchant_id,'pause_reason','');
    		
			AR_merchant_meta::saveMeta($merchant_id,'store_status','open');
            Yii::app()->db->createCommand(
                "DELETE FROM {{merchant_meta}}
                WHERE merchant_id=".$merchant_id."
                AND meta_name='pause_order'
                "
            )->query();

    		try {
    		   $merchant = CMerchants::get($merchant_id);
    		   $merchant->pause_ordering = false;    		   
    		   $merchant->save();
    		} catch (Exception $e) {
    		   //	
            }
    }
}
// end class