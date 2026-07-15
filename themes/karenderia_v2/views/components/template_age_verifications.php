<script type="text/x-template" id="xtemplate_age_verifications">
<el-dialog 
v-model="modal" 
width="400" 
id="footer-none-bg1"	
:close-on-click-modal="false"
:close-on-press-escape="false"        
:show-close	="false"
align-center
>

<div>
<p class="font14">
    {{label.title}}<br/>{{label.subtitle}}
</p>
</div>

<template #footer>    
   <div class="dialog-footer" style="text-align:left;" > 
   <el-row class="w-100">
     <el-col :span="12" class="text-center">
        <el-button color="#626aef" size="large" @click="ConfirmAge" >
            {{ label.over }}
        </el-button>
     </el-col>
     <el-col :span="12" class="text-center" >
        <el-button color="#3da3b2" size="large" style="color:#fff;" @click="Cancel" >
        {{ label.under }}
        </el-button>
     </el-col>
   </el-row>   
   </div>
</template>

</el-dialog>
</script>