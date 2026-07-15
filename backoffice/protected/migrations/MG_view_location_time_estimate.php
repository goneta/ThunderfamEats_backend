<?php
class MG_view_location_time_estimate extends CDbMigration
{
	public function up()
	{
		if($check = Yii::app()->db->schema->getTable("{{view_location_time_estimate}}")){
			if($schema = Yii::app()->db->createCommand("SELECT * FROM information_schema.tables 
			where table_name  LIKE '%{{view_location_time_estimate}}%'
             ; ")->queryRow()){				
				if(strtolower($schema['TABLE_TYPE'])!="view"){
					$this->dropTable("{{view_location_time_estimate}}");
				}				
			}
		}
		
		$stmt="						
		CREATE OR REPLACE VIEW {{view_location_time_estimate}} as            
        SELECT
        a.id,
        a.service_type,
        a.merchant_id,
        b.country_name,
        state.name as state_name,
        city.name as city_name,
        area.name as area_name,
        a.estimated_time_min,
        a.estimated_time_max,
        a.created_at

        FROM st_location_time_estimate a
        left JOIN st_location_countries b
        ON
        a.country_id = b.country_id

        left JOIN st_location_states state
        ON
        a.state_id = state.state_id

        left JOIN st_location_cities city
        ON
        a.city_id = city.city_id

        left JOIN st_location_area area
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