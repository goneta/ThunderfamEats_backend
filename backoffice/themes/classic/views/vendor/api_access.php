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

<?php if(!empty($model->meta_value)):?>
  <h6 class="mb-3 mt-4"><?php echo t("Merchant API BASE URL")?></h6>    
  <div class="form-label-group">    
   <?php echo $form->textField($model,'api_url',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'api_url'),     
     'disabled'=>true,
     'value'=>CommonUtility::getHomebaseUrl()
   )); ?>   
   <?php echo $form->labelEx($model,'api_url'); ?>
   <?php echo $form->error($model,'api_url'); ?>
</div>

<!-- <div class="form-label-group">    
   <?php echo $form->textField($model,'payment_api_url',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'payment_api_url'),     
     'disabled'=>true,
     'value'=>CommonUtility::getHomebaseUrl()."/pv1"
   )); ?>   
   <?php echo $form->labelEx($model,'payment_api_url'); ?>
   <?php echo $form->error($model,'payment_api_url'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'booking_api',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'booking_api'),     
     'disabled'=>true,
     'value'=>CommonUtility::getHomebaseUrl()."/apibooking"
   )); ?>   
   <?php echo $form->labelEx($model,'booking_api'); ?>
   <?php echo $form->error($model,'booking_api'); ?>
</div> -->

  <h6 class="mb-3 mt-4"><?php echo t("Merchant API Keys")?></h6>  
  <p class="text-muted"><?php echo t("Below is merchant api keys that you need to set in single website configurations")?></p>
  <div class="form-label-group mt-2">    
   <?php echo $form->textArea($model,'meta_value',array(
     'class'=>"form-control form-control-text",     
     'placeholder'=>t("Contact Content"),
     'disabled'=>true
   )); ?>      
   <?php echo $form->error($model,'meta_value'); ?>
   <a class="btn btn-link" href="<?php echo Yii::app()->createUrl("/vendor/delete_apikeys",['id'=>$id])?>"><?php echo t("Delete Keys")?></a>
</div>

<?php else :?>
<h6 class="mb-4 mt-4"><?php echo t("Website Domain URL address")?></h6>
 <div class="form-label-group">    
   <?php echo $form->textField($model,'website_url',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'website_url'),     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'website_url'); ?>
   <?php echo $form->error($model,'website_url'); ?>
</div>


<div class="row text-left mt-4">
<div class="col-md-12 m-0">
<?php echo CHtml::submitButton('save',array(
'class'=>"btn btn-green btn-full",
'value'=>CommonUtility::t("Generate API keys")
)); ?>
</div>
</div>
<?php endif?>

<?php $this->endWidget(); ?>
 