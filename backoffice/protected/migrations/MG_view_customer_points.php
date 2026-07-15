<?php
class MG_view_customer_points extends CDbMigration
{
	public function up()
	{
		if($check = Yii::app()->db->schema->getTable("{{view_customer_points}}")){
			if($schema = Yii::app()->db->createCommand("SELECT * FROM information_schema.tables 
			where table_name  LIKE '%{{view_customer_points}}%'
             ; ")->queryRow()){				
				if(strtolower($schema['TABLE_TYPE'])!="view"){
					$this->dropTable("{{view_customer_points}}");
				}				
			}
		}
		
		$stmt="						
		CREATE OR REPLACE VIEW {{view_customer_points}} AS 						
		SELECT 
			a.card_id,
			b.account_id,
			c.first_name,
			c.last_name,
			SUM(
				CASE 
					WHEN a.transaction_type IN ('points_earned', 'points_signup', 'points_firstorder', 'points_review', 'points_booking') 
					THEN a.transaction_amount 					
				END
			) AS total_earning
		FROM 
			{{wallet_transactions}} a
		LEFT JOIN 
			{{wallet_cards}} b 
			ON a.card_id = b.card_id
		LEFT JOIN 
			{{client}} c
			ON b.account_id = c.client_id
		WHERE 
			b.account_type = 'customer_points'
		GROUP BY 
			a.card_id, b.account_id, c.first_name, c.last_name
		ORDER BY 
			a.card_id DESC;
		";		
		$this->execute($stmt);
	}
	
	public function down()
	{
		
	}
}
/*end clas*/