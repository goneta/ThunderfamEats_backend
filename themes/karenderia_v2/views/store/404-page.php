<div class="container mt-3 mb-3">


<div class="pagenotfound-section mb-4 mt-5">
  <img class="img-350 img-fluid m-auto d-block" src="<?php echo Yii::app()->theme->baseUrl."/assets/images/404@2x.png"?>" />
</div>

<div class="text-center mb-5">
  <h3><?php echo t("Page not found")?></h3>
  <p><?php echo t("uh-oh! Looks like the page you are trying to access, doesn't exist. Please start afresh")?>.</p>
  <a href="<?php echo Yii::app()->createUrl("/store/index")?>" class="btn btn-green w25">
  <?php echo t("Go home")?>
  </a>
</div>
 
</div> <!--container-->