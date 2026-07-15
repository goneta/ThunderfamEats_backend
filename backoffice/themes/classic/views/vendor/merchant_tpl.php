<nav class="navbar navbar-light justify-content-between">
<?php

$this->widget('zii.widgets.CBreadcrumbs', 
array(
'links'=>isset($nav)?$nav:array(),
'homeLink'=>false,
'separator'=>'<span class="separator">
<i class="zmdi zmdi-chevron-right"></i><i class="zmdi zmdi-chevron-right"></i></span>'
));
?>
</nav>

<div class="card">

  <div class="card-body">
    
  <div class="row">
    <div class="col-md-3">

    <?php if(isset($avatar)):?>
    <div class="preview-image mb-2">
     <div class="col-lg-7">
      <img src="<?php echo $avatar?>" class="img-fluid mb-2 rounded-circle img-120">
     </div>     
    </div>   
    <?php endif;?>
        
    <div class="attributes-menu-wrap">
	<?php $this->widget('application.components.WidgetMerchantAttMenu',array(
	 'id'=>$model->merchant_id,	 
	 //'ctr'=>isset($ctr)?$ctr:'vendor',
	 'merchant_type'=>isset($model->merchant_type)?$model->merchant_type:''
	));?>
	</div>
    
    </div> <!--col-->
    <div class="col-md-9">
    
     <?php echo $this->renderPartial($template_name, $params); ?>  
    
    </div>
  </div> <!--row-->  
  
  </div> <!--card-body-->

</div> <!--card-->