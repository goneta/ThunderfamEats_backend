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
	

<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"is_featured",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>2,
     'id'=>"is_featured",
     'checked'=>$model->is_featured==2?true:false
   )); ?>   
  <label class="custom-control-label" for="is_featured">
   <?php echo t("Featured")?>
  </label>
</div>    


<div class="row text-left mt-4">
<div class="col-md-12 m-0">
<?php echo CHtml::submitButton('save',array(
'class'=>"btn btn-green btn-full",
'value'=>CommonUtility::t("Save")
)); ?>
</div>
</div>

<?php $this->endWidget(); ?>
 