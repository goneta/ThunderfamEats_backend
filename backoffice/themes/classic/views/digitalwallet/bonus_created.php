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

<div class="form-label-group">    
   <?php echo $form->textField($model,'title',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'title')     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'title'); ?>
   <?php echo $form->error($model,'title'); ?>
</div>

<h6 class="mb-2 mt-4"><?php echo t("Bonus Type")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'discount_type', (array) $type_list,array(
     'class'=>"form-control custom-select form-control-select discount_type",
     'placeholder'=>$form->label($model,'discount_type'),
   )); ?>         
   <?php echo $form->error($model,'discount_type'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'amount',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'amount')     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'amount'); ?>
   <?php echo $form->error($model,'amount'); ?>
</div>


<div class="form-label-group">    
   <?php echo $form->textField($model,'minimum_amount',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'minimum_amount')     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'minimum_amount'); ?>
   <?php echo $form->error($model,'minimum_amount'); ?>
</div>


<div class="form-label-group">    
   <?php echo $form->textField($model,'start_date',array(
     'class'=>"form-control form-control-text datepick2",
     'readonly'=>true,
     'placeholder'=>$form->label($model,'start_date'),     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'start_date'); ?>
   <?php echo $form->error($model,'start_date'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'expiration_date',array(
     'class'=>"form-control form-control-text datepick",
     'readonly'=>true,
     'placeholder'=>$form->label($model,'expiration_date'),     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'expiration_date'); ?>
   <?php echo $form->error($model,'expiration_date'); ?>
</div>

<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"status",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"status",
     'checked'=>$model->status==1?true:false
   )); ?>   
  <label class="custom-control-label" for="status">
   <?php echo t("Publish")?>
  </label>
</div>    


  </div> <!--body-->
</div> <!--card-->



<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>