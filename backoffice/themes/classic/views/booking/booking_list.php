<nav class="navbar navbar-light justify-content-between">
  <a class="navbar-brand">
  <h5><?php echo CHtml::encode($this->pageTitle)?></h5>
  </a>

  <div class="d-flex flex-row justify-content-end align-items-center">
    <div class="p-2">  
	  <a type="button" class="btn btn-black btn-circle" 
	  href="<?php echo $link?>">
	    <i class="zmdi zmdi-plus"></i>
	  </a>  
	 </div>
	<div class="p-2"><h5 class="m-0"><?php echo t("Add Booking")?></h5></div>    

  </div>

</nav>


<div id="vue-tables" class="card">
<div class="card-body">

<h5 class="mb-4"><?php echo t("Reservation summary")?></h5>

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
  <!-- bg-light   -->


<components-datatable
ref="datatable"
ajax_url="<?php echo $ajax_url?>" 
actions="<?php echo $actions?>"
:table_col='<?php echo json_encode($table_col)?>'
:columns='<?php echo json_encode($columns)?>'
:settings="{  
    auto_load : '<?php echo true;?>',   
    filter_date_disabled : '<?php echo false;?>',   
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