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

<div class="custom-control custom-switch custom-switch-md mb-3">  
    <?php echo $form->checkBox($model,"multicurrency_enabled",array(
        'class'=>"custom-control-input checkbox_child",     
        'value'=>1,
        'id'=>"multicurrency_enabled",
        'checked'=>$model->multicurrency_enabled==1?true:false
    )); ?>   
    <label class="custom-control-label" for="multicurrency_enabled"><?php echo t("Enabled Multi Currency")?></label>
</div>  


<h6 class="mb-3"><?php echo t("Exchange rate provider")?></h6>

<?php 
echo CHtml::activeRadioButtonList($model,'multicurrency_provider',array(
  'manual'=>t("Manual - enter the rates manually"),
  'free_currency'=>t("Automatically - take rates from provider"),
)); 
?>
<!-- 
<table class="table table-striped mt-2">
<tr>
    <th><?php echo t("Provider")?></th>
    <th><?php echo t("Description")?></th>
</tr>
<tr>
    <td><?php echo t("Manual")?></td>
    <td><?php echo t("using this options you will have to update the exchange rate manually")?></td>
</tr>
<tr>
    <td><?php echo t("Free currency API")?></td>
    <td><?php echo t("this options will auto update exchange rate once cron successfully get data from provider")?></td>
</tr>
</table> -->

<div class="p-2"></div>

<h6><?php echo t("Free Currency API")?></h6>
<div class="form-label-group">    
    <?php echo $form->textField($model,'multicurrency_apikey',array(
        'class'=>"form-control form-control-text",
        'placeholder'=>$form->label($model,'multicurrency_apikey'),
    )); ?>   
    <?php echo $form->labelEx($model,'multicurrency_apikey'); ?>
    <?php echo $form->error($model,'multicurrency_apikey'); ?>
</div>
<p class="text-muted"><?php echo t("get your api key")?> <a target="_blank" href="https://freecurrencyapi.com">https://freecurrencyapi.com</a></p>

<hr />
<div class="p-1"></div>

<h5><?php echo t("Cron link")?></h5>
<p class="text-muted mb-1""><?php echo t("below are the cron jobs that needed to run in your cpanel as http cron")?>.</p>

<?php foreach ($cron_link as $items):?>
<table class="table">
<tbody>
    <tr>
        <td>Link</td>
        <td><?php echo $items['link']?></td>
    </tr>
    <tr>
        <td><?php echo t("Note")?></td>
        <td><?php echo $items['label']?></td>
    </tr>
</tbody>
</table>
<?php endforeach;?>

<p>
    <a href="https://www.youtube.com/watch?v=7lrNECQ5bvM" target="_blank">
        <?php echo t("Click here how to run cron jobs")?>
    </a>
</p>

</div> <!--body-->
</div> <!--card-->


<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>