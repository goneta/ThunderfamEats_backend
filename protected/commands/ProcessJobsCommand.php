<?php
set_time_limit(0);

class ProcessJobsCommand extends CConsoleCommand
{
    public function actionIndex()
    {                
        CommonUtility::SetTimezone();
        CommonUtility::mysqlSetTimezone();        
        
        Yii::app()->db->createCommand("
            DELETE FROM {{job_queue}} 
            WHERE status = 'completed' 
            AND created_at < NOW() - INTERVAL 20 MINUTE
        ")->execute();        

        $criteria = new CDbCriteria();
        $criteria->condition = 'status=:status';
        $criteria->params = [':status' => 'pending'];        
        $criteria->order = 'id ASC';
        $criteria->limit = 50;
        $model = AR_job_queue::model()->findAll($criteria);
        if($model){            
            foreach ($model as $items) {
                try {
                    $jobData = json_decode($items->job_data, true);
                    $jobClass = $items->job_name;

                    if (!class_exists($jobClass)) {
                        throw new Exception("Job class $jobClass does not exist.");
                    }
                    $jobInstance = new $jobClass($jobData);
                    $jobInstance->execute();

                    $items->status='completed';
                } catch (Throwable $e) {
                    $items->status = $e->getMessage();
                }
                
                if (!$items->save()) {                 
                    Yii::log("Failed to save job with ID {$items->id}", 'error');
                }
            }            
        } 
    }
}
// end class