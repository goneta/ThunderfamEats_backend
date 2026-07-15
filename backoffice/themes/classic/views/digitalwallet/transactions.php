<nav class="navbar navbar-light justify-content-between">
  <div>
  <a class="navbar-brand">
     <h5><?php echo CHtml::encode($this->pageTitle)?></h5>
  </a>     
  </div>  
</nav>

<div id="vue-digital-wallet-transaction" v-cloak class="card">
  <div class="card-body">

    <div class="bg-light p-3 mb-3 rounded">
       <div class="row align-items-center">
           <div class="col-lg-8 col-md-8 col-sm-6 mb-3 mb-xl-0"></div>
           <div class="col-lg-4 col-md-4 col-sm-6 text-md-right mb-3 mb-xl-0">
           
               <div class="dropdown">
                    <button class="btn btn-green dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> 
                        <?php echo t("Create a Transaction")?>
                    </button><div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <a @click="createTransaction" class="dropdown-item"><?php echo t("Adjustment")?></a>
                    </div>
                </div>

           </div>
       </div>
       <!-- row -->
    </div>
    <!-- rounded -->


    <components-datatable
    ref="datatable"
    ajax_url="<?php echo Yii::app()->createUrl("/api")?>" 
    actions="digitalWalletTransactions"
    :table_col='<?php echo json_encode($table_col)?>'
    :columns='<?php echo json_encode($columns)?>'
    :date_filter='<?php echo false;?>'    
    :settings="{
        auto_load : '<?php echo true;?>',
        filter : '<?php echo false;?>',   
        ordering :'<?php echo true;?>',  
        order_col :'<?php echo intval($order_col);?>',   
        sortby :'<?php echo $sortby;?>',     
        placeholder : '<?php echo CJavaScript::quote(t("Start date -- End date"))?>',  
        separator : '<?php echo CJavaScript::quote(t("to"))?>',
        all_transaction : '<?php echo CJavaScript::quote(t("All transactions"))?>'
    }"  
    page_limit = "<?php echo Yii::app()->params->list_limit?>"  
    >
    </components-datatable>

      
  <components-create-adjustment-digitalwallet
  ref="create_adjustment"
  ajax_url="<?php echo Yii::app()->createUrl("/api")?>" 
  :transaction_type_list='<?php echo json_encode($transaction_type)?>' 
  :ref_id="<?php echo ''?>" 
  action_name='digitalwalletadjustment'
  :label="{    
      title : '<?php echo CJavaScript::quote(t("Create adjustment"))?>',
      close : '<?php echo CJavaScript::quote(t("Close"))?>',
      submit : '<?php echo CJavaScript::quote(t("Submit"))?>',
      transaction_description : '<?php echo CJavaScript::quote(t("Transaction Description"))?>',
      transaction_amount : '<?php echo CJavaScript::quote(t("Amount"))?>',
      customer_name : '<?php echo CJavaScript::quote(t("Customer"))?>',
    }"  
  @after-save="afterSave"
  >
  </components-create-adjustment-digitalwallet>


  </div>
  <!-- card body-->
</div>  
<!-- card -->

