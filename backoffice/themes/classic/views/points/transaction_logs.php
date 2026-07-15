<nav class="navbar navbar-light justify-content-between">
  <div>
  <a class="navbar-brand">
     <h5><?php echo CHtml::encode($this->pageTitle)?></h5>
  </a>     
  </div>
  <?php if(isset($back_url)):?>
  <div>
    <a href="<?php echo isset($back_url)?$back_url:''?>" class="btn btn-link"><?php echo t("Go back")?></a>
  </div>
  <?php endif;?>
</nav>


<div id="vue-user-rewards" v-cloak class="card">
  <div class="card-body">

   <div class="mb-3">      

      <?php if($customer):?>
      <div class="bg-light p-3 mb-3 rounded">
          <div class="row align-items-center">
              <div class="col-lg-4 col-md-4 col-sm-6 mb-3 mb-xl-0">
                  <h5 class="m-0"><?php echo isset($customer['customer_name']) ? ucwords($customer['customer_name']) : t('Name not available')?></h5>
                  <p class="m-0 text-muted"><?php echo isset($customer['email_address'])?$customer['email_address']:''?></p>
              </div>

              <div class="col-lg-4 col-md-4 col-sm-6 mb-3 mb-xl-0">
                <div class="d-flex">
                    <p class="m-0 mr-2 text-muted">
                      <?php echo isset($available_label)?$available_label:t("Balance")?>
                    </p>
                    <h5 class="m-0">
                    <span>
                        <components-points-balance
                          ref="points_balance"
                          ajax_url="<?php echo Yii::app()->createUrl("/api")?>"                         
                          :card_id="<?php echo $card_id?>"                              
                          return_format="<?php echo isset($return_format)?$return_format:''?>"    
                        >
                        </components-points-balance>
                    </span>
                </h5>
                </div>
              </div>

              <div class="col-lg-4 col-md-4 col-sm-6 text-md-right mb-3 mb-xl-0">
                <div class="dropdown">
                    <button class="btn btn-green dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> 
                        <?php echo t("Create a Transaction ")?>
                    </button><div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <a @click="createTransaction" class="dropdown-item"><?php echo t("Adjustment")?></a>
                    </div>
                </div>
            </div>

          </div>
          <!-- row -->
      </div>
      <?php endif;?>
      <!-- light -->

   </div>
   <!-- mb-3 -->
  
<components-datatable
ref="datatable"
ajax_url="<?php echo Yii::app()->createUrl("/api")?>" 
actions="PointsTransactionLogs"
:table_col='<?php echo json_encode($table_col)?>'
:columns='<?php echo json_encode($columns)?>'
:date_filter='<?php echo false;?>'
:ref_id="<?php echo $card_id?>"
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

<components-create-adjustment
ref="create_adjustment"
ajax_url="<?php echo Yii::app()->createUrl("/api")?>" 
:transaction_type_list='<?php echo json_encode($transaction_type)?>'
:ref_id="<?php echo $card_id?>"
action_name='<?php echo $action_name ?? 'pointsadjustment' ?>'
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


  </div>
  <!-- card body-->
</div>  
<!-- card -->