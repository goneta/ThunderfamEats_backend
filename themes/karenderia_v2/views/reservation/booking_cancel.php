<div class="container p-2">  

<div id="vue-booking-reservation" >
   <div class="mt-4 mb-4">
   <booking-cancel
    ref="booking_cancel"
    id="<?php echo $id;?>"
    ajax_url="<?php echo Yii::app()->createUrl("/Apibooking")?>" 
    :label="{
        confirm: '<?php echo CJavaScript::quote(t("Are you sure to continue?"))?>',        
        cancel_reservation: '<?php echo CJavaScript::quote(t("Cancel reservation"))?>',
        yes: '<?php echo CJavaScript::quote(t("Yes"))?>',
        cancel: '<?php echo CJavaScript::quote(t("No"))?>',  
        ok: '<?php echo CJavaScript::quote(t("Okay"))?>',  
        }"    
    >
    </booking-cancel>
    </div>
</div>
<!-- vue-booking-reservation -->

<script type="text/x-template" id="xtemplate_booking_cancel">


   <template v-if="loading">
     <div style="min-height:300px;" v-loading="loading" class="text-center">        
     </div>
   </template>

   <template v-else>
   <div class="card w-50  m-auto">
      <div class="card-body p-0">
        
        <div class="items justify-content-between">
            <i class="zmdi zmdi-arrow-left"></i> 
            <a href="<?php echo $back_url;?>" class="pl-2"><b><?php echo t("Back")?></b></a>
        </div>
        
        <div class="p-2"></div>
        <h5><?php echo t("We're sorry that you have to cancel your reservation.")?></h5>

        <p><?php echo t("Would you please tell us why you wish to cancel?")?></p>
                
        <template v-for="items in data" :key="items">
          <div><el-radio v-model="reason"  :label="items">{{items}}</el-radio></div>        
        </template>        

      </div>
      <!-- card body -->
   </div>
   <!-- card -->

   <div class="text-center p-2">        
       <el-button @click="ConfirmcancelReservation" type="success" size="large" class="w-50" 
       :disabled="!hasData"
       :loading="submit"
        >
        <?php echo t("Cancel Reservation")?>
       </el-button>
    </div>    

    </template>

</script>

</div>
<!-- container -->