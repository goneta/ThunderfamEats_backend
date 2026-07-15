
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

<h6 class="mb-2"><?php echo t("Mobile Store Information")?></h6>

<div class="form-label-group">    
   <?php echo $form->textField($model,'merchant_mobile_webpush_certificates',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'merchant_mobile_webpush_certificates'),
   )); ?>   
   <?php    
    echo $form->labelEx($model,'merchant_mobile_webpush_certificates'); ?>
   <?php echo $form->error($model,'merchant_mobile_webpush_certificates'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'merchant_android_download_url',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'merchant_android_download_url'),
   )); ?>   
   <?php    
    echo $form->labelEx($model,'merchant_android_download_url'); ?>
   <?php echo $form->error($model,'merchant_android_download_url'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'merchant_ios_download_url',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'merchant_ios_download_url'),
   )); ?>   
   <?php    
    echo $form->labelEx($model,'merchant_ios_download_url'); ?>
   <?php echo $form->error($model,'merchant_ios_download_url'); ?>
</div>

<h6 class="mb-2"><?php echo t("Mobile Version")?></h6>


<div class="form-label-group">    
   <?php echo $form->textField($model,'merchant_mobile_app_version_android',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'merchant_mobile_app_version_android'),
   )); ?>   
   <?php    
    echo $form->labelEx($model,'merchant_mobile_app_version_android'); ?>
   <?php echo $form->error($model,'merchant_mobile_app_version_android'); ?>
   <div class="text-muted"><?php echo t("example 1.0")?></div>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'merchant_mobile_app_version_ios',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'merchant_mobile_app_version_ios'),
   )); ?>   
   <?php    
    echo $form->labelEx($model,'merchant_mobile_app_version_ios'); ?>
   <?php echo $form->error($model,'merchant_mobile_app_version_ios'); ?>
   <div class="text-muted"><?php echo t("example 1.0")?></div>
</div>

<?php echo CHtml::submitButton('save',array(
'class'=>"btn btn-green btn-full",
'value'=>CommonUtility::t("Save")
)); ?>


<?php $this->endWidget(); ?>
