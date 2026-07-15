
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


<h5 class="pb-1"><?php echo t("Service account JSON file")?></h5>
<?php if(!empty($model->meta_value)): ?>
<div class="pb-4"><b><?php echo t("Current Uploaded File")?></b> : <?php echo $model->meta_value?></div>
<?php endif?>
<div class="form-group">
<?php 
echo $form->fileField($model, 'file',array(
    'class'=>"form-control-file"
));
echo $form->error($model, 'file');
?>
</div>


<p class="text-muted">
	<?php echo t("Important! Use one service account json file for all apps")?>
</p>

<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>
