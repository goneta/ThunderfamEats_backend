<?php
class AfterReview 
{
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }
    
    public function execute()
    {        
        try { 

            $logs = '';            
            
            $language = isset($this->data['language'])?$this->data['language']:null;
            $id = isset($this->data['id'])?$this->data['id']:null;
            if($language){
                Yii::app()->language = $language;
            } 

            $options = OptionsTools::find(['points_review','points_enabled']);
            $points_enabled = $options['points_enabled'] ?? false;
            $points_enabled = $points_enabled==1?true:false;
            $points_review = $options['points_review'] ?? 0;

            if(!$points_enabled){
                return;
            }

            if($points_review<=0){
                return;
            }
            
            $model = AR_review::model()->find("id=:id",[
                ':id'=>$id
            ]);
            if(!$model){
                throw new Exception( 'Review id not found' );
                return;
            }            

            $card_id = CWallet::createCard( Yii::app()->params->account_type['customer_points'], $model->client_id); 

            $transaction_type = 'points_review';

            $find = AR_wallet_transactions::model()->find("reference_id=:reference_id AND transaction_type=:transaction_type",[
                ':reference_id'=>$model->order_id,
                ':transaction_type'=>$transaction_type
            ]);
            if($find){
				throw new Exception( 'Transaction already exist' );
			}

            $params = array(					  		 
                'transaction_description'=>"Points awarded for your review of Order #{order_id}",
                'transaction_description_parameters'=>array('{order_id}'=>$model->order_id),					  
                'transaction_type'=>$transaction_type,
                'transaction_amount'=>$points_review,
                'status'=>'paid',                
                'reference_id'=>$model->order_id,
                'reference_id1'=>$model->merchant_id
            );
            CWallet::inserTransactions($card_id,$params);

        } catch (Exception $e) {                                            
            $logs = $e->getMessage();
        }                
    }
}
// end class