<DIV id="app-tableside">

<q-layout view="hHr lpR fFf"  v-cloak >

<q-header class="bg-white text-dark"  >    
  <!-- <div class="flex q-gutter-x-sm" >
    <div>
        <q-btn label="Ongoing orders" unelevated no-caps color="green-5" class="radius15" ></q-btn>
    </div>
    <div>
        <q-btn label="Order history" unelevated no-caps color="white" text-color="dark"></q-btn>
    </div>
  </div> -->
    <q-tabs
    v-model="tab"    
    no-caps
    active-bg-color="primary radius15"
    active-color="white"
    indicator-color="transparent"
    dense    
    >
    <q-tab name="ongoing" label="Ongoing Orders" ></q-tab>
    <q-tab name="orderhistory" label="Order History" ></q-tab>    
    </q-tabs>
</q-header>

<q-drawer v-model="drawer_left" side="left" 
show-if-above
:width="200"
:breakpoint="500"
:mini="miniState"
@mouseover="miniState = false"
@mouseout="miniState = true"
>
<q-scroll-area class="fit" :horizontal-thumb-style="{ opacity: 0 }">

<q-list padding class="q-gutter-y-md q-pa-sm q-pt-lg">
  

<q-item clickable v-ripple class="text-grey-5">
    <q-item-section avatar>
       <q-icon name="table_restaurant" ></q-icon>
   </q-item-section> 
   <q-item-section>
       <q-item-label>Table manage</q-item-label>
    </q-item-section>  
  </q-item>

  <q-item clickable v-ripple class="text-grey-5">
    <q-item-section avatar>
       <q-icon name="local_mall" ></q-icon>
   </q-item-section> 
   <q-item-section>
       <q-item-label>Orders</q-item-label>
    </q-item-section>  
  </q-item>

</q-list>
</q-scroll-area>
</q-drawer>

<q-drawer v-model="drawer" side="right" 
show-if-above
:width="450"
:breakpoint="1023"
class="bg-grey-2"
>

<div class="q-pa-md border-bottom bg-white">
    <div class="text-h6">Order ID#10002</div>
    <div class="flex justify-between">
        <div>John doe</div>
        <div>Dine-in &bull; T-10</div>
    </div>
</div>

<div class="q-pa-md bg-white scroll card-small-1">
<q-list>
   <template v-for="items in 5" :key="items">
    <q-item :active="items%2?true:false" active-class="bg-grey-2 text-dark q-mb-sm radius5">
       <q-item-section avatar>
         <q-item-label>
            1
         </q-item-label>
       </q-item-section>
       <q-item-section>
         <q-item-label>
            Cheese burger
         </q-item-label>
         <q-item-label caption>
            medium half grilled
         </q-item-label>
       </q-item-section>
       <q-item-section side>
         <q-item-label>
            25.00$
         </q-item-label>
       </q-item-section>
    </q-item>
   </template>
</q-list>
</div>

<div class="q-pl-md q-pr-md">
<q-list dense >
    <q-item>
       <q-item-section avatar>
         <q-item-label class="text-weight-bold">
            Sub total
         </q-item-label>
       </q-item-section>
       <q-item-section></q-item-section>
       <q-item-section side>
         <q-item-label class="text-weight-bold">
            25.00$
         </q-item-label>
       </q-item-section>        
    </q-item>
    <q-item>
       <q-item-section avatar>
         <q-item-label class="text-weight-bold">
            Tax
         </q-item-label>
       </q-item-section>
       <q-item-section></q-item-section>
       <q-item-section side>
         <q-item-label class="text-weight-bold">
            25.00$
         </q-item-label>
       </q-item-section>        
    </q-item>
    <q-item>
       <q-item-section avatar>
         <q-item-label class="text-weight-bold">
            Total
         </q-item-label>
       </q-item-section>
       <q-item-section></q-item-section>
       <q-item-section side>
         <q-item-label class="text-weight-bold">
            25.00$
         </q-item-label>
       </q-item-section>        
    </q-item>
</q-list>
<q-space class="q-pa-sm"></q-space>
<q-btn no-caps label="Print Invoice" icon="print" unelevated color="green-6" text-color="white" class="fit" size="18px" ></q-btn>

</div>

</q-drawer>

<q-page-container >
  <q-page padding class="bg-cream">

  <!-- <q-card flat class="radius5 q-pa-sm">
    <q-card-content >
        xx
    </q-card-content>
  </q-card> -->
  
  <q-card flat class="radius5 q-pa-sm">
    <q-card-content >    
        <q-input outlined v-model="text" label="Search Order ID or table number" dense color="yellow-9" class="bg-white"  >
            <template v-slot:prepend>
                <q-icon name="search" />
            </template>
        </q-input>
        <div class="border-top q-mt-md q-pt-md">
            <q-list separator>
                <template v-for="items in 10" :key="items">
                <q-item clickable >
                    <q-item-section>
                        <q-item-label>
                            #10001
                        </q-item-label>
                        <q-item-label caption>
                            28 Feb 2021 10:00am
                        </q-item-label>
                    </q-item-section>
                    <q-item-section side>
                       <q-item-label>
                            100.00$
                        </q-item-label>
                    </q-item-section>
                </q-item>
                </template>
            </q-list>
        </div>
    </q-card-content>
  </q-card>


  </q-page>
</q-page-container>

</q-layout>

</DIV>