<?php
class MG_view_location_rates extends CDbMigration
{
	public function up()
	{
		if($check = Yii::app()->db->schema->getTable("{{view_location_rates}}")){
			if($schema = Yii::app()->db->createCommand("SELECT * FROM information_schema.tables 
			where table_name  LIKE '%{{view_location_rates}}%'
             ; ")->queryRow()){				
				if(strtolower($schema['TABLE_TYPE'])!="view"){
					$this->dropTable("{{view_location_rates}}");
				}				
			}
		}
		
		$stmt="						
		CREATE OR REPLACE VIEW {{view_location_rates}} as
        SELECT
        a.rate_id,
        a.merchant_id,
        b.country_name,
        state.name as state_name,
        city.name as city_name,
        area.name as area_name,
        a.fee as delivery_fee,
        a.minimum_order as minimum_amount,
        a.maximum_amount,
        a.free_above_subtotal,
        a.date_created

        FROM {{location_rate}} a
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