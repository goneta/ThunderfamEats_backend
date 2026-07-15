<nav class="navbar navbar-light justify-content-between">
  <a class="navbar-brand">
  <h5><?php echo CHtml::encode($this->pageTitle)?></h5>
  </a>
 
  <?php if(isset($link)):?>
  <?php if(!empty($link)):?> 
    <div class="d-flex flex-row justify-content-end align-items-center">
	  <div class="p-2">  
	  <a type="button" class="btn btn-black btn-circle" 
	  href="<?php echo $link?>">
	    <i class="zmdi zmdi-plus"></i>
	  </a>  
	  </div>
	  <div class="p-2"><h5 class="m-0"><?php echo t("Add new")?></h5></div>

    <?php if(!empty($sort_link)):?>
    <div class="p-2">  
	  <a type="button" class="btn btn-primary btn-circle" 
	  href="<?php echo $sort_link?>">
	    <i class="zmdi zmdi-sort-asc"></i>
	  </a>  
	  </div>
	  <div class="p-2"><h5 class="m-0"><?php echo t("Sort")?></h5></div>
    <?php endif;?>

    <?php if(isset($bulk_link)):?>  
    <?php if(!empty($bulk_link)):?>
    <div class="p-2">  
	  <a type="button" class="btn btn-green btn-circle" 
	  href="<?php echo $bulk_link;?>">
	    <i class="zmdi zmdi-file-plus text-white"></i>
	  </a>  
	  </div>
	  <div class="p-2"><h5 class="m-0"><?php echo t("Upload Bulk")?></h5></div>
    <?php endif?>
    <?php endif;?>

	</div> <!--flex-->     
  <?php endif;?>
 <?php endif;?>	 
</nav>  
    
<!--SEARCH -->
<?php echo CHtml::beginForm('','post',array(
  'id'=>"frm_search",
  'class'=>"form-inline justify-content-end frm_search mb-2",
  'onsubmit'=>"return false;"
)); 
?> 

<div class="input-group rounded">
  <input type="search" class="form-control rounded search w-25" placeholder="<?php echo t("Search")?>" required  />
  <button type="submit" class="submit input-group-text border-0 ml-2 normal">
    <i class="zmdi zmdi-search"></i>
  </button>
   <button type="button" class="input-group-text border-0 ml-2 btn-black normal search_close" >
    <i class="zmdi zmdi-close"></i>
    </button>
</div> <!--input-group-->

<?php echo CHtml::endForm(); ?>
<!--END SEARCH -->