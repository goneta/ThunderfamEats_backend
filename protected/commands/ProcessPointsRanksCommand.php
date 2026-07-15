<?php
class ProcessPointsRanksCommand extends CConsoleCommand
{
    public function actionIndex()
    {        
        set_time_limit(0);       
        CommonUtility::SetTimezone();
        CommonUtility::mysqlSetTimezone(); 
       
         Yii::app()->db->createCommand("
         SET @rank := 0;
        SET @prev_points := NULL;

        TRUNCATE TABLE {{customer_points_ranks}};

        INSERT INTO {{customer_points_ranks}}
        SELECT 
            account_id,
            first_name,
            last_name,
            total_earning,
            @rank := @rank + 1 AS rank_position,
            @prev_points := total_earning AS previous_points,
            @total_players := (SELECT COUNT(*) FROM {{view_customer_points}}) AS total_players,
            ROUND(((@total_players - @rank) / @total_players) * 100) AS percentage_better_than
        FROM 
            {{view_customer_points}},
            (SELECT @rank := 0, @prev_points := NULL) AS init
        ORDER BY 
            total_earning DESC;
         ");
                
    }    
}
// end class