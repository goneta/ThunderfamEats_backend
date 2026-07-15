<q-dialog
v-model="modal"    
:maximized="this.$q.screen.lt.sm?true:false"
:position="this.$q.screen.lt.sm?'bottom':'standard'"
@before-show="beforeShow"
@show="OnShow"
persistent
>
<q-card class="card-form-width">
   <q-form @submit="onSubmit">
   <q-card-section class="row items-center q-pb-none">
        <div class="text-h6 text-subtitle1">Apply Points discount</div>
        <q-space />
        <q-btn icon="close" color="grey" flat round dense v-close-popup ></q-btn>
    </q-card-section>

    <q-card-section>        
        
        <div class="flex justify-end text-body2 text-grey q-mb-xs">
           <div>Points Balance : {{available_points}}</div>
        </div>

        <template v-if="use_thresholds">
        
           <q-tabs              
              v-model="points_tab"
              class="text-dark q-mb-lg"
              no-caps
              active-color="white"
              active-bg-color="primary"
              indicator-color="transparent"
              @update:model-value="setPoints"
            >
              <template v-for="items in data_points" :key="items">
                <q-tab
                  :name="items"
                  :disable="balance > items.points ? false : true"
                >
                  <div class="text-caption">{{ items.label }}</div>
                  <div class="text-subtitle2 q-mb-sm">{{ items.amount }}</div>
                  <q-linear-progress
                    size="18px"
                    :value="balance / items.points"
                    style="min-width: 80px"
                    class="radius20"
                    :color="balance >= items.points ? 'green' : 'blue'"
                  >
                    <div
                      v-if="balance >= items.points"
                      class="absolute-full flex flex-center"
                    >
                      <span class="text-white text-caption text-weight-bold">REDEEM</span>
                    </div>
                  </q-linear-progress>
                </q-tab>
              </template>
            </q-tabs>

        </template>
        <template v-else>
            
             <q-input
                v-model="points"
                ref="points"
                outlined
                color="grey-5"
                :rules="[
                (val) =>
                    (val && val.length > 0) || 'Please enter numbers',
                ]"
                type="number"
            >            
            </q-input>          

        </template>
      </q-card-section>
    
    <q-card-actions class="border-top">
    <q-btn type="submit" unelevated no-caps label="Apply" color="primary" size="lg" class="fit"
        :loading="loading"        
        ></q-btn>
    </q-card-actions>
    </q-form>
</q-card>
</q-dialog>