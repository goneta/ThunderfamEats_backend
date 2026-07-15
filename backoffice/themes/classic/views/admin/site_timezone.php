
  
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


<h6 class="mb-2"><?php echo t("Time Zone")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'website_timezone_new', (array)$timezone,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'website_timezone_new'),
   )); ?>         
   <?php echo $form->error($model,'website_timezone_new'); ?>
</div>		

<h6 class="mb-2"><?php echo t("Date Format")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'website_date_format_new', (array) $date_format,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'website_date_format_new'),
   )); ?>         
   <?php echo $form->error($model,'website_date_format_new'); ?>
</div>		


<h6 class="mb-2"><?php echo t("Time Format")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'website_time_format_new', (array)$time_format,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'website_time_format_new'),
   )); ?>         
   <?php echo $form->error($model,'website_time_format_new'); ?>
</div>		

<div class="custom-control custom-switch custom-switch-md">  
    <?php echo $form->checkBox($model,"website_twentyfour_format",array(
        'class'=>"custom-control-input checkbox_child",     
        'value'=>1,
        'id'=>"website_twentyfour_format",
        'checked'=>$model->website_twentyfour_format==1?true:false
    )); ?>   
    <label class="custom-control-label" for="website_twentyfour_format">
    <?php echo t("24 hour time format")?>
    </label>
</div>  

<h6 class="mb-2 mt-3"><?php echo t("Checkout Date/Time")?></h6>

<div class="form-label-group">    
   <?php echo $form->textField($model,'website_time_picker_interval',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'website_time_picker_interval')     
   )); ?>   
   <?php    
    echo $form->label($model,'website_time_picker_interval'); ?>
   <?php echo $form->error($model,'website_time_picker_interval'); ?>      
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'website_date_range',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'website_date_range')     
   )); ?>   
   <?php    
    echo $form->label($model,'website_date_range'); ?>
   <?php echo $form->error($model,'website_date_range'); ?>   
   <small class="form-text text-muted mb-2">
    <?php echo t("default is 7 days")?>
   </small>
</div>

  </div> <!--body-->
</div> <!--card-->



<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>