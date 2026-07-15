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

<h5 class="pb-2"><?php echo t("Menu Display Style (Mobile App)") ?></h5>

<?php
echo $form->radioButtonList($model,'menu_display_type',[
    'all'=>t("Show All Items"),
    'by_category'=>t("Show Categories First")
],
   array('separator'=>' ',
   'labelOptions'=>array(
     'style'=>'display:inline;margin-right:20px;',	 
   ),
));
?>

<hr/>
 
<h5 class="pb-2"><?php echo t("Menu options for single app addon only") ?></h5>

<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"merchant_addons_use_checkbox",array(
     'class'=>"custom-control-input checkbox_child",     
     'value'=>1,
     'id'=>"merchant_addons_use_checkbox",
     'checked'=>$model->merchant_addons_use_checkbox==1?true:false
   )); ?>   
  <label class="custom-control-label" for="merchant_addons_use_checkbox">
   <?php echo t("Addons use checkbox")?>
  </label>
</div>    

<div class="p-2"></div>

<h5><?php echo t("Menu options")?></h5>
<?php
echo $form->radioButtonList($model,'merchant_menu_type',[
    1=>t("Open in new window"),
    2=>t("Open in a pop up")
],
   array('separator'=>' ',
   'labelOptions'=>array(
     'style'=>'display:inline;margin-right:20px;',	 
   ),
));
?>

  </div> <!--body-->
</div> <!--card-->

<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>  