<el-dialog
v-model="modal_addfunds"
width="420"
:show-close="false"
@open="fetchPayment"
>
<div class="text-center" v-loading="payment_processsing">
    
    <h5><?php echo t('Add Funds to Your Wallet')?></h5>
    <div class="mb-3"><?php echo t('Enter the amount you wish to top up')?></div>
    
    <div style="max-width: 300px;margin:auto;">        

        <div class="mb-3">
            <el-input 
            v-model.number="amount"             
            min="1"
            size="large" 
            placeholder="<?php echo t('eg., {amount}',[
                '{amount}'=>Price_Formatter::formatNumber(10)
            ])?>" 
            clearable
            @input="onInputNumber"
            ></el-input>
        </div>

        <div class="mb-3">            
            <h5 v-if="!hasPayment"><?php echo t("Payment Method")?></h5>                        
            <template v-if="hasPayment">
                <a  href="<?php echo Yii::app()->createUrl("/account/payments")?>" class="d-block chevron-section medium d-flex align-items-center rounded mb-2">
                    <div class="flexcol mr-0 mr-lg-2  payment-logo-wrap">
                    
                        <el-image
							style="width: 30px; height: 30px"
							:src="payment_data?.data?.logo_image"
							fit="contain"
							lazy
						></el-image>

                    </div>
                    <div class="flexcol" > 
                        {{ payment_data?.data?.payment_name }}
                    </div>
                </a>
            </template>
            <template v-else>
               <p>
                <?php echo t("We noticed you haven't added a default payment method yet.")?><br/>
                <?php echo t("To proceed with your transaction smoothly, please add a payment online method to your account. Thank you!")?>
               </p>   

               <el-link type="success" class="text-green" href="<?php echo Yii::app()->createUrl("/account/payments")?>" >
                  <?php echo t("Click here to add online payment")?>
               </el-link>

            </template>
        </div>

        <div class="mb-3">
            <el-button @click="ConfirmTopup" href="<?php echo Yii::app()->createUrl("/account/payments")?>" 
            :disabled="!hasPayment" :loading="loading_addfunds || is_redirect" color="#1976D2" size="large" style="width: 100%;">              
              {{ is_redirect ? '<?php echo t("Redirecting to payment")?>' : '<?php echo t("CONFIRM TO UP")?>' }}
            </el-button>
        </div>
        <div>
            <el-button @click="modal_addfunds=false" link>
                <?php echo t("CLOSE")?>
            </el-button>
        </div>

    </div>

</div>
</el-dialog>