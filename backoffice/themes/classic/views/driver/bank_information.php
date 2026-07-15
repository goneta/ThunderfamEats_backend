<?php
$form = $this->beginWidget(
	'CActiveForm',
	array(
		'id' => 'vue-uploader',
		'enableAjaxValidation' => false,		
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

<h6 class="mb-0"><?php echo t("Bank information")?></h6>
<p class="mb-4 text-muted"><?php echo t("Bank information to send payment")?>.</p>

<div class="form-label-group">    
        <?php echo $form->textField($model,'account_name',array(
        'class'=>"form-control form-control-text",
        'placeholder'=>$form->label($model,'account_name')     
    )); ?>   
    <?php    
        echo $form->labelEx($model,'account_name'); ?>
    <?php echo $form->error($model,'account_name'); ?>
</div>

<div class="form-label-group">    
        <?php echo $form->textField($model,'account_number_iban',array(
        'class'=>"form-control form-control-text",
        'placeholder'=>$form->label($model,'account_number_iban')     
    )); ?>   
    <?php    
        echo $form->labelEx($model,'account_number_iban'); ?>
    <?php echo $form->error($model,'account_number_iban'); ?>
</div>

<div class="form-label-group">    
        <?php echo $form->textField($model,'swift_code',array(
        'class'=>"form-control form-control-text",
        'placeholder'=>$form->label($model,'swift_code')     
    )); ?>   
    <?php    
        echo $form->labelEx($model,'swift_code'); ?>
    <?php echo $form->error($model,'swift_code'); ?>
</div>

<div class="form-label-group">    
        <?php echo $form->textField($model,'bank_name',array(
        'class'=>"form-control form-control-text",
        'placeholder'=>$form->label($model,'bank_name')     
    )); ?>   
    <?php    
        echo $form->labelEx($model,'bank_name'); ?>
    <?php echo $form->error($model,'bank_name'); ?>
</div>

<div class="form-label-group">    
        <?php echo $form->textField($model,'bank_branch',array(
        'class'=>"form-control form-control-text",
        'placeholder'=>$form->label($model,'bank_branch')     
    )); ?>   
    <?php    
        echo $form->labelEx($model,'bank_branch'); ?>
    <?php echo $form->error($model,'bank_branch'); ?>
</div>

<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>