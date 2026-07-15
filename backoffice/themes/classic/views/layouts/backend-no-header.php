<!DOCTYPE html>
<html lang="<?php echo Yii::app()->language;?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="robots" content="noindex, nofollow" />
<meta name="<?php echo Yii::app()->request->csrfTokenName?>" content="<?php echo Yii::app()->request->csrfToken?>" />    
<link rel="apple-touch-icon" sizes="76x76" href="<?php echo Yii::app()->theme->baseUrl?>/assets/icons/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="<?php echo Yii::app()->theme->baseUrl?>/assets/icons/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="<?php echo Yii::app()->theme->baseUrl?>/assets/icons/favicon-16x16.png">
<link rel="manifest" href="<?php echo Yii::app()->theme->baseUrl?>/assets/icons/site.webmanifest">
<link rel="mask-icon" href="<?php echo Yii::app()->theme->baseUrl?>/assets/icons/safari-pinned-tab.svg" color="#5bbad5">
<title><?php echo CHtml::encode($this->pageTitle); ?></title>
</head>
<body>

<div class="container-fluid m-0 p-0">
 
 <div class="sidebar-panel nice-scroll">
	 <div class="sidebar-wrap">
	 
	  <div class="sidebar-logo">	   
	   <?php 
       $this->widget('application.components.WidgetLogo',array(
         'class_name'=>'top-logo',
		 'link'=>Yii::app()->createUrl("/admin/dashboard")
       ));
       ?>
	  </div>
	  
	  <div class="sidebar-profile">
	     <div class="row m-0">
	       <div class="col-md-3 m-0 p-0" >
	         <img class="rounded-circle" src="<?php echo AdminTools::getProfilePhoto()?>" />
	       </div>
	       <div class="col-md-9 m-0 p-0" >
	         <h6><?php echo AdminTools::displayAdminName();?></h6>
	         <p class="dim">	         
	         <?php 
	         if(!empty(Yii::app()->user->contact_number)){
	         	echo t("T.")." ".Yii::app()->user->contact_number;
	         }
	         if(!empty(Yii::app()->user->email_address)){
	         	echo '<br/>'.t("E.")." ".Yii::app()->user->email_address;
	         }	        	        
	         ?>
	         </p>
	       </div>
	     </div> <!--row-->
	  </div> 
	  <!--sidebar-profile-->
	  
	  <div class="siderbar-menu">	  
	   <?php 
	   $this->widget('application.components.WidgetMenu',array(
	     'menu_type'=>"admin"
	   ));
	   ?>
	  </div> <!--siderbar-menu-->
	  
	  <div class="pl-4 mt-3">		
        <p class="m-0">&copy; 
			<?php 
			$backend_version = Yii::app()->params['settings'];
			$backend_version = isset($backend_version['backend_version'])?$backend_version['backend_version']:"1.0.0";
			echo t("Version {{version}}",['{{version}}'=>$backend_version]);
			?>			
	  </div>

	 </div> <!--sidebar-wrap-->
 </div> <!--sidebar-panel-->
 
 
 
 <div class="main-container" style="padding-top: 0px;">
    <?php echo $content;?>
 </div><!-- main-container-->
 
 <div class="ssm-overlay ssm-toggle-nav"></div>
 <div id="vue-search-menu"></div>
 
</div><!--container-->

</body>
</html> 