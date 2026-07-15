<script type="text/x-template" id="xtemplate_order_details">

<components-loading-box
  ref="loading_box"
  message="<?php echo CommonUtility::safeTranslate("Processing ...")?>"
  donnot_close="<?php echo CommonUtility::safeTranslate("don't close this window")?>"
  >
</components-loading-box>

<!--COMPONENTS FORMS-->
<components-rejection-forms
ref="rejection"
ajax_url="<?php echo $ajax_url;?>"  
@after-submit="afterRejectionFormsSubmit"
@after-update="afterUpdateStatus" 
:order_uuid="order_uuid"
:label="<?php echo CommonUtility::safeJsonEncode([
  'title'=>t("Enter why you cannot make this order."),
  'reject_order'=>t("Reject order"),
  'reason'=>t("Reason")
])?>"
>
</components-rejection-forms>		

<components-refund-forms
ref="refund"
:order_uuid="order_uuid"
:label="<?php echo CommonUtility::safeJsonEncode([
  'title'=>t("Refund payment"),
  'refund'=>t("Refund"),
  'cancel'=>t("Cancel"),
  'refund_full'=>t("Refund the full amount")
])?>"
>
</components-refund-forms>

<components-preparationtime
ref="ref_preparation_time"
:data="order_info.preparation_time_estimation_raw"
:order_uuid="order_uuid"
ajax_url="<?php echo $ajax_url;?>"  
@after-updatepreptime="afterUpdatepreptime"
>  
</components-preparationtime>

<div v-if="is_loading" class="loading cover-loader d-flex align-items-center justify-content-center">
    <div>
      <div class="m-auto circle-loader medium" data-loader="circle-side"></div> 
    </div>
</div>

<template v-if="response_code==2">

   <div class="fixed-height text-center justify-content-center d-flex align-items-center">
     <div class="flex-col">
     <img class="img-300" src="<?php echo Yii::app()->theme->baseUrl?>/assets/images/order-best-food@2x.png" />
      <h5 class="mt-3"><?php echo t("Order Details will show here")?></h5>
     </div>     
   </div>

</template>

<template v-else>

<div class="card" v-cloak v-if="!loading" >
  <div class="card-body" >
  
   <div class="row align-items-start">
      <div class="col" >
     
     <button v-for="button in buttons" :class="button.class_name" 
        @click="doUpdateOrderStatus(button.uuid,order_info.order_uuid,button.do_actions)"        
        class="btn normal mr-2 font13  mb-3 mb-xl-0">
           <span>{{button.button_name}}</span>
           <div class="m-auto circle-loader" data-loader="circle-side"></div> 
      </button>                             
      <button v-if="manual_status=='1'" class="btn btn-yellow normal mr-2" @click="manualStatusList"><?php echo t("Manual Status")?></button>   
                       
      </div> <!-- flex-col -->
    
      <div class="col" >
        <div class="d-flex justify-content-end">
          
          <div v-if="showSendtokicthen" class="flex-col mr-3">            
              <button class="btn btn-warning normal" style="padding:.375rem .75rem;"
              :disabled="found_in_kitchen"
              @click="SendToKitchen"
               ><?php echo t("Send Kitchen")?></button>
          </div>          
          
          <div class="flex-col mr-3">
             <?php $printer_list = isset($printer_list)?$printer_list:''; ?>
             <?php if(is_array($printer_list) && count($printer_list)>=1):?>
              <div class="dropdown dropleft">
              <a class="rounded-pill rounded-button-icon d-inline-block bg-dark" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="zmdi zmdi-print" style="color:#fff;"></i>
              </a>
              <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                  <a class="dropdown-item" href="javascript:;" @click="printOrder"><?php echo t("Web print")?></a>
                  <?php foreach ($printer_list as $printers):?>                    
                  <a class="dropdown-item" href="javascript:;" @click="SwitchPrinter(<?php echo $printers['printer_id']?>,'<?php echo isset($printers['printer_model'])?$printers['printer_model']:'' ?>')" ><?php echo $printers['printer_name']?></a>
                  <?php endforeach;?>
              </div>              
              </div>
             <?php else : ?>
              <button class="btn btn-black normal" @click="printOrder" ><?php echo t("Print")?></button>
             <?php endif;?>             
                                    

          </div>          
          <div class="flex-col">                    
                            
           <div class="dropdown dropleft">
            <a class="rounded-pill rounded-button-icon d-inline-block" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="zmdi zmdi-more"></i>
            </a>
			
            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">                            
              <template v-if="enabled_delay_order && modify_order">                
                <a class="dropdown-item" href="javascript:;" @click="delayOrder" ><?php echo t("Delay Order")?></a>
              </template>
              <a v-if="modify_order" class="dropdown-item" href="javascript:;" @click="cancelOrder" ><?php echo t("Cancel order")?></a>
              <a class="dropdown-item" href="javascript:;" @click="orderHistory" ><?php echo t("Timeline")?></a>
              <a class="dropdown-item" target="_blank" :href="link_pdf.pdf_a4" ><?php echo t("Download PDF (A4)")?></a>	
              <?php if($view_admin):?>
              <a @click="this.$emit('showManageorder')" class="dropdown-item" :disabled="!canManageOrder" ><?php echo t("Manage Order")?></a>	
              <a @click="this.$emit('showIssuerefund')" class="dropdown-item" :disabled="!canIssueRefund" ><?php echo t("Issue Refund")?></a>	
              <a @click="this.$emit('deleteOrderconfirm')" class="dropdown-item" :disabled="!canDeleteOrder" ><?php echo t("Delete Order")?></a>	
              <?php endif;?>		    
            </div>
          </div>
          
          </div> <!--flex-col-->
        </div><!--flex--> 
      
      </div>
   </div> <!--flex-->
  
   
   <div class="row mt-3"> 
     <div class="col-md-5">
     
     <div class="d-flex">
        <div class="mr-2">
        <img class="img-20" src="<?php echo Yii::app()->theme->baseUrl?>/assets/images/orders-icon.png" >
        </div>
        <div>
          <div class="d-flex align-items-center">
           <div class=""><h5 class="m-0"><?php echo t("Order #")?>{{order_info.order_id}}</h5></div>
           <div class="ml-3">
             <h6 v-if="order_status[order_info.status]" 
             :style="{background:order_status[order_info.status].background_color_hex,color:order_status[order_info.status].font_color_hex}"
             class="font13 m-0 badge">
             <template v-if="order_status[order_info.status]">
             {{order_status[order_info.status].status}}
             </template>
             <template v-else>
             {{order_info.status}}             
             </template>
            </h6>
           </div>
          </div> <!-- flex -->
          <p class="m-0">{{order_info.place_on}}</p>          
        </div>
      </div> <!--flex-->

      <?php if($view_admin):?>
      <template v-if="order_info.base_currency_code!=order_info.admin_base_currency">
      <div class="d-flex mt-3">  
         <div class="mr-2">
            <i class="zmdi zmdi-balance-wallet" style="font-size:18px;"></i>
        </div>
        <div>            
            <h5 class="m-0"><?php echo t("Exchange rate info")?> :</h5> 
            <p class="m-0">Order currency: <i>{{order_info.base_currency_code}}</i></p>   
            <p class="m-0">Base currency: <i>{{order_info.admin_base_currency}}</i></p>   
            <p class="m-0">Exchange rate: {{order_info.exchange_rate_merchant_to_admin}}</p>   
            <p class="m-0">Total amount: {{order_info.total_from_merchant_to_admin_currency_pretty}}</p>   
        </div>
      </div> <!--flex-->
      </template>
      <?php else :?>
        <template v-if="order_info.use_currency_code!=order_info.base_currency_code">
        <div class="d-flex mt-3">  
          <div class="mr-2">
              <i class="zmdi zmdi-balance-wallet" style="font-size:18px;"></i>
          </div>
          <div>             
              <h5 class="m-0"><?php echo t("Exchange rate info")?> :</h5> 
              <p class="m-0">Order currency: <i>{{order_info.use_currency_code}}</i></p>   
              <p class="m-0">Base currency: <i>{{order_info.base_currency_code}}</i></p>   
              <p class="m-0">Exchange rate: {{order_info.exchange_rate}}</p>   
              <p class="m-0">Total amount: {{order_info.total_from_used_currency_to_based_currency_pretty}}</p>   
          </div>
        </div> <!--flex-->
        </template>
      <?php endif;?>
      
          
      <?php if($view_admin):?>
      <div class="d-flex mt-3">
        <div class="mr-2">
        <img class="img-20 rounded-circle"  :src="merchant.url_logo">
        </div>
        <div>
          <h5 class="m-0"><?php echo t("Restaurant")?> :</h5>          
          <p class="m-0">{{merchant.restaurant_name}}</p>
          <p class="m-0">{{merchant.contact_phone}}</p>          
          <p class="m-0">{{merchant.contact_email}}</p>
          <p class="m-0">{{merchant.merchant_address}}</p>
          <p class="m-0" v-if="merchant_zone[merchant.merchant_id]" >                        
              <b><?php echo t("Zone")?></b> : 
              <template v-for="zone in merchant_zone[merchant.merchant_id]">
                {{zone_list[zone]}},
              </template>            
          </p>
          <div class="d-flex">
           <div><a @click="$emit('viewMerchantTransaction')" class="link">{{merchant.order_count}} <?php echo t("Orders")?></a></div>
           <div class="text-green ml-2 mr-2">|</div>
           <div><p class="m-0"><a :href="merchant.restaurant_direction" target="_blank" class="a-12"><u><?php echo t("Get direction")?></u></a></p></div>
          </div>
        </div>
      </div> <!--flex-->
      <?php endif?>
      
      <div class="d-flex mt-3">
        <div class="mr-2">
        <img class="img-20 rounded-circle"  :src="customer.avatar">
        </div>
        <div>
          <h5 class="m-0"><?php echo t("Customer")?> :</h5>          
          <template v-if="customer.first_name">
            <p class="m-0">{{customer.first_name}} {{customer.last_name}}</p>
            <p class="m-0">{{customer.contact_phone}}</p>          
            <p class="m-0">{{customer.email_address}}</p>
            <a @click="showCustomer" class="link">{{customer.order_count}} <?php echo t("Orders")?></a>
          </template>
          <template v-else>            
            <p class="m-0">{{order_info.customer_name}} </p>
          </template>
        </div>
      </div> <!--flex-->
      
      <div v-if="hasBooking" class="d-flex mt-3">
         <div class="mr-4">         
        </div>
        <div>
           <h5 class="m-0"><?php echo t("Table information")?> :</h5>
           <p class="m-0"><?php echo t("Guest")?> : {{order_table_data.guest_number}}</p>
           <p class="m-0"><?php echo t("Room name")?> : {{order_table_data.room_name}}</p>
           <p class="m-0"><?php echo t("Table name")?> : {{order_table_data.table_name}}</p>
        </div>
      </div>

      <div v-if="order_info.points_to_earn>0" class="d-flex mt-3">
        <div class="mr-2">
          <img class="img-20" src="<?php echo Yii::app()->theme->baseUrl?>/assets/images/gift.png">
        </div>
        <div>
           <h5 class="m-0"><?php echo t("Loyalty Points")?> :</h5>
           <p class="m-0">{{order_info.points_label2}}</p>
        </div>
      </div>
            
      <div v-if="order_info.service_code=='delivery'" class="d-flex mt-3">
        <div class="mr-2">
        <img class="img-20" src="<?php echo Yii::app()->theme->baseUrl?>/assets/images/location.png">
        </div>
        <div>                    
          <h5 class="m-0"><?php echo t("Delivery information")?> :</h5>
          <p class="m-0">{{order_info.customer_name}}</p>
          <p class="m-0">{{order_info.contact_number}}</p>          
          <p class="m-0">                      
          <a v-if="modify_order" @click="editOrderInformation" class="link"><i class="zmdi zmdi-edit"></i> <?php echo t("Edit")?></a>
          <b v-if="order_info.address_label" :class="{ 'ml-1' : modify_order  }">{{order_info.address_label}}:</b> {{order_info.complete_delivery_address}}          
          </p>
          <p v-if="order_info?.order_version==1" class="m-0">&bull; {{order_info?.address1}} {{order_info?.delivery_address}}</p>
          <template v-if="order_info.address_format_use!=2">
            <p class="m-0" v-if="order_info.location_name"><b><?php echo t("Aparment, suite or floor")?>:</b> {{order_info.location_name}}</p>
          </template>          
          <p class="m-0" v-if="order_info.delivery_options"><b><?php echo t("Delivery options")?>:</b> {{order_info.delivery_options}}</p>
          <p class="m-0" v-if="order_info.delivery_instructions"><b><?php echo t("Delivery instructions")?>:</b> {{order_info.delivery_instructions}}</p>

          <!-- <p class="m-0" v-if="order_info.custom_field1"><b><?php echo t("Bonito")?>:</b> {{order_info.custom_field1}}</p>
          <p class="m-0" v-if="order_info.custom_field2"><b><?php echo t("Caliente")?>:</b> {{order_info.custom_field2}}</p> -->

          <p class="m-0"><a :href="delivery_direction" target="_blank" class="a-12"><u><?php echo t("Get direction")?></u></a></p>          
        </div>
      </div> <!--flex-->
      
      <div v-if="order_info.show_assign_driver" class="d-flex mt-3">
        <div class="mr-2">           
           <i v-if="order_info.driver_id<=0" class="zmdi zmdi-car" style="font-size:18px;"></i>
           <img v-else class="img-20" :src="driver_data.photo_url">
        </div>
        <div>
          <h5 v-if="order_info.driver_id<=0" class="m-0 pb-2"><?php echo t("Assign Driver")?> :</h5>   
          <h5 v-else class="m-0 pb-2"><?php echo t("Delivery man")?> :</h5>   
          <template v-if="order_info.driver_id<=0">
              <template v-if="order_info.can_reassign_driver">
               <button @click="$emit('showAssigndriver')" type="button" class="btn btn-primary"><?php echo t("Assign Driver")?></button>  
              </template>
              <template v-else>
                <p class="text-muted"><?php echo t("No assign driver to this order")?></p>
              </template>
          </template>       
          <template v-else>
              <p v-if="driver_data.active_task>0" class="m-0"><b>{{driver_data.active_task}}</b> <span class="text-grey">active orders</span></p>
              <a :href="driver_data.url" target="blank"><p class="m-0">{{driver_data.driver_name}}</p></a>
              <p class="m-0">{{driver_data.phone_number}}</p>
              <p class="m-0">{{driver_data.email_address}}</p>
              <template v-if="order_info.can_reassign_driver">
              <p class="m-0"><a class="link"  @click="$emit('showAssigndriver')" ><u><?php echo t("Regassign Driver")?></u></a></p>          
              </template>
          </template>
        </div>
      </div> <!--flex-->
      
                 
      <table class="table table-bordered mt-4">
      <tr>
       <td><?php echo t("Order type")?></td>
       <td>       
        <span v-if="services[order_info.service_code]" class="badge services" 
        :style="{background:services[order_info.service_code].background_color_hex,color:services[order_info.service_code].font_color_hex}"
        >
          {{services[order_info.service_code].service_name}}
        </span>
       </td>
      </tr>
      <tr v-if="order_info.order_reference">
        <td><?php echo t("KOT Reference")?></td>
        <td>{{ order_info.order_reference }}</td>
      </tr>
      <tr>
       <td><?php echo t("Delivery Date/Time")?></td>
       <td>       
       <p v-if="order_info.whento_deliver=='now'" class="m-0 text-muted">{{order_info.schedule_at}}</p>
       <p v-if="order_info.whento_deliver=='schedule'" class="m-0 text-muted">{{order_info.schedule_at}}</p>
       </td>
      </tr>
      <tr>
       <td><?php echo t("Include utensils")?></td>
       <td>         
         <p class="m-0" v-if="order_info.include_utensils==1" ><?php echo t("Yes")?></p>         
       </td>
      </tr>            
      <tr>
       <td><?php echo t("Payment")?></td>
       <td>{{order_info.payment_name}}</td>
      </tr>
      <tr v-if="order_info.receive_amount>0">
       <td><?php echo t("Receive amount")?></td>
       <td>         
         <p class="m-0" >{{order_info.receive_amount_pretty}}</p>         
       </td>
      </tr>      
      <tr v-if="order_info.payment_change>0">
       <td><?php echo t("Payment change")?></td>
       <td>         
         <p class="m-0" >{{order_info.payment_change_pretty}}</p>         
       </td>
      </tr>      
      <tr>
       <td><?php echo t("Payment status")?></td>
       <td>        
       <p class="m-0" v-if="payment_status[order_info.payment_status]">        
        <span
        class="badge"
        :style="{background:payment_status[order_info.payment_status].color_hex,color:payment_status[order_info.payment_status].font_color_hex}"
        >{{payment_status[order_info.payment_status].title}}</span>
       </p>
       <p v-else>
        {{order_info.payment_status}}
       </p>
       </td>
      </tr>                

      <tr  v-if="order_info.show_assign_driver" >
       <td><?php echo t("Delivery status")?></td>
       <td>        
       <p class="m-0">
        <template v-if="delivery_status[order_info.delivery_status]">
          {{delivery_status[order_info.delivery_status].status}}
        </template>
        <template v-else>
           {{order_info.delivery_status}}
        </template>
       </p>
       </td>
      </tr>                


      </table>
     
     </div> <!--col-->
     <div class="col-md-7">
     
     
     <div class="card border">
       <div class="card-body pt-3">
       
        <div class="d-flex mb-4 justify-content-between align-items-centerx">
           <div>
            <h5><?php echo t("Summary")?></h5>
            <a v-if="modify_order" class="btn btn-green small" href="javascript:;" @click="$emit('showMenu')"
            :class="{disabled : hasInvoiceUnpaid}"
            >
            <i class="zmdi zmdi-plus mr-2"></i><?php echo t("Add")?>
            </a>
          </div>

          
          <!-- <pre>{{order_info}}</pre> -->

          <div class="text-center" v-if="order_info.is_timepreparation">
            <h6 class="m-0">
                <template v-if="order_info.preparation_starts && order_info.order_accepted_at">
                <?php echo t("Preparation will start at")?>
                </template>
                <template v-else>
                <?php echo t("Preparation Estimate")?>
                </template>                
            </h6>
            <h4 class="m-0 font-weight-bold">              
              <template v-if="order_info.order_accepted_at_raw">
                <components-prepcountdown
                :order_accepted_at="order_info.order_accepted_at"
                :preparation_starts="order_info.preparation_starts"
                :timezone="order_info.timezone"
                :label="{
                    hour:'<?php echo CJavaScript::quote(t("hour"))?>',                      
                    hours:'<?php echo CJavaScript::quote(t("hours"))?>',
                    min:'<?php echo CJavaScript::quote(t("min"))?>',
                    mins:'<?php echo CJavaScript::quote(t("mins"))?>',
                    order_overdue:'<?php echo CJavaScript::quote(t("Order is Overdue!"))?>',                    
                }"
                >
                </components-prepcountdown>
              </template>
              <template v-else>
                {{ order_info.preparation_time_estimation }}
              </template>              
            </h4>
            <div class="text-success font11 mb-1">
              <template  v-if="order_info.order_accepted_at_raw">
                 <?php echo t("Remaining")?>
              </template>
              <template v-else>
                 <?php echo t("Suggested")?>
              </template>              
            </div>            
            <el-button type="success" size="small" plain round @click="editPrepationtime(order_info.preparation_time_estimation_raw)" >
              <i class="zmdi zmdi-edit mr-1"></i> <?php echo t("Edit")?>
            </el-button>
          </div>
        </div> 
        <!-- d-flex -->
         
        <!-- ITEMS  -->        
        <template v-for="(items, index) in items" >
        <div class="row" >
        
         <div class="col-2 d-flex justify-content-center">
           <img class="rounded img-40" :src="items.url_image" >
         </div>
         
         <div class="col-6 d-flex justify-content-start flex-column">
                  
         <p class="mb-1">
         {{items.qty}}x
         {{ items.item_name }}
          <template v-if=" items.price.size_name!='' "> 
          ({{items.price.size_name}})
          </template>          
          
           <template v-if="items.item_changes=='replacement'">
           <div class="m-0 text-muted small">
            Replace "{{items.item_name_replace}}"
           </div>
           <div class="badge badge-success small"><?php echo t("Replacement")?></div>
           </template>
         </p> 

         <!-- KITCHEN STATUS -->
         
         <template v-if="kitchen_addon">
          <template v-if="items.item_status">
          <div>
            <span class="badge font11" :class="items.item_status_class" >
              {{ items.item_status }}
            </span>
          </div>                  
          </template>
         </template>
         
         <template v-if="items.is_free" >
         <p>           
           <el-tag effect="plain" type="success" size="small" style="white-space: nowrap !important;" >
            <?php echo CommonUtility::safeTranslate("Free")?>
           </el-tag>
         </p>
         </template>
         <template v-else>
            <template v-if="items.price.discount>0">         
              <p class="m-0 font11"><del>{{items.price.pretty_price}}</del> {{items.price.pretty_price_after_discount}}</p>
            </template>
            <template v-else>
              <p class="m-0 font11">{{items.price.pretty_price}}</p>
            </template>
         </template>
         
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
        
        <template v-if="modify_order">
        <p class="m-0"><b><?php echo t("If sold out")?></b></p>
        <p class="m-0 text-danger" v-if="sold_out_options[items.if_sold_out]">
        {{sold_out_options[items.if_sold_out]}}        
        </p>
        </template>
                
         </div> <!-- col -->        
         
         <div class="col-3 d-flex justify-content-start flex-column text-right">
           <template v-if="items.price.discount<=0 ">
	          {{ items.price.pretty_total }}
	        </template>
	        <template v-else>
	           {{ items.price.pretty_total_after_discount }}
	        </template>	        
         </div> <!-- col -->
         
         <div  v-if="modify_order"  class="col-1">         
           <div class="dropdown dropleft">
			  <a class="more-vert rounded-pill d-inline-block" href="#" role="button" id="dropdownMenuLink" 
			  data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
			  :class="{disabled : hasInvoiceUnpaid}"
			  >
			    <i class="zmdi zmdi-more-vert"></i>
			  </a>
			
			  <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
			    <a class="dropdown-item"  @click="markItemOutStock(items)" ><?php echo t("Mark item as Out of Stock")?></a>
			    <a class="dropdown-item"  @click="adjustOrder(items)"><?php echo t("Adjust Item")?></a>
			    <a class="dropdown-item"  @click="additionalCharge(items)"><?php echo t("Add an additonal charge")?></a>			    
			  </div>
		  </div>
         
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
             <div class="col-6 d-flex justify-content-start flex-column">{{ summary.name }}</div>
             <div class="col-3 d-flex justify-content-start flex-column text-right">{{ summary.value }}</div>
           </div>
         </template>
       </template>
                                   
                               
       <template v-if="hasTotalDecrease">       
        <hr/>
       <div class="row mb-1">
         <div class="col-2 d-flex justify-content-center"></div>
         <div class="col-6 d-flex justify-content-start flex-column"><b><?php echo t("Paid by customer")?></b></div>
         <div class="col-3 d-flex justify-content-start flex-column text-right"><b>{{ summary_changes.total_paid }}</b></div>
       </div>   
       
        <div v-for="refund in summary_changes.refund_list" class="row mb-1">
         <div class="col-2 d-flex justify-content-center"></div>
         <div class="col-6 d-flex justify-content-start flex-column"><b>{{refund.transaction_description}}</b></div>
         <div class="col-3 d-flex justify-content-start flex-column text-right"><b>({{ refund.transaction_amount }})</b></div>
       </div>  
       
       <div v-if="summary_changes.refund_due>0" class="row mb-1">
         <div class="col-2 d-flex justify-content-center"></div>
         <div class="col-6 d-flex justify-content-start flex-column"><b><?php echo t("Refund Due")?></b></div>
         <div class="col-3 d-flex justify-content-start flex-column text-right"><b>{{ summary_changes.refund_due_pretty }}</b></div>
       </div>
       
        <div v-else class="row mb-1">
         <div class="col-2 d-flex justify-content-center"></div>
         <div class="col-6 d-flex justify-content-start flex-column"><b><?php echo t("Net payment")?></b></div>
         <div class="col-3 d-flex justify-content-start flex-column text-right"><b>{{ summary_changes.net_payment }}</b></div>
        </div>      
       
       </template>
       
       <template v-else-if="hasTotalIncrease">       
        <hr/>
                
       <div class="row mb-1">
         <div class="col-2 d-flex justify-content-center"></div>
         <div class="col-6 d-flex justify-content-start flex-column"><b><?php echo t("Paid by customer")?></b></div>
         <div class="col-3 d-flex justify-content-start flex-column text-right"><b>{{ summary_changes.total_paid }}</b></div>
       </div>   
       
       
       <div v-for="refund in summary_changes.refund_list" class="row mb-1">
         <div class="col-2 d-flex justify-content-center"></div>
         <div class="col-6 d-flex justify-content-start flex-column"><b>{{refund.transaction_description}}</b></div>
         <div class="col-3 d-flex justify-content-start flex-column text-right"><b>({{refund.transaction_amount}})</b></div>
       </div>                
       
       <div class="row mb-1">
         <div class="col-2 d-flex justify-content-center"></div>
         <div class="col-6 d-flex justify-content-start flex-column"><b><?php echo t("Amount to collect")?></b></div>
         <div class="col-3 d-flex justify-content-start flex-column text-right"><b>{{ summary_changes.refund_due_pretty }}</b></div>
       </div>
       
       </template>
              
            
       <template v-if="summaryTransaction">   
       <hr/>
       <div class="row mb-1">
         <div class="col-2 d-flex justify-content-center"></div>
         <div class="col-6 d-flex justify-content-start flex-column"><?php echo t("Paid by customer")?></div>
         <div class="col-3 d-flex justify-content-start flex-column text-right">{{ summary_transaction.total_paid }}</div>
       </div>   
       
       <div v-for="sumlist in summary_transaction.summary_list" class="row mb-1">
         <div class="col-2 d-flex justify-content-center"></div>
         <div class="col-6 d-flex justify-content-start flex-column">{{sumlist.transaction_description}}</div>
         <div class="col-3 d-flex justify-content-start flex-column text-right">
           <template v-if="sumlist.transaction_type=='debit'">
             ({{sumlist.transaction_amount}})          
           </template>
           <template v-else>
             {{sumlist.transaction_amount}}
           </template>
         </div>
       </div>  
       
        <div v-else class="row mb-1">
         <div class="col-2 d-flex justify-content-center"></div>
         <div class="col-6 d-flex justify-content-start flex-column"><b><?php echo t("Net payment")?></b></div>
         <div class="col-3 d-flex justify-content-start flex-column text-right"><b>{{ summary_transaction.net_payment }}</b></div>
        </div>    
       
       </template>
       
       
       
       </div> <!--body-->
     </div><!-- card-->
  
     <!--<div class="mt-3">Time line here</div> -->
     
     </div> <!--col-->
   </div> <!--row-->

             
     <h6 class="font13 mt-1"><?php echo t("Payment history")?></h6>
     <div class="table-responsive-md">
     <table class="table table-bordered">
      <tr>
       <th width="15%"><?php echo t("Date")?></th>
       <th width="15%"><?php echo t("Payment")?></th>
       <th width="25%"><?php echo t("Description")?></th>
       <th width="15%"><?php echo t("Amount")?></th>
       <th width="15%"><?php echo t("Status")?></th>       
      </tr>
      <tr v-for="payment in payment_history">
        <td>{{payment.date_created}}</td>
        <td>{{payment.payment_code}}</td>
        <td>
        {{payment.transaction_description}} 
        <p v-if="payment.payment_reference" class="text-muted"><i><small><?php echo t("Reference")?># {{payment.payment_reference}}</small></i></p> 
        </td>
        <td>        
          <template v-if="payment.transaction_type==='debit'">
            <b>({{payment.trans_amount}})</b>
          </template>          
          <template v-else>
            {{payment.trans_amount}}
          </template>
        </td>
        <td>
          <span class="badge payment" :class="payment.status">
            <template v-if="payment_status[payment.status]">
              {{payment_status[payment.status].title}}
            </template>          
            <template v-else>
            {{payment.status}}
            </template>
          </span>          
          <p v-if="payment.reason" class="text-muted"><i><small>{{payment.reason}}</small></i></p> 
        </td>        
      </tr>
     </table>
      </div>

      
     <template v-if="order_info.payment_code=='ocr' && credit_card_details">
     <h6 class="font13 mt-1"><?php echo t("Credit card details")?></h6>
     <div class="table-responsive-md">      
      <table class="table table-bordered">
        <tr>        
        <th width="15%"><?php echo t("Name")?></th>
        <th width="15%"><?php echo t("Card number")?></th>
        <th width="15%"><?php echo t("Expiry")?></th>
        <th width="15%"><?php echo t("CVV")?></th>
       </tr>
       <tbody>
        <tr>
          <td>{{credit_card_details.card_number}}</td>
          <td>{{credit_card_details.card_name}}</td>
          <td>{{credit_card_details.expiration_month}}/{{credit_card_details.expiration_yr}}</td>
          <td>{{credit_card_details.cvv}}</td>
        </tr>
       </tbody>
      </table>
     </div>
     </template>


  </div> <!--body-->
</div> <!--card-->  


<div ref="manual_status_modal" class="modal" tabindex="-1" role="dialog" >
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><?php echo t("Select Order Status")?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
    
     
      <div class="list-group list-group-flush">
         <a v-for="item in status_data" 
         @click="stats_id=item.stats_id"
         :class="{ active: stats_id==item.stats_id }"
         class="text-center list-group-item list-group-item-action">
         {{item.description}}
         </a>
      </div>
      
      </div>      
      <div class="modal-footer">            
        <button type="button" @click="confirm" class="btn btn-green pl-4 pr-4" :class="{ loading: is_loading }"         
         :disabled="!hasData"
         >
          <span><?php echo t("Confirm")?></span>
          <div class="m-auto circle-loader" data-loader="circle-side"></div> 
        </button>
      </div>
      
    </div>
  </div>
</div>  
<!-- manual_status_modal -->


<div ref="out_stock_modal" class="modal" tabindex="-1" role="dialog" >
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><?php echo t("Item is Out of Stock")?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
            
      <h5 class="mb-1">{{item_row.item_name}}</h5>
      <h6><?php echo t("is out of stock")?></h6>      
            
      <ul class="list-group list-group-flush">
         <li class="list-group-item">
            <div class="custom-control custom-radio">
			  <input v-model="out_stock_options" 
			  type="radio" id="out_stock_1" name="out_stock_options" class="custom-control-input" value="1">
			  <label class="custom-control-label" for="out_stock_1">Until end of the day</label>
			</div>
         </li>
         
         <li class="list-group-item">
            <div class="custom-control custom-radio">
			  <input v-model="out_stock_options" 
			  type="radio" id="out_stock_2" name="out_stock_options" class="custom-control-input" value="2">
			  <label class="custom-control-label" for="out_stock_2"><?php echo t("Until end of the day tomorrow")?></label>
			</div>
         </li>
         
      </ul>

      </div>      
      <div class="modal-footer">            
        <button type="button" @click="setOutOfStocks" class="btn btn-green pl-4 pr-4" :class="{ loading: is_loading }"         
         :disabled="!outStockOptions"
         >
          <span><?php echo t("Confirm")?></span>
          <div class="m-auto circle-loader" data-loader="circle-side"></div> 
        </button>
      </div>
      
    </div>
  </div>
</div>  
<!-- out_stock_modal -->
      

<div ref="adjust_order_modal" class="modal adjust_order_modal" tabindex="-1" role="dialog" >
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><?php echo t("Adjust Order")?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body bg-light p-0 m-0">
      
      <div class="p-2 pl-3 pr-3">
        <h5>{{order_info.customer_name}} <?php echo t("said")?> :</h5>
        
        <template v-if="item_row.if_sold_out=='substitute'">        
        <p><?php echo t("Go with merchant recommendation")?></p>
        </template>
        
        <template v-else-if="item_row.if_sold_out=='refund'">
        <p><?php echo t("Refund this item")?></p>
        </template>
        
        <template v-else-if="item_row.if_sold_out=='contact'">
        <h4>{{order_info.contact_number}}</h4>
        <p><?php echo t("Call the customer, ask them if they like to replace")?><br/>
        <?php echo t("the item, refund the item, or cancel the entire order.")?></p>
        </template>
        
        <template v-else-if="item_row.if_sold_out=='cancel'">
        <p><?php echo t("Cancel the entire order")?></p>
        </template>
        
      </div>
                        
      <div class="bg-white p-2 pl-3 pr-3">
        <div class="d-flex justify-content-between">
          <div class="flex-col"><h6>{{item_row.item_name}}</h6></div>
          <div class="flex-col">

          <template v-if="item_row.price">
          {{item_row.price.pretty_total_after_discount}}
          </template>
           
          </div>
        </div>
      </div>
      
      </div>      
      <div class="modal-footer">            
            
        <template v-if="order_info.payment_status=='paid'">
        <button type="button"         
        @click="refundItem" class="btn btn-green pl-4 pr-4" :class="{ loading: is_loading }"           
         >
          <span><?php echo t("Remove Item")?></span>
          <div class="m-auto circle-loader" data-loader="circle-side"></div> 
        </button>
        </template>
        <template v-else>
         <button type="button"         
        @click="removeItem" class="btn btn-green pl-4 pr-4" :class="{ loading: is_loading }"           
         >
          <span><?php echo t("Remove Item")?></span>
          <div class="m-auto circle-loader" data-loader="circle-side"></div> 
        </button>
        </template>
        
        <button type="button" @click="replaceItem" class="btn btn-black pl-4 pr-4" :class="{ loading: is_loading }"           
         >
          <span><?php echo t("Replace")?></span>
          <div class="m-auto circle-loader" data-loader="circle-side"></div> 
        </button>
        
        <button type="button" @click="cancelEntireOrder" class="btn btn-yellow pl-4 pr-4"   
         >
          <span><?php echo t("Cancel the entire order")?></span>
          <div class="m-auto circle-loader" data-loader="circle-side"></div> 
        </button>
        
      </div>
      
    </div>
  </div>
</div>  
<!-- adjust_order_modal -->      


<div ref="additional_charge_modal" class="modal" tabindex="-1" role="dialog" >
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><?php echo t("Add an additional charge")?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

       <div class="form-label-group">    
        <input 
        v-model="additional_charge"  v-maska="'#############'" 
        class="form-control form-control-text" placeholder="" id="charge" type="text"  maxlength="14">   
        <label for="charge" class="required"><?php echo t("Charge amount")?></label> 
       </div>    
       
       <div class="form-label-group">    
        <input 
        v-model="additional_charge_name" 
        class="form-control form-control-text" placeholder="" id="additional_charge_name" type="text"  maxlength="14">   
        <label for="additional_charge_name" class="required"><?php echo t("Reason for additional charge (optional)")?></label> 
       </div>    
       
      </div>      
      <div class="modal-footer">   
      
        <button class="btn btn-black pl-4 pr-4" data-dismiss="modal" >
        <?php echo t("Cancel")?>
        </button>
             
        <button type="button" @click="doAdditionalCharge" 
        class="btn btn-green pl-4 pr-4" :class="{ loading: is_loading }"         
         :disabled="!hasValidCharge"
         >
          <span><?php echo t("Add Charge")?></span>
          <div class="m-auto circle-loader" data-loader="circle-side"></div> 
        </button>
      </div>
      
    </div>
  </div>
</div>  
<!-- additional_charge_modal -->



<div ref="update_info_modal" class="modal" tabindex="-1" role="dialog" >
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><?php echo t("Update Delivery Information")?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      
      <form @submit.prevent="updateOrderDeliveryInformation" >
      <div class="modal-body">
      
            
         <div class="form-label-group">    
          <input v-model="customer_name" type="text" id="customer_name" placeholder="" class="form-control form-control-text">
          <label for="customer_name"><?php echo t("Customer name")?></label>
         </div>    
         
         <div class="form-label-group">    
          <input v-model="contact_number"  v-maska="'############'"
          type="text" id="contact_number" placeholder="" class="form-control form-control-text">
          <label for="contact_number"><?php echo t("Contact number")?></label>
         </div>    

         <template v-if="address_format_use==2">

            <div class="row">
              <div class="col">
              <div class="form-label-group">    
                  <input v-model="delivery_address" type="text" id="delivery_address" placeholder="" class="form-control form-control-text">
                  <label for="delivery_address"><?php echo t("Street name")?></label>
                </div>    
              </div>
              <div class="col">
              <div class="form-label-group">    
                  <input v-model="address1" type="text" id="address1" placeholder="" class="form-control form-control-text">
                  <label for="address1"><?php echo t("Street number")?></label>
                </div>                    
              </div>
            </div>

            <div class="row">
              <div class="col">
                <div class="form-label-group">    
                  <input v-model="location_name" type="text" id="location_name" placeholder="" class="form-control form-control-text">
                  <label for="location_name"><?php echo t("Entrance")?></label>
                </div>  
              </div>
              <div class="col">
                <div class="form-label-group">    
                  <input v-model="address2" type="text" id="address2" placeholder="" class="form-control form-control-text">
                  <label for="address2"><?php echo t("Floor")?></label>
                </div>  
              </div>
            </div>

            <div class="row">
              <div class="col">
                <div class="form-label-group">    
                  <input v-model="postal_code" type="text" id="postal_code" placeholder="" class="form-control form-control-text">
                  <label for="postal_code"><?php echo t("Door")?></label>
                </div>   
              </div>
              <div class="col">
                <div class="form-label-group">    
                  <input v-model="company" type="text" id="company" placeholder="" class="form-control form-control-text">
                  <label for="company"><?php echo t("Company")?></label>
                </div>    
              </div>
            </div>                                                              
         </template>
         <template v-else>         
            <div class="form-label-group">    
              <input v-model="delivery_address" type="text" id="delivery_address" placeholder="" class="form-control form-control-text">
              <label for="delivery_address"><?php echo t("Street name")?></label>
            </div>    

            <div class="form-label-group">    
              <input v-model="address1" type="text" id="address1" placeholder="" class="form-control form-control-text">
              <label for="address1"><?php echo t("Street number")?></label>
            </div>    

         </template>
         
         <div class="row">
           <div class="col">
	         <div class="form-label-group">    
	          <input v-model="latitude" type="text" id="latitude" placeholder="" class="form-control form-control-text">
	          <label for="latitude"><?php echo t("Latitude")?></label>
	         </div>    
           </div>
           <div class="col">
              <div class="form-label-group">    
	          <input v-model="longitude" type="text" id="longitude" placeholder="" class="form-control form-control-text">
	          <label for="longitude"><?php echo t("Longitude")?></label>
	          </div>
           </div>
         </div>
         
         
         <div  v-cloak v-if="error.length>0" class="alert alert-warning mb-2" role="alert">
		    <p v-cloak v-for="err in error" class="m-0">{{err}}</p>	    
		 </div>   
      
       </div>      
      <div class="modal-footer">   

         <button type="button" class="btn btn-black pl-4 pr-4" data-dismiss="modal" >         
         <?php echo t("Cancel")?>
        </button>
             
        <button type="submit" 
        class="btn btn-green pl-4 pr-4" :class="{ loading: is_loading }"                  
         >
          <span><?php echo t("Save Changes")?></span>
          <div class="m-auto circle-loader" data-loader="circle-side"></div> 
        </button>     
      
      </div>
       </form>
      
    </div>
  </div>
</div>  
<!-- additional_charge_modal -->      
            

</template>

</script>