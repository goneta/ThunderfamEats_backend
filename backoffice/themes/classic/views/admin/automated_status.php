<h6 class="mb-1 mt-1"><?php echo t("Automated Status Updates")?></h6>
<p><?php echo t("allow you to define actions that automatically change the status of an order after a certain amount of time.")?> 
<br/>    
<?php echo t("For example, you can set an order to be automatically accepted 10 minutes after it's placed, or mark it as delivered 1 hour after it's accepted")?>
<br/>
<?php echo t("This feature helps streamline order management by reducing manual intervention and ensuring timely updates for each order.")?>
</p>

<div id="app-automated-status" v-cloak>
<div v-loading="loading">

<div class="text-right mb-3">
   <el-button plain  type="primary" @click="addRow" >
      <?php echo t("Add Row")?>
   </el-button>
</div>

<el-row :gutter="10" class="mb-1 font-weight-bold">
   <el-col :span="7">  
      <?php echo t("From Status")?>
   </el-col>
   <el-col :span="7">  
      <?php echo t("Update To")?>
   </el-col>
   <el-col :span="7">  
    <?php echo t("Time Update after (minutes)")?>  
     <el-popover
         placement="bottom"                        
         :width="200"
         trigger="click"
         content="<?php echo t("Eg. 1 hour = 60 for 1 hour and 10 minutes = 70")?>"
      >
         <template #reference>
            <el-button circle size="small" link ><i class="zmdi zmdi-info-outline font16"></i></el-button>
         </template>
      </el-popover>
   </el-col>
   <el-col :span="2">      
   </el-col>
</el-row>

<template v-for="(items,index) in data">
<el-row :gutter="10" class="mb-2">
   <el-col  :xs="24" :span="7">  
      <el-select
        v-model="items.from"
        placeholder="<?php echo t("Please select")?>"
        size="large"      
      >      
      <el-option
        v-for="item in status"
        :key="item.value"
        :label="item.label"
        :value="item.value"
      ></el-option>
      </el-select>
   </el-col>
   <el-col :xs="24" :span="7">
      <el-select
          v-model="items.to"
          placeholder="<?php echo t("Please select")?>"
          size="large"      
      >      
      <el-option
        v-for="item in status"
        :key="item.value"
        :label="item.label"
        :value="item.value"
      ></el-option>
      </el-select>
   </el-col>
   <el-col :xs="24" :span="7">            
        <el-input-number
            v-model="items.time"
            :min="1"
            :max="999999999"
            controls-position="right"
            size="large"            
         ></el-input-number>
   </el-col>
   <el-col :xs="24" :span="2">          
      <el-button type="danger" size="large" @click="removeRow(index)" class="w-100">
         <i class="fas fa-minus"></i>
      </el-button>
   </el-col>
</el-row>
</template>



<div class="mt-3" >
<el-button @click="onSubmit" :loading="loading_submit" type="success" class="w-100" size="large">
   <?php echo t("Save")?>
</el-button>
</div>

</div>
</div>
<!-- app-automated-status -->