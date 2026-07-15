

<div id="payment-plan" class="container mt-4 mb-4" v-cloak>

<div class="text-center" style="max-width: 450px;margin:auto;" v-loading="loading">
   <h2 class="font-weight-bolder"><?php echo t("Select Payment")?> <span class="text-green"><?php echo t("Method")?></span></h2>
   
   
   <div class="mt-3 mb-3">
      <h4 class="mb-0">{{plan_details.title}}</h4>
      <p class="ellipsis-2-lines" v-html="plan_details.description"></p>
    </div>

    <h4 class=" font-weight-bolder">
       <template v-if="plan_details.promo_price_raw>0">
        <span class="text-muted opacity-60"><del>{{plan_details.price}}</del></span> <span>{{ plan_details.promo_price }}</span>
      </template>
      <template v-else>
        {{plan_details.price}}
      </template>
    </h4>

    <br/>    
          
    <div class="row justify-content-center q-gutter-md">  
        <template v-for="items in payment_list" :keys="items">
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

</div>

<?php CComponentsManager::renderComponents($payments,$payments_credentials,$this,'plans')?>

</div>