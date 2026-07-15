<?php
$form = $this->beginWidget(
	'CActiveForm',
	array(
		'id' => 'active-form',
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

<div class="d-flex flex-row">
    <div class="p-2 font-weight-bold"><?php echo t("Platform")?>:</div>
    <div class="p-2"><?php echo $model->platform?></div>
</div>

<div class="d-flex flex-row">
    <div class="p-2 font-weight-bold"><?php echo t("Token")?>:</div>
    <div class="p-2"><?php echo $model->device_token?></div>
</div>

<div class="form-label-group">    
    <?php echo $form->textField($model,'title',array(
        'class'=>"form-control form-control-text",
        'placeholder'=>$form->label($model,'title')     
    )); ?>   
    <?php    
    echo $form->labelEx($model,'title'); ?>
    <?php echo $form->error($model,'title'); ?>
</div>

<div class="form-label-group mt-2">    
   <?php echo $form->textArea($model,'message',array(
     'class'=>"form-control form-control-text",     
     'placeholder'=>t("Message")
   )); ?>      
   <?php echo $form->error($model,'message'); ?>
</div>

<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Send Push")
)); ?>

<?php $this->endWidget(); ?>