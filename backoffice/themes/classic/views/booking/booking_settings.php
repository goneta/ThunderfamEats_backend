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
  <?php echo $form->checkBox($model,"booking_enabled",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"booking_enabled",
     'checked'=>$model->booking_enabled==1?true:false
   )); ?>   
  <label class="custom-control-label" for="booking_enabled">
   <?php echo $form->label($model,'booking_enabled')?>
  </label>
</div>    

<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"booking_enabled_capcha",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"booking_enabled_capcha",
     'checked'=>$model->booking_enabled_capcha==1?true:false
   )); ?>   
  <label class="custom-control-label" for="booking_enabled_capcha">
   <?php echo $form->label($model,'booking_enabled_capcha')?>
  </label>
</div>    



<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"booking_allowed_choose_table",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"booking_allowed_choose_table",
     'checked'=>$model->booking_allowed_choose_table==1?true:false
   )); ?>   
  <label class="custom-control-label" for="booking_allowed_choose_table">
   <?php echo $form->label($model,'booking_allowed_choose_table')?>
  </label>
</div>    

<h6 class="mb-1"><?php echo $form->label($model,'booking_reservation_custom_message')?></h6>
  <div class="form-label-group mt-2">    
   <?php echo $form->textArea($model,'booking_reservation_custom_message',array(
     'class'=>"form-control form-control-text",          
   )); ?>      
   <?php echo $form->error($model,'booking_reservation_custom_message'); ?>   
</div>

<h6 class="mb-1"><?php echo $form->label($model,'booking_reservation_terms')?></h6>
  <div class="form-label-group mt-2">    
   <?php echo $form->textArea($model,'booking_reservation_terms',array(
     'class'=>"form-control form-control-text",          
   )); ?>      
   <?php echo $form->error($model,'booking_reservation_terms'); ?>   
</div>




  </div> <!--body-->
</div> <!--card-->

<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>  