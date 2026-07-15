<nav class="navbar navbar-light justify-content-between">
  <a class="navbar-brand">
  <h5><?php echo CHtml::encode($this->pageTitle)?></h5>
  </a>     
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

<div class="custom-control custom-switch custom-switch-md">  
    <?php echo $form->checkBox($model,"booking_time_format",array(
        'class'=>"custom-control-input checkbox_child",     
        'value'=>1,
        'id'=>"booking_time_format",
        'checked'=>$model->booking_time_format==1?true:false
    )); ?>   
    <label class="custom-control-label" for="booking_time_format">
    <?php echo t("24 hour time format")?>
    </label>
</div>  

<h5 class="mb-4 mt-3"><?php echo t("Templates")?></h5>

<h6 class="m-0 p-0"><?php echo $form->label($model,'booking_tpl_reservation_requested')?></h6>
<span class="text-muted"><?php echo t("Will sent to merchant if a reservation was place")?></span>
<div class="mb-1"></div>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'booking_tpl_reservation_requested', (array)$template_list ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'booking_tpl_reservation_requested'),
   )); ?>         
   <?php echo $form->error($model,'booking_tpl_reservation_requested'); ?>   
</div>		


<h6 class="mb-1"><?php echo $form->label($model,'booking_tpl_reservation_confirmed')?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'booking_tpl_reservation_confirmed', (array)$template_list ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'booking_tpl_reservation_confirmed'),
   )); ?>         
   <?php echo $form->error($model,'booking_tpl_reservation_confirmed'); ?>
</div>		

<h6 class="mb-1"><?php echo $form->label($model,'booking_tpl_reservation_canceled')?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'booking_tpl_reservation_canceled', (array)$template_list ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'booking_tpl_reservation_canceled'),
   )); ?>         
   <?php echo $form->error($model,'booking_tpl_reservation_canceled'); ?>
</div>		

<h6 class="mb-1"><?php echo $form->label($model,'booking_tpl_reservation_denied')?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'booking_tpl_reservation_denied', (array)$template_list ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'booking_tpl_reservation_denied'),
   )); ?>         
   <?php echo $form->error($model,'booking_tpl_reservation_denied'); ?>
</div>		

<h6 class="mb-1"><?php echo $form->label($model,'booking_tpl_reservation_finished')?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'booking_tpl_reservation_finished', (array)$template_list ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'booking_tpl_reservation_finished'),
   )); ?>         
   <?php echo $form->error($model,'booking_tpl_reservation_finished'); ?>
</div>		

<h6 class="mb-1"><?php echo $form->label($model,'booking_tpl_reservation_no_show')?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'booking_tpl_reservation_no_show', (array)$template_list ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'booking_tpl_reservation_no_show'),
   )); ?>         
   <?php echo $form->error($model,'booking_tpl_reservation_no_show'); ?>
</div>		


  </div> <!--body-->
</div> <!--card-->

<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>  