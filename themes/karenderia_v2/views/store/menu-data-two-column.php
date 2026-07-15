<section v-cloak v-for="(val,index) in menu_data" :id="val.category_uiid" :data-cat_id="val.cat_id">    
    <template v-if="index<=0">        
        <div class="d-flex justify-content-between">
            <div>
               <h5>{{ val.category_name }}</h5>
               <p>{{val.category_description}}</p>    
            </div>
            <div>            
              <div class="d-flex">
                 <div class="mr-2">
                    <el-link @click="changeMenuLayout('list')" :underline="false" >
                        <i class="zmdi zmdi-view-agenda" :class="{ 'text-grey': menu_layout=='column' }" style="font-size:30px;"></i>
                    </el-link>
                </div>
                 <div>
                    <el-link @click="changeMenuLayout('column')" :underline="false" >
                        <i class="zmdi zmdi-view-dashboard" :class="{ 'text-grey': menu_layout=='list' }" style="font-size:30px;"></i>
                    </el-link>
                </div>
              </div>                            
            </div>
        </div>
    </template>
    <template v-else>
        <h5>{{ val.category_name }}</h5>
        <p>{{val.category_description}}</p>    
    </template>    

    <template v-if="menu_layout=='list'">
    <div class="row">
    <div v-for="items in val.items" class="col-lg-6 col-sm-12 mb-3" > 
        <el-card shadow="hover" >
           <div class="d-flex align-items-start align-items-stretch">
              <div class="borderx">         
                  <el-image
                        style="height:100px;max-width:130px;"                        
                        :src="items.url_image"
                        fit="cover"
                        lazy
                    >
                    </el-image>
              </div>
              <div class="p-2 borderx w-100">
                 <div class="d-flex align-items-start flex-column" >                    
                    <div class="d-flex align-items-center mb-1">
                        <h6 class="m-0" >{{items.item_name}}</h6>                        
                    </div>
                    <p class="ellipsis-3-lines mb-1" v-html="items.item_description"></p>       
                                                                                
                    <div v-if="items.dish" class="d-flex">
                    <template v-for="dish_id in items.dish" ::key="dish_id">                        
                       <div v-if="dish[dish_id]" class="mr-1">
                        <el-avatar fit="contain" :size="30" :src="dish[dish_id].url_image" style="background-color:#f9a825;" ></el-avatar>
                       </div>
                    </template>
                    </div>
                    
                 </div>
                 <!-- d-flex -->                 
              </div>
              <!-- p2 -->
           </div>

           <template v-if="items.promo_data?.message && itemAvailable(items.item_id,val.cat_id)">
             <el-tag effect="plain" :type="isEligible(items) ? 'success' : 'primary'" class="mb-1 mt-1" round  >
                {{ getPromoMessage(items) }}
            </el-tag>
           </template>

           <div class="row align-items-center">
              <div class="col-4" v-if="items.total_allergens>0">                  
                    <el-button                                                
                        type="plain"
                        link
                        @click="showAllergens(items.item_id)"
                        >
                        <i class="zmdi zmdi-info-outline font25"></i>
                    </el-button>                  
              </div>
              <div class="col">
                  <div class="row align-items-center">
                      <div class="col-6 text-center">                        
                        <template v-if="items.lowest_price_raw>0">
                            <h5 class="m-0" :class="{ 'text-success' : isEligible(items) }">
                                {{ isEligible(items) ? '<?php echo t('Free!')?>' : items.lowest_price }}
                            </h5>
                        </template>            
                        <template v-else>
                            <template v-if="items.price[0]">                       
                            <template v-if="items.price[0].discount>0">
                                <h5 class="m-0 text-grey"><del>{{ items.price[0].pretty_price }}</del></h5>
                                <h5 class="m-0">{{ items.price[0].pretty_price_after_discount }}</h5>
                            </template>
                            <template v-else>
                                <h5 class="m-0">{{ items.price[0].pretty_price }}</h5>
                            </template>
                            </template>
                        </template>

                      </div>
                      <div class="col-6 text-right">
                            <template v-if="items.total_addon <=0 && items.total_meta <=0 ">
                                <div class="d-none btn-group btn-group-toggle input-group-small" data-toggle="buttons">
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
                            </template>
                               
                            <template v-if="itemAvailable(items.item_id,val.cat_id)">
                                <template v-if="items.total_addon <=0 && items.total_meta <=1 && items.price.length<=1 ">

                                    <template v-if=" items.qty>0 " >
                                        <div class="position-relative quantity-wrapper">
                                        <div class="quantity-parent">
                                            <div class="quantity d-flex align-items-center justify-content-between m-auto">                                            
                                            <el-button @click.stop="updateInlineQtyBefore('less',items,val.cat_id)" color="#3ecf8e" class="white-color" circle>
                                               <i class="zmdi zmdi-minus font20"></i>
                                            </el-button>
                                            <div class="qty">{{ items.qty }}</div>                                            
                                            <el-button @click.stop="updateInlineQtyBefore('add',items,val.cat_id)" color="#3ecf8e" class="white-color" circle>
                                               <i class="zmdi zmdi-plus font20"></i>
                                            </el-button>
                                            </div>  
                                        </div> <!--quantity-parent-->		
                                        </div>
                                    </template>
                                    <template v-else=" items<=0 " >  
                                        <?php if($disabled_inline_addtocart):?>
                                            <el-button @click="viewItemBefore({cat_id:val.cat_id,item_uuid:items.item_uuid})" color="#3ecf8e" class="white-color" circle>
                                              <i class="zmdi zmdi-plus font20"></i>
                                            </el-button>
                                        <?php else :?>                                                                                        
                                            <el-button @click="updateInlineQtyBefore('add',items,val.cat_id)"  color="#3ecf8e" class="white-color" circle>
                                               <i class="zmdi zmdi-plus font20"></i>
                                            </el-button>
                                        <?php endif;?> 
                                    </template>

                                </template>
                                <template v-else>                                                                  
                                    <el-button :disabled="!isEligibleItem(items)" :color="isEligibleItem(items) ? '#3ecf8e' :'#b2b2b2' " @click="viewItemBefore({cat_id:val.cat_id,item_uuid:items.item_uuid})" class="white-color" circle >                                      
                                      <i class="zmdi zmdi-plus font20"></i>
                                    </el-button>
                                </template>
                            </template>
                            <template v-else>
                                  <el-button disabled color="#b2b2b2" class="white-color" circle>
                                     <i class="zmdi zmdi-plus font20"></i>
                                  </el-button>
                            </template>
                        

                      </div>
                  </div>
              </div>
           </div>
           <!-- row -->

        </el-card>
    </div>
    <!-- col -->
    </div>
    <!-- row -->
    </template>
    
    <template v-else>
        <div class="row">
           <div v-for="items in val.items" class="col-lg-4 col-sm-12 mb-3" > 
           <el-card shadow="hover" :body-style="{ padding: '0px' }" >
                <div class="position-relative">
                    <el-image
                        style="height: 130px;"
                        class="w-100"
                        :src="items.url_image"
                        fit="cover"
                        lazy
                    >
                    </el-image>
                    <div v-if="items.dish" class="w-100 d-flex align-items-center justify-content-end" style="position:absolute;bottom:5px;">                        
                        <template v-for="dish_id in items.dish" ::key="dish_id">                        
                            <div v-if="dish[dish_id]" class="mr-1">
                                <el-avatar fit="contain" :size="30" :src="dish[dish_id].url_image" style="background-color:#f9a825;" ></el-avatar>
                            </div>
                        </template>                        
                    </div>
                </div>

                <div class="p-2">                   
                   <div class="d-flex align-items-center mb-1">
                        <h6 class="m-0 text-truncate">{{items.item_name}}</h6>
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

                   <div v-if="items.promo_data?.message && itemAvailable(items.item_id,val.cat_id)">
                     <el-tooltip
                        class="box-item"
                        effect="dark"
                        :content="getPromoMessage(items)"
                        placement="top-start"
                    >
                        <el-button circle >
                            <i class="zmdi zmdi-card-giftcard" style="font-size:16px;"></i>
                        </el-button>
                    </el-tooltip>
                   </div>

                    <div class="d-flex justify-content-between align-items-center w-100">

                       <template v-if="items.lowest_price_raw>0">
                            <h5 class="m-0" :class="{ 'text-success' : isEligible(items) }">
                                {{ isEligible(items) ? '<?php echo t('Free!')?>' : items.lowest_price }}
                            </h5>
                        </template>            
                        <template v-else>
                            <template v-if="items.price[0]">                       
                                <template v-if="items.price[0].discount>0">
                                    <h5 class="m-0 text-grey"><del>{{ items.price[0].pretty_price }}</del></h5>
                                    <h5 class="m-0">{{ items.price[0].pretty_price_after_discount }}</h5>
                                </template>
                                <template v-else>
                                    <h5 class="m-0">{{ items.price[0].pretty_price }}</h5>
                                </template>
                            </template>
                        </template>

                        <template v-if="items.total_addon <=0 && items.total_meta <=0 ">
                           <div class="d-none btn-group btn-group-toggle input-group-small" data-toggle="buttons">
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
                        </template>

                        <div>
                            
                           <template v-if="items.total_addon <=0 && items.total_meta <=0 ">
                            <div class="d-none btn-group btn-group-toggle input-group-small" data-toggle="buttons">
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
                            </template>

                            <template v-if="itemAvailable(items.item_id,val.cat_id)">
                                <template v-if="items.total_addon <=0 && items.total_meta <=1 && items.price.length<=1 ">

                                    <template v-if=" items.qty>0 " >
                                        <div class="position-relative quantity-wrapper">
                                        <div class="quantity-parent">
                                            <div class="quantity d-flex align-items-center justify-content-between m-auto">                                            
                                            <el-button @click.stop="updateInlineQtyBefore('less',items,val.cat_id)" color="#3ecf8e" class="white-color" circle>
                                               <i class="zmdi zmdi-minus font20"></i>
                                            </el-button>
                                            <div class="qty">{{ items.qty }}</div>                                            
                                            <el-button @click.stop="updateInlineQtyBefore('add',items,val.cat_id)" color="#3ecf8e" class="white-color" circle>
                                               <i class="zmdi zmdi-plus font20"></i>
                                            </el-button>
                                            </div>  
                                        </div> <!--quantity-parent-->		
                                        </div>
                                    </template>
                                    <template v-else=" items<=0 " >  
                                        <?php if($disabled_inline_addtocart):?>
                                            <el-button @click="viewItemBefore({cat_id:val.cat_id,item_uuid:items.item_uuid})" color="#3ecf8e" class="white-color" circle>
                                               <i class="zmdi zmdi-plus font20"></i>
                                            </el-button>
                                        <?php else :?>
                                            <el-button @click="updateInlineQtyBefore('add',items,val.cat_id)"  color="#3ecf8e" class="white-color" circle>
                                               <i class="zmdi zmdi-plus font20"></i>
                                            </el-button>
                                        <?php endif;?> 
                                    </template>

                                </template>
                                <template v-else>
                                    <el-button :disabled="!isEligibleItem(items)" :color="isEligibleItem(items) ? '#3ecf8e' :'#b2b2b2' " @click="viewItemBefore({cat_id:val.cat_id,item_uuid:items.item_uuid})"  class="white-color" circle>
                                       <i class="zmdi zmdi-plus font20"></i>
                                    </el-button>
                                </template>
                            </template>
                            <template v-else>
                                  <el-button disabled color="#b2b2b2" class="white-color" circle>
                                     <i class="zmdi zmdi-plus font20"></i>
                                  </el-button>
                            </template>

                        </div>
                   </div>

                </div>
                <!-- p2 -->

            </el-card>
           </div>
           <!-- col -->
        </div>
        <!-- row -->
    </template>


</section>