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

<h6 class="mb-4"><?php echo $this->pageTitle?></h6>


<div class="custom-control custom-switch custom-switch-md mb-2">  
  <?php echo $form->checkBox($model,"chat_enabled",array(
     'class'=>"custom-control-input chat_enabled",     
     'value'=>1,
     'id'=>"chat_enabled",
     'checked'=>$model->chat_enabled==1?true:false
   )); ?>   
  <label class="custom-control-label" for="chat_enabled">
   <?php echo t("Enabled chat")?>
  </label>
</div>    


<div class="custom-control custom-switch custom-switch-md mb-2">  
  <?php echo $form->checkBox($model,"chat_enabled_merchant_delete_chat",array(
     'class'=>"custom-control-input chat_enabled_merchant_delete_chat",     
     'value'=>1,
     'id'=>"chat_enabled_merchant_delete_chat",
     'checked'=>$model->chat_enabled_merchant_delete_chat==1?true:false
   )); ?>   
  <label class="custom-control-label" for="chat_enabled_merchant_delete_chat">
   <?php echo t("Enabled chat deletion")?>
  </label>
</div>    

   
</div> <!--body-->
</div> <!--card-->



<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>