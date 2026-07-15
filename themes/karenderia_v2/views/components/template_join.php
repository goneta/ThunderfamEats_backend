<script type="text/x-template" id="xtemplate_join">          
<template v-if="loading">
   <el-skeleton animated >
      <template #template>        
         <div class="mb-4 mt-4">
            <el-skeleton-item variant="caption" style="height: 20px;width:100%;"  />
         </div>
         <el-skeleton-item variant="rect" style="height: 180px;width:100%;"  />                           
      </template>
   </el-skeleton>
</template>

<template v-else >   
<div v-if="hasData" class="swiperOutsideContainer" >
   
   <div class="text-center mt-4 mb-4">
        <h3 class=""><?php echo t("Join")?> <span class="warm-pink font-weight-bold"><?php echo $website_title;?></span></h3>
    </div>
   
   <div class="swiper-container">
         <div class="swiper swiperRestox" ref="refSwiperList" style=" position:static;" >
            <div class="swiper-wrapper join-sections">
                  <template v-for="items in data">
                     <div class="swiper-slide swiperSlide" >
                        <img :src="items.thumbnail" 
                        loading="lazy"                                                      
                        ></img>
                        <div class="content text-center">                        
                          <h5 class="font-weight-bold truncate">{{items.title}}</h5>
                          <p>{{items.content}}</p>

                          <el-button 
                            type="success"  
                            tag="a" 
                            size="large" 
                            class="w-100" round plain  
                            :href="items.url" 
                            >
                            {{items.button_caption}}
                            </el-button>

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
</template>
</script>