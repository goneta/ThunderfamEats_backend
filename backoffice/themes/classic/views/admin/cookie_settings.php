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

  <h5 class="mb-4"><?php echo t("Settings")?></h5>

    <div class="custom-control custom-switch custom-switch-md">  
    <?php echo $form->checkBox($model,"cookie_consent_enabled",array(
        'class'=>"custom-control-input checkbox_child",     
        'value'=>1,
        'id'=>"cookie_consent_enabled",
        'checked'=>$model->cookie_consent_enabled==1?true:false
    )); ?>   
    <label class="custom-control-label" for="cookie_consent_enabled">
    <?php echo t("Enabled cookie consent")?>
    </label>
    </div>  

    <div class="custom-control custom-switch custom-switch-md">  
    <?php echo $form->checkBox($model,"cookie_show_preferences",array(
        'class'=>"custom-control-input checkbox_child",     
        'value'=>1,
        'id'=>"cookie_show_preferences",
        'checked'=>$model->cookie_show_preferences==1?true:false
    )); ?>   
    <label class="custom-control-label" for="cookie_show_preferences">
    <?php echo t("Show Preferences Menu")?>
    </label>
    </div>    

    <h5 class="mb-4 mt-4"><?php echo t("Theme & Colors")?></h5>
        
    <h6 class="mb-1"><?php echo t("Theme mode")?></h6>
    <div class="form-label-group">    
    <?php echo $form->dropDownList($model,'cookie_theme_mode', (array)$theme_mode ,array(
        'class'=>"form-control custom-select form-control-select",     
        'placeholder'=>$form->label($model,'cookie_theme_mode'),
    )); ?>         
    <?php echo $form->error($model,'cookie_theme_mode'); ?>
    </div>		

    <h6 class="mb-4 mt-4"><?php echo t("Primary Color")?></h6>
    <div class="form-label-group">    
    <?php echo $form->textField($model,'cookie_theme_primary_color',array(
        'class'=>"form-control form-control-text colorpicker",
        'placeholder'=>$form->label($model,'cookie_theme_primary_color'),
        'readonly'=>false
    )); ?>      
    <?php echo $form->error($model,'cookie_theme_primary_color'); ?>
    </div>

    <h6 class="mb-4 mt-4"><?php echo t("Dark Color")?></h6>
    <div class="form-label-group">    
    <?php echo $form->textField($model,'cookie_theme_dark_color',array(
        'class'=>"form-control form-control-text colorpicker",
        'placeholder'=>$form->label($model,'cookie_theme_dark_color'),
        'readonly'=>false
    )); ?>      
    <?php echo $form->error($model,'cookie_theme_dark_color'); ?>
    </div>

    <h6 class="mb-4 mt-4"><?php echo t("Light Color")?></h6>
    <div class="form-label-group">    
    <?php echo $form->textField($model,'cookie_theme_light_color',array(
        'class'=>"form-control form-control-text colorpicker",
        'placeholder'=>$form->label($model,'cookie_theme_light_color'),
        'readonly'=>false
    )); ?>      
    <?php echo $form->error($model,'cookie_theme_light_color'); ?>
    </div>


    <h5 class="mb-4 mt-4"><?php echo t("Position & Width")?></h5>

    <h6 class="mb-1"><?php echo t("Display Position")?></h6>
    <div class="form-label-group">    
    <?php echo $form->dropDownList($model,'cookie_position', (array)$display_position ,array(
        'class'=>"form-control custom-select form-control-select",     
        'placeholder'=>$form->label($model,'cookie_position'),
    )); ?>         
    <?php echo $form->error($model,'cookie_position'); ?>
    </div>		

    <h5 class="mb-4 mt-4"><?php echo t("Cookie Expiration")?></h5>

    <div class="form-label-group">    
    <?php echo $form->numberField($model,'cookie_expiration',array(
        'class'=>"form-control form-control-text",
        'placeholder'=>$form->label($model,'cookie_expiration'),
    )); ?>   
    <?php echo $form->labelEx($model,'cookie_expiration'); ?>
    <?php echo $form->error($model,'cookie_expiration'); ?>
    </div>

    <h5 class="mb-4 mt-4"><?php echo t("Textual Changes")?></h5>

    <div class="form-label-group">    
    <?php echo $form->textField($model,'cookie_title',array(
        'class'=>"form-control form-control-text",
        'placeholder'=>$form->label($model,'cookie_title'),
    )); ?>   
    <?php echo $form->labelEx($model,'cookie_title'); ?>
    <?php echo $form->error($model,'cookie_title'); ?>
    </div>

    <!-- <div class="form-label-group">    
    <?php echo $form->textField($model,'cookie_link_label',array(
        'class'=>"form-control form-control-text",
        'placeholder'=>$form->label($model,'cookie_link_label'),
    )); ?>   
    <?php echo $form->labelEx($model,'cookie_link_label'); ?>
    <?php echo $form->error($model,'cookie_link_label'); ?>
    </div> -->

    <div class="form-label-group">    
    <?php echo $form->textField($model,'cookie_link_accept_button',array(
        'class'=>"form-control form-control-text",
        'placeholder'=>$form->label($model,'cookie_link_accept_button'),
    )); ?>   
    <?php echo $form->labelEx($model,'cookie_link_accept_button'); ?>
    <?php echo $form->error($model,'cookie_link_accept_button'); ?>
    </div>

    <div class="form-label-group">    
    <?php echo $form->textField($model,'cookie_link_reject_button',array(
        'class'=>"form-control form-control-text",
        'placeholder'=>$form->label($model,'cookie_link_reject_button'),
    )); ?>   
    <?php echo $form->labelEx($model,'cookie_link_reject_button'); ?>
    <?php echo $form->error($model,'cookie_link_reject_button'); ?>
    </div>

        
    <h6 class="mb-1"><?php echo t("Privacy Policy")?></h6>
    <div class="form-label-group">    
    <?php echo $form->dropDownList($model,'cookie_link_label', (array)$page_list ,array(
        'class'=>"form-control custom-select form-control-select",     
        'placeholder'=>$form->label($model,'cookie_link_label'),
    )); ?>         
    <?php echo $form->error($model,'cookie_link_label'); ?>
    </div>		

    <div class="form-label-group mt-2">    
    <?php echo $form->textArea($model,'cookie_message',array(
        'class'=>"form-control form-control-text summernotex",     
        'placeholder'=>t("Message or Description"),
        'style'=>"height:100px;"
    )); ?>      
    <?php echo $form->error($model,'cookie_message'); ?>
    </div>


  </div> <!--body-->
</div> <!--card-->

<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>