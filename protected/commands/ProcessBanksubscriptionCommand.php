<?php

use Symfony\Component\Translation\Dumper\DumperInterface;

set_time_limit(0);

class ProcessBanksubscriptionCommand extends CConsoleCommand
{
    public $payment_code = 'bank';
    public $subscriber_type = 'merchant';

    public function run($args)
    {                
        if (isset($args[0])) {
            $frequency = $args[0];              
            $stmt = "
            SELECT 
            a.merchant_id,
            a.package_id,
            a.restaurant_name
            FROM {{merchant}} a

            LEFT JOIN {{plans}} b
            ON
            a.package_id=b.package_id

            WHERE 
            a.package_payment_code=".q($this->payment_code)."       
            AND b.package_period = ".q($frequency)."
            ";                        
            if($res = Yii::app()->db->createCommand($stmt)->queryAll()){                
                $this->processSubscriptions($res,$frequency);
            }
        } else {
            echo "No frequency specified. Please provide 'daily', 'weekly', or 'monthly' as a parameter.\n";
        }
    }    

    public function processSubscriptions($data=[], $frequency='')
    {        
        $jobs = 'merchantSubscriptions';
        $todo_job = 'Sendbanksubscription';
        $sucess_url = Yii::app()->createAbsoluteUrl("/merchant/thankyou");                
        $failed_url = Yii::app()->createAbsoluteUrl("/merchant/signup-failed");        

        if(is_array($data) && count($data)>=1){
            foreach ($data as $items) {
                dump($items);
                $subscriber_id = $items['merchant_id'];
                $package_id = $items['package_id'];          
                
                $plans = Cplans::get($package_id);                        
                $periods = Cplans::getDatebyperiod($plans->package_period);                
                
                $find = AR_plan_subscriptions::model()->find("
                subscriber_id=:subscriber_id 
                AND payment_code=:payment_code
                AND subscriber_type=:subscriber_type 
                AND current_start=:current_start
                AND current_end=:current_end
                ",[
                   ':subscriber_id' =>$subscriber_id,
                   ':payment_code' =>$this->payment_code,
                   ':subscriber_type' =>$this->subscriber_type,
                   ':current_start' =>isset($periods['current_start'])?$periods['current_start']:null,
                   ':current_end' =>isset($periods['current_end'])?$periods['current_end']:null,
                ]);
                if($find){
                    break;
                }

                $model = new AR_plan_subscriptions();
                $model->payment_id = CommonUtility::createUUID("{{plan_subscriptions}}",'payment_id');
                $model->payment_code = $this->payment_code;
                $model->subscriber_id = $subscriber_id;   
                $model->package_id = $plans->package_id;     
                $model->amount = $plans->promo_price>0?$plans->promo_price:$plans->price;           
                $model->subscriber_type = $this->subscriber_type;             
                $model->subscription_id = CommonUtility::createUniqueTransaction("{{plan_subscriptions}}",'subscription_id',"BANK",5);
                $model->plan_name = $plans->title;
                $model->billing_cycle = $plans->package_period;
                $model->currency_code = AttributesTools::defaultCurrency();
                $model->created_at = CommonUtility::dateNow();
                $model->next_due = isset($periods['next_due'])?$periods['next_due']:null;
                $model->expiration = isset($periods['expiration'])?$periods['expiration']:null;
                $model->current_start = isset($periods['current_start'])?$periods['current_start']:null;
                $model->current_end = isset($periods['current_end'])?$periods['current_end']:null;
                $model->status = 'pending';
                $model->jobs= $jobs;
                $model->sucess_url = $sucess_url;
                $model->failed_url = $failed_url;					
                if($model->save()){                                 
                    if (class_exists($todo_job)) {
                        $jobInstance = new $todo_job([
                            'plan_subscriptions_id'=>$model->id,
                            'language'=>Yii::app()->language                        
                        ]);
                        $jobInstance->execute();	
                    }            
                }
            }
        }
    }

}
// end class