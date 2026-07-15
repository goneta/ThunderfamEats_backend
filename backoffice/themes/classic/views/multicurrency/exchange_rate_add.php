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


<h6 class="mb-2"><?php echo t("Base currency")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'base_currency', (array)$list ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'base_currency'),
   )); ?>         
   <?php echo $form->error($model,'base_currency'); ?>
</div>		

<h6 class="mb-2"><?php echo t("To currency")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'currency_code', (array)$list ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'currency_code'),
   )); ?>         
   <?php echo $form->error($model,'currency_code'); ?>
</div>		

<div class="form-label-group">    
        <?php echo $form->textField($model,'exchange_rate',array(
        'class'=>"form-control form-control-text",
        'placeholder'=>$form->label($model,'exchange_rate')     
    )); ?>   
    <?php    
        echo $form->labelEx($model,'exchange_rate'); ?>
    <?php echo $form->error($model,'exchange_rate'); ?>
</div>


</div> <!--body-->
</div> <!--card-->



<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>