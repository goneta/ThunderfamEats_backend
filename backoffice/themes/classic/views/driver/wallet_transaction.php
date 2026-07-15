


<div id="vue-driver-wallet" class="card">
<div class="card-body">

<div class="mb-3">
   <h5 class="mb-4"><?php echo t("Wallet History")?></h5>
   <div class="bg-light p-3 mb-3 rounded">
       <div class="row align-items-center">
           <div class=" col-lg-4 col-md-4 col-sm-6  mb-3 mb-xl-0">
             <h5 class="m-0"><?php echo t("Earnings")?></h5>
             <p class="m-0 text-muted"><?php echo t("Your earnings transaction for all deliveries")?></p>
           </div>
           <div class=" col-lg-4 col-md-4 col-sm-6  mb-3 mb-xl-0">
             <p class="m-0 mr-2 text-muted"><?php echo t("Total Balance")?></p>
             <h5 class="m-0">
               <components-commission-balance
                ref="balance"
                ajax_url="<?php echo isset($ajax_url)?$ajax_url:Yii::app()->createUrl("/api");?>" 
                action_name='driverWalletBalance'
                ref_id='<?php echo $driver_uuid;?>'
                @after-balance="afterBalance"
                >
                </components-commission-balance>         
             </h5>
           </div>

           <div class=" col-lg-4 col-md-4 col-sm-6 text-md-right  mb-3 mb-xl-0">       
            <div class="dropdown">
                <button class="btn btn-green dropdown-toggle"           
                type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">		    
                    <?php echo t("Create a Transaction")?>
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                 <a @click="createTransaction" class="dropdown-item" ><?php echo t("Adjustment")?></a>		    
                 <a @click="clearWallet" class="dropdown-item" ><?php echo t("Clear wallet transactions")?></a>
               </div>
		       </div>

       </div>
   </div>
</div>

<components-datatable
ref="datatable"
ajax_url="<?php echo isset($ajax_url)?$ajax_url:Yii::app()->createUrl("/api");?>" 
actions="driverWalletTransactions"
:table_col='<?php echo json_encode($table_col)?>'
:columns='<?php echo json_encode($columns)?>'
:transaction_type_list='<?php echo json_encode($transaction_type)?>'
:date_filter='<?php echo true;?>'
ref_id='<?php echo $driver_uuid;?>'
:settings="{
    auto_load : '<?php echo true;?>',
    filter : '<?php echo false;?>',   
    ordering :'<?php echo false;?>',  
    order_col :'<?php echo intval($order_col);?>',   
    sortby :'<?php echo $sortby;?>',     
    placeholder : '<?php echo CJavaScript::quote(t("Start date -- End date"))?>',  
    separator : '<?php echo CJavaScript::quote(t("to"))?>',
    all_transaction : '<?php echo CJavaScript::quote(t("All transactions"))?>'
  }"  
page_limit = "<?php echo Yii::app()->params->list_limit?>"  
>
</components-datatable>


<components-create-adjustment
ref="create_adjustment"
ajax_url="<?php echo isset($ajax_url)?$ajax_url:Yii::app()->createUrl("/api");?>" 
:transaction_type_list='<?php echo json_encode($transaction_type)?>'
action_name='driverWalletAdjustment'
ref_id='<?php echo $driver_uuid;?>'
:label="{    
    title : '<?php echo CJavaScript::quote(t("Create adjustment"))?>',
    close : '<?php echo CJavaScript::quote(t("Close"))?>',
    submit : '<?php echo CJavaScript::quote(t("Submit"))?>',
    transaction_description : '<?php echo CJavaScript::quote(t("Transaction Description"))?>',
    transaction_amount : '<?php echo CJavaScript::quote(t("Amount"))?>',
  }"  
@after-save="afterSave"
>
</components-create-adjustment>

<components-clearwallet
ref="clear_wallet"
ajax_url="<?php echo isset($ajax_url)?$ajax_url:Yii::app()->createUrl("/api");?>" 
ref_id='<?php echo $driver_uuid;?>'
:label="{    
    confirm : '<?php echo CJavaScript::quote(t("Clear wallet transactions"))?>',
    message : '<?php echo CJavaScript::quote(t("Are you sure you want to permanently delete all transactions?"))?>',
    ok : '<?php echo CJavaScript::quote(t("Yes"))?>',
    cancel : '<?php echo CJavaScript::quote(t("Cancel"))?>',    
}"  
@after-save="afterSave"
>
</components-clearwallet>


</div> <!--card body-->
</div> <!--card-->