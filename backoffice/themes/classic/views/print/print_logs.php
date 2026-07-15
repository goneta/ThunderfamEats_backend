<nav class="navbar navbar-light justify-content-between">
  <a class="navbar-brand">
  <h5><?php echo CHtml::encode($this->pageTitle)?></h5>
  </a>
</nav>

<div id="vue-tables" class="card">
<div class="card-body">

<div class="text-right">
  <a href="<?php echo $clear_logs_url;?>" class="btn btn-danger">
    <?php echo t("Delete past 30 days records")?>
  </a>
</div>

<components-datatable
ref="datatable"
ajax_url="<?php echo $ajax_url?>" 
actions="<?php echo $actions?>"
:table_col='<?php echo json_encode($table_col)?>'
:columns='<?php echo json_encode($columns)?>'
:settings="{  
    auto_load : '<?php echo true;?>',   
    filter_date_disabled : '<?php echo true;?>',   
    filter : '<?php echo true;?>',   
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


</div>
</div>

<?php $this->renderPartial("/admin/modal_delete");?>