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



<h6 class="mb-3 mt-4"><?php echo t("Enter email address that will receive test email")?></h6>    
  <div class="form-label-group">    
   <?php echo $form->textField($model,'test_runactions_email_address',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'test_runactions_email_address'),               
   )); ?>   
   <?php echo $form->labelEx($model,'test_runactions_email_address'); ?>
   <?php echo $form->error($model,'test_runactions_email_address'); ?>
</div>

<p>
    <?php echo t("By clicking submit this will send email and instant notifications in your admin panel,")?>
    <br/>
    <?php echo t("if you cannot receive instant notifications or email check the following and change settings.")?>    
    <ul>
      <li><?php echo t("Check your pusher credentials")?></li>
      <li><?php echo t("Check your email provider settings")?></li>      
    </ul>
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