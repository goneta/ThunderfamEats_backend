<script type="text/x-template" id="xtemplate_succesful_modal">

  <el-dialog
    v-model="modal"	
    width="40%"  	
    modal-class="modified-modal"    
  >    


    <div class="text-center mb-3" >
	  <h4><?php echo t("Congratulations")?>!</h4>
      <p >
        <?php echo t("Your digital wallet has been successfully loaded.")?>        
      </p>
   
	</div>    
  

    <el-descriptions
    title="<?php echo t("Transaction Details!")?>"
    direction="vertical"
    :column="2"
    :size="size"
    border
    class="mb-4"
  >
    <el-descriptions-item label="<?php echo t("Amount Loaded")?>"><?php echo t("Payment Method")?></el-descriptions-item>
    <el-descriptions-item :label="data.amount">{{data.payment_name}}</el-descriptions-item>    
    <el-descriptions-item label="<?php echo t("Transaction ID")?>"><?php echo t("Date and Time")?></el-descriptions-item>
    <el-descriptions-item :label="data.transaction_id">{{data.transaction_date}}</el-descriptions-item>
  </el-descriptions>
  

   <el-button type="success" size="large" class="w-100" @click="modal=!true" >
        <?php echo t("Close")?>
    </el-button>

  </el-dialog>

</script>