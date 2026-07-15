<?php
class merchantSubscriptions 
{
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }

    public function execute()
    {        
        try {            
            
            $package_id = isset($this->data['package_id'])?$this->data['package_id']:null;
            $subscriber_type = isset($this->data['subscriber_type'])?$this->data['subscriber_type']:null;
            $subscriber_id = isset($this->data['subscriber_id'])?$this->data['subscriber_id']:null;        
            $subscription_id = isset($this->data['subscription_id'])?$this->data['subscription_id']:null;        
            $language = isset($this->data['language'])?$this->data['language']:null;
            $is_new = isset($this->data['is_new'])?$this->data['is_new']:false;
            $is_new = $is_new==1?true:false;            
            if($language){
                Yii::app()->language = $language;
            } 

            $new_item_limit  = 0; $new_order_limit =0;

            if($package_id){                
                $subscriber_model =  Cplans::getSubscriberRecords($subscriber_id,$subscriber_type,'model');            
                $remaining_items = $subscriber_model->item_limit - $subscriber_model->items_added;
                $remaining_orders = $subscriber_model->order_limit - $subscriber_model->orders_added;

                $subscription_features = [];
                try {
                    $plan_model = Cplans::get($package_id);														                    
                    $new_item_limit = $plan_model->item_limit;
                    $new_order_limit = $plan_model->order_limit;
                    $subscription_features = [
                        'pos'=>$plan_model->pos==1?true:false,
                        'self_delivery'=>$plan_model->self_delivery==1?true:false,
                        'chat'=>$plan_model->chat==1?true:false,
                        'loyalty_program'=>$plan_model->loyalty_program==1?true:false,
                        'table_reservation'=>$plan_model->table_reservation==1?true:false,
                        'marketing_tools'=>$plan_model->marketing_tools==1?true:false,
                        'mobile_app'=>$plan_model->mobile_app==1?true:false,
                        'payment_processing'=>$plan_model->payment_processing==1?true:false,
                        'customer_feedback'=>$plan_model->customer_feedback==1?true:false,
                        'coupon_creation'=>$plan_model->coupon_creation==1?true:false,
                    ];
                } catch (Exception $e) {}

                $new_total_item_limit = $new_item_limit>0 ? ($new_item_limit+$remaining_items) : 0;
                $new_total_order_limit = $new_order_limit>0 ? ($new_order_limit+$remaining_orders) : 0;    
                                
                $subscriber_model->orders_added = 0;
                $subscriber_model->items_added = 0;
                $subscriber_model->order_limit = $new_total_order_limit > 0 ? $new_total_order_limit :0;
                $subscriber_model->item_limit = $new_total_item_limit > 0 ? $new_total_item_limit :0;
                $subscriber_model->status = 'active';
                $subscriber_model->package_id = $package_id;			
                $subscriber_model->subscription_features = json_encode($subscription_features);                
                $subscriber_model->save();                        
                Yii::log( "Subscription activated $subscription_id" , CLogger::LEVEL_INFO);
                
                if($is_new){                    
                    $jobs = 'MerchantRegWelcome';
                    if (class_exists($jobs)) {                        
                        $jobInstance = new $jobs([
                            'merchant_id'=>$subscriber_id,
                            'language'=>Yii::app()->language                        
                        ]);
                        $jobInstance->execute();	
                    }           
                } 
                CommonUtility::pushJobs("SubscriptionPayment",[
                    'merchant_id'=>$subscriber_id,
                    'language'=>Yii::app()->language
                ]);
            }
        } catch (Exception $e) {            
            Yii::log( "Subscription ERROR ".$e->getMessage() , CLogger::LEVEL_ERROR);
        }
    }
}
// end class