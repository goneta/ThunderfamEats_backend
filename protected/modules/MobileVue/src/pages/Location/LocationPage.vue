<template>
  <q-page class="flex flex-center q-pl-md q-pr-md">
    <div class="full-width text-center">
      <q-img
        src="cuttery.png"
        fit="fill"
        spinner-color="primary"
        style="height: 130px; max-width: 120px"
      />

      <h5 class="text-weight-bold">{{ $t("Let's find best restaurant") }}</h5>
      <p class="text-weight-medium q-ma-none">
        {{
          $t(
            "Set your location to started searching for restaurant in your area"
          )
        }}
      </p>
      <q-space class="q-pa-sm"></q-space>
      <q-btn
        unelevated
        no-caps
        color="primary text-white"
        class="full-width text-weight-bold radius28 q-mb-md"
        size="lg"
        @click="locationPermission"
      >
        <div class="row items-center justify-start fit q-pl-md">
          <div class="q-mr-md">
            <q-icon name="las la-search-location" />
          </div>
          <div>{{ $t("Share location") }}</div>
        </div>
      </q-btn>
      <q-btn
        unelevated
        no-caps
        color="primary text-white"
        class="full-width text-weight-bold radius28"
        size="lg"
        to="/location/map"
      >
        <div class="row items-center justify-start fit q-pl-md">
          <div class="q-mr-md">
            <q-icon name="las la-map-marker" />
          </div>
          <div>{{ $t("Choose from map") }}</div>
        </div>
      </q-btn>
    </div>

    <q-inner-loading
      :showing="loading"
      color="primary"
      size="md"
      :label="loading_label"
    />
  </q-page>
  <DeliverySched ref="delivery_sched" @after-savetrans="afterSavetrans" />
</template>

<script>
import { defineAsyncComponent } from "vue";
import AppLocation from "src/api/AppLocation";
import APIinterface from "src/api/APIinterface";
import { useDataStore } from "stores/DataStore";
import auth from "src/api/auth";

export default {
  name: "LocationPage",
  data() {
    return {
      position: [],
      loading: false,
      loading_label: "",
      data: [],
      address: "",
    };
  },
  components: {
    DeliverySched: defineAsyncComponent(() =>
      import("components/DeliverySched.vue")
    ),
  },
  setup() {
    const DataStore = useDataStore();
    return { DataStore };
  },
  methods: {
    locationPermission() {
      if (this.$q.capacitor) {
        //android
        APIinterface.showLoadingBox("", this.$q);
        AppLocation.checkAccuracy()
          .then((data) => {
            this.locateLocation();
          })
          .catch((error) => {
            if (error.code === 4) {
              this.reverseGeocoding(
                this.DataStore.maps_config.default_lat,
                this.DataStore.maps_config.default_lng
              );
            } else {
              APIinterface.hideLoadingBox(this.$q);
              APIinterface.notify("dark", error.message, "error", this.$q);
            }
          })
          .then((data) => {
            //
          });
      } else {
        //web
        if (navigator.geolocation) {
          APIinterface.showLoadingBox("", this.$q);
          navigator.geolocation.getCurrentPosition(
            (data) => {
              this.reverseGeocoding(
                data.coords.latitude,
                data.coords.longitude
              );
            },
            (error) => {
              this.reverseGeocoding(
                this.DataStore.maps_config.default_lat,
                this.DataStore.maps_config.default_lng
              );
            }
          );
        }
      }
    },
    locateLocation() {
      AppLocation.getPosition()
        .then((data) => {
          this.reverseGeocoding(data.lat, data.lng);
        })
        .catch((error) => {
          this.reverseGeocoding(
            this.DataStore.maps_config.default_lat,
            this.DataStore.maps_config.default_lng
          );
        })
        .then((data) => {});
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
            this.setLocation();
          } else {
            APIinterface.notify(
              "dark",
              "This location is not available",
              "error",
              this.$q
            );
            this.address = "";
            this.data = [];
          }
        })
        .catch((error) => {
          APIinterface.notify("dark", error, "error", this.$q);
        })
        .then((data) => {
          this.loading = false;
          APIinterface.hideLoadingBox(this.$q);
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
        this.loading = true;
        APIinterface.SavePlaceByID(this.data.place_id)
          .then((data) => {
            if (APIinterface.empty(deliverySched)) {
              this.$refs.delivery_sched.showSched(true);
            } else {
              this.backPage();
            }
          })
          .catch((error) => {
            APIinterface.notify("dark", error, "error", this.$q);
          })
          .then((data) => {
            this.loading = false;
          });
      } else {
        if (APIinterface.empty(deliverySched)) {
          this.$refs.delivery_sched.showSched(true);
        } else {
          this.backPage();
        }
      }
    },
    afterSavetrans(data) {
      this.backPage();
    },
    backPage() {
      if (!APIinterface.empty(this.back_url)) {
        this.$router.push(this.back_url);
      } else {
        this.$router.replace("/home");
      }
    },
  },
};
</script>
