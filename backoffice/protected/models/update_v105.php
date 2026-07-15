<?php
$table = new TableDataStatus();

ob_start();
if(Yii::app()->db->schema->getTable("{{admin_meta_translation}}")){
    $table->add_Column("{{admin_meta_translation}}",array(
        'meta_value1'=>"text",          
    ));
}

if(Yii::app()->db->schema->getTable("{{item}}")){
    $table->add_Column("{{item}}",array(
        'ingredients_preselected'=>"tinyint(1) NOT NULL DEFAULT '0' AFTER `cooking_ref_required`",          
    ));
}
ob_end_clean();

// INSERT TEMPLATE
$model = new AR_templates();
$model->template_name = "Contact Us";
$model->enabled_email = 1;
$model->enabled_sms = 0;
$model->enabled_push = 1;
if($model->save()){
    $template_id = $model->template_id;
    $model2 = new AR_templates_translation();
    $model2->template_id = $template_id;
    $model2->template_type = "email";
    $model2->language = KMRS_DEFAULT_LANGUAGE;
    $model2->title = "New contact form from {{email_address}}";
    //$model2->content = 'New contact form from {{email_address}}, {% include \'header.html\' %}\n<table style=\"width:100%;\">\n <tbody><tr>\n  <td style=\"background:#fef9ef;padding:20px 30px;\">\n    <img style=\"max-width:20%;max-height:50px;\" src=\"{{logo}}\">\n  </td>\n </tr>\n <tr>\n   <td style=\"padding:30px;background:#ffffff;\" valign=\"middle\" align=\"left\">\n    \n   <table width=\"50%\" align=\"center\">\n   <tbody><tr>\n    <td>\n		 \n	\n	 <h5 style=\"margin-bottom:10px;\">CONTACT DETAILS</h5>\n	 \n	 <table>	 \n	 <tbody><tr><td>Email address:</td><td>: {{email_address}}</td></tr>	 \n	 <tr><td>Full name</td><td>: {{fullname}}</td></tr>	 \n	 <tr><td>Contact number</td><td>: {{contact_number}}</td></tr>	 \n	 <tr><td>Country</td><td>: {{country_name}}</td></tr>	 \n	 <tr><td>Message</td><td>: {{message}}</td></tr>	 \n	 </tbody></table>\n	 \n	 \n	\n	</td>\n   </tr>\n   </tbody></table>\n	\n   </td>\n </tr>\n \n \n \n  \n <tr>\n  <td style=\"background:#fef9ef;padding:20px 30px;\">\n    \n   <table style=\"width:100%; table-layout: fixed;\">\n	  <tbody><tr>\n	    <th colspan=\"3\" style=\"text-align: left;\"><h5>Contact Us</h5></th>\n	    <th colspan=\"7\" style=\"text-align: left;\"><h5>For  promos, news, and updates, follow us on:</h5></th>\n	  </tr>\n	  <tr>\n	    <td colspan=\"3\" style=\"text-align: left; padding:0 3px;\" valign=\"top\">\n	     <p>{{site.address}}</p>\n         <p>{{site.contact}}</p>\n         <p>{{site.email}}</p>\n	    </td><td colspan=\"7\" style=\"padding:0 3px;\" valign=\"top\">\n	    \n	      {% include \'social_link.html\' %}\n	     \n	     <table>\n	      <tbody><tr>\n	      <td style=\"padding:0;\"><a href=\"#\" style=\"color:#000;font-size:16px;\">Terms and Conditions</a></td>\n	      <td>●</td>\n	      <td style=\"padding:0;\"><a href=\"#\" style=\"color:#000;font-size:16px;\">Privacy Policy</a></td>\n	      </tr>\n	     </tbody></table>\n	    \n	    </td>\n	  </tr>\n	</tbody></table>\n  \n  </td>\n </tr>\n \n</tbody></table>\n{% include \'footer.html\' %}\n';
    $model2->content = '{% include \'header.html\' %} <table style="width:100%;"> <tbody><tr> <td style="background:#fef9ef;padding:20px 30px;"> <img style="max-width:20%;max-height:50px;" src="{{logo}}"> </td> </tr> <tr> <td style="padding:30px;background:#ffffff;" valign="middle" align="left"> <table width="50%" align="center"> <tbody><tr> <td> <h5 style="margin-bottom:10px;">CONTACT DETAILS</h5> <table> <tbody><tr><td>Email address:</td><td>: {{email_address}}</td></tr> <tr><td>Full name</td><td>: {{fullname}}</td></tr> <tr><td>Contact number</td><td>: {{contact_number}}</td></tr> <tr><td>Country</td><td>: {{country_name}}</td></tr> <tr><td>Message</td><td>: {{message}}</td></tr> </tbody></table> </td> </tr> </tbody></table> </td> </tr> <tr> <td style="background:#fef9ef;padding:20px 30px;"> <table style="width:100%; table-layout: fixed;"> <tbody><tr> <th colspan="3" style="text-align: left;"><h5>Contact Us</h5></th> <th colspan="7" style="text-align: left;"><h5>For promos, news, and updates, follow us on:</h5></th> </tr> <tr> <td colspan="3" style="text-align: left; padding:0 3px;" valign="top"> <p>{{site.address}}</p> <p>{{site.contact}}</p> <p>{{site.email}}</p> </td><td colspan="7" style="padding:0 3px;" valign="top"> {% include \'social_link.html\' %} <table> <tbody><tr> <td style="padding:0;"><a href="#" style="color:#000;font-size:16px;">Terms and Conditions</a></td> <td>●</td> <td style="padding:0;"><a href="#" style="color:#000;font-size:16px;">Privacy Policy</a></td> </tr> </tbody></table> </td> </tr> </tbody></table> </td> </tr> </tbody></table> {% include \'footer.html\' %}';
    $model2->save();
    
    $model2 = new AR_templates_translation();
    $model2->template_id = $template_id;
    $model2->template_type = "push";
    $model2->language = KMRS_DEFAULT_LANGUAGE;
    $model2->title = "New contact form from {{email_address}}";
    $model2->content = "New contact form from {{email_address}}";
    $model2->save();

    $model_options = new AR_option();
    $model_options->merchant_id = 0;
    $model_options->option_name = 'contact_us_tpl';
    $model_options->option_value = $template_id;
    $model_options->save();
}

$model = new AR_templates();
$model->template_name = "Booking Update status";
$model->enabled_email = 1;
$model->enabled_sms = 0;
$model->enabled_push = 1;
if($model->save()){
    $template_id = $model->template_id;
    $model2 = new AR_templates_translation();
    $model2->template_id = $template_id;
    $model2->template_type = "email";
    $model2->language = KMRS_DEFAULT_LANGUAGE;
    $model2->title = "Reservation {{status}} at {{restaurant_name}}";
    $model2->content = '{% include \'header.html\' %} <table style="width:100%;"> <tbody><tr> <td style="background:#fef9ef;padding:20px 30px;"> <img style="max-width:20%;max-height:50px;" src="{{logo}}"> </td> </tr> <tr> <td style="padding:30px;background:#ffffff;" valign="middle" align="left"> <table width="50%" align="center"> <tbody><tr> <td> <p style="margin-bottom:10px;">Your reservation at {{restaurant_name}} is {{status}}!</p> <h5 style="margin-bottom:10px;">RESERVATION DETAILS</h5> <table> <tbody><tr><td>Name of guest:</td><td>: {{guest_fullname}}</td></tr> <tr><td>Number of guests</td><td>: {{guest_number}}</td></tr> <tr><td>Time of arrival</td><td>: {{reservation_datetime}}</td></tr> </tbody></table> <p style="margin-bottom:10px;">Your Reservation ID is <b>{{reservation_id}}</b></p> <h5 style="margin-bottom:10px;">Special Requests</h5> <p style="margin-bottom:10px;">{{special_request}}</p> </td> </tr> </tbody></table> </td> </tr> <tr> <td style="background:#fef9ef;padding:20px 30px;"> <table style="width:100%; table-layout: fixed;"> <tbody><tr> <th colspan="3" style="text-align: left;"><h5>Contact Us</h5></th> <th colspan="7" style="text-align: left;"><h5>For promos, news, and updates, follow us on:</h5></th> </tr> <tr> <td colspan="3" style="text-align: left; padding:0 3px;" valign="top"> <p>{{site.address}}</p> <p>{{site.contact}}</p> <p>{{site.email}}</p> </td><td colspan="7" style="padding:0 3px;" valign="top"> {% include \'social_link.html\' %} <table> <tbody><tr> <td style="padding:0;"><a href="#" style="color:#000;font-size:16px;">Terms and Conditions</a></td> <td>●</td> <td style="padding:0;"><a href="#" style="color:#000;font-size:16px;">Privacy Policy</a></td> </tr> </tbody></table> </td> </tr> </tbody></table> </td> </tr> </tbody></table> {% include \'footer.html\' %}';
    $model2->save();
    
    $model2 = new AR_templates_translation();
    $model2->template_id = $template_id;
    $model2->template_type = "push";
    $model2->language = KMRS_DEFAULT_LANGUAGE;
    $model2->title = "Reservation {{status}} at {{restaurant_name}}";
    $model2->content = "Reservation {{status}} at {{restaurant_name}}";
    $model2->save();
    
    $data = [
        [
            'merchant_id'=>0,
            'option_name'=>'booking_tpl_reservation_canceled',
            'option_value'=>$template_id
        ],
        [
            'merchant_id'=>0,
            'option_name'=>'booking_tpl_reservation_denied',
            'option_value'=>$template_id
        ],
        [
            'merchant_id'=>0,
            'option_name'=>'booking_tpl_reservation_finished',
            'option_value'=>$template_id
        ],
        [
            'merchant_id'=>0,
            'option_name'=>'booking_tpl_reservation_no_show',
            'option_value'=>$template_id
        ]
    ];
    $builder=Yii::app()->db->schema->commandBuilder;
    $command=$builder->createMultipleInsertCommand('{{option}}',$data);
    $command->execute();
}

$model = new AR_templates();
$model->template_name = "Booking confirmed";
$model->enabled_email = 1;
$model->enabled_sms = 0;
$model->enabled_push = 1;
if($model->save()){
    $template_id = $model->template_id;
    $model2 = new AR_templates_translation();
    $model2->template_id = $template_id;
    $model2->template_type = "email";
    $model2->language = KMRS_DEFAULT_LANGUAGE;
    $model2->title = "Reservation Confirmed at {{restaurant_name}}";
    $model2->content = '{% include \'header.html\' %} <table style="width:100%;"> <tbody><tr> <td style="background:#fef9ef;padding:20px 30px;"> <img style="max-width:20%;max-height:50px;" src="{{logo}}"> </td> </tr> <tr> <td style="padding:30px;background:#ffffff;" valign="middle" align="left"> <table width="50%" align="center"> <tbody><tr> <td> <p style="margin-bottom:10px;">Your reservation at {{restaurant_name}} is confirmed!</p> <h5 style="margin-bottom:10px;">RESERVATION DETAILS</h5> <table> <tbody><tr><td>Name of guest:</td><td>: {{guest_fullname}}</td></tr> <tr><td>Number of guests</td><td>: {{guest_number}}</td></tr> <tr><td>Time of arrival</td><td>: {{reservation_datetime}}</td></tr> </tbody></table> <p style="margin-bottom:10px;">Your Reservation ID is <b>{{reservation_id}}</b></p> <div style="margin:auto;text-align:center;padding-top:10px; padding-bottom:10px;"> <a href="{{manage_reservation_link}}" target="_blank" style="display:block;margin:auto;max-width:200px;padding:10px;background:#3ecf8e;color:#fff;
    text-decoration:none;font-size:18px;font-weight:bold;"> Manage reservation </a> </div> <p style="text-align:center;">or click this link:</p> <p style="text-align:center;"><a href="{{manage_reservation_link}}">{{manage_reservation_link}}</a></p> <br><br> <h5 style="margin-bottom:10px;">Special Requests</h5> <p style="margin-bottom:10px;">{{special_request}}</p> <br><br> <h5 style="margin-bottom:10px;">RESTAURANT DETAILS</h5> <p style="margin-bottom:10px;">{{restaurant_name}}</p> <p style="margin-bottom:10px;">{{restaurant_contact_phone}} / {{restaurant_contact_email}}</p> <br><br> <h5 style="margin-bottom:10px;">Notes from the restaurant</h5> <p style="margin-bottom:10px;">{{notes_from_restaurant}}</p> </td> </tr> </tbody></table> </td> </tr> <tr> <td style="background:#fef9ef;padding:20px 30px;"> <table style="width:100%; table-layout: fixed;"> <tbody><tr> <th colspan="3" style="text-align: left;"><h5>Contact Us</h5></th> <th colspan="7" style="text-align: left;"><h5>For promos, news, and updates, follow us on:</h5></th> </tr> <tr> <td colspan="3" style="text-align: left; padding:0 3px;" valign="top"> <p>{{site.address}}</p> <p>{{site.contact}}</p> <p>{{site.email}}</p> </td><td colspan="7" style="padding:0 3px;" valign="top"> {% include \'social_link.html\' %} <table> <tbody><tr> <td style="padding:0;"><a href="#" style="color:#000;font-size:16px;">Terms and Conditions</a></td> <td>●</td> <td style="padding:0;"><a href="#" style="color:#000;font-size:16px;">Privacy Policy</a></td> </tr> </tbody></table> </td> </tr> </tbody></table> </td> </tr> </tbody></table> {% include \'footer.html\' %}';
    $model2->save();
    
    $model2 = new AR_templates_translation();
    $model2->template_id = $template_id;
    $model2->template_type = "push";
    $model2->language = KMRS_DEFAULT_LANGUAGE;
    $model2->title = "Reservation Confirmed at {{restaurant_name}}";
    $model2->content = "Reservation Confirmed at {{restaurant_name}}";
    $model2->save();

    $model_options = new AR_option();
    $model_options->merchant_id = 0;
    $model_options->option_name = 'booking_tpl_reservation_confirmed';
    $model_options->option_value = $template_id;
    $model_options->save();
}

$model = new AR_templates();
$model->template_name = "Booking requested";
$model->enabled_email = 1;
$model->enabled_sms = 0;
$model->enabled_push = 1;
if($model->save()){
    $template_id = $model->template_id;
    $model2 = new AR_templates_translation();
    $model2->template_id = $template_id;
    $model2->template_type = "email";
    $model2->language = KMRS_DEFAULT_LANGUAGE;
    $model2->title = "Online Reservation Confirmation Notification";
    $model2->content = '{% include \'header.html\' %} <table style="width:100%;"> <tbody><tr> <td style="background:#fef9ef;padding:20px 30px;"> <img style="max-width:20%;max-height:50px;" src="{{logo}}"> </td> </tr> <tr> <td style="padding:30px;background:#ffffff;" valign="middle" align="left"> <table width="50%" align="center"> <tbody><tr> <td> <p style="margin-bottom:10px;">You have received an online reservation.</p> <h5 style="margin-bottom:10px;">RESERVATION DETAILS</h5> <table> <tbody><tr><td>Restaurant Name</td><td>: {{restaurant_name}}</td></tr> <tr><td>Guest Name</td><td>: {{guest_fullname}}</td></tr> <tr><td>Guest Phone</td><td>: {{contact_phone}}</td></tr> <tr><td>Guest Email</td><td>: {{email_address}}</td></tr> <tr><td>Reservation ID</td><td>: {{reservation_id}}</td></tr> <tr><td>Date of booking</td><td>: {{date_created}}</td></tr> <tr><td>Time of arrival</td><td>: {{reservation_datetime}}</td></tr> <tr><td>Party of</td><td>: {{guest_number}}</td></tr> <tr><td>Special Request</td><td>: {{special_request}}</td></tr> </tbody></table> <br><br> <div style="margin:auto;text-align:center;padding-top:10px; padding-bottom:10px;"> <a href="{{manage_reservation_link}}" target="_blank" style="display:block;margin:auto;max-width:200px;padding:10px;background:#3ecf8e;color:#fff;
    text-decoration:none;font-size:18px;font-weight:bold;"> Manage reservation </a> </div> <p style="text-align:center;">or click this link:</p> <p style="text-align:center;"><a href="{{manage_reservation_link}}">{{manage_reservation_link}}</a></p> </td> </tr> </tbody></table> </td> </tr> <tr> <td style="background:#fef9ef;padding:20px 30px;"> <table style="width:100%; table-layout: fixed;"> <tbody><tr> <th colspan="3" style="text-align: left;"><h5>Contact Us</h5></th> <th colspan="7" style="text-align: left;"><h5>For promos, news, and updates, follow us on:</h5></th> </tr> <tr> <td colspan="3" style="text-align: left; padding:0 3px;" valign="top"> <p>{{site.address}}</p> <p>{{site.contact}}</p> <p>{{site.email}}</p> </td><td colspan="7" style="padding:0 3px;" valign="top"> {% include \'social_link.html\' %} <table> <tbody><tr> <td style="padding:0;"><a href="#" style="color:#000;font-size:16px;">Terms and Conditions</a></td> <td>●</td> <td style="padding:0;"><a href="#" style="color:#000;font-size:16px;">Privacy Policy</a></td> </tr> </tbody></table> </td> </tr> </tbody></table> </td> </tr> </tbody></table> {% include \'footer.html\' %}';
    $model2->save();
    
    $model2 = new AR_templates_translation();
    $model2->template_id = $template_id;
    $model2->template_type = "push";
    $model2->language = KMRS_DEFAULT_LANGUAGE;
    $model2->title = "Online Reservation Confirmation Notification";
    $model2->content = "Online Reservation Confirmation Notification";
    $model2->save();

    $model_options = new AR_option();
    $model_options->merchant_id = 0;
    $model_options->option_name = 'booking_tpl_reservation_requested';
    $model_options->option_value = $template_id;
    $model_options->save();
}


$model = new AR_templates();
$model->template_name = "Invoice new upload deposit";
$model->enabled_email = 1;
$model->enabled_sms = 0;
$model->enabled_push = 1;
if($model->save()){
    $template_id = $model->template_id;
    $model2 = new AR_templates_translation();
    $model2->template_id = $template_id;
    $model2->template_type = "email";
    $model2->language = KMRS_DEFAULT_LANGUAGE;
    $model2->title = "New bank deposit with invoice #{{invoice_number}}";
    $model2->content = '<p>New bank deposit with invoice #{{invoice_number}}<br></p>';
    $model2->save();
    
    $model2 = new AR_templates_translation();
    $model2->template_id = $template_id;
    $model2->template_type = "push";
    $model2->language = KMRS_DEFAULT_LANGUAGE;
    $model2->title = "New bank deposit with invoice #{{invoice_number}}";
    $model2->content = "New bank deposit with invoice #{{invoice_number}}";
    $model2->save();

    $model_options = new AR_option();
    $model_options->merchant_id = 0;
    $model_options->option_name = 'invoice_new_upload_deposit';
    $model_options->option_value = $template_id;
    $model_options->save();
}

$model = new AR_templates();
$model->template_name = "Invoice created";
$model->enabled_email = 1;
$model->enabled_sms = 0;
$model->enabled_push = 1;
if($model->save()){
    $template_id = $model->template_id;
    $model2 = new AR_templates_translation();
    $model2->template_id = $template_id;
    $model2->template_type = "email";
    $model2->language = KMRS_DEFAULT_LANGUAGE;
    $model2->title = "You have new Invoice #{{invoice_number}}";
    $model2->content = '<{% include \'header.html\' %} <table style="width:100%;"> <tbody><tr> <td style="background:#fef9ef;padding:20px 30px;"> <img style="max-width:20%;max-height:50px;" src="{{logo}}"> </td> </tr> <tr> <td style="background:#ffffff;"> <p>Your invoice is now ready, you can view your invoice by going to backoffice</p> </td> </tr> <tr> <td style="background:#fef9ef;padding:20px 30px;"> <table style="width:100%; table-layout: fixed;"> <tbody><tr> <th colspan="3" style="text-align: left;"><h5>Contact Us</h5></th> <th colspan="7" style="text-align: left;"><h5>For promos, news, and updates, follow us on:</h5></th> </tr> <tr> <td colspan="3" style="text-align: left; padding:0 3px;" valign="top"> <p>{{site.address}}</p> <p>{{site.contact}}</p> <p>{{site.email}}</p> </td><td colspan="7" style="padding:0 3px;" valign="top"> {% include \'social_link.html\' %} <table> <tbody><tr> <td style="padding:0;"><a href="#" style="color:#000;font-size:16px;">Terms and Conditions</a></td> <td>●</td> <td style="padding:0;"><a href="#" style="color:#000;font-size:16px;">Privacy Policy</a></td> </tr> </tbody></table> </td> </tr> </tbody></table> </td> </tr> </tbody></table> {% include \'footer.html\' %}';
    $model2->save();
    
    $model2 = new AR_templates_translation();
    $model2->template_id = $template_id;
    $model2->template_type = "push";
    $model2->language = KMRS_DEFAULT_LANGUAGE;
    $model2->title = "You have new Invoice #{{invoice_number}}";
    $model2->content = "You have new Invoice #{{invoice_number}}";
    $model2->save();

    $model_options = new AR_option();
    $model_options->merchant_id = 0;
    $model_options->option_name = 'invoice_created';
    $model_options->option_value = $template_id;
    $model_options->save();
}


// INSERT OPTIONS
$data = [    
    [
        'merchant_id'=>0,
        'option_name'=>'cookie_theme_mode',
        'option_value'=>'light'
    ],
    [
        'merchant_id'=>0,
        'option_name'=>'cookie_position',
        'option_value'=>'bottom_right'
    ],
    [
        'merchant_id'=>0,
        'option_name'=>'cookie_expiration',
        'option_value'=>365
    ],
    [
        'merchant_id'=>0,
        'option_name'=>'cookie_title',
        'option_value'=>'Cookie Consent'
    ],
    [
        'merchant_id'=>0,
        'option_name'=>'cookie_link_accept_button',
        'option_value'=>'Accept'
    ],
    [
        'merchant_id'=>0,
        'option_name'=>'cookie_link_reject_button',
        'option_value'=>'Decline'
    ],
    [
        'merchant_id'=>0,
        'option_name'=>'cookie_message',
        'option_value'=>'This website uses cookies or similar technologies, to enhance your browsing experience and provide personalized recommendations. By continuing to use our website, you agree to our {{privacy_policy_link}} '
    ],
    [
        'merchant_id'=>0,
        'option_name'=>'runactions_enabled',
        'option_value'=>1
    ],
    [
        'merchant_id'=>0,
        'option_name'=>'menu_layout',
        'option_value'=>'left_image'
    ],
];
$builder=Yii::app()->db->schema->commandBuilder;
$command=$builder->createMultipleInsertCommand('{{option}}',$data);
$command->execute();


// INSERT MENU
$menu_id = AttributesTools::getMenuParentID("admin",'Attributes');
if($menu_id){
    $model = new AR_menu();
    $model->menu_type="admin";
    $model->menu_name="Cookie Preferences";
    $model->parent_id=intval($menu_id);
    $model->link="attributes/cookie_preferences";
    $model->action_name="attributes.cookie_preferences";
    $model->sequence=18;
    $model->save();

    $model = new AR_menu();
    $model->menu_type="admin";
    $model->menu_name="Tips List";
    $model->parent_id=intval($menu_id);
    $model->link="attributes/tip_list";
    $model->action_name="attributes.tip_list";
    $model->sequence=16;
    $model->save();

    $model = new AR_menu();
    $model->menu_type="admin";
    $model->menu_name="Booking cancellation";
    $model->parent_id=intval($menu_id);
    $model->link="attributes/booking_cancel_list";
    $model->action_name="attributes.booking_cancel_list";
    $model->sequence=17;
    $model->save();
}

$menu_id = AttributesTools::getMenuParentID("merchant",'Printers');
if($menu_id){
    $model = new AR_menu();
    $model->menu_type="merchant";
    $model->menu_name="Print logs";
    $model->parent_id=intval($menu_id);
    $model->link="printers/logs";
    $model->action_name="printers.logs";
    $model->sequence=2;
    $model->save();
}

$menu_id = AttributesTools::getMenuParentID("admin",'Printers');
if($menu_id){
    $model = new AR_menu();
    $model->menu_type="admin";
    $model->menu_name="Print logs";
    $model->parent_id=intval($menu_id);
    $model->link="printer/logs";
    $model->action_name="printer.logs";
    $model->sequence=2;
    $model->save();
}

$check_exist = AttributesTools::getMenuParentID("admin",'Table reservation');
if(!$check_exist){
    $model = new AR_menu();
    $model->menu_type="admin";
    $model->menu_name="Table reservation";
    $model->parent_id=0;
    $model->link="";
    $model->action_name="table.reservation";
    $model->sequence=9;
    if($model->save()){
        $menu_id = $model->menu_id;
        $data = [
            [
            'menu_type'=>"admin",
            'menu_name'=>"Settings",
            'parent_id'=>intval($menu_id),
            'link'=>'reservation/settings',
            'action_name'=>"reservation.settings",
            'sequence'=>0
            ],
            [
                'menu_type'=>"admin",
                'menu_name'=>"List",
                'parent_id'=>intval($menu_id),
                'link'=>'reservation/list',
                'action_name'=>"reservation.list",
                'sequence'=>1
            ],
        ];
        $builder=Yii::app()->db->schema->commandBuilder;
        $command=$builder->createMultipleInsertCommand('{{menu}}',$data);
        $command->execute();
    }
}

$check_exist = AttributesTools::getMenuParentID("merchant",'Table Booking');
if(!$check_exist){
    $model = new AR_menu();
    $model->menu_type="merchant";
    $model->menu_name="Table Booking";
    $model->parent_id=0;
    $model->link="";
    $model->action_name="table.booking";
    $model->sequence=3;
    if($model->save()){
        $menu_id = $model->menu_id;
        $data = [
        [
            'menu_type'=>"merchant",
            'menu_name'=>"List",
            'parent_id'=>intval($menu_id),
            'link'=>'booking/list',
            'action_name'=>"booking.list",
            'sequence'=>0
        ],
        [
            'menu_type'=>"merchant",
            'menu_name'=>"Settings",
            'parent_id'=>intval($menu_id),
            'link'=>'booking/settings',
            'action_name'=>"booking.settings",
            'sequence'=>1
        ],
        [
            'menu_type'=>"merchant",
            'menu_name'=>"Shifts",
            'parent_id'=>intval($menu_id),
            'link'=>'booking/shifts',
            'action_name'=>"booking.shifts",
            'sequence'=>2
        ],
        [
            'menu_type'=>"merchant",
            'menu_name'=>"Room",
            'parent_id'=>intval($menu_id),
            'link'=>'booking/room',
            'action_name'=>"booking.room",
            'sequence'=>3
        ],
        [
            'menu_type'=>"merchant",
            'menu_name'=>"Tables",
            'parent_id'=>intval($menu_id),
            'link'=>'booking/tables',
            'action_name'=>"booking.tables",
            'sequence'=>4
        ]
        ];
        $builder=Yii::app()->db->schema->commandBuilder;
        $command=$builder->createMultipleInsertCommand('{{menu}}',$data);
        $command->execute();
    }
}

$check_exist = AttributesTools::getMenuParentID("admin",'Utilities');
if(!$check_exist){
    $model = new AR_menu();
    $model->menu_type="admin";
    $model->menu_name="Utilities";
    $model->parent_id=0;
    $model->link="";
    $model->action_name="Utilities";
    $model->sequence=21;
    if($model->save()){
        $menu_id = $model->menu_id;
        $data = [
            [
                'menu_type'=>"admin",
                'menu_name'=>"Fixed database",
                'parent_id'=>intval($menu_id),
                'link'=>'utilities/fixed_database',
                'action_name'=>"utilities.fixed_database",
                'sequence'=>1
            ],
            [
                'menu_type'=>"admin",
                'menu_name'=>"Clean database",
                'parent_id'=>intval($menu_id),
                'link'=>'utilities/clean_database',
                'action_name'=>"utilities.clean_database",
                'sequence'=>2
            ],
        ];
        $builder=Yii::app()->db->schema->commandBuilder;
        $command=$builder->createMultipleInsertCommand('{{menu}}',$data);
        $command->execute();
    }
}

// UPDATE COD ATTRIBUTES
$model = AR_payment_gateway::model()->find("payment_code=:payment_code",[
    ':payment_code'=>'code'
]);
if($model){
    $model->attr_json = '{\n	\"attr1\": {\n		\"label\": \"Change required, if required value = 1\"\n	},\n	\"attr2\": {\n		\"label\": \"Maximum limit\"\n	}\n}';
    $model->save();
}

$model = AR_payment_gateway_merchant::model()->find("payment_code=:payment_code",[
    ':payment_code'=>'code'
]);
if($model){
    $model->attr_json = '{\n	\"attr1\": {\n		\"label\": \"Change required, if required value = 1\"\n	},\n	\"attr2\": {\n		\"label\": \"Maximum limit\"\n	}\n}';
    $model->save();
}

// BOOKING CANCELLATION REASON
$model = AR_admin_meta::model()->find("meta_name=:meta_name",[
    ':meta_name'=>'reason_cancel_booking'
]);
if(!$model){
    $model = new AR_admin_meta();
    $model->meta_name="reason_cancel_booking";
    $model->meta_value="Reserved on another day or time";        
    if($model->save()){
        $meta_id = $model->meta_id;
        $data = [
            [
             'meta_id'=>$meta_id,
             'language'=>KMRS_DEFAULT_LANGUAGE,
             'meta_value'=>'Reserved on another day or time'
            ],        
        ];
        $builder=Yii::app()->db->schema->commandBuilder;
        $command=$builder->createMultipleInsertCommand('{{admin_meta_translation}}',$data);
        $command->execute();        
    }
    //
    $model = new AR_admin_meta();
    $model->meta_name="reason_cancel_booking";
    $model->meta_value="Reserved at another restaurant";        
    if($model->save()){
        $meta_id = $model->meta_id;
        $data = [
            [
             'meta_id'=>$meta_id,
             'language'=>KMRS_DEFAULT_LANGUAGE,
             'meta_value'=>'Reserved at another restaurant'
            ],        
        ];
        $builder=Yii::app()->db->schema->commandBuilder;
        $command=$builder->createMultipleInsertCommand('{{admin_meta_translation}}',$data);
        $command->execute();        
    }//
    $model = new AR_admin_meta();
    $model->meta_name="reason_cancel_booking";
    $model->meta_value="No longer dining out";        
    if($model->save()){
        $meta_id = $model->meta_id;
        $data = [
            [
             'meta_id'=>$meta_id,
             'language'=>KMRS_DEFAULT_LANGUAGE,
             'meta_value'=>'No longer dining out'
            ],        
        ];
        $builder=Yii::app()->db->schema->commandBuilder;
        $command=$builder->createMultipleInsertCommand('{{admin_meta_translation}}',$data);
        $command->execute();        
    }
    //
    $model = new AR_admin_meta();
    $model->meta_name="reason_cancel_booking";
    $model->meta_value="Other";        
    if($model->save()){
        $meta_id = $model->meta_id;
        $data = [
            [
             'meta_id'=>$meta_id,
             'language'=>KMRS_DEFAULT_LANGUAGE,
             'meta_value'=>"Other"
            ],        
        ];
        $builder=Yii::app()->db->schema->commandBuilder;
        $command=$builder->createMultipleInsertCommand('{{admin_meta_translation}}',$data);
        $command->execute();        
    }
}