<?php
set_time_limit(0);
class ScrapmenuController  extends SiteCommon {

    private $runactions_enabled;

    public function beforeAction($action)
	{	
		$key = Yii::app()->input->get("key");			
		if(CRON_KEY===$key){
		   $this->runactions_enabled = isset(Yii::app()->params['settings']['runactions_enabled'])?Yii::app()->params['settings']['runactions_enabled']:false;		
		   return true;
		}
		return false;
	}

    public function actionIndex(){

    }
	
    public function actionDuplicatemerchant()
    {
        Yii::import('ext.runactions.components.ERunActions');
		if($this->runactions_enabled){
			if (ERunActions::runBackground()) {
				$this->copyMerchant();
			}		
		} else {
			$this->copyMerchant();
		}        
    }

    public function copyMerchant()
    {                

        CCacheData::add();        
        $original_merchant_id = intval(Yii::app()->input->get('merchant_id')); 
        $payload = Yii::app()->input->get('payload');        
        $payload = !empty($payload)?explode(",",$payload):false;

        if(!is_array($payload) && count((array)$payload)<=1 || $original_merchant_id<=0){
            Yii::app()->end();
        }                

        $builder = Yii::app()->db->schema->commandBuilder;
        $table  = new TableDataStatus();        
        $stmt = "        
        INSERT INTO {{merchant}} (merchant_uuid,restaurant_name,restaurant_phone,contact_name,
        address,free_delivery,status,is_featured,is_ready,is_sponsored,sponsored_expiration,is_commission,percent_commision,
        commision_based,latitude,lontitude,logo,path,merchant_type,commision_type,package_id,allowed_offline_payment,invoice_terms,
        distance_unit,delivery_distance_covered,header_image,path2,description,short_description,close_store,
        disabled_ordering,pause_ordering,date_created,ip_address
        )
        SELECT uuid(),restaurant_name,restaurant_phone,contact_name,
        address,free_delivery,status,is_featured,is_ready,is_sponsored,sponsored_expiration,is_commission,percent_commision,
        commision_based,latitude,lontitude,logo,path,merchant_type,commision_type,package_id,allowed_offline_payment,invoice_terms,
        distance_unit,delivery_distance_covered,header_image,path2,description,short_description,close_store,
        disabled_ordering,pause_ordering,".q(CommonUtility::dateNow()).",".q(CommonUtility::userIp())."
        FROM {{merchant}}
        WHERE merchant_id = ".q($original_merchant_id).";
        ";        
        Yii::app()->db->createCommand($stmt)->query();

        $new_merchant_id = Yii::app()->db->getLastInsertID();        
            
        $merchant = new AR_merchant();
        $model = AR_merchant::model()->findByPk($original_merchant_id);
        $new_slug = $merchant->generateSlug($model->restaurant_name);
        
        Yii::app()->db->createCommand("
            UPDATE {{merchant}}
            SET restaurant_slug=".q($new_slug)."        
            WHERE merchant_id=".q($new_merchant_id)."
        ")->query();
        

        // COPY BANNER
        if(in_array('banner',$payload)){
            $stmt_banner = "        
            INSERT INTO {{banner}} (banner_uuid,owner,title,banner_type,meta_value1,meta_value2,meta_value3,meta_value4,path,photo,sequence,status,date_created,ip_address)
            SELECT uuid(),owner,title,banner_type,".q($new_merchant_id).",meta_value2,meta_value3,meta_value4,path,photo,sequence,status,date_created,ip_address
            FROM {{banner}}
            WHERE meta_value1 = ".q($original_merchant_id)."
            AND owner='merchant'         
            ";                
            Yii::app()->db->createCommand($stmt_banner)->query();
        }

        // COPY FOOD ITEMS        
        $last_item_id = $table->getLastIncrement("{{item}}");        
        $last_category_id = $table->getLastIncrement("{{category}}");
        $last_item_relationship_size_id = $table->getLastIncrement("{{item_relationship_size}}");

        $data = []; 
        $meta_data = [];         
        $irc_data=[];
        $irs_data=[];
        $irsub_data = [];
        $irsi_data = [];
        $availability_data = [];
        $item_promo_data = [];
        $item_translation_data = [];
        if($res = Yii::app()->db->createCommand("SELECT * FROM {{item}} WHERE merchant_id=".q($original_merchant_id)." ")->queryAll()){
            foreach ($res as $items) {       
                
                $new_item_id = $last_item_id++;

                $data[] = [
                    'item_id'=>$new_item_id,
                    'merchant_id'=>$new_merchant_id,
                    'item_name'=>$items['item_name'],
                    'slug'=>AttributesTools::createSlug($items['item_name'],"{{item}}"),
                    'item_description'=>$items['item_description'],
                    'item_short_description'=>$items['item_short_description'],
                    'status'=>$items['status'],
                    'photo'=>$items['photo'],
                    'path'=>$items['path'],
                    'sequence'=>$items['sequence'],
                    'is_featured'=>$items['is_featured'],
                    'non_taxable'=>$items['non_taxable'],
                    'available'=>$items['available'],
                    'points_earned'=>$items['points_earned'],
                    'points_enabled'=>$items['points_enabled'],
                    'packaging_fee'=>$items['packaging_fee'],
                    'packaging_incremental'=>$items['packaging_incremental'],
                    'item_token'=>CommonUtility::generateToken("{{item}}",'item_token', CommonUtility::generateAplhaCode(20) ),
                    'sku'=>'',
                    'track_stock'=>$items['track_stock'],
                    'supplier_id'=>$items['supplier_id'],
                    'meta_title'=>$items['meta_title'],
                    'meta_description'=>$items['meta_description'],
                    'meta_keywords'=>$items['meta_keywords'],
                    'meta_image'=>$items['meta_image'],
                    'meta_image_path'=>$items['meta_image_path'],
                    'cooking_ref_required'=>$items['cooking_ref_required'],
                    'ingredients_preselected'=>$items['ingredients_preselected'],
                    'available_at_specific'=>$items['available_at_specific'],
                    'not_for_sale'=>$items['not_for_sale'],
                    'color_hex'=>$items['color_hex'],
                    'visible'=>$items['visible'],
                    'date_created'=>CommonUtility::dateNow(),
                    'ip_address'=>CommonUtility::userIp()
                ];              
                            

                if($item_translation = Yii::app()->db->createCommand("SELECT * FROM {{item_translation}} WHERE item_id=".q($items['item_id'])." ")->queryAll()){
                    foreach ($item_translation as $item_translation_items) {                        
                        $item_translation_data[] = [
                            'merchant_id'=>$new_merchant_id,
                            'item_id'=>$new_item_id,
                            'language'=>$item_translation_items['language'],
                            'item_name'=>$item_translation_items['item_name'],
                            'item_description'=>$item_translation_items['item_description'],
                            'item_short_description'=>$item_translation_items['item_short_description'],
                        ];
                    }
                }

                if($meta = Yii::app()->db->createCommand("SELECT * FROM {{item_meta}} WHERE 
                merchant_id=".q($original_merchant_id)." AND item_id=".q($items['item_id'])." ")->queryAll()){
                    foreach ($meta as $item_meta) {
                        $meta_data[] = [
                            'merchant_id'=>$new_merchant_id,
                            'item_id'=>$new_item_id,
                            'meta_name'=>$item_meta['meta_name'],
                            'meta_id'=>$item_meta['meta_id'],
                            'meta_value'=>$item_meta['meta_value'],
                        ];
                    }
                }

                if($irc = Yii::app()->db->createCommand("SELECT * FROM {{item_relationship_category}} WHERE 
                  merchant_id=".q($original_merchant_id)." AND item_id=".q($items['item_id'])." ")->queryAll()){ 
                    foreach ($irc as $item_irc) {                        
                        $irc_data[] = [
                            'merchant_id'=>$new_merchant_id,
                            'item_id'=>$new_item_id,
                            'cat_id'=>$item_irc['cat_id'],
                            'sequence'=>$item_irc['sequence'],
                        ];
                    }
                }
                

                if($irs = Yii::app()->db->createCommand("SELECT * FROM {{item_relationship_size}} WHERE 
                  merchant_id=".q($original_merchant_id)." AND item_id=".q($items['item_id'])." ")->queryAll()){ 
                    foreach ($irs as $item_irs) {
                        $new_item_relationship_size_id = $last_item_relationship_size_id++;
                        $irs_data[] = [
                            'item_size_id'=>$new_item_relationship_size_id,
                            'merchant_id'=>$new_merchant_id,
                            'item_token'=>CommonUtility::generateToken("{{item_relationship_size}}",'item_token', CommonUtility::generateAplhaCode(20) ),
                            'item_id'=>$new_item_id,
                            'size_id'=>$item_irs['size_id'],
                            'price'=>$item_irs['price'],
                            'cost_price'=>$item_irs['cost_price'],
                            'discount'=>$item_irs['discount'],
                            'discount_type'=>$item_irs['discount_type'],
                            'discount_start'=>$item_irs['discount_start'],
                            'discount_end'=>$item_irs['discount_end'],
                            'sequence'=>$item_irs['sequence'],
                            'sku'=>$item_irs['sku'],
                            'available'=>$item_irs['available'],
                            'low_stock'=>$item_irs['low_stock'],
                            'created_at'=>CommonUtility::dateNow()
                        ];

                        if($irsub = Yii::app()->db->createCommand("SELECT * FROM {{item_relationship_subcategory}} WHERE 
                        merchant_id=".q($original_merchant_id)." AND item_id=".q($items['item_id'])." 
                        AND item_size_id=".q($item_irs['item_size_id'])." ")->queryAll()){                             
                            foreach ($irsub as $item_irsub) {
                                $irsub_data[] = [
                                    'merchant_id'=>$new_merchant_id,
                                    'item_id'=>$new_item_id,
                                    'item_size_id'=>$new_item_relationship_size_id,
                                    'subcat_id'=>$item_irsub['subcat_id'],
                                    'multi_option'=>$item_irsub['multi_option'],
                                    'multi_option_min'=>$item_irsub['multi_option_min'],
                                    'multi_option_value'=>$item_irsub['multi_option_value'],
                                    'require_addon'=>$item_irsub['require_addon'],
                                    'pre_selected'=>$item_irsub['pre_selected'],
                                    'sequence'=>$item_irsub['sequence'],
                                ];
                            }
                        }
                    }
                }                


                if($irsi = Yii::app()->db->createCommand("SELECT * FROM {{item_relationship_subcategory_item}} WHERE 
                  merchant_id=".q($original_merchant_id)." AND item_id=".q($items['item_id'])." ")->queryAll()){ 
                    foreach ($irsi as $item_irsi) {
                        $irsi_data[] = [
                            'merchant_id'=>$new_merchant_id,
                            'item_id'=>$new_item_id,
                            'subcat_id'=>$item_irsi['subcat_id'],
                            'sub_item_id'=>$item_irsi['sub_item_id'],
                        ];
                    }
                }
                
                if($availability = Yii::app()->db->createCommand("SELECT * FROM {{availability}} WHERE 
                  merchant_id=".q($original_merchant_id)." AND meta_value=".q($items['item_id'])." AND meta_name='item' ")->queryAll()){ 
                    foreach ($availability as $availability_items) {
                        $availability_data[] = [
                            'merchant_id'=>$new_merchant_id,
                            'meta_name'=>$availability_items['meta_name'],
                            'meta_value'=>$new_item_id,
                            'day_of_week'=>$availability_items['day_of_week'],
                            'status'=>$availability_items['status'],
                            'start_time'=>$availability_items['start_time'],
                            'end_time'=>$availability_items['end_time'],
                            'date_created'=>CommonUtility::dateNow(),
                            'ip_address'=>CommonUtility::userIp(),
                        ];
                    }
                }

                if($item_promo = Yii::app()->db->createCommand("SELECT * FROM {{item_promo}} WHERE 
                  merchant_id=".q($original_merchant_id)." AND item_id=".q($items['item_id'])." ")->queryAll()){ 
                    foreach ($item_promo as $item_promo_items) {
                        $item_promo_data[] = [
                            'merchant_id'=>$new_merchant_id,
                            'item_id'=>$new_item_id,
                            'promo_type'=>$item_promo_items['promo_type'],
                            'buy_qty'=>$item_promo_items['buy_qty'],
                            'get_qty'=>$item_promo_items['get_qty'],
                            'item_id_promo'=>$new_item_id,
                            'discount_start'=>$item_promo_items['discount_start'],
                            'discount_end'=>$item_promo_items['discount_end'],
                            'date_created'=>CommonUtility::dateNow(),
                            'ip_address'=>CommonUtility::userIp()
                        ];
                    }  
                }

                if(in_array('banner',$payload)){
                    Yii::app()->db->createCommand("
                        UPDATE {{banner}}
                        SET meta_value2=".q($new_item_id)."
                        WHERE meta_value1=".q($new_merchant_id)."
                        AND meta_value2=".q($items['item_id'])."
                    ")->query();    
                }                

            }
            // END FOOD ITEM LOOP
            
            if(in_array('items',$payload)):
                if(is_array($data) && count($data)>=1){ 
                    $command=$builder->createMultipleInsertCommand('{{item}}', $data);
                    $command->execute();
                }
                
                if(is_array($item_translation_data) && count($item_translation_data)>=1){                 
                    $command=$builder->createMultipleInsertCommand('{{item_translation}}', $item_translation_data);
                    $command->execute();
                }
                
                if(is_array($meta_data) && count($meta_data)>=1){                   
                    $command=$builder->createMultipleInsertCommand('{{item_meta}}', $meta_data);
                    $command->execute();
                }
                
                if(is_array($irc_data) && count($irc_data)>=1){                            
                    $command=$builder->createMultipleInsertCommand('{{item_relationship_category}}', $irc_data);
                    $command->execute();
                }

                
                if(is_array($irs_data) && count($irs_data)>=1){ 
                    $command=$builder->createMultipleInsertCommand('{{item_relationship_size}}', $irs_data);
                    $command->execute();
                }                
                
                if(is_array($irsub_data) && count($irsub_data)>=1){          
                    $command=$builder->createMultipleInsertCommand('{{item_relationship_subcategory}}', $irsub_data);
                    $command->execute();
                }
                
                if(is_array($irsi_data) && count($irsi_data)>=1){                     
                    $command=$builder->createMultipleInsertCommand('{{item_relationship_subcategory_item}}', $irsi_data);
                    $command->execute();
                }
                
                if(is_array($availability_data) && count($availability_data)>=1){  
                    $command=$builder->createMultipleInsertCommand('{{availability}}', $availability_data);
                    $command->execute();
                }
                
                if(is_array($item_promo_data) && count($item_promo_data)>=1){      
                    $command=$builder->createMultipleInsertCommand('{{item_promo}}', $item_promo_data);
                    $command->execute();
                }
            endif;
        }
        // END FOOD ITEMS

                

        // FOOD CATEGORY        
        if(in_array('category',$payload)){
            $stmt_availability = "        
            INSERT INTO {{availability}} (merchant_id,meta_name,meta_value,day_of_week,status,start_time,end_time,date_created,ip_address)
            SELECT ".q($new_merchant_id).",meta_name,meta_value,day_of_week,status,start_time,end_time,date_created,ip_address
            FROM {{availability}}
            WHERE merchant_id = ".q($original_merchant_id)."
            AND meta_name='category'
            ";        
            Yii::app()->db->createCommand($stmt_availability)->query();

            $stmt_category_translation = "        
            INSERT INTO {{category_translation}} (merchant_id,cat_id,language,category_name,category_description)
            SELECT ".q($new_merchant_id).",cat_id,language,category_name,category_description
            FROM {{category_translation}}
            where 
            cat_id IN (
                select cat_id from {{category}}
                where merchant_id=".q($original_merchant_id)."
            )
            ";        
            Yii::app()->db->createCommand($stmt_category_translation)->query();


            $stmt = "        
            INSERT INTO {{category_relationship_dish}} (merchant_id,cat_id,dish_id)
            SELECT ".q($new_merchant_id).",cat_id,dish_id
            FROM {{category_relationship_dish}}
            where 
            cat_id IN (
                select cat_id from {{category}}
                where merchant_id=".q($original_merchant_id)."
            )
            ";        
            Yii::app()->db->createCommand($stmt)->query();
                    
            $category_data =[];
            if($category = Yii::app()->db->createCommand("SELECT * FROM {{category}} WHERE 
                    merchant_id=".q($original_merchant_id)." ")->queryAll()){            
                foreach ($category as  $category_items) {
                    $new_category_id = $last_category_id++;
                    $category_data[] = [
                        'cat_id'=>$new_category_id,
                        'merchant_id'=>$new_merchant_id,
                        'category_name'=>$category_items['category_name'],
                        'category_description'=>$category_items['category_description'],
                        'photo'=>$category_items['photo'],
                        'path'=>$category_items['path'],
                        'icon'=>$category_items['icon'],
                        'icon_path'=>$category_items['icon_path'],
                        'status'=>$category_items['status'],
                        'sequence'=>$category_items['sequence'],
                        'available_at_specific'=>$category_items['available_at_specific'],    
                        'date_created'=>CommonUtility::dateNow(),
                        'ip_address'=>CommonUtility::userIp()
                    ];            
                    Yii::app()->db->createCommand("
                        UPDATE {{item_relationship_category}}
                        SET cat_id=".q($new_category_id)."
                        WHERE merchant_id=".q($new_merchant_id)."
                        AND cat_id=".$category_items['cat_id'].";

                        UPDATE {{availability}}
                        SET meta_value=".q($new_category_id)."
                        WHERE merchant_id=".q($new_merchant_id)."
                        AND meta_value=".$category_items['cat_id']."
                        AND meta_name='category';

                        UPDATE {{category_translation}}
                        SET cat_id=".q($new_category_id)."
                        WHERE merchant_id=".q($new_merchant_id)."
                        AND cat_id=".$category_items['cat_id'].";                  

                        UPDATE {{category_relationship_dish}}
                        SET cat_id=".q($new_category_id)."
                        WHERE merchant_id=".q($new_merchant_id)."
                        AND cat_id=".$category_items['cat_id'].";  

                    ")->query();    
                }                        
                $command=$builder->createMultipleInsertCommand('{{category}}', $category_data);
                $command->execute();
            }        
        }


        // FOOD SIZE        
        if(in_array('size',$payload)){        
            $stmt = "        
            INSERT INTO {{size_translation}} (merchant_id,size_id,language,size_name)
            SELECT ".q($new_merchant_id).",size_id,language,size_name
            FROM {{size_translation}}
            where 
            size_id IN (
                select size_id from {{size}}
                where merchant_id=".q($original_merchant_id)."
            )
            ";        
            Yii::app()->db->createCommand($stmt)->query();

            $size_data = [];
            $last_size_id = $table->getLastIncrement("{{size}}");
            if($size = Yii::app()->db->createCommand("SELECT * FROM {{size}} WHERE 
                    merchant_id=".q($original_merchant_id)." ")->queryAll()){            
                foreach ($size as $item_size) {
                    $new_size_id = $last_size_id++;
                    $size_data[] = [
                        'size_id'=>$new_size_id,
                        'merchant_id'=>$new_merchant_id,
                        'size_name'=>$item_size['size_name'],
                        'sequence'=>$item_size['sequence'],
                        'status'=>$item_size['status'],
                        'date_created'=>CommonUtility::dateNow(),
                        'ip_address'=>CommonUtility::userIp()
                    ];
                    Yii::app()->db->createCommand("
                        UPDATE {{item_relationship_size}}
                        SET size_id=".q($new_size_id)."
                        WHERE merchant_id=".q($new_merchant_id)."
                        AND size_id=".$item_size['size_id']."
                    ")->query();    

                    Yii::app()->db->createCommand("
                        UPDATE {{size_translation}}
                        SET size_id=".q($new_size_id)."                        
                        WHERE size_id=".$item_size['size_id']."
                        AND merchant_id=".q($new_merchant_id)."
                    ")->query();    
                }            
                $command=$builder->createMultipleInsertCommand('{{size}}', $size_data);
                $command->execute();
            }
        }
        
        $stmt = "        
        INSERT INTO {{subcategory_item_relationships}} (merchant_id,subcat_id,sub_item_id,sequence)
        SELECT ".q($new_merchant_id).",subcat_id,sub_item_id,sequence
        FROM {{subcategory_item_relationships}}
        WHERE subcat_id IN (
            select subcat_id from st_subcategory
            where merchant_id=".q($original_merchant_id)."
        )
        ";                
        Yii::app()->db->createCommand($stmt)->query();            

        $stmt = "        
        INSERT INTO {{subcategory_translation}} (merchant_id,subcat_id,language,subcategory_name,subcategory_description)
        SELECT ".q($new_merchant_id).",subcat_id,language,subcategory_name,subcategory_description
        FROM {{subcategory_translation}}
        where 
        subcat_id IN (
            select subcat_id from {{subcategory}}
            where merchant_id=".q($original_merchant_id)."
        )
        ";        
        Yii::app()->db->createCommand($stmt)->query();

        if(in_array('addon_category',$payload)){
            $subcategory_data = [];
            $last_subcategory_data_id = $table->getLastIncrement("{{subcategory}}");
            if($subcategory = Yii::app()->db->createCommand("SELECT * FROM {{subcategory}} WHERE 
                    merchant_id=".q($original_merchant_id)." ")->queryAll()){
                foreach ($subcategory as $item_subcategory) {                
                    $new_subcategory_id = $last_subcategory_data_id++;
                    $subcategory_data[] = [
                        'subcat_id'=>$new_subcategory_id,
                        'merchant_id'=>$new_merchant_id,
                        'subcategory_name'=>$item_subcategory['subcategory_name'],
                        'subcategory_description'=>$item_subcategory['subcategory_description'],
                        'featured_image'=>$item_subcategory['featured_image'],
                        'path'=>$item_subcategory['path'],
                        'discount'=>$item_subcategory['discount'],
                        'sequence'=>$item_subcategory['sequence'],
                        'status'=>$item_subcategory['status'],
                        'date_created'=>CommonUtility::dateNow(),
                        'ip_address'=>CommonUtility::userIp()
                    ];
                    Yii::app()->db->createCommand("
                        UPDATE {{item_relationship_subcategory}}
                        SET subcat_id=".q($new_subcategory_id)."
                        WHERE merchant_id=".q($new_merchant_id)."
                        AND subcat_id=".$item_subcategory['subcat_id'].";

                        UPDATE {{subcategory_item_relationships}}
                        SET subcat_id=".q($new_subcategory_id)."
                        WHERE merchant_id=".q($new_merchant_id)."
                        AND subcat_id=".$item_subcategory['subcat_id'].";

                        UPDATE {{item_relationship_subcategory_item}}
                        SET subcat_id=".q($new_subcategory_id)."
                        WHERE merchant_id=".q($new_merchant_id)."
                        AND subcat_id=".$item_subcategory['subcat_id'].";

                        UPDATE {{subcategory_translation}}
                        SET subcat_id=".q($new_subcategory_id)."
                        WHERE merchant_id=".q($new_merchant_id)."
                        AND subcat_id=".$item_subcategory['subcat_id'].";
                    ")->query();    
                }
                $command=$builder->createMultipleInsertCommand('{{subcategory}}', $subcategory_data);
                $command->execute();
            }
            
            $stmt = "        
            INSERT INTO {{subcategory_item_translation}} (merchant_id,sub_item_id ,language ,sub_item_name,item_description)
            SELECT ".q($new_merchant_id).",sub_item_id ,language ,sub_item_name,item_description
            FROM {{subcategory_item_translation}}
            where 
            sub_item_id IN (
                select sub_item_id from {{subcategory_item}}
                where merchant_id=".q($original_merchant_id)."
            )
            ";        
            Yii::app()->db->createCommand($stmt)->query();
        }
        
        if(in_array('addon_items',$payload)){
            $subcategory_item_data = [];
            $last_subcategory_item_id = $table->getLastIncrement("{{subcategory_item}}");
            if($subcategory_item = Yii::app()->db->createCommand("SELECT * FROM {{subcategory_item}} WHERE 
                    merchant_id=".q($original_merchant_id)." ")->queryAll()){
                foreach ($subcategory_item as $item_subcategory_item) {
                    $new_subcategory_item_id = $last_subcategory_item_id++;
                    $subcategory_item_data[] = [
                        'sub_item_id'=>$new_subcategory_item_id,
                        'merchant_id'=>$new_merchant_id,
                        'sub_item_name'=>$item_subcategory_item['sub_item_name'],
                        'item_description'=>$item_subcategory_item['item_description'],
                        'price'=>$item_subcategory_item['price'],
                        'photo'=>$item_subcategory_item['photo'],
                        'path'=>$item_subcategory_item['path'],
                        'sequence'=>$item_subcategory_item['sequence'],
                        'status'=>$item_subcategory_item['status'],
                        'date_created'=>CommonUtility::dateNow(),
                        'ip_address'=>CommonUtility::userIp()
                    ];
                    Yii::app()->db->createCommand("                
                        UPDATE {{subcategory_item_relationships}}
                        SET sub_item_id=".q($new_subcategory_item_id)."
                        WHERE merchant_id=".q($new_merchant_id)."
                        AND sub_item_id=".$item_subcategory_item['sub_item_id'].";

                        UPDATE {{item_relationship_subcategory_item}}
                        SET sub_item_id=".q($new_subcategory_item_id)."
                        WHERE merchant_id=".q($new_merchant_id)."
                        AND sub_item_id=".$item_subcategory_item['sub_item_id'].";

                        UPDATE {{subcategory_item_translation}}
                        SET sub_item_id=".q($new_subcategory_item_id)."
                        WHERE merchant_id=".q($new_merchant_id)."
                        AND sub_item_id=".$item_subcategory_item['sub_item_id'].";

                    ")->query();    
                }            
                $command=$builder->createMultipleInsertCommand('{{subcategory_item}}', $subcategory_item_data);
                $command->execute();
            }
        }
        
        if(in_array('cooking_ref',$payload)){

            $stmt = "        
            INSERT INTO {{cooking_ref_translation}} (merchant_id,cook_id,language,cooking_name)
            SELECT ".q($new_merchant_id).",cook_id,language,cooking_name
            FROM {{cooking_ref_translation}}
            where 
            cook_id IN (
                select cook_id from {{cooking_ref}}
                where merchant_id=".q($original_merchant_id)."
            )
            ";        
            Yii::app()->db->createCommand($stmt)->query();

            $cooking_ref_data = [];
            $last_cooking_ref_id = $table->getLastIncrement("{{cooking_ref}}");
            if($cooking_ref = Yii::app()->db->createCommand("SELECT * FROM {{cooking_ref}} WHERE  merchant_id=".q($original_merchant_id)." ")->queryAll()){
                foreach ($cooking_ref as $cooking_ref_item) {
                    $new_cooking_ref_id = $last_cooking_ref_id++;
                    $cooking_ref_data[] = [
                        'cook_id'=>$new_cooking_ref_id,
                        'merchant_id'=>$new_merchant_id,
                        'cooking_name'=>$cooking_ref_item['cooking_name'],
                        'sequence'=>$cooking_ref_item['sequence'],
                        'status'=>$cooking_ref_item['status'],
                        'date_created'=>CommonUtility::dateNow(),
                        'ip_address'=>CommonUtility::userIp()
                    ];
                    Yii::app()->db->createCommand("                
                        UPDATE {{item_meta}}
                        SET meta_id=".q($new_cooking_ref_id)."
                        WHERE merchant_id=".q($new_merchant_id)."
                        AND meta_name='cooking_ref'
                        AND meta_id=".$cooking_ref_item['cook_id'].";                
                    ")->query();    

                    Yii::app()->db->createCommand("
                        UPDATE {{cooking_ref_translation}}
                        SET cook_id=".q($new_cooking_ref_id)."                        
                        WHERE cook_id=".$cooking_ref_item['cook_id']."
                        AND merchant_id=".q($new_merchant_id)."
                    ")->query();    

                }            
                $command=$builder->createMultipleInsertCommand('{{cooking_ref}}', $cooking_ref_data);
                $command->execute();
            }
         }
         
         if(in_array('ingredients',$payload)){

            $stmt = "        
            INSERT INTO {{ingredients_translation}} (merchant_id,ingredients_id,language,ingredients_name)
            SELECT ".q($new_merchant_id).",ingredients_id,language,ingredients_name
            FROM {{ingredients_translation}}
            where 
            ingredients_id IN (
                select ingredients_id from {{ingredients}}
                where merchant_id=".q($original_merchant_id)."
            )
            ";        
            Yii::app()->db->createCommand($stmt)->query();

            $ingredients_data = [];
            $last_ingredients_id = $table->getLastIncrement("{{ingredients}}");
            if($ingredients = Yii::app()->db->createCommand("SELECT * FROM {{ingredients}} WHERE  merchant_id=".q($original_merchant_id)." ")->queryAll()){
                foreach ($ingredients as $ingredients_item) {
                    $new_ingredients_id = $last_ingredients_id++;
                    $ingredients_data[] = [
                        'ingredients_id'=>$new_ingredients_id,
                        'merchant_id'=>$new_merchant_id,
                        'ingredients_name'=>$ingredients_item['ingredients_name'],
                        'sequence'=>$ingredients_item['sequence'],
                        'status'=>$ingredients_item['status'],
                        'date_created'=>CommonUtility::dateNow(),
                        'ip_address'=>CommonUtility::userIp()
                    ];
                    Yii::app()->db->createCommand("                
                        UPDATE {{item_meta}}
                        SET meta_id=".q($new_ingredients_id)."
                        WHERE merchant_id=".q($new_merchant_id)."
                        AND meta_name='ingredients'
                        AND meta_id=".$ingredients_item['ingredients_id'].";                
                    ")->query();    

                    Yii::app()->db->createCommand("
                        UPDATE {{ingredients_translation}}
                        SET ingredients_id=".q($new_ingredients_id)."                        
                        WHERE ingredients_id=".$ingredients_item['ingredients_id']."
                        AND merchant_id=".q($new_merchant_id)."
                    ")->query();    
                }                        
                $command=$builder->createMultipleInsertCommand('{{ingredients}}', $ingredients_data);
                $command->execute();
            }
        }
        
        if(in_array('pages',$payload)){
            $stmt_pages = "        
            INSERT INTO {{pages}} (owner,merchant_id,page_type,slug,title,long_content,short_content,meta_title,meta_description,meta_keywords,meta_image,path,status,date_created,ip_address)
            SELECT owner,".q($new_merchant_id).",page_type,slug,title,long_content,short_content,meta_title,meta_description,meta_keywords,meta_image,path,status,date_created,ip_address
            FROM {{pages}}
            WHERE merchant_id = ".q($original_merchant_id)."
            AND page_type='page'
            ";        
            Yii::app()->db->createCommand($stmt_pages)->query();
        }
        
        if(in_array('menu',$payload)){
            $stmt_pages = "        
            INSERT INTO {{menu}} (menu_type,menu_name,parent_id,meta_value1,link,action_name,sequence,status,visible)
            SELECT menu_type,menu_name,parent_id,".q($new_merchant_id).",link,action_name,sequence,status,visible
            FROM {{menu}}
            WHERE meta_value1 = ".q($original_merchant_id)."
            AND menu_type='website_merchant'
            ";        
            Yii::app()->db->createCommand($stmt_pages)->query();
        }
        
        $stmt_merchant_meta = "        
        INSERT INTO {{merchant_meta}} (merchant_id,meta_name,meta_value,meta_value1,meta_value2,meta_value3,date_modified)
        SELECT ".q($new_merchant_id).",meta_name,meta_value,meta_value1,meta_value2,meta_value3,date_modified
        FROM {{merchant_meta}}
        WHERE merchant_id = ".q($original_merchant_id)."        
        ";        
        Yii::app()->db->createCommand($stmt_merchant_meta)->query();
        
        $zone_data = [];
        $last_zone_id = $table->getLastIncrement("{{zones}}");
        if($merchant->merchant_type==2){
            if($zones = Yii::app()->db->createCommand("SELECT * FROM {{zones}} WHERE  merchant_id=".q($original_merchant_id)." ")->queryAll()){
                foreach ($zones as $zones_items) {
                    $new_zone_id = $last_zone_id++;
                    $zone_data[] = [
                        'zone_id'=>$new_zone_id,
                        'zone_uuid'=>CommonUtility::createUUID("{{zones}}",'zone_uuid'),
                        'merchant_id'=>$new_merchant_id,
                        'zone_name'=>$zones_items['zone_name'],
                        'description'=>$zones_items['description'],
                        'date_created'=>CommonUtility::dateNow(),
                        'ip_address'=>CommonUtility::userIp()
                    ];
                    Yii::app()->db->createCommand("                
                        UPDATE {{merchant_meta}}
                        SET meta_value=".q($new_zone_id)."
                        WHERE merchant_id=".q($new_merchant_id)."
                        AND meta_name='zone'                        
                    ")->query();    
                }                
                $command=$builder->createMultipleInsertCommand('{{zones}}', $zone_data);
                $command->execute();
            }
        }         


        if(in_array('settings',$payload)){
            $stmt_option = "        
            INSERT INTO {{option}} (merchant_id,option_name,option_value,last_update)
            SELECT ".q($new_merchant_id).",option_name,option_value,".q(CommonUtility::dateNow())."
            FROM {{option}}
            WHERE merchant_id = ".q($original_merchant_id)."        
            ";        
            Yii::app()->db->createCommand($stmt_option)->query();

            $stmt = "        
            INSERT INTO {{opening_hours}} (merchant_id,day,day_of_week,status,start_time,end_time,start_time_pm,end_time_pm,custom_text,last_update)
            SELECT ".q($new_merchant_id).",day,day_of_week,status,start_time,end_time,start_time_pm,end_time_pm,custom_text,".q(CommonUtility::dateNow())."
            FROM {{opening_hours}}
            WHERE merchant_id = ".q($original_merchant_id)."        
            ";        
            Yii::app()->db->createCommand($stmt)->query();

            $stmt = "        
            INSERT INTO {{tax}} (tax_uuid,merchant_id,tax_type,tax_name,tax_in_price,tax_rate,tax_rate_type,default_tax,active,date_created,ip_address)
            SELECT uuid(),".q($new_merchant_id).",tax_type,tax_name,tax_in_price,tax_rate,tax_rate_type,default_tax,active,".q(CommonUtility::dateNow()).",".q(CommonUtility::userIp())."
            FROM {{tax}}
            WHERE merchant_id = ".q($original_merchant_id)."        
            ";        
            Yii::app()->db->createCommand($stmt)->query();

        }

        $stmt = "
        INSERT INTO {{merchant_user}} (user_uuid,merchant_id,first_name,last_name,username,password,status,contact_email,session_token,contact_number,main_account,date_created,ip_address)
        SELECT uuid(),".q($new_merchant_id).",first_name,last_name,".q(CommonUtility::generateAplhaCode()).",".q(md5(CommonUtility::generateNumber(5,true))).",status,
        ".q(CommonUtility::generateAplhaCode()."@gmail.com").",".q(CommonUtility::createUUID("{{merchant_user}}",'session_token')).",contact_number,main_account,date_created,ip_address
        FROM {{merchant_user}}
        WHERE merchant_id = ".q($original_merchant_id)."    
        AND main_account=1    
        ";        
        Yii::app()->db->createCommand($stmt)->query();
                

        if(in_array('gallery',$payload)){
            $stmt = "        
            INSERT INTO {{merchant_meta}} (merchant_id,meta_name,meta_value,meta_value1,meta_value2,meta_value3,date_modified)
            SELECT ".q($new_merchant_id).",meta_name,meta_value,meta_value1,meta_value2,meta_value3,".q(CommonUtility::dateNow())."
            FROM {{merchant_meta}}
            WHERE merchant_id = ".q($original_merchant_id)."
            AND meta_name='merchant_gallery'
            ";        
            Yii::app()->db->createCommand($stmt)->query();
        }

        if(in_array('media_library',$payload)){
            $stmt = "        
            INSERT INTO {{media_files}} (upload_uuid,merchant_id,title,filename,path,size,media_type,meta_name,date_created,ip_address)
            SELECT uuid(),".q($new_merchant_id).",title,filename,path,size,media_type,meta_name,date_created,ip_address
            FROM {{media_files}}
            WHERE merchant_id = ".q($original_merchant_id)."            
            ";        
            Yii::app()->db->createCommand($stmt)->query();
        }
        

        $interest = AttributesTools::pushInterest();
        $message = "The duplication process for merchant {{restaurant_name}} is complete. Your new merchant ID is {{new_merchant_id}}. Your duplicated merchant is now ready for use.";
        $message_parameters = [
            '{{restaurant_name}}'=>$model->restaurant_name,
            '{{new_merchant_id}}'=>$new_merchant_id
        ];
        $noti = new AR_notifications;    							
        $noti->notication_channel = Yii::app()->params->realtime['admin_channel'] ;
        $noti->notification_event = Yii::app()->params->realtime['notification_event'] ;
        $noti->notification_type = $interest['order_update'];
        $noti->message = $message;				
        $noti->message_parameters = json_encode($message_parameters);                    
        $noti->image_type = 'icon';
        $noti->image = 'zmdi zmdi-alert-circle-o';  
        $noti->save();

        CCacheData::add();
            
    }
   
}
// end script