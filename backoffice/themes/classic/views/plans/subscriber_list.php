<nav class="navbar navbar-light justify-content-between">
  <a class="navbar-brand">
  <h5><?php echo CHtml::encode($this->pageTitle)?></h5>
  </a>     
</nav>

<div id="vue-subscribers" class="card" v-cloak >
<div class="card-body" v-loading="loading">

   <h5 class="mb-4">
    <?php echo t("Subscription Summary")?>
   </h5>

   
   <div class="row">

     <div class="col">
     <div class="bg-light p-4 mb-3 rounded">
        <h5>{{ getData ? getData.total_subscriptions : '' }}</h5>
        <div><?php echo t("Total Subscriptions")?></div>	   
	  </div><!-- bg-light-->
     </div>
     <!-- col -->

     <div class="col">
     <div class="bg-light p-4 mb-3 rounded">
        <h5>{{ getData ? getData.active_subscriptions : '' }}</h5>
        <div><?php echo t("Active Subscriptions")?></div>	   	   
	  </div><!-- bg-light-->
     </div>
     <!-- col -->

     <div class="col">
     <div class="bg-light p-4 mb-3 rounded">
        <h5>{{ getData ? getData.expired_subscriptions : '' }}</h5>
        <div><?php echo t("Expired Subscriptions")?></div>	   	   	 
	  </div><!-- bg-light-->
     </div>
     <!-- col -->

     <div class="col">
     <div class="bg-light p-4 mb-3 rounded">
        <h5>{{ getData ? getData.new_subscriptions : '' }}</h5>
        <div><?php echo t("New Subscriptions")?></div>	   	   	 	   
	  </div><!-- bg-light-->
     </div>
     <!-- col -->

     <div class="col">
     <div class="bg-light p-4 mb-3 rounded">
        <h5>{{ getData ? getData.cancelled_subscriptions : '' }}</h5>
        <div><?php echo t("Cancelled Subscriptions")?></div>		   
	  </div><!-- bg-light-->
     </div>
     <!-- col -->

   </div>
   <!-- row -->
  

    <components-datatable
    ref="datatable"
    ajax_url="<?php echo Yii::app()->createUrl("/api")?>" 
    actions="subscriberList"
    :table_col='<?php echo json_encode($table_col)?>'
    :columns='<?php echo json_encode($columns)?>'
    :date_filter='<?php echo true;?>'
    :settings="{
        auto_load : '<?php echo true;?>',
        filter : '<?php echo true;?>',   
        ordering :'<?php echo true;?>',  
        order_col :'<?php echo intval($order_col);?>',   
        sortby :'<?php echo $sortby;?>',         
        placeholder : '<?php echo t("Start date -- End date")?>',  
        separator : '<?php echo t("to")?>',
        all_transaction : '<?php echo t("All transactions")?>'
    }"  
    page_limit = "<?php echo Yii::app()->params->list_limit?>"  
    @view-transaction="viewMerchantTransaction"
    @after-selectdate="afterSelectdate"
    >
    </components-datatable>

</div> <!--card body-->
</div> <!--card-->