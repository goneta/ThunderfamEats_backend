
<DIV id="vue-search" v-cloak>
<div class="search-banner">    
    <div class="container">
        <div class="sub-header d-flex align-items-center justify-content-center" style="height:230px;">
            <div class="">
               <h2 class="text-center mb-3"><?php echo t("Find Your Perfect Bite") ?></h2>
               <components-search-food 
               ref="search_food"
               :label="{		    
                    search: '<?php echo CJavaScript::quote(t("Search foods and restaurants"))?>',                     
                    clear: '<?php echo CJavaScript::quote(t("Clear"))?>',  
                    go: '<?php echo CJavaScript::quote(t("Search"))?>',  
                }"	    
                @after-suggestion="afterSuggestion"
               >
               </components-search-food>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5 pt-4">    
    <components-search-food-result
    ref="search_food_result"
    :q="searchString"
    @handle-view="handleView"
    @set-search="setSearch"
    >
    </components-search-food-result>

    <template v-if="!getisSearch">
        <components-food-list
        ref="food_list"
        meta_name="best_seller"
        sub_title="<?php echo t("Looking for Best Sellers food near your area?")?>"
        @handle-view="handleView"
        >
        </components-food-list>

        <div class="pt-4">
            <components-food-list
            ref="food_list_recommended"
            meta_name="recommended"
            sub_title="<?php echo t("See our recommended food for today")?>"
            @handle-view="handleView"
            >
            </components-food-list>
        </div>
    </template>

</div> 
<!-- container -->

<components-item-details
ref="item_details"
>
</components-item-details>
</DIV>


<script type="text/x-template" id="xtemplate_search_food_result">

<el-tabs v-if="hasQ" v-model="tabs" class="demo-tabs" @tab-click="tabClick">
   <el-tab-pane label="<?php echo t("Foods")?>" name="searchFood" >
   
   <div class="row" v-if="loading">
   <div v-for="items in 4" class="col-lg-3 col-sm-12 mb-3" > 
       <el-skeleton animated >
        <template #template>
        <el-skeleton-item variant="image" style="min-height: 130px;max-height: 130px;" />
        <div style="padding: 14px">
            <el-skeleton-item variant="p" style="width: 50%" />
            <div
            style="
                display: flex;
                align-items: center;
                justify-items: space-between;
            "
            >
            <el-skeleton-item variant="text" style="margin-right: 16px" />
            <el-skeleton-item variant="text" style="width: 30%" />
            </div>
        </div>
        </template>
       </el-skeleton>
    </div>
    </div>
       

    <template v-if="hasDataFood && !loading">
       <h3>{{getFoodCount}} <?php echo t("foods")?></h3>
    </template>
    <template v-else>
        <template v-if="hasQ && !loading">
            <div class="d-flex align-items-center justify-content-center" style="min-height:200px;">
               <p class="text-muted"><?php echo t("No data available")?></p>
            </div>
        </template>
    </template>    
        
    <div v-if="hasDataFood && !loading" class="row">
       <components-item-list 
       :data="getFood"
       :items_not_available="items_not_available"
       :category_not_available="category_not_available"
       @handle-view="handleView"
       ></components-item-list>
    </div>    

   </el-tab-pane>
   <el-tab-pane label="<?php echo t("Restaurants")?>" name="searchRestaurant">
    
    <div class="row" v-if="loading">
    <div v-for="items in 4" class="col-lg-3 col-sm-12 mb-3" > 
        <el-skeleton animated >
            <template #template>
            <el-skeleton-item variant="image" style="min-height: 130px;max-height: 130px;" />
            <div style="padding: 14px">
                <el-skeleton-item variant="p" style="width: 50%" />
                <div
                style="
                    display: flex;
                    align-items: center;
                    justify-items: space-between;
                "
                >
                <el-skeleton-item variant="text" style="margin-right: 16px" />
                <el-skeleton-item variant="text" style="width: 30%" />
                </div>
            </div>
            </template>
        </el-skeleton>
        </div>
    </div>

    <template v-if="hasMerchant && !loading">
       <h3>{{getMerchantCount}} <?php echo t("restaurants")?></h3>
    </template>
    <template v-else>
        <template v-if="hasQ && !loading">
            <div class="d-flex align-items-center justify-content-center" style="min-height:200px;">
               <p class="text-muted"><?php echo t("No data available")?></p>
            </div>
        </template>
    </template>
    
    <div v-if="hasMerchant && !loading" class="row">
        <div v-for="items in getMerchant" class="col-lg-3 col-sm-12 mb-3" >            
           <el-link :underline="false" :href="items.restaurant_url">
           <el-card shadow="hover" :body-style="{ padding: '0px' }" >
               <el-image  
                style="min-height: 130px;max-height: 130px; height:130px !important;"
                class="w-100 h-100"
                :src="items.url_logo"
                fit="cover"
                lazy
                >
                </el-image>

                <div class="p-2">
                    <div class="d-flex align-items-center mb-1">
                        <h6 class="m-0 text-truncate">{{items.restaurant_name}}</h6>
                    </div>                    

                    <p class="d-inline-block text-truncate-lines" style="max-width: 200px;">
                      <template v-for="cusine_id in items.cuisine_group">
                        <template v-if="cuisine[cusine_id]">
                            <span class="mr-1">{{cuisine[cusine_id].name}},</span>
                        </template>
                      </template>
                    </p>

                    <div class="d-flex justify-content-between align-items-center w-100">
                        <div></div>
                        <div class="font13">{{items.distance_pretty}}</div>
                    </div>
                    
                </div>
                <!-- p-2 -->
           </el-card>         
           </el-link>  
        </div>
    </div>    
    
   </el-tab-pane>

</el-tabs>
</script>


<?php 
$this->renderPartial("//components/item-details",[
    'is_mobile'=>Yii::app()->params['isMobile']
]);
$this->renderPartial("//components/items-list");

$this->renderPartial("//components/food_carousel");
?>