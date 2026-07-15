<div class="container mt-3 mb-3">


<div class="pagenotfound-section mb-4 mt-5">
  
</div>

<div class="text-center mb-5">
 <div class="card">
 <div class="card-body">
      <h5 class="card-title"><?php echo t("An error has occured")?></h5>

      <p class="card-text">
        <?php echo isset($error['message'])?$error['message']:t("Undefined error")?>
      </p> 

      <?php $back_url = isset($back)?$back:'';?>
      <?php if(!empty($back_url)):?>
        <a href="<?php echo $back_url?>"> <i class="zmdi zmdi-arrow-left"></i>
          <?php echo isset($back_text)? $back_text : t("Go Back")?>
        </a>
      <?php endif;?>
   </div>
  </div>
</div>
 
</div> <!--container-->