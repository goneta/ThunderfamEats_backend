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

<!--<h5 class="card-title"><?php echo t("Status for order receive")?></h5>  
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'tracking_status_receive', (array) $status_list,array(
     'class'=>"form-control custom-select form-control-select",
     'placeholder'=>$form->label($model,'tracking_status_receive'),
   )); ?>         
   <?php echo $form->error($model,'tracking_status_receive'); ?>
</div>-->


<div class="d-flex align-items-start">
  <div><h5 class="card-title"><?php echo t("Threshold for late orders (Accepting)")?></h5>  </div>
  <div class="ml-2">
    <a href="javascript:;" class="tool_tips" data-toggle="tooltip" data-placement="top" title="" 
    data-original-title="<?php echo t("The maximum time a restaurant or merchant has to accept an order before it's considered late")?>">
     <i class="zmdi zmdi-info-outline font16"></i>
   </a>
  </div>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'admin_threshold_late',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'admin_threshold_late')     
   )); ?>   
   <?php    
    echo $form->label($model,'admin_threshold_late'); ?>
   <?php echo $form->error($model,'admin_threshold_late'); ?>   
</div>


<div class="d-flex align-items-start">
  <div><h5 class="card-title"><?php echo t("Threshold for Cancellation (Accepting)")?></h5>  </div>
  <div class="ml-2">
    <a href="javascript:;" class="tool_tips" data-toggle="tooltip" data-placement="top" title="" 
    data-original-title="<?php echo t("the time limit after which an order is considered extremely delayed, allowing the customer to cancel the order directly from the customer tracking page. Once the order exceeds this threshold, a 'Cancel Order' button will appear, enabling the customer to cancel the order due to excessive lateness.")?>">
     <i class="zmdi zmdi-info-outline font16"></i>
   </a>
  </div>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'admin_cancellation_threshold',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'admin_cancellation_threshold')     
   )); ?>   
   <?php    
    echo $form->label($model,'admin_cancellation_threshold'); ?>
   <?php echo $form->error($model,'admin_cancellation_threshold'); ?>   
</div>


<div class="d-flex align-items-start">
  <div><h5 class="card-title"><?php echo t("Late Delivery Threshold")?></h5>  </div>
  <div class="ml-2">
    <a href="javascript:;" class="tool_tips" data-toggle="tooltip" data-placement="top" title="" 
    data-original-title="<?php echo t("Defines the maximum time (in minutes) after the estimated delivery time that the delivery is considered late. If the order exceeds this time, it will be flagged as delayed.")?>">
     <i class="zmdi zmdi-info-outline font16"></i>
   </a>
  </div>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'admin_threshold_late_delivery',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'admin_threshold_late_delivery')     
   )); ?>   
   <?php    
    echo $form->label($model,'admin_threshold_late_delivery'); ?>
   <?php echo $form->error($model,'admin_threshold_late_delivery'); ?>   
</div>

<div class="d-flex align-items-start">
  <div><h5 class="card-title"><?php echo t("Cancellation Threshold for Late Delivery")?></h5>  </div>
  <div class="ml-2">
    <a href="javascript:;" class="tool_tips" data-toggle="tooltip" data-placement="top" title="" 
    data-original-title="<?php echo t("Sets the time (in minutes) after which customers will be offered the option to cancel their order due to a delayed delivery. Once the delivery exceeds this threshold, the option to cancel will be presented to the customer.")?>">
     <i class="zmdi zmdi-info-outline font16"></i>
   </a>
  </div>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'admin_cancellation_threshold_delivery',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'admin_cancellation_threshold_delivery')     
   )); ?>   
   <?php    
    echo $form->label($model,'admin_cancellation_threshold_delivery'); ?>
   <?php echo $form->error($model,'admin_cancellation_threshold_delivery'); ?>   
</div>

<h5 class="card-title"><?php echo t("Status for order processing")?></h5>  
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'tracking_status_process', (array) $status_list,array(
     'class'=>"form-control custom-select form-control-select",
     'placeholder'=>$form->label($model,'tracking_status_process'),
   )); ?>         
   <?php echo $form->error($model,'tracking_status_process'); ?>
</div>

<h5 class="card-title"><?php echo t("Status for food ready")?></h5>  
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'tracking_status_ready', (array) $status_list,array(
     'class'=>"form-control custom-select form-control-select",
     'placeholder'=>$form->label($model,'tracking_status_ready'),
   )); ?>         
   <?php echo $form->error($model,'tracking_status_ready'); ?>
</div>

<h5 class="card-title"><?php echo t("Status for in transit")?></h5>  
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'tracking_status_in_transit', (array) $status_list,array(
     'class'=>"form-control custom-select form-control-select",
     'placeholder'=>$form->label($model,'tracking_status_in_transit'),
   )); ?>         
   <?php echo $form->error($model,'tracking_status_in_transit'); ?>
</div>

<h5 class="card-title"><?php echo t("Status for delivered")?></h5>  
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'tracking_status_delivered', (array) $status_list,array(
     'class'=>"form-control custom-select form-control-select",
     'placeholder'=>$form->label($model,'tracking_status_delivered'),
   )); ?>         
   <?php echo $form->error($model,'tracking_status_delivered'); ?>
</div>


<h5 class="card-title"><?php echo t("Status for delivery failed")?></h5>  
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'tracking_status_delivery_failed', (array) $status_list,array(
     'class'=>"form-control custom-select form-control-select",
     'placeholder'=>$form->label($model,'tracking_status_delivery_failed'),
   )); ?>         
   <?php echo $form->error($model,'tracking_status_delivery_failed'); ?>
</div>

<hr>

<h5 class="card-title"><?php echo t("Status for completed pickup/dinein order")?></h5>  
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'tracking_status_completed', (array) $status_list,array(
     'class'=>"form-control custom-select form-control-select",
     'placeholder'=>$form->label($model,'tracking_status_completed'),
   )); ?>         
   <?php echo $form->error($model,'tracking_status_completed'); ?>
</div>

<h5 class="card-title"><?php echo t("Status for failed pickup/dinein order")?></h5>  
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'tracking_status_failed', (array) $status_list,array(
     'class'=>"form-control custom-select form-control-select",
     'placeholder'=>$form->label($model,'tracking_status_failed'),
   )); ?>         
   <?php echo $form->error($model,'tracking_status_failed'); ?>
</div>
		
<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full",
'value'=>t("Save")
)); ?>
      
<?php $this->endWidget(); ?>
