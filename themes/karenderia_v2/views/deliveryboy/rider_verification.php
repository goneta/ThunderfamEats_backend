

<div class="container">
 
<div class="login-container m-auto pt-5 pb-5">

  <div class="text-center">
     <h5 class="m-1"><?php echo t("Enter verification code we've sent on given number")?></h5>
     <p class="m-0"><?php echo $message;?></p>  
  </div>

  <div id="vue-rider-registration" class="forms-center mt-4">
        
    <?php
    $form = $this->beginWidget(
        'CActiveForm',
        array(
            'id' => 'upload-form',
            'enableAjaxValidation' => false,		
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
        <?php echo $form->textField($model,'code',array(
            'class'=>"form-control form-control-text",
            'placeholder'=>$form->label($model,'code')     
        )); ?>   
        <?php    
            echo $form->labelEx($model,'code'); ?>
        <?php echo $form->error($model,'code'); ?>
     </div>
     
     <component-rider-verifycode
     ref="verify_code"
     resend_counter="<?php echo CJavaScript::quote($resend_counter)?>"
     uuid="<?php echo trim($driver_uuid)?>"
     :label="{                
          resend_code: '<?php echo CJavaScript::quote(t("Resend Code in"))?>',  
          resend: '<?php echo CJavaScript::quote(t("Resend"))?>',  
      }"
     >
     </component-rider-verifycode>

     <?php echo CHtml::submitButton('submit',array(
        'class'=>"btn btn-green w-100 mt-3",
        'value'=>t("Submit")
     )); ?>

    <?php $this->endWidget(); ?>

  </div>
    
</div> <!--login container-->

</div> <!--containter-->