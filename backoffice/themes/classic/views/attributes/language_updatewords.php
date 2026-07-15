<nav class="navbar navbar-light justify-content-between">
<?php
$this->widget('zii.widgets.CBreadcrumbs', 
array(
'links'=>$links,
'homeLink'=>false,
'separator'=>'<span class="separator">
<i class="zmdi zmdi-chevron-right"></i><i class="zmdi zmdi-chevron-right"></i></span>'
));
?>
</nav>


<div class="card">
  <div class="card-body p-4">

     <div class="row align-items-center">
        <div class="col">
        <a target="_blank" href="<?php echo Yii::app()->createUrl("/attributes/updatewords?category=front");?>" class="btn btn-green">
            <?php echo t("Update front language words")?>
        </a>
        </div>

        <div class="col">
        <a target="_blank" href="<?php echo Yii::app()->createUrl("/attributes/updatewords?category=backend");?>" class="btn btn-black">
            <?php echo t("Update backend words")?>
        </a>
        </div>

        <div class="col">
        <a target="_blank" href="<?php echo Yii::app()->createUrl("/attributes/updatewords?category=kitchen");?>" class="btn btn-green">
            <?php echo t("Update kitchen words")?>
        </a>
        </div>

        <div class="col">
        <a target="_blank" href="<?php echo Yii::app()->createUrl("/attributes/updatewords?category=tableside");?>" class="btn btn-black">
            <?php echo t("Update tableside words")?>
        </a>
        </div>

     </div>

  </div>
</div>