<?php
class CScrapmenu
{    
    public function Duplicateitem($merchant_id=0, $item_id=0)
    {
        $builder = Yii::app()->db->schema->commandBuilder;
        $table  = new TableDataStatus();
        
        $data = []; 
        $meta_data = [];         
        $irc_data=[];
        $irs_data=[];
        $irsub_data = [];
        $irsi_data = [];
        $availability_data = [];
        $item_promo_data = [];
        $item_translation_data = [];

        $last_item_id = $table->getLastIncrement("{{item}}");          
        $last_item_relationship_size_id = $table->getLastIncrement("{{item_relationship_size}}");
                
        if($items = Yii::app()->db->createCommand("SELECT * FROM {{item}} WHERE merchant_id=".q($merchant_id)."  AND item_id=".q($item_id)." ")->queryRow()){
            $new_item_id = $last_item_id++;
            $data[] = [
                'item_id'=>$new_item_id,
                'merchant_id'=>$merchant_id,
                'item_name'=>$items['item_name'],
                'slug'=>AttributesTools::createSlug(CommonUtility::toSeoURL($items['item_name']),"{{item}}"),
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
                        'merchant_id'=>$merchant_id,
                        'item_id'=>$new_item_id,
                        'language'=>$item_translation_items['language'],
                        'item_name'=>$item_translation_items['item_name'],
                        'item_description'=>$item_translation_items['item_description'],
                        'item_short_description'=>$item_translation_items['item_short_description'],
                    ];
                }
            }

            if($meta = Yii::app()->db->createCommand("SELECT * FROM {{item_meta}} WHERE 
                merchant_id=".q($merchant_id)." AND item_id=".q($items['item_id'])." ")->queryAll()){
                foreach ($meta as $item_meta) {
                    $meta_data[] = [
                        'merchant_id'=>$merchant_id,
                        'item_id'=>$new_item_id,
                        'meta_name'=>$item_meta['meta_name'],
                        'meta_id'=>$item_meta['meta_id'],
                        'meta_value'=>$item_meta['meta_value'],
                    ];
                }
            }

            if($irc = Yii::app()->db->createCommand("SELECT * FROM {{item_relationship_category}} WHERE 
                merchant_id=".q($merchant_id)." AND item_id=".q($items['item_id'])." ")->queryAll()){ 
                foreach ($irc as $item_irc) {                        
                    $irc_data[] = [
                        'merchant_id'=>$merchant_id,
                        'item_id'=>$new_item_id,
                        'cat_id'=>$item_irc['cat_id']
                    ];
                }
            }

            if($irs = Yii::app()->db->createCommand("SELECT * FROM {{item_relationship_size}} WHERE 
                merchant_id=".q($merchant_id)." AND item_id=".q($items['item_id'])." ")->queryAll()){ 
                foreach ($irs as $item_irs) {
                    $new_item_relationship_size_id = $last_item_relationship_size_id++;
                    $irs_data[] = [
                        'item_size_id'=>$new_item_relationship_size_id,
                        'merchant_id'=>$merchant_id,
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
                    merchant_id=".q($merchant_id)." AND item_id=".q($items['item_id'])." 
                    AND item_size_id=".q($item_irs['item_size_id'])." ")->queryAll()){                             
                        foreach ($irsub as $item_irsub) {
                            $irsub_data[] = [
                                'merchant_id'=>$merchant_id,
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
                merchant_id=".q($merchant_id)." AND item_id=".q($items['item_id'])." ")->queryAll()){ 
                foreach ($irsi as $item_irsi) {
                    $irsi_data[] = [
                        'merchant_id'=>$merchant_id,
                        'item_id'=>$new_item_id,
                        'subcat_id'=>$item_irsi['subcat_id'],
                        'sub_item_id'=>$item_irsi['sub_item_id'],
                    ];
                }
            }

            if($availability = Yii::app()->db->createCommand("SELECT * FROM {{availability}} WHERE 
                merchant_id=".q($merchant_id)." AND meta_value=".q($items['item_id'])." AND meta_name='item' ")->queryAll()){ 
                foreach ($availability as $availability_items) {
                    $availability_data[] = [
                        'merchant_id'=>$merchant_id,
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
                merchant_id=".q($merchant_id)." AND item_id=".q($items['item_id'])." ")->queryAll()){ 
                foreach ($item_promo as $item_promo_items) {
                    $item_promo_data[] = [
                        'merchant_id'=>$merchant_id,
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
            
        }
        // end if items
        
        // CACHE
        CCacheData::add();
    }        

}
// end class