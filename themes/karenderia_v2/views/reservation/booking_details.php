
<div class="container p-2">  
   

   <div id="vue-booking-reservation" >
    <booking-details
    ref="booking"
    id="<?php echo $id;?>"
    ajax_url="<?php echo Yii::app()->createUrl("/Apibooking")?>" 
    >
    </booking-details>
   </div>

   <script type="text/x-template" id="xtemplate_booking_details">

   <template v-if="loading && !hasData">
     <div style="min-height:300px;" v-loading="loading" class="text-center">        
     </div>
   </template>
   
   <div v-else class="p-4">    
    <template v-if="!hasData">
        <div class="p-4 items text-center">
        <div class="pagenotfound-section mb-4 mt-5">
        <img class="img-fluid m-auto d-block" style="width:350px;height:auto;" src="<?php echo Yii::app()->theme->baseUrl."/assets/images/404@2x.png"?>" />
        </div>

        <div class="text-center mb-5">
        <h3><?php echo t("Reservation not found")?></h3>
        <p><?php echo t("uh-oh! Looks like the page you are trying to access, doesn't exist. Please start afresh")?>.</p>
        <a href="<?php echo Yii::app()->createUrl("/")?>" class="btn btn-green w25">
        <?php echo t("Go home")?>
        </a>
        </div>
        </div>
    </template>

    <template v-else>
             
    <template v-if="steps<=3">
    <el-steps :active="steps" finish-status="success" >
        <el-step title="<?php echo t("Pending")?>" />
        <el-step title="<?php echo t("Confirm")?>" />
        <el-step title="<?php echo t("Completed")?>" />
    </el-steps>
    </template>
    <template v-else> 
      <el-steps :active="steps" finish-status="error"  align-center >
        <el-step :title="data.status_pretty" />        
       </el-steps>
    </template>

    <!-- <pre>{{cancel_reservation_stats}}</pre> -->

    <div class="card mt-4">
      <div class="card-body p-0">
      
        <div class="row align-items-center">
            <div class="col">
                
                <div v-if="hasMerchant" class="items d-flex justify-content-between">
                <div>		       		            
                    <el-image
                        style="width: 50px; height: 50px"
                        class="rounded-pill"
                        :src="merchant.logo"
                        fit="cover"
                        lazy
                    ></el-image>
                </div> <!--col-->
                <div class=" flex-fill pl-2">
                                    
                    <a :href="merchant.menu_url" class="m-0 p-0">
                    <h5 class="m-0 chevron d-inline position-relative">{{merchant.restaurant_name}}</h5>
                    </a>                                 
                    <p class="m-0">{{merchant.address}}</p>
                </div> <!--col-->
                </div> <!--items-->               

            </div>
            <!-- col -->
            <div class="col text-right">                               
                <el-button type="danger" @click="toCancelPage"
                :disabled="!CanCancelReservation"
                ><?php echo t("Cancel Reservation")?></el-button>               
            </div>
        </div>
        <!-- row -->

      </div>
      <!-- card-body -->
    </div>
    <!-- card -->

    <div class="card border mt-3">
       <div class="card-body">
          <div class="row">
            <div class="col">
                <h5><?php echo t("Reservation Details")?></h5>
                <table class="table table-striped">
                    <tr>
                        <td ><?php echo t("Reservation ID")?>: {{data.reservation_id}}</td>                        
                        <td><b>{{data.status_pretty}}</b></td>
                    </tr>
                    <tr>
                        <td colspan="2">{{data.guest_number}}</td>                        
                    </tr>
                    <tr>
                        <td colspan="2">{{data.reservation_date}}</td>                        
                    </tr>
                    <tr>
                        <td colspan="2">{{data.reservation_time}}</td>                        
                    </tr>
                </table>
            </div>
            <div class="col">
            <h5><?php echo t("Your Details")?></h5>
            <table class="table table-striped">
                    <tr>
                        <td colspan="2">{{data.full_name}}</td>                        
                    </tr>
                    <tr>
                        <td colspan="2">{{data.email_address}}</td>                        
                    </tr>
                    <tr>
                        <td colspan="2">+{{data.contact_phone}}</td>                        
                    </tr>
                    <tr>                        
                    <td colspan="2">
                        <b><?php echo t("Special Request")?></b> {{data.special_request}}
                        <template v-if="data.cancellation_reason">
                        <p><?php echo t("CANCELLATION NOTES")?> =  {{data.cancellation_reason}}</p>
                        </template>                    
                    </td>                        
                    </tr>
                </table>
            </div>
          </div>
       </div>
       <!-- card body-->
    </div>
    <!-- card -->

    <div class="text-center p-2">
       <el-button @click="toUpdatePage" type="success" size="large" class="w-50"
       :disabled="!CanCancelReservation"
       >
        <?php echo t("Modify Reservation")?>
       </el-button>
    </div>    

    </template>
   </div>
   <!-- p-4 -->

   </script>
   
</div>
<!-- container -->