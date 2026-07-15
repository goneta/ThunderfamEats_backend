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

  <div class="alert alert-danger p-3" role="alert">
    <i class="zmdi zmdi-info-outline"></i> <b class="mr-2"><?php echo t("Note:")?></b><?php echo t("this will remove your saved data, please make sure before continuing.")?>
  </div>
        
    <?php $count=0?>    
    <?php foreach ($data as $index=> $item):?>
      <h5><?php echo t($index)?></h5>
      <div class="row align-items-center mb-3">      
      <?php foreach ($item as $key=> $items):?>            
          <div class="col-3">    
          
            <div class="d-flex flex-row">
                <div class="p-2" style="min-width: 40px;" >
                  <div class="badge btn-green p-2"><b><?php echo $items['count']?></b></div>
                </div>           
                
                <div class="p-1">
                <div class="custom-control custom-switch custom-switch-md mr-4">                    
                  <?php echo $form->checkBox($model,"table_list[$count]",array(
                      'class'=>"custom-control-input checkbox_child",     
                      'value'=>$items['name'],
                      'id'=>"table_list".$count,    
                      'checked'=>in_array($items['name'],(array)$model->table_list)?true:false
                  )); ?>   
                  <label class="custom-control-label" for="table_list<?php echo $count?>">
                  <?php echo $items['name']?>
                  </label>
              </div>            
              </div> 
          
            </div>         
            <!-- flex -->

          </div>
          <!-- col -->
          <?php $count++;?>
      <?php endforeach;?>
       </div>  
    <?php endforeach;?>  

  <h5><?php echo t("Enter your password to continue")?></h5>
  <div class="form-label-group">    
    <?php echo $form->passwordField($model,'password',array(
      'class'=>"form-control form-control-text",
      'placeholder'=>$form->label($model,'password'),
      'autocomplete'=>"new-password",
    )); ?>   
    <?php echo $form->labelEx($model,'password',array('label'=>t("Password"))); ?>
    <?php echo $form->error($model,'password'); ?>
  </div>

  </div> <!--body-->
</div> <!--card-->

  
<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Clear tables")
)); ?>


<?php $this->endWidget(); ?>  