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


<h6><?php echo t("Days of week");?></h6>
<div class="row">  
<?php foreach ($day_list as $day=>$day_name):?>
<div class="col-3">
    <div class="custom-control custom-switch custom-switch-md">  
    <?php echo $form->checkBox($model,"days_of_week[$day]",array(
        'class'=>"custom-control-input checkbox_child",     
        'id'=>"days_of_week[$day]",
        'value'=>$day,
        'checked'=>in_array($day,(array)$model->days_of_week)?true:false
    )); ?>   
    <label class="custom-control-label" for="days_of_week[<?php echo $day?>]">
    <?php echo $day_name?>
    </label>
    </div>    
</div>    

<?php endforeach?>
</div>


<div class="form-label-group">    
   <?php echo $form->textField($model,'shift_name',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'shift_name')     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'shift_name'); ?>
   <?php echo $form->error($model,'shift_name'); ?>
</div>

<div class="p-1"></div>


<h6 class="mb-1"><?php echo $form->label($model,'first_seating')?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'first_seating', (array)$time_range ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'first_seating'),
   )); ?>         
   <?php echo $form->error($model,'first_seating'); ?>
</div>		

<h6 class="mb-1"><?php echo $form->label($model,'last_seating')?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'last_seating', (array)$time_range ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'last_seating'),
   )); ?>         
   <?php echo $form->error($model,'last_seating'); ?>
</div>		

<h6 class="mb-1"><?php echo $form->label($model,'shift_interval')?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'shift_interval', (array)$time_interval ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'shift_interval'),
   )); ?>         
   <?php echo $form->error($model,'shift_interval'); ?>
</div>		


<h6 class="mb-1"><?php echo t("Status")?></h6>
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