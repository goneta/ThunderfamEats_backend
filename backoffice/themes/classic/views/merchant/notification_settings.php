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

<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"merchant_enabled_alert",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"merchant_enabled_alert",
     'checked'=>$model->merchant_enabled_alert==1?true:false
   )); ?>   
  <label class="custom-control-label" for="merchant_enabled_alert">
   <?php echo t("Enabled Notification")?>
  </label>
</div>    

<small class="form-text text-muted mb-2">
  <?php echo t("Email and Mobile number who will receive notifications like new order and cancel order.")?><br/>
  <?php echo t("Multiple email/mobile must be separated by comma.")?>
</small>   

<div class="form-label-group">    
   <?php echo $form->textField($model,'merchant_email_alert',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'merchant_email_alert')     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'merchant_email_alert'); ?>
   <?php echo $form->error($model,'merchant_email_alert'); ?>
</div>


<div class="form-label-group">    
   <?php echo $form->textField($model,'merchant_mobile_alert',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'merchant_mobile_alert')     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'merchant_mobile_alert'); ?>
   <?php echo $form->error($model,'merchant_mobile_alert'); ?>
</div>

<hr/>

<h6 class="mt-3"><?php echo t("Web Settings")?></h6>

<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"merchant_enabled_continues_alert",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"merchant_enabled_continues_alert",
     'checked'=>$model->merchant_enabled_continues_alert==1?true:false
   )); ?>   
  <label class="custom-control-label" for="merchant_enabled_continues_alert">
   <?php echo t("Enabled Continues alert for new order")?>
  </label>
</div>    

<h6 class="mt-3"><?php echo t("Mobile Merchant Settings")?></h6>

<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"merchant_enable_new_order_alert",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"merchant_enable_new_order_alert",
     'checked'=>$model->merchant_enable_new_order_alert==1?true:false
   )); ?>   
  <label class="custom-control-label" for="merchant_enable_new_order_alert">
   <?php echo t("Enable New Order Alert")?>
  </label>
</div>    

<div class="form-label-group">    
   <?php echo $form->textField($model,'merchant_new_order_alert_interval',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'merchant_new_order_alert_interval'),
   )); ?>   
   <?php    
    echo $form->labelEx($model,'merchant_new_order_alert_interval'); ?>
   <?php echo $form->error($model,'merchant_new_order_alert_interval'); ?>   
</div>

<h6 class="mt-3"><?php echo t("Tableside Settings")?></h6>

<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"merchant_enabled_tableside_alert",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"merchant_enabled_tableside_alert",
     'checked'=>$model->merchant_enabled_tableside_alert==1?true:false
   )); ?>   
  <label class="custom-control-label" for="merchant_enabled_tableside_alert">
   <?php echo t("Enabled Continues alert for tableside ordering")?>
  </label>
</div>    

  </div> <!--body-->
</div> <!--card-->

<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>  