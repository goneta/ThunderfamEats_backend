<?php
class ProcessPromos 
{
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }
    
    public function execute()
    {        
        try { 

            $stmt = "		
            INSERT INTO {{promos}}(
            id,
            merchant_id,
            valid_from,
            valid_to,
            offer_type,
            discount_name,
            status,
            visible,
            offer_amount,
            discount_type,
            min_order,
            max_order,
            max_discount_cap,
            applicable_to
            )

            select 
            offers_id,
            merchant_id,
            valid_from,
            valid_to,
            'offers',
            offer_name,
            status,
            visible,
            offer_percentage,
            'percentage',
            min_order,
            offer_price,
            max_discount_cap,
            applicable_to

            from {{offers}}
            WHERE offers_id NOT IN (
            select id from {{promos}}
            where offer_type = 'offers'
            )
            ";		
            Yii::app()->db->createCommand($stmt)->query();


            $stmt = "
            SELECT * FROM
            {{voucher_new}}
            WHERE voucher_id
            NOT IN (
            select id from {{promos}}
            where offer_type = 'voucher'
            )
            ";		
            if($res = Yii::app()->db->createCommand($stmt)->queryAll()){			
                $params = [];
                foreach ($res as $items) {
                    $joining_merchant =!empty($items['joining_merchant']) ? json_decode($items['joining_merchant']) :null;				
                    if(is_array($joining_merchant) && count($joining_merchant)>=1){
                        foreach ($joining_merchant as $merchant_id) {					
                            $params[] = [
                                'id'=>$items['voucher_id'],
                                'merchant_id'=>$merchant_id,
                                'valid_from'=>date("Y-m-d",strtotime($items['date_created'])),
                                'valid_to'=>$items['expiration'],
                                'offer_type'=>'voucher',
                                'discount_name'=>$items['voucher_name'],
                                'status'=>$items['status'],
                                'visible'=>$items['visible'],
                                'monday'=>$items['monday'],
                                'tuesday'=>$items['tuesday'],
                                'wednesday'=>$items['wednesday'],
                                'thursday'=>$items['thursday'],
                                'friday'=>$items['friday'],
                                'saturday'=>$items['saturday'],
                                'sunday'=>$items['sunday'],
                                'offer_amount'=>$items['amount'],
                                'discount_type'=>$items['voucher_type'],
                                'min_order'=>$items['min_order'],
                                'max_order'=>$items['max_order'],
                                'applicable_to'=>$items['applicable_to'],
                                'max_discount_cap'=>$items['max_discount_cap'],
                            ];
                        }
                    } else {
                        $params[] = [
                            'id'=>$items['voucher_id'],
                            'merchant_id'=>$items['merchant_id'],
                            'valid_from'=>date("Y-m-d",strtotime($items['date_created'])),
                            'valid_to'=>$items['expiration'],
                            'offer_type'=>'voucher',
                            'discount_name'=>$items['voucher_name'],
                            'status'=>$items['status'],
                            'visible'=>$items['visible'],
                            'monday'=>$items['monday'],
                            'tuesday'=>$items['tuesday'],
                            'wednesday'=>$items['wednesday'],
                            'thursday'=>$items['thursday'],
                            'friday'=>$items['friday'],
                            'saturday'=>$items['saturday'],
                            'sunday'=>$items['sunday'],
                            'offer_amount'=>$items['amount'],
                            'discount_type'=>$items['voucher_type'],
                            'min_order'=>$items['min_order'],
                            'max_order'=>$items['max_order'],
                            'applicable_to'=>$items['applicable_to'],
                            'max_discount_cap'=>$items['max_discount_cap'],
                        ];
                    }
                }//			
                $builder=Yii::app()->db->schema->commandBuilder;
                $command=$builder->createMultipleInsertCommand('{{promos}}',$params);
                $command->execute();
            }
                

            Yii::app()->db->createCommand("
            DELETE FROM {{promos}}
            WHERE merchant_id NOT IN (
              select merchant_id from {{merchant}}
            )
            ")->query();

            Yii::app()->db->createCommand("
            DELETE from {{promos}}
                where offer_type = 'voucher'
                and id NOT IN (
                select voucher_id
                from  {{voucher_new}}
            );                        
            delete  from {{promos}}
            where offer_type = 'offers'
            and id NOT IN (
            select offers_id
            from {{offers}}
            );
            ")->query();

        } catch (Exception $e) {                                            
            $logs = $e->getMessage();
        }        
        //Yii::log( $logs, CLogger::LEVEL_ERROR);
    }
}
// end class