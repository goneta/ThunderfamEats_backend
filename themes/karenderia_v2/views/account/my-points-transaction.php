
<div v-if="!hasData && !loading" class="d-flex justify-content-center mt-4 mb-5">
  <p class="m-0 text-muted"><?php echo t("No data available")?></p>
</div>

<template v-for="datas in getData" >
<div v-for="row_data in datas" class="kmrs-row row m-0 rounded p-2 mb-2" >

 <div class="col d-flex align-items-center ">      
     <p class="m-0">{{row_data.transaction_date}}</p>  
 </div> <!--col-->
 
 <div class="col d-flex align-items-center ">         
  <p class="m-0">{{row_data.transaction_description}}</p>   
 </div> <!--col-->
 
 <div class="col d-flex align-items-center  justify-content-end">     
  <b :class="{'text-danger':row_data.transaction_type=='debit','text-success':row_data.transaction_type=='credit'}">{{row_data.transaction_amount}}</b>   
 </div> <!--col-->
  
</div> <!--kmrs-row-->
</template>


<div v-if="showNext"  class="d-flex justify-content-center mt-4 mb-5" >  
  <el-button type="primary" round      
   size="large"   
   @click="nextPage(page)"
   :loading="loading_next"
  >
    <?php echo t("Load more")?>
  </el-button> 
</div>