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

<h6 class="mb-2"><?php echo t("reCAPTCHA v2")?></h6>
<p class="text-muted"><?php echo t("Notice : this section need to be fill only if you have single website restaurant")?>.</p>

<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"merchant_captcha_enabled",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"merchant_captcha_enabled",
     'checked'=>$model->merchant_captcha_enabled==1?true:false
   )); ?>   
  <label class="custom-control-label" for="merchant_captcha_enabled">
   <?php echo t("Enabled")?>
  </label>
</div>    

<div class="form-label-group">    
   <?php echo $form->textField($model,'merchant_captcha_site_key',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'merchant_captcha_site_key'),
   )); ?>   
   <?php    
    echo $form->labelEx($model,'merchant_captcha_site_key'); ?>
   <?php echo $form->error($model,'merchant_captcha_site_key'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'merchant_captcha_secret',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'merchant_captcha_secret'),
   )); ?>   
   <?php    
    echo $form->labelEx($model,'merchant_captcha_secret'); ?>
   <?php echo $form->error($model,'merchant_captcha_secret'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'merchant_captcha_lang',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'merchant_captcha_lang'),
   )); ?>   
   <?php    
    echo $form->labelEx($model,'merchant_captcha_lang'); ?>
   <?php echo $form->error($model,'merchant_captcha_lang'); ?>
</div>

  </div> <!--body-->
</div> <!--card-->



<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>  