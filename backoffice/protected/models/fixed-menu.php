<?php
$menu_type_admin = 'admin';

// Site configuration
$parent_id = AttributesTools::getMenuParentID($menu_type_admin,'Site configuration');
if($parent_id){               
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Map API Keys";
    $model->parent_id=intval($parent_id);
    $model->link="admin/map_keys";
    $model->action_name="admin/map_keys";
    $model->sequence=1;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Google Recaptcha";
    $model->parent_id=intval($parent_id);
    $model->link="admin/recaptcha";
    $model->action_name="admin/recaptcha";
    $model->sequence=2;
    $model->visible =0;
    $model->save();
    
    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Search Mode";
    $model->parent_id=intval($parent_id);
    $model->link="admin/search_settings";
    $model->action_name="admin/search_settings";
    $model->sequence=3;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Delivery Fee Management";
    $model->parent_id=intval($parent_id);
    $model->link="admin/delivery_management";
    $model->action_name="admin/delivery_management";
    $model->sequence=3.1;
    $model->visible =0;
    $model->role_delete = 'admin/delete_location_rate';
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Time Estimates Management";
    $model->parent_id=intval($parent_id);
    $model->link="admin/estimate_management";
    $model->action_name="admin/estimate_management";
    $model->sequence=3.2;
    $model->visible =0;
    $model->role_delete = 'admin/delete_estimate_time';
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Fee Management";
    $model->parent_id=intval($parent_id);
    $model->link="admin/fee_management";
    $model->action_name="admin/fee_management";
    $model->sequence=3.3;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Login & Signup";
    $model->parent_id=intval($parent_id);
    $model->link="admin/login_sigup";
    $model->action_name="admin/login_sigup";
    $model->sequence=4;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Custom Fields";
    $model->parent_id=intval($parent_id);
    $model->link="admin/custom_fields";
    $model->action_name="admin/custom_fields";
    $model->sequence=4.1;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Phone Settings";
    $model->parent_id=intval($parent_id);
    $model->link="admin/phone_settings";
    $model->action_name="admin/phone_settings";
    $model->sequence=5;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Social Settings";
    $model->parent_id=intval($parent_id);
    $model->link="admin/social_settings";
    $model->action_name="admin/social_settings";
    $model->sequence=6;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Printer Settings";
    $model->parent_id=intval($parent_id);
    $model->link="admin/printing";
    $model->action_name="admin/printing";
    $model->sequence=7;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Reviews";
    $model->parent_id=intval($parent_id);
    $model->link="admin/reviews";
    $model->action_name="admin/reviews";
    $model->sequence=8;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Timezone";
    $model->parent_id=intval($parent_id);
    $model->link="admin/timezone";
    $model->action_name="admin/timezone";
    $model->sequence=9;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Ordering";
    $model->parent_id=intval($parent_id);
    $model->link="admin/ordering";
    $model->action_name="admin/ordering";
    $model->sequence=10;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Automated Status Updates";
    $model->parent_id=intval($parent_id);
    $model->link="admin/automatedstatus";
    $model->action_name="admin/automatedstatus";
    $model->sequence=10.1;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Merchant Registration";
    $model->parent_id=intval($parent_id);
    $model->link="admin/merchant_registration";
    $model->action_name="admin/merchant_registration";
    $model->sequence=11;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Notifications";
    $model->parent_id=intval($parent_id);
    $model->link="admin/notifications";
    $model->action_name="admin/notifications";
    $model->sequence=12;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Contact Settings";
    $model->parent_id=intval($parent_id);
    $model->link="admin/contact_settings";
    $model->action_name="admin/contact_settings";
    $model->sequence=13;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Anaylytics";
    $model->parent_id=intval($parent_id);
    $model->link="admin/analytics_settings";
    $model->action_name="admin/analytics_settings";
    $model->sequence=14;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="API Access";
    $model->parent_id=intval($parent_id);
    $model->link="admin/api_access";
    $model->action_name="admin/api_access";
    $model->sequence=15;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Mobile Page";
    $model->parent_id=intval($parent_id);
    $model->link="admin/mobilepage";
    $model->action_name="admin/mobilepage";
    $model->sequence=16;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Mobile Settings";
    $model->parent_id=intval($parent_id);
    $model->link="admin/mobile_settings";
    $model->action_name="admin/mobile_settings";
    $model->sequence=17;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Push Notifications";
    $model->parent_id=intval($parent_id);
    $model->link="admin/push_notifications";
    $model->action_name="admin/push_notifications";
    $model->sequence=18;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="GDPR cookie consent";
    $model->parent_id=intval($parent_id);
    $model->link="admin/cookie_consent";
    $model->action_name="admin/cookie_consent";
    $model->sequence=19;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Cron Jobs";
    $model->parent_id=intval($parent_id);
    $model->link="admin/cronjobs";
    $model->action_name="admin/cronjobs";
    $model->sequence=19.1;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Others";
    $model->parent_id=intval($parent_id);
    $model->link="admin/site_others";
    $model->action_name="admin/site_others";
    $model->sequence=20;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Test Runactions";
    $model->parent_id=intval($parent_id);
    $model->link="admin/test_runactions";
    $model->action_name="admin/test_runactions";
    $model->sequence=21;
    $model->visible =0;
    $model->save();
}
// END Site configuration


// Merchant
$parent_id = AttributesTools::getMenuParentID($menu_type_admin,'Merchant');
if($parent_id){               
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="List";
    $model->parent_id=intval($parent_id);
    $model->link="vendor/list";
    $model->action_name="vendor/list";
    $model->sequence=1;
    $model->visible =1;
    $model->role_create = 'vendor/create';
    $model->role_update = 'vendor/edit';
    $model->role_delete = 'vendor/delete';
    $model->role_view = '';
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="New signup";
    $model->parent_id=intval($parent_id);
    $model->link="vendor/pending_list";
    $model->action_name="vendor/pending_list";
    $model->sequence=1.1;
    $model->visible =1;
    $model->role_create = 'vendor/approved';
    $model->role_update = 'vendor/denied';
    $model->role_delete = 'vendor/delete';
    $model->role_view = '';
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Sponsored";
    $model->parent_id=intval($parent_id);
    $model->link="vendor/sponsored";
    $model->action_name="vendor/sponsored";
    $model->sequence=2;
    $model->visible =1;
    $model->role_create = 'vendor/create_sponsored';
    $model->role_update = 'vendor/edit_sponsored';
    $model->role_delete = 'vendor/delete_sponsored';
    $model->role_view = '';
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Merchant Upload Bulk";
    $model->parent_id=intval($parent_id);
    $model->link="vendor/bulkupload";
    $model->action_name="vendor/bulkupload";
    $model->sequence=2.1;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Merchant Autologin";
    $model->parent_id=intval($parent_id);
    $model->link="vendor/autologin";
    $model->action_name="vendor/autologin";
    $model->sequence=3;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Merchant Login information";
    $model->parent_id=intval($parent_id);
    $model->link="vendor/login";
    $model->action_name="vendor/login";
    $model->sequence=4;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Merchant Address";
    $model->parent_id=intval($parent_id);
    $model->link="vendor/address";
    $model->action_name="vendor/address";
    $model->sequence=5;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Merchant Zone";
    $model->parent_id=intval($parent_id);
    $model->link="vendor/zone";
    $model->action_name="vendor/zone";
    $model->sequence=6;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Merchant type";
    $model->parent_id=intval($parent_id);
    $model->link="vendor/membership";
    $model->action_name="vendor/membership";
    $model->sequence=7;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Merchant Featured";
    $model->parent_id=intval($parent_id);
    $model->link="vendor/featured";
    $model->action_name="vendor/featured";
    $model->sequence=8;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Merchant Payment History";
    $model->parent_id=intval($parent_id);
    $model->link="vendor/payment_history";
    $model->action_name="vendor/payment_history";
    $model->sequence=9;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Subscriptions";
    $model->parent_id=intval($parent_id);
    $model->link="vendor/subscriptions";
    $model->action_name="vendor/subscriptions";
    $model->sequence=9.1;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Merchant Payment Settings";
    $model->parent_id=intval($parent_id);
    $model->link="vendor/payment_settings";
    $model->action_name="vendor/payment_settings";
    $model->sequence=10;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Access Settings";
    $model->parent_id=intval($parent_id);
    $model->link="vendor/access_settings";
    $model->action_name="vendor/access_settings";
    $model->sequence=10.1;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Settings";
    $model->parent_id=intval($parent_id);
    $model->link="vendor/others";
    $model->action_name="vendor/others";
    $model->sequence=11;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="API Access";
    $model->parent_id=intval($parent_id);
    $model->link="vendor/api_access";
    $model->action_name="vendor/api_access";
    $model->sequence=12;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Mobile Settings";
    $model->parent_id=intval($parent_id);
    $model->link="vendor/mobile_settings";
    $model->action_name="vendor/mobile_settings";
    $model->sequence=12.1;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Search Mode";
    $model->parent_id=intval($parent_id);
    $model->link="vendor/search_mode";
    $model->action_name="vendor/search_mode";
    $model->sequence=13;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Login & Signup";
    $model->parent_id=intval($parent_id);
    $model->link="vendor/login_sigup";
    $model->action_name="vendor/login_sigup";
    $model->sequence=14;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Phone Settings";
    $model->parent_id=intval($parent_id);
    $model->link="vendor/phone_settings";
    $model->action_name="vendor/phone_settings";
    $model->sequence=15;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Social Settings";
    $model->parent_id=intval($parent_id);
    $model->link="vendor/social_settings";
    $model->action_name="vendor/social_settings";
    $model->sequence=16;
    $model->visible =0;
    $model->save();

    
    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Google Recaptcha";
    $model->parent_id=intval($parent_id);
    $model->link="vendor/recaptcha_settings";
    $model->action_name="vendor/recaptcha_settings";
    $model->sequence=17;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Map API Keys";
    $model->parent_id=intval($parent_id);
    $model->link="vendor/map_keys";
    $model->action_name="vendor/map_keys";
    $model->sequence=18;
    $model->visible =0;
    $model->save();    
    
}
// Merchant


// Merchant
$parent_id = AttributesTools::getMenuParentID($menu_type_admin,'Membership');
if($parent_id){               
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Plan List";
    $model->parent_id=intval($parent_id);
    $model->link="plans/list";
    $model->action_name="plans/list";
    $model->sequence=1;
    $model->visible =1;
    $model->role_create = 'plans/create';
    $model->role_update = 'plans/update';
    $model->role_delete = 'plans/plan_delete';
    $model->role_view = '';
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Subscriber List";
    $model->parent_id=intval($parent_id);
    $model->link="plans/subscriber_list";
    $model->action_name="plans/subscriber_list";
    $model->sequence=1.1;
    $model->visible =1;
    // $model->role_create = 'plans/create';
    // $model->role_update = 'plans/update';
    // $model->role_delete = 'plans/plan_delete';
    // $model->role_view = '';
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Subscriptions Bank Deposit";
    $model->parent_id=intval($parent_id);
    $model->link="plans/bank_deposit";
    $model->action_name="plans/bank_deposit";
    $model->sequence=1.2;
    $model->visible =1;
    //$model->role_create = 'plans/create';
    $model->role_update = 'plans/bank_deposit_view';
    $model->role_delete = 'plans/bank_deposit_delete';
    //$model->role_view = 'plans/bank_deposit_view';
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Plan Features";
    $model->parent_id=intval($parent_id);
    $model->link="plans/features";
    $model->action_name="plans/features";
    $model->sequence=2;
    $model->visible =0;
    $model->role_create = 'plans/feature_create';
    $model->role_update = 'plans/feature_update';
    $model->role_delete = 'plans/feature_delete';
    $model->role_view = '';
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Plans Payment ID";
    $model->parent_id=intval($parent_id);
    $model->link="plans/payment_list";
    $model->action_name="plans/payment_list";
    $model->sequence=18;
    $model->visible =0;
    $model->save();

}
// PLAN


// ORDERS
$parent_id = AttributesTools::getMenuParentID($menu_type_admin,'Orders');
if($parent_id){               
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="All Orders";
    $model->parent_id=intval($parent_id);
    $model->link="order/list";
    $model->action_name="order/list";
    $model->sequence=1;
    $model->visible =1;
    $model->role_create = '';
    $model->role_update = '';
    $model->role_delete = '';
    $model->role_view = 'order/view';
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Order Settings";
    $model->parent_id=intval($parent_id);
    $model->link="order/settings";
    $model->action_name="order/settings";
    $model->sequence=2;
    $model->visible =1;
    $model->save();

    
    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="View PDF";
    $model->parent_id=intval($parent_id);
    $model->link="preprint/pdf";
    $model->action_name="preprint/pdf";
    $model->sequence=3;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Order Tabs";
    $model->parent_id=intval($parent_id);
    $model->link="order/settings_tabs";
    $model->action_name="order/settings_tabs";
    $model->sequence=3;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Order Buttons";
    $model->parent_id=intval($parent_id);
    $model->link="order/settings_buttons";
    $model->action_name="order/settings_buttons";
    $model->sequence=4;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Order Tracking";
    $model->parent_id=intval($parent_id);
    $model->link="order/settings_tracking";
    $model->action_name="order/settings_tracking";
    $model->sequence=5;
    $model->visible =0;
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Order Template";
    $model->parent_id=intval($parent_id);
    $model->link="order/settings_template";
    $model->action_name="order/settings_template";
    $model->sequence=6;
    $model->visible =0;
    $model->save();
}
// ORDERS


// Payment gateway
$parent_id = AttributesTools::getMenuParentID($menu_type_admin,'Payment gateway');
if($parent_id){               
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="All Payment";
    $model->parent_id=intval($parent_id);
    $model->link="payment_gateway/list";
    $model->action_name="payment_gateway/list";
    $model->sequence=1;
    $model->visible =1;
    $model->role_create = 'payment_gateway/create';
    $model->role_update = 'payment_gateway/update';
    $model->role_delete = 'payment_gateway/delete';
    $model->role_view = '';
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Bank Deposit";
    $model->parent_id=intval($parent_id);
    $model->link="payment_gateway/bank_deposit";
    $model->action_name="payment_gateway/bank_deposit";
    $model->sequence=2;
    $model->visible =1;
    $model->role_create = '';
    $model->role_update = '';
    $model->role_delete = 'payment_gateway/bank_deposit_delete';
    $model->role_view = 'payment_gateway/bank_deposit_view';
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Pay on delivery";
    $model->parent_id=intval($parent_id);
    $model->link="payment_gateway/paydelivery_list";
    $model->action_name="payment_gateway/paydelivery_list";
    $model->sequence=3;
    $model->visible =1;
    $model->role_create = "payment_gateway/paydelivery_create";
    $model->role_update = "payment_gateway/paydelivery_update";
    $model->role_delete = 'payment_gateway/paydelivery_delete';    
    $model->save();
}
// Payment gateway


// Account
$parent_id = AttributesTools::getMenuParentID($menu_type_admin,'Account');
if($parent_id){               
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Transactions";
    $model->parent_id=intval($parent_id);
    $model->link="account/transactions";
    $model->action_name="account/transactions";
    $model->role_create = 'api/commissionadjustment';
    $model->sequence=1;
    $model->visible =1;    
    $model->save();
}
// Account

// Earnings
$parent_id = AttributesTools::getMenuParentID($menu_type_admin,'Earnings');
if($parent_id){               
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Merchant Earnings";
    $model->parent_id=intval($parent_id);
    $model->link="earnings/merchant";
    $model->action_name="earnings/merchant";
    $model->role_create = 'api/merchantEarningAdjustment';
    $model->sequence=1;
    $model->visible =1;    
    $model->save();
}
// Account

// Withdrawals
$parent_id = AttributesTools::getMenuParentID($menu_type_admin,'Withdrawals');
if($parent_id){               
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Merchant withdrawals";
    $model->parent_id=intval($parent_id);
    $model->link="withdrawals/merchant";
    $model->action_name="withdrawals/merchant";    
    $model->sequence=1;
    $model->visible =1;    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Settings";
    $model->parent_id=intval($parent_id);
    $model->link="withdrawals/settings";
    $model->action_name="withdrawals/settings";    
    $model->sequence=2;
    $model->visible =1;    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Template";
    $model->parent_id=intval($parent_id);
    $model->link="withdrawals/settings_template";
    $model->action_name="withdrawals/settings_template";    
    $model->sequence=3;
    $model->visible =0;    
    $model->save();
}
// Account


// INVOICE
$parent_id = AttributesTools::getMenuParentID($menu_type_admin,'Invoice');
if($parent_id){               
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Invoice List";
    $model->parent_id=intval($parent_id);
    $model->link="invoice/list";
    $model->action_name="invoice/list";    
    $model->sequence=1;
    $model->visible =1;    
    $model->role_create = 'invoice/create';
    $model->role_update = 'invoice/update';
    $model->role_delete = 'invoice/delete';
    $model->role_view = 'invoice/view';
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Invoice View PDF";
    $model->parent_id=intval($parent_id);
    $model->link="invoice/pdf";
    $model->action_name="invoice/pdf";    
    $model->sequence=2;
    $model->visible =0;        
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Invoice Cancel";
    $model->parent_id=intval($parent_id);
    $model->link="invoice/cancel";
    $model->action_name="invoice/cancel";    
    $model->sequence=2.1;
    $model->visible =0;        
    $model->save();
    
    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Bank Deposit";
    $model->parent_id=intval($parent_id);
    $model->link="invoice/deposit";
    $model->action_name="invoice/deposit";    
    $model->sequence=3;
    $model->visible =1;    
    $model->role_create = '';
    $model->role_update = '';
    $model->role_delete = 'invoice/bank_deposit_delete';
    $model->role_view = 'invoice/bank_deposit_view';
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Invoice Settings";
    $model->parent_id=intval($parent_id);
    $model->link="invoice/settings";
    $model->action_name="invoice/settings";    
    $model->sequence=4;
    $model->visible =1;        
    $model->save();
}
// INVOICE


// Table reservation
$parent_id = AttributesTools::getMenuParentID($menu_type_admin,'Table reservation');
if(!$parent_id){    
    $model = new AR_menu();
    $model->menu_type="admin";
    $model->menu_name="Table reservation";    
    $model->link="";
    $model->action_name="table.reservation";
    $model->sequence=9;
    if($model->save()){
        $parent_id = $model->menu_id;
    }    
}
if($parent_id){               
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Setting";
    $model->parent_id=intval($parent_id);
    $model->link="reservation/settings";
    $model->action_name="reservation/settings";    
    $model->sequence=1;
    $model->visible =1;        
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Reservation list";
    $model->parent_id=intval($parent_id);
    $model->link="reservation/list";
    $model->action_name="reservation/list";    
    $model->sequence=2;
    $model->visible =1;    
    $model->role_create = 'reservation/create_reservation';
    $model->role_update = 'reservation/update_reservation';
    $model->role_delete = 'reservation/reservation_delete';
    $model->role_view = 'reservation/reservation_overview';
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Update Booking Status";
    $model->parent_id=intval($parent_id);
    $model->link="reservation/update_status";
    $model->action_name="reservation/update_status";    
    $model->sequence=3;
    $model->visible =0;        
    $model->save();
}
// Table reservation


// Table reservation
$parent_id = AttributesTools::getMenuParentID($menu_type_admin,'Promo');
if($parent_id){               
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Promo List";
    $model->parent_id=intval($parent_id);
    $model->link="promo/coupon";
    $model->action_name="promo/coupon";    
    $model->sequence=1;
    $model->visible =1;       
    $model->role_create = 'promo/coupon_create';
    $model->role_update = 'promo/coupon_update';
    $model->role_delete = 'promo/coupon_delete';
    $model->role_view = ''; 
    $model->save();
}



// Notifications
$parent_id = AttributesTools::getMenuParentID($menu_type_admin,'Notifications');
if($parent_id){               
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Email Provider";
    $model->parent_id=intval($parent_id);
    $model->link="notifications/provider";
    $model->action_name="notifications/provider";    
    $model->sequence=1;
    $model->visible =1;       
    $model->role_create = 'notifications/provider_create';
    $model->role_update = 'notifications/provider_update';
    $model->role_delete = 'notifications/email_provider_delete';
    $model->role_view = ''; 
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Template List";
    $model->parent_id=intval($parent_id);
    $model->link="notifications/template";
    $model->action_name="notifications/template";    
    $model->sequence=2;
    $model->visible =1;       
    $model->role_create = 'notifications/template_create';
    $model->role_update = 'notifications/template_update';
    $model->role_delete = 'notifications/template_delete';
    $model->role_view = ''; 
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Email Logs";
    $model->parent_id=intval($parent_id);
    $model->link="notifications/email_logs";
    $model->action_name="notifications/email_logs";    
    $model->sequence=2;
    $model->visible =1;       
    $model->role_create = '';
    $model->role_update = '';
    $model->role_delete = 'notifications/delete_email';
    $model->role_view = ''; 
    $model->save();

    
    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Email Clear";
    $model->parent_id=intval($parent_id);
    $model->link="notifications/clear_email";
    $model->action_name="notifications/clear_email";    
    $model->sequence=2;
    $model->visible =0;       
    $model->save();
}
// Notifications


// Marketing
$parent_id = AttributesTools::getMenuParentID($menu_type_admin,'Marketing');
if(!$parent_id){
    $model = new AR_menu();
    $model->menu_type="admin";
    $model->menu_name="Marketing";    
    $model->link="";
    $model->action_name="marketing";
    $model->sequence=12;
    if($model->save()){
        $parent_id = $model->menu_id;
    }    
}
if($parent_id){               
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Banner List";
    $model->parent_id=intval($parent_id);
    $model->link="marketing/banner_list";
    $model->action_name="marketing/banner_list";    
    $model->sequence=1;
    $model->visible =1;       
    $model->role_create = 'marketing/banner_create';
    $model->role_update = 'marketing/banner_update';
    $model->role_delete = 'marketing/banner_delete';
    $model->role_view = ''; 
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Featured Items";
    $model->parent_id=intval($parent_id);
    $model->link="marketing/featured_items";
    $model->action_name="marketing/featured_items";    
    $model->sequence=1.1;
    $model->visible =1;           
    $model->role_view = ''; 
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Suggested Items";
    $model->parent_id=intval($parent_id);
    $model->link="marketing/suggested_items";
    $model->action_name="marketing/suggested_items";    
    $model->sequence=1.2;
    $model->visible =1;           
    $model->role_view = ''; 
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Push notification";
    $model->parent_id=intval($parent_id);
    $model->link="marketing/notification";
    $model->action_name="marketing/notification";    
    $model->sequence=2;
    $model->visible =1;       
    $model->role_create = 'marketing/push_new';
    $model->role_update = '';
    $model->role_delete = 'marketing/notification_delete';
    $model->role_view = ''; 
    $model->save();
}
// Marketing


// Buyers
$parent_id = AttributesTools::getMenuParentID($menu_type_admin,'Buyers');
if($parent_id){               
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Customer list";
    $model->parent_id=intval($parent_id);
    $model->link="buyer/customers";
    $model->action_name="buyer/customers";    
    $model->sequence=1;
    $model->visible =1;       
    $model->role_create = 'buyer/customer_create';
    $model->role_update = 'buyer/customer_update';
    $model->role_delete = 'buyer/customer_delete';
    $model->role_view = ''; 
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Customer Address";
    $model->parent_id=intval($parent_id);
    $model->link="buyer/address";
    $model->action_name="buyer/address";    
    $model->sequence=1;
    $model->visible =0;       
    $model->role_create = 'buyer/address_create';
    $model->role_update = 'buyer/address_update';
    $model->role_delete = 'buyer/address_delete';
    $model->role_view = ''; 
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Order History";
    $model->parent_id=intval($parent_id);
    $model->link="buyer/order_history";
    $model->action_name="buyer/order_history";    
    $model->sequence=2;
    $model->visible =0;           
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Review List";
    $model->parent_id=intval($parent_id);
    $model->link="buyer/review_list";
    $model->action_name="buyer/review_list";    
    $model->sequence=3;
    $model->visible =1;       
    $model->role_create = '';
    $model->role_update = 'buyer/review_update';
    $model->role_delete = 'buyer/review_delete';
    $model->role_view = ''; 
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Email Subscribers";
    $model->parent_id=intval($parent_id);
    $model->link="buyer/email_subscriber";
    $model->action_name="buyer/email_subscriber";    
    $model->sequence=4;
    $model->visible =1;       
    $model->role_create = '';
    $model->role_update = '';
    $model->role_delete = 'buyer/esubscriber_delete';
    $model->role_view = ''; 
    $model->save();
}
// Buyers


// Third Party App
$parent_id = AttributesTools::getMenuParentID($menu_type_admin,'Third Party App');
if($parent_id){               
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Real time application";
    $model->parent_id=intval($parent_id);
    $model->link="thirdparty/realtime";
    $model->action_name="thirdparty/realtime";    
    $model->sequence=1;
    $model->visible =1;           
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Web push notification";
    $model->parent_id=intval($parent_id);
    $model->link="thirdparty/webpush";
    $model->action_name="thirdparty/webpush";    
    $model->sequence=2;
    $model->visible =1;           
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Firebase Configuration";
    $model->parent_id=intval($parent_id);
    $model->link="thirdparty/firebase";
    $model->action_name="thirdparty/firebase";    
    $model->sequence=3;
    $model->visible =1;           
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Whatsapp Configuration";
    $model->parent_id=intval($parent_id);
    $model->link="thirdparty/whatsapp_settings";
    $model->action_name="thirdparty/whatsapp_settings";    
    $model->sequence=3;
    $model->visible =1;           
    $model->save();

}
// Third Party App


// SMS
$parent_id = AttributesTools::getMenuParentID($menu_type_admin,'SMS');
if($parent_id){               
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="SMS Provider List";
    $model->parent_id=intval($parent_id);
    $model->link="sms/settings";
    $model->action_name="sms/settings";    
    $model->sequence=1;
    $model->visible =1;        
    $model->role_create = 'sms/provider_create';
    $model->role_update = 'sms/provider_update';
    $model->role_delete = 'sms/provider_delete';
    $model->role_view = '';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="SMS Logs";
    $model->parent_id=intval($parent_id);
    $model->link="sms/logs";
    $model->action_name="sms/logs";    
    $model->sequence=2;
    $model->visible =1;            
    $model->role_delete = 'sms/delete';
    $model->role_view = '';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="SMS Clear";
    $model->parent_id=intval($parent_id);
    $model->link="sms/clear_smslogs";
    $model->action_name="sms/clear_smslogs";    
    $model->sequence=3;
    $model->visible =0;                
    $model->save();
    
}
// SMS


// Delivery Management
$parent_id = AttributesTools::getMenuParentID($menu_type_admin,'Delivery Management');
if($parent_id){ 
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        
} else {        
    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Delivery Management";
    $model->parent_id=intval($parent_id);
    $model->link="";
    $model->action_name="delivery.driver";
    $model->sequence=16;
    $model->visible =1;        
    if($model->save()){
        $parent_id = $model->menu_id;
    }    
}
if($parent_id){
    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Settings";
    $model->parent_id=intval($parent_id);
    $model->link="driver/settings";
    $model->action_name="driver/settings";    
    $model->sequence=1;
    $model->visible =1;     
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="API Access";
    $model->parent_id=intval($parent_id);
    $model->link="driver/api_access";
    $model->action_name="driver/api_access";    
    $model->sequence=1.1;
    $model->visible =0;     
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Delete API Keys";
    $model->parent_id=intval($parent_id);
    $model->link="driver/delete_apikeys";
    $model->action_name="driver/delete_apikeys";    
    $model->sequence=1.2;
    $model->visible =0;     
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Push notifications";
    $model->parent_id=intval($parent_id);
    $model->link="driver/push_notifications";
    $model->action_name="driver/push_notifications";    
    $model->sequence=2;
    $model->visible =0;     
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Firebase Settings";
    $model->parent_id=intval($parent_id);
    $model->link="driver/firebase_settings";
    $model->action_name="driver/firebase_settings";    
    $model->sequence=3;
    $model->visible =0;     
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Signup Settings";
    $model->parent_id=intval($parent_id);
    $model->link="driver/signup_settings";
    $model->action_name="driver/signup_settings";    
    $model->sequence=4;
    $model->visible =0;     
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Cashout settings";
    $model->parent_id=intval($parent_id);
    $model->link="driver/withdrawal_settings";
    $model->action_name="driver/withdrawal_settings";    
    $model->sequence=5;
    $model->visible =0;     
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Order Status";
    $model->parent_id=intval($parent_id);
    $model->link="driver/order_status";
    $model->action_name="driver/order_status";    
    $model->sequence=6;
    $model->visible =0;     
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Order Tabs";
    $model->parent_id=intval($parent_id);
    $model->link="driver/settings_tabs";
    $model->action_name="driver/settings_tabs";    
    $model->sequence=7;
    $model->visible =0;     
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Pages";
    $model->parent_id=intval($parent_id);
    $model->link="driver/settings_page";
    $model->action_name="driver/settings_page";    
    $model->sequence=8;
    $model->visible =0;     
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Cron jobs";
    $model->parent_id=intval($parent_id);
    $model->link="driver/cronjobs";
    $model->action_name="driver/cronjobs";    
    $model->sequence=9;
    $model->visible =0;     
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Cashout list";
    $model->parent_id=intval($parent_id);
    $model->link="driver/cashout_list";
    $model->action_name="driver/cashout_list";    
    $model->sequence=10;
    $model->visible =1;     
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Collect cash";
    $model->parent_id=intval($parent_id);
    $model->link="driver/collect_cash";
    $model->action_name="driver/collect_cash";    
    $model->sequence=11;
    $model->visible =1;        
    $model->role_create = 'driver/collect_cash_add';
    $model->role_update = 'driver/collect_transactions';
    $model->role_delete = 'driver/collect_cash_void';
    $model->role_view = '';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Driver list";
    $model->parent_id=intval($parent_id);
    $model->link="driver/list";
    $model->action_name="driver/list";    
    $model->sequence=12;
    $model->visible =1;        
    $model->role_create = 'driver/add';
    $model->role_update = 'driver/update';
    $model->role_delete = 'driver/delete';
    $model->role_view = 'driver/overview';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="License";
    $model->parent_id=intval($parent_id);
    $model->link="driver/license";
    $model->action_name="driver/license";    
    $model->sequence=12.1;
    $model->visible =0;                
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Vehicle";
    $model->parent_id=intval($parent_id);
    $model->link="driver/vehicle";
    $model->action_name="driver/vehicle";    
    $model->sequence=12.2;
    $model->visible =0;                
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Bank Information";
    $model->parent_id=intval($parent_id);
    $model->link="driver/bank_info";
    $model->action_name="driver/bank_info";    
    $model->sequence=12.3;
    $model->visible =0;                
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Wallet";
    $model->parent_id=intval($parent_id);
    $model->link="driver/wallet";
    $model->action_name="driver/wallet";    
    $model->sequence=12.4;
    $model->visible =0;                
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Cashout Transactions";
    $model->parent_id=intval($parent_id);
    $model->link="driver/cashout_transactions";
    $model->action_name="driver/cashout_transactions";    
    $model->sequence=12.4;
    $model->visible =0;                
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Delivery transactions";
    $model->parent_id=intval($parent_id);
    $model->link="driver/delivery_transactions";
    $model->action_name="driver/delivery_transactions";    
    $model->sequence=12.5;
    $model->visible =0;                
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Order tips";
    $model->parent_id=intval($parent_id);
    $model->link="driver/order_tips";
    $model->action_name="driver/order_tips";    
    $model->sequence=12.6;
    $model->visible =0;                
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Time logs";
    $model->parent_id=intval($parent_id);
    $model->link="driver/time_logs";
    $model->action_name="driver/time_logs";    
    $model->sequence=12.7;
    $model->visible =0;                
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Reviews";
    $model->parent_id=intval($parent_id);
    $model->link="driver/review_ratings";
    $model->action_name="driver/review_ratings";    
    $model->sequence=12.8;
    $model->visible =0;                
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Car registration";
    $model->parent_id=intval($parent_id);
    $model->link="driver/carlist";
    $model->action_name="driver/carlist";    
    $model->sequence=13;
    $model->visible =1;        
    $model->role_create = 'driver/addcar';
    $model->role_update = 'driver/update_car';
    $model->role_delete = 'driver/car_delete';
    $model->role_view = '';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Groups";
    $model->parent_id=intval($parent_id);
    $model->link="driver/group";
    $model->action_name="driver/group";    
    $model->sequence=14;
    $model->visible =1;        
    $model->role_create = 'driver/addgroup';
    $model->role_update = 'driver/group_update';
    $model->role_delete = '/group_delete';
    $model->role_view = '';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Employee Schedule";
    $model->parent_id=intval($parent_id);
    $model->link="driver/schedule";
    $model->action_name="driver/schedule";    
    $model->sequence=15;
    $model->visible =1;        
    $model->role_create = 'schedule/add';
    $model->role_update = 'schedule/update';
    $model->role_delete = 'schedule/delete';
    $model->role_view = 'driver/schedule_bulk';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Shifts Schedule";
    $model->parent_id=intval($parent_id);
    $model->link="driver/shift_list";
    $model->action_name="driver/shift_list";    
    $model->sequence=16;
    $model->visible =1;        
    $model->role_create = 'driver/addshift';
    $model->role_update = 'driver/shift_update';
    $model->role_delete = 'driver/shift_delete';
    $model->role_view = 'driver/shift_bulkupload';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Reviews";
    $model->parent_id=intval($parent_id);
    $model->link="driver/review_list";
    $model->action_name="driver/review_list";    
    $model->sequence=17;
    $model->visible =1;            
    $model->role_update = 'driver/review_update';
    $model->role_delete = 'driver/review_delete';    
    $model->save();        

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Map View";
    $model->parent_id=intval($parent_id);
    $model->link="driver/mapview";
    $model->action_name="driver/mapview";    
    $model->sequence=18;
    $model->visible =1;            
    $model->save();
}
// Delivery Management


// Mobile Merchant
$parent_id = AttributesTools::getMenuParentID($menu_type_admin,'Mobile Merchant');
if(!$parent_id){    
    $model = new AR_menu();
    $model->menu_type="admin";
    $model->menu_name="Mobile Merchant";    
    $model->link="";
    $model->action_name="mobile.merchant";
    $model->sequence=16;
    if($model->save()){
        $parent_id = $model->menu_id;
    }    
}
if($parent_id){               
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Settings";
    $model->parent_id=intval($parent_id);
    $model->link="mobilemerchant/api_access";
    $model->action_name="mobilemerchant/api_access";    
    $model->sequence=1;
    $model->visible =1;     
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Delete API Keys";
    $model->parent_id=intval($parent_id);
    $model->link="mobilemerchant/delete_apikeys";
    $model->action_name="mobilemerchant/delete_apikeys";    
    $model->sequence=2;
    $model->visible =0;     
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Settings";
    $model->parent_id=intval($parent_id);
    $model->link="mobilemerchant/settings";
    $model->action_name="mobilemerchant/settings";    
    $model->sequence=3;
    $model->visible =0;     
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Push Notifications";
    $model->parent_id=intval($parent_id);
    $model->link="mobilemerchant/push_notifications";
    $model->action_name="mobilemerchant/push_notifications";    
    $model->sequence=4;
    $model->visible =0;     
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Pages";
    $model->parent_id=intval($parent_id);
    $model->link="mobilemerchant/settings_page";
    $model->action_name="mobilemerchant/settings_page";    
    $model->sequence=5;
    $model->visible =0;     
    $model->save();
}
// Mobile Merchant


// Tableside Ordering
$parent_id = AttributesTools::getMenuParentID($menu_type_admin,'Tableside Ordering');
if(!$parent_id){    
    $model = new AR_menu();
    $model->menu_type="admin";
    $model->menu_name="Tableside Ordering";    
    $model->link="";
    $model->action_name="tableside.ordering";
    $model->sequence=16;
    if($model->save()){
        $parent_id = $model->menu_id;
    }    
}
if($parent_id){               
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Settings";
    $model->parent_id=intval($parent_id);
    $model->link="tableside/api_access";
    $model->action_name="tableside/api_access";    
    $model->sequence=1;
    $model->visible =1;     
    $model->save();
}

// Kitchen App
$parent_id = AttributesTools::getMenuParentID($menu_type_admin,'Kitchen App');
if(!$parent_id){    
    $model = new AR_menu();
    $model->menu_type="admin";
    $model->menu_name="Kitchen App";    
    $model->link="";
    $model->action_name="Kitchen.app";
    $model->sequence=16.1;
    if($model->save()){
        $parent_id = $model->menu_id;
    }    
}
if($parent_id){               
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Settings";
    $model->parent_id=intval($parent_id);
    $model->link="kitchen/settings";
    $model->action_name="kitchen/settings";    
    $model->sequence=1;
    $model->visible =1;     
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="API Access";
    $model->parent_id=intval($parent_id);
    $model->link="kitchen/api_access";
    $model->action_name="kitchen/api_access";    
    $model->sequence=2;
    $model->visible =0;     
    $model->save();
}

// POINTS
$parent_id = AttributesTools::getMenuParentID($menu_type_admin,'Loyalty Points');
if(!$parent_id){    
    $model = new AR_menu();
    $model->menu_type="admin";
    $model->menu_name="Loyalty Points";    
    $model->link="";
    $model->action_name="admin.loyalty";
    $model->sequence=16;
    if($model->save()){
        $parent_id = $model->menu_id;
    }    
}
if($parent_id){
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Settings";
    $model->parent_id=intval($parent_id);
    $model->link="points/settings";
    $model->action_name="points/settings";    
    $model->sequence=1;
    $model->visible =1;     
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Redeem thresholds";
    $model->parent_id=intval($parent_id);
    $model->link="points/thresholds";
    $model->action_name="points/thresholds";    
    $model->sequence=2;
    $model->visible =1;     
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Monthly thresholds";
    $model->parent_id=intval($parent_id);
    $model->link="points/monthly_thresholds";
    $model->action_name="points/monthly_thresholds";    
    $model->sequence=2.1;
    $model->visible =1;     
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="User Reward Points";
    $model->parent_id=intval($parent_id);
    $model->link="points/rewards";
    $model->action_name="points/rewards";    
    $model->sequence=3;
    $model->visible =1;     
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Transactions";
    $model->parent_id=intval($parent_id);
    $model->link="points/alltransactions";
    $model->action_name="points/alltransactions";    
    $model->sequence=4;
    $model->visible =1;     
    $model->save();

}
// POINTS

// EWALLET
$parent_id = AttributesTools::getMenuParentID($menu_type_admin,'Digital Wallet');
if(!$parent_id){    
    $model = new AR_menu();
    $model->menu_type="admin";
    $model->menu_name="Digital Wallet";    
    $model->link="";
    $model->action_name="admin.wallet";
    $model->sequence=16.1;
    if($model->save()){
        $parent_id = $model->menu_id;
    }    
}
if($parent_id){
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Settings";
    $model->parent_id=intval($parent_id);
    $model->link="digitalwallet/settings";
    $model->action_name="digitalwallet/settings";    
    $model->sequence=1;
    $model->visible =1;     
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Bonus Funds";
    $model->parent_id=intval($parent_id);
    $model->link="digitalwallet/bonus_funds";
    $model->action_name="digitalwallet/bonus_funds";    
    $model->sequence=2;
    $model->visible =1;     
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Transactions";
    $model->parent_id=intval($parent_id);
    $model->link="digitalwallet/transactions";
    $model->action_name="digitalwallet/transactions";    
    $model->sequence=3;
    $model->visible =1;     
    $model->save();
    
}
// EWALLET

// Multi currency
$parent_id = AttributesTools::getMenuParentID($menu_type_admin,'Multi Currency');
if(!$parent_id){    
    $model = new AR_menu();
    $model->menu_type="admin";
    $model->menu_name="Multi Currency";    
    $model->link="";
    $model->action_name="multi.currency";
    $model->sequence=16.2;
    if($model->save()){
        $parent_id = $model->menu_id;
    }    
}
if($parent_id){               
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Settings";
    $model->parent_id=intval($parent_id);
    $model->link="multicurrency/settings";
    $model->action_name="multicurrency/settings";    
    $model->sequence=1;
    $model->visible =1;     
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Exchange Rates";
    $model->parent_id=intval($parent_id);
    $model->link="multicurrency/exchangerate";
    $model->action_name="multicurrency/exchangerate";    
    $model->sequence=1;
    $model->visible =1;     
    $model->save();
}
// Multi currency


// CHAT
$parent_id = AttributesTools::getMenuParentID($menu_type_admin,'Communication');
if(!$parent_id){    
    $model = new AR_menu();
    $model->menu_type="admin";
    $model->menu_name="Communication";    
    $model->link="";
    $model->action_name="communication";
    $model->sequence=16.3;
    if($model->save()){
        $parent_id = $model->menu_id;
    }    
}
if($parent_id){               
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Chats";
    $model->parent_id=intval($parent_id);
    $model->link="communication/chats";
    $model->action_name="communication/chats";    
    $model->sequence=1;
    $model->visible =1;     
    $model->role_create = 'communication/framechat';
    $model->save();    

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Settings";
    $model->parent_id=intval($parent_id);
    $model->link="communication/settings";
    $model->action_name="communication/settings";    
    $model->sequence=2;
    $model->visible =1;     
    $model->save();    
}

// END CHAT

// Reports
$parent_id = AttributesTools::getMenuParentID($menu_type_admin,'Reports');
if(!$parent_id){
    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Reports";
    $model->parent_id=intval($parent_id);
    $model->link="";
    $model->action_name="reports";    
    $model->sequence=17;
    $model->visible =1;     
    if($model->save()){
        $parent_id = $model->menu_id;
    }    
}
if($parent_id){               
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Merchant Registration";
    $model->parent_id=intval($parent_id);
    $model->link="reports/merchant_registration";
    $model->action_name="reports/merchant_registration";    
    $model->sequence=1;
    $model->visible =1;     
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Membership Payment";
    $model->parent_id=intval($parent_id);
    $model->link="reports/merchant_payment";
    $model->action_name="reports/merchant_payment";    
    $model->sequence=2;
    $model->visible =1;     
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Merchant Sales";
    $model->parent_id=intval($parent_id);
    $model->link="reports/merchant_sales";
    $model->action_name="reports/merchant_sales";    
    $model->sequence=3;
    $model->visible =1;     
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Order earnings";
    $model->parent_id=intval($parent_id);
    $model->link="reports/order_earnings";
    $model->action_name="reports/order_earnings";    
    $model->sequence=4;
    $model->visible =1;     
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Refund Report";
    $model->parent_id=intval($parent_id);
    $model->link="reports/refund";
    $model->action_name="reports/refund";    
    $model->sequence=5;
    $model->visible =1;     
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Driver Earnings";
    $model->parent_id=intval($parent_id);
    $model->link="reports/driver_earnings";
    $model->action_name="reports/driver_earnings";    
    $model->sequence=6;
    $model->visible =1;     
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Driver wallet";
    $model->parent_id=intval($parent_id);
    $model->link="reports/driver_wallet";
    $model->action_name="reports/driver_wallet";    
    $model->sequence=7;
    $model->visible =1;     
    $model->save();
}
// Reports


// Users
$parent_id = AttributesTools::getMenuParentID($menu_type_admin,'Users');
if($parent_id){               
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="All User";
    $model->parent_id=intval($parent_id);
    $model->link="user/list";
    $model->action_name="user/list";    
    $model->sequence=1;
    $model->visible =1;     
    $model->role_create = 'user/create';
    $model->role_update = 'user/update';
    $model->role_delete = 'user/delete';
    $model->role_view = '';
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Change Password";
    $model->parent_id=intval($parent_id);
    $model->link="user/change_password";
    $model->action_name="user/change_password";    
    $model->sequence=2;
    $model->visible =0;         
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="All Roles";
    $model->parent_id=intval($parent_id);
    $model->link="user/roles_list";
    $model->action_name="user/roles_list";    
    $model->sequence=3;
    $model->visible =1;     
    $model->role_create = 'user/role_create';
    $model->role_update = 'user/role_update';
    $model->role_delete = 'user/role_delete';
    $model->role_view = '';
    $model->save();
}
// Users


// Printers
$parent_id = AttributesTools::getMenuParentID($menu_type_admin,'Printers');
if(!$parent_id){      
    $model = new AR_menu();
    $model->menu_type="admin";
    $model->menu_name="Printers";    
    $model->link="";
    $model->action_name="printers";
    $model->sequence=18;
    if($model->save()){
        $parent_id = $model->menu_id;
    }    
}
if($parent_id){               
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Printer List";
    $model->parent_id=intval($parent_id);
    $model->link="printer/all";
    $model->action_name="printer/all";    
    $model->sequence=1;
    $model->visible =1;     
    $model->role_create = 'printer/create';
    $model->role_update = 'printer/update';
    $model->role_delete = 'printer/delete';
    $model->role_view = '';
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Print Logs";
    $model->parent_id=intval($parent_id);
    $model->link="printer/logs";
    $model->action_name="printer/logs";    
    $model->sequence=2;
    $model->visible =1;     
    $model->role_create = '';
    $model->role_update = '';
    $model->role_delete = 'printer/print_delete';
    $model->role_view = 'printer/print_view';
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Clear Print Logs";
    $model->parent_id=intval($parent_id);
    $model->link="printer/clear_printlogs";
    $model->action_name="printer/clear_printlogs";    
    $model->sequence=3;
    $model->visible =0;     
    $model->save();
}
// Printers


// Website
$parent_id = AttributesTools::getMenuParentID($menu_type_admin,'Website');
if($parent_id){               
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Theme";
    $model->parent_id=intval($parent_id);
    $model->link="theme/changer";
    $model->action_name="theme/changer";    
    $model->sequence=1;
    $model->visible =1;         
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="SEO Setup";
    $model->parent_id=intval($parent_id);
    $model->link="website/seosetup";
    $model->action_name="website/seosetup";    
    $model->sequence=1.1;
    $model->visible =1;         
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="XML Sitemap";
    $model->parent_id=intval($parent_id);
    $model->link="website/sitemap";
    $model->action_name="website/sitemap";    
    $model->sequence=1.2;
    $model->visible =1;         
    $model->save();


    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Theme Settings";
    $model->parent_id=intval($parent_id);
    $model->link="theme/settings";
    $model->action_name="theme/settings";    
    $model->sequence=2;
    $model->visible =0;         
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Menu";
    $model->parent_id=intval($parent_id);
    $model->link="theme/menu";
    $model->action_name="theme/menu";
    $model->sequence=3;
    $model->visible =0;         
    $model->save();
    
    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Theme Layout";
    $model->parent_id=intval($parent_id);
    $model->link="theme/layout";
    $model->action_name="theme/layout";
    $model->sequence=4;
    $model->visible =0;         
    $model->save();
}
// Website


// Website
$parent_id = AttributesTools::getMenuParentID($menu_type_admin,'Media Library');
if(!$parent_id){  
    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Media Library";
    $model->parent_id=0;
    $model->link="media/library";
    $model->action_name="media/library";    
    $model->sequence=20;
    $model->visible =1;         
    if($model->save()){
        $parent_id = $model->menu_id;
    }    
} else {    
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        
    
    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Upload File";
    $model->parent_id=intval($parent_id);
    $model->link="uploadfiles/file";
    $model->action_name="uploadfiles/file";
    $model->sequence=1;
    $model->visible =1;                 
    $model->role_update = 'uploadfiles/getMediaSeleted';    
    $model->role_delete = 'uploadfiles/deleteFiles';    
    $model->role_view = 'uploadfiles/getMedia';     
    $model->save();    
}
// Website

// Addon manager
$parent_id = AttributesTools::getMenuParentID($menu_type_admin,'Addon manager');
if(!$parent_id){                   
    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Addon manager";
    $model->parent_id=0;
    $model->link="addon/manager";
    $model->action_name="addon/manager";    
    $model->sequence=21;
    $model->visible =1;         
    if($model->save()){
        $parent_id = $model->menu_id;
    }    
}
if($parent_id){ 
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Addon Install";
    $model->parent_id=intval($parent_id);
    $model->link="addon/install";
    $model->action_name="addon/install";    
    $model->sequence=1;
    $model->visible =0;         
    $model->save();
}
// Addon manager

// Utilities
$parent_id = AttributesTools::getMenuParentID($menu_type_admin,'Utilities');
if($parent_id){               
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Fixed database";
    $model->parent_id=intval($parent_id);
    $model->link="utilities/fixed_database";
    $model->action_name="utilities/fixed_database";
    $model->sequence=1;
    $model->visible =1;         
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Clean database";
    $model->parent_id=intval($parent_id);
    $model->link="utilities/clean_database";
    $model->action_name="utilities/clean_database";
    $model->sequence=2;
    $model->visible =1;         
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Cron Jobs";
    $model->parent_id=intval($parent_id);
    $model->link="utilities/cronjobs";
    $model->action_name="utilities/cronjobs";
    $model->sequence=3;
    $model->visible =1;         
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Migration Tools";
    $model->parent_id=intval($parent_id);
    $model->link="utilities/migration_tools";
    $model->action_name="utilities/migration_tools";
    $model->sequence=4;
    $model->visible =1;         
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Clear Cache";
    $model->parent_id=intval($parent_id);
    $model->link="utilities/clear_cache";
    $model->action_name="utilities/clear_cache";
    $model->sequence=5;
    $model->visible =1;         
    $model->save();
}
// Utilities


// Attributes
$parent_id = AttributesTools::getMenuParentID($menu_type_admin,'Attributes');
if($parent_id){               
    $criteria = new CDbCriteria();
    $criteria->condition = "parent_id=:parent_id";
    $criteria->params = [':parent_id'=>$parent_id];    
    AR_menu::model()->deleteAll($criteria);        

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Cuisine";
    $model->parent_id=intval($parent_id);
    $model->link="attributes/cuisine_list";
    $model->action_name="attributes/cuisine_list";
    $model->sequence=1;
    $model->visible =1;             
    $model->role_create = 'attributes/cuisine_create';
    $model->role_update = 'attributes/cuisine_update';
    $model->role_delete = 'attributes/cuisine_delete';    
    $model->role_view = 'attributes/cuisine_sort'; 
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Dishes";
    $model->parent_id=intval($parent_id);
    $model->link="attributes/dish_list";
    $model->action_name="attributes/dish_list";
    $model->sequence=2;
    $model->visible =1;             
    $model->role_create = 'attributes/dish_create';
    $model->role_update = 'attributes/dish_update';
    $model->role_delete = 'attributes/dishes_delete';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Allergens";
    $model->parent_id=intval($parent_id);
    $model->link="attributes/allergens_list";
    $model->action_name="attributes/allergens_list";
    $model->sequence=2.1;
    $model->visible =1;             
    $model->role_create = 'attributes/allergens_create';
    $model->role_update = 'attributes/allergens_update';
    $model->role_delete = 'attributes/allergens_delete';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Tags";
    $model->parent_id=intval($parent_id);
    $model->link="attributes/tag_list";
    $model->action_name="attributes/tag_list";
    $model->sequence=3;
    $model->visible =1;             
    $model->role_create = 'attributes/tags_create';
    $model->role_update = 'attributes/tags_update';
    $model->role_delete = 'attributes/tags_delete';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Order Status";
    $model->parent_id=intval($parent_id);
    $model->link="attributes/order_status";
    $model->action_name="attributes/order_status";
    $model->sequence=3.1;
    $model->visible =1;             
    $model->role_create = 'attributes/status_create';
    $model->role_update = 'attributes/status_update';
    $model->role_delete = 'attributes/status_delete';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Order Status Actions";
    $model->parent_id=intval($parent_id);
    $model->link="attributes/status_actions";
    $model->action_name="attributes/status_actions";
    $model->sequence=3.2;
    $model->visible =0;                 
    $model->role_create = 'attributes/create_action';
    $model->role_update = 'attributes/update_action';
    $model->role_delete = 'attributes/status_action_delete';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Currency";
    $model->parent_id=intval($parent_id);
    $model->link="attributes/currency";
    $model->action_name="attributes/currency";
    $model->sequence=4;
    $model->visible =1;             
    $model->role_create = 'attributes/currency_create';
    $model->role_update = 'attributes/currency_update';
    $model->role_delete = 'attributes/currency_delete';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Manage Location";
    $model->parent_id=intval($parent_id);
    $model->link="location/country_list";
    $model->action_name="location/country_list";
    $model->sequence=5;
    $model->visible =1;             
    $model->role_create = 'location/country_create';
    $model->role_update = 'location/country_update';
    $model->role_delete = 'location/delete_country';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="State List";
    $model->parent_id=intval($parent_id);
    $model->link="location/state_list";
    $model->action_name="location/state_list";
    $model->sequence=6;
    $model->visible =0;             
    $model->role_create = 'location/state_create';
    $model->role_update = 'location/state_update';
    $model->role_delete = 'location/state_delete';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="City List";
    $model->parent_id=intval($parent_id);
    $model->link="location/city_list";
    $model->action_name="location/city_list";
    $model->sequence=7;
    $model->visible =0;             
    $model->role_create = 'location/city_create';
    $model->role_update = 'location/city_update';
    $model->role_delete = 'location/city_delete';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Area List";
    $model->parent_id=intval($parent_id);
    $model->link="location/area_list";
    $model->action_name="location/area_list";
    $model->sequence=8;
    $model->visible =0;             
    $model->role_create = 'location/area_create';
    $model->role_update = 'location/area_update';
    $model->role_delete = 'location/area_delete';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Zones";
    $model->parent_id=intval($parent_id);
    $model->link="attributes/zone_list";
    $model->action_name="attributes/zone_list";
    $model->sequence=9;
    $model->visible =1;             
    $model->role_create = 'attributes/zone_create';
    $model->role_update = 'attributes/zone_update';
    $model->role_delete = 'attributes/zone_delete';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Featured Locations";
    $model->parent_id=intval($parent_id);
    $model->link="attributes/featured_loc";
    $model->action_name="attributes/featured_loc";
    $model->sequence=10;
    $model->visible =1;             
    $model->role_create = 'attributes/featured_loc_create';
    $model->role_update = 'attributes/featured_loc_update';
    $model->role_delete = 'attributes/featured_loc_delete';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Pages";
    $model->parent_id=intval($parent_id);
    $model->link="attributes/pages_list";
    $model->action_name="attributes/pages_list";
    $model->sequence=10;
    $model->visible =1;             
    $model->role_create = 'attributes/pages_create';
    $model->role_update = 'attributes/page_update';
    $model->role_delete = 'attributes/pages_delete';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Languages";
    $model->parent_id=intval($parent_id);
    $model->link="attributes/language_list";
    $model->action_name="attributes/language_list";
    $model->sequence=11;
    $model->visible =1;             
    $model->role_create = 'attributes/language_create';
    $model->role_update = 'attributes/language_update';
    $model->role_delete = 'attributes/language_delete';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Language Settings";
    $model->parent_id=intval($parent_id);
    $model->link="attributes/language_settings";
    $model->action_name="attributes/language_settings";
    $model->sequence=12;
    $model->visible =0;                 
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Language Import";
    $model->parent_id=intval($parent_id);
    $model->link="attributes/language_import";
    $model->action_name="attributes/language_import";
    $model->sequence=13;
    $model->visible =0;                 
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Language Translations";
    $model->parent_id=intval($parent_id);
    $model->link="attributes/language_translation";
    $model->action_name="attributes/language_translation";
    $model->sequence=14;
    $model->visible =0;                 
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Language Export";
    $model->parent_id=intval($parent_id);
    $model->link="attributes/language_export";
    $model->action_name="attributes/language_export";
    $model->sequence=15;
    $model->visible =0;                 
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Status Management";
    $model->parent_id=intval($parent_id);
    $model->link="attributes/status_mgt";
    $model->action_name="attributes/status_mgt";
    $model->sequence=16;
    $model->visible =1;             
    $model->role_create = 'attributes/status_mgt_create';
    $model->role_update = 'attributes/status_mgt_update';
    $model->role_delete = 'attributes/status_mgt_delete';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Order Type";
    $model->parent_id=intval($parent_id);
    $model->link="attributes/services_list";
    $model->action_name="attributes/services_list";
    $model->sequence=17;
    $model->visible =1;             
    $model->role_create = 'attributes/services_create';
    $model->role_update = 'attributes/services_update';
    $model->role_delete = 'attributes/services_delete';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Merchant Type";
    $model->parent_id=intval($parent_id);
    $model->link="attributes/merchant_type_list";
    $model->action_name="attributes/merchant_type_list";
    $model->sequence=18;
    $model->visible =1;             
    $model->role_create = 'attributes/merchant_type_create';
    $model->role_update = 'attributes/merchant_type_update';
    $model->role_delete = 'attributes/merchant_type_delete';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Rejection List";
    $model->parent_id=intval($parent_id);
    $model->link="attributes/rejection_list";
    $model->action_name="attributes/rejection_list";
    $model->sequence=19;
    $model->visible =1;             
    $model->role_create = 'attributes/rejection_create';
    $model->role_update = 'attributes/rejection_update';
    $model->role_delete = 'attributes/rejection_reason_delete';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Pause Order list";
    $model->parent_id=intval($parent_id);
    $model->link="attributes/pause_reason_list";
    $model->action_name="attributes/pause_reason_list";
    $model->sequence=19;
    $model->visible =1;             
    $model->role_create = 'attributes/pause_create';
    $model->role_update = 'attributes/pause_reason_update';
    $model->role_delete = 'attributes/pause_reason_delete';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Status Actions";
    $model->parent_id=intval($parent_id);
    $model->link="attributes/actions_list";
    $model->action_name="attributes/actions_list";
    $model->sequence=20;
    $model->visible =1;             
    $model->role_create = 'attributes/action_create';
    $model->role_update = 'attributes/action_update';
    $model->role_delete = 'attributes/action_delete';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Tip List";
    $model->parent_id=intval($parent_id);
    $model->link="attributes/tip_list";
    $model->action_name="attributes/tip_list";
    $model->sequence=21;
    $model->visible =1;             
    $model->role_create = 'attributes/tips_create';
    $model->role_update = 'attributes/tips_update';
    $model->role_delete = 'attributes/tips_delete';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Booking Cancellation";
    $model->parent_id=intval($parent_id);
    $model->link="attributes/booking_cancel_list";
    $model->action_name="attributes/booking_cancel_list";
    $model->sequence=22;
    $model->visible =1;             
    $model->role_create = 'attributes/booking_cancellation_create';
    $model->role_update = 'attributes/booking_cancellation_update';
    $model->role_delete = 'attributes/booking_cancellation_delete';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Cookie Preferences";
    $model->parent_id=intval($parent_id);
    $model->link="attributes/cookie_preferences";
    $model->action_name="attributes/cookie_preferences";
    $model->sequence=23;
    $model->visible =1;             
    $model->role_create = 'attributes/cookie_preferences_create';
    $model->role_update = 'attributes/cookie_preferences_update';
    $model->role_delete = 'attributes/cookie_preferences_delete';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Vehicle maker";
    $model->parent_id=intval($parent_id);
    $model->link="attributes/vehicle";
    $model->action_name="attributes/vehicle";
    $model->sequence=24;
    $model->visible =1;             
    $model->role_create = 'attributes/vehicle_maker_create';
    $model->role_update = 'attributes/vehicle_maker_update';
    $model->role_delete = 'attributes/vehicle_maker_delete';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Delivery Order Help";
    $model->parent_id=intval($parent_id);
    $model->link="attributes/delivery_order_help";
    $model->action_name="attributes/delivery_order_help";
    $model->sequence=25;
    $model->visible =1;             
    $model->role_create = 'attributes/delivery_order_help_create';
    $model->role_update = 'attributes/delivery_order_help_update';
    $model->role_delete = 'attributes/delivery_order_help_delete';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Delivery Decline Reason";
    $model->parent_id=intval($parent_id);
    $model->link="attributes/delivery_decline_reason";
    $model->action_name="attributes/delivery_decline_reason";
    $model->sequence=26;
    $model->visible =1;             
    $model->role_create = 'attributes/delivery_decline_reason_create';
    $model->role_update = 'attributes/delivery_decline_reason_update';
    $model->role_delete = 'attributes/delivery_decline_reason_delete';    
    $model->save();

    $model = new AR_menu();
    $model->menu_type=$menu_type_admin;
    $model->menu_name="Call Staff Menu";
    $model->parent_id=intval($parent_id);
    $model->link="attributes/call_staff_menu";
    $model->action_name="attributes/call_staff_menu";
    $model->sequence=27;
    $model->visible =1;             
    $model->role_create = 'attributes/call_staff_menu_create';
    $model->role_update = 'attributes/call_staff_menu_update';
    $model->role_delete = 'attributes/call_staff_menu_delete';    
    $model->save();
}
// Attributes