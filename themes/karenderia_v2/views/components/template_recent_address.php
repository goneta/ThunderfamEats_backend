
<script type="text/x-template" id="xtemplate_recent_address">       
<el-dialog
    v-model="modal"    
    width="500"            
    title="<?php echo t("Choose your location")?>"
  >


  <template v-if="loading">
        <el-skeleton animated  >
        <template #template>
            <el-skeleton-item variant="rect"  style="height:70px;" ></el-skeleton-item>
        </template>
        </el-skeleton>
    </template>
    <template v-else>        
    <div v-if="hasAddress" class="custom-element-radio">  
        <el-scrollbar max-height="280px">
            <el-radio-group v-model="address_uuid">
            <template v-for="items in data">
                <el-radio :value="items.address_uuid" size="large" border class="w-100">
                    <div class="text-dark font-weight-bold">{{items.address_label}}</div>
                    <div>{{items.complete_address}}</div>
                </el-radio>                      
            </template>
            </el-radio-group>
        </el-scrollbar>        
        <div class="text-right mt-2">
            <el-button size="large"  @click="modal = false"><?php echo t("Cancel")?></el-button>
            <el-button type="success" size="large" @click="setSelectAddress()"><?php echo t("Select")?></el-button>
        </div>                            
    </div>       
    </template>
  
</el-dialog>
</script>