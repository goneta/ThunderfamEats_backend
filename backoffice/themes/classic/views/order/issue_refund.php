<script type="text/x-template" id="xtemplate_issue_refund">

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
   
   <el-form-item label="<?php echo CommonUtility::safeTranslate('Refund Type')?>" label-position="top"  >
        <el-radio-group v-model="refund_type" size="large" fill="#626aef"  >
            <template v-for="(items,index) in refund_type_list" :key="items">
              <el-radio-button :label="items" :value="index"  ></el-radio-button>
            </template>            
        </el-radio-group>
   </el-form-item>
   
   <el-row :gutter="20">
       <el-col :span="12">
        <el-form-item label="<?php echo CommonUtility::safeTranslate('Order Amount')?>" label-position="top"   >       
        <el-input-number
            v-model="amount"
            :min="1"
            :max="max_refund_amont"
            :precision="2"
            controls-position="right"
            size="large"
            @change="handleChange"
            :disabled="isFullRefund"        
            class="w-100"
        ></el-input-number>        
        <div class="disabled-text font11"><?php echo CommonUtility::safeTranslate('Amount should not exceed')?> {{ order_information ? order_information.pretty_total : '' }}.</div>
        </el-form-item>        
       </el-col>
       <el-col :span="12">
          <el-form-item label="<?php echo CommonUtility::safeTranslate('Payment Type')?>" label-position="top" >       
              <el-select
                v-model="payment_code"
                placeholder="<?php echo CommonUtility::safeTranslate('Select')?>"
                size="large"              
              >
              <el-option label="<?php echo CommonUtility::safeTranslate('Please Select')?>" value="select"></el-option>
              <el-option
                v-for="item in payment_list"
                :key="item.payment_code"
                :label="item.payment_name"
                :value="item.payment_code"
              ></el-option>
              </el-select>
          </el-form-item>
       </el-col>
   </el-row>   

   <el-input
    v-model="reason"    
    :rows="2"
    type="textarea"
    placeholder="<?php echo CommonUtility::safeTranslate('Reason')?>"
   ></el-input>

   <!-- <pre>{{payment_list}}</pre> -->

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