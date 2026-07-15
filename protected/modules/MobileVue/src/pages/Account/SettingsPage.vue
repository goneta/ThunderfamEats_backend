<template>
  <q-header reveal reveal-offset="50" class="bg-grey-1 text-dark">
    <q-toolbar>
      <q-btn
        @click="$router.back()"
        flat
        round
        dense
        icon="las la-angle-left"
        class="q-mr-sm"
        color="dark"
      />
      <q-toolbar-title class="text-weight-bold">{{
        $t("Settings")
      }}</q-toolbar-title>
    </q-toolbar>
  </q-header>

  <q-page padding class="bg-grey-1 q-pl-md q-pr-md row items-stretch">
    <q-inner-loading
      v-if="loading"
      :showing="true"
      color="primary"
      size="md"
      label-class="dark"
      class="transparent"
    />

    <q-card v-else flat class="radius8 col-12">
      <q-list>
        <q-item>
          <q-item-section>
            <q-item-label>{{ $t("Receive push notifications") }}</q-item-label>
          </q-item-section>
          <q-item-section side>
            <q-toggle v-model="app_push_notifications" />
          </q-item-section>
        </q-item>

        <q-item>
          <q-item-section>
            <q-item-label>{{ $t("Receive SMS notifications") }}</q-item-label>
          </q-item-section>
          <q-item-section side>
            <q-toggle v-model="app_sms_notifications" />
          </q-item-section>
        </q-item>

        <q-item>
          <q-item-section>
            <q-item-label>{{
              $t("Promotional Push notifications")
            }}</q-item-label>
          </q-item-section>
          <q-item-section side>
            <q-toggle v-model="promotional_push_notifications" />
          </q-item-section>
        </q-item>

        <q-item>
          <q-item-section>
            <q-item-label>{{ $t("Receive offers by email") }}</q-item-label>
          </q-item-section>
          <q-item-section side>
            <q-toggle v-model="offers_email_notifications" />
          </q-item-section>
        </q-item>
      </q-list>
    </q-card>

    <q-footer
      reveal
      class="bg-grey-1 q-pl-md q-pr-md q-pb-sm q-pt-sm text-dark"
    >
      <q-btn
        @click="saveSettings"
        :loading="loading2"
        :label="$t('Save')"
        unelevated
        no-caps
        color="primary text-white"
        class="full-width text-weight-bold"
        size="lg"
      />
    </q-footer>
  </q-page>
</template>

<script>
import { FCM } from "@capacitor-community/fcm";
import { PushNotifications } from "@capacitor/push-notifications";
import APIinterface from "src/api/APIinterface";
import config from "src/api/config";

export default {
  name: "SettingsPage",
  data() {
    return {
      loading: false,
      loading2: false,
      data: [],
      app_push_notifications: false,
      app_sms_notifications: false,
      offers_email_notifications: false,
      promotional_push_notifications: false,
    };
  },
  mounted() {
    this.getSettings();
  },
  methods: {
    getSettings() {
      this.loading = true;
      APIinterface.getSettings()
        .then((data) => {
          this.app_push_notifications =
            data.details.app_push_notifications === "1";
          this.app_sms_notifications =
            data.details.app_sms_notifications === "1";
          this.offers_email_notifications =
            data.details.offers_email_notifications === "1";
          this.promotional_push_notifications =
            data.details.promotional_push_notifications === "1";
        })
        .catch((error) => {
          APIinterface.notify("dark", error, "error", this.$q);
        })
        .then((data) => {
          this.loading = false;
        });
    },
    saveSettings() {
      this.loading2 = true;
      const $data = {
        app_push_notifications: this.app_push_notifications,
        app_sms_notifications: this.app_sms_notifications,
        offers_email_notifications: this.offers_email_notifications,
        promotional_push_notifications: this.promotional_push_notifications,
      };
      APIinterface.saveSettings($data)
        .then((data) => {
          APIinterface.notify("dark", data.msg, "check", this.$q);
          const $appPush = data.details.app_push_notifications;

          if (this.$q.platform.is.mobile) {
            if ($appPush) {
              console.debug("subscribe");
              FCM.subscribeTo({ topic: config.topic })
                .then((r) => console.debug("subscribed to topic"))
                .catch((err) => console.log(err));
            } else {
              console.debug("un-subscribe");
              FCM.unsubscribeFrom({ topic: config.topic })
                .then(() => console.debug("unsubscribed from topic"))
                .catch((err) => console.log(err));
            }
          }
        })
        .catch((error) => {
          APIinterface.notify("dark", error, "error", this.$q);
        })
        .then((data) => {
          this.loading2 = false;
        });
    },
  },
};
</script>
