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
   <?php if($field_id=="meta_value1"):?>
	<?php echo $form->textArea($model,$id."[$field_id]",array(
     'class'=>"form-control custom-select form-control-select",     
   )); ?>       
   <?php else :?>
   <?php echo $form->dropDownList($model,$id."[$field_id]", (array) $status_list,array(
     'class'=>"form-control custom-select form-control-select",
     'placeholder'=>$form->label($model,$id."[$field_id]"),
   )); ?>       
   <?php endif?>     
   <?php echo $form->error($model,$id."[$field_id]"); ?>   
</div>
<hr class=""/>
<?php endforeach;?>
<?php endforeach;?>

		
<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full",
'value'=>t("Save")
)); ?>
      
<?php $this->endWidget(); ?>
