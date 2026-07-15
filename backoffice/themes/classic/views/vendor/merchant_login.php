<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'frm-merchant',
	'enableAjaxValidation'=>false,
)); ?>

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
   <?php echo $form->textField($model,'first_name',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'first_name'),     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'first_name'); ?>
   <?php echo $form->error($model,'first_name'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'last_name',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'last_name'),     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'last_name'); ?>
   <?php echo $form->error($model,'last_name'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'contact_email',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'contact_email'),     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'contact_email'); ?>
   <?php echo $form->error($model,'contact_email'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'contact_number',array(
     'class'=>"form-control form-control-text mask_mobile",
     'placeholder'=>$form->label($model,'contact_number'),     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'contact_number'); ?>
   <?php echo $form->error($model,'contact_number'); ?>
</div>


 <div class="form-label-group">    
   <?php echo $form->textField($model,'username',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'username'),     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'username'); ?>
   <?php echo $form->error($model,'username'); ?>
</div>
 
<div class="form-label-group">    
   <?php echo $form->passwordField($model,'new_password',array(
     'class'=>"form-control form-control-text",
     'autocomplete'=>"new-password",
     'placeholder'=>Yii::t("app","new_password")
   )); ?>
    <?php echo $form->labelEx($model,'new_password'); ?>
   <?php echo $form->error($model,'new_password'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->passwordField($model,'repeat_password',array(
     'class'=>"form-control form-control-text",
     'autocomplete'=>"repeat-password",
     'placeholder'=>Yii::t("app","repeat_password")
   )); ?>
    <?php echo $form->labelEx($model,'repeat_password'); ?>
   <?php echo $form->error($model,'repeat_password'); ?>
</div>

<h6 class="mb-4"><?php echo CommonUtility::t("Status")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'status',(array)$status,array(
     'class'=>"form-control custom-select form-control-select",
     'placeholder'=>$form->label($model,'status'),
   )); ?>         
   <?php echo $form->error($model,'status'); ?>
</div>

<div class="row text-left mt-4">
<div class="col-md-12 m-0">
<?php echo CHtml::submitButton('Login',array(
'class'=>"btn btn-green btn-full",
'value'=>CommonUtility::t("Save")
)); ?>
</div>
</div>

<?php $this->endWidget(); ?>
 