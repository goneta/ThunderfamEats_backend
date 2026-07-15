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

<h6 class="mb-2 mt-4"><?php echo t("Banner Type")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'banner_type', (array) $banner_type,array(
     'class'=>"form-control custom-select form-control-select banner_type",
     'placeholder'=>$form->label($model,'banner_type'),
   )); ?>         
   <?php echo $form->error($model,'banner_type'); ?>
</div>

<div class="section-restaurant section-banner">
<h6 class="mb-2"><?php echo t("Select Merchant")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'meta_value1',(array)$items,array(
     'class'=>"form-control custom-select form-control-select select_two_ajax",
     'placeholder'=>$form->label($model,'meta_value1'),   
     'action'=>'search_merchant'
   )); ?>         
   <?php echo $form->error($model,'meta_value1'); ?>
</div>
</div>

<div class="section-food section-banner">
<h6 class="mb-2"><?php echo t("Select Item")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'meta_value2',(array)$items,array(
     'class'=>"form-control custom-select form-control-select select_two_ajax2",
     'placeholder'=>$form->label($model,'meta_value2'),   
     'action'=>'search_item'
   )); ?>         
   <?php echo $form->error($model,'meta_value2'); ?>
</div>
</div>

<div class="section-restaurant_featured section-banner">
<h6 class="mb-2"><?php echo t("Select Featured")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'meta_value3',(array)$restaurant_featured,array(
     'class'=>"form-control custom-select form-control-select",
     'placeholder'=>$form->label($model,'meta_value3'),        
   )); ?>         
   <?php echo $form->error($model,'meta_value3'); ?>
</div>
</div>

<div class="section-cuisine section-banner">
<h6 class="mb-2"><?php echo t("Select Cuisine")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'meta_value4',(array)$cuisine_list,array(
     'class'=>"form-control custom-select form-control-select",
     'placeholder'=>$form->label($model,'meta_value4'),        
   )); ?>         
   <?php echo $form->error($model,'meta_value3'); ?>
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

<?php if($home_search_mode=="address"):?>
<h6 class="mb-2"><?php echo t("Coordinates")?></h6>
<p class="mb-0"><?php echo t("If coordinates is not set, this banner will shown to all locations.")?></p>

<p class="dim">
  <?php echo t("Get your address geolocation via service like [link] or [link2]",array(
    '[link]'=>'<a href="https://www.maps.ie/coordinates.html" target="_blank">https://www.maps.ie/coordinates.html</a>',
    '[link2]'=>'<a href="https://www.latlong.net/" target="_blank">https://www.latlong.net/</a>',
  ))?>
</p>

<div class="row">
  <div class="col">
  
      <div class="form-label-group">    
      <?php echo $form->textField($model,'latitude',array(
          'class'=>"form-control form-control-text",
          'placeholder'=>$form->label($model,'latitude')     
        )); ?>   
        <?php    
          echo $form->labelEx($model,'latitude'); ?>
        <?php echo $form->error($model,'latitude'); ?>
      </div>

  </div>
  <div class="col">
  
      <div class="form-label-group">    
      <?php echo $form->textField($model,'longitude',array(
          'class'=>"form-control form-control-text",
          'placeholder'=>$form->label($model,'longitude')     
        )); ?>   
        <?php    
          echo $form->labelEx($model,'longitude'); ?>
        <?php echo $form->error($model,'longitude'); ?>
      </div>

  </div>
</div>

<div class="row">
  <div class="col-6">
      <div class="form-label-group">    
      <?php echo $form->textField($model,'radius',array(
          'class'=>"form-control form-control-text",
          'placeholder'=>$form->label($model,'radius')     
        )); ?>   
        <?php    
          echo $form->labelEx($model,'radius'); ?>
        <?php echo $form->error($model,'radius'); ?>
      </div>
  </div>
  <div class="col-6">    
    <div class="form-label-group">    
    <?php echo $form->dropDownList($model,'radius_unit', (array)$units ,array(
        'class'=>"form-control custom-select form-control-select",     
        'placeholder'=>$form->label($model,'radius_unit'),
    )); ?>         
    <?php echo $form->error($model,'radius_unit'); ?>
    </div>		
  </div>
</div>
<?php endif;?>

<?php if($home_search_mode=="location"):?>
<h6 class="mb-2"><?php echo t("Locations")?></h6>
<p><?php echo t("If no option is selected, this will shown to all locations.")?></p>
<div class="row">
  <div class="col">
    <div id="form-label-group2" class="form-label-group">  
       <label for="select_country_id"><?php echo t("Country")?></label>
      <select name="AR_banner[country_id]" class="form-control select_country_id" >	  	
         <?php if(isset($location_data['country'])):?>
            <option value="<?php echo $location_data['country']['value'];?>" selected="selected"><?php echo $location_data['country']['label'];?></option>  
         <?php endif;?>         
      </select>
    </div>
  </div>
  <div class="col">
  <div id="form-label-group2" class="form-label-group">  
      <label for="select_state_id"><?php echo t("State/Region")?></label>
      <select name="AR_banner[state_id]" class="form-control select_state_id" >	  	         
         <?php if(isset($location_data['state'])):?>
            <option value="<?php echo $location_data['state']['value'];?>" selected="selected"><?php echo $location_data['state']['label'];?></option>  
         <?php endif;?>         
      </select>
    </div>
  </div>
</div>

<div class="row">
  <div class="col">
    <div id="form-label-group2" class="form-label-group">  
      <label for="select_city_id"><?php echo t("City")?></label>
      <select name="AR_banner[city_id]" class="form-control select_city_id" >	  	
         <?php if(isset($location_data['city'])):?>
            <option value="<?php echo $location_data['city']['value'];?>" selected="selected"><?php echo $location_data['city']['label'];?></option>  
         <?php endif;?>                  
      </select>
    </div>
  </div>
  <div class="col">
  <div id="form-label-group2" class="form-label-group">  
      <label for="select_area_id"><?php echo t("Distric/Area/neighborhood")?></label>
      <select name="AR_banner[area_id]" class="form-control select_area_id" >	  	         
         <?php if(isset($location_data['area'])):?>
            <option value="<?php echo $location_data['area']['value'];?>" selected="selected"><?php echo $location_data['area']['label'];?></option>  
         <?php endif;?>                  
      </select>
    </div>
  </div>
</div>
<?php endif;?>

<h6 class="mb-4"><?php echo t("Status")?></h6>
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