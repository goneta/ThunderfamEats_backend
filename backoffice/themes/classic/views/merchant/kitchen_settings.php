
  
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


<h6><?php echo t("Low Workload")?></h6>
<div class="form-label-group">    
   <?php echo $form->textField($model,'low_workload_max_orders',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'low_workload_max_orders')     
   )); ?>   
   <?php    
    echo $form->label($model,'low_workload_max_orders'); ?>
   <?php echo $form->error($model,'low_workload_max_orders'); ?>   
</div>
<div class="form-label-group">    
   <?php echo $form->textField($model,'low_workload_extra_time',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'low_workload_extra_time')     
   )); ?>   
   <?php    
    echo $form->label($model,'low_workload_extra_time'); ?>
   <?php echo $form->error($model,'low_workload_extra_time'); ?>   
</div>

<h6><?php echo t("Medium Workload")?></h6>
<div class="form-label-group">    
   <?php echo $form->textField($model,'medium_workload_min_orders',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'medium_workload_min_orders')     
   )); ?>   
   <?php    
    echo $form->label($model,'medium_workload_min_orders'); ?>
   <?php echo $form->error($model,'medium_workload_min_orders'); ?>   
</div>
<div class="form-label-group">    
   <?php echo $form->textField($model,'medium_workload_max_orders',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'medium_workload_max_orders')     
   )); ?>   
   <?php    
    echo $form->label($model,'medium_workload_max_orders'); ?>
   <?php echo $form->error($model,'medium_workload_max_orders'); ?>   
</div>
<div class="form-label-group">    
   <?php echo $form->textField($model,'medium_workload_extra_time',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'medium_workload_extra_time')     
   )); ?>   
   <?php    
    echo $form->label($model,'medium_workload_extra_time'); ?>
   <?php echo $form->error($model,'medium_workload_extra_time'); ?>   
</div>

<h6><?php echo t("High Workload")?></h6>
<div class="form-label-group">    
   <?php echo $form->textField($model,'high_workload_min_orders',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'high_workload_min_orders')     
   )); ?>   
   <?php    
    echo $form->label($model,'high_workload_min_orders'); ?>
   <?php echo $form->error($model,'high_workload_min_orders'); ?>   
</div>
<div class="form-label-group">    
   <?php echo $form->textField($model,'high_workload_extra_time',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'high_workload_extra_time')     
   )); ?>   
   <?php    
    echo $form->label($model,'high_workload_extra_time'); ?>
   <?php echo $form->error($model,'high_workload_extra_time'); ?>   
</div>


  </div> <!--body-->
</div> <!--card-->



<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>