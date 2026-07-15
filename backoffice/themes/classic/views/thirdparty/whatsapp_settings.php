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

<h6 class="mb-3"><?php echo t("Whatsapp Configuration")?></h6>

<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"whatsapp_enabled",array(
     'class'=>"custom-control-input whatsapp_enabled",     
     'value'=>1,
     'id'=>"whatsapp_enabled",
     'checked'=>$model->whatsapp_enabled==1?true:false
   )); ?>   
  <label class="custom-control-label" for="whatsapp_enabled">
   <?php echo t("Enabled WhatsApp")?>
  </label>
</div>    


<div class="form-label-group">    
   <?php echo $form->textField($model,'whatsapp_business_accountid',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'whatsapp_business_accountid'),               
   )); ?>   
   <?php echo $form->labelEx($model,'whatsapp_business_accountid'); ?>
   <?php echo $form->error($model,'whatsapp_business_accountid'); ?>
</div>


<div class="form-label-group">    
   <?php echo $form->textField($model,'whatsapp_phone_number',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'whatsapp_phone_number'),               
   )); ?>   
   <?php echo $form->labelEx($model,'whatsapp_phone_number'); ?>
   <?php echo $form->error($model,'whatsapp_phone_number'); ?>
</div>



<div class="form-label-group">    
   <?php echo $form->textField($model,'whatsapp_token',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'whatsapp_token'),               
   )); ?>   
   <?php echo $form->labelEx($model,'whatsapp_token'); ?>
   <?php echo $form->error($model,'whatsapp_token'); ?>
</div>

<?php if(!empty($model->whatsapp_token) && !empty($model->whatsapp_business_accountid)):?>
<a href="<?php echo $update_template_link;?>" class="btn btn-green btn-small"><?php echo t("Update Templates")?></a>

<h6 class="mb-2 mt-4"><?php echo t("Language Template")?></h6>
<div class="form-label-group">    
  <?php echo $form->dropDownList($model,'whatsapp_language', (array)$language_list ,array(
      'class'=>"form-control custom-select form-control-select",     
      'placeholder'=>$form->label($model,'whatsapp_language'),
  )); ?>         
  <?php echo $form->error($model,'whatsapp_language'); ?>
</div>		

<h6 class="mb-2 mt-4"><?php echo t("Whatsapp Receipt Template")?></h6>
  <div class="form-label-group">    
  <?php echo $form->dropDownList($model,'whatsapp_receipt_templatename', (array)$templates ,array(
      'class'=>"form-control custom-select form-control-select",     
      'placeholder'=>$form->label($model,'whatsapp_receipt_templatename'),
  )); ?>         
  <?php echo $form->error($model,'whatsapp_receipt_templatename'); ?>
  <small id="whatsapp_receipt_templatename" class="form-text text-muted">
    <?php echo t('Template Variables')?> : 
    <ul>
      <li>{{1}} - <?php echo t("Customer name")?></li>
      <li>{{2}} - <?php echo t("Restaurant name")?></li>
      <li>{{3}} - <?php echo t("Order ID")?></li>
      <li>{{4}} - <?php echo t("Food item lines")?></li>
      <li>{{5}} - <?php echo t("Total Amount of order")?></li>
      <li>{{6}} - <?php echo t("Delivery Address")?></li>
      <li>{{7}} - <?php echo t("Restaurant Contact number")?></li>
    </ul>
  </small>
</div>		


<h6 class="mb-2 mt-4"><?php echo t("Whatsapp Receipt Template To Merchant")?></h6>
  <div class="form-label-group">    
  <?php echo $form->dropDownList($model,'whatsapp_receipt_templatename_merchant', (array)$templates ,array(
      'class'=>"form-control custom-select form-control-select",     
      'placeholder'=>$form->label($model,'whatsapp_receipt_templatename_merchant'),
  )); ?>         
  <?php echo $form->error($model,'whatsapp_receipt_templatename_merchant'); ?>
  <small id="whatsapp_receipt_templatename" class="form-text text-muted">
    <?php echo t('Template Variables')?> : 
    <ul>
      <li>{{1}} - <?php echo t("Restaurant name")?></li>
      <li>{{2}} - <?php echo t("Order ID")?></li>
      <li>{{3}} - <?php echo t("Order Type")?></li>
      <li>{{4}} - <?php echo t("Delivery Time")?></li>
      <li>{{5}} - <?php echo t("Food item lines")?></li>
      <li>{{6}} - <?php echo t("Total lines")?></li>
      <li>{{7}} - <?php echo t("Customer Details")?></li>
    </ul>
  </small>
</div>		

<?php endif;?>

   
</div> <!--body-->
</div> <!--card-->



<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>