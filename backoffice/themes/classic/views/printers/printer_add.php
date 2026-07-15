<nav class="navbar navbar-light justify-content-between">
<?php
$this->widget('zii.widgets.CBreadcrumbs', 
array(
'links'=>isset($links)?$links:array(),
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
		'id' => 'printer-form',
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


<h6 class="mb-1"><?php echo t("Select Printer Connectivity")?></h6>
<div class="form-label-group">    
<?php echo $form->dropDownList($model,'printer_model', (array)$printer_type_list ,array(
    'class'=>"form-control custom-select form-control-select printer_type_list",     
    'placeholder'=>$form->label($model,'printer_model'),
)); ?>         
<?php echo $form->error($model,'printer_model'); ?>
</div>	


<div class="form-label-group">    
   <?php echo $form->textField($model,'printer_name',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'printer_name')     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'printer_name'); ?>
   <?php echo $form->error($model,'printer_name'); ?>
</div>

<div class="element-feieyun d-none">


<div class="form-label-group">    
   <?php echo $form->textField($model,'printer_user',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'printer_user')     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'printer_user'); ?>
   <?php echo $form->error($model,'printer_user'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'printer_ukey',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'printer_ukey')     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'printer_ukey'); ?>
   <?php echo $form->error($model,'printer_ukey'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'printer_sn',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'printer_sn')     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'printer_sn'); ?>
   <?php echo $form->error($model,'printer_sn'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'printer_key',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'printer_key')     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'printer_key'); ?>
   <?php echo $form->error($model,'printer_key'); ?>
</div>

</div>
<!-- end feieyun -->

<div class="element-wifi d-none">

<div class="form-label-group">    
   <?php echo $form->textField($model,'service_id',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'service_id')     
   )); ?>   
   <label for="AR_printer_service_id" class="required">
    <?php echo t("Printer I.P address")?>
    <span class="required">*</span>
   </label>
   <?php echo $form->error($model,'service_id'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'characteristics',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'characteristics')     
   )); ?>   
   <label for="AR_printer_characteristics" class="required">
    <?php echo t("Printer Port")?>
    <span class="required">*</span>
   </label>
   <?php echo $form->error($model,'characteristics'); ?>
   <small id="emailHelp" class="form-text text-muted">
    <?php echo t("Default printer port: 9100")?>
   </small>
</div>

<div class="mb-1"><?php echo t("Paper Width")?></div>
<div class="form-label-group">    
<?php echo $form->dropDownList($model,'paper_width', (array)$printer_paperwidth_list ,array(
    'class'=>"form-control custom-select form-control-select paper_width",     
    'placeholder'=>$form->label($model,'paper_width'),
)); ?>         
<?php echo $form->error($model,'paper_width'); ?>
</div>	

<div class="mb-1"><?php echo t("Choose Printing Type")?></div>
<div class="form-label-group">    
<?php echo $form->dropDownList($model,'print_type', (array)$printing_type_list ,array(
    'class'=>"form-control custom-select form-control-select print_type",     
    'placeholder'=>$form->label($model,'print_type'),
)); ?>         
<?php echo $form->error($model,'print_type'); ?>
 <small id="emailHelp" class="form-text text-muted">
    <?php echo t("Choose an image if you are printing a receipt in a non-English language (e.g., Arabic, Korean, Japanese) on a thermal printer")?>
   </small>
</div>	

<div class="mb-1"><?php echo t("Select the Character Code")?></div>
<div class="form-label-group">    
<?php echo $form->dropDownList($model,'character_code', (array)$printing_character_list ,array(
    'class'=>"form-control custom-select form-control-select character_code",     
    'placeholder'=>$form->label($model,'character_code'),
)); ?>         
<?php echo $form->error($model,'character_code'); ?>
 <small id="emailHelp" class="form-text text-muted">
    <?php echo t("Select the Character Code only if the Printing Type is set to 'image'")?>
   </small>
</div>	

</div>
<!-- end wifi -->

<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"auto_print",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"auto_print",
     'checked'=>$model->auto_print==1?true:false
   )); ?>   
  <label class="custom-control-label" for="auto_print">
   <?php echo t("Auto print")?>
  </label>
</div>    

   </div>
</div>

<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>