<script type="text/x-template" id="xtemplate_topupform">

  <el-dialog
    v-model="modal"	
    width="40%"  	
    modal-class="modified-modal"    
	 >    
	<DIV 
  v-loading="loading_payment"
  element-loading-text="Processing payment, don't close this window"
  >

	<div class="text-center mb-3" >
	  <h5><?php echo t("Add Funds to Your Wallet")?></h5>
      <p class="font11">
        <?php echo t("Please note that for top-up transactions, only online payment methods are currently supported.")?>
        <?php echo t("Kindly select an online payment option to proceed.")?>
      </p>
	</div>

	<div class="mb-3">  
      <el-input-number
        v-model="amount"
        :min="topup_minimum"
        :max="topup_maximum"
        :step="10"
        controls-position="right"
        size="large"
        class="w-100"
     >
     </el-input-number>     
	</div>

    <template v-if="loading">
       <el-skeleton :rows="5" animated />
    </template>
    
    <template v-else>

    <div class="text-center mb-3">
	  <h5><?php echo t("Payment Method")?></h5>
	</div>

	<div v-if="getPayment" class="row mb-3 align-items-center">
	  <div class="col">
	     <div class="d-flex align-items-center">
		    <div class="mr-2">
                <template v-if="getPayment.logo_type=='image'">
                   <el-image style="width: 30px; height: 30px" :src="getPayment.logo_image" fit="contain"></el-image>
                </template>                
            </div>
			<div>
			  <b>{{getPayment.payment_name}}</b>
			  <p>{{getPayment.attr2}}</p>
			</div>
		 </div>
	  </div>
	  <div class="col text-right">
	      <el-link type="primary" href="<?php echo Yii::app()->createUrl("/account/payments")?>">
		  <?php echo t("Change")?>		  
		  </el-link>
	  </div>
	</div>

    <div v-else>
        <div class="mb-3">
            <p>
                <?php echo t("We noticed you haven't added a default payment method yet.")?><br/>
                <?php echo t("To proceed with your transaction smoothly, please add a payment online method to your account. Thank you!")?>
            </p>            

                    
            
            <a href="<?php echo Yii::app()->createUrl("/account/payments")?>" class="w-100 d-block text-center text-green">
            <?php echo t("Click here to add online payment")?>
            </a>

        </div>
    </div>

    <template v-if="getPayment">
	<el-button type="success" size="large" class="w-100" @click="onAddfunds" :loading="loading_submit" >
        <?php echo t("Add Funds")?>
    </el-button>

	<p class="mt-2">
    <?php echo t("Please ensure that the entered payment amount is correct and matches your intended top-up value.")?>
    <?php echo t("Make sure to enter any promo codes accurately to avail of bonuses or discounts.")?> 
    <?php echo t("Funds added to your wallet are non-refundable.")?>
    </p>
    </template>

    </template> 
    <!-- end loading -->
	

    </DIV>
	</el-dialog>

</script>