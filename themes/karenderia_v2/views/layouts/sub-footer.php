<div class="sub-footer">
  <div class="container">

    <div class="row">    

     <div class="col-xl-4 col-lg-4 col-md-6 col-6 d-flex justify-content-start align-items-center">       
       <?php 
       $this->widget('application.components.WidgetSiteLogo',array(
         'class_name'=>'footer-logo'
       ));
       ?>
     </div> <!--col-->
     
     <?php if(Yii::app()->params['settings']['enabled_social_links']==1):?>
     <div class="col-xl-4 col-lg-4 col-md-6 col-6 d-flex 
     justify-content-end justify-content-lg-center align-items-center">
         <div class="d-flex align-items-center social-list">

           <?php if(isset(Yii::app()->params['settings']['facebook_page'])):?>
            <div class="">
              <a href="<?php echo Yii::app()->params['settings']['facebook_page'];?>" target="_blank" class="facebook"><i class="zmdi zmdi-facebook"></i></a>
            </div>
            <?php endif?>

            <?php if(isset(Yii::app()->params['settings']['instagram_page'])):?>
            <div class="ml-2 ml-md-3 ml-lg-4">
              <a href="<?php echo Yii::app()->params['settings']['instagram_page'];?>" target="_blank" class="instagram"><i class="zmdi zmdi-instagram"></i></a>
            </div>
            <?php endif?>

            <?php if(isset(Yii::app()->params['settings']['linkedin_page'])):?>
            <div class="ml-2 ml-md-3 ml-lg-4">
              <a href="<?php echo Yii::app()->params['settings']['linkedin_page'];?>" target="_blank" class="linkedin"><i class="zmdi zmdi-linkedin"></i></a>
            </div>
            <?php endif?>

            <?php if(isset(Yii::app()->params['settings']['twitter_page'])):?>
            <div class="ml-2 ml-md-3 ml-lg-4">
               <a href="<?php echo Yii::app()->params['settings']['twitter_page'];?>" target="_blank" class="twitter"><i class="zmdi zmdi-twitter"></i></a>
            </div>
            <?php endif?>

            <?php if(isset(Yii::app()->params['settings']['google_page'])):?>
            <div class="ml-2 ml-md-3 ml-lg-4">
               <a href="<?php echo Yii::app()->params['settings']['google_page'];?>" target="_blank" class="youtube"><i class="zmdi zmdi-youtube-play"></i></a>
            </div>
            <?php endif?>
         </div>
     </div> <!--col-->
     <?php endif?>
     
     <div id="vue-esubscription" v-cloack class="col-xl-4 col-lg-4 col-md-6 col-12 text-right mt-3 mt-lg-0">
         <div class="position-relative esubscription">
          <div class="field-wrap">
            <el-input v-model="email_address" placeholder="<?php echo t("Email")?>" size="large"  maxlength="50" ></el-input>
          </div>
          <div class="butoon-wrap">
            <el-button type="success" size="large" @click="submit" :loading="loading">
              <?php echo t("Subscribe")?>
            </el-button>
          </div>
         </div>
     </div> <!--col-->        
    </div> <!--row-->
    
   <?php $this->widget('application.components.WidgetFooterMenu');?>
  
  </div> <!--container-->
</div> <!--sub-footer-->