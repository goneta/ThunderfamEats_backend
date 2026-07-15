<script type="text/x-template" id="xtemplate_restaurant_list">
<template v-if="loading && page<=1">

    <div class="mb-4 mt-4">
    <el-skeleton animated >
        <template #template>
            <div><el-skeleton-item variant="rect" style="height: 15px;width:180px;"></el-skeleton-item></div>            
        </template>
    </el-skeleton>

    <div class="flex-content">
        <template v-for="items in <?php echo Yii::app()->params['isMobile']?2:10; ?>">
        <div class="flex-content-item">
            <el-skeleton animated >
                <template #template>
                    <el-skeleton-item variant="rect" style="height: 180px;width:180px;"  />                           
                </template>
            </el-skeleton>
        </div>
        </template>
    </div>
    </div> 
</template>


<template v-if="hasData">
   <div class="mb-4 mt-4">
       <h3 class="font-weight-bold">{{title}}</h3>
       <h5 class="font-weight-normal">
         <template v-if="is_filters">
            {{total_pretty}}
         </template>
         <template v-else>
            {{title}}
         </template>
       </h5>
   </div> 
</template>

<div class="row no-gutters">
   <template v-for="items in data">
      <div class="col-lg-3 col-md-3 col-sm-6 p-1 mb-3 ">
         <div class="rounded-box" type="button"  @click="onBannerClick(items)" >
             <div class="position-relative"> 
                <div class="position-absolute p-2" style="z-index: 9;">
                     <template v-for="promos_items in items.vouchers">
                        <div class="mb-1"><el-tag type="danger">{{promos_items.title}}</el-tag></div>
                     </template>
                     <template v-for="promos_items in items.promos">
                        <div class="mb-1"><el-tag type="warning">{{promos_items.title}}</el-tag></div>
                     </template>
                </div>
                <el-image :src="items.logo" lazy ></el-image>                
             </div>          
             <div class="p-2">
                <div class="font-weight-bold mb-2 truncate">{{ items.restaurant_name }}</div>
                <div class="font-weight-light truncate mb-1">{{ items.cuisines }}</div>
                <div class="row align-items-center no-gutters">
                     <div class="col">
                        <div class="d-flex">
                           <div class="mr-1"><i class="fas fa-star gold-color"></i></div>
                           <div>{{items.ratings}} ({{items.review_count}}+ Ratings)</div>
                        </div>                        
                     </div>
                     <div class="col-5">
                        <div class="d-flex">
                           <div class="mr-2"><i class="zmdi zmdi-time"></i></div>
                           <div class="truncate">{{items.estimated_time_min}}</div>
                        </div>                        
                     </div>   
                </div> 
                <!-- row -->
             </div>            
         </div>
         <!-- rounded -->
     </div>
     <!-- col -->
   </template>
</div>
<!-- row -->

<infinite-loading  @infinite="getData" distance="50" :identifier="identifier" >
   <template #spinner>
     <div class="p-2 justify-center">
        <div class="typing_loader"></div>
     </div>
   </template>
   <template #complete>      
     <template v-if="hasData">
     <div class="p-2 justify-center d-none">
        No more items to display
     </div>
     </template>
     <div v-else class="mb-4 mt-4">       
         <h3 class="font-weight-bold">No Restaurants found</h3>
         <p class="text-muted">We couldn't find any results with the current filters. Try changing the filters or reset them to explore more options.</p>
      </div>     
   </template>
</infinite-loading> 

</script>