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

<h6 class="mb-2"><?php echo t("Default Auto Print Status")?></h6>
<div class="form-label-group">    
    <?php echo $form->dropDownList($model,'auto_print_status', (array)$status_list ,array(
        'class'=>"form-control custom-select form-control-select",     
        'placeholder'=>$form->label($model,'auto_print_status'),
    )); ?>         
    <?php echo $form->error($model,'auto_print_status'); ?>
</div>		

<div class="form-label-group">    
   <?php echo $form->textField($model,'merchant_tax_number',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'merchant_tax_number')     
   )); ?>   
   <?php    
    echo $form->label($model,'merchant_tax_number'); ?>
   <?php echo $form->error($model,'merchant_tax_number'); ?>   
   <small class="form-text text-muted mb-2">
	  <?php echo t("This will appear in your receipt")?>
	</small>
</div>


<h6 class="mb-3 mt-3"><?php echo t("Two Flavor Options")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'merchant_two_flavor_option', (array) $two_flavor_options,array(
     'class'=>"form-control custom-select form-control-select",
     'placeholder'=>$form->label($model,'merchant_two_flavor_option'),
   )); ?>         
   <?php echo $form->error($model,'merchant_two_flavor_option'); ?>
</div>


<div class="form-label-group">    
   <?php echo $form->textField($model,'merchant_extenal',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'merchant_extenal')     
   )); ?>   
   <?php    
    echo $form->label($model,'merchant_extenal'); ?>
   <?php echo $form->error($model,'merchant_extenal'); ?>      
</div>


<div class="form-label-group">    
   <?php echo $form->textField($model,'merchant_default_preparation_time',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'merchant_default_preparation_time')     
   )); ?>   
   <?php    
    echo $form->label($model,'merchant_default_preparation_time'); ?>
   <?php echo $form->error($model,'merchant_default_preparation_time'); ?>      
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'merchant_whatsapp_phone_number',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'merchant_whatsapp_phone_number')     
   )); ?>   
   <?php    
    echo $form->label($model,'merchant_whatsapp_phone_number'); ?>
   <?php echo $form->error($model,'merchant_whatsapp_phone_number'); ?>      
   <small class="form-text text-muted mb-2">
	  <?php echo t("Include country code")?>
	</small>
</div>

<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"merchant_close_store",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"merchant_close_store",
     'checked'=>$model->merchant_close_store==1?true:false
   )); ?>   
  <label class="custom-control-label" for="merchant_close_store">
   <?php echo t("Close Store")?>
  </label>
</div>    

<!--
<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"merchant_disabled_ordering",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"merchant_disabled_ordering",
     'checked'=>$model->merchant_disabled_ordering==1?true:false
   )); ?>   
  <label class="custom-control-label" for="merchant_disabled_ordering">
   <?php echo t("Disabled Ordering")?>
  </label>
</div> -->   

<!--
<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"enabled_private_menu",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"enabled_private_menu",
     'checked'=>$model->enabled_private_menu==1?true:false
   )); ?>   
  <label class="custom-control-label" for="enabled_private_menu">
   <?php echo t("Make menu private")?>
  </label>
</div>    
-->

<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"merchant_enabled_voucher",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"merchant_enabled_voucher",
     'checked'=>$model->merchant_enabled_voucher==1?true:false
   )); ?>   
  <label class="custom-control-label" for="merchant_enabled_voucher">
   <?php echo t("Enabled Voucher")?>
  </label>
</div>    


<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"merchant_enabled_tip",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"merchant_enabled_tip",
     'checked'=>$model->merchant_enabled_tip==1?true:false
   )); ?>   
  <label class="custom-control-label" for="merchant_enabled_tip">
   <?php echo t("Enabled Tips")?>
  </label>
</div>    

<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"merchant_enabled_whatsapp",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"merchant_enabled_whatsapp",
     'checked'=>$model->merchant_enabled_whatsapp==1?true:false
   )); ?>   
  <label class="custom-control-label" for="merchant_enabled_whatsapp">
   <?php echo t("Enabled Whatsapp Ordering")?>      
  </label>  
</div>    

<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"merchant_enabled_age_verification",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"merchant_enabled_age_verification",
     'checked'=>$model->merchant_enabled_age_verification==1?true:false
   )); ?>   
  <label class="custom-control-label" for="merchant_enabled_age_verification">
   <?php echo t("Enabled Age Verification Popup")?>      
  </label>  
</div>    

<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"merchant_enabled_language",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"merchant_enabled_language",
     'checked'=>$model->merchant_enabled_language==1?true:false
   )); ?>   
  <label class="custom-control-label" for="merchant_enabled_language">
   <?php echo t("Enabled language (Single app only)")?>      
  </label>  
</div>    

<h6 class="mb-3 mt-3"><?php echo t("Checkout Time Selection")?></h6>
<div class="custom-control custom-radio mb-2">  
  <?php 
  echo $form->radioButton($model, 'merchant_time_selection', array(
    'value'=>2,
    'uncheckValue'=>null,
    'id'=>'time_only',
    'class'=>"custom-control-input"
  ));
  ?>
  <label class="custom-control-label" for="time_only"><?php echo t("Time only")?></label>
</div>

<div class="custom-control custom-radio mb-2">  
  <?php 
  echo $form->radioButton($model, 'merchant_time_selection', array(
    'value'=>3,
    'uncheckValue'=>null,
    'id'=>'asap_only',
    'class'=>"custom-control-input"
  ));
  ?>
  <label class="custom-control-label" for="asap_only"><?php echo t("Asap only")?></label>
</div>

<div class="custom-control custom-radio mb-2">  
  <?php 
  echo $form->radioButton($model, 'merchant_time_selection', array(
    'value'=>1,
    'uncheckValue'=>null,
    'id'=>'both_asaptime',
    'class'=>"custom-control-input"
  ));
  ?>
  <label class="custom-control-label" for="both_asaptime"><?php echo t("Both")?></label>
</div>


<h6 class="mb-3 mt-3"><?php echo t("Default Tip")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'merchant_default_tip', (array) $tips,array(
     'class'=>"form-control custom-select form-control-select",
     'placeholder'=>$form->label($model,'merchant_default_tip'),
   )); ?>         
   <?php echo $form->error($model,'merchant_default_tip'); ?>
</div>

<h6 class="mb-3 mt-3"><?php echo t("Tip Type")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'merchant_tip_type', (array) $tip_type,array(
     'class'=>"form-control custom-select form-control-select",
     'placeholder'=>$form->label($model,'merchant_tip_type'),
   )); ?>         
   <?php echo $form->error($model,'merchant_tip_type'); ?>
</div>

<h6 class="mb-3 mt-3"><?php echo t("Enabled Tips on the following transaction")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'tips_in_transactions', (array)$service_list,array(
     'class'=>"form-control custom-select form-control-select select_two",
     'multiple'=>true,
     'placeholder'=>$form->label($model,'tips_in_transactions'),
   )); ?>         
   <?php echo $form->error($model,'tips_in_transactions'); ?>
</div>


   
  </div> <!--body-->
</div> <!--card-->



<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>