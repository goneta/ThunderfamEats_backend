<?php
class Bookingsearch extends CAction
{
    public $_controller;
    public $_id;
    public $data;

    public function __construct($controller,$id)
    {
       $this->_controller=$controller;
       $this->_id=$id;
    }

    public function run()
    {        
        try {
            $client_id = Yii::app()->user->id;                              
            $limit = Yii::app()->params->list_limit;             
            $searchString = intval(Yii::app()->input->post('search'));				
            $page = intval(Yii::app()->input->post('page'));				
            $page_raw = intval(Yii::app()->input->post('page'));
            if($page>0){
                $page = $page-1;
            }
            
            $criteria=new CDbCriteria();
            $criteria->addCondition('client_id=:client_id');
            $criteria->params = array(':client_id'=>intval($client_id));
            $criteria->addSearchCondition("reservation_id",$searchString);
            $criteria->order = "reservation_id DESC";
                        
            $count = AR_table_reservation::model()->count($criteria); 
            $pages=new CPagination($count);
            $pages->pageSize=$limit;
            $pages->setCurrentPage( $page );        
            $pages->applyLimit($criteria);
            $page_count = $pages->getPageCount();

            if($page>0){
                if($page_raw>$page_count){
                    $this->_controller->code = 3;
			        $this->_controller->msg  = t("end of results");                    
                    $this->_controller->responseJson();
                }
            }
                        
            $data = []; $all_merchant = [];
            if($models = AR_table_reservation::model()->findAll($criteria)){
                foreach ($models as $item) {
                    $all_merchant[] = $item->merchant_id;
                    $data[] = [
                        'reservation_uuid'=>$item->reservation_uuid,
                        'reservation_id'=>$item->reservation_id,
                        'booking_id'=>t("Booking ID #{reservation_id}",[
                            '{reservation_id}'=>$item->reservation_id          
                        ]),
                        'merchant_id'=>$item->merchant_id,
                        'client_id'=>$item->client_id,
                        'guest_number'=>$item->guest_number,
                        'table_id'=>$item->table_id,
                        'reservation_date_raw'=>Date_Formatter::dateTime($item->reservation_date." ".$item->reservation_time),
                        'reservation_date'=>t("Reservation date {date}",[
                            '{date}'=>Date_Formatter::dateTime($item->reservation_date." ".$item->reservation_time)
                        ]),
                        'status'=>$item->status,
                        'status_color'=>CBooking::statusColor($item->status),
                        'view'=>Yii::app()->createUrl("/reservation/details",[
                            'id'=>$item->reservation_uuid
                        ]),
                        'cancel'=>Yii::app()->createUrl("/reservation/cancel",[
                            'id'=>$item->reservation_uuid
                        ]),
                    ];
                }

                $merchant = [];
                if(is_array($all_merchant) && count($all_merchant)>=1){
                    $merchant = COrders::orderMerchantInfo($all_merchant);
                }            
                $table_list = CommonUtility::getDataToDropDown("{{table_tables}}",'table_id','table_name',"where available=1");            
    
                $this->_controller->code = 1;
                $this->_controller->msg  = "Ok";
                $this->_controller->details = [
                    'page_raw'=>$page_raw+1,                
                    'page_count'=>$page_count,      
                    'show_next'=>($page_raw+1)>$page_count?false:true,
                    'data'=>$data,
                    'merchant'=>$merchant,
                    'table_list'=>$table_list
                ];
                
            } else {
                $this->_controller->msg  = t("No results");
            }           
            
		} catch (Exception $e) {            
			$this->_controller->msg[] = t($e->getMessage());							
		}			
		$this->_controller->responseJson();
    }
}
// end class