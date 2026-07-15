<template>
  <q-header reveal reveal-offset="50" class="bg-white">
    <q-toolbar>
      <q-btn
        @click="$router.back()"
        flat
        round
        dense
        icon="arrow_back"
        class="q-mr-sm"
        color="dark"
      />
    </q-toolbar>
  </q-header>

  <q-page padding>
    <q-card flat>
      <q-inner-loading :showing="inner_loading" color="primary" size="md" />
      <div class="text-center">
        <q-form @submit="onSubmit" class="q-gutter-md">
          <h5 class="text-weight-bold q-mb-lg">Fill your information</h5>

          <div class="w-75 margin-auto">
            <q-input
              v-model="first_name"
              :label="$t('First name')"
              lazy-rules
              :rules="[
                (val) => (val && val.length > 0) || 'This field is required',
              ]"
            />

            <q-input
              v-model="last_name"
              :label="$t('Last name')"
              lazy-rules
              :rules="[
                (val) => (val && val.length > 0) || 'This field is required',
              ]"
            />

            <q-input
              v-model="email_address"
              label="Email address"
              disable
              lazy-rules
              :rules="[
                (val) => (val && val.length > 0) || 'This field is required',
              ]"
            />

            <q-input
              v-model="mobile_number"
              dense
              mask="##############"
              lazy-rules
              :rules="[
                (val) => (val && val.length > 0) || 'This field is required',
              ]"
            >
              <template v-slot:prepend>
                <q-select
                  dense
                  v-model="mobile_prefix"
                  :options="options"
                  @filter="filterFn"
                  behavior="dialog"
                  input-debounce="700"
                  style="border: none"
                  emit-value
                  borderless
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
                        <q-item-label v-html="opt.label" />
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
              :label="$t('Password')"
              lazy-rules
              :rules="[
                (val) => (val && val.length > 0) || 'This field is required',
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

            <q-input
              :type="field_type1"
              v-model="cpassword"
              :label="$t('Confirm Password')"
              lazy-rules
              :rules="[
                (val) => (val && val.length > 0) || 'This field is required',
              ]"
            >
              <template v-slot:append>
                <q-icon
                  @click="
                    field_type1 =
                      field_type1 == 'password' ? 'text' : 'password'
                  "
                  :name="FieldIcon1"
                  color="grey"
                  class="cursor-pointer"
                />
              </template>
            </q-input>

            <q-space class="q-pa-md"></q-space>

            <q-card-actions vertical align="center">
              <q-btn
                :loading="loading"
                type="submit"
                :label="$t('Submit')"
                unelevated
                color="primary"
                text-color="white"
                class="full-width text-weight-bold"
                size="lg"
              />
            </q-card-actions>

            <q-space class="q-pa-md"></q-space>
          </div>
          <!-- w-75 -->
        </q-form>
      </div>
      <!-- text-center -->
    </q-card>
  </q-page>
</template>

<script>
import APIinterface from "src/api/APIinterface";
import auth from "src/api/auth";
import { useClientStore } from "stores/ClientStore";

export default {
  name: "CompleteRegistration",
  data() {
    return {
      loading: false,
      field_type: "password",
      field_type1: "password",
      first_name: "",
      last_name: "",
      email_address: "",
      password: "",
      cpassword: "",
      mobile_number: "",
      mobile_prefix: "",
      options: [],
      inner_loading: false,
      client_uuid: "",
    };
  },
  setup() {
    const ClientStore = useClientStore();
    return { ClientStore };
  },
  computed: {
    FieldIcon() {
      return this.field_type === "password"
        ? "eva-eye-outline"
        : "eva-eye-off-outline";
    },
    FieldIcon1() {
      return this.field_type1 === "password"
        ? "eva-eye-outline"
        : "eva-eye-off-outline";
    },
  },
  mounted() {
    this.client_uuid = this.$route.query.uuid;
    this.getCustomerInfo();
  },
  methods: {
    getCustomerInfo() {
      this.inner_loading = true;
      APIinterface.getCustomerInfo(this.client_uuid)
        .then((data) => {
          this.first_name = data.details.first_name;
          this.last_name = data.details.last_name;
          this.email_address = data.details.email_address;

          this.mobile_prefix = "+" + data.details.default_data.phonecode;
          if (Object.keys(data.details.data).length > 0) {
            Object.entries(data.details.data).forEach(([key, items]) => {
              this.options.push({
                label: "+" + items.phonecode + " " + items.country_name,
                value: "+" + items.phonecode,
                flag: items.flag,
              });
            });
          }
        })
        .catch((error) => {
          APIinterface.notify("red-5", error, "error_outline", this.$q);
        })
        .then((data) => {
          this.inner_loading = false;
        });
    },
    onSubmit() {
      const $data = {
        client_uuid: this.client_uuid,
        first_name: this.first_name,
        last_name: this.last_name,
        password: this.password,
        cpassword: this.cpassword,
        mobile_prefix: this.mobile_prefix,
        mobile_number: this.mobile_number,
        local_id: APIinterface.getStorage("place_id"),
      };
      this.loading = true;
      APIinterface.completeSocialSignup($data)
        .then((data) => {
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
        })
        .catch((error) => {
          APIinterface.notify("red-5", error, null, this.$q);
        })
        .then((data) => {
          this.loading = false;
        });
    },
  },
};
</script>
