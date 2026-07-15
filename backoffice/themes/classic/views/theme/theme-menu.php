<nav class="navbar navbar-light justify-content-between">
<?php
$this->widget('zii.widgets.CBreadcrumbs',$links);
?>
</nav>

<DIV id="vue-theme-menu" class="card">
 <div class="card-body">
  
     <div class="position-relative">
     <components-menu-list
     ref="menu_list"    
     ajax_url="<?php echo $ajax_url?>"
     :label="{
	    title:'<?php echo CJavaScript::quote(t("Add menu items"))?>', 	 
		select_menu:'<?php echo CJavaScript::quote(t("Select a menu to edit or"))?>', 	 
		create_menu:'<?php echo CJavaScript::quote(t("create new menu"))?>', 	 
	  }"  
	 @set-currentmenu="setCurrentmenu" 
	 @create-newmenu="createNewmenu" 
     >
     </components-menu-list>    
     </div>
	
	<div class="row mt-4">
	    <div class="col-md-4">
	    
	     <components-menu-allpages
	     ref="all_pages"    
         ajax_url="<?php echo $ajax_url?>"
         :menu_id="current_menu"
         :label="{
		    title:'<?php echo CJavaScript::quote(t("Add menu items"))?>', 
		    pages:'<?php echo CJavaScript::quote(t("Pages"))?>', 
		    custom_links:'<?php echo CJavaScript::quote(t("Custom links"))?>', 
			add_to_menu:'<?php echo CJavaScript::quote(t("Add to menu"))?>', 		
		  }"  
		 @after-addpage="afterAddpage"
	     >
	     </components-menu-allpages>
	    
	    </div> <!--col-->
	    
	    <div class="col-md-8">
	    
	     <components-menu-structure
	     ref="menu_structure"    
         ajax_url="<?php echo $ajax_url?>"
         :current_menu="current_menu"
         :label="{
		    title:'<?php echo CJavaScript::quote(t("Menu structure"))?>', 
		    pages:'<?php echo CJavaScript::quote(t("Pages"))?>', 
		    delete_confirmation:'<?php echo CJavaScript::quote(t("Delete Confirmation"))?>', 
		    are_you_sure:'<?php echo CJavaScript::quote(t("Are you sure you want to permanently delete the selected item?"))?>', 
		    cancel:'<?php echo CJavaScript::quote(t("Cancel"))?>', 
		    delete:'<?php echo CJavaScript::quote(t("Delete"))?>', 
			drag:'<?php echo CJavaScript::quote(t("Drag the items into the order you prefer."))?>', 
			menu_name:'<?php echo CJavaScript::quote(t("Menu name"))?>', 			
			save_menu:'<?php echo CJavaScript::quote(t("Save menu"))?>', 		
			create_menu:'<?php echo CJavaScript::quote(t("Create menu"))?>', 												
		  }"  		  
		 @after-savemenu="afterSavemenu"  
		 @after-cancelmenu="afterCancelmenu"  
	     >
	     </components-menu-structure>
	    
	    </div> <!--col-->
	    
	</div>
	<!--row-->
  
 </div><!-- card body-->
</DIV> <!--theme-->