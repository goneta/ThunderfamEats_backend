<q-dialog
v-model="modal"    
:maximized="this.$q.screen.lt.sm?true:false"
:position="this.$q.screen.lt.sm?'bottom':'standard'"
@before-show="beforeShow"
@show="OnShow"
persistent
>
<q-card  class="full-height" style="width: 360px">
   <q-card-section class="row items-center q-pb-none">
        <div class="text-h6 text-subtitle1">Receipt</div>
        <q-space />
        <q-btn icon="close" color="grey" flat round dense v-close-popup ></q-btn>
    </q-card-section>

    <!-- style="max-height: 65vh;" -->
    <q-card-section class="receipt printhis"   >        
    <template v-if="loading">

        <div class="q-gutter-y-sm">
        <template v-for="items in 2">
            <div><q-skeleton height="200px" square ></q-skeleton></div>
        </template>
        <template v-for="items in 2">
            <div><q-skeleton height="20px" square ></q-skeleton></div>
        </template>
        <template v-for="items in 1">
            <div><q-skeleton height="200px" square ></q-skeleton></div>
        </template>
        </div>

    </template>
    <template v-else>
        <div class="text-center q-mb-md">
            <div class="text-h6">{{merchant.restaurant_name}}</div>
            <div class="text-caption">Adddress: {{merchant.address}}</div>
            <div class="text-caption">Tel: {{merchant.contact_phone}}</div>
        </div>

        <DIV class="text-caption">
            <div class="dashed-line q-mb-xs"></div>

            <div class="row justify-between">
                <div class="col">Order ID:</div>
                <div class="col text-right">{{order_info.order_id}}</div>
            </div>
            
            <div class="row justify-between">
                <div class="col">Customer:</div>
                <div class="col text-right">
                    <template v-if="order_info.client_id>0">{{order_info.customer_name}}</template>                    
                    <template v-else>Walk-in Customer</template>
                </div>
            </div>

            <div class="row justify-between">
                <div class="col">Phone:</div>
                <div class="col text-right">{{order_info.contact_number}}</div>
            </div>

            <template v-if="order_info.order_type=='delivery'">
            <div class="row justify-between">
                <div class="col">Address:</div>
                <div class="col text-right line-normal">{{order_info.address1}} {{order_info.delivery_address}}</div>
            </div>
            </template>
            
            <div class="row justify-between">
                <div class="col">Order Type:</div>
                <div class="col text-right">
                   <template v-if="services[order_info.service_code]" > {{services[order_info.service_code].service_name}}</template>
                </div>
            </div>

            <div class="row justify-between">
                <div class="col">
                    <template v-if="order_info.order_type=='delivery'">
                    Delivery Date/Time:
                    </template>
                    <template v-else>
                    Date/Time:
                    </template>
                </div>
                <div class="col text-right">                    
                    {{order_info.schedule_at}}
                </div>
            </div>

            <div class="row justify-between">
               <div class="col">{{order_info.payment_name}}</div>                
            </div>

            <template v-if="hasBooking">
                <div class="row justify-between">
                    <div class="col">Guest:</div>
                    <div class="col text-right">{{order_table_data.guest_number}}</div>
                </div>
                <div class="row justify-between">
                    <div class="col">Room name:</div>
                    <div class="col text-right">{{order_table_data.room_name}}</div>
                </div>
                <div class="row justify-between">
                    <div class="col">Table name:</div>
                    <div class="col text-right">{{order_table_data.table_name}}</div>
                </div>
            </template>

            <div class="dashed-line q-mt-xs q-mb-xs"></div>            
            <div class="row">
                <div class="col-2">Qty</div>
                <div class="col-7">Item Description</div>
                <div class="col-3 text-right">Price</div>
            </div>
            <div class="dashed-line q-mt-xs q-mb-xs"></div>
    
            <template v-for="(items,index) in order_items" :key="items">
                <div class="row">
                    <div class="col-2">{{items.qty}}</div>
                    <div class="col-7" v-html="items.item_name"></div>
                    <div class="col-3 text-right">
                        <template v-if="items.price.discount<=0 ">
                        {{ items.price.pretty_total }}
                        </template>
                        <template v-else>
                        {{ items.price.pretty_total_after_discount }}
                        </template>	     
                    </div>
                </div>
                <!-- row -->

                <template v-if=" items.price.size_name!='' "> 
                <div class="row">
                    <div class="col-2"></div>
                    <div class="col">({{items.price.size_name}})</div>
                </div>
                </template>

                <template v-if="items.item_changes=='replacement'">
                <div class="row">
                    <div class="col-2"></div>
                    <div class="col">Replace {{items.item_name_replace}}</div>
                </div>
                </template>

                <template v-if=" items.special_instructions!='' ">
                <div class="row">
                    <div class="col-2"></div>
                    <div class="col">{{items.special_instructions}}</div>
                </div>
                </template>

                <template v-if=" items.attributes!='' "> 
                <div class="row">
                    <div class="col-2"></div>
                    <div class="col">
                        <template v-for="(attributes, attributes_key) in items.attributes">
                          <template v-for="(attributes_data, attributes_index) in attributes">            
	                        {{attributes_data}},
	                      </template>
                        </template>
                    </div>
                </div>
                </template>


                <!-- addon -->
                <template v-for="(addons, index_addon) in items.addons" >
                <div class="row">
                   <div class="col-2"></div>
                   <div class="col"><span class="circle-symbol">●</span> {{addons.subcategory_name}}</div>
                </div>                
                <template v-for="addon_items in addons.addon_items">
                <div class="row">                    
                    <div class="col-2"></div>
                    <div class="col-7">{{addon_items.qty}} x {{addon_items.pretty_price}} {{addon_items.sub_item_name}}</div>
                    <div class="col-3 text-right">{{addon_items.pretty_addons_total}}</div>
                </div>             
                </template>
                </template>   
                <!-- addon -->

                <!-- <div class="q-pa-xs"></div> -->                                
                <template v-if="(index+1)<order_items.length">
                   <q-separator class="q-mb-xs q-mt-xs"></q-separator>
                </template>
                

            </template>       
            <!-- end of items      -->

            <div class="dashed-line q-mt-xs q-mb-xs"></div>   
            
            <template v-for="summary in order_summary">
            <div class="row">
                <div class="col-2"></div>
                <div class="col-7" :class="{'text-weight-bold':summary.type=='total'}">{{summary.name}}:</div>
                <div class="col-3 text-right" :class="{'text-weight-bold':summary.type=='total'}">{{summary.value}}</div>
            </div>          
            </template>            

            <div class="dashed-line q-mt-xs q-mb-sm"></div>
             <div>{{order_info.place_on}}</div>             
            <div class="dashed-line q-mt-xs q-mb-xs"></div>  

            <div class="text-center font12">
                <div>Thank you for your order</div>
                <div>Please visit us again.</div>
            </div>

        </DIV>

        </template> 
        <!-- end loading -->
    </q-card-section>

    <q-card-actions class="border-top" align="center">
        <template v-if="!loading">
        <q-btn @click="printReceipt" :loading="is_printing" unelevated no-caps label="Print" color="primary" size="lg" style="width: 100px;" ></q-btn>
        <q-btn v-close-popup unelevated no-caps label="Close" color="grey-5" size="lg" style="width: 100px;"  ></q-btn>
        </template>
    </q-card-actions>
    </q-form>
    
</q-card>
</q-dialog>