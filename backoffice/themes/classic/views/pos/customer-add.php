<q-dialog
      v-model="modal"
      persistent        
      :position="this.$q.screen.lt.md ? 'bottom' : 'standard'"
      >
      <q-card  :class="{ 'card-form-width': !this.$q.screen.lt.md }">
        <q-form  @submit="onSubmit" class="form">
        <q-card-section class="row items-center q-pb-none">
            <div class="text-h6 ellipsis">
            <?php echo t("Customer")?>
            </div>
            <q-space ></q-space>
            <q-btn icon="close" color="grey-1" text-color="grey-5" unelevated round dense v-close-popup ></q-btn>
        </q-card-section>
        <q-card-section>
        
           <q-input outlined v-model="first_name" label="<?php echo t("First name")?>"  color="yellow-9" class="bg-white" stack-label
           :rules="[
              (val) =>
                (val && val.length > 0) || '<?php echo t("This field is required")?>',
            ]"
            ></q-input>

           <q-input outlined v-model="last_name" label="<?php echo t("Last name")?>"  color="yellow-9" class="bg-white" stack-label 
           :rules="[
              (val) =>
                (val && val.length > 0) || '<?php echo t("This field is required")?>',
            ]"
            ></q-input>

            <q-input outlined v-model="email_address" label="<?php echo t("Email address")?>"  color="yellow-9" class="bg-white" stack-label 
			:rules="[val => /.+@.+\..+/.test(val) || 'Invalid email']"
            ></q-input>

            <q-input outlined v-model="contact_number" label="<?php echo t("Contact number")?>"  color="yellow-9" class="bg-white" stack-label 
           :rules="[
              (val) =>
                (val && val.length > 0) || '<?php echo t("This field is required")?>',
            ]"
            ></q-input>

        </q-card-section>
        <q-card-actions class="border-top">
           <q-btn type="submit" :loading="loading"  no-caps label="<?php echo t("Save")?>" unelevated color="primary" text-color="white" class="fit" size="18px" ></q-btn>
        </q-card-actions>
        </q-form>
      </q-card>
</q-dialog>