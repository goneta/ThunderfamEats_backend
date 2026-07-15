<DIV id="app-pos">
  <q-layout view="hHh lpR fFf"  v-cloak >

   <q-drawer v-model="drawer" side="right" 
   show-if-above
   :width="350"
   :breakpoint="800"
   bordered
   >
   <div class="q-pa-md">

      <template v-if="order_details_loading">
         <q-inner-loading  
         :showing="true" 
         color="primary"
         ></q-inner-loading>
      </template>
            
      <template v-if="hasOrderDetails">
      <div class="row items-center justify-between">
          <div class="col">
            <div class="text-h6">
               {{ getOrderInfo.order_type1 }}
            </div>
          </div>
          <div class="col-5 text-grey-7 text-right">
             <div>{{ getOrderInfo.order_id1 }}</div>
             <div class="text-caption">{{ getOrderInfo.place_datetime }}</div>
          </div>
      </div>
      <!-- row -->
      
      <q-list separator class="text-grey-7">
         <q-item>
            <q-item-section class="text-weight-medium">
               {{ getOrderInfo.customer_name }}
            </q-item-section>
            <q-item-section side class="text-weight-regular">
               {{ getOrderInfo.contact_number }}
            </q-item-section>
         </q-item>
         <q-item>
            <q-item-section class="text-weight-medium">               
               <?php echo t("Total")?>
            </q-item-section>
            <q-item-section side class="text-primary text-weight-bold">
               {{ getOrderInfo.pretty_total }}
            </q-item-section>
         </q-item>
         <q-item >
            <q-item-section><?php echo t("Status")?></q-item-section>
            <q-item-section side>
               {{getOrderInfo.payment_status1}}
            </q-item-section>
         </q-item>
      </q-list>
         

      <q-list >
         <q-expansion-item
         expand-separator         
         :label="getOrderInfo.total_items1"         
         default-opened
         >
            <div class="scroll" style="max-height: calc(30vh);">
               <q-list separatorx class="text-grey-7 text-weight-light">
                  <template v-for="items in getOrderItems" keys="items">
                     <q-item class="q-item-small">
                        <q-item-section>
                           <q-item-label>{{ items.item_name }}</q-item-label>
                           <q-item-label caption>{{ items.qty }} x {{ items.price.pretty_price_after_discount}}</q-item-label>
                           <q-item-label>
                              <template v-if="items.attributes">
                                 <template v-for="cooking_ref in items.attributes.cooking_ref">
                                    <div>{{cooking_ref}}</div>
                                 </template>
                                 <div>
                                 <template v-for="(ingredient, index) in items.attributes.ingredients" :key="index">
                                    <span>{{ ingredient }}</span>
                                    <span v-if="index !== items.attributes.ingredients.length - 1">,</span>
                                 </template>
                                 </div>
                              </template>                                                
                           </q-item-label>
                        </q-item-section>
                        <q-item-section top side class="text-blue-3">
                           {{items.price.pretty_total_after_discount}}
                        </q-item-section>
                     </q-item>           
                     
                     <!-- addons -->
                     <template v-for="addons in items.addons" :key="addons">
                        <q-item class="q-item-small">                           
                           <q-item-section>
                           <q-item-label class="text-weight-bold font12">{{addons.subcategory_name}}</q-item-label>
                           </q-item-section>
                           <q-item-section side top></q-item-section>
                        </q-item>

                        <q-item
                           v-for="addon_items in addons.addon_items"
                           :key="addon_items"
                           class="q-item-small"
                        >                           
                           <q-item-section top>
                              <q-item-label>{{ addon_items.sub_item_name }}</q-item-label>
                              <q-item-label caption>{{ addon_items.qty }} x {{ addon_items.pretty_price }}</q-item-label>
                           </q-item-section>
                           <q-item-section top side class="text-blue-3">
                             {{ addon_items.pretty_addons_total }}
                           </q-item-section>
                        </q-item>
                     </template>
                     <!-- addons -->
                     
                     <q-separator inset ></q-separator>
                  </template>    
                  
                  <template v-for="summary in getOrderSummary" :keys="summary">
                       <q-item class="q-item-small">                           
                           <q-item-section>
                           <q-item-label>{{summary.name}}</q-item-label>
                           </q-item-section>
                           <q-item-section side top>
                              {{summary.value}}
                           </q-item-section>
                        </q-item>
                  </template>
                  
               </q-list>
            </div>
         </q-expansion-item>
      </q-list>

             
       <q-space class="q-pa-sm"></q-space>
       <q-list >
         <q-expansion-item
         expand-separator         
         label="<?php echo t("More Order Details")?>"       
         default-openedx           
         >
            <div class="scroll"  style="max-height: calc(30vh);" >
               <q-list dense class="text-weight-light"> 

                 <q-item v-if="getOrderInfo.order_reference">
                     <q-item-section><?php echo t("Order Reference")?>#</q-item-section>
                     <q-item-section side>
                        {{getOrderInfo.order_reference}}
                     </q-item-section>
                 </q-item>

                 <template v-if="getOrderInfo.order_type == 'delivery'">
                 <q-item>
                     <q-item-section top><?php echo t("Delivery Address")?></q-item-section>
                     <q-item-section side top>
                        <template v-if="getOrderInfo.complete_delivery_address">
                           <div>{{ getOrderInfo.complete_delivery_address }}</div>
                           <div>{{ getOrderInfo.delivery_instructions }}</div>
                           <div>{{ getOrderInfo.delivery_options }}</div>
                        </template>
                        <template v-else>
                           <div>{{ getOrderInfo.delivery_address }} </div>
                           <div>{{ getOrderInfo.delivery_instructions }}</div>
                           <div>{{ getOrderInfo.delivery_options }}</div>
                        </template>
                     </q-item-section>
                  </q-item>
                  </template>
                  <q-item>
                     <q-item-section>{{getOrderInfo.order_type1}} <?php echo t("Date/Time")?></q-item-section>
                     <q-item-section side>                        
                        <template v-if="getOrderInfo.whento_deliver == 'now'">                                                      
                           {{ getOrderInfo.schedule_at }}
                        </template>
                        <template v-else>                                                      
                           {{ getOrderInfo.delivery_date }} {{ getOrderInfo.delivery_time }}
                        </template>
                     </q-item-section>
                  </q-item>
                  <q-item>
                     <q-item-section><?php echo t("Include utensils")?></q-item-section>
                     <q-item-section side>
                        <template v-if="getOrderInfo.include_utensils == 1">                           
                           <?php echo t("Yes")?>
                        </template>
                        <template v-else>                           
                           <?php echo t("No")?>
                        </template>
                     </q-item-section>
                  </q-item>
                  <q-item>
                     <q-item-section><?php echo t("Payment")?></q-item-section>
                     <q-item-section side>
                        {{getOrderInfo.payment_name}}
                     </q-item-section>
                  </q-item>
               </q-list>
            </div>
            <q-space class="q-pa-sm"></q-space>
         </q-expansion-item>
      </q-list>
      
      
      <q-footer class="bg-transparent text-grey-7" style="bottom: -40px;">
         <div class="row q-gutter-x-sm q-pa-sm">
         <q-btn @click="viewOrder" class="col radius6" unelevated color="amber-12" icon="las la-eye" no-caps size="17px"></q-btn>    
         <q-btn class="col radius6" unelevated color="primary" icon="las la-print" no-caps size="17px">            
            <q-menu>
               <q-list style="min-width: 200px" separator>
                  <q-item clickable v-close-popup @click="webPrint">
                     <q-item-section><?php echo t("Web Print")?></q-item-section>
                  </q-item>
                  <template v-for="printer in printerList" :key="printer">
                  <q-item clickable v-close-popup @click="SwitchPrinter(printer.printer_id,printer.printer_model)">
                     <q-item-section>
                        {{printer.printer_name}}
                     </q-item-section>
                  </q-item>
                  </template>
               </q-list>
         </q-menu>            
         </q-btn> 
         <q-btn @click="chooseReceipt" class="col radius6" unelevated color="blue-grey-3" text-color="white" icon="lab la-whatsapp" no-caps size="17px"></q-btn> 
         <q-btn @click="chooseReceipt" class="col radius6" unelevated color="blue-grey-3" text-color="white" icon="las la-envelope" no-caps size="17px"></q-btn> 
         </div>
      </q-footer>

      </template>
   </div>
   <!-- pad -->
   </q-drawer>

    <q-page-container>
      <q-page padding>
      
        <q-ajax-bar ref="bar" position="top" color="blue" size="3px" skip-hijack ></q-ajax-bar>
          
        <div class="row q-mb-mdx items-center q-gutter-x-md">
            <div class="col">

               <q-input outlinedx v-model="q" label="<?php echo t("Search Order")?>" dense color="grey-7" :loading="awaitingSearch" >
                  <template v-slot:prepend>
                     <q-icon name="search" ></q-icon>
                  </template>
                  <template v-slot:append>
                     <template v-if="isSearch">
                     <q-btn @click="clearSearch" flat label="Clear" color="dark" no-caps class="text-weight-regular"></q-btn>
                     </template>
                  </template>
               </q-input>

            </div>
            <div class="col-5">                           
               <q-tabs
                  v-model="order_type"                        
                  active-color="primary"      
                  class="text-grey-7"
                  @update:model-value="updateOrderList"
                  narrow-indicator
                  align="right"
               >
                  <template v-for="items in getTabList" keys="items">
                     <q-tab :name="items.value" :label="items.label" no-caps class="text-capitalize" ></q-tab>      
                  </template>
               </q-tabs>      

            </div>
            <!-- col -->
        </div>
        <!-- row -->        
                       
        <q-table     
          ref="tableRef"               
         :rows="rows"
         :columns="columns"
         row-key="name"
         :hide-pagination="false"
         flat
         :title="getLabel(order_type)"
         v-model:pagination="pagination"
         :loading="loading"
         @request="onRequest"         
         :filter="filters"         
         :rows-per-page-label="rowPerPageLabel"
         :pagination-label="paginationLabel"
         >         

         <template v-slot:loading>
           <q-inner-loading showing color="primary"></q-inner-loading>
         </template>

         <template v-slot:no-data>      
            <template v-if="!loading && !hasData">
               <div class="text-body2 text-grey">                 
                 <?php echo t("No available data")?>
               </div>
            </template>   
         </template>

         <template v-slot:top-right>     
         
         <div class="q-gutter-x-sm">            
            
          <template v-if="hasFilters">
          <q-btn  
            icon="refresh" 
            no-caps unelevated
            color="white"
            text-color="grey-7"
            outline                
            class="radius6"        
            dense
            @click="resetFilters"
            >
          </q-btn>
          </template>          
         
         <q-btn label="<?php echo t("Filters")?>" icon="filter_list" 
            no-caps unelevated
            color="white"
            text-color="grey-7"
            outline                
            class="radius6"        
            dense
            >
            <q-popup-proxy v-model="filter_proxy">
               <q-card style="min-width:400px;">
                  <q-card-section class="text-body2 text-grey-7 q-gutter-y-sm">
                                    
                     <q-select 
                     v-model="filter_order_status" 
                     :options="getStatusListValue" 
                     label="<?php echo t("Status")?>"
                     emit-value
                     map-options
                     use-inputx
                     >
                     </q-select>
                     
                     <components-searchcustomer             
                     v-model:filter_customer="filter_customer"
                     >         
                     </components-searchcustomer>                  
                                                            
                     <q-input v-model="filterDate">                       
                        <template v-slot:append>
                           <q-icon name="event" class="cursor-pointer">
                               <q-popup-proxy v-model="calendarProxy" cover transition-show="scale" transition-hide="scale">
                                   <q-date v-model="filter_date" range @range-end="rangeEnded" @update:model-value="updateRangevalue" :locale="calendarLocale" >
                                      <div class="row items-center justify-end">                                    
                                         <q-btn v-close-popup label="<?php echo t("Close")?>" color="primary" flat ></q-btn>
                                      </div>
                                   </q-date>
                               </q-popup-proxy>
                           </q-icon>
                        </template>
                     </q-input>
                  
                  </q-card-section>

                  <q-card-actions>
                     <q-btn                     
                     no-caps unelevated
                     color="primary"                    
                     class="radius6 fit"        
                     label="<?php echo t("Apply Filters")?>"
                     @click="applyFilters"
                     >
                     </q-btn>
                  </q-card-actions>
               </q-card>
            </q-popup-proxy>
         </q-btn>        
         </div>
         </template>

         <template v-slot:body="props">
            <q-tr :props="props" @click="onRowClick(props.row)" class="cursor-pointer">
               <q-td key="order_id" :props="props">
                  {{ props.row.order_id }}
               </q-td>
               <q-td key="order_type" :props="props">                  
                  {{ props.row.order_type }}
               </q-td>
               <q-td key="customer_name" :props="props">
                  {{ props.row.customer_name }}
               </q-td>
               <q-td key="total" :props="props">
                  {{ props.row.total }}
               </q-td>
               <q-td key="date_created" :props="props">
                  {{ props.row.date_created }}
               </q-td>
               <q-td key="status" :props="props">                  
                  <q-badge :color="props.row.status_raw" class="modified-badge"> 
                    {{ props.row.status }}
                  </q-badge>
               </q-td>
            </q-tr>
         </template>

         </q-table>      
         
         <components-send-receipt
         ref="choose_receipt"
         title="<?php echo t("Go paperless and green, send receipt to customer's email or whatsApp")?>"
         :enabled_email="true"
         :enabled_whatsapp="true"
         :enabled_webprint="false"
         :enabled_print="false"
         >
         </components-send-receipt>

         <components-receipt
         ref="receipt"
         >
         </components-receipt>		      

      </q-page>
    </q-page-container>

  </q-layout>
</DIV>

<script type="text/x-template" id="xchoose_receipt">   
<?php $this->renderPartial("/pos/choose-receipt");?>       
</script>

<script type="text/x-template" id="xtemplate_receipt">
<?php $this->renderPartial("/pos/receipt");?>    
</script>