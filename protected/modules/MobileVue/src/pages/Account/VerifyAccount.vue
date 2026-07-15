<template>
  <q-header
    :class="{
      'bg-mydark text-white': $q.dark.mode,
      'bg-white text-black': !$q.dark.mode,
    }"
  >
    <q-toolbar>
      <q-btn
        to="/user/login"
        flat
        round
        dense
        icon="arrow_back"
        class="q-mr-sm"
        :color="$q.dark.mode ? 'white' : 'dark'"
      />
    </q-toolbar>
  </q-header>

  <!-- <q-footer class="transparent text-dark"> -->
  <q-page class="flex flex-center">
    <div class="full-width">
      <q-card flat>
        <div class="text-center">
          <q-form @submit="onSubmit" class="q-gutter-md">
            <div class="w-75 margin-auto">
              <p class="line-normal text-left">{{ sent_message }}</p>

              <div class="row items-center q-col-gutter-md">
                <div class="col">
                  <q-input
                    v-model="code1"
                    ref="code1"
                    outlined
                    mask="#"
                    input-class="text-center text-h5 text-weight-bold"
                  />
                </div>
                <div class="col">
                  <q-input
                    v-model="code2"
                    ref="code2"
                    mask="#"
                    outlined
                    input-class="text-center text-h5 text-weight-bold"
                  />
                </div>
                <div class="col">
                  <q-input
                    v-model="code3"
                    ref="code3"
                    mask="#"
                    outlined
                    input-class="text-center text-h5 text-weight-bold"
                  />
                </div>
                <div class="col">
                  <q-input
                    v-model="code4"
                    ref="code4"
                    mask="#"
                    outlined
                    input-class="text-center text-h5 text-weight-bold"
                  />
                </div>
              </div>
              <!-- row -->

              <q-space class="q-pa-sm"></q-space>

              <q-btn
                @click="resendCode"
                v-if="counter === 0"
                dense
                no-caps
                flat
                text-color="text=dark"
                >{{ $t("Resend Code") }}</q-btn
              >
              <p v-else class="q-ma-none no-margin">
                <u>{{ $t("Resend Code in") }} {{ counter }}</u>
              </p>

              <q-space class="q-pa-sm"></q-space>

              <q-card-actions vertical align="center">
                <q-btn
                  type="submit"
                  :label="$t('Verify Now')"
                  unelevated
                  color="primary"
                  text-color="white"
                  class="full-width text-weight-bold"
                  :loading="loading"
                  size="lg"
                  no-caps
                />
              </q-card-actions>

              <q-space class="q-pa-md"></q-space>
            </div>
            <!-- w 75 -->
          </q-form>
        </div>
        <!-- center -->
      </q-card>
    </div>
  </q-page>
</template>

<script>
import APIinterface from "src/api/APIinterface";
import auth from "src/api/auth";
import { useClientStore } from "stores/ClientStore";

export default {
  name: "VerifyAccount",
  data() {
    return {
      loading: false,
      code1: "",
      code2: "",
      code3: "",
      code4: "",
      client_uuid: "",
      sent_message: "",
      code: "",
      counter: 5,
      timer: undefined,
      settings: [],
      data: [],
      signup_resend_counter: 5,
    };
  },
  setup() {
    const ClientStore = useClientStore();
    return { ClientStore };
  },
  watch: {
    counter(newval, oldval) {
      if (newval <= 0) {
        this.stopTimer();
      }
    },
    code1(newval, oldval) {
      if (!APIinterface.empty(newval)) {
        this.$refs.code2.focus();
      } else {
        this.$refs.code1.focus();
      }
    },
    code2(newval, oldval) {
      if (!APIinterface.empty(newval)) {
        this.$refs.code3.focus();
      } else {
        this.$refs.code1.focus();
      }
    },
    code3(newval, oldval) {
      if (!APIinterface.empty(newval)) {
        this.$refs.code4.focus();
      } else {
        this.$refs.code2.focus();
      }
    },
    code4(newval, oldval) {
      if (!APIinterface.empty(newval)) {
        this.$refs.code4.focus();
      } else {
        this.$refs.code3.focus();
      }
    },
  },
  mounted() {
    this.$refs.code1.focus();
    this.startTimer();
    this.getAccountStatus();
    this.client_uuid = this.$route.query.uuid;
  },
  methods: {
    getAccountStatus() {
      this.loading = true;
      APIinterface.getAccountStatus(this.$route.query.uuid)
        .then((data) => {
          this.sent_message = data.msg;
          this.data = data.details.data;
          this.settings = data.details.settings;
          this.counter = data.details.settings.signup_resend_counter;
          this.signup_resend_counter = this.counter;
        })
        .catch((error) => {
          APIinterface.notify("dark", error, "error", this.$q);
        })
        .then((data) => {
          this.loading = false;
        });
    },
    onSubmit() {
      let $autoLogin = true;
      if (
        this.settings.enabled_verification === "1" &&
        this.data.social_strategy === "google"
      ) {
        $autoLogin = false;
      }
      if (
        this.settings.enabled_verification === "1" &&
        this.data.social_strategy === "facebook"
      ) {
        $autoLogin = false;
      }
      const params = {
        auto_login: $autoLogin,
        client_uuid: this.$route.query.uuid,
        verification_code: this.code1 + this.code2 + this.code3 + this.code4,
        local_id: APIinterface.getStorage("local_id"),
      };
      this.loading = true;
      APIinterface.verifyCodeSignup(params)
        .then((data) => {
          if ($autoLogin) {
            auth.setUser(data.details.user_data);
            auth.setToken(data.details.user_token);
            this.ClientStore.user_settings = data.details.user_settings;

            const $placeId = APIinterface.getStorage("place_id");
            console.debug("=>" + $placeId);
            if (typeof $placeId !== "undefined" && $placeId !== null) {
              this.$router.push("/home");
            } else {
              this.$router.push("/location");
            }
          } else {
            this.$router.push({
              path: "/account/complete-registration",
              query: { uuid: this.$route.query.uuid },
            });
          }
        })
        .catch((error) => {
          APIinterface.notify("dark", error, "error", this.$q);
        })
        .then((data) => {
          this.loading = false;
        });
    },
    startTimer() {
      this.stopTimer();
      this.timer = setInterval(() => {
        this.counter--;
      }, 1000);
    },
    stopTimer() {
      clearInterval(this.timer);
    },
    resendCode() {
      APIinterface.requestCode(this.client_uuid)
        .then((data) => {
          this.counter = this.signup_resend_counter;
          this.startTimer();
          APIinterface.notify("green", data.msg, "check", this.$q);
        })
        .catch((error) => {
          APIinterface.notify("dark", error, "error", this.$q);
        })
        .then((data) => {});
    },
  },
};
</script>
