<nav class="navbar navbar-light justify-content-between">
  <div>
  <a class="navbar-brand">
     <h5><?php echo CHtml::encode($this->pageTitle)?></h5>
  </a>     
  </div>  
</nav>

<?php
$form = $this->beginWidget(
	'CActiveForm',
	array(
		'id' => 'active-form',
		'enableAjaxValidation' => false,
		'htmlOptions' => array('enctype' => 'multipart/form-data', 'autocomplete'=>"off"),
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

<?php if(is_array($provider) && count($provider)>=1):?>
<?php foreach ($provider as $payment_code=>$payment_name):
?>

<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"block_payment_method[$payment_code]",array(
     'class'=>"custom-control-input checkbox_child",     
     'id'=>"block_payment_method[$payment_code]",
     'value'=>$payment_code,
     'checked'=>in_array($payment_code,(array)$model->block_payment_method)?true:false
   )); ?>   
  <label class="custom-control-label" for="block_payment_method[<?php echo $payment_code?>]">
   <?php echo $payment_name?>
  </label>
</div>    

<?php endforeach;?>
<?php endif;?>


</div>
<!-- card-body -->
</div>
<!-- card -->


<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>