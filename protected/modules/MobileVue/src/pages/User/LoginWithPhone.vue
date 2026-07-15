<template>
  <q-header
    reveal
    reveal-offset="50"
    :class="{
      'bg-mydark text-white': $q.dark.mode,
      'bg-white text-dark': !$q.dark.mode,
    }"
  >
    <q-toolbar>
      <q-btn
        @click="$router.back()"
        flat
        round
        dense
        icon="las la-angle-left"
        class="q-mr-sm"
        :color="$q.dark.mode ? 'white' : 'dark'"
      />
      <q-toolbar-title class="text-weight-bold"
        >Sigin With Phone</q-toolbar-title
      >
    </q-toolbar>
  </q-header>
  <q-page padding class="flex flex-center">
    <div class="full-width q-pa-md">
      <h5 class="text-weight-bold">{{ $t("Let's Sign You In") }}</h5>
      <p class="text-weight-medium q-ma-none">
        {{ $t("Enter your phone number and password for sigin.") }}
      </p>
      <q-btn
        flat
        :label="dont_have_account"
        no-caps
        class="q-pa-none text-weight-bold min-height q-mb-md"
        color="secondary"
        to="/user/signup"
      />

      <q-form @submit="onSubmit">
        <q-input
          v-model="mobile_number"
          mask="##############"
          outlined
          lazy-rules
          :bg-color="$q.dark.mode ? 'grey600' : 'input'"
          :label-color="$q.dark.mode ? 'grey300' : 'grey'"
          borderless
          class="input-borderless"
          :rules="[
            (val) =>
              (val && val.length > 0) || this.$t('This field is required'),
          ]"
        >
          <template v-slot:prepend>
            <q-select
              dense
              v-model="mobile_prefix"
              :options="DataStore.phone_prefix_data"
              @filter="filterFn"
              behavior="dialog"
              input-debounce="700"
              style="border: none"
              emit-value
              borderless
              class="myq-field"
            >
              <template v-slot:option="{ itemProps, opt }">
                <q-item v-bind="itemProps">
                  <q-item-section avatar>
                    <q-img
                      :src="opt.flag"
                      style="height: 15px; max-width: 20px"
                    />
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>{{ opt.label }}</q-item-label>
                  </q-item-section>
                </q-item>
              </template>
              <template v-slot:no-option>
                <q-item>
                  <q-item-section class="text-grey">
                    {{ $t("No results") }}
                  </q-item-section>
                </q-item>
              </template>
            </q-select>
          </template>
        </q-input>

        <q-input
          v-model="password"
          :type="field_type"
          outlined
          :bg-color="$q.dark.mode ? 'grey600' : 'input'"
          :label-color="$q.dark.mode ? 'grey300' : 'grey'"
          borderless
          class="input-borderless"
          :label="$t('Password')"
          lazy-rules
          :rules="[
            (val) =>
              (val && val.length > 0) || this.$t('This field is required'),
          ]"
        >
          <template v-slot:append>
            <q-icon
              @click="
                field_type = field_type == 'password' ? 'text' : 'password'
              "
              :name="FieldIcon"
              color="grey"
              class="cursor-pointer"
            />
          </template>
        </q-input>

        <div class="row">
          <div class="col">
            <q-checkbox
              dense
              v-model="remember"
              :label="$t('Remember Me')"
              color="primary"
            />
          </div>
          <div class="col text-right">
            <q-btn
              flat
              :label="$t('Forgot Password?')"
              no-caps
              class="q-pa-none text-weight-bold min-height q-mb-md"
              color="secondary"
              to="/user/forgotpass"
            />
          </div>
        </div>
        <!-- row -->

        <q-btn
          :loading="loading"
          type="submit"
          :label="$t('Sign In')"
          unelevated
          no-caps
          color="primary text-white"
          class="full-width text-weight-bold"
          size="lg"
        />
        <div class="text-center q-pa-sm">
          <q-btn
            flat
            :label="$t('Continue as guest')"
            no-caps
            class="q-pa-none text-weight-bold min-height"
            color="secondary"
            to="/home"
            replace
          />
        </div>
      </q-form>
    </div>
  </q-page>
</template>

<script>
import { defineAsyncComponent } from "vue";
import APIinterface from "src/api/APIinterface";
import auth from "src/api/auth";
import config from "src/api/config";
import { useDataStore } from "stores/DataStore";

export default {
  name: "LoginPage",
  data() {
    return {
      mobile_number: "",
      mobile_prefix: "",
      password: "",
      field_type: "password",
      loading: false,
      redirect: "",
      google_client_id: "",
      facebook_app_id: "",
      remember: false,
      dont_have_account: this.$t("Don't have an account?"),
    };
  },
  components: {},
  setup() {
    const DataStore = useDataStore();
    return { DataStore };
  },
  mounted() {
    this.redirect = this.$route.query.redirect;
    this.google_client_id = config.google_client_id;
    this.facebook_app_id = config.facebook_app_id;
  },
  computed: {
    FieldIcon() {
      return this.field_type === "password"
        ? "eva-eye-outline"
        : "eva-eye-off-outline";
    },
    hasSocialLogin() {
      if (!APIinterface.empty(this.google_client_id)) {
        return true;
      }
      if (!APIinterface.empty(this.facebook_app_id)) {
        return true;
      }
      return false;
    },
  },
  watch: {
    DataStore: {
      immediate: true,
      deep: true,
      handler(newValue, oldValue) {
        if (Object.keys(newValue.phone_default_data).length > 0) {
          this.mobile_prefix = "+" + newValue.phone_default_data.phonecode;
        }
      },
    },
  },
  methods: {
    onSubmit() {
      this.loading = true;
      APIinterface.fetchData("userLoginPhone", {
        mobile_prefix: this.mobile_prefix,
        mobile_number: this.mobile_number,
        password: this.password,
        local_id: APIinterface.getStorage("place_id"),
      })
        .then((data) => {
          APIinterface.notify("light-green", data.msg, "check_circle", this.$q);
          auth.setUser(data.details.user_data);
          auth.setToken(data.details.user_token);
          auth.setStorage("user_settings", data.details.user_settings);

          const $placeId = APIinterface.getStorage("place_id");
          if (typeof $placeId !== "undefined" && $placeId !== null) {
            if (
              typeof this.redirect !== "undefined" &&
              this.redirect !== null
            ) {
              this.$router.push(this.redirect);
            } else {
              this.$router.push("/home");
            }
          } else {
            this.$router.push("/location");
          }
        })
        .catch((error) => {
          APIinterface.notify("dark", error, "error", this.$q);
        })
        .then((data) => {
          this.loading = false;
        });
    },
  },
};
</script>
