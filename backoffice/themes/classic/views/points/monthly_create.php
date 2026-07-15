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
   <?php echo $form->textField($model,'meta_value',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>t("Points earning in month")
   )); ?>   
   <label for="AR_admin_meta_meta_value"><?php echo t("Points earning in month")?></label>   
   <?php echo $form->error($model,'meta_value'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'meta_value1',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>t("Bunos points")
   )); ?>   
   <label for="AR_admin_meta_meta_value1"><?php echo t("Bunos points").""?></label>   
   <?php echo $form->error($model,'meta_value1'); ?>
</div>

</div> <!--body-->
</div> <!--card-->


<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>