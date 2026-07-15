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


<h6 ><?php echo $form->label($model,'driver_cashout_fee')?></h6>
<div class="form-label-group">    
<?php echo $form->numberField($model,'driver_cashout_fee',array(
     'class'=>"form-control form-control-text",
   )); ?>      
   <?php echo $form->error($model,'driver_cashout_fee'); ?>
</div>

<h6 ><?php echo $form->label($model,'driver_cashout_minimum')?></h6>
<div class="form-label-group">    
<?php echo $form->numberField($model,'driver_cashout_minimum',array(
     'class'=>"form-control form-control-text",
   )); ?>      
   <?php echo $form->error($model,'driver_cashout_minimum'); ?>
</div>

<h6 ><?php echo $form->label($model,'driver_cashout_miximum')?></h6>
<div class="form-label-group">    
<?php echo $form->numberField($model,'driver_cashout_miximum',array(
     'class'=>"form-control form-control-text",
   )); ?>      
   <?php echo $form->error($model,'driver_cashout_miximum'); ?>
</div>

<h6 ><?php echo $form->label($model,'driver_cashout_request_limit')?></h6>
<div class="form-label-group">    
<?php echo $form->numberField($model,'driver_cashout_request_limit',array(
     'class'=>"form-control form-control-text",
   )); ?>      
   <?php echo $form->error($model,'driver_cashout_request_limit'); ?>   
</div>
   
</div> <!--body-->
</div> <!--card-->



<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>