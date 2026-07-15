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


<div class="custom-control custom-switch custom-switch-md">  
    <?php echo $form->checkBox($model,"points_enabled",array(
        'class'=>"custom-control-input checkbox_child",     
        'value'=>1,
        'id'=>"points_enabled",
        'checked'=>$model->points_enabled==1?true:false
    )); ?>   
    <label class="custom-control-label" for="points_enabled"><?php echo t("Enabled Loyalty Points")?></label>
</div>  


<div class="pt-2">
   <h5 class="m-0 p-0"><?php echo t("Earn Points")?></h5>
   <p><?php echo t("Manage the way customer earn points.")?></p>

   <div class="row">
     <div class="col-6">

     <h6 class="mb-1"><?php echo t("Rule based")?></h6>
      <div class="form-label-group">    
      <?php echo $form->dropDownList($model,'points_earning_rule', (array)$rule_based ,array(
          'class'=>"form-control custom-select form-control-select",     
          'placeholder'=>$form->label($model,'points_earning_rule'),
      )); ?>         
      <?php echo $form->error($model,'points_earning_rule'); ?>
      </div>		

     </div>
     <div class="col-6">
     
     <h6 class="mb-1">&nbsp;</h6>    
          
     <div class="form-label-group">    
      <?php echo $form->textField($model,'points_earning_points',array(
          'class'=>"form-control form-control-text",
          'placeholder'=>$form->label($model,'points_earning_points'),
      )); ?>         
      <label for="AR_option_points_earning_points">
        <?php 
        if($model->points_earning_rule=='fixed_points'){
          echo t("Points earned per order",[
            '{amount}'=>Price_Formatter::formatNumber(1)
          ]);
        } else {
          echo t("Points earned per each {amount} spent",[
            '{amount}'=>Price_Formatter::formatNumber(1)
          ]);
        }
        ?>
      </label>
      <?php echo $form->error($model,'points_earning_points'); ?>
    </div>
   
   
     </div>
   </div>
   <!-- row -->

   <div class="row">
    <div class="col-6">    
    
    <div class="form-label-group">    
      <?php echo $form->textField($model,'points_minimum_purchase',array(
          'class'=>"form-control form-control-text",
          'placeholder'=>$form->label($model,'points_minimum_purchase'),
      )); ?>         
      <label for="AR_option_points_minimum_purchase">
        <?php echo t("Minimum spent ({currency_code}) to get points",[
          '{currency_code}'=>Price_Formatter::$number_format['currency_symbol']
        ])?>
      </label>
      <?php echo $form->error($model,'points_minimum_purchase'); ?>
    </div>

    </div>    
    <div class="col-6">    
    
    <div class="form-label-group">    
      <?php echo $form->textField($model,'points_maximum_purchase',array(
          'class'=>"form-control form-control-text",
          'placeholder'=>$form->label($model,'points_maximum_purchase'),
      )); ?>         
      <label for="AR_option_points_maximum_purchase">
        <?php echo t("Maximum spent ({currency_code}) to get points",[
          '{currency_code}'=>Price_Formatter::$number_format['currency_symbol']
        ])?>
      </label>
      <?php echo $form->error($model,'points_maximum_purchase'); ?>
    </div>

    </div>    
   </div>
   <!-- row -->

</div>
<!-- pt-2 -->


<h5 class="m-0 p-0"><?php echo t("Redeeming Points")?></h5>
<p><?php echo t("Set the value of points redeemed for a discount.")?></p>

<div class="row">
  <div class="col-6">
    

    <div class="custom-control custom-switch custom-switch-md">  
        <?php echo $form->checkBox($model,"points_use_thresholds",array(
            'class'=>"custom-control-input checkbox_child",     
            'value'=>1,
            'id'=>"points_use_thresholds",
            'checked'=>$model->points_use_thresholds==1?true:false
        )); ?>   
        <label class="custom-control-label" for="points_use_thresholds" style="line-height: inherit;">
          <?php echo t("Enabled thresholds")?>
          <p class="m-0 p-0 text-muted">
            <?php echo t("if enabled this will use redeem thresholds")?>
          </p>
        </label>        
    </div>  
      

  </div>
  <div class="col-6">

    <div class="form-label-group">    
      <?php echo $form->textField($model,'points_redeemed_points',array(
          'class'=>"form-control form-control-text",
          'placeholder'=>$form->label($model,'points_redeemed_points'),
      )); ?>   
      <?php //echo $form->labelEx($model,'points_redeemed_points'); ?>
      <label for="AR_option_points_redeemed_points">
        <?php echo t("Points the customer needs to get {amount}",[
          '{amount}'=>Price_Formatter::formatNumber(1)
        ])?>
      </label>
      <?php echo $form->error($model,'points_redeemed_points'); ?>
    </div>
   
    
  </div>
</div>
<!-- row -->


<div class="row">
  <div class="col-6">
    
    <div class="form-label-group">    
      <?php echo $form->textField($model,'points_minimum_redeemed',array(
          'class'=>"form-control form-control-text",
          'placeholder'=>$form->label($model,'points_minimum_redeemed'),
      )); ?>   
      <?php echo $form->labelEx($model,'points_minimum_redeemed'); ?>
      <?php echo $form->error($model,'points_minimum_redeemed'); ?>
    </div>

  </div>
  <div class="col-6">
    
  <div class="form-label-group">    
      <?php echo $form->textField($model,'points_maximum_redeemed',array(
          'class'=>"form-control form-control-text",
          'placeholder'=>$form->label($model,'points_maximum_redeemed'),
      )); ?>   
      <?php echo $form->labelEx($model,'points_maximum_redeemed'); ?>
      <?php echo $form->error($model,'points_maximum_redeemed'); ?>
    </div>
    
  </div>
</div>
<!-- row -->
 
<div class="row">
<div class="col-6">
<div class="form-label-group">    
      <?php echo $form->textField($model,'points_minimum_subtotal',array(
          'class'=>"form-control form-control-text",
          'placeholder'=>$form->label($model,'points_minimum_subtotal'),
      )); ?>   
      <?php echo $form->labelEx($model,'points_minimum_subtotal'); ?>
      <?php echo $form->error($model,'points_minimum_subtotal'); ?>
    </div>
  </div>
</div>

<div class="pt-2">
  
  <div class="row">
    <div class="col-6">

      <h6 class="mb-2"><?php echo t("Redemption policy")?></h6>      
      <div class="form-label-group">    
      <?php echo $form->dropDownList($model,'points_redemption_policy', (array)$redemption_policy_list ,array(
          'class'=>"form-control custom-select form-control-select",     
          'placeholder'=>$form->label($model,'points_redemption_policy'),
      )); ?>         
      <?php echo $form->error($model,'points_redemption_policy'); ?>
      </div>		

    </div>
    <div class="col-6">

      <h6 class="mb-2"><?php echo t("Cost covered by")?></h6>      
      <div class="form-label-group">    
      <?php echo $form->dropDownList($model,'points_cover_cost', (array)$redemption_covered_list ,array(
          'class'=>"form-control custom-select form-control-select",     
          'placeholder'=>$form->label($model,'points_cover_cost'),
      )); ?>         
      <?php echo $form->error($model,'points_cover_cost'); ?>
      </div>		

    </div>
  </div>
  <!-- row -->

  <table class="table">
    <thead>
      <tr>
        <th><?php echo t("Redemption Type")?></th>
        <th><?php echo t("Description")?></th>
      </tr>
    </thead>
    <tr>
      <td width="25%"><b><?php echo t("Universal Redemption")?></b></td>
      <td class="text-muted"><?php echo t("customers can use the loyalty points earned from one merchant to redeem rewards or discounts at any participating merchant.")?></td>
    </tr>
    <tr>
      <td width="25%"><b><?php echo t("Merchant-Specific Redemption")?></b></td>
      <td class="text-muted"><?php echo t("customers can only redeem the points they earned within the same merchant's ecosystem. The loyalty points earned from one merchant cannot be used or transferred to other merchants or businesses.")?></td>
    </tr>
  </table>
  
</div>
<!-- pt-2 -->


<div class="pt-2">
   <h5 class=""><?php echo t("Global Points")?></h5>   

   <div class="row">
     <div class="col-6">
      <div class="form-label-group">    
        <?php echo $form->textField($model,'points_registration',array(
            'class'=>"form-control form-control-text",
            'placeholder'=>$form->label($model,'points_registration'),
        )); ?>   
        <?php echo $form->labelEx($model,'points_registration'); ?>
        <?php echo $form->error($model,'points_registration'); ?>
        <p class="text-muted ml-1"><?php echo t("Points the customer gets for register an account")?>.</p>
      </div>
     </div>
     <div class="col-6">     
      <div class="form-label-group">    
          <?php echo $form->textField($model,'points_review',array(
              'class'=>"form-control form-control-text",
              'placeholder'=>$form->label($model,'points_review'),
          )); ?>   
          <?php echo $form->labelEx($model,'points_review'); ?>
          <?php echo $form->error($model,'points_review'); ?>
          <p class="text-muted ml-1"><?php echo t("Points writing additional reviews")?>.</p>
        </div>
     </div>
   </div>
   <!-- row -->

   <div class="row">
     <div class="col-6">
      <div class="form-label-group">    
        <?php echo $form->textField($model,'points_first_order',array(
            'class'=>"form-control form-control-text",
            'placeholder'=>$form->label($model,'points_first_order'),
        )); ?>   
        <?php echo $form->labelEx($model,'points_first_order'); ?>
        <?php echo $form->error($model,'points_first_order'); ?>
        <p class="text-muted ml-1"><?php echo t("Points placing first order")?>.</p>
      </div>
     </div>
     <div class="col-6">     
      <div class="form-label-group">    
          <?php echo $form->textField($model,'points_booking',array(
              'class'=>"form-control form-control-text",
              'placeholder'=>$form->label($model,'points_booking'),
          )); ?>   
          <?php echo $form->labelEx($model,'points_booking'); ?>
          <?php echo $form->error($model,'points_booking'); ?>
          <p class="text-muted ml-1"><?php echo t("Points the customer will get for booking table")?>.</p>
        </div>
     </div>
   </div>
   <!-- row -->   

</div>
<!-- pt-2 -->


<div class="pt-2">
   <h5 class=""><?php echo t("Expiration")?></h5>   
   
    <div class="form-label-group">    
      <?php echo $form->dropDownList($model,'points_expiry', (array)$points_expiry_options ,array(
          'class'=>"form-control custom-select form-control-select",     
          'placeholder'=>$form->label($model,'points_expiry'),
      )); ?>         
      <?php echo $form->error($model,'points_expiry'); ?>
    </div>		

</div>


<div class="pt-2">
   <h5 class=""><?php echo t("Bonus Template")?></h5>   
   
    <div class="form-label-group">    
      <?php echo $form->dropDownList($model,'points_bunos_template', (array)$template_list ,array(
          'class'=>"form-control custom-select form-control-select",     
          'placeholder'=>$form->label($model,'points_bunos_template'),
      )); ?>         
      <?php echo $form->error($model,'points_bunos_template'); ?>
    </div>		

</div>


</div> <!--body-->
</div> <!--card-->


<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>