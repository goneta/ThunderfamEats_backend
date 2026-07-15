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

<?php if(is_array($payment_list) && count($payment_list)):?>
    <?php foreach ($payment_list as $items): $id = $items['id']?>
            
    <div class="custom-control custom-switch custom-switch-md">  
        <?php echo $form->checkBox($model,"payondelivery_data[$id]",array(
            'class'=>"custom-control-input checkbox_child",     
            'id'=>"payondelivery_data[$id]",
            'value'=>$id,
            'checked'=>in_array($id,(array)$model->payondelivery_data)?true:false
        )); ?>   
        <label class="custom-control-label" for="payondelivery_data[<?php echo $id?>]">
        <?php echo $items['description']?>
        </label>
    </div>   

    <?php endforeach;?>    
<?php endif;?>

</div> <!--body-->
</div> <!--card-->


<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>