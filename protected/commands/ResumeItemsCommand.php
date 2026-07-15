<?php
set_time_limit(0);

class ResumeItemsCommand extends CConsoleCommand
{
    public function actionIndex()
    {                
        CommonUtility::SetTimezone();
        CommonUtility::mysqlSetTimezone(); 
        
        $stmt = "
            UPDATE {{item}} 
            SET unavailable_until = NULL,
                available=1,
                status='publish'
            WHERE unavailable_until IS NOT NULL 
                AND unavailable_until < NOW();
        ";

        $command = Yii::app()->db->createCommand($stmt);
        $rowsAffected = $command->execute(); 

        if ($rowsAffected > 0) {
            // Rows were affected, handle accordingly
            echo "{$rowsAffected} rows updated."; 
            CCacheData::add();
        } else {
            // No rows were affected
            echo "No items were updated.";
        }
    }
}
// end class