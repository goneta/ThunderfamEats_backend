<template>
  <q-header reveal reveal-offset="50" class="bg-white">
    <q-toolbar>
      <q-btn
        @click="$router.back()"
        flat
        round
        dense
        icon="arrow_back"
        color="dark"
      />
      <q-toolbar-title class="text-dark text-center text-weight-bold">
        {{ $t("Delivery Address") }}
      </q-toolbar-title>
    </q-toolbar>
  </q-header>
  <!-- banner -->

  <q-page padding class="bg-grey-2">
    <q-space class="q-pa-xs"></q-space>

    <q-card flat class="no-border-radius">
      <q-list>
        <q-item tag="label" @click="locateLocation">
          <q-item-section avatar>
            <q-avatar
              rounded
              color="amber-2"
              text-color="orange-5"
              icon="eva-navigation-2-outline"
            />
          </q-item-section>
          <q-item-section>
            <q-item-label lines="2" class="font12 text-weight-bold">{{
              $t("Share your location")
            }}</q-item-label>
            <q-item-label caption class="font12 text-weight-medium">{{
              $t("Enabled location services")
            }}</q-item-label>
          </q-item-section>
          <q-item-section side>
            <q-btn
              round
              unelevated
              text-color="dark"
              icon="chevron_right"
              dense
            />
          </q-item-section>
        </q-item>
      </q-list>
    </q-card>
    <q-space class="q-pa-xs"></q-space>

    <q-card flat class="no-border-radius">
      <q-list>
        <q-item
          tag="label"
          :to="{
            path: '/location/map',
            query: {
              url: '/address/select?url=' + this.back_url,
            },
          }"
        >
          <q-item-section avatar>
            <q-avatar
              rounded
              color="blue-2"
              text-color="dark"
              icon="las la-map-marked"
            />
          </q-item-section>
          <q-item-section>
            <q-item-label lines="2" class="font12 text-weight-bold">{{
              $t("Choose from Map")
            }}</q-item-label>
            <q-item-label caption class="font12 text-weight-medium">{{
              $t("select your address from map")
            }}</q-item-label>
          </q-item-section>
          <q-item-section side>
            <q-btn
              round
              unelevated
              text-color="dark"
              icon="chevron_right"
              dense
            />
          </q-item-section>
        </q-item>
      </q-list>
    </q-card>
    <q-space class="q-pa-xs"></q-space>

    <div v-if="loading">
      <template v-for="i in 6" :key="i">
        <q-card flat class="no-border-radius">
          <q-list>
            <q-item tag="label">
              <q-item-section avatar>
                <q-skeleton type="QCheckbox" size="20px" />
              </q-item-section>
              <q-item-section>
                <q-skeleton type="text" />
                <q-skeleton type="text" />
              </q-item-section>
              <q-item-section side>
                <q-skeleton type="circle" size="20px" class="q-mb-sm" />
                <q-skeleton type="circle" size="20px" />
              </q-item-section>
            </q-item>
          </q-list>
        </q-card>
        <q-space class="q-pa-xs"></q-space>
      </template>
    </div>

    <div v-else>
      <template v-for="items in data" :key="items.address_uuid">
        <q-card flat class="no-border-radius">
          <q-list>
            <q-item
              tag="label"
              @click.stop="setAddress(items)"
              :active="isSelected(items.place_id)"
            >
              <q-item-section avatar>
                <q-radio v-model="address_uuid" :val="items.place_id" />
              </q-item-section>
              <q-item-section>
                <q-item-label lines="2" class="font12 text-weight-bold">{{
                  items.address.formatted_address
                }}</q-item-label>
                <q-item-label caption class="font12 text-weight-medium">{{
                  items.address.address2
                }}</q-item-label>
                <q-item-label caption class="font11 text-weight-medium"
                  >Home</q-item-label
                >
              </q-item-section>
              <q-item-section side>
                <q-btn
                  icon="eva-edit-2-outline"
                  dense
                  unelevated
                  rounded
                  size="sm"
                  class="q-mb-sm"
                  @click.stop="editAddress(items)"
                />
                <q-btn
                  icon="eva-trash-2-outline"
                  dense
                  unelevated
                  rounded
                  size="sm"
                  @click.stop="deleteConfirm(items.address_uuid)"
                  :disabled="isSelected(items.place_id)"
                />
              </q-item-section>
            </q-item>
          </q-list>
        </q-card>
        <q-space class="q-pa-xs"></q-space>
      </template>
    </div>

    <q-inner-loading :showing="locate_loading" size="md" color="primary" />

    <AddressDetails
      ref="address_details"
      @after-saveaddress="afterSaveaddress"
    />
  </q-page>
</template>

<script>
import APIinterface from "src/api/APIinterface";
import auth from "src/api/auth";
import { defineAsyncComponent } from "vue";

export default {
  name: "AddressSelect",
  components: {
    AddressDetails: defineAsyncComponent(() =>
      import("components/AddressDetails.vue")
    ),
  },
  data() {
    return {
      modal: false,
      back_url: "",
      data: [],
      address_uuid: "",
      loading: false,
      locate_loading: false,
      location_data: [],
    };
  },
  mounted() {
    this.back_url = this.$route.query.url;
    if (APIinterface.empty(this.back_url)) {
      this.back_url = "/home";
    }
    if (auth.authenticated()) {
      this.getAddresses();
    }

    const placeID = APIinterface.getStorage("place_id");
    if (typeof placeID !== "undefined" && placeID !== null) {
      this.address_uuid = placeID;
    }
  },
  computed: {},
  methods: {
    getAddresses() {
      this.loading = true;
      APIinterface.getAddresses()
        .then((data) => {
          this.data = data.details.data;
        })
        // eslint-disable-next-line
        .catch((error) => {
          //
        })
        .then((data) => {
          this.loading = false;
        });
    },
    afterSelectaddress(data) {
      console.debug(data);
    },
    removeFromList(addressUuid) {
      Object.entries(this.data).forEach(([key, items]) => {
        if (items.address_uuid === addressUuid) {
          console.debug(key);
          this.data.splice(key, 1);
        }
      });
    },
    deleteConfirm(addressUuid) {
      this.$q
        .dialog({
          title: "Confirm",
          message: "Are you sure you want to Delete?",
          persistent: true,
          ok: {
            unelevated: true,
            color: "warning",
            rounded: true,
            "text-color": "black",
            size: "md",
            label: "Yes",
            "no-caps": true,
          },
          cancel: {
            unelevated: true,
            rounded: true,
            color: "grey-3",
            "text-color": "black",
            size: "md",
            label: "Cancel",
            "no-caps": true,
          },
        })
        .onOk(() => {
          APIinterface.deleteAddress(addressUuid)
            .then((data) => {
              this.removeFromList(addressUuid);
            })
            .catch((error) => {
              APIinterface.notify("negative", error, "error_outline", this.$q);
            })
            .then((data) => {});
        })
        .onOk(() => {
          // console.log('>>>> second OK catcher')
        })
        .onCancel(() => {
          // console.log('>>>> Cancel')
        })
        .onDismiss(() => {
          // console.log('I am triggered on both OK and Cancel')
        });
    },
    locateLocation() {
      if (navigator.geolocation) {
        this.locate_loading = true;
        navigator.geolocation.getCurrentPosition(
          (position) => {
            this.locate_loading = false;
            this.reverseGeocoding(
              position.coords.latitude,
              position.coords.longitude
            );
            // eslint-disable-next-line
          },
          (error) => {
            this.locate_loading = false;
          }
        );
      } else {
        this.locate_loading = false;
        APIinterface.notify(
          "negative",
          "Browser doesn't support Geolocation",
          "error_outline",
          this.$q
        );
      }
    },
    reverseGeocoding(lat, lng) {
      this.locate_loading = true;
      APIinterface.reverseGeocoding(lat, lng)
        .then((data) => {
          if (
            typeof data.details.data.address !== "undefined" &&
            data.details.data.address !== null
          ) {
            // APIinterface.setStorage('place_data', data.details.data)
            // APIinterface.setStorage('place_id', data.details.data.place_id)
            this.setAddress(data.details.data);
            this.saveAddress(data.details.data.place_id);
          } else {
            APIinterface.notify(
              "negative",
              "This location is not available",
              "error_outline",
              this.$q
            );
          }
        })
        .catch((error) => {
          APIinterface.notify("negative", error, "error_outline", this.$q);
        })
        .then((data) => {
          this.locate_loading = false;
        });
    },
    setAddress(data) {
      APIinterface.setStorage("place_data", data);
      APIinterface.setStorage("place_id", data.place_id);
      this.backPage();
    },
    backPage() {
      if (!APIinterface.empty(this.back_url)) {
        this.$router.push(this.back_url);
      } else {
        this.$router.push("/home");
      }
    },
    saveAddress(placeID) {
      if (auth.authenticated()) {
        APIinterface.SavePlaceByID(placeID)
          .then((data) => {
            //
          })
          // eslint-disable-next-line
          .catch((error) => {})
          .then((data) => {});
      }
    },
    isSelected(placeID) {
      if (this.address_uuid === placeID) {
        return true;
      }
      return false;
    },
    editAddress(data) {
      this.$refs.address_details.location_data = data;
      this.$refs.address_details.showModal();
      // this.$router.push({
      //   path: '/account/address',
      //   query: {
      //     uuid: addressUuid,
      //     url: '/address/select?url=' + this.back_url
      //   }
      // })
    },
    afterSaveaddress() {
      if (auth.authenticated()) {
        this.getAddresses();
      }
    },
  },
};
</script>
