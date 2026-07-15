<template>
  <q-layout view="lHh Lpr lFf">
    <q-header reveal class="bg-white" @reveal="revealStateChange">
      <q-toolbar
        class="top-toolbar row"
        dense
        :class="{
          'bg-mydark text-white': $q.dark.mode,
          'text-primary': !$q.dark.mode,
        }"
      >
        <q-btn
          to="/account-menu"
          flat
          dense
          icon="las la-bars"
          class="q-mr-sm"
          :color="$q.dark.mode ? 'white' : 'dark'"
        />
        <q-btn
          @click="this.$refs.delivery_details.showModal(true)"
          unelevated
          :color="$q.dark.mode ? 'grey600' : 'white'"
          :text-color="$q.dark.mode ? 'grey300' : 'dark'"
          dense
          no-caps
          :class="{
            'col-8': DataStore.enabled_language,
            'col-9': !DataStore.enabled_language,
          }"
        >
          <div class="fit row items-center">
            <q-icon name="las la-map-marker-alt" color="grey" size="15px" />
            <div
              class="q-ml-xs font13 ellipsis col-10"
              :class="{
                'text-grey300': $q.dark.mode,
                'text-dark': !$q.dark.mode,
              }"
            >
              {{ PlaceStore.address }}
            </div>
          </div>
        </q-btn>

        <q-btn
          v-if="DataStore.enabled_language"
          to="/account/language"
          flat
          round
          dense
          icon="las la-globe"
          class="q-mr-smx"
          :color="$q.dark.mode ? 'white' : 'grey'"
        />

        <NotiButton></NotiButton>
      </q-toolbar>
    </q-header>

    <q-footer bordered class="bg-white text-dark">
      <q-tabs
        v-model="tab"
        dense
        indicator-color="transparent"
        active-color="secondary"
        :class="{
          'bg-mydark text-white': $q.dark.mode,
          'text-dark': !$q.dark.mode,
        }"
      >
        <q-route-tab
          name="home"
          icon="las la-home"
          :label="$t('Home')"
          no-caps
          to="/home"
        />
        <q-route-tab
          name="browse"
          icon="search"
          :label="$t('Search')"
          no-caps
          to="/search"
        />
        <q-route-tab
          name="cart"
          icon="las la-shopping-bag"
          :label="$t('Cart')"
          no-caps
          to="/cart"
        >
          <q-badge v-if="CartStore.hasItem" color="secondary" floating rounded>
            {{ CartStore.items_count }}
          </q-badge>
        </q-route-tab>

        <q-route-tab
          name="account"
          icon="las la-user-alt"
          :label="$t('Account')"
          no-caps
          to="/account-menu"
        />
      </q-tabs>
    </q-footer>

    <q-page-container>
      <router-view />
    </q-page-container>

    <DeliveryDetails
      ref="delivery_details"
      :back_url="back_url"
      @show-sched="showSched"
      @after-savetrans="afterSavetrans"
      @after-savetranstype="afterSavetranstype"
    />

    <DeliverySched
      ref="delivery_sched"
      slug="0"
      @after-savetrans="afterSavetrans"
    />

    <QuickTrack ref="quick_track" />

    <PushDialog ref="push_dialog" />
  </q-layout>
</template>

<script>
import { Device } from "@capacitor/device";
import { FCM } from "@capacitor-community/fcm";
import { defineComponent, defineAsyncComponent } from "vue";
import APIinterface from "src/api/APIinterface";
import { usePlaceStore } from "stores/PlaceStore";
import { useDeliveryschedStore } from "stores/DeliverySched";
import { useCartStore } from "stores/CartStore";
import auth from "src/api/auth";
import { useDataStore } from "stores/DataStore";
import { useClientStore } from "stores/ClientStore";

export default defineComponent({
  name: "MainLayout",
  components: {
    DeliveryDetails: defineAsyncComponent(() =>
      import("components/DeliveryDetails.vue")
    ),
    DeliverySched: defineAsyncComponent(() =>
      import("components/DeliverySched.vue")
    ),
    NotiButton: defineAsyncComponent(() => import("components/NotiButton.vue")),
    QuickTrack: defineAsyncComponent(() => import("components/QuickTrack.vue")),
    PushDialog: defineAsyncComponent(() => import("components/PushDialog.vue")),
  },
  setup() {
    const PlaceStore = usePlaceStore();
    const deliveryschedStore = useDeliveryschedStore();
    const CartStore = useCartStore();
    const DataStore = useDataStore();
    const ClientStore = useClientStore();
    return {
      PlaceStore,
      deliveryschedStore,
      CartStore,
      DataStore,
      ClientStore,
    };
  },
  data() {
    return {
      tab: "home",
      dialog_location: false,
      dialog_deliverytime: false,
      transaction_type: "delivery",
      location_options: "share_location",
      delivery_time_options: "",
      back_url: "/home?refresh=1",
    };
  },
  created() {
    if (this.$q.capacitor) {
      this.DeviceInit();
    }
  },
  mounted() {
    this.initData();
  },
  methods: {
    initData() {
      this.PlaceStore.getPlace();

      // if (!this.transactionStore.hadData()) {
      //   this.transactionStore.TransactionInfo();
      // }

      //if (!this.deliveryschedStore.hadTransactionList()) {
      this.deliveryschedStore.getDeliverySched(
        APIinterface.getStorage("cart_uuid"),
        0
      );
      //}

      if (!this.CartStore.hadItem()) {
        this.CartStore.getCount();
      }
    },
    showSched() {
      this.$refs.delivery_details.showModal(false);
      this.$refs.delivery_sched.showSched(true);
    },
    afterSavetrans() {
      console.log("afterSavetrans");
      this.deliveryschedStore.getDeliverySched(
        APIinterface.getStorage("cart_uuid"),
        0
      );
    },
    afterSavetranstype(data) {
      console.log("afterSavetranstype");
      // this.deliveryschedStore.getDeliverySched(
      //   APIinterface.getStorage("cart_uuid")
      // );
    },
    DeviceInit() {
      const $deviceToken = APIinterface.getSession("device_token");
      const $isRegistered = APIinterface.getSession("device_registered");
      const $isRegisteredAuth = APIinterface.getSession(
        "device_registered_auth"
      );

      if (auth.authenticated()) {
        if ($isRegisteredAuth !== 1) {
          Device.getId().then((data) => {
            Device.getInfo().then((Info) => {
              this.updateDevice($deviceToken, data.identifier, Info.platform);
            });
          });
        }
      } else {
        if ($isRegistered !== 1) {
          Device.getId().then((data) => {
            Device.getInfo().then((Info) => {
              this.registerDevice($deviceToken, data.identifier, Info.platform);
            });
          });
        }
      }

      this.subsribeDevice();
    },
    registerDevice(token, deviceUuid, platform) {
      APIinterface.registerDevice(token, deviceUuid, platform).then((data) => {
        APIinterface.setSession("device_registered", 1);
      });
    },
    updateDevice(token, deviceUuid, platform) {
      APIinterface.updateDevice(token, deviceUuid, platform).then((data) => {
        APIinterface.setSession("device_registered_auth", 1);
      });
    },
    revealStateChange(data) {
      this.deliveryschedStore.main_layout_header = data;
    },
    subsribeDevice() {
      let $user_data = auth.getUser();
      if ($user_data && !this.ClientStore.push_off) {
        FCM.subscribeTo({ topic: $user_data.client_uuid })
          .then((r) => {
            this.ClientStore.push_notifications = true;
            // APIinterface.notify(
            //   "light-green",
            //   "Succesful subscribe to " + $user_data.client_uuid,
            //   "check_circle",
            //   this.$q
            // );
          })
          .catch((error) => {
            this.ClientStore.push_notifications = false;
            // APIinterface.notify(
            //   "dark",
            //   "Error subscribing topics" + JSON.stringify(error),
            //   "warning",
            //   this.$q
            // );
          });
      }
    },
  },
});
</script>
