<div class="<?php echo isset($class_name)?$class_name:''?>">
   <a href="<?php echo Yii::app()->getBaseUrl(true);?>">
     <?php if(strpos((string)(isset($class_name)?$class_name:''), 'top-logo') !== false):?>
      <?php /* ThunderfamEats wordmark logo (brand image on black; rounded so it reads as a chip on any nav) */ ?>
      <img class="tf-nav-logo-img" src="<?php echo Yii::app()->getBaseUrl().'/images/logo_thunderfameats.jpeg'?>" alt="ThunderfamEats" />
     <?php elseif(!empty($website_logo)):?>
      <img class="img-200" src="<?php echo $image_url;?>" />
     <?php else :?>
     <img class="img-200" src="<?php echo Yii::app()->theme->baseUrl?>/assets/images/logo@2x.png" />
     <?php endif?>
   </a>
</div>