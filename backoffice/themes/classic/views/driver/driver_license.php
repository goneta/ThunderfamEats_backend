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

<h6 class="mb-4"><?php echo t("License information")?></h6>

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
</div>
<!-- row -->

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
<p class="font11 mt-2 text-muted"><?php echo t("License front photo")?></p> 


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
<p class="font11 mt-2 text-muted"><?php echo t("License back photo")?></p> 


  </div> <!--body-->
</div> <!--card-->



<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>