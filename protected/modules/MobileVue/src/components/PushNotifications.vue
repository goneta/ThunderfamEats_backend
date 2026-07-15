<template>
  <div></div>
</template>

<script>
/* eslint-disable */
import { PushNotifications } from "@capacitor/push-notifications";
import { Device } from "@capacitor/device";
import APIinterface from "src/api/APIinterface";
import auth from "src/api/auth";

export default {
  name: "PushNotifications",
  data() {
    return {
      token: "",
      device_uuid: "",
      platform: "",
    };
  },
  mounted() {
    //if (this.$q.platform.is.mobile) {
    if (this.$q.capacitor) {
      this.initPush();
    }
  },
  methods: {
    initPush() {
      PushNotifications.requestPermissions().then((result) => {
        if (result.receive === "granted") {
          PushNotifications.register();
        } else {
          APIinterface.notify(
            "negative",
            "Error on push permission",
            "warning",
            this.$q
          );
        }
      });

      PushNotifications.addListener("registration", (Token) => {
        this.token = Token.value;
        let $currentToken = APIinterface.getStorage("device_token");
        if (APIinterface.empty($currentToken)) {
          APIinterface.setStorage("device_token", this.token);
        }

        Device.getId().then((data) => {
          this.device_uuid = data.uuid;
          Device.getInfo().then((Info) => {
            this.platform = Info.platform;
            if (auth.authenticated()) {
              this.updateDevice();
            } else {
              this.registerDevice();
            }
          });
        });
      });

      PushNotifications.addListener("registrationError", (error) => {
        APIinterface.notify(
          "negative",
          "Error on registration" + JSON.stringify(error),
          "warning",
          this.$q
        );
      });

      PushNotifications.addListener(
        "pushNotificationReceived",
        (notification) => {
          alert("Push received: " + JSON.stringify(notification));
        }
      );
    },
    registerDevice() {
      APIinterface.registerDevice(
        this.token,
        this.device_uuid,
        this.platform
      ).then((data) => {
        //APIinterface.notify('green-5', data.msg, 'check_circle', this.$q)
      });
    },
    updateDevice() {
      APIinterface.updateDevice(
        this.token,
        this.device_uuid,
        this.platform
      ).then((data) => {
        //APIinterface.notify('green-5', data.msg, 'check_circle', this.$q)
      });
    },
  },
};
</script>
