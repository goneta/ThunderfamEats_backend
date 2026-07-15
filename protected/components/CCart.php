<?php
class CCart
{	
	private static $content;		
	private static $condition;
	private static $items=array();
	private static $summary=array();
	private static $packaging_fee=0;
	private static $tax_condition;	
	private static $tax_type;	
	private static $tax_group=array();
	private static $tax_total;	
	private static $exchange_rate;	
	private static $admin_exchange_rate;
	const CONDITION_NAME = array('promo');
	const CONDITION_RM = array('promo','promo_type','promo_id');

	private static $points_enabled;	
	private static $points_earning_rule;	
	private static $points_earning_points;	
	private static $points_minimum_purchase;	
	private static $points_maximum_purchase;	
	private static $total_points_item;
	private static $total_preparation_time;

	private static $payload=array();
	private static $cart_merchant_id;
	private static $transactionType;
	
	private static $total_original;
	
	public static function setPayload($data=''){
		self::$payload = $data;
	}

	public static function getPayload(){
		return self::$payload;
	}

	public static function setTransactionType($data=''){
		self::$transactionType = $data;
	}

	public static function getTransactionType($data=''){
		return !empty(self::$transactionType)? self::$transactionType : null;
	}

	public static function setOriginaltotal($value=0){
		self::$total_original = floatval($value);
	}

	public static function getOriginaltotal(){
		return floatval(self::$total_original);
	}

	public static function getMerchantId($cart_uuid='')
	{				
		$dependency = CCacheData::dependency();
		$model = AR_cart::model()->cache( Yii::app()->params->cache , $dependency  )->find('cart_uuid=:cart_uuid',array(':cart_uuid'=>$cart_uuid)); 
		if($model){
			return intval($model->merchant_id);
		}
		throw new Exception( 'no results' );
	}
	
	public static function getMerchantForCredentials($cart_uuid='')
	{
		$stmt="
		SELECT a.merchant_id,
		b.merchant_type
		FROM {{cart}} a
		LEFT JOIN {{merchant}} b
		ON
		a.merchant_id = b.merchant_id
		WHERE 
		a.cart_uuid = ".q($cart_uuid)."
		";
		if ($res = CCacheData::queryRow($stmt)){
			return $res;
		}
		throw new Exception( 'no results' );
	}
		
	public static function add($data = array() )
	{		
		if(is_array($data) && count($data)>=1){				
			if( $results = CCart::find($data) ){																
				$qty = isset($results['qty'])?$results['qty']:0;								
				if($qty<=0){
					CCart::remove($results['cart_uuid'],$results['cart_row']);
				} else {							
					CCart::update($results['cart_uuid'],$results['cart_row'],$results['qty'] , isset($results['addons'])?$results['addons']:'' );					
				}				
				return true;				
			} else {				
				$cart_id = $data['cart_id'] ?? null;
				$items = new AR_cart;
				if($cart_id){
					$items->id = intval($cart_id);
				}
				$items->cart_row = isset($data['cart_row'])?$data['cart_row']:'';
				$items->cart_uuid = isset($data['cart_uuid'])?$data['cart_uuid']:'';
				$items->merchant_id = isset($data['merchant_id'])?(integer)$data['merchant_id']:'';
				$items->cat_id = isset($data['cat_id'])?(integer)$data['cat_id']:'';
				$items->item_token = isset($data['item_token'])?$data['item_token']:'';
				$items->item_size_id = isset($data['item_size_id'])?(integer)$data['item_size_id']:'';
				$items->qty = isset($data['qty'])?(integer)$data['qty']:'';
				$items->special_instructions = isset($data['special_instructions'])?$data['special_instructions']:'';
				$items->if_sold_out = isset($data['if_sold_out'])?$data['if_sold_out']:'';
				$items->transaction_type = isset($data['transaction_type'])?$data['transaction_type']:'';				

				if(!$items->save()){
					throw new Exception( 'error cannot insert records to cart' );
				}				
				
				$builder=Yii::app()->db->schema->commandBuilder;
				
				// addon
				$item_addons = array();
				$addons = isset($data['addons'])?$data['addons']:'';
				if(is_array($addons) && count($addons)>=1){
					foreach ($addons as $item) {					
						$item_addons[] = array(
						 'cart_row'=>isset($item['cart_row'])?$item['cart_row']:'',
						 'cart_uuid'=>isset($item['cart_uuid'])?$item['cart_uuid']:'',
						 'subcat_id'=>isset($item['subcat_id'])?(integer)$item['subcat_id']:0,
						 'sub_item_id'=>isset($item['sub_item_id'])?(integer)$item['sub_item_id']:0,
						 'qty'=>isset($item['qty'])?(integer)$item['qty']:0,
						 'multi_option'=>isset($item['multi_option'])?$item['multi_option']:'',
						 'created_at'=>CommonUtility::dateNow()
						);
					}				
					$command=$builder->createMultipleInsertCommand('{{cart_addons}}',$item_addons);
					$command->execute();
				}
				
				// attributes
				$item_attributes = array();
				$attributes = isset($data['attributes'])?$data['attributes']:'';
				if(is_array($attributes) && count($attributes)>=1){
					foreach ($attributes as $item) {					
						$item_attributes[] = array(
						 'cart_row'=>isset($item['cart_row'])?$item['cart_row']:'',
						 'cart_uuid'=>isset($item['cart_uuid'])?$item['cart_uuid']:'',
						 'meta_name'=>isset($item['meta_name'])?$item['meta_name']:'',
						 'meta_id'=>isset($item['meta_id'])?(integer)$item['meta_id']:'',
						);
					}				
					$command=$builder->createMultipleInsertCommand('{{cart_attributes}}',$item_attributes);
					$command->execute();
				}
			    return true;
			}
		} 
		throw new Exception( 'invalid data' );
	}
	
	public static function update($cart_uuid='',$cart_row='',$qty=0, $addons=array())
	{		
		$cart = AR_cart::model()->find('cart_uuid=:cart_uuid AND cart_row=:cart_row', 
		array(':cart_uuid'=>$cart_uuid, ':cart_row'=>$cart_row ));			
		if($cart){			
			$cart->scenario = "update_qty";
			$cart->qty = intval($qty);
			$cart->update();
						
			if(is_array($addons) && count($addons)>=1){
				foreach ($addons as $val) {
					$stmt="UPDATE {{cart_addons}}			
					SET qty =".q( intval($val['qty']) )."
					WHERE id=".q( intval($val['id']) )."					
					";
					Yii::app()->db->createCommand($stmt)->query();
				}
			}
			
			return true;
		}
		throw new Exception( 'row not found' );
	}
	
	public static function updateAddon($id='', $qty=0)
	{
		$stmt="
		UPDATE {{cart_addons}}
		SET qty=".q(intval($qty))."
		WHERE id=".q(intval($id))."
		";
		Yii::app()->db->createCommand($stmt)->query();
	}
	
	protected static function find( $data=array() )
	{				
		
		$merchant_id = isset($data['merchant_id'])?(integer)$data['merchant_id']:'';		
		$cart_uuid = isset($data['cart_uuid'])?$data['cart_uuid']:'';
		$cat_id = isset($data['cat_id'])?(integer)$data['cat_id']:'';
		$item_token = isset($data['item_token'])?$data['item_token']:'';
		$item_size_id = isset($data['item_size_id'])?(integer)$data['item_size_id']:'';	
		$item_qty = isset($data['qty'])?(integer)$data['qty']:'';			
		$inline_qty = isset($data['inline_qty'])?(integer)$data['inline_qty']:'';		
		$special_instructions = isset($data['special_instructions'])?$data['special_instructions']:'';
		
		$addons = array(); $attributes = array();
		if(is_array($data['addons']) && count($data['addons'])>=1 ){
			foreach ($data['addons'] as $add_on) {				
				$addons[]=array(
				  'subcat_id'=>$add_on['subcat_id'],
				  'sub_item_id'=>$add_on['sub_item_id'],
				  'qty'=>$add_on['qty'],
				);
			}
		}
		
		if(is_array($data['attributes']) && count($data['attributes'])>=1 ){
			foreach ($data['attributes'] as $attributes_val) {
				$attributes[]=array(
				  'meta_name'=>$attributes_val['meta_name'],
				  'meta_id'=>$attributes_val['meta_id'],
				);
			}
		}				
		
		$stmt="
		SELECT a.cart_uuid,a.cart_row,a.qty,
		(
		  select GROUP_CONCAT(id,';',subcat_id,';',sub_item_id,';',qty,';',multi_option)
		  from {{cart_addons}}
		  where
		  cart_row = a.cart_row
		) as addons,
		
		(
		  select GROUP_CONCAT(meta_name,';',meta_id)
		  from {{cart_attributes}}
		  where
		  cart_row = a.cart_row
		) as attributes
		
		FROM {{cart}} a
		WHERE merchant_id = ".$merchant_id."
		AND a.cart_uuid = ".q($cart_uuid)."
		AND a.cat_id = ".q($cat_id)."
		AND a.item_token = ".q($item_token)."
		AND a.item_size_id = ".q($item_size_id)."		
		AND a.special_instructions =".q($special_instructions)."
		";				
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
			foreach ($res as $items) {				
				
				/*attributes*/			
				$found_attr = array(); $attr_not_found = 0;	
				if(is_array($attributes) && count($attributes)>=1 && !empty($items['attributes']) ){
					$attributes_data = isset($items['attributes'])?explode(",",$items['attributes']):'';
					$new_attributes_data  = array();
					foreach ($attributes_data as $attr_data) {						
						$attr_data2 = explode(";",$attr_data);
						$new_attributes_data[] = array(
						  'meta_name'=>isset($attr_data2[0])?$attr_data2[0]:'',
						  'meta_id'=>isset($attr_data2[1])?$attr_data2[1]:'',
						);
					}					
															
					foreach ($attributes as $attributes_data) {						
						if($found = CCart::findAttributes($new_attributes_data,$attributes_data['meta_name'],$attributes_data['meta_id'])){
							$found_attr[]=$found;
						} else $attr_not_found++; 
					}			
								
					if( count($attributes)!= count($new_attributes_data)){
						$attr_not_found = 1;
					}
				} else {					
					if(count($attributes)>0 && empty($items['attributes'])){
						$attr_not_found = 1;
					}
				}
							
				if (is_array($addons) && count($addons)>=1 && !empty($items['addons']) ){				
					$addons_data = !empty($items['addons'])?explode(",",$items['addons']):'';						
					$new_addons_data = array();
					foreach ($addons_data as $addons_data1) {
						$addons_data2 = explode(";",$addons_data1);
						$new_addons_data[] = array(
						   'id'=>isset($addons_data2[0])?$addons_data2[0]:'',
						   'subcat_id'=>isset($addons_data2[1])?$addons_data2[1]:'',
						   'sub_item_id'=>isset($addons_data2[2])?$addons_data2[2]:'',
						   'qty'=>isset($addons_data2[3])?$addons_data2[3]:'',
						   'multi_option'=>isset($addons_data2[4])?$addons_data2[4]:'',
						);						
					}
									
					$found_addons = array(); $addons_not_found = 0;
					foreach ($addons as $addons_val) {										
						if($found = CCart::findaddon($new_addons_data,$addons_val['subcat_id'],$addons_val['sub_item_id'], $addons_val['qty'] )){
							$found_addons[]=$found;
						} else $addons_not_found++; 
					}
					
					if($addons_not_found<=0 && $attr_not_found<=0){						
						$items['qty'] = intval($items['qty']) + intval($item_qty);
						$items['addons'] = $found_addons;						
						return $items;
					}
					
				} else {												
					if (count($addons)<=0 && empty($items['addons']) && $attr_not_found<=0 ){															
						if($inline_qty>0){									
							$items['qty']  = intval($item_qty);	
						} else $items['qty'] = intval($items['qty']) + intval($item_qty);
												
						return $items;
					} 					
				}
			}						
		}				
		return false;		
	}
	
	protected static function findaddon($addon_data, $subcat_id='', $sub_item_id='', $qty=0)
	{
		$found = false;	
		if(is_array($addon_data) && count($addon_data)>=1){
			foreach ($addon_data as $val) {				
				if($val['subcat_id']==$subcat_id && $val['sub_item_id']==$sub_item_id){
					$val['qty'] = intval($val['qty']) + intval($qty);
					$found = $val;
					break;
				}
			}
		}
		return $found;
	}
	
	protected static function findAttributes($attributes_data='',$meta_name='',$meta_id='')
	{		
		$found = false;			
		if(is_array($attributes_data) && count($attributes_data)>=1){
			foreach ($attributes_data as $val) {				
				if($val['meta_name']==$meta_name && $val['meta_id']==$meta_id){					
					$found = $val;
					break;
				}
			}
		}
		return $found;
	}
		
	public static function getContent($cart_uuid='', $lang = KMRS_DEFAULT_LANGUAGE)
	{
		$content = CCart::getCart($cart_uuid,$lang);				
		$subcategory = CCart::getSubcategory($cart_uuid,$lang);
		$size = CCart::getSize($cart_uuid,$lang);		
		$addon_items = CCart::getAddonItems($cart_uuid,$lang);
		$meta_cooking = CCart::getMetaCooking($cart_uuid,$lang);
		$meta_ingredients = CCart::getMetaIngredients($cart_uuid,$lang);		
		
		$subtotal = self::calculateInitialSubtotal($content,$size,$addon_items);		
		$content = CMerchantMenu::applyPromoFreeItems($content,$subtotal,self::$cart_merchant_id);		

		if($content){
			self::$content = array(			  
			  'content'=>$content,
			  'subcategory'=>$subcategory?$subcategory:'',
		      'size'=>$size?$size:'', 
		      'addon_items'=>$addon_items?$addon_items:'',
		      'attributes'=>array(
		        'cooking_ref'=>$meta_cooking,
		        'ingredients'=>$meta_ingredients
		      )
			);
			return self::$content;
		}
		throw new Exception( 'cart is empty' );
	}

	public static function calculateInitialSubtotal($items=[],$size=[],$addon_items=[])
	{	
		$sub_total = 0;	
		$exchange_rate = 1;
		if(!is_array($items) && count($items)<=0){
			return $sub_total;
		}		
		$free_items = CMerchantMenu::getPromoItems(self::$cart_merchant_id);
		
		foreach ($items as $item) {
			if(isset($free_items[$item['item_id']])){
				continue;
			}			
			$qty = intval($item['qty']);
			$item_size_id = isset($item['item_size_id'])?(integer)$item['item_size_id']:0;
			$item_price_data = isset($size[$item_size_id])?$size[$item_size_id]:'';    			    			
			$item_price = CCart::parseItemPrice($item_price_data);				
			$item_price = $item_price>0? (floatval($item_price)*$exchange_rate) : $item_price;
			$total_price = $qty*$item_price;
			$sub_total+=$total_price;

			$addon_total = 0;
			if(is_array($item['addon_items']) && count($item['addon_items'])>=1){
				foreach ($item['addon_items'] as $addon_category) {
					foreach ($addon_category as $addons_item) {    						
						if(is_array($addons_item) && count($addons_item)>=1){    							
							foreach ($addons_item as $addon_items_id=>$addon) {    								
								$addon_price = isset($addon_items[$addon_items_id]['price'])?$addon_items[$addon_items_id]['price']:0;									
								$addon_qty = isset($addon['qty'])?(integer)$addon['qty']:0;
								$multi_option = isset($addon['multi_option'])?$addon['multi_option']:'';    								
								if($multi_option=="multiple"){
									$addon_total_price = floatval($addon_price)*intval($addon_qty); 
								} else $addon_total_price = floatval($addon_price)*intval($qty); 
								$addon_total+= $addon_total_price;
								$sub_total+=$addon_total_price;
								
							}
						}
					}
				}
			} // addons item 
		}
		return $sub_total;
	}
	
	public static function getCountCart($cart_uuid='')
	{
		$stmt="
		SELECT COUNT(*) as total FROM {{cart}}
		WHERE cart_uuid=".q($cart_uuid)."
		";
		if( $res = Yii::app()->db->createCommand($stmt)->queryRow() ){
			return $res['total'];
		}
		return false;
	}
	
	public static function getMerchant($merchant_id='',$lang='')
	{		
		$stmt="
		SELECT merchant_id,merchant_uuid,restaurant_name,restaurant_slug,		
		address,
		distance_unit,delivery_distance_covered,latitude,lontitude,
		delivery_zone_type,delivery_zone_covered,
		merchant_type,
		commision_type,
		percent_commision as commission,
		logo,path,
		
		
		IFNULL((
		 select GROUP_CONCAT(cuisine_name,';',color_hex,';',font_color_hex)
		 from {{view_cuisine}}
		 where language=".q($lang)."
		 and cuisine_id in (
		    select cuisine_id from {{cuisine_merchant}}
		    where merchant_id  = a.merchant_id
		 )		 
		),'') as cuisine_name
			
		FROM {{merchant}} a
		WHERE merchant_id =".q($merchant_id)."
		";						
		if( $res = Yii::app()->db->createCommand($stmt)->queryRow() ){			
			$cuisine_list = array();
			$cuisine_name = explode(",",$res['cuisine_name']);
			if(is_array($cuisine_name) && count($cuisine_name)>=1){
				foreach ($cuisine_name as $cuisine_val) {						
					$cuisine = explode(";",$cuisine_val);								
					$cuisine_list[]=array(
					  'cuisine_name'=>isset($cuisine[0])?Yii::app()->input->xssClean($cuisine[0]):'',
					  'bgcolor'=>isset($cuisine[1])?  (!empty($cuisine[1])?$cuisine[1]:'#ffd966')  :'#ffd966',
					  'fncolor'=>isset($cuisine[2])? (!empty($cuisine[2])?$cuisine[2]:'#ffd966') :'#000',
					);
				}
			}
			
			$res['cuisine'] = (array)$cuisine_list;
			$res['restaurant_name'] = Yii::app()->input->xssClean($res['restaurant_name']);
			$res['merchant_address'] = Yii::app()->input->xssClean($res['address']);
			return $res;
		}
		return false;
	}

	private static function calculateItemPreparationTime($baseTime=0, $quantity=0, $scalingFactor=0) {
		// If there is only 1 item, no additional time is added
		if ($quantity <= 1) {
			return $baseTime;
		}
	
		// Calculate extra time based on the number of items minus the first one
		$extraTime = ($quantity - 1) * $scalingFactor;
	
		// Total preparation time for this item
		return (float)$baseTime + (float)$extraTime;
	}
	
	public static function getCart($cart_uuid='', $lang = KMRS_DEFAULT_LANGUAGE)
	{
		$tax_use = array(); 
		$tax_data_list = array();		
		$tax_type = self::getTaxType();	
		$unavailable_item = [];
		$total_points_earn = 0;
		$merchant_id = 0;
		$transaction_type = self::getTransactionType();
						
		try {
		   $merchant_id = CCart::getMerchantId($cart_uuid);
		   $tax_data_list = CTax::getTax($merchant_id,$tax_type);		
		   $unavailable_item = CMerchantMenu::getItemAvailability($merchant_id,date("w"),date("H:h:i"));
		} catch (Exception $e) {
			//
		}

		self::$cart_merchant_id = $merchant_id;
		
		$and_send_order = '';
		$payload = self::getPayload();		
		$send_order = in_array("send_order",(array)$payload)?1:0;
		if(in_array("all_orders",(array)$payload)){
			$and_send_order = '';
		} else $and_send_order = "AND a.send_order = ".q($send_order)."";		
				
		$stmt="
		SELECT 
		a.cart_row,
		a.cart_uuid,
		a.cat_id,		
		a.item_token,		
		b.item_name as original_item_name,
		c.item_name,
		b.item_id,
		b.photo,	
		b.path,
		b.non_taxable as taxable,	
		b.packaging_fee,	
		b.packaging_incremental,
		b.points_earned,
		b.points_enabled,
		b.preparation_time,
		b.extra_preparation_time,
		a.item_size_id,a.qty,a.special_instructions,a.if_sold_out,
				
		(
		 select GROUP_CONCAT(cart_row,';',subcat_id,';',sub_item_id,';',qty,';',multi_option)
		 from {{cart_addons}}
		 where cart_uuid = a.cart_uuid
		 and cart_row = a.cart_row
		) as addon_items,
		
		(
		 select GROUP_CONCAT(meta_name,';',meta_id)
		 from {{cart_attributes}}
		 where cart_uuid = a.cart_uuid
		 and cart_row = a.cart_row
		) as attributes,
		
		(
		 select GROUP_CONCAT(meta_id)
		 from {{item_meta}}
		 where merchant_id = a.merchant_id
		 and item_id = b.item_id
		 and meta_name='tax'
		) as item_tax
		
		FROM {{cart}} a
		LEFT JOIN {{item}} b
		ON
		a.item_token = b.item_token

		left JOIN (
		   SELECT item_id, item_name FROM {{item_translation}} where language = ".q($lang)."
		) c 
		ON b.item_id = c.item_id
		
		WHERE a.cart_uuid = ".q($cart_uuid)."		
		$and_send_order
		AND b.available = 1
		AND b.not_for_sale = 0
		AND b.merchant_id =".q($merchant_id)."
		ORDER BY id ASC
		";		
		//dump($stmt);die();
		if( $res = Yii::app()->db->createCommand($stmt)->queryAll() ){									
			$data = array();  $total_preparation_time = 0;
			$atts = OptionsTools::find(['merchant_default_preparation_time'],$merchant_id);
			$merchant_default_preparation_time = isset($atts['merchant_default_preparation_time'])?$atts['merchant_default_preparation_time']:0;			
			$maxPreparationTime = 0;

			foreach ($res as $val) {				
				
				$addon = array();
				$cart_row = $val['cart_row'];	
				
				if($val['points_enabled']==1){
					$item_points = isset($val['points_earned'])?$val['points_earned']:0;
				    $total_points_earn+= $item_points;
				}				

				$item_id = $val['item_id'];				
				if(in_array($item_id,$unavailable_item)){
					continue;
				}
								
				$addon_items = array();
				$_addon_items = isset($val['addon_items'])? explode(",",$val['addon_items']) :'';
				if(is_array($_addon_items) && count($_addon_items)>=1){
					foreach ($_addon_items as $val3) {						
						$addonitems = explode(";",$val3);																		
						$row = isset($addonitems[0])?$addonitems[0]:'';
						$subcat_id = isset($addonitems[1])?$addonitems[1]:'';
						$sub_item_id = isset($addonitems[2])?$addonitems[2]:'';
						$qty = isset($addonitems[3])?$addonitems[3]:'';
						$multi_option = isset($addonitems[4])?$addonitems[4]:'';						
						$addon_items[$row][$subcat_id][$sub_item_id] = array(
						 'qty'=>$qty,
						 'multi_option'=>$multi_option
						);
					}
				}
				
				$attributes = array();
				$attributes_raw = isset($val['attributes'])? explode(",",$val['attributes']) :'';
				if(is_array($attributes_raw) && count($attributes_raw)>=1){
					foreach ($attributes_raw as $val4) {
						$attributes_item = explode(";",$val4);
						$meta_name = isset($attributes_item[0])?$attributes_item[0]:'';
						$meta_value = isset($attributes_item[1])?$attributes_item[1]:'';						
						$attributes[$meta_name][$meta_value]=$meta_value;
					}
				}
								
				if($transaction_type!="dinein"){
					if($val['packaging_fee']>0 && $val['packaging_incremental']<=0){
						self::$packaging_fee+= $val['packaging_fee'];					
					} elseif ( $val['packaging_fee']>0 && $val['packaging_incremental']>0){
						self::$packaging_fee+= floatval($val['packaging_fee']) * intval($val['qty']);
					}
			    }
				
				$tax_use = array();
											
				if($tax_type=="multiple"){						
					if(!empty($val['item_tax'])){					
						$item_tax = isset($val['item_tax'])? explode(",",$val['item_tax']) :'';									
						if(is_array($item_tax) && count($item_tax)>=1){					
							foreach ($item_tax as $tax_id) {
								if(array_key_exists($tax_id,(array)$tax_data_list)){
									array_push($tax_use,$tax_data_list[$tax_id]);
								}
							}
						}
					}
				} else $tax_use = $tax_data_list;
				
				$preparation_time = $val['preparation_time']>0?$val['preparation_time'] : $merchant_default_preparation_time;
				$extra_preparation_time = isset($val['extra_preparation_time'])?$val['extra_preparation_time']:0;
				$itemPrepTime = self::calculateItemPreparationTime($preparation_time,$val['qty'],$extra_preparation_time);
				if ($itemPrepTime > $maxPreparationTime) {
					$maxPreparationTime = $itemPrepTime;
				}
				
				$data[] = array(				  
				  'cart_row'=>$val['cart_row'],
				  'cart_uuid'=>$val['cart_uuid'],
				  'cat_id'=>$val['cat_id'],
				  'item_id'=>$val['item_id'],
				  'item_token'=>$val['item_token'],
				  'item_name'=> empty($val['item_name'])? Yii::app()->input->xssClean($val['original_item_name']) : Yii::app()->input->xssClean($val['item_name']),				  				  
				  'url_image'=>CMedia::getImage($val['photo'],$val['path'],Yii::app()->params->size_image_thumbnail,
				   CommonUtility::getPlaceholderPhoto('item')),
				  'item_size_id'=>$val['item_size_id'],
				  'qty'=>intval($val['qty']),				  
				  'special_instructions'=>Yii::app()->input->xssClean($val['special_instructions']),				  
				  'if_sold_out'=>$val['if_sold_out'],
				  'addon_items'=>$addon_items,
				  'attributes'=>$attributes,
				  'tax'=>$tax_use,		
				  'is_free'=>false,		  
				);								
			}					
						
			self::setTotalFoodPoints($total_points_earn);
			self::setTotalPreparationTime($maxPreparationTime);
			
			return $data;
		}
		throw new Exception( t("No items found in the cart") );
	}
	
	public static function itemCount($cart_uuid='')
	{
		$payload = self::getPayload();
		//$send_order = in_array("send_order",(array)$payload)?1:0;		

		$and_send_order = '';
		$payload = self::getPayload();		
		$send_order = in_array("send_order",(array)$payload)?1:0;
		if(in_array("all_orders",(array)$payload)){
			$and_send_order = '';
		} else $and_send_order = "AND send_order = ".q($send_order)."";		

		$stmt="
		SELECT SUM(qty) as item_count
		FROM {{cart}}
		WHERE cart_uuid=".q($cart_uuid)."
		$and_send_order
		";		
		$dependency = CCacheData::dependency();			
		if($res = Yii::app()->db->cache(Yii::app()->params->cache, $dependency)->createCommand($stmt)->queryRow()){			
			return $res['item_count'];
		}
		return 0;
	}
	
	public static function getSubcategory($cart_uuid='', $lang = KMRS_DEFAULT_LANGUAGE)
	{
		$stmt="
		SELECT 
		a.subcat_id , 
		a.subcategory_name as original_subcategory_name,
		b.subcategory_name
		FROM {{subcategory}} a		
		left JOIN (
			SELECT subcat_id, subcategory_name FROM {{subcategory_translation}} where language = ".q($lang)."
		) b 		
		ON
		a.subcat_id = b.subcat_id
		WHERE
		a.subcat_id IN (
		  select subcat_id from {{cart_addons}}
		  where cart_uuid =".q($cart_uuid)."
		)
		";					
		if( $res = Yii::app()->db->createCommand($stmt)->queryAll() ){			
			$data = array();
			foreach ($res as $val) {				
				$data[$val['subcat_id']] = array(
				  'subcat_id'=>$val['subcat_id'],
				  'subcategory_name'=> empty($val['subcategory_name'])? $val['original_subcategory_name'] :  $val['subcategory_name']
				);
			}
			return $data;
		}
		return false;
	}
	
	public static function getSize($cart_uuid='', $lang = KMRS_DEFAULT_LANGUAGE)
	{
		$stmt="
		SELECT a.item_size_id,a.size_name,
		a.price , a.discount,
		a.discount_type,		
		 (
		  select count(*) from {{view_item_lang_size}}
		  where item_size_id = a.item_size_id 		  
		  and CURDATE() >= discount_start and CURDATE() <= discount_end
		 ) as discount_valid
		
		FROM {{view_item_lang_size}}	a		
				
		WHERE a.language IN ('',".q($lang).")
		AND a.item_size_id IN (
		 select item_size_id from {{cart}}
		 where cart_uuid =".q($cart_uuid)."
		)
		";						
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
			$data = array();
			foreach ($res as $val) {				
				$data[$val['item_size_id']] = array(
				  'item_size_id'=>$val['item_size_id'],
				  'size_name'=>$val['size_name'],
				  'price'=>$val['price'],				  
				  'discount'=>$val['discount_valid']>0?$val['discount']:0,
				  'discount_type'=>$val['discount_type'],
				  'discount_valid'=>$val['discount_valid'],				  
				);
			}
			return $data;
		}
		return false;
	}
	
	public static function getAddonItems($cart_uuid='', $lang = KMRS_DEFAULT_LANGUAGE)
	{
		$exchange_rate = self::getExchangeRate();

		$stmt="
		SELECT a.sub_item_id,
		a.sub_item_name as original_sub_item_name,
		a.item_description as original_item_description,
		b.sub_item_name, b.item_description,
		a.price, a.photo, a.path
		
		FROM {{subcategory_item}} a		
		left JOIN (
			SELECT id,sub_item_id, sub_item_name,item_description FROM {{subcategory_item_translation}} where language = ".q($lang)."
		) b 		
		ON
		a.sub_item_id = b.sub_item_id
		WHERE a.status = 'publish'		
		
		AND a.sub_item_id IN (		  
		  select sub_item_id from {{cart_addons}}
		  where cart_uuid =".q($cart_uuid)."
		)
		ORDER BY a.sequence,b.id ASC
		";				
		if( $res = Yii::app()->db->createCommand($stmt)->queryAll() ){			
			$data = array();
			foreach ($res as $val) {	
				$sub_item_id = (integer) $val['sub_item_id'];
				$data[$sub_item_id] = array(
				  'sub_item_id'=>$sub_item_id,
				  'sub_item_name'=> empty($val['sub_item_name'])? Yii::app()->input->xssClean($val['original_sub_item_name']) : Yii::app()->input->xssClean($val['sub_item_name']),
				  'item_description'=> empty($val['item_description'])? Yii::app()->input->xssClean($val['original_item_description']) : Yii::app()->input->xssClean($val['item_description']),
				  'price'=>(float)(floatval($val['price'])*$exchange_rate),
				  'pretty_price'=>Price_Formatter::formatNumber( (floatval($val['price'])*$exchange_rate) ),
				  'url_image'=>CMedia::getImage($val['photo'],$val['path'],Yii::app()->params->size_image_thumbnail,
				               CommonUtility::getPlaceholderPhoto('item')),
				);
			}
			return $data;
		}
		return false;
	}
	
	public static function getMetaCooking($cart_uuid='', $lang = KMRS_DEFAULT_LANGUAGE)
	{
		$stmt="
		SELECT 
		a.cook_id,
		a.cooking_name as original_cooking_name,
		b.cooking_name
		FROM {{cooking_ref}} a
		left JOIN (
			SELECT cook_id, cooking_name FROM {{cooking_ref_translation}} where language = ".q($lang)."
		) b 
		on a.cook_id = b.cook_id

		WHERE
		a.cook_id IN (
		  select meta_id from {{cart_attributes}}
		  where cart_uuid =".q($cart_uuid)."
		  and meta_name = 'cooking_ref'
		)
		";					
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){						
			$data = array();
			foreach ($res as $val) {
				$id = (integer) $val['cook_id'];
				$data[$id] = empty($val['cooking_name'])? Yii::app()->input->xssClean($val['original_cooking_name']) : Yii::app()->input->xssClean($val['cooking_name']);
			}
			return $data;
		}
		return false;
	}
	
	public static function getMetaIngredients($cart_uuid='', $lang = KMRS_DEFAULT_LANGUAGE)
	{
		$stmt="
		SELECT 
		a.ingredients_id,
		a.ingredients_name as original_ingredients_name, 
		b.ingredients_name
		FROM {{ingredients}} a
		left JOIN (
			SELECT ingredients_id, ingredients_name FROM {{ingredients_translation}} where language = ".q($lang)."
		) b 
		on a.ingredients_id = b.ingredients_id

		WHERE 
		a.ingredients_id IN (
		  select meta_id from {{cart_attributes}}
		  where cart_uuid =".q($cart_uuid)."
		  and meta_name = 'ingredients'
		)
		";			
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
			$data = array();
			foreach ($res as $val) {
				$id = (integer) $val['ingredients_id'];
				$data[$id] = empty($val['ingredients_name'])?  Yii::app()->input->xssClean($val['original_ingredients_name']) : Yii::app()->input->xssClean($val['ingredients_name']);
			}
			return $data;
		}
		return false;
	}
	
	public static function remove($cart_uuid='',$row_id='')
	{
		
		$cart = AR_cart::model()->find('cart_uuid=:cart_uuid AND cart_row=:cart_row', 
		array(':cart_uuid'=>$cart_uuid, ':cart_row'=>$row_id ));		
	
		if($cart){
			
			AR_cart::model()->deleteAll('cart_uuid=:cart_uuid AND cart_row=:cart_row',array(
			  ':cart_uuid'=>$cart_uuid,
			  ':cart_row'=>$row_id 
			));
			
			AR_cart_addons::model()->deleteAll('cart_uuid=:cart_uuid AND cart_row=:cart_row',array(
			  ':cart_uuid'=>$cart_uuid,
			  ':cart_row'=>$row_id 
			));
			
			AR_cart_attributes::model()->deleteAll('cart_uuid=:cart_uuid AND cart_row=:cart_row',array(
			  ':cart_uuid'=>$cart_uuid,
			  ':cart_row'=>$row_id 
			));
					
		    return true;
		}
		throw new Exception( 'row not found' );
	}
	
	public static function clear($cart_uuid='')
	{
		
		AR_cart::model()->deleteAll('cart_uuid=:cart_uuid',array(
			':cart_uuid'=>$cart_uuid			  
		  ));
		
		AR_cart_attributes::model()->deleteAll('cart_uuid=:cart_uuid',array(
		':cart_uuid'=>$cart_uuid,			  
		));

		AR_cart_addons::model()->deleteAll('cart_uuid=:cart_uuid',array(
		':cart_uuid'=>$cart_uuid,			  
		));

		AR_customer_request::model()->deleteAll('cart_uuid=:cart_uuid',array(
			':cart_uuid'=>$cart_uuid,			   
		));
				
		return true;
	}
	
	public static function addCondition($data = array() )
	{
		if(is_array($data) && count($data)>=1){
		   CCart::$condition[] = $data;
		}
	}
	
	public static function getCondition()
	{
		if(is_array(CCart::$condition) && count(CCart::$condition)>=1){
		   return CCart::$condition;
		}
		return false;
	}
				
	public static function isEmpty()
	{
		if(is_array(self::$content) && count(self::$content)>=1){
			return false;
		}
		return true;
	}
	
	public static function addTaxCondition($data = array() )
	{
		if(is_array($data) && count($data)>=1){
		   CCart::$tax_condition = $data;
		}
	}
	
	public static function getTaxCondition()
	{
		if(is_array(CCart::$tax_condition) && count(CCart::$tax_condition)>=1){
		   return CCart::$tax_condition;
		}
		return false;
	}
	
	public static function setTaxType($tax_type='')
	{
		if(!empty($tax_type)){
			self::$tax_type = $tax_type;
		}
	}
	
	public static function addTaxGroup($key=0,$data = array() )
	{		
		$current_data=0;
		if(isset(CCart::$tax_group[$key])){
			$current_data = CCart::$tax_group[$key]['total'];
		}
		CCart::$tax_group[$key] = array(
		  'tax_in_price'=>isset($data['tax_in_price'])?$data['tax_in_price']:false,
		  'total'=>floatval($current_data) + floatval($data['tax_total'])
		);
	}
	
	public static function getTaxGroup()
	{
		if(is_array(CCart::$tax_group) && count(CCart::$tax_group)>=1){
		   return CCart::$tax_group;
		}
		return false;
	}
	
	public static function addTax($tax=array(), $total=0){				
		$tax_total = 0;
		if(is_array($tax) && count($tax)>=1 && $total>0){
			foreach ($tax as $tax_item) {
				$tax_in_price = $tax_item['tax_in_price'] ?? false;
				$tax_in_price = $tax_in_price==1?true:false;
				if($tax_in_price){
					$tax_rate = $tax_item['tax_rate'] ?? 0;
					$vat_multiplier = 1 + ($tax_rate / 100);					
					$price_excl_vat = round($total / $vat_multiplier, 2);
					$tax_total = $total - $price_excl_vat;
				} else {
					$tax_rate = isset($tax_item['tax_rate']) ? floatval($tax_item['tax_rate']) :0;
				    $tax_rate = $tax_rate/100;				
				    $tax_total = $tax_rate*floatval($total);				
				}							
				
				self::addTaxGroup($tax_item['tax_id'], array(
				 'tax_total'=>$tax_total,
				 'tax_in_price'=>$tax_item['tax_in_price']
				));
			}
		}
	}
	
	public static function getTaxType()
	{
		if(!empty(self::$tax_type)){
			return self::$tax_type;
		}
		return false;
	}
		
	protected static function valueIsPercentage($value='')
    {
        return (preg_match('/%/', $value) == 1);
    }
        
    protected static function valueIsToBeSubtracted($value)
    {
        return (preg_match('/\-/', $value) == 1);
    }
       
    protected static function valueIsToBeAdded($value)
    {
        return (preg_match('/\+/', $value) == 1);
    }
    
    protected static function normalizePrice($price)
    {
        return (is_string($price)) ? floatval(CCart::cleanValue($price)) : $price;
    }
    
    protected static function cleanValue($value)
    {
        return str_replace(array('%','-','+'),'',$value);
    }

	public static function cleanValues($value)
    {
        return str_replace(array('%','-','+'),'',$value);
    }
    
    public static function cleanNumber($value)
    {
    	return self::cleanValue($value);
    }
    
    public static function getSubTotal()
    {    	
		$exchange_rate = self::getExchangeRate();
    	$sub_total = 0; $sub_total_without_cnd = 0; $taxable_subtotal = 0;  $sub_total_without_admin_discount = 0;
    	    	
    	if(!CCart::isEmpty()){
    		$items = isset(CCart::$content['content'])?CCart::$content['content']:'';
    		$size = isset(CCart::$content['size'])?CCart::$content['size']:'';
    		$addon_items = isset(CCart::$content['addon_items'])?CCart::$content['addon_items']:'';
    		foreach ($items as $val) {    	    			
    			    			
    			$qty = intval($val['qty']);
				$is_free = $val['is_free'] ?? false;				
    			
    			$item_size_id = isset($val['item_size_id'])?(integer)$val['item_size_id']:0;
    			$item_price_data = isset($size[$item_size_id])?$size[$item_size_id]:'';    			    			
    			$item_price = CCart::parseItemPrice($item_price_data);
				if($is_free){
					$item_price = 0;
				}
				$item_price = $item_price>0? (floatval($item_price)*$exchange_rate) : $item_price;
    			$total_price = $qty*$item_price;
    			$sub_total+=$total_price;
    			
    			$addon_total = 0;
    			if(is_array($val['addon_items']) && count($val['addon_items'])>=1){
    				foreach ($val['addon_items'] as $addon_category) {
    					foreach ($addon_category as $addons_item) {    						
    						if(is_array($addons_item) && count($addons_item)>=1){    							
    							foreach ($addons_item as $addon_items_id=>$addon) {    								
    								$addon_price = isset($addon_items[$addon_items_id]['price'])?$addon_items[$addon_items_id]['price']:0;		
									if($is_free){
										$addon_price = 0;
									}
    								$addon_qty = isset($addon['qty'])?(integer)$addon['qty']:0;
    								$multi_option = isset($addon['multi_option'])?$addon['multi_option']:'';    								
    								if($multi_option=="multiple"){
    									$addon_total_price = floatval($addon_price)*intval($addon_qty); 
    								} else $addon_total_price = floatval($addon_price)*intval($qty); 
    								$addon_total+= $addon_total_price;
    								$sub_total+=$addon_total_price;
    								
    							}
    						}
    					}
    				}
    			} // addons item 
    			
    			/*ADD TAX*/
    			if(isset($val['tax'])){    				    				    				
    				$total_to_tax = floatval($total_price)+floatval($addon_total);        						
    				self::addTax($val['tax'], $total_to_tax);
    			}
    			
    		} // items
    		    		   	   
			
				
    		$sub_total_without_cnd = $sub_total;
    		$sub_total_without_admin_discount = $sub_total;
    		/*CONDITION*/
    		if ( $condition = CCart::getCondition()){    			
    			foreach ($condition as $val) {       				
    				if($val['target']=="subtotal"){         					
    					$raw_sub_total = CCart::apply($sub_total,$val['value']);  
    					$sub_total = $raw_sub_total;
    					if(isset($val['voucher_owner'])){
    						if($val['voucher_owner']=='admin'){
    							//
    						} else $sub_total_without_admin_discount = $raw_sub_total;
    					} else $sub_total_without_admin_discount = $raw_sub_total;
    				}
    			}
    		}    		
    		    		    		
    	}
    	
    	return array(
    	  'sub_total'=>floatval($sub_total),
    	  'taxable_subtotal'=>floatval($taxable_subtotal),
    	  'sub_total_without_cnd'=>$sub_total_without_cnd,
    	  'sub_total_without_admin_discount'=>$sub_total_without_admin_discount,
    	);
    }
    
    public static function getSubTotal_lessDiscount()
    {
    	$subtotal = CCart::getSubTotal();    	
    	$sub_total = floatval($subtotal['sub_total']);
    	return $sub_total;
    }
    
    public static function getSubTotal_TobeCommission()
    {
    	$subtotal = CCart::getSubTotal();    	
    	$sub_total = $subtotal['sub_total_without_admin_discount']>0?floatval($subtotal['sub_total_without_admin_discount']):$subtotal['sub_total'];
    	return $sub_total;
    }
    
    public static function getTotal()
    {   
    	self::$tax_group = array(); 	
    	$results = CCart::getSubTotal();    	
		$tax_type = self::getTaxType();	
    	$sub_total = $results['sub_total'];
		$subTotal = $results['sub_total'];		
    	$taxable_subtotal = $results['taxable_subtotal'];
		$taxAmount = 0;	  		
    	    			   
    	/*CONDITION*/
    	if ( $condition = CCart::getCondition()){    		
    		foreach ($condition as $val) {    				
    			if($val['target']=="total"){    				
    				
					$isTaxable = $val['taxable'] ?? false;
					$isTaxable = $isTaxable==1?true:false;
					if($isTaxable){
						$subTotal+=$val['value'];
					}

					/*ADD TAX*/    					    					
					if(isset($val['tax']) && isset($val['taxable'])){        						
						if($val['taxable']){    										            	
							$total_to_tax = isset($val['value'])? floatval($val['value']) : 0;			                
							self::addTax($val['tax'], $total_to_tax);
						}
					}   

					if($tax_type=="multiple"){												
						if($val['type']=="tax"){
							$tax_group_data = self::getTaxGroup();
							$tax_value = isset($tax_group_data[$val['tax_id']]) ? $tax_group_data[$val['tax_id']]['total'] : 0;
							$tax_in_price = isset($tax_group_data[$val['tax_id']]) ? $tax_group_data[$val['tax_id']]['tax_in_price'] : false;
							if($tax_value>0){
								self::setTotalTax($tax_value);
								$results[] = array(
									'name'=>$val['name'],
									'value'=>Price_Formatter::formatNumber($tax_value),
									'raw'=>$tax_value,
									'type'=>$val['type'],
								);	  
							}
							if($tax_in_price==false){                       	  
							   $sub_total = CCart::apply($sub_total,$tax_value);  
							} 
						 } else {
							 $sub_total = CCart::apply($sub_total,$val['value']); 
						 }
					} else if ( $tax_type=="standard") {
						if($val['type']=="tax"){			
							$tax_group_data = self::getTaxGroup();						
							$firstTax = reset($tax_group_data);								
							$tax_in_price = $firstTax['tax_in_price'] ?? false;
							$tax_in_price = $tax_in_price==1?true:false;

							if($tax_in_price){
								$taxRate = $val['value1'] ?? 0;
								$discountedSubtotal = max($subTotal,0);
								$taxExtracted = $discountedSubtotal / (1+($taxRate/100));
								$taxAmount = $discountedSubtotal - $taxExtracted;								
							} else {
								$taxRate = $val['value1'] ?? 0;
								$discountedSubtotal = max($subTotal,0);							
								$taxAmount = $discountedSubtotal * ($taxRate / 100);							
							}															

							if(!$tax_in_price){
							   $sub_total = CCart::apply($sub_total,$taxAmount);  
							}
						} else $sub_total = CCart::apply($sub_total,$val['value']); 						
					} else $sub_total = CCart::apply($sub_total,$val['value']); 
    			}
    		}
    	}
    	    			
    	return $sub_total;
    }
    
    public static function getItems()
    {    	    	    
    	$results = array(); $exchange_rate = self::getExchangeRate();
    	if(!CCart::isEmpty()){
    		$items = isset(CCart::$content['content'])?CCart::$content['content']:'';
    		$size = isset(CCart::$content['size'])?CCart::$content['size']:'';    		
    		$subcategory = isset(CCart::$content['subcategory'])?CCart::$content['subcategory']:'';
    		$addon_items = isset(CCart::$content['addon_items'])?CCart::$content['addon_items']:'';
    		$attributes = isset(CCart::$content['attributes'])?CCart::$content['attributes']:'';			
    		    		
    		foreach ($items as $val) {    			
    			$qty = intval($val['qty']);
    			$item_size_id = isset($val['item_size_id'])?(integer)$val['item_size_id']:0;
    			$item_price_data = isset($size[$item_size_id])?$size[$item_size_id]:'';    			

    			$item_price_raw = isset($item_price_data['price'])?(float)$item_price_data['price']:0;
				$item_price_raw = $item_price_raw>0? ($item_price_raw*$exchange_rate):$item_price_raw;

    			$item_price = CCart::parseItemPrice($item_price_data);
				$item_price = $item_price>0? ($item_price*$exchange_rate) : $item_price;
    			
				$is_free = $val['is_free'] ?? false;

    			/*ADDON*/
    			$results_addon = array(); $results_addon_item = array(); $addonsTotal = 0;
    			if(is_array($val['addon_items']) && count($val['addon_items'])>=1){
    				foreach ($val['addon_items'] as $addon_category) {    					
    					foreach ($addon_category as $addon_cat_id => $addons_item) {   
    						$results_addon_item = array();
    						if(is_array($addons_item) && count($addons_item)>=1){
    							foreach ($addons_item as $sub_item_id=>$sub_item_data) {     								
    								if(isset($addon_items[$sub_item_id])){
	    								$multi_option = isset($sub_item_data['multi_option'])?$sub_item_data['multi_option']:'';
	    								$addons_qty = isset($sub_item_data['qty'])?intval($sub_item_data['qty']):1;    	
	    								$addons_price = isset($addon_items[$sub_item_id]['price'])?floatval($addon_items[$sub_item_id]['price']):0;																			
										if($is_free){
											$addons_price = 0;
										}
	    								if($multi_option=="multiple"){    					
	    									$addons_total = $addons_qty*$addons_price; 		
											$addonsTotal+=$addons_total;
	    									$addon_items[$sub_item_id]['qty']=$addons_qty;
	    									$addon_items[$sub_item_id]['addons_total']=$addons_total;    							
	    								} else {
	    									$addons_total = $qty*$addons_price; 
											$addonsTotal+=$addons_total;
	    									$addon_items[$sub_item_id]['qty']=$qty;
	    									$addon_items[$sub_item_id]['addons_total']=$addons_total;    									
	    								}
	    								$addon_items[$sub_item_id]['multiple'] = $multi_option;
	    								$addon_items[$sub_item_id]['pretty_addons_total']=Price_Formatter::formatNumber($addons_total);
	    								$results_addon_item[]=$addon_items[$sub_item_id];
    								}
    							}
    						}
    						 												
    						$results_addon[] = array(
    						  'subcat_id'=>$addon_cat_id,
    						  'subcategory_name'=>isset($subcategory[$addon_cat_id]['subcategory_name'])?$subcategory[$addon_cat_id]['subcategory_name']:'',
    						  'addon_items'=>$results_addon_item
    						);    						
    					}
    				}
    			}
    			
    			/*ATTRIBUTES*/
    			$attributes_list=array(); $attributes_list_raw = array();
    			if(is_array($val['attributes']) && count($val['attributes'])>=1 ){
    				foreach ($val['attributes'] as $meta_key=>$data_attributes) {    					
    					$attributes_items = array(); $attributes_items_raw = array();
    					if(is_array($data_attributes) && count($data_attributes)>=1){
    						foreach ($data_attributes as $meta_value) {    	    							
    							if(isset($attributes[$meta_key])){
    							   $attributes_items[] = isset($attributes[$meta_key][$meta_value])?$attributes[$meta_key][$meta_value]:'';
    							   $attributes_items_raw[$meta_value] = isset($attributes[$meta_key][$meta_value])?$attributes[$meta_key][$meta_value]:'';
    							}
    						}
    						$attributes_list[$meta_key] = $attributes_items;
    						$attributes_list_raw[$meta_key] = $attributes_items_raw;
    					}
    				}
    			}    
								
    			    			    		
    			$price = isset($item_price_data['price'])?(float)$item_price_data['price']:0;				
				
				if($is_free){
					$price = 0;
					$item_price = 0;
					$item_price_raw = 0;
					$addonsTotal = 0;
				}

				$price = $price>0? floatval($price)*floatval($exchange_rate) :0;
    			$total = intval($val['qty']) * floatval($price);
    			$total_after_discount = intval($val['qty']) * floatval($item_price);
				$subTotal = $total_after_discount+$addonsTotal;
    			$results[] = array(
				   'is_edit'=>false,
    			   'cart_row'=>$val['cart_row'],
    			   'cat_id'=>$val['cat_id'],
    			   'item_id'=>$val['item_id'],
    			   'item_token'=>$val['item_token'],
    			   'item_name'=>CHtml::decode($val['item_name']),
    			   'url_image'=>$val['url_image'],
    			   'special_instructions'=>$val['special_instructions'],
    			   'if_sold_out'=>$val['if_sold_out'],
    			   'qty'=>intval($val['qty']),
    			   'price'=>array(
    			     'item_size_id'=>$val['item_size_id'],
    			     'price'=>$price,
    			     'size_name'=>isset($item_price_data['size_name'])?$item_price_data['size_name']:'',
    			     'discount'=>isset($item_price_data['discount'])?(float)$item_price_data['discount']:'',
    			     'discount_type'=>isset($item_price_data['discount_type'])?$item_price_data['discount_type']:'',
    			     'price_after_discount'=>(float)$item_price,
    			     'pretty_price'=>Price_Formatter::formatNumber($item_price_raw),
    			     'pretty_price_after_discount'=>Price_Formatter::formatNumber($item_price),
    			     'total'=>$total, 
    			     'pretty_total'=>Price_Formatter::formatNumber($total),
    			     'total_after_discount'=>$total_after_discount,
    			     'pretty_total_after_discount'=>Price_Formatter::formatNumber($total_after_discount),
    			   ),
    			   'attributes'=>$attributes_list,    			   
    			   'attributes_raw'=>$attributes_list_raw,
    			   'addons'=>$results_addon,
    			   'tax'=>isset($val['tax'])?$val['tax']:'',
				   'subtotal'=>$subTotal,
				   'subtotal_pretty'=>Price_Formatter::formatNumber($subTotal),
				   'is_free'=>$is_free
    			);
    		}
    	}
    	return $results;
    }
    
    public static function getSummary()
    {    	
    	$results = array(); self::$tax_group = array();
    	if(!CCart::isEmpty()){
    		$resp = CCart::getSubTotal();       
			$tax_type = self::getTaxType();   					
    	    $sub_total = $resp['sub_total'];
			$subTotal = $resp['sub_total'];
    	    $sub_total_without_cnd = $resp['sub_total_without_cnd'];		
			$taxAmount = 0;	
    	    	    	     
    		if ( $condition = CCart::getCondition()){
    			    			
    			/*SUB TOTAL*/
    			foreach ($condition as $val) {    				    				    				
    				if($val['target']=="subtotal"){          
						$max_discount_cap = $val['max_discount_cap'] ?? 0;						
    					$value = CCart::summary($val['value'],$sub_total_without_cnd , $max_discount_cap);    					
    					$results[] = array(
    					 'name'=>$val['name'],
    					 'value'=>isset($value['value'])?$value['value']:0,
    					 'raw'=>isset($value['raw'])?$value['raw']:0,
    					 'type'=>$val['type'],
    					);
    				}
    			}
    			
    			$value = CCart::summary( $sub_total );
    			$results[]=array(
    			  'name'=>t("Sub total"),
    			  'value'=>isset($value['value'])?$value['value']:0,
    			  'raw'=>isset($value['raw'])?$value['raw']:0,
    			  'type'=>'subtotal',
    			);
    			
    			/*TOTAL*/				
    			foreach ($condition as $val) {    						
    				if($val['target']=="total"){    	
						
						$isTaxable = $val['taxable'] ?? false;
						$isTaxable = $isTaxable==1?true:false;
						if($isTaxable){
							$subTotal+=$val['value'];
						}
    									
    					$value = CCart::summary($val['value'],$sub_total);
    					
    					/*ADD TAX*/    					    					
    					if(isset($val['tax']) && isset($val['taxable'])){        						
    						if($val['taxable']){    							
			    				$total_to_tax = isset($value['raw'])? floatval($value['raw']) : 0;								
			    				self::addTax($val['tax'], $total_to_tax);
    						}
		    			}    
		    				
		    			if($val['type']=="tax"){		
							if($tax_type=="multiple"){
								$tax_group_data = self::getTaxGroup();		    				
								$tax_value = isset($tax_group_data[$val['tax_id']]) ? $tax_group_data[$val['tax_id']]['total'] : 0;
								$tax_in_price = isset($tax_group_data[$val['tax_id']]) ? $tax_group_data[$val['tax_id']]['tax_in_price'] : false;
								if($tax_value>0){
									self::setTotalTax($tax_value);
									$results[] = array(
										'name'=>$val['name'],
										'value'=>Price_Formatter::formatNumber($tax_value),
										'raw'=>$tax_value,
										'type'=>$val['type'],
									);	    
								}		    				
								if($tax_in_price==false){
									$sub_total = CCart::apply($sub_total,$tax_value);  
								} 
						    } else if ($tax_type=="standard") {		
								$tax_group_data = self::getTaxGroup();						
								$firstTax = reset($tax_group_data);								
								$tax_in_price = $firstTax['tax_in_price'] ?? false;
				                $tax_in_price = $tax_in_price==1?true:false;								
								if($tax_in_price){
									$taxRate = $val['value1'] ?? 0;
									$discountedSubtotal = max($subTotal,0);
									$taxExtracted = $discountedSubtotal / (1+($taxRate/100));
									$taxAmount = $discountedSubtotal - $taxExtracted;
								} else {
									$taxRate = $val['value1'] ?? 0;								
								    $discountedSubtotal = max($subTotal,0);
								    $taxAmount = $discountedSubtotal * ($taxRate / 100);
								}
															
								self::setTotalTax($taxAmount);

								$results[] = array(
									'name'=>$val['name'],
									'value'=>Price_Formatter::formatNumber($taxAmount),
									'raw'=>$taxAmount,
									'type'=>$val['type'],
								);	    

								if(!$tax_in_price){
									$sub_total = CCart::apply($sub_total,$taxAmount);  
								}								
							}
		    			} else {
	    					$results[] = array(
	    					 'name'=>$val['name'],
	    					 'value'=>isset($value['value'])?$value['value']:0,
	    					 'raw'=>isset($value['raw'])?$value['raw']:0,
	    					 'type'=>$val['type'],
	    					);	    					
	    					$sub_total = CCart::apply($sub_total,$val['value']);    				
		    			}    							    			    					   	
    				}
    			}
    			
    			$value = CCart::summary( $sub_total );
    			$results[]=array(
    			  'name'=>t("Total"),
    			  'value'=> isset($value['value'])?$value['value']:0,
    			  'raw'=>isset($value['raw'])?$value['raw']:0,
    			  'type'=>'total',
    			);
    		} else {
    			$value = CCart::summary( $sub_total );
    			$results[]=array(
    			  'name'=>t("Sub total"),
    			  'value'=> isset($value['value'])?$value['value']:0,
    			  'raw'=>isset($value['raw'])?$value['raw']:0,
    			  'type'=>'subtotal',
    			);
    			$results[]=array(
    			  'name'=>t("Total"),
    			  'value'=> isset($value['value'])?$value['value']:0,
    			  'raw'=>isset($value['raw'])?$value['raw']:0,
    			  'type'=>'total',
    			);
    		}
    		    		
    		return $results;
    	}
    	return false;
    }
    
    public static function parseItemPrice($value='')
    {
    	$price = 0;
    	if(is_array($value) && count($value)>=1){
    		if ($value['discount']>0 && $value['discount_valid']){
    			$raw_price = isset($value['price'])?floatval($value['price']):0;
    			$raw_discount = isset($value['discount'])?floatval($value['discount']):0;
    			if ( $value['discount_type']=="percentage"){    				
    				$price = floatval($raw_price) - ((floatval($raw_discount)/100)*floatval($raw_price));
    			} else $price = floatval($raw_price) - floatval($raw_discount);
    		} else $price = floatval($value['price']);
    	}
    	return $price;
    }
    
    public static function apply($total=0, $condition_val=0,$max_discount_cap=0)
    {    	
    	$results = 0;    	
    	if ( CCart::valueIsPercentage($condition_val)){
    		
    		$value = (float) CCart::cleanValue($condition_val);    		
    		$raw_value = (float)$total * ($value/100);    		    		
			
			if($max_discount_cap>0){
				if($raw_value>$max_discount_cap){
					$raw_value = $max_discount_cap;
				}
			}
    		
    		if ( CCart::valueIsToBeSubtracted($condition_val)){ 
    			$results = floatval($total) - floatval($raw_value);
    		} else $results = floatval($total) + floatval($raw_value);
    	} else {
    		$raw_value = (float) CCart::cleanValue($condition_val); 
    		if ( CCart::valueIsToBeSubtracted($condition_val)){
    			$results = floatval($total) - floatval($raw_value);
    		} else $results = floatval($total) + floatval($raw_value);
    	}
    	return $results;
    }
    
    public static function summary($condition_val=0,$total=0,$max_discount_cap=0)
    {    	
    	$results = '';  
    	$raw_value = (float) CCart::cleanValue($condition_val);     	
    	if ( CCart::valueIsPercentage($condition_val)){    		    		
    		$value = (float) CCart::cleanValue($condition_val);        		
    		$raw_value = (float)$total * ($value/100);        		

			if($max_discount_cap>0){
				if($raw_value>$max_discount_cap){
					$raw_value = $max_discount_cap;
				}
			}

    		if ( CCart::valueIsToBeSubtracted($condition_val)){ 
    			$total  = t("({{total}})",array(
    			 '{{total}}'=>Price_Formatter::formatNumber($raw_value)
    			));
    			$results = array(
    			  'value'=>$total,
    			  'raw'=>$raw_value
    			);
    		} else $results = array(
    		  'value'=>Price_Formatter::formatNumber($raw_value),
    		  'raw'=>$raw_value,
    		);
    	} else {    		
    		if ( CCart::valueIsToBeSubtracted($condition_val)){    			
    			$total  = t("({{total}})",array(
    			 '{{total}}'=>Price_Formatter::formatNumber($raw_value)
    			));
    			$results = array(
    			  'value'=>$total,
    			  'raw'=>$condition_val
    			);
    		} else $results = array(
    		  'value'=>Price_Formatter::formatNumber($raw_value),
    		  'raw'=>$raw_value
    		);
    	}    	
    	return $results;
    }
    
    public static function getPackagingFee()
    {
    	if(CCart::$packaging_fee>0){
    		return CCart::$packaging_fee;
    	}
    	return false;
    }
    
    public static function savedAttributes($cart_uuid='',$meta_name='', $meta_id='')
    {    	
		$model = AR_cart_attributes::model()->find('cart_uuid=:cart_uuid AND meta_name=:meta_name', 
		   array(
		      ':cart_uuid'=>$cart_uuid, 
		     ':meta_name'=> $meta_name )
		   ); 
		   		   
		if($model){			
			if($model->meta_id!=$meta_id){
				$model->meta_id = $meta_id;
				$model->update();
			}
		} else {
			$insert = new AR_cart_attributes;
			$insert->cart_row=0;
			$insert->cart_uuid=$cart_uuid;
			$insert->meta_name=$meta_name;
			$insert->meta_id = $meta_id;
			$insert->save();
		}
    }
    
    public static function getAttributes($cart_uuid='',$meta_name='')
    {
    	$model = AR_cart_attributes::model()->find('cart_uuid=:cart_uuid AND meta_name=:meta_name', 
		   array(
		      ':cart_uuid'=>$cart_uuid, 
		     ':meta_name'=> $meta_name )
		   ); 
		if($model){
			return $model;
		}
		return false;
    }
    
    public static function deleteAttributes($cart_uuid='',$meta_name='')
    {
    	$model = AR_cart_attributes::model()->find('cart_uuid=:cart_uuid AND meta_name=:meta_name', 
		   array(
		      ':cart_uuid'=>$cart_uuid, 
		     ':meta_name'=> $meta_name )
		   ); 
		if($model){
			if($model->delete()){
				return true;
			}
		}
		return false;
    }
    
    public static function deleteAttributesAll($cart_uuid='',$meta = array() ) 
    {    	
    	$meta_name = '';    
    	foreach ($meta as $val) {
    		$meta_name.=q($val).",";
    	}
    	$meta_name = substr($meta_name,0,-1);
    	
    	$stmt="
    	DELETE
    	FROM {{cart_attributes}}
    	WHERE cart_uuid=".q($cart_uuid)."
    	AND meta_name IN ($meta_name)
    	";    	
    	if(Yii::app()->db->createCommand($stmt)->query()){
    		return true;
    	}
    	return false;
    }
    
    public static function getAttributesAll($cart_uuid='',$meta = array() ) 
    {    	
    	$meta_name = '';    
    	foreach ($meta as $val) {
    		$meta_name.=q($val).",";
    	}
    	$meta_name = substr($meta_name,0,-1);
    	
    	$stmt="
    	SELECT meta_name,meta_id
    	FROM {{cart_attributes}}
    	WHERE cart_uuid=".q($cart_uuid)."
    	AND meta_name IN ($meta_name)
    	";    	    	
    	if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
    		$data = array();    		
    		foreach ($res as $val) {
    			$data[$val['meta_name']]=$val['meta_id'];    			
    		}
    		return $data;
    	}
    	return false;
    }
    
    public static function cartTransaction($cart_uuid='',$meta_name='',$merchant_id='')
    {    	
    	$transaction='';
    	$stmt="
    	SELECT a.service_code
    	FROM {{services}} a
    	WHERE 
		a.status='publish'
		and
		a.service_code IN (
		  select meta_value from {{merchant_meta}}
		  where meta_name='services'
		  and merchant_id = ".q($merchant_id)."
		  and meta_value IN (
		    select meta_id from {{cart_attributes}}
		    where cart_uuid = ".q($cart_uuid)."
		    and meta_name = ".q($meta_name)."
		  )
		)
    	";    	    	    	    	    	
		$dependency = CCacheData::dependency();					
		if($res = Yii::app()->db->cache(Yii::app()->params->cache, $dependency)->createCommand($stmt)->queryRow()){		   		   
    		$transaction = $res['service_code'];
    	} else $transaction = CCheckout::getFirstTransactionType($merchant_id,Yii::app()->language);
    	return $transaction;
    }
   
    public static function getDistanceOld($cart_uuid='',$merchant_id='',$current_place_id='')
	{
		$stmt="
		SELECT a.reference_id as place_id, a.latitude,a.longitude,a.address1,a.address2,a.street_number,
		a.country,a.postal_code,a.formatted_address,
		(
		  select concat(latitude,',',lontitude,',',distance_unit,',',delivery_distance_covered)
		  from {{merchant}}
		  where merchant_id = ".q($merchant_id)."
		) as merchant_location,
		
		(
		select meta_id from {{cart_attributes}}
		where cart_uuid = ".q($cart_uuid)."
		and meta_name ='address'
		) as address_components
		
		FROM {{map_places}} a
		WHERE reference_id  = (
		  select meta_id from {{cart_attributes}}
		  where cart_uuid = ".q($cart_uuid)."
		  and meta_name = ".q(Yii::app()->params->local_id)."
		)
		";					
		if($res = Yii::app()->db->createCommand($stmt)->queryRow()){							
			
			$merchant_location = explode(",",$res['merchant_location']);
			$merchant_lat = isset($merchant_location[0])?$merchant_location[0]:'';
			$merchant_lng = isset($merchant_location[1])?$merchant_location[1]:'';	
			$unit = isset($merchant_location[2])?$merchant_location[2]:Yii::app()->params['settings']['home_search_unit_type'];	
			$distance_covered = isset($merchant_location[3])?$merchant_location[3]:'';	
			
			$atts_data = array('delivery_distance','delivery_distance_unit','distance_covered','merchant_lat','merchant_lng');
			$atts = CCart::getAttributesAll($cart_uuid,$atts_data);						
			$atts_merchant_lat = isset($atts['merchant_lat'])?$atts['merchant_lat']:'';
			$atts_merchant_lng = isset($atts['merchant_lng'])?$atts['merchant_lng']:'';
					
			$place_id = $res['place_id'];
			$customer_lat = $res['latitude'];
			$customer_lng = $res['longitude'];
			
			if ( $address = json_decode($res['address_components'],true)){
				$customer_lat = $address['latitude'];
				$customer_lng = $address['longitude'];
			}					
						
			MapSdk::$map_provider = Yii::app()->params['settings']['map_provider'];		   
		    MapSdk::setKeys(array(
		     'google.maps'=>Yii::app()->params['settings']['google_geo_api_key'],
		     'mapbox'=>Yii::app()->params['settings']['mapbox_access_token'],
		    ));
		    		    		    		    		    		  
		    MapSdk::setMapParameters(array(
		      'from_lat'=>$merchant_lat,
		      'from_lng'=>$merchant_lng,
		      'to_lat'=>$customer_lat,
		      'to_lng'=>$customer_lng,
		      'place_id'=>$place_id,
		      'unit'=>$unit,
		      'mode'=>'driving'
		    ));
		    		    			    
		    if($resp = MapSdk::distance()){
		    	$resp['found']=false;
		    	$resp['distance_covered'] = $distance_covered;
		    	$resp['merchant_lat']=$merchant_lat;
		    	$resp['merchant_lng']=$merchant_lng;
		    	return $resp;
		    }
		} else CCart::savedAttributes($cart_uuid, Yii::app()->params->local_id, $current_place_id);
		return false;
	}			
		
	public static function shippingRate($merchant_id='',$charge_type='',$shipping_type='', $distance='' , $unit='',$transaction_type='')
	{
		$shipping_type = !empty($shipping_type)?$shipping_type:'standard';
				
		$and = "";
		if($charge_type=="dynamic"){
			$and.="
			AND a.distance_from<=".q( floatval($distance) )."
		    AND a.distance_to>=".q( floatval($distance) )."		
		    AND a.shipping_units = ".q($unit)."   
			";
		}
		
		$stmt="
		SELECT 
		a.id, 
		a.charge_type,
		a.shipping_type,
		a.distance_from,
		a.distance_to,
		a.shipping_units,
		a.distance_price,
		a.minimum_order,
		a.minimum_order,
		a.maximum_order,
		a.estimation
		
		FROM {{shipping_rate}} a
		WHERE a.merchant_id = ".q($merchant_id)."				
		AND a.charge_type=".q($charge_type)."
		AND a.shipping_type=".q($shipping_type)."	
		AND a.service_code=".q($transaction_type)."
		$and	
		";					
		if($res = Yii::app()->db->createCommand($stmt)->queryRow()){
			return $res;
		} 
		return false;
	}
	
	public static function getMaxMinEstimationOrder($merchant_id="",$transaction_type='' , $charge_type='',$distance='', $unit='')
	{
		$resp = array();
		//dump("$transaction_type=>$charge_type=>$distance=>$unit");
		if($transaction_type=="delivery" && $charge_type=="dynamic"){
			$resp = CCart::shippingRate($merchant_id,$charge_type,'standard',$distance,$unit,$transaction_type);
		} else if ( $transaction_type=="delivery" && $charge_type=="fixed" ) {
			$resp = CCart::shippingRate($merchant_id,$charge_type,'',0,'',$transaction_type);
		} else if ( $transaction_type=="pickup" || $transaction_type =="dinein") {
			$resp = CCart::getEstimation($merchant_id,$transaction_type);
		} 		
		if(is_array($resp) && count($resp)>=1){
			return $resp;
		}
		return false;
	}
	
	public static function getEstimation($merchant_id='',$transaction_type='')
	{
		$stmt="
		SELECT 
		estimation,minimum_order,maximum_order
		FROM {{shipping_rate}}
		WHERE service_code = ".q($transaction_type)."
		AND merchant_id = ".q($merchant_id)."
		LIMIT 0,1
		";				
		if( $res = Yii::app()->db->createCommand($stmt)->queryRow() ){
			return $res;
		}
		return false;	
	}
	
	public static function cartCondition($cart_uuid='', $condition_value = CCart::CONDITION_NAME)
	{
		$in = CommonUtility::arrayToQueryParameters( $condition_value );		
		$stmt="
		SELECT id,meta_name,meta_id as meta_value
		FROM {{cart_attributes}}
		WHERE cart_uuid = ".q($cart_uuid)."
		AND meta_name IN ($in)
		";		
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
			return $res;
		}
		return false;	
	}
	
	public static function getTips($cart_uuid='',$merchant_id='', $merchant_default_tip=0)
	{
		if ( $tips = CCart::getAttributes($cart_uuid,'tips')){
			if($tips->meta_id>0){
				return floatval($tips->meta_id);
			}
		} else {
			if($merchant_default_tip>0){
				return floatval($merchant_default_tip);
			}
		}
		return false;
	}
	
	public static function getLocalDistance($local_id='',$unit='',$merchant_lat='',$merchant_lng='')
	{		
		if(!empty($local_id)){
			$model = AR_map_places::model()->find('reference_id=:reference_id',
			    array(':reference_id'=>$local_id
			)); 		

			if(!$model){
				try {
					$resp = CMaps::locationDetails($local_id);						
					$distance = CMaps::getLocalDistance($unit,$resp['latitude'],$resp['longitude'],$merchant_lat,$merchant_lng);
					return [
						'distance'=>$distance,
						'address_component'=>array(
							'place_id'=>$resp['place_id'],
							'latitude'=>$resp['latitude'],
							'longitude'=>$resp['longitude'],
							'address1'=>$resp['parsed_address']['street_number'],
							'address2'=>$resp['parsed_address']['street_name'],
							'formatted_address'=>$resp['parsed_address']['formatted_address'],
						)
					];
				} catch (Exception $e) {					
					return false;
				}				
			}

			if($model){			
				$distance = CMaps::getLocalDistance($unit,$model->latitude,$model->longitude,$merchant_lat,$merchant_lng);
				return array(
				 'distance'=>$distance,
				 'address_component'=>array(
				   'place_id'=>$model->reference_id,
				   'latitude'=>$model->latitude,
				   'longitude'=>$model->longitude,
				   'address1'=>$model->address1,
				   'street_number'=>$model->street_number,
				   'address2'=>$model->address2,
				   'formatted_address'=>$model->formatted_address
				 )
				);
			}
		}
		return false;
	}
	
	public static function getDistance($client_id='', $place_id='',$unit='',$merchant_lat='',$merchant_lng='')
	{
		 if(!empty(MapSdk::$map_provider)){
			 //
		 } else {
			MapSdk::$map_provider = Yii::app()->params['settings']['map_provider'];		   			
			if(MapSdk::$map_provider=="yandex"){
				//MapSdk::$map_provider = 'google.maps';
				MapSdk::$map_provider = 'mapbox';
			}			
			MapSdk::setKeys(array(
				'google.maps'=>Yii::app()->params['settings']['google_geo_api_key'],
				'mapbox'=>Yii::app()->params['settings']['mapbox_access_token'],
				'yandex'=>isset(Yii::app()->params['settings']['yandex_distance_api'])?Yii::app()->params['settings']['yandex_distance_api']:'',
			));
		 }		 
	     		   
	     try {
	     	$address = CClientAddress::getAddress($place_id,$client_id);
	     	$params = array(
		      'from_lat'=>$merchant_lat,
		      'from_lng'=>$merchant_lng,
		      'to_lat'=>$address['latitude'],
		      'to_lng'=>$address['longitude'],		      
		      'unit'=>$unit,
		      'mode'=>'driving'
		    );		    		    
	     	MapSdk::setMapParameters($params);		    
		    $distance =  MapSdk::distance();			
			
		    return array(
			 'distance'=>$distance['distance'],
			 'duration_value'=>isset($distance['duration_value'])?$distance['duration_value']:0,
			 'address_component'=>array(
			   'place_id'=>$address['place_id'],
			   'latitude'=>$address['latitude'],
			   'longitude'=>$address['longitude'],
			   'address1'=>$address['address']['address1'],
			   'street_number'=>$address['address']['street_number'],
			   'address2'=>$address['address']['address2'],
			   'formatted_address'=>$address['address']['formatted_address'],
			 )
			);		    	     	
	     } catch (Exception $e) {	     				
			 throw new Exception($e->getMessage());	     	
	     }
	}	

	public static function addOrderToCart($merchant_id=0,$items=array())
	{		
		$cart_uuid = CommonUtility::generateUIID();
		if(is_array($items) && count($items)>=1){
			foreach ($items as $val) {
				$cart_row = CommonUtility::generateUIID();				
				$items = new AR_cart;
				$items->cart_row = $cart_row;
				$items->cart_uuid = $cart_uuid;
				$items->merchant_id = intval($merchant_id);
				$items->cat_id = intval($val['cat_id']);
				$items->item_token = $val['item_token'];
				$items->item_size_id = intval($val['price']['item_size_id']);
				$items->qty = (integer)$val['qty'];
				$items->special_instructions = $val['special_instructions'];
				$items->save();	
				
				$builder=Yii::app()->db->schema->commandBuilder;			
				
				// addon
				$item_addons = array();
				if(is_array($val['addons']) && count($val['addons'])>=1){
					foreach ($val['addons'] as $addons) {						
						$subcat_id = $addons['subcat_id'];
						if(is_array($addons['addon_items']) && count($addons['addon_items'])>=1){
							foreach ($addons['addon_items'] as $addon_items) {								
								$item_addons[] = array(
								  'cart_row'=>$cart_row,
								  'cart_uuid'=>$cart_uuid,
								  'subcat_id'=>intval($subcat_id),
								  'sub_item_id'=>intval($addon_items['sub_item_id']),
								  'qty'=>$addon_items['qty'],
								  'multi_option'=>$addon_items['multiple'],
								);
							}							
							$command=$builder->createMultipleInsertCommand('{{cart_addons}}',$item_addons);
					        $command->execute();
						}
					}
				}
											
				// attributes
				$item_attributes = array();
				$attributes = isset($val['attributes_raw'])?$val['attributes_raw']:'';
				if(is_array($attributes) && count($attributes)>=1){
					foreach ($attributes as $meta_name=>$item) {						
						if(is_array($item) && count($item)>=1){
							foreach ($item as $meta_id=> $item_val) {
								$item_attributes[] = array(
								 'cart_row'=>$cart_row,
								 'cart_uuid'=>$cart_uuid,
								 'meta_name'=>$meta_name,
								 'meta_id'=>intval($meta_id),
								);
							}							
						}						
					}
					$command=$builder->createMultipleInsertCommand('{{cart_attributes}}',$item_attributes);
					$command->execute();
				}
				
			} /*each item*/
			return $cart_uuid;
		}
		throw new Exception( 'order has no item' );
	}

	public static function validateFoodItems($item_token='',$cart_uuid='', $language='')
	{
		$stmt = "
		SELECT 
		a.item_token, 
		a.merchant_id,		
		IF(COALESCE(NULLIF(c.item_name, ''), '') = '', b.item_name, c.item_name) as item_name,
		merchant.merchant_uuid,
		merchant.restaurant_slug

		FROM {{cart}} a
		LEFT JOIN {{item}} b
		ON a.item_token = b.item_token

		LEFT JOIN (
			SELECT item_id, item_name FROM {{item_translation}} where language = ".q($language)."
		) c
		on b.item_id = c.item_id

		LEFT JOIN {{merchant}} merchant
		ON
		a.merchant_id = merchant.merchant_id

		WHERE 
		a.cart_uuid=".q($cart_uuid)."
		AND
		a.item_token=".q($item_token)."
		";				
		$dependency = CCacheData::dependency();					
        if($res = Yii::app()->db->cache(Yii::app()->params->cache, $dependency)->createCommand($stmt)->queryAll()){
			$data = []; $item_line='';  $merchant_id = ''; $merchant_uuid=''; $restaurant_slug='';
			foreach ($res as $items) {				
				$item_name = CHtml::decode($items['item_name']) ?? '';
				$item_line.= "$item_name,";
				$merchant_id = $items['merchant_id'] ?? null;
				$merchant_uuid = $items['merchant_uuid'] ?? null;
				$restaurant_slug = $items['restaurant_slug'] ?? null;
				$data[] = [					
					'item_token'=>$items['item_token'],
					'item_name'=> $item_name,										
				];
			}
			return [
				'data'=>$data,
				'merchant_id'=>$merchant_id,
				'merchant_uuid'=>$merchant_uuid,
				'restaurant_slug'=>$restaurant_slug,
				'count'=>count($res),
				'item_line'=>substr($item_line,0,-1),
			];
		}
		throw new Exception( t(HELPER_NO_RESULTS) );
	}

	public static function setTotalTax($tax_amount=0)
	{
		if($tax_amount>0){
			self::$tax_total = $tax_amount;
		}
	}

	public static function getTotalTax()
	{
		return floatval(self::$tax_total);
	}

	public static function setExchangeRate($exchange_rate=0)
	{
		if($exchange_rate>0){
			self::$exchange_rate = $exchange_rate;
		} else {
			self::$exchange_rate = 1;
		}
	}

	public static function getExchangeRate()
	{		
		return self::$exchange_rate>0? floatval(self::$exchange_rate) : 1;
	}
	
	
	public static function setAdminExchangeRate($exchange_rate=0)
	{
		if($exchange_rate>0){
			self::$admin_exchange_rate = $exchange_rate;
		} else {
			self::$admin_exchange_rate = 1;
		}
	}

	public static function getAdminExchangeRate()
	{	
		return self::$admin_exchange_rate>0? floatval(self::$admin_exchange_rate) : 1 ;
	}

	public static function get($cart_uuid='')
	{				
		$dependency = CCacheData::dependency();
		$model = AR_cart::model()->cache(Yii::app()->params->cache, $dependency)->find('cart_uuid=:cart_uuid',array(':cart_uuid'=>$cart_uuid)); 
		if($model){
			return $model;
		}
		throw new Exception( 'no results' );
	}

	public static function setPointsRate($points_enabled=false,$earning_rule='',$earning_points=0,$points_minimum_purchase=0,$points_maximum_purchase=0)
	{
		self::$points_enabled = $points_enabled;
		self::$points_earning_rule = $earning_rule;
		self::$points_earning_points = $earning_points;
		self::$points_minimum_purchase = $points_minimum_purchase;
		self::$points_maximum_purchase = $points_maximum_purchase;
	}

	public static function getEnabledPoints()
	{
		return self::$points_enabled?self::$points_enabled:false;
	}

	public static function getPointsRule()
	{
		return !empty(self::$points_earning_rule)? trim(self::$points_earning_rule) : 'sub_total';
	}

	public static function getEarningPoints()
	{
		return self::$points_earning_points>0? floatval(self::$points_earning_points) : 0;
	}
	
	public static function getPointsMinimumPurchase()
	{
		return self::$points_minimum_purchase>0? floatval(self::$points_minimum_purchase) : 0;
	}

	public static function getPointsMaximumPurchase()
	{
		return self::$points_maximum_purchase>0? floatval(self::$points_maximum_purchase) : 0;
	}
	
	//
	public static function setTotalFoodPoints($total=0)
	{
		self::$total_points_item = $total>0?$total:0;
	}

	public static function getTotalFoodPoints()
	{
		return self::$total_points_item>0? floatval(self::$total_points_item) : 0;
	}
	
	public static function getTotalPoints($subtotal=0,$total=0)
	{
		
		$points = 0;
		$points_enabled = self::getEnabledPoints();
		$points_earning_rule = self::getPointsRule();
		$points_earning_points = self::getEarningPoints();
		$points_minimum_purchase = self::getPointsMinimumPurchase();
		$points_maximum_purchase = self::getPointsMaximumPurchase();

		$admin_exchange_rate = self::getAdminExchangeRate();

		// dump("points_minimum_purchase=>$points_minimum_purchase");
		// dump("points_maximum_purchase=>$points_maximum_purchase");
		// dump("subtotal=>$subtotal");
		// dump("admin_exchange_rate=>$admin_exchange_rate");

		$subtotal = ($subtotal*$admin_exchange_rate);
		$total = ($total*$admin_exchange_rate);
		
		// dump("points_earning_rule=>$points_earning_rule");
		// dump("subtotal exchange rate=>$subtotal");
		// dump("total exchange rate=>$total");
		// die();
				
		if($points_enabled && $points_earning_points>0){
			switch ($points_earning_rule) {
				case 'sub_total':		
					if($points_minimum_purchase>0 && $points_minimum_purchase>$subtotal){
						return Price_Formatter::convertToRaw($points,0);
					}
			
					if($points_maximum_purchase>0 && $subtotal>$points_maximum_purchase){			
						$subtotal = $points_maximum_purchase;
					}
					$points = ($subtotal*$points_earning_points);
					break;							
				case 'cart_total':					
					if($points_minimum_purchase>0 && $points_minimum_purchase>$total){
						return Price_Formatter::convertToRaw($points,0);
					}
			
					if($points_maximum_purchase>0 && $total>$points_maximum_purchase){			
						$total = $points_maximum_purchase;
					}
					$points = ($total*$points_earning_points);
					break;							
				case 'food_item':		
					$points = self::getTotalFoodPoints();					
					break;	
				case "fixed_points":						
					$points = $points_earning_points;
					break;	
			}
		}				
		return Price_Formatter::convertToRaw($points,0);
	}

	public static function getAllItemsByToken($data=[],$language=KMRS_DEFAULT_LANGUAGE)
	{
		$stmt = "
		SELECT a.item_id,a.item_token,
		IF(COALESCE(NULLIF(b.item_name, ''), '') = '', a.item_name, b.item_name) as new_item_name
		FROM {{item}} a

		left JOIN (
			SELECT item_id,item_name FROM {{item_translation}} where language=".q($language)."
		) b 
		ON a.item_id = b.item_id

		WHERE a.item_token IN (".CommonUtility::arrayToQueryParameters($data) .")		
		";
		$data = [];
		if($res =CCacheData::queryAll($stmt)){
			foreach ($res as $items) {
				$data[$items['item_token']] = $items['new_item_name'];
			}
		}
		return $data;
	}

	public static function getTimeEstimation($filter=[],$transaction_type='',$currentDateTime='')
	{
		$time_estimation = 30;
		$estimation = CMerchantListingV1::estimationMerchantNew($filter);		
		if($estimation){
			$charges_type = isset($filter['charges_type'])?$filter['charges_type']:'fixed';			
			$data = isset($estimation[$transaction_type])?$estimation[$transaction_type]:false;
			$time = isset($data[$charges_type])?$data[$charges_type]:false;
			$timeestimation = isset($time['estimation'])?$time['estimation']:0;			
			$parts = explode("-", $timeestimation);
			$time_estimation = isset($parts[1])?$parts[1]:$time_estimation;			
		}				

		$time_estimation = intval($time_estimation);

		//$currentDateTime = date('Y-m-d H:i:s');
		$tracking_start = date('Y-m-d\TH:i:s', strtotime($currentDateTime));
		$tracking_end = date('Y-m-d\TH:i:s', strtotime($currentDateTime . " +$time_estimation minutes"));			
		return [
			'tracking_start'=>$tracking_start,
			'tracking_end'=>$tracking_end,
			'time_estimation'=>$time_estimation
		];
	}

	public static function getSendOrderTotal($merchant_id='',$cart_uuid='',$table_number='')
	{
		$total = 0;
		$model = AR_cart::model()->find("merchant_id=:merchant_id AND cart_uuid=:cart_uuid AND table_uuid=:table_uuid",[
			':merchant_id'=>$merchant_id,
			':cart_uuid'=>$cart_uuid,
			':table_uuid'=>$table_number,
		]);		
		if($model){
			$total = Price_Formatter::convertToRaw($model->total,2);
		}
		return $total;
	}

	public static function clearItems($cart_uuid='',$merchant_id='')
	{
		$stmt = "
		DELETE FROM {{cart_attributes}}
		WHERE cart_row IN (
			select cart_row from {{cart}}
			where cart_uuid=".q($cart_uuid)."
			and merchant_id=".q($merchant_id)."
			and send_order=0
		)
		";		
		Yii::app()->db->createCommand($stmt)->queryAll();

		$stmt = "
		DELETE FROM {{cart_addons}}
		WHERE cart_row IN (
			select cart_row from {{cart}}
			where cart_uuid=".q($cart_uuid)."
			and merchant_id=".q($merchant_id)."
			and send_order=0
		)
		";		
		Yii::app()->db->createCommand($stmt)->queryAll();
				
		AR_cart::model()->deleteAll('cart_uuid=:cart_uuid AND send_order=:send_order',array(
			':cart_uuid'=>$cart_uuid,
			':send_order'=>0
		));
		return true;  
	}

	public static function ClearCart($cart_uuid='')
	{
		
		AR_cart_attributes::model()->deleteAll('cart_uuid=:cart_uuid',array(
			':cart_uuid'=>$cart_uuid,			
		));
	

		AR_cart_attributes::model()->deleteAll('cart_uuid=:cart_uuid',array(
			':cart_uuid'=>$cart_uuid,			
		));
				
		AR_cart::model()->deleteAll('cart_uuid=:cart_uuid',array(
			':cart_uuid'=>$cart_uuid,			
		));
		return true;  
	}	

	public static function getTotalSendOrder($cart_uuid='',$send_order=0)
	{
		$criteria = new CDbCriteria;        
		$criteria->condition = 'cart_uuid = :cart_uuid AND send_order=:send_order';
		$criteria->params = [
			':cart_uuid'=>$cart_uuid,
			':send_order'=>$send_order
		];
		$model = AR_cart::model()->count($criteria);
		if($model){
			return $model;
		}
		return 0;
	}

	public static function clearNewItems($merchant_id='',$cart_uuid='')
	{
		AR_cart::model()->deleteAll('merchant_id=:merchant_id AND cart_uuid=:cart_uuid AND send_order=:send_order',array(
			':merchant_id'=>$merchant_id,
			':cart_uuid'=>$cart_uuid,
			':send_order'=>0
		));
	}

	public static function getTotalFromKitchen($order_reference='')
	{
		$criteria = new CDbCriteria;        
		$criteria->condition = 'order_reference = :order_reference';
		$criteria->params = [
			':cart_uuid'=>$order_reference,			
		];
		$model = AR_kitchen_order::model()->count($criteria);
		if($model){
			return $model;
		}
		return 0;
	}

	public static function getAddons($cart_uuid='', $lang = KMRS_DEFAULT_LANGUAGE)
	{
		$stmt = "
		SELECT 
		a.cart_row,
		a.sub_item_id,
		a.qty,
		a.multi_option,		
		IF(COALESCE(NULLIF(bb.sub_item_name, ''), '') = '', b.sub_item_name, bb.sub_item_name) as sub_item_name,
		IF(COALESCE(NULLIF(bb.item_description, ''), '') = '', b.item_description, bb.item_description) as item_description

		FROM {{cart_addons}} a
		LEFT JOIN {{subcategory_item}} b
		ON 
		a.sub_item_id = b.sub_item_id

		left JOIN (
			SELECT sub_item_id, sub_item_name,item_description FROM {{subcategory_item_translation}} where language = ".q($lang)."
		) bb
		ON
		a.sub_item_id = bb.sub_item_id

		WHERE
		cart_uuid=".q($cart_uuid)."
		ORDER BY id ASC
		";
		if( $res = Yii::app()->db->createCommand($stmt)->queryAll() ){	
			$data = [];
			foreach ($res as $items) {
				$data[$items['cart_row']][] = [
					'sub_item_id'=>$items['sub_item_id'],
					'qty'=>$items['qty'],
					'multi_option'=>$items['multi_option'],
					'sub_item_name'=>$items['sub_item_name'],
					'item_description'=>$items['item_description'],
				];
			}
			return $data;
		}			
		return false;
	}

	public static function getCooking($cart_uuid='', $lang = KMRS_DEFAULT_LANGUAGE,$meta_name='cooking_ref')
	{		
		$stmt = "
		SELECT 
		a.cart_row,
		a.meta_name,
		a.meta_id,		
		IF(COALESCE(NULLIF(bb.cooking_name, ''), '') = '', b.cooking_name, bb.cooking_name) as cooking_name

		FROM {{cart_attributes}} a

		LEFT JOIN {{cooking_ref}} b
		ON
		a.meta_id = b.cook_id

		left JOIN (
			SELECT cook_id,cooking_name FROM {{cooking_ref_translation}} where language = ".q($lang)."
		) bb
		ON
		a.meta_id = bb.cook_id

		WHERE
		a.cart_uuid = ".q($cart_uuid)."
		AND a.meta_name = ".q($meta_name)."		
		";		
		if( $res = Yii::app()->db->createCommand($stmt)->queryAll() ){	
			$data = [];
			foreach ($res as $items) {
				$data[$items['cart_row']][] = CHtml::encode($items['cooking_name']);
			}
			return $data;
		}
		return false;
	}

	public static function getIngredients($cart_uuid='', $lang = KMRS_DEFAULT_LANGUAGE,$meta_name='ingredients')
	{
		$stmt = "
		SELECT 
		a.cart_row,
		a.meta_name,
		a.meta_id,		
		IF(COALESCE(NULLIF(bb.ingredients_name, ''), '') = '', b.ingredients_name, bb.ingredients_name) as ingredients_name

		FROM {{cart_attributes}} a

		LEFT JOIN {{ingredients}} b
		ON
		a.meta_id = b.ingredients_id

		left JOIN (
			SELECT ingredients_id,ingredients_name FROM {{ingredients_translation}} where language = ".q($lang)."
		) bb
		ON
		a.meta_id = bb.ingredients_id

		WHERE
		a.cart_uuid = ".q($cart_uuid)."
		AND a.meta_name = ".q($meta_name)."		
		";		
		if( $res = Yii::app()->db->createCommand($stmt)->queryAll() ){				
			$data = [];
			foreach ($res as $items) {
				$data[$items['cart_row']][] = CHtml::encode($items['ingredients_name']);
			}
			return $data;
		}
		return false;
	}

	public static function setTotalPreparationTime($total=0)
	{
		self::$total_preparation_time = $total>0?$total:0;
	}

	public static function getPreparationtime()
	{
		return self::$total_preparation_time>0? floatval(self::$total_preparation_time) : 0;		
	}

	public static function kicthenWorkLoad($merchant_id=0, $date='')
	{
		$total = 0;
		$status = COrders::getStatusAllowedToCancel();		
		if(!is_array($status)){
			$status = ['new','accepted'];
		};
		$stmt = "
		SELECT a.merchant_id,
		b.order_count,
		CASE
		    WHEN b.order_count < a.low_workload_max_orders THEN a.low_workload_extra_time
			WHEN b.order_count BETWEEN a.medium_workload_min_orders AND a.medium_workload_max_orders THEN a.medium_workload_extra_time
            WHEN b.order_count >= a.high_workload_min_orders THEN a.high_workload_extra_time
		END AS extra_time_minutes

		FROM {{kitchen_workload_settings}} a

		JOIN (
			SELECT 
				merchant_id,
				COUNT(*) AS order_count
			FROM {{ordernew}}
			WHERE status IN ( ".CommonUtility::arrayToQueryParameters($status)." )
			GROUP BY merchant_id
		) AS b ON b.merchant_id = a.merchant_id

		WHERE a.merchant_id = ".q($merchant_id)."
		";		
		if($res = Yii::app()->db->createCommand($stmt)->queryRow()){			
			$total = $res['extra_time_minutes'];
		}
		return $total;
	}

	public static function totalPreparationTime($merchant_id = 0 , $date='')
	{
		 $prep_time = self::getPreparationtime();
		 $work_time = self::kicthenWorkLoad($merchant_id,$date);		 
		 $prepTime = intval($prep_time)+intval($work_time);
		 return $prepTime>0?$prepTime:10;
	}
	
	public static function getDeliveryEstimation($distance=0, $average_speed=40)
	{
		$estimated_delivery_time = ($distance / $average_speed) * 60;
		return round($estimated_delivery_time);
	}

	public static function getCartWithMerchant($cart_uuid='')
	{
		$stmt = "
		SELECT cart.cart_uuid, cart.merchant_id,
		merchant.restaurant_name
		FROM {{cart}} cart
		LEFT JOIN {{merchant}} merchant
		ON
		cart.merchant_id = merchant.merchant_id
		AND cart.cart_uuid=".q($cart_uuid)."
		";
		if($res = Yii::app()->db->createCommand($stmt)->queryRow()){
			return $res;
		}
		throw new Exception( t(HELPER_RECORD_NOT_FOUND) );
	}

	public static function getCartDetails($cart_row='')
	{
		$model_cart = AR_cart::model()->find("cart_row=:cart_row",[
			':cart_row'=>$cart_row
		]);
		if($model_cart){
			$addons = []; $attributes=[];
			$data_cart = [
				'item_size_id'=>$model_cart->item_size_id,
				'qty'=>$model_cart->qty,
				'special_instructions'=>$model_cart->special_instructions,
				'if_sold_out'=>$model_cart->if_sold_out,
			];

			$addons = array_map(fn($item) => [
				'subcat_id' => $item->subcat_id,
				'sub_item_id' => $item->sub_item_id,
				'qty' => $item->qty,
				'multi_option' => $item->multi_option,
			], AR_cart_addons::model()->findAll("cart_row=:cart_row", [':cart_row' => $cart_row]) ?: []);			

			if($model_atts = AR_cart_attributes::model()->findAll("cart_row=:cart_row", [':cart_row' => $cart_row])){
				foreach ($model_atts as $items) {
					if($items->meta_name=="cooking_ref"){
						$attributes['cooking_ref'] = $items->meta_id;
					} else if ( $items->meta_name=="ingredients"){
						$attributes['ingredients'][] = $items->meta_id;
					}
				}
			}

			return [
				'cart_row'=>$cart_row,
				'cart'=>$data_cart,
				'addons'=>$addons,
				'attributes'=>$attributes
			];
		}
		return false;
	}

	public static function getRow($cart_row='')
	{
		$model_cart = AR_cart::model()->find("cart_row=:cart_row",[
			':cart_row'=>$cart_row
		]);
		if($model_cart){
			return $model_cart;
		}
		return false;
	}

	public static function getCartSubtotal($cart_uuid='',$promos=[])
	{
		 $subtotal = 0;

		 $promo_ids = [];
		 if(is_array($promos) && count($promos)>=1){
			foreach ($promos as $items) {
				$promo_ids[] = $items['free_item_id'];
			}
		 }

		 CCart::getContent($cart_uuid,Yii::app()->language);
		 $cartItems = CCart::getItems();		 
		 if(is_array($cartItems) && count($cartItems)>=1){
			foreach ($cartItems as $items) {				
				if(!in_array($items['item_id'],(array)$promo_ids)){
					$subtotal+=$items['subtotal'];
				}				
			}
		 }
		 return $subtotal;
	}

	public static function applyAutoAddPromo($cart_uuid='', $merchant_id = 0)
	{
		 $promos = CMerchantMenu::getItemAutoAddPromo($merchant_id);		 
         if (empty($promos) || !is_array($promos)) {			
			return;
		 }		 
		 
		 $subtotal = self::getCartSubtotal($cart_uuid,$promos);		 
		
		 foreach ($promos as $promo) {
			$free_item_id = $promo['free_item_id'];			
			$free_item_token = $promo['item_token'];		
			$minimum_cart_total = $promo['minimum_cart_total'];
			$model_cart = AR_cart::model()->find("cart_uuid=:cart_uuid AND merchant_id=:merchant_id AND item_token=:item_token",[
				':cart_uuid'=>$cart_uuid,
				':merchant_id'=>$merchant_id,
				':item_token'=>$free_item_token
			]);
			if ($subtotal >= $minimum_cart_total) {				
				$free_item = [
					'merchant_id'=>$merchant_id,
					'cart_row'=>CommonUtility::generateUIID(),
					'cart_uuid'=>$cart_uuid,
					'cat_id'=>$promo['cat_id'],
					'item_token'=>$promo['item_token'],
					'item_size_id'=>$promo['item_size_id'],
					'qty'=>$promo['max_free_quantity'],
					'item_total'=>0,
					'addons'=>'',
					'attributes'=>''
				];
				CCart::add($free_item);				
			} else {
				 if ($model_cart) {
				     $model_cart->delete();
				 }				
			}
		 }
	}

	public static function getCartTransactionType($cart_uuid='',$merchant_id='')
	{
		$transaction_type = '';
		if($cart_uuid){
			$transaction_type = CCart::cartTransaction($cart_uuid,Yii::app()->params->local_transtype,$merchant_id);			
		} else {
			$transaction_resp = CCheckout::getFirstTransactionType($merchant_id,Yii::app()->language);			
			$transaction_type = $transaction_resp ? $transaction_resp : 'delivery';
		}				
		return $transaction_type;
	}

	public static function validateItemPromo($item_token='',$cart_uuid='')
	{
		$stmt = "
		SELECT a.item_token
		FROM {{item_free_promo}} a	
		WHERE a.item_token IN (
		  select item_token from {{cart}}
		  where item_token=:item_token
		  and cart_uuid=:cart_uuid
		)
		";
		$command = Yii::app()->db->createCommand($stmt);
		$command->bindValues([
			':item_token' => $item_token,			
			':cart_uuid' => $cart_uuid,	
		]);
		if($res = $command->queryRow()){			
			throw new Exception( t("Item Promo already added") );
		}
		return true;
	}

}
/*end class*/