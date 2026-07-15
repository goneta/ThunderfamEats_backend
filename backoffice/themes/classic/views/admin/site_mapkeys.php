
  
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

<h6 class="mb-4"><?php echo t("Choose Map Provider")?></h6>

<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'map_provider', (array)$map_provider,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'map_provider'),
   )); ?>         
   <?php echo $form->error($model,'map_provider'); ?>
</div>		

<h6 class="mb-4"><?php echo t("Google Maps")?></h6>


<div class="form-label-group">    
   <?php echo $form->textField($model,'google_geo_api_key',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'google_geo_api_key')     
   )); ?>   
   <?php    
    echo $form->label($model,'google_geo_api_key'); ?>
   <?php echo $form->error($model,'google_geo_api_key'); ?>   
</div>
   
<div class="form-label-group">    
   <?php echo $form->textField($model,'google_maps_api_key',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'google_maps_api_key')     
   )); ?>   
   <?php    
    echo $form->label($model,'google_maps_api_key'); ?>
   <?php echo $form->error($model,'google_maps_api_key'); ?>   
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'google_maps_api_key_for_mobile',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'google_maps_api_key_for_mobile')     
   )); ?>   
   <?php    
    echo $form->label($model,'google_maps_api_key_for_mobile'); ?>
   <?php echo $form->error($model,'google_maps_api_key_for_mobile'); ?>   
</div>

<div class="d-flex mb-2">
   <div><h6 class="mr-2"><?php echo t("Google Maps Custom Style")?></h6></div>
   <div>
    <a href="https://snazzymaps.com/" target="_blank">https://snazzymaps.com/</a>
   </div>
</div>
<div class="form-label-group mt-2">    
   <?php echo $form->textArea($model,'map_custom_style',array(
     'class'=>"form-control form-control-text",     
     'style'=>'height:120px'
   )); ?>      
   <?php echo $form->error($model,'map_custom_style'); ?>
</div>

<h6 class="mb-4"><?php echo t("Mapbox")?></h6>

<div class="form-label-group">    
   <?php echo $form->textField($model,'mapbox_access_token',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'mapbox_access_token')     
   )); ?>   
   <?php    
    echo $form->label($model,'mapbox_access_token'); ?>
   <?php echo $form->error($model,'mapbox_access_token'); ?>   
</div>

<div class="card mt-3">
  <div class="card-header bg-primary text-white">
    <?php echo t("API Key Restriction Instructions for Google Maps Platform"); ?>
  </div>
  <div class="card-body">

    <h5 class="card-title">1. <?php echo t("Google Maps Platform API Key (Server-side)"); ?></h5>
    <p class="card-text"><?php echo t("Used for server requests such as geocoding, directions, and distance calculations."); ?></p>
    <ol>
      <li><?php echo t("Go to"); ?> <a href="https://console.cloud.google.com/" target="_blank"><?php echo t("Google Cloud Console"); ?></a>.</li>
      <li><?php echo t("Navigate to"); ?> <strong><?php echo t("APIs & Services → Credentials"); ?></strong>.</li>
      <li><?php echo t("Enable the following APIs:"); ?>
        <ul>
          <li><?php echo t("Directions API"); ?></li>
          <li><?php echo t("Distance Matrix API"); ?></li>
          <li><?php echo t("Geocoding API"); ?></li>
          <li><?php echo t("Places API"); ?></li>
          <li><?php echo t("Maps Static API"); ?></li>
        </ul>
      </li>
      <li><?php echo t("Under"); ?> <strong><?php echo t("Key restrictions"); ?></strong>, <?php echo t("choose"); ?> <strong><?php echo t("IP addresses"); ?></strong>.</li>
      <li><?php echo t("Enter your server's IP address (e.g., {ip})", ['{ip}' => '<code>123.45.67.89</code>']); ?></li>
      <li><?php echo t("Click"); ?> <strong><?php echo t("Save"); ?></strong>.</li>
    </ol>

    <h5 class="card-title mt-4">2. <?php echo t("Google Maps JavaScript API Key (Website)"); ?></h5>
    <p class="card-text"><?php echo t("Used for displaying interactive maps in your website."); ?></p>
    <ol>
      <li><?php echo t("Go to"); ?> <strong><?php echo t("APIs & Services → Credentials"); ?></strong>.</li>
      <li><?php echo t("Select your {api} for the website.", ['{api}' => '<strong>'.t("Maps JavaScript API key").'</strong>']); ?></li>
      <li><?php echo t("Under"); ?> <strong><?php echo t("Key restrictions"); ?></strong>, <?php echo t("select"); ?> <strong><?php echo t("HTTP referrers (websites)"); ?></strong>.</li>
      <li><?php echo t("Add your domain(s) in this format:"); ?>
        <ul>
          <li><code>https://yourdomain.com/*</code></li>
          <li><code>https://www.yourdomain.com/*</code></li>
        </ul>
      </li>
      <li><?php echo t("Click"); ?> <strong><?php echo t("Save"); ?></strong>.</li>
    </ol>

    <h5 class="card-title mt-4">3. <?php echo t("Google Maps JavaScript API Key (Mobile App)"); ?></h5>
    <p class="card-text"><?php echo t("Used when loading maps inside mobile apps"); ?></p>
    <ol>
      <li><?php echo t("Go to"); ?> <strong><?php echo t("APIs & Services → Credentials"); ?></strong>.</li>
      <li><?php echo t("Select the API key used in your mobile app."); ?></li>
      <li><strong><?php echo t("Do not"); ?></strong> <?php echo t("set domain or IP restrictions — instead, scroll to"); ?> <strong><?php echo t("API restrictions"); ?></strong>.</li>
      <li><?php echo t("Select"); ?> <strong><?php echo t("Restrict key"); ?></strong> <?php echo t("and enable only:"); ?>
        <ul>
          <li><?php echo t("Google Maps JavaScript API"); ?></li>
        </ul>
      </li>
      <li><?php echo t("Click"); ?> <strong><?php echo t("Save"); ?></strong>.</li>
    </ol>

    <div class="alert alert-warning mt-4">
      <strong><?php echo t("Tip:"); ?></strong> <?php echo t("Use separate API keys for server, website, and mobile to improve security and manage usage limits more easily."); ?>
    </div>
  </div>
</div>


<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>