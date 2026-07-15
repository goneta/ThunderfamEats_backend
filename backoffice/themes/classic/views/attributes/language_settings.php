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

<h6 class="mb-4"><?php echo t("Show language selection")?></h6>


<div class="row mb-4">
  <div class="col-3">

  <h6 class="mb-2"><?php echo t("Backend")?></h6>
  <div class="custom-control custom-switch custom-switch-md">  
    <?php echo $form->checkBox($model,"enabled_language_bar",array(
      'class'=>"custom-control-input checkbox_child",     
      'value'=>1,
      'id'=>"enabled_language_bar",
      'checked'=>$model->enabled_language_bar==1?true:false
    )); ?>   
    <label class="custom-control-label" for="enabled_language_bar">
    <?php echo t("Enabled")?>
    </label>
  </div>    

  </div>
  <!-- col -->
  <div class="col-3">

  <h6 class="mb-2"><?php echo t("Front end")?></h6>
  <div class="custom-control custom-switch custom-switch-md">  
    <?php echo $form->checkBox($model,"enabled_language_bar_front",array(
      'class'=>"custom-control-input checkbox_child",     
      'value'=>1,
      'id'=>"enabled_language_bar_front",
      'checked'=>$model->enabled_language_bar_front==1?true:false
    )); ?>   
    <label class="custom-control-label" for="enabled_language_bar_front">
    <?php echo t("Enabled")?>
    </label>
  </div>    

  </div>
  <!-- col -->
  <div class="col-3">

  <h6 class="mb-2"><?php echo t("Customer Mobile App")?></h6>
  <div class="custom-control custom-switch custom-switch-md">  
    <?php echo $form->checkBox($model,"enabled_language_customer_app",array(
      'class'=>"custom-control-input checkbox_child",     
      'value'=>1,
      'id'=>"enabled_language_customer_app",
      'checked'=>$model->enabled_language_customer_app==1?true:false
    )); ?>   
    <label class="custom-control-label" for="enabled_language_customer_app">
    <?php echo t("Enabled")?>
    </label>
  </div>    

  </div>
  <!-- col -->
  <div class="col-3">

  <h6 class="mb-2"><?php echo t("Merchant App")?></h6>
  <div class="custom-control custom-switch custom-switch-md">  
    <?php echo $form->checkBox($model,"enabled_language_merchant_app",array(
      'class'=>"custom-control-input checkbox_child",     
      'value'=>1,
      'id'=>"enabled_language_merchant_app",
      'checked'=>$model->enabled_language_merchant_app==1?true:false
    )); ?>   
    <label class="custom-control-label" for="enabled_language_merchant_app">
    <?php echo t("Enabled")?>
    </label>
  </div>    

  </div>
  <!-- col -->
</div>
<!-- row -->


<div class="row">
  <div class="col-3">
  
  <h6 class="mb-2"><?php echo t("Rider App")?></h6>
  <div class="custom-control custom-switch custom-switch-md">  
    <?php echo $form->checkBox($model,"enabled_language_rider_app",array(
      'class'=>"custom-control-input checkbox_child",     
      'value'=>1,
      'id'=>"enabled_language_rider_app",
      'checked'=>$model->enabled_language_rider_app==1?true:false
    )); ?>   
    <label class="custom-control-label" for="enabled_language_rider_app">
    <?php echo t("Enabled")?>
    </label>
  </div>    


  </div>
  <!-- col -->
   
  <div class="col-3">

  <h6 class="mb-2"><?php echo t("Tableside App")?></h6>
  <div class="custom-control custom-switch custom-switch-md">  
    <?php echo $form->checkBox($model,"enabled_language_tableside_app",array(
      'class'=>"custom-control-input checkbox_child",     
      'value'=>1,
      'id'=>"enabled_language_tableside_app",
      'checked'=>$model->enabled_language_tableside_app==1?true:false
    )); ?>   
    <label class="custom-control-label" for="enabled_language_tableside_app">
    <?php echo t("Enabled")?>
    </label>
  </div>    
    
  </div>
  <!-- col -->

  <div class="col-3">
  
  <h6 class="mb-2"><?php echo t("Kicthen App")?></h6>
  <div class="custom-control custom-switch custom-switch-md">  
    <?php echo $form->checkBox($model,"enabled_language_kicthen_app",array(
      'class'=>"custom-control-input checkbox_child",     
      'value'=>1,
      'id'=>"enabled_language_kicthen_app",
      'checked'=>$model->enabled_language_kicthen_app==1?true:false
    )); ?>   
    <label class="custom-control-label" for="enabled_language_kicthen_app">
    <?php echo t("Enabled")?>
    </label>
  </div>    

  </div>
  <!-- col -->
</div>
<!-- row -->



<h6 class="mb-2 mt-4"><?php echo t("Default language")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'default_language', (array) $language_list ,array(
     'class'=>"form-control custom-select form-control-select",
     'placeholder'=>$form->label($model,'default_language'),
   )); ?>         
   <?php echo $form->error($model,'default_language'); ?>
</div>


  </div> <!--body-->
</div> <!--card-->


<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>