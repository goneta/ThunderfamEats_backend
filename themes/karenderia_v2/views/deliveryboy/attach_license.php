

<div class="container">
 
<div class="login-container m-auto pt-5 pb-5">

  <div class="text-center">
     <h5 class="m-1"><?php echo t("We need your driving license information")?></h5>
     <p class="m-0"><?php echo t("Enter your license correctly");?></p>  
  </div>

  <div class="forms-center mt-4">
        
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
        <?php echo $form->textField($model,'license_number',array(
            'class'=>"form-control form-control-text",
            'placeholder'=>$form->label($model,'license_number')     
        )); ?>   
        <?php    
            echo $form->labelEx($model,'license_number'); ?>
        <?php echo $form->error($model,'license_number'); ?>
     </div>

     <div class="form-label-group">    
        <?php echo $form->textField($model,'license_expiration',array(
            'class'=>"form-control form-control-text",
            'placeholder'=>$form->label($model,'license_expiration')     
        )); ?>   
        <?php    
            echo $form->labelEx($model,'license_expiration'); ?>
        <?php echo $form->error($model,'license_expiration'); ?>
     </div>  

     <div class="form-label-group">    
        <?php echo $form->fileField($model,'license_front_photo',array(
            'class'=>"form-control form-control-text",
            'placeholder'=>$form->label($model,'license_front_photo')     
        )); ?>   
        <?php    
            echo $form->labelEx($model,'license_front_photo'); ?>
        <?php echo $form->error($model,'license_front_photo'); ?>
     </div>

     <div class="form-label-group">    
        <?php echo $form->fileField($model,'license_back_photo',array(
            'class'=>"form-control form-control-text",
            'placeholder'=>$form->label($model,'license_back_photo')     
        )); ?>   
        <?php    
            echo $form->labelEx($model,'license_back_photo'); ?>
        <?php echo $form->error($model,'license_back_photo'); ?>
     </div>

   
     <?php echo CHtml::submitButton('submit',array(
        'class'=>"btn btn-green w-100 mt-3",
        'value'=>t("Continue")
     )); ?>

    <?php $this->endWidget(); ?>

  </div>
    
</div> <!--login container-->

</div> <!--containter-->