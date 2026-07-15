<?php
class ThermalPrinterFormatter
{
    public static $printer;
    public static $items;
    public static $summary;
    public static $orderinfo;
    public static $merchant;

    public static function setPrinter($data = [])
    {
        self::$printer = $data;
    }

    public static function getPrinter()
    {
        $printer = self::$printer;                
        $pageWidth = '';
        $print_type = isset($printer['print_type'])?$printer['print_type']:'';
        $paper_width = isset($printer['paper_width'])?$printer['paper_width']:'';
        
        if($print_type=="raw"){
            if($paper_width=="80"){
                $pageWidth = 40;
            } else $pageWidth = 32;
        } else {
            if($paper_width=="80"){
                $pageWidth = 500;
            } else $pageWidth = 363;
        }
        
        $settings = [
            'print_type'=>$print_type,
            'ip'=>$printer['ip_address'],
            'port'=>$printer['port'],
            'character_code'=>$printer['character_code'],
            'page_width'=>$pageWidth,
            'font_size'=>17,
            'line_height'=>25,
        ];        
        return $settings;
    }

    public static function setItems($data = [])
    {
        self::$items = $data;
    }

    public static function setSummary($data = [])
    {
        self::$summary = $data;
    }

    public static function setOrderInfo($data = [])
    {
        self::$orderinfo = $data;  
    }

    public static function setMerchant($data = [])
    {
        self::$merchant = $data;  
    }

    public static function RawReceipt()
    {
        $header = [];
        $merchant = self::$merchant;
        $orderinfo = self::$orderinfo;        
        $items = self::$items;        
        $summary = self::$summary;
        
        $order_id = isset($orderinfo['order_id'])?$orderinfo['order_id']:'';
        $printer = self::$printer;        

        $pageWidth = '';
        $print_type = isset($printer['print_type'])?$printer['print_type']:'';
        $paper_width = isset($printer['paper_width'])?$printer['paper_width']:'';
        
        if($print_type=="raw"){
            if($paper_width=="80"){
                $pageWidth = 40;
            } else $pageWidth = 32;
        } else {
            if($paper_width=="80"){
                $pageWidth = 500;
            } else $pageWidth = 363;
        }
        
        $settings = [
            'print_type'=>$print_type,
            'ip'=>$printer['ip_address'],
            'port'=>$printer['port'],
            'character_code'=>$printer['character_code'],
            'page_width'=>$pageWidth,
            'font_size'=>17,
            'line_height'=>25,
        ];        

        if(is_array($merchant) && count($merchant)>=1){
            $header = [    
                [
                    'position'=>'left',
                    'type'=>'font',
                    'font_type'=>'bold',        
                ],
                [
                    'position'=>"center",
                    'type'=>"text",
                    'value'=>isset($merchant['restaurant_name'])?$merchant['restaurant_name']:''
                ],
                [
                    'position'=>'left',
                    'type'=>'font',
                    'font_type'=>"normal",        
                ],
                [
                    'position'=>"center",
                    'type'=>"text",
                    'value'=>t("Adddress: {address}",[
                        '{address}'=>isset($merchant['address'])?$merchant['address']:''
                    ])
                ],
                [
                    'position'=>"center",
                    'type'=>"text",                    
                    'value'=>t("Tel: {contact_phone}",[
                        '{contact_phone}'=>isset($merchant['contact_phone'])?$merchant['contact_phone']:''
                    ])
                ],
                [
                    'position'=>"center",
                    'type'=>"text",
                    'value'=>isset($orderinfo['place_datetime'])?$orderinfo['place_datetime']:''
                ],
                [
                    'position'=>"left",
                    'type'=>"line_break",
                    'value'=>""
                ]
            ];            
        }
        
        $footer = [
            [
                'position'=>"left",
                'type'=>"line_break_big",
                'value'=>""
            ],
            [
                'position'=>'center',
                'type'=>"text",
                'value'=>t('Thank you for your order')
            ],
            [
                'position'=>'center',
                'type'=>"text",
                'value'=>t('Please visit us again.')
            ],
        ];        
        
        $body = [
            [
                'position'=>'left',
                'type'=>"dotted_line",
                'value'=>'-'
            ],    
            [
                'position'=>'left_right_text',
                'type'=>"text",
                'label'=>t('Order ID').":",
                'value'=>isset($orderinfo['order_id'])?$orderinfo['order_id']:''
            ],
            [
                'position'=>'left_right_text',
                'type'=>"text",
                'label'=>t('Customer').":",
                'value'=>isset($orderinfo['customer_name'])?$orderinfo['customer_name']:''
            ],
            [
                'position'=>'left_right_text',
                'type'=>"text",
                'label'=>t('Phone').":",
                'value'=>isset($orderinfo['contact_number'])?$orderinfo['contact_number']:''
            ],        
        ];
             
        $body[]=[
            'position'=>'left_right_text',
            'type'=>"text",
            'label'=>t('Order Type').":",
            'value'=>isset($orderinfo['order_type1'])?t($orderinfo['order_type1']):''             
       ];

       $body[]=[
            'position'=>'left_right_text',
            'type'=>"text",
            'label'=>t('{order_type} Date/Time:',[
                '{order_type}'=>isset($orderinfo['order_type1'])?t($orderinfo['order_type1']):''
            ]),
            'value'=>$orderinfo['whento_deliver']=="now"?t($orderinfo['schedule_at']):$orderinfo['delivery_date1']." ".$orderinfo['delivery_time1']
       ];       
       
       if($orderinfo['order_type']=="delivery"){
          $address_label = isset($orderinfo['address_label'])? t($orderinfo['address_label']) :'';
          $delivery_address = isset($orderinfo['complete_delivery_address'])?$orderinfo['complete_delivery_address']:$orderinfo['delivery_address'];        
          $body[]=[
            'position'=>'left',
            'type'=>"text",                
            'value'=>t("Delivery Address").":"
         ];
          $body[]=[
             'position'=>'left',
             'type'=>"text",                
             'value'=>"$address_label: $delivery_address"
          ];
       } else if ( $orderinfo['order_type']=="dinein"){
            $order_table_data = isset($orderinfo['order_table_data'])?$orderinfo['order_table_data']:'';
            $body[]=[
                'position'=>'left_right_text',
                'type'=>"text",
                'label'=>t('Guest').":",
                'value'=>isset($order_table_data['guest_number'])?$order_table_data['guest_number']:''             
            ];
            $body[]=[
                'position'=>'left_right_text',
                'type'=>"text",
                'label'=>t('Room name').":",
                'value'=>isset($order_table_data['room_name'])?$order_table_data['room_name']:''             
            ];
            $body[]=[
                'position'=>'left_right_text',
                'type'=>"text",
                'label'=>t('Table name').":",
                'value'=>isset($order_table_data['table_name'])?$order_table_data['table_name']:''             
            ];
       }       

       $body[]=[
            'position'=>'left',
            'type'=>"text",        
            'value'=>isset($orderinfo['payment_name'])?$orderinfo['payment_name']:''
       ];

       $payment_change = isset($orderinfo['payment_change'])?$orderinfo['payment_change']:0;
       if($payment_change>0){
            $body[]=[
                'position'=>'left_right_text',
                'type'=>"text",
                'label'=>t('Payment change').":",
                'value'=>$print_type=="raw"? Price_Formatter::formatNumberNoSymbol($orderinfo['payment_change']) :$orderinfo['payment_change_pretty']    
            ];    
       }

       $body[]=[
            'position'=>'left',
            'type'=>"dotted_line",
            'value'=>'-'
       ];

              
       if(is_array($items) && count($items)>=1){
         foreach ($items as $items) {                  
             $attributes = isset($items['attributes'])?$items['attributes']:'';
             $size_name = isset($items['price']['size_name'])?$items['price']['size_name']:'';
             $sizeName = !empty($size_name)? " ($size_name)" :'';

             $body[] = [
                'position'=>'left_right_text',
                'type'=>"text",                
                'label'=>$items['qty']." x ".$items['item_name'].$sizeName,
                'value'=>$print_type=="raw"? Price_Formatter::formatNumberNoSymbol($items['price']['total_after_discount']):$items['price']['pretty_total_after_discount']
             ];

             if(!empty($items['special_instructions'])){
                $body[] = [
                    'position'=>'left',
                    'type'=>"text",        
                    'value'=>$items['special_instructions']
                ];
             }             

             $item_attributes = '';
             if(is_array($attributes) && count($attributes)>=1){
                foreach ($attributes as $attributes_items) {
                    foreach ($attributes_items as $attr_key => $attributes_value) {                         
                        $item_attributes.="$attributes_value,";
                    }
                }
                $item_attributes = !empty($item_attributes)?substr($item_attributes,0,-1):'';                
                $body[] = [
                    'position'=>'left',
                    'type'=>"text",        
                    'value'=>$item_attributes
                ];
             }

             $addons = isset($items['addons'])?$items['addons']:'';
             if(is_array($addons) && count($addons)>=1){
                foreach ($addons as $addons_key => $addons_val) { 
                    $body[] = [
                        'position'=>'left',
                        'type'=>"text",        
                        'value'=>$addons_val['subcategory_name']
                    ];
                    $addon_items = isset($addons_val['addon_items'])?$addons_val['addon_items']:'';
                    if(is_array($addon_items) && count($addon_items)>=1){
                        foreach ($addon_items as $addon_items_key => $addon_items_val) { 
                            $body[] = [
                                'position'=>'left_right_text',
                                'type'=>"text",                
                                'label'=>$addon_items_val['qty']." x ".$addon_items_val['sub_item_name'],
                                'value'=>$print_type=="raw"? Price_Formatter::formatNumberNoSymbol($addon_items_val['addons_total']):$addon_items_val['pretty_addons_total']
                             ];
                        }
                    }
                }
             }

             $body[] = [
                'position'=>"left",
                'type'=>"line_break",
                'value'=>""
             ];

         }
        //  end foreach
       }              
                
       $body[] = [
            'position'=>'left',
            'type'=>"dotted_line",
            'value'=>'-'
       ];
              
       if(is_array($summary) && count($summary)>=1){
            foreach ($summary as $items) {                 
                $type = isset($items['type'])?$items['type']:'';
                if($type=="total"){
                    $body[] = [
                        'position'=>'left',
                        'type'=>'font',
                        'font_type'=>'bold', 
                    ];             
                }
                $body[] = [
                    'position'=>'left_right_text',
                    'type'=>"text",
                    'label'=>$items['name'],
                    'value'=>$print_type=="raw"?Price_Formatter::formatNumberNoSymbol($items['raw']):$items['value']
                ];
            }        
       }

       $body[] = [
            'position'=>'left',
            'type'=>'font',
            'font_type'=>"normal",    
       ];
       $body[] = [
            'position'=>'left',
            'type'=>"dotted_line",
            'value'=>'-'
       ];       
       $body[] = [
            'position'=>'left',
            'type'=>"text",        
            'value'=>isset($orderinfo['place_on'])?$orderinfo['place_on']:''
       ];
       $body[] = [
            'position'=>'left',
            'type'=>"dotted_line",
            'value'=>'-'
       ];

       $data = [
            'job_id'=>$order_id,
            'settings'=>$settings,            
            'header'=>$header,
            'body'=>$body,
            'footer'=>$footer
       ];
       
       return $data;
    }
    //

    public static function RawDailySales($start='',$end='',$services=[],$payment_list=[])
    {
        $header = [];
        $job_id = "daily_sales_report";
        $body = [];
        $footer = [];

        $merchant = self::$merchant;        
        $items = self::$items;        

        $printer = self::$printer;        

        $pageWidth = '';
        $print_type = isset($printer['print_type'])?$printer['print_type']:'';
        $paper_width = isset($printer['paper_width'])?$printer['paper_width']:'';

        if($print_type=="raw"){
            if($paper_width=="80"){
                $pageWidth = 40;
            } else $pageWidth = 32;
        } else {
            if($paper_width=="80"){
                $pageWidth = 500;
            } else $pageWidth = 363;
        }

        $settings = [
            'print_type'=>$print_type,
            'ip'=>$printer['ip_address'],
            'port'=>$printer['port'],
            'character_code'=>$printer['character_code'],
            'page_width'=>$pageWidth,
            'font_size'=>17,
            'line_height'=>25,
        ];        
        
        if(is_array($merchant) && count($merchant)>=1){
            $header = [    
                [
                    'position'=>"center",
                    'type'=>"text",
                    'value'=>isset($merchant['restaurant_name'])?$merchant['restaurant_name']:''
                ],
                [
                    'position'=>"center",
                    'type'=>"text",
                    'value'=>t("Date from: {start} to {end}",[
                        '{start}'=>$start,
                        '{end}'=>$end,
                    ])
                ],
                [
                    'position'=>"left",
                    'type'=>"line_break",
                    'value'=>""
                ]
            ];
        }
        

        if(is_array($items) && count($items)>=1){
            foreach ($items as $value) {
                $body[]=[
                    'position'=>'left',
                    'type'=>"text",        
                    'value'=>t("{payment_code} {transaction_type} Sales",[
                        '{payment_code}'=> ucwords($value['payment_code']) ,
                        '{transaction_type}'=> isset($services[$value['service_code']])?$services[$value['service_code']]['service_name']: $value['service_code'] ,                        
                    ])
                ];
                if( $value['service_fee']>0){          
                    $service_fee = Price_Formatter::formatNumberNoSymbol($value['service_fee']);
                    $body[]=[
                        'position'=>'left',
                        'type'=>"text",
                        'value'=>t('Service Fee')." " . $service_fee
                    ];
                }
                if( $value['small_order_fee']>0){          
                    $small_order_fee = Price_Formatter::formatNumberNoSymbol($value['small_order_fee']);
                    $body[]=[
                        'position'=>'left',
                        'type'=>"text",
                        'value'=>t('Small Order Fee')." " . $small_order_fee
                    ];
                }                
                if( $value['delivery_fee']>0){          
                    $delivery_fee = Price_Formatter::formatNumberNoSymbol($value['delivery_fee']);
                    $body[]=[
                        'position'=>'left',
                        'type'=>"text",
                        'value'=>t('Delivery Fee')." " . $delivery_fee
                    ];
                }
                if( $value['tax_total']>0){
                    $tax_total = Price_Formatter::formatNumberNoSymbol($value['tax_total']);
                    $body[]=[
                        'position'=>'left',
                        'type'=>"text",
                        'value'=>t('Tax')." ". $tax_total
                    ];
                }
                if( $value['courier_tip']>0){
                    $courier_tip = Price_Formatter::formatNumberNoSymbol($value['courier_tip']);
                    $body[]=[
                        'position'=>'left',
                        'type'=>"text",
                        'value'=>t('Tips')." ". $courier_tip
                    ];
                }
                if( $value['total']>0){
                    $total = Price_Formatter::formatNumberNoSymbol($value['total']);
                    $body[]=[
                        'position'=>'left',
                        'type'=>"text",
                        'value'=>t('Total')." " . $total
                    ];
                }

                $body[]=[
                    'position'=>"left",
                    'type'=>"line_break",
                    'value'=>""
                ];
            }
        }        

                
        $data = [
            'job_id'=>$job_id,
            'settings'=>$settings,            
            'header'=>$header,
            'body'=>$body,
            'footer'=>$footer
       ];
       
       return $data;
    }
    
    public static function RawKitchenTicket($data=[],$settings=[])
    {
        $header = [];
        $job_id = isset($data['order_reference'])?$data['order_reference']:'';
        $items = isset($data['items'])?$data['items']:null;
        $body = [];
        $footer = [];
        
        $table_name = isset($data['table_name'])?$data['table_name']:'';
        $transaction_type = isset($data['transaction_type_pretty'])?$data['transaction_type_pretty']:'';
        $delivery_time = isset($data['delivery_time_pretty'])?$data['delivery_time_pretty']:'';
        $created_at = isset($data['created_at'])?$data['created_at']:'';
    
        $header[] = [
            'position'=>"left",
            'type'=>"font",
            'font_type'=>'bold'
        ];
        $header[] = [
            'position'=>"center",
            'type'=>"text",
            'value'=>$job_id
        ];
        if(!empty($table_name)){
            $header[] = [
                'position'=>"center",
                'type'=>"text",
                'value'=>$table_name
            ];
        }      
        $header[] = [
            'position'=>"left",
            'type'=>"font",
            'font_type'=>'normal'
        ];        
        $header[] = [
            'position'=>"left",
            'type'=>"text",
            'value'=>$created_at
        ];      
        $header[] = [
            'position'=>"left",
            'type'=>"dotted_line",
            'value'=>"-"
        ];        
        $header[] = [
            'position'=>"left",
            'type'=>"font",
            'font_type'=>'bold'
        ];
        $header[] = [
            'position'=>"center",
            'type'=>"text",
            'value'=>$transaction_type
        ];
        $header[] = [
            'position'=>"left",
            'type'=>"font",
            'font_type'=>'normal'
        ];        
        $header[] = [
            'position'=>"left",
            'type'=>"dotted_line",
            'value'=>"-"
        ];        
        $header[] = [
            'position'=>"left",
            'type'=>"line_break",
            'value'=>''
        ];

        $spaces_front = str_repeat(" ",4);        
                
        if(is_array($items) && count($items)>=1){
            foreach ($items as $value) {
                $attributes = isset($value['attributes'])?$value['attributes']:null;
                $addons = isset($value['addons'])?$value['addons']:null;

                $item_name = isset($value['item_name'])? trim($value['item_name']) :'';
                $qty = isset($value['qty'])?$value['qty']:0;

                $body[] = [
                    'position'=>"left",
                    'type'=>"text",
                    'value'=>"$qty x $item_name"
                ];

                if(is_array($attributes) && count($attributes)>=1){
                    foreach ($attributes as $attributes_items) {
                        $attributes_value = isset($attributes_items['value'])?$attributes_items['value']:'';
                        $body[] = [
                            'position'=>"left",
                            'type'=>"text",
                            'value'=>$spaces_front.$attributes_value
                        ];
                    }
                }

                if(is_array($addons) && count($addons)>=1){
                    foreach ($addons as $addons_items) {                        
                        $addons_items_value = isset($addons_items['value'])?$addons_items['value']:'';                        
                        $body[] = [
                            'position'=>"left",
                            'type'=>"text",
                            'value'=>$spaces_front."- ".$addons_items_value
                        ];
                    }
                }

            }
            // end for

            $body[] = [
                'position'=>"left",
                'type'=>"dotted_line",
                'value'=>"-"
            ];        
            $body[] = [
                'position'=>"left",
                'type'=>"line_break_big",
                'value'=>''
            ];
        }
        
        $data = [
            'job_id'=>$job_id,
            'settings'=>$settings,            
            'header'=>$header,
            'body'=>$body,
            'footer'=>$footer
       ];       
       return $data;
    }

    public static function RawKitchenPOS_Orders($data=[],$settings=[])
    {
        $header = [];
        $job_id = isset($data['order_reference'])?$data['order_reference']:'';
        $hold_order_reference = isset($data['hold_order_reference'])?$data['hold_order_reference']:'';

        $job_id = !empty($job_id)?$job_id:$hold_order_reference;

        $print_type = isset($settings['print_type'])?$settings['print_type']:'';

        $items = isset($data['items'])?$data['items']:null;
        $body = [];
        $footer = [];

        $table_name = isset($data['table_name'])?$data['table_name']:'';
        $room_name = isset($data['room_name'])?$data['room_name']:'';
        $customer_name = isset($data['customer_name'])?$data['customer_name']:'';
        $transaction_type = isset($data['transaction_type'])?$data['transaction_type']:'';
        $delivery_time = isset($data['date_pretty'])?$data['date_pretty']:'';
        $created_at = isset($data['date_pretty'])?$data['date_pretty']:'';
        
        $header[] = [
            'position'=>"left",
            'type'=>"line_break",
            'value'=>''
        ];
        $header[] = [
            'position'=>"left",
            'type'=>"font",
            'font_type'=>'bold'
        ];
        $header[] = [
            'position'=>"center",
            'type'=>"text",
            'value'=>"#".$job_id
        ];
        if(!empty($room_name)){
            $header[] = [
                'position'=>"center",
                'type'=>"text",
                'value'=>$room_name
            ];  
        }
        if(!empty($table_name)){
            $header[] = [
                'position'=>"center",
                'type'=>"text",
                'value'=>is_numeric($table_name)? t("Table #{table_name}",['{table_name}'=>$table_name]) :$table_name
            ];            
        }        
        $header[] = [
            'position'=>"left",
            'type'=>"line_break",
            'value'=>''
        ];        
        $header[] = [
            'position'=>"left",
            'type'=>"font",
            'font_type'=>'normal'
        ];        
        $header[] = [
            'position'=>"left",
            'type'=>"text",
            'value'=>$created_at
        ];      
        $header[] = [
            'position'=>"left",
            'type'=>"text",
            'value'=>$customer_name
        ];      
        $header[] = [
            'position'=>"left",
            'type'=>"dotted_line",
            'value'=>"-"
        ];        
        $header[] = [
            'position'=>"left",
            'type'=>"font",
            'font_type'=>'bold'
        ];
        $header[] = [
            'position'=>"center",
            'type'=>"text",
            'value'=>strtoupper($transaction_type)
        ];
        $header[] = [
            'position'=>"left",
            'type'=>"font",
            'font_type'=>'normal'
        ];        
        $header[] = [
            'position'=>"left",
            'type'=>"dotted_line",
            'value'=>"-"
        ];        
        $header[] = [
            'position'=>"left",
            'type'=>"line_break",
            'value'=>''
        ];

        $spaces_front = str_repeat(" ",4);  
        $with_price = false;
        
        if(is_array($items) && count($items)>=1){
            foreach ($items as $value) {                
                $item_name = isset($value['item_name'])? trim($value['item_name']) :'';
                $special_instructions = isset($value['special_instructions'])? trim($value['special_instructions']) :'';
                $qty = isset($value['qty'])?$value['qty']:0;

                $size_name = isset($value['size_name'])? trim($value['size_name']) :'';

                if($with_price==true){
                    $body[] = [
                        'position'=>"left_right_text",
                        'type'=>"text",
                        'label'=>"$qty x $item_name",
                        'value'=>$print_type=="raw"? Price_Formatter::formatNumberNoSymbol($value['item_price']) :$value['item_price_pretty']    
                    ];
                } else {
                    $body[] = [
                        'position'=>"left",
                        'type'=>"text",
                        'value'=>"$qty x $item_name"
                    ];         
                }                   
                           
                if(!empty($size_name)){
                    $body[] = [
                        'position'=>"left",
                        'type'=>"text",
                        'value'=>"($size_name)"
                    ];  
                }

                if(!empty($special_instructions)){
                    $body[] = [
                        'position'=>"left",
                        'type'=>"text",
                        'value'=>$spaces_front.$special_instructions
                    ];
                }

                $cooking = isset($value['cooking'])? $value['cooking'] :null;
                if(is_array($cooking) && count($cooking)>=1){
                    $cooking_list = '';
                    foreach ($cooking as $cooking_item) {     
                        $cooking_list.="$cooking_item,";
                    }
                    $cooking_list = !empty($cooking_list)?substr($cooking_list,0,-1):'';
                    $body[] = [
                        'position'=>"left",
                        'type'=>"text",
                        'value'=>$spaces_front.$cooking_list
                    ];
                }

                $ingredients = isset($value['ingredients'])? $value['ingredients'] :null;
                if(is_array($ingredients) && count($ingredients)>=1){
                    $ingredients_list = '';
                    foreach ($ingredients as $ingredients_item) {     
                        $ingredients_list.="$ingredients_item,";
                    }
                    $ingredients_list = !empty($ingredients_list)?substr($ingredients_list,0,-1):'';
                    $body[] = [
                        'position'=>"left",
                        'type'=>"text",
                        'value'=>$spaces_front.$ingredients_list
                    ];
                }

                $addons = isset($value['addons'])? $value['addons'] :null;
                if(is_array($addons) && count($addons)>=1){
                    foreach ($addons as $addons_item) {
                        if($addons_item['multi_option']=="multiple"){
                            $qty_addon = $addons_item['qty'];
                        } else $qty_addon = $qty;                        
                        $body[] = [
                            'position'=>"left",
                            'type'=>"text",
                            'value'=>$spaces_front."$qty_addon x ".$addons_item['sub_item_name']
                        ];
                    }
                }

                $body[] = [
                    'position'=>"left",
                    'type'=>"line_break",
                    'value'=>''
                ];

            }
            // end for
        }        

        $body[] = [
            'position'=>"left",
            'type'=>"dotted_line",
            'value'=>"-"
        ];        
        $footer[] = [
            'position'=>"left",
            'type'=>"line_break",
            'value'=>''
        ];
        
        $data = [
            'job_id'=>$job_id,
            'settings'=>$settings,            
            'header'=>$header,
            'body'=>$body,
            'footer'=>$footer
       ];       
       return $data;
    }

}
// end class
