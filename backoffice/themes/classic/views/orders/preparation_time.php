<script type="text/x-template" id="xtemplate_preparation_time">
<el-dialog
    v-model="modal"    
    width="500"    
    :close-on-click-modal="false"
  >
  <div class="text-center p-2">
      <h5 class="mb-3"><?php echo t("Preparation Estimate")?></h5>
            
      <div class="d-flex align-items-center justify-content-center mb-2">
        <div class="mr-4">
          <el-button circle @click="lessTimes" >            
            <i class="zmdi zmdi-minus"></i>
          </el-button>
        </div>
        <div class="" >
          <h1 v-if="estimate_data" class="m-0 font-weight-bold">
            {{estimate_data.hour}}:{{estimate_data.minute}}
          </h1>
        </div>
        <div class="ml-4">
          <el-button circle  @click="addTimes" >
             <i class="zmdi zmdi-plus"></i>
          </el-button>
        </div>
      </div>
  </div>
  <template #footer>
      <div class="dialog-footer">        
        <el-button :loading="loading" type="success" @click="updatePreparationtime">
          <?php echo t("Update Time")?>
        </el-button>
      </div>
  </template>
</el-dialog>
</script>