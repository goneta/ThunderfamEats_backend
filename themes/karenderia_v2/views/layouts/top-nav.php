<!--TOP SECTION-->
<div class="container-fluid">
 <div id="top-navigation" class="row" >

    <div class=" col-lg-auto col-md-6 col d-flex justify-content-start align-items-center">          
       <?php 
       $this->widget('application.components.WidgetSiteLogo',array(
         'class_name'=>'top-logo'
       ));
       ?>
    </div> <!--col-->
        
    <div id="vue-widget-nav" class=" col d-none d-lg-block">    
      <div class="d-flex justify-content-start align-items-center">
      <?php           
      if(!empty($widget_col1)){         
         $setttings = Yii::app()->params['settings'];
         $home_search_mode = isset($setttings['home_search_mode'])?$setttings['home_search_mode']:'address';		
		   $home_search_mode = !empty($home_search_mode)?$home_search_mode:'address';
         if($home_search_mode=="location"){
            $this->renderPartial("//components/widget-nav-locations");         
         } else $this->renderPartial("//components/$widget_col1");         
      }
      ?>   
      </div>     
    </div> <!--col-->

    <?php if(!empty($widget_col1)):?>
      <?php if($home_search_mode=="location"):?>
         <?php $this->renderPartial("//components/location_current_address")?>     
      <?php else :?>
         <script type="text/x-template" id="xtemplate_address_form">
            <?php $this->renderPartial("//account/checkout-address")?>
         </script>               
      <?php endif;?>             
    <?php endif;?>

    <div class=" col-lg-auto col-md-6 col d-flex justify-content-end align-items-center">
     <!-- ThunderfamEats redesign: light/dark theme toggle -->
     <button type="button" id="tf-theme-toggle" class="tf-theme-toggle" aria-label="<?php echo t("Toggle dark mode")?>" title="<?php echo t("Toggle dark mode")?>">
       <i class="zmdi zmdi-brightness-3 tf-icon-moon" aria-hidden="true"></i>
       <i class="zmdi zmdi-brightness-5 tf-icon-sun" aria-hidden="true"></i>
     </button>
     <?php
     if(!empty($widget_col2)){
    	 $this->renderPartial("//components/$widget_col2");
     }
     ?>
     <?php $this->widget('application.components.WidgetUserNav');?>
    </div> <!--col-->

 </div><!-- row-->

 <!-- mobile view --> 
 <?php 
 $action_id = Yii::app()->controller->action->id;
 if($action_id=="restaurants" || $action_id=="menu"){
    if($home_search_mode=="location"){       
    } else $this->renderPartial("//components/widget-subnav");    
 }
 ?>
 <!-- mobile view -->

</div> <!--container-->

<?php 
if($action_id=="restaurants" || $action_id=="menu"){
   if($home_search_mode=="location"){
      $this->renderPartial("//components/widget-subnav-locations");    
   }
}
?>
<!--END TOP SECTION-->