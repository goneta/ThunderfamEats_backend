<div class="container p-2">  

<div id="vue-booking-reservation" >
   <div class="mt-4 mb-4">

    <div class="card m-auto w-75">
      <div class="card-body p-0">
          <h5 class="mb-3"><?php echo t("Update Reservation")?></h5>

          <component-reservation ref="booking"
            ajax_url="<?php echo Yii::app()->createUrl("/Apibooking")?>" 
            api_url="<?php echo Yii::app()->createUrl("/api")?>" 
            merchant_uuid="<?php echo $merchant_uuid?>"		
            booking_enabled_capcha="<?php echo $booking_enabled_capcha?>"		
            captcha_site_key="<?php echo $captcha_site_key?>"		
            reservation_uuid="<?php echo $id?>"	
            :label="{		    
                guest: '<?php echo CJavaScript::quote(t("Guest"))?>', 
                date: '<?php echo CJavaScript::quote(t("Date"))?>', 		    
                time: '<?php echo CJavaScript::quote(t("Time"))?>',	
                no_results: '<?php echo CJavaScript::quote(t("We do not have any slots available for given criteria, please view the next available date"))?>',	
                terms: '<?php echo CJavaScript::quote(t("Restaurant Terms & Conditions"))?>',	
                continue: '<?php echo CJavaScript::quote(t("Continue"))?>',	
                reservation_details: '<?php echo CJavaScript::quote(t("Reservation details"))?>',	
                personal_details: '<?php echo CJavaScript::quote(t("Personal details"))?>',	
                first_name: '<?php echo CJavaScript::quote(t("First name"))?>',	
                last_name: '<?php echo CJavaScript::quote(t("Last name"))?>',	
                email_address: '<?php echo CJavaScript::quote(t("Email address"))?>',	
                special_request: '<?php echo CJavaScript::quote(t("Special requests"))?>',	
                agree: '<?php echo CJavaScript::quote(t("By continuing, you agree to Terms of Service and Privacy Policy."))?>',	
                back: '<?php echo CJavaScript::quote(t("Back"))?>',	
                reserve: '<?php echo CJavaScript::quote(t("Reserve"))?>',	
                reservation_id: '<?php echo CJavaScript::quote(t("Reservation ID"))?>',							
                reservation_succesful: '<?php echo CJavaScript::quote(t("Your reservation succesfully placed."))?>',	
                reservation_succesful_notes: '<?php echo CJavaScript::quote(t("You will receive another email once your reservation is confirm."))?>',	
                reserved_table_again: '<?php echo CJavaScript::quote(t("Reserved table again"))?>',	
                track_your_reservation: '<?php echo CJavaScript::quote(t("Track your reservation"))?>',	
                reservation_updated: '<?php echo CJavaScript::quote(t("Your reservation succesfully updated."))?>',	                
            }"	    
            >
            </component-reservation>

      </div>
      <!-- card body -->
    </div>
    <!-- card -->

    </div>
</div>
<!-- vue-booking-reservation -->