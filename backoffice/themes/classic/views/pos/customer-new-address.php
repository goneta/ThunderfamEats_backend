<q-dialog
    v-model="modal"    
    :maximized="this.$q.screen.lt.sm?true:false"
    :position="this.$q.screen.lt.sm?'bottom':'standard'"
    persistent
>
<q-card class="card-form-width">
   <q-form @submit="onSubmit">
   <q-card-section class="row items-center q-pb-none">
        <div class="text-h6 text-subtitle1"><?php echo t("Delivery address")?></div>
        <q-space />
        <q-btn icon="close" color="grey" flat round dense v-close-popup ></q-btn>
    </q-card-section>

    <q-card-section class="card-form scroll" > 
        
        <!-- <q-inner-loading :showing="loading" color="primary"></q-inner-loading>    -->        

        <SearchAddress
         ref="search_address"
         @after-selectaddress="afterSelectaddress"
         @on-clear="onClear"
         placeholder="<?php echo t("Enter your street and house number")?>"
        >
        </SearchAddress>
        
        <components-maps
            ref="cmaps"
            :keys="getMapsConfig.key"
            :provider="getMapsConfig.map_provider"
            :zoom="getMapsConfig.zoom"
            size="map-smalls q-mt-sm"
            :center="center"
            :markers="marker_position"                        
            @after-selectmap="afterSelectmap"
            @drag-marker="dragMarker"
            >
        </components-maps>

        <div class="q-pt-smx q-pb-md">
           <div class="text-subtitle2">{{ address1 }}</div>
           <div class="text-caption">{{ formatted_address }}</div>
        </div>
           
        
        <q-input
            outlined
            v-model="formatted_address"
            label="<?php echo t("Street name")?>"
            stack-label
            color="grey-5"
            lazy-rules
            :rules="[
            (val) => (val && val.length > 0) || 'This field is required',
            ]"
        />

        <q-input
        outlined
        v-model="address1"
        label="<?php echo t("Street number")?>"
        stack-label
        color="grey-5"
        lazy-rules
        :rules="[
          (val) => (val && val.length > 0) || 'This field is required',
        ]"
        />

        <q-input
        outlined
        v-model="location_name"
        label="<?php echo t("Aparment, suite or floor")?>"
        stack-label
        color="grey-5"
        />
        <q-space class="q-pa-sm"></q-space>
                
        <q-select
            outlined
            v-model="delivery_options"
            :options="Object.keys(attributes_data).length > 0 ?attributes_data.delivery_option:[]"
            label="<?php echo t("Delivery options")?>"
            emit-value
            emit-value
            map-options
            stack-label      
            color="grey-5"
        />

        <q-space class="q-pa-sm"></q-space>
        <q-input
            v-model="delivery_instructions"
            outlined
            autogrow
            stack-label
            label="<?php echo t("Add delivery instructions")?>"
            hint="<?php echo t("eg. ring the bell after dropoff, leave next to the porch, call upon arrival, etc")?>"
            color="grey-5"
        />

        <template v-if="attributes_data.custom_field_enabled">
        <q-space class="q-pa-md"></q-space>
            <q-select
                outlined
                v-model="custom_field1"
                :options="Object.keys(attributes_data).length > 0 ?attributes_data.custom_field1_data:[]"
                label="<?php echo t("Bonito")?>"
                emit-value
                emit-value
                map-options
                stack-label      
                color="grey-5"
            />

            <q-space class="q-pa-sm"></q-space>
            <q-input
                v-model="custom_field2"
                outlined
                autogrow
                stack-label               
                label="<?php echo t("Caliente")?>"   
                color="grey-5"
            />
        </template>

        <q-space class="q-pa-md"></q-space>
        <div class="text-weight-bold q-mb-sm"><?php echo t("Address label")?></div>

        <div class="border-greyx radius8">
        <q-btn-toggle
          v-model="address_label"
          color="blue-grey-3"
          text-color="blue-grey-7"
          toggle-color="primary"
          unelevated
          no-caps
          :options="Object.keys(attributes_data).length > 0 ?attributes_data.address_label:[]"
          spread
          padding="10px"
         />
        </div>
        
        <!-- <q-space class="q-pa-md"> </q-space> -->
       
    </q-card-section>

    <q-card-actions class="border-top">
        <q-btn type="submit" unelevated no-caps label="<?php echo t("Save Address")?>" color="primary" size="lg" class="fit"
        :loading="loading"
        :disable="!hasAddress"
        ></q-btn>
    </q-card-actions>

    </q-form>
</q-card>
</q-dialog>