<script type="text/x-template" id="xtemplate_wallet_transaction">

<el-skeleton v-if="loading" :rows="10" animated />

<div v-if="!has_data && !loading" class="d-flex justify-content-center align-items-center" style="min-height:200px;">
  <p class="m-0 text-muted"><?php echo t("No data available")?></p>
</div>

<template v-for="datas in data" >
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


</script>