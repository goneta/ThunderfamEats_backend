<?php
class UpdateOrder 
{
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }
    
    public function execute()
    {        
        try { 

            $logs = '';            

            $order_uuid  = isset($this->data['order_uuid'])?$this->data['order_uuid']:null;
            $language = isset($this->data['language'])?$this->data['language']:null;
            if($language){
                Yii::app()->language = $language;
            } 

            $model = COrders::get($order_uuid);	
            
            $model->scenario = 'adjustment';
            COrders::getContent($order_uuid,Yii::app()->language);
            $summary = COrders::getSummary();  
            
            $sub_total = 0;
            
            $result_subtotal = array_filter($summary, function($item) {
                return $item['type'] === 'subtotal';
            });

            $sub_total = isset($result_subtotal[0])?$result_subtotal[0]['value']:null;
            if(!$sub_total){
                return;
            }
            
            $resp_comm = CCommission::getCommissionValueNew([
                'merchant_id'=>$model->merchant_id,
                'sub_total'=>$sub_total,
                'transaction_type'=>$model->service_code,
            ]);

            $commission_value = $resp_comm['commission_value'];
            $commission_based = $resp_comm['commission_based'];
            $merchant_earning = $resp_comm['merchant_earning'];
            $commission = $resp_comm['commission'];
            $commission_type = $resp_comm['commission_type'];

            $model->commission_value = $commission_value;
            $model->commission_based = $commission_based;
            $model->merchant_earning = $merchant_earning;
            $model->commission = $commission;
            $model->commission_type = $commission_type;
            $model->save();
            
        } catch (Exception $e) {                                            
            $logs = $e->getMessage();
        }                
    }
}
// end class