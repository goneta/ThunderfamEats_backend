<nav class="navbar navbar-light justify-content-between">
  <div>
  <a class="navbar-brand">
     <h5><?php echo CHtml::encode($this->pageTitle)?></h5>
  </a>     
  </div>  
</nav>

<div id="vue-user-rewards" v-cloak class="card">
  <div class="card-body">

  <div class="bg-light p-3 mb-3 mt-3 rounded">
    <div class="row">
        <div class="col-md-2 col-sm-6 col-xs-6 text-center">
            <h5><?php echo isset($summary['total_upcoming'])?$summary['total_upcoming']:0?></h5>
            <p class="p-0 m-0"><?php echo t("Upcoming")?></p>
        </div>            
        <div class="col-md-2 col-sm-6 col-xs-6 text-center">
            <h5><?php echo isset($summary['total_reservation'])?$summary['total_reservation']:0?></h5>
            <p class="p-0 m-0"><?php echo t("Total")?></p>
        </div>            
        <div class="col-md-2 col-sm-6 col-xs-6 text-center">
            <h5><?php echo isset($summary['total_denied'])?$summary['total_denied']:0?></h5>
            <p class="p-0 m-0"><?php echo t("Denied")?></p>
        </div>            
        <div class="col-md-2 col-sm-6 col-xs-6 text-center">
            <h5><?php echo isset($summary['total_cancelled'])?$summary['total_cancelled']:0?></h5>
            <p class="p-0 m-0"><?php echo t("Cancelled")?></p>
        </div>            
        <div class="col-md-2 col-sm-6 col-xs-6 text-center">
            <h5><?php echo isset($summary['total_noshow'])?$summary['total_noshow']:0?></h5>
            <p class="p-0 m-0"><?php echo t("No show")?></p>
        </div>            
        <div class="col-md-2 col-sm-6 col-xs-6 text-center">
            <h5><?php echo isset($summary['total_waitlist'])?$summary['total_waitlist']:0?></h5>
            <p class="p-0 m-0"><?php echo t("Wait List")?></p>
        </div>            
    </div>
</div>
  
<components-datatable
ref="datatable"
ajax_url="<?php echo Yii::app()->createUrl("/api")?>" 
actions="customerbooking"
:table_col='<?php echo json_encode($table_col)?>'
:columns='<?php echo json_encode($columns)?>'
:date_filter='<?php echo false;?>'
:ref_id="<?php echo $client_id?>"
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

  </div>
</div>  