<template>
  <q-item v-if="loading">
    <q-item-section side style="width: 120px">
      <q-skeleton class="map small full-width" square />
    </q-item-section>
    <q-item-section top>
      <q-skeleton type="text" />
      <q-skeleton type="text" style="width: 90%" />
      <q-skeleton type="text" style="width: 50%" />
    </q-item-section>
  </q-item>

  <q-item v-else>
    <q-item-section side style="width: 120px">
      <GoogleMap
        v-if="maps_config.key"
        ref="mapRef"
        class="map small full-width bg-grey-3"
        :api-key="maps_config.key"
        :center="map_center"
        :zoom="zoom"
        :disable-default-ui="true"
      >
        <template v-for="items in map_marker" :key="items">
          <Marker :options="items" />
        </template>
      </GoogleMap>
    </q-item-section>
    <q-item-section top>
      <q-item-label class="no-padding no-margin text-weight-bold">
        <q-chip
          size="13px"
          color="white"
          icon="eva-home-outline"
          class="no-padding no-margin transparent"
        >
          {{ attributes.address_label }}
        </q-chip>
      </q-item-label>
      <q-item-label lines="2" class="font13 text-weight-medium">
        {{ address.formatted_address }}
      </q-item-label>
      <q-item-label class="font12 text-weight-light">
        <span class="text-weight-medium">{{ $t("Delivery options") }}:</span>
        {{ attributes.delivery_instructions }}
      </q-item-label>
    </q-item-section>
  </q-item>
</template>

<script>
import APIinterface from "src/api/APIinterface";
import { GoogleMap, Marker } from "vue3-google-map";
// eslint-disable-next-line
import jwt_decode from "jwt-decode";

export default {
  name: "CheckoutAddress",
  data() {
    return {
      loading: false,
      address: {},
      attributes: {},
      maps_config: [],
      map_marker: {},
      map_center: {},
      zoom: 16,
    };
  },
  components: {
    GoogleMap,
    Marker,
  },
  mounted() {
    this.checkoutAddress();
  },
  methods: {
    checkoutAddress() {
      const placeId = APIinterface.getStorage("place_id");
      this.loading = true;
      APIinterface.checkoutAddress(placeId)
        .then((data) => {
          this.address = data.details.data.address;
          this.attributes = data.details.data.attributes;
          this.maps_config = jwt_decode(data.details.maps_config);

          this.zoom = this.maps_config.zoom;

          this.map_center = {
            lat: parseFloat(data.details.data.latitude),
            lng: parseFloat(data.details.data.longitude),
          };
          this.map_marker = [
            {
              position: {
                lat: parseFloat(data.details.data.latitude),
                lng: parseFloat(data.details.data.longitude),
              },
              icon: "placeholder.png",
            },
          ];
        })
        .catch((error) => {
          APIinterface.notify("red-5", error, "error_outline", this.$q);
        })
        .then((data) => {
          this.loading = false;
        });
    },
  },
};
</script>
