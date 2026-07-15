<?php
class MG_view_user_union extends CDbMigration
{
	public function up()
	{
		if($check = Yii::app()->db->schema->getTable("{{view_user_union}}")){
			if($schema = Yii::app()->db->createCommand("SELECT * FROM information_schema.tables 
			where table_name  LIKE '%{{view_user_union}}%'
             ; ")->queryRow()){				
				if(strtolower($schema['TABLE_TYPE'])!="view"){
					$this->dropTable("{{view_user_union}}");
				}				
			}
		}
		
		$stmt="						
		CREATE OR REPLACE VIEW {{view_user_union}} as
        select
        client_uuid as uuid,
        'customer' as user_type,
        first_name,
        last_name,
        avatar,
        path
        from  {{client}}

        UNION ALL

        select
        user_uuid as uuid,
        'merchant' as user_type,
        first_name,
        last_name,
        profile_photo as avatar,
        path
        from  {{merchant_user}}

        UNION ALL

        select
        admin_id_token as uuid,
        'admin' as user_type,
        first_name,
        last_name,
        profile_photo as avatar,
        path
        from  {{admin_user}}

        UNION ALL

        select
        merchant_uuid as uuid,
        'merchant' as user_type,
        restaurant_name as first_name,
        contact_name as last_name,
        logo as avatar,
        path
        from  {{merchant}}
		";		
		$this->execute($stmt);
	}
	
	public function down()
	{
		
	}
}
/*end clas*/