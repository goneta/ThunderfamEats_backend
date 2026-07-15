<?php
class MG_view_client_address_locations extends CDbMigration
{
	public function up()
	{
		if($check = Yii::app()->db->schema->getTable("{{view_client_address_locations}}")){
			if($schema = Yii::app()->db->createCommand("SELECT * FROM information_schema.tables 
			where table_name  LIKE '%{{view_client_address_locations}}%'
             ; ")->queryRow()){				
				if(strtolower($schema['TABLE_TYPE'])!="view"){
					$this->dropTable("{{view_client_address_locations}}");
				}				
			}
		}
		
		$stmt="						
		CREATE OR REPLACE VIEW {{view_client_address_locations}} as            
        SELECT
        a.address_id,
        a.client_id,
        a.address_uuid,
        a.formatted_address as street,
        a.address1 as street_number,                
        state.name as state_name,
        city.name as city_name,
        area.name as area_name,
        b.country_name,
        b.shortcode as country_code,
        a.location_name,
        a.delivery_options,
        a.delivery_instructions,
        a.address_label,

        b.country_id,
        state.state_id,
        city.city_id,
        area.area_id,
        a.company as zip_code,
        a.custom_field2 as house_number,
        a.latitude,
        a.longitude,

        a.date_created,
        a.date_modified

        FROM {{client_address}} a
        left JOIN {{location_countries}} b
        ON
        a.country_code = b.country_id

        left JOIN {{location_states}} state
        ON
        a.postal_code = state.state_id

        left JOIN {{location_cities}} city
        ON
        a.address2 = city.city_id

        left JOIN {{location_area}} area
        ON
        a.custom_field1 = area.area_id

        WHERE
        a.address_type='location-based'
		";		
		$this->execute($stmt);
	}
	
	public function down()
	{
		
	}
}
/*end clas*/