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


<div class="form-label-group">    
   <?php echo $form->textField($model,'service_code',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'service_code'),
     'readonly'=>$model->isNewRecord?false:true
   )); ?>   
   <?php    
    echo $form->labelEx($model,'service_code'); ?>
   <?php echo $form->error($model,'service_code'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'service_name',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'service_name'),     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'service_name'); ?>
   <?php echo $form->error($model,'service_name'); ?>
</div>

<div class="row">
  <div class="col">

  <div class="form-label-group">    
   <?php echo $form->dropDownList($model,'charge_type', (array) $charge_type,array(
     'class'=>"form-control custom-select form-control-select",
     'placeholder'=>$form->label($model,'charge_type'),
   )); ?>         
   <?php echo $form->error($model,'charge_type'); ?>
  </div>

  </div>
  <div class="col">

  <div class="form-label-group">    
    <?php echo $form->textField($model,'service_fee',array(
      'class'=>"form-control form-control-text",
      'placeholder'=>$form->label($model,'service_fee'),     
    )); ?>   
    <?php    
      echo $form->labelEx($model,'service_fee'); ?>
    <?php echo $form->error($model,'service_fee'); ?>
  </div>

  </div>
</div>
<!-- row -->


<div class="row">
  <div class="col">

  <div class="form-label-group">    
    <?php echo $form->textField($model,'small_order_fee',array(
      'class'=>"form-control form-control-text",
      'placeholder'=>$form->label($model,'small_order_fee'),     
    )); ?>   
    <?php    
      echo $form->labelEx($model,'small_order_fee'); ?>
    <?php echo $form->error($model,'small_order_fee'); ?>
  </div>

  </div>
  <div class="col">

  <div class="form-label-group">    
    <?php echo $form->textField($model,'small_less_order_based',array(
      'class'=>"form-control form-control-text",
      'placeholder'=>$form->label($model,'small_less_order_based'),     
    )); ?>   
    <?php    
      echo $form->labelEx($model,'small_less_order_based'); ?>
    <?php echo $form->error($model,'small_less_order_based'); ?>
  </div>

  </div>
</div>
<!-- row -->


<h6 class="mb-0 mt-2"><?php echo t("Registration Message")?></h6>
<p class="text-muted m-0 mb-2"><?php echo t("this message will show on front end during merchant registration")?>.</p>
<div class="form-label-group mt-2">    
   <?php echo $form->textArea($model,'description',array(
     'class'=>"form-control form-control-text",        
   )); ?>      
   <?php echo $form->error($model,'description'); ?>
</div>


<h6 class="mb-4 mt-4"><?php echo t("Background Color Hex")?></h6>
<div class="form-label-group">    
   <?php echo $form->textField($model,'color_hex',array(
     'class'=>"form-control form-control-text colorpicker",
     'placeholder'=>$form->label($model,'color_hex'),
     'readonly'=>true
   )); ?>      
   <?php echo $form->error($model,'color_hex'); ?>
</div>

<h6 class="mb-4 mt-4"><?php echo t("Font Color Hex")?></h6>
<div class="form-label-group">    
   <?php echo $form->textField($model,'font_color_hex',array(
     'class'=>"form-control form-control-text colorpicker",
     'placeholder'=>$form->label($model,'font_color_hex'),
     'readonly'=>true
   )); ?>      
   <?php echo $form->error($model,'font_color_hex'); ?>
</div>


<h6 class="mb-4"><?php echo t("Status")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'status', (array) $status,array(
     'class'=>"form-control custom-select form-control-select",
     'placeholder'=>$form->label($model,'status'),
   )); ?>         
   <?php echo $form->error($model,'status'); ?>
</div>

  </div> <!--body-->
</div> <!--card-->


<!--TRANSLATION-->
<?php if($multi_language && is_array($language) && count($language)>=1 ):?>
<?php 
$this->widget('application.components.WidgetTranslation',array(
  'form'=>$form,
  'model'=>$model,
  'language'=>$language,
  'field'=>$fields,
  'data'=>$data
));
?>   
<?php endif;?>
<!--END TRANSLATION-->	


<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>