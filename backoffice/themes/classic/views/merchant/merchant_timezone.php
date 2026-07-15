
  
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


<h6 class="mt-4"><?php echo t("Set Your Time Zone")?></h6>

<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'merchant_timezone', (array)$timezone,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'merchant_timezone'),
   )); ?>         
   <?php echo $form->error($model,'merchant_timezone'); ?>
</div>		


<div class="form-label-group">    
   <?php echo $form->textField($model,'merchant_time_picker_interval',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'merchant_time_picker_interval')     
   )); ?>   
   <?php    
    echo $form->label($model,'merchant_time_picker_interval'); ?>
   <?php echo $form->error($model,'merchant_time_picker_interval'); ?>   
</div>


  </div> <!--body-->
</div> <!--card-->



<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>