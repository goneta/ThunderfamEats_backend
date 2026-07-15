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

<h6 class="mb-1"><?php echo $form->label($model,'driver_id')?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'driver_id',array(),array(
     'class'=>"form-control custom-select form-control-select select_two_ajax",
     'placeholder'=>$form->label($model,'driver_id'),   
     'action'=>'searchDriver',
     'id'=>"driver_id_ca"
   )); ?>         
   <?php echo $form->error($model,'driver_id'); ?>
</div>

<div class="ca_balance_wrap hidden">
  <h6>Wallet Balance : <span class="ca_balance"></span></h6>
</div>


<div class="form-label-group">    
        <?php echo $form->textField($model,'amount_collected',array(
        'class'=>"form-control form-control-text",
        'placeholder'=>$form->label($model,'amount_collected')     
    )); ?>   
    <?php    
        echo $form->labelEx($model,'amount_collected'); ?>
    <?php echo $form->error($model,'amount_collected'); ?>
</div>


<div class="form-label-group">    
        <?php echo $form->textField($model,'reference_id',array(
        'class'=>"form-control form-control-text",
        'placeholder'=>$form->label($model,'reference_id')     
    )); ?>   
    <?php    
        echo $form->labelEx($model,'reference_id'); ?>
    <?php echo $form->error($model,'reference_id'); ?>
</div>

  </div> <!--body-->
</div> <!--card-->



<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>