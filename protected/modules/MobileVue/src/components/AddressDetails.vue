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
            {{ $t("Delivery Address") }}
          </q-toolbar-title>
        </q-toolbar>

        <div :class="{ 'q-pl-md q-pr-md': !adjust_pin, 'fit ': adjust_pin }">
          <template v-if="!adjust_pin">
            <q-form @submit="onSubmit">
              <template v-if="DataStore.hasMapConfig">
                <MapsComponents
                  ref="mapRef"
                  class="maps"
                  size="small q-mb-sm radius8"
                  :keys="DataStore.maps_config.key"
                  :provider="DataStore.maps_config.provider"
                  :zoom="DataStore.maps_config.zoom"
                  :center="center"
                  :markers="marker_position"
                  @after-selectmap="afterSelectmap"
                >
                </MapsComponents>
              </template>

              <div class="row items-center items-stretch">
                <div class="col-9">
                  <div class="text-weight-bold">
                    {{ address1 }}
                  </div>
                  <div class="cursor-pointer font12 text-grey">
                    {{ formatted_address }}
                  </div>
                </div>
                <div class="col-3">
                  <q-btn
                    @click="adjust_pin = !adjust_pin"
                    unelevated
                    :color="$q.dark.mode ? 'grey600' : 'mygrey'"
                    :text-color="$q.dark.mode ? 'grey300' : 'dark'"
                    icon="las la-map-marker"
                    no-caps
                    class="line-normal fit"
                  />
                </div>
              </div>

              <q-space class="q-pa-sm"></q-space>

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
                    :label="$t('Save Address')"
                    unelevated
                    no-caps
                    color="primary text-white"
                    class="full-width text-weight-bold"
                    size="lg"
                  />
                </q-footer>
              </div>
            </q-form>
          </template>
          <!-- end form -->

          <template v-else>
            <div class="fit relative-position">
              <div
                class="absolute-top-left full-width z-top"
                :class="{
                  'bg-dark': $q.dark.mode,
                  'bg-white': !$q.dark.mode,
                }"
              >
                <div
                  class="absolute-top-left full-width text-rightx q-pa-sm"
                  style="z-index: 999"
                >
                  <q-btn
                    round
                    dense
                    icon="close"
                    class="q-mr-sm"
                    :color="$q.dark.mode ? 'white' : 'dark'"
                    unelevated
                    size="sm"
                    @click="adjust_pin = !adjust_pin"
                  />
                </div>
              </div>

              <template v-if="DataStore.hasMapConfig">
                <MapsComponents
                  ref="mapRef"
                  class="maps"
                  size="fit"
                  :keys="DataStore.maps_config.key"
                  :provider="DataStore.maps_config.provider"
                  :zoom="DataStore.maps_config.zoom"
                  :center="center"
                  :markers="marker_position"
                  @after-selectmap="afterSelectmap"
                >
                </MapsComponents>
              </template>
            </div>

            <q-footer class="q-pl-md q-pr-md q-pt-sm q-pb-sm bg-white">
              <q-btn
                :label="$t('Save')"
                :loading="loading"
                @click="validateCoordinates"
                unelevated
                no-caps
                color="primary text-white"
                class="full-width text-weight-bold"
                size="lg"
              />
            </q-footer>
          </template>
        </div>
      </div>
      <!-- col-12 -->
    </q-card>
  </q-dialog>
</template>

<script>
import { defineAsyncComponent } from "vue";
import APIinterface from "src/api/APIinterface";
import { useMapStore } from "stores/MapStore";
import { useDataStore } from "stores/DataStore";

export default {
  name: "AddressDetails",
  props: ["maps_config"],
  components: {
    MapsComponents: defineAsyncComponent(() =>
      import("components/MapsComponents.vue")
    ),
  },
  setup() {
    const MapStore = useMapStore();
    const DataStore = useDataStore();
    return {
      MapStore,
      DataStore,
    };
  },
  data() {
    return {
      show_modal: false,
      loading: false,
      location_data: [],
      location_name: "",
      delivery_options: "",
      delivery_instructions: "",
      address_label: "Home",
      attributes: [],
      delivery_options_data: [],
      address_label_data: [],
      address1: "",
      formatted_address: "",
      adjust_pin: false,
      class_map: "map bg-grey-2 rounded-10 q-mb-md",
      marker: [],
      validat_coord: false,
      new_lat: "",
      new_lng: "",
      center: [],
      marker_position: [],
      icon: {
        text: "\uea44",
        fontFamily: "Material Icons",
        color: "#ffffff",
        fontSize: "18px",
      },
      circles: {},
    };
  },
  mounted() {
    this.addressAtttibues();
  },
  watch: {
    adjust_pin(newval, oldval) {
      if (newval) {
        this.class_map = "window-height full-width";
        this.marker_position[0].draggable = true;
      } else {
        this.class_map = "map bg-grey-2 rounded-10 q-mb-md";
        this.marker_position[0].draggable = false;
      }
    },
  },
  methods: {
    beforeShow() {
      this.adjust_pin = false;
    },
    showModal() {
      this.show_modal = true;

      this.marker_position = [
        {
          id: 0,
          lat: parseFloat(this.location_data.latitude),
          lng: parseFloat(this.location_data.longitude),
          label: APIinterface.getIcon("customer"),
          icon: null,
          draggable: false,
        },
      ];
      this.center = {
        lat: parseFloat(this.location_data.latitude),
        lng: parseFloat(this.location_data.longitude),
      };
      this.new_lat = parseFloat(this.location_data.latitude);
      this.new_lng = parseFloat(this.location_data.longitude);

      if (this.location_data.attributes) {
        this.address1 = this.location_data.address.address1;
        this.formatted_address = this.location_data.address.formatted_address;

        this.location_name = this.location_data.attributes.location_name;
        this.delivery_options = this.location_data.attributes.delivery_options;
        this.delivery_instructions =
          this.location_data.attributes.delivery_instructions;
        this.address_label = this.location_data.attributes.address_label;
      }
    },
    closeModal() {
      this.show_modal = false;
      this.adjust_pin = false;
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
    afterSelectmap(lat, lng) {
      this.new_lat = lat;
      this.new_lng = lng;
    },
    validateCoordinates() {
      const $params = {
        lat: parseFloat(this.location_data.latitude),
        lng: parseFloat(this.location_data.longitude),
        new_lat: this.new_lat,
        new_lng: this.new_lng,
      };
      this.loading = true;
      APIinterface.validateCoordinates($params)
        .then((data) => {
          this.adjust_pin = false;
          this.location_data.latitude = this.new_lat;
          this.location_data.longitude = this.new_lng;

          this.marker_position[0].lat = this.new_lat;
          this.marker_position[0].lng = this.new_lng;
        })
        .catch((error) => {
          APIinterface.notify("dark", error, "error", this.$q);
        })
        .then((data) => {
          this.loading = false;
        });
    },
    onSubmit() {
      console.debug("onSubmit");
      this.loading = true;
      const $params = {
        address1: this.address1,
        formatted_address: this.formatted_address,
        delivery_options: this.delivery_options,
        location_name: this.location_name,
        address_label: this.address_label,
        delivery_instructions: this.delivery_instructions,
        data: this.location_data,
      };
      APIinterface.saveClientAddress($params)
        .then((data) => {
          this.closeModal();
          APIinterface.setStorage("place_id", data.details.place_id);
          this.$emit("afterSaveaddress");
        })
        .catch((error) => {
          APIinterface.notify("dark", error, "error", this.$q);
        })
        .then((data) => {
          this.loading = false;
        });
    },
  },
};
</script>
