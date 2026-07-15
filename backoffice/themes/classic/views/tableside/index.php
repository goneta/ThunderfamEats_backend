<DIV id="app-tableside">

<q-layout view="hHr lpR fFf"  v-cloak >


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
  <q-page padding style="background:#fffdfb;">
  
      <div class="flex justify-between q-mb-md items-center">
        <div><h6 class="q-ma-none">Tables</h6></div>
        <div>
           <q-btn square color="white" text-color="yellow-9" dense unelevated >
                <div class="border-yellow rounded-borders q-pa-xs">
                <q-icon name="filter_alt"></q-icon>
                </div>
            </q-btn>      

            <q-btn square color="white" text-color="yellow-9" dense unelevated >
                <div class="border-yellow rounded-borders q-pa-xs">
                <q-icon name="grid_view"></q-icon>
                </div>
            </q-btn>            
            
        </div>
      </div>

      <div class="row items-center">
        <div class="col">
            <q-tabs
            v-model="category"        
            no-caps
            active-color="yellow-9"        
            dense        
            align="left"
            class="border-bottom"
            >
            <q-tab name="All Tables"  label="All Tables"></q-tab>
            <q-tab name="First Floor"  label="First Floor"></q-tab>
            <q-tab name="Second Floor"  label="Second Floor" ></q-tab>
            <q-tab name="Outdoor"  label="Outdoor" ></q-tab>
        </q-tabs>        
        </div>       
      </div>
      <!-- row -->

      <div class="q-pa-md q-pt-lg">
         <div class="row justify-start q-gutter-md">
            <template v-for="items in 10">
               <div :class="{'col-2.5':this.$q.screen.gt.sm , 'col-5':this.$q.screen.lt.sm}" > 
                  <q-list class="bg-white box-shadow radius5">
                     <q-item clickable v-ripple class="relative-position q-pb-md">
                        <div class="bg-white table-square flex flex-center text-body2 radius5">
                            <div class="text-center">
                                <div class="text-weight-bold">T{{items}}</div>
                                <div>#1001</div>
                                <!-- <div class="text-caption">4 guest</div>
                                <div class="text-caption">
                                   Dine-in
                                </div>
                                <div class="text-caption">
                                   5 mins
                                </div> -->
                                <div class="text-caption">
                                    <q-icon name="schedule"></q-icon>
                                    15 mins
                                </div>
                            </div>
                        </div>                        
                        <div class="bg-green-6 absolute-bottom full-width table-status-bar" style="height: 5px;" ></div>
                     </q-item>
                  </q-list>
               </div>
            </template>
         </div>
      </div>

      <q-footer v-if="$q.screen.gt.sm" class="bg-white text-dark q-pa-md border-top">
         <div class="flex items-center q-gutter text-body2 text-weight-bold q-gutter-x-sm">
            <div>
                <q-btn round color="green-6" size="11px" unelevated ></q-btn>
            </div>
            <div class="q-mr-md">Vacant</div>

            <div>
                <q-btn round color="red" size="11px" unelevated ></q-btn>
            </div>
            <div class="q-mr-md">Occupied</div>

            <div>
                <q-btn round color="yellow" size="11px" unelevated ></q-btn>
            </div>
            <div class="q-mr-md">Reserved</div>

            <div>
                <q-btn round color="grey" size="11px" unelevated ></q-btn>
            </div>
            <div class="q-mr-md">Closed</div>

         </div>
      </q-footer>

  </q-page>
</q-page-container>

</q-layout>

</DIV>