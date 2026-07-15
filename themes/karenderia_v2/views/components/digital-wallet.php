<script type="text/x-template" id="xtemplate_digital_wallet">
    
   <template v-if="enabled_digital_wallet">
   
   <h5 class="mb-3"><?php echo t("Digital Wallet")?></h5>
   <el-card class="gradient-yellow-bg mb-4" shadow="hover" v-loading="loading" >
        <div class="row no-gutters align-items-center">
            <div class="col">
            
            <div class="d-flex align-items-center">
                <div class="mr-2">					
                <el-image
                    style="width: 50px; height: 50px"						
                    src="<?php echo Yii::app()->theme->baseUrl."/assets/images/wallet-1.png"?>"
                    fit="cover"
                    lazy
                ></el-image>
                </div>
                <div>                    
                    <h5 class="m-0">{{data.balance}}</h5>
                    <p class="m-0"><?php echo t("Your Available Balance")?></p>
                    <div v-if="message" class="bg-light text-dark p-2 rounded mt-2" style="max-width:80%;">
                    {{message}}
                    </div>
                </div>
            </div>

            </div>
            <!-- col -->
            <div class="col-3">                                
                <el-checkbox v-model="use_wallet" label="<?php echo t("Use Now")?>" size="large" border 
                :disabled="!canUseWallet"                                
                 ></el-checkbox>
            </div>
            <!-- col -->
        </div>
        <!-- row -->
    </el-card>
    </template>

</script>