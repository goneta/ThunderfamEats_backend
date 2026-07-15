<?php
class WidgetLanguageselection extends CWidget 
{	
	public function run() {		        
                
        $dependency = CCacheData::dependency();        
        $model = AR_language::model()->cache(Yii::app()->params->cache, $dependency)->find("code=:code",array(
            ':code'=>Yii::app()->language
        ));           

        $cacheKey = 'cache_language_data';
        $data = Yii::app()->cache->get($cacheKey);
                
        if ($data === false) {            
            $criteria=new CDbCriteria();        
            $criteria->condition = "status=:status ";		    
            $criteria->params  = array(			  
                ':status'=>'publish'
            );
            $criteria->order ="sequence ASC";
            $data = AR_language::model()->cache(Yii::app()->params->cache, $dependency)->findAll($criteria); 
            Yii::app()->cache->set($cacheKey, $data, CACHE_LONG_DURATION);
        } 

        $enabled =  isset(Yii::app()->params['settings']['enabled_language_bar'])?Yii::app()->params['settings']['enabled_language_bar']:false;
        $enabled = $enabled==1?true:false;              
        
        if($enabled){
            $this->render('language_selection', array(
                'data'=>$data,
                'flag'=>$model?$model->flag:'us',
                'current_lang'=>Yii::app()->language,
            ));
        }
	}
	
}
/*end class*/