
  
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

<h6 class="mb-4"><?php echo t("Merchant Registration")?></h6>

<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"merchant_enabled_registration",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"merchant_enabled_registration",
     'checked'=>$model->merchant_enabled_registration==1?true:false
   )); ?>   
  <label class="custom-control-label" for="merchant_enabled_registration">
   <?php echo t("Enabled Registration")?>
  </label>
</div>    


<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"merchant_enabled_registration_capcha",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"merchant_enabled_registration_capcha",
     'checked'=>$model->merchant_enabled_registration_capcha==1?true:false
   )); ?>   
  <label class="custom-control-label" for="merchant_enabled_registration_capcha">
   <?php echo t("Enabled CAPTCHA")?>
  </label>
</div>    

<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"merchant_allow_login_afterregistration",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"merchant_allow_login_afterregistration",
     'checked'=>$model->merchant_allow_login_afterregistration==1?true:false
   )); ?>   
  <label class="custom-control-label" for="merchant_allow_login_afterregistration">
   <?php echo t("Allow instant access upon registration (no admin approval required)")?>
  </label>  
</div>    
<small>**** <?php echo t("for commission type only")?></small>

<!--
<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"merchant_reg_verification",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"merchant_reg_verification",
     'checked'=>$model->merchant_reg_verification==1?true:false
   )); ?>   
  <label class="custom-control-label" for="merchant_reg_verification">
   <?php echo t("Enabled Signup Verification")?>
  </label>
</div>    

<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"merchant_reg_admin_approval",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"merchant_reg_admin_approval",
     'checked'=>$model->merchant_reg_admin_approval==1?true:false
   )); ?>   
  <label class="custom-control-label" for="merchant_reg_admin_approval">
   <?php echo t("Enabled admin approval")?>
  </label>  
</div>    -->

<!--
<h6 class="mb-4 mt-4"><?php echo t("Registration Default Country")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'merchant_default_country', (array)$country_list,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'merchant_default_country'),
   )); ?>         
   <?php echo $form->error($model,'merchant_default_country'); ?>
</div>		   
-->


<h6 class="mb-2 mt-3"><?php echo t("Merchant default avatar")?></h6>
<div id="vue-uploader">
<component-uploader
ref="uploader"
max_file="<?php echo Yii::app()->params->dropzone['max_file'];?>"
max_file_size = "<?php echo Yii::app()->params->dropzone['max_file_size']?>"
select_type="single"
field = "photo"
field_path = "path"
inline="false"
selected_file="<?php echo $model->site_merchant_avatar;?>"
upload_path="<?php echo $upload_path?>"
save_path="<?php echo $upload_path?>"

@set-afer-upload="afterUpload"
@set-afer-delete="afterDelete"
:label="{
    select_file:'<?php echo CJavaScript::quote(t("Select File"))?>',       
    upload_new:'<?php echo CJavaScript::quote(t("Upload New"))?>',     
    upload_button:'<?php echo CJavaScript::quote(t("Select image"))?>',     
    add_file:'<?php echo CJavaScript::quote(t("Add Files"))?>',
    previous:'<?php echo CJavaScript::quote(t("Previous"))?>',
    next:'<?php echo CJavaScript::quote(t("Next"))?>',
    search:'<?php echo CJavaScript::quote(t("Search"))?>',    
    delete_file:'<?php echo CJavaScript::quote(t("Delete File"))?>',   
    drop_files:'<?php echo CJavaScript::quote(t("Drop files anywhere to upload"))?>',   
    or:'<?php echo CJavaScript::quote(t("or"))?>',   
    select_files:'<?php echo CJavaScript::quote(t("Select Files"))?>',   
    add_more:'<?php echo CJavaScript::quote(t("Add more"))?>',   
}"
>
</component-uploader>
</div>

<h6 class="mb-4 mt-4"><?php echo t("Membership Program")?></h6>   
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'registration_program', (array)$program_list,array(
     'class'=>"form-control custom-select form-control-select select_two",     
     'placeholder'=>$form->label($model,'registration_program'),
     'multiple'=>true,
   )); ?>         
   <?php echo $form->error($model,'registration_program'); ?>
</div>	

<h6 class="mb-4 mt-4"><?php echo t("Set Specific Country")?> (<?php echo t("maximum of 5 country")?>)</h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'merchant_specific_country',(array)$country_list,array(
     'class'=>"form-control custom-select form-control-select select_two",
     'placeholder'=>$form->label($model,'merchant_specific_country'),
     'multiple'=>true,
   )); ?>         
   <?php echo $form->error($model,'merchant_specific_country'); ?>
</div>
<small class="form-text text-muted mb-2">
  <?php echo t("leave empty to show all country")?>
</small>



<hr/>

<h6 class="mb-4 mt-4"><?php echo t("Terms and conditions")?></h6>
<div class="form-label-group mt-2">    
   <?php echo $form->textArea($model,'registration_terms_condition',array(
     'class'=>"form-control form-control-text summernote",     
     'placeholder'=>t("Your terms and condition here...")
   )); ?>      
   <?php echo $form->error($model,'registration_terms_condition'); ?>
</div>

<h6 class="mb-4"><?php echo t("Pre-configure food item size")?></h6>
<div class="form-label-group mt-2">    
   <?php echo $form->textArea($model,'pre_configure_size',array(
     'class'=>"form-control form-control-text",     
     'placeholder'=>t("")
   )); ?>      
   <?php echo $form->error($model,'pre_configure_size'); ?>
</div>
<p>
  <?php echo t("this will be added as default food item size to merchant during registration. value must be separated by comma eg. small,medium,large")?>
</p>




<h6 class="mt-4"><?php echo t("Merchant Default opening hours")?></h6>
<p><?php echo t("when creating new merchant will copy this opening hours")?></p>


<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"enabled_copy_opening_hours",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"enabled_copy_opening_hours",
     'checked'=>$model->enabled_copy_opening_hours==1?true:false
   )); ?>   
  <label class="custom-control-label" for="enabled_copy_opening_hours">
   <?php echo t("Enabled")?>
  </label>
</div>    


<div class="d-flex">

<div class="form-label-group mr-3">    
   <?php echo $form->textField($model,'merchant_default_opening_hours_start',array(
     'class'=>"form-control form-control-text timepick datetimepicker-input",     
     'placeholder'=>$form->label($model,'merchant_default_opening_hours_start'),     
     'readonly'=>true,
     'data-toggle'=>'datetimepicker'
   )); ?>   
   <?php    
    echo $form->labelEx($model,'merchant_default_opening_hours_start'); ?>
   <?php echo $form->error($model,'merchant_default_opening_hours_start'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'merchant_default_opening_hours_end',array(
     'class'=>"form-control form-control-text timepick datetimepicker-input",     
     'placeholder'=>$form->label($model,'merchant_default_opening_hours_end'),     
     'readonly'=>true,
     'data-toggle'=>'datetimepicker'
   )); ?>   
   <?php    
    echo $form->labelEx($model,'merchant_default_opening_hours_end'); ?>
   <?php echo $form->error($model,'merchant_default_opening_hours_end'); ?>
</div>
 
</div>
<!--flex-->


<?php //dump($provider)?>

<h6 class="mt-3"><?php echo t("Merchant Default payment settings")?></h6>
<p><?php echo t("when creating new merchant this will enabled payment gateway")?></p>

<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"enabled_copy_payment_setting",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"enabled_copy_payment_setting",
     'checked'=>$model->enabled_copy_payment_setting==1?true:false
   )); ?>   
  <label class="custom-control-label" for="enabled_copy_payment_setting">
   <?php echo t("Enabled")?>
  </label>
</div>    

<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'copy_payment_list',(array)$provider,array(
     'class'=>"form-control custom-select form-control-select select_two",
     'placeholder'=>$form->label($model,'copy_payment_list'),
     'multiple'=>true,
   )); ?>         
   <?php echo $form->error($model,'copy_payment_list'); ?>
</div>

<hr/>

<h5><?php echo t("Templates")?></h5>

<h6 class="mb-2 mt-4"><?php echo t("Confirm Account")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'registration_confirm_account_tpl', (array)$template_list,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'registration_confirm_account_tpl'),
   )); ?>         
   <?php echo $form->error($model,'registration_confirm_account_tpl'); ?>
</div>		   

<h6 class="mb-2 mt-4"><?php echo t("Welcome email")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'merchant_registration_welcome_tpl', (array)$template_list,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'merchant_registration_welcome_tpl'),
   )); ?>         
   <?php echo $form->error($model,'merchant_registration_welcome_tpl'); ?>
</div>		   

<h6 class="mb-2 mt-4"><?php echo t("Bank Deposit Instructions for Subscription Payment")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'merchant_bank_deposit_subscriptions', (array)$template_list,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'merchant_bank_deposit_subscriptions'),
   )); ?>         
   <?php echo $form->error($model,'merchant_bank_deposit_subscriptions'); ?>
</div>		   

<h6 class="mb-2 mt-4"><?php echo t("Bank Deposit Approved for Subscription")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'merchant_subscription_approved', (array)$template_list,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'merchant_subscription_approved'),
   )); ?>         
   <?php echo $form->error($model,'merchant_subscription_approved'); ?>
</div>		   

<h6 class="mb-2 mt-4"><?php echo t("Merchant Registration Approved")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'merchant_registration_approved', (array)$template_list,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'merchant_registration_approved'),
   )); ?>         
   <?php echo $form->error($model,'merchant_registration_approved'); ?>
</div>		   

<h6 class="mb-2 mt-4"><?php echo t("Subscription Payment Process")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'merchant_subscription_payment_process', (array)$template_list,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'merchant_subscription_payment_process'),
   )); ?>         
   <?php echo $form->error($model,'merchant_subscription_payment_process'); ?>
</div>		   

<h6 class="mb-2 mt-4"><?php echo t("Subscription Payment Failed")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'merchant_subscription_payment_failed', (array)$template_list,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'merchant_subscription_payment_failed'),
   )); ?>         
   <?php echo $form->error($model,'merchant_subscription_payment_failed'); ?>
</div>		   

<h6 class="mb-2 mt-4"><?php echo t("Subscription Canceled")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'merchant_subscription_cancelled', (array)$template_list,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'merchant_subscription_cancelled'),
   )); ?>         
   <?php echo $form->error($model,'merchant_subscription_cancelled'); ?>
</div>		   

<h6 class="mb-2 mt-4"><?php echo t("Plan Near Expiration")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'merchant_plan_near_expired_tpl', (array)$template_list,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'merchant_plan_near_expired_tpl'),
   )); ?>         
   <?php echo $form->error($model,'merchant_plan_near_expired_tpl'); ?>
</div>		   

<h6 class="mb-2 mt-4"><?php echo t("Plan Expired")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'merchant_plan_expired_tpl', (array)$template_list,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'merchant_plan_expired_tpl'),
   )); ?>         
   <?php echo $form->error($model,'merchant_plan_expired_tpl'); ?>
</div>		   

<h6 class="mb-2 mt-4"><?php echo t("New Signup")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'merchant_registration_new_tpl', (array)$template_list,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'merchant_registration_new_tpl'),
   )); ?>         
   <?php echo $form->error($model,'merchant_registration_new_tpl'); ?>
   <small><?php echo t("this template will send to admin")?></small>
</div>		   


  </div> <!--body-->
</div> <!--card-->



<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>