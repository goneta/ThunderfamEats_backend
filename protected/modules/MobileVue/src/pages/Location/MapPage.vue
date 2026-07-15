<template>
  <q-page class="bg-grey-1 row items-stretch">
    <div class="col-12 relative-position">
      <div
        class="absolute-top-left full-width text-rightx q-pa-sm"
        style="z-index: 999"
      >
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
      </div>
      <div
        class="absolute-bottom-left full-width text-right q-pa-sm"
        style="z-index: 99"
      >
        <q-btn
          @click="locateCurrentLocation"
          round
          color="secondary"
          icon="o_near_me"
          unelevated
          :loading="loading2"
          class="rotate-270"
        />
      </div>

      <template v-if="DataStore.hasMapConfig">
        <MapsComponents
          ref="mapRef"
          class="maps"
          size="fit"
          :keys="maps_config.key"
          :provider="maps_config.provider"
          :zoom="maps_config.zoom"
          :center="center"
          :markers="marker_position"
          @after-selectmap="afterSelectmap"
        >
        </MapsComponents>
      </template>
    </div>

    <q-inner-loading :showing="geocoder_loading" size="md" color="primary" />

    <q-footer
      reveal
      class="bg-whitex q-pl-md q-pr-md q-pb-lg q-pt-md text-dark"
      style="border-top-right-radius: 15px; border-top-left-radius: 15px"
      :class="{
        'bg-white': !loading,
        'bg-grey-1': loading,
      }"
    >
      <q-inner-loading :showing="loading" color="primary" size="md" />

      <template v-if="!loading">
        <div
          class="row items-center q-mb-md no-wrap"
          :class="{
            'text-white': $q.dark.mode,
            'text-dark': !$q.dark.mode,
          }"
        >
          <div class="q-pr-sm">
            <q-icon name="las la-map-marker" size="md" />
          </div>
          <div class="font13 col-9">
            <template v-if="!hasAddress">
              {{ address }}
            </template>
            <template v-else> {{ $t("Location is not available") }} </template>
          </div>
          <div>
            <q-btn
              round
              color="primary"
              icon="edit"
              unelevated
              size="md"
              flat
              @click="modal = !modal"
            />
          </div>
        </div>

        <q-btn
          :label="$t('Confirm Location')"
          @click="setLocation"
          :disable="hasAddress || geocoder_loading"
          :loading="loading"
          unelevated
          no-caps
          :color="geocoder_loading == true ? 'grey' : 'primary'"
          text-color="white"
          class="full-width text-weight-medium radius28"
          size="lg"
        />
      </template>
      <template v-else>
        <div class="q-pa-lg">&nbsp;</div>
      </template>
    </q-footer>
  </q-page>

  <q-dialog
    v-model="modal"
    transition-show="fade"
    transition-hide="fade"
    @show="addressFocus()"
    :maximized="true"
  >
    <q-card
      class="no-shadow q-pt-sm q-pb-sm"
      :class="{ 'bg-mydark': $q.dark.mode, 'bg-white': !$q.dark.mode }"
    >
      <q-bar class="bg-white">
        <q-space />
        <q-btn dense flat icon="close" v-close-popup>
          <q-tooltip class="bg-white text-primary">Close</q-tooltip>
        </q-btn>
      </q-bar>
      <q-card-section>
        <SearchAddress
          ref="search_address"
          @after-selectaddress="afterSelectaddress"
          :placeholder="$t('Enter your location')"
        />
      </q-card-section>
    </q-card>
  </q-dialog>

  <DeliverySched ref="delivery_sched" @after-savetrans="afterSavetrans" />

  <AddressInformation
    ref="address_information"
    :back_url="back_url"
  ></AddressInformation>
</template>

<script>
import { defineAsyncComponent } from "vue";
import APIinterface from "src/api/APIinterface";
import auth from "src/api/auth";
import { useDataStore } from "stores/DataStore";
import { useClientStore } from "stores/ClientStore";
import { useMapStore } from "stores/MapStore";
import AppLocation from "src/api/AppLocation";

export default {
  name: "MapPage",
  components: {
    SearchAddress: defineAsyncComponent(() =>
      import("components/SearchAddress.vue")
    ),
    DeliverySched: defineAsyncComponent(() =>
      import("components/DeliverySched.vue")
    ),
    MapsComponents: defineAsyncComponent(() =>
      import("components/MapsComponents.vue")
    ),
    AddressInformation: defineAsyncComponent(() =>
      import("components/AddressInformation.vue")
    ),
  },
  setup() {
    const DataStore = useDataStore();
    const ClientStore = useClientStore();
    const MapStore = useMapStore();
    return { DataStore, ClientStore, MapStore };
  },
  data() {
    return {
      address: "",
      address_search: "",
      modal: false,
      geocoder_loading: true,
      center: { lat: 34.04703, lng: -118.24686 },
      data: [],
      marker_position: {},
      default_icon: {
        text: "\uea44",
        fontFamily: "Material Icons",
        color: "#ffffff",
        fontSize: "18px",
      },
      icon: {},
      back_url: "",
      loading: true,
      maps_config: [],
    };
  },
  created() {
    this.back_url = this.$route.query.url;
    this.icon = this.default_icon;
  },
  computed: {
    hasAddress() {
      if (APIinterface.empty(this.address)) {
        return true;
      }
      return false;
    },
  },
  watch: {
    DataStore: {
      immediate: true,
      deep: true,
      handler(newValue, oldValue) {
        console.log(newValue.loading);
        if (Object.keys(newValue.maps_config).length > 0) {
          this.maps_config = newValue.maps_config;

          this.setMarkerPosition(
            this.maps_config.default_lat,
            this.maps_config.default_lng
          );
          this.checkSavedLocation();
        } else if (newValue.loading == false) {
          this.geocoder_loading = false;
        }
      },
    },
  },
  methods: {
    setMarkerPosition(lat, lng) {
      this.marker_position = [
        {
          id: 0,
          lat: parseFloat(lat),
          lng: parseFloat(lng),
          label: APIinterface.getIcon("customer"),
          icon: null,
          draggable: true,
        },
      ];
    },
    locateCurrentLocation() {
      this.geocoder_loading = true;
      this.locationPermission();
    },
    checkSavedLocation() {
      let $data = APIinterface.getStorage("place_data");
      if (!APIinterface.empty($data)) {
        this.geocoder_loading = false;
        this.loading = false;
        this.data = $data;
        this.address_search = $data.address.formatted_address;
        this.address = $data.address.formatted_address;

        this.center = {
          lat: parseFloat(this.data.latitude),
          lng: parseFloat(this.data.longitude),
        };

        this.setMarkerPosition(this.data.latitude, this.data.longitude);
      } else {
        this.geocoder_loading = false;
        this.locationPermission();
      }
    },
    addressFocus() {
      this.$refs.search_address.Focus();
    },
    afterSelectaddress(data) {
      this.data = data;
      this.address = data.address.formatted_address;
      this.address_search = data.address.formatted_address;
      this.modal = false;

      this.setMarkerPosition(data.latitude, data.longitude);
      this.center = {
        lat: parseFloat(data.latitude),
        lng: parseFloat(data.longitude),
      };
    },
    reverseGeocoding(lat, lng) {
      this.geocoder_loading = true;
      APIinterface.reverseGeocoding(lat, lng)
        .then((data) => {
          this.data = data.details.data;
          if (
            typeof data.details.data.address !== "undefined" &&
            data.details.data.address !== null
          ) {
            this.address = data.details.data.address.formatted_address;
          } else {
            this.address = "";
            this.data = [];
          }
        })
        .catch((error) => {
          APIinterface.notify("dark", error, "error", this.$q);
        })
        .then((data) => {
          this.geocoder_loading = false;
          this.loading = false;
        });
    },
    setLocation() {
      if (APIinterface.empty(this.data.place_id)) {
        APIinterface.notify(
          "dark",
          "Enter your location or select on the map",
          "error",
          this.$q
        );
      }

      APIinterface.setStorage("place_data", this.data);
      APIinterface.setStorage("place_id", this.data.place_id);
      const deliverySched = APIinterface.getStorage("delivery_sched");
      console.debug("deliverySched=>" + deliverySched);

      if (auth.authenticated()) {
        this.$refs.address_information.show(this.data);
      } else {
        this.geocoder_loading = true;
        if (APIinterface.empty(deliverySched)) {
          this.geocoder_loading = false;
          this.$refs.delivery_sched.showSched(true);
        } else {
          this.DataStore.list_data = [];
          this.backPage();
        }
      }
    },
    backPage() {
      if (!APIinterface.empty(this.back_url)) {
        this.$router.push(this.back_url);
      } else {
        this.$router.push("/home");
      }
    },
    afterSavetrans(data) {
      this.backPage();
    },
    locationPermission() {
      if (this.$q.capacitor) {
        //android
        AppLocation.checkAccuracy()
          .then((data) => {
            this.locateLocation();
          })
          .catch((error) => {
            if (error.code === 4) {
              this.geocoder_loading = false;

              this.setMarkerPosition(
                this.maps_config.default_lat,
                this.maps_config.default_lng
              );
              this.reverseGeocoding(
                parseFloat(this.maps_config.default_lat),
                parseFloat(this.maps_config.default_lng)
              );
            } else {
              APIinterface.notify("dark", error.message, "error", this.$q);
            }
          })
          .then((data) => {
            //
          });
      } else {
        //web
        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(
            (data) => {
              this.setMarkerPosition(
                data.coords.latitude,
                data.coords.longitude
              );
              this.reverseGeocoding(
                data.coords.latitude,
                data.coords.longitude
              );
            },
            (error) => {
              this.setMarkerPosition(
                this.maps_config.default_lat,
                this.maps_config.default_lng
              );
              this.reverseGeocoding(
                parseFloat(this.maps_config.default_lat),
                parseFloat(this.maps_config.default_lng)
              );
            }
          );
        }
      }
    },
    locateLocation() {
      AppLocation.getPosition()
        .then((data) => {
          this.setMarkerPosition(data.lat, data.lng);
          this.reverseGeocoding(data.lat, data.lng);
        })
        .catch((error) => {
          this.setMarkerPosition(
            this.maps_config.default_lat,
            this.maps_config.default_lng
          );
          this.reverseGeocoding(
            parseFloat(this.maps_config.default_lat),
            parseFloat(this.maps_config.default_lng)
          );
        })
        .then((data) => {});
    },
    afterSelectmap(lat, lng) {
      console.log("afterSelectmap =>" + lat + lng);
      this.reverseGeocoding(lat, lng);
    },
  },
};
</script>
