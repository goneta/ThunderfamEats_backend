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
   <?php echo $form->textField($model,'table_name',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'table_name')     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'table_name'); ?>
   <?php echo $form->error($model,'table_name'); ?>
</div>

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

<h6 class="mb-1"><?php echo t("Table Order Status")?></h6>
    <div class="form-label-group">    
    <?php echo $form->dropDownList($model,'table_status', (array)$table_status_list ,array(
        'class'=>"form-control custom-select form-control-select",     
        'placeholder'=>$form->label($model,'table_status'),
    )); ?>         
    <?php echo $form->error($model,'table_status'); ?>
</div>		

<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"available",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"available",
     'checked'=>$model->available==1?true:false
   )); ?>   
  <label class="custom-control-label" for="available">
   <?php echo t("Available")?>
  </label>
</div>    


</div> <!--body-->
</div> <!--card-->

  
<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>


<?php $this->endWidget(); ?>