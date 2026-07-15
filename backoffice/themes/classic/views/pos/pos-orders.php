
<q-ajax-bar ref="bar" position="top" color="blue" size="3px" skip-hijack ></q-ajax-bar>

<div class="row justify-between items-center">
    <div class="col-4 ">
        <template v-if="this.$q.screen.lt.sm">
            <div class="text-body2">
            {{ title }}
            </div>
        </template>
        <template v-else>
            <div class="text-h6">
                {{ title }}
            </div>
        </template>        
    </div>
    <div class="col-4 text-right q-gutter-x-sm">
       
       <template v-if="hasFilter">
       <q-btn  icon="refresh" 
        no-caps unelevated
        color="white"
        text-color="grey-7"
        outline                
        class="radius6"        
        dense
        @click="refreshData"
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
        <q-popup-proxy v-model="proxy">
           <q-card style="min-width:300px;">
            <q-card-section class="text-body2 text-grey-7">
                
                <div class="q-mb-sm"><?php echo t("Order Type")?></div>
                <q-select                
                v-model="order_type"
                :options="transaction_list"
                label="<?php echo t("Select Order Type")?>"
                multiple
                emit-value
                map-options
                dense
                outlined 
                >
                <template v-slot:option="{ itemProps, opt, selected, toggleOption }">
                <q-item v-bind="itemProps">
                    <q-item-section avatar>
                      <q-checkbox :model-value="selected" @update:model-value="toggleOption(opt)" ></q-checkbox>
                    </q-item-section>
                    <q-item-section>
                    <q-item-label v-html="opt.label" />
                    </q-item-section>                    
                </q-item>
                </template>
            </q-select>

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
</div>

<template v-if="!hasData && !loading">
    <div class="card-form flex flex-center">
        <div class="text-body2 text-grey">          
          <?php echo t("No available data")?>
        </div>
    </div>
</template>

<q-inner-loading  
:showing="loading" 
color="primary"
>
</q-inner-loading>


<div class="row">
    <template
        v-for="(items,index) in data"
        :key="items"
    >
    <div                     
    :class="{'col-3':this.$q.screen.gt.sm , 'col-6':this.$q.screen.lt.md}" 
    class="relative-position"                    
    >
        <div class="q-pa-sm">
        <div
        v-ripplex
        class="rounded-borders full-width cursor-pointerx q-pa-sm text-grey-7"    
        :class="getStatusClass(items.status_class)"           
        style="min-height: 9em;"                                   
        >
        <!-- @click.stop="this.$emit('loadOrders',items)" -->
        
            <div class="flex justify-between items-center">
                <div class="text-weight-medium">
                    <template v-if="transaction_type=='hold_orders'">
                     {{ items.hold_order_reference }}
                    </template>
                    <template v-else>
                    #{{ items.order_reference }}
                    </template>
                </div>
                <div class="text-capitalize text-weight-medium">{{ items.transaction_type }}
                    <template v-if="items.table_name">
                    [ {{items.table_name}} ]
                    </template>
                </div>
            </div>

            <div class="flex justify-between items-center">
                <div>{{ items.customer_name }}</div>
                <div class="text-weight-bold text-blue">{{items.total_pretty}}</div>
            </div>

            <div class="flex justify-between items-center">
                <div>{{ items.date_pretty }}</div>
                <div>
                    <!-- <q-chip dense color="transparent" text-color="grey-7" icon="schedule"> -->
                      <components-elapsetime 
                      :start="items.date"
                      :timezone="items.timezone"
                      >
                      </components-elapsetime>
                    </q-chip>   
                </div>
            </div>
                        
            <q-list dense class="modified-expansion q-mb-sm" >
                <q-expansion-item
                    expand-separator                                
                    :label="items.total_items"                                
                >                          
                <q-card>
                    <q-table                    
                    :rows="items.items"
                    :columns="columns"
                    row-key="name"
                    :hide-pagination="true"
                    >
                    <template v-slot:body="props">
                       <q-tr :props="props" >
                         <q-td key="item_name" :props="props">
                            {{ props.row.item_name }}
                         </q-td>
                         <q-td key="qty" :props="props">
                            {{ props.row.qty }}
                         </q-td>
                         <q-td key="status" :props="props">                                     
                            <q-badge outline :color="getItemStatus(props.row.status_raw)"  >
                              {{ props.row.status }}
                            </q-badge>
                         </q-td>
                       </q-tr>
                    </template>
                    </q-table>
                </q-card>
                </q-expansion-item>
            </q-list>   
            
            <div class="row q-gutter-x-sm">         
                        
            <q-btn 
            class="col radius6" 
            unelevated 
            color="purple-6" 
            icon="las la-print" 
            no-caps size="17px" 
            label="<?php echo t("Ticket")?>"
            :disabled="!hasPrinter"
            >            
            <q-menu>
               <q-list style="min-width: 200px" separator>                  
                  <template v-for="printer in printer_list" :key="printer">
                  <q-item clickable v-close-popup @click="SwitchPrinter(items.cart_uuid,printer.printer_id,printer.printer_model)">
                     <q-item-section>
                        {{printer.printer_name}}
                     </q-item-section>
                  </q-item>
                  </template>
               </q-list>
            </q-menu>      
            </q-btn>       

            <q-btn color="primary" unelevated no-caps label="<?php echo t("View")?>" class="radius6 col"
            @click.stop="this.$emit('loadOrders',items)" 
            >
            </q-btn>

            <q-btn color="red" unelevated no-caps label="<?php echo t("Delete")?>" class="radius6 col"            
            @click="deleteConfirm(items,index)"
            >
            </q-btn>
            </div>

        </div>
        <!-- v-ripple -->
        </div>
        
    </div>
    <!-- col -->
    </template>
</div>