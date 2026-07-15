<?php if(!$hide_nav):?>
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
<?php endif?>

  
<?php
$form = $this->beginWidget(
	'CActiveForm',
	array(
		'id' => 'vue-uploader',
		'enableAjaxValidation' => false,	
        'htmlOptions' => array(
			'autocomplete' => 'off', // This disables autofill for the form
		),	
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

<h6 class="mb-4"><?php echo t("Basic information")?></h6>

<div class="row">
    <div class="col-md-6">
      <div class="form-label-group">    
        <?php echo $form->textField($model,'first_name',array(
            'class'=>"form-control form-control-text",
            'placeholder'=>$form->label($model,'first_name')     
        )); ?>   
        <?php    
            echo $form->labelEx($model,'first_name'); ?>
        <?php echo $form->error($model,'first_name'); ?>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-label-group">    
        <?php echo $form->textField($model,'last_name',array(
            'class'=>"form-control form-control-text",
            'placeholder'=>$form->label($model,'last_name')     
        )); ?>   
        <?php    
            echo $form->labelEx($model,'last_name'); ?>
        <?php echo $form->error($model,'last_name'); ?>
        </div>
    </div>
</div>
<!-- row -->

<div class="row">
    <div class="col-md-6">
      <div class="form-label-group">    
        <?php echo $form->textField($model,'email',array(
            'class'=>"form-control form-control-text",
            'placeholder'=>$form->label($model,'email')     
        )); ?>   
        <?php    
            echo $form->labelEx($model,'email'); ?>
        <?php echo $form->error($model,'email'); ?>
        </div>
    </div>

    <div id="app-phone" class="col-md-6">
        <!-- <div class="form-label-group">    
        <?php echo $form->textField($model,'phone',array(
            'class'=>"form-control form-control-text",
            'placeholder'=>$form->label($model,'phone')     
        )); ?>   
        <?php    
            echo $form->labelEx($model,'phone'); ?>
        <?php echo $form->error($model,'phone'); ?>
        </div> -->
                
        <component-phone
	    default_country="<?php echo CJavaScript::quote($phone_default_country);?>"    
	    :only_countries='<?php echo json_encode($phone_country_list)?>'		   
        :phone="<?php echo $model->phone?>" 
        field_phone="AR_driver[phone]"
        field_phone_prefix="AR_driver[phone_prefix]"
	    >
	    </component-phone>

    </div>
</div>
<!-- row -->

<div class="row">
 <div class="col">
 <div class="form-label-group">    
    <?php echo $form->textField($model,'address',array(
        'class'=>"form-control form-control-text",
        'placeholder'=>$form->label($model,'address')     
    )); ?>   
    <?php    
        echo $form->labelEx($model,'address'); ?>
    <?php echo $form->error($model,'address'); ?>
    </div>
 </div>
</div>
<!-- row -->

<component-uploader
ref="uploader"
max_file="<?php echo Yii::app()->params->dropzone['max_file'];?>"
max_file_size = "<?php echo Yii::app()->params->dropzone['max_file_size']?>"
select_type="single"
field = "photo"
field_path = "path"
inline="false"
selected_file="<?php echo $model->photo;?>"
upload_path="<?php echo $upload_path?>"
save_path="<?php echo $model->path?>"

@set-afer-upload="afterUpload"
@set-afer-delete="afterDelete"
:label="{
    select_file:'<?php echo CJavaScript::quote(t("Select File"))?>',       
    upload_new:'<?php echo CJavaScript::quote(t("Upload New"))?>',     
    upload_button:'<?php echo CJavaScript::quote(t("Driver photo"))?>',     
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
<p class="font11 mt-2 text-muted"><?php echo t("Recommended image size is (120x120)")?></p>


<?php if($multicurrency_enabled):?>
<h6 class="mb-3 mt-3"><?php echo t("Default Currency")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'default_currency', (array) $currency_list,array(
     'class'=>"form-control custom-select form-control-select",
     'placeholder'=>$form->label($model,'default_currency'),
   )); ?>         
   <?php echo $form->error($model,'default_currency'); ?>
   <p class="m-1"><?php echo t("Leave empty to use admin based currency")?></p>
</div>
<?php endif;?>

<?php if($model->isNewRecord):?>
<h6 class="mb-4"><?php echo t("Login")?></h6>
<?php else :?>
<h6 class="mb-4"><?php echo t("Update login")?></h6>
<?php endif?>

<input type="password" style="display:none">

<div class="form-label-group">    
   <?php echo $form->passwordField($model,'new_password',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'new_password')     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'new_password'); ?>
   <?php echo $form->error($model,'new_password'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->passwordField($model,'confirm_password',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'confirm_password')     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'confirm_password'); ?>
   <?php echo $form->error($model,'confirm_password'); ?>
</div>

<h6 class="mb-4"><?php echo t("Employment Type")?></h6>
<div class="form-label-group">    
<?php echo $form->dropDownList($model,'employment_type', (array)$employment_type ,array(
    'class'=>"form-control custom-select form-control-select",     
    'placeholder'=>$form->label($model,'employment_type'),
)); ?>         
<?php echo $form->error($model,'employment_type'); ?>
</div>		

<h6 class="mb-4"><?php echo t("Payment options")?></h6>
     
<h6 class="mb-1"><?php echo t("Salary Type")?></h6>
<div class="form-label-group">    
<?php echo $form->dropDownList($model,'salary_type', (array)$salary_type ,array(
    'class'=>"form-control custom-select form-control-select",     
    'placeholder'=>$form->label($model,'salary_type'),
)); ?>         
<?php echo $form->error($model,'salary_type'); ?>
</div>		

<div class="row">
    <div class="col-md-12">
      <div class="form-label-group">    
        <?php echo $form->textField($model,'salary',array(
            'class'=>"form-control form-control-text",
            'placeholder'=>$form->label($model,'salary')     
        )); ?>   
        <?php    
            echo $form->labelEx($model,'salary'); ?>
        <?php echo $form->error($model,'salary'); ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
      <div class="form-label-group">    
        <?php echo $form->textField($model,'fixed_amount',array(
            'class'=>"form-control form-control-text",
            'placeholder'=>$form->label($model,'fixed_amount')     
        )); ?>   
        <?php    
            echo $form->labelEx($model,'fixed_amount'); ?>
        <?php echo $form->error($model,'fixed_amount'); ?>
        </div>
    </div>

    <div class="col-md-6">
        <div class="row no-gutters">
            <div class="col-md-7 mr-1">
                <div class="form-label-group">    
                <?php echo $form->textField($model,'commission',array(
                    'class'=>"form-control form-control-text",
                    'placeholder'=>$form->label($model,'commission')     
                )); ?>   
                <?php    
                    echo $form->labelEx($model,'commission'); ?>
                <?php echo $form->error($model,'commission'); ?>
                </div>        
            </div>
            <!-- col -->
            <div class="col">
            
            <div class="form-label-group">    
            <?php echo $form->dropDownList($model,'commission_type', (array)$commission_type ,array(
                'class'=>"form-control custom-select form-control-select",     
                'placeholder'=>$form->label($model,'commission_type'),
            )); ?>         
            <?php echo $form->error($model,'commission_type'); ?>
            </div>		

            </div>
            <!-- col -->
        </div>
        <!-- row -->
    </div>
</div>
<!-- row -->

<h6 class="mb-4"><?php echo t("Incentives")?></h6>

<div class="row">
 <div class="col">
 <div class="form-label-group">    
    <?php echo $form->textField($model,'incentives_amount',array(
        'class'=>"form-control form-control-text",
        'placeholder'=>$form->label($model,'incentives_amount')        
    )); ?>   
    <?php    
        echo $form->labelEx($model,'incentives_amount'); ?>
    <?php echo $form->error($model,'incentives_amount'); ?>
    </div>
 </div>

 <div class="col">
 <div class="form-label-group">    
    <?php echo $form->textField($model,'allowed_offline_amount',array(
        'class'=>"form-control form-control-text",
        'placeholder'=>$form->label($model,'allowed_offline_amount')     
    )); ?>   
    <?php    
        echo $form->labelEx($model,'allowed_offline_amount'); ?>
    <?php echo $form->error($model,'allowed_offline_amount'); ?>
    </div>
 </div>

</div>
<!-- row -->
<!-- 
<h6 class="mb-4"><?php echo t("License")?></h6>

<div class="row">
    <div class="col-md-6">
      <div class="form-label-group">    
        <?php echo $form->textField($model,'license_number',array(
            'class'=>"form-control form-control-text",
            'placeholder'=>$form->label($model,'license_number')     
        )); ?>   
        <?php    
            echo $form->labelEx($model,'license_number'); ?>
        <?php echo $form->error($model,'license_number'); ?>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-label-group">    
        <?php echo $form->textField($model,'license_expiration',array(
            'class'=>"form-control form-control-text mask_date",
            'placeholder'=>$form->label($model,'license_expiration')     
        )); ?>   
        <?php    
            echo $form->labelEx($model,'license_expiration'); ?>
        <?php echo $form->error($model,'license_expiration'); ?>
        </div>        
    </div>
</div> -->
<!-- row -->

<!-- 
<component-uploader
ref="uploader"
max_file="<?php echo Yii::app()->params->dropzone['max_file'];?>"
max_file_size = "<?php echo Yii::app()->params->dropzone['max_file_size']?>"
select_type="single"
field = "license_front_photo"
field_path = "path"
inline="false"
selected_file="<?php echo $model->license_front_photo;?>"
upload_path="<?php echo $upload_path?>"
save_path="<?php echo $model->path_license?>"

@set-afer-upload="afterUpload"
@set-afer-delete="afterDelete"
:label="{
    select_file:'<?php echo CJavaScript::quote(t("Select File"))?>',       
    upload_new:'<?php echo CJavaScript::quote(t("Upload New"))?>',     
    upload_button:'<?php echo CJavaScript::quote(t("Photo"))?>',     
    browse:'<?php echo CJavaScript::quote(t("Browse"))?>',    
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
<p class="font11 mt-2 text-muted"><?php echo t("License front photo")?></p> -->

<!-- 
<component-uploader
ref="uploader"
max_file="<?php echo Yii::app()->params->dropzone['max_file'];?>"
max_file_size = "<?php echo Yii::app()->params->dropzone['max_file_size']?>"
select_type="single"
field = "license_back_photo"
field_path = "path"
inline="false"
selected_file="<?php echo $model->license_back_photo;?>"
upload_path="<?php echo $upload_path?>"
save_path="<?php echo $model->path_license?>"

@set-afer-upload="afterUpload"
@set-afer-delete="afterDelete"
:label="{
    select_file:'<?php echo CJavaScript::quote(t("Select File"))?>',       
    upload_new:'<?php echo CJavaScript::quote(t("Upload New"))?>',     
    upload_button:'<?php echo CJavaScript::quote(t("Photo"))?>',     
    browse:'<?php echo CJavaScript::quote(t("Browse"))?>',    
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
<p class="font11 mt-2 text-muted"><?php echo t("License back photo")?></p> -->



<h6 class="mb-4 mt-4"><?php echo t("Color Hex")?></h6>
<div class="form-label-group">    
   <?php echo $form->textField($model,'color_hex',array(
     'class'=>"form-control form-control-text colorpicker",
     'placeholder'=>$form->label($model,'color_hex'),
     'readonly'=>false
   )); ?>      
   <?php echo $form->error($model,'color_hex'); ?>
</div>

<h6 class="mb-4"><?php echo t("Status")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'status', (array) $status,array(
     'class'=>"form-control custom-select form-control-select",
     'placeholder'=>$form->label($model,'status'),
   )); ?>         
   <?php echo $form->error($model,'status'); ?>
</div>

  </div> <!--body-->
</div> <!--card-->



<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>