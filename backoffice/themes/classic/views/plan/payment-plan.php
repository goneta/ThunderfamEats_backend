
<nav class="navbar navbar-light justify-content-between">
<?php
$this->widget('zii.widgets.CBreadcrumbs', 
array(
'links'=>$params['links'],
'homeLink'=>false,
'separator'=>'<span class="separator">
<i class="zmdi zmdi-chevron-right"></i><i class="zmdi zmdi-chevron-right"></i></span>'
));
?>
</nav>


<div id="vue-payment-plan" v-cloak>

   
  <div class="text-center" style="max-width: 490px; margin: auto;">
  <el-card shadow="never" v-loading="loading" > 

      <div><?php echo CommonUtility::safeTranslate("Amount to be paid")?></div>
      <h4><?php echo $amount;?></h4>

      <h2 class="font-weight-bolder"><?php echo CommonUtility::safeTranslate("Select Payment")?> <span class="text-green"><?php echo CommonUtility::safeTranslate("Method")?></span></h2>
      
      <div class="row justify-content-center q-gutter-md">  
      <template v-for="items in data" :keys="items">
            <div 
                @click="payment_code=items.payment_code" 
                class="bordered-box col-3 p-3 mb-3" 
                type="button"
                :class="{ 'selected': isActive(items.payment_code) }"
            >                
                <el-image style="width: 80px; height: 30px" :src="items.logo_image" fit="scale-down" lazy  ></el-image>
                <div class="text-xs font-medium mt-3">{{ items.payment_name }}</div>
            </div>    
      </template>
      </div>


      <button 
        type="button" 
        class="btn btn-success btn-block" 
        :disabled="!hasPaymentSelected"
        @click="showPayment"
        >        
            <?php echo t("Subscribe")?>
      </button>

 </el-card>
  </div>

  <!-- <components-subs-razorpay></components-subs-razorpay>   -->
  

</div>
<!-- vue-payment-plan -->