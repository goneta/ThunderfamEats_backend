
<div class="container">
 
<div class="login-container m-auto pt-5 pb-5">

<h5 class="text-center mb-4"><?php echo t("Reset Password")?></h5>

<?php
$form = $this->beginWidget(
	'CActiveForm',
	array(
		'id' => 'upload-form',
		'enableAjaxValidation' => false,		
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


<div class="form-label-group">    
   <?php echo $form->passwordField($model,'new_password',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'new_password'),
   )); ?>   
   <?php    
    echo $form->labelEx($model,'new_password'); ?>
   <?php echo $form->error($model,'new_password'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->passwordField($model,'confirm_password',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'confirm_password'),
   )); ?>   
   <?php    
    echo $form->labelEx($model,'confirm_password'); ?>
   <?php echo $form->error($model,'confirm_password'); ?>
</div>

<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green w-100 mt-3",
'value'=>t("Submit")
)); ?>

<?php $this->endWidget(); ?>

</div> <!--login container-->

</div> <!--containter-->