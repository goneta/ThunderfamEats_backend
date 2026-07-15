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

<h6 class="mb-1"><?php echo t("Merchant")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'merchant_id',(array)$model->merchant_id_selected,array(
     'class'=>"form-control custom-select form-control-select select_two_ajax",
     'placeholder'=>$form->label($model,'client_id'),   
     'action'=>'searchMerchant',
     'id'=>"merchant_id_booking"
   )); ?>         
   <?php echo $form->error($model,'client_id'); ?>
</div>

<h6 class="mb-1"><?php echo t("Customer")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'client_id',(array)$model->client_id_selected,array(
     'class'=>"form-control custom-select form-control-select select_two_ajax2",
     'placeholder'=>$form->label($model,'client_id'),   
     'action'=>'searchCustomer'
   )); ?>         
   <?php echo $form->error($model,'client_id'); ?>
</div>


<div class="form-label-group">    
   <?php echo $form->textField($model,'reservation_date',array(
     'class'=>"form-control form-control-text datepick",
     'placeholder'=>$form->label($model,'reservation_date'),
     'readonly'=>true
   )); ?>   
   <?php    
    echo $form->labelEx($model,'reservation_date'); ?>
   <?php echo $form->error($model,'reservation_date'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'reservation_time',array(
     'class'=>"form-control form-control-text timepick datetimepicker-input",     
     'placeholder'=>$form->label($model,'reservation_time'),     
     'readonly'=>true,
     'data-toggle'=>'datetimepicker',
     'readonly'=>true
   )); ?>   
   <?php    
    echo $form->labelEx($model,'reservation_time'); ?>
   <?php echo $form->error($model,'reservation_time'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->textField($model,'guest_number',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'guest_number')     
   )); ?>   
   <?php    
    echo $form->labelEx($model,'guest_number'); ?>
   <?php echo $form->error($model,'guest_number'); ?>
</div>

<h6 class="mb-1"><?php echo t("Table name")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'table_id', (array)$table_list ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'table_id'),
   )); ?>         
   <?php echo $form->error($model,'table_id'); ?>
</div>		

<h6 class="mb-4"><?php echo t("Special request")?></h6>
<div class="form-label-group mt-2">    
   <?php echo $form->textArea($model,'special_request',array(
     'class'=>"form-control form-control-text",          
   )); ?>      
   <?php echo $form->error($model,'special_request'); ?>
</div>

<h6 class="mb-1"><?php echo t("Status")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'status', (array)$status_list ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'status'),
   )); ?>         
   <?php echo $form->error($model,'status'); ?>
</div>		


</div> <!--body-->
</div> <!--card-->

  
<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>


<?php $this->endWidget(); ?>