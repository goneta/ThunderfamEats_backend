<template>
  <!-- <pre>{{ markers }}</pre> -->
  <div ref="cmaps" class="map bg-grey-1" :class="size"></div>
</template>

<script>
import APIinterface from "src/api/APIinterface";
import { loadScript, unloadScript } from "vue-plugin-load-script";

const cmapsMarker = [];
let bounds = [];
let track_bounds;

export default {
  name: "MapsComponents",
  props: ["keys", "provider", "zoom", "center", "markers", "size"],
  data() {
    return {
      cmaps: undefined,
      data: [],
      loading: false,
      map_style: [
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
      ],
      map_style_dark: [
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
      ],
    };
  },
  mounted() {
    this.loadMap();
  },
  watch: {
    markers(newval, oldval) {
      this.renderMap();
    },
    provider(newval, oldval) {
      this.loadMap();
    },
  },
  methods: {
    loadMap() {
      try {
        switch (this.provider) {
          case "google.maps":
            loadScript(
              "https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=places&key=" +
                this.keys +
                "&callback=Function.prototype"
            )
              .then(() => {
                this.renderMap();
              })
              .catch(() => {
                console.debug("failed loading map script");
              });
            break;
          case "mapbox":
            loadScript(
              "https://api.mapbox.com/mapbox-gl-js/v2.11.0/mapbox-gl.js"
            )
              .then(() => {
                this.renderMap();
              })
              .catch(() => {
                console.debug("failed loading mapboxscript");
              });
            break;
        }
      } catch (err) {
        console.error(err);
      }
    },
    renderMap() {
      try {
        switch (this.provider) {
          case "google.maps":
            bounds = new window.google.maps.LatLngBounds();
            if (typeof this.cmaps !== "undefined" && this.cmaps !== null) {
              Object.entries(this.markers).forEach(([key, items]) => {
                this.removeMarker(items.id);
              });
            } else {
              this.cmaps = new window.google.maps.Map(this.$refs.cmaps, {
                center: {
                  lat: parseFloat(this.center.lat),
                  lng: parseFloat(this.center.lng),
                },
                zoom: parseInt(this.zoom),
                disableDefaultUI: true,
                styles: this.$q.dark.mode
                  ? this.map_style_dark
                  : this.map_style,
              });
            }

            Object.entries(this.markers).forEach(([key, items]) => {
              this.addMarker(
                {
                  position: {
                    lat: parseFloat(items.lat),
                    lng: parseFloat(items.lng),
                  },
                  map: this.cmaps,
                  //animation: window.google.maps.Animation.DROP,
                  draggable: items.draggable,
                  label: items.label,
                },
                items.id,
                items.draggable
              );
            });

            if (Object.keys(this.markers).length > 1) {
              this.FitBounds();
            } else {
              this.setCenter(this.markers[0].lat, this.markers[0].lng);
            }
            break;
          case "mapbox":
            mapboxgl.accessToken = this.keys;
            bounds = new mapboxgl.LngLatBounds();
            this.cmaps = new mapboxgl.Map({
              container: this.$refs.cmaps,
              style: "mapbox://styles/mapbox/streets-v12",
              center: [
                parseFloat(this.center.lng),
                parseFloat(this.center.lat),
              ],
              zoom: 14,
            });
            Object.entries(this.markers).forEach(([key, items]) => {
              this.addMarker(
                {
                  position: {
                    lat: parseFloat(items.lat),
                    lng: parseFloat(items.lng),
                  },
                  map: this.cmaps,
                  animation: null,
                  draggable: items.draggable,
                  icon: items.icon,
                },
                items.id,
                items.draggable
              );
            });
            if (Object.keys(this.markers).length > 1) {
              this.FitBounds();
            } else {
              this.setCenter(this.markers[0].lat, this.markers[0].lng);
            }
            break;
        }
      } catch (err) {
        console.error(err);
      }
    },
    addMarker(properties, index, draggable) {
      try {
        switch (this.provider) {
          case "google.maps":
            cmapsMarker[index] = new window.google.maps.Marker(properties);
            this.cmaps.panTo(
              new window.google.maps.LatLng(
                properties.position.lat,
                properties.position.lng
              )
            );
            bounds.extend(cmapsMarker[index].position);

            if (draggable === true) {
              window.google.maps.event.addListener(
                cmapsMarker[index],
                "drag",
                (marker) => {
                  this.$emit("dragMarker", true);
                }
              );

              window.google.maps.event.addListener(
                cmapsMarker[index],
                "dragend",
                (marker) => {
                  const latLng = marker.latLng;
                  this.$emit("afterSelectmap", latLng.lat(), latLng.lng());
                }
              );
            }

            break;

          case "mapbox":
            if (!APIinterface.empty(properties.icon)) {
              const el = document.createElement("div");
              el.className = properties.icon;
              cmapsMarker[index] = new mapboxgl.Marker(el)
                .setLngLat([properties.position.lng, properties.position.lat])
                .addTo(this.cmaps);
            } else {
              cmapsMarker[index] = new mapboxgl.Marker(properties)
                .setLngLat([properties.position.lng, properties.position.lat])
                .addTo(this.cmaps);
            }

            bounds.extend(
              new mapboxgl.LngLat(
                properties.position.lng,
                properties.position.lat
              )
            );
            if (draggable === true) {
              cmapsMarker[index].on("dragend", (event) => {
                const lngLat = cmapsMarker[index].getLngLat();
                this.$emit("afterSelectmap", lngLat.lat, lngLat.lng);
              });
            }
            this.mapBoxResize();
            break;
        }
      } catch (err) {
        console.error(err);
      }
    },
    mapBoxResize() {
      if (this.provider == "mapbox") {
        setTimeout(() => {
          this.cmaps.resize();
        }, 500);
      }
    },
    removeMarker(index) {
      try {
        switch (this.provider) {
          case "google.maps":
            if (
              typeof cmapsMarker[index] !== "undefined" &&
              cmapsMarker[index] !== null
            ) {
              cmapsMarker[index].setMap(null);
              cmapsMarker.splice(index, 1);
            }
            break;
        }
      } catch (err) {
        console.error(err);
      }
    },
    centerMap() {
      this.FitBounds();
    },
    FitBounds() {
      try {
        switch (this.provider) {
          case "google.maps":
            if (!APIinterface.empty(bounds)) {
              this.cmaps.fitBounds(bounds);
            }
            break;
          case "mapbox":
            if (!APIinterface.empty(bounds)) {
              this.cmaps.fitBounds(bounds, { duration: 0, padding: 50 });
            }
            break;
        }
      } catch (err) {
        console.error(err);
      }
    },
    setCenter(lat, lng) {
      try {
        switch (this.provider) {
          case "google.maps":
            this.cmaps.setCenter(new window.google.maps.LatLng(lat, lng));
            break;
          case "mapbox":
            this.cmaps.flyTo({
              center: [lng, lat],
              essential: true,
            });
            break;
        }
      } catch (err) {
        console.error(err);
      }
    },
    //
  },
};
</script>
