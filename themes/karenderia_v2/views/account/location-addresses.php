
<DIV id="location-addresses">

<div v-loading="loading" v-cloak >
<div class="card p-3 mb-3 d-none d-lg-block" >
 <div class="rounded p-3 grey-bg" >
  <div class="row no-gutters align-items-center">
    
    <div class="col-md-2 ">
       <div class="header_icon _icons location d-flex align-items-center justify-content-center">         
       </div>
    </div>
    
    <div class="col-md-4 ">             
       <h5><?php echo t("Addresses")?></h5>
       <p class="m-0" v-if="data.length>0"><?php echo t("Wow, man of many places :)")?></p>            
       <p class="m-0" v-else><?php echo t("No address, lets change that!")?></p>            
    </div>
    
    <div class="col-md-3 ">           
      <h5>{{ getTotal }}</h5>
      <p><?php echo t("Addresses")?></p>
    </div>
    
    <div class="col-md-3  text-center ">
      <a class="btn btn-green"  href="javascript:;" @click="showAddAddress">
        <?php echo t("Add new address")?>
      </a>
    </div>
    
  </div>
 </div>
</div> <!--card -->


<!-- mobile view -->
<div class="card mb-3 mt-3 d-block d-lg-none">
<div class="rounded p-3 grey-bg" >
   <div class="d-flex justify-content-between align-items-center w-100">
     <div>
       <h5><?php echo t("Addresses")?></h5>
       <p class="m-0" v-if="data.length>0"><?php echo t("Wow, man of many places :)")?></p>            
       <p class="m-0" v-else><?php echo t("No address, lets change that!")?></p>            
     </div>
     <div>
        <a class="btn btn-green"  href="javascript:;" @click="showAddAddress" >
          <?php echo t("Add new address")?>
        </a>
     </div>
   </div>
 </div>
</div>
<!-- mobile view -->

<div class="row equal align-items-center position-relative">
   <template v-for="items in data">
   <div class="col-lg-3 col-md-4 mb-3" >
      <div class="card p-3 fixed-height address-slot" >
          <h5>{{items.address_label}}</h5>

          <div class="module truncate-overflow">           
             <p class="m-0 p-0">
                {{items.complete_address}}
             </p>
         </div>

         <div class="d-flex justify-content-between">
            <div><a @click="getAddress(items.address_uuid)" href="javascript:;" 
                class="btn normal small"><?php echo t("Edit")?> <span class="ml-1"><i class="zmdi zmdi-edit"></i></span></a></div>
            
            <div><a @click="ConfirmDelete(items.address_uuid)" 
                href="javascript:;" class="btn normal small"><?php echo t("Delete")?></a></div>       
        </div> <!--flex-->

      </div>
   </div>
   </template>
</div>
<!-- row -->

</div>

<?php $maps = CMaps::config();?>    
<components-location-address
ref="ref_location_address"
:delivery_option_list='<?php echo json_encode($delivery_option)?>'
:address_label_list='<?php echo json_encode($address_label)?>'
delivery_option_first_value="<?php echo $delivery_option_first_value?>"
address_label_first_value="<?php echo $address_label_first_value?>"
enabled_map_selection="<?php echo $enabled_map_selection?>"
:cmaps_config="{
    provider: '<?php echo CJavaScript::quote($maps['provider'])?>',  
    key: '<?php echo CJavaScript::quote($maps['key'])?>',  
    zoom: '<?php echo CJavaScript::quote($maps['zoom'])?>',
    icon: '<?php echo CJavaScript::quote($maps['icon'])?>',
    icon_merchant: '<?php echo CJavaScript::quote($maps['icon_merchant'])?>',
    icon_destination: '<?php echo CJavaScript::quote($maps['icon_destination'])?>',
    lat: '<?php echo CJavaScript::quote($maps['default_lat'])?>',
    lng: '<?php echo CJavaScript::quote($maps['default_lng'])?>',
}"   
@after-saveaddress="afterSaveaddress"
>
</components-location-address>

</DIV>
<!-- location-addresses -->


<script type="text/x-template" id="xtemplate_location_address">
<el-dialog
    v-model="modal"
    title="<?php echo t("Address")?>"
    width="500"       
    :close-on-click-modal="false"
    @close="clearForm" 
    @open="Onopendialog"
>
<div v-loading="loading" >
<?php 
$this->renderPartial("//components/template_location_address",[
   'country_id'=>$country_id,
   'location_searchtype'=>$location_searchtype,
]);
?>
</div>

<template #footer>
      <div class="dialog-footer pr-2">
        <el-button 
        @click="onSubmit" 
        type="primary" 
        size="large"
        :loading="loading"
        >
        <?php echo t("Submit")?>
        </el-button>
      </div>
</template>

</el-dialog>
</script>