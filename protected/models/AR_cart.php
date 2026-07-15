<?php
class AR_cart extends CActiveRecord
{	

	public $customer_name,$items_data,$table_name,$transaction_type,
	$room_name,$total_in_kicthen,$merchant_uuid,$item_name,$item_status,$table_status,$kicthen_merchant_uuid,
	$size_name,$item_price;

	public $restaurant_name;
	/**
	 * Returns the static model of the specified AR class.
	 * @return static the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{cart}}';
	}
	
	public function primaryKey()
	{
	    return 'cart_row';	 
	}
		
	public function attributeLabels()
	{
		return array(
		  'item_token'=>t("item_token")
		);
	}
	
	public function rules()
	{
		return array(

		  array('special_instructions', 'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')),  
		
		  array('cart_row,cart_uuid,item_token,item_size_id,qty', 
		  'required','message'=> t( "Required" ) ),
		  
		  array('special_instructions,hold_order,order_reference,date_created,
		  date_modified,ip_address,send_order,payment_status,total,table_uuid,transaction_type,hold_order_reference,change_trans,is_view,item_total','safe'),

		  //array('order_reference','unique','message'=>t(Helper_field_unique) , 'on'=>'hold_cart' ),

		  array('hold_order_reference','ext.UniqueAttributesValidator','with'=>'merchant_id',
		   'message'=>t(Helper_field_unique),'on'=>'hold_cart'
		  ),
		  		  
		);
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){
			if($this->isNewRecord){				
				$this->date_created = CommonUtility::dateNow();					
			} else {
				$this->date_modified = CommonUtility::dateNow();											
			}
			$this->ip_address = CommonUtility::userIp();	
			
			return true;
		} else return true;
	}
	
	protected function afterSave()
	{					
		switch ($this->scenario) {
			case "update_qty":
				$model = AR_cart::model()->findAll("cart_uuid=:cart_uuid AND send_order=:send_order",[
					":cart_uuid"=>$this->cart_uuid,
					":send_order"=>1
				]);
				if($model){
					foreach ($model as $items) {
						$modelKitchen = AR_kitchen_order::model()->find("order_ref_id=:order_ref_id",[
							':order_ref_id'=>$items->cart_row
						]);
						if(!$modelKitchen){
							$modelKitchen = new AR_kitchen_order();
						}

						$addons = []; 

						$model_cart_addons = AR_cart_addons::model()->findAll("cart_row=:cart_row",[
							':cart_row'=>$items->cart_row
						]);
						if($model_cart_addons){
							foreach ($model_cart_addons as $cart_addons) {
								$addons[] = [
									'subcat_id'=>$cart_addons->subcat_id,
									'sub_item_id'=>$cart_addons->sub_item_id,
									'qty'=>$cart_addons->qty,
									'multi_option'=>$cart_addons->multi_option
								];
							}
						}
						$modelKitchen->addons = json_encode($addons);
						$modelKitchen->qty = $items->qty;
						$modelKitchen->save();

					}
				}
				break;

			case 'send_order':		
				
				$kitchen_uuid = '';
				$order_reference = '';
				$whento_deliver = '';
				$merchant_uuid = '';
				$merchant_id = '';
				
				$model = AR_cart::model()->findAll("cart_uuid=:cart_uuid AND send_order=:send_order",[
					":cart_uuid"=>$this->cart_uuid,
					":send_order"=>0
				]);
				if($model){					

					$attrs = CCart::getAttributesAll($this->cart_uuid,[
						'table_uuid','room_uuid','customer_name','transaction_type','timezone','delivery_date','whento_deliver','delivery_time'
					]);          
					$room_uuid = isset($attrs['room_uuid'])?$attrs['room_uuid']:'';  					
                    $customer_name = isset($attrs['customer_name'])?$attrs['customer_name']:'';
					$transaction_type = isset($attrs['transaction_type'])?$attrs['transaction_type']:'';
					$timezone = isset($attrs['timezone'])?$attrs['timezone']:'';
					$delivery_date = isset($attrs['delivery_date'])?$attrs['delivery_date']:'';
					$whento_deliver = isset($attrs['whento_deliver'])?$attrs['whento_deliver']:'';
					$delivery_time = isset($attrs['delivery_time'])?$attrs['delivery_time']:'';

					foreach ($model as $items) {						
						$modelKitchen = new AR_kitchen_order();
						$modelKitchen->order_reference = $this->order_reference;
						//kicthen_merchant_uuid $this->merchant_uuid;
						$modelKitchen->merchant_uuid =  !empty($this->kicthen_merchant_uuid)?$this->kicthen_merchant_uuid:$this->merchant_uuid;

						$order_reference = $this->order_reference;
						$kitchen_uuid = $modelKitchen->merchant_uuid;
						$merchant_uuid = $modelKitchen->merchant_uuid;
						$merchant_id = $items->merchant_id;

						//$modelKitchen->order_ref_id = $items->id;
						$modelKitchen->order_ref_id = $items->cart_row;
						$modelKitchen->merchant_id = $items->merchant_id;						
						$modelKitchen->table_uuid = $this->table_uuid;
						$modelKitchen->room_uuid = $room_uuid;
						$modelKitchen->item_token = $items->item_token;
						$modelKitchen->qty = $items->qty;
						$modelKitchen->special_instructions = $items->special_instructions;
						$modelKitchen->customer_name = $customer_name;
						$modelKitchen->transaction_type = $transaction_type;
						$modelKitchen->timezone =  !empty($timezone)?$timezone:Yii::app()->timezone ;
						$modelKitchen->whento_deliver = $whento_deliver;
						$modelKitchen->delivery_date = !empty($delivery_date)?$delivery_date:CommonUtility::dateOnly();
						$modelKitchen->delivery_time = $delivery_time;						

						$addons = []; $attributes =[];

						$model_cart_addons = AR_cart_addons::model()->findAll("cart_row=:cart_row",[
							':cart_row'=>$items->cart_row
						]);
						if($model_cart_addons){
							foreach ($model_cart_addons as $cart_addons) {
								$addons[] = [
									'subcat_id'=>$cart_addons->subcat_id,
									'sub_item_id'=>$cart_addons->sub_item_id,
									'qty'=>$cart_addons->qty,
									'multi_option'=>$cart_addons->multi_option
								];
							}
						}
						$modelKitchen->addons = json_encode($addons);


						$model_cart_atts = AR_cart_attributes::model()->findAll("cart_row=:cart_row",[
							':cart_row'=>$items->cart_row
						]);
						if($model_cart_atts){
							foreach ($model_cart_atts as $cart_atts) {
								$attributes[] = [
									'meta_name'=>$cart_atts->meta_name,
									'meta_id'=>$cart_atts->meta_id,									
								];
							}
						}
						$modelKitchen->attributes = json_encode($attributes);
						$modelKitchen->sequence = CommonUtility::getNextAutoIncrementID('kitchen_order');

						$modelKitchen->save();
					}										
				}
				
				// SEND NOTIFICATIONS
				if(!empty($kitchen_uuid)){
					AR_kitchen_order::SendNotifications([
						'kitchen_uuid'=>$kitchen_uuid,
						'order_reference'=>$order_reference,
						'whento_deliver'=>$whento_deliver,
						'merchant_uuid'=>$merchant_uuid,
						'merchant_id'=>$merchant_id
					]);
				}
			    // SEND NOTIFICATIONS
				
				$table_model = AR_table_status::model()->find("table_uuid=:table_uuid",[
					':table_uuid'=>$this->table_uuid
				]);
				if(!$table_model){
					$table_model = new AR_table_status();
				}
				$table_model->merchant_id = $this->merchant_id;
				$table_model->table_uuid = $this->table_uuid;
				$table_model->status = "ordered";
				$table_model->save();

				// SEND NOTIFICATION TO MERCHANT
				if(!empty($this->merchant_uuid)){
					$noti = new AR_notifications;    							
					$noti->notication_channel = $this->merchant_uuid;
					$noti->notification_event = Yii::app()->params->realtime['notification_event'] ;
					$noti->notification_type = 'order';
					$noti->message = "You have new table order Table #{room_name}-{table_name}";				
					$noti->message_parameters = json_encode([						
						'{room_name}'=>$this->room_name,
						'{table_name}'=>$this->table_name,
					]);
					$noti->meta_data = json_encode([
						'notification_type'=>"order",						
						'cart_uuid'=>$this->cart_uuid
					]);			
					$noti->save();				
				}
				break;		

			case "request_bill":
				$table_model = AR_table_status::model()->find("table_uuid=:table_uuid",[
					':table_uuid'=>$this->table_uuid
				]);
				if(!$table_model){
					$table_model = new AR_table_status();
				}
				$table_model->merchant_id = $this->merchant_id;
				$table_model->table_uuid = $this->table_uuid;
				$table_model->status = "waiting for bill";
				$table_model->save();

				// SEND NOTIFICATION TO MERCHANT
				if(!empty($this->merchant_uuid)){
					$noti = new AR_notifications;    							
					$noti->notication_channel = $this->merchant_uuid;
					$noti->notification_event = Yii::app()->params->realtime['notification_event'] ;
					$noti->notification_type = 'request_bill';
					$noti->message = "Table #{room_name}-{table_name} is requesting a bill.";				
					$noti->message_parameters = json_encode([						
						'{room_name}'=>$this->room_name,
						'{table_name}'=>$this->table_name,
					]);
					$noti->meta_data = json_encode([
						'notification_type'=>"request_bill",						
						'cart_uuid'=>$this->cart_uuid
					]);			
					$noti->save();	
				}

				break;		

		    case "held_cart":
				AR_cart::model()->updateAll(
					['hold_order_admin' => 1, 'held_time'=> CommonUtility::dateNow() ],
					'cart_uuid=:cart_uuid',[':cart_uuid'=>$this->cart_uuid]
				);
				break;		
		}

		parent::afterSave();		
		CCacheData::add();
	}

	protected function afterDelete()
	{
		parent::afterDelete();				
				
		// AR_kitchen_order::model()->deleteAll("order_reference=:order_reference",array(
		// 	':order_reference'=> $this->order_reference 			
		// ));		
		
		CCart::clear($this->cart_uuid);

		CCacheData::add();
	}
		
}
/*end class*/
