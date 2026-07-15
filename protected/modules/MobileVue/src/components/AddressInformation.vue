<template>
  <q-dialog
    v-model="show_modal"
    persistent
    @before-show="beforeShow"
    :maximized="true"
    transition-show="fade"
    transition-hide="fade"
  >
    <q-card class="row items-stretch">
      <div class="col-12">
        <q-toolbar v-if="!adjust_pin">
          <q-btn
            icon="close"
            flat
            round
            dense
            v-close-popup
            :color="$q.dark.mode ? 'white' : 'dark'"
          />
          <q-toolbar-title
            class="text-weight-bold"
            :class="{
              'text-white': $q.dark.mode,
              'text-dark': !$q.dark.mode,
            }"
          >
            {{ $t("Delivery details") }}
          </q-toolbar-title>
        </q-toolbar>

        <div class="q-pl-md q-pr-md">
          <div class="row items-center items-stretch">
            <div class="col-9">
              <div class="text-weight-bold">{{ address1 }}</div>
              <div class="cursor-pointer font12 text-grey">
                {{ formatted_address }}
              </div>
            </div>
          </div>
          <q-space class="q-pa-sm"></q-space>
          <q-form @submit="onSubmit">
            <div class="q-gutter-sm">
              <q-input
                v-model="address1"
                :label="$t('Street name')"
                outlined
                lazy-rules
                :bg-color="$q.dark.mode ? 'grey600' : 'input'"
                :label-color="$q.dark.mode ? 'grey300' : 'grey'"
                borderless
                class="input-borderless"
              />

              <q-input
                v-model="formatted_address"
                :label="$t('Street number')"
                outlined
                lazy-rules
                :bg-color="$q.dark.mode ? 'grey600' : 'input'"
                :label-color="$q.dark.mode ? 'grey300' : 'grey'"
                borderless
                class="input-borderless"
              />

              <q-input
                v-model="location_name"
                :label="$t('Aparment, suite or floor')"
                outlined
                lazy-rules
                :bg-color="$q.dark.mode ? 'grey600' : 'input'"
                :label-color="$q.dark.mode ? 'grey300' : 'grey'"
                borderless
                class="input-borderless"
              />
              <div class="text-h6">{{ $t("Delivery options") }}</div>
              <q-select
                v-model="delivery_options"
                :options="delivery_options_data"
                transition-show="fade"
                transition-hide="fade"
                emit-value
                outlined
                lazy-rules
                :bg-color="$q.dark.mode ? 'grey600' : 'input'"
                :label-color="$q.dark.mode ? 'grey300' : 'grey'"
                borderless
                class="input-borderless"
              />

              <q-input
                v-model="delivery_instructions"
                autogrow
                :label="$t('Add delivery instructions')"
                outlined
                lazy-rules
                :bg-color="$q.dark.mode ? 'grey600' : 'input'"
                :label-color="$q.dark.mode ? 'grey300' : 'grey'"
                borderless
                class="input-borderless"
              />

              <div class="text-h6">{{ $t("Address label") }}</div>
              <q-btn-toggle
                v-model="address_label"
                toggle-color="secondary"
                :color="$q.dark.mode ? 'grey600' : 'mygrey'"
                :text-color="$q.dark.mode ? 'grey300' : 'dark'"
                no-caps
                no-wrap
                unelevated
                :options="address_label_data"
                class="rounded-group2 text-weight-bold line-1"
              />
              <q-space class="q-pa-xl"></q-space>

              <q-footer class="q-pl-md q-pr-md q-pt-sm q-pb-sm bg-white">
                <q-btn
                  type="submit"
                  :loading="loading"
                  :label="$t('Save')"
                  unelevated
                  no-caps
                  color="primary text-white"
                  class="full-width text-weight-bold"
                  size="lg"
                />
              </q-footer>
            </div>
          </q-form>
        </div>
      </div>
    </q-card>
  </q-dialog>
</template>

<script>
import APIinterface from "src/api/APIinterface";
import { useDataStore } from "stores/DataStore";

export default {
  name: "AddressInformation",
  props: ["back_url"],
  data() {
    return {
      show_modal: false,
      loading: false,
      location_data: [],
      location_name: "",
      delivery_options: "Leave it at my door",
      delivery_instructions: "",
      address_label: "Home",
      attributes: [],
      delivery_options_data: [],
      address_label_data: [],
      address1: "",
      formatted_address: "",
      latitude: "",
      longitude: "",
      place_id: "",
    };
  },
  setup() {
    const DataStore = useDataStore();
    return {
      DataStore,
    };
  },
  created() {
    this.addressAtttibues();
  },
  methods: {
    show(data) {
      this.show_modal = true;
      this.address1 = data.address.address1;
      this.formatted_address = data.address.formatted_address;
      this.latitude = data.latitude;
      this.longitude = data.longitude;
      this.place_id = data.place_id;
    },
    addressAtttibues() {
      APIinterface.addressAtttibues()
        .then((data) => {
          this.attributes = data.details;

          if (Object.keys(data.details.delivery_option).length > 0) {
            Object.entries(data.details.delivery_option).forEach(
              ([key, items]) => {
                this.delivery_options_data.push({ label: items, value: key });
              }
            );
          }

          if (Object.keys(data.details.address_label).length > 0) {
            Object.entries(data.details.address_label).forEach(
              ([key, items]) => {
                this.address_label_data.push({ label: items, value: key });
              }
            );
          }
        })
        .catch((error) => {
          APIinterface.notify("dark", error, "error", this.$q);
        })
        .then((data) => {});
    },
    onSubmit() {
      this.loading = true;
      let params = "address1=" + this.address1;
      params += "&formatted_address=" + this.formatted_address;
      params += "&location_name=" + this.location_name;
      params += "&delivery_instructions=" + this.delivery_instructions;
      params += "&delivery_options=" + this.delivery_options;
      params += "&address_label=" + this.address_label;
      params += "&latitude=" + this.latitude;
      params += "&longitude=" + this.longitude;
      params += "&place_id=" + this.place_id;

      APIinterface.fetchDataByTokenPost("SaveAddress", params)
        .then((data) => {
          this.backPage();
        })
        .catch((error) => {
          APIinterface.notify("dark", error, "error", this.$q);
        })
        .then((data) => {
          this.loading = false;
        });
    },
    backPage() {
      if (!APIinterface.empty(this.back_url)) {
        this.$router.push(this.back_url);
      } else {
        this.$router.push("/home");
      }
    },
  },
};
</script>
