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

<h6 class="mb-3"><?php echo t("Firestore Database configuration")?></h6>

<div class="form-label-group">    
   <?php echo $form->textField($model,'firebase_apikey',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'firebase_apikey'),               
   )); ?>   
   <?php echo $form->labelEx($model,'firebase_apikey'); ?>
   <?php echo $form->error($model,'firebase_apikey'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'firebase_domain',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'firebase_domain'),               
   )); ?>   
   <?php echo $form->labelEx($model,'firebase_domain'); ?>
   <?php echo $form->error($model,'firebase_domain'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'firebase_projectid',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'firebase_projectid'),               
   )); ?>   
   <?php echo $form->labelEx($model,'firebase_projectid'); ?>
   <?php echo $form->error($model,'firebase_projectid'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'firebase_storagebucket',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'firebase_storagebucket'),               
   )); ?>   
   <?php echo $form->labelEx($model,'firebase_storagebucket'); ?>
   <?php echo $form->error($model,'firebase_storagebucket'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'firebase_messagingid',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'firebase_messagingid'),               
   )); ?>   
   <?php echo $form->labelEx($model,'firebase_messagingid'); ?>
   <?php echo $form->error($model,'firebase_messagingid'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'firebase_appid',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'firebase_appid'),               
   )); ?>   
   <?php echo $form->labelEx($model,'firebase_appid'); ?>
   <?php echo $form->error($model,'firebase_appid'); ?>
</div>
   
</div> <!--body-->
</div> <!--card-->



<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>