<nav class="navbar navbar-light justify-content-between">
<?php
$this->widget('zii.widgets.CBreadcrumbs', 
array(
'links'=>array(
    t("All Invoice")=>array('invoice/list'),        
    $this->pageTitle,
),
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


<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'merchant_id',(array)$merchant_selected,array(
     'class'=>"form-control custom-select form-control-select select_two_ajax",
     'placeholder'=>$form->label($model,'merchant_id'),   
     'action'=>'searchMerchant'
   )); ?>         
   <?php echo $form->error($model,'merchant_id'); ?>
</div>

<div class="form-label-group">    
<?php echo $form->textField($model,'business_address',array(
    'class'=>"form-control form-control-text",
    'placeholder'=>$form->label($model,'business_address'),
)); ?>   
<?php    
    echo $form->labelEx($model,'business_address'); ?>
<?php echo $form->error($model,'business_address'); ?>
</div>

<div class="form-label-group">    
<?php echo $form->textField($model,'contact_email',array(
    'class'=>"form-control form-control-text",
    'placeholder'=>$form->label($model,'contact_email'),
)); ?>   
<?php    
    echo $form->labelEx($model,'contact_email'); ?>
<?php echo $form->error($model,'contact_email'); ?>
</div>

<div class="form-label-group">    
<?php echo $form->textField($model,'contact_phone',array(
    'class'=>"form-control form-control-text",
    'placeholder'=>$form->label($model,'contact_phone'),
)); ?>   
<?php    
    echo $form->labelEx($model,'contact_phone'); ?>
<?php echo $form->error($model,'contact_phone'); ?>
</div>

<h6 class="mb-1"><?php echo $form->label($model,'invoice_terms')?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'invoice_terms', (array)$invoice_terms ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'invoice_terms'),
   )); ?>         
   <?php echo $form->error($model,'invoice_terms'); ?>
</div>		

<?php if($multicurrency_enabled && !$model->isNewRecord):?>
<?php if($model->merchant_base_currency!=$model->admin_base_currency):?>
<h5><?php echo t("Exchange rate info")?></h5>
<?php 
$merchant_price_format = isset($price_list_format[$model->merchant_base_currency])?$price_list_format[$model->merchant_base_currency]:Price_Formatter::$number_format;
$admin_price_format = isset($price_list_format[$model->admin_base_currency])?$price_list_format[$model->admin_base_currency]:Price_Formatter::$number_format;
?>
<table class="table table-bordered">
<thead>
    <tr>
      <th scope="col"><?php echo t("Invoice amount")?> : <span class="text-dark"><?php echo Price_Formatter::formatNumber2($model->invoice_total,$merchant_price_format) ?></span></th>
      <th scope="col"><?php echo t("Base currency")?> : <span class="text-dark"><?php echo $model->admin_base_currency ?></span></th>
      <th scope="col"><?php echo t("Exchange rate")?> : <span class="text-dark"><?php echo $model->exchange_rate_merchant_to_admin ?></span></th>
      <th scope="col"><?php echo t("Total amount")?> : <span class="text-dark"><?php echo Price_Formatter::formatNumber2( ($model->invoice_total*$model->exchange_rate_merchant_to_admin) ,$admin_price_format) ?></span></th>
    </tr>
  </thead>
</table>
<?php endif;?>
<?php endif;?>

<div class="form-label-group">    
<?php echo $form->textField($model,'invoice_total',array(
    'class'=>"form-control form-control-text",
    'placeholder'=>$form->label($model,'invoice_total'),
)); ?>   
<?php    
    echo $form->labelEx($model,'invoice_total'); ?>
<?php echo $form->error($model,'invoice_total'); ?>
</div>

<div class="form-label-group">    
<?php echo $form->textField($model,'amount_paid',array(
    'class'=>"form-control form-control-text",
    'placeholder'=>$form->label($model,'amount_paid'),
)); ?>   
<?php    
    echo $form->labelEx($model,'amount_paid'); ?>
<?php echo $form->error($model,'amount_paid'); ?>
</div>

<div class="form-label-group">    
<?php echo $form->textField($model,'invoice_created',array(
    'class'=>"form-control form-control-text datepick_withtime",
    'placeholder'=>$form->label($model,'invoice_created'),
    'readonly'=>true
)); ?>   
<?php    
    echo $form->labelEx($model,'invoice_created'); ?>
<?php echo $form->error($model,'invoice_created'); ?>
</div>

<div class="form-label-group">    
<?php echo $form->textField($model,'due_date',array(
    'class'=>"form-control form-control-text datepick_withtime",
    'placeholder'=>$form->label($model,'due_date'),
    'readonly'=>true
)); ?>   
<?php    
    echo $form->labelEx($model,'due_date'); ?>
<?php echo $form->error($model,'due_date'); ?>
</div>

<div class="form-label-group">    
<?php echo $form->textField($model,'date_from',array(
    'class'=>"form-control form-control-text datepick_withtime",
    'placeholder'=>$form->label($model,'date_from'),
    'readonly'=>true
)); ?>   
<?php    
    echo $form->labelEx($model,'date_from'); ?>
<?php echo $form->error($model,'date_from'); ?>
</div>

<div class="form-label-group">    
<?php echo $form->textField($model,'date_to',array(
    'class'=>"form-control form-control-text datepick_withtime",
    'placeholder'=>$form->label($model,'date_to'),
    'readonly'=>true
)); ?>   
<?php    
    echo $form->labelEx($model,'date_to'); ?>
<?php echo $form->error($model,'date_to'); ?>
</div>

<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'payment_status', (array)$status ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'payment_status'),
   )); ?>         
   <?php echo $form->error($model,'payment_status'); ?>
</div>		


</div> <!--body-->
</div> <!--card-->

<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>
