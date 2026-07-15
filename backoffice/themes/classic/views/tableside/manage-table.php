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
:width="450"
:breakpoint="1023"
class="bg-grey-2"
>

<div class="q-pa-md border-bottom bg-white">
   <div class="text-h6">Add new table</div>         
</div>

<div class="q-pa-md bg-white card-small-1">
   <q-form>
       <q-input outlined v-model="text" label="Table name" dense color="yellow-9" class="bg-white" 
       lazy-rules
      :rules="[ val => val && val.length > 0 || 'Please type something']"
       ></q-input>  

       <q-select dense outlined v-model="model" :options="options" label="Select Room" color="yellow-9"
       :rules="[ val => val && val.length > 0 || 'Please type something']"
       emit-value
       map-options
       ></q-select>

       <q-input outlined v-model="text" label="Min Covers" dense color="yellow-9" class="bg-white" 
       lazy-rules
      :rules="[ val => val && val.length > 0 || 'Please type something']"
       ></q-input>  

       <q-input outlined v-model="text" label="Max Covers" dense color="yellow-9" class="bg-white" 
       lazy-rules
      :rules="[ val => val && val.length > 0 || 'Please type something']"
       ></q-input>         
   </q-form>   
</div>
<div class="q-pa-md">
<q-btn no-caps label="Save" unelevated color="green-6" text-color="white" class="full-width" size="18px" ></q-btn>
</div>


</q-drawer>

<q-page-container >
  <q-page padding class="bg-cream">
  
  <q-card flat class="radius5 q-pa-sm card-medium">
    <q-card-content >    
              
        <div class="row items-center">
             <div class="col-6">

             <q-input outlined v-model="text" label="Search table" dense color="yellow-9" class="bg-white"  >
                  <template v-slot:prepend>
                      <q-icon name="search" />
                  </template>
              </q-input>


             </div>
             <div class="col-6 text-right">
              
             <q-btn square color="white" text-color="yellow-9" dense unelevated >
                  <div class="border-yellow rounded-borders q-pa-xs">
                    <q-icon name="add"></q-icon>
                  </div>
              </q-btn>           

             </div>
        </div>
        <!-- row -->


        <div class="border-top q-mt-md q-pt-md">
        
        <q-tabs
            v-model="category"        
            no-caps
            active-color="yellow-9"        
            dense        
            align="left"
            class="border-bottom q-mb-md"
            >
            <q-tab name="Rooms"  label="Rooms"></q-tab>
            <q-tab name="Tables"  label="Tables"></q-tab>            
          </q-tabs>        

            <q-list separator>
                <template v-for="items in 5" :key="items">
                <q-item clickable >
                    <q-item-section>
                        <q-item-label>
                           Table 44
                        </q-item-label>
                        <q-item-label caption>
                            Balcony
                        </q-item-label>                        
                    </q-item-section>
                    <q-item-section side>
                       <q-item-label>
                            Min 1 Max 3
                        </q-item-label>
                        <q-item-label>
                           <q-badge outline color="green" label="Published"></q-badge>
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