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


<h6 class="mb-3"><?php echo t("Enabled Registration")?></h6>
<div class="custom-control custom-switch custom-switch-md">  
  <?php echo $form->checkBox($model,"driver_enabled_registration",array(
     'class'=>"custom-control-input",     
     'value'=>1,
     'id'=>"driver_enabled_registration",
     'checked'=>$model->driver_enabled_registration==1?true:false
   )); ?>   
  <label class="custom-control-label" for="driver_enabled_registration">
   <?php echo t("Enabled")?>
  </label>
</div>    

<h6 class="mb-3 mt-3"><?php echo t("Your rider registration url")?></h6>    
  <div class="form-label-group">    
   <?php echo $form->textField($model,'api_url',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>"", 
     'disabled'=>true,
     'value'=>CommonUtility::getHomebaseUrl()."/deliveryboy/signup"
   )); ?>   
   <label for="AR_option_api_url"><?php echo t("Registration URL")?></label>
</div>

<h5 class="mb-3"><?php echo t("Driver Registration")?></h5>

<h6 class="mb-1"><?php echo t("Employment Type")?></h6>
<div class="form-label-group">    
<?php echo $form->dropDownList($model,'driver_employment_type', (array)$employment_type ,array(
    'class'=>"form-control custom-select form-control-select",     
    'placeholder'=>$form->label($model,'driver_employment_type'),
)); ?>         
<?php echo $form->error($model,'driver_employment_type'); ?>
</div>	

<h6 class="mb-4"><?php echo t("Payment options")?></h6>

<h6 class="mb-1"><?php echo t("Salary Type")?></h6>
<div class="form-label-group">    
<?php echo $form->dropDownList($model,'driver_salary_type', (array)$salary_type ,array(
    'class'=>"form-control custom-select form-control-select",     
    'placeholder'=>$form->label($model,'driver_salary_type'),
)); ?>         
<?php echo $form->error($model,'driver_salary_type'); ?>
</div>		

<div class="row">
    <div class="col-md-12">
      <div class="form-label-group">    
        <?php echo $form->numberField($model,'driver_salary',array(
            'class'=>"form-control form-control-text",
            'placeholder'=>$form->label($model,'driver_salary')     
        )); ?>   
        <?php    
            echo $form->labelEx($model,'driver_salary'); ?>
        <?php echo $form->error($model,'driver_salary'); ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
      <div class="form-label-group">    
        <?php echo $form->numberField($model,'driver_fixed_amount',array(
            'class'=>"form-control form-control-text",
            'placeholder'=>$form->label($model,'driver_fixed_amount'),
            'step'=>"0.01"
        )); ?>   
        <?php    
            echo $form->labelEx($model,'driver_fixed_amount'); ?>
        <?php echo $form->error($model,'driver_fixed_amount'); ?>
        </div>
    </div>

    <div class="col-md-6">
        <div class="row no-gutters">
            <div class="col-md-7 mr-1">
                <div class="form-label-group">    
                <?php echo $form->numberField($model,'driver_commission',array(
                    'class'=>"form-control form-control-text",
                    'placeholder'=>$form->label($model,'driver_commission') ,
                    'step'=>"0.01"
                )); ?>   
                <?php    
                    echo $form->labelEx($model,'driver_commission'); ?>
                <?php echo $form->error($model,'driver_commission'); ?>
                </div>        
            </div>
            <!-- col -->
            <div class="col">
            
            <div class="form-label-group">    
            <?php echo $form->dropDownList($model,'driver_commission_type', (array)$commission_type ,array(
                'class'=>"form-control custom-select form-control-select",     
                'placeholder'=>$form->label($model,'driver_commission_type'),
            )); ?>         
            <?php echo $form->error($model,'driver_commission_type'); ?>
            </div>		

            </div>
            <!-- col -->
        </div>
        <!-- row -->
    </div>
</div>
<!-- row -->

<h6 class="mb-4"><?php echo t("Incentives")?></h6>
<div class="row">
  <div class="col">

   <div class="form-label-group">    
    <?php echo $form->numberField($model,'driver_incentives_amount',array(
        'class'=>"form-control form-control-text",
        'placeholder'=>$form->label($model,'driver_incentives_amount')     
    )); ?>   
    <?php    
        echo $form->labelEx($model,'driver_incentives_amount'); ?>
    <?php echo $form->error($model,'driver_incentives_amount'); ?>
    </div>        

  </div>
  <div class="col">

  <div class="form-label-group">    
    <?php echo $form->numberField($model,'driver_maximum_cash_amount_limit',array(
        'class'=>"form-control form-control-text",
        'placeholder'=>$form->label($model,'driver_maximum_cash_amount_limit')     
    )); ?>   
    <?php    
        echo $form->labelEx($model,'driver_maximum_cash_amount_limit'); ?>
    <?php echo $form->error($model,'driver_maximum_cash_amount_limit'); ?>
    </div>        

  </div>
</div>

<h6 class="mb-3 mt-2"><?php echo t("After registration")?></h6>
<div class="form-label-group">    
<?php echo $form->dropDownList($model,'driver_registration_process', (array)$registration_process,array(
    'class'=>"form-control custom-select form-control-select",     
    'placeholder'=>$form->label($model,'driver_registration_process'),
)); ?>         
<?php echo $form->error($model,'driver_registration_process'); ?>
</div>		

<!-- END PAYMENT -->

<h6 class="mb-3 mt-3"><?php echo t("Send verification code by")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'driver_sendcode_via', (array) array(
     'sms'=>t("SMS"),
     'email'=>t("Email")
   ),array(
     'class'=>"form-control custom-select form-control-select",
     'placeholder'=>$form->label($model,'driver_sendcode_via'),
   )); ?>         
   <?php echo $form->error($model,'driver_sendcode_via'); ?>
</div>

<h6 class="mb-2"><?php echo t("Resend code interval")?></h6>
<div class="form-label-group">    
   <?php echo $form->textField($model,'driver_sendcode_interval',array(
     'class'=>"form-control form-control-text",
     'placeholder'=>$form->label($model,'driver_sendcode_interval'),               
   )); ?>   
   <?php echo $form->labelEx($model,'driver_sendcode_interval'); ?>
   <?php echo $form->error($model,'driver_sendcode_interval'); ?>
</div>

<h6 class="mb-3"><?php echo t("Resend Code Template")?></h6>
<div class="form-label-group">    
   <?php echo $form->dropDownList($model,'driver_sendcode_tpl', (array)$template_list ,array(
     'class'=>"form-control custom-select form-control-select",     
     'placeholder'=>$form->label($model,'driver_sendcode_tpl'),
   )); ?>         
   <?php echo $form->error($model,'driver_sendcode_tpl'); ?>
</div>		

<div class="form-label-group mt-2">    
   <?php echo $form->textArea($model,'driver_signup_terms_condition',array(
     'class'=>"form-control form-control-text summernote",     
     'placeholder'=>t("Your terms and condition here...")
   )); ?>      
   <?php echo $form->error($model,'driver_signup_terms_condition'); ?>
</div>


</div> <!--body-->
</div> <!--card-->



<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>
