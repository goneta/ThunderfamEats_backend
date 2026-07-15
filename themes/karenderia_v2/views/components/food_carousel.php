
<script type="text/x-template" id="xtemplate_food_carousel">

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
   <div class="row">
      <div class="col-12 pl-0 pr-0">
        <h3>{{title}}</h3>  
        <p>{{sub_title}}</p>
      </div>
   </div>
</template>
<template v-else-if="!loading">
   <div class="d-flex align-items-center justify-content-center" style="min-height:200px;">
      <p class="text-muted"><?php echo t("No data available")?></p>
   </div>
</template>    

<div v-if="hasDataFood && !loading" class="row position-relative">
    <div ref="owl_item_slide" class="owl-carousel owl-theme">
        <div v-for="items in getFood">
           <el-card shadow="hover" :body-style="{ padding: '0px' }" >
                <el-image
                    style="width: 100%; height: 130px"
                :src="items.url_image"
                :fit="cover"                 
                ></el-image>

                <div class="p-2">
                    <div class="d-flex align-items-center mb-1">
                        <h6 class="m-0 text-truncate">{{items.item_name}}</h6>
                    </div>
                    <p class="d-inline-block text-truncate" style="max-width: 200px;" v-html="items.item_description"></p>

                    <div class="d-flex justify-content-between align-items-center w-100">
                        <template v-if="items.price[0]">                       
                            <template v-if="items.price[0].discount>0">
                                <h5 class="m-0 text-grey"><del>{{ items.price[0].pretty_price }}</del></h5>
                                <h5 class="m-0">{{ items.price[0].pretty_price_after_discount }}</h5>
                            </template>
                            <template v-else>
                                <h5 class="m-0">{{ items.price[0].pretty_price }}</h5>
                            </template>
                        </template>    
                                                        
                        <template v-if="itemAvailable(items.item_id,items.cat_id)">
                            <el-button color="#3ecf8e" class="white-color" @click="viewItem(items)" >
                            <?php echo t("Add")?>
                            </el-button>
                        </template>
                        <template v-else>
                            <el-button disabled color="#b2b2b2" class="white-color">
                                <?php echo t("Not available")?>
                            </el-button>
                        </template>
                    </div>          

                </div>
            </el-card>
        </div>
    </div>
    
    <div class="slider_nav">
        <button class="btn btn-green-circle next-slide" ref="owl_next">
           <i class="zmdi zmdi-arrow-left"></i>
        </button>
        <button class="btn btn-green-circle prev-slide" ref="owl_previous">
           <i class="zmdi zmdi-arrow-right"></i>
        </button>
    </div>

</div>
</script>