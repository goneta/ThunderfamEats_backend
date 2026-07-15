<?php
class TableDataStatus extends CDbMigration 
{

    public function add_Column($table_name='',$fields = array())
	{
		$stats = array();
		$table_cols = Yii::app()->db->schema->getTable($table_name);
		if(is_array($fields) && count($fields)>=1){
			foreach ($fields as $key=>$val) {
				if(!isset($table_cols->columns[$key])) {							
				   $this->addColumn($table_name,$key,$val);				   
				    $stats[]= "field $key [OK]";
				} else {
					$stats[]= "field $key already exist";
				}							
			}
		}			
		return $stats;																			
	}
		
	public function create_Index($table_name='', $fields = array())
	{	
		$stats = array();
		foreach ($fields as $val) {		   
		   try {
		      $this->createIndex($val,$table_name,$val);
		      $stats[]  = "index [$val] created";
		   } catch (Exception $e) {
			  $stats[]  = "index [$val] already";
		   }					
		}	
		return $stats;
	}
    
    public static function get()
    {
        $item[] = [
            'name'=>'item',
            'count'=>AR_item::model()->count()
        ];
        $item[] = [
            'name'=>'category',
            'count'=>AR_category::model()->count()
        ];
        $item[] = [
            'name'=>'subcategory',
            'count'=>AR_subcategory::model()->count()
        ];
        $item[] = [
            'name'=>'subcategory_item',
            'count'=>AR_subcategory_item::model()->count()
        ];
        $item[] = [
            'name'=>'size',
			'count'=>AR_size::model()->count()
        ];
        $item[] = [
            'name'=>'ingredients',
			'count'=>AR_ingredients::model()->count()
        ];
        $item[] = [
            'name'=>'cooking_ref',
			'count'=>AR_cookingref::model()->count()
        ];

        $merchant[] = [
            'name'=>'merchant',
			'count'=>AR_merchant::model()->count()
        ];
        $merchant[] = [
            'name'=>'merchant_payment_method',
			'count'=>AR_merchant_payment_method::model()->count()
        ];
        $merchant[] = [
            'name'=>'merchant_user',
			'count'=>AR_merchant_user::model()->count()
        ];

        $table_booking[] = [
            'name'=>'table_reservation',
			'count'=>AR_table_reservation::model()->count()
        ];
        $table_booking[] = [
            'name'=>'table_reservation_history',
			'count'=>AR_table_reservation_history::model()->count()
        ];
        $table_booking[] = [
            'name'=>'table_room',
			'count'=>AR_table_room::model()->count()
        ];
        $table_booking[] = [
            'name'=>'table_shift',
			'count'=>AR_table_shift::model()->count()
        ];
        $table_booking[] = [
            'name'=>'table',
			'count'=>AR_table_tables::model()->count()
        ];

        $customer[] = [
            'name'=>'client',
			'count'=>AR_client::model()->count()
        ];
        $customer[] = [
            'name'=>'client_address',
			'count'=>AR_client_address::model()->count()
        ];
        $customer[] = [
            'name'=>'client_cc',
			'count'=>AR_client_cc::model()->count()
        ];
        $customer[] = [
            'name'=>'client_payment_method',
			'count'=>AR_client_payment_method::model()->count()
        ];
        $customer[] = [
            'name'=>'review',
			'count'=>AR_review::model()->count()
        ];

        $printer[] = [
            'name'=>'printer',
			'count'=>AR_printer::model()->count()
        ];
        $printer[] = [
            'name'=>'printer_logs',
			'count'=>AR_printer_logs::model()->count()
        ];

        $logs[] = [
            'name'=>'email_logs',
			'count'=>AR_email_logs::model()->count()
        ];
        $logs[] = [
            'name'=>'push',
			'count'=>AR_push::model()->count()
        ];
        $logs[] = [
            'name'=>'sms_broadcast',
			'count'=>AR_smsbroadcast::model()->count()
        ];
        $logs[] = [
            'name'=>'sms_broadcast_details',
			'count'=>AR_sms_broadcast_details::model()->count()
        ];

        $promotional[] = [
            'name'=>'voucher_new',
			'count'=>AR_voucher_new::model()->count()
        ];
        $promotional[] = [
            'name'=>'offers',
			'count'=>AR_offers::model()->count()
        ];
        $promotional[] = [
            'name'=>'banner',
			'count'=>AR_banner::model()->count()
        ];

        $invoice[] = [
            'name'=>'invoice',
			'count'=>AR_invoice::model()->count()
        ];
        $invoice[] = [
            'name'=>'plans_invoice',
			'count'=>AR_plans_invoice::model()->count()
        ];

        $transaction[] = [
            'name'=>'wallet_transactions',
			'count'=>AR_wallet_transactions::model()->count()
        ];

        $order[] = [
            'name'=>'ordernew',
			'count'=>AR_ordernew::model()->count()
        ];        
        $order[] = [
            'name'=>'ordernew_additional_charge',
			'count'=>AR_ordernew_additional_charge::model()->count()
        ];        
        $order[] = [
            'name'=>'ordernew_addons',
			'count'=>AR_ordernew_addons::model()->count()
        ];        
        $order[] = [
            'name'=>'ordernew_attributes',
			'count'=>AR_ordernew_attributes::model()->count()
        ];        
        $order[] = [
            'name'=>'ordernew_history',
			'count'=>AR_ordernew_history::model()->count()
        ];        
        $order[] = [
            'name'=>'ordernew_item',
			'count'=>AR_ordernew_item::model()->count()
        ];        
        $order[] = [
            'name'=>'ordernew_meta',
			'count'=>AR_ordernew_meta::model()->count()
        ];        
        $order[] = [
            'name'=>'ordernew_summary_transaction',
			'count'=>AR_ordernew_summary_transaction::model()->count()
        ];        
        $order[] = [
            'name'=>'ordernew_transaction',
			'count'=>AR_ordernew_transaction::model()->count()
        ];        
        $order[] = [
            'name'=>'ordernew_trans_meta',
			'count'=>AR_ordernew_trans_meta::model()->count()
        ];        

        $attributes[] = [
            'name'=>'cuisine',
			'count'=>AR_cuisine::model()->count()
        ];        
        $attributes[] = [
            'name'=>'dishes',
			'count'=>AR_dishes::model()->count()
        ];        
        $attributes[] = [
            'name'=>'tags',
			'count'=>AR_tags::model()->count()
        ];        
        $attributes[] = [
            'name'=>'pages',
			'count'=>AR_pages::model()->count()
        ];        

        $location[] = [
            'name'=>'featured_location',
			'count'=>AR_featured_location::model()->count()
        ];        
        $location[] = [
            'name'=>'location_area',
			'count'=>AR_area::model()->count()
        ];        
        $location[] = [
            'name'=>'location_cities',
			'count'=>AR_city::model()->count()
        ];        
        $location[] = [
            'name'=>'location_rate',
			'count'=>AR_location_rate::model()->count()
        ];        
        $location[] = [
            'name'=>'location_states',
			'count'=>AR_location_states::model()->count()
        ];        
        $location[] = [
            'name'=>'zones',
			'count'=>AR_zones::model()->count()
        ];        
        $location[] = [
            'name'=>'map_places',
			'count'=>AR_map_places::model()->count()
        ];        

        // $user[] = [
        //     'name'=>'admin_user',
		// 	'count'=>AR_AdminUser::model()->count()
        // ];        
        $user[] = [
            'name'=>'merchant_user',
			'count'=>AR_merchant_user::model()->count()
        ];        

        $media[] = [
            'name'=>'media_files',
			'count'=>AR_media::model()->count()
        ];        

        $cart[] = [
            'name'=>'cart',
			'count'=>AR_cart::model()->count()
        ];        
        $cart[] = [
            'name'=>'cart_addons',
			'count'=>AR_cart_addons::model()->count()
        ];        
        $cart[] = [
            'name'=>'cart_attributes',
			'count'=>AR_cart_attributes::model()->count()
        ];        

        $jobs[] = [
            'name'=>'job_queue',
			'count'=>AR_job_queue::model()->count()
        ];        

        return [
            'Food items'=>$item,
            'Merchant'=>$merchant,
            'Table Reservation'=>$table_booking,
            'Customer'=>$customer,
            'Printer'=>$printer,
            'Logs'=>$logs,
            'Promotional'=>$promotional,
            'Invoice'=>$invoice,
            'Transactions'=>$transaction,
            'Orders'=>$order,
            'Attributes'=>$attributes,
            'Locations'=>$location,
            'Users'=>$user,
            'Media files'=>$media,
            'Cart'=>$cart,
            'Jobs'=>$jobs
        ];
    }

    public static function processDelete($data=array())
    {        
        if(is_array($data) && count($data)>=1){
            foreach ($data as $key => $items) {                                
                switch ($items) {
                    case 'item':         
                        AR_item::model()->deleteAll();
                        AR_item_meta::model()->deleteAll();
                        AR_item_relationship_category::model()->deleteAll();
                        AR_item_relationship_size::model()->deleteAll();
                        AR_item_addon::model()->deleteAll();
                        AR_item_translation::model()->deleteAll();
                        //AR_subcategory_item_relationships::model()->deleteAll();                        
                        //Yii::app()->db->createCommand("DELETE FROM {{item_relationship_subcategory_item}}")->query();
                        //Yii::app()->db->createCommand("DELETE FROM {{subcategory_item_translation}}")->query();                        
                        break; 

                    case "category":    
                        AR_category::model()->deleteAll();
                        AR_category_relationship_dish::model()->deleteAll();
                        Yii::app()->db->createCommand("DELETE FROM {{category_translation}}")->query();
                        AR_item_relationship_category::model()->deleteAll();
                        break; 

                    case "subcategory":    
                        AR_subcategory::model()->deleteAll();
                        Yii::app()->db->createCommand("DELETE FROM {{item_relationship_subcategory}}")->query();
                        Yii::app()->db->createCommand("DELETE FROM {{tem_relationship_subcategory_item}}")->query();                                                
                        Yii::app()->db->createCommand("DELETE FROM {{subcategory_translation}}")->query();
                        break; 

                    case "subcategory_item":    
                        AR_subcategory_item::model()->deleteAll();
                        AR_subcategory_item_relationships::model()->deleteAll(); 
                        Yii::app()->db->createCommand("DELETE FROM {{subcategory_item_translation}}")->query();
                        break; 

                    case "size":     
                        AR_size::model()->deleteAll();
                        AR_item_relationship_size::model()->deleteAll();
                        Yii::app()->db->createCommand("DELETE FROM {{size_translation}}")->query();
                        break; 

                    case "ingredients":         
                        AR_ingredients::model()->deleteAll();
                        Yii::app()->db->createCommand("DELETE FROM {{ingredients_translation}}")->query();
                        break; 

                    case "cooking_ref":      
                        AR_cookingref::model()->deleteAll();
                        Yii::app()->db->createCommand("DELETE FROM {{cooking_ref_translation}}")->query();
                        break; 

                    case "merchant":     
                        AR_merchant::model()->deleteAll();
                        AR_cuisine_merchant::model()->deleteAll();
                        AR_merchant_meta::model()->deleteAll();                        
                        break; 

                    case "merchant_payment_method":    
                        AR_merchant_payment_method::model()->deleteAll();
                        AR_payment_gateway_merchant::model()->deleteAll();
                        break; 

                    case "merchant_user":    
                        AR_merchant_user::model()->deleteAll();
                        break; 

                    case "table_reservation":    
                        AR_table_reservation::model()->deleteAll();
                        break;                         
                      
                    case "table_reservation_history":    
                        AR_table_reservation_history::model()->deleteAll();
                        break;                         

                    case "table_room":    
                        AR_table_room::model()->deleteAll();
                        break;                                                 

                    case "table_shift":    
                        AR_table_shift::model()->deleteAll();
                        break;                        

                    case "table":    
                        AR_table_tables::model()->deleteAll();
                        break;                            

                    case "client":    
                        AR_client::model()->deleteAll();
                        break;                                

                    case "client_address":    
                        AR_client_address::model()->deleteAll();
                        break;                                     

                    case "client_cc":    
                        AR_client_cc::model()->deleteAll();
                        break;                                          

                    case "client_payment_method":    
                        AR_client_payment_method::model()->deleteAll();
                        break;                                              

                    case "review":    
                        AR_review::model()->deleteAll();
                        break;                                                   
                     
                    case "printer":    
                        AR_printer::model()->deleteAll();
                        break;                                                       

                    case "printer_logs":    
                        AR_printer_logs::model()->deleteAll();
                        break;                                                            

                    case "email_logs":    
                        AR_email_logs::model()->deleteAll();
                        break; 

                    case 'push':                        
                        AR_push::model()->deleteAll();
                        break;

                    case 'sms_broadcast':             
                        AR_smsbroadcast::model()->deleteAll();           
                        break;    

                    case 'sms_broadcast_details':                        
                        AR_sms_broadcast_details::model()->deleteAll();           
                        break;                            

                    case 'voucher_new':                        
                        AR_voucher_new::model()->deleteAll();           
                        break;                                 

                    case 'offers':                        
                        AR_offers::model()->deleteAll();           
                        break;                                 

                    case 'banner':                        
                        AR_banner::model()->deleteAll();           
                        break;                                       

                    case 'invoice':                        
                        AR_invoice::model()->deleteAll();           
                        AR_invoice_meta::model()->deleteAll();           
                        break;                                       

                    case 'plans_invoice':                        
                        AR_plans_invoice::model()->deleteAll();           
                        break;                                                               

                    case 'wallet_transactions':                        
                        AR_wallet_transactions::model()->deleteAll();           
                        AR_wallet_transactions_meta::model()->deleteAll();  
                        break;                                                               

                    case 'ordernew':                        
                        AR_ordernew::model()->deleteAll();                        
                        break;                                                                   

                    case 'ordernew_additional_charge':                        
                        AR_ordernew_additional_charge::model()->deleteAll();           
                        break;                                                                                           

                    case 'ordernew_addons':                        
                        AR_ordernew_addons::model()->deleteAll();           
                        break;                                                                                                                   

                    case 'ordernew_attributes':                        
                        AR_ordernew_attributes::model()->deleteAll();           
                        break;                                                                      

                    case 'ordernew_history':                        
                        AR_ordernew_history::model()->deleteAll();           
                        break;                                                                           

                    case 'ordernew_item':                        
                        AR_ordernew_item::model()->deleteAll();           
                        break;                                                                                 

                    case 'ordernew_meta':                        
                        AR_ordernew_meta::model()->deleteAll();           
                        break;                                                                                                         

                    case 'ordernew_summary_transaction':                        
                        AR_ordernew_summary_transaction::model()->deleteAll();           
                        break;                                                                                                              

                    case 'ordernew_transaction':                        
                        AR_ordernew_transaction::model()->deleteAll();           
                        break;                                                                                                                                      

                    case 'ordernew_trans_meta':                        
                        AR_ordernew_trans_meta::model()->deleteAll();           
                        break;                                                                                                                                                              

                    case 'cuisine':                        
                        AR_cuisine::model()->deleteAll();           
                        break;                                                                                       

                    case 'dishes':                        
                        AR_dishes::model()->deleteAll();           
                        break;                                                                                            
                      
                    case 'tags':                        
                        AR_tags::model()->deleteAll();           
                        break;                                                                                                 

                    case 'pages':                        
                        AR_pages::model()->deleteAll();           
                        break;                                                                                                      

                    case 'featured_location':                        
                        AR_featured_location::model()->deleteAll();           
                        break;                                                                                                           

                    case 'location_area':                        
                        AR_area::model()->deleteAll();           
                        break;                                                                                                                
                       
                    case 'location_cities':                        
                        AR_city::model()->deleteAll();           
                        break;                                                                                                                     

                    case 'location_rate':                        
                        AR_location_rate::model()->deleteAll();           
                        break;                                                                                                                           

                    case 'location_states':                        
                        AR_location_states::model()->deleteAll();           
                        break;                                                                                                                                

                    case 'zones':                        
                        AR_zones::model()->deleteAll();           
                        break;                                                                                                                                     

                    case 'merchant_user':                        
                        AR_merchant_user::model()->deleteAll();           
                        break;                                                                                                                                          

                    case 'media_files':                        
                        AR_media::model()->deleteAll();           
                        break;      

                    case 'map_places':    
                        AR_map_places::model()->deleteAll();           
                        break;     

                    case "job_queue":                        
                       AR_job_queue::model()->deleteAll();           
                       break;     

                }// end switch
            }
        }
    }

    public function createFullTextIndexIfNeeded($tableName='', $columnName='', $indexName='') {

        $stats = array();  $indexExists = 0;        
        $query = "SELECT COUNT(*) AS index_exists FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = '$tableName' AND index_name = '$indexName'";
        $result = Yii::app()->db->createCommand($query)->queryRow();
        $indexExists = isset($result['index_exists'])?$result['index_exists']:0;        
        if ($indexExists == 0) {            
            $sql = "ALTER TABLE $tableName ADD FULLTEXT($columnName)";            
            Yii::app()->db->createCommand($sql)->query();
            $stats[] = "Full-text index($columnName) created successfully.\n";
        } else {
            $stats[] = "Full-text index($columnName) already exists.\n";
        }
        return $stats;
    }

    public function fieldExist($table_name='',$field_name=''){
        $query = "        
        SELECT *
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = ".q(DB_NAME)."
            AND TABLE_NAME = ".q($table_name)."
            AND COLUMN_NAME = ".q($field_name)."
        ";
        $result = Yii::app()->db->createCommand($query)->queryRow();
        if($result){            
            return true;
        }
        return false;
    }

    public function getLastIncrement($table='')
    {
        $stmt = "SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = ".q(DB_NAME)." AND TABLE_NAME = ".q($table)."";
        if($res = Yii::app()->db->createCommand($stmt)->queryRow()){
            return $res['AUTO_INCREMENT'];
        }
        return false;
    }

    public static function getLastSequence($table_name='',$fields='', $where='')
	{
		$stmt = "
		SELECT max($fields) as total
		FROM {{{$table_name}}}
		$where
		";	
		if($res = Yii::app()->db->createCommand($stmt)->queryRow()){
			return $res['total']+1;
		}
		return 1;
	}

}
// end class