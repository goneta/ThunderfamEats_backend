<template>
  <router-view />
</template>

<script>
import { defineComponent } from "vue";
import APIinterface from "./api/APIinterface";
import config from "./api/config";
import { usePushStore } from "stores/PushStore";
import auth from "./api/auth";
import { useDataStore } from "stores/DataStore";
import { useDataStorePersisted } from "stores/DataStorePersisted";
import { App } from "@capacitor/app";
import { Network } from "@capacitor/network";
import { api } from "boot/axios";
import { FCM } from "@capacitor-community/fcm";
import { PushNotifications } from "@capacitor/push-notifications";

export default defineComponent({
  name: "App",
  data() {
    return {
      token: "",
      close_count: 0,
    };
  },
  setup() {
    const PushStore = usePushStore();
    const DataStore = useDataStore();
    const DataStorePersisted = useDataStorePersisted();
    return { PushStore, DataStore, DataStorePersisted };
  },
  created() {
    this.$q.dark.set(this.DataStorePersisted.dark_mode);

    if (!APIinterface.empty(this.DataStorePersisted.app_language)) {
      this.$i18n.locale = this.DataStorePersisted.app_language;
    }

    if (!APIinterface.empty(this.DataStorePersisted.rtl)) {
      this.$q.lang.set({ rtl: this.DataStorePersisted.rtl });
    }

    api.defaults.params = {};
    api.defaults.params["language"] = this.$i18n.locale;

    if (this.$q.capacitor) {
      this.initPush();
    }
    this.verifyToken();
    if (!this.DataStore.hadPrefix()) {
      this.DataStore.getAttributes();
    }

    this.close_count = 0;

    App.addListener("backButton", (data) => {
      this.close_count++;
      if (!data.canGoBack) {
        if (this.close_count >= 2) {
          this.closeApp();
        } else {
          APIinterface.showToast("Press BACK again to exit");
          setTimeout(() => {
            this.close_count = 0;
          }, 1000);
        }
      }
    });

    App.addListener("appStateChange", (data) => {
      if (data.isActive && this.$q.platform.is.ios && this.$q.capacitor) {
        PushNotifications.removeAllDeliveredNotifications().then((result) => {
          // do nothing
        });
      }
    });

    if (this.$q.capacitor) {
      this.checkNetwork();
      Network.addListener("networkStatusChange", (status) => {
        if (status.connected === false) {
          APIinterface.showToast("No internet connection");
          this.$router.push("/errornetwork");
        }
      });
    }
  },
  methods: {
    async checkNetwork() {
      const status = await Network.getStatus();
      if (status.connected === false) {
        this.$router.push("/errornetwork");
      }
    },
    closeApp() {
      App.exitApp();
    },
    verifyToken() {
      auth
        .authenticate()
        .then((data) => {
          //
        })
        // eslint-disable-next-line
        .catch((error) => {
          auth.logout();
        })
        .then((data) => {});
    },
    initPush() {
      //if (this.$q.platform.is.ios) {
      PushNotifications.checkPermissions().then((result) => {
        console.log(JSON.stringify(result));
        if (result.receive === "prompt") {
          PushNotifications.requestPermissions().then((result) => {
            if (result.receive === "granted") {
              PushNotifications.register();
            }
          });
        } else if (result.receive === "granted") {
          PushNotifications.register();
        } else if (result.receive === "prompt-with-rationale") {
          PushNotifications.register();
        }
      });
      // } else {
      //   PushNotifications.requestPermissions().then((result) => {
      //     if (result.receive === "granted") {
      //       PushNotifications.register();
      //     } else {
      //       APIinterface.notify(
      //         "negative",
      //         "Error on push permission",
      //         "warning",
      //         this.$q
      //       );
      //     }
      //   });
      // }

      FCM.setAutoInit({ enabled: true }).then(() => {
        //
      });

      FCM.isAutoInitEnabled().then((r) => {
        // alert('Auto init is ' + (r.enabled ? 'enabled' : 'disabled'))
      });

      FCM.subscribeTo({ topic: config.topic })
        .then((r) => {
          //
        })
        .catch((error) => {
          APIinterface.notify(
            "red-5",
            "Error subscribing topics" + JSON.stringify(error),
            "warning",
            this.$q
          );
        });

      PushNotifications.addListener("registration", (Token) => {
        if (this.$q.platform.is.android) {
          this.token = Token.value;
          APIinterface.setSession("device_token", this.token);
        } else {
          FCM.getToken()
            .then((r) => {
              this.token = r.token;
              APIinterface.setSession("device_token", this.token);
            })
            .catch((error) => {
              APIinterface.notify("red-5", error, "error_outline", this.$q);
            });
        }
      });

      PushNotifications.addListener("registrationError", (error) => {
        APIinterface.notify(
          "red-5",
          "Error on registration" + JSON.stringify(error),
          "warning",
          this.$q
        );
      });

      PushNotifications.createChannel({
        description: "KMRS mobile app channel",
        id: config.channel,
        importance: 5,
        lights: true,
        name: "kmrs channel",
        sound: config.sound,
        vibration: true,
        visibility: 1,
      })
        .then(() => {
          // alert('push channel created: ')
        })
        .catch((error) => {
          // APIinterface.notify(
          //   "red-5",
          //   "Error on registration" + JSON.stringify(error),
          //   "warning",
          //   this.$q
          // );
        });

      PushNotifications.addListener(
        "pushNotificationReceived",
        (notification) => {
          // alert('Push received: ' + JSON.stringify(notification))
          //this.PushStore.push_modal = true;
          // this.PushStore.ShowModal(true);
          // this.PushStore.push_message = {
          //   title: notification.title,
          //   body: notification.body,
          //   dialog_title: notification.data.dialog_title,
          // };
        }
      );
    },
  },
});
</script>
