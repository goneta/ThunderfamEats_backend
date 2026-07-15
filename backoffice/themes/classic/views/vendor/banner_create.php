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


<div class="form-label-group">    
   <?php echo $form->textField($model,'title',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'title')     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'title'); ?>
   <?php echo $form->error($model,'title'); ?>
</div>

<div id="vue-uploader">
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
    upload_button:'<?php echo CJavaScript::quote(t("Featured Image"))?>',   
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
<p class="font11 mt-2 text-muted"><?php echo t("Recommended image size is (1400x600)")?></p>
</div>

<h6 class="mb-4 mt-4"><?php echo t("Banner Type")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'banner_type', (array) $banner_type,array(
     'class'=>"form-control custom-select form-control-select banner_type",
     'placeholder'=>$form->label($model,'banner_type'),
   )); ?>         
   <?php echo $form->error($model,'banner_type'); ?>
</div>

<div class="section-food section-banner">
<h6 class="mb-4"><?php echo t("Select Item")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'meta_value2',(array)$items,array(
     'class'=>"form-control custom-select form-control-select select_two_ajax ",
     'placeholder'=>$form->label($model,'meta_value2'),   
     'action'=>'search_item'
   )); ?>         
   <?php echo $form->error($model,'meta_value2'); ?>
</div>
</div>

<div class="section-banner section-custom_link">
  <h6 class="mb-2"><?php echo t("Custom URL")?></h6>
  <div class="form-label-group">    
  <?php echo $form->textField($model,'custom_link',array(
          'class'=>"form-control form-control-text",
          'placeholder'=>$form->label($model,'custom_link')     
        )); ?>   
  </div>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'sequence',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'sequence')     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'sequence'); ?>
   <?php echo $form->error($model,'sequence'); ?>
</div>

<h6 class="mb-4"><?php echo t("Status")?></h6>
<!-- <div class="form-label-group">    
   <?php echo $form->dropDownList($model,'status', (array) $status,array(
     'class'=>"form-control custom-select form-control-select",
     'placeholder'=>$form->label($model,'status'),
   )); ?>         
   <?php echo $form->error($model,'status'); ?>
</div> -->
<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"status",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"status",
     'checked'=>$model->status==1?true:false
   )); ?>   
  <label class="custom-control-label" for="status">
   <?php echo t("Publish")?>
  </label>
</div>    


  </div> <!--body-->
</div> <!--card-->



<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>