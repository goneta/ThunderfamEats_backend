<script type="text/x-template" id="xtemplate_order_details_pos">
<div class="card">

  <div class="card-body" v-loading="inner_loading">
    
	<div class="input-group mb-3">
	  <select ref="customer" class="custom-select" id="inputGroupSelect02">   	      
	  </select>
	  <div class="input-group-append">
	    <label @click="showCustomer" class="input-group-text cursor-pointer btn-green" for="inputGroupSelect02">
	      <i class="zmdi zmdi-account-add"></i>
	    </label>
	  </div>
	</div>    
  
   
     
   <el-radio-group v-model="transaction_type" @change="udatePosTransaction" >
       <template v-for="items_payment in transaction_list">
          <el-radio-button :label="items_payment.service_code" >{{items_payment.service_name}}</el-radio-button>      
      </template>
   </el-radio-group>
      
   <div v-if="transaction_type=='delivery'" class="d-flex align-items-center justify-content-between mb-2 mt-1">
     <div><h6 class="m-0"><i class="zmdi zmdi-account-o" style="font-size:17px;"></i> <?php echo t("Delivery Infomation")?></h6></div>     
     <div>
       <el-button @click="showAddress" type="primary" link><i class="zmdi zmdi-edit" style="font-size:17px;"></i></el-button>
     </div>
   </div>
   <p v-if="transaction_type=='delivery'">{{delivery_address}}</p>
  
   <h5 class="mb-2"><?php echo t("Items")?></h5>
          
   <!-- ITEMS  -->   
   <DIV class="pos-order-details nice-scroll p-2 pb-0">
    <template v-for="(items, index) in items" >
    <div class="row" >
    
     <div class="col-2 d-flex justify-content-center">
       <img class="rounded img-40" :src="items.url_image" >
     </div>
       
     <div class="col-5 d-flex justify-content-start flex-column">
              
	     <p class="mb-1">
	     <!-- {{items.qty}}x -->
	     {{ items.item_name }}
	      <template v-if=" items.price.size_name!='' "> 
	      ({{items.price.size_name}})
	      </template>                      
	     </p> 
	     	    
	     <template v-if="items.price.discount>0">         
	       <p class="m-0 font11"><del>{{items.price.pretty_price}}</del> {{items.price.pretty_price_after_discount}}</p>
	     </template>
	     <template v-else>
	       <p class="m-0 font11">{{items.price.pretty_price}}</p>
	     </template>
	     
	     <!-- QUANTITTY -->
	     <div class="mt-1 mb-1 quantity-wrap">
		  <div class="quantity d-flex justify-content-between align-items-center">
		    <div>
		      <a @click="changeQty(items,'less')" href="javascript:;" class="rounded-pill qty-btn">
		        <i class="zmdi zmdi-minus"></i>
		      </a>
		    </div>
		    <div class="qty">{{items.qty}}</div>
		    <div>
		      <a  @click="changeQty(items,'add')"  href="javascript:;" class="rounded-pill qty-btn">
		        <i class="zmdi zmdi-plus"></i>
		      </a>
		    </div>
		  </div>
		</div>
		<!-- QUANTITTY -->
	     	     
	     <p class="mb-0 text-success" v-if=" items.special_instructions!='' ">{{ items.special_instructions }}</p>
	     
	     <template v-if=" items.attributes!='' "> 
	      <template v-for="(attributes, attributes_key) in items.attributes">                    
	        <p class="mb-0">            
	        <template v-for="(attributes_data, attributes_index) in attributes">            
	          {{attributes_data}}<template v-if=" attributes_index<(attributes.length-1) ">, </template>
	        </template>
	        </p>
	      </template>
	    </template>
	                    
     </div> <!-- col -->        
     
     <div class="col-4 d-flex justify-content-start flex-column text-right pr-0 pl-0">
     
       <!--REMOVE ITEM -->
       <a @click="removeItem(items)" href="javascript:;" class="rounded-pill circle-button ml-auto mb-1"><i class="zmdi zmdi-close"></i></a>
     
       <template v-if="items.price.discount<=0 ">
          {{ items.price.pretty_total }}
        </template>
        <template v-else>
           {{ items.price.pretty_total_after_discount }}
        </template>	        
     </div> <!-- col -->
          
   </div> <!-- row -->
   <!-- ITEMS  -->       
  
    <!-- ADDON -->
   <div class="row mb-2" v-for="(addons, index_addon) in items.addons" >
      <div class=" col-2 d-flex justify-content-center">&nbsp;</div>
      <div class=" col-9 d-flex justify-content-start flex-column">
        <p class="m-0"><b>{{ addons.subcategory_name }}</b></p>		
        
        <div class="row" v-for="addon_items in addons.addon_items" >
          <div class=" col-8">
            <p class="m-0">{{addon_items.qty}} x {{addon_items.pretty_price}} {{addon_items.sub_item_name}}</p>
          </div> <!-- col -->          
          <div class=" col-4 text-right">
            <p class="m-0">{{addon_items.pretty_addons_total}}</p>
          </div>
          <!-- col -->          
        </div>
          
      </div> <!-- col -->          
   </div> <!-- row -->
   <!-- ADDON -->
   
   <!-- ADDITIONAL CHARGE -->       
   <div class="row mb-2" v-for="item_charge in items.additional_charge_list" >
      <div class=" col-2 d-flex justify-content-center">&nbsp;</div>
      <div class=" col-6 d-flex justify-content-start flex-column">
        <span class="text-success">{{item_charge.charge_name}} </span>
      </div>
      <div class=" col-3 d-flex justify-content-start flex-column text-right">
       <p class="m-0">{{item_charge.pretty_price}}</p>
      </div>
   </div> <!-- row -->
   <!-- ADDITIONAL CHARGE -->
   
   <hr> 
   </template>
   </DIV> <!-- END OF ITEMS -->
      
   <DIV class="pt-3 ">     
   <template v-for="summary in order_summary" >
     <template v-if=" summary.type=='total' ">
     <hr/>
     <div class="row mb-1">
       <div class="col-2 d-flex justify-content-center"></div>
       <div class="col-6 d-flex justify-content-start flex-column"><h6 class="m-0">{{summary.name}}</h6></div>
       <div class="col-3 d-flex justify-content-start flex-column text-right"><h6 class="m-0">{{summary.value}}</h6></div>
     </div>
     </template>
     
     <template v-else>
       <div class="row mb-1">
         <div class="col-2 d-flex justify-content-center"></div>
         <div class="col-5 d-flex justify-content-start flex-column">{{ summary.name }}</div>
         <div class="col-4 d-flex justify-content-start flex-column text-right">

         <template v-if="summary.type=='voucher'">
             <div class="d-flex"> 
              <div>
                <a ref="voucher"  @click="removeVoucher"><h5 class="m-0 text-danger"><i class="zmdi zmdi-delete"></i></h5></a>
              </div>
              <div>
                {{ summary.value }}
              </div>
            </div>
         </template>
         <template v-else-if="summary.type=='offers'">
            <div class="d-flex justify-content-end"> 
              <div class="mr-1">
                <a ref="voucher"  @click="removeDiscount"><h5 class="m-0 text-danger"><i class="zmdi zmdi-delete"></i></h5></a>
              </div>
              <div>
                {{ summary.value }}
              </div>
            </div>
         </template>

         <template v-else-if="summary.type=='delivery_fee' || summary.type=='tip'">
            <div class="d-flex justify-content-end"> 
              <div class="mr-1">
                <a ref="voucher"  @click="removeAdditionalFee(summary.type)"><h5 class="m-0 text-danger"><i class="zmdi zmdi-delete"></i></h5></a>
              </div>
              <div>
                {{ summary.value }}
              </div>
            </div>
         </template>

         <template v-else>
         {{ summary.value }}
         </template>
                
         
         </div>
       </div>
     </template>
   </template>
   
   <div class="mt-4">       
    <el-input
      v-model="additional_fee"      
      class="input-with-select"      
    >
      <template #prepend>
        <el-select v-model="additional_fee_type" placeholder="<?php echo t("Select")?>" style="width:80px">
        <el-option
          v-for="(item,key) in additional_list"
          :key="key"
          :label="item"
          :value="key"
        />
        </el-select>
      </template>
      <template #append>
        <el-button @click="addAdditionalFee" :disabled="!hasData" ><?php echo t("Apply")?></el-button>
      </template>
    </el-input>
  </div>
  
  <div class="btn-group btn-group-lg w-100 mt-3" role="group" aria-label="Large button group">
    <button @click="showPromo" :disabled="!hasData" type="button" class="btn btn-secondary text-left">
      <p class="m-0"><i class="zmdi zmdi-label"></i></p>
      <p class="m-0"><?php echo t("Promo")?></p>
    </button>
    <button @click="showDiscount" :disabled="!hasData" type="button" class="btn btn-secondary text-left">
      <p class="m-0"><i class="zmdi zmdi-money-off"></i></p>
      <p class="m-0"><?php echo t("Discount")?></p>
    </button>    
    <button @click="resetPos" type="button" class="btn btn-secondary text-left">
      <p class="m-0"><i class="zmdi zmdi-refresh"></i></p>
      <p class="m-0"><?php echo t("Reset")?></p>
    </button>    
  </div>
  
  <button @click="showPayment" class="btn-green btn w-100 mt-2" :disabled="!hasData">
   <div class="d-flex justify-content-between align-items-center">
     <div class="flex-col text-left">
       <p class="m-0"><b><?php echo t("Proceed to pay")?></b></p>
       <p class="m-0"><i>{{items.length}} <?php echo t("Items")?></i></p>
     </div>
     <div class="flex-col">
       <h5>       
       <money-format :amount="summary_total" ></money-format>
       </h5>
     </div>
   </div>
  </button>  
  </DIV>
   
 
       
  
  </div> <!-- card body -->
</div> <!-- card -->

<components-customer-entry
ref="customer_entry"    
  :ajax_url="ajax_url"  
  :label="{
      clear_items:'<?php echo CJavaScript::quote(t("Clear all items"))?>',     
      customer:'<?php echo CJavaScript::quote(t("Customer"))?>',     
      first_name:'<?php echo CJavaScript::quote(t("First Name"))?>',     
      last_name:'<?php echo CJavaScript::quote(t("Last Name"))?>',     
      emaiL_address:'<?php echo CJavaScript::quote(t("Email address"))?>',     
      contact_phone:'<?php echo CJavaScript::quote(t("Contact Phone"))?>',     
      submit:'<?php echo CJavaScript::quote(t("Submit"))?>',     
  }"  
  @after-savecustomer="afterSavecustomer"
>
</components-customer-entry>

<?php $maps_config = CMaps::config(); ?>


<components-customer-address
ref="customer_address"    
:ajax_url="ajax_url"  
:label="{            
      submit:'<?php echo CJavaScript::quote(t("Submit"))?>',     
}"  
keys="<?php echo isset($maps_config['key'])?$maps_config['key']:''?>"
provider="<?php echo isset($maps_config['provider'])?$maps_config['provider']:''?>"
zoom="<?php echo isset($maps_config['zoom'])?$maps_config['zoom']:''?>"
:center="{
  lat: '<?php echo CJavaScript::quote( isset($maps_config['default_lat'])?$maps_config['default_lat']:'' )?>',  
  lng: '<?php echo CJavaScript::quote( isset($maps_config['default_lng'])?$maps_config['default_lng']:'' )?>',  
}"        
:order_uuid="order_uuid"
:transaction_type="transaction_type"
@refresh-order="POSdetails" 
@Set-address="SetAddress" 
>
</components-customer-address>


<div ref="promo_modal" class="modal" tabindex="-1" role="dialog" data-backdrop="static"  >
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content">

     <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle"><?php echo t("Have a promo code?")?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      
      <div class="modal-body">            
      <form @submit.prevent="applyPromoCode">
        <div class="form-label-group">
          <input ref="promo_code" v-model="promo_code" class="form-control form-control-text" placeholder="" id="promo_code" type="text" maxlength="20">
          <label for="promo_code" class="required"><?php echo t("Add promo code")?></label>
        </div>
	    </form>      
      </div> 

      <div class="modal-footer">   
        <button type="button" @click="applyPromoCode" class="btn btn-green w-100" :class="{ loading: promo_loading }"         
         :disabled="!hasCoopon"
         >
          <span><?php echo t("Apply")?></span>
          <div class="m-auto circle-loader" data-loader="circle-side"></div> 
        </button>
      </div>
      
    </div>
  </div>
</div>  
<!-- modal -->


<div ref="promo_discount" class="modal" tabindex="-1" role="dialog" data-backdrop="static"  >
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content">

     <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle"><?php echo t("Discount")?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      
      <div class="modal-body">            
      <form @submit.prevent="applyDiscount">
      
        <el-input v-model="discount"  size="large" v-maska="'#*.##'"  >
          <template #append>%</template>
        </el-input>       

	    </form>      
      </div> 

      <div class="modal-footer">   
          <el-button @click="applyDiscount" type="success" size="large" class="w-100" :loading="loading_discount" >
            <?php echo t("Apply")?>
          </el-button>
      </div>
      
    </div>
  </div>
</div>  
<!-- modal -->


<div ref="submit_order_modal" class="modal" tabindex="-1" role="dialog" data-backdrop="static"  >
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle"><?php echo t("Create Payment")?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      
      <div class="modal-body">
                                   
       
        <div class="mb-3">  
          <el-row :gutter="12">
            <el-col :span="8">
              <el-card shadow="hover">
                 <h6 class="m-0"><?php echo t("Total Due")?></h6>
                 <money-format :amount="summary_total" ></money-format>
              </el-card>
            </el-col>
            <el-col :span="8">
              <el-card shadow="hover">
                  <h6 class="m-0"><?php echo t("Pay Left")?></h6>
                  <money-format :amount="pay_left" ></money-format>
              </el-card>
            </el-col>
            <el-col :span="8">
              <el-card shadow="hover">
                 <h6 class="m-0"><?php echo t("Change")?></h6>
                 <money-format :amount="change" ></money-format>
              </el-card>
            </el-col>
          </el-row>
        </div> 
       
       <el-row :gutter="20">
        <el-col :span="24">
         <p class="mb-1"><?php echo t("Choose preferred time")?></p>
         <el-radio-group v-model="whento_deliver" size="large">
            <template v-for="delivery_option_item in delivery_option">
               <el-radio-button :label="delivery_option_item.value">                
                  {{delivery_option_item.short_name}}
               </el-radio-button>
            </template>            
          </el-radio-group>
        </el-col>
      </el-row>
                        
      <template v-if="whento_deliver=='schedule'">
      <el-row :gutter="20">
        <el-col :span="12">
           <p class="mb-1"><?php echo t("Date")?></p>
            <el-select v-model="delivery_date"  
            size="large" 
            placeholder="<?php echo t("Select")?>" 
            no-data-text="<?php echo t("No data")?>"
            style="width:100%;" 
            >
              <el-option
                v-for="item in opening_hours.dates"
                :key="item.value"
                :label="item.name"
                :value="item.value"                  
              />
            </el-select>
        </el-col>
        <el-col :span="12">                        
           <p class="mb-1"><?php echo t("Time")?></p>            
            <el-select v-model="delivery_time"  
            size="large" 
            placeholder="<?php echo t("Select")?>" 
            no-data-text="<?php echo t("No data")?>"
            style="width:100%;" 
            >
              <el-option
                v-for="item in getTimelist" 
                :key="item.start_time"
                :label="item.pretty_time"
                :value="item.start_time"                  
              />
            </el-select>
        </el-col>
      </el-row>
      <div class="p-2"></div>
      </template>
      
      <template v-if="transaction_type=='delivery'">

      </template>
                             
       <el-row :gutter="20">
        <el-col :span="24">
            <p class="mb-1"><?php echo t("Order status")?></p>
            <el-select v-model="order_status"  
            size="large" 
            placeholder="<?php echo t("Select")?>" 
            no-data-text="<?php echo t("No data")?>"
            style="width:100%;"
            >
                  <el-option
                    v-for="item in order_status_list"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value"
                  />
              </el-select>
          </el-col>
       </el-row>       
       
       <div class="pt-2 pb-2"></div>
       
       <template v-if="transaction_type=='dinein'">        
       <!-- <el-card shadow="never"> -->
                
        <el-row :gutter="20">
           <el-col :span="24">
              <p class="mb-1"><?php echo t("Guest")?></p>
              <el-input-number
                  v-model="guest_number"
                  :min="1"
                  :max="999999"
                  controls-position="right"
                  size="large"                
                  style="width:100%;"
                />
            </el-col>
         </el-row>
         <div class="pt-2 pb-2"></div>

          <el-row :gutter="20">
              <el-col :span="12">
                  <p class="mb-1"><?php echo t("Room name")?></p>
                  <el-select v-model="room_id"  
                  size="large" 
                  @change="roomChange" 
                  placeholder="<?php echo t("Select")?>" 
                  style="width:100%;"
                  no-data-text="<?php echo t("No data")?>"
                   >
                    <el-option
                      v-for="item in room_list"
                      :key="item.value"
                      :label="item.label"
                      :value="item.value"
                      style="width:100%;"
                    />
                  </el-select>
              </el-col>
              <el-col :span="12">
                  <p class="mb-1"><?php echo t("Table name")?></p>
                  <el-select v-model="table_id"  size="large" 
                  placeholder="<?php echo t("Select")?>"
                  style="width:100%;" 
                  no-data-text="<?php echo t("No data")?>"
                  >
                    <el-option
                      v-for="item in table_list[room_id]"
                      :key="item.value"
                      :label="item.label"
                      :value="item.value"
                      style="width:100%;"
                    />
                  </el-select>
              </el-col>
          </el-row>
          

        <!-- </el-card> -->
        <div class="pt-2 pb-2"></div>
       </template>
         
       <el-row :gutter="20">
           <el-col :span="12" >
              <p class="mb-1"><?php echo t("Receive amount")?></p>              
              <el-input-number
                v-model="receive_amount"
                :min="1"
                :max="999999999"
                controls-position="right"
                size="large"           
                style="width:100%;"                 
              />
           </el-col>
           <el-col :span="12" >
              <p class="mb-1"><?php echo t("Payment Method")?></p>
              <el-select v-model="payment_code"  
              size="large" 
              placeholder="<?php echo t("Select")?>" 
              no-data-text="<?php echo t("No data")?>"
              style="width:100%;" 
              >
                <el-option
                  v-for="item in payment_list"
                  :key="item.payment_code"
                  :label="item.payment_name"
                  :value="item.payment_code"                  
                />
              </el-select>
           </el-col>
       </el-row>

       <div class="pt-2 pb-2"></div>

       <el-row :gutter="20">
        <el-col :span="24">
            <p class="mb-1"><?php echo t("Payment Reference number")?></p>
            <el-input
              v-model="payment_reference"              
              size="large"              
            />
        </el-col>
       </el-row>

       <div class="pt-2 pb-2"></div>

       <el-row :gutter="20">
        <el-col :span="24">
            <p class="mb-1"><?php echo t("Add order notes")?></p>
            <el-input
                v-model="order_notes"
                :autosize="{ minRows: 2, maxRows: 4 }"
                type="textarea"            
            />
          </el-col>
       </el-row>

         
      </div>
      <!-- modal body --> 
      
      <div class="modal-footer">
        <button type="button" @click="submitOrder" class="btn btn-green pl-4 pr-4 w-100" :class="{ loading: create_payment_loading }"         
         :disabled="!hasValidPayment"
         >
          <span><?php echo t("Pay Now")?></span>
          <div class="m-auto circle-loader" data-loader="circle-side"></div> 
        </button>
      </div>

    </div>
  </div>
</div>  
<!-- modal -->      

</script>