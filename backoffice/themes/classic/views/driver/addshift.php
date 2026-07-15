
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

<h6 class="mb-1"><?php echo $form->label($model,'zone_id')?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'zone_id', (array)$zone_list ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'zone_id'),
   )); ?>         
   <?php echo $form->error($model,'zone_id'); ?>
</div>		

<div class="form-label-group">    
<?php echo $form->textField($model,'date_shift',array(
    'class'=>"form-control form-control-text datepick",
    'placeholder'=>$form->label($model,'date_shift'),
	'readonly'=>true
)); ?>   
<?php    
    echo $form->labelEx($model,'date_shift'); ?>
<?php echo $form->error($model,'date_shift'); ?>
</div>


<h6 class="mb-1"><?php echo $form->label($model,'time_start')?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'time_start', (array)$time_range ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'time_start'),
   )); ?>         
   <?php echo $form->error($model,'time_start'); ?>
</div>		

<div class="form-label-group">    
<?php echo $form->textField($model,'date_shift_end',array(
    'class'=>"form-control form-control-text datepick2",
    'placeholder'=>$form->label($model,'date_shift_end'),
	   'readonly'=>true
)); ?>   
<?php    
    echo $form->labelEx($model,'date_shift_end'); ?>
<?php echo $form->error($model,'date_shift_end'); ?>
</div>

<h6 class="mb-1"><?php echo $form->label($model,'time_end')?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'time_end', (array)$time_range ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'time_end'),
   )); ?>         
   <?php echo $form->error($model,'time_end'); ?>
</div>		


<div class="form-label-group">    
<?php echo $form->textField($model,'max_allow_slot',array(
    'class'=>"form-control form-control-text",
    'placeholder'=>$form->label($model,'max_allow_slot')     
)); ?>   
<?php    
    echo $form->labelEx($model,'max_allow_slot'); ?>
<?php echo $form->error($model,'max_allow_slot'); ?>
<p class="text-muted"><?php echo t("Number of driver that can take this shift. default value is 0 for unlimited")?></p>
</div>

<h6 class="mb-4"><?php echo t("Status")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'status', (array) $status,array(
     'class'=>"form-control custom-select form-control-select",
     'placeholder'=>$form->label($model,'status'),
   )); ?>         
   <?php echo $form->error($model,'status'); ?>
</div>

  </div> <!--body-->
</div> <!--card-->



<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>