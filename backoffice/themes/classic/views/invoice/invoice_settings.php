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

<h5><?php echo t("Invoice Payment Information")?></h5>


<div class="form-label-group">    
<?php echo $form->textField($model,'invoice_payment_bank_name',array(
    'class'=>"form-control form-control-text",
    'placeholder'=>$form->label($model,'invoice_payment_bank_name'),
)); ?>   
<?php    
    echo $form->labelEx($model,'invoice_payment_bank_name'); ?>
<?php echo $form->error($model,'invoice_payment_bank_name'); ?>
</div>

<div class="form-label-group">    
<?php echo $form->textField($model,'invoice_payment_bank_account_name',array(
    'class'=>"form-control form-control-text",
    'placeholder'=>$form->label($model,'invoice_payment_bank_account_name'),
)); ?>   
<?php    
    echo $form->labelEx($model,'invoice_payment_bank_account_name'); ?>
<?php echo $form->error($model,'invoice_payment_bank_account_name'); ?>
</div>

<div class="form-label-group">    
<?php echo $form->textField($model,'invoice_payment_bank_account_number',array(
    'class'=>"form-control form-control-text",
    'placeholder'=>$form->label($model,'invoice_payment_bank_account_number'),
)); ?>   
<?php    
    echo $form->labelEx($model,'invoice_payment_bank_account_number'); ?>
<?php echo $form->error($model,'invoice_payment_bank_account_number'); ?>
</div>

<div class="pt-2 pb-2"></div>

<h6 class="mb-3"><?php echo t("Invoice created template")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'invoice_created', (array)$template_list ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'invoice_created'),
   )); ?>         
   <?php echo $form->error($model,'invoice_created'); ?>
</div>

<h6 class="mb-3"><?php echo t("New upload deposit template")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'invoice_new_upload_deposit', (array)$template_list ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'invoice_new_upload_deposit'),
   )); ?>         
   <?php echo $form->error($model,'invoice_new_upload_deposit'); ?>
</div>

<h6 class="mb-3"><?php echo t("Cron jobs")?></h6>
<uL>
    <?php foreach ($cronjobs as $items):?>
    <li class="mb-2">
        <div class="text-bold font-weight-bold"><?php echo $items['value']?></div>
        <div class="text-muted"><?php echo $items['label']?></div>
    </li>
    <?php endforeach?>
</uL>


</div> <!--body-->
</div> <!--card-->


<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>
