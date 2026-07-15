<?php
require 'intervention/vendor/autoload.php';
use Intervention\Image\ImageManager;

class CMerchantInvoice 
{
    public static function generateRange($terms=0)
    {        
        $start = ''; $end = '';  $date = date("Y-m-d");
        if($terms==1){
            $start = date("Y-m-d 00:01:00"); $end = date("Y-m-d 23:59:00");
        } else if ($terms==7){
            $start = date('Y-m-d 00:01:00',(strtotime ( '-7 day' , strtotime ( $date) ) ));
            $end = date("Y-m-d 23:59:00");
        } else if ($terms==15){
            $start = date('Y-m-d 00:01:00',(strtotime ( '-15 day' , strtotime ( $date) ) ));
            $end = date("Y-m-d 23:59:00");
        } else if ($terms==30){
            $start = date('Y-m-d 00:01:00',(strtotime ( '-30 day' , strtotime ( $date) ) ));
            $end = date("Y-m-d 23:59:00");
        }

        if(!empty($start) && !empty($end)){
            return [
                'start'=>$start,
                'end'=>$end,
            ];
        }     
        throw new Exception("Range not available");
    }

    public static function getSummary($terms=0, $start='', $end='')
    {
        // $order_status = AOrders::getOrderTabsStatus($filter);			    		
        // dump($order_status);
        $payment_code = [];
        $all_offline = CPayments::getPaymentTypeOnline(0);				
		$status_completed = AOrderSettings::getStatus(array('status_delivered','status_completed'));        
        if(is_array($all_offline) && count($all_offline)>=1){
            foreach ($all_offline as $items) {
                $payment_code[] = $items['payment_code'];
            }
        }
        
        $stmt="
        SELECT a.merchant_id,sum(commission) as invoice_total,
        b.restaurant_name,b.contact_phone,b.contact_email,b.address as business_address    	
        FROM {{ordernew}} a
        LEFT JOIN {{merchant}} b
        ON 
        a.merchant_id = b.merchant_id
        WHERE 
        a.date_created BETWEEN ".q($start)." AND ".q($end)."
        AND a.payment_code IN (".CommonUtility::arrayToQueryParameters($payment_code).")
        AND a.status IN (".CommonUtility::arrayToQueryParameters($status_completed).")
        AND
        a.merchant_id IN (
            select merchant_id from {{merchant}}
            where invoice_terms = ".q($terms)."
        )
        AND
        a.merchant_id NOT IN (
            select merchant_id from {{invoice}}
            where date_from=".q($start)."
            and date_to = ".q($end)."
        )
        group by merchant_id        
        ";            
        if($res = Yii::app()->db->createCommand($stmt)->queryAll()){            
            $data = [];
            foreach ($res as $items) {
                $data[] = [
                    'merchant_id'=>$items['merchant_id'],
                    'invoice_total'=>$items['invoice_total'],
                    'restaurant_name'=>$items['restaurant_name'],
                    'contact_phone'=>$items['contact_phone'],
                    'contact_email'=>$items['contact_email'],
                    'business_address'=>$items['business_address'],
                ];
            }
            return $data;
        } else throw new Exception( t(HELPER_NO_RESULTS));
    }

    public static function imageBase64($image_name='',$uploadPath=''){        
        $image_driver = !empty(Yii::app()->params['settings']['image_driver'])?Yii::app()->params['settings']['image_driver']:Yii::app()->params->image['driver'];
        
        $path = CMedia::homeDir();        
        if(empty($uploadPath)){            
            $upload_path = $path."/upload/";
            $assets_path = $path."/themes/karenderia_v2/assets/images"; 
        } else {
            $upload_path = $path."/$uploadPath";
            $assets_path = $path."/themes/karenderia_v2/assets/images"; 
        }                    
        if(!empty($image_name)){
            $image_path = $upload_path."/".$image_name;
        } else {
            $image_path = $assets_path."/logo@2x.png";
        }    
                
        if(file_exists($image_path)){                    
            $manager = new ImageManager(array('driver' => $image_driver ));      
            $image = $manager->make($image_path)->encode('data-url');
            return $image->encoded;
        } else {
            return false;
        }
    }

    public static function getInvoice($invoice_uuid='')
    {
        $model = AR_invoice::model()->find("invoice_uuid=:invoice_uuid",[
            ':invoice_uuid'=>$invoice_uuid
        ]);
        if($model){
            return $model;
        }
        throw new Exception( t(HELPER_NO_RESULTS));
    }

    public static function getInvoiceByID($invoice_number='')
    {
        $model = AR_invoice::model()->find("invoice_number=:invoice_number",[
            ':invoice_number'=>$invoice_number
        ]);
        if($model){
            return $model;
        }
        throw new Exception( t(HELPER_NO_RESULTS));
    }

    public static function getHistory($invoice_number='')
    {        
        $criteria=new CDbCriteria();
        $criteria->addCondition("invoice_number=:invoice_number");
        $criteria->params = [
          ':invoice_number'=>$invoice_number
        ];
        $criteria->addInCondition('meta_name',['history','viewed','bank_deposit']);
        $criteria->order = "id DESC";
        $model = AR_invoice_meta::model()->findAll($criteria); 
        if($model){
            return $model;
        }
        throw new Exception( t(HELPER_NO_RESULTS));
    }

    public static function getPaymentHistory($invoice_number='',$deposit_type='invoice',$status='approved')
    {        
        $criteria=new CDbCriteria();
        $criteria->addCondition("transaction_ref_id=:transaction_ref_id AND deposit_type=:deposit_type AND status=:status");
        $criteria->params = [
          ':transaction_ref_id'=>$invoice_number,
          ':deposit_type'=>$deposit_type,
          ':status'=>$status
        ];        
        $criteria->order = "deposit_id DESC";
        $model = AR_bank_deposit::model()->findAll($criteria); 
        if($model){
            return $model;
        }
        throw new Exception( t(HELPER_NO_RESULTS));
    }
    
    public static function SetViewed($invoice_number='')
    {
        $history = AR_invoice_meta::model()->find("invoice_number=:invoice_number AND meta_name=:meta_name",[
            ':invoice_number'=>$invoice_number,
            ':meta_name'=>'viewed',            
        ]);
        if(!$history){
            $model = new AR_invoice_meta;
            $model->invoice_number = intval($invoice_number);
            $model->meta_name = "viewed";
            $model->meta_value1 = "This invoice was viewed";
            $model->meta_value2 = CommonUtility::dateNow();
            $model->save();
        }
    }

    public static function getTotalDeposit($invoice_number='')
    {
        $stmt= "
        SELECT sum(amount) as total
        FROM {{bank_deposit}}
        WHERE transaction_ref_id = ".q($invoice_number)."
        and status='approved'
        ";        
        if($res = Yii::app()->db->createCommand($stmt)->queryRow()){
            return $res['total'];
        }
        return 0;
    }

    public static function getInvoiceDetails($merchant_id=0,$terms=0, $start='', $end='',$exchange_type='merchant')
    {
        $data = []; 
        $amount = 0;
        $total = 0;
        $commission_amount_total =0;
        $commission_plus_tax_total=0;
        $service_fee_total=0;
        $delivery_fee_total=0;
        $tax_service_fee_total = 0;
        $commission_based = '';

        $payment_code = [];
        $all_offline = CPayments::getPaymentTypeOnline(0);				
		$status_completed = AOrderSettings::getStatus(array('status_delivered','status_completed'));        
        if(is_array($all_offline) && count($all_offline)>=1){
            foreach ($all_offline as $items) {
                $payment_code[] = $items['payment_code'];
            }
        }
        $stmt="
        SELECT 
        a.merchant_id,
        a.order_id,
        a.date_created,   
        a.commission_based,          
        a.commission_type,
        a.exchange_rate_merchant_to_admin,
        a.sub_total,        
        a.total,        
        a.tax,
        a.tax_total,
        a.service_fee,
        a.delivery_fee,

        a.commission_value,
        a.commission
        
        FROM {{ordernew}} a
        LEFT JOIN {{merchant}} b
        ON 
        a.merchant_id = b.merchant_id
        WHERE 
        a.merchant_id=".q($merchant_id)."
        AND 
        a.date_created BETWEEN ".q($start)." AND ".q($end)."
        AND a.payment_code IN (".CommonUtility::arrayToQueryParameters($payment_code).")
        AND a.status IN (".CommonUtility::arrayToQueryParameters($status_completed).")
        AND
        a.merchant_id IN (
            select merchant_id from {{merchant}}
            where invoice_terms = ".q($terms)."
        )        
        ORDER BY a.order_id ASC
        ";               
        if($res = Yii::app()->db->createCommand($stmt)->queryAll()){                            
            foreach ($res as $items) {                                         
                $commission_based = $items['commission_based'];
                if($exchange_type=='admin'){
                    $exchange_rate = $items['exchange_rate_merchant_to_admin']>0?$items['exchange_rate_merchant_to_admin']:1;
                    $items['sub_total'] = $items['sub_total']*$exchange_rate;
                    $items['total'] = $items['total']*$exchange_rate;
                    $items['commission'] = $items['commission']*$exchange_rate;
                    if($items['commission_type']=='fixed'){
                        $items['commission_value'] = $items['commission_value']*$exchange_rate;
                    }                    
                }

                $commission_amount =  $items['commission_type']=='percentage'?  $items['sub_total']*($items['commission_value']/100) :$items['commission_value'] ;
                $commission_plus_tax = $commission_amount * ($items['tax']/100);

                $tax_service_fee = $items['service_fee']  * ($items['tax']/100);

                $amount +=$items['sub_total'];
                $total +=$items['commission'];                
                $commission_amount_total+=$commission_amount;
                $commission_plus_tax_total+=$commission_plus_tax;
                $service_fee_total+=$items['service_fee'];
                $delivery_fee_total+=$items['delivery_fee'];                
                $tax_service_fee_total+=$tax_service_fee;

                $data[] = [
                    'order_id'=>$items['order_id'],
                    'date_created'=>Date_Formatter::dateTime($items['date_created']),
                    'amount'=>Price_Formatter::formatNumberNoSymbol($items['sub_total']),
                    'commission'=> $items['commission_type']=='fixed'? Price_Formatter::formatNumberNoSymbol($items['commission_value']) :  Price_Formatter::convertToRaw($items['commission_value'],0)."%",
                    'total'=>Price_Formatter::formatNumberNoSymbol($items['commission']),
                    //
                    'service_fee'=>Price_Formatter::formatNumberNoSymbol($items['service_fee']),
                    'delivery_fee'=>Price_Formatter::formatNumberNoSymbol($items['delivery_fee']),
                    'commission_amount'=> Price_Formatter::formatNumberNoSymbol($commission_amount),
                    'commission_plus_tax'=> Price_Formatter::formatNumberNoSymbol($commission_plus_tax),
                    'tax_service_fee'=>Price_Formatter::formatNumberNoSymbol($tax_service_fee)
                ];
            }
        }            
        return [
            'commission_based'=>$commission_based,
            'total'=>[
                'amount'=>Price_Formatter::formatNumberNoSymbol($amount),
                'total'=>Price_Formatter::formatNumberNoSymbol($total),
                'commission_amount_total'=>Price_Formatter::formatNumberNoSymbol($commission_amount_total),
                'commission_plus_tax_total'=>Price_Formatter::formatNumberNoSymbol($commission_plus_tax_total),
                'service_fee_total'=>Price_Formatter::formatNumberNoSymbol($service_fee_total),
                'delivery_fee_total'=>Price_Formatter::formatNumberNoSymbol($delivery_fee_total),
                'tax_service_fee_total'=>Price_Formatter::formatNumberNoSymbol($tax_service_fee_total),
            ],
            'items'=>$data
        ];
    }

    public static function getTermsLabel($terms='')
    {
        $data = '';
        if($terms==1){
            $data = t("Everyday");
        } else if ($terms==7){
            $data = t("Every 7 days");
        } else if ($terms==15){
            $data = t("Every 15 days");
        } else if ($terms==30){
            $data = t("Every 30 days");
        }   
        return $data; 
    }

}
// end class