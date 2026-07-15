
<div v-if="!hasData && !loading1" class="d-flex justify-content-center mt-4 mb-5">
  <p class="m-0 text-muted"><?php echo t("No data available")?></p>
</div>

<template v-for="datas in getData1" >
<div v-for="row_data in datas" class="kmrs-row row m-0 rounded p-2 mb-2" >

 <div class="col d-flex align-items-center ">      
     <p class="m-0">{{row_data.restaurant_name}}</p>  
 </div> <!--col-->
  
 <div class="col d-flex align-items-center  justify-content-end">     
  <b :class="{'text-danger':row_data.total_earning<=0,'text-success':row_data.total_earning>0}" >{{row_data.total_earning}}</b>   
 </div> <!--col-->
  
</div> <!--kmrs-row-->
</template>

<div v-if="showNext1"  class="d-flex justify-content-center mt-4 mb-5" >
  <el-button type="primary" round      
   size="large"   
   @click="nextPage2(page2)"
   :loading="loading_next1"
  >
    <?php echo t("Load more")?>
  </el-button> 
</div>