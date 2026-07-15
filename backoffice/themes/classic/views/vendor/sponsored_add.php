<nav class="navbar navbar-light justify-content-between">
<?php
$this->widget('zii.widgets.CBreadcrumbs', $links);
?>
</nav>

<div class="card">
  <div class="card-body">
  
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

<!--<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'merchant_id',(array) $merchant_list,array(
     'class'=>"form-control custom-select form-control-select ",     
     'placeholder'=>$form->label($model,'merchant_id'),
   )); ?>         
   <?php echo $form->error($model,'merchant_id'); ?>
</div>-->
   
<h6 class="mb-4"><?php echo t("Merchant")?></h6>
<?php if($model->isNewRecord):?>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'merchant_id',(array)$selected_merchant,array(
     'class'=>"form-control custom-select form-control-select select_two_ajax",
     'placeholder'=>$form->label($model,'merchant_id'),
     'multiple'=>false,
     'action'=>'search_merchant'     
   )); ?>         
   <?php echo $form->error($model,'merchant_id'); ?>
</div>
<?php else :?>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'merchant_id',(array)$selected_merchant,array(
     'class'=>"form-control custom-select form-control-select",
     'placeholder'=>$form->label($model,'merchant_id'),
     'multiple'=>false,
     'action'=>'search_merchant',
     'readonly'=>true
   )); ?>         
   <?php echo $form->error($model,'merchant_id'); ?>
</div>
<?php endif;?>

 <div class="form-label-group">    
   <?php echo $form->textField($model,'sponsored_expiration',array(
     'class'=>"form-control form-control-text datepick",
     'placeholder'=>$form->label($model,'sponsored_expiration'),
     'readonly'=>true,     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'sponsored_expiration'); ?>
   <?php echo $form->error($model,'sponsored_expiration'); ?>
</div>

<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>
  
  
  </div> <!--body-->
</div> <!--card-->