<?php
class FPtemplate
{

    public static function TestTemplate($paper_width='')
    {
        $paperWidth = self::getPaperLenght($paper_width);
        $content = '';
        $content.='<CB>TEST RECEIPT</CB><BR>';
        $content.= self::getLine($paperWidth);
        $content.= "<BR>";

        $content.= self::leftRightData("1 x Cheese burger","3.00",$paperWidth);
        $content.= self::leftRightData("5 x Sauce","100.00",$paperWidth);

        $content.= self::getLine($paperWidth);
        $content.= "<BR>";

        $content.= self::leftRightData("TOTAL AMOUNT","126.00",$paperWidth);
        $content.= self::leftRightData("CASH","200.00",$paperWidth);
        $content.= self::leftRightData("CHANGE","74.00",$paperWidth);
        $content.= "<BR><BR>";

        $content.= self::leftRightData("BANK CARD","****7212",$paperWidth);
        $content.= self::getLine($paperWidth);
        $content.= "<BR>";

        $content.= self::centerData("THANK YOU!",$paperWidth);
        $content.= "<BR>";
        $content.= "<BR>";
        $content.= "<CUT>";
        
        return $content;
    }

    public static function centerData($string='', $paperWidth='')
    {
        $totalPad = $paperWidth - strlen($string);
        $totalPad = $totalPad / 2;
        $RowItems = "";
        $RowItems .= str_pad("",intval($totalPad),"*",STR_PAD_LEFT);
        $RowItems .= $string;
        $RowItems .= str_pad("",intval($totalPad),"*",STR_PAD_RIGHT);
        return $RowItems;
    }

    public static function leftRightData($string1='', $string2='', $paperWidth='')
    {
        $string1 = empty($string1)?"":$string1;
        $string2 = empty($string2)?"":$string2;
        $totalPad = $paperWidth - (  strlen($string1) + strlen($string2));
        $totalPad = $totalPad + strlen($string1);

        $RowItems = "";
        if (strlen($string1) > $paperWidth) {
            $RowItems = $string1;
            $RowItems.= "<BR>";
            $RowItems.= $string2;
        } else {
            $RowItems = str_pad($string1,$totalPad," ",STR_PAD_RIGHT);
            $RowItems.= $string2;
        }
        $RowItems .= "<BR>";
        return $RowItems;
    }

    public static function getLine($paperWidth) {
        $Line = "-";                
        return str_pad($Line,$paperWidth,$Line);
    }

    public static function getPaperLenght($paperWidth='')
    {
        if ($paperWidth == "58") {
            return 32;
        } else return 45;
    }

    public static function ReceiptTemplate($paper_width='',$order_info=array(),$merchant=array(),$order_items=array(),$order_summary=array())
    {
        $paperWidth = self::getPaperLenght($paper_width);
        $content='';
        $content .= "<BR>";
        $content = "<CB>". $merchant['restaurant_name'] ."</CB><BR>";        
        $content .= $merchant['address']."<BR>";        
        $content .= self::getLine($paperWidth)."<BR>";
        $content .= "<BR>";
        
        $content .= self::leftRightData("Order ID",$order_info['order_id'],$paperWidth);
        $content .= self::leftRightData("Customer Name",$order_info['customer_name'],$paperWidth);
        $content .= self::leftRightData("Email",$order_info['contact_email'],$paperWidth);
        $content .= self::leftRightData("Phone",$order_info['contact_number'],$paperWidth);

        if($order_info['order_type']=="delivery"):
        $content .= self::leftRightData("Address",$order_info['complete_delivery_address'],$paperWidth);
        $content .= self::leftRightData("Address label",$order_info['address_label'],$paperWidth);
        endif;
        
        $content .= self::leftRightData("Order Type",$order_info['order_type'],$paperWidth);

        $label_delivery_time = "Delivery Date/Time";
        if($order_info['order_type']=="pickup"){
            $label_delivery_time = "Pickup Date/Time";
        } else if ($order_info['order_type']=="dinein" ){
            $label_delivery_time = "Dinein Date/Time";
        }
        
        if ($order_info['whento_deliver'] == "now") {
            $content .= self::leftRightData($label_delivery_time,$order_info['schedule_at'],$paperWidth);
        } else {
            $content .= self::leftRightData($label_delivery_time,$order_info['delivery_date']." ".$order_info['delivery_time'],$paperWidth);
        }

        if($order_info['order_type']=="delivery" && isset($order_info['delivery_options'])){
            if(!empty($order_info['delivery_options'])){
                $content .= self::leftRightData("Delivery options",$order_info['delivery_options'],$paperWidth);
            }            
        }

        $content .= $order_info['payment_name']."<BR>";  
        if(isset($order_info['credit_card_details'])){
            if(is_array($order_info['credit_card_details']) && count($order_info['credit_card_details'])>=1){
                $content .= $order_info['credit_card_details']['card_number']."<BR>";  
                $expiration = $order_info['credit_card_details']['expiration_month']."/".$order_info['credit_card_details']['expiration_yr'];
                $content .= $expiration."<BR>";  
            }            
        }
        
        if($order_info['order_type']=="dinein" && isset($order_info['order_table_data'])){
            $order_table_data = isset($order_info['order_table_data'])?$order_info['order_table_data']:'';
            if(is_array($order_table_data) && count($order_table_data)>=1){                
                $content .= self::leftRightData("Guest",$order_table_data['guest_number'],$paperWidth);
                $content .= self::leftRightData("Room name",$order_table_data['room_name'],$paperWidth);
                $content .= self::leftRightData("Table name",$order_table_data['table_name'],$paperWidth);
            }            
        }

        $content .= self::getLine($paperWidth);
        $content .= "<BR><BR>";

        if ( is_array($order_items) && count($order_items)>=1) {
            foreach ($order_items as $key => $items) {                
                if ($items['price']['discount'] > 0) {
                    $content .= self::leftRightData($items['qty']. " x ". $items['item_name'],
                      $items['price']['pretty_total_after_discount'],
                      $paperWidth
                    );
                    if(!empty($items['price']['size_name'])){
                        $content .= "(".$items['price']['size_name'].")";
                        $content .= "<BR>";
                    } 
                    $content .= $items['price']['pretty_price'] . " " . $items['price']['pretty_price_after_discount'];
                    $content .= "<BR>";
                } else {
                    $content .= self::leftRightData(
                        $items['qty']. " x ". $items['item_name'],
                        $items['price']['pretty_total'],
                        $paperWidth
                    );
                    if(!empty($items['price']['size_name'])){
                        $content .= "(".$items['price']['size_name'].")";
                        $content .= "<BR>";
                    }                    
                    $content .= $items['price']['pretty_price'];                    
                    $content .= "<BR>";
                }

                // special_instructions
                if (!empty($items['special_instructions'])) {
                    $content .= $items['special_instructions'];
                    $content .= "<BR>";
                }

                // attributes                
                if (!empty($items['attributes'])) {
                    foreach ($items['attributes'] as $keyatt => $itemsatt) {
                        foreach ($itemsatt as $keyatt1 => $itemsatt1) {
                            $content .= $itemsatt1;
                            $content .= "<BR>";
                        }
                    }
                }

                // addon
                if ( is_array($items['addons']) && count($items['addons'])>=1) {
                    foreach ($items['addons'] as $keyaddon => $addons) {
                        $content .= trim($addons['subcategory_name']);
                        $content .= "<BR>";                        
                        foreach ($addons['addon_items'] as $keyaddonitems => $addon_items) {                                                        
                            $content.= self::leftRightData(
                                $addon_items['qty'] . " x" . $addon_items['sub_item_name'],
                                $addon_items['pretty_addons_total'],
                                $paperWidth
                            );
                            $content .= $addon_items['pretty_price'];
                        }
                    }
                    $content .= "<BR>";                    
                }

            } // END ITEMS LOOP
        }
        // END ITEMS

        $content .= self::getLine($paperWidth)."<BR>";
        $content .= "<BR>";

        if ( is_array($order_summary) && count($order_summary)>=1) {
            foreach ($order_summary as $key => $items) {
                $content.= self::leftRightData(
                    $items['name'],
                    $items['value'],
                    $paperWidth
                );
            }
        }

        $content .= "<BR>";
        $content .= self::getLine($paperWidth)."<BR>";
        $content .= "<BR>";
        
        $content .= self::centerData("THANK YOU!",$paperWidth);
        $content .= "<BR>";
        $content .= "<BR>";
        $content .= "<CUT>";

        return $content;
    }

    public static function DailySalesReport($paper_width='',$data=array(), $merchant=array(), $start='', $end='',$payment_list=array(),$services=array())
    {
        $paperWidth = self::getPaperLenght($paper_width);
        $content='';
        $content .= "<BR>";
        $content = "<CB>". $merchant['restaurant_name'] ."</CB><BR>";        
        $content .= t("Date from: {start} to {end}",[
            '{start}'=>$start,
            '{end}'=>$end,
        ])."<BR>";        
        $content .= self::getLine($paperWidth)."<BR>";
        $content .= "<BR>";

        if(is_array($data) && count($data)>=1){
            foreach ($data as $items) {
                $content .= t("{payment_code} {transaction_type} Sales",[
                    '{payment_code}'=> ucwords($items['payment_code']) ,
                    '{transaction_type}'=> isset($services[$items['service_code']])?$services[$items['service_code']]['service_name']: $items['service_code'] ,
                ]);                
                $content .= "<BR>";

                if( $items['service_fee']>0){
                    $content .= self::leftRightData("Service Fee",
                    Price_Formatter::formatNumberNoSymbol($items['service_fee'])
                    ,$paper_width);
                }
                if( $items['small_order_fee']>0){
                    $content .= self::leftRightData("Small Order Fee",
                    Price_Formatter::formatNumberNoSymbol($items['small_order_fee'])
                    ,$paper_width);
                }
                if( $items['delivery_fee']>0){
                    $content .= self::leftRightData("Delivery Fee",
                    Price_Formatter::formatNumberNoSymbol($items['delivery_fee'])
                    ,$paper_width);
                }
                if( $items['tax_total']>0){
                    $content .= self::leftRightData("Tax",
                    Price_Formatter::formatNumberNoSymbol($items['tax_total'])
                    ,$paper_width);
                }
                if( $items['courier_tip']>0){
                    $content .= self::leftRightData("Tips",
                    Price_Formatter::formatNumberNoSymbol($items['courier_tip'])
                    ,$paper_width);
                }
                if( $items['total']>0){
                    $content .= self::leftRightData("Total",
                    Price_Formatter::formatNumberNoSymbol($items['total'])
                    ,$paper_width);
                }


                $content .= "<BR>";
            }
        }

        $content .= "<BR>";
        $content .= self::getLine($paperWidth)."<BR>";
        $content .= "<BR>";
        
        $content .= self::centerData("END OF SALES REPORT",$paperWidth);
        $content .= "<BR>";
        $content .= "<BR>";
        $content .= "<CUT>";
        return $content;        
    }

    public static function Ticket($data = [],$paper_width='')
    {
        $job_id = isset($data['order_reference'])?$data['order_reference']:'';
        $items = isset($data['items'])?$data['items']:null;

        $table_name = isset($data['table_name'])?$data['table_name']:'';
        $transaction_type = isset($data['transaction_type_pretty'])?$data['transaction_type_pretty']:'';
        $delivery_time = isset($data['delivery_time_pretty'])?$data['delivery_time_pretty']:'';
        $created_at = isset($data['created_at'])?$data['created_at']:'';

        $paperWidth = self::getPaperLenght($paper_width);
        $content = '';
        $content.='<CB>#'.$job_id.'</CB><BR>';
        if(!empty($table_name)){
            $content.='<CB>#'.$table_name.'</CB><BR>';
        }        
        $content.=$created_at.'<BR>';
        $content.= self::getLine($paperWidth);
        $content.= "<BR>";        
        $content.='<CB>#'.$transaction_type.'</CB><BR>';
        $content.= self::getLine($paperWidth);
        $content.= "<BR>";        

        $spaces_front = str_repeat(" ",4);   

        if(is_array($items) && count($items)>=1){
            foreach ($items as $value) {
                $attributes = isset($value['attributes'])?$value['attributes']:null;
                $addons = isset($value['addons'])?$value['addons']:null;

                $item_name = isset($value['item_name'])? trim($value['item_name']) :'';
                $qty = isset($value['qty'])?$value['qty']:0;
                $content.= "$qty x $item_name<BR>";  

                if(is_array($attributes) && count($attributes)>=1){
                    foreach ($attributes as $attributes_items) {
                        $attributes_value = isset($attributes_items['value'])?$attributes_items['value']:'';
                        $content.= $spaces_front.$attributes_value."<BR>";  
                    }
                }

                if(is_array($addons) && count($addons)>=1){
                    foreach ($addons as $addons_items) {                        
                        $addons_items_value = isset($addons_items['value'])?$addons_items['value']:'';                        
                        $content.= $spaces_front."- ".$addons_items_value."<BR>";                          
                    }
                }
            }
            $content.= self::getLine($paperWidth);
            $content.= "<BR>";        
            // end for
        }

        $content.= "<BR>";
        $content.= "<BR>";
        $content.= "<CUT>";
        
        return $content;
    }

    public static function TicketOrder($data = [],$paper_width='')
    {
        $job_id = isset($data['order_reference'])?$data['order_reference']:'';
        $items = isset($data['items'])?$data['items']:null;        

        $table_name = isset($data['table_name'])?$data['table_name']:'';
        $room_name = isset($data['room_name'])?$data['room_name']:'';
        $customer_name = isset($data['customer_name'])?$data['customer_name']:'';
        $transaction_type = isset($data['transaction_type'])?$data['transaction_type']:'';
        $delivery_time = isset($data['date_pretty'])?$data['date_pretty']:'';
        $created_at = isset($data['date_pretty'])?$data['date_pretty']:'';

        $paperWidth = self::getPaperLenght($paper_width);
        $content = '';
        $content.='<CB>#'.$job_id.'</CB><BR>';

        if(!empty($room_name)){
            $content.='<CB>'.$room_name.'</CB><BR>';
        }        
        if(!empty($table_name)){
            $tbname = is_numeric($table_name)? t("Table #{table_name}",['{table_name}'=>$table_name]) :$table_name;
            $content.='<CB>'.$tbname.'</CB><BR>';
        }        
        $content.=$created_at.'<BR>';
        $content.= self::getLine($paperWidth);
        $content.= "<BR>";        
        $content.='<CB>#'.$transaction_type.'</CB><BR>';
        $content.= self::getLine($paperWidth);
        $content.= "<BR>";        
        
        $spaces_front = str_repeat(" ",4);  

        if(is_array($items) && count($items)>=1){
            foreach ($items as $value) {
                $item_name = isset($value['item_name'])? trim($value['item_name']) :'';
                $special_instructions = isset($value['special_instructions'])? trim($value['special_instructions']) :'';
                $qty = isset($value['qty'])?$value['qty']:0;
                $content.= "$qty x $item_name<BR>";  
                if(!empty($special_instructions)){
                    $content.= $spaces_front.$special_instructions."<BR>";
                }
            }
            $content.= self::getLine($paperWidth);
            $content.= "<BR>"; 
        }

        $content.= "<BR>";
        $content.= "<BR>";
        $content.= "<CUT>";

        return $content;
    }

}
// end class