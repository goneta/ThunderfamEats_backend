<?php
$menu_type_merchant = 'merchant';

// Dashboard
$parent_id = AttributesTools::getMenuParentID($menu_type_merchant,'Dashboard');
if($parent_id){      
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Order Summary";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/dashboard/order_summary";
    $model->action_name="merchant/dashboard/order_summary";
    $model->sequence=1;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Week Sales";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/dashboard/week_sales";
    $model->action_name="merchant/dashboard/week_sales";
    $model->sequence=2;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Daily statistic";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/dashboard/today_summary";
    $model->action_name="merchant/dashboard/today_summary";
    $model->sequence=2;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Last 5 Orders";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/dashboard/last_5_orders";
    $model->action_name="merchant/dashboard/last_5_orders";
    $model->sequence=3;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Popular items";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/dashboard/popular_items";
    $model->action_name="merchant/dashboard/popular_items";
    $model->sequence=3;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Sales overview";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/dashboard/sales_overview";
    $model->action_name="merchant/dashboard/sales_overview";
    $model->sequence=4;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Top Customers";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/dashboard/top_customer";
    $model->action_name="merchant/dashboard/top_customer";
    $model->sequence=5;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Overview of Review";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/dashboard/review_overview";
    $model->action_name="merchant/dashboard/review_overview";
    $model->sequence=6;
    $model->visible =0;
    $model->save();
}
// Dashboard

// Merchant
$parent_id = AttributesTools::getMenuParentID($menu_type_merchant,'Merchant');
if($parent_id){      
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Merchant Information";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/edit";
    $model->action_name="merchant/edit";
    $model->sequence=1;
    $model->visible =1;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Login information";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/login";
    $model->action_name="merchant/login";
    $model->sequence=2;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Address";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/address";
    $model->action_name="merchant/address";
    $model->sequence=3;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Payment history";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/payment_history";
    $model->action_name="merchant/payment_history";
    $model->sequence=3.1;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Settings";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/settings";
    $model->action_name="merchant/settings";
    $model->sequence=4;
    $model->visible =1;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Time Zone";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/timezone";
    $model->action_name="merchant/timezone";
    $model->sequence=4.1;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Store Hours";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/store_hours";
    $model->action_name="merchant/store_hours";
    $model->sequence=5;
    $model->visible =0;
    $model->role_create = 'merchant/store_hours_create';
    $model->role_update = 'merchant/store_hours_update';
    $model->role_delete = 'merchant/store_hours_delete';
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Taxes";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/taxes";
    $model->action_name="merchant/taxes";
    $model->sequence=6;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="SEO";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/seo";
    $model->action_name="merchant/seo";
    $model->sequence=6.1;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Kitchen Workload";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/kitchen_settings";
    $model->action_name="merchant/kitchen_settings";
    $model->sequence=6.11;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Location Management";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/location_management";
    $model->action_name="merchant/location_management";
    $model->sequence=6.2;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Delivery Management";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/delivery_management";
    $model->action_name="merchant/delivery_management";
    $model->sequence=6.3;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Time Estimates Management";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/estimate_management";
    $model->action_name="merchant/estimate_management";
    $model->sequence=6.4;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Zone";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/zone_settings";
    $model->action_name="merchant/zone_settings";
    $model->sequence=6.2;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Search Mode";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/search_settings";
    $model->action_name="merchant/search_settings";
    $model->sequence=7;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Login & Signup";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/login_sigup";
    $model->action_name="merchant/login_sigup";
    $model->sequence=8;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Phone Settings";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/phone_settings";
    $model->action_name="merchant/phone_settings";
    $model->sequence=9;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Social Settings";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/social_settings";
    $model->action_name="merchant/social_settings";
    $model->sequence=10;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Google Recaptcha";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/recaptcha_settings";
    $model->action_name="merchant/recaptcha_settings";
    $model->sequence=11;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Map API Keys";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/map_keys";
    $model->action_name="merchant/map_keys";
    $model->sequence=12;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Notification Settings";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/notification_settings";
    $model->action_name="merchant/notification_settings";
    $model->sequence=13;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Orders Settings";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/orders_settings";
    $model->action_name="merchant/orders_settings";
    $model->sequence=14;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Menu Options";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/menu_options";
    $model->action_name="merchant/menu_options";
    $model->sequence=15;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Mobile Page";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/mobilepage";
    $model->action_name="merchant/mobilepage";
    $model->sequence=16;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Order limit";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/time_management";
    $model->action_name="merchant/time_management";
    $model->sequence=17;
    $model->visible =1;
    $model->role_create = 'merchant/time_management_create';
    $model->role_update = 'merchant/time_management_update';
    $model->role_delete = 'merchant/time_mgt_delete';
    $model->role_view = ''; 
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Banner";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/banner";
    $model->action_name="merchant/banner";
    $model->sequence=18;
    $model->visible =1;
    $model->role_create = 'merchant/banner_create';
    $model->role_update = 'merchant/banner_update';
    $model->role_delete = 'merchant/banner_delete';
    $model->role_view = ''; 
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Pages";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/pages_list";
    $model->action_name="merchant/pages_list";
    $model->sequence=19;
    $model->visible =1;
    $model->role_create = 'merchant/pages_create';
    $model->role_update = 'merchant/page_update';
    $model->role_delete = 'merchant/pages_delete';
    $model->role_view = ''; 
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Menu";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/pages_menu";
    $model->action_name="merchant/pages_menu";
    $model->sequence=20;
    $model->visible =1;
    $model->save();

}
// Merchant


// Merchant
$parent_id = AttributesTools::getMenuAction($menu_type_merchant,'merchant.orders',1);
if($parent_id){      
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="View Order";
    $model->parent_id=intval($parent_id);
    $model->link="orders/view";
    $model->action_name="orders/view";
    $model->sequence=0;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="New Orders";
    $model->parent_id=intval($parent_id);
    $model->link="orders/new";
    $model->action_name="orders/new";
    $model->sequence=1;
    $model->visible =1;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Orders Processing";
    $model->parent_id=intval($parent_id);
    $model->link="orders/processing";
    $model->action_name="orders/processing";
    $model->sequence=2;
    $model->visible =1;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Orders Ready";
    $model->parent_id=intval($parent_id);
    $model->link="orders/ready";
    $model->action_name="orders/ready";
    $model->sequence=3;
    $model->visible =1;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Completed";
    $model->parent_id=intval($parent_id);
    $model->link="orders/completed";
    $model->action_name="orders/completed";
    $model->sequence=4;
    $model->visible =1;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Scheduled";
    $model->parent_id=intval($parent_id);
    $model->link="orders/scheduled";
    $model->action_name="orders/scheduled";
    $model->sequence=5;
    $model->visible =1;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="All Orders";
    $model->parent_id=intval($parent_id);
    $model->link="orders/history";
    $model->action_name="orders/history";
    $model->sequence=6;
    $model->visible =1;
    $model->save();
}
// Merchant


// POS
$parent_id = AttributesTools::getMenuParentID($menu_type_merchant,'POS');
if($parent_id){      
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Create Order";
    $model->parent_id=intval($parent_id);
    $model->link="pos/create_order";
    $model->action_name="pos/create_order";
    $model->sequence=1;
    $model->visible =1;
    $model->role_create = 'pos/createorder';
    $model->save();

    // $model = new AR_menu();
    // $model->menu_type=$menu_type_merchant;
    // $model->menu_name="Hold Orders";
    // $model->parent_id=intval($parent_id);
    // $model->link="pos/hold_orders";
    // $model->action_name="pos/hold_orders";
    // $model->sequence=2;
    // $model->visible =1;
    // $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Order History";
    $model->parent_id=intval($parent_id);
    $model->link="pos/order_history";
    $model->action_name="pos/order_history";
    $model->sequence=2;
    $model->visible =1;
    $model->role_create = 'pos/orderhistory';
    $model->save();

}
// POS

// Delivery management
$parent_id = AttributesTools::getMenuParentID($menu_type_merchant,'Delivery Management');
if($parent_id){ 
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        
} else {        
    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Delivery Management";
    $model->parent_id=intval($parent_id);
    $model->link="";
    $model->action_name="merchant.driver";
    $model->sequence=4;
    $model->visible =1;        
    if($model->save()){
        $parent_id = $model->menu_id;
    }    
}

$model = new AR_menu();
$model->menu_type=$menu_type_merchant;
$model->menu_name="Cashout list";
$model->parent_id=intval($parent_id);
$model->link="merchantdriver/cashout_list";
$model->action_name="merchantdriver/cashout_list";
$model->sequence=1;
$model->visible =1;
$model->save();

$model = new AR_menu();
$model->menu_type=$menu_type_merchant;
$model->menu_name="Collect cash";
$model->parent_id=intval($parent_id);
$model->link="merchantdriver/collect_cash";
$model->action_name="merchantdriver/collect_cash";
$model->sequence=2;
$model->visible =1;
$model->role_create = 'merchantdriver/collect_cash_add';
//$model->role_update = 'merchantdriver/collect_cash_delete';
$model->role_delete = 'merchantdriver/collect_cash_delete';
$model->role_view = 'merchantdriver/collect_transactions'; 
$model->save();

$model = new AR_menu();
$model->menu_type=$menu_type_merchant;
$model->menu_name="Driver list";
$model->parent_id=intval($parent_id);
$model->link="merchantdriver/list";
$model->action_name="merchantdriver/list";
$model->sequence=3;
$model->visible =1;
$model->role_create = 'merchantdriver/add';
$model->role_update = 'merchantdriver/update';
$model->role_delete = 'merchantdriver/delete';
$model->role_view = 'merchantdriver/overview'; 
$model->save();

$model = new AR_menu();
$model->menu_type=$menu_type_merchant;
$model->menu_name="Driver License";
$model->parent_id=intval($parent_id);
$model->link="merchantdriver/license";
$model->action_name="merchantdriver/license";
$model->sequence=3.1;
$model->visible =0;
$model->save();

$model = new AR_menu();
$model->menu_type=$menu_type_merchant;
$model->menu_name="Driver Vehicle";
$model->parent_id=intval($parent_id);
$model->link="merchantdriver/vehicle";
$model->action_name="merchantdriver/vehicle";
$model->sequence=3.2;
$model->visible =0;
$model->save();

$model = new AR_menu();
$model->menu_type=$menu_type_merchant;
$model->menu_name="Driver Bank Information";
$model->parent_id=intval($parent_id);
$model->link="merchantdriver/bank_info";
$model->action_name="merchantdriver/bank_info";
$model->sequence=3.3;
$model->visible =0;
$model->save();

$model = new AR_menu();
$model->menu_type=$menu_type_merchant;
$model->menu_name="Driver Wallet";
$model->parent_id=intval($parent_id);
$model->link="merchantdriver/wallet";
$model->action_name="merchantdriver/wallet";
$model->sequence=3.4;
$model->visible =0;
$model->save();

$model = new AR_menu();
$model->menu_type=$menu_type_merchant;
$model->menu_name="Driver Cashout";
$model->parent_id=intval($parent_id);
$model->link="merchantdriver/cashout_transactions";
$model->action_name="merchantdriver/cashout_transactions";
$model->sequence=3.5;
$model->visible =0;
$model->save();

$model = new AR_menu();
$model->menu_type=$menu_type_merchant;
$model->menu_name="Driver Delivery Transactions";
$model->parent_id=intval($parent_id);
$model->link="merchantdriver/delivery_transactions";
$model->action_name="merchantdriver/delivery_transactions";
$model->sequence=3.6;
$model->visible =0;
$model->save();

$model = new AR_menu();
$model->menu_type=$menu_type_merchant;
$model->menu_name="Driver Order Tips";
$model->parent_id=intval($parent_id);
$model->link="merchantdriver/order_tips";
$model->action_name="merchantdriver/order_tips";
$model->sequence=3.7;
$model->visible =0;
$model->save();

$model = new AR_menu();
$model->menu_type=$menu_type_merchant;
$model->menu_name="Driver Time Logs";
$model->parent_id=intval($parent_id);
$model->link="merchantdriver/time_logs";
$model->action_name="merchantdriver/time_logs";
$model->sequence=3.8;
$model->visible =0;
$model->save();

$model = new AR_menu();
$model->menu_type=$menu_type_merchant;
$model->menu_name="Driver Review";
$model->parent_id=intval($parent_id);
$model->link="merchantdriver/review_ratings";
$model->action_name="merchantdriver/review_ratings";
$model->sequence=3.9;
$model->visible =0;
$model->save();


$model = new AR_menu();
$model->menu_type=$menu_type_merchant;
$model->menu_name="Car registration";
$model->parent_id=intval($parent_id);
$model->link="merchantdriver/carlist";
$model->action_name="merchantdriver/carlist";
$model->sequence=4;
$model->visible =1;
$model->role_create = 'merchantdriver/addcar';
$model->role_update = 'merchantdriver/update_car';
$model->role_delete = 'merchantdriver/delete_car';
$model->save();

$model = new AR_menu();
$model->menu_type=$menu_type_merchant;
$model->menu_name="Groups";
$model->parent_id=intval($parent_id);
$model->link="merchantdriver/group_list";
$model->action_name="merchantdriver/group_list";
$model->sequence=5;
$model->visible =1;
$model->role_create = 'merchantdriver/addgroup';
$model->role_update = 'merchantdriver/group_update';
$model->role_delete = 'merchantdriver/group_delete';
$model->save();

$model = new AR_menu();
$model->menu_type=$menu_type_merchant;
$model->menu_name="Zones";
$model->parent_id=intval($parent_id);
$model->link="merchantdriver/zone_list";
$model->action_name="merchantdriver/zone_list";
$model->sequence=6;
$model->visible =1;
$model->role_create = 'merchantdriver/zone_create';
$model->role_update = 'merchantdriver/zone_update';
$model->role_delete = 'merchantdriver/zone_delete';
$model->save();

$model = new AR_menu();
$model->menu_type=$menu_type_merchant;
$model->menu_name="Employee Schedule";
$model->parent_id=intval($parent_id);
$model->link="merchantdriver/schedule_list";
$model->action_name="merchantdriver/schedule_list";
$model->sequence=7;
$model->visible =1;
$model->role_create = 'merchantdriver/schedule_add';
$model->role_update = 'merchantdriver/schedule_update';
$model->role_delete = 'merchantdriver/schedule_delete';
$model->role_view = 'merchantdriver/schedule_bulk';    
$model->save();

$model = new AR_menu();
$model->menu_type=$menu_type_merchant;
$model->menu_name="Shifts Schedule";
$model->parent_id=intval($parent_id);
$model->link="merchantdriver/shift_list";
$model->action_name="merchantdriver/shift_list";
$model->sequence=8;
$model->visible =1;
$model->role_create = 'merchantdriver/shift_add';
$model->role_update = 'merchantdriver/shift_update';
$model->role_delete = 'merchantdriver/shift_delete';
$model->role_view = 'merchantdriver/shift_bulkupload';    
$model->save();

$model = new AR_menu();
$model->menu_type=$menu_type_merchant;
$model->menu_name="Reviews";
$model->parent_id=intval($parent_id);
$model->link="merchantdriver/review_list";
$model->action_name="merchantdriver/review_list";
$model->role_create = 'merchantdriver/review_create';
$model->role_update = 'merchantdriver/review_update';
$model->role_delete = 'merchantdriver/review_delete';
$model->sequence=9;
$model->visible =1;
$model->save();

// Delivery management


// CAMPAIGN
$parent_id = AttributesTools::getMenuParentID($menu_type_merchant,'Campaigns');
if($parent_id){ 
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        
} else {        
    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Campaigns";
    $model->parent_id=intval($parent_id);
    $model->link="";
    $model->action_name="merchant.campaigns";
    $model->sequence=4.0;
    $model->visible =1;        
    if($model->save()){
        $parent_id = $model->menu_id;
    }    
}
$model = new AR_menu();
$model->menu_type=$menu_type_merchant;
$model->menu_name="Loyalty Points";
$model->parent_id=intval($parent_id);
$model->link="merchant/loyalty_points";
$model->action_name="merchant/loyalty_points";
$model->sequence=1;
$model->visible =1;
$model->save();

$model = new AR_menu();
$model->menu_type=$menu_type_merchant;
$model->menu_name="Suggested Items";
$model->parent_id=intval($parent_id);
$model->link="merchant/suggested_items";
$model->action_name="merchant/suggested_items";
$model->sequence=2;
$model->visible =1;
$model->save();

$model = new AR_menu();
$model->menu_type=$menu_type_merchant;
$model->menu_name="Spend-Based Free Item";
$model->parent_id=intval($parent_id);
$model->link="merchant/free_item";
$model->action_name="merchant/free_item";
$model->sequence=3;
$model->visible =1;
$model->save();
// CAMPAIGN

// CHAT
$parent_id = AttributesTools::getMenuParentID($menu_type_merchant,'Communication');
if($parent_id){ 
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        
} else {        
    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Communication";
    $model->parent_id=intval($parent_id);
    $model->link="";
    $model->action_name="merchant.communication";
    $model->sequence=4.1;
    $model->visible =1;        
    if($model->save()){
        $parent_id = $model->menu_id;
    }    
}
$model = new AR_menu();
$model->menu_type=$menu_type_merchant;
$model->menu_name="Chats";
$model->parent_id=intval($parent_id);
$model->link="communications/chats";
$model->action_name="communications/chats";
$model->sequence=1;
$model->visible =1;
$model->role_create = 'communications/framechat';
$model->save();
// END CHAT


// Table Booking
$parent_id = AttributesTools::getMenuParentID($menu_type_merchant,'Reservations');
if($parent_id){      
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $criteria = new CDbCriteria();
    $criteria->condition = "menu_id=:menu_id";
    $criteria->params = [':menu_id'=>$parent_id];        
    $model = AR_menu::model()->find($criteria);
    $model->sequence = 4;
    $model->menu_name = "Reservations";
    $model->save();
} else {        
    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Reservations";
    $model->parent_id=intval($parent_id);
    $model->link="";
    $model->action_name="table.booking";
    $model->sequence=4;
    $model->visible =1;        
    if($model->save()){
        $parent_id = $model->menu_id;
    }    
}  
if($parent_id){
    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="List";
    $model->parent_id=intval($parent_id);
    $model->link="booking/list";
    $model->action_name="booking/list";
    $model->sequence=1;
    $model->visible =1;
    $model->role_create = 'booking/create_reservation';
    $model->role_update = 'booking/update_reservation';
    $model->role_delete = 'booking/reservation_delete';
    $model->role_view = 'booking/reservation_overview'; 
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Booking Update Status";
    $model->parent_id=intval($parent_id);
    $model->link="booking/update_status";
    $model->action_name="booking/update_status";
    $model->sequence=2;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Settings";
    $model->parent_id=intval($parent_id);
    $model->link="booking/settings";
    $model->action_name="booking/settings";
    $model->sequence=3;
    $model->visible =1;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Shifts";
    $model->parent_id=intval($parent_id);
    $model->link="booking/shifts";
    $model->action_name="booking/shifts";
    $model->sequence=4;
    $model->visible =1;
    $model->role_create = 'booking/create_shift';
    $model->role_update = 'booking/update_shift';
    $model->role_delete = 'booking/shift_delete';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Room";
    $model->parent_id=intval($parent_id);
    $model->link="booking/room";
    $model->action_name="booking/room";
    $model->sequence=5;
    $model->visible =1;
    $model->role_create = 'booking/create_room';
    $model->role_update = 'booking/update_room';
    $model->role_delete = 'booking/room_delete';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Tables";
    $model->parent_id=intval($parent_id);
    $model->link="booking/tables";
    $model->action_name="booking/tables";
    $model->sequence=6;
    $model->visible =1;
    $model->role_create = 'booking/create_table';
    $model->role_update = 'booking/update_tables';
    $model->role_delete = 'booking/tables_delete';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Generate Table";
    $model->parent_id=intval($parent_id);
    $model->link="booking/generate_table";
    $model->action_name="booking/generate_table";
    $model->sequence=6.1;
    $model->visible =0;    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Tableside QrCode Configuration";
    $model->parent_id=intval($parent_id);
    $model->link="booking/tableside_config";
    $model->action_name="booking/tableside_config";
    $model->sequence=6.2;
    $model->visible =0;    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="View Table QrCode";
    $model->parent_id=intval($parent_id);
    $model->link="booking/table_view";
    $model->action_name="booking/table_view";
    $model->sequence=6.3;
    $model->visible =0;    
    $model->save();

}
// Table Booking

// Table Booking
$parent_id = AttributesTools::getMenuParentID($menu_type_merchant,'Attributes');
if($parent_id){      
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Size";
    $model->parent_id=intval($parent_id);
    $model->link="attrmerchant/size_list";
    $model->action_name="attrmerchant/size_list";
    $model->sequence=1;
    $model->visible =1;
    $model->role_create = 'attrmerchant/size_create';
    $model->role_update = 'attrmerchant/size_update';
    $model->role_delete = 'attrmerchant/size_delete';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Ingredients";
    $model->parent_id=intval($parent_id);
    $model->link="attrmerchant/ingredients_list";
    $model->action_name="attrmerchant/ingredients_list";
    $model->sequence=2;
    $model->visible =1;
    $model->role_create = 'attrmerchant/ingredients_create';
    $model->role_update = 'attrmerchant/ingredients_update';
    $model->role_delete = 'attrmerchant/ingredients_delete';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Cooking Reference";
    $model->parent_id=intval($parent_id);
    $model->link="attrmerchant/cookingref_list";
    $model->action_name="attrmerchant/cookingref_list";
    $model->sequence=3;
    $model->visible =1;
    $model->role_create = 'attrmerchant/cookingref_create';
    $model->role_update = 'attrmerchant/cookingref_update';
    $model->role_delete = 'attrmerchant/cookingref_delete';    
    $model->save();
   
}
// Table Booking


// Food
$parent_id = AttributesTools::getMenuParentID($menu_type_merchant,'Food');
if($parent_id){      
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Category";
    $model->parent_id=intval($parent_id);
    $model->link="food/category";
    $model->action_name="food/category";
    $model->sequence=1;
    $model->visible =1;
    $model->role_create = 'food/category_create';
    $model->role_update = 'food/category_update';
    $model->role_delete = 'food/category_delete';   
    $model->role_view = 'food/category_sort';  
    $model->save();    

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Category Availability";
    $model->parent_id=intval($parent_id);
    $model->link="food/category_availability";
    $model->action_name="food/category_availability";
    $model->sequence=2;
    $model->visible =0;    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Addon Category";
    $model->parent_id=intval($parent_id);
    $model->link="food/addoncategory";
    $model->action_name="food/addoncategory";
    $model->sequence=3;
    $model->visible =1;
    $model->role_create = 'food/addoncategory_create';
    $model->role_update = 'food/addoncategory_update';
    $model->role_delete = 'food/addoncategory_delete';   
    $model->role_view = 'food/addoncategory_sort';   
    $model->save();
    
    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Addon Items";
    $model->parent_id=intval($parent_id);
    $model->link="food/addonitem";
    $model->action_name="food/addonitem";
    $model->sequence=5;
    $model->visible =1;
    $model->role_create = 'food/addonitem_create';
    $model->role_update = 'food/addonitem_update';
    $model->role_delete = 'food/addonitem_delete';    
    $model->role_view = 'food/addonitem_sort';   
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Items";
    $model->parent_id=intval($parent_id);
    $model->link="food/items";
    $model->action_name="food/items";
    $model->sequence=7;
    $model->visible =1;
    $model->role_create = 'food/item_create';
    $model->role_update = 'food/item_update';
    $model->role_delete = 'food/item_delete';  
    $model->role_view = 'food/item_sort';     
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Items Availability";
    $model->parent_id=intval($parent_id);
    $model->link="food/items_availability";
    $model->action_name="food/items_availability";
    $model->sequence=7.1;
    $model->visible =1;
    //$model->role_create = 'food/item_create';
    // $model->role_update = 'food/item_update';
    // $model->role_delete = 'food/item_delete';  
    // $model->role_view = 'food/item_sort';     
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Bulk Import";
    $model->parent_id=intval($parent_id);
    $model->link="food/bulkimport";
    $model->action_name="food/bulkimport";
    $model->sequence=7.01;
    $model->visible =0;    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Item Duplicate";
    $model->parent_id=intval($parent_id);
    $model->link="food/item_duplicate";
    $model->action_name="food/item_duplicate";
    $model->sequence=7.02;
    $model->visible =0;    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Items price";
    $model->parent_id=intval($parent_id);
    $model->link="food/item_price";
    $model->action_name="food/item_price";
    $model->sequence=7.1;
    $model->visible =0;    
    $model->role_create = 'food/itemprice_create';
    $model->role_update = 'food/itemprice_update';
    $model->role_delete = 'food/itemprice_delete';  
    $model->role_view = '';     
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Items addon";
    $model->parent_id=intval($parent_id);
    $model->link="food/item_addon";
    $model->action_name="food/item_addon";
    $model->sequence=7.2;
    $model->visible =0;    
    $model->role_create = 'food/itemaddon_create';
    $model->role_update = 'food/itemaddon_update';
    $model->role_delete = 'food/itemaddon_delete';  
    $model->role_view = 'food/itemaddon_sort';     
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Items attributes";
    $model->parent_id=intval($parent_id);
    $model->link="food/item_attributes";
    $model->action_name="food/item_attributes";
    $model->sequence=7.3;
    $model->visible =0;    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Tax";
    $model->parent_id=intval($parent_id);
    $model->link="food/item_tax";
    $model->action_name="food/item_tax";
    $model->sequence=7.3;
    $model->visible =0;    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Items availability";
    $model->parent_id=intval($parent_id);
    $model->link="food/item_availability";
    $model->action_name="food/item_availability";
    $model->sequence=7.4;
    $model->visible =0;    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Items inventory";
    $model->parent_id=intval($parent_id);
    $model->link="food/item_inventory";
    $model->action_name="food/item_inventory";
    $model->sequence=7.5;
    $model->visible =0;    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="View Barcode";
    $model->parent_id=intval($parent_id);
    $model->link="food/view_barcode";
    $model->action_name="food/view_barcode";
    $model->sequence=7.51;
    $model->visible =0;    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Items promo";
    $model->parent_id=intval($parent_id);
    $model->link="food/item_promos";
    $model->action_name="food/item_promos";
    $model->sequence=7.6;
    $model->visible =0;  
    $model->role_create = 'food/itempromo_create';
    $model->role_update = 'food/itempromo_update';
    $model->role_delete = 'food/itempromo_delete';           
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Items gallery";
    $model->parent_id=intval($parent_id);
    $model->link="food/item_gallery";
    $model->action_name="food/item_gallery";
    $model->sequence=7.7;
    $model->visible =0;    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Items seo";
    $model->parent_id=intval($parent_id);
    $model->link="food/item_seo";
    $model->action_name="food/item_seo";
    $model->sequence=7.8;
    $model->visible =0;    
    $model->save();

}
// Food


// Order type
$parent_id = AttributesTools::getMenuParentID($menu_type_merchant,'Order type');
if($parent_id){      
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Delivery Settings";
    $model->parent_id=intval($parent_id);
    $model->link="services/delivery_settings";
    $model->action_name="services/delivery_settings";
    $model->sequence=1;
    $model->visible =1;    
    $model->save();    

    // $model = new AR_menu();
    // $model->menu_type=$menu_type_merchant;
    // $model->menu_name="Settings";
    // $model->parent_id=intval($parent_id);
    // $model->link="services/delivery_settings";
    // $model->action_name="services/delivery_settings";
    // $model->sequence=2;
    // $model->visible =0;    
    // $model->save();    

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Fixed Charge";
    $model->parent_id=intval($parent_id);
    $model->link="services/fixed_charge";
    $model->action_name="services/fixed_charge";
    $model->sequence=3;
    $model->visible =0;    
    $model->save();    

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Charges Table";
    $model->parent_id=intval($parent_id);
    $model->link="services/charges_table";
    $model->action_name="services/charges_table";
    $model->sequence=4;
    $model->visible =0;    
    $model->role_create = 'services/chargestable_create';
    $model->role_update = 'services/chargestable_update';
    $model->role_delete = 'services/chargestable_delete';    
    $model->save();    

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Pickup";
    $model->parent_id=intval($parent_id);
    $model->link="services/settings_pickup";
    $model->action_name="services/settings_pickup";
    $model->sequence=5;
    $model->visible =1;    
    $model->save();   

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Pickup Instructions";
    $model->parent_id=intval($parent_id);
    $model->link="services/pickup_instructions";
    $model->action_name="services/pickup_instructions";
    $model->sequence=6;
    $model->visible =0;    
    $model->save();  

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Dinein";
    $model->parent_id=intval($parent_id);
    $model->link="services/settings_dinein";
    $model->action_name="services/settings_dinein";
    $model->sequence=7;
    $model->visible =1;    
    $model->save();   

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Dinein Instructions";
    $model->parent_id=intval($parent_id);
    $model->link="services/dinein_instructions";
    $model->action_name="services/dinein_instructions";
    $model->sequence=8;
    $model->visible =0;    
    $model->save();  

}
// Order type


// Payment gateway
$parent_id = AttributesTools::getMenuParentID($menu_type_merchant,'Payment gateway');
if($parent_id){      
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="All Payments";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/payment_list";
    $model->action_name="merchant/payment_list";
    $model->sequence=1;
    $model->visible =1;
    $model->role_create = 'merchant/payment_create';
    $model->role_update = 'merchant/payment_update';
    $model->role_delete = 'merchant/payment_delete';  
    $model->role_view = '';      
    $model->save();    

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Bank Deposit";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/bank_deposit";
    $model->action_name="merchant/bank_deposit";
    $model->sequence=2;
    $model->visible =1;    
    $model->role_create = '';
    $model->role_update = 'invoice/bank_deposit_view';
    $model->role_delete = 'invoice/bank_deposit_delete';  
    $model->role_view = '';      
    $model->save();    

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Pay on delivery";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/payondelivery";
    $model->action_name="merchant/payondelivery";
    $model->sequence=3;
    $model->visible =1;       
    $model->save();    
}
//Payment gateway


// Promo
$parent_id = AttributesTools::getMenuParentID($menu_type_merchant,'Promo');
if($parent_id){      
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Coupon";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/coupon";
    $model->action_name="merchant/coupon";
    $model->sequence=1;
    $model->visible =1;
    $model->role_create = 'merchant/coupon_create';
    $model->role_update = 'merchant/coupon_update';
    $model->role_delete = 'merchant/coupon_delete';  
    $model->role_view = '';   
    $model->save();    

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Offers";
    $model->parent_id=intval($parent_id);
    $model->link="merchant/offers";
    $model->action_name="merchant/offers";
    $model->sequence=2;
    $model->visible =1;
    $model->role_create = 'merchant/offer_create';
    $model->role_update = 'merchant/offer_update';
    $model->role_delete = 'merchant/offer_delete';  
    $model->role_view = '';   
    $model->save();    
}
// Promo


// Images
$parent_id = AttributesTools::getMenuParentID($menu_type_merchant,'Images');
if($parent_id){      
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Gallery";
    $model->parent_id=intval($parent_id);
    $model->link="images/gallery";
    $model->action_name="images/gallery";
    $model->sequence=1;
    $model->visible =1;    
    $model->save();    

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Media Library";
    $model->parent_id=intval($parent_id);
    $model->link="images/media_library";
    $model->action_name="images/media_library";
    $model->sequence=2;
    $model->visible =1;    
    $model->save();    

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Get Media Files";
    $model->parent_id=intval($parent_id);
    $model->link="upload/getMedia";
    $model->action_name="upload/getMedia";
    $model->sequence=3;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Get Media Files Selected";
    $model->parent_id=intval($parent_id);
    $model->link="upload/getMediaSeleted";
    $model->action_name="upload/getMediaSeleted";
    $model->sequence=4;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Upload Media Files";
    $model->parent_id=intval($parent_id);
    $model->link="upload/file";
    $model->action_name="upload/file";
    $model->sequence=5;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Delete media files";
    $model->parent_id=intval($parent_id);
    $model->link="upload/deleteFiles";
    $model->action_name="upload/deleteFiles";
    $model->sequence=6;
    $model->visible =0;
    $model->save();
}
//Images


// Account
$parent_id = AttributesTools::getMenuParentID($menu_type_merchant,'Account');
if($parent_id){     
    
    Yii::app()->db->createCommand("
    UPDATE {{menu}}
    SET action_name='merchant.account'
    WHERE
    menu_id = ".q($parent_id)."
    ")->query();

    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Statement";
    $model->parent_id=intval($parent_id);
    $model->link="commission/statement";
    $model->action_name="commission/statement";
    $model->sequence=1;
    $model->visible =1;    
    $model->save();    

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Withdrawals";
    $model->parent_id=intval($parent_id);
    $model->link="commission/withdrawals";
    $model->action_name="commission/withdrawals";
    $model->sequence=1;
    $model->visible =1;    
    $model->save();    
}
//Account


// Invoice
$parent_id = AttributesTools::getMenuParentID($menu_type_merchant,'Invoice');
if($parent_id){      
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="List";
    $model->parent_id=intval($parent_id);
    $model->link="invoicemerchant/list";
    $model->action_name="invoicemerchant/list";
    $model->sequence=1;
    $model->visible =1;    
    $model->save();      

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Invoice View";
    $model->parent_id=intval($parent_id);
    $model->link="invoicemerchant/view";
    $model->action_name="invoicemerchant/view";
    $model->sequence=2;
    $model->visible =0;    
    $model->save();      

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Upload Deposit";
    $model->parent_id=intval($parent_id);
    $model->link="invoicemerchant/uploaddeposit";
    $model->action_name="invoicemerchant/uploaddeposit";
    $model->sequence=3;
    $model->visible =0;    
    $model->save();      

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="View PDF";
    $model->parent_id=intval($parent_id);
    $model->link="invoicemerchant/pdf";
    $model->action_name="invoicemerchant/pdf";
    $model->sequence=4;
    $model->visible =0;    
    $model->save();      
}
//Account

//Buyers
$parent_id = AttributesTools::getMenuParentID($menu_type_merchant,'Buyers');
if($parent_id){      
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Customer list";
    $model->parent_id=intval($parent_id);
    $model->link="customer/list";
    $model->action_name="customer/list";
    $model->sequence=1;
    $model->visible =1;    
    $model->role_view = 'customer/view';   
    $model->save();    

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Address";
    $model->parent_id=intval($parent_id);
    $model->link="customer/addresses";
    $model->action_name="customer/addresses";
    $model->sequence=2;
    $model->visible =0;
    $model->role_create = 'customer/address_create';
    $model->role_update = 'customer/address_update';
    $model->role_delete = 'customer/address_delete';      
    $model->save();    

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Order History";
    $model->parent_id=intval($parent_id);
    $model->link="customer/order_history";
    $model->action_name="customer/order_history";
    $model->sequence=3;
    $model->visible =0;    
    $model->save();      

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Print PDF";
    $model->parent_id=intval($parent_id);
    $model->link="print/pdf";
    $model->action_name="print/pdf";
    $model->sequence=4;
    $model->visible =0;    
    $model->save();      

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Review List";
    $model->parent_id=intval($parent_id);
    $model->link="customer/reviews";
    $model->action_name="customer/reviews";
    $model->sequence=5;
    $model->visible =1;    
    $model->role_create = 'customer/review_reply';
    $model->role_update = 'customer/review_reply_update';
    $model->role_delete = 'customer/customerreview_delete';      
    $model->save();     
    
    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Email Subscribers";
    $model->parent_id=intval($parent_id);
    $model->link="customer/email_subscriber";
    $model->action_name="customer/email_subscriber";    
    $model->sequence=6;
    $model->visible =1;       
    $model->role_create = '';
    $model->role_update = '';
    $model->role_delete = 'customer/esubscriber_delete';
    $model->role_view = ''; 
    $model->save();
}
// Buyers


//Buyers
$parent_id = AttributesTools::getMenuParentID($menu_type_merchant,'Users');
if($parent_id){      
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="All User";
    $model->parent_id=intval($parent_id);
    $model->link="usermerchant/user_list";
    $model->action_name="usermerchant/user_list";
    $model->sequence=1;
    $model->visible =1;    
    $model->role_create = 'usermerchant/user_create';
    $model->role_update = 'usermerchant/user_update';
    $model->role_delete = 'usermerchant/user_delete';      
    $model->save();    

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="All Roles";
    $model->parent_id=intval($parent_id);
    $model->link="usermerchant/role_list";
    $model->action_name="usermerchant/role_list";
    $model->sequence=2;
    $model->visible =1;    
    $model->role_create = 'usermerchant/role_create';
    $model->role_update = 'usermerchant/role_update';
    $model->role_delete = 'usermerchant/role_delete';      
    $model->save();    
}
// Buyers


//Reports
$parent_id = AttributesTools::getMenuParentID($menu_type_merchant,'Reports');
if($parent_id){      
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Sales Report";
    $model->parent_id=intval($parent_id);
    $model->link="merchantreport/sales";
    $model->action_name="merchantreport/sales";
    $model->sequence=1;
    $model->visible =1;        
    $model->save();    

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Daily Sales Report";
    $model->parent_id=intval($parent_id);
    $model->link="merchantreport/dailysalesreport";
    $model->action_name="merchantreport/dailysalesreport";
    $model->sequence=1.1;
    $model->visible =1;        
    $model->save();    

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Sales Summary";
    $model->parent_id=intval($parent_id);
    $model->link="merchantreport/summary";
    $model->action_name="merchantreport/summary";
    $model->sequence=2;
    $model->visible =1;        
    $model->save();    

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Sales Chart";
    $model->parent_id=intval($parent_id);
    $model->link="merchantreport/summary";
    $model->action_name="merchantreport/summary_chart";
    $model->sequence=2.1;
    $model->visible =0;        
    $model->save();    

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Refund Report";
    $model->parent_id=intval($parent_id);
    $model->link="merchantreport/refund";
    $model->action_name="merchantreport/refund";
    $model->sequence=3;
    $model->visible =1;        
    $model->save();    
}
// Reports


//Reports
$parent_id = AttributesTools::getMenuParentID($menu_type_merchant,'Printers');
if($parent_id){      
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="All printers";
    $model->parent_id=intval($parent_id);
    $model->link="printers/all";
    $model->action_name="printers/all";
    $model->sequence=1;
    $model->visible =1;
    $model->role_create = 'printers/create';
    $model->role_update = 'printers/update';
    $model->role_delete = 'printers/delete';              
    $model->save();    

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Printer logs";
    $model->parent_id=intval($parent_id);
    $model->link="printers/logs";
    $model->action_name="printers/logs";
    $model->sequence=2;
    $model->visible =1;
    $model->role_create = 'printers/print_view';
    $model->role_update = 'printers/print_delete';
    $model->role_delete = 'printers/clear_printlogs';         
    $model->save();    

}
//Reports


//Inventory Management
$parent_id = AttributesTools::getMenuParentID($menu_type_merchant,'Inventory Management');
if($parent_id){      
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_merchant;
    $model->menu_name="Suppliers";
    $model->parent_id=intval($parent_id);
    $model->link="supplier/list";
    $model->action_name="supplier/list";
    $model->sequence=1;
    $model->visible =1;
    $model->role_create = 'supplier/create';
    $model->role_update = 'supplier/update';
    $model->role_delete = 'supplier/delete';              
    $model->save();    
}
// Inventory Management


//Archive
$parent_id = AttributesTools::getMenuParentID($menu_type_merchant,'Archive');
if($parent_id){      
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);

    $criteria = new CDbCriteria();
    $criteria->condition = "menu_id=:menu_id";
    $criteria->params = [':menu_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);
}
// Archive


// $parent_id = AttributesTools::getMenuParentID($menu_type_merchant,'SMS');
// if($parent_id){         
//     Yii::app()->db->createCommand("
//     UPDATE {{menu}}
//     SET visible=1
//     WHERE
//     menu_id = ".q($parent_id)."
//     ")->query();

//     $criteria = new CDbCriteria();
//     $criteria->condition = "parent_id=:parent_id";
//     $criteria->params = [':parent_id'=>$parent_id];    
//     AR_menu::model()->deleteAll($criteria);        

//     $model = new AR_menu();
//     $model->menu_type=$menu_type_merchant;
//     $model->menu_name="Settings";
//     $model->parent_id=intval($parent_id);
//     $model->link="smsmerchant/sms_settings";
//     $model->action_name="smsmerchant/sms_settings";
//     $model->sequence=1;
//     $model->visible =1;        
//     $model->save();    


//     $model = new AR_menu();
//     $model->menu_type=$menu_type_merchant;
//     $model->menu_name="BroadCast";
//     $model->parent_id=intval($parent_id);
//     $model->link="smsmerchant/broadcast";
//     $model->action_name="smsmerchant/broadcast";
//     $model->sequence=2;
//     $model->visible =1;    
//     $model->role_create = 'smsmerchant/smsbroadcast_create';
//     $model->role_update = 'smsmerchant/broadcast_details';
//     $model->role_delete = 'smsmerchant/smsbroadcast_delete';              
//     $model->save();    
// }