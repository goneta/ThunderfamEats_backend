<?php
set_time_limit(0);
class SponsoredExpiryCommand extends CConsoleCommand
{
    public function actionIndex()
    {                
        CommonUtility::SetTimezone();
        CommonUtility::mysqlSetTimezone(); 
        
        $model = AR_merchant::model()->findAll("is_sponsored=:is_sponsored AND sponsored_expiration<=:sponsored_expiration",[
            ':is_sponsored'=>2,
            ':sponsored_expiration'=>date("Y-m-d")
        ]);
        if($model){
            foreach ($model as $items) {                
                $items->is_sponsored = 1;
                $items->save();
            }
        }
    }
}
// end class