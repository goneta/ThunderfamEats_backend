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
        <div class="text-h6 text-subtitle1">{{title}}</div>
        <q-space />
        <q-btn icon="close" color="grey" flat round dense v-close-popup ></q-btn>
    </q-card-section>

    <q-card-section>   
        <!-- pay_left=>{{pay_left}}
        change=>{{change}} -->
        <div class="text-center q-mb-md">
            
            <template v-if="payment_code=='cod'">
            <div class="row">
                <div class="col-5">                
                    <h6 class="q-ma-none text-grey-5"><?php echo t("Total Amount")?></h6>
                    <h4 class="q-ma-none text-grey-7">
                        {{cart_total.value}}
                    </h4>   
                </div>
                <div class="col">
                    <div class="flex justify-center q-gutter-x-md">
                        <div>
                            <h6 class="q-ma-none text-grey-5"><?php echo t("Balance")?></h6>
                            <h6 class="q-ma-none text-grey-7" style="font-size: 1rem;">                        
                              <money-format :amount="pay_left" ></money-format> 
                            </h6>
                        </div>
                        <div>
                            <h6 class="q-ma-none text-grey-5"><?php echo t("Change")?></h6>
                            <h6 class="q-ma-none text-grey-7" style="font-size: 1rem;">                            
                              <money-format :amount="change" ></money-format> 
                            </h6>
                        </div>
                    </div>
                </div>
            </div>
            <!-- row -->
            </template>
            <template v-else>
                <h6 class="q-ma-none text-grey-5"><?php echo t("Total Amount")?></h6>
                <h4 class="q-ma-none text-grey-7">
                    {{cart_total.value}}
                </h4>                            
            </template>                       

            <template v-if="payment_code=='cod'">
            <q-input v-model="receive_amount" 
            label="<?php echo t("Receive Amount")?>" 
            type="number"
            :rules="[
              (val) => /^[0-9]+(\.[0-9]{0,2})?$/.test(val) || 'Invalid number'
            ]"    
            max="999999999" 
            min="1" 
            autocomplete="off"
            step="any"
            ></q-input>

            <div class="scroll">                                 
              <q-btn-toggle
                v-model="change_denomination"
                toggle-color="primary"                
                unelevated
                size="lg"
                :options="change_denomination_list"
               >
              </q-btn-toggle>
            </div>

            </template>          

        </div>               
        
        <div class="row q-gutter-md justify-center" >
           <template
                v-for="items in getPaymentMethod"
                :key="items"
            >
            <div class="col-3 relative-position">
                <div
                v-ripple 
                class="rounded-borders full-width cursor-pointer row items-stretch justify-center q-pa-sm text-grey-8"                                                        
                style="height: 6em;"       
                :class="getSelectedPayment(items.payment_code)" 
                @click="setPaymenMethod(items.payment_code)"                             
                >
                   <div class="column col-12 text-center">
                       <div class="col">
                        <template v-if="items.logo_type == 'icon'">

                              <q-icon
                                :name="payment_icons[items.payment_code]? payment_icons[items.payment_code].icon: 'credit_card'"
                                size="sm"
                                color="primary"
                            ></q-icon>

                        </template>
                        <template v-else>
                            <q-img
                                :src="items.logo_image"
                                spinner-color="white"
                                style="height: 25px; max-width: 30px"
                                fit="scale-down"
                                >
                            </q-img>
                        </template>
                       </div>
                       <div class="col">
                           <div class="text-weight-regular text-grey-7" style="line-height: 1;">
                            {{items.payment_name}}
                           </div>                   
                       </div>
                   </div>                                      
                </div>
            </div>
           </template>
        </div>

                

        <q-list class="rounded-borders">
           <q-expansion-item
            expand-separator                        
            caption="<?php echo t("Add more information about this payment")?>"
           >
           <q-card>
           <q-card-section class="q-gutter-y-sm">
                      
           <!-- <q-btn-toggle              
            v-model="whento_deliver"
            toggle-color="primary"
            text-color="grey-7"
            unelevated
            no-caps
            :options="attributes_data.preferred_times?attributes_data.preferred_times:[]"
            spread
           ></q-btn-toggle>

           <template v-if="whento_deliver == 'schedule'">
           <q-select                            
              v-model="delivery_date"
              :options="getOpeningDates"
              label="Date"
              emit-value
              stack-label
              :rules="[
                (val) =>
                  (val && val.length > 0) || 'This field is required',
              ]"
              map-options             
            >
            </q-select>

            <q-select              
              v-model="delivery_time"
              :options="getTimelist"
              label="Time"
              option-value="start_time"
              option-label="pretty_time"
              stack-label
              emit-value
              :rules="[
                (val) =>
                  (val && val.length > 0) || 'This field is required',
              ]"
              map-options              
            ></q-select>
           </template> -->

           <q-select                            
              v-model="status"
              :options="getOrderStatus"
              label="<?php echo t("Status")?>"
              emit-value
              stack-labelx              
              map-options             
            >
            </q-select>

           <q-input              
              v-model="payment_reference"
              label="<?php echo t("Payment Reference number")?>"                            
            ></q-input>

            <q-input              
              v-model="order_notes"
              label="<?php echo t("Add order notes")?>"                            
              autogrow
             ></q-input>

           </q-card-section>
           </q-card>
           </q-expansion-item>
        </q-list>
        
    </q-card-section> 
    
    <q-card-actions>
        <q-btn         
            no-caps 
            label="<?php echo t("Submit")?>" 
            type="submit"
            unelevated 
            color="primary" 
            size="18px" 
            class="fit"        
            :disabled="!hasPaymentSelected"          
        >        
        </q-btn>
    </q-card-actions>
    </q-form>

</q-card>
</q-dialog>