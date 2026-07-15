<nav class="navbar navbar-light justify-content-between">
<?php
$this->widget('zii.widgets.CBreadcrumbs', 
array(
'links'=>isset($params['links'])?$params['links']:'',
'homeLink'=>false,
'separator'=>'<span class="separator">
<i class="zmdi zmdi-chevron-right"></i><i class="zmdi zmdi-chevron-right"></i></span>'
));
?>
</nav>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'frm-merchant',
	'enableAjaxValidation'=>false,
)); ?>

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


<div class="row">
  <div class="col-4">
      
  <h5><?php echo t("Digital Wallet")?></h5>
  <div class="custom-control custom-switch custom-switch-md mb-3">  
      <?php echo $form->checkBox($model,"digitalwallet_enabled",array(
          'class'=>"custom-control-input checkbox_child",     
          'value'=>1,
          'id'=>"digitalwallet_enabled",
          'checked'=>$model->digitalwallet_enabled==1?true:false
      )); ?>   
      <label class="custom-control-label" for="digitalwallet_enabled"><?php echo t("Enabled")?></label>
  </div>  

  </div>
  <div class="col-4">
    
      
    <h5><?php echo t("Top-Up")?></h5>
    <div class="custom-control custom-switch custom-switch-md mb-3">  
        <?php echo $form->checkBox($model,"digitalwallet_enabled_topup",array(
            'class'=>"custom-control-input checkbox_child",     
            'value'=>1,
            'id'=>"digitalwallet_enabled_topup",
            'checked'=>$model->digitalwallet_enabled_topup==1?true:false
        )); ?>   
        <label class="custom-control-label" for="digitalwallet_enabled_topup"><?php echo t("Enabled Top-Up")?></label>
    </div>  
 
    
  </div>

  <div class="col-4">
          
  <h5><?php echo t("Refund to Wallet")?></h5>
  <div class="custom-control custom-switch custom-switch-md">  
      <?php echo $form->checkBox($model,"digitalwallet_refund_to_wallet",array(
          'class'=>"custom-control-input checkbox_child",     
          'value'=>1,
          'id'=>"digitalwallet_refund_to_wallet",
          'checked'=>$model->digitalwallet_refund_to_wallet==1?true:false
      )); ?>   
      <label class="custom-control-label" for="digitalwallet_refund_to_wallet"><?php echo t("Enabled")?></label>      
  </div>    
    
  </div>

</div>
<!-- row -->



<div class="row">
  <div class="col-6">
        
    <div class="form-label-group">    
      <?php echo $form->textField($model,'digitalwallet_topup_minimum',array(
          'class'=>"form-control form-control-text",
          'placeholder'=>$form->label($model,'digitalwallet_topup_minimum'),
      )); ?>   
      <?php echo $form->labelEx($model,'digitalwallet_topup_minimum'); ?>
      <?php echo $form->error($model,'digitalwallet_topup_minimum'); ?>      
    </div>    

  </div>
  <div class="col-6">
    
  <div class="form-label-group">    
      <?php echo $form->textField($model,'digitalwallet_topup_maximum',array(
          'class'=>"form-control form-control-text",
          'placeholder'=>$form->label($model,'digitalwallet_topup_maximum'),
      )); ?>   
      <?php echo $form->labelEx($model,'digitalwallet_topup_maximum'); ?>
      <?php echo $form->error($model,'digitalwallet_topup_maximum'); ?>      
    </div>    
    
  </div>
</div>
<!-- row -->

<h5><?php echo t("Transactions")?></h5>

<div class="row">
  <div class="col-6">
        
    <div class="form-label-group">    
      <?php echo $form->textField($model,'digitalwallet_transaction_limit',array(
          'class'=>"form-control form-control-text",
          'placeholder'=>$form->label($model,'digitalwallet_transaction_limit'),
      )); ?>   
      <?php echo $form->labelEx($model,'digitalwallet_transaction_limit'); ?>
      <?php echo $form->error($model,'digitalwallet_transaction_limit'); ?>
      <p class="m-1"><?php echo t("Spending amount limit")?></p>
    </div>    

  </div>
  <div class="col-6">
    
  
    
  </div>
</div>
<!-- row -->


</div> <!--body-->
</div> <!--card-->


<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>