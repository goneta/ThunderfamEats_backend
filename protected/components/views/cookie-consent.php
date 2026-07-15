<DIV id="vue-cookie-consent">
<component-cookie-consent
:preferences_data='<?php echo json_encode($preferences_data)?>'
:show_preferences='<?php echo $cookie_show_preferences?>'
:cookie_expiration='<?php echo intval($cookie_expiration)?>'
:label="{
    cookie_title:'<?php echo CJavaScript::quote(t($cookie_title))?>', 
    accept_button: '<?php echo CJavaScript::quote(t($accept_button))?>',
    reject_button: '<?php echo CJavaScript::quote(t($reject_button))?>',
    cookie_message: '<?php echo CJavaScript::quote(t($cookie_message))?>',    
    select_cookies: '<?php echo CJavaScript::quote(t("SELECT COOKIES TO ACCEPT"))?>',
    customize: '<?php echo CJavaScript::quote(t("CUSTOMIZE"))?>',    
}"
:themes="{
    cookie_theme_mode:'<?php echo CJavaScript::quote($cookie_theme_mode)?>',     
    cookie_theme_primary_color:'<?php echo CJavaScript::quote($cookie_theme_primary_color)?>', 
    cookie_theme_dark_color:'<?php echo CJavaScript::quote($cookie_theme_dark_color)?>', 
    cookie_theme_light_color:'<?php echo CJavaScript::quote($cookie_theme_light_color)?>', 
    cookie_position:'<?php echo CJavaScript::quote($cookie_position)?>', 
}"
>
</component-cookie-consent>
</DIV>