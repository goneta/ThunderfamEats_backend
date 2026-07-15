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

<?php foreach ($fields as $id => $items):?>
<?php foreach ($items as $field_id => $label): ?>
<h5 class="card-title"><?php echo $label?></h5>  
<div class="form-label-group">       
<?php echo $form->dropDownList($model,$id."[$field_id]", (array) $page_list,array(
     'class'=>"form-control custom-select form-control-select",
     'placeholder'=>$form->label($model,$id."[$field_id]"),
   )); ?>       
   <?php echo $form->error($model,$id."[$field_id]"); ?>   
</div>
<?php endforeach;?>
<?php endforeach;?>
      


</div> <!--body-->
</div> <!--card-->

<div style="height: 100px;"></div>

<div class="float-button  text-center p-4">
<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>
</div>

<?php $this->endWidget(); ?>