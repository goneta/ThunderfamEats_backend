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


<h6 class="mb-1"><?php echo $form->label($model,'room_id')?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'room_id',(array)$model->room_id_selected,array(
     'class'=>"form-control custom-select form-control-select select_two_ajax",
     'placeholder'=>$form->label($model,'room_id'),   
     'action'=>'searchTableroom'
   )); ?>         
   <?php echo $form->error($model,'room_id'); ?>
</div>


<div class="form-label-group">    
   <?php echo $form->textField($model,'number_of_tables',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'number_of_tables')     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'number_of_tables'); ?>
   <?php echo $form->error($model,'number_of_tables'); ?>
</div>


<div class="form-label-group">    
   <?php echo $form->textField($model,'min_covers',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'min_covers')     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'min_covers'); ?>
   <?php echo $form->error($model,'min_covers'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'max_covers',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'max_covers')     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'max_covers'); ?>
   <?php echo $form->error($model,'max_covers'); ?>
</div>

</div> <!--body-->
</div> <!--card-->

  
<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>


<?php $this->endWidget(); ?>