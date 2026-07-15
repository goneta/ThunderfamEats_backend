<?php
set_time_limit(0);
$table = new TableDataStatus();
$data = [];

// VERSION 1.0.1
ob_start();
if(Yii::app()->db->schema->getTable("{{notifications}}")){
    $data[] = $table->add_Column("{{notifications}}",array(
        'status'=>"varchar(50) NOT NULL DEFAULT 'pending'",
        'response'=>"text",
    ));
}

// VERSION 1.0.2
if(Yii::app()->db->schema->getTable("{{item}}")){
    $data[] = $table->add_Column("{{item}}",array(
        'slug'=>"varchar(255) NOT NULL DEFAULT ''",        
    ));
}

if(Yii::app()->db->schema->getTable("{{category}}")){
    $data[] = $table->add_Column("{{category}}",array(
        'icon'=>"varchar(255) DEFAULT ''",        
        'icon_path'=>"varchar(255) NOT NULL DEFAULT ''",  
    ));
}

if(Yii::app()->db->schema->getTable("{{favorites}}")){
    $data[] = $table->add_Column("{{favorites}}",array(
        'item_id'=>"int(14) NOT NULL DEFAULT '0'",        
        'cat_id'=>"int(14) NOT NULL DEFAULT '0'",  
    ));
}

if(!Yii::app()->db->schema->getTable("{{subscriber}}")){    
    $table->createTable(
        "{{subscriber}}",
        array(
          'id'=>'pk',		        
          'email_address'=>"varchar(100) NOT NULL DEFAULT ''",          
          'merchant_id'=>"int(14) NOT NULL DEFAULT '0'", 
          'subcsribe_type'=>"varchar(50) NOT NULL DEFAULT 'website'", 
          'date_created'=>"timestamp NULL DEFAULT NULL", 
          'ip_address'=>"varchar(50) NOT NULL DEFAULT ''", 
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');
    $table->create_Index("{{subscriber}}",array(
        'email_address','merchant_id','subcsribe_type'
    )); 	    
    $data[] = "{{subscriber}} table created";
} else $data[] = "{{subscriber}} table already exist";


if(!Yii::app()->db->schema->getTable("{{banner}}")){    
    $table->createTable(
        "{{banner}}",
        array(
          'banner_id'=>'pk',		        
          'banner_uuid'=>"varchar(100) NOT NULL DEFAULT ''",          
          'owner'=>"varchar(50) NOT NULL DEFAULT 'admin'", 
          'title'=>"varchar(255) NOT NULL DEFAULT ''", 
          'banner_type'=>"varchar(100) NOT NULL DEFAULT ''", 
          'meta_value1'=>"int(10) NOT NULL DEFAULT '0'", 
          'meta_value2'=>"int(10) NOT NULL DEFAULT '0'", 
          'path'=>"varchar(255) NOT NULL DEFAULT ''", 
          'photo'=>"varchar(255) NOT NULL DEFAULT ''", 
          'sequence'=>"int(10) NOT NULL DEFAULT '0'", 
          'status'=>"int(10) NOT NULL DEFAULT '1'", 
          'date_created'=>"timestamp NULL DEFAULT NULL", 
          'date_modified'=>"timestamp NULL DEFAULT NULL", 
          'ip_address'=>"varchar(50) NOT NULL DEFAULT ''",           
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');

    $table->create_Index("{{banner}}",array(
        'banner_uuid','owner','banner_type'
    )); 	    
    
    $data[] = "{{banner}} table created";
} else $data[] = "{{banner}} table already exist";


if(Yii::app()->db->schema->getTable("{{pages}}")){
    $data[] = $table->add_Column("{{pages}}",array(
        'owner'=>"varchar(50) NOT NULL DEFAULT 'admin'",        
        'merchant_id'=>"int(10) NOT NULL DEFAULT '0'",  
    ));
}

if(Yii::app()->db->schema->getTable("{{menu}}")){
    $data[] = $table->add_Column("{{menu}}",array(
        'meta_value1'=>"int(10) NOT NULL DEFAULT '0'",        
    ));
}

if(Yii::app()->db->schema->getTable("{{client}}")){
    $data[] = $table->add_Column("{{client}}",array(
        'merchant_id'=>"int(10) NOT NULL DEFAULT '0'",        
    ));
}

if(!Yii::app()->db->schema->getTable("{{addons}}")){    
    $table->createTable(
        "{{addons}}",
        array(
          'id'=>'pk',		        
          'addon_name'=>"varchar(255) NOT NULL DEFAULT ''",
          'uuid'=>"varchar(255) NOT NULL DEFAULT ''",
          'version'=>"varchar(5) NOT NULL DEFAULT ''",
          'activated'=>"int(1) NOT NULL DEFAULT '0'",
          'image'=>"varchar(100) NOT NULL DEFAULT ''",
          'path'=>"varchar(255) NOT NULL DEFAULT ''",
          'purchase_code'=>"varchar(50) NOT NULL DEFAULT ''",
          'date_created'=>"timestamp NULL DEFAULT NULL",
          'date_modified'=>"timestamp NULL DEFAULT NULL",
          'ip_address'=>"varchar(50) NOT NULL DEFAULT ''",          
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');

    $table->create_Index("{{addons}}",array(
        'uuid'
    )); 	        
    $data[] = "{{addons}} table created";
} else $data[] = "{{addons}} table already exist";

// VERSION 1.0.3
if(Yii::app()->db->schema->getTable("{{cuisine}}")){
    $data[] = $table->add_Column("{{cuisine}}",array(
        'icon'=>"varchar(255) NOT NULL DEFAULT ''",  
        'icon_path'=>"varchar(255) NOT NULL DEFAULT ''",         
    ));
}

if(Yii::app()->db->schema->getTable("{{payment_gateway}}")){
    $data[] = $table->add_Column("{{payment_gateway}}",array(        
        'attr5'=>"varchar(255) NOT NULL DEFAULT ''", 
        'attr6'=>"varchar(255) NOT NULL DEFAULT ''", 
        'attr7'=>"varchar(255) NOT NULL DEFAULT ''", 
        'attr8'=>"varchar(255) NOT NULL DEFAULT ''", 
        'attr9'=>"text", 
        'capture'=>"smallint(1) NOT NULL DEFAULT '0'",      
        'split'=>"smallint(1) NOT NULL DEFAULT '0'",  
    ));
}

if(Yii::app()->db->schema->getTable("{{payment_gateway_merchant}}")){
    $data[] = $table->add_Column("{{payment_gateway_merchant}}",array(     
        'attr5'=>"varchar(255) NOT NULL DEFAULT ''",    
        'attr6'=>"varchar(255) NOT NULL DEFAULT ''", 
        'attr7'=>"varchar(255) NOT NULL DEFAULT ''", 
        'attr8'=>"varchar(255) NOT NULL DEFAULT ''", 
        'attr9'=>"text", 
        'capture'=>"smallint(1) NOT NULL DEFAULT '0'",      
        'split'=>"smallint(1) NOT NULL DEFAULT '0'",  
    ));
}

if(Yii::app()->db->schema->getTable("{{push}}")){
    $data[] = $table->add_Column("{{push}}",array(
        'image'=>"varchar(255) NOT NULL DEFAULT ''",  
        'path'=>"varchar(255) NOT NULL DEFAULT ''",         
    ));
}

if(Yii::app()->db->schema->getTable("{{order_status}}")){
    $data[] = $table->add_Column("{{order_status}}",array(
        'group_name'=>"varchar(100) NOT NULL DEFAULT 'order_status'",          
    ));
}

if($check = Yii::app()->db->schema->getTable("{{view_order_status}}")){
    if($schema = Yii::app()->db->createCommand("SELECT * FROM information_schema.tables 
    where table_name  LIKE '%{{view_order_status}}%'
     ; ")->queryRow()){				
        if(strtolower($schema['TABLE_TYPE'])!="view"){
            $table->dropTable("{{view_order_status}}");
        }				
    }
}

$stmt="		
CREATE OR REPLACE VIEW {{view_order_status}} as
select
a.stats_id,
a.group_name,
a.description,
IFNULL(b.language,'en') as language,
IF(b.description IS NULL or b.description = '',a.description,b.description) as description_trans

from  {{order_status}} a
left join  {{order_status_translation}} b
on
a.stats_id = b.stats_id
";		
$table->execute($stmt);
$data[] = "{{view_order_status}} table created";

$stmt="		
CREATE OR REPLACE VIEW {{view_status_management}} as
select
a.status_id,a.group_name,a.status,a.title,a.color_hex,font_color_hex,

IFNULL(b.language,'en') as language,
IF(b.title IS NULL or b.title = '',a.title,b.title) as title_trans

from  {{status_management}} a
left join  {{status_management_translation}} b
on
a.status_id = b.status_id
";		
$table->execute($stmt);
$data[] = "{{view_status_management}} table created";

if(Yii::app()->db->schema->getTable("{{ordernew}}")){
    $data[] = $table->add_Column("{{ordernew}}",array(
        'delivery_status'=>"varchar(255) NOT NULL DEFAULT 'unassigned'",          
        'vehicle_id'=>"int(14) NOT NULL DEFAULT '0'",          
        'request_from'=>"varchar(50) NOT NULL DEFAULT 'web'",          
        'delivered_at'=>"datetime DEFAULT NULL",          
    ));
}

if(Yii::app()->db->schema->getTable("{{ordernew_history}}")){
    $data[] = $table->add_Column("{{ordernew_history}}",array(
        'latitude'=>"varchar(100) NOT NULL DEFAULT ''",  
        'longitude'=>"varchar(100) NOT NULL DEFAULT ''",        
    ));
}

if(Yii::app()->db->schema->getTable("{{notifications}}")){
    $data[] = $table->add_Column("{{notifications}}",array(
        'visible'=>"smallint(1) NOT NULL DEFAULT '1'",          
        'viewed'=>"tinyint(1) NOT NULL DEFAULT '0'",  
    ));
}

if(Yii::app()->db->schema->getTable("{{driver_activity}}")){
    $data[] = $table->add_Column("{{driver_activity}}",array(
        'reference_id'=>"bigint(20) NOT NULL DEFAULT '0'",          
    ));
}

if(Yii::app()->db->schema->getTable("{{offers}}")){
    $data[] = $table->add_Column("{{offers}}",array(
        'offer_type'=>"varchar(50) NOT NULL DEFAULT 'percentage'",          
    ));
}

if(!Yii::app()->db->schema->getTable("{{bank_deposit}}")){    
    $table->createTable(
        "{{bank_deposit}}",
        array(
          'deposit_id'=>'pk',		                  
          'deposit_uuid'=>"varchar(100) NOT NULL DEFAULT ''",
          'status'=>"varchar(100) DEFAULT 'pending'",
          'deposit_type'=>"varchar(50) NOT NULL DEFAULT 'order'",
          'merchant_id'=>"bigint(20) NOT NULL DEFAULT '0'",
          'transaction_ref_id'=>"bigint(20) DEFAULT '0'",
          'account_name'=>"varchar(255) NOT NULL DEFAULT ''",
          'amount'=>"decimal(10,4) NOT NULL DEFAULT '0.00'",
          'reference_number'=>"varchar(100) NOT NULL DEFAULT ''",
          'proof_image'=>"varchar(100) NOT NULL DEFAULT ''",
          'path'=>"varchar(255) NOT NULL DEFAULT ''",
          'date_created'=>"datetime DEFAULT NULL",
          'date_modified'=>"datetime DEFAULT NULL",
          'ip_address'=>"varchar(50) NOT NULL DEFAULT ''",          
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');

    $table->create_Index("{{bank_deposit}}",array(
        'status'
    )); 	    
    $data[] = "{{bank_deposit}} table created";
} else $data[] = "{{bank_deposit}} table already exist";


if($check = Yii::app()->db->schema->getTable("{{view_offers}}")){
    if($schema = Yii::app()->db->createCommand("SELECT * FROM information_schema.tables 
    where table_name  LIKE '%{{view_offers}}%'
     ; ")->queryRow()){				
        if(strtolower($schema['TABLE_TYPE'])!="view"){
            $table->dropTable("{{view_offers}}");
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
status
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
status
from 
{{offers}}
";		
$table->execute($stmt);
$data[] = "{{view_offers}} table created";


// VERSION 1.0.4
if(Yii::app()->db->schema->getTable("{{item_relationship_category}}")){
    $data[] = $table->add_Column("{{item_relationship_category}}",array(
        'sequence'=>"int(14) NOT NULL DEFAULT '0'",          
    ));
}
if(Yii::app()->db->schema->getTable("{{merchant_user}}")){
    $data[] = $table->add_Column("{{merchant_user}}",array(
        'verification_code'=>"int(10) NOT NULL DEFAULT '0'",          
    ));
}
if(Yii::app()->db->schema->getTable("{{notifications}}")){
    $data[] = $table->add_Column("{{notifications}}",array(
        'meta_data'=>"text",          
    ));
}
if(Yii::app()->db->schema->getTable("{{merchant}}")){
    $data[] = $table->add_Column("{{merchant}}",array(
        'allowed_offline_payment'=>"int(1) NOT NULL DEFAULT '0'",          
        'invoice_terms'=>"int(14) NOT NULL DEFAULT '0'",          
    ));
}

if(!Yii::app()->db->schema->getTable("{{invoice}}")){    
    $table->createTable(
        "{{invoice}}",
        array(
          'invoice_number'=>'pk',		                  
          'invoice_uuid'=>"varchar(100) NOT NULL DEFAULT ''",         
          'merchant_id'=>"int(14) NOT NULL DEFAULT '0'",  
          'restaurant_name'=>"varchar(255) NOT NULL DEFAULT ''",  
          'business_address'=>"varchar(255) DEFAULT ''",  
          'contact_email'=>"varchar(200) NOT NULL DEFAULT ''",  
          'contact_phone'=>"varchar(50) NOT NULL DEFAULT ''",  
          'invoice_terms'=>"int(14) NOT NULL DEFAULT '0'",  
          'invoice_total'=>"decimal(10,4) NOT NULL DEFAULT '0.00'",  
          'amount_paid'=>"decimal(10,4) NOT NULL DEFAULT '0.00'",  
          'invoice_created'=>"datetime DEFAULT NULL",  
          'due_date'=>"datetime DEFAULT NULL",  
          'date_from'=>"datetime DEFAULT NULL",  
          'date_to'=>"datetime DEFAULT NULL",  
          'payment_status'=>"varchar(255) NOT NULL DEFAULT 'unpaid'",  
          'viewed'=>"smallint(1) NOT NULL DEFAULT '0'",  
          'date_created'=>"datetime DEFAULT NULL",  
          'date_modified'=>"datetime DEFAULT NULL",  
          'ip_address'=>"varchar(50) NOT NULL DEFAULT ''",  
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');

    $table->create_Index("{{invoice}}",array(
        'invoice_token','merchant_id','date_from','date_to','invoice_total','invoice_terms'
    )); 	        
    $data[] = "{{invoice}} table created";
} else $data[] = "{{invoice}} table already exist";

if(!Yii::app()->db->schema->getTable("{{printer}}")){    
    $table->createTable(
        "{{printer}}",
        array(
          'printer_id'=>'pk',		                  
          'merchant_id'=>"int(14) NOT NULL DEFAULT '0'",
          'device_uuid'=>"varchar(255) NOT NULL DEFAULT ''",
          'printer_name'=>"varchar(100) NOT NULL DEFAULT ''",
          'printer_model'=>"varchar(100) NOT NULL DEFAULT 'bluetooth'",
          'paper_width'=>"int(10) NOT NULL DEFAULT '58'",
          'auto_print'=>"int(1) NOT NULL DEFAULT '0'",
          'date_created'=>"datetime NOT NULL DEFAULT CURRENT_TIMESTAMP",
          'date_modified'=>"datetime NOT NULL DEFAULT CURRENT_TIMESTAMP",
          'ip_address'=>"varchar(50) NOT NULL DEFAULT ''",
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');

    $table->create_Index("{{printer}}",array(
        'merchant_id','device_uuid'
    )); 	        
    $data[] = "{{printer}} table created";
} else $data[] = "{{printer}} table already exist";

if(!Yii::app()->db->schema->getTable("{{printer_meta}}")){    
    $table->createTable(
        "{{printer_meta}}",
        array(
          'printer_id'=>'pk',         
          'printer_id'=>"int(14) NOT NULL DEFAULT '0'",
          'meta_name'=>"varchar(100) NOT NULL DEFAULT ''",
          'meta_value1'=>"varchar(255) NOT NULL DEFAULT ''",
          'meta_value2'=>"text",          
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');

    $table->create_Index("{{printer_meta}}",array(
        'printer_id'
    )); 	        
    $data[] = "{{printer_meta}} table created";
} else $data[] = "{{printer_meta}} table already exist";

if(!Yii::app()->db->schema->getTable("{{invoice_meta}}")){    
    $table->createTable(
        "{{invoice_meta}}",
        array(
          'printer_id'=>'pk',         
          'invoice_number'=>"int(14) NOT NULL DEFAULT '0'",          
          'meta_name'=>"varchar(255) NOT NULL DEFAULT ''",          
          'meta_value1'=>"varchar(255) NOT NULL DEFAULT ''",          
          'meta_value2'=>"varchar(255) NOT NULL DEFAULT ''",          
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');

    $table->create_Index("{{invoice_meta}}",array(
        'invoice_number'
    )); 	        
    $data[] = "{{invoice_meta}} table created";
} else $data[] = "{{invoice_meta}} table already exist";

// VERSION 1.0.5
if(!Yii::app()->db->schema->getTable("{{table_reservation}}")){    
    $table->createTable(
        "{{table_reservation}}",
        array(
          'reservation_id'=>'pk',         
          'reservation_uuid'=>"varchar(100) NOT NULL DEFAULT ''", 
          'client_id'=>"int(14) DEFAULT '0'", 
          'merchant_id'=>"int(14) NOT NULL DEFAULT '0'", 
          'room_id'=>"int(14) NOT NULL DEFAULT '0'", 
          'table_id'=>"int(14) NOT NULL DEFAULT '0'", 
          'reservation_date'=>"date DEFAULT NULL", 
          'reservation_time'=>"time DEFAULT NULL", 
          'guest_number'=>"int(14) NOT NULL DEFAULT '0'", 
          'special_request'=>"varchar(255) NOT NULL DEFAULT ''", 
          'cancellation_reason'=>"text", 
          'status'=>"varchar(100) NOT NULL DEFAULT 'pending'", 
          'date_created'=>"datetime DEFAULT NULL", 
          'date_modified'=>"datetime DEFAULT NULL", 
          'ip_address'=>"varchar(50) DEFAULT ''",           
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');

    $table->create_Index("{{table_reservation}}",array(
        'merchant_id','reservation_date','table_id','guest_number','status'
    )); 	        
    $data[] = "{{table_reservation}} table created";
} else $data[] = "{{table_reservation}} table already exist";


if(!Yii::app()->db->schema->getTable("{{table_reservation_history}}")){    
    $table->createTable(
        "{{table_reservation_history}}",
        array(
          'id'=>'pk',         
          'created_at'=>"timestamp NULL DEFAULT NULL",          
          'reservation_id'=>"bigint(20) NOT NULL DEFAULT '0'",          
          'status'=>"varchar(255) NOT NULL DEFAULT ''",          
          'remarks'=>"text",          
          'ramarks_trans'=>"text",          
          'change_by'=>"varchar(100) NOT NULL DEFAULT ''",          
          'ip_address'=>"varchar(50) NOT NULL DEFAULT ''",                    
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');

    $table->create_Index("{{table_reservation_history}}",array(
        'reservation_id','status'
    )); 	        
    $data[] = "{{table_reservation_history}} table created";
} else $data[] = "{{table_reservation_history}} table already exist";

if(!Yii::app()->db->schema->getTable("{{table_room}}")){    
    $table->createTable(
        "{{table_room}}",
        array(
          'room_id'=>'pk',         
          'room_uuid'=>"varchar(100) NOT NULL DEFAULT ''",
          'room_name'=>"varchar(255) NOT NULL DEFAULT ''",
          'merchant_id'=>"int(14) NOT NULL DEFAULT '0'",
          'status'=>"varchar(100) NOT NULL DEFAULT 'pending'",
          'date_created'=>"datetime DEFAULT NULL",
          'date_modified'=>"datetime DEFAULT NULL",
          'ip_address'=>"varchar(50) NOT NULL DEFAULT ''",          
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');

    $table->create_Index("{{table_room}}",array(
        'room_uuid','merchant_id','status'
    )); 	        
    $data[] = "{{table_room}} table created";
} else $data[] = "{{table_room}} table already exist";

if(!Yii::app()->db->schema->getTable("{{table_shift}}")){    
    $table->createTable(
        "{{table_shift}}",
        array(
          'shift_id'=>'pk',         
          'shift_uuid'=>"varchar(100) NOT NULL DEFAULT ''",
          'merchant_id'=>"int(14) NOT NULL DEFAULT '0'",
          'shift_name'=>"varchar(255) NOT NULL DEFAULT ''",
          'days_of_week'=>"text",
          'first_seating'=>"time DEFAULT NULL",
          'last_seating'=>"time DEFAULT NULL",
          'shift_interval'=>"int(14) NOT NULL DEFAULT '0'",
          'status'=>"varchar(100) NOT NULL DEFAULT ''",
          'date_created'=>"datetime DEFAULT NULL",
          'date_modified'=>"datetime DEFAULT NULL",
          'ip_address'=>"varchar(50) NOT NULL DEFAULT ''",
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');

    $table->create_Index("{{table_shift}}",array(
        'merchant_id','status','shift_uuid'
    )); 	   
    $data[] = "{{table_shift}} table created";
} else $data[] = "{{table_shift}} table already exist";

if(!Yii::app()->db->schema->getTable("{{table_shift_days}}")){    
    $table->createTable(
        "{{table_shift_days}}",
        array(
          'id'=>'pk',         
          'merchant_id'=>"int(14) NOT NULL DEFAULT '0'",
          'shift_id'=>"int(14) NOT NULL DEFAULT '0'",
          'day_of_week'=>"int(10) NOT NULL DEFAULT '0'",
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');

    $table->create_Index("{{table_shift_days}}",array(
        'merchant_id','shift_id','day_of_week'
    )); 	    
    $data[] = "{{table_shift_days}} table created";
} else $data[] = "{{table_shift_days}} table already exist";

if(!Yii::app()->db->schema->getTable("{{table_tables}}")){
    $table->createTable(
        "{{table_tables}}",
        array(
          'table_id'=>'pk',         
          'table_uuid'=>"varchar(100) NOT NULL DEFAULT ''",
          'merchant_id'=>"int(14) NOT NULL DEFAULT '0'",
          'room_id'=>"int(14) NOT NULL DEFAULT '0'",
          'table_name'=>"varchar(255) NOT NULL DEFAULT ''",
          'min_covers'=>"int(14) NOT NULL DEFAULT '0'",
          'max_covers'=>"int(14) NOT NULL DEFAULT '0'",
          'available'=>"smallint(1) NOT NULL DEFAULT '1'",
          'date_created'=>"datetime DEFAULT NULL",
          'date_modified'=>"datetime DEFAULT NULL",
          'ip_address'=>"varchar(50) NOT NULL DEFAULT ''",
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');

    $table->create_Index("{{table_tables}}",array(
        'merchant_id','room_id','min_covers','max_covers','available'
    )); 	        
    $data[] = "{{table_tables}} table created";
} else $data[] = "{{table_tables}} table already exist";

if(!Yii::app()->db->schema->getTable("{{printer_logs}}")){    
    $table->createTable(
        "{{printer_logs}}",
        array(
          'id'=>'pk',         
          'order_id'=>"bigint(20) NOT NULL DEFAULT '0'",
          'merchant_id'=>"bigint(20) NOT NULL DEFAULT '0'",
          'printer_type'=>"varchar(100) NOT NULL DEFAULT 'feie'",
          'printer_number'=>"varchar(100) NOT NULL DEFAULT ''",
          'print_content'=>"text",
          'job_id'=>"varchar(100) NOT NULL DEFAULT ''",
          'status'=>"varchar(255) NOT NULL DEFAULT 'pending'",
          'date_created'=>"datetime DEFAULT NULL",
          'ip_address'=>"varchar(50) NOT NULL DEFAULT ''",
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');

    $table->create_Index("{{printer_logs}}",array(
        'order_id','merchant_id','printer_type','status'
    )); 	    
    $data[] = "{{printer_logs}} table created";
} else $data[] = "{{printer_logs}} table already exist";

if(!Yii::app()->db->schema->getTable("{{contact}}")){    
    $table->createTable(
        "{{contact}}",
        array(
          'id'=>'pk',         
          'email_address'=>"varchar(255) NOT NULL DEFAULT 'feie'",
          'receiver_email_address'=>"varchar(255) NOT NULL DEFAULT ''",
          'fullname'=>"varchar(255) NOT NULL DEFAULT ''",
          'contact_number'=>"varchar(100) NOT NULL DEFAULT ''",
          'country_name'=>"varchar(100) NOT NULL DEFAULT ''",
          'message'=>"text",
          'date_created'=>"datetime DEFAULT NULL",
          'ip_address'=>"varchar(50) NOT NULL DEFAULT ''",
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');

    $table->create_Index("{{printer_logs}}",array(
        'order_id','merchant_id','printer_type','status'
    )); 	    
    $data[] = "{{printer_logs}} table created";
} else $data[] = "{{printer_logs}} table already exist";

Yii::app()->db->createCommand("ALTER TABLE {{contact}} MODIFY COLUMN id bigint(20) NOT NULL AUTO_INCREMENT")->query();

if(Yii::app()->db->schema->getTable("{{item}}")){
    $data[] = $table->add_Column("{{item}}",array(
        'ingredients_preselected'=>"tinyint(1) NOT NULL DEFAULT '0' AFTER `cooking_ref_required`",          
    ));
}

if(Yii::app()->db->schema->getTable("{{admin_meta_translation}}")){
    $data[] = $table->add_Column("{{admin_meta_translation}}",array(
        'meta_value1'=>"text",          
    ));
}

/* 1.0.6 */
if(Yii::app()->db->schema->getTable("{{printer}}")){
    $data[] = $table->add_Column("{{printer}}",array(
        'service_id'=>"VARCHAR(100) NOT NULL DEFAULT '' AFTER `printer_model`",          
    ));
    $data[] = $table->add_Column("{{printer}}",array(
        'characteristics'=>"VARCHAR(100) NOT NULL DEFAULT '' AFTER `printer_model`",          
    ));
}

/* 1.0.6 */


/* 1.0.7 */
if(!Yii::app()->db->schema->getTable("{{driver}}")){
    $table->createTable(
        "{{driver}}",
        array(
          'driver_id'=>'pk',         
          'merchant_id'=>"bigint(20) NOT NULL DEFAULT '0'",          
          'driver_uuid'=>"varchar(100) NOT NULL DEFAULT ''",
          'token'=>"varchar(255) NOT NULL DEFAULT ''",
          'employment_type'=>"varchar(100) NOT NULL DEFAULT 'employee'",
          'last_seen'=>"timestamp NULL DEFAULT NULL",
          'first_name'=>"varchar(255) NOT NULL DEFAULT ''",
          'last_name'=>"varchar(255) NOT NULL DEFAULT ''",
          'email'=>"varchar(255) NOT NULL DEFAULT ''",
          'phone_prefix'=>"varchar(5) DEFAULT ''",
          'phone'=>"varchar(20) NOT NULL DEFAULT ''",
          'password'=>"varchar(100) NOT NULL DEFAULT ''",
          'photo'=>"varchar(200) NOT NULL DEFAULT ''",
          'team_id'=>"int(14) NOT NULL DEFAULT '0'",
          'address'=>"tinytext",
          'salary_type'=>"varchar(100) NOT NULL DEFAULT 'salary'",
          'salary'=>"decimal(10,2) NOT NULL DEFAULT '0.00'",
          'fixed_amount'=>"decimal(10,2) DEFAULT '0.00'",
          'commission'=>"decimal(10,2) NOT NULL DEFAULT '0.00'",
          'commission_type'=>"varchar(50) NOT NULL DEFAULT 'percentage'",
          'incentives_amount'=>"double(10,2) NOT NULL DEFAULT '0.00'",
          'allowed_offline_amount'=>"double(10,2) NOT NULL DEFAULT '0.00'",
          'license_number'=>"varchar(100) NOT NULL DEFAULT ''",
          'license_expiration'=>"date DEFAULT NULL",
          'license_front_photo'=>"varchar(100) NOT NULL DEFAULT ''",
          'license_back_photo'=>"varchar(100) DEFAULT ''",
          'color_hex'=>"varchar(10) NOT NULL DEFAULT '#3ecf8e'",
          'path'=>"varchar(255) NOT NULL DEFAULT ''",
          'path_license'=>"varchar(255) NOT NULL DEFAULT ''",
          'status'=>"varchar(100) DEFAULT 'active'",
          'latitude'=>"varchar(50) NOT NULL DEFAULT ''",
          'lontitude'=>"varchar(50) NOT NULL DEFAULT ''",
          'delivery_distance_covered'=>"decimal(10,2) NOT NULL DEFAULT '10000.00'",
          'verification_code'=>"int(10) NOT NULL DEFAULT '0'",
          'account_verified'=>"smallint(1) NOT NULL DEFAULT '0'",
          'verify_code_requested'=>"datetime DEFAULT NULL",
          'reset_password_request'=>"smallint(1) NOT NULL DEFAULT '0'",
          'notification'=>"int(1) NOT NULL DEFAULT '1'",
          'date_created'=>"datetime DEFAULT NULL",
          'date_modified'=>"datetime DEFAULT NULL",
          'ip_address'=>"varchar(50) NOT NULL DEFAULT ''"
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');

    $table->create_Index("{{driver}}",array(
        'driver_uuid','token','status','email','team_id','salary_type','latitude','lontitude','delivery_distance_covered'
    )); 	    
    $data[] = "{{driver}} table created";
} else $data[] = "{{driver}} table already exist";

if(!Yii::app()->db->schema->getTable("{{driver_activity}}")){    
    $table->createTable(
        "{{driver_activity}}",
        array(
          'id'=>'pk',         
          'created_at'=>"datetime DEFAULT NULL",
          'date_created'=>"date DEFAULT NULL",
          'driver_id'=>"bigint(20) DEFAULT '0'",
          'order_id'=>"bigint(20) NOT NULL DEFAULT '0'",
          'reference_id'=>"bigint(20) NOT NULL DEFAULT '0'",
          'status'=>"varchar(255) NOT NULL DEFAULT ''",
          'remarks'=>"tinytext",
          'remarks_args'=>"tinytext",
          'latitude'=>"varchar(100) NOT NULL DEFAULT ''",
          'longitude'=>"varchar(100) NOT NULL DEFAULT ''",
          'ip_address'=>"varchar(50) NOT NULL DEFAULT ''"
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');

    $table->create_Index("{{driver_activity}}",array(
        'driver_id','order_id','reference_id'
    )); 	    
    $data[] = "{{driver_activity}} table created";
} else $data[] = "{{driver_activity}} table already exist";

if(!Yii::app()->db->schema->getTable("{{driver_break}}")){    
    $table->createTable(
        "{{driver_break}}",
        array(
          'id'=>'pk',         
          'schedule_id'=>"bigint(20) NOT NULL DEFAULT '0'",
          'driver_id'=>"bigint(20) NOT NULL DEFAULT '0'",
          'break_duration'=>"varchar(50) NOT NULL DEFAULT ''",
          'break_started'=>"datetime DEFAULT NULL",
          'break_ended'=>"datetime DEFAULT NULL",
          'date_created'=>"datetime DEFAULT NULL",
          'ip_address'=>"varchar(50) NOT NULL DEFAULT ''"
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');

    $table->create_Index("{{driver_break}}",array(
        'schedule_id','driver_id'
    )); 	    
    $data[] = "{{driver_break}} table created";
} else $data[] = "{{driver_break}} table already exist";

if(!Yii::app()->db->schema->getTable("{{driver_collect_cash}}")){    
    $table->createTable(
        "{{driver_collect_cash}}",
        array(
          'collect_id'=>'pk', 
          'collection_uuid'=>"varchar(100) NOT NULL DEFAULT ''",
          'reference_id'=>"varchar(255) NOT NULL DEFAULT ''",
          'merchant_id'=>"bigint(20) NOT NULL DEFAULT '0'",
          'driver_id'=>"bigint(20) NOT NULL DEFAULT '0'",
          'amount_collected'=>"decimal(10,4) DEFAULT '0.00'",
          'transaction_date'=>"datetime DEFAULT NULL",
          'date_created'=>"datetime DEFAULT NULL",
          'ip_address'=>"varchar(50) NOT NULL DEFAULT ''"
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');

    $table->create_Index("{{driver_collect_cash}}",array(
        'collection_uuid','reference_id','merchant_id','driver_id'
    )); 	    
    $data[] = "{{driver_collect_cash}} table created";
} else $data[] = "{{driver_collect_cash}} table already exist";


if(!Yii::app()->db->schema->getTable("{{driver_group}}")){    
    $table->createTable(
        "{{driver_group}}",
        array(
          'group_id'=>'pk',
          'merchant_id'=>"bigint(20) NOT NULL DEFAULT '0'",
          'group_uuid'=>"varchar(100) NOT NULL DEFAULT ''",
          'group_name'=>"varchar(100) NOT NULL DEFAULT ''",
          'color_hex'=>"varchar(10) NOT NULL DEFAULT ''",
          'date_created'=>"datetime DEFAULT NULL",
          'date_modified'=>"datetime DEFAULT NULL",
          'ip_address'=>"varchar(50) DEFAULT ''"
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');

    $table->create_Index("{{driver_group}}",array(
        'group_uuid','merchant_id'
    )); 	    
    $data[] = "{{driver_group}} table created";
} else $data[] = "{{driver_group}} table already exist";

if(!Yii::app()->db->schema->getTable("{{driver_group_relations}}")){    
    $table->createTable(
        "{{driver_group_relations}}",
        array(
          'date_created'=>'datetime DEFAULT NULL',
          'group_id'=>"bigint(20) NOT NULL DEFAULT '0'",
          'driver_id'=>"bigint(20) NOT NULL DEFAULT '0'",
          'vehicle_id'=>"bigint(20) NOT NULL DEFAULT '0'"
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');

    $table->create_Index("{{driver_group_relations}}",array(
        'group_id','driver_id','vehicle_id'
    )); 	    
    $data[] = "{{driver_group_relations}} table created";
} else $data[] = "{{driver_group_relations}} table already exist";

if(!Yii::app()->db->schema->getTable("{{driver_meta}}")){    
    $table->createTable(
        "{{driver_meta}}",
        array(
          'meta_id'=>'pk',         
          'merchant_id'=>"bigint(20) NOT NULL DEFAULT '0'",  
          'reference_id'=>"bigint(20) NOT NULL DEFAULT '0'",  
          'meta_name'=>"varchar(100) NOT NULL DEFAULT ''",
          'meta_value1'=>"varchar(255) NOT NULL DEFAULT ''",
          'meta_value2'=>"varchar(255) NOT NULL DEFAULT ''",
          'meta_value3'=>"varchar(255) NOT NULL DEFAULT ''"
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');

    $table->create_Index("{{driver_meta}}",array(
        'merchant_id','reference_id','meta_name'
    )); 	    
    $data[] = "{{driver_meta}} table created";
} else $data[] = "{{driver_meta}} table already exist";

if(!Yii::app()->db->schema->getTable("{{driver_payment_method}}")){    
    $table->createTable(
        "{{driver_payment_method}}",
        array(
          'payment_method_id'=>'pk',         
          'payment_uuid'=>"varchar(100) NOT NULL DEFAULT ''",
          'driver_id'=>"int(14) NOT NULL DEFAULT '0'",
          'merchant_id'=>"bigint(20) DEFAULT '0'",
          'payment_code'=>"varchar(100) NOT NULL DEFAULT ''",
          'as_default'=>"int(1) NOT NULL DEFAULT '0'",
          'reference_id'=>"int(14) NOT NULL DEFAULT '0'",
          'attr1'=>"varchar(255) NOT NULL DEFAULT ''",
          'attr2'=>"varchar(255) NOT NULL DEFAULT ''",
          'attr3'=>"varchar(255) NOT NULL DEFAULT ''",
          'date_created'=>"datetime DEFAULT NULL",
          'date_modified'=>"datetime DEFAULT NULL",
          'ip_address'=>"varchar(50) NOT NULL DEFAULT ''"
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');

    $table->create_Index("{{driver_payment_method}}",array(
        'driver_id','reference_id','payment_uuid','payment_code','merchant_id'
    )); 	    
    $data[] = "{{driver_payment_method}} table created";
} else $data[] = "{{driver_payment_method}} table already exist";

if(!Yii::app()->db->schema->getTable("{{driver_schedule}}")){    
    $table->createTable(
        "{{driver_schedule}}",
        array(
          'schedule_id'=>'pk',           
          'schedule_uuid'=>"varchar(100) NOT NULL DEFAULT ''",
          'merchant_id'=>"bigint(20) NOT NULL DEFAULT '0'",
          'driver_id'=>"int(14) NOT NULL DEFAULT '0'",
          'vehicle_id'=>"int(14) NOT NULL DEFAULT '0'",
          'zone_id'=>"bigint(20) NOT NULL DEFAULT '0'",
          'shift_id'=>"bigint(20) NOT NULL DEFAULT '0'",
          'time_start'=>"datetime DEFAULT NULL",
          'time_end'=>"datetime DEFAULT NULL",
          'shift_time_started'=>"datetime DEFAULT NULL",
          'shift_time_ended'=>"datetime DEFAULT NULL",
          'break_duration'=>"varchar(50) NOT NULL DEFAULT '0'",
          'instructions'=>"text",
          'active'=>"smallint(1) NOT NULL DEFAULT '1'",
          'date_created'=>"timestamp NULL DEFAULT NULL",
          'date_modified'=>"timestamp NULL DEFAULT NULL",
          'ip_address'=>"varchar(50) NOT NULL DEFAULT ''"
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');

    $table->create_Index("{{driver_schedule}}",array(
        'schedule_uuid','driver_id','vehicle_id','zone_id','shift_id','active'
    )); 	    
    $data[] = "{{driver_schedule}} table created";
} else $data[] = "{{driver_schedule}} table already exist";

if(!Yii::app()->db->schema->getTable("{{driver_shift_schedule}}")){    
    $table->createTable(
        "{{driver_shift_schedule}}",
        array(
          'shift_id'=>'pk',         
          'shift_uuid'=>"varchar(100) NOT NULL DEFAULT ''",
          'zone_id'=>"bigint(20) NOT NULL DEFAULT '0'",
          'time_start'=>"datetime DEFAULT NULL",
          'time_end'=>"datetime DEFAULT NULL",
          'max_allow_slot'=>"int(14) NOT NULL DEFAULT '0'",
          'status'=>"varchar(100) NOT NULL DEFAULT 'active'",
          'date_created'=>"datetime DEFAULT NULL",
          'date_modified'=>"datetime DEFAULT NULL",
          'ip_address'=>"varchar(50) NOT NULL DEFAULT ''"
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');

    $table->create_Index("{{printer_logs}}",array(
        'shift_uuid','zone_id'
    )); 	    
    $data[] = "{{driver_shift_schedule}} table created";
} else $data[] = "{{driver_shift_schedule}} table already exist";


if(!Yii::app()->db->schema->getTable("{{driver_vehicle}}")){    
    $table->createTable(
        "{{driver_vehicle}}",
        array(
          'vehicle_id'=>'pk',          
          'vehicle_uuid'=>"varchar(100) NOT NULL DEFAULT ''",
          'vehicle_type_id'=>"int(14) DEFAULT '0'",
          'driver_id'=>"bigint(20) NOT NULL DEFAULT '0'",
          'plate_number'=>"varchar(100) NOT NULL DEFAULT ''",
          'maker'=>"varchar(100) NOT NULL DEFAULT ''",
          'model'=>"varchar(100) NOT NULL DEFAULT ''",
          'color'=>"varchar(50) NOT NULL DEFAULT ''",
          'photo'=>"varchar(100) NOT NULL DEFAULT ''",
          'path'=>"varchar(200) NOT NULL DEFAULT ''",
          'active'=>"smallint(1) NOT NULL DEFAULT '1'",
          'date_created'=>"datetime DEFAULT NULL",
          'date_modified'=>"datetime DEFAULT NULL",
          'ip_address'=>"varchar(50) NOT NULL DEFAULT ''"
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');

    $table->create_Index("{{driver_vehicle}}",array(
        'vehicle_uuid','active','vehicle_type_id','driver_id'
    )); 	    
    $data[] = "{{driver_vehicle}} table created";
} else $data[] = "{{driver_vehicle}} table already exist";


if(!Yii::app()->db->schema->getTable("{{cron}}")){    
    $table->createTable(
        "{{cron}}",
        array(
          'cron_id'=>'pk',          
          'url'=>"text",
          'status'=>"smallint(6) NOT NULL DEFAULT '0'",
          'date_created'=>"datetime DEFAULT NULL",          
          'date_modified'=>"datetime DEFAULT NULL", 
          'ip_address'=>"varchar(50) NOT NULL DEFAULT ''"
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');

    $table->create_Index("{{cron}}",array(
        'status'
    )); 	    
    $data[] = "{{cron}} table created";
} else $data[] = "{{cron}} table already exist";

/* 1.0.7 */

// END CREATE TABLE


// UPDATE COD ATTRIBUTES
$model = AR_payment_gateway::model()->find("payment_code=:payment_code",[
    ':payment_code'=>'cod'
]);
if($model){    
    $model->attr_json = '{"attr1":{"label":"Change required, if required value = 1"},"attr2":{"label":"Maximum limit"}}';
    $model->save();    
}

$model = AR_payment_gateway_merchant::model()->find("payment_code=:payment_code",[
    ':payment_code'=>'cod'
]);
if($model){
    $model->attr_json = '{"attr1":{"label":"Change required, if required value = 1"},"attr2":{"label":"Maximum limit"}}';
    $model->save();
}


// INSERT TEMPLATE
$model = AR_templates::model()->find("template_name=:template_name",[':template_name'=>'Delivery arrived at restaurant']);
if(!$model){
    $model = new AR_templates();
    $model->template_name = 'Delivery arrived at restaurant';
    $model->enabled_email = 0;
    $model->enabled_sms = 0;
    $model->enabled_push = 1;
    if($model->save()){
        $template_id = $model->template_id;
        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "push";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "{{driver_firstname}} has arrived restaurant {{restaurant_name}}";    
        $model2->content = "{{driver_firstname}} has arrived restaurant {{restaurant_name}}";
        $model2->save();
    }
}

$model = AR_templates::model()->find("template_name=:template_name",[':template_name'=>'Delivery order pickup']);
if(!$model){
    $model = new AR_templates();
    $model->template_name = 'Delivery order pickup';
    $model->enabled_email = 0;
    $model->enabled_sms = 0;
    $model->enabled_push = 1;
    if($model->save()){
        $template_id = $model->template_id;
        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "push";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "{{driver_firstname}} pickup the order#{{order_id}}";    
        $model2->content = "{{driver_firstname}} pickup the order#{{order_id}}";
        $model2->save();
    }
}

$model = AR_templates::model()->find("template_name=:template_name",[':template_name'=>'Delivery missed assigned task']);
if(!$model){
    $model = new AR_templates();
    $model->template_name = 'Delivery missed assigned task';
    $model->enabled_email = 0;
    $model->enabled_sms = 0;
    $model->enabled_push = 1;
    if($model->save()){
        $template_id = $model->template_id;
        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "push";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "{{driver_name}} has missed the assign order#{{order_id}}";    
        $model2->content = "{{driver_name}} has missed the assign order#{{order_id}}";
        $model2->save();
    }
}

$model = AR_templates::model()->find("template_name=:template_name",[':template_name'=>'Delivery order OTP to customer']);
if(!$model){
    $model = new AR_templates();
    $model->template_name = 'Delivery order OTP to customer';
    $model->enabled_email = 1;
    $model->enabled_sms = 0;
    $model->enabled_push = 1;
    if($model->save()){
        $template_id = $model->template_id;
        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "push";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "Your order#{{order_id}} OTP is {{code}}";    
        $model2->content = "Your order#{{order_id}} OTP is {{code}}";
        $model2->save();

        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "email";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "Your order#{{order_id}} OTP is {{code}}";    
        $model2->content = "Your order#{{order_id}} OTP is {{code}}";
        $model2->save();
    }
}

$model = AR_templates::model()->find("template_name=:template_name",[':template_name'=>'Delivery order assigned to driver']);
if(!$model){
    $model = new AR_templates();
    $model->template_name = 'Delivery order assigned to driver';
    $model->enabled_email = 0;
    $model->enabled_sms = 0;
    $model->enabled_push = 1;
    if($model->save()){
        $template_id = $model->template_id;
        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "push";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "You have new assigned order#{{order_id}} from {{restaurant_name}}";    
        $model2->content = "You have new assigned order#{{order_id}} from {{restaurant_name}}";
        $model2->save();
    }
}

$model = AR_templates::model()->find("template_name=:template_name",[':template_name'=>'Delivery order ready for pickup']);
if(!$model){
    $model = new AR_templates();
    $model->template_name = 'Delivery order ready for pickup';
    $model->enabled_email = 0;
    $model->enabled_sms = 0;
    $model->enabled_push = 1;
    if($model->save()){
        $template_id = $model->template_id;
        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "push";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "Order#{{order_id}} is ready for pickup at {{restaurant_name}}";    
        $model2->content = "Order#{{order_id}} is ready for pickup at {{restaurant_name}}";
        $model2->save();
    }
}

$model = AR_templates::model()->find("template_name=:template_name",[':template_name'=>'Delivery auto assign order']);
if(!$model){
    $model = new AR_templates();
    $model->template_name = 'Delivery auto assign order';
    $model->enabled_email = 0;
    $model->enabled_sms = 0;
    $model->enabled_push = 1;
    if($model->save()){
        $template_id = $model->template_id;
        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "push";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "Order#{{order_id}} is assigned to {{driver_name}}";    
        $model2->content = "Order#{{order_id}} is assigned to {{driver_name}}";
        $model2->save();
    }
}

$model = AR_templates::model()->find("template_name=:template_name",[':template_name'=>'Delivery Status']);
if(!$model){
    $model = new AR_templates();
    $model->template_name = 'Delivery Status';
    $model->enabled_email = 0;
    $model->enabled_sms = 0;
    $model->enabled_push = 1;
    if($model->save()){
        $template_id = $model->template_id;
        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "push";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "Update delivery status for Order#{{order_id}}";    
        $model2->content = "Update delivery status for Order#{{order_id}}";
        $model2->save();
    }
}

$model = AR_templates::model()->find("template_name=:template_name",[':template_name'=>'Delivery started to customer']);
if(!$model){
    $model = new AR_templates();
    $model->template_name = 'Delivery started to customer';
    $model->enabled_email = 0;
    $model->enabled_sms = 0;
    $model->enabled_push = 1;
    if($model->save()){
        $template_id = $model->template_id;
        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "push";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "Your delivery rider is it is way to you location";    
        $model2->content = "Your delivery rider is it is way to you location";
        $model2->save();
    }
}

$model = AR_templates::model()->find("template_name=:template_name",[':template_name'=>'Delivery arrived at customer location']);
if(!$model){
    $model = new AR_templates();
    $model->template_name = 'Delivery arrived at customer location';
    $model->enabled_email = 0;
    $model->enabled_sms = 0;
    $model->enabled_push = 1;
    if($model->save()){
        $template_id = $model->template_id;
        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "push";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "Your delivery rider has arrived to your location";    
        $model2->content = "Your delivery rider has arrived to your location";
        $model2->save();
    }
}

$model = AR_templates::model()->find("template_name=:template_name",[':template_name'=>'Driver Verification code']);
if(!$model){
    $model = new AR_templates();
    $model->template_name = 'Driver Verification code';
    $model->enabled_email = 1;
    $model->enabled_sms = 1;
    $model->enabled_push = 0;
    if($model->save()){
        $template_id = $model->template_id;
        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "email";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "OTP!";    
        $model2->content = 'Your OTP is {{code}}.';
        $model2->save();

        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "sms";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "OTP!";    
        $model2->content = 'Your OTP is {{code}}.';
        $model2->save();
    }
}

$model = AR_templates::model()->find("template_name=:template_name",[':template_name'=>'Test runactions']);
if(!$model){
    $model = new AR_templates();
    $model->template_name = 'Test runactions';
    $model->enabled_email = 1;
    $model->enabled_sms = 0;
    $model->enabled_push = 1;
    if($model->save()){
        $template_id = $model->template_id;
        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "email";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "Test runactions";    
        $model2->content = '<p>this is a test runactions<br></p>';
        $model2->save();

        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "push";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "Test runactions";    
        $model2->content = 'Test runactions';
        $model2->save();

        $model_option = new AR_option();
        $model_option->option_name = "runaction_test_tpl";
        $model_option->option_value = $template_id;
        $model_option->save();
       
    }
}
$model = AR_templates::model()->find("template_name=:template_name",[':template_name'=>'Contact Us']);
if(!$model){
    $model = new AR_templates();
    $model->template_name = "Contact Us";
    $model->enabled_email = 1;
    $model->enabled_sms = 0;
    $model->enabled_push = 1;
    if($model->save()){
        $template_id = $model->template_id;
        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "email";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "New contact form from {{email_address}}";
        //$model2->content = 'New contact form from {{email_address}}, {% include \'header.html\' %}\n<table style=\"width:100%;\">\n <tbody><tr>\n  <td style=\"background:#fef9ef;padding:20px 30px;\">\n    <img style=\"max-width:20%;max-height:50px;\" src=\"{{logo}}\">\n  </td>\n </tr>\n <tr>\n   <td style=\"padding:30px;background:#ffffff;\" valign=\"middle\" align=\"left\">\n    \n   <table width=\"50%\" align=\"center\">\n   <tbody><tr>\n    <td>\n \n \n <h5 style=\"margin-bottom:10px;\">CONTACT DETAILS</h5>\n \n <table> \n <tbody><tr><td>Email address:</td><td>: {{email_address}}</td></tr> \n <tr><td>Full name</td><td>: {{fullname}}</td></tr> \n <tr><td>Contact number</td><td>: {{contact_number}}</td></tr> \n <tr><td>Country</td><td>: {{country_name}}</td></tr> \n <tr><td>Message</td><td>: {{message}}</td></tr> \n </tbody></table>\n \n \n \n </td>\n   </tr>\n   </tbody></table>\n \n   </td>\n </tr>\n \n \n \n  \n <tr>\n  <td style=\"background:#fef9ef;padding:20px 30px;\">\n    \n   <table style=\"width:100%; table-layout: fixed;\">\n  <tbody><tr>\n    <th colspan=\"3\" style=\"text-align: left;\"><h5>Contact Us</h5></th>\n    <th colspan=\"7\" style=\"text-align: left;\"><h5>For  promos, news, and updates, follow us on:</h5></th>\n  </tr>\n  <tr>\n    <td colspan=\"3\" style=\"text-align: left; padding:0 3px;\" valign=\"top\">\n     <p>{{site.address}}</p>\n         <p>{{site.contact}}</p>\n         <p>{{site.email}}</p>\n    </td><td colspan=\"7\" style=\"padding:0 3px;\" valign=\"top\">\n    \n      {% include \'social_link.html\' %}\n     \n     <table>\n      <tbody><tr>\n      <td style=\"padding:0;\"><a href=\"#\" style=\"color:#000;font-size:16px;\">Terms and Conditions</a></td>\n      <td>●</td>\n      <td style=\"padding:0;\"><a href=\"#\" style=\"color:#000;font-size:16px;\">Privacy Policy</a></td>\n      </tr>\n     </tbody></table>\n    \n    </td>\n  </tr>\n </tbody></table>\n  \n  </td>\n </tr>\n \n</tbody></table>\n{% include \'footer.html\' %}\n';
        $model2->content = '{% include \'header.html\' %} <table style="width:100%;"> <tbody><tr> <td style="background:#fef9ef;padding:20px 30px;"> <img style="max-width:20%;max-height:50px;" src="{{logo}}"> </td> </tr> <tr> <td style="padding:30px;background:#ffffff;" valign="middle" align="left"> <table width="50%" align="center"> <tbody><tr> <td> <h5 style="margin-bottom:10px;">CONTACT DETAILS</h5> <table> <tbody><tr><td>Email address:</td><td>: {{email_address}}</td></tr> <tr><td>Full name</td><td>: {{fullname}}</td></tr> <tr><td>Contact number</td><td>: {{contact_number}}</td></tr> <tr><td>Country</td><td>: {{country_name}}</td></tr> <tr><td>Message</td><td>: {{message}}</td></tr> </tbody></table> </td> </tr> </tbody></table> </td> </tr> <tr> <td style="background:#fef9ef;padding:20px 30px;"> <table style="width:100%; table-layout: fixed;"> <tbody><tr> <th colspan="3" style="text-align: left;"><h5>Contact Us</h5></th> <th colspan="7" style="text-align: left;"><h5>For promos, news, and updates, follow us on:</h5></th> </tr> <tr> <td colspan="3" style="text-align: left; padding:0 3px;" valign="top"> <p>{{site.address}}</p> <p>{{site.contact}}</p> <p>{{site.email}}</p> </td><td colspan="7" style="padding:0 3px;" valign="top"> {% include \'social_link.html\' %} <table> <tbody><tr> <td style="padding:0;"><a href="#" style="color:#000;font-size:16px;">Terms and Conditions</a></td> <td>●</td> <td style="padding:0;"><a href="#" style="color:#000;font-size:16px;">Privacy Policy</a></td> </tr> </tbody></table> </td> </tr> </tbody></table> </td> </tr> </tbody></table> {% include \'footer.html\' %}';
        $model2->save();
       
        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "push";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "New contact form from {{email_address}}";
        $model2->content = "New contact form from {{email_address}}";
        $model2->save();

        $model_options = new AR_option();
        $model_options->merchant_id = 0;
        $model_options->option_name = 'contact_us_tpl';
        $model_options->option_value = $template_id;
        $model_options->save();
    }
}
$model = AR_templates::model()->find("template_name=:template_name",[':template_name'=>'Booking Update status']);
if(!$model){
    $model = new AR_templates();
    $model->template_name = "Booking Update status";
    $model->enabled_email = 1;
    $model->enabled_sms = 0;
    $model->enabled_push = 1;
    if($model->save()){
        $template_id = $model->template_id;
        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "email";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "Reservation {{status}} at {{restaurant_name}}";
        $model2->content = '{% include \'header.html\' %} <table style="width:100%;"> <tbody><tr> <td style="background:#fef9ef;padding:20px 30px;"> <img style="max-width:20%;max-height:50px;" src="{{logo}}"> </td> </tr> <tr> <td style="padding:30px;background:#ffffff;" valign="middle" align="left"> <table width="50%" align="center"> <tbody><tr> <td> <p style="margin-bottom:10px;">Your reservation at {{restaurant_name}} is {{status}}!</p> <h5 style="margin-bottom:10px;">RESERVATION DETAILS</h5> <table> <tbody><tr><td>Name of guest:</td><td>: {{guest_fullname}}</td></tr> <tr><td>Number of guests</td><td>: {{guest_number}}</td></tr> <tr><td>Time of arrival</td><td>: {{reservation_datetime}}</td></tr> </tbody></table> <p style="margin-bottom:10px;">Your Reservation ID is <b>{{reservation_id}}</b></p> <h5 style="margin-bottom:10px;">Special Requests</h5> <p style="margin-bottom:10px;">{{special_request}}</p> </td> </tr> </tbody></table> </td> </tr> <tr> <td style="background:#fef9ef;padding:20px 30px;"> <table style="width:100%; table-layout: fixed;"> <tbody><tr> <th colspan="3" style="text-align: left;"><h5>Contact Us</h5></th> <th colspan="7" style="text-align: left;"><h5>For promos, news, and updates, follow us on:</h5></th> </tr> <tr> <td colspan="3" style="text-align: left; padding:0 3px;" valign="top"> <p>{{site.address}}</p> <p>{{site.contact}}</p> <p>{{site.email}}</p> </td><td colspan="7" style="padding:0 3px;" valign="top"> {% include \'social_link.html\' %} <table> <tbody><tr> <td style="padding:0;"><a href="#" style="color:#000;font-size:16px;">Terms and Conditions</a></td> <td>●</td> <td style="padding:0;"><a href="#" style="color:#000;font-size:16px;">Privacy Policy</a></td> </tr> </tbody></table> </td> </tr> </tbody></table> </td> </tr> </tbody></table> {% include \'footer.html\' %}';
        $model2->save();
       
        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "push";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "Reservation {{status}} at {{restaurant_name}}";
        $model2->content = "Reservation {{status}} at {{restaurant_name}}";
        $model2->save();
       
        $data = [
            [
                'merchant_id'=>0,
                'option_name'=>'booking_tpl_reservation_canceled',
                'option_value'=>$template_id
            ],
            [
                'merchant_id'=>0,
                'option_name'=>'booking_tpl_reservation_denied',
                'option_value'=>$template_id
            ],
            [
                'merchant_id'=>0,
                'option_name'=>'booking_tpl_reservation_finished',
                'option_value'=>$template_id
            ],
            [
                'merchant_id'=>0,
                'option_name'=>'booking_tpl_reservation_no_show',
                'option_value'=>$template_id
            ]
        ];
        $builder=Yii::app()->db->schema->commandBuilder;
        $command=$builder->createMultipleInsertCommand('{{option}}',$data);
        $command->execute();
    }
}
$model = AR_templates::model()->find("template_name=:template_name",[':template_name'=>'Booking confirmed']);
if(!$model){
    $model = new AR_templates();
    $model->template_name = "Booking confirmed";
    $model->enabled_email = 1;
    $model->enabled_sms = 0;
    $model->enabled_push = 1;
    if($model->save()){
        $template_id = $model->template_id;
        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "email";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "Reservation Confirmed at {{restaurant_name}}";
        $model2->content = '{% include \'header.html\' %} <table style="width:100%;"> <tbody><tr> <td style="background:#fef9ef;padding:20px 30px;"> <img style="max-width:20%;max-height:50px;" src="{{logo}}"> </td> </tr> <tr> <td style="padding:30px;background:#ffffff;" valign="middle" align="left"> <table width="50%" align="center"> <tbody><tr> <td> <p style="margin-bottom:10px;">Your reservation at {{restaurant_name}} is confirmed!</p> <h5 style="margin-bottom:10px;">RESERVATION DETAILS</h5> <table> <tbody><tr><td>Name of guest:</td><td>: {{guest_fullname}}</td></tr> <tr><td>Number of guests</td><td>: {{guest_number}}</td></tr> <tr><td>Time of arrival</td><td>: {{reservation_datetime}}</td></tr> </tbody></table> <p style="margin-bottom:10px;">Your Reservation ID is <b>{{reservation_id}}</b></p> <div style="margin:auto;text-align:center;padding-top:10px; padding-bottom:10px;"> <a href="{{manage_reservation_link}}" target="_blank" style="display:block;margin:auto;max-width:200px;padding:10px;background:#3ecf8e;color:#fff;
        text-decoration:none;font-size:18px;font-weight:bold;"> Manage reservation </a> </div> <p style="text-align:center;">or click this link:</p> <p style="text-align:center;"><a href="{{manage_reservation_link}}">{{manage_reservation_link}}</a></p> <br><br> <h5 style="margin-bottom:10px;">Special Requests</h5> <p style="margin-bottom:10px;">{{special_request}}</p> <br><br> <h5 style="margin-bottom:10px;">RESTAURANT DETAILS</h5> <p style="margin-bottom:10px;">{{restaurant_name}}</p> <p style="margin-bottom:10px;">{{restaurant_contact_phone}} / {{restaurant_contact_email}}</p> <br><br> <h5 style="margin-bottom:10px;">Notes from the restaurant</h5> <p style="margin-bottom:10px;">{{notes_from_restaurant}}</p> </td> </tr> </tbody></table> </td> </tr> <tr> <td style="background:#fef9ef;padding:20px 30px;"> <table style="width:100%; table-layout: fixed;"> <tbody><tr> <th colspan="3" style="text-align: left;"><h5>Contact Us</h5></th> <th colspan="7" style="text-align: left;"><h5>For promos, news, and updates, follow us on:</h5></th> </tr> <tr> <td colspan="3" style="text-align: left; padding:0 3px;" valign="top"> <p>{{site.address}}</p> <p>{{site.contact}}</p> <p>{{site.email}}</p> </td><td colspan="7" style="padding:0 3px;" valign="top"> {% include \'social_link.html\' %} <table> <tbody><tr> <td style="padding:0;"><a href="#" style="color:#000;font-size:16px;">Terms and Conditions</a></td> <td>●</td> <td style="padding:0;"><a href="#" style="color:#000;font-size:16px;">Privacy Policy</a></td> </tr> </tbody></table> </td> </tr> </tbody></table> </td> </tr> </tbody></table> {% include \'footer.html\' %}';
        $model2->save();
       
        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "push";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "Reservation Confirmed at {{restaurant_name}}";
        $model2->content = "Reservation Confirmed at {{restaurant_name}}";
        $model2->save();

        $model_options = new AR_option();
        $model_options->merchant_id = 0;
        $model_options->option_name = 'booking_tpl_reservation_confirmed';
        $model_options->option_value = $template_id;
        $model_options->save();
    }
}
$model = AR_templates::model()->find("template_name=:template_name",[':template_name'=>'Booking requested']);
if(!$model){
    $model = new AR_templates();
    $model->template_name = "Booking requested";
    $model->enabled_email = 1;
    $model->enabled_sms = 0;
    $model->enabled_push = 1;
    if($model->save()){
        $template_id = $model->template_id;
        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "email";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "Online Reservation Confirmation Notification";
        $model2->content = '{% include \'header.html\' %} <table style="width:100%;"> <tbody><tr> <td style="background:#fef9ef;padding:20px 30px;"> <img style="max-width:20%;max-height:50px;" src="{{logo}}"> </td> </tr> <tr> <td style="padding:30px;background:#ffffff;" valign="middle" align="left"> <table width="50%" align="center"> <tbody><tr> <td> <p style="margin-bottom:10px;">You have received an online reservation.</p> <h5 style="margin-bottom:10px;">RESERVATION DETAILS</h5> <table> <tbody><tr><td>Restaurant Name</td><td>: {{restaurant_name}}</td></tr> <tr><td>Guest Name</td><td>: {{guest_fullname}}</td></tr> <tr><td>Guest Phone</td><td>: {{contact_phone}}</td></tr> <tr><td>Guest Email</td><td>: {{email_address}}</td></tr> <tr><td>Reservation ID</td><td>: {{reservation_id}}</td></tr> <tr><td>Date of booking</td><td>: {{date_created}}</td></tr> <tr><td>Time of arrival</td><td>: {{reservation_datetime}}</td></tr> <tr><td>Party of</td><td>: {{guest_number}}</td></tr> <tr><td>Special Request</td><td>: {{special_request}}</td></tr> </tbody></table> <br><br> <div style="margin:auto;text-align:center;padding-top:10px; padding-bottom:10px;"> <a href="{{manage_reservation_link}}" target="_blank" style="display:block;margin:auto;max-width:200px;padding:10px;background:#3ecf8e;color:#fff;
        text-decoration:none;font-size:18px;font-weight:bold;"> Manage reservation </a> </div> <p style="text-align:center;">or click this link:</p> <p style="text-align:center;"><a href="{{manage_reservation_link}}">{{manage_reservation_link}}</a></p> </td> </tr> </tbody></table> </td> </tr> <tr> <td style="background:#fef9ef;padding:20px 30px;"> <table style="width:100%; table-layout: fixed;"> <tbody><tr> <th colspan="3" style="text-align: left;"><h5>Contact Us</h5></th> <th colspan="7" style="text-align: left;"><h5>For promos, news, and updates, follow us on:</h5></th> </tr> <tr> <td colspan="3" style="text-align: left; padding:0 3px;" valign="top"> <p>{{site.address}}</p> <p>{{site.contact}}</p> <p>{{site.email}}</p> </td><td colspan="7" style="padding:0 3px;" valign="top"> {% include \'social_link.html\' %} <table> <tbody><tr> <td style="padding:0;"><a href="#" style="color:#000;font-size:16px;">Terms and Conditions</a></td> <td>●</td> <td style="padding:0;"><a href="#" style="color:#000;font-size:16px;">Privacy Policy</a></td> </tr> </tbody></table> </td> </tr> </tbody></table> </td> </tr> </tbody></table> {% include \'footer.html\' %}';
        $model2->save();
       
        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "push";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "Online Reservation Confirmation Notification";
        $model2->content = "Online Reservation Confirmation Notification";
        $model2->save();

        $model_options = new AR_option();
        $model_options->merchant_id = 0;
        $model_options->option_name = 'booking_tpl_reservation_requested';
        $model_options->option_value = $template_id;
        $model_options->save();
    }
}
$model = AR_templates::model()->find("template_name=:template_name",[':template_name'=>'Invoice new upload deposit']);
if(!$model){
    $model = new AR_templates();
    $model->template_name = "Invoice new upload deposit";
    $model->enabled_email = 1;
    $model->enabled_sms = 0;
    $model->enabled_push = 1;
    if($model->save()){
        $template_id = $model->template_id;
        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "email";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "New bank deposit with invoice #{{invoice_number}}";
        $model2->content = '<p>New bank deposit with invoice #{{invoice_number}}<br></p>';
        $model2->save();
       
        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "push";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "New bank deposit with invoice #{{invoice_number}}";
        $model2->content = "New bank deposit with invoice #{{invoice_number}}";
        $model2->save();

        $model_options = new AR_option();
        $model_options->merchant_id = 0;
        $model_options->option_name = 'invoice_new_upload_deposit';
        $model_options->option_value = $template_id;
        $model_options->save();
    }
}
$model = AR_templates::model()->find("template_name=:template_name",[':template_name'=>'Invoice created']);
if(!$model){
    $model = new AR_templates();
    $model->template_name = "Invoice created";
    $model->enabled_email = 1;
    $model->enabled_sms = 0;
    $model->enabled_push = 1;
    if($model->save()){
        $template_id = $model->template_id;
        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "email";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "You have new Invoice #{{invoice_number}}";
        $model2->content = '<{% include \'header.html\' %} <table style="width:100%;"> <tbody><tr> <td style="background:#fef9ef;padding:20px 30px;"> <img style="max-width:20%;max-height:50px;" src="{{logo}}"> </td> </tr> <tr> <td style="background:#ffffff;"> <p>Your invoice is now ready, you can view your invoice by going to backoffice</p> </td> </tr> <tr> <td style="background:#fef9ef;padding:20px 30px;"> <table style="width:100%; table-layout: fixed;"> <tbody><tr> <th colspan="3" style="text-align: left;"><h5>Contact Us</h5></th> <th colspan="7" style="text-align: left;"><h5>For promos, news, and updates, follow us on:</h5></th> </tr> <tr> <td colspan="3" style="text-align: left; padding:0 3px;" valign="top"> <p>{{site.address}}</p> <p>{{site.contact}}</p> <p>{{site.email}}</p> </td><td colspan="7" style="padding:0 3px;" valign="top"> {% include \'social_link.html\' %} <table> <tbody><tr> <td style="padding:0;"><a href="#" style="color:#000;font-size:16px;">Terms and Conditions</a></td> <td>●</td> <td style="padding:0;"><a href="#" style="color:#000;font-size:16px;">Privacy Policy</a></td> </tr> </tbody></table> </td> </tr> </tbody></table> </td> </tr> </tbody></table> {% include \'footer.html\' %}';
        $model2->save();
       
        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "push";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "You have new Invoice #{{invoice_number}}";
        $model2->content = "You have new Invoice #{{invoice_number}}";
        $model2->save();

        $model_options = new AR_option();
        $model_options->merchant_id = 0;
        $model_options->option_name = 'invoice_created';
        $model_options->option_value = $template_id;
        $model_options->save();
    }
}
// END INSERT TEMPLATE



// INSERT DELIVERY STATUS
$delivery_status = 'delivery_status';
$model = AR_status::model()->find("group_name=:group_name AND description=:description",[':group_name'=>$delivery_status,':description'=>'assigned']);
if(!$model){
    $model = new AR_status();
    $model->group_name = $delivery_status;
    $model->description = "assigned";
    $model->font_color_hex = 'white';
    $model->background_color_hex = '#ffa726';
    $model->save();
    $stats_id = $model->stats_id;

    $model2  = new AR_order_status_actions();
    $model2->stats_id = $stats_id;
    $model2->action_type = "notification_to_driver";
    $model2->action_value = AR_templates::getTemplateID("Delivery order assigned to driver");
    $model2->save();

    $model2  = new AR_order_status_actions();
    $model2->stats_id = $stats_id;
    $model2->action_type = "notification_to_admin";
    $model2->action_value = AR_templates::getTemplateID("Delivery auto assign order");
    $model2->save();
}

$model = AR_status::model()->find("group_name=:group_name AND description=:description",[':group_name'=>$delivery_status,':description'=>'acknowledged']);
if(!$model){
    $model = new AR_status();
    $model->group_name = $delivery_status;
    $model->description = "acknowledged";
    $model->font_color_hex = 'white';
    $model->background_color_hex = '#f8af01';
    $model->save();
    $stats_id = $model->stats_id;

    $model2  = new AR_order_status_actions();
    $model2->stats_id = $stats_id;
    $model2->action_type = "notification_to_admin";
    $model2->action_value = AR_templates::getTemplateID("Delivery Order accepted");
    $model2->save();
}

$model = AR_status::model()->find("group_name=:group_name AND description=:description",[':group_name'=>$delivery_status,':description'=>'on the way to restaurant']);
if(!$model){
    $model = new AR_status();
    $model->group_name = $delivery_status;
    $model->description = "on the way to restaurant";
    $model->font_color_hex = 'white';
    $model->background_color_hex = '#8fce00';
    $model->save();
    $stats_id = $model->stats_id;

    $model2  = new AR_order_status_actions();
    $model2->stats_id = $stats_id;
    $model2->action_type = "notification_to_admin";
    $model2->action_value = AR_templates::getTemplateID("Delivery on the way to restaurant");
    $model2->save();
    
}

$model = AR_status::model()->find("group_name=:group_name AND description=:description",[':group_name'=>$delivery_status,':description'=>'arrived at restaurant']);
if(!$model){
    $model = new AR_status();
    $model->group_name = $delivery_status;
    $model->description = "arrived at restaurant";
    $model->font_color_hex = 'white';
    $model->background_color_hex = '#ea9999';
    $model->save();
    $stats_id = $model->stats_id;

    $model2  = new AR_order_status_actions();
    $model2->stats_id = $stats_id;
    $model2->action_type = "notification_to_admin";
    $model2->action_value = AR_templates::getTemplateID("Delivery arrived at restaurant");
    $model2->save();
}

$model = AR_status::model()->find("group_name=:group_name AND description=:description",[':group_name'=>$delivery_status,':description'=>'waiting for order']);
if(!$model){
    $model = new AR_status();
    $model->group_name = $delivery_status;
    $model->description = "waiting for order";
    $model->font_color_hex = '#5b5b5b';
    $model->background_color_hex = '#8fce00';
    $model->save();
}

$model = AR_status::model()->find("group_name=:group_name AND description=:description",[':group_name'=>$delivery_status,':description'=>'order pickup']);
if(!$model){
    $model = new AR_status();
    $model->group_name = $delivery_status;
    $model->description = "order pickup";
    $model->font_color_hex = 'white';
    $model->background_color_hex = '#8e7cc3';
    $model->save();
    $stats_id = $model->stats_id;

    $model2  = new AR_order_status_actions();
    $model2->stats_id = $stats_id;
    $model2->action_type = "notification_to_admin";
    $model2->action_value = AR_templates::getTemplateID("Delivery order pickup");
    $model2->save();
}

$model = AR_status::model()->find("group_name=:group_name AND description=:description",[':group_name'=>$delivery_status,':description'=>'delivery started']);
if(!$model){
    $model = new AR_status();
    $model->group_name = $delivery_status;
    $model->description = "delivery started";
    $model->font_color_hex = 'white';
    $model->background_color_hex = '#c90076';
    $model->save();
    $stats_id = $model->stats_id;

    $model2  = new AR_order_status_actions();
    $model2->stats_id = $stats_id;
    $model2->action_type = "notification_to_customer";
    $model2->action_value = AR_templates::getTemplateID("Delivery started to customer");
    $model2->save();
}

$model = AR_status::model()->find("group_name=:group_name AND description=:description",[':group_name'=>$delivery_status,':description'=>'arrived at customer']);
if(!$model){
    $model = new AR_status();
    $model->group_name = $delivery_status;
    $model->description = "arrived at customer";
    $model->font_color_hex = 'white';
    $model->background_color_hex = '#3d85c6';
    $model->save();
    $stats_id = $model->stats_id;

    $model2  = new AR_order_status_actions();
    $model2->stats_id = $stats_id;
    $model2->action_type = "notification_to_customer";
    $model2->action_value = AR_templates::getTemplateID("Delivery arrived at customer location");
    $model2->save();
}

$model = AR_status::model()->find("group_name=:group_name AND description=:description",[':group_name'=>$delivery_status,':description'=>'unassigned']);
if(!$model){
    $model = new AR_status();
    $model->group_name = $delivery_status;
    $model->description = "unassigned";
    $model->font_color_hex = 'white';
    $model->background_color_hex = '#78909c';
    $model->save();
}

$model = AR_status::model()->find("group_name=:group_name AND description=:description",[':group_name'=>$delivery_status,':description'=>'delivered']);
if(!$model){
    $model = new AR_status();
    $model->group_name = $delivery_status;
    $model->description = "delivered";
    $model->font_color_hex = 'white';
    $model->background_color_hex = '#3ecf8e';
    $model->save();
    $stats_id = $model->stats_id;

    $model2  = new AR_order_status_actions();
    $model2->stats_id = $stats_id;
    $model2->action_type = "notification_to_admin";
    $model2->action_value = AR_templates::getTemplateID("Delivery Status");
    $model2->save();
}

$model = AR_status::model()->find("group_name=:group_name AND description=:description",[':group_name'=>$delivery_status,':description'=>'declined']);
if(!$model){
    $model = new AR_status();
    $model->group_name = $delivery_status;
    $model->description = "declined";
    $model->font_color_hex = 'white';
    $model->background_color_hex = '#f11707';
    $model->save();
    $stats_id = $model->stats_id;

    $model2  = new AR_order_status_actions();
    $model2->stats_id = $stats_id;
    $model2->action_type = "notification_to_admin";
    $model2->action_value = AR_templates::getTemplateID("Delivery Status");
    $model2->save();
}

$model = AR_status::model()->find("group_name=:group_name AND description=:description",[':group_name'=>$delivery_status,':description'=>'failed']);
if(!$model){
    $model = new AR_status();
    $model->group_name = $delivery_status;
    $model->description = "failed";
    $model->font_color_hex = '#999999';
    $model->background_color_hex = '#dc1e10';
    $model->save();
    $stats_id = $model->stats_id;

    $model2  = new AR_order_status_actions();
    $model2->stats_id = $stats_id;
    $model2->action_type = "notification_to_admin";
    $model2->action_value = AR_templates::getTemplateID("Delivery Status");
    $model2->save();
}

$model = AR_status::model()->find("group_name=:group_name AND description=:description",[':group_name'=>$delivery_status,':description'=>'cancelled']);
if(!$model){
    $model = new AR_status();
    $model->group_name = $delivery_status;
    $model->description = "cancelled";
    $model->font_color_hex = '#fff';
    $model->background_color_hex = '#f44336';
    $model->save();
    $stats_id = $model->stats_id;    
}

$model = AR_status::model()->find("group_name=:group_name AND description=:description",[':group_name'=>'order_status',':description'=>'deleted']);
if(!$model){
    $model = new AR_status();
    $model->group_name = 'order_status';
    $model->description = "deleted";
    $model->font_color_hex = '#fff';
    $model->background_color_hex = '#f44336';
    $model->save();
    $stats_id = $model->stats_id;    
}

$model = AR_status::model()->find("group_name=:group_name AND description=:description",[':group_name'=>'order_status',':description'=>'refunded']);
if(!$model){
    $model = new AR_status();
    $model->group_name = 'order_status';
    $model->description = "refunded";
    $model->font_color_hex = '#fff';
    $model->background_color_hex = '#f44336';
    $model->save();
    $stats_id = $model->stats_id;    
}

// END INSERT DELIVERY STATUS


// DEFAULT DELIVERY SETTINGS
$opt_name = 'driver_alert_time';
$model = AR_option::model()->find("option_name=:option_name",[':option_name'=>$opt_name]);
if(!$model){
    $model = new AR_option();
    $model->option_name = $opt_name;
    $model->option_value = 5;
}

$opt_name = 'driver_allowed_number_task';
$model = AR_option::model()->find("option_name=:option_name",[':option_name'=>$opt_name]);
if(!$model){
    $model = new AR_option();
    $model->option_name = $opt_name;
    $model->option_value = 1;
    $model->save();
}

$opt_name = 'driver_request_break_limit';
$model = AR_option::model()->find("option_name=:option_name",[':option_name'=>$opt_name]);
if(!$model){
    $model = new AR_option();
    $model->option_name = $opt_name;
    $model->option_value = 2;
    $model->save();
}

$opt_name = 'driver_time_allowed_accept_order';
$model = AR_option::model()->find("option_name=:option_name",[':option_name'=>$opt_name]);
if(!$model){
    $model = new AR_option();
    $model->option_name = $opt_name;
    $model->option_value = 45;
    $model->save();
}

$opt_name = 'driver_enabled_alert';
$model = AR_option::model()->find("option_name=:option_name",[':option_name'=>$opt_name]);
if(!$model){
    $model = new AR_option();
    $model->option_name = $opt_name;
    $model->option_value = 1;
    $model->save();
}

$opt_name = 'driver_missed_order_tpl';
$model = AR_option::model()->find("option_name=:option_name",[':option_name'=>$opt_name]);
if(!$model){
    $model = new AR_option();
    $model->option_name = $opt_name;
    $model->option_value = AR_templates::getTemplateID("Delivery missed assigned task");
    $model->save();
}

$opt_name = 'driver_order_otp_tpl';
$model = AR_option::model()->find("option_name=:option_name",[':option_name'=>$opt_name]);
if(!$model){
    $model = new AR_option();
    $model->option_name = $opt_name;
    $model->option_value = AR_templates::getTemplateID("Delivery order OTP to customer");
    $model->save();
}

$opt_name = 'driver_employment_type';
$model = AR_option::model()->find("option_name=:option_name",[':option_name'=>$opt_name]);
if(!$model){
    $model = new AR_option();
    $model->option_name = $opt_name;
    $model->option_value = 'contractor';
    $model->save();
}

$opt_name = 'driver_salary_type';
$model = AR_option::model()->find("option_name=:option_name",[':option_name'=>$opt_name]);
if(!$model){
    $model = new AR_option();
    $model->option_name = $opt_name;
    $model->option_value = 'delivery_fee';
    $model->save();
}

$opt_name = 'driver_registration_process';
$model = AR_option::model()->find("option_name=:option_name",[':option_name'=>$opt_name]);
if(!$model){
    $model = new AR_option();
    $model->option_name = $opt_name;
    $model->option_value = 'activate_account';
    $model->save();
}

$opt_name = 'driver_sendcode_via';
$model = AR_option::model()->find("option_name=:option_name",[':option_name'=>$opt_name]);
if(!$model){
    $model = new AR_option();
    $model->option_name = $opt_name;
    $model->option_value = 'email';
    $model->save();
}

$opt_name = 'driver_sendcode_interval';
$model = AR_option::model()->find("option_name=:option_name",[':option_name'=>$opt_name]);
if(!$model){
    $model = new AR_option();
    $model->option_name = $opt_name;
    $model->option_value = 20;
    $model->save();
}

$opt_name = 'driver_sendcode_tpl';
$model = AR_option::model()->find("option_name=:option_name",[':option_name'=>$opt_name]);
if(!$model){
    $model = new AR_option();
    $model->option_name = $opt_name;
    $model->option_value = AR_templates::getTemplateID("Driver Verification code");
    $model->save();
}

$opt_name = 'driver_signup_terms_condition';
$model = AR_option::model()->find("option_name=:option_name",[':option_name'=>$opt_name]);
if(!$model){
    $model = new AR_option();
    $model->option_name = $opt_name;
    $model->option_value = 'By clicking "Submit," you agree to <a href="" class="text-green">Karenderia General Terms and Conditions</a>and acknowledge you have read the <a href="" class="text-green">Privacy Policy</a>.';
    $model->save();
}

$opt_name = 'driver_cashout_minimum';
$model = AR_option::model()->find("option_name=:option_name",[':option_name'=>$opt_name]);
if(!$model){
    $model = new AR_option();
    $model->option_name = $opt_name;
    $model->option_value = 5;
    $model->save();
}

$opt_name = 'driver_cashout_miximum';
$model = AR_option::model()->find("option_name=:option_name",[':option_name'=>$opt_name]);
if(!$model){
    $model = new AR_option();
    $model->option_name = $opt_name;
    $model->option_value = 500;
    $model->save();
}

$opt_name = 'driver_cashout_request_limit';
$model = AR_option::model()->find("option_name=:option_name",[':option_name'=>$opt_name]);
if(!$model){
    $model = new AR_option();
    $model->option_name = $opt_name;
    $model->option_value = 2;
    $model->save();
}

$meta_name = 'status_unassigned';
$model = AR_admin_meta::getValue($meta_name);
if(!$model){
    $model = new AR_admin_meta();
    $model->meta_name = $meta_name;
    $model->meta_value = 'unassigned';
    $model->meta_value1 = "Status updated from {{current_status}} to {{status}}";
    $model->save();
}

$meta_name = 'status_assigned';
$model = AR_admin_meta::getValue($meta_name);
if(!$model){
    $model = new AR_admin_meta();
    $model->meta_name = $meta_name;
    $model->meta_value = 'assigned';
    $model->meta_value1 = "assign to {{first_name}}";
    $model->save();
}

$meta_name = 'status_acknowledged';
$model = AR_admin_meta::getValue($meta_name);
if(!$model){
    $model = new AR_admin_meta();
    $model->meta_name = $meta_name;
    $model->meta_value = 'acknowledged';
    $model->meta_value1 = "Status updated from {{current_status}} to {{status}}";
    $model->save();
}

$meta_name = 'status_delivery_declined';
$model = AR_admin_meta::getValue($meta_name);
if(!$model){
    $model = new AR_admin_meta();
    $model->meta_name = $meta_name;
    $model->meta_value = 'declined';
    $model->meta_value1 = "{{first_name}} has decline the order {{remarks}}";
    $model->save();
}

$meta_name = 'status_driver_to_restaurant';
$model = AR_admin_meta::getValue($meta_name);
if(!$model){
    $model = new AR_admin_meta();
    $model->meta_name = $meta_name;
    $model->meta_value = 'on the way to restaurant';
    $model->meta_value1 = "Status updated from {{current_status}} to {{status}}";
    $model->save();
}

$meta_name = 'status_arrived_at_restaurant';
$model = AR_admin_meta::getValue($meta_name);
if(!$model){
    $model = new AR_admin_meta();
    $model->meta_name = $meta_name;
    $model->meta_value = 'arrived at restaurant';
    $model->meta_value1 = "Status updated from {{current_status}} to {{status}}";
    $model->save();
}

$meta_name = 'status_waiting_for_order';
$model = AR_admin_meta::getValue($meta_name);
if(!$model){
    $model = new AR_admin_meta();
    $model->meta_name = $meta_name;
    $model->meta_value = 'waiting for order';
    $model->meta_value1 = "Status updated from {{current_status}} to {{status}}";
    $model->save();
}

$meta_name = 'status_order_pickup';
$model = AR_admin_meta::getValue($meta_name);
if(!$model){
    $model = new AR_admin_meta();
    $model->meta_name = $meta_name;
    $model->meta_value = 'order pickup';
    $model->meta_value1 = "Status updated from {{current_status}} to {{status}}";
    $model->save();
}

$meta_name = 'status_delivery_started';
$model = AR_admin_meta::getValue($meta_name);
if(!$model){
    $model = new AR_admin_meta();
    $model->meta_name = $meta_name;
    $model->meta_value = 'delivery started';
    $model->meta_value1 = "Status updated from {{current_status}} to {{status}} by {{first_name}}";
    $model->save();
}

$meta_name = 'status_arrived_at_customer';
$model = AR_admin_meta::getValue($meta_name);
if(!$model){
    $model = new AR_admin_meta();
    $model->meta_name = $meta_name;
    $model->meta_value = 'arrived at customer';
    $model->meta_value1 = "Status updated from {{current_status}} to {{status}}";
    $model->save();
}

$meta_name = 'status_delivery_delivered';
$model = AR_admin_meta::getValue($meta_name);
if(!$model){
    $model = new AR_admin_meta();
    $model->meta_name = $meta_name;
    $model->meta_value = 'delivered';
    $model->meta_value1 = "Status updated from {{current_status}} to {{status}}";
    $model->save();
}

$meta_name = 'status_delivery_failed';
$model = AR_admin_meta::getValue($meta_name);
if(!$model){
    $model = new AR_admin_meta();
    $model->meta_name = $meta_name;
    $model->meta_value = 'failed';
    $model->meta_value1 = "Delivery failed {{remarks}}";
    $model->save();
}

$meta_name = 'status_delivery_cancelled';
$model = AR_admin_meta::getValue($meta_name);
if(!$model){
    $model = new AR_admin_meta();
    $model->meta_name = $meta_name;
    $model->meta_value = 'cancelled';
    $model->meta_value1 = "order cancelled";
    $model->save();
}

// ORDER TABS
$meta_name = 'unassigned';
$model = AR_order_settings_tabs::model()->find("group_name=:group_name",[':group_name'=>$meta_name]);
if(!$model){
    $status_in = ['unassigned','declined'];
    $status_in = CommonUtility::arrayToQueryParameters($status_in);
    $stmt = "SELECT stats_id FROM 
    {{order_status}} 
    WHERE group_name=".q($delivery_status)." 
    AND description IN (".$status_in.")
    ";
    if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
        foreach ($res as $items) {
            $model_tabs = new AR_order_settings_tabs();
            $model_tabs->group_name = $meta_name;
            $model_tabs->stats_id = $items['stats_id'];
            $model_tabs->save();
        }
    }
}

$meta_name = 'assigned';
$model = AR_order_settings_tabs::model()->find("group_name=:group_name",[':group_name'=>$meta_name]);
if(!$model){
    $status_in = ['assigned','arrived at restaurant','acknowledged','waiting for order','on the way to restaurant','order pickup','delivery started','arrived at customer'];
    $status_in = CommonUtility::arrayToQueryParameters($status_in);
    $stmt = "SELECT stats_id FROM 
    {{order_status}} 
    WHERE group_name=".q($delivery_status)." 
    AND description IN (".$status_in.")
    ";
    if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
        foreach ($res as $items) {
            $model_tabs = new AR_order_settings_tabs();
            $model_tabs->group_name = $meta_name;
            $model_tabs->stats_id = $items['stats_id'];
            $model_tabs->save();
        }
    }
}

$meta_name = 'completed';
$model = AR_order_settings_tabs::model()->find("group_name=:group_name",[':group_name'=>$meta_name]);
if(!$model){
    $status_in = ['delivered','failed'];
    $status_in = CommonUtility::arrayToQueryParameters($status_in);
    $stmt = "SELECT stats_id FROM 
    {{order_status}} 
    WHERE group_name=".q($delivery_status)." 
    AND description IN (".$status_in.")
    ";
    if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
        foreach ($res as $items) {
            $model_tabs = new AR_order_settings_tabs();
            $model_tabs->group_name = $meta_name;
            $model_tabs->stats_id = $items['stats_id'];
            $model_tabs->save();
        }
    }
}

// END DEFAULT DELIVERY SETTINGS

// DRIVER ATTRIBUTES
$meta_name = 'vehicle_maker';
$model = AR_admin_meta::getValue($meta_name);
if(!$model){
    $data = [
        [
            'meta_name'=>$meta_name,
            'meta_value'=>'Mazda'
        ],
        [
            'meta_name'=>$meta_name,
            'meta_value'=>'Toyota'
        ],
        [
            'meta_name'=>$meta_name,
            'meta_value'=>'Isuzu'
        ]
    ];
    $builder=Yii::app()->db->schema->commandBuilder;
    $command=$builder->createMultipleInsertCommand('{{admin_meta}}',$data);
    $command->execute();
} 

$meta_name = 'vehicle_type';
$model = AR_admin_meta::getValue($meta_name);
if(!$model){
    $data = [
        [
            'meta_name'=>$meta_name,
            'meta_value'=>'Car'
        ],
        [
            'meta_name'=>$meta_name,
            'meta_value'=>'Scooter'
        ],
        [
            'meta_name'=>$meta_name,
            'meta_value'=>'Bike'
        ]
    ];
    $builder=Yii::app()->db->schema->commandBuilder;
    $command=$builder->createMultipleInsertCommand('{{admin_meta}}',$data);
    $command->execute();
} 

$meta_name = 'order_help';
$model = AR_admin_meta::getValue($meta_name);
if(!$model){
    $data = [
        [
            'meta_name'=>$meta_name,
            'meta_value'=>'Unable to find address'
        ],
        [
            'meta_name'=>$meta_name,
            'meta_value'=>'Order was cancelled'
        ],
        [
            'meta_name'=>$meta_name,
            'meta_value'=>'Food spill'
        ],
        [
            'meta_name'=>$meta_name,
            'meta_value'=>"Customer can't be reached"
        ],
        [
            'meta_name'=>$meta_name,
            'meta_value'=>"Dropoff address is changed"
        ],
        [
            'meta_name'=>$meta_name,
            'meta_value'=>"Dropoff is inaccessible"
        ]
    ];
    $builder=Yii::app()->db->schema->commandBuilder;
    $command=$builder->createMultipleInsertCommand('{{admin_meta}}',$data);
    $command->execute();
} 

$meta_name = 'order_decline_reason';
$model = AR_admin_meta::getValue($meta_name);
if(!$model){
    $data = [
        [
            'meta_name'=>$meta_name,
            'meta_value'=>'Distance is too far'
        ],
        [
            'meta_name'=>$meta_name,
            'meta_value'=>'Excessive wait time'
        ],
        [
            'meta_name'=>$meta_name,
            'meta_value'=>'Order was cancelled'
        ],
        [
            'meta_name'=>$meta_name,
            'meta_value'=>"Restaurant is close"
        ],
        [
            'meta_name'=>$meta_name,
            'meta_value'=>"Order pickup up by someone else"
        ],
        [
            'meta_name'=>$meta_name,
            'meta_value'=>"I don't want to do delivery"
        ],
        [
            'meta_name'=>$meta_name,
            'meta_value'=>"Oversize item"
        ],
        [
            'meta_name'=>$meta_name,
            'meta_value'=>"I have too many orders"
        ]
    ];
    $builder=Yii::app()->db->schema->commandBuilder;
    $command=$builder->createMultipleInsertCommand('{{admin_meta}}',$data);
    $command->execute();
} 
// END  DRIVER ATTRIBUTES


// 1.0.8 UPDATE
$model_status = AR_status_management::model()->find("group_name=:group_name AND status=:status",[
    ':group_name'=>'customer',
    ':status'=>"deleted"
]);
if(!$model_status){
    $model_status = new AR_status_management;
    $model_status->group_name = 'customer';
    $model_status->status = 'deleted';
    $model_status->title = 'deleted';
    $model_status->color_hex = '#880808';
    $model_status->font_color_hex = 'white';
    $model_status->save();
}

if(Yii::app()->db->schema->getTable("{{menu}}")){
    $data[] = $table->add_Column("{{menu}}",array(
        'role_view'=>"varchar(255) NOT NULL DEFAULT '' AFTER `visible`",         
        'role_delete'=>"varchar(255) NOT NULL DEFAULT '' AFTER `visible`", 
        'role_update'=>"varchar(255) NOT NULL DEFAULT '' AFTER `visible`", 
        'role_create'=>"varchar(255) NOT NULL DEFAULT '' AFTER `visible`", 
    ));
    
    Yii::app()->db->createCommand("
    ALTER TABLE {{menu}} CHANGE `sequence` `sequence` DECIMAL(10,2) NOT NULL DEFAULT '0'
    ")->query();
}

if(Yii::app()->db->schema->getTable("{{item_relationship_subcategory}}")){
    $data[] = $table->add_Column("{{item_relationship_subcategory}}",array(
        'sequence'=>"int(12) NOT NULL DEFAULT '0'",          
    ));
}

if(Yii::app()->db->schema->getTable("{{subcategory_item_relationships}}")){
    $data[] = $table->add_Column("{{subcategory_item_relationships}}",array(
        'sequence'=>"int(12) NOT NULL DEFAULT '0'",          
        'merchant_id'=>"int(14) NOT NULL DEFAULT '0'",  
    ));
}

if(Yii::app()->db->schema->getTable("{{item_translation}}")){
    $data[] = $table->add_Column("{{item_translation}}",array(
        'item_short_description'=>"text", 
        'merchant_id'=>"int(14) NOT NULL DEFAULT '0'",           
    ));
}

if(Yii::app()->db->schema->getTable("{{merchant_type}}")){
    $data[] = $table->add_Column("{{merchant_type}}",array(
        'commission_data'=>"text",          
    ));
}

if(!Yii::app()->db->schema->getTable("{{merchant_commission_order}}")){    
    $table->createTable(
        "{{merchant_commission_order}}",
        array(
          'id'=>'pk',		        
          'merchant_id'=>"bigint(20) NOT NULL DEFAULT '0'",
          'transaction_type'=>"varchar(255) NOT NULL DEFAULT ''",
          'commission_type'=>"varchar(255) NOT NULL DEFAULT ''",
          'commission'=>"decimal(10,4) NOT NULL DEFAULT '0.00'",
          'date_created'=>"datetime DEFAULT NULL",
          'date_modified'=>"datetime DEFAULT NULL",
          'ip_address'=>"varchar(50) NOT NULL DEFAULT ''",
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');
    $table->create_Index("{{merchant_commission_order}}",array(
        'merchant_id','transaction_type'
    )); 	    
    $data[] = "{{merchant_commission_order}} table created";
} else $data[] = "{{merchant_commission_order}} table already exist";

if(Yii::app()->db->schema->getTable("{{services_fee}}")){
    $data[] = $table->add_Column("{{services_fee}}",array(        
        'small_less_order_based'=>"decimal(10,4) NOT NULL DEFAULT '0.00' AFTER `merchant_id`",
        'small_order_fee'=>"decimal(10,4) NOT NULL DEFAULT '0.00' AFTER `merchant_id`",        
        'charge_type'=>"varchar(50) NOT NULL DEFAULT 'fixed' AFTER `merchant_id`",
    ));
}

if(Yii::app()->db->schema->getTable("{{ordernew}}")){
    $data[] = $table->add_Column("{{ordernew}}",array(
        'small_order_fee'=>"decimal(10,4) NOT NULL DEFAULT '0.00'",          
    ));
}

if(Yii::app()->db->schema->getTable("{{services}}")){
    $data[] = $table->add_Column("{{services}}",array(
        'description'=>"text AFTER `service_name`",          
    ));
}

if(Yii::app()->db->schema->getTable("{{item_relationship_subcategory}}")){
    $data[] = $table->add_Column("{{item_relationship_subcategory}}",array(
        'multi_option_min'=>"int(14) NOT NULL DEFAULT '0' AFTER `multi_option`",          
    ));
}

if(Yii::app()->db->schema->getTable("{{pages_translation}}")){
     Yii::app()->db->createCommand("
     ALTER TABLE {{pages_translation}} CHANGE `meta_description` `meta_description` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL
    ")->query();
}

if(Yii::app()->db->schema->getTable("{{ordernew}}")){
    Yii::app()->db->createCommand("
    UPDATE {{ordernew}} 
    SET 
    service_code='delivery',
    request_from='pos'
    WHERE service_code='pos'
    ")->query();
}

$model_view = new MG_view_item_relationship_subcategory;
$model_view->up();

$model_view = new MG_view_item_lang_size;
$model_view->up();

if(Yii::app()->db->schema->getTable("{{ordernew}}")){    
    $description="Use the {site_name} to get orders to customer";
    Yii::app()->db->createCommand("
    UPDATE {{services}} 
    SET 
    description=".q($description)."    
    WHERE service_code='delivery'
    ")->query();

    $description="Let customer {transaction_type} their orders to get more sales at a low fee";
    Yii::app()->db->createCommand("
    UPDATE {{services}} 
    SET 
    description=".q($description)."    
    WHERE service_code='pickup'
    ")->query();

    $description="Let customer {transaction_type} at your restaurant at low fee";
    Yii::app()->db->createCommand("
    UPDATE {{services}} 
    SET 
    description=".q($description)."    
    WHERE service_code='dinein'
    ")->query();
}

// UPDATE VERSION
$backend_version = '2.0.7';
if(Yii::app()->db->schema->getTable("{{option}}")){ 
    Yii::app()->db->createCommand("
    UPDATE {{option}} 
    SET 
    option_value = ".q($backend_version)."
    WHERE option_name = 'backend_version'
    ")->query();
}


$model_pages = AR_pages::model()->find("owner=:owner AND slug=:slug",[
    ':owner'=>'seo',
    'slug'=>'order-delicious-food-from-the-comfort-of-your-home-explore-our-restaurant-food-ordering-website-now'
]);  
if(Yii::app()->db->schema->getTable("{{pages}}") && !$model_pages ){
    $stmt = "
    INSERT INTO {{pages}} ( `owner`, `merchant_id`, `page_type`, `slug`, `title`, `long_content`, `short_content`, `meta_title`, `meta_description`, `meta_keywords`, `meta_image`, `path`, `status`, `date_created`, `date_modified`, `ip_address`) VALUES
    ('seo', 0, 'page', 'order-delicious-food-from-the-comfort-of-your-home-explore-our-restaurant-food-ordering-website-now', 'Homepage', NULL, '', 'Order Delicious Food from the Comfort of Your Home - Explore Our Restaurant Food Ordering Website Now!', 'Order food online from multiple restaurants with ease at our restaurant food ordering website. Enjoy the convenience of browsing menus, placing orders, and getting your favorite meals delivered to your doorstep. Satisfy your cravings today!', 'Delicious cuisine,Dining experience,International cuisine,Fine dining,Family-friendly,Special occasions', '', '', 'publish', '2023-06-15 18:35:44', '2023-06-15 18:41:51', '127.0.0.1'),
    ('seo', 0, 'page', 'find-your-perfect-meal-with-our-restaurant-food-ordering-search-explore-the-best-restaurant-results-now', 'Search results', NULL, '', 'Find Your Perfect Meal with Our Restaurant Food Ordering Search - Explore the Best Restaurant Results Now!', 'Looking for the best dining experience? Look no further than our multiple restaurant Karenderia! Our website offers a seamless food ordering process and a comprehensive search feature that allows you to find the perfect restaurant for your taste buds. Wit', 'Restaurants,Dining,Cuisine,Menu,Food,Reservation,Offers', '', '', 'publish', '2023-06-15 18:37:42', '2023-06-15 18:43:28', '127.0.0.1'),
    ('seo', 0, 'page', 'get-in-touch-with-us-contact-our-team-for-exceptional-customer-service-and-support', 'Contact us', NULL, '', 'Get in Touch with Us - Contact Our Team for Exceptional Customer Service and Support', 'Looking to get in touch with us? Our contact page is the perfect place to start. Whether you have a question, comment, or feedback, we\'re always happy to hear from our customers. Simply fill out the form on our contact page, and we\'ll get back to you as s', '', '', '', 'publish', '2023-06-15 18:38:48', '2023-06-15 18:43:59', '127.0.0.1'),
    ('seo', 0, 'page', 'explore-our-mouth-watering-cuisine-list-a-culinary-journey-you-dont-want-to-miss', 'Cuisine', NULL, '', 'Explore Our Mouth-Watering Cuisine List - A Culinary Journey You Don\'t Want to Miss!', 'At our multiple restaurant karenderia, we offer a diverse cuisine list that caters to all taste buds. From savory Filipino dishes to international favorites, our menu is sure to satisfy your cravings. Indulge in our delicious meals and experience the best', '', '', '', 'publish', '2023-06-15 18:40:07', '2023-06-15 18:44:26', '127.0.0.1'),
    ('seo', 0, 'page', 'explore-our-mouth-watering-menu-delicious-burgers-fries-and-more', 'Menu', NULL, '', 'Explore Our Mouth-Watering Menu - Delicious Burgers, Fries, and More!', 'Looking for a diverse range of mouth-watering dishes? Look no further than our restaurant menu! From savory appetizers to delectable entrees and desserts, we have something for everyone. Our menu is carefully crafted using only the freshest ingredients, e', '', '', '', 'publish', '2023-06-15 18:46:27', '2023-06-15 23:27:39', '127.0.0.1'),
    ('seo', 0, 'page', 'login-to-your-restaurant-account-manage-your-menu-and-orders-with-ease', 'Login', NULL, '', 'Login to Your Restaurant Account - Manage Your Menu and Orders with Ease', 'Welcome to our restaurant login page! Access your account and manage your restaurant\'s menu, orders, and promotions with ease. Join our platform today and offer your customers the best dining experience. Sign in now and take your business to new heights!', '', '', '', 'publish', '2023-06-15 18:47:13', '2023-06-15 18:47:13', '127.0.0.1'),
    ('seo', 0, 'page', 'join-our-restaurant-network-and-expand-your-reach-signup-today', 'Signup page', NULL, '', 'Join Our Restaurant Network and Expand Your Reach - Signup Today!', 'Looking to expand your restaurant\'s online presence? Sign up with our multiple restaurant Karenderia platform and showcase your delicious cuisine to a wider audience. Our user-friendly interface and powerful marketing tools make it easy to manage your onl', '', '', '', 'publish', '2023-06-15 18:48:23', '2023-06-15 18:48:23', '127.0.0.1'),
    ('seo', 0, 'page', 'effortlessly-manage-your-account-profile-with-our-user-friendly-platform-sign-up-today', 'Manage account', NULL, '', 'Effortlessly Manage Your Account Profile with Our User-Friendly Platform - Sign Up Today!', 'Effortlessly manage your account profile with our user-friendly platform. Update your personal information, track your orders, and enjoy a seamless dining experience at our multiple restaurant karenderia.', '', '', '', 'publish', '2023-06-15 18:50:57', '2023-06-15 18:54:15', '127.0.0.1'),
    ('seo', 0, 'page', 'secure-your-account-change-password-for-enhanced-online-security', 'Change password', NULL, '', 'Secure Your Account: Change Password for Enhanced Online Security', 'Boost your account security with a password change on our website. Safeguard your personal information and enjoy peace of mind knowing your account is protected. Follow our simple steps to update your password and fortify your online presence today.', '', '', '', 'publish', '2023-06-15 18:56:26', '2023-06-15 18:56:26', '127.0.0.1'),
    ('seo', 0, 'page', 'manage-your-orders-convenient-access-to-your-restaurant-accounts-order-list', 'User Orders', NULL, '', 'Manage Your Orders: Convenient Access to Your Restaurant Account\'s Order List', 'Easily track and manage your restaurant orders with our user-friendly account interface. Stay organized and informed with instant access to your order list, allowing you to review, modify, and monitor the status of your past and current orders. Streamline your dining experience and enjoy seamless control over your restaurant account\'s order history.', '', '', '', 'publish', '2023-06-15 18:58:09', '2023-06-15 18:59:08', '127.0.0.1'),
    ('seo', 0, 'page', 'effortless-address-management-save-and-update-your-accounts-address', 'User address', NULL, '', 'Effortless Address Management: Save and Update Your Account\'s Address', 'Simplify your address management with our account feature that allows you to save and update your addresses effortlessly. Whether it\'s for shipping, billing, or personal preferences, easily store multiple addresses and conveniently select them during checkout. Experience convenience and efficiency as you streamline your account\'s address information to enhance your online experience.', '', '', '', 'publish', '2023-06-15 19:01:00', '2023-06-15 19:01:00', '127.0.0.1'),
    ('seo', 0, 'page', 'secure-and-convenient-manage-saved-payment-methods-in-your-account', 'User saved payments', NULL, '', 'Secure and Convenient: Manage Saved Payment Methods in Your Account', 'Experience hassle-free transactions with our account\'s saved payment feature. Safely store your preferred payment methods for quick and secure checkouts. Enjoy the convenience of managing and updating your saved payment options, providing you with a seamless and efficient payment experience. Simplify your online transactions and enjoy peace of mind with our reliable and secure account saved payment feature.', '', '', '', 'publish', '2023-06-15 19:01:57', '2023-06-15 19:01:57', '127.0.0.1'),
    ('seo', 0, 'page', 'personalized-favorites-store-and-access-your-accounts-preferred-selections', 'User saved store', NULL, '', 'Personalized Favorites: Store and Access Your Account\'s Preferred Selections', 'Make your online experience truly tailored to your preferences by utilizing our account\'s \'Store Favorites\' feature. Save your favorite items, products, or content to easily access them whenever you visit our website. Whether it\'s for shopping, browsing, or entertainment, enjoy the convenience of having your preferred selections readily available at your fingertips. Enhance your user experience and discover the power of personalized favorites with our account\'s intuitive feature.', '', '', '', 'publish', '2023-06-15 19:02:51', '2023-06-15 19:02:51', '127.0.0.1'),
    ('seo', 0, 'page', 'join-the-culinary-experience-sign-up-for-a-memorable-restaurant-account', 'Restaurant signup', NULL, '', 'Join the Culinary Experience: Sign Up for a Memorable Restaurant Account', 'Embark on a delightful culinary journey by signing up for a restaurant account. Unlock exclusive benefits, personalized recommendations, and seamless online ordering. Experience the convenience of managing reservations, accessing loyalty programs, and receiving updates on special promotions. Join today and indulge in a memorable dining experience tailored to your preferences. Start your gastronomic adventure with our easy restaurant signup process.', '', '', '', 'publish', '2023-06-15 19:04:07', '2023-06-15 19:04:07', '127.0.0.1'),
    ('seo', 0, 'page', 'effortless-dining-experience-streamlined-checkout-at-our-restaurant', 'Checkout', NULL, '', 'Effortless Dining Experience: Streamlined Checkout at Our Restaurant', 'Enjoy a seamless and hassle-free checkout experience at our restaurant. Our user-friendly checkout page ensures a smooth transaction process, allowing you to review your order, make any necessary modifications, and securely complete your payment. With convenient options for delivery or pickup, you can finalize your dining experience with ease. Experience efficiency and satisfaction as you navigate our optimized restaurant checkout page, making your journey from selection to satisfaction a breeze.', '', '', '', 'publish', '2023-06-15 19:05:04', '2023-06-15 19:05:04', '127.0.0.1'),
    ('seo', 0, 'page', 'simplified-dining-experience-guest-checkout-at-our-restaurant', 'Guest checkout', NULL, '', 'Simplified Dining Experience: Guest Checkout at Our Restaurant', 'Indulge in a hassle-free dining experience with our guest checkout option at the restaurant. No account creation required! Seamlessly proceed through the checkout process, review your order details, and securely complete your payment as a guest. Whether you\'re a first-time visitor or prefer to skip the account setup, our streamlined guest checkout page ensures a smooth and efficient transaction. Enjoy the convenience and ease of a simplified dining experience, tailored for guests like you.', '', '', '', 'publish', '2023-06-15 19:06:03', '2023-06-15 19:06:03', '127.0.0.1'),
    ('seo', 0, 'page', 'reserve-your-table-effortless-restaurant-table-booking', 'Table booking', NULL, '', 'Reserve Your Table: Effortless Restaurant Table Booking', 'Secure your dining experience with ease through our hassle-free restaurant table booking. Reserve your preferred table in advance, ensuring a seamless and enjoyable dining occasion. Whether it\'s for a romantic dinner, a family gathering, or a business meeting, our convenient booking process allows you to select the date, time, and party size effortlessly. Experience personalized hospitality and guarantee your spot at our restaurant by making a reservation today. Unlock exceptional dining moments with our effortless table booking service.', '', '', '', 'publish', '2023-06-15 19:07:18', '2023-06-15 19:07:18', '127.0.0.1'),
    ('seo', 0, 'page', 'take-control-of-your-reservations-manage-restaurant-table-bookings', 'Manage table booking', NULL, '', 'Take Control of Your Reservations: Manage Restaurant Table Bookings', 'Empower yourself with the ability to effortlessly manage your restaurant table bookings. With our intuitive management system, you can easily view, modify, and cancel your reservations. Keep track of your upcoming dining plans, make adjustments as needed, and ensure a smooth and seamless experience. Take control of your restaurant bookings and enjoy the convenience of managing your table reservations with ease. Enhance your dining journey with our efficient table booking management feature.', '', '', '', 'publish', '2023-06-15 19:07:59', '2023-06-15 19:07:59', '127.0.0.1'),
    ('seo', 0, 'page', 'stay-in-the-know-track-your-favorite-restaurants-latest-updates-and-specials', 'Tracking order', NULL, '', 'Stay in the Know: Track Your Favorite Restaurant\'s Latest Updates and Specials!', 'Welcome to our Restaurant Tracking Page! Stay informed and never miss a beat with the latest updates from your favorite dining establishments. Track new menu additions, seasonal specials, promotions, and events happening at the restaurants you love. Our comprehensive tracking system ensures you\'re always in the loop, allowing you to plan your culinary adventures with ease. Discover the freshest flavors, follow the trends, and satisfy your cravings by staying connected to the pulse of the restaurant scene. Start tracking today and never miss out on the exciting happenings at your go-to restaurants!', '', '', '', 'publish', '2023-06-15 23:34:56', '2023-06-15 23:34:56', '127.0.0.1');
    ";
    Yii::app()->db->createCommand($stmt)->query();
}



require_once 'fixed-menu.php';  
require_once 'fixed-menu-merchant.php';  


// END 1.0.8 UPDATE

// 1.0.9 UPDATE

if(!Yii::app()->db->schema->getTable("{{currency_exchangerate}}")){    
    $table->createTable(
        "{{currency_exchangerate}}",
        array(
          'id'=>'pk',		        
          'provider'=>"varchar(100) NOT NULL DEFAULT ''", 
          'base_currency'=>"varchar(10) NOT NULL DEFAULT ''", 
          'currency_code'=>"varchar(10) NOT NULL DEFAULT ''", 
          'exchange_rate'=>"decimal(10,4) DEFAULT '0.0000'", 
          'date_created'=>"datetime DEFAULT NULL", 
          'date_modified'=>"datetime DEFAULT NULL", 
          'ip_address'=>"varchar(50) NOT NULL DEFAULT ''", 
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');
    $table->create_Index("{{currency_exchangerate}}",array(
        'provider','currency_code','exchange_rate','base_currency'
    )); 	    
    $data[] = "{{currency_exchangerate}} table created";
} else $data[] = "{{currency_exchangerate}} table already exist";

if(Yii::app()->db->schema->getTable("{{currency}}")){
    $data[] = $table->add_Column("{{currency}}",array(
        'status'=>"varchar(100) NOT NULL DEFAULT 'publish' AFTER `thousand_separator`",
    ));
}

if(Yii::app()->db->schema->getTable("{{voucher_new}}")){
    $data[] = $table->add_Column("{{voucher_new}}",array(
        'visible'=>"smallint(1) NOT NULL DEFAULT '1'",
        'max_order'=>"decimal(10,2) DEFAULT '0.00' AFTER `used_once`",
        'max_discount_cap'=>"decimal(10,2) NOT NULL DEFAULT '0.00' AFTER `used_once`",
        'applicable_to'=>"text AFTER `selected_customer`",
    ));
}


Yii::app()->db->createCommand("    
     ALTER TABLE {{voucher_new}} CHANGE `min_order` `min_order` DECIMAL(14,2) NOT NULL DEFAULT '0.00';
    ")->query();    

if(Yii::app()->db->schema->getTable("{{ordernew}}")){
    $data[] = $table->add_Column("{{ordernew}}",array(        
        'card_fee'=>"decimal(10,4) NOT NULL DEFAULT '0.0000' AFTER `packaging_fee`",
        'admin_base_currency'=>"varchar(10) NOT NULL DEFAULT '' AFTER `exchange_rate`",
        'exchange_rate_use_currency_to_admin'=>"decimal(10,4) NOT NULL DEFAULT '1.0000' AFTER `exchange_rate`",
        'exchange_rate_merchant_to_admin'=>"decimal(10,4) NOT NULL DEFAULT '1.0000' AFTER `exchange_rate`",
        'exchange_rate_admin_to_merchant'=>"decimal(10,4) NOT NULL DEFAULT '1.0000' AFTER `exchange_rate`",
    ));    

    Yii::app()->db->createCommand("
    ALTER TABLE {{ordernew}} CHANGE `points` `points` DECIMAL(10,4) NOT NULL DEFAULT '0.0';
    ALTER TABLE {{ordernew}} CHANGE `total_discount` `total_discount` DECIMAL(10,4) NOT NULL DEFAULT '0.0';
    ALTER TABLE {{ordernew}} CHANGE `sub_total` `sub_total` DECIMAL(10,4) NOT NULL DEFAULT '0.0';
    ALTER TABLE {{ordernew}} CHANGE `sub_total_less_discount` `sub_total_less_discount` DECIMAL(10,4) NOT NULL DEFAULT '0.0';
    ALTER TABLE {{ordernew}} CHANGE `service_fee` `service_fee` DECIMAL(10,4) NOT NULL DEFAULT '0.0';
    ALTER TABLE {{ordernew}} CHANGE `small_order_fee` `small_order_fee` DECIMAL(10,4) NOT NULL DEFAULT '0.0';
    ALTER TABLE {{ordernew}} CHANGE `delivery_fee` `delivery_fee` DECIMAL(10,4) NOT NULL DEFAULT '0.0';
    ALTER TABLE {{ordernew}} CHANGE `packaging_fee` `packaging_fee` DECIMAL(10,4) NOT NULL DEFAULT '0.0';
    ALTER TABLE {{ordernew}} CHANGE `tax` `tax` DECIMAL(10,4) NOT NULL DEFAULT '0.0';
    ALTER TABLE {{ordernew}} CHANGE `tax_total` `tax_total` DECIMAL(10,4) NOT NULL DEFAULT '0.0';
    ALTER TABLE {{ordernew}} CHANGE `courier_tip` `courier_tip` DECIMAL(10,4) NOT NULL DEFAULT '0.0';
    ALTER TABLE {{ordernew}} CHANGE `total` `total` DECIMAL(10,4) NOT NULL DEFAULT '0.0';
    ALTER TABLE {{ordernew}} CHANGE `promo_total` `promo_total` DECIMAL(10,4) NOT NULL DEFAULT '0.0';
    ALTER TABLE {{ordernew}} CHANGE `offer_total` `offer_total` DECIMAL(10,4) NOT NULL DEFAULT '0.0';
    ALTER TABLE {{ordernew}} CHANGE `commission_value` `commission_value` DECIMAL(10,4) NOT NULL DEFAULT '0.0';
    ALTER TABLE {{ordernew}} CHANGE `commission` `commission` DECIMAL(10,4) NOT NULL DEFAULT '0.0';
    ALTER TABLE {{ordernew}} CHANGE `merchant_earning` `merchant_earning` DECIMAL(10,4) NOT NULL DEFAULT '0.0';
    ALTER TABLE {{ordernew}} CHANGE `total_original` `total_original` DECIMAL(10,4) NOT NULL DEFAULT '0.0';
    ALTER TABLE {{ordernew}} CHANGE `commission_original` `commission_original` DECIMAL(10,4) NOT NULL DEFAULT '0.0';
    ALTER TABLE {{ordernew}} CHANGE `merchant_earning_original` `merchant_earning_original` DECIMAL(10,4) NOT NULL DEFAULT '0.0';
    ALTER TABLE {{ordernew}} CHANGE `adjustment_commission` `adjustment_commission` DECIMAL(10,4) NOT NULL DEFAULT '0.0';
    ALTER TABLE {{ordernew}} CHANGE `adjustment_total` `adjustment_total` DECIMAL(10,4) NOT NULL DEFAULT '0.0';
    ALTER TABLE {{ordernew}} CHANGE `exchange_rate` `exchange_rate` DECIMAL(10,4) NOT NULL DEFAULT '1';    
    ")->query();    
}

if(Yii::app()->db->schema->getTable("{{wallet_transactions}}")){
    Yii::app()->db->createCommand("
    ALTER TABLE {{wallet_transactions}} CHANGE `transaction_amount` `transaction_amount` DECIMAL(10,4) NOT NULL DEFAULT '0.0';  
    ALTER TABLE {{wallet_transactions}} CHANGE `running_balance` `running_balance` DECIMAL(10,4) NOT NULL DEFAULT '0.0'; 
    ALTER TABLE {{wallet_transactions}} CHANGE `orig_transaction_amount` `orig_transaction_amount` DECIMAL(10,4) NOT NULL DEFAULT '0.0'; 
    ")->query();    
    
    $data[] = $table->add_Column("{{wallet_transactions}}",array(
        'orig_transaction_amount'=>"decimal(10,4) NOT NULL DEFAULT '0' AFTER `status`",
        'merchant_base_currency'=>"varchar(10) NOT NULL DEFAULT '' AFTER `status`",
        'admin_base_currency'=>"varchar(10) NOT NULL DEFAULT '' AFTER `status`",
        'exchange_rate_merchant_to_admin'=>"decimal(10,4) NOT NULL DEFAULT '1' AFTER `status`",
        'exchange_rate_admin_to_merchant'=>"decimal(10,4) NOT NULL DEFAULT '1' AFTER `status`",
        'reference_id'=>"bigint(20) NOT NULL DEFAULT '0' AFTER `status`",
        'reference_id1'=>"varchar(255) NOT NULL DEFAULT '' AFTER `status`",
    ));
}

if(Yii::app()->db->schema->getTable("{{ordernew_transaction}}")){
    $data[] = $table->add_Column("{{ordernew_transaction}}",array(
        'to_currency_code'=>"varchar(10) NOT NULL DEFAULT '' AFTER `currency_code`",       
        'exchange_rate'=>"decimal(10,4) NOT NULL DEFAULT '1' AFTER `currency_code`",   
        'admin_base_currency'=>"varchar(10) NOT NULL DEFAULT '' AFTER `currency_code`",
        'exchange_rate_merchant_to_admin'=>"decimal(10,4) NOT NULL DEFAULT '1' AFTER `currency_code`",          
        'exchange_rate_admin_to_merchant'=>"decimal(10,4) NOT NULL DEFAULT '1' AFTER `currency_code`",  
    ));
}

if(Yii::app()->db->schema->getTable("{{invoice}}")){
    $data[] = $table->add_Column("{{invoice}}",array(
        'merchant_base_currency'=>"varchar(10) NOT NULL DEFAULT '' AFTER `amount_paid`",               
        'admin_base_currency'=>"varchar(10) NOT NULL DEFAULT '' AFTER `amount_paid`",
        'exchange_rate_merchant_to_admin'=>"decimal(10,4) NOT NULL DEFAULT '1' AFTER `amount_paid`",          
        'exchange_rate_admin_to_merchant'=>"decimal(10,4) NOT NULL DEFAULT '1' AFTER `amount_paid`",          
    ));
    Yii::app()->db->createCommand("
    ALTER TABLE {{invoice}} CHANGE `invoice_total` `invoice_total` DECIMAL(10,4) NOT NULL DEFAULT '0.0';
    ALTER TABLE {{invoice}} CHANGE `amount_paid` `amount_paid` DECIMAL(10,4) NOT NULL DEFAULT '0.0';
    ")->query();    
}

if(Yii::app()->db->schema->getTable("{{bank_deposit}}")){
    $data[] = $table->add_Column("{{bank_deposit}}",array(
        'use_amount'=>"decimal(10,4) NOT NULL DEFAULT '0.0000' AFTER `amount`",         
        'use_currency_code'=>"varchar(10) NOT NULL DEFAULT '' AFTER `amount`",
        'base_currency_code'=>"varchar(10) NOT NULL DEFAULT '' AFTER `amount`",
        'admin_base_currency'=>"varchar(10) NOT NULL DEFAULT '' AFTER `amount`",
        'exchange_rate'=>"decimal(10,4) NOT NULL DEFAULT '1' AFTER `amount`",
        'exchange_rate_merchant_to_admin'=>"decimal(10,4) NOT NULL DEFAULT '1' AFTER `amount`",
        'exchange_rate_admin_to_merchant'=>"decimal(10,4) NOT NULL DEFAULT '1' AFTER `amount`",
    ));

    Yii::app()->db->createCommand("
    ALTER TABLE {{bank_deposit}} CHANGE `amount` `amount` DECIMAL(10,4) NOT NULL DEFAULT '0.0'    
    ")->query();    
}

if(Yii::app()->db->schema->getTable("{{driver_vehicle}}")){
    $data[] = $table->add_Column("{{driver_vehicle}}",array(
        'merchant_id'=>"bigint(20) NOT NULL DEFAULT '0' AFTER `vehicle_type_id`",      
    ));
    $table->create_Index("{{driver_vehicle}}",array(
        'merchant_id'
    )); 	    
}

if(Yii::app()->db->schema->getTable("{{driver_shift_schedule}}")){
    $data[] = $table->add_Column("{{driver_shift_schedule}}",array(
        'merchant_id'=>"bigint(20) NOT NULL DEFAULT '0' AFTER `zone_id`",      
    ));
    $table->create_Index("{{driver_shift_schedule}}",array(
        'merchant_id'
    )); 	    
}

if(Yii::app()->db->schema->getTable("{{driver}}")){
    $data[] = $table->add_Column("{{driver}}",array(
        'default_currency'=>"varchar(10) NOT NULL DEFAULT '' AFTER `notification`",      
    ));
    $table->create_Index("{{driver}}",array(
        'default_currency'
    )); 	    
}


if(Yii::app()->db->schema->getTable("{{driver_collect_cash}}")){
    $data[] = $table->add_Column("{{driver_collect_cash}}",array(
        'merchant_base_currency'=>"varchar(10) NOT NULL DEFAULT '' AFTER `amount_collected`",      
        'admin_base_currency'=>"varchar(10) NOT NULL DEFAULT '' AFTER `amount_collected`", 
        'exchange_rate_merchant_to_admin'=>"decimal(10,4) NOT NULL DEFAULT '1' AFTER `amount_collected`",
        'exchange_rate_admin_to_merchant'=>"decimal(10,4) NOT NULL DEFAULT '1' AFTER `amount_collected`",
    ));    
}

if(Yii::app()->db->schema->getTable("{{services}}")){
    $data[] = $table->add_Column("{{services}}",array(
        'sequence'=>"int(14) NOT NULL DEFAULT '0' AFTER `font_color_hex`",      
    ));    
}

if(!Yii::app()->db->schema->getTable("{{paydelivery}}")){    
    $table->createTable(
        "{{paydelivery}}",
        array(
          'id'=>'pk',		        
          'payment_name'=>"varchar(255) NOT NULL DEFAULT ''",
          'photo'=>"varchar(255) NOT NULL DEFAULT ''",
          'path'=>"varchar(255) NOT NULL DEFAULT ''",
          'sequence'=>"int(14) NOT NULL DEFAULT '0'",
          'status'=>"varchar(100) NOT NULL DEFAULT ''",
          'date_created'=>"datetime NOT NULL DEFAULT CURRENT_TIMESTAMP",
          'date_modified'=>"datetime NOT NULL DEFAULT CURRENT_TIMESTAMP",
          'ip_address'=>"varchar(100) NOT NULL DEFAULT ''",
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');
    $table->create_Index("{{paydelivery}}",array(
        'payment_name','status'
    )); 	    
    $data[] = "{{paydelivery}} table created";
} else $data[] = "{{paydelivery}} table already exist";


$payment = AR_payment_gateway::model()->find("payment_code=:payment_code",[
    ':payment_code'=>'paydelivery'
]);
if(!$payment){    
    $payment = new AR_payment_gateway();
    $payment->payment_name= "Pay on delivery";
    $payment->payment_code= "paydelivery";
    $payment->logo_type= "icon";
    $payment->logo_class= "zmdi zmdi-card";
    $payment->status= "active";
    $payment->save();
}

$base_currency = Price_Formatter::$number_format['currency_code'];

Yii::app()->db->createCommand("
UPDATE {{ordernew}} SET admin_base_currency = ".q($base_currency)."
WHERE admin_base_currency = ''
")->query();

Yii::app()->db->createCommand("
UPDATE {{ordernew_transaction}} SET to_currency_code = ".q($base_currency)."
WHERE to_currency_code = ''
")->query();

Yii::app()->db->createCommand("
UPDATE {{ordernew_transaction}} SET admin_base_currency = ".q($base_currency)."
WHERE admin_base_currency = ''
")->query();

Yii::app()->db->createCommand("
UPDATE {{wallet_transactions}} SET merchant_base_currency = ".q($base_currency)."
WHERE merchant_base_currency = ''
")->query();

Yii::app()->db->createCommand("
UPDATE {{wallet_transactions}} SET admin_base_currency = ".q($base_currency)."
WHERE admin_base_currency = ''
")->query();

Yii::app()->db->createCommand("
UPDATE {{invoice}} SET merchant_base_currency = ".q($base_currency)."
WHERE merchant_base_currency = ''
")->query();

Yii::app()->db->createCommand("
UPDATE {{invoice}} SET admin_base_currency = ".q($base_currency)."
WHERE admin_base_currency = ''
")->query();

Yii::app()->db->createCommand("
UPDATE {{bank_deposit}} SET use_currency_code = ".q($base_currency)."
WHERE use_currency_code = ''
")->query();

Yii::app()->db->createCommand("
UPDATE {{bank_deposit}} SET base_currency_code = ".q($base_currency)."
WHERE base_currency_code = ''
")->query();

Yii::app()->db->createCommand("
UPDATE {{bank_deposit}} SET admin_base_currency = ".q($base_currency)."
WHERE admin_base_currency = ''
")->query();

Yii::app()->db->createCommand("
UPDATE {{driver}} SET default_currency = ".q($base_currency)."
WHERE default_currency = ''
")->query();

Yii::app()->db->createCommand("
UPDATE {{driver_collect_cash}} SET merchant_base_currency = ".q($base_currency)."
WHERE merchant_base_currency = ''
")->query();

Yii::app()->db->createCommand("
UPDATE {{driver_collect_cash}} SET admin_base_currency = ".q($base_currency)."
WHERE admin_base_currency = ''
")->query();

// END 1.0.9 UPDATE


// 1.1.1 UPDATE
if(Yii::app()->db->schema->getTable("{{cart}}")){
    $data[] = $table->add_Column("{{cart}}",array(
        'order_reference'=>"varchar(255) NOT NULL DEFAULT '' AFTER `if_sold_out`",  
        'hold_order'=>"int(1) NOT NULL DEFAULT '0' AFTER `if_sold_out`",                     
    ));    
}
if(Yii::app()->db->schema->getTable("{{driver}}")){
    $data[] = $table->add_Column("{{driver}}",array(
        'is_online'=>"smallint(1) NOT NULL DEFAULT '0' AFTER `default_currency`",          
    ));    
}
if(Yii::app()->db->schema->getTable("{{driver_schedule}}")){
    $data[] = $table->add_Column("{{driver_schedule}}",array(
        'on_demand'=>"int(1) NOT NULL DEFAULT '0' AFTER `active`",          
    ));    
}
// END 1.1.1 UPDATE

// 1.1.2 UPDATE
if(Yii::app()->db->schema->getTable("{{printer}}")){
    $data[] = $table->add_Column("{{printer}}",array(
        'printer_bt_name'=>"varchar(255) NOT NULL DEFAULT '' AFTER `printer_name`",         
    ));    
}

if(Yii::app()->db->schema->getTable("{{banner}}")){
    $data[] = $table->add_Column("{{banner}}",array(
        'meta_value4'=>"int(10) NOT NULL DEFAULT '0' AFTER `meta_value2`", 
        'meta_value3'=>"varchar(255) NOT NULL DEFAULT '' AFTER `meta_value2`",         
    ));    
}
// END 1.1.2 UPDATE


// 1.1.3 UPDATE
if(Yii::app()->db->schema->getTable("{{merchant_user}}")){
    Yii::app()->db->createCommand("
    ALTER TABLE {{merchant_user}} CHANGE `first_name` `first_name` varchar(255) NOT NULL DEFAULT '';
    ALTER TABLE {{merchant_user}} CHANGE `last_name` `last_name` varchar(255) NOT NULL DEFAULT '';
    ")->query();
}

if(!Yii::app()->db->schema->getTable("{{discount}}")){    
    $table->createTable(
        "{{discount}}",
        array(
          'discount_id'=>'pk',		        
          'discount_uuid'=>"varchar(100) NOT NULL DEFAULT ''",              
          'merchant_id'=>"int(11) NOT NULL DEFAULT '0'",
          'transaction_type'=>"varchar(100) NOT NULL DEFAULT ''",
          'title'=>"varchar(255) NOT NULL DEFAULT ''",
          'description'=>"text",
          'discount_type'=>"varchar(100) NOT NULL DEFAULT ''",
          'amount'=>"decimal(12,4) NOT NULL DEFAULT '0.0000'",
          'minimum_amount'=>"decimal(12,4) DEFAULT '0.0000'",
          'maximum_amount'=>"decimal(12,4) NOT NULL DEFAULT '0.0000'",
          'start_date'=>"date DEFAULT NULL",
          'expiration_date'=>"date DEFAULT NULL",
          'status'=>"int(1) NOT NULL DEFAULT '1'",
          'sequence'=>"int(14) NOT NULL DEFAULT '0'",
          'date_created'=>"timestamp NULL DEFAULT NULL",
          'date_modified'=>"timestamp NULL DEFAULT NULL",
          'ip_address'=>"varchar(50) NOT NULL DEFAULT ''",              
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');
    $table->create_Index("{{discount}}",array(
        'merchant_id','status','discount_uuid','transaction_type'
    )); 	    
    $data[] = "{{discount}} table created";
} else $data[] = "{{discount}} table already exist";

if(Yii::app()->db->schema->getTable("{{ordernew}}")){
    $data[] = $table->add_Column("{{ordernew}}",array(
        'wallet_amount'=>"decimal(10,4) NOT NULL DEFAULT '0.0000' AFTER `total`",  
        'amount_due'=>"decimal(10,4) NOT NULL DEFAULT '0.0000' AFTER `total`",        
    ));    
}

if(Yii::app()->db->schema->getTable("{{wallet_transactions}}")){
    Yii::app()->db->createCommand("
    ALTER TABLE {{wallet_transactions}} CHANGE `reference_id` `reference_id` VARCHAR(255) NOT NULL DEFAULT '';    
    ")->query();
}

// END 1.1.3 UPDATE


// 1.1.4 UPDATE
if(Yii::app()->db->schema->getTable("{{item}}")){
    $data[] = $table->add_Column("{{item}}",array(
        'visible'=>"INT(1) NOT NULL DEFAULT '1' AFTER `color_hex`", 
    ));    
    $table->create_Index("{{item}}",array(
        'available','available_at_specific','visible','not_for_sale','item_token','sequence','points_enabled'
    )); 	    
}

if(Yii::app()->db->schema->getTable("{{push}}")){
    $data[] = $table->add_Column("{{push}}",array(
        'merchant_id'=>"int(14) NOT NULL DEFAULT '0' AFTER `push_uuid`",          
    ));    
}

if(Yii::app()->db->schema->getTable("{{client}}")){
    $model_view = new MG_view_user_union;
    $model_view->up();
}

if(Yii::app()->db->schema->getTable("{{merchant_meta}}")){
    $data[] = $table->createFullTextIndexIfNeeded("{{merchant_meta}}","meta_value","meta_value");
    $data[] = $table->createFullTextIndexIfNeeded("{{merchant_meta}}","meta_value1","meta_value1");
    $data[] = $table->createFullTextIndexIfNeeded("{{merchant_meta}}","meta_value2","meta_value2");
    $data[] = $table->createFullTextIndexIfNeeded("{{merchant_meta}}","meta_value3","meta_value3");
}

if(Yii::app()->db->schema->getTable("{{option}}")){
    $data[] = $table->createFullTextIndexIfNeeded("{{option}}","option_value","option_value");
}
// END 1.1.4 UPDATE

// 1.1.5 UPDATE
if(Yii::app()->db->schema->getTable("{{ordernew}}")){
    $data[] = $table->add_Column("{{ordernew}}",array(
        'created_at'=>"date DEFAULT NULL AFTER `date_created`",
    ));    
    
    $table->create_Index("{{ordernew}}",array(
        'created_at','date_created'
    )); 	    

    Yii::app()->db->createCommand("
    Update {{ordernew}}  SET `created_at` = DATE(`date_created`)
    WHERE `created_at` IS NULL 
    ")->query();
}
// 1.1.5 UPDATE


// 1.1.8 UPDATE
if(Yii::app()->db->schema->getTable("{{category_translation}}")){
    $data[] = $table->add_Column("{{category_translation}}",array(
        'merchant_id'=>"int(14) NOT NULL DEFAULT '0'", 
    ));    
}

if(Yii::app()->db->schema->getTable("{{subcategory_translation}}")){
    $data[] = $table->add_Column("{{subcategory_translation}}",array(
        'merchant_id'=>"int(14) NOT NULL DEFAULT '0'", 
    ));    
}

if(Yii::app()->db->schema->getTable("{{subcategory_item_translation}}")){
    $data[] = $table->add_Column("{{subcategory_item_translation}}",array(
        'merchant_id'=>"int(14) NOT NULL DEFAULT '0'", 
    ));    
}

if(Yii::app()->db->schema->getTable("{{category_relationship_dish}}")){
    $data[] = $table->add_Column("{{category_relationship_dish}}",array(
        'merchant_id'=>"int(14) NOT NULL DEFAULT '0'", 
    ));    
}

if(Yii::app()->db->schema->getTable("{{size_translation}}")){
    $data[] = $table->add_Column("{{size_translation}}",array(
        'merchant_id'=>"int(14) NOT NULL DEFAULT '0'", 
    ));    
}

if(Yii::app()->db->schema->getTable("{{ingredients_translation}}")){
    $data[] = $table->add_Column("{{ingredients_translation}}",array(
        'merchant_id'=>"int(14) NOT NULL DEFAULT '0'", 
    ));    
}

if(Yii::app()->db->schema->getTable("{{cooking_ref_translation}}")){
    $data[] = $table->add_Column("{{cooking_ref_translation}}",array(
        'merchant_id'=>"int(14) NOT NULL DEFAULT '0'", 
    ));    
}

if(Yii::app()->db->schema->getTable("{{templates}}")){
    $data[] = $table->add_Column("{{templates}}",array(
        'sms_template_id'=>"VARCHAR(255) NOT NULL DEFAULT '' AFTER `tags`",
    ));    
}

if(Yii::app()->db->schema->getTable("{{client_address}}")){
    $data[] = $table->add_Column("{{client_address}}",array(
        'company'=>"VARCHAR(255) NOT NULL DEFAULT '' AFTER `address_label`",
        'address_format_use'=>"int(1) NOT NULL DEFAULT '1' AFTER `address_label`",
    ));        
}

if(Yii::app()->db->schema->getTable("{{map_places}}")){
    $data[] = $table->add_Column("{{map_places}}",array(
        'parsed_address'=>"text",
    ));    
}

if(Yii::app()->db->schema->getTable("{{cart}}")){
    $data[] = $table->add_Column("{{cart}}",array(
        'send_order'=>"int(1) NOT NULL DEFAULT '0'",
        'payment_status'=>"int(1) NOT NULL DEFAULT '0'",
        'total'=>"decimal(10,4) NOT NULL DEFAULT '0.0000'",                
        'table_uuid'=>"varchar(100) NOT NULL DEFAULT ''",     
        'transaction_type'=>"varchar(100) NOT NULL DEFAULT ''",           
        'order_reference'=>"varchar(255) NOT NULL DEFAULT ''",      
        'hold_order_reference'=>"varchar(100) NOT NULL DEFAULT ''", 
        'change_trans'=>"int(1) NOT NULL DEFAULT '0'",
        'is_view'=>"int(1) NOT NULL DEFAULT '0'",
    ));        
}

if(!Yii::app()->db->schema->getTable("{{kitchen_order}}")){    
    $table->createTable(
        "{{kitchen_order}}",
        array(
          'kitchen_order_id'=>'pk',		        
          'is_completed'=>"tinyint(1) NOT NULL DEFAULT '0'",
          'merchant_id'=>"int(14) NOT NULL DEFAULT '0'",
          'order_reference'=>"varchar(50) NOT NULL DEFAULT ''",
          'order_ref_id'=>"varchar(100) NOT NULL DEFAULT ''",
          'table_uuid'=>"varchar(100) NOT NULL DEFAULT ''",
          'room_uuid'=>"varchar(100) NOT NULL DEFAULT ''",
          'customer_name'=>"varchar(200) NOT NULL DEFAULT ''",
          'transaction_type'=>"varchar(50) NOT NULL DEFAULT ''",
          'item_token'=>"varchar(255) NOT NULL DEFAULT ''",
          'qty'=>"int(14) NOT NULL DEFAULT '0'",
          'item_status'=>"varchar(50) NOT NULL DEFAULT 'queue'",
          'special_instructions'=>"text",
          'attributes'=>"text",
          'addons'=>"text",
          'whento_deliver'=>"varchar(50) NOT NULL DEFAULT ''",
          'delivery_date'=>"date DEFAULT NULL",
          'delivery_time'=>"varchar(50) NOT NULL DEFAULT ''",
          'timezone'=>"varchar(50) NOT NULL DEFAULT ''",
          'sequence'=>"int(14) NOT NULL DEFAULT '0'",
          'created_at'=>"datetime DEFAULT NULL",
          'updated_at'=>"datetime DEFAULT NULL",
          'date_completed'=>"datetime DEFAULT NULL"
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');
    $table->create_Index("{{kitchen_order}}",array(
        'merchant_id','table_uuid','item_token','item_status','item_row','is_completed','order_reference','order_ref_id',
        'room_uuid','transaction_type'
    )); 	    
    $data[] = "{{kitchen_order}} table created";
} else $data[] = "{{kitchen_order}} table already exist";

if(!Yii::app()->db->schema->getTable("{{customer_request}}")){    
    $table->createTable(
        "{{customer_request}}",
        array(
          'request_id'=>'pk',		        
          'merchant_id'=>"int(14) NOT NULL DEFAULT '0'", 
          'cart_uuid'=>"varchar(100) NOT NULL DEFAULT ''", 
          'table_uuid'=>"varchar(100) NOT NULL DEFAULT ''",           
          'transaction_type'=>"varchar(100) NOT NULL DEFAULT ''",
          'timezone'=>"varchar(100) NOT NULL DEFAULT ''",
          'request_item'=>"varchar(200) NOT NULL DEFAULT ''",
          'qty'=>"int(14) NOT NULL DEFAULT '0'",
          'request_time'=>"datetime DEFAULT NULL",
          'completed_time'=>"datetime DEFAULT NULL",
          'request_status'=>"varchar(20) NOT NULL DEFAULT 'pending'",
          'is_view'=>"int(1) DEFAULT '0'"
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');
    $table->create_Index("{{customer_request}}",array(
        'merchant_id','cart_uuid','table_uuid','transaction_type','request_status','is_view'
    )); 	    
    $data[] = "{{customer_request}} table created";
} else $data[] = "{{customer_request}} table already exist";

if(!Yii::app()->db->schema->getTable("{{table_status}}")){    
    $table->createTable(
        "{{table_status}}",
        array(
          'table_uuid'=>"varchar(100) NOT NULL DEFAULT ''",
          'merchant_id'=>"int(14) NOT NULL DEFAULT '0'",
          'status'=>"varchar(20) NOT NULL DEFAULT 'available'"
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');
    $table->create_Index("{{table_status}}",array(
        'table_uuid','status','merchant_id'
    )); 	    
    $data[] = "{{table_status}} table created";
} else $data[] = "{{table_status}} table already exist";


if(!Yii::app()->db->schema->getTable("{{table_device}}")){    
    $table->createTable(
        "{{table_device}}",
        array(
          'table_uuid'=>"varchar(100) NOT NULL DEFAULT ''",
          'device_id'=>"varchar(50) NOT NULL DEFAULT ''",
          'device_info'=>"varchar(50) NOT NULL DEFAULT ''",
          'created_at'=>"datetime DEFAULT NULL",
          'ip_address'=>"varchar(50) NOT NULL DEFAULT ''"
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');
    $table->create_Index("{{table_device}}",array(
        'table_uuid','device_id'
    )); 	    
    $data[] = "{{table_device}} table created";
} else $data[] = "{{table_device}} table already exist";


if(Yii::app()->db->schema->getTable("{{table_room}}")){
    $data[] = $table->add_Column("{{table_room}}",array(
        'sequence'=>"int(14) NOT NULL DEFAULT '0'",
    ));    
}

if(Yii::app()->db->schema->getTable("{{table_tables}}")){
    $data[] = $table->add_Column("{{table_tables}}",array(
        'sequence'=>"int(14) NOT NULL DEFAULT '0'",
        'device_id'=>"varchar(50) NOT NULL DEFAULT ''",
        'device_info'=>"varchar(100) NOT NULL DEFAULT ''"
    ));    
}

if(Yii::app()->db->schema->getTable("{{ordernew}}")){
    $data[] = $table->add_Column("{{ordernew}}",array(
        'order_reference'=>"varchar(100) NOT NULL DEFAULT ''",
    ));    
}

if(Yii::app()->db->schema->getTable("{{cart_addons}}")){
    $data[] = $table->add_Column("{{cart_addons}}",array(
        'created_at'=>"datetime DEFAULT NULL",
    ));    
}

if(Yii::app()->db->schema->getTable("{{printer}}")){
    $data[] = $table->add_Column("{{printer}}",array(
        'paper_width'=>"int(10) NOT NULL DEFAULT '58'",
        'auto_print'=>"int(1) NOT NULL DEFAULT '0'",
        'print_type'=>"varchar(20) NOT NULL DEFAULT 'raw'",
        'character_code'=>"varchar(20) NOT NULL DEFAULT ''",
        'auto_close'=>"int(1) NOT NULL DEFAULT '1'",
        'status'=>"int(1) NOT NULL DEFAULT '1'",
        'date_created'=>"datetime NOT NULL DEFAULT CURRENT_TIMESTAMP",
        'date_modified'=>"datetime NOT NULL DEFAULT CURRENT_TIMESTAMP",
        'ip_address'=>"varchar(50) NOT NULL DEFAULT ''",
        'device_id'=>"varchar(100) NOT NULL DEFAULT ''",  
        'platform'=>"varchar(20) NOT NULL DEFAULT ''",          
    ));    
}

if(Yii::app()->db->schema->getTable("{{cache}}")){
    $table->create_Index("{{cache}}",array(
        'date_modified'
    )); 	    
}

if(Yii::app()->db->schema->getTable("{{services}}")){
    $model_services = AR_services::model()->find("service_code=:service_code",[
        ':service_code'=>'takeout'
    ]);
    if(!$model_services){
        Yii::app()->db->createCommand("
        INSERT INTO `st_services` ( `service_code`, `service_name`, `description`, `color_hex`, `font_color_hex`, `sequence`, `status`, `date_created`, `date_modified`, `ip_address`) VALUES
          ('takeout', 'Takeout', '', '#eeeeee', '#c90076', 3, 'publish', '2024-04-02 09:54:24', '2024-04-10 09:31:01', '127.0.0.1')
        ")->query();
    }
}

if(Yii::app()->db->schema->getTable("{{sms_provider}}")){
    $model_sms = AR_sms_provider::model()->find("provider_id=:provider_id",[
        ':provider_id'=>'msg91'
    ]);
    if(!$model_sms){
        Yii::app()->db->createCommand("
        INSERT INTO `st_sms_provider` (`provider_id`, `provider_name`, `as_default`, `key1`, `key2`, `key3`, `key4`, `key5`, `key6`, `key7`) VALUES
        ('msg91', 'Msg91', 0, '', '', '', '', '', '', '')
        ")->query();
    }
}

$model_call_staff = AR_admin_meta::model()->find("meta_name=:meta_name",[
    ':meta_name'=>'call_staff_menu',    
]);  
if(Yii::app()->db->schema->getTable("{{pages}}") && !$model_call_staff ){
    $stmt_staff = "
    INSERT INTO `st_admin_meta` ( `meta_name`, `meta_value`, `meta_value1`, `date_modified`) VALUES
    ( 'call_staff_menu', 'Table Clean', '', '2024-05-17 08:30:59'),
    ( 'call_staff_menu', 'Phone Charger', '', '2024-05-17 08:31:10'),
    ( 'call_staff_menu', 'Kids Cutlery', '', '2024-05-17 08:31:21'),
    ( 'call_staff_menu', 'Toothpick', '', '2024-05-17 08:32:56'),
    ( 'call_staff_menu', 'Extra Bowl', '', '2024-05-17 08:33:22'),
    ( 'call_staff_menu', 'Extra Plate', '', '2024-05-17 08:33:32'),
    ( 'call_staff_menu', 'Ice', '', '2024-05-17 08:33:43'),
    ( 'call_staff_menu', 'Chopstick', '', '2024-05-17 08:33:52'),
    ( 'call_staff_menu', 'Water refill', '', '2024-05-17 08:33:59'),
    ( 'call_staff_menu', 'Wet tissue', '', '2024-05-17 08:33:14'),
    ( 'call_staff_menu', ' Paper napkin', '', '2024-04-03 18:04:12');
    ";
    Yii::app()->db->createCommand($stmt_staff)->query();
}


// END 1.1.8 UPDATE


// START 1.1.9
if(!Yii::app()->db->schema->getTable("{{plan_subscriptions}}")){    
    $table->createTable(
        "{{plan_subscriptions}}",
        array(
            'id'=>'pk', 
            'payment_id'=>"varchar(50) NOT NULL DEFAULT ''", 
            'payment_code'=>"varchar(50) NOT NULL DEFAULT ''", 
            'subscriber_id'=>"int(10) NOT NULL DEFAULT '0'", 
            'package_id'=>"int(10) NOT NULL DEFAULT '0'", 
            'plan_name'=>"varchar(255) NOT NULL DEFAULT ''", 
            'billing_cycle'=>"varchar(50) NOT NULL DEFAULT ''", 
            'amount'=>"decimal(10,4) NOT NULL DEFAULT '0'", 
            'currency_code'=>"varchar(5) NOT NULL DEFAULT ''", 
            'subscriber_type'=>"varchar(50) NOT NULL DEFAULT 'merchant'", 
            'subscription_id'=>"varchar(255) NOT NULL DEFAULT ''", 
            'created_at'=>"date DEFAULT NULL", 
            'next_due'=>"date DEFAULT NULL", 
            'expiration'=>"date DEFAULT NULL", 
            'current_start'=>"date DEFAULT NULL", 
            'current_end'=>"date DEFAULT NULL", 
            'jobs'=>"varchar(100) NOT NULL DEFAULT ''", 
            'sucess_url'=>"varchar(255) NOT NULL DEFAULT ''", 
            'failed_url'=>"varchar(255) NOT NULL DEFAULT ''", 
            'status'=>"varchar(50) NOT NULL DEFAULT 'pending'", 
            'date_created'=>"timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP", 
            'updated_at'=>"timestamp NULL DEFAULT NULL",             
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');
    $table->create_Index("{{plan_subscriptions}}",array(
        'subscriber_id','subscriber_type','package_id','subscription_id','status'
    )); 	    
    $data[] = "{{plan_subscriptions}} table created";
} else $data[] = "{{plan_subscriptions}} table already exist";


if(!Yii::app()->db->schema->getTable("{{plans_create_payment}}")){    
    $table->createTable(
        "{{plans_create_payment}}",
        array(
            'id'=>'pk', 
            'payment_id'=>"varchar(100) NOT NULL DEFAULT ''",             
            'package_id'=>"int(14) NOT NULL DEFAULT '0'",             
            'subscriber_id'=>"int(14) NOT NULL DEFAULT '0'",             
            'subscription_type'=>"varchar(50) NOT NULL DEFAULT ''", 
            'subscriber_type'=>"varchar(50) NOT NULL DEFAULT 'merchant'", 
            'jobs'=>"varchar(100) NOT NULL DEFAULT ''", 
            'success_url'=>"varchar(255) DEFAULT ''", 
            'failed_url'=>"varchar(255) NOT NULL DEFAULT ''", 
            'created_at'=>"timestamp NULL DEFAULT NULL", 
            'updated_at'=>"timestamp NULL DEFAULT NULL",             
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');
    $table->create_Index("{{plans_create_payment}}",array(
        'payment_id','package_id','subscriber_id','subscription_type','subscriber_type'
    )); 	    
    $data[] = "{{plans_create_payment}} table created";
} else $data[] = "{{plans_create_payment}} table already exist";

if(!Yii::app()->db->schema->getTable("{{plans_webhooks}}")){    
    $table->createTable(
        "{{plans_webhooks}}",
        array(
            'event_id'=>'pk', 
            'id'=>"varchar(255) NOT NULL", 
            'event_type'=>"varchar(255) NOT NULL DEFAULT ''", 
            'processed_at'=>"timestamp NULL DEFAULT NULL",            
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');

    $data[] = "{{plans_webhooks}} table created";
} else $data[] = "{{plans_webhooks}} table already exist";

if(!Yii::app()->db->schema->getTable("{{plans_customer}}")){    
    $table->createTable(
        "{{plans_customer}}",
        array(
            'id'=>'pk', 
            'payment_code'=>"varchar(50) NOT NULL DEFAULT ''", 
            'subscriber_id'=>"int(14) NOT NULL DEFAULT '0'",             
            'subscriber_type'=>"varchar(50) NOT NULL DEFAULT ''", 
            'customer_id'=>"varchar(100) NOT NULL DEFAULT ''", 
            'livemode'=>"tinyint(1) NOT NULL DEFAULT '0'", 
            'created_at'=>"datetime DEFAULT NULL", 
            'updated_at'=>"datetime DEFAULT NULL",             
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');

    $data[] = "{{plans_customer}} table created";
} else $data[] = "{{plans_customer}} table already exist";


if(!Yii::app()->db->schema->getTable("{{merchant_location}}")){    
    $table->createTable(
        "{{merchant_location}}",
        array(
            'id'=>'pk', 
            'merchant_id'=>"int(14) DEFAULT '0'",   
            'country_id'=>"int(14) NOT NULL DEFAULT '0'",
            'state_id'=>"int(14) NOT NULL DEFAULT '0'",
            'city_id'=>"int(14) NOT NULL DEFAULT '0'",
            'area_id'=>"int(14) DEFAULT '0'",
            'created_at'=>"timestamp NULL DEFAULT NULL",
            'updated_at'=>"timestamp NULL DEFAULT NULL",            
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');
    $table->create_Index("{{merchant_location}}",array(
        'merchant_id','country_id','state_id','city_id','area_id'
    )); 	    
    $data[] = "{{merchant_location}} table created";
} else $data[] = "{{merchant_location}} table already exist";


if(!Yii::app()->db->schema->getTable("{{location_time_estimate}}")){    
    $table->createTable(
        "{{location_time_estimate}}",
        array(
            'id'=>'pk', 
            'merchant_id'=>"int(14) NOT NULL DEFAULT '0'",
            'service_type'=>"varchar(100) NOT NULL DEFAULT ''",
            'country_id'=>"int(14) NOT NULL DEFAULT '0'",
            'state_id'=>"int(14) NOT NULL DEFAULT '0'",
            'city_id'=>"int(14) NOT NULL DEFAULT '0'",
            'area_id'=>"int(14) NOT NULL DEFAULT '0'",
            'estimated_time_min'=>"int(10) NOT NULL DEFAULT '0'",
            'estimated_time_max'=>"int(10) NOT NULL DEFAULT '0'",
            'created_at'=>"timestamp NULL DEFAULT NULL",
            'updated_at'=>"timestamp NULL DEFAULT NULL",
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');
    $table->create_Index("{{location_time_estimate}}",array(
        'merchant_id','service_type','country_id','city_id','state_id','area_id'
    )); 	    
    $data[] = "{{location_time_estimate}} table created";
} else $data[] = "{{location_time_estimate}} table already exist";

if(Yii::app()->db->schema->getTable("{{merchant}}")){
    $data[] = $table->add_Column("{{merchant}}",array(
        'orders_added'=>"int(14) NOT NULL DEFAULT '0' AFTER `pause_ordering`",
        'order_limit'=>"int(14) NOT NULL DEFAULT '0' AFTER `pause_ordering`",
        'items_added'=>"int(14) NOT NULL DEFAULT '0' AFTER `pause_ordering`",
        'item_limit'=>"int(14) NOT NULL DEFAULT '0' AFTER `pause_ordering`",
        'package_payment_code'=>"varchar(100) NOT NULL DEFAULT '' AFTER `package_id`",
        'charge_type'=>"varchar(50) NOT NULL DEFAULT 'fixed' AFTER `last_login`",
        'free_delivery_on_first_order'=>"int(1) NOT NULL DEFAULT '0' AFTER `last_login`",
        'self_delivery'=>"int(1) DEFAULT '0' AFTER `last_login`",
    ));    
}

if(Yii::app()->db->schema->getTable("{{client_address}}")){
    $data[] = $table->add_Column("{{client_address}}",array(
        'custom_field2'=>"text  AFTER `address_format_use`",
        'custom_field1'=>"varchar(255) NOT NULL DEFAULT '' AFTER `address_format_use` ",        
        'address_type'=>"varchar(50) NOT NULL DEFAULT 'map-based' AFTER `client_id`",
    ));    
    $table->create_Index("{{client_address}}",array(
        'address_type'
    )); 	    
}

if(Yii::app()->db->schema->getTable("{{plans}}")){
    $data[] = $table->add_Column("{{plans}}",array(
        'pos'=>"tinyint(1) NOT NULL DEFAULT '0'  AFTER `status`",        
        'self_delivery'=>"tinyint(1) NOT NULL DEFAULT '0'  AFTER `status`",        
        'chat'=>"tinyint(1) NOT NULL DEFAULT '0'  AFTER `status`",        
        'loyalty_program'=>"tinyint(1) NOT NULL DEFAULT '0'  AFTER `status`",        
        'table_reservation'=>"tinyint(1) NOT NULL DEFAULT '0'  AFTER `status`",        
        'inventory_management'=>"tinyint(1) NOT NULL DEFAULT '0'  AFTER `status`",        
        'marketing_tools'=>"tinyint(1) NOT NULL DEFAULT '0'  AFTER `status`",        
        'mobile_app'=>"tinyint(1) NOT NULL DEFAULT '0'  AFTER `status`",        
        'payment_processing'=>"tinyint(1) NOT NULL DEFAULT '0'  AFTER `status`",        
        'customer_feedback'=>"tinyint(1) NOT NULL DEFAULT '0'  AFTER `status`",        
        'coupon_creation'=>"tinyint(1) NOT NULL DEFAULT '0'  AFTER `status`",        
    ));        
}

if(Yii::app()->db->schema->getTable("{{cart}}")){
    $data[] = $table->add_Column("{{cart}}",array(
        'subtotal'=>"decimal(10,4) NOT NULL DEFAULT '0.0000'",        
        'currency_code'=>"varchar(5) NOT NULL DEFAULT ''",        
    ));    
}

if(Yii::app()->db->schema->getTable("{{location_rate}}")){
    $data[] = $table->add_Column("{{location_rate}}",array(
        'maximum_amount'=>"float(14,2) NOT NULL DEFAULT '0.00' AFTER `minimum_order` ",                
    ));    
    $table->create_Index("{{location_rate}}",array(
        'merchant_id','country_id','state_id','area_id','city_id','fee'
    )); 	    
}

if(Yii::app()->db->schema->getTable("{{banner}}")){
    $data[] = $table->add_Column("{{banner}}",array(
        'meta_slug'=>"varchar(200) DEFAULT '' AFTER `meta_value4` ",                
    ));        
}

if(Yii::app()->db->schema->getTable("{{shipping_rate}}")){
    $data[] = $table->add_Column("{{shipping_rate}}",array(
        'fixed_free_delivery_threshold'=>"float(10,2) NOT NULL DEFAULT '0.00' AFTER `estimation` ",                
        'cap_delivery_charge'=>"float(10,2) NOT NULL DEFAULT '0.00' AFTER `estimation` ",                
        'time_per_additional'=>"float(10,2) NOT NULL DEFAULT '0.00' AFTER `estimation` ",                
        'delivery_radius'=>"float(10,2) NOT NULL DEFAULT '0.00' AFTER `estimation` ",                
    ));    
    $table->create_Index("{{shipping_rate}}",array(
        'merchant_id','service_code','charge_type','shipping_type','distance_from','distance_to','shipping_units','distance_price',
        'fixed_free_delivery_threshold','delivery_radius','cap_delivery_charge','time_per_additional'
    )); 	        
}

if(Yii::app()->db->schema->getTable("{{admin_meta}}")){
    $data[] = $table->add_Column("{{admin_meta}}",array(
        'meta_value2'=>"varchar(255) NOT NULL DEFAULT '' AFTER `meta_value1` ",                
    ));        
}

if(!Yii::app()->db->schema->getTable("{{job_queue}}")){    
    $table->createTable(
        "{{job_queue}}",
        array(
            'id'=>'pk', 
            'job_name'=>"varchar(255) NOT NULL", 
            'job_data'=>"text NOT NULL", 
            'status'=>"varchar(50) NOT NULL DEFAULT 'pending'", 
            'created_at'=>"timestamp NULL DEFAULT NULL", 
            'updated_at'=>"timestamp NULL DEFAULT NULL",             
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');
    $table->create_Index("{{job_queue}}",array(
        'job_name','status','created_at'
    )); 	    
    $data[] = "{{job_queue}} table created";
} else $data[] = "{{job_queue}} table already exist";

$model_view = new MG_view_location_rates;
$model_view->up();

$model_view = new MG_view_merchant_location;
$model_view->up();

$model_view = new MG_view_location_time_estimate;
$model_view->up();

$model_view = new MG_view_client_address_locations;
$model_view->up();

$model_view = new MG_view_cuisine_merchant;
$model_view->up();

if(Yii::app()->db->schema->getTable("{{payment_gateway}}")){
    Yii::app()->db->createCommand("
    ALTER TABLE {{payment_gateway}} CHANGE `attr4` `attr4` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
    ")->query();

    Yii::app()->db->createCommand("
    UPDATE st_payment_gateway SET attr4=".q('{"Webhooks Plan":"{site_url}/stripe/apiv2/webhooksplans","Events":"checkout.session.completed, invoice.paid, invoice.payment_failed, customer.subscription.deleted, subscription_schedule.canceled"}')."
    WHERE payment_code='stripe';

    UPDATE st_payment_gateway SET attr4=".q('{"Webhooks":"{site_url}/paypal/api/webhooks","Event":"PAYMENT.CAPTURE.COMPLETED","Webhooks Plan":"{site_url}/paypal/apiv2/webhooksplans","Events":"BILLING.SUBSCRIPTION.ACTIVATED, BILLING.SUBSCRIPTION.CREATED, BILLING.SUBSCRIPTION.CANCELLED, BILLING.SUBSCRIPTION.PAYMENT.FAILED"}')."
    WHERE payment_code='paypal';   

    UPDATE st_payment_gateway SET attr4=".q('{"Webhooks":"{site_url}/razorpay/api/webhooks","Event":"payment.captured","Webhooks Plan":"{site_url}/razorpay/apiv2/webhooksplans","Events":"subscription.charged, subscription.updated, subscription.cancelled, subscription.pending, subscription.halted"}')."
    WHERE payment_code='razorpay';   

    ")->query();
}

// UPDATE ALL MERCHANT SELF DELIVERY SETTINGS
Yii::app()->db->createCommand("
UPDATE st_merchant a
JOIN st_option b
  ON a.merchant_id = b.merchant_id
SET a.self_delivery = 1
WHERE b.option_name = 'self_delivery'
  AND b.option_value = 1;
")->query();

$model = AR_templates::model()->find("template_name=:template_name",[':template_name'=>'Complete registration']);
if(!$model){
    $model = new AR_templates();
    $model->template_name = 'Complete registration';
    $model->enabled_email = 1;
    $model->enabled_sms = 0;
    $model->enabled_push = 1;
    if($model->save()){
        $template_id = $model->template_id;
        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "email";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "Complete your signup for {{site.site_name}}";    
        $model2->content = '<p>Dear {{first_name}},</p><p>Thank you for signing up for {{site.site_name}}!</p><p>To ensure the security of your account and complete the signup process, 
please verify your email address by clicking the link below:</p>
<p><a href="{{verification_link}}" target="_blank">
Click here
</a></p>
<p>If the above link doesn\'t work, copy and paste the following URL into your browser\'s address bar:</p><p>{{verification_link}}</p><p>Thank you for choosing {{site.site_name}}. If you have any questions or need assistance, feel free to contact our support team at {{site.email}} / {{site.contact}} .</p><p>Best regards,</p><p>{{site_name}} Team</p><p></p><p></p><p></p><p></p><p></p><p></p><p></p>';
        $model2->save();

        $model_option = new AR_option();
        $model_option->option_name = "signup_complete_registration_tpl";
        $model_option->option_value = $template_id;
        $model_option->save();

    }
}

$model = AR_templates::model()->find("template_name=:template_name",[':template_name'=>'New Bank Deposit Uploaded for Subscription Payment']);
if(!$model){
    $model = new AR_templates();
    $model->template_name = 'New Bank Deposit Uploaded for Subscription Payment';
    $model->enabled_email = 1;
    $model->enabled_sms = 0;
    $model->enabled_push = 1;
    if($model->save()){
        $template_id = $model->template_id;
        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "email";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "New Bank Deposit Uploaded for Subscription Payment";    
        $model2->content = 'Dear Admin,<br><br>We have received a new bank deposit upload for a subscription payment.<br><br>Details:<br>- **Merchant Name:** {{restaurant_name}}<br>- **Merchant Email:** {{restaurant_email}}<br>- **Subscription Plan:** {{plan_name}}<br>- **Amount:** {{amount}}<br>- **Reference Number:** {{reference_number}}<br>- **Upload Date:** {{uploaded_date}}<br><br>Please verify the payment and update the subscription status accordingly.<br><br>Best regards,<br>{{site_title}}<br><br>';
        $model2->save();

        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "push";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "New bank deposit uploaded for subscription payment by {{restaurant_name}} with the amount of {{amount}}.";    
        $model2->content = 'New bank deposit uploaded for subscription payment by {{restaurant_name}} with the amount of {{amount}}.';
        $model2->save();

        $model_option = new AR_option();
        $model_option->option_name = "merchant_bank_deposit_subscriptions";
        $model_option->option_value = $template_id;
        $model_option->save();
    }
}

$model = AR_templates::model()->find("template_name=:template_name",[':template_name'=>'Bank Subscription Approved']);
if(!$model){
    $model = new AR_templates();
    $model->template_name = 'Bank Subscription Approved';
    $model->enabled_email = 1;
    $model->enabled_sms = 0;
    $model->enabled_push = 1;
    if($model->save()){
        $template_id = $model->template_id;
        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "email";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "Your Subscription is Approved and Account is Now Active";    
        $model2->content = '<p>Dear {{restaurant_name}},<br><br>We are pleased to inform you that your bank deposit has been verified, and your subscription is now approved. Your account is active and you can now proceed to the merchant panel to start using our services.<br><br>Subscription Details:<br>- **Subscription Plan:** {{plan_name}}<br>- **Start Date:** {{start_date}}<br>- **End Date:** {{end_date}}<br><br>You can log in to your merchant panel here: {{merchant_panel_url}}<br><br>If you have any questions or need assistance, please do not hesitate to contact our support team.<br><br>Best regards,<br>{{site_title}}<br><br></p>';
        $model2->save();

        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "push";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "Subscription Approved";    
        $model2->content = 'Your subscription is approved and your account is active. You can now proceed to the merchant panel.';
        $model2->save();

        $model_option = new AR_option();
        $model_option->option_name = "merchant_subscription_approved";
        $model_option->option_value = $template_id;
        $model_option->save();
    }
}

$model = AR_templates::model()->find("template_name=:template_name",[':template_name'=>'Merchant Registration is Approved']);
if(!$model){
    $model = new AR_templates();
    $model->template_name = 'Merchant Registration is Approved';
    $model->enabled_email = 1;
    $model->enabled_sms = 0;
    $model->enabled_push = 0;
    if($model->save()){
        $template_id = $model->template_id;
        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "email";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "Your Merchant Registration is Approved";    
        $model2->content = '<p>Dear {{restaurant_name}},<br><br>We are delighted to inform you that your merchant registration has been approved. You can now access your merchant panel and start managing your restaurant\'s orders, menu, and other settings.<br><br><br>You can log in to your merchant panel using the following link:<br>{{merchant_panel_url}}<br><br>If you have any questions or need assistance, please feel free to contact our support team.<br><br>Thank you for choosing our services. We look forward to a successful partnership!<br><br>Best regards,<br>{{site_title}}<br><br></p>';
        $model2->save();

        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "push";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "Your registration is approved! You can now access the merchant panel to start managing your restaurant.";    
        $model2->content = 'Your registration is approved! You can now access the merchant panel to start managing your restaurant.';
        $model2->save();

        $model_option = new AR_option();
        $model_option->option_name = "merchant_registration_approved";
        $model_option->option_value = $template_id;
        $model_option->save();
    }
}

$model = AR_templates::model()->find("template_name=:template_name",[':template_name'=>'Subscription Payment Received and Processed']);
if(!$model){
    $model = new AR_templates();
    $model->template_name = 'Subscription Payment Received and Processed';
    $model->enabled_email = 1;
    $model->enabled_sms = 0;
    $model->enabled_push = 1;
    if($model->save()){
        $template_id = $model->template_id;
        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "email";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "Subscription Payment Received and Processed";    
        $model2->content = '<p>Dear {{restaurant_name}},<br><br>We have successfully received and processed your subscription payment. Thank you for your prompt payment!<br><br>**Subscription Details:**<br>- **Subscription Plan:** {{plan_name}}<br>- **Amount Paid:** {{amount}}<br>- **Payment Date:** {{payment_date}}<br>- **Subscription Period:** {{start_date}} to {{end_date}}<br><br>Your account is now active, and you can continue to use all the features associated with your subscription plan.<br><br>You can log in to your merchant panel here: {{merchant_panel_url}}<br><br>If you have any questions or need further assistance, please do not hesitate to contact our support team.<br><br>Thank you for choosing our services!<br><br>Best regards,<br>{{site_title}}<br><br></p>';
        $model2->save();

        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "push";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "Payment Processed";    
        $model2->content = 'Your subscription payment has been successfully processed. Your account is now active. Thank you for choosing {{site_title}}!';
        $model2->save();

        $model_option = new AR_option();
        $model_option->option_name = "merchant_subscription_payment_process";
        $model_option->option_value = $template_id;
        $model_option->save();
    }
}

$model = AR_templates::model()->find("template_name=:template_name",[':template_name'=>'Subscription Payment Failed']);
if(!$model){
    $model = new AR_templates();
    $model->template_name = 'Subscription Payment Failed';
    $model->enabled_email = 1;
    $model->enabled_sms = 0;
    $model->enabled_push = 1;
    if($model->save()){
        $template_id = $model->template_id;
        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "email";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "Important: Subscription Payment Failed";    
        $model2->content = '<p>Dear {{merchant_name}},<br><br>We regret to inform you that your recent attempt to process the payment for your subscription has failed.<br><br>**Subscription Details:**<br>- **Subscription Plan:** {{plan_name}}<br>- **Amount Due:** {{amount}}<br>- **Payment Date:** {{attempted_payment_date}}<br><br>Unfortunately, we were unable to process.<br><br>To ensure uninterrupted access to your subscription, please update your payment information and try again. You can update your payment details by logging in to your merchant panel using the following link:<br><br>{{merchant_panel_url}}<br><br>If the issue persists or if you need any assistance, please do not hesitate to contact our support team. We are here to help you resolve the issue as quickly as possible.<br><br>Thank you for your attention to this matter.<br><br>Best regards, &nbsp;<br>{{site_title}}<br><br></p>';
        $model2->save();

        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "push";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "Subscription Payment Failed";    
        $model2->content = 'Payment failed for your subscription plan. Please update your payment details in your merchant panel to avoid service disruption. Need help? Contact our support team.';
        $model2->save();

        $model_option = new AR_option();
        $model_option->option_name = "merchant_subscription_payment_failed";
        $model_option->option_value = $template_id;
        $model_option->save();
    }
}

$model = AR_templates::model()->find("template_name=:template_name",[':template_name'=>'Subscription Canceled']);
if(!$model){
    $model = new AR_templates();
    $model->template_name = 'Subscription Canceled';
    $model->enabled_email = 1;
    $model->enabled_sms = 0;
    $model->enabled_push = 0;
    if($model->save()){
        $template_id = $model->template_id;
        $model2 = new AR_templates_translation();
        $model2->template_id = $template_id;
        $model2->template_type = "email";
        $model2->language = KMRS_DEFAULT_LANGUAGE;
        $model2->title = "Your Subscription Has Been Canceled";    
        $model2->content = 'Dear {{restaurant_name}},<br><br>We’re writing to inform you that your subscription to the {{plan_name}} plan has been canceled effective {{cancellation_date}}.<br><br>**What This Means:**<br>- You will no longer have access to the features included in the {{plan_name}} plan.<br>- Your account will be placed in an inactive status, and certain services may be limited or unavailable.<br><br>**Next Steps:**<br>If this cancellation was not intended or if you wish to reactivate your subscription, please contact our support team or log in to your merchant panel to choose a new subscription plan.<br><br>We value your partnership with us and hope to continue supporting your business in the future. If there’s anything we can do to assist you or if you have any questions, please don’t hesitate to reach out to us.<br><br>Thank you for being a valued customer.<br><br>Best regards, &nbsp;<br>{{site_title}}<br>{{contact_number}}';
        $model2->save();
        
        $model_option = new AR_option();
        $model_option->option_name = "merchant_subscription_cancelled";
        $model_option->option_value = $template_id;
        $model_option->save();
    }
}


$model_bank = AR_payment_gateway::model()->find("payment_code=:payment_code AND attr_json IS NULL",[
    ':payment_code'=>'bank'    
]);
if($model_bank){
    $model_bank->attr_json = '{"attr9":{"label":"Bank Instructions","field_type":"textarea"},"attr1":{"label":"Subject","field_type":"text"}}';
    $model_bank->attr_json1 = '{"full_name":"Full Name","billing_address1":"Billing Address Line 1","billing_address2":"Billing Address Line 2","city":"City","state":"State","post_code":"Postcode","country":"Country","account_name":"Bank Account Holder\'s Name","account_number":"Bank Account Number\/IBAN","swift_code":"SWIFT Code","bank_name":"Bank Name in Full","bank_branch":"Bank Branch City"}';
    $model_bank->attr1 = 'Bank instructions deposit';
    $model_bank->attr2 = 'Bank instructions deposit';
    $model_bank->attr9 = '<p>Hi {{first_name}},<br><br>Deposit Instructions<br><br>Please deposit {{amount}} to :<br><br>Bank : Your bank name<br>Account Number : Your bank account number<br>Account Name : Your bank account name<br><br>When deposit is completed<br>fill in your bank deposit information {{upload_deposit_url}}<br><br><br>Kind Regards<br></p>';
    $model_bank->save();
}


// END 1.1.9 UPDATE

// START 2.0.1
if(Yii::app()->db->schema->getTable("{{banner}}")){
    $data[] = $table->add_Column("{{banner}}",array(
        'latitude'=>"float DEFAULT NULL AFTER `photo` ",
        'longitude'=>"float DEFAULT NULL AFTER `photo` ",
        'radius'=>"float NOT NULL DEFAULT '0' AFTER `photo` ",
        'radius_unit'=>"varchar(5) NOT NULL DEFAULT 'mi' AFTER `photo` ",
        'country_id'=>"int(10) NOT NULL DEFAULT '0' AFTER `photo` ",
        'state_id'=>"int(10) NOT NULL DEFAULT '0' AFTER `photo` ",
        'city_id'=>"int(10) NOT NULL DEFAULT '0' AFTER `photo` ",
        'area_id'=>"int(10) NOT NULL DEFAULT '0' AFTER `photo` ",
    ));        
    $table->create_Index("{{banner}}",array(
        'latitude','longitude','radius','radius_unit','radius_unit','country_id','state_id','city_id',
        'area_id'
    )); 	        
}

if(Yii::app()->db->schema->getTable("{{item}}")){
    $data[] = $table->add_Column("{{item}}",array(
        'preparation_time'=>"int(10) NOT NULL DEFAULT '0' AFTER `visible`",
        'extra_preparation_time'=>"int(10) NOT NULL DEFAULT '0' AFTER `visible`",
        'unavailable_until'=>"datetime DEFAULT NULL AFTER `visible`",        
        'featured_priority'=>"int(10) DEFAULT NULL AFTER `sequence`",
    ));        
}

if(!Yii::app()->db->schema->getTable("{{kitchen_workload_settings}}")){    
    $table->createTable(
        "{{kitchen_workload_settings}}",
        array(
            'id'=>'pk', 
            'merchant_id'=>"int(10) DEFAULT '0'", 
            'low_workload_max_orders'=>"int(10) NOT NULL DEFAULT '0'", 
            'low_workload_extra_time'=>"int(10) NOT NULL DEFAULT '0'", 
            'medium_workload_min_orders'=>"int(10) NOT NULL DEFAULT '0'", 
            'medium_workload_max_orders'=>"int(10) NOT NULL DEFAULT '0'", 
            'medium_workload_extra_time'=>"int(10) NOT NULL DEFAULT '0'", 
            'high_workload_min_orders'=>"int(10) NOT NULL DEFAULT '0'", 
            'high_workload_extra_time'=>"int(10) NOT NULL DEFAULT '0'", 
            'created_at'=>"timestamp NULL DEFAULT NULL", 
            'updated_at'=>"timestamp NULL DEFAULT NULL", 
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');
    $table->create_Index("{{kitchen_workload_settings}}",array(
        'merchant_id'
    )); 	    
    $data[] = "{{kitchen_workload_settings}} table created";
} else $data[] = "{{kitchen_workload_settings}} table already exist";

if(Yii::app()->db->schema->getTable("{{ordernew}}")){
    $data[] = $table->add_Column("{{ordernew}}",array(       
        'promo_code'=>"varchar(100) NOT NULL DEFAULT '' AFTER `wallet_amount`",         
        'promo_cap'=>"decimal(10,2) NOT NULL DEFAULT '0.00' AFTER `promo_total`",        
        'promo_owner'=>"varchar(50) NOT NULL DEFAULT 'admin' AFTER `promo_total`",
        'offer_cap'=>"decimal(10,2) NOT NULL DEFAULT '0.00' AFTER `offer_total`",
        'delivery_date_time'=>"datetime DEFAULT NULL AFTER `delivery_time`",
        'order_accepted_at'=>"timestamp NULL DEFAULT NULL AFTER `delivery_time_end`",
        'preparation_time_estimation'=>"int(10) DEFAULT NULL AFTER `delivery_time_end`",
        'pickup_time'=>"timestamp NULL DEFAULT NULL AFTER `delivery_time_end`",
        'delivery_time_estimation'=>"int(10) DEFAULT NULL AFTER `delivery_time_end`",
        'assigned_at'=>"timestamp NULL DEFAULT NULL AFTER `vehicle_id`",
        'assigned_expired_at'=>"datetime DEFAULT NULL AFTER `vehicle_id`",
        'late_notification_sent'=>"tinyint(1) NOT NULL DEFAULT '0' AFTER `request_from`",
        'preparation_late_sent'=>"tinyint(1) NOT NULL DEFAULT '0' AFTER `request_from`",
        'delivering_late_sent'=>"tinyint(1) NOT NULL DEFAULT '0' AFTER `request_from`",
        'retry_attempts'=>"int(1) NOT NULL DEFAULT '0' AFTER `request_from`",
        'last_retry'=>"timestamp NULL DEFAULT NULL AFTER `request_from`",
        'auto_assign_status'=>"varchar(50) NOT NULL DEFAULT 'pending' AFTER `request_from`",
    ));        
}

if(!Yii::app()->db->schema->getTable("{{driver_reviews}}")){    
    $table->createTable(
        "{{driver_reviews}}",
        array(
            'review_id'=>'pk', 
            'order_id'=>"int(10) DEFAULT NULL",
            'customer_id'=>"int(10) DEFAULT NULL",
            'driver_id'=>"int(10) DEFAULT NULL",
            'rating'=>"tinyint(5) DEFAULT '0'",
            'review_text'=>"text",
            'as_anonymous'=>"tinyint(1) NOT NULL DEFAULT '0'",
            'status'=>"varchar(100) NOT NULL DEFAULT 'publish'",
            'created_at'=>"datetime DEFAULT NULL",
            'updated_at'=>"datetime DEFAULT NULL",
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');
    $table->create_Index("{{driver_reviews}}",array(
        'order_id','customer_id','driver_id','rating'
    )); 	    
    $data[] = "{{driver_reviews}} table created";
} else $data[] = "{{driver_reviews}} table already exist";

if(Yii::app()->db->schema->getTable("{{driver_reviews}}")){
    $data[] = $table->add_Column("{{driver_reviews}}",array(
        'status'=>"varchar(100) NOT NULL DEFAULT 'publish'",        
    ));        
}

if(Yii::app()->db->schema->getTable("{{offers}}")){
    $data[] = $table->add_Column("{{offers}}",array(
        'offer_name'=>"varchar(100) NOT NULL DEFAULT '' AFTER `offers_id`",
        'visible'=>"smallint(1) NOT NULL DEFAULT '1' AFTER `status`",  
        'min_order'=>"decimal(10,2) NOT NULL DEFAULT '0.00' AFTER `offer_percentage`",
        'max_discount_cap'=>"decimal(10,2) NOT NULL DEFAULT '0.00' AFTER `offer_percentage`",
    ));        
}

if(!Yii::app()->db->schema->getTable("{{custom_fields}}")){ 
    $table->createTable(
        "{{custom_fields}}",
        array(
            'field_id'=>'pk', 
            'field_name'=>"varchar(100) NOT NULL",
            'field_label'=>"varchar(255) NOT NULL",
            'field_type'=>"enum('text','number','date','checkbox','select','textarea') NOT NULL",
            'options'=>"text",
            'is_required'=>"tinyint(1) DEFAULT '0'",
            'entity'=>"varchar(100) NOT NULL DEFAULT 'client'",
            'sequence'=>"int(11) NOT NULL DEFAULT '0'",
            'visible'=>"smallint(1) NOT NULL DEFAULT '1'",
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');
    $table->create_Index("{{custom_fields}}",array(
        'field_name','visible'
    )); 	    
    $data[] = "{{custom_fields}} table created";
}


if(!Yii::app()->db->schema->getTable("{{user_custom_field_values}}")){ 
    $table->createTable(
        "{{user_custom_field_values}}",
        array(
            'value_id'=>'pk',             
            'user_id'=>"int(11) NOT NULL",
            'field_id'=>"int(11) NOT NULL",
            'value'=>"text",
            'entity'=>"varchar(50) NOT NULL DEFAULT 'client'",
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');
    $table->create_Index("{{user_custom_field_values}}",array(
        'user_id','field_id'
    )); 	    
    $data[] = "{{user_custom_field_values}} table created";
}

if(Yii::app()->db->schema->getTable("{{wallet_transactions}}")){
    $table->create_Index("{{wallet_transactions}}",array(
        'transaction_date'
    )); 	    
}

if(!Yii::app()->db->schema->getTable("{{promos}}")){ 
    $table->createTable(
        "{{promos}}",
        array(
            'promo_id'=>'pk',                         
            'id'=>"int(11) DEFAULT NULL",
            'merchant_id'=>"int(11) DEFAULT NULL",
            'valid_from'=>"date DEFAULT NULL",
            'valid_to'=>"date DEFAULT NULL",
            'offer_type'=>"varchar(100) NOT NULL DEFAULT ''",
            'discount_name'=>"varchar(255) NOT NULL DEFAULT ''",
            'offer_amount'=>"decimal(10,2) DEFAULT NULL",
            'discount_type'=>"varchar(100) NOT NULL DEFAULT ''",
            'min_order'=>"decimal(10,2) DEFAULT NULL",
            'max_order'=>"decimal(10,2) NOT NULL DEFAULT '0.00'",
            'max_discount_cap'=>"decimal(10,2) NOT NULL DEFAULT '0.00'",
            'applicable_to'=>"varchar(255) DEFAULT NULL",
            'status'=>"varchar(100) NOT NULL DEFAULT ''",
            'visible'=>"smallint(1) NOT NULL DEFAULT '1'",
            'monday'=>"smallint(1) NOT NULL DEFAULT '1'",
            'tuesday'=>"smallint(1) NOT NULL DEFAULT '1'",            
            'wednesday'=>"smallint(1) NOT NULL DEFAULT '1'",
            'thursday'=>"smallint(1) NOT NULL DEFAULT '1'",
            'friday'=>"smallint(1) NOT NULL DEFAULT '1'",
            'saturday'=>"smallint(1) NOT NULL DEFAULT '1'",
            'sunday'=>"smallint(1) NOT NULL DEFAULT '1'",
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');
    $table->create_Index("{{promos}}",array(
        'id','merchant_id','valid_from','valid_to','offer_type','status','visible'
    )); 	    
    $data[] = "{{promos}} table created";
}

if(Yii::app()->db->schema->getTable("{{promos}}")){
    $data[] = $table->add_Column("{{promos}}",array(
        'max_order'=>"decimal(10,2) NOT NULL DEFAULT '0.00'", 
        'max_discount_cap'=>"decimal(10,2) NOT NULL DEFAULT '0.00'"
    ));        
}

if(!Yii::app()->db->schema->getTable("{{customer_points_ranks}}")){ 
    $table->createTable(
        "{{customer_points_ranks}}",
        array(
            'account_id'=>'int(11) DEFAULT NULL',             
            'first_name'=>"varchar(255) NOT NULL DEFAULT ''",
            'last_name'=>"varchar(255) NOT NULL DEFAULT ''",        
            'total_earning'=>"decimal(10,2) DEFAULT NULL",
            'rank'=>"int(10) DEFAULT NULL",
            'previous_points'=>"decimal(10,2) DEFAULT NULL",
            'total_players'=>"int(10) DEFAULT NULL",
            'percentage_better_than'=>"int(10) DEFAULT NULL",    
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');    

    $table->create_Index("{{customer_points_ranks}}",array(
        'account_id'
    )); 	    
    $data[] = "{{customer_points_ranks}} table created";
}

if(!Yii::app()->db->schema->getTable("{{payment_reference}}")){
    $table->createTable(
        "{{payment_reference}}",
        array(
            'id'=>'pk',                      
            'payment_type'=>"varchar(50) NOT NULL DEFAULT 'order'", 
            'reference_id'=>"varchar(100) DEFAULT NULL", 
            'payment_reference_id'=>"varchar(100) DEFAULT NULL", 
            'meta_value'=>"text", 
            'created_at'=>"timestamp NULL DEFAULT NULL", 
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');    
    $table->create_Index("{{payment_reference}}",array(
        'payment_type','reference_id','payment_reference_id'
    )); 	    
    $data[] = "{{payment_reference}} table created";
}


if(!Yii::app()->db->schema->getTable("{{holidays}}")){
    $table->createTable(
        "{{holidays}}",
        array(
            'id'=>'pk',                      
            'merchant_id' => "int(11) NOT NULL",
            'holiday_date' => "date NOT NULL",
            'holiday_name' => "varchar(255) NOT NULL",
            'is_closed' => "tinyint(1) DEFAULT '1'",
            'open_time' => "time DEFAULT NULL",
            'close_time' => "time DEFAULT NULL",
            'reason' => "text",
            'created_at' => "timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');    
    $table->create_Index("{{holidays}}",array(
        'merchant_id','holiday_date'
    )); 	    
    $data[] = "{{holidays}} table created";

    Yii::app()->db->createCommand("
      ALTER TABLE `st_holidays`
      ADD CONSTRAINT `st_holidays_ibfk_1` FOREIGN KEY (`merchant_id`) REFERENCES `st_merchant` (`merchant_id`) ON DELETE CASCADE;
    ")->query();
}

if(!Yii::app()->db->schema->getTable("{{driver_attempts}}")){
    $table->createTable(
        "{{driver_attempts}}",
        array(
            'attempt_id'=>'pk',
            'order_id'=>"int(11) NOT NULL",
            'driver_id'=>"int(11) NOT NULL",
            'attempt_status'=>"varchar(100) DEFAULT 'pending'",
            'attempt_time'=>"datetime DEFAULT CURRENT_TIMESTAMP",
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');    
    $table->create_Index("{{driver_attempts}}",array(
        'order_id','driver_id'
    )); 	    
    $data[] = "{{driver_attempts}} table created";
}

if(!Yii::app()->db->schema->getTable("{{suggested_items}}")){
    $table->createTable(
        "{{suggested_items}}",
        array(
            'id'=>'pk',                                  
            'merchant_id'=>"int(11) DEFAULT NULL",
            'item_id'=>"int(10) DEFAULT NULL",
            'status'=>"varchar(50) DEFAULT 'pending'",
            'reason'=>"text",
            'created_at'=>"datetime DEFAULT NULL",
            'updated_at'=>"datetime DEFAULT NULL",
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');    
    $table->create_Index("{{suggested_items}}",array(
        'merchant_id','item_id','status'
    )); 	    
    $data[] = "{{suggested_items}} table created";
}


try {
    $db = Yii::app()->db;
    $db->createCommand("
    ALTER TABLE `st_payment_method_meta`
    ADD CONSTRAINT `fk_payment_method` FOREIGN KEY (`payment_method_id`) REFERENCES `st_client_payment_method` (`payment_method_id`) ON DELETE CASCADE
    ")->execute();    
} catch (Exception $e) {        
}


$model_view = new MG_view_offers;
$model_view->up();

$model_view = new MG_view_customer_points;
$model_view->up();

// END 2.0.0 UPDATE

// START 2.0.3 UPDATE
if(Yii::app()->db->schema->getTable("{{category}}")){
    $data[] = $table->add_Column("{{category}}",array(
        'available'=>"tinyint(1) NOT NULL DEFAULT '1'",        
    ));        
    $table->create_Index("{{category}}",array(
        'available'
    )); 	    
}

if(Yii::app()->db->schema->getTable("{{subcategory}}")){
    $data[] = $table->add_Column("{{subcategory}}",array(
        'available'=>"tinyint(1) NOT NULL DEFAULT '1'",        
    ));        
    $table->create_Index("{{subcategory}}",array(
        'available'
    )); 	    
}

if(Yii::app()->db->schema->getTable("{{subcategory_item}}")){
    $data[] = $table->add_Column("{{subcategory_item}}",array(
        'available'=>"tinyint(1) NOT NULL DEFAULT '1'",        
    ));        
    $table->create_Index("{{subcategory_item}}",array(
        'available'
    )); 	    
}

if(Yii::app()->db->schema->getTable("{{wallet_transactions}}")){
    $data[] = $table->add_Column("{{wallet_transactions}}",array(
        'reference_id2'=>"varchar(100) NOT NULL DEFAULT ''",        
    ));            
}
// END 2.0.3 UPDATE

// START 2.0.4
if(Yii::app()->db->schema->getTable("{{client_address}}")){
    $data[] = $table->add_Column("{{client_address}}",array(
        'city'=>"varchar(200) NOT NULL DEFAULT '' AFTER `address2` ",         
        'state'=>"varchar(200) NOT NULL DEFAULT '' AFTER `address2` ",         
        'formattedAddress'=>"TEXT NULL DEFAULT NULL AFTER `formatted_address` ", 
    ));            
}

if(Yii::app()->db->schema->getTable("{{item_relationship_size}}")){
    $data[] = $table->add_Column("{{item_relationship_size}}",array(
        'barcode'=>"varchar(50) NOT NULL DEFAULT '' AFTER `sku`",         
    ));            
}

if(Yii::app()->db->schema->getTable("{{bank_deposit}}")){
    Yii::app()->db->createCommand("
    ALTER TABLE {{bank_deposit}} CHANGE `transaction_ref_id` `transaction_ref_id` VARCHAR(100) NULL DEFAULT ''
    ")->query();
}

// END 2.0.4 UPDATE


// START 2.0.5
if(!Yii::app()->db->schema->getTable("{{item_free_promo}}")){
    $table->createTable(
        "{{item_free_promo}}",
        array(
            'promo_id'=>'pk',                                  
            'merchant_id'=>"int(11) DEFAULT '0'",
            'free_item_id'=>"int(10) DEFAULT NULL",
            'item_token'=>"varchar(50) NOT NULL DEFAULT ''",
            'item_size_id'=>"int(10) NOT NULL DEFAULT '0'",
            'cat_id'=>"int(10) NOT NULL DEFAULT '0'",
            'minimum_cart_total'=>"decimal(10,2) NOT NULL DEFAULT '0.00'",
            'max_free_quantity'=>"int(1) DEFAULT '1'",
            'auto_add'=>"tinyint(1) DEFAULT '0'",
            'status'=>"varchar(50) NOT NULL DEFAULT 'publish'",
            'created_at'=>"datetime DEFAULT NULL",
            'updated_at'=>"datetime DEFAULT NULL",            
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');    
    $table->create_Index("{{item_free_promo}}",array(
        'merchant_id','free_item_id','status','item_token','item_size_id','cat_id'
    )); 	    
    $data[] = "{{item_free_promo}} table created";
}

if(Yii::app()->db->schema->getTable("{{ordernew_item}}")){
    $data[] = $table->add_Column("{{ordernew_item}}",array(
        'is_free'=>"tinyint(1) NOT NULL DEFAULT '0'",
        'kot_status'=>"varchar(100) NOT NULL DEFAULT 'pending'",
        'voided_by'=>"varchar(100) NOT NULL DEFAULT ''",
        'voided_at'=>"datetime DEFAULT NULL",
        'void_reason'=>"text"
    ));            
    $table->create_Index("{{ordernew_item}}",array(
        'kot_status'
    )); 	    
}

if(Yii::app()->db->schema->getTable("{{item}}")){
    $data[] = $table->add_Column("{{item}}",array(
        'is_promo_free_item'=>"tinyint(1) NOT NULL DEFAULT '0'",
    ));            
    $table->create_Index("{{item}}",array(
        'is_promo_free_item'
    )); 	    
}

if(Yii::app()->db->schema->getTable("{{ordernew}}")){
    $data[] = $table->add_Column("{{ordernew}}",array(
        'flow_status'=>"varchar(30) NOT NULL DEFAULT 'draft' AFTER `delivery_status`",
    ));            
    $table->create_Index("{{ordernew}}",array(
        'flow_status'
    )); 	    
}

if(!Yii::app()->db->schema->getTable("{{kot_tickets}}")){
    $table->createTable(
        "{{kot_tickets}}",
        array(
            'kot_id'=>'pk',                                              
            'kot_number'=>"int(10) NOT NULL DEFAULT '0'",
            'order_id'=>"int(10) NOT NULL DEFAULT '0'",
            'merchant_id'=>"int(10) NOT NULL DEFAULT '0'",
            'created_at'=>"datetime NOT NULL DEFAULT CURRENT_TIMESTAMP"
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');    
    $table->create_Index("{{kot_tickets}}",array(
        'kot_number','order_id','merchant_id'
    )); 	    
    $data[] = "{{kot_tickets}} table created";
}

if(!Yii::app()->db->schema->getTable("{{kot_items}}")){
    $table->createTable(
        "{{kot_items}}",
        array(
            'id'=>'pk',                                                          
            'kot_id'=>"int(10) NOT NULL DEFAULT '0'",
            'order_item_id'=>"int(10) NOT NULL DEFAULT '0'",
            'qty'=>"int(10) NOT NULL DEFAULT '0'",
            'note'=>"text",
            'created_at'=>"datetime NOT NULL DEFAULT CURRENT_TIMESTAMP"
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');    
    $table->create_Index("{{kot_items}}",array(
        'kot_id','order_item_id'
    )); 	    
    $data[] = "{{kot_items}} table created";

    Yii::app()->db->createCommand("
     ALTER TABLE {{kot_items}}
     ADD CONSTRAINT `fk_kot_items_kot_delete` FOREIGN KEY (`kot_id`) REFERENCES {{kot_tickets}} (`kot_id`) ON DELETE CASCADE,
     ADD CONSTRAINT `fk_kot_items_kot_id` FOREIGN KEY (`kot_id`) REFERENCES {{kot_tickets}} (`kot_id`);
    ")->query();
}

if(Yii::app()->db->schema->getTable("{{cart}}")){
    $data[] = $table->add_Column("{{cart}}",array(
        'hold_order_admin'=>"tinyint(1) NOT NULL DEFAULT '0' AFTER `hold_order`",
        'held_time'=>"datetime DEFAULT NULL AFTER `hold_order_admin`"
    ));                
}

if(Yii::app()->db->schema->getTable("{{table_tables}}")){
    $data[] = $table->add_Column("{{table_tables}}",array(
        'status'=>"varchar(100) NOT NULL DEFAULT 'available'",
        'current_order_id'=>"int(10) DEFAULT NULL"
    ));          
    $table->create_Index("{{table_tables}}",array(
        'status'
    )); 	          
}

if(Yii::app()->db->schema->getTable("{{opening_hours}}")){
    $data[] = $table->add_Column("{{opening_hours}}",array(
        'time_config_type'=>"varchar(50) NOT NULL DEFAULT 'regular_hours'",
        'transaction_type'=>"varchar(50) DEFAULT NULL"
    ));                 
    $table->create_Index("{{opening_hours}}",array(
        'time_config_type','transaction_type'
    )); 	          
}

if(Yii::app()->db->schema->getTable("{{wallet_transactions_meta}}")){
    $data[] = $table->add_Column("{{wallet_transactions_meta}}",array(
        'meta_value2'=>"varchar(255) DEFAULT NULL",
        'meta_value3'=>"varchar(255) DEFAULT NULL"
    ));                
}

if(Yii::app()->db->schema->getTable("{{printer}}")){
    $data[] = $table->add_Column("{{printer}}",array(
        'printer_uuid'=>"varchar(100) DEFAULT NULL",        
    ));                
}

// END 2.0.5 UPDATE


// START 2.0.6 UPDATE

if(Yii::app()->db->schema->getTable("{{ordernew}}")){
    $data[] = $table->add_Column("{{ordernew}}",array(
        'is_commission_applied'=>"tinyint(1) NOT NULL DEFAULT '0'",
        'merchant_subtotal'=>"decimal(10,2) NOT NULL DEFAULT '0.00'",
        'merchant_total'=>"decimal(10,2) NOT NULL DEFAULT '0.00'",
    ));                
}

if(Yii::app()->db->schema->getTable("{{ordernew_item}}")){
    $data[] = $table->add_Column("{{ordernew_item}}",array(
        'merchant_price'=>"decimal(10,2) DEFAULT '0.00'",
        'commission_amount'=>"decimal(10,2) NOT NULL DEFAULT '0.00'",
        'merchant_earning'=>"decimal(10,2) NOT NULL DEFAULT '0.00'",
    ));                
}

if(Yii::app()->db->schema->getTable("{{ordernew_addons}}")){
    $data[] = $table->add_Column("{{ordernew_addons}}",array(
        'merchant_price'=>"decimal(10,2) DEFAULT '0.00'",
        'commission_amount'=>"decimal(10,2) NOT NULL DEFAULT '0.00'",
        'merchant_earning'=>"decimal(10,2) NOT NULL DEFAULT '0.00'",
    ));                
}

if(Yii::app()->db->schema->getTable("{{ordernew_transaction}}")){
    $data[] = $table->add_Column("{{ordernew_transaction}}",array(
        'merchant_amount'=>"decimal(10,2) NOT NULL DEFAULT '0.00'",        
    ));                
}

if(Yii::app()->db->schema->getTable("{{merchant}}")){
    $data[] = $table->add_Column("{{merchant}}",array(
        'access_permission_id'=>"int(10) DEFAULT NULL",        
    ));                
}

if(Yii::app()->db->schema->getTable("{{wallet_transactions}}")){
    $data[] = $table->add_Column("{{wallet_transactions}}",array(
        'expiry_date'=>"date DEFAULT NULL",        
        'expired_flag'=>"tinyint(1) NOT NULL DEFAULT '0'"
    ));                
}

// END 2.0.6 UPDATE


// START 2.0.7 UPDATE
if(!Yii::app()->db->schema->getTable("{{food_item_reviews}}")){
    $table->createTable(
        "{{food_item_reviews}}",
        array(
            'review_id'=>'pk',                                                          
            'merchant_id'=>"int(10) NOT NULL DEFAULT '0'",
            'item_id'=>"int(10) NOT NULL DEFAULT '0'",
            'order_id'=>"int(10) NOT NULL DEFAULT '0'",
            'client_id'=>"int(10) NOT NULL DEFAULT '0'",
            'rating'=>"tinyint(5) NOT NULL DEFAULT '0'",
            'review_text'=>"text",
            'created_at'=>"datetime DEFAULT NULL",
        ),
    'ENGINE=InnoDB DEFAULT CHARSET=utf8');    
    $table->create_Index("{{food_item_reviews}}",array(
        'merchant_id','item_id','order_id','client_id','rating'
    )); 	    
    $data[] = "{{food_item_reviews}} table created";    
}
// END 2.0.7 UPDATE

CMerchantMenu::CleanTranslationData();
if(Yii::app()->db->schema->getTable("{{client_address}}")){
    $data[] = $table->add_Column("{{client_address}}",array(
        'street_number'=>"varchar(255) NOT NULL DEFAULT '' AFTER `address1`",  
    ));    
}
if(Yii::app()->db->schema->getTable("{{map_places}}")){
    $data[] = $table->add_Column("{{map_places}}",array(
        'street_number'=>"varchar(255) NOT NULL DEFAULT '' AFTER `address1`",  
    ));    
}
if(Yii::app()->db->schema->getTable("{{map_places}}")){
    $data[] = $table->add_Column("{{map_places}}",array(
        'city'=>"varchar(255) NOT NULL DEFAULT '' AFTER `street_number`",  
    ));    
}
if(Yii::app()->db->schema->getTable("{{client_address}}")){
    $data[] = $table->add_Column("{{client_address}}",array(
        'city'=>"varchar(255) NOT NULL DEFAULT '' AFTER `street_number`",  
    ));    
}
if(Yii::app()->db->schema->getTable("{{merchant}}")){
    $data[] = $table->add_Column("{{merchant}}",array(
        'delivery_zone_type'=>"varchar(255) NOT NULL DEFAULT '' AFTER `distance_unit`",  
    ));    
}
if(Yii::app()->db->schema->getTable("{{merchant}}")){
    $data[] = $table->add_Column("{{merchant}}",array(
        'delivery_zone_covered'=>"mediumtext AFTER `delivery_zone_type`",  
    ));    
}
if(Yii::app()->db->schema->getTable("{{review}}")){
    $data[] = $table->add_Column("{{review}}",array(
        'driver_id'=>"int(10) NOT NULL DEFAULT 0 AFTER `client_id`",  
    ));    
}
// if(Yii::app()->db->schema->getTable("{{review}}")){
//     Yii::app()->db->createCommand("
//     ALTER TABLE {{review}} ADD KEY `driver_id`
//    ")->query();
// }

if(Yii::app()->db->schema->getTable("{{pages}}")){
    Yii::app()->db->createCommand("
    ALTER TABLE {{pages}} MODIFY COLUMN `long_content` longtext
   ")->query();
}
// $model_item = AR_item::model()->findAll();
// if($model_item){
//     foreach ($model_item as $key => $value) {
//         $item = AR_item::model()->find('item_id=:item_id',array(
//             ':item_id' => $model_item[$key]->item_id,
//             ));
//         $data[] = "model ".$item->item_token;
//         if($item){
//             $data[] = "item ".$item->item_token;
//             $item->scenario = 'update_item_available';
//             $item->item_token = CommonUtility::generateToken("{{item}}",'item_token', CommonUtility::generateAplhaCode(20) );
//             if($item->save()){
//                 $data[] = "new ".$item->item_token;
//             }
//         };
//     }
// }

// INSERT DRIVER ADDON
$model = AR_addons::model()->find("uuid=:uuid",[':uuid'=>'JNx1b1VG5yF0lL5n+0aILB+oj4uN13AiDlQilYCEG/cIJVCdlLdnjQTu7B+SPpbRrxlPNud7N6wSnoCxHQ==']);
if(!$model){
    $model = new AR_addons();
    $model->addon_name = "Driver";
    $model->uuid = "JNx1b1VG5yF0lL5n+0aILB+oj4uN13AiDlQilYCEG/cIJVCdlLdnjQTu7B+SPpbRrxlPNud7N6wSnoCxHQ==";
    $model->version = "1.0.3";
    $model->activated = 1;
    $model->image = "rider_banner.png";
    $model->path = "upload/all";
    $model->purchase_code = "";
    $model->date_created = CommonUtility::dateNow();
    $model->date_modified = CommonUtility::dateNow();
    $model->ip_address = "127.0.0.1";
    $model->save();
}
$data[] = "Driver Addon Added";
// END INSERT DRIVER ADDON
$rider_version = '1.0.3';
if($model = AR_addons::model()->find("uuid=:uuid",[':uuid'=>'JNx1b1VG5yF0lL5n+0aILB+oj4uN13AiDlQilYCEG/cIJVCdlLdnjQTu7B+SPpbRrxlPNud7N6wSnoCxHQ=='])){ 
    $model->addon_name = "Driver";    
    $model->version = "1.0.3";
    $model->save();
}

Yii::app()->cache->flush();

// FINISH
$data[] = "FINISH FIXING TABLES";

ob_end_clean();