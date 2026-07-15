<div class="modal clone_merchant_modal fade"   role="dialog"  data-backdrop="static">
  <div class="modal-dialog modal-dialog-centered" role="document">
     <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLongTitle"><?php echo t("Duplicate Merchant")?></h5>
             <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	          <span aria-hidden="true">&times;</span>
	        </button>
        </div>
        <div class="modal-body">
        <p><?php echo t("Please select the sections that you want to copy")?></p>      
        
        <div class="mb-2 p-2 bg-light">          
          <p><u><?php echo t("Merchant information")?></u></p>
          <div class="custom-control custom-checkbox custom-control-inline">
            <input type="checkbox" class="custom-control-input" id="info" value="info" checked disabled>
            <label class="custom-control-label" for="info"><?php echo t("Information")?></label>
          </div>
          <div class="custom-control custom-checkbox custom-control-inline">
            <input type="checkbox" class="custom-control-input" id="settings" value="settings">
            <label class="custom-control-label" for="settings"><?php echo t("Settings")?></label>
          </div>
          <div class="custom-control custom-checkbox custom-control-inline">
            <input type="checkbox" class="custom-control-input" id="banner" value="banner">
            <label class="custom-control-label" for="banner"><?php echo t("Banner")?></label>
          </div>
          
          <div class="custom-control custom-checkbox custom-control-inline">
            <input type="checkbox" class="custom-control-input" id="pages" value="pages">
            <label class="custom-control-label" for="pages"><?php echo t("Pages")?></label>
          </div>
          <div class="custom-control custom-checkbox custom-control-inline">
            <input type="checkbox" class="custom-control-input" id="menu" value="menu">
            <label class="custom-control-label" for="menu"><?php echo t("Menu")?></label>
          </div>
        </div>

        <div class="mb-2 p-2 bg-light"> 
           <p><u><?php echo t("Attributes")?></u></p>
           <div class="custom-control custom-checkbox custom-control-inline">
            <input type="checkbox" class="custom-control-input" id="size" value="size">
            <label class="custom-control-label" for="size"><?php echo t("Size")?></label>
          </div>
          <div class="custom-control custom-checkbox custom-control-inline">
            <input type="checkbox" class="custom-control-input" id="ingredients" value="ingredients">
            <label class="custom-control-label" for="ingredients"><?php echo t("Ingredients")?></label>
          </div>
          <div class="custom-control custom-checkbox custom-control-inline">
            <input type="checkbox" class="custom-control-input" id="cooking_ref" value="cooking_ref">
            <label class="custom-control-label" for="cooking_ref"><?php echo t("Cooking Reference")?></label>
          </div>
        </div>

        <div class="mb-2 p-2 bg-light"> 
           <p><u><?php echo t("Food")?></u></p>
           <div class="custom-control custom-checkbox custom-control-inline">
            <input type="checkbox" class="custom-control-input" id="category" value="category">
            <label class="custom-control-label" for="category"><?php echo t("Category")?></label>
          </div>
          <div class="custom-control custom-checkbox custom-control-inline">
            <input type="checkbox" class="custom-control-input" id="addon_category" value="addon_category">
            <label class="custom-control-label" for="addon_category"><?php echo t("Addon category")?></label>
          </div>
          <div class="custom-control custom-checkbox custom-control-inline">
            <input type="checkbox" class="custom-control-input" id="addon_items" value="addon_items">
            <label class="custom-control-label" for="addon_items"><?php echo t("Addon items")?></label>
          </div>
          <div class="custom-control custom-checkbox custom-control-inline">
            <input type="checkbox" class="custom-control-input" id="items" value="items">
            <label class="custom-control-label" for="items"><?php echo t("items")?></label>
          </div>
        </div>

        <div class="mb-2 p-2 bg-light"> 
           <p><u><?php echo t("Images")?></u></p>
           <div class="custom-control custom-checkbox custom-control-inline">
            <input type="checkbox" class="custom-control-input" id="gallery" value="gallery">
            <label class="custom-control-label" for="gallery"><?php echo t("Gallery")?></label>
          </div>
          <div class="custom-control custom-checkbox custom-control-inline">
            <input type="checkbox" class="custom-control-input" id="media_library" value="media_library">
            <label class="custom-control-label" for="media_library"><?php echo t("Media library")?></label>
          </div>
        </div>        

        </div>
        <!-- body -->

       <div class="modal-footer">                  
         <button type="button" class="btn" data-dismiss="modal">
          <?php echo t("Cancel")?>
         </button>
         <a href="javascript:;" class="btn btn-green duplicate_merchant">
         <span class="hidden"><i class="fas fa-spinner fa-spin"></i>&nbsp;</span>
         <?php echo t("Duplicate")?>
         </a>
       </div>
               
     </div>
  </div>
</div>