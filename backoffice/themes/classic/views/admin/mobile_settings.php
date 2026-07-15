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

<h6 class="mb-4"><?php echo t("Mobile Domain URL")?></h6>

<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"enabled_auto_pwa_redirect",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"enabled_auto_pwa_redirect",
     'checked'=>$model->enabled_auto_pwa_redirect==1?true:false
   )); ?>   
  <label class="custom-control-label" for="enabled_auto_pwa_redirect">
   <?php echo t("Auto Redirect to PWA url when using device")?>
  </label>
</div>    


<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"admin_addons_use_checkbox",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"admin_addons_use_checkbox",
     'checked'=>$model->admin_addons_use_checkbox==1?true:false
   )); ?>   
  <label class="custom-control-label" for="admin_addons_use_checkbox">
   <?php echo t("Menu addons use checkbox")?>
  </label>
</div>    

<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"admin_category_use_slide",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"admin_category_use_slide",
     'checked'=>$model->admin_category_use_slide==1?true:false
   )); ?>   
  <label class="custom-control-label" for="admin_category_use_slide">
   <?php echo t("Menu category use slide")?>
  </label>
</div>    

<div class="form-label-group mt-2">    
   <?php echo $form->textField($model,'webpush_certificates',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'webpush_certificates'),
   )); ?>   
   <?php    
    echo $form->labelEx($model,'webpush_certificates'); ?>
   <?php echo $form->error($model,'webpush_certificates'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'pwa_url',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'pwa_url'),
   )); ?>   
   <?php    
    echo $form->labelEx($model,'pwa_url'); ?>
   <?php echo $form->error($model,'pwa_url'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'android_download_url',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'android_download_url'),
   )); ?>   
   <?php    
    echo $form->labelEx($model,'android_download_url'); ?>
   <?php echo $form->error($model,'android_download_url'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'ios_download_url',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'ios_download_url'),
   )); ?>   
   <?php    
    echo $form->labelEx($model,'ios_download_url'); ?>
   <?php echo $form->error($model,'ios_download_url'); ?>
</div>

<h6 class="mb-4"><?php echo t("Mobile Version")?></h6>

<div class="form-label-group">    
   <?php echo $form->textField($model,'mobile_app_version_android',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'mobile_app_version_android'),
   )); ?>   
   <?php    
    echo $form->labelEx($model,'mobile_app_version_android'); ?>
   <?php echo $form->error($model,'mobile_app_version_android'); ?>
   <div class="text-muted"><?php echo t("example 1.0")?></div>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'mobile_app_version_ios',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'mobile_app_version_ios'),
   )); ?>   
   <?php    
    echo $form->labelEx($model,'mobile_app_version_ios'); ?>
   <?php echo $form->error($model,'mobile_app_version_ios'); ?>
   <div class="text-muted"><?php echo t("example 1.0")?></div>
</div>

<?php echo CHtml::submitButton('save',array(
'class'=>"btn btn-green btn-full",
'value'=>CommonUtility::t("Save")
)); ?>


<?php $this->endWidget(); ?>
