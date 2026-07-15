<template>
  <q-btn
    @click="Signin"
    :loading="loading"
    unelevated
    no-caps
    color="mygrey"
    class="full-width text-weight-bold"
    size="lg"
  >
    <div class="row justify-between full-width">
      <div class="col text-left text-dark">
        {{ $t("Continue with Google") }}
      </div>
      <div class="col-1 text-right text-dark">
        <q-icon name="lab la-google-plus-g" />
      </div>
    </div>
  </q-btn>
</template>

<script>
import { GoogleAuth } from "@codetrix-studio/capacitor-google-auth";
import APIinterface from "src/api/APIinterface";

export default {
  name: "GoogleLogin",
  props: ["client_id"],
  data() {
    return {
      loading: false,
    };
  },
  mounted() {
    this.init();
  },
  methods: {
    init() {
      //if (this.$q.platform.is.desktop) {
      if (!this.$q.capacitor) {
        GoogleAuth.initialize({
          clientId: this.client_id,
          scopes: ["profile", "email"],
          grantOfflineAccess: true,
        });
      } else {
        GoogleAuth.initialize();
      }
    },
    Signin() {
      this.loading = true;
      GoogleAuth.signIn()
        .then((data) => {
          const $params = {
            id: data.id,
            email_address: data.email,
            first_name: data.givenName,
            last_name: data.familyName,
            social_strategy: "google",
            social_token: data.authentication.idToken,
          };
          this.$emit("afterLogin", $params);
        })
        .catch((error) => {
          if (error.code == 10) {
            APIinterface.notify(
              "dark",
              "Error app is not signin",
              "error",
              this.$q
            );
          } else if (error.code == -5) {
            //
          } else {
            APIinterface.notify(
              "dark",
              JSON.stringify(error),
              "error",
              this.$q
            );
          }
        })
        .then((data) => {
          this.loading = false;
        });
    },
  },
};
</script>
