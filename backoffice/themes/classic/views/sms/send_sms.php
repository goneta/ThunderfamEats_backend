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

<?php 
if(isset($custom_link)){
  echo $custom_link;
}
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



<h6 class="mb-3 mt-4"><?php echo t("Enter mobile number that will receive test sms")?></h6>    
  <div class="form-label-group">    
   <?php echo $form->textField($model,'test_mobile_number',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'test_mobile_number'),               
   )); ?>   
   <?php echo $form->labelEx($model,'test_mobile_number'); ?>
   <?php echo $form->error($model,'test_mobile_number'); ?>
</div>

<p>
    <?php echo t("By clicking submit this will send test sms to the mobile number provided.")?>
    <br/>
    <?php echo t("if you cannot receive test sms check the following and change settings.")?>        
    <p><?php echo t("Change runactions settings as follows")?>.</p>
    <ul>
      <li><?php echo t("Runactions enabled + touchUrl")?></li>
      <li><?php echo t("Runactions enabled + touchUrlExt")?></li>
      <li><?php echo t("Runactions disabled + fastRequest")?></li>
    </ul>
</p>


</div> <!--body-->
</div> <!--card-->



<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Submit")
)); ?>

<?php $this->endWidget(); ?>