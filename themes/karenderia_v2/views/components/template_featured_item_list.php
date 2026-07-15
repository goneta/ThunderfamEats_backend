<script type="text/x-template" id="xtemplate_featured_item_list">       


<div v-if="hasFeaturedItems" class="swiperOutsideContainer">
    <div class="mb-4 mt-4">
        <h3 class="font-weight-bold">{{title}}</h3>
    </div>               

    <div class="swiper-container">
       <div class="swiper swiperResto" ref="refSwiperList" style=" position:static;" >
           <div class="swiper-wrapper">
              <template v-for="items in data" :key="items">
                  <div class="swiper-slide swiperSlide" type="button" @click.stop="this.$emit('viewItem',items)" >   
                      <div class="position-relative">
                      <img :src="items.url_image" lazy  >
                      </div>
                      <div class="p-2">
                        <div class="font-weight-bold mb-2 truncate">{{ items.item_name }}</div>
                        <div class="font-weight-light text-truncate-lines-2 mb-1" v-html="items.item_description"></div>

                        <div v-if="items.is_promo_free_item">
                           <el-tag effect="plain" :type="items?.is_eligible ? 'success' : 'primary'" class="mb-1 mt-1" round >
                               {{ items?.promo?.message || '' }}
                           </el-tag>
                        </div>

                        <div class="row align-items-center no-gutters">
                           <div class="col">
                              <div class="d-flex flex-row align-items-center">                                 
                                 <h4 class="featured-item-price m-0">                                    
                                    {{items.lowest_price}}
                                 </h4>
                              </div>
                              
                           </div>
                           <div class="col text-right">                                                         
                              <el-button :disabled="!items?.is_eligible" @click.stop="this.$emit('viewItem',items)" color="#3ecf8e" style="color:#fff;" round >
                                 <?php echo CommonUtility::safeTranslate("Add")?>
                              </el-button> 
                           </div>
                        </div>
                      </div>
                  </div>
              </template>
           </div>

           <div class="swiper-button-next">
               <el-button circle>
                  <i class="fas fa-arrow-right"></i>
               </el-button>
            </div>
            <div class="swiper-button-prev">
               <el-button circle>
                  <i class="fas fa-arrow-left"></i>
               </el-button>
            </div>

       </div>
    </div> 
    <!-- swiper-container -->

</div>
<!-- swiperOutsideContainer -->

</script>