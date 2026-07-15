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
		'id' => 'form',
		'enableAjaxValidation' => false,
		'htmlOptions' => array('enctype' => 'multipart/form-data'),				
	)
);
?>

<div class="card">
  <div class="card-body">

   <p><?php echo t("Drag image to sort")?></p>

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
    
    
    <div class="row" id="sort-items">
        <?php foreach ($data as $items):?>
        <div class="col-2">           
           <?php echo CHtml::hiddenField('id[]', $items['id'])?>
           <div class="box mb-2">
              <img src="<?php echo $items['url_image']?>"  class="rounded float-left cursor-pointer" >
              <div class="text-center ">
                <?php echo $items['name'] ?>
              </div>           
           </div>
        </div>
        <?php endforeach?>
    </div>


<div class="p-3"></div>
    
<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

  </div> 
</div>   

<?php $this->endWidget(); ?>