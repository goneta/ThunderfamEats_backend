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

<h6 class="mb-4"><?php echo t("Free Item")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'free_item_id',(array)$items,array(
     'class'=>"form-control custom-select form-control-select select_two_ajax",
     'placeholder'=>$form->label($model,'free_item_id'),   
     'action'=>'search_item'
   )); ?>         
   <?php echo $form->error($model,'free_item_id'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'minimum_cart_total',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'minimum_cart_total')     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'minimum_cart_total'); ?>
   <?php echo $form->error($model,'minimum_cart_total'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'max_free_quantity',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'max_free_quantity')     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'max_free_quantity'); ?>
   <?php echo $form->error($model,'max_free_quantity'); ?>
</div>

<div class="custom-control custom-switch custom-switch-md mb-2">  
  <?php echo $form->checkBox($model,"auto_add",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"auto_add",
     'checked'=>$model->auto_add==1?true:false
   )); ?>   
  <label class="custom-control-label" for="auto_add">
   <?php echo t("Auto Add to Cart")?>
  </label>
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
  
  
<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>


<?php $this->endWidget(); ?>