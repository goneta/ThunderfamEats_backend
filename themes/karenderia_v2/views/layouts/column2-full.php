<?php $this->beginContent('/layouts/main-layout'); ?>

<nav id="sidebar" class="border d-none d-lg-block">

<div class="mb-5">
<?php 
$this->widget('application.components.WidgetSiteLogo',array(
 'class_name'=>'top-logo'
));
?>
</div>

<div class="d-flex mb-4">
  <div class="mr-2 position-relative">
  
   <div class="skeleton-placeholder rounded-pill img-50"></div>
   <img class="lazy img-50 rounded-pill" data-src="<?php echo Yii::app()->user->avatar?>" />   
  
  </div>
  <div class="">
    <h6><?php echo Yii::app()->input->xssClean(Yii::app()->user->first_name)?></h6>
    <p class="text-grey">
    
    <?php if(!empty(Yii::app()->user->contact_number)):?>
    <?php echo Yii::app()->input->xssClean(Yii::app()->user->contact_number)?><br/>
    <?php endif;?>
    
    <?php if(!empty(Yii::app()->user->email_address)):?>
    <?php echo Yii::app()->input->xssClean(Yii::app()->user->email_address)?>
    <?php endif;?>
    
    </p>
  </div> <!--col-->
</div> <!--flex-->

<?php $this->widget('application.components.WidgetCustomerMenu',array());?>

</nav> <!--nav-->


<div id="main-container" style="padding-top: 0px;">

<!--MAIN CONTENT-->
<?php echo $content; ?>
<!--END MAIN CONTENT-->

</div> <!--main-container-->

<?php $this->endContent(); ?>