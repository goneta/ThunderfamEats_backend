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


<h6 class="mb-1"><?php echo $form->label($model,'merchant_page_privacy_policy')?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'merchant_page_privacy_policy', (array)$page_list ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'merchant_page_privacy_policy'),
   )); ?>         
   <?php echo $form->error($model,'merchant_page_privacy_policy'); ?>
</div>		

<h6 class="mb-1"><?php echo $form->label($model,'merchant_page_terms')?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'merchant_page_terms', (array)$page_list ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'merchant_page_terms'),
   )); ?>         
   <?php echo $form->error($model,'merchant_page_terms'); ?>
</div>		

<h6 class="mb-1"><?php echo $form->label($model,'merchant_page_aboutus')?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'merchant_page_aboutus', (array)$page_list ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'merchant_page_aboutus'),
   )); ?>         
   <?php echo $form->error($model,'merchant_page_aboutus'); ?>
</div>		

 

  </div> <!--body-->
</div> <!--card-->

<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>  