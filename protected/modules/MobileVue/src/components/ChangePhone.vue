<template>
  <q-dialog v-model="show_modal" position="bottom">
    <q-card>
      <q-toolbar class="text-primary top-toolbar q-pl-md" dense>
        <q-toolbar-title
          class="text-weight-bold"
          :class="{
            'text-white': $q.dark.mode,
            'text-dark': !$q.dark.mode,
          }"
        >
          {{ $t("Your phone number") }}
        </q-toolbar-title>
        <q-space></q-space>
        <q-btn
          @click="show_modal = !true"
          color="white"
          square
          unelevated
          text-color="grey"
          icon="las la-times"
          dense
          no-caps
          size="sm"
          class="border-grey radius8"
        />
      </q-toolbar>
      <q-form @submit="beforeSubmit">
        <q-card-section>
          <!-- <div class="text-h5 text-weight-bold col">Phone Number</div> -->
          <div class="font12">
            {{
              $t(
                "A 6 digit OTP will be sent via SMS to verify your mobile number"
              )
            }}
          </div>

          <div class="radius8 q-pa-sm border-grey">
            <q-input
              v-model="phone_number"
              dense
              mask="####################"
              :color="$q.dark.mode ? 'grey300' : 'dark'"
              :bg-color="$q.dark.mode ? 'grey600' : 'white'"
              borderless
              size="lg"
            >
              <template v-slot:prepend>
                <q-select
                  dense
                  v-model="phone_prefix"
                  :options="prefixes"
                  behavior="dialog"
                  input-debounce="700"
                  style="border: none"
                  emit-value
                  borderless
                >
                  <template v-slot:no-option>
                    <q-item>
                      <q-item-section class="text-grey">
                        {{ $t("No results") }}
                      </q-item-section>
                    </q-item>
                  </template>
                </q-select>
              </template>
            </q-input>
          </div>
        </q-card-section>

        <q-card-actions vertical align="center" class="q-pa-md">
          <q-btn
            :disabled="hasChangePhone"
            :loading="loading"
            type="submit"
            :label="$t('Save')"
            unelevated
            :color="hasChangePhone == false ? 'primary' : 'grey-5'"
            :text-color="hasChangePhone == false ? 'white' : 'dark'"
            no-caps
            class="full-width"
            size="lg"
          />
        </q-card-actions>
      </q-form>
    </q-card>
  </q-dialog>

  <StepsVerification
    ref="steps_verification"
    :sent_message="sent_message"
    :phone_prefix="phone_prefix"
    :phone_number="phone_number"
    @after-verifycode="afterVerifycode"
  />
</template>

<script>
import { defineAsyncComponent } from "vue";
import APIinterface from "src/api/APIinterface";

export default {
  name: "ChangePhone",
  props: ["prefixes", "phone_prefix_orig", "contact_number_orig"],
  components: {
    StepsVerification: defineAsyncComponent(() =>
      import("components/StepsVerification.vue")
    ),
  },
  data() {
    return {
      show_modal: false,
      loading: false,
      phone_prefix: "",
      phone_number: "",
      sent_message: "",
    };
  },
  computed: {
    hasChangePhone() {
      if (this.phone_prefix_orig !== this.phone_prefix) {
        return false;
      }
      if (this.contact_number_orig !== this.phone_number) {
        return false;
      }
      return true;
    },
  },
  updated() {
    if (APIinterface.empty(this.phone_prefix)) {
      this.phone_prefix = this.phone_prefix_orig;
    }
    if (APIinterface.empty(this.phone_number)) {
      this.phone_number = this.contact_number_orig;
    }
  },
  watch: {
    phone_prefix_orig(newval, oldval) {
      this.phone_prefix = newval;
    },
    contact_number_orig(newval, oldval) {
      this.phone_number = newval;
    },
  },
  methods: {
    showModal(data) {
      this.show_modal = data;
    },
    beforeSubmit() {
      this.loading = true;
      APIinterface.RequestEmailCode()
        .then((data) => {
          this.sent_message = data.msg;
          this.show_modal = false;
          this.$refs.steps_verification.show_modal = true;
        })
        .catch((error) => {
          APIinterface.notify("grey-8", error, "error_outline", this.$q);
        })
        .then((data) => {
          this.loading = false;
        });
    },
    afterVerifycode(code) {
      this.$refs.steps_verification.visible = true;
      const $data = {
        code,
        phone_prefix: this.phone_prefix,
        phone_number: this.phone_number,
        cart_uuid: APIinterface.getStorage("cart_uuid"),
      };
      APIinterface.ChangePhone($data)
        .then((data) => {
          this.$refs.steps_verification.show_modal = false;
          APIinterface.notify("dark", data.msg, "done", this.$q);
          this.$emit("afterChangephone", data.details);
        })
        .catch((error) => {
          APIinterface.notify("dark", error, "error", this.$q);
        })
        .then((data) => {
          this.$refs.steps_verification.visible = false;
        });
    },
  },
};
</script>
