<q-dialog
v-model="modal"    
:maximized="this.$q.screen.lt.sm?true:false"
:position="this.$q.screen.lt.sm?'bottom':'standard'"
@before-show="beforeShow"
@show="OnShow"
persistent
>
<q-card class="card-form-width">

    <q-card-section class="row items-center q-pb-none">
        <div class="text-h6 text-subtitle1" style="max-width:370px;line-height:normal;">{{title}}</div>
        <q-space />
        <q-btn icon="close" color="grey" flat round dense v-close-popup ></q-btn>
    </q-card-section>

    <q-card-section class="q-gutter-y-sm">       
        
        <div v-if="success_message" class="inline-message-succes q-pa-sm radius6 q-mt-sm q-mb-sm">
        {{success_message}}
        </div>        
    
        <q-input v-if="enabled_email" v-model="email_address" label="<?php echo htmlspecialchars(t('Enter Email Address'), ENT_QUOTES, 'UTF-8'); ?>" type="email" 
         :rules="[val => !!val || 'Email is required', email => isValidEmail(email) || '<?php echo CJavaScript::quote(t("Invalid email format"))?>']"
         >
            <template v-slot:append>
                <q-btn         
                    no-caps 
                    label="<?php echo htmlspecialchars(t('Send'), ENT_QUOTES, 'UTF-8'); ?>"             
                    unelevated 
                    color="primary"             
                    outline                
                    class="radius6 col"         
                    icon="email"       
                    size="15px"    
                    @click="sendReceipt('email')"
                    :disabled="!ValidEmail"
                >        
                </q-btn>                 
            </template>
        </q-input>

        <q-input v-if="enabled_whatsapp" v-model="mobile_number" label="<?php echo htmlspecialchars(t('WhatsApp number with country code'), ENT_QUOTES, 'UTF-8'); ?>" 
        :rules="[val => !!val || 'Mobile number is required', mobile => isValidMobileNumber(mobile) || '<?php echo CJavaScript::quote(t("Invalid mobile number format"))?>']"
        >
            <template v-slot:append>
                <q-btn         
                    no-caps 
                    label="<?php echo htmlspecialchars(t('Send'), ENT_QUOTES, 'UTF-8'); ?>"             
                    unelevated 
                    color="primary"             
                    outline                
                    class="radius6 col"         
                    icon="lab la-whatsapp"       
                    size="15px"    
                    @click="sendReceipt('whatsapp')"
                    :disabled="!ValidMobileNumber"
                >        
                </q-btn>            
            </template>
        </q-input>    
    <q-card-section>   

    <q-card-actions class="row q-gutter-x-sm" v-if="enabled_webprint ||  enabled_print">
       <q-btn     
            v-if="enabled_webprint"    
            no-caps 
            label="<?php echo htmlspecialchars(t('Print Web'), ENT_QUOTES, 'UTF-8'); ?>"             
            unelevated 
            color="amber-12"             
            size="18px" 
            class="radius6 col"         
            icon="local_printshop"   
            @click="PrintWeb"            
        >        
        </q-btn>        
        <q-btn         
           v-if="enabled_print && getPrinterList"    
            no-caps 
            label="<?php echo htmlspecialchars(t('Print Thermal'), ENT_QUOTES, 'UTF-8'); ?>"             
            unelevated 
            color="primary" 
            size="18px" 
            class="radius6 col"      
            icon="las la-print"            
        >        
        <q-menu>
            <q-list style="min-width: 200px" separator>
                <template v-for="printer in getPrinterList" :key="printer">                
                <q-item clickable v-close-popup @click="SwitchPrinter(printer.printer_id,printer.printer_model)">
                  <q-item-section>
                    {{printer.printer_name}}
                  </q-item-section>
                </q-item>
                </template>                
            </q-list>
        </q-menu>
        </q-btn>
    </q-card-actions>
</q-card>
</q-dialog>