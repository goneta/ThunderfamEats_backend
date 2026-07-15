
<section v-cloak v-for="val in menu_data" :id="val.category_uiid" :data-cat_id="val.cat_id">
    <h5>{{ val.category_name }}</h5>
    <p>{{val.category_description}}</p>
    
    <div class="list-item-rows hover01" v-for="items in val.items" >		     
    <div class="row m-0">

    
    <div class="col-9 p-0">        
        <div class="row m-0 p-0">
            <div class="col-lg-8 col-md-8 d-flex align-items-center fixed-height">
                <div class="center">                  
                <div class="d-flex align-items-center mb-1">
                   <h6 class="m-0">{{items.item_name}}</h6>
                   <div v-if="items.total_allergens>0" class="ml-2">                       
                       <el-button                                                
                        type="plain"
                        link
                        @click="showAllergens(items.item_id)"
                        >
                        <i class="zmdi zmdi-info-outline font20"></i>
                        </el-button>
                   </div>
                </div>
                <p class="ellipsis-2-lines" v-html="items.item_description"></p>

                <template v-if="items.promo_data?.message && itemAvailable(items.item_id,val.cat_id)">
                    <el-tag effect="plain" :type="isEligible(items) ? 'success' : 'primary'" class="mb-1 mt-1" round  >
                        {{ getPromoMessage(items) }}
                    </el-tag>
                </template>

                <div v-if="items.dish" class="d-flex">
                    <template v-for="dish_id in items.dish" ::key="dish_id">                        
                        <div v-if="dish[dish_id]" class="mr-1">
                        <el-avatar fit="contain" :size="30" :src="dish[dish_id].url_image" style="background-color:#f9a825;" ></el-avatar>
                        </div>
                    </template>
                </div> 
                
                <template v-if="items.total_addon <=0 && items.total_meta <=0 ">
                
                    <div class="btn-group btn-group-toggle input-group-small" data-toggle="buttons">
                    <label v-for="(price, index) in items.price" class="btn" :class="{ active: index==0 }">	                           
                        <input :value="price.item_size_id" name="size" id="size" type="radio" :class="'item_size_id_'+ items.item_uuid"  > 
                        <template v-if="price.discount <=0">
                        {{price.size_name}} {{price.pretty_price}}
                        </template><!-- v-if-->
                        <template v-else>
                        {{price.size_name}} <del>{{price.pretty_price}}</del> {{price.pretty_price_after_discount}}
                        </template> <!--v-else-->
                    </label>
                    </div> <!--btn-group-->
                    
                </template> <!--v-if-->
                
                <template v-else>                                
                    <p class="bold m-0 prices"  :class="{ 'text-success' : isEligible(items) }" >            
                    <template v-if="items.lowest_price_raw>0">                      
                      {{ isEligible(items) ? '<?php echo t('Free!')?>' : items.lowest_price_label }}
                    </template>            
                    <template v-else>
                        <template  v-for="(price, index) in items.price">
                            <template v-if="price.discount <=0">
                            <span class="mr-2">{{price.size_name}} {{price.pretty_price}}</span>
                            </template><!-- v-if-->
                            <template v-else>
                            <span class="mr-2">{{price.size_name}} <del>{{price.pretty_price}}</del> {{price.pretty_price_after_discount}}</span>
                            </template> <!--v-else-->
                        </template > <!--v-for-->
                    </template>
                    </p>
                </template> <!--v-else-->
                
                </div> <!--center-->
                
            </div> <!--col-->
                                                          
        </div> <!--row-->
                
    </div> <!--col-->

    <div class="col-3 p-0">	   
       <div class="item-image-preview position-relative">        
        <el-image
            style="width: 100%; height: 140px"
            :src="items.url_image"
            fit="cover"
            lazy
        >
        </el-image>
        <template  v-if="itemAvailable(items.item_id,val.cat_id)">
            <div class="position-absolute button-fixed-buttom">
                <template v-if="items.total_addon <=0 && items.total_meta <=0 && items.price.length<=1 ">		                            	                 
                    <template v-if=" items.qty>0"> 
                        <div class="quantity-parent">
                            <div class="quantity d-flex justify-content-between m-auto">
                            <div><a href="javascript:;" @click="updateInlineQtyBefore('less',items,val.cat_id)" class="rounded-pill qty-btn" data-id="less"><i class="zmdi zmdi-minus"></i></a></div>
                            <div class="qty">{{ items.qty }}</div>
                            <div><a href="javascript:;" @click="updateInlineQtyBefore('add',items,val.cat_id)" class="rounded-pill qty-btn" data-id="plus"><i class="zmdi zmdi-plus"></i></a></div>
                            </div>  
                        </div> <!--quantity-parent-->		
                    </template>
                    <template v-else="items<=0" > 
                        <a href="javascript:;" @click="updateInlineQtyBefore('add',items,val.cat_id)" class="btn btn-grey quantity-add-cart">								 
                           <i class="zmdi zmdi-plus"></i>
                        </a>	                
                    </template>
                </template>
                <template v-else>
                    <!-- <a href="javascript:;" class="btn btn-grey xget-item-details" 
                    @click="viewItemBefore({cat_id:val.cat_id,item_uuid:items.item_uuid})">					   
                       <i class="zmdi zmdi-plus"></i>
                    </a> -->
                    <el-button size="large" :disabled="!isEligibleItem(items)" :color="isEligibleItem(items) ? '#3ecf8e' :'#b2b2b2' " @click="viewItemBefore({cat_id:val.cat_id,item_uuid:items.item_uuid})" class="white-color" >                                      
                      <i class="zmdi zmdi-plus"></i>
                    </el-button>             
                </template>
            </div>
            <!-- fixed -->
        </template>
        <template v-else>
           <div class="position-absolute button-fixed-buttom">
               <div class="badge badge-light"><?php echo t("Not available")?></div>
            </div>
        </template>
       </div>
    </div> <!--col-->
    
    
    </div> <!--row-->
    </div><!-- list-item-rows-->		     
</section>