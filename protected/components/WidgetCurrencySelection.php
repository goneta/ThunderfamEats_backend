<?php
class WidgetCurrencySelection extends CWidget 
{	
	public function run() {		                                
        $enabled = isset(Yii::app()->params['settings']['multicurrency_enabled'])?Yii::app()->params['settings']['multicurrency_enabled']:false;
        $enabled = $enabled==1?true:false;		        
        if($enabled){
            $this->render('currency_selection', array(                
            ));
        }
	}

}
// end class