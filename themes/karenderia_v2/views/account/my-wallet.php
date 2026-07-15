<DIV id="vue-my-wallet" v-cloak> 


<div class="card p-3 mb-3"  >
 <div class="rounded p-3 grey-bg gradient-yellow-bgx d-none d-lg-block d-xl-nonex" >

     <?php if($bonus_count):?>        
     <div class="row no-guttersx align-items-center">
        <div class="col-md-5 d-none d-lg-block">           
           
           <div class="d-flex justify-content-between align-items-center">
              <div>
                 <div class="header_icon _icons digital-wallet" style="width: 40px;height: 40px;"></div> 
                 <h5><component-wallet-balance ref="wallet_balance"></component-wallet-balance></h5>
                 <p class="m-0"><?php echo t("Available Balance")?></p>
              </div>          
              <?php if($enabled_topup):?>                   
              <a class="btn btn-green" href="javascript:;" @click="showFundsForm">
                  <div class="d-flex align-items-start">
                  <span class="mr-1"><i class="zmdi zmdi-plus-circle font20"></i></span>
                  <?php echo t("Add Funds")?>
                  </div>
               </a> 
               <?php endif;?>
           </div>              
           <!-- flex -->

        </div>
        <!-- col -->
        <div class="col-md-7"> 
          <component-bonusfunds 
          ref="bonusfunds"
          transaction_type="<?php echo CDigitalWallet::transactionName();?>"
          @set-bonusfunds="setBonusfunds"
          ></component-bonusfunds>
        </div>
        <!-- col -->
     </div>
     <!-- row -->
     <?php else :?>
         <div class="row no-gutters align-items-center">
            <div class="col-md-2 d-none d-lg-block">
               <div class="header_icon _icons digital-wallet d-flex align-items-center justify-content-center">         
               </div>
            </div>         
            <div class="col-md-6"> 
               <h5><component-wallet-balance ref="wallet_balance"></component-wallet-balance></h5>
               <p class="m-0"><?php echo t("Available Balance")?></p>
            </div>      
            <?php if($enabled_topup):?>
            <div class="col-md-4 text-center">        
               <a class="btn btn-green" href="javascript:;" @click="showFundsForm">
                     <div class="d-flex align-items-start">
                     <span class="mr-1"><i class="zmdi zmdi-plus-circle font20"></i></span>
                     <?php echo t("Add Funds")?>
                     </div>
               </a>        
            </div>
            <?php endif;?>    
         </div>
     <?php endif;?>

 </div>

 <!-- MOBILE VIEW -->
 <div class="d-block d-sm-block d-md-block d-lg-none">
      <div class="d-flex justify-content-between align-items-center">
         <div>
            <div class="header_icon _icons digital-wallet" style="width: 40px;height: 40px;"></div> 
            <h5><component-wallet-balance ref="wallet_balance_mobile"></component-wallet-balance></h5>
            <p class="m-0"><?php echo t("Available Balance")?></p>
         </div>          
         <?php if($enabled_topup):?>                   
         <a class="btn btn-green" href="javascript:;" @click="showFundsForm">
            <div class="d-flex align-items-start">
            <span class="mr-1"><i class="zmdi zmdi-plus-circle font20"></i></span>
            <?php echo t("Add Funds")?>
            </div>
         </a> 
         <?php endif;?>
      </div>              
      <!-- flex -->    

      <div class="mt-4">
         <component-bonusfunds 
         ref="bonusfunds"
         transaction_type="<?php echo CDigitalWallet::transactionName();?>"
         @set-bonusfunds="setBonusfunds"
         ></component-bonusfunds>
      </div>
 </div>  
  <!-- MOBILE -->

</div> <!--card -->

<el-tabs v-model="tab" @tab-change="tabChange" >
    <el-tab-pane label="<?php echo t("All")?>" name="all">

       <components-wallet-transaction
       :data="getData"        
       :has_data="hasData"    
       :loading="loading"    
       :show_next="showNext"
       >
       </components-wallet-transaction>       

    </el-tab-pane>
    <el-tab-pane label="<?php echo t("Orders")?>" name="order">
    
       <components-wallet-transaction
       :data="getData"        
       :has_data="hasData"    
       :loading="loading"    
       :show_next="showNext"
       >
       </components-wallet-transaction>       

    </el-tab-pane>    
    <el-tab-pane label="<?php echo t("Refunds")?>" name="refund">
    
       <components-wallet-transaction
       :data="getData"        
       :has_data="hasData"    
       :loading="loading"    
       :show_next="showNext"
       >
       </components-wallet-transaction>   

    </el-tab-pane>    
    <el-tab-pane label="<?php echo t("Top-ups")?>" name="topup">
    
    <components-wallet-transaction
    :data="getData"        
    :has_data="hasData"    
    :loading="loading"    
    :show_next="showNext"
    >
    </components-wallet-transaction>   

    </el-tab-pane>    
    <el-tab-pane label="<?php echo t("Cashbacks")?>" name="cashback">
    
      <components-wallet-transaction
       :data="getData"        
       :has_data="hasData"    
       :loading="loading"    
       :show_next="showNext"
       >
       </components-wallet-transaction>   

    </el-tab-pane>       
    <el-tab-pane label="<?php echo t("Adjustment")?>" name="adjustment">
    
      <components-wallet-transaction
       :data="getData"        
       :has_data="hasData"    
       :loading="loading"    
       :show_next="showNext"
       >
       </components-wallet-transaction>   

    </el-tab-pane>       
</el-tabs>

<!-- showNext=>{{showNext}}
page=>{{page}}
=>{{loading_next}} -->
<div v-if="showNext" class="d-flex justify-content-center mt-4 mb-5" >  
  <el-button type="primary" round      
   size="large"   
   @click="nextPage(page)"
   :loading="loading_next"
  >
    <?php echo t("Load more")?>
  </el-button> 
</div>

<components-topup-form
ref="topup"
@after-preparepayment="afterPreparepayment"
@after-preparepayment="afterPreparepayment"
:topup_minimum="<?php echo floatval($topup_minimum)?>"
:topup_maximum="<?php echo floatval($topup_maximum)?>"
>
</components-topup-form>

<components-succesful-modal 
ref="succesful_modal"
:data="payment_data"
>
</components-succesful-modal>

<!--RENDER PAYMENT COMPONENTS-->
<?php CComponentsManager::renderComponents($payments,$payments_credentials,$this)?>

<el-backtop :right="50" :bottom="30" ></el-backtop>

</DIV>
<!-- vue-my-wallet -->

<?php 
$this->renderPartial('//account/my-wallet-transaction');
$this->renderPartial('//components/topup-form');
$this->renderPartial('//components/succesful-modal');
//$this->renderPartial("//components/loading-box");
?>