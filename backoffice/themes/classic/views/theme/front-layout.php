<nav class="navbar navbar-light justify-content-between">
<?php
$this->widget('zii.widgets.CBreadcrumbs',$links);
?>
</nav>


<DIV id="vue-theme-menu" class="card">
 <div class="card-body">

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


<h6 class="mb-4"><?php echo t("Home page")?></h6>

<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"enabled_home_steps",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"enabled_home_steps",
     'checked'=>$model->enabled_home_steps==1?true:false
   )); ?>   
  <label class="custom-control-label" for="enabled_home_steps">
   <?php echo t("Enabled front steps sections")?>
  </label>
</div>    

<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"enabled_home_promotional",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"enabled_home_promotional",
     'checked'=>$model->enabled_home_promotional==1?true:false
   )); ?>   
  <label class="custom-control-label" for="enabled_home_promotional">
   <?php echo t("Enabled Promotion section")?>
  </label>
</div>    

<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"enabled_signup_section",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"enabled_signup_section",
     'checked'=>$model->enabled_signup_section==1?true:false
   )); ?>   
  <label class="custom-control-label" for="enabled_signup_section">
   <?php echo t("Enabled Signup section")?>
  </label>
</div>    

<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"enabled_mobileapp_section",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"enabled_mobileapp_section",
     'checked'=>$model->enabled_mobileapp_section==1?true:false
   )); ?>   
  <label class="custom-control-label" for="enabled_mobileapp_section">
   <?php echo t("Enabled Mobileapp section")?>
  </label>
</div>    


<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"enabled_social_links",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"enabled_social_links",
     'checked'=>$model->enabled_social_links==1?true:false
   )); ?>   
  <label class="custom-control-label" for="enabled_social_links">
   <?php echo t("Enabled Social links")?>
  </label>
</div>    

<div class="pt-3"></div>

<?php echo CHtml::submitButton('save',array(
'class'=>"btn btn-green btn-full",
'value'=>CommonUtility::t("Save")
)); ?>

<?php $this->endWidget(); ?>
 
  
</div>
</DIV>