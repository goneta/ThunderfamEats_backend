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
    <?php echo $form->textField($model,'transaction_ref_id',array(
        'class'=>"form-control form-control-text",
        'placeholder'=>$form->label($model,'transaction_ref_id'),
        'readonly'=>true
    )); ?>   
    <?php //echo $form->labelEx($model,'transaction_ref_id'); ?>
    <label for="AR_bank_deposit_transaction_ref_id">
        <?php 
        echo $model->deposit_type=="order"?t("Order#"):t("Subscriptin ID");
        ?>
    </label>
    <?php echo $form->error($model,'transaction_ref_id'); ?>
    </div>    

    <div class="form-label-group">    
    <?php echo $form->textField($model,'account_name',array(
        'class'=>"form-control form-control-text",
        'placeholder'=>$form->label($model,'account_name')     
    )); ?>   
    <?php    
        echo $form->labelEx($model,'account_name'); ?>
    <?php echo $form->error($model,'account_name'); ?>
    </div>    

    <?php if($multicurrency_enabled && !$model->isNewRecord):?>
        <?php if($model->base_currency_code!=$model->admin_base_currency):?>
            <?php 
            $merchant_price_format = isset($price_list_format[$model->base_currency_code])?$price_list_format[$model->base_currency_code]:Price_Formatter::$number_format;
            $admin_price_format = isset($price_list_format[$model->admin_base_currency])?$price_list_format[$model->admin_base_currency]:Price_Formatter::$number_format;
            ?>
            <h5><?php echo t("Exchange rate info")?></h5>
            <table class="table table-bordered">
            <thead>
                <tr>
                <th scope="col"><?php echo t("Amount")?> : <span class="text-dark"><?php echo Price_Formatter::formatNumber2($model->amount,$merchant_price_format) ?></span></th>
                <th scope="col"><?php echo t("Base currency")?> : <span class="text-dark"><?php echo $model->admin_base_currency ?></span></th>
                <th scope="col"><?php echo t("Exchange rate")?> : <span class="text-dark"><?php echo $model->exchange_rate_merchant_to_admin ?></span></th>
                <th scope="col"><?php echo t("Total amount")?> : <span class="text-dark"><?php echo Price_Formatter::formatNumber2( ($model->amount*$model->exchange_rate_merchant_to_admin) ,$admin_price_format) ?></span></th>
                </tr>
            </thead>
            </table>
        <?php endif;?>        
    <?php endif;?>

    <div class="form-label-group">    
    <?php echo $form->textField($model,'amount',array(
        'class'=>"form-control form-control-text",
        'placeholder'=>$form->label($model,'amount')     
    )); ?>   
    <?php    
        echo $form->labelEx($model,'amount'); ?>
    <?php echo $form->error($model,'amount'); ?>
    </div>    

    <div class="form-label-group">    
    <?php echo $form->textField($model,'reference_number',array(
        'class'=>"form-control form-control-text",
        'placeholder'=>$form->label($model,'reference_number')     
    )); ?>   
    <?php    
        echo $form->labelEx($model,'reference_number'); ?>
    <?php echo $form->error($model,'reference_number'); ?>
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



<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Update")
)); ?>

<?php $this->endWidget(); ?>