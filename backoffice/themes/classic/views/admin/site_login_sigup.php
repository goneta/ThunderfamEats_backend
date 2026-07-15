
  
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


<h6 class="mb-2"><?php echo t("User default avatar")?></h6>
<div id="vue-uploader">
<component-uploader
ref="uploader"
max_file="<?php echo Yii::app()->params->dropzone['max_file'];?>"
max_file_size = "<?php echo Yii::app()->params->dropzone['max_file_size']?>"
select_type="single"
field = "photo"
field_path = "path"
inline="false"
selected_file="<?php echo $model->site_user_avatar;?>"
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


<h6 class="mb-3 mt-3"><?php echo t("Signup Method")?></h6>

<div class="custom-control custom-radio mb-2">  
  <?php 
  echo $form->radioButton($model, 'signup_type', array(
    'value'=>'standard',
    'uncheckValue'=>null,
    'id'=>'signup_type',
    'class'=>"custom-control-input"
  ));
  ?>
  <label class="custom-control-label" for="signup_type"><?php echo t("Complete signup form")?></label>  
</div>

<div class="custom-control custom-radio mb-2">  
  <?php 
  echo $form->radioButton($model, 'signup_type', array(
    'value'=>'mobile_phone',
    'uncheckValue'=>null,
    'id'=>'mobile_phone',
    'class'=>"custom-control-input"
  ));
  ?>
  <label class="custom-control-label" for="mobile_phone"><?php echo t("Phone number signup")?></label>  
</div>


<h6 class="mb-3 mt-3"><?php echo t("Signup Verifications")?></h6>
<!-- <p class="mb-3"><small><?php echo t("This settings only works in standard signup")?></small></p> -->

<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"signup_enabled_verification",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"signup_enabled_verification",
     'checked'=>$model->signup_enabled_verification==1?true:false
   )); ?>   
  <label class="custom-control-label" for="signup_enabled_verification">
   <?php echo t("Enabled")?>
  </label>
</div>    

<!--<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'signup_verification_type', (array)$verification_list ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'signup_verification_type'),
   )); ?>         
   <?php echo $form->error($model,'signup_verification_type'); ?>
</div>		-->

<div class="form-label-group">    
   <?php echo $form->textField($model,'signup_resend_counter',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'signup_resend_counter'),
   )); ?>   
   <?php    
    echo $form->labelEx($model,'signup_resend_counter'); ?>
   <?php echo $form->error($model,'signup_resend_counter'); ?>
</div>

<h6 class="mb-3 mt-3"><?php echo t("Guest Checkout")?></h6>

<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"enabled_guest",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"enabled_guest",
     'checked'=>$model->enabled_guest==1?true:false
   )); ?>   
  <label class="custom-control-label" for="enabled_guest">
   <?php echo t("Enabled")?>
  </label>
</div>    

<h6 class="mb-1 mt-3"><?php echo t("Login Method")?></h6>
<div class="custom-control custom-radio mb-2 mt-2">  
  <?php 
  echo $form->radioButton($model, 'login_method', array(
    'value'=>'user',
    'uncheckValue'=>null,
    'id'=>'login_method_1',
    'class'=>"custom-control-input"
  ));
  ?>
  <label class="custom-control-label" for="login_method_1"><?php echo t("Login using username and password")?></label>  
</div>

<div class="custom-control custom-radio mb-2 mt-2">  
  <?php 
  echo $form->radioButton($model, 'login_method', array(
    'value'=>'otp',
    'uncheckValue'=>null,
    'id'=>'login_method_2',
    'class'=>"custom-control-input"
  ));
  ?>
  <label class="custom-control-label" for="login_method_2"><?php echo t("Login using OTP")?></label>  
</div>


<h6 class="mb-1 mt-3"><?php echo t("Password Reset Options")?></h6>
<div><?php echo t("Choose the method for password reset verification")?>:</div>

<div class="custom-control custom-radio mb-2 mt-2">  
  <?php 
  echo $form->radioButton($model, 'password_reset_options', array(
    'value'=>'email',
    'uncheckValue'=>null,
    'id'=>'password_reset_email',
    'class'=>"custom-control-input"
  ));
  ?>
  <label class="custom-control-label" for="password_reset_email"><?php echo t("Email Only")?></label>  
</div>

<div class="custom-control custom-radio mb-2">  
  <?php 
  echo $form->radioButton($model, 'password_reset_options', array(
    'value'=>'sms',
    'uncheckValue'=>null,
    'id'=>'password_reset_sms',
    'class'=>"custom-control-input"
  ));
  ?>
  <label class="custom-control-label" for="password_reset_sms"><?php echo t("SMS Only")?></label>  
</div>

<div class="custom-control custom-radio mb-2">  
  <?php 
  echo $form->radioButton($model, 'password_reset_options', array(
    'value'=>'both_sms_email',
    'uncheckValue'=>null,
    'id'=>'both_sms_email',
    'class'=>"custom-control-input"
  ));
  ?>
  <label class="custom-control-label" for="both_sms_email"><?php echo t("Both SMS and Email")?></label>  
</div>

<hr/>

<h6 class="mt-3 mb-3"><?php echo t("Google reCapcha")?></h6>
<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"signup_enabled_capcha",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"signup_enabled_capcha",
     'checked'=>$model->signup_enabled_capcha==1?true:false
   )); ?>   
  <label class="custom-control-label" for="signup_enabled_capcha">
   <?php echo t("Enabled")?>
  </label>
</div>    

<h6 class="mt-3 mb-3"><?php echo t("Terms and condition")?></h6>

<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"signup_enabled_terms",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"signup_enabled_terms",
     'checked'=>$model->signup_enabled_terms==1?true:false
   )); ?>   
  <label class="custom-control-label" for="signup_enabled_terms">
   <?php echo t("Enabled")?>
  </label>
</div>    

<div class="form-label-group mt-2">    
   <?php echo $form->textArea($model,'signup_terms',array(
     'class'=>"form-control form-control-text summernote",     
     'placeholder'=>t("Your terms and condition here...")
   )); ?>      
   <?php echo $form->error($model,'signup_terms'); ?>
</div>

<hr/>
<h6 class="mb-3"><?php echo t("Welcome Template")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'signup_welcome_tpl', (array)$template_list ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'signup_welcome_tpl'),
   )); ?>         
   <?php echo $form->error($model,'signup_welcome_tpl'); ?>
</div>		

<h6 class="mb-3"><?php echo t("New Signup Template")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'signupnew_tpl', (array)$template_list ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'signupnew_tpl'),
   )); ?>         
   <?php echo $form->error($model,'signupnew_tpl'); ?>
   <small><?php echo t("this template will send to admin user")?></small>
</div>		

<h6 class="mb-3"><?php echo t("Verification Template")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'signup_verification_tpl', (array)$template_list ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'signup_verification_tpl'),
   )); ?>         
   <?php echo $form->error($model,'signup_verification_tpl'); ?>
</div>		

<h6 class="mb-3"><?php echo t("Reset Password Template")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'signup_resetpass_tpl', (array)$template_list ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'signup_resetpass_tpl'),
   )); ?>         
   <?php echo $form->error($model,'signup_resetpass_tpl'); ?>
</div>		

<h6 class="mb-3"><?php echo t("Forgot Backend Password Template")?></h6>
<div class="form-label-group">    
<?php echo $form->dropDownList($model,'backend_forgot_password_tpl', (array)$template_list ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'backend_forgot_password_tpl'),
   )); ?>         
   <?php echo $form->error($model,'backend_forgot_password_tpl'); ?>
</div>

<h6 class="mb-3"><?php echo t("Complete Registration")?></h6>
<div class="form-label-group">    
<?php echo $form->dropDownList($model,'signup_complete_registration_tpl', (array)$template_list ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'signup_complete_registration_tpl'),
   )); ?>         
   <?php echo $form->error($model,'signup_complete_registration_tpl'); ?>
</div>

<hr/>

<h6 class="mb-3"><?php echo t("Block user from registering")?></h6>

<div class="form-label-group mt-2">    
   <?php echo $form->textArea($model,'blocked_email_add',array(
     'class'=>"form-control form-control-text autosize",     
     'placeholder'=>t("Block email address list")
   )); ?>      
   <?php echo $form->error($model,'blocked_email_add'); ?>
</div>
<small class="form-text text-muted mb-3">
  <?php echo t("Multiple email separated by comma")?>
</small>

<div class="form-label-group">    
   <?php echo $form->textArea($model,'blocked_mobile',array(
     'class'=>"form-control form-control-text autosize",     
     'placeholder'=>t("Block mobile number list")
   )); ?>      
   <?php echo $form->error($model,'blocked_mobile'); ?>
</div>
<small class="form-text text-muted">
  <?php echo t("Multiple mobile separated by comma")?>
</small>
   
  </div> <!--body-->
</div> <!--card-->



<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>