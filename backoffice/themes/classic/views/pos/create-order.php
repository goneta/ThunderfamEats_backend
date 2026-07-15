<DIV id="app-pos">

<q-layout view="lHh lpR fFf"  v-cloak >


<q-header class="bg-white text-dark q-pa-sm q-pl-md q-pr-md" bordered>
    <div class="row items-center">
        <div class="col q-gutter-x-sm">
            <q-btn-toggle
                v-model="view"
                color="blue-grey-3"
                text-color="blue-grey-7"
                toggle-color="primary"
                toggle-text-color="white"
                unelevated
                no-caps                        
                :options="view_list"      
                @update:model-value="updateView"  
                :dense="this.$q.screen.lt.sm?true:false"
            >
            </q-btn-toggle>

            <!-- <template v-if="isEdit">
                <q-btn color="red" size="14px" no-caps unelevated  @click="closeOrder">
                Close
                </q-btn>
            </template> -->

        </div>
        <!-- col -->
        <div class="col text-right">         
          <div class="flex justify-end items-center">
            <div class="q-mr-sm">
                <q-btn @click="testPrint" round icon="print" size="12px" unelevated color="light" text-color="grey-7"></q-btn>
            </div>
            <div class="q-mr-sm">
            <components-notifications
                ref="notifications"
                merchant_uuid="<?php echo Yii::app()->merchant->merchant_uuid?>"
                :realtime_data="{
                    enabled : '<?php echo Yii::app()->params['realtime_settings']['enabled']==1?true:false ;?>',  
                    provider : '<?php echo CJavaScript::quote( Yii::app()->params['realtime_settings']['provider'] )?>',  
                    key : '<?php echo CJavaScript::quote( Yii::app()->params['realtime_settings']['key'] )?>',  			   
                    cluster : '<?php echo CJavaScript::quote( Yii::app()->params['realtime_settings']['cluster'] )?>', 
                    channel : '<?php echo CJavaScript::quote( Yii::app()->params->realtime['admin_channel'] )?>',  			   
                    event : '<?php echo CJavaScript::quote( Yii::app()->params->realtime['notification_event'] )?>',  
                }"
                @after-receivenotification="afterReceivenotification"
                @on-alertactions="onAlertactions"
                >
            </components-notifications>  

            </div>
            <div>                
               <q-btn @click="sidebar=!sidebar" round :icon="sidebar?'close':'menu'" size="12px" unelevated color="light" text-color="grey-7"></q-btn>
            </div>
          </div>
          <!-- flex -->
        </div>
        <!-- col -->
    </div>
</q-header>

<!-- CART DRAWER -->
<q-drawer  v-model="sidebar" side="left" overlay bordered
width="250"
>

<div class="q-pa-md"> 

<q-img
src="<?php echo $website_logo?>"
spinner-color="white"
style="max-height: 50px; max-width: 180px"
fit="scale-down"
>
</q-img>

<q-space class="q-pa-md"></q-space>

<div class="flex items-center q-gutter-x-sm">
    <div>
        <q-avatar size="50px">
            <img src="<?php echo MerchantTools::getProfilePhoto()?>"></q-avatar>
        </q-avatar>
    </div>
    <div>
        <div class="text-subtitle2"><?php echo MerchantTools::displayAdminName();?></div>
        <div class="text-caption">
             <?php 
	         if(!empty(Yii::app()->merchant->contact_number)){
	         	echo t("T.")." ".Yii::app()->merchant->contact_number;
	         }
	         if(!empty(Yii::app()->merchant->email_address)){
	         	echo '<br/>'.t("E.")." ".Yii::app()->merchant->email_address;
	         }	        	        
	         ?>
        </div>
    </div>
</div>
<!-- flex -->
</div>

<template v-for="items_menu in sidebar_menu" :key="items_menu">
<q-list>
    <template v-if="items_menu.items">        
    <q-expansion-item
    expand-separator            
    :label="items_menu.label"    
    dense    
    :expand-icon="items_menu.items?'keyboard_arrow_down':'x'"        
    >
    <div class="q-pl-md">        
        <q-list dense>
            <template v-for="items_sub_menu in items_menu.items" :key="items_sub_menu">
                <q-item clickable :href="items_sub_menu.url_absolute">
                    <q-item-section>
                        {{items_sub_menu.label}}
                    </q-item-section>            
                </q-item>            
            </template>
        </q-list>
    </div>
    </q-expansion-item>
    </template>
    <template v-else>
       <q-item clickable :href="items_menu.url_absolute">
            <q-item-section>
                {{items_menu.label}}
            </q-item-section>            
        </q-item>      
    </template>
</q-list>
</template>

</q-drawer>

<q-drawer v-model="drawer" side="right" 
show-if-above
:width="this.$q.screen.lt.sm?300:350"
:breakpoint="800"
bordered
>
<!-- :breakpoint="800" -->

<div class="q-pa-md">
    

<q-btn-toggle
      v-model="transaction_type"
      color="grey-3"
      text-color="grey-7"
      toggle-color="primary"
      toggle-text-color="white"
      unelevated
      no-caps
      spread      
      :options="transaction_list"
      @update:model-value="setTransactionType"      
    >
</q-btn-toggle>

<q-space class="q-pa-xs"></q-space>

  <div class="row items-center q-gutter-x-sm">
    <div class="col">                        
        <components-searchcustomer 
          ref="customer_search"      
          :default_list="getDefaultCustomer"
          @after-selectcustomer="afterSelectcustomer"    
          @clear-selectcustomer="clearSelectcustomer"    
          @onselect-customer="onselectCustomer"
        >
        </components-searchcustomer>
   </div>
   <div class="btn-block">
    <q-btn no-caps color="primary" text-color="white" unelevated @click="this.$refs.customer.modal=true">
        <div><q-icon name="add" size="xs"></q-icon></div>
        <div><?php echo t("Customer")?></div>
    </q-btn>
   </div>
  </div>
  <!-- row -->
    
  
  
  <template v-if="transaction_type=='delivery'">
     <div v-if="customerSelected" class="q-mb-sm border-bottom">
        <q-item dense clickable >         
           <q-item-section @click="beforeShowaddress" >
               <q-chip color="grey-2" text-color="grey-7" icon="home" class="ellipsis" >                    
               {{hasAddress? getAddress.address.address1 + ' '+ getAddress.address.formatted_address :'<?php echo t("Delivery Address")?>'}}
                </q-chip>
           </q-item-section>
           <template v-if="hasAddress">
           <q-item-section side >
              <q-btn 
              dense 
              round color="blue" 
              unelevated 
              icon="edit" 
              size="11px"
              @click="this.$refs.address_list.dialog=true"
              ></q-btn>
           </q-item-section>
           <q-item-section side >
              <q-btn 
              dense 
              round 
              color="red" 
              unelevated 
              icon="delete_outline" 
              size="11px"
              @click="clearAddress"
              >              
              </q-btn>
           </q-item-section>
           </template>
        </q-item>
     </div>
  </template>
  <template v-else>
    <q-space class="q-mb-sm"></q-space>
  </template>

  <q-item dense style="padding-left: 0; padding-right:0px;">    
   <q-item-section class="btn-toggle-small">    
      <q-btn-toggle              
        v-model="whento_deliver"
        toggle-color="red"
        text-color="grey-7"
        unelevated
        no-caps
        :options="attributes_data.preferred_times?attributes_data.preferred_times:[]"
        spread
        dense
        size="12px"
        padding="0px;"
        @update:model-value="updateWhenDelivery"
        ></q-btn-toggle>
   </q-item-section>
  </q-item>
  

  <template v-if="whento_deliver == 'schedule'">
  <q-item dense style="padding-left: 0; padding-right:0px;">    
   <q-item-section>

      <q-select                            
        v-model="delivery_date"      
        :options="getOpeningDates"  
        label="<?php echo t("Date")?>"
        emit-value
        stack-label        
        map-options        
        dense     
        @update:model-value="updateDeliveryDate"
      >
     </q-select>

     <q-select              
        v-model="delivery_time"   
        :options="getTimelist"           
        label="<?php echo t("Time")?>"
        option-value="start_time"
        option-label="pretty_time"
        stack-label
        emit-value
        :rules="[
        (val) =>
            (val && val.length > 0) || '<?php echo t("This field is required")?>',
        ]"
        map-options       
        dense            
        @update:model-value="updateDeliveryTime"
    ></q-select>

   </q-item-section>
  </q-item>
  </template>

  
  <template v-if="hasTabledata && transaction_type=='dinein'">    
    <div class="q-mb-sm border-bottom">                   
        <q-item dense >
            <q-item-section avatar>
                <q-chip color="grey-2" text-color="grey-7" icon="event_seat" style="max-width: 6em;">
                    {{ table_data.table_name }}
                </q-chip>
            </q-item-section>
            <q-item-section>
               <q-chip color="primary" text-color="white" :icon="table_data.guest_number>1?'group':'person'" style="max-width: 5em;">
                    {{ table_data.guest_number }}
                </q-chip>
            </q-item-section>
            <q-item-section side>                
                <q-btn dense round color="blue" unelevated icon="edit" size="11px" @click="editTable" ></q-btn>
            </q-item-section>
            <q-item-section side v-if="!isEdit">                   
                <q-btn dense round color="red" unelevated icon="delete_outline" size="11px"  @click="removeTable" ></q-btn>
            </q-item-section>
        </q-item>
     </div>
  </template>

  <div class="scroll card-small relative-position">

  <q-inner-loading 
  :showing="refresh_cart" 
  color="primary"
  ></q-inner-loading>

  <template v-if="loading_cart">
      <div class="q-gutter-y-sm">
         <template v-for="items in 5">
            <div><q-skeleton height="100px" square class="rounded-borders" /></div>
         </template>
      </div>
  </template>

  <template v-if="!hasCart && !loading_cart">  
    <div class="card-small flex flex-center">
         <div class="text-grey"><?php echo t('No items added')?></div>
    </div>
  </template>

  <template v-if="hasCart && !loading_cart">  
    <q-list separator>
        <template v-for="items in getCartItems" :key="items">
         <q-item>
            <q-item-section avatar top>
                <q-img
                :src="items.url_image"
                style="height: 50px; width: 50px"
                loading="lazy"
                fit="cover"
                spinner-color="yellow-9"
                spinner-size="sm"
                class="rounded-borders"
                ></q-img>
            </q-item-section>
            <q-item-section top  >
                <q-item-label class="text-weight-regular"><span v-html="items.item_name"></span></q-item-label>

                <q-item-label caption class="text-weight-light">
                    <template v-if="items.price.discount > 0">
                    <p class="no-margin">
                        <del>{{ items.price.pretty_price }}</del>
                        {{ items.price.pretty_price_after_discount }}
                    </p>
                    </template>
                    <template v-else>
                    <p class="no-margin">{{ items.price.pretty_price }}</p>
                    </template>
                </q-item-label>
                
                <!-- qty -->
                <q-item-label>
                <div class="flex items-center q-col-gutter-x-sm">
                    <div class="borderx">
                    <q-btn
                        :icon="items.qty == 1 ? 'delete_outline' : 'remove'"
                        outline 
                        round color="grey"
                        unelevated
                        size="sm"                        
                        style="width:5px"
                        rounded
                        @click="lessCartQty(items.qty > 1 ? items.qty-- : 1, items)"
                        :disable="isLoading"
                    ></q-btn>
                    </div>
                    <div class="borderx">
                    {{items.qty}}
                    </div>
                    <div class="borderx">
                    <q-btn
                        icon="add"
                        outline 
                        round color="grey"
                        unelevated
                        size="sm"                        
                        style="width:5px"
                        rounded
                        @click="addCartQty(items.qty++, items)"
                        :disable="isLoading"
                    ></q-btn>
                    </div>
                </div>
                </q-item-label>
                <!-- qty -->

                <q-item-label caption class="text-weight-light">
                    <p class="no-margin" v-if="items.special_instructions != ''">{{ items.special_instructions }}</p>

                    <template v-if="items.attributes != ''">
                        <template
                            v-for="attributes in items.attributes"
                            :key="attributes"
                        >
                            <p class="no-margin">
                            <template v-for="(attributes_data, attributes_index) in attributes">
                                {{ attributes_data
                                }}<template v-if="attributes_index < attributes.length - 1"
                                >,
                                </template>
                            </template>
                            </p>
                        </template>
                    </template>

                    <!-- addons -->
                    <template v-for="addons in items.addons" :key="addons">
                        <template v-for="(addon_items,addons_index) in addons.addon_items" :key="addon_items">
                           {{addon_items.sub_item_name}} (+{{ addon_items.pretty_addons_total }})
                           <template v-if="addons_index < addon_items.length - 1">,</template>
                        </template>
                    </template>
                    <!-- addons -->

                </q-item-label>

            </q-item-section>            
            <q-item-section side top >           

                <div class="column justify-end items-end fit">
                    <div class="col">
                        <q-btn
                        round
                        color="grey-3"
                        text-color="grey-7"
                        icon="clear"
                        size="xs"
                        unelevated
                        @click="removeCartItem(items.cart_row)"
                        :disable="isLoading"
                        />
                    </div>
                    <div class="col text-weight-light relative-position text-grey-7">
                        <div class="absolute-bottom-right">
                        
                        <template v-if="items.price.discount <= 0">
                            <div class="q-ma-none">{{ items.price.pretty_total }}</div>
                        </template>
                        <template v-else>
                            <div class="q-ma-none">
                            {{ items.price.pretty_total_after_discount }}
                            </div>
                        </template>

                        </div>
                    </div>
                </div>

            </q-item-label>

            </q-item-section>            
         </q-item>
        </template>
    </q-list>
    </template>    
  </div>
  <!-- scroll -->
  
  <template v-if="points_data">      
      <template v-if="points_data.points_enabled">
        <div class="text-grey text-center">{{points_data.points_label}}</div>
      </template>        
   </template>      
  
    <div class="row q-mb-sm items-center justify-center btn-block border-top q-pt-sm">
       
       <q-btn @click="this.$refs.promo.modal=true" :disable="!hasCart" square color="white" text-color="grey-7" dense unelevated no-caps size="17px">
        <div class="border-grey rounded-borders q-pa-xs text-weight-regular text-body2" style="width: 55px;">
           <div><q-icon name="local_offer" size="20px"></q-icon></div>
           <div><?php echo t('Promo')?></div>
        </div>
        </q-btn>    

        <q-btn @click="this.$refs.discount.modal=true" :disable="!hasCart" square color="white" text-color="grey-7" dense unelevated no-caps size="17px">
        <div class="border-grey rounded-borders q-pa-xs text-weight-regular text-body2" >
          <div><q-icon name="percent" size="20px"></q-icon></div>
          <div><?php echo t("Discount")?></div>
        </div>
        </q-btn>    

        <q-btn  @click="this.$refs.tips.modal=true"  :disable="!hasCart" square color="white" text-color="grey-7" dense unelevated no-caps size="17px">
        <div class="border-grey rounded-borders q-pa-xs text-weight-regular text-body2" style="width: 55px;">
           <div><q-icon name="favorite_border" size="20px"></q-icon></div> 
           <div><?php echo t("Tips")?></div>
        </div>
        </q-btn>    

        <q-btn @click="this.$refs.points.modal=true" :disable="!hasCart || !customerSelected" square color="white" text-color="grey-7" dense unelevated no-caps size="17px">
         <div class="border-grey rounded-borders q-pa-xs text-weight-regular text-body2" style="width: 55px;">
           <div><q-icon name="loyalty" size="20px"></q-icon></div>
           <div><?php echo t("Points")?></div>
         </div>
        </q-btn>           
    </div>
        
    <q-list class="fit" dense>
        <template v-for="(summary,index) in getSummary" :keys="items">
         <q-item :class="{'text-weight-bold text-body1': summary.type=='total' }" :active="index%2?true:false" active-class="bg-grey-2 text-dark radius5" >
            <q-item-section>
                <q-item-label>
                    <div class="flex q-gutter-x-sm">
                        <div>{{summary.name}}</div>                        
                        <div>
                          <template v-if="summary.type == 'voucher' || summary.type == 'manual_discount'">
                             <q-btn
                                size="xs"
                                icon="delete"
                                dense
                                color="red"
                                unelevated
                                @click="removePromocode"
                            ></q-btn>
                          </template>
                          <template v-else-if="summary.type == 'tip'">
                             <q-btn
                                size="xs"
                                icon="delete"
                                dense
                                color="red"
                                unelevated
                                @click="removeTips">
                            </q-btn>
                          </template>
                          <template v-else-if="summary.type == 'points_discount'">
                             <q-btn
                                size="xs"
                                icon="delete"
                                dense
                                color="red"
                                unelevated
                                @click="removePoints">
                              </q-btn>
                          </template>
                        </div>
                    </div>
                </q-item-label>                
            </q-item-section>
            <q-item-section side>
                <q-item-label>{{summary.value}}</q-item-label>
            </q-item-section>
        </q-item>       
        </template>
    </q-list>    
        
    <template v-if="hasCartError">
    <div class="inline-message-error q-pa-sm radius6 q-mt-sm q-mb-sm">
        <div class="row items-start q-gutter-x-sm">
            <div class="col-1"><q-icon name="highlight_off" size="sm"></q-icon></div>
            <div class="col">
                <template v-for="error in getCartError" :key="error">
                    <div>{{error}}</div>
                </template>
            </div>
        </div>        
    </div>
    </template>

    <q-list dense class="bg-grey-3 text-grey-7 radius6">
        <q-item clickable tag="label" v-ripple>
            <q-item-section><?php echo t("Skip Kitchen")?></q-item-section>
            <q-item-section side>
               <q-checkbox 
               v-model="skip_kitchen" 
               val="1" 
               color="primary" 
               @update:model-value="onSelectSkipkitchen"
               ></q-checkbox>
            </q-item-section>
        </q-item>
    </q-list>

    <template v-if="isEdit">
    <q-btn 
     color="red"
     size="18px" 
     no-caps unelevated  
     @click="closeOrder" 
     class="fit q-mt-sm"
     >
    <?php echo t("Close")?>
    </q-btn>
    </template>
                      
    <div class="row q-gutter-x-sm q-mt-sm">       
       <!-- -> {{cart_transaction_type}} =>  {{transaction_type}} -->
    <template v-if="isChangeTransaction">
       <q-btn       
        no-caps label="<?php echo t("Save")?>" unelevated color="amber-12" text-color="grey-7" size="18px" 
        class="col-3" 
        @click="UpdateTransactions"            
        >        
        </q-btn>
    </template>
    <template v-else>        
            <q-btn
            :disable="!hasCart || !customerSelected || hasCartError || !isNeedtosendorder"
            no-caps label="<?php echo t('Kitchen')?>" unelevated color="amber-12" text-color="grey-7" size="18px" 
            class="col-3"
            @click="SendToKitchen"
            :disabled="send_kds_loading"
            >        
            </q-btn>        
    </template>
     <q-btn 
       :disable="!hasCart || !customerSelected || hasCartError || isChangeTransaction"         
        no-caps label="<?php echo t('Proceed to pay')?>" unelevated color="primary" size="18px" 
        class="col"
        @click="MakePayment"
     >        
     </q-btn>
    </div>
    
    <div class="row q-gutter-sm q-mt-sm justify-center">        
        <template v-if="isEdit">
           <q-btn class="col-3" @click="deleteConfirm" :disable="!hasCart" no-caps unelevated label="<?php echo t("Delete")?>" size="16px" color="red" ></q-btn>
        </template>
        <template v-else>
        <q-btn class="col-3" @click="resetConfirm" :disable="!hasCart" no-caps unelevated 
        label="<?php echo t('Reset')?>" size="16px" color="grey-3" text-color="grey-7">
        </q-btn>
        </template>        
        <q-btn class="col" @click="this.$refs.holdorder.modal=true" :disable="!hasCart || isEdit" no-caps unelevated 
        label="<?php echo t('Hold Bill')?>" size="16px" color="grey-3" text-color="grey-7">
        </q-btn>
        <q-btn class="col" @click="this.$refs.addtotal.modal=true" no-caps unelevated 
        label="<?php echo t('Total')?>" size="16px" color="grey-3" text-color="grey-7">
        </q-btn>
    </div>
  

</div>
<!-- padding -->

</q-drawer>

<q-page-container >
  <q-page padding>
  

       <q-ajax-bar ref="bar" position="top" color="blue" size="3px" skip-hijack ></q-ajax-bar>
                  
       <template v-if="category_loading">
           <div class="q-gutter-y-md">
            <div><q-skeleton height="50px" square ></q-skeleton></div>
            <div><q-skeleton type="rect" ></q-skeleton></div>
           </div>
        </template>
        <template v-else>
            
            <template v-if="do_search">
              <q-intersection  transition="slide-right">
               <div class="flex items-center">
                  <div class="col">

                    <q-input outlinedx v-model="q" label="<?php echo t("Search food")?>" dense color="grey-7" :loading="awaitingSearch" >
                        <template v-slot:prepend>
                            <q-icon name="search" ></q-icon>
                        </template>
                        <template v-slot:append>
                            <template v-if="isSearch">
                            <q-btn @click="this.q=''" flat label="<?php echo t("Clear")?>" color="dark" no-caps class="text-weight-regular"></q-btn>
                            </template>
                        </template>
                    </q-input>

                  </div>
                  <!-- col -->
                  <div class="">
                     <q-btn @click="closeSearch"  round icon="close" size="sm" unelevated color="light" text-color="grey-7">                            
                      </q-btn> 
                  </div>
               </div>                     
               <!-- row -->
               </q-intersection>
            </template>            
           
            <!-- CATEGORY HERE -->       
            
            <template v-if="transaction_type=='dinein' && isTableEmpty"></template>
            <template v-else>           
                <template v-if="view!='new_view'"></template>                                                                                   
                <template v-else>
                <div v-if="!do_search" class="flex items-center q-mb-mdx q-gutter-x-sm">                    
                    <div v-if="isBarcodeactive" class="colx borderx">
                      <q-btn @click="showScanbarcode"  round icon="qr_code_scanner" size="12px" unelevated color="light" text-color="grey-7">                            
                      </q-btn> 
                    </div>
                    <div class="colx borderx">
                      <q-btn @click="do_search=true"  round icon="search" size="12px" unelevated color="light" text-color="grey-7">                            
                      </q-btn> 
                    </div>
                    <div class="col borderx">

                    <q-tabs
                        v-model="category_id"        
                        no-caps
                        active-color="white"  
                        active-bg-color="grey-8"                    
                        indicator-color="transparent"
                        active-class="radius20"
                        dense                
                        align="left"
                        class="text-grey-7"
                    >
                    <template v-for="items in getCategory" :keys="items">
                        <q-tab :name="items.cat_id" :label="items.category_name" @click="loadItems(items.cat_id)"></q-tab>
                    </template>            
                    </q-tabs>   

                    </div>
                </div>  
                <!-- row-->
                </template>
            </template>
            <!-- CATEGORY -->

        </template>
            
            
      <template v-if="view=='table_view'">
         <div class="q-pa-md q-pt-md">
                      
           <q-inner-loading  
            :showing="table_status_loading" 
            color="primary"
            ></q-inner-loading>

            
           <template v-if="hasRooms">           
            <q-tabs
                    v-model="room_uuid"        
                    no-caps
                    active-color="white"  
                    active-bg-color="grey-8"                    
                    indicator-color="transparent"
                    active-class="radius20"
                    dense                
                    align="left"
                    class="text-grey-7"
                >
                <template v-for="(items,roomid) in getRoomList" :keys="items">
                    <q-tab :name="roomid" :label="items"></q-tab>
                </template>            
                  <!-- <q-tab name="add" icon="add"></q-tab> -->
                </q-tabs>   
             </template>    
             <template v-else>
                   <div class="card-form flex flex-center">
                        <div class="text-body2 text-grey text-center">
                           <div class="q-mb-md">
                            <?php echo t("No available Rooms and Tables")?>
                           </div>        
                           <q-btn                            
                           @click="OpenParentLink(attributes_data.create_table_link)"
                           icon="add" 
                           color="primary" 
                           label="<?php echo t("Click here to create")?>"                            
                           outline
                           class="radius6"
                           no-caps>                                              
                           </q-btn>
                        </div>                        
                    </div>                 
             </template>        

            <q-space class="q-pa-md"></q-space>
            
            <template v-if="!hasTableList">
               <div class="card-form flex flex-center">
                    <div class="text-body2 text-grey text-center">
                        <div class="q-mb-md">
                            <?php echo t("No available data")?>
                        </div>
                    </div>
                </div>
            </template>

            
            <div class="row q-gutter-smx" >
            
                    <template
                        v-for="items in getTableList"
                        :key="items"
                    >
                        <div class="relative-position" :class="{'col-3':this.$q.screen.gt.sm , 'col-6':this.$q.screen.lt.md}" >                                                              
                            <div class="q-pa-xs">
                                <div
                                v-ripple 
                                class="rounded-borders full-width cursor-pointer row items-stretch q-pa-sm text-grey-8"                                                        
                                style="height: 9em;"   
                                :class="getStatusClass(items.status_class)"       
                                @click.stop="showSelectGuestNumber(items)"                  
                                >
                                <div class="column col-12">
                                    <div class="col">
                                        <div class="flex justify-between items-center">
                                            <div class="text-weight-regular">{{items.table_name}}</div>
                                            <div class="colx">                                                                                                                               
                                                <q-chip color="primary" text-color="white" icon="person">
                                                    {{ items.min_covers }} - {{ items.max_covers }}
                                                </q-chip>                                                                                            
                                            </div>                                            
                                        </div>
                                    </div>
                                    <div class="col relative-position">
                                        <div class="absolute-bottom-left text-weight-regular">
                                            {{ items.status }} 
                                            <template v-if="items.transaction_type">
                                            / {{items.transaction_type}}
                                            </template>
                                        </div>
                                        <div class="absolute-bottom-right text-weight-regular">                                                                                    
                                            <components-elapsetime 
                                            :start="items.time_seated"
                                            :timezone="items.timezone"
                                            >
                                            </components-elapsetime>
                                        </div>
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <!-- row -->

         </div>

         <q-page-sticky position="bottom-left" :offset="[20, 18]">
              <div class="flex q-gutter-x-md">
                <div>
                    <q-badge color="mysuccess" rounded class="q-mr-sm" ></q-badge> <?php echo t("Available")?>
                </div>
                <div>
                    <q-badge color="myerror" rounded class="q-mr-sm" ></q-badge> <?php echo t("Ordered")?>
                </div>
                <div>
                    <q-badge color="occupied" rounded class="q-mr-sm" ></q-badge> <?php echo t("Occupied")?>
                </div>
                <div>
                    <q-badge color="yellow-2" rounded class="q-mr-sm" ></q-badge> <?php echo t("Waiting for bill")?>
                </div>
              </div>
          </q-page-sticky>
         
      </template>
      <template v-else-if="view=='hold_view'">
          <components-orders
          ref="ref_order_list"
          title="<?php echo t("Hold Orders")?>"  
          :transaction_list="transaction_list"
          transaction_type="hold_orders"
          :printer_list="attributes_data.printer_list?attributes_data.printer_list:null"
          @load-orders="loadOrders"    
          @after-deleteorders="afterDeleteorders"               
          >
          </components-orders>
      </template>
      <template v-else-if="view=='order_view'">                  
          <components-orders
          ref="ref_order_list"
          title="<?php echo t("Open Orders")?>"      
          :transaction_list="transaction_list"
          :printer_list="attributes_data.printer_list?attributes_data.printer_list:null"
          transaction_type="send_orders"
          @load-orders="loadOrders"                
          @after-deleteorders="afterDeleteorders" 
          >
          </components-orders>
      </template>

      <template v-else-if="view=='table_request'">          
         <components-request-list
         ref="request_list"
         >
         </components-request-list>         
      </template>

      <div v-else class="q-pa-md q-pt-md">
           
           <!-- SEARCH RESULTS -->           
           <template v-if="isSearch">
               <div class="text-h5"><?php echo t("Search for")?> "{{q}}"</div>

                
                <template v-if="!searchResults && !awaitingSearch">
                    <div class="text-body2"><?php echo t("Sorry, no product matched for your search. Please try again")?>.</div>
                </template>

               <div class="row" >
               <template v-for="items in item_results" :key="items">
                   <div class="borderx" :class="{'col-3':this.$q.screen.gt.sm , 'col-6':this.$q.screen.lt.md}" >
                   
                   <q-list>
                        <q-item clickable v-ripple clickable @click="viewItems(items)">
                            <q-item-section>
                                <q-item-label>
                                <q-img
                                   :src="items.url_image"
                                    style="height: 9em;"
                                    fit="scale-down"
                                    loading="lazy"
                                    spinner-color="primary"
                                    spinner-size="xs"
                                    class="rounded-borders"
                                >
                                </q-img>
                                </q-item-label>
                                <q-item-label lines="1" class="text-weight-regular">
                                    <span v-html="items.item_name"></span>
                                </q-item-label>
                                <q-item-label caption lines="1" class="text-weight-light">
                                    <span v-html="items.item_description"></span>
                                </q-item-label>    

                                  <q-item-label v-if="items?.promo_data?.message" class="text-weight-light">
                                        <q-badge :color="isEligible(items) ? 'green' :'amber' " outline multi-line text-color="black" :label="getPromoMessage(items)" />
                                    </q-item-label>  

                                <q-item-label class="flex justify-between items-center">
                                    <div class="text-blue text-weight-medium text-caption">
                                    <template
                                        v-for="(prices, index) in items.price"
                                        :key="prices"
                                        >
                                        <template v-if="index <= 0">
                                            <template v-if="prices.discount > 0">{{
                                            prices.pretty_price_after_discount
                                            }}</template>
                                            <template v-else>{{ prices.pretty_price }}</template>
                                        </template>
                                        </template>
                                    </div>
                                    <div>
                                        <q-btn icon="add" size="xs" outline round color="grey" ></q-btn>
                                    </div>
                                </q-item-label>
                            </q-item-section>                                       
                        </q-item>
                    </q-list>

                   </div>
               </template>
               </div>
           </template>
           <!-- END SEARCH RESULTS -->

           <template v-else>           
           

           <!-- LOADING -->
           <template v-if="items_loading">      
            <div class="row">                                            
                    <template v-for="skeleton in 16" :key="skeleton">
                        <div class="borderx" :class="{'col-3':this.$q.screen.gt.sm , 'col-6':this.$q.screen.lt.md}" > 
                        <div class="q-pa-xs">
                        <q-skeleton
                            height="120px"
                            square
                            class="radius8"                        
                            ></q-skeleton>                            
                        </div>
                        </div>
                    </template>                 
            </div>     
            </template> 
            <!-- LOADING -->

            <template v-else>            

            <!-- TABLE STARTS HERE -->                 
            <q-inner-loading  
            :showing="table_status_loading" 
            color="primary"
            >
            </q-inner-loading>       

            <template v-if="transaction_type=='dinein' && isTableEmpty">    
                       
                <template v-if="hasRooms">
                <q-tabs
                    v-model="room_uuid"        
                    no-caps
                    active-color="white"  
                    active-bg-color="grey-8"                    
                    indicator-color="transparent"
                    active-class="radius20"
                    dense                
                    align="left"
                    class="text-grey-7"
                >
                <template v-for="(items,roomid) in getRoomList" :keys="items">
                    <q-tab :name="roomid" :label="items"></q-tab>
                </template>            
                </q-tabs>   
                </template>
                <template v-else>
                    <div class="card-form flex flex-center">
                        <div class="text-body2 text-grey text-center">
                           <div class="q-mb-md"><?php echo t("No available Rooms and Tables")?></div>                           
                           <q-btn                            
                           @click="OpenParentLink(attributes_data.create_table_link)"
                           icon="add" 
                           color="primary" 
                           label="<?php echo t("Click here to create")?>"                            
                           outline
                           class="radius6"
                           no-caps>                           
                        </q-btn>
                        </div>                        
                    </div>                 
                </template>

                <q-space class="q-pa-md"></q-space>
                
                <div class="row q-gutter-md" >
                    <template
                        v-for="items in getTableList"
                        :key="items"
                    >
                        <div class="col-3 relative-position">                              
                            <div
                            v-ripple 
                            class="rounded-borders full-width cursor-pointer row items-stretch q-pa-sm text-grey-8"                                                        
                            style="height: 9em;"   
                            :class="getStatusClass(items.status_class)"       
                            @click="showSelectGuestNumber(items)"                  
                            >
                            <div class="column col-12">
                                <div class="col">
                                    <div class="flex justify-between items-center">
                                        <div class="text-weight-regular">{{items.table_name}}</div>
                                        <div class="colx">                                                                                                                               
                                            <q-chip color="primary" text-color="white" icon="person">
                                                {{ items.min_covers }} - {{ items.max_covers }}
                                            </q-chip>                                            
                                        </div>
                                    </div>
                                </div>
                                <div class="col relative-position">
                                    <div class="absolute-bottom-left text-weight-regular">
                                        {{ items.status }}
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>
                    </template>
                </div>
                <!-- row -->
            
            </template>
            <template v-else>

            <template v-if="!hasItems && !items_loading">
                   <div class="card-form flex flex-center">
                        <div class="text-body2 text-grey text-center">
                           <div class="q-mb-md"><?php echo t("No available Food items")?></div>                           
                           <q-btn                            
                           href="<?php echo Yii::app()->createAbsoluteUrl("/food/item_create")?>"                           
                           icon="add" 
                           color="primary" 
                           label="<?php echo t("Available")?>"                            
                           outline
                           class="radius6"
                           no-caps>
                        </q-btn>
                        </div>                        
                    </div>                 
            </template>
                        
            <!-- ITEMS HERE -->            
            <!-- <template v-if="menu_layout=='column'" > -->
                <div class="row" >                
                    <template v-for="items in getItems" :key="items">            
                        <div class="borderx" :class="{'col-3':this.$q.screen.gt.sm , 'col-6':this.$q.screen.lt.md}" > 
                            <div class="q-pa-xs">
                            <q-list>
                                <q-item clickable v-ripple @click="viewItems(items)">
                                    <q-item-section>
                                        <q-item-label>
                                        <q-img
                                        :src="items.url_image"
                                            style="height: 9em;"
                                            fit="scale-down"
                                            loading="lazy"
                                            spinner-color="primary"
                                            spinner-size="xs"
                                            class="rounded-borders"
                                        >
                                        </q-img>
                                        </q-item-label>
                                        <q-item-label lines="1" class="text-weight-regular">
                                            <span v-html="items.item_name"></span>
                                        </q-item-label>
                                        <q-item-label caption lines="1" class="text-weight-light">
                                            <span v-html="items.item_description"></span>
                                        </q-item-label>    

                                        <q-item-label v-if="items?.promo_data?.message" class="text-weight-light">
                                            <q-badge :color="isEligible(items) ? 'green' :'amber' " outline multi-line text-color="black" :label="getPromoMessage(items)" />
                                        </q-item-label>    
                                        
                                        <!-- =>{{ isEligible(items) }} -->

                                        <q-item-label class="flex justify-between items-center">
                                            <div class="text-blue text-weight-medium text-caption">
                                            <template
                                                v-for="(prices, index) in items.price"
                                                :key="prices"
                                                >
                                                <template v-if="index <= 0">
                                                    <template v-if="prices.discount > 0">{{
                                                    prices.pretty_price_after_discount
                                                    }}</template>
                                                    <template v-else>{{ prices.pretty_price }}</template>
                                                </template>
                                                </template>
                                            </div>
                                            <div>                                            
                                                <q-btn icon="add" size="xs" outline round color="grey" ></q-btn>
                                            </div>
                                        </q-item-label>
                                    </q-item-section>                                       
                                </q-item>
                            </q-list>
                            </div>
                        </div>
                    </template>                
                </div>        
                <!-- row -->
            <!-- </template> -->

            </template> 
            <!-- end if table empty -->
          

            </template>
            <!-- LOADING -->

         </template>
         <!-- END IF SEARCH -->

      </div>
      <!-- ITEMS -->
      

    
      <!-- BACK TO TOP -->
      <q-page-scroller position="bottom-center" :scroll-offset="150" :offset="[18, 18]">
            <q-btn fab icon="keyboard_arrow_up" unelevated padding="10px" color="primary" />
      </q-page-scroller>          

      <components-customer 
      ref="customer"
      @after-createcustomer="afterCreatecustomer"
      >
      </components-customer>

            
      <components-item ref="item_details"
       @after-addtocart="afterAddtocart"
       @item-show="itemShow"
       @item-hide="itemHide"
       :transaction_type="transaction_type"
       :edit_cart="pos_edit_cart"
       :customer_id="customerID"
      >
      </components-item>

      <components-addresslist
      ref="address_list"
      :client_id="customer_data.id"
      @show-newaddress="showNewaddress"
      @after-selectaddress="afterSaveaddress" 
      >      
      </components-addresslist>
      
      <components-newaddress
      ref="new_address"
      :client_id="customer_data.id"
      :attributes_data="attributes_data"
      @after-saveaddress="afterSaveaddress"      
      >    
      </components-newaddress>

      <components-promo 
      ref="promo"
      @refresh-cart="refreshCart"
      label="<?php echo t("Enter Promo code")?>"
      field_type="text"
      filed_name="promo_code"
      method_name="applyPromoCode"
      icon=""
      :transaction_type="transaction_type"
      >      
      </components-promo>

      <components-discount 
      ref="discount"
      @refresh-cart="refreshCart"
      label="<?php echo t("Discount")?>"
      field_type="number"
      filed_name="discount"
      method_name="applyDiscount"
      icon="percent"
      :transaction_type="transaction_type"
      >      
      </components-discount>

      <components-tips 
      ref="tips"
      @refresh-cart="refreshCart"
      label="<?php echo t("Tips")?>"
      field_type="number"
      filed_name="tips"
      method_name="applyTips"
      icon=""
      :transaction_type="transaction_type"
      > 
      </components-tips>
      
      <components-points 
      ref="points"      
      @refresh-cart="refreshCart"      
      :use_thresholds="attributes_data.use_thresholds"
      :client_id="customer_data.id"
      >      
      </components-points>
            
      <components-addtotal 
      ref="addtotal"
      @refresh-cart="refreshCart"
      label="<?php echo t("Apply Total Manually")?>"
      field_type="number"
      filed_name="total"
      method_name="addTotal"
      icon=""
      :transaction_type="transaction_type"
      >      
      </components-addtotal>

      <components-holdorder
      ref="holdorder"
      @after-holdcart="afterHoldcart"
      >
      </components-holdorder>

      <components-payment
      ref="payment"
      @after-payment="afterPayment"
      :attributes_data="attributes_data"
      :transaction_list="transaction_list"
      :transaction_type="transaction_type"
      :cart_total="cart_total"
      >
      </components-payment>     
      
      <components-receipt
      ref="receipt"
      >
      </components-receipt>

      <components-selecttable
      ref="selectable"
      label="<?php echo t("Please enter guest number")?>"  
      @after-addguestnumber="afterAddguestnumber"    
      >
      <!-- @after-addtocart="afterAddtocart" -->
      </components-selecttable>      
      
            
      <components-makepayment
      ref="makepayment"
      title="<?php echo t("Payment")?>"
      :attributes_data="attributes_data"
      :transaction_type="transaction_type"
      :cart_total="cart_total"
      :room_uuid="room_uuid"      
      :table_uuid="table_uuid"
      :skip_kitchen="skip_kitchen"
      :whento_deliver="whento_deliver"
      :delivery_date="delivery_date"
      :delivery_time="delivery_time"
      @after-payment="afterPayment"
      >
      </components-makepayment>
      
      <components-choose-receipt 
      ref="choose_receipt"
      title="<?php echo t("Go paperless and green, send receipt to customer's email or whatsApp")?>"
      @web-print="webPrint"
      :enabled_email="true"
      :enabled_whatsapp="whatsAppEnabled"
      :enabled_webprint="true"
      :enabled_print="true"
      :printer_list="printerList"
      >
      </components-choose-receipt>

      <components-continuesalert
      ref="continues_alert"      
      :enabled_interval="<?php echo $enabled_tableside_alert?>"
      :interval_seconds="<?php echo $interval_seconds?>"
      @on-alertactions="onAlertactions"
      >
      </components-continuesalert>

      <components-requestitem
      ref="request_item"
      >
      </components-requestitem>

      <components-barcode
      ref="ref_barcode"
      :customer_id="customerID"
      @after-scan="afterScan"
      >
      </components-barcode>

      <template v-if="this.$q.screen.lt.md && drawer==false">  
        <!-- cart here -->
          <q-page-sticky position="bottom-right" :offset="[18, 18]">            
            <q-btn @click="drawer=!drawer" round icon="las la-shopping-bag" size="18px" unelevated color="light" text-color="grey-7">
                <template v-if="items_count>0">
                <q-badge rounded  color="red" floating>
                    {{items_count}}
                </q-badge>
                </template>
            </q-btn> 
          </q-page-sticky>
      </template>

  </q-page>
</q-page-container>

</q-layout>

</DIV>

<script type="text/x-template" id="xtemplate_search_customer">
<q-select
    v-model="customer_name"
    ref="customer"
    use-input
    hide-selected
    fill-input
    input-debounce="0"
    :options="options"
    @filter="searchCustomer"	
    @update:model-value="onSelect"
    @input-value="setModel"
    @clear="Clear"
    hide-dropdown-icon
    :loading="loading"
    borderless
    color="primary"
    dense
    clearable
    outlined
    clear-icon="clear"
    placeholder="<?php echo t("Search customer name")?>"    
>            
    <template v-slot:no-option>
        <q-item>
        <q-item-section class="text-grey">            
            <?php echo t("No results")?>
        </q-item-section>
        </q-item>
    </template>
</q-select>
</script>    

<script type="text/x-template" id="xtemplate_customer">
<?php $this->renderPartial("/pos/customer-add");?>
</script>

<script type="text/x-template" id="xtemplate_item_details">
<?php $this->renderPartial("/pos/item-details");?>
</script>

<script type="text/x-template" id="xtemplate_address_list">
<?php $this->renderPartial("/pos/customer-address-list");?>
</script>

<script type="text/x-template" id="xtemplate_new_address">
<?php $this->renderPartial("/pos/customer-new-address");?>    
</script>

<script type="text/x-template" id="xtemplate_discount">
<?php $this->renderPartial("/pos/discount");?>    
</script>

<script type="text/x-template" id="xtemplate_points">
<?php $this->renderPartial("/pos/points");?>    
</script>

<script type="text/x-template" id="xtemplate_holdorder">
<?php $this->renderPartial("/pos/holdorder");?>    
</script>

<script type="text/x-template" id="xtemplate_createpayment">
<?php $this->renderPartial("/pos/create-payment");?>    
</script>

<script type="text/x-template" id="xtemplate_receipt">
<?php $this->renderPartial("/pos/receipt");?>    
</script>

<script type="text/x-template" id="xtemplate_select_table">
<?php $this->renderPartial("/pos/select-table");?>    
</script>

<script type="text/x-template" id="xtemplate_makepayment">
<?php $this->renderPartial("/pos/make-payment");?>    
</script>

<script type="text/x-template" id="xtemplate_orders">
<?php $this->renderPartial("/pos/pos-orders");?>    
</script>

<script type="text/x-template" id="xchoose_receipt">
<?php $this->renderPartial("/pos/choose-receipt");?>    
</script>

<script type="text/x-template" id="xnotifications">
<?php $this->renderPartial("/pos/notifications");?>    
</script>

<script type="text/x-template" id="xrequest_item_details">
<?php $this->renderPartial("/pos/request-item-details");?>    
</script>

<script type="text/x-template" id="xrequest_list">
<?php $this->renderPartial("/pos/request-list");?>    
</script>

<script type="text/x-template" id="xbarcode_template">
<q-dialog v-model="modal" @before-show="onBeforeshow" @before-hide="onBeforehide" persistent >
<q-card style="width: 450px">
 <q-card-section class="row items-center q-pb-none">
    <div class="text-h6"><?php echo CommonUtility::safeTranslate("Scan Barcode")?></div>
    <q-space />
    <q-btn icon="close" flat round dense v-close-popup />
 </q-card-section>
  <q-card-section style="min-height: 50vh">    
     <template v-if="!is_scan">
      <q-btn @click="startScan" :loading="loading" color="primary" label="<?php echo CommonUtility::safeTranslate("Start Scan")?>" no-caps class="absolute-center" ></q-btn>
     </template>
     <template v-else>
        <div class="text-center">    
        
           <q-btn @click="stopScan" :loading="loading" color="red" label="<?php echo CommonUtility::safeTranslate("Stop Scan")?>" no-caps  ></q-btn>
           
           <div class="reader_wrapper q-mt-sm">
              <q-circular-progress    
                v-if="scan_loading"
                indeterminate
                size="50px"
                :thickness="0.22"
                rounded
                color="primary"
                track-color="grey-3"          
                class="absolute-center"      
                >
                </q-circular-progress>
              <div id="reader" ref="ref_reader"></div>
           </div>
        </div>
     </template>
  </q-card-section>
</q-card>
</q-dialog>
</script>
