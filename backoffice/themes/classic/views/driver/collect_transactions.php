<nav class="navbar navbar-light justify-content-between">
<?php
$this->widget('zii.widgets.CBreadcrumbs', 
array(
'links'=>$links,
'homeLink'=>false,
'separator'=>'<span class="separator">
<i class="zmdi zmdi-chevron-right"></i><i class="zmdi zmdi-chevron-right"></i></span>'
));
?>
</nav>


<div class="card">
  <div class="card-body">

    <div class="row">
        <div class="col-md-6">

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="p-0 m-0"><?php echo t("Driver information")?></h5>
                </div>
                <div class="card-body">                  
                  <h5><?php echo t("Name")?> : <?php echo isset($data['driver_name'])?$data['driver_name']:t("Not found") ?></h5>
                  <h6><?php echo  isset($employment_type[$data['employment_type']])?$employment_type[$data['employment_type']]:''; ?></h6>
                  <h6><?php echo t("Cash collected balance")?> : <?php echo Price_Formatter::formatNumber($balance);?></h6>                  
                  <a href="<?php echo isset($driver_link) ? $driver_link : Yii::app()->createUrl("/driver/overview",['id'=>$data['driver_uuid']])?>" class="btn btn-link"><?php echo t("View details")?></a>
                </div> 
            </div>

        </div>
        <!-- col -->
        <div class="col-md-6">

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="p-0 m-0"><?php echo t("Transaction Information")?></h5>
                </div>
                <div class="card-body">
                  <h5><?php echo t("Amount")?> : <?php echo Price_Formatter::formatNumber($data['amount_collected'])?></h5>
                  <h6><?php echo t("Date/time")?> : <?php echo Date_Formatter::dateTime($data['transaction_date'])?></h6>
                  <h6><?php echo t("Reference")?> : <?php echo $data['reference_id'];?></h6>
                </div>
            </div>

        </div>
        <!-- col -->
    </div>
    <!-- row -->

  </div>
</div>