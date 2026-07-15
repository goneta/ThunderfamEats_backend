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
        <div class="text-h6 text-subtitle1">{{label}}</div>
        <q-space />
        <q-btn icon="close" color="grey" flat round dense v-close-popup ></q-btn>
   </q-card-section>

   <q-card-section>      
      <!-- {{guest_number}}
      <pre>{{guest_options}}</pre> -->
      <q-select v-model="guest_number" :options="guest_options" color="primary" emit-value map-options ></q-select>
   </q-card-section>

   <q-card-actions class="border-top">
    <q-btn type="submit" unelevated no-caps label="<?php echo t("Submit")?>" color="primary" size="lg" class="fit"
        :loading="loading"        
        ></q-btn>
   </q-card-actions>

   </q-form>
</q-card>
</q-dialog>