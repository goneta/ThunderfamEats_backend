<q-dialog
v-model="modal"    
:maximized="this.$q.screen.lt.sm?true:false"
:position="this.$q.screen.lt.sm?'bottom':'standard'"
@before-show="beforeShow"
@show="OnShow"
persistent
>
<q-card style="min-width: 50vw;" >
<q-form @submit="onSubmit">
   <q-card-section class="row items-center q-pb-none">
        <div class="text-h6 text-subtitle1"><?php echo t("Create Payment")?></div>
        <q-space />
        <q-btn icon="close" color="grey" flat round dense v-close-popup ></q-btn>
    </q-card-section>

    <q-card-section style="max-height: 65vh;" class="scroll">        

   
            <div class="row">
                <div class="col-4 q-pa-xs">
                    <div class="border-grey rounded-borders flex flex-center box text-center">
                        <div>
                        <div class="text-h6"><?php echo t("Total Due")?></div>
                        <div class="text-body2">{{total}}</div>
                        </div>                
                    </div>
                </div>
                <div class="col-4 q-pa-xs">
                    <div class="border-grey rounded-borders flex flex-center box text-center">
                        <div>
                        <div class="text-h6"><?php echo t("Pay Left")?></div>
                        <div class="text-body2">{{pay_left}}</div>
                        </div>                
                    </div>
                </div>
                <div class="col-4 q-pa-xs">
                <div class="border-grey rounded-borders flex flex-center box text-center">
                        <div>
                        <div class="text-h6"><?php echo t("Change")?></div>
                        <div class="text-body2">{{change}}</div>
                        </div>                
                    </div>
                </div>
            </div>
            <!-- row -->

            <div class="q-ma-sm border-top"></div>

            <!-- <div class="text-body2">Choose preferred time</div>             -->

            <div class="border-grey radius8">
            <q-btn-toggle              
              v-model="whento_deliver"
              toggle-color="green"
              unelevated
              no-caps
              :options="attributes_data.preferred_times?attributes_data.preferred_times:[]"
              spread
            ></q-btn-toggle>
          </div>
        

          <template v-if="whento_deliver == 'schedule'">
          <div class="q-mt-sm">
          <q-select              
              outlined
              v-model="delivery_date"
              :options="getOpeningDates"
              label="<?php echo t("Date")?>"
              emit-value
              stack-label
              :rules="[
                (val) =>
                  (val && val.length > 0) || '<?php echo t("This field is required")?>',
              ]"
              map-options
              @update:model-value="afterSelectDate"
              color="grey-5"
            >
            </q-select>
            
            <q-select
              outlined
              v-model="delivery_time"
              :options="getTimelist"
              label="Time"
              option-value="start_time"
              option-label="pretty_time"
              stack-label
              emit-value
              :rules="[
                (val) =>
                  (val && val.length > 0) || '<?php echo t("This field is required")?>',
              ]"
              map-options
              color="grey-5"
            ></q-select>
          </div>
          </template>

          <q-space class="q-pa-sm"></q-space>
      

          <q-input
            outlined
            v-model="receive_amount"
            label="<?php echo t("Receive amount")?>"                                                          
            type="number"
            color="grey-5"            
            :rules="[
              (val) => /^[0-9]+(\.[0-9]{0,2})?$/.test(val) || '<?php echo t("Invalid number")?>'
            ]"    
            max="999999999" 
            min="1" 
            autocomplete="off"
            step="any"
          ></q-input>

          <q-select
            outlined
            v-model="order_status"
            label="<?php echo t("Order status")?>"
            :options="getOrderStatus"
            stack-label
            :rules="[
              (val) =>
                (val && val.length > 0) || '<?php echo t("This field is required")?>',
            ]"
            emit-value
            map-options
            color="grey-5"
          ></q-select>

          <q-select
            outlined
            v-model="payment_code"
            label="<?php echo t("Payment Method")?>"
            :options="getPaymentMethod"
            option-value="payment_code"
            option-label="payment_name"
            stack-label
            :rules="[
              (val) =>
                (val && val.length > 0) || '<?php echo t("This field is required")?>',
            ]"
            emit-value
            map-options
            color="grey-5"
          ></q-select>

 
          <template v-if="transaction_type == 'dinein'">
          <q-input
              outlined
              v-model.number="guest_number"
              mask="#############"
              label="<?php echo t("Guest")?>"
              stack-label
              color="grey-5"
              lazy-rules
              :rules="[(val) => val > 0 || '<?php echo t("This field is required")?>']"
            ></q-input>

            <q-select
              outlined
              v-model="room_id"
              label="<?php echo t("Room Name")?>"
              :options="getRoomList"
              stack-label
              :rules="[
                (val) =>
                  (val && val.length > 0) || '<?php echo t("This field is required")?>',
              ]"
              emit-value
              map-options
              @update:model-value="afterSelectRoom"
              color="grey-5"
            ></q-select>

            <q-select
              outlined
              v-model="table_id"
              label="<?php echo t("Table Name")?>"
              :options="getTableList"
              stack-label
              :rules="[
                (val) =>
                  (val && val.length > 0) || '<?php echo t("This field is required")?>',
              ]"
              emit-value
              map-options
              color="grey-5"
            ></q-select>

          </template>

          <div class="q-gutter-y-md">
            <q-input
              outlined
              v-model="payment_reference"
              label="<?php echo t("Payment Reference number")?>"
              stack-label
              color="grey-5"
            ></q-input>

            <q-input
              outlined
              v-model="order_notes"
              label="<?php echo t("Add order notes")?>"
              stack-label
              color="grey-5"
              autogrow
             ></q-input>
          </div>
    
                      
    </q-card-section>
    
    <q-card-actions class="border-top">
    <q-btn type="submit" unelevated no-caps label="Apply" color="primary" size="lg" class="fit"
        :loading="loading"        
        ></q-btn>
    </q-card-actions>
    </q-form>
</q-card>
</q-dialog>