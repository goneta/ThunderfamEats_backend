<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'frm-merchant',
	'enableAjaxValidation'=>false,
	'htmlOptions' => array('enctype' => 'multipart/form-data'),
)); ?>

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
<div class="row" style="padding-bottom: 10px;text-align:center" id="vue-printhis">
  <div class="col-md-6">
  <?php if($download_qrcode_pickup != ""): ?>
  <a href="<?php echo $download_qrcode_pickup;?>" target="_blank" class="btn btn-secondary">
    <?php echo t("Download Pickup QR Code")?>
    </a>
    <?php endif;?>
  </div>
  <div class="col-md-6">
  <?php if($download_qrcode_dinein != ""): ?>
  <a href="<?php echo $download_qrcode_dinein;?>" target="_blank" class="btn btn-secondary">
    <?php echo t("Download Dinein QR Code")?>
    </a>
    <?php endif;?>
  </div>
</div>
<div class="row">
  <div class="col-md-6">
 
 <div class="form-label-group">    
   <?php echo $form->textField($model,'restaurant_name',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'restaurant_name'),     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'restaurant_name'); ?>
   <?php echo $form->error($model,'restaurant_name'); ?>
</div>
 
 </div>
 <div class="col-md-6">
 
 <div class="form-label-group">    
   <?php echo $form->textField($model,'restaurant_slug',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'restaurant_slug'),     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'restaurant_slug'); ?>
   <?php echo $form->error($model,'restaurant_slug'); ?>
</div>
 
 </div>
</div> <!--row-->


 <div class="form-label-group">    
   <?php echo $form->textField($model,'contact_name',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'contact_name'),     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'contact_name'); ?>
   <?php echo $form->error($model,'contact_name'); ?>
</div>


<div class="row">
 <div class="col-md-6">
 
 <div class="form-label-group">    
   <?php echo $form->textField($model,'contact_phone',array(
     'class'=>"form-control form-control-text mask_mobile",
     'placeholder'=>$form->label($model,'contact_phone'),     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'contact_phone'); ?>
   <?php echo $form->error($model,'contact_phone'); ?>
</div>
 
 </div>
 <div class="col-md-6">
 
 <div class="form-label-group">    
   <?php echo $form->textField($model,'contact_email',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'contact_email'),     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'contact_email'); ?>
   <?php echo $form->error($model,'contact_email'); ?>
</div>
 
 </div>
</div> <!--row-->


<div id="vue-uploader">
<component-uploader
ref="uploader"
max_file="<?php echo Yii::app()->params->dropzone['max_file'];?>"
max_file_size = "<?php echo Yii::app()->params->dropzone['max_file_size']?>"
select_type="single"
field = "photo"
field_path = "path"
inline="false"
selected_file="<?php echo $model->logo;?>"
upload_path="<?php echo $upload_path?>"
save_path="<?php echo $model->path?>"

@set-afer-upload="afterUpload"
@set-afer-delete="afterDelete"
:label="{
    select_file:'<?php echo CJavaScript::quote(t("Select File"))?>',       
    upload_new:'<?php echo CJavaScript::quote(t("Upload New"))?>',     
    upload_button:'<?php echo CJavaScript::quote(t("Logo"))?>',     
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
<div>
  <?php echo t("Recommended image size: 600x600 pixels.")?>
</div>


<div class="pt-3">
<component-uploader
ref="uploader"
max_file="<?php echo Yii::app()->params->dropzone['max_file'];?>"
max_file_size = "<?php echo Yii::app()->params->dropzone['max_file_size']?>"
select_type="single"
field = "header_image"
field_path = "path2"
inline="false"
selected_file="<?php echo $model->header_image;?>"
upload_path="<?php echo $upload_path?>"
save_path="<?php echo $model->path2?>"

@set-afer-upload="afterUpload"
@set-afer-delete="afterDelete"
:label="{
    select_file:'<?php echo CJavaScript::quote(t("Select File"))?>',       
    upload_new:'<?php echo CJavaScript::quote(t("Upload New"))?>',     
    upload_button:'<?php echo CJavaScript::quote(t("Header"))?>',     
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
</div>
<div>
  <?php echo t("Recommended image size: 1400x600 pixels.")?>
</div>

</div> <!--vue-->


<h6 class="mb-4 mt-4"><?php echo t("About")?></h6>
<div class="form-label-group mt-2">    
   <?php echo $form->textArea($model,'description',array(
     'class'=>"form-control form-control-text summernote",     
     'placeholder'=>t("Contact Content")
   )); ?>      
   <?php echo $form->error($model,'description'); ?>
</div>

<h6 class="mb-4 mt-4"><?php echo t("Short About")?></h6>
<div class="form-label-group mt-2">    
   <?php echo $form->textArea($model,'short_description',array(
     'class'=>"form-control form-control-text textarea_min",     
     'placeholder'=>t(""),     
   )); ?>      
   <?php echo $form->error($model,'short_description'); ?>
</div>

<!--TRANSLATION-->
<?php if(isset($language)):?>
<?php if(is_array($language) && count($language)>=1 ):?>
<?php 
$this->widget('application.components.WidgetTranslation',array(
  'form'=>$form,
  'model'=>$model,
  'language'=>$language,
  'field'=>$fields,
  'data'=>$data
));
?>   
<?php endif;?>
<?php endif;?>
<!--END TRANSLATION-->	

<h6 class="mb-2 mt-4"><?php echo t("Cuisine")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'cuisine2', (array)$cuisine,array(
     'class'=>"form-control custom-select form-control-select select_two",
     'multiple'=>true,
     'placeholder'=>$form->label($model,'cuisine2'),
   )); ?>         
   <?php echo $form->error($model,'cuisine2'); ?>
</div>

<h6 class="mb-2 mt-4"><?php echo t("Online Services")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'service2', (array)$services,array(
     'class'=>"form-control custom-select form-control-select select_two",
     'placeholder'=>$form->label($model,'service2'),
     'multiple'=>true,
   )); ?>         
   <?php echo $form->error($model,'service2'); ?>
</div>

<h6 class="mb-2 mt-4"><?php echo t("POS Services")?> <span class="font11 text-grey">(<?php echo t("If empty will use online services instead")?>)</span></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'service3', (array)$services,array(
     'class'=>"form-control custom-select form-control-select select_two",
     'placeholder'=>$form->label($model,'service3'),
     'multiple'=>true,
   )); ?>         
   <?php echo $form->error($model,'service3'); ?>   
</div>

<h6 class="mb-2 mt-4"><?php echo t("Tableside Services")?> <span class="font11 text-grey">(<?php echo t("If empty will use online services instead")?>)</span></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'tableside_services', (array)$services,array(
     'class'=>"form-control custom-select form-control-select select_two",
     'placeholder'=>$form->label($model,'tableside_services'),
     'multiple'=>true,
   )); ?>         
   <?php echo $form->error($model,'tableside_services'); ?>   
</div>

<h6 class="mb-2 mt-4"><?php echo t("Tags")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'tags',(array)$tags,array(
     'class'=>"form-control custom-select form-control-select select_two",
     'placeholder'=>$form->label($model,'tags'),
     'multiple'=>true,
   )); ?>         
   <?php echo $form->error($model,'tags'); ?>
</div>


<?php if(isset($is_admin)):?>
<?php if($is_admin):?>
<h6 class="mb-4"><?php echo t("Featured")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'featured',(array)$featured,array(
     'class'=>"form-control custom-select form-control-select select_two",
     'placeholder'=>$form->label($model,'featured'),
     'multiple'=>true,
   )); ?>         
   <?php echo $form->error($model,'featured'); ?>
</div>
<?php endif?>
<?php endif?>

<div class="row">
 <div class="col-md-6">
 
 <div class="form-label-group">    
   <?php echo $form->textField($model,'delivery_distance_covered',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'delivery_distance_covered'),     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'delivery_distance_covered'); ?>
   <?php echo $form->error($model,'delivery_distance_covered'); ?>
</div>
 
 </div>
 <div class="col-md-6">
 
 <div class="form-label-group">    
   <?php echo $form->dropDownList($model,'distance_unit',(array)$unit,array(
     'class'=>"form-control custom-select form-control-select",
     'placeholder'=>$form->label($model,'distance_unit'),
   )); ?>         
   <?php echo $form->error($model,'distance_unit'); ?>
</div>
 
 </div>
</div> <!--row-->

<div class="row">
 <div class="col-md-6">
 
 <div class="form-label-group">    
   <?php echo $form->textField($model,'delivery_zone_covered',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'delivery_zone_covered'),     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'delivery_zone_covered'); ?>
   <!-- <?php echo $form->error($model,'delivery_zone_covered'); ?> -->
</div>
 
 </div>
 <div class="col-md-6">
 
 <div class="form-label-group">    
   <?php echo $form->dropDownList($model,'delivery_zone_type',(array)$zone_type,array(
     'class'=>"form-control custom-select form-control-select",
     'placeholder'=>$form->label($model,'delivery_zone_type'),
   )); ?>         
   <!-- <?php echo $form->error($model,'delivery_zone_type'); ?> -->
</div>
 
 </div>
</div> <!--row-->

<div id="map" style="height: 300px;"></div>
<input type="hidden" id="drawn_latitude" name="drawn_latitude" />
<input type="hidden" id="drawn_longitude" name="drawn_longitude" />

<DIV class="row mt-2 mb-2">
  <div class="col-md-6">
  
  <div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"is_ready",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>2,
     'id'=>"is_ready",
     'checked'=>$model->is_ready==2?true:false
   )); ?>   
  <label class="custom-control-label" for="is_ready">
   <?php echo t("Published Merchant")?>
  </label>
</div>    
  
  
  </div><!-- col-->
  
</DIV>
<!--row-->

<?php if($show_status):?>
<h6 class="mb-4"><?php echo t("Status")?></h6>

<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'status', (array) $status,array(
     'class'=>"form-control custom-select form-control-select",
     'placeholder'=>$form->label($model,'status'),
   )); ?>         
   <?php echo $form->error($model,'status'); ?>
</div>
<?php endif;?>


<div class="row text-left mt-4">
<div class="col-md-12 m-0">
<?php echo CHtml::submitButton('Login',array(
'class'=>"btn btn-green btn-full",
'value'=>CommonUtility::t("Save")
)); ?>
</div>
</div>

<?php $this->endWidget(); ?>
 <?php $this->renderPartial("/admin/modal_delete_image");?>
 <!-- Include Google Maps API -->
<?php 
$google_maps_api_key = isset(Yii::app()->params['settings']['google_geo_api_key']) ? Yii::app()->params['settings']['google_geo_api_key'] : '';
if(empty($google_maps_api_key)){
	$google_maps_api_key = isset(Yii::app()->params['settings']['google_maps_api_key']) ? Yii::app()->params['settings']['google_maps_api_key'] : '';
}
?>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $google_maps_api_key; ?>&libraries=drawing&callback=initMap" async defer></script>

<!-- Add this script to initialize Google Maps and Drawing Library -->
<script>
  var map;
  var drawnPolygon = ""; 
  var center_lat = <?php echo $model['latitude']>0?$model['latitude']:0 ?> ;
  var center_lon = <?php echo $model['lontitude']>0?$model['lontitude']:0 ?> ;
  function initMap() {
    console.log(<?php echo $model['latitude'] ?>)
    map = new google.maps.Map(document.getElementById('map'), {
      center: { lat: center_lat, lng: center_lon },
      zoom: 17,
    });
    var delivery_zone_covered = [];
    // Add a drawing manager
    var drawingManager = new google.maps.drawing.DrawingManager({
      drawingMode: google.maps.drawing.OverlayType.POLYGON,
      drawingControl: true,
      drawingControlOptions: {
        position: google.maps.ControlPosition.TOP_CENTER,
        drawingModes: ['polygon'],
      },
      polygonOptions:{
        draggable: true,
        editable: true
      }
    });
    drawingManager.setMap(map);
    
    // Retrieve the coordinates from the input field
    var coordinatesString = document.getElementById('AR_merchant_delivery_zone_covered').value;

    if(coordinatesString !== ""){
      var coordinatePairs = coordinatesString.replace(/[{()}]/g, '').split(',');

      // Parse the coordinates string into an array of LatLng objects
      // Stored format is lng lat (for MySQL WKT), but Google Maps uses lat lng
      var coordinates = coordinatePairs.map(function (pair) {
        var parts = pair.trim().split(' ');
        console.log(parts);
        // If stored as lng lat, swap to lat lng for Google Maps
        if(parts.length >= 2){
          return { lat: parseFloat(parts[1]), lng: parseFloat(parts[0]) };
        }
        return { lat: parseFloat(parts[0]), lng: parseFloat(parts[1]) };
      });
      // Create a polygon using the retrieved coordinates
      drawnPolygon = new google.maps.Polygon({
        paths: coordinates,
        editable: true, // Make the polygon editable
        draggable: true, // Make the polygon draggable
        map: map,
      });
  
      // Event listener for polygon edit
      google.maps.event.addListener(drawnPolygon, 'mouseup', function (event) {
        updateHiddenFields(drawnPolygon);
      });
  
      // Event listener for polygon click
      google.maps.event.addListener(drawnPolygon, 'click', function (clickEvent) {
        // Capture the individual point's coordinates
        var clickedLatitude = clickEvent.latLng.lat();
        var clickedLongitude = clickEvent.latLng.lng();
  
        // Do something with the clicked coordinates, e.g., display in console
        console.log('Clicked Point:', clickedLatitude, clickedLongitude);
      });
    }
  
  google.maps.event.addListener(drawingManager, 'overlaycomplete', function (event) {
      if (event.type === 'polygon') {
        if (drawnPolygon) {
          drawnPolygon.setMap(null);
        }
        var coordinates = event.overlay.getPath().getArray();
        var loop_closer = '';
        // Update hidden fields with coordinates
        document.getElementById('drawn_latitude').value = coordinates.map(function (point) {
          return point.lat();
        }).join(',');

        document.getElementById('drawn_longitude').value = coordinates.map(function (point) {
          return point.lng();
        }).join(',');
       var i = 0
       var loop_closer = '';
        // Build WKT format: POLYGON((lng lat, lng lat, ...)) - MySQL expects longitude first
        var polygonCoords = coordinates.map(function (point) {
          i++
          if(i==1){
            loop_closer = point.lng() + " " + point.lat();
          }
          return point.lng() + " " + point.lat();
        }).join(',');
        // Store only the inner coordinates: ((lng lat, lng lat, ...))
        // The POLYGON prefix will be added in PHP when querying
        document.getElementById('AR_merchant_delivery_zone_covered').value = "((" + polygonCoords + ","+ loop_closer + "))";
        i=0;
        drawnPolygon = event.overlay;
      }
    });
  }
  // Function to update hidden fields with polygon coordinates
  function updateHiddenFields(polygon) {
    var coordinates = polygon.getPath().getArray();

    // Update hidden field with coordinates
    // Build WKT format: POLYGON((lng lat, lng lat, ...)) - MySQL expects longitude first
    var i = 0
    var loop_closer = '';
    var polygonCoords = coordinates.map(function (point) {
      i++
      if(i==1){
        loop_closer = point.lng() + " " + point.lat();
      }
      return point.lng() + " " + point.lat();
    }).join(',');
    // Store only the inner coordinates: ((lng lat, lng lat, ...))
    // The POLYGON prefix will be added in PHP when querying
    document.getElementById('AR_merchant_delivery_zone_covered').value = "((" + polygonCoords + ","+loop_closer + "))";
    i=0;
    
  }
</script>