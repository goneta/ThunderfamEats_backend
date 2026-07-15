<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'frm-merchant',
	'enableAjaxValidation'=>false,
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


<h6 class="mb-4 mt-4"><?php echo t("Address details")?></h6>
 <div class="form-label-group">    
   <?php echo $form->textField($model,'address',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'address'),     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'address'); ?>
   <?php echo $form->error($model,'address'); ?>
</div>

	

<h6 class="mb-4 mt-4"><?php echo t("Geolocation")?></h6>


<div class="row">
 <div class="col-md-6">
 
 <div class="form-label-group">    
   <?php echo $form->textField($model,'latitude',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'latitude'),     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'latitude'); ?>
   <?php echo $form->error($model,'latitude'); ?>
</div>
 
 </div>
 <div class="col-md-6">
 
 <div class="form-label-group">    
   <?php echo $form->textField($model,'lontitude',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'lontitude'),     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'lontitude'); ?>
   <?php echo $form->error($model,'lontitude'); ?>
</div>
 
 </div>
</div> <!--row-->


<p class="dim">
<?php echo t("Get your address geolocation via service like [link] or [link2], entering invalid coordinates will make your store not available for ordering",array(
  '[link]'=>'<a href="https://www.maps.ie/coordinates.html" target="_blank">https://www.maps.ie/coordinates.html</a>',
  '[link2]'=>'<a href="https://www.latlong.net/" target="_blank">https://www.latlong.net/</a>',
))?></p>

<h6 class="mb-4 mt-4"><?php echo t("Radius distance covered")?></h6>
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



<div class="row text-left mt-4">
<div class="col-md-12 m-0">
<?php echo CHtml::submitButton('save',array(
'class'=>"btn btn-green btn-full",
'value'=>t("Save")
)); ?>
</div>
</div>

<?php $this->endWidget(); ?>
 