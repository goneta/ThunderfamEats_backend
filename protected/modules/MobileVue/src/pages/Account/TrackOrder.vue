<template>
  <q-header
    v-if="$q.platform.is.ios"
    :class="{
      'bg-mydark text-white': $q.dark.mode,
      'bg-white text-black': !$q.dark.mode,
    }"
  >
    <q-toolbar>
      <q-btn
        @click="$router.back()"
        flat
        round
        dense
        icon="close"
        class="q-mr-sm"
        :color="$q.dark.mode ? 'white' : 'dark'"
      />
      <q-toolbar-title class="text-weight-bold">
        {{ $t("Order #") }}{{ order_info.order_id }}
      </q-toolbar-title>
    </q-toolbar>
  </q-header>

  <q-page
    class="row items-stretch"
    :class="{
      'bg-mydark ': $q.dark.mode,
      'bg-grey-1 ': !$q.dark.mode,
    }"
  >
    <template v-if="loading">
      <q-inner-loading :showing="true" size="md" color="primary" />
    </template>
    <template v-else>
      <div class="col-12">
        <div
          v-if="!$q.platform.is.ios"
          class="absolute-top-left full-width q-pa-sm"
          style="z-index: 999"
        >
          <div class="flex">
            <q-btn
              @click="$router.back()"
              round
              dense
              icon="close"
              class="q-mr-sm"
              :color="$q.dark.mode ? 'white' : 'dark'"
              unelevated
              size="sm"
            />
            <div class="bg-white radius8 q-pl-sm q-pr-sm">
              {{ $t("Order #") }}{{ order_info.order_id }}
            </div>
          </div>
        </div>

        <template v-if="!loading && hasMarkers">
          <template v-if="DataStore.hasMapConfig">
            <MapsComponents
              ref="mapRef"
              class="maps"
              size="fit"
              :keys="DataStore.maps_config.key"
              :provider="DataStore.maps_config.provider"
              :zoom="DataStore.maps_config.zoom"
              :center="map_center"
              :markers="map_marker"
              @after-selectmap="afterSelectmap"
            />
          </template>
        </template>
      </div>

      <q-footer
        reveal
        class="bg-white q-pl-md q-pr-md q-pt-md text-dark"
        style="
          border-top-right-radius: 15px;
          border-top-left-radius: 15px;
          bottom: 10px;
        "
      >
        <template v-if="order_info.order_type == 'delivery'">
          <template v-if="order_progress > 0">
            <q-linear-progress
              v-if="order_progress <= 3"
              indeterminate
              color="secondary"
            />
          </template>
        </template>
        <template v-else>
          <template v-if="order_progress > 0">
            <q-linear-progress
              v-if="order_progress <= 2"
              indeterminate
              color="warning"
            />
          </template>
        </template>

        <h5
          class="text-weight-bold q-mt-sm q-mb-none"
          :class="{
            'text-white': $q.dark.mode,
            'text-dark': !$q.dark.mode,
          }"
        >
          {{ order_status }}
        </h5>
        <p
          class="no-margin font11"
          :class="{
            'text-white': $q.dark.mode,
            'text-dark': !$q.dark.mode,
          }"
        >
          {{ order_status_details }}
        </p>

        <!-- steps -->
        <template v-if="order_info.order_type == 'delivery'">
          <q-stepper
            v-model="order_progress"
            ref="stepper"
            color="primary"
            animated
            flat
            class="no-padding tracking-steps"
            :class="{
              'bg-mydark text-white': $q.dark.mode,
              'bg-white text-black': !$q.dark.mode,
            }"
          >
            <q-step
              :name="1"
              title=""
              icon="east"
              active-icon="east"
              done-icon="east"
              active-color="primary"
              :done-color="order_progress > 1 ? 'primary' : 'negative'"
              :done="order_progress > 1 || order_progress <= 0"
            />
            <q-step
              :name="2"
              title=""
              icon="local_dining"
              active-icon="local_dining"
              done-icon="local_dining"
              active-color="primary"
              :done-color="order_progress > 1 ? 'primary' : 'negative'"
              :done="order_progress > 2 || order_progress <= 0"
            />
            <q-step
              :name="3"
              title=""
              icon="drive_eta"
              active-icon="drive_eta"
              done-icon="drive_eta"
              active-color="primary"
              :done-color="order_progress > 1 ? 'primary' : 'negative'"
              :done="order_progress > 3 || order_progress <= 0"
            />
            <q-step
              :name="4"
              title=""
              icon="home"
              active-icon="home"
              done-icon="check_circle"
              active-color="primary"
              :done-color="order_progress > 1 ? 'primary' : 'negative'"
              :done="order_progress >= 4 || order_progress <= 0"
            />
          </q-stepper>
        </template>

        <template v-else>
          <q-stepper
            v-model="order_progress"
            ref="stepper"
            color="primary"
            animated
            flat
            class="no-padding tracking-steps"
          >
            <q-step
              :name="1"
              title=""
              icon="east"
              active-icon="east"
              done-icon="east"
              active-color="primary"
              :done-color="order_progress > 1 ? 'primary' : 'negative'"
              :done="order_progress > 1 || order_progress <= 0"
            />
            <q-step
              :name="2"
              title=""
              icon="local_dining"
              active-icon="local_dining"
              done-icon="local_dining"
              active-color="primary"
              :done-color="order_progress > 1 ? 'primary' : 'negative'"
              :done="order_progress > 2 || order_progress <= 0"
            />
            <q-step
              :name="3"
              title=""
              icon="shopping_basket"
              active-icon="shopping_basket"
              done-icon="check_circle"
              active-color="primary"
              :done-color="order_progress > 1 ? 'primary' : 'negative'"
              :done="order_progress >= 3 || order_progress <= 0"
            />
          </q-stepper>
        </template>
        <!-- steps -->

        <q-list class="q-mb-sm q-mt-sm">
          <q-item clickable>
            <q-item-section avatar>
              <q-avatar>
                <q-img
                  :src="merchant_info.url_logo"
                  :fit="contain"
                  style="height: 50px; max-width: 50px"
                  class="no-margin"
                  loading="lazy"
                />
              </q-avatar>
            </q-item-section>
            <q-item-section>
              <q-item-label lines="1" class="text-weight-bold">{{
                merchant_info.restaurant_name
              }}</q-item-label>
              <q-item-label caption lines="2" class="font11">{{
                merchant_info.merchant_address
              }}</q-item-label>
            </q-item-section>
            <q-item-section side>
              <q-btn
                square
                color="secondary"
                unelevated
                text-color="white"
                icon="las la-phone"
                dense
                class="radius8"
                :href="'tel:' + merchant_info.contact_phone"
              />
            </q-item-section>
          </q-item>
        </q-list>
      </q-footer>
    </template>
  </q-page>

  <ComponentsRealtime
    ref="realtime"
    getevent="tracking"
    @after-receive="afterReceive"
  />
</template>

<script>
import { defineAsyncComponent } from "vue";
import APIinterface from "src/api/APIinterface";
import { useDataStore } from "stores/DataStore";

let bounds = [];
export default {
  name: "TrackOrder",
  components: {
    MapsComponents: defineAsyncComponent(() =>
      import("components/MapsComponents.vue")
    ),
    ComponentsRealtime: defineAsyncComponent(() =>
      import("components/ComponentsRealtime.vue")
    ),
  },
  data() {
    return {
      order_progress: -1,
      order_details: true,
      loading: false,
      order_uuid: "",
      lat: "",
      lng: "",
      merchant_info: [],
      order_info: [],
      items: [],
      meta: [],
      items_count: 0,
      order_status: "",
      order_status_details: "",
      instructions: [],
      map_marker: {},
      map_center: {},
    };
  },
  setup() {
    const DataStore = useDataStore();
    return { DataStore };
  },
  mounted() {
    this.order_uuid = this.$route.query.order_uuid;
    this.getOrder();
  },
  computed: {
    hasMarkers() {
      if (Object.keys(this.map_marker).length > 0) {
        return true;
      }
      return false;
    },
  },
  methods: {
    getOrder() {
      this.loading = true;
      APIinterface.getOrder(this.order_uuid)
        .then((data) => {
          this.merchant_info = data.details.merchant_info;
          this.order_info = data.details.order_info;
          this.items = data.details.items;
          this.meta = data.details.meta;
          this.items_count = data.details.items_count;
          this.order_progress = data.details.progress.order_progress;
          this.order_status = data.details.progress.order_status;
          this.order_status_details =
            data.details.progress.order_status_details;
          this.instructions = data.details.instructions;

          if (this.order_info.order_type === "delivery") {
            this.map_center = {
              lat: parseFloat(this.merchant_info.latitude),
              lng: parseFloat(this.merchant_info.longitude),
            };
            this.map_marker = [
              {
                lat: parseFloat(this.merchant_info.latitude),
                lng: parseFloat(this.merchant_info.longitude),
                label: APIinterface.getIcon("merchant"),
                icon: "marker_icon_merchant",
                draggable: false,
              },
              {
                lat: parseFloat(this.meta.latitude),
                lng: parseFloat(this.meta.longitude),
                label: APIinterface.getIcon("customer"),
                icon: "marker_icon_destination",
                draggable: false,
              },
            ];
          } else {
            this.map_marker = [
              {
                lat: parseFloat(this.merchant_info.latitude),
                lng: parseFloat(this.merchant_info.longitude),
                label: APIinterface.getIcon("merchant"),
                icon: "marker_icon_merchant",
                draggable: false,
              },
            ];
            this.map_center = {
              lat: parseFloat(this.merchant_info.latitude),
              lng: parseFloat(this.merchant_info.longitude),
            };
          }
        })
        .catch((error) => {
          APIinterface.notify("red-5", error, "error_outline", this.$q);
        })
        .then((data) => {
          this.loading = false;
        });
    },
    afterReceive(data) {
      if (data.order_id !== this.order_info.order_id) {
        return false;
      }
      if (data.order_progress === 0) {
        this.order_progress = data.order_progress;
        this.order_status = data.order_status;
        this.order_status_details = data.order_status_details;
      } else if (data.order_progress === -1) {
        // do nothing
      } else {
        this.order_progress = data.order_progress;
        this.order_status = data.order_status;
        this.order_status_details = data.order_status_details;
      }
    },
  },
};
</script>
