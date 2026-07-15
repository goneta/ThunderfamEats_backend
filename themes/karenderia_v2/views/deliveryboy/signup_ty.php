

<div class="container">
 
<div class="login-container m-auto pt-5 pb-5">

  <div class="text-center">
     <h5 class="m-1"><?php echo t("Thank you for signing up!")?></h5>
     <p class="m-0"><?php echo t("Your registration is now complete.");?></p>  
     
     <div class="mt-4"></div>
     <h6><?php echo t("Download our rider app")?>!</h6>
     <p>
        <?php echo t("you can easily download the app by visiting the Google Play Store or the App Store. Simply search for '{rider_app_name}' and click 'download' to get started. With our app.",[
            '{rider_app_name}'=>$rider_app_name
        ])?>
     </p>

     <div class="d-flex justify-content-center">
      <?php if(!empty($android_download_url)):?>
         <a href="<?php echo $android_download_url;?>" target="_blank">
            <img style="max-width:8em;" class="mr-1" src="<?php echo Yii::app()->theme->baseUrl."/assets/images/google-play@2x.png"?>" />
         </a>
      <?php endif;?>
      <?php if(!empty($ios_download_url)):?>
         <a href="<?php echo $ios_download_url;?>" target="_blank">
      <img style="max-width:8em;"  href="<?php echo $ios_download_url;?>" target="_blank"  src="<?php echo Yii::app()->theme->baseUrl."/assets/images/app-store@2x.png"?>" />         
         </a>
      <?php endif;?>
     </div>

     <a class="btn btn-link" href="<?php echo Yii::app()->createUrl("/deliveryboy/signup")?>">
        <?php echo t("Register again")?>
     </a>     
  </div>

  </div> <!--login container-->

</div> <!--containter-->  