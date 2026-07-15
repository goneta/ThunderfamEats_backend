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
    <?php echo $form->checkBox($model,"multicurrency_enabled_hide_payment",array(
        'class'=>"custom-control-input checkbox_child",     
        'value'=>1,
        'id'=>"multicurrency_enabled",
        'checked'=>$model->multicurrency_enabled_hide_payment==1?true:false
    )); ?>   
    <label class="custom-control-label" for="multicurrency_enabled"><?php echo t("Hide payment gateway for specific currencies")?></label>
</div>  

<?php if(is_array($currency_list) && count($currency_list)>=1):?>
<table class="table">    
    <?php foreach ($currency_list as $currency_code => $items):?>
    <tr>
        <td width="30%">
            <?php echo t("Gateways hidden for user that pay in {currency_description}",[
                '{currency_description}'=>$items
            ])?>
        </td>
        <td>
        <div class="form-label-group">    
            <?php echo $form->dropDownList($model,"multicurrency_currency_list[".$currency_code."]", (array)$payment_list ,array(
                'class'=>"form-control custom-select form-control-select select_two",
                'placeholder'=>$form->label($model,'multicurrency_currency_list'),
                'multiple'=>true,
            )); ?>         
            <?php echo $form->error($model,'multicurrency_currency_list'); ?>
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