
  
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


<h6 class="mb-2">  
  <?php echo t("Search Mode")?>
</h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'home_search_mode', (array)$search_type,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'home_search_mode'),
   )); ?>         
   <?php echo $form->error($model,'home_search_mode'); ?>
</div>		   

<ul class="nav nav-tabs" id="myTab" role="tablist">
  <li class="nav-item">
    <a class="nav-link <?php echo $home_search_mode=='address'?'active':'';?>" id="home-tab" data-toggle="tab" href="#map-based" role="tab" aria-controls="home" aria-selected="true">
      <?php echo t("Map-Based Settings")?>
    </a>
  </li>
  <?php if($location_addon):?>
  <li class="nav-item">
    <a class="nav-link <?php echo $home_search_mode=='location'?'active':'';?>" id="profile-tab" data-toggle="tab" href="#location-based" role="tab" aria-controls="profile" aria-selected="false">
      <?php echo t("Location-Based Settings")?>
    </a>
  </li>  
  <?php endif;?>
</ul>

<div class="tab-content" id="myTabContent">
  <!-- MAP BASED -->
  <div class="tab-pane fade pt-3 <?php echo $home_search_mode=='address'?'show active':'';?>" id="map-based" role="tabpanel" aria-labelledby="map-based">
  
  
  <div class="mb-2 mt-2"><?php echo t("Set Specific Country")?> (<?php echo t("maximum of 5 country")?>)</div>
  <div class="form-label-group">    
    <?php echo $form->dropDownList($model,'merchant_specific_country',(array)$country_list,array(
      'class'=>"form-control custom-select form-control-select select_two",
      'placeholder'=>$form->label($model,'merchant_specific_country'),
      'multiple'=>true,
    )); ?>         
    <?php echo $form->error($model,'merchant_specific_country'); ?>
    <small class="form-text text-muted mb-2">
      <?php echo t("leave empty to show all country")?>
    </small>
  </div>

  <div class="custom-control custom-switch custom-switch-md">  
    <?php echo $form->checkBox($model,"enabled_auto_detect_address",array(
      'class'=>"custom-control-input checkbox_child",     
      'value'=>1,
      'id'=>"enabled_auto_detect_address",
      'checked'=>$model->enabled_auto_detect_address==1?true:false
    )); ?>   
    <label class="custom-control-label" for="enabled_auto_detect_address">
    <?php echo t("Enabled detect address")?>
    </label>
  </div>    

  <div class="custom-control custom-switch custom-switch-md">  
    <?php echo $form->checkBox($model,"use_local_distance",array(
      'class'=>"custom-control-input checkbox_child",     
      'value'=>1,
      'id'=>"use_local_distance",
      'checked'=>$model->use_local_distance==1?true:false
    )); ?>   
    <label class="custom-control-label" for="use_local_distance">
    <?php echo t("Use Haversine formula")?>
    </label>
  </div>    

  <div class="mb-2 mt-2"><?php echo t("Default Map Location")?></div>

  <div class="form-label-group">    
    <?php echo $form->textField($model,'default_location_lat',array(
      'class'=>"form-control form-control-text",
      'placeholder'=>!empty($model->default_location_lat) ? $model->default_location_lat : '5.3600',
    )); ?>   
    <?php    
      echo $form->labelEx($model,'default_location_lat'); ?>
    <?php echo $form->error($model,'default_location_lat'); ?>
    <small class="form-text text-muted"><?php echo t("Example for Ivory Coast (Abidjan): 5.3600"); ?></small>
  </div>

  <div class="form-label-group">    
    <?php echo $form->textField($model,'default_location_lng',array(
      'class'=>"form-control form-control-text",
      'placeholder'=>!empty($model->default_location_lng) ? $model->default_location_lng : '-4.0083',
    )); ?>   
    <?php    
      echo $form->labelEx($model,'default_location_lng'); ?>
    <?php echo $form->error($model,'default_location_lng'); ?>
    <small class="form-text text-muted"><?php echo t("Example for Ivory Coast (Abidjan): -4.0083"); ?></small>
  </div>

  <h6 class="mb-1"><?php echo t("Default Distance Unit")?></h6>
    <div class="form-label-group">    
    <?php echo $form->dropDownList($model,'default_distance_unit', (array)$unit ,array(
        'class'=>"form-control custom-select form-control-select",     
        'placeholder'=>$form->label($model,'default_distance_unit'),
    )); ?>         
    <?php echo $form->error($model,'default_distance_unit'); ?>
    </div>		
  
  <p class="dim">
  <?php echo t("Get your address geolocation via service like [link] or [link2]",array(
    '[link]'=>'<a href="https://www.maps.ie/coordinates.html" target="_blank">https://www.maps.ie/coordinates.html</a>',
    '[link2]'=>'<a href="https://www.latlong.net/" target="_blank">https://www.latlong.net/</a>',
  ))?>
  </p>

  <div class="mb-2 mt-2"><?php echo t("Address Format Options")?></div>

  <div class="custom-control custom-radio mb-2">  
    <?php 
    echo $form->radioButton($model, 'address_format_use', array(
      'value'=>1,
      'uncheckValue'=>null,
      'id'=>'addressform1',
      'class'=>"custom-control-input"
    ));
    ?>
    <label class="custom-control-label" for="addressform1"><?php echo t("Basic address format")?></label>
    <div class="text-muted">(<?php echo t("street number and street name fields")?>)</div>
  </div>

  <div class="custom-control custom-radio mb-2">  
    <?php 
    echo $form->radioButton($model, 'address_format_use', array(
      'value'=>2,
      'uncheckValue'=>null,
      'id'=>'addressform2',
      'class'=>"custom-control-input"
    ));
    ?>
    <label class="custom-control-label" for="addressform2"><?php echo t("Extended address format")?></label>
    <div class="text-muted">(<?php echo t("street number,street name,entrance,floor,door and company fields")?>)</div>
  </div> 

  <div class="custom-control custom-radio mb-2">  
    <?php 
    echo $form->radioButton($model, 'address_format_use', array(
      'value'=>3,
      'uncheckValue'=>null,
      'id'=>'addressform3',
      'class'=>"custom-control-input"
    ));
    ?>
    <label class="custom-control-label" for="addressform3"><?php echo t("Current location format")?></label>
    <div class="text-muted">(<?php echo t("location name")?>)</div>
  </div>  


  </div>
  <!-- MAP BASED -->

  <!-- LOCATION BASED -->
  <div class="tab-pane fade pt-3 <?php echo $home_search_mode=='location'?'show active':'';?>" id="location-based" role="tabpanel" aria-labelledby="location-based">
    <?php unset($country_list['all']);?>

    <div class="mb-2 mt-2"><?php echo t("Location Country")?></div>
    <div class="form-label-group">    
      <?php echo $form->dropDownList($model,'location_default_country', (array)$country_list,array(
        'class'=>"form-control custom-select form-control-select",     
        'placeholder'=>$form->label($model,'location_default_country'),
      )); ?>         
      <?php echo $form->error($model,'location_default_country'); ?>
      <small class="form-text text-muted"><?php echo t("Recommended for Ivory Coast: CI (Côte d'Ivoire)"); ?></small>
    </div>		   

    <div class="mb-2 mt-2"><?php echo t("Location search type")?></div>
     <div class="form-label-group">    
      <?php echo $form->dropDownList($model,'location_searchtype', (array)$location_search,array(
        'class'=>"form-control custom-select form-control-select",     
        'placeholder'=>$form->label($model,'location_searchtype'),
      )); ?>         
      <?php echo $form->error($model,'location_searchtype'); ?>
    </div>

    <div class="custom-control custom-switch custom-switch-md">  
      <?php echo $form->checkBox($model,"location_enabled_map_selection",array(
          'class'=>"custom-control-input checkbox_child",     
          'value'=>1,
          'id'=>"location_enabled_map_selection",
          'checked'=>$model->location_enabled_map_selection==1?true:false
      )); ?>   
      <label class="custom-control-label" for="location_enabled_map_selection">
         <?php echo t("Enabled get coordinates from maps")?>      
      </label>          
    </div>  

    <small class="form-text text-muted mb-2">  
        <?php echo t("if enabled you need to put your map api keys")?>.
    </small>

  </div>
  <!-- LOCATION BASED -->

</div>
<!-- TAB -->


  </div> <!--body-->
</div> <!--card-->



<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>