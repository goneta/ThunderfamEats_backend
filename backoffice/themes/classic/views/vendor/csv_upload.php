<div class="row">
<div class="col-md-6">
<?php
$this->widget('zii.widgets.CBreadcrumbs', $links);
?>
</div> <!--col-->

<div class="col-md-6">
</div> <!--col-->
</div><!-- row-->


<div class="card">
  <div class="card-body">
  
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
   
<div class="input-group mb-3">  
  <div class="custom-file">    
    <?php echo $form->fileField($model, 'image',array(
     'class'=>"custom-file-input"
    ));?>
    <label class="custom-file-label" for="inputGroupFile01"><?php echo $form->labelEx($model, 'image')?></label>
  </div>  
</div>
<?php echo $form->error($model, 'image');?>

<div class="row text-left mt-4">
<div class="col-md-12 m-0">
<?php echo CHtml::submitButton('Login',array(
'class'=>"btn btn-green btn-full",
'value'=>CommonUtility::t("Upload")
)); ?>
</div>
</div>

<?php $this->endWidget(); ?>
  
  
  </div> <!--body-->
</div> <!--card-->