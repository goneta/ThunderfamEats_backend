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
   <?php echo $form->textField($model,'type_id',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'type_id'),
     'readonly'=>$model->isNewRecord?false:true
   )); ?>   
   <?php    
    echo $form->labelEx($model,'type_id'); ?>
   <?php echo $form->error($model,'type_id'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'type_name',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'type_name'),     
   )); ?>   
   <?php echo $form->labelEx($model,'type_name'); ?>
   <?php echo $form->error($model,'type_name'); ?>
</div>

<?php if(is_array($service_list) && count($service_list)>=1):?>  
  <?php foreach ($service_list as $key => $items):?>  
    <div class="row align-items-center">
      <div class="col-2">        
        <h6 class="m-0"><?php  echo $items['service_name']?></h6>
      </div>   
      <div class="col">
      
      <div class="form-label-group">    
        <?php echo $form->dropDownList($model,"commission_type[".$items['service_code']."]", (array) $commission_type_list,array(
          'class'=>"form-control custom-select form-control-select",
          'placeholder'=>$form->label($model,"commission_type[".$items['service_code']."]"),
        )); ?>         
        <?php echo $form->error($model,"commission_type[".$items['service_code']."]"); ?>
      </div>

      </div> 
      <div class="col">

      <div class="form-label-group">    
        <?php echo $form->textField($model,"commission_value[".$items['service_code']."]",array(
          'class'=>"form-control form-control-text",
          'placeholder'=>$form->label($model,"commission_value[".$items['service_code']."]"),     
        )); ?>   
        <?php echo $form->labelEx($model,"commission_value[".$items['service_code']."]",[
          'label'=>t("Commission")
        ]); ?>
        <?php echo $form->error($model,"commission_value[".$items['service_code']."]"); ?>
      </div> 

      </div>
    </div>
    <!-- row -->
  <?php endforeach;?>    
<?php endif;?>


<!-- <h6 class="mb-3 mt-4"><?php echo t("Commission Computation Method")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'based_on', (array) $commission_based,array(
     'class'=>"form-control custom-select form-control-select",
     'placeholder'=>$form->label($model,'based_on'),
   )); ?>         
   <?php echo $form->error($model,'based_on'); ?>
</div>

<h6><?php echo t("Commission Formula")?></h6>
<ul>
  <li class="mb-1">
    <div class="font-weight-bold"><?php echo t("Method 1")?></div>
    <p class="m-0"><?php echo t("Admin earnings = subtotal * commission")?></p>
    <p class="m-0"><?php echo t("Merchant earnings = subtotal - commission")?></p>
  </li>
  <li class="mb-1">
    <div class="font-weight-bold"><?php echo t("Method 2")?></div>
    <p class="m-0"><?php echo t("Admin earnings = commission with tax + service fee + delivery fee")?></p>
    <p class="m-0"><?php echo t("Merchant earnings = subtotal - commission with tax")?></p>
  </li>
  <li class="mb-1">
    <div class="font-weight-bold"><?php echo t("Method 3")?></div>
    <p class="m-0"><?php echo t("Admin earnings = commission + tax on commission + service fee + tax on service fee")?></p>
    <p class="m-0"><?php echo t("Merchant earnings = subtotal + tax - commission - tax on commission")?></p>
  </li>
</ul> -->

<h6 class="mb-4 mt-4"><?php echo t("Background Color Hex")?></h6>
<div class="form-label-group">    
   <?php echo $form->textField($model,'color_hex',array(
     'class'=>"form-control form-control-text colorpicker",
     'placeholder'=>$form->label($model,'color_hex'),
     'readonly'=>true
   )); ?>      
   <?php echo $form->error($model,'color_hex'); ?>
</div>

<h6 class="mb-4 mt-4"><?php echo t("Font Color Hex")?></h6>
<div class="form-label-group">    
   <?php echo $form->textField($model,'font_color_hex',array(
     'class'=>"form-control form-control-text colorpicker",
     'placeholder'=>$form->label($model,'font_color_hex'),
     'readonly'=>true
   )); ?>      
   <?php echo $form->error($model,'font_color_hex'); ?>
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


<!--TRANSLATION-->
<?php if($multi_language && is_array($language) && count($language)>=1 ):?>
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
<!--END TRANSLATION-->	


<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>