<?php
class MG_view_cuisine_merchant extends CDbMigration
{
	public function up()
	{
		if($check = Yii::app()->db->schema->getTable("{{view_cuisine_merchant}}")){
			if($schema = Yii::app()->db->createCommand("SELECT * FROM information_schema.tables 
			where table_name  LIKE '%{{view_cuisine_merchant}}%'
             ; ")->queryRow()){				
				if(strtolower($schema['TABLE_TYPE'])!="view"){
					$this->dropTable("{{view_cuisine_merchant}}");
				}				
			}
		}
		
		$stmt="		
		CREATE OR REPLACE VIEW {{view_cuisine_merchant}} as
		SELECT 
        cm.merchant_id,
			vc.language,
			GROUP_CONCAT(vc.cuisine_name ORDER BY vc.cuisine_name SEPARATOR ',') AS cuisines
		FROM {{cuisine_merchant}} cm
		JOIN {{view_cuisine}} vc ON cm.cuisine_id = vc.cuisine_id			
		GROUP BY 
		cm.merchant_id,vc.language;
		";		
		$this->execute($stmt);
	}
	
	public function down()
	{
		
	}
}
/*end clas*/