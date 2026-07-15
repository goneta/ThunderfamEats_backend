<nav class="navbar navbar-light justify-content-between">
  <a class="navbar-brand">
  <h5><?php echo CHtml::encode($this->pageTitle)?></h5>
  </a>     
</nav>


<div id="vue-daily-sales-report" class="card">
<div class="card-body" v-loading="loading">

<div class="mb-3" >    

<div class="row">
    <div class="col">
    
<div class="row">
   <div class="col-3">
	  <div class="bg-light p-3 mb-3 rounded">   
	   <div class="d-flex">
        <p class="m-0 mr-2 text-muted text-truncate"><?php echo t("Total Sales")?></p><h5 ref="total_sales" class="m-0">0</h5>
       </div>  	  
	  </div><!-- bg-light-->
   </div> <!--col-->

   <div class="col-3">
	  <div class="bg-light p-3 mb-3 rounded">   
	   <div class="d-flex">
        <p class="m-0 mr-2 text-muted text-truncate"><?php echo t("Delivery Fee")?></p><h5 ref="total_delivery_fee" class="m-0">0</h5>
       </div>  	  
	  </div><!-- bg-light-->
   </div> <!--col-->

   <div class="col-3">
	  <div class="bg-light p-3 mb-3 rounded">   
	   <div class="d-flex">
        <p class="m-0 mr-2 text-muted text-truncate"><?php echo t("Total Tax")?></p><h5 ref="total_tax" class="m-0">0</h5>
       </div>  	  
	  </div><!-- bg-light-->
   </div> <!--col-->

   <div class="col-3">
	  <div class="bg-light p-3 mb-3 rounded">   
	   <div class="d-flex">
        <p class="m-0 mr-2 text-muted text-truncate"><?php echo t("Total Tips")?></p><h5 ref="total_tips" class="m-0">0</h5>
       </div>  	  
	  </div><!-- bg-light-->
   </div> <!--col-->

   <div class="col-3">
	  <div class="bg-light p-3 mb-3 rounded">   
	   <div class="d-flex">
        <p class="m-0 mr-2 text-muted text-truncate"><?php echo t("Total")?></p><h5 ref="total" class="m-0">0</h5>
       </div>  	  
	  </div><!-- bg-light-->
   </div> <!--col-->

</div>
<!-- row -->

</div>

    <div class="col-1">
       <?php if(is_array($printer_list) && count($printer_list)>=1):?>
        <div class="dropdown dropleft">
              <a class="rounded-pill rounded-button-icon d-inline-block bg-dark" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="zmdi zmdi-print" style="color:#fff;"></i>
              </a>
              <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">                  
                  <?php foreach ($printer_list as $printers):?>
                  <a class="dropdown-item" href="javascript:;" @click="SwitchPrinter(<?php echo $printers['printer_id']?>,'<?php echo $printers['printer_model']?>')" ><?php echo $printers['printer_name']?></a>
                  <?php endforeach;?>
              </div>              
        </div>
       <?php endif;?>        
    </div>
</div>
<!-- row -->

<components-datatable
ref="datatable"
ajax_url="<?php echo Yii::app()->createUrl("/apibackend")?>" 
actions="dailyReportSales"
:table_col='<?php echo CommonUtility::safeJsonEncode($table_col)?>'
:columns='<?php echo CommonUtility::safeJsonEncode($columns)?>'
:transaction_type_list='<?php echo json_encode($transaction_type)?>'
:filter="<?php echo false; ?>"
:settings="{
    filter_date_disabled : '<?php echo false;?>',   
    filter : '<?php echo false;?>',   
    ordering :'<?php echo true;?>',    
    order_col :'<?php echo intval($order_col);?>',   
    sortby :'<?php echo $sortby;?>',    
    placeholder : '<?php echo CommonUtility::safeTranslate("Start date -- End date")?>',  
    separator : '<?php echo CommonUtility::safeTranslate("to")?>',
    all_transaction : '<?php echo CommonUtility::safeTranslate("All transactions")?>',
    searching : '<?php echo CommonUtility::safeTranslate("Searching...")?>',
    no_results : '<?php echo CommonUtility::safeTranslate("No results")?>'
  }"  
page_limit = "<?php echo Yii::app()->params->list_limit?>"  
@after-selectdate="afterSelectdate"
>
</components-datatable>

</div> <!--mb-3-->

</div> <!--body-->
</div> <!--card-->