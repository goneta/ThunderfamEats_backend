<div id="location-checkout-address" v-cloak>

   <template v-if="loading">
     <div class="card-body">
     <el-skeleton animated>
        <template #template>
           <el-skeleton-item variant="rect"  style="height: 80px;" ></el-skeleton-item>
        </template>
    </el-skeleton>
     </div>
   </template>

    <template v-if="isAddressNeeded">
    <div class="card-body" >
    
        <div class="row mb-3" >    
            <div class="col-lg-6 col-md-6 col d-flex justify-content-start align-items-center" >
                <span class="badge badge-dark rounded-pill">2</span>
                <h5 class="m-0 ml-2 section-title"><?php echo t("Choose a delivery address")?></h5>			         
            </div>		     			   
        </div> <!--row-->

        
        <template v-if="hasDefaultAddress && !add_address">

            <div class="row align-items-center">
                <div class="col-6">
                    <h6 class="m-0 p-0"><?php echo t("Delivery Address")?></h6>
                </div>
                <div class="col-6 text-right">
                <button type="button" class="btn btn-link" @click="add_address=!add_address">
                <i class="zmdi zmdi-plus"></i> <?php echo t("Add Address")?>
                </button>
                </div>
            </div>
                       
            <div class="border-green p-4" v-loading="loading_get">
            <div class="row align-items-center">
                <div class="col-9">                                        
                   <el-radio-group v-model="address_uuid" class="ml-4">                      
                        <el-radio :label="default_address.address_uuid" size="large">
                            <div class="text-dark font-weight-bold">
                            {{default_address.address_label}}
                            </div>
                            <div class="text-dark">
                            {{default_address.complete_address}}
                            </div>
                            <div class="text-dark"><?php echo t("Delivery instructions")?>: {{default_address.delivery_instructions}}</div>
                        </el-radio>                        
                   </el-radio-group>
                </div>
                <div class="col-3 text-right">
                   <el-button @click="getAddress()" circle ><i style="font-size: 20px;" class="zmdi zmdi-edit"></i></el-button>                   
                   <el-popconfirm @confirm="deleteAddress" title="<?php echo t("Are you sure to delete this?")?>" 
                   confirm-button-text="<?php echo t("Yes")?>" cancel-button-text="<?php echo t("Cancel")?>" width="200">
                        <template #reference>
                        <el-button type="danger" circle >
                           <i style="font-size: 20px;" class="zmdi zmdi-delete"></i>
                        </el-button>
                        </template>
                    </el-popconfirm>

                </div>
            </div>
            </div>
            <!-- border-green -->

                        
            <!-- POP OVER SAVE ADDRESS -->
            <div class="d-none d-lg-block">
            <el-popover :visible="visible" placement="right" :width="300">
               <template #default>                  
               <div class="el-popover__title" role="title">
                  <?php echo t("Save Address")?>
               </div>
               <div class="custom-element-radio">
                  <el-scrollbar max-height="280px">
                     <el-radio-group v-model="save_address_uuid">
                        <template v-for="items in data">
                            <el-radio :value="items.address_uuid" size="large" border class="w-100">
                               <div class="text-dark font-weight-bold">{{items.address_label}}</div>
                                <div>{{items.complete_address}}</div>
                            </el-radio>                      
                        </template>
                     </el-radio-group>
                  </el-scrollbar>                  
                  <div class="text-right mt-2">
                        <el-button size="large"  @click="visible = false"><?php echo t("Cancel")?></el-button>
                        <el-button type="success" size="large" @click="setSelectAddress()"><?php echo t("Select")?></el-button>
                    </div>                  
               </div>
               </template>
               <template #reference>                  
                  <el-button class="m-2" link size="large" type="primary"  @click="visible = true" >
                      <?php echo t("View Saved Addresses")?>
                   </el-button>
               </template>
            </el-popover>
            <!-- POP OVER SAVE ADDRESS -->
            </div>

            <div class="d-block d-lg-none">
                <el-button class="m-2" link size="large" type="primary" @click="modal_address_list=true" >
                    <?php echo t("View Saved Addresses")?>
                </el-button>
            </div>


        </template>
        <template v-else>        
            <div class="row align-items-center mb-1">
                <div class="col-6">
                    <h6 class="m-0 p-0"><?php echo t("Delivery Address")?></h6>
                </div>                
                <div class="col-6 text-right" v-if="add_address">
                   <button  @click="add_address=!add_address" type="button" class="btn btn-link">
                     <?php echo t("Cancel")?>
                  </button>
                </div>                
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

        </template>

    </div>
    <!-- card body -->
    </template>

    <el-dialog
        v-model="modal_address_list"
        title="<?php echo t("Save Address")?>"
        width="500"        
        modal-class="position-at-bottom"
    >
     
      <div class="custom-element-radio">
      <el-scrollbar max-height="250px">
        <el-radio-group v-model="save_address_uuid">
        <template v-for="items in data">
            <el-radio :value="items.address_uuid" size="large" border class="w-100">
                <div class="text-dark font-weight-bold">{{items.address_label}}</div>
                <div>{{items.complete_address}}</div>
            </el-radio>                      
        </template>
        </el-radio-group>
      </el-scrollbar>                  
      </div>

       <div class="text-right mt-2">
          <el-button @click="modal_address_list = false" size="large" ><?php echo t("Cancel")?></el-button>
          <el-button type="success" size="large" @click="setSelectAddress()" >
          <?php echo t("Select")?>
          </el-button>
       </div>
    </el-dialog>

</div>
<!-- location-checkout-address -->

<script type="text/x-template" id="xtemplate_location_address">

<div v-loading="loading" >
<?php 
$this->renderPartial("//components/template_location_address",[
   'country_id'=>$country_id,
   'location_searchtype'=>$location_searchtype,
   'delivery_option'=>$delivery_option,
   'address_label'=>$address_label,
   'delivery_option_first_value'=>$delivery_option_first_value,
   'address_label_first_value'=>$address_label_first_value,
   'enabled_map_selection'=>$enabled_map_selection
]);
?>
</div>

<el-button 
@click="onSubmit" 
type="success" 
size="large"
:loading="loading"
class="w-100"
>    
    <?php echo t("Save Address")?>
</el-button>
</script>