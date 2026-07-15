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

<div class="form-label-group">    
   <?php echo $form->textField($model,'title',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'title'),     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'title'); ?>
   <?php echo $form->error($model,'title'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'meta_title',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'meta_title'),     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'meta_title'); ?>
   <?php echo $form->error($model,'meta_title'); ?>
</div>

<h6 class="mb-1 mt-2"><?php echo t("Meta Description")?></h6>
<div class="form-label-group mt-2">    
   <?php echo $form->textArea($model,'meta_description',array(
     'class'=>"form-control form-control-text",
     'style'=>"height:90px;"
   )); ?>      
   <?php echo $form->error($model,'meta_description'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'meta_keywords',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'meta_keywords'),     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'meta_keywords'); ?>
   <?php echo $form->error($model,'meta_keywords'); ?>
</div>


<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'status', (array)$status_list,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'status'),
   )); ?>         
   <?php echo $form->error($model,'status'); ?>
</div>		

</div> <!--body-->
</div> <!--card-->

<?php 
$this->widget('application.components.WidgetTranslation',array(
  'form'=>$form,
  'model'=>$model,
  'language'=>$language,
  'field'=>$fields,
  'data'=>$data
));
?>  


<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>