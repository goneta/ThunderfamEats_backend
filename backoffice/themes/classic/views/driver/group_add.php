
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
		'id' => 'vue-uploader',
		'enableAjaxValidation' => false,		
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
<?php echo $form->textField($model,'group_name',array(
    'class'=>"form-control form-control-text",
    'placeholder'=>$form->label($model,'group_name')     
)); ?>   
<?php    
    echo $form->labelEx($model,'group_name'); ?>
<?php echo $form->error($model,'group_name'); ?>
</div>

<h6 class="mb-4 mt-4"><?php echo t("Select Drivers")?></h6>

<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'drivers', (array)$drivers,array(
     'class'=>"form-control custom-select form-control-select select_two",
     'multiple'=>true,
     'placeholder'=>$form->label($model,'drivers'),
   )); ?>         
   <?php echo $form->error($model,'drivers'); ?>
</div>


<h6 class="mb-4 mt-4"><?php echo t("Color Hex")?></h6>
<div class="form-label-group">    
   <?php echo $form->textField($model,'color_hex',array(
     'class'=>"form-control form-control-text colorpicker",
     'placeholder'=>$form->label($model,'color_hex'),
     'readonly'=>false
   )); ?>      
   <?php echo $form->error($model,'color_hex'); ?>
</div>

  </div> <!--body-->
</div> <!--card-->



<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>