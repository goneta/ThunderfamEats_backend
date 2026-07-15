<script type="text/x-template" id="xtemplate_cuisine_list">

<template v-if="loading">
   <el-skeleton animated >
      <template #template>         
           <div class="mb-4 mt-4">
            <el-skeleton-item variant="caption" style="height: 20px;width:100%;"  />
           </div>
           <el-skeleton-item variant="rect" style="height: 70px;width:100%"  />          
      </template>
   </el-skeleton>
</template>
<template v-else>
<div  v-if="hasData" class="swiperOutsideContainer" >

   <div class="mb-4 mt-4">
       <h3 class="font-weight-bold">{{title}}</h3>
   </div>           

   <div class="swiper-container">
      <div class="swiper swiperCuisine" ref="swiperCuisineList" style=" position:static;" >
         <div class="swiper-wrapper">
            <template v-for="items in data">
               <div class="swiper-slide swiperSlide" type="button">    
                  <el-link :href="items.url" class="el-links" :underline="false">
                     <div>
                     <img :src="items.featured_image" 
                     loading="lazy"
                     class="rounded-circle"
                     />
                     </div>
                     <div class="truncate">{{items.cuisine_name}}</div>
                  </el-link>
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
</div>
</template>
</script>