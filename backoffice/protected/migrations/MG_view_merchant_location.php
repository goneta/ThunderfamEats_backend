<?php
class MG_view_merchant_location extends CDbMigration
{
	public function up()
	{
		if($check = Yii::app()->db->schema->getTable("{{view_merchant_location}}")){
			if($schema = Yii::app()->db->createCommand("SELECT * FROM information_schema.tables 
			where table_name  LIKE '%{{view_merchant_location}}%'
             ; ")->queryRow()){				
				if(strtolower($schema['TABLE_TYPE'])!="view"){
					$this->dropTable("{{view_merchant_location}}");
				}				
			}
		}
		
		$stmt="						
		CREATE OR REPLACE VIEW {{view_merchant_location}} as
        SELECT
        a.id,
        a.merchant_id,
        b.country_name,
        state.name as state_name,
        city.name as city_name,
        area.name as area_name,
        a.created_at

        FROM {{merchant_location}} a
        left JOIN {{location_countries}} b
        ON
        a.country_id = b.country_id

        left JOIN {{location_states}} state
        ON
        a.state_id = state.state_id

        left JOIN {{location_cities}} city
        ON
        a.city_id = city.city_id

        left JOIN {{location_area}} area
        ON
        a.area_id = area.area_id
		";		
		$this->execute($stmt);
	}
	
	public function down()
	{
		
	}
}
/*end clas*/