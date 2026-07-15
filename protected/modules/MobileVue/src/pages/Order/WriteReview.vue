<template>
  <q-header
    reveal
    reveal-offset="50"
    :class="{
      'bg-mydark text-white': $q.dark.mode,
      'bg-white text-black': !$q.dark.mode,
    }"
  >
    <q-toolbar class="text-dark">
      <q-btn
        v-if="back_url"
        @click="$router.back()"
        flat
        round
        dense
        icon="las la-angle-left"
        class="q-mr-sm"
        :color="$q.dark.mode ? 'white' : 'dark'"
      />
      <q-btn
        v-else
        :to="`/account/trackorder?order_uuid=${order_uuid}`"
        rounded
        unelevated
        color="white"
        text-color="dark"
        icon="eva-arrow-back-outline"
        dense
        no-caps
      />
      <q-toolbar-title
        class="text-darkx text-weight-bold"
        :class="{
          'text-white': $q.dark.mode,
          'text-dark': !$q.dark.mode,
        }"
      >
        {{ $t("Write Review") }}
      </q-toolbar-title>
    </q-toolbar>
  </q-header>

  <q-form @submit="onSubmit">
    <q-page class="q-pl-md q-pr-md" padding>
      <div class="q-mb-md">
        <q-rating
          v-model="rating_value"
          size="md"
          :max="5"
          :color="$q.dark.mode ? 'white' : 'grey-5'"
          color-selected="primary"
          class="q-mb-xs"
        />
      </div>

      <h6 class="text-weight-bold no-margin q-pb-sm">What did you like?</h6>
      <q-input
        v-model="tags_like"
        label="Describe in few words"
        outlined
        lazy-rules
        :bg-color="$q.dark.mode ? 'grey600' : 'input'"
        :label-color="$q.dark.mode ? 'grey300' : 'grey'"
        borderless
        class="input-borderless"
        :rules="[
          (val) => val.length <= 50 || 'Please use maximum 50 characters',
        ]"
      />

      <h6 class="text-weight-bold no-margin q-pb-sm">What did you not like?</h6>

      <q-input
        v-model="tags_not_like"
        label="Describe in few words"
        outlined
        lazy-rules
        :bg-color="$q.dark.mode ? 'grey600' : 'input'"
        :label-color="$q.dark.mode ? 'grey300' : 'grey'"
        borderless
        class="input-borderless"
        :rules="[
          (val) => val.length <= 50 || 'Please use maximum 50 characters',
        ]"
      />

      <h6 class="text-weight-bold no-margin q-pb-sm">Add Photos</h6>
      <q-uploader
        :url="upload_api"
        label="Drop files here to upload"
        :color="$q.dark.mode ? 'grey600' : 'secondary'"
        :text-color="$q.dark.mode ? 'grey300' : 'white'"
        no-thumbnails
        class="full-width q-mb-md"
        flat
        accept=".jpg, image/*"
        bordered
        auto-upload
        max-total-size="1048576"
        @rejected="onRejectedFiles"
        :headers="[
          { name: 'Authorization', value: `token ${this.getToken()}` },
        ]"
        field-name="file"
        @uploaded="afterUploaded"
      />

      <h6 class="text-weight-bold no-margin q-pb-sm">Write your review</h6>

      <q-input
        v-model="review_content"
        outlined
        autogrow
        lazy-rules
        :bg-color="$q.dark.mode ? 'grey600' : 'input'"
        :label-color="$q.dark.mode ? 'grey300' : 'grey'"
        borderless
        class="input-borderless"
        :rules="[(val) => (val && val.length > 0) || 'This field is required']"
      />

      <div class="q-pb-sm">
        <q-checkbox
          v-model="as_anonymous"
          size="sm"
          color="secondary"
          label="post review as anonymous"
        />
      </div>
    </q-page>

    <q-footer class="bg-white q-pl-md q-pr-md q-pb-sm q-pt-sm text-dark">
      <q-btn
        type="submit"
        unelevated
        color="primary"
        text-color="white"
        no-caps
        class="full-width"
        :loading="loading"
        style="letter-spacing: 2px"
        label="Add Review"
        size="lg"
      />
    </q-footer>
  </q-form>
</template>

<script>
import APIinterface from "src/api/APIinterface";
import config from "src/api/config";
import auth from "src/api/auth";

export default {
  name: "WriteReview",
  data() {
    return {
      order_uuid: "",
      loading: false,
      upload_api: config.api_base_url + "/uploadReview",
      rating_value: 0,
      as_anonymous: false,
      review_content: "",
      tags_like: "",
      tags_not_like: "",
      upload_images: "",
      back_url: false,
    };
  },
  mounted() {
    this.order_uuid = this.$route.params.order_uuid;
    this.back_url = this.$route.params.back_url;
    //this.back_url = this.back_url === "1";
  },
  methods: {
    onRejectedFiles(rejectedEntries) {
      APIinterface.notify(
        "negative",
        `${rejectedEntries.length} file(s) did not pass validation constraints`,
        "error_outline",
        this.$q
      );
    },
    getToken() {
      return auth.getToken();
    },
    onSubmit() {
      const params = {
        order_uuid: this.order_uuid,
        review_content: this.review_content,
        rating_value: this.rating_value,
        as_anonymous: this.as_anonymous,
        tags_like: [this.tags_like],
        tags_not_like: [this.tags_not_like],
        upload_images: this.upload_images,
      };
      this.loading = true;
      APIinterface.addReview(params)
        .then((data) => {
          APIinterface.notify("green", data.msg, "check", this.$q);
          if (this.back_url) {
            this.$router.push(this.back_url);
          } else {
            this.$router.push({
              path: "/account/trackorder",
              query: { order_uuid: this.order_uuid },
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
    afterUploaded(files) {
      const response = JSON.parse(files.xhr.responseText);
      if (response.code === 1) {
        this.upload_images = response.details;
      } else {
        APIinterface.notify("dark", response.msg, "error", this.$q);
      }
    },
  },
};
</script>
