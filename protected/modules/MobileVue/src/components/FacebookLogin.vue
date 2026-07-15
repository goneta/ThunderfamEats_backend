<template>
  <q-btn
    @click="Signin"
    :loading="loading"
    unelevated
    no-caps
    color="myblue"
    class="full-width text-weight-bold"
    size="lg"
  >
    <div class="row justify-between full-width">
      <div class="col text-left text-white">
        {{ $t("Continue with Facebook") }}
      </div>
      <div class="col-1 text-right text-white">
        <q-icon name="lab la-facebook" />
      </div>
    </div>
  </q-btn>
</template>

<script>
import { FacebookLogin } from "@capacitor-community/facebook-login";
import APIinterface from "src/api/APIinterface";

const FACEBOOK_PERMISSIONS = [
  "email",
  "user_birthday",
  "user_photos",
  "user_gender",
];

export default {
  name: "FacebookLogin",
  props: ["app_id"],
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
      FacebookLogin.initialize({ appId: this.app_id });
    },
    Signin() {
      FacebookLogin.login({ permissions: FACEBOOK_PERMISSIONS })
        .then((data) => {
          this.getFbProfile(data.accessToken.token);
        })
        // eslint-disable-next-line
        .catch((error) => {
          // alert(JSON.stringify(error))
        })
        .then((data) => {
          //
        });
    },
    getFbProfile(accessToken) {
      this.loading = true;
      FacebookLogin.getProfile({ fields: ["email", "first_name", "last_name"] })
        .then((data) => {
          console.debug(data);
          const $params = {
            id: data.id,
            email_address: data.email,
            first_name: data.first_name,
            last_name: data.last_name,
            social_strategy: "facebook",
            social_token: accessToken,
          };
          this.$emit("afterLogin", $params);
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
