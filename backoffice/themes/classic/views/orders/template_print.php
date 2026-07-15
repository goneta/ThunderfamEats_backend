<script type="text/x-template" id="xtemplate_print_order">

<div ref="print_modal" class="modal" tabindex="-1" role="dialog" data-backdrop="static" >
    <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header">
              
        <h5 class="modal-title" id="exampleModalLabel"><?php echo t("Print Order")?> #{{order_info.order_id}}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      
      <div v-if="is_loading" class="loading cover-loader d-flex align-items-center justify-content-center">
	    <div>
	      <div class="m-auto circle-loader medium" data-loader="circle-side"></div> 
	    </div>
	  </div>
	  
      <div class="modal-body printhis">
            
      <DIV v-if="hasData" class="receipt-container m-autox pt-2">		  
		  <template v-if="print_settings.receipt_logo">
		     <img class="img-200 d-block m-auto" :src="print_settings.receipt_logo"/>
			 <div class="pb-2"></div>
		  </template>
	      <div class="text-center mb-3">
		      <h5 class="m-0 mb-1">{{merchant.restaurant_name}}</h5>
		      <p class="m-0">{{merchant.merchant_address}}</p>
		      <p class="m-0"><?php echo t("Phone")?> : {{merchant.contact_phone}} /  <?php echo t("Email")?> : {{merchant.contact_email}}</p>
	      </div>
	      
	      <span v-for="index in line">-</span>
	      	      
	      <template v-if="order_info.service_code=='pos'">
	      <div class="details mt-2 mb-2">	     
	         <div class="row">
	           <div class="col"><?php echo t("Order ID")?> : {{order_info.order_id}}</div>	         
	          </div>	         	        	        
	          <div class="row">
	           <div class="col"><?php echo t("Date")?> : {{order_info.place_on_raw}}</div>	         
	          </div>	         	        	        
	          <div class="row">
	           <div class="col"><?php echo t("Customer")?> : 
	             <span v-if="order_info.client_id>0">{{order_info.customer_name}}</span>
	             <span v-else><?php echo t("Walk-in Customer")?></span>
	           </div>	         
	          </div>	         	        	        
	          
	          <div v-if="order_info.order_notes!=''" class="row">
	           <div class="col"><?php echo t("Notes")?> : {{order_info.order_notes}}</div>	         
	          </div>	         	        	        
	          
	      </div> <!-- order details -->
	      </template>
	      
	      <template v-else>
	      <div class="details mt-2 mb-2">	        	        	        
	        
	        <div class="row">
	         <div class="col"><?php echo t("Order ID")?> : {{order_info.order_id}}</div>	         
	        </div>	      
	        
	        <div class="row">
	         <div class="col"><?php echo t("Customer Name")?> : {{order_info.customer_name}}</div>	         
	        </div>	          
	        
	        <div class="row">
	         <div class="col"><?php echo t("Phone")?> : {{order_info.contact_number}}</div>	         
	        </div>	          
	        
			<template v-if="order_info.order_type=='delivery'">
	        <div class="row">
	         <div class="col"><?php echo t("Address")?> :  {{order_info.complete_delivery_address}}				 
			</div>	         
	        </div>	          
			
			<div v-if="order_info?.order_version==1" class="row">
	         <div class="col">{{order_info?.address1}} {{order_info?.delivery_address}}</div>	         
	        </div>	          

			<div class="row" v-if="order_info.address_label">
	         <div class="col"><?php echo t("Address label")?> : {{order_info.address_label}}</div>	         
	        </div>	          

			<div class="row" v-if="order_info.location_name">
	         <div class="col"><?php echo t("Aparment, suite or floor")?> : {{order_info.location_name}}</div>	         
	        </div>	          

			<div class="row" v-if="order_info.delivery_options">
	         <div class="col"><?php echo t("Delivery options")?> : {{order_info.delivery_options}}</div>	         
	        </div>	          

			<div class="row" v-if="order_info.custom_field1">
	         <div class="col"><?php echo t("Bonito")?> : {{order_info.custom_field1}}</div>	         
	        </div>	          

			<div class="row" v-if="order_info.custom_field2">
	         <div class="col"><?php echo t("Caliente")?> : {{order_info.custom_field2}}</div>	         
	        </div>	          
			</template>
	        
	        <div class="row mt-2">
	         <div class="col"><?php echo t("Order Type")?> : 
	          <template v-if="services[order_info.service_code]" > {{services[order_info.service_code].service_name}}</template>
	         </div>	         
	        </div>	
	                  
	        <div class="row">
	         <div class="col"><?php echo t("Delivery Date/Time")?> :
	           <template v-if="order_info.whento_deliver=='now'">
	           {{order_info.schedule_at}}
	           </template>
	           <template v-else>
	           {{order_info.schedule_at}}
	           </template>	            	            
	         </div>	         
	        </div>	  
	                
	        <div class="row">
	         <div class="col">{{order_info.payment_name}}</div>	         
	        </div>	          
			
			<div class="row" v-if="order_info.payment_change>0">
	         <div class="col"><?php echo t("Payment change")?> : {{order_info.payment_change_pretty}}</div>	         
	        </div>	
			
			<template v-if="hasBooking">
				<div class="row">
					<div class="col"><?php echo t("Guest")?> : {{order_table_data.guest_number}}</div>	         
				</div>	  	                							
				<div class="row">
					<div class="col"><?php echo t("Room name")?> : {{order_table_data.room_name}}</div>	         
				</div>	  	                			
				<div class="row">
					<div class="col"><?php echo t("Table name")?> : {{order_table_data.table_name}}</div>	         
				</div>	  	                			
			</template>
	        
	      </div>
	      <!-- details -->
	      </template>
	      
	      <span v-for="index in line">-</span>
	      
	      
	      <div class="items-details mt-2 mb-2"> 
	      
	       <!-- ITEMS  -->
	       <template v-for="(items, index) in items" >
	       <div class="row mb-1">
	         <div class="col">
	           <b>{{items.qty}} x {{ items.item_name }}</b><br/>
	           
	           <template v-if=" items.price.size_name!='' "> 
               ({{items.price.size_name}})
               </template>          
               
			   <template v-if="items.is_free" >
				  <?php echo CommonUtility::safeTranslate("Free")?>
               </template>
			   <template v-else>
					<template v-if="items.price.discount>0">         
					<del>{{items.price.pretty_price}}</del> {{items.price.pretty_price_after_discount}}
					</template>
					<template v-else>
					{{items.price.pretty_price}}
					</template>
			   </template>
	           
               <template v-if="items.item_changes=='replacement'">
	           <div class="m-0 text-muted small">
	            <?php echo t("Replace")?> "{{items.item_name_replace}}"
	           </div>	           
	           </template>
	           
	           <p class="mb-0" v-if=" items.special_instructions!='' ">{{ items.special_instructions }}</p>
	           
	          <template v-if=" items.attributes!='' "> 
	           <template v-for="(attributes, attributes_key) in items.attributes">                    
	            <p class="mb-0">            
	            <template v-for="(attributes_data, attributes_index) in attributes">            
	              {{attributes_data}}<template v-if=" attributes_index<(attributes.length-1) ">, </template>
	            </template>
	            </p>
	           </template>
	          </template>
               
	         </div> 
	         
	         <div class="col text-right">
	         
	           <template v-if="items.price.discount<=0 ">
	           {{ items.price.pretty_total }}
	           </template>
	          <template v-else>
	           {{ items.price.pretty_total_after_discount }}
	          </template>	           
	         
	         </div>
	       </div> <!-- row -->
	       
	       <!-- ADDON -->
	       <div class="mt-2 mb-2">
	       <template v-for="(addons, index_addon) in items.addons" >
	       <h6 class="m-0 ">{{ addons.subcategory_name }}</h6>
	       
	        <div class="row"  v-for="addon_items in addons.addon_items" >
	         <div class="col">
	            {{addon_items.qty}} x {{addon_items.pretty_price}} {{addon_items.sub_item_name}}
	         </div>
	         <div class="col text-right">{{addon_items.pretty_addons_total}}</div>
	       </div>	       
	       </template>
	       </div>
	       <!-- ADDON -->
	       
	       
	       <!-- ADDITIONAL CHARGE -->  
	       <div class="row mb-1"  v-for="item_charge in items.additional_charge_list" >
	         <div class="col">
	            <i><b>{{item_charge.charge_name}}</b></i>
	         </div>
	         <div class="col text-right">{{item_charge.pretty_price}}</div>
	       </div>
	       <!-- ADDITIONAL CHARGE -->  
	       
	       </template>
	       <!-- ITEMS  -->
	       	       
	      
	      </div>
	      <!-- items-details -->
	      
	      <span v-for="index in line">-</span>
	      
	      <div class="summary mt-2 mb-2"> 
	      	       
	       <div class="row" v-for="summary in order_summary" >	         
	         
	         <template v-if=" summary.type=='total' ">
	         
	         <div class="col">	           
	           <h5 class="m-0">{{summary.name}}</h5>
	         </div>
	         <div class="col text-right"><h5 class="m-0">{{summary.value}}</h5></div>
	         
	         </template>
	         <template v-else>
	         
	         <div class="col">	           
	           {{summary.name}}
	         </div>
	         <div class="col text-right">{{summary.value}}</div>
	         
	         </template>
	         
	       </div>	      	      
	      
	      </div>
	      <!-- summary -->
	      	      
	      <template v-if="order_info.service_code=='pos'">
	          <div  class="row">
	            <div v-if="payment_list[order_info.payment_code]" class="col">{{payment_list[order_info.payment_code]}}</div>	         
	            <div v-else class="col">{{order_info.payment_code}}</div>	         
	            <div class="col text-right">	              
	              <money-format :amount="order_info.receive_amount" ></money-format>
	            </div>	         
	          </div>	         	        	        
	          <span v-for="index in line">-</span>
	          <div class="row">
	            <div class="col"><?php echo t("Total Tendered")?></div>	         
	            <div class="col text-right"><money-format :amount="order_info.receive_amount" ></money-format></div>	         
	          </div>	         	        	        
	          <div class="row">
	            <div class="col"><?php echo t("Change")?></div>	         
	            <div class="col text-right"><money-format :amount="order_info.order_change" ></money-format></div>	         
	          </div>	         	        	        
	      </template>
	      
	      
	      <span v-for="index in line">-</span>
	      <div class="footer text-center mt-2 mb-2">
	        <h4>{{print_settings.receipt_thank_you}}</h4>	        
	      </div>
	      <span v-for="index in line">-</span>
	      

		  <div class="footer text-center mt-1 mb-1">
		     {{print_settings.receipt_footer}}
		  </div>
	      
      </DIV>
      <!-- receipt-container -->
      
      <DIV v-else > 
        <div class="text-center p-3" >
          <h5 class="text-muted" v-if="!is_loading"><?php echo t("Data not available")?></h5>
        </div>
      </DIV>     
      
      </div> <!-- body -->    
      
      <div class="modal-footer justify-content-end border-0" v-loading="loading_printing" >            		 
         <button class="btn btn-black" data-dismiss="modal" >&nbsp;&nbsp;<?php echo t("Close")?>&nbsp;&nbsp;</button>
		 <?php $printer_list = isset($printer_list)?$printer_list:''; ?>
		 <?php if(is_array($printer_list) && count($printer_list)>=1):?>
			<div class="dropdown dropleft">              
			  <button 
               :disabled="!hasData" class="btn btn-green"			   
			   role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
			    >&nbsp;&nbsp;<?php echo t("Print")?>&nbsp;&nbsp;</button>
              <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                  <a class="dropdown-item" href="javascript:;" @click="print" ref="print_button" ><?php echo t("Web print")?></a>
                  <?php foreach ($printer_list as $printers):?>
                  <a class="dropdown-item" href="javascript:;" @click="FPprint(<?php echo $printers['printer_id']?>)" ><?php echo $printers['printer_name']?></a>
                  <?php endforeach;?>
              </div>              
              </div>
		 <?php else : ?>
		   <button @click="print" ref="print_button"
           :disabled="!hasData" class="btn btn-green" >&nbsp;&nbsp;<?php echo t("Print")?>&nbsp;&nbsp;</button>
		 <?php endif;?>              
      </div>
      <!-- footer -->
        
    </div> <!-- content -->      
  </div> <!-- dialog -->      
</div>  <!-- modal -->              

</script>