<div id="vue-subscription" class="container mt-4 mb-4" v-cloak>

<div class="text-center" style="max-width: 400px;margin:auto;">
  <h2 class="font-weight-bolder"><?php echo t("Flexible")?> <span class="text-green"><?php echo t("Pricing")?></span> <?php echo t("for Every")?> 
  <span class="text-green"><?php echo t("Restaurant")?></span></h2>
  <br/>
  <h6><?php echo t("Transparent pricing. No hidden costs. Advanced features to elevate your business")?>.</h6>
  <br/>
</div>


<br/>

<div class="row justify-content-center pricing-plans q-gutter-md" v-loading="is_loading" >  
  <template v-for="items in data">
  <div class="plans position-relative mb-3 text-center">
     <!-- <div class="icon"><i class="zmdi zmdi-fire"></i></div> -->
     <div class="mt-3 mb-3">
      <h4 class="mb-0">{{ items.title }}</h4>
      <div class="mt-1 mb-2 font-medium">{{ items.billed }}</div>
      <div class="ellipsis-2-lines" v-html="items.description"></div>
     </div>

     <h4 class=" font-weight-bolder">
      <template v-if="items.promo_price_raw>0">
        <span class="text-muted opacity-60"><del>{{items.price}}</del></span> <span>{{ items.promo_price }}</span>
      </template>
      <template v-else>
        {{items.price}}
      </template>
     </h4>    

    <template v-if="plan_details">
    <ul>
      <li>
        <i class="zmdi zmdi-check text-green mr-1"></i>
        <?php echo t("Max Order")?> (<template v-if="items.order_limit==0"><?php echo t("Unlimited")?></template> <template v-else>{{items.order_limit}}</template>)
      </li>
      <li>
        <i class="zmdi zmdi-check text-green mr-1"></i>
        <?php echo t("Max Product")?> (<template v-if="items.item_limit==0"><?php echo t("Unlimited")?></template> <template v-else>{{items.item_limit}}</template>)
      </li>
      <template v-for="(features,features_key) in plan_details">
         <li>          
          <i v-if="items[features_key]=='1'" class="zmdi zmdi-check text-green mr-1"></i>
          <i v-else class="zmdi zmdi-close text-red mr-1"></i>
           {{ features }}
          </li>
      </template>      
    </ul>
    </template>
    
    <div class="mt-3">          
      <a @click="setChoosePlan(items.package_id)" class="btn btn-outline-success btn-block font-medium">
         {{ items.label }}   
      </a>      
    </div>
  </div>  
  </template>
</div>

<br/><br/>

</div> <!--container-->