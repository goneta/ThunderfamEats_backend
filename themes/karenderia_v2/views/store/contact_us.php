<div class="container mt-5 mb-5">
 
<div class="row">
  <div class="col-md-8 border-right">
     
   <div class="text-center mb-4">
      <h3><?php echo $this->pageTitle?></h3>  
   </div>
   

   
<?php
$form = $this->beginWidget(
	'CActiveForm',
	array(
		'id' => 'upload-form',
		'enableAjaxValidation' => false,		
	)
);
?>

<?php if(!empty($contact_content)):?>
  <div>
    <?php echo Yii::app()->input->xssClean($contact_content)?>
  </div>
<?php endif;?>

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

<?php if(is_array($contact_field)):?>
<?php foreach ($contact_field as $item):?>    
<?php if($item=="message"):?>
  <div class="form-label-group">    
  <?php echo $form->textArea($model,$item,array(
     'class'=>"form-control form-control-text",     
     'placeholder'=>t("Your Message"),       
   )); ?>         
   </div>
<?php else :?>
    <div class="form-label-group">    
    <?php echo $form->textField($model,$item,array(
        'class'=>"form-control form-control-text",
        'placeholder'=>$form->label($model,$item),     
    )); ?>   
    <?php    
        echo $form->labelEx($model,$item); ?>
    <?php echo $form->error($model,$item); ?>
    </div>
<?php endif?>
<?php endforeach;?>
<?php endif?>

<div id="vue-captcha">
     <input type="hidden" name="recaptcha_response" id="recaptcha_response" :value="recaptcha_response"/>     
     <components-recapcha  
     sitekey="<?php echo $captcha_site_key;?>"
		 size="normal" 
		 theme="light"
		 :tabindex="0"
		 is_enabled="<?php echo CJavaScript::quote($enabled_captcha)?>"
     captcha_lang="<?php echo CJavaScript::quote($captcha_lang)?>"
		 @verify="recaptchaVerified"
		 @expire="recaptchaExpired"
		 @fail="recaptchaFailed"
		 ref="vueRecaptcha">
     </components-recapcha>		
</div>
<!-- vue-captcha -->

<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green w-100 mt-3",
'value'=>t("Submit")
)); ?>

<?php $this->endWidget(); ?>
   
  
  </div> <!--col-->
  <div class="col-md-4"> 
  
  </div> <!--col-->
</div>
<!--row--> 
</div> <!--container-->


<div class="container-fluid m-0 p-0 full-width">
 <?php $this->renderPartial("//store/join-us")?>
</div>
  

<div id="vue-carousel" class="container">

   <!--COMPONENTS FEATURED LOCATION-->
  <component-carousel
  title="<?php echo t("Try something new in")?>"
  featured_name="best_seller"
  :settings="{
      theme: '<?php echo CJavaScript::quote('rounded')?>',       
      items: '<?php echo CJavaScript::quote(5)?>', 
      lazyLoad: '<?php echo CJavaScript::quote(true)?>', 
      loop: '<?php echo CJavaScript::quote(true)?>', 
      margin: '<?php echo CJavaScript::quote(15)?>', 
      nav: '<?php echo CJavaScript::quote(false)?>', 
      dots: '<?php echo CJavaScript::quote(false)?>', 
      stagePadding: '<?php echo CJavaScript::quote(10)?>', 
      free_delivery: '<?php echo CJavaScript::quote( t("Free delivery") )?>', 
  }"
  :responsive='<?php echo json_encode($responsive);?>'
  />
  </component-carousel>  
  <!--COMPONENTS FEATURED LOCATION-->

</div> <!--vue-carousel-->  