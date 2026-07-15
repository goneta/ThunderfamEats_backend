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
:width="350"
:breakpoint="1023"
>

<div class="q-pa-md">

<q-btn-toggle
      v-model="transaction_type"
      color="grey-1"
      text-color="grey-5"
      toggle-color="primary"
      toggle-text-color="white"
      unelevated
      no-caps
      spread      
      :options="[
        {label: 'Dine-in', value: 'Dine-in'},
        {label: 'Takeout', value: 'Takeout'},        
      ]"
    >
</q-btn-toggle>

<q-space class="q-pa-xs"></q-space>  

  <div class="scroll card-small-1">
  <q-list separator>
    <template v-for="items in 2" :key="items">
       <q-item  class="q-pa-smx radius10">
           <q-item-section avatar top>
                <q-img
                src="http://localhost/kmrs2/upload/3/fa6e06df-80aa-11ec-859e-99479722e411.png"
                style="height: 60px; width: 60px"
                loading="lazy"
                fit="cover"
                spinner-color="yellow-9"
                spinner-size="sm"
                class="rounded-borders"
                ></q-img>
          </q-item-section>
          <q-item-section top>
                <q-item-label >
                Cheese burger
                </q-item-label>
                <q-item-label caption >
                100$
                </q-item-label>
                <q-item-label class="borderx">
                     <!-- qty -->
                    <div class="flex items-center q-col-gutter-x-sm">
                        <div class="borderx">
                        <q-btn
                            :icon="items.qty == 1 ? 'delete_outline' : 'remove'"
                            :color="items.qty == 1 ? 'negative' : 'primary'"
                            unelevated
                            size="11px"
                            class="rounded-borders"
                            style="width: 30px"
                            @click="lessCartQty(items.qty > 1 ? items.qty-- : 1, items)"
                            :disable="isLoading"
                        ></q-btn>
                        </div>
                        <div class="borderx">
                        1
                        </div>
                        <div class="borderx">
                        <q-btn
                            icon="add"
                            color="primary"
                            unelevated
                            size="11px"
                            class="rounded-borders"
                            style="width: 30px"
                            @click="addCartQty(items.qty++, items)"
                            :disable="isLoading"
                        ></q-btn>
                        </div>
                    </div>
                    <!-- qty -->
                </q-item-label>
                <q-item-label caption>
                    <div>Special instructions</div>
                </q-item-label>
          </q-item-section>        
          <q-item-section top side>
              <div class="column justify-end items-end fit">
                <div class="col">
                    <q-btn
                    round
                    :color="$q.dark.mode ? 'grey500' : 'grey-4'"
                    icon="clear"
                    size="xs"
                    unelevated
                    @click="removeItem(items)"
                    :disable="isLoading"
                    />
                </div>
                <div
                class="col text-weight-bold relative-position"
                :class="{
                  'text-grey300': $q.dark.mode,
                  'text-negative': !$q.dark.mode,
                }"
                >
                <div class="absolute-bottom-right">
                   100$
                </div>
                </div>
              </div>
          </q-item-section>  
       </q-item>
    </template>
  </q-list>
  </div>

  
    <div class="row q-mb-sm items-center justify-center btn-block border-top q-pt-sm">
       
       <q-btn square color="white" text-color="yellow-9" dense unelevated no-caps size="17px">
        <div class="border-yellow rounded-borders q-pa-xs text-weight-regular text-body2" style="width: 55px;">
           <div><q-icon name="local_offer" size="20px"></q-icon></div>
           <div>Promo</div>
        </div>
        </q-btn>    

        <q-btn square color="white" text-color="yellow-9" dense unelevated no-caps size="17px">
        <div class="border-yellow rounded-borders q-pa-xs text-weight-regular text-body2" >
          <div><q-icon name="percent" size="20px"></q-icon></div>
          <div>Discount</div>
        </div>
        </q-btn>    

        <q-btn square color="white" text-color="yellow-9" dense unelevated no-caps size="17px">
        <div class="border-yellow rounded-borders q-pa-xs text-weight-regular text-body2" style="width: 55px;">
           <div><q-icon name="favorite_border" size="20px"></q-icon></div> 
           <div>Tips</div>
        </div>
        </q-btn>    

        <q-btn square color="white" text-color="yellow-9" dense unelevated no-caps size="17px">
         <div class="border-yellow rounded-borders q-pa-xs text-weight-regular text-body2" style="width: 55px;">
           <div><q-icon name="loyalty" size="20px"></q-icon></div>
           <div>Points</div>
         </div>
        </q-btn>           
    </div>
    
    <q-list class="fit">
        <q-item class="bg-grey-2 text-dark radius5">
            <q-item-section>
                <q-item-label>Sub total (1 items)</q-item-label>
            </q-item-section>
            <q-item-section side>
                <q-item-label>100$</q-item-label>
            </q-item-section>
        </q-item>
        <q-item class="text-weight-bold">
            <q-item-section>
                <q-item-label>Total</q-item-label>
            </q-item-section>
            <q-item-section side>
                <q-item-label>100.00$</q-item-label>
            </q-item-section>
        </q-item>
    </q-list>    

    <q-btn no-caps label="Proceed to pay 200$" rounded unelevated color="green-6" text-color="white" class="fit" size="18px" ></q-btn>

</div>
<!-- padding -->


</q-drawer>

<q-page-container >
  <q-page padding class="bg-cream">

  
  <!-- <q-card flat class="radius5 q-pa-sm q-mb-sm bg-transparent">
    <q-card-content >    
        <q-input outlined v-model="text" label="Search Order ID or table number" dense color="yellow-9" class="bg-white"  >
            <template v-slot:prepend>
                <q-icon name="search" />
            </template>
        </q-input>       
    </q-card-content>
  </q-card> -->

  <div class="row q-mb-sm items-center q-pl-sm">
        <!-- <div class="q-mr-sm">            
            <q-btn square color="white" text-color="grey-5" dense unelevated @click="drawer=!drawer" >
                  <div class="border-grey rounded-borders q-pa-xs">
                    <q-icon name="menu"></q-icon>
                  </div>
              </q-btn>    
        </div> -->
        <div class="col-6">
            <q-input outlined v-model="text" label="Search Order ID or table number" dense color="yellow-9" class="bg-white"  >
            <template v-slot:prepend>
                <q-icon name="search" />
            </template>
            </q-input>
        </div>
        <div class="col  text-right">               
              <q-btn square color="white" text-color="yellow-9" dense unelevated >
                  <div class="border-yellow rounded-borders q-pa-xs">
                    <q-icon name="add"></q-icon>
                  </div>
              </q-btn>            
        </div>
  </div>


  <div class="row justify-center q-pa-none q-ma-none">
    <template v-for="items in 10">
        <div class="q-pa-sm" :class="{'col-6':this.$q.screen.gt.sm , 'col-12':this.$q.screen.lt.sm}" > 
          <div class="bg-white box-shadow radius5">
            <q-item dense>
              <q-item-section avatar>
                <q-item-label>Table no. 1001</q-item-label>
              </q-item-section>
              <q-item-section></q-item-section>
              <q-item-section side>
                <q-item-label>#10001</q-item-label>
              </q-item-section>
            </q-item>
            <q-item dense>
              <q-item-section avatar>
                <q-item-label>Name: Walk-in</q-item-label>
              </q-item-section>
              <q-item-section></q-item-section>
              <q-item-section side>
                <q-item-label>100.00$</q-item-label>
              </q-item-section>
            </q-item>
            <q-item dense>
               <div class="flex justify-between fit items-center">
                  <div><q-btn unelevated no-caps label="Pay" color="primary"></q-btn></div>
                  <div class="q-gutter-x-sm">
                    <q-btn unelevated no-caps icon="visibility" color="grey-1" text-color="grey-5"></q-btn>
                    <q-btn unelevated no-caps icon="edit" color="grey-1" text-color="grey-5"></q-btn>
                    <q-btn unelevated no-caps icon="print" color="grey-1" text-color="grey-5"></q-btn>
                  </div>
               </div>
            </q-item>
            <div class="bg-green-1 text-green-9 text-weight-medium q-mt-sm q-pl-md q-pr-md q-pt-xs q-pb-xs flex justify-between">
              <div>Dine-in</div>
              <div>
                <q-icon name="schedule"></q-icon>
                15 mins
              </div>
            </div>
          </div>
        </div>
    </template>
    </div>
  

  </q-page>
</q-page-container>

</q-layout>

</DIV>