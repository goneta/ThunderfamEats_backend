
  
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


<h6 class="mb-2"><?php echo t("Food default avatar")?></h6>
<div id="vue-uploader">
<component-uploader
ref="uploader"
max_file="<?php echo Yii::app()->params->dropzone['max_file'];?>"
max_file_size = "<?php echo Yii::app()->params->dropzone['max_file_size']?>"
select_type="single"
field = "photo"
field_path = "path"
inline="false"
selected_file="<?php echo $model->site_food_avatar;?>"
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


<h6 class="mb-2"><?php echo t("Ordering")?></h6>
<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"enabled_website_ordering",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"enabled_website_ordering",
     'checked'=>$model->enabled_website_ordering==1?true:false
   )); ?>   
  <label class="custom-control-label" for="enabled_website_ordering">
   <?php echo t("Enabled Ordering")?>
  </label>
</div>    

<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"enabled_include_utensils",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"enabled_include_utensils",
     'checked'=>$model->enabled_include_utensils==1?true:false
   )); ?>   
  <label class="custom-control-label" for="enabled_include_utensils">
   <?php echo t("Enabled Include utensils and condoments")?>
  </label>
</div>    

<h6 class="mb-2 mt-3"><?php echo t("Cannot do order again if previous order status is")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'restrict_order_by_status',$status_list,array(
     'class'=>"form-control custom-select form-control-select select_two",
     'placeholder'=>$form->label($model,'restrict_order_by_status'),
     'multiple'=>true,
   )); ?>         
   <?php echo $form->error($model,'restrict_order_by_status'); ?>
</div>

<h6 class="mb-2 mt-3"><?php echo t("Order Cancellation")?></h6>
<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"cancel_order_enabled",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"cancel_order_enabled",
     'checked'=>$model->cancel_order_enabled==1?true:false
   )); ?>   
  <label class="custom-control-label" for="cancel_order_enabled">
   <?php echo t("Enabled cancellation of order")?>
  </label>
</div>    



<h6 class="mb-2 mt-4"><?php echo t("Food menu layout")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'menu_layout', (array)$menu_layout ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'menu_layout'),
   )); ?>         
   <?php echo $form->error($model,'menu_layout'); ?>
</div>

<h6 class="mb-2 mt-4"><?php echo t("Food category position")?></h6>

<?php 
echo CHtml::activeRadioButtonList($model,'category_position',array(
  'left'=>t("Left"),
  'top'=>t("Top"),
)); 
?>

<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"menu_disabled_inline_addtocart",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"menu_disabled_inline_addtocart",
     'checked'=>$model->menu_disabled_inline_addtocart==1?true:false
   )); ?>   
  <label class="custom-control-label" for="menu_disabled_inline_addtocart">
   <?php echo t("Disabled inline add to cart")?>
  </label>
</div>    

<div style="height:15px;"></div>


  </div> <!--body-->
</div> <!--card-->



<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>