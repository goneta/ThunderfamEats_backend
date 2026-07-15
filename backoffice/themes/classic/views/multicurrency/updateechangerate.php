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
  <div class="card-body">

    <div class="alert <?php echo $class?$class:"alert-success";?>"><?php echo isset($msg)?$msg:'';?></div>

    <a href="<?php echo isset($return_url)?$return_url:'#'?>" class="btn btn-link"><?php echo t("Go back")?></a>

  </div>
</div>  