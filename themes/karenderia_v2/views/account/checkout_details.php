<div id="vm_delivery_details" class="container-fluid page-grey">
=>{{isAddressNeeded}}
   <div class="w-75 m-auto card">
      <div class="card-body"  v-loading="loading">                 
        
                      
        <template v-if="hasSavedAddress && !loading && !isEdit && !isAdd">
           <div class="row align-items-center">
             <div class="col-6">
                 <h6 class="m-0 p-0"><?php echo t("Delivery address")?></h6>
             </div>
             <div class="col-6 text-right">
              <button @click="AddNewAddress" type="button" class="btn btn-link">
              <i class="zmdi zmdi-plus"></i> <?php echo t("Add Address")?>
              </button>
             </div>
           </div>

           <!-- <pre>{{deliveryAddress}}</pre> -->
           <div class="border-green p-4">
             <div class="row align-items-start">
              <div class="col-9">                          
                  <el-radio-group v-model="address_uuid" class="ml-4">
                    <el-radio :label="address_uuid" size="large">
                      <div class="text-dark font-weight-bold">{{deliveryAddress.attributes.address_label}}</div>
                      <div class="text-dark">
                      {{deliveryAddress.address.address1}} {{deliveryAddress.address.formatted_address}}
                      </div>
                      <div class="text-dark">Delivery instructions: {{deliveryAddress.attributes.delivery_instructions}}</div>
                    </el-radio> 
                  </el-radio-group>                    
              </div>
              <div class="col-3 text-right">
              
              <!-- EDIT SAVED ADDRESSS -->
              <button @click="editSaveAddress"  class="btn btn-link text-green">
                <i style="font-size: 20px;" class="zmdi zmdi-edit"></i>
              </button>              
              <button @click="confirmDelete(deliveryAddress.address_uuid)" class="btn btn-link text-green">
                <i style="font-size: 20px;" class="zmdi zmdi-delete"></i>
              </button>

              </div>
             </div>
           </div>

           <button type="button" class="btn btn-link" @click="this.$refs.save_address.show(true)">
              <?php echo t("View Saved Addresses")?>
            </button>
            
            <div class="text-right">
              <a class="btn btn-green pl-3 pr-3" href="<?php echo $redirect_to;?>">
                <?php echo t("Continue")?>            
              </a>
            </div>
        </template>
                
                        
        <template v-if="!hasSavedAddress && !loading || isEdit || isAdd">          
        
        <div class="row align-items-center">
             <div class="col-6">
                 <h6 class="m-0 p-0"><?php echo t("Delivery Address")?></h6>
             </div>
             <div class="col-6 text-right">                              
                <template v-if="isEdit">
                  <button  @click="CancelEdit" type="button" class="btn btn-link">
                    <?php echo t("Cancel")?>
                  </button>                
                </template>     
                <template v-else-if="isAdd">
                  <button  @click="CanceAdd" type="button" class="btn btn-link">
                  <?php echo t("Cancel")?>
                  </button>
                </template>             
                <template v-else-if="hasAddressList && !isAdd && !isEdit">
                  <button type="button" class="btn btn-link" @click="this.$refs.save_address.show(true)">
                  <?php echo t("View Saved Addresses")?>
                  </button>
                </template>   
                <template v-else>
                  <div class="pb-4">&nbsp;</div>
                </template>          
             </div>             

          </div>
        
        <form  @submit.prevent="saveAddress" > 
            
        <!-- <pre>{{markers}}</pre>
        =>default_address
        <pre>{{default_address}}</pre> -->
        <components-maps
        ref="maps"
        keys="<?php echo $maps_config['key']?>"
        provider="<?php echo $maps_config['provider']?>"
        zoom="<?php echo $maps_config['zoom']?>"
        size="map-small"
        :center="{
          lat: '<?php echo CJavaScript::quote($maps_config['default_lat'])?>',  
          lng: '<?php echo CJavaScript::quote($maps_config['default_lng'])?>',  
        }"        
        :markers="markers"
        ></components-maps>
        
        <template v-if="!loading">          
          <components-select-address
          ref="address_modal"
          :data="deliveryAddress"
          keys="<?php echo $maps_config['key']?>"
          provider="<?php echo $maps_config['provider']?>"
          zoom="<?php echo $maps_config['zoom']?>"
          :center="{
            lat: '<?php echo CJavaScript::quote($maps_config['default_lat'])?>',  
            lng: '<?php echo CJavaScript::quote($maps_config['default_lng'])?>',  
          }"        
          :label="{
              exact_location : '<?php echo CJavaScript::quote(t("What's your exact location?"))?>', 
              enter_address : '<?php echo CJavaScript::quote(t("Enter your street and house number"))?>', 
              submit : '<?php echo CJavaScript::quote(t("Submit"))?>', 
          }"
          @after-changeaddress="afterChangeaddress"
          >
          </components-select-address>
        </template>
                
        <!-- <pre>{{deliveryAddress}}</pre> -->
        <div class="row pt-2 align-items-center">
          <div class="col-10" v-if="hasDeliveryAddress">
            <!-- <h5 class="m-0 p-0">{{deliveryAddress.address.formatted_address}}</h5>
            <p class="m-0 p-0">{{deliveryAddress.address.address1}}</p> -->
            <h5 class="m-0 p-0">{{address1}}</h5>
            <p class="m-0 p-0">{{formatted_address}}</p>
          </div>
          <div class="col-2">
             <button @click="showSelectAddress" type="button" class="btn btn-link">
              <?php echo t("Edit")?>
             </button>
          </div>
        </div>
        <!-- row -->
                
        <div class="pt-2"></div>        

        <div class="form-label-group">
          <input v-model="formatted_address" class="form-control form-control-text" placeholder="" id="formatted_address" type="text">
          <label for="formatted_address">
            <?php echo t("Street name")?>
          </label>
        </div>

        <div class="form-label-group">
          <input v-model="address1" class="form-control form-control-text" placeholder="" id="address1" type="text">
          <label for="address1">
            <?php echo t("Street number")?>
          </label>
        </div>
        
        <div class="form-label-group">
          <input v-model="location_name" class="form-control form-control-text" placeholder="" id="location_name" type="text">
          <label for="location_name">
            <?php echo t("Aparment, suite or floor")?>
          </label>
        </div>

        <h5 class="m-0 mt-2 mb-2"><?php echo t("Delivery options")?></h5>
        <select v-model="delivery_options" class="form-control custom-select">          
           <option v-for="(items, key) in data.delivery_option" :value="key" >{{items}}</option>      
        </select>        

        <h5 class="m-0 mt-3 mb-2"><?php echo t("Add delivery instructions")?></h5>
        <textarea v-model="delivery_instructions" id="delivery_instructions" class="form-control form-control-text font13" 
        placeholder="eg. ring the bell after dropoff, leave next to the porch, call upon arrival, etc" style="max-height: 150px;">
        </textarea>

        <h5 class="m-0 mt-3 mb-2"><?php echo t("Address label")?></h5>
        <div class="btn-group btn-group-toggle input-group-small mb-4" >
	           <label class="btn" v-for="(items, key) in data.address_label" :class="{ active: address_label==key }" >
	             <input v-model="address_label" type="radio" :value="key" > 
	             {{ items }}
	           </label>           
	      </div>

        <button class="btn btn-green w-100" @click="save" :class="{ loading: loading_save }"  >
		        <span class="label">
              <?php echo t("Save address")?>
            </span>
		        <div class="m-auto circle-loader" data-loader="circle-side"></div>
		    </button>
        
        </form>        

        </template>

      </div>
      <!-- body -->
    </div>
    
    <components-saveaddress
    ref="save_address"
    @after-selectaddress="afterSelectaddress"
    :label="<?php echo CommonUtility::safeJsonEncode([
      'save_address'=>t("Saved Addresses"),
      'save'=>t("Saved"),
      'cancel'=>t("Cancel"),
    ])?>"
    >
    </components-saveaddress>

</div>