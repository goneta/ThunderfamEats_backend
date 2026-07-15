<div id="vm_delivery_details">

    <template v-if="isAddressNeeded">
    <div class="card-body" v-loading="loading">

        <div class="row mb-3" >    
            <div class="col-lg-6 col-md-6 col d-flex justify-content-start align-items-center" >
                <span class="badge badge-dark rounded-pill">2</span>
                <h5 class="m-0 ml-2 section-title"><?php echo t("Choose a delivery address")?></h5>			         
            </div>		     			   
        </div> <!--row-->
        
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
                                 
           <div class="border-green p-4">
             <div class="row align-items-start">
              <div class="col-9">                                   
                  <el-radio-group v-model="address_uuid" class="ml-4">
                    <el-radio :label="address_uuid" size="large">
                      <div class="text-dark font-weight-bold">
                        <template v-if="getAddressLabel[deliveryAddress.attributes.address_label]">
                           {{getAddressLabel[deliveryAddress.attributes.address_label]}}
                        </template>
                        <template v-else
                        >
                        {{deliveryAddress.attributes.address_label}}
                        </template>
                      </div>
                      <div class="text-dark">
                        <!-- {{deliveryAddress.address.address1}} {{deliveryAddress.address.formatted_address}} -->
                        {{deliveryAddress.address.complete_delivery_address}}
                      </div>
                      <div class="text-dark"><?php echo t("Delivery instructions")?>: {{deliveryAddress.attributes.delivery_instructions}}</div>
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
                        
        </template>
                
                        
        <template v-if="!hasSavedAddress && !loading || isEdit || isAdd">          
        
        <div class="row align-items-center">
             <div class="col-6">
                 <h6 class="m-0 p-0"><?php echo t("Delivery address")?></h6>
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
        
        <!-- <form  @submit.prevent="saveAddress" >  -->
                    
        <div class="position-relative">        
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
        </div>
        
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
          @after-closemodal="afterClosemodal"
          >
          </components-select-address>
        </template>
                        
        <div class="row pt-2 align-items-center">
          <div class="col-10" v-if="hasDeliveryAddress">            
            <h5 class="m-0 p-0">{{formatted_address}} {{street_number}}</h5>
            <!-- <p class="m-0 p-0">{{city}}, {{postal_code}}</p> -->
          </div>
          <div class="col-2">
             <button @click="showSelectAddress" type="button" class="btn btn-link">
              <?php echo t("Edit")?>
             </button>
          </div>
        </div>
        <!-- row -->
                
        <div class="pt-2"></div>     
                
        <?php if($maps_config['address_format_use']==2):?>
          
          <div class="row">
            <div class="col">
                <div class="form-label-group">    
                  <input class="form-control form-control-text" v-model="formatted_address"
                    id="formatted_address" type="text" >   
                  <label for="formatted_address"><?php echo t("Street name")?> (<?php echo t("Mandatory")?>)</label> 
                </div>               
            </div>
            <div class="col">
                <div class="form-label-group">    
                  <input class="form-control form-control-text" v-model="street_number"
                    id="street_number" type="text" >   
                  <label for="street_number"><?php echo t("Street number")?> (<?php echo t("Mandatory")?>)</label> 
                </div>               
            </div>
          </div>

          <div class="row">
            <div class="col">
                <div class="form-label-group">    
                  <input class="form-control form-control-text" v-model="city"
                    id="city" type="text" >   
                  <label for="city"><?php echo t("City")?></label> 
                </div>   
            </div>
            <div class="col">
              <div class="form-label-group">    
                  <input class="form-control form-control-text" v-model="address2"
                    id="address2" type="text" >   
                  <label for="address2"><?php echo t("Floor")?></label> 
                </div>   
            </div>
          </div>

          <div class="row">
            <div class="col">
                <div class="form-label-group">    
                  <input class="form-control form-control-text" v-model="postal_code"
                    id="postal_code" type="text" >   
                  <label for="postal_code"><?php echo t("Postal Code")?> (<?php echo t("Mandatory")?>)</label> 
                </div>   
            </div>
            <div class="col">
              <div class="form-label-group">    
                  <input class="form-control form-control-text" v-model="company"
                    id="company" type="text" >   
                  <label for="company"><?php echo t("Company")?></label> 
                </div>  
            </div>
          </div>
        <?php elseif($maps_config['address_format_use']==3):?>
          <div class="form-label-group">
              <input v-model="formatted_address" class="form-control form-control-text" placeholder="" id="formatted_address" type="text">
              <label for="formatted_address">
                <?php echo t("Street name")?>
              </label>
            </div>
        <?php else :?>

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

        <?php endif;?>    

        <h5 class="m-0 mt-2 mb-2"><?php echo t("Delivery options")?></h5>
        <select v-model="delivery_options" class="form-control custom-select">          
           <option v-for="(items, key) in data.delivery_option" :value="key" >{{items}}</option>      
        </select>        

        <h5 class="m-0 mt-3 mb-2"><?php echo t("Add delivery instructions")?></h5>
        <textarea v-model="delivery_instructions" id="delivery_instructions" class="form-control form-control-text font13" 
        placeholder="<?php echo t("eg. ring the bell after dropoff, leave next to the porch, call upon arrival, etc")?>" style="max-height: 150px;">
        </textarea>

        <template v-if="data.custom_field_enabled">          

          <div class="d-flex align-items-center">
				  <div class="mr-2"><h5 class="m-0 mt-2 mb-2"><?php echo t("Bonito")?></h5></div>
				  <div>				 
				  <el-tooltip placement="top" content="<?php echo t("custom_field1_info")?>">				
					<el-button link type="plain">
					 <i class="zmdi zmdi-info font20"></i>
					</el-button>
				  </el-tooltip>				  
				  </div>
				</div>

          <select v-model="custom_field1" class="form-control custom-select">          
            <option v-for="(items, key) in data.custom_field1_data" :value="key" >{{items}}</option>      
          </select>        

          <div class="d-flex align-items-center">
            <div class="mr-2"><h5 class="m-0 mt-2 mb-2"><?php echo t("Caliente")?></h5></div>
            <div>				 
            <el-tooltip placement="top" content="<?php echo t("custom_field2_info")?>">				
            <el-button link type="plain">
            <i class="zmdi zmdi-info font20"></i>
            </el-button>
            </el-tooltip>				  
            </div>
				 </div>

          <textarea v-model="custom_field2" id="custom_field2" class="form-control form-control-text font13" 
          style="max-height: 150px;">
          </textarea>
        </template>

        <h5 class="m-0 mt-3 mb-2"><?php echo t("Address label")?></h5>
        <div class="btn-group btn-group-toggle input-group-small mb-4" >
	           <label class="btn" v-for="(items, key) in data.address_label" :class="{ active: address_label==key }" >
	             <input v-model="address_label" type="radio" :value="key" > 
	             {{ items }}
	           </label>           
	      </div>

        <button class="btn btn-green w-100" @click="saveAddress" :class="{ loading: loading_save }"  >
		        <span class="label">
              <?php echo t("Save address")?>
            </span>
		        <div class="m-auto circle-loader" data-loader="circle-side"></div>
		    </button>
        
        <!-- </form>         -->

        </template>

    </div>
    <!-- body -->
    <div class="divider p-0"></div>

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

    </template>
</div>