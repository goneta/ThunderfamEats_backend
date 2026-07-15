<script type="text/x-template" id="xtemplate_manage_order">

<el-dialog v-model="modal"
    :title="label.title"
    width="500"		
    id="footer-none-bg"
    :close-on-click-modal="false"
    :close-on-press-escape="false"     
    @open="Onopen"   				
>


<el-form
    label-position="top"        
   >
   <el-form-item :label="label.status" label-position="top"   >
        <el-select
            v-model="status"
            placeholder="<?php echo CommonUtility::safeTranslate("Please select status")?>"
            size="large"                      
            label-position="top"
        >         
            <el-option
                v-for="(items,keys) in status_list"
                :key="items"
                :label="items"
                :value="keys"              
            ></el-option>
        </el-select>
    </el-form-item>
</el-form>

<template #footer>
    <div class="dialog-footer">
      <el-button size="large" @click="modal = false">
        {{ label.close}}
      </el-button>   
      <el-button 
      @click="onSubmit" 
      color="#626aef" 
      size="large"
      :loading="loading"        
      >
          {{label.confirm}}
      </el-button>
    </div>
</template>
  

</el-dialog>
</script>