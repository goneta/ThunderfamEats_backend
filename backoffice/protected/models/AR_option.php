<?php
class AR_option extends CActiveRecord
{	
	   		
	public $image,$image2,$website_title,$website_logo,$mobilelogo;
		
	public $map_provider,$google_geo_api_key,$google_maps_api_key,$mapbox_access_token;	
	
	public $captcha_site_key,$captcha_secret,$captcha_lang,$captcha_customer_signup,
	$captcha_merchant_signup,$captcha_customer_login,$captcha_merchant_login,$captcha_admin_login,
	$captcha_order,$captcha_driver_signup,$captcha_contact_form;
	
	public $admin_printing_receipt_width,$admin_printing_receipt_size,$website_enabled_rcpt,$website_receipt_logo;
	
	public $signup_verification,$signup_verification_type,$blocked_email_add,
	$blocked_mobile,$website_terms_customer,$website_terms_customer_url;
	
	public $website_review_type,$review_baseon_status,$earn_points_review_status,$publish_review_status,
	$website_reviews_actual_purchase,$merchant_can_edit_reviews;
	
	public $website_merchant_mutiple_login,$website_admin_mutiple_login;
	
	public $website_timezone_new,$website_date_format_new,$website_time_format_new,$website_time_picker_interval;
	
	public $disabled_website_ordering,$website_hide_foodprice,$website_disbaled_auto_cart,
	$website_disabled_cart_validation,$enabled_merchant_check_closing_time,$disabled_order_confirm_page,
	$restrict_order_by_status,$enabled_map_selection_delivery,$admin_service_fee,$admin_service_fee_applytax;
	
	public $admin_country_set,$website_address,$website_contact_phone,$website_contact_email;
	
	public $admin_currency_set,$admin_currency_position,$admin_decimal_place,$admin_thousand_separator,$admin_decimal_separator;
	
	public $admin_menu_allowed_merchant,$admin_menu_lazyload,$mobile2_hide_empty_category,$admin_activated_menu,$enabled_food_search_menu;
	
	public $merchant_enabled_registration,$merchant_sigup_status,$merchant_default_country,
	$merchant_specific_country,$merchant_email_verification,$pre_configure_size;
	
	public $home_search_mode,$enabled_advance_search,$enabled_share_location,$google_default_country,$admin_zipcode_searchtype,$location_default_country;
	
	public $merchant_tbl_book_enabled,$booking_cancel_days,$booking_cancel_hours;
	
	public $website_enabled_guest_checkout,$enabled_cc_management,$enabled_featured_merchant,$enabled_subscription;
	
	public $cancel_order_enabled,$cancel_order_days_applied,$cancel_order_hours,$cancel_order_status_accepted,$website_review_approved_status;
	
	public $noti_new_signup_email,$noti_new_signup_sms,$noti_receipt_email,$noti_receipt_sms,$noti_booked_admin_email,$order_idle_admin_email,
	$order_cancel_admin_email,$order_cancel_admin_sms,$order_idle_admin_minutes,
	$merchant_near_expiration_day,$admin_enabled_order_notification,$admin_enabled_order_notification_sounds;
	
	public $enabled_multiple_translation_new,$enabled_language_admin,$enabled_language_merchant,$enabled_language_front;
	
	public $fb_flag,$fb_app_id,$fb_app_secret,$google_login_enabled,$google_client_id,$google_client_secret,$google_client_redirect_url,
	$enabled_contact_form,$contact_email_receiver,$contact_field,$contact_content,$admin_header_codes,
	$enabled_fb_pixel,$fb_pixel_id,$enabled_google_analytics,$google_analytics_tracking_id;
	
	/*MERCHANT SETTINGS*/
	public $food_option_not_available,$enabled_private_menu,$merchant_two_flavor_option,$merchant_tax_number,
	$merchant_extenal,$merchant_enabled_voucher,$merchant_required_delivery_time,$merchant_packaging_wise,
	$merchant_packaging_charge,$merchant_packaging_increment, $merchant_tax, $merchant_apply_tax,$merchant_delivery_charges,
	$merchant_no_tax_delivery_charges, $merchant_opt_contact_delivery,$merchant_delivery_estimation,$merchant_delivery_miles,$merchant_distance_type,
	$merchant_enabled_tip,$merchant_default_tip,$merchant_close_store,$merchant_show_time,$merchant_disabled_ordering
	;
	
	public $tracking_estimation_delivery1,$tracking_estimation_delivery2,$tracking_estimation_pickup1,$tracking_estimation_pickup2,
	$tracking_estimation_dinein1,$tracking_estimation_dinein2;
	
	/*BOOKING SETTINGS*/
	public $enabled_merchant_table_booking,$accept_booking_sameday,$fully_booked_msg,$enabled_merchant_booking_alert,
	$merchant_booking_receiver,$merchant_delivery_estimation_inminutes, $merchant_delivery_estimation_min1, $merchant_delivery_estimation_min2;
	
	public $merchant_delivery_charges_type,$merchant_maximum_order,$merchant_minimum_order,
	$merchant_delivery_fee_priority , $merchant_delivery_fee_no_rush,
	$merchant_minimum_order_pickup,$merchant_maximum_order_pickup,$merchant_minimum_order_dinein,$merchant_maximum_order_dinein,
	$sms_notify_number,$facebook_page,$twitter_page,$google_page,$merchant_enabled_alert,$merchant_email_alert,$merchant_mobile_alert,
	$order_verification,$order_sms_code_waiting,$free_delivery_above_price,$merchant_pickup_estimation,$free_delivery_on_first_order,
	$merchant_service_fee,$merchant_service_fee_applytax
	;
	
	public $admin_enabled_alert, $admin_email_alert, $admin_mobile_alert;
	
	public $signup_type, $signup_enabled_verification, $signup_enabled_terms,$signup_terms,$signup_enabled_capcha,
	$signup_welcome_tpl,$signup_verification_tpl,$signup_resetpass_tpl, $signup_resend_counter,$signupnew_tpl,
	$enabled_website_ordering , $merchant_reg_verification, $merchant_reg_admin_approval,
	$search_enabled_select_from_map ,$search_default_country, $location_searchtype, $review_template_id,
	$review_send_after,$review_template_enabled , $review_image_resize_width
	;
	
	public $realtime_provider, $pusher_app_id, $pusher_key, $pusher_secret, $pusher_cluster,
	$merchant_enabled_registration_capcha, $registration_membeship, $registration_commission,
	$registration_confirm_account_tpl, $registration_welcome_tpl, $registration_program,
	$registration_terms_condition , $merchant_registration_new_tpl, $merchant_registration_welcome_tpl,
	$merchant_plan_expired_tpl,$merchant_plan_near_expired_tpl, $merchant_order_critical_mins, $merchant_order_reject_mins,
	$mobilephone_settings_country,$mobilephone_settings_default_country ,$capcha_admin_login_enabled, $capcha_merchant_login_enabled,
	$enabled_language_bar, $default_language, $enabled_language_bar_front,$enabled_language_customer_app,$enabled_language_rider_app,$enabled_language_merchant_app,
	$backend_forgot_password_tpl, $allow_return_home, $image_resizing
	;

	public $merchant_fb_flag,$merchant_fb_app_id,$merchant_fb_app_secret,$merchant_google_login_enabled,
	$merchant_google_client_id,$merchant_google_client_secret;

	public $merchant_captcha_enabled,$merchant_captcha_site_key,$merchant_captcha_secret,$merchant_captcha_lang;

	public $merchant_map_provider,$merchant_google_geo_api_key,$merchant_google_maps_api_key,$merchant_mapbox_access_token;

	public $merchant_signup_enabled_verification,$merchant_signup_resend_counter,$merchant_signup_enabled_terms,$merchant_signup_terms,
	$merchant_mobilephone_settings_country,$merchant_mobilephone_settings_default_country, $merchant_set_default_country,$instagram_page;

	public $website_jwt_token,$runactions_method;

	public $driver_enabled_alert, $driver_alert_time,
	$driver_enabled_auto_assign, $driver_allowed_number_task,
	$driver_jwt_token,$driver_assign_when_accepted,
	$driver_sendcode_via,$driver_sendcode_interval,$driver_sendcode_tpl,
	$driver_map_enabled_cluster,$driver_task_take_pic;
	
	public $enabled_auto_pwa_redirect,$pwa_url,$android_download_url,$ios_download_url,$mobile_app_version_android,$mobile_app_version_ios,
	$tips_in_transactions,$merchant_tip_type;	

	public $merchant_jwt_token,$mt_app_version_android,$mt_app_version_ios,$mt_android_download_url,$mt_ios_download_url;

	public $enabled_home_steps,$enabled_home_promotional,$enabled_signup_section,$enabled_mobileapp_section,$enabled_social_links,$linkedin_page,
	$enabled_auto_detect_address,$invoice_new_upload_deposit,$invoice_created,$booking_enabled, $booking_enabled_capcha, $booking_days_of_week,$booking_time_start,$booking_time_end,$booking_time_interval,$booking_allowed_choose_table,
	$booking_reservation_custom_message ,$booking_reservation_terms,
	$booking_tpl_reservation_confirmed,$booking_tpl_reservation_canceled,$booking_tpl_reservation_denied,$booking_tpl_reservation_finished,$booking_tpl_reservation_no_show,
	$booking_tpl_reservation_requested,$booking_tpl_reservation_updated,$contact_us_tpl,$contact_enabled_captcha,$contact_page_url,$runactions_enabled,$table_list,$password
	;

	public $cookie_consent_enabled,$cookie_theme_primary_color,$cookie_theme_mode,$cookie_theme_dark_color,$cookie_theme_light_color,$cookie_position,$cookie_full_width,
	$cookie_title,$cookie_link_label,$cookie_link_accept_button,$cookie_link_reject_button,$cookie_message,$cookie_show_preferences,$cookie_expiration,$menu_layout,
	$merchant_menu_type,$merchant_enabled_language,$merchant_default_language, $driver_signup_terms_condition,
	$driver_employment_type,$driver_salary_type,$driver_salary,$driver_fixed_amount,$driver_commission,$driver_commission_type,$driver_registration_process,
	$driver_commission_per_delivery,$driver_incentives_amount,$driver_maximum_cash_amount_limit,$merchant_addons_use_checkbox
	;

	public $driver_cashout_fee,$driver_cashout_minimum,$driver_cashout_miximum,$driver_cashout_request_limit,$driver_enabled_request_break,$driver_request_break_limit,$driver_enabled_delivery_otp,
	$driver_maximum_cash_amount,$driver_time_allowed_accept_order,$driver_enabled_time_allowed_acceptance,$driver_missed_order_tpl,$driver_order_otp_tpl,$driver_enabled_end_shift;

	public $firebase_apikey,$firebase_domain,$firebase_projectid,$firebase_storagebucket,$firebase_messagingid,$firebase_appid;

	public $merchant_page_privacy_policy,$merchant_page_terms,$merchant_page_aboutus,$merchant_geolocationdb;

	public $points_enabled,$points_earning_points,$points_spent_value,$points_minimum_purchase,$points_maximum_purchase,
	$points_earning_rule,$points_redeemed_points,$points_redeemed_value,$points_minimum_redeemed,$points_maximum_redeemed,$points_redemption_policy,$points_cover_cost,
	$points_registration,$points_review,$points_first_order,$points_booking,$points_expiry,$points_use_thresholds,$points_minimum_subtotal;

	public $enabled_copy_opening_hours, $merchant_default_opening_hours_start, $merchant_default_opening_hours_end,
	$enabled_copy_payment_setting,$copy_payment_list , $test_runactions_email_address, $runaction_test_tpl, $merchant_allow_login_afterregistration,
	$enabled_guest,$merchant_timezone,$merchant_time_picker_interval,$merchant_default_currency;
	
	public $invoice_payment_bank_name,$invoice_payment_bank_account_name,$invoice_payment_bank_account_number,$invoice_payment_bank_custom_template,
	$menu_disabled_inline_addtocart;

	public $driver_app_name,$driver_android_download_url,$driver_ios_download_url,$driver_app_version_android,$driver_app_version_ios,$self_delivery;

	public $merchant_charge_type,$merchant_small_order_fee,$merchant_small_less_order_based, $category_position;

	public $multicurrency_enabled,$multicurrency_apikey,$multicurrency_provider,$multicurrency_allowed_merchant_choose_currency,
	$multicurrency_enabled_hide_payment,$multicurrency_currency_list,$multicurrency_enabled_checkout_currency,
	$admin_enabled_continues_alert,$admin_continues_alert_interval,$merchant_enabled_continues_alert,$merchant_continues_alert_interval,
	$booking_time_format, $merchant_disabled_pos_earnings,$website_twentyfour_format, $auto_accept_order_status,$auto_accept_order_timer,$auto_accept_order_enabled,
	$merchant_enabled_auto_accept_order,$driver_add_proof_photo,$driver_on_demand_availability,$admin_addons_use_checkbox,$admin_category_use_slide,$site_food_avatar,
	$site_user_avatar,$site_merchant_avatar, $default_location_lat,$default_location_lng,$digitalwallet_transaction_limit,
	$digitalwallet_enabled,$digitalwallet_enabled_topup,$digitalwallet_topup_minimum,$digitalwallet_topup_maximum,$digitalwallet_refund_to_wallet;

	public $import_select_type,$import_select_table,$chat_enabled,$chat_enabled_merchant_delete_chat,$backend_phone_mask,$enabled_include_utensils,
	$signup_complete_registration_tpl;

	public $merchant_android_download_url,$merchant_ios_download_url,$merchant_mobile_app_version_android,$merchant_mobile_app_version_ios,
	$merchant_enabled_guest , $test_mobile_number,$merchant_time_selection,$enabled_review,$address_format_use,$password_reset_options;

	public $yandex_javascript_api,$yandex_static_api,$yandex_distance_api,$yandex_geocoder_api,$yandex_geosuggest_api,$yandex_language,$tableside_services;

	public $tableside_jwt_token,$merchant_enabled_tableside_alert , $tableside_send_status,$tableside_auto_print_status,
	$kitchen_jwt_token,$whatsapp_enabled,$whatsapp_business_accountid,$whatsapp_phone_number,$whatsapp_token,$whatsapp_receipt_templatename,
	$enabled_language_tableside_app,$enabled_language_kicthen_app, $login_method , 
	$merchant_bank_deposit_subscriptions, $merchant_subscription_approved, $merchant_registration_approved,
	$merchant_subscription_payment_process,$merchant_subscription_payment_failed,$merchant_subscription_cancelled,$location_enabled_map_selection;

	public $admin_delivery_charges_type,$admin_opt_contact_delivery,$admin_free_delivery_on_first_order,$default_distance_unit,$website_date_range,
	$merchant_default_preparation_time,$driver_threshold_meters,$merchant_whatsapp_phone_number,$merchant_enabled_whatsapp,
	$whatsapp_receipt_templatename_merchant,$whatsapp_language,$auto_print_status,$driver_auto_assign_retry,$driver_assign_max_retry, $merchant_enabled_age_verification,
	$driver_enabled_registration,$file_sounds,$file_sounds1,$admin_sounds_order,$admin_sounds_chat,$maintenance_mode;

	public $enabled_barcode,$enabled_barcode_search,$menu_display_type,$new_order_alert_interval,$enable_new_order_alert,
	$merchant_enable_new_order_alert,$merchant_new_order_alert_interval, $hubrise_enabled, $hubrise_client_id, $hubrise_secret, $strict_to_wallet,
	$app_enabled_google_login,$app_enabled_fb_login,$app_enabled_apple_login,$app_apple_app_id,$app_apple_team_id,$app_apple_key_id,$app_apple_key_crt,
	$file_crt,$web_enabled_apple_login,$app_google_client_id,$webpush_certificates,$app_facebook_client_token,$google_maps_api_key_for_mobile,
	$map_custom_style,$use_local_distance,$points_bunos_template,$ads_frequency,$ads_cooldown,$ads_unit,$enabled_ads,$order_assigment_flow,
	$driver_assigned_order_tpl,$merchant_fb_client_token
	;

	public $merchant_app_enabled_apple_login,$merchant_app_apple_app_id,$merchant_app_apple_team_id,$merchant_app_apple_key_id,$merchant_app_apple_service_file,
	$merchant_google_maps_api_key_for_mobile,$merchant_mobile_webpush_certificates;

	/**
	 * Returns the static model of the specified AR class.
	 * @return static the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{option}}';
	}
	
	public function primaryKey()
	{
	    return 'id';	 
	}
		
	public function attributeLabels()
	{
		return array(		    
		    'website_logo'=>t("Desktop Website Logo"),
		    'mobilelogo'=>t("Mobile Website Logo"),
		    'google_geo_api_key'=>t("Google Maps Platform API Key (Server-side)"),
		    'google_maps_api_key'=>t("Google Maps JavaScript API Key (Website)"),
		    'admin_printing_receipt_width'=>t("Receipt Width"),
		    'admin_printing_receipt_size'=>t("Font size"),
		    'website_receipt_logo'=>t("Receipt Logo"),
		    'website_terms_customer_url'=>t("Terms and conditions Link"),
		    'website_time_picker_interval'=>t("Time interval"),
			'merchant_time_picker_interval'=>t("Time interval"),
		    'admin_service_fee'=>t("Service fee"),
		    'admin_decimal_place'=>t("Decimals"),
		    'admin_decimal_separator'=>t("Decimals"),
		    'admin_thousand_separator'=>t("Thousand Separator"),
		    'booking_cancel_days'=>t("after how many days after booking"),
		    'booking_cancel_hours'=>t("after how many hours"),
		    'cancel_order_days_applied'=>t("after how many days of purchase"),
		    'cancel_order_hours'=>t("after how many hours"),
		    'noti_new_signup_email'=>t("Merchant new signup email"),
		    'noti_new_signup_sms'=>t("Merchant new signup SMS"),
		    'noti_receipt_email'=>t("Receipt Email send to admin"),
		    'noti_receipt_sms'=>t("Receipt SMS send to admin"),
		    'noti_booked_admin_email'=>t("New Table Booking email"),
		    'order_idle_admin_email'=>t("Order IDLE email"),
		    'order_cancel_admin_email'=>t("Order Cancel Request email"),
		    'order_cancel_admin_sms'=>t("Order Cancel Request sms"),
		    'order_idle_admin_minutes'=>t("Order IDLE minutes"),
		    'merchant_near_expiration_day'=>t("Number of days before expiration"),
		    'fb_app_id'=>t("App ID"),
		    'fb_app_secret'=>t("App Secret"),
		    'google_client_id'=>t("Web Client ID"),
			'app_google_client_id'=>t("App Client ID"),
		    'google_client_secret'=>t("Client Secret"),
		    'google_client_redirect_url'=>t("Redirect URL"),
		    'contact_email_receiver'=>t("Receiver Email Address"),
		    'fb_pixel_id'=>t("Facebook Pixel ID"),
		    'google_analytics_tracking_id'=>t("Tracking ID"),
		    'merchant_tax_number'=>t("Tax number"),
		    'merchant_extenal'=>t("Website address"),
		    'merchant_packaging_charge'=>t("Packaging Fee"),
		    'merchant_tax'=>t("Tax"),
		    'merchant_delivery_charges'=>t("Standard Delivery Fee"),	
		    'merchant_delivery_fee_priority'=>t("Priority Delivery Fee"),	
		    'merchant_delivery_fee_no_rush'=>t("No Rush Delivery Fee"),	
		    'merchant_delivery_estimation'=>t("Delivery Estimation"),
		    'merchant_delivery_miles'=>t("Delivery Distance Covered"),
		    'tracking_estimation_delivery1'=>t("From"),
		    'tracking_estimation_delivery2'=>t("To"),
		    'merchant_delivery_estimation_inminutes'=>t("Delivery Estimation Minutes"),
		    'merchant_minimum_order'=>t("Minimum purchase amount"),
		    'merchant_maximum_order'=>t("Maximum purchase amount"),
		    
		    'merchant_minimum_order_pickup'=>t("Minimum purchase amount"),
		    'merchant_maximum_order_pickup'=>t("Maximum purchase amount"),
		    
		    'merchant_minimum_order_dinein'=>t("Minimum purchase amount"),
		    'merchant_maximum_order_dinein'=>t("Maximum purchase amount"),
		    'facebook_page'=>t("Facebook Page"),
		    'twitter_page'=>t("Twitter Page"),
		    'google_page'=>t("Google Page"),
		    'merchant_enabled_alert'=>t("Enabled Notifications"),
		    'merchant_email_alert'=>t("Email address"),
		    'merchant_enabled_alert'=>t("Mobile Number"),
		    'merchant_booking_receiver'=>t("Email address"),
		    'order_sms_code_waiting'=>t("Request code in minutes default is 5mins"),
		    'free_delivery_above_price'=>t("Free Delivery if Sub-Total Greater Or Equal To"),
		    'merchant_pickup_estimation'=>t("Standard estimate time for pickup"),
		    'merchant_service_fee'=>t("Service Fee"),
			'merchant_charge_type'=>t("Charge Type"),
			'merchant_small_order_fee'=>t("Small order fee"),
			'merchant_small_less_order_based'=>t("Less than subtotal"),
		    'merchant_delivery_estimation_min1'=> t('Minutes'),
		    'merchant_delivery_estimation_min2'=> t('Minutes'),
		    
		    'admin_email_alert'=> t('Email address'),
		    'admin_mobile_alert'=> t('Mobile number'),
		    'signup_resend_counter'=> t('Resend code interval'),
		    'review_send_after'=>t("Send after how many days"),
		    'review_image_resize_width'=>t("Resize image width"),
		    
		    'pusher_app_id'=>t("App ID"),
		    'pusher_key'=>t("Key"),
		    'pusher_secret'=>t("Secret"),
		    'pusher_cluster'=>t("Cluster"),
		    
		    'merchant_order_critical_mins'=>t("Critical minutes"),
		    'merchant_order_reject_mins'=>t("Reject order minutes"),
		    'mapbox_access_token'=>t("Mapbox Access Token"),
		    'captcha_site_key'=>t("Captcha Site Key"),
		    'captcha_secret'=>t("Captcha Secret"),
		    'captcha_lang'=>t("Captcha Lang"),
		    'merchant_mobile_alert'=>t("Mobile number"),

			'merchant_fb_app_id'=>t("App ID"),
		    'merchant_fb_app_secret'=>t("App Secret"),
		    'merchant_google_client_id'=>t("Client ID"),
		    'merchant_google_client_secret'=>t("Client Secret"),

			'merchant_captcha_site_key'=>t("Captcha Site Key"),
		    'merchant_captcha_secret'=>t("Captcha Secret"),
		    'merchant_captcha_lang'=>t("Captcha Lang"),

			'merchant_google_geo_api_key'=>t("Geocoding API Key (Server-side)"),
		    'merchant_google_maps_api_key'=>t("Google Maps JavaScript API (Website)"),
			'merchant_mapbox_access_token'=>t("Mapbox Access Token"),
			'merchant_signup_resend_counter'=> t('Resend code interval'),		
			'instagram_page'=>t("Instagram Page"),
			'website_jwt_token'=>'',

			'driver_alert_time'=>t("Alert delayed time in minutes"),
			'driver_allowed_number_task'=>t("Allowed number of task"),
			'driver_sendcode_interval'=>t("Resend code interval"),

		    'pwa_url'=>t("Your PWA Domain URL"),
			'android_download_url'=>t("Your Android Google play URL"),
			'ios_download_url'=>t("Your iOS Apps Store URL"),
			'mobile_app_version_android'=>t("Your android latest version"),
			'mobile_app_version_ios'=>t("Your iOS latest version"),
			'mt_android_download_url'=>t("Your Android Google play URL"),
			'mt_ios_download_url'=>t("Your iOS Apps Store URL"),
			'mt_app_version_android'=>t("Your android latest version"),
			'mt_app_version_ios'=>t("Your iOS latest version"),

			'booking_enabled'=>t("Enabled Reservation"),
			'booking_enabled_capcha'=>t("Enabled Captcha"),
			'booking_time_start'=>t("First seating"),
			'booking_time_end'=>t("Last seating"),
			'booking_time_interval'=>t("Interval"),
			'booking_allowed_choose_table'=>t("Allowed guest choose table"),
			'booking_reservation_custom_message'=>t("Online booking custom confirmation message (optional)"),
			'booking_reservation_terms'=>t("Online booking T&C (optional)"),
			'booking_tpl_reservation_requested'=>t("Booking reservation requested"),
			'booking_tpl_reservation_confirmed'=>t("Booking reservation confirmed"),
			'booking_tpl_reservation_canceled'=>t("Booking reservation canceled"),
			'booking_tpl_reservation_denied'=>t("Booking reservation denied"),
			'booking_tpl_reservation_finished'=>t("Booking reservation finished"),
			'booking_tpl_reservation_no_show'=>t("Booking reservation no show"),
			'booking_tpl_reservation_updated'=>t("Booking reservation updated"),

			'cookie_title'=>t("Title"),
			'cookie_link_accept_button'=>t("Accept Button Label"),
			'cookie_link_reject_button'=>t("Reject Button Label"),
			'cookie_expiration'=>t("Cookie expiration (days)"),
			'menu_layout'=>t("Menu Layout"),

			'driver_salary_type'=>t("Salary type"),
			'driver_salary'=>t("Salary amount"),
			'driver_fixed_amount'=>t("Fixed amount"),
			'employment_type'=>t("Employment Type"),
			'driver_commission'=>t("Commission amount"),
			'driver_commission_type'=>t("Commission type"),
			'driver_registration_process'=>t("Registration process"),
			'driver_commission_per_delivery'=>t("Your commission in %"),
			'driver_cashout_fee'=>t('Cashout processing fee'),
			'driver_cashout_minimum'=>t('Cashout minimum amount'),
			'driver_cashout_miximum'=>t('Cashout maximum amount'),
			'driver_cashout_request_limit'=>t("Number of cashout request limit"),
			'driver_request_break_limit'=>t("allowed number of break"),
			'driver_maximum_cash_amount'=>t("Allowed cash amount that can collect"),
			'driver_time_allowed_accept_order'=>t("Time in Minutes"),
			'driver_incentives_amount'=>t("Incentives amount (per delivery) "),
			'driver_maximum_cash_amount_limit'=>t("Maximum cash amount that can collect "),

			'firebase_apikey'=>t("API Key"),
			'firebase_domain'=>t("Auth domain"),
			'firebase_projectid'=>t("Project Id"),
			'firebase_storagebucket'=>t("Storage Bucket"),
			'firebase_messagingid'=>t("Messaging SenderId"),
			'firebase_appid'=>t("App Id"),
			'merchant_geolocationdb'=>t("API key"),
			
			'points_earning_points'=>t("Customer will be awarded point(s)"),	
			'points_minimum_purchase'=>t("Minimum purchase"),	
			'points_spent_value'=>t("For a purchase of every"),
			'points_redeemed_points'=>t("Redeem"),
			'points_redeemed_value'=>t("To get"),
			'points_minimum_redeemed'=>t("Minimum Redemption Points"),
			'points_maximum_redeemed'=>t("Maximum Redemption Points"),
			'points_registration'=>t("Points for Registration"),
			'points_review'=>t("Points for Review"),
			'points_first_order'=>t("Points for first order"),
			'points_booking'=>t("Points for Booking"),

			'merchant_default_opening_hours_start'=>t("From"),
			'merchant_default_opening_hours_end'=>t("To"),
			'test_runactions_email_address'=>t("Email address"),

			'test_mobile_number'=>t("Mobile number"),

			'invoice_payment_bank_name'=>t("Bank name"),
			'invoice_payment_bank_account_name'=>t("Account name"),
			'invoice_payment_bank_account_number'=>t("Account number"),
			'invoice_payment_bank_custom_template'=>t("Custom Template"),

			'driver_android_download_url'=>t("Your Android Google play URL"),
			'driver_ios_download_url'=>t("Your iOS Apps Store URL"),
			'driver_app_version_android'=>t("Your android latest version"),
			'driver_app_version_ios'=>t("Your iOS latest version"),
			'driver_app_name'=>t("Your rider mobile application name"),
			'multicurrency_apikey'=>t("API Keys"),

			'admin_continues_alert_interval'=>t("Alert Interval in seconds"),
			'merchant_continues_alert_interval'=>t("Alert Interval in seconds"),
			'default_location_lat'=>t("Latitude"),
			'default_location_lng'=>t("Longitude"),
			'digitalwallet_transaction_limit'=>t("Transaction Limits"),
			'digitalwallet_topup_minimum'=>t("Minimum top-up amount"),
			'digitalwallet_topup_maximum'=>t("Maximum top-up amount"),
			'backend_phone_mask'=>t("eg. +100000000"),

			'merchant_android_download_url'=>t("Your Android Google play URL"),
			'merchant_ios_download_url'=>t("Your iOS Apps Store URL"),
			'merchant_mobile_app_version_android'=>t("Your android latest version"),
			'merchant_mobile_app_version_ios'=>t("Your iOS latest version"),
			'yandex_javascript_api'=>t("Javascript API"),
			'yandex_static_api'=>t("Static API"),
			'yandex_distance_api'=>t("Distance Matrix"),
			'yandex_geocoder_api'=>t("Geocoder"),
			'yandex_geosuggest_api'=>t("Geosuggest"),
			'yandex_language'=>t("Language"),
			'whatsapp_business_accountid'=>t("Business Account ID"),
			'whatsapp_phone_number'=>t("Phone number ID"),
			'whatsapp_token'=>t("Token"),
			'whatsapp_receipt_templatename'=>t("Receipt template name"),
			'sms_notify_number'=>t("Sms Notify Number"),
			'points_minimum_subtotal'=>t("Minimum Subtotal"),
			'website_date_range'=>t("Number of days"),
			'merchant_default_preparation_time'=>t("Default Preparation Time (minutes)"),
			'driver_threshold_meters'=>t("In meters"),
			'merchant_whatsapp_phone_number'=>t("Whatsapp Phone number"),
			'merchant_enabled_whatsapp'=>t("Enabled Whatsapp Ordering"),
			'driver_assign_max_retry'=>t("minimum is 1"),
			'new_order_alert_interval'=>t('New Order Alert Interval (seconds)'),
			'enable_new_order_alert'=>t("Enable New Order Alert"),
			'merchant_enable_new_order_alert'=>t("Enable New Order Alert"),
			'merchant_new_order_alert_interval'=>t('New Order Alert Interval (seconds)'),

			'hubrise_client_id'=>t("Client ID"),
			'hubrise_secret'=>t("Client Secret"),
			'hubrise_enabled'=>t("Enabled"),
			'app_apple_app_id'=>t("Apple App ID"),
			'app_apple_team_id'=>t("Team ID"),
			'app_apple_key_id'=>t("Key ID"),
			'webpush_certificates'=>t("Web Push Certificates"),
			'app_facebook_client_token'=>t("Client Token"),
			'google_maps_api_key_for_mobile'=>t("Google Maps JavaScript API Key (Mobile App)"),
			'merchant_google_maps_api_key_for_mobile'=>t("Google Maps JavaScript API Key (Mobile App)"),
			'merchant_mobile_webpush_certificates'=>t("Web Push Certificates"),

			'ads_cooldown'=>t("Interval"),
			'merchant_fb_client_token'=>t("Client Token"),

			'merchant_app_apple_app_id'=>t("Apple App ID"),
			'merchant_app_apple_team_id'=>t("Team ID"),
			'merchant_app_apple_key_id'=>t("Key ID"),
		);
	}
		
	public function rules()
	{
		return array(
		  array('website_title,', 
		  'required','on'=>"site_config", 'message'=> t( Helper_field_required ) ),
		  
		  array('website_title', 'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')), 
		  
		  array('image,image2,website_logo,mobilelogo,admin_menu_allowed_merchant,admin_menu_lazyload,mobile2_hide_empty_category,
		  admin_activated_menu,enabled_food_search_menu,
		  merchant_enabled_registration,merchant_sigup_status,merchant_default_country,merchant_specific_country,
		  merchant_email_verification,pre_configure_size,home_search_mode,enabled_advance_search,enabled_share_location,
		  google_default_country,admin_zipcode_searchtype,location_default_country,merchant_tbl_book_enabled,
		  booking_cancel_days,booking_cancel_hours,website_enabled_guest_checkout,enabled_cc_management,enabled_featured_merchant,
		  enabled_subscription,cancel_order_enabled,cancel_order_days_applied,cancel_order_hours,cancel_order_status_accepted,website_review_approved_status
		  ',
		  'safe'),
		  
		  array('map_provider,google_geo_api_key,google_maps_api_key,mapbox_access_token',
		  'safe'),
		  
		  array('captcha_site_key,captcha_secret,captcha_lang,captcha_customer_signup,captcha_merchant_signup,captcha_customer_login,
		  captcha_merchant_login,captcha_admin_login,captcha_order,captcha_driver_signup,captcha_contact_form',
		  'safe'),
		  		  
		  array('admin_printing_receipt_width,admin_printing_receipt_size,website_enabled_rcpt,
		  website_receipt_logo','safe'),
		  
		  array('signup_verification,signup_verification_type,blocked_email_add,blocked_mobile,
		  website_terms_customer,website_terms_customer_url','safe'),		
		  
		  //array('website_terms_customer_url', 'url','message'=>t(Helper_field_url)),
		  
		  array('website_review_type,review_baseon_status,earn_points_review_status,publish_review_status,
		  website_reviews_actual_purchase,merchant_can_edit_reviews',
		  'safe'),
		  
		  array('website_admin_mutiple_login,website_merchant_mutiple_login','safe'),
		  array('website_timezone_new,website_date_format_new,website_time_format_new,website_time_picker_interval,merchant_time_picker_interval','safe'),
		  		  
		  array('website_time_picker_interval,signup_resend_counter,review_send_after,merchant_order_critical_mins,merchant_order_reject_mins,merchant_time_picker_interval,website_date_range', 
		  'numerical', 'integerOnly' => true,
		  'min'=>2,
		  'max'=>60,
		  'tooSmall'=>t("You must enter at least greater than 1"),
		  'tooBig'=>t("You must enter below 59"),
		  'message'=>t(Helper_field_numeric)),
		  
		  array('review_image_resize_width', 'numerical', 'integerOnly' => true,
		  'min'=>100,
		  'max'=>1000,
		  'tooSmall'=>t("You must enter at least greater than 100"),
		  'tooBig'=>t("You must enter below 1000"),
		  'message'=>t(Helper_field_numeric)),
		  
		  array('disabled_website_ordering,website_hide_foodprice,website_disbaled_auto_cart,
		  website_disabled_cart_validation,enabled_merchant_check_closing_time,
		  disabled_order_confirm_page,restrict_order_by_status,enabled_map_selection_delivery,
		  admin_service_fee,admin_service_fee_applytax','safe'),
		  
		  array('admin_service_fee,booking_cancel_days,cancel_order_days_applied,
		  merchant_near_expiration_day,order_idle_admin_minutes,order_sms_code_waiting,free_delivery_above_price,driver_sendcode_interval,driver_time_allowed_accept_order,
		  digitalwallet_topup_minimum,digitalwallet_topup_maximum,driver_threshold_meters
		  ', 'numerical', 'integerOnly' => false,
		  'min'=>1,		  
		  'tooSmall'=>t("You must enter at least greater than 1"),		  
		  'message'=>t(Helper_field_numeric)),

		  array('digitalwallet_transaction_limit', 'numerical', 'integerOnly' => false,
		  'min'=>0,		  
		  'tooSmall'=>t("You must enter at least greater than 1"),		  
		  'message'=>t(Helper_field_numeric)),
		  
		  
		  array('admin_printing_receipt_width,admin_printing_receipt_size,admin_decimal_place,driver_assign_max_retry', 'numerical', 'integerOnly' => true,
		  'min'=>1,		  
		  'tooSmall'=>t("You must enter at least greater than 1"),		  
		  'message'=>t(Helper_field_numeric)),

		  array('admin_continues_alert_interval,merchant_continues_alert_interval', 'numerical', 'integerOnly' => true,
		  'min'=>30,		  
		  'tooSmall'=>t("Minimum is 30 seconds"),		  
		  'message'=>t(Helper_field_numeric)),
		  
		  array('admin_country_set,website_address,website_contact_phone,website_contact_email','safe'),
		  
		  array('website_contact_email','email','message'=>t(Helper_field_email)),
		  
		  array('admin_currency_set,admin_currency_position,admin_decimal_place,admin_thousand_separator,admin_decimal_separator','safe'),
		  
		  array('admin_decimal_separator,admin_thousand_separator','length' , 'min'=>1,'max'=>1,
		  'tooLong'=>t("this fields is too long (maximum is 1 characters).")
		  ),
		  
		  //array('booking_cancel_hours','type'=>'time','timeFormat'=>'hh:mm' ),
		  array('booking_cancel_hours,cancel_order_hours', 'type', 'type'=>'time', 'timeFormat'=>'hh:mm',
		   'message'=>t(Helper_field_time)
		  ),
		  
		  array('noti_new_signup_email,noti_new_signup_sms,noti_receipt_email,noti_receipt_sms,noti_booked_admin_email,
		  order_idle_admin_email,order_cancel_admin_email,order_cancel_admin_sms,order_idle_admin_minutes,
		  merchant_near_expiration_day,admin_enabled_order_notification,admin_enabled_order_notification_sounds',
		  'safe'),
		  
		  array('enabled_multiple_translation_new,enabled_language_admin,enabled_language_merchant,enabled_language_front',
		  'safe'),
		  
		  array('fb_flag,fb_app_id,fb_app_secret,google_login_enabled,google_client_id,
		  google_client_secret,google_client_redirect_url,enabled_contact_form,contact_email_receiver,
		  contact_field,contact_content,admin_header_codes,enabled_fb_pixel,fb_pixel_id,enabled_google_analytics,
		  google_analytics_tracking_id',
		  'safe'),

		  array('contact_email_receiver', 'email', 'message'=> CommonUtility::t(Helper_field_email) ),		 
		  
		  array('image,image2', 'file', 'types'=>Helper_imageType, 'safe' => false,
			  'maxSize'=>Helper_maxSize,
			  'tooLarge'=>t(Helper_file_tooLarge),
			  'wrongType'=>t(Helper_file_wrongType),
			  'allowEmpty' => true
		  ),      

		  array('file_sounds,file_sounds1', 
			'file', 
			'types' => 'mp3,wav,ogg', 
			'maxSize' => 100 * 1024,
			'tooLarge' => t('The file "{file}" is too large. Maximum size is 100 KB.'),
			'allowEmpty' => true
		  ),
			
		  array('file_crt', 
			'file', 
			'types' => 'p8', 
			'maxSize' => 10 * 1024, // 10 KB
			'tooLarge' => t('The file "{file}" is too large. Maximum size is 10 KB.'),
			 'wrongType' => t('Invalid file type. Only .p8 files are allowed.'),
			'allowEmpty' => true
		  ),		  

		  array('food_option_not_available,enabled_private_menu,merchant_two_flavor_option,merchant_tax_number,
		  merchant_extenal,merchant_enabled_voucher,merchant_required_delivery_time,merchant_packaging_wise,
		  merchant_packaging_charge,merchant_packaging_increment,merchant_tax,merchant_apply_tax,merchant_delivery_charges,
		  merchant_no_tax_delivery_charges,merchant_opt_contact_delivery,merchant_delivery_estimation,merchant_delivery_miles,
		  merchant_distance_type,merchant_enabled_tip,merchant_default_tip,merchant_close_store,merchant_show_time,merchant_disabled_ordering,
		  tracking_estimation_delivery1,tracking_estimation_delivery2,merchant_delivery_estimation_inminutes,
		  merchant_minimum_order,merchant_maximum_order,merchant_delivery_fee_priority,merchant_delivery_fee_no_rush,
		  merchant_minimum_order_pickup,merchant_maximum_order_pickup,merchant_minimum_order_dinein,merchant_maximum_order_dinein,
		  sms_notify_number,facebook_page,twitter_page,google_page,merchant_enabled_alert,merchant_email_alert,merchant_mobile_alert,
		  order_verification,order_sms_code_waiting,merchant_pickup_estimation,free_delivery_on_first_order,merchant_service_fee,
		  merchant_service_fee_applytax,merchant_charge_type,merchant_small_order_fee,merchant_small_less_order_based,category_position,multicurrency_enabled,
		  multicurrency_apikey,multicurrency_provider,multicurrency_allowed_merchant_choose_currency,multicurrency_enabled_hide_payment,multicurrency_currency_list,
		  multicurrency_enabled_checkout_currency,admin_enabled_continues_alert,admin_continues_alert_interval,merchant_enabled_continues_alert,merchant_continues_alert_interval,
		  booking_time_format,merchant_disabled_pos_earnings,website_twentyfour_format,auto_accept_order_status,auto_accept_order_timer,auto_accept_order_enabled,
		  merchant_enabled_auto_accept_order,driver_add_proof_photo,driver_on_demand_availability,admin_addons_use_checkbox,admin_category_use_slide,import_select_type,
		  import_select_table,site_food_avatar,site_user_avatar,site_merchant_avatar,default_location_lat,default_location_lng,
		  digitalwallet_transaction_limit,digitalwallet_enabled,digitalwallet_enabled_topup,digitalwallet_topup_minimum,digitalwallet_topup_maximum,digitalwallet_refund_to_wallet,
		  chat_enabled,chat_enabled_merchant_delete_chat,backend_phone_mask,enabled_include_utensils,signup_complete_registration_tpl,file_sounds,file_sounds1,
		  admin_sounds_order,admin_sounds_chat,maintenance_mode,enabled_barcode,enabled_barcode_search,menu_display_type,
		  new_order_alert_interval,enable_new_order_alert,merchant_enable_new_order_alert,merchant_new_order_alert_interval,hubrise_client_id,hubrise_secret,hubrise_enabled,
		  strict_to_wallet,app_enabled_google_login,app_enabled_fb_login,app_enabled_apple_login,app_apple_app_id,app_apple_team_id,app_apple_key_id,app_apple_key_crt,
		  web_enabled_apple_login,app_google_client_id,webpush_certificates,app_facebook_client_token,google_maps_api_key_for_mobile,map_custom_style,use_local_distance,
		  points_bunos_template,ads_frequency,ads_cooldown,ads_unit,enabled_ads,order_assigment_flow,driver_assigned_order_tpl,merchant_fb_client_token,
		  merchant_app_enabled_apple_login,merchant_app_apple_app_id,merchant_app_apple_team_id,merchant_app_apple_key_id,merchant_app_apple_service_file,
		  merchant_google_maps_api_key_for_mobile,merchant_mobile_webpush_certificates
		  ',
		  'safe'),
			
		  array('tracking_estimation_delivery1,tracking_estimation_delivery2', 'numerical', 'integerOnly' => true,
		  'min'=>1,
		  'max'=>60,
		  'tooSmall'=>t("You must enter at least greater than 1"),
		  'tooBig'=>t("You must enter below 60"),
		  'message'=>t(Helper_field_numeric),
		  'on'=>'tracking_estimation'
		  ),

		  array('tracking_estimation_delivery1,tracking_estimation_delivery2', 
		  'required','on'=>"tracking_estimation", 'message'=> t( Helper_field_required ) ),	  
		  
		  array('merchant_booking_receiver','email','message'=>t(Helper_field_email),
		   'on'=>'booking_settings'
		  ),
		  
		  array('enabled_merchant_table_booking,accept_booking_sameday,enabled_merchant_booking_alert,
		  fully_booked_msg,merchant_booking_receiver','safe','on'=>'booking_settings'),
		  
		   array('merchant_delivery_estimation_inminutes,merchant_delivery_estimation_min1,merchant_delivery_estimation_min2', 'numerical', 'integerOnly' => true,		  
		  'min'=>1,'max'=>300,
		  'tooSmall'=>t("Minimum value is 1"),
		  'message'=>t(Helper_field_numeric)),
		  
		  array('merchant_delivery_charges_type', 
		  'required','on'=>"delivery_settings", 'message'=> t( Helper_field_required ) ),
		  
		   array('merchant_delivery_charges,merchant_maximum_order,merchant_minimum_order,
		   merchant_delivery_fee_priority,merchant_delivery_fee_no_rush,merchant_minimum_order_pickup,merchant_maximum_order_pickup,
		   merchant_minimum_order_dinein,merchant_maximum_order_dinein,driver_cashout_fee,driver_cashout_minimum,driver_cashout_miximum,driver_cashout_request_limit,driver_request_break_limit
		   ', 'numerical', 'integerOnly' => false,		   
		  'min'=>1,		  
		  'tooSmall'=>t("You must enter at least greater than 1"),		  
		  'message'=>t(Helper_field_numeric)),
		  
		  array('website_terms_customer_url,facebook_page,twitter_page,google_page,merchant_extenal','url',
		   'defaultScheme'=>'http',
		   'message'=>t(HELPER_NOT_VALID_URL)
		  ),
		 	
		  array('merchant_service_fee,merchant_small_order_fee,merchant_small_less_order_based','numerical','integerOnly'=>false),	 
		  
		  array('admin_enabled_alert,admin_email_alert,admin_mobile_alert,signup_type,
		  signup_enabled_verification,signup_enabled_terms,signup_terms,signup_enabled_capcha,
		  signup_welcome_tpl,signup_verification_tpl,signup_resetpass_tpl,enabled_website_ordering,signupnew_tpl,
		  merchant_reg_verification,merchant_reg_admin_approval, search_enabled_select_from_map,
		  search_default_country,location_searchtype,review_template_id,review_send_after,review_template_enabled,
		  review_image_resize_width
		  ', 'safe' ),
		  
		  array('pusher_app_id,pusher_key,pusher_secret,pusher_cluster,realtime_provider,
		  merchant_enabled_registration_capcha ,registration_membeship , registration_commission,
		  registration_confirm_account_tpl,registration_welcome_tpl,registration_program,registration_terms_condition,
		  merchant_registration_new_tpl,merchant_registration_welcome_tpl,merchant_plan_expired_tpl,
		  merchant_plan_near_expired_tpl,merchant_order_critical_mins,merchant_order_reject_mins,
		  mobilephone_settings_country,mobilephone_settings_default_country,
		  capcha_admin_login_enabled,capcha_merchant_login_enabled,enabled_language_bar,default_language,enabled_language_bar_front,enabled_language_customer_app,enabled_language_rider_app,
		  enabled_language_merchant_app,backend_forgot_password_tpl,allow_return_home,image_resizing,
		  merchant_fb_flag,merchant_fb_app_id,merchant_fb_app_secret,merchant_google_login_enabled,merchant_google_client_id,
		  merchant_google_client_secret,
		  merchant_captcha_enabled,merchant_captcha_site_key,merchant_captcha_secret,merchant_captcha_lang,
		  merchant_map_provider,merchant_google_geo_api_key,merchant_google_maps_api_key,merchant_mapbox_access_token,
		  merchant_signup_enabled_verification,merchant_signup_resend_counter,merchant_signup_enabled_terms,merchant_signup_terms,
		  merchant_mobilephone_settings_country,merchant_mobilephone_settings_default_country,merchant_set_default_country,instagram_page,
		  runactions_method
		  ','safe'),

		  array('driver_enabled_alert,driver_alert_time,driver_enabled_auto_assign,
		  driver_allowed_number_task,driver_jwt_token,driver_assign_when_accepted,driver_sendcode_via,driver_sendcode_tpl,driver_map_enabled_cluster,driver_task_take_pic,
		  enabled_auto_pwa_redirect,pwa_url,android_download_url,ios_download_url,tips_in_transactions,merchant_tip_type,mt_android_download_url,
		  mt_ios_download_url,mt_app_version_android,mt_app_version_ios,mobile_app_version_android,mobile_app_version_ios,enabled_home_steps,enabled_home_promotional,
		  enabled_signup_section,enabled_mobileapp_section,enabled_social_links,linkedin_page,enabled_auto_detect_address,invoice_new_upload_deposit,invoice_created,
		  booking_enabled,booking_enabled_capcha,booking_days_of_week,booking_time_start,booking_time_end,booking_time_interval,booking_allowed_choose_table,booking_reservation_custom_message ,booking_reservation_terms,
		  booking_tpl_reservation_confirmed,booking_tpl_reservation_canceled,
		  booking_tpl_reservation_denied,booking_tpl_reservation_finished,booking_tpl_reservation_no_show,booking_tpl_reservation_requested,booking_tpl_reservation_updated,
		  contact_us_tpl,contact_enabled_captcha,runactions_enabled,table_list,password,cookie_consent_enabled,cookie_show_preferences,cookie_theme_mode,
		  cookie_theme_primary_color,cookie_theme_dark_color,cookie_theme_light_color,cookie_position,cookie_full_width,cookie_title,cookie_link_label,
		  cookie_link_accept_button,cookie_link_reject_button,cookie_message,cookie_expiration,menu_layout,merchant_menu_type,merchant_enabled_language,merchant_default_language,
		  driver_signup_terms_condition,driver_employment_type,driver_salary_type,driver_salary,driver_fixed_amount,driver_commission,driver_commission_type,driver_registration_process,
		  driver_commission_per_delivery,driver_cashout_fee,driver_cashout_minimum,driver_cashout_miximum,driver_cashout_request_limit,driver_enabled_request_break,
		  driver_request_break_limit,driver_enabled_delivery_otp,driver_maximum_cash_amount,driver_maximum_cash_amount,driver_time_allowed_accept_order,driver_enabled_time_allowed_acceptance,
		  driver_missed_order_tpl,driver_incentives_amount,driver_maximum_cash_amount_limit,driver_order_otp_tpl,firebase_apikey,firebase_domain,firebase_projectid,firebase_storagebucket,
		  firebase_messagingid,firebase_appid,driver_enabled_end_shift,merchant_addons_use_checkbox,
		  merchant_page_privacy_policy,merchant_page_terms,merchant_page_aboutus,merchant_geolocationdb,
		  points_enabled,points_earning_rule,merchant_default_opening_hours_start,merchant_default_opening_hours_end,enabled_copy_opening_hours,
		  enabled_copy_payment_setting,copy_payment_list,runaction_test_tpl,merchant_allow_login_afterregistration,enabled_guest,merchant_timezone,merchant_default_currency,
		  invoice_payment_bank_name,invoice_payment_bank_account_name,invoice_payment_bank_account_number,invoice_payment_bank_custom_template,menu_disabled_inline_addtocart,
		  driver_app_name,driver_android_download_url,driver_ios_download_url,driver_app_version_android,driver_app_version_ios,self_delivery,points_redemption_policy,points_cover_cost,
		  points_registration,points_review,points_first_order,points_booking,points_expiry,points_use_thresholds,points_minimum_subtotal,
		  merchant_android_download_url,merchant_ios_download_url,merchant_mobile_app_version_android,merchant_mobile_app_version_ios,merchant_enabled_guest,merchant_time_selection,
		  enabled_review,address_format_use,password_reset_options,yandex_javascript_api,yandex_static_api,yandex_distance_api,yandex_geocoder_api,yandex_geosuggest_api,yandex_language,
		  tableside_services,tableside_jwt_token,merchant_enabled_tableside_alert,tableside_send_status,tableside_auto_print_status,
		  kitchen_jwt_token,whatsapp_enabled,whatsapp_business_accountid,whatsapp_phone_number,whatsapp_token,whatsapp_receipt_templatename,enabled_language_tableside_app,enabled_language_kicthen_app,
		  login_method,merchant_bank_deposit_subscriptions,merchant_subscription_approved,merchant_registration_approved,merchant_subscription_payment_process,merchant_subscription_payment_failed,
		  merchant_subscription_cancelled,location_enabled_map_selection,admin_delivery_charges_type,admin_opt_contact_delivery,admin_free_delivery_on_first_order,default_distance_unit,
		  website_date_range,merchant_default_preparation_time,driver_threshold_meters,merchant_whatsapp_phone_number,merchant_enabled_whatsapp,whatsapp_receipt_templatename_merchant,
		  whatsapp_language,auto_print_status,driver_auto_assign_retry,driver_assign_max_retry,merchant_enabled_age_verification,driver_enabled_registration
		  ',
		  'safe'),

		  array('pwa_url,android_download_url,ios_download_url,mt_android_download_url,mt_ios_download_url','url'),

		  array('password,', 
		  'required','on'=>"clean_database", 'message'=> t( Helper_field_required ) ),


		  array('points_minimum_purchase,points_maximum_purchase,points_earning_points,points_spent_value,points_redeemed_points,points_redeemed_value,
		  points_minimum_redeemed,points_maximum_redeemed,points_registration,points_review,points_first_order,points_booking,points_minimum_subtotal', 
			'numerical', 'integerOnly' => false,
			'min'=>0.1,		  
			'tooSmall'=>t("You must enter at least greater than 1"),			
			'message'=>t(Helper_field_numeric),
			'on'=>'points_settings'
		  ),

		  array('test_runactions_email_address,', 
		  'required','on'=>"test_runactions", 'message'=> t( Helper_field_required ) ),
		  
		  array('test_runactions_email_address,', 
		  'email','on'=>"test_runactions", 'message'=> t( Helper_field_email ) ),
		  
		//   array('website_jwt_token','url', 'defaultScheme'=>'http'),

		//   array('website_jwt_token', 
		//   'required','message'=> t( Helper_field_required ) ),
		 
		array('test_mobile_number,', 
		  'required','on'=>"test_sms", 'message'=> t( Helper_field_required ) ),
		  		  		
		  
		);
	}

	
	protected function beforeSave()
	{
		if(parent::beforeSave()){
			$this->last_update = CommonUtility::dateNow();
		} 
		return true;
	}
	
	protected function afterSave()
	{
		parent::afterSave();		
		
		/*ADD CACHE REFERENCE*/
		CCacheData::add();
	}

	protected function afterDelete()
	{
		parent::afterDelete();				
		
		/*ADD CACHE REFERENCE*/
		CCacheData::add();	
	}
	
}
/*end class*/
