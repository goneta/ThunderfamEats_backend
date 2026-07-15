<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'frm-merchant',
	'enableAjaxValidation'=>false,
)); ?>

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


<h5 class="card-title"><?php echo t("Status (Send to Kitchen)")?></h5>  
<p><?php echo t("When the status is set to (e.g., 'Accepted'), the order will automatically be sent to the kitchen for preparation")?>.</p>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'tableside_send_status', (array) $status_list,array(
     'class'=>"form-control custom-select form-control-select",
     'placeholder'=>$form->label($model,'tableside_send_status'),
   )); ?>         
   <?php echo $form->error($model,'tableside_send_status'); ?>
</div>

<!-- <h5 class="card-title"><?php echo t("Status (Kitchen Auto print)")?></h5>  
<p><?php echo t("When this status is selected (e.g., 'Accepted'), the order will automatically be printed in the kitchen, initiating the preparation process without requiring manual printing by the staff")?>.</p>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'tableside_auto_print_status', (array) $status_list,array(
     'class'=>"form-control custom-select form-control-select",
     'placeholder'=>$form->label($model,'tableside_auto_print_status'),
   )); ?>         
   <?php echo $form->error($model,'tableside_auto_print_status'); ?>
</div> -->


<div class="row text-left mt-4">
<div class="col-md-12 m-0">
<?php echo CHtml::submitButton('save',array(
'class'=>"btn btn-green btn-full",
'value'=>CommonUtility::t("Save")
)); ?>
</div>
</div>


</div>
</div>

<?php $this->endWidget(); ?>