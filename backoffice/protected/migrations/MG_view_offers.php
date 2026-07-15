<?php
class MG_view_offers extends CDbMigration
{
	public function up()
	{
		if($check = Yii::app()->db->schema->getTable("{{view_offers}}")){
			if($schema = Yii::app()->db->createCommand("SELECT * FROM information_schema.tables 
			where table_name  LIKE '%{{view_offers}}%'
             ; ")->queryRow()){				
				if(strtolower($schema['TABLE_TYPE'])!="view"){
					$this->dropTable("{{view_offers}}");
				}				
			}
		}
		
		$stmt="						
		CREATE OR REPLACE VIEW {{view_offers}} as
        select 
        'voucher' as discount_type,
        voucher_id as id,
        merchant_id,
        voucher_name as discount_name,
        amount as offer_amount,
        DATE_FORMAT(NOW(),'%Y-%m-%d') as valid_from,
        expiration as valid_to,
        voucher_type as offer_type,
        min_order,
        status,
        visible,
        monday,
        tuesday,
        wednesday,
        thursday,        
        friday,
        saturday,
        sunday
        from {{voucher_new}}

        UNION ALL

        select 
        'offers' as discount_type,
        offers_id  as id,
        merchant_id,
        offer_percentage as discount_name,
        offer_price as offer_amount,
        valid_from ,
        valid_to ,
        offer_type,
        offer_price as min_order,
        status,
        visible,
        1 as monday,
        1 as tuesday,
        1 as wednesday,
        1 as thursday,
        1 as friday,
        1 as saturday,
        1 as sunday
        from 
        {{offers}}
		";		
		$this->execute($stmt);
	}
	
	public function down()
	{
		
	}
}
/*end clas*/