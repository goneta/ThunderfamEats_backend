<DIV id="app-pos-hold">

<q-layout view="hHr lpR fFf"  v-cloak >

<template v-if="this.$q.screen.lt.sm">      
<q-footer unelevated class="bg-cream">        
<q-tabs
    v-model="mobile_tab"
    dense
    indicator-color="transparent"
    active-color="primary"
    active-bg-color="secondary"        
    class="tabs-modified text-grey-5  bg-white"
    dense
  >
  <q-route-tab
      name="pos"
      icon="adf_scanner"
      no-caps      
      label="POS"              
   >
  </q-route-tab>
  <q-route-tab
      name="hold"
      icon="pause_circle_outline"
      no-caps      
      label="Hold"  
   >
  </q-route-tab>
  <q-route-tab
      name="orders"
      icon="local_mall"
      no-caps      
      label="Orders"  
   >
  </q-route-tab>
  <q-route-tab
      name="customer"
      icon="person_outline"
      no-caps      
      label="Customer"  
   >
  </q-route-tab>
</q-tabs>
</q-footer>
</template>

<q-page-container >
  <q-page padding class="bg-cream text-cream">
  
    
  <q-card flat class="radius5 q-pa-sm">
    <q-card-content >    
        <q-input v-model="q" label="Search Order reference" dense color="grey-5"  :loading="awaitingSearch"  >
            <template v-slot:prepend>
                <q-icon name="search" />
            </template>
            <template v-slot:append>
                <template v-if="isSearch">
                <q-btn @click="clearSearch" flat label="Clear" color="dark" no-caps class="text-weight-regular"></q-btn>
                </template>
            </template>
        </q-input>        
    </q-card-content>
    </q-card>

    <q-space class="q-pa-sm"></q-space>

    <template v-if="loading">
    
       <div class="row">
          <template v-for="(item,item_index) in 12" :key="items">
             <div class="q-pa-sm col-4" :class="{'col-4':this.$q.screen.gt.sm , 'col-12':this.$q.screen.lt.sm}" >
               <q-skeleton height="150px" square></q-skeleton>            
             </div>
          </template>
       </div>

    </template>
    <template v-else>
      <template v-if="!hasData">
        <div class="card-medium flex flex-center">
          <div class="text-grey">No available data</div>
        </div>
      </template>
    </template>

    <template v-if="isSearch">

        <div class="text-h5">Search for "{{q}}"</div>
        <template v-if="!searchResults && !awaitingSearch">
              <div class="text-body2">Sorry, no order reference matched for your search. Please try again.</div>
        </template>

    </template>
    
      <template v-if="hasData">
      <div style="min-height: calc(75vh);">
      <div class="row">
        <template v-for="(item,item_index) in data" :key="items">
        <div class="q-pa-sm" :class="{'col-4':this.$q.screen.gt.sm , 'col-12':this.$q.screen.lt.sm}" > 
          <div class="bg-white box-shadow radius5">
            <q-item>
              <q-item-section>
                  <q-item-label class="text-weight-medium text-capitalize">{{item.customer_name}}</q-item-label>
                  <q-item-label caption>{{item.date_created}}</q-item-label>
              </q-item-section>          
              <q-item-section side top>
                  <q-item-label class="text-weight-medium">&bull; {{item.order_reference}}</q-item-label>
              </q-item-section>   
            </q-item>          
            
            <q-separator></q-separator>

            <template v-for="(item_token,index) in item.items_data">
            <div class="scroll" style="max-height: calc(25vh);">
            <q-item :active="index%2?true:false" active-class="bg-grey-2 text-dark">
              <q-item-section avatar>
                  <q-item-label>
                    <template v-if="item_data[item_token]">                     
                      <span v-html="item_data[item_token]"></span>
                    </template>                  
                    <template v-else>N/A</template>
                  </q-item-label>                
              </q-item-section>             
              <q-item-section></q-item-section>
              <q-item-section side>
                  <q-item-label>{{item.qty}}</q-item-label>                
              </q-item-section>             
            </q-item>
            </div>
            </template>

            <q-separator class="q-mt-sm"></q-separator>

            <q-card-actions class="q-pl-md q-pr-md">
                <q-btn @click="resumeHoldorder(item)" unelevated label="Resume" icon="arrow_circle_right" color="green-5" no-caps></q-btn>
                <q-btn @click="confirmDelete(item.cart_uuid,item_index)" unelevated label="Remove" icon="delete" color="orange" outline no-caps></q-btn>
            </q-card-actions>
          </div>
        </div>
        </template>
      </div>
      <!-- row -->
      </div>
      
    
       <div class="q-pa-lg flex flex-center">
         <q-pagination
            v-model="page"
            :max="page_count"
            @update:model-value="Paginate"
            unelevated
            color="dark"
            direction-links
         ></q-pagination>
        </div>


      </template>
  

  </q-page>
</q-page-container>

</q-layout>

</DIV>