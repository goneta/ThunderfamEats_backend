<template>
  <q-header
    reveal
    reveal-offset="50"
    :class="{
      'bg-mydark text-white': $q.dark.mode,
      'bg-grey-1 text-dark': !$q.dark.mode,
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
      <q-toolbar-title class="text-weight-bold">{{
        $t("Edit Profile")
      }}</q-toolbar-title>
    </q-toolbar>
  </q-header>

  <q-page
    padding
    class="q-pl-md q-pr-md row items-stretch"
    :class="{
      'bg-mydark ': $q.dark.mode,
      'bg-grey-1 ': !$q.dark.mode,
    }"
  >
    <q-form @submit="checkForm" class="col-12">
      <div class="flex flex-center q-mb-md">
        <div class="relative-position">
          <q-avatar size="60px">
            <template v-if="hasData">
              <q-img
                :src="photo_data.path"
                spinner-color="primary"
                spinner-size="sm"
                fit="cover"
              />
            </template>
            <template v-else>
              <q-img
                :src="avatar"
                class="fit"
                fit="cover"
                spinner-size="sm"
                spinner-color="primary"
              />
            </template>
          </q-avatar>
          <div class="absolute-right" style="top: 20px; right: -10px">
            <q-btn
              v-if="$q.capacitor"
              round
              :color="$q.dark.mode ? 'grey600' : 'primary'"
              :text-color="$q.dark.mode ? 'grey300' : 'white'"
              icon="las la-pen"
              size="8px"
              unelevated
              @click="takePhoto"
            />

            <q-btn
              v-else
              round
              :color="$q.dark.mode ? 'grey600' : 'primary'"
              :text-color="$q.dark.mode ? 'grey300' : 'white'"
              icon="las la-pen"
              size="8px"
              unelevated
              @click="upload_enabled = !upload_enabled"
            />
          </div>
        </div>
      </div>
      <!-- flex -->

      <q-uploader
        v-if="upload_enabled"
        :url="upload_api"
        :label="$t('Upload files')"
        :color="$q.dark.mode ? 'grey600' : 'primary'"
        :text-color="$q.dark.mode ? 'grey300' : 'white'"
        no-thumbnails
        class="full-width q-mb-md"
        flat
        accept=".jpg, image/*"
        bordered
        :max-files="1"
        auto-upload
        max-total-size="1048576"
        @rejected="onRejectedFiles"
        :headers="[
          { name: 'Authorization', value: `token ${this.getToken()}` },
        ]"
        field-name="file"
        @uploaded="afterUploaded"
      />

      <q-card
        flat
        :class="{
          'bg-mydark text-white': $q.dark.mode,
          'bg-white text-black': !$q.dark.mode,
        }"
      >
        <q-card-section>
          <q-input
            v-model="first_name"
            :label="$t('First name')"
            outlined
            lazy-rules
            :bg-color="$q.dark.mode ? 'grey600' : 'input'"
            :label-color="$q.dark.mode ? 'grey300' : 'grey'"
            :rules="[
              (val) =>
                (val && val.length > 0) || this.$t('This field is required'),
            ]"
          />

          <q-input
            v-model="last_name"
            :label="$t('Last name')"
            outlined
            lazy-rules
            :bg-color="$q.dark.mode ? 'grey600' : 'input'"
            :label-color="$q.dark.mode ? 'grey300' : 'grey'"
            :rules="[
              (val) =>
                (val && val.length > 0) || this.$t('This field is required'),
            ]"
          />

          <q-input
            v-model="email_address"
            :label="$t('Email address')"
            outlined
            lazy-rules
            :bg-color="$q.dark.mode ? 'grey600' : 'input'"
            :label-color="$q.dark.mode ? 'grey300' : 'grey'"
            :rules="[
              (val) =>
                (val && val.length > 0) || this.$t('This field is required'),
            ]"
          />

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
        </q-card-section>
      </q-card>

      <q-space class="q-pa-sm"></q-space>
      <q-btn
        type="submit"
        :label="$t('Save')"
        unelevated
        no-caps
        color="primary text-white"
        class="full-width text-weight-bold"
        size="lg"
        :loading="loading"
      />
    </q-form>

    <StepsVerification
      ref="steps_verification"
      :sent_message="sent_message"
      @after-verifycode="afterVerifycode"
    />
  </q-page>
</template>

<script>
import { defineAsyncComponent } from "vue";
import APIinterface from "src/api/APIinterface";
import auth from "src/api/auth";
import { useDataStore } from "stores/DataStore";
import config from "src/api/config";
import AppCamera from "src/api/AppCamera";

export default {
  name: "EditProfile",
  components: {
    StepsVerification: defineAsyncComponent(() =>
      import("components/StepsVerification.vue")
    ),
  },
  data() {
    return {
      loading: false,
      first_name: "",
      last_name: "",
      email_address: "",
      mobile_number: "",
      mobile_prefix: "",
      avatar: "",
      original_email_address: "",
      original_mobile_number: "",
      sent_message: "",
      upload_api: config.api_base_url + "/updateAvatar",
      upload_enabled: false,
      filename: "",
      upload_path: "",
      photo_data: "",
    };
  },
  setup() {
    const DataStore = useDataStore();
    return { DataStore };
  },
  created() {
    this.getCurrentProfile();
  },
  computed: {
    hasData() {
      if (Object.keys(this.photo_data).length > 0) {
        return true;
      }
      return false;
    },
  },
  methods: {
    getCurrentProfile() {
      const $oldprofile = auth.getUser();
      if ($oldprofile) {
        this.original_email_address = $oldprofile.email_address;
        this.original_mobile_number = $oldprofile.contact_number;
        this.first_name = $oldprofile.first_name;
        this.last_name = $oldprofile.last_name;
        this.mobile_number = $oldprofile.contact_number_noprefix;
        this.mobile_prefix = $oldprofile.phone_prefix;
        this.email_address = $oldprofile.email_address;
        this.avatar = $oldprofile.avatar;
      }
    },
    checkForm() {
      let _change = false;
      if (this.email_address !== this.original_email_address) {
        _change = true;
      }
      const phone = this.mobile_prefix + this.mobile_number;
      if (phone !== this.original_mobile_number) {
        _change = true;
      }

      console.log(_change);

      if (_change) {
        this.loading = true;
        APIinterface.RequestEmailCode()
          .then((data) => {
            this.sent_message = data.msg;
            this.show_modal = false;
            this.$refs.steps_verification.show_modal = true;
          })
          .catch((error) => {
            APIinterface.notify("dark", error, "error", this.$q);
          })
          .then((data) => {
            this.loading = false;
          });
      } else {
        this.onSubmit("");
      }
    },
    onSubmit(code) {
      this.loading = true;
      APIinterface.showLoadingBox("", this.$q);
      const params = {
        code,
        first_name: this.first_name,
        last_name: this.last_name,
        email_address: this.email_address,
        mobile_number: this.mobile_number,
        mobile_prefix: this.mobile_prefix,
        filename: this.filename,
        upload_path: this.upload_path,
        file_data: this.hadData() ? this.photo_data.data : "",
        image_type: this.hadData() ? this.photo_data.format : "",
      };
      APIinterface.saveProfile(params)
        .then((data) => {
          this.$refs.steps_verification.show_modal = false;
          auth.setUser(data.details);
          APIinterface.notify("green", data.msg, "check", this.$q);
          this.getCurrentProfile();
        })
        .catch((error) => {
          APIinterface.notify("dark", error, "error", this.$q);
        })
        .then((data) => {
          this.loading = false;
          APIinterface.hideLoadingBox(this.$q);
        });
    },
    afterVerifycode(code) {
      this.onSubmit(code);
    },
    getToken() {
      return auth.getToken();
    },
    afterUploaded(files) {
      const response = JSON.parse(files.xhr.responseText);
      if (response.code === 1) {
        this.avatar = response.details.url_image;
        this.filename = response.details.filename;
        this.upload_path = response.details.upload_path;
      } else {
        this.avatar = "";
        this.filename = "";
        this.upload_path = "";
        APIinterface.notify("dark", response.msg, "error", this.$q);
      }
    },
    takePhoto() {
      AppCamera.isCameraEnabled()
        .then((data) => {
          AppCamera.isFileAccessEnabled()
            .then((data) => {
              AppCamera.getPhoto(1)
                .then((data) => {
                  this.photo_data = data;
                })
                .catch((error) => {
                  this.photo_data = [];
                });
              //
            })
            .catch((error) => {
              if (this.$q.platform.is.ios) {
                this.upload_enabled = !this.upload_enabled;
              }
            });
          //
        })
        .catch((error) => {
          if (this.$q.platform.is.ios) {
            this.upload_enabled = !this.upload_enabled;
          }
        });
    },
    hadData() {
      if (Object.keys(this.photo_data).length > 0) {
        return true;
      }
      return false;
    },
  },
};
</script>
