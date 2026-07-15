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

<h5 class="mb-4"><?php echo $this->pageTitle?></h5>

<div class="custom-control custom-switch custom-switch-md mb-3">  
    <?php echo $form->checkBox($model,"multicurrency_enabled_checkout_currency",array(
        'class'=>"custom-control-input checkbox_child",     
        'value'=>1,
        'id'=>"multicurrency_enabled_checkout_currency",
        'checked'=>$model->multicurrency_enabled_checkout_currency==1?true:false
    )); ?>   
    <label class="custom-control-label" for="multicurrency_enabled_checkout_currency"><?php echo t("Force to a specific currency during payment")?></label>
</div>  


<?php if(is_array($payment_list) && count($payment_list)>=1):?>
<table class="table">    
    <?php foreach ($payment_list as $payment_code => $items):?>
    <tr>
        <td width="30%">
            <div class="mt-3">
              <b><?php echo $items;?></b>
            </div>
        </td>
        <td>
            <div class="form-label-group m-0">    
            <?php echo $form->dropDownList($model,"multicurrency_currency_list[".$payment_code."]", (array)$currency_list ,array(
                'class'=>"form-control custom-select form-control-select",     
                'placeholder'=>$form->label($model,'multicurrency_currency_list'),
            )); ?>                     
            </div>		
        </td>
    </tr>
    <?php endforeach;?>
</table>
<?php endif;?>



<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>