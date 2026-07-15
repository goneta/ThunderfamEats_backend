<?php
$form = $this->beginWidget(
	'CActiveForm',
	array(
		'id' => 'upload-form',
		'enableAjaxValidation' => false,
		'htmlOptions' => array('enctype' => 'multipart/form-data'),
	)
);
?>

<div class="card">
  <div class="card-body">

<?php if(Yii::app()->user->hasFlash('success')): ?>
	<div class="alert alert-success">
		<?php echo Yii::app()->user->getFlash('success'); ?>
	</div>
<?php endif;?>

<?php if(Yii::app()->user->hasFlash('error')): ?>
	<div class="alert alert-danger">
		<?php echo Yii::app()->user->getFlash('error'); ?>
	</div>
<?php endif;?>

<div class="form-label-group">    
   <?php echo $form->textField($model,'driver_app_name',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'driver_app_name'),               
   )); ?>   
   <?php echo $form->labelEx($model,'driver_app_name'); ?>
   <?php echo $form->error($model,'driver_app_name'); ?>
</div>

<h6 class="mb-3 mt-0"><?php echo t("Google play & Apple store")?></h6>
<div class="form-label-group">    
   <?php echo $form->textField($model,'driver_android_download_url',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'driver_android_download_url'),               
   )); ?>   
   <?php echo $form->labelEx($model,'driver_android_download_url'); ?>
   <?php echo $form->error($model,'driver_android_download_url'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'driver_ios_download_url',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'driver_ios_download_url'),               
   )); ?>   
   <?php echo $form->labelEx($model,'driver_ios_download_url'); ?>
   <?php echo $form->error($model,'driver_ios_download_url'); ?>
</div>

<h6 class="mb-3"><?php echo t("Mobile Version")?></h6>

<div class="form-label-group">    
   <?php echo $form->textField($model,'driver_app_version_android',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'driver_app_version_android'),
   )); ?>   
   <?php    
    echo $form->labelEx($model,'driver_app_version_android'); ?>
   <?php echo $form->error($model,'driver_app_version_android'); ?>
   <div class="text-muted"><?php echo t("example 1.0")?></div>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'driver_app_version_ios',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'driver_app_version_ios'),
   )); ?>   
   <?php    
    echo $form->labelEx($model,'driver_app_version_ios'); ?>
   <?php echo $form->error($model,'driver_app_version_ios'); ?>
   <div class="text-muted"><?php echo t("example 1.0")?></div>
</div>

<hr />

<h6 class="mb-3"><?php echo t("Admin Commission per delivery")?></h6>
<div class="form-label-group">    
   <?php echo $form->numberField($model,'driver_commission_per_delivery',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'driver_commission_per_delivery'),               
   )); ?>   
   <?php echo $form->labelEx($model,'driver_commission_per_delivery'); ?>
   <?php echo $form->error($model,'driver_commission_per_delivery'); ?>
</div>


<h6 class="mb-2"><?php echo t("Alert delayed time")?></h6>
<div class="form-label-group">    
   <?php echo $form->textField($model,'driver_alert_time',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'driver_alert_time'),               
   )); ?>   
   <?php echo $form->labelEx($model,'driver_alert_time'); ?>
   <?php echo $form->error($model,'driver_alert_time'); ?>
</div>

<h6 class="mb-2"><?php echo t("Number of allowed order per driver")?></h6>
<div class="form-label-group">    
   <?php echo $form->textField($model,'driver_allowed_number_task',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'driver_allowed_number_task'),               
   )); ?>   
   <?php echo $form->labelEx($model,'driver_allowed_number_task'); ?>
   <?php echo $form->error($model,'driver_allowed_number_task'); ?>
</div>

<!-- <h6 class="mb-2"><?php echo t("Maximum cash amount collected")?></h6>
<div class="form-label-group">    
   <?php echo $form->textField($model,'driver_maximum_cash_amount',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'driver_maximum_cash_amount'),               
   )); ?>   
   <?php echo $form->labelEx($model,'driver_maximum_cash_amount'); ?>
   <?php echo $form->error($model,'driver_maximum_cash_amount'); ?>
</div> -->

<h6 class="mb-2"><?php echo t("Number of allowed break per shift")?></h6>
<div class="form-label-group">    
   <?php echo $form->textField($model,'driver_request_break_limit',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'driver_request_break_limit'),               
   )); ?>   
   <?php echo $form->labelEx($model,'driver_request_break_limit'); ?>
   <?php echo $form->error($model,'driver_request_break_limit'); ?>
</div>

<h6 class="mb-2"><?php echo t("Time allowed to accept or decline order")?></h6>
<div class="form-label-group">    
   <?php echo $form->textField($model,'driver_time_allowed_accept_order',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'driver_time_allowed_accept_order'),               
   )); ?>   
   <?php echo $form->labelEx($model,'driver_time_allowed_accept_order'); ?>
   <?php echo $form->error($model,'driver_time_allowed_accept_order'); ?>
</div>

<h6 class="mb-2"><?php echo t("Driver location update threshold")?></h6>
<div class="form-label-group">    
   <?php echo $form->textField($model,'driver_threshold_meters',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'driver_threshold_meters'),               
   )); ?>   
   <?php echo $form->labelEx($model,'driver_threshold_meters'); ?>
   <?php echo $form->error($model,'driver_threshold_meters'); ?>
</div>

<h6 class="mb-1 mt-4"><?php echo t("On-Demand Availability")?></h6>
<p><?php echo t("Driver will Go online anytime, no shift selection is required")?>.</p>
<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"driver_on_demand_availability",array(
     'class'=>"custom-control-input",      
     'value'=>1,
     'id'=>"driver_on_demand_availability",
     'checked'=>$model->driver_on_demand_availability==1?true:false
   )); ?>   
  <label class="custom-control-label" for="driver_on_demand_availability">
   <?php echo t("Enabled")?>
  </label>
</div>    
<div class="p-1"></div>

<h6 class="mb-2 mt-2"><?php echo t("Enable confirmation photos of deliveries")?></h6>
<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"driver_add_proof_photo",array(
     'class'=>"custom-control-input",      
     'value'=>1,
     'id'=>"driver_add_proof_photo",
     'checked'=>$model->driver_add_proof_photo==1?true:false
   )); ?>   
  <label class="custom-control-label" for="driver_add_proof_photo">
   <?php echo t("Enabled")?>
  </label>
</div>    
<div class="p-1"></div>

<h6 class="mb-2"><?php echo t("Enabled alert delayed order")?></h6>
<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"driver_enabled_alert",array(
     'class'=>"custom-control-input",      
     'value'=>1,
     'id'=>"driver_enabled_alert",
     'checked'=>$model->driver_enabled_alert==1?true:false
   )); ?>   
  <label class="custom-control-label" for="driver_enabled_alert">
   <?php echo t("Enabled")?>
  </label>
</div>    

<h6 class="mb-2 mt-3"><?php echo t("Enabled end shift")?></h6>
<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"driver_enabled_end_shift",array(
     'class'=>"custom-control-input",     
     'value'=>1,
     'id'=>"driver_enabled_end_shift",
     'checked'=>$model->driver_enabled_end_shift==1?true:false
   )); ?>   
  <label class="custom-control-label" for="driver_enabled_end_shift">
   <?php echo t("Enabled")?>
  </label>
</div>    

<h6 class="mb-2 mt-3"><?php echo t("Enabled request break")?></h6>
<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"driver_enabled_request_break",array(
     'class'=>"custom-control-input",     
     'value'=>1,
     'id'=>"driver_enabled_request_break",
     'checked'=>$model->driver_enabled_request_break==1?true:false
   )); ?>   
  <label class="custom-control-label" for="driver_enabled_request_break">
   <?php echo t("Enabled")?>
  </label>
</div>    

<h6 class="mb-2 mt-3"><?php echo t("Enabled Map Cluster")?></h6>
<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"driver_map_enabled_cluster",array(
     'class'=>"custom-control-input",     
     'value'=>1,
     'id'=>"driver_map_enabled_cluster",
     'checked'=>$model->driver_map_enabled_cluster==1?true:false
   )); ?>   
  <label class="custom-control-label" for="driver_map_enabled_cluster">
   <?php echo t("Enabled")?>
  </label>
</div>    

<h6 class="mb-2 mt-3"><?php echo t("Driver Task Options")?></h6>
<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"driver_task_take_pic",array(
     'class'=>"custom-control-input",     
     'value'=>1,
     'id'=>"driver_task_take_pic",
     'checked'=>$model->driver_task_take_pic==1?true:false
   )); ?>   
  <label class="custom-control-label" for="driver_task_take_pic">
   <?php echo t("Enabled Photo Capture before Working")?>
  </label>
</div>    

<h6 class="mb-2 mt-3"><?php echo t("Enabled delivery OTP")?></h6>
<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"driver_enabled_delivery_otp",array(
     'class'=>"custom-control-input",      
     'value'=>1,
     'id'=>"driver_enabled_delivery_otp",
     'checked'=>$model->driver_enabled_delivery_otp==1?true:false
   )); ?>   
  <label class="custom-control-label" for="driver_enabled_delivery_otp">
   <?php echo t("Enabled")?>
  </label>  
</div>    

<h6 class="mb-2 mt-3"><?php echo t("Enabled time allowed acceptance of orders")?></h6>
<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"driver_enabled_time_allowed_acceptance",array(
     'class'=>"custom-control-input",      
     'value'=>1,
     'id'=>"driver_enabled_time_allowed_acceptance",
     'checked'=>$model->driver_enabled_time_allowed_acceptance==1?true:false
   )); ?>   
  <label class="custom-control-label" for="driver_enabled_time_allowed_acceptance">
   <?php echo t("Enabled")?>
  </label>  
</div>    


<hr/>

<h6 class="mb-2 mt-3"><?php echo t("Enabled auto assign")?></h6>
<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"driver_enabled_auto_assign",array(
     'class'=>"custom-control-input",     
     'value'=>1,
     'id'=>"driver_enabled_auto_assign",
     'checked'=>$model->driver_enabled_auto_assign==1?true:false
   )); ?>   
  <label class="custom-control-label" for="driver_enabled_auto_assign">
   <?php echo t("Enabled")?>
  </label>
</div>    

<h6 class="mb-2 mt-3"><?php echo t("Assign only if merchant accepted the order")?></h6>
<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"driver_assign_when_accepted",array(
     'class'=>"custom-control-input",     
     'value'=>1,
     'id'=>"driver_assign_when_accepted",
     'checked'=>$model->driver_assign_when_accepted==1?true:false
   )); ?>   
  <label class="custom-control-label" for="driver_assign_when_accepted">
   <?php echo t("Enabled")?>
  </label>
</div>    


<h6 class="mb-2 mt-3"><?php echo t("Enabled Retry Auto Assign")?></h6>
<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"driver_auto_assign_retry",array(
     'class'=>"custom-control-input",     
     'value'=>1,
     'id'=>"driver_auto_assign_retry",
     'checked'=>$model->driver_auto_assign_retry==1?true:false
   )); ?>   
  <label class="custom-control-label" for="driver_auto_assign_retry">
   <?php echo t("Enabled")?>
  </label>
</div>    

<h6 class="mb-2"><?php echo t("Auto Assign Max Retries")?></h6>
<div class="form-label-group">    
   <?php echo $form->textField($model,'driver_assign_max_retry',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'driver_assign_max_retry'),               
   )); ?>   
   <?php echo $form->labelEx($model,'driver_assign_max_retry'); ?>
   <?php echo $form->error($model,'driver_assign_max_retry'); ?>
</div>

<hr/>
<h5 class="mb-3" ><?php echo t("Templates")?></h5>
<h6 class="mb-3"><?php echo t("Missed assigned orders")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'driver_missed_order_tpl', (array)$template_list ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'driver_missed_order_tpl'),
   )); ?>         
   <?php echo $form->error($model,'driver_missed_order_tpl'); ?>
</div>

<h6 class="mb-3"><?php echo t("Order OTP to customer")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'driver_order_otp_tpl', (array)$template_list ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'driver_order_otp_tpl'),
   )); ?>         
   <?php echo $form->error($model,'driver_order_otp_tpl'); ?>
</div>


   
  </div> <!--body-->
</div> <!--card-->



<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>