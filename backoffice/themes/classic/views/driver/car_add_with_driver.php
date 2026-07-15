<?php
$form = $this->beginWidget(
	'CActiveForm',
	array(
		'id' => 'vue-uploader',
		'enableAjaxValidation' => false,		
	)
);
?>

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

<div class="row align-items-center">
  <div class="col"><h6 class="m-0"><?php echo t("Vehicle type")?></h6></div>
  <?php if($model->scenario=="update"):?>
  <div class="col text-right">
    <a href="<?php echo Yii::app()->createUrl("/driver/vehicle_delete",[
        'id'=>$model->vehicle_uuid
    ])?>" class="btn btn-link">
        <?php echo t("Delete");?>
    </a>
  </div>
  <?php endif;?>
</div>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'vehicle_type_id', (array) $vehicle_type,array(
     'class'=>"form-control custom-select form-control-select",
     'placeholder'=>$form->label($model,'vehicle_type_id'),
   )); ?>         
   <?php echo $form->error($model,'vehicle_type_id'); ?>
</div>

<div class="form-label-group">    
        <?php echo $form->textField($model,'plate_number',array(
        'class'=>"form-control form-control-text",
        'placeholder'=>$form->label($model,'plate_number')     
    )); ?>   
    <?php    
        echo $form->labelEx($model,'plate_number'); ?>
    <?php echo $form->error($model,'plate_number'); ?>
</div>


<h6 class="mb-2"><?php echo t("Maker")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'maker', (array) $vehicle_maker,array(
     'class'=>"form-control custom-select form-control-select",
     'placeholder'=>$form->label($model,'maker'),
   )); ?>         
   <?php echo $form->error($model,'maker'); ?>
</div>

<div class="form-label-group">    
        <?php echo $form->textField($model,'model',array(
        'class'=>"form-control form-control-text",
        'placeholder'=>$form->label($model,'model')     
    )); ?>   
    <?php    
        echo $form->labelEx($model,'model'); ?>
    <?php echo $form->error($model,'model'); ?>
</div>

<div class="form-label-group">    
        <?php echo $form->textField($model,'color',array(
        'class'=>"form-control form-control-text",
        'placeholder'=>$form->label($model,'color')     
    )); ?>   
    <?php    
        echo $form->labelEx($model,'color'); ?>
    <?php echo $form->error($model,'color'); ?>
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
    upload_button:'<?php echo CJavaScript::quote(t("Vehicle Thumbnail"))?>',     
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

<div class="pt-3"></div>

<component-uploader
ref="uploader"
max_file="<?php echo Yii::app()->params->dropzone['max_file'];?>"
max_file_size = "<?php echo Yii::app()->params->dropzone['max_file_size']?>"
select_type="multiple"
field = "item_gallery"
field_path = "path"
inline="false"
selected_file=""
:selected_multiple_file='<?php echo json_encode($item_gallery)?>'
upload_path="<?php echo $upload_path?>"
save_path="<?php echo $upload_path?>"

@set-afer-upload="afterUpload"
@set-afer-delete="afterDelete"
:label="{
    select_file:'<?php echo CJavaScript::quote(t("Select File"))?>',       
    upload_new:'<?php echo CJavaScript::quote(t("Upload New"))?>',     
    upload_button:'<?php echo CJavaScript::quote(t("Add Documents"))?>',     
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


<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>