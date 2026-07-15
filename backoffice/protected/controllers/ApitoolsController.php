<?php
class ApitoolsController extends CommonApi
{			

	public function beforeAction($action)
	{								
		$method = Yii::app()->getRequest()->getRequestType();
		if($method=="PUT"){
			$this->data = Yii::app()->input->xssClean(json_decode(file_get_contents('php://input'), true));
		} else $this->data = Yii::app()->input->xssClean($_POST);				
		return true;
	}

	public function actiongetMigrationAttr()
	{
		try {

			// $data_table = Yii::app()->db->schema->getTableNames();
			// $data_table = CMigrationTools::RemoveOwnTableFromList($data_table);			
            // $data_table = CommonUtility::ArrayToValue($data_table);        
			// $data_table = CommonUtility::ArrayToLabelValue($data_table);        

			$data_type = CMigrationTools::DataType();
			$data_type = CommonUtility::ArrayToLabelValue($data_type);

			$this->code = 1;
			$this->msg = "Ok";
			$this->details = [
				'data_type'=>$data_type,
				'data_table'=>[]
			];
		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}		
		$this->responseJson();
	}

	public function actionImportData()
	{
		try {
			
			$data_type = Yii::app()->input->post('data_type');
			//$data_table = Yii::app()->input->post('data_table');
			$table_prefix = Yii::app()->input->post('table_prefix');

			$builder=Yii::app()->db->schema->commandBuilder;

			$services = AttributesTools::ListSelectServices();			
			
			switch ($data_type) {
				case 'merchant':																																		
					try {

						CMigrationTools::CheckTableExist([
							$table_prefix."_merchant",                            
						]);

						Yii::app()->db->createCommand()->truncateTable("{{merchant}}");		
						Yii::app()->db->createCommand()->truncateTable("{{merchant_commission_order}}");
						Yii::app()->db->createCommand()->truncateTable("{{merchant_user}}");
						Yii::app()->db->createCommand()->truncateTable("{{cuisine_merchant}}");						
						Yii::app()->db->createCommand()->truncateTable("{{merchant_meta}}");	
                        Yii::app()->db->createCommand()->truncateTable("{{opening_hours}}");	

                        $stmt = "
                        INSERT INTO {{merchant}} (
                            merchant_id,
                            merchant_uuid,
                            restaurant_slug,
                            restaurant_name,
                            restaurant_phone,
                            contact_name,
                            contact_phone,
                            contact_email,
                            address,
                            status,
                            is_featured,
                            is_ready,
                            latitude,
                            lontitude,
                            logo,
                            path,
                            merchant_type,
                            invoice_terms,
                            distance_unit,
                            delivery_distance_covered,
                            close_store
                        )
                        SELECT 
                        merchant_id,
                        uuid(),
                        restaurant_slug,
                        restaurant_name,
                        restaurant_phone,
                        contact_name,
                        contact_phone,
                        contact_email,
                        concat(street,' ',city,' ',state,' ',post_code,' ',country_code),
                        status,
                        is_featured,
                        is_ready,
                        latitude,
                        lontitude,
                        logo,
                        'upload',
                        merchant_type,
                        invoice_terms,
                        distance_unit,
                        delivery_distance_covered,
                        close_store					
                        FROM ".$table_prefix."_merchant		
                        ";					

                        $stmt_user = "
                        INSERT INTO {{merchant_user}} (
                            user_uuid,
                            merchant_id,
                            first_name,
                            contact_email,
                            username,
                            password,
                            main_account
                        )
                        SELECT 
                        uuid(),
                        merchant_id,
                        contact_name,
                        contact_email,
                        username,
                        password,
                        1
                        FROM ".$table_prefix."_merchant
                        ";			

						Yii::app()->db->createCommand($stmt)->query();													
						Yii::app()->db->createCommand($stmt_user)->query();	

						// UPDATE MERCHANT TYPE
						$stmt_cmt = "
						UPDATE {{merchant}}
						SET merchant_type=2 
						WHERE
						merchant_type=3
						";
						Yii::app()->db->createCommand($stmt_cmt)->query();	


                        // OPENING HOURS
                        $stmt = "
                        INSERT INTO {{opening_hours}} (
                            merchant_id,
                            day,
                            status,
                            start_time,
                            end_time
                        )
                        SELECT
                        merchant_id,
                        day,
                        status,
                        start_time,
                        end_time

                        FROM ".$table_prefix."_opening_hours
                        ";							
                        Yii::app()->db->createCommand($stmt)->query();
                        $days = CMigrationTools::OpeningDays();
                        foreach ($days as $numberDays => $dayName) {
                            Yii::app()->db->createCommand("
                            UPDATE {{opening_hours}}
                            SET day_of_week=".q(intval($numberDays))." WHERE day=".q($dayName)."
                            ")->query();
                        }		


						// PAYMENT GATEWAY
						$payment_list = CPayments::List();						

						// COMMISSION // CUISINE
						$stmt = "
						SELECT merchant_id,commision_type,percent_commision,cuisine,service
						FROM ".$table_prefix."_merchant
						";
						if($res = Yii::app()->db->createCommand($stmt)->queryAll()){							
							$data = []; $cuisine_data = []; $service_data = []; $payment_gateway = [];
							foreach ($res as $items) {			
								$cuisine = !empty($items['cuisine'])?json_decode($items['cuisine'],true):false;
								
								$merchant_services = CMigrationTools::getMerchantServices($items['service']);																
								
								if(is_array($cuisine) && count($cuisine)>=1){
									foreach ($cuisine as $cuisine_id) {
										$cuisine_data[] = [
											'merchant_id'=>$items['merchant_id'],
											'cuisine_id'=>intval($cuisine_id)
										];
									}
								}

								foreach ($services as $service=>$itemservice) {
									$data[] = [
										'merchant_id'=>$items['merchant_id'],
										'transaction_type'=>$service,
										'commission_type'=>$items['commision_type'],
										'commission'=>$items['percent_commision']
									];
								}								

								if(is_array($merchant_services) && count($merchant_services)>=1){
									foreach ($merchant_services as $service_code) {
										$service_data[] = [
											'merchant_id'=>$items['merchant_id'],
											'meta_name'=>'services',
											'meta_value'=>$service_code
										];
									}
								}

								if(is_array($payment_list) && count($payment_list)>=1){
									foreach ($payment_list as $item_payment) {
										$payment_gateway[] = [
											'merchant_id'=>$items['merchant_id'],
											'meta_name'=>"payment_gateway",
											'meta_value'=>$item_payment['description']											
										];
									}
								}

							}
							// END LOOP

							if(is_array($data) && count($data)>=1){								
								$command=$builder->createMultipleInsertCommand("{{merchant_commission_order}}",$data);
								$command->execute();
							}			

							if(is_array($cuisine_data) && count($cuisine_data)>=1){
								$command=$builder->createMultipleInsertCommand("{{cuisine_merchant}}",$cuisine_data);
								$command->execute();
							}		
											
							if(is_array($service_data) && count($service_data)>=1){
								$command=$builder->createMultipleInsertCommand("{{merchant_meta}}",$service_data);
								$command->execute();
							}		

							if(is_array($payment_gateway) && count($payment_gateway)>=1){								
								$command=$builder->createMultipleInsertCommand("{{merchant_meta}}",$payment_gateway);
								$command->execute();
							}

						}
						// END IF

						$this->code = 1; $this->msg = t("Successful");
					} catch (Exception $e) {
						$this->msg = $e->getMessage();
					}
					break;			
									
					case "customer":
						try {

							CMigrationTools::CheckTableExist([
								$table_prefix."_client",                            
							]);

							Yii::app()->db->createCommand()->truncateTable("{{client}}");	

							$stmt = "
							INSERT INTO {{client}} (
								client_id,
								client_uuid,
								social_strategy,
								first_name,
								last_name,
								email_address,
								password,
								contact_phone,
								avatar,
								path,
								status,
								social_id
							)

							SELECT
							client_id,
							uuid(),
							social_strategy,
							first_name,
							last_name,
							email_address,
							password,
							contact_phone,
							avatar,
							'upload',
							status,
							social_id

							FROM ".$table_prefix."_client
							";				

							Yii::app()->db->createCommand($stmt)->query();
							$this->code = 1; $this->msg = t("Successful");	

						} catch (Exception $e) {
							$this->msg = $e->getMessage();
						}		
						break;

					case "food_size":

						try {

							CMigrationTools::CheckTableExist([
								$table_prefix."_size", 
								$table_prefix."_size_translation", 
							]);

							Yii::app()->db->createCommand()->truncateTable("{{size}}");	
                            Yii::app()->db->createCommand()->truncateTable("{{size_translation}}");	

							$stmt = "
							INSERT INTO {{size}} (
								size_id,
								merchant_id,
								size_name,
								sequence,
								status
							)
							SELECT 
							size_id,
							merchant_id,
							size_name,
							sequence,
							status
							FROM ".$table_prefix."_size
							";
							Yii::app()->db->createCommand($stmt)->query();

                            $stmt = "
                            INSERT INTO {{size_translation}} (
                                size_id,
                                language,
                                size_name
                            )

                            SELECT 
                            size_id,
                            language,
                            size_name
                            FROM ".$table_prefix."_size_translation
                            ";
                            Yii::app()->db->createCommand($stmt)->query();
                            
							$this->code = 1; $this->msg = t("Successful");	
						} catch (Exception $e) {
							$this->msg = $e->getMessage();
						}		
						break;					
					
					case "food_ingredients":
						try {

							CMigrationTools::CheckTableExist([
								$table_prefix."_ingredients",
								$table_prefix."_ingredients_translation",
							]);

							Yii::app()->db->createCommand()->truncateTable("{{ingredients}}");	
                            Yii::app()->db->createCommand()->truncateTable("{{ingredients_translation}}");	
							$stmt = "
							INSERT INTO {{ingredients}} (
								ingredients_id,
								merchant_id,
								ingredients_name,
								sequence,
								status
							)
							SELECT 
							ingredients_id,
							merchant_id,
							ingredients_name,
							sequence,
							status
							FROM ".$table_prefix."_ingredients
							";
							Yii::app()->db->createCommand($stmt)->query();

                            $stmt = "
							INSERT INTO {{ingredients_translation}} (
								ingredients_id,
								language,
								ingredients_name
							)
							SELECT 
							ingredients_id,
							language,
							ingredients_name							
							FROM ".$table_prefix."_ingredients_translation
							";
							Yii::app()->db->createCommand($stmt)->query();

							$this->code = 1; $this->msg = t("Successful");	
						} catch (Exception $e) {
							$this->msg = $e->getMessage();
						}		
						break;																										

					case "cooking_ref":

						try {

							CMigrationTools::CheckTableExist([
								$table_prefix."_cooking_ref",                            
								$table_prefix."_cooking_ref_translation", 
							]);

							Yii::app()->db->createCommand()->truncateTable("{{cooking_ref}}");	
                            Yii::app()->db->createCommand()->truncateTable("{{cooking_ref_translation}}");	
							$stmt = "
							INSERT INTO {{cooking_ref}} (
								cook_id,
								merchant_id,
								cooking_name,
								sequence,
								status
							)
							SELECT 
							cook_id,
							merchant_id,
							cooking_name,
							sequence,
							status							
							FROM ".$table_prefix."_cooking_ref
							";
							Yii::app()->db->createCommand($stmt)->query();

                            $stmt = "
							INSERT INTO {{cooking_ref_translation}} (
								cook_id,
								language,
								cooking_name
							)
							SELECT 
							cook_id,
							language,
							cooking_name							
							FROM ".$table_prefix."_cooking_ref_translation
							";
							Yii::app()->db->createCommand($stmt)->query();

							Yii::app()->db->createCommand("
							UPDATE {{cooking_ref_translation}}
							SET language=".q(KMRS_DEFAULT_LANGUAGE)."
							WHERE 
							language='default'
							")->query();

							Yii::app()->db->createCommand("
							DELETE FROM {{cooking_ref_translation}}
							WHERE
							cooking_name=''
							")->query();

							$this->code = 1; $this->msg = t("Successful");	
						} catch (Exception $e) {
							$this->msg = $e->getMessage();
						}		
						break;

					case "category":
						try {

							CMigrationTools::CheckTableExist([
								$table_prefix."_category", 
								$table_prefix."_category_translation", 
							]);

							Yii::app()->db->createCommand()->truncateTable("{{category}}");	
                            Yii::app()->db->createCommand()->truncateTable("{{category_translation}}");	
							$stmt = "
							INSERT INTO {{category}} (
								cat_id,
								merchant_id,
								category_name,
								category_description,
								photo,
								path,
								status,
								sequence														
							)
							SELECT 
							cat_id,
							merchant_id,
							category_name,
							category_description,
							photo,
							'upload',
							status,
							sequence							
							FROM ".$table_prefix."_category
							";
							Yii::app()->db->createCommand($stmt)->query();

                            $stmt = "
							INSERT INTO {{category_translation}} (
								cat_id,
								language,
								category_name,
								category_description
							)
							SELECT 
							cat_id,
							language,
							category_name,
							category_description
							FROM ".$table_prefix."_category_translation
							";
							Yii::app()->db->createCommand($stmt)->query();

							$this->code = 1; $this->msg = t("Successful");	
						} catch (Exception $e) {
							$this->msg = $e->getMessage();
						}		
						break;										
	
					case "subcategory":
						try {

							CMigrationTools::CheckTableExist([
								$table_prefix."_subcategory",                            
								$table_prefix."_subcategory_translation",
							]);

							Yii::app()->db->createCommand()->truncateTable("{{subcategory}}");	
                            Yii::app()->db->createCommand()->truncateTable("{{subcategory_translation}}");	
							$stmt = "
							INSERT INTO {{subcategory}} (
								subcat_id,
								merchant_id,
								subcategory_name,
								subcategory_description
							)
							SELECT 
							subcat_id,
							merchant_id,
							subcategory_name,
							subcategory_description
							FROM ".$table_prefix."_subcategory
							";
							Yii::app()->db->createCommand($stmt)->query();

                            $stmt = "
							INSERT INTO {{subcategory_translation}} (
								subcat_id,
								language,								
								subcategory_name,
								subcategory_description
							)
							SELECT 
							subcat_id,
							language,							
							subcategory_name,
							subcategory_description
							FROM ".$table_prefix."_subcategory_translation
							";                            
							Yii::app()->db->createCommand($stmt)->query();

							$this->code = 1; $this->msg = t("Successful");	
						} catch (Exception $e) {
							$this->msg = $e->getMessage();
						}		
						break;											
						
					case "subcategory_item":
						try {

							CMigrationTools::CheckTableExist([
								$table_prefix."_subcategory_item",                            
								$table_prefix."_subcategory_item_translation",
							]);

							Yii::app()->db->createCommand()->truncateTable("{{subcategory_item}}");	
                            Yii::app()->db->createCommand()->truncateTable("{{subcategory_item_translation}}");	
                            Yii::app()->db->createCommand()->truncateTable("{{subcategory_item_relationships}}");	
							$stmt = "
							INSERT INTO {{subcategory_item}} (
								sub_item_id,
								merchant_id,
								sub_item_name,
								item_description,
								price,
								photo,
								path,
								sequence,
								status
							)
							SELECT 
							sub_item_id,
							merchant_id,
							sub_item_name,
							item_description,
							price,
							photo,
							'upload',
							sequence,
							status
							FROM ".$table_prefix."_subcategory_item
							";
							Yii::app()->db->createCommand($stmt)->query();

                            $stmt = "
							INSERT INTO {{subcategory_item_translation}} (
								sub_item_id,
								language,
								sub_item_name,
								item_description
							)
							SELECT 
							sub_item_id,
							language,
							sub_item_name,
							item_description
							FROM ".$table_prefix."_subcategory_item_translation
							";
							Yii::app()->db->createCommand($stmt)->query();

                            $stmt = "
							INSERT INTO {{subcategory_item_relationships}} (
								subcat_id,
                                sub_item_id
							)
							SELECT 
							subcat_id,
                            sub_item_id
							FROM ".$table_prefix."_subcategory_item_relationships
							";
							Yii::app()->db->createCommand($stmt)->query();

							$this->code = 1; $this->msg = t("Successful");	
						} catch (Exception $e) {
							$this->msg = $e->getMessage();
						}		
						break;			
											
					case "item":
						try {

                            CMigrationTools::CheckTableExist([
                                $table_prefix."_item",
                                $table_prefix."_item_relationship_category",
                                $table_prefix."_item_translation",
                                $table_prefix."_item_relationship_size",
								$table_prefix."_item_relationship_subcategory_item",
                            ]);

							Yii::app()->db->createCommand()->truncateTable("{{item}}");		
							Yii::app()->db->createCommand()->truncateTable("{{item_relationship_category}}");		
							Yii::app()->db->createCommand()->truncateTable("{{item_translation}}");
                            Yii::app()->db->createCommand()->truncateTable("{{item_relationship_size}}");
							Yii::app()->db->createCommand()->truncateTable("{{item_relationship_subcategory_item}}");

                            // FOOD ITEM
							$stmt = "
							INSERT INTO {{item}} (
								item_id,
								merchant_id,
								item_name,	
								slug,									
								item_description,	
                                item_short_description,					
								status,
								photo,
								path,
								sequence,
								item_token
							)
							SELECT 
							item_id,
							merchant_id,
							item_name,															
							LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(item_name), ':', ''), ')', ''), '(', ''), ',', ''), ".q("\\").", ''), '\/', ''), '\"', ''), '?', ''), '\'', ''), '&', ''), '!', ''), '.', ''), ' ', '-'), '--', '-'), '--', '-')) AS slug,
							item_description,							
                            substring(item_description,1,255) as item_short_description,
							status,
							photo,
							'upload',
							sequence,
							item_token
							FROM ".$table_prefix."_item
							";							
							Yii::app()->db->createCommand($stmt)->query();
												
                            // RELATIONSHIP CATEGORY
							Yii::app()->db->createCommand("
                            INSERT INTO {{item_relationship_category}} (
								merchant_id,
								item_id,
								cat_id
							)
							SELECT 
							merchant_id,
							item_id,
							cat_id
							FROM ".$table_prefix."_item_relationship_category
                            ")->query();	
							
                            // ITEM TRANSLATION
							Yii::app()->db->createCommand("
                            INSERT INTO {{item_translation}} (
								item_id,
								language,
								item_name,
								item_description
							)
							SELECT 
							item_id,
							language,
							item_name,
							item_description
							FROM ".$table_prefix."_item_translation
                            ")->query();				
                            
                            
                            // ITEM RELATIONSHIP SIZE
                            Yii::app()->db->createCommand("
                            INSERT INTO {{item_relationship_size}} (
								item_size_id,
                                merchant_id,
                                item_token,
                                item_id,
                                size_id,
                                price
							)
							SELECT 
							item_size_id,
                            merchant_id,
                            item_token,
                            item_id,
                            size_id,
                            price
							FROM ".$table_prefix."_item_relationship_size
                            ")->query();


							// ITEM RELATIONSHIP SUBCATEGORY ITEM
							Yii::app()->db->createCommand("
                            INSERT INTO {{item_relationship_subcategory_item}} (
								id,
								merchant_id,
								item_id,
								subcat_id,
								sub_item_id
							)
							SELECT 
							id,
							merchant_id,
							item_id,
							subcat_id,
							sub_item_id
							FROM ".$table_prefix."_item_relationship_subcategory_item
                            ")->query();

							$this->code = 1; $this->msg = t("Successful");						
						} catch (Exception $e) {
							$this->msg = $e->getMessage();
						}		
						break;

                    case "item_addon":
						try { 

							CMigrationTools::CheckTableExist([
								$table_prefix."_item_relationship_subcategory",                            
							]);

							Yii::app()->db->createCommand()->truncateTable("{{item_relationship_subcategory}}");

							$stmt="
							INSERT INTO {{item_relationship_subcategory}} (                            
								merchant_id,
								item_id,
								item_size_id,
								subcat_id                            
							)
							SELECT 
							b.merchant_id,
							b.item_id,
							a.item_size_id,
							b.subcat_id

							FROM {{item_relationship_size}} a
							LEFT JOIN (
								SELECT 
								merchant_id,item_id,subcat_id 
								FROM ".$table_prefix."_item_relationship_subcategory
							) b 
							ON a.item_id = b.item_id
							WHERE a.merchant_id = b.merchant_id
							";              						
							Yii::app()->db->createCommand($stmt)->query();		
							
							// UPDATE ADDON MULTI OPTIONS AND REQUIRED
							$stmt_item = "
							SELECT merchant_id,item_id,multi_option,multi_option_value,require_addon,gallery_photo
							FROM ".$table_prefix."_item
							";
							if($res = Yii::app()->db->createCommand($stmt_item)->queryAll()){
								//$gallery_photo_params = [];
								foreach ($res as $items) {
									
									$item_id = $items['item_id'];
									$merchant_id = $items['merchant_id'];
									$multi_option_data = !empty($items['multi_option'])?json_decode($items['multi_option'],true):false;								
									$multi_option_value_data = !empty($items['multi_option_value'])?json_decode($items['multi_option_value'],true):false;								
									$require_addon_data = !empty($items['require_addon'])?json_decode($items['require_addon'],true):false;
									//$gallery_photo_data = !empty($items['gallery_photo'])?json_decode($items['gallery_photo'],true):false;
									

									// if(is_array($gallery_photo_data) && count($gallery_photo_data)>=1){
									// 	foreach ($gallery_photo_data as $gallery_photo) {
									// 		$gallery_photo_params[] = [
									// 			'merchant_id'=>$merchant_id,
									// 			'item_id'=>$item_id,
									// 			'meta_name'=>'item_gallery',
									// 			'meta_id'=>$gallery_photo,
									// 			'meta_value'=>"upload"
									// 		];
									// 	}
									// }
									
									if(is_array($multi_option_data) && count($multi_option_data)>=1){
										foreach ($multi_option_data as $subcat_id => $subitems) {
											$multi_option = isset($subitems[0])?$subitems[0]:'one';
											$multi_option_value = isset($multi_option_value_data[$subcat_id])? ( isset($multi_option_value_data[$subcat_id][0])?$multi_option_value_data[$subcat_id][0]:'' ) :'';										
											$require_addon = isset($require_addon_data[$subcat_id])? ( isset($require_addon_data[$subcat_id][0])?$require_addon_data[$subcat_id][0]:0 ) :0;										
											$require_addon = $require_addon==2?1:0;

											// dump("item_id => $item_id");
											// dump("subcat_id => $subcat_id");
											// dump("multi_option => $multi_option");
											// dump("multi_option_value => $multi_option_value");
											// dump("require_addon => $require_addon");
											
											$stmtupdate = "
											UPDATE {{item_relationship_subcategory}}
											SET multi_option=".Yii::app()->db->quoteValue($multi_option).",
											multi_option_value=".Yii::app()->db->quoteValue($multi_option_value).",
											require_addon=".q(intval($require_addon))."
											WHERE 
											merchant_id=".q($merchant_id)."
											AND
											item_id = ".q($item_id)."
											AND
											subcat_id=".q($subcat_id)."
											";
											//dump($stmtupdate);
											Yii::app()->db->createCommand($stmtupdate)->query();	
										}
									}
								}

								// if(is_array($gallery_photo_params) && count($gallery_photo_params)>=1){		
								// 	Yii::app()->db->createCommand("
								// 	DELETE FROM {{item_meta}}
								// 	WHERE meta_name='item_gallery'
								// 	")->query();								
								// 	$command=$builder->createMultipleInsertCommand("{{item_meta}}",$gallery_photo_params);
								// 	$command->execute();
								// }
							}							
							

							$this->code = 1; $this->msg = t("Successful");

						} catch (Exception $e) {
							$this->msg = $e->getMessage();
						}	
                        break;


					case "item_attributes":
						try {

							CMigrationTools::CheckTableExist([
								$table_prefix."_item_meta",                            
							]);

							Yii::app()->db->createCommand()->truncateTable("{{item_meta}}");

							Yii::app()->db->createCommand("
                            INSERT INTO {{item_meta}} (
								merchant_id,
								item_id,
								meta_name,
								meta_id								
							)
							SELECT 
							merchant_id,
							item_id,
							meta_name,
							meta_id
							FROM ".$table_prefix."_item_meta
                            ")->query();


							Yii::app()->db->createCommand("
							UPDATE {{item_meta}}
							SET meta_name='delivery_options'
							WHERE
							meta_name='delivery_vehicle'
							")->query();	

							
							$this->code = 1; $this->msg = t("Successful");
							
						} catch (Exception $e) {
							$this->msg = $e->getMessage();
						}	
						break;

					default:
					     $this->msg = t("Invalid Data Type");
					   break;					
			}
			// END SWITCH

		} catch (Exception $e) {
		    $this->msg = t($e->getMessage());		   
		}		
		$this->responseJson();
	}    

}
// end class