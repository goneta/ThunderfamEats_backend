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
        <div class="text-h6 text-subtitle1"><?php echo t("Hold Order")?></div>
        <q-space />
        <q-btn icon="close" color="grey" flat round dense v-close-popup ></q-btn>
    </q-card-section>

    <q-card-section>                        
          
         <q-input
            v-model="order_reference"
            ref="order_reference"
            outlined
            color="grey-5"
            :rules="[
               (val) => (val && val.length > 0) || '<?php echo t("This field is required")?>',
            ]"                 
            label="<?php echo t("Order Reference")?>"       
            stack-label
          >            
          </q-input>       
          
          <p class="text-caption q-ma-none"><?php echo t("The current order will be set on hold. You can retrieve this order from the pending tabs")?>.</p>
          <p class="text-caption q-ma-none"><?php echo t("Providing a reference to it might help you to identify the order more quickly")?>.</p>

      </q-card-section>
    
    <q-card-actions class="border-top">
    <q-btn type="submit" unelevated no-caps label="<?php echo t("Submit")?>" color="primary" size="lg" class="fit"
        :loading="loading"        
        ></q-btn>
    </q-card-actions>
    </q-form>
</q-card>
</q-dialog>