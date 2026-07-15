<DIV id="vue-my-points" v-cloak> 

<el-skeleton animated :loading="loading" >
<template #template>
   
    <div class="mt-4 mb-4">
       <div><el-skeleton-item style="width: 100%;" variant="button" /></div>
       <div><el-skeleton-item style="width: 100%;" variant="text" /></div>
    </div>
    <el-skeleton variant="p" :rows="12" />
</template>
<template #default>

<div class="card p-3 mb-3"  v-if="!is_loading" >
 <div class="rounded p-3 grey-bg" >
  <div class="row no-gutters align-items-center">
    <div class="col-md-2 d-none d-lg-block">
       <div class="header_icon _icons points d-flex align-items-center justify-content-center">         
       </div>
    </div>
    
    <div class="col-md-6">             
        <h5><component-available-points></component-available-points></h5>
        <p class="m-0"><?php echo t("Available Points")?></p>
    </div>      
    
  </div>
 </div>
</div> <!--card -->

<el-tabs v-model="tab" >
    <el-tab-pane label="<?php echo t("Transactions")?>" name="transaction">
      <?php $this->renderPartial('//account/my-points-transaction')?>
    </el-tab-pane>
    <el-tab-pane label="<?php echo t("Points earn by merchant")?>" name="by_merchant">
       <?php $this->renderPartial('//account/my-points-merchant')?>
    </el-tab-pane>    
</el-tabs>

<div><el-backtop /></div>

</template>
</el-skeleton>

</DIV>
<!-- vue- -->