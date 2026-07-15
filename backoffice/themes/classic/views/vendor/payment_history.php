
<div id="vue-tables" >
 <components-datatable
  ref="datatable"
  ajax_url="<?php echo $ajax_url?>" 
  actions="MerchantPaymentPlans"
  :table_col='<?php echo json_encode($table_col)?>'
  :columns='<?php echo json_encode($columns)?>'  
  :date_filter='<?php echo false;?>'
  :filter="<?php echo false; ?>"
  :ref_id="<?php echo $merchant_id; ?>"
  :settings="{
      auto_load : '<?php echo true;?>',
      filter : '<?php echo false;?>',   
      ordering :'<?php echo true;?>',  
      order_col :'<?php echo intval($order_col);?>',   
      load_filter :'<?php echo false;?>',  
      sortby :'<?php echo $sortby;?>',     
      placeholder : '<?php echo CJavaScript::quote(t("Start date -- End date"))?>',  
      separator : '<?php echo CJavaScript::quote(t("to"))?>',
      all_transaction : '<?php echo CJavaScript::quote(t("All status"))?>'
    }"  
  page_limit = "<?php echo Yii::app()->params->list_limit?>"  
  >
  </components-datatable>
  
  
 
</div> <!--tables-->
