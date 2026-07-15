<?php
class AfterUpdatebooking 
{
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }
    
    public function execute()
    {        
        try { 

            $logs = '';            

            $reservation_id = isset($this->data['reservation_id'])?$this->data['reservation_id']:null;
            $language = isset($this->data['language'])?$this->data['language']:null;
            if($language){
                Yii::app()->language = $language;
            } 

            if(!$reservation_id){
                return;
            }

            $options = OptionsTools::find(['points_booking','points_enabled']);
            $points_enabled = $options['points_enabled'] ?? false;
            $points_booking = $options==1?true:false;
            $points_booking = $options['points_booking'] ?? 0;

            if(!$points_enabled){                
                return;
            }

            if($points_booking<=0){                
                return;
            }
            

            $model = AR_table_reservation::model()->find("reservation_id=:reservation_id",[
                ':reservation_id'=>$reservation_id
            ]);
            if(!$model){
                return;
            }
            
            $card_id = CWallet::createCard( Yii::app()->params->account_type['customer_points'], $model->client_id);
            
            $transaction_type = 'points_booking';            

            $find = AR_wallet_transactions::model()->find("reference_id=:reference_id AND transaction_type=:transaction_type",[
                ':reference_id'=>$model->reservation_id,
                ':transaction_type'=>$transaction_type
            ]);
            if($find){                
				throw new Exception( 'Transaction already exist' );
			}

            $params = array(					  		 
                'transaction_description'=>"Points earned on booking #{reservation_id}",
                'transaction_description_parameters'=>array('{reservation_id}'=>$model->reservation_id),					  
                'transaction_type'=>$transaction_type,
                'transaction_amount'=>$points_booking,
                'status'=>'paid',                
                'reference_id'=>$model->reservation_id,
                'reference_id1'=>$model->merchant_id
            );            
            CWallet::inserTransactions($card_id,$params);

        } catch (Exception $e) {                                            
            $logs = $e->getMessage();            
        }        
        //Yii::log( $logs, CLogger::LEVEL_ERROR);
    }
}
// end class