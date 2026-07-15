<q-dialog
    v-model="dialog"
    @before-show="clientAddresses"
    @before-hide="beforeHide"
    :maximized="this.$q.screen.lt.sm?true:false"
    :position="this.$q.screen.lt.sm?'bottom':'standard'"
>
<q-card class="card-form-width">
   <q-card-section class="row items-center q-pb-none">
        <div class="text-h6 text-subtitle1"><?php echo t("Select address")?></div>
        <q-space />
        <q-btn icon="close" color="grey" flat round dense v-close-popup ></q-btn>
    </q-card-section>

    <q-card-section style="max-height: 60vh; min-height: 30vh" class="scroll"> 
        
        <q-inner-loading :showing="loading" color="primary"></q-inner-loading>   

        <template v-if="!hasData && !loading">
            <div class="flex flex-center" style="min-height: 30vh;">
               <div class="text-grey"><?php echo t("No available data")?></div>
            </div>
        </template>

        <template v-if="hasData">
        <q-list separator>
            <template v-for="items in data" :keys="items">
                <q-item clickable @click="setAddress(items)">
                    <q-item-section avatar>
                        <q-icon name="place" color="primary"></q-icon>
                    </q-item-section>
                    <q-item-section>
                        <q-item-label>{{items.attributes.address_label}}</q-item-label>
                        <q-item-label >{{items.address.address1}}</q-item-label>
                        <q-item-label caption>{{items.address.formatted_address}}</q-item-label>
                    </q-item-section>
                </q-item>
            </template>
        </q-list>
        </template>    
    </q-card-section>

    <q-card-actions class="border-top">
        <q-btn @click="showNewaddress" flat no-caps label="<?php echo t("Add new address")?>" color="primary" size="lg" class="fit"></q-btn>
    </q-card-actions>

</q-card>
</q-dialog>