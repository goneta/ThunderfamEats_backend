
  
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


<?php foreach ($services as $key => $items):?>
<div class="mb-3">
<h6 class="mb-1"><?php echo t($items)?></h6>
<?php echo $form->hiddenField($model,"transaction_type[$key]",array(
       'class'=>"form-control form-control-text",
       'value'=>$key
    )
);?> 

<div class="row">
  <div class="col-6">
      <div class="form-label-group">    
      <?php echo $form->textField($model,"services_fee[$key]",array(
        'class'=>"form-control form-control-text",     
      )); ?>   
      <?php echo $form->label($model,"services_fee[$key]",array('label' => t("Service fee"))); ?>
      <?php echo $form->error($model,"services_fee[$key]"); ?>   
      </div>
  </div>
  <div class="col-6">
  
   <div class="form-label-group">    
    <?php echo $form->dropDownList($model,"service_fee_type[$key]", $services_type ,array(
        'class'=>"form-control custom-select form-control-select",     
        'placeholder'=>$form->label($model,'service_fee_type'),
    )); ?>         
    <?php echo $form->error($model,'service_fee_type'); ?>
    </div>		

  </div>
</div>
<!-- row -->

<div class="row">
  <div class="col-6">

  <div class="form-label-group">    
    <?php echo $form->textField($model,"small_order_fees[$key]",array(
      'class'=>"form-control form-control-text",     
    )); ?>   
    <?php echo $form->label($model,"small_order_fees[$key]",array('label' => t("Small Order Fee"))); ?>
    <?php echo $form->error($model,"small_order_fees[$key]"); ?>   
  </div>

  </div>
  <!-- col -->

  <div class="col-6">

  <div class="form-label-group">    
    <?php echo $form->textField($model,"min_order_amount[$key]",array(
      'class'=>"form-control form-control-text",     
    )); ?>   
    <?php echo $form->label($model,"min_order_amount[$key]",array('label' => t("Minimum Order Amount"))); ?>
    <?php echo $form->error($model,"min_order_amount[$key]"); ?>   
  </div>
    
  </div>
  <!-- col -->
</div>
<!-- row -->


</div>  
<?php endforeach;?>

   
</div> <!--body-->
</div> <!--card-->



<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>