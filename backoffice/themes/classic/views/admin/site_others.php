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

<h6 class="mb-4"><?php echo t("Others")?></h6>




<h6 class="mb-1"><?php echo t("Allow return to home")?></h6>
<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"allow_return_home",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"allow_return_home",
     'checked'=>$model->allow_return_home==1?true:false
   )); ?>   
  <label class="custom-control-label" for="allow_return_home">
   <?php echo t("Enabled")?>
  </label>
</div>    

<h6 class="mb-1 mt-4"><?php echo t("Image resizing")?></h6>
<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"image_resizing",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"image_resizing",
     'checked'=>$model->image_resizing==1?true:false
   )); ?>   
  <label class="custom-control-label" for="image_resizing">
   <?php echo t("Enabled")?>
  </label>
</div>    


<h6 class="mb-1 mt-4"><?php echo t("Maintenance Mode")?></h6>
<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"maintenance_mode",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"maintenance_mode",
     'checked'=>$model->maintenance_mode==1?true:false
   )); ?>   
  <label class="custom-control-label" for="maintenance_mode">
   <?php echo t("Enabled")?>
  </label>
</div>    


<div class="row align-items-center">
  <div class="col-6">
     <h6 class="mb-1 mt-3"><?php echo t("Runactions")?></h6>
  </div>
  <div class="col-6 text-right">
     <a class="btn btn-link"  href="<?php echo $test_runactions?>">
      <?php echo t("Test Runactions")?>
    </a>
  </div>
</div>

<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"runactions_enabled",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"runactions_enabled",
     'checked'=>$model->runactions_enabled==1?true:false
   )); ?>   
  <label class="custom-control-label" for="runactions_enabled">
   <?php echo t("Enabled runactions")?>
  </label>
</div>    

<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'runactions_method', (array)$runactions_method ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'runactions_method'),
   )); ?>         
   <?php echo $form->error($model,'runactions_method'); ?>
</div>

<h6 class="mb-3"><?php echo t("Runactions Test Template")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'runaction_test_tpl', (array)$template_list ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'runaction_test_tpl'),
   )); ?>         
   <?php echo $form->error($model,'runaction_test_tpl'); ?>
</div>		


  </div> <!--body-->
</div> <!--card-->



<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>