<nav class="navbar navbar-light justify-content-between">
  <a class="navbar-brand">
  <h5><?php echo CHtml::encode($this->pageTitle)?></h5>
  </a>     
</nav>


<div id="vue-tables" class="card">
<div class="card-body">
  

 <div class="mt-3">

 <components-reports-merchant
    ref="summary"
    ajax_url="<?php echo Yii::app()->createUrl("/api")?>"  
    :label="{    
	    total_registered : '<?php echo CommonUtility::safeTrim("Total Registered");?>',    
	    commission_total : '<?php echo CommonUtility::safeTrim("Commission Total");?>',    
	    membership_total : '<?php echo CommonUtility::safeTrim("Membership Total");?>',    
	    total_active : '<?php echo CommonUtility::safeTrim("Total Active");?>',    
	    total_inactive : '<?php echo CommonUtility::safeTrim("Total Inactive");?>',    
	}"            
 />
 </components-reports-merchant>                

 <components-datatable
  ref="datatable"
  ajax_url="<?php echo Yii::app()->createUrl("/api")?>" 
  actions="ReportsMerchantReg"
  :table_col='<?php echo CommonUtility::safeJsonEncode($table_col)?>'
  :columns='<?php echo CommonUtility::safeJsonEncode($columns)?>'
  :transaction_type_list='<?php echo CommonUtility::safeJsonEncode($transaction_type)?>'
  :date_filter='<?php echo true;?>'
  :filter="<?php echo true; ?>"
  :settings="{
      auto_load : '<?php echo true;?>',
      filter : '<?php echo false;?>',   
      ordering :'<?php echo true;?>',  
      order_col :'<?php echo intval($order_col);?>',   
      load_filter :'<?php echo false;?>',  
      sortby :'<?php echo $sortby;?>',     
      placeholder : '<?php echo CommonUtility::safeTrim("Start date -- End date")?>',  
      separator : '<?php echo CommonUtility::safeTrim("to")?>',
      all_transaction : '<?php echo CommonUtility::safeTrim("All Status")?>'
    }"  
  page_limit = "<?php echo Yii::app()->params->list_limit?>"  
  >
  </components-datatable>
  
  
  </div>
  

</div> <!--card body-->
</div> <!--card-->

<?php $this->renderPartial("/reports/filter_reports");?>