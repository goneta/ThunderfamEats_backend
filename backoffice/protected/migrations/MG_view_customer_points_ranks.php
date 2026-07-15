<?php
class MG_view_customer_points_ranks extends CDbMigration
{
	public function up()
	{
		if($check = Yii::app()->db->schema->getTable("{{view_customer_points_ranks}}")){
			if($schema = Yii::app()->db->createCommand("SELECT * FROM information_schema.tables 
			where table_name  LIKE '%{{view_customer_points_ranks}}%'
             ; ")->queryRow()){				
				if(strtolower($schema['TABLE_TYPE'])!="view"){
					$this->dropTable("{{view_customer_points_ranks}}");
				}				
			}
		}
		
		$stmt="						
		CREATE OR REPLACE VIEW {{view_customer_points_ranks}} AS 						
		SELECT 
			account_id,
			first_name,
			last_name,
			total_earning,
			@rank := @rank + 1 AS rank,
			@prev_points := total_earning AS previous_points,
			@total_players := (SELECT COUNT(*) FROM {{view_customer_points}}) AS total_players,
			ROUND(((@total_players - @rank) / @total_players) * 100) AS percentage_better_than
		FROM 
			{{view_customer_points}},
			(SELECT @rank := 0, @prev_points := NULL) AS init			
		ORDER BY 
			total_earning DESC;
		";		
		$this->execute($stmt);
	}
	
	public function down()
	{
		
	}
}
/*end clas*/