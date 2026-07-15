<div class="row justify-between items-center">
   <div class="col-4 ">
       <div class="text-h6"><?php echo t("Customer Request")?></div>
   </div>
   <div class="col-4"></div>
</div>
<!-- row -->


<q-inner-loading  
:showing="loading" 
color="primary"
>
</q-inner-loading>

<template v-if="!hasData && !loading">
    <div class="card-form flex flex-center">
        <div class="text-body2 text-grey text-center">
            <div class="q-mb-md"><?php echo t("No available data")?></div>
        </div>
    </div>
</template>


<div class="row">
    <template
        v-for="(items,index) in data"
        :key="items"
    >
    <div 
    :class="{'col-3':this.$q.screen.gt.sm , 'col-6':this.$q.screen.lt.md}"     
    >
    <div class="q-pa-sm">
        <q-card flat bordered >            
           <div class="bg-primary text-white q-pa-sm row items-center">
             <div class="col text-weight-regular text-body1 line-normal ellipsis">                
                <div class="text-caption">#{{items.room_name}}-{{items.table_name}}</div>
                <div class="text-overline text-uppercase" style="line-height: normal;">{{ items.transaction_type_pretty }}</div>
             </div>
             <div class="col text-right text-weight-light">                
                <components-elapsetime 
                :start="items.request_time"
                :timezone="items.timezone"
                >
                </components-elapsetime>
             </div>
           </div>
           <!-- row -->
            <q-list separator dense>
                <template v-for="item_request in items.items">
                <q-item tag="label" v-ripple>
                    <q-item-section avatar>                        
                        <q-checkbox                         
                        color="primary"      
                        v-model="item_request.checked"                        
                        >
                        </q-checkbox>
                    </q-item-section>
                    <q-item-section>
                    <q-item-label>{{item_request.qty}} x {{item_request.request_item}}</q-item-label>
                    </q-item-section>
                </q-item>
                </template>
            </q-list>            

            <q-card-section>                                        
                <q-btn outline 
                color="blue" 
                label="<?php echo t("Completed")?>" 
                class="fit radius6" 
                no-caps             
                :disabled="!hasValue(items.table_uuid)"   
                @click="setCompleted(items.table_uuid)"
                :loading="isLoading(items.table_uuid)"
                >        
                </q-btn>              
            </q-card-section>

        </q-card>        
    </div>
    </div>
    <!-- col -->
    </template>
</div>
<!-- row -->