<template>
  <q-header
    reveal
    reveal-offset="50"
    :class="{
      'bg-transparent text-white': $q.dark.mode,
      'bg-grey-1 text-dark': !$q.dark.mode,
    }"
  >
    <q-toolbar>
      <q-btn
        to="/home"
        flat
        round
        dense
        icon="las la-angle-left"
        class="q-mr-sm"
        :text-color="$q.dark.mode ? 'white' : 'dark'"
      />
      <q-toolbar-title class="text-weight-bold">{{
        $t("Profile")
      }}</q-toolbar-title>
      <NotiButton></NotiButton>
    </q-toolbar>
  </q-header>
  <q-page
    padding
    class="q-pa-md"
    :class="{ 'bg-transparent': $q.dark.mode, 'bg-grey-1': !$q.dark.mode }"
  >
    <div class="row items-center">
      <div class="col-3">
        <q-avatar>
          <q-img
            :src="data.avatar"
            lazy
            style="height: 50px; max-width: 50px"
            spinner-color="secondary"
            spinner-size="20px"
          />
        </q-avatar>
      </div>
      <div class="col">
        <h5 class="text-weight-bold q-ma-none">
          {{ data.first_name }} {{ data.last_name }}
        </h5>
        <p class="text-weight-medium q-ma-none ellipsis">
          {{ data.email_address }}
        </p>
      </div>
    </div>
    <!-- rows -->

    <q-space class="q-pa-sm"></q-space>

    <q-card
      flat
      class="radius8"
      :class="{ 'bg-grey500': $q.dark.mode, 'bg-white': !$q.dark.mode }"
    >
      <q-card-section>
        <div class="row items-center q-gutter-sm">
          <div class="col text-center">
            <q-btn
              to="/account/allorder"
              round
              color="secondary"
              icon="las la-truck"
              dense
              flat
            />
            <q-btn
              to="/account/allorder"
              flat
              no-caps
              :label="$t('My All orders')"
              class="line-height-one q-pa-none"
              :text-color="$q.dark.mode ? 'bluegrey500' : 'dark'"
            ></q-btn>
          </div>
          <div class="col text-center">
            <q-btn
              :to="{
                path: 'feed',
                query: {
                  query: 'promo',
                  page_title: this.$t('Promo'),
                },
              }"
              round
              color="primary"
              icon="las la-percentage"
              dense
              flat
            />
            <q-btn
              flat
              no-caps
              :label="$t('Offers & Coupons')"
              class="line-height-one q-pa-none"
              :to="{
                path: 'feed',
                query: {
                  query: 'promo',
                  page_title: this.$t('Promo'),
                },
              }"
              :text-color="$q.dark.mode ? 'bluegrey500' : 'dark'"
            ></q-btn>
          </div>
          <div class="col text-center">
            <q-btn
              round
              color="yellow-5"
              icon="las la-map-marker"
              dense
              flat
              to="/account/my-address"
            />
            <q-btn
              to="/account/my-address"
              flat
              no-caps
              :label="$t('Your Addresses')"
              class="line-height-one q-pa-none"
              :text-color="$q.dark.mode ? 'bluegrey500' : 'dark'"
            ></q-btn>
          </div>
        </div>
        <!-- row -->
      </q-card-section>
    </q-card>

    <q-space class="q-pa-sm"></q-space>

    <q-card
      flat
      class="radius8"
      :class="{ 'bg-transparent': $q.dark.mode, 'bg-grey-1': !$q.dark.mode }"
    >
      <q-list class="modified-qlist">
        <q-item clickable v-ripple to="/account/edit-profile">
          <q-item-section avatar>
            <q-icon color="grey-5" name="las la-user-alt" />
          </q-item-section>
          <q-item-section>{{ $t("Manage Profile") }}</q-item-section>
          <q-item-section side>
            <q-btn
              round
              unelevated
              text-color="dark"
              :icon="
                DataStorePersisted.rtl
                  ? 'las la-angle-left'
                  : 'las la-angle-right'
              "
              dense
              class="text-grey-5 font13"
            />
          </q-item-section>
        </q-item>

        <q-item clickable v-ripple to="/account/change-password">
          <q-item-section avatar>
            <q-icon color="grey-5" name="las la-lock" />
          </q-item-section>
          <q-item-section>{{ $t("Change Password") }}</q-item-section>
          <q-item-section side>
            <q-btn
              round
              unelevated
              text-color="dark"
              :icon="
                DataStorePersisted.rtl
                  ? 'las la-angle-left'
                  : 'las la-angle-right'
              "
              dense
              class="text-grey-5 font13"
            />
          </q-item-section>
        </q-item>

        <q-item clickable v-ripple to="/account/payments">
          <q-item-section avatar>
            <q-icon color="grey-5" name="las la-credit-card" />
          </q-item-section>
          <q-item-section>{{ $t("Payment") }}</q-item-section>
          <q-item-section side>
            <q-btn
              round
              unelevated
              text-color="dark"
              :icon="
                DataStorePersisted.rtl
                  ? 'las la-angle-left'
                  : 'las la-angle-right'
              "
              dense
              class="text-grey-5 font13"
            />
          </q-item-section>
        </q-item>

        <q-item clickable v-ripple to="/account/favourites">
          <q-item-section avatar>
            <q-icon color="grey-5" name="lab la-gratipay" />
          </q-item-section>
          <q-item-section>{{ $t("Favourites") }}</q-item-section>
          <q-item-section side>
            <q-btn
              round
              unelevated
              text-color="dark"
              :icon="
                DataStorePersisted.rtl
                  ? 'las la-angle-left'
                  : 'las la-angle-right'
              "
              dense
              class="text-grey-5 font13"
            />
          </q-item-section>
        </q-item>

        <q-item
          v-if="DataStore.points_enabled"
          clickable
          v-ripple
          to="/account/points"
        >
          <q-item-section avatar>
            <q-icon color="grey-5" name="card_giftcard" />
          </q-item-section>
          <q-item-section>{{ $t("Points") }}</q-item-section>
          <q-item-section side>
            <q-btn
              round
              unelevated
              text-color="dark"
              :icon="
                DataStorePersisted.rtl
                  ? 'las la-angle-left'
                  : 'las la-angle-right'
              "
              dense
              class="text-grey-5 font13"
            />
          </q-item-section>
        </q-item>

        <q-item clickable v-ripple to="/booking">
          <q-item-section avatar>
            <q-icon color="grey-5" name="table_restaurant" />
          </q-item-section>
          <q-item-section>{{ $t("Bookings") }}</q-item-section>
          <q-item-section side>
            <q-btn
              round
              unelevated
              text-color="dark"
              :icon="
                DataStorePersisted.rtl
                  ? 'las la-angle-left'
                  : 'las la-angle-right'
              "
              dense
              class="text-grey-5 font13"
            />
          </q-item-section>
        </q-item>

        <q-item clickable v-ripple>
          <q-item-section avatar>
            <q-icon color="grey-5" name="las la-bell" />
          </q-item-section>
          <q-item-section>{{ $t("Push Notifications") }}</q-item-section>
          <q-item-section side>
            <q-toggle
              v-model="ClientStore.push_notifications"
              color="secondary"
              @update:model-value="Updateaccountnotification"
            />
          </q-item-section>
        </q-item>

        <q-item clickable v-ripple>
          <q-item-section avatar>
            <q-icon color="grey-5" name="las la-bell" />
          </q-item-section>
          <q-item-section>{{ $t("Promotional Notifications") }}</q-item-section>
          <q-item-section side>
            <q-toggle
              v-model="promotional_push_notifications"
              color="secondary"
              @update:model-value="Updateaccountpromonotification"
            />
          </q-item-section>
        </q-item>

        <q-item
          v-if="DataStore.multicurrency_enabled"
          clickable
          v-ripple
          to="/account/currency"
        >
          <q-item-section avatar>
            <q-icon color="grey-5" name="toll" />
          </q-item-section>
          <q-item-section>{{ $t("Currency") }}</q-item-section>
          <q-item-section side>
            <q-btn
              no-caps
              :label="getCurrency"
              unelevated
              text-color="dark"
              :icon-right="
                DataStorePersisted.rtl
                  ? 'las la-angle-left'
                  : 'las la-angle-right'
              "
              dense
              class="text-grey-5 font13"
            />
          </q-item-section>
        </q-item>

        <q-item
          v-if="DataStore.enabled_language"
          clickable
          v-ripple
          to="/account/language"
        >
          <q-item-section avatar>
            <q-icon color="grey-5" name="las la-credit-card" />
          </q-item-section>
          <q-item-section>{{ $t("Language") }}</q-item-section>
          <q-item-section side v-if="DataStore.language_data">
            <template v-for="lang in DataStore.language_data.data" :key="lang">
              <q-btn
                v-if="lang.code == this.DataStorePersisted.app_language"
                no-caps
                :label="lang.title"
                unelevated
                text-color="dark"
                :icon-right="
                  DataStorePersisted.rtl
                    ? 'las la-angle-left'
                    : 'las la-angle-right'
                "
                dense
                class="text-grey-5 font13"
              />
            </template>
          </q-item-section>
        </q-item>

        <q-item
          v-if="DataStore.enabled_language"
          clickable
          v-ripple
          @click="rtl = !rtl"
        >
          <q-item-section avatar>
            <q-icon
              color="grey-5"
              :name="
                DataStorePersisted.rtl
                  ? 'format_textdirection_l_to_r'
                  : 'format_textdirection_r_to_l'
              "
            />
          </q-item-section>
          <q-item-section>{{ $t("Direction") }}</q-item-section>
          <q-item-section side>
            <q-btn
              no-caps
              :label="DataStorePersisted.rtl ? $t('LRT') : $t('RTL')"
              unelevated
              text-color="dark"
              :icon-right="
                DataStorePersisted.rtl
                  ? 'las la-angle-left'
                  : 'las la-angle-right'
              "
              dense
              class="text-grey-5 font13"
            />
          </q-item-section>
        </q-item>

        <q-item clickable v-ripple>
          <q-item-section avatar>
            <q-icon color="grey-5" name="las la-adjust" />
          </q-item-section>
          <q-item-section>{{ $t("Dark Mode") }}</q-item-section>
          <q-item-section side>
            <q-toggle v-model="theme_mode" color="secondary" />
          </q-item-section>
        </q-item>

        <q-item clickable v-ripple to="/account/delete">
          <q-item-section avatar>
            <q-icon color="grey-5" name="las la-user-slash" />
          </q-item-section>
          <q-item-section>{{ $t("Delete Account") }}</q-item-section>
          <q-item-section side>
            <q-btn
              round
              unelevated
              text-color="dark"
              :icon="
                DataStorePersisted.rtl
                  ? 'las la-angle-left'
                  : 'las la-angle-right'
              "
              dense
              class="text-grey-5 font13"
            />
          </q-item-section>
        </q-item>

        <q-item clickable v-ripple @click="inviteFriends">
          <q-item-section avatar>
            <q-icon color="grey-5" name="las la-user-friends" />
          </q-item-section>
          <q-item-section>{{ $t("Invite Friends") }}</q-item-section>
          <q-item-section side>
            <q-btn
              round
              unelevated
              text-color="dark"
              :icon="
                DataStorePersisted.rtl
                  ? 'las la-angle-left'
                  : 'las la-angle-right'
              "
              dense
              class="text-grey-5 font13"
            />
          </q-item-section>
        </q-item>

        <q-item to="/legal" clickable v-ripple>
          <q-item-section avatar>
            <q-icon color="grey-5" name="las la-balance-scale" />
          </q-item-section>
          <q-item-section>{{ $t("Legal") }}</q-item-section>
          <q-item-section side>
            <q-btn
              round
              unelevated
              text-color="dark"
              :icon="
                DataStorePersisted.rtl
                  ? 'las la-angle-left'
                  : 'las la-angle-right'
              "
              dense
              class="text-grey-5 font13"
            />
          </q-item-section>
        </q-item>

        <q-item clickable v-ripple @click="logout">
          <q-item-section avatar>
            <q-icon color="secondary" name="las la-sign-out-alt" />
          </q-item-section>
          <q-item-section>{{ $t("Logout") }}</q-item-section>
          <q-item-section side>
            <q-btn
              round
              unelevated
              text-color="dark"
              :icon="
                DataStorePersisted.rtl
                  ? 'las la-angle-left'
                  : 'las la-angle-right'
              "
              dense
              class="text-grey-5 font13"
            />
          </q-item-section>
        </q-item>
      </q-list>
    </q-card>
  </q-page>
</template>

<script>
import { defineComponent, defineAsyncComponent } from "vue";
import { FCM } from "@capacitor-community/fcm";
import auth from "src/api/auth";
import APIinterface from "src/api/APIinterface";
import { useDataStore } from "stores/DataStore";
import { useDataStorePersisted } from "stores/DataStorePersisted";
import { useClientStore } from "stores/ClientStore";
import { Share } from "@capacitor/share";

export default {
  name: "AccountMenu",
  components: {
    NotiButton: defineAsyncComponent(() => import("components/NotiButton.vue")),
  },
  data() {
    return {
      data: [],
      theme_mode: false,
      app_push_notifications: false,
      promotional_push_notifications: false,
      user_settings: {},
      rtl: false,
    };
  },
  setup() {
    const DataStore = useDataStore();
    const DataStorePersisted = useDataStorePersisted();
    const ClientStore = useClientStore();
    return { DataStore, DataStorePersisted, ClientStore };
  },
  watch: {
    theme_mode(newval, oldval) {
      this.$q.dark.set(newval);
      this.DataStorePersisted.dark_mode = newval;
    },
    rtl(newval, oldval) {
      this.DataStorePersisted.rtl = newval;
      this.$q.lang.set({ rtl: newval });
    },
  },
  created() {
    this.data = auth.getUser();
    this.authenticate();

    this.theme_mode = this.DataStorePersisted.dark_mode;
    this.$q.dark.set(this.theme_mode);

    this.rtl = this.DataStorePersisted.rtl;

    this.app_push_notifications =
      this.ClientStore.user_settings.app_push_notifications == 1 ? true : false;
    this.promotional_push_notifications =
      this.ClientStore.user_settings.promotional_push_notifications == 1
        ? true
        : false;
  },
  computed: {
    getCurrency() {
      if (Object.keys(this.DataStore.currency_list).length > 0) {
        let Currency = this.DataStorePersisted.use_currency_code
          ? this.DataStorePersisted.use_currency_code
          : this.DataStore.default_currency_code;
        return Currency;
      }
      return false;
    },
  },
  methods: {
    authenticate() {
      auth
        .authenticate()
        .then((data) => {
          //
        })
        .catch((error) => {
          APIinterface.notify("dark", error, "error", this.$q);
          auth.logout();
          this.$router.push("/user/login");
        })
        .then((data) => {});
    },
    logout() {
      this.$q
        .dialog({
          title: this.$t("Logout"),
          message: this.$t("Are you sure you want to logout?"),
          persistent: true,
          position: "standard",
          transitionShow: "fade",
          transitionHide: "fade",
          ok: {
            unelevated: true,
            color: "primary",
            rounded: false,
            "text-color": "white",
            size: "md",
            label: this.$t("Yes"),
            "no-caps": true,
          },
          cancel: {
            unelevated: true,
            rounded: false,
            color: "grey-3",
            "text-color": "black",
            size: "md",
            label: this.$t("Cancel"),
            "no-caps": true,
          },
        })
        .onOk(() => {
          if (this.$q.capacitor) {
            let $user_data = auth.getUser();
            if ($user_data) {
              this.unsubscribeToTopic($user_data.client_uuid);
            }
          }

          auth.logout();
          this.ClientStore.user_settings = [];
          this.$router.push("/home");
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
    Updateaccountnotification(value) {
      if (this.$q.capacitor) {
        if (value) {
          this.subsribeDevice();
        } else {
          let $user_data = auth.getUser();
          if ($user_data) {
            this.unsubscribeToTopic($user_data.client_uuid);
          }
        }
      } else {
        APIinterface.fetchDataByTokenPost(
          "Updateaccountnotification",
          "app_push_notifications=" + this.ClientStore.push_notifications
        )
          .then((data) => {
            this.ClientStore.user_settings.app_push_notifications =
              data.details.app_push_notifications;
          })
          .catch((error) => {})
          .then((data) => {});
      }
    },
    Updateaccountpromonotification() {
      APIinterface.fetchDataByTokenPost(
        "Updateaccountpromonotification",
        "promotional_push_notifications=" + this.promotional_push_notifications
      )
        .then((data) => {
          this.ClientStore.user_settings.promotional_push_notifications =
            data.details.promotional_push_notifications;
        })
        .catch((error) => {})
        .then((data) => {});
    },
    inviteFriends() {
      if (this.$q.capacitor) {
        Share.share({
          title: this.DataStore.invite_friend_settings.title,
          text: this.DataStore.invite_friend_settings.text,
          url: this.DataStore.invite_friend_settings.url,
          dialogTitle: "",
        })
          .then((data) => {
            //
          })
          .catch((error) => {
            //APIinterface.notify("dark", error, "error", this.$q);
          });
      } else {
        if (navigator.share) {
          navigator
            .share({
              title: this.DataStore.invite_friend_settings.title,
              text: this.DataStore.invite_friend_settings.text,
              url: this.DataStore.invite_friend_settings.url,
            })
            .then(() => console.log("Successful share"))
            .catch((error) => console.log("Error sharing", error));
        } else {
          if (this.$q.capacitor) {
            APIinterface.showToast("Share not supported");
          } else {
            APIinterface.notify(
              "dark",
              "Share not supported",
              "error",
              this.$q
            );
          }
        }
      }
    },
    subsribeDevice() {
      let $user_data = auth.getUser();
      if ($user_data) {
        FCM.subscribeTo({ topic: $user_data.client_uuid })
          .then((r) => {
            this.ClientStore.push_notifications = true;
            this.ClientStore.push_off = false;
          })
          .catch((error) => {
            this.ClientStore.push_notifications = false;
          });
      }
    },
    unsubscribeToTopic(data) {
      FCM.unsubscribeFrom({ topic: data })
        .then(() => {
          this.ClientStore.push_notifications = false;
          this.ClientStore.push_off = true;
        })
        .catch((err) => {});
    },
  },
};
</script>
