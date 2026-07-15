<template>
  <GoogleMap
    ref="mapRef"
    :api-key="api_key"
    :center="center"
    :zoom="default_zoom"
    class="fit"
    :disable-default-ui="true"
    :styles="$q.dark.mode ? map_style_dark : map_style"
  >
    <template v-for="items in markers" :key="items">
      <Marker :options="items" />
    </template>
  </GoogleMap>
</template>

<script>
import { GoogleMap, Marker } from "vue3-google-map";
import { ref, watch } from "vue";
import { useDataStore } from "stores/DataStore";

let bounds = [];
export default {
  name: "MapComponents",
  components: { GoogleMap, Marker },
  props: ["center", "markers"],
  setup(props) {
    const mapRef = ref(null);
    const cmapsMarker = ref([]);
    const api_key = ref("");
    const default_zoom = ref("");
    const DataStore = useDataStore();

    const map_style = ref([
      {
        featureType: "administrative",
        elementType: "labels.text.fill",
        stylers: [
          {
            color: "#686868",
          },
        ],
      },
      {
        featureType: "landscape",
        elementType: "all",
        stylers: [
          {
            color: "#f2f2f2",
          },
        ],
      },
      {
        featureType: "poi",
        elementType: "all",
        stylers: [
          {
            visibility: "off",
          },
        ],
      },
      {
        featureType: "road",
        elementType: "all",
        stylers: [
          {
            saturation: -100,
          },
          {
            lightness: 45,
          },
        ],
      },
      {
        featureType: "road.highway",
        elementType: "all",
        stylers: [
          {
            visibility: "simplified",
          },
        ],
      },
      {
        featureType: "road.highway",
        elementType: "geometry.fill",
        stylers: [
          {
            lightness: "-22",
          },
        ],
      },
      {
        featureType: "road.highway",
        elementType: "geometry.stroke",
        stylers: [
          {
            saturation: "11",
          },
          {
            lightness: "-51",
          },
        ],
      },
      {
        featureType: "road.highway",
        elementType: "labels.text",
        stylers: [
          {
            saturation: "3",
          },
          {
            lightness: "-56",
          },
          {
            weight: "2.20",
          },
        ],
      },
      {
        featureType: "road.highway",
        elementType: "labels.text.fill",
        stylers: [
          {
            lightness: "-52",
          },
        ],
      },
      {
        featureType: "road.highway",
        elementType: "labels.text.stroke",
        stylers: [
          {
            weight: "6.13",
          },
        ],
      },
      {
        featureType: "road.highway",
        elementType: "labels.icon",
        stylers: [
          {
            lightness: "-10",
          },
          {
            gamma: "0.94",
          },
          {
            weight: "1.24",
          },
          {
            saturation: "-100",
          },
          {
            visibility: "off",
          },
        ],
      },
      {
        featureType: "road.arterial",
        elementType: "geometry",
        stylers: [
          {
            lightness: "-16",
          },
        ],
      },
      {
        featureType: "road.arterial",
        elementType: "labels.text.fill",
        stylers: [
          {
            saturation: "-41",
          },
          {
            lightness: "-41",
          },
        ],
      },
      {
        featureType: "road.arterial",
        elementType: "labels.text.stroke",
        stylers: [
          {
            weight: "5.46",
          },
        ],
      },
      {
        featureType: "road.arterial",
        elementType: "labels.icon",
        stylers: [
          {
            visibility: "off",
          },
        ],
      },
      {
        featureType: "road.local",
        elementType: "geometry.fill",
        stylers: [
          {
            weight: "0.72",
          },
          {
            lightness: "-16",
          },
        ],
      },
      {
        featureType: "road.local",
        elementType: "labels.text.fill",
        stylers: [
          {
            lightness: "-37",
          },
        ],
      },
      {
        featureType: "transit",
        elementType: "all",
        stylers: [
          {
            visibility: "off",
          },
        ],
      },
      {
        featureType: "water",
        elementType: "all",
        stylers: [
          {
            color: "#b7e4f4",
          },
          {
            visibility: "on",
          },
        ],
      },
    ]);

    const map_style_dark = [
      {
        featureType: "all",
        elementType: "labels.text.fill",
        stylers: [
          {
            color: "#ffffff",
          },
        ],
      },
      {
        featureType: "all",
        elementType: "labels.text.stroke",
        stylers: [
          {
            color: "#000000",
          },
          {
            lightness: 13,
          },
        ],
      },
      {
        featureType: "administrative",
        elementType: "geometry.fill",
        stylers: [
          {
            color: "#000000",
          },
        ],
      },
      {
        featureType: "administrative",
        elementType: "geometry.stroke",
        stylers: [
          {
            color: "#144b53",
          },
          {
            lightness: 14,
          },
          {
            weight: 1.4,
          },
        ],
      },
      {
        featureType: "landscape",
        elementType: "all",
        stylers: [
          {
            color: "#08304b",
          },
        ],
      },
      {
        featureType: "poi",
        elementType: "geometry",
        stylers: [
          {
            color: "#0c4152",
          },
          {
            lightness: 5,
          },
        ],
      },
      {
        featureType: "road.highway",
        elementType: "geometry.fill",
        stylers: [
          {
            color: "#000000",
          },
        ],
      },
      {
        featureType: "road.highway",
        elementType: "geometry.stroke",
        stylers: [
          {
            color: "#0b434f",
          },
          {
            lightness: 25,
          },
        ],
      },
      {
        featureType: "road.arterial",
        elementType: "geometry.fill",
        stylers: [
          {
            color: "#000000",
          },
        ],
      },
      {
        featureType: "road.arterial",
        elementType: "geometry.stroke",
        stylers: [
          {
            color: "#0b3d51",
          },
          {
            lightness: 16,
          },
        ],
      },
      {
        featureType: "road.local",
        elementType: "geometry",
        stylers: [
          {
            color: "#000000",
          },
        ],
      },
      {
        featureType: "transit",
        elementType: "all",
        stylers: [
          {
            color: "#146474",
          },
        ],
      },
      {
        featureType: "water",
        elementType: "all",
        stylers: [
          {
            color: "#021019",
          },
        ],
      },
    ];

    const getMapKeys = () => {
      try {
        const $decoded = DataStore.maps_config;
        api_key.value = $decoded.key;
        default_zoom.value = $decoded.zoom;
      } catch (err) {
        console.debug(err);
      }
    };

    getMapKeys();

    watch(
      () => mapRef.value?.ready,
      (ready) => {
        if (!ready) return;
        centerMap();
      }
    );

    const centerMap = () => {
      bounds = new window.google.maps.LatLngBounds();
      Object.entries(props.markers).forEach(([key, items]) => {
        cmapsMarker[key] = new window.google.maps.Marker(items.position);
        bounds.extend(cmapsMarker[key]);
      });
      FitBounds();
    };

    const FitBounds = () => {
      mapRef.value.map.fitBounds(bounds);
    };

    return {
      mapRef,
      api_key,
      default_zoom,
      map_style,
      centerMap,
      map_style_dark,
    };
  },
};
</script>
